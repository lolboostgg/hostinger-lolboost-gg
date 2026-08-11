<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Items - Admin Area | LoLBoost.gg', 'h1' => 'Seller Items', 'description' => 'Manage seller items.']]) ?>
<?php $activeTab = 'items'; include __DIR__ . '/_shared.php'; ?>
<?php
$items = is_array($items ?? null) ? $items : [];
$lstRows = [];
foreach ($items as $row) {
    $active = (int)($row['active'] ?? 1) === 1;
    $game = admin_seller_game_meta((string)($row['game'] ?? ''), (string)($row['db_game_name'] ?? ''), (string)($row['game_icon'] ?? ''));
    $lstRows[] = [
        'id' => (int)($row['id'] ?? 0),
        'title' => (string)($row['title'] ?? 'Item'),
        'subtitle' => (string)($row['slug'] ?? ''),
        'image' => admin_seller_asset_url(admin_seller_first_image($row['images'] ?? '')),
        'filter_key' => $game['key'],
        'filter_label' => $game['label'],
        'filter_icon' => admin_seller_asset_url($game['icon']),
        'price' => (int)($row['price'] ?? 0),
        'stock' => (int)($row['stock'] ?? 0),
        'sold' => (int)($row['sold_count'] ?? 0),
        'status' => $active ? 'listed' : 'unlisted',
        'status_label' => $active ? 'Listed' : 'Unlisted',
        'created' => (string)($row['created_at'] ?? ''),
        'toggle' => ['action' => 'admin_unlist_marketplace_listing', 'kind' => 'item', 'id' => (int)($row['id'] ?? 0), 'active' => $active ? 0 : 1],
    ];
}
$lstCfg = [
    'id' => 'items',
    'title' => 'Item',
    'icon' => 'fa-box-open',
    'filterLabel' => 'Game',
    'columns' => ['game', 'stock', 'sold'],
    'searchPlaceholder' => 'Search items…',
    'emptyText' => 'No seller items found.',
];
include __DIR__ . '/_listings_table.php';
