<?php
/*
 * Merged _core/functions.php
 * Base: _core (29) test.zip, with selected live-safe fixes from _core (30) live.zip.
 * Keeps Dynamic Games + Digital Goods from test and preserves live fixes for sessions, discount usage, Riot tracking and analytics.
 */


date_default_timezone_set('Europe/Berlin');

use Ramsey\Uuid\Uuid;
use DeviceDetector\DeviceDetector;
use Ozdemir\Datatables\Datatables;
use Ozdemir\Datatables\DB\MySQL;
use Bulletproof\Image;
use Stripe\Stripe;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;

if (is_file(__DIR__ . '/seller_api.php')) {
    require_once __DIR__ . '/seller_api.php';
}

function esc_array($array)
{
    $rslt = [];
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $rslt[$key] = esc_array($val);
        } else {
            $rslt[$key] = esc($val);
        }
    }
    return $rslt;
}
function esc($value)
{
    // Idempotent on purpose: most user input is already stored HTML-escaped, so escaping
    // it again on output turned "Evori's" into "Evori&#039;s" on the page. Decoding first
    // means esc(esc($x)) === esc($x) — raw input is still fully escaped, and already
    // escaped values pass through unchanged instead of being mangled.
    $value = (string) ($value ?? '');
    if ($value !== '' && strpos($value, '&') !== false) {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
function unesc($string)
{
    if (empty($string)) {
        return null;
    }
    return htmlspecialchars_decode($string, ENT_QUOTES);
}

function decode_entities($s)
{
    return html_entity_decode($s ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}


function db_format_val($val, $operator = null)
{
    if (is_numeric($val)) {
        $val = esc($val);
    } elseif ($operator == 'LIKE') {
        $val = "'%" . esc($val) . "%'";
    } else {
        $val = '"' . esc($val) . '"';
    }
    return $val;
}
function db_format_params($params, $table)
{
    // gte = greather than or equal
    // lte = lower than or equal
    // gt = greather than
    // lt = lower than
    // s = IS LIKE
    // n = not equal

    $operators_sym = ['=', '>=', '<=', '>', '<', 'LIKE', '!='];
    $operators_txt = ['eq', 'gte', 'lte', 'gt', 'lt', 's', 'n'];
    $def_params = [
        'select' => '*',
        'limit' => null,
        'offset' => null,
        'order' => '',
        'where' => '',
        'update' => ''
    ];
    $rslt = $def_params;

    if (count($params) > 0) {
        foreach ($params as $key => $val) {
            switch ($key) {
                case 'select':
                    $rslt_select = '';
                    if ($val != '*') {
                        $val = explode(',', $val);
                        foreach ($val as $v) {
                            $rslt_select .= $table . '.' . $v . ',';
                        }
                        $rslt['select'] = rtrim($rslt_select, ',');
                    }
                    break;
                case 'limit':
                    $rslt['limit'] = intval($val);
                    break;
                case 'offset':
                    if (!empty($val)) {
                        $rslt['offset'] = 'OFFSET ' . intval($val);
                    }
                    break;
                case 'order':
                    if (!empty($val)) {
                        $tmp_order = esc($val);
                        if (str_contains($tmp_order, ',')) {
                            $tmp_order = explode(',', $tmp_order);
                            $rslt['order'] = 'ORDER BY ' . $table . '.' . $tmp_order[0] . ' ' . $tmp_order[1];
                        } else {
                            $rslt['order'] = 'ORDER BY ' . $tmp_order;
                        }
                    }
                    break;
                case 'fn':
                    $rslt['select'] = " " . $val;
                    break;
                default:
                    if (is_array($val)) {
                        foreach ($val as $operator => $query_tmp) {
                            if (is_array($query_tmp)) {
                                $operator = str_replace($operators_txt, $operators_sym, $operator);
                                $rslt['where'] .= ' (';
                                if ($operator == 'LIKE') {
                                    foreach ($query_tmp as $query) {
                                        $rslt['where'] .= $table . '.' . $key . ' ' . $operator . ' ' . db_format_val($query, $operator) . ' AND ';
                                    }
                                    $rslt['where'] = rtrim($rslt['where'], ' AND ');
                                } else {
                                    foreach ($query_tmp as $query) {
                                        $rslt['where'] .= $table . '.' . $key . ' ' . $operator . ' ' . db_format_val($query) . ' OR ';
                                    }
                                    $rslt['where'] = rtrim($rslt['where'], ' OR ');
                                }
                                $rslt['where'] .= ' ) AND ';
                            } elseif (!empty($query_tmp) or is_numeric($query_tmp)) {
                                $operator = str_replace($operators_txt, $operators_sym, $operator);
                                $rslt['where'] .= $table . '.' . esc($key) . ' ' . $operator . ' ' . db_format_val($query_tmp, $operator);
                                if ($operator == 'or') {
                                    $rslt['where'] .= ' OR ';
                                } else {
                                    $rslt['where'] .= ' AND ';
                                }
                            }
                        }
                    } else {
                        if (!empty($val) or is_numeric($val)) {
                            $rslt['where'] .= $table . '.' . esc($key) . ' = ' . db_format_val($val) . ' AND ';
                        } else {
                            $rslt['where'] .= $table . '.' . esc($key) . ' IS NULL AND ';
                        }
                    }
                    break;
            }
        }
        if (!empty($rslt['where'])) {
            $rslt['where'] = "WHERE " . $rslt['where'];
            $rslt['where'] = rtrim($rslt['where'], "AND ");
        }
        if (!empty($rslt['limit'])) {
            $rslt['limit'] = "LIMIT " . $rslt['limit'];
        }
    }
    if ($rslt['select'] == "*") {
        $rslt['select'] = $table . '.* ';
    }
    return $rslt;
}

function redirect_url($url)
{
    header('location: ' . BASE_URL . '/' . $url);
    exit();
}


function seller_profile_slug($username)
{
    $username = trim((string)($username ?? ''));
    if ($username === '') {
        return '';
    }

    $username = html_entity_decode($username, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $username = rawurldecode($username);
    $username = preg_replace('/\s+/', '-', $username);
    $username = preg_replace('/[^\pL\pN\-_]/u', '', $username);
    $username = preg_replace('/-+/', '-', $username);
    return mb_strtolower(trim($username, '-'), 'UTF-8');
}

function seller_profile_url($sellerOrUsername)
{
    $slug = '';

    if (is_array($sellerOrUsername)) {
        $slug = trim((string)($sellerOrUsername['slug'] ?? ''));
        if ($slug === '') {
            $slug = seller_profile_slug($sellerOrUsername['username'] ?? '');
        }
    } else {
        $slug = seller_profile_slug($sellerOrUsername);
    }

    return '/sellers/' . rawurlencode($slug);
}

function arrfilter($var)
{
    return ($var !== null && $var !== false && $var !== "");
}

function auth_session_check_user_type($session_token)
{
    $user_type = false;
    $pattern_client = "/client/i";
    $pattern_booster = "/booster/i";
    $pattern_admin = "/admin/i";
    $pattern_seller = "/seller/i";
    if ($session_token == 'global-l9') {
        $user_type = "global";
    } else {
        if (preg_match($pattern_client, $session_token)) {
            $user_type = "client";
        } elseif (preg_match($pattern_booster, $session_token)) {
            $user_type = "booster";
        } elseif (preg_match($pattern_admin, $session_token)) {
            $user_type = "admin";
        } elseif (preg_match($pattern_seller, $session_token)) {
            $user_type = "seller";
        }
    }
    if ($user_type != false) {
        return $user_type;
    } else {
        return false;
    }
}
function auth_session_check_token($session_token, $user_type)
{
    global $db;
    if ($user_type != "global") {
        switch ($user_type) {
            case "client":
                $col = 'client_id';
                $tbl = 'client_sessions';
                break;
            case "booster":
                $col = 'booster_id';
                $tbl = 'booster_sessions';
                break;
            case "admin":
                $col = 'admin_id';
                $tbl = 'admin_sessions';
                break;
            case "seller":
                $col = 'seller_id';
                $tbl = 'seller_sessions';
                break;
            default:
                return false;
        }

        $check_session = $db->cell('SELECT ' . $col . ' FROM ' . $tbl . ' WHERE token = ?', $session_token);
        if (!empty($check_session)) {
            return $check_session;
        } else {
            return false;
        }
    } else {
        return 1;
    }
}

function util_rank_img($game, $size, $rank)
{
    if (in_array(strtolower(trim((string)$game)), ['lol_classic', 'lol-classic', 'league-of-legends-classic'], true)) {
        return util_lol_classic_rank_img((int)$rank);
    }

    // Normalize full slugs to short asset-folder names
    $_map = [
        'league-of-legends'  => 'lol',
        'leagu'              => 'lol',
        'valorant'           => 'val',
        'valor'              => 'val',
        'teamfight-tactics'  => 'lol',
        'teamf'              => 'lol',
        'apex-legends'       => 'apex',
        'overwatch-2'        => 'ow2',
        'rocket-league'      => 'rl',
        'marvel-rivals'      => 'rivals',
    ];
    $game = $_map[$game] ?? $game;
    // tft shares lol rank icons
    if ($game === 'tft') $game = 'lol';

    // Overwatch 2 placements have a real Unranked tier (index 0). Its asset lives
    // in the newer website rank directory rather than the legacy core rank tree.
    if ($game === 'ow2' && (int)$rank === 0) {
        return '/public/assets/website/images/boosting/ranks/overwatch-2/unranked.webp';
    }

    if ($game === 'rl' && (int)$rank === 0) {
        return '/public/assets/website/images/boosting/ranks/rocket-league/unranked.webp';
    }

    // Marvel Rivals Grandmaster uses the new website asset. Its tier position is 6
    // in the authoritative public rank list (before Celestial and Eternity).
    if ($game === 'rivals' && (int)$rank === 6) {
        return '/public/assets/website/images/boosting/ranks/marvel-rivals/grandmaster.webp';
    }

    // Fortnite rank icons live in the newer website asset tree (no size subfolder).
    if ($game === 'fortnite') {
        $fortniteRanks = [1 => 'bronze', 2 => 'silver', 3 => 'gold', 4 => 'platinum', 5 => 'diamond', 6 => 'elite', 7 => 'champion', 8 => 'unreal'];
        $filename = $fortniteRanks[(int)$rank] ?? 'bronze';
        return '/public/assets/website/images/boosting/ranks/fortnite/' . $filename . '.webp';
    }

    // For games with named rank files (apex, ow2, rl, rivals) we map tier index → filename
    $_namedRanks = [
        'apex'   => [1=>'rookie', 2=>'bronze', 3=>'silver', 4=>'gold', 5=>'platinum', 6=>'diamond', 7=>'master'],
        'ow2'    => [1=>'bronze', 2=>'silver', 3=>'gold', 4=>'platinum', 5=>'diamond', 6=>'master', 7=>'grandmaster', 8=>'champion', 9=>'top500'],
        'rl'     => [1=>'bronze', 2=>'silver', 3=>'gold', 4=>'platinum', 5=>'diamond', 6=>'champion', 7=>'grand-champion', 8=>'supersonic-legend'],
        'rivals' => [1=>'bronze', 2=>'silver', 3=>'gold', 4=>'platinum', 5=>'diamond', 6=>'grandmaster', 7=>'celestial', 8=>'eternity'],
    ];

    if (isset($_namedRanks[$game])) {
        $filename = $_namedRanks[$game][(int)$rank] ?? 'bronze';
        return ASSET_URL . '/core/main/img/' . $game . '/ranks/' . $size . '/' . $filename . '.webp';
    }

    // Check if the rank folder exists on disk — if not, return empty string
    // so callers can skip rendering a broken <img> tag
    $folder = defined('ROOT_PATH')
        ? ROOT_PATH . '/public/assets/core/main/img/' . $game . '/ranks/' . $size
        : null;
    if ($folder !== null && !is_dir($folder)) {
        return '';
    }

    return ASSET_URL . '/core/main/img/' . $game . '/ranks/' . $size . '/' . $rank . '.png';
}

function util_lol_classic_rank_name($tier): string
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
    return $ranks[max(0, min(7, (int)$tier))] ?? 'Unranked';
}

function util_lol_classic_rank_img($tier): string
{
    $filename = strtolower(util_lol_classic_rank_name($tier));
    return '/public/assets/website/images/lol-classic/ranks/' . $filename . '.webp';
}

/**
 * Returns true when util_rank_img would produce a valid (non-empty) URL.
 */
function util_rank_img_exists($game, $size = 'mini'): bool
{
    return util_rank_img($game, $size, 1) !== '';
}

function util_format_lol_division($division)
{
    $divisions = [
        1 => 'IV',
        2 => 'III',
        3 => 'II',
        4 => 'I'
    ];
    return $divisions[$division] ?? '';
}

function util_format_val_division($division)
{
    $divisions = [
        1 => 'I',
        2 => 'II',
        3 => 'III'
    ];
    return $divisions[$division];
}

function util_time_ago($time)
{
    // Calculate difference between current
    // time and given timestamp in seconds
    $diff = time() - $time;

    if ($diff < 1) {
        return 'less than 1 second ago';
    }

    $time_rules = array(
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hr',
        60 => 'min',
        1 => 'sec'
    );

    foreach ($time_rules as $secs => $str) {
        $div = $diff / $secs;

        if ($div >= 1) {
            $t = round($div);

            return $t . ' ' . $str .
                ($t > 1 ? 's' : '') . ' ago';
        }
    }
}

function util_censor_string($string)
{
    $string_first = substr($string, 0, 1);
    return $string_first . str_repeat("*", strlen($string));
}

if (!function_exists('util_mask_username')) {
/**
 * Public-facing client name: keeps the first and last character and stars out
 * everything in between ("SettingsAtMax" -> "S***********x").
 */
function util_mask_username($name, string $fallback = 'Client'): string
{
    $name = trim((string)$name);
    if ($name === '') {
        return $fallback;
    }

    $len = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
    if ($len <= 2) {
        return $len === 1 ? $name . '*' : $name;
    }

    $cut   = static fn($s, $start, $length) => function_exists('mb_substr')
        ? mb_substr($s, $start, $length, 'UTF-8')
        : substr($s, $start, $length);

    return $cut($name, 0, 1) . str_repeat('*', $len - 2) . $cut($name, $len - 1, 1);
}
}

if (!function_exists('egirl_no_feedback_entries')) {
/**
 * Completed GG-Girl sessions whose client did not leave a review within $hours.
 *
 * Mirrors seller_no_feedback_entries(): the rows are derived on read rather than
 * written to egirl_reviews, so they never distort the stored review_avg cache.
 * Each entry renders as a 5-star "No Feedback left." card once the grace period
 * has passed.
 *
 * @return array<int,array{order_id:int,confirmed_at:string,created_at:string,client_username:?string,client_icon:?string,rating:int,comment:string,is_placeholder:int}>
 */
function egirl_no_feedback_entries(int $egirl_id, int $hours = 24): array
{
    global $db;

    if ($egirl_id <= 0 || !isset($db) || !is_object($db)) {
        return [];
    }

    $hours = max(1, min(8760, $hours));
    $rows  = [];

    $sql = "SELECT eo.id AS order_id,
                   COALESCE(eo.completed_at, eo.created_at) AS confirmed_at,
                   c.username AS client_username,
                   c.icon AS client_icon
            FROM egirl_orders eo
            LEFT JOIN clients c ON c.id = eo.client_id
            WHERE eo.egirl_id = ?
              AND UPPER(eo.status) = 'COMPLETED'
              AND COALESCE(eo.completed_at, eo.created_at) <= (NOW() - INTERVAL {$hours} HOUR)
              AND NOT EXISTS (SELECT 1 FROM egirl_reviews er WHERE er.egirl_order_id = eo.id)
            ORDER BY confirmed_at DESC";

    try {
        foreach (($db->run($sql, $egirl_id) ?: []) as $row) {
            $confirmedAt = (string)($row['confirmed_at'] ?? '');
            $confirmedTs = $confirmedAt !== '' ? strtotime($confirmedAt) : false;
            if ($confirmedTs === false) continue;
            $rows[] = [
                'order_id'        => (int)($row['order_id'] ?? 0),
                'confirmed_at'    => $confirmedAt,
                // The card appears the moment the grace period runs out.
                'created_at'      => date('Y-m-d H:i:s', $confirmedTs + ($hours * 3600)),
                'client_username' => $row['client_username'] ?? null,
                'client_icon'     => $row['client_icon'] ?? null,
                'rating'          => 5,
                'comment'         => 'No Feedback left.',
                'is_placeholder'  => 1,
            ];
        }
    } catch (\Throwable $e) {
        // Table may not exist on older installs — degrade to no placeholders.
    }

    return $rows;
}
}

if (!function_exists('egirl_no_feedback_counts')) {
/**
 * Bulk variant of egirl_no_feedback_entries() for listing pages: one grouped
 * query for many GG-Girls instead of one query per card.
 *
 * @param int[] $egirl_ids
 * @return array<int,int> egirl_id => number of placeholder reviews
 */
function egirl_no_feedback_counts(array $egirl_ids, int $hours = 24): array
{
    global $db;

    $ids = array_values(array_unique(array_filter(array_map('intval', $egirl_ids), static fn($v) => $v > 0)));
    if (!$ids || !isset($db) || !is_object($db)) {
        return [];
    }

    $hours = max(1, min(8760, $hours));
    $in    = implode(',', array_fill(0, count($ids), '?'));

    $sql = "SELECT eo.egirl_id, COUNT(*) AS c
            FROM egirl_orders eo
            WHERE eo.egirl_id IN ({$in})
              AND UPPER(eo.status) = 'COMPLETED'
              AND COALESCE(eo.completed_at, eo.created_at) <= (NOW() - INTERVAL {$hours} HOUR)
              AND NOT EXISTS (SELECT 1 FROM egirl_reviews er WHERE er.egirl_order_id = eo.id)
            GROUP BY eo.egirl_id";

    $out = [];
    try {
        foreach (($db->run($sql, ...$ids) ?: []) as $row) {
            $out[(int)$row['egirl_id']] = (int)$row['c'];
        }
    } catch (\Throwable $e) {
        // Older installs without the tables simply get no placeholders.
    }

    return $out;
}
}

function util_random_number($length = 8)
{
    $intMin = (10 ** $length) / 10; // 100...
    $intMax = (10 ** $length) - 1;  // 999...

    $codeRandom = mt_rand($intMin, $intMax);

    return $codeRandom;
}

function util_random_password($length = 9, $add_dashes = false, $available_sets = 'luds')
{
    $sets = array();
    if (strpos($available_sets, 'l') !== false) {
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    }
    if (strpos($available_sets, 'u') !== false) {
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    }
    if (strpos($available_sets, 'd') !== false) {
        $sets[] = '23456789';
    }
    if (strpos($available_sets, 's') !== false) {
        $sets[] = '!@#$%&*?';
    }

    $all = '';
    $password = '';
    foreach ($sets as $set) {
        $password .= $set[array_rand(str_split($set))];
        $all .= $set;
    }

    $all = str_split($all);
    for ($i = 0; $i < $length - count($sets); $i++) {
        $password .= $all[array_rand($all)];
    }

    $password = str_shuffle($password);

    if (!$add_dashes) {
        return $password;
    }

    $dash_len = floor(sqrt($length));
    $dash_str = '';
    while (strlen($password) > $dash_len) {
        $dash_str .= substr($password, 0, $dash_len) . '-';
        $password = substr($password, $dash_len);
    }
    $dash_str .= $password;
    return $dash_str;
}
function util_validate($value)
{
    if (isset($value) && !empty($value)) {
        return htmlspecialchars($value);
    } else {
        return false;
    }
}
function util_create_cookie($name, $value, $lifetime)
{
    if ($lifetime) {
        $expire = time() + 60 * 60 * 24 * 365;
    } else {
        $expire = time() + 60 * 60 * 24;
    }
    setcookie($name, $value, $expire, '/');
}

function get_ip_address()
{
    // Behind Cloudflare, REMOTE_ADDR is the CF edge IP (rotates between requests),
    // and X-Forwarded-For may be a "realIP, edgeIP" list. CF-Connecting-IP holds the
    // clean real client IP. Prefer it, then take the FIRST entry of any forwarded
    // list, and always validate — so the stored ip_address is a stable real client
    // IP (the booster single-IP session rule depends on this being reliable).
    $candidates = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    foreach ($candidates as $header) {
        $raw = $_SERVER[$header] ?? getenv($header);
        $raw = trim((string) $raw);
        if ($raw === '') {
            continue;
        }
        // Forwarded lists are comma-separated; the first entry is the origin client.
        $ip = trim(explode(',', $raw)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return 'UNKNOWN';
}


function util_validate_array($post_array)
{
    if (isset($post_array) && !empty($post_array) && is_array($post_array)) {
        $new_post_array = [];
        foreach ($post_array as $key => $val) {
            $new_post_array[esc($key)] = esc($val);
        }
        return $new_post_array;
    } else {
        return false;
    }
}

function get_lol_version()
{
    static $version = null;
    if ($version !== null) {
        return $version;
    }

    $fallback = '15.10.1';
    $cacheDir = defined('ROOT_DIR') ? ROOT_DIR . '/cache' : sys_get_temp_dir();
    $cacheFile = rtrim($cacheDir, "/\\") . '/lol_ddragon_version.cache';

    if (is_file($cacheFile)) {
        $cached = trim((string) @file_get_contents($cacheFile));
        if ($cached !== '') {
            if ((time() - (int) @filemtime($cacheFile)) < 86400) {
                return $version = $cached;
            }
            $fallback = $cached;
        }
    }

    // Do not let Riot Data Dragon block the landing page. If the request is slow,
    // keep using the cached version or the safe fallback.
    $context = stream_context_create([
        'http' => [
            'timeout' => 0.8,
            'ignore_errors' => true,
            'header' => "User-Agent: LoLBoost/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $json = @file_get_contents('https://ddragon.leagueoflegends.com/api/versions.json', false, $context);
    $versions = $json ? json_decode($json, true) : null;

    if (is_array($versions) && !empty($versions[0])) {
        $version = (string) $versions[0];
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            @file_put_contents($cacheFile, $version, LOCK_EX);
        }
        return $version;
    }

    return $version = $fallback;
}

function util_format_date_dt($date)
{
    $date = date_create($date);
    return date_format($date, "Y-m-d");
}
// function to format date to readable format (DD, MM YYYY)
function util_format_date_display($date)
{
    $date = date_create($date);
    return date_format($date, "M d, Y");
}
// function to format date to readable format with hour and minute
function util_format_date_display_hm($date)
{
    $date = date_create($date);
    return date_format($date, "M d, Y H:i");
}

// function to format date for order panel in Europe/Berlin timezone (e.g. "14 Jan • 11:00")
function util_format_date_display_panel($date)
{
    // Assume stored timestamps are UTC; convert to Europe/Berlin for display
    $dt = new DateTime($date, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Europe/Berlin'));

    return $dt->format('M d, Y') . ' • ' . $dt->format('H:i');
}
function util_format_rank($tier, $div = null)
{
    $tiers = ['unranked', 'master', 'grandmaster', 'challenger', 'radiant'];
    if (in_array($tier, $tiers)) {
        return ucfirst($tier);
    } else {
        return ucfirst($tier) . " " . $div;
    }
}

function util_format_yes_no($value)
{
    return $value == 1 ? '<span class="legend-indicator bg-success"></span> Yes' : '<span class="legend-indicator bg-danger"></span> No';
}

function util_banned_or_no($value)
{
    return $value == 1 ? ' <span class="badge fw-bold bg-danger">Inactive</span>' : '<span class="badge bg-success">Active</span>';
}

function util_format_solo_duo($value)
{
    return $value == 1 ? ' <span class="badge border text-body border-success">Duo Boost</span>' : '<span class="badge border text-body border-primary">Solo Boost</span>';
}
function util_format_rank_advanced($tier, $div, $game)
{
    if (in_array(strtolower(trim((string)$game)), ['lol_classic', 'lol-classic', 'league-of-legends-classic'], true)) {
        return util_lol_classic_rank_name($tier);
    }
    $tiers = ['Unranked', 'Master', 'Grandmaster', 'Challenger', 'Immortal', 'Radiant'];
    $tier = util_format_tier($tier, $game);
    if (in_array($tier, $tiers)) {
        return $tier;
    } else {
        return $tier . " " . util_format_division($div, $game);
    }
}

function util_format_server($server)
{
    $old_keys = array('euw', 'eune', 'na', 'oce', 'ru', 'tr', 'lan', 'las', 'br', 'jp', 'kr', 'eu', 'sea', 'us', 'me', 'lan', 'las', 'jp', 'vn', 'ph', 'sg', 'th', 'tw');
    $new_keys = array('EU-West', 'EU-North East', 'North America', 'Oceania', 'Russia', 'Turkey', 'Latin America', 'Latin America', 'Brazil', 'Japan', 'Korea', 'Europe', 'Southeast Asia', 'United States', 'Middle East', 'Latin America North', 'Latin America South', 'Japan', 'Vietnam', 'Philippines', 'Singapore', 'Thailand', 'Taiwan');

    $index = array_search($server, $old_keys);
    if ($index !== false) {
        return $new_keys[$index];
    } else {
        return $server;
    }
}

if (!function_exists('util_format_server_code')) {
    function util_format_server_code($server): string
    {
        $raw = strtolower(trim((string)$server));
        if ($raw === '') return '';

        $normalized = preg_replace('/[\s_]+/', '-', $raw);
        $map = [
            'euw' => 'EUW',
            'eu-west' => 'EUW',
            'europe-west' => 'EUW',
            'eune' => 'EUNE',
            'eu-north-east' => 'EUNE',
            'europe-north-east' => 'EUNE',
            'europe-nordic-east' => 'EUNE',
            'europe-nordic-and-east' => 'EUNE',
            'na' => 'NA',
            'north-america' => 'NA',
            'oce' => 'OCE',
            'oceania' => 'OCE',
            'kr' => 'KR',
            'korea' => 'KR',
            'jp' => 'JP',
            'japan' => 'JP',
            'br' => 'BR',
            'brazil' => 'BR',
            'lan' => 'LAN',
            'latin-america-north' => 'LAN',
            'las' => 'LAS',
            'latin-america-south' => 'LAS',
            'tr' => 'TR',
            'turkey' => 'TR',
            'ru' => 'RU',
            'russia' => 'RU',
            'me' => 'ME',
            'middle-east' => 'ME',
            'sea' => 'SEA',
            'southeast-asia' => 'SEA',
        ];

        return $map[$normalized] ?? strtoupper($raw);
    }
}

function util_servers_list()
{
    // Werte die gespeichert werden (keys)
    return [
        // LoL
        'euw',
        'eune',
        'na',
        'oce',
        'ru',
        'tr',
        'lan',
        'las',
        'br',
        'jp',
        'kr',
        // Valorant / generell
        'eu',
        'sea',
        'us',
        'me',
        'vn',
        'ph',
        'sg',
        'th',
        'tw',
    ];
}

function util_load_servers_select($selected = [])
{
    if (!is_array($selected))
        $selected = [];

    // falls String "euw|eune" reinkommt
    if (is_string($selected) && strpos($selected, '|') !== false) {
        $selected = explode('|', $selected);
    }

    $selected = array_values(array_unique(array_map('strtolower', $selected)));

    $out = '';
    foreach (util_servers_list() as $key) {
        $isSel = in_array($key, $selected, true) ? 'selected' : '';
        $label = util_format_server($key);
        $out .= '<option value="' . htmlspecialchars($key) . '" ' . $isSel . '>' . htmlspecialchars($label) . '</option>';
    }
    return $out;
}

function util_load_account_badges($data)
{
    $acc_badges = "";

    if ($data['is_featured'] == 1) {
        $acc_badges .= '<span class="badge bg-red rounded-pill fw-normal" style="padding-top: 0.3rem !important;padding-bottom: 0.3rem !important;"><i class="uil uil-fire align-bottom"></i> Hot</span>';
        $acc_badges .= '<span class="badge bg-yellow rounded-pill fw-normal" style="padding-top: 0.3rem !important;padding-bottom: 0.3rem !important;"><i class="uil uil-favorite align-bottom"></i> Featured</span>';
    }
    if ($data['level_method'] == "by_hand") {
        $acc_badges .= '<span class="badge bg-orange rounded-pill fw-normal" style="padding-top: 0.3rem !important;padding-bottom: 0.3rem !important;"><i class="uil uil-power align-bottom"></i> Handleveled </span>';
    }
    return $acc_badges;
}
function util_load_discount_data($order)
{
    $invoice = db_get_row('invoices', ['order_id' => $order]);
    $discount_data = db_get_row('discounts', ['id' => $invoice['discount_id']]);
    return $discount_data;
}
function util_format_account_name($account_name)
{
    return str_replace([' / ', '/'], ' • ', $account_name);
}
function util_format_account_status($account_status)
{
    $status_class = str_replace([0, 1, 2], ['success', 'danger', 'danger',], $account_status);
    $account_status = str_replace([0, 1, 2], ['Available', 'Sold', 'Banned',], $account_status);
    return '<span class="legend-indicator bg-' . $status_class . '"></span>' . $account_status;
}
if (!function_exists('util_chat_json_unread_count')) {
    // Counts unread-by-$viewerRole messages in one legacy chat JSON file (used by the
    // account/item/top-up/premium-account chat send actions in ajax.php — these all still write
    // to uploads/private/chat/*.json, NOT the seller_chat_messages/seller_conversations DB
    // tables, despite those tables' "current chat system" comments — nothing actually writes to
    // them yet). A reply from $viewerRole marks everything before it as handled, mirroring the
    // read-receipt logic each chat's own send handler applies.
    function util_chat_json_unread_count(string $chatPath, string $viewerRole): int
    {
        if (!is_file($chatPath)) return 0;
        $data = json_decode(@file_get_contents($chatPath) ?: '', true);
        if (!is_array($data) || empty($data['messages']) || !is_array($data['messages'])) return 0;

        // Once an archived seller chat has been imported, its JSON seen flags are
        // intentionally frozen. Never count those stale flags again after F5; use
        // the database read state that the live chat updates instead.
        global $db;
        try {
            $refType = (string)($data['ref_type'] ?? (!empty($data['purchase_id']) ? 'item_purchase' : (!empty($data['account_id']) ? 'account' : '')));
            $refId = (int)($data['ref_id'] ?? $data['purchase_id'] ?? $data['account_id'] ?? 0);
            if ($refType !== '' && $refId > 0 && isset($db) && is_object($db)) {
                $thread = $db->row("SELECT id FROM legacy_chat_threads WHERE chat_key=? AND imported_at IS NOT NULL LIMIT 1", 'seller:' . $refType . ':' . $refId);
                if (!empty($thread['id'])) {
                    $seenColumn = $viewerRole === 'seller' ? 'seen_by_seller' : ($viewerRole === 'admin' ? 'seen_by_admin' : 'seen_by_client');
                    // For the seller's unified account/item/top-up chat, "unread" must mean
                    // "unread client message" only — matching the seller inbox listing
                    // (routes.php $read_chat_summary), which never counts admin/system rows.
                    // Counting admin-sent rows here made the sidebar badge disagree with the
                    // inbox's own "unread only" filter (badge > 0 while the list showed none).
                    if ($viewerRole === 'seller') {
                        $rows = $db->run("SELECT COUNT(*) AS cnt FROM legacy_chat_messages WHERE thread_id=? AND sender_type='client' AND {$seenColumn}=0 AND deleted=0", (int)$thread['id']) ?: [];
                    } else {
                        $rows = $db->run("SELECT COUNT(*) AS cnt FROM legacy_chat_messages WHERE thread_id=? AND sender_type NOT IN (?, 'system') AND {$seenColumn}=0 AND deleted=0", (int)$thread['id'], $viewerRole) ?: [];
                    }
                    return (int)($rows[0]['cnt'] ?? 0);
                }
            }
        } catch (\Throwable $e) {
            // Tables may not exist before the first migrated chat; use JSON then.
        }
        $messages = array_values($data['messages']);

        $senderOf = static function (array $m): string {
            return strtolower(trim((string)($m['sender'] ?? $m['sender_type'] ?? $m['type'] ?? '')));
        };
        $orderOf = static function (array $m, int $index): int {
            if (!empty($m['time']) && is_numeric($m['time'])) return (int)$m['time'];
            if (!empty($m['created_at'])) {
                $ts = strtotime((string)$m['created_at']);
                if ($ts !== false) return (int)$ts;
            }
            return $index;
        };
        $seenKey = 'seen_by_' . $viewerRole;

        $lastViewerValue = 0;
        foreach ($messages as $idx => $m) {
            if (!is_array($m) || !empty($m['deleted'])) continue;
            if ($senderOf($m) === $viewerRole) {
                $lastViewerValue = max($lastViewerValue, $orderOf($m, $idx + 1));
            }
        }

        $count = 0;
        foreach ($messages as $idx => $m) {
            if (!is_array($m) || !empty($m['deleted'])) continue;
            $sender = $senderOf($m);
            if ($sender === $viewerRole || $sender === 'system') continue;

            if (array_key_exists($seenKey, $m)) $seen = (int)$m[$seenKey];
            elseif (array_key_exists('seen', $m)) $seen = (int)$m['seen'];
            elseif (array_key_exists('is_read', $m)) $seen = (int)$m['is_read'];
            // This JSON fallback only runs for threads that have never been imported into
            // legacy_chat_messages (see the DB short-circuit above), i.e. genuinely live/unread
            // conversations — so a message with no seen flag at all must count as unread here,
            // matching how the actual inbox listing (routes.php $read_chat_summary) treats it.
            // A "1" default previously made brand-new flagless messages (e.g. account requests)
            // silently invisible to the sidebar/unread-count badge while the inbox still showed them.
            else $seen = 0;

            if ($seen === 1) continue;

            $value = $orderOf($m, $idx + 1);
            if ($lastViewerValue > 0 && $value <= $lastViewerValue) continue;

            $count++;
        }
        return $count;
    }
}

if (!function_exists('util_client_chat_unread_count')) {
    // Canonical unread count for a client across every chat surface: account/item/top-up chat
    // (uploads/private/chat/selling_*.json), premium-account chat with admin
    // (uploads/private/chat/accounts_*.json), and booster/admin order chat (DB
    // chat_messages/orders). Used by both the AJAX endpoint (client_seller_chat_unread_count,
    // pushed live via the socket server's chat_unread_subscribe handler) and the client
    // sidebar's initial server-rendered badge — they must agree or the badge flashes a wrong
    // number until the websocket round-trip corrects it a moment later.
    function util_client_chat_unread_count(int $clientId): int
    {
        if ($clientId <= 0) return 0;
        $total = 0;

        global $db;
        if (isset($db) && is_object($db)) {
            try {
                // Do not count seller_chat_messages here. That retired parallel store is not
                // rendered by the unified inbox and previously produced ghost unread badges.

                // Booster chats are primarily stored in one JSON file per order. Count the
                // exact same source that the inbox renders and marks as read. Falling back to
                // chat_messages only for orders without a JSON chat prevents stale DB rows from
                // restoring an old unread badge after a page reload.
                $orderRows = $db->run("SELECT id FROM orders WHERE client_id = ?", $clientId) ?: [];
                $chatDirForOrders = (defined('SYS_PATH') ? SYS_PATH : dirname(__DIR__)) . '/public/uploads/private/chat';
                foreach ($orderRows as $orderRow) {
                    $orderId = (int)($orderRow['id'] ?? 0);
                    if ($orderId <= 0) continue;
                    $orderChat = $chatDirForOrders . '/' . sha1((string)$orderId) . '.json';
                    if (is_file($orderChat)) {
                        $total += util_chat_json_unread_count($orderChat, 'client');
                        continue;
                    }
                    $total += (int)($db->run(
                        "SELECT COUNT(*) AS cnt
                           FROM chat_messages
                          WHERE order_id = ?
                            AND sender IN ('booster', 'admin')
                            AND seen = 0
                            AND COALESCE(deleted, 0) = 0",
                        $orderId
                    )[0]['cnt'] ?? 0);
                }
            } catch (\Throwable $e) {}
        }

        $chatDir = (defined('SYS_PATH') ? SYS_PATH : dirname(__DIR__)) . '/public/uploads/private/chat';
        if (!is_dir($chatDir)) return $total;

        $belongsToClient = static function (array $data, array $messages) use ($clientId): bool {
            if ((int)($data['client_id'] ?? 0) === $clientId) return true;
            foreach ($messages as $m) {
                if (!is_array($m) || !empty($m['deleted'])) continue;
                $sender = strtolower(trim((string)($m['sender'] ?? $m['sender_type'] ?? $m['type'] ?? '')));
                if ($sender === 'client' && (int)($m['sender_id'] ?? 0) === $clientId) return true;
            }
            return false;
        };

        // Account / item purchase / top-up chats all share the "selling_*.json" naming.
        foreach (glob($chatDir . '/selling_*.json') ?: [] as $chatFile) {
            $data = json_decode(@file_get_contents($chatFile) ?: '', true);
            if (!is_array($data) || empty($data['messages']) || !is_array($data['messages'])) continue;
            if (!$belongsToClient($data, array_values($data['messages']))) continue;
            $total += util_chat_json_unread_count($chatFile, 'client');
        }

        // Premium account chat (client <-> admin) uses "accounts_*.json".
        foreach (glob($chatDir . '/accounts_*.json') ?: [] as $chatFile) {
            $data = json_decode(@file_get_contents($chatFile) ?: '', true);
            if (!is_array($data) || empty($data['messages']) || !is_array($data['messages'])) continue;
            if (!$belongsToClient($data, array_values($data['messages']))) continue;
            $total += util_chat_json_unread_count($chatFile, 'client');
        }

        return $total;
    }
}

if (!function_exists('util_seller_chat_unread_count')) {
    // Canonical unread count for a seller: account/item/top-up chat JSON files that belong to
    // this seller's own listings/purchases, plus the (currently unused, kept for forward-compat)
    // seller_chat_messages/seller_conversations DB table. Mirrors util_client_chat_unread_count.
    function util_seller_chat_unread_count(int $sellerId): int
    {
        if ($sellerId <= 0 || !defined('SYS_PATH') || !function_exists('db_get_rows')) return 0;
        global $db;
        if (!isset($db) || !is_object($db)) return 0;
        $total = 0;
        $chatDir = SYS_PATH . '/public/uploads/private/chat';

        $countThread = static function(string $type, int $id, string $path) use ($sellerId, $db): int {
            if ($id <= 0) return 0;
            try {
                $key = 'seller:' . $type . ':' . $id;
                if (function_exists('lb_legacy_chat_open')) {
                    lb_legacy_chat_open($key, $type, $id, ['seller_id' => $sellerId, 'ref_type' => $type, 'ref_id' => $id], $path);
                }
                $thread = $db->row("SELECT id FROM legacy_chat_threads WHERE chat_key=? AND seller_id=? LIMIT 1", $key, $sellerId);
                if (!empty($thread['id'])) {
                    $rows = $db->run("SELECT COUNT(*) AS cnt FROM legacy_chat_messages WHERE thread_id=? AND sender_type='client' AND seen_by_seller=0 AND deleted=0", (int)$thread['id']) ?: [];
                    return (int)($rows[0]['cnt'] ?? 0);
                }
            } catch (\Throwable $e) {}
            return is_file($path) ? util_chat_json_unread_count($path, 'seller') : 0;
        };

        foreach (db_get_rows('selling_accounts', ['seller_id' => $sellerId, 'select' => 'id']) ?: [] as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) continue;
            $total += $countThread('account', $id, $chatDir . '/selling_' . sha1('selling_account_' . $id) . '.json');
        }
        foreach (db_get_rows('selling_item_purchases', ['seller_id' => $sellerId, 'select' => 'id']) ?: [] as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) continue;
            $total += $countThread('item_purchase', $id, $chatDir . '/selling_' . sha1('selling_item_purchase_' . $id) . '.json');
        }
        foreach (db_get_rows('selling_topup_purchases', ['seller_id' => $sellerId, 'select' => 'id']) ?: [] as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) continue;
            $total += $countThread('topup_purchase', $id, $chatDir . '/selling_' . sha1('selling_topup_purchase_' . $id) . '.json');
        }
        foreach (db_get_rows('digital_goods', ['seller_id' => $sellerId, 'select' => 'id']) ?: [] as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) continue;
            $total += $countThread('digital_good', $id, $chatDir . '/selling_' . sha1('digital_good_' . $id) . '.json');
        }

        return $total;
    }
}

function util_format_boost_status($boost_status)
{
    $status_class = str_replace(['UNPAID', 'PAID', 'IN_PROGRESS', 'COMPLETED', 'PAUSED', 'CANCELLED', 'REFUND', 'REFUNDED'], ['danger', 'success', 'primary', 'light', 'warning', 'danger', 'info', 'info'], $boost_status);
    $boost_status = str_replace(['UNPAID', 'PAID', 'IN_PROGRESS', 'COMPLETED', 'PAUSED', 'CANCELLED', 'REFUND', 'REFUNDED'], ['Unpaid', 'Processing', 'In Progress', 'Completed', 'Paused', 'Cancelled', 'Refunded', 'Refunded'], $boost_status);
    return '<span class="badge text-bg-' . $status_class . '"</span>' . $boost_status;
    // return '<span class="badge fw-bold me-auto px-4 py-3 badge-light-' . $status_class . '">' . $boost_status . '</span>';
}
function util_format_invoice_status($invoice_status)
{
    $status_class = str_replace(["UNPAID", "PAID"], ['danger', 'success'], $invoice_status);
    $invoice_status = str_replace(["UNPAID", "PAID"], ['Unpaid', 'Paid'], $invoice_status);
    return '<span class="badge badge-' . $status_class . '">' . $invoice_status . '</span>';
}
function util_format_tx_status($tx_status)
{
    $status_class = str_replace(["failed", "pending", "succeeded"], ['danger', 'warning', 'success'], $tx_status);
    return '<span class="legend-indicator bg-' . $status_class . '"></span>' . ucfirst($tx_status);
}
function util_format_smurf_pkg_status($pkg_status)
{
    $status_class = str_replace([0, 1], ['danger', 'success'], $pkg_status);
    $pkg_status = str_replace([0, 1], ['Disabled', 'Enabled'], $pkg_status);
    return '<span class="legend-indicator bg-' . $status_class . '"></span>' . $pkg_status;
}
function util_format_price_display($price)
{
    $price = number_format($price / 100, 2, '.', ',');
    return $price;
}

/**
 * Insurance reserve (Frozen) helpers for Booster payouts
 * - insurance_required_amount: stored in boosters table in cents (INT). NULL => default 25€.
 * - Frozen is a permanent reserve: frozen = min(balance, required)
 * - Available for payout: available = max(balance - required, 0)
 */
function booster_insurance_required_cents($boosterData = null)
{
    if ($boosterData === null) {
        $boosterData = defined('BOOSTER_DATA') ? BOOSTER_DATA : [];
    }
    // Optional per-booster override in DB (cents)
    if (isset($boosterData['insurance_required_amount']) && $boosterData['insurance_required_amount'] !== null && $boosterData['insurance_required_amount'] !== '') {
        $req = (int)$boosterData['insurance_required_amount'];
        if ($req < 0) $req = 0;
        return $req;
    }
    // Default: 25€
    return 2500;
}

function booster_insurance_frozen_cents($boosterData = null)
{
    if ($boosterData === null) {
        $boosterData = defined('BOOSTER_DATA') ? BOOSTER_DATA : [];
    }
    $balance = (int)($boosterData['balance'] ?? 0);
    $req = booster_insurance_required_cents($boosterData);
    if ($balance <= 0) return 0;
    return min($balance, $req);
}

function booster_available_for_payout_cents($boosterData = null)
{
    if ($boosterData === null) {
        $boosterData = defined('BOOSTER_DATA') ? BOOSTER_DATA : [];
    }
    $balance = (int)($boosterData['balance'] ?? 0);
    $req = booster_insurance_required_cents($boosterData);
    $available = $balance - $req;
    return $available > 0 ? $available : 0;
}

function booster_balance_tooltip_html($boosterData = null)
{
    $frozen = booster_insurance_frozen_cents($boosterData);
    $available = booster_available_for_payout_cents($boosterData);

    return 'Frozen: <b>' . util_format_price_display($frozen) . ' EUR</b> Insurance'
        . '<br>'
        . 'Available for payout: <b>' . util_format_price_display($available) . ' EUR</b>';
}

function util_format_discount_display($order)
{
    $discount_data = util_load_discount_data($order);
    if ($discount_data != null) {
        return $discount_data['code'];
    } else {
        return "No Discount";
    }
}
function util_format_price_input($price)
{
    $price = number_format($price / 100, 2, '.', '');
    return $price;
}
function util_format_currency_display($currency)
{
    $currency = str_replace(['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CNY', 'RUB'], ['$', '€', '£', 'C$', 'A$', '¥', '¥', '₽'], $currency);
    return $currency;
}

function selling_account_status_key(array $account): string
{
    $sold = (int)($account['sold'] ?? 0);
    $active = (int)($account['active'] ?? 1) === 1;

    if ($sold === 2) {
        return 'refunded';
    }

    if ($sold === 1 || !empty($account['sold_at'])) {
        return 'sold';
    }

    return $active ? 'listed' : 'unlisted';
}

function selling_account_status_label(array $account): string
{
    $map = [
        'listed' => 'Listed',
        'unlisted' => 'Unlisted',
        'sold' => 'Sold',
        'refunded' => 'Refunded',
    ];

    $key = selling_account_status_key($account);
    return $map[$key] ?? 'Listed';
}

function reverse_seller_account_payout(int $account_id, string $sender = 'System', string $sender_type = 'system', ?int $sender_id = null): array
{
    global $db;

    $account = db_get_row('selling_accounts', ['id' => $account_id]);
    if (empty($account)) {
        return ['ok' => false, 'message' => 'Account not found'];
    }

    $seller_id = (int)($account['seller_id'] ?? 0);
    if ($seller_id <= 0) {
        return ['ok' => true, 'reversed_amount' => 0, 'already_reversed' => true];
    }

    $seller = db_get_row('sellers', ['id' => $seller_id]);
    if (empty($seller)) {
        return ['ok' => false, 'message' => 'Seller not found'];
    }

    $paid_row = $db->row(
        "SELECT COALESCE(SUM(amount_cents), 0) AS total_paid
         FROM seller_payments
         WHERE seller_id = ? AND account_id = ? AND type = 'sale_payout'",
        $seller_id,
        $account_id
    );
    $paid_total = (int)($paid_row['total_paid'] ?? 0);

    $reversed_row = $db->row(
        "SELECT COALESCE(SUM(ABS(amount_cents)), 0) AS total_reversed
         FROM seller_payments
         WHERE seller_id = ? AND account_id = ? AND type = 'refund_reversal'",
        $seller_id,
        $account_id
    );
    $reversed_total = (int)($reversed_row['total_reversed'] ?? 0);

    $remaining = $paid_total - $reversed_total;
    if ($remaining <= 0) {
        db_update_row('selling_accounts', ['id' => $account_id], ['seller_paid' => 0]);
        return ['ok' => true, 'reversed_amount' => 0, 'already_reversed' => true];
    }

    $old_balance = (int)($seller['balance'] ?? 0);
    $new_balance = $old_balance - $remaining;

    $db->run("UPDATE sellers SET balance = ? WHERE id = ?", $new_balance, $seller_id);

    db_add_row('seller_payments', [
        'seller_id' => $seller_id,
        'account_id' => $account_id,
        'type' => 'refund_reversal',
        'amount_cents' => -$remaining,
        'note' => "Refund reversal for account #{$account_id}",
        'balance_after' => $new_balance,
    ]);

    db_update_row('selling_accounts', ['id' => $account_id], ['seller_paid' => 0]);
    process_seller_rank($seller_id);

    return ['ok' => true, 'reversed_amount' => $remaining, 'already_reversed' => false];
}


function restore_seller_account_payout(int $account_id): array
{
    global $db;

    $account = db_get_row('selling_accounts', ['id' => $account_id]);
    if (empty($account)) {
        return ['ok' => false, 'message' => 'Account not found'];
    }

    $seller_id = (int)($account['seller_id'] ?? 0);
    if ($seller_id <= 0) {
        return ['ok' => true, 'restored_amount' => 0, 'already_restored' => true];
    }

    $seller = db_get_row('sellers', ['id' => $seller_id]);
    if (empty($seller)) {
        return ['ok' => false, 'message' => 'Seller not found'];
    }

    $refunded_row = $db->row(
        "SELECT COALESCE(SUM(ABS(amount_cents)), 0) AS total_refunded
         FROM seller_payments
         WHERE seller_id = ? AND account_id = ? AND type = 'refund_reversal'",
        $seller_id,
        $account_id
    );
    $refunded_total = (int)($refunded_row['total_refunded'] ?? 0);

    $restored_row = $db->row(
        "SELECT COALESCE(SUM(amount_cents), 0) AS total_restored
         FROM seller_payments
         WHERE seller_id = ? AND account_id = ? AND type = 'refund_restore'",
        $seller_id,
        $account_id
    );
    $restored_total = (int)($restored_row['total_restored'] ?? 0);

    $remaining = $refunded_total - $restored_total;
    if ($remaining <= 0) {
        if ((int)($account['seller_paid'] ?? 0) !== 1) {
            db_update_row('selling_accounts', ['id' => $account_id], ['seller_paid' => 1]);
        }
        return ['ok' => true, 'restored_amount' => 0, 'already_restored' => true];
    }

    $old_balance = (int)($seller['balance'] ?? 0);
    $new_balance = $old_balance + $remaining;

    $db->run("UPDATE sellers SET balance = ? WHERE id = ?", $new_balance, $seller_id);

    db_add_row('seller_payments', [
        'seller_id' => $seller_id,
        'account_id' => $account_id,
        'type' => 'refund_restore',
        'amount_cents' => $remaining,
        'note' => "Refund undo for account #{$account_id}",
        'balance_after' => $new_balance,
    ]);

    db_update_row('selling_accounts', ['id' => $account_id], ['seller_paid' => 1]);
    process_seller_rank($seller_id);

    return ['ok' => true, 'restored_amount' => $remaining, 'already_restored' => false];
}


// function to convert price from dollars to cents
function util_format_price_db($price)
{
    return round($price * 100);
}

function upload_image($file, $path = null)
{
    $file_up = new \Bulletproof\Image($file);

    $storage_path = SYS_PATH . "/public/uploads/" . $path;
    if (!is_dir($storage_path)) {
        mkdir($storage_path, 0755, true);
    }

    // Allowed types
    $allowed = ["png", "jpeg", "jpg", "gif", "webp"];
    $mime = strtolower($file_up->getMime());
    if (!in_array($mime, $allowed)) {
        return false;
    }

    // Optional: hard size limit (8MB)
    if (!empty($file['size']) && (int)$file['size'] > 8 * 1024 * 1024) {
        return false;
    }

    // FORCE a safe filename
    $file_up->setName(uniqid('img_', true));

    // Set folder
    $file_up->setLocation($storage_path);

    // Upload
    $upload = $file_up->upload();
    if (!$upload) {
        return false;
    }

    // Resize/compress AFTER upload (skip GIF to avoid breaking animations)
    $fullPath = $upload->getFullPath();
    if (is_file($fullPath) && in_array($mime, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        chat_image_resize_max($fullPath, 1200, 1200);
    }

    // Convert absolute path to URL
    $file_path = str_replace(SYS_PATH, BASE_URL, $fullPath);
    return $file_path;
}

function chat_image_resize_max(string $filePath, int $maxW = 1200, int $maxH = 1200): void
{
    if (!function_exists('getimagesize') || !function_exists('imagecreatetruecolor')) {
        return; // GD not available
    }

    $info = @getimagesize($filePath);
    if (!$info) return;

    [$w, $h, $type] = $info;
    if ($w <= 0 || $h <= 0) return;

    // Helper: save with correct transparency handling
    $save = function ($img) use ($filePath, $type) {
        if (!$img) return;

        if ($type === IMAGETYPE_PNG) {
            // IMPORTANT: keep alpha channel
            imagealphablending($img, false);
            imagesavealpha($img, true);
            @imagepng($img, $filePath, 6);
        } elseif ($type === IMAGETYPE_WEBP) {
            // IMPORTANT: keep alpha channel (if supported by GD)
            imagealphablending($img, false);
            imagesavealpha($img, true);
            @imagewebp($img, $filePath, 82);
        } elseif ($type === IMAGETYPE_JPEG) {
            @imagejpeg($img, $filePath, 82);
        }
    };

    // Already small enough: just compress a bit (but preserve alpha!)
    if ($w <= $maxW && $h <= $maxH) {
        if ($type === IMAGETYPE_JPEG && function_exists('imagecreatefromjpeg')) {
            $img = @imagecreatefromjpeg($filePath);
            $save($img);
            if ($img) imagedestroy($img);
        } elseif ($type === IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
            $img = @imagecreatefrompng($filePath);
            if ($img) { imagealphablending($img, false); imagesavealpha($img, true); }
            $save($img);
            if ($img) imagedestroy($img);
        } elseif ($type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $img = @imagecreatefromwebp($filePath);
            if ($img) { imagealphablending($img, false); imagesavealpha($img, true); }
            $save($img);
            if ($img) imagedestroy($img);
        }
        return;
    }

    $scale = min($maxW / $w, $maxH / $h);
    $newW = max(1, (int)floor($w * $scale));
    $newH = max(1, (int)floor($h * $scale));

    switch ($type) {
        case IMAGETYPE_JPEG:
            if (!function_exists('imagecreatefromjpeg')) return;
            $src = @imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            if (!function_exists('imagecreatefrompng')) return;
            $src = @imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_WEBP:
            if (!function_exists('imagecreatefromwebp')) return;
            $src = @imagecreatefromwebp($filePath);
            break;
        default:
            return;
    }
    if (!$src) return;

    $dst = imagecreatetruecolor($newW, $newH);

    // Preserve alpha for PNG/WebP (without this, GD fills with black/white background)
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

    $save($dst);

    imagedestroy($src);
    imagedestroy($dst);
}

// function to upload multiple iamges
function upload_multiple_images($files, $path = null)
{
    $storage_path = SYS_PATH . "/public/uploads/" . $path;
    if (!file_exists($storage_path)) {
        mkdir($storage_path, 0755, true);
    }
    $images = [];

    for ($i = 0; $i < count($files['name']); $i++) {
        $arr_file = array(
            "name" => $files['name'][$i],
            "type" => $files['type'][$i],
            "tmp_name" => $files['tmp_name'][$i],
            "error" => $files['error'][$i],
            "size" => $files['size'][$i],
        );

        $file_up = new \Bulletproof\Image($arr_file);
        $file_up->setLocation($storage_path);
        $file_up->setMime(array('jpeg', 'gif', 'jpg', 'png', 'image/webp'));
        $upload = $file_up->upload();
        if ($upload) {
            $images[] = BASE_URL . str_replace(SYS_PATH, '', $upload->getFullPath());
        } else {
            $images[] = $file_up->getError();
        }
    }

    return implode(',', $images);
}

function util_format_default_type($type)
{
    if (!empty($type)) {
        $type = str_replace('_', ' ', $type);
        $parts = explode('/', $type);
        $parts = array_map(function ($part) {
            return ucwords(trim($part));
        }, $parts);
        return implode('/', $parts);
    }
    return null;
}

if (!function_exists('util_format_platform')) {
    function util_format_platform($platform): string
    {
        $value = strtolower(trim((string)$platform));
        $key = preg_replace('/[^a-z0-9]+/', '', $value);
        $labels = [
            'pc' => 'PC',
            'playstation' => 'PlayStation',
            'ps' => 'PlayStation',
            'psn' => 'PlayStation',
            'ps4' => 'PlayStation',
            'ps5' => 'PlayStation',
            'xbox' => 'Xbox',
            'xboxone' => 'Xbox',
            'xboxseries' => 'Xbox',
            'xboxseriesxs' => 'Xbox',
        ];
        return $labels[$key] ?? ($platform !== null ? trim((string)$platform) : '');
    }
}

if (!function_exists('util_format_ranked_marks')) {
    function util_format_ranked_marks($marks): string
    {
        $value = trim((string)$marks);
        if ($value === '' || strtolower($value) === 'none' || $value === '0') return 'None';
        if (preg_match('/^\d+$/', $value)) {
            return $value . ((int)$value === 1 ? ' Mark' : ' Marks');
        }
        return util_format_default_type($value);
    }
}

if (!function_exists('lb_order_view_purchase_fields')) {
    function lb_order_view_purchase_fields(array $order): array
    {
        $game = str_replace('_', '-', strtolower(trim((string)($order['game'] ?? ''))));
        $type = strtolower(trim((string)($order['type'] ?? '')));
        $slug = strtolower(trim((string)($order['slug'] ?? '')));
        $isWin = $type === 'win' || $slug === 'win-boost';
        $fields = [];
        if ($game !== 'counter-strike-2' && trim((string)($order['server'] ?? '')) !== '') {
            $server = function_exists('lb_format_discord_server') ? lb_format_discord_server($order['server']) : util_format_default_type($order['server']);
            $fields[] = ['label' => 'Server', 'value' => $server, 'icon' => 'fa-duotone fa-server'];
        }
        if (trim((string)($order['platform'] ?? '')) !== '') {
            $fields[] = ['label' => 'Platform', 'value' => util_format_platform($order['platform']), 'icon' => 'fa-duotone fa-gamepad-modern'];
        }
        if (trim((string)($order['queue_type'] ?? '')) !== '') {
            $label = 'Queue Type';
            $value = util_format_default_type($order['queue_type']);
            if (in_array($game, ['overwatch', 'overwatch-2', 'ow2'], true)) {
                $label = 'Role';
            } elseif (in_array($game, ['wild-rift', 'wildrift', 'lol-wild-rift'], true)) {
                $label = 'Ranked Marks';
                $value = $isWin ? '' : util_format_ranked_marks($order['queue_type']);
            }
            if ($value !== '') $fields[] = ['label' => $label, 'value' => $value, 'icon' => 'fa-duotone fa-layer-group'];
        }
        if (trim((string)($order['vpn_country'] ?? '')) !== '' && empty($order['is_duo'])) {
            $fields[] = ['label' => 'VPN Country', 'value' => util_format_default_type($order['vpn_country']), 'icon' => 'fa-duotone fa-shield-halved'];
        }
        return $fields;
    }
}

if (!function_exists('lb_order_start_lp_display')) {
    /**
     * "Start LP" label for the order overview (admin / client / booster).
     *
     * order_options.start_lp holds two different things: the LP *band* the customer
     * picked below Master ("0-20", "21-40", ... — historically also stored as the plain
     * lower bound 0/21/41/...) and the exact points value for open ended ranks
     * (LoL Master+, Apex Master RP). A stored 0 is a valid band, so callers must not
     * use empty() to decide whether to render the row — they use this helper instead
     * and skip the row only when it returns an empty string.
     */
    function lb_order_start_lp_display(array $order): string
    {
        if (!array_key_exists('start_lp', $order)) return '';
        $raw = trim((string)($order['start_lp'] ?? ''));
        if ($raw === '') return '';

        $game = strtolower(trim((string)($order['game'] ?? '')));
        $isLolFamily = in_array($game, [
            'lol', 'league-of-legends', 'leagu', 'lol_classic', 'lol-classic',
            'tft', 'teamfight-tactics', 'teamf',
        ], true);

        // Already a band string ("21-40" / "21-40 LP").
        if (preg_match('/\d+\s*-\s*\d+/', $raw)) {
            return stripos($raw, 'lp') !== false ? $raw : ($raw . ' LP');
        }

        if (!is_numeric($raw)) return $raw;

        $lp = (int)$raw;

        if ($isLolFamily && (int)($order['start_tier'] ?? 0) < 8) {
            if ($lp <= 20) return '0-20 LP';
            if ($lp <= 40) return '21-40 LP';
            if ($lp <= 60) return '41-60 LP';
            if ($lp <= 80) return '61-80 LP';
            if ($lp <= 100) return '81-100 LP';
            return $lp . ' LP';
        }

        // Open ended ranks (Master+, Apex RP): 0 simply means "nothing selected".
        return $lp > 0 ? ($lp . ' LP') : '';
    }
}

function util_format_replace_opt($opt)
{
    $new = ["off_region_fee", "champions", "master_plus_roles", "is_priority", "is_streaming", "is_solo_only", "bonus_win_extra_fee", "is_coaching", "is_duo", "agents", "is_hidden_duo", "is_undercover_winrate", "is_moderate_kda"];
    $old = ["Off Region Fee", "Custom Champions", "Master Plus Roles", "Priority Boost", "Streaming", "Solo Only", "Bonus Win Extra Fee", "Voice Chat", "Play With Booster", "Agents", "Hidden Duo", "Undercover Winrate", "Moderate KDA"];
    return str_replace($new, $old, $opt);
}

function util_format_tier($tier, $game, $low = false)
{
    // Normalize to short codes
    $_gn = ['league-of-legends'=>'lol','leagu'=>'lol','leag'=>'lol','league'=>'lol',
            'valorant'=>'val','valor'=>'val','valo'=>'val',
            'teamfight-tactics'=>'tft','teamf'=>'tft','teamfi'=>'tft'];
    $game = $_gn[$game] ?? $game;

    $tier_id_array = [
        'lol' => [
            0 => 'Unranked',
            1 => 'Iron',
            2 => 'Bronze',
            3 => 'Silver',
            4 => 'Gold',
            5 => 'Platinum',
            6 => 'Emerald',
            7 => 'Diamond',
            8 => 'Master',
            9 => 'Grandmaster',
            10 => 'Challenger',
        ],
        'tft' => [
            0 => 'Unranked',
            1 => 'Iron',
            2 => 'Bronze',
            3 => 'Silver',
            4 => 'Gold',
            5 => 'Platinum',
            6 => 'Emerald',
            7 => 'Diamond',
            8 => 'Master',
            9 => 'Grandmaster',
            10 => 'Challenger',
        ],
        'val' => [
            0 => 'Unranked',
            1 => 'Iron',
            2 => 'Bronze',
            3 => 'Silver',
            4 => 'Gold',
            5 => 'Platinum',
            6 => 'Diamond',
            7 => 'Ascendant',
            8 => 'Immortal',
            9 => 'Radiant',
        ],
    ];
    if ($low) {
        return strtolower($tier_id_array[$game][$tier] ?? '');
    }
    return $tier_id_array[$game][$tier] ?? null;
}

function util_format_rank_display($game, $tier, $division, $lp = null, $rr = null)
{
    $_gn = ['league-of-legends'=>'lol','leagu'=>'lol','leag'=>'lol','valorant'=>'val','valor'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft'];
    $game = $_gn[$game] ?? $game;
    if (in_array(strtolower(trim((string)$game)), ['lol_classic', 'lol-classic', 'league-of-legends-classic'], true)) {
        $tierId = (int)$tier;
        $rank = util_lol_classic_rank_name($tierId);

        // LoL Classic tiers Salt through Diamond use divisions IV through I.
        // Unranked and Legend do not have a division.
        if ($tierId >= 1 && $tierId <= 6 && $division !== null && $division !== '') {
            if (in_array((int)$division, [1, 2, 3, 4], true)) {
                $rank .= ' ' . util_format_lol_classic_division((int)$division);
            }
        }

        return trim($rank);
    }
    // NOTE: $game is normalized above to lol/tft/val, so compare against the
    // normalized keys (comparing to the long names here was dead code and hid the
    // LP/RR display for Master+ ranks).
    if ($game === 'lol' || $game === 'tft') {
        if ($lp != null && $tier > 7) {
            return util_format_tier($tier, $game) . ' ' . $lp . ' LP';
        }
    } elseif ($game === 'val') {
        if ($rr != null && $tier > 7) {
            return util_format_tier($tier, $game) . ' ' . $rr . ' RR';
        }
    }

    $tier = util_format_tier($tier, $game);
    $division = util_format_division($division, $game);

    return $tier . ' ' . $division;
}

function util_format_arena_display($rank)
{
    $ranks = [
        1 => 'Wood',
        2 => 'Bronze',
        3 => 'Silver',
        4 => 'Gold',
        5 => 'Gladiator',
    ];

    return $ranks[$rank];
}

function util_format_game($game)
{
    $game_id_array = [
        'league-of-legends' => 'League of Legends',
        'valorant' => 'Valorant',
        'teamfight-tactics' => 'Teamfight Tactics',
        'wow' => 'World of Warcraft',
    ];
    return $game_id_array[$game];
}

function util_format_boost_type($game, $name)
{
    $iconGame = $game;

    return '<div class="d-flex align-items-center"><div class="symbol symbol-40px w-40px bg-light bg-hover-light-primary overflow-hidden me-2"><img src="' . ASSET_URL . '/global/games/' . $iconGame . '.svg" alt="' . $iconGame . '" class="p-2"></div><div class="ms-2"><span" class="text-gray-800 fw-bold">' . $name . '</span></div></div>';
}

// ═══════════════════════════════════════════════════════════════════════════
// DYNAMIC / GENERIC GAME RANK SYSTEM
// Boost forms for games other than lol/val/tft store their own rank names,
// rank icons and division config inside the form's pricing JSON (set via the
// admin "Boost Form Editor" at /admin-area/games/{id}/boost-forms). This was
// previously only consumed by the live boost-form page (order-summary-multigame.php).
// These helpers are promoted to always-available globals so checkout, the
// admin/booster/client order lists and order detail pages can render the
// correct rank name + icon for any dynamically added game as well.
// ═══════════════════════════════════════════════════════════════════════════
if (!function_exists('lb_summary_rank_label')) {
    function lb_summary_rank_label($value, $fallback) {
        if (is_array($value)) {
            foreach (['name','label','title','rank','long_name'] as $k) {
                if (isset($value[$k]) && trim((string)$value[$k]) !== '') return trim((string)$value[$k]);
            }
        } elseif (trim((string)$value) !== '') return trim((string)$value);
        return $fallback;
    }
}

if (!function_exists('lb_summary_division_count_from_json')) {
    function lb_summary_division_count_from_json(array $jsonData, int $fallback = 4): int {
        foreach (['division_count','divisions_count','divisionCount','divisions','division'] as $key) {
            if (isset($jsonData[$key]) && is_numeric($jsonData[$key])) {
                $count = (int)$jsonData[$key];
                return ($count >= 0 && $count <= 5) ? $count : $fallback;
            }
        }
        if (!empty($jsonData['main']) && is_array($jsonData['main'])) {
            $keys = array_keys($jsonData['main']);
            $numericKeys = array_values(array_filter($keys, static function ($k) { return is_numeric($k); }));
            if (count($numericKeys) === 1) {
                $count = (int)$numericKeys[0];
                if ($count >= 0 && $count <= 5) return $count;
            }
        }
        return $fallback;
    }
}

if (!function_exists('lb_summary_rank_icon_url')) {
    function lb_summary_rank_icon_url(array $jsonData, string $game, string $size, int $rank): string {
        $normalizedGame = strtolower(trim($game));
        if (in_array($normalizedGame, ['rocket-league', 'rocket_league', 'rl'], true) && $rank === 0) {
            return '/public/assets/website/images/boosting/ranks/rocket-league/unranked.webp';
        }
        if (in_array($normalizedGame, ['marvel-rivals', 'marvel_rivals', 'rivals'], true) && $rank === 6) {
            return '/public/assets/website/images/boosting/ranks/marvel-rivals/grandmaster.webp';
        }
        $sources = [
            $jsonData['rank_icons_' . $size] ?? null,
            $jsonData['rank_icons'] ?? null,
            $jsonData['rank_icon_urls'] ?? null,
            $jsonData['icons'] ?? null,
        ];
        foreach ($sources as $icons) {
            if (!is_array($icons)) continue;
            $raw = $icons[$rank] ?? $icons[(string)$rank] ?? null;
            if (is_array($raw)) $raw = $raw[$size] ?? $raw['url'] ?? $raw['icon'] ?? $raw['src'] ?? null;
            $raw = trim((string)$raw);
            if ($raw === '') continue;
            if (preg_match('~^https?://~i', $raw) || strpos($raw, '/') === 0) return $raw;
            return ASSET_URL . '/' . ltrim($raw, '/');
        }
        return util_rank_img($game, $size, $rank);
    }
}

if (!function_exists('lb_summary_rank_labels_map')) {
    function lb_summary_rank_labels_map(array $jsonData): array {
        $rankLabels = [];
        $rankSource = $jsonData['form_config']['ranks']
            ?? ($jsonData['rank_names'] ?? ($jsonData['rankNames'] ?? ($jsonData['ranks'] ?? ($jsonData['tiers'] ?? []))));
        if (is_array($rankSource)) {
            foreach ($rankSource as $key => $value) {
                $idx = is_numeric($key) ? (int)$key : (count($rankLabels) + 1);
                $rankLabels[$idx] = lb_summary_rank_label($value, 'Tier ' . $idx);
            }
        }
        if (empty($rankLabels) && !empty($jsonData['main']) && is_array($jsonData['main'])) {
            foreach (array_keys($jsonData['main']) as $key) {
                if (is_numeric($key)) $rankLabels[(int)$key] = lb_summary_rank_label($jsonData['main'][$key], 'Tier ' . (int)$key);
            }
        }
        return $rankLabels;
    }
}

if (!function_exists('lb_summary_rank_divs_for_tier')) {
    function lb_summary_rank_divs_for_tier(array $jsonData, int $tier): int {
        $cfg = $jsonData['form_config'] ?? [];
        $rankDivs = $cfg['rank_divs'] ?? [];
        if (is_array($rankDivs)) {
            if (array_key_exists((string)$tier, $rankDivs)) return (int)$rankDivs[(string)$tier];
            if (array_key_exists($tier, $rankDivs)) return (int)$rankDivs[$tier];
        }
        return lb_summary_division_count_from_json($jsonData, 4);
    }
}

if (!function_exists('lb_summary_division_label')) {
    function lb_summary_division_label(int $division, int $totalDivs, string $order = 'desc'): string {
        if ($totalDivs <= 0 || $division <= 0) return '';
        // Mirrors the DIV_DESC map used by the admin Boost Form Editor's JS (divLabel()).
        $maps = [
            3 => ['desc' => [1 => 'III', 2 => 'II', 3 => 'I'], 'asc' => [1 => 'I', 2 => 'II', 3 => 'III']],
            4 => ['desc' => [1 => 'IV', 2 => 'III', 3 => 'II', 4 => 'I'], 'asc' => [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']],
        ];
        $order = ($order === 'asc') ? 'asc' : 'desc';
        if (isset($maps[$totalDivs][$order][$division])) {
            return $maps[$totalDivs][$order][$division];
        }
        $romanValue = ($order === 'asc') ? $division : ($totalDivs - $division + 1);
        $numerals = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
        return $numerals[$romanValue] ?? ('D' . $division);
    }
}

if (!function_exists('lb_summary_rank_display')) {
    function lb_summary_rank_display(array $jsonData, int $tier, $division = null, $points = null): string {
        $labels = lb_summary_rank_labels_map($jsonData);
        if ($tier < 0) return 'Unranked';
        if ($tier === 0) {
            // Placement forms can add their own tier 0 (for example CS2 "New Account").
            // The authoritative per-game config used by panels/checkouts has no tier 0
            // and can otherwise mask the form JSON's explicit label.
            foreach (['rank_names', 'rankNames', 'ranks', 'tiers'] as $sourceKey) {
                $source = $jsonData[$sourceKey] ?? null;
                if (is_array($source) && array_key_exists(0, $source)) {
                    return lb_summary_rank_label($source[0], 'New Account');
                }
            }
            return $labels[0] ?? 'Unranked';
        }
        $name = $labels[$tier] ?? ('Tier ' . $tier);
        $ratingOnly = !empty($jsonData['rating_only']) || !empty($jsonData['form_config']['rating_only']);
        if ($ratingOnly && $points !== null && $points !== '') {
            $label = $jsonData['points_label'] ?? ($jsonData['form_config']['points_label'] ?? 'Rating');
            return number_format((int)$points, 0, '.', ',') . ' ' . $label;
        }
        $divs = lb_summary_rank_divs_for_tier($jsonData, $tier);
        if ($divs <= 0) {
            $label = $jsonData['points_label'] ?? ($jsonData['form_config']['points_label'] ?? 'LP');
            if ($points !== null && $points !== '') {
                return $name . ' ' . $points . ' ' . $label;
            }
            return $name;
        }
        $order = $jsonData['form_config']['div_order'] ?? 'desc';
        $divLabel = lb_summary_division_label((int)$division, $divs, (string)$order);
        return trim($name . ' ' . $divLabel);
    }
}

if (!function_exists('lb_load_boost_form_json_by_id')) {
    // Cached lookup so admin/booster/client order lists don't re-read the pricing
    // JSON file from disk once per row when rendering a dynamic game's order overview.
    function lb_load_boost_form_json_by_id($formId): array {
        static $cache = [];
        $formId = (int)$formId;
        if ($formId <= 0) return [];
        if (array_key_exists($formId, $cache)) return $cache[$formId];
        $json = [];
        try {
            $form = db_get_row('boost_forms', ['id' => $formId, 'select' => 'uuid']);
            if (!empty($form['uuid'])) {
                $json = get_pricing_json($form['uuid']) ?: [];
            }
        } catch (\Throwable $e) {
            $json = [];
        }
        return $cache[$formId] = $json;
    }
}

if (!function_exists('util_game_display_name')) {
    // Human-readable Title Case game name for any slug — lol/val/tft/classic use their
    // established short labels, any dynamically added game falls back to its name from
    // the "Games" admin area (or a Title-Cased version of the slug as a last resort).
    function util_game_display_name(string $gameSlug): string {
        $slug = strtolower(trim($gameSlug));
        $known = [
            'lol' => 'LoL', 'league-of-legends' => 'LoL', 'league' => 'LoL', 'leag' => 'LoL', 'leagu' => 'LoL',
            'lol_classic' => 'LoL Classic', 'lol-classic' => 'LoL Classic',
            'val' => 'VAL', 'valorant' => 'VAL', 'valor' => 'VAL',
            'tft' => 'TFT', 'teamfight-tactics' => 'TFT', 'teamf' => 'TFT',
            'wild-rift' => 'LoL Wild Rift', 'wildrift' => 'LoL Wild Rift', 'lol-wild-rift' => 'LoL Wild Rift',
            'ow2' => 'Overwatch 2', 'overwatch-2' => 'Overwatch 2', 'overwatch' => 'Overwatch 2',
            'rl' => 'Rocket League', 'rocket-league' => 'Rocket League',
            'apex' => 'Apex Legends', 'apex-legends' => 'Apex Legends',
            'rivals' => 'Marvel Rivals', 'marvel-rivals' => 'Marvel Rivals',
        ];
        if (isset($known[$slug])) return $known[$slug];
        if (str_contains($slug, 'classic')) return 'LoL Classic';
        $game = function_exists('util_get_game_by_slug') ? util_get_game_by_slug($slug) : null;
        if (!empty($game['name'])) return $game['name'];
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }
}

if (!function_exists('util_game_icon_url')) {
    // Small game-logo icon URL for any slug, used for the bottom-right game badge shown
    // on order/client list rows. lol/val/tft/classic use the existing static assets;
    // anything else is resolved from the game's own "icon" set in the admin Games area.
    function util_game_icon_url(string $gameSlug): string {
        $slug = strtolower(trim($gameSlug));
        $known = [
            'lol' => '/public/assets/website/images/icons/league-of-legends.png',
            'league-of-legends' => '/public/assets/website/images/icons/league-of-legends.png',
            'val' => '/public/assets/website/images/icons/valorant.png',
            'valorant' => '/public/assets/website/images/icons/valorant.png',
            'tft' => '/public/assets/website/images/icons/teamfight-tactics.png',
            'teamfight-tactics' => '/public/assets/website/images/icons/teamfight-tactics.png',
            'wild-rift' => '/public/assets/website/images/icons/lol-wild-rift.png',
            'wildrift' => '/public/assets/website/images/icons/lol-wild-rift.png',
            'lol-wild-rift' => '/public/assets/website/images/icons/lol-wild-rift.png',
            'ow2' => '/public/assets/website/images/icons/overwatch-2.png',
            'overwatch-2' => '/public/assets/website/images/icons/overwatch-2.png',
            'overwatch' => '/public/assets/website/images/icons/overwatch-2.png',
            'rl' => '/public/assets/website/images/icons/rocket-league.png',
            'rocket-league' => '/public/assets/website/images/icons/rocket-league.png',
            'apex' => '/public/assets/website/images/icons/apex-legends.png',
            'apex-legends' => '/public/assets/website/images/icons/apex-legends.png',
            'rivals' => '/public/assets/website/images/icons/marvel-rivals.png',
            'marvel-rivals' => '/public/assets/website/images/icons/marvel-rivals.png',
        ];
        if (isset($known[$slug])) return $known[$slug];
        if (str_contains($slug, 'classic')) return '/public/assets/website/images/icons/lol-classic.png';
        $game = function_exists('util_get_game_by_slug') ? util_get_game_by_slug($slug) : null;
        $icon = trim((string)($game['icon'] ?? ''));
        if ($icon !== '') {
            if (preg_match('~^https?://~i', $icon) || strpos($icon, '/') === 0) return $icon;
            return (defined('ASSET_URL') ? ASSET_URL : '') . '/' . ltrim($icon, '/');
        }

        // The icon set ships one file per game slug, so a game without an explicit
        // icon in the admin Games area still resolves instead of falling back to text.
        if ($slug !== '' && defined('SYS_PATH')) {
            $relative = 'website/images/icons/' . $slug . '.png';
            if (is_file(rtrim((string)SYS_PATH, '/\\') . '/public/assets/' . $relative)) {
                return (defined('ASSET_URL') ? ASSET_URL : '') . '/' . $relative;
            }

            // The large dynamic game catalogue stores its thumbnails separately.
            // Reuse those images in admin filters and tables when no dedicated
            // compact icon was configured.
            foreach (['png', 'webp', 'jpg', 'jpeg'] as $extension) {
                $relative = 'website/images/game-icons/' . $slug . '.' . $extension;
                if (is_file(rtrim((string)SYS_PATH, '/\\') . '/public/assets/' . $relative)) {
                    return (defined('ASSET_URL') ? ASSET_URL : '') . '/' . $relative;
                }
            }
        }

        return '';
    }
}

if (!function_exists('lb_booster_game_profiles_ensure')) {
    function lb_booster_game_profiles_ensure(): void {
        static $done = false;
        if ($done) return;
        $done = true;
        global $db;
        try {
            $db->run("CREATE TABLE IF NOT EXISTS booster_game_profiles (
                booster_id INT UNSIGNED NOT NULL,
                game_slug VARCHAR(100) NOT NULL,
                rank_tier INT NOT NULL DEFAULT 0,
                rank_division INT NOT NULL DEFAULT 0,
                specialties TEXT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (booster_id, game_slug),
                KEY idx_game_slug (game_slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {}
    }
}

if (!function_exists('lb_booster_game_profiles')) {
    function lb_booster_game_profiles(int $boosterId): array {
        if ($boosterId <= 0) return [];
        lb_booster_game_profiles_ensure();
        global $db;
        $result = [];
        try {
            foreach ((array)$db->run("SELECT game_slug, rank_tier, rank_division, specialties FROM booster_game_profiles WHERE booster_id = ?", $boosterId) as $row) {
                $slug = strtolower(trim((string)($row['game_slug'] ?? '')));
                if ($slug === '') continue;
                $row['specialties'] = array_values(array_filter(array_map('trim', explode('|', (string)($row['specialties'] ?? '')))));
                $result[$slug] = $row;
            }
        } catch (Throwable $e) {}
        return $result;
    }
}

if (!function_exists('lb_booster_game_profiles_save')) {
    function lb_booster_game_profiles_save(int $boosterId, array $profiles): void {
        if ($boosterId <= 0) return;
        lb_booster_game_profiles_ensure();
        global $db;
        foreach ($profiles as $gameSlug => $profile) {
            $gameSlug = strtolower(trim((string)$gameSlug));
            if ($gameSlug === '' || !is_array($profile)) continue;
            $tier = max(0, (int)($profile['rank_tier'] ?? 0));
            $division = max(0, (int)($profile['rank_division'] ?? 0));
            $specialties = array_slice(array_values(array_unique(array_filter(array_map(
                static fn($value) => preg_replace('/[^a-z0-9_-]/i', '', trim((string)$value)),
                (array)($profile['specialties'] ?? [])
            )))), 0, 20);
            $db->run(
                "INSERT INTO booster_game_profiles (booster_id, game_slug, rank_tier, rank_division, specialties)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rank_tier=VALUES(rank_tier), rank_division=VALUES(rank_division), specialties=VALUES(specialties)",
                $boosterId, $gameSlug, $tier, $division, implode('|', $specialties)
            );
        }
    }
}

if (!function_exists('lb_booster_game_specialty_options')) {
    function lb_booster_game_specialty_options(string $gameSlug): array {
        $slug = strtolower(trim($gameSlug));
        $folders = [
            'apex-legends' => ['apex-legends/legends', 'Legends'],
            'apex' => ['apex-legends/legends', 'Legends'],
            'marvel-rivals' => ['marvel-rivals/heroes', 'Heroes'],
            'rivals' => ['marvel-rivals/heroes', 'Heroes'],
            'overwatch-2' => ['overwatch-2/heroes', 'Heroes'],
            'ow2' => ['overwatch-2/heroes', 'Heroes'],
        ];
        if (!isset($folders[$slug])) return [];
        [$folder, $label] = $folders[$slug];
        $base = rtrim((string)SYS_PATH, '/\\') . '/public/assets/website/images/boosting/' . $folder;
        $items = [];
        foreach ((array)glob($base . '/*.{webp,png,jpg,jpeg,svg}', GLOB_BRACE) as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $nameKey = preg_replace('/^mr-/', '', $key);
            $items[$key] = [
                'key' => $key,
                'name' => ucwords(str_replace('-', ' ', $nameKey)),
                'icon' => ASSET_URL . '/website/images/boosting/' . $folder . '/' . basename($file),
                'label' => $label,
            ];
        }
        uasort($items, static fn($a, $b) => strcasecmp($a['name'], $b['name']));
        return array_values($items);
    }
}

if (!function_exists('util_format_boost_overview_dynamic')) {
    // Fallback renderer used by util_format_boost_overview() for any game that isn't
    // one of the hardcoded lol/val/tft games, driven by the boost form's own JSON config.
    function util_format_boost_overview_dynamic(array $jsonData, $type, $data): string {
        // Dynamic games store the server as whatever value the admin configured in the Boost Form
        // Editor's server options (often a lowercase full word like "europe"), unlike lol/val/tft
        // which store short region codes (euw, na). Expand known short codes via util_format_server();
        // anything else gets a clean Title Case instead of being shown verbatim ("europe" -> "Europe").
        $serverRaw = trim((string)($data['server'] ?? ''));
        $serverKey = strtolower($serverRaw);
        $server = $serverKey !== '' ? util_format_server($serverKey) : '';
        if ($server !== '' && $server === $serverKey) {
            $server = ucwords(str_replace(['-', '_'], ' ', $serverKey));
        }
        $ratingOnly = !empty($jsonData['rating_only']) || !empty($jsonData['form_config']['rating_only']);
        $prefix = !$ratingOnly && $server !== '' ? ($server . ' - ') : '';
        switch ($type) {
            case 'rank':
                $start = lb_summary_rank_display($jsonData, (int)($data['start_tier'] ?? 0), $data['start_division'] ?? null, $data['start_lp'] ?? $data['start_rr'] ?? null);
                $end = lb_summary_rank_display($jsonData, (int)($data['end_tier'] ?? 0), $data['end_division'] ?? null, $data['end_lp'] ?? $data['end_rr'] ?? null);
                return $prefix . $start . ' > ' . $end;
            case 'win':
                $start = lb_summary_rank_display($jsonData, (int)($data['start_tier'] ?? 0), $data['start_division'] ?? null, $data['start_lp'] ?? null);
                return $prefix . $start . ' > ' . ($data['matches'] ?? 0) . ' Wins';
            case 'placement':
                $start = lb_summary_rank_display($jsonData, (int)($data['start_tier'] ?? 0), $data['start_division'] ?? null);
                return $prefix . $start . ' > ' . ($data['matches'] ?? 0) . ' Placements';
            case 'normal':
                return $prefix . 'Normals Boost > ' . ($data['matches'] ?? 0) . ' Normals';
            case 'match':
                $start = lb_summary_rank_display($jsonData, (int)($data['start_tier'] ?? 0), $data['start_division'] ?? null, $data['start_lp'] ?? null);
                return $prefix . $start . ' > ' . ($data['matches'] ?? 0) . ' Games';
            case 'coaching':
                return $prefix . util_format_default_type($data['coach_type'] ?? null) . ' > ' . ($data['hours'] ?? 0) . ' Hours';
            case 'level':
                return $prefix . 'Level ' . ($data['start_tier'] ?? 0) . ' > Level ' . ($data['end_tier'] ?? 0);
            case 'mastery':
                return $prefix . 'Mastery ' . ($data['start_tier'] ?? 0) . ' > Mastery ' . ($data['end_tier'] ?? 0);
            case 'clash':
                return $prefix . ($data['hours'] ?? 0) . ' Boosters - ' . lb_summary_rank_display($jsonData, (int)($data['start_tier'] ?? 0)) . ' > ' . ($data['matches'] ?? 0) . ' Matches';
            default:
                return $prefix . ucfirst(str_replace(['_', '-'], ' ', $type)) . ' Boost';
        }
    }
}

function util_format_boost_overview($game, $type, $data)
{
    $_gn = ['league-of-legends'=>'lol','leagu'=>'lol','valorant'=>'val','valor'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft'];
    $mappedGame = $_gn[$game] ?? $game;
    $knownGames = ['lol', 'val', 'tft', 'lol_classic', 'lol-classic'];
    if (!in_array($mappedGame, $knownGames, true)) {
        $jsonData = (isset($data['json']) && is_array($data['json'])) ? $data['json'] : [];
        if (empty($jsonData) && !empty($data['form_id'])) {
            $jsonData = lb_load_boost_form_json_by_id($data['form_id']);
        }
        if (!empty($jsonData)) {
            // The stored form JSON can still contain an older rank/division layout.
            // Use the same authoritative game config as the live form so open-ended
            // ranks such as Apex Master include their selected RP in webhook titles.
            $rankConfig = function_exists('lb_generic_game_rank_config')
                ? lb_generic_game_rank_config((string)$mappedGame)
                : null;
            if (is_array($rankConfig)) {
                $jsonData['form_config'] = array_replace(
                    is_array($jsonData['form_config'] ?? null) ? $jsonData['form_config'] : [],
                    $rankConfig
                );
            }
            return util_format_boost_overview_dynamic($jsonData, $type, $data);
        }
    }
    $game = $mappedGame;
    switch ($type) {
        case 'win':
            return strtoupper($data["server"]) . ' - ' . util_format_rank_display($game, $data["start_tier"], $data["start_division"] ?? null, $data['start_lp'] ?? $data['start_rr'] ?? null) . ' > ' . $data["matches"] . ' Wins';
            break;
        case 'placement':
            return strtoupper($data["server"]) . ' - ' . util_format_rank_display($game, $data["start_tier"], $data["start_division"] ?? null) . ' > ' . $data["matches"] . ' Placements';
            break;
        case 'normal':
            return strtoupper($data["server"]) . ' - ' . 'Normals Boost' . ' > ' . $data["matches"] . ' Normals';
            break;
        case 'coaching':
            return strtoupper($data["server"] ?? ' ') . ' - ' . util_format_default_type($data["coach_type"] ?? null) . ' > ' . ($data["hours"] ?? 0) . ' Hours';
            break;
        case 'rank':
            return strtoupper($data["server"]) . ' - ' . util_format_rank_display($game, $data["start_tier"], $data["start_division"] ?? null, $data['start_lp'] ?? null, $data['start_rr'] ?? null) . ' > ' . util_format_rank_display($game, $data["end_tier"], $data["end_division"] ?? null, $data['end_lp'] ?? null, $data['end_rr'] ?? null);
            break;
        case 'match':
            return strtoupper($data["server"]) . ' - ' . util_format_rank_display($game, $data["start_tier"], $data["start_division"] ?? null, $data['start_lp'] ?? null) . ' > ' . $data["matches"] . ' Games';
            break;
        case 'arena':
            return strtoupper($data["server"]) . ' - ' . util_format_arena_display($data['start_tier']) . ' > ' . $data["matches"] . ' Wins';
            break;
        case 'mastery':
            return strtoupper($data["server"]) . ' - Mastery ' . $data["start_tier"] . ' > Mastery ' . $data["end_tier"];
            break;
        case 'clash':
            return strtoupper($data["server"]) . ' - ' . $data["hours"] . ' Boosters - Tier ' . $data["start_tier"] . ' > ' . $data["matches"] . ' Matches';
            break;
        case 'level':
            return strtoupper($data["server"]) . ' - Level ' . $data["start_tier"] . ' > Level ' . $data["end_tier"];
            break;
        case 'double up':
            return strtoupper($data["server"]) . ' - ' . util_format_rank_display($game, $data["start_tier"], $data["start_division"] ?? null, $data['start_lp'] ?? null, $data['start_rr'] ?? null) . ' > ' . util_format_rank_display($game, $data["end_tier"], $data["end_division"] ?? null, $data['end_lp'] ?? null, $data['end_rr'] ?? null);
        case 'pro-games':
            return strtoupper($data["server"] ?? '') . ' - Pro Games ' . util_format_rank_display($game, $data["start_tier"] ?? 1, $data["start_division"] ?? null) . ' > ' . ($data["matches"] ?? 1) . ' Games';
        case 'duo-pass':
            return strtoupper($data["server"] ?? '') . ' - Duo Pass ' . util_format_rank_display($game, $data["start_tier"] ?? 1, $data["start_division"] ?? null) . ' > ' . ($data["hours"] ?? 3) . ' Hours';
        case 'ranked-5s':
        case 'ranked_5s':
            $rankGame = in_array($game, ['lol_classic', 'lol-classic'], true) ? 'lol_classic' : 'lol';
            $startTier = (int)($data['start_tier'] ?? 0);
            $rank = $startTier === 0
                ? 'Unranked'
                : util_format_rank_display(
                    $rankGame,
                    $startTier,
                    $data['start_division'] ?? null,
                    $data['start_lp'] ?? null
                );
            $serverRaw = strtolower(trim((string)($data['server'] ?? '')));
            $serverMap = [
                'eu-west' => 'EUW',
                'euw' => 'EUW',
                'eu-nordic-east' => 'EUNE',
                'eune' => 'EUNE',
                'north-america' => 'NA',
                'na' => 'NA',
                'oceania' => 'OCE',
                'oce' => 'OCE',
                'korea' => 'KR',
                'kr' => 'KR',
            ];
            $serverLabel = $serverMap[$serverRaw] ?? strtoupper((string)($data['server'] ?? ''));
            $games = max(1, (int)($data['matches'] ?? 1));
            $boosters = max(1, min(4, (int)($data['boosters'] ?? $data['ranked_5s_boosters_count'] ?? 1)));
            return $serverLabel . ' - Ranked 5s ' . $rank
                . ' > ' . $games . ' ' . ($games === 1 ? 'Game' : 'Games')
                . ' · ' . $boosters . ' ' . ($boosters === 1 ? 'Booster' : 'Boosters');
        default:
            return strtoupper($data["server"] ?? '') . ' - ' . ucfirst(str_replace(['_', '-'], ' ', $type)) . ' Boost';
    }
}


function util_boost_form_icon_html($icon, $sizeRem = 1.5, $extraClass = 'text-body')
{
    $icon = trim((string) $icon);
    if ($icon === '') {
        return '';
    }

    // Hauptsite SVG-Pfad: /public/assets/website/images/boost-forms/boost-type-icons/<file>.svg
    $svgBaseUrl = defined('ASSET_URL')
        ? (ASSET_URL . '/website/images/boost-forms/boost-type-icons/')
        : '/website/images/boost-forms/boost-type-icons/';

    // Wenn DB-Wert ein SVG-Dateiname ist (z.B. rank-boost.svg)
    if (strtolower(substr($icon, -4)) === '.svg') {
        $safe = basename($icon); // nur Dateiname
        $size = (float) $sizeRem;

        // Macht das SVG über einen Filter weiß (funktioniert am besten bei dunklen/monochromen SVGs)
        $whiteFilter = 'filter:brightness(0) invert(1);';

        return '<img class="boost-form-svg ' . htmlspecialchars($extraClass, ENT_QUOTES) . '" ' .
            'src="' . htmlspecialchars(rtrim($svgBaseUrl, '/') . '/' . $safe, ENT_QUOTES) . '" alt="" ' .
            'style="width:' . $size . 'rem;height:' . $size . 'rem;display:block;' . $whiteFilter . '">';
    }

    // Fallback: alter Flow (FontAwesome Klassen in DB)
    // $cls = trim('fa-duotone ' . $extraClass . ' ' . $icon);
    // return '<i class="' . htmlspecialchars($cls, ENT_QUOTES) . '"></i>';
}

function util_format_boost_form($data)
{
    // game label — normalize all known slugs/shortcodes
    $_gNorm = [
        'lol' => 'LoL', 'league-of-legends' => 'LoL', 'league' => 'LoL', 'leag' => 'LoL', 'leagu' => 'LoL',
        'val' => 'VAL', 'valorant' => 'VAL', 'valor' => 'VAL', 'valo' => 'VAL',
        'tft' => 'TFT', 'teamfight-tactics' => 'TFT', 'teamf' => 'TFT', 'teamfi' => 'TFT',
        'wow' => 'WoW', 'cod' => 'CoD', 'call-of-duty' => 'CoD',
        'ow2' => 'OW2', 'overwatch-2' => 'OW2', 'rl' => 'RL', 'rocket-league' => 'RL',
        'apex' => 'Apex', 'apex-legends' => 'Apex', 'rivals' => 'Rivals', 'marvel-rivals' => 'Rivals',
    ];
    $_gameRaw = strtolower(trim((string)($data['game'] ?? '')));
    $game = $_gNorm[$_gameRaw] ?? (function_exists('util_game_display_name') ? util_game_display_name($_gameRaw) : strtoupper((string)($data['game'] ?? 'LoL')));

    if (defined('ADMIN_DATA') && ADMIN_DATA) {
        $order_link = '/admin-area/order/' . $data['id'];
    } elseif (defined('BOOSTER_DATA') && BOOSTER_DATA) {
        $order_link = '/booster-area/order/' . $data['id'];
    } else {
        $order_link = '/order/' . $data['id'];
    }


    $icon_html = util_boost_form_icon_html($data['icon'] ?? '', 1.5, 'text-body');

    return '<a class="d-flex align-items-center justify-content-between" href="' . $order_link . '">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-light avatar-rounded">
                <span class="avatar-initials">
                    ' . $icon_html . '
                </span>
            </div>
            <div class="ms-3">
                <span class="d-block text-body h4 mb-0 fw-bold">' . util_format_boost_overview($data['game'], $data['type'], $data) . '</span>
                <small class="text-muted">' . $game . " " . $data['name'] . '</small>
            </div>
        </div>
    </a>';
}

function util_format_user($name, $icon)
{
    if (empty($icon)) {
        $icon = ASSET_URL . '/core/main/img/logos/PNG/icon-bg.png';
    }

    return '<div class="d-flex align-items-center"><div class="avatar rounded-circle me-1"><img class="avatar-img rounded-circle" src="' . $icon . '" alt="' . $name . '"></div><div class="ms-1"><span" class="">' . $name . '</span"></div></div>';
}

function util_format_game_short($game)
{
    $_norm = [
        'lol' => 'lol', 'league-of-legends' => 'lol', 'league' => 'lol', 'leag' => 'lol', 'leagu' => 'lol',
        'val' => 'val', 'valorant' => 'val', 'valor' => 'val', 'valo' => 'val',
        'tft' => 'tft', 'teamfight-tactics' => 'tft', 'teamf' => 'tft',
        'wow' => 'wow', 'cod' => 'cod', 'call-of-duty' => 'cod',
        'ow2' => 'ow2', 'overwatch-2' => 'ow2', 'rl' => 'rl', 'rocket-league' => 'rl',
        'apex' => 'apex', 'apex-legends' => 'apex', 'rivals' => 'rivals', 'marvel-rivals' => 'rivals',
    ];
    $g = $_norm[strtolower(trim((string)$game))] ?? null;

    $game_id_array = [
        'lol'    => 'LoL',
        'val'    => 'Val',
        'tft'    => 'TFT',
        'wow'    => 'WoW',
        'cod'    => 'CoD',
        'ow2'    => 'OW2',
        'rl'     => 'RL',
        'apex'   => 'Apex',
        'rivals' => 'Rivals',
    ];
    return $game_id_array[$g ?? ''] ?? strtoupper((string)$game);
}

function util_format_division($division, $game, $low = false)
{
    $_gn = ['league-of-legends'=>'lol','leagu'=>'lol','valorant'=>'val','valor'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft'];
    $game = $_gn[$game] ?? $game;
    $division_id_array = [
        'lol' => [
            4 => 'I',
            3 => 'II',
            2 => 'III',
            1 => 'IV',
        ],
        'tft' => [
            4 => 'I',
            3 => 'II',
            2 => 'III',
            1 => 'IV',
        ],
        'valorant' => [
            3 => 'III',
            2 => 'II',
            1 => 'I',
        ]
    ];
    if (isset($division_id_array[$game][$division])) {
        return $division_id_array[$game][$division];
    } else {
        return $division;
    }
}
function util_format_tagify($data)
{
    return htmlspecialchars(implode(', ', array_column(json_decode($data), 'value')));
}
function util_format_region_from_server($server)
{
    $server_id_array = ['euw', 'eune', 'eu', 'ru', 'tr'];
    if (in_array($server, $server_id_array)) {
        return 'eu';
    } else {
        return 'na';
    }
}
function util_format_rank_img($tier, $division, $game, $res = "full")
{
    $_gn = ['league-of-legends'=>'lol','leagu'=>'lol','valorant'=>'val','valor'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft'];
    $game = $_gn[$game] ?? $game;
    switch ($game) {
        case 'league-of-legends':
        case 'teamfight-tactics':
            return ASSET_URL . "/core/main/img/$game/ranks/$res/$tier.png";
            break;
        case 'valorant':
            if ($res == "mini") {
                return ASSET_URL . "/core/main/img/$game/ranks/mini/$tier.png";
            } else {
                return ASSET_URL . "/core/main/img/$game/ranks/$res/$tier$division.png";
            }
            break;
    }
}

function db_load_boost_forms()
{
    global $db;
    $query = 'SELECT * FROM boost_forms';
    $row = $db->run($query);
    return $row;
}

function db_load_boost_form($id)
{
    $row = db_get_row('boost_forms', ['id' => $id]);
    if ($row != false) {
        $row['json'] = load_boost_form_json($row['uuid']);
        // Normalize game slug to short code for view templates
        $_s2s = ['league-of-legends'=>'lol','leagu'=>'lol','leag'=>'lol','valorant'=>'val','valor'=>'val','valo'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft','teamfi'=>'tft'];
        if (isset($row['game'], $_s2s[$row['game']])) {
            $row['game'] = $_s2s[$row['game']];
        }
        return $row;
    } else {
        return false;
    }
}

function load_boost_form_json($uuid)
{
    return get_pricing_json($uuid);
}


function get_pricing_json($uuid)
{
    $path = SYS_PATH . "/public/uploads/private/boost-forms/$uuid.json";
    // Deleted / never-uploaded pricing files are a normal state for old boost forms.
    // Return null instead of warning so callers can fall back.
    if ($uuid === '' || !is_file($path) || !is_readable($path)) {
        return null;
    }
    $pricing_json = @file_get_contents($path);
    if ($pricing_json === false) {
        return null;
    }
    return json_decode($pricing_json, true);
}

function format_new_pricing_data($game, $new_data, $data)
{
    $lp_array = ["0-20", "21-40", "41-60", "61-80", "81-100"];
    $rr_array = ["0-20", "21-40", "41-60", "61-80", "81-100"];
    $rank_lp_gain_array = ["30+", "25-29", "20-24", "10-19"];
    $win_lp_gain_array = ["30+", "25-29", "20-24", "10-19"];

    $extra_opts_array = ["is_duo", "champions", "agents", "is_priority", "is_streaming", "is_solo_only", "bonus_win_extra_fee", "is_coaching", "is_hidden_duo", "is_undercover_winrate", "is_moderate_kda"];

    // Ensure new privacy options exist in JSON data before saving (auto-migration)
    if (isset($data['extra'])) {
        if (!isset($data['extra']['is_undercover_winrate'])) {
            $data['extra']['is_undercover_winrate'] = 0.40;
        }
        if (!isset($data['extra']['is_moderate_kda'])) {
            $data['extra']['is_moderate_kda'] = 0.30;
        }
    }

    if ($game === 'league-of-legends') {
        switch ($new_data['slug']) {
            case 'rank-boost':
                #iron to diamond pricing
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 4; $y++) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }
                #masters pricing
                for ($y = 100; $y <= 1500; $y += 100) {
                    $data['main'][8][$y]['eu'] = isset($new_data["eu-8-" . $y]) ? util_format_price_db($new_data["eu-8-" . $y]) : $data['main'][8][$y]['eu'];

                    $data['main'][8][$y]['na'] = isset($new_data["na-8-" . $y]) ? util_format_price_db($new_data["na-8-" . $y]) : $data['main'][8][$y]['na'];
                }
                #LP discount pricing
                foreach ($lp_array as $lp) {
                    $data['start_lp'][$lp] = isset($new_data["lp-" . $lp]) ? util_format_price_input($new_data["lp-" . $lp]) : $data['start_lp'][$lp];
                }
                #LP gain pricing
                foreach ($rank_lp_gain_array as $lp) {
                    $data['lp_gain'][$lp] = isset($new_data["lp-gain-" . $lp]) ? util_format_price_input($new_data["lp-gain-" . $lp]) : $data['lp_gain'][$lp];
                }
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'win-boost':
                #iron to diamond pricing
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 4; $y++) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }

                #maters pricing
                for ($y = 100; $y <= 1500; $y += 100) {
                    $data['main'][8][$y]['eu'] = isset($new_data["eu-8-" . $y]) ? util_format_price_db($new_data["eu-8-" . $y]) : $data['main'][8][$y]['eu'];

                    $data['main'][8][$y]['na'] = isset($new_data["na-8-" . $y]) ? util_format_price_db($new_data["na-8-" . $y]) : $data['main'][8][$y]['na'];
                }
                for ($i = 8; $i < 10; $i++) {
                    $data['main'][$i]['eu'] = isset($new_data["eu-" . $i]) ? util_format_price_db($new_data["eu-" . $i]) : $data['main'][$i]['eu'];

                    $data['main'][$i]['na'] = isset($new_data["na-" . $i]) ? util_format_price_db($new_data["na-" . $i]) : $data['main'][$i]['na'];
                }

                # LP gain pricing
                foreach ($win_lp_gain_array as $lp) {
                    $data['lp_gain'][$lp] = isset($new_data["lp-gain-" . $lp]) ? util_format_price_input($new_data["lp-gain-" . $lp]) : $data['lp_gain'][$lp];
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'matches-boost':
                #iron to diamond pricing
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 4; $y++) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }

                #maters pricing
                for ($y = 100; $y <= 1500; $y += 100) {
                    $data['main'][8][$y]['eu'] = isset($new_data["eu-8-" . $y]) ? util_format_price_db($new_data["eu-8-" . $y]) : $data['main'][8][$y]['eu'];

                    $data['main'][8][$y]['na'] = isset($new_data["na-8-" . $y]) ? util_format_price_db($new_data["na-8-" . $y]) : $data['main'][8][$y]['na'];
                }
                for ($i = 8; $i < 10; $i++) {
                    $data['main'][$i]['eu'] = isset($new_data["eu-" . $i]) ? util_format_price_db($new_data["eu-" . $i]) : $data['main'][$i]['eu'];

                    $data['main'][$i]['na'] = isset($new_data["na-" . $i]) ? util_format_price_db($new_data["na-" . $i]) : $data['main'][$i]['na'];
                }

                # LP gain pricing
                foreach ($win_lp_gain_array as $lp) {
                    $data['lp_gain'][$lp] = isset($new_data["lp-gain-" . $lp]) ? util_format_price_input($new_data["lp-gain-" . $lp]) : $data['lp_gain'][$lp];
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'placements-boost':
                #unranked pricing
                $data['main']["0"]['eu'] = isset($new_data["eu-0"]) ? util_format_price_db($new_data["eu-0"]) : $data['main']["0"]['eu'];
                $data['main']["0"]['na'] = isset($new_data["na-0"]) ? util_format_price_db($new_data["na-0"]) : $data['main']["0"]['na'];

                #iron to diamond pricing
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 4; $y++) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }
                for ($i = 8; $i < 11; $i++) {
                    $data['main'][$i]['eu'] = isset($new_data["eu-" . $i]) ? util_format_price_db($new_data["eu-" . $i]) : $data['main'][$i]['eu'];

                    $data['main'][$i]['na'] = isset($new_data["na-" . $i]) ? util_format_price_db($new_data["na-" . $i]) : $data['main'][$i]['na'];
                }
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'normals-boost':
                $data['main']["summoners_rift"]['eu'] = isset($new_data["eu-summoners_rift"]) ? util_format_price_db($new_data["eu-summoners_rift"]) : $data['main']["summoners_rift"]['eu'];
                $data['main']["summoners_rift"]['na'] = isset($new_data["na-summoners_rift"]) ? util_format_price_db($new_data["na-summoners_rift"]) : $data['main']["summoners_rift"]['na'];

                $data['main']["aram"]['eu'] = isset($new_data["eu-aram"]) ? util_format_price_db($new_data["eu-aram"]) : $data['main']["aram"]['eu'];
                $data['main']["aram"]['na'] = isset($new_data["na-aram"]) ? util_format_price_db($new_data["na-aram"]) : $data['main']["aram"]['na'];

                $data['main']["featured"]['eu'] = isset($new_data["eu-featured"]) ? util_format_price_db($new_data["eu-featured"]) : $data['main']["featured"]['eu'];
                $data['main']["featured"]['na'] = isset($new_data["na-featured"]) ? util_format_price_db($new_data["na-featured"]) : $data['main']["featured"]['na'];
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'duo-pass':
                foreach ($data['main'] as $rank => $durations) {
                    foreach ([3, 6, 8] as $duration) {
                        if (isset($data['main'][$rank][$duration]['eu'])) {
                            $data['main'][$rank][$duration]['eu'] = isset($new_data["eu-" . $rank . "-" . $duration])
                                ? util_format_price_db($new_data["eu-" . $rank . "-" . $duration])
                                : $data['main'][$rank][$duration]['eu'];
                        }

                        if (isset($data['main'][$rank][$duration]['na'])) {
                            $data['main'][$rank][$duration]['na'] = isset($new_data["na-" . $rank . "-" . $duration])
                                ? util_format_price_db($new_data["na-" . $rank . "-" . $duration])
                                : $data['main'][$rank][$duration]['na'];
                        }
                    }
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                if (isset($new_data['completion-time'])) {
                    $data['completion_time'] = max(1, (int) $new_data['completion-time']);
                }

                if (isset($data['options']) && is_array($data['options'])) {
                    foreach ($data['options'] as $key => $value) {
                        if (isset($new_data["option-" . $key])) {
                            $data['options'][$key] = $new_data["option-" . $key];
                        }
                    }
                }

                break;

            case 'coaching':
                $data['main']["copilot"] = isset($new_data["copilot"]) ? util_format_price_db($new_data["copilot"]) : $data['main']["copilot"];

                $data['main']["vodreview"] = isset($new_data["vodreview"]) ? util_format_price_db($new_data["vodreview"]) : $data['main']["vodreview"];
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;

            case 'arena-boost':
                for ($i = 1; $i <= 5; $i++) {
                    $data["main"]["$i"] = [
                        "eu" => isset($new_data["eu-" . $i]) ? util_format_price_db($new_data["eu-" . $i]) : $data['main'][$i]['eu'],
                        "na" => isset($new_data["na-" . $i]) ? util_format_price_db($new_data["na-" . $i]) : $data['main'][$i]['na'],
                    ];
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;

            case 'champion-mastery':
                for ($i = 1; $i < 10; $i++) {
                    $keys = array_keys($data['main'][$i]);
                    foreach ($keys as $y) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'clash-boost':
                for ($i = 1; $i <= 4; $i++) {
                    $data["main"]["$i"] = [
                        "eu" => isset($new_data["eu-" . $i]) ? util_format_price_db($new_data["eu-" . $i]) : $data['main'][$i]['eu'],
                        "na" => isset($new_data["na-" . $i]) ? util_format_price_db($new_data["na-" . $i]) : $data['main'][$i]['na'],
                    ];
                }

                if (isset($data['booster'])) {
                    $data['booster'] = isset($new_data["booster"]) ? util_format_price_db($new_data["booster"]) : $data['booster'];
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'level-boost':
                for ($i = 1; $i < 30; $i++) {
                    $keys = array_keys($data['main'][$i]);
                    foreach ($keys as $y) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }

                if (isset($data['per_level'])) {
                    $data['per_level']['eu'] = isset($new_data["per_level_eu"]) ? util_format_price_db($new_data["per_level_eu"]) : $data['per_level']['eu'];
                    $data['per_level']['na'] = isset($new_data["per_level_na"]) ? util_format_price_db($new_data["per_level_na"]) : $data['per_level']['na'];
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
        }
    } elseif ($game === 'valorant') {
        switch ($new_data['slug']) {
            case 'rank-boost':
                #iron to diamond pricing
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 3; $y++) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }
                #Immortal pricing
                for ($y = 100; $y <= 1500; $y += 100) {
                    $data['main'][8][$y]['eu'] = isset($new_data["eu-8-" . $y]) ? util_format_price_db($new_data["eu-8-" . $y]) : $data['main'][8][$y]['eu'];

                    $data['main'][8][$y]['na'] = isset($new_data["na-8-" . $y]) ? util_format_price_db($new_data["na-8-" . $y]) : $data['main'][8][$y]['na'];
                }
                #RR discount pricing
                foreach ($rr_array as $rr) {
                    $data['start_rr'][$rr] = isset($new_data["rr-" . $rr]) ? util_format_price_input($new_data["rr-" . $rr]) : $data['start_rr'][$rr];
                }
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'win-boost':
                #iron to diamond pricing
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 3; $y++) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }

                #Immortal pricing
                for ($y = 100; $y <= 1500; $y += 100) {
                    $data['main'][8][$y]['eu'] = isset($new_data["eu-8-" . $y]) ? util_format_price_db($new_data["eu-8-" . $y]) : $data['main'][8][$y]['eu'];

                    $data['main'][8][$y]['na'] = isset($new_data["na-8-" . $y]) ? util_format_price_db($new_data["na-8-" . $y]) : $data['main'][8][$y]['na'];
                }
                for ($i = 8; $i < 10; $i++) {
                    $data['main'][$i]['eu'] = isset($new_data["eu-" . $i]) ? util_format_price_db($new_data["eu-" . $i]) : $data['main'][$i]['eu'];

                    $data['main'][$i]['na'] = isset($new_data["na-" . $i]) ? util_format_price_db($new_data["na-" . $i]) : $data['main'][$i]['na'];
                }

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'placements-boost':
                #unranked pricing
                $data['main']["0"]['eu'] = isset($new_data["eu-0"]) ? util_format_price_db($new_data["eu-0"]) : $data['main']["0"]['eu'];
                $data['main']["0"]['na'] = isset($new_data["na-0"]) ? util_format_price_db($new_data["na-0"]) : $data['main']["0"]['na'];

                #iron to diamond pricing
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 3; $y++) {
                        $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];

                        $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                    }
                }
                for ($i = 8; $i < 10; $i++) {
                    $data['main'][$i]['eu'] = isset($new_data["eu-" . $i]) ? util_format_price_db($new_data["eu-" . $i]) : $data['main'][$i]['eu'];

                    $data['main'][$i]['na'] = isset($new_data["na-" . $i]) ? util_format_price_db($new_data["na-" . $i]) : $data['main'][$i]['na'];
                }
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'normals-boost':
                $data['main']["unrated-matches"]['eu'] = isset($new_data["eu-unrated-matches"]) ? util_format_price_db($new_data["eu-unrated-matches"]) : $data['main']["unrated-matches"]['eu'];
                $data['main']["unrated-matches"]['na'] = isset($new_data["na-unrated-matches"]) ? util_format_price_db($new_data["na-unrated-matches"]) : $data['main']["unrated-matches"]['na'];

                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
            case 'coaching':
                $data['main']["copilot"] = isset($new_data["copilot"]) ? util_format_price_db($new_data["copilot"]) : $data['main']["copilot"];

                $data['main']["vodreview"] = isset($new_data["vodreview"]) ? util_format_price_db($new_data["vodreview"]) : $data['main']["vodreview"];
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }

                break;
        }
    }

    // TFT admin pricing save support (previously TFT edits were NOT persisted because only LoL was handled)
    if ($game === 'teamfight-tactics') {
        switch ($new_data['slug']) {
            case 'rank-boost':
            case 'win-boost':
            case 'placements-boost':
            case 'double-up-boost':
                // Tiers 1-7 have divisions (1-4)
                for ($i = 1; $i < 8; $i++) {
                    for ($y = 1; $y <= 4; $y++) {
                        if (isset($data['main'][$i][$y])) {
                            $data['main'][$i][$y]['eu'] = isset($new_data["eu-" . $i . "-" . $y]) ? util_format_price_db($new_data["eu-" . $i . "-" . $y]) : $data['main'][$i][$y]['eu'];
                            $data['main'][$i][$y]['na'] = isset($new_data["na-" . $i . "-" . $y]) ? util_format_price_db($new_data["na-" . $i . "-" . $y]) : $data['main'][$i][$y]['na'];
                        }
                    }
                }

                // Tiers 8+ use LP brackets (e.g. 100, 200, ...)
                foreach ($data['main'] as $tier => $values) {
                    if (is_numeric($tier) && intval($tier) >= 8 && is_array($values)) {
                        foreach ($values as $lp => $val) {
                            if (is_array($val) && isset($val['eu'], $val['na'])) {
                                $data['main'][$tier][$lp]['eu'] = isset($new_data["eu-" . $tier . "-" . $lp]) ? util_format_price_db($new_data["eu-" . $tier . "-" . $lp]) : $data['main'][$tier][$lp]['eu'];
                                $data['main'][$tier][$lp]['na'] = isset($new_data["na-" . $tier . "-" . $lp]) ? util_format_price_db($new_data["na-" . $tier . "-" . $lp]) : $data['main'][$tier][$lp]['na'];
                            }
                        }
                    }
                }

                // LP discount pricing (if present)
                if (isset($data['start_lp']) && is_array($data['start_lp'])) {
                    foreach ($data['start_lp'] as $lp => $v) {
                        $data['start_lp'][$lp] = isset($new_data["lp-" . $lp]) ? util_format_price_input($new_data["lp-" . $lp]) : $data['start_lp'][$lp];
                    }
                }

                // LP gain pricing (if present)
                if (isset($data['lp_gain']) && is_array($data['lp_gain'])) {
                    foreach ($data['lp_gain'] as $lp => $v) {
                        $data['lp_gain'][$lp] = isset($new_data["lp-gain-" . $lp]) ? util_format_price_input($new_data["lp-gain-" . $lp]) : $data['lp_gain'][$lp];
                    }
                }

                // Extra options (if present)
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }
                break;

            case 'coaching':
                // Coaching JSON structure is shared across games
                $data['main']["copilot"] = isset($new_data["copilot"]) ? util_format_price_db($new_data["copilot"]) : $data['main']["copilot"];
                $data['main']["vodreview"] = isset($new_data["vodreview"]) ? util_format_price_db($new_data["vodreview"]) : $data['main']["vodreview"];
                foreach ($extra_opts_array as $opt) {
                    if (isset($data['extra'][$opt])) {
                        $data['extra'][$opt] = isset($new_data["extra-" . $opt]) ? util_format_price_input($new_data["extra-" . $opt]) : $data['extra'][$opt];
                    }
                }
                break;
        }
    }
    return $data;
}

function get_pricing_json_path($uuid)
{
    return SYS_PATH . "/public/uploads/private/boost-forms/$uuid.json";
}
function format_val_sql($val)
{
    if (is_numeric($val)) {
        $val = esc($val);
    } else {
        $val = '"' . esc($val) . '"';
    }
    return $val;
}

function format_params($params, $table)
{
    // gte = greather than or equal
    // lte = lower than or equal
    // gt = greather than
    // lt = lower than
    // s = IS LIKE
    // n = not equal

    $operators_sym = ['>=', '<=', '>', '<', 'LIKE', '<>'];
    $operators_txt = ['gte', 'lte', 'gt', 'lt', 's', 'n'];
    $def_params = [
        'select' => '*',
        'limit' => null,
        'order' => '',
        'where' => '',
        'update' => ''
    ];
    $rslt = $def_params;

    if (count($params) > 0) {
        foreach ($params as $key => $val) {
            switch ($key) {
                case 'select':
                    $rslt_select = '';
                    if ($val != '*') {
                        $val = explode(',', $val);
                        foreach ($val as $v) {
                            $rslt_select .= $table . '.' . $v . ',';
                        }
                        $rslt['select'] = rtrim($rslt_select, ',');
                    }
                    break;
                case 'limit':
                    $rslt['limit'] = intval($val);
                    break;
                case 'order':
                    if (!empty($val)) {
                        $tmp_order = esc($val);
                        $tmp_order = explode(',', $tmp_order);
                        $rslt['order'] = 'ORDER BY ' . $table . '.' . $tmp_order[0] . ' ' . $tmp_order[1];
                    }
                    break;
                default:
                    if (is_array($val)) {
                        foreach ($val as $operator => $query_tmp) {
                            if (!empty($query_tmp) or is_numeric($query_tmp)) {
                                $operator = str_replace($operators_txt, $operators_sym, $operator);
                                $rslt['where'] .= $table . '.' . esc($key) . ' ' . $operator . ' ' . format_val_sql($query_tmp) . '  AND ';
                            }
                        }
                    } else {
                        if (!empty($val) or is_numeric($val)) {
                            $rslt['where'] .= $table . '.' . esc($key) . ' = ' . format_val_sql($val) . ' AND ';
                        }
                    }
                    break;
            }
        }
        if (!empty($rslt['where'])) {
            $rslt['where'] = "WHERE " . $rslt['where'];
            $rslt['where'] = rtrim($rslt['where'], "AND ");
        }
        if (!empty($rslt['limit'])) {
            $rslt['limit'] = "LIMIT " . $rslt['limit'];
        }
    }
    if ($rslt['select'] == "*") {
        $rslt['select'] = $table . '.* ';
    }
    return $rslt;
}


function update_row($table, $data, $params)
{
    global $db;
    $db->update($table, $data, $params);
    return ['success' => true];
}


function generate_random_number($length = 6)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function calculate_lol_rank_pricing($data, $region, $start_tier, $start_div, $end_tier, $end_div, $lp_discount, $start_lp = 0, $end_lp = 0, $apex_tier = 8)
{
    $price = 0;
    $completion_time = $data['completion_time'];
    $idx = 0;
    // sum total of div values from start_tier to end_tier and add to price variable
    if ($start_tier == $apex_tier && $end_tier == $apex_tier) {
        $temp_price = calculate_lp_boost($start_lp, $end_lp, $data, $region, $apex_tier);
        $idx += $temp_price[1];
        $price += $temp_price[0];
    } else {
        if ($end_tier > $start_tier or ($end_tier == $start_tier && $end_div > $start_div)) {
            if ($start_tier == $end_tier) {
                for ($i = $start_div; $i < $end_div; $i++) {
                    $price += $data['main'][$start_tier][$i][$region];
                    $idx++;
                    if ($i == $start_div) {
                        $price -= $data['main'][$start_tier][$i][$region] * $lp_discount;
                    }
                }
            } else {
                for ($i = $start_tier; $i <= $end_tier; $i++) {
                    if ($i == $start_tier) {
                        for ($y = $start_div; $y <= 4; $y++) {
                            $price += $data['main'][$i][$y][$region];
                            $idx++;
                            if ($y == $start_div) {
                                $price -= $data['main'][$i][$y][$region] * $lp_discount;
                            }
                        }
                    } elseif ($i == $end_tier && $end_tier != $apex_tier) {
                        for ($y = 1; $y < $end_div; $y++) {
                            $idx++;
                            $price += $data['main'][$i][$y][$region];
                        }
                    } elseif ($i == $end_tier && $end_tier == $apex_tier) {
                        $temp_price = calculate_lp_boost(0, $end_lp, $data, $region, $apex_tier);
                        $idx += $temp_price[1];
                        $price += $temp_price[0];
                    } else {
                        for ($y = 1; $y <= 4; $y++) {
                            $idx++;
                            $price += $data['main'][$i][$y][$region];
                        }
                    }
                }
            }
        }
    }
    $completion_time = round($completion_time * $idx);

    return [$price, $completion_time];
}

function calculate_val_rank_pricing($data, $region, $start_tier, $start_div, $end_tier, $end_div, $rr_discount, $start_rr = 0, $end_rr = 0)
{
    $price = 0;
    $completion_time = $data['completion_time'];
    $idx = 0;
    // sum total of div values from start_tier to end_tier and add to price variable
    if ($start_tier == 8 && $end_tier == 8) {
        $temp_price = calculate_rr_boost($start_rr, $end_rr, $data, $region);
        $idx += $temp_price[1];
        $price += $temp_price[0];
    } else {
        if ($end_tier > $start_tier or ($end_tier == $start_tier && $end_div > $start_div)) {
            if ($start_tier == $end_tier) {
                for ($i = $start_div; $i < $end_div; $i++) {
                    $price += $data['main'][$start_tier][$i][$region];
                    $idx++;
                    if ($i == $start_div) {
                        $price -= $data['main'][$start_tier][$i][$region] * $rr_discount;
                    }
                }
            } else {
                for ($i = $start_tier; $i <= $end_tier; $i++) {
                    if ($i == $start_tier) {
                        for ($y = $start_div; $y <= 3; $y++) {
                            $price += $data['main'][$i][$y][$region];
                            $idx++;
                            if ($y == $start_div) {
                                $price -= $data['main'][$i][$y][$region] * $rr_discount;
                            }
                        }
                    } elseif ($i == $end_tier && $end_tier != 8) {
                        for ($y = 1; $y < $end_div; $y++) {
                            $idx++;
                            $price += $data['main'][$i][$y][$region];
                        }
                    } elseif ($i == $end_tier && $end_tier == 8) {
                        $temp_price = calculate_rr_boost(0, $end_rr, $data, $region);
                        $idx += $temp_price[1];
                        $price += $temp_price[0];
                    } else {
                        for ($y = 1; $y <= 3; $y++) {
                            $idx++;
                            $price += $data['main'][$i][$y][$region];
                        }
                    }
                }
            }
        }
    }
    $completion_time = round($completion_time * $idx);

    return [$price, $completion_time];
}

function calculate_lp_boost($start_lp, $end_lp, $data, $region, $tier = 8)
{
    $total_lp = $end_lp - $start_lp;
    $price = 0;
    $idx = 0;
    $idx = round($total_lp / 50);

    for ($i = $start_lp; $i < $end_lp; $i++) {
        for ($lps = 1500; $lps > 0; $lps -= 100) {
            if ($start_lp <= $lps) {
                $lp_section = $lps;
            } else {
                break;
            }
        }
        $lp_price = round(($data['main'][(string)$tier][$lp_section][$region] ?? $data['main'][$tier][$lp_section][$region] ?? 0) / 10, 2);
        $price += $lp_price;
    }
    return [$price, $idx];
}

function calculate_rr_boost($start_rr, $end_rr, $data, $region)
{
    $total_rr = $end_rr - $start_rr;
    $price = 0;
    $idx = 0;
    $idx = round($total_rr / 50);

    for ($i = $start_rr; $i < $end_rr; $i++) {
        for ($rrs = 1500; $rrs > 0; $rrs -= 100) {
            if ($start_rr <= $rrs) {
                $rr_section = $rrs;
            } else {
                break;
            }
        }
        $rr_price = round($data['main']["8"][$rr_section][$region] / 10, 2);
        $price += $rr_price;
    }
    return [$price, $idx];
}

function calculate_extra_pricing($extra_data, $extra_opts, $price)
{
    $extra_price = 0;

    foreach ($extra_data as $opt => $multiplier) {
        if (isset($extra_opts[$opt])) {
            if ($opt === 'champions') {
                if (is_array($extra_opts[$opt]) && count($extra_opts[$opt]) > 0) {
                    $extra_price += $price * floatval($multiplier);
                }
            } elseif ($extra_opts[$opt] == 1) {
                $extra_price += $price * floatval($multiplier);
            }
        }
    }

    return $extra_price;
}

function calculate_win_lp_pricing($start_lp, $data, $region, $tier = 8)
{
    $lp_section = 100;
    for ($lps = 1500; $lps > 0; $lps -= 100) {
        if ($start_lp <= $lps) {
            $lp_section = $lps;
        } else {
            break;
        }
    }
    $tierPrices = $data['main'][(string)$tier] ?? $data['main'][$tier] ?? [];
    $bucket = $tierPrices[$lp_section]
        ?? $tierPrices[(string)$lp_section]
        ?? $tierPrices['lt_' . $lp_section]
        ?? $tierPrices['lt' . $lp_section]
        ?? $tierPrices['<' . $lp_section]
        ?? [];
    $pricePerGame = is_array($bucket)
        ? (float)($bucket[$region] ?? 0)
        : (float)$bucket;
    $lp_price = round($pricePerGame / 500, 2);
    return [$lp_price, $lp_section, $pricePerGame];
}

function calculate_win_rr_pricing($start_rr, $data, $region)
{
    for ($rrs = 1500; $rrs > 0; $rrs -= 100) {
        if ($start_rr <= $rrs) {
            $rr_section = $rrs;
        } else {
            break;
        }
    }
    $rr_price = round($data['main']["8"][$rr_section][$region] / 500, 2);
    return [$rr_price, $rr_section];
}

function calculate_lol_win_boost_match_pricing($data, $region, $start_tier, $start_div, $matches, $start_lp = 0, $apex_tier = 8)
{
    $price = 0;
    $completion_time = floatval($data['completion_time']);
    if ($start_tier < $apex_tier && $start_tier > 0) {
        $price_per_game = $data['main'][$start_tier][$start_div][$region];
    } else {
        $temp_price = calculate_win_lp_pricing($start_lp, $data, $region, $start_tier);
        $price_per_game = (float)($temp_price[2] ?? 0);
        $price += $temp_price[0] * $start_lp;
    }
    $price += $price_per_game * $matches;

    $completion_time = round($completion_time * $matches);
    return [$price, $completion_time];
}

function calculate_val_win_boost_match_pricing($data, $region, $start_tier, $start_div, $matches, $start_rr = 0)
{
    $price = 0;
    $completion_time = floatval($data['completion_time']);
    if ($start_tier < 8 && $start_tier > 0) {
        $price_per_game = $data['main'][$start_tier][$start_div][$region];
    } else {
        $temp_price = calculate_win_rr_pricing($start_rr, $data, $region);
        $rr_section = $temp_price[1];
        $price_per_game = $data['main'][$start_tier][$rr_section][$region];
        $price += $temp_price[0] * $start_rr;
    }
    $price += $price_per_game * $matches;

    $completion_time = round($completion_time * $matches);
    return [$price, $completion_time];
}

function calculate_lol_match_pricing($data, $region, $start_tier, $start_div, $matches, $start_lp = 0, $apex_tier = 8)
{
    $price = 0;
    $completion_time = floatval($data['completion_time']);
    if ($start_tier == 0) {
        $price_per_game = $data['main']['0'][$region];
    } else if ($start_tier < $apex_tier && $start_tier > 0) {
        $price_per_game = $data['main'][$start_tier][$start_div][$region];
    } else {
        $price_per_game = $data['main'][$start_tier][$region]
            ?? $data['main'][$start_tier][$start_div][$region]
            ?? 0;
    }
    $price = $price_per_game * $matches;

    $completion_time = round($completion_time * $matches);
    return [$price, $completion_time];
}

function calculate_val_match_pricing($data, $region, $start_tier, $start_div, $matches)
{
    $price = 0;
    $completion_time = floatval($data['completion_time']);
    if ($start_tier == 0) {
        $price_per_game = $data['main']['0'][$region];
    } else if ($start_tier < 8 && $start_tier > 0) {
        $price_per_game = $data['main'][$start_tier][$start_div][$region];
    } else {
        $price_per_game = $data['main'][$start_tier][$region];
    }
    $price = $price_per_game * $matches;

    $completion_time = round($completion_time * $matches);
    return [$price, $completion_time];
}

function calculate_boost_pricing($data, $post_data)
{
    $price = 0;
    $completion_time = 0;

    // Not every boost form submits every rank field (win/placement forms have no
    // desired rank, divisionless tiers have no division). Seed the shared keys so
    // the calculators below never warn on an undefined array key.
    foreach (['start_tier', 'start_division', 'end_tier', 'end_division', 'matches', 'hours'] as $__lbBoostKey) {
        if (!array_key_exists($__lbBoostKey, $post_data)) $post_data[$__lbBoostKey] = 0;
    }

    // The generic multigame summary exposes these defaults when an older form
    // has no saved `extra` configuration. Mirror the same trusted values here
    // so checked Fortnite extras affect both the preview and checkout price.
    if (empty($data['extra']) && (
        isset($data['rank_names']) || isset($data['rankNames']) ||
        isset($data['ranks']) || isset($data['tiers']) || isset($data['form_config'])
    )) {
        $data['extra'] = [
            'is_priority' => 0.25,
            'is_solo_only' => 0.20,
            'is_streaming' => 0.15,
        ];
    }

    // TFT and LoL Classic forms use their own IDs in DB, while their pricing
    // structures reuse existing calculators. Only the local copy is remapped,
    // the real form ID remains unchanged for order creation and storage.
    $original_form_id = (int)($post_data['form_id'] ?? 0);
    $is_lol_classic_pricing = $original_form_id >= 30 && $original_form_id <= 36;
    if ($is_lol_classic_pricing) {
        // Older Classic price files used Bronze/Silver/Gold/Platinum/Diamond/
        // Challenger at keys 1,2,3,4,5,7. Keep their monetary values usable
        // while moving the ladder to Salt/Wood/Silver/Gold/Platinum/Diamond/
        // Legend. Once a price file already contains tier 6 it is considered
        // migrated and is left untouched.
        if (in_array($original_form_id, [30, 31, 32], true)
            && isset($data['main']) && is_array($data['main'])
            && !array_key_exists(6, $data['main']) && !array_key_exists('6', $data['main'])) {
            $legacyClassicMain = $data['main'];
            $data['main'][1] = $legacyClassicMain[1] ?? $legacyClassicMain['1'] ?? [];
            $data['main'][2] = $legacyClassicMain[1] ?? $legacyClassicMain['1'] ?? [];
            $data['main'][3] = $legacyClassicMain[2] ?? $legacyClassicMain['2'] ?? [];
            $data['main'][4] = $legacyClassicMain[3] ?? $legacyClassicMain['3'] ?? [];
            $data['main'][5] = $legacyClassicMain[4] ?? $legacyClassicMain['4'] ?? [];
            $data['main'][6] = $legacyClassicMain[5] ?? $legacyClassicMain['5'] ?? [];
            $data['main'][7] = $legacyClassicMain[7] ?? $legacyClassicMain['7'] ?? [];
        }
        foreach (['start_division', 'end_division'] as $classicDivisionKey) {
            $classicDivision = (int)($post_data[$classicDivisionKey] ?? 4);
            $post_data[$classicDivisionKey] = $classicDivision >= 1 && $classicDivision <= 4
                ? 5 - $classicDivision
                : 1;
        }
    }
    if (isset($post_data['form_id'])) {
        $fid = (int)$post_data['form_id'];
        if ($fid === 21) { $post_data['form_id'] = 1; }          // TFT Rank Boost -> LoL Rank Boost
        elseif ($fid === 22) { $post_data['form_id'] = 2; }      // TFT Win Boost -> LoL Win Boost
        elseif ($fid === 23) { $post_data['form_id'] = 3; }      // TFT Placements -> LoL Placements
        elseif ($fid === 24) { $post_data['form_id'] = 1; }      // TFT Double Up -> LoL Rank Boost
        elseif ($fid === 25) { $post_data['form_id'] = 15; }     // TFT Coaching -> Coaching
        elseif ($fid === 30) { $post_data['form_id'] = 1; }      // LoL Classic Rank Boost -> LoL Rank Boost
        elseif ($fid === 31) { $post_data['form_id'] = 2; }      // LoL Classic Win Boost -> LoL Win Boost
        elseif ($fid === 32) { $post_data['form_id'] = 3; }      // LoL Classic Placements -> LoL Placements
        elseif ($fid === 33) { $post_data['form_id'] = 15; }     // LoL Classic Coaching -> Coaching
        elseif ($fid === 34) { $post_data['form_id'] = 20; }     // LoL Classic Level Boost -> LoL Level Boost
        elseif ($fid === 35) { $post_data['form_id'] = 26; }     // LoL Classic Pro Games -> Pro Games
        elseif ($fid === 36) { $post_data['form_id'] = 27; }     // LoL Classic Duo Pass -> Duo Pass
    }


    switch ($post_data['form_id']) {
        case 1:
            $region = util_format_region_from_server($post_data['server']);
            // start_lp is a bracket key like '0-20'. When missing/0 (e.g. admin "Calculate price"),
            // there is no matching discount bracket, so default to 0 instead of warning.
            $lp_discount = floatval($data['start_lp'][$post_data['start_lp'] ?? ''] ?? 0);
            if (isset($post_data['start_lp_full']) && isset($post_data['end_lp_full'])) {
                $start_lp = intval($post_data['start_lp_full']);
                $end_lp = intval($post_data['end_lp_full']);
            } else {
                $start_lp = 0;
                $end_lp = 0;
            }
            $pricing = calculate_lol_rank_pricing($data, $region, $post_data['start_tier'], $post_data['start_division'], $post_data['end_tier'], $post_data['end_division'], $lp_discount, $start_lp, $end_lp, $is_lol_classic_pricing ? 7 : 8);
            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }

            $price += calculate_extra_pricing($data['extra'], $post_data, $price);

            $price += $extra_price;
            if (!$is_lol_classic_pricing) {
                $price += $price * ($data['lp_gain'][$post_data['lp_gain'] ?? ''] ?? 0);
            }

            // if isset post bonus_win and bonus_win = 1, add bonus win price
            if (isset($post_data['is_bonus_win']) && $post_data['is_bonus_win'] == 1 && $price > 0) {
                $win_data = get_pricing_json('9b6d47e8-f6cc-4f5f-85bf-0c2539646cd2');
                $win_price = calculate_lol_win_boost_match_pricing($win_data, $region, $post_data['end_tier'], $post_data['end_division'], 1)[0];
                $price += $win_price;
            }
            break;

        case 2:
            $region = util_format_region_from_server($post_data['server']);
            $post_data['matches'] = intval($post_data['matches']);
            if (isset($post_data['start_lp_full'])) {
                $start_lp = intval($post_data['start_lp_full']);
            } else {
                $start_lp = 0;
            }
            $pricing = calculate_lol_win_boost_match_pricing($data, $region, $post_data['start_tier'], $post_data['start_division'], $post_data['matches'], $start_lp, $is_lol_classic_pricing ? 7 : 8);
            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;
            $price += $price * $data['lp_gain'][$post_data['lp_gain']];
            break;

        case 3:
            $region = util_format_region_from_server($post_data['server']);
            $post_data['matches'] = intval($post_data['matches']);
            $pricing = calculate_lol_match_pricing($data, $region, $post_data['start_tier'], $post_data['start_division'], $post_data['matches'], 0, $is_lol_classic_pricing ? 7 : 8);
            $price = $pricing[0];
            $completion_time = $pricing[1];
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            break;
        case 4:
            $region = util_format_region_from_server($post_data['server']);
            $post_data['matches'] = max(1, min(20, intval($post_data['matches'] ?? $post_data['matches0'] ?? 5)));
            $post_data['boosters'] = max(1, min(4, intval($post_data['boosters'] ?? 1)));
            $pricing_per_game = $data['main'][$post_data['queue_type']][$region];
            $price = $pricing_per_game * $post_data['matches'] * $post_data['boosters'];
            $completion_time = round($data['completion_time'] * $post_data['matches']);
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            break;
        case 15:
            $coachingPrices = is_array($data['main'] ?? null) ? $data['main'] : [];
            $coachType = trim((string)($post_data['coach_type'] ?? ''));
            if ($coachType === '' || !array_key_exists($coachType, $coachingPrices)) {
                $coachType = array_key_exists('Co-Pilot', $coachingPrices)
                    ? 'Co-Pilot'
                    : (string)(array_key_first($coachingPrices) ?? '');
            }
            $post_data['coach_type'] = $coachType;
            $post_data['hours'] = max(1, (int)($post_data['hours'] ?? 1));
            $pricing_per_game = (int)($coachingPrices[$coachType] ?? 0);
            $price = $pricing_per_game * $post_data['hours'];
            $completion_time = round(($data['completion_time'] ?? 0) * $post_data['hours']);
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            break;
        case 5:
            $region = util_format_region_from_server($post_data['server']);
            $rr_discount = floatval($data['start_rr'][$post_data['start_rr']]);
            if (isset($post_data['start_rr_full']) && isset($post_data['end_rr_full'])) {
                $start_rr = intval($post_data['start_rr_full']);
                $end_rr = intval($post_data['end_rr_full']);
            } else {
                $start_rr = 0;
                $end_rr = 0;
            }
            $pricing = calculate_val_rank_pricing($data, $region, $post_data['start_tier'], $post_data['start_division'], $post_data['end_tier'], $post_data['end_division'], $rr_discount, $start_rr, $end_rr);
            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_agents']))) {
                $data['extra']['agents'] = null;
            }

            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;

            // if isset post bonus_win and bonus_win = 1, add bonus win price
            if (isset($post_data['is_bonus_win']) && $post_data['is_bonus_win'] == 1 && $price > 0) {
                $win_data = get_pricing_json('9b6d47e8-f6cc-4f5f-85bf-0c2539646cd2');
                $win_price = calculate_val_win_boost_match_pricing($win_data, $region, $post_data['end_tier'], $post_data['end_division'], 1)[0];
                $price += $win_price;
            }
            break;
        case 6:
            $region = util_format_region_from_server($post_data['server']);
            $post_data['matches'] = intval($post_data['matches']);
            if (isset($post_data['start_rr_full'])) {
                $start_rr = intval($post_data['start_rr_full']);
            } else {
                $start_rr = 0;
            }
            $pricing = calculate_val_win_boost_match_pricing($data, $region, $post_data['start_tier'], $post_data['start_division'], $post_data['matches'], $start_rr);
            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_agents']))) {
                $data['extra']['agents'] = null;
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;
            break;
        case 7:
            $region = util_format_region_from_server($post_data['server']);
            $pricing = calculate_val_match_pricing($data, $region, $post_data['start_tier'], $post_data['start_division'], $post_data['matches']);
            $price = $pricing[0];
            $completion_time = $pricing[1];
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_agents']))) {
                $data['extra']['agents'] = null;
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            break;
        case 8:
            $region = util_format_region_from_server($post_data['server']);
            $pricing_per_game = $data['main']['unrated_matches'][$region];
            $price = $pricing_per_game * $post_data['matches'];
            $completion_time = round($data['completion_time'] * $post_data['matches']);
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_agents']))) {
                $data['extra']['agents'] = null;
                $data['extra']['roles'] = null;
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            break;

        case 9:
            $region = util_format_region_from_server($post_data['server']);
            $post_data['matches'] = intval($post_data['matches']);
            if (isset($post_data['start_lp_full'])) {
                $start_lp = intval($post_data['start_lp_full']);
            } else {
                $start_lp = 0;
            }
            $pricing = calculate_lol_win_boost_match_pricing($data, $region, $post_data['start_tier'], $post_data['start_division'], $post_data['matches'], $start_lp);
            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;

            if (!empty($post_data['lp_gain']) && isset($data['lp_gain'][$post_data['lp_gain']])) {
                $price += $price * $data['lp_gain'][$post_data['lp_gain']];
            } else {
                $price += 0;
            }

            break;

        case 26: // Pro Games — booster price per rank tier
            $post_data['matches'] = max(1, min(10, intval($post_data['matches'] ?? $post_data['matches0'] ?? 1)));
            $start_tier_pg = max(0, min(10, intval($post_data['start_tier'] ?? 1)));
            $price_per_game = 0;
            if (!empty($post_data['selected_booster_id'])) {
                $bp = db_get_row('booster_profiles', [
                    'booster_id' => (int)$post_data['selected_booster_id'],
                    'select' => 'pg_prices,service_prices'
                ]);
                $servicePrices = [];
                if ($bp && !empty($bp['service_prices'])) {
                    $servicePrices = is_array($bp['service_prices']) ? $bp['service_prices'] : (json_decode($bp['service_prices'], true) ?? []);
                }
                $proGamesPriceKey = $original_form_id === 35 ? 'lol_classic_pro_games' : 'pro_games';
                if (!empty($servicePrices[$proGamesPriceKey][$start_tier_pg])) {
                    $price_per_game = (int)$servicePrices[$proGamesPriceKey][$start_tier_pg];
                } elseif ($original_form_id !== 35 && $bp && !empty($bp['pg_prices'])) {
                    // Classic (35) never falls back to pg_prices: different rank ladder.
                    $pgArr = is_array($bp['pg_prices']) ? $bp['pg_prices'] : (json_decode($bp['pg_prices'], true) ?? []);
                    if (!empty($pgArr[$start_tier_pg])) {
                        $price_per_game = (int)$pgArr[$start_tier_pg];
                    }
                }
            }
            $price = $price_per_game * $post_data['matches'];
            $completion_time = $post_data['matches'] <= 3 ? '~3 Hours' : '~' . $post_data['matches'] . ' Hours';
            break;

        case 27: // Duo Pass — booster hourly price per rank tier
            $hours = max(1, min(24, intval($post_data['hours'] ?? 3)));
            $start_tier_dp = max(0, min(10, intval($post_data['start_tier'] ?? 0)));
            $hour_price = 0;
            if (!empty($post_data['selected_booster_id'])) {
                $bp = db_get_row('booster_profiles', [
                    'booster_id' => (int)$post_data['selected_booster_id'],
                    'select' => 'service_prices'
                ]);
                if ($bp && !empty($bp['service_prices'])) {
                    $servicePrices = is_array($bp['service_prices']) ? $bp['service_prices'] : (json_decode($bp['service_prices'], true) ?? []);
                    if (!empty($servicePrices['duo_pass'][$start_tier_dp])) {
                        $hour_price = (int)$servicePrices['duo_pass'][$start_tier_dp];
                    }
                }
            }
            $price = $hour_price * $hours;
            $completion_time = '~' . $hours . ' Hours';
            break;

        case 16:
            $coachingPrices = is_array($data['main'] ?? null) ? $data['main'] : [];
            $coachType = trim((string)($post_data['coach_type'] ?? ''));
            if ($coachType === '' || !array_key_exists($coachType, $coachingPrices)) {
                $coachType = array_key_exists('Co-Pilot', $coachingPrices)
                    ? 'Co-Pilot'
                    : (string)(array_key_first($coachingPrices) ?? '');
            }
            $post_data['coach_type'] = $coachType;
            $post_data['hours'] = max(1, (int)($post_data['hours'] ?? 1));
            $pricing_per_game = (int)($coachingPrices[$coachType] ?? 0);
            $price = $pricing_per_game * $post_data['hours'];
            $completion_time = round(($data['completion_time'] ?? 0) * $post_data['hours']);
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            break;
        case 17:
            $region = util_format_region_from_server($post_data['server']);
            $matches = intval($post_data['matches']);
            $start_tier = $post_data['start_tier'];

            $price = 0;
            $completion_time = floatval($data['completion_time']);

            $price_per_game = $data['main'][$start_tier][$region];
            $price += $price_per_game * $matches;

            $completion_time = round($completion_time * $matches);
            $pricing = [$price, $completion_time];

            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];

            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~ $completion_time Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }

            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }

            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;

            break;
        case 18:
            $region = util_format_region_from_server($post_data['server']);
            $start_tier = $post_data['start_tier'];
            $end_tier = $post_data['end_tier'];

            $price = 0;
            $completion_time = $data['completion_time'];
            $idx = 0;

            if ($end_tier > $start_tier) {
                for ($i = $start_tier; $i < $end_tier; $i++) {
                    if (isset($data['main'][$i][$i + 1][$region])) {
                        $price += $data['main'][$i][$i + 1][$region];
                        $idx++;
                    }
                }
            }

            $completion_time = round($completion_time * $idx);

            $pricing = [$price, $completion_time];

            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];

            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }

            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }

            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;

            break;

        case 19:
            $region = util_format_region_from_server($post_data['server']);
            $start_tier = $post_data['start_tier'];

            $price = 0;
            $completion_time = $data['completion_time'];

            $price_per_tier = $data['main'][$start_tier][$region]; // Tier price

            // Ensure at least 1 booster and 1 match (to avoid multiplying by 0)
            $matches = max(1, (int)($post_data['matches'] ?? 1));
            $boosters = max(1, (int)($post_data['boosters'] ?? 1));

            $price = $price_per_tier * $matches * $boosters; // Correct formula

            $completion_time = round($completion_time * $matches); // Adjust completion time

            $pricing = [$price, $completion_time];


            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];

            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~ $completion_time Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }

            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }

            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;

            break;
        case 29:
            $region = util_format_region_from_server($post_data['server'] ?? 'euw');
            $start_tier = (int)($post_data['start_tier'] ?? 3);
            $start_division = (int)($post_data['start_division'] ?? 4);
            $matches = max(1, (int)($post_data['matches'] ?? $post_data['matches0'] ?? 1));
            $boosters = max(1, min(4, (int)($post_data['boosters'] ?? 1)));
            $completion_time = (float)($data['completion_time'] ?? 1);
            $main = $data['main'] ?? [];
            $tierPricing = $main[$start_tier] ?? $main[(string)$start_tier] ?? [];
            $price_per_game = 0;

            if ($start_tier === 0) {
                if (is_array($tierPricing)) {
                    $price_per_game = (int)($tierPricing[$region] ?? $tierPricing[(string)$region] ?? 0);
                }
                if ($price_per_game <= 0) {
                    $ironPricing = $main[1] ?? $main['1'] ?? [];
                    $ironIvPricing = is_array($ironPricing) ? ($ironPricing[4] ?? $ironPricing['4'] ?? []) : [];
                    if (is_array($ironIvPricing)) {
                        $price_per_game = (int)($ironIvPricing[$region] ?? $ironIvPricing[(string)$region] ?? 0);
                    }
                    if ($price_per_game <= 0 && is_array($ironPricing)) {
                        $price_per_game = (int)($ironPricing[$region] ?? $ironPricing[(string)$region] ?? 0);
                    }
                }
            } elseif ($start_tier >= 1 && $start_tier <= 7) {
                $divisionPricing = $tierPricing[$start_division] ?? $tierPricing[(string)$start_division] ?? [];
                if (is_array($divisionPricing)) {
                    $price_per_game = (int)($divisionPricing[$region] ?? $divisionPricing[(string)$region] ?? 0);
                }
                if ($price_per_game <= 0 && is_array($tierPricing)) {
                    $price_per_game = (int)($tierPricing[$region] ?? $tierPricing[(string)$region] ?? 0);
                }
            } else {
                $start_lp = isset($post_data['start_lp_full']) ? (int)$post_data['start_lp_full'] : (int)($post_data['start_lp'] ?? 0);
                if ($start_tier === 8 && is_array($tierPricing)) {
                    $bucket = max(100, (int)(ceil(max(1, $start_lp) / 100) * 100));
                    $lpPricing = $tierPricing[$bucket] ?? $tierPricing[(string)$bucket] ?? [];
                    if (is_array($lpPricing)) {
                        $price_per_game = (int)($lpPricing[$region] ?? $lpPricing[(string)$region] ?? 0);
                    }
                }
                if ($price_per_game <= 0 && is_array($tierPricing)) {
                    $price_per_game = (int)($tierPricing[$region] ?? $tierPricing[(string)$region] ?? 0);
                }
            }

            $price = max(0, $price_per_game) * $matches * $boosters;
            $completion_time = round($completion_time * $matches);
            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }
            $extra_price = 0;
            break;

        case 20:
            $region = util_format_region_from_server($post_data['server']);
            $start_tier = $post_data['start_tier'];
            $end_tier = $post_data['end_tier'];

            $price = 0;
            $completion_time = $data['completion_time'];
            $idx = 0;

            for ($i = $start_tier; $i < $end_tier; $i++) {
                if ($i < 30) {
                    if (isset($data['main'][$i][$i + 1][$region])) {
                        $price += $data['main'][$i][$i + 1][$region];
                        $idx++;
                    }
                } else {
                    $price += $data['per_level'][$region];
                    $idx++;
                }
            }

            $completion_time = round($completion_time * $idx);

            $pricing = [$price, $completion_time];

            $price = $pricing[0];
            $extra_price = 0;
            $completion_time = $pricing[1];

            if ($completion_time <= 0) {
                $completion_time = "Invalid";
            } elseif ($completion_time <= 24) {
                $completion_time = "~" . $completion_time . " Hours";
            } else {
                $completion_time = "~" . round($completion_time / 24) . " Days";
            }

            if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || (!isset($post_data['is_champions_roles']))) {
                $data['extra']['champions'] = null;
                $data['extra']['roles'] = null;
            }

            $price += calculate_extra_pricing($data['extra'], $post_data, $price);
            $price += $extra_price;

            break;

        default:
            // ── Generic / multigame rank boost ────────────────────────────────
            // Supports the dynamic admin JSON that is generated as:
            //   main[tier][division][region] = cents   (division based rows)
            // and older/simple structures:
            //   main[fromTier][toTier][region] = cents
            //   main[tier][region] = cents
            //   main[tier] = cents
            $genericWinIds = [38, 43, 46, 49, 52];
            $genericPlacementIds = [44, 50];
            $genericServiceSlug = strtolower(trim((string)($post_data['slug'] ?? $post_data['form_slug'] ?? '')));
            $genericGamesMode = in_array((int)($post_data['form_id'] ?? 0), $genericWinIds, true)
                || in_array((int)($post_data['form_id'] ?? 0), $genericPlacementIds, true)
                || $genericServiceSlug === 'win-boost'
                || in_array($genericServiceSlug, ['placement', 'placement-boost', 'placements-boost'], true)
                || !empty($data['games_pricing']);

            if ($genericGamesMode && isset($post_data['start_tier']) && is_numeric($post_data['start_tier']) && !empty($data['main']) && is_array($data['main'])) {
                $serverRaw = strtolower(trim((string)($post_data['server'] ?? 'eu')));
                $serverKey = str_replace([' ', '-'], '_', $serverRaw);
                $genericRegionAliases = [
                    'europe' => 'eu', 'eu_west' => 'eu', 'eu_nordic_&_east' => 'eu',
                    'north_america' => 'na', 'usa' => 'na', 'us' => 'na',
                    'asia' => 'asia', 'asia_pacific' => 'asia', 'as' => 'asia',
                    'south_east_asia' => 'sea', 'southeast_asia' => 'sea',
                    'oceania' => 'oceania', 'middle_east' => 'middle_east',
                ];
                $region = $genericRegionAliases[$serverKey] ?? util_format_region_from_server($serverKey);
                $regionCandidates = array_values(array_unique(array_filter([
                    $region, $serverKey, str_replace('_', '', $serverKey),
                    $serverKey === 'europe' ? 'eu' : null,
                    $serverKey === 'north_america' ? 'na' : null, 'eu', 'na',
                ])));
                $pickPrice = static function ($node) use ($regionCandidates) {
                    if (is_numeric($node)) return (int)$node;
                    if (!is_array($node)) return 0;
                    foreach ($regionCandidates as $key) {
                        if (isset($node[$key]) && is_numeric($node[$key])) return (int)$node[$key];
                    }
                    foreach ($node as $value) if (is_numeric($value)) return (int)$value;
                    return 0;
                };
                $tier = (int)$post_data['start_tier'];
                $division = max(0, (int)($post_data['start_division'] ?? 0));
                $tierNode = $data['main'][$tier] ?? $data['main'][(string)$tier] ?? ($tier === 0 ? ($data['main'][1] ?? $data['main']['1'] ?? []) : []);
                $unitNode = $division > 0 && is_array($tierNode)
                    ? ($tierNode[$division] ?? $tierNode[(string)$division] ?? $tierNode) : $tierNode;
                if ($division === 0 && is_array($unitNode)) {
                    $hasDirectRegionPrice = false;
                    foreach ($regionCandidates as $candidate) {
                        if (isset($unitNode[$candidate]) && is_numeric($unitNode[$candidate])) {
                            $hasDirectRegionPrice = true;
                            break;
                        }
                    }
                    if (!$hasDirectRegionPrice) {
                        $legacyDivisionKeys = array_values(array_filter(
                            array_keys($unitNode),
                            static fn($key) => is_numeric($key) && (int)$key > 0
                        ));
                        rsort($legacyDivisionKeys, SORT_NUMERIC);
                        if (!empty($legacyDivisionKeys)) {
                            $unitNode = $unitNode[$legacyDivisionKeys[0]] ?? $unitNode;
                        }
                    }
                }
                if ($tier === 0 && $division === 0 && is_array($unitNode)) {
                    $unitNode = $unitNode[1] ?? $unitNode['1'] ?? $unitNode;
                }
                $maxMatches = max(1, min(100, (int)($data['matches_max'] ?? $data['games_max'] ?? 100)));
                $matches = max(1, min($maxMatches, (int)($post_data['matches'] ?? $post_data['matches0'] ?? 1)));
                $price = max(0, $pickPrice($unitNode)) * $matches;
                $hours = max(1, (float)($data['completion_time'] ?? 1)) * $matches;
                $completion_time = $price > 0
                    ? ($hours <= 24 ? '~' . round($hours) . ' Hours' : '~' . round($hours / 24) . ' Days') : 'Invalid';
                if ($price > 0) $price += calculate_extra_pricing($data['extra'] ?? [], $post_data, $price);
            } elseif (!empty($post_data['start_tier']) && !empty($post_data['end_tier']) && !empty($data['main']) && is_array($data['main'])) {
                $serverRaw = strtolower(trim((string)($post_data['server'] ?? 'euw')));
                $serverKey = str_replace([' ', '-'], '_', $serverRaw);
                $genericRegionAliases = [
                    'europe' => 'eu', 'eu_west' => 'eu', 'eu_nordic_&_east' => 'eu',
                    'north_america' => 'na', 'usa' => 'na', 'us' => 'na',
                    'asia' => 'asia', 'asia_pacific' => 'asia', 'as' => 'asia',
                    'south_east_asia' => 'sea', 'southeast_asia' => 'sea',
                    'oceania' => 'oceania', 'middle_east' => 'middle_east',
                ];
                $region = $genericRegionAliases[$serverKey] ?? util_format_region_from_server($serverKey);

                $regionCandidates = array_values(array_unique(array_filter([
                    $region,
                    $serverKey,
                    str_replace('_', '', $serverKey),
                    ($serverKey === 'europe' ? 'eu' : null),
                    ($serverKey === 'eu_west' ? 'eu' : null),
                    ($serverKey === 'eu_west' ? 'euw' : null),
                    ($serverKey === 'north_america' ? 'na' : null),
                    ($serverKey === 'usa' ? 'na' : null),
                    ($serverKey === 'us' ? 'na' : null),
                    'eu',
                    'na',
                ])));

                $pickRegionValue = static function ($node) use ($regionCandidates) {
                    if (!is_array($node)) return is_numeric($node) ? (int)$node : null;
                    foreach ($regionCandidates as $rk) {
                        if (isset($node[$rk]) && is_numeric($node[$rk])) return (int)$node[$rk];
                    }
                    foreach ($node as $value) {
                        if (is_numeric($value)) return (int)$value;
                    }
                    return null;
                };

                $parsePointRanges = static function ($data): array {
                    $src = [];
                    if (!empty($data['points_options']) && is_array($data['points_options'])) {
                        $src = $data['points_options'];
                    } elseif (!empty($data['lp_options']) && is_array($data['lp_options'])) {
                        $src = $data['lp_options'];
                    } elseif (!empty($data['start_lp_options']) && is_array($data['start_lp_options'])) {
                        $src = $data['start_lp_options'];
                    }
                    $ranges = [];
                    foreach ($src as $key => $label) {
                        $text = (string)$key;
                        if (!preg_match('/(-?\d+)\s*[-_\/]\s*(-?\d+)/', $text, $m)) {
                            $text = (string)$label;
                            preg_match('/(-?\d+)\s*[-_\/]\s*(-?\d+)/', $text, $m);
                        }
                        if (!empty($m)) {
                            $lo = (int)$m[1]; $hi = (int)$m[2];
                            if ($hi < $lo) { $tmp = $lo; $lo = $hi; $hi = $tmp; }
                            $ranges[] = ['key' => (string)$key, 'lo' => $lo, 'hi' => $hi];
                        }
                    }
                    usort($ranges, static fn($a, $b) => $a['lo'] <=> $b['lo']);
                    return $ranges;
                };

                $main       = $data['main'];
                $start_tier = (int)$post_data['start_tier'];
                $end_tier   = (int)$post_data['end_tier'];
                $start_div  = max(0, (int)($post_data['start_division'] ?? 0));
                $end_div    = max(0, (int)($post_data['end_division'] ?? 0));
                $start_pts  = max(0, (int)($post_data['start_lp_full'] ?? $post_data['start_points_full'] ?? 0));
                $end_pts    = max(0, (int)($post_data['end_lp_full'] ?? $post_data['end_points_full'] ?? 0));

                $completionBase = (float)($data['completion_time'] ?? 24);
                $idx = 0;

                // Style A: direct from-tier to end-tier price.
                // Dynamic rank forms such as Marvel Rivals use main[tier][division][region].
                // In that structure an end tier like Gold (3) collides with division key 3,
                // so main[2][3] is Silver I's step price, not a direct Silver to Gold price.
                $divisionBasedFormIds = [37, 42, 45, 48, 51, 53];
                $isKnownDivisionBasedForm = in_array((int)($post_data['form_id'] ?? 0), $divisionBasedFormIds, true);
                $direct = $isKnownDivisionBasedForm
                    ? null
                    : ($main[$start_tier][$end_tier] ?? $main[(string)$start_tier][(string)$end_tier] ?? null);
                $directPrice = $pickRegionValue($direct);
                if (!$isKnownDivisionBasedForm && $end_tier > $start_tier && $directPrice !== null) {
                    $price = $directPrice;
                    $idx   = max(1, $end_tier - $start_tier);
                } else {
                    // Build sorted dynamic rank keys from main and optional rank names.
                    $rankKeys = [];
                    $sources = [$main, $data['rank_names'] ?? [], $data['rankNames'] ?? [], $data['ranks'] ?? [], $data['tiers'] ?? []];
                    foreach ($sources as $src) {
                        if (!is_array($src)) continue;
                        foreach (array_keys($src) as $k) {
                            if (is_numeric($k) && (int)$k > 0 && !in_array((int)$k, $rankKeys, true)) {
                                $rankKeys[] = (int)$k;
                            }
                        }
                    }
                    sort($rankKeys, SORT_NUMERIC);
                    if (empty($rankKeys)) {
                        $rankKeys = range(min($start_tier, $end_tier), max($start_tier, $end_tier));
                    }

                    $nextTier = static function (int $tier) use ($rankKeys): ?int {
                        foreach ($rankKeys as $rk) {
                            if ($rk > $tier) return $rk;
                        }
                        return null;
                    };

                    // Detect division based pricing like main[tier][division][region].
                    $divisionCount = 0;
                    foreach ($main as $tierNode) {
                        if (!is_array($tierNode)) continue;
                        foreach ($tierNode as $dk => $dv) {
                            if (is_numeric($dk) && (int)$dk >= 1 && (int)$dk <= 10 && is_array($dv)) {
                                foreach ($regionCandidates as $rk) {
                                    if (isset($dv[$rk]) && is_numeric($dv[$rk])) {
                                        $divisionCount = max($divisionCount, (int)$dk);
                                    }
                                }
                            }
                        }
                    }

                    $pointRanges = $parsePointRanges($data);

                    // No-div ranks use LoL Master-style threshold pricing:
                    // main[tier][lt_100][region], main[tier][lt_200][region], ...
                    $parsePointThresholds = static function (int $tier) use ($main): array {
                        $node = $main[$tier] ?? $main[(string)$tier] ?? null;
                        if (!is_array($node)) return [];
                        $limits = [];
                        foreach (array_keys($node) as $k) {
                            if (preg_match('/^lt[_-]?(\d+)$/i', (string)$k, $m) || preg_match('/^<(\d+)$/', (string)$k, $m)) {
                                $limits[] = (int)$m[1];
                            }
                        }
                        $limits = array_values(array_unique(array_filter($limits, static fn($v) => $v > 0)));
                        sort($limits, SORT_NUMERIC);
                        return $limits;
                    };
                    $hasThresholdPricing = static function (int $tier) use ($parsePointThresholds): bool {
                        return !empty($parsePointThresholds($tier));
                    };
                    $sumThresholdPricing = static function (int $tier, int $from, int $to) use ($main, $parsePointThresholds, $pickRegionValue): array {
                        $node = $main[$tier] ?? $main[(string)$tier] ?? null;
                        $limits = $parsePointThresholds($tier);
                        if (!is_array($node) || empty($limits) || $to <= $from) return [0, 0];
                        $sum = 0; $steps = 0; $prev = 0;
                        foreach ($limits as $limit) {
                            if ($to > $prev && $from < $limit) {
                                $key = 'lt_' . $limit;
                                $bucketPrice = $pickRegionValue($node[$key] ?? $node['lt'.$limit] ?? $node['<'.$limit] ?? null);
                                if ($bucketPrice !== null && $bucketPrice > 0) {
                                    $sum += (int)$bucketPrice;
                                    $steps++;
                                }
                            }
                            $prev = $limit;
                        }
                        return [$sum, $steps];
                    };

                    $thresholdHandled = false;
                    if ($hasThresholdPricing($start_tier) || $hasThresholdPricing($end_tier)) {
                        if ($end_tier === $start_tier && $hasThresholdPricing($start_tier)) {
                            [$pAdd, $pSteps] = $sumThresholdPricing($start_tier, $start_pts, max($end_pts, $start_pts));
                            $price += $pAdd; $idx += $pSteps; $thresholdHandled = true;
                        } elseif ($end_tier > $start_tier) {
                            // Mixed path: finish division ranks first, then add threshold rows for the no-div desired rank.
                            if ($divisionCount > 0 && $start_div > 0) {
                                $curTier = $start_tier;
                                $curDiv  = min($divisionCount, max(1, $start_div));
                                $guard   = 0;
                                while ($guard++ < 300 && $curTier < $end_tier) {
                                    $stepNode = $main[$curTier][$curDiv] ?? $main[(string)$curTier][(string)$curDiv] ?? null;
                                    $stepPrice = $pickRegionValue($stepNode);
                                    if ($stepPrice === null) break;
                                    $price += $stepPrice; $idx++;
                                    if ($curDiv < $divisionCount) {
                                        $curDiv++;
                                    } else {
                                        $nt = $nextTier($curTier);
                                        if ($nt === null) break;
                                        $curTier = $nt;
                                        $curDiv = 1;
                                    }
                                }
                            }
                            if ($hasThresholdPricing($end_tier) && $end_pts > 0) {
                                [$pAdd, $pSteps] = $sumThresholdPricing($end_tier, 0, $end_pts);
                                $price += $pAdd; $idx += $pSteps;
                            }
                            $thresholdHandled = ($price > 0);
                        }
                    }
                    // The uploaded price sheets are authoritative for these established
                    // games. Some JSON files retain a generated fourth/fifth row that is
                    // not a real division; do not let those stale rows alter traversal.
                    $configuredDivisionCounts = [
                        37 => 3, // Marvel Rivals Rank
                        42 => 3, // Rocket League Rank
                        45 => 4, // Apex Legends Rank
                        48 => 5, // Overwatch 2 Rank
                        51 => 4, // Wild Rift Rank
                        53 => 3, // Fortnite Rank
                    ];
                    $configuredDivisionCount = $configuredDivisionCounts[(int)($post_data['form_id'] ?? 0)] ?? null;
                    if ($configuredDivisionCount !== null) {
                        $divisionCount = $configuredDivisionCount;
                    }

                    // Apex Master is an open RP ladder. Older pricing files still contain
                    // the former "Predator" tier as four generated division rows. Use the
                    // last of those rows as the configured price per 100 Master RP and
                    // prorate it for the exact RP difference. This also supports an explicit
                    // main[7][per_100_rp] value when the pricing file is updated in admin.
                    $apexRpHandled = false;
                    if ((int)($post_data['form_id'] ?? 0) === 45 && $end_tier === 7) {
                        // Diamond I -> Master 15,050 RP is already the paid rank
                        // transition in the supplied sheet. Charge the continuous
                        // Master tariff only for RP above that exact baseline.
                        $masterBase = 15050;
                        $legacyRpNode = $main[7]['per_100_rp'] ?? $main['7']['per_100_rp']
                            ?? $main[8][4] ?? $main['8']['4'] ?? null;
                        $per100Rp = $pickRegionValue($legacyRpNode);

                        if ($per100Rp !== null && $per100Rp > 0) {
                            if ($start_tier < 7 && $divisionCount > 0 && $start_div > 0) {
                                $curTier = $start_tier;
                                $curDiv = min($divisionCount, max(1, $start_div));
                                $guard = 0;
                                while ($guard++ < 300 && $curTier < 7) {
                                    $stepNode = $main[$curTier][$curDiv] ?? $main[(string)$curTier][(string)$curDiv] ?? null;
                                    $stepPrice = $pickRegionValue($stepNode);
                                    if ($stepPrice === null) break;
                                    $price += $stepPrice;
                                    $idx++;
                                    if ($curDiv < $divisionCount) {
                                        $curDiv++;
                                    } else {
                                        $nt = $nextTier($curTier);
                                        if ($nt === null) break;
                                        $curTier = $nt;
                                        $curDiv = 1;
                                    }
                                }
                            }

                            $fromRp = $start_tier === 7 ? max($masterBase, $start_pts) : $masterBase;
                            $toRp = max($fromRp, $end_pts);
                            $rpDifference = max(0, $toRp - $fromRp);
                            if ($rpDifference > 0) {
                                $price += (int)round(($per100Rp / 100) * $rpDifference);
                                $idx += max(1, (int)ceil($rpDifference / 100));
                            }
                            $apexRpHandled = $price > 0;
                        }
                    }

                    $hasPointPricing = static function (int $tier) use ($main, $pointRanges, $pickRegionValue): bool {
                        $node = $main[$tier] ?? $main[(string)$tier] ?? null;
                        if (!is_array($node)) return false;
                        foreach ($pointRanges as $pr) {
                            if ($pickRegionValue($node[$pr['key']] ?? null) !== null) return true;
                        }
                        return false;
                    };
                    $sumPointPricing = static function (int $tier, int $from, int $to) use ($main, $pointRanges, $pickRegionValue): array {
                        $node = $main[$tier] ?? $main[(string)$tier] ?? null;
                        if (!is_array($node) || empty($pointRanges) || $to <= $from) return [0, 0];
                        $sum = 0; $steps = 0;
                        foreach ($pointRanges as $pr) {
                            $bucketPrice = $pickRegionValue($node[$pr['key']] ?? null);
                            if ($bucketPrice === null || $bucketPrice <= 0) continue;
                            $lo = (int)$pr['lo'];
                            $hiExclusive = ((int)$pr['hi']) + 1;
                            $overlap = max(0, min($to, $hiExclusive) - max($from, $lo));
                            if ($overlap <= 0) continue;
                            $width = max(1, $hiExclusive - $lo);
                            $sum += (int)round($bucketPrice * min(1, $overlap / $width));
                            $steps++;
                        }
                        return [$sum, $steps];
                    };

                    if (!$thresholdHandled && !$apexRpHandled && !empty($pointRanges) && ($hasPointPricing($start_tier) || $hasPointPricing($end_tier))) {
                        $minPoint = min(array_map(static fn($r) => (int)$r['lo'], $pointRanges));
                        $maxPoint = max(array_map(static fn($r) => (int)$r['hi'], $pointRanges)) + 1;
                        if ($end_tier === $start_tier) {
                            [$pAdd, $pSteps] = $sumPointPricing($start_tier, $start_pts, max($end_pts, $start_pts));
                            $price += $pAdd; $idx += $pSteps;
                        } elseif ($end_tier > $start_tier) {
                            $curTier = $start_tier;
                            $guard = 0;
                            while ($guard++ < 300 && $curTier <= $end_tier) {
                                $fromPoint = ($curTier === $start_tier) ? $start_pts : $minPoint;
                                $toPoint = ($curTier === $end_tier) ? $end_pts : $maxPoint;
                                [$pAdd, $pSteps] = $sumPointPricing($curTier, $fromPoint, $toPoint);
                                if ($pAdd > 0) {
                                    $price += $pAdd; $idx += max(1, $pSteps);
                                }
                                if ($curTier === $end_tier) break;
                                $nt = $nextTier($curTier);
                                if ($nt === null) break;
                                $curTier = $nt;
                            }
                        }
                    } elseif (!$thresholdHandled && !$apexRpHandled && $divisionCount > 0 && $start_div > 0 && $end_div > 0) {
                        $curTier = $start_tier;
                        $curDiv  = min($divisionCount, max(1, $start_div));
                        $guard   = 0;

                        while ($guard++ < 300) {
                            if ($curTier === $end_tier && $curDiv === $end_div) break;

                            $stepNode = $main[$curTier][$curDiv] ?? $main[(string)$curTier][(string)$curDiv] ?? null;
                            $stepPrice = $pickRegionValue($stepNode);
                            if ($stepPrice === null) break;

                            $price += $stepPrice;
                            $idx++;

                            if ($curDiv < $divisionCount) {
                                $curDiv++;
                            } else {
                                $nt = $nextTier($curTier);
                                if ($nt === null) break;
                                $curTier = $nt;
                                $curDiv  = 1;
                            }
                        }
                    } elseif (!$thresholdHandled && !$apexRpHandled && $divisionCount > 0 && $start_div > 0 && $end_tier > $start_tier && $end_div === 0) {
                        // Desired top ranks such as Master, Top 500, Supersonic Legend,
                        // Unreal and One Above All have no division of their own. Their
                        // price is the path through the remaining divisions of the current
                        // and intermediate tiers. Previously this fell through to the flat
                        // tier lookup, which cannot read division-shaped pricing nodes and
                        // therefore returned €0.
                        $curTier = $start_tier;
                        $curDiv = min($divisionCount, max(1, $start_div));
                        $guard = 0;
                        while ($guard++ < 300 && $curTier < $end_tier) {
                            $stepNode = $main[$curTier][$curDiv] ?? $main[(string)$curTier][(string)$curDiv] ?? null;
                            $stepPrice = $pickRegionValue($stepNode);
                            if ($stepPrice === null) break;
                            $price += $stepPrice;
                            $idx++;
                            if ($curDiv < $divisionCount) {
                                $curDiv++;
                            } else {
                                $nt = $nextTier($curTier);
                                if ($nt === null) break;
                                $curTier = $nt;
                                $curDiv = 1;
                            }
                        }
                    } elseif (!$thresholdHandled && !$apexRpHandled && $end_tier > $start_tier) {
                        // Style B/C: no divisions, one price per step tier.
                        $curTier = $start_tier;
                        $guard = 0;
                        while ($guard++ < 300 && $curTier < $end_tier) {
                            $stepNode = $main[$curTier] ?? $main[(string)$curTier] ?? null;
                            $stepPrice = $pickRegionValue($stepNode);
                            // A few legacy files generated division-shaped rows before a
                            // leaderboard-only top rank was changed to divisionless (for
                            // example Eternity -> One Above All). The last generated row is
                            // the transition price into the next rank.
                            if ($stepPrice === null && is_array($stepNode)) {
                                $legacyDivisionKeys = array_values(array_filter(
                                    array_keys($stepNode),
                                    static fn($key) => is_numeric($key) && (int)$key > 0
                                ));
                                rsort($legacyDivisionKeys, SORT_NUMERIC);
                                foreach ($legacyDivisionKeys as $legacyDivisionKey) {
                                    $stepPrice = $pickRegionValue($stepNode[$legacyDivisionKey] ?? null);
                                    if ($stepPrice !== null) break;
                                }
                            }
                            if ($stepPrice === null) break;
                            $price += $stepPrice;
                            $idx++;
                            $nt = $nextTier($curTier);
                            if ($nt === null) break;
                            $curTier = $nt;
                        }
                    }
                }

                $completion_time = $idx > 0 ? round($completionBase * $idx) : (int)$completionBase;

                if ($completion_time <= 0 || $price <= 0) {
                    $completion_time = 'Invalid';
                } elseif ($completion_time <= 24) {
                    $completion_time = '~' . $completion_time . ' Hours';
                } else {
                    $completion_time = '~' . round($completion_time / 24) . ' Days';
                }

                if ($price > 0) {
                    // Heroes/Roles/Legends selection is free when boosting Duo (the booster already
                    // knows what to play), same as the League "Champs & Roles" behavior. Only charge
                    // the configured Solo percentage when the picker was actually used.
                    if ((isset($post_data['is_duo']) && $post_data['is_duo'] == 1) || !isset($post_data['is_champions_roles'])) {
                        $data['extra']['is_champions_roles'] = null;
                    }
                    $price += calculate_extra_pricing($data['extra'] ?? [], $post_data, $price);

                    // Generic multigame rank boosts need the same +1 Bonus Win behavior
                    // as LoL and Valorant. The win price is taken from the matching
                    // win-boost JSON at the selected desired rank and division.
                    if (!empty($post_data['is_bonus_win']) && (int)$post_data['is_bonus_win'] === 1) {
                        $genericBonusWinJsonByRankForm = [
                            37 => 'f4c3bbfe-750e-59c5-a2d6-fc09cf9df2eb', // Marvel Rivals
                        ];
                        $bonusWinUuid = $genericBonusWinJsonByRankForm[(int)($post_data['form_id'] ?? 0)] ?? null;
                        if ($bonusWinUuid) {
                            $bonusWinData = get_pricing_json($bonusWinUuid);
                            $bonusTier = (int)($post_data['end_tier'] ?? 0);
                            $bonusDivision = max(0, (int)($post_data['end_division'] ?? 0));
                            $bonusTierNode = $bonusWinData['main'][$bonusTier] ?? $bonusWinData['main'][(string)$bonusTier] ?? null;
                            $bonusNode = ($bonusDivision > 0 && is_array($bonusTierNode))
                                ? ($bonusTierNode[$bonusDivision] ?? $bonusTierNode[(string)$bonusDivision] ?? $bonusTierNode)
                                : $bonusTierNode;
                            $bonusPrice = $pickRegionValue($bonusNode);
                            if ($bonusPrice !== null && $bonusPrice > 0) {
                                $price += (int)$bonusPrice;
                            }
                        }
                    }
                }
            }
            break;
    }

    $price_eur = $price;

    if ($_SESSION['currency'] == 'USD') {
        $price = $price * get_exchange_rate();
    }

    return [$price, $completion_time, $price_eur];
}

function load_boost_options()
{
    return ['server', 'platform', 'hours', 'boosters', 'selected_boosters', 'start_tier', 'start_division', 'end_tier', 'end_division', 'start_lp', 'end_lp', 'lp_gain', 'start_rr', 'end_rr', 'matches', 'queue_type', 'roles', 'agents', 'champions', 'flash_position', 'vpn_country', 'is_priority', 'is_streaming', 'is_solo_only', 'is_bonus_win', 'is_offline_mode', 'is_coaching', 'coach_type', 'is_duo', 'is_hidden_duo', 'is_undercover_winrate', 'is_moderate_kda', 'specific_hero', 'heroes_roles', 'priority'];
}

if (!function_exists('lb_order_options_schema_ensure')) {
    // New boost forms (e.g. the 37-52 range) can submit fields from load_boost_options()
    // that older order_options table versions don't have a column for yet (e.g. "platform"
    // for console/PC selectors). Add them defensively so checkout doesn't fail with
    // "Unknown column ... in 'INSERT INTO'" on installs that haven't been migrated.
    function lb_order_options_schema_ensure(): void
    {
        global $db;
        if (empty($db)) return;
        static $done = false;
        if ($done) return;
        $done = true;
        try {
            $db->run("ALTER TABLE order_options ADD COLUMN IF NOT EXISTS platform VARCHAR(100) NULL DEFAULT NULL");
        } catch (\Throwable $e) {}
        try {
            $db->run("ALTER TABLE order_options ADD COLUMN IF NOT EXISTS selected_boosters VARCHAR(255) NULL DEFAULT NULL");
        } catch (\Throwable $e) {}
    }
}
function util_load_roles_select($select = [], $separator = '|')
{
    // value => label (Anzeige)
    $roles = [
        'TopLane'  => 'Toplane',
        'Jungle'   => 'Jungle',
        'MidLane'  => 'Midlane',
        'AdCarry'  => 'ADC',
        'Support'  => 'Support',
    ];

    if (!is_array($select)) {
        $select = explode($separator, (string)($select ?? ''));
    }

    $select = array_values(array_filter(array_map(
        static fn($v) => trim((string)$v),
        $select
    ), 'strlen'));

    $html = '';
    foreach ($roles as $value => $label) {
        $html .= '<option value="'.$value.'"'
            . (in_array($value, $select, true) ? ' selected=""' : '')
            . '>'.$label.'</option>';
    }

    return $html;
}


function util_load_games_select($select = [], $separator = '|')
{
    if (!is_array($select)) {
        $raw = trim((string)($select ?? ''));
        $decoded = json_decode($raw, true);
        $select = is_array($decoded)
            ? $decoded
            : preg_split('/[|,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
    }

    $aliases = [
        'league-of-legends' => 'lol',
        'league_of_legends' => 'lol',
        'leagueoflegends' => 'lol',
        'valorant' => 'val',
        'teamfight-tactics' => 'tft',
        'teamfight_tactics' => 'tft',
        'teamfighttactics' => 'tft',
        'league-of-legends-classic' => 'lol_classic',
        'lol-classic' => 'lol_classic',
        'league_classic' => 'lol_classic',
        // Dynamically added games (Rocket League, Apex, Marvel Rivals, Overwatch 2, Wild Rift, ...)
        // are intentionally NOT aliased here: boost_forms.game for these games stores the full
        // games.slug (e.g. "rocket-league"), set verbatim in the "INSERT INTO boost_forms" of the
        // admin Boost Form Editor. The <option value> must stay the raw slug so it matches what
        // orders_panel_state()/the orders-panel route compares boosters.games against.
    ];

    $select = array_values(array_unique(array_filter(array_map(
        static function ($value) use ($aliases) {
            $value = strtolower(trim((string)$value));
            return $aliases[$value] ?? $value;
        },
        $select
    ), 'strlen')));

    $games = [];
    // Icon per game so the picker can show the same artwork as the rest of the admin.
    $icons = [];
    try {
        global $db;
        $rows = $db->run(
            "SELECT DISTINCT g.name, g.slug, g.sort_order, g.icon
             FROM games g
             INNER JOIN game_services gs ON gs.game_id = g.id
             WHERE g.status = 1
               AND gs.status = 1
               AND gs.service_type = 'boosting'
             ORDER BY g.sort_order ASC, g.name ASC"
        );

        foreach ((array)$rows as $row) {
            $slug = strtolower(trim((string)($row['slug'] ?? '')));
            if ($slug === '') continue;
            $value = $aliases[$slug] ?? $slug;
            $games[$value] = (string)($row['name'] ?? $slug);
            $icons[$value] = trim((string)($row['icon'] ?? '')) !== ''
                ? trim((string)($row['icon']))
                : ASSET_URL . '/website/images/icons/' . $slug . '.png';
        }
    } catch (Throwable $e) {
        // Keep the admin form usable when the dynamic marketplace tables are unavailable.
    }

    $fallback = [
        'lol' => 'League of Legends',
        'lol_classic' => 'LoL Classic (League of Legends Classic)',
        'val' => 'Valorant',
        'tft' => 'Teamfight Tactics',
    ];
    $games = $games + $fallback;

    // LoL Classic uses the LoL limits but remains independently selectable.
    // The label always carries both spellings so the picker finds it whether the
    // admin types "lol" or "classic" - "League of Legends Classic" alone contains
    // neither the string "lol" nor an obvious short form.
    $games['lol_classic'] = 'LoL Classic (League of Legends Classic)';
    $icons['lol_classic'] = ASSET_URL . '/website/images/icons/lol-classic.png';

    $iconFallback = [
        'lol' => 'league-of-legends.png',
        'lol_classic' => 'lol-classic.png',
        'val' => 'valorant.png',
        'tft' => 'teamfight-tactics.png',
    ];

    $html = '';
    foreach ($games as $value => $label) {
        $icon = $icons[$value] ?? '';
        if ($icon === '') {
            $icon = ASSET_URL . '/website/images/icons/'
                . ($iconFallback[$value] ?? (str_replace('_', '-', $value) . '.png'));
        }
        $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-image="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '"'
            . (in_array($value, $select, true) ? ' selected=""' : '')
            . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
}

function util_load_languages_select($select = [], $separator = '|')
{
    $languages = [
        'en' => 'English',
        'de' => 'Deutsch',
        'fr' => 'Français',
        'es' => 'Español',
        'pt' => 'Português',
        'it' => 'Italiano',
        'nl' => 'Nederlands',
        'pl' => 'Polski',
        'ru' => 'Русский',
        'jp' => '日本語',
        'zh' => '中文',
        'sv' => 'Svenska',
        'no' => 'Norsk',
        'da' => 'Dansk',
        'fi' => 'Suomi',
        'el' => 'Ελληνικά',
        'hu' => 'Magyar',
        'cs' => 'Čeština',
        'bg' => 'Български',
        'ro' => 'Română',
        'tr' => 'Türkçe',
        'hr' => 'Hrvatski',
        'ar' => 'العربية',
        'fili' => 'Filipino',
    ];

    // Sprache -> Flag-Dateicode (weil dein Ordner teils Länder-Codes nutzt)
    $flagMap = [
        'el' => 'gr',
        'cs' => 'cz',
        'zh' => 'ch',
    ];

    $flagUrlBase = ASSET_URL . '/core/main/img/flags/';
    $flagDiskBase = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/public/assets/core/main/img/flags/';

    if (!is_array($select)) {
        $select = array_filter(array_map('trim', explode($separator, (string) ($select ?? ''))));
    }

    $html = '';

    foreach ($languages as $code => $label) {
        $fileCode = $flagMap[$code] ?? $code;

        // webp bevorzugen, sonst png
        $flagUrl = '';
        if (is_file($flagDiskBase . $fileCode . '.webp')) {
            $flagUrl = $flagUrlBase . $fileCode . '.webp';
        } elseif (is_file($flagDiskBase . $fileCode . '.png')) {
            $flagUrl = $flagUrlBase . $fileCode . '.png';
        }

        $selected = in_array($code, $select, true) ? ' selected=""' : '';
        $dataFlag = $flagUrl ? ' data-flag="' . htmlspecialchars($flagUrl, ENT_QUOTES, 'UTF-8') . '"' : '';

        $html .= '<option value="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '"' . $selected . $dataFlag . '>'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . '</option>';
    }

    return $html;
}


function util_load_champions_select($select = [], $separator = '|')
{
    $html = '';

    // Accept both array and string input
    if (!is_array($select)) {
        $select = explode($separator, (string) ($select ?? ''));
    }

    // Normalize + remove empty/falsy values to avoid "select all" issues
    $select = array_values(array_filter(array_map('strval', $select), 'strlen'));

    $champions_array = json_decode(file_get_contents(SYS_PATH . '/public/uploads/lists/lol-champions.json'), true);

    foreach ($champions_array as $key => $value) {
        $imageUrl = LOL_CHAMP_URL . '/' . $key . '.png';

        if (in_array((string) $key, $select, true)) {
            $html .= "<option data-image='$imageUrl' value='$key' selected>$value</option>";
        } else {
            $html .= "<option data-image='$imageUrl' value='$key'>$value</option>";
        }
    }

    return $html;
}

function util_lol_queue_labels()
{
    return [
        0 => 'Custom',
        2 => 'Blind Pick',
        4 => 'Ranked Solo',
        6 => 'Ranked Premade',
        7 => 'Co-op vs AI',
        8 => '3v3 Normal',
        9 => '3v3 Ranked Flex',
        14 => 'Draft Pick',
        16 => 'Dominion Blind',
        17 => 'Dominion Draft',
        25 => 'Dominion Co-op vs AI',
        31 => 'Co-op vs AI Intro',
        32 => 'Co-op vs AI Beginner',
        33 => 'Co-op vs AI Intermediate',
        41 => '3v3 Ranked Team',
        42 => '5v5 Ranked Team',
        52 => '3v3 Co-op vs AI',
        61 => 'Team Builder',
        65 => 'ARAM',
        67 => 'ARAM Co-op vs AI',
        70 => 'One for All',
        72 => '1v1 Snowdown',
        73 => '2v2 Snowdown',
        75 => 'Hexakill',
        76 => 'URF',
        78 => 'One For All: Mirror',
        83 => 'Co-op vs AI URF',
        91 => 'Doom Bots Rank 1',
        92 => 'Doom Bots Rank 2',
        93 => 'Doom Bots Rank 5',
        96 => 'Ascension',
        98 => '3v3 Hexakill',
        100 => 'ARAM',
        300 => 'Poro King',
        310 => 'Nemesis',
        313 => 'Black Market Brawlers',
        315 => 'Nexus Siege',
        317 => 'Definitely Not Dominion',
        318 => 'ARURF',
        325 => 'All Random',
        400 => 'Normal Draft',
        410 => 'Ranked Dynamic',
        420 => 'Ranked Solo/Duo',
        430 => 'Normal Blind',
        440 => 'Ranked Flex',
        450 => 'ARAM',
        460 => '3v3 Blind Pick',
        470 => '3v3 Ranked Flex',
        480 => 'Swiftplay',
        490 => 'Quickplay',
        600 => 'Blood Hunt Assassin',
        610 => 'Dark Star: Singularity',
        700 => 'Clash',
        720 => 'ARAM Clash',
        800 => '3v3 Co-op vs AI Intermediate',
        810 => '3v3 Co-op vs AI Intro',
        820 => '3v3 Co-op vs AI Beginner',
        830 => 'Co-op vs AI Intro',
        840 => 'Co-op vs AI Beginner',
        850 => 'Co-op vs AI Intermediate',
        870 => 'Co-op vs AI Intro',
        880 => 'Co-op vs AI Beginner',
        890 => 'Co-op vs AI Intermediate',
        900 => 'ARURF',
        910 => 'Ascension',
        920 => 'Poro King',
        940 => 'Nexus Siege',
        950 => 'Doom Bots Voting',
        960 => 'Doom Bots Standard',
        980 => 'Star Guardian: Normal',
        990 => 'Star Guardian: Onslaught',
        1000 => 'PROJECT: Hunters',
        1010 => 'Snow ARURF',
        1020 => 'One for All',
        1030 => 'Odyssey: Intro',
        1040 => 'Odyssey: Cadet',
        1050 => 'Odyssey: Crewmember',
        1060 => 'Odyssey: Captain',
        1070 => 'Odyssey: Onslaught',
        1090 => 'TFT',
        1100 => 'Ranked TFT',
        1110 => 'TFT Tutorial',
        1111 => 'TFT Test',
        1200 => 'Nexus Blitz',
        1210 => "TFT Choncc's Treasure",
        1300 => 'Nexus Blitz',
        1400 => 'Ultimate Spellbook',
        1700 => 'Arena',
        1710 => 'Arena',
        1810 => 'Swarm Solo',
        1820 => 'Swarm Duo',
        1830 => 'Swarm Trio',
        1840 => 'Swarm Squad',
        1900 => 'Pick URF',
        2000 => 'Tutorial 1',
        2010 => 'Tutorial 2',
        2020 => 'Tutorial 3',
        2300 => 'Brawl',
        2400 => 'ARAM: Mayhem',
    ];
}

function util_lol_queue_type_labels()
{
    return [
        'solo/duo' => 'Solo/Duo',
        'flex' => 'Flex',
        'normal' => 'Normal Games',
        'aram' => 'ARAM',
        'arena' => 'Arena',
        'clash' => 'Clash',
        'bots' => 'Co-op vs AI',
        'teamfight-tactics' => 'TFT',
        'featured' => 'Featured / Rotating',
        'custom' => 'Custom Games',
        'tutorial' => 'Tutorial',
        'summoners_rift' => "Summoner's Rift",
    ];
}

function util_lol_queue_type_ids($type = null)
{
    $types = [
        'solo/duo' => [4, 420],
        'flex' => [6, 9, 41, 42, 440, 470],
        'normal' => [2, 8, 14, 400, 430, 460, 480, 490],
        'aram' => [65, 67, 100, 300, 450, 720, 920, 2400],
        'arena' => [1700, 1710],
        'clash' => [700, 720],
        'bots' => [7, 25, 31, 32, 33, 52, 67, 83, 800, 810, 820, 830, 840, 850, 870, 880, 890],
        'teamfight-tactics' => [1090, 1100, 1110, 1111, 1210],
        'featured' => [16, 17, 61, 70, 72, 73, 75, 76, 78, 91, 92, 93, 96, 98, 310, 313, 315, 317, 318, 325, 410, 600, 610, 900, 910, 940, 950, 960, 980, 990, 1000, 1010, 1020, 1030, 1040, 1050, 1060, 1070, 1200, 1300, 1400, 1810, 1820, 1830, 1840, 1900, 2300],
        'custom' => [0],
        'tutorial' => [2000, 2010, 2020],
        'summoners_rift' => [2, 4, 6, 7, 14, 31, 32, 33, 42, 61, 70, 75, 76, 83, 91, 92, 93, 310, 313, 315, 318, 325, 400, 410, 420, 430, 440, 480, 490, 600, 700, 830, 840, 850, 870, 880, 890, 900, 940, 950, 960, 1010, 1020, 1400, 1900, 2000, 2010, 2020],
    ];

    if ($type === null) {
        return $types;
    }

    return $types[$type] ?? [];
}

function util_format_lol_queue($queue_id)
{
    $queue_id = (int) $queue_id;
    $labels = util_lol_queue_labels();
    return $labels[$queue_id] ?? ('Queue ' . $queue_id);
}

function util_lol_queue_type_from_id($queue_id)
{
    $queue_id = (int) $queue_id;
    foreach (util_lol_queue_type_ids() as $type => $ids) {
        if (in_array($queue_id, $ids, true)) {
            return $type;
        }
    }
    return 'other';
}

function util_load_queue_type_select($select = [], $separator = '|')
{
    $html = '';
    if (!is_array($select)) {
        $select = explode($separator, $select ?? ' ');
    }

    $queue_types = function_exists('util_lol_queue_type_labels') ? util_lol_queue_type_labels() : [
        'solo/duo' => 'Solo/Duo',
        'flex' => 'Flex',
        'normal' => 'Normal Games',
        'aram' => 'ARAM',
        'arena' => 'Arena',
        'clash' => 'Clash',
        'bots' => 'Co-op vs AI',
        'teamfight-tactics' => 'TFT',
        'featured' => 'Featured / Rotating',
        'custom' => 'Custom Games',
        'tutorial' => 'Tutorial',
        'summoners_rift' => "Summoner's Rift",
    ];

    foreach ($queue_types as $key => $value) {
        if (isset($select) && in_array($key, $select)) {
            $html .= "<option value='$key' selected>$value</option>";
        } else {
            $html .= "<option value='$key'>$value</option>";
        }
    }
    return $html;
}

function util_load_start_lp_select($select = [], $separator = '|')
{
    $html = '';
    if (!is_array($select)) {
        $select = explode($separator, $select ?? ' ');
    }
    $queue_types = [
        '0-20' => '0-20 LP',
        '21-40' => '21-40 LP',
        '41-60' => '41-60 LP',
        '61-80' => '61-80 LP',
        '81-100' => '81-100 LP',
    ];

    foreach ($queue_types as $key => $value) {
        if (isset($select) && in_array($key, $select)) {
            $html .= "<option value='$key' selected>$value</option>";
        } else {
            $html .= "<option value='$key'>$value</option>";
        }
    }
    return $html;
}

function util_load_lp_gain_select($select = [], $separator = '|')
{
    $html = '';
    if (!is_array($select)) {
        $select = explode($separator, $select ?? ' ');
    }
    $queue_types = [
        '10-19' => '10-19 LP',
        '20-24' => '20-24 LP',
        '25-29' => '25-29 LP',
        '30+' => '30+ LP',
    ];

    foreach ($queue_types as $key => $value) {
        if (isset($select) && in_array($key, $select)) {
            $html .= "<option value='$key' selected>$value</option>";
        } else {
            $html .= "<option value='$key'>$value</option>";
        }
    }
    return $html;
}

function util_load_agents_select($select = [], $separator = '|')
{
    $html = '';
    if (!is_array($select)) {
        $select = explode($separator, $select ?? ' ');
    }
    $champions_array = json_decode(file_get_contents(SYS_PATH . '/public/uploads/lists/val-agents.json'), true);
    foreach ($champions_array as $key => $value) {
        if (isset($select) && in_array($key, $select)) {
            $html .= "<option data-image='{$value['icon']}' value='$key' selected>{$value['name']}</option>";
        } else {
            $html .= "<option data-image='{$value['icon']}' value='$key'>{$value['name']}</option>";
        }
    }
    return $html;
}

function db_get_row($table, $params = [], $return_array = false)
{
    global $db;
    $params = db_format_params($params, $table);
    $query = "SELECT * FROM $table $params[where] $params[order] $params[limit]";
    $results = $db->query("SET session wait_timeout=28800");
    // UPDATE - this is also needed
    $results = $db->query("SET session interactive_timeout=28800");
    $rows = $db->row($query);
    if (!empty($rows)) {
        return $rows;
    } else {
        if ($return_array) {
            return [];
        } else {
            return false;
        }
    }
}

function db_get_row_count($table, $params = [])
{
    global $db;
    $params = db_format_params($params, $table);
    $query = "SELECT COUNT(*) FROM $table $params[where]";
    $rows = $db->single($query);
    if (!empty($rows)) {
        return $rows;
    } else {
        return false;
    }
}

function db_get_rows($table, $params = [], $return_array = false)
{
    global $db;
    $params = db_format_params($params, $table);
    $query = "SELECT $params[select] FROM $table $params[where] $params[order] $params[limit] $params[offset]";
    $rows = $db->run($query);
    if (!empty($rows)) {
        return $rows;
    } else {
        if ($return_array) {
            return [];
        } else {
            return false;
        }
    }
}

function db_get_query($table, $params = [])
{
    $params = db_format_params($params, $table);
    $query = "SELECT $params[select] FROM $table $params[where] $params[order] $params[limit]";
    return $query;
}

function db_add_row($table, $data)
{
    global $db;
    $db->insert($table, $data);
    $id = $db->lastInsertId();
    return $id;
}

function db_insert_row($table, $data)
{
    global $db;
    $db->insert($table, $data);
    $id = $db->lastInsertId();
    return $id;
}


function db_update_row($table, $params, $data)
{
    global $db;
    $db->update($table, $data, $params);
    return true;
}

function db_delete_rows($table, $params)
{
    global $db;

    $params = db_format_params($params, $table);

    $query = "DELETE FROM $table $params[where]";
    $db->query($query);
    return true;
}


function db_auth_login($login, $password, $table)
{
    global $db;
    if ($table != "admins") {
        $query = 'SELECT id FROM ' . $table . ' WHERE email = ? AND password = ?';
    } else {
        $query = 'SELECT id FROM ' . $table . ' WHERE login = ? AND password = ?';
    }
    $row = $db->row($query, $login, $password);

    return empty($row) ? false : $row['id'];
}

function db_auth_session_delete($id, $table)
{
    global $db;

    $id = (int) $id;
    $table = preg_replace('/[^a-z_]/', '', (string) $table);

    if ($id <= 0 || $table === '') {
        return false;
    }

    $query = 'DELETE FROM ' . $table . '_sessions WHERE ' . $table . '_id = ?';
    $db->run($query, $id);

    // Logging out is an explicit "I'm gone" — don't wait for the grace window.
    if ($table === 'booster' && function_exists('lb_booster_set_status')) {
        lb_booster_set_status($id, LB_BOOSTER_STATUS_OFFLINE);
    }

    return true;
}
function get_device_info()
{
    $device = new DeviceDetector($_SERVER['HTTP_USER_AGENT']);
    $device->parse();
    $device_info = [
        'brand' => $device->getBrandName(),
        'os' => $device->getOs(),
        'client' => $device->getClient(),
        'device' => $device->getDeviceName(),
        'model' => $device->getModel(),
    ];
    return $device_info;
}

function lb_is_public_ip_address($ip)
{
    if (!is_string($ip) || trim($ip) === '') {
        return false;
    }

    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) !== false;
}

function lb_lookup_ip_location($ip)
{
    $result = [
        'city' => null,
        'region' => null,
        'country' => null,
    ];

    if (!lb_is_public_ip_address($ip)) {
        return $result;
    }

    $url = 'https://api.country.is/' . rawurlencode($ip) . '?fields=city,subdivision';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 2.5,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\nUser-Agent: LoLBoost/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $json = @file_get_contents($url, false, $context);
    if (!is_string($json) || trim($json) === '') {
        return $result;
    }

    $data = json_decode($json, true);
    if (!is_array($data)) {
        return $result;
    }

    $result['city'] = isset($data['city']) && is_array($data['city'])
        ? trim((string) ($data['city']['name'] ?? ''))
        : trim((string) ($data['city'] ?? ''));

    $result['region'] = isset($data['subdivision']) && is_array($data['subdivision'])
        ? trim((string) ($data['subdivision']['name'] ?? ''))
        : trim((string) ($data['subdivision'] ?? ''));

    $result['country'] = isset($data['country']) && is_array($data['country'])
        ? trim((string) ($data['country']['name'] ?? ''))
        : trim((string) ($data['country'] ?? ''));

    if ($result['city'] === '') $result['city'] = null;
    if ($result['region'] === '') $result['region'] = null;
    if ($result['country'] === '') $result['country'] = null;

    return $result;
}

function db_auth_session_start($id, $table)
{
    global $db;

    $table = substr($table, 0, -1);

    $token = "lolbstgg-" . $table . "-" . Uuid::uuid4()->toString();

    // Session policy:
    //   admin   → stay logged in on multiple devices (persistent with "remember me").
    //   seller  → multiple simultaneous logins allowed (teams share one account).
    //   client  → multiple devices allowed.
    //   e-girl  → (is_egirl=1 on the boosters table) persistent / multi-device.
    //   booster → single active IP: a login from a DIFFERENT IP logs out the other IP.
    // None of these get a blanket "delete other sessions" on login — that is handled
    // per-role below. Any OTHER table falls back to single-session.
    if (!in_array($table, ['admin', 'client', 'seller', 'booster'], true)) {
        db_auth_session_delete($id, $table);
    }

    $base_device_info = get_device_info();
    $session_device_info = json_encode($base_device_info, JSON_UNESCAPED_SLASHES);
    $ipAddress = get_ip_address();

    // easydb insert query
    $db->insert($table . '_sessions', [
        'token' => $token,
        $table . '_id' => $id,
        'device_info' => $session_device_info,
        'ip_address' => $ipAddress,
    ]);

    if ($table === 'booster') {
        // Logging in counts as "I'm here": go Online unless the booster explicitly
        // parked themselves on Away/Offline before. Only the stale timestamp is refreshed then.
        if (function_exists('lb_booster_presence_state')) {
            $presence = lb_booster_presence_state((int) $id);
            if (($presence['status'] ?? '') === LB_BOOSTER_STATUS_ONLINE) {
                lb_booster_presence_touch((int) $id);
            } elseif (empty($presence['status']) || $presence['status'] === LB_BOOSTER_STATUS_OFFLINE) {
                lb_booster_set_status((int) $id, LB_BOOSTER_STATUS_ONLINE);
            } else {
                lb_booster_presence_touch((int) $id);
            }
        }

        // E-girls share the boosters table (is_egirl = 1) but must stay logged in
        // persistently across devices — only REAL boosters get the single-IP rule.
        $isEgirl = false;
        try {
            $egRow = $db->run("SELECT is_egirl FROM boosters WHERE id = ? LIMIT 1", (int) $id);
            $isEgirl = !empty($egRow[0]['is_egirl']);
        } catch (Throwable $e) {
            $isEgirl = false;
        }

        if (!$isEgirl) {
            // Booster policy: one active location. A login from a DIFFERENT IP removes
            // every session that is not on the current IP (the previous device/location
            // is logged out). Multiple tabs/devices on the SAME IP stay logged in.
            try {
                $db->run(
                    "DELETE FROM booster_sessions
                      WHERE booster_id = ?
                        AND token <> ?
                        AND (ip_address IS NULL OR ip_address <> ?)",
                    (int) $id,
                    $token,
                    $ipAddress
                );
            } catch (Throwable $e) {
                // Keep login working even if the cleanup query fails.
            }
        }
        // else: e-girl → keep all existing sessions (persistent, multi-device).

        $boosterId = (int) $id;
        if ($boosterId > 0) {
            $location = lb_lookup_ip_location($ipAddress);

            $historyDeviceInfo = json_encode([
                'ua' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'os' => (string) ($base_device_info['os']['name'] ?? ''),
                'browser' => (string) ($base_device_info['client']['name'] ?? ''),
                'device' => (string) ($base_device_info['device'] ?? ''),
                'brand' => (string) ($base_device_info['brand'] ?? ''),
                'model' => (string) ($base_device_info['model'] ?? ''),
                'city' => $location['city'],
                'region' => $location['region'],
                'country' => $location['country'],
                'ts' => date('c'),
            ], JSON_UNESCAPED_SLASHES);

            $db->insert('booster_sessions_history', [
                'booster_id' => $boosterId,
                'token' => $token,
                'device_info' => $historyDeviceInfo,
                'ip_address' => $ipAddress,
                'city' => $location['city'],
                'region' => $location['region'],
                'country' => $location['country'],
            ]);

            $newHistoryId = (int)$db->lastInsertId();
            if ($newHistoryId > 0) {
                lb_check_booster_session_security_alert($boosterId, [
                    'id' => $newHistoryId,
                    'booster_id' => $boosterId,
                    'token' => $token,
                    'device_info' => $historyDeviceInfo,
                    'ip_address' => $ipAddress,
                    'created_at' => date('Y-m-d H:i:s'),
                    'city' => $location['city'],
                    'region' => $location['region'],
                    'country' => $location['country'],
                ]);
            }

            $db->run(
                "DELETE FROM booster_sessions_history
                 WHERE booster_id = ?
                   AND id NOT IN (
                        SELECT id FROM (
                            SELECT id
                            FROM booster_sessions_history
                            WHERE booster_id = ?
                            ORDER BY created_at DESC, id DESC
                            LIMIT 50
                        ) keep_rows
                   )",
                $boosterId,
                $boosterId
            );
        }
    }

    return $token;
}

function db_auth_session_end($token, $table)
{
    global $db;

    // $table wird ausschliesslich intern gesetzt -> Whitelist statt Escaping.
    $allowed = ['booster', 'client', 'seller', 'admin'];
    if (!in_array($table, $allowed, true)) {
        return false;
    }

    // $token stammt aus einem Cookie und darf NICHT in den Query-String
    // konkateniert werden. Vorher: '... WHERE token = "' . $token . '"'
    $db->run('DELETE FROM ' . $table . '_sessions WHERE token = ?', $token);
    return true;
}
function log_admin_action($admin_id, $action): int
{
    return (int) db_insert_row('admin_logs', [
        'admin_id' => (int) $admin_id,
        'action' => (string) $action,
    ]);
}

function generate_password($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}

function verify_discount($discount_data, $service)
{
    $service = str_replace('order', 'boosting', $service);
    $service = str_replace('account', 'smurf_shop', $service);
    if ($discount_data['status'] == 1) {
        if (isset($discount_data['max_uses']) && (int)$discount_data['max_uses'] > 0 && (int)$discount_data['uses'] >= (int)$discount_data['max_uses']) {
            return [false, 'This discount code has already been used 😥'];
        }

        // if current time is between discount start date and end date
        if (time() >= strtotime($discount_data['starts_at']) && time() <= strtotime($discount_data['expires_at'])) {
            // if discount is for all services
            $discount_services_array = [];
            if (!empty($discount_data['services'])) {
                $discount_services_array = explode(',', $discount_data['services']);
            }

            if (in_array($service, $discount_services_array)) {
                return [true, 'Discount code applied successfully 🎉'];
            } else {
                return [false, 'Discount code can only be used in ' . ucwords($discount_data['services']) . ' 😥 ' . $service];
            }
        } else {
            return [false, 'Discount code has expired 😥'];
        }
    } else {
        return [false, 'Discount code has expired 😥'];
    }
}


function apply_discount($discount_data, $price)
{
    $discount = 0;
    if ($discount_data['is_fixed'] != 1) {
        $discount_data['amount'] = util_format_price_input($discount_data['amount']);
        $discount = $price * $discount_data['amount'];
    } else {
        if (isset($_SESSION['currency']) && $_SESSION['currency'] == 'USD') {
            $discount = $discount_data['amount'] * get_exchange_rate();
        } else {
            $discount = $discount_data['amount'];
        }
    }
    $price = round($price - $discount, 2);
    if ($price < 0) {
        $price = 0;
    }

    // Important: applying a discount only recalculates the price.
    // The usage counter is increased only after the invoice is actually PAID.
    return $price;
}

if (!function_exists('lb_generic_game_rank_config')) {
    // Authoritative rank tier / division config for the newer generic-form games, keyed by
    // canonical game slug. Overrides whatever placeholder rank list the boost form's DB row
    // may already have — same "known games win" reasoning as the options-bar field config in
    // rank-dynamic.php. Returns null for games not in this list (existing DB-driven behavior
    // stays untouched for those).
    function lb_generic_game_rank_config(string $gameSlug, string $formSlug = ''): ?array {
        static $aliases = [
            'rivals' => 'marvel-rivals', 'marvel-rival' => 'marvel-rivals', 'marvel_rivals' => 'marvel-rivals',
            'wild-rift' => 'lol-wild-rift', 'wild_rift' => 'lol-wild-rift', 'wildrift' => 'lol-wild-rift',
            'rl' => 'rocket-league', 'rocket_league' => 'rocket-league',
            'ow2' => 'overwatch-2', 'ow' => 'overwatch-2', 'overwatch' => 'overwatch-2', 'overwatch_2' => 'overwatch-2',
            'apex' => 'apex-legends', 'apex_legends' => 'apex-legends',
        ];
        $key = $aliases[strtolower(trim($gameSlug))] ?? strtolower(trim($gameSlug));

        // CS2 Wingman shares the game slug with CS2 Premier but uses the classic
        // Silver-to-Supreme-Master-First-Class ladder instead of Premier's CS Rating
        // buckets — keyed off the boost form's own slug, not the game, since both
        // forms live under the same "counter-strike-2" game.
        if ($key === 'counter-strike-2' && strpos(strtolower(trim($formSlug)), 'wingman') !== false) {
            return [
                'ranks' => [
                    1 => 'Silver I', 2 => 'Silver II', 3 => 'Silver III', 4 => 'Silver IV',
                    5 => 'Silver Elite', 6 => 'Silver Elite Master',
                    7 => 'Gold Nova I', 8 => 'Gold Nova II', 9 => 'Gold Nova III', 10 => 'Gold Nova Master',
                    11 => 'Master Guardian I', 12 => 'Master Guardian II', 13 => 'Master Guardian Elite',
                    14 => 'Distinguished Master Guardian', 15 => 'Legendary Eagle', 16 => 'Legendary Eagle Master',
                    17 => 'Supreme Master First Class', 18 => 'The Global Elite',
                ],
                'rank_divs' => array_fill(1, 18, 0),
                'flat_tiers' => range(1, 18),
                // The Global Elite (18) is desired-only — nobody starts a boost already at
                // the very top, same pattern as Fortnite's Unreal tier.
                'start_max_tier' => 17,
                'rank_files' => [
                    1 => 'silver-1', 2 => 'silver-2', 3 => 'silver-3', 4 => 'silver-4',
                    5 => 'silver-elite', 6 => 'silver-elite-master',
                    7 => 'gold-nova-1', 8 => 'gold-nova-2', 9 => 'gold-nova-3', 10 => 'gold-nova-master',
                    11 => 'master-guardian-1', 12 => 'master-guardian-2', 13 => 'master-guardian-elite',
                    14 => 'distinguished-master-guardian', 15 => 'legendary-eagle', 16 => 'legendary-eagle-master',
                    17 => 'supreme-master-first-class', 18 => 'the-global-elite',
                ],
            ];
        }

        $configs = [
            // Current Rank stops one tier below Desired Rank's top tier for all of these except
            // Apex, where Master is an open-ended RP ladder you can both be at and climb within.
            'marvel-rivals' => [
                'ranks' => [1 => 'Bronze', 2 => 'Silver', 3 => 'Gold', 4 => 'Platinum', 5 => 'Diamond', 6 => 'Grandmaster', 7 => 'Celestial', 8 => 'Eternity'],
                'rank_divs' => [1 => 3, 2 => 3, 3 => 3, 4 => 3, 5 => 3, 6 => 3, 7 => 3, 8 => 0],
                'start_max_tier' => 7,
                'flat_tiers' => [8],
            ],
            'lol-wild-rift' => [
                'ranks' => [1 => 'Iron', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum', 6 => 'Emerald', 7 => 'Diamond', 8 => 'Master', 9 => 'Grandmaster', 10 => 'Challenger'],
                'rank_divs' => [1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 4, 6 => 4, 7 => 4, 8 => 0, 9 => 0, 10 => 0],
                'start_max_tier' => 9,
                'flat_tiers' => [8, 9, 10], // Master, Grandmaster and Challenger have no divisions
            ],
            'rocket-league' => [
                'ranks' => [1 => 'Bronze', 2 => 'Silver', 3 => 'Gold', 4 => 'Platinum', 5 => 'Diamond', 6 => 'Champion', 7 => 'GrandChampion', 8 => 'SupersonicLegend'],
                'rank_divs' => [1 => 3, 2 => 3, 3 => 3, 4 => 3, 5 => 3, 6 => 3, 7 => 3, 8 => 0],
                'start_max_tier' => 7,
                'flat_tiers' => [8], // SupersonicLegend: no divisions, no points input at all
            ],
            'overwatch-2' => [
                'ranks' => [1 => 'Bronze', 2 => 'Silver', 3 => 'Gold', 4 => 'Platinum', 5 => 'Diamond', 6 => 'Master', 7 => 'Grandmaster', 8 => 'Champion', 9 => 'Top500'],
                'rank_divs' => [1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5, 6 => 5, 7 => 5, 8 => 5, 9 => 0],
                'start_max_tier' => 8,
                'flat_tiers' => [9], // Top500: no divisions, no points input at all
            ],
            'counter-strike-2' => [
                'ranks' => [
                    1 => 'Below 5,000', 2 => '5,000 - 9,999', 3 => '10,000 - 14,999',
                    4 => '15,000 - 19,999', 5 => '20,000 - 24,999', 6 => '25,000 - 29,999', 7 => '30,000+',
                ],
                'rank_divs' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0],
                'rank_files' => [1 => 'grey', 2 => 'light-blue', 3 => 'blue', 4 => 'purple', 5 => 'pink', 6 => 'red', 7 => 'gold'],
                'points_min_start' => [1 => 0, 2 => 5000, 3 => 10000, 4 => 15000, 5 => 20000, 6 => 25000, 7 => 30000],
                'points_min_end' => [1 => 100, 2 => 5000, 3 => 10000, 4 => 15000, 5 => 20000, 6 => 25000, 7 => 30000],
                'points_max_start' => [1 => 4999, 2 => 9999, 3 => 14999, 4 => 19999, 5 => 24999, 6 => 29999, 7 => 40000],
                'points_max_end' => [1 => 4999, 2 => 9999, 3 => 14999, 4 => 19999, 5 => 24999, 6 => 29999, 7 => 40000],
                'points_max' => 40000,
                'points_step' => 100,
            ],
            'apex-legends' => [
                'ranks' => [1 => 'Rookie', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum', 6 => 'Diamond', 7 => 'Master'],
                'rank_divs' => [1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 4, 6 => 4, 7 => 0],
                'start_max_tier' => 7, // Master is selectable as both Current and Desired
                'points_min_start' => [7 => 15050],
                'points_min_end' => [7 => 15051],
                'points_max' => 50000,
                // Master is an open RP ladder. Predator is a title for the highest-ranked
                // Master players, not another purchasable rank/division.
                'points_step' => 1,
                // No flat_tiers here — Master keeps the RP number input (it's an open ladder).
            ],
        ];
        return $configs[$key] ?? null;
    }
}

if (!function_exists('lb_booster_game_rank_icon_url')) {
    function lb_booster_game_rank_icon_url(string $gameSlug, int $rankTier): string {
        if ($rankTier <= 0) return '';

        static $aliases = [
            'rivals' => 'marvel-rivals', 'marvel_rivals' => 'marvel-rivals',
            'wild-rift' => 'lol-wild-rift', 'wild_rift' => 'lol-wild-rift', 'wildrift' => 'lol-wild-rift',
            'rl' => 'rocket-league', 'rocket_league' => 'rocket-league',
            'ow2' => 'overwatch-2', 'overwatch' => 'overwatch-2', 'overwatch_2' => 'overwatch-2',
            'apex' => 'apex-legends', 'apex_legends' => 'apex-legends',
        ];
        $game = strtolower(trim($gameSlug));
        $game = $aliases[$game] ?? $game;
        $config = lb_generic_game_rank_config($game);
        $rankName = trim((string)(($config['ranks'] ?? [])[$rankTier] ?? ''));
        if ($rankName === '') return '';

        $file = trim((string)(($config['rank_files'] ?? [])[$rankTier] ?? ''));
        if ($file === '') $file = strtolower((string)preg_replace('/[^a-z0-9]+/i', '-', $rankName));
        $file = trim($file, '-');
        $candidates = array_values(array_unique([$file, str_replace('-', '', $file)]));
        foreach ($candidates as $candidate) {
            foreach (['webp', 'png', 'svg', 'jpg', 'jpeg'] as $ext) {
                $relative = 'website/images/boosting/ranks/' . $game . '/' . $candidate . '.' . $ext;
                if (is_file(SYS_PATH . 'public/assets/' . $relative)) {
                    return ASSET_URL . '/' . $relative;
                }
            }
        }
        return '';
    }
}

if (!function_exists('lb_resolve_character_icon_url')) {
    // Looks up a locally hosted hero/legend/role icon (public/assets/website/images/boosting/<game>/...)
    // by slugified name. Used so order displays show a real icon for non-LoL games (Marvel Rivals,
    // Apex Legends, Overwatch 2) instead of a broken League of Legends ddragon URL.
    function lb_resolve_character_icon_url(string $name, string $kind = 'champion'): string {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string)$slug, '-');
        if ($slug === '') return '';

        $dirs = $kind === 'role' ? ['marvel-rivals/roles'] : ['apex-legends/legends', 'overwatch-2/heroes', 'marvel-rivals/heroes'];
        $candidates = $kind === 'role' ? [$slug] : [$slug, 'mr-' . $slug];

        foreach ($dirs as $dir) {
            foreach ($candidates as $cand) {
                foreach (['webp', 'png', 'svg', 'jpg', 'jpeg'] as $ext) {
                    $rel = $dir . '/' . $cand . '.' . $ext;
                    if (is_file(SYS_PATH . 'public/assets/website/images/boosting/' . $rel)) {
                        return ASSET_URL . '/website/images/boosting/' . $rel;
                    }
                }
            }
        }
        return '';
    }
}

function util_format_roles($roles)
{
    $roles_html = '';
    if (!empty($roles)) {
        $roles = explode(',', $roles);
        foreach ($roles as $role) {
            $role = trim($role);
            if ($role === '') continue;
            $icon = lb_resolve_character_icon_url($role, 'role');
            $roleKey = strtolower((string)preg_replace('/[^a-z0-9]+/i', '', $role));
            $roleFiles = ['toplane' => 'TopLane', 'jungle' => 'Jungle', 'midlane' => 'MidLane', 'adcarry' => 'AdCarry', 'support' => 'Support'];
            $roleFile = $roleFiles[$roleKey] ?? str_replace(' ', '', $role);
            $src = $icon !== '' ? $icon : (ASSET_URL . '/core/main/img/lol/roles/' . $roleFile . '.svg');
            $roles_html .= '<img  style="height:20px" src="' . htmlspecialchars($src, ENT_QUOTES) . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . htmlspecialchars($role, ENT_QUOTES) . '">';
        }
    } else {
        $roles_html = 'None';
    }
    return $roles_html;
}
function util_format_champions($champions)
{
    $champions_html = '';
    if (!empty($champions)) {
        if (!is_array($champions)) {
            $champions = explode(',', $champions);
        }
        foreach ($champions as $champion) {
            $champion = trim($champion);
            if ($champion === '') continue;
            $label = ($champion == 'MonkeyKing') ? 'Wukong' : $champion;
            $icon = lb_resolve_character_icon_url($champion, 'champion');
            $src = $icon !== '' ? $icon : (LOL_CHAMP_URL . '/' . $champion . '.png');
            $champions_html .= ' <img class="rounded-circle border" style="height:20px" src="' . htmlspecialchars($src, ENT_QUOTES) . '" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-inverse" data-bs-placement="bottom" title="' . htmlspecialchars($label, ENT_QUOTES) . '">';
        }
    } else {
        $champions_html = 'None';
    }
    return $champions_html;
}

function util_format_agents($agents)
{
    $agents_list = json_decode(file_get_contents(SYS_PATH . '/public/uploads/lists/val-agents.json'), true);
    $agents_html = '';
    if (!empty($agents)) {
        if (!is_array($agents)) {
            $agents = explode(',', $agents);
        }
        foreach ($agents as $agent) {
            $agents_html .= ' <img class="rounded-circle border" style="height:20px" src="' . $agents_list[$agent]['icon'] . '" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-inverse" data-bs-placement="bottom" title="' . $agent . '">';
        }
    } else {
        $agents_html = 'None';
    }
    return $agents_html;
}

function util_load_country_list($country = null)
{
    $html = '';
    $country_names = ["Afghanistan", "Aland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Cook Islands", "Costa Rica", "Croatia", "Cuba", "Curaçao", "Cyprus", "Czech Republic", "Côte d\'Ivoire", "Democratic Republic of the Congo", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands", "Faroe Islands", "Federated States of Micronesia", "Fiji", "Finland", "Former Yugoslav Republic of Macedonia", "France", "French Polynesia", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Holy See", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mexico", "Moldova", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "North Korea", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Republic of the Congo", "Romania", "Russia", "Rwanda", "Saint Barthélemy", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Sint Maarten", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "State of Palestine", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Western Sahara", "Yemen", "Zambia", "Zimbabwe"];
    $country_list = ["AF", "AX", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AW", "AU", "AT", "AZ", "BS", "BH", "BD", "BB", "BY", "BE", "BZ", "BJ", "BM", "BT", "BO", "BA", "BW", "BR", "IO", "BN", "BG", "BF", "BI", "CV", "KH", "CM", "CA", "KY", "CF", "TD", "CL", "CN", "CX", "CC", "CO", "KM", "CK", "CR", "HR", "CU", "CW", "CY", "CZ", "CI", "CD", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "GQ", "ER", "EE", "ET", "FK", "FO", "FM", "FJ", "FI", "MK", "FR", "PF", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GD", "GU", "GT", "GG", "GN", "GW", "GY", "HT", "VA", "HN", "HK", "HU", "IS", "IN", "ID", "IR", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LR", "LY", "LI", "LT", "LU", "MO", "MG", "MW", "MY", "MV", "ML", "MT", "MH", "MQ", "MR", "MU", "MX", "MD", "MC", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "NF", "KP", "MP", "NO", "OM", "PK", "PW", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "CG", "RO", "RU", "RW", "BL", "KN", "LC", "VC", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SX", "SK", "SI", "SB", "SO", "ZA", "KR", "SS", "ES", "LK", "PS", "SD", "SR", "SZ", "SE", "CH", "SY", "TW", "TJ", "TZ", "TH", "TL", "TG", "TK", "TO", "TT", "TN", "TR", "TM", "TC", "TV", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "VG", "VI", "EH", "YE", "ZM", "ZW"];
    foreach ($country_list as $key => $country_code) {
        if ($country_code == $country) {
            $html .= '<option value="' . $country_code . '" selected="">' . $country_names[$key] . '</option>';
        } else {
            $html .= '<option value="' . $country_code . '">' . $country_names[$key] . '</option>';
        }
    }
    return $html;
}

function util_format_country($country_iso)
{
    if (!empty($country_iso) && $country_iso != "none") {
        $url = "https://restcountries.com/v3.1/alpha/" . rawurlencode($country_iso) . "?fields=name";
        $ctx = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true]]);
        $raw = @file_get_contents($url, false, $ctx);
        $data = $raw !== false ? json_decode($raw, true) : null;
        $name = $data['name']['common'] ?? null;
        // Fall back to the raw ISO code instead of emitting warnings when the
        // external lookup fails or returns no name.
        return $name !== null ? esc($name) : esc($country_iso);
    } else {
        return "None";
    }
}

function util_format_option_emoji($option)
{
    $arr = [
        'roles' => '🧭',
        'champions' => '🏆',
        'agents' => '🏆',
        'flash_position' => '💥',
        'vpn_country' => '🌎',
        'is_priority' => '🔥',
        'is_streaming' => '🍿',
        'is_solo_only' => '🔏',
        'is_bonus_win' => '🏅',
        'is_offline_mode' => '🔇',
        'is_coaching' => '🔊',
        'is_hidden_duo' => '👻',
        'is_undercover_winrate' => '🕵️',
        'is_moderate_kda' => '📊',
    ];
    if (array_key_exists($option, $arr)) {
        return $arr[$option];
    } else {
        // get first letter of option
        $first_letter = mb_substr($option, 0, 1);
        return ucfirst($first_letter);
    }
}

function util_format_option($name, $value)
{
    if (str_contains($name, 'is_')) {
        $value = util_format_yes_no($value);
    } else {
        switch ($name) {
            case 'roles':
                $value = util_format_roles($value);
                break;
            case 'champions':
                $value = util_format_champions($value);
                break;
            case 'agents':
                $value = util_format_agents($value);
                break;
            case 'vpn_country':
                $value = util_format_country($value);
                break;
            default:
                $value = util_format_default_type($value);
                break;
        }
    }
    $name = str_replace('is_', '', $name);
    $name = util_format_default_type($name);

    if ($name == 'Coaching') {
        $name = 'Voice Chat';
    }

    if ($name == 'Champions') {
        $name = 'Customer Champions';
    }

    if ($name == 'Moderate Kda') {
        $name = 'Moderate KDA';
    }

    return [$name, $value];
}

function util_format_solo($name, $value)
{
    if (str_contains($name, 'is_')) {
        $value = util_format_solo_duo($value);
    } else {
        switch ($name) {
            case 'roles':
                $value = util_format_roles($value);
                break;
            case 'champions':
                $value = util_format_champions($value);
                break;
            case 'agents':
                $value = util_format_agents($value);
                break;
            case 'vpn_country':
                $value = util_format_country($value);
                break;
            default:
                $value = util_format_default_type($value);
                break;
        }
    }
    $name = str_replace('is_', '', $name);
    $name = util_format_default_type($name);
    return [$name, $value];
}
require_once __DIR__ . '/chat_functions_db.php';
require_once __DIR__ . '/legacy_chat_db.php';


function create_payment_stripe($invoice_data)
{
    \Stripe\Stripe::setApiKey(STRIPE_API_KEY);
    if (empty($invoice_data['description'])) {
        $invoice_data['description'] = "Payment";
    }
    $item_array[] = [
        'price_data' => [
            'product_data' => [
                'name' => $invoice_data['description'],
                'metadata' => [
                    'pro_id' => $invoice_data['order_id']
                ],
                'description' => util_format_default_type($invoice_data['order_type']),
            ],
            'unit_amount' => $invoice_data['total_price'],
            'currency' => $invoice_data['currency'],
        ],
        'quantity' => 1,
    ];

    $payment_method_types = match ($invoice_data['currency']) {
        'EUR' => ['card', 'klarna', 'giropay', 'ideal', 'eps', 'p24', 'bancontact'],
        'USD' => ['card'],
        default => ['card', 'klarna', 'giropay', 'ideal', 'eps', 'p24', 'bancontact'],
    };

    $cancel_url = $invoice_data['order_type'] == 'addon' ? "order/$invoice_data[order_id]" : "checkout/$invoice_data[uuid]";

    $session = \Stripe\Checkout\Session::create([
        'line_items' => $item_array,
        'mode' => 'payment',
        'payment_method_types' => $payment_method_types,
        'customer_email' => CLIENT_DATA['email'],
        'client_reference_id' => (string)$invoice_data['id'],
        'metadata' => [
            'invoice_id' => (string)$invoice_data['id'],
            'order_id' => (string)$invoice_data['order_id'],
            'order_type' => (string)$invoice_data['order_type'],
        ],
        'payment_intent_data' => [
            'metadata' => [
                'invoice_id' => (string)$invoice_data['id'],
                'order_id' => (string)$invoice_data['order_id'],
                'order_type' => (string)$invoice_data['order_type'],
            ],
        ],
        'success_url' => BASE_URL . '/checkout/' . $invoice_data['uuid'] . '/process?m=stripe&t={CHECKOUT_SESSION_ID}',
        'cancel_url' => BASE_URL . '/' . $cancel_url,
    ]);
    return $session->url;
}

function create_payment_stripe_paypal($invoice_data)
{
    \Stripe\Stripe::setApiKey(STRIPE_API_KEY);
    if (empty($invoice_data['description'])) {
        $invoice_data['description'] = "Payment";
    }
    $item_array[] = [
        'price_data' => [
            'product_data' => [
                'name' => $invoice_data['description'],
                'metadata' => [
                    'pro_id' => $invoice_data['order_id']
                ],
                'description' => util_format_default_type($invoice_data['order_type']),
            ],
            'unit_amount' => $invoice_data['total_price'],
            'currency' => $invoice_data['currency'],
        ],
        'quantity' => 1,
    ];

    $cancel_url = $invoice_data['order_type'] == 'addon' ? "order/$invoice_data[order_id]" : "checkout/$invoice_data[uuid]";

    $session = \Stripe\Checkout\Session::create([
        'line_items' => $item_array,
        'payment_method_types' => ['paypal'],
        'mode' => 'payment',
        'customer_email' => CLIENT_DATA['email'],
        'client_reference_id' => (string)$invoice_data['id'],
        'metadata' => [
            'invoice_id' => (string)$invoice_data['id'],
            'order_id' => (string)$invoice_data['order_id'],
            'order_type' => (string)$invoice_data['order_type'],
        ],
        'payment_intent_data' => [
            'metadata' => [
                'invoice_id' => (string)$invoice_data['id'],
                'order_id' => (string)$invoice_data['order_id'],
                'order_type' => (string)$invoice_data['order_type'],
            ],
        ],
        'success_url' => BASE_URL . '/checkout/' . $invoice_data['uuid'] . '/process?m=stripe&t={CHECKOUT_SESSION_ID}',
        'cancel_url' => BASE_URL . '/' . $cancel_url,
    ]);
    return $session->url;
}

function create_payment_coinbase($invoice_data)
{

    ApiClient::init(COINBASE_API_KEY);
    $cancel_url = $invoice_data['order_type'] == 'addon' ? BASE_URL . "/order/$invoice_data[order_id]" : BASE_URL . "/checkout/$invoice_data[uuid]";

    $chargeObj = new Charge([
        "name" => ucfirst($invoice_data['order_type']) . ' #' . $invoice_data['order_id'],
        "description" => $invoice_data['description'] ?? 'Payment',
        'local_price' => [
            "amount" => util_format_price_input($invoice_data['total_price']),
            "currency" => $invoice_data['currency']
        ],
        'pricing_type' => 'fixed_price',
        "requested_info" => ["name", "email"],
        "metadata" => [
            'invoiceId' => $invoice_data['id']
        ],
        "redirect_url" => BASE_URL . "/checkout/pending",
        "cancel_url" => BASE_URL . "/$cancel_url",
    ]);

    $chargeObj->save();

    return $chargeObj->hosted_url;
}

function create_payment_payop($invoice_data)
{
    $paymentDetails = [
        'publicKey' => PAYOP_PUBLIC_KEY,
        'order' => [
            'id' => $invoice_data['order_id'],
            'amount' => util_format_price_input($invoice_data['total_price']),
            'currency' => $invoice_data['currency'],
            'items' => [$invoice_data['description']]
        ],
        'payer' => [
            'email' => CLIENT_DATA['email'],
        ],
        'language' => 'en',
        'resultUrl' => BASE_URL . '/checkout/' . $invoice_data['uuid'] . '/process?m=payop&t={{txid}}',
        'failPath' => BASE_URL . '/checkout/' . $invoice_data['uuid'],
    ];

    $order = [
        'id' => $invoice_data['order_id'],
        'amount' => util_format_price_input($invoice_data['total_price']),
        'currency' => $invoice_data['currency']
    ];
    ksort($order, SORT_STRING);
    $dataSet = array_values($order);
    $dataSet[] = PAYOP_SECRET_KEY;
    $paymentDetails['signature'] = hash('sha256', implode(':', $dataSet));

    $jsonData = json_encode($paymentDetails);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://payop.com/v1/invoices/create',
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_RETURNTRANSFER => true,
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    $data = json_decode($response, true);

    return 'https://checkout.payop.com/en/payment/invoice-preprocessing/' . $data['data'];
}

function get_payment_url($invoice_data, $payment_method)
{
    switch ($payment_method) {
        case 'stripe':
            return create_payment_stripe($invoice_data);
            break;
        case 'stripe_paypal':
            return create_payment_stripe_paypal($invoice_data);
            break;
        case 'coinbase':
            return create_payment_coinbase($invoice_data);
            break;
        case 'payop':
            return create_payment_payop($invoice_data);
            break;
    }
}

function get_stripe_tx($tx_token)
{
    \Stripe\Stripe::setApiKey(STRIPE_API_KEY);

    try {
        $checkout_session = \Stripe\Checkout\Session::retrieve([
            'id' => $tx_token,
            'expand' => ['payment_intent.latest_charge'],
        ]);
    } catch (\Throwable $e) {
        error_log('Stripe Checkout lookup failed: ' . $e->getMessage());
        return false;
    }

    if (!$checkout_session || ($checkout_session->payment_status ?? '') !== 'paid') {
        return false;
    }

    $intent = $checkout_session->payment_intent ?? null;
    $charge = is_object($intent) ? ($intent->latest_charge ?? null) : null;
    $payment_type = 'stripe';
    $created = (int)($checkout_session->created ?? time());

    if (is_object($charge)) {
        $payment_type = (string)($charge->payment_method_details->type ?? 'stripe');
        $created = (int)($charge->created ?? $created);
    }

    return [
        'processor' => 'stripe',
        'amount' => (int)($checkout_session->amount_total ?? 0),
        'currency' => strtoupper((string)($checkout_session->currency ?? '')),
        'status' => 'succeeded',
        'token' => (string)$checkout_session->id,
        'payment_method' => $payment_type,
        'created_at' => date('Y-m-d H:i:s', $created),
    ];
}

/**
 * Fulfil a paid Stripe Checkout Session exactly once.
 * Used by both the Stripe webhook and the browser success URL fallback.
 */
function lb_fulfill_stripe_checkout_session($checkout_session)
{
    global $db;

    if (is_string($checkout_session)) {
        \Stripe\Stripe::setApiKey(STRIPE_API_KEY);
        $checkout_session = \Stripe\Checkout\Session::retrieve([
            'id' => $checkout_session,
            'expand' => ['payment_intent.latest_charge'],
        ]);
    }

    if (!$checkout_session || empty($checkout_session->id)) {
        throw new \RuntimeException('Invalid Stripe Checkout Session.');
    }

    if (($checkout_session->payment_status ?? '') !== 'paid') {
        return ['ok' => true, 'processed' => false, 'reason' => 'not_paid'];
    }

    $metadata = $checkout_session->metadata ?? null;
    $invoice_id = (int)($metadata->invoice_id ?? $checkout_session->client_reference_id ?? 0);
    if ($invoice_id <= 0) {
        throw new \RuntimeException('Stripe Session has no invoice_id metadata.');
    }

    $lock_name = 'stripe_invoice_' . $invoice_id;
    $lock = $db->row('SELECT GET_LOCK(?, 10) AS acquired', $lock_name);
    if ((int)($lock['acquired'] ?? 0) !== 1) {
        throw new \RuntimeException('Could not acquire Stripe invoice lock.');
    }

    try {
        $invoice = db_get_row('invoices', ['id' => $invoice_id]);
        if (!$invoice) {
            throw new \RuntimeException('Invoice not found.');
        }

        if (($invoice['status'] ?? '') === 'PAID') {
            return ['ok' => true, 'processed' => false, 'reason' => 'already_paid'];
        }

        $amount = (int)($checkout_session->amount_total ?? 0);
        $currency = strtoupper((string)($checkout_session->currency ?? ''));
        if ($amount < (int)$invoice['total_price']) {
            throw new \RuntimeException('Stripe amount is lower than invoice amount.');
        }
        if ($currency !== strtoupper((string)$invoice['currency'])) {
            throw new \RuntimeException('Stripe currency does not match invoice currency.');
        }

        $existing_tx = db_get_row('transactions', ['token' => (string)$checkout_session->id]);
        if (!$existing_tx) {
            $intent = $checkout_session->payment_intent ?? null;
            $charge = is_object($intent) ? ($intent->latest_charge ?? null) : null;
            $payment_type = is_object($charge)
                ? (string)($charge->payment_method_details->type ?? 'stripe')
                : 'stripe';

            db_add_row('transactions', [
                'client_id' => $invoice['client_id'],
                'invoice_id' => $invoice['id'],
                'order_id' => $invoice['order_id'],
                'order_type' => $invoice['order_type'],
                'processor' => 'stripe',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'succeeded',
                'token' => (string)$checkout_session->id,
                'payment_method' => $payment_type,
                'created_at' => date('Y-m-d H:i:s', (int)($checkout_session->created ?? time())),
            ]);
        }

        $notifications = process_order($invoice);
        if ($notifications === false) {
            throw new \RuntimeException('Order processing failed.');
        }

        db_update_row('invoices', ['id' => $invoice['id']], [
            'status' => 'PAID',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        foreach ($notifications as $notification) {
            db_add_row('notifications', $notification);
        }

        if (($invoice['order_type'] ?? '') === 'order'
            && !empty($invoice['order_id'])
            && function_exists('lb_realtime_emit_new_order')) {
            lb_realtime_emit_new_order((int)$invoice['order_id']);
        }

        if (function_exists('trigger_notification_sender_async')) {
            trigger_notification_sender_async();
        }

        return ['ok' => true, 'processed' => true, 'invoice_id' => $invoice_id];
    } finally {
        $db->row('SELECT RELEASE_LOCK(?) AS released', $lock_name);
    }
}

function get_payop_tx($tx_token)
{
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . PAYOP_JWT_TOKEN
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://payop.com/v2/transactions/' . $tx_token);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    curl_close($ch);

    $data = json_decode($response, true);

    if (!isset($data['message'])) {
        switch ($data['data']['state']) {
            case 2:
                $status = 'succeeded';
                break;
            case 3:
                $status = 'failed';
                break;
            case 4:
                $status = 'pending';
                break;
            case 5:
                $status = 'failed';
                break;
        }

        $tx_data = [
            'processor' => 'payop',
            'amount' => $data['data']['amount'],
            'currency' => strtoupper($data['data']['currency']),
            'status' => $status,
            'token' => $tx_token,
            'payment_method' => $data['data']['cardMetadata']['paymentSystem'] ?? 'Unknown',
            'created_at' => date('Y-m-d H:i:s', $data['data']['createdAt']),
        ];

        return $tx_data;
    }
    return false;
}


function get_tx_data($tx_method, $tx_token)
{
    switch ($tx_method) {
        case 'stripe':
            $tx_data = get_stripe_tx($tx_token);
            break;
        case 'payop':
            $tx_data = get_payop_tx($tx_token);
            break;
    }
    return $tx_data;
}

function tx_status_to_invoice($status)
{
    switch ($status) {
        case 'pending':
            return "PENDING";
            break;
        case 'succeeded':
            return "PAID";
            break;
        case 'failed':
            return "UNPAID";
            break;
    }
}


if (!function_exists('lb_build_new_order_ping_notification')) {
    // Single source of truth for the "order_ping" (Discord "New Order" webhook) notification
    // row. A brand-new paid order and an admin/system repost of an existing order need to
    // produce the byte-identical payload — notification_discord_body_load() resolves game
    // routing, role ping, embed fields and the claim link purely from order_id at send time,
    // so every caller of this builder gets exactly the same Discord message either way.
    function lb_build_new_order_ping_notification($orderId, bool $directWebhook = false): array
    {
        return [
            'type' => 'order_ping',
            'data' => json_encode([
                'order_id' => base64_encode((string)$orderId),
                // Admin reposts use the boost-panel webhook itself. Normal new
                // orders keep their existing game/thread routing.
                'direct_webhook' => $directWebhook ? 1 : 0,
            ]),
            'is_discord' => true,
        ];
    }
}

function process_order($invoice)
{
    $notification = [];
    $notif_invoice = [];
    // base64_encode values in notif_invoice
    foreach ($invoice as $key => $value) {
        $notif_invoice[$key] = base64_encode($value ?? ' ');
    }
    $notification[] = [
        'type' => 'invoice_paid',
        'data' => json_encode($notif_invoice),
        'recipient' => 'client',
        'recipient_id' => $invoice['client_id'],
        'is_web' => 1,
        'is_email' => 1,
    ];
    switch ($invoice['order_type']) {
        case 'order':
            $order = db_get_row('orders', ['id' => $invoice['order_id']]);
            if ($order != false) {
                if (isset($order['booster_id'])) {
                    $requestedIds = [(int)$order['booster_id']];
                    if (in_array((int)($order['form_id'] ?? 0), [4, 19, 29], true)) {
                        $requestOptions = db_get_row('order_options', ['order_id' => (int)$order['id']], 1);
                        $rawRequested = (string)($requestOptions['selected_boosters'] ?? '');
                        $requestedIds = array_values(array_unique(array_filter(array_map('intval', preg_split('/[\s,|]+/', $rawRequested, -1, PREG_SPLIT_NO_EMPTY)))));
                        if (empty($requestedIds)) {
                            $requestedIds = [(int)$order['booster_id']];
                        }
                    }
                    $requestedBoosterIds = [];
                    foreach ($requestedIds as $requestedId) {
                        $booster = db_get_row('boosters', ['id' => $requestedId]);
                        if (!$booster) continue;
                        $requestedBoosterIds[] = (int)$requestedId;
                        $notification[] = [
                            'type' => 'booster_ready_request',
                            'recipient' => 'booster',
                            'recipient_id' => $requestedId,
                            'data' => json_encode([
                                'order_id' => base64_encode($order['id'] ?? ' '),
                                'booster_id' => base64_encode((string)$requestedId),
                                'booster_discord_id' => base64_encode($booster['discord_id'] ?? ' '),
                                'client_username' => base64_encode((string)($invoice['username'] ?? 'Client')),
                            ]),
                            'is_web' => 1,
                            'is_discord' => false,
                            'is_sent' => 1,
                        ];
                    }
                    if (!empty($requestedBoosterIds)) {
                        $firstRequestedId = (int)$requestedBoosterIds[0];
                        $notification[] = [
                            'type' => 'booster_ready_request',
                            'recipient' => 'booster',
                            'recipient_id' => $firstRequestedId,
                            'data' => json_encode([
                                'order_id' => base64_encode((string)($order['id'] ?? ' ')),
                                'booster_id' => base64_encode((string)$firstRequestedId),
                                'booster_ids' => base64_encode(implode(',', $requestedBoosterIds)),
                                'client_username' => base64_encode((string)($invoice['username'] ?? 'Client')),
                            ]),
                            'is_web' => 0,
                            'is_discord' => true,
                        ];
                    }
                    $cut = ((int)($order['form_id'] ?? 0) === 26) ? '50' : '55';
                    db_update_row('orders', ['id' => $order['id']], [
                        'status' => 'IN_PROGRESS',
                        'paid_at' => date('Y-m-d H:i:s'),
                        'claimed_at' => null,
                        'booster_cut' => $cut
                    ]);

                    // Push the pending-request modal to the requested booster right away.
                    // Without this the modal only appeared on the next page load / slow
                    // AJAX fallback, although the header notification was already there.
                    if (function_exists('lb_realtime_emit')) {
                        lb_realtime_emit('booster_request', ['order_id' => (int)$order['id']]);
                        lb_realtime_emit('orders_panel_update', ['order_id' => (int)$order['id']]);
                    }
                } else {
                    $notification[] = lb_build_new_order_ping_notification($order['id'] ?? 0);
                    db_update_row('orders', ['id' => $order['id']], ['status' => 'PAID', 'paid_at' => date('Y-m-d H:i:s')]);
                }

                // Lock in the REAL start rank the moment the order starts, not the rank the
                // customer picked in the form (they may have demoted in between).
                lb_order_capture_riot_start_rank((int) $order['id']);

                process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $order['id']);
                process_cashback_points(
                    $invoice['client_id'],
                    $invoice['total_price'] != 0 ?
                    $invoice['total_price'] / 100 :
                    $invoice['coins_used'],
                    $order['id'],
                    0.00,
                );
                process_client_loyalty($invoice['client_id']);

                $order_id = $invoice['order_id'];
                $chat_data = [
                    'message' => 'Thank you for your purchase at lolboost.gg! 🎉

                    Here you can chat with your booster. 😊
                    If you have any questions, our live chat is available anytime! 💬

                    📌 Please note: You\'ll be notified as soon as your booster accepts the Order!',
                    'message_type' => 'text',
                ];

                $user = [
                    'id' => '999',
                    'username' => 'System',
                    'icon' => 'https://lolboost.gg/public/uploads/icons/default1.png'
                ];

                chat_insert_message($order_id, $chat_data, $user, 'system');

                // Giveaway tickets (1 ticket per PAID invoice)
                if (function_exists('giveaway_grant_invoice_ticket')) {
                    giveaway_grant_invoice_ticket((int)($invoice['id'] ?? 0));
                }
                return $notification;
            }
            break;

        case 'egirl_session':
            $order = db_get_row('egirl_orders', ['id' => $invoice['order_id']]);
            if ($order != false) {
                db_update_row('egirl_orders', ['id' => $order['id']], [
                    'status' => 'PAID',
                    'paid_at' => date('Y-m-d H:i:s')
                ]);

                process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $order['id']);
                process_cashback_points(
                    $invoice['client_id'],
                    $invoice['total_price'] != 0 ?
                    $invoice['total_price'] / 100 :
                    $invoice['coins_used'],
                    $order['id'],
                    0.00
                );
                process_client_loyalty($invoice['client_id']);

                $notification[] = [
                    'type' => 'egirl_booking_paid',
                    'recipient' => 'booster',
                    'recipient_id' => (int)($order['egirl_id'] ?? 0),
                    'data' => json_encode([
                        'order_id' => base64_encode((string)($order['id'] ?? '')),
                        'egirl_id' => base64_encode((string)($order['egirl_id'] ?? '')),
                        'client_id' => base64_encode((string)($invoice['client_id'] ?? '')),
                        'service_title' => base64_encode((string)($order['service_title'] ?? 'E-Girl Session')),
                        'service_type' => base64_encode((string)($order['service_type'] ?? '')),
                        'unit_value' => base64_encode((string)($order['unit_value'] ?? '')),
                        'unit_type' => base64_encode((string)($order['unit_type'] ?? '')),
                        'client_notes' => base64_encode((string)($order['client_notes'] ?? '')),
                    ]),
                    'is_web' => 1,
                    'is_discord' => true,
                ];

                if (function_exists('chat_insert_message')) {
                    $chat_data = [
                        'message' => 'Thank you for your booking at lolboost.gg! 🎉

                        Here you can chat with your E-Girl. 😊
                        You will be notified as soon as your session starts. 💬',
                        'message_type' => 'text',
                    ];

                    $user = [
                        'id' => '999',
                        'username' => 'System',
                        'icon' => 'https://lolboost.gg/public/uploads/icons/default1.png'
                    ];

                    chat_insert_message('eg_' . $order['id'], $chat_data, $user, 'system');
                }

                if (function_exists('giveaway_grant_invoice_ticket')) {
                    giveaway_grant_invoice_ticket((int)($invoice['id'] ?? 0));
                }

                return $notification;
            }
            break;

        case 'account':
            // assign a random account based on package_id from invoice to client
            $account = db_get_row('accounts', ['package_id' => $invoice['order_id'], 'status' => 0, 'order' => 'RAND()']);
            if ($account != false) {
                db_update_row('accounts', ['id' => $account['id']], ['status' => 1, 'client_id' => $invoice['client_id'], 'sold_at' => date('Y-m-d H:i:s')]);

                // Auto-payout seller #28 for accounts sold via admin #51 (70% of package price, fee: 30%)
                if ((int)($account['admin_id'] ?? 0) === 51 && function_exists('seller28_pay_admin_account')) {
                    seller28_pay_admin_account((int)$account['id']);
                }

                // Giveaway tickets (1 ticket per PAID invoice)
                if (function_exists('giveaway_grant_invoice_ticket')) {
                    giveaway_grant_invoice_ticket((int)($invoice['id'] ?? 0));
                }

                $notification[] = [
                    'type' => 'account_ping',
                    'data' => json_encode([
                        'account_id' => base64_encode($account['id'] ?? ' '),
                        'package_id' => base64_encode($account['package_id'] ?? ' '),
                    ]),
                    'is_discord' => true,
                ];

                $remainingStock = db_get_row_count('accounts', [
                    'package_id' => $account['package_id'],
                    'status' => 0
                ]);

                if ((int)$remainingStock === 1) {
                    $notification[] = [
                        'type' => 'account_low_stock',
                        'data' => json_encode([
                            'package_id' => base64_encode($account['package_id'] ?? ' '),
                            'remaining_stock' => base64_encode($remainingStock),
                        ]),
                        'is_discord' => true,
                    ];
                }

                // =============================================
                // DISCORD WEBHOOK FOR PACKAGE ACCOUNT SALES
                // =============================================
                try {
                    global $db;

                    $package = $db->row(
                        "SELECT id, name, price FROM account_packages WHERE id = ? LIMIT 1",
                        (int)($account['package_id'] ?? 0)
                    );

                    $client = $db->row(
                        "SELECT id, username FROM clients WHERE id = ? LIMIT 1",
                        (int)($invoice['client_id'] ?? 0)
                    );

                    $uploader_admin = null;
                    if (!empty($account['admin_id'])) {
                        $uploader_admin = $db->row(
                            "SELECT id, username FROM admins WHERE id = ? LIMIT 1",
                            (int)$account['admin_id']
                        );
                    }

                    $sale_price_cents = ((int)($package['price'] ?? 0) > 0)
                        ? (int)$package['price']
                        : (int)($invoice['total_price'] ?? 0);

                    $server_label = strtoupper((string)($account['server'] ?? 'EUW'));
                    $package_name = trim((string)($package['name'] ?? ('Package #' . (int)($account['package_id'] ?? 0))));
                    $admin_package_url = BASE_URL . '/admin-area/account-package/' . (int)($account['package_id'] ?? 0);

                    $webhookData = [
                        "username" => "Account Notif",
                        "content"  => "",
                        "embeds"   => [[
                            "title"       => "💸 Account Sold",
                            "description" => "**" . $package_name . "**",
                            "color"       => 0x5865F2,
                            "fields"      => [
                                ["name" => "🆔 Account ID", "value" => "#" . (int)$account['id'], "inline" => true],
                                ["name" => "🌐 Server", "value" => $server_label, "inline" => true],
                                ["name" => "💰 Price", "value" => "€" . number_format($sale_price_cents / 100, 2), "inline" => true],
                                ["name" => "👤 Customer", "value" => !empty($client) ? ("#" . (int)$client['id'] . " – " . ($client['username'] ?? 'Guest')) : ("#" . (int)($invoice['client_id'] ?? 0)), "inline" => true],
                                ["name" => "🛡️ Uploader", "value" => !empty($uploader_admin) ? ("#" . (int)$uploader_admin['id'] . " – " . ($uploader_admin['username'] ?? 'Admin')) : "—", "inline" => true],
                                ["name" => "🔗 Admin Link", "value" => "[Open Package](" . $admin_package_url . ")", "inline" => true],
                            ],
                            "timestamp" => date("c")
                        ]]
                    ];

                    lb_send_sold_notification('account', $webhookData);
                } catch (\Throwable $e) {
                    // Never block sale processing because of webhook errors.
                }

                // =============================================
                // DISCORD DM TO UPLOADING ADMIN on account sale
                // =============================================
                if (!empty($account['admin_id']) && defined('DS_BOT_TOKEN') && DS_BOT_TOKEN) {
                    try {
                        global $db;

                        $dm_admin = $db->row(
                            "SELECT id, username, discord_user_id FROM admins WHERE id = ? LIMIT 1",
                            (int)$account['admin_id']
                        );

                        if (!empty($dm_admin['discord_user_id'])) {
                            $package = $db->row(
                                "SELECT id, name, price, game_id FROM account_packages WHERE id = ? LIMIT 1",
                                (int)($account['package_id'] ?? 0)
                            );

                            $client = $db->row(
                                "SELECT id, username, email FROM clients WHERE id = ? LIMIT 1",
                                (int)($invoice['client_id'] ?? 0)
                            );

                            $sale_price_cents = ((int)($package['price'] ?? 0) > 0)
                                ? (int)$package['price']
                                : (int)($invoice['total_price'] ?? 0);

                            $sale_price_eur = number_format($sale_price_cents / 100, 2);
                            $admin_share_percent = ((int)($account['admin_id'] ?? 0) === 51) ? 70 : 100;
                            $earnings_cents = (int) round($sale_price_cents * ($admin_share_percent / 100));
                            $earnings_eur = number_format($earnings_cents / 100, 2);
                            $server_label   = strtoupper((string)($account['server'] ?? 'EUW'));
                            $package_name   = trim((string)($package['name'] ?? 'Unknown package'));
                            $login_value    = trim((string)($account['login'] ?? '—'));
                            $data_value     = trim((string)($account['data'] ?? ''));
                            $created_at     = !empty($account['created_at']) ? util_format_date_display_hm($account['created_at']) : '—';
                            $sold_at        = date('M d, Y H:i');
                            $buyer_value    = !empty($client)
                                ? ('#' . (int)$client['id'] . ' – ' . ($client['username'] ?? 'Unknown') . (!empty($client['email']) ? ('
' . $client['email']) : ''))
                                : ('#' . (int)($invoice['client_id'] ?? 0));
                            $admin_url      = BASE_URL . '/admin-area/account-package/' . (int)($account['package_id'] ?? 0);
                            $preview_data   = $data_value !== '' ? mb_substr($data_value, 0, 400) : '—';

                            $dmChannel = getDMChannelId(DS_BOT_TOKEN, trim((string)$dm_admin['discord_user_id']));
                            if ($dmChannel) {
                                $dm_embed = [
                                    'embeds' => [[
                                        'title'       => '🎉 Account Sold!',
                                        'description' => $server_label . ' | ' . $package_name,
                                        'color'       => 0x22c55e,
                                        'fields'      => [
                                            ['name' => '💰 Sale Price',      'value' => $sale_price_eur . ' EUR',                   'inline' => true],
                                            ['name' => '🏦 Your Earnings',   'value' => $earnings_eur . ' EUR',                     'inline' => true],
                                            ['name' => '🌐 Server',          'value' => $server_label,                              'inline' => true],
                                            ['name' => '🔗 View Account',    'value' => '[Open Package](' . $admin_url . ')',       'inline' => false],
                                        ],
                                        'footer' => [
                                            'text' => 'LoLBoost.GG Admin Portal'
                                        ],
                                        'timestamp' => date('c')
                                    ]]
                                ];

                                sendEmbedDM(
                                    DS_BOT_TOKEN,
                                    $dmChannel,
                                    json_encode($dm_embed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                                );
                            }
                        }
                    } catch (\Throwable $e) {
                        // Never block sale processing because of DM errors.
                    }
                }

                // Premium account chat runs against support, not a seller — give the buyer
                // the same welcome message the marketplace account chat gets.
                lb_seed_chat_system_message(
                    SYS_PATH . '/public/uploads/private/chat/accounts_' . sha1('account_' . (int)$account['id']) . '.json',
                    'account_' . (int)$account['id'],
                    'Thank you for your purchase at lolboost.gg! 🎉

Here you can chat with our support team. 😊
If you have any questions, our live chat is available anytime! 💬

📌 Please note: Your account details are shown on this page — our team will assist you here if anything is missing!'
                );

                process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $invoice['order_id']);
                process_cashback_points($invoice['client_id'], $invoice['total_price'] != 0 ? $invoice['total_price'] / 100 : $invoice['coins_used'], $invoice['order_id'], 0.00);
                process_client_loyalty($invoice['client_id']);

                return $notification;
            }
            break;

        case 'lol_account':
            $account = db_get_row('selling_accounts', ['id' => $invoice['order_id']]);

            if ($account != false) {
                db_update_row('selling_accounts', ['id' => $invoice['order_id']], [
                    'sold' => 1,
                    'client_id' => $invoice['client_id'],
                    // Without this the sale timestamp stayed NULL and the admin
                    // "Sold at" column rendered as "-" for freshly paid accounts.
                    'sold_at' => !empty($account['sold_at']) ? $account['sold_at'] : date('Y-m-d H:i:s'),
                ]);
                if (!empty($account['seller_id']) && function_exists('sync_seller_stats')) {
                    sync_seller_stats((int)$account['seller_id']);
                }

                // =============================================
                // SELLER PAYOUT on account sale
                // =============================================
                if (!empty($account['seller_id']) && empty($account['seller_paid'])) {
                    global $db;
                    $seller = $db->row("SELECT * FROM sellers WHERE id = ? LIMIT 1", (int)$account['seller_id']);
                    if (!empty($seller) && empty($seller['is_banned'])) {
                        $effective_fee = ($seller['fee_percent'] !== null && $seller['fee_percent'] !== '')
                            ? (float)$seller['fee_percent']
                            : 15.0;

                        // FIX: Immer den vollen Account-Preis verwenden, unabhängig von LB Coins
                        $sale_price    = (int)(((int)$account['price'] > 0) ? $account['price'] : $invoice['total_price']);
                        $platform_fee  = (int)round($sale_price * $effective_fee / 100);
                        $seller_cut    = $sale_price - $platform_fee;
                        $old_balance   = (int)$seller['balance'];
                        $new_balance   = $old_balance + $seller_cut;

                        $db->run("UPDATE sellers SET balance = ? WHERE id = ?", $new_balance, (int)$seller['id']);
                        db_add_row('seller_payments', [
                            'seller_id'    => (int)$seller['id'],
                            'account_id'   => (int)$account['id'],
                            'type'         => 'sale_payout',
                            'amount_cents' => $seller_cut,
                            'note'         => "Account #{$account['id']} sold – fee: {$effective_fee}%",
                            'balance_after' => $new_balance,
                        ]);
                        db_update_row('selling_accounts', ['id' => (int)$account['id']], ['seller_paid' => 1]);
                        process_seller_rank((int)$seller['id']);
                    }
                }
                // =============================================

                $client_id  = $invoice['client_id'];
                $client     = db_get_row('clients', ['id' => $client_id]);

                // FIX: Vollen Account-Preis für Webhook-Anzeige verwenden
                $sale_price = ((int)$account['price'] > 0) ? (int)$account['price'] : (int)$invoice['total_price'];

                // Load seller info for webhook
                $wh_seller = null;
                if (!empty($account['seller_id'])) {
                    global $db;
                    $wh_seller = $db->row("SELECT id, username FROM sellers WHERE id = ? LIMIT 1", (int)$account['seller_id']);
                }
                $admin_account_url = 'https://lolboost.gg/admin-area/selling-account/' . (int)$account['id'];

                $data = [
                    "username" => "Account Notif",
                    "content"  => "",
                    "embeds"   => [[
                        "title"       => "💸 New Account Sold",
                        "description" => "**" . ($account['title'] ?? 'Untitled') . "**",
                        "color"       => 0x4ade80,
                        "fields"      => [
                            ["name" => "🆔 Account ID",  "value" => "#" . (int)$account['id'],                                                    "inline" => true],
                            ["name" => "🌐 Server",      "value" => strtoupper($account['server'] ?? '?'),                                       "inline" => true],
                            ["name" => "💰 Price",       "value" => "€" . number_format($sale_price / 100, 2),                          "inline" => true],
                            ["name" => "👤 Customer",    "value" => "#" . (int)$invoice['client_id'] . " – " . ($client['username'] ?? 'Guest'), "inline" => true],
                            ["name" => "🏪 Seller",      "value" => !empty($wh_seller) ? "#" . (int)$wh_seller['id'] . " – " . $wh_seller['username'] : "—", "inline" => true],
                            ["name" => "🔗 Admin Link",  "value" => "[View Account](" . $admin_account_url . ")",                                  "inline" => true],
                        ],
                        "timestamp" => date("c")
                    ]]
                ];

                lb_send_sold_notification('account', $data);

                // =============================================
                // DISCORD DM TO SELLER on account sale
                // =============================================
                if (!empty($account['seller_id'])) {
                    try {
                        global $db;
                        $dm_seller = $db->row("SELECT discord_id, fee_percent FROM sellers WHERE id = ? LIMIT 1", (int)$account['seller_id']);
                        if (!empty($dm_seller['discord_id']) && defined('DS_BOT_TOKEN') && DS_BOT_TOKEN) {
                            // FIX: Vollen Account-Preis für DM-Anzeige verwenden
                            $sale_price_cents = ((int)$account['price'] > 0) ? (int)$account['price'] : (int)$invoice['total_price'];
                            $price_eur      = number_format($sale_price_cents / 100, 2);
                            $effective_fee  = !empty($dm_seller['fee_percent']) ? (float)$dm_seller['fee_percent'] : 15.0;
                            $seller_cut_eur = number_format(($sale_price_cents / 100) * (1 - $effective_fee / 100), 2);
                            $rank_labels    = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
                            $rank_label     = $rank_labels[(int)($account['current_rank'] ?? 0)] ?? 'Unranked';
                            $account_url    = BASE_URL . '/seller-area/account/' . (int)$account['id'];
                            $dm_embed = [
                                'embeds' => [[
                                    'title'       => "🎉 Account Sold!",
                                    'description' => '**' . addslashes($account['title'] ?? '') . '**',
                                    'color'       => 0x4ade80,
                                    'fields'      => [
                                        ['name' => "💰 Sale Price",    'value' => "€" . $price_eur,      'inline' => true],
                                        ['name' => "🏦 Your Earnings", 'value' => "€" . $seller_cut_eur, 'inline' => true],
                                        ['name' => "🏆 Rank",          'value' => $rank_label,                      'inline' => true],
                                        ['name' => "🌐 Server",        'value' => strtoupper($account['server'] ?? '-'), 'inline' => true],
                                        ['name' => "🔗 View Account",  'value' => '[Open in Seller Area](' . $account_url . ')', 'inline' => false],
                                    ],
                                    
                                    'timestamp' => date('c'),
                                ]]
                            ];
                            // Step 1: open DM channel
                            $ch_dm = curl_init('https://discord.com/api/v10/users/@me/channels');
                            curl_setopt_array($ch_dm, [
                                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bot ' . DS_BOT_TOKEN],
                                CURLOPT_POST           => 1,
                                CURLOPT_POSTFIELDS     => json_encode(['recipient_id' => $dm_seller['discord_id']]),
                                CURLOPT_RETURNTRANSFER => 1,
                                CURLOPT_TIMEOUT        => 5,
                            ]);
                            $dm_resp    = curl_exec($ch_dm);
                            curl_close($ch_dm);
                            $dm_channel = json_decode($dm_resp, true);
                            if (!empty($dm_channel['id'])) {
                                // Step 2: send embed message
                                $ch_msg = curl_init('https://discord.com/api/v10/channels/' . $dm_channel['id'] . '/messages');
                                curl_setopt_array($ch_msg, [
                                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bot ' . DS_BOT_TOKEN],
                                    CURLOPT_POST           => 1,
                                    CURLOPT_POSTFIELDS     => json_encode($dm_embed),
                                    CURLOPT_RETURNTRANSFER => 1,
                                    CURLOPT_TIMEOUT        => 5,
                                ]);
                                curl_exec($ch_msg);
                                curl_close($ch_msg);
                            }
                        }
                    } catch (\Throwable $dm_e) {
                        // DM failure must never block the sale flow
                    }
                }
                // =============================================

                $notification[] = [
                    'type' => 'account_sold',
                    'data' => json_encode([
                        'account_id' => base64_encode($account['id'] ?? ''),
                        'login' => base64_encode($account['login'] ?? ''),
                        'password' => base64_encode($account['password'] ?? ''),
                        'data' => base64_encode($account['data'] ?? ''),
                        'lol_account' => base64_encode(true),
                        'email' => base64_encode($account['email'] ?? ''),
                        'email_password' => base64_encode($account['email_password'] ?? ''),
                        'in_game_name' => base64_encode($account['in_game_name'] ?? ''),
                        'delivery_instructions' => base64_encode($account['delivery_instructions'] ?? '')
                    ]),
                    'recipient' => 'client',
                    'recipient_id' => $invoice['client_id'],
                    'is_email' => true,
                ];

                // ── Seller notification email ──────────────────────────────
                if (!empty($account['seller_id'])) {
                    $seller_data = db_get_row('sellers', ['id' => (int)$account['seller_id']]);
                    $account_url = BASE_URL . '/seller-area/account/' . (int)$account['id'];

                    $effective_fee = !empty($seller_data['fee_percent'])
                        ? (float)$seller_data['fee_percent']
                        : 15.0;

                    $sale_price_cents = ((int)$account['price'] > 0)
                        ? (int)$account['price']
                        : (int)$invoice['total_price'];

                    $seller_cut_cents = (int) round($sale_price_cents * (1 - ($effective_fee / 100)));

                    $rank_labels = [
                        'Unranked', 'Iron', 'Bronze', 'Silver', 'Gold',
                        'Platinum', 'Emerald', 'Diamond', 'Master',
                        'Grandmaster', 'Challenger'
                    ];

                    $notification[] = [
                        'type' => 'seller_account_sold',
                        'recipient' => 'seller',
                        'recipient_id' => (int)$account['seller_id'],
                        'is_email' => true,
                        'is_web' => false,
                        'is_discord' => false,
                        'data' => json_encode([
                            'username' => base64_encode((string)($seller_data['username'] ?? 'there')),
                            'account_title' => base64_encode((string)($account['title'] ?? 'Account')),
                            'account_url' => base64_encode($account_url),
                            'price' => base64_encode(number_format($sale_price_cents / 100, 2, '.', '')),
                            'earnings' => base64_encode(number_format($seller_cut_cents / 100, 2, '.', '')),
                            'fee_percent' => base64_encode(number_format($effective_fee, 2, '.', '')),
                            'balance_after' => base64_encode(number_format(((int)($new_balance ?? ($seller_data['balance'] ?? 0))) / 100, 2, '.', '')),
                            'buyer' => base64_encode((string)($client['username'] ?? 'Guest')),
                            'server' => base64_encode((string)strtoupper($account['server'] ?? '-')),
                            'rank' => base64_encode((string)($rank_labels[(int)($account['current_rank'] ?? 0)] ?? 'Unranked')),
                        ]),
                    ];
                }

                process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $invoice['order_id']);
                process_cashback_points($invoice['client_id'], $invoice['total_price'] != 0 ? $invoice['total_price'] / 100 : $invoice['coins_used'], $invoice['order_id'], 0.00);
                process_client_loyalty($invoice['client_id']);

                // Post an initial system message into the sold account chat
                $selling_chat_key  = 'selling_account_' . (int)$account['id'];
                $selling_chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1($selling_chat_key) . '.json';
                $selling_chat_message = 'Thank you for your purchase at lolboost.gg! 🎉

Here you can chat with the seller. 😊
If you have any questions, our live chat is available anytime! 💬

📌 Please note: The seller has already been notified about your purchase and will assist you here if needed!';

                try {
                    $msg_content = esc($selling_chat_message);
                    if ($msg_content !== '') {
                        if (!is_dir(dirname($selling_chat_path))) {
                            @mkdir(dirname($selling_chat_path), 0777, true);
                        }

                        $fp = @fopen($selling_chat_path, 'c+');
                        if ($fp && @flock($fp, LOCK_EX)) {
                            rewind($fp);
                            $raw = stream_get_contents($fp);
                            $chat_json = json_decode($raw ?: '', true);
                            if (!is_array($chat_json)) {
                                $chat_json = ['order_id' => $selling_chat_key, 'messages' => []];
                            }
                            if (!isset($chat_json['messages']) || !is_array($chat_json['messages'])) {
                                $chat_json['messages'] = [];
                            }

                            $already_exists = false;
                            foreach ($chat_json['messages'] as $existing_message) {
                                if ((($existing_message['sender'] ?? '') === 'system') && (($existing_message['raw'] ?? '') === $msg_content)) {
                                    $already_exists = true;
                                    break;
                                }
                            }

                            if (!$already_exists) {
                                $now = time();
                                $chat_json['messages'][] = [
                                    'sender' => 'system',
                                    'sender_id' => 999,
                                    'sender_name' => 'System',
                                    'sender_icon' => 'https://lolboost.gg/public/uploads/icons/default1.png',
                                    'content' => make_links_clickable($msg_content),
                                    'raw' => $msg_content,
                                    'edited' => 0,
                                    'type' => 'text',
                                    'seen' => 0,
                                    'seen_at' => 0,
                                    'notify' => 0,
                                    'time' => $now,
                                    'uuid' => $now . '-' . bin2hex(random_bytes(4)),
                                ];

                                rewind($fp);
                                ftruncate($fp, 0);
                                fwrite($fp, json_encode($chat_json, JSON_PRETTY_PRINT));
                                fflush($fp);
                            }

                            @flock($fp, LOCK_UN);
                            fclose($fp);
                        } elseif ($fp) {
                            fclose($fp);
                        }
                    }
                } catch (\Throwable $chat_e) {
                    // Chat failure must never block the sale flow
                }

                return $notification;
            }

            break;

        case 'selling_item':
            $si_item = db_get_row('selling_items', ['id' => $invoice['order_id']]);
            if ($si_item) {
                global $db;

                $si_seller = null;
                $si_sale_price = ((int)($si_item['price'] ?? 0) > 0)
                    ? (int)$si_item['price']
                    : (int)($invoice['price_eur'] ?? $invoice['total_price'] ?? 0);

                $si_game_id    = (int)($si_item['game_id'] ?? 0);
                $si_game_slug  = trim((string)($si_item['game'] ?? ''));
                $si_game_label = '';
                try {
                    if ($si_game_id > 0) {
                        $si_game_row = db_get_row('games', ['id' => $si_game_id], 1);
                        if (!empty($si_game_row)) {
                            $si_game_label = trim((string)($si_game_row['name'] ?? ''));
                            if ($si_game_slug === '') {
                                $si_game_slug = trim((string)($si_game_row['slug'] ?? ''));
                            }
                        }
                    }
                    if ($si_game_label === '' && $si_game_slug !== '' && function_exists('util_get_game_by_slug')) {
                        $si_game_row = util_get_game_by_slug($si_game_slug);
                        if (is_array($si_game_row)) {
                            $si_game_label = trim((string)($si_game_row['name'] ?? ''));
                        }
                    }
                } catch (Throwable $game_e) {}
                if ($si_game_label === '') {
                    $si_game_label = $si_game_slug !== '' ? ucwords(str_replace(['-', '_'], ' ', $si_game_slug)) : 'Unknown Game';
                }

                // ── Seller payout ──────────────────────────────────────────
                if (!empty($si_item['seller_id'])) {
                    $si_seller = $db->row("SELECT * FROM sellers WHERE id = ? LIMIT 1", (int)$si_item['seller_id']);
                    if (!empty($si_seller) && empty($si_seller['is_banned'])) {
                        $si_fee        = ($si_seller['fee_percent'] !== null && $si_seller['fee_percent'] !== '')
                            ? (float)$si_seller['fee_percent'] : 15.0;
                        // Always use the original item price, never invoice total_price,
                        // which is 0 when the client paid with LB Coins.
                        $si_cut        = (int)round($si_sale_price * (1 - $si_fee / 100));
                        $si_old_bal    = (int)$si_seller['balance'];
                        $si_new_bal    = $si_old_bal + $si_cut;
                        $db->run("UPDATE sellers SET balance = ? WHERE id = ?", $si_new_bal, (int)$si_seller['id']);
                        db_add_row('seller_payments', [
                            'seller_id'     => (int)$si_seller['id'],
                            'type'          => 'sale_payout',
                            'amount_cents'  => $si_cut,
                            'note'          => "Item #{$si_item['id']} sold – fee: {$si_fee}%",
                            'balance_after' => $si_new_bal,
                        ]);
                        process_seller_rank((int)$si_seller['id']);

                        // Discord DM to seller
                        try {
                            $dm_seller = $db->row("SELECT discord_id FROM sellers WHERE id = ? LIMIT 1", (int)$si_seller['id']);
                            if (!empty($dm_seller['discord_id']) && defined('DS_BOT_TOKEN') && DS_BOT_TOKEN) {
                                $price_eur  = number_format($si_sale_price / 100, 2);
                                $cut_eur    = number_format($si_cut / 100, 2);
                                $item_url   = BASE_URL . '/seller-area/item-orders';
                                $dm_embed   = ['embeds' => [['title' => '🎁 Item Sold!',
                                    'description' => '**' . addslashes($si_item['title'] ?? 'Item') . '**',
                                    'color' => 0x4ade80,
                                    'fields' => [
                                        ['name' => '🎮 Game',          'value' => $si_game_label,  'inline' => true],
                                        ['name' => '💰 Sale Price',    'value' => '€' . $price_eur, 'inline' => true],
                                        ['name' => '🏦 Your Earnings', 'value' => '€' . $cut_eur,   'inline' => true],
                                        ['name' => '🔗 View Orders',   'value' => '[Open Seller Area](' . $item_url . ')', 'inline' => false],
                                    ],
                                    
                                    'timestamp' => date('c'),
                                ]]];
                                $ch_dm = curl_init('https://discord.com/api/v10/users/@me/channels');
                                curl_setopt_array($ch_dm, [CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bot ' . DS_BOT_TOKEN], CURLOPT_POST => 1, CURLOPT_POSTFIELDS => json_encode(['recipient_id' => $dm_seller['discord_id']]), CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 5]);
                                $dm_resp    = curl_exec($ch_dm); curl_close($ch_dm);
                                $dm_channel = json_decode($dm_resp, true);
                                if (!empty($dm_channel['id'])) {
                                    $ch_msg = curl_init('https://discord.com/api/v10/channels/' . $dm_channel['id'] . '/messages');
                                    curl_setopt_array($ch_msg, [CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bot ' . DS_BOT_TOKEN], CURLOPT_POST => 1, CURLOPT_POSTFIELDS => json_encode($dm_embed), CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 5]);
                                    curl_exec($ch_msg); curl_close($ch_msg);
                                }
                            }
                        } catch (\Throwable $dm_e) { /* never block sale flow */ }
                    }
                }

                // ── Cashback / loyalty ─────────────────────────────────────
                process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $invoice['order_id']);
                process_cashback_points(
                    $invoice['client_id'],
                    $invoice['total_price'] != 0 ? $invoice['total_price'] / 100 : $invoice['coins_used'],
                    $invoice['order_id'],
                    0.00
                );
                process_client_loyalty($invoice['client_id']);

                // ── Giveaway ticket ────────────────────────────────────────
                if (function_exists('giveaway_grant_invoice_ticket')) {
                    giveaway_grant_invoice_ticket((int)($invoice['id'] ?? 0));
                }

                // ── Buyer notification email ───────────────────────────────
                $si_images_arr = json_decode((string)($si_item['images'] ?? '[]'), true);
                $si_cover      = is_array($si_images_arr) && !empty($si_images_arr[0]) ? $si_images_arr[0] : '';
                $si_qty        = 1;
                if (!empty($invoice['description']) && preg_match('/x(\d+)$/', (string)$invoice['description'], $si_m)) {
                    $si_qty = max(1, (int)$si_m[1]);
                }
                $si_price_fmt  = '€' . number_format($si_sale_price / 100, 2);
                $si_purchase_row = !empty($invoice['id'])
                    ? $db->row("SELECT id FROM selling_item_purchases WHERE invoice_id = ? LIMIT 1", (int)$invoice['id'])
                    : null;
                $si_purchase_id = (int)($si_purchase_row['id'] ?? 0);
                $si_item_url   = BASE_URL . '/profile/item/' . $si_purchase_id;
                $si_buyer_data = !empty($invoice['client_id'])
                    ? db_get_row('clients', ['id' => (int)$invoice['client_id']])
                    : [];

                $notification[] = [
                    'type'         => 'item_purchased',
                    'recipient'    => 'client',
                    'recipient_id' => $invoice['client_id'],
                    'is_email'     => true,
                    'is_web'       => false,
                    'is_discord'   => false,
                    'data'         => json_encode([
                        'username'              => base64_encode((string)($si_buyer_data['username'] ?? 'there')),
                        'item_title'            => base64_encode((string)($si_item['title'] ?? 'Item')),
                        'item_cover'            => base64_encode($si_cover),
                        'item_url'              => base64_encode($si_item_url),
                        'price'                 => base64_encode($si_price_fmt),
                        'quantity'              => base64_encode((string)$si_qty),
                        'delivery_instructions' => base64_encode((string)($si_item['delivery_instructions'] ?? '')),
                    ]),
                ];

                // ── Admin Discord webhook for item sales ───────────────────
                try {
                    $admin_webhook_url = lb_sold_notification_webhook_url('item');
                    if (!empty($admin_webhook_url) && function_exists('curl_init')) {
                        $si_admin_base = defined('ADMN_URL') ? ADMN_URL : BASE_URL . '/admin-area';
                        $si_admin_url = $si_purchase_id > 0
                            ? $si_admin_base . '/item-order/' . $si_purchase_id
                            : $si_admin_base . '/item-orders';
                        $si_seller_value = !empty($si_seller)
                            ? ('#' . (int)$si_seller['id'] . ' - ' . ($si_seller['username'] ?? 'Seller'))
                            : ('#' . (int)($si_item['seller_id'] ?? 0));
                        $si_customer_value = !empty($si_buyer_data)
                            ? ('#' . (int)($si_buyer_data['id'] ?? $invoice['client_id'] ?? 0) . ' - ' . ($si_buyer_data['username'] ?? 'Guest'))
                            : ('#' . (int)($invoice['client_id'] ?? 0));
                        $admin_webhook_data = [
                            'username' => 'Item Notif',
                            'content'  => '',
                            'embeds'   => [[
                                'title'       => '🎁 New Item Sold',
                                'description' => '**' . (string)($si_item['title'] ?? 'Item') . '**',
                                'color'       => 0x22c55e,
                                'fields'      => [
                                    ['name' => '🆔 Item Order ID', 'value' => $si_purchase_id > 0 ? ('#' . $si_purchase_id) : 'Pending', 'inline' => true],
                                    ['name' => '🎮 Game',          'value' => $si_game_label, 'inline' => true],
                                    ['name' => '💰 Price',         'value' => $si_price_fmt, 'inline' => true],
                                    ['name' => '👤 Customer',      'value' => $si_customer_value, 'inline' => true],
                                    ['name' => '🏪 Seller',        'value' => $si_seller_value, 'inline' => true],
                                    ['name' => '🔗 Admin Link',    'value' => '[View Item Order](' . $si_admin_url . ')', 'inline' => true],
                                ],
                                'timestamp' => date('c'),
                            ]],
                        ];
                        $ch_admin_item = curl_init($admin_webhook_url);
                        curl_setopt($ch_admin_item, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
                        curl_setopt($ch_admin_item, CURLOPT_POST, 1);
                        curl_setopt($ch_admin_item, CURLOPT_POSTFIELDS, json_encode($admin_webhook_data));
                        curl_setopt($ch_admin_item, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($ch_admin_item, CURLOPT_HEADER, 0);
                        curl_setopt($ch_admin_item, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch_admin_item, CURLOPT_TIMEOUT, 5);
                        curl_exec($ch_admin_item);
                        curl_close($ch_admin_item);
                    }
                } catch (Throwable $admin_webhook_e) {}

                // ── Seller notification email ──────────────────────────────
                if (!empty($si_seller)) {
                    $si_order_url = BASE_URL . '/seller-area/item-order/' . $si_purchase_id;
                    $notification[] = [
                        'type'         => 'item_sold',
                        'recipient'    => 'seller',
                        'recipient_id' => (int)$si_seller['id'],
                        'is_email'     => true,
                        'is_web'       => false,
                        'is_discord'   => false,
                        'data'         => json_encode([
                            'username'   => base64_encode((string)($si_seller['username'] ?? 'there')),
                            'item_title' => base64_encode((string)($si_item['title'] ?? 'Item')),
                            'item_cover' => base64_encode($si_cover),
                            'order_url'  => base64_encode($si_order_url),
                            'price'      => base64_encode($si_price_fmt),
                            'earnings'   => base64_encode('€' . number_format(((int)($si_cut ?? 0)) / 100, 2, '.', '')),
                            'fee_percent'=> base64_encode(number_format((float)($si_fee ?? 15), 2, '.', '')),
                            'balance_after' => base64_encode('€' . number_format(((int)($si_new_bal ?? ($si_seller['balance'] ?? 0))) / 100, 2, '.', '')),
                            'game'       => base64_encode($si_game_label),
                            'quantity'   => base64_encode((string)$si_qty),
                            'buyer'      => base64_encode((string)($si_buyer_data['username'] ?? 'Guest')),
                        ]),
                    ];
                }

                return $notification;
            }
            break;

        case 'selling_topup':
            global $db;
            $tu_topup = $db->row("SELECT st.*, g.name AS db_game_name, g.slug AS db_game_slug FROM selling_topups st LEFT JOIN games g ON g.id = st.game_id WHERE st.id = ? LIMIT 1", (int)$invoice['order_id']);
            if ($tu_topup) {
                try {
                    $db->run("CREATE TABLE IF NOT EXISTS selling_topup_purchases (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        topup_id INT NOT NULL,
                        seller_id INT NOT NULL,
                        client_id INT NULL,
                        invoice_id INT NULL,
                        game_id INT NULL,
                        game_slug VARCHAR(191) NULL,
                        game_name VARCHAR(191) NULL,
                        offer_key VARCHAR(191) NULL,
                        offer_title VARCHAR(255) NOT NULL,
                        offer_amount DECIMAL(12,2) NULL,
                        offer_unit VARCHAR(64) NULL,
                        region VARCHAR(128) NULL,
                        platform VARCHAR(128) NULL,
                        quantity INT NOT NULL DEFAULT 1,
                        unit_price INT NOT NULL DEFAULT 0,
                        price INT NOT NULL DEFAULT 0,
                        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
                        waiting_time_value INT NOT NULL DEFAULT 0,
                        waiting_time_unit VARCHAR(16) NOT NULL DEFAULT 'minutes',
                        waiting_time_minutes INT NOT NULL DEFAULT 0,
                        checkout_data LONGTEXT NULL,
                        status VARCHAR(32) NOT NULL DEFAULT 'PAID',
                        paid_at DATETIME NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY uniq_invoice (invoice_id),
                        INDEX idx_seller (seller_id),
                        INDEX idx_client (client_id),
                        INDEX idx_game (game_id),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                } catch (Throwable $e) {}

                $tu_purchase = !empty($invoice['id']) ? $db->row("SELECT * FROM selling_topup_purchases WHERE invoice_id = ? LIMIT 1", (int)$invoice['id']) : null;
                $tu_qty = max(1, (int)($tu_purchase['quantity'] ?? 1));
                if (empty($tu_purchase) && !empty($invoice['description']) && preg_match('/x(\d+)$/', (string)$invoice['description'], $tu_m)) {
                    $tu_qty = max(1, (int)$tu_m[1]);
                }
                $tu_unit_price = (int)($tu_purchase['unit_price'] ?? $tu_topup['price'] ?? 0);
                $tu_sale_price = (int)($tu_purchase['price'] ?? ($tu_unit_price * $tu_qty));
                if (empty($tu_purchase) && !empty($invoice['id'])) {
                    $tu_checkout_data = (string)($invoice['checkout_data'] ?? '');
                    try {
                        $db->run("INSERT INTO selling_topup_purchases (topup_id, seller_id, client_id, invoice_id, game_id, game_slug, game_name, offer_key, offer_title, offer_amount, offer_unit, region, platform, quantity, unit_price, price, currency, waiting_time_value, waiting_time_unit, waiting_time_minutes, checkout_data, status, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PAID', NOW())",
                            (int)$tu_topup['id'],
                            (int)$tu_topup['seller_id'],
                            !empty($invoice['client_id']) ? (int)$invoice['client_id'] : null,
                            (int)$invoice['id'],
                            !empty($tu_topup['game_id']) ? (int)$tu_topup['game_id'] : null,
                            (string)($tu_topup['db_game_slug'] ?? $tu_topup['game_slug'] ?? ''),
                            (string)($tu_topup['db_game_name'] ?? $tu_topup['game_name'] ?? 'Game'),
                            (string)($tu_topup['offer_key'] ?? ''),
                            (string)($tu_topup['offer_title'] ?? 'Top Up'),
                            $tu_topup['offer_amount'] ?? null,
                            (string)($tu_topup['offer_unit'] ?? ''),
                            (string)($tu_topup['region'] ?? 'Global'),
                            (string)($tu_topup['platform'] ?? ''),
                            $tu_qty,
                            $tu_unit_price,
                            $tu_sale_price,
                            (string)($invoice['currency'] ?? 'EUR'),
                            (int)($tu_topup['waiting_time_value'] ?? 0),
                            (string)($tu_topup['waiting_time_unit'] ?? 'minutes'),
                            (int)($tu_topup['waiting_time_minutes'] ?? 0),
                            $tu_checkout_data
                        );
                        $tu_purchase = $db->row("SELECT * FROM selling_topup_purchases WHERE invoice_id = ? LIMIT 1", (int)$invoice['id']);
                        $db->run("UPDATE selling_topups SET stock = GREATEST(0, stock - ?), sold_count = COALESCE(sold_count,0) + ?, updated_at = NOW() WHERE id = ?", $tu_qty, $tu_qty, (int)$tu_topup['id']);
                        if (!empty($tu_topup['seller_id']) && function_exists('sync_seller_stats')) sync_seller_stats((int)$tu_topup['seller_id']);
                    } catch (Throwable $tu_create_e) {}
                }
                $tu_game_label = trim((string)($tu_purchase['game_name'] ?? $tu_topup['db_game_name'] ?? $tu_topup['game_name'] ?? 'Game'));
                $tu_game_slug = trim((string)($tu_purchase['game_slug'] ?? $tu_topup['db_game_slug'] ?? $tu_topup['game_slug'] ?? ''));
                $tu_title = trim((string)($tu_purchase['offer_title'] ?? $tu_topup['offer_title'] ?? 'Top Up'));
                $tu_region = trim((string)($tu_purchase['region'] ?? $tu_topup['region'] ?? 'Global'));
                $tu_platform = trim((string)($tu_purchase['platform'] ?? $tu_topup['platform'] ?? ''));
                $tu_wait_val = (int)($tu_purchase['waiting_time_value'] ?? $tu_topup['waiting_time_value'] ?? 0);
                $tu_wait_unit = (string)($tu_purchase['waiting_time_unit'] ?? $tu_topup['waiting_time_unit'] ?? 'minutes');
                $tu_wait_label = $tu_wait_unit === 'hours' ? $tu_wait_val . ' h' : ($tu_wait_unit === 'days' ? $tu_wait_val . ' d' : $tu_wait_val . ' min');
                $tu_purchase_id = (int)($tu_purchase['id'] ?? 0);
                $tu_seller = null;

                // Seller payout
                if (!empty($tu_topup['seller_id'])) {
                    $tu_seller = $db->row("SELECT * FROM sellers WHERE id = ? LIMIT 1", (int)$tu_topup['seller_id']);
                    if (!empty($tu_seller) && empty($tu_seller['is_banned'])) {
                        $tu_fee = ($tu_seller['fee_percent'] !== null && $tu_seller['fee_percent'] !== '') ? (float)$tu_seller['fee_percent'] : 15.0;
                        $tu_cut = (int)round($tu_sale_price * (1 - $tu_fee / 100));
                        $tu_old_bal = (int)($tu_seller['balance'] ?? 0);
                        $tu_new_bal = $tu_old_bal + $tu_cut;
                        $db->run("UPDATE sellers SET balance = ? WHERE id = ?", $tu_new_bal, (int)$tu_seller['id']);
                        db_add_row('seller_payments', [
                            'seller_id' => (int)$tu_seller['id'],
                            'type' => 'sale_payout',
                            'amount_cents' => $tu_cut,
                            'note' => "Top Up #" . (int)$tu_topup['id'] . " sold" . ($tu_purchase_id ? " – order #" . $tu_purchase_id : "") . " – fee: " . $tu_fee . "%",
                            'balance_after' => $tu_new_bal,
                        ]);
                        if (function_exists('process_seller_rank')) process_seller_rank((int)$tu_seller['id']);

                        // Discord DM to seller
                        try {
                            if (!empty($tu_seller['discord_id']) && defined('DS_BOT_TOKEN') && DS_BOT_TOKEN) {
                                $tu_price_eur = number_format($tu_sale_price / 100, 2);
                                $tu_cut_eur = number_format($tu_cut / 100, 2);
                                $tu_seller_url = BASE_URL . '/seller-area/top-ups';
                                $tu_dm_embed = ['embeds' => [[
                                    'title' => '⚡ New Top Up Sold!',
                                    'description' => '**' . addslashes($tu_title) . '**',
                                    'color' => 0x4ade80,
                                    'fields' => [
                                        ['name' => '🎮 Game', 'value' => $tu_game_label, 'inline' => true],
                                        ['name' => '🌍 Region', 'value' => $tu_region ?: 'Global', 'inline' => true],
                                        ['name' => '⏱️ Delivery', 'value' => $tu_wait_label, 'inline' => true],
                                        ['name' => '🔢 Quantity', 'value' => (string)$tu_qty, 'inline' => true],
                                        ['name' => '💰 Sale Price', 'value' => '€' . $tu_price_eur, 'inline' => true],
                                        ['name' => '🏦 Your Earnings', 'value' => '€' . $tu_cut_eur, 'inline' => true],
                                        ['name' => '🔗 View Orders', 'value' => '[Open Seller Area](' . $tu_seller_url . ')', 'inline' => false],
                                    ],
                                    'timestamp' => date('c'),
                                ]]];
                                $ch_dm = curl_init('https://discord.com/api/v10/users/@me/channels');
                                curl_setopt_array($ch_dm, [CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bot ' . DS_BOT_TOKEN], CURLOPT_POST => 1, CURLOPT_POSTFIELDS => json_encode(['recipient_id' => $tu_seller['discord_id']]), CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 5]);
                                $dm_resp = curl_exec($ch_dm); curl_close($ch_dm);
                                $dm_channel = json_decode($dm_resp, true);
                                if (!empty($dm_channel['id'])) {
                                    $ch_msg = curl_init('https://discord.com/api/v10/channels/' . $dm_channel['id'] . '/messages');
                                    curl_setopt_array($ch_msg, [CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bot ' . DS_BOT_TOKEN], CURLOPT_POST => 1, CURLOPT_POSTFIELDS => json_encode($tu_dm_embed), CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 5]);
                                    curl_exec($ch_msg); curl_close($ch_msg);
                                }
                            }
                        } catch (Throwable $dm_e) {}
                    }
                }

                process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $invoice['order_id']);
                process_cashback_points($invoice['client_id'], $invoice['total_price'] != 0 ? $invoice['total_price'] / 100 : $invoice['coins_used'], $invoice['order_id'], 0.00);
                process_client_loyalty($invoice['client_id']);
                if (function_exists('giveaway_grant_invoice_ticket')) giveaway_grant_invoice_ticket((int)($invoice['id'] ?? 0));

                $tu_buyer_data = !empty($invoice['client_id']) ? db_get_row('clients', ['id' => (int)$invoice['client_id']]) : [];
                $tu_price_fmt = '€' . number_format($tu_sale_price / 100, 2);

                // Admin Discord webhook for top up sales
                try {
                    $admin_webhook_url = lb_sold_notification_webhook_url('topup');
                    if (!empty($admin_webhook_url) && function_exists('curl_init')) {
                        $tu_admin_base = defined('ADMN_URL') ? ADMN_URL : BASE_URL . '/admin-area';
                        $tu_admin_url = $tu_admin_base . '/top-up-orders';
                        $tu_seller_value = !empty($tu_seller) ? ('#' . (int)$tu_seller['id'] . ' - ' . ($tu_seller['username'] ?? 'Seller')) : ('#' . (int)($tu_topup['seller_id'] ?? 0));
                        $tu_customer_value = !empty($tu_buyer_data) ? ('#' . (int)($tu_buyer_data['id'] ?? $invoice['client_id'] ?? 0) . ' - ' . ($tu_buyer_data['username'] ?? 'Guest')) : ('#' . (int)($invoice['client_id'] ?? 0));
                        $admin_webhook_data = [
                            'username' => 'Top Up Notif',
                            'content' => '',
                            'embeds' => [[
                                'title' => '⚡ Top Up Sold',
                                'description' => '**' . $tu_title . '**',
                                'color' => 0x3b82f6,
                                'fields' => [
                                    ['name' => '🆔 Top Up Order ID', 'value' => $tu_purchase_id > 0 ? ('#' . $tu_purchase_id) : 'Pending', 'inline' => true],
                                    ['name' => '🎮 Game', 'value' => $tu_game_label, 'inline' => true],
                                    ['name' => '🌍 Region', 'value' => $tu_region ?: 'Global', 'inline' => true],
                                    ['name' => '💰 Price', 'value' => $tu_price_fmt, 'inline' => true],
                                    ['name' => '🔢 Quantity', 'value' => (string)$tu_qty, 'inline' => true],
                                    ['name' => '⏱️ Delivery', 'value' => $tu_wait_label, 'inline' => true],
                                    ['name' => '👤 Customer', 'value' => $tu_customer_value, 'inline' => true],
                                    ['name' => '🏪 Seller', 'value' => $tu_seller_value, 'inline' => true],
                                    ['name' => '🔗 Admin Link', 'value' => '[View Top Up Orders](' . $tu_admin_url . ')', 'inline' => true],
                                ],
                                'timestamp' => date('c'),
                            ]],
                        ];
                        $ch_admin_topup = curl_init($admin_webhook_url);
                        curl_setopt($ch_admin_topup, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
                        curl_setopt($ch_admin_topup, CURLOPT_POST, 1);
                        curl_setopt($ch_admin_topup, CURLOPT_POSTFIELDS, json_encode($admin_webhook_data));
                        curl_setopt($ch_admin_topup, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($ch_admin_topup, CURLOPT_HEADER, 0);
                        curl_setopt($ch_admin_topup, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch_admin_topup, CURLOPT_TIMEOUT, 5);
                        curl_exec($ch_admin_topup);
                        curl_close($ch_admin_topup);
                    }
                } catch (Throwable $admin_webhook_e) {}

                $notification[] = [
                    'type' => 'topup_purchased',
                    'recipient' => 'client',
                    'recipient_id' => $invoice['client_id'],
                    'is_email' => true,
                    'is_web' => true,
                    'is_discord' => false,
                    'data' => json_encode([
                        'purchase_id' => base64_encode((string)$tu_purchase_id),
                        'topup_title' => base64_encode($tu_title),
                        'game' => base64_encode($tu_game_label),
                        'region' => base64_encode($tu_region),
                        'price' => base64_encode($tu_price_fmt),
                        'quantity' => base64_encode((string)$tu_qty),
                    ]),
                ];

                if (!empty($tu_seller)) {
                    $notification[] = [
                        'type' => 'topup_sold',
                        'recipient' => 'seller',
                        'recipient_id' => (int)$tu_seller['id'],
                        'is_email' => true,
                        'is_web' => true,
                        'is_discord' => false,
                        'data' => json_encode([
                            'purchase_id' => base64_encode((string)$tu_purchase_id),
                            'topup_title' => base64_encode($tu_title),
                            'game' => base64_encode($tu_game_label),
                            'order_url' => base64_encode(BASE_URL . '/seller-area/top-ups'),
                            'price' => base64_encode($tu_price_fmt),
                            'earnings' => base64_encode('€' . number_format(((int)($tu_cut ?? 0)) / 100, 2, '.', '')),
                            'fee_percent' => base64_encode(number_format((float)($tu_fee ?? 15), 2, '.', '')),
                            'balance_after' => base64_encode('€' . number_format(((int)($tu_new_bal ?? ($tu_seller['balance'] ?? 0))) / 100, 2, '.', '')),
                            'quantity' => base64_encode((string)$tu_qty),
                            'region' => base64_encode($tu_region),
                            'buyer' => base64_encode((string)($tu_buyer_data['username'] ?? 'Guest')),
                        ]),
                    ];
                }

                return $notification;
            }
            break;

        case 'digital_good':
            $purchaseId = function_exists('dg_mark_invoice_paid') ? dg_mark_invoice_paid($invoice) : null;
            $purchase = $purchaseId ? (function_exists('dg_get_purchase') ? dg_get_purchase((int)$purchaseId) : null) : null;
            $item = null;
            if ($purchase && !empty($purchase['item_id'])) {
                $item = function_exists('dg_get_listing') ? dg_get_listing((int)$purchase['item_id']) : db_get_row('digital_goods', ['id' => (int)$purchase['item_id']]);
            } else {
                $item = function_exists('dg_get_listing') ? dg_get_listing((int)$invoice['order_id']) : db_get_row('digital_goods', ['id' => (int)$invoice['order_id']]);
            }

            if ($item) {
                global $db;
                $seller = !empty($item['seller_id']) ? $db->row("SELECT * FROM sellers WHERE id = ? LIMIT 1", (int)$item['seller_id']) : null;
                $sale_price_cents = (int)($purchase['price'] ?? 0);
                if ($sale_price_cents <= 0) {
                    $unit = (int)($item['price'] ?? 0);
                    $qty  = (int)($purchase['quantity'] ?? 1);
                    $sale_price_cents = $unit * max(1, $qty);
                }

                if (!empty($seller) && empty($seller['is_banned']) && $sale_price_cents > 0) {
                    $existingPay = $purchaseId ? $db->row("SELECT id FROM seller_payments WHERE seller_id=? AND note LIKE ? AND type='sale_payout' LIMIT 1", (int)$seller['id'], '%order #' . (int)$purchaseId . '%') : null;
                    if (empty($existingPay)) {
                        $fee = ($seller['fee_percent'] !== null && $seller['fee_percent'] !== '') ? (float)$seller['fee_percent'] : 15.0;
                        $seller_cut = (int)round($sale_price_cents * (1 - $fee / 100));
                        $old_balance = (int)($seller['balance'] ?? 0);
                        $new_balance = $old_balance + $seller_cut;
                        $db->run("UPDATE sellers SET balance = ? WHERE id = ?", $new_balance, (int)$seller['id']);
                        db_add_row('seller_payments', [
                            'seller_id' => (int)$seller['id'],
                            'type' => 'digital_good_payout',
                            'amount_cents' => $seller_cut,
                            'note' => 'Digital Good #' . (int)($item['id'] ?? 0) . ' sold' . ($purchaseId ? ' – order #' . (int)$purchaseId : '') . ' – fee: ' . $fee . '%',
                            'balance_after' => $new_balance,
                        ]);
                        if (function_exists('process_seller_rank')) process_seller_rank((int)$seller['id']);
                    }
                }

                process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $invoice['order_id']);
                process_cashback_points($invoice['client_id'], $invoice['total_price'] != 0 ? $invoice['total_price'] / 100 : $invoice['coins_used'], $invoice['order_id'], 0.00);
                process_client_loyalty($invoice['client_id']);

                if (function_exists('giveaway_grant_invoice_ticket')) giveaway_grant_invoice_ticket((int)($invoice['id'] ?? 0));

                // ── Admin Discord webhook for digital good sales ────────────
                $dg_buyer = [];
                try {
                    $dg_admin_base = defined('ADMN_URL') ? ADMN_URL : BASE_URL . '/admin-area';
                    $dg_admin_url = (int)($item['id'] ?? 0) > 0
                        ? $dg_admin_base . '/digital-goods/listings/' . (int)$item['id'] . '/edit'
                        : $dg_admin_base . '/digital-goods/listings';
                    $dg_buyer = !empty($invoice['client_id']) ? db_get_row('clients', ['id' => (int)$invoice['client_id']]) : [];
                    $dg_customer_value = !empty($dg_buyer)
                        ? ('#' . (int)($dg_buyer['id'] ?? $invoice['client_id'] ?? 0) . ' - ' . ($dg_buyer['username'] ?? 'Guest'))
                        : ('#' . (int)($invoice['client_id'] ?? 0));
                    $dg_seller_value = !empty($seller)
                        ? ('#' . (int)$seller['id'] . ' - ' . ($seller['username'] ?? 'Seller'))
                        : ('#' . (int)($item['seller_id'] ?? 0));

                    lb_send_sold_notification('digital_good', [
                        'username' => 'Digital Good Notif',
                        'content'  => '',
                        'embeds'   => [[
                            'title'       => '💾 Digital Good Sold',
                            'description' => '**' . (string)($item['title'] ?? 'Digital Good') . '**',
                            'color'       => 0xa855f7,
                            'fields'      => [
                                ['name' => '🆔 Order ID',   'value' => (int)($purchaseId ?? 0) > 0 ? ('#' . (int)$purchaseId) : 'Pending', 'inline' => true],
                                ['name' => '🏷️ Brand',      'value' => (string)($item['brand'] ?? '—') ?: '—', 'inline' => true],
                                ['name' => '🌍 Region',     'value' => (string)($item['region'] ?? '') ?: 'Global', 'inline' => true],
                                ['name' => '💰 Price',      'value' => '€' . number_format($sale_price_cents / 100, 2), 'inline' => true],
                                ['name' => '🔢 Quantity',   'value' => (string)((int)($purchase['quantity'] ?? 1)), 'inline' => true],
                                ['name' => '👤 Customer',   'value' => $dg_customer_value, 'inline' => true],
                                ['name' => '🏪 Seller',     'value' => $dg_seller_value, 'inline' => true],
                                ['name' => '🔗 Admin Link', 'value' => '[View Listing](' . $dg_admin_url . ')', 'inline' => true],
                            ],
                            'timestamp' => date('c'),
                        ]],
                    ]);
                } catch (\Throwable $dg_webhook_e) {}

                // The email templates expect a cover image, a formatted price and the
                // counterparty name — the same set the item sale sends.
                $dg_images = json_decode((string)($item['images'] ?? '[]'), true);
                $dg_cover = is_array($dg_images) && !empty($dg_images[0]) ? (string)$dg_images[0] : (string)($item['brand_icon'] ?? '');
                $dg_qty = (int)($purchase['quantity'] ?? 1);
                $dg_price_fmt = '€' . number_format($sale_price_cents / 100, 2, '.', '');
                $dg_buyer_name = (string)($dg_buyer['username'] ?? 'Guest');
                $dg_seller_name = (string)($seller['username'] ?? 'Seller');
                $dg_order_url = BASE_URL . '/seller-area/digital-goods/' . (int)($purchaseId ?? 0);
                $dg_client_url = BASE_URL . '/profile/digital-goods/' . (int)($purchaseId ?? 0);

                $notification[] = [
                    'type' => 'digital_good_purchased',
                    'recipient' => 'client',
                    'recipient_id' => $invoice['client_id'],
                    'is_email' => true,
                    'is_web' => 1,
                    'data' => json_encode([
                        'username' => base64_encode($dg_buyer_name),
                        'purchase_id' => base64_encode((string)($purchaseId ?? 0)),
                        'item_title' => base64_encode((string)($item['title'] ?? 'Digital Good')),
                        'item_cover' => base64_encode($dg_cover),
                        'order_url' => base64_encode($dg_client_url),
                        'seller' => base64_encode($dg_seller_name),
                        'price' => base64_encode($dg_price_fmt),
                        'quantity' => base64_encode((string)$dg_qty),
                    ]),
                ];

                if (!empty($item['seller_id'])) {
                    $notification[] = [
                        'type' => 'digital_good_sold',
                        'recipient' => 'seller',
                        'recipient_id' => (int)$item['seller_id'],
                        'is_email' => true,
                        'is_web' => 1,
                        'data' => json_encode([
                            'username' => base64_encode($dg_seller_name),
                            'purchase_id' => base64_encode((string)($purchaseId ?? 0)),
                            'item_title' => base64_encode((string)($item['title'] ?? 'Digital Good')),
                            'item_cover' => base64_encode($dg_cover),
                            'order_url' => base64_encode($dg_order_url),
                            'price' => base64_encode($dg_price_fmt),
                            'earnings' => base64_encode('€' . number_format(((int)($seller_cut ?? 0)) / 100, 2, '.', '')),
                            'fee_percent' => base64_encode(number_format((float)($fee ?? 15), 2, '.', '')),
                            'balance_after' => base64_encode('€' . number_format(((int)($new_balance ?? ($seller['balance'] ?? 0))) / 100, 2, '.', '')),
                            'quantity' => base64_encode((string)$dg_qty),
                            'buyer' => base64_encode($dg_buyer_name),
                        ]),
                    ];

                    // Discord DM to the seller. The admin webhook above only posts into
                    // the staff channel, so without this the seller learns about the sale
                    // from the email alone.
                    try {
                        lb_send_seller_sale_dm($seller, [
                            'title' => '💾 You sold a digital good!',
                            'description' => '**' . (string)($item['title'] ?? 'Digital Good') . '**',
                            'color' => 0xa855f7,
                            'fields' => [
                                ['name' => '🆔 Order ID', 'value' => (int)($purchaseId ?? 0) > 0 ? ('#' . (int)$purchaseId) : 'Pending', 'inline' => true],
                                ['name' => '🔢 Quantity', 'value' => (string)$dg_qty, 'inline' => true],
                                ['name' => '💰 Revenue', 'value' => $dg_price_fmt, 'inline' => true],
                                ['name' => '🤑 You earn', 'value' => '€' . number_format(((int)($seller_cut ?? 0)) / 100, 2, '.', ''), 'inline' => true],
                                ['name' => '🏦 New balance', 'value' => '€' . number_format(((int)($new_balance ?? ($seller['balance'] ?? 0))) / 100, 2, '.', ''), 'inline' => true],
                                ['name' => '👤 Buyer', 'value' => $dg_buyer_name, 'inline' => true],
                            ],
                        ], $dg_order_url, '📦 Deliver now');
                    } catch (\Throwable $dg_dm_e) {}
                }

                return $notification;
            }
            break;

        case 'tip':
            $tip = db_get_row('tips', ['id' => $invoice['order_id']]);
            if ($tip != false) {
                $booster_cut = round($tip['amount'] * 0.80);
                $booster = db_get_row('boosters', ['id' => $tip['booster_id']]);
                db_update_row('boosters', ['id' => $tip['booster_id']], ['balance' => $booster['balance'] + $booster_cut]);
                $desc = $tip['description'];
                if (empty($desc)) {
                    $desc = 'Tip #' . $tip['id'] . ' from ' . CLIENT_DATA['username'];
                }
                db_add_row('booster_payments', [
                    'booster_id' => $booster['id'],
                    'type' => 'tip',
                    'note' => $desc,
                    'amount' => $booster_cut,
                    'currency' => $invoice['currency'],
                    'balance_update' => $booster['balance'] . '|' . $booster['balance'] + $booster_cut,
                    'sender' => CLIENT_DATA['username'],
                    'sender_type' => 'client',
                    'sender_id' => CLIENT_ID
                ]);
                db_update_row('tips', ['id' => $tip['id']], ['status' => 1, 'paid_at' => date('Y-m-d H:i:s')]);
                $notification[] = [
                    'type' => 'booster_tip',
                    'recipient' => 'booster',
                    'recipient_id' => $booster['id'],
                    'data' => json_encode([
                        'client_username' => base64_encode(CLIENT_DATA['username']),
                        'amount' => base64_encode($booster_cut),
                        'description' => base64_encode($tip['description']),
                        'currency' => base64_encode($invoice['currency']),
                    ]),
                    'is_email' => true,
                    'is_web' => true,
                ];

                $notification[] = [
                    'type' => 'booster_tip_discord',
                    'data' => json_encode([
                        'tip_id' => base64_encode($tip['id']),
                        'client_id' => base64_encode(CLIENT_ID),
                        'client_username' => base64_encode(CLIENT_DATA['username']),
                        'booster_id' => base64_encode($booster['id']),
                        'booster_username' => base64_encode($booster['username']),
                        'amount_booster' => base64_encode($booster_cut),
                        'currency' => base64_encode($invoice['currency']),
                        'description' => base64_encode($tip['description']),
                    ]),
                    'is_discord' => true,
                ];

                // Also post a system message into the order chat (if the tip is linked to an order)
                $tip_order_id = (int)($tip['order_id'] ?? 0);
                if ($tip_order_id > 0) {
                    $tip_amount_display = util_format_currency_display($tip['currency'] ?? $invoice['currency']) . util_format_price_display((int)($tip['amount'] ?? 0));

                    $chat_data = [
                        'message' => "💝 Tip sent: " . $tip_amount_display,
                        'message_type' => 'text',
                    ];

                    $user = [
                        'id' => '999',
                        'username' => 'System',
                        'icon' => 'https://lolboost.gg/public/uploads/icons/default1.png',
                    ];

                    chat_insert_message($tip_order_id, $chat_data, $user, 'system');
                }

                return $notification;
                break;
            } else {
                return false;
            }

        case 'invoice':
            $order = db_get_row('orders', ['id' => $invoice['order_id']]);
            if ($order != false) {
                db_update_row('orders', ['id' => $order['id']], ['price' => $invoice['price'] + $order['price']]);
            }
            $client = db_get_row('clients', ['id' => $invoice['client_id']]);
            $notification[] = [
                'type' => 'custom_invoice_paid',
                'recipient' => 'admin',
                'recipient_id' => db_get_row('admins', ['username' => $invoice['issuer']])['id'],
                'data' => json_encode([
                    'invoice_id' => base64_encode($invoice['id']),
                    'client_id' => base64_encode($client['id']),
                    'client_username' => base64_encode($client['username']),
                    'amount' => base64_encode($invoice['price']),
                    'description' => base64_encode($invoice['description']),
                    'currency' => base64_encode($invoice['currency']),
                ]),
                'is_email' => true,
            ];
            process_cashback_deduction($invoice['client_id'], $invoice['coins_used'], $invoice['order_id']);
            process_cashback_points($invoice['client_id'], $invoice['total_price'] != 0 ? $invoice['total_price'] / 100 : $invoice['coins_used'], $invoice['order_id'], 0.00);
            process_client_loyalty($invoice['client_id']);

            $order_id = $invoice['order_id'];
            $chat_data = [
                'message' => 'Thank you for your purchase at lolboost.gg! 🎉

                Here you can chat with your booster. 😊
                If you have any questions, our live chat is available anytime! 💬

                📌 Please note: You\'ll be notified as soon as your booster accepts the Order!',
                'message_type' => 'text',
            ];

            $user = [
                'id' => '999',
                'username' => 'System',
                'icon' => 'https://lolboost.gg/public/uploads/icons/default1.png'
            ];

            chat_insert_message($order_id, $chat_data, $user, 'system');

            return $notification;
            break;

        case 'addon':
            $addons = json_decode($invoice['note']);
            $order = db_get_row('orders', ['id' => $invoice['order_id']]);

            if (is_array($addons)) {
                foreach ($addons as $addon) {
                    db_update_row('order_options', ['order_id' => $invoice['order_id']], [$addon => 1]);
                }
            }
            db_update_row('invoices', ['id' => $invoice['id']], ['note' => null]);

            db_update_row('orders', ['id' => $order['id']], ['price' => $order['price'] + $invoice['total_price']]);

            // Also post a system message into the order chat so both client/booster see the add-on purchase
            $addon_text = '';
            if (is_array($addons)) {
                $addon_names = [];
                foreach ($addons as $a) {
                    $a = trim((string)$a);
                    if ($a === '') continue;
                    $addon_names[] = function_exists('util_format_replace_opt') ? util_format_replace_opt($a) : $a;
                }
                if (!empty($addon_names)) {
                    $addon_text = implode(', ', $addon_names);
                }
            } else {
                $addon_text = trim((string)($invoice['note'] ?? ''));
            }

            $addon_amount_display = util_format_currency_display($order['currency'] ?? ($invoice['currency'] ?? 'EUR')) . util_format_price_display((int)($invoice['total_price'] ?? $invoice['price'] ?? 0));
            $addon_msg = "✅ Add-on payment received: " . ($addon_text !== '' ? ($addon_text . ' — ') : '') . $addon_amount_display;

            $chat_data = [
                'message' => $addon_msg,
                'message_type' => 'text',
            ];

            $user = [
                'id' => '999',
                'username' => 'System',
                'icon' => 'https://lolboost.gg/public/uploads/icons/default1.png',
            ];

            chat_insert_message((int)$invoice['order_id'], $chat_data, $user, 'system');

            return $notification;
            break;

        default:
            return $notification;
            break;
    }
    return false;
}


function util_format_booster_rank($rank_id)
{
    $rank = db_get_row('booster_ranks', ['id' => $rank_id]);
    if ($rank != false) {
        return $rank['name'];
    }
    return false;
}

function auth_google_login($code)
{
    $client = new Google_Client();
    $client->setClientId(GL_CLIENT_ID);
    $client->setClientSecret(GL_SECRET);
    $client->setRedirectUri(GL_REDIRECT_URL);
    $token = $client->fetchAccessTokenWithAuthCode($code);
    if (isset($token['access_token'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $data = [
            "email" => $google_account_info->email,
            "username" => generate_password(5),
            "oauth_provider" => "google",
            "oauth_uid" => $google_account_info->id
        ];
        return $data;
    } else {
        return false;
    }
}
function auth_discord_load_token($code)
{
    $url = "https://discord.com/api/oauth2/token";
    $data = array(
        "client_id" => DS_CLIENT_ID,
        "client_secret" => DS_SECRET_ID,
        "grant_type" => "authorization_code",
        "code" => $code,
        "redirect_uri" => DS_REDIRECT_URL
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    $results = json_decode($response, true);
    if (isset($results['access_token'])) {
        return $results['access_token'];
    } else {
        return false;
    }
}
function auth_discord_login($code)
{
    $access_token = auth_discord_load_token($code);
    if ($access_token != false) {
        $url = "https://discord.com/api/users/@me";
        $headers = array('Content-Type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $access_token);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $results = json_decode($response, true);
        $data = [
            "email" => $results['email'],
            "username" => $results['username'],
            "discord" => $results['username'] . "#" . $results['discriminator'],
            "oauth_provider" => "discord",
            "oauth_uid" => $results['id']
        ];
        return $data;
    } else {
        return false;
    }
}

function db_run_query($query)
{
    global $db;
    $rows = $db->run($query);
    return $rows;
}

function util_format_option_inline($name, $value)
{
    if (str_contains($name, 'is_')) {
        $value = $value == 1 ? '🟢 Enabled' : '🔴 Disabled';
    } else {
        switch ($name) {
            case 'roles':
            case 'champions':
                $value = str_replace('MonkeyKing', 'Wukong', $value);
                break;
            case 'agents':
                $value = str_replace(',', ', ', $value);
                $value = str_replace('|', ', ', $value);
                break;
            case 'vpn_country':
                $value = util_format_country($value);
                break;
            default:
                $value = util_format_default_type($value);
                break;
        }
    }
    $name = str_replace('is_', '', $name);
    $name = util_format_default_type($name);

    if ($name == 'Coaching') {
        $name = 'Voice Chat';
    }

    if ($name == 'Champions') {
        $name = 'Customer Champions';
    }

    if ($name == 'Roles') {
        $name = 'Customer Roles';
    }

    if ($name == 'Undercover Winrate') {
        $name = 'Undercover Winrate';
    }

    if ($name == 'Moderate Kda') {
        $name = 'Moderate KDA';
    }

    return [$name, $value];
}

function load_boost_extra_options()
{
    return ["agents", "champions", "roles", "is_priority", "is_streaming", "is_solo_only", "bonus_win_extra_fee", "is_coaching", "is_hidden_duo", "is_undercover_winrate", "is_moderate_kda"];
}

function util_load_boost_forms_select($select = null, $game = 'league-of-legends')
{
    $html = '';
    $forms = db_get_rows('boost_forms', ['game' => $game]);
    foreach ($forms as $form) {
        if ($form['id'] == $select) {
            $html .= '<option value="' . $form['id'] . '" selected="">' . $form['name'] . '</option>';
        } else {
            $html .= '<option value="' . $form['id'] . '">' . $form['name'] . '</option>';
        }
    }
    return $html;
}



function util_get_all_games(bool $activeOnly = true)
{
    static $cache = [];
    $key = $activeOnly ? 'active' : 'all';
    if (isset($cache[$key])) return $cache[$key];
    global $db;
    $sql = 'SELECT * FROM games' . ($activeOnly ? ' WHERE status = 1' : '') . ' ORDER BY sort_order ASC, name ASC';
    $cache[$key] = $db->run($sql) ?: [];
    return $cache[$key];
}

function util_get_game_by_slug(string $slug)
{
    static $cache = [];
    if (array_key_exists($slug, $cache)) return $cache[$slug];
    global $db;
    $row = $db->run('SELECT * FROM games WHERE slug = ? AND status = 1 LIMIT 1', $slug);
    $cache[$slug] = (!empty($row) ? $row[0] : null);
    return $cache[$slug];
}

function util_get_game_services(int $gameId)
{
    // Request-scoped cache, batch-loaded once. util_game_nav_config() calls this once per game to
    // build the site nav on every page load, and the /services/* hub pages call it again per game —
    // that was N separate queries (one per game) instead of one query for every game's services.
    static $cache = [];
    static $batchLoaded = false;
    if (isset($cache[$gameId])) return $cache[$gameId];

    global $db;

    if (!$batchLoaded) {
        $batchLoaded = true;
        $allRows = $db->run('SELECT * FROM game_services WHERE status = 1 ORDER BY game_id ASC, sort_order ASC') ?: [];
        foreach ($allRows as $row) {
            $row['config'] = !empty($row['config']) ? (json_decode($row['config'], true) ?? []) : [];
            $cache[(int)$row['game_id']][] = $row;
        }
    }

    if (!isset($cache[$gameId])) {
        $cache[$gameId] = [];
    }
    return $cache[$gameId];
}

function util_game_has_service(int $gameId, string $serviceType)
{
    foreach (util_get_game_services($gameId) as $s) {
        if ($s['service_type'] === $serviceType) return true;
    }
    return false;
}

if (!function_exists('lb_seed_chat_system_message')) {
    /**
     * Writes a one-off system message into a JSON chat file, creating it if needed.
     * Idempotent: the same text is never appended twice, so re-processing an invoice
     * cannot duplicate the welcome message. Never throws — chat problems must not
     * break the sale flow that calls this.
     */
    function lb_seed_chat_system_message(string $chatPath, $chatKey, string $message): bool
    {
        $message = trim($message);
        if ($chatPath === '' || $message === '') return false;

        try {
            $dir = dirname($chatPath);
            if (!is_dir($dir)) @mkdir($dir, 0777, true);

            $fp = @fopen($chatPath, 'c+');
            if (!$fp) return false;
            if (!@flock($fp, LOCK_EX)) { fclose($fp); return false; }

            rewind($fp);
            $chat = json_decode(stream_get_contents($fp) ?: '', true);
            if (!is_array($chat)) $chat = ['order_id' => $chatKey, 'messages' => []];
            if (!isset($chat['messages']) || !is_array($chat['messages'])) $chat['messages'] = [];

            $raw = esc($message);

            // Dedup on a normalized hash instead of the stored string: escaping and
            // whitespace of older rows can differ, which would let the same welcome
            // message be appended a second time.
            $fingerprint = static function ($text): string {
                $text = html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = preg_replace('/\s+/u', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $text)));
                return md5(trim((string)$text));
            };
            $seedKey = $fingerprint($message);

            if (in_array($seedKey, (array)($chat['system_seeds'] ?? []), true)) {
                @flock($fp, LOCK_UN);
                fclose($fp);
                return false;
            }
            foreach ($chat['messages'] as $existing) {
                if (($existing['sender'] ?? '') !== 'system') continue;
                if ($fingerprint($existing['raw'] ?? $existing['content'] ?? '') === $seedKey) {
                    @flock($fp, LOCK_UN);
                    fclose($fp);
                    return false;
                }
            }

            $now = time();
            $chat['messages'][] = [
                'sender' => 'system',
                'sender_id' => 999,
                'sender_name' => 'System',
                'sender_icon' => 'https://lolboost.gg/public/uploads/icons/default1.png',
                'content' => function_exists('make_links_clickable') ? make_links_clickable($raw) : $raw,
                'raw' => $raw,
                'edited' => 0,
                'type' => 'text',
                'seen' => 0,
                'seen_at' => 0,
                'notify' => 0,
                'time' => $now,
                'uuid' => $now . '-' . bin2hex(random_bytes(4)),
            ];
            $chat['system_seeds'] = array_values(array_unique(array_merge((array)($chat['system_seeds'] ?? []), [$seedKey])));

            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, json_encode($chat, JSON_PRETTY_PRINT));
            fflush($fp);
            @flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('lb_discord_webhook_post')) {
    /** Fire-and-forget Discord webhook POST. Never throws. */
    function lb_discord_webhook_post(string $url, array $payload): bool
    {
        $url = trim($url);
        if ($url === '' || !function_exists('curl_init')) return false;

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
            ]);
            curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $httpCode >= 200 && $httpCode < 300;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('lb_mask_review_client_name')) {
    /** "MostFeared" -> "M*d" — same masking the boosting review webhook uses. */
    function lb_mask_review_client_name(string $name): string
    {
        $name = trim($name);
        if ($name === '') return 'Guest';
        $first = mb_substr($name, 0, 1);
        $last  = mb_strlen($name) > 1 ? mb_substr($name, -1) : '';
        return $first . '*' . $last;
    }
}

if (!function_exists('lb_seller_review_service_label')) {
    /**
     * Human label for what a seller review is about. review_source is only set for
     * digital goods, so the marketplace tables are probed for the older sources.
     */
    function lb_seller_review_service_label(array $review): string
    {
        $source = strtolower(trim((string)($review['review_source'] ?? '')));
        $map = [
            'digital_good' => 'Digital Good',
            'account'      => 'Account',
            'item'         => 'Item',
            'topup'        => 'Top Up',
            'topup_purchase' => 'Top Up',
            'item_purchase'  => 'Item',
        ];
        if (isset($map[$source])) return $map[$source];

        $purchaseId = (int)($review['purchase_id'] ?? 0);
        if ($purchaseId <= 0) return 'Marketplace Purchase';

        global $db;
        try {
            $sellerId = (int)($review['seller_id'] ?? 0);
            foreach ([
                'selling_accounts'         => 'Account',
                'selling_item_purchases'   => 'Item',
                'selling_topup_purchases'  => 'Top Up',
                'digital_good_purchases'   => 'Digital Good',
            ] as $table => $label) {
                $row = $db->row("SELECT id FROM {$table} WHERE id = ? AND seller_id = ? LIMIT 1", $purchaseId, $sellerId);
                if (!empty($row)) return $label;
            }
        } catch (\Throwable $e) {}

        return 'Marketplace Purchase';
    }
}

if (!function_exists('lb_send_seller_review_webhook')) {
    /**
     * Announces a new seller review (account / item / top up / digital good) on Discord,
     * using the same two channels as the boosting and GG-Girl reviews. URLs are passed
     * in by the caller — they live next to the other review webhooks in ajax.php.
     * Never throws: a Discord outage must not fail the client's submission.
     */
    function lb_send_seller_review_webhook(array $review, string $adminWebhookUrl = '', string $communityWebhookUrl = ''): bool
    {
        try {
            global $db;
            $sellerId = (int)($review['seller_id'] ?? 0);
            $clientId = (int)($review['client_id'] ?? 0);
            $rating   = max(0, min(5, (int)($review['rating'] ?? 0)));
            $approved = !empty($review['approved']);

            $seller = $sellerId > 0 ? $db->row("SELECT id, username, icon FROM sellers WHERE id = ? LIMIT 1", $sellerId) : null;
            $client = $clientId > 0 ? $db->row("SELECT id, username FROM clients WHERE id = ? LIMIT 1", $clientId) : null;

            $sellerName  = trim((string)($seller['username'] ?? '')) ?: 'Seller';
            $clientName  = trim((string)($client['username'] ?? '')) ?: 'Guest';
            $serviceName = lb_seller_review_service_label($review);
            $stars       = str_repeat('⭐', max(1, $rating));

            $comment = html_entity_decode(trim((string)($review['comment'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (mb_strlen($comment) > 900) $comment = mb_substr($comment, 0, 897) . '…';

            $sellerIcon = trim((string)($seller['icon'] ?? ''));
            if ($sellerIcon !== '' && strpos($sellerIcon, 'http') !== 0) {
                $sellerIcon = (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($sellerIcon, '/');
            }
            if ($sellerIcon === '') $sellerIcon = 'https://lolboost.gg/public/uploads/icons/default1.png';

            $adminUrl = (defined('ADMN_URL') ? ADMN_URL : '') . '/seller/' . $sellerId . '/reviews';
            $sent = false;

            // ── Admin channel ──
            if (trim($adminWebhookUrl) !== '') {
                $sent = lb_discord_webhook_post($adminWebhookUrl, [
                    'username' => 'Order Reviews Notifier',
                    'avatar_url' => 'https://lolboost.gg/public/uploads/icons/default1.png',
                    'allowed_mentions' => ['parse' => []],
                    'embeds' => [[
                        'title' => $approved ? 'New Seller Review Submitted!' : '⚠️ Seller Review needs approval',
                        'color' => $approved ? hexdec('5865F2') : hexdec('F59E0B'),
                        'fields' => [
                            ['name' => 'Customer', 'value' => $clientName . ' (ID: ' . $clientId . ')', 'inline' => true],
                            ['name' => 'Seller',   'value' => $sellerName . ' (ID: ' . $sellerId . ')', 'inline' => true],
                            ['name' => 'Score',    'value' => $rating . '/5', 'inline' => true],
                            ['name' => 'Service',  'value' => $serviceName, 'inline' => true],
                            ['name' => 'Status',   'value' => $approved ? 'Published automatically' : 'Hidden until approved', 'inline' => true],
                            ['name' => 'Comment',  'value' => $comment !== '' ? ('*"' . $comment . '"*') : '_No comment._', 'inline' => false],
                            ['name' => 'Action',   'value' => '[🚀 View](' . $adminUrl . ')', 'inline' => false],
                        ],
                        'timestamp' => date('c'),
                    ]],
                ]) || $sent;
            }

            // ── Community channel: published reviews only ──
            if ($approved && trim($communityWebhookUrl) !== '') {
                $reviewUrl     = 'https://lolboost.gg/reviews';
                $trustpilotUrl = 'https://www.trustpilot.com/review/lolboost.gg';
                $profileUrl    = function_exists('seller_profile_url') && !empty($seller)
                    ? ((defined('BASE_URL') ? BASE_URL : '') . seller_profile_url($seller))
                    : $reviewUrl;

                $sent = lb_discord_webhook_post($communityWebhookUrl, [
                    'username' => 'lolboost.gg Reviews',
                    'avatar_url' => 'https://lolboost.gg/public/assets/website/images/icon-bg.png',
                    'content' => '⭐ Client ' . lb_mask_review_client_name($clientName) . ' rated the service with **' . $rating . ' stars!**',
                    'allowed_mentions' => ['parse' => []],
                    'embeds' => [[
                        'title' => 'New Review Available!',
                        'description' => $comment !== '' ? ('*"' . $comment . '"*') : '',
                        'url' => $reviewUrl,
                        'color' => hexdec('FFD700'),
                        'author' => ['name' => 'Seller ' . $sellerName, 'icon_url' => $sellerIcon],
                        'fields' => [
                            ['name' => 'Score',   'value' => $stars . ' **' . $rating . '/5**', 'inline' => true],
                            ['name' => 'Client',  'value' => lb_mask_review_client_name($clientName), 'inline' => true],
                            ['name' => 'Service', 'value' => $serviceName, 'inline' => true],
                            ['name' => 'Seller',  'value' => $sellerName, 'inline' => true],
                            ['name' => 'Links',   'value' => '[View Reviews](' . $reviewUrl . ') • [Trustpilot](' . $trustpilotUrl . ') • [Seller Profile](' . $profileUrl . ')', 'inline' => false],
                        ],
                        'footer' => ['text' => 'LOLBoost.gg verified review'],
                        'timestamp' => date('c'),
                    ]],
                ]) || $sent;
            }

            return $sent;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('lb_send_egirl_review_webhook')) {
    /**
     * Announces a new GG-Girl review on Discord, mirroring the boosting reviews:
     *  - the internal #review-notif channel always gets a compact entry,
     *  - the public #reviews channel only gets reviews that are actually published,
     *    so a held-back sub-3-star review never leaks to the community.
     *
     * Both URLs are passed in by the caller — they live next to the boosting review
     * webhooks in ajax.php, not in a config constant.
     * Never throws: a Discord outage must not fail the client's submission.
     */
    function lb_send_egirl_review_webhook(array $review, string $adminWebhookUrl = '', string $communityWebhookUrl = ''): bool
    {
        try {
            global $db;
            $egirlId  = (int)($review['egirl_id'] ?? 0);
            $clientId = (int)($review['client_id'] ?? 0);
            $orderId  = (int)($review['order_id'] ?? 0);
            $rating   = max(0, min(5, (int)($review['rating'] ?? 0)));
            $approved = !empty($review['approved']);

            $egirl  = $egirlId  > 0 ? $db->row("SELECT id, username, icon FROM boosters WHERE id = ? LIMIT 1", $egirlId) : null;
            $client = $clientId > 0 ? $db->row("SELECT id, username FROM clients WHERE id = ? LIMIT 1", $clientId) : null;
            $order  = $orderId  > 0 ? $db->row("SELECT service_title FROM egirl_orders WHERE id = ? LIMIT 1", $orderId) : null;

            $egirlName   = trim((string)($egirl['username'] ?? '')) ?: 'GG-Girl';
            $clientName  = trim((string)($client['username'] ?? '')) ?: 'Guest';
            $serviceName = trim((string)($order['service_title'] ?? '')) ?: 'GG-Girl Session';
            $stars       = str_repeat('⭐', max(1, $rating));

            $comment = html_entity_decode(trim((string)($review['comment'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (mb_strlen($comment) > 900) $comment = mb_substr($comment, 0, 897) . '…';

            $egirlIcon = trim((string)($egirl['icon'] ?? ''));
            if ($egirlIcon !== '' && strpos($egirlIcon, 'http') !== 0) {
                $egirlIcon = (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($egirlIcon, '/');
            }
            if ($egirlIcon === '') $egirlIcon = 'https://lolboost.gg/public/uploads/icons/default1.png';

            $adminUrl = (defined('ADMN_URL') ? ADMN_URL : '') . '/egirl/' . $egirlId . '/reviews';
            $sent = false;

            // ── Admin channel: every review, flagged when it needs approval ──
            if (trim($adminWebhookUrl) !== '') {
                $sent = lb_discord_webhook_post($adminWebhookUrl, [
                    'username' => 'Order Reviews Notifier',
                    'avatar_url' => 'https://lolboost.gg/public/uploads/icons/default1.png',
                    'allowed_mentions' => ['parse' => []],
                    'embeds' => [[
                        'title' => $approved ? 'New GG-Girl Review Submitted!' : '⚠️ GG-Girl Review needs approval',
                        'color' => $approved ? hexdec('5865F2') : hexdec('F59E0B'),
                        'fields' => [
                            ['name' => 'Customer',  'value' => $clientName . ' (ID: ' . $clientId . ')', 'inline' => true],
                            ['name' => 'Booking ID','value' => (string)$orderId, 'inline' => true],
                            ['name' => 'Score',     'value' => $rating . '/5', 'inline' => true],
                            ['name' => 'GG-Girl',   'value' => $egirlName . ' (ID: ' . $egirlId . ')', 'inline' => true],
                            ['name' => 'Status',    'value' => $approved ? 'Published automatically' : 'Hidden until approved', 'inline' => true],
                            ['name' => 'Comment',   'value' => $comment !== '' ? ('*"' . $comment . '"*') : '_No comment._', 'inline' => false],
                            ['name' => 'Action',    'value' => '[🚀 View](' . $adminUrl . ')', 'inline' => false],
                        ],
                        'timestamp' => date('c'),
                    ]],
                ]) || $sent;
            }

            // ── Community channel: published reviews only ──
            if ($approved && trim($communityWebhookUrl) !== '') {
                $reviewUrl     = 'https://lolboost.gg/reviews';
                $trustpilotUrl = 'https://www.trustpilot.com/review/lolboost.gg';
                $profileUrl    = $egirlId > 0 ? ((defined('BASE_URL') ? BASE_URL : '') . '/egirls/' . $egirlId) : $reviewUrl;

                $sent = lb_discord_webhook_post($communityWebhookUrl, [
                    'username' => 'lolboost.gg Reviews',
                    'avatar_url' => 'https://lolboost.gg/public/assets/website/images/icon-bg.png',
                    'content' => '⭐ Client ' . lb_mask_review_client_name($clientName) . ' rated the service with **' . $rating . ' stars!**',
                    'allowed_mentions' => ['parse' => []],
                    'embeds' => [[
                        'title' => 'New Review Available!',
                        'description' => $comment !== '' ? ('*"' . $comment . '"*') : '',
                        'url' => $reviewUrl,
                        'color' => hexdec('FFD700'),
                        'author' => ['name' => 'GG-Girl ' . $egirlName, 'icon_url' => $egirlIcon],
                        'fields' => [
                            ['name' => 'Score',   'value' => $stars . ' **' . $rating . '/5**', 'inline' => true],
                            ['name' => 'Client',  'value' => lb_mask_review_client_name($clientName), 'inline' => true],
                            ['name' => 'Service', 'value' => $serviceName, 'inline' => true],
                            ['name' => 'GG-Girl', 'value' => $egirlName, 'inline' => true],
                            ['name' => 'Booking', 'value' => '#' . $orderId, 'inline' => true],
                            ['name' => 'Links',   'value' => '[View Reviews](' . $reviewUrl . ') • [Trustpilot](' . $trustpilotUrl . ') • [GG-Girl Profile](' . $profileUrl . ')', 'inline' => false],
                        ],
                        'footer' => ['text' => 'LOLBoost.gg verified review'],
                        'timestamp' => date('c'),
                    ]],
                ]) || $sent;
            }

            return $sent;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('lb_egirl_game_key')) {
    /**
     * Canonical key for a GG-Girl game, matching the short codes stored
     * pipe-separated in egirl_profiles.games ("lol|val|tft|apex-legends").
     */
    function lb_egirl_game_key(string $game): string
    {
        $game = strtolower(trim($game));
        if ($game === '') return '';
        return function_exists('util_account_short_game_code')
            ? util_account_short_game_code($game)
            : $game;
    }
}

if (!function_exists('lb_egirl_profiles_ensure_game_ranks_column')) {
    /**
     * The games pipe string now holds every boosting game, which no longer fits the
     * original column, and per-game ranks need somewhere to live that does not
     * require a migration whenever a game is added.
     */
    function lb_egirl_profiles_ensure_game_ranks_column(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;

        global $db;
        if (!isset($db) || !is_object($db)) return;

        foreach ([
            "ALTER TABLE egirl_profiles MODIFY games VARCHAR(1024) NULL",
            "ALTER TABLE egirl_profiles ADD COLUMN IF NOT EXISTS game_ranks TEXT NULL",
        ] as $sql) {
            try { $db->run($sql); } catch (Throwable $e) {}
        }
    }
}

if (!function_exists('lb_egirl_division_labels')) {
    /**
     * Division labels for a ladder with $divCount divisions, highest first
     * (4 divisions => IV, III, II, I). Empty for divisionless ranks.
     */
    function lb_egirl_division_labels(int $divCount): array
    {
        if ($divCount <= 0) return [];
        $numerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
        $labels = array_slice($numerals, 0, min($divCount, count($numerals)));
        return array_reverse($labels);
    }
}

if (!function_exists('lb_egirl_game_tiers')) {
    /**
     * Rank ladder of one game as [tierName => divisionCount].
     *
     * LoL/Valorant/TFT keep their classic ladders; every other game reuses the
     * authoritative boost-form config, so a game added there needs no extra work here.
     */
    function lb_egirl_game_tiers(string $gameKey): array
    {
        $key = lb_egirl_game_key($gameKey);

        $lol = ['Iron' => 4, 'Bronze' => 4, 'Silver' => 4, 'Gold' => 4, 'Platinum' => 4, 'Emerald' => 4, 'Diamond' => 4, 'Master' => 0, 'Grandmaster' => 0, 'Challenger' => 0];
        if ($key === 'lol' || $key === 'tft') return $lol;
        if ($key === 'val') {
            return ['Iron' => 3, 'Bronze' => 3, 'Silver' => 3, 'Gold' => 3, 'Platinum' => 3, 'Diamond' => 3, 'Ascendant' => 3, 'Immortal' => 3, 'Radiant' => 0];
        }

        if (!function_exists('lb_generic_game_rank_config')) return [];
        $config = lb_generic_game_rank_config($key);
        if (!is_array($config) || empty($config['ranks'])) return [];

        $tiers = [];
        foreach ($config['ranks'] as $index => $name) {
            $name = trim((string)$name);
            if ($name === '') continue;
            $tiers[$name] = (int)(($config['rank_divs'] ?? [])[$index] ?? 0);
        }
        return $tiers;
    }
}

if (!function_exists('lb_egirl_game_ranks')) {
    /**
     * Saved ranks of a GG-Girl as [gameKey => "Tier Division"].
     *
     * LoL/Valorant/TFT have their own columns for historical reasons; every other
     * game lives in the game_ranks JSON column.
     */
    function lb_egirl_game_ranks($profile): array
    {
        if (!is_array($profile)) return [];

        $ranks = [];
        foreach (['lol' => 'lol_rank', 'val' => 'val_rank', 'tft' => 'tft_rank'] as $key => $column) {
            $value = trim((string)($profile[$column] ?? ''));
            if ($value !== '') $ranks[$key] = $value;
        }

        $decoded = json_decode((string)($profile['game_ranks'] ?? ''), true);
        if (is_array($decoded)) {
            foreach ($decoded as $gameKey => $rank) {
                $gameKey = lb_egirl_game_key((string)$gameKey);
                $rank = trim((string)$rank);
                if ($gameKey !== '' && $rank !== '') $ranks[$gameKey] = $rank;
            }
        }

        return $ranks;
    }
}

if (!function_exists('lb_egirl_game_options')) {
    /**
     * Games a GG-Girl can pick in her profile / setup wizard.
     *
     * Every active game with a boosting service, keyed by the same short code
     * that is stored pipe-separated in boosters.games ("lol|val|tft"), so the
     * saved selection round-trips. Games without a short code use their slug.
     *
     * Shape: ['lol' => ['label' => 'League of Legends', 'icon' => '…', 'slug' => '…', 'tiers' => []]]
     */
    function lb_egirl_game_options(): array
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        $options = [];

        try {
            foreach (util_get_all_games(true) as $game) {
                $slug = strtolower(trim((string)($game['slug'] ?? '')));
                if ($slug === '') continue;

                $gameId = (int)($game['id'] ?? 0);
                if ($gameId > 0 && !util_game_has_service($gameId, 'boosting')) continue;

                $key = function_exists('util_account_short_game_code')
                    ? util_account_short_game_code($slug)
                    : $slug;

                $icon = trim((string)($game['icon'] ?? ''));
                if ($icon === '' && function_exists('util_game_icon_url')) {
                    $icon = util_game_icon_url($slug);
                }

                $options[$key] = [
                    'label' => trim((string)($game['name'] ?? '')) ?: ucwords(str_replace('-', ' ', $slug)),
                    'icon'  => $icon,
                    'slug'  => $slug,
                    // [tier => divisionCount]. Games without a known ladder return an
                    // empty list, which the views render as "no rank picker".
                    'tiers' => lb_egirl_game_tiers($key),
                ];
            }
        } catch (Throwable $e) {
            $options = [];
        }

        // The girls' own dashboard must never end up with an empty picker, so the
        // three core games stay available even if the games table is unreachable.
        if (empty($options)) {
            $options = [
                'lol' => ['label' => 'League of Legends', 'icon' => '', 'slug' => 'league-of-legends', 'tiers' => lb_egirl_game_tiers('lol')],
                'val' => ['label' => 'Valorant',          'icon' => '', 'slug' => 'valorant',          'tiers' => lb_egirl_game_tiers('val')],
                'tft' => ['label' => 'Teamfight Tactics', 'icon' => '', 'slug' => 'teamfight-tactics', 'tiers' => lb_egirl_game_tiers('tft')],
            ];
            foreach ($options as $k => $o) {
                if (function_exists('util_game_icon_url')) {
                    $options[$k]['icon'] = util_game_icon_url($o['slug']);
                }
            }
        }

        return $cache = $options;
    }
}

function util_game_nav_config($game = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (util_get_all_games() as $g) {
            $slug     = $g['slug'];
            $gameId   = (int)$g['id'];
            $services = util_get_game_services($gameId);

            $categories = [];
            $cards      = [];

            // Keep the root game URL as a neutral services overview. The Boosting
            // navigation entry opens the first actual boost form, preferably rank-boost.
            $boostForms = util_load_game_boost_forms($slug);
            $firstBoostHref = '';
            foreach ($boostForms as $boostForm) {
                if (($boostForm['slug'] ?? '') === 'rank-boost') {
                    $firstBoostHref = (string)($boostForm['href'] ?? '');
                    break;
                }
            }
            if ($firstBoostHref === '' && !empty($boostForms[0]['href'])) {
                $firstBoostHref = (string)$boostForms[0]['href'];
            }

            foreach ($services as $svc) {
                switch ($svc['service_type']) {
                    case 'boosting':
                        $boostingHref = $firstBoostHref !== '' ? $firstBoostHref : '/' . $slug . '/rank-boost';
                        $categories['boosting'] = ['label' => 'Boosting', 'href' => $boostingHref];
                        $cards[] = [
                            'label' => 'Boosting',
                            'description' => 'Rank, wins and other boosting services available for this game.',
                            'href' => $boostingHref,
                            'fa_icon' => 'fa-solid fa-rocket',
                        ];
                        break;
                    case 'accounts':
                        $categories['accounts'] = ['label' => 'Accounts', 'href' => '/' . $slug . '/accounts'];
                        $cards[] = ['label' => 'Premium Accounts', 'description' => 'Hand-leveled accounts ready to play.',      'href' => '/' . $slug . '/premium-accounts', 'icon' => '/public/uploads/icons/default2.png'];
                        $cards[] = ['label' => 'Ranked Accounts',  'description' => 'Verified listings from trusted sellers.',   'href' => '/' . $slug . '/accounts',          'icon' => '/public/uploads/icons/challenger.png'];
                        break;
                    case 'items':
                        $categories['items'] = ['label' => 'Items', 'href' => '/' . $slug . '/items'];
                        $cards[] = ['label' => 'Items & Gifting',   'description' => 'Browse skins, loot and more.',             'href' => '/' . $slug . '/items',             'icon' => ASSET_URL . '/website/images/icons/' . $slug . '.png'];
                        break;
                    case 'coaching':
                        $coachingHref = '/' . $slug . '/coaching';
                        $categories['coaching'] = ['label' => 'Coaching', 'href' => $coachingHref];
                        $cards[] = [
                            'label' => 'Coaching',
                            'description' => 'Personal coaching sessions with experienced players.',
                            'href' => $coachingHref,
                            'fa_icon' => 'fa-solid fa-user-graduate',
                        ];
                        break;
                    case 'egirl':
                        $categories['egirl'] = ['label' => 'Companion', 'href' => '/egirls'];
                        break;
                }
            }

            // LoL Classic uses the legacy boost forms (IDs 30–36) and can exist
            // without a separate `game_services` boosting row. It is still a
            // boosting game, so expose the same Boosting pill/card everywhere
            // that consumes util_game_nav_config().
            if (
                in_array(strtolower(trim((string)$slug)), ['lol-classic', 'lol_classic', 'league-of-legends-classic'], true)
                && empty($categories['boosting'])
            ) {
                $boostingHref = $firstBoostHref !== '' ? $firstBoostHref : '/lol-classic/rank-boost';
                $categories['boosting'] = ['label' => 'Boosting', 'href' => $boostingHref];
                array_unshift($cards, [
                    'label' => 'Boosting',
                    'description' => 'Rank, wins and other LoL Classic boosting services.',
                    'href' => $boostingHref,
                    'fa_icon' => 'fa-solid fa-rocket',
                ]);
            }

            $_iconMap = [
                'league-of-legends' => 'league-of-legends.png',
                'valorant' => 'valorant.png',
                'teamfight-tactics' => 'teamfight-tactics.png',
                'lol-classic' => 'lol-classic.png',
                'lol_classic' => 'lol-classic.png',
            ];
            $_iconFile = $_iconMap[$slug] ?? ($slug . '.png');
            $iconFile = !empty($g['icon'])
                ? $g['icon']
                : (function_exists('util_game_icon_url')
                    ? util_game_icon_url($slug)
                    : ASSET_URL . '/website/images/icons/' . $_iconFile);

            $cache[$slug] = [
                'label'      => !empty($g['short_code']) ? $g['short_code'] : strtoupper($slug),
                'name'       => $g['name'],
                'slug'       => $slug,
                'icon'       => $iconFile,
                'color'      => $g['color_primary'] ?? '#8b5cf6',
                'categories' => $categories,
                'cards'      => $cards,
                'services'   => $services,
                'game'       => $g,
            ];
        }
    }

    if ($game === null) return $cache;
    return $cache[$game] ?? null;
}

function util_boost_form_href($game, $slug)
{
    $map = [
        'valorant' => [
            'unrated-matches-boost' => '/valorant/unrated-matches',
        ],
    ];

    if (isset($map[$game][$slug])) {
        return $map[$game][$slug];
    }

    return '/' . trim($game, '/') . '/' . trim($slug, '/');
}

function util_boost_form_icon_class($form)
{
    $slug = strtolower((string)($form['slug'] ?? ''));
    $type = strtolower((string)($form['type'] ?? ''));

    $map = [
        'rank-boost' => 'fa-rocket',
        'win-boost' => 'fa-trophy',
        'placements-boost' => 'fa-list-check',
        'placements' => 'fa-list-check',
        'normals-boost' => 'fa-gamepad',
        'unrated-matches-boost' => 'fa-gamepad',
        'matches-boost' => 'fa-gamepad',
        'coaching' => 'fa-headset',
        'arena-boost' => 'fa-shield-halved',
        'champion-mastery' => 'fa-star',
        'clash-boost' => 'fa-people-group',
        'level-boost' => 'fa-arrow-trend-up',
        'pro-games' => 'fa-medal',
        'duo-pass' => 'fa-users',
        'double-up-boost' => 'fa-users',
    ];

    if (isset($map[$slug])) {
        return $map[$slug];
    }

    $typeMap = [
        'rank' => 'fa-rocket',
        'win' => 'fa-trophy',
        'placement' => 'fa-list-check',
        'normal' => 'fa-gamepad',
        'match' => 'fa-gamepad',
        'coaching' => 'fa-headset',
        'arena' => 'fa-shield-halved',
        'clash' => 'fa-people-group',
        'level' => 'fa-arrow-trend-up',
        'pro-games' => 'fa-medal',
        'duo-pass' => 'fa-users',
        'double_up' => 'fa-users',
    ];

    return $typeMap[$type] ?? 'fa-bolt';
}

function util_boost_form_icon_url($icon, $game = 'league-of-legends')
{
    $icon = trim((string)$icon);
    if ($icon === '') {
        return '';
    }

    if (preg_match('~^https?://~i', $icon)) {
        return $icon;
    }

    $clean = basename(ltrim($icon, '/'));

    return '/public/assets/website/images/boost-forms/boost-type-icons/' . $clean;
}


function util_get_accounts_page_config(string $gameSlug): array
{
    $gameSlug = strtolower(trim($gameSlug));
    $aliasMap = [
        'lol' => 'league-of-legends',
        'leagu' => 'league-of-legends',
        'leag' => 'league-of-legends',
        'val' => 'valorant',
        'valor' => 'valorant',
        'valo' => 'valorant',
        'tft' => 'teamfight-tactics',
        'teamf' => 'teamfight-tactics',
        'teamfi' => 'teamfight-tactics',
        'cod' => 'call-of-duty',
        'callofduty' => 'call-of-duty',
    ];
    $gameSlug = $aliasMap[$gameSlug] ?? $gameSlug;

    $game = util_get_game_by_slug($gameSlug);
    if (!$game) return [];
    $services = util_get_game_services((int)$game['id']);
    foreach ($services as $svc) {
        if ($svc['service_type'] === 'accounts') {
            return $svc['config'] ?? [];
        }
    }
    return [];
}

function util_load_game_boost_forms($game)
{
    global $db;

    $game = strtolower(trim((string)$game));

    $publicAliases = [
        'lol' => 'league-of-legends',
        'league_of_legends' => 'league-of-legends',
        'league-of-legends' => 'league-of-legends',
        'lol_classic' => 'lol-classic',
        'league_classic' => 'lol-classic',
        'league-of-legends-classic' => 'lol-classic',
        'lol-classic' => 'lol-classic',
        'val' => 'valorant',
        'valorant' => 'valorant',
        'tft' => 'teamfight-tactics',
        'teamfight_tactics' => 'teamfight-tactics',
        'teamfight-tactics' => 'teamfight-tactics',
    ];

    $gameSlug = $publicAliases[$game] ?? $game;

    // Request-scoped cache. This function used to run a fresh query per call with no memoization,
    // and it's invoked once per game inside util_game_nav_config() (which builds the nav on every
    // page load) plus again per game on the /services/* hub pages and lb_count_service_offers() —
    // easily 100+ redundant queries per hub page load. Batch-load every game's forms in a single
    // query the first time this runs in a request, then serve every subsequent call from memory.
    static $cache = [];
    static $batchLoaded = false;
    static $enriched = [];

    // Only serve from cache once the rows have been through the enrichment loop
    // below. The batch load seeds $cache with raw DB rows for EVERY game, so
    // returning early on a plain array_key_exists() handed out rows without
    // href/icon_url for every game except the one that triggered the batch.
    if (isset($enriched[$gameSlug])) {
        return $cache[$gameSlug];
    }

    $shortCodes = [
        'league-of-legends' => 'lol',
        'lol-classic' => 'lol_classic',
        'valorant' => 'val',
        'teamfight-tactics' => 'tft',
    ];
    $gameShort = $shortCodes[$gameSlug] ?? $gameSlug;

    if (!$batchLoaded) {
        $batchLoaded = true;
        $allRows = $db->run(
            'SELECT bf.*, g.slug AS __game_slug
             FROM boost_forms bf
             INNER JOIN games g ON g.id = bf.game_id
             WHERE bf.status != 0
             ORDER BY bf.game_id ASC, bf.id ASC'
        ) ?: [];
        foreach ($allRows as $row) {
            $slug = $row['__game_slug'];
            unset($row['__game_slug']);
            if ($slug === 'apex-legends' && strtolower(trim((string)($row['slug'] ?? ''))) === 'kills-boost') {
                continue;
            }
            $cache[$slug][] = $row;
        }
    }

    $forms = $cache[$gameSlug] ?? [];

    // Apex Kills Boost is intentionally unavailable until its dedicated form
    // and pricing are ready. Keeping it out of this list also prevents route registration.
    if ($gameSlug === 'apex-legends') {
        $forms = array_values(array_filter($forms, static function ($form) {
            return strtolower(trim((string)($form['slug'] ?? ''))) !== 'kills-boost';
        }));
    }

    // Compatibility fallback for merged databases where old rows still use the game column.
    if (empty($forms)) {
        $gameValues = [$gameSlug, $gameShort];

        if ($gameSlug === 'lol-classic') {
            $gameValues = array_merge($gameValues, [
                'lol_classic',
                'league_classic',
                'league-of-legends-classic',
            ]);
        }

        $gameValues = array_values(array_unique(array_filter($gameValues)));
        $placeholders = implode(',', array_fill(0, count($gameValues), '?'));

        $forms = $db->run(
            "SELECT * FROM boost_forms
             WHERE game IN ({$placeholders})
               AND (status IS NULL OR status != 0)
             ORDER BY id ASC",
            ...$gameValues
        ) ?: [];
    }

    foreach ($forms as &$form) {
        $form['game'] = $gameShort;
        $form['href'] = util_boost_form_href($gameSlug, $form['slug'] ?? '');
        $form['icon_url'] = util_boost_form_icon_url($form['icon'] ?? '', $gameShort);
        $form['icon_class'] = util_boost_form_icon_class($form);
    }
    unset($form);

    $cache[$gameSlug] = $forms;
    $enriched[$gameSlug] = true;

    return $forms;
}

function db_load_boost_form_by_slug(string $gameSlug, string $formSlug)
{
    global $db;
    $rows = $db->run(
        'SELECT bf.* FROM boost_forms bf
         JOIN games g ON g.id = bf.game_id
         WHERE g.slug = ? AND bf.slug = ? AND bf.status != 0
         LIMIT 1',
        $gameSlug, $formSlug
    );
    if (empty($rows)) {
        $rows = $db->run(
            'SELECT * FROM boost_forms WHERE game = ? AND slug = ? AND status != 0 LIMIT 1',
            $gameSlug, $formSlug
        );
    }
    if (empty($rows)) return false;
    $row = $rows[0];
    $row['json'] = load_boost_form_json($row['uuid']);
    // Normalize game to short code (handles truncated values like 'leagu', 'valor', 'teamf')
    $_n = ['league-of-legends'=>'lol','leagu'=>'lol','leag'=>'lol','league'=>'lol',
           'valorant'=>'val','valor'=>'val','valo'=>'val',
           'teamfight-tactics'=>'tft','teamf'=>'tft','teamfi'=>'tft',
           'apex-legends'=>'apex','overwatch-2'=>'ow2',
           'rocket-league'=>'rl','marvel-rivals'=>'rivals','lol-wild-rift'=>'wild-rift'];
    if (isset($row['game'], $_n[$row['game']])) $row['game'] = $_n[$row['game']];
    return $row;
}

function util_register_game_boost_routes(\Buki\Router\Router $router, string $gameSlug, string $viewTemplate)
{
    $game = util_get_game_by_slug($gameSlug);
    if (!$game) return;

    $router->get('/', function () use ($game, $gameSlug) {
        $__cacheKey = 'game-hub-' . $gameSlug;
        if (function_exists('lb_public_page_cache_serve') && lb_public_page_cache_serve($__cacheKey, 180)) return;
        $__pageCache = function_exists('lb_public_page_cache_start') ? lb_public_page_cache_start() : false;
        $boostForms = util_load_game_boost_forms($gameSlug);
        $config     = util_game_nav_config($gameSlug);
        $isLolClassic = in_array($gameSlug, ['lol-classic', 'lol_classic', 'league-of-legends-classic'], true);
        $meta = [
            'title'       => ($game['name'] ?? $gameSlug) . ' Services | LoLBoost.gg',
            'h1'          => ($game['name'] ?? $gameSlug) . ' Services',
            'description' => $isLolClassic
                ? t('Browse all available LoL Classic boosting services in one place.')
                : t('Browse all ' . ($game['name'] ?? $gameSlug) . ' boosting, account and item categories in one place.'),
            'canonical'   => 'https://lolboost.gg/' . $gameSlug,
            'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        ];
        $_shortCodes2 = ['league-of-legends'=>'lol','valorant'=>'val','teamfight-tactics'=>'tft'];
        $_gs = $_shortCodes2[$gameSlug] ?? $gameSlug;
        view_file('website/pages/game-services', [
            'meta'           => $meta,
            'game'           => $_gs,
            'boostForms'     => $boostForms,
            'cards'          => $isLolClassic ? [] : ($config['cards'] ?? []),
            'hidePageHeader' => true,
        ]);
        if (function_exists('lb_public_page_cache_finish')) lb_public_page_cache_finish($__cacheKey, $__pageCache);
    });

    // Register each boost form slug explicitly — avoids conflicts with /accounts /items etc.
    $_boostForms = util_load_game_boost_forms($gameSlug);
    foreach ($_boostForms as $_bf) {
        if (empty($_bf['slug'])) continue;
        $_bfSlug = $_bf['slug'];
        $router->get('/' . $_bfSlug, function () use ($gameSlug, $_bfSlug, $viewTemplate) {
            $__cacheKey = 'boost-form-' . $gameSlug . '-' . $_bfSlug;
            if (function_exists('lb_public_page_cache_serve') && lb_public_page_cache_serve($__cacheKey, 180)) return;

            $data = db_load_boost_form_by_slug($gameSlug, $_bfSlug);
            if (!$data) {
                http_response_code(404);
                header('Location: /404');
                exit;
            }

            $__pageCache = function_exists('lb_public_page_cache_start') ? lb_public_page_cache_start() : false;

            $_shortCodes = ['league-of-legends' => 'lol', 'valorant' => 'val', 'teamfight-tactics' => 'tft'];
            $_gameShort  = $_shortCodes[$gameSlug] ?? $gameSlug;
            $meta = [
                'title'       => ($data['name_long'] ?? $data['name'] ?? ucfirst($_bfSlug)) . ' | LoLBoost.gg',
                'h1'          => ($data['name_long'] ?? $data['name'] ?? ucfirst($_bfSlug)),
                'description' => $data['description'] ?? '',
                'canonical'   => 'https://lolboost.gg/' . $gameSlug . '/' . $_bfSlug,
                'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
            ];
            view_file('website/boost/' . $viewTemplate, ['data' => $data, 'meta' => $meta, 'game' => $_gameShort]);
            if (function_exists('lb_public_page_cache_finish')) lb_public_page_cache_finish($__cacheKey, $__pageCache);
        });
    }
}

function admin_create_game(array $data, array $services = [])
{
    global $db;
    $slug = trim(strtolower($data['slug'] ?? ''));
    $name = trim($data['name'] ?? '');
    if (empty($slug) || empty($name)) return false;
    $existing = $db->run('SELECT id FROM games WHERE slug = ? LIMIT 1', $slug);
    if (!empty($existing)) return false;
    $maxOrder = $db->run('SELECT MAX(sort_order) AS mo FROM games')[0]['mo'] ?? 0;
    $db->run(
        'INSERT INTO games (name, slug, short_code, icon, banner, color_primary, color_accent, sort_order, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())',
        $name, $slug,
        $data['short_code']    ?? strtoupper(substr($slug, 0, 4)),
        $data['icon']          ?? null,
        $data['banner']        ?? null,
        $data['color_primary'] ?? '#8b5cf6',
        $data['color_accent']  ?? '#a78bfa',
        (int)$maxOrder + 1
    );
    $gameId = (int)$db->getPdo()->lastInsertId();
    if ($gameId <= 0) return false;
    $order = 1;
    $defaults = [
        'boosting' => 'Boosting',
        'accounts' => 'Account Shop',
        'items'    => 'Items & Gifting',
        'coaching' => 'Coaching',
        'egirl'    => 'Companion',
    ];
    foreach ($services as $type) {
        if (!isset($defaults[$type])) continue;
        $db->run(
            'INSERT IGNORE INTO game_services (game_id, service_type, label, status, sort_order, config) VALUES (?, ?, ?, 1, ?, ?)',
            $gameId, $type, $defaults[$type], $order++, '{}'
        );
    }
    return $gameId;
}

function admin_toggle_game_service(int $gameId, string $serviceType, bool $enable)
{
    global $db;
    $existing = $db->run(
        'SELECT id FROM game_services WHERE game_id = ? AND service_type = ? LIMIT 1',
        $gameId, $serviceType
    );
    if (empty($existing)) {
        if (!$enable) return true;
        $db->run(
            'INSERT INTO game_services (game_id, service_type, label, status, sort_order) VALUES (?, ?, ?, 1, 99)',
            $gameId, $serviceType, ucfirst($serviceType)
        );
        return true;
    }
    $db->run(
        'UPDATE game_services SET status = ? WHERE game_id = ? AND service_type = ?',
        (int)$enable, $gameId, $serviceType
    );
    return true;
}


/* =========================================================
 * Dynamic account schema support
 * Table: game_account_schemas
 * Lets every game define its own account fields, filters,
 * card badges and view details without editing shop/card/view files.
 * ========================================================= */
if (!function_exists('util_account_normalize_game_slug')) {
    function util_account_normalize_game_slug(string $game): string
    {
        $game = strtolower(trim($game));
        $map = [
            'lol' => 'league-of-legends', 'leagu' => 'league-of-legends', 'leag' => 'league-of-legends',
            'val' => 'valorant', 'valor' => 'valorant', 'valo' => 'valorant',
            'tft' => 'teamfight-tactics', 'teamf' => 'teamfight-tactics', 'teamfi' => 'teamfight-tactics',
            'cod' => 'call-of-duty', 'callofduty' => 'call-of-duty',
        ];
        return $map[$game] ?? $game;
    }
}

if (!function_exists('util_account_short_game_code')) {
    function util_account_short_game_code(string $game): string
    {
        $slug = util_account_normalize_game_slug($game);
        $map = ['league-of-legends'=>'lol','valorant'=>'val','teamfight-tactics'=>'tft','call-of-duty'=>'cod'];
        return $map[$slug] ?? $slug;
    }
}

if (!function_exists('util_account_schema_default')) {
    function util_account_schema_default(string $game): array
    {
        $slug = util_account_normalize_game_slug($game);
        if ($slug !== 'call-of-duty') {
            return [];
        }
        return [
            'enabled' => true,
            'title_field' => 'main_title',
            'headline_icon_field' => 'platform',
            'fields' => [
                ['key'=>'main_title','label'=>'Main Game','type'=>'select','options'=>['Black Ops 6','Black Ops 7','Modern Warfare','Modern Warfare 2','Modern Warfare 3','Black Ops / Warzone 1','Warzone','Warzone 2'],'required'=>true,'show_on_upload'=>true,'show_on_view'=>true,'show_on_card_header'=>true,'filterable'=>true,'filter_type'=>'select','icon'=>'fa-solid fa-gamepad'],
                ['key'=>'cod_titles','label'=>'Extra Games','type'=>'multiselect','options'=>['Black Ops 6','Black Ops 7','Modern Warfare','Modern Warfare 2','Modern Warfare 3','Black Ops / Warzone 1','Warzone','Warzone 2'],'show_on_upload'=>true,'show_on_view'=>true,'filterable'=>true,'filter_type'=>'multiselect','icon'=>'fa-solid fa-layer-group'],
                ['key'=>'platform','label'=>'Platforms','type'=>'select','options'=>['PC (Game Pass)','PlayStation','Xbox One','BattleNet','Steam','All Platforms'],'required'=>true,'show_on_upload'=>true,'show_on_view'=>true,'show_on_card_header'=>true,'show_on_view_header'=>true,'filterable'=>true,'filter_type'=>'multiselect','icon_type'=>'platform','icon'=>'fa-solid fa-desktop'],
                ['key'=>'level','label'=>'Account Level','type'=>'number','min'=>0,'max'=>1000,'show_on_upload'=>true,'show_on_card'=>true,'show_on_view'=>true,'filterable'=>true,'filter_type'=>'range','icon'=>'fas fa-arrow-turn-up','suffix'=>' Level'],
                ['key'=>'prestige','label'=>'Prestige Level','type'=>'number','min'=>0,'show_on_upload'=>true,'show_on_card'=>true,'show_on_view'=>true,'icon'=>'fas fa-medal','suffix'=>' Prestige'],
                ['key'=>'operators','label'=>'Operators','type'=>'number','min'=>0,'show_on_upload'=>true,'show_on_card'=>true,'show_on_view'=>true,'icon'=>'fas fa-users','suffix'=>' Operators'],
                ['key'=>'weapons','label'=>'Weapon Unlocks','type'=>'number','min'=>0,'show_on_upload'=>true,'show_on_card'=>true,'show_on_view'=>true,'icon'=>'fas fa-gun','suffix'=>' Weapons'],
                ['key'=>'camos','label'=>'Camos','type'=>'number','min'=>0,'show_on_upload'=>true,'show_on_card'=>true,'show_on_view'=>true,'icon'=>'fas fa-palette','suffix'=>' Camos'],
                ['key'=>'cod_points','label'=>'CoD Points','type'=>'number','min'=>0,'show_on_upload'=>true,'show_on_card'=>true,'show_on_view'=>true,'icon'=>'fas fa-coins','suffix'=>' Points'],
                ['key'=>'ranked_ready','label'=>'Ranked Ready','type'=>'checkbox','show_on_upload'=>true,'show_on_card'=>true,'show_on_view'=>true,'icon'=>'fas fa-circle-check'],
            ],
        ];
    }
}

if (!function_exists('util_get_game_account_schema')) {
    function util_get_game_account_schema(string $game): array
    {
        static $cache = [];
        $slug = util_account_normalize_game_slug($game);
        if (array_key_exists($slug, $cache)) return $cache[$slug];

        $schema = [];
        try {
            global $db;
            if (isset($db)) {
                $rows = $db->run('SELECT schema_json FROM game_account_schemas WHERE game_slug = ? AND enabled = 1 LIMIT 1', $slug);
                if (!empty($rows[0]['schema_json'])) {
                    $decoded = json_decode((string)$rows[0]['schema_json'], true);
                    if (is_array($decoded)) $schema = $decoded;
                }
            }
        } catch (Throwable $e) {
            $schema = [];
        }

        if (empty($schema)) {
            $schema = util_account_schema_default($slug);
        }
        if (!empty($schema)) {
            $schema['enabled'] = $schema['enabled'] ?? true;
            $schema['fields'] = isset($schema['fields']) && is_array($schema['fields']) ? $schema['fields'] : [];
        }
        $cache[$slug] = $schema;
        return $schema;
    }
}

if (!function_exists('util_account_schema_fields')) {
    function util_account_schema_fields(string $game, string $flag = ''): array
    {
        $schema = util_get_game_account_schema($game);
        $fields = $schema['fields'] ?? [];
        if ($flag === '') return $fields;
        return array_values(array_filter($fields, static function($field) use ($flag) {
            return !empty($field[$flag]);
        }));
    }
}

if (!function_exists('util_account_schema_filter_fields')) {
    function util_account_schema_filter_fields(string $game): array
    {
        return util_account_schema_fields($game, 'filterable');
    }
}

if (!function_exists('util_account_schema_value')) {
    function util_account_schema_value(array $account, array $gameData, array $field)
    {
        $key = (string)($field['key'] ?? '');
        $column = (string)($field['db_column'] ?? '');
        if ($column !== '' && array_key_exists($column, $account)) return $account[$column];
        if ($key !== '' && array_key_exists($key, $gameData)) return $gameData[$key];
        if ($key !== '' && array_key_exists($key, $account)) return $account[$key];
        return $field['default'] ?? null;
    }
}

if (!function_exists('util_account_platform_icon_files')) {
    function util_account_platform_icon_files($platform): array
    {
        $rawValues = is_array($platform) ? $platform : preg_split('/\s*(?:,|\||\/)\s*/', trim((string)$platform), -1, PREG_SPLIT_NO_EMPTY);
        $files = [];
        $add = static function($file) use (&$files) { if ($file && !in_array($file, $files, true)) $files[] = $file; };
        foreach ($rawValues as $raw) {
            $label = strtolower(trim((string)$raw));
            $value = str_replace(['.', '-', '_', ' '], '', $label);
            if ($label === '') continue;
            if (strpos($label, 'all platforms') !== false || $value === 'allplatforms') { foreach (['pc.webp','playstation.webp','xbox.webp','battlenet.webp','steam.webp','switch.webp','android.webp','ios.webp'] as $f) $add($f); continue; }
            if (strpos($value, 'playstation') !== false || preg_match('/\bps[345]?\b/', $label)) { $add('playstation.webp'); continue; }
            if (strpos($value, 'xbox') !== false) { $add('xbox.webp'); continue; }
            if (strpos($value, 'battle') !== false || strpos($value, 'bnet') !== false) { $add('battlenet.webp'); continue; }
            if (strpos($value, 'steam') !== false) { $add('steam.webp'); continue; }
            if (strpos($value, 'switch') !== false || strpos($value, 'nintendo') !== false) { $add('switch.webp'); continue; }
            if (strpos($value, 'android') !== false) { $add('android.webp'); continue; }
            if ($value === 'ios' || strpos($value, 'iphone') !== false || strpos($value, 'ipad') !== false || strpos($value, 'apple') !== false) { $add('ios.webp'); continue; }
            if (strpos($value, 'pc') !== false || strpos($value, 'gamepass') !== false || strpos($value, 'windows') !== false) { $add('pc.webp'); continue; }
        }
        return $files;
    }
}

if (!function_exists('util_account_platform_icons_html')) {
    function util_account_platform_icons_html($platform, string $class = 'account-platform-icon'): string
    {
        $files = util_account_platform_icon_files($platform);
        if (empty($files)) return '';
        $base = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/platforms/';
        $html = '<span class="account-platform-icons">';
        foreach ($files as $file) {
            $html .= '<img src="' . htmlspecialchars($base . $file, ENT_QUOTES, 'UTF-8') . '" alt="" loading="lazy" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';
        }
        $html .= '</span>';
        return $html;
    }
}

if (!function_exists('util_account_format_schema_value')) {
    function util_account_format_schema_value($value): string
    {
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if (is_array($value)) return implode(', ', array_values(array_filter(array_map('strval', $value), static fn($v) => trim($v) !== '')));
        return trim((string)$value);
    }
}


if (!function_exists('util_account_schema_input_name')) {
    function util_account_schema_input_name(string $key, bool $multiple = false): string
    {
        // Prefix dynamic account attributes so they never conflict with fixed columns
        // like level, server, rank, title, etc.
        return 'schema_' . $key . ($multiple ? '[]' : '');
    }
}

if (!function_exists('util_render_account_upload_fields')) {
    function util_render_account_upload_fields(string $game): string
    {
        $fields = util_account_schema_fields($game, 'show_on_upload');
        if (empty($fields)) return '';
        $html = '<div class="oc-section-label"><i class="fa-solid fa-sliders"></i> Account Attributes</div><div class="row g-2 mb-3">';
        foreach ($fields as $field) {
            $key = (string)($field['key'] ?? '');
            if ($key === '') continue;
            $label = htmlspecialchars((string)($field['label'] ?? ucwords(str_replace('_',' ', $key))), ENT_QUOTES, 'UTF-8');
            $type = (string)($field['type'] ?? 'text');
            $required = !empty($field['required']) ? ' required' : '';
            $col = (string)($field['col'] ?? ($type === 'multiselect' ? 'col-12' : 'col-md-6'));
            $min = isset($field['min']) ? ' min="' . htmlspecialchars((string)$field['min'], ENT_QUOTES, 'UTF-8') . '"' : '';
            $max = isset($field['max']) ? ' max="' . htmlspecialchars((string)$field['max'], ENT_QUOTES, 'UTF-8') . '"' : '';
            $placeholder = isset($field['placeholder']) ? ' placeholder="' . htmlspecialchars((string)$field['placeholder'], ENT_QUOTES, 'UTF-8') . '"' : '';
            $html .= '<div class="' . htmlspecialchars($col, ENT_QUOTES, 'UTF-8') . '"><label class="form-label">' . $label . (!empty($field['required']) ? ' <span class="oc-required">*</span>' : '') . '</label>';
            if ($type === 'select' || $type === 'multiselect') {
                $name = util_account_schema_input_name($key, $type === 'multiselect');
                $multiple = $type === 'multiselect' ? ' multiple' : '';
                $html .= '<select class="form-select" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"' . $multiple . $required . ' data-placeholder="Select ' . $label . '">';
                if ($type !== 'multiselect') $html .= '<option value="">Select ' . $label . '</option>';
                foreach (($field['options'] ?? []) as $opt) {
                    $optVal = is_array($opt) ? (string)($opt['value'] ?? $opt['label'] ?? '') : (string)$opt;
                    $optLab = is_array($opt) ? (string)($opt['label'] ?? $optVal) : (string)$opt;
                    $html .= '<option value="' . htmlspecialchars($optVal, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($optLab, ENT_QUOTES, 'UTF-8') . '</option>';
                }
                $html .= '</select>';
            } elseif ($type === 'checkbox') {
                $html .= '<div class="oc-toggle-row" style="margin-top:6px;"><label class="switch"><input type="hidden" name="' . htmlspecialchars(util_account_schema_input_name($key), ENT_QUOTES, 'UTF-8') . '" value="0"><input type="checkbox" role="switch" name="' . htmlspecialchars(util_account_schema_input_name($key), ENT_QUOTES, 'UTF-8') . '" value="1"><span class="slider"></span></label><span style="font-size:.82rem;color:rgba(255,255,255,.5);">' . $label . '</span></div>';
            } elseif ($type === 'textarea') {
                $html .= '<textarea class="form-control" name="' . htmlspecialchars(util_account_schema_input_name($key), ENT_QUOTES, 'UTF-8') . '"' . $required . $placeholder . '></textarea>';
            } else {
                $inputType = $type === 'number' ? 'number' : 'text';
                $html .= '<input type="' . $inputType . '" class="form-control" name="' . htmlspecialchars(util_account_schema_input_name($key), ENT_QUOTES, 'UTF-8') . '"' . $min . $max . $required . $placeholder . '>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('util_collect_account_schema_data')) {
    function util_collect_account_schema_data(string $game, array $post): array
    {
        $gameData = [];
        foreach (util_account_schema_fields($game, 'show_on_upload') as $field) {
            $key = (string)($field['key'] ?? '');
            if ($key === '') continue;
            $type = (string)($field['type'] ?? 'text');
            $postKey = 'schema_' . $key;
            // Backwards compatible: old forms posted plain keys; new forms post schema_* keys.
            $sourceKey = array_key_exists($postKey, $post) ? $postKey : $key;
            if ($type === 'checkbox') {
                $gameData[$key] = !empty($post[$sourceKey]) ? 1 : 0;
            } elseif ($type === 'multiselect') {
                $vals = $post[$sourceKey] ?? [];
                if (!is_array($vals)) $vals = [$vals];
                $gameData[$key] = array_values(array_filter(array_map('esc', $vals), static fn($v) => $v !== ''));
            } elseif ($type === 'number') {
                $gameData[$key] = (isset($post[$sourceKey]) && $post[$sourceKey] !== '') ? (int)$post[$sourceKey] : null;
            } else {
                $gameData[$key] = esc($post[$sourceKey] ?? '');
            }
        }
        return $gameData;
    }
}


function util_load_server_select($select = null, $game = 'league-of-legends')
{
    $val_servers = [
        'eu'  => 'Europe',
        'na'  => 'North America',
        'sea' => 'Southeast Asia',
        'me'  => 'Middle East',
        'vn'  => 'Vietnam',
        'ph'  => 'Philippines',
        'sg'  => 'Singapore',
        'th'  => 'Thailand',
        'tw'  => 'Taiwan',
     ];
    $lol_servers = [
        'euw' => 'EU-West',
        'eune' => 'EU-Nordic & East',
        'na' => 'North America',
        'br' => 'Brazil',
        'lan' => 'Latin America North',
        'las' => 'Latin America South',
        'oce' => 'Oceania',
        'ru' => 'Russia',
        'tr' => 'Turkey',
        'jp' => 'Japan',
        'kr' => 'Korea',
        'pbe' => 'PBE',
        'me' => 'Middle East',
        'vn' => 'Vietnam',
        'ph' => 'Philippines',
        'sg' => 'Singapore',
        'th' => 'Thailand',
        'tw' => 'Taiwan',
    ];
    $html = '';
    // Normalize game key. LoL, LoL Classic and TFT all use Riot/LoL region servers;
    // only Valorant uses the Valorant server list. The views call this with 'lol',
    // 'lol_classic', 'tft' or 'val', so match against those (not only 'league-of-legends').
    $gameKey = strtolower((string) $game);
    $isValorant = in_array($gameKey, ['val', 'valorant'], true);
    if (!$isValorant) {
        foreach ($lol_servers as $key => $server) {
            if ($key == $select) {
                $html .= '<option value="' . $key . '" selected="">' . $server . '</option>';
            } else {
                $html .= '<option value="' . $key . '">' . $server . '</option>';
            }
        }
    } else {
        foreach ($val_servers as $key => $server) {
            if ($key == $select) {
                $html .= '<option value="' . $key . '" selected="">' . $server . '</option>';
            } else {
                $html .= '<option value="' . $key . '">' . $server . '</option>';
            }
        }
    }
    return $html;
}


function util_load_lol_tier_select($start_tier = 0, $end_tier = 10, $selected = null)
{
    $tiers = [
        0 => 'Unranked',
        1 => 'Iron',
        2 => 'Bronze',
        3 => 'Silver',
        4 => 'Gold',
        5 => 'Platinum',
        6 => 'Emerald',
        7 => 'Diamond',
        8 => 'Master',
        9 => 'Grandmaster',
        10 => 'Challenger',
    ];

    $html = '';
    for ($i = $start_tier; $i <= $end_tier; $i++) {
        if ($i == $selected) {
            $html .= '<option value="' . $i . '" selected="">' . $tiers[$i] . '</option>';
        } else {
            $html .= '<option value="' . $i . '">' . $tiers[$i] . '</option>';
        }
    }

    return $html;
}

function util_load_lol_arena_select($start_tier = 0, $end_tier = 10, $selected = null)
{
    $tiers = [
        1 => 'Wood',
        2 => 'Bronze',
        3 => 'Silver',
        4 => 'Gold',
        5 => 'Gladiator',
    ];

    $html = '';
    for ($i = $start_tier; $i <= $end_tier; $i++) {
        if ($i == $selected) {
            $html .= '<option value="' . $i . '" selected="">' . $tiers[$i] . '</option>';
        } else {
            $html .= '<option value="' . $i . '">' . $tiers[$i] . '</option>';
        }
    }
    return $html;
}
function util_load_lol_level_select($start_tier = 0, $end_tier = 10, $selected = null)
{
    $tiers = [
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4',
        5 => '5',
        6 => '6',
        7 => '7',
        8 => '8',
        9 => '9',
        10 => '10',
    ];

    $html = '';
    for ($i = $start_tier; $i <= $end_tier; $i++) {
        if ($i == $selected) {
            $html .= '<option value="' . $i . '" selected="">' . $tiers[$i] . '</option>';
        } else {
            $html .= '<option value="' . $i . '">' . $tiers[$i] . '</option>';
        }
    }
    return $html;
}

function util_load_val_tier_select($start_tier = 0, $end_tier = 10, $selected = null)
{
    $tiers = [
        0 => 'Unranked',
        1 => 'Iron',
        2 => 'Bronze',
        3 => 'Silver',
        4 => 'Gold',
        5 => 'Platinum',
        6 => 'Diamond',
        7 => 'Ascended',
        8 => 'Immortal',
        9 => 'Radiant',
    ];
    $html = '';
    for ($i = $start_tier; $i <= $end_tier; $i++) {
        if ($i == $selected) {
            $html .= '<option value="' . $i . '" selected="">' . $tiers[$i] . '</option>';
        } else {
            $html .= '<option value="' . $i . '">' . $tiers[$i] . '</option>';
        }
    }
    return $html;
}


function util_load_lol_division_select($selected = null)
{

    $divisions = [
        1 => 'IV',
        2 => 'III',
        3 => 'II',
        4 => 'I'
    ];

    $html = '';
    foreach ($divisions as $i => $div) {
        if ($i == $selected) {
            $html .= '<option value="' . $i . '" selected="">' . $div . '</option>';
        } else {
            $html .= '<option value="' . $i . '">' . $div . '</option>';
        }
    }
    return $html;
}

function util_load_val_division_select($selected = null)
{

    $divisions = [
        1 => 'I',
        2 => 'II',
        3 => 'III'
    ];

    $html = '';
    foreach ($divisions as $i => $div) {
        if ($i == $selected) {
            $html .= '<option value="' . $i . '" selected="">' . $div . '</option>';
        } else {
            $html .= '<option value="' . $i . '">' . $div . '</option>';
        }
    }
    return $html;
}

function util_load_booster_ranks($selected = null)
{
    $ranks = db_get_rows('booster_ranks');
    $html = '';
    foreach ($ranks as $rank) {
        if ($rank['id'] == $selected) {
            $html .= '<option value="' . $rank['id'] . '" selected="">' . $rank['name'] . ' - ' . $rank['cut'] . '%</option>';
        } else {
            $html .= '<option value="' . $rank['id'] . '">' . $rank['name'] . ' - ' . $rank['cut'] . '%</option>';
        }
    }

    return $html;
}

/**
 * Converts simple Markdown pipe tables into HTML tables.
 * Supports blocks like:
 * | A | B |
 * |---|---|
 * | 1 | 2 |
 */
function markdown_table_to_html(string $text): string
{
    $lines = preg_split("/\r\n|\n|\r/", (string)$text);
    $out = [];
    $i = 0;

    while ($i < count($lines)) {
        $line = trim((string)$lines[$i]);

        // Detect header row with pipes and a valid separator row below it
        if ($line !== '' && str_contains($line, '|') && ($i + 1) < count($lines)) {
            $sep = trim((string)$lines[$i + 1]);

            // Separator like: | --- | --- | or ---|--- (with optional colons)
            if (preg_match('/^\|?\s*:?-{3,}:?\s*(\|\s*:?-{3,}:?\s*)+\|?$/', $sep)) {
                $headerCells = array_map('trim', explode('|', trim($line, '|')));
                $headerCells = array_values(array_filter($headerCells, fn($c) => $c !== ''));

                $rows = [];
                $i += 2; // skip header + separator

                while ($i < count($lines)) {
                    $rowLine = trim((string)$lines[$i]);
                    if ($rowLine === '' || !str_contains($rowLine, '|')) {
                        break;
                    }

                    $cells = array_map('trim', explode('|', trim($rowLine, '|')));
                    $rows[] = $cells;
                    $i++;
                }

                $html = '<div class="table-responsive my-4"><table class="table align-middle">';
                if (!empty($headerCells)) {
                    $html .= '<thead><tr>';
                    foreach ($headerCells as $c) {
                        $html .= '<th>' . sanitize_rich_text((string)$c) . '</th>';
                    }
                    $html .= '</tr></thead>';
                }

                $html .= '<tbody>';
                foreach ($rows as $r) {
                    $html .= '<tr>';
                    foreach ($r as $c) {
                        $html .= '<td>' . sanitize_rich_text((string)$c) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';

                $out[] = $html;
                continue;
            }
        }

        $out[] = (string)$lines[$i];
        $i++;
    }

    return implode("\n", $out);
}

function sanitize_rich_text(string $html): string
{
    // Erlaube nur diese Tags (anpassen, wenn du mehr brauchst)
    $allowed = '<b><strong><i><em><u><s><br><a><code><mark><span><sub><sup><p><ul><ol><li><table><thead><tbody><tfoot><tr><th><td>';
    $clean = strip_tags($html, $allowed);

    // Links absichern (nofollow + noopener)
    $clean = preg_replace_callback('/<a\s+[^>]*href=("|\')(.*?)\1[^>]*>/i', function ($m) {
        $href = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
        return '<a href="' . $href . '" target="_blank" rel="nofollow noopener">';
    }, $clean);

    return $clean;
}


function parse_article_content($content)
{
    $data = json_decode($content, true);
    if (!$data || empty($data['blocks']) || !is_array($data['blocks'])) {
        return '';
    }

    $html = '<div class="article-content">';

    for ($i = 0; $i < count($data['blocks']); $i++) {
        $block = $data['blocks'][$i];
        $type = $block['type'] ?? '';
        $d = $block['data'] ?? [];

        switch ($type) {

            case 'header':
                $level = (int) ($d['level'] ?? 2);
                $level = max(1, min(6, $level));
                $text = sanitize_rich_text($d['text'] ?? '');

                // bessere Abstände + Hierarchie
                $classes = match ($level) {
                    1 => 'mt-0 mb-3 fw-semibold',
                    2 => 'mt-5 mb-3 fw-semibold',
                    3 => 'mt-4 mb-2 fw-semibold',
                    default => 'mt-3 mb-2 fw-semibold',
                };

                $html .= "<h{$level} class=\"{$classes}\">{$text}</h{$level}>";
                break;

            case 'paragraph':
                // Paragraph may contain Markdown table rows split into multiple paragraph blocks.
                $raw = (string) ($d['text'] ?? '');
                $plain = trim(strip_tags($raw));

                // Detect a Markdown table header row (contains pipes) and look ahead for separator row.
                if (str_contains($plain, '|') && ($i + 1) < count($data['blocks'])) {
                    $next = $data['blocks'][$i + 1] ?? [];
                    $nextType = $next['type'] ?? '';
                    $nextRaw = (string) (($next['data']['text'] ?? ''));

                    if ($nextType === 'paragraph') {
                        $nextPlain = trim(strip_tags($nextRaw));

                        // If the next paragraph is a separator like | --- | --- |
                        if (preg_match('/^\|?\s*:?-{3,}:?\s*(\|\s*:?-{3,}:?\s*)+\|?$/', $nextPlain)) {
                            // Collect consecutive paragraph blocks that belong to the table
                            $tableLines = [];
                            $j = $i;

                            while ($j < count($data['blocks'])) {
                                $b = $data['blocks'][$j];
                                $bt = $b['type'] ?? '';
                                if ($bt !== 'paragraph') break;

                                $tRaw = (string) (($b['data']['text'] ?? ''));
                                $tPlain = trim(strip_tags($tRaw));

                                if ($tPlain === '') break;
                                if (!str_contains($tPlain, '|')) break;

                                $tableLines[] = $tPlain;
                                $j++;
                            }

                            $tableText = implode("\n", $tableLines);
                            $tableHtml = markdown_table_to_html($tableText);

                            if (str_contains($tableHtml, '<table')) {
                                // Insert table HTML directly (not wrapped in <p>)
                                $html .= $tableHtml;
                                // Skip processed blocks
                                $i = $j - 1;
                                break;
                            }
                        }
                    }
                }

                // Normal paragraph
                $text = $raw;
                $text = markdown_table_to_html($text);
                $text = sanitize_rich_text($text);
                if (trim(strip_tags($text)) !== '') {
                    $html .= "<p class=\"mb-3\">{$text}</p>";
                }
                break;

            case 'image':
                // EditorJS image tool nutzt je nach Setup: url / file.url
                $image_url = $d['url'] ?? ($d['file']['url'] ?? '');
                $image_url = htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8');

                $caption = sanitize_rich_text($d['caption'] ?? '');
                $alt = trim(strip_tags($caption));
                $alt = $alt !== '' ? htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') : 'Article image';

                // Kein .ratio um Bild + Caption; lieber <figure>
                $html .= '<figure class="my-4">';
                $html .= "<img src=\"{$image_url}\" alt=\"{$alt}\" class=\"img-fluid rounded\" loading=\"lazy\" decoding=\"async\">";
                if (trim(strip_tags($caption)) !== '') {
                    $html .= "<figcaption class=\"mt-2 small text-muted\">{$caption}</figcaption>";
                }
                $html .= '</figure>';
                break;

            case 'embed':
                $embedUrl = htmlspecialchars($d['embed'] ?? '', ENT_QUOTES, 'UTF-8');
                $caption = sanitize_rich_text($d['caption'] ?? '');

                // Ratio nur fürs iframe, Caption darunter
                $html .= '<div class="my-4">';
                $html .= "<div class=\"ratio ratio-16x9\"><iframe src=\"{$embedUrl}\" allowfullscreen loading=\"lazy\"></iframe></div>";
                if (trim(strip_tags($caption)) !== '') {
                    $html .= "<div class=\"mt-2 small text-muted\">{$caption}</div>";
                }
                $html .= '</div>';
                break;

            case 'list':
                $style = (($d['style'] ?? '') === 'ordered') ? 'ol' : 'ul';
                $items = (isset($d['items']) && is_array($d['items'])) ? $d['items'] : [];

                $html .= "<{$style} class=\"mb-3 ps-4\">";
                foreach ($items as $item) {
                    $item = sanitize_rich_text((string) $item);
                    $html .= "<li class=\"mb-1\">{$item}</li>";
                }
                $html .= "</{$style}>";
                break;

            case 'table':
                // Falls du EditorJS Table Tool nutzt: data.content = rows[]
                $rows = $d['content'] ?? [];
                if (is_array($rows) && count($rows)) {
                    $html .= '<div class="table-responsive my-4">';
                    $html .= '<table class="table align-middle">';
                    foreach ($rows as $rIndex => $row) {
                        if (!is_array($row))
                            continue;
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $cell = sanitize_rich_text((string) $cell);
                            $tag = ($rIndex === 0 && !empty($d['withHeadings'])) ? 'th' : 'td';
                            $html .= "<{$tag}>{$cell}</{$tag}>";
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</table></div>';
                }
                break;

            // Optional: delimiter / quote / code etc.
        }
    }

    $html .= '</div>';
    return $html;
}


function get_exchange_rate()
{
    $exchange_rate_archive = json_decode(file_get_contents(SYS_PATH . '/public/uploads/private/exchange-rate/exchange_rate.json'), true);
    $latest = end($exchange_rate_archive);
    return $latest['exchange_rate'];
}

/**
 * Get USD->EUR rate for a specific order.
 * - EUR orders: 1.0
 * - USD orders: prefer stable per-order rate derived from stored cents (price_eur / price)
 * - Fallback: invert latest EUR->USD rate from exchange_rate.json
 */
function util_get_usd_to_eur_rate_for_order($order)
{
    $currency = strtoupper($order['currency'] ?? 'EUR');
    if ($currency === 'EUR') {
        return 1.0;
    }

    $price_order = (int)($order['price'] ?? 0);      // cents in order currency
    $price_eur   = (int)($order['price_eur'] ?? 0);  // cents in EUR

    if ($price_order > 0 && $price_eur > 0) {
        return $price_eur / $price_order; // USD->EUR factor used by this order
    }

    // Fallback: latest archived EUR->USD rate, invert to USD->EUR
    $eur_to_usd = (float)get_exchange_rate();
    if ($eur_to_usd <= 0) {
        return 1.0;
    }
    return 1.0 / $eur_to_usd;
}

/** Convert cents in order currency -> EUR cents, using order-specific FX. */
function util_order_cents_to_eur_cents($order, $order_cents)
{
    $currency = strtoupper($order['currency'] ?? 'EUR');
    $order_cents = (int)$order_cents;

    if ($currency === 'EUR') {
        return $order_cents;
    }

    $fx = util_get_usd_to_eur_rate_for_order($order); // USD->EUR
    return (int)round($order_cents * $fx);
}

/** Convert EUR cents -> cents in order currency, using order-specific FX. */
function util_eur_cents_to_order_cents($order, $eur_cents)
{
    $currency = strtoupper($order['currency'] ?? 'EUR');
    $eur_cents = (int)$eur_cents;

    if ($currency === 'EUR') {
        return $eur_cents;
    }

    $fx = util_get_usd_to_eur_rate_for_order($order); // USD->EUR
    if ($fx <= 0) {
        return $eur_cents;
    }

    return (int)round($eur_cents / $fx);
}

function util_currency_symbol($currency)
{
    $c = strtoupper((string)$currency);
    if ($c === 'USD') return '$';
    return '€';
}


function util_get_lol_rank($rank_id)
{
    $ranks = [
        0 => 'Unranked',
        1 => 'Iron',
        2 => 'Bronze',
        3 => 'Silver',
        4 => 'Gold',
        5 => 'Platinum',
        6 => 'Emerald',
        7 => 'Diamond',
        8 => 'Master',
        9 => 'Grandmaster',
        10 => 'Challenger',
    ];

    return $ranks[$rank_id] ?? 'Unranked';
}

// ── Seller sales statistics ───────────────────────────────────────────────
if (!function_exists('seller_stats_table_exists')) {
    function seller_stats_table_exists(): bool
    {
        global $db;
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        try {
            $exists = !empty($db->row("SHOW TABLES LIKE 'seller_stats'"));
        } catch (\Throwable $e) {
            $exists = false;
        }

        return $exists;
    }
}

if (!function_exists('get_seller_total_sales_subquery')) {
    function get_seller_total_sales_subquery(string $seller_table_alias = 's'): string
    {
        $accountSales = "COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = {$seller_table_alias}.id AND sa2.sold = 1), 0)";
        $itemSales = "COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = {$seller_table_alias}.id), 0)";
        $topupSales = "COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = {$seller_table_alias}.id), 0)";
        $digitalSales = "COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = {$seller_table_alias}.id), 0)";
        $adminSales = "CASE
                WHEN {$seller_table_alias}.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                WHEN {$seller_table_alias}.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                ELSE 0
              END";

        if (seller_stats_table_exists()) {
            return "(
                {$accountSales}
                + {$itemSales}
                + {$topupSales}
                + {$digitalSales}
                + GREATEST({$adminSales}, COALESCE((SELECT ss.admin_alias_sales FROM seller_stats ss WHERE ss.seller_id = {$seller_table_alias}.id LIMIT 1), 0))
            )";
        }

        return "({$accountSales} + {$itemSales} + {$topupSales} + {$digitalSales} + {$adminSales})";
    }
}

// ── Valorant Ranks ─────────────────────────────────────────────────────────
function util_get_val_rank(int $rank_id): string
{
    $ranks = [
        0 => 'Unranked',
        1 => 'Iron',
        2 => 'Bronze',
        3 => 'Silver',
        4 => 'Gold',
        5 => 'Platinum',
        6 => 'Diamond',
        7 => 'Ascendant',
        8 => 'Immortal',
        9 => 'Radiant',
    ];
    return $ranks[$rank_id] ?? 'Unknown';
}

function util_val_rank_img(string $size, int $rank_id): string
{
    // Images are stored as 0.png, 1.png ... 9.png
    return ASSET_URL . "/public/assets/core/main/img/val/ranks/{$size}/{$rank_id}.png";
}

// ── Universal rank helper (cross-game) ────────────────────────────────────
// Returns the rank name for any game based on the universal `rank` field.
function util_get_rank_label(string $game, int $rank): string
{
    $game = strtolower(trim($game));
    if ($game === 'valorant') return util_get_val_rank($rank);
    return util_get_lol_rank($rank); // lol default
}

// Returns rank image URL for any game
function util_get_rank_img(string $game, string $size, int $rank): string
{
    $_map = ['league-of-legends'=>'lol','leagu'=>'lol','valorant'=>'val','valor'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft'];
    $game = $_map[$game] ?? $game;
    switch ($game) {
        case 'val':
            return ASSET_URL . "/core/main/img/val/ranks/mini/{$rank}.png";
        case 'tft':
        case 'lol':
        default:
            return ASSET_URL . "/core/main/img/lol/ranks/{$size}/{$rank}.png";
    }
}

// Returns rank options array for any game (for dropdowns)
function util_get_rank_options(string $game): array
{
    $game = strtolower(trim($game));
    if ($game === 'valorant') {
        return [
            0 => 'Unranked', 1 => 'Iron', 2 => 'Bronze', 3 => 'Silver',
            4 => 'Gold', 5 => 'Platinum', 6 => 'Diamond',
            7 => 'Ascendant', 8 => 'Immortal', 9 => 'Radiant',
        ];
    }
    // lol default
    return [
        0 => 'Unranked', 1 => 'Iron', 2 => 'Bronze', 3 => 'Silver',
        4 => 'Gold', 5 => 'Platinum', 6 => 'Emerald', 7 => 'Diamond',
        8 => 'Master', 9 => 'Grandmaster', 10 => 'Challenger',
    ];
}

// function util_format_boost_icons($game, $type, $data)
// {
//     $html = '';

//     if ($game === 'league-of-legends') {
//         switch ($type) {
//             case 'win':
//                 $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="' . BASE_URL . '/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . util_get_lol_rank($data['start_tier'] ?? '0') . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Wins <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
//                 break;
//             case 'placement':
//                 $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="' . BASE_URL . '/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . util_get_lol_rank($data['start_tier'] ?? '0') . ' ' . $data['start_division'] . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Matches <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
//                 break;
//             case 'normal':
//                 $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="' . BASE_URL . '/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . $data['queue_type'] . '" class="icon">' . util_format_default_type($data['queue_type']) . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Matches <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
//                 break;
//             case 'coaching':
//                 $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="' . BASE_URL . '/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . util_get_lol_rank($data['start_tier'] ?? '0') . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Hours <span class="fw-bold"> ' . $data['hours'] . '</span></p></div></div>';
//                 break;
//             case 'rank':
//                 $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="' . BASE_URL . '/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . util_get_lol_rank($data['start_tier'] ?? '0') . ' ' . $data['start_division'] . '</div> <i class="fa-duotone fa-solid fa-arrow-right"></i> <div class="d-flex align-items-center justify-content-between">' .
//                     '<img src="' . BASE_URL . '/public/assets/core/main/img/lol/ranks/max/' . ($data['end_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['end_tier'] ?? '0') . '" class="icon">' . util_get_lol_rank($data['end_tier'] ?? '0') . ' ' . $data['end_division'] . '</div></div>';
//                 break;
//             case 'match':
//                 $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="' . BASE_URL . '/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . ($data['start_tier'] ?? '0') . '" class="icon">' . util_get_lol_rank($data['start_tier'] ?? '0') . ' ' . $data['start_division'] . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Matches <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
//                 break;
//         }
//     }

//     return $html;
// }

function util_format_boost_icons($game, $type, $data)
{
    $html = '';

    if ($game === 'league-of-legends') {
        switch ($type) {
            case 'win':
                $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Wins <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
                break;
            case 'placement':
                $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Matches <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
                break;
            case 'normal':
                $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . $data['queue_type'] . '" class="icon">' . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Matches <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
                break;
            case 'coaching':
                $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Hours <span class="fw-bold"> ' . $data['hours'] . '</span></p></div></div>';
                break;
            case 'duo-pass':
                $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Hours <span class="fw-bold"> ' . ($data['hours'] ?? 3) . '</span></p></div></div>';
                break;
            case 'rank':
                $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '</div> <i class="fa-duotone fa-solid fa-arrow-right"></i> <div class="d-flex align-items-center justify-content-between">' .
                    '<img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['end_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['end_tier'] ?? '0') . '" class="icon">' . '</div></div>';
                break;
            case 'match':
                $html .= '<div class="boost_detail"><div class="d-flex align-items-center justify-content-between"><img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . ($data['start_tier'] ?? '0') . '" class="icon">' . '</div><div class="d-flex align-items-center justify-content-between"><p class="mb-0">Matches <span class="fw-bold"> ' . $data['matches'] . '</span></p></div></div>';
                break;
        }
    }

    return $html;
}

function util_format_boost_icons_new($game, $type, $data)
{
    $html = '';

    if ($game === 'league-of-legends') {
        switch ($type) {
            case 'win':
                $html .= '<img src="/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '<p>Wins <span> ' . $data['matches'] . '</span></p>';
                break;
            case 'placement':
                $html .= '<img src="/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '<p>Matches <span> ' . $data['matches'] . '</span></p>';
                break;
            case 'normal':
                $html .= '<img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . $data['queue_type'] . '" class="icon">' . '<p>Matches <span> ' . $data['matches'] . '</span></p>';
                break;
            case 'coaching':
                $html .= '<img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '<p>Hours <span> ' . $data['hours'] . '</span></p>';
                break;
            case 'duo-pass':
                $html .= '<img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '<p>Hours <span> ' . ($data['hours'] ?? 3) . '</span></p>';
                break;
            case 'rank':
                $html .= '<img src="/public/assets/core/main/img/lol/ranks/max/' . $data['start_tier'] . '.png" alt="' . util_get_lol_rank($data['start_tier'] ?? '0') . '" class="icon">' . '<i class="fa-duotone fa-solid fa-arrow-right"></i>' .
                    '<img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['end_tier'] ?? '0') . '.png" alt="' . util_get_lol_rank($data['end_tier'] ?? '0') . '" class="icon">';
                break;
            case 'match':
                $html .= '<img src="/public/assets/core/main/img/lol/ranks/max/' . ($data['start_tier'] ?? '0') . '.png" alt="' . ($data['start_tier'] ?? '0') . '" class="icon">' . '<p>Matches <span> ' . $data['matches'] . '</span></p>';
                break;
        }
    }

    return $html;
}



function seller_rank_table_exists(): bool
{
    global $db;
    static $exists = null;

    if ($exists !== null) {
        return $exists;
    }

    try {
        $row = $db->row("SHOW TABLES LIKE 'seller_ranks'");
        $exists = !empty($row);
    } catch (\Throwable $e) {
        $exists = false;
    }

    return $exists;
}

if (!function_exists('get_seller_service_sales')) {
    function get_seller_service_sales(int $seller_id, string $service): int
    {
        global $db;
        if ($seller_id <= 0 || empty($db)) return 0;

        $columnMap = [
            'accounts' => 'account_sales',
            'account' => 'account_sales',
            'items' => 'item_sales',
            'item' => 'item_sales',
            'topups' => 'topup_sales',
            'topup' => 'topup_sales',
            'digital_goods' => 'digital_good_sales',
            'digital-goods' => 'digital_good_sales',
            'digital' => 'digital_good_sales',
        ];
        $column = $columnMap[strtolower(trim($service))] ?? '';
        if ($column === '') return 0;

        if (function_exists('sync_seller_stats') && seller_stats_table_exists()) {
            try {
                sync_seller_stats($seller_id);
                return max(0, (int)($db->single(
                    "SELECT {$column} FROM seller_stats WHERE seller_id = ? LIMIT 1",
                    [$seller_id]
                ) ?: 0));
            } catch (Throwable $e) {}
        }

        try {
            if ($column === 'account_sales') {
                return max(0, (int)$db->single("SELECT COUNT(*) FROM selling_accounts WHERE seller_id = ? AND sold = 1 AND client_id IS NOT NULL", [$seller_id]));
            }
            if ($column === 'item_sales') {
                return max(0, (int)$db->single("SELECT COALESCE(SUM(sold_count),0) FROM selling_items WHERE seller_id = ?", [$seller_id]));
            }
            if ($column === 'topup_sales') {
                return max(0, (int)$db->single("SELECT COALESCE(SUM(sold_count),0) FROM selling_topups WHERE seller_id = ?", [$seller_id]));
            }
            if ($column === 'digital_good_sales') {
                return max(0, (int)$db->single("SELECT COALESCE(SUM(sold_count),0) FROM digital_goods WHERE seller_id = ?", [$seller_id]));
            }
        } catch (Throwable $e) {}
        return 0;
    }
}

if (!function_exists('sync_seller_stats')) {
    function sync_seller_stats(int $seller_id): int
    {
        global $db;
        if ($seller_id <= 0 || empty($db)) return 0;

        $stored = seller_stats_table_exists()
            ? ($db->row("SELECT * FROM seller_stats WHERE seller_id = ? LIMIT 1", $seller_id) ?: [])
            : [];

        // Resolve every service independently. A missing optional table must never
        // collapse the seller's complete statistic to zero.
        $accountSales = max(0, (int)($stored['account_sales'] ?? 0));
        $itemSales = max(0, (int)($stored['item_sales'] ?? 0));
        $topupSales = max(0, (int)($stored['topup_sales'] ?? 0));
        $digitalSales = max(0, (int)($stored['digital_good_sales'] ?? 0));
        try { $accountSales = max(0, (int)($db->single("SELECT COUNT(*) FROM selling_accounts WHERE seller_id = ? AND sold = 1 AND client_id IS NOT NULL", [$seller_id]) ?: 0)); } catch (Throwable $e) {}
        try { $itemSales = max(0, (int)($db->single("SELECT COALESCE(SUM(sold_count),0) FROM selling_items WHERE seller_id = ?", [$seller_id]) ?: 0)); } catch (Throwable $e) {}
        try { $topupSales = max(0, (int)($db->single("SELECT COALESCE(SUM(sold_count),0) FROM selling_topups WHERE seller_id = ?", [$seller_id]) ?: 0)); } catch (Throwable $e) {}
        try { $digitalSales = max(0, (int)($db->single("SELECT COALESCE(SUM(sold_count),0) FROM digital_goods WHERE seller_id = ?", [$seller_id]) ?: 0)); } catch (Throwable $e) {}

        $adminSales = 0;
        $adminAliases = [28 => 51, 1 => 2];
        if (!empty($adminAliases[$seller_id])) {
            try {
                $adminSales = (int)($db->single(
                    "SELECT COUNT(*) FROM accounts WHERE admin_id = ? AND status = 1",
                    [$adminAliases[$seller_id]]
                ) ?: 0);
            } catch (Throwable $e) {
                $adminSales = (int)($stored['admin_alias_sales'] ?? 0);
            }
        }
        $adminSales = max((int)($stored['admin_alias_sales'] ?? 0), $adminSales);
        $totalSales = $accountSales + $itemSales + $topupSales + $digitalSales + $adminSales;
        $statsChanged = $accountSales !== (int)($stored['account_sales'] ?? 0)
            || $itemSales !== (int)($stored['item_sales'] ?? 0)
            || $topupSales !== (int)($stored['topup_sales'] ?? 0)
            || $digitalSales !== (int)($stored['digital_good_sales'] ?? 0)
            || $adminSales !== (int)($stored['admin_alias_sales'] ?? 0)
            || $totalSales !== (int)($stored['total_sales'] ?? 0);

        if (seller_stats_table_exists()) {
            $db->run(
                "INSERT INTO seller_stats
                    (seller_id, account_sales, item_sales, topup_sales, digital_good_sales, admin_alias_sales, total_sales, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    account_sales = VALUES(account_sales),
                    item_sales = VALUES(item_sales),
                    topup_sales = VALUES(topup_sales),
                    digital_good_sales = VALUES(digital_good_sales),
                    admin_alias_sales = VALUES(admin_alias_sales),
                    total_sales = VALUES(total_sales),
                    updated_at = NOW()",
                $seller_id,
                $accountSales,
                $itemSales,
                $topupSales,
                $digitalSales,
                $adminSales,
                $totalSales
            );
        }

        if ($statsChanged) {
            $cacheBase = defined('SYS_PATH') ? rtrim((string)SYS_PATH, '/\\') : dirname(__DIR__, 2);
            $cacheDir = $cacheBase . '/public/uploads/private/page-cache';
            if (is_dir($cacheDir)) {
                foreach ((glob($cacheDir . '/public-*.html') ?: []) as $cacheFile) {
                    if (is_file($cacheFile)) @unlink($cacheFile);
                }
            }
        }

        return $totalSales;
    }
}

function get_seller_total_sales(int $seller_id): int
{
    global $db;

    if ($seller_id <= 0) {
        return 0;
    }

    try {
        return sync_seller_stats($seller_id);
    } catch (\Throwable $e) {
        try {
            if (seller_stats_table_exists()) {
                return max(0, (int)($db->single(
                    "SELECT total_sales FROM seller_stats WHERE seller_id = ? LIMIT 1",
                    [$seller_id]
                ) ?: 0));
            }
        } catch (\Throwable $ignored) {}
        return 0;
    }
}

function get_matching_seller_rank_by_sales(int $sales)
{
    global $db;

    if (!seller_rank_table_exists()) {
        return false;
    }

    try {
        $rank = $db->row(
            "SELECT *
             FROM seller_ranks
             WHERE min_sales <= ?
             ORDER BY min_sales DESC, id DESC
             LIMIT 1",
            $sales
        );

        if (!empty($rank)) {
            return $rank;
        }

        return $db->row(
            "SELECT *
             FROM seller_ranks
             ORDER BY COALESCE(sort_order, min_sales) ASC, min_sales ASC, id ASC
             LIMIT 1"
        );
    } catch (\Throwable $e) {
        return false;
    }
}

function process_seller_rank(int $seller_id): bool
{
    global $db;

    if ($seller_id <= 0 || !seller_rank_table_exists()) {
        return false;
    }

    $seller = db_get_row('sellers', ['id' => $seller_id]);
    if (empty($seller)) {
        return false;
    }

    $total_sales = get_seller_total_sales($seller_id);
    $rank = get_matching_seller_rank_by_sales($total_sales);

    if (empty($rank)) {
        return false;
    }

    $update_data = [
        'seller_rank_id' => (int)$rank['id'],
        'rank' => $rank['name'],
        'fee_percent' => $rank['fee_percent'] !== null && $rank['fee_percent'] !== ''
            ? (float)$rank['fee_percent']
            : (float)($seller['fee_percent'] ?? 15),
    ];

    if (isset($rank['icon']) && trim((string)$rank['icon']) !== '') {
        $update_data['icon'] = trim((string)$rank['icon']);
    }

    db_update_row('sellers', ['id' => $seller_id], $update_data);
    return true;
}

function process_all_seller_ranks(): void
{
    $sellers = db_get_rows('sellers', ['select' => 'id']);
    if (empty($sellers)) {
        return;
    }

    foreach ($sellers as $seller) {
        process_seller_rank((int)($seller['id'] ?? 0));
    }
}


function process_client_loyalty($client_id)
{
    $client = db_get_row('clients', ['id' => $client_id]);
    $orders = db_get_rows('orders', ['client_id' => $client_id, 'status' => ['n' => 'UNPAID'], 'order' => 'created_at,DESC']);

    if (empty($orders)) {
        return;
    }

    $loyalty_ranks = db_get_rows('loyalty_ranks');

    $total_spent = array_sum(array_column($orders, 'price')) / 100;

    usort($loyalty_ranks, function ($a, $b) {
        return $a['target_amount'] <=> $b['target_amount'];
    });

    $current_rank = null;
    foreach ($loyalty_ranks as $rank) {
        if ($total_spent >= $rank['target_amount']) {
            $current_rank = $rank['id'];
        }
    }

    if ($current_rank !== null && $client['loyalty_rank_id'] !== $current_rank) {
        db_update_row('clients', ['id' => $client_id], ['loyalty_rank_id' => $current_rank]);
    }
}

function process_cashback_points($client_id, $amount, $order, $coins_used = 0.00)
{
    $client = db_get_row('clients', ['id' => $client_id]);
    $loyalty_rank = db_get_row('loyalty_ranks', ['id' => $client['loyalty_rank_id']]);
    $cashback_percentage = $loyalty_rank['cashback'];

    $cashback_points = ($amount * $cashback_percentage) / 100;
    $cashback_points = round($cashback_points, 2);
    $new_points = floatval($client['points']) + $cashback_points;
    $new_points -= floatval($coins_used);
    $new_points = number_format($new_points, 2);

    db_update_row('clients', ['id' => $client_id], ['points' => round($new_points, 2)]);

    db_add_row('coins_history', [
        'client_id' => $client_id,
        'type' => 'increment',
        'amount' => $cashback_points,
        'reason' => '💸 Coins added (Order #' . $order . ')',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    if ($coins_used > 0) {
        db_add_row('coins_history', [
            'client_id' => $client_id,
            'type' => 'decrement',
            'amount' => round($coins_used, 2),
            'reason' => '🛒 Coins spent (Order #' . $order . ')',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

function process_cashback_return($client_id, $amount, $order, $coins_used = 0.00)
{
    $client = db_get_row('clients', ['id' => $client_id]);
    $loyalty_rank = db_get_row('loyalty_ranks', ['id' => $client['loyalty_rank_id']]);
    $cashback_percentage = $loyalty_rank['cashback'];

    $cashback_points = ($amount * $cashback_percentage) / 100;
    $cashback_points = round($cashback_points, 2);
    $new_points = floatval($client['points']) + floatval($coins_used);
    $new_points -= floatval($cashback_points);
    $new_points = number_format($new_points, 2);

    db_update_row('clients', ['id' => $client_id], ['points' => round(max(0, $new_points), 2)]);

    db_add_row('coins_history', [
        'client_id' => $client_id,
        'type' => 'decrement',
        'amount' => $cashback_points,
        'reason' => '🔄 Coins removed (Order #' . $order . ')',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    if ($coins_used > 0) {
        db_add_row('coins_history', [
            'client_id' => $client_id,
            'type' => 'increment',
            'amount' => round($coins_used, 2),
            'reason' => '🛒 Coins spent (Order #' . $order . ')',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

function process_cashback_deduction($client_id, $amount, $order)
{
    $client = db_get_row('clients', ['id' => $client_id]);
    $cashback_points = max(0, floatval($client['points']) - floatval($amount));

    db_update_row('clients', ['id' => $client_id], ['points' => round($cashback_points, 2)]);

    if ($amount > 0) {
        db_add_row('coins_history', [
            'client_id' => $client_id,
            'type' => 'decrement',
            'amount' => $amount,
            'reason' => '🔄 Coins removed (Order #' . $order . ')',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

function get_loyalty_target_price($id)
{
    $loyalty_rank = db_get_row('loyalty_ranks', ['id' => $id]);

    return $loyalty_rank['target_amount'];
}

function get_user_cashback_percentage($id)
{
    $loyalty_rank = db_get_row('loyalty_ranks', ['id' => $id]);

    return $loyalty_rank['cashback'];
}

function make_links_clickable($text)
{
    return preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="text-primary">$1</a>', $text);
}

function getDMChannelId($botToken, $userId)
{
    // Every DM costs two round trips to Discord: open the channel, then post to it.
    // The channel id for a (bot, user) pair never changes, so the first call is
    // pure overhead on every repeat DM — and it ran while the admin waited.
    // Caching it removes one of the two calls for everyone who has been messaged
    // before, without any caller having to change.
    $cacheKey = 'lb_dm_ch_' . md5((string)$botToken . '|' . (string)$userId);
    $cacheFile = sys_get_temp_dir() . '/' . $cacheKey;
    $cacheTtl = 30 * 24 * 3600;

    if (function_exists('apcu_fetch')) {
        $cached = apcu_fetch($cacheKey, $ok);
        if ($ok && is_string($cached) && $cached !== '') {
            return $cached;
        }
    } elseif (is_file($cacheFile) && (time() - (int)@filemtime($cacheFile)) < $cacheTtl) {
        $cached = trim((string)@file_get_contents($cacheFile));
        if ($cached !== '') {
            return $cached;
        }
    }

    $url = "https://discord.com/api/v10/users/@me/channels";
    $data = json_encode(["recipient_id" => $userId]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bot $botToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Most callers run this inside a normal request while the user waits. Discord
    // answers well within a second in practice, so 20s only ever meant "hang the
    // page for 20s when Discord is having a bad day" — and this is always paired
    // with a second call, doubling the wait. 8s is still ample headroom.
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_USERAGENT, 'LoLBoostGG (https://lolboost.gg, 1.0)');

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && is_array($result) && !empty($result['id'])) {
        $channelId = (string)$result['id'];
        if (function_exists('apcu_store')) {
            @apcu_store($cacheKey, $channelId, $cacheTtl);
        } else {
            @file_put_contents($cacheFile, $channelId, LOCK_EX);
        }
        return $channelId;
    }
    error_log('Discord DM channel failed: HTTP ' . $httpCode . ($curlError !== '' ? ' - ' . $curlError : '') . ' response=' . substr((string)$response, 0, 300));
    return null;
}

function sendEmbedDM($botToken, $channelId, $data)
{
    $url = "https://discord.com/api/v10/channels/$channelId/messages";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bot $botToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // See getDMChannelId(): kept low because this runs while the user waits.
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_USERAGENT, 'LoLBoostGG (https://lolboost.gg, 1.0)');

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $result = json_decode((string)$response, true);
    $ok = $httpCode >= 200 && $httpCode < 300 && is_array($result) && !empty($result['id']);
    if (!$ok) {
        error_log('Discord DM send failed: HTTP ' . $httpCode . ($curlError !== '' ? ' - ' . $curlError : '') . ' response=' . substr((string)$response, 0, 300));
    }
    return $ok;
}

/**
 * Sends a sale notification as a Discord DM to a seller.
 *
 * The admin sale webhooks only reach the staff channel — this is the seller's own
 * copy, with a button straight to the order. Silently returns false when the seller
 * never linked a Discord account or the bot token is not configured.
 */
function lb_send_seller_sale_dm($seller, array $embed, string $url = '', string $buttonLabel = '🔎 Open Order'): bool
{
    if (!is_array($seller) || empty($seller['discord_id'])) return false;
    if (!defined('DS_BOT_TOKEN') || !DS_BOT_TOKEN) return false;

    $discordUserId = trim((string)$seller['discord_id']);
    if ($discordUserId === '' || !ctype_digit($discordUserId)) return false;

    try {
        $channelId = getDMChannelId(DS_BOT_TOKEN, $discordUserId);
        if (!$channelId) return false;

        $embed += [
            'author' => [
                'name' => 'LoLBoost.GG',
                'icon_url' => 'https://lolboost.gg/public/uploads/icons/default1.png',
            ],
            'footer' => ['text' => 'LoLBoost.GG Seller Area'],
            'timestamp' => date('c'),
        ];

        $payload = ['embeds' => [$embed]];
        if ($url !== '') {
            $payload['components'] = [[
                'type' => 1,
                'components' => [[
                    'type' => 2,
                    'style' => 5,
                    'label' => $buttonLabel,
                    'url' => $url,
                ]],
            ]];
        }

        return sendEmbedDM(DS_BOT_TOKEN, $channelId, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    } catch (\Throwable $e) {
        error_log('lb_send_seller_sale_dm failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Internal helper: resolves the cut limits and elapsed time for a given order.
 * Single source of truth — both calculate_booster_cut() and calculate_booster_cut_meta()
 * use this so they can never drift apart.
 *
 * Returns: [min_cut, max_cut, final_at, step_percent, step_interval, elapsed_seconds, cut_percent, is_max]
 */
function _booster_cut_resolve(array $order): array
{
    $paid_at = $order['paid_at'] ?? null;

    // --- Fallback: no paid_at yet (order requested but not yet live timer) ---
    if (!$paid_at) {
        return [
            'min_cut'        => 40,
            'max_cut'        => 40,
            'final_at'       => 0,
            'step_percent'   => 5,
            'step_interval'  => 90,
            'elapsed'        => 0,
            'cut_percent'    => 40,
            'is_max'         => true,
            'no_paid_at'     => true,
        ];
    }

    $elapsed = max(0, (new DateTime('now'))->getTimestamp() - (new DateTime($paid_at))->getTimestamp());

    $order_options = db_get_row('order_options', ['order_id' => $order['id']]);
    $is_duo  = (int) ($order_options['is_duo'] ?? 0);
    $form_id = (int) ($order['form_id'] ?? 0);

    $boost_form = db_get_row('boost_forms', ['id' => $form_id, 'select' => 'game']);
    $game = $boost_form['game'] ?? 'league-of-legends';

    // --- Cut limits per order type ---
    // Coaching (15=LoL, 16=VAL, 25=TFT): 40 → 65%, max after 15 min
    if (in_array($form_id, [15, 16, 25], true)) {
        $min_cut  = 40;
        $max_cut  = 65;
        $final_at = 900;
    } elseif ($game === 'valorant' || $game === 'teamfight-tactics') {
        if ($is_duo) {
            $min_cut  = 40; // val/tft duo: 40 → 65%, max after 10 min
            $max_cut  = 65;
            $final_at = 600;
        } else {
            $min_cut  = 40; // val/tft solo: 40 → 60%, max after 10 min
            $max_cut  = 60;
            $final_at = 600;
        }
    } else {
        // LoL
        if ($is_duo) {
            $min_cut  = 40; // lol duo: 40 → 60%, max after 10 min
            $max_cut  = 60;
            $final_at = 600;
        } else {
            $min_cut  = 40; // lol solo: 40 → 55%, max after 10 min
            $max_cut  = 55;
            $final_at = 600;
        }
    }

    $step_percent  = 5;
    $step_interval = 90;

    // Calculate current cut
    $cut_percent = $min_cut + floor($elapsed / $step_interval) * $step_percent;
    $cut_percent = min($cut_percent, $max_cut);

    // Don't allow max until final_at is reached
    if ($max_cut > $min_cut) {
        $before_max = $max_cut - $step_percent;
        if ($elapsed < $final_at) {
            $cut_percent = min($cut_percent, $before_max);
        }
    }

    $is_max = ($cut_percent >= $max_cut);

    return [
        'min_cut'       => $min_cut,
        'max_cut'       => $max_cut,
        'final_at'      => $final_at,
        'step_percent'  => $step_percent,
        'step_interval' => $step_interval,
        'elapsed'       => $elapsed,
        'cut_percent'   => $cut_percent,
        'is_max'        => $is_max,
        'no_paid_at'    => false,
    ];
}


if (!function_exists('lb_ranked_5s_fixed_cut_percent')) {
    function lb_ranked_5s_fixed_cut_percent(): int
    {
        return 50;
    }
}

if (!function_exists('lb_ranked_5s_required_boosters_from_order')) {
    function lb_ranked_5s_required_boosters_from_order(array $order): int
    {
        $count = (int)($order['boosters'] ?? $order['ranked_5s_boosters_count'] ?? 0);

        if ($count <= 0 && !empty($order['id'])) {
            try {
                $opts = db_get_row('order_options', [
                    'order_id' => (int)$order['id'],
                    'select' => 'boosters',
                ], 1);
                $count = (int)($opts['boosters'] ?? 0);
            } catch (Throwable $e) {
                $count = 0;
            }
        }

        return max(1, min(4, $count > 0 ? $count : 1));
    }
}

if (!function_exists('lb_ranked_5s_booster_earning_cents')) {
    function lb_ranked_5s_booster_earning_cents(array $order, bool $useEurPrice = false): int
    {
        $priceCents = 0;

        if ($useEurPrice && isset($order['price_eur']) && is_numeric($order['price_eur'])) {
            $priceCents = (int)$order['price_eur'];
        }

        if ($priceCents <= 0) {
            $priceCents = (int)($order['price'] ?? 0);
        }

        $requiredBoosters = lb_ranked_5s_required_boosters_from_order($order);
        $teamPool = (int)floor(($priceCents * lb_ranked_5s_fixed_cut_percent()) / 100);

        return (int)floor($teamPool / $requiredBoosters);
    }
}

function calculate_booster_cut($order, $return_type = 'amount')
{
    $order = (array)$order;

    if ((int)($order['form_id'] ?? 0) === 29) {
        if ($return_type === 'percent') {
            return lb_ranked_5s_fixed_cut_percent();
        }

        return lb_ranked_5s_booster_earning_cents($order);
    }

    $price_cents = (int) ($order['price'] ?? 0);
    $r = _booster_cut_resolve($order);
    $cut_percent = $r['cut_percent'];

    if ($return_type === 'percent') {
        return $cut_percent;
    }

    return (int) floor(($price_cents * $cut_percent) / 100);
}

/**
 * UI helper: if an admin stored a higher manual cut than the dynamic max cut,
 * prefer the stored cut for display in the order panel/live refresh.
 */
function calculate_effective_booster_cut_percent($order): float
{
    $order = (array)$order;
    if ((int)($order['form_id'] ?? 0) === 29) {
        return (float)lb_ranked_5s_fixed_cut_percent();
    }

    $r = _booster_cut_resolve($order);

    $dynamic_cut = (float) ($r['cut_percent'] ?? 0);
    $max_cut = (float) ($r['max_cut'] ?? $dynamic_cut);
    $stored_cut = (isset($order['booster_cut']) && is_numeric($order['booster_cut']))
        ? (float) $order['booster_cut']
        : 0.0;

    return ($stored_cut > $max_cut) ? $stored_cut : $dynamic_cut;
}

function calculate_effective_booster_cut_amount($order): int
{
    $order = (array)$order;
    if ((int)($order['form_id'] ?? 0) === 29) {
        return lb_ranked_5s_booster_earning_cents($order);
    }

    $price_cents = (int) ($order['price'] ?? 0);
    $cut_percent = calculate_effective_booster_cut_percent($order);

    return (int) floor(($price_cents * $cut_percent) / 100);
}

/**
 * Helper for UI: returns current cut percent and how many seconds until the next
 * scheduled cut change (or null if there will be no further changes).
 *
 * @return array{percent: float|int, next_change_in: ?int, is_max: bool}
 */
function calculate_booster_cut_meta($order): array
{
    $order = (array)$order;
    if ((int)($order['form_id'] ?? 0) === 29) {
        return [
            'percent' => lb_ranked_5s_fixed_cut_percent(),
            'next_change_in' => null,
            'is_max' => true,
        ];
    }

    $r = _booster_cut_resolve($order);

    // No paid_at: static fallback
    if ($r['no_paid_at']) {
        return [
            'percent'        => 40,
            'next_change_in' => 90,
            'is_max'         => false,
        ];
    }

    $elapsed       = $r['elapsed'];
    $cut_percent   = $r['cut_percent'];
    $max_cut       = $r['max_cut'];
    $min_cut       = $r['min_cut'];
    $step_interval = $r['step_interval'];
    $step_percent  = $r['step_percent'];
    $final_at      = $r['final_at'];
    $is_max        = $r['is_max'];

    // Compute next_change_in
    $next_change_in = null;
    if (!$is_max) {
        $before_max = $max_cut - $step_percent;
        if ($cut_percent < $before_max) {
            // Next regular step
            $next_step_at   = (floor($elapsed / $step_interval) + 1) * $step_interval;
            $next_change_in = max(1, (int) ($next_step_at - $elapsed));
        } else {
            // Next change is the final jump to max_cut at final_at
            $next_change_in = max(1, (int) ($final_at - $elapsed));
        }
    }

    $override_applied = false;

    return [
        'percent'        => $cut_percent,
        'next_change_in' => $next_change_in,
        'is_max'         => $is_max,
    ];
}




function sanitize_post_data(array $post): array
{
    $requiredFields = [
        'order_accounts' => ['ign'],
        'order_options' => ['end_lp'], // example: adjust based on your actual required fields
    ];

    $data = [];

    foreach ($post as $key => $value) {
        $parts = explode('-', $key, 2);
        if (count($parts) !== 2)
            continue;

        [$table, $column] = $parts;

        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $value = trim($value); // strip whitespace

        $isRequired = isset($requiredFields[$table]) && in_array($column, $requiredFields[$table]);

        // Nullify empty values unless required
        if ($value === '' && !$isRequired) {
            $value = null;
        }

        // Format price
        if ($column === 'price' && $value !== null) {
            $value = util_format_price_db($value);
        }

        // Escape only non-null values
        $data[$table][$column] = $value !== null ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : null;
    }

    // Validate required fields
    foreach ($requiredFields as $table => $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$table][$field]) || $data[$table][$field] === null || $data[$table][$field] === '') {
                throw new Exception("Field '{$field}' in '{$table}' is required.");
            }
        }
    }

    return $data;
}

function generateOneTimeLink($action, $expiryHours = 24)
{
    global $db;

    $token = bin2hex(random_bytes(32));

    $expiresAt = new DateTime("+{$expiryHours} hours");

    // The admin who generated the link is the one who hired the applicant.
    try { $db->run("ALTER TABLE one_time_links ADD COLUMN IF NOT EXISTS created_by_admin_id INT UNSIGNED NULL DEFAULT NULL"); } catch (Throwable $e) {}

    db_add_row('one_time_links', [
        'token' => $token,
        'action' => $action,
        'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        'created_by_admin_id' => (defined('ADMIN_ID') && ADMIN_ID) ? (int) ADMIN_ID : null,
    ]);

    return $token;
}

/**
 * Resolves the admin that created a given onboarding one-time link.
 */
function onboarding_link_admin_id($token, $action = null)
{
    global $db;

    $token = trim((string) $token);
    if ($token === '') {
        return 0;
    }

    try {
        $row = null;
        if ($action !== null) {
            $row = $db->row("SELECT created_by_admin_id FROM one_time_links WHERE token = ? AND action = ? LIMIT 1", $token, $action);
        }
        // The applicant can end up on a different form than the link action suggests
        // (e.g. a GG Girl filling the generic booster onboarding). The token itself is
        // unique, so resolving it without the action is still correct.
        if (empty($row)) {
            $row = $db->row("SELECT created_by_admin_id FROM one_time_links WHERE token = ? LIMIT 1", $token);
        }
    } catch (Throwable $e) {
        return 0;
    }

    return (int) ($row['created_by_admin_id'] ?? 0);
}

/**
 * Remembers the onboarding token of the currently opened onboarding page.
 *
 * The token also travels in a hidden form field, but that field is lost whenever the
 * applicant reloads a step or the form is rebuilt client-side — which is what turns
 * "Hired by" into "Unknown". The session copy survives all of that.
 */
function onboarding_remember_token($token): void
{
    $token = trim((string) $token);
    if ($token === '') {
        return;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['lb_onboarding_token'] = $token;
    }
}

/**
 * The onboarding token for the current submission: the posted field first, the token
 * remembered when the onboarding page was opened as fallback.
 */
function onboarding_current_token(): string
{
    $token = trim((string) ($_POST['onboarding_token'] ?? ''));
    if ($token !== '') {
        return $token;
    }

    return trim((string) ($_SESSION['lb_onboarding_token'] ?? ''));
}

function hex2rgb($hex)
{
    $hex = str_replace("#", "", $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "$r, $g, $b";
}

// ====================================
// Functions for Analytics
// ====================================

function get_monthly_revenue_data()
{
    $monthly_rev = [
        'January' => 0,
        'February' => 0,
        'March' => 0,
        'April' => 0,
        'May' => 0,
        'June' => 0,
        'July' => 0,
        'August' => 0,
        'September' => 0,
        'October' => 0,
        'November' => 0,
        'December' => 0
    ];

    $year = date('Y');
    $date_condition = "AND created_at >= '$year-01-01' AND created_at <= '$year-12-31'";

    $monthly_eur_revenue = db_run_query("SELECT
        MONTHNAME(created_at) AS month, created_at, SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'EUR' AND status = 'succeeded'
        $date_condition
        AND created_at IS NOT NULL
        GROUP BY month ORDER BY created_at ASC");

    $monthly_usd_revenue = db_run_query("SELECT
        MONTHNAME(created_at) AS month, created_at, SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'USD' AND status = 'succeeded'
        $date_condition
        AND created_at IS NOT NULL
        GROUP BY month ORDER BY created_at ASC");

    $monthly_usd_revenue = array_map(function ($val) {
        $val['sum'] = $val['sum'] / get_exchange_rate();
        return $val;
    }, $monthly_usd_revenue);

    foreach ($monthly_eur_revenue as $val) {
        if (isset($val['month'], $val['sum'])) {
            $monthly_rev[$val['month']] += $val['sum']; // Add EUR revenue
        }
    }
    foreach ($monthly_usd_revenue as $val) {
        if (isset($val['month'], $val['sum'])) {
            $monthly_rev[$val['month']] += $val['sum']; // Add converted USD revenue
        }
    }

    foreach ($monthly_rev as &$value) {
        $value = util_format_price_input($value);
    }

    $monthly_revenue_val = array_values($monthly_rev);
    $monthly_revenue_val_sum = array_sum($monthly_revenue_val);
    $monthly_revenue_val_max = max($monthly_revenue_val ?: [0]);

    return [
        'values' => $monthly_revenue_val,
        'sum' => $monthly_revenue_val_sum,
        'max' => $monthly_revenue_val_max,
        'json_values' => json_encode($monthly_revenue_val)
    ];
}

function get_revenue_data($start_date = null, $end_date = null, $order_type = null)
{
    $date_condition = '';
    $prev_date_condition = '';

    // Current period conditions
    if ($start_date && $end_date) {
        $date_condition = "AND created_at BETWEEN '$start_date' AND '$end_date'";

        // Calculate previous period (same duration before start_date)
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $interval = $start_date_obj->diff($end_date_obj);

        $prev_start_date_obj = clone $start_date_obj;
        $prev_start_date_obj->sub($interval);
        $prev_end_date_obj = clone $start_date_obj;
        $prev_end_date_obj->sub(new DateInterval('P1D'));

        $prev_start_date = $prev_start_date_obj->format('Y-m-d');
        $prev_end_date = $prev_end_date_obj->format('Y-m-d');

        $prev_date_condition = "AND created_at BETWEEN '$prev_start_date' AND '$prev_end_date'";
    } else {
        // Default to current year
        $year = date('Y');
        $date_condition = "AND YEAR(created_at) = $year";
        $prev_year = $year - 1;
        $prev_date_condition = "AND YEAR(created_at) = $prev_year";
    }

    // Add order_type condition if specified
    $type_condition = $order_type ? "AND order_type = '$order_type'" : 'AND order_type != \'order\'';

    // Current period queries
    $eur_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'EUR' 
        AND status = 'succeeded'
        $type_condition
        $date_condition
        AND created_at IS NOT NULL");

    $usd_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'USD'
        AND status = 'succeeded'
        $type_condition
        $date_condition
        AND created_at IS NOT NULL");

    $current_eur_sum = $eur_revenue[0]['sum'] ?? 0;
    $current_usd_sum = ($usd_revenue[0]['sum'] ?? 0) / get_exchange_rate();
    $current_total = $current_eur_sum + $current_usd_sum;

    // Previous period queries
    $prev_eur_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'EUR' 
        AND status = 'succeeded'
        $type_condition
        $prev_date_condition
        AND created_at IS NOT NULL");

    $prev_usd_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'USD'
        AND status = 'succeeded'
        $type_condition
        $prev_date_condition
        AND created_at IS NOT NULL");

    $prev_eur_sum = $prev_eur_revenue[0]['sum'] ?? 0;
    $prev_usd_sum = ($prev_usd_revenue[0]['sum'] ?? 0) / get_exchange_rate();
    $prev_total = $prev_eur_sum + $prev_usd_sum;

    // Calculate percentage change
    $percentage_change = 0;
    if ($prev_total != 0) {
        $percentage_change = (($current_total - $prev_total) / $prev_total) * 100;
    } elseif ($current_total != 0) {
        $percentage_change = 100; // infinite growth (from 0 to positive)
    }

    return [
        'current' => $current_total != 0 ? util_format_price_input($current_total) : '0.00',
        'previous' => $prev_total != 0 ? util_format_price_input($prev_total) : '0.00',
        'change' => round($percentage_change, 2),
        'is_up' => $percentage_change >= 0
    ];
}

function get_boosting_revenue($start_date = null, $end_date = null)
{
    $date_condition = '';
    $prev_date_condition = '';

    // Current period conditions
    if ($start_date && $end_date) {
        $date_condition = "AND created_at BETWEEN '$start_date' AND '$end_date'";

        // Calculate previous period (same duration before start_date)
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $interval = $start_date_obj->diff($end_date_obj);

        $prev_start_date_obj = clone $start_date_obj;
        $prev_start_date_obj->sub($interval);
        $prev_end_date_obj = clone $start_date_obj;
        $prev_end_date_obj->sub(new DateInterval('P1D'));

        $prev_start_date = $prev_start_date_obj->format('Y-m-d');
        $prev_end_date = $prev_end_date_obj->format('Y-m-d');

        $prev_date_condition = "AND created_at BETWEEN '$prev_start_date' AND '$prev_end_date'";
    } else {
        // Default to current year
        $year = date('Y');
        $date_condition = "AND YEAR(created_at) = $year";
        $prev_year = $year - 1;
        $prev_date_condition = "AND YEAR(created_at) = $prev_year";
    }

    // Current period queries
    $eur_revenue = db_run_query("SELECT SUM(price) AS sum
        FROM orders
        WHERE currency = 'EUR' 
        AND UPPER(status) NOT IN ('UNPAID','UNKNOWN','REFUNDED','CANCELLED')
        $date_condition
        AND paid_at IS NOT NULL");

    $usd_revenue = db_run_query("SELECT SUM(price) AS sum
        FROM orders
        WHERE currency = 'USD'
        AND UPPER(status) NOT IN ('UNPAID','UNKNOWN','REFUNDED','CANCELLED')
        $date_condition
        AND paid_at IS NOT NULL");

    $current_eur_sum = $eur_revenue[0]['sum'] ?? 0;
    $current_usd_sum = ($usd_revenue[0]['sum'] ?? 0) / get_exchange_rate();
    $current_total = $current_eur_sum + $current_usd_sum;

    // Previous period queries
    $prev_eur_revenue = db_run_query("SELECT SUM(price) AS sum
        FROM orders
        WHERE currency = 'EUR' 
        AND status != 'unpaid'
        $prev_date_condition
        AND paid_at IS NOT NULL");

    $prev_usd_revenue = db_run_query("SELECT SUM(price) AS sum
        FROM orders
        WHERE currency = 'USD'
        AND status != 'unpaid'
        $prev_date_condition
        AND paid_at IS NOT NULL");

    $prev_eur_sum = $prev_eur_revenue[0]['sum'] ?? 0;
    $prev_usd_sum = ($prev_usd_revenue[0]['sum'] ?? 0) / get_exchange_rate();
    $prev_total = $prev_eur_sum + $prev_usd_sum;

    $percentage_change = 0;
    if ($prev_total != 0) {
        $percentage_change = (($current_total - $prev_total) / $prev_total) * 100;
    } elseif ($current_total != 0) {
        $percentage_change = 100;
    }

    return [
        'current' => $current_total != 0 ? util_format_price_input($current_total) : '0.00',
        'previous' => $prev_total != 0 ? util_format_price_input($prev_total) : '0.00',
        'change' => round($percentage_change, 2),
        'is_up' => $percentage_change >= 0,
        'current_raw' => $current_total,
        'previous_raw' => $prev_total,
    ];
}

function get_total_revenue($start_date = null, $end_date = null)
{
    // Platform-real revenue:
    // - Boosting orders = full order price
    // - Selling marketplace accounts = seller fee only
    // - Normal smurf/package accounts = 100% for admin_id 2, 30% for admin_id 51
    // - Other non-order transactions remain included, but account/lol_account gross transactions are excluded
    $other_revenue = get_other_revenue_excluding_account_sales($start_date, $end_date);
    $boosting_revenue = get_boosting_revenue($start_date, $end_date);
    $account_sales_revenue = get_account_sales_revenue($start_date, $end_date);

    $current_total = (float)($other_revenue['current_raw'] ?? 0)
        + (float)($boosting_revenue['current_raw'] ?? (float) str_replace(['.', ','], ['', '.'], $boosting_revenue['current']))
        + (float)($account_sales_revenue['current_raw'] ?? 0);

    $prev_total = (float)($other_revenue['previous_raw'] ?? 0)
        + (float)($boosting_revenue['previous_raw'] ?? (float) str_replace(['.', ','], ['', '.'], $boosting_revenue['previous']))
        + (float)($account_sales_revenue['previous_raw'] ?? 0);

    $metric = analytics_money_metric($current_total, $prev_total);
    $metric['current_raw'] = $current_total;
    $metric['previous_raw'] = $prev_total;

    return $metric;
}

function get_accounts_revenue($start_date = null, $end_date = null)
{
    return get_revenue_data($start_date, $end_date, 'account');
}

function get_tips_revenue($start_date = null, $end_date = null)
{
    return get_revenue_data($start_date, $end_date, 'tip');
}

function get_expenses_data($start_date = null, $end_date = null)
{
    $date_condition = '';
    $prev_date_condition = '';

    // Current period conditions
    if ($start_date && $end_date) {
        $date_condition = "AND created_at BETWEEN '$start_date' AND '$end_date'";

        // Calculate previous period (same duration before start_date)
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $interval = $start_date_obj->diff($end_date_obj);

        $prev_start_date_obj = clone $start_date_obj;
        $prev_start_date_obj->sub($interval);
        $prev_end_date_obj = clone $start_date_obj;
        $prev_end_date_obj->sub(new DateInterval('P1D'));

        $prev_start_date = $prev_start_date_obj->format('Y-m-d');
        $prev_end_date = $prev_end_date_obj->format('Y-m-d');

        $prev_date_condition = "AND created_at BETWEEN '$prev_start_date' AND '$prev_end_date'";
    } else {
        // Default to current year
        $year = date('Y');
        $date_condition = "AND YEAR(created_at) = $year";
        $prev_year = $year - 1;
        $prev_date_condition = "AND YEAR(created_at) = $prev_year";
    }

    // Current period queries (still separate by currency for accurate conversion)
    $eur_expenses = db_run_query("SELECT SUM(amount) AS sum
        FROM booster_payments
        WHERE currency = 'EUR'
        AND amount NOT LIKE '-%'
        $date_condition");

    $usd_expenses = db_run_query("SELECT SUM(amount) AS sum
        FROM booster_payments
        WHERE currency = 'USD'
        AND amount NOT LIKE '-%'
        $date_condition");

    $current_eur_sum = $eur_expenses[0]['sum'] ?? 0;
    $current_usd_sum = ($usd_expenses[0]['sum'] ?? 0) / get_exchange_rate();
    $current_total = $current_eur_sum + $current_usd_sum;

    // Previous period queries
    $prev_eur_expenses = db_run_query("SELECT SUM(amount) AS sum
        FROM booster_payments
        WHERE currency = 'EUR'
        AND amount NOT LIKE '-%'
        $prev_date_condition");

    $prev_usd_expenses = db_run_query("SELECT SUM(amount) AS sum
        FROM booster_payments
        WHERE currency = 'USD'
        AND amount NOT LIKE '-%'
        $prev_date_condition");

    $prev_eur_sum = $prev_eur_expenses[0]['sum'] ?? 0;
    $prev_usd_sum = ($prev_usd_expenses[0]['sum'] ?? 0) / get_exchange_rate();
    $prev_total = $prev_eur_sum + $prev_usd_sum;

    // Calculate percentage change
    $percentage_change = 0;
    if ($prev_total != 0) {
        $percentage_change = (($current_total - $prev_total) / $prev_total) * 100;
    } elseif ($current_total != 0) {
        $percentage_change = 100; // infinite growth (from 0 to positive)
    }

    // Simplified return format (same as original revenue function)
    return [
        'current' => $current_total != 0 ? util_format_price_input($current_total) : '0.00',
        'previous' => $prev_total != 0 ? util_format_price_input($prev_total) : '0.00',
        'change' => round($percentage_change, 2),
        'is_up' => $percentage_change >= 0,
        'current_raw' => $current_total,
        'previous_raw' => $prev_total,
    ];
}

function get_profit_data($start_date = null, $end_date = null)
{
    // Get raw revenue sums
    $revenue = get_total_revenue($start_date, $end_date);
    $current_revenue = $revenue['current_raw'];
    $previous_revenue = $revenue['previous_raw'];

    // Get raw expense sums (assuming get_expenses_data returns raw values)
    $expenses = get_expenses_data($start_date, $end_date);
    $current_expenses = $expenses['current_raw'] ?? (float) str_replace(['.', ','], ['', '.'], $expenses['current']);
    $previous_expenses = $expenses['previous_raw'] ?? (float) str_replace(['.', ','], ['', '.'], $expenses['previous']);

    // Calculate profit values
    $current_profit = $current_revenue - $current_expenses;
    $previous_profit = $previous_revenue - $previous_expenses;

    // Calculate percentage change
    $percentage_change = 0;
    if ($previous_profit != 0) {
        $percentage_change = (($current_profit - $previous_profit) / $previous_profit) * 100;
    } elseif ($current_profit != 0) {
        $percentage_change = 100;
    }

    return [
        'current' => util_format_price_input($current_profit),
        'previous' => util_format_price_input($previous_profit),
        'change' => round($percentage_change, 2),
        'is_up' => $percentage_change >= 0,
        'current_raw' => $current_profit,  // Include raw values for future calculations
        'previous_raw' => $previous_profit // Include raw values for future calculations
    ];
}

function get_payments_summary($start_date = null, $end_date = null)
{
    $date_condition = '';

    if ($start_date && $end_date) {
        $date_condition = "AND created_at BETWEEN '$start_date' AND '$end_date'";
    } else {
        // Default to current year
        $year = date('Y');
        $date_condition = "AND YEAR(created_at) = $year";
    }

    // Helper function to combine EUR and USD results
    $combine_currencies = function ($eur_result, $usd_result) {
        $eur_total = $eur_result[0]['total'] ?? 0;
        $usd_total = ($usd_result[0]['total'] ?? 0) / get_exchange_rate();

        return util_format_price_input($eur_total + $usd_total);
    };

    // Get data for each type
    $order_completed_eur = db_run_query("SELECT 
        COUNT(*) AS count, SUM(amount) AS total
        FROM booster_payments
        WHERE type = 'order_completion'
        AND currency = 'EUR'
        $date_condition");

    $order_completed_usd = db_run_query("SELECT 
        COUNT(*) AS count, SUM(amount) AS total
        FROM booster_payments
        WHERE type = 'order_completion'
        AND currency = 'USD'
        $date_condition");

    $private_orders_eur = db_run_query("SELECT 
        COUNT(*) AS count, SUM(amount) AS total
        FROM booster_payments
        WHERE type = 'private_order'
        AND currency = 'EUR'
        $date_condition");

    $private_orders_usd = db_run_query("SELECT 
        COUNT(*) AS count, SUM(amount) AS total
        FROM booster_payments
        WHERE type = 'private_order'
        AND currency = 'USD'
        $date_condition");

    // For fines, use ABS() to convert negative values to positive before summing
    $fines_eur = db_run_query("SELECT 
        COUNT(*) AS count, SUM(ABS(amount)) AS total
        FROM booster_payments
        WHERE type = 'fine'
        AND currency = 'EUR'
        $date_condition");

    $fines_usd = db_run_query("SELECT 
        COUNT(*) AS count, SUM(ABS(amount)) AS total
        FROM booster_payments
        WHERE type = 'fine'
        AND currency = 'USD'
        $date_condition");

    return [
        'order_completed' => $combine_currencies($order_completed_eur, $order_completed_usd),
        'private_orders' => $combine_currencies($private_orders_eur, $private_orders_usd),
        'fines' => $combine_currencies($fines_eur, $fines_usd)
    ];
}

function get_weekly_orders_data($start_date = null, $end_date = null)
{
    // Weekly activity: REAL (non-tip) paid/active orders, bucketed by DAYNAME(created_at).
    // Same rules as Order status widget:
    //  - created_at defines bucket and must be within selected range
    //  - include IN_PROGRESS (manual/private payments)
    //  - exclude UNPAID/UNKNOWN
    //  - exclude successful tip transactions (order_type='tip')
    //
    // Performance: uses LEFT JOIN on DISTINCT tip order_ids (instead of correlated NOT EXISTS).

    $current_weekly_ords = [
        'Sunday' => 0,
        'Monday' => 0,
        'Tuesday' => 0,
        'Wednesday' => 0,
        'Thursday' => 0,
        'Friday' => 0,
        'Saturday' => 0
    ];

    $previous_weekly_ords = $current_weekly_ords;

    $date_condition = '';
    $prev_date_condition = '';

    if ($start_date && $end_date) {
        $date_condition = "AND o.created_at BETWEEN '$start_date' AND '$end_date'";

        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $interval = $start_date_obj->diff($end_date_obj);

        $prev_start_date_obj = clone $start_date_obj;
        $prev_start_date_obj->sub($interval);
        $prev_end_date_obj = clone $start_date_obj;
        $prev_end_date_obj->sub(new DateInterval('P1D'));

        $prev_start_date = $prev_start_date_obj->format('Y-m-d 00:00:00');
        $prev_end_date = $prev_end_date_obj->format('Y-m-d 23:59:59');

        $prev_date_condition = "AND o.created_at BETWEEN '$prev_start_date' AND '$prev_end_date'";
    } else {
        $date_condition = "AND WEEK(o.created_at) = WEEK(NOW()) AND YEAR(o.created_at) = YEAR(NOW())";
        $prev_date_condition = "AND WEEK(o.created_at) = WEEK(NOW()) - 1 AND YEAR(o.created_at) = YEAR(NOW())";
    }

    $paid_or_active = "(o.paid_at IS NOT NULL OR o.status IN ('IN_PROGRESS','COMPLETED','PAUSED'))";

    $base_where = "UPPER(o.status) NOT IN ('UNKNOWN','UNPAID','REFUNDED','CANCELLED') AND $paid_or_active";

    $sql_template = function ($extra_date_condition) use ($base_where) {
        return "
            SELECT DAYNAME(o.created_at) AS day, COUNT(*) AS count
            FROM orders o
            LEFT JOIN (
                SELECT DISTINCT t.order_id
                FROM transactions t
                WHERE t.order_type = 'tip'
                  AND t.status = 'succeeded'
            ) tip ON tip.order_id = o.id
            WHERE $base_where
              AND tip.order_id IS NULL
              $extra_date_condition
            GROUP BY day
        ";
    };

    $current_orders = db_run_query($sql_template($date_condition));
    foreach ($current_orders as $val) {
        if (isset($current_weekly_ords[$val['day']])) {
            $current_weekly_ords[$val['day']] = intval($val['count']);
        }
    }

    $previous_orders = db_run_query($sql_template($prev_date_condition));
    foreach ($previous_orders as $val) {
        if (isset($previous_weekly_ords[$val['day']])) {
            $previous_weekly_ords[$val['day']] = intval($val['count']);
        }
    }

    $current_values = array_values($current_weekly_ords);
    $previous_values = array_values($previous_weekly_ords);

    return [
        'current' => [
            'values' => $current_values,
            'sum' => array_sum($current_values),
            'max' => max($current_values ?: [0]),
            'json_values' => json_encode($current_values)
        ],
        'previous' => [
            'values' => $previous_values,
            'sum' => array_sum($previous_values),
            'max' => max($previous_values ?: [0]),
            'json_values' => json_encode($previous_values)
        ]
    ];
}

function get_customers_summary($start_date = null, $end_date = null)
{
    $labels = [];
    $current_counts = [];
    $previous_counts = [];

    if ($start_date && $end_date) {
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);

        // Calculate same period in previous year
        $prev_start_date_obj = clone $start_date_obj;
        $prev_start_date_obj->sub(new DateInterval('P1Y'));
        $prev_end_date_obj = clone $end_date_obj;
        $prev_end_date_obj->sub(new DateInterval('P1Y'));

        $prev_start_date = $prev_start_date_obj->format('Y-m-d');
        $prev_end_date = $prev_end_date_obj->format('Y-m-d');

        // Determine if we should group by month or day
        $interval = $start_date_obj->diff($end_date_obj);
        $days_diff = $interval->days;

        if ($days_diff > 60) {
            // Group by month
            $period = new DatePeriod(
                $start_date_obj,
                new DateInterval('P1M'),
                $end_date_obj->modify('+1 month')
            );

            foreach ($period as $date) {
                $month_start = $date->format('Y-m-01');
                $month_end = $date->format('Y-m-t');

                $labels[] = $date->format('F');

                // Current year counts
                $current_data = db_run_query("
                    SELECT COUNT(*) AS count
                    FROM clients
                    WHERE created_at BETWEEN '$month_start' AND '$month_end'
                ");
                $current_counts[] = $current_data[0]['count'] ?? 0;

                // Previous year counts
                $prev_month_start = $date->modify('-1 year')->format('Y-m-01');
                $prev_month_end = $date->format('Y-m-t');

                $previous_data = db_run_query("
                    SELECT COUNT(*) AS count
                    FROM clients
                    WHERE created_at BETWEEN '$prev_month_start' AND '$prev_month_end'
                ");
                $previous_counts[] = $previous_data[0]['count'] ?? 0;
            }
        } else {
            // Group by day
            $period = new DatePeriod(
                $start_date_obj,
                new DateInterval('P1D'),
                $end_date_obj->modify('+1 day')
            );

            foreach ($period as $date) {
                $day = $date->format('Y-m-d');
                $prev_day = $date->modify('-1 year')->format('Y-m-d');

                $labels[] = $date->format('M j');

                // Current year counts
                $current_data = db_run_query("
                    SELECT COUNT(*) AS count
                    FROM clients
                    WHERE DATE(created_at) = '$day'
                ");
                $current_counts[] = $current_data[0]['count'] ?? 0;

                // Previous year counts
                $previous_data = db_run_query("
                    SELECT COUNT(*) AS count
                    FROM clients
                    WHERE DATE(created_at) = '$prev_day'
                ");
                $previous_counts[] = $previous_data[0]['count'] ?? 0;
            }
        }
    } else {
        // Default to current week (group by day)
        $monday = new DateTime('monday this week');

        for ($i = 0; $i < 7; $i++) {
            $day = clone $monday;
            $day->add(new DateInterval("P{$i}D"));
            $day_str = $day->format('Y-m-d');

            $prev_day = clone $day;
            $prev_day->sub(new DateInterval('P1Y'));
            $prev_day_str = $prev_day->format('Y-m-d');

            $labels[] = $day->format('D');

            // Current week counts
            $current_data = db_run_query("
                SELECT COUNT(*) AS count
                FROM clients
                WHERE DATE(created_at) = '$day_str'
            ");
            $current_counts[] = $current_data[0]['count'] ?? 0;

            // Previous year counts
            $previous_data = db_run_query("
                SELECT COUNT(*) AS count
                FROM clients
                WHERE DATE(created_at) = '$prev_day_str'
            ");
            $previous_counts[] = $previous_data[0]['count'] ?? 0;
        }
    }

    return [
        'current' => $current_counts,
        'previous' => $previous_counts,
        'labels' => $labels
    ];
}

function get_monthly_forms_revenue_data($start_date = null, $end_date = null)
{
    $forms = db_get_rows('boost_forms', ['select' => 'id,name']);
    $monthly_form_chart = [];
    $form_colors = ['#377dff', '#00c9db', '#ffc107', '#17c671', '#ea4335', '#6f42c1', '#fd7e14', '#20c997'];

    // Build date condition
    $date_condition = '';
    if ($start_date && $end_date) {
        $date_condition = "AND paid_at BETWEEN '$start_date' AND '$end_date'";
    } else {
        // Default to current year if no range provided
        $year = date('Y');
        $date_condition = "AND YEAR(paid_at) = $year";
    }

    foreach ($forms as $key => $form) {
        $monthly_orders = db_run_query("SELECT
            MONTH(paid_at) AS month, paid_at, SUM(price) AS sum
            FROM orders
            WHERE UPPER(status) NOT IN ('UNKNOWN','UNPAID','REFUNDED','CANCELLED') 
            AND form_id = " . $form['id'] . "
            $date_condition
            AND paid_at IS NOT NULL
            GROUP BY month ORDER BY paid_at ASC");

        $monthly_orders = array_column($monthly_orders, 'sum', 'month');

        // Ensure all months are represented
        $months = range(1, 12);
        foreach ($months as $month) {
            if (!isset($monthly_orders[$month])) {
                $monthly_orders[$month] = 0;
            } else {
                $monthly_orders[$month] = util_format_price_input($monthly_orders[$month]);
            }
        }

        ksort($monthly_orders);

        $monthly_form_chart[$key] = [
            'data' => array_values($monthly_orders),
            'name' => $form['name'],
            'color' => $form_colors[$key % count($form_colors)],
            'borderColor' => $form_colors[$key % count($form_colors)]
        ];
    }

    return $monthly_form_chart;
}

function get_order_stats($start_date = null, $end_date = null)
{
    // Order status widget: REAL (non-tip) paid/active orders created in the selected range.
    // Requirements:
    //  - Option C: created_at determines whether it is inside the selected range.
    //  - Include IN_PROGRESS (manual/private payments) even if there is no matching transaction row.
    //  - Exclude UNPAID and UNKNOWN.
    //  - Exclude Tips (transactions.order_type = 'tip', status='succeeded').
    //
    // Performance notes:
    //  - Single aggregated query (instead of multiple COUNT queries).
    //  - Uses a LEFT JOIN on a DISTINCT list of tip order_ids (instead of correlated NOT EXISTS per row).

    $start = $start_date ?: date('Y-m-01 00:00:00');
    $end   = $end_date   ?: date('Y-m-t 23:59:59');

    // "Paid/active" definition: paid_at is set OR status indicates work has started / finished.
    // Refunded/cancelled orders are displayed separately but do NOT count into total/completed/active orders.
    $paid_or_active = "(o.paid_at IS NOT NULL OR o.status IN ('IN_PROGRESS','COMPLETED','PAUSED','REFUNDED'))";
    $countable_order = "UPPER(o.status) NOT IN ('UNPAID','UNKNOWN','REFUNDED','CANCELLED')";

    $sql = "
        SELECT
            SUM(CASE WHEN $countable_order THEN 1 ELSE 0 END) AS total_orders,
            SUM(CASE WHEN o.status = 'COMPLETED' THEN 1 ELSE 0 END) AS completed_orders,
            SUM(CASE WHEN o.status = 'IN_PROGRESS' THEN 1 ELSE 0 END) AS in_progress_orders,
            SUM(CASE WHEN o.status = 'PAUSED' THEN 1 ELSE 0 END) AS paused_orders,
            SUM(CASE WHEN o.status = 'REFUNDED' THEN 1 ELSE 0 END) AS refunded_orders
        FROM orders o
        LEFT JOIN (
            SELECT DISTINCT t.order_id
            FROM transactions t
            WHERE t.order_type = 'tip'
              AND t.status = 'succeeded'
        ) tip ON tip.order_id = o.id
        WHERE o.created_at BETWEEN '$start' AND '$end'
          AND UPPER(o.status) NOT IN ('UNPAID','UNKNOWN')
          AND $paid_or_active
          AND tip.order_id IS NULL
    ";

    $row = db_run_query($sql);
    $row = $row[0] ?? [];

    // Account sales counts for the same selected range.
    // Smurf accounts = normal accounts table, sold accounts from admin_id 2 / 51.
    // Seller accounts = marketplace selling_accounts sold by sellers.
    $accountSalesSql = "
        SELECT
            (
                SELECT COUNT(*)
                FROM accounts a
                WHERE a.status = 1
                  AND a.client_id IS NOT NULL
                  AND a.admin_id IN (2, 51)
                  AND COALESCE(a.sold_at, a.created_at) BETWEEN '$start' AND '$end'
            ) AS smurf_account_sales,
            (
                SELECT COUNT(*)
                FROM selling_accounts sa
                WHERE sa.sold = 1
                  AND sa.client_id IS NOT NULL
                  AND COALESCE(sa.sold_at, sa.created_at) BETWEEN '$start' AND '$end'
            ) AS seller_account_sales
    ";

    $accountSalesRow = db_run_query($accountSalesSql);
    $accountSalesRow = $accountSalesRow[0] ?? [];

    return [
        'total_orders'          => intval($row['total_orders'] ?? 0),
        'completed_orders'      => intval($row['completed_orders'] ?? 0),
        'paused_orders'         => intval($row['paused_orders'] ?? 0),
        'in_progress_orders'    => intval($row['in_progress_orders'] ?? 0),
        'refunded_orders'       => intval($row['refunded_orders'] ?? 0),
        'smurf_account_sales'   => intval($accountSalesRow['smurf_account_sales'] ?? 0),
        'seller_account_sales'  => intval($accountSalesRow['seller_account_sales'] ?? 0),
    ];
}

function get_boosters_balance()
{
    $booster_balance = db_run_query("SELECT SUM(balance) AS total
        FROM boosters
        WHERE balance NOT LIKE '-%'
        AND balance IS NOT NULL
        AND balance != ''"
    );

    $total_balance = $booster_balance[0]['total'] ?? 0;

    return util_format_price_input($total_balance);
}

function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

function generateUniqueSlug($title, $tableName, $column = 'slug')
{
    $slug = slugify($title);
    $baseSlug = $slug;
    $i = 1;

    while (true) {
        $count = db_get_row_count($tableName, [$column => $slug]);

        if ($count == 0) {
            return $slug;
        }

        $slug = "$baseSlug-$i";
        $i++;
    }
}

function util_get_lol_skins($selected = [])
{
    $skins = @file_get_contents(SYS_PATH . '/public/assets/lol_skins.json');

    if ($skins === false) {
        throw new Exception("Failed to fetch LOL skins data.");
    }

    $skins = json_decode($skins, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid LOL skins data format: " . json_last_error_msg());
    }

    if (!is_array($selected)) {
        $selected = explode('|', (string) ($selected ?? ''));
    }

    // Normalize + remove empty/falsy values to avoid "select all" issues
    $selected = array_values(array_filter(array_map('strval', $selected), 'strlen'));

    $options = '';
    foreach ($skins as $skin) {
        $imageUrl = 'https://ddragon.leagueoflegends.com/cdn/img/champion/loading/' . $skin['value'] . '.jpg';

        $options .= '<option value="' . $skin['value'] . '" data-image="' . $imageUrl . '" ' . (in_array($skin['value'], $selected, true) ? 'selected' : '') . '>' . $skin['label'] . '</option>';
    }

    return $options;
}

function util_get_skin_label($skinId)
{
    $skins = @file_get_contents(SYS_PATH . '/public/assets/lol_skins.json');

    if ($skins === false) {
        throw new Exception("Failed to fetch LOL skins data.");
    }

    $skins = json_decode($skins, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid LOL skins data format: " . json_last_error_msg());
    }

    foreach ($skins as $skin) {
        if ($skin['value'] == $skinId) {
            return $skin['label'];
        }
    }

    return 'Unknown Skin';
}

function util_language_list()
{
    return [
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'pt' => 'Portuguese',
        'it' => 'Italian',
        'ru' => 'Russian',
        'jp' => 'Japanese',
        'zh' => 'Chinese (Simplified)',
        'zh-TW' => 'Chinese (Traditional)',
        'ko' => 'Korean',
        'ar' => 'Arabic',
        'hi' => 'Hindi',
        'ur' => 'Urdu',
        'tr' => 'Turkish',
        'nl' => 'Dutch',
        'sv' => 'Swedish',
        'pl' => 'Polish',
        'id' => 'Indonesian',
        'th' => 'Thai',
        'vi' => 'Vietnamese',
        'ms' => 'Malay',
        'fa' => 'Persian (Farsi)',
        'he' => 'Hebrew',
        'el' => 'Greek',
        'cs' => 'Czech',
        'hu' => 'Hungarian',
        'fi' => 'Finnish',
        'da' => 'Danish',
        'no' => 'Norwegian',
        'ro' => 'Romanian',
        'bg' => 'Bulgarian',
        'uk' => 'Ukrainian',
        'sr' => 'Serbian',
        'hr' => 'Croatian',
    ];
}

function t(string $key, ?string $lang = null): string
{
    static $cache = [];
    $lang = $lang ?? LANG;

    if (!isset($cache[$lang])) {
        $file = dirname(__DIR__, 2) . "/public/assets/core/main/translations/{$lang}.json";
        $cache[$lang] = is_file($file)
            ? (json_decode(file_get_contents($file), true)['translations'] ?? [])
            : [];
    }

    if (isset($cache[$lang][$key])) {
        return $cache[$lang][$key];
    }

    // --- Keep translation files in sync with master keys ---
    // When a new key is encountered, we append it to master.json (keys list)
    // AND also ensure every <lang>.json file contains the new key.
    // This keeps your translation tool consistent across languages.
    //
    // Notes:
    // - We store placeholders as the original key (same behavior as fallback UI)
    // - Uses LOCK_EX to reduce race conditions
    // - Skips non-standard files like master.json itself

    $translationsDir = dirname(__DIR__, 2) . "/public/assets/core/main/translations";
    $masterFile = $translationsDir . "/master.json";

    $raw = is_file($masterFile) ? json_decode(file_get_contents($masterFile), true) : null;
    $keys = (is_array($raw) && isset($raw['keys']) && is_array($raw['keys']))
        ? $raw
        : ['keys' => []];
    if (!in_array($key, $keys['keys'], true)) {
        $keys['keys'][] = $key;
        file_put_contents(
            $masterFile,
            json_encode($keys, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        // Ensure all language json files contain the new key.
        // (We do this only when a key is NEW to master.json to keep runtime low.)
        $langFiles = glob($translationsDir . '/*.json') ?: [];
        foreach ($langFiles as $filePath) {
            $base = pathinfo($filePath, PATHINFO_FILENAME);
            if ($base === 'master') {
                continue;
            }

            $json = is_file($filePath) ? json_decode(file_get_contents($filePath), true) : null;
            if (!is_array($json)) {
                $json = [];
            }
            if (!isset($json['translations']) || !is_array($json['translations'])) {
                $json['translations'] = [];
            }

            if (!array_key_exists($key, $json['translations'])) {
                // Placeholder: default to the key itself
                $json['translations'][$key] = $key;

                // Write back
                file_put_contents(
                    $filePath,
                    json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    LOCK_EX
                );
            }
        }
    }

    return $key;
}

function logout_all_sessions()
{
    global $is_booster;
    global $is_client;
    global $is_admin;


    if ($is_booster) {
        unset($_SESSION['booster']);
        setcookie('booster_session_token', '', time() - 3600, '/');
        db_auth_session_end(SESSION_TOKEN['booster'], 'booster');
    }

    if ($is_client) {
        unset($_SESSION['client']);
        setcookie('client_session_token', '', time() - 3600, '/');
        db_auth_session_end(SESSION_TOKEN['client'], 'client');
    }

    // Seller. Es gibt kein $is_seller-Global, deshalb ueber die Konstante,
    // die core/session.php setzt. Ohne diesen Block blieb das Cookie
    // seller_session_token bestehen und der Logout hatte keine Wirkung.
    if (defined('SELLER_DATA') && SELLER_DATA !== false) {
        unset($_SESSION['seller']);
        setcookie('seller_session_token', '', time() - 3600, '/');
        if (defined('SESSION_TOKEN') && !empty(SESSION_TOKEN['seller'])) {
            db_auth_session_end(SESSION_TOKEN['seller'], 'seller');
        }
    }

    if ($is_admin) {
        unset($_SESSION['admin']);
        setcookie('admin_session_token', '', time() - 3600, '/');
        db_auth_session_end(SESSION_TOKEN['admin'], 'admin');
    }
}

function get_addable_addons_for_order(array $order)
{
    $addons = [];

    $allOptions = [
        'is_duo' => [
            'label' => 'Duo Mode',
            'description' => 'Play alongside your booster in duo mode.',
        ],
        'is_priority' => [
            'label' => 'Priority Boost',
            'description' => 'Your boost order will be completed around 2x faster than regular ones.',
        ],
        'is_bonus_win' => [
            'label' => '+1 Bonus Win',
            'description' => 'Your booster will win an additional game after you reach your desired rank.',
        ],
        'is_solo_only' => [
            'label' => 'Solo Queue',
            'description' => 'Your booster will play solo only on your account and will not duo with any other account.',
        ],
        'is_streaming' => [
            'label' => 'Stream Games',
            'description' => 'Your booster will privately stream you the games while he is playing.',
        ],
        'is_coaching' => [
            'label' => 'Voice Chat',
            'description' => 'Your booster will be in a call with you and point out your mistakes and give you guidance.',
        ],
        'is_hidden_duo' => [
            'label' => 'Hidden Duo',
            'description' => 'Your Booster will use multiple accounts to play with you.',
        ],
        'is_undercover_winrate' => [
            'label' => 'Undercover Winrate',
            'description' => 'Your booster will keep the win rate at 65% or below so the account looks more natural.',
        ],
        'is_moderate_kda' => [
            'label' => 'Moderate KDA',
            'description' => 'Your booster will keep the average KDA at 4.5 or below over the whole order.',
        ],
        'is_champions_roles' => [
            'label' => 'Champs & Roles',
            'description' => 'Select which champions and roles the booster can play free of charge.',
        ],
    ];

    $soloOptions = ['is_solo_only', 'is_streaming'];
    $duoOptions = ['is_coaching', 'is_hidden_duo'];

    $basePost = [
        'form_id' => $order['form_id'],
        'server' => $order['server'],
        'start_tier' => $order['start_tier'],
        'start_division' => $order['start_division'],
        'end_tier' => $order['end_tier'],
        'end_division' => $order['end_division'],
        'start_lp' => $order['start_lp'],
        'end_lp' => $order['end_lp'],
        'start_rr' => $order['start_rr'],
        'end_rr' => $order['end_rr'],
        'matches' => $order['matches'],
        'queue_type' => $order['queue_type'],
        'lp_gain' => $order['lp_gain'],
        'is_duo' => (int) $order['is_duo'],
    ];

    $basePost = array_filter($basePost, fn($v) => $v !== '' && $v !== null);

    $pricingBase = get_pricing_json($order['uuid']);
    if (empty($pricingBase['extra']))
        return [];

    [$basePrice] = calculate_boost_pricing($pricingBase, $basePost);

    $paidPrice = isset($order['price']) ? (float) $order['price'] : null;
    $discountMultiplier = ($paidPrice !== null && $basePrice > 0) ? ($paidPrice / $basePrice) : 1.0;

    foreach ($pricingBase['extra'] as $key => $multiplier) {

        if (isset($order[$key]) && (int) $order[$key] === 1)
            continue;

        if ($order['is_duo'] == 1 && in_array($key, $soloOptions))
            continue;
        if ($order['is_duo'] == 0 && in_array($key, $duoOptions))
            continue;

        $postSim = $basePost;
        $postSim[$key] = 1;
        [$newPrice] = calculate_boost_pricing($pricingBase, $postSim);

        $deltaFull = round($newPrice - $basePrice, 2);
        $delta = max(0, round($deltaFull * $discountMultiplier, 2));
        if ($delta > 0) {
            $addons[] = [
                'key' => $key,
                'label' => $allOptions[$key]['label'] ?? $key,
                'description' => $allOptions[$key]['description'] ?? '',
                'price' => $delta,
                'price_formatted' => number_format($delta / 100, 2),
                'currency' => $order['currency'],
            ];
        }
    }

    return $addons;
}

function get_booster_data($booster_id)
{
    return db_get_row('boosters', [
        'id' => $booster_id,
    ]);
}

function get_review_highlights()
{
    return [
        'great-communication',
        'very-skilled',
        'fast-delivery',
        'friendly-booster',
        'would-order-again',
        'followed-instructions',
        'consistent-performance',
        'good-updates',
        'clean-&-discreet',
        'tilt-proof',
    ];
}

function process_stars($number)
{
    $full_star = '★';
    $empty_star = '✩';

    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $number) {
            $stars .= $full_star;
        } else {
            $stars .= $empty_star;
        }
    }

    return $stars;
}
/**
 * Returns a persistent fake start rating between 4.6 and 5.0.
 * Stored in boosters.rating so the value does not change on every page load.
 */
function random_booster_start_rating()
{
    return mt_rand(46, 50) / 10;
}

/**
 * Recalculate one booster rating.
 *
 * Rule:
 * - Fewer than 3 approved client reviews: keep existing 4.6-5.0 start rating,
 *   or create one if the current rating is missing/outside that range.
 * - 3 or more approved client reviews: use the rounded AVG(reviews.overall).
 */
function recalculate_booster_rating($booster_id)
{
    global $db;

    $booster_id = intval($booster_id);
    if ($booster_id <= 0) {
        return false;
    }

    $stats = $db->row("
        SELECT
            COUNT(*) AS review_count,
            ROUND(AVG(overall), 1) AS avg_overall
        FROM reviews
        WHERE booster_id = ?
          AND approved = 1
    ", $booster_id);

    $review_count = intval($stats['review_count'] ?? 0);

    if ($review_count >= 3) {
        $rating = floatval($stats['avg_overall'] ?? 0);
    } else {
        $booster = db_get_row('boosters', ['id' => $booster_id]);
        if (!$booster) {
            return false;
        }

        $current_rating = floatval($booster['rating'] ?? 0);

        if ($current_rating >= 4.6 && $current_rating <= 5.0) {
            $rating = $current_rating;
        } else {
            $rating = random_booster_start_rating();
        }
    }

    db_update_row('boosters', ['id' => $booster_id], [
        'rating' => $rating
    ]);

    return $rating;
}

function aggregateReviewScores(array $reviews): array
{
    $totals = [
        'communication' => 0,
        'skill' => 0,
        'speed' => 0,
        'overall' => 0,
    ];
    $count = count($reviews);

    if ($count === 0) {
        return [
            'communication' => 0,
            'skill' => 0,
            'speed' => 0,
            'overall' => 0,
            'average' => 0,
        ];
    }

    foreach ($reviews as $review) {
        $totals['communication'] += $review['communication'] ?? 0;
        $totals['skill'] += $review['skill'] ?? 0;
        $totals['speed'] += $review['speed'] ?? 0;
        $totals['overall'] += $review['overall'] ?? 0;
    }

    $aggregated = [
        'communication' => round($totals['communication'] / $count, 2),
        'skill' => round($totals['skill'] / $count, 2),
        'speed' => round($totals['speed'] / $count, 2),
        'overall' => round($totals['overall'] / $count, 2),
    ];

    $aggregated['average'] = number_format(round(array_sum($aggregated) / 4, 2), 1);

    return $aggregated;
}


function mask_guest_style(string $name): string
{
    $name = trim($name);
    if ($name === '')
        return $name;

    if (preg_match('/^(.*)#(\d+)$/', $name, $m)) {
        $base = $m[1];
        $digits = $m[2];
        $start = mb_substr($base, 0, 2);
        $last2 = substr($digits, -2);
        return $start . '***' . $last2; // Gu***43
    }

    // fallback wenn kein "#123"
    $start = mb_substr($name, 0, 2);
    return $start . '***';
}


/**
 * Build <option> list for IANA timezones.
 * Empty value represents "N/A (not set)".
 */
function util_load_timezones_select($current = null)
{
    $current = is_string($current) ? trim($current) : '';
    $selectedEmpty = ($current === '' || $current === null);

    $popular = [
        'Europe/Berlin',
        'Europe/London',
        'Europe/Paris',
        'Europe/Warsaw',
        'Europe/Istanbul',
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles',
        'America/Sao_Paulo',
        'Asia/Dubai',
        'Asia/Kolkata',
        'Asia/Singapore',
        'Asia/Tokyo',
        'Australia/Sydney',
        'UTC',
    ];

    $all = DateTimeZone::listIdentifiers();
    $opts = [];

    // N/A first
    $opts[] = '<option value="" ' . ($selectedEmpty ? 'selected' : '') . '>N/A (not set)</option>';

    // Popular block
    $opts[] = '<optgroup label="Popular">';
    foreach ($popular as $tz) {
        if (!in_array($tz, $all, true)) {
            continue;
        }
        $sel = ($tz === $current) ? 'selected' : '';
        $opts[] = '<option value="' . esc($tz) . '" ' . $sel . '>' . esc($tz) . '</option>';
    }
    $opts[] = '</optgroup>';

    // All timezones
    $opts[] = '<optgroup label="All timezones">';
    foreach ($all as $tz) {
        $sel = ($tz === $current) ? 'selected' : '';
        $opts[] = '<option value="' . esc($tz) . '" ' . $sel . '>' . esc($tz) . '</option>';
    }
    $opts[] = '</optgroup>';

    return implode("\n", $opts);
}

/**
 * Format timezone for display: "Europe/Berlin (UTC+01:00)" or "N/A".
 */
function util_format_timezone_display($tz)
{
    $tz = is_string($tz) ? trim($tz) : '';
    if ($tz === '') {
        return 'N/A';
    }

    try {
        $dt = new DateTime('now', new DateTimeZone($tz));
        $offset = $dt->format('P'); // e.g. +01:00
        return esc($tz) . ' (UTC' . esc($offset) . ')';
    } catch (Exception $e) {
        return 'N/A';
    }
}

/**
 * Booster Setup Guide helpers
 * - Enforces required profile completeness before accessing the Booster Dashboard.
 * - Valorant is intentionally ignored (LoLBoost setup only).
 *
 * Required:
 *  - Discord connected
 *  - Languages selected
 *  - Setup "Next" acknowledged (setup_settings_ack=1)
 *  - Servers selected
 *  - Timezone set
 *  - Description set
 *  - LoL rank set (if booster has LoL enabled)
 *  - Champions selected (if booster has LoL enabled)
 *  - Roles selected (if booster has LoL enabled)
 *  - Payout method exists (at least one; default is ensured by existing payout logic)
 */

function booster_setup_status(int $booster_id): array
{
    global $db;

    $booster = db_get_row('boosters', ['id' => $booster_id], 1);
    if (!$booster) {
        return [
            'complete' => false,
            'percent' => 0,
            'missing' => ['booster_not_found'],
            'steps' => [],
            'start_step' => 1,
        ];
    }

    // Ensure profile row exists
    $profile = db_get_row('booster_profiles', ['booster_id' => $booster_id], 1);
    if (empty($profile)) {
        db_add_row('booster_profiles', ['booster_id' => $booster_id]);
        $profile = db_get_row('booster_profiles', ['booster_id' => $booster_id], 1);
    }

    $games = array_values(array_filter(explode('|', (string)($booster['games'] ?? ''))));
    if (empty($games)) {
        $games = ['league-of-legends'];
    }

    $has_lol = in_array('league-of-legends', $games, true);
    $has_val = in_array('valorant', $games, true);
    $has_tft = in_array('teamfight-tactics', $games, true);

    $has_text = function ($v): bool {
        return is_string($v) && trim($v) !== '';
    };

    $has_pipe_list = function ($v): bool {
        if (!is_string($v) || trim($v) === '') return false;
        $arr = array_values(array_filter(array_map('trim', explode('|', $v))));
        return count($arr) > 0;
    };

    // profile media requirements
    $default_icon = 'https://lolboost.gg/public/uploads/icons/default.png';
    $icon_raw = trim((string)($booster['icon'] ?? ''));
    $cover_raw = $booster['cover'] ?? null;

    $has_custom_icon = ($icon_raw !== ''
        && $icon_raw !== $default_icon
        && strpos($icon_raw, '/uploads/icons/default.png') === false);

    $has_custom_cover = !(is_null($cover_raw) || (is_string($cover_raw) && trim($cover_raw) === ''));

    // payout: require at least 1 method (default is ensured by payout save logic)
    $has_payout = $db->row("SELECT id FROM booster_payout_methods WHERE booster_id=? LIMIT 1", $booster_id) ? true : false;

    $steps = [];

    $steps['discord'] = [
        'label' => 'Connect Discord',
        'done'  => $has_text($booster['discord_id'] ?? ''),
    ];

    $steps['profile_picture'] = [
        'label' => 'Upload profile picture',
        'done'  => $has_custom_icon,
    ];

    $steps['banner'] = [
        'label' => 'Upload profile banner',
        'done'  => $has_custom_cover,
    ];

    $steps['languages'] = [
        'label' => 'Select languages',
        'done'  => $has_pipe_list($booster['languages'] ?? ''),
    ];

    // This is NOT a toggle requirement. It simply means the user pressed "Next" once on the settings step.
    $steps['settings_ack'] = [
        'label' => 'Confirm setup preferences',
        'done'  => ((int)($booster['setup_settings_ack'] ?? 0) === 1),
    ];

    $steps['servers'] = [
        'label' => 'Select servers',
        'done'  => $has_pipe_list($profile['servers'] ?? ''),
    ];

    $steps['timezone'] = [
        'label' => 'Set timezone',
        'done'  => $has_text($profile['timezone'] ?? ''),
    ];

    $steps['description'] = [
        'label' => 'Write a profile description',
        'done'  => $has_text($profile['description'] ?? ''),
    ];

    if ($has_lol) {
        $lol_rank = (string)($profile['lol_rank'] ?? '');
        $steps['lol_rank'] = [
            'label' => 'League of Legends rank',
            'done'  => $has_text($lol_rank) && $lol_rank !== '0|0',
        ];
        $steps['champions'] = [
            'label' => 'LoL champions',
            'done'  => $has_pipe_list($profile['champions'] ?? ''),
        ];
        $steps['roles'] = [
            'label' => 'LoL roles',
            'done'  => $has_pipe_list($profile['roles'] ?? ''),
        ];
    }

    if ($has_val) {
        $val_rank = (string)($profile['val_rank'] ?? '');
        $steps['val_rank'] = [
            'label' => 'Valorant rank',
            'done'  => $has_text($val_rank) && $val_rank !== '0|0',
        ];
        $steps['agents'] = [
            'label' => 'Valorant agents',
            'done'  => $has_pipe_list($profile['agents'] ?? ''),
        ];
    }

    if ($has_tft) {
        $tft_rank = (string)($profile['tft_rank'] ?? '');
        $steps['tft_rank'] = [
            'label' => 'Teamfight Tactics rank',
            'done'  => $has_text($tft_rank) && $tft_rank !== '0|0',
        ];
    }

    $steps['payout'] = [
        'label' => 'Payout settings',
        'done'  => $has_payout,
    ];

    $total = count($steps);
    $done = 0;
    $missing = [];
    foreach ($steps as $k => $s) {
        if (!empty($s['done'])) {
            $done++;
        } else {
            $missing[] = $k;
        }
    }

    $percent = $total > 0 ? (int)floor(($done / $total) * 100) : 0;

    // Determine wizard start step (server-side, refresh-safe)
    $step5_missing = false;
    if ($has_lol && (!$steps['lol_rank']['done'] || !$steps['champions']['done'] || !$steps['roles']['done'])) {
        $step5_missing = true;
    }
    if ($has_val && (!$steps['val_rank']['done'] || !$steps['agents']['done'])) {
        $step5_missing = true;
    }
    if ($has_tft && !$steps['tft_rank']['done']) {
        $step5_missing = true;
    }

    if (!$steps['discord']['done']) {
        $start_step = 1;
    } elseif (!$steps['profile_picture']['done'] || !$steps['banner']['done']) {
        $start_step = 2;
    } elseif (!$steps['languages']['done'] || !$steps['settings_ack']['done']) {
        $start_step = 3;
    } elseif (!$steps['servers']['done'] || !$steps['timezone']['done'] || !$steps['description']['done']) {
        $start_step = 4;
    } elseif ($step5_missing) {
        $start_step = 5;
    } elseif (!$steps['payout']['done']) {
        $start_step = 6;
    } else {
        $start_step = 6;
    }

    // Fetch payout methods (for display in setup page)
    $methods = $db->run("SELECT * FROM booster_payout_methods WHERE booster_id=? ORDER BY is_default DESC, id DESC", $booster_id);

    return [
        'complete' => ($done === $total),
        'percent'  => $percent,
        'steps'    => $steps,
        'missing'  => $missing,
        'games'    => $games,
        'booster'  => $booster,
        'profile'  => $profile,
        'start_step' => $start_step,
        'payout_methods' => $methods,
    ];
}

function booster_setup_is_complete(int $booster_id): bool
{
    $st = booster_setup_status($booster_id);
    return !empty($st['complete']);
}

// =========================
// E-Girl Setup Status
// =========================

function egirl_setup_status(int $egirl_id): array
{
    global $db;

    $booster = db_get_row('boosters', ['id' => $egirl_id], 1);
    if (!$booster) {
        return [
            'complete'   => false,
            'percent'    => 0,
            'missing'    => ['egirl_not_found'],
            'steps'      => [],
            'start_step' => 1,
        ];
    }

    // Ensure egirl_profiles row exists
    $profile = db_get_row('egirl_profiles', ['egirl_id' => $egirl_id], 1);
    if (empty($profile)) {
        db_add_row('egirl_profiles', ['egirl_id' => $egirl_id]);
        $profile = db_get_row('egirl_profiles', ['egirl_id' => $egirl_id], 1);
    }

    $has_text = function ($v): bool {
        return is_string($v) && trim($v) !== '';
    };

    $has_pipe_list = function ($v): bool {
        if (!is_string($v) || trim($v) === '') return false;
        $arr = array_values(array_filter(array_map('trim', explode('|', $v))));
        return count($arr) > 0;
    };

    // Media
    $default_icon    = 'https://lolboost.gg/public/uploads/icons/default.png';
    $icon_raw        = trim((string)($booster['icon'] ?? ''));
    $cover_raw       = $booster['cover'] ?? null;
    $has_custom_icon = ($icon_raw !== '' && $icon_raw !== $default_icon && strpos($icon_raw, '/uploads/icons/default.png') === false);
    $has_custom_cover = !(is_null($cover_raw) || (is_string($cover_raw) && trim($cover_raw) === ''));

    // Payout
    $has_payout = $db->row("SELECT id FROM booster_payout_methods WHERE booster_id=? LIMIT 1", $egirl_id) ? true : false;

    // Bio — stored in egirl_profiles
    $bio_val = trim((string)($profile['bio'] ?? ''));

    // Languages — stored in egirl_profiles
    $langs_val = trim((string)($profile['languages'] ?? $booster['languages'] ?? ''));

    // Games — stored in egirl_profiles
    $games_val = trim((string)($profile['games'] ?? $booster['games'] ?? ''));

    $steps = [];

    $steps['discord'] = [
        'label' => 'Connect Discord',
        'done'  => $has_text($booster['discord_id'] ?? '') || $has_text($booster['discord'] ?? ''),
    ];

    $steps['profile_picture'] = [
        'label' => 'Upload profile picture',
        'done'  => $has_custom_icon,
    ];

    $steps['banner'] = [
        'label' => 'Upload profile banner',
        'done'  => $has_custom_cover,
    ];

    $steps['bio'] = [
        'label' => 'Write a bio',
        'done'  => (strlen($bio_val) >= 30),
    ];

    $steps['languages'] = [
        'label' => 'Select languages',
        'done'  => $has_pipe_list($langs_val),
    ];

    $steps['games'] = [
        'label' => 'Select games',
        'done'  => $has_pipe_list($games_val),
    ];

    $steps['payout'] = [
        'label' => 'Payout settings',
        'done'  => $has_payout,
    ];

    $total   = count($steps);
    $done    = 0;
    $missing = [];
    foreach ($steps as $k => $s) {
        if (!empty($s['done'])) {
            $done++;
        } else {
            $missing[] = $k;
        }
    }

    $percent = $total > 0 ? (int)floor(($done / $total) * 100) : 0;

    // Determine wizard start step
    if (!$steps['discord']['done']) {
        $start_step = 1;
    } elseif (!$steps['profile_picture']['done'] || !$steps['banner']['done']) {
        $start_step = 2;
    } elseif (!$steps['bio']['done'] || !$steps['languages']['done'] || !$steps['games']['done']) {
        $start_step = 3;
    } elseif (!$steps['payout']['done']) {
        $start_step = 4;
    } else {
        $start_step = 4;
    }

    $methods = $db->run("SELECT * FROM booster_payout_methods WHERE booster_id=? ORDER BY is_default DESC, id DESC", $egirl_id);

    return [
        'complete'       => ($done === $total),
        'percent'        => $percent,
        'steps'          => $steps,
        'missing'        => $missing,
        'booster'        => $booster,
        'profile'        => $profile,
        'start_step'     => $start_step,
        'payout_methods' => $methods,
    ];
}

function egirl_setup_is_complete(int $egirl_id): bool
{
    $st = egirl_setup_status($egirl_id);
    return !empty($st['complete']);
}

// =========================
// Giveaway System (Tickets)
// =========================

function giveaway_ticket_events_insert(array $data): bool
{
    global $db;
    static $cols = null;

    if ($cols === null) {
        $cols = [];
        try {
            $rows = $db->run("SHOW COLUMNS FROM giveaway_ticket_events");
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    if (!empty($r['Field'])) $cols[$r['Field']] = true;
                }
            }
        } catch (Exception $e) {
            // If we can't introspect, just attempt raw insert.
            $cols = null;
        }
    }

    if (is_array($cols)) {
        $filtered = [];
        foreach ($data as $k => $v) {
            if (isset($cols[$k])) $filtered[$k] = $v;
        }
        $data = $filtered;
    }

    return db_add_row('giveaway_ticket_events', $data) ? true : false;
}

function giveaway_db_first(string $sql, ...$params)
{
    global $db;
    $rows = $db->run($sql, ...$params);
    if (is_array($rows) && count($rows) > 0) return $rows[0];
    return null;
}

function giveaway_get_active()
{
    global $db;
    try {
        $row = giveaway_db_first(
            "SELECT * FROM giveaways WHERE status = 'ACTIVE' AND NOW() BETWEEN starts_at AND ends_at ORDER BY starts_at DESC LIMIT 1"
        );
        return $row ?: false;
    } catch (Exception $e) {
        return false;
    }
}

function giveaway_mask_username(string $username): string
{
    $u = trim($username);
    if ($u === '') return '';
    if (mb_strlen($u) <= 2) {
        return mb_substr($u, 0, 1) . '**';
    }
    $first = mb_substr($u, 0, 1);
    $last = mb_substr($u, -1, 1);
    return $first . '**' . $last;
}

function giveaway_grant_invoice_ticket(int $invoice_id): bool
{
    if ($invoice_id <= 0) return false;

    global $db;

    // Must exist and be paid
    $invoice = db_get_row('invoices', ['id' => $invoice_id]);
    if (!$invoice || strtoupper((string)($invoice['status'] ?? '')) !== 'PAID') {
        return false;
    }

    $giveaway = giveaway_get_active();
    if (!$giveaway) return false;

    // Ticket already granted for this invoice in this giveaway?
    $exists = db_get_row('giveaway_ticket_events', [
        'giveaway_id' => (int)$giveaway['id'],
        'invoice_id' => $invoice_id,
                'source_type' => 'ORDER',
'select' => 'id,status,delta',
    ], 1);

    if (!empty($exists)) {
        // If it exists but was revoked earlier, re-apply by flipping status and re-adding delta
        if (strtoupper((string)($exists['status'] ?? '')) === 'REVOKED') {
            $db->run(
                "UPDATE giveaway_ticket_events SET status='APPLIED', revoked_at=NULL, revoke_reason=NULL WHERE id = ?",
                (int)$exists['id']
            );
            giveaway_apply_ticket_delta((int)$giveaway['id'], (int)$invoice['client_id'], (int)($exists['delta'] ?? 1));
            return true;
        }
        return true;
    }

    // Insert event
    $ok = giveaway_ticket_events_insert([
        'giveaway_id' => (int)$giveaway['id'],
        'client_id' => (int)$invoice['client_id'],
        'invoice_id' => $invoice_id,
                'source_type' => 'ORDER',
'purchase_type' => $invoice['order_type'] ?? null,
        'delta' => 1,
        'status' => 'APPLIED',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    if ($ok) {
        giveaway_apply_ticket_delta((int)$giveaway['id'], (int)$invoice['client_id'], 1);
        return true;
    }

    return false;
}

function giveaway_revoke_invoice_ticket(int $invoice_id, string $reason = 'unpaid_or_refunded'): bool
{
    if ($invoice_id <= 0) return false;

    global $db;

    $ev = giveaway_db_first(
        "SELECT * FROM giveaway_ticket_events WHERE invoice_id = ? AND status = 'APPLIED' ORDER BY id DESC LIMIT 1",
        $invoice_id
    );

    if (empty($ev)) return false;

    // If giveaway is already drawn, we don't mutate past leaderboards.
    $gw = db_get_row('giveaways', ['id' => (int)$ev['giveaway_id']]);
    if (!empty($gw) && strtoupper((string)$gw['status']) === 'DRAWN') {
        return false;
    }

    $db->run(
        "UPDATE giveaway_ticket_events SET status='REVOKED', revoked_at=NOW(), revoke_reason=? WHERE id = ?",
        $reason,
        (int)$ev['id']
    );

    giveaway_apply_ticket_delta((int)$ev['giveaway_id'], (int)$ev['client_id'], -abs((int)($ev['delta'] ?? 1)));
    return true;
}

function giveaway_apply_ticket_delta(int $giveaway_id, int $client_id, int $delta): void
{
    if ($giveaway_id <= 0 || $client_id <= 0 || $delta === 0) return;

    global $db;
    $row = db_get_row('giveaway_tickets', ['giveaway_id' => $giveaway_id, 'client_id' => $client_id]);
    if ($row) {
        $new = max(0, (int)$row['tickets'] + $delta);
        db_update_row('giveaway_tickets', ['giveaway_id' => $giveaway_id, 'client_id' => $client_id], [
            'tickets' => $new,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    } else {
        db_add_row('giveaway_tickets', [
            'giveaway_id' => $giveaway_id,
            'client_id' => $client_id,
            'tickets' => max(0, $delta),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

function giveaway_admin_adjust_tickets(int $giveaway_id, int $client_id, int $delta, int $admin_id, string $note = ''): bool
{
    if ($giveaway_id <= 0 || $client_id <= 0 || $delta === 0) return false;
    $gw = db_get_row('giveaways', ['id' => $giveaway_id]);
    if (!$gw || strtoupper((string)$gw['status']) !== 'ACTIVE') return false;

    $ok = giveaway_ticket_events_insert([
        'giveaway_id' => $giveaway_id,
        'client_id' => $client_id,
        'invoice_id' => null,
                'source_type' => 'MANUAL',
'purchase_type' => 'manual',
        'delta' => $delta,
        'status' => 'APPLIED',
        'admin_id' => $admin_id,
        'note' => $note,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    if ($ok) {
        giveaway_apply_ticket_delta($giveaway_id, $client_id, $delta);
        return true;
    }
    return false;
}

function giveaway_draw_winners(int $giveaway_id): array
{
    global $db;
    $gw = db_get_row('giveaways', ['id' => $giveaway_id]);
    if (!$gw) return ['ok' => false, 'error' => 'Giveaway not found'];

    if (strtoupper((string)$gw['status']) === 'DRAWN') {
        return ['ok' => false, 'error' => 'Already drawn'];
    }

    $winners_count = max(1, (int)($gw['winners_count'] ?? 1));
    $participants = $db->rows(
        "SELECT gt.client_id, gt.tickets FROM giveaway_tickets gt WHERE gt.giveaway_id = ? AND gt.tickets > 0 ORDER BY gt.tickets DESC",
        $giveaway_id
    );

    if (empty($participants)) {
        return ['ok' => false, 'error' => 'No participants'];
    }

    // Weighted draw without replacement
    $pool = [];
    $total = 0;
    foreach ($participants as $p) {
        $tickets = max(0, (int)$p['tickets']);
        if ($tickets <= 0) continue;
        $total += $tickets;
        $pool[] = [
            'client_id' => (int)$p['client_id'],
            'tickets' => $tickets,
        ];
    }
    if ($total <= 0) return ['ok' => false, 'error' => 'No tickets'];

    $selected = [];
    for ($rank = 1; $rank <= $winners_count; $rank++) {
        if ($total <= 0 || empty($pool)) break;

        $r = random_int(1, $total);
        $acc = 0;
        $picked_idx = null;
        foreach ($pool as $idx => $p) {
            $acc += (int)$p['tickets'];
            if ($r <= $acc) {
                $picked_idx = $idx;
                break;
            }
        }
        if ($picked_idx === null) break;

        $picked = $pool[$picked_idx];
        $selected[] = [
            'rank' => $rank,
            'client_id' => (int)$picked['client_id'],
            'tickets_at_draw' => (int)$picked['tickets'],
        ];

        // remove from pool
        $total -= (int)$picked['tickets'];
        array_splice($pool, $picked_idx, 1);
    }

    // Save winners (clear previous just in case)
    $db->run("DELETE FROM giveaway_winners WHERE giveaway_id = ?", $giveaway_id);
    foreach ($selected as $w) {
        db_add_row('giveaway_winners', [
            'giveaway_id' => $giveaway_id,
            'rank' => (int)$w['rank'],
            'client_id' => (int)$w['client_id'],
            'tickets_at_draw' => (int)$w['tickets_at_draw'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    db_update_row('giveaways', ['id' => $giveaway_id], [
        'status' => 'DRAWN',
        'drawn_at' => date('Y-m-d H:i:s'),
    ]);

    return ['ok' => true, 'winners' => $selected];
}



/**
 * Abandoned checkout reminder:
 * - Finds UNPAID orders older than 24 hours (order_type='order')
 * - Generates a one-time token and expiry for an extra -10% (applied on /checkout/:uuid when ?ab=TOKEN is present)
 * - Creates an email notification (type: abandoned_unpaid_order_reminder)
 *
 * IMPORTANT: Does NOT modify total_price here.
 */
function cron_send_abandoned_unpaid_order_emails(int $limit = 250): array
{
    global $db;

    $cutoff = date('Y-m-d H:i:s', time() - (24 * 3600));

    $sql = "
        SELECT
            i.id   AS invoice_id,
            i.uuid AS invoice_uuid,
            i.client_id,
            i.order_id,
            i.order_type,
            i.abandoned_email_sent_at,
            i.abandoned_bonus_token,
            i.abandoned_discount_expires_at,
            o.status AS order_status,
            o.created_at AS order_created_at
        FROM invoices i
        INNER JOIN orders o ON o.id = i.order_id
        WHERE
            i.order_type = 'order'
            AND i.client_id IS NOT NULL
            AND i.client_id != 0
            AND (i.abandoned_email_sent_at IS NULL OR i.abandoned_email_sent_at = '')
            AND o.status = 'UNPAID'
            AND o.created_at <= ?
        ORDER BY o.created_at ASC
        LIMIT {$limit}
    ";

    $rows = $db->run($sql, [$cutoff]);
    if (empty($rows)) {
        return ['ok' => true, 'processed' => 0];
    }

    $processed = 0;

    foreach ($rows as $row) {
        $invoiceId = (int)($row['invoice_id'] ?? 0);
        $clientId  = (int)($row['client_id'] ?? 0);
        $uuid      = (string)($row['invoice_uuid'] ?? '');

        if ($invoiceId <= 0 || $clientId <= 0 || $uuid === '') {
            continue;
        }

        $client = db_get_row('clients', ['id' => $clientId], true);
        if (empty($client) || empty($client['email'])) {
            // prevent endless retry loops
            db_update_row('invoices', ['id' => $invoiceId], ['abandoned_email_sent_at' => date('Y-m-d H:i:s')]);
            continue;
        }

        // If token already exists, re-use it (avoid creating multiple valid tokens)
        $token = (string)($row['abandoned_bonus_token'] ?? '');
        if ($token === '') {
            try {
                $token = bin2hex(random_bytes(16)); // 32 hex chars
            } catch (Exception $e) {
                // fallback
                $token = md5($invoiceId . '|' . $clientId . '|' . microtime(true));
            }
        }

        $expires = (string)($row['abandoned_discount_expires_at'] ?? '');
        if ($expires === '') {
            $expires = date('Y-m-d H:i:s', time() + (72 * 3600)); // 72h validity
        }

        // Mark invoice as reminded + store token/expiry
        db_update_row('invoices', ['id' => $invoiceId], [
            'abandoned_email_sent_at' => date('Y-m-d H:i:s'),
            'abandoned_bonus_token' => $token,
            'abandoned_discount_expires_at' => $expires,
        ]);

        // Create email notification
        db_add_row('notifications', [
            'type' => 'abandoned_unpaid_order_reminder',
            'data' => json_encode([
                'invoice_uuid' => base64_encode($uuid),
                'ab_token' => base64_encode($token),
                'discount_percent' => base64_encode('10'),
                'expires_at' => base64_encode($expires),
                'order_id' => base64_encode((string)($row['order_id'] ?? '')),
            ]),
            'recipient' => 'client',
            'recipient_id' => $clientId,
            'is_web' => 0,
            'is_email' => 1,
            'created_at' => $now,
        ]);

        $processed++;
    }

    return ['ok' => true, 'processed' => $processed];
}


/**
 * Sends one birthday discount email per client per year.
 *
 * Required DB columns:
 * - clients.birthday DATE NULL
 * - clients.birthday_coupon_sent_year INT NULL
 *
 * The generated coupon is valid for 48 hours and is limited to one total use
 * and one use by the birthday client.
 */
function cron_send_birthday_discount_emails(int $limit = 250): array
{
    global $db;

    $debug = [];
    $errors = [];
    $created = [];

    $limit = max(1, min(1000, (int)$limit));
    $todayMonthDay = date('m-d');
    $year = (int)date('Y');
    $now = date('Y-m-d H:i:s');
    $expires = date('Y-m-d H:i:s', time() + (48 * 3600));
    $discountPercent = 30;

    $debug[] = 'Server now: ' . $now;
    $debug[] = 'Today month-day: ' . $todayMonthDay;
    $debug[] = 'Current year: ' . $year;

    $sql = "
        SELECT id, username, email, birthday, birthday_coupon_sent_year
        FROM clients
        WHERE birthday IS NOT NULL
          AND birthday != ''
          AND DATE_FORMAT(birthday, '%m-%d') = ?
          AND (birthday_coupon_sent_year IS NULL OR birthday_coupon_sent_year != ?)
          AND email IS NOT NULL
          AND email != ''
        ORDER BY id ASC
        LIMIT {$limit}
    ";

    try {
        $clients = $db->run($sql, [$todayMonthDay, $year]);
    } catch (Throwable $e) {
        // Fallback for DB wrappers that do not support bound params in run().
        $todayMonthDaySql = esc($todayMonthDay);
        $sqlFallback = "
            SELECT id, username, email, birthday, birthday_coupon_sent_year
            FROM clients
            WHERE birthday IS NOT NULL
              AND birthday != ''
              AND DATE_FORMAT(birthday, '%m-%d') = '{$todayMonthDaySql}'
              AND (birthday_coupon_sent_year IS NULL OR birthday_coupon_sent_year != {$year})
              AND email IS NOT NULL
              AND email != ''
            ORDER BY id ASC
            LIMIT {$limit}
        ";
        $debug[] = 'Prepared query failed, used fallback SQL: ' . $e->getMessage();
        $clients = $db->run($sqlFallback);
    }

    $matched = is_array($clients) ? count($clients) : 0;
    $debug[] = 'Matched eligible clients: ' . $matched;

    if (empty($clients)) {
        return [
            'ok' => true,
            'processed' => 0,
            'matched' => 0,
            'created' => [],
            'errors' => [],
            'debug' => $debug,
        ];
    }

    $processed = 0;

    foreach ($clients as $client) {
        $clientId = (int)($client['id'] ?? 0);
        $email = trim((string)($client['email'] ?? ''));
        $username = (string)($client['username'] ?? 'there');

        $debug[] = 'Client candidate: id=' . $clientId . ', email=' . $email . ', birthday=' . ($client['birthday'] ?? '') . ', sent_year=' . (($client['birthday_coupon_sent_year'] ?? '') === null ? 'NULL' : (string)($client['birthday_coupon_sent_year'] ?? 'NULL'));

        if ($clientId <= 0 || $email === '') {
            $errors[] = 'Skipped invalid client row: ' . json_encode($client);
            continue;
        }

        $code = '';
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                $random = strtoupper(bin2hex(random_bytes(3)));
            } catch (Exception $e) {
                $random = strtoupper(substr(md5($clientId . '|' . microtime(true) . '|' . $attempt), 0, 6));
            }

            $candidate = 'BDAY-' . $clientId . '-' . $random;
            if (!db_get_row('discounts', ['code' => $candidate])) {
                $code = $candidate;
                break;
            }
        }

        if ($code === '') {
            $errors[] = 'Could not generate unique code for client_id=' . $clientId;
            continue;
        }

        try {
            $discountId = db_add_row('discounts', [
                'admin_id' => 0,
                'code' => $code,
                'uses' => 0,
                'max_uses' => 1,
                'max_uses_client' => 1,
                'services' => 'boosting,coaching',
                'amount' => $discountPercent,
                'is_fixed' => 0,
                'status' => 1,
                'starts_at' => $now,
                'expires_at' => $expires,
                'created_at' => $now,
            ]);
        } catch (Throwable $e) {
            $errors[] = 'Discount insert failed for client_id=' . $clientId . ': ' . $e->getMessage();
            continue;
        }

        if (!$discountId) {
            $errors[] = 'Discount insert returned empty ID for client_id=' . $clientId;
            continue;
        }

        $notificationPayload = [
            'type' => 'birthday_discount',
            'data' => json_encode([
                'preheader' => base64_encode('Happy birthday! Your personal LoLBoost.gg discount is ready.'),
                'username' => base64_encode($username),
                'discount_code' => base64_encode($code),
                'discount_percent' => base64_encode((string)$discountPercent),
                'expires_at' => base64_encode($expires),
                'discount_url' => base64_encode(BASE_URL . '/lol-boosting'),
            ]),
            'recipient' => 'client',
            'recipient_id' => $clientId,
            'is_seen' => 0,
            'is_sent' => 0,
            'is_discord' => 0,
            'is_email' => 1,
            'is_web' => 0,
            'is_fail' => 0,
            'created_at' => $now,
        ];

        try {
            $notificationId = db_add_row('notifications', $notificationPayload);
        } catch (Throwable $e) {
            $errors[] = 'Notification insert failed for client_id=' . $clientId . ', discount_id=' . $discountId . ': ' . $e->getMessage();
            continue;
        }

        if (!$notificationId) {
            $errors[] = 'Notification insert returned empty ID for client_id=' . $clientId . ', discount_id=' . $discountId;
            continue;
        }

        // Mark yearly send only after both discount and notification were created.
        try {
            db_update_row('clients', ['id' => $clientId], [
                'birthday_coupon_sent_year' => $year,
            ]);
        } catch (Throwable $e) {
            $errors[] = 'Client sent_year update failed for client_id=' . $clientId . ': ' . $e->getMessage();
            // Do not undo the created coupon/notification; report it.
        }

        $processed++;
        $created[] = [
            'client_id' => $clientId,
            'discount_id' => $discountId,
            'notification_id' => $notificationId,
            'code' => $code,
            'expires_at' => $expires,
        ];
    }

    return [
        'ok' => true,
        'processed' => $processed,
        'matched' => $matched,
        'created' => $created,
        'errors' => $errors,
        'debug' => $debug,
    ];
}

/**
 * Pins an internal self-call to 127.0.0.1 so it never leaves the server.
 *
 * BASE_URL points at Cloudflare, so with "Under Attack" mode on, server-to-server
 * calls between our own PHP tasks get the JS challenge (HTTP 403) instead of the
 * real response. The Host header stays untouched, so vhost routing and TLS SNI
 * still work; only certificate validation is dropped because the loopback cert
 * does not match the public hostname.
 */
function lb_internal_curl_use_loopback($ch, string $url): void
{
    $parts = parse_url($url);
    $host = (string)($parts['host'] ?? '');
    if ($host === '') {
        return;
    }
    $port = (int)($parts['port'] ?? (($parts['scheme'] ?? 'https') === 'https' ? 443 : 80));

    curl_setopt($ch, CURLOPT_RESOLVE, [$host . ':' . $port . ':127.0.0.1']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
}

/**
 * Sends everything buffered so far to the browser and closes the connection, while
 * PHP keeps running.
 *
 * Needed because several actions do slow outbound HTTP (Discord DMs) after all the
 * user-visible work is already done. Without this the admin stares at a spinner for
 * the full round-trip to Discord even though the order was updated in the first
 * few hundred milliseconds.
 *
 * Availability differs per SAPI, so try both and fall back to a plain flush. The
 * fallback does not actually close the connection — the request just behaves as
 * before, which is the old (slow but correct) behaviour.
 */
function lb_finish_request_early(): void
{
    if (function_exists('fastcgi_finish_request')) {
        @fastcgi_finish_request();
        return;
    }
    // LiteSpeed (Hostinger's default) exposes its own equivalent.
    if (function_exists('litespeed_finish_request')) {
        @litespeed_finish_request();
        return;
    }
    while (ob_get_level() > 0) {
        @ob_end_flush();
    }
    @flush();
}

function trigger_notification_sender_async(): void
{
    $url = rtrim(BASE_URL, '/') . '/app/tasks/notification_sender.php';

    for ($attempt = 0; $attempt < 4; $attempt++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 750);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: close']);
        lb_internal_curl_use_loopback($ch, $url);
        $response = @curl_exec($ch);
        $curlError = curl_errno($ch);
        curl_close($ch);

        // Loopback not reachable (e.g. no local HTTPS listener) — fall back to the
        // normal public route so notifications still go out.
        if ($curlError === CURLE_COULDNT_CONNECT || $curlError === CURLE_SSL_CONNECT_ERROR) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 750);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: close']);
            $response = @curl_exec($ch);
            $curlError = curl_errno($ch);
            curl_close($ch);
        }

        // A timeout means the worker started and continues after disconnect.
        // Retry only when another worker explicitly reports a busy lock.
        if ($curlError !== 0 || trim((string)$response) !== 'BUSY') {
            break;
        }
        usleep(250000);
    }
}

// ============================================================
// SELLER #28 / ADMIN #51 ACCOUNT PAYOUT FUNCTIONS
// ============================================================
// Package accounts sold via admin_id=51 count toward seller #28.
// Payout: 70% of package price (fee: 30%).
// Tracked in seller_payments with type='admin_account_payout'.
// ============================================================

function seller28_admin_account_already_paid(int $accounts_id): bool
{
    global $db;
    try {
        $count = (int)$db->single(
            "SELECT COUNT(*) FROM seller_payments
             WHERE seller_id = 28
               AND type = 'admin_account_payout'
               AND note LIKE ?",
            ["Admin Account #" . $accounts_id . " sold%"]
        );
        return $count > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function seller28_pay_admin_account(int $accounts_id): array
{
    global $db;

    if ($accounts_id <= 0) {
        return ['ok' => false, 'message' => 'Invalid account ID', 'amount_cents' => 0];
    }

    if (seller28_admin_account_already_paid($accounts_id)) {
        return ['ok' => false, 'message' => "Account #{$accounts_id} already paid", 'amount_cents' => 0];
    }

    // Load account + package price
    $account = $db->row(
        "SELECT a.*, ap.price AS package_price
         FROM accounts a
         LEFT JOIN account_packages ap ON ap.id = a.package_id
         WHERE a.id = ? AND a.admin_id = 51 AND a.status = 1 AND a.client_id IS NOT NULL
         LIMIT 1",
        $accounts_id
    );

    if (empty($account)) {
        return ['ok' => false, 'message' => "Account #{$accounts_id} not found or not sold via admin #51", 'amount_cents' => 0];
    }

    $seller = $db->row("SELECT * FROM sellers WHERE id = 28 LIMIT 1");
    if (empty($seller) || !empty($seller['is_banned'])) {
        return ['ok' => false, 'message' => 'Seller #28 not found or banned', 'amount_cents' => 0];
    }

    $sale_price_cents = (int)($account['package_price'] ?? 0);
    if ($sale_price_cents <= 0) {
        return ['ok' => false, 'message' => "No price found for account #{$accounts_id}", 'amount_cents' => 0];
    }

    $fee_percent = 30;
    $seller_cut  = (int)round($sale_price_cents * 0.70);
    $old_balance = (int)($seller['balance'] ?? 0);
    $new_balance = $old_balance + $seller_cut;

    try {
        $db->run("UPDATE sellers SET balance = ? WHERE id = 28", $new_balance);

        db_add_row('seller_payments', [
            'seller_id'     => 28,
            'account_id'    => null,
            'type'          => 'admin_account_payout',
            'amount_cents'  => $seller_cut,
            'note'          => "Admin Account #" . $accounts_id . " sold - fee: " . $fee_percent . "%",
            'balance_after' => $new_balance,
        ]);

        process_seller_rank(28);

        return [
            'ok'           => true,
            'message'      => "Paid EUR " . number_format($seller_cut / 100, 2) . " for account #" . $accounts_id,
            'amount_cents' => $seller_cut,
        ];

    } catch (\Throwable $e) {
        return ['ok' => false, 'message' => 'DB error: ' . $e->getMessage(), 'amount_cents' => 0];
    }
}

function seller28_sync_admin_account_payouts(): array
{
    global $db;

    // Find all sold admin #51 accounts that have no payout yet
    $accounts = $db->run(
        "SELECT a.id
         FROM accounts a
         WHERE a.admin_id = 51
           AND a.status = 1
           AND a.client_id IS NOT NULL
           AND NOT EXISTS (
               SELECT 1 FROM seller_payments sp
               WHERE sp.seller_id = 28
                 AND sp.type = 'admin_account_payout'
                 AND sp.note LIKE CONCAT('Admin Account #', a.id, ' sold%')
           )
         ORDER BY a.sold_at ASC, a.id ASC"
    ) ?: [];

    $results = ['processed' => 0, 'paid' => 0, 'skipped' => 0, 'errors' => [], 'total_cents' => 0];

    foreach ($accounts as $row) {
        $results['processed']++;
        $result = seller28_pay_admin_account((int)$row['id']);
        if ($result['ok']) {
            $results['paid']++;
            $results['total_cents'] += $result['amount_cents'];
        } else {
            if (strpos($result['message'], 'already paid') !== false) {
                $results['skipped']++;
            } else {
                $results['errors'][] = $result['message'];
            }
        }
    }

    return $results;
}


if (!function_exists('lb_referral_setting_key')) {
    define('LB_REFERRAL_COOKIE', 'lb_referral_code');
    define('LB_REFERRAL_COOKIE_DAYS', 30);

    function lb_referral_setting_key($key)
    {
        return 'referral_' . trim((string)$key);
    }

    function lb_referral_get_setting($key, $default = null)
    {
        global $db;

        $setting_key = lb_referral_setting_key($key);
        $row = $db->row("SELECT setting_value FROM referral_settings WHERE setting_key = ? LIMIT 1", $setting_key);

        if (!$row || !array_key_exists('setting_value', $row)) {
            return $default;
        }

        return $row['setting_value'];
    }

    function lb_referral_get_settings()
    {
        return [
            'enabled' => (int) lb_referral_get_setting('enabled', 1),
            'client_reward_percent' => (float) lb_referral_get_setting('client_reward_percent', 5),
            'booster_reward_percent' => (float) lb_referral_get_setting('booster_reward_percent', 5),
            'seller_reward_percent' => (float) lb_referral_get_setting('seller_reward_percent', 5),
            'min_order_cents' => (int) lb_referral_get_setting('min_order_cents', 0),
            'cookie_days' => (int) lb_referral_get_setting('cookie_days', LB_REFERRAL_COOKIE_DAYS),
            'require_completed' => (int) lb_referral_get_setting('require_completed', 1),
            'block_same_client_account' => (int) lb_referral_get_setting('block_same_client_account', 1),
            'block_same_email' => (int) lb_referral_get_setting('block_same_email', 1),
            'allow_booster_referrals' => (int) lb_referral_get_setting('allow_booster_referrals', 1),
            'allow_client_referrals' => (int) lb_referral_get_setting('allow_client_referrals', 1),
            'allow_seller_referrals' => (int) lb_referral_get_setting('allow_seller_referrals', 1),
        ];
    }

    function lb_referral_set_setting($key, $value)
    {
        $setting_key = lb_referral_setting_key($key);
        $row = db_get_row('referral_settings', ['setting_key' => $setting_key], 1);

        if ($row) {
            return db_update_row('referral_settings', ['id' => $row['id']], [
                'setting_value' => (string)$value,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        db_add_row('referral_settings', [
            'setting_key' => $setting_key,
            'setting_value' => (string)$value,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    function lb_referral_public_slug($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            $value = mb_strtolower($value, 'UTF-8');
        } else {
            $value = strtolower($value);
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = rawurldecode($value);
        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^\pL\pN\-_]/u', '', $value);
        $value = preg_replace('/-+/', '-', $value);

        return trim($value, '-_');
    }

    function lb_referral_get_booster_public_code($boosterId)
    {
        global $db;

        $boosterId = (int)$boosterId;
        if ($boosterId <= 0) {
            return '';
        }

        $booster = $db->row("SELECT id, username FROM boosters WHERE id = ? LIMIT 1", $boosterId);
        if (!$booster) {
            return '';
        }

        return lb_referral_public_slug($booster['username'] ?? '');
    }

    function lb_referral_get_seller_public_code($sellerId)
    {
        global $db;

        $sellerId = (int)$sellerId;
        if ($sellerId <= 0) {
            return '';
        }

        $seller = $db->row("SELECT id, username FROM sellers WHERE id = ? LIMIT 1", $sellerId);
        if (!$seller) {
            return '';
        }

        return trim((string)($seller['username'] ?? ''));
    }

    function lb_referral_get_public_code_for_link($link)
    {
        if (is_array($link)) {
            $ownerType = (string)($link['owner_type'] ?? '');
            if ($ownerType === 'booster') {
                $slug = lb_referral_get_booster_public_code((int)($link['owner_id'] ?? 0));
                if ($slug !== '') {
                    return $slug;
                }
            } elseif ($ownerType === 'seller') {
                $username = lb_referral_get_seller_public_code((int)($link['owner_id'] ?? 0));
                if ($username !== '') {
                    return $username;
                }
            }
        }

        if (is_array($link)) {
            return (string)($link['code'] ?? '');
        }

        return (string)$link;
    }

    function lb_referral_generate_code($ownerType, $ownerId)
    {
        if ($ownerType === 'booster') {
            $slug = lb_referral_get_booster_public_code((int)$ownerId);
            if ($slug !== '') {
                return $slug;
            }
            $prefix = 'BST';
        } elseif ($ownerType === 'seller') {
            $username = lb_referral_get_seller_public_code((int)$ownerId);
            if ($username !== '') {
                return $username;
            }
            $prefix = 'SLR';
        } else {
            $prefix = 'CLT';
        }
        return strtoupper($prefix . (int)$ownerId . substr(bin2hex(random_bytes(4)), 0, 8));
    }

    function lb_referral_get_link_by_code($code)
    {
        $rawCode = trim((string)$code);
        if ($rawCode === '') {
            return false;
        }

        global $db;

        // Keep all old referral links working, e.g. ?ref=BST50549EE7F22.
        $link = $db->row("SELECT * FROM referral_links WHERE code = ? AND status = 1 LIMIT 1", $rawCode);
        if ($link) {
            return $link;
        }

        // Public seller links use the seller username while old SLR codes remain valid.
        $seller = $db->row("SELECT id, username FROM sellers WHERE LOWER(username) = LOWER(?) LIMIT 1", $rawCode);
        if ($seller) {
            return lb_referral_get_or_create_link('seller', (int)$seller['id']);
        }

        // New booster public links use the readable booster username, e.g. ?ref=ricardoxmch.
        // This does not require a DB migration and still attaches the real referral_links row.
        $slug = lb_referral_public_slug($rawCode);
        if ($slug === '') {
            return false;
        }

        $booster = $db->row("SELECT id, username FROM boosters WHERE LOWER(username) = ? LIMIT 1", $slug);
        if (!$booster) {
            return false;
        }

        return lb_referral_get_or_create_link('booster', (int)$booster['id']);
    }

    function lb_referral_get_or_create_link($ownerType, $ownerId)
    {
        if ((int)$ownerId <= 0 || !in_array($ownerType, ['client', 'booster', 'seller'], true)) {
            return false;
        }

        $settings = lb_referral_get_settings();
        if ($ownerType === 'client' && !(int)$settings['allow_client_referrals']) {
            return false;
        }
        if ($ownerType === 'booster' && !(int)$settings['allow_booster_referrals']) {
            return false;
        }
        if ($ownerType === 'seller' && !(int)$settings['allow_seller_referrals']) {
            return false;
        }

        global $db;

        // Use a raw lookup here instead of db_get_row(), because some installs add
        // implicit filters in that helper. The unique index only cares about
        // owner_type + owner_id, so we must find any existing row before inserting.
        $existing = $db->row(
            "SELECT * FROM referral_links WHERE owner_type = ? AND owner_id = ? LIMIT 1",
            $ownerType,
            (int)$ownerId
        );

        if ($existing) {
            return $existing;
        }

        $code = lb_referral_generate_code($ownerType, $ownerId);
        $dup = $db->row("SELECT id FROM referral_links WHERE code = ? LIMIT 1", $code);
        if ($dup && $ownerType === 'booster') {
            $code = lb_referral_public_slug($code . '-' . (int)$ownerId);
            $dup = $db->row("SELECT id FROM referral_links WHERE code = ? LIMIT 1", $code);
        }
        while ($dup) {
            $code = strtoupper(($ownerType === 'seller' ? 'SLR' : ($ownerType === 'client' ? 'CLT' : 'BST')) . (int)$ownerId . substr(bin2hex(random_bytes(4)), 0, 8));
            $dup = $db->row("SELECT id FROM referral_links WHERE code = ? LIMIT 1", $code);
        }

        try {
            $id = db_add_row('referral_links', [
                'owner_type' => $ownerType,
                'owner_id' => (int)$ownerId,
                'code' => $code,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Duplicate fallback: another request or an old row may already own
            // this owner_type + owner_id. Return it instead of breaking /profile.
            $existing = $db->row(
                "SELECT * FROM referral_links WHERE owner_type = ? AND owner_id = ? LIMIT 1",
                $ownerType,
                (int)$ownerId
            );
            if ($existing) {
                return $existing;
            }
            throw $e;
        }

        return db_get_row('referral_links', ['id' => $id], 1);
    }

    function lb_referral_build_url($code)
    {
        return rtrim((string)BASE_URL, '/') . '/?ref=' . urlencode(lb_referral_get_public_code_for_link($code));
    }

    function lb_referral_store_code($code)
{
    $settings = lb_referral_get_settings();
    if (!(int)$settings['enabled']) {
        return false;
    }

    $link = lb_referral_get_link_by_code($code);
    if (!$link) {
        return false;
    }

    $expires = time() + (max(1, (int)$settings['cookie_days']) * 86400);

    setcookie(
        LB_REFERRAL_COOKIE,
        $link['code'],
        $expires,
        '/',
        '',
        !empty($_SERVER['HTTPS']),
        true
    );

    $_COOKIE[LB_REFERRAL_COOKIE] = $link['code'];
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['lb_referral_code'] = $link['code'];
    }

    db_add_row('referral_visits', [
        'referral_link_id' => $link['id'],
        'code' => $link['code'],
        'ip_address' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        'device_info' => json_encode([
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'landing' => $_SERVER['REQUEST_URI'] ?? '',
        ]),
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    return true;
}

function lb_referral_get_active_referral_link()
{
    $code = '';

    if (!empty($_COOKIE[LB_REFERRAL_COOKIE])) {
        $code = strtoupper(trim((string)$_COOKIE[LB_REFERRAL_COOKIE]));
    }

    if ($code === '' && session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['lb_referral_code'])) {
        $code = strtoupper(trim((string)$_SESSION['lb_referral_code']));
    }

    if ($code === '' && !empty($_GET['ref'])) {
        $code = strtoupper(trim((string)$_GET['ref']));
    }

    if ($code === '') {
        return false;
    }

    return lb_referral_get_link_by_code($code);
}

function lb_referral_get_active_link_from_cookie()
{
    return lb_referral_get_active_referral_link();
}


    function lb_referral_get_invoice_snapshot()
    {
        $settings = lb_referral_get_settings();
        $link = lb_referral_get_active_referral_link();

        if (!(int)$settings['enabled'] || !$link) {
            return [
                'referral_link_id' => null,
                'referral_owner_type' => null,
                'referral_owner_id' => null,
                'referral_code' => null,
            ];
        }

        return [
            'referral_link_id' => (int)$link['id'],
            'referral_owner_type' => (string)$link['owner_type'],
            'referral_owner_id' => (int)$link['owner_id'],
            'referral_code' => (string)$link['code'],
        ];
    }

    function lb_referral_attach_snapshot_to_invoice_data($invoiceData)
    {
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }

        return array_merge($invoiceData, lb_referral_get_invoice_snapshot());
    }

    function lb_referral_get_owner_email($ownerType, $ownerId)
    {
        if ($ownerType === 'booster') {
            $row = db_get_row('boosters', ['id' => (int)$ownerId], 1);
        } elseif ($ownerType === 'seller') {
            $row = db_get_row('sellers', ['id' => (int)$ownerId], 1);
        } else {
            $row = db_get_row('clients', ['id' => (int)$ownerId], 1);
        }

        return strtolower(trim((string)($row['email'] ?? '')));
    }

    function lb_referral_get_buyer_email($invoice)
    {
        $clientId = (int)($invoice['client_id'] ?? 0);
        if ($clientId > 0) {
            $client = db_get_row('clients', ['id' => $clientId], 1);
            if ($client && !empty($client['email'])) {
                return strtolower(trim((string)$client['email']));
            }
        }

        return strtolower(trim((string)($invoice['email'] ?? '')));
    }

    function lb_referral_is_self_referral($invoice)
    {
        $settings = lb_referral_get_settings();

        $ownerType = (string)($invoice['referral_owner_type'] ?? '');
        $ownerId = (int)($invoice['referral_owner_id'] ?? 0);
        $buyerClientId = (int)($invoice['client_id'] ?? 0);

        if ($ownerId <= 0 || !in_array($ownerType, ['client', 'booster', 'seller'], true)) {
            return true;
        }

        if ((int)$settings['block_same_client_account'] === 1 && $ownerType === 'client' && $buyerClientId > 0 && $buyerClientId === $ownerId) {
            return true;
        }

        if ((int)$settings['block_same_email'] === 1) {
            $ownerEmail = lb_referral_get_owner_email($ownerType, $ownerId);
            $buyerEmail = lb_referral_get_buyer_email($invoice);

            if ($ownerEmail !== '' && $buyerEmail !== '' && $ownerEmail === $buyerEmail) {
                return true;
            }
        }

        return false;
    }

    function lb_referral_calculate_client_reward_points($invoiceTotalCents)
    {
        $settings = lb_referral_get_settings();
        $eur = max(0, (int)$invoiceTotalCents) / 100;
        return round(($eur * (float)$settings['client_reward_percent']) / 100, 2);
    }

    function lb_referral_calculate_booster_reward_cents($invoiceTotalCents)
    {
        $settings = lb_referral_get_settings();
        return (int) round(max(0, (int)$invoiceTotalCents) * (((float)$settings['booster_reward_percent']) / 100));
    }

    function lb_referral_calculate_seller_reward_cents($invoiceTotalCents)
    {
        $settings = lb_referral_get_settings();
        return (int) round(max(0, (int)$invoiceTotalCents) * (((float)$settings['seller_reward_percent']) / 100));
    }


function lb_referral_order_is_completed($orderId)
{
    global $db;

    $orderId = (int)$orderId;
    if ($orderId <= 0) {
        return false;
    }

    $order = $db->row("SELECT id, status, completed_at FROM orders WHERE id = ? LIMIT 1", $orderId);
    if (!$order) {
        return false;
    }

    return strtoupper((string)($order['status'] ?? '')) === 'COMPLETED';
}

    function lb_referral_can_process_invoice($invoice)
    {
        $settings = lb_referral_get_settings();
        $invoiceId = (int)($invoice['id'] ?? 0);
        $invoiceTotal = (int)($invoice['total_price'] ?? 0);
        $orderId = (int)($invoice['order_id'] ?? 0);

        if (!(int)$settings['enabled']) {
            return [false, 'referrals_disabled'];
        }
        if ($invoiceId <= 0 || $invoiceTotal <= 0) {
            return [false, 'invalid_invoice'];
        }
        if ($invoiceTotal < (int)$settings['min_order_cents']) {
            return [false, 'below_min_order'];
        }
        if (db_get_row('referral_conversions', ['invoice_id' => $invoiceId], 1)) {
            return [false, 'already_processed'];
        }
        if (lb_referral_is_self_referral($invoice)) {
            return [false, 'self_referral_blocked'];
        }
        if ((int)$settings['require_completed'] === 1 && !lb_referral_order_is_completed($orderId)) {
            return [false, 'order_not_completed'];
        }

        return [true, 'ok'];
    }

    function lb_referral_process_commission($invoice)
    {
        $referralLinkId = (int)($invoice['referral_link_id'] ?? 0);
        $ownerType = (string)($invoice['referral_owner_type'] ?? '');
        $ownerId = (int)($invoice['referral_owner_id'] ?? 0);
        $invoiceId = (int)($invoice['id'] ?? 0);
        $orderId = (int)($invoice['order_id'] ?? 0);
        $clientId = (int)($invoice['client_id'] ?? 0);
        $invoiceTotal = (int)($invoice['total_price'] ?? 0);

        if ($referralLinkId <= 0 || $ownerId <= 0 || $invoiceId <= 0 || $invoiceTotal <= 0) {
            return false;
        }

        list($canProcess, $reason) = lb_referral_can_process_invoice($invoice);
        if (!$canProcess) {
            return false;
        }

        $rewardPoints = 0.00;
        $rewardCents = 0;

        if ($ownerType === 'client') {
            $client = db_get_row('clients', ['id' => $ownerId], 1);
            if (!$client) {
                return false;
            }

            $rewardPoints = lb_referral_calculate_client_reward_points($invoiceTotal);
            if ($rewardPoints <= 0) {
                return false;
            }

            db_update_row('clients', ['id' => $ownerId], [
                'points' => round((float)($client['points'] ?? 0) + $rewardPoints, 2),
            ]);

            db_add_row('coins_history', [
                'client_id' => $ownerId,
                'amount' => number_format($rewardPoints, 2, '.', ''),
                'type' => 'increment',
                'reason' => '🎁 Referral bonus after completed order #' . $orderId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($ownerType === 'booster') {
            $booster = db_get_row('boosters', ['id' => $ownerId], 1);
            if (!$booster) {
                return false;
            }

            $rewardCents = lb_referral_calculate_booster_reward_cents($invoiceTotal);
            if ($rewardCents <= 0) {
                return false;
            }

            $oldBalance = (int)($booster['balance'] ?? 0);
            $newBalance = $oldBalance + $rewardCents;

            db_update_row('boosters', ['id' => $ownerId], ['balance' => $newBalance]);

            db_add_row('booster_payments', [
                'booster_id' => $ownerId,
                'type' => 'referral_bonus',
                'note' => 'Referral bonus after completed order #' . $orderId . ' / invoice #' . $invoiceId,
                'amount' => $rewardCents,
                'currency' => 'EUR',
                'sender' => 'System',
                'sender_type' => 'system',
                'sender_id' => 0,
                'balance_update' => $oldBalance . '|' . $newBalance,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($ownerType === 'seller') {
            $seller = db_get_row('sellers', ['id' => $ownerId], 1);
            if (!$seller) {
                return false;
            }

            $rewardCents = lb_referral_calculate_seller_reward_cents($invoiceTotal);
            if ($rewardCents <= 0) {
                return false;
            }

            $oldBalance = (int)($seller['balance'] ?? 0);
            $newBalance = $oldBalance + $rewardCents;

            db_update_row('sellers', ['id' => $ownerId], ['balance' => $newBalance]);

            db_add_row('seller_payments', [
                'seller_id' => $ownerId,
                'type' => 'referral_bonus',
                'amount_cents' => $rewardCents,
                'note' => 'Referral bonus after completed order #' . $orderId . ' / invoice #' . $invoiceId,
                'balance_after' => $newBalance,
            ]);
        } else {
            return false;
        }

        db_add_row('referral_conversions', [
            'referral_link_id' => $referralLinkId,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'invoice_id' => $invoiceId,
            'order_id' => $orderId > 0 ? $orderId : null,
            'buyer_client_id' => $clientId > 0 ? $clientId : null,
            'buyer_email' => lb_referral_get_buyer_email($invoice),
            'invoice_total_cents' => $invoiceTotal,
            'reward_points' => $rewardPoints > 0 ? number_format($rewardPoints, 2, '.', '') : 0.00,
            'reward_cents' => $rewardCents,
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    function lb_referral_handle_completed_order($orderId)
    {
        global $db;

        $orderId = (int)$orderId;
        if ($orderId <= 0) {
            return false;
        }

        $order = $db->row("SELECT * FROM orders WHERE id = ? LIMIT 1", $orderId);
        if (!$order) {
            return false;
        }

        $invoice = false;
        $invoiceId = (int)($order['invoice_id'] ?? 0);

        if ($invoiceId > 0) {
            $invoice = $db->row("SELECT * FROM invoices WHERE id = ? LIMIT 1", $invoiceId);
        }

        if (!$invoice) {
            $invoice = $db->row(
                "SELECT * FROM invoices WHERE order_id = ? AND order_type = 'order' ORDER BY id DESC LIMIT 1",
                $orderId
            );
        }

        if (!$invoice) {
            return false;
        }

        if (strtoupper((string)($invoice['status'] ?? '')) !== 'PAID') {
            return false;
        }

        return lb_referral_process_commission($invoice);
    }

    function lb_referral_get_dashboard_data($ownerType, $ownerId)
    {
        global $db;

        $result = [
            'link' => false,
            'share_url' => '',
            'clicks' => 0,
            'signups' => 0,
            'purchases' => 0,
            'earnings_points' => 0.00,
            'earnings_cents' => 0,
        ];

        if ((int)$ownerId <= 0) {
            return $result;
        }

        $link = lb_referral_get_or_create_link($ownerType, (int)$ownerId);
        if (!$link) {
            return $result;
        }

        $result['link'] = $link;
        $result['share_url'] = lb_referral_build_url($link);
        $result['clicks'] = (int)db_get_row_count('referral_visits', ['referral_link_id' => $link['id']]);

        $rows = $db->run(
            "SELECT COUNT(*) AS purchases, COALESCE(SUM(reward_points), 0) AS reward_points, COALESCE(SUM(reward_cents), 0) AS reward_cents
             FROM referral_conversions
             WHERE referral_link_id = ? AND status = 'approved'",
            $link['id']
        );

        if (is_array($rows) && !empty($rows[0])) {
            $row = $rows[0];
            $result['purchases'] = (int)($row['purchases'] ?? 0);
            $result['earnings_points'] = round((float)($row['reward_points'] ?? 0), 2);
            $result['earnings_cents'] = (int)($row['reward_cents'] ?? 0);
        }

        $rows = $db->run(
            "SELECT COUNT(DISTINCT COALESCE(NULLIF(buyer_client_id, 0), buyer_email)) AS cnt
             FROM referral_conversions
             WHERE referral_link_id = ?",
            $link['id']
        );

        if (is_array($rows) && !empty($rows[0])) {
            $result['signups'] = (int)($rows[0]['cnt'] ?? 0);
        }

        return $result;
    }
}


// --- Progress tracking / Riot sync functions merged from functions.php ---
function riot_platform_url(string $server): string
{
    $platforms = [
        'euw' => 'https://euw1.api.riotgames.com',
        'eune' => 'https://eun1.api.riotgames.com',
        'na' => 'https://na1.api.riotgames.com',
        'br' => 'https://br1.api.riotgames.com',
        'lan' => 'https://la1.api.riotgames.com',
        'las' => 'https://la2.api.riotgames.com',
        'oce' => 'https://oc1.api.riotgames.com',
        'ru' => 'https://ru.api.riotgames.com',
        'tr' => 'https://tr1.api.riotgames.com',
        'jp' => 'https://jp1.api.riotgames.com',
        'kr' => 'https://kr.api.riotgames.com',
        'me' => 'https://me1.api.riotgames.com',
        'vn' => 'https://vn2.api.riotgames.com',
        'ph' => 'https://ph2.api.riotgames.com',
        'sg' => 'https://sg2.api.riotgames.com',
        'th' => 'https://th2.api.riotgames.com',
        'tw' => 'https://tw2.api.riotgames.com',
        'pbe' => 'https://na1.api.riotgames.com',
    ];

    $key = strtolower($server);

    if (!isset($platforms[$key])) {
        throw new \InvalidArgumentException("Unknown server: {$server}");
    }

    return $platforms[$key];
}

function riot_regional_url(string $server): string
{
    $regionals = [
        'euw' => 'https://europe.api.riotgames.com',
        'eune' => 'https://europe.api.riotgames.com',
        'tr' => 'https://europe.api.riotgames.com',
        'ru' => 'https://europe.api.riotgames.com',
        'me' => 'https://europe.api.riotgames.com',
        'na' => 'https://americas.api.riotgames.com',
        'br' => 'https://americas.api.riotgames.com',
        'lan' => 'https://americas.api.riotgames.com',
        'las' => 'https://americas.api.riotgames.com',
        'oce' => 'https://sea.api.riotgames.com',
        'jp' => 'https://asia.api.riotgames.com',
        'kr' => 'https://asia.api.riotgames.com',
        'vn' => 'https://sea.api.riotgames.com',
        'ph' => 'https://sea.api.riotgames.com',
        'sg' => 'https://sea.api.riotgames.com',
        'th' => 'https://sea.api.riotgames.com',
        'tw' => 'https://sea.api.riotgames.com',
        'pbe' => 'https://americas.api.riotgames.com',
    ];

    $key = strtolower($server);

    if (!isset($regionals[$key])) {
        throw new \InvalidArgumentException("Unknown server: {$server}");
    }

    return $regionals[$key];
}

function riot_account_url(string $server): string
{
    // Account-V1 only accepts americas/europe/asia routing values.
    // Do not use the SEA regional route here; OCE/SEA Riot IDs resolve via ASIA.
    $accountRegions = [
        'euw' => 'https://europe.api.riotgames.com',
        'eune' => 'https://europe.api.riotgames.com',
        'tr' => 'https://europe.api.riotgames.com',
        'ru' => 'https://europe.api.riotgames.com',
        'me' => 'https://europe.api.riotgames.com',

        'na' => 'https://americas.api.riotgames.com',
        'br' => 'https://americas.api.riotgames.com',
        'lan' => 'https://americas.api.riotgames.com',
        'las' => 'https://americas.api.riotgames.com',
        'pbe' => 'https://americas.api.riotgames.com',

        'jp' => 'https://asia.api.riotgames.com',
        'kr' => 'https://asia.api.riotgames.com',
        'oce' => 'https://asia.api.riotgames.com',
        'vn' => 'https://asia.api.riotgames.com',
        'ph' => 'https://asia.api.riotgames.com',
        'sg' => 'https://asia.api.riotgames.com',
        'th' => 'https://asia.api.riotgames.com',
        'tw' => 'https://asia.api.riotgames.com',
    ];

    $key = strtolower(trim($server));

    if (!isset($accountRegions[$key])) {
        throw new \InvalidArgumentException("Unknown server: {$server}");
    }

    return $accountRegions[$key];
}

function lb_normalize_riot_id(string $riotId): string
{
    $riotId = trim($riotId);
    $riotId = preg_replace("/[\\x{200B}-\\x{200F}\\x{202A}-\\x{202E}\\x{2066}-\\x{2069}\\x{FEFF}]/u", "", $riotId);
    $riotId = str_replace(["＃", "♯"], "#", $riotId);
    $riotId = preg_replace("/\\s*#\\s*/u", "#", $riotId);
    return trim($riotId);
}

function lb_is_valid_riot_id(string $riotId): bool
{
    $riotId = lb_normalize_riot_id($riotId);
    if ($riotId === "" || substr_count($riotId, "#") !== 1) return false;
    [$gameName, $tagLine] = explode("#", $riotId, 2);
    $gameName = trim($gameName);
    $tagLine = trim($tagLine);
    $gameLen = function_exists("mb_strlen") ? mb_strlen($gameName, "UTF-8") : strlen($gameName);
    $tagLen = function_exists("mb_strlen") ? mb_strlen($tagLine, "UTF-8") : strlen($tagLine);
    return $gameLen >= 2 && $gameLen <= 32 && $tagLen >= 2 && $tagLen <= 16;
}

function parse_riot_id(string $riotId): array
{
    $riotId = function_exists("lb_normalize_riot_id") ? lb_normalize_riot_id($riotId) : trim($riotId);
    $parts = explode("#", $riotId, 2);

    if (!function_exists("lb_is_valid_riot_id") || !lb_is_valid_riot_id($riotId)) {
        throw new \InvalidArgumentException("Invalid Riot ID format: {$riotId}");
    }

    return [
        "game_name" => trim($parts[0]),
        "tag_line"  => trim($parts[1]),
    ];
}
function riot_user_friendly_error_message(\Throwable $e): string
{
    $message = $e->getMessage();

    if (preg_match('/HTTP\s+429\b/i', $message)) {
        return 'Riot API rate limit reached. Please try again in a minute.';
    }

    if (preg_match('/HTTP\s+(500|502|503|504)\b/i', $message)
        || stripos($message, 'temporarily unavailable') !== false
        || stripos($message, 'Service Unavailable') !== false) {
        return 'Riot API is temporarily unavailable. Please try again in a few minutes.';
    }

    return $message;
}

function riot_api_get(string $url, int $retries = 3): array
{
    $lastHttpCode = 0;
    $lastCurlError = '';

    for ($attempt = 0; $attempt <= $retries; $attempt++) {
        $headers = [];
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'X-Riot-Token: ' . RIOT_API_KEY,
            ],
            CURLOPT_HEADERFUNCTION => function ($curl, $headerLine) use (&$headers) {
                $len = strlen($headerLine);
                $parts = explode(':', $headerLine, 2);
                if (count($parts) === 2) {
                    $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return $len;
            },
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = (int) curl_errno($ch);
        $curlError = (string) curl_error($ch);
        curl_close($ch);

        $lastHttpCode = $httpCode;
        $lastCurlError = $curlError;

        if ($response !== false && $curlErrno === 0 && $httpCode === 200) {
            $decoded = json_decode((string) $response, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            $preview = trim(strip_tags(substr((string)$response, 0, 160)));
            throw new \RuntimeException(
                'Riot API returned an invalid response instead of JSON.' .
                ($preview !== '' ? ' Response: ' . $preview : '')
            );
        }

        if ($httpCode === 429) {
            $retryAfter = isset($headers['retry-after']) ? (int) $headers['retry-after'] : 0;
            // Most callers sit inside a page render or an admin action, so a long
            // wait here is a frozen browser tab, not a graceful retry. Riot's
            // Retry-After can be 30s, which used to mean up to 90s of sleeping in
            // a single request. Give up quickly instead — the caller falls back.
            $waitSeconds = max(1, min(4, $retryAfter > 0 ? $retryAfter : (1 + $attempt)));
            if ($attempt < $retries) {
                sleep($waitSeconds);
                continue;
            }
            throw new \RuntimeException('Riot API request failed with HTTP 429.');
        }

        if (in_array($httpCode, [500, 502, 503, 504], true) || $response === false || $curlErrno !== 0) {
            if ($attempt < $retries) {
                // Same reasoning as the 429 branch: keep the total wait bounded so
                // a Riot outage never holds a page open for tens of seconds.
                sleep(min(3, 1 + $attempt));
                continue;
            }

            if ($curlErrno !== 0) {
                throw new \RuntimeException('Riot API request failed: ' . ($lastCurlError !== '' ? $lastCurlError : 'network error'));
            }

            throw new \RuntimeException("Riot API request failed with HTTP {$httpCode}.");
        }

        throw new \RuntimeException("Riot API request failed with HTTP {$httpCode}.");
    }

    throw new \RuntimeException("Riot API temporarily unavailable after retries. Last HTTP code: {$lastHttpCode}.");
}

function riot_get_puuid(string $riotId, string $server): ?string
{
    $riot = parse_riot_id($riotId);

    // The admin order page resolves the PUUID on every load when none is stored,
    // and riot_api_get() retries up to four times with sleeps in between — a rate
    // limited or unreachable Riot API therefore froze the page for ~15 seconds,
    // on every single reload, because a failed lookup was never remembered.
    //
    // A PUUID never changes, so success is cached for a month. A failure is cached
    // for 15 minutes: long enough that a reload is instant, short enough that a
    // corrected Riot ID or a recovered API is picked up on its own.
    $cacheKey = 'lb_puuid_' . md5(strtolower($riot['game_name'] . '#' . $riot['tag_line'] . '|' . $server));
    $cacheFile = sys_get_temp_dir() . '/' . $cacheKey;
    $okTtl = 30 * 24 * 3600;
    $failTtl = 900;

    $readCache = static function () use ($cacheKey, $cacheFile, $okTtl, $failTtl) {
        if (function_exists('apcu_fetch')) {
            $hit = apcu_fetch($cacheKey, $ok);
            return $ok ? $hit : false;
        }
        if (!is_file($cacheFile)) {
            return false;
        }
        $value = trim((string)@file_get_contents($cacheFile));
        $age = time() - (int)@filemtime($cacheFile);
        // "-" marks a remembered failure and expires much sooner than a hit.
        $ttl = ($value === '-') ? $failTtl : $okTtl;
        return $age < $ttl ? $value : false;
    };
    $writeCache = static function ($value, $ttl) use ($cacheKey, $cacheFile) {
        if (function_exists('apcu_store')) {
            @apcu_store($cacheKey, $value, $ttl);
        } else {
            @file_put_contents($cacheFile, $value, LOCK_EX);
        }
    };

    $cached = $readCache();
    if ($cached === '-') {
        return null;
    }
    if (is_string($cached) && $cached !== '') {
        return $cached;
    }

    $baseUrl = riot_account_url($server);
    $url = "{$baseUrl}/riot/account/v1/accounts/by-riot-id/" . rawurlencode($riot['game_name']) . "/" . rawurlencode($riot['tag_line']);

    try {
        $decoded = riot_api_get($url);
    } catch (\Throwable $e) {
        $writeCache('-', $failTtl);
        return null;
    }

    $puuid = $decoded['puuid'] ?? null;
    if (is_string($puuid) && $puuid !== '') {
        $writeCache($puuid, $okTtl);
        return $puuid;
    }

    $writeCache('-', $failTtl);
    return null;
}

function riot_get_summoner_profile(string $puuid, string $server): ?array
{
    $puuid = trim($puuid);
    if ($puuid === '') {
        return null;
    }

    $baseUrl = riot_platform_url($server);
    $url = "{$baseUrl}/lol/summoner/v4/summoners/by-puuid/" . urlencode($puuid);

    try {
        return riot_api_get($url);
    } catch (\Throwable $e) {
        return null;
    }
}

function riot_profile_icon_url($profileIconId): ?string
{
    $profileIconId = (int) $profileIconId;
    if ($profileIconId <= 0) {
        return null;
    }

    try {
        $version = function_exists('get_lol_version') ? (string) get_lol_version() : '15.1.1';
    } catch (\Throwable $e) {
        $version = '15.1.1';
    }

    return 'https://ddragon.leagueoflegends.com/cdn/' . rawurlencode($version) . '/img/profileicon/' . $profileIconId . '.png';
}

function riot_get_rank(string $puuid, string $server, $queueType = 'solo/duo'): array
{
    $baseUrl = riot_platform_url($server);
    $url = "{$baseUrl}/lol/league/v4/entries/by-puuid/" . urlencode($puuid);

    $entries = riot_api_get($url);

    $normalizedQueueType = function_exists('util_normalize_lol_queue_type')
        ? util_normalize_lol_queue_type($queueType)
        : strtolower(trim((string) $queueType));
    $riotQueueType = ($normalizedQueueType === 'flex') ? 'RANKED_FLEX_SR' : 'RANKED_SOLO_5x5';

    $rankedEntry = null;
    if (is_array($entries)) {
        foreach ($entries as $entry) {
            if (($entry['queueType'] ?? '') === $riotQueueType) {
                $rankedEntry = $entry;
                break;
            }
        }
    }

    if (!$rankedEntry) {
        return [
            'tier' => null,
            'division' => null,
            'lp' => null,
        ];
    }

    return [
        'tier' => $rankedEntry['tier'] ?? null,
        'division' => $rankedEntry['rank'] ?? null,
        'lp' => $rankedEntry['leaguePoints'] ?? null,
    ];
}

/**
 * order_progress.start_rank_source records where the stored start rank came from
 * ('order' = taken from the ordered rank, 'riot' = confirmed by a Riot API sync).
 * Only an order-derived seed may later be replaced by a real Riot value.
 */
function lb_order_progress_ensure_start_rank_column(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    global $db;
    if (!isset($db) || !is_object($db) || !method_exists($db, 'run')) {
        return;
    }

    try {
        $db->run("ALTER TABLE order_progress ADD COLUMN IF NOT EXISTS start_rank_source VARCHAR(20) NULL DEFAULT NULL");
    } catch (\Throwable $e) {
    }
}

/**
 * The start rank the customer actually ordered, normalized to the Riot naming that
 * order_progress stores ("EMERALD" / "IV" / LP).
 *
 * Source order: the order_original_data snapshot (what the customer booked, immune to
 * admin edits) first, then the live order_options row.
 *
 * @return array{tier:?string,division:?string,lp:?int}
 */
function lb_order_ordered_start_rank(int $orderId): array
{
    $empty = ['tier' => null, 'division' => null, 'lp' => null];

    if ($orderId <= 0) {
        return $empty;
    }

    $order = db_get_row('orders', ['id' => $orderId, 'select' => 'id,form_id'], 1) ?: [];
    if (empty($order)) {
        return $empty;
    }

    $form_id = (int) ($order['form_id'] ?? 0);
    $opts = [];

    $snap = db_get_row('order_original_data', ['order_id' => $orderId], 1);
    if (!empty($snap)) {
        $snap_opts = json_decode((string) ($snap['options_json'] ?? ''), true);
        if (is_array($snap_opts) && isset($snap_opts['start_tier'])) {
            $opts = $snap_opts;
        }
        $snap_order = json_decode((string) ($snap['orders_json'] ?? ''), true);
        if (is_array($snap_order) && !empty($snap_order['form_id'])) {
            $form_id = (int) $snap_order['form_id'];
        }
    }

    if (empty($opts)) {
        $opts = db_get_row('order_options', ['order_id' => $orderId], 1) ?: [];
    }

    if (!isset($opts['start_tier']) || $opts['start_tier'] === '' || $opts['start_tier'] === null) {
        return $empty;
    }

    $form = $form_id > 0 ? db_get_row('boost_forms', ['id' => $form_id, 'select' => 'id,game'], 1) : null;
    $game = strtolower(trim((string) ($form['game'] ?? '')));
    // order_progress only tracks the Riot ladder; other games use a different tier scale.
    if (!in_array($game, ['lol', 'league-of-legends', 'tft', 'teamfight-tactics'], true)) {
        return $empty;
    }

    $tier_id = (int) $opts['start_tier'];
    // 0 = Unranked. There is nothing to display, so leave the row untouched.
    if ($tier_id <= 0) {
        return $empty;
    }

    $tier_name = util_format_tier($tier_id, $game);
    if ($tier_name === null || $tier_name === '') {
        return $empty;
    }

    $division = null;
    $lp = null;

    if ($tier_id <= 7) {
        // Below Master the checkout stores start_lp as an LP *band* index, not real LP,
        // so only the division is meaningful here.
        $division_raw = $opts['start_division'] ?? null;
        if ($division_raw !== null && $division_raw !== '') {
            $division = util_format_division((int) $division_raw, $game);
        }
    } elseif (isset($opts['start_lp']) && $opts['start_lp'] !== '' && $opts['start_lp'] !== null) {
        // Master+ is open ended, checkout writes the exact LP amount into start_lp.
        $lp = (int) $opts['start_lp'];
    }

    return [
        'tier' => strtoupper($tier_name),
        'division' => $division !== null ? (string) $division : null,
        'lp' => $lp,
    ];
}

/**
 * Makes sure order_progress carries a start rank. Riot only reports one once the tracking
 * sync succeeds; until then (or if it never does) the ordered start rank is used, so the
 * progress card never falls back to "Unranked".
 *
 * Returns the progress row with the start rank applied.
 */
function lb_order_progress_ensure_start_rank(int $orderId, array $progress = []): array
{
    if ($orderId <= 0) {
        return $progress;
    }

    if (empty($progress)) {
        $progress = db_get_row('order_progress', ['order_id' => $orderId], 1) ?: [];
    }

    // A Riot-confirmed baseline is final. Likewise, do not overwrite an
    // existing legacy baseline whose source is unknown.
    $existingStart = trim((string)($progress['start_tier'] ?? ''));
    $existingSource = trim((string)($progress['start_rank_source'] ?? ''));
    if ($existingSource === 'riot' || ($existingStart !== '' && $existingSource !== 'order')) {
        return $progress;
    }

    $ordered = lb_order_ordered_start_rank($orderId);
    $orderedTier = trim((string)($ordered['tier'] ?? ''));
    if ($orderedTier === '') {
        return $progress;
    }

    lb_order_progress_ensure_start_rank_column();

    $seed = [
        'start_tier' => $orderedTier,
        'start_division' => $ordered['division'] ?? null,
        'start_lp' => $ordered['lp'] ?? null,
        'start_rank_source' => 'order',
    ];

    if (empty($progress)) {
        db_add_row('order_progress', array_merge(['order_id' => $orderId], $seed));
        return array_merge(['order_id' => $orderId], $seed);
    }

    db_update_row('order_progress', ['order_id' => $orderId], $seed);
    return array_merge($progress, $seed);
}

if (!function_exists('lb_order_capture_riot_start_rank')) {
    /**
     * Captures the REAL start rank of a LoL order straight from the Riot API.
     *
     * The ordered rank is only what the customer selected in the form — they may have
     * demoted since (buys "Diamond I net wins" while actually sitting in Diamond II).
     * Whatever Riot reports the moment the order starts is the truth, so this overwrites
     * an "order"-seeded start rank once and then never touches it again
     * (start_rank_source = 'riot' is final).
     *
     * Safe to call repeatedly: it no-ops when the start rank is already Riot-verified,
     * when the game is not on the Riot ladder, or when no Riot ID is saved yet.
     */
    function lb_order_capture_riot_start_rank(int $orderId): bool
    {
        if ($orderId <= 0 || !function_exists('riot_get_puuid')) {
            return false;
        }

        try {
            $progress = db_get_row('order_progress', ['order_id' => $orderId], 1) ?: [];
            if (trim((string) ($progress['start_rank_source'] ?? '')) === 'riot') {
                return false;
            }

            $order = db_get_row('orders', ['id' => $orderId, 'select' => 'id,form_id'], 1) ?: [];
            if (empty($order)) {
                return false;
            }

            // Only the Riot ladder has a trackable rank. Coaching has no rank progress at all.
            if (function_exists('lb_is_profile_game_tracking_disabled_form')
                && lb_is_profile_game_tracking_disabled_form($order['form_id'] ?? 0)) {
                return false;
            }

            $form = db_get_row('boost_forms', ['id' => (int) ($order['form_id'] ?? 0), 'select' => 'id,game'], 1) ?: [];
            $game = strtolower(trim((string) ($form['game'] ?? '')));
            if (!in_array($game, ['lol', 'league-of-legends', 'leagu', 'lol_classic', 'lol-classic'], true)) {
                return false;
            }

            $account = db_get_row('order_accounts', ['order_id' => $orderId], 1) ?: [];
            $riotId = trim((string) ($account['ign'] ?? ''));
            if ($riotId === '') {
                // Riot ID is added later by the client; the save handler calls us again.
                return false;
            }

            $options = db_get_row('order_options', ['order_id' => $orderId], 1) ?: [];
            $server = trim((string) ($options['server'] ?? '')) ?: 'euw';

            $puuid = trim((string) ($progress['puuid'] ?? ''));
            if ($puuid === '') {
                $puuid = (string) (riot_get_puuid($riotId, $server) ?? '');
            }
            if ($puuid === '') {
                return false;
            }

            $rank = riot_get_rank($puuid, $server, $options['queue_type'] ?? 'solo/duo');
            $tier = trim((string) ($rank['tier'] ?? ''));
            if ($tier === '') {
                // Unranked / decayed / API hiccup: keep the ordered rank as the placeholder.
                return false;
            }

            $division = trim((string) ($rank['division'] ?? ''));
            $lp = ($rank['lp'] === null || $rank['lp'] === '') ? null : (int) $rank['lp'];

            lb_order_progress_ensure_start_rank_column();

            $payload = [
                'puuid' => $puuid,
                'start_tier' => $tier,
                'start_division' => $division !== '' ? $division : null,
                'start_lp' => $lp,
                'start_rank_source' => 'riot',
                'current_tier' => $tier,
                'current_division' => $division !== '' ? $division : null,
                'current_lp' => $lp,
                'last_sync_at' => date('Y-m-d H:i:s'),
            ];

            if (empty($progress)) {
                db_add_row('order_progress', array_merge(['order_id' => $orderId], $payload));
            } else {
                db_update_row('order_progress', ['order_id' => $orderId], $payload);
            }

            return true;
        } catch (\Throwable $e) {
            // Never let a Riot outage block checkout or the account save.
            return false;
        }
    }
}

function save_riot_rank(int $orderId, array $rank, $db, ?array $match_summary = null): void
{
    if ($orderId <= 0) {
        throw new \InvalidArgumentException('Invalid order id.');
    }

    $tier = isset($rank['tier']) ? trim((string) $rank['tier']) : null;
    $division = isset($rank['division']) ? trim((string) $rank['division']) : null;
    $lp = isset($rank['lp']) && $rank['lp'] !== '' ? (int) $rank['lp'] : null;

    if ($tier === '') {
        $tier = null;
    }

    if ($division === '') {
        $division = null;
    }

    $existing = db_get_row('order_progress', ['order_id' => $orderId], 1) ?: [];
    $has_match_summary = is_array($match_summary) && (
        array_key_exists('wins', $match_summary)
        || array_key_exists('losses', $match_summary)
        || array_key_exists('last_match_id', $match_summary)
    );
    $payload = [
        'current_tier' => $tier,
        'current_division' => $division,
        'current_lp' => $lp,
        'last_sync_at' => date('Y-m-d H:i:s'),
    ];

    if ($has_match_summary) {
        $record = riot_get_order_match_record($orderId, $db);
        $payload['wins'] = (int) ($record['wins'] ?? 0);
        $payload['losses'] = (int) ($record['losses'] ?? 0);

        $latest_match_id = trim((string) ($match_summary['last_match_id'] ?? ''));
        if ($latest_match_id !== '') {
            $payload['last_match_id'] = $latest_match_id;
        }
    }

    if (empty($existing)) {
        $insert_payload = [
            'order_id' => $orderId,
            // The very first sync also defines the start rank of the order.
            // Without this the progress card always renders "Unranked".
            'start_tier' => $tier,
            'start_division' => $division,
            'start_lp' => $lp,
            'start_rank_source' => 'riot',
            'current_tier' => $tier,
            'current_division' => $division,
            'current_lp' => $lp,
            'last_sync_at' => $payload['last_sync_at'],
        ];

        lb_order_progress_ensure_start_rank_column();

        if ($has_match_summary) {
            $insert_payload['wins'] = (int) ($payload['wins'] ?? 0);
            $insert_payload['losses'] = (int) ($payload['losses'] ?? 0);
            if (!empty($payload['last_match_id'])) {
                $insert_payload['last_match_id'] = $payload['last_match_id'];
            }
        }

        db_add_row('order_progress', $insert_payload);
        return;
    }

    // riot_sync_order_progress() creates the row with only order_id + puuid, so the
    // "first sync" is usually an UPDATE. Seed the start rank once, then never touch it again.
    // A start rank that was only derived from the ordered rank still counts as unseeded:
    // the first rank Riot actually confirms replaces it.
    $existing_start = trim((string) ($existing['start_tier'] ?? ''));
    $existing_from_order = trim((string) ($existing['start_rank_source'] ?? '')) === 'order';
    if (($existing_start === '' || $existing_from_order) && $tier !== null) {
        lb_order_progress_ensure_start_rank_column();
        $payload['start_tier'] = $tier;
        $payload['start_division'] = $division;
        $payload['start_lp'] = $lp;
        $payload['start_rank_source'] = 'riot';
    }

    db_update_row('order_progress', ['order_id' => $orderId], $payload);
}

function riot_order_sync_start_time(array $order): string
{
    $paid_at = trim((string) ($order['paid_at'] ?? ''));
    if ($paid_at !== '') {
        $paid_timestamp = strtotime($paid_at);
        if ($paid_timestamp !== false && $paid_timestamp > 0) {
            return (string) $paid_timestamp;
        }
    }

    $created_at = trim((string) ($order['created_at'] ?? ''));
    if ($created_at !== '') {
        $created_timestamp = strtotime($created_at);
        if ($created_timestamp !== false && $created_timestamp > 0) {
            return (string) $created_timestamp;
        }
    }

    return (string) max(0, time() - (30 * 86400));
}

function riot_get_matches(string $puuid, string $server, string $startTime, ?string $lastMatchId = null, $queueTypes = null): array
{
    $baseUrl = riot_regional_url($server);
    $count = 20;
    $maxMatchesPerSync = 30;
    $all_match_ids = [];
    $cursor_match_id = trim((string) ($lastMatchId ?? ''));
    $start_time = max(0, (int) $startTime);

    // Riot's match-v5 endpoint accepts only one queue parameter per request.
    // For Normal Games/Flex/etc. we therefore query every queueId belonging to
    // the order queue_type instead of hard-coding queue=420.
    $queueIds = [];
    if ($queueTypes !== null && $queueTypes !== '' && $queueTypes !== 'all') {
        $queueValues = is_array($queueTypes) ? $queueTypes : [$queueTypes];
        foreach ($queueValues as $queueValue) {
            if (is_numeric($queueValue)) {
                $queueIds[] = (int) $queueValue;
                continue;
            }
            if (function_exists('util_lol_queue_type_ids')) {
                $ids = util_lol_queue_type_ids((string) $queueValue);
                if (is_array($ids)) {
                    foreach ($ids as $id) $queueIds[] = (int) $id;
                }
            }
        }
    }

    // Backwards-compatible default: old ranked orders still sync Solo/Duo only.
    if (empty($queueIds)) {
        $queueIds = [420];
    }
    $queueIds = array_values(array_unique(array_filter(array_map('intval', $queueIds), fn($id) => $id > 0)));

    foreach ($queueIds as $queueId) {
        $start = 0;
        while (count($all_match_ids) < $maxMatchesPerSync) {
            $url = "{$baseUrl}/lol/match/v5/matches/by-puuid/" . urlencode($puuid) . "/ids?queue={$queueId}&start={$start}&count={$count}&startTime={$start_time}";
            $batch = riot_api_get($url);

            if (!is_array($batch) || empty($batch)) {
                break;
            }

            if ($cursor_match_id !== '') {
                $cursor_index = array_search($cursor_match_id, $batch, true);
                if ($cursor_index !== false) {
                    $all_match_ids = array_merge($all_match_ids, array_slice($batch, 0, $cursor_index));
                    break;
                }
            }

            $all_match_ids = array_merge($all_match_ids, $batch);

            if (count($batch) < $count) {
                break;
            }

            $start += $count;
        }
    }

    $all_match_ids = array_values(array_unique($all_match_ids));
    return array_slice($all_match_ids, 0, $maxMatchesPerSync);
}

function riot_get_match_result(string $matchId, string $puuid, string $server): ?bool
{
    $baseUrl = riot_regional_url($server);
    $url = "{$baseUrl}/lol/match/v5/matches/" . urlencode($matchId);

    $match = riot_api_get($url);
    $participants = $match['info']['participants'] ?? [];
    if (!is_array($participants)) {
        return null;
    }

    foreach ($participants as $participant) {
        if (($participant['puuid'] ?? '') === $puuid) {
            return !empty($participant['win']);
        }
    }

    return null;
}

function riot_order_matches_columns($db): array
{
    $columns = [];

    try {
        $rows = $db->run("SHOW COLUMNS FROM order_matches");
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $field = strtolower((string)($row['Field'] ?? ''));
                if ($field !== '') {
                    $columns[$field] = true;
                }
            }
        }
    } catch (\Throwable $e) {
        $columns = [];
    }

    return $columns;
}


function riot_get_order_match_record(int $orderId, $db = null): array
{
    if ($orderId <= 0) {
        return ['wins' => 0, 'losses' => 0, 'total' => 0, 'total_matches' => 0];
    }

    if ($db === null) {
        global $db;
    }

    if (!$db) {
        return ['wins' => 0, 'losses' => 0, 'total' => 0, 'total_matches' => 0];
    }

    $columns = riot_ensure_order_matches_remake_columns($db);
    $remakeChecks = [];

    if (!empty($columns['is_remake'])) {
        $remakeChecks[] = 'COALESCE(is_remake, 0) = 1';
    }
    if (!empty($columns['game_ended_in_early_surrender'])) {
        $remakeChecks[] = 'COALESCE(game_ended_in_early_surrender, 0) = 1';
    }
    $remakeChecks[] = '(COALESCE(duration, 0) > 0 AND COALESCE(duration, 0) < 300)';

    $remakeSql = '(' . implode(' OR ', $remakeChecks) . ')';

    // Progress should count ALL stored non-remake matches for the order.
    // Do not filter by rank_snapshot/start_tier here: after promotions the snapshot changes
    // (e.g. SILVER -> GOLD), but those games still belong to the same order progress.
    $rankFilterSql = '';
    $rankFilterParams = [];

    // Fix (Bug 3): Count total wins/losses across the ENTIRE order for "current progress" display —
    // this gives the client the full progress view regardless of how many boosters have worked on it.
    // No rank_snapshot filter is applied here, so this matches the full match history count.
    $rows = $db->run(
        "SELECT
            COALESCE(SUM(CASE WHEN {$remakeSql} THEN 0 WHEN COALESCE(won, 0) = 1 THEN 1 ELSE 0 END), 0) AS wins,
            COALESCE(SUM(CASE WHEN {$remakeSql} THEN 0 WHEN COALESCE(won, 0) = 0 THEN 1 ELSE 0 END), 0) AS losses,
            COALESCE(SUM(CASE WHEN {$remakeSql} THEN 0 ELSE 1 END), 0) AS total,
            COUNT(*) AS total_matches
         FROM order_matches
         WHERE order_id = ? AND COALESCE(is_hidden, 0) = 0{$rankFilterSql}",
        ...array_merge([$orderId], $rankFilterParams)
    );

    $row = is_array($rows) ? ($rows[0] ?? []) : [];

    return [
        'wins' => (int)($row['wins'] ?? 0),
        'losses' => (int)($row['losses'] ?? 0),
        'total' => (int)($row['total'] ?? 0),
        'total_matches' => (int)($row['total_matches'] ?? 0),
    ];
}

function riot_ensure_order_matches_remake_columns($db): array
{
    static $done = false;

    if ($done) {
        return riot_order_matches_columns($db);
    }

    $columns = riot_order_matches_columns($db);

    $addColumn = function (string $name, string $sql) use ($db, &$columns) {
        if (!empty($columns[strtolower($name)])) {
            return;
        }

        try {
            $db->run($sql);
        } catch (\Throwable $e) {
            // Keep sync/read flows usable even if the DB user cannot ALTER yet.
        }
    };

    // Stores the booster that was assigned when the match was synced.
    // Existing installs keep working if ALTER permissions are missing; reads fall back to orders.booster_id.
    $addColumn('booster_id', "ALTER TABLE order_matches ADD COLUMN booster_id INT NULL AFTER order_id");
    try {
        $db->run("ALTER TABLE order_matches ADD INDEX idx_order_matches_booster_id (booster_id)");
    } catch (\Throwable $e) {
        // Index may already exist or ALTER may not be permitted.
    }

    // Stores whether the tracked stat row was created from a solo boost or a duo game.
    // For solo boosts, the booster is playing on the client account; for duo games, the row is the client account stats.
    $addColumn('play_mode', "ALTER TABLE order_matches ADD COLUMN play_mode VARCHAR(10) NULL AFTER booster_id");

    $addColumn('is_remake', "ALTER TABLE order_matches ADD COLUMN is_remake TINYINT(1) NOT NULL DEFAULT 0 AFTER won");
    $addColumn('game_ended_in_early_surrender', "ALTER TABLE order_matches ADD COLUMN game_ended_in_early_surrender TINYINT(1) NOT NULL DEFAULT 0 AFTER is_remake");
    $addColumn('game_ended_in_surrender', "ALTER TABLE order_matches ADD COLUMN game_ended_in_surrender TINYINT(1) NOT NULL DEFAULT 0 AFTER game_ended_in_early_surrender");

    // Stores the live client Solo/Duo rank at sync time for newly inserted matches.
    // This prevents all tracked games from being bucketed by the original order start rank.
    $addColumn('rank_snapshot', "ALTER TABLE order_matches ADD COLUMN rank_snapshot VARCHAR(64) NULL AFTER queue_id");

    // Soft-hide admin removed/reported games instead of deleting them.
    // Hidden rows stay in DB for audit/debugging and duplicate prevention, but are excluded from history/stats.
    $addColumn('is_hidden', "ALTER TABLE order_matches ADD COLUMN is_hidden TINYINT(1) NOT NULL DEFAULT 0 AFTER rank_snapshot");
    $addColumn('hidden_at', "ALTER TABLE order_matches ADD COLUMN hidden_at DATETIME NULL AFTER is_hidden");
    $addColumn('hidden_by', "ALTER TABLE order_matches ADD COLUMN hidden_by INT NULL AFTER hidden_at");
    $addColumn('hidden_reason', "ALTER TABLE order_matches ADD COLUMN hidden_reason VARCHAR(255) NULL AFTER hidden_by");
    try {
        $db->run("ALTER TABLE order_matches ADD INDEX idx_order_matches_is_hidden (is_hidden)");
    } catch (\Throwable $e) {
        // Index may already exist or ALTER may not be permitted.
    }

    // Refresh columns after possible ALTERs.
    $columns = riot_order_matches_columns($db);

    // Backfill old already-tracked remakes using duration as fallback.
    if (!empty($columns['is_remake'])) {
        try {
            $db->run("UPDATE order_matches SET is_remake = 1 WHERE duration > 0 AND duration < 300 AND is_remake = 0");
        } catch (\Throwable $e) {}
    }

    $done = true;
    return $columns;
}


function riot_format_rank_snapshot(?array $rank): ?string
{
    if (!is_array($rank)) {
        return null;
    }

    $tier = isset($rank['tier']) ? strtoupper(trim((string)$rank['tier'])) : '';
    $division = isset($rank['division']) ? strtoupper(trim((string)$rank['division'])) : '';
    $lp = (isset($rank['lp']) && $rank['lp'] !== '' && $rank['lp'] !== null) ? (int)$rank['lp'] : null;

    if ($tier === '') {
        return 'UNRANKED';
    }

    $parts = [$tier];
    if ($division !== '') {
        $parts[] = $division;
    }
    if ($lp !== null) {
        $parts[] = $lp . ' LP';
    }

    return substr(implode(' ', $parts), 0, 64);
}


if (!function_exists('riot_short_rank_snapshot_label')) {
    function riot_short_rank_snapshot_label($snapshot): string
    {
        $raw = trim((string)$snapshot);
        if ($raw === '') {
            return '';
        }

        $cleaned = preg_replace('/\s+/', ' ', str_replace('·', ' ', $raw));
        $cleaned = trim((string)$cleaned);

        if (!preg_match('/^(IRON|BRONZE|SILVER|GOLD|PLATINUM|EMERALD|DIAMOND|MASTER|GRANDMASTER|CHALLENGER)\s*(I|II|III|IV)?\s*(?:(\d+)\s*LP)?/i', $cleaned, $m)) {
            return preg_replace('/\s*LP\b/i', 'LP', $cleaned) ?: $cleaned;
        }

        $tierMap = [
            'IRON' => 'I',
            'BRONZE' => 'B',
            'SILVER' => 'S',
            'GOLD' => 'G',
            'PLATINUM' => 'P',
            'EMERALD' => 'E',
            'DIAMOND' => 'D',
            'MASTER' => 'M',
            'GRANDMASTER' => 'GM',
            'CHALLENGER' => 'C',
        ];
        $divMap = ['I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4'];

        $tierKey = strtoupper($m[1]);
        $divisionKey = isset($m[2]) ? strtoupper((string)$m[2]) : '';
        $lp = isset($m[3]) && $m[3] !== '' ? (' ' . (int)$m[3] . 'LP') : '';

        return trim(($tierMap[$tierKey] ?? substr($tierKey, 0, 1)) . ($divMap[$divisionKey] ?? '') . $lp);
    }
}


function riot_queue_panel_sharing_alert_for_match($db, int $orderId, string $matchId, string $puuid = ''): void
{
    static $guardLoaded = false;

    if (!$guardLoaded && !function_exists('lb_order_match_queue_panel_sharing_alert')) {
        $candidates = [
            __DIR__ . '/order_match_panel_sharing_guard_fast.php',
            __DIR__ . '/tasks/order_match_panel_sharing_guard_fast.php',
            __DIR__ . '/../tasks/order_match_panel_sharing_guard_fast.php',
            dirname(__DIR__) . '/tasks/order_match_panel_sharing_guard_fast.php',
        ];

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/') . '/app/tasks/order_match_panel_sharing_guard_fast.php';
        }

        if (defined('SYS_PATH')) {
            $candidates[] = rtrim((string)SYS_PATH, '/') . '/app/tasks/order_match_panel_sharing_guard_fast.php';
            $candidates[] = rtrim((string)SYS_PATH, '/') . '/tasks/order_match_panel_sharing_guard_fast.php';
        }

        foreach (array_unique($candidates) as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                require_once $candidate;
                break;
            }
        }

        $guardLoaded = true;
    }

    if (!function_exists('lb_order_match_queue_panel_sharing_alert')) {
        return;
    }

    try {
        $newOrderMatchId = 0;
        if (is_object($db) && method_exists($db, 'lastInsertId')) {
            $newOrderMatchId = (int)$db->lastInsertId();
        }

        // ON DUPLICATE KEY UPDATE can return 0 as lastInsertId(), so fetch the row id by the unique match data.
        if ($newOrderMatchId <= 0) {
            if ($puuid !== '') {
                $rows = $db->run(
                    "SELECT id FROM order_matches WHERE order_id = ? AND match_id = ? AND puuid = ? ORDER BY id DESC LIMIT 1",
                    $orderId,
                    $matchId,
                    $puuid
                );
            } else {
                $rows = $db->run(
                    "SELECT id FROM order_matches WHERE order_id = ? AND match_id = ? ORDER BY id DESC LIMIT 1",
                    $orderId,
                    $matchId
                );
            }

            if (is_array($rows) && !empty($rows)) {
                $row = reset($rows);
                if (is_array($row)) {
                    $newOrderMatchId = (int)($row['id'] ?? 0);
                }
            }
        }

        if ($newOrderMatchId > 0) {
            lb_order_match_queue_panel_sharing_alert($db, $newOrderMatchId);
        }
    } catch (\Throwable $e) {
        // Never slow down or break match syncing because of the panel-sharing alert guard.
    }
}

function riot_process_matches(array $matchIds, string $puuid, string $server, int $orderId, $db, int $boosterId = 0, string $playMode = 'solo', ?array $rankSnapshot = null): array
{
    if ($orderId <= 0) {
        throw new \InvalidArgumentException('Invalid order id for match processing.');
    }

    $playMode = strtolower(trim($playMode));
    $playMode = in_array($playMode, ['solo', 'duo'], true) ? $playMode : 'solo';

    // Snapshot the currently assigned booster once, before inserting matches.
    // This preserves who owned the order at sync time even if the order is reassigned later.
    $boosterId = (int) $boosterId;
    if ($boosterId <= 0) {
        try {
            $boosterRow = db_get_row('orders', ['id' => $orderId, 'select' => 'booster_id'], 1);
            $boosterId = (int)($boosterRow['booster_id'] ?? 0);
        } catch (\Throwable $e) {
            $boosterId = 0;
        }
    }
    $boosterIdSnapshot = $boosterId > 0 ? $boosterId : null;

    // Store the live Solo/Duo rank that was current at this sync for every newly created match row.
    $rankSnapshotValue = riot_format_rank_snapshot($rankSnapshot);

    $wins = 0;
    $losses = 0;
    $lastMatchId = null;

    foreach ($matchIds as $matchId) {
        $baseUrl = riot_regional_url($server);
        $url = "{$baseUrl}/lol/match/v5/matches/" . urlencode($matchId);

        $match = riot_api_get($url);

        if (!is_array($match)) {
            continue;
        }

        $participants = $match['info']['participants'] ?? [];
        if (!is_array($participants) || empty($participants)) {
            continue;
        }

        $participant = null;
        foreach ($participants as $p) {
            if ((string) ($p['puuid'] ?? '') === $puuid) {
                $participant = $p;
                break;
            }
        }

        if (!$participant) {
            continue;
        }

        $won = !empty($participant['win']) ? 1 : 0;
        $gameEndMs = (int) ($match['info']['gameEndTimestamp'] ?? 0);
        $gameCreationMs = (int) ($match['info']['gameCreation'] ?? 0);
        $gameDuration = (int) ($match['info']['gameDuration'] ?? 0);
        $gameEndedInEarlySurrender = !empty($participant['gameEndedInEarlySurrender']) ? 1 : 0;
        $gameEndedInSurrender = !empty($participant['gameEndedInSurrender']) ? 1 : 0;
        $isRemake = ($gameEndedInEarlySurrender === 1) || ($gameDuration > 0 && $gameDuration < 300);

        if ($gameEndMs > 0) {
            $playedAtTs = (int) floor($gameEndMs / 1000);
        } elseif ($gameCreationMs > 0) {
            $playedAtTs = (int) floor($gameCreationMs / 1000);
        } else {
            $playedAtTs = time();
        }

        $champion = trim((string) ($participant['championName'] ?? ''));
        $position = trim((string) ($participant['teamPosition'] ?? ''));
        if ($position === '') {
            $position = trim((string) ($participant['individualPosition'] ?? ''));
        }
        if ($position === '') {
            $position = trim((string) ($participant['lane'] ?? ''));
        }

        $matchColumns = riot_ensure_order_matches_remake_columns($db);
        $hasBoosterColumn = !empty($matchColumns['booster_id']);
        $hasPlayModeColumn = !empty($matchColumns['play_mode']);
        $hasRankSnapshotColumn = !empty($matchColumns['rank_snapshot']);
        $hasRemakeColumns = !empty($matchColumns['is_remake'])
            && !empty($matchColumns['game_ended_in_early_surrender'])
            && !empty($matchColumns['game_ended_in_surrender']);

        if ($hasRemakeColumns && $hasBoosterColumn && $hasPlayModeColumn) {
            $db->run(
                "
                INSERT INTO order_matches
                    (order_id, booster_id, play_mode, match_id, puuid, champion, position, kills, deaths, assists, won, is_remake, game_ended_in_early_surrender, game_ended_in_surrender, duration, queue_id, played_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    booster_id = COALESCE(VALUES(booster_id), booster_id),
                    play_mode = VALUES(play_mode),
                    puuid = VALUES(puuid),
                    champion = VALUES(champion),
                    position = VALUES(position),
                    kills = VALUES(kills),
                    deaths = VALUES(deaths),
                    assists = VALUES(assists),
                    won = VALUES(won),
                    is_remake = VALUES(is_remake),
                    game_ended_in_early_surrender = VALUES(game_ended_in_early_surrender),
                    game_ended_in_surrender = VALUES(game_ended_in_surrender),
                    duration = VALUES(duration),
                    queue_id = VALUES(queue_id),
                    played_at = VALUES(played_at)
                ",
                $orderId,
                $boosterIdSnapshot,
                $playMode,
                (string) $matchId,
                $puuid,
                $champion !== '' ? $champion : null,
                $position !== '' ? $position : null,
                max(0, (int) ($participant['kills'] ?? 0)),
                max(0, (int) ($participant['deaths'] ?? 0)),
                max(0, (int) ($participant['assists'] ?? 0)),
                $won,
                $isRemake ? 1 : 0,
                $gameEndedInEarlySurrender,
                $gameEndedInSurrender,
                max(0, $gameDuration),
                (int) ($match['info']['queueId'] ?? 0),
                date('Y-m-d H:i:s', $playedAtTs)
            );
        } elseif ($hasRemakeColumns) {
            $db->run(
                "
                INSERT INTO order_matches
                    (order_id, match_id, puuid, champion, position, kills, deaths, assists, won, is_remake, game_ended_in_early_surrender, game_ended_in_surrender, duration, queue_id, played_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    puuid = VALUES(puuid),
                    champion = VALUES(champion),
                    position = VALUES(position),
                    kills = VALUES(kills),
                    deaths = VALUES(deaths),
                    assists = VALUES(assists),
                    won = VALUES(won),
                    is_remake = VALUES(is_remake),
                    game_ended_in_early_surrender = VALUES(game_ended_in_early_surrender),
                    game_ended_in_surrender = VALUES(game_ended_in_surrender),
                    duration = VALUES(duration),
                    queue_id = VALUES(queue_id),
                    played_at = VALUES(played_at)
                ",
                $orderId,
                (string) $matchId,
                $puuid,
                $champion !== '' ? $champion : null,
                $position !== '' ? $position : null,
                max(0, (int) ($participant['kills'] ?? 0)),
                max(0, (int) ($participant['deaths'] ?? 0)),
                max(0, (int) ($participant['assists'] ?? 0)),
                $won,
                $isRemake ? 1 : 0,
                $gameEndedInEarlySurrender,
                $gameEndedInSurrender,
                max(0, $gameDuration),
                (int) ($match['info']['queueId'] ?? 0),
                date('Y-m-d H:i:s', $playedAtTs)
            );
        } elseif ($hasBoosterColumn && $hasPlayModeColumn) {
            // Fallback when booster_id/play_mode exists but remake columns are not available yet.
            $db->run(
                "
                INSERT INTO order_matches
                    (order_id, booster_id, play_mode, match_id, puuid, champion, position, kills, deaths, assists, won, duration, queue_id, played_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    booster_id = COALESCE(VALUES(booster_id), booster_id),
                    play_mode = VALUES(play_mode),
                    puuid = VALUES(puuid),
                    champion = VALUES(champion),
                    position = VALUES(position),
                    kills = VALUES(kills),
                    deaths = VALUES(deaths),
                    assists = VALUES(assists),
                    won = VALUES(won),
                    duration = VALUES(duration),
                    queue_id = VALUES(queue_id),
                    played_at = VALUES(played_at)
                ",
                $orderId,
                $boosterIdSnapshot,
                $playMode,
                (string) $matchId,
                $puuid,
                $champion !== '' ? $champion : null,
                $position !== '' ? $position : null,
                max(0, (int) ($participant['kills'] ?? 0)),
                max(0, (int) ($participant['deaths'] ?? 0)),
                max(0, (int) ($participant['assists'] ?? 0)),
                $won,
                max(0, $gameDuration),
                (int) ($match['info']['queueId'] ?? 0),
                date('Y-m-d H:i:s', $playedAtTs)
            );
        } elseif ($hasBoosterColumn) {
            // Fallback when booster_id exists but play_mode/remake columns are not available yet.
            $db->run(
                "
                INSERT INTO order_matches
                    (order_id, booster_id, match_id, puuid, champion, position, kills, deaths, assists, won, duration, queue_id, played_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    booster_id = COALESCE(VALUES(booster_id), booster_id),
                    puuid = VALUES(puuid),
                    champion = VALUES(champion),
                    position = VALUES(position),
                    kills = VALUES(kills),
                    deaths = VALUES(deaths),
                    assists = VALUES(assists),
                    won = VALUES(won),
                    duration = VALUES(duration),
                    queue_id = VALUES(queue_id),
                    played_at = VALUES(played_at)
                ",
                $orderId,
                $boosterIdSnapshot,
                (string) $matchId,
                $puuid,
                $champion !== '' ? $champion : null,
                $position !== '' ? $position : null,
                max(0, (int) ($participant['kills'] ?? 0)),
                max(0, (int) ($participant['deaths'] ?? 0)),
                max(0, (int) ($participant['assists'] ?? 0)),
                $won,
                max(0, $gameDuration),
                (int) ($match['info']['queueId'] ?? 0),
                date('Y-m-d H:i:s', $playedAtTs)
            );
        } else {
            // Fallback for installations where the DB user has not yet added the remake/booster columns.
            $db->run(
                "
                INSERT IGNORE INTO order_matches
                    (order_id, match_id, puuid, champion, position, kills, deaths, assists, won, duration, queue_id, played_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ",
                $orderId,
                (string) $matchId,
                $puuid,
                $champion !== '' ? $champion : null,
                $position !== '' ? $position : null,
                max(0, (int) ($participant['kills'] ?? 0)),
                max(0, (int) ($participant['deaths'] ?? 0)),
                max(0, (int) ($participant['assists'] ?? 0)),
                $won,
                max(0, $gameDuration),
                (int) ($match['info']['queueId'] ?? 0),
                date('Y-m-d H:i:s', $playedAtTs)
            );
        }

        if ($hasRankSnapshotColumn && $rankSnapshotValue !== null) {
            try {
                $db->run(
                    "UPDATE order_matches SET rank_snapshot = COALESCE(NULLIF(rank_snapshot, ''), ?) WHERE order_id = ? AND match_id = ?",
                    $rankSnapshotValue,
                    $orderId,
                    (string)$matchId
                );
            } catch (\Throwable $e) {
                // Keep match tracking working even if the snapshot update fails.
            }
        }

        riot_queue_panel_sharing_alert_for_match($db, $orderId, (string)$matchId, $puuid);

        if (!$isRemake) {
            if ($won === 1) {
                $wins++;
            } else {
                $losses++;
            }
        }

        if ($lastMatchId === null) {
            $lastMatchId = (string) $matchId;
        }
    }

    return [
        'wins' => $wins,
        'losses' => $losses,
        'last_match_id' => $lastMatchId,
    ];
}

// Throttle windows for riot_sync_order_progress(). The order views fire an automatic
// sync on every page load, so that path gets the longer window; only an explicit click
// on the refresh button uses the short one.
if (!defined('RIOT_SYNC_MIN_INTERVAL_AUTO')) {
    define('RIOT_SYNC_MIN_INTERVAL_AUTO', 120);
}
if (!defined('RIOT_SYNC_MIN_INTERVAL_FORCED')) {
    define('RIOT_SYNC_MIN_INTERVAL_FORCED', 20);
}

function riot_sync_order_progress(int $orderId, array $order, array $order_options, array $order_account, array $order_progress, $db, int $minIntervalSeconds = 0): array
{
    // Serve the stored row when the last sync is still inside the throttle window.
    // Riot rate limits are per API key, so an order page opened by client, booster and
    // admin at the same time must not trigger three lookups.
    if ($minIntervalSeconds > 0 && !empty($order_progress['last_sync_at'])) {
        $lastSync = strtotime((string) $order_progress['last_sync_at']);
        if ($lastSync !== false && (time() - $lastSync) < $minIntervalSeconds) {
            return $order_progress;
        }
    }

    $riot_id = trim((string) ($order_account['ign'] ?? ''));
    if ($riot_id === '') {
        throw new \RuntimeException('Riot ID is missing on this order. Tracking cannot be refreshed.');
    }

    $server = trim((string) ($order_options['server'] ?? ''));
    if ($server === '') {
        $server = 'euw';
    }

    // Client PUUID is always kept for rank/current progress and for identifying
    // which stored match rows belong to the client account.
    $client_puuid = trim((string) ($order_progress['puuid'] ?? ''));
    if ($client_puuid === '') {
        $client_puuid = (string) (riot_get_puuid($riot_id, $server) ?? '');
        if ($client_puuid === '') {
            throw new \RuntimeException('PUUID not found for Riot ID.');
        }

        if (empty($order_progress)) {
            db_add_row('order_progress', [
                'order_id' => $orderId,
                'puuid' => $client_puuid,
            ]);
        } else {
            db_update_row('order_progress', ['order_id' => $orderId], [
                'puuid' => $client_puuid,
            ]);
        }

        $order_progress['puuid'] = $client_puuid;
    }

    $isDuo = !empty($order_options['is_duo']);
    $booster_puuid = trim((string) ($order_progress['booster_puuid'] ?? ''));

    // Important: for Duo orders with a saved booster Duo account, match tracking
    // must use the booster account, not the client's Riot account. Rank/current
    // progress still uses the client account below.
    $tracking_puuid = ($isDuo && $booster_puuid !== '') ? $booster_puuid : $client_puuid;

    // Track the ladder selected on the order. Without the queue type this
    // defaults to Solo/Duo, which displays the wrong rank for Flex orders.
    $rank = riot_get_rank(
        $client_puuid,
        $server,
        $order_options['queue_type'] ?? 'solo/duo'
    );

    // For Duo orders: use booster_ign_set_at as tracking start time if available.
    // Games played before the duo account was linked are excluded.
    $start_time = riot_order_sync_start_time($order);
    if ($isDuo && $booster_puuid !== '') {
        $duo_set_at = trim((string) ($order_progress['booster_ign_set_at'] ?? ''));
        if ($duo_set_at !== '' && $duo_set_at !== '0000-00-00 00:00:00') {
            $duo_set_ts = strtotime($duo_set_at);
            if ($duo_set_ts !== false && $duo_set_ts > 0) {
                $start_time = (string) $duo_set_ts;
            }
        }
    }

    $last_match_id = trim((string) ($order_progress['last_match_id'] ?? ''));

    if ($last_match_id !== '') {
        // If all match rows were deleted, or if the latest stored cursor belongs
        // to a different PUUID, ignore the cursor. Otherwise Riot may return 0
        // matches because the same matchId exists on both the client and Duo
        // booster accounts.
        try {
            $cursorRows = $db->run(
                "SELECT COUNT(*) AS total_rows,
                        COALESCE(SUM(CASE WHEN puuid = ? THEN 1 ELSE 0 END), 0) AS rows_for_tracking
                   FROM order_matches
                  WHERE order_id = ?",
                $tracking_puuid,
                $orderId
            );
            $cursorRow = is_array($cursorRows) ? ($cursorRows[0] ?? []) : [];
            $totalRows = (int)($cursorRow['total_rows'] ?? 0);
            $rowsForTracking = (int)($cursorRow['rows_for_tracking'] ?? 0);
            if ($totalRows === 0 || $rowsForTracking === 0) {
                $last_match_id = '';
            }
        } catch (\Throwable $e) {
            // Keep sync working even if the safety query fails.
        }
    }

    $match_ids = riot_get_matches(
        $tracking_puuid,
        $server,
        $start_time,
        $last_match_id !== '' ? $last_match_id : null,
        $order_options['queue_type'] ?? 'solo/duo'
    );

    $playMode = $isDuo ? 'duo' : 'solo';
    $match_summary = riot_process_matches($match_ids, $tracking_puuid, $server, $orderId, $db, (int)($order['booster_id'] ?? 0), $playMode, $rank);

    save_riot_rank($orderId, $rank, $db, $match_summary);

    return db_get_row('order_progress', ['order_id' => $orderId], 1) ?: [];
}

function get_order_matches(int $orderId, int $page = 1, int $perPage = 20, $db = null): array
{
    if ($orderId <= 0) {
        return ['rows' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage, 'pages' => 0];
    }

    if ($db === null) {
        global $db;
    }

    $perPage = max(1, min(100, $perPage));
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;

    $totalResult = $db->run(
        "SELECT COUNT(*) AS cnt FROM order_matches WHERE order_id = ? AND COALESCE(is_hidden, 0) = 0",
        $orderId
    );
    $total = (int) ($totalResult[0]['cnt'] ?? 0);
    $pages = $total > 0 ? (int) ceil($total / $perPage) : 0;

    if ($total === 0) {
        return ['rows' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage, 'pages' => 0];
    }

    $matchColumns = riot_ensure_order_matches_remake_columns($db);
    $boosterIdExpr = !empty($matchColumns['booster_id'])
        ? 'COALESCE(om.booster_id, o.booster_id)'
        : 'o.booster_id';
    $playModeExpr = !empty($matchColumns['play_mode'])
        ? "COALESCE(NULLIF(om.play_mode, ''), CASE WHEN COALESCE(oo.is_duo, 0) = 1 THEN 'duo' ELSE 'solo' END)"
        : "CASE WHEN COALESCE(oo.is_duo, 0) = 1 THEN 'duo' ELSE 'solo' END";

    if (!empty($matchColumns['is_remake'])) {
        $select = "SELECT om.id, om.match_id, om.champion, om.position, om.kills, om.deaths, om.assists, om.won, om.is_remake, om.game_ended_in_early_surrender, om.game_ended_in_surrender, om.duration, om.queue_id, om.played_at, COALESCE(om.rank_snapshot, '') AS rank_snapshot, {$boosterIdExpr} AS booster_id, b.username AS booster_username, COALESCE(b.icon, '') AS booster_icon, {$playModeExpr} AS play_mode, om.puuid AS match_puuid, op.puuid AS client_puuid, op.booster_puuid AS booster_puuid";
    } else {
        $select = "SELECT om.id, om.match_id, om.champion, om.position, om.kills, om.deaths, om.assists, om.won, om.duration, om.queue_id, om.played_at, COALESCE(om.rank_snapshot, '') AS rank_snapshot, {$boosterIdExpr} AS booster_id, b.username AS booster_username, COALESCE(b.icon, '') AS booster_icon, {$playModeExpr} AS play_mode";
    }

    $rows = $db->run(
        "$select
         FROM order_matches om
         LEFT JOIN orders o ON o.id = om.order_id
         LEFT JOIN order_options oo ON oo.order_id = om.order_id
         LEFT JOIN order_progress op ON op.order_id = om.order_id
         LEFT JOIN boosters b ON b.id = {$boosterIdExpr}
         WHERE om.order_id = ?
           AND COALESCE(om.is_hidden, 0) = 0
         ORDER BY om.played_at DESC, om.id DESC
         LIMIT ? OFFSET ?",
        $orderId,
        $perPage,
        $offset
    );

    if (is_array($rows)) {
        foreach ($rows as &$row) {
            $duration = (int)($row['duration'] ?? 0);
            $storedRemake = (int)($row['is_remake'] ?? 0) === 1;
            $durationFallbackRemake = ($duration > 0 && $duration < 300);
            $row['is_remake'] = ($storedRemake || $durationFallbackRemake) ? 1 : 0;
            $row['result'] = ((int)$row['is_remake'] === 1) ? 'remake' : (((int)($row['won'] ?? 0) === 1) ? 'win' : 'loss');

            $boosterId = (int)($row['booster_id'] ?? 0);
            $boosterName = trim((string)($row['booster_username'] ?? ''));
            $row['booster_id'] = $boosterId > 0 ? $boosterId : null;
            $row['booster_name'] = $boosterName !== '' ? $boosterName : ($boosterId > 0 ? ('#' . $boosterId) : 'Unassigned');

            $boosterIcon = trim((string)($row['booster_icon'] ?? ''));
            $row['booster_icon'] = $boosterIcon;
            // Compatibility aliases for match-history UIs that read a generic avatar/icon field.
            $row['booster_avatar'] = $boosterIcon;
            $row['avatar'] = $boosterIcon;
            $row['icon'] = $boosterIcon;

            $playMode = strtolower(trim((string)($row['play_mode'] ?? 'solo')));
            $playMode = in_array($playMode, ['solo', 'duo'], true) ? $playMode : 'solo';
            $row['play_mode'] = $playMode;
            $row['match_type'] = $playMode;

            $matchPuuid = strtolower(trim((string)($row['match_puuid'] ?? '')));
            $clientPuuid = strtolower(trim((string)($row['client_puuid'] ?? '')));
            $boosterPuuid = strtolower(trim((string)($row['booster_puuid'] ?? '')));

            $rankSnapshot = trim((string)($row['rank_snapshot'] ?? ''));
            $row['rank_snapshot'] = $rankSnapshot;
            // Match History rank must be derived only from the stored per-game snapshot.
            // Example: GOLD IV 61 LP -> G4 61LP, GOLD I -> G1.
            $row['rank_display'] = function_exists('riot_short_rank_snapshot_label') ? riot_short_rank_snapshot_label($rankSnapshot) : $rankSnapshot;
            // Some match-history UIs read `rank` instead of `rank_snapshot`.
            $row['rank'] = $rankSnapshot;

            if ($playMode !== 'duo') {
                $row['stat_subject'] = 'booster';
            } elseif ($boosterPuuid !== '') {
                // Saved Duo account is the source of truth.
                // Only that exact PUUID is booster stats; everything else is client/other stats.
                $row['stat_subject'] = ($matchPuuid !== '' && hash_equals($boosterPuuid, $matchPuuid)) ? 'booster' : 'client';
            } elseif ($matchPuuid !== '' && $clientPuuid !== '' && hash_equals($clientPuuid, $matchPuuid)) {
                $row['stat_subject'] = 'client';
            } elseif ($matchPuuid !== '' && $clientPuuid !== '' && !hash_equals($clientPuuid, $matchPuuid)) {
                // Fallback only when no booster_puuid is stored yet:
                // if the match is not from the client account, treat it as booster stats.
                $row['stat_subject'] = 'booster';
            } else {
                $row['stat_subject'] = 'client';
            }
        }
        unset($row);
    }

    return [
        'rows' => is_array($rows) ? $rows : [],
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'pages' => $pages,
    ];
}

// ══════════════════════════════════════════════════════════════════════════════
//  DIGITAL GOODS — Helper Functions
//  Modell: identisch zu selling_items + selling_item_purchases
//  Chat: JSON-Dateien wie bei item_purchase_chat_path()
// ══════════════════════════════════════════════════════════════════════════════

function dg_chat_path(int $purchase_id): string
{
    return SYS_PATH . '/public/uploads/private/chat/dg_' . sha1('dg_purchase_' . $purchase_id) . '.json';
}

// ── Kategorien ────────────────────────────────────────────────────────────────

// Default FontAwesome icons per category slug (used if DB icon is empty)
function dg_default_icon(string $slug): string {
    $map = [
        'streaming'      => 'fa-solid fa-play-circle',
        'software'       => 'fa-solid fa-microchip',
        'subscriptions'  => 'fa-solid fa-repeat',
        'gaming-credits' => 'fa-solid fa-coins',
        'gift-cards'     => 'fa-solid fa-gift',
        'vpn'            => 'fa-solid fa-shield-halved',
        'antivirus'      => 'fa-solid fa-bug-slash',
        'music'          => 'fa-solid fa-music',
        'cloud'          => 'fa-solid fa-cloud',
        'productivity'   => 'fa-solid fa-chart-line',
    ];
    return $map[$slug] ?? 'fa-solid fa-layer-group';
}

function dg_get_categories(bool $activeOnly = true): array
{
    global $db;
    $where = $activeOnly ? "WHERE active=1" : "";
    $rows  = $db->run("SELECT * FROM digital_good_categories {$where} ORDER BY sort_order ASC, name ASC") ?: [];
    // Fill default icons where empty
    foreach ($rows as &$row) {
        if (empty(trim((string)($row['icon'] ?? '')))) {
            $row['icon'] = dg_default_icon((string)($row['slug'] ?? ''));
        }
    }
    unset($row);
    return $rows;
}

function dg_get_category($slugOrId): ?array
{
    global $db;
    $field = is_numeric($slugOrId) ? 'id' : 'slug';
    $rows  = $db->run("SELECT * FROM digital_good_categories WHERE {$field}=? LIMIT 1", $slugOrId);
    if (empty($rows[0])) return null;
    $row = $rows[0];
    if (empty(trim((string)($row['icon'] ?? '')))) {
        $row['icon'] = dg_default_icon((string)($row['slug'] ?? ''));
    }
    return $row;
}

// ── Listings ──────────────────────────────────────────────────────────────────

function dg_get_listings(int $categoryId = 0, array $filters = [], int $limit = 50, int $offset = 0): array
{
    global $db;
    $where  = "dg.active=1 AND dg.stock>0";
    $params = [];
    if ($categoryId > 0)          { $where .= " AND dg.category_id=?"; $params[] = $categoryId; }
    if (!empty($filters['seller_id'])) { $where .= " AND dg.seller_id=?"; $params[] = (int)$filters['seller_id']; }
    if (!empty($filters['brand']))     { $where .= " AND dg.brand=?";     $params[] = esc($filters['brand']); }
    if (!empty($filters['region']))    { $where .= " AND (dg.region IS NULL OR dg.region=?)"; $params[] = esc($filters['region']); }
    if (!empty($filters['search']))    { $where .= " AND (dg.title LIKE ? OR dg.brand LIKE ?)"; $s = '%'.esc($filters['search']).'%'; $params[] = $s; $params[] = $s; }

    $orderBy = "dg.sort_order ASC, dg.sold_count DESC";
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_asc':  $orderBy = "dg.price ASC"; break;
            case 'price_desc': $orderBy = "dg.price DESC"; break;
            case 'newest':     $orderBy = "dg.created_at DESC"; break;
            case 'oldest':     $orderBy = "dg.created_at ASC"; break;
            case 'sold_desc':  $orderBy = "dg.sold_count DESC"; break;
        }
    }

    $params[] = $limit;
    $params[] = $offset;
    return $db->run(
        "SELECT dg.*,
                dgc.name AS category_name, dgc.slug AS category_slug, dgc.icon AS category_icon,
                s.username AS seller_username, s.icon AS seller_icon,
                COALESCE(AVG(dgr.rating),0) AS avg_rating,
                COUNT(dgr.id) AS review_count
         FROM digital_goods dg
         LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id
         LEFT JOIN sellers s                   ON s.id=dg.seller_id
         LEFT JOIN seller_reviews dgr           ON dgr.digital_good_id=dg.id AND dgr.review_source='digital_good' AND dgr.approved=1
         WHERE {$where}
         GROUP BY dg.id
         ORDER BY {$orderBy}
         LIMIT ? OFFSET ?",
        ...$params
    ) ?: [];
}

function dg_count_listings(int $categoryId = 0, array $filters = []): int
{
    global $db;
    $where  = "active=1 AND stock>0";
    $params = [];
    if ($categoryId > 0)          { $where .= " AND category_id=?"; $params[] = $categoryId; }
    if (!empty($filters['brand']))  { $where .= " AND brand=?"; $params[] = esc($filters['brand']); }
    if (!empty($filters['region'])) { $where .= " AND (region IS NULL OR region=?)"; $params[] = esc($filters['region']); }
    if (!empty($filters['search'])) { $where .= " AND (title LIKE ? OR brand LIKE ?)"; $s = '%'.esc($filters['search']).'%'; $params[] = $s; $params[] = $s; }
    $rows = $db->run("SELECT COUNT(*) AS cnt FROM digital_goods WHERE {$where}", ...$params);
    return (int)($rows[0]['cnt'] ?? 0);
}

function dg_get_listing($idOrSlug): ?array
{
    global $db;
    $field = is_numeric($idOrSlug) ? 'dg.id' : 'dg.slug';
    $rows  = $db->run(
        "SELECT dg.*, dgc.name AS category_name, dgc.slug AS category_slug, dgc.icon AS category_icon,
                s.username AS seller_username, s.icon AS seller_icon,
                COALESCE(AVG(dgr.rating),0) AS avg_rating, COUNT(dgr.id) AS review_count
         FROM digital_goods dg
         LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id
         LEFT JOIN sellers s                   ON s.id=dg.seller_id
         LEFT JOIN seller_reviews dgr           ON dgr.digital_good_id=dg.id AND dgr.review_source='digital_good' AND dgr.approved=1
         WHERE {$field}=?
         GROUP BY dg.id LIMIT 1",
        $idOrSlug
    );
    return !empty($rows[0]) ? $rows[0] : null;
}

function dg_get_seller_listings(int $sellerId, bool $activeOnly = false): array
{
    global $db;
    $where = "dg.seller_id=?";
    if ($activeOnly) $where .= " AND dg.active=1";
    return $db->run(
        "SELECT dg.*, dgc.name AS category_name, dgc.slug AS category_slug
         FROM digital_goods dg
         LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id
         WHERE {$where} ORDER BY dg.created_at DESC",
        $sellerId
    ) ?: [];
}

// ── Purchases ─────────────────────────────────────────────────────────────────

function dg_get_purchase(int $purchaseId): ?array
{
    global $db;
    $rows = $db->run(
        "SELECT dgp.*, CASE WHEN dgp.status='UNPAID' AND inv.status='PAID' THEN 'PAID' ELSE dgp.status END AS status,
                dg.title AS item_title, dg.brand_icon AS item_brand_icon, dg.brand_icon AS listing_brand_icon, dg.brand_icon AS brand_icon, dg.delivery_type,
                dg.delivery_instructions, dg.brand, dg.validity_days,
                dgc.name AS category_name, dgc.slug AS category_slug,
                s.username AS seller_username, s.icon AS seller_icon,
                c.username AS client_username, c.icon AS client_icon, c.email AS client_email
         FROM digital_good_purchases dgp
         LEFT JOIN invoices inv                ON inv.id=dgp.invoice_id
         LEFT JOIN digital_goods dg            ON dg.id=COALESCE(NULLIF(dgp.item_id,0), NULLIF(inv.order_id,0))
         LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id
         LEFT JOIN sellers s                   ON s.id=dgp.seller_id
         LEFT JOIN clients c                   ON c.id=dgp.client_id
         WHERE dgp.id=? LIMIT 1",
        $purchaseId
    );
    return !empty($rows[0]) ? $rows[0] : null;
}

function dg_get_client_purchases(int $clientId, string $status = '', int $limit = 20, int $offset = 0): array
{
    global $db;
    $effectiveStatus = "CASE WHEN dgp.status='UNPAID' AND inv.status='PAID' THEN 'PAID' ELSE dgp.status END";
    $where = "dgp.client_id=?"; $params = [$clientId];
    if ($status !== '') { $where .= " AND {$effectiveStatus}=?"; $params[] = $status; }
    $params[] = $limit; $params[] = $offset;
    return $db->run(
        "SELECT dgp.*, {$effectiveStatus} AS status, dg.title AS item_title, dg.brand_icon AS item_brand_icon, dg.brand_icon AS listing_brand_icon, dg.brand_icon AS brand_icon, dg.brand,
                dgc.name AS category_name, s.username AS seller_username, s.icon AS seller_icon
         FROM digital_good_purchases dgp
         LEFT JOIN invoices inv                ON inv.id=dgp.invoice_id
         LEFT JOIN digital_goods dg            ON dg.id=COALESCE(NULLIF(dgp.item_id,0), NULLIF(inv.order_id,0))
         LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id
         LEFT JOIN sellers s                   ON s.id=dgp.seller_id
         WHERE {$where} ORDER BY dgp.created_at DESC LIMIT ? OFFSET ?",
        ...$params
    ) ?: [];
}

function dg_get_seller_purchases(int $sellerId, string $status = '', int $limit = 50, int $offset = 0): array
{
    global $db;
    $effectiveStatus = "CASE WHEN dgp.status='UNPAID' AND inv.status='PAID' THEN 'PAID' ELSE dgp.status END";
    $where = "dgp.seller_id=?"; $params = [$sellerId];
    if ($status !== '') { $where .= " AND {$effectiveStatus}=?"; $params[] = $status; }
    $params[] = $limit; $params[] = $offset;
    return $db->run(
        "SELECT dgp.*, {$effectiveStatus} AS status, dg.title AS item_title, dg.brand_icon AS item_brand_icon, dg.brand_icon AS listing_brand_icon, dg.brand_icon AS brand_icon, dg.brand,
                dgc.name AS category_name, c.username AS client_username, c.email AS client_email,
                -- Guest checkouts leave clients.icon NULL. Hand the list a usable avatar URL
                -- here so the Buyer column never falls back to the letter.
                COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS client_icon,
                COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS buyer_icon
         FROM digital_good_purchases dgp
         LEFT JOIN invoices inv                ON inv.id=dgp.invoice_id
         LEFT JOIN digital_goods dg            ON dg.id=COALESCE(NULLIF(dgp.item_id,0), NULLIF(inv.order_id,0))
         LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id
         LEFT JOIN clients c                   ON c.id=dgp.client_id
         WHERE {$where} ORDER BY dgp.created_at DESC LIMIT ? OFFSET ?",
        ...$params
    ) ?: [];
}

function dg_create_purchase(int $itemId, int $clientId, int $quantity = 1, array $extra = [])
{
    global $db;
    $listing = dg_get_listing($itemId);
    if (!$listing || !(int)$listing['active']) return false;
    if ((int)$listing['stock'] < $quantity) return false;
    $unit  = (int)$listing['price'];
    $total = $unit * $quantity;
    $db->run(
        "INSERT INTO digital_good_purchases (item_id,seller_id,client_id,quantity,unit_price,price,currency,region,customer_note,status) VALUES (?,?,?,?,?,?,?,?,?,'UNPAID')",
        $itemId, (int)$listing['seller_id'], $clientId, $quantity, $unit, $total,
        esc($extra['currency'] ?? $listing['currency'] ?? 'EUR'),
        esc($extra['region']   ?? $listing['region']   ?? ''),
        esc($extra['customer_note'] ?? '')
    );
    $purchaseId = (int)$db->lastInsertId();
    if (!$purchaseId) return false;
    dg_log($purchaseId, 'system', 0, 'purchase_created', ['item_id' => $itemId, 'quantity' => $quantity]);
    return $purchaseId;
}

function dg_mark_paid(int $purchaseId, ?int $invoiceId = null): bool
{
    global $db;
    $purchase = dg_get_purchase($purchaseId);
    if (!$purchase || $purchase['status'] !== 'UNPAID') return false;
    $now = date('Y-m-d H:i:s');
    $db->run("UPDATE digital_good_purchases SET status='PAID',invoice_id=?,paid_at=? WHERE id=?", $invoiceId, $now, $purchaseId);
    $db->run("UPDATE digital_goods SET stock=GREATEST(0,stock-?),sold_count=sold_count+? WHERE id=?", (int)$purchase['quantity'], (int)$purchase['quantity'], (int)$purchase['item_id']);
    if (!empty($purchase['seller_id']) && function_exists('sync_seller_stats')) sync_seller_stats((int)$purchase['seller_id']);
    dg_log($purchaseId, 'system', 0, 'purchase_paid', ['invoice_id' => $invoiceId]);
    dg_chat_append($purchaseId, 'system', 0, 'System', '✅ Payment received. The seller has been notified and will deliver shortly.');
    return true;
}

/**
 * Complete digital-good deliveries that the buyer did not confirm within 24 hours.
 * Safe to run repeatedly from cron or another background worker.
 */
function dg_auto_complete_overdue_purchases(int $limit = 250): int
{
    global $db;

    $limit = max(1, min(1000, $limit));
    $rows = $db->run(
        "SELECT id
         FROM digital_good_purchases
         WHERE status = 'DELIVERED'
           AND delivered_at IS NOT NULL
           AND delivered_at <> '0000-00-00 00:00:00'
           AND delivered_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
         ORDER BY delivered_at ASC
         LIMIT {$limit}"
    ) ?: [];

    $completed = 0;
    foreach ($rows as $row) {
        $purchaseId = (int)($row['id'] ?? 0);
        if ($purchaseId <= 0) {
            continue;
        }

        $now = date('Y-m-d H:i:s');
        $db->run(
            "UPDATE digital_good_purchases
             SET status = 'COMPLETED',
                 completed_at = COALESCE(completed_at, ?),
                 updated_at = ?
             WHERE id = ?
               AND status = 'DELIVERED'
               AND delivered_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            $now,
            $now,
            $purchaseId
        );

        $check = $db->row(
            "SELECT status, completed_at
             FROM digital_good_purchases
             WHERE id = ?
             LIMIT 1",
            $purchaseId
        );
        if (strtoupper(trim((string)($check['status'] ?? ''))) !== 'COMPLETED'
            || trim((string)($check['completed_at'] ?? '')) !== $now) {
            continue;
        }

        $completed++;
        try {
            dg_log($purchaseId, 'system', 0, 'auto_completed_after_24h');
            dg_chat_append($purchaseId, 'system', 0, 'System', 'Delivery automatically confirmed after 24 hours.');
        } catch (Throwable $e) {
            error_log('dg_auto_complete_overdue_purchases side effect failed for #' . $purchaseId . ': ' . $e->getMessage());
        }
    }

    return $completed;
}



/**
 * Creates or updates a digital-good purchase for a paid invoice.
 * Mirrors the normal LoL order flow: once the invoice is PAID, the order row is PAID.
 * Idempotent: repeated /process or /complete hits are safe.
 */
function dg_mark_invoice_paid(array $invoice): ?int
{
    global $db;

    if (($invoice['order_type'] ?? '') !== 'digital_good') return null;

    $invoiceId = (int)($invoice['id'] ?? 0);
    $itemId    = (int)($invoice['order_id'] ?? 0);
    $clientId  = (int)($invoice['client_id'] ?? 0);
    if ($invoiceId <= 0 || $itemId <= 0) return null;

    $listing = function_exists('dg_get_listing') ? dg_get_listing($itemId) : db_get_row('digital_goods', ['id' => $itemId]);
    if (!$listing || !is_array($listing)) return null;

    $existing = $db->row("SELECT id, status FROM digital_good_purchases WHERE invoice_id = ? LIMIT 1", $invoiceId);
    $unit     = (int)($listing['price'] ?? 0);
    $totalEur = (int)($invoice['price_eur'] ?? $invoice['total_price'] ?? 0);
    $qty      = max(1, (int)round($totalEur / max(1, $unit)));
    $now      = date('Y-m-d H:i:s');

    if (!empty($existing['id'])) {
        $purchaseId = (int)$existing['id'];
        if (strtoupper((string)($existing['status'] ?? '')) === 'UNPAID') {
            $db->run(
                "UPDATE digital_good_purchases
                 SET status='PAID', client_id=COALESCE(NULLIF(client_id,0), ?), paid_at=COALESCE(paid_at, ?), updated_at=?
                 WHERE id=?",
                $clientId ?: null, $now, $now, $purchaseId
            );
            if (function_exists('dg_log')) dg_log($purchaseId, 'system', 0, 'purchase_paid', ['invoice_id' => $invoiceId]);
            if (function_exists('dg_chat_append')) dg_chat_append($purchaseId, 'system', 0, 'System', '✅ Payment confirmed! The seller has been notified and will deliver shortly.');
        }
        return $purchaseId;
    }

    $db->run(
        "INSERT INTO digital_good_purchases
         (item_id, seller_id, client_id, invoice_id, quantity, unit_price, price, currency, status, paid_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PAID', ?, ?, ?)",
        $itemId,
        (int)($listing['seller_id'] ?? 0),
        $clientId ?: null,
        $invoiceId,
        $qty,
        $unit,
        $unit * $qty,
        $invoice['currency'] ?? 'EUR',
        $now, $now, $now
    );

    $row = $db->row("SELECT id FROM digital_good_purchases WHERE invoice_id = ? ORDER BY id DESC LIMIT 1", $invoiceId);
    $purchaseId = (int)($row['id'] ?? 0);

    if ($purchaseId > 0) {
        $db->run("UPDATE digital_goods SET stock = GREATEST(0, stock - ?), sold_count = sold_count + ? WHERE id = ?", $qty, $qty, $itemId);
        $listing = function_exists('dg_get_listing') ? dg_get_listing($itemId) : null;
        if (!empty($listing['seller_id']) && function_exists('sync_seller_stats')) sync_seller_stats((int)$listing['seller_id']);
        if (function_exists('dg_log')) dg_log($purchaseId, 'system', 0, 'purchase_paid', ['invoice_id' => $invoiceId]);
        if (function_exists('dg_chat_append')) dg_chat_append($purchaseId, 'system', 0, 'System', '✅ Payment confirmed! The seller has been notified and will deliver shortly.');
    }

    return $purchaseId > 0 ? $purchaseId : null;
}
// ── Logging ───────────────────────────────────────────────────────────────────

function dg_log(int $purchaseId, string $actorType, int $actorId, string $action, array $meta = []): void
{
    global $db;
    $db->run("INSERT INTO digital_good_logs (purchase_id,actor_type,actor_id,action,meta) VALUES (?,?,?,?,?)",
        $purchaseId, $actorType, $actorId, $action,
        empty($meta) ? null : json_encode($meta, JSON_UNESCAPED_UNICODE)
    );
}

// ── Chat ──────────────────────────────────────────────────────────────────────

function dg_chat_append(int $purchaseId, string $sender, int $senderId, string $senderName, string $message = '', ?string $imageUrl = null): array
{
    $chatPath = dg_chat_path($purchaseId);
    $dir      = dirname($chatPath);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $fp = @fopen($chatPath, 'c+');
    if (!$fp) return ['success' => false, 'message' => 'Could not open chat file.'];
    flock($fp, LOCK_EX);
    $raw      = stream_get_contents($fp);
    $chatData = json_decode($raw ?: '', true);
    if (!is_array($chatData)) $chatData = ['purchase_id' => $purchaseId, 'messages' => []];
    if (!isset($chatData['messages'])) $chatData['messages'] = [];
    $now  = date('Y-m-d H:i:s');
    $base = ['type'=>$sender,'sender'=>$sender,'sender_id'=>$senderId,'sender_name'=>$senderName,'message_type'=>'text','created_at'=>$now,'time'=>time(),'seen'=>0,'notify'=>0,'seen_by_seller'=>($sender==='seller')?1:0,'seen_by_client'=>($sender==='client')?1:0,'seen_by_admin'=>($sender==='admin')?1:0];
    if ($message !== '') $chatData['messages'][] = array_merge($base, ['message'=>$message,'content'=>$message]);
    if ($imageUrl)       $chatData['messages'][] = array_merge($base, ['message'=>$imageUrl,'content'=>'<img src="'.htmlspecialchars($imageUrl,ENT_QUOTES).'" alt="image">','message_type'=>'image']);
    rewind($fp); ftruncate($fp, 0); fwrite($fp, json_encode($chatData, JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN); fclose($fp);
    return ['success' => true, 'created_at' => $now];
}

function dg_chat_normalize(array $messages): array
{
    $cache = []; $normalized = [];
    foreach ($messages as $i => $m) {
        $sender   = $m['sender'] ?? $m['type'] ?? 'seller';
        $sid      = (int)($m['sender_id'] ?? 0);
        $msgText  = $m['message'] ?? '';
        $isImage  = ($m['message_type'] ?? '') === 'image';
        $key      = $sender.':'.$sid;
        if (!isset($cache[$key])) {
            $stored = (string)($m['sender_icon'] ?? '');
            if ($stored !== '') { $cache[$key] = $stored; }
            elseif ($sid > 0) {
                $table = $sender === 'client' ? 'clients' : ($sender === 'seller' ? 'sellers' : 'admins');
                $row = db_get_row($table, ['id' => $sid, 'select' => 'icon']);
                $cache[$key] = !empty($row['icon']) ? $row['icon'] : '';
            } else { $cache[$key] = ''; }
        }
        $normalized[$i] = [
            'sender'         => $sender, 'sender_id' => $sid,
            'sender_name'    => $m['sender_name'] ?? ucfirst($sender),
            'sender_icon'    => $cache[$key] ?? '',
            'content'        => $m['content'] ?? ($isImage ? '<img src="'.htmlspecialchars($msgText,ENT_QUOTES).'" alt="image">' : $msgText),
            'time'           => $m['time'] ?? (isset($m['created_at']) ? strtotime($m['created_at']) : 0),
            'message_type'   => $m['message_type'] ?? 'text',
            'deleted'        => $m['deleted'] ?? 0,
            'seen'           => (int)($m['seen'] ?? 0),
            'seen_by_seller' => (int)($m['seen_by_seller'] ?? 0),
            'seen_by_client' => (int)($m['seen_by_client'] ?? 0),
            'seen_by_admin'  => (int)($m['seen_by_admin']  ?? 0),
        ];
    }
    return $normalized;
}

// ── Formatierung ──────────────────────────────────────────────────────────────

function dg_format_price(int $cents, string $currency = 'EUR'): string
{
    $symbols = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'];
    $sym     = $symbols[$currency] ?? $currency . ' ';
    return $sym . number_format($cents / 100, 2, '.', ',');
}

function dg_listing_url(array $listing): string
{
    // Canonical digital-good URLs use the stored unique slug.
    // The slug is generated from the title only; duplicates are saved as -2, -3, -4 ...
    // Example: /digital-good/youtube-1-month, then /digital-good/youtube-1-month-2.
    $slug = trim((string)($listing['slug'] ?? ''), '/');

    if ($slug === '') {
        $title = trim((string)($listing['title'] ?? ''));
        if ($title !== '') {
            if (function_exists('slugify')) {
                $slug = slugify($title);
            } else {
                if (function_exists('iconv')) {
                    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
                    if ($converted !== false) $title = $converted;
                }
                $slug = strtolower($title);
                $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
                $slug = trim((string)$slug, '-');
            }
        }
    }

    if ($slug === '' || $slug === 'n-a') {
        $slug = 'digital-good';
    }

    return BASE_URL . '/digital-good/' . rawurlencode($slug);
}


// ── Digital Goods Brands ─────────────────────────────────────────────────────
if (!function_exists('dg_brand_slugify')) {
    function dg_brand_slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') return '';
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) $value = $converted;
        }
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim((string)$value, '-');
    }
}

if (!function_exists('dg_ensure_brand_table')) {
    function dg_ensure_brand_table(): void
    {
        global $db;
        try {
            $db->run("CREATE TABLE IF NOT EXISTS digital_good_brands (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(120) NOT NULL,
                slug VARCHAR(140) NOT NULL,
                icon_path VARCHAR(255) NOT NULL DEFAULT '',
                active TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_digital_good_brands_slug (slug),
                KEY idx_digital_good_brands_active_sort (active, sort_order, name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            error_log('dg_ensure_brand_table failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('dg_seed_default_brands')) {
    function dg_seed_default_brands(): void
    {
        global $db;
        dg_ensure_brand_table();
        $defaults = [
            ['YouTube', '/website/images/digital-goods/youtube.png'],
            ['Spotify', '/website/images/digital-goods/spotify.jpg'],
            ['Netflix', '/website/images/digital-goods/netflix.svg'],
            ['Nord VPN', '/website/images/digital-goods/nord-vpn.jpeg'],
            ['Discord Nitro', '/website/images/digital-goods/discord-nitro.png'],
            ['ChatGPT', '/website/images/digital-goods/chat-gpt.png'],
            ['Xbox Game Pass', '/website/images/digital-goods/xbox-gamepass.jpg'],
            ['Hytale', '/website/images/digital-goods/hytale.webp'],
            ['AdGuard Premium', '/website/images/digital-goods/adguard-premium.webp'],
            ['VoiceMod Pro', '/website/images/digital-goods/voicemod-pro.webp'],
            ['Perplexity', '/website/images/digital-goods/perplexity.webp'],
            ['Deezer', '/website/images/digital-goods/deezer.webp'],
            ['Fortnite V-Bucks', '/website/images/digital-goods/fortnite-vbucks.webp'],
            ['Grok', '/website/images/digital-goods/grok.webp'],
            ['Warframe', '/website/images/digital-goods/warframe.webp'],
            ['Rocket League', '/website/images/digital-goods/rocket-league.webp'],
            ['LinkedIn', '/website/images/digital-goods/linkedin.webp'],
            ['RuneScape', '/website/images/digital-goods/runescape-fantasy.webp'],
            ['Evernote', '/website/images/digital-goods/evernote.webp'],
            ['Canva', '/website/images/digital-goods/canva.webp'],
            ['PhotoRoom', '/website/images/digital-goods/photoroom.webp'],
            ['Grammarly', '/website/images/digital-goods/grammarly.webp'],
            ['F1 TV', '/website/images/digital-goods/f1-tv.webp'],
            ['Steam', '/website/images/digital-goods/steam.webp'],
            ['Snapchat', '/website/images/digital-goods/snapchat.webp'],
            ['HBO', '/website/images/digital-goods/hbo.webp'],
            ['Bumble', '/website/images/digital-goods/bumble.webp'],
            ['Disney+', '/website/images/digital-goods/disney.webp'],
            ['CapCut', '/website/images/digital-goods/capcut.webp'],
            ['Duolingo', '/website/images/digital-goods/duolingo.webp'],
            ['NBA League Pass', '/website/images/digital-goods/nba-pass.webp'],
            ['Reddit', '/website/images/digital-goods/reddit.webp'],
            ['MedalTV', '/website/images/digital-goods/medaltv.webp'],
            ['Turbo VPN', '/website/images/digital-goods/turbo-vpn.webp'],
            ['Prime Video', '/website/images/digital-goods/prime-video.webp'],
            ['Twitch', '/website/images/digital-goods/twitch.webp'],
            ['Adobe Creative Cloud', '/website/images/digital-goods/adobe-creative-cloud.webp'],
            ['Badoo', '/website/images/digital-goods/badoo.webp'],
            ['Claude', '/website/images/digital-goods/claude.webp'],
            ['Epic Games', '/website/images/digital-goods/epic-games.webp'],
            ['Crunchyroll', '/website/images/digital-goods/crunchyroll.webp'],
            ['Tinder', '/website/images/digital-goods/tinder.webp'],
            ['PS Plus', '/website/images/digital-goods/ps-plus.webp'],
            ['Gemini', '/website/images/digital-goods/gemini.webp'],
            ['COD Points', '/website/images/digital-goods/cod-points.webp'],
        ];
        foreach ($defaults as $i => $brand) {
            [$name, $icon] = $brand;
            $slug = dg_brand_slugify($name);
            if ($slug === '') continue;
            try {
                $db->run(
                    "INSERT INTO digital_good_brands (name, slug, icon_path, active, sort_order)
                     VALUES (?, ?, ?, 1, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), icon_path = IF(icon_path = '', VALUES(icon_path), icon_path)",
                    $name,
                    $slug,
                    $icon,
                    ($i + 1) * 10
                );
            } catch (Throwable $e) {}
        }
    }
}

if (!function_exists('dg_get_brands')) {
    function dg_get_brands(bool $activeOnly = true): array
    {
        global $db;
        dg_seed_default_brands();
        $where = $activeOnly ? 'WHERE active = 1' : '';
        try {
            return $db->run("SELECT id, name, slug, icon_path, active, sort_order FROM digital_good_brands {$where} ORDER BY sort_order ASC, name ASC") ?: [];
        } catch (Throwable $e) {
            error_log('dg_get_brands failed: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('dg_brand_upload_icon')) {
    function dg_brand_upload_icon(array $file, string $brandName): string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return '';
        if (!empty($file['size']) && (int)$file['size'] > 3 * 1024 * 1024) return '';

        $original = (string)($file['name'] ?? 'icon');
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) return '';

        $mime = strtolower((string)($file['type'] ?? ''));
        $imageMimes = ['image/png','image/jpeg','image/jpg','image/webp','image/gif','image/svg+xml','application/svg+xml'];
        if ($mime !== '' && !in_array($mime, $imageMimes, true)) return '';

        if ($ext === 'svg') {
            $svg = @file_get_contents($file['tmp_name']);
            if ($svg === false) return '';
            if (preg_match('/<\s*script\b|on\w+\s*=|javascript\s*:/i', $svg)) return '';
        } else {
            if (@getimagesize($file['tmp_name']) === false) return '';
        }

        $slug = dg_brand_slugify($brandName) ?: 'brand';
        $dir = rtrim((string)SYS_PATH, '/') . '/public/assets/website/images/digital-goods/brands';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (!is_dir($dir) || !is_writable($dir)) return '';

        $filename = $slug . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        $target = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) return '';
        return '/website/images/digital-goods/brands/' . $filename;
    }
}

// =========================================================
// Simple Support Shift System
// =========================================================
if (!function_exists('lb_support_shift_super_admins')) {
    function lb_support_shift_super_admins(): array
    {
        return [
            'r.machmueller@gmx.de',
            'nimm2oder3@gmx.de',
            'Duck_sauce@live.de',
            'hbilalshah@gmail.com',
            'lovely@lolboost.gg',
            'justsromail@freenet.de',
        ];
    }
}

if (!function_exists('lb_support_shift_super_admin_ids')) {
    function lb_support_shift_super_admin_ids(): array
    {
        return [1];
    }
}

if (!function_exists('lb_support_shift_admins')) {
    function lb_support_shift_admins(): array
    {
        return array_values(array_unique(array_map('strtolower', array_merge(lb_support_shift_super_admins(), [
            'duck_sauce@live.de',
            'nimm2oder3@gmx.de',
            'r.machmueller@gmx.de',
            'hbilalshah@gmail.com',
            'lovely@lolboost.gg',
        ]))));
    }
}

if (!function_exists('lb_support_shift_helpers')) {
    function lb_support_shift_helpers(): array
    {
        return array_values(array_unique(array_merge(lb_support_shift_admins(), [
            'ziad202175@yahoo.com',
            'abdoazzam281@gmail.com',
            'nototakuulol@gmail.com',
        ])));
    }
}

if (!function_exists('lb_support_shift_current_admin_id')) {
    function lb_support_shift_current_admin_id(): int
    {
        return (defined('ADMIN_DATA') && !empty(ADMIN_DATA['id'])) ? (int)ADMIN_DATA['id'] : 0;
    }
}

if (!function_exists('lb_support_shift_current_email')) {
    function lb_support_shift_current_email(): string
    {
        return (defined('ADMIN_DATA') && !empty(ADMIN_DATA['email'])) ? strtolower(trim((string)ADMIN_DATA['email'])) : '';
    }
}


if (!function_exists('lb_support_shift_excluded_admins')) {
    function lb_support_shift_excluded_admins(): array
    {
        return ['primeseohub92@gmail.com'];
    }
}

if (!function_exists('lb_support_shift_is_super_admin')) {
    function lb_support_shift_is_super_admin(): bool
    {
        
        $adminId = lb_support_shift_current_admin_id();
        $data = (defined('ADMIN_DATA') && is_array(ADMIN_DATA)) ? ADMIN_DATA : [];
        $role = strtolower(trim((string)($data['role'] ?? $data['type'] ?? $data['permission'] ?? '')));
        if ($adminId > 0 && in_array($adminId, lb_support_shift_super_admin_ids(), true)) return true;
        if (in_array($role, ['superadmin', 'super_admin', 'owner'], true)) return true;
        return in_array(lb_support_shift_current_email(), lb_support_shift_super_admins(), true);
    }
}

if (!function_exists('lb_support_shift_can_access')) {
    function lb_support_shift_can_access(): bool
    {
        $email = lb_support_shift_current_email();
        if ($email !== '' && function_exists('lb_support_shift_excluded_admins') && in_array($email, lb_support_shift_excluded_admins(), true)) {
            return false;
        }

        // Any logged-in admin may access the Support Shifts module, except explicitly excluded admins.
        // This prevents /admin-area/support-shifts from being blocked when
        // ADMIN_DATA has an id but the email whitelist is incomplete.
        if (lb_support_shift_current_admin_id() > 0) {
            return true;
        }

        if ($email === '') {
            return false;
        }

        return in_array($email, lb_support_shift_helpers(), true);
    }
}



if (!function_exists('lb_support_shift_now')) {
    function lb_support_shift_now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('lb_support_shift_datetime_for_display')) {
    function lb_support_shift_datetime_for_display($value): string
    {
        $value = trim((string)($value ?? ''));
        if ($value === '' || $value === '0000-00-00 00:00:00') return '';
        return date('Y-m-d H:i:s', strtotime($value));
    }
}

if (!function_exists('lb_support_shift_ensure_tables')) {
    function lb_support_shift_ensure_tables(): void
    {
        global $db;

        $db->query("CREATE TABLE IF NOT EXISTS support_shifts (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(120) NOT NULL DEFAULT 'Support Shift',
            shift_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            duration_minutes SMALLINT UNSIGNED NULL,
            template_id INT UNSIGNED NULL,
            assigned_admin_id INT UNSIGNED NULL,
            status ENUM('open','assigned','active','paused','completed','cancelled') NOT NULL DEFAULT 'open',
            started_at DATETIME NULL,
            ended_at DATETIME NULL,
            created_by_admin_id INT UNSIGNED NULL,
            assigned_by_admin_id INT UNSIGNED NULL,
            next_activity_check_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_shift_date (shift_date),
            KEY idx_assigned_admin (assigned_admin_id),
            KEY idx_status (status),
            KEY idx_template (template_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->query("CREATE TABLE IF NOT EXISTS support_shift_checks (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            shift_id INT UNSIGNED NOT NULL,
            admin_id INT UNSIGNED NOT NULL,
            due_at DATETIME NOT NULL,
            expires_at DATETIME NOT NULL,
            confirmed_at DATETIME NULL,
            status ENUM('pending','confirmed','missed') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_shift_admin (shift_id, admin_id),
            KEY idx_due_at (due_at),
            KEY idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->query("CREATE TABLE IF NOT EXISTS support_shift_login_choices (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            admin_id INT UNSIGNED NOT NULL,
            shift_id INT UNSIGNED NULL,
            choice ENUM('working','not_working') NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_admin_created (admin_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // NEW: weekly schedule templates. One row per (admin, weekday, start_time).
        // duration_minutes handles overnight automatically (start + duration crosses 24:00).
        $db->query("CREATE TABLE IF NOT EXISTS support_shift_templates (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            admin_id INT UNSIGNED NOT NULL,
            weekday TINYINT UNSIGNED NOT NULL,
            start_time TIME NOT NULL,
            duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 480,
            title VARCHAR(120) NOT NULL DEFAULT 'Support Shift',
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_by_admin_id INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_weekday_active (weekday, active),
            KEY idx_admin (admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        try { $db->query("ALTER TABLE support_shifts ADD COLUMN created_by_admin_id INT UNSIGNED NULL"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts ADD COLUMN assigned_by_admin_id INT UNSIGNED NULL"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shift_checks ADD COLUMN expires_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts ADD COLUMN next_activity_check_at DATETIME NULL"); } catch (Throwable $e) {}

        // NEW columns for flexible schedule
        try { $db->query("ALTER TABLE support_shifts ADD COLUMN duration_minutes SMALLINT UNSIGNED NULL AFTER end_time"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts ADD COLUMN template_id INT UNSIGNED NULL AFTER duration_minutes"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts ADD KEY idx_template (template_id)"); } catch (Throwable $e) {}

        // Backfill duration_minutes for old rows (idempotent)
        try {
            $db->query("UPDATE support_shifts
                SET duration_minutes = CASE
                    WHEN CAST(end_time AS TIME) <= CAST(start_time AS TIME)
                        THEN TIMESTAMPDIFF(MINUTE, TIMESTAMP(shift_date, start_time), DATE_ADD(TIMESTAMP(shift_date, end_time), INTERVAL 1 DAY))
                    ELSE TIMESTAMPDIFF(MINUTE, TIMESTAMP(shift_date, start_time), TIMESTAMP(shift_date, end_time))
                END
                WHERE duration_minutes IS NULL");
        } catch (Throwable $e) {}

        try { $db->query("ALTER TABLE support_shifts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shift_checks CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shift_login_choices CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shift_templates CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts MODIFY shift_date DATE NOT NULL"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts MODIFY start_time TIME NOT NULL"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts MODIFY end_time TIME NOT NULL"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shifts MODIFY status ENUM('open','assigned','active','paused','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open'"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shift_checks MODIFY status ENUM('pending','confirmed','missed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'"); } catch (Throwable $e) {}
        try { $db->query("ALTER TABLE support_shift_login_choices MODIFY choice ENUM('working','not_working') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL"); } catch (Throwable $e) {}

    }
}


if (!function_exists('lb_support_shift_seed_week_ahead')) {
    function lb_support_shift_seed_week_ahead(string $from = '', int $days = 7): void
    {
        lb_support_shift_ensure_tables();
        $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : date('Y-m-d');
        $days = max(1, min(31, $days));
        if (function_exists('lb_support_shift_apply_templates_for_range')) {
            lb_support_shift_apply_templates_for_range($from, $days, false);
        }
    }
}
if (!function_exists('lb_support_shift_duration_minutes')) {
    /**
     * Real duration of a shift in minutes, handles overnight correctly.
     * Prefers duration_minutes column when set; falls back to time math.
     */
    function lb_support_shift_duration_minutes(array $shift): int
    {
        $stored = (int)($shift['duration_minutes'] ?? 0);
        if ($stored > 0) return $stored;

        $date = (string)($shift['shift_date'] ?? date('Y-m-d'));
        $start = strtotime($date . ' ' . (string)($shift['start_time'] ?? '00:00:00'));
        $end = strtotime($date . ' ' . (string)($shift['end_time'] ?? '00:00:00'));
        if ($end <= $start) $end = strtotime('+1 day', $end);
        return max(0, (int)round(($end - $start) / 60));
    }
}

if (!function_exists('lb_support_shift_apply_templates_for_range')) {
    /**
     * Creates the three standard open support shifts for every selected day:
     * 06:00-14:00, 14:00-22:00 and 22:00-06:00.
     * Existing non-cancelled shifts with the same date and start time are not duplicated.
     */
    function lb_support_shift_apply_templates_for_range(string $from, int $days = 7, bool $force = false): int
    {
        global $db;
        lb_support_shift_ensure_tables();

        $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : date('Y-m-d');
        $days = max(1, min(31, $days));

        $standardShifts = [
            ['start' => '06:00:00', 'end' => '14:00:00', 'duration' => 480],
            ['start' => '14:00:00', 'end' => '22:00:00', 'duration' => 480],
            ['start' => '22:00:00', 'end' => '06:00:00', 'duration' => 480],
        ];

        $inserted = 0;
        $cursor = new DateTime($from, new DateTimeZone('Europe/Berlin'));

        for ($i = 0; $i < $days; $i++) {
            $date = $cursor->format('Y-m-d');

            foreach ($standardShifts as $standardShift) {
                $startTime = $standardShift['start'];
                $endTime = $standardShift['end'];
                $duration = (int)$standardShift['duration'];

                $existingRows = $db->run(
                    "SELECT id, status
                     FROM support_shifts
                     WHERE shift_date = ?
                       AND start_time = ?
                       AND status <> 'cancelled'
                     LIMIT 1",
                    $date,
                    $startTime
                ) ?: [];

                if (!empty($existingRows)) {
                    continue;
                }

                $db->run(
                    "INSERT INTO support_shifts
                        (title, shift_date, start_time, end_time, duration_minutes,
                         assigned_admin_id, status, template_id, created_by_admin_id, assigned_by_admin_id)
                     VALUES ('Support Shift', ?, ?, ?, ?, NULL, 'open', NULL, NULL, NULL)",
                    $date,
                    $startTime,
                    $endTime,
                    $duration
                );

                $inserted++;
            }

            $cursor->modify('+1 day');
        }

        return $inserted;
    }
}

if (!function_exists('lb_support_shift_templates_list')) {
    /**
     * Returns all templates with joined admin data, ordered by weekday then start_time.
     */
    function lb_support_shift_templates_list(): array
    {
        global $db;
        lb_support_shift_ensure_tables();
        return $db->run(
            "SELECT t.*, a.username, a.email, a.icon
             FROM support_shift_templates t
             LEFT JOIN admins a ON a.id = t.admin_id
             ORDER BY t.weekday ASC, t.start_time ASC, t.id ASC"
        ) ?: [];
    }
}

if (!function_exists('lb_support_shift_coverage_segments')) {
    /**
     * For a given calendar date, compute the 24h coverage as a list of segments.
     * Each segment: ['from_min' => int (0-1440), 'to_min' => int, 'admins' => [...], 'is_gap' => bool, 'is_overlap' => bool].
     *
     * Considers overnight shifts from the previous day. The caller should pass ALL
     * shifts in the week (or at least date-1 .. date) so overnight is handled.
     */
    function lb_support_shift_coverage_segments(array $allShifts, string $forDate): array
    {
        $tz = new DateTimeZone('Europe/Berlin');
        try {
            $dayStart = new DateTime($forDate . ' 00:00:00', $tz);
        } catch (Throwable $e) {
            return [['from_min' => 0, 'to_min' => 1440, 'admins' => [], 'is_gap' => true, 'is_overlap' => false]];
        }
        $dayEnd = (clone $dayStart)->modify('+1 day');
        $dayStartTs = $dayStart->getTimestamp();
        $dayEndTs   = $dayEnd->getTimestamp();

        $intervals = [];
        foreach ($allShifts as $s) {
            $status = (string)($s['status'] ?? 'open');
            if ($status === 'cancelled') continue;

            $sdate = (string)($s['shift_date'] ?? '');
            $start = strtotime($sdate . ' ' . (string)($s['start_time'] ?? '00:00:00'));
            $end   = strtotime($sdate . ' ' . (string)($s['end_time']   ?? '00:00:00'));
            if ($end <= $start) $end = strtotime('+1 day', $end);

            $clipStart = max($start, $dayStartTs);
            $clipEnd   = min($end, $dayEndTs);
            if ($clipEnd <= $clipStart) continue;

            $adminIds = [];
            if (!empty($s['participants']) && is_array($s['participants'])) {
                foreach ($s['participants'] as $p) {
                    $aid = (int)($p['admin_id'] ?? 0);
                    if ($aid <= 0) continue;
                    $adminIds[$aid] = (string)($p['username'] ?? 'Admin');
                }
            }
            if (empty($adminIds) && !empty($s['assigned_admin_id'])) {
                $adminIds[(int)$s['assigned_admin_id']] = (string)($s['assigned_username'] ?? 'Admin');
            }

            $intervals[] = [
                'shift_id'   => (int)($s['id'] ?? 0),
                'status'     => $status,
                'admins'     => $adminIds, // [id => name]
                'is_open'    => empty($adminIds),
                'from_min'   => (int)round(($clipStart - $dayStartTs) / 60),
                'to_min'     => (int)round(($clipEnd - $dayStartTs) / 60),
            ];
        }

        // Sweep boundaries
        $boundaries = [0, 1440];
        foreach ($intervals as $iv) {
            $boundaries[] = $iv['from_min'];
            $boundaries[] = $iv['to_min'];
        }
        $boundaries = array_values(array_unique(array_filter($boundaries, static fn($v) => $v >= 0 && $v <= 1440)));
        sort($boundaries);

        $segments = [];
        for ($i = 0, $n = count($boundaries) - 1; $i < $n; $i++) {
            $segFrom = (int)$boundaries[$i];
            $segTo   = (int)$boundaries[$i + 1];
            if ($segTo <= $segFrom) continue;

            $mergedAdmins = [];
            $anyOpenSlot  = false;
            $shiftIds     = [];
            foreach ($intervals as $iv) {
                if ($iv['from_min'] < $segTo && $iv['to_min'] > $segFrom) {
                    if ($iv['is_open']) {
                        $anyOpenSlot = true;
                    } else {
                        foreach ($iv['admins'] as $aid => $name) {
                            $mergedAdmins[$aid] = $name;
                        }
                    }
                    $shiftIds[] = $iv['shift_id'];
                }
            }

            $segments[] = [
                'from_min'   => $segFrom,
                'to_min'     => $segTo,
                'admins'     => $mergedAdmins, // [id => name]
                'admin_ids'  => array_values(array_map('intval', array_keys($mergedAdmins))),
                'shift_ids'  => array_values(array_unique(array_map('intval', $shiftIds))),
                'is_open'    => $anyOpenSlot && empty($mergedAdmins),
                'is_gap'     => empty($mergedAdmins) && !$anyOpenSlot,
                'is_overlap' => count($mergedAdmins) >= 2,
            ];
        }

        // Coalesce adjacent equivalent segments (same admin set)
        $collapsed = [];
        foreach ($segments as $seg) {
            $key = ($seg['is_gap'] ? 'G' : ($seg['is_open'] ? 'O' : ('A:' . implode(',', $seg['admin_ids']))));
            if (!empty($collapsed) && end($collapsed)['_key'] === $key) {
                $last = array_pop($collapsed);
                $last['to_min'] = $seg['to_min'];
                $collapsed[] = $last;
            } else {
                $seg['_key'] = $key;
                $collapsed[] = $seg;
            }
        }
        foreach ($collapsed as &$c) unset($c['_key']);

        return $collapsed;
    }
}

if (!function_exists('lb_support_shift_coverage_totals')) {
    /**
     * Sum of covered / open / gap minutes for a segments array.
     */
    function lb_support_shift_coverage_totals(array $segments): array
    {
        $covered = 0; $open = 0; $gap = 0; $overlap = 0;
        foreach ($segments as $s) {
            $len = max(0, (int)$s['to_min'] - (int)$s['from_min']);
            if (!empty($s['is_gap']))         $gap     += $len;
            elseif (!empty($s['is_open']))    $open    += $len;
            else                              $covered += $len;
            if (!empty($s['is_overlap']))     $overlap += $len;
        }
        return [
            'covered_min' => $covered,
            'open_min'    => $open,
            'gap_min'     => $gap,
            'overlap_min' => $overlap,
            'total_min'   => 1440,
        ];
    }
}



if (!function_exists('lb_support_shift_add_display_times')) {
    function lb_support_shift_add_display_times(array $shift): array
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $date = (string)($shift['shift_date'] ?? date('Y-m-d'));
        $startTime = (string)($shift['start_time'] ?? '00:00:00');
        $endTime = (string)($shift['end_time'] ?? '00:00:00');
        $start = new DateTime($date . ' ' . $startTime, $tz);
        $end = new DateTime($date . ' ' . $endTime, $tz);
        if ($end <= $start) {
            $end->modify('+1 day');
        }
        $shift['start_iso'] = $start->format(DateTime::ATOM);
        $shift['end_iso'] = $end->format(DateTime::ATOM);
        return $shift;
    }
}

if (!function_exists('lb_support_shift_user_options')) {
    function lb_support_shift_user_options(): array
    {
        global $db;
        $rows = $db->run("SELECT id, username, email, icon FROM admins ORDER BY username ASC") ?: [];
        return $rows;
    }
}


if (!function_exists('lb_support_shift_auto_complete_expired')) {
    function lb_support_shift_auto_complete_expired(): void
    {
        global $db;
        lb_support_shift_ensure_tables();
        $now = lb_support_shift_now();
        $db->run("UPDATE support_shifts
            SET status = 'completed', ended_at = CASE
                WHEN CAST(end_time AS TIME) <= CAST(start_time AS TIME) THEN DATE_ADD(TIMESTAMP(shift_date, end_time), INTERVAL 1 DAY)
                ELSE TIMESTAMP(shift_date, end_time)
            END,
                next_activity_check_at = NULL
            WHERE status IN ('active','paused')
              AND (CASE WHEN CAST(end_time AS TIME) <= CAST(start_time AS TIME) THEN DATE_ADD(TIMESTAMP(shift_date, end_time), INTERVAL 1 DAY) ELSE TIMESTAMP(shift_date, end_time) END) <= ?", $now);
        $db->run("UPDATE support_shift_checks SET status = 'missed' WHERE status = 'pending' AND expires_at < ?", $now);
    }
}

if (!function_exists('lb_support_shift_fetch_data')) {
    function lb_support_shift_fetch_data(string $from = '', string $to = ''): array
    {
        global $db;
        lb_support_shift_ensure_tables();
        if (function_exists('lb_support_shift_auto_complete_expired')) { lb_support_shift_auto_complete_expired(); }

        $adminId = lb_support_shift_current_admin_id();
        $isSuper = lb_support_shift_is_super_admin();

        if ($from === '') $from = date('Y-m-d');
        if ($to === '')   $to   = date('Y-m-d', strtotime('+6 days'));

        $seedDays = max(1, min(31, (int)((strtotime($to) - strtotime($from)) / 86400) + 1));
        lb_support_shift_seed_week_ahead($from, $seedDays);

        // Every supporter must see the complete schedule, including shifts and
        // hourly blocks already assigned to other supporters. Permissions are
        // still enforced by the individual claim, join, release and admin actions.
        $where = "s.shift_date BETWEEN ? AND ?";
        $args  = [$from, $to];

        $sql = "SELECT s.*, a.username AS assigned_username, a.email AS assigned_email, a.icon AS assigned_icon,
                    creator.username AS creator_username,
                    COALESCE(ch.confirmed_checks, 0) AS confirmed_checks,
                    COALESCE(ch.missed_checks, 0)   AS missed_checks,
                    COALESCE(ch.pending_checks, 0)  AS pending_checks
                FROM support_shifts s
                LEFT JOIN admins a       ON a.id = s.assigned_admin_id
                LEFT JOIN admins creator ON creator.id = s.created_by_admin_id
                LEFT JOIN (
                    SELECT shift_id,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_checks,
                        SUM(CASE WHEN status = 'missed'    THEN 1 ELSE 0 END) AS missed_checks,
                        SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending_checks
                    FROM support_shift_checks
                    GROUP BY shift_id
                ) ch ON ch.shift_id = s.id
                WHERE {$where}
                ORDER BY s.shift_date ASC, s.start_time ASC";
        $shifts = array_map('lb_support_shift_add_display_times', $db->run($sql, ...$args) ?: []);

        $active = [];
        if ($adminId > 0) {
            $activeRows = $db->run("SELECT s.*, a.username AS assigned_username, a.email AS assigned_email, a.icon AS assigned_icon
                FROM support_shifts s
                LEFT JOIN admins a ON a.id = s.assigned_admin_id
                WHERE s.assigned_admin_id = ? AND s.status IN ('active','paused')
                ORDER BY s.started_at DESC LIMIT 1", $adminId) ?: [];
            $active = !empty($activeRows[0]) ? lb_support_shift_add_display_times($activeRows[0]) : [];
        }

        $stats = [];
        if ($isSuper) {
            $stats = $db->run("SELECT a.id, a.username, a.email, a.icon,
                    COUNT(s.id) AS total_shifts,
                    SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) AS completed_shifts,
                    SUM(CASE WHEN s.status = 'active'    THEN 1 ELSE 0 END) AS active_shifts,
                    SUM(CASE WHEN s.status IN ('open','assigned') AND TIMESTAMP(s.shift_date, s.start_time) < CAST(? AS DATETIME) THEN 1 ELSE 0 END) AS not_started,
                    COALESCE(SUM(CASE WHEN s.started_at IS NOT NULL AND s.ended_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, s.started_at, s.ended_at) ELSE 0 END), 0) AS worked_minutes,
                    COALESCE(SUM(ch.confirmed_checks), 0) AS confirmed_checks,
                    COALESCE(SUM(ch.missed_checks), 0)    AS missed_checks
                FROM admins a
                INNER JOIN support_shifts s ON s.assigned_admin_id = a.id
                LEFT JOIN (
                    SELECT shift_id,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_checks,
                        SUM(CASE WHEN status = 'missed'    THEN 1 ELSE 0 END) AS missed_checks
                    FROM support_shift_checks
                    GROUP BY shift_id
                ) ch ON ch.shift_id = s.id
                WHERE s.shift_date BETWEEN ? AND ?
                GROUP BY a.id, a.username, a.email, a.icon
                ORDER BY completed_shifts DESC, total_shifts DESC, a.username ASC", lb_support_shift_now(), $from, $to) ?: [];
        }

        $templates = function_exists('lb_support_shift_templates_list') ? lb_support_shift_templates_list() : [];

        return [
            'from'           => $from,
            'to'             => $to,
            'shifts'         => $shifts,
            'active_shift'   => $active,
            'stats'          => $stats,
            'admins'         => lb_support_shift_user_options(),
            'templates'      => $templates,
            'is_super_admin' => $isSuper,
        ];
    }
}

if (!function_exists('lb_support_shift_datetime_window')) {
    function lb_support_shift_datetime_window(array $shift): array
    {
        $date = (string)($shift['shift_date'] ?? date('Y-m-d'));
        $startTime = substr((string)($shift['start_time'] ?? '00:00:00'), 0, 8);
        $start = strtotime($date . ' ' . $startTime);

        // The planner uses three fixed eight-hour standard windows. Personal
        // supporter hours belong to support_shift_participants and must never
        // shorten the standard window itself.
        $startHour = (int)date('G', $start);
        if (in_array($startHour, [6, 14, 22], true)) {
            $end = $start + (8 * 3600);
        } else {
            $end = strtotime($date . ' ' . (string)($shift['end_time'] ?? '00:00:00'));
            if ($end <= $start) {
                $end = strtotime('+1 day', $end);
            }
        }

        return [$start, $end];
    }
}


/**
 * Monthly Top Booster bonus system
 * Can be run by cron on the 1st of each month. As fallback it also runs lazily:
 * the first order completion in a new month creates awards for the current month
 * from the previous month's qualified leaderboard top 3.
 * Amounts are stored in cents in booster_payments as type monthly_top_booster_bonus.
 */
if (!function_exists('lb_monthly_bonus_config')) {
    function lb_monthly_bonus_config(): array
    {
        return [
            1 => ['percent' => 5, 'title' => 'Top Booster #1'],
            2 => ['percent' => 5, 'title' => 'Top Booster #2'],
            3 => ['percent' => 5, 'title' => 'Top Booster #3'],
        ];
    }
}

if (!function_exists('lb_monthly_bonus_ensure_tables')) {
    function lb_monthly_bonus_ensure_tables(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;
        global $db;
        try {
            $db->run("CREATE TABLE IF NOT EXISTS booster_monthly_bonus_awards (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                award_month CHAR(7) NOT NULL,
                source_month CHAR(7) NOT NULL,
                booster_id INT UNSIGNED NOT NULL,
                position TINYINT UNSIGNED NOT NULL,
                bonus_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                title VARCHAR(64) NOT NULL DEFAULT 'Top Booster',
                active_from DATETIME NOT NULL,
                active_until DATETIME NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_award_month_booster (award_month, booster_id),
                KEY idx_active_booster (booster_id, active_from, active_until),
                KEY idx_award_month_position (award_month, position)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        } catch (Throwable $e) {}
    }
}

if (!function_exists('lb_monthly_bonus_calculate_top3_from_view')) {
    function lb_monthly_bonus_calculate_top3_from_view(string $sourceMonth): array
    {
        global $db;
        $rows = $db->run(
            "SELECT booster_id, username, score, winrate, games, qualified
             FROM booster_monthly_leaderboard
             WHERE leaderboard_month = ?
               AND qualified = 1
             ORDER BY score DESC, winrate DESC, games DESC
             LIMIT 3",
            $sourceMonth
        ) ?: [];

        $out = [];
        foreach ($rows as $i => $row) {
            $cfg = lb_monthly_bonus_config()[$i + 1] ?? null;
            if (!$cfg) continue;
            $out[] = [
                'booster_id' => (int)($row['booster_id'] ?? 0),
                'position' => $i + 1,
                'percent' => (float)$cfg['percent'],
                'title' => (string)$cfg['title'],
            ];
        }
        return array_values(array_filter($out, fn($r) => $r['booster_id'] > 0));
    }
}

if (!function_exists('lb_monthly_bonus_refresh_awards')) {
    function lb_monthly_bonus_refresh_awards(?string $awardMonth = null, bool $force = false): array
    {
        global $db;
        lb_monthly_bonus_ensure_tables();

        $awardMonth = $awardMonth && preg_match('/^\\d{4}-\\d{2}$/', $awardMonth) ? $awardMonth : date('Y-m');
        $awardStart = $awardMonth . '-01 00:00:00';
        $awardEnd = date('Y-m-d H:i:s', strtotime($awardStart . ' +1 month'));
        $sourceMonth = date('Y-m', strtotime($awardStart . ' -1 month'));

        $existing = (int)($db->cell('SELECT COUNT(*) FROM booster_monthly_bonus_awards WHERE award_month = ?', $awardMonth) ?: 0);
        if ($existing > 0 && !$force) {
            return $db->run('SELECT * FROM booster_monthly_bonus_awards WHERE award_month = ? ORDER BY position ASC', $awardMonth) ?: [];
        }

        if ($force && $existing > 0) {
            $db->run('DELETE FROM booster_monthly_bonus_awards WHERE award_month = ?', $awardMonth);
        }

        try {
            $topRows = lb_monthly_bonus_calculate_top3_from_view($sourceMonth);
        } catch (Throwable $e) {
            return [];
        }

        foreach ($topRows as $row) {
            db_add_row('booster_monthly_bonus_awards', [
                'award_month' => $awardMonth,
                'source_month' => $sourceMonth,
                'booster_id' => (int)$row['booster_id'],
                'position' => (int)$row['position'],
                'bonus_percent' => number_format((float)$row['percent'], 2, '.', ''),
                'title' => (string)$row['title'],
                'active_from' => $awardStart,
                'active_until' => $awardEnd,
            ]);
        }

        return $db->run('SELECT * FROM booster_monthly_bonus_awards WHERE award_month = ? ORDER BY position ASC', $awardMonth) ?: [];
    }
}

if (!function_exists('lb_monthly_bonus_get_active_award')) {
    function lb_monthly_bonus_get_active_award(int $boosterId, ?string $at = null): ?array
    {
        if ($boosterId <= 0) return null;
        global $db;

        try {
            lb_monthly_bonus_ensure_tables();

            $at = $at ?: date('Y-m-d H:i:s');
            $row = db_get_row('booster_monthly_bonus_awards', [
                'booster_id' => $boosterId,
                'active_from' => ['lte' => $at],
                'active_until' => ['gt' => $at],
            ], 1);

            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('lb_monthly_bonus_special_rank')) {
    function lb_monthly_bonus_special_rank(int $boosterId, ?string $at = null): ?string
    {
        $award = lb_monthly_bonus_get_active_award($boosterId, $at);
        return $award ? (string)$award['title'] : null;
    }
}

if (!function_exists('lb_monthly_bonus_add_payment')) {
    function lb_monthly_bonus_add_payment(int $boosterId, int $orderId, int $bonusBaseAmount, string $sender, string $senderType, int $senderId, ?string $createdAt = null): int
    {
        if ($boosterId <= 0 || $orderId <= 0 || $bonusBaseAmount <= 0) return 0;
        global $db;
        $createdAt = $createdAt ?: date('Y-m-d H:i:s');
        $award = lb_monthly_bonus_get_active_award($boosterId, $createdAt);
        if (!$award) return 0;

        $existingRows = $db->run(
            "SELECT COALESCE(SUM(amount), 0) AS paid
             FROM booster_payments
             WHERE type = 'monthly_top_booster_bonus' AND note = ? AND booster_id = ?",
            (string)$orderId,
            $boosterId
        );
        $alreadyPaid = (is_array($existingRows) && isset($existingRows[0]['paid'])) ? (int)$existingRows[0]['paid'] : 0;
        if ($alreadyPaid !== 0) return 0;

        $percent = (float)($award['bonus_percent'] ?? 0);
        $bonusAmount = (int)round($bonusBaseAmount * $percent / 100);
        if ($bonusAmount <= 0) return 0;

        $booster = db_get_row('boosters', ['id' => $boosterId]);
        if ($booster == false) return 0;

        $oldBalance = (int)($booster['balance'] ?? 0);
        $newBalance = $oldBalance + $bonusAmount;
        db_update_row('boosters', ['id' => $boosterId], ['balance' => $newBalance]);

        db_add_row('booster_payments', [
            'booster_id' => $boosterId,
            'type' => 'monthly_top_booster_bonus',
            'note' => (string)$orderId,
            'amount' => $bonusAmount,
            'currency' => 'EUR',
            'sender' => $sender,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'balance_update' => $oldBalance . '|' . $newBalance,
            'created_at' => $createdAt,
        ]);

        if (function_exists('log_admin_action')) {
            log_admin_action($senderId, "Order #{$orderId}: monthly top booster bonus for booster #{$boosterId} +{$percent}% (€" . number_format($bonusAmount / 100, 2, '.', '') . ")");
        }

        return $bonusAmount;
    }
}


/* ===== MERGED FROM LIVE lolboost.gg core/functions.php =====
   Missing helper functions appended with guards to preserve newlolboost Digital Goods/dashboard helpers. */

if (!function_exists('analytics_current_date_condition')) {
function analytics_current_date_condition($start_date, $end_date, $field_sql)
{
    if ($start_date && $end_date) {
        return "AND $field_sql BETWEEN '$start_date' AND '$end_date'";
    }

    $year = date('Y');
    return "AND YEAR($field_sql) = $year";
}
}

if (!function_exists('analytics_money_metric')) {
function analytics_money_metric($current_total, $prev_total)
{
    $current_total = (float)$current_total;
    $prev_total = (float)$prev_total;

    $percentage_change = 0;
    if ($prev_total != 0) {
        $percentage_change = (($current_total - $prev_total) / $prev_total) * 100;
    } elseif ($current_total != 0) {
        $percentage_change = 100;
    }

    return [
        'current' => util_format_price_input($current_total),
        'previous' => util_format_price_input($prev_total),
        'change' => round($percentage_change, 2),
        'is_up' => $percentage_change >= 0,
        'current_raw' => $current_total,
        'previous_raw' => $prev_total,
    ];
}
}

if (!function_exists('analytics_previous_date_condition')) {
function analytics_previous_date_condition($start_date, $end_date, $field_sql)
{
    if ($start_date && $end_date) {
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $interval = $start_date_obj->diff($end_date_obj);

        $prev_start_date_obj = clone $start_date_obj;
        $prev_start_date_obj->sub($interval);
        $prev_end_date_obj = clone $start_date_obj;
        $prev_end_date_obj->sub(new DateInterval('P1D'));

        $prev_start_date = $prev_start_date_obj->format('Y-m-d 00:00:00');
        $prev_end_date = $prev_end_date_obj->format('Y-m-d 23:59:59');

        return "AND $field_sql BETWEEN '$prev_start_date' AND '$prev_end_date'";
    }

    $prev_year = date('Y') - 1;
    return "AND YEAR($field_sql) = $prev_year";
}
}

if (!function_exists('get_account_sales_revenue')) {
function get_account_sales_revenue($start_date = null, $end_date = null)
{
    $selling_accounts = get_selling_accounts_fee_revenue($start_date, $end_date);
    $smurf_accounts = get_smurf_accounts_revenue($start_date, $end_date);

    $current_total = (float)($selling_accounts['current_raw'] ?? 0) + (float)($smurf_accounts['current_raw'] ?? 0);
    $previous_total = (float)($selling_accounts['previous_raw'] ?? 0) + (float)($smurf_accounts['previous_raw'] ?? 0);

    return analytics_money_metric($current_total, $previous_total);
}
}

if (!function_exists('get_other_revenue_excluding_account_sales')) {
function get_other_revenue_excluding_account_sales($start_date = null, $end_date = null)
{
    $date_condition = '';
    $prev_date_condition = '';

    if ($start_date && $end_date) {
        $date_condition = "AND created_at BETWEEN '$start_date' AND '$end_date'";

        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $interval = $start_date_obj->diff($end_date_obj);

        $prev_start_date_obj = clone $start_date_obj;
        $prev_start_date_obj->sub($interval);
        $prev_end_date_obj = clone $start_date_obj;
        $prev_end_date_obj->sub(new DateInterval('P1D'));

        $prev_start_date = $prev_start_date_obj->format('Y-m-d');
        $prev_end_date = $prev_end_date_obj->format('Y-m-d');

        $prev_date_condition = "AND created_at BETWEEN '$prev_start_date' AND '$prev_end_date'";
    } else {
        $year = date('Y');
        $date_condition = "AND YEAR(created_at) = $year";
        $prev_year = $year - 1;
        $prev_date_condition = "AND YEAR(created_at) = $prev_year";
    }

    $type_condition = "AND order_type NOT IN ('order', 'account', 'lol_account')";

    $eur_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'EUR'
        AND status = 'succeeded'
        $type_condition
        $date_condition
        AND created_at IS NOT NULL");

    $usd_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'USD'
        AND status = 'succeeded'
        $type_condition
        $date_condition
        AND created_at IS NOT NULL");

    $current_total = ($eur_revenue[0]['sum'] ?? 0) + (($usd_revenue[0]['sum'] ?? 0) / get_exchange_rate());

    $prev_eur_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'EUR'
        AND status = 'succeeded'
        $type_condition
        $prev_date_condition
        AND created_at IS NOT NULL");

    $prev_usd_revenue = db_run_query("SELECT SUM(amount) AS sum
        FROM transactions
        WHERE currency = 'USD'
        AND status = 'succeeded'
        $type_condition
        $prev_date_condition
        AND created_at IS NOT NULL");

    $prev_total = ($prev_eur_revenue[0]['sum'] ?? 0) + (($prev_usd_revenue[0]['sum'] ?? 0) / get_exchange_rate());

    return analytics_money_metric($current_total, $prev_total);
}
}

if (!function_exists('get_selling_accounts_fee_revenue')) {
function get_selling_accounts_fee_revenue($start_date = null, $end_date = null)
{
    $date_field = "COALESCE(sa.sold_at, sa.created_at)";
    $date_condition = analytics_current_date_condition($start_date, $end_date, $date_field);
    $prev_date_condition = analytics_previous_date_condition($start_date, $end_date, $date_field);

    $sql = function ($condition) {
        return "
            SELECT SUM(sa.price * (COALESCE(NULLIF(s.fee_percent, ''), 15) / 100)) AS sum
            FROM selling_accounts sa
            LEFT JOIN sellers s ON s.id = sa.seller_id
            WHERE sa.sold = 1
              AND sa.client_id IS NOT NULL
              AND sa.price IS NOT NULL
              $condition
        ";
    };

    $current = db_run_query($sql($date_condition));
    $previous = db_run_query($sql($prev_date_condition));

    return analytics_money_metric($current[0]['sum'] ?? 0, $previous[0]['sum'] ?? 0);
}
}

if (!function_exists('get_smurf_accounts_revenue')) {
function get_smurf_accounts_revenue($start_date = null, $end_date = null)
{
    $date_field = "COALESCE(a.sold_at, a.created_at)";
    $date_condition = analytics_current_date_condition($start_date, $end_date, $date_field);
    $prev_date_condition = analytics_previous_date_condition($start_date, $end_date, $date_field);

    $sql = function ($condition) {
        return "
            SELECT SUM(ap.price * CASE
                    WHEN a.admin_id = 51 THEN 0.30
                    WHEN a.admin_id = 2 THEN 1.00
                    ELSE 0
                END) AS sum
            FROM accounts a
            LEFT JOIN account_packages ap ON ap.id = a.package_id
            WHERE a.status = 1
              AND a.client_id IS NOT NULL
              AND a.admin_id IN (2, 51)
              AND ap.price IS NOT NULL
              $condition
        ";
    };

    $current = db_run_query($sql($date_condition));
    $previous = db_run_query($sql($prev_date_condition));

    return analytics_money_metric($current[0]['sum'] ?? 0, $previous[0]['sum'] ?? 0);
}
}

if (!function_exists('lb_booster_device_family')) {
function lb_booster_device_family($deviceInfo): string
{
    if (is_string($deviceInfo)) {
        $decoded = json_decode($deviceInfo, true);
        $deviceInfo = is_array($decoded) ? $decoded : [];
    }

    if (!is_array($deviceInfo)) {
        $deviceInfo = [];
    }

    $device = strtolower(trim((string)($deviceInfo['device'] ?? '')));
    $osValue = $deviceInfo['os'] ?? '';
    if (is_array($osValue)) {
        $osValue = $osValue['name'] ?? '';
    }
    $os = strtolower(trim((string)$osValue));
    $ua = strtolower(trim((string)($deviceInfo['ua'] ?? '')));

    if (in_array($device, ['smartphone', 'feature phone', 'phablet', 'mobile'], true)) {
        return 'mobile';
    }

    if ($device === 'tablet') {
        return 'tablet';
    }

    if (
        strpos($os, 'android') !== false ||
        strpos($os, 'ios') !== false ||
        strpos($os, 'iphone') !== false ||
        strpos($ua, 'iphone') !== false ||
        (strpos($ua, 'android') !== false && strpos($ua, 'mobile') !== false)
    ) {
        return 'mobile';
    }

    if (strpos($os, 'ipad') !== false || strpos($ua, 'ipad') !== false) {
        return 'tablet';
    }

    if (
        strpos($os, 'windows') !== false ||
        strpos($os, 'mac') !== false ||
        strpos($os, 'linux') !== false ||
        strpos($ua, 'windows') !== false ||
        strpos($ua, 'macintosh') !== false ||
        strpos($ua, 'x11') !== false
    ) {
        return 'desktop';
    }

    return 'unknown';
}
}

if (!function_exists('lb_booster_device_type_label')) {
function lb_booster_device_type_label($deviceInfo)
{
    if (is_string($deviceInfo)) {
        $decoded = json_decode($deviceInfo, true);
        $deviceInfo = is_array($decoded) ? $decoded : [];
    }

    if (!is_array($deviceInfo)) {
        $deviceInfo = [];
    }

    $device = strtolower(trim((string)($deviceInfo['device'] ?? '')));
    $osValue = $deviceInfo['os'] ?? '';
    if (is_array($osValue)) {
        $osValue = $osValue['name'] ?? '';
    }
    $os = strtolower(trim((string)$osValue));

    if (in_array($device, ['smartphone', 'feature phone', 'phablet'], true)) {
        return 'Mobile';
    }

    if ($device === 'tablet') {
        return 'Tablet';
    }

    if ($os !== '') {
        if (strpos($os, 'windows') !== false) return 'Windows';
        if (strpos($os, 'android') !== false) return 'Android Mobile';
        if (strpos($os, 'ios') !== false || strpos($os, 'iphone') !== false || strpos($os, 'ipad') !== false) return 'iOS Mobile';
        if (strpos($os, 'mac') !== false) return 'macOS';
        if (strpos($os, 'linux') !== false) return 'Linux';
    }

    return $device !== '' ? ucfirst($device) : 'Unknown';
}
}

if (!function_exists('lb_booster_location_label')) {
function lb_booster_location_label($row)
{
    $parts = [];
    foreach (['city', 'region', 'country'] as $key) {
        $value = trim((string)($row[$key] ?? ''));
        if ($value !== '' && !in_array($value, $parts, true)) {
            $parts[] = $value;
        }
    }

    return !empty($parts) ? implode(', ', $parts) : 'Unknown location';
}
}

if (!function_exists('lb_booster_security_discord_send')) {
function lb_booster_security_discord_send($payload)
{
    $webhookUrl = lb_booster_security_webhook_url();
    if ($webhookUrl === '') {
        return false;
    }

    $ch = curl_init($webhookUrl);
    if (!$ch) {
        return false;
    }

    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 5,
    ]);

    curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}
}

if (!function_exists('lb_discord_channel_webhook')) {
/**
 * Central place for the plain-channel Discord webhooks.
 *
 * Forum threads go inactive after two weeks and every new team member has to be
 * invited into each thread individually, so every notification is being moved to
 * its own normal channel. Paste the channel webhook URL next to its key below.
 *
 * A key that is still empty falls back to the old forum thread, so this can be
 * migrated one channel at a time without any downtime.
 *
 * @param string $group sold|seller|booster|new_order|apply
 * @param string $key   the event/game key inside that group
 */
function lb_discord_channel_webhook(string $group, string $key): string
{
    static $channels = [
        // ── SOLD NOTIFICATION ────────────────────────────────────────────────
        'sold' => [
            'account'      => '',
            'topup'        => '',
            'item'         => '',
            'digital_good' => '',
        ],

        // ── SELLER NOTIFICATION ──────────────────────────────────────────────
        'seller' => [
            'account_low_stock'    => '',
            'account_problem'      => '',
            'item_problem'         => '',
            'topup_problem'        => '',
            'digital_good_problem' => '',
        ],

        // ── BOOSTER NOTIFICATION ─────────────────────────────────────────────
        'booster' => [
            'change_booster'        => '',
            'order_drop'            => '',
            'delete_booster_games'  => '',
            'auto_order_completion' => '',
            'order_completion'      => '',
        ],

        // ── NEW ORDER (one channel per game) ─────────────────────────────────
        // Also used for booster-request reposts and re-available orders.
        'new_order' => [
            'lol'         => 'https://discord.com/api/webhooks/1534831208599650334/nKcpwjwXREudYwD99lVg4k-s69-U4yFeiErWh6IFKzebx2ejqlVpBLvV--avgJ2bBdmr',
            'lol_classic' => 'https://discord.com/api/webhooks/1534831315881427036/wrfMH6Pmdh0Qkoj4O8sLt2EiAwW8B-xH_FaIbXR6OCHcFS8FnG0r3Tn30ENwUeihtrkx',
            'tft'         => 'https://discord.com/api/webhooks/1534832077269110834/MXeJYo9yxxtxaUbrK1tKOsImR6AwUHg4NhlfoWP_TtmzuAydHLz2Zri-XU2s2K_j3CR0',
            'val'         => 'https://discord.com/api/webhooks/1534831589077291130/gb8h6AwcldJZXZge91ZPqGnRPpG6WW_jV9TIJaTAg4S8f7xJboaUh9NTFgaOCKK6kE_g',
            'wild-rift'   => 'https://discord.com/api/webhooks/1534832296551645184/LBDPyFjk8ryeKfQpJtDilazMWqgVkAGRI_Hfh-uO5zuBrC9XA25B4KhPZs7ujcDRyVjq',
            'ow2'         => 'https://discord.com/api/webhooks/1534832418920595511/GF4wPaBXOQam6R7Rc8ULyfdNUx-YpXjk0kxHptFBAs-5gTohlaQ8ym8M-TdK82nhSFRQ',
            'rl'          => 'https://discord.com/api/webhooks/1534832701117567089/33swyY4A6B3ozv2-iW2L03fCi3FnzGjZqb7XGWiTU5B-sPodGLS8OxxwG7M7uNP-ThIZ',
            'apex'        => 'https://discord.com/api/webhooks/1534832809661694073/BsFQS0ZIgeocLXspd8I4sgwddVvr9BDXDGi2Njg2BZfjbj4F02jghtBrDgCz5TxM8sxp',
            'rivals'      => 'https://discord.com/api/webhooks/1534832972820250775/zu4rtbkR550tg5nr7S67866oM5PMLUyDIkW2coDWRqYEjptpzrTDjJWfTXu1T40pCg5s',
            'fortnite'    => 'https://discord.com/api/webhooks/1534830811600130150/hQP1NkUkpDFujia64409k0LxGJ7ZTfkigtocZZD7DRbKbKqgxD5Jsw4YsoiVyqXetvR2',
            'cs2'         => 'https://discord.com/api/webhooks/1534830413980110848/_tj2ZUc2vpPTPavQNShnPoDROMlqWH6YA2zh2Gvy_SAF8j4lbnxsfNtuL_9fQ51xrRYK',
        ],

        // ── JOB APPLICATIONS (one channel per game/role) ─────────────────────
        'apply' => [
            'lol'           => '',
            'val'           => '',
            'tft'           => '',
            'cs2'           => '',
            'fortnite'      => '',
            'wild-rift'     => '',
            'overwatch'     => '',
            'rocket-league' => '',
            'apex'          => '',
            'marvel-rivals' => '',
            'other-games'   => '',
            'gg_girl'       => '',
            'seller'        => '',
        ],
    ];

    $url = trim((string)($channels[$group][$key] ?? ''));

    // Guard against a half-pasted URL silently swallowing notifications.
    if ($url !== '' && strpos($url, 'discord.com/api/webhooks/') === false) {
        error_log('[lb_discord_channel_webhook] Ignoring malformed URL for ' . $group . '/' . $key);
        return '';
    }

    return $url;
}
}

if (!function_exists('lb_booster_security_webhook_url')) {
function lb_booster_security_webhook_url()
{
    if (defined('BOOSTER_SECURITY_WEBHOOK_URL') && BOOSTER_SECURITY_WEBHOOK_URL) {
        return BOOSTER_SECURITY_WEBHOOK_URL;
    }

    $envUrl = getenv('BOOSTER_SECURITY_WEBHOOK_URL');
    return is_string($envUrl) ? trim($envUrl) : '';
}
}

if (!function_exists('lb_sold_notification_webhook_url')) {
/**
 * Webhook URL for the "SOLD NOTIFICATION" forum channel.
 * Each sale type posts into its own thread of the same webhook.
 *
 * @param string $type account|topup|item|digital_good
 */
function lb_sold_notification_webhook_url(string $type): string
{
    // Prefer the plain channel once its URL is configured.
    $channel = lb_discord_channel_webhook('sold', $type);
    if ($channel !== '') {
        return $channel;
    }

    // SOLD NOTIFICATION forum webhook + one thread per sale type.
    $webhook = 'https://discord.com/api/webhooks/1530615644607746190/-ep8ubYHUd2Hp1GhspR_JCOZIgohxwyY8IhplToDeMFZtMYpRiN2zsbbP94Lp9f1kaXP';

    $threads = [
        'account'      => '1530615448155062322',
        'topup'        => '1530615562537926777',
        'item'         => '1530615512604606544',
        'digital_good' => '1530615608863883424',
    ];

    $threadId = trim((string)($threads[$type] ?? ''));
    if ($threadId === '') {
        return $webhook;
    }

    return $webhook . '?thread_id=' . rawurlencode($threadId);
}
}

if (!function_exists('lb_seller_notification_webhook_url')) {
/**
 * Webhook URL for the "SELLER NOTIFICATION" forum channel.
 * Client "report a problem" events post into their own thread per service.
 *
 * @param string $type account_low_stock|account_problem|item_problem|topup_problem|digital_good_problem
 */
function lb_seller_notification_webhook_url(string $type): string
{
    // Prefer the plain channel once its URL is configured.
    $channel = lb_discord_channel_webhook('seller', $type);
    if ($channel !== '') {
        return $channel;
    }

    // SELLER NOTIFICATION forum webhook + one thread per event type.
    $webhook = 'https://discord.com/api/webhooks/1530621254950719499/FeNmPMaEGAbOk7XnHOR7fHE51RYOFJGu95RreCn-wGuet72mFgqtxCy21qJ8RHsOtQXO';

    $threads = [
        'account_low_stock'     => '1530620754633429259',
        'account_problem'       => '1530620671644799137',
        'item_problem'          => '1530621472471646388',
        'topup_problem'         => '1530621510903926895',
        'digital_good_problem'  => '1530621544483651595',
    ];

    $threadId = trim((string)($threads[$type] ?? ''));
    if ($threadId === '') {
        return $webhook;
    }

    return $webhook . '?thread_id=' . rawurlencode($threadId);
}
}

if (!function_exists('lb_booster_notification_webhook_url')) {
/**
 * Webhook URL for the "BOOSTER NOTIFICATION" forum channel.
 * Each booster event posts into its own thread of the same webhook.
 *
 * @param string $type change_booster|order_drop|delete_booster_games|auto_order_completion|order_completion
 */
function lb_booster_notification_webhook_url(string $type): string
{
    // Prefer the plain channel once its URL is configured.
    $channel = lb_discord_channel_webhook('booster', $type);
    if ($channel !== '') {
        return $channel;
    }

    // BOOSTER NOTIFICATION forum webhook + one thread per event type.
    $webhook = 'https://discord.com/api/webhooks/1530619786294460637/R87Z4BZucoi1w7hH5SeYzFVfKemEACT7CteD6d_GY-PfV2yiRx5pRnKQXhdWKdfEfiY6';

    $threads = [
        'change_booster'        => '1530619589409378476',
        'order_drop'            => '1530619637467971665',
        'delete_booster_games'  => '1530619528038449203',
        'auto_order_completion' => '1530619474607214802',
        'order_completion'      => '1530619356281569492',
    ];

    $threadId = trim((string)($threads[$type] ?? ''));
    if ($threadId === '') {
        return $webhook;
    }

    return $webhook . '?thread_id=' . rawurlencode($threadId);
}
}

if (!function_exists('lb_send_sold_notification')) {
/**
 * Post a sale embed into the matching SOLD NOTIFICATION thread.
 * Never throws: a failing webhook must not block sale processing.
 */
function lb_send_sold_notification(string $type, array $payload): void
{
    try {
        $url = lb_sold_notification_webhook_url($type);
        if ($url === '' || strpos($url, 'discord.com/api/webhooks/') === false || !function_exists('curl_init')) {
            return;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    } catch (\Throwable $e) {
        // ignore
    }
}
}

if (!function_exists('lb_check_booster_session_security_alert')) {
function lb_check_booster_session_security_alert($boosterId, $newSession)
{
    global $db;

    $boosterId = (int)$boosterId;
    if ($boosterId <= 0 || !is_array($newSession)) {
        return false;
    }

    // Alert only if the previous and new login are close together.
    // Example: Cairo -> Berlin 8 hours later should trigger.
    $windowHours = defined('BOOSTER_SECURITY_ALERT_WINDOW_HOURS')
        ? (int)BOOSTER_SECURITY_ALERT_WINDOW_HOURS
        : 12;
    if ($windowHours <= 0) {
        $windowHours = 12;
    }

    $newDeviceInfo = json_decode((string)($newSession['device_info'] ?? ''), true);
    $newDeviceInfo = is_array($newDeviceInfo) ? $newDeviceInfo : [];
    $newDeviceFamily = lb_booster_device_family($newDeviceInfo);

    // Skip unknown device families to avoid noisy or unreliable alerts.
    if ($newDeviceFamily === 'unknown') {
        return false;
    }

    // Compare only against the latest previous login from the same device family.
    // Example: mobile -> desktop should not alert, but desktop -> desktop can alert.
    $recentSessions = $db->run(
        "SELECT id, booster_id, token, ip_address, device_info, created_at, city, region, country
         FROM booster_sessions_history
         WHERE booster_id = ? AND id <> ?
         ORDER BY created_at DESC, id DESC
         LIMIT 20",
        $boosterId,
        (int)($newSession['id'] ?? 0)
    );

    $previous = null;
    if (is_array($recentSessions)) {
        foreach ($recentSessions as $session) {
            $sessionDeviceInfo = json_decode((string)($session['device_info'] ?? ''), true);
            $sessionDeviceInfo = is_array($sessionDeviceInfo) ? $sessionDeviceInfo : [];

            if (lb_booster_device_family($sessionDeviceInfo) === $newDeviceFamily) {
                $previous = $session;
                break;
            }
        }
    }

    if (empty($previous)) {
        return false;
    }

    $prevTs = strtotime((string)($previous['created_at'] ?? ''));
    $newTs = strtotime((string)($newSession['created_at'] ?? ''));
    if (!$prevTs || !$newTs) {
        return false;
    }

    $diffSeconds = abs($newTs - $prevTs);
    if ($diffSeconds > ($windowHours * 3600)) {
        return false;
    }

    $prevIp = trim((string)($previous['ip_address'] ?? ''));
    $newIp = trim((string)($newSession['ip_address'] ?? ''));
    $ipChanged = ($prevIp !== '' && $newIp !== '' && $prevIp !== $newIp);

    $prevLocation = lb_booster_location_label($previous);
    $newLocation = lb_booster_location_label($newSession);
    $locationChanged = ($prevLocation !== 'Unknown location' && $newLocation !== 'Unknown location' && $prevLocation !== $newLocation);

    $prevDeviceInfo = json_decode((string)($previous['device_info'] ?? ''), true);
    $prevDeviceInfo = is_array($prevDeviceInfo) ? $prevDeviceInfo : [];
    $prevDeviceType = lb_booster_device_type_label($prevDeviceInfo);
    $newDeviceType = lb_booster_device_type_label($newDeviceInfo);

    // Avoid noise: only alert when the IP and location changed within the same device family.
    // Device-family changes like mobile -> desktop are ignored above.
    if (!$ipChanged || !$locationChanged) {
        return false;
    }

    $booster = $db->row("SELECT id, username, email, discord, discord_id FROM boosters WHERE id = ? LIMIT 1", $boosterId);
    $boosterName = !empty($booster['username']) ? (string)$booster['username'] : ('Booster #' . $boosterId);

    $hours = floor($diffSeconds / 3600);
    $minutes = floor(($diffSeconds % 3600) / 60);
    $diffLabel = ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm';

    $riskReasons = [];
    if ($ipChanged) $riskReasons[] = 'IP changed';
    if ($locationChanged) $riskReasons[] = 'location changed';
    $riskReasons[] = 'same device family: ' . $newDeviceFamily;

    $boosterProfileUrl = 'https://lolboost.gg/admin-area/booster/' . $boosterId;

    $payload = [
        'username' => 'Booster Security',
        'embeds' => [[
            'title' => '⚠️ Suspicious booster login detected',
            'description' => 'A booster logged in from a different IP/location within the same device family and a short time window.',
            'color' => 15158332,
            'fields' => [
                ['name' => 'Booster', 'value' => '#' . $boosterId . ' - ' . $boosterName, 'inline' => true],
                ['name' => 'Time difference', 'value' => $diffLabel . ' (limit: ' . $windowHours . 'h)', 'inline' => true],
                ['name' => 'Reason', 'value' => implode(', ', $riskReasons), 'inline' => false],
                ['name' => 'Previous login', 'value' => "IP: `" . ($prevIp ?: 'Unknown') . "`\nLocation: " . $prevLocation . "\nDevice: " . $prevDeviceType . "\nAt: " . (string)$previous['created_at'], 'inline' => true],
                ['name' => 'New login', 'value' => "IP: `" . ($newIp ?: 'Unknown') . "`\nLocation: " . $newLocation . "\nDevice: " . $newDeviceType . "\nAt: " . (string)$newSession['created_at'], 'inline' => true],
            ],
            'timestamp' => date('c'),
        ]],
        'components' => [[
            'type' => 1,
            'components' => [[
                'type' => 2,
                'style' => 5,
                'label' => 'View Booster',
                'url' => $boosterProfileUrl,
            ]],
        ]],
    ];

    return lb_booster_security_discord_send($payload);
}
}

if (!function_exists('lb_is_profile_game_tracking_disabled_form')) {
function lb_is_profile_game_tracking_disabled_form($formId): bool
    {
        return in_array((int)$formId, [15, 16, 25], true);
    }
}

if (!function_exists('lb_referral_buyer_had_previous_order')) {
function lb_referral_buyer_had_previous_order($invoice)
    {
        global $db;

        if (!is_array($invoice)) {
            return true;
        }

        $clientId = (int)($invoice['client_id'] ?? 0);
        $orderId = (int)($invoice['order_id'] ?? 0);
        $invoiceId = (int)($invoice['id'] ?? 0);
        $buyerEmail = lb_referral_get_buyer_email($invoice);

        try {
            if ($clientId > 0) {
                $previousByClient = (int)($db->cell(
                    "SELECT COUNT(*)
                     FROM orders
                     WHERE client_id = ?
                       AND id <> ?
                     LIMIT 1",
                    $clientId,
                    $orderId
                ) ?: 0);

                if ($previousByClient > 0) {
                    return true;
                }
            }

            if ($buyerEmail !== '') {
                $previousByEmail = (int)($db->cell(
                    "SELECT COUNT(*)
                     FROM invoices
                     WHERE LOWER(email) = LOWER(?)
                       AND id <> ?
                       AND (order_id IS NULL OR order_id <> ?)
                     LIMIT 1",
                    $buyerEmail,
                    $invoiceId,
                    $orderId
                ) ?: 0);

                if ($previousByEmail > 0) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // Safer default: if the eligibility check fails, do not pay a bonus.
            return true;
        }

        return false;
    }
}

if (!function_exists('lb_referral_client_is_allowed')) {
function lb_referral_client_is_allowed($clientId)
    {
        global $db;

        $clientId = (int)$clientId;
        if ($clientId <= 0) {
            return false;
        }

        $settings = lb_referral_get_settings();
        if (!(int)($settings['enabled'] ?? 0) || !(int)($settings['allow_client_referrals'] ?? 0)) {
            return false;
        }

        try {
            $row = $db->row("SELECT referral_enabled FROM clients WHERE id = ? LIMIT 1", $clientId);
        } catch (\Throwable $e) {
            return false;
        }

        return !empty($row) && (int)($row['referral_enabled'] ?? 0) === 1;
    }
}

if (!function_exists('lb_referral_get_client_reward_percent')) {
function lb_referral_get_client_reward_percent($clientId = null)
    {
        global $db;

        $settings = lb_referral_get_settings();
        $defaultPercent = (float)($settings['client_reward_percent'] ?? 5);
        $clientId = (int)$clientId;

        if ($clientId <= 0) {
            return $defaultPercent;
        }

        try {
            $row = $db->row("SELECT referral_percent FROM clients WHERE id = ? LIMIT 1", $clientId);
        } catch (\Throwable $e) {
            return $defaultPercent;
        }

        if (!$row || !array_key_exists('referral_percent', $row) || $row['referral_percent'] === null || $row['referral_percent'] === '') {
            return $defaultPercent;
        }

        return max(0, min(100, (float)$row['referral_percent']));
    }
}

if (!function_exists('lb_referral_link_owner_is_allowed')) {
function lb_referral_link_owner_is_allowed($link)
    {
        if (!is_array($link)) {
            return false;
        }

        $ownerType = (string)($link['owner_type'] ?? '');
        $ownerId = (int)($link['owner_id'] ?? 0);
        $settings = lb_referral_get_settings();

        if (!(int)($settings['enabled'] ?? 0)) {
            return false;
        }
        if ($ownerType === 'client') {
            return lb_referral_client_is_allowed($ownerId);
        }
        if ($ownerType === 'booster') {
            return (int)($settings['allow_booster_referrals'] ?? 0) === 1;
        }
        if ($ownerType === 'seller') {
            return (int)($settings['allow_seller_referrals'] ?? 0) === 1;
        }

        return false;
    }
}

if (!function_exists('lb_wc_client_id')) {
function lb_wc_client_id(): int
    {
        return defined('CLIENT_ID') ? (int)CLIENT_ID : (int)(CLIENT_DATA['id'] ?? 0);
    }
}

if (!function_exists('lb_wc_discount_code')) {
function lb_wc_discount_code(int $clientId): string
    {
        return 'WC26-' . $clientId . '-' . strtoupper(substr(md5($clientId . '|' . microtime(true) . '|' . random_int(1000, 9999)), 0, 8));
    }
}

if (!function_exists('lb_wc_prediction_points')) {
function lb_wc_prediction_points(int $predHome, int $predAway, int $realHome, int $realAway): int
    {
        if ($predHome === $realHome && $predAway === $realAway) {
            return 5;
        }

        $predDiff = $predHome - $predAway;
        $realDiff = $realHome - $realAway;

        if (($predDiff <=> 0) === ($realDiff <=> 0)) {
            if ($predDiff === $realDiff) {
                return 3;
            }
            return 2;
        }

        return 0;
    }
}

if (!function_exists('mark_discount_used_for_paid_invoice')) {
function mark_discount_used_for_paid_invoice($invoice): bool
{
    if (!is_array($invoice)) {
        return false;
    }

    $discount_id = (int)($invoice['discount_id'] ?? 0);
    if ($discount_id <= 0) {
        return false;
    }

    $invoice_status = strtoupper((string)($invoice['status'] ?? ''));
    if ($invoice_status !== 'PAID') {
        return false;
    }

    $discount = db_get_row('discounts', ['id' => $discount_id], 1);
    if ($discount == false) {
        return false;
    }

    db_update_row('discounts', ['id' => $discount_id], [
        'uses' => ((int)($discount['uses'] ?? 0)) + 1
    ]);

    return true;
}
}

if (!function_exists('riot_backfill_order_matches')) {
function riot_backfill_order_matches(int $orderId, array $order, array $order_options, array $order_account, array $order_progress, $db, array $options = []): array
{
    if ($orderId <= 0) {
        throw new \InvalidArgumentException('Invalid order id.');
    }

    if (function_exists('lb_is_profile_game_tracking_disabled_form') && lb_is_profile_game_tracking_disabled_form($order['form_id'] ?? 0)) {
        return [
            'ok' => true,
            'order_id' => $orderId,
            'tracking_disabled' => true,
            'matched_from_riot' => 0,
            'inserted_visible' => 0,
            'processed_wins' => 0,
            'processed_losses' => 0,
            'total_visible_matches' => 0,
            'wins' => 0,
            'losses' => 0,
        ];
    }

    $server = trim((string)($options['server'] ?? $order_options['server'] ?? ''));
    if ($server === '') {
        $server = 'euw';
    }

    $playMode = strtolower(trim((string)($options['play_mode'] ?? '')));
    if (!in_array($playMode, ['solo', 'duo'], true)) {
        $playMode = !empty($order_options['is_duo']) ? 'duo' : 'solo';
    }

    $riotId = trim((string)($options['riot_id'] ?? ''));
    $puuid = trim((string)($options['puuid'] ?? ''));

    if ($puuid === '') {
        if ($riotId === '') {
            $riotId = $playMode === 'duo'
                ? trim((string)($order_progress['booster_ign'] ?? ''))
                : trim((string)($order_account['ign'] ?? ''));
        }

        if ($riotId === '') {
            throw new \RuntimeException('Riot ID or PUUID is required for backfill sync.');
        }

        $puuid = (string)(riot_get_puuid($riotId, $server) ?? '');
        if ($puuid === '') {
            throw new \RuntimeException('PUUID not found for Riot ID.');
        }
    }

    $defaultStart = (int)riot_order_sync_start_time($order);
    $startTime = riot_parse_backfill_time($options['start_at'] ?? '', $defaultStart);
    $endTime = riot_parse_backfill_time($options['end_at'] ?? '', time());

    if ($startTime === null || $startTime <= 0) {
        throw new \RuntimeException('Invalid backfill start time.');
    }
    if ($endTime !== null && $endTime <= $startTime) {
        throw new \RuntimeException('Backfill end time must be after start time.');
    }

    $maxMatches = (int)($options['max_matches'] ?? 100);
    $queueTypes = $options['queue_type'] ?? ($order_options['queue_type'] ?? 'solo/duo');

    $matchIds = riot_get_matches_between($puuid, $server, $startTime, $endTime, $queueTypes, $maxMatches);

    $clientPuuid = trim((string)($order_progress['puuid'] ?? ''));
    if ($clientPuuid === '') {
        $clientRiotId = trim((string)($order_account['ign'] ?? ''));
        if ($clientRiotId !== '') {
            $clientPuuid = (string)(riot_get_puuid($clientRiotId, $server) ?? '');
        }
    }

    $rank = $clientPuuid !== '' ? riot_get_rank($clientPuuid, $server, $queueTypes) : ['tier' => null, 'division' => null, 'lp' => null];
    $beforeRecord = riot_get_order_match_record($orderId, $db);

    $summary = riot_process_matches(
        $matchIds,
        $puuid,
        $server,
        $orderId,
        $db,
        (int)($order['booster_id'] ?? 0),
        $playMode,
        $rank
    );

    $afterRecord = riot_get_order_match_record($orderId, $db);
    $insertedVisible = max(0, (int)($afterRecord['total_matches'] ?? 0) - (int)($beforeRecord['total_matches'] ?? 0));

    save_riot_rank($orderId, $rank, $db, [
        'wins' => (int)($summary['wins'] ?? 0),
        'losses' => (int)($summary['losses'] ?? 0),
    ]);
    riot_sync_order_start_from_options($orderId, $order_options);

    if (!empty($options['save_order_account']) && $riotId !== '') {
        $currentAccount = db_get_row('order_accounts', ['order_id' => $orderId], 1);
        if (empty($currentAccount)) {
            db_add_row('order_accounts', [
                'order_id' => $orderId,
                'ign' => $riotId,
                'login' => '',
                'password' => '',
            ]);
        } else {
            db_update_row('order_accounts', ['order_id' => $orderId], ['ign' => $riotId]);
        }

        try {
            $progressPayload = ['puuid' => $puuid];
            $currentProgress = db_get_row('order_progress', ['order_id' => $orderId], 1);
            if (empty($currentProgress)) {
                $progressPayload['order_id'] = $orderId;
                db_add_row('order_progress', $progressPayload);
            } else {
                db_update_row('order_progress', ['order_id' => $orderId], $progressPayload);
            }
        } catch (\Throwable $e) {}
    }

    if (!empty($options['save_duo_account']) && $playMode === 'duo') {
        try {
            $columns = [];
            $rows = $db->run('SHOW COLUMNS FROM order_progress');
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $columns[strtolower((string)($row['Field'] ?? ''))] = true;
                }
            }
            if (empty($columns['booster_ign'])) {
                $db->run('ALTER TABLE order_progress ADD COLUMN booster_ign VARCHAR(100) NULL DEFAULT NULL');
            }
            if (empty($columns['booster_puuid'])) {
                $db->run('ALTER TABLE order_progress ADD COLUMN booster_puuid VARCHAR(100) NULL DEFAULT NULL');
            }
            if (empty($columns['booster_ign_set_at'])) {
                $db->run('ALTER TABLE order_progress ADD COLUMN booster_ign_set_at DATETIME NULL DEFAULT NULL');
            }
        } catch (\Throwable $e) {}

        $payload = [
            'booster_puuid' => $puuid,
            'booster_ign_set_at' => date('Y-m-d H:i:s', $startTime),
        ];
        if ($riotId !== '') {
            $payload['booster_ign'] = $riotId;
        }

        $currentProgress = db_get_row('order_progress', ['order_id' => $orderId], 1);
        if (empty($currentProgress)) {
            $payload['order_id'] = $orderId;
            db_add_row('order_progress', $payload);
        } else {
            db_update_row('order_progress', ['order_id' => $orderId], $payload);
        }
    }

    return [
        'ok' => true,
        'order_id' => $orderId,
        'riot_id' => $riotId !== '' ? $riotId : null,
        'puuid' => $puuid,
        'server' => $server,
        'play_mode' => $playMode,
        'start_at' => date('Y-m-d H:i:s', $startTime),
        'end_at' => $endTime !== null ? date('Y-m-d H:i:s', $endTime) : null,
        'matched_from_riot' => count($matchIds),
        'inserted_visible' => $insertedVisible,
        'processed_wins' => (int)($summary['wins'] ?? 0),
        'processed_losses' => (int)($summary['losses'] ?? 0),
        'total_visible_matches' => (int)($afterRecord['total_matches'] ?? 0),
        'wins' => (int)($afterRecord['wins'] ?? 0),
        'losses' => (int)($afterRecord['losses'] ?? 0),
    ];
}
}

if (!function_exists('riot_get_matches_between')) {
function riot_get_matches_between(string $puuid, string $server, int $startTime, ?int $endTime = null, $queueTypes = null, int $maxMatches = 100): array
{
    $baseUrl = riot_regional_url($server);
    $count = 100;
    $maxMatches = max(1, min(300, $maxMatches));
    $all_match_ids = [];
    $start_time = max(0, $startTime);
    $end_time = $endTime !== null && $endTime > $start_time ? (int)$endTime : null;

    $queueIds = [];
    if ($queueTypes !== null && $queueTypes !== '' && $queueTypes !== 'all') {
        $queueValues = is_array($queueTypes) ? $queueTypes : [$queueTypes];
        foreach ($queueValues as $queueValue) {
            if (is_numeric($queueValue)) {
                $queueIds[] = (int)$queueValue;
                continue;
            }
            if (function_exists('util_lol_queue_type_ids')) {
                $ids = util_lol_queue_type_ids((string)$queueValue);
                if (is_array($ids)) {
                    foreach ($ids as $id) $queueIds[] = (int)$id;
                }
            }
        }
    }

    if (empty($queueIds)) {
        $queueIds = [420];
    }
    $queueIds = array_values(array_unique(array_filter(array_map('intval', $queueIds), fn($id) => $id > 0)));

    foreach ($queueIds as $queueId) {
        $start = 0;
        while (count($all_match_ids) < $maxMatches) {
            $url = "{$baseUrl}/lol/match/v5/matches/by-puuid/" . urlencode($puuid) . "/ids?queue={$queueId}&start={$start}&count={$count}&startTime={$start_time}";
            if ($end_time !== null) {
                $url .= '&endTime=' . $end_time;
            }

            $batch = riot_api_get($url);
            if (!is_array($batch) || empty($batch)) {
                break;
            }

            $all_match_ids = array_merge($all_match_ids, $batch);

            if (count($batch) < $count) {
                break;
            }

            $start += $count;
        }
    }

    $all_match_ids = array_values(array_unique($all_match_ids));
    return array_slice($all_match_ids, 0, $maxMatches);
}
}

if (!function_exists('riot_order_matches_visible_sql')) {
function riot_order_matches_visible_sql(string $alias = 'om'): string
{
    $alias = trim($alias) !== '' ? trim($alias) : 'om';
    return "COALESCE({$alias}.is_hidden, 0) = 0";
}
}

if (!function_exists('riot_order_start_payload_from_options')) {
function riot_order_start_payload_from_options(array $order_options): array
{
    $payload = [];

    $tierToRiot = static function ($value): ?string {
        if ($value === '' || $value === null) {
            return null;
        }

        $map = [
            0 => 'UNRANKED',
            1 => 'IRON',
            2 => 'BRONZE',
            3 => 'SILVER',
            4 => 'GOLD',
            5 => 'PLATINUM',
            6 => 'EMERALD',
            7 => 'DIAMOND',
            8 => 'MASTER',
            9 => 'GRANDMASTER',
            10 => 'CHALLENGER',
        ];

        if (is_numeric($value)) {
            return $map[(int) $value] ?? null;
        }

        $value = strtoupper(trim((string) $value));
        return $value !== '' ? $value : null;
    };

    $divisionToRiot = static function ($value): ?string {
        if ($value === '' || $value === null) {
            return null;
        }

        $map = [
            1 => 'IV',
            2 => 'III',
            3 => 'II',
            4 => 'I',
        ];

        if (is_numeric($value)) {
            return $map[(int) $value] ?? null;
        }

        $value = strtoupper(trim((string) $value));
        return $value !== '' ? $value : null;
    };

    if (array_key_exists('start_tier', $order_options)) {
        $tier = $tierToRiot($order_options['start_tier']);
        if ($tier !== null) {
            $payload['start_tier'] = $tier;
        }
    }

    if (array_key_exists('start_division', $order_options)) {
        $division = $divisionToRiot($order_options['start_division']);
        if ($division !== null) {
            $payload['start_division'] = $division;
        }
    }

    if (array_key_exists('start_lp', $order_options) && $order_options['start_lp'] !== '' && $order_options['start_lp'] !== null) {
        $payload['start_lp'] = (string) ((int) $order_options['start_lp']);
    }

    return $payload;
}
}

if (!function_exists('riot_parse_backfill_time')) {
function riot_parse_backfill_time($value, ?int $fallback = null): ?int
{
    $value = trim((string)$value);
    if ($value === '') {
        return $fallback;
    }

    if (ctype_digit($value)) {
        $timestamp = (int)$value;
        return $timestamp > 0 ? $timestamp : $fallback;
    }

    $timestamp = strtotime($value);
    if ($timestamp === false || $timestamp <= 0) {
        return $fallback;
    }

    return $timestamp;
}
}

if (!function_exists('riot_sync_order_start_from_options')) {
function riot_sync_order_start_from_options(int $orderId, array $order_options): void
{
    if ($orderId <= 0) {
        return;
    }

    $payload = riot_order_start_payload_from_options($order_options);
    if (empty($payload)) {
        return;
    }

    db_update_row('order_progress', ['order_id' => $orderId], $payload);
}
}

if (!function_exists('seller_auto_confirmed_sales_label')) {
function seller_auto_confirmed_sales_label(int $count): string
    {
        if ($count <= 0) {
            return '';
        }
        return $count === 1
            ? '1 review'
            : number_format($count) . ' reviews';
    }
}

if (!function_exists('seller_auto_confirmed_sales_without_review_count')) {
function seller_auto_confirmed_sales_without_review_count(int $seller_id, int $days = 3): int
    {
        global $db;

        if ($seller_id <= 0 || !isset($db) || !is_object($db)) {
            return 0;
        }

        $days = max(1, $days);
        $total = 0;

        try {
            $row = $db->row(
                "SELECT COUNT(*) AS cnt
                 FROM selling_accounts sa
                 WHERE sa.seller_id = ?
                   AND sa.sold = 1
                   AND sa.client_id IS NOT NULL
                   AND COALESCE(sa.sold_at, sa.created_at) <= (NOW() - INTERVAL {$days} DAY)
                   AND NOT EXISTS (
                       SELECT 1
                       FROM seller_reviews sr
                       WHERE sr.seller_id = sa.seller_id
                         AND sr.client_id = sa.client_id
                       LIMIT 1
                   )",
                $seller_id
            );
            $total += (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            // Older installs may miss sold_at/created_at. Keep UI alive.
        }

        try {
            $row = $db->row(
                "SELECT COUNT(*) AS cnt
                 FROM selling_item_purchases sip
                 WHERE sip.seller_id = ?
                   AND sip.client_id IS NOT NULL
                   AND COALESCE(sip.created_at, NOW()) <= (NOW() - INTERVAL {$days} DAY)
                   AND NOT EXISTS (
                       SELECT 1
                       FROM seller_reviews sr
                       WHERE sr.seller_id = sip.seller_id
                         AND sr.client_id = sip.client_id
                       LIMIT 1
                   )",
                $seller_id
            );
            $total += (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            // selling_item_purchases may not exist in older installs.
        }

        return max(0, $total);
    }
}

if (!function_exists('seller_auto_confirmed_sales_without_review_rows')) {
function seller_auto_confirmed_sales_without_review_rows(int $seller_id, int $days = 3, int $limit = 10, int $offset = 0): array
    {
        global $db;

        if ($seller_id <= 0 || !isset($db) || !is_object($db)) {
            return [];
        }

        $days = max(1, $days);
        $limit = max(1, min(5000, $limit));
        $offset = max(0, $offset);
        $rows = [];

        try {
            $accountRows = $db->run(
                "SELECT sa.id AS sale_id,
                        COALESCE(sa.sold_at, sa.created_at) AS confirmed_at,
                        c.username AS client_username,
                        c.icon AS client_icon,
                        'account' AS sale_type
                 FROM selling_accounts sa
                 LEFT JOIN clients c ON c.id = sa.client_id
                 WHERE sa.seller_id = ?
                   AND sa.sold = 1
                   AND sa.client_id IS NOT NULL
                   AND COALESCE(sa.sold_at, sa.created_at) <= (NOW() - INTERVAL {$days} DAY)
                   AND NOT EXISTS (
                       SELECT 1
                       FROM seller_reviews sr
                       WHERE sr.seller_id = sa.seller_id
                         AND sr.client_id = sa.client_id
                       LIMIT 1
                   )
                 ORDER BY confirmed_at DESC",
                $seller_id
            ) ?: [];

            foreach ($accountRows as $row) {
                $rows[] = $row;
            }
        } catch (\Throwable $e) {
            // Older installs may miss sold_at/created_at. Keep UI alive.
        }

        try {
            $itemRows = $db->run(
                "SELECT sip.id AS sale_id,
                        COALESCE(sip.created_at, NOW()) AS confirmed_at,
                        c.username AS client_username,
                        c.icon AS client_icon,
                        'item' AS sale_type
                 FROM selling_item_purchases sip
                 LEFT JOIN clients c ON c.id = sip.client_id
                 WHERE sip.seller_id = ?
                   AND sip.client_id IS NOT NULL
                   AND COALESCE(sip.created_at, NOW()) <= (NOW() - INTERVAL {$days} DAY)
                   AND NOT EXISTS (
                       SELECT 1
                       FROM seller_reviews sr
                       WHERE sr.seller_id = sip.seller_id
                         AND sr.client_id = sip.client_id
                       LIMIT 1
                   )
                 ORDER BY confirmed_at DESC",
                $seller_id
            ) ?: [];

            foreach ($itemRows as $row) {
                $rows[] = $row;
            }
        } catch (\Throwable $e) {
            // selling_item_purchases may not exist in older installs.
        }

        usort($rows, static function ($a, $b) {
            return strtotime((string)($b['confirmed_at'] ?? '')) <=> strtotime((string)($a['confirmed_at'] ?? ''));
        });

        return array_slice($rows, $offset, $limit);
    }
}

if (!function_exists('seller_no_feedback_entries')) {
/**
 * Completed sales whose buyer did not leave a review within $hours.
 *
 * These are not stored in seller_reviews — they are derived on read, so they never
 * touch the seller's rating average or review count. Each entry is rendered on the
 * seller profile as a "No Feedback left." card once the grace period has passed.
 *
 * @return array<int,array{sale_type:string,sale_id:int,confirmed_at:string,created_at:string,client_username:?string,client_icon:?string,is_placeholder:int}>
 */
function seller_no_feedback_entries(int $seller_id, int $hours = 24): array
    {
        global $db;

        if ($seller_id <= 0 || !isset($db) || !is_object($db)) {
            return [];
        }

        $hours = max(1, min(8760, $hours));
        $rows = [];

        // Each source: completed sale, buyer known, grace period over, and that buyer
        // never left a review for this seller.
        $sources = [
            'account' => "SELECT sa.id AS sale_id,
                                 COALESCE(sa.sold_at, sa.created_at) AS confirmed_at,
                                 c.username AS client_username,
                                 c.icon AS client_icon
                          FROM selling_accounts sa
                          LEFT JOIN clients c ON c.id = sa.client_id
                          WHERE sa.seller_id = ?
                            AND sa.sold = 1
                            AND sa.client_id IS NOT NULL
                            AND COALESCE(sa.sold_at, sa.created_at) <= (NOW() - INTERVAL {$hours} HOUR)
                            AND NOT EXISTS (SELECT 1 FROM seller_reviews sr
                                            WHERE sr.seller_id = sa.seller_id AND sr.client_id = sa.client_id)",
            'item' => "SELECT sip.id AS sale_id,
                              COALESCE(sip.completed_at, sip.paid_at, sip.created_at) AS confirmed_at,
                              c.username AS client_username,
                              c.icon AS client_icon
                       FROM selling_item_purchases sip
                       LEFT JOIN clients c ON c.id = sip.client_id
                       WHERE sip.seller_id = ?
                         AND sip.client_id IS NOT NULL
                         AND UPPER(sip.status) = 'COMPLETED'
                         AND COALESCE(sip.completed_at, sip.paid_at, sip.created_at) <= (NOW() - INTERVAL {$hours} HOUR)
                         AND NOT EXISTS (SELECT 1 FROM seller_reviews sr
                                         WHERE sr.seller_id = sip.seller_id AND sr.client_id = sip.client_id)",
            'topup' => "SELECT stp.id AS sale_id,
                               COALESCE(stp.paid_at, stp.created_at) AS confirmed_at,
                               c.username AS client_username,
                               c.icon AS client_icon
                        FROM selling_topup_purchases stp
                        LEFT JOIN clients c ON c.id = stp.client_id
                        WHERE stp.seller_id = ?
                          AND stp.client_id IS NOT NULL
                          AND UPPER(stp.status) = 'COMPLETED'
                          AND COALESCE(stp.paid_at, stp.created_at) <= (NOW() - INTERVAL {$hours} HOUR)
                          AND NOT EXISTS (SELECT 1 FROM seller_reviews sr
                                          WHERE sr.seller_id = stp.seller_id AND sr.client_id = stp.client_id)",
            'digital_good' => "SELECT dgp.id AS sale_id,
                                      COALESCE(dgp.completed_at, dgp.delivered_at, dgp.paid_at, dgp.created_at) AS confirmed_at,
                                      c.username AS client_username,
                                      c.icon AS client_icon
                               FROM digital_good_purchases dgp
                               LEFT JOIN clients c ON c.id = dgp.client_id
                               WHERE dgp.seller_id = ?
                                 AND dgp.client_id IS NOT NULL
                                 AND UPPER(dgp.status) = 'COMPLETED'
                                 AND COALESCE(dgp.completed_at, dgp.delivered_at, dgp.paid_at, dgp.created_at) <= (NOW() - INTERVAL {$hours} HOUR)
                                 AND NOT EXISTS (SELECT 1 FROM seller_reviews sr
                                                 WHERE sr.seller_id = dgp.seller_id AND sr.client_id = dgp.client_id)",
        ];

        foreach ($sources as $type => $sql) {
            try {
                foreach (($db->run($sql, $seller_id) ?: []) as $row) {
                    $confirmedAt = (string)($row['confirmed_at'] ?? '');
                    $confirmedTs = $confirmedAt !== '' ? strtotime($confirmedAt) : false;
                    if ($confirmedTs === false) continue;
                    $rows[] = [
                        'sale_type'       => $type,
                        'sale_id'         => (int)($row['sale_id'] ?? 0),
                        'confirmed_at'    => $confirmedAt,
                        // The card appears the moment the grace period runs out.
                        'created_at'      => date('Y-m-d H:i:s', $confirmedTs + ($hours * 3600)),
                        'client_username' => $row['client_username'] ?? null,
                        'client_icon'     => $row['client_icon'] ?? null,
                        'is_placeholder'  => 1,
                    ];
                }
            } catch (\Throwable $e) {
                // Source table may not exist on older installs — skip it.
            }
        }

        usort($rows, static fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

        return $rows;
    }
}

if (!function_exists('track_selling_account_view')) {
function track_selling_account_view(int $account_id, int $seller_id = 0): bool
{
    global $db;

    if ($account_id <= 0 || empty($db)) {
        return false;
    }

    // Do not count the seller's own public listing views.
    if ($seller_id > 0 && defined('SELLER_ID') && SELLER_ID && (int)SELLER_ID === $seller_id) {
        return false;
    }

    $cookie_name = 'lb_account_viewer';
    if (empty($_COOKIE[$cookie_name]) || !preg_match('/^[a-f0-9]{64}$/i', (string)$_COOKIE[$cookie_name])) {
        $viewer_id = bin2hex(random_bytes(32));
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        if (!headers_sent()) {
            setcookie($cookie_name, $viewer_id, [
                'expires'  => time() + 31536000,
                'path'     => '/',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        $_COOKIE[$cookie_name] = $viewer_id;
    } else {
        $viewer_id = (string)$_COOKIE[$cookie_name];
    }

    $salt = defined('APP_KEY') ? (string)APP_KEY : (defined('GL_SECRET') ? (string)GL_SECRET : 'lolboost_selling_account_views');
    $visitor_hash = hash('sha256', $viewer_id . '|selling_account|' . $account_id . '|' . $salt);

    $existing = $db->row(
        "SELECT id, viewed_at
         FROM selling_account_views
         WHERE account_id = ? AND visitor_hash = ?
         LIMIT 1",
        $account_id,
        $visitor_hash
    );

    if (!empty($existing)) {
        $last_view = !empty($existing['viewed_at']) ? strtotime((string)$existing['viewed_at']) : false;

        if ($last_view && $last_view >= (time() - 86400)) {
            return false;
        }

        $db->run(
            "UPDATE selling_account_views
             SET viewed_at = NOW()
             WHERE id = ?",
            (int)$existing['id']
        );
    } else {
        $db->run(
            "INSERT INTO selling_account_views (account_id, visitor_hash, viewed_at)
             VALUES (?, ?, NOW())",
            $account_id,
            $visitor_hash
        );
    }

    $db->run(
        "UPDATE selling_accounts
         SET views = views + 1
         WHERE id = ?",
        $account_id
    );

    return true;
}
}

if (!function_exists('util_load_countries_select')) {
function util_load_countries_select($current = null)
{
    $current = is_string($current) ? trim($current) : '';
    $currentLower = strtolower($current);

    $countries = [
        'Afghanistan','Albania','Algeria','Andorra','Angola','Argentina','Armenia','Australia','Austria',
        'Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bhutan',
        'Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi',
        'Cambodia','Cameroon','Canada','Cape Verde','Central African Republic','Chad','Chile','China','Colombia',
        'Comoros','Congo','Costa Rica','Croatia','Cuba','Cyprus','Czech Republic','Denmark','Djibouti','Dominica',
        'Dominican Republic','Ecuador','Egypt','El Salvador','Equatorial Guinea','Eritrea','Estonia','Eswatini',
        'Ethiopia','Fiji','Finland','France','Gabon','Gambia','Georgia','Germany','Ghana','Greece','Grenada',
        'Guatemala','Guinea','Guinea-Bissau','Guyana','Haiti','Honduras','Hungary','Iceland','India','Indonesia',
        'Iran','Iraq','Ireland','Israel','Italy','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Kiribati',
        'Kosovo','Kuwait','Kyrgyzstan','Laos','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein',
        'Lithuania','Luxembourg','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands',
        'Mauritania','Mauritius','Mexico','Micronesia','Moldova','Monaco','Mongolia','Montenegro','Morocco',
        'Mozambique','Myanmar','Namibia','Nauru','Nepal','Netherlands','New Zealand','Nicaragua','Niger',
        'Nigeria','North Korea','North Macedonia','Norway','Oman','Pakistan','Palau','Palestine','Panama',
        'Papua New Guinea','Paraguay','Peru','Philippines','Poland','Portugal','Qatar','Romania','Russia',
        'Rwanda','Saint Lucia','Samoa','San Marino','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone',
        'Singapore','Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Korea','South Sudan',
        'Spain','Sri Lanka','Sudan','Suriname','Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania',
        'Thailand','Timor-Leste','Togo','Tonga','Trinidad and Tobago','Tunisia','Turkey','Turkmenistan','Tuvalu',
        'Uganda','Ukraine','United Arab Emirates','United Kingdom','United States','Uruguay','Uzbekistan',
        'Vanuatu','Vatican City','Venezuela','Vietnam','Yemen','Zambia','Zimbabwe',
    ];

    $opts = [];
    $opts[] = '<option value="" ' . ($currentLower === '' ? 'selected' : '') . '>N/A (not set)</option>';

    $matched = false;
    foreach ($countries as $country) {
        $sel = (strtolower($country) === $currentLower) ? 'selected' : '';
        if ($sel !== '') $matched = true;
        $opts[] = '<option value="' . esc($country) . '" ' . $sel . '>' . esc($country) . '</option>';
    }

    // If the stored value doesn't match any known country (legacy free-text
    // entry), keep it visible/selected as its own option so it isn't lost.
    if (!$matched && $current !== '') {
        $opts[] = '<option value="' . esc($current) . '" selected>' . esc($current) . '</option>';
    }

    return implode("\n", $opts);
}
}

if (!function_exists('util_normalize_lol_queue_type')) {
function util_normalize_lol_queue_type($type): string
{
    $type = strtolower(trim((string) $type));
    $type = str_replace(['-', ' '], ['_', '_'], $type);

    $aliases = [
        'soloq' => 'solo/duo',
        'solo' => 'solo/duo',
        'solo_duo' => 'solo/duo',
        'solo/duo' => 'solo/duo',
        'ranked_solo' => 'solo/duo',
        'ranked_solo_duo' => 'solo/duo',
        'duo' => 'solo/duo',
        'flexq' => 'flex',
        'flex' => 'flex',
        'flex_queue' => 'flex',
        'ranked_flex' => 'flex',
        'ranked_flex_queue' => 'flex',
        'ranked_flex_sr' => 'flex',
        'normal_games' => 'normal',
        'quickplay' => 'normal',
        'draft' => 'normal',
        'aram' => 'aram',
        'arena' => 'arena',
        'clash' => 'clash',
        'bots' => 'bots',
        'coop' => 'bots',
        'co_op' => 'bots',
        'tft' => 'tft',
        'featured' => 'featured',
        'custom' => 'custom',
        'tutorial' => 'tutorial',
        'summoners_rift' => 'summoners_rift',
        'all' => 'all',
    ];

    return $aliases[$type] ?? $type;
}
}

if (!function_exists('lb_multi_booster_sync_primary')) {
/**
 * Keep the legacy orders.booster_id pointer in sync with the multi-booster team.
 *
 * Access and payouts use order_boosters for multi-booster orders, while older
 * views still read orders.booster_id. The primary pointer must therefore always
 * reference the first active slot and must not jump to the latest claimant.
 */
function lb_multi_booster_sync_primary(int $orderId): ?array
{
    global $db;

    if ($orderId <= 0 || empty($db)) {
        return null;
    }

    try {
        $order = $db->row(
            "SELECT id, form_id, booster_id, claimed_at, status
               FROM orders
              WHERE id = ?
              LIMIT 1",
            $orderId
        );
        if (empty($order)) {
            return null;
        }

        $formId = (int)($order['form_id'] ?? 0);
        $legacyBoosterId = (int)($order['booster_id'] ?? 0);
        $supportsSimpleTeamSlots = in_array($formId, [4, 19], true);

        $activeRows = $db->run(
            "SELECT id, booster_id, slot_no, role, claimed_at
               FROM order_boosters
              WHERE order_id = ?
                AND status = 'ACTIVE'
                AND booster_id IS NOT NULL
                AND booster_id > 0
              ORDER BY slot_no ASC, id ASC",
            $orderId
        ) ?: [];

        // Older claim code could put the newest booster only into
        // orders.booster_id without creating its team membership. Preserve that
        // booster by backfilling the next free slot before normalizing primary.
        if ($supportsSimpleTeamSlots && $legacyBoosterId > 0) {
            $legacyIsActive = false;
            $usedSlots = [];
            foreach ($activeRows as $activeRow) {
                $activeBoosterId = (int)($activeRow['booster_id'] ?? 0);
                if ($activeBoosterId === $legacyBoosterId) {
                    $legacyIsActive = true;
                }
                $usedSlots[(int)($activeRow['slot_no'] ?? 0)] = true;
            }

            $required = function_exists('lb_multi_booster_required_count')
                ? lb_multi_booster_required_count($orderId)
                : 1;
            if ($required <= 1) {
                $opts = $db->row(
                    "SELECT boosters, hours FROM order_options WHERE order_id = ? LIMIT 1",
                    $orderId
                ) ?: [];
                $required = max(1, (int)($opts['boosters'] ?? $opts['hours'] ?? 1));
            }
            $required = max(1, min(4, $required));

            if (!$legacyIsActive && count($activeRows) < $required) {
                $slotNo = 1;
                while (!empty($usedSlots[$slotNo]) && $slotNo <= $required) {
                    $slotNo++;
                }

                if ($slotNo <= $required) {
                    $role = 'ClashSlot' . $slotNo;
                    $claimedAt = trim((string)($order['claimed_at'] ?? ''));
                    if ($claimedAt === '' || $claimedAt === '0000-00-00 00:00:00') {
                        $claimedAt = date('Y-m-d H:i:s');
                    }

                    // Release unique role/slot keys held by historical rows.
                    $db->run(
                        "UPDATE order_boosters
                            SET slot_no = 1000000 + id,
                                role = CONCAT('Removed', id, '_', UNIX_TIMESTAMP())
                          WHERE order_id = ?
                            AND status <> 'ACTIVE'
                            AND (slot_no = ? OR role = ?)",
                        $orderId,
                        $slotNo,
                        $role
                    );

                    $existing = $db->row(
                        "SELECT id FROM order_boosters WHERE order_id = ? AND booster_id = ? LIMIT 1",
                        $orderId,
                        $legacyBoosterId
                    );
                    if (!empty($existing)) {
                        $db->run(
                            "UPDATE order_boosters
                                SET slot_no = ?, role = ?, cut_percent = 50,
                                    status = 'ACTIVE', claimed_at = ?,
                                    removed_at = NULL, removed_by_admin_id = NULL
                              WHERE id = ?",
                            $slotNo,
                            $role,
                            $claimedAt,
                            (int)$existing['id']
                        );
                    } else {
                        $db->run(
                            "INSERT INTO order_boosters
                                (order_id, booster_id, slot_no, role, cut_percent, status, claimed_at)
                             VALUES (?, ?, ?, ?, 50, 'ACTIVE', ?)",
                            $orderId,
                            $legacyBoosterId,
                            $slotNo,
                            $role,
                            $claimedAt
                        );
                    }
                }
            }
        }

        $primary = $db->row(
            "SELECT booster_id, claimed_at, slot_no
               FROM order_boosters
              WHERE order_id = ?
                AND status = 'ACTIVE'
                AND booster_id IS NOT NULL
                AND booster_id > 0
              ORDER BY slot_no ASC, id ASC
              LIMIT 1",
            $orderId
        );

        if (empty($primary) || (int)($primary['booster_id'] ?? 0) <= 0) {
            return null;
        }

        $claimedAt = trim((string)($primary['claimed_at'] ?? ''));
        if ($claimedAt === '' || $claimedAt === '0000-00-00 00:00:00') {
            $claimedAt = date('Y-m-d H:i:s');
        }

        $primaryUpdate = [
            'booster_id' => (int)$primary['booster_id'],
            'claimed_at' => $claimedAt,
            'booster_cut' => 50,
        ];

        // Synchronizing the legacy primary-booster pointer must not reopen an
        // order that has already reached a terminal state. This helper also
        // runs whenever an admin opens or refreshes a team-order page.
        $currentStatus = strtoupper(trim((string)($order['status'] ?? '')));
        if (in_array($currentStatus, ['', 'PAID', 'PROCESSING', 'IN_PROGRESS'], true)) {
            $primaryUpdate['status'] = 'IN_PROGRESS';
        }

        db_update_row('orders', ['id' => $orderId], $primaryUpdate);

        $primary['claimed_at'] = $claimedAt;
        return $primary;
    } catch (\Throwable $e) {
        return null;
    }
}
}
