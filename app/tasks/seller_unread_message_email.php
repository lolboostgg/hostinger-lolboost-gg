<?php
ignore_user_abort(true);
set_time_limit(60);

ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');

function lb_su_find_up(string $start, string $relative, int $depth = 8): ?string
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

$autoload = lb_su_find_up(__DIR__, 'vendor/autoload.php');
$config = lb_su_find_up(__DIR__, 'core/config.php');
$functions = lb_su_find_up(__DIR__, 'core/functions.php');
$view = lb_su_find_up(__DIR__, 'core/view.php');
if (!$autoload || !$config || !$functions || !$view) { http_response_code(500); exit; }
require $autoload;
require $config;
require $functions;
require $view;

function lb_seller_unread_send_email(array $seller, string $clientName, string $kindLabel, string $listingTitle, string $message, string $chatUrl): bool
{
    $email = trim((string)($seller['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

    $sellerName = trim((string)($seller['username'] ?? 'there')) ?: 'there';
    $safeMessage = $message !== '' ? $message : '[Image]';
    // Same rich Plates template + layout the client unread-message email already uses
    // (views/emails/body/seller_unread_message.php), instead of a hand-rolled HTML string.
    $templateData = [
        'preheader' => "You missed a chat with your client — check the website and reply before they start wondering where you went!",
        'username' => $sellerName,
        'client_name' => $clientName,
        'kind_label' => $kindLabel,
        'listing_title' => $listingTitle,
        'message' => $safeMessage,
        'chat_url' => $chatUrl,
    ];
    $body = function_exists('view_file_store')
        ? view_file_store('emails/body/seller_unread_message', ['data' => $templateData])
        : '';
    if ($body === '') return false;

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = SMTP_HOST;
        $mail->Port = (int)SMTP_PORT;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = $mail->Port === 465
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout = 20;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom(SMTP_USER, 'LoLBoost.gg');
        $mail->addReplyTo('support@lolboost.gg', 'LoLBoost.gg Support');
        $mail->addAddress($email, $sellerName);
        $mail->isHTML(true);
        $mail->Subject = '🚨 You missed a chat with your client!';
        $mail->Body = $body;
        $mail->AltBody = "Hi {$sellerName},\n\n{$clientName} sent you an unread message about {$listingTitle}:\n{$message}\n\nOpen chat: {$chatUrl}";
        return $mail->send();
    } catch (\Throwable $e) {
        error_log('Seller unread direct email failed for seller #' . (int)($seller['id'] ?? 0) . ': ' . $e->getMessage());
        return false;
    }
}

$provided = trim((string)($_SERVER['HTTP_X_REALTIME_SECRET'] ?? ''));
$expected = defined('REALTIME_SECRET') ? trim((string)REALTIME_SECRET) : '';
if ($expected === '' || !hash_equals($expected, $provided)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'reason' => 'unauthorized']);
    exit;
}

$payload = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($payload)) $payload = $_POST;
$sellerId = (int)($payload['seller_id'] ?? 0);
$clientId = (int)($payload['client_id'] ?? 0);
$refType = trim((string)($payload['ref_type'] ?? ''));
$refId = (int)($payload['ref_id'] ?? 0);
if ($sellerId <= 0 || $clientId <= 0 || $refId <= 0 || !in_array($refType, ['account', 'item_purchase', 'topup_purchase'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'reason' => 'invalid_payload']);
    exit;
}

global $db;
$record = null;
$chatSeed = '';
$chatUrl = '';
$listingTitle = '';
$kindLabel = '';
if ($refType === 'account') {
    $record = $db->row('SELECT id,seller_id,client_id,title FROM selling_accounts WHERE id=? AND seller_id=? LIMIT 1', $refId, $sellerId);
    $chatSeed = 'selling_account_' . $refId;
    $chatUrl = rtrim(BASE_URL, '/') . '/seller-area/chat/account/' . $refId;
    $listingTitle = (string)($record['title'] ?? ('Account #' . $refId));
    $kindLabel = 'Account';
} elseif ($refType === 'item_purchase') {
    $record = $db->row('SELECT p.id,p.seller_id,p.client_id,COALESCE(i.title,p.item_title,CONCAT("Item Order #",p.id)) title FROM selling_item_purchases p LEFT JOIN selling_items i ON i.id=p.item_id WHERE p.id=? AND p.seller_id=? LIMIT 1', $refId, $sellerId);
    $chatSeed = 'selling_item_purchase_' . $refId;
    $chatUrl = rtrim(BASE_URL, '/') . '/seller-area/item-order/' . $refId;
    $listingTitle = (string)($record['title'] ?? ('Item Order #' . $refId));
    $kindLabel = 'Item order';
} else {
    $record = $db->row('SELECT p.id,p.seller_id,p.client_id,COALESCE(p.offer_title,t.offer_title,CONCAT("Top Up Order #",p.id)) title FROM selling_topup_purchases p LEFT JOIN selling_topups t ON t.id=p.topup_id WHERE p.id=? AND p.seller_id=? LIMIT 1', $refId, $sellerId);
    $chatSeed = 'selling_topup_purchase_' . $refId;
    $chatUrl = rtrim(BASE_URL, '/') . '/seller-area/top-up-order/' . $refId;
    $listingTitle = (string)($record['title'] ?? ('Top Up Order #' . $refId));
    $kindLabel = 'Top Up order';
}
if (!$record || ((int)($record['client_id'] ?? 0) > 0 && (int)$record['client_id'] !== $clientId)) {
    echo json_encode(['ok' => true, 'queued' => false, 'reason' => 'not_found']);
    exit;
}

