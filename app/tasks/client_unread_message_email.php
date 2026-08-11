<?php
ignore_user_abort(true);
set_time_limit(60);

ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');

function lb_cu_find_up(string $start, string $relative, int $depth = 8): ?string
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

$autoload = lb_cu_find_up(__DIR__, 'vendor/autoload.php');
$config = lb_cu_find_up(__DIR__, 'core/config.php');
$functions = lb_cu_find_up(__DIR__, 'core/functions.php');
$view = lb_cu_find_up(__DIR__, 'core/view.php');
if (!$autoload || !$config || !$functions || !$view) { http_response_code(500); exit; }
require $autoload;
require $config;
require $functions;
require $view;

function lb_client_unread_send_email(array $client, string $sellerName, string $kind, string $title, string $message, string $url): bool
{
    $email = trim((string)($client['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    $escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $clientName = trim((string)($client['username'] ?? 'there')) ?: 'there';
    $templateData = [
        'preheader' => "You missed a chat with the seller — head back to the website to catch up!",
        'username' => $clientName,
        'seller_name' => $sellerName,
        'kind_label' => $kind,
        'listing_title' => $title,
        'message' => $message !== '' ? $message : '[Image]',
        'chat_url' => $url,
    ];
    $body = function_exists('view_file_store')
        ? view_file_store('emails/body/client_unread_message', ['data' => $templateData])
        : '';
    if ($body === '') return false;
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP(); $mail->SMTPAuth = true; $mail->Host = SMTP_HOST; $mail->Port = (int)SMTP_PORT;
        $mail->Username = SMTP_USER; $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = $mail->Port === 465 ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout = 20; $mail->CharSet = 'UTF-8'; $mail->Encoding = 'base64';
        $mail->setFrom(SMTP_USER, 'LoLBoost.gg'); $mail->addReplyTo('support@lolboost.gg', 'LoLBoost.gg Support');
        $mail->addAddress($email, $clientName); $mail->isHTML(true); $mail->Subject = '🚨 You missed a chat with the seller!';
        $mail->Body = $body;
        $mail->AltBody = "Hi {$clientName},\n\n{$sellerName} sent you a message about {$title}:\n{$message}\n\nOpen chat: {$url}";
        return $mail->send();
    } catch (\Throwable $e) {
        error_log('Client unread direct email failed for client #' . (int)($client['id'] ?? 0) . ': ' . $e->getMessage());
        return false;
    }
}

$provided = trim((string)($_SERVER['HTTP_X_REALTIME_SECRET'] ?? ''));
$expected = defined('REALTIME_SECRET') ? trim((string)REALTIME_SECRET) : '';
if ($expected === '' || !hash_equals($expected, $provided)) { http_response_code(401); exit; }
$payload = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($payload)) $payload = $_POST;
$clientId = (int)($payload['client_id'] ?? 0);
$sellerId = (int)($payload['seller_id'] ?? 0);
$refType = trim((string)($payload['ref_type'] ?? ''));
$refId = (int)($payload['ref_id'] ?? 0);
if ($clientId <= 0 || $sellerId <= 0 || $refId <= 0 || !in_array($refType, ['account', 'item_purchase', 'topup_purchase'], true)) { http_response_code(400); exit; }

global $db;
$record = null; $seed = ''; $url = ''; $title = ''; $kind = '';
if ($refType === 'account') {
    $record = $db->row('SELECT id,seller_id,client_id,title FROM selling_accounts WHERE id=? AND client_id=? LIMIT 1', $refId, $clientId);
    $seed = 'selling_account_' . $refId; $url = rtrim(BASE_URL, '/') . '/client-area/account/' . $refId;
    $title = (string)($record['title'] ?? ('Account #' . $refId)); $kind = 'Account';
} elseif ($refType === 'item_purchase') {
    $record = $db->row('SELECT p.id,p.seller_id,p.client_id,COALESCE(i.title,CONCAT("Item Order #",p.id)) title FROM selling_item_purchases p LEFT JOIN selling_items i ON i.id=p.item_id WHERE p.id=? AND p.client_id=? LIMIT 1', $refId, $clientId);
    $seed = 'selling_item_purchase_' . $refId; $url = rtrim(BASE_URL, '/') . '/client-area/item-order/' . $refId;
    $title = (string)($record['title'] ?? ('Item Order #' . $refId)); $kind = 'Item order';
} else {
    $record = $db->row('SELECT p.id,p.seller_id,p.client_id,COALESCE(p.offer_title,t.offer_title,CONCAT("Top Up Order #",p.id)) title FROM selling_topup_purchases p LEFT JOIN selling_topups t ON t.id=p.topup_id WHERE p.id=? AND p.client_id=? LIMIT 1', $refId, $clientId);
    $seed = 'selling_topup_purchase_' . $refId; $url = rtrim(BASE_URL, '/') . '/client-area/top-up-order/' . $refId;
    $title = (string)($record['title'] ?? ('Top Up Order #' . $refId)); $kind = 'Top Up order';
}
if (!$record || (int)($record['seller_id'] ?? 0) !== $sellerId) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'not_found']); exit; }
$url = rtrim(BASE_URL, '/') . '/profile/chat#conv=' . rawurlencode($refType . ':' . $refId);

