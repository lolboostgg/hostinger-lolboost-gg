<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Top Ups - Admin Area | LoLBoost.gg', 'h1' => 'Seller Top Ups', 'description' => 'Manage seller top ups.']]) ?>
<?php $activeTab = 'topups'; include __DIR__ . '/_shared.php'; ?>
<?php
$topups = is_array($topups ?? null) ? $topups : [];
$lstRows = [];
foreach ($topups as $row) {
    $active = (int)($row['active'] ?? 1) === 1;
    $game = admin_seller_game_meta(
        (string)($row['game_slug'] ?? $row['game'] ?? ''),
        (string)(($row['db_game_name'] ?? '') ?: ($row['game_name'] ?? '')),
        (string)($row['game_icon'] ?? '')
    );
    $amount = trim((string)($row['offer_amount'] ?? '') . ' ' . (string)($row['offer_unit'] ?? ''));
    $lstRows[] = [
        'id' => (int)($row['id'] ?? 0),
        'title' => (string)($row['offer_title'] ?? 'Top Up'),
        'subtitle' => $amount,
        'image' => admin_seller_asset_url($row['image'] ?? ''),
        'filter_key' => $game['key'],
        'filter_label' => $game['label'],
        'filter_icon' => admin_seller_asset_url($game['icon']),
        'region' => (string)($row['region'] ?? 'Global'),
        'price' => (int)($row['price'] ?? 0),
        'stock' => (int)($row['stock'] ?? 0),
        'sold' => (int)($row['sold_count'] ?? 0),
        'status' => $active ? 'listed' : 'unlisted',
        'status_label' => $active ? 'Listed' : 'Unlisted',
        'created' => (string)($row['created_at'] ?? ''),
        'toggle' => ['action' => 'admin_unlist_marketplace_listing', 'kind' => 'topup', 'id' => (int)($row['id'] ?? 0), 'active' => $active ? 0 : 1],
    ];
}
$lstCfg = [
    'id' => 'topups',
    'title' => 'Top Up',
    'icon' => 'fa-coins',
    'filterLabel' => 'Game',
    'columns' => ['game', 'region', 'stock', 'sold'],
    'searchPlaceholder' => 'Search top ups…',
    'emptyText' => 'No seller top ups found.',
];
include __DIR__ . '/_listings_table.php';
