<?php

date_default_timezone_set('Europe/Berlin');

require '/home/u319049446/domains/lolboost.gg/public_html/vendor/autoload.php';

require '/home/u319049446/domains/lolboost.gg/public_html/app/core/config.php';
require '/home/u319049446/domains/lolboost.gg/public_html/app/core/functions.php';
require '/home/u319049446/domains/lolboost.gg/public_html/app/core/view.php';

$currentTimestamp = time();
$targetTimestamp = $currentTimestamp - (48 * 60 * 60); // Subtract 48 hours

$unpaid_orders = db_get_rows('orders', [
    'status' => 'UNPAID',
    'created_at' => ['lt' => date('Y-m-d H:i:s', $targetTimestamp)]
]);

if (!empty($unpaid_orders)) {
    foreach ($unpaid_orders as $order) {
        db_delete_rows('orders', ['id' => $order['id']]);
        db_delete_rows('invoices', ['order_id' => $order['id']]);
        db_delete_rows('order_options', ['order_id' => $order['id']]);
    }
}

// E-Girl bookings live in their own table (egirl_orders) and share the invoices
// table via order_type = 'egirl_session'. Remove the ones that were never paid
// after 48h, exactly like the boost orders above.
$unpaid_egirl_orders = db_get_rows('egirl_orders', [
    'status' => 'UNPAID',
    'created_at' => ['lt' => date('Y-m-d H:i:s', $targetTimestamp)]
]);

if (!empty($unpaid_egirl_orders)) {
    foreach ($unpaid_egirl_orders as $order) {
        db_delete_rows('egirl_orders', ['id' => $order['id']]);
        db_delete_rows('invoices', ['order_id' => $order['id'], 'order_type' => 'egirl_session']);
    }
}
