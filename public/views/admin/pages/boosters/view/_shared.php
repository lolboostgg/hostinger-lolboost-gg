<?php
$boosterId    = (int)($data['id'] ?? 0);
$boosterBaseUrl = ADMN_URL . '/booster/' . $boosterId;
$username     = htmlspecialchars((string)($data['username'] ?? ''), ENT_QUOTES);
$displayName  = htmlspecialchars((string)($data['name'] ?? ''), ENT_QUOTES);
$email        = htmlspecialchars((string)($data['email'] ?? ''), ENT_QUOTES);
$discord      = htmlspecialchars((string)($data['discord'] ?? ''), ENT_QUOTES);
$balanceEuro  = number_format(((int)($data['balance'] ?? 0)) / 100, 2);
$isBanned     = (int)($data['is_banned'] ?? 0) === 1;
$isVerified   = (int)($data['verified'] ?? 0) === 1;
$gamesRaw     = $data['games'] ?? [];
if (is_string($gamesRaw)) {
    $gamesList = array_values(array_filter(array_map('trim', preg_split('/[,|]+/', strtolower($gamesRaw)))));
} elseif (is_array($gamesRaw)) {
    $gamesList = array_values(array_filter(array_map(fn($g) => strtolower(trim((string)$g)), $gamesRaw)));
} else {
    $gamesList = [];
}
$gameLabels = ['lol' => 'LoL', 'tft' => 'TFT', 'val' => 'Valorant', 'valorant' => 'Valorant'];
$gameIcons = [
    'lol' => ASSET_URL . '/website/images/icons/league-of-legends.png',
    'tft' => ASSET_URL . '/website/images/icons/teamfight-tactics.png',
    'val' => ASSET_URL . '/website/images/icons/valorant.png',
    'valorant' => ASSET_URL . '/website/images/icons/valorant.png',
];
$gameItems = [];
$seenGames = [];
foreach ($gamesList as $game) {
    $slug = strtolower(trim((string)$game));
    if ($slug === '') {
        continue;
    }
    $canonical = $slug === 'valorant' ? 'val' : $slug;
    if (isset($seenGames[$canonical])) {
        continue;
    }
    $seenGames[$canonical] = true;
    // Any game beyond lol/tft/val (dynamically added via the admin Games area) falls back
    // to its proper name/icon from the games table instead of an uppercased raw slug.
    $label = $gameLabels[$slug] ?? $gameLabels[$canonical]
        ?? (function_exists('util_game_display_name') ? util_game_display_name($canonical) : strtoupper($canonical));
    $icon = $gameIcons[$slug] ?? $gameIcons[$canonical]
        ?? (function_exists('util_game_icon_url') ? util_game_icon_url($canonical) : '');
    $gameItems[] = [
        'slug' => $canonical,
        'label' => $label,
        'icon' => $icon,
    ];
}
$gameNames = array_column($gameItems, 'label');
$gameCount = count($gameItems);

if (!function_exists('admin_booster_count_key_from_data')) {
    function admin_booster_count_key_from_data(array $data, array $countKeys): ?int
    {
        foreach ($countKeys as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                return max(0, (int)$data[$key]);
            }
        }

        return null;
    }
}

if (!function_exists('admin_booster_count_from_db')) {
    function admin_booster_count_from_db(array $candidates, int $boosterId): ?int
    {
        if ($boosterId <= 0) {
            return null;
        }

        $dbObj = $GLOBALS['db'] ?? null;
        if (!$dbObj || !method_exists($dbObj, 'row')) {
            return null;
        }

        foreach ($candidates as $candidate) {
            $table = $candidate[0] ?? '';
            $column = $candidate[1] ?? 'booster_id';

            // Only allow hard-coded table/column names from the candidate list.
            if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
                continue;
            }

            try {
                $row = $dbObj->row("SELECT COUNT(*) AS total_count FROM `{$table}` WHERE `{$column}`=?", $boosterId);
                if (is_array($row) && isset($row['total_count'])) {
                    return max(0, (int)$row['total_count']);
                }
                if (is_object($row) && isset($row->total_count)) {
                    return max(0, (int)$row->total_count);
                }
            } catch (Throwable $e) {
                // Try the next known table name. This keeps the header working across installs.
                continue;
            }
        }

        return null;
    }
}

if (!function_exists('admin_booster_metric_count')) {
    function admin_booster_metric_count(array $data, int $boosterId, array $countKeys, string $listKey, array $dbCandidates = []): int
    {
        // 1) Prefer explicit *_count values if the controller already provides them.
        $fromCountKey = admin_booster_count_key_from_data($data, $countKeys);
        if ($fromCountKey !== null) {
            return $fromCountKey;
        }

        // 2) Otherwise query a lightweight COUNT(*) so the compact header is correct
        //    on every tab, even when that tab's full data list was not loaded.
        $fromDb = admin_booster_count_from_db($dbCandidates, $boosterId);
        if ($fromDb !== null) {
            return $fromDb;
        }

        // 3) Last fallback: use loaded rows if no DB helper is available in this view.
        if (isset($data[$listKey]) && is_array($data[$listKey])) {
            return count($data[$listKey]);
        }

        return 0;
    }
}

if (!function_exists('admin_booster_order_status_count_from_rows')) {
    function admin_booster_order_status_count_from_rows(array $data, array $statuses): int
    {
        if (!isset($data['orders']) || !is_array($data['orders'])) {
            return 0;
        }

        $needles = array_map(fn($status) => strtolower(trim((string)$status)), $statuses);
        $count = 0;
        foreach ($data['orders'] as $order) {
            if (!is_array($order) && !is_object($order)) {
                continue;
            }
            $row = (array)$order;
            $status = strtolower(trim((string)($row['status'] ?? ($row['order_status'] ?? ($row['state'] ?? '')))));
            $status = str_replace(['-', ' '], '_', $status);
            if ($status !== '' && in_array($status, $needles, true)) {
                $count++;
            }
        }

        return $count;
    }
}

