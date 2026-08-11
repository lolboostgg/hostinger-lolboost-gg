<?php
// Now triggered by discord_message_notification.php's own already-scheduled cron via
// a blocking HTTP call — keep running even if that caller's timeout elapses first.
ignore_user_abort(true);
set_time_limit(180);

ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');

function lb_sumc_find_up(string $start, string $relative, int $depth = 8): ?string
{
    $dir = $start;
    for ($i = 0; $i <= $depth; $i++) {
        $candidate = $dir . '/' . ltrim($relative, '/');
        if (is_file($candidate)) return $candidate;
        $parent = dirname($dir);
        if ($parent === $dir) break;
        $dir = $parent;
    }
    return null;
}

$autoload = lb_sumc_find_up(__DIR__, 'vendor/autoload.php');
$config = lb_sumc_find_up(__DIR__, 'core/config.php');
$functions = lb_sumc_find_up(__DIR__, 'core/functions.php');
if (!$autoload || !$config || !$functions) { http_response_code(500); exit; }
require $autoload;
require $config;
require $functions;

// Single-instance guard: if a previous run is still going (e.g. slow SMTP/DB),
// skip this tick instead of stacking a second process on top — that stacking is
// what drives CPU to 100% when a minute-cron overruns a minute.
$__sumc_lock = @fopen(rtrim(sys_get_temp_dir(), "/\\") . '/lolboost_seller_unread_cron.lock', 'c');
if ($__sumc_lock === false || !flock($__sumc_lock, LOCK_EX | LOCK_NB)) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'skipped' => 'already_running']);
    exit;
}

// Hostinger URL cron: ?key=<REALTIME_SECRET>. CLI execution needs no key.
$isCli = PHP_SAPI === 'cli';
$provided = trim((string)($_GET['key'] ?? $_SERVER['HTTP_X_REALTIME_SECRET'] ?? ''));
$expected = defined('REALTIME_SECRET') ? trim((string)REALTIME_SECRET) : '';
if (!$isCli && ($expected === '' || !hash_equals($expected, $provided))) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'reason' => 'unauthorized']);
    exit;
}

global $db;
if (function_exists('lb_legacy_chat_ensure_schema')) lb_legacy_chat_ensure_schema();

// One-time reminder marker. Without this, a message that stays unseen for hours
// gets re-processed (and re-curled to the email endpoint = a full PHP bootstrap)
// EVERY minute for up to 24h — up to 1440 wasted processes per stuck message,
// which is what pegs the CPU. Once a message has been handled we stamp this column
// and exclude it from the queries below, so each message triggers at most one
// reminder pass (transient HTTP failures leave it NULL so they still retry).
try { $db->run("ALTER TABLE legacy_chat_messages ADD COLUMN IF NOT EXISTS reminder_notified_at DATETIME NULL DEFAULT NULL"); } catch (\Throwable $e) {}

// Repair rollout rows that were imported as Europe/Berlin wall-clock values
// into a UTC database. Limit this strictly to timestamps slightly in the future.
$localUtcOffset = (int)(new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format('Z');
if ($localUtcOffset > 0) {
    $db->run(
        "UPDATE legacy_chat_messages
            SET created_at=DATE_SUB(created_at, INTERVAL " . $localUtcOffset . " SECOND)
          WHERE created_at>DATE_ADD(UTC_TIMESTAMP(),INTERVAL 5 MINUTE)
            AND created_at<=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 3 HOUR)"
    );
}

// Import rollout JSON archives first. This also catches messages sent before
// the immediate DB-sync code was deployed (for example an already waiting chat).
$chatDir = (defined('SYS_PATH') ? rtrim((string)SYS_PATH, '/\\') : dirname(__DIR__, 3)) . '/public/uploads/private/chat';

// The archive import re-reads and re-syncs the 250 newest chat JSON files PER
// source table (up to 750 file reads + DB syncs) — that ran on every cron tick
// (every minute) and was a major CPU cost. It is only a safety backfill for chats
// created before immediate DB-sync was deployed; today every message is synced to
// the DB the moment it is sent. So we throttle the whole block to once per hour.
$archiveMarker = sys_get_temp_dir() . '/lolboost_seller_unread_archive_sync.ts';
$archiveDue = true;
$lastArchive = @filemtime($archiveMarker);
if ($lastArchive !== false && (time() - $lastArchive) < 3600) {
    $archiveDue = false;
}
// CLI/manual runs with --archive force a full backfill regardless of the throttle.
if ($isCli && in_array('--archive', $argv ?? [], true)) {
    $archiveDue = true;
}

