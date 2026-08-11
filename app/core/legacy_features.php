<?php
if (!defined('RANKED_5S_FORM_ID')) {
    define('RANKED_5S_FORM_ID', 29);
}
if (!defined('CLASH_BOOST_FORM_ID')) {
    define('CLASH_BOOST_FORM_ID', 19);
}
if (!defined('NORMAL_MATCHES_FORM_ID')) {
    define('NORMAL_MATCHES_FORM_ID', 4);
}
/**
 * Merged legacy features from lolboost.gg
 * - LoL Classic (util_is_lol_classic, pricing, ranks, champions)
 * - Ranked 5s (multi-booster roles/slots, earning split)
 * Ported 1:1 from the old codebase, wrapped in function_exists() guards.
 */

if (!function_exists('calculate_lol_classic_match_pricing')) {
function calculate_lol_classic_match_pricing($data, $region, $tier, $division, $matches)
{
    return calculate_lol_classic_win_boost_match_pricing($data, $region, $tier, $division, $matches, 0);
}
}

if (!function_exists('calculate_lol_classic_rank_pricing')) {
function calculate_lol_classic_rank_pricing($data, $region, $start_tier, $start_div, $end_tier, $end_div, $lp_discount, $start_lp = 0, $end_lp = 0)
{
    $price = 0;
    $completion_time = $data['completion_time'];
    $idx = 0;
    $division_count = lb_lol_classic_division_count($data);
    $apex_from = lb_lol_classic_apex_from($data);

    if ($start_tier >= $apex_from && $end_tier >= $apex_from) {
        $temp_price = calculate_lp_boost($start_lp, $end_lp, $data, $region);
        $idx += $temp_price[1];
        $price += $temp_price[0];
    } elseif ($end_tier > $start_tier || ($end_tier == $start_tier && $end_div < $start_div)) {
        if ($start_tier == $end_tier) {
            for ($i = $start_div; $i > $end_div; $i--) {
                $price += $data['main'][$start_tier][$i][$region] ?? 0;
                $idx++;
                if ($i == $start_div) {
                    $price -= ($data['main'][$start_tier][$i][$region] ?? 0) * $lp_discount;
                }
            }
        } else {
            for ($i = $start_tier; $i <= $end_tier; $i++) {
                if ($i == $start_tier) {
                    for ($y = $start_div; $y >= 1; $y--) {
                        $price += $data['main'][$i][$y][$region] ?? 0;
                        $idx++;
                        if ($y == $start_div) {
                            $price -= ($data['main'][$i][$y][$region] ?? 0) * $lp_discount;
                        }
                    }
                } elseif ($i == $end_tier && $end_tier < $apex_from) {
                    for ($y = $division_count; $y > $end_div; $y--) {
                        $idx++;
                        $price += $data['main'][$i][$y][$region] ?? 0;
                    }
                } elseif ($i == $end_tier && $end_tier >= $apex_from) {
                    $temp_price = calculate_lp_boost(0, $end_lp, $data, $region);
                    $idx += $temp_price[1];
                    $price += $temp_price[0];
                } else {
                    for ($y = $division_count; $y >= 1; $y--) {
                        $idx++;
                        $price += $data['main'][$i][$y][$region] ?? 0;
                    }
                }
            }
        }
    }
    $completion_time = round($completion_time * $idx);

    return [$price, $completion_time];
}
}

if (!function_exists('calculate_lol_classic_win_boost_match_pricing')) {
function calculate_lol_classic_win_boost_match_pricing($data, $region, $tier, $division, $matches, $lp = 0)
{
    $completion_time = $data['completion_time'];
    $apex_from = lb_lol_classic_apex_from($data);
    if ($tier >= $apex_from) {
        $lpBucket = max(100, min(1500, (int)(ceil(max(0, (int)$lp) / 100) * 100)));
        $tierPrices = $data['main'][$tier] ?? $data['main'][(string)$tier] ?? [];
        $bucket = $tierPrices[$lpBucket]
            ?? $tierPrices[(string)$lpBucket]
            ?? $tierPrices['lt_' . $lpBucket]
            ?? $tierPrices['lt' . $lpBucket]
            ?? $tierPrices['<' . $lpBucket]
            ?? [];
        $pricePerGame = is_array($bucket) ? (float)($bucket[$region] ?? 0) : (float)$bucket;
        $price = $pricePerGame * $matches;
    } else {
        // Unranked placement pricing is stored directly by region, without a division level.
        // Keep the direct-region fallback so tier 0 does not incorrectly return €0.00.
        $price = ($data['main'][$tier][$division][$region] ?? $data['main'][$tier][$region] ?? 0) * $matches;
    }
    return [$price, round($completion_time * $matches)];
}
}

