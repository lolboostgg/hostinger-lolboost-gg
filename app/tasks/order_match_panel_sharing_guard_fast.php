<?php
/**
 * Fast order/panel sharing guard for order_matches.
 *
 * Detects: same booster has overlapping games in the same mode.
 * Alerts: solo+solo and duo+duo only. solo+duo is allowed.
 * Time: played_at + duration. Do NOT use created_at for game time.
 *
 * Fast path for website requests:
 *   - one indexed SELECT by primary key
 *   - one indexed overlap SELECT bounded to a small time window
 *   - inserts a pending alert into order_match_overlap_alerts
 *   - does NOT call Discord inside the customer/admin request by default
 *
 * After inserting a new order_matches row:
 *   require_once __DIR__ . '/order_match_panel_sharing_guard_fast.php';
 *   lb_order_match_queue_panel_sharing_alert($db, $newOrderMatchId);
 *
 * Cron/worker every minute:
 *   lb_order_match_send_pending_panel_sharing_alerts($db, DISCORD_ADMIN_WEBHOOK_URL, 20);
 */

if (!function_exists('lb_psg_quote')) {
    function lb_psg_quote($db, string $value): string {
        if (is_object($db) && method_exists($db, 'quote')) {
            return $db->quote($value);
        }
        if (isset($GLOBALS['mysqli']) && $GLOBALS['mysqli'] instanceof mysqli) {
            return "'" . $GLOBALS['mysqli']->real_escape_string($value) . "'";
        }
        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
    }
}

if (!function_exists('lb_psg_fetch_one')) {
    function lb_psg_fetch_one($db, string $sql): ?array {
        $rows = $db->run($sql);
        if (!is_array($rows) || empty($rows)) return null;
        $row = reset($rows);
        return is_array($row) ? $row : null;
    }
}

if (!function_exists('lb_psg_fetch_all')) {
    function lb_psg_fetch_all($db, string $sql): array {
        $rows = $db->run($sql);
        return is_array($rows) ? $rows : [];
    }
}

if (!function_exists('lb_psg_exec')) {
    function lb_psg_exec($db, string $sql): void {
        $db->run($sql);
    }
}

if (!function_exists('lb_psg_mode')) {
    function lb_psg_mode($playMode): ?string {
        $mode = strtolower(trim((string)$playMode));
        if ($mode === 'solo') return 'solo';
        if ($mode === 'duo') return 'duo';
        return null; // old NULL rows are ignored to avoid false alerts
    }
}

if (!function_exists('lb_psg_seconds')) {
    function lb_psg_seconds(int $seconds): string {
        $seconds = max(0, $seconds);
        return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    }
}

