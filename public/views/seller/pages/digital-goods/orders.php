<?php
/* ── Seller: Digital Goods Orders — /seller-area/digital-goods
   Modell: identisch zu seller/pages/items/orders.php
   ─────────────────────────────────────────────────────── */
echo $this->layout('seller/layouts/main', ['meta' => $meta ?? ['title' => 'Digital Goods Orders | LoLBoost.gg']]);
require_once dirname(__DIR__) . '/_seller_rank.php';
$purchases   = is_array($purchases ?? null) ? $purchases : [];
$filterStatus = $status ?? '';
$page        = (int)($page ?? 1);
$perPage     = (int)($perPage ?? 30);

function dgso_get($row,$keys,$def=null){foreach((array)$keys as $k){if(is_array($row)&&array_key_exists($k,$row)&&$row[$k]!==null&&$row[$k]!=='')return $row[$k];}return $def;}
function dgso_money($v){return round((float)$v/100,2);}
function dgso_sym(){return function_exists('util_format_currency_display')?util_format_currency_display('EUR'):'€';}
function dgso_asset_path($path, string $default = ''): string
{
    $path = trim((string)($path ?? ''));
    if ($path === '') return $default;
    if (preg_match('#^https?://#i', $path)) return $path;

    $path = preg_replace('#^https?://[^/]+#i', '', $path);
    $path = preg_replace('#^/public/assets#i', '', $path);
    if ($path === '') return $default;
    if ($path[0] !== '/') $path = '/' . $path;

    return defined('ASSET_URL') ? rtrim(ASSET_URL, '/') . $path : $path;
}
function dgso_brand_icon_from_name(string $brand): string
{
    $key = strtolower(trim($brand));
    $map = [
        'youtube' => '/website/images/digital-goods/youtube.png',
        'spotify' => '/website/images/digital-goods/spotify.jpg',
        'discord' => '/website/images/digital-goods/discord-nitro.png',
        'discord nitro' => '/website/images/digital-goods/discord-nitro.png',
        'chatgpt' => '/website/images/digital-goods/chat-gpt.png',
        'chat gpt' => '/website/images/digital-goods/chat-gpt.png',
        'openai' => '/website/images/digital-goods/chat-gpt.png',
        'xbox' => '/website/images/digital-goods/xbox-gamepass.jpg',
        'xbox gamepass' => '/website/images/digital-goods/xbox-gamepass.jpg',
        'xbox game pass' => '/website/images/digital-goods/xbox-gamepass.jpg',
        'hytale' => '/website/images/digital-goods/hytale.webp',
        'adguard premium' => '/website/images/digital-goods/adguard-premium.webp',
        'adguard' => '/website/images/digital-goods/adguard-premium.webp',
        'voicemod pro' => '/website/images/digital-goods/voicemod-pro.webp',
        'voicemod' => '/website/images/digital-goods/voicemod-pro.webp',
        'perplexity' => '/website/images/digital-goods/perplexity.webp',
        'deezer' => '/website/images/digital-goods/deezer.webp',
        'fortnite vbucks' => '/website/images/digital-goods/fortnite-vbucks.webp',
        'fortnite' => '/website/images/digital-goods/fortnite-vbucks.webp',
        'vbucks' => '/website/images/digital-goods/fortnite-vbucks.webp',
        'v-bucks' => '/website/images/digital-goods/fortnite-vbucks.webp',
        'grok' => '/website/images/digital-goods/grok.webp',
        'warframe' => '/website/images/digital-goods/warframe.webp',
        'rocket league' => '/website/images/digital-goods/rocket-league.webp',
        'linkedin' => '/website/images/digital-goods/linkedin.webp',
        'runescape' => '/website/images/digital-goods/runescape-fantasy.webp',
        'evernote' => '/website/images/digital-goods/evernote.webp',
        'canva' => '/website/images/digital-goods/canva.webp',
        'photoroom' => '/website/images/digital-goods/photoroom.webp',
        'grammarly' => '/website/images/digital-goods/grammarly.webp',
        'f1 tv' => '/website/images/digital-goods/f1-tv.webp',
        'steam' => '/website/images/digital-goods/steam.webp',
        'snapchat' => '/website/images/digital-goods/snapchat.webp',
        'hbo' => '/website/images/digital-goods/hbo.webp',
        'bumble' => '/website/images/digital-goods/bumble.webp',
        'disney plus' => '/website/images/digital-goods/disney.webp',
        'disney+' => '/website/images/digital-goods/disney.webp',
        'capcut' => '/website/images/digital-goods/capcut.webp',
        'duolingo' => '/website/images/digital-goods/duolingo.webp',
        'nba league pass' => '/website/images/digital-goods/nba-pass.webp',
        'reddit' => '/website/images/digital-goods/reddit.webp',
        'medal tv' => '/website/images/digital-goods/medaltv.webp',
        'turbo vpn' => '/website/images/digital-goods/turbo-vpn.webp',
        'prime video' => '/website/images/digital-goods/prime-video.webp',
        'prime' => '/website/images/digital-goods/prime-video.webp',
        'amazon prime' => '/website/images/digital-goods/prime-video.webp',
        'twitch' => '/website/images/digital-goods/twitch.webp',
        'adobe creative cloud' => '/website/images/digital-goods/adobe-creative-cloud.webp',
        'adobe' => '/website/images/digital-goods/adobe-creative-cloud.webp',
        'badoo' => '/website/images/digital-goods/badoo.webp',
        'claude' => '/website/images/digital-goods/claude.webp',
        'epic games' => '/website/images/digital-goods/epic-games.webp',
        'crunchyroll' => '/website/images/digital-goods/crunchyroll.webp',
        'tinder' => '/website/images/digital-goods/tinder.webp',
        'ps plus' => '/website/images/digital-goods/ps-plus.webp',
        'playstation' => '/website/images/digital-goods/ps-plus.webp',
        'gemini' => '/website/images/digital-goods/gemini.webp',
        'cod points' => '/website/images/digital-goods/cod-points.webp',
        'cod' => '/website/images/digital-goods/cod-points.webp',
        'call of duty' => '/website/images/digital-goods/cod-points.webp',
    ];
    return $map[$key] ?? '';
}
function dgso_order_brand_icon(array $p): string
{
    // Digital Goods orders must show the offer's stored brand_icon.
    // Important: some purchase rows do not contain item_id/brand_icon depending on the route/query version.
    // Therefore we resolve it by: row fields -> item_id -> invoice.order_id -> purchase id -> title/seller fallback.
    $brandIcon = dgso_get($p, ['item_brand_icon','listing_brand_icon','brand_icon','dg_brand_icon','digital_good_brand_icon'], '');
    if ($brandIcon !== '') return dgso_asset_path((string)$brandIcon);

    $brand = (string)dgso_get($p, ['brand','item_brand','listing_brand'], '');
    $itemId = (int)dgso_get($p, ['item_id','digital_good_id','listing_id','order_id'], 0);
    $purchaseId = (int)dgso_get($p, ['id','purchase_id'], 0);
    $invoiceId = (int)dgso_get($p, ['invoice_id'], 0);
    $sellerId = (int)dgso_get($p, ['seller_id'], defined('SELLER_ID') ? SELLER_ID : 0);
    $title = (string)dgso_get($p, ['item_title','title'], '');

    try {
        global $db;
        if (!empty($db)) {
            // 1) Direct listing id from the purchase row.
            if ($itemId > 0) {
                $row = $db->row("SELECT brand_icon, brand FROM digital_goods WHERE id = ? LIMIT 1", $itemId);
                if (is_array($row)) {
                    $dbIcon = trim((string)($row['brand_icon'] ?? ''));
                    if ($dbIcon !== '') return dgso_asset_path($dbIcon);
                    if ($brand === '') $brand = (string)($row['brand'] ?? '');
                }
            }

            // 2) Resolve through invoice.order_id; this is the checkout source for digital_good orders.
            if ($invoiceId > 0) {
                $row = $db->row(
                    "SELECT dg.brand_icon, dg.brand
                     FROM invoices inv
                     INNER JOIN digital_goods dg ON dg.id = inv.order_id
                     WHERE inv.id = ? AND inv.order_type = 'digital_good'
                     LIMIT 1",
                    $invoiceId
                );
                if (is_array($row)) {
                    $dbIcon = trim((string)($row['brand_icon'] ?? ''));
                    if ($dbIcon !== '') return dgso_asset_path($dbIcon);
                    if ($brand === '') $brand = (string)($row['brand'] ?? '');
                }
            }

            // 3) Resolve from the purchase id itself, trying item_id first and invoice.order_id second.
            if ($purchaseId > 0) {
                $row = $db->row(
                    "SELECT dg.brand_icon, dg.brand
                     FROM digital_good_purchases dgp
                     LEFT JOIN invoices inv ON inv.id = dgp.invoice_id
                     LEFT JOIN digital_goods dg ON dg.id = COALESCE(NULLIF(dgp.item_id,0), NULLIF(inv.order_id,0))
                     WHERE dgp.id = ?
                     LIMIT 1",
                    $purchaseId
                );
                if (is_array($row)) {
                    $dbIcon = trim((string)($row['brand_icon'] ?? ''));
                    if ($dbIcon !== '') return dgso_asset_path($dbIcon);
                    if ($brand === '') $brand = (string)($row['brand'] ?? '');
                }
            }

            // 4) Last DB fallback: same seller + same title. Useful for old rows missing item_id/invoice mapping.
            if ($sellerId > 0 && trim($title) !== '') {
                $row = $db->row(
                    "SELECT brand_icon, brand
                     FROM digital_goods
                     WHERE seller_id = ? AND title = ?
                     ORDER BY id DESC
                     LIMIT 1",
                    $sellerId,
                    $title
                );
                if (is_array($row)) {
                    $dbIcon = trim((string)($row['brand_icon'] ?? ''));
                    if ($dbIcon !== '') return dgso_asset_path($dbIcon);
                    if ($brand === '') $brand = (string)($row['brand'] ?? '');
                }
            }
        }
    } catch (Throwable $e) {}

    // 5) Last non-DB fallback for known brands.
    $byBrand = dgso_brand_icon_from_name($brand);
    if ($byBrand !== '') return dgso_asset_path($byBrand);

    return '';
}
$sym = dgso_sym();
?>
<?php
// The Items / Top Ups / Digital Goods order lists all render through the same
// shared table so they stay visually identical; only the row data differs.
require_once dirname(__DIR__) . '/_orders_shared.php';