if ($archiveDue) {
    @touch($archiveMarker);
    $sources = [
        ['table' => 'selling_accounts', 'type' => 'account', 'seed' => 'selling_account_', 'where' => "client_id IS NOT NULL AND client_id>0"],
        ['table' => 'selling_item_purchases', 'type' => 'item_purchase', 'seed' => 'selling_item_purchase_', 'where' => 'client_id>0'],
        ['table' => 'selling_topup_purchases', 'type' => 'topup_purchase', 'seed' => 'selling_topup_purchase_', 'where' => 'client_id>0'],
    ];
    foreach ($sources as $source) {
        try {
            $archiveRows = $db->run(
                "SELECT id,seller_id,client_id FROM {$source['table']} WHERE {$source['where']} ORDER BY id DESC LIMIT 250"
            ) ?: [];
            foreach ($archiveRows as $archiveRow) {
                $refId = (int)($archiveRow['id'] ?? 0);
                if ($refId <= 0) continue;
                $path = $chatDir . '/selling_' . sha1($source['seed'] . $refId) . '.json';
                if (!is_file($path)) continue;
                lb_legacy_chat_open(
                    'seller:' . $source['type'] . ':' . $refId,
                    $source['type'],
                    $refId,
                    ['seller_id' => (int)($archiveRow['seller_id'] ?? 0), 'client_id' => (int)($archiveRow['client_id'] ?? 0)],
                    $path
                );
            }
        } catch (\Throwable $e) {
            error_log('Seller unread cron archive sync failed for ' . $source['type'] . ': ' . $e->getMessage());
        }
    }
}

$rows = $db->run(
    "SELECT t.chat_type AS ref_type,t.ref_id,t.seller_id,t.client_id,m.id AS message_id,m.created_at
       FROM legacy_chat_threads t
       INNER JOIN legacy_chat_messages m ON m.id=(
           SELECT lm.id FROM legacy_chat_messages lm
            WHERE lm.thread_id=t.id AND lm.deleted=0
            ORDER BY lm.created_at DESC,lm.id DESC LIMIT 1
       )
      WHERE t.chat_type IN ('account','item_purchase','topup_purchase')
        AND t.seller_id>0 AND t.client_id>0
        AND m.sender_type='client'
        AND m.seen_by_seller=0
        AND m.reminder_notified_at IS NULL
        AND m.created_at<=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE)
        AND m.created_at>=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 24 HOUR)
      ORDER BY m.created_at ASC
      LIMIT 50"
) ?: [];

// Diagnostic state for the most recent message of every seller marketplace
// thread. Only computed on demand (?debug=1 / CLI with --debug) since the
// correlated subquery scans every thread and this cron already runs every
// minute — running it unconditionally on every automatic run was pure cost
// for a value nobody was looking at most of the time.
$wantsDebug = $isCli ? in_array('--debug', $argv ?? [], true) : !empty($_GET['debug']);
$diagnostics = ['recent_threads' => null, 'too_new' => null, 'already_read' => null, 'last_from_seller_or_system' => null, 'eligible' => null];
if ($wantsDebug) {
    $diagnosticRows = $db->run(
        "SELECT
            COUNT(*) AS recent_threads,
            SUM(CASE WHEN m.sender_type='client' AND m.seen_by_seller=0 AND m.created_at>DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) THEN 1 ELSE 0 END) AS too_new,
            SUM(CASE WHEN m.sender_type='client' AND m.seen_by_seller=1 THEN 1 ELSE 0 END) AS already_read,
            SUM(CASE WHEN m.sender_type<>'client' THEN 1 ELSE 0 END) AS last_from_seller_or_system,
            SUM(CASE WHEN m.sender_type='client' AND m.seen_by_seller=0 AND m.created_at<=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) THEN 1 ELSE 0 END) AS eligible
           FROM legacy_chat_threads t
           INNER JOIN legacy_chat_messages m ON m.id=(
               SELECT lm.id FROM legacy_chat_messages lm
                WHERE lm.thread_id=t.id AND lm.deleted=0
                ORDER BY lm.created_at DESC,lm.id DESC LIMIT 1
           )
          WHERE t.chat_type IN ('account','item_purchase','topup_purchase')
            AND t.seller_id>0 AND t.client_id>0
            AND m.created_at>=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 24 HOUR)"
    ) ?: [];
    $diagnostics = [
        'recent_threads' => (int)($diagnosticRows[0]['recent_threads'] ?? 0),
        'too_new' => (int)($diagnosticRows[0]['too_new'] ?? 0),
        'already_read' => (int)($diagnosticRows[0]['already_read'] ?? 0),
        'last_from_seller_or_system' => (int)($diagnosticRows[0]['last_from_seller_or_system'] ?? 0),
        'eligible' => (int)($diagnosticRows[0]['eligible'] ?? 0),
    ];
}