if (!function_exists('admin_booster_order_status_count_from_db')) {
    function admin_booster_order_status_count_from_db(int $boosterId, array $statuses): ?int
    {
        if ($boosterId <= 0 || !$statuses) {
            return null;
        }

        $dbObj = $GLOBALS['db'] ?? null;
        if (!$dbObj || !method_exists($dbObj, 'row')) {
            return null;
        }

        $normalized = array_values(array_unique(array_map(fn($status) => strtolower(trim((string)$status)), $statuses)));
        $placeholders = implode(',', array_fill(0, count($normalized), '?'));

        // Count the order status, not the membership status. Multi-booster
        // members are linked through order_boosters while the primary booster
        // remains available through orders.booster_id.
        try {
            $sql = "SELECT COUNT(DISTINCT o.id) AS total_count
                      FROM orders o
                     WHERE (
                            o.booster_id = ?
                            OR EXISTS (
                                SELECT 1
                                  FROM order_boosters ob
                                 WHERE ob.order_id = o.id
                                   AND ob.booster_id = ?
                                   AND ob.status = 'ACTIVE'
                            )
                     )
                       AND LOWER(REPLACE(REPLACE(o.status, '-', '_'), ' ', '_')) IN ({$placeholders})";
            $row = $dbObj->row($sql, ...array_merge([$boosterId, $boosterId], $normalized));
            if (is_array($row) && isset($row['total_count'])) {
                return max(0, (int)$row['total_count']);
            }
            if (is_object($row) && isset($row->total_count)) {
                return max(0, (int)$row->total_count);
            }
        } catch (Throwable $e) {
            // Fall back to legacy schemas below.
        }

        $tables = [
            ['orders', 'booster_id', 'status'],
            ['orders', 'booster', 'status'],
        ];
        foreach ($tables as $candidate) {
            [$table, $boosterColumn, $statusColumn] = $candidate;
            if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $boosterColumn) || !preg_match('/^[A-Za-z0-9_]+$/', $statusColumn)) {
                continue;
            }

            try {
                $sql = "SELECT COUNT(*) AS total_count FROM `{$table}` WHERE `{$boosterColumn}`=? AND LOWER(REPLACE(REPLACE(`{$statusColumn}`, '-', '_'), ' ', '_')) IN ({$placeholders})";
                $row = $dbObj->row($sql, ...array_merge([$boosterId], $normalized));
                if (is_array($row) && isset($row['total_count'])) {
                    return max(0, (int)$row['total_count']);
                }
                if (is_object($row) && isset($row->total_count)) {
                    return max(0, (int)$row->total_count);
                }
            } catch (Throwable $e) {
                continue;
            }
        }

        return null;
    }
}

if (!function_exists('admin_booster_order_status_count')) {
    function admin_booster_order_status_count(array $data, int $boosterId, array $dataKeys, array $statuses): int
    {
        $fromData = admin_booster_count_key_from_data($data, $dataKeys);
        if ($fromData !== null) {
            return $fromData;
        }

        $fromDb = admin_booster_order_status_count_from_db($boosterId, $statuses);
        if ($fromDb !== null) {
            return $fromDb;
        }

        return admin_booster_order_status_count_from_rows($data, $statuses);
    }
}

if (!function_exists('admin_booster_money_from_data')) {
    function admin_booster_money_from_data(array $data, array $keys): ?int
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                return (int)$data[$key];
            }
        }

        return null;
    }
}

if (!function_exists('admin_booster_money_sum_from_db')) {
    function admin_booster_money_sum_from_db(array $candidates, int $boosterId): ?int
    {
        if ($boosterId <= 0) {
            return null;
        }

        $dbObj = $GLOBALS['db'] ?? null;
        if (!$dbObj || !method_exists($dbObj, 'row')) {
            return null;
        }

        foreach ($candidates as $candidate) {
            $table = $candidate[0] ?? '';
            $boosterColumn = $candidate[1] ?? 'booster_id';
            $amountColumn = $candidate[2] ?? 'amount';
            $whereExtra = $candidate[3] ?? '';

            if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $boosterColumn) || !preg_match('/^[A-Za-z0-9_]+$/', $amountColumn)) {
                continue;
            }

            if ($whereExtra !== '' && !preg_match('/^[A-Za-z0-9_ .`=<>!\'"-]+$/', $whereExtra)) {
                continue;
            }

            try {
                $sql = "SELECT COALESCE(SUM(`{$amountColumn}`), 0) AS total_amount FROM `{$table}` WHERE `{$boosterColumn}`=?";
                if ($whereExtra !== '') {
                    $sql .= ' AND ' . $whereExtra;
                }
                $row = $dbObj->row($sql, $boosterId);
                if (is_array($row) && isset($row['total_amount'])) {
                    return (int)$row['total_amount'];
                }
                if (is_object($row) && isset($row->total_amount)) {
                    return (int)$row->total_amount;
                }
            } catch (Throwable $e) {
                continue;
            }
        }

        return null;
    }
}

if (!function_exists('admin_booster_payment_kpis_from_db')) {
    function admin_booster_payment_kpis_from_db(int $boosterId): ?array
    {
        if ($boosterId <= 0) {
            return null;
        }

        $dbObj = $GLOBALS['db'] ?? null;
        if (!$dbObj) {
            return null;
        }

        $fetchRows = function (string $sql) use ($dbObj, $boosterId): ?array {
            try {
                if (method_exists($dbObj, 'run')) {
                    $rows = $dbObj->run($sql, $boosterId);
                    return is_array($rows) ? $rows : null;
                }

                if (method_exists($dbObj, 'row')) {
                    $row = $dbObj->row($sql, $boosterId);
                    if (is_object($row)) {
                        $row = (array)$row;
                    }
                    return is_array($row) ? [$row] : null;
                }
            } catch (Throwable $e) {
                return null;
            }

            return null;
        };

        $lifetimeRows = $fetchRows(
            "SELECT
                COALESCE(SUM(CASE WHEN currency='EUR' AND amount > 0 THEN amount ELSE 0 END), 0) AS eur_total,
                COALESCE(SUM(CASE WHEN currency='USD' AND amount > 0 THEN amount ELSE 0 END), 0) AS usd_total
             FROM booster_payments
             WHERE booster_id = ? AND type <> 'client_tip'"
        );

        $tipsRows = $fetchRows(
            "SELECT
                COALESCE(SUM(CASE WHEN currency='EUR' THEN amount ELSE 0 END), 0) AS eur_total,
                COALESCE(SUM(CASE WHEN currency='USD' THEN amount ELSE 0 END), 0) AS usd_total
             FROM booster_payments
             WHERE booster_id = ? AND type IN ('tip','client_tip') AND amount > 0"
        );

        $finesRows = $fetchRows(
            "SELECT
                COALESCE(SUM(CASE WHEN currency='EUR' THEN ABS(amount) ELSE 0 END), 0) AS eur_total,
                COALESCE(SUM(CASE WHEN currency='USD' THEN ABS(amount) ELSE 0 END), 0) AS usd_total
             FROM booster_payments
             WHERE booster_id = ?
               AND (type = 'fine' OR type = 'progress_payment_fine' OR type LIKE '%fine%')
               AND amount < 0"
        );

        if ($lifetimeRows === null && $tipsRows === null && $finesRows === null) {
            return null;
        }

        $xr = function_exists('get_exchange_rate') ? (float)get_exchange_rate() : 1.0;
        if ($xr <= 0) {
            $xr = 1.0;
        }

        $toEurCents = function (?array $rows) use ($xr): int {
            $row = is_array($rows) ? ($rows[0] ?? []) : [];
            if (is_object($row)) {
                $row = (array)$row;
            }

            $eur = (int)($row['eur_total'] ?? 0);
            $usd = (int)($row['usd_total'] ?? 0);
            return (int)round($eur + ($usd / $xr));
        };

        return [
            'total_earned' => max(0, $toEurCents($lifetimeRows)),
            'tips' => max(0, $toEurCents($tipsRows)),
            'fines' => max(0, $toEurCents($finesRows)),
        ];
    }
}