$eff_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);

$rows = [];
foreach ($purchases as $p) {
    $id     = (int)dgso_get($p, ['id', 'purchase_id'], 0);
    $qty    = max(1, (int)dgso_get($p, ['quantity', 'qty'], 1));
    $total  = dgso_money(dgso_get($p, ['price', 'total_price'], 0));
    $brand  = (string)dgso_get($p, ['brand'], '');
    $region = (string)dgso_get($p, ['region'], 'Global');
    $status = (string)dgso_get($p, ['status'], 'UNPAID');

    $rows[] = [
        'id'        => $id,
        'url'       => BASE_URL . '/seller-area/digital-goods/' . $id,
        'cover'     => dgso_order_brand_icon($p),
        'name'      => (string)dgso_get($p, ['item_title', 'title'], '—'),
        'sub'       => $brand !== '' ? $brand : 'Digital Good',
        'game_name' => $brand !== '' ? $brand : 'Digital Good',
        'game_icon' => dgso_order_brand_icon($p),
        'meta'      => trim(($region !== '' ? $region : 'Global') . ' · ' . (string)dgso_get($p, ['category_name'], 'Digital Good')),
        'price'     => $total,
        'earnings'  => round($total * (1 - $eff_fee / 100), 2),
        'stock'     => $qty,
        'sold'      => $qty,
        'status'    => sol_status($status),
        'buyer'     => (string)dgso_get($p, ['client_username', 'buyer'], 'Unknown buyer'),
        'buyer_icon' => sol_client_icon(dgso_get($p, ['buyer_icon', 'client_icon', 'client_avatar'], '')),
        'created'   => sol_relative(dgso_get($p, ['created_at', 'paid_at'], '')),
    ];
}

$rows = sol_visible_rows($rows);

$olCfg = [
    'icon'     => 'fa-duotone fa-box-open',
    'title'    => 'Digital Goods Orders',
    'subtitle' => count($rows) . ' order' . (count($rows) !== 1 ? 's' : '') . ' total',
    'link'     => ['href' => BASE_URL . '/seller-area/digital-goods/listings', 'label' => 'My Listings', 'icon' => 'fa-solid fa-layer-group'],
    'search_placeholder' => 'Search digital goods...',
    'empty'    => 'No digital good orders yet.',
    'columns'  => ['item' => 'Product', 'game' => 'Brand', 'game_data' => 'Region & Category'],
    'fallback_icon' => 'fa-solid fa-box-open',
    'filters'  => sol_filters($rows),
    'rows'     => $rows,
];
include dirname(__DIR__) . '/_orders_list.php';