/* Import newly appended rollout JSON messages before checking the DB thread. */
$base = defined('SYS_PATH') ? rtrim((string)SYS_PATH, '/\\') : dirname(__DIR__, 3);
$chatPath = $base . '/public/uploads/private/chat/selling_' . sha1($chatSeed) . '.json';

/* DB is authoritative after the idempotent archive sync. */
$latest = null;
$jsonState = null;
try {
    if (function_exists('lb_legacy_chat_ensure_schema')) lb_legacy_chat_ensure_schema();
    if (function_exists('lb_legacy_chat_open')) {
        $opened = lb_legacy_chat_open(
            'seller:' . $refType . ':' . $refId,
            $refType,
            $refId,
            ['seller_id' => $sellerId, 'client_id' => $clientId],
            is_file($chatPath) ? $chatPath : null
        );
        $thread = $opened['thread'] ?? null;
    } else {
        $thread = $db->row('SELECT id FROM legacy_chat_threads WHERE chat_key=? LIMIT 1', 'seller:' . $refType . ':' . $refId);
    }
    if (!empty($thread['id'])) {
        $latest = $db->row("SELECT id,body,message_type,created_at,seen_by_seller,sender_type FROM legacy_chat_messages WHERE thread_id=? AND deleted=0 ORDER BY created_at DESC,id DESC LIMIT 1", (int)$thread['id']);
    }
} catch (\Throwable $e) {
    $latest = null;
}

if ($latest) {
    if (($latest['sender_type'] ?? '') !== 'client') { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'seller_replied']); exit; }
    // legacy_chat_messages timestamps are canonical UTC values.
    $latestTs = strtotime((string)$latest['created_at'] . ' UTC') ?: 0;
    $latestMessage = trim(strip_tags(html_entity_decode((string)($latest['body'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if ($latestMessage === '' && ($latest['message_type'] ?? '') === 'image') $latestMessage = '[Image]';
    $seen = (int)($latest['seen_by_seller'] ?? 0);
    $messageIdentity = 'db:' . (int)$latest['id'];
} else {
    if (!is_file($chatPath)) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'no_chat']); exit; }
    $data = json_decode((string)@file_get_contents($chatPath), true);
    $messages = is_array($data['messages'] ?? null) ? $data['messages'] : [];
    $latestIndex = null; $latestTs = 0; $latestMessage = ''; $seen = 0;
    foreach ($messages as $i => $m) {
        if (!is_array($m) || !empty($m['deleted'])) continue;
        $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['type'] ?? '')));
        if ($sender !== 'client') continue;
        $ts = is_numeric($m['time'] ?? null) ? (int)$m['time'] : 0;
        if ($ts > 20000000000) $ts = (int)floor($ts / 1000);
        if ($ts <= 0 && !empty($m['created_at'])) $ts = strtotime((string)$m['created_at']) ?: 0;
        if ($ts >= $latestTs) {
            $latestTs = $ts; $latestIndex = $i;
            $latestMessage = trim(strip_tags(html_entity_decode((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($latestMessage === '' && ($m['message_type'] ?? '') === 'image') $latestMessage = '[Image]';
            $seen = (int)($m['seen_by_seller'] ?? $m['is_read'] ?? $m['seen'] ?? 0);
        }
    }
    if ($latestIndex === null) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'no_message']); exit; }
    $messageIdentity = 'json:' . $latestTs . ':' . $latestIndex;
    $jsonState = [$chatPath, $data, $latestIndex];
}

if ($latestTs <= 0 || (time() - $latestTs) < 300) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'not_ready']); exit; }
if ((time() - $latestTs) > 86400) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'stale_message']); exit; }
if ($seen === 1) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'already_read']); exit; }

$signature = hash('sha256', $sellerId . '|' . $refType . '|' . $refId . '|' . $messageIdentity);
$storedSignature = base64_encode($signature);
$existing = $db->row("SELECT id FROM notifications WHERE type='seller_unread_message' AND recipient='seller' AND recipient_id=? AND data LIKE ? LIMIT 1", $sellerId, '%' . addcslashes($storedSignature, '%_\\') . '%');
if ($existing) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'duplicate']); exit; }