if (!function_exists('admin_booster_total_earned')) {
    function admin_booster_total_earned(array $data, int $boosterId): int
    {
        $fromDb = admin_booster_payment_kpis_from_db($boosterId);
        if (is_array($fromDb) && isset($fromDb['total_earned'])) {
            return max(0, (int)$fromDb['total_earned']);
        }

        $fromData = admin_booster_money_from_data($data, ['total_earned', 'earned_total', 'total_earned_amount', 'total_payments_amount']);
        if ($fromData !== null) {
            return max(0, $fromData);
        }

        $fromDbFallback = admin_booster_money_sum_from_db([
            ['booster_payments', 'booster_id', 'amount', "`amount` > 0 AND `type` <> 'client_tip'"],
            ['payments', 'booster_id', 'amount', '`amount` > 0'],
        ], $boosterId);
        if ($fromDbFallback !== null) {
            return max(0, $fromDbFallback);
        }

        $sum = 0;
        if (isset($data['payments']) && is_array($data['payments'])) {
            foreach ($data['payments'] as $payment) {
                $amount = (int)($payment['amount'] ?? 0);
                $type = strtolower(trim((string)($payment['type'] ?? '')));
                if ($amount > 0 && $type !== 'client_tip') {
                    $sum += $amount;
                }
            }
        }

        return max(0, $sum);
    }
}

if (!function_exists('admin_booster_money_from_rows_by_keywords')) {
    function admin_booster_money_from_rows_by_keywords(array $data, array $keywords, bool $negativeAmounts = false): int
    {
        if (!isset($data['payments']) || !is_array($data['payments'])) {
            return 0;
        }

        $sum = 0;
        foreach ($data['payments'] as $payment) {
            if (!is_array($payment) && !is_object($payment)) {
                continue;
            }
            $row = (array)$payment;
            $amount = (int)($row['amount'] ?? ($row['value'] ?? ($row['price'] ?? 0)));
            $haystack = strtolower(trim(implode(' ', array_filter([
                $row['type'] ?? '',
                $row['category'] ?? '',
                $row['reason'] ?? '',
                $row['description'] ?? '',
                $row['title'] ?? '',
            ]))));

            $matched = false;
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && strpos($haystack, strtolower($keyword)) !== false) {
                    $matched = true;
                    break;
                }
            }

            if ($matched || ($negativeAmounts && $amount < 0)) {
                $sum += abs($amount);
            }
        }

        return max(0, $sum);
    }
}

if (!function_exists('admin_booster_money_metric')) {
    function admin_booster_money_metric(array $data, array $keys, array $keywords = [], bool $negativeAmounts = false): int
    {
        $fromData = admin_booster_money_from_data($data, $keys);
        if ($fromData !== null) {
            return abs($fromData);
        }

        return admin_booster_money_from_rows_by_keywords($data, $keywords, $negativeAmounts);
    }
}

$orderCount = admin_booster_metric_count($data, $boosterId, ['orders_count', 'order_count', 'accounts_count', 'account_count'], 'orders', [
    ['orders', 'booster_id'],
    ['orders', 'booster'],
    ['order_boosters', 'booster_id'],
]);
try {
    $orderCountDb = $GLOBALS['db'] ?? null;
    $orderCountRow = $orderCountDb && method_exists($orderCountDb, 'row') ? $orderCountDb->row(
        "SELECT COUNT(DISTINCT o.id) AS total_count
           FROM orders o
          WHERE o.booster_id = ?
             OR EXISTS (
                SELECT 1
                  FROM order_boosters ob
                 WHERE ob.order_id = o.id
                   AND ob.booster_id = ?
                   AND ob.status = 'ACTIVE'
             )",
        $boosterId,
        $boosterId
    ) : null;
    if (is_array($orderCountRow) && isset($orderCountRow['total_count'])) {
        $orderCount = max(0, (int)$orderCountRow['total_count']);
    } elseif (is_object($orderCountRow) && isset($orderCountRow->total_count)) {
        $orderCount = max(0, (int)$orderCountRow->total_count);
    }
} catch (Throwable $e) {
    // Keep the legacy count when the membership table is unavailable.
}
$paymentCount = admin_booster_metric_count($data, $boosterId, ['payments_count', 'payment_count'], 'payments', [
    ['booster_payments', 'booster_id'],
    ['payments', 'booster_id'],
]);
$methodsCount = admin_booster_metric_count($data, $boosterId, ['payout_methods_count', 'payout_method_count', 'methods_count'], 'payout_methods', [
    ['booster_payment_methods', 'booster_id'],
    ['payout_methods', 'booster_id'],
]);
$payoutCount = admin_booster_metric_count($data, $boosterId, ['payouts_count', 'payout_count', 'payout_requests_count'], 'payouts', [
    ['booster_payout_requests', 'booster_id'],
    ['payout_requests', 'booster_id'],
]);
$reviewCount = admin_booster_metric_count($data, $boosterId, ['reviews_count', 'review_count'], 'reviews', [
    ['booster_reviews', 'booster_id'],
    ['reviews', 'booster_id'],
]);
$accountCount = admin_booster_metric_count($data, $boosterId, ['accounts_count', 'account_count'], 'accounts', [
    ['booster_accounts', 'booster_id'],
    ['accounts', 'booster_id'],
]);