/**
 * Server-to-server call that skips Cloudflare.
 *
 * BASE_URL resolves to the Cloudflare edge, so with "Under Attack" mode enabled
 * these internal cron callbacks get the JS challenge back (HTTP 403) instead of
 * the task response. Pinning the hostname to 127.0.0.1 keeps the correct Host
 * header (vhost + TLS SNI stay intact) while the TCP connection never leaves the
 * server. If the loopback attempt fails at connection level we retry the normal
 * way, so nothing breaks on hosts without loopback HTTPS.
 */
function lb_sumc_post_internal(string $url, string $payload, string $secret, int $timeout): array
{
    $parts = parse_url($url);
    $host = (string)($parts['host'] ?? '');
    $port = (int)($parts['port'] ?? (($parts['scheme'] ?? 'https') === 'https' ? 443 : 80));

    $run = static function (bool $viaLoopback) use ($url, $payload, $secret, $timeout, $host, $port): array {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Realtime-Secret: ' . $secret],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => $timeout,
        ];
        if ($viaLoopback && $host !== '') {
            $opts[CURLOPT_RESOLVE] = [$host . ':' . $port . ':127.0.0.1'];
            // The loopback certificate does not match the public hostname.
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        return ['body' => $body, 'code' => $code, 'error' => $error];
    };

    $res = $run(true);
    if ($res['code'] === 0) {
        // Loopback unavailable (no listener / TLS refused) — use the public route.
        $res = $run(false);
    }
    return $res;
}

$taskUrl = rtrim((string)BASE_URL, '/') . '/app/tasks/seller_unread_message_email.php';
$results = [
    'checked' => count($rows), 'queued' => 0, 'skipped' => 0, 'failed' => 0,
    'email_sent' => 0, 'email_retry_queued' => 0, 'discord_dm_sent' => 0,
    'skip_reasons' => [], 'errors' => [],
];
foreach ($rows as $row) {
    $payload = json_encode([
        'seller_id' => (int)$row['seller_id'],
        'client_id' => (int)$row['client_id'],
        'ref_type' => (string)$row['ref_type'],
        'ref_id' => (int)$row['ref_id'],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $call = lb_sumc_post_internal($taskUrl, $payload, $expected, 15);
    $body = $call['body'];
    $code = $call['code'];
    $error = $call['error'];
    $decoded = json_decode((string)$body, true);
    if ($code >= 200 && $code < 300 && is_array($decoded)) {
        // Definitive answer (queued or permanently skipped) — stamp the message so
        // it isn't re-processed every minute for the next 24h.
        try { $db->run("UPDATE legacy_chat_messages SET reminder_notified_at=UTC_TIMESTAMP() WHERE id=?", (int)$row['message_id']); } catch (\Throwable $e) {}
        if (!empty($decoded['queued'])) {
            $results['queued']++;
            if (!empty($decoded['email_sent'])) $results['email_sent']++;
            elseif (!empty($decoded['email'])) $results['email_retry_queued']++;
            if (!empty($decoded['discord_dm'])) $results['discord_dm_sent']++;
        }
        else {
            $results['skipped']++;
            $reason = trim((string)($decoded['reason'] ?? 'unknown')) ?: 'unknown';
            $results['skip_reasons'][$reason] = (int)($results['skip_reasons'][$reason] ?? 0) + 1;
        }
    } else {
        $results['failed']++;
        $results['errors'][] = [
            'ref_type' => (string)$row['ref_type'],
            'ref_id' => (int)$row['ref_id'],
            'seller_id' => (int)$row['seller_id'],
            'http_code' => $code,
            'curl_error' => $error,
            'response' => substr(trim((string)$body), 0, 300),
        ];
        error_log('Seller unread cron callback failed: HTTP ' . $code . ($error !== '' ? ' - ' . $error : '') . ' body=' . substr((string)$body, 0, 300));
    }
}

// Reverse direction: seller/admin -> client. Keep this in the same cron so
// marketplace chat reminders need only one Hostinger cron job.
$clientRows = $db->run(
    "SELECT t.chat_type AS ref_type,t.ref_id,t.seller_id,t.client_id,m.id AS message_id,m.created_at
       FROM legacy_chat_threads t
       INNER JOIN legacy_chat_messages m ON m.id=(
           SELECT lm.id FROM legacy_chat_messages lm
            WHERE lm.thread_id=t.id AND lm.deleted=0
            ORDER BY lm.created_at DESC,lm.id DESC LIMIT 1
       )
      WHERE t.chat_type IN ('account','item_purchase','topup_purchase')
        AND t.seller_id>0 AND t.client_id>0
        AND m.sender_type IN ('seller','admin')
        AND m.seen_by_client=0
        AND m.reminder_notified_at IS NULL
        AND m.created_at<=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE)
        AND m.created_at>=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 24 HOUR)
      ORDER BY m.created_at ASC
      LIMIT 50"
) ?: [];

