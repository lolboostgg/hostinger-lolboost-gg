<?php
/**
 * Discord DM notification: "MISSING MESSAGE"
 *
 * V3 (autopath; file logging disabled)
 * - Finds vendor/autoload.php and core/config.php automatically by walking up directories
 * - File logging disabled to avoid creating .log files
 * - DEBUG_ALWAYS=true so it inserts ds_discord_debug rows when DB is reachable
 */

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');

define('MISSED_MINUTES', 5);
define('DEBUG_ALWAYS', false);
define('DEBUG_TYPE', 'ds_discord_debug');
define('MISSED_LOG_TYPE', 'ds_msg_notif_booster_missed_log');

ini_set('log_errors', '0');

// --- Auto-find project files by walking up directories ---
function find_up(string $startDir, string $relativePath, int $maxDepth = 8): ?string {
    $dir = $startDir;
    for ($i = 0; $i <= $maxDepth; $i++) {
        $candidate = $dir . '/' . ltrim($relativePath, '/');
        if (is_file($candidate)) return $candidate;
        $parent = dirname($dir);
        if ($parent === $dir) break;
        $dir = $parent;
    }
    return null;
}

$autoload = find_up(__DIR__, 'vendor/autoload.php');
$config   = find_up(__DIR__, 'core/config.php');
$funcs    = find_up(__DIR__, 'core/functions.php');
$view     = find_up(__DIR__, 'core/view.php');

if (!$autoload || !$config || !$funcs || !$view) {
    exit;
}

require $autoload;
require $config;
require $funcs;
require $view;

// Single-instance guard. This cron does up to 200 notifications × several DB queries
// + Discord API calls per run; if that overruns the cron interval, a second process
// starts on top of the first and they pile up into 100% CPU. Skip if one is running.
$__dmn_lock = @fopen(rtrim(sys_get_temp_dir(), "/\\") . '/lolboost_discord_missed_dm_cron.lock', 'c');
if ($__dmn_lock === false || !flock($__dmn_lock, LOCK_EX | LOCK_NB)) {
    exit;
}

try {
    run_discord_missed_message_cron();
} catch (Throwable $e) {
    // Logging disabled.
}

// seller_unread_message_cron.php now has its own direct hosting-panel cron entry
// (added later), so the piggyback trigger below would run it a second time every
// minute on top of that. Disabled to stop the duplicate run.

function run_seller_unread_message_cron_trigger(): void
{
    if (!defined('REALTIME_SECRET') || !REALTIME_SECRET || !defined('BASE_URL') || !BASE_URL) {
        return;
    }

    $url = rtrim(BASE_URL, '/') . '/app/tasks/seller_unread_message_cron.php';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Realtime-Secret: ' . REALTIME_SECRET]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    // Skip Cloudflare for this internal self-call (Under Attack mode answers with 403).
    if (function_exists('lb_internal_curl_use_loopback')) {
        lb_internal_curl_use_loopback($ch, $url);
    }
    curl_exec($ch);
    $curlError = curl_errno($ch);
    curl_close($ch);

    if ($curlError === CURLE_COULDNT_CONNECT || $curlError === CURLE_SSL_CONNECT_ERROR) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Realtime-Secret: ' . REALTIME_SECRET]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_exec($ch);
        curl_close($ch);
    }
}