$completedOrderCount = admin_booster_order_status_count($data, $boosterId, ['completed_orders_count', 'orders_completed_count', 'completed_count'], ['completed', 'complete', 'done', 'finished']);
$inProgressOrderCount = admin_booster_order_status_count($data, $boosterId, ['in_progress_orders_count', 'orders_in_progress_count', 'progress_orders_count', 'active_orders_count'], ['in_progress', 'progress', 'active', 'processing', 'ongoing', 'accepted']);
$refundedOrderCount = admin_booster_order_status_count($data, $boosterId, ['refunded_orders_count', 'orders_refunded_count', 'refunded_count'], ['refunded', 'refund']);

$insuranceRequired = (int)($data['insurance_required_amount'] ?? ($data['insurance_required_override'] ?? 2500));
$balanceCents = (int)($data['balance'] ?? 0);
$availableFromData = admin_booster_money_from_data($data, ['available_balance', 'available_amount', 'available', 'withdrawable_balance', 'withdrawable_amount', 'withdrawable']);
$availableCents = $availableFromData !== null ? max(0, min($balanceCents, $availableFromData)) : max($balanceCents - $insuranceRequired, 0);
$insuranceCents = max($balanceCents - $availableCents, 0);
$availableEuro = number_format($availableCents / 100, 2);
$insuranceEuro = number_format($insuranceCents / 100, 2);
$paymentKpis = admin_booster_payment_kpis_from_db($boosterId) ?: [];
$totalEarnedCents = isset($paymentKpis['total_earned']) ? (int)$paymentKpis['total_earned'] : admin_booster_total_earned($data, $boosterId);
$totalEarnedEuro = number_format($totalEarnedCents / 100, 2);
$finesCents = isset($paymentKpis['fines']) ? (int)$paymentKpis['fines'] : admin_booster_money_metric($data, ['fines_total', 'total_fines', 'fine_total', 'fines_amount'], ['fine', 'penalty', 'fines'], true);
$tipsCents = isset($paymentKpis['tips']) ? (int)$paymentKpis['tips'] : admin_booster_money_metric($data, ['tips_total', 'total_tips', 'tip_total', 'tips_amount'], ['tip', 'tips'], false);
$finesEuro = number_format(max(0, $finesCents) / 100, 2);
$tipsEuro = number_format(max(0, $tipsCents) / 100, 2);
$avatarLetters = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($data['username'] ?? 'B')) ?: 'B', 0, 1));
$avatarRaw = trim((string)($data['icon'] ?? ''));
$avatarSrc = '';
if ($avatarRaw !== '') {
    $avatarSrc = preg_match('~^https?://~i', $avatarRaw) ? $avatarRaw : rtrim(SITE_URL ?? '', '/') . '/' . ltrim($avatarRaw, '/');
}
$bannerRaw = trim((string)($data['banner'] ?? ($data['cover'] ?? '')));
$bannerSrc = '';
if ($bannerRaw !== '') {
    $bannerSrc = preg_match('~^https?://~i', $bannerRaw) ? $bannerRaw : rtrim(SITE_URL ?? '', '/') . '/' . ltrim($bannerRaw, '/');
}
$rankName = trim((string)($data['name'] ?? ''));
$rankConfigs = [
    'challenger'  => ['color' => '#ffd700', 'bg' => 'rgba(255,215,0,.12)', 'border' => 'rgba(255,215,0,.28)', 'icon' => 'fa-crown'],
    'grandmaster' => ['color' => '#ed4c78', 'bg' => 'rgba(237,76,120,.13)', 'border' => 'rgba(237,76,120,.28)', 'icon' => 'fa-fire'],
    'master'      => ['color' => '#c09bff', 'bg' => 'rgba(180,120,255,.12)', 'border' => 'rgba(180,120,255,.28)', 'icon' => 'fa-gem'],
    'mythic'      => ['color' => '#9b8bf0', 'bg' => 'rgba(92,74,227,.13)', 'border' => 'rgba(92,74,227,.30)', 'icon' => 'fa-bolt'],
    'diamond'     => ['color' => '#55aaff', 'bg' => 'rgba(85,170,255,.12)', 'border' => 'rgba(85,170,255,.28)', 'icon' => 'fa-gem'],
    'emerald'     => ['color' => '#00c9a7', 'bg' => 'rgba(0,201,167,.12)', 'border' => 'rgba(0,201,167,.28)', 'icon' => 'fa-shield-check'],
    'platinum'    => ['color' => '#09a5be', 'bg' => 'rgba(9,165,190,.12)', 'border' => 'rgba(9,165,190,.28)', 'icon' => 'fa-medal'],
    'gold'        => ['color' => '#f5ca99', 'bg' => 'rgba(245,202,153,.12)', 'border' => 'rgba(245,202,153,.28)', 'icon' => 'fa-star'],
    'silver'      => ['color' => '#96aabe', 'bg' => 'rgba(150,170,190,.10)', 'border' => 'rgba(150,170,190,.25)', 'icon' => 'fa-medal'],
    'bronze'      => ['color' => '#c07840', 'bg' => 'rgba(180,110,60,.12)', 'border' => 'rgba(180,110,60,.25)', 'icon' => 'fa-medal'],
    'iron'        => ['color' => '#909090', 'bg' => 'rgba(120,120,120,.10)', 'border' => 'rgba(120,120,120,.25)', 'icon' => 'fa-seedling'],
];
$rankCfg = $rankConfigs[strtolower($rankName)] ?? ['color' => '#8c98a4', 'bg' => 'rgba(109,116,123,.13)', 'border' => 'rgba(109,116,123,.30)', 'icon' => 'fa-user-shield'];
if ($isBanned) {
    $statusLabel = 'Banned';   $statusClass = 'bg-soft-danger text-danger';   $statusIcon = 'fa-ban';
} elseif (!$isVerified) {
    $statusLabel = 'Unverified'; $statusClass = 'bg-soft-warning text-warning'; $statusIcon = 'fa-clock';
} else {
    $statusLabel = 'Active';   $statusClass = 'bg-soft-success text-success'; $statusIcon = 'fa-circle-check';
}