if (!function_exists('lb_is_ranked_5s_order')) {
    function lb_is_ranked_5s_order($order, $form = null): bool
    {
        $formId = is_array($order) ? (int)($order['form_id'] ?? 0) : (int)$order;
        $type = '';
        if (is_array($form)) {
            $type = (string)($form['type'] ?? '');
        } elseif (is_array($order)) {
            $type = (string)($order['type'] ?? '');
        }
        return $formId === RANKED_5S_FORM_ID || $type === 'ranked-5s';
    }
}

if (!function_exists('lb_is_multi_booster_order')) {
    function lb_is_multi_booster_order($order, $form = null): bool
    {
        $formId = is_array($order) ? (int)($order['form_id'] ?? 0) : (int)$order;
        if (lb_is_ranked_5s_order($order, $form)) {
            return true;
        }

        if (!in_array($formId, [NORMAL_MATCHES_FORM_ID, CLASH_BOOST_FORM_ID], true)) {
            return false;
        }

        $orderId = is_array($order) ? (int)($order['id'] ?? $order['order_id'] ?? 0) : 0;
        return $orderId <= 0 || lb_multi_booster_required_count($orderId) > 1;
    }
}

if (!function_exists('lb_multi_booster_required_count')) {
    function lb_multi_booster_required_count(int $order_id): int
    {
        $opts = db_get_row('order_options', ['order_id' => $order_id, 'select' => 'boosters,hours'], 1);
        $count = is_array($opts) ? (int)($opts['boosters'] ?? $opts['hours'] ?? 1) : 1;
        return max(1, min(4, $count));
    }
}

if (!function_exists('lb_multi_booster_has_claim')) {
    function lb_multi_booster_has_claim(int $order_id, int $booster_id): bool
    {
        return lb_ranked_5s_booster_has_claim($order_id, $booster_id);
    }
}

if (!function_exists('lb_multi_booster_claimed_count')) {
    function lb_multi_booster_claimed_count(int $order_id): int
    {
        return lb_ranked_5s_claimed_boosters($order_id);
    }
}

if (!function_exists('lb_lol_classic_apex_from')) {
function lb_lol_classic_apex_from($data)
{
    return (int)($data['apex_from'] ?? 7) ?: 7;
}
}

if (!function_exists('lb_lol_classic_division_count')) {
function lb_lol_classic_division_count($data)
{
    return (int)($data['division_count'] ?? 5) ?: 5;
}
}

if (!function_exists('lb_ranked_5s_available_roles')) {
    function lb_ranked_5s_available_roles(int $order_id, $client_role = null): array
    {
        $all = ['TopLane', 'Jungle', 'MidLane', 'AdCarry', 'Support'];
        $clientRole = lb_ranked_5s_normalize_role($client_role);
        $claimed = lb_ranked_5s_claimed_roles($order_id);
        return array_values(array_filter($all, static function($role) use ($clientRole, $claimed) {
            if ($clientRole !== '' && $role === $clientRole) return false;
            return !in_array($role, $claimed, true);
        }));
    }
}

