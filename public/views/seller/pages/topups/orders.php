<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Top Up Orders | Seller Area']]) ?>
<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
require_once dirname(__DIR__) . '/_orders_shared.php';

$effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$orders = is_array($orders ?? null) ? $orders : [];

$rows = [];
foreach ($orders as $o) {
    $id     = (int)($o['id'] ?? 0);
    $qty    = max(1, (int)($o['quantity'] ?? 1));
    $price  = sol_money($o['price'] ?? $o['total_price'] ?? 0);
    $payout = $o['seller_payout'] ?? $o['seller_amount'] ?? $o['earnings'] ?? null;
    $game   = (string)(($o['game_name'] ?? '') ?: ($o['db_game_name'] ?? 'Game'));
    $offer  = (string)($o['offer_title'] ?? $o['listing_offer_title'] ?? 'Top Up');
    $amount = trim((string)($o['offer_amount'] ?? $o['listing_offer_amount'] ?? '') . ' ' . (string)($o['offer_unit'] ?? $o['listing_offer_unit'] ?? ''));
    $region = (string)($o['region'] ?? $o['listing_region'] ?? 'Global');

    $waitValue = (int)($o['waiting_time_value'] ?? $o['waiting_time_amount'] ?? 0);
    $waiting   = $waitValue > 0 ? $waitValue . ' ' . (string)($o['waiting_time_unit'] ?? 'minutes') : '';

    $rows[] = [
        'id'        => $id,
        'url'       => BASE_URL . '/seller-area/top-up-order/' . $id,
        'cover'     => (string)($o['image'] ?? $o['game_icon'] ?? ''),
        'name'      => $offer,
        'sub'       => trim($amount) !== '' ? $amount : $game,
        'game_name' => $game,
        'game_icon' => (string)($o['game_icon'] ?? ''),
        'meta'      => trim($region . ($waiting !== '' ? ' · ' . $waiting : '')),
        'price'     => $price,
        'earnings'  => $payout !== null ? sol_money($payout) : $price * (1 - $effective_fee / 100),
        'stock'     => $qty,
        'sold'      => $qty,
        'status'    => sol_status($o['status'] ?? 'sold'),
        'buyer'     => (string)($o['client_username'] ?? 'Unknown buyer'),
        'buyer_icon' => sol_client_icon($o['buyer_icon'] ?? $o['client_icon'] ?? $o['client_avatar'] ?? ''),
        'created'   => sol_relative($o['created_at'] ?? ''),
    ];
}

$rows = sol_visible_rows($rows);

$olCfg = [
    'icon'     => 'fa-duotone fa-coins',
    'title'    => 'Top Up Orders',
    'subtitle' => count($rows) . ' sold top up' . (count($rows) !== 1 ? 's' : '') . ' total',
    'link'     => ['href' => BASE_URL . '/seller-area/top-ups', 'label' => 'My Top Ups', 'icon' => 'fa-solid fa-store'],
    'search_placeholder' => 'Search top ups...',
    'empty'    => 'No sold top up orders yet.',
    'columns'  => ['item' => 'Offer', 'game_data' => 'Region & Delivery'],
    'fallback_icon' => 'fa-solid fa-coins',
    'filters'  => sol_filters($rows),
    'rows'     => $rows,
];
include dirname(__DIR__) . '/_orders_list.php';