if (!function_exists('admin_booster_normalize_admin_icon')) {
    function admin_booster_normalize_admin_icon($icon): string
    {
        $icon = trim((string)$icon);
        $base = defined('ICON_URL') ? rtrim(ICON_URL, '/') : 'https://lolboost.gg/public/uploads/icons';

        if ($icon === '' || strtolower($icon) === 'null') {
            return $base . '/default.png';
        }

        if (preg_match('~^https?://~i', $icon)) {
            return $icon;
        }

        return $base . '/' . ltrim($icon, '/');
    }
}

if (!function_exists('admin_booster_admin_from_id')) {
    function admin_booster_admin_from_id(int $adminId): array
    {
        if ($adminId <= 0) {
            return ['id' => 0, 'label' => '', 'icon' => admin_booster_normalize_admin_icon('')];
        }

        $dbObj = $GLOBALS['db'] ?? null;
        if ($dbObj && method_exists($dbObj, 'row')) {
            try {
                $row = $dbObj->row('SELECT id, username, email, icon FROM admins WHERE id = ? LIMIT 1', $adminId);
                if (is_object($row)) {
                    $row = (array)$row;
                }
                if (is_array($row) && $row) {
                    $name = trim((string)($row['username'] ?? ''));
                    if ($name === '') {
                        $name = trim((string)($row['email'] ?? ''));
                    }

                    return [
                        'id' => $adminId,
                        'label' => ($name !== '' ? $name : 'Admin'),
                        'icon' => admin_booster_normalize_admin_icon($row['icon'] ?? ''),
                    ];
                }
            } catch (Throwable $e) {
                // Fall back to the numeric admin id below.
            }
        }

        return ['id' => $adminId, 'label' => 'Admin', 'icon' => admin_booster_normalize_admin_icon('')];
    }
}

if (!function_exists('admin_booster_admin_label_from_id')) {
    function admin_booster_admin_label_from_id(int $adminId): string
    {
        $admin = admin_booster_admin_from_id($adminId);
        return (string)($admin['label'] ?? '');
    }
}