if (!function_exists('lb_order_match_queue_panel_sharing_alert')) {
    function lb_order_match_queue_panel_sharing_alert($db, $newOrderMatchId): int {
        $newOrderMatchId = (int)$newOrderMatchId;
        if ($newOrderMatchId <= 0) return 0;

        $minDuration = defined('LB_PSG_MIN_DURATION_SECONDS') ? (int)LB_PSG_MIN_DURATION_SECONDS : 300;
        $lookbackHours = defined('LB_PSG_LOOKBACK_HOURS') ? (int)LB_PSG_LOOKBACK_HOURS : 4;
        if ($lookbackHours < 1) $lookbackHours = 4;

        $current = lb_psg_fetch_one($db, "
            SELECT
                id, order_id, booster_id, play_mode, match_id, champion, position,
                duration, queue_id, played_at,
                DATE_ADD(played_at, INTERVAL duration SECOND) AS ended_at
            FROM order_matches
            WHERE id = {$newOrderMatchId}
            LIMIT 1
        ");

        if (!$current) return 0;
        if ((int)($current['booster_id'] ?? 0) <= 0) return 0;
        if ((int)($current['duration'] ?? 0) < $minDuration) return 0;

        $mode = lb_psg_mode($current['play_mode'] ?? null);
        if ($mode === null) return 0;

        $boosterId = (int)$current['booster_id'];
        $orderId = (int)$current['order_id'];
        $modeSql = lb_psg_quote($db, $mode);
        $matchIdSql = lb_psg_quote($db, (string)$current['match_id']);
        $startedAtSql = lb_psg_quote($db, (string)$current['played_at']);
        $endedAtSql = lb_psg_quote($db, (string)$current['ended_at']);

        // Uses idx_order_matches_panel_guard: booster_id + play_mode + played_at.
        // The played_at lower bound prevents scanning all historical games for the booster.
        $overlaps = lb_psg_fetch_all($db, "
            SELECT
                id, order_id, booster_id, play_mode, match_id, champion, position,
                duration, queue_id, played_at,
                DATE_ADD(played_at, INTERVAL duration SECOND) AS ended_at,
                TIMESTAMPDIFF(
                    SECOND,
                    GREATEST(played_at, {$startedAtSql}),
                    LEAST(DATE_ADD(played_at, INTERVAL duration SECOND), {$endedAtSql})
                ) AS overlap_seconds
            FROM order_matches
            WHERE booster_id = {$boosterId}
              AND play_mode = {$modeSql}
              AND id <> {$newOrderMatchId}
              AND order_id <> {$orderId}
              AND match_id <> {$matchIdSql}
              AND duration >= {$minDuration}
              AND COALESCE(is_remake, 0) = 0
              AND played_at >= DATE_SUB({$startedAtSql}, INTERVAL {$lookbackHours} HOUR)
              AND played_at < {$endedAtSql}
              AND DATE_ADD(played_at, INTERVAL duration SECOND) > {$startedAtSql}
            ORDER BY overlap_seconds DESC
            LIMIT 5
        ");

        $queued = 0;
        foreach ($overlaps as $other) {
            $otherId = (int)$other['id'];
            $pairA = min($newOrderMatchId, $otherId);
            $pairB = max($newOrderMatchId, $otherId);
            $otherOrderId = (int)$other['order_id'];
            $orderA = min($orderId, $otherOrderId);
            $orderB = max($orderId, $otherOrderId);
            $overlapSeconds = max(0, (int)($other['overlap_seconds'] ?? 0));
            if ($overlapSeconds <= 0) continue;

            $matchAInfo = lb_psg_quote($db, json_encode($current, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $matchBInfo = lb_psg_quote($db, json_encode($other, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            lb_psg_exec($db, "
                INSERT IGNORE INTO order_match_overlap_alerts
                    (booster_id, play_mode_group, match_a_id, match_b_id, order_a_id, order_b_id,
                     overlap_seconds, match_a_info, match_b_info, status)
                VALUES
                    ({$boosterId}, {$modeSql}, {$pairA}, {$pairB}, {$orderA}, {$orderB},
                     {$overlapSeconds}, {$matchAInfo}, {$matchBInfo}, 'pending')
            ");
            $queued++;
        }

        return $queued;
    }
}

if (!function_exists('lb_psg_discord_post')) {
    function lb_psg_discord_post(string $webhookUrl, array $payload): bool {
        if ($webhookUrl === '') return false;
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) return false;

        if (function_exists('curl_init')) {
            $ch = curl_init($webhookUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 4,
            ]);
            curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code >= 200 && $code < 300;
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $json,
                'timeout' => 4,
            ],
        ]);
        return @file_get_contents($webhookUrl, false, $ctx) !== false;
    }
}

if (!function_exists('lb_psg_decode_info')) {
    function lb_psg_decode_info($json): array {
        $data = json_decode((string)$json, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('lb_order_match_send_pending_panel_sharing_alerts')) {
    function lb_order_match_send_pending_panel_sharing_alerts($db, ?string $webhookUrl = null, int $limit = 20): int {
        $webhookUrl = $webhookUrl
            ?? (defined('DISCORD_ADMIN_WEBHOOK_URL') ? DISCORD_ADMIN_WEBHOOK_URL : '')
            ?: (getenv('DISCORD_ADMIN_WEBHOOK_URL') ?: '');
        if ($webhookUrl === '') return 0;
        if ($limit < 1 || $limit > 100) $limit = 20;

        $alerts = lb_psg_fetch_all($db, "
            SELECT *
            FROM order_match_overlap_alerts
            WHERE status = 'pending'
              AND attempts < 5
            ORDER BY id ASC
            LIMIT {$limit}
        ");

        $sent = 0;
        foreach ($alerts as $alert) {
            $alertId = (int)$alert['id'];
            lb_psg_exec($db, "
                UPDATE order_match_overlap_alerts
                SET status = 'sending', attempts = attempts + 1, last_attempt_at = NOW()
                WHERE id = {$alertId} AND status = 'pending'
            ");

            $a = lb_psg_decode_info($alert['match_a_info'] ?? '');
            $b = lb_psg_decode_info($alert['match_b_info'] ?? '');
            $mode = strtoupper((string)$alert['play_mode_group']);
            $overlap = lb_psg_seconds((int)$alert['overlap_seconds']);

            $payload = [
                'username' => 'Panel Guard',
                'content' => '@admins possible order/panel sharing detected',
                'embeds' => [[
                    'title' => '⚠️ Same booster has overlapping ' . $mode . ' games',
                    'color' => 16753920,
                    'fields' => [
                        ['name' => 'Booster ID', 'value' => (string)$alert['booster_id'], 'inline' => true],
                        ['name' => 'Mode', 'value' => $mode, 'inline' => true],
                        ['name' => 'Overlap', 'value' => $overlap, 'inline' => true],
                        ['name' => 'Order A / Match A', 'value' => '#' . ($a['order_id'] ?? $alert['order_a_id']) . ' / ' . ($a['match_id'] ?? $alert['match_a_id']), 'inline' => false],
                        ['name' => 'A Time', 'value' => ($a['played_at'] ?? '-') . ' → ' . ($a['ended_at'] ?? '-') . ' (' . lb_psg_seconds((int)($a['duration'] ?? 0)) . ')', 'inline' => false],
                        ['name' => 'Order B / Match B', 'value' => '#' . ($b['order_id'] ?? $alert['order_b_id']) . ' / ' . ($b['match_id'] ?? $alert['match_b_id']), 'inline' => false],
                        ['name' => 'B Time', 'value' => ($b['played_at'] ?? '-') . ' → ' . ($b['ended_at'] ?? '-') . ' (' . lb_psg_seconds((int)($b['duration'] ?? 0)) . ')', 'inline' => false],
                    ],
                    'timestamp' => gmdate('c'),
                ]],
            ];

            if (lb_psg_discord_post($webhookUrl, $payload)) {
                lb_psg_exec($db, "
                    UPDATE order_match_overlap_alerts
                    SET status = 'sent', sent_at = NOW(), error_message = NULL
                    WHERE id = {$alertId}
                ");
                $sent++;
            } else {
                lb_psg_exec($db, "
                    UPDATE order_match_overlap_alerts
                    SET status = IF(attempts >= 5, 'failed', 'pending'), error_message = 'Discord webhook failed'
                    WHERE id = {$alertId}
                ");
            }
        }

        return $sent;
    }
}