$clientTaskUrl = rtrim((string)BASE_URL, '/') . '/app/tasks/client_unread_message_email.php';
$clientResults = ['checked'=>count($clientRows),'queued'=>0,'email_sent'=>0,'email_retry_queued'=>0,'skipped'=>0,'failed'=>0,'skip_reasons'=>[],'errors'=>[]];
foreach ($clientRows as $row) {
    $payload = json_encode([
        'seller_id'=>(int)$row['seller_id'], 'client_id'=>(int)$row['client_id'],
        'ref_type'=>(string)$row['ref_type'], 'ref_id'=>(int)$row['ref_id'],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $call = lb_sumc_post_internal($clientTaskUrl, $payload, $expected, 25);
    $body = $call['body']; $code = $call['code']; $error = $call['error'];
    $decoded=json_decode((string)$body,true);
    if ($code>=200 && $code<300 && is_array($decoded)) {
        try { $db->run("UPDATE legacy_chat_messages SET reminder_notified_at=UTC_TIMESTAMP() WHERE id=?", (int)$row['message_id']); } catch (\Throwable $e) {}
        if (!empty($decoded['queued'])) {
            $clientResults['queued']++;
            if (!empty($decoded['email_sent'])) $clientResults['email_sent']++;
            elseif (!empty($decoded['email'])) $clientResults['email_retry_queued']++;
        } else {
            $clientResults['skipped']++;
            $reason=trim((string)($decoded['reason']??'unknown'))?:'unknown';
            $clientResults['skip_reasons'][$reason]=(int)($clientResults['skip_reasons'][$reason]??0)+1;
        }
    } else {
        $clientResults['failed']++;
        $clientResults['errors'][]=['ref_type'=>(string)$row['ref_type'],'ref_id'=>(int)$row['ref_id'],'client_id'=>(int)$row['client_id'],'http_code'=>$code,'curl_error'=>$error,'response'=>substr(trim((string)$body),0,300)];
    }
}

$clientResults['diagnostics'] = ['too_new' => null, 'already_read' => null, 'last_from_client' => null, 'eligible' => null];
if ($wantsDebug) {
    $clientDiagnosticRows = $db->run(
        "SELECT
            SUM(CASE WHEN m.sender_type IN ('seller','admin') AND m.seen_by_client=0 AND m.created_at>DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) THEN 1 ELSE 0 END) AS too_new,
            SUM(CASE WHEN m.sender_type IN ('seller','admin') AND m.seen_by_client=1 THEN 1 ELSE 0 END) AS already_read,
            SUM(CASE WHEN m.sender_type='client' THEN 1 ELSE 0 END) AS last_from_client,
            SUM(CASE WHEN m.sender_type IN ('seller','admin') AND m.seen_by_client=0 AND m.created_at<=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) THEN 1 ELSE 0 END) AS eligible
           FROM legacy_chat_threads t
           INNER JOIN legacy_chat_messages m ON m.id=(SELECT lm.id FROM legacy_chat_messages lm WHERE lm.thread_id=t.id AND lm.deleted=0 ORDER BY lm.created_at DESC,lm.id DESC LIMIT 1)
          WHERE t.chat_type IN ('account','item_purchase','topup_purchase') AND t.seller_id>0 AND t.client_id>0
            AND m.created_at>=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 24 HOUR)"
    ) ?: [];
    $clientResults['diagnostics'] = [
        'too_new'=>(int)($clientDiagnosticRows[0]['too_new']??0),
        'already_read'=>(int)($clientDiagnosticRows[0]['already_read']??0),
        'last_from_client'=>(int)($clientDiagnosticRows[0]['last_from_client']??0),
        'eligible'=>(int)($clientDiagnosticRows[0]['eligible']??0),
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'seller_notifications'=>$results + ['diagnostics'=>$diagnostics], 'client_notifications'=>$clientResults], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