$seller = $db->row('SELECT id,username,email,discord_id FROM sellers WHERE id=? LIMIT 1', $sellerId);
$client = $db->row('SELECT id,username,email FROM clients WHERE id=? LIMIT 1', $clientId);
if (!$seller) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'seller_missing']); exit; }
if (function_exists('mb_strlen') && mb_strlen($latestMessage, 'UTF-8') > 500) $latestMessage = mb_substr($latestMessage, 0, 497, 'UTF-8') . '...';
elseif (strlen($latestMessage) > 500) $latestMessage = substr($latestMessage, 0, 497) . '...';

$hasEmail = filter_var((string)($seller['email'] ?? ''), FILTER_VALIDATE_EMAIL) !== false;
$clientName = trim((string)($client['username'] ?? $client['email'] ?? 'Client')) ?: 'Client';
$dmSent = false;
if (!empty($seller['discord_id']) && defined('DS_BOT_TOKEN') && DS_BOT_TOKEN && function_exists('getDMChannelId') && function_exists('sendEmbedDM')) {
    $channelId = getDMChannelId(DS_BOT_TOKEN, trim((string)$seller['discord_id']));
    if ($channelId) {
        $discordPayload = json_encode([
            'embeds' => [[
                'title' => 'MISSING MESSAGE',
                'description' => "📩 You have a missing message from **{$clientName}**.\nPlease check the order chat.",
                'color' => 0x2b2d31,
                'fields' => [
                    ['name' => '🆔 Order ID', 'value' => '#' . $refId, 'inline' => true],
                    ['name' => '🧾 Listing', 'value' => $listingTitle, 'inline' => true],
                    ['name' => '💬 Message', 'value' => $latestMessage !== '' ? $latestMessage : '[Image]', 'inline' => false],
                ],
                'footer' => ['text' => date('d.m.Y H:i')],
            ]],
            'components' => [[
                'type' => 1,
                'components' => [[
                    'type' => 2,
                    'style' => 5,
                    'label' => '🔎 View Order',
                    'url' => $chatUrl,
                ]],
            ]],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $dmSent = sendEmbedDM(DS_BOT_TOKEN, $channelId, $discordPayload) === true;
    }
}

$emailSent = $hasEmail
    ? lb_seller_unread_send_email($seller, $clientName, $kindLabel, $listingTitle, $latestMessage, $chatUrl)
    : false;
$sellerEmail = trim((string)($seller['email'] ?? ''));
$maskedEmail = $sellerEmail;
if (($at = strpos($sellerEmail, '@')) !== false && $at > 1) {
    $maskedEmail = substr($sellerEmail, 0, 1) . str_repeat('*', max(1, $at - 2)) . substr($sellerEmail, $at - 1);
}

$db->insert('notifications', [
    'type' => 'seller_unread_message', 'recipient' => 'seller', 'recipient_id' => $sellerId,
    'is_email' => $hasEmail ? 1 : 0, 'is_web' => 1, 'is_discord' => $dmSent ? 1 : 0,
    // A successful direct email needs no queue processing. On failure the
    // central notification sender retries the same email from this row.
    'is_sent' => (!$hasEmail || $emailSent) ? 1 : 0,
    'sent_at' => (!$hasEmail || $emailSent) ? date('Y-m-d H:i:s') : null,
    'data' => json_encode([
        'username' => base64_encode((string)($seller['username'] ?? 'there')),
        'client_name' => base64_encode((string)($client['username'] ?? $client['email'] ?? 'Client')),
        'kind_label' => base64_encode($kindLabel), 'listing_title' => base64_encode($listingTitle),
        'message' => base64_encode($latestMessage), 'chat_url' => base64_encode($chatUrl),
        'action_url' => base64_encode($chatUrl),
        'signature' => $storedSignature,
        'discord_dm_sent' => base64_encode($dmSent ? '1' : '0'),
        'email_sent' => base64_encode($emailSent ? '1' : '0'),
        'email_to' => base64_encode($sellerEmail),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
]);

if ($jsonState) {
    [$chatPath, $data, $latestIndex] = $jsonState;
    $data['messages'][$latestIndex]['seller_unread_email_at'] = date('Y-m-d H:i:s');
    @file_put_contents($chatPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
}
if (function_exists('lb_realtime_emit')) {
    lb_realtime_emit('notification_update', ['scope' => 'seller', 'seller_id' => $sellerId, 'ts' => time()], ['sellers']);
}
if ($hasEmail && !$emailSent && function_exists('trigger_notification_sender_async')) trigger_notification_sender_async();
echo json_encode(['ok'=>true,'queued'=>true,'email'=>$hasEmail,'email_sent'=>$emailSent,'email_to'=>$maskedEmail,'discord_dm'=>$dmSent]);
