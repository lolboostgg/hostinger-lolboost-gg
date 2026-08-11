<?= $this->layout('admin/layouts/main', ['meta'=>['title'=>'Seller Accounts - Admin Area | LoLBoost.gg']]) ?>
<?php $activeTab='accounts'; include __DIR__.'/_shared.php'; ?>
<?php
$accounts = is_array($accounts ?? null) ? $accounts : [];
$lstRows = [];
foreach ($accounts as $row) {
    $id = (int)($row['id'] ?? 0);
    $sold = (int)($row['sold'] ?? 0);
    $active = (int)($row['active'] ?? 1) === 1;
    $status = $sold === 2 ? 'refunded' : ($sold === 1 ? 'sold' : ($active ? 'listed' : 'unlisted'));
    $game = admin_seller_game_meta((string)($row['game'] ?? 'lol'));
    $image = admin_seller_first_image($row['images'] ?? '');
    $paid = (int)($row['seller_paid'] ?? 0) === 1;
    $lstRows[] = [
        'id' => $id,
        'title' => (string)($row['title'] ?? 'Account'),
        'subtitle' => trim(strtoupper((string)($row['server'] ?? '')) . ($paid ? ' · Payout done' : '')),
        'image' => admin_seller_asset_url($image ?: $game['icon']),
        'filter_key' => $game['key'],
        'filter_label' => $game['label'],
        'filter_icon' => admin_seller_asset_url($game['icon']),
        'region' => (string)($row['server'] ?? '—'),
        'price' => (int)($row['price'] ?? 0),
        'status' => $status,
        'status_label' => ucfirst($status),
        'created' => (string)($row['created_at'] ?? ''),
        'url' => ADMN_URL . '/selling-account/' . $id,
        'toggle' => in_array($status, ['sold', 'refunded'], true) ? null : ['action' => 'admin_toggle_account_active', 'account_id' => $id],
    ];
}
$lstCfg = [
    'id' => 'accounts',
    'title' => 'Account',
    'icon' => 'fa-user-shield',
    'filterLabel' => 'Game',
    'columns' => ['game', 'region'],
    'statuses' => ['All', 'Listed', 'Unlisted', 'Sold', 'Refunded'],
    'searchPlaceholder' => 'Search accounts…',
    'emptyText' => 'No seller accounts found.',
];
include __DIR__ . '/_listings_table.php';
