<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Digital Goods - Admin Area | LoLBoost.gg', 'h1' => 'Seller Digital Goods', 'description' => 'Manage seller digital goods.']]) ?>
<?php $activeTab = 'digital-goods'; include __DIR__ . '/_shared.php'; ?>
<?php
$digitalGoods = is_array($digitalGoods ?? null) ? $digitalGoods : [];
$lstRows = [];
foreach ($digitalGoods as $row) {
    $active = (int)($row['active'] ?? 1) === 1;
    $category = trim((string)($row['category_name'] ?? '')) ?: 'Other';
    $image = admin_seller_first_image($row['images'] ?? '');
    if ($image === '') $image = (string)($row['brand_icon'] ?? '');
    $lstRows[] = [
        'id' => (int)($row['id'] ?? 0),
        'title' => (string)($row['title'] ?? 'Digital Good'),
        'subtitle' => (string)($row['slug'] ?? ''),
        'image' => admin_seller_asset_url($image),
        'filter_key' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $category)),
        'filter_label' => $category,
        'filter_icon' => admin_seller_asset_url($row['brand_icon'] ?? ''),
        'region' => (string)($row['region'] ?? 'Global'),
        'price' => (int)($row['price'] ?? 0),
        'stock' => (int)($row['stock'] ?? 0),
        'sold' => (int)($row['sold_count'] ?? 0),
        'status' => $active ? 'listed' : 'unlisted',
        'status_label' => $active ? 'Listed' : 'Unlisted',
        'created' => (string)($row['created_at'] ?? ''),
        'toggle' => ['action' => 'admin_unlist_marketplace_listing', 'kind' => 'digital_good', 'id' => (int)($row['id'] ?? 0), 'active' => $active ? 0 : 1],
    ];
}
$lstCfg = [
    'id' => 'digitalgoods',
    'title' => 'Digital Good',
    'icon' => 'fa-gem',
    'filterLabel' => 'Category',
    'filterIcons' => false,
    'tagIcon' => 'fa-tag',
    'columns' => ['game', 'region', 'stock', 'sold'],
    'searchPlaceholder' => 'Search digital goods…',
    'emptyText' => 'No seller digital goods found.',
];
include __DIR__ . '/_listings_table.php';