function run_discord_missed_message_cron(): void
{
    global $db;

    if (!defined('DS_BOT_TOKEN') || !DS_BOT_TOKEN) {
        return;
    }

    $rows = $db->run("
        SELECT *
        FROM `notifications`
        WHERE `type` = 'ds_msg_notif_booster'
          AND `is_fail` = 0
          AND `is_discord` = 0
          AND `created_at` <= (NOW() - INTERVAL " . (int)MISSED_MINUTES . " MINUTE)
        ORDER BY `id` ASC
        LIMIT 200
    ") ?: [];


    $handled = [];

    foreach ($rows as $n) {
        $data = json_decode($n['data'] ?? '', true);

        if (!is_array($data) || empty($data['order_id']) || empty($data['client_username'])) {
            $db->update('notifications', ['is_fail' => 1], ['id' => $n['id']]);
            continue;
        }

        $orderIdB64 = (string)$data['order_id'];
        $orderIdRaw = base64_decode($orderIdB64, true);
        $orderId    = (int)($orderIdRaw !== false ? $orderIdRaw : 0);

        $clientNameRaw = base64_decode((string)$data['client_username'], true);
        $clientName    = (string)($clientNameRaw !== false ? $clientNameRaw : 'Customer');

        $boosterId = (int)($n['recipient_id'] ?? 0);

        if ($orderId <= 0 || $boosterId <= 0) {
            $db->update('notifications', ['is_fail' => 1], ['id' => $n['id']]);
            continue;
        }

        $key = $boosterId . ':' . $orderId;
        if (isset($handled[$key])) continue;

        $order = db_get_row('orders', ['id' => $orderId]);
        $allowedStatuses = ['IN_PROGRESS', 'PAUSED'];
        if (!$order || !in_array(($order['status'] ?? ''), $allowedStatuses, true) || empty($order['booster_id'])) {
            mark_all_order_booster_notifications_fail($boosterId, $orderIdB64);
            $handled[$key] = true;
            continue;
        }

        $createdAtUnix = !empty($n['created_at']) ? (strtotime((string)$n['created_at']) ?: 0) : 0;

        $lastMsgTime = 0;
        $lastMsgBody = '';
        $status = missed_message_status($orderId, (int)MISSED_MINUTES, $lastMsgTime, $createdAtUnix, $lastMsgBody);

        write_debug_row($boosterId, $orderIdB64, 'n/a', null, true, [
            '_http_code' => 0,
            'notification_id' => (int)$n['id'],
            'status' => $status,
            'lastMsgTime' => $lastMsgTime,
            'created_at' => $n['created_at'] ?? null,
        ], 'decision');

        if ($status === 'NOT_READY') {
            continue;
        }

        if ($status === 'NO_LONGER_RELEVANT') {
            mark_all_order_booster_notifications_fail($boosterId, $orderIdB64);
            $handled[$key] = true;
            continue;
        }

        if (already_alerted_for_message($boosterId, $orderIdB64, $lastMsgTime)) {
            mark_all_order_booster_notifications_fail($boosterId, $orderIdB64);
            $handled[$key] = true;
            continue;
        }

        $booster = db_get_row('boosters', ['id' => $boosterId]);
        if (!$booster || empty($booster['discord_id'])) {
            mark_all_order_booster_notifications_fail($boosterId, $orderIdB64);
            $handled[$key] = true;
            continue;
        }

        $dmDebugOpen = [];
        $dmChannelId = discord_dm_channel_id(DS_BOT_TOKEN, (string)$booster['discord_id'], $dmDebugOpen);

        if (!$dmChannelId) {
            write_debug_row($boosterId, $orderIdB64, (string)$booster['discord_id'], null, false, $dmDebugOpen, 'open_dm_failed');
            continue;
        }

        $orderUrl = rtrim(BASE_URL, '/') . '/booster-area/order/' . $orderId;

        $orderTitle = 'Order #' . $orderId;
        if (function_exists('util_format_boost_overview')) {
            $orderForm = db_get_row('boost_forms', ['id' => $order['form_id'] ?? 0]);
            $orderOpts = db_get_row('order_options', ['order_id' => $orderId]);
            if ($orderForm || $orderOpts) {
                $overviewData = array_merge((array)$orderForm, (array)$orderOpts, $order);
                $overview = util_format_boost_overview($overviewData['game'] ?? '', $overviewData['type'] ?? '', $overviewData);
                if (trim((string)$overview) !== '') $orderTitle = $overview;
            }
        }

        $messageText = trim(strip_tags(html_entity_decode($lastMsgBody, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if ($messageText === '') $messageText = '[Image]';
        if (function_exists('mb_strlen') && mb_strlen($messageText, 'UTF-8') > 900) {
            $messageText = mb_substr($messageText, 0, 897, 'UTF-8') . '...';
        } elseif (strlen($messageText) > 900) {
            $messageText = substr($messageText, 0, 897) . '...';
        }

        $payload = [
            'embeds' => [[
                'title' => 'MISSING MESSAGE',
                'description' => "📩️ You have a missing message from **{$clientName}**.\nPlease check the order chat.",
                'fields' => [
                    ['name' => '👤 Customer', 'value' => $clientName, 'inline' => true],
                    ['name' => '🆔 Order ID', 'value' => '#' . $orderId, 'inline' => true],
                    ['name' => '🧾 Order', 'value' => $orderTitle, 'inline' => false],
                    ['name' => '💬 Message', 'value' => $messageText, 'inline' => false],
                ],
                'timestamp' => date('c'),
            ]],
            'components' => [[
                'type' => 1,
                'components' => [[
                    'type' => 2,
                    'style' => 5,
                    'label' => '🔎 View Order',
                    'url' => $orderUrl,
                ]]
            ]]
        ];

        $sendDebug = [];
        $ok = discord_send_message(DS_BOT_TOKEN, $dmChannelId, $payload, $sendDebug);

        if ($ok) {
            mark_all_order_booster_notifications_discord_sent($boosterId, $orderIdB64);
            write_missed_log_row($boosterId, $orderIdB64, (string)$data['client_username'], $lastMsgTime);
            write_debug_row($boosterId, $orderIdB64, (string)$booster['discord_id'], $dmChannelId, true, $sendDebug, 'sent_ok');
            $handled[$key] = true;
        } else {
            write_debug_row($boosterId, $orderIdB64, (string)$booster['discord_id'], $dmChannelId, false, $sendDebug, 'send_failed');
        }
    }
}

function missed_message_status(int $orderId, int $minutes, int &$lastMsgTime, int $fallbackNotificationUnix = 0, string &$lastMsgBody = ''): string
{
    $lastMsgTime = 0;
    $lastMsgBody = '';

    $chat = chat_load_messages((string)$orderId);
    if (empty($chat['messages']) || !is_array($chat['messages'])) {
        return 'NO_LONGER_RELEVANT';
    }

    $last = end($chat['messages']);
    if (!is_array($last)) return 'NO_LONGER_RELEVANT';

    if (($last['sender'] ?? '') !== 'client') return 'NO_LONGER_RELEVANT';
    if ((int)($last['seen'] ?? 0) === 1) return 'NO_LONGER_RELEVANT';

    $lastMsgBody = (string)($last['content'] ?? $last['raw'] ?? '');
    if ($lastMsgBody === '' && (($last['type'] ?? 'text') === 'image')) $lastMsgBody = '[Image]';

    $tRaw = $last['time'] ?? 0;
    $t = 0;

    if (is_numeric($tRaw)) {
        $t = (int)$tRaw;
        if ($t > 20000000000) $t = (int)floor($t / 1000);
    } elseif (is_string($tRaw)) {
        $parsed = strtotime($tRaw);
        if ($parsed !== false) $t = (int)$parsed;
    }

    if ($t <= 0 && $fallbackNotificationUnix > 0) $t = $fallbackNotificationUnix;
    if ($t <= 0) return 'NOT_READY';

    $lastMsgTime = $t;

    if ((time() - $t) < ($minutes * 60)) return 'NOT_READY';
    return 'MISSED';
}

function already_alerted_for_message(int $boosterId, string $orderIdB64, int $msgTime): bool
{
    global $db;

    $orderIdB64 = addslashes($orderIdB64);
    $msgTime = (int)$msgTime;

    $rows = $db->run("
        SELECT `id`
        FROM `notifications`
        WHERE `type` = '" . MISSED_LOG_TYPE . "'
          AND `recipient_id` = " . (int)$boosterId . "
          AND `data` LIKE '%\"order_id\":\"{$orderIdB64}\"%'
          AND `data` LIKE '%\"msg_time\":{$msgTime}%'
        LIMIT 1
    ");

    return !empty($rows);
}

function mark_all_order_booster_notifications_discord_sent(int $boosterId, string $orderIdB64): void
{
    global $db;
    $orderIdB64 = addslashes($orderIdB64);

    $db->run("
        UPDATE `notifications`
        SET `is_discord` = 1,
            `is_sent` = 1,
            `sent_at` = NOW()
        WHERE `type` = 'ds_msg_notif_booster'
          AND `recipient_id` = " . (int)$boosterId . "
          AND `is_fail` = 0
          AND `is_discord` = 0
          AND `data` LIKE '%\"order_id\":\"{$orderIdB64}\"%'
    ");
}

function mark_all_order_booster_notifications_fail(int $boosterId, string $orderIdB64): void
{
    global $db;
    $orderIdB64 = addslashes($orderIdB64);

    $db->run("
        UPDATE `notifications`
        SET `is_fail` = 1
        WHERE `type` = 'ds_msg_notif_booster'
          AND `recipient_id` = " . (int)$boosterId . "
          AND `is_fail` = 0
          AND `is_discord` = 0
          AND `data` LIKE '%\"order_id\":\"{$orderIdB64}\"%'
    ");
}

function write_missed_log_row(int $boosterId, string $orderIdB64, string $clientNameB64, int $msgTime): void
{
    $payload = json_encode([
        'order_id' => $orderIdB64,
        'client_username' => $clientNameB64,
        'msg_time' => $msgTime,
    ], JSON_UNESCAPED_SLASHES);

    if (function_exists('db_insert_row')) {
        db_insert_row('notifications', [
            'type' => MISSED_LOG_TYPE,
            'recipient' => 'booster',
            'recipient_id' => $boosterId,
            'data' => $payload,
            'is_sent' => 1,
            'is_fail' => 0,
            'is_discord' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
        return;
    }

    global $db;
    $db->insert('notifications', [
        'type' => MISSED_LOG_TYPE,
        'recipient' => 'booster',
        'recipient_id' => $boosterId,
        'data' => $payload,
        'is_sent' => 1,
        'is_fail' => 0,
        'is_discord' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'sent_at' => date('Y-m-d H:i:s'),
    ]);
}

function write_debug_row(int $boosterId, string $orderIdB64, string $discordId, ?string $dmChannelId, bool $ok, array $debug, string $stage): void
{
    if (!DEBUG_ALWAYS) return;

    $payload = json_encode([
        'order_id' => $orderIdB64,
        'discord_id' => $discordId,
        'dm_channel_id' => $dmChannelId,
        'ok' => $ok ? 1 : 0,
        'stage' => $stage,
        'http_code' => (int)($debug['_http_code'] ?? 0),
        'debug' => $debug,
    ], JSON_UNESCAPED_SLASHES);

    if (function_exists('db_insert_row')) {
        db_insert_row('notifications', [
            'type' => DEBUG_TYPE,
            'recipient' => 'booster',
            'recipient_id' => $boosterId,
            'data' => $payload,
            'is_sent' => 1,
            'is_fail' => 0,
            'is_discord' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
        return;
    }

    global $db;
    $db->insert('notifications', [
        'type' => DEBUG_TYPE,
        'recipient' => 'booster',
        'recipient_id' => $boosterId,
        'data' => $payload,
        'is_sent' => 1,
        'is_fail' => 0,
        'is_discord' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'sent_at' => date('Y-m-d H:i:s'),
    ]);
}

function discord_dm_channel_id(string $botToken, string $discordUserId, array &$debugOut = []): ?string
{
    $res = discord_api('POST', 'https://discord.com/api/v10/users/@me/channels', $botToken, [
        'recipient_id' => $discordUserId,
    ]);

    $debugOut = is_array($res) ? $res : ['raw' => $res];
    $http = (int)($debugOut['_http_code'] ?? 0);

    if ($http >= 200 && $http < 300 && !empty($debugOut['id'])) {
        return (string)$debugOut['id'];
    }
    return null;
}

function discord_send_message(string $botToken, string $channelId, array $payload, array &$debugOut = []): bool
{
    $res = discord_api('POST', "https://discord.com/api/v10/channels/{$channelId}/messages", $botToken, $payload);

    $debugOut = is_array($res) ? $res : ['raw' => $res];
    $http = (int)($debugOut['_http_code'] ?? 0);

    return ($http >= 200 && $http < 300 && !empty($debugOut['id']));
}

function discord_api(string $method, string $url, string $botToken, array $payload)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bot ' . $botToken,
        'Content-Type: application/json',
        'User-Agent: LoLBoostGG (https://lolboost.gg, 1.0)',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));

    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return ['_http_code' => $code, 'curl_error' => $err];
    }

    $decoded = json_decode($body, true);
    if ($decoded === null) {
        return ['_http_code' => $code, 'raw' => $body];
    }

    $decoded['_http_code'] = $code;
    return $decoded;
}
