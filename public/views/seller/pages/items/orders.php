<?php echo $this->layout('seller/layouts/main', ['meta' => ['title' => 'Item Orders | LoLBoost.gg']]); ?>
<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
require_once dirname(__DIR__) . '/_orders_shared.php';

$effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$orders = is_array($orders ?? null) ? $orders : [];

$rows = [];
foreach ($orders as $o) {
    $id     = (int)($o['id'] ?? 0);
    $qty    = max(1, (int)($o['quantity'] ?? $o['qty'] ?? 1));
    $total  = sol_money($o['price'] ?? $o['total_price'] ?? $o['order_total'] ?? 0);
    $payout = $o['seller_payout'] ?? $o['seller_amount'] ?? $o['payout'] ?? $o['earnings'] ?? null;
    $server = (string)($o['server'] ?? '');
    $type   = (string)($o['type'] ?? 'Item');
    $game   = (string)($o['game_name'] ?? '');
    if ($game === '') $game = ucwords(str_replace('-', ' ', (string)($o['game_slug'] ?? $o['game'] ?? 'Game')));
    $images = json_decode((string)($o['images'] ?? '[]'), true);
    if (!is_array($images)) $images = [];

    $rows[] = [
        'id'        => $id,
        'url'       => BASE_URL . '/seller-area/item-order/' . $id,
        'cover'     => $images[0] ?? (ASSET_URL . '/public/uploads/icons/default2.png'),
        'name'      => (string)($o['item_title'] ?? 'Untitled Item'),
        'sub'       => $game . ' · ' . ($server !== '' ? $server : 'Global'),
        'game_name' => $game,
        'game_icon' => (string)($o['game_icon'] ?? ''),
        'meta'      => ucwords(str_replace(['_', '-'], ' ', $type)) . ($server !== '' ? ' · ' . strtoupper($server) : ''),
        'price'     => $total,
        'earnings'  => $payout !== null ? sol_money($payout) : $total * (1 - $effective_fee / 100),
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
    'icon'     => 'fa-duotone fa-gift',
    'title'    => 'Item Orders',
    'subtitle' => count($rows) . ' sold item' . (count($rows) !== 1 ? 's' : '') . ' total',
    'link'     => ['href' => BASE_URL . '/seller-area/items', 'label' => 'My Items', 'icon' => 'fa-solid fa-store'],
    'search_placeholder' => 'Search items...',
    'empty'    => 'No sold item orders yet.',
    'columns'  => ['item' => 'Item'],
    'fallback_icon' => 'fa-solid fa-gift',
    'filters'  => sol_filters($rows),
    'rows'     => $rows,
];
include dirname(__DIR__) . '/_orders_list.php';
