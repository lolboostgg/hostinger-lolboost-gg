<?php
/* ── Seller: Digital Goods Offers — /seller-area/digital-goods/listings
   Create flow is handled inside this page through an offcanvas drawer.
   ─────────────────────────────────────────────────────────────────── */
echo $this->layout('seller/layouts/main', ['meta' => $meta ?? ['title' => 'Digital Goods Offers | LoLBoost.gg']]);
require_once dirname(__DIR__) . '/_seller_rank.php';

$listings    = is_array($listings   ?? null) ? $listings   : [];
$categories  = is_array($categories ?? null) ? $categories : [];
$seller_data = is_array($seller_data ?? null) ? $seller_data : (defined('SELLER_DATA') && is_array(SELLER_DATA) ? SELLER_DATA : []);
$effective_fee = seller_effective_fee_from_rank($seller_data);

$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$dgBrandsForJs = [];
foreach ((is_array($brands ?? null) ? $brands : []) as $brandRow) {
    $brandName = trim((string)($brandRow['name'] ?? ''));
    $brandIcon = trim((string)($brandRow['icon_path'] ?? ''));
    if ($brandName === '' || $brandIcon === '') continue;
    $brandSlug = trim((string)($brandRow['slug'] ?? ''));
    if ($brandSlug === '') {
        $brandSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $brandName));
        $brandSlug = trim($brandSlug, '-');
    }
    $dgBrandsForJs[] = ['id'=>(int)($brandRow['id'] ?? 0), 'name'=>$brandName, 'slug'=>$brandSlug, 'icon'=>$brandIcon, 'active'=>(int)($brandRow['active'] ?? 1)];
}

$dgCategoryIconFallback = static function (array $cat): string {
    $icon = trim((string)($cat['icon'] ?? ''));
    if ($icon !== '') return $icon;

    // Fallback only for older controller queries that still do not SELECT the icon column.
    // The view prefers digital_good_categories.icon whenever it is available.
    $slug = strtolower(trim((string)($cat['slug'] ?? '')));
    $name = strtolower(trim((string)($cat['name'] ?? '')));
    $key  = $slug !== '' ? $slug : preg_replace('/[^a-z0-9]+/', '-', $name);
    $key  = trim((string)$key, '-');

    $fallback = [
        'streaming' => 'fa-solid fa-play',
        'streaming-music' => 'fa-solid fa-play',
        'software' => 'fa-solid fa-microchip',
        'software-tools' => 'fa-solid fa-microchip',
        'subscriptions' => 'fa-solid fa-repeat',
        'ingame-currency' => 'fa-solid fa-coins',
        'social-dating' => 'fa-solid fa-heart',
        'gaming-subscriptions' => 'fa-solid fa-gamepad',
        'ai-productivity' => 'fa-solid fa-robot',
        'discord' => 'fa-brands fa-discord',
    ];

    return $fallback[$key] ?? 'fa-solid fa-tag';
};
$catMap = [];
foreach ($categories as $cat) {
    $catMap[(int)($cat['id'] ?? 0)] = (string)($cat['name'] ?? '');
}

$dgSlugFromTitle = static function (string $title): string {
    $title = trim($title !== '' ? $title : 'digital-good');
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
        if ($converted !== false) $title = $converted;
    }
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim((string)$slug, '-');
    return $slug !== '' ? $slug : 'digital-good';
};

$dgListingUrl = static function (array $listing) use ($dgSlugFromTitle): string {
    $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    // Public offer URLs use the stored unique slug. If none exists yet, fall back to the title.
    $slug = trim((string)($listing['slug'] ?? ''));
    if ($slug === '') $slug = $dgSlugFromTitle((string)($listing['title'] ?? 'digital-good'));
    return $baseUrl . '/digital-good/' . rawurlencode($slug);
};