if (!function_exists('lb_ranked_5s_booster_has_claim')) {
    function lb_ranked_5s_booster_has_claim(int $order_id, int $booster_id): bool
    {
        global $db;
        try {
            $rows = $db->run("SELECT id FROM order_boosters WHERE order_id = ? AND booster_id = ? AND status = 'ACTIVE' LIMIT 1", $order_id, $booster_id);
            return !empty($rows);
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('lb_ranked_5s_claimed_boosters')) {
    function lb_ranked_5s_claimed_boosters(int $order_id): int
    {
        global $db;
        try {
            $rows = $db->run("SELECT COUNT(DISTINCT booster_id) AS total FROM order_boosters WHERE order_id = ? AND status = 'ACTIVE' AND booster_id IS NOT NULL AND booster_id > 0", $order_id);
            return (int)($rows[0]['total'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('lb_ranked_5s_claimed_label')) {
    function lb_ranked_5s_claimed_label(int $order_id): string
    {
        $required = lb_ranked_5s_required_boosters($order_id);
        $claimed = lb_ranked_5s_claimed_boosters($order_id);
        return max(0, min($required, $claimed)) . '/' . $required . ' Boosters';
    }
}

if (!function_exists('lb_ranked_5s_claimed_roles')) {
    function lb_ranked_5s_claimed_roles(int $order_id): array
    {
        global $db;
        $roles = [];
        try {
            $rows = $db->run("SELECT role FROM order_boosters WHERE order_id = ? AND status = 'ACTIVE'", $order_id) ?: [];
            foreach ($rows as $row) {
                $role = lb_ranked_5s_normalize_role($row['role'] ?? '');
                if ($role !== '') $roles[] = $role;
            }
        } catch (Throwable $e) {}
        return array_values(array_unique($roles));
    }
}

if (!function_exists('lb_ranked_5s_earning_divisor')) {
    function lb_ranked_5s_earning_divisor(array $order): int
    {
        $formId = (int)($order['form_id'] ?? 0);
        $type = (string)($order['type'] ?? '');
        if ($formId !== RANKED_5S_FORM_ID && $type !== 'ranked-5s') {
            return 1;
        }

        $boosters = (int)($order['boosters'] ?? 0);

        if ($boosters <= 0) {
            $orderId = (int)($order['id'] ?? $order['order_id'] ?? 0);
            if ($orderId > 0) {
                try {
                    $opts = db_get_row('order_options', [
                        'order_id' => $orderId,
                        'select' => 'boosters,hours'
                    ], 1);
                    if (is_array($opts)) {
                        $boosters = (int)($opts['boosters'] ?? $opts['hours'] ?? 0);
                    }
                } catch (Throwable $e) {}
            }
        }

        return max(1, min(4, $boosters > 0 ? $boosters : 1));
    }
}

if (!function_exists('lb_ranked_5s_normalize_role')) {
    function lb_ranked_5s_normalize_role($role): string
    {
        $role = trim((string)$role);
        if ($role === '') return '';
        $key = strtolower(preg_replace('/[^a-z0-9]+/i', '', $role));
        $map = [
            'top' => 'TopLane', 'toplane' => 'TopLane',
            'jungle' => 'Jungle', 'jg' => 'Jungle',
            'mid' => 'MidLane', 'middle' => 'MidLane', 'midlane' => 'MidLane',
            'adc' => 'AdCarry', 'adcarry' => 'AdCarry', 'bot' => 'AdCarry', 'botlane' => 'AdCarry',
            'support' => 'Support', 'sup' => 'Support',
        ];
        return $map[$key] ?? '';
    }
}

if (!function_exists('lb_ranked_5s_required_boosters')) {
    function lb_ranked_5s_required_boosters(int $order_id): int
    {
        return lb_multi_booster_required_count($order_id);
    }
}

if (!function_exists('lb_ranked_5s_role_label')) {
    function lb_ranked_5s_role_label($role): string
    {
        $norm = function_exists('lb_ranked_5s_normalize_role') ? lb_ranked_5s_normalize_role($role) : (string)$role;
        $labels = ['TopLane' => 'Top', 'Jungle' => 'Jungle', 'MidLane' => 'Mid', 'AdCarry' => 'ADC', 'Support' => 'Support'];
        return $labels[$norm] ?? (string)$role;
    }
}

if (!function_exists('lb_ranked_5s_split_earning_amount')) {
    function lb_ranked_5s_split_earning_amount($order, int $amount_cents): int
    {
        $order = (array)$order;
        $formId = (int)($order['form_id'] ?? 0);
        $type = (string)($order['type'] ?? '');
        if ($formId !== RANKED_5S_FORM_ID && $type !== 'ranked-5s') {
            return $amount_cents;
        }

        $boosters = (int)($order['boosters'] ?? 0);
        if ($boosters <= 0) {
            $orderId = (int)($order['id'] ?? $order['order_id'] ?? 0);
            if ($orderId > 0) {
                try {
                    $opts = db_get_row('order_options', [
                        'order_id' => $orderId,
                        'select' => 'boosters,hours'
                    ], 1);
                    if (is_array($opts)) {
                        $boosters = (int)($opts['boosters'] ?? $opts['hours'] ?? 0);
                    }
                } catch (Throwable $e) {}
            }
        }

        $boosters = max(1, min(4, $boosters > 0 ? $boosters : 1));
        return (int)floor($amount_cents / $boosters);
    }
}

if (!function_exists('util_format_lol_classic_division')) {
function util_format_lol_classic_division($division)
{
    $divisions = [
        4 => 'IV',
        3 => 'III',
        2 => 'II',
        1 => 'I',
    ];
    return $divisions[(int)$division] ?? $division;
}
}

if (!function_exists('util_get_lol_classic_rank')) {
function util_get_lol_classic_rank($rank_id)
{
    $ranks = [
        0 => 'Unranked',
        1 => 'Salt',
        2 => 'Wood',
        3 => 'Silver',
        4 => 'Gold',
        5 => 'Platinum',
        6 => 'Diamond',
        7 => 'Legend',
    ];

    return $ranks[$rank_id] ?? 'Unknown';
}
}

if (!function_exists('lol_classic_rank_asset_url')) {
function lol_classic_rank_asset_url($tier, $division = 4)
{
    $files = [
        0 => 'unranked',
        1 => 'salt',
        2 => 'wood',
        3 => 'silver',
        4 => 'gold',
        5 => 'platinum',
        6 => 'diamond',
        7 => 'legend',
    ];
    $tier = (int)$tier;
    return ASSET_URL . '/website/images/lol-classic/ranks/' . ($files[$tier] ?? 'salt') . '.webp';
}
}

if (!function_exists('util_is_lol_classic')) {
function util_is_lol_classic($game)
{
    $game = strtolower((string)$game);
    return $game === 'lol_classic' || $game === 'lol-classic';
}
}

if (!function_exists('util_load_lol_classic_champions_select')) {
function util_load_lol_classic_champions_select($select = [], $separator = '|')
{
    $html = '';

    if (!is_array($select)) {
        $select = explode($separator, (string) ($select ?? ''));
    }

    $select = array_values(array_filter(array_map('strval', $select), 'strlen'));
    $championDir = SYS_PATH . '/public/assets/website/images/lol-classic/champions';
    $files = is_dir($championDir) ? glob($championDir . '/*.{png,jpg,jpeg,webp}', GLOB_BRACE) : [];

    $champions = [];
    foreach ($files as $file) {
        $base = pathinfo($file, PATHINFO_FILENAME);
        $label = str_replace('_', ' ', $base);
        $champions[$base] = $label;
    }

    if (empty($champions) && is_file(SYS_PATH . '/public/uploads/lists/lol-champions.json')) {
        $fallback = json_decode(file_get_contents(SYS_PATH . '/public/uploads/lists/lol-champions.json'), true);
        if (is_array($fallback)) {
            $champions = $fallback;
        }
    }

    asort($champions, SORT_NATURAL | SORT_FLAG_CASE);

    foreach ($champions as $key => $value) {
        $imageUrl = ASSET_URL . '/website/images/lol-classic/champions/' . rawurlencode((string)$key) . '.png';
        $selected = in_array((string) $key, $select, true) ? ' selected' : '';
        $html .= "<option data-image='$imageUrl' value='$key'$selected>$value</option>";
    }

    return $html;
}
}

if (!function_exists('lb_rewards_route_row')) {
    function lb_rewards_route_row(string $sql, array $params = []): array
    {
        global $db;
        try {
            $row = $db->row($sql, $params);
            if (is_array($row)) return $row;
        } catch (Throwable $e) {}
        try {
            $rows = $db->run($sql, ...$params);
            if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) return $rows[0];
        } catch (Throwable $e) {}
        try {
            $rows = $db->run($sql, $params);
            if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) return $rows[0];
        } catch (Throwable $e) {}
        return [];
    }
}

if (!function_exists('lb_rewards_route_rows')) {
    function lb_rewards_route_rows(string $sql, array $params = []): array
    {
        global $db;
        try {
            $rows = $db->run($sql, ...$params);
            if (is_array($rows)) return $rows;
        } catch (Throwable $e) {}
        try {
            $rows = $db->run($sql, $params);
            if (is_array($rows)) return $rows;
        } catch (Throwable $e) {}
        return [];
    }
}

if (!function_exists('lb_rewards_route_single')) {
    function lb_rewards_route_single(string $sql, array $params = [])
    {
        global $db;
        try { return $db->single($sql, $params); } catch (Throwable $e) {}
        try {
            $rows = $db->run($sql, ...$params);
            if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) {
                $first = reset($rows[0]);
                return $first === false ? null : $first;
            }
        } catch (Throwable $e) {}
        try {
            $rows = $db->run($sql, $params);
            if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) {
                $first = reset($rows[0]);
                return $first === false ? null : $first;
            }
        } catch (Throwable $e) {}
        return null;
    }
}

if (!function_exists('lb_seller_setup_status')) {
    function lb_seller_setup_status(array $seller, $db = null): array
    {
        if (!$db) { global $db; }
        $seller_id = (int)($seller['id'] ?? 0);

        $read_list = static function ($value): array {
            if (is_array($value)) return array_values(array_filter(array_map('strval', $value)));
            $raw = trim((string)($value ?? ''));
            if ($raw === '') return [];
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return array_values(array_filter(array_map('strval', $json)));
            }
            $sep = strpos($raw, '|') !== false ? '|' : ',';
            return array_values(array_filter(array_map('trim', explode($sep, $raw))));
        };

        $default_icon = 'https://lolboost.gg/public/uploads/icons/default.png';
        $icon = trim((string)($seller['icon'] ?? ''));
        $banner = trim((string)($seller['banner'] ?? ''));
        $languages = $read_list($seller['languages'] ?? '');
        $description = trim((string)($seller['description'] ?? ''));

        $payout_count = 0;
        if ($seller_id > 0 && $db && is_object($db)) {
            try {
                $db->run("CREATE TABLE IF NOT EXISTS seller_payout_methods (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    seller_id INT NOT NULL,
                    method VARCHAR(32) NOT NULL,
                    details TEXT,
                    is_default TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX(seller_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                if (method_exists($db, 'cell')) {
                    $payout_count = (int)$db->cell("SELECT COUNT(*) FROM seller_payout_methods WHERE seller_id = ?", $seller_id);
                } else {
                    $row = $db->row("SELECT COUNT(*) AS cnt FROM seller_payout_methods WHERE seller_id = ?", $seller_id);
                    $payout_count = (int)($row['cnt'] ?? 0);
                }
            } catch (\Throwable $e) { $payout_count = 0; }
        }

        $steps = [
            'discord' => [
                'label' => 'Connect Discord',
                'done' => trim((string)($seller['discord_id'] ?? '')) !== '' || trim((string)($seller['discord'] ?? '')) !== '',
            ],
            'profile_picture' => [
                'label' => 'Profile picture',
                'done' => $icon !== '' && $icon !== $default_icon && strpos($icon, '/uploads/icons/default.png') === false,
            ],
            'banner' => [
                'label' => 'Profile banner',
                'done' => $banner !== '',
            ],
            'languages' => [
                'label' => 'Languages',
                'done' => count($languages) > 0,
            ],
            'description' => [
                'label' => 'Description',
                'done' => $description !== '',
            ],
            'chat_requests' => [
                'label' => 'Chat request preference',
                'done' => (int)($seller['seller_setup_chat_ack'] ?? 0) === 1,
            ],
            'payout' => [
                'label' => 'Payout method',
                'done' => $payout_count > 0,
            ],
        ];

        $missing = [];
        foreach ($steps as $key => $step) {
            if (empty($step['done'])) $missing[] = $key;
        }
        $done_count = count($steps) - count($missing);
        $percent = count($steps) > 0 ? (int)round(($done_count / count($steps)) * 100) : 100;

        return [
            'complete' => count($missing) === 0,
            'percent' => $percent,
            'steps' => $steps,
            'missing' => $missing,
            'seller' => $seller,
            'languages' => $languages,
            'payout_count' => $payout_count,
        ];
    }
}

if (!function_exists('lb_seller_setup_require_complete')) {
    function lb_seller_setup_require_complete(array $seller, $db = null): void
    {
        if (!$db) { global $db; }
        $status = lb_seller_setup_status($seller, $db);
        if (empty($status['complete'])) {
            redirect_url('seller-area/setup');
            exit;
        }
    }
}

if (!function_exists('lb_client_selling_account_order_view')) {
    function lb_client_selling_account_order_view($account_id) {

    global $is_client;
    if (!$is_client) {
        redirect_url('');
    }

    $id = (int)$account_id;
    $account = db_get_row('selling_accounts', ['id' => $id, 'client_id' => CLIENT_ID, 'sold' => 1], 1);
    if (empty($account)) {
        redirect_url('profile/accounts');
    }

    $seller = null;
    if (!empty($account['seller_id'])) {
        $seller = db_get_row('sellers', ['id' => (int)$account['seller_id']], 1);
    }

    // Load and normalize marketplace account chat messages for the client account view.
    // Admin messages must use admins.icon, not stale sender_icon values from old JSON.
    global $db;
    $chat_messages = [];
    $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_account_' . $id) . '.json';
    if (is_file($chat_path)) {
        $raw = file_get_contents($chat_path);
        $chat_data = json_decode($raw, true);
        if (is_array($chat_data) && isset($chat_data['messages']) && is_array($chat_data['messages'])) {
            $chat_messages = array_values(array_filter($chat_data['messages'], fn($m) => is_array($m) && empty($m['deleted'])));
        }
    }

    $admin_rows_by_id = [];
    $admin_rows_by_name = [];
    foreach ($chat_messages as $m) {
        if (!is_array($m) || !empty($m['deleted'])) { continue; }
        $sender = strtolower(trim((string)($m['sender'] ?? $m['type'] ?? $m['sender_type'] ?? '')));
        if ($sender !== 'admin') { continue; }
        $aid = (int)($m['sender_id'] ?? $m['admin_id'] ?? 0);
        if ($aid > 0 && empty($admin_rows_by_id[$aid])) {
            $row = $db->row("SELECT id, username, icon FROM admins WHERE id = ? LIMIT 1", $aid);
            if (!empty($row)) {
                $admin_rows_by_id[$aid] = $row;
                $admin_rows_by_name[strtolower(trim((string)($row['username'] ?? '')))] = $row;
            }
        }
        $name = trim((string)($m['sender_name'] ?? $m['username'] ?? $m['name'] ?? ''));
        $nameKey = strtolower($name);
        if ($name !== '' && empty($admin_rows_by_name[$nameKey])) {
            $row = $db->row("SELECT id, username, icon FROM admins WHERE username = ? LIMIT 1", $name);
            if (!empty($row)) {
                $admin_rows_by_id[(int)($row['id'] ?? 0)] = $row;
                $admin_rows_by_name[strtolower(trim((string)($row['username'] ?? $name)))] = $row;
            }
        }
    }

    foreach ($chat_messages as &$m) {
        if (!is_array($m)) { continue; }
        $sender = strtolower(trim((string)($m['sender'] ?? $m['type'] ?? $m['sender_type'] ?? '')));
        if ($sender === 'admin') {
            $aid = (int)($m['sender_id'] ?? $m['admin_id'] ?? 0);
            $nameKey = strtolower(trim((string)($m['sender_name'] ?? $m['username'] ?? $m['name'] ?? '')));
            $row = ($aid > 0 && !empty($admin_rows_by_id[$aid])) ? $admin_rows_by_id[$aid] : ($admin_rows_by_name[$nameKey] ?? null);
            if (!empty($row)) {
                $m['sender_id'] = (int)($row['id'] ?? $aid);
                $m['sender_name'] = (string)($row['username'] ?? ($m['sender_name'] ?? 'Admin'));
                $m['sender_icon'] = trim((string)($row['icon'] ?? ''));
            } else {
                // Never let an admin message inherit the client icon.
                $m['sender_icon'] = '';
            }
        }
    }
    unset($m);

    $can_review = false;
    $already_reviewed = false;
    if (!empty($account['seller_id'])) {
        global $db;
        $existing_rv = $db->row(
            "SELECT id FROM seller_reviews WHERE seller_id = ? AND client_id = ? LIMIT 1",
            (int)$account['seller_id'], (int)CLIENT_ID
        );
        $can_review       = true;
        $already_reviewed = !empty($existing_rv);
    }

    $meta = [
        'title'       => htmlspecialchars($account['title'] ?? ('Account #S' . $id)) . ' | LoLBoost.gg',
        'h1'          => 'Account Details',
        'description' => 'View your purchased LoL account details and chat with the seller.',
    ];

    view_file('client/pages/orders/account_view', [
        'account'          => $account,
        'seller'           => $seller,
        'meta'             => $meta,
        'can_review'       => $can_review,
        'already_reviewed' => $already_reviewed,
        'chat_messages'    => $chat_messages,
    ]);

    }
}

if (!function_exists('lb_ajax_realtime_notify_update')) {
    function lb_ajax_realtime_notify_update(string $room = ''): void
    {
        if (!function_exists('lb_realtime_emit')) return;
        $payload = ['scope' => $room, 'ts' => time()];
        if ($room !== '') {
            lb_realtime_emit('notification_update', $payload, [$room]);
        } else {
            lb_realtime_emit('notification_update', $payload);
        }
    }
}