$latest = null; $jsonState = null;
try {
    if (function_exists('lb_legacy_chat_ensure_schema')) lb_legacy_chat_ensure_schema();
    $thread = $db->row('SELECT id FROM legacy_chat_threads WHERE chat_key=? LIMIT 1', 'seller:' . $refType . ':' . $refId);
    if (!empty($thread['id'])) {
        $latest = $db->row("SELECT id,body,message_type,created_at,seen_by_client,sender_type FROM legacy_chat_messages WHERE thread_id=? AND deleted=0 ORDER BY created_at DESC,id DESC LIMIT 1", (int)$thread['id']);
    }
} catch (\Throwable $e) { $latest = null; }

if ($latest) {
    if (!in_array(($latest['sender_type'] ?? ''), ['seller','admin'], true)) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'client_replied']); exit; }
    $latestTs = strtotime((string)$latest['created_at'] . ' UTC') ?: 0;
    $text = trim(strip_tags(html_entity_decode((string)($latest['body'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if ($text === '' && ($latest['message_type'] ?? '') === 'image') $text = '[Image]';
    $seen = (int)($latest['seen_by_client'] ?? 0);
    $messageIdentity = 'db:' . (int)$latest['id'];
} else {
    $base = defined('SYS_PATH') ? rtrim((string)SYS_PATH, '/\\') : dirname(__DIR__, 3);
    $path = $base . '/public/uploads/private/chat/selling_' . sha1($seed) . '.json';
    if (!is_file($path)) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'no_chat']); exit; }
    $data = json_decode((string)@file_get_contents($path), true);
    $messages = is_array($data['messages'] ?? null) ? $data['messages'] : [];
    $idx = null; $latestTs = 0; $text = ''; $seen = 0;
    foreach ($messages as $i => $m) {
        if (!is_array($m) || !empty($m['deleted'])) continue;
        $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['type'] ?? '')));
        if (!in_array($sender, ['seller', 'admin'], true)) continue;
        $ts = is_numeric($m['time'] ?? null) ? (int)$m['time'] : 0;
        if ($ts > 20000000000) $ts = (int)floor($ts / 1000);
        if ($ts <= 0 && !empty($m['created_at'])) $ts = strtotime((string)$m['created_at']) ?: 0;
        if ($ts >= $latestTs) {
            $latestTs = $ts; $idx = $i;
            $text = trim(strip_tags(html_entity_decode((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($text === '' && ($m['message_type'] ?? '') === 'image') $text = '[Image]';
            $seen = (int)($m['seen_by_client'] ?? $m['is_read'] ?? $m['seen'] ?? 0);
        }
    }
    if ($idx === null) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'no_message']); exit; }
    $messageIdentity = 'json:' . $latestTs . ':' . $idx;
    $jsonState = [$path, $data, $idx];
}

if ($latestTs <= 0 || (time() - $latestTs) < 300) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'not_ready']); exit; }
if ((time() - $latestTs) > 86400) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'stale_message']); exit; }
if ($seen === 1) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'already_read']); exit; }
$client = $db->row('SELECT id,username,email FROM clients WHERE id=? LIMIT 1', $clientId);
$seller = $db->row('SELECT id,username,email FROM sellers WHERE id=? LIMIT 1', $sellerId);
if (!$client || !filter_var((string)($client['email'] ?? ''), FILTER_VALIDATE_EMAIL)) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'no_email']); exit; }

$sig = hash('sha256', $clientId . '|' . $refType . '|' . $refId . '|' . $messageIdentity);
$storedSignature = base64_encode($sig);
$existing = $db->row("SELECT id FROM notifications WHERE type='client_unread_message' AND recipient='client' AND recipient_id=? AND data LIKE ? LIMIT 1", $clientId, '%' . addcslashes($storedSignature, '%_\\') . '%');
if ($existing) { echo json_encode(['ok'=>true,'queued'=>false,'reason'=>'duplicate']); exit; }
if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > 500) $text = mb_substr($text, 0, 497, 'UTF-8') . '...';
elseif (strlen($text) > 500) $text = substr($text, 0, 497) . '...';

$sellerName = trim((string)($seller['username'] ?? 'Seller')) ?: 'Seller';
$emailSent = lb_client_unread_send_email($client, $sellerName, $kind, $title, $text, $url);

$db->insert('notifications', [
    'type'=>'client_unread_message', 'recipient'=>'client', 'recipient_id'=>$clientId,
    'is_email'=>1, 'is_web'=>0, 'is_discord'=>0,
    'is_sent'=>$emailSent ? 1 : 0,
    'sent_at'=>$emailSent ? date('Y-m-d H:i:s') : null,
    'data'=>json_encode([
        'username'=>base64_encode((string)($client['username'] ?? 'there')),
        'seller_name'=>base64_encode((string)($seller['username'] ?? 'Seller')),
        'kind_label'=>base64_encode($kind), 'listing_title'=>base64_encode($title),
        'message'=>base64_encode($text), 'chat_url'=>base64_encode($url),
        'signature'=>$storedSignature, 'email_sent'=>base64_encode($emailSent ? '1' : '0'),
        'email_to'=>base64_encode((string)$client['email']),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
]);
if ($jsonState) {
    [$path, $data, $idx] = $jsonState;
    $data['messages'][$idx]['client_unread_email_at'] = date('Y-m-d H:i:s');
    @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
}
if (!$emailSent && function_exists('trigger_notification_sender_async')) trigger_notification_sender_async();
echo json_encode(['ok'=>true,'queued'=>true,'email'=>true,'email_sent'=>$emailSent]);