$totalActive = count(array_filter($listings, fn($l) => (int)($l['active'] ?? 0) === 1));
$dgInfiniteStockThreshold = 999999;
$dgIsInfiniteStock = static fn($stock): bool => (int)($stock ?? 0) >= $dgInfiniteStockThreshold;
$totalSold = 0;
$totalStock = 0;
$hasInfiniteStock = false;
$totalRevenueCents = 0;
foreach ($listings as $l) {
    $sold = (int)($l['sold_count'] ?? 0);
    $price = (int)($l['price'] ?? 0);
    $stock = (int)($l['stock'] ?? 0);
    $totalSold += $sold;
    if ($dgIsInfiniteStock($stock)) {
        $hasInfiniteStock = true;
    } else {
        $totalStock += $stock;
    }
    $totalRevenueCents += $sold * $price;
}
?>
<?= $this->start('styles') ?>
<style>
.dg-offers{--dg-panel:#25282a;--dg-border:rgba(255,255,255,.07);--dg-muted:rgba(255,255,255,.48);--dg-soft:rgba(255,255,255,.06);}
.dg-offers .card{background:var(--dg-panel)!important;border:1px solid var(--dg-border)!important;border-radius:22px!important;box-shadow:none!important;}
.dg-offers .card::before{display:none!important;}
.dg-hero{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:22px;}
.dg-hero-title{font-size:1.45rem;font-weight:950;color:#fff;margin:0;letter-spacing:-.03em;}
.dg-hero-sub{font-size:.86rem;color:rgba(255,255,255,.42);margin-top:4px;}
.dg-add-btn{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#8b3cf7,#c026d3);border:none;border-radius:13px;padding:.62rem 1.35rem;font-weight:950;font-size:.9rem;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;text-decoration:none;}
.dg-add-btn:hover{opacity:.9;color:#fff;transform:translateY(-1px);}
.dg-btn{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:7px 12px;border-radius:10px;font-size:.76rem;font-weight:850;cursor:pointer;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:rgba(255,255,255,.74);text-decoration:none;transition:background .12s,color .12s,border-color .12s;}
.dg-btn:hover{background:rgba(255,255,255,.12);color:#fff;}
.dg-btn--edit{background:rgba(139,60,247,.14);border-color:rgba(139,60,247,.28);color:#c084fc;}
.dg-btn--duplicate{background:rgba(34,211,238,.10);border-color:rgba(34,211,238,.25);color:#67e8f9;}
.dg-btn--duplicate:hover{background:rgba(34,211,238,.18);color:#fff;}
.dg-btn--delete{background:rgba(251,113,133,.1);border-color:rgba(251,113,133,.25);color:#fb7185;}
.dg-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:22px;}
.dg-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;}
.dg-stat-inner{display:flex;align-items:center;gap:11px;}
.dg-stat-icon{width:40px;height:40px;border-radius:12px;background:rgba(139,60,247,.18);border:1px solid rgba(139,60,247,.28);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.dg-stat-label{font-size:.74rem;color:rgba(255,255,255,.42);font-weight:750;}
.dg-stat-value{font-size:1.12rem;font-weight:950;color:#fff;line-height:1.2;}
.dg-panel{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;background:#25282a;}
.dg-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025);}
.dg-panel-title{font-size:.98rem;font-weight:950;color:#fff;display:flex;align-items:center;gap:.5rem;}
.dg-panel-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
.dg-search{width:min(280px,60vw);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.11);border-radius:11px;color:#fff;padding:8px 12px;font-size:.86rem;outline:none;}
.dg-search:focus{border-color:rgba(139,60,247,.52);box-shadow:0 0 0 3px rgba(139,60,247,.12);}
.dg-table-wrap{overflow-x:auto;}
.dg-table{width:100%;border-collapse:collapse;min-width:900px;}
.dg-table thead tr{background:rgba(255,255,255,.015);border-bottom:1px solid rgba(255,255,255,.06);}
.dg-table thead th{padding:11px 16px;font-size:.68rem;font-weight:950;color:rgba(255,255,255,.34);text-transform:uppercase;letter-spacing:.075em;white-space:nowrap;}
.dg-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.dg-table tbody tr:last-child{border-bottom:none;}
.dg-table tbody tr:hover{background:rgba(139,60,247,.08);}
.dg-table tbody td{padding:13px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8);}
.dg-offer{display:flex;align-items:center;gap:12px;min-width:260px;}
.dg-offer-img{width:48px;height:48px;border-radius:12px;object-fit:cover;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);flex-shrink:0;}
.dg-offer-img--ph{display:grid;place-items:center;background:linear-gradient(145deg,rgba(99,102,241,.22),rgba(139,92,246,.12));border-color:rgba(129,140,248,.3);color:#a5b4fc;font-size:18px;}
.dg-offer-title{font-size:.9rem;font-weight:900;color:#fff;line-height:1.18;}
.dg-offer-sub{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:2px;display:flex;gap:6px;align-items:center;flex-wrap:wrap;}
.dg-pill{display:inline-flex;align-items:center;gap:.3rem;padding:4px 10px;border-radius:999px;font-size:.71rem;font-weight:850;white-space:nowrap;}
.dg-pill--active{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;}
.dg-pill--inactive{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.3);color:#facc15;}
.dg-pill--manual{background:rgba(96,165,250,.11);border:1px solid rgba(96,165,250,.25);color:#93c5fd;}
.dg-money{font-weight:950;color:#fff;}
.dg-actions{display:flex;gap:7px;align-items:center;}
.dg-toggle{appearance:none;width:40px;height:22px;border-radius:99px;background:rgba(255,255,255,.13);cursor:pointer;position:relative;transition:background .2s;border:none;flex-shrink:0;}
.dg-toggle::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .2s;}
.dg-toggle:checked{background:linear-gradient(135deg,#8b3cf7,#c026d3);}
.dg-toggle:checked::after{transform:translateX(18px);}
.dg-empty{text-align:center;padding:64px 24px;color:rgba(255,255,255,.38);}
.dg-empty i{font-size:3rem;display:block;margin-bottom:12px;opacity:.28;}
.dg-oc{width:50vw!important;display:flex!important;flex-direction:column!important;height:100%!important;}
.dg-oc .offcanvas-header{flex-shrink:0;border-bottom:1px solid var(--bs-card-border-color);padding:18px 22px;}
.dg-oc .offcanvas-body{flex:1!important;overflow:hidden!important;display:flex!important;flex-direction:column!important;padding:0!important;}
.dg-oc form{height:100%;display:flex;flex-direction:column;overflow:hidden;min-height:0;}
.dg-oc-scroll{flex:1;overflow-y:auto;padding:18px 22px;}
.dg-oc-footer{flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 22px;border-top:1px solid var(--bs-card-border-color);background:var(--bs-offcanvas-bg,#1e2028);}
.dg-earnings-bar{flex-shrink:0;padding:10px 22px;background:rgba(139,60,247,.08);border-bottom:1px solid rgba(139,60,247,.18);display:flex;align-items:center;gap:8px;font-size:.83rem;color:rgba(255,255,255,.72);}
.dg-section-label{display:flex;align-items:center;gap:6px;font-size:.68rem;font-weight:950;text-transform:uppercase;letter-spacing:.09em;color:rgba(255,255,255,.35);margin:16px 0 9px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.06);}
.dg-section-label:first-child{margin-top:0;}.dg-section-label i{color:#8b5cf6;font-size:.68rem;}
.dg-form-label{font-size:.78rem;font-weight:800;color:rgba(255,255,255,.58);margin-bottom:5px;display:block;text-transform:uppercase;letter-spacing:.05em;}
.dg-required{color:#fb7185;font-size:.75rem;vertical-align:super;}
.dg-form-input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:9px 13px;font-size:.9rem;outline:none;}
.dg-form-input:focus{border-color:rgba(139,60,247,.5);box-shadow:0 0 0 3px rgba(139,60,247,.12);}
.dg-form-input::placeholder{color:rgba(255,255,255,.25);}select.dg-form-input option{background:#1a1a2e;color:#fff;}
/* Custom Category Select */
.dg-custom-select{position:relative;user-select:none;}
.dg-custom-select-trigger{display:flex;align-items:center;gap:10px;width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:9px 13px;font-size:.9rem;cursor:pointer;transition:border-color .15s,box-shadow .15s;}
.dg-custom-select-trigger:hover{border-color:rgba(139,60,247,.4);}
.dg-custom-select.open .dg-custom-select-trigger{border-color:rgba(139,60,247,.5);box-shadow:0 0 0 3px rgba(139,60,247,.12);}
.dg-custom-select-icon{width:22px;height:22px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:rgba(255,255,255,.5);font-size:.85rem;}
.dg-custom-select-text{flex:1;color:rgba(255,255,255,.6);}
.dg-custom-select-text.selected{color:#fff;}
.dg-custom-select-arrow{color:rgba(255,255,255,.35);font-size:.7rem;transition:transform .2s;margin-left:auto;}
.dg-custom-select.open .dg-custom-select-arrow{transform:rotate(180deg);}
.dg-custom-select-dropdown{display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:9999;background:#1e2028;border:1px solid rgba(139,60,247,.35);border-radius:12px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.5);}
.dg-custom-select.open .dg-custom-select-dropdown{display:block;}
.dg-custom-select-option{display:flex;align-items:center;gap:10px;padding:10px 13px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.05);transition:background .1s;font-size:.88rem;color:#fff;font-weight:600;}
.dg-custom-select-option:last-child{border-bottom:none;}
.dg-custom-select-option:hover{background:rgba(139,60,247,.18);}
.dg-custom-select-option.active{background:rgba(139,60,247,.25);color:#c084fc;}
.dg-custom-select-option .dg-custom-select-icon{color:rgba(255,255,255,.7);}
.dg-custom-select-option.active .dg-custom-select-icon{color:#c084fc;}
.dg-hint{font-size:.72rem;color:rgba(255,255,255,.32);margin-top:4px;line-height:1.4;}
.dg-dropzone{border:1.5px dashed rgba(255,255,255,.16);background:rgba(255,255,255,.035);border-radius:14px;padding:18px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s;}
.dg-dropzone:hover,.dg-dropzone.is-dragover{border-color:rgba(139,60,247,.65);background:rgba(139,60,247,.09);}
.dg-dropzone i{font-size:1.55rem;color:#a78bfa;margin-bottom:8px;display:block;}.dg-dropzone-title{font-size:.9rem;font-weight:900;color:#fff;}.dg-dropzone-sub{font-size:.76rem;color:rgba(255,255,255,.38);margin-top:3px;}
/* ── Validity Picker ── */
.dg-validity-picker{margin-bottom:0;}
.dg-vp-pills{display:flex;flex-wrap:wrap;gap:7px;}
.dg-vp-pill{display:inline-flex;align-items:center;padding:6px 14px;border-radius:99px;font-size:.8rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:rgba(255,255,255,.6);transition:background .15s,border-color .15s,color .15s;}
.dg-vp-pill:hover{background:rgba(139,60,247,.15);border-color:rgba(139,60,247,.4);color:#c084fc;}
.dg-vp-pill.active{background:rgba(139,60,247,.25);border-color:rgba(139,60,247,.6);color:#fff;}
.dg-vp-pill--custom{border-style:solid;color:rgba(255,255,255,.45);}
.dg-vp-custom-wrap{display:flex;align-items:center;gap:8px;margin-top:8px;padding:7px 12px;border-radius:10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);}
.dg-vp-custom-input{flex:1;background:transparent;border:0;outline:0;color:rgba(255,255,255,.85);font-size:.84rem;font-weight:600;}
.dg-vp-custom-input::placeholder{color:rgba(255,255,255,.22);font-weight:500;}
.dg-vp-custom-hint{font-size:.74rem;font-weight:700;color:#a78bfa;white-space:nowrap;}
.dg-preview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(84px,1fr));gap:10px;margin-top:12px;}
.dg-preview{position:relative;border-radius:12px;overflow:hidden;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);aspect-ratio:1/1;}
.dg-preview img{width:100%;height:100%;object-fit:cover;display:block;}.dg-preview button{position:absolute;top:5px;right:5px;width:24px;height:24px;border-radius:999px;border:none;background:rgba(0,0,0,.65);color:#fff;font-size:.7rem;display:flex;align-items:center;justify-content:center;}
.dg-submit{display:inline-flex;align-items:center;gap:.45rem;background:linear-gradient(135deg,#8b3cf7,#c026d3);border:none;border-radius:11px;padding:9px 20px;font-size:.88rem;font-weight:950;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;}
.dg-submit:hover{opacity:.9;transform:translateY(-1px);}.dg-submit:disabled{opacity:.55;cursor:wait;transform:none;}
.dg-secondary{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:11px;padding:9px 16px;font-size:.87rem;font-weight:750;color:rgba(255,255,255,.68);cursor:pointer;}
.dg-secondary:hover{background:rgba(255,255,255,.08);color:#fff;}

.dg-stock-box{display:flex;flex-direction:column;gap:8px;}
.dg-stock-input-wrap{position:relative;}
.dg-stock-input-wrap.is-infinite .dg-form-input{padding-right:92px;color:rgba(255,255,255,.58);}
.dg-stock-infinity-badge{display:none;position:absolute;right:10px;top:50%;transform:translateY(-50%);align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:rgba(139,60,247,.18);border:1px solid rgba(139,60,247,.32);color:#c4b5fd;font-size:.72rem;font-weight:900;pointer-events:none;}
.dg-stock-input-wrap.is-infinite .dg-stock-infinity-badge{display:inline-flex;}
.dg-infinite-stock-toggle{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 10px;border-radius:10px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.09);cursor:pointer;user-select:none;}
.dg-infinite-stock-copy{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.68);font-size:.78rem;font-weight:850;}
.dg-infinite-stock-copy i{color:#c4b5fd;font-size:.82rem;}
.dg-infinite-stock-switch{appearance:none;width:38px;height:20px;border-radius:999px;background:rgba(255,255,255,.14);position:relative;cursor:pointer;transition:background .18s;flex-shrink:0;border:0;}
.dg-infinite-stock-switch::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:#fff;transition:transform .18s;}
.dg-infinite-stock-switch:checked{background:linear-gradient(135deg,#8b3cf7,#c026d3);}
.dg-infinite-stock-switch:checked::after{transform:translateX(18px);}

/* Custom compact selects for seller listing offcanvas */
.dg-native-select-hidden{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important;left:-9999px!important;}
.dg-select{position:relative;user-select:none;}
.dg-select-trigger{display:flex;align-items:center;gap:10px;width:100%;min-height:40px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:9px 13px;font-size:.9rem;cursor:pointer;transition:border-color .15s,box-shadow .15s,background .15s;}
.dg-select-trigger:hover{border-color:rgba(139,60,247,.4);background:rgba(255,255,255,.075);}
.dg-select.open .dg-select-trigger{border-color:rgba(139,60,247,.62);box-shadow:0 0 0 3px rgba(139,60,247,.13);}
.dg-select-icon{width:22px;height:22px;border-radius:7px;background:rgba(139,60,247,.12);border:1px solid rgba(139,60,247,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#c4b5fd;font-size:.78rem;}
.dg-select-text{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#fff;font-weight:750;}
.dg-select-arrow{color:rgba(255,255,255,.38);font-size:.72rem;margin-left:auto;transition:transform .18s;}
.dg-select.open .dg-select-arrow{transform:rotate(180deg);}
.dg-select-dropdown{display:none;position:absolute;top:calc(100% + 5px);left:0;right:0;z-index:10000;background:#1e2028;border:1px solid rgba(139,60,247,.35);border-radius:12px;overflow:hidden;box-shadow:0 12px 34px rgba(0,0,0,.48);max-height:260px;overflow-y:auto;scrollbar-width:thin;}
.dg-select.open .dg-select-dropdown{display:block;}
.dg-select-option{display:flex;align-items:center;gap:10px;padding:10px 13px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.05);transition:background .1s,color .1s;color:rgba(255,255,255,.78);font-size:.88rem;font-weight:750;}
.dg-select-option:last-child{border-bottom:none;}
.dg-select-option:hover{background:rgba(139,60,247,.16);color:#fff;}
.dg-select-option.active{background:rgba(139,60,247,.24);color:#fff;}
.dg-select-option.active .dg-select-icon{background:rgba(139,60,247,.2);border-color:rgba(139,60,247,.38);color:#fff;}
@media(max-width:991px){.dg-oc{width:100vw!important}.dg-panel-head{align-items:stretch;flex-direction:column}.dg-search{width:100%;}}
</style>
<?= $this->stop() ?>

<div class="dg-offers">
  <div class="container-fluid p-0">
    <div class="dg-hero">
      <div>
        <h1 class="dg-hero-title">Digital Goods Offers</h1>
        <div class="dg-hero-sub">Manage your digital goods offers and add new listings without leaving this page.</div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/seller-area/digital-goods" class="dg-btn"><i class="fa-solid fa-box-open"></i> Orders</a>
        <button type="button" class="dg-add-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDgListing" aria-controls="offcanvasAddDgListing"><i class="fa-solid fa-plus"></i> Add Listing</button>
      </div>
    </div>

    <div class="dg-stats">
      <?php
        $stats = [
            ['icon'=>'fa-tags','label'=>'Total Offers','val'=>count($listings),'color'=>'#a78bfa'],
            ['icon'=>'fa-check-circle','label'=>'Active','val'=>$totalActive,'color'=>'#4ade80'],
            ['icon'=>'fa-boxes-stacked','label'=>'Stock','val'=>$hasInfiniteStock ? '∞' : $totalStock,'color'=>'#60a5fa'],
            ['icon'=>'fa-fire','label'=>'Total Sold','val'=>$totalSold,'color'=>'#fbbf24'],
            ['icon'=>'fa-coins','label'=>'Revenue','val'=>'€'.number_format($totalRevenueCents/100,2),'color'=>'#c084fc'],
        ];
        foreach ($stats as $s): ?>
      <div class="dg-stat">
        <div class="dg-stat-inner">
          <div class="dg-stat-icon" style="color:<?= $s['color'] ?>;"><i class="fa-solid <?= $s['icon'] ?>"></i></div>
          <div><div class="dg-stat-label"><?= $h($s['label']) ?></div><div class="dg-stat-value"><?= $h($s['val']) ?></div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="dg-panel">
      <div class="dg-panel-head">
        <div class="dg-panel-title"><i class="fa-solid fa-layer-group"></i> Offers</div>
        <div class="dg-panel-tools"><input class="dg-search" id="dgOfferSearch" type="search" placeholder="Search offers..."></div>
      </div>
      <div class="dg-table-wrap">
        <?php if (empty($listings)): ?>
        <div class="dg-empty">
          <i class="fa-solid fa-layer-group"></i>
          <div style="font-size:1rem;font-weight:850;color:rgba(255,255,255,.55);margin-bottom:8px;">No offers yet</div>
          <div style="margin-bottom:16px;">Create your first digital good offer to start selling.</div>
          <button type="button" class="dg-add-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDgListing"><i class="fa-solid fa-plus"></i> Add Listing</button>
        </div>
        <?php else: ?>
        <table class="dg-table" id="dgOffersTable">
          <thead>
            <tr>
              <th>Offer</th><th>Category</th><th>Price</th><th>Stock</th><th>Sold</th><th>Delivery</th><th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="dgOffersBody">
          <?php foreach ($listings as $l):
            $imgs  = json_decode((string)($l['images'] ?? '[]'), true);
            if (!is_array($imgs)) $imgs = [];
            // Brand-Icon als Fallback wenn kein eigenes Bild vorhanden
            $brandIconPath = !empty($l['brand_icon']) ? $l['brand_icon'] : '';
            // Normalize stored paths: strip legacy /public/assets prefix so ASSET_URL concat works correctly
            // Same normalization as the public digital-good pages: stored paths may be
            // absolute URLs, /public/assets/... or plain relative paths.
            $dgAssetUrl = static function ($path) {
                $path = trim((string)($path ?? ''));
                if ($path === '') return '';
                if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;
                $path = preg_replace('#^/public/assets#', '', $path);
                $path = preg_replace('#/public/assets/#', '/', $path);
                return rtrim((string)ASSET_URL, '/') . '/' . ltrim((string)$path, '/');
            };
            $thumb = $dgAssetUrl(!empty($imgs[0]) ? $imgs[0] : $brandIconPath);
            $isActive = (int)($l['active'] ?? 0) === 1;
            $price = function_exists('dg_format_price') ? dg_format_price((int)($l['price'] ?? 0), $l['currency'] ?? 'EUR') : ('€' . number_format(((int)($l['price'] ?? 0))/100, 2));
            $catName = $catMap[(int)($l['category_id'] ?? 0)] ?? '—';
            $searchBlob = strtolower(trim(($l['title'] ?? '') . ' ' . ($l['brand'] ?? '') . ' ' . $catName . ' ' . ($l['region'] ?? '')));
          ?>
            <tr id="dg-row-<?= (int)$l['id'] ?>" data-search="<?= $h($searchBlob) ?>"
                data-id="<?= (int)$l['id'] ?>"
                data-title="<?= $h($l['title']??'') ?>"
                data-slug="<?= $h($l['slug'] ?? '') ?>"
                data-brand="<?= $h($l['brand']??'') ?>"
                data-region="<?= $h($l['region']??'') ?>"
                data-category="<?= (int)($l['category_id']??0) ?>"
                data-validity="<?= (int)($l['validity_days']??0) ?>"
                data-description="<?= $h($l['description']??'') ?>"
                data-delivery-instructions="<?= $h($l['delivery_instructions']??'') ?>"
                data-safety-information="<?= $h($l['safety_information'] ?? $l['safety_info'] ?? '') ?>"
                data-price="<?= number_format((int)($l['price']??0)/100, 2, '.', '') ?>"
                data-stock="<?= (int)($l['stock']??0) ?>"
                data-min-qty="<?= (int)($l['min_purchase_qty']??1) ?>"
                data-max-qty="<?= (int)($l['max_purchase_qty']??0) ?>"
                data-delivery="<?= $h($l['delivery_type']??'manual') ?>"
                data-active="<?= (int)($l['active']??0) ?>"
                data-thumb="<?= $h($thumb) ?>"
                data-brand-icon="<?= $h(preg_replace('#^/public/assets#', '', $l['brand_icon'] ?? '')) ?>"
            >
              <td>
                <div class="dg-offer">
                  <?php if ($thumb !== ''): ?>
                    <img class="dg-offer-img" src="<?= $h($thumb) ?>" alt="" onerror="this.outerHTML='<div class=\'dg-offer-img dg-offer-img--ph\'><i class=\'fa-solid fa-layer-group\'></i></div>';">
                  <?php else: ?>
                    <div class="dg-offer-img dg-offer-img--ph"><i class="fa-solid fa-layer-group"></i></div>
                  <?php endif; ?>
                  <div>
                    <div class="dg-offer-title"><?= $h($l['title'] ?? '') ?></div>
                    <div class="dg-offer-sub">
                      <span><?= $h(($l['brand'] ?? '') !== '' ? $l['brand'] : 'No brand') ?></span><span>•</span><span><?= $h(($l['region'] ?? '') !== '' ? $l['region'] : 'Global') ?></span>
                    </div>
                  </div>
                </div>
              </td>
              <td><span style="color:rgba(255,255,255,.62);font-size:.82rem;"><?= $h($catName) ?></span></td>
              <td><span class="dg-money"><?= $price ?></span></td>
              <td style="font-weight:850;"><?= $dgIsInfiniteStock($l['stock'] ?? 0) ? '∞ Infinite' : (int)($l['stock'] ?? 0) ?></td>
              <td style="color:rgba(255,255,255,.58);"><?= (int)($l['sold_count'] ?? 0) ?></td>
              <td><span class="dg-pill dg-pill--manual"><i class="fa-solid fa-paper-plane"></i> <?= $h(ucfirst($l['delivery_type'] ?? 'manual')) ?></span></td>
              <td><input type="checkbox" class="dg-toggle dg-active-toggle" <?= $isActive ? 'checked' : '' ?> data-id="<?= (int)$l['id'] ?>" title="<?= $isActive ? 'Active' : 'Inactive' ?>"></td>
              <td>
                <div class="dg-actions">
                  <button type="button" class="dg-btn dg-btn--edit dg-edit-btn" data-id="<?= (int)$l['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                  <button type="button" class="dg-btn dg-btn--duplicate dg-duplicate-btn" data-id="<?= (int)$l['id'] ?>" data-name="<?= $h($l['title'] ?? '') ?>" title="Duplicate offer"><i class="fa-solid fa-copy"></i></button>
                  <a href="<?= $h($dgListingUrl($l)) ?>" target="_blank" class="dg-btn"><i class="fa-solid fa-eye"></i></a>
                  <button type="button" class="dg-btn dg-btn--delete dg-delete-btn" data-id="<?= (int)$l['id'] ?>" data-name="<?= $h($l['title'] ?? '') ?>"><i class="fa-solid fa-trash"></i></button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end dg-oc" tabindex="-1" id="offcanvasAddDgListing" aria-labelledby="offcanvasAddDgListingLabel" data-bs-backdrop="static" data-bs-keyboard="true">
  <div class="offcanvas-header">
    <div>
      <h5 class="offcanvas-title mb-0" id="offcanvasAddDgListingLabel" style="font-weight:950;font-size:1.02rem;">Add Digital Good Listing</h5>
      <div style="font-size:.75rem;color:rgba(255,255,255,.4);margin-top:2px;">Create your offer</div>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="dg-earnings-bar">
    <i class="fa-solid fa-coins" style="color:#c084fc;"></i>
    <span>Seller fee: <strong><?= number_format((float)$effective_fee, 1) ?>%</strong>. Your estimated net updates after entering a price.</span>
  </div>
  <div class="offcanvas-body">
    <form id="dgCreateForm" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="action" value="seller_dg_create_listing">
      <input type="hidden" name="active" value="1">
      <div class="dg-oc-scroll">
        <div class="dg-section-label"><i class="fa-solid fa-circle-info"></i> Product Details</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="dg-form-label">Title <span class="dg-required">*</span></label>
            <input type="text" class="dg-form-input" name="title" id="dgCreateTitle" required placeholder="e.g. Netflix Premium 1 Month">
          </div>
          <div class="col-12">
            <label class="dg-form-label">Slug</label>
            <input type="text" class="dg-form-input" name="slug" id="dgCreateSlug" placeholder="youtube-premium-12-months">
            <div class="dg-hint">Auto-generated from the title. You can customize it. Duplicates become -2, -3, etc.</div>
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Category <span class="dg-required">*</span></label>
            <input type="hidden" name="category_id" id="dgCategoryField" required>
            <div class="dg-custom-select" id="dgCategorySelect">
              <div class="dg-custom-select-trigger" id="dgCategoryTrigger">
                <span class="dg-custom-select-icon"><i class="fa-solid fa-layer-group"></i></span>
                <span class="dg-custom-select-text" id="dgCategoryText">Select category</span>
                <i class="fa-solid fa-chevron-down dg-custom-select-arrow"></i>
              </div>
              <div class="dg-custom-select-dropdown" id="dgCategoryDropdown">
                <?php foreach ($categories as $cat): ?>
                <div class="dg-custom-select-option" data-value="<?= (int)$cat['id'] ?>" data-name="<?= $h($cat['name'] ?? '') ?>">
                  <span class="dg-custom-select-icon"><?php
                    $catIcon = trim((string)($cat['icon'] ?? ''));
                    echo '<i class="' . $h($catIcon !== '' ? $catIcon : 'fa-solid fa-tag') . '"></i>';
                  ?></span>
                  <span><?= $h($cat['name'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Brand</label>
            <div class="dg-brand-wrap" style="position:relative;">
              <div style="display:flex;align-items:center;gap:10px;">
                <div id="dgBrandIconWrap" style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                  <i class="fa-solid fa-tag" style="color:rgba(255,255,255,.25);font-size:.9rem;" id="dgBrandIconPlaceholder"></i>
                  <img id="dgBrandIconImg" src="" alt="" style="width:100%;height:100%;object-fit:contain;display:none;border-radius:9px;">
                </div>
                <input type="text" class="dg-form-input" name="brand" id="dgBrandInput" placeholder="Netflix, Spotify, Discord..." autocomplete="off" style="flex:1;">
              </div>
              <div id="dgBrandSuggestions" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:9999;background:#1e2028;border:1px solid rgba(139,60,247,.35);border-radius:12px;margin-top:4px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.5);max-height:420px;"></div>
            </div>
            <input type="hidden" name="brand_icon" id="dgBrandIconField" value="">
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Region availability</label>
            <input type="hidden" name="region" id="dgRegionField" value="Global">
            <select class="dg-form-input" id="dgRegionMode">
              <option value="all" selected>All regions</option>
              <option value="locked">Region locked</option>
            </select>
            <div id="dgRegionLockedWrap" style="display:none;margin-top:8px;">
              <select class="dg-form-input" id="dgRegionPreset">
                <option value="Europe">Europe</option>
                <option value="North America">North America</option>
                <option value="South America">South America</option>
                <option value="Asia">Asia</option>
                <option value="Oceania">Oceania</option>
                <option value="Africa">Africa</option>
                <option value="United States">United States</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="Germany">Germany</option>
                <option value="France">France</option>
                <option value="Turkey">Turkey</option>
                <option value="Brazil">Brazil</option>
                <option value="__custom">Custom region…</option>
              </select>
              <input type="text" class="dg-form-input" id="dgRegionCustom" placeholder="e.g. EU, DE, US, LATAM…" style="display:none;margin-top:8px;">
            </div>
            <div class="dg-hint">All regions = buyers from any region can use it. Region locked = only the selected region should buy.</div>
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Validity</label>
            <div class="dg-validity-picker" id="dgCreateValidityPicker">
              <div class="dg-vp-pills">
                <button type="button" class="dg-vp-pill" data-days="7">7 days</button>
                <button type="button" class="dg-vp-pill" data-days="14">14 days</button>
                <button type="button" class="dg-vp-pill" data-days="30">1 month</button>
                <button type="button" class="dg-vp-pill" data-days="90">3 months</button>
                <button type="button" class="dg-vp-pill" data-days="180">6 months</button>
                <button type="button" class="dg-vp-pill" data-days="365">12 months</button>
                <button type="button" class="dg-vp-pill" data-days="0">Lifetime</button>
                <button type="button" class="dg-vp-pill dg-vp-pill--custom" data-days="custom">Custom</button>
              </div>
              <div class="dg-vp-custom-wrap" style="display:none;">
                <input type="text" class="dg-vp-custom-input" placeholder="e.g. 12 hours, 3 days, 2 weeks…">
                <span class="dg-vp-custom-hint"></span>
              </div>
            </div>
            <input type="hidden" name="validity_days" id="dgCreateValidityDays" value="0">
          </div>
          <div class="col-12">
            <label class="dg-form-label">Description <span class="dg-required">*</span></label>
            <textarea class="dg-form-input" name="description" rows="4" required placeholder="Describe what the buyer receives, restrictions and important details."></textarea>
          </div>
          <div class="col-12">
            <label class="dg-form-label">Safety information</label>
            <textarea class="dg-form-input" name="safety_information" rows="3" placeholder="Example: This product can be revoked by the provider in rare cases. Spotify, for example, may revoke access."></textarea>
            <div class="dg-hint">Use this for revoke risks, account safety notes, warranty limits or important buyer warnings.</div>
          </div>
        </div>

        <div class="dg-section-label"><i class="fa-solid fa-euro-sign"></i> Price & Stock</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="dg-form-label">Price EUR <span class="dg-required">*</span></label>
            <input type="number" class="dg-form-input" name="price_display" id="dgPriceInput" min="0.01" step="0.01" required placeholder="9.99">
            <div class="dg-hint" id="dgNetHint">Net after fee: —</div>
          </div>
          <div class="col-md-4">
            <label class="dg-form-label">Stock <span class="dg-required">*</span></label>
            <div class="dg-stock-box">
              <div class="dg-stock-input-wrap" id="dgCreateStockWrap">
                <input type="number" class="dg-form-input" name="stock" id="dgCreateStock" min="1" step="1" value="1" required>
                <span class="dg-stock-infinity-badge"><i class="fa-solid fa-infinity"></i> Infinite</span>
              </div>
              <label class="dg-infinite-stock-toggle" for="dgCreateInfiniteStock">
                <span class="dg-infinite-stock-copy"><i class="fa-solid fa-infinity"></i> Infinite stock</span>
                <input type="checkbox" class="dg-infinite-stock-switch" id="dgCreateInfiniteStock">
              </label>
            </div>
          </div>
          <div class="col-md-2">
            <label class="dg-form-label">Min qty</label>
            <input type="number" class="dg-form-input" name="min_purchase_qty" min="1" step="1" value="1">
          </div>
          <div class="col-md-2">
            <label class="dg-form-label">Max qty</label>
            <input type="number" class="dg-form-input" name="max_purchase_qty" min="1" step="1" placeholder="—">
          </div>
        </div>

        <div class="dg-section-label"><i class="fa-solid fa-truck-fast"></i> Delivery</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="dg-form-label">Delivery type</label>
            <select class="dg-form-input" name="delivery_type" id="dgDeliveryType">
              <option value="manual">Manual delivery</option>
              <option value="auto">Automatic delivery</option>
            </select>
          </div>
          <div class="col-12">
            <label class="dg-form-label">Delivery instructions</label>
            <textarea class="dg-form-input" name="delivery_instructions" rows="4" placeholder="Private delivery instructions or redemption details for the buyer."></textarea>
          </div>
        </div>
      </div>
      <div class="dg-oc-footer">
        <button type="button" class="dg-secondary" data-bs-dismiss="offcanvas"><i class="fa-solid fa-xmark"></i> Cancel</button>
        <button type="submit" class="dg-submit" id="dgCreateSubmit"><i class="fa-solid fa-plus"></i> Create Listing</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ Edit Listing Offcanvas ══ -->
<div class="offcanvas offcanvas-end dg-oc" tabindex="-1" id="offcanvasEditDgListing" aria-labelledby="offcanvasEditDgListingLabel" data-bs-backdrop="static" data-bs-keyboard="true">
  <div class="offcanvas-header">
    <div>
      <h5 class="offcanvas-title mb-0" id="offcanvasEditDgListingLabel" style="font-weight:950;font-size:1.02rem;">Edit Listing</h5>
      <div style="font-size:.75rem;color:rgba(255,255,255,.4);margin-top:2px;" id="dgEditSubtitle">Update your offer details</div>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="dg-earnings-bar">
    <i class="fa-solid fa-coins" style="color:#c084fc;"></i>
    <span>Seller fee: <strong><?= number_format((float)$effective_fee, 1) ?>%</strong>. Your estimated net updates after changing the price.</span>
  </div>
  <div class="offcanvas-body">
    <form id="dgEditForm" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="action" value="seller_dg_update_listing">
      <input type="hidden" name="id" id="dgEditId" value="">
      <div class="dg-oc-scroll">

        <div class="dg-section-label"><i class="fa-solid fa-circle-info"></i> Product Details</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="dg-form-label">Title <span class="dg-required">*</span></label>
            <input type="text" class="dg-form-input" name="title" id="dgEditTitle" required placeholder="e.g. Netflix Premium 1 Month">
          </div>
          <div class="col-12">
            <label class="dg-form-label">Slug</label>
            <input type="text" class="dg-form-input" name="slug" id="dgEditSlug" placeholder="youtube-premium-12-months">
            <div class="dg-hint">Auto-generated from the title. You can customize it. Duplicates become -2, -3, etc.</div>
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Category <span class="dg-required">*</span></label>
            <input type="hidden" name="category_id" id="dgEditCategory" required>
            <div class="dg-custom-select" id="dgEditCategorySelect">
              <div class="dg-custom-select-trigger" id="dgEditCategoryTrigger">
                <span class="dg-custom-select-icon"><i class="fa-solid fa-layer-group"></i></span>
                <span class="dg-custom-select-text" id="dgEditCategoryText">Select category</span>
                <i class="fa-solid fa-chevron-down dg-custom-select-arrow"></i>
              </div>
              <div class="dg-custom-select-dropdown" id="dgEditCategoryDropdown">
                <?php foreach ($categories as $cat): ?>
                <div class="dg-custom-select-option" data-value="<?= (int)$cat['id'] ?>" data-name="<?= $h($cat['name'] ?? '') ?>">
                  <span class="dg-custom-select-icon"><?php
                    $catIcon = trim((string)($cat['icon'] ?? ''));
                    echo '<i class="' . $h($catIcon !== '' ? $catIcon : 'fa-solid fa-tag') . '"></i>';
                  ?></span>
                  <span><?= $h($cat['name'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Brand</label>
            <div class="dg-brand-wrap" style="position:relative;">
              <div style="display:flex;align-items:center;gap:10px;">
                <div id="dgEditBrandIconWrap" style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                  <i class="fa-solid fa-tag" style="color:rgba(255,255,255,.25);font-size:.9rem;" id="dgEditBrandIconPlaceholder"></i>
                  <img id="dgEditBrandIconImg" src="" alt="" style="width:100%;height:100%;object-fit:contain;display:none;border-radius:9px;">
                </div>
                <input type="text" class="dg-form-input" name="brand" id="dgEditBrand" placeholder="Netflix, Spotify, Discord..." autocomplete="off" style="flex:1;">
              </div>
              <div id="dgEditBrandSuggestions" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:9999;background:#1e2028;border:1px solid rgba(139,60,247,.35);border-radius:12px;margin-top:4px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.5);"></div>
            </div>
            <input type="hidden" name="brand_icon" id="dgEditBrandIconField" value="">
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Region availability</label>
            <input type="hidden" name="region" id="dgEditRegion" value="Global">
            <select class="dg-form-input" id="dgEditRegionMode">
              <option value="all">All regions</option>
              <option value="locked">Region locked</option>
            </select>
            <div id="dgEditRegionLockedWrap" style="display:none;margin-top:8px;">
              <select class="dg-form-input" id="dgEditRegionPreset">
                <option value="Europe">Europe</option>
                <option value="North America">North America</option>
                <option value="South America">South America</option>
                <option value="Asia">Asia</option>
                <option value="Oceania">Oceania</option>
                <option value="Africa">Africa</option>
                <option value="United States">United States</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="Germany">Germany</option>
                <option value="France">France</option>
                <option value="Turkey">Turkey</option>
                <option value="Brazil">Brazil</option>
                <option value="__custom">Custom region…</option>
              </select>
              <input type="text" class="dg-form-input" id="dgEditRegionCustom" placeholder="e.g. EU, DE, US, LATAM…" style="display:none;margin-top:8px;">
            </div>
            <div class="dg-hint">All regions stores this as Global. Region locked stores the selected region.</div>
          </div>
          <div class="col-md-6">
            <label class="dg-form-label">Validity</label>
            <div class="dg-validity-picker" id="dgEditValidityPicker">
              <div class="dg-vp-pills">
                <button type="button" class="dg-vp-pill" data-days="7">7 days</button>
                <button type="button" class="dg-vp-pill" data-days="14">14 days</button>
                <button type="button" class="dg-vp-pill" data-days="30">1 month</button>
                <button type="button" class="dg-vp-pill" data-days="90">3 months</button>
                <button type="button" class="dg-vp-pill" data-days="180">6 months</button>
                <button type="button" class="dg-vp-pill" data-days="365">12 months</button>
                <button type="button" class="dg-vp-pill" data-days="0">Lifetime</button>
                <button type="button" class="dg-vp-pill dg-vp-pill--custom" data-days="custom">Custom</button>
              </div>
              <div class="dg-vp-custom-wrap" style="display:none;">
                <input type="text" class="dg-vp-custom-input" placeholder="e.g. 12 hours, 3 days, 2 weeks…">
                <span class="dg-vp-custom-hint"></span>
              </div>
            </div>
            <input type="hidden" name="validity_days" id="dgEditValidity" value="0">
          </div>
          <div class="col-12">
            <label class="dg-form-label">Description <span class="dg-required">*</span></label>
            <textarea class="dg-form-input" name="description" id="dgEditDesc" rows="4" required placeholder="Describe what the buyer receives..."></textarea>
          </div>
          <div class="col-12">
            <label class="dg-form-label">Safety information</label>
            <textarea class="dg-form-input" name="safety_information" id="dgEditSafetyInfo" rows="3" placeholder="Example: This product can be revoked by the provider in rare cases. Spotify, for example, may revoke access."></textarea>
            <div class="dg-hint">Use this for revoke risks, account safety notes, warranty limits or important buyer warnings.</div>
          </div>
        </div>

        <div class="dg-section-label"><i class="fa-solid fa-euro-sign"></i> Price & Stock</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="dg-form-label">Price EUR <span class="dg-required">*</span></label>
            <input type="number" class="dg-form-input" name="price_display" id="dgEditPrice" min="0.01" step="0.01" required placeholder="9.99">
            <div class="dg-hint" id="dgEditNetHint">Net after fee: —</div>
          </div>
          <div class="col-md-4">
            <label class="dg-form-label">Stock <span class="dg-required">*</span></label>
            <div class="dg-stock-box">
              <div class="dg-stock-input-wrap" id="dgEditStockWrap">
                <input type="number" class="dg-form-input" name="stock" id="dgEditStock" min="0" step="1" required>
                <span class="dg-stock-infinity-badge"><i class="fa-solid fa-infinity"></i> Infinite</span>
              </div>
              <label class="dg-infinite-stock-toggle" for="dgEditInfiniteStock">
                <span class="dg-infinite-stock-copy"><i class="fa-solid fa-infinity"></i> Infinite stock</span>
                <input type="checkbox" class="dg-infinite-stock-switch" id="dgEditInfiniteStock">
              </label>
            </div>
          </div>
          <div class="col-md-2">
            <label class="dg-form-label">Min qty</label>
            <input type="number" class="dg-form-input" name="min_purchase_qty" id="dgEditMinQty" min="1" step="1" value="1">
          </div>
          <div class="col-md-2">
            <label class="dg-form-label">Max qty</label>
            <input type="number" class="dg-form-input" name="max_purchase_qty" id="dgEditMaxQty" min="1" step="1" placeholder="—">
          </div>
        </div>

        <div class="dg-section-label"><i class="fa-solid fa-truck-fast"></i> Delivery</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="dg-form-label">Delivery type</label>
            <select class="dg-form-input" name="delivery_type" id="dgEditDelivery">
              <option value="manual">Manual delivery</option>
              <option value="auto">Automatic delivery</option>
            </select>
          </div>
          <div class="col-12">
            <label class="dg-form-label">Delivery instructions</label>
            <textarea class="dg-form-input" name="delivery_instructions" id="dgEditDeliveryInstructions" rows="4" placeholder="Private delivery instructions or redemption details for the buyer."></textarea>
          </div>
        </div>

        <div class="dg-section-label"><i class="fa-solid fa-eye"></i> Visibility</div>
        <div class="row g-3">
          <div class="col-12">
            <div style="display:flex;align-items:center;gap:12px;">
              <input type="checkbox" name="active" id="dgEditActive" class="dg-toggle" value="1" checked>
              <label for="dgEditActive" style="font-size:.88rem;font-weight:750;color:rgba(255,255,255,.75);cursor:pointer;">Listing active (visible to buyers)</label>
            </div>
          </div>
        </div>

      </div>
      <div class="dg-oc-footer">
        <button type="button" class="dg-secondary" data-bs-dismiss="offcanvas"><i class="fa-solid fa-xmark"></i> Cancel</button>
        <button type="submit" class="dg-submit" id="dgEditSubmit"><i class="fa-solid fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var dgAjaxUrl = (typeof ajax_url !== 'undefined') ? ajax_url : '<?= AJAX_URL ?>';
  var sellerFee = <?= json_encode((float)$effective_fee) ?>;
  var form = document.getElementById('dgCreateForm');
  var submitBtn = document.getElementById('dgCreateSubmit');
  var priceInput = document.getElementById('dgPriceInput');
  var netHint = document.getElementById('dgNetHint');

  function toast(type, title, message){
    if (typeof create_toast === 'function') create_toast(type || 'primary', title || '', message || '');
    else if (message) console.log((title ? title + ': ' : '') + message);
  }
  function parseAjaxResponse(raw){
    if (raw && typeof raw === 'object') return raw;
    raw = String(raw || '').trim();
    if (!raw) return null;
    try { return JSON.parse(raw); } catch(e) {}
    var first = raw.indexOf('{');
    var last = raw.lastIndexOf('}');
    if (first >= 0 && last > first) {
      try { return JSON.parse(raw.slice(first, last + 1)); } catch(e) {}
    }
    return null;
  }
  function updateNet(){
    if (!priceInput || !netHint) return;
    var val = parseFloat(String(priceInput.value || '').replace(',', '.'));
    if (!isFinite(val) || val <= 0) { netHint.textContent = 'Net after fee: —'; return; }
    var net = val * (1 - (sellerFee / 100));
    netHint.textContent = 'Net after fee: €' + net.toFixed(2) + ' (' + sellerFee.toFixed(1) + '% fee)';
  }

  if (priceInput) priceInput.addEventListener('input', updateNet);

  function dgSlugify(value) {
    return String(value || '')
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '') || 'digital-good';
  }

  function dgBindSlugAuto(titleId, slugId) {
    var titleEl = document.getElementById(titleId);
    var slugEl = document.getElementById(slugId);
    if (!titleEl || !slugEl) return;
    var touched = false;
    slugEl.addEventListener('input', function() {
      touched = true;
      this.value = dgSlugify(this.value);
    });
    titleEl.addEventListener('input', function() {
      if (!touched || !slugEl.value.trim()) slugEl.value = dgSlugify(this.value);
    });
    return function(resetTouched) { touched = !!resetTouched; };
  }
  var dgCreateSlugTouched = dgBindSlugAuto('dgCreateTitle', 'dgCreateSlug');
  var dgEditSlugTouched = dgBindSlugAuto('dgEditTitle', 'dgEditSlug');


  /* ── Validity Picker ── */
  function parseValidityInput(str) {
    // Returns fractional days (e.g. 0.5 for 12 hours), or null if invalid
    str = String(str || '').trim().toLowerCase();
    if (!str) return null;
    // patterns: "12 hours", "1 hour", "2h", "3 days", "1 day", "2d", "1 week", "2 weeks", "1w"
    var m;
    m = str.match(/^(\d+(?:\.\d+)?)\s*(hour|hours|hr|hrs|h)$/);
    if (m) return Math.round(parseFloat(m[1]) / 24 * 10000) / 10000;
    m = str.match(/^(\d+(?:\.\d+)?)\s*(day|days|d)$/);
    if (m) return parseFloat(m[1]);
    m = str.match(/^(\d+(?:\.\d+)?)\s*(week|weeks|w)$/);
    if (m) return parseFloat(m[1]) * 7;
    m = str.match(/^(\d+(?:\.\d+)?)\s*(month|months|mo)$/);
    if (m) return Math.round(parseFloat(m[1]) * 30);
    m = str.match(/^(\d+(?:\.\d+)?)$/); // plain number = days
    if (m) return parseFloat(m[1]);
    return null;
  }
  function formatValidityPreview(days) {
    if (days === null) return '—';
    if (days <= 0) return 'Lifetime';
    if (days < 1) {
      var hours = Math.round(days * 24);
      return hours + (hours === 1 ? ' hour' : ' hours');
    }
    if (days < 7)  return days + (days === 1 ? ' day' : ' days');
    if (days < 30) { var w = Math.round(days/7); return w + (w===1?' week':' weeks'); }
    var mo = Math.round(days/30); return mo + (mo===1?' month':' months');
  }
  function initValidityPicker(pickerId, hiddenId, initialDays) {
    var picker = document.getElementById(pickerId);
    var hidden = document.getElementById(hiddenId);
    if (!picker || !hidden) return;
    var customWrap  = picker.querySelector('.dg-vp-custom-wrap');
    var customInput = picker.querySelector('.dg-vp-custom-input');
    var customHint  = picker.querySelector('.dg-vp-custom-hint');

    function setVal(days, isCustom) {
      // Store fractional days * 1440 as minutes in validity_days? No — keep as days (integer).
      // For hours: store as decimal fraction, view rounds. Or store 0 and show custom note.
      // We store ceiling of days so 12h → 1 day minimum (warn user).
      var stored = (days === null || days === 'custom') ? 0 : Math.max(0, parseFloat(days));
      // Round to nearest integer day (minimum 1 if > 0)
      stored = stored > 0 ? Math.max(1, Math.round(stored)) : 0;
      hidden.value = stored;

      if (!isCustom) {
        // Deactivate all, activate matching pill
        picker.querySelectorAll('.dg-vp-pill').forEach(function(p) {
          var pd = p.dataset.days;
          p.classList.toggle('active', pd !== 'custom' && parseInt(pd, 10) === parseInt(stored, 10));
        });
        if (customWrap) customWrap.style.display = 'none';
      }
    }

    picker.querySelectorAll('.dg-vp-pill').forEach(function(pill) {
      pill.addEventListener('click', function() {
        var d = this.dataset.days;
        // Deactivate all first
        picker.querySelectorAll('.dg-vp-pill').forEach(function(p){ p.classList.remove('active'); });
        this.classList.add('active');
        if (d === 'custom') {
          if (customWrap) { customWrap.style.display = 'flex'; }
          if (customInput) customInput.focus();
        } else {
          if (customWrap) customWrap.style.display = 'none';
          hidden.value = d;
        }
      });
    });

    if (customInput) {
      customInput.addEventListener('input', function() {
        var raw = this.value;
        var days = parseValidityInput(raw);
        if (days !== null) {
          var stored = days > 0 ? Math.max(1, Math.round(days)) : 0;
          hidden.value = stored;
          if (customHint) {
            var preview = stored === 0 ? 'Lifetime' : formatValidityPreview(stored) + ' (' + stored + 'd)';
            customHint.textContent = '→ ' + preview;
          }
        } else {
          if (customHint) customHint.textContent = raw.trim() ? '? invalid' : '';
        }
      });
    }

    // Init with value
    var initDays = parseInt(initialDays || 0, 10);
    var presetMatch = [0,7,14,30,90,180,365].indexOf(initDays) !== -1;
    if (presetMatch || initDays === 0) {
      setVal(initDays, false);
    } else {
      // Custom value — show custom field
      picker.querySelectorAll('.dg-vp-pill').forEach(function(p){ p.classList.remove('active'); });
      var customPill = picker.querySelector('.dg-vp-pill--custom');
      if (customPill) customPill.classList.add('active');
      if (customWrap) customWrap.style.display = 'flex';
      if (customInput) { customInput.value = initDays + ' days'; }
      if (customHint)  { customHint.textContent = '→ ' + initDays + ' days'; }
      hidden.value = initDays;
    }

    return function(days) { setVal(days, false); };
  }
  var setCreateValidity = initValidityPicker('dgCreateValidityPicker', 'dgCreateValidityDays', 0);
  var setEditValidity   = initValidityPicker('dgEditValidityPicker',   'dgEditValidity',        0);

  var dgCommonRegions = ['Europe','North America','South America','Asia','Oceania','Africa','United States','United Kingdom','Germany','France','Turkey','Brazil'];

  function dgSelectIconForOption(value, selectId) {
    value = String(value || '').toLowerCase();
    if (selectId && selectId.toLowerCase().indexOf('delivery') !== -1) {
      return value === 'auto' ? 'fa-solid fa-bolt' : 'fa-solid fa-paper-plane';
    }
    if (value === 'all') return 'fa-solid fa-globe';
    if (value === 'locked') return 'fa-solid fa-lock';
    if (value === '__custom') return 'fa-solid fa-pen';
    var regionIcons = {
      'europe':'fa-solid fa-earth-europe',
      'north america':'fa-solid fa-earth-americas',
      'south america':'fa-solid fa-earth-americas',
      'asia':'fa-solid fa-earth-asia',
      'oceania':'fa-solid fa-earth-oceania',
      'africa':'fa-solid fa-earth-africa',
      'united states':'fa-solid fa-flag-usa',
      'united kingdom':'fa-solid fa-flag',
      'germany':'fa-solid fa-flag',
      'france':'fa-solid fa-flag',
      'turkey':'fa-solid fa-flag',
      'brazil':'fa-solid fa-flag'
    };
    return regionIcons[value] || 'fa-solid fa-location-dot';
  }

  function dgRefreshCustomSelect(selectEl) {
    if (!selectEl) return;
    var wrap = selectEl._dgCustomSelectWrap;
    if (!wrap) return;
    var selected = selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
    var textEl = wrap.querySelector('.dg-select-text');
    var iconEl = wrap.querySelector('.dg-select-trigger .dg-select-icon');
    if (textEl) textEl.textContent = selected ? selected.textContent : '';
    if (iconEl) iconEl.innerHTML = '<i class="' + dgSelectIconForOption(selected ? selected.value : '', selectEl.id) + '"></i>';
    wrap.querySelectorAll('.dg-select-option').forEach(function(opt) {
      opt.classList.toggle('active', selected && opt.dataset.value === selected.value);
    });
  }

  function dgRefreshCustomSelectById(id) {
    dgRefreshCustomSelect(document.getElementById(id));
  }

  function dgInitNativeSelectAsCustom(selectId) {
    var selectEl = document.getElementById(selectId);
    if (!selectEl || selectEl._dgCustomSelectWrap) return;
    selectEl.classList.add('dg-native-select-hidden');

    var wrap = document.createElement('div');
    wrap.className = 'dg-select';
    wrap.setAttribute('data-for', selectId);
    wrap.innerHTML = '<div class="dg-select-trigger" tabindex="0" role="button" aria-haspopup="listbox" aria-expanded="false">' +
      '<span class="dg-select-icon"><i class="fa-solid fa-chevron-down"></i></span>' +
      '<span class="dg-select-text"></span>' +
      '<i class="fa-solid fa-chevron-down dg-select-arrow"></i>' +
      '</div><div class="dg-select-dropdown" role="listbox"></div>';

    var dropdown = wrap.querySelector('.dg-select-dropdown');
    Array.prototype.forEach.call(selectEl.options, function(option) {
      var item = document.createElement('div');
      item.className = 'dg-select-option';
      item.dataset.value = option.value;
      item.setAttribute('role', 'option');
      item.innerHTML = '<span class="dg-select-icon"><i class="' + dgSelectIconForOption(option.value, selectId) + '"></i></span>' +
                       '<span>' + option.textContent + '</span>';
      item.addEventListener('click', function() {
        selectEl.value = option.value;
        dgRefreshCustomSelect(selectEl);
        wrap.classList.remove('open');
        wrap.querySelector('.dg-select-trigger').setAttribute('aria-expanded','false');
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
      });
      dropdown.appendChild(item);
    });

    selectEl.insertAdjacentElement('afterend', wrap);
    selectEl._dgCustomSelectWrap = wrap;

    var trigger = wrap.querySelector('.dg-select-trigger');
    trigger.addEventListener('click', function(e) {
      e.stopPropagation();
      document.querySelectorAll('.dg-select.open').forEach(function(openWrap) { if (openWrap !== wrap) openWrap.classList.remove('open'); });
      var nextOpen = !wrap.classList.contains('open');
      wrap.classList.toggle('open', nextOpen);
      trigger.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
    });
    trigger.addEventListener('keydown', function(e) {
      var opts = Array.prototype.slice.call(selectEl.options);
      var idx = Math.max(0, selectEl.selectedIndex);
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); trigger.click(); }
      if (e.key === 'Escape') { wrap.classList.remove('open'); trigger.setAttribute('aria-expanded','false'); }
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        idx += e.key === 'ArrowDown' ? 1 : -1;
        idx = Math.max(0, Math.min(opts.length - 1, idx));
        selectEl.selectedIndex = idx;
        dgRefreshCustomSelect(selectEl);
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
    selectEl.addEventListener('change', function(){ dgRefreshCustomSelect(selectEl); });
    dgRefreshCustomSelect(selectEl);
  }


  function dgIsAllRegion(value) {
    value = String(value || '').trim().toLowerCase();
    return !value || value === 'global' || value === 'all regions' || value === 'all';
  }

  function dgSyncRegionPicker(prefix) {
    var hidden = document.getElementById(prefix + 'RegionField') || document.getElementById(prefix + 'Region');
    var mode = document.getElementById(prefix + 'RegionMode');
    var wrap = document.getElementById(prefix + 'RegionLockedWrap');
    var preset = document.getElementById(prefix + 'RegionPreset');
    var custom = document.getElementById(prefix + 'RegionCustom');
    if (!hidden || !mode) return;

    var locked = mode.value === 'locked';
    if (wrap) wrap.style.display = locked ? '' : 'none';
    if (!locked) {
      hidden.value = 'Global';
      if (custom) custom.style.display = 'none';
      return;
    }

    var useCustom = preset && preset.value === '__custom';
    if (custom) custom.style.display = useCustom ? '' : 'none';
    hidden.value = useCustom ? String(custom && custom.value ? custom.value : '').trim() : (preset ? preset.value : 'Europe');
    if (!hidden.value) hidden.value = 'Europe';
    dgRefreshCustomSelect(mode);
    dgRefreshCustomSelect(preset);
  }

  function dgSetRegionPicker(prefix, value) {
    var hidden = document.getElementById(prefix + 'RegionField') || document.getElementById(prefix + 'Region');
    var mode = document.getElementById(prefix + 'RegionMode');
    var preset = document.getElementById(prefix + 'RegionPreset');
    var custom = document.getElementById(prefix + 'RegionCustom');
    value = String(value || '').trim();
    if (!hidden || !mode) return;

    if (dgIsAllRegion(value)) {
      mode.value = 'all';
      hidden.value = 'Global';
      if (preset) preset.value = 'Europe';
      if (custom) custom.value = '';
    } else {
      mode.value = 'locked';
      if (preset) preset.value = dgCommonRegions.indexOf(value) !== -1 ? value : '__custom';
      if (custom) custom.value = dgCommonRegions.indexOf(value) !== -1 ? '' : value;
      hidden.value = value;
    }
    dgSyncRegionPicker(prefix);
  }

  function dgBindRegionPicker(prefix) {
    ['RegionMode','RegionPreset','RegionCustom'].forEach(function(suffix){
      var el = document.getElementById(prefix + suffix);
      if (el) el.addEventListener(suffix === 'RegionCustom' ? 'input' : 'change', function(){ dgSyncRegionPicker(prefix); });
    });
    dgSyncRegionPicker(prefix);
  }

  dgBindRegionPicker('dg');
  dgBindRegionPicker('dgEdit');

  ['dgRegionMode','dgRegionPreset','dgDeliveryType','dgEditRegionMode','dgEditRegionPreset','dgEditDelivery'].forEach(dgInitNativeSelectAsCustom);

  var search = document.getElementById('dgOfferSearch');
  if (search) {
    search.addEventListener('input', function(){
      var q = String(search.value || '').toLowerCase().trim();
      document.querySelectorAll('#dgOffersBody tr').forEach(function(row){
        row.style.display = (!q || String(row.dataset.search || '').indexOf(q) !== -1) ? '' : 'none';
      });
    });
  }

  $(document).on('change','.dg-active-toggle',function(){
    var id=$(this).data('id'), val=$(this).is(':checked')?1:0, el=this;
    $.ajax({url:dgAjaxUrl, method:'POST', data:{action:'seller_dg_toggle_listing',id:id,active:val}, dataType:'text'})
      .done(function(raw){
        var r=parseAjaxResponse(raw);
        if(!r || !r.success){ $(el).prop('checked', !val); toast('danger','Error',(r && (r.message || (r.sendToast && r.sendToast.message))) || 'Could not update status.'); }
        else { toast('success','Saved', val ? 'Offer activated.' : 'Offer deactivated.'); }
      }).fail(function(){ $(el).prop('checked', !val); toast('danger','Error','Could not reach the server.'); });
  });

  $(document).on('click','.dg-duplicate-btn',function(){
    var id=$(this).data('id'), name=$(this).data('name') || 'this offer';
    if(!confirm('Duplicate offer "'+name+'"? The copy will be inactive. You can edit or activate it manually.')) return;
    var $btn=$(this); $btn.prop('disabled',true);
    $.ajax({url:dgAjaxUrl, method:'POST', data:{action:'seller_dg_duplicate_listing',id:id}, dataType:'text'})
      .done(function(raw){
        var r=parseAjaxResponse(raw);
        if(r && r.success){
          toast('success','Duplicated',(r.sendToast && r.sendToast.message) || 'Offer duplicated.');
          window.location.href = r.redirectUrl || '<?= BASE_URL ?>/seller-area/digital-goods/listings';
        } else {
          $btn.prop('disabled',false);
          toast('danger','Error',(r && (r.message || (r.sendToast && r.sendToast.message))) || 'Could not duplicate offer.');
        }
      }).fail(function(){ $btn.prop('disabled',false); toast('danger','Error','Could not reach the server.'); });
  });

  $(document).on('click','.dg-delete-btn',function(){
    var id=$(this).data('id'), name=$(this).data('name');
    if(!confirm('Delete offer "'+name+'"? This cannot be undone.')) return;
    var $btn=$(this); $btn.prop('disabled',true);
    $.ajax({url:dgAjaxUrl, method:'POST', data:{action:'seller_dg_delete_listing',id:id}, dataType:'text'})
      .done(function(raw){
        var r=parseAjaxResponse(raw);
        if(r && r.success){ toast('success','Deleted',(r.sendToast && r.sendToast.message) || 'Offer deleted.'); $('#dg-row-'+id).fadeOut(220,function(){ $(this).remove(); }); }
        else { $btn.prop('disabled',false); toast('danger','Error',(r && (r.message || (r.sendToast && r.sendToast.message))) || 'Could not delete offer.'); }
      }).fail(function(){ $btn.prop('disabled',false); toast('danger','Error','Could not reach the server.'); });
  });

  if (form) {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        toast('danger','Missing information','Please check the required fields.');
        return;
      }
      dgSyncRegionPicker('dg');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
      if (document.getElementById('dgCreateInfiniteStock') && document.getElementById('dgCreateInfiniteStock').checked) {
        document.getElementById('dgCreateStock').value = String(DG_INFINITE_STOCK_VALUE);
      }
      var fd = new FormData(form);
      fd.set('safety_info', fd.get('safety_information') || '');
      $.ajax({url:dgAjaxUrl, method:'POST', data:fd, processData:false, contentType:false, dataType:'text'})
        .done(function(raw){
          var r = parseAjaxResponse(raw);
          if (!r) {
            toast('danger','Server response','Could not read the server response.');
            console.error('Digital goods create raw response:', raw);
            return;
          }
          if (r.sendToast) toast(r.sendToast.type || (r.success?'success':'danger'), r.sendToast.title || '', r.sendToast.message || '');
          if (r.success) {
            var oc = document.getElementById('offcanvasAddDgListing');
            if (oc) (bootstrap.Offcanvas.getInstance(oc) || bootstrap.Offcanvas.getOrCreateInstance(oc)).hide();
            setTimeout(function(){ window.location.href = r.redirectUrl || '<?= BASE_URL ?>/seller-area/digital-goods/listings'; }, 850);
          } else if (r.validationErrors) {
            console.warn('Validation errors:', r.validationErrors);
          }
        })
        .fail(function(xhr){
          toast('danger','Error','Could not reach the server. Please try again.');
          console.error('Digital goods create failed:', xhr && xhr.responseText ? xhr.responseText : xhr);
        })
        .always(function(){
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fa-solid fa-plus"></i> Create Listing';
        });
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    var params = new URLSearchParams(window.location.search);

    // Reset brand icon picker when Create offcanvas is hidden
    var createOcEl = document.getElementById('offcanvasAddDgListing');
    if (createOcEl) {
      createOcEl.addEventListener('hidden.bs.offcanvas', function() {
        var imgEl = document.getElementById('dgBrandIconImg');
        var placeholderEl = document.getElementById('dgBrandIconPlaceholder');
        var hiddenEl = document.getElementById('dgBrandIconField');
        if (imgEl) { imgEl.src=''; imgEl.style.display='none'; }
        if (placeholderEl) placeholderEl.style.display='block';
        if (hiddenEl) hiddenEl.value='';
        dgSetInfiniteStock('create', false);
        var createStockEl = document.getElementById('dgCreateStock');
        if (createStockEl) createStockEl.value = '1';
        var slugEl = document.getElementById('dgCreateSlug');
        if (slugEl) slugEl.value = '';
        if (typeof dgCreateSlugTouched === 'function') dgCreateSlugTouched(false);
        var sugEl = document.getElementById('dgBrandSuggestions');
        if (sugEl) sugEl.style.display='none';
        dgSetRegionPicker('dg', 'Global');
      });
    }

    if (params.get('add') === '1' || params.get('create') === '1') {
      var el = document.getElementById('offcanvasAddDgListing');
      if (el) bootstrap.Offcanvas.getOrCreateInstance(el).show();
    }

    var editId = params.get('edit_dg') || params.get('edit');
    if (editId) {
      setTimeout(function(){
        var btn = document.querySelector('.dg-edit-btn[data-id="' + CSS.escape(editId) + '"]');
        if (btn) btn.click();
      }, 120);
    }
  });

  /* ══════════════════════════════════════════════
     EDIT OFFCANVAS
  ══════════════════════════════════════════════ */
  var editForm         = document.getElementById('dgEditForm');
  var editSubmitBtn    = document.getElementById('dgEditSubmit');
  var editPriceInput   = document.getElementById('dgEditPrice');
  var editNetHint      = document.getElementById('dgEditNetHint');
  /* Net-after-fee hint for edit price */
  function updateEditNet() {
    if (!editPriceInput || !editNetHint) return;
    var val = parseFloat(String(editPriceInput.value || '').replace(',', '.'));
    if (!isFinite(val) || val <= 0) { editNetHint.textContent = 'Net after fee: —'; return; }
    var net = val * (1 - (sellerFee / 100));
    editNetHint.textContent = 'Net after fee: €' + net.toFixed(2) + ' (' + sellerFee.toFixed(1) + '% fee)';
  }
  if (editPriceInput) editPriceInput.addEventListener('input', updateEditNet);


  var DG_INFINITE_STOCK_VALUE = 999999;
  function dgIsInfiniteStockValue(value) {
    return parseInt(value || 0, 10) >= DG_INFINITE_STOCK_VALUE;
  }
  function dgStockDisplay(value) {
    return dgIsInfiniteStockValue(value) ? '∞ Infinite' : String(value || '0');
  }
  function dgSetInfiniteStock(mode, enabled) {
    var prefix = mode === 'edit' ? 'dgEdit' : 'dgCreate';
    var input = document.getElementById(prefix + 'Stock');
    var toggle = document.getElementById(prefix + 'InfiniteStock');
    var wrap = document.getElementById(prefix + 'StockWrap');
    if (!input || !toggle) return;
    toggle.checked = !!enabled;
    if (wrap) wrap.classList.toggle('is-infinite', !!enabled);
    input.readOnly = !!enabled;
    input.classList.toggle('is-infinite', !!enabled);
    if (enabled) {
      input.dataset.prevStockValue = input.value && !dgIsInfiniteStockValue(input.value) ? input.value : (input.dataset.prevStockValue || '1');
      input.value = String(DG_INFINITE_STOCK_VALUE);
    } else if (dgIsInfiniteStockValue(input.value)) {
      input.value = input.dataset.prevStockValue || (mode === 'edit' ? '' : '1');
    }
  }
  function dgBindInfiniteStock(mode) {
    var prefix = mode === 'edit' ? 'dgEdit' : 'dgCreate';
    var input = document.getElementById(prefix + 'Stock');
    var toggle = document.getElementById(prefix + 'InfiniteStock');
    if (!input || !toggle) return;
    toggle.addEventListener('change', function(){ dgSetInfiniteStock(mode, this.checked); });
    input.addEventListener('input', function(){
      if (!dgIsInfiniteStockValue(this.value)) this.dataset.prevStockValue = this.value || '';
    });
    dgSetInfiniteStock(mode, dgIsInfiniteStockValue(input.value));
  }
  dgBindInfiniteStock('create');
  dgBindInfiniteStock('edit');

  /* Open edit offcanvas and pre-fill fields */
  $(document).on('click', '.dg-edit-btn', function(){
    var id  = $(this).data('id');
    var row = document.getElementById('dg-row-' + id);
    if (!row) return;
    var d = row.dataset;

    // Reset edit state
    if (editForm) editForm.classList.remove('was-validated');

    // Pre-fill all fields
    document.getElementById('dgEditId').value    = d.id    || '';
    document.getElementById('dgEditTitle').value = d.title || '';
    document.getElementById('dgEditSlug').value = d.slug || dgSlugify(d.title || '');
    if (typeof dgEditSlugTouched === 'function') dgEditSlugTouched(false);
    document.getElementById('dgEditBrand').value = d.brand || '';
    dgSetRegionPicker('dgEdit', d.region || 'Global');
    document.getElementById('dgEditDesc').value  = d.description || '';
    document.getElementById('dgEditSafetyInfo').value = d.safetyInformation || '';
    document.getElementById('dgEditDeliveryInstructions').value = d.deliveryInstructions || '';
    document.getElementById('dgEditPrice').value = d.price || '';
    document.getElementById('dgEditStock').value = d.stock || '';
    dgSetInfiniteStock('edit', dgIsInfiniteStockValue(d.stock));
    document.getElementById('dgEditMinQty').value= d.minQty || '1';
    var maxQty = parseInt(d.maxQty||'0'); document.getElementById('dgEditMaxQty').value = maxQty > 0 ? maxQty : '';
    document.getElementById('dgEditValidity').value = (d.validity && d.validity !== '0') ? d.validity : '';
    if (typeof setEditValidity === 'function') setEditValidity(parseInt(d.validity || '0', 10));
    document.getElementById('dgEditActive').checked = d.active === '1';

    // Category
    var catSel = document.getElementById('dgEditCategory');
    if (catSel) {
      catSel.value = d.category || '';
      dgCustomSelectSetValue('dgEditCategorySelect', d.category || '');
    }

    // Delivery type
    var delSel = document.getElementById('dgEditDelivery');
    if (delSel) {
      delSel.value = d.delivery || 'manual';
      dgRefreshCustomSelect(delSel);
    }

    // Subtitle
    var sub = document.getElementById('dgEditSubtitle');
    if (sub) sub.textContent = (d.title || 'Listing') + ' — editing';

    updateEditNet();

    // Restore brand icon in edit form
    (function(){
      var imgEl       = document.getElementById('dgEditBrandIconImg');
      var placeholderEl = document.getElementById('dgEditBrandIconPlaceholder');
      var hiddenEl    = document.getElementById('dgEditBrandIconField');
      var brandIcon   = d.brandIcon || '';
      if (imgEl && placeholderEl) {
        if (brandIcon) {
          imgEl.src = dgBrandIconUrl(brandIcon);
          imgEl.style.display = 'block';
          placeholderEl.style.display = 'none';
          if (hiddenEl) hiddenEl.value = brandIcon;
        } else {
          // Try to auto-match by brand name
          var brandKey = (d.brand || '').trim().toLowerCase();
          var autoIcon = typeof dgBrandIcons !== 'undefined' ? (dgBrandIcons[brandKey] || null) : null;
          if (autoIcon) {
            imgEl.src = dgBrandIconUrl(autoIcon);
            imgEl.style.display = 'block';
            placeholderEl.style.display = 'none';
            if (hiddenEl) hiddenEl.value = autoIcon;
          } else {
            imgEl.style.display = 'none';
            placeholderEl.style.display = 'block';
            if (hiddenEl) hiddenEl.value = '';
          }
        }
      }
    })();

    // Open offcanvas
    var ocEl = document.getElementById('offcanvasEditDgListing');
    bootstrap.Offcanvas.getOrCreateInstance(ocEl).show();
  });

  /* Submit edit form */
  if (editForm) {
    editForm.addEventListener('submit', function(e){
      e.preventDefault();
      if (!editForm.checkValidity()) {
        editForm.classList.add('was-validated');
        toast('danger', 'Missing information', 'Please check the required fields.');
        return;
      }
      dgSyncRegionPicker('dgEdit');
      editSubmitBtn.disabled = true;
      editSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
      if (document.getElementById('dgEditInfiniteStock') && document.getElementById('dgEditInfiniteStock').checked) {
        document.getElementById('dgEditStock').value = String(DG_INFINITE_STOCK_VALUE);
      }
      var fd = new FormData(editForm);
      fd.set('safety_info', fd.get('safety_information') || '');
      if (!document.getElementById('dgEditActive').checked) fd.set('active','0');
      $.ajax({url:dgAjaxUrl, method:'POST', data:fd, processData:false, contentType:false, dataType:'text'})
        .done(function(raw){
          var r = parseAjaxResponse(raw);
          if (!r) { toast('danger','Error','Could not read server response.'); return; }
          if (r.sendToast) toast(r.sendToast.type||(r.success?'success':'danger'), r.sendToast.title||'', r.sendToast.message||'');
          if (r.success) {
            /* Update table row live */
            var id  = document.getElementById('dgEditId').value;
            var row = document.getElementById('dg-row-' + id);
            if (row) {
              var titleEl = row.querySelector('.dg-offer-title');
              var subEl   = row.querySelector('.dg-offer-sub');
              var priceEl = row.querySelector('.dg-money');
              var stockEl = row.querySelectorAll('td')[3];
              var togEl   = row.querySelector('.dg-active-toggle');
              if (titleEl) titleEl.textContent = document.getElementById('dgEditTitle').value;
              if (subEl)   subEl.innerHTML =
                '<span>' + (document.getElementById('dgEditBrand').value || 'No brand') + '</span>' +
                '<span>•</span>' +
                '<span>' + (document.getElementById('dgEditRegion').value || 'Global') + '</span>';
              if (priceEl) priceEl.textContent = '€' + parseFloat(document.getElementById('dgEditPrice').value).toFixed(2);
              if (stockEl) stockEl.textContent = dgStockDisplay(document.getElementById('dgEditStock').value);
              if (togEl)   togEl.checked = document.getElementById('dgEditActive').checked;
              /* Update data-* attrs so re-opening edit shows fresh values */
              row.dataset.title       = document.getElementById('dgEditTitle').value;
              row.dataset.slug        = document.getElementById('dgEditSlug').value;
              row.dataset.brand       = document.getElementById('dgEditBrand').value;
              row.dataset.region      = document.getElementById('dgEditRegion').value;
              row.dataset.description = document.getElementById('dgEditDesc').value;
              row.dataset.safetyInformation = document.getElementById('dgEditSafetyInfo').value;
              row.dataset.deliveryInstructions = document.getElementById('dgEditDeliveryInstructions').value;
              row.dataset.price       = document.getElementById('dgEditPrice').value;
              row.dataset.stock       = document.getElementById('dgEditStock').value;
              row.dataset.minQty      = document.getElementById('dgEditMinQty').value;
              row.dataset.maxQty      = document.getElementById('dgEditMaxQty').value || '0';
              row.dataset.validity    = document.getElementById('dgEditValidity').value || '0';
              row.dataset.category    = document.getElementById('dgEditCategory').value;
              row.dataset.delivery    = document.getElementById('dgEditDelivery').value;
              row.dataset.active      = document.getElementById('dgEditActive').checked ? '1' : '0';
            }
            var ocEl = document.getElementById('offcanvasEditDgListing');
            bootstrap.Offcanvas.getOrCreateInstance(ocEl).hide();
          }
        })
        .fail(function(xhr){
          toast('danger','Error','Could not reach the server. Please try again.');
          console.error('DG edit failed:', xhr && xhr.responseText ? xhr.responseText : xhr);
        })
        .always(function(){
          editSubmitBtn.disabled = false;
          editSubmitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Changes';
        });
    });
  }

  /* ══════════════════════════════════════════════
     BRAND ICON AUTO-PICKER
  ══════════════════════════════════════════════ */
  var DG_ASSET_URL = (typeof asset_url !== 'undefined') ? asset_url : '';
  var dgBrands = <?= json_encode($dgBrandsForJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var dgBrandIcons = {};
  var dgBrandLabels = {};
  var dgBrandDisplayKeys = [];
  function dgBrandNormalizeKey(value) {
    return String(value || '').trim().toLowerCase().replace(/[\s_-]+/g, ' ');
  }
  function dgBrandIconUrl(path) {
    path = String(path || '').trim();
    if (!path) return '';
    if (/^(https?:)?\/\//i.test(path) || path.indexOf('data:') === 0) return path;
    path = path.replace(/^\/public\/assets/i, '');
    return DG_ASSET_URL.replace(/\/$/, '') + '/' + path.replace(/^\/+/, '');
  }
  dgBrands.forEach(function (brand) {
    if (!brand || !brand.name || !brand.icon) return;
    var label = String(brand.name || '').trim();
    var keys = [label, brand.slug || ''];
    keys.forEach(function (rawKey) {
      var key = dgBrandNormalizeKey(rawKey);
      if (!key) return;
      dgBrandIcons[key] = brand.icon;
      dgBrandLabels[key] = label;
    });
    var displayKey = dgBrandNormalizeKey(label);
    if (displayKey && dgBrandDisplayKeys.indexOf(displayKey) === -1) dgBrandDisplayKeys.push(displayKey);
  });

  function dgSetBrandIcon(inputEl, imgEl, placeholderEl, hiddenEl, val) {
    var key = dgBrandNormalizeKey(val);
    var icon = dgBrandIcons[key] || null;
    if (icon) {
      imgEl.src = dgBrandIconUrl(icon);
      imgEl.style.display = 'block';
      placeholderEl.style.display = 'none';
      if (hiddenEl) hiddenEl.value = icon;
    } else {
      imgEl.style.display = 'none';
      placeholderEl.style.display = 'block';
      if (hiddenEl) hiddenEl.value = '';
    }
  }

  function dgBuildSuggestions(query) {
    var q = dgBrandNormalizeKey(query);
    if (!q) return dgBrandDisplayKeys.slice();
    // Only search display keys + their label to avoid duplicates
    return dgBrandDisplayKeys.filter(function(key) {
      var label = (dgBrandLabels[key] || key).toLowerCase();
      return key.indexOf(q) !== -1 || label.indexOf(q) !== -1;
    });
  }

  function dgRenderSuggestions(suggestionsEl, inputEl, imgEl, placeholderEl, hiddenEl, items) {
    suggestionsEl.innerHTML = '';
    if (!items) items = [];

    var searchWrap = document.createElement('div');
    searchWrap.style.cssText = 'position:sticky;top:0;z-index:2;padding:10px;background:#1e2028;border-bottom:1px solid rgba(255,255,255,.07);';
    var searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.placeholder = 'Search Brand...';
    searchInput.autocomplete = 'off';
    searchInput.style.cssText = 'width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:8px 12px;font-size:.86rem;outline:none;';
    searchWrap.appendChild(searchInput);

    var listWrap = document.createElement('div');
    listWrap.style.cssText = 'max-height:340px;overflow-y:auto;overscroll-behavior:contain;scrollbar-width:thin;';

    function drawList(listItems) {
      listWrap.innerHTML = '';
      if (!listItems.length) {
        var empty = document.createElement('div');
        empty.style.cssText = 'padding:12px 13px;color:rgba(255,255,255,.45);font-size:.86rem;';
        empty.textContent = 'No brands found';
        listWrap.appendChild(empty);
        return;
      }
      listItems.forEach(function(key) {
        var icon = dgBrandIcons[key];
        var label = dgBrandLabels[key] || key;
        var item = document.createElement('div');
        item.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 13px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.05);transition:background .1s;';
        item.innerHTML = (icon ? '<img src="' + dgBrandIconUrl(icon) + '" style="width:28px;height:28px;object-fit:contain;border-radius:6px;flex-shrink:0;" alt="">' : '<span style="width:28px;height:28px;border-radius:6px;background:rgba(255,255,255,.08);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;color:rgba(255,255,255,.5);"><i class="fa-solid fa-tag"></i></span>') +
                                 '<span style="font-size:.88rem;font-weight:800;color:#fff;">' + label + '</span>';
        item.addEventListener('mouseenter', function(){ this.style.background = 'rgba(139,60,247,.18)'; });
        item.addEventListener('mouseleave', function(){ this.style.background = ''; });
        item.addEventListener('mousedown', function(e) {
          e.preventDefault();
          inputEl.value = label;
          dgSetBrandIcon(inputEl, imgEl, placeholderEl, hiddenEl, key);
          suggestionsEl.style.display = 'none';
        });
        listWrap.appendChild(item);
      });
    }

    function filterItems(q) {
      q = dgBrandNormalizeKey(q);
      if (!q) return items.slice();
      return items.filter(function(key) {
        var label = (dgBrandLabels[key] || key).toLowerCase();
        return key.indexOf(q) !== -1 || label.indexOf(q) !== -1;
      });
    }

    searchInput.addEventListener('input', function(){ drawList(filterItems(this.value)); });
    searchInput.addEventListener('keydown', function(e){ if (e.key === 'Escape') suggestionsEl.style.display = 'none'; });

    suggestionsEl.appendChild(searchWrap);
    suggestionsEl.appendChild(listWrap);
    drawList(items);
    suggestionsEl.style.display = 'block';
  }

  function dgInitBrandPicker(inputId, imgId, placeholderId, hiddenId, suggestionsId) {
    var inputEl       = document.getElementById(inputId);
    var imgEl         = document.getElementById(imgId);
    var placeholderEl = document.getElementById(placeholderId);
    var hiddenEl      = document.getElementById(hiddenId);
    var suggestionsEl = document.getElementById(suggestionsId);
    if (!inputEl) return;

    inputEl.addEventListener('input', function() {
      var val = this.value;
      dgSetBrandIcon(inputEl, imgEl, placeholderEl, hiddenEl, val);
      var matches = dgBuildSuggestions(val);
      dgRenderSuggestions(suggestionsEl, inputEl, imgEl, placeholderEl, hiddenEl, matches);
    });
    inputEl.addEventListener('focus', function() {
      var matches = dgBuildSuggestions(this.value);
      dgRenderSuggestions(suggestionsEl, inputEl, imgEl, placeholderEl, hiddenEl, matches);
    });
    inputEl.addEventListener('blur', function() {
      setTimeout(function(){
        if (!suggestionsEl.contains(document.activeElement)) suggestionsEl.style.display = 'none';
      }, 180);
    });
  }

  // Init Create form brand picker
  dgInitBrandPicker('dgBrandInput','dgBrandIconImg','dgBrandIconPlaceholder','dgBrandIconField','dgBrandSuggestions');
  // Init Edit form brand picker
  dgInitBrandPicker('dgEditBrand','dgEditBrandIconImg','dgEditBrandIconPlaceholder','dgEditBrandIconField','dgEditBrandSuggestions');

  // When edit form opens, pre-fill the icon
  var _origEditClickHandler = $(document).data('dgEditClickBound');
  $(document).on('dg:edit:opened', function(e, data) {
    if (data && data.brandIcon) {
      var imgEl = document.getElementById('dgEditBrandIconImg');
      var placeholderEl = document.getElementById('dgEditBrandIconPlaceholder');
      var hiddenEl = document.getElementById('dgEditBrandIconField');
      if (imgEl && placeholderEl) {
        if (data.brandIcon) {
          imgEl.src = dgBrandIconUrl(data.brandIcon);
          imgEl.style.display = 'block';
          placeholderEl.style.display = 'none';
          if (hiddenEl) hiddenEl.value = data.brandIcon;
        }
      }
    }
  });

  /* ══════════════════════════════════════════════
     CUSTOM CATEGORY SELECT
  ══════════════════════════════════════════════ */
  function dgCustomSelectSetValue(selectId, value) {
    var wrap = document.getElementById(selectId);
    if (!wrap) return;
    var textEl = wrap.querySelector('.dg-custom-select-text');
    var iconEl = wrap.querySelector('.dg-custom-select-trigger .dg-custom-select-icon');
    var hiddenId = selectId === 'dgCategorySelect' ? 'dgCategoryField' : 'dgEditCategory';
    var hiddenEl = document.getElementById(hiddenId);
    var opts = wrap.querySelectorAll('.dg-custom-select-option');
    opts.forEach(function(opt) { opt.classList.remove('active'); });
    if (!value) {
      textEl.textContent = 'Select category';
      textEl.classList.remove('selected');
      iconEl.innerHTML = '<i class="fa-solid fa-layer-group"></i>';
      if (hiddenEl) hiddenEl.value = '';
      return;
    }
    opts.forEach(function(opt) {
      if (opt.dataset.value == value) {
        opt.classList.add('active');
        textEl.textContent = opt.dataset.name;
        textEl.classList.add('selected');
        iconEl.innerHTML = opt.querySelector('.dg-custom-select-icon').innerHTML;
        if (hiddenEl) hiddenEl.value = value;
      }
    });
  }

  function dgInitCustomSelect(selectId) {
    var wrap = document.getElementById(selectId);
    if (!wrap) return;
    var trigger = wrap.querySelector('.dg-custom-select-trigger');
    var opts = wrap.querySelectorAll('.dg-custom-select-option');
    trigger.addEventListener('click', function(e) {
      e.stopPropagation();
      // Close other open selects
      document.querySelectorAll('.dg-custom-select.open').forEach(function(el) {
        if (el !== wrap) el.classList.remove('open');
      });
      wrap.classList.toggle('open');
    });
    opts.forEach(function(opt) {
      opt.addEventListener('click', function() {
        dgCustomSelectSetValue(selectId, this.dataset.value);
        wrap.classList.remove('open');
      });
    });
  }

  // Close on outside click
  document.addEventListener('click', function() {
    document.querySelectorAll('.dg-custom-select.open').forEach(function(el) {
      el.classList.remove('open');
    });
  });

  dgInitCustomSelect('dgCategorySelect');
  dgInitCustomSelect('dgEditCategorySelect');


})();
</script>
<?= $this->stop() ?>