if (!function_exists('admin_booster_hired_admin_from_logs')) {
    function admin_booster_hired_admin_from_logs(int $boosterId): ?array
    {
        if ($boosterId <= 0) {
            return null;
        }

        $dbObj = $GLOBALS['db'] ?? null;
        if (!$dbObj || !method_exists($dbObj, 'row')) {
            return null;
        }

        $patterns = [
            'Added verified booster #' . $boosterId,
            'Verified booster #' . $boosterId,
            'Added new booster #' . $boosterId,
            'Added verified egirl #' . $boosterId,
        ];

        $where = implode(' OR ', array_fill(0, count($patterns), 'al.action LIKE ?'));

        try {
            $row = $dbObj->row(
                "SELECT al.admin_id, a.username AS admin_name, a.email AS admin_email, a.icon AS admin_icon, al.created_at
                 FROM admin_logs al
                 LEFT JOIN admins a ON al.admin_id = a.id
                 WHERE al.admin_id > 0 AND ({$where})
                 ORDER BY al.id ASC
                 LIMIT 1",
                ...array_map(fn($pattern) => '%' . $pattern . '%', $patterns)
            );
            if (is_object($row)) {
                $row = (array)$row;
            }
            if (is_array($row) && !empty($row['admin_id'])) {
                $adminId = (int)$row['admin_id'];
                $name = trim((string)($row['admin_name'] ?? ''));
                if ($name === '') {
                    $name = trim((string)($row['admin_email'] ?? ''));
                }

                return [
                    'id' => $adminId,
                    'label' => ($name !== '' ? $name : 'Admin'),
                    'created_at' => trim((string)($row['created_at'] ?? '')),
                    'icon' => admin_booster_normalize_admin_icon($row['admin_icon'] ?? ''),
                ];
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }
}

if (!function_exists('admin_booster_hired_admin')) {
    function admin_booster_hired_admin(array $data, int $boosterId): ?array
    {
        $idKeys = ['hired_by_admin_id', 'hired_admin_id', 'verified_by_admin_id', 'verified_admin_id', 'created_by_admin_id', 'created_admin_id', 'admin_id'];
        foreach ($idKeys as $key) {
            if (!empty($data[$key]) && is_numeric($data[$key])) {
                $adminId = (int)$data[$key];
                if ($adminId > 0) {
                    $nameKeys = ['hired_by_admin_name', 'hired_admin_name', 'verified_by_admin_name', 'verified_admin_name', 'created_by_admin_name', 'admin_name'];
                    $label = '';
                    foreach ($nameKeys as $nameKey) {
                        if (!empty($data[$nameKey])) {
                            $label = trim((string)$data[$nameKey]);
                            break;
                        }
                    }
                    $adminInfo = admin_booster_admin_from_id($adminId);
                    if ($label === '') {
                        $label = (string)($adminInfo['label'] ?? '');
                    }

                    return ['id' => $adminId, 'label' => $label, 'icon' => (string)($adminInfo['icon'] ?? admin_booster_normalize_admin_icon('')), 'created_at' => ''];
                }
            }
        }

        return admin_booster_hired_admin_from_logs($boosterId);
    }
}

$hiredAdmin = admin_booster_hired_admin($data, $boosterId);
$hiredAdminLabel = $hiredAdmin ? htmlspecialchars((string)$hiredAdmin['label'], ENT_QUOTES) : '';
$hiredAdminIcon = $hiredAdmin ? htmlspecialchars((string)($hiredAdmin['icon'] ?? admin_booster_normalize_admin_icon('')), ENT_QUOTES) : '';
$hiredAdminTitle = $hiredAdmin && !empty($hiredAdmin['created_at']) ? ' title="Hired at ' . htmlspecialchars((string)$hiredAdmin['created_at'], ENT_QUOTES) . '"' : '';
$activeTab = $activeTab ?? ($page ?? 'profile');
?>

<style>
/* Booster view shared header: wider, flatter and better aligned with admin cards */
.booster-shared-card {
    position: relative;
    overflow: hidden;
    border: 1px solid #2f3235;
    border-radius: .75rem;
    background: #25282a;
    box-shadow: 0rem 0.375rem 0.75rem rgba(30, 32, 34, 0.2);
}
.booster-profile-banner {
    min-height: 92px;
    position: relative;
    background: linear-gradient(90deg, #202325 0%, #2a2d30 52%, #202325 100%);
}
.booster-profile-banner-glow {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(255,255,255,.045), transparent 68%),
        radial-gradient(circle at 18% 18%, rgba(255,255,255,.08), transparent 18%);
}
.booster-shared-body { padding: 1.15rem 1.25rem 1.25rem; }
.booster-header-main {
    position: relative;
    margin-top: 0;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 1rem;
    align-items: start;
}
.booster-identity {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    min-width: 0;
}
.booster-profile-avatar {
    width: 82px;
    height: 82px;
    border-radius: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: 1.9rem;
    font-weight: 800;
    color: #b4a9ff;
    background: linear-gradient(180deg, #34373a 0%, #202325 100%);
    border: 4px solid #25282a;
    box-shadow: 0 14px 30px rgba(0,0,0,.28);
    overflow: hidden;
}
.booster-profile-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.booster-title-block { min-width: 0; padding-bottom: .15rem; }
.booster-title-line { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.booster-title-line .page-header-title { line-height: 1.1; }
.booster-status-icon {
    width: 1.55rem;
    height: 1.55rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50rem;
    padding: 0;
    line-height: 1;
}
.booster-status-icon i { margin: 0 !important; font-size: .78rem; }
.booster-header-meta {
    margin-top: .5rem;
    display: flex;
    align-items: center;
    gap: .55rem 1rem;
    flex-wrap: wrap;
    color: #9aa3ad;
}
.booster-header-meta span { display: inline-flex; align-items: center; min-width: 0; }
.booster-hired-inline {
    display: inline-flex;
    align-items: center;
    gap: .42rem;
    min-width: 0;
    max-width: 100%;
    padding: .18rem .55rem .18rem .22rem;
    border-radius: 50rem;
    color: #cbd0d6;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.10);
}
.booster-hired-inline__avatar {
    width: 1.35rem;
    height: 1.35rem;
    border-radius: 50rem;
    object-fit: cover;
    flex: 0 0 auto;
    border: 1px solid rgba(245,202,153,.45);
    background: #202325;
}
.booster-hired-inline__label {
    color: #91989e;
    font-weight: 600;
    white-space: nowrap;
}
.booster-hired-inline__value {
    color: #fff;
    font-weight: 700;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.booster-game-badges { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .65rem !important; }
.booster-game-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .24rem .58rem;
    border-radius: 50rem;
    font-size: .72rem;
    font-weight: 700;
    color: #cbd0d6;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.045);
}
.booster-game-badge img { width: 18px; height: 18px; object-fit: contain; display: block; border-radius: 4px; }
.booster-header-actions {
    margin-top: 0;
    display: flex;
    justify-content: flex-end;
}
.booster-stat-pill {
    position: relative;
    overflow: hidden;
    border: 1px solid #2f3235;
    border-radius: .75rem;
    background: #202325;
    padding: .95rem 1rem;
    text-align: left;
    min-height: 148px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.025);
}
.booster-stat-pill:before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(92,74,227,.65), transparent);
    opacity: .85;
}
.booster-stat-pill--balance:before { background: linear-gradient(90deg, transparent, rgba(150,170,190,.75), transparent); }
.booster-stat-pill--orders:before { background: linear-gradient(90deg, transparent, rgba(0,201,167,.78), transparent); }
.booster-stat-pill--payments:before { background: linear-gradient(90deg, transparent, rgba(9,165,190,.78), transparent); }
.booster-stat-pill--earned:before { background: linear-gradient(90deg, transparent, rgba(245,202,153,.78), transparent); }
.booster-stat-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
.booster-stat-icon {
    width: 34px;
    height: 34px;
    border-radius: .85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #c8ccd0;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.075);
}
.booster-stat-value { font-size: 1.22rem; font-weight: 800; line-height: 1.1; color:#e5e7eb; }
.booster-stat-label { font-size: .68rem; letter-spacing: .08em; text-transform: uppercase; color: #8d949b; margin-top: .3rem; font-weight: 700; }
.booster-balance-breakdown { margin-top: .65rem; display: grid; grid-template-columns: 1fr; gap: .45rem; font-size: .74rem; color: #a7adb3; }
.booster-balance-line {
    display: flex;
    flex-direction: column;
    gap: .1rem;
    line-height: 1.15;
    padding: .42rem .5rem;
    border-radius: .65rem;
    background: #1e2022;
    border: 1px solid #2f3235;
}
.booster-balance-line span { font-size: .65rem; text-transform: uppercase; letter-spacing: .06em; color: #8d949b; }
.booster-balance-line strong { color: #d7dadd; font-weight: 800; }
.booster-stat-breakdown { margin-top: .7rem; display: grid; gap: .42rem; }
.booster-stat-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .38rem .5rem;
    border-radius: .55rem;
    background: #1e2022;
    border: 1px solid #2f3235;
    color: #a9afb6;
    font-size: .73rem;
    line-height: 1.15;
}
.booster-stat-line span { display: inline-flex; align-items: center; gap: .35rem; min-width: 0; }
.booster-stat-line strong { color: #fff; font-weight: 800; }
.booster-stat-dot { width: .43rem; height: .43rem; border-radius: 50%; display: inline-block; flex: 0 0 auto; background: #6d747b; }
.booster-stat-dot--success { background: #00c9a7; }
.booster-stat-dot--info { background: #09a5be; }
.booster-stat-dot--warning { background: #f5ca99; }
.booster-stat-dot--danger { background: #ed4c78; }

.booster-dropdown-section { padding: .45rem 1rem .25rem; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #757b82; }
.booster-dropdown-divider { height: 1px; margin: .35rem .75rem; background: rgba(255,255,255,.08); }
.booster-tab-wrap { border-bottom: .0625rem solid #2f3235; margin-bottom: 1.5rem; overflow-x:auto; }
.booster-nav-tabs { gap: .65rem; border-bottom: 0; flex-wrap: nowrap; }
.booster-nav-tabs .nav-link {
    border: 0;
    border-bottom: 2px solid transparent;
    color: #91989e;
    padding: .75rem .2rem;
    border-radius: 0;
    font-weight: 500;
    background: transparent;
    white-space: nowrap;
    transition: color .15s, border-color .15s;
}
.booster-nav-tabs .nav-link:hover { color: #fff; border-bottom-color: rgba(92,74,227,.5); }
.booster-nav-tabs .nav-link.active { color: #fff; border-bottom-color: #5c4ae3; }
.booster-action-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .4rem .78rem; border-radius: .6rem; border: 1px solid transparent;
    font-size: .82rem; font-weight: 600; cursor: pointer;
    transition: background .15s, border-color .15s, color .15s; background: transparent;
}
.booster-action-btn--ghost { border-color: rgba(255,255,255,.12); color: rgba(255,255,255,.72); background: rgba(255,255,255,.045); }
.booster-action-btn--ghost:hover { border-color: rgba(255,255,255,.22); color: #fff; background: rgba(255,255,255,.08); }
.booster-action-btn--success { border-color: rgba(0,201,167,.35); color: #00c9a7; background: rgba(0,201,167,.08); }
.booster-action-btn--success:hover { border-color: rgba(0,201,167,.6); background: rgba(0,201,167,.15); }
.booster-action-btn--danger { border-color: rgba(237,76,120,.30); color: #ed4c78; background: rgba(237,76,120,.07); }
.booster-action-btn--danger:hover { border-color: rgba(237,76,120,.55); background: rgba(237,76,120,.13); }
.booster-action-divider { width: 1px; height: 22px; background: rgba(255,255,255,.10); margin: 0 .15rem; flex-shrink: 0; }
.booster-rank-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .65rem;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 700;
}
.booster-stats-row { margin-top: 1.25rem; }
@media (min-width: 1200px) {
    .booster-stats-row > .col-xl-3 { width: 25%; }
}
@media (max-width: 991.98px) {
    .booster-shared-body { padding: 1rem; }
    .booster-profile-banner { min-height: 82px; }
    .booster-header-main { grid-template-columns: 1fr; margin-top: 0; }
    .booster-header-actions { margin-top: .1rem; justify-content: flex-start; }
    .booster-profile-avatar { width: 74px; height: 74px; font-size: 1.55rem; }
}
@media (max-width: 575.98px) {
    .booster-identity { align-items: center; gap: .75rem; }
    .booster-profile-avatar { width: 62px; height: 62px; border-radius: 1rem; border-width: 3px; }
    .booster-title-line .page-header-title { font-size: 1.1rem; }
    .booster-header-meta { font-size: .75rem; }
    .booster-action-btn span { display: none; }
    .booster-balance-breakdown { grid-template-columns: 1fr; }
}
</style>

<div class="card mb-4 booster-shared-card">
    <div class="booster-profile-banner" <?php if ($bannerSrc): ?>style="background-image:linear-gradient(110deg, rgba(30,32,34,.78), rgba(30,32,34,.28)), url('<?= htmlspecialchars($bannerSrc, ENT_QUOTES) ?>');background-size:cover;background-position:center;"<?php endif; ?>>
        <div class="booster-profile-banner-glow"></div>
    </div>
    <div class="card-body booster-shared-body">
        <div class="booster-header-main">
            <div class="booster-identity">
                <div class="booster-profile-avatar position-relative">
                    <?php if ($avatarSrc): ?>
                        <img src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES) ?>" alt="<?= $username ?>">
                    <?php else: ?>
                        <span><?= $avatarLetters ?></span>
                    <?php endif; ?>
                </div>

                <div class="booster-title-block">
                    <div class="booster-title-line">
                        <h2 class="page-header-title mb-0"><?= $username ?: 'Booster' ?></h2>
                        <?php if ($isBanned || $isVerified): ?>
                            <span class="badge <?= $statusClass ?> booster-status-icon" title="<?= htmlspecialchars($statusLabel, ENT_QUOTES) ?>"><i class="fa-duotone <?= $statusIcon ?>"></i></span>
                        <?php else: ?>
                            <span class="badge <?= $statusClass ?>"><i class="fa-duotone <?= $statusIcon ?> me-1"></i><?= $statusLabel ?></span>
                        <?php endif; ?>
                        <?php if ($rankName !== ''): ?>
                            <span class="booster-rank-badge" style="color:<?= $rankCfg['color'] ?>;background:<?= $rankCfg['bg'] ?>;border:1px solid <?= $rankCfg['border'] ?>;">
                                <i class="fa-duotone <?= $rankCfg['icon'] ?>"></i><?= htmlspecialchars($rankName, ENT_QUOTES) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="small booster-header-meta">
                        <?php if ($email): ?><span><i class="fa-duotone fa-envelope me-1"></i><?= $email ?></span><?php endif; ?>
                        <?php if ($discord): ?><span><i class="fa-brands fa-discord me-1"></i><?= $discord ?></span><?php endif; ?>
                        <?php if ($hiredAdminLabel): ?>
                            <span class="booster-hired-inline"<?= $hiredAdminTitle ?>>
                                <?php if ($hiredAdminIcon): ?><img class="booster-hired-inline__avatar" src="<?= $hiredAdminIcon ?>" alt=""><?php endif; ?>
                                <span class="booster-hired-inline__label">Hired by:</span>
                                <strong class="booster-hired-inline__value"><?= $hiredAdminLabel ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($gameItems): ?>
                        <div class="booster-game-badges">
                            <?php foreach ($gameItems as $game): ?>
                                <span class="booster-game-badge" title="<?= htmlspecialchars($game['label'], ENT_QUOTES) ?>">
                                    <?php if (!empty($game['icon'])): ?>
                                        <img src="<?= htmlspecialchars($game['icon'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($game['label'], ENT_QUOTES) ?>">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($game['label'], ENT_QUOTES) ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="booster-header-actions">
                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                    <?php if (!$isVerified): ?>
                        <button type="button" class="booster-action-btn booster-action-btn--success" data-id="<?= $boosterId ?>" data-action="admin_verify_booster">
                            <i class="fa-duotone fa-circle-check"></i><span>Verify & Activate</span>
                        </button>
                        <button type="button" class="booster-action-btn booster-action-btn--danger" data-bs-toggle="modal" data-bs-target="#confirmationModal">
                            <i class="fa-duotone fa-ban"></i><span>Decline</span>
                        </button>
                    <?php else: ?>
                        <div class="dropdown">
                            <button type="button" class="booster-action-btn booster-action-btn--ghost" id="boosterActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-duotone fa-ellipsis-vertical"></i><span>Actions</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="boosterActionsDropdown">
                                <div class="booster-dropdown-section">Balance Actions</div>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#add_money_md">
                                    <i class="fa-duotone fa-wallet dropdown-item-icon"></i> Balance
                                </a>
                                <?php if (in_array(strtolower(ADMIN_DATA['email']), $allowedEmails ?? [])): ?>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#withdraw_balance_md">
                                        <i class="fa-duotone fa-money-simple-from-bracket dropdown-item-icon"></i> Withdraw Balance
                                    </a>
                                <?php endif; ?>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#fine_booster_md">
                                    <i class="fa-duotone fa-triangle-exclamation dropdown-item-icon"></i> Fine Booster
                                </a>
                                <div class="booster-dropdown-divider"></div>
                                <div class="booster-dropdown-section">Danger Zone</div>
                                <?php if (!$isBanned): ?>
                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#ban_booster_md">
                                        <i class="fa-duotone fa-ban dropdown-item-icon"></i> Ban Booster
                                    </a>
                                <?php else: ?>
                                    <a class="dropdown-item text-success" href="#" data-id="<?= $boosterId ?>" data-action="admin_unban_booster">
                                        <i class="fa-duotone fa-circle-check dropdown-item-icon"></i> Unban Booster
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3 booster-stats-row">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="booster-stat-pill booster-stat-pill--balance">
                    <div>
                        <div class="booster-stat-head">
                            <div>
                                <div class="booster-stat-value">€<?= $balanceEuro ?></div>
                                <div class="booster-stat-label">Balance</div>
                            </div>
                            <div class="booster-stat-icon"><i class="fa-duotone fa-wallet"></i></div>
                        </div>
                        <div class="booster-balance-breakdown">
                            <div class="booster-balance-line"><span>Available</span> <strong>€<?= $availableEuro ?></strong></div>
                            <div class="booster-balance-line"><span>Insurance</span> <strong>€<?= $insuranceEuro ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-6 col-xl-3">
                <div class="booster-stat-pill booster-stat-pill--orders">
                    <div>
                        <div class="booster-stat-head">
                            <div>
                                <div class="booster-stat-value text-success"><?= $orderCount ?></div>
                                <div class="booster-stat-label">Orders</div>
                            </div>
                            <div class="booster-stat-icon"><i class="fa-duotone fa-bag-shopping"></i></div>
                        </div>
                        <div class="booster-stat-breakdown">
                            <div class="booster-stat-line"><span><i class="booster-stat-dot booster-stat-dot--success"></i>Completed</span><strong><?= $completedOrderCount ?></strong></div>
                            <div class="booster-stat-line"><span><i class="booster-stat-dot booster-stat-dot--info"></i>In Progress</span><strong><?= $inProgressOrderCount ?></strong></div>
                            <div class="booster-stat-line"><span><i class="booster-stat-dot booster-stat-dot--warning"></i>Refunded</span><strong><?= $refundedOrderCount ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-6 col-xl-3">
                <div class="booster-stat-pill booster-stat-pill--payments">
                    <div>
                        <div class="booster-stat-head">
                            <div>
                                <div class="booster-stat-value text-info"><?= $paymentCount ?></div>
                                <div class="booster-stat-label">Payments</div>
                            </div>
                            <div class="booster-stat-icon"><i class="fa-duotone fa-receipt"></i></div>
                        </div>
                        <div class="booster-stat-breakdown">
                            <div class="booster-stat-line"><span><i class="booster-stat-dot booster-stat-dot--info"></i>Payouts</span><strong><?= $payoutCount ?></strong></div>
                            <div class="booster-stat-line"><span><i class="booster-stat-dot"></i>Methods</span><strong><?= $methodsCount ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-6 col-xl-3">
                <div class="booster-stat-pill booster-stat-pill--earned">
                    <div>
                        <div class="booster-stat-head">
                            <div>
                                <div class="booster-stat-value text-warning">€<?= $totalEarnedEuro ?></div>
                                <div class="booster-stat-label">Total Earned</div>
                            </div>
                            <div class="booster-stat-icon"><i class="fa-duotone fa-chart-line-up"></i></div>
                        </div>
                        <div class="booster-stat-breakdown">
                            <div class="booster-stat-line"><span><i class="booster-stat-dot booster-stat-dot--danger"></i>Fines</span><strong>€<?= $finesEuro ?></strong></div>
                            <div class="booster-stat-line"><span><i class="booster-stat-dot booster-stat-dot--success"></i>Tips</span><strong>€<?= $tipsEuro ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="booster-tab-wrap">
    <ul class="nav booster-nav-tabs">
        <li class="nav-item"><a class="nav-link <?= $activeTab === 'profile' ? 'active' : '' ?>" href="<?= $boosterBaseUrl ?>/profile">Profile</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>" href="<?= $boosterBaseUrl ?>/orders">Orders <span class="badge bg-soft-secondary text-secondary ms-1"><?= $orderCount ?></span></a></li>
        <li class="nav-item"><a class="nav-link <?= $activeTab === 'payments' ? 'active' : '' ?>" href="<?= $boosterBaseUrl ?>/payments">Payments <span class="badge bg-soft-secondary text-secondary ms-1"><?= $paymentCount ?></span></a></li>
        <li class="nav-item"><a class="nav-link <?= $activeTab === 'performance' ? 'active' : '' ?>" href="<?= $boosterBaseUrl ?>/performance">Performance</a></li>
        <?php if (in_array(strtolower(ADMIN_DATA['email']), $allowedEmails ?? [])): ?>
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'personal-details' ? 'active' : '' ?>" href="<?= $boosterBaseUrl ?>/personal-details">Personal Details</a></li>
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'payout-methods' ? 'active' : '' ?>" href="<?= $boosterBaseUrl ?>/payout-methods">Payout Methods <span class="badge bg-soft-secondary text-secondary ms-1"><?= $methodsCount ?></span></a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to decline this booster request? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmButton" data-id="<?= $boosterId ?>" data-action="admin_decline_booster">Confirm</button>
      </div>
    </div>
  </div>
</div>
