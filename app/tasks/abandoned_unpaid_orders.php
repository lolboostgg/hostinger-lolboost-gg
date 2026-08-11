<?php

date_default_timezone_set('Europe/Berlin');

require '/home/u319049446/domains/lolboost.gg/public_html/vendor/autoload.php';
require '/home/u319049446/domains/lolboost.gg/public_html/app/core/config.php';
require '/home/u319049446/domains/lolboost.gg/public_html/app/core/functions.php';
require '/home/u319049446/domains/lolboost.gg/public_html/app/core/view.php';


$cutoffTs = time() - (24 * 60 * 60);
$cutoff = date('Y-m-d H:i:s', $cutoffTs);


// 1) Get unpaid orders older than 24h
$orders = db_get_rows('orders', [
    'status' => 'UNPAID',
    'created_at' => ['lt' => $cutoff],
]);

if (empty($orders)) {
    echo "Processed: 0\n";
    exit;
}

$processed = 0;

foreach ($orders as $o) {
    $orderId = (int)($o['id'] ?? 0);
    if ($orderId <= 0) continue;

    // 2) invoice for that order
    $invoice = db_get_row('invoices', [
        'order_id' => $orderId,
        'order_type' => 'order',
    ], true);

    if (empty($invoice) || empty($invoice['id'])) {
        continue;
    }

    // already emailed?
    if (!empty($invoice['abandoned_email_sent_at'])) {
        continue;
    }

    $invoiceId = (int)$invoice['id'];
    $clientId  = (int)($invoice['client_id'] ?? 0);
    $uuid      = (string)($invoice['uuid'] ?? '');

    if ($clientId <= 0 || $uuid === '') {
        // mark as emailed to avoid infinite loop
        db_update_row('invoices', ['id' => $invoiceId], [
            'abandoned_email_sent_at' => date('Y-m-d H:i:s'),
        ]);
        continue;
    }

    // client email exists?
    $client = db_get_row('clients', ['id' => $clientId], true);
    if (empty($client) || empty($client['email'])) {
        db_update_row('invoices', ['id' => $invoiceId], [
            'abandoned_email_sent_at' => date('Y-m-d H:i:s'),
        ]);
        continue;
    }

    // token + expiry (match your 48h order_cleaner)
    $token = (string)($invoice['abandoned_bonus_token'] ?? '');
    if ($token === '') {
        $token = bin2hex(random_bytes(16));
    }

    $expires = (string)($invoice['abandoned_discount_expires_at'] ?? '');
    if ($expires === '') {
        $expires = date('Y-m-d H:i:s', time() + (48 * 60 * 60));
    }

    // mark invoice + store token/expiry
    db_update_row('invoices', ['id' => $invoiceId], [
        'abandoned_email_sent_at' => date('Y-m-d H:i:s'),
        'abandoned_bonus_token' => $token,
        'abandoned_discount_expires_at' => $expires,
    ]);

    // create notification
    db_add_row('notifications', [
        'type' => 'abandoned_unpaid_order_reminder',
        'data' => json_encode([
            'invoice_uuid' => base64_encode($uuid),
            'ab_token' => base64_encode($token),
            'discount_percent' => base64_encode('5'),
            'expires_at' => base64_encode($expires),
            'order_id' => base64_encode((string)$orderId),
        ]),
        'recipient' => 'client',
        'recipient_id' => $clientId,
        'is_seen' => 0,
        'is_sent' => 0,
        'is_web' => 0,
        'is_email' => 1,
        'is_discord' => 0,
        'is_fail' => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    $processed++;
}

echo "Processed: {$processed}\n";