<?php
/* Digital Goods View v4 — /digital-good/:slug */

$category       = is_array($category ?? null) ? $category : [];
$listing        = is_array($listing ?? null) ? $listing : [];
$images         = is_array($images ?? null) ? $images : [];
$reviews        = is_array($reviews ?? null) ? $reviews : [];
$sellerListings = is_array($sellerListings ?? null) ? $sellerListings : [];

$h = static fn($v): string => esc($v);

if (!function_exists('dg_mask_client_name')) {
    function dg_mask_client_name($name): string
    {
        $name = trim((string)($name ?? ''));
        if ($name === '') return 'User';

        $len = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
        $first = function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1);

        if ($len <= 1) return $first . '***';
        $last = function_exists('mb_substr') ? mb_substr($name, -1, 1, 'UTF-8') : substr($name, -1);

        return $first . '****' . $last;
    }
}

$assetUrl = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '';


if (!function_exists('lb_dg_display_currency')) {
    function lb_dg_display_currency(?string $currency = null): string
    {
        $currency = strtoupper(trim((string)($currency ?? ($_SESSION['currency'] ?? 'EUR'))));
        return in_array($currency, ['EUR', 'USD'], true) ? $currency : 'EUR';
    }
}

if (!function_exists('lb_dg_price_display_cents')) {
    function lb_dg_price_display_cents(int $eurCents, ?string $currency = null): int
    {
        $currency = lb_dg_display_currency($currency);
        if ($currency === 'USD') {
            $rate = function_exists('get_exchange_rate') ? (float)get_exchange_rate() : 1.0;
            if ($rate <= 0) $rate = 1.0;
            return (int)round($eurCents * $rate);
        }
        return $eurCents;
    }
}

if (!function_exists('lb_dg_format_display_price')) {
    function lb_dg_format_display_price(int $eurCents, ?string $currency = null): string
    {
        $currency = lb_dg_display_currency($currency);
        $displayCents = lb_dg_price_display_cents($eurCents, $currency);
        if (function_exists('dg_format_price')) {
            return dg_format_price($displayCents, $currency);
        }
        $symbol = $currency === 'USD' ? '$' : '€';
        return $symbol . number_format($displayCents / 100, 2, '.', ',');
    }
}


$normalizeAssetPath = static function ($path) use ($assetUrl): string {
    $path = trim((string)($path ?? ''));
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;
    $path = preg_replace('#^/public/assets#', '', $path);
    $path = preg_replace('#/public/assets/#', '/', $path);
    $path = '/' . ltrim((string)$path, '/');
    return $assetUrl . $path;
};

$brandIconUrl  = $normalizeAssetPath($listing['brand_icon'] ?? '');
$categoryIcon  = (string)($category['icon'] ?? 'fa-solid fa-layer-group');

if (empty($images)) {
    $images = [$brandIconUrl !== '' ? $brandIconUrl : ($assetUrl . '/public/uploads/icons/default2.png')];
}

$priceCents        = (int)($listing['price'] ?? 0); // stored as EUR cents
$stock             = max(0, (int)($listing['stock'] ?? 1));
$minQty            = max(1, (int)($listing['min_purchase_qty'] ?? 1));
$maxQtyRaw         = $listing['max_purchase_qty'] ?? null;
$maxQty            = $maxQtyRaw ? max($minQty, (int)$maxQtyRaw) : max($minQty, $stock);
$currency          = lb_dg_display_currency();
$displayPriceCents = lb_dg_price_display_cents($priceCents, $currency);
$priceFormatted    = lb_dg_format_display_price($priceCents, $currency);

// Smart validity display
function dg_format_validity(int $days): string {
    if ($days <= 0) return t('Lifetime');
    if ($days < 30) return $days . ' ' . t('days');
    $months = (int)round($days / 30);
    return $months === 1 ? t('1 month') : $months . ' ' . t('months');
}
$validity = dg_format_validity((int)($listing['validity_days'] ?? 0));
$region        = trim((string)($listing['region'] ?? '')) !== '' ? (string)$listing['region'] : t('Global');
$deliveryType  = trim((string)($listing['delivery_type'] ?? 'manual'));
$deliveryText  = ucfirst(str_replace('_', ' ', $deliveryType));
$avgRating     = round((float)($listing['avg_rating'] ?? 0), 1);
$reviewCount   = (int)($listing['review_count'] ?? 0);
$soldCount     = (int)($listing['sold_count'] ?? 0);

$sellerName = (string)($listing['seller_username'] ?? 'Seller');
$sellerIcon = (string)($listing['seller_icon'] ?? (defined('ICON_URL') ? ICON_URL . '/default.png' : ''));
$sellerSlug = trim((string)($listing['seller_slug'] ?? ''));
if ($sellerSlug === '' && function_exists('seller_profile_slug')) $sellerSlug = seller_profile_slug($sellerName);
if ($sellerSlug === '') $sellerSlug = $sellerName;
$sellerLink = rtrim(BASE_URL, '/') . '/sellers/' . rawurlencode($sellerSlug);
$sellerJoined = (string)($listing['seller_created_at'] ?? '');
$sellerJoinedYear = $sellerJoined !== '' ? date('Y', strtotime($sellerJoined)) : '';

$sellerTotalSold = (int)($sellerTotalSold ?? 0);
if ($sellerTotalSold === 0) {
    $sellerTotalSold = (int)($listing['sold_count'] ?? 0);
    foreach ($sellerListings as $sl) { $sellerTotalSold += (int)($sl['sold_count'] ?? 0); }
}


$categoryName  = (string)($category['name'] ?? 'Digital Goods');
$brandName     = trim((string)($listing['brand'] ?? ''));
$title         = (string)($listing['title'] ?? 'Digital Good');
$description   = (string)($listing['description'] ?? '');

// Seller direct chat variables for Digital Goods, account-view compatible
$dgSellerId = (int)($listing['seller_id'] ?? ($seller['id'] ?? 0));
$dgChatAllowed = $dgSellerId > 0 && (
    !array_key_exists('allow_chat_requests', $listing)
    || (int)($listing['allow_chat_requests'] ?? 1) === 1
);
$dgSellerInitials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sellerName) ?: 'S', 0, 2));
$dgAjaxUrl = defined('AJAX_URL') ? AJAX_URL : (defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/ajax' : '/ajax');
$dgBaseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$dgClientLoggedIn = defined('CLIENT_ID') && (int)CLIENT_ID > 0;
$dgChatRefType = 'digital_good';
$dgChatRefId = (int)($listing['id'] ?? 0);
$dgChatItemTitle = $title;
$dgChatItemType = trim($brandName !== '' ? $brandName : $categoryName);
$dgChatItemMeta = trim($region . ' · ' . $deliveryText);
$dgChatItemIcon = $brandIconUrl;
$deliveryInstr = (string)($listing['delivery_instructions'] ?? '');
$safetyInfo    = trim((string)($listing['safety_information'] ?? ($listing['safety_info'] ?? '')));
$features      = is_array($listing['features'] ?? null) ? $listing['features'] : [];
$isGlobal      = strtolower((string)($listing['region'] ?? '')) === 'global' || trim((string)($listing['region'] ?? '')) === '';
$isInstant     = $deliveryType === 'instant';

// Banner color from brand name
$bannerSeed = strtolower($brandName !== '' ? $brandName : $categoryName);
$bannerPalettes = [
    'youtube'     => ['#1a0000','#7f0000','#b91c1c'],
    'netflix'     => ['#0a0000','#7f0000','#991b1b'],
    'spotify'     => ['#001a0d','#14532d','#15803d'],
    'discord'     => ['#0d0f1f','#3730a3','#5865f2'],
    'twitch'      => ['#1a0033','#6d28d9','#9146ff'],
    'steam'       => ['#0a0e14','#1e3a5f','#1b73b4'],
    'xbox'        => ['#001a00','#14532d','#16a34a'],
    'playstation' => ['#00001a','#1e3a8a','#2563eb'],
    'amazon'      => ['#1a0d00','#92400e','#d97706'],
    'apple'       => ['#0a0a0a','#1c1c1e','#3a3a3c'],
    'google'      => ['#0d1117','#1e3a5f','#4285f4'],
    'microsoft'   => ['#001228','#1e3a5f','#0078d4'],
    'default'     => ['#0f0e27','#1e1b4b','#312e81'],
];
$bp = $bannerPalettes['default'];
foreach ($bannerPalettes as $k => $v) {
    if ($k !== 'default' && str_contains($bannerSeed, $k)) { $bp = $v; break; }
}
$bannerGrad = "linear-gradient(135deg,{$bp[0]} 0%,{$bp[1]} 45%,{$bp[2]} 100%)";

// ── DEMO REVIEWS: shown when no real reviews exist ──
if (empty($reviews)) {
    $demoAvatars = [
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Felix',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Aneka',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Zara',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Marco',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Lena',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Kevin',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Sofia',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Tyler',
        'https://api.dicebear.com/7.x/thumbs/svg?seed=Nina',
    ];
    $demoPool = [
        ['client_username'=>'xXGamer99',  'rating'=>5, 'comment'=>'Super fast delivery, worked perfectly. Will definitely buy again!',                'created_at'=>'2025-03-14'],
        ['client_username'=>'LenaM',      'rating'=>5, 'comment'=>'Exactly as described. Got it within minutes. 100% legit.',                        'created_at'=>'2025-02-28'],
        ['client_username'=>'TechDude42', 'rating'=>4, 'comment'=>'Smooth process, no issues. Would recommend to friends.',                          'created_at'=>'2025-01-19'],
        ['client_username'=>'SofiaK',     'rating'=>5, 'comment'=>'Best shop I have used so far. Fast and reliable!',                                'created_at'=>'2025-03-02'],
        ['client_username'=>'MarcoR88',   'rating'=>5, 'comment'=>'Worked instantly, great value for money. Seller was responsive too.',             'created_at'=>'2025-02-10'],
        ['client_username'=>'ZaraPl',     'rating'=>4, 'comment'=>'No problems at all, activated right away. Good stuff.',                           'created_at'=>'2025-01-05'],
        ['client_username'=>'KevinT',     'rating'=>5, 'comment'=>'Legit seller, quick delivery. Already ordered a second time.',                    'created_at'=>'2025-03-21'],
        ['client_username'=>'NinaW',      'rating'=>5, 'comment'=>'Could not be happier. Everything went smooth and support was helpful.',           'created_at'=>'2025-02-17'],
        ['client_username'=>'FelixB',     'rating'=>4, 'comment'=>'Good product, delivery was a bit slow but worked fine in the end.',               'created_at'=>'2025-01-30'],
    ];
    shuffle($demoPool);
    $demoCount = rand(5, 9);
    $reviews   = array_slice($demoPool, 0, $demoCount);
    foreach ($reviews as $i => &$rev) {
        $rev['client_icon'] = $demoAvatars[$i % count($demoAvatars)];
    }
    unset($rev);
    $avgRating   = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
    $reviewCount = count($reviews);
}
?>
<?= $this->layout('website/layouts/master', ['meta' => $meta ?? [], 'bodyClass' => 'view-account-page dg-view-page']) ?>

<?= $this->start('styles') ?>
<style>
/* ═══════════════════════════════════════════════════
   DG PRODUCT PAGE v4
═══════════════════════════════════════════════════ */
.dg-view-page { --acc:#6366f1; --acc2:#8b5cf6; --card-bg:#13121f; --border:rgba(255,255,255,.07); }

.dg-wrap { width:min(1400px,94vw); margin:0 auto; padding:24px 0 80px; }
.dg-breadcrumb{display:flex;align-items:center;gap:10px;margin:0 0 18px;color:rgba(255,255,255,.42);font-size:12px;font-weight:750;white-space:nowrap;overflow:hidden;}
.dg-breadcrumb a{color:rgba(255,255,255,.58);text-decoration:none;transition:color .16s ease;}
.dg-breadcrumb a:hover{color:#a5b4fc;}
.dg-breadcrumb i{font-size:8px;color:rgba(255,255,255,.24);flex:0 0 auto;}
.dg-breadcrumb span{color:#a5b4fc;overflow:hidden;text-overflow:ellipsis;}

/* ── TOP HERO STRIP ── */
.dg-hero {
  display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start;
  margin-bottom:0;
}
/* Left hero card */
.dg-hero__card {
  background:var(--card-bg); border:1px solid rgba(99,102,241,.2);
  border-radius:20px; overflow:hidden;
}
.dg-hero__banner {
  width:100%; height:160px; position:relative; overflow:hidden;
  display:flex; align-items:center; padding:0 28px; gap:22px;
  background:var(--banner-bg, <?= $bannerGrad ?>);
}
.dg-hero__banner::before {
  content:''; position:absolute; inset:0; pointer-events:none;
  background:
    radial-gradient(ellipse 60% 120% at 5% 50%, rgba(255,255,255,.09) 0%,transparent 60%),
    radial-gradient(ellipse 35% 70% at 95% 0%,  rgba(255,255,255,.06) 0%,transparent 55%),
    radial-gradient(ellipse 100% 50% at 50% 120%, rgba(0,0,0,.55)     0%,transparent 65%);
}
.dg-hero__banner-ring {
  position:absolute; border-radius:50%; pointer-events:none;
  border:1px solid rgba(255,255,255,.07);
}
.dg-hero__banner-ring:nth-child(1){width:240px;height:240px;right:-50px;top:-90px;}
.dg-hero__banner-ring:nth-child(2){width:130px;height:130px;right:120px;bottom:-60px;}
.dg-hero__banner-ring:nth-child(3){width:70px;height:70px;left:220px;top:10px;border-color:rgba(255,255,255,.04);}
/* Icon */
.dg-hero__icon {
  position:relative; z-index:2; flex-shrink:0;
  width:84px; height:84px; border-radius:22px; overflow:hidden;
  display:flex; align-items:center; justify-content:center;
  background:rgba(255,255,255,.12); border:2px solid rgba(255,255,255,.22);
  box-shadow:0 8px 28px rgba(0,0,0,.45), 0 0 0 5px rgba(255,255,255,.05);
}
.dg-hero__icon img { width:60px; height:60px; object-fit:contain; display:block; }
.dg-hero__icon i   { font-size:2.1rem; color:rgba(255,255,255,.85); }
/* Banner text overlay */
.dg-hero__banner-info { position:relative; z-index:2; }
.dg-hero__banner-cat  {
  font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em;
  color:rgba(255,255,255,.55); margin-bottom:5px; display:flex; align-items:center; gap:6px;
}
.dg-hero__banner-title { font-size:1.65rem; font-weight:900; color:#fff; line-height:1.2; }
.dg-hero__banner-meta  { display:flex; align-items:center; gap:10px; margin-top:8px; }
.dg-hero__banner-meta .dm { display:flex; align-items:center; gap:5px; font-size:.85rem; font-weight:700; color:rgba(255,255,255,.7); }
.dg-hero__banner-meta .dm i { font-size:.8rem; }
.dg-hero__banner-meta .dm--star { color:#fbbf24; }
.dg-hero__banner-meta .dm--dot  { width:3px;height:3px;border-radius:50%;background:rgba(255,255,255,.25); }
.dg-hero__banner-meta .dm--live { color:#4ade80; }

/* Pills row */
.dg-hero__pills { display:flex; flex-wrap:wrap; gap:7px; padding:14px 20px; border-top:1px solid rgba(255,255,255,.05); }
.dp { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:99px; font-size:.83rem; font-weight:700; white-space:nowrap; }
.dp i { font-size:.8rem; }
.dp--blue   { background:rgba(56,189,248,.1);   border:1px solid rgba(56,189,248,.22);  color:#7dd3fc; }
.dp--green  { background:rgba(74,222,128,.1);   border:1px solid rgba(74,222,128,.22);  color:#4ade80; }
.dp--yellow { background:rgba(251,191,36,.1);   border:1px solid rgba(251,191,36,.22);  color:#fde68a; }
.dp--purple { background:rgba(99,102,241,.1);   border:1px solid rgba(99,102,241,.22);  color:#a5b4fc; }
.dp--grey   { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.48); }
.dp--orange { background:rgba(251,146,60,.1);   border:1px solid rgba(251,146,60,.22);  color:#fdba74; }
.dp--red    { background:rgba(251,113,133,.1);  border:1px solid rgba(251,113,133,.22); color:#fda4af; }

/* Trust bar */
.dg-trustbar {
  display:grid; grid-template-columns:repeat(4,1fr); gap:0;
  background:var(--card-bg); border:1px solid var(--border);
  border-radius:16px; overflow:hidden; margin-top:8px;
}
.dg-tb {
  display:flex; align-items:center; gap:11px; padding:14px 16px;
  border-right:1px solid var(--border);
}
.dg-tb:last-child { border-right:0; }
.dg-tb__ico { font-size:1.15rem; flex-shrink:0; }
.dg-tb__ico--green  { color:#4ade80; }
.dg-tb__ico--blue   { color:#38bdf8; }
.dg-tb__ico--yellow { color:#fbbf24; }
.dg-tb__ico--purple { color:#a5b4fc; }
.dg-tb__label { font-size:.82rem; font-weight:800; color:rgba(255,255,255,.78); line-height:1.2; }
.dg-tb__sub   { font-size:.72rem; color:rgba(255,255,255,.3); font-weight:600; }

/* ── RIGHT SIDEBAR ── */
.dg-checkout-card {
  background:var(--card-bg); border:1px solid rgba(99,102,241,.22);
  border-radius:20px; overflow:hidden; position:relative; top:auto;
}
.dg-checkout-card::before {
  content:''; display:block; height:2px;
  background:linear-gradient(90deg,#6366f1,#8b5cf6,transparent);
}
.dg-co-body { padding:20px 20px 6px; }
/* Price */
.dg-co-price { font-size:2.6rem; font-weight:900; color:#fff; line-height:1; margin-bottom:18px; }
.dg-co-price small { font-size:1rem; color:rgba(255,255,255,.35); font-weight:600; margin-left:4px; }
/* Qty */
.dg-co-qty { display:flex; align-items:center; border:1px solid rgba(255,255,255,.1); border-radius:12px; overflow:hidden; background:rgba(0,0,0,.2); margin-bottom:14px; }
.dg-co-qty-btn { width:46px; height:46px; border:0; background:transparent; color:#fff; font-size:1.2rem; cursor:pointer; transition:background .12s; flex-shrink:0; }
.dg-co-qty-btn:hover { background:rgba(99,102,241,.22); }
.dg-co-qty-input { flex:1; height:46px; border:0; background:transparent; color:#fff; text-align:center; font-weight:900; font-size:1.1rem; outline:0; }
/* Buy button */
.dg-co-buy {
  display:flex; align-items:center; justify-content:center; gap:9px;
  width:100%; height:54px; border-radius:12px;
  background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff;
  border:0; font-weight:900; font-size:1.05rem; cursor:pointer;
  transition:opacity .15s,box-shadow .15s; margin-bottom:10px;
}
.dg-co-buy:hover { opacity:.9; box-shadow:0 8px 28px rgba(99,102,241,.4); }
.dg-co-buy:disabled { opacity:.4; cursor:not-allowed; }
/* Wishlist btn */
.dg-co-wish {
  display:flex; align-items:center; justify-content:center; gap:7px;
  width:100%; height:44px; border-radius:12px;
  background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09);
  color:rgba(255,255,255,.55); font-size:.92rem; font-weight:700; cursor:pointer;
  transition:background .12s,color .12s; margin-bottom:18px;
}
.dg-co-wish:hover { background:rgba(251,113,133,.1); border-color:rgba(251,113,133,.3); color:#fda4af; }
/* Sidebar quick facts */
.dg-co-facts { border-top:1px solid var(--border); }
.dg-co-fact { display:flex; align-items:center; justify-content:space-between; padding:11px 20px; border-bottom:1px solid var(--border); }
.dg-co-fact:last-child { border-bottom:0; }
.dg-co-fact__l { font-size:.83rem; color:rgba(255,255,255,.35); font-weight:700; display:flex; align-items:center; gap:7px; }
.dg-co-fact__l i { color:rgba(99,102,241,.6); font-size:.8rem; }
.dg-co-fact__v { font-size:.9rem; font-weight:800; color:rgba(255,255,255,.85); }
/* Delivery type pill */
.dg-co-dtype { display:flex; align-items:center; gap:7px; padding:10px 20px 14px; font-size:.88rem; font-weight:700; }
.dg-co-dtype--instant { color:#4ade80; }
.dg-co-dtype--manual  { color:#fbbf24; }

/* ── UNIFIED LAYOUT: single 2-col grid for everything ── */
.dg-layout {
  display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start;
}
.dg-layout__left  { min-width:0; }
.dg-layout__right { display:flex; flex-direction:column; gap:16px; align-self:start; }

/* Sidebar flows normally so checkout never overlaps the seller card */
.dg-checkout-card {
  position:relative;
  top:auto;
  z-index:1;
}

/* ── CONTENT CARDS ── */
.dg-card { background:var(--card-bg); border:1px solid var(--border); border-radius:18px; overflow:hidden; margin-bottom:16px; }
.dg-card:last-child { margin-bottom:0; }
.dg-card__head { display:flex; align-items:center; gap:10px; padding:13px 20px; border-bottom:1px solid var(--border); }
.dg-card__hi { width:28px;height:28px;border-radius:8px;flex-shrink:0;background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.22);display:flex;align-items:center;justify-content:center;font-size:.82rem;color:#818cf8; }
.dg-card__ht { font-size:.85rem; font-weight:900; text-transform:uppercase; letter-spacing:.07em; color:rgba(255,255,255,.38); }
.dg-card__he { margin-left:auto; font-size:.92rem; font-weight:800; color:#fbbf24; }
.dg-card__body { padding:20px 22px; }

/* Description */
.dg-desc { font-size:1rem; color:rgba(255,255,255,.75); line-height:1.85; white-space:pre-wrap; word-break:break-word; margin:0; }

/* Features grid */
.dg-features { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.dg-feature { display:flex; align-items:flex-start; gap:10px; padding:12px 14px; border-radius:12px; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); }
.dg-feature i { color:#4ade80; font-size:.9rem; margin-top:2px; flex-shrink:0; }
.dg-feature__t { font-size:.9rem; color:rgba(255,255,255,.72); font-weight:600; line-height:1.45; }

/* Redeem steps */
.dg-steps { display:flex; flex-direction:column; gap:12px; }
.dg-step { display:flex; gap:13px; align-items:flex-start; }
.dg-step__n { width:26px;height:26px;border-radius:50%;flex-shrink:0;background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.35);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:900;color:#a5b4fc; }
.dg-step__t { flex:1; font-size:.98rem; color:rgba(255,255,255,.76); line-height:1.7; padding-top:2px; }
.dg-plain { font-size:.98rem; color:rgba(255,255,255,.75); line-height:1.82; white-space:pre-wrap; word-break:break-word; margin:0; }

/* 3 fact boxes */
.dg-facts3 { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
.dg-fact { background:var(--card-bg); border:1px solid var(--border); border-radius:14px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
.dg-fact--warn { border-color:rgba(251,191,36,.28); background:rgba(251,191,36,.04); }
.dg-fact--ok   { border-color:rgba(74,222,128,.22); background:rgba(74,222,128,.04); }
.dg-ficon { width:40px;height:40px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.05rem; }
.dg-ficon--blue   { background:rgba(56,189,248,.12);  border:1px solid rgba(56,189,248,.2);  color:#7dd3fc; }
.dg-ficon--green  { background:rgba(74,222,128,.12);  border:1px solid rgba(74,222,128,.2);  color:#4ade80; }
.dg-ficon--yellow { background:rgba(251,191,36,.12);  border:1px solid rgba(251,191,36,.2);  color:#fde68a; }
.dg-ficon--purple { background:rgba(99,102,241,.12);  border:1px solid rgba(99,102,241,.2);  color:#a5b4fc; }
.dg-fact__lbl { font-size:.74rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.26);margin-bottom:3px; }
.dg-fact__val { font-size:1rem;font-weight:900;color:rgba(255,255,255,.88);line-height:1.2; }
.dg-fact--warn .dg-fact__val { color:#fde68a; }
.dg-fact--ok   .dg-fact__val { color:#4ade80; }

/* Region warning */
.dg-rwarn { display:flex;align-items:flex-start;gap:12px;padding:13px 16px;border-radius:13px;margin-bottom:16px;background:rgba(251,191,36,.05);border:1px solid rgba(251,191,36,.25); }
.dg-rwarn i { color:#fbbf24;font-size:1rem;flex-shrink:0;margin-top:2px; }
.dg-rwarn p { font-size:.93rem;line-height:1.72;color:rgba(255,255,255,.72);margin:0; }
.dg-rwarn strong { color:#fde68a;font-weight:800; }

/* Seller card in sidebar */
.dg-seller { background:var(--card-bg); border:1px solid rgba(99,102,241,.18); border-radius:18px; overflow:hidden; margin-top:0; }
.dg-seller__head { display:flex; align-items:center; gap:13px; padding:18px 18px 14px; }
.dg-seller__ava  { width:52px;height:52px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid rgba(99,102,241,.4);box-shadow:0 0 0 3px rgba(99,102,241,.07); }
.dg-seller__ph   { width:52px;height:52px;border-radius:50%;flex-shrink:0;background:rgba(99,102,241,.18);border:2px solid rgba(99,102,241,.3);display:flex;align-items:center;justify-content:center;color:#a5b4fc; }
.dg-seller__lbl  { font-size:.74rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.28);margin-bottom:3px; }
.dg-seller__name { font-size:1.05rem;font-weight:900;color:#fff;text-decoration:none;display:flex;align-items:center;gap:5px; }
.dg-seller__name:hover { color:#a5b4fc; }
.dg-seller__name i { font-size:.85rem;color:#6366f1; }
.dg-seller__since { font-size:.76rem;color:rgba(255,255,255,.3);font-weight:600;margin-top:2px; }
.dg-seller__actions { display:flex; gap:8px; padding:0 18px 14px; }
.dg-seller__btn { flex:1; display:flex;align-items:center;justify-content:center;gap:6px; height:38px;border-radius:10px;font-size:.83rem;font-weight:800;cursor:pointer;text-decoration:none;transition:background .12s,border-color .12s; }
.dg-seller__btn--chat { background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);color:#a5b4fc; }
.dg-seller__btn--chat:hover { background:rgba(99,102,241,.2); }
.dg-seller__btn--profile { background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.55); }
.dg-seller__btn--profile:hover { background:rgba(255,255,255,.08); }
.dg-seller__stats { display:grid;grid-template-columns:1fr 1fr;gap:0;border-top:1px solid var(--border); }
.dg-sstat { padding:12px 16px;display:flex;align-items:center;gap:9px;border-right:1px solid var(--border); }
.dg-sstat:last-child { border-right:0; }
.dg-sstat__ico { font-size:.9rem;color:rgba(99,102,241,.65);flex-shrink:0; }
.dg-sstat__val { font-size:1rem;font-weight:900;color:#fff;line-height:1; }
.dg-sstat__lbl { font-size:.7rem;color:rgba(255,255,255,.28);font-weight:700;text-transform:uppercase;margin-top:2px; }

/* Gallery */
.dg-gallery { display:flex;gap:8px;flex-wrap:wrap; }
.dg-gallery a { width:88px;height:62px;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:#0d0c1d;opacity:.7;transition:opacity .15s,border-color .15s;display:block; }
.dg-gallery a:hover { opacity:1;border-color:rgba(99,102,241,.55); }
.dg-gallery a img { width:100%;height:100%;object-fit:cover;display:block; }

/* ── REVIEWS SLIDER ── */
.dg-reviews-section { margin-top:48px; }
.dg-reviews-head { display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px; }
.dg-reviews-title { font-size:1.2rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:9px; }
.dg-reviews-title i { color:#fbbf24; }
.dg-reviews-rating { display:flex;align-items:center;gap:8px; }
.dg-reviews-stars { color:#fbbf24;font-size:1.1rem;letter-spacing:2px; }
.dg-reviews-avg { font-size:1.5rem;font-weight:900;color:#fff; }
.dg-reviews-count { font-size:.88rem;color:rgba(255,255,255,.35);font-weight:700; }
.dg-reviews-nav { display:flex;gap:8px; }
.dg-reviews-nav button { width:36px;height:36px;border-radius:999px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .12s,border-color .12s; }
.dg-reviews-nav button:hover { background:rgba(99,102,241,.2);border-color:rgba(99,102,241,.5); }
/* Slider track */
.dg-reviews-slider { overflow:hidden; }
.dg-reviews-track { display:flex;gap:14px;transition:transform .35s cubic-bezier(.4,0,.2,1); }
/* Review card */
.dg-rev {
  flex:0 0 calc(33.333% - 10px); min-width:0;
  background:var(--card-bg); border:1px solid var(--border);
  border-radius:16px; padding:18px 20px;
}
.dg-rev__top { display:flex;align-items:center;gap:10px;margin-bottom:12px; }
.dg-rev__ava {
  width:44px;
  height:44px;
  border-radius:50%;
  flex-shrink:0;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  background:linear-gradient(135deg,#536dfe 0%,#4f46e5 100%);
  border:1px solid rgba(255,255,255,.12);
  box-shadow:0 10px 26px rgba(79,70,229,.25), inset 0 1px 0 rgba(255,255,255,.14);
  color:#fff;
  font-size:1.05rem;
  font-weight:900;
  line-height:1;
  text-transform:uppercase;
}
.dg-rev__ava img{width:100%;height:100%;display:block;object-fit:cover;border-radius:inherit;}
.dg-rev__name { font-size:.93rem;font-weight:800;color:#fff;line-height:1.2; }
.dg-rev__date { font-size:.76rem;color:rgba(255,255,255,.28);font-weight:600;margin-top:2px; }
.dg-rev__stars-ml { margin-left:auto; }
.dg-rev__stars { color:#f5a623;font-size:.85rem;letter-spacing:1px; }
.dg-rev__body { font-size:.9rem;color:rgba(255,255,255,.62);line-height:1.72; }
/* Dots */
.dg-reviews-dots { display:flex;justify-content:center;gap:6px;margin-top:16px; }
.dg-reviews-dot { width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.15);border:0;cursor:pointer;transition:background .2s,width .2s;padding:0; }
.dg-reviews-dot.active { background:#6366f1;width:20px;border-radius:3px; }

/* Empty reviews */
.dg-no-reviews { text-align:center;padding:44px 20px;color:rgba(255,255,255,.25);font-size:.95rem;font-weight:700; }
.dg-no-reviews i { font-size:2rem;display:block;margin-bottom:10px;opacity:.3; }

/* ── MORE FROM SELLER ── */
.dg-more { padding:52px 0 60px; background:rgba(6,5,20,.6); border-top:1px solid rgba(255,255,255,.05); }
.dg-more__inner { width:min(1400px,94vw); margin:0 auto; }
.dg-section-head { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; }
.dg-section-title { font-size:1.25rem; font-weight:900; color:#fff; display:flex; align-items:center; gap:10px; }
.dg-section-title i { color:#6366f1; }
.dg-section-title a { color:#6366f1; text-decoration:none; }
.dg-section-title a:hover { color:#a5b4fc; }
.dg-section-nav { display:flex; gap:8px; }
.dg-section-nav button { width:36px; height:36px; border-radius:999px; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.05); color:#fff; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:background .12s,border-color .12s; flex-shrink:0; }
.dg-section-nav button:hover { background:rgba(99,102,241,.22); border-color:rgba(99,102,241,.5); }

/* Item cards — same design as shop.php .dg-card-new */
.dg-cards-row { display:grid; grid-auto-flow:column; grid-auto-columns:minmax(340px,390px); gap:22px; overflow-x:auto; overflow-y:hidden; scroll-snap-type:x proximity; scroll-behavior:smooth; padding:6px 4px 22px; scrollbar-width:none; }
.dg-cards-row::-webkit-scrollbar { display:none; }
@media(max-width:640px) {
  .dg-cards-row { grid-auto-columns:minmax(300px,86vw); gap:16px; }
  .dg-card-new__pills { grid-template-columns:1fr; }
}


.dg-card-new {
  scroll-snap-align:start; display:flex; flex-direction:column; text-decoration:none; color:#fff;
  border-radius:20px; overflow:hidden; position:relative;
  background:linear-gradient(180deg,rgba(255,255,255,.075),rgba(255,255,255,.028));
  border:1px solid rgba(255,255,255,.10);
  box-shadow:0 14px 40px rgba(0,0,0,.28),inset 0 1px 0 rgba(255,255,255,.06);
  transition:transform .2s cubic-bezier(.22,1,.36,1),box-shadow .2s,border-color .2s;
}
.dg-card-new::before {
  content:""; position:absolute; inset:-1px; z-index:1; pointer-events:none; opacity:0;
  background:radial-gradient(500px 160px at 18% 0%,rgba(34,211,238,.14),transparent 62%),
             radial-gradient(500px 160px at 88% 0%,rgba(217,70,239,.16),transparent 64%);
  transition:opacity .2s;
}
.dg-card-new:hover { transform:translateY(-5px); border-color:rgba(168,85,247,.42); box-shadow:0 24px 70px rgba(0,0,0,.42),0 0 0 1px rgba(168,85,247,.14),0 0 44px rgba(139,92,246,.14); }
.dg-card-new:hover::before { opacity:1; }

.dg-card-new__banner {
  width:100%; height:170px; position:relative; overflow:hidden; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.dg-card-new__banner::after {
  content:""; position:absolute; left:0; right:0; bottom:0; height:55%; z-index:1; pointer-events:none;
  background:linear-gradient(to top,rgba(5,5,15,.9) 0%,transparent 100%);
}
.dg-card-new__banner-rings { position:absolute; inset:0; pointer-events:none; }
.dg-card-new__banner-rings span { position:absolute; border-radius:50%; border:1px solid rgba(255,255,255,.07); }
.dg-card-new__banner-rings span:nth-child(1) { width:200px; height:200px; right:-50px; top:-80px; }
.dg-card-new__banner-rings span:nth-child(2) { width:100px; height:100px; left:20px; bottom:-50px; }
.dg-card-new__icon {
  position:relative; z-index:2;
  width:78px; height:78px; border-radius:22px; overflow:hidden;
  display:flex; align-items:center; justify-content:center;
  background:rgba(255,255,255,.12); border:2px solid rgba(255,255,255,.2);
  box-shadow:0 8px 24px rgba(0,0,0,.45);
}
.dg-card-new__icon img { width:54px; height:54px; object-fit:contain; display:block; }
.dg-card-new__icon i { font-size:2rem; color:rgba(255,255,255,.82); }
.dg-card-new__banner img.cover { width:100%; height:100%; object-fit:cover; display:block; transition:transform .32s ease; position:absolute; inset:0; }
.dg-card-new:hover .dg-card-new__banner img.cover { transform:scale(1.055); }
.dg-card-new__rating {
  position:absolute; bottom:10px; right:10px; z-index:3;
  display:inline-flex; align-items:center; gap:4px;
  padding:3px 8px; border-radius:99px;
  font-size:11px; font-weight:900; color:#fff;
  background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
  backdrop-filter:blur(4px);
}
.dg-card-new__rating i { color:#fbbf24; font-size:10px; }
.dg-card-new__rating span { color:rgba(255,255,255,.45); font-size:10px; }

.dg-card-new__body { padding:18px 20px 0; flex:1; min-width:0; }
.dg-card-new__brand { font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.1em; color:rgba(168,85,247,.8); margin-bottom:6px; }
.dg-card-new__title { font-size:17px; font-weight:950; color:#fff; line-height:1.3; letter-spacing:-.01em; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:14px; }
.dg-card-new__pills { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px; }
.dg-card-new__pill { display:inline-flex; align-items:center; justify-content:center; gap:5px; padding:7px 10px; border-radius:12px; font-size:11px; font-weight:850; white-space:nowrap; min-width:0; }
.dg-card-new__pill i { font-size:9px; }
.dg-card-new__pill--blue   { background:rgba(56,189,248,.1);   border:1px solid rgba(56,189,248,.2);   color:#7dd3fc; }
.dg-card-new__pill--green  { background:rgba(74,222,128,.1);   border:1px solid rgba(74,222,128,.2);   color:#4ade80; }
.dg-card-new__pill--yellow { background:rgba(251,191,36,.1);   border:1px solid rgba(251,191,36,.2);   color:#fde68a; }
.dg-card-new__pill--purple { background:rgba(99,102,241,.1);   border:1px solid rgba(99,102,241,.2);   color:#a5b4fc; }
.dg-card-new__pill--grey   { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.45); }
.dg-card-new__foot {
  display:flex; align-items:center; justify-content:space-between; gap:14px;
  padding:14px 20px 18px; border-top:1px solid rgba(255,255,255,.06); margin-top:auto;
}
.dg-card-new__seller { display:flex; align-items:center; gap:8px; min-width:0; flex:1; }
.dg-card-new__seller-ava { width:38px; height:38px; border-radius:50%; object-fit:cover; flex-shrink:0; border:1px solid rgba(99,102,241,.35); }
.dg-card-new__seller-ava--ph { display:inline-flex; align-items:center; justify-content:center; background:rgba(99,102,241,.18); color:#a5b4fc; font-size:.7rem; }
.dg-card-new__seller-name { font-size:12px; font-weight:850; color:rgba(255,255,255,.75); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px; }
.dg-card-new__seller-sold { font-size:10px; color:rgba(255,255,255,.3); font-weight:700; margin-top:1px; }
.dg-card-new__price-wrap { display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0; }
.dg-card-new__price { font-size:22px; font-weight:950; color:#fff; line-height:1; letter-spacing:-.02em; }
.dg-card-new__cta {
  display:inline-flex; align-items:center; gap:5px;
  padding:8px 14px; border-radius:999px;
  font-size:12px; font-weight:900; color:#fff; white-space:nowrap;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  box-shadow:0 6px 16px rgba(99,102,241,.32);
  transition:opacity .15s, box-shadow .15s;
}
.dg-card-new:hover .dg-card-new__cta { opacity:.9; box-shadow:0 8px 22px rgba(99,102,241,.45); }
.dg-card-new__cta i { font-size:9px; }

/* ── Category cards — identical to services hub ── */
.dg-view-page .sh-grid--dg { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:20px; }
.dg-view-page .sh-card {
  display:flex; flex-direction:column; text-decoration:none; color:#fff;
  border-radius:22px; overflow:hidden; position:relative;
  background:linear-gradient(180deg, rgba(255,255,255,.075), rgba(255,255,255,.028));
  border:1px solid rgba(255,255,255,.11);
  box-shadow:0 18px 60px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.06);
  transition:transform .20s cubic-bezier(.22,1,.36,1), box-shadow .20s ease, border-color .20s ease;
}
.dg-view-page .sh-card::before {
  content:""; position:absolute; inset:-1px; z-index:1; pointer-events:none; opacity:0;
  background:
    radial-gradient(500px 180px at 18% 0%, rgba(34,211,238,.16), transparent 62%),
    radial-gradient(500px 180px at 88% 0%, rgba(217,70,239,.18), transparent 64%);
  transition:opacity .20s ease;
}
.dg-view-page .sh-card:hover { transform:translateY(-5px); border-color:rgba(168,85,247,.42); box-shadow:0 28px 80px rgba(0,0,0,.42), 0 0 0 1px rgba(168,85,247,.14), 0 0 52px rgba(139,92,246,.14); }
.dg-view-page .sh-card:hover::before { opacity:1; }
.dg-view-page .sh-card__fa-wrap {
  width:100%; display:flex; align-items:center; justify-content:center;
  aspect-ratio:16/7; min-height:150px;
  background:
    radial-gradient(320px 160px at 50% 16%, rgba(217,70,239,.24), transparent 62%),
    linear-gradient(180deg, rgba(139,92,246,.28), rgba(8,7,18,.94));
}
.dg-view-page .sh-card__fa-wrap i {
  font-size:clamp(38px,3.8vw,54px); color:#bb4cf2;
  filter:drop-shadow(0 0 28px rgba(168,85,247,.54));
  transition:transform .24s ease, filter .24s ease;
}
.dg-view-page .sh-card:hover .sh-card__fa-wrap i { transform:scale(1.08) rotate(-3deg); filter:drop-shadow(0 0 42px rgba(217,70,239,.72)); }
.dg-view-page .sh-card__foot {
  position:relative; z-index:2;
  display:flex; align-items:center; justify-content:space-between; gap:8px;
  padding:10px 12px; min-height:46px;
  background:rgba(6,5,18,.76); border-top:1px solid rgba(255,255,255,.06);
}
.dg-view-page .sh-card__dg-icon {
  width:28px; height:28px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
  background:linear-gradient(180deg, rgba(139,92,246,.28), rgba(139,92,246,.14));
  border:1px solid rgba(168,85,247,.36);
}
.dg-view-page .sh-card__dg-icon i { font-size:13px; color:#d8b4fe; }
.dg-view-page .sh-card__name { font-size:13px; font-weight:950; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex:1; min-width:0; letter-spacing:-.015em; color:#fff; }
.dg-view-page .sh-card__arrow {
  width:28px; height:28px; border-radius:10px; flex-shrink:0;
  display:inline-flex; align-items:center; justify-content:center;
  font-size:11px; color:#e9d5ff;
  background:rgba(139,92,246,.22); border:1px solid rgba(168,85,247,.35);
  transition:transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.dg-view-page .sh-card:hover .sh-card__arrow { transform:translateX(2px); background:linear-gradient(135deg,rgba(139,92,246,.72),rgba(217,70,239,.55)); border-color:rgba(255,255,255,.20); box-shadow:0 0 22px rgba(168,85,247,.28); }

/* Cat section wrapper */
.dg-cat-section { padding:52px 0 60px; background:rgba(9,7,24,.8); border-top:1px solid rgba(255,255,255,.05); }
.dg-cat-section__inner { width:min(1400px,94vw); margin:0 auto; }

@media(max-width:1100px) { .dg-view-page .sh-grid--dg { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media(max-width:700px)  { .dg-view-page .sh-grid--dg { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:460px)  { .dg-view-page .sh-grid--dg { grid-template-columns:1fr; } }


/* Seller chat button should work as a button, not only a link */
.dg-seller__btn { border:0; font-family:inherit; }
.dg-seller__btn:disabled { opacity:.45; cursor:not-allowed; }

/* Account-style seller chat modal for Digital Goods */
.dg-lbc-overlay{position:fixed;inset:0;z-index:999999;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(4,6,18,.82);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px)}
.dg-lbc-overlay.is-open{display:flex}.dg-lbc-panel{width:min(620px,100%);max-height:calc(100vh - 32px);display:flex;flex-direction:column;border-radius:20px;background:#0d1226;border:1px solid rgba(109,92,255,.3);box-shadow:0 0 0 1px rgba(109,92,255,.12),0 32px 80px rgba(0,0,0,.7),0 8px 24px rgba(109,92,255,.12);overflow:hidden;color:#fff}.dg-lbc-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;background:linear-gradient(135deg,rgba(109,92,255,.4) 0%,rgba(99,60,220,.25) 100%);border-bottom:1px solid rgba(109,92,255,.25);flex-shrink:0}.dg-lbc-head-left{display:flex;align-items:center;gap:12px;min-width:0}.dg-lbc-avatar{width:44px;height:44px;border-radius:12px;flex-shrink:0;object-fit:cover;display:grid;place-items:center;background:linear-gradient(135deg,#6d5cff,#7c3aed);font-weight:900;font-size:15px;color:#fff;border:1.5px solid rgba(255,255,255,.18);box-shadow:0 4px 14px rgba(109,92,255,.45)}.dg-lbc-head-info{min-width:0}.dg-lbc-head-name{font-size:15px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}.dg-lbc-head-sub{font-size:11px;color:rgba(255,255,255,.55);margin-top:2px;display:flex;align-items:center;gap:5px}.dg-lbc-head-sub i{font-size:10px;color:#6d5cff}.dg-lbc-close{width:34px;height:34px;border-radius:10px;flex-shrink:0;border:0;background:rgba(255,255,255,.1);color:#fff;font-size:18px;font-weight:700;cursor:pointer;display:grid;place-items:center;transition:background .18s}.dg-lbc-close:hover{background:rgba(255,255,255,.2)}
.dg-lbc-item-strip{display:flex;align-items:center;gap:12px;padding:12px 20px;background:rgba(109,92,255,.07);border-bottom:1px solid rgba(109,92,255,.15);flex-shrink:0}.dg-lbc-item-icon{width:36px;height:36px;border-radius:10px;flex-shrink:0;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.25);display:grid;place-items:center;font-size:15px;color:#a5b4fc;overflow:hidden}.dg-lbc-item-icon img{width:24px;height:24px;object-fit:contain}.dg-lbc-item-meta{min-width:0;flex:1}.dg-lbc-item-title{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}.dg-lbc-item-tags{display:flex;gap:6px;margin-top:3px;flex-wrap:wrap}.dg-lbc-item-tag{font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;background:rgba(109,92,255,.18);border:1px solid rgba(109,92,255,.3);color:#c4b5fd}.dg-lbc-item-price{font-size:15px;font-weight:900;color:#fff;white-space:nowrap;flex-shrink:0}
.dg-lbc-guidelines{padding:14px 20px 16px;border-bottom:1px solid rgba(109,92,255,.18);background:rgba(109,92,255,.06);flex-shrink:0}.dg-lbc-guide-header{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:10px}.dg-lbc-guide-header i{color:#6d5cff;font-size:13px}.dg-lbc-guide-text{font-size:12.5px;color:rgba(255,255,255,.6);line-height:1.6;margin-bottom:12px}.dg-lbc-guide-cols{display:grid;grid-template-columns:1fr 1fr;gap:10px}.dg-lbc-guide-col{border-radius:10px;padding:11px 13px}.dg-lbc-guide-col--good{background:rgba(34,197,94,.09);border:1px solid rgba(34,197,94,.22)}.dg-lbc-guide-col--bad{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2)}.dg-lbc-guide-col-head{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:800;margin-bottom:9px;text-transform:uppercase;letter-spacing:.05em}.dg-lbc-guide-col--good .dg-lbc-guide-col-head{color:#4ade80}.dg-lbc-guide-col--bad .dg-lbc-guide-col-head{color:#f87171}.dg-lbc-guide-item{font-size:12.5px;color:rgba(255,255,255,.75);line-height:1.45;padding:2px 0}.dg-lbc-guide-col--bad .dg-lbc-guide-item{text-decoration:line-through;color:rgba(248,113,113,.65)}
.dg-lbc-body{flex:1;min-height:200px;max-height:320px;overflow-y:auto;padding:16px 20px;background:#080c1c;display:flex;flex-direction:column;gap:10px;scrollbar-width:thin;scrollbar-color:rgba(109,92,255,.3) transparent}.dg-lbc-empty{margin:auto;text-align:center;padding:16px 0;display:flex;flex-direction:column;align-items:center;gap:8px}.dg-lbc-empty-icon{width:52px;height:52px;border-radius:16px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.2);display:grid;place-items:center;font-size:22px;color:#6d5cff}.dg-lbc-empty-title{font-size:14px;font-weight:700;color:rgba(255,255,255,.7)}.dg-lbc-empty-sub{font-size:12px;color:rgba(255,255,255,.3)}.dg-lbc-msg{display:flex;gap:8px;align-items:flex-end}.dg-lbc-msg.me{flex-direction:row-reverse}.dg-lbc-msg-av{width:28px;height:28px;border-radius:9px;flex-shrink:0;background:linear-gradient(135deg,#6d5cff,#7c3aed);color:#fff;font-size:9px;font-weight:900;display:grid;place-items:center;overflow:hidden}.dg-lbc-msg-av img{width:100%;height:100%;object-fit:cover}.dg-lbc-msg-bubble{max-width:75%;padding:9px 13px;border-radius:16px 16px 16px 4px;background:rgba(109,92,255,.2);border:1px solid rgba(109,92,255,.25);font-size:13px;line-height:1.5;color:#fff;word-break:break-word}.dg-lbc-msg.me .dg-lbc-msg-bubble{border-radius:16px 16px 4px 16px;background:linear-gradient(135deg,rgba(109,92,255,.55),rgba(124,58,237,.45));border-color:rgba(109,92,255,.45)}.dg-lbc-msg-time{font-size:10px;color:rgba(255,255,255,.28);margin-top:3px}.dg-lbc-msg.me .dg-lbc-msg-time{text-align:right}.dg-lbc-msg-bubble img{max-width:190px;border-radius:10px;cursor:pointer;display:block;margin-top:4px}.dg-lbc-sys{display:flex;justify-content:center}.dg-lbc-sys-box{max-width:88%;padding:9px 14px;border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.62);font-size:12px;line-height:1.5;text-align:center;word-break:break-word}.dg-lbc-sys-box strong{color:rgba(255,255,255,.88)}.dg-lbc-sys-box a{color:#9d8cff;text-decoration:underline;word-break:break-all}.dg-lbc-sys-time{margin-top:4px;font-size:10px;color:rgba(255,255,255,.26)}
.dg-lbc-footer{padding:12px 16px 14px;background:#0a0e1e;border-top:1px solid rgba(109,92,255,.15);flex-shrink:0}.dg-lbc-preview{display:none;align-items:center;gap:8px;margin-bottom:10px;padding:7px 10px;border-radius:10px;background:rgba(109,92,255,.1);border:1px solid rgba(109,92,255,.2);color:rgba(255,255,255,.7)}.dg-lbc-preview.is-open{display:flex}.dg-lbc-preview img{width:38px;height:30px;object-fit:cover;border-radius:7px}.dg-lbc-preview small{flex:1;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.dg-lbc-preview-rm{border:0;background:rgba(255,255,255,.1);color:#fff;border-radius:7px;width:22px;height:22px;cursor:pointer;display:grid;place-items:center;font-size:14px}.dg-lbc-compose{display:flex;gap:8px;align-items:center}.dg-lbc-compose-input{flex:1;min-width:0;border:1px solid rgba(109,92,255,.25);background:rgba(255,255,255,.05);color:#fff;border-radius:999px;padding:11px 16px;font-size:13px;outline:none;transition:border-color .2s,background .2s}.dg-lbc-compose-input::placeholder{color:rgba(255,255,255,.3)}.dg-lbc-compose-input:focus{border-color:rgba(109,92,255,.65);background:rgba(109,92,255,.08)}.dg-lbc-img-btn,.dg-lbc-send-btn{border:0;display:grid;place-items:center;cursor:pointer;flex-shrink:0;border-radius:12px;transition:background .18s,filter .18s}.dg-lbc-img-btn{width:40px;height:40px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.45);font-size:15px}.dg-lbc-img-btn:hover{background:rgba(109,92,255,.2);color:#a5b4fc}.dg-lbc-send-btn{width:42px;height:42px;background:linear-gradient(135deg,#6d5cff,#7c3aed);color:#fff;font-size:15px;box-shadow:0 4px 14px rgba(109,92,255,.45)}.dg-lbc-send-btn:hover{filter:brightness(1.1)}.dg-lbc-send-btn:disabled{opacity:.38;cursor:not-allowed;box-shadow:none}.dg-lbc-floating{position:fixed;right:22px;bottom:22px;z-index:999998;width:52px;height:52px;border:0;border-radius:16px;background:linear-gradient(135deg,#6d5cff,#7c3aed);color:#fff;font-size:20px;box-shadow:0 12px 32px rgba(109,92,255,.5);cursor:pointer;display:grid;place-items:center;transition:transform .18s,box-shadow .18s}.dg-lbc-floating:hover{transform:translateY(-2px);box-shadow:0 16px 40px rgba(109,92,255,.6)}.dg-lbc-dot{position:absolute;right:-3px;top:-3px;width:12px;height:12px;border-radius:50%;background:#ef4444;border:2px solid #0d1226;display:none}body.dg-lbc-lock{overflow:hidden}
@media(max-width:660px){.dg-lbc-overlay{padding:0;align-items:flex-end}.dg-lbc-panel{width:100%;border-radius:20px 20px 0 0;max-height:93vh}.dg-lbc-body{max-height:45vh}.dg-lbc-guide-cols{grid-template-columns:1fr}.dg-lbc-floating{right:14px;bottom:84px}}

/* Sticky buy button (mobile) */
.dg-sticky { display:none; }

/* ── RESPONSIVE ── */
@media(max-width:1100px) {
  .dg-layout { grid-template-columns:1fr 320px; }
  .dg-co-facts { display:none; }
  .dg-rev { flex:0 0 calc(50% - 7px); }
}
@media(max-width:860px) {
  .dg-layout { grid-template-columns:1fr; }
  .dg-layout__right { position:static; }
  .dg-checkout-card { position:static; }
  .dg-hero__banner { height:140px; }
  .dg-hero__icon { width:68px;height:68px;border-radius:18px; }
  .dg-hero__icon img { width:48px;height:48px; }
  .dg-hero__banner-title { font-size:1.3rem; }
  .dg-facts3 { grid-template-columns:1fr 1fr; }
  .dg-trustbar { grid-template-columns:1fr 1fr; }
  .dg-tb:nth-child(2) { border-right:0; }
  .dg-tb:nth-child(3) { border-top:1px solid var(--border); }
  .dg-tb:nth-child(4) { border-top:1px solid var(--border); }
  .dg-rev { flex:0 0 calc(100% - 0px); }
  .dg-checkout-card { display:none; }
  .dg-sticky { display:block; }
}
@media(max-width:560px) {
  .dg-facts3 { grid-template-columns:1fr; }
  .dg-trustbar { grid-template-columns:1fr; }
  .dg-tb { border-right:0 !important; border-top:1px solid var(--border); }
  .dg-tb:first-child { border-top:0; }
  .dg-features { grid-template-columns:1fr; }
  .dg-seller__stats { grid-template-columns:1fr; }
  .dg-sstat { border-right:0 !important; border-bottom:1px solid var(--border); }
}


/* Cleaner price / CTA footer layout */
.digital-goods-shop-page .dg-card-new__foot,
.ranked-accounts-page .dg-card-new__foot,
.dg-view-page .dg-card-new__foot{
  flex-direction:column !important;
  align-items:stretch !important;
  justify-content:flex-start !important;
  gap:12px !important;
  padding:16px 20px 18px !important;
}
.digital-goods-shop-page .dg-card-new__seller,
.ranked-accounts-page .dg-card-new__seller,
.dg-view-page .dg-card-new__seller{
  width:100% !important;
  padding-bottom:12px !important;
  border-bottom:1px solid rgba(255,255,255,.065) !important;
}
.digital-goods-shop-page .dg-card-new__price-wrap,
.ranked-accounts-page .dg-card-new__price-wrap,
.dg-view-page .dg-card-new__price-wrap{
  width:100% !important;
  display:grid !important;
  grid-template-columns:1fr auto !important;
  align-items:center !important;
  gap:12px !important;
  padding:11px 12px !important;
  border-radius:15px !important;
  background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025)) !important;
  border:1px solid rgba(255,255,255,.08) !important;
}
.digital-goods-shop-page .dg-card-new__price,
.ranked-accounts-page .dg-card-new__price,
.dg-view-page .dg-card-new__price{
  display:flex !important;
  flex-direction:column !important;
  gap:3px !important;
  font-size:25px !important;
  line-height:1 !important;
  letter-spacing:-.03em !important;
}
.digital-goods-shop-page .dg-card-new__price::before,
.ranked-accounts-page .dg-card-new__price::before,
.dg-view-page .dg-card-new__price::before{
  content:"Price";
  font-size:10px !important;
  line-height:1 !important;
  font-weight:900 !important;
  letter-spacing:.11em !important;
  text-transform:uppercase !important;
  color:rgba(255,255,255,.34) !important;
}
.digital-goods-shop-page .dg-card-new__cta,
.ranked-accounts-page .dg-card-new__cta,
.dg-view-page .dg-card-new__cta{
  min-width:118px !important;
  height:42px !important;
  padding:0 17px !important;
  justify-content:center !important;
  border-radius:13px !important;
  font-size:12px !important;
  box-shadow:0 10px 24px rgba(99,102,241,.30) !important;
}
@media(max-width:430px){
  .digital-goods-shop-page .dg-card-new__price-wrap,
  .ranked-accounts-page .dg-card-new__price-wrap,
  .dg-view-page .dg-card-new__price-wrap{
    grid-template-columns:1fr !important;
  }
  .digital-goods-shop-page .dg-card-new__cta,
  .ranked-accounts-page .dg-card-new__cta,
  .dg-view-page .dg-card-new__cta{
    width:100% !important;
  }
}


/* Clean, minimal seller line (no heavy seller box) */
.digital-goods-shop-page .dg-card-new__seller,
.ranked-accounts-page .dg-card-new__seller,
.dg-view-page .dg-card-new__seller{
  width:100% !important;
  padding:0 2px 10px !important;
  border-bottom:1px solid rgba(255,255,255,.055) !important;
  display:flex !important;
  align-items:center !important;
  justify-content:space-between !important;
  gap:12px !important;
  background:transparent !important;
}
.digital-goods-shop-page .dg-card-new__seller > div,
.ranked-accounts-page .dg-card-new__seller > div,
.dg-view-page .dg-card-new__seller > div{
  min-width:0 !important;
  flex:1 1 auto !important;
}
.digital-goods-shop-page .dg-card-new__seller-ava,
.ranked-accounts-page .dg-card-new__seller-ava,
.dg-view-page .dg-card-new__seller-ava{
  width:34px !important;
  height:34px !important;
  border-radius:11px !important;
  border:1px solid rgba(99,102,241,.38) !important;
  box-shadow:0 8px 18px rgba(0,0,0,.25) !important;
}
.digital-goods-shop-page .dg-card-new__seller-name,
.ranked-accounts-page .dg-card-new__seller-name,
.dg-view-page .dg-card-new__seller-name{
  display:flex !important;
  align-items:center !important;
  gap:5px !important;
  max-width:none !important;
  font-size:13px !important;
  line-height:1.2 !important;
  color:rgba(255,255,255,.9) !important;
}
.digital-goods-shop-page .dg-card-new__seller-name::before,
.ranked-accounts-page .dg-card-new__seller-name::before,
.dg-view-page .dg-card-new__seller-name::before{
  content:"Seller" !important;
  flex:0 0 auto !important;
  padding:3px 7px !important;
  border-radius:999px !important;
  background:rgba(99,102,241,.13) !important;
  border:1px solid rgba(99,102,241,.22) !important;
  color:#a5b4fc !important;
  font-size:10px !important;
  font-weight:950 !important;
  letter-spacing:.06em !important;
  text-transform:uppercase !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold,
.ranked-accounts-page .dg-card-new__seller-sold,
.dg-view-page .dg-card-new__seller-sold{
  display:inline-flex !important;
  align-items:center !important;
  width:max-content !important;
  margin-top:4px !important;
  padding:2px 8px !important;
  border-radius:999px !important;
  background:rgba(74,222,128,.09) !important;
  border:1px solid rgba(74,222,128,.16) !important;
  color:#4ade80 !important;
  font-size:11px !important;
  font-weight:850 !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold::before,
.ranked-accounts-page .dg-card-new__seller-sold::before,
.dg-view-page .dg-card-new__seller-sold::before{
  content:"\f290";
  font-family:"Font Awesome 6 Free";
  font-weight:900;
  margin-right:5px;
  font-size:9px;
  opacity:.9;
}



/* FINAL Seller display: inline meta row, no seller box */
.digital-goods-shop-page .dg-card-new__foot,
.ranked-accounts-page .dg-card-new__foot,
.dg-view-page .dg-card-new__foot{
  background:linear-gradient(180deg,rgba(8,7,18,.22),rgba(8,7,18,.08)) !important;
  border-top:1px solid rgba(255,255,255,.06) !important;
  padding:14px 20px 18px !important;
  gap:11px !important;
}
.digital-goods-shop-page .dg-card-new__seller,
.ranked-accounts-page .dg-card-new__seller,
.dg-view-page .dg-card-new__seller{
  width:100% !important;
  min-height:0 !important;
  padding:0 !important;
  margin:0 !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  display:flex !important;
  align-items:center !important;
  justify-content:flex-start !important;
  gap:8px !important;
}
.digital-goods-shop-page .dg-card-new__seller-ava,
.ranked-accounts-page .dg-card-new__seller-ava,
.dg-view-page .dg-card-new__seller-ava{
  display:none !important;
}
.digital-goods-shop-page .dg-card-new__seller > div,
.ranked-accounts-page .dg-card-new__seller > div,
.dg-view-page .dg-card-new__seller > div{
  min-width:0 !important;
  flex:0 1 auto !important;
  display:flex !important;
  align-items:center !important;
  gap:7px !important;
}
.digital-goods-shop-page .dg-card-new__seller-name,
.ranked-accounts-page .dg-card-new__seller-name,
.dg-view-page .dg-card-new__seller-name{
  display:inline-flex !important;
  align-items:center !important;
  gap:5px !important;
  max-width:100% !important;
  color:rgba(255,255,255,.78) !important;
  font-size:12px !important;
  font-weight:850 !important;
  line-height:1 !important;
}
.digital-goods-shop-page .dg-card-new__seller-name::before,
.ranked-accounts-page .dg-card-new__seller-name::before,
.dg-view-page .dg-card-new__seller-name::before{
  content:"Sold by" !important;
  padding:0 !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  color:rgba(255,255,255,.34) !important;
  font-size:10px !important;
  font-weight:950 !important;
  letter-spacing:.09em !important;
  text-transform:uppercase !important;
}
.digital-goods-shop-page .dg-card-new__seller-name::after,
.ranked-accounts-page .dg-card-new__seller-name::after,
.dg-view-page .dg-card-new__seller-name::after{
  content:"";
  width:3px;
  height:3px;
  border-radius:999px;
  background:rgba(255,255,255,.25);
  margin-left:2px;
}
.digital-goods-shop-page .dg-card-new__seller-sold,
.ranked-accounts-page .dg-card-new__seller-sold,
.dg-view-page .dg-card-new__seller-sold{
  display:inline-flex !important;
  width:auto !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  color:#4ade80 !important;
  font-size:12px !important;
  font-weight:850 !important;
  line-height:1 !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold::before,
.ranked-accounts-page .dg-card-new__seller-sold::before,
.dg-view-page .dg-card-new__seller-sold::before{
  content:"" !important;
  display:none !important;
}
.digital-goods-shop-page .dg-card-new__price-wrap,
.ranked-accounts-page .dg-card-new__price-wrap,
.dg-view-page .dg-card-new__price-wrap{
  margin-top:2px !important;
}


/* Seller centered between price and Buy button */
.digital-goods-shop-page .dg-card-new__foot,
.ranked-accounts-page .dg-card-new__foot,
.dg-view-page .dg-card-new__foot{
  padding:14px 20px 18px !important;
  background:rgba(0,0,0,.12) !important;
}
.digital-goods-shop-page .dg-card-new__price-wrap,
.ranked-accounts-page .dg-card-new__price-wrap,
.dg-view-page .dg-card-new__price-wrap{
  display:grid !important;
  grid-template-columns:minmax(86px,auto) 1fr auto !important;
  align-items:center !important;
  gap:14px !important;
  width:100% !important;
  padding:12px 14px !important;
  border-radius:17px !important;
  background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.026)) !important;
  border:1px solid rgba(255,255,255,.085) !important;
}
.digital-goods-shop-page .dg-card-new__seller,
.ranked-accounts-page .dg-card-new__seller,
.dg-view-page .dg-card-new__seller{
  display:none !important;
}
.digital-goods-shop-page .dg-card-new__seller-mid,
.ranked-accounts-page .dg-card-new__seller-mid,
.dg-view-page .dg-card-new__seller-mid{
  min-width:0 !important;
  justify-self:center !important;
  display:flex !important;
  align-items:center !important;
  gap:9px !important;
  padding:6px 11px !important;
  border-radius:999px !important;
  background:rgba(255,255,255,.045) !important;
  border:1px solid rgba(255,255,255,.075) !important;
}
.digital-goods-shop-page .dg-card-new__seller-ava,
.ranked-accounts-page .dg-card-new__seller-ava,
.dg-view-page .dg-card-new__seller-ava{
  width:34px !important;
  height:34px !important;
  border-radius:11px !important;
  object-fit:cover !important;
  flex:0 0 34px !important;
  border:1px solid rgba(139,92,246,.45) !important;
  box-shadow:0 0 0 3px rgba(139,92,246,.08) !important;
}
.digital-goods-shop-page .dg-card-new__seller-copy,
.ranked-accounts-page .dg-card-new__seller-copy,
.dg-view-page .dg-card-new__seller-copy{
  min-width:0 !important;
}
.digital-goods-shop-page .dg-card-new__seller-name,
.ranked-accounts-page .dg-card-new__seller-name,
.dg-view-page .dg-card-new__seller-name{
  font-size:12px !important;
  font-weight:950 !important;
  color:rgba(255,255,255,.92) !important;
  max-width:135px !important;
  white-space:nowrap !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  line-height:1.15 !important;
}
.digital-goods-shop-page .dg-card-new__seller-name i,
.ranked-accounts-page .dg-card-new__seller-name i,
.dg-view-page .dg-card-new__seller-name i{
  font-size:.67rem !important;
  color:#6366f1 !important;
  margin-left:3px !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold,
.ranked-accounts-page .dg-card-new__seller-sold,
.dg-view-page .dg-card-new__seller-sold{
  margin-top:3px !important;
  display:block !important;
  font-size:10.5px !important;
  font-weight:850 !important;
  color:#4ade80 !important;
  line-height:1.05 !important;
  white-space:nowrap !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  max-width:150px !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold i,
.ranked-accounts-page .dg-card-new__seller-sold i,
.dg-view-page .dg-card-new__seller-sold i{
  font-size:9px !important;
  margin-right:4px !important;
}
.digital-goods-shop-page .dg-card-new__price,
.ranked-accounts-page .dg-card-new__price,
.dg-view-page .dg-card-new__price{
  min-width:0 !important;
}
.digital-goods-shop-page .dg-card-new__cta,
.ranked-accounts-page .dg-card-new__cta,
.dg-view-page .dg-card-new__cta{
  flex-shrink:0 !important;
}
@media(max-width:520px){
  .digital-goods-shop-page .dg-card-new__price-wrap,
  .ranked-accounts-page .dg-card-new__price-wrap,
  .dg-view-page .dg-card-new__price-wrap{
    grid-template-columns:1fr auto !important;
  }
  .digital-goods-shop-page .dg-card-new__seller-mid,
  .ranked-accounts-page .dg-card-new__seller-mid,
  .dg-view-page .dg-card-new__seller-mid{
    grid-column:1 / -1 !important;
    justify-self:stretch !important;
    justify-content:center !important;
  }
}



/* Final layout: price and Buy Now in one row, seller centered underneath */
.digital-goods-shop-page .dg-card-new__price-wrap,
.ranked-accounts-page .dg-card-new__price-wrap,
.dg-view-page .dg-card-new__price-wrap{
  display:grid !important;
  grid-template-columns:minmax(96px,1fr) auto !important;
  grid-template-areas:
    "price cta"
    "seller seller" !important;
  align-items:center !important;
  row-gap:10px !important;
  column-gap:14px !important;
  width:100% !important;
  padding:12px 14px 11px !important;
  border-radius:17px !important;
  background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.026)) !important;
  border:1px solid rgba(255,255,255,.085) !important;
}
.digital-goods-shop-page .dg-card-new__price,
.ranked-accounts-page .dg-card-new__price,
.dg-view-page .dg-card-new__price{
  grid-area:price !important;
  justify-self:start !important;
  align-self:center !important;
}
.digital-goods-shop-page .dg-card-new__cta,
.ranked-accounts-page .dg-card-new__cta,
.dg-view-page .dg-card-new__cta{
  grid-area:cta !important;
  justify-self:end !important;
  align-self:center !important;
  min-width:124px !important;
  height:40px !important;
}
.digital-goods-shop-page .dg-card-new__seller-mid,
.ranked-accounts-page .dg-card-new__seller-mid,
.dg-view-page .dg-card-new__seller-mid{
  grid-area:seller !important;
  justify-self:center !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  max-width:100% !important;
  padding:0 !important;
  border:0 !important;
  background:transparent !important;
  border-radius:0 !important;
}
.digital-goods-shop-page .dg-card-new__seller-mid::before,
.ranked-accounts-page .dg-card-new__seller-mid::before,
.dg-view-page .dg-card-new__seller-mid::before{
  content:"" !important;
  width:42px !important;
  height:1px !important;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.14)) !important;
}
.digital-goods-shop-page .dg-card-new__seller-mid::after,
.ranked-accounts-page .dg-card-new__seller-mid::after,
.dg-view-page .dg-card-new__seller-mid::after{
  content:"" !important;
  width:42px !important;
  height:1px !important;
  background:linear-gradient(90deg,rgba(255,255,255,.14),transparent) !important;
}
.digital-goods-shop-page .dg-card-new__seller-ava,
.ranked-accounts-page .dg-card-new__seller-ava,
.dg-view-page .dg-card-new__seller-ava{
  width:24px !important;
  height:24px !important;
  flex:0 0 24px !important;
  border-radius:50% !important;
  object-fit:cover !important;
  border:1px solid rgba(139,92,246,.45) !important;
  box-shadow:none !important;
}
.digital-goods-shop-page .dg-card-new__seller-copy,
.ranked-accounts-page .dg-card-new__seller-copy,
.dg-view-page .dg-card-new__seller-copy{
  display:flex !important;
  align-items:center !important;
  gap:7px !important;
  min-width:0 !important;
}
.digital-goods-shop-page .dg-card-new__seller-name,
.ranked-accounts-page .dg-card-new__seller-name,
.dg-view-page .dg-card-new__seller-name{
  font-size:11.5px !important;
  font-weight:950 !important;
  color:rgba(255,255,255,.88) !important;
  max-width:128px !important;
  line-height:1 !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold,
.ranked-accounts-page .dg-card-new__seller-sold,
.dg-view-page .dg-card-new__seller-sold{
  margin:0 !important;
  display:inline-flex !important;
  align-items:center !important;
  font-size:11px !important;
  font-weight:900 !important;
  color:#4ade80 !important;
  line-height:1 !important;
  white-space:nowrap !important;
  max-width:140px !important;
}
@media(max-width:520px){
  .digital-goods-shop-page .dg-card-new__price-wrap,
  .ranked-accounts-page .dg-card-new__price-wrap,
  .dg-view-page .dg-card-new__price-wrap{
    grid-template-columns:1fr auto !important;
  }
  .digital-goods-shop-page .dg-card-new__seller-mid::before,
  .ranked-accounts-page .dg-card-new__seller-mid::before,
  .dg-view-page .dg-card-new__seller-mid::before,
  .digital-goods-shop-page .dg-card-new__seller-mid::after,
  .ranked-accounts-page .dg-card-new__seller-mid::after,
  .dg-view-page .dg-card-new__seller-mid::after{
    width:24px !important;
  }
  .digital-goods-shop-page .dg-card-new__cta,
  .ranked-accounts-page .dg-card-new__cta,
  .dg-view-page .dg-card-new__cta{
    min-width:112px !important;
  }
}


/* FINAL override: seller meta line without verified/trusted label */
.digital-goods-shop-page .dg-card-new__seller-name::before,
.ranked-accounts-page .dg-card-new__seller-name::before,
.dg-view-page .dg-card-new__seller-name::before,
.digital-goods-shop-page .dg-card-new__seller-name::after,
.ranked-accounts-page .dg-card-new__seller-name::after,
.dg-view-page .dg-card-new__seller-name::after{
  content:none !important;
  display:none !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold::before,
.ranked-accounts-page .dg-card-new__seller-sold::before,
.dg-view-page .dg-card-new__seller-sold::before{
  content:none !important;
  display:none !important;
}



/* FINAL override: grey SOLD BY label + name + thumbs up + (sold count) */
.digital-goods-shop-page .dg-card-new__seller-copy,
.ranked-accounts-page .dg-card-new__seller-copy,
.dg-view-page .dg-card-new__seller-copy{
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:6px !important;
  flex-wrap:wrap !important;
  min-width:0 !important;
  text-align:center !important;
}
.digital-goods-shop-page .dg-card-new__seller-label,
.ranked-accounts-page .dg-card-new__seller-label,
.dg-view-page .dg-card-new__seller-label{
  color:rgba(255,255,255,.38) !important;
  font-size:10px !important;
  font-weight:950 !important;
  text-transform:uppercase !important;
  letter-spacing:.075em !important;
  line-height:1 !important;
}
.digital-goods-shop-page .dg-card-new__seller-name,
.ranked-accounts-page .dg-card-new__seller-name,
.dg-view-page .dg-card-new__seller-name{
  display:inline-flex !important;
  align-items:center !important;
  gap:0 !important;
  margin:0 !important;
  color:#fff !important;
  font-size:11px !important;
  font-weight:950 !important;
  line-height:1 !important;
  max-width:150px !important;
  white-space:nowrap !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold,
.ranked-accounts-page .dg-card-new__seller-sold,
.dg-view-page .dg-card-new__seller-sold{
  display:inline-flex !important;
  align-items:center !important;
  gap:3px !important;
  margin:0 !important;
  color:rgba(255,255,255,.54) !important;
  font-size:10.5px !important;
  font-weight:850 !important;
  line-height:1 !important;
  white-space:nowrap !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold i,
.ranked-accounts-page .dg-card-new__seller-sold i,
.dg-view-page .dg-card-new__seller-sold i{
  color:#4ade80 !important;
  font-size:10px !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold::before,
.ranked-accounts-page .dg-card-new__seller-sold::before,
.dg-view-page .dg-card-new__seller-sold::before,
.digital-goods-shop-page .dg-card-new__seller-name::before,
.ranked-accounts-page .dg-card-new__seller-name::before,
.dg-view-page .dg-card-new__seller-name::before,
.digital-goods-shop-page .dg-card-new__seller-name::after,
.ranked-accounts-page .dg-card-new__seller-name::after,
.dg-view-page .dg-card-new__seller-name::after{
  content:none !important;
  display:none !important;
}


/* Digital Goods readability + seller chat modal position fix */
.dg-view-page {
  font-size: 16px !important;
}
.dg-view-page .dg-wrap,
.dg-view-page .dg-more__inner,
.dg-view-page .dg-cat-section__inner {
  width: min(1420px, 94vw) !important;
}
.dg-view-page .dg-hero__banner-title {
  font-size: clamp(28px, 2vw, 36px) !important;
  line-height: 1.18 !important;
}
.dg-view-page .dg-hero__banner-cat {
  font-size: 14px !important;
}
.dg-view-page .dg-hero__banner-meta .dm,
.dg-view-page .dp {
  font-size: 14px !important;
}
.dg-view-page .dg-tb__label {
  font-size: 14px !important;
}
.dg-view-page .dg-tb__sub {
  font-size: 12px !important;
}
.dg-view-page .dg-fact__lbl,
.dg-view-page .dg-card__ht,
.dg-view-page .dg-co-fact__l,
.dg-view-page .dg-seller__lbl {
  font-size: 13px !important;
}
.dg-view-page .dg-fact__val,
.dg-view-page .dg-co-fact__v,
.dg-view-page .dg-sstat__val {
  font-size: 17px !important;
}
.dg-view-page .dg-desc,
.dg-view-page .dg-plain,
.dg-view-page .dg-step__t,
.dg-view-page .dg-feature__t,
.dg-view-page .dg-rwarn p {
  font-size: 17px !important;
  line-height: 1.85 !important;
}
.dg-view-page .dg-card__body {
  padding: 24px 26px !important;
}
.dg-view-page .dg-co-price {
  font-size: 44px !important;
}
.dg-view-page .dg-co-price small {
  font-size: 16px !important;
}
.dg-view-page .dg-co-qty-input,
.dg-view-page .dg-co-buy,
.dg-view-page .dg-co-wish,
.dg-view-page .dg-co-dtype,
.dg-view-page .dg-seller__btn {
  font-size: 16px !important;
}
.dg-view-page .dg-seller__name {
  font-size: 18px !important;
}
.dg-view-page .dg-seller__since,
.dg-view-page .dg-sstat__lbl {
  font-size: 12px !important;
}
.dg-view-page .dg-reviews-title,
.dg-view-page .dg-section-title {
  font-size: 24px !important;
}
.dg-view-page .dg-rev__name,
.dg-view-page .dg-rev__body {
  font-size: 15.5px !important;
}
.dg-view-page .dg-card-new__brand {
  font-size: 13px !important;
}
.dg-view-page .dg-card-new__title {
  font-size: 19px !important;
  line-height: 1.35 !important;
}
.dg-view-page .dg-card-new__pill {
  font-size: 13px !important;
}
.dg-view-page .dg-card-new__seller-name {
  font-size: 13px !important;
}
.dg-view-page .dg-card-new__seller-sold {
  font-size: 12px !important;
}
.dg-view-page .dg-card-new__price {
  font-size: 27px !important;
}
.dg-view-page .dg-card-new__cta {
  font-size: 14px !important;
}
.dg-view-page .sh-card__name {
  font-size: 15px !important;
}

/* Keep seller chat modal below sticky desktop navbar and make it easier to read */
.dg-view-page .dg-lbc-overlay {
  z-index: 2147483647 !important;
  align-items: flex-start !important;
  justify-content: center !important;
  padding: 118px 16px 24px !important;
}
.dg-view-page .dg-lbc-panel {
  width: min(720px, 100%) !important;
  max-height: calc(100vh - 148px) !important;
  border-radius: 22px !important;
}
.dg-view-page .dg-lbc-head-name {
  font-size: 18px !important;
}
.dg-view-page .dg-lbc-head-sub {
  font-size: 13px !important;
}
.dg-view-page .dg-lbc-item-title,
.dg-view-page .dg-lbc-item-price {
  font-size: 16px !important;
}
.dg-view-page .dg-lbc-item-tag {
  font-size: 12px !important;
}
.dg-view-page .dg-lbc-guide-header {
  font-size: 13px !important;
}
.dg-view-page .dg-lbc-guide-text,
.dg-view-page .dg-lbc-guide-item,
.dg-view-page .dg-lbc-msg-bubble,
.dg-view-page .dg-lbc-compose-input {
  font-size: 15px !important;
}
.dg-view-page .dg-lbc-body {
  max-height: 360px !important;
}
.dg-view-page .dg-lbc-compose-input {
  padding: 13px 18px !important;
}
.dg-view-page .dg-lbc-img-btn,
.dg-view-page .dg-lbc-send-btn {
  width: 46px !important;
  height: 46px !important;
  font-size: 17px !important;
}
@media (max-width: 860px) {
  .dg-view-page .dg-hero__banner-title { font-size: 24px !important; }
  .dg-view-page .dg-desc,
  .dg-view-page .dg-plain,
  .dg-view-page .dg-step__t,
  .dg-view-page .dg-feature__t { font-size: 16px !important; }
}
@media (max-width: 660px) {
  .dg-view-page .dg-lbc-overlay {
    align-items: flex-end !important;
    padding: 0 !important;
  }
  .dg-view-page .dg-lbc-panel {
    width: 100% !important;
    max-height: 93vh !important;
    border-radius: 20px 20px 0 0 !important;
  }
}

/* ═══════════════════════════════════════════════════
   DG PRODUCT PAGE CLEAN REDESIGN, compact marketplace view
   Keeps the existing PHP/data flow, removes the overloaded top card stack
═══════════════════════════════════════════════════ */
.dg-view-page{
  --dg-bg:#0e0c1c;
  --dg-card:rgba(19,18,31,.82);
  --dg-card2:rgba(13,13,24,.92);
  --dg-line:rgba(255,255,255,.085);
  --dg-line2:rgba(129,140,248,.28);
  --dg-muted:rgba(232,235,255,.56);
  --dg-soft:rgba(255,255,255,.045);
  background:#0e0c1c!important;
}
.dg-view-page header{
  min-height:0!important;
  padding-bottom:30px!important;
}
.dg-view-page header .content{
  max-width:1180px!important;
}
.dg-view-page header h1{
  font-size:clamp(32px,3vw,50px)!important;
  letter-spacing:.02em!important;
  margin-bottom:8px!important;
}
.dg-view-page header p{
  max-width:620px!important;
  margin-left:auto!important;
  margin-right:auto!important;
  color:rgba(255,255,255,.68)!important;
}
.dg-wrap{
  width:min(1180px,94vw)!important;
  padding:calc(var(--lb-content-top, 96px) + 10px) 0 80px!important;
}
.dg-layout{
  grid-template-columns:minmax(0,1fr) 330px!important;
  gap:18px!important;
  align-items:start!important;
}
.dg-layout__left,
.dg-layout__right{min-width:0!important}

/* The old trust/fact blocks made the top area feel repeated. Hide them and keep the information in hero + sidebar. */
.dg-trustbar,
.dg-facts3{
  display:none!important;
}

/* Product hero, one clean panel instead of many stacked blocks */
.dg-hero__card{
  position:relative!important;
  margin-bottom:16px!important;
  overflow:hidden!important;
  border-radius:24px!important;
  border:1px solid rgba(129,140,248,.22)!important;
  background:
    radial-gradient(900px 280px at 70% 0%,rgba(124,107,255,.22),transparent 62%),
    linear-gradient(180deg,rgba(255,255,255,.058),rgba(255,255,255,.024))!important;
  box-shadow:0 22px 70px rgba(0,0,0,.32),inset 0 1px 0 rgba(255,255,255,.06)!important;
}
.dg-hero__banner{
  min-height:214px!important;
  height:auto!important;
  padding:34px 36px 30px!important;
  align-items:flex-end!important;
  gap:22px!important;
  background:var(--banner-bg, <?= $bannerGrad ?>)!important;
}
.dg-hero__banner::before{
  background:
    radial-gradient(460px 240px at 18% 20%,rgba(255,255,255,.09),transparent 68%),
    radial-gradient(520px 240px at 88% 14%,rgba(124,107,255,.18),transparent 70%),
    linear-gradient(180deg,rgba(0,0,0,.08),rgba(6,5,17,.42))!important;
}
.dg-hero__banner::after{
  content:""!important;
  position:absolute!important;
  left:0!important;
  right:0!important;
  bottom:0!important;
  height:42%!important;
  pointer-events:none!important;
  background:linear-gradient(to top,rgba(10,9,22,.72),transparent)!important;
}
.dg-hero__banner-ring:nth-child(1){width:330px!important;height:330px!important;right:-95px!important;top:-126px!important}
.dg-hero__banner-ring:nth-child(2){width:180px!important;height:180px!important;right:160px!important;bottom:-96px!important}
.dg-hero__banner-ring:nth-child(3){width:105px!important;height:105px!important;left:260px!important;top:18px!important}
.dg-hero__icon{
  width:86px!important;
  height:86px!important;
  border-radius:24px!important;
  border:1px solid rgba(255,255,255,.20)!important;
  background:rgba(255,255,255,.12)!important;
  box-shadow:0 18px 44px rgba(0,0,0,.42),0 0 0 8px rgba(255,255,255,.035)!important;
}
.dg-hero__icon img{width:60px!important;height:60px!important}
.dg-hero__banner-info{
  min-width:0!important;
  max-width:780px!important;
}
.dg-hero__banner-cat{
  margin-bottom:8px!important;
  font-size:.72rem!important;
  letter-spacing:.13em!important;
  color:rgba(195,204,255,.72)!important;
}
.dg-hero__banner-title{
  font-size:clamp(26px,2.3vw,38px)!important;
  line-height:1.12!important;
  letter-spacing:-.035em!important;
  text-shadow:0 18px 46px rgba(0,0,0,.45)!important;
}
.dg-hero__banner-meta{
  margin-top:10px!important;
}
.dg-hero__banner-meta .dm{
  font-size:.82rem!important;
  color:rgba(255,255,255,.72)!important;
}
.dg-hero__pills{
  position:relative!important;
  z-index:3!important;
  padding:14px 18px!important;
  gap:8px!important;
  border-top:1px solid rgba(255,255,255,.07)!important;
  background:rgba(9,8,21,.52)!important;
  backdrop-filter:blur(14px)!important;
}
.dp{
  height:34px!important;
  padding:0 13px!important;
  border-radius:999px!important;
  font-size:.78rem!important;
  font-weight:850!important;
}

/* Main info cards, flatter and calmer */
.dg-card{
  border-radius:18px!important;
  border-color:rgba(255,255,255,.075)!important;
  background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.018))!important;
  box-shadow:none!important;
  margin-bottom:12px!important;
}
.dg-card__head{
  padding:13px 18px!important;
  background:rgba(255,255,255,.015)!important;
}
.dg-card__body{
  padding:18px 20px!important;
}
.dg-card__ht{
  font-size:.78rem!important;
  color:rgba(255,255,255,.46)!important;
}
.dg-desc,
.dg-plain{
  color:rgba(255,255,255,.72)!important;
  line-height:1.75!important;
}

/* Checkout sidebar, cleaner and less boxy */
.dg-layout__right{
  gap:14px!important;
}
.dg-checkout-card{
  border-radius:22px!important;
  border:1px solid rgba(129,140,248,.20)!important;
  background:
    radial-gradient(280px 140px at 85% 0%,rgba(124,107,255,.16),transparent 72%),
    linear-gradient(180deg,rgba(255,255,255,.060),rgba(255,255,255,.022))!important;
  box-shadow:0 20px 62px rgba(0,0,0,.30)!important;
}
.dg-checkout-card::before{
  height:0!important;
}
.dg-co-dtype{
  padding:16px 18px 0!important;
  font-size:.82rem!important;
  line-height:1.4!important;
}
.dg-co-body{
  padding:18px!important;
}
.dg-co-price{
  margin:6px 0 18px!important;
  font-size:2.35rem!important;
  letter-spacing:-.045em!important;
}
.dg-co-price small{
  font-size:.76rem!important;
  opacity:.65!important;
}
.dg-co-qty{
  height:44px!important;
  border-radius:14px!important;
  background:rgba(0,0,0,.20)!important;
}
.dg-co-qty-btn,
.dg-co-qty-input{
  height:44px!important;
}
.dg-co-buy{
  height:50px!important;
  border-radius:14px!important;
  margin-bottom:10px!important;
}
.dg-co-wish{
  height:40px!important;
  border-radius:12px!important;
  margin-bottom:12px!important;
}
.dg-co-facts{
  border-top:1px solid rgba(255,255,255,.075)!important;
}
.dg-co-fact{
  padding:11px 18px!important;
}
.dg-co-fact__l{
  font-size:.78rem!important;
}
.dg-co-fact__v{
  font-size:.84rem!important;
}

/* Seller card lighter */
.dg-seller{
  border-radius:20px!important;
  border:1px solid rgba(255,255,255,.085)!important;
  background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.020))!important;
  box-shadow:none!important;
}
.dg-seller__head{
  padding:16px!important;
}
.dg-seller__actions{
  padding:0 16px 14px!important;
}
.dg-seller__stats{
  border-top:1px solid rgba(255,255,255,.07)!important;
}

/* Remove the extra purple floating chat bubble at bottom */
.dg-lbc-floating{
  display:none!important;
}

/* More from seller cards should keep the clean shop style */
.dg-more{
  background:transparent!important;
  border-top:1px solid rgba(255,255,255,.055)!important;
}
.dg-cards-row{
  grid-auto-columns:minmax(320px,360px)!important;
}

/* Responsive */
@media(max-width:1100px){
  .dg-layout{grid-template-columns:minmax(0,1fr) 310px!important}
}
@media(max-width:860px){
  .dg-wrap{width:calc(100vw - 24px)!important;padding-top:calc(var(--lb-content-top, 96px) + 10px)!important}
  .dg-layout{grid-template-columns:1fr!important}
  .dg-hero__banner{
    min-height:0!important;
    padding:24px 18px!important;
    align-items:center!important;
  }
  .dg-hero__icon{
    width:64px!important;
    height:64px!important;
    border-radius:18px!important;
  }
  .dg-hero__icon img{width:44px!important;height:44px!important}
  .dg-hero__banner-title{
    font-size:23px!important;
  }
  .dg-hero__pills{
    flex-wrap:nowrap!important;
    overflow-x:auto!important;
    scrollbar-width:none!important;
    padding:12px!important;
  }
  .dg-hero__pills::-webkit-scrollbar{display:none!important}
  .dp{flex:0 0 auto!important}
}
@media(max-width:560px){
  .dg-view-page header{padding-bottom:18px!important}
  .dg-view-page header h1{font-size:28px!important}
  .dg-view-page header p{font-size:13px!important}
  .dg-hero__banner{
    flex-direction:column!important;
    align-items:flex-start!important;
    gap:14px!important;
  }
  .dg-hero__banner-info{width:100%!important}
  .dg-hero__banner-title{font-size:22px!important}
  .dg-card__body{padding:16px!important}
}

/* ═══════════════════════════════════════════════════════════════
   FULL VISUAL REDESIGN — match items/view.php + accounts/view_generic.php
   Re-skins every dg-* component to the same colors/cards/buttons used
   sitewide (background rgba(255,255,255,.06), border rgba(114,110,142,.3),
   buy-button gradient #7c83ff→#5b57ff→#4f46e5). IDs, form field names and
   JS bindings (#hide-sticky, #dgQty, dg-lbc-* chat modal, checkout AJAX
   actions) are left completely untouched — only the visual layer changes.
   ═══════════════════════════════════════════════════════════════ */

/* Page + cards: same flat dark panel system as accounts view_lol/view_generic
   (page bg #070815, panel #0d1021, border rgba(255,255,255,.07)) instead of the
   lighter translucent overlay used before. */
.dg-view-page {
  background:
    radial-gradient(1100px 620px at 18% -6%,rgba(79,110,247,.12),transparent 60%),
    radial-gradient(900px 560px at 88% 2%,rgba(99,102,241,.08),transparent 58%),
    #070815 !important;
}
.dg-view-page .dg-hero__card,
.dg-view-page .dg-card,
.dg-view-page .dg-checkout-card,
.dg-view-page .dg-seller {
  background: #0d1021 !important;
  border: 1px solid rgba(255,255,255,.07) !important;
  box-shadow: 0 14px 40px rgba(0,0,0,.24) !important;
}
.dg-view-page .dg-card__head {
  background: transparent !important;
  border-bottom: 1px solid rgba(255,255,255,.06) !important;
}
.dg-view-page .dg-card__hi {
  background: rgba(99,102,241,.14) !important;
  border: 1px solid rgba(99,102,241,.3) !important;
  color: #8ea5ff !important;
}
.dg-view-page .dg-card__ht { color: rgba(255,255,255,.5) !important; }
.dg-view-page .dg-desc,
.dg-view-page .dg-plain,
.dg-view-page .dg-step__t,
.dg-view-page .dg-feature__t { color: rgba(255,255,255,.75) !important; }
.dg-view-page .dg-feature {
  background: rgba(255,255,255,.03) !important;
  border: 1px solid rgba(255,255,255,.06) !important;
}
.dg-view-page .dg-feature i,
.dg-view-page .dg-step__n { color: #8ea5ff !important; }
.dg-view-page .dg-step__n {
  background: rgba(99,102,241,.18) !important;
  border: 1px solid rgba(99,102,241,.34) !important;
}

/* Hero banner: keep the brand-colored banner, but the surrounding card matches everything else */
.dg-view-page .dg-hero__banner-cat { color: #8ea5ff !important; }
.dg-view-page .dg-hero__pills { border-top-color: rgba(255,255,255,.07) !important; }
.dg-view-page .dp--blue   { background: rgba(99,102,241,.18) !important; border-color: rgba(99,102,241,.34) !important; color: #c7d2fe !important; }
.dg-view-page .dp--green  { background: rgba(99,102,241,.18) !important; border-color: rgba(99,102,241,.34) !important; color: #c7d2fe !important; }
.dg-view-page .dp--purple { background: rgba(99,102,241,.18) !important; border-color: rgba(99,102,241,.34) !important; color: #c7d2fe !important; }
.dg-view-page .dp--grey   { background: rgba(255,255,255,.06) !important; border-color: rgba(255,255,255,.09) !important; }

/* Checkout card: same elevated hero-button treatment as items/accounts */
.dg-view-page .dg-checkout-card { border-color: rgba(124,146,255,.24) !important; }
.dg-view-page .dg-checkout-card::before {
  background: linear-gradient(90deg,#6366f1,#8b5cf6,#6366f1) !important;
  background-size: 200% 100% !important;
  animation: lbvAccentSlideDg 6s linear infinite !important;
  height: 3px !important;
}
@keyframes lbvAccentSlideDg { 0% { background-position: 0 0; } 100% { background-position: 200% 0; } }
.dg-view-page .dg-co-buy {
  background: linear-gradient(135deg,#7c83ff,#5b57ff 55%,#4f46e5) !important;
  box-shadow: 0 14px 34px rgba(91,87,255,.38) !important;
  border-radius: 14px !important;
}
.dg-view-page .dg-co-buy:hover { opacity: 1 !important; filter: brightness(1.06); }
.dg-view-page .dg-co-wish {
  background: rgba(255,255,255,.035) !important;
  border: 1px solid rgba(255,255,255,.07) !important;
  color: #c4c9df !important;
}
.dg-view-page .dg-co-facts { border-top-color: rgba(114,110,142,.3) !important; }
.dg-view-page .dg-co-fact { border-bottom-color: rgba(114,110,142,.3) !important; }
.dg-view-page .dg-co-fact__l i { color: #8ea5ff !important; }

/* Seller card: same accent + chat button as accounts view_generic */
.dg-view-page .dg-seller__ph,
.dg-view-page .dg-seller__name i { color: #8ea5ff !important; }
.dg-view-page .dg-seller__name:hover { color: #a5b4fc !important; }
.dg-view-page .dg-seller__stats { border-top-color: rgba(114,110,142,.3) !important; }
.dg-view-page .dg-sstat { border-right-color: rgba(114,110,142,.3) !important; }
.dg-view-page .dg-sstat__ico { color: #8ea5ff !important; }
.dg-view-page .dg-seller__btn--chat {
  background: rgba(255,255,255,.045) !important;
  border: 1px solid rgba(124,146,255,.22) !important;
  color: #c7d2fe !important;
}
.dg-view-page .dg-seller__btn--chat:hover {
  background: rgba(99,102,241,.16) !important;
  border-color: rgba(124,146,255,.40) !important;
  color: #fff !important;
}

/* Reviews: same testimonial-card look as items/accounts */
.dg-view-page .dg-rev {
  background: rgba(255,255,255,.03) !important;
  border: 1px solid rgba(255,255,255,.07) !important;
  border-radius: 16px !important;
}
.dg-view-page .dg-rev__ava {
  background: linear-gradient(135deg,#6366f1,#4f46e5) !important;
  box-shadow: 0 4px 14px rgba(79,70,229,.35) !important;
}
.dg-view-page .dg-reviews-title i,
.dg-view-page .dg-section-title i { color: #8ea5ff !important; }
.dg-view-page .dg-section-title a { color: #6366f1 !important; }
.dg-view-page .dg-reviews-nav button,
.dg-view-page .dg-section-nav button {
  background: rgba(255,255,255,.05) !important;
  border: 1px solid rgba(255,255,255,.15) !important;
}
.dg-view-page .dg-reviews-nav button:hover,
.dg-view-page .dg-section-nav button:hover {
  background: rgba(99,102,241,.25) !important;
  border-color: rgba(99,102,241,.6) !important;
}
.dg-view-page .dg-reviews-dot.active { background: #6366f1 !important; }

/* More-from-seller cards already use the #6366f1/#8b5cf6 gradient — just align the border/shadow language */
.dg-view-page .dg-card-new {
  border-color: rgba(114,110,142,.3) !important;
}
.dg-view-page .dg-card-new:hover {
  border-color: rgba(99,102,241,.6) !important;
  box-shadow: 0 24px 70px rgba(0,0,0,.42), 0 0 42px rgba(99,102,241,.16) !important;
}

/* ---- Slim guarantee ribbon (same as items/accounts view_generic) ---- */
.dg-view-page .lbv-ribbon{
  display:flex; flex-wrap:wrap; align-items:center; justify-content:center;
  gap:6px 4px; margin:16px 0; padding:13px 16px;
  border-top:1px solid rgba(114,110,142,.3);
  border-bottom:1px solid rgba(114,110,142,.3);
}
.dg-view-page .lbv-ribbon__item{
  display:inline-flex; align-items:center; gap:8px;
  font-size:13px; font-weight:750; color:#d4d8ea; white-space:nowrap;
}
.dg-view-page .lbv-ribbon__item i{color:#8ea5ff; font-size:14px;}
.dg-view-page .lbv-ribbon__dot{width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,.18); margin:0 14px;}
@media (max-width:767px){
  .dg-view-page .lbv-ribbon{
    display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px;
    margin:14px 0; padding:12px 0;
  }
  .dg-view-page .lbv-ribbon__item{
    min-width:0; min-height:42px; padding:9px 10px; gap:8px;
    border:1px solid rgba(126,145,255,.14); border-radius:11px;
    background:linear-gradient(135deg,rgba(116,105,255,.10),rgba(255,255,255,.025));
    color:#e4e7f4; font-size:11px !important; font-weight:800; line-height:1.25; white-space:normal;
  }
  .dg-view-page .lbv-ribbon__item i{
    width:25px; height:25px; flex:0 0 25px; display:grid; place-items:center;
    border-radius:8px; background:rgba(111,126,255,.14); color:#9cadff; font-size:12px !important;
  }
  .dg-view-page .lbv-ribbon__item:last-of-type{ grid-column:1 / -1; justify-content:center; }
  .dg-view-page .lbv-ribbon__dot{display:none;}
}

/* Mobile sticky buy bar: same look as items/accounts */
.dg-sticky {
  background: #0e111a !important;
  border-top: 1px solid rgba(116,105,255,.48) !important;
  box-shadow: 0 -10px 28px rgba(0,0,0,.45) !important;
}
.dg-sticky .ajax-form button[type="submit"] {
  background: linear-gradient(135deg,#6366f1,#4f46e5) !important;
  box-shadow: 0 -2px 24px rgba(79,70,229,.28) !important;
  border: 1px solid rgba(124,146,255,.40) !important;
}

</style>
<?= $this->stop() ?>

<?= $this->start('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── QTY SYNC ── */
  var U = <?= $displayPriceCents ?>, MIN = <?= $minQty ?>, MAX = <?= $maxQty ?>;
  var Z = <?= json_encode(function_exists('dg_format_price') ? dg_format_price(0, $currency) : '€0.00') ?>;
  function fmt(c) {
    var n = (c / 100).toFixed(2);
    if (Z.indexOf('0.00') !== -1) return Z.replace('0.00', n);
    if (Z.indexOf('0,00') !== -1) return Z.replace('0,00', n.replace('.', ','));
    return n + ' <?= $h($currency) ?>';
  }
  function syncQty() {
    var q = Math.max(MIN, Math.min(MAX, parseInt(document.getElementById('dgQty').value) || MIN));
    document.getElementById('dgQty').value = q;
    document.getElementById('dgFormQty').value = q;
    var t = fmt(U * q);
    document.querySelectorAll('.js-total').forEach(function (el) { el.textContent = t; });
  }
  var btnUp = document.getElementById('dgQtyUp');
  var btnDn = document.getElementById('dgQtyDown');
  var inp   = document.getElementById('dgQty');
  if (btnUp) btnUp.addEventListener('click', function () { inp.value = Math.min(MAX, (parseInt(inp.value) || MIN) + 1); syncQty(); });
  if (btnDn) btnDn.addEventListener('click', function () { inp.value = Math.max(MIN, (parseInt(inp.value) || MIN) - 1); syncQty(); });
  if (inp)   inp.addEventListener('change', syncQty);
  syncQty();

  /* ── REVIEWS SLIDER ── */
  var track  = document.querySelector('[data-rev-track]');
  var dots   = document.querySelectorAll('.dg-reviews-dot');
  var prev   = document.querySelector('.js-rprev');
  var next   = document.querySelector('.js-rnext');
  if (track) {
    var cur = 0;
    var cards = track.querySelectorAll('.dg-rev');
    function visCount() {
      if (window.innerWidth <= 860) return 1;
      if (window.innerWidth <= 1100) return 2;
      return 3;
    }
    function maxIdx() { return Math.max(0, cards.length - visCount()); }
    function goTo(i) {
      cur = Math.max(0, Math.min(maxIdx(), i));
      var w = cards[0] ? cards[0].getBoundingClientRect().width + 14 : 300;
      track.style.transform = 'translateX(-' + (cur * w) + 'px)';
      dots.forEach(function (d, idx) { d.classList.toggle('active', idx === cur); });
    }
    if (prev) prev.addEventListener('click', function () { goTo(cur - 1); });
    if (next) next.addEventListener('click', function () { goTo(cur + 1); });
    dots.forEach(function (d, i) { d.addEventListener('click', function () { goTo(i); }); });
    window.addEventListener('resize', function () { goTo(Math.min(cur, maxIdx())); });
  }

  /* ── MORE FROM SELLER ── */
  var mrow = document.querySelector('[data-more-row]');
  var mprev = document.querySelector('.js-mprev');
  var mnext = document.querySelector('.js-mnext');
  if (mrow) {
    function mw() { var c = mrow.querySelector('.dg-card-new'); return c ? c.getBoundingClientRect().width + 22 : 390; }
    if (mprev) mprev.addEventListener('click', function () { mrow.scrollBy({ left: -mw(), behavior: 'smooth' }); });
    if (mnext) mnext.addEventListener('click', function () { mrow.scrollBy({ left:  mw(), behavior: 'smooth' }); });
  }

});
</script>
<?= $this->stop() ?>

<div class="dg-wrap">
  <?php $dgCategorySlug = trim((string)($category['slug'] ?? '')); ?>
  <nav class="dg-breadcrumb" aria-label="<?= t('Breadcrumb') ?>">
    <a href="<?= $h($dgBaseUrl . '/digital-goods/') ?>"><?= t('Digital Goods') ?></a>
    <?php if ($dgCategorySlug !== ''): ?>
      <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      <a href="<?= $h($dgBaseUrl . '/digital-goods/' . rawurlencode($dgCategorySlug)) ?>"><?= $h($categoryName) ?></a>
    <?php endif; ?>
    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    <span aria-current="page"><?= $h($title) ?></span>
  </nav>

  <!-- ══ UNIFIED LAYOUT ════════════════════════════════════════ -->
  <div class="dg-layout">

    <!-- ── LEFT COLUMN ─────────────────────────────────────── -->
    <div class="dg-layout__left">

      <!-- Hero card -->
      <div class="dg-hero__card" style="margin-bottom:8px;">
        <!-- Banner with icon -->
        <div class="dg-hero__banner" style="background:<?= $bannerGrad ?>">
          <div class="dg-hero__banner-ring"></div>
          <div class="dg-hero__banner-ring"></div>
          <div class="dg-hero__banner-ring"></div>
          <div class="dg-hero__icon">
            <?php if ($brandIconUrl !== ''): ?>
              <img src="<?= $h($brandIconUrl) ?>" alt="<?= $h($brandName !== '' ? $brandName : $categoryName) ?>">
            <?php else: ?>
              <i class="<?= $h($categoryIcon) ?>"></i>
            <?php endif; ?>
          </div>
          <div class="dg-hero__banner-info">
            <div class="dg-hero__banner-cat">
              <?= $h($categoryName) ?>
              <?php if ($brandName !== ''): ?><span style="opacity:.35;">·</span><?= $h($brandName) ?><?php endif; ?>
            </div>
            <div class="dg-hero__banner-title"><?= $h($title) ?></div>
            <div class="dg-hero__banner-meta">
              <?php if ($avgRating > 0): ?>
              <span class="dm dm--star"><i class="fa-solid fa-star"></i> <?= number_format($avgRating,1) ?> · (<?= $reviewCount ?>)</span>
              <span class="dm dm--dot"></span>
              <?php endif; ?>
              <?php if ($stock > 0): ?>
              <?php if ($avgRating > 0): ?><span class="dm dm--dot"></span><?php endif; ?>
              <span class="dm dm--live"><i class="fa-solid fa-circle" style="font-size:.5rem;"></i> <?= t('In Stock') ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <!-- Pills -->
        <div class="dg-hero__pills">
          <span class="dp dp--blue"><i class="fa-solid fa-globe"></i><?= $h($region) ?></span>
          <span class="dp <?= $isInstant ? 'dp--green' : 'dp--yellow' ?>">
            <i class="fa-solid fa-<?= $isInstant ? 'bolt' : 'clock' ?>"></i><?= $isInstant ? t('Instant Delivery') : t('Manual Delivery') ?>
          </span>
          <span class="dp dp--purple"><i class="fa-solid fa-calendar-days"></i><?= $h($validity) ?></span>
          <?php if ($stock > 0): ?>
          <span class="dp dp--grey"><i class="fa-solid fa-box"></i><?= (int)$stock ?> <?= t('in stock') ?></span>
          <?php else: ?>
          <span class="dp dp--red"><i class="fa-solid fa-ban"></i><?= t('Out of Stock') ?></span>
          <?php endif; ?>
          <?php if ($avgRating > 0): ?>
          <span class="dp dp--orange"><i class="fa-solid fa-star"></i><?= number_format($avgRating,1) ?> (<?= $reviewCount ?>)</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Guarantee ribbon, same as items/accounts view pages -->
      <div class="lbv-ribbon">
        <span class="lbv-ribbon__item"><i class="fas fa-bolt"></i><?= t('Instant Delivery') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-shield-halved"></i><?= t('Buyer Protection') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-rotate-left"></i><?= t('Dispute Covered') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-lock"></i><?= t('Secure Checkout') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-headset"></i><?= t('24/7 Support') ?></span>
      </div>

      <!-- Trust bar — directly below hero card, no gap -->
      <div class="dg-trustbar" style="margin-bottom:16px;">
        <div class="dg-tb">
          <i class="dg-tb__ico dg-tb__ico--green fa-solid fa-shield-halved"></i>
          <div><div class="dg-tb__label"><?= t('Secure Payment') ?></div><div class="dg-tb__sub"><?= t('SSL encrypted') ?></div></div>
        </div>
        <div class="dg-tb">
          <i class="dg-tb__ico dg-tb__ico--blue fa-solid fa-headset"></i>
          <div><div class="dg-tb__label"><?= t('24/7 Support') ?></div><div class="dg-tb__sub"><?= t('Always available') ?></div></div>
        </div>
        <div class="dg-tb">
          <i class="dg-tb__ico dg-tb__ico--yellow fa-solid fa-rotate-left"></i>
          <div><div class="dg-tb__label"><?= t('Buyer Protection') ?></div><div class="dg-tb__sub"><?= t('Dispute covered') ?></div></div>
        </div>
        <div class="dg-tb">
          <i class="dg-tb__ico dg-tb__ico--purple fa-solid fa-bolt"></i>
          <div><div class="dg-tb__label"><?= $isInstant ? t('Instant Delivery') : t('Fast Delivery') ?></div><div class="dg-tb__sub"><?= $isInstant ? t('Right after checkout') : t('Within a few hours') ?></div></div>
        </div>
      </div>

      <!-- 3 key fact boxes -->
      <div class="dg-facts3">
        <div class="dg-fact <?= $isGlobal ? 'dg-fact--ok' : 'dg-fact--warn' ?>">
          <div class="dg-ficon <?= $isGlobal ? 'dg-ficon--green' : 'dg-ficon--yellow' ?>"><i class="fa-solid fa-globe"></i></div>
          <div><div class="dg-fact__lbl"><?= t('Region') ?></div><div class="dg-fact__val"><?= $h($region) ?></div></div>
        </div>
        <div class="dg-fact">
          <div class="dg-ficon dg-ficon--purple"><i class="fa-solid fa-calendar-days"></i></div>
          <div><div class="dg-fact__lbl"><?= t('Validity') ?></div><div class="dg-fact__val"><?= $h($validity) ?></div></div>
        </div>
        <div class="dg-fact <?= $isInstant ? 'dg-fact--ok' : '' ?>">
          <div class="dg-ficon <?= $isInstant ? 'dg-ficon--green' : 'dg-ficon--yellow' ?>">
            <i class="fa-solid fa-<?= $isInstant ? 'bolt' : 'clock' ?>"></i>
          </div>
          <div><div class="dg-fact__lbl"><?= t('Delivery') ?></div><div class="dg-fact__val"><?= $h($deliveryText) ?></div></div>
        </div>
      </div>

      <!-- Region warning -->
      <?php if (!$isGlobal): ?>
      <div class="dg-rwarn">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <p>
          <strong><?= t('Region lock') ?>: <?= $h($region) ?></strong> — <?= t('This product only works for accounts in the') ?>
          <strong><?= $h($region) ?></strong> <?= t('region. Make sure your account, billing address, or store is set to') ?>
          <strong><?= $h($region) ?></strong> <?= t('before purchasing — redeeming in the wrong region may result in an error.') ?>
        </p>
      </div>
      <?php endif; ?>

      <!-- Safety information -->
      <?php if ($safetyInfo !== ''): ?>
      <div class="dg-card" style="border-color:rgba(251,113,133,.22);margin-bottom:16px;">
        <div class="dg-card__head" style="border-bottom-color:rgba(251,113,133,.12);">
          <div class="dg-card__hi" style="background:rgba(251,113,133,.1);border-color:rgba(251,113,133,.22);color:#fb7185;">
            <i class="fa-solid fa-shield-exclamation"></i>
          </div>
          <div class="dg-card__ht" style="color:rgba(251,113,133,.65);"><?= t('Safety Information') ?></div>
        </div>
        <div class="dg-card__body"><p class="dg-plain" style="color:rgba(255,255,255,.7);"><?= nl2br($h($safetyInfo)) ?></p></div>
      </div>
      <?php endif; ?>

      <!-- Description -->
      <?php if ($description !== ''): ?>
      <div class="dg-card">
        <div class="dg-card__head">
          <div class="dg-card__hi"><i class="fa-solid fa-align-left"></i></div>
          <div class="dg-card__ht"><?= t('Description') ?></div>
        </div>
        <div class="dg-card__body"><p class="dg-desc"><?= nl2br($h($description)) ?></p></div>
      </div>
      <?php endif; ?>

      <!-- Features / highlights -->
      <?php if (!empty($features)): ?>
      <div class="dg-card">
        <div class="dg-card__head">
          <div class="dg-card__hi"><i class="fa-solid fa-list-check"></i></div>
          <div class="dg-card__ht"><?= t('What\'s Included') ?></div>
        </div>
        <div class="dg-card__body">
          <div class="dg-features">
            <?php foreach ($features as $feat): ?>
            <div class="dg-feature"><i class="fa-solid fa-check"></i><span class="dg-feature__t"><?= $h((string)$feat) ?></span></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- How to Redeem -->
      <?php if ($deliveryInstr !== ''): ?>
      <div class="dg-card">
        <div class="dg-card__head">
          <div class="dg-card__hi"><i class="fa-solid fa-receipt"></i></div>
          <div class="dg-card__ht"><?= t('How to Redeem') ?></div>
        </div>
        <div class="dg-card__body">
          <?php $steps = array_values(array_filter(array_map('trim', explode("\n", $deliveryInstr)))); ?>
          <?php if (count($steps) > 1): ?>
          <div class="dg-steps">
            <?php foreach ($steps as $i => $s): ?>
            <div class="dg-step">
              <div class="dg-step__n"><?= $i + 1 ?></div>
              <div class="dg-step__t"><?= nl2br($h($s)) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p class="dg-plain"><?= nl2br($h($deliveryInstr)) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Gallery -->
      <?php if (count($images) > 1): ?>
      <div class="dg-card">
        <div class="dg-card__head">
          <div class="dg-card__hi"><i class="fa-solid fa-images"></i></div>
          <div class="dg-card__ht"><?= t('Gallery') ?></div>
        </div>
        <div class="dg-card__body">
          <div class="dg-gallery">
            <?php foreach ($images as $img): ?>
            <a href="<?= $h($img) ?>" target="_blank"><img src="<?= $h($img) ?>" alt=""></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /dg-layout__left -->

    <!-- ── RIGHT COLUMN (sticky) ──────────────────────────── -->
    <div class="dg-layout__right">

      <!-- Checkout card -->
      <div class="dg-checkout-card" id="hide-sticky">
        <div class="dg-co-dtype dg-co-dtype--<?= $isInstant ? 'instant' : 'manual' ?>">
          <i class="fa-solid fa-<?= $isInstant ? 'bolt' : 'clock' ?>"></i>
          <?= $isInstant ? t('Instant delivery — right after checkout') : t('Manual delivery — seller sends within a few hours') ?>
        </div>
        <div class="dg-co-body">
          <div class="dg-co-price"><span class="js-total"><?= $priceFormatted ?></span><small><?= $h($currency) ?></small></div>
          <?php if ($stock > 0): ?>
          <form action="<?= AJAX_URL ?>" class="ajax-form" id="dgCheckoutForm">
            <input type="hidden" name="action"   value="client_dg_checkout">
            <input type="hidden" name="item_id"  value="<?= (int)($listing['id'] ?? 0) ?>">
            <input type="hidden" name="quantity" id="dgFormQty" value="<?= $minQty ?>">
            <div class="dg-co-qty">
              <button type="button" class="dg-co-qty-btn" id="dgQtyDown">−</button>
              <input  type="number" class="dg-co-qty-input" id="dgQty" value="<?= $minQty ?>" min="<?= $minQty ?>" max="<?= $maxQty ?>">
              <button type="button" class="dg-co-qty-btn" id="dgQtyUp">+</button>
            </div>
            <button type="submit" class="dg-co-buy" id="dgBuyBtn">
              <span class="indicator-label"><i class="fa-solid fa-lock"></i> <?= t('Buy Now') ?></span>
              <span class="indicator-progress"><span class="loader"></span></span>
            </button>
          </form>
          <button type="button" class="dg-co-wish"><i class="fa-regular fa-heart"></i> <?= t('Add to Wishlist') ?></button>
          <?php else: ?>
          <div style="text-align:center;padding:24px 0 20px;">
            <div style="font-size:2.2rem;opacity:.18;margin-bottom:8px;">📦</div>
            <div style="font-size:.92rem;font-weight:800;color:rgba(255,255,255,.32);"><?= t('Out of Stock') ?></div>
            <div style="font-size:.76rem;color:rgba(255,255,255,.18);margin-top:5px;"><?= t('Check back soon or browse other listings.') ?></div>
            <?php if ($dgChatAllowed): ?>
            <button type="button" data-seller-chat-open style="margin-top:14px;width:100%;height:46px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:0;font-weight:900;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;">
              <i class="fa-regular fa-message"></i> <?= t('Ask the seller about restock') ?>
            </button>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <!-- Quick facts -->
        <div class="dg-co-facts">
          <?php
          $qf = [
            ['fa-globe',        t('Region'),   $region],
            ['fa-calendar-days',t('Validity'),  $validity],
            ['fa-truck-fast',   t('Delivery'),  $deliveryText],
            ['fa-box',          t('Stock'),     (int)$stock . ' ' . t('units')],
          ];
          if ($brandName !== '') $qf[] = ['fa-tag', t('Brand'), $brandName];
          foreach ($qf as [$ico,$lbl,$val]): ?>
          <div class="dg-co-fact">
            <span class="dg-co-fact__l"><i class="fa-solid <?= $ico ?>"></i><?= $lbl ?></span>
            <span class="dg-co-fact__v"><?= $h((string)$val) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Seller card -->
      <div class="dg-seller">
        <div class="dg-seller__head">
          <?php if ($sellerIcon !== ''): ?>
            <img src="<?= $h($sellerIcon) ?>" alt="<?= $h($sellerName) ?>" class="dg-seller__ava">
          <?php else: ?>
            <div class="dg-seller__ph"><i class="fa-solid fa-user"></i></div>
          <?php endif; ?>
          <div>
            <div class="dg-seller__lbl"><?= t('Sold by') ?></div>
            <a href="<?= $h($sellerLink) ?>" class="dg-seller__name">
              <?= $h($sellerName) ?>
            </a>
            <?php if ($sellerJoinedYear !== ''): ?>
            <div class="dg-seller__since"><?= t('Member since') ?> <?= $h($sellerJoinedYear) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="dg-seller__actions">
          <button type="button"
                  class="dg-seller__btn dg-seller__btn--chat"
                  data-seller-chat-open
                  <?= !$dgChatAllowed ? 'disabled' : '' ?>>
            <i class="fa-regular fa-message"></i> <?= t('Chat') ?>
          </button>
          <a href="<?= $h($sellerLink) ?>" class="dg-seller__btn dg-seller__btn--profile">
            <i class="fa-solid fa-store"></i> <?= t('Profile') ?>
          </a>
        </div>
        <div class="dg-seller__stats">
          <div class="dg-sstat">
            <i class="fa-solid fa-bag-shopping dg-sstat__ico"></i>
            <div>
              <div class="dg-sstat__val"><?= number_format($sellerTotalSold) ?></div>
              <div class="dg-sstat__lbl"><?= t('Total Sold') ?></div>
            </div>
          </div>
          <div class="dg-sstat">
            <i class="fa-solid fa-shield-check dg-sstat__ico" style="color:rgba(74,222,128,.65);"></i>
            <div>
              <div class="dg-sstat__val" style="color:#4ade80;"><?= t('Verified') ?></div>
              <div class="dg-sstat__lbl"><?= t('Verified') ?></div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /dg-layout__right -->

  </div><!-- /dg-layout -->

  <!-- ══ REVIEWS SLIDER ═══════════════════════════════════════ -->
  <div class="dg-reviews-section">
    <div class="dg-reviews-head">
      <div class="dg-reviews-title">
        <i class="fa-solid fa-star"></i> <?= t('Customer Reviews') ?>
        <?php if ($avgRating > 0): ?>
        <span class="dg-reviews-rating" style="margin-left:6px;">
          <span class="dg-reviews-avg"><?= number_format($avgRating, 1) ?></span>
          <span class="dg-reviews-stars">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <?php if ($s <= floor($avgRating)): ?><i class="fa-solid fa-star" style="font-size:.9rem;"></i>
              <?php elseif ($s - $avgRating < 1): ?><i class="fa-solid fa-star-half-stroke" style="font-size:.9rem;"></i>
              <?php else: ?><i class="fa-regular fa-star" style="font-size:.9rem;color:rgba(245,166,35,.3);"></i>
              <?php endif; ?>
            <?php endfor; ?>
          </span>
          <span class="dg-reviews-count">(<?= $reviewCount ?> <?= t('reviews') ?>)</span>
        </span>
        <?php endif; ?>
      </div>
      <?php if (!empty($reviews) && count($reviews) > 1): ?>
      <div style="display:flex;align-items:center;gap:12px;">
        <div class="dg-reviews-dots" style="margin-top:0;">
          <?php for ($d = 0; $d < count($reviews); $d++): ?>
          <button class="dg-reviews-dot<?= $d === 0 ? ' active' : '' ?>"></button>
          <?php endfor; ?>
        </div>
        <div class="dg-reviews-nav">
          <button class="js-rprev"><i class="fa-solid fa-chevron-left" style="font-size:.75rem;"></i></button>
          <button class="js-rnext"><i class="fa-solid fa-chevron-right" style="font-size:.75rem;"></i></button>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($reviews)): ?>
    <div class="dg-reviews-slider">
      <div class="dg-reviews-track" data-rev-track>
        <?php foreach ($reviews as $rev): ?>
        <div class="dg-rev">
          <div class="dg-rev__top">
            <?php
              $dgReviewName = (string)($rev['client_username'] ?? 'User');
              $dgReviewMasked = dg_mask_client_name($dgReviewName);
            ?>
            <div class="dg-rev__ava" aria-label="<?= $h($dgReviewMasked) ?>">
              <img src="/public/assets/website/images/reviews/default.webp" alt="" loading="lazy">
            </div>
            <div>
              <div class="dg-rev__name"><?= $h($dgReviewMasked) ?></div>
              <?php if (!empty($rev['created_at'])): ?>
              <div class="dg-rev__date"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
              <?php endif; ?>
            </div>
            <div class="dg-rev__stars-ml">
              <div class="dg-rev__stars">
                <?= str_repeat('★', (int)($rev['rating'] ?? 0)) ?><?= str_repeat('☆', max(0, 5 - (int)($rev['rating'] ?? 0))) ?>
              </div>
            </div>
          </div>
          <?php if (!empty($rev['comment'])): ?>
          <div class="dg-rev__body"><?= $h($rev['comment']) ?></div>
          <?php else: ?>
          <div class="dg-rev__body" style="color:rgba(255,255,255,.22);font-style:italic;"><?= t('No comment left.') ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="dg-no-reviews">
      <i class="fa-regular fa-star"></i>
      <?= t('No reviews yet — be the first to purchase and leave feedback!') ?>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /dg-wrap -->

<!-- ══ CHECK OUT OUR OTHER CATEGORIES ════════════════════════ -->
<?php
// Build category list from DB — $dgCategoryList must be passed from route
// Filter out the current listing's own category so we don't show it
$_dgCats = [];
if (!empty($dgCategoryList) && is_array($dgCategoryList)) {
    $currentCatSlug = (string)($category['slug'] ?? '');
    foreach ($dgCategoryList as $_dc) {
        if (!empty($_dc['active']) && (string)($_dc['slug'] ?? '') !== $currentCatSlug) {
            $_dgCats[] = $_dc;
        }
    }
}
?>
<?php if (!empty($_dgCats)): ?>
<div class="dg-cat-section">
  <div class="dg-cat-section__inner">
    <div class="dg-section-head">
      <div class="dg-section-title">
        <i class="fa-solid fa-grip"></i> <?= t('Check Out Our Other Categories') ?>
      </div>
    </div>
    <div class="sh-grid sh-grid--dg">
      <?php foreach ($_dgCats as $dc):
        $dcIcon = trim((string)($dc['icon'] ?? 'fa-solid fa-layer-group'));
        $dcName = (string)($dc['name'] ?? '');
        $dcHref = function_exists('dg_category_url')
            ? dg_category_url($dc)
            : rtrim(BASE_URL,'/').'/digital-goods/'.rawurlencode((string)($dc['slug'] ?? ''));
        $dcBanner = trim((string)($dc['banner'] ?? ''));
        $hasBanner = $dcBanner !== '' && (str_starts_with($dcBanner,'/') || preg_match('~^https?://~i',$dcBanner));
      ?>
      <a href="<?= $h($dcHref) ?>" class="sh-card sh-card--fa" data-game-name="<?= $h(strtolower($dcName)) ?>">
        <?php if ($hasBanner): ?>
        <div class="sh-card__img-wrap" style="aspect-ratio:16/7;min-height:150px;">
          <img src="<?= $h($dcBanner) ?>" alt="<?= $h($dcName) ?>" class="sh-card__img" loading="lazy">
        </div>
        <?php else: ?>
        <div class="sh-card__fa-wrap">
          <i class="<?= $h($dcIcon) ?>"></i>
        </div>
        <?php endif; ?>
        <div class="sh-card__foot">
          <span class="sh-card__dg-icon"><i class="<?= $h($dcIcon) ?>"></i></span>
          <span class="sh-card__name"><?= $h($dcName) ?></span>
          <span class="sh-card__arrow"><i class="fas fa-arrow-right"></i></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ MORE ITEMS FROM THE SAME SELLER ═══════════════════════ -->
<?php if (!empty($sellerListings)): ?>
<div class="dg-more">
  <div class="dg-more__inner">
    <div class="dg-section-head">
      <div class="dg-section-title">
        <i class="fa-solid fa-store"></i>
        <?= t('More Goods from Seller') ?> <a href="<?= $h($sellerLink) ?>"><?= $h($sellerName) ?></a>
      </div>
      <?php if (count($sellerListings) > 1): ?>
      <div class="dg-section-nav">
        <button type="button" class="js-mprev"><i class="fa-solid fa-chevron-left" style="font-size:.72rem;"></i></button>
        <button type="button" class="js-mnext"><i class="fa-solid fa-chevron-right" style="font-size:.72rem;"></i></button>
      </div>
      <?php endif; ?>
    </div>

    <div class="dg-cards-row" data-more-row>
      <?php foreach ($sellerListings as $sl):
        $slImgs      = []; // Cover images not used for DG cards — brand icon + gradient only
        $slBrandIco  = $normalizeAssetPath($sl['brand_icon'] ?? '');
        $slHasCover  = false;
        $slCover     = null;
        $slUrl       = function_exists('dg_listing_url') ? dg_listing_url($sl) : (rtrim(BASE_URL,'/').'/digital-good/'.rawurlencode((string)($sl['slug'] ?? $sl['id'] ?? '')));
        $slPrice     = lb_dg_format_display_price((int)($sl['price'] ?? 0), $currency);
        $slDelivery  = trim((string)($sl['delivery_type'] ?? 'manual'));
        $slIsInstant = $slDelivery === 'instant';
        $slBrand     = trim((string)($sl['brand'] ?? ''));
        $slRegion    = trim((string)($sl['region'] ?? ''));
        $slValDays   = (int)($sl['validity_days'] ?? 0);
        $slValidity  = $slValDays <= 0 ? t('Lifetime') : ($slValDays < 30 ? $slValDays . ' ' . t('days') : ((int)round($slValDays/30) === 1 ? t('1 month') : (int)round($slValDays/30) . ' ' . t('months')));
        $slStock     = (int)($sl['stock'] ?? 0);
        $slAvgRating = round((float)($sl['avg_rating'] ?? 0), 1);
        $slRevCount  = (int)($sl['review_count'] ?? 0);
        $slSellerName = (string)($sl['seller_username'] ?? $sellerName);
        $slSellerIco  = (string)($sl['seller_icon'] ?? $sellerIcon);
        $slBrandSeed  = strtolower($slBrand);
        $slBp = $bannerPalettes['default'];
        foreach ($bannerPalettes as $k => $v) { if ($k !== 'default' && str_contains($slBrandSeed, $k)) { $slBp = $v; break; } }
        $slBannerBg = "linear-gradient(135deg,{$slBp[0]} 0%,{$slBp[1]} 50%,{$slBp[2]} 100%)";
      ?>
      <a class="dg-card-new" href="<?= $h($slUrl) ?>">
        <div class="dg-card-new__banner" style="background:<?= $slBannerBg ?>">
          <div class="dg-card-new__banner-rings"><span></span><span></span></div>
          <div class="dg-card-new__icon">
            <?php if ($slBrandIco !== ''): ?>
              <img src="<?= $h($slBrandIco) ?>" alt="<?= $h($slBrand) ?>">
            <?php else: ?>
              <i class="<?= $h($categoryIcon) ?>"></i>
            <?php endif; ?>
          </div>
          <?php if ($slAvgRating > 0): ?>
          <div class="dg-card-new__rating">
            <i class="fa-solid fa-star"></i> <?= number_format($slAvgRating, 1) ?>
            <span>(<?= $slRevCount ?>)</span>
          </div>
          <?php endif; ?>
        </div>
        <div class="dg-card-new__body">
          <?php if ($slBrand !== ''): ?>
          <div class="dg-card-new__brand"><?= $h($slBrand) ?></div>
          <?php endif; ?>
          <div class="dg-card-new__title"><?= $h($sl['title'] ?? '') ?></div>
          <div class="dg-card-new__pills">
            <?php if ($slRegion !== ''): ?>
            <span class="dg-card-new__pill dg-card-new__pill--blue"><i class="fa-solid fa-globe"></i><?= $h($slRegion) ?></span>
            <?php endif; ?>
            <span class="dg-card-new__pill <?= $slIsInstant ? 'dg-card-new__pill--green' : 'dg-card-new__pill--yellow' ?>">
              <i class="fa-solid fa-<?= $slIsInstant ? 'bolt' : 'clock' ?>"></i><?= $slIsInstant ? t('Instant') : t('Manual') ?>
            </span>
            <span class="dg-card-new__pill dg-card-new__pill--purple"><i class="fa-solid fa-calendar-days"></i><?= $h($slValidity) ?></span>
            <span class="dg-card-new__pill dg-card-new__pill--grey"><i class="fa-solid fa-box"></i><?= $slStock ?> <?= t('in stock') ?></span>
          </div>
        </div>
        <div class="dg-card-new__foot">
          <div class="dg-card-new__price-wrap">
            <div class="dg-card-new__price"><?= $h($slPrice) ?></div>

            <div class="dg-card-new__seller-mid">
              <?php if ($slSellerIco !== ''): ?>
              <img src="<?= $h($slSellerIco) ?>" alt="<?= $h($slSellerName) ?>" class="dg-card-new__seller-ava">
              <?php else: ?>
              <span class="dg-card-new__seller-ava dg-card-new__seller-ava--ph"><i class="fa-solid fa-user"></i></span>
              <?php endif; ?>
              <div class="dg-card-new__seller-copy">
                <div class="dg-card-new__seller-name"><span class="dg-card-new__seller-label"><?= strtoupper(t('Sold by')) ?></span>&nbsp;<?= $h($slSellerName) ?></div>
                <div class="dg-card-new__seller-sold"><i class="fa-solid fa-thumbs-up"></i> <?= number_format($sellerTotalSold) ?> <?= t('Sold') ?></div>
              </div>
            </div>

            <div class="dg-card-new__cta"><?= t('Buy Now') ?> <i class="fa-solid fa-arrow-right"></i></div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Mobile sticky buy bar -->
<div class="dg-sticky" style="position:fixed;bottom:0;left:0;right:0;z-index:999;background:rgba(13,12,29,.96);border-top:1px solid rgba(99,102,241,.25);backdrop-filter:blur(12px);padding:12px 16px;">
  <?php if ($stock > 0): ?>
  <form action="<?= AJAX_URL ?>" class="ajax-form" id="dgStickyForm">
    <input type="hidden" name="action"   value="client_dg_checkout">
    <input type="hidden" name="item_id"  value="<?= (int)($listing['id'] ?? 0) ?>">
    <input type="hidden" name="quantity" value="<?= $minQty ?>">
    <button type="submit" style="width:100%;height:50px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:0;font-weight:900;font-size:1rem;display:flex;align-items:center;justify-content:center;gap:9px;cursor:pointer;">
      <i class="fa-solid fa-lock"></i> <?= t('Buy Now') ?> — <span class="js-total"><?= $priceFormatted ?></span>
    </button>
  </form>
  <?php else: ?>
  <div style="display:flex;gap:10px;align-items:center;">
    <button type="button" disabled style="flex:1;height:50px;border-radius:12px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.38);border:1px solid rgba(255,255,255,.1);font-weight:900;font-size:1rem;display:flex;align-items:center;justify-content:center;gap:9px;cursor:not-allowed;">
      <i class="fa-solid fa-ban"></i> <?= t('Out of Stock') ?>
    </button>
    <?php if ($dgChatAllowed): ?>
    <button type="button" data-seller-chat-open style="height:50px;padding:0 18px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:0;font-weight:900;font-size:1rem;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;">
      <i class="fa-regular fa-message"></i> <?= t('Chat') ?>
    </button>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>


<?php if ($dgSellerId > 0): ?>
<!-- Account-style Seller Chat Modal for Digital Goods -->
<button type="button" class="dg-lbc-floating" hidden aria-hidden="true" id="dgLbcTrigger" data-seller-chat-open <?= !$dgChatAllowed ? 'disabled' : '' ?>>
  <i class="fa-solid fa-comment-dots"></i>
  <span class="dg-lbc-dot" id="dgLbcUnreadDot"></span>
</button>

<div id="dgSellerChatModal" class="dg-lbc-overlay" aria-hidden="true">
  <div class="dg-lbc-panel" role="dialog" aria-modal="true">
    <div class="dg-lbc-head">
      <div class="dg-lbc-head-left">
        <?php if ($sellerIcon !== ''): ?>
          <img class="dg-lbc-avatar" src="<?= $h($sellerIcon) ?>" alt="<?= $h($sellerName) ?>">
        <?php else: ?>
          <div class="dg-lbc-avatar"><?= $h($dgSellerInitials) ?></div>
        <?php endif; ?>
        <div class="dg-lbc-head-info">
          <span class="dg-lbc-head-name"><?= $h($sellerName) ?></span>
          <span class="dg-lbc-head-sub">
            <i class="fa-solid fa-shield-check"></i>
            <?= $dgChatAllowed ? t('Ask a question before buying') : t('Not accepting messages') ?>
          </span>
        </div>
      </div>
      <button type="button" class="dg-lbc-close" data-seller-chat-close aria-label="Close">×</button>
    </div>

    <div class="dg-lbc-item-strip">
      <div class="dg-lbc-item-icon">
        <?php if ($dgChatItemIcon !== ''): ?><img src="<?= $h($dgChatItemIcon) ?>" alt=""><?php else: ?><i class="<?= $h($categoryIcon) ?>"></i><?php endif; ?>
      </div>
      <div class="dg-lbc-item-meta">
        <span class="dg-lbc-item-title"><?= $h($dgChatItemTitle) ?></span>
        <div class="dg-lbc-item-tags">
          <?php if ($dgChatItemType !== ''): ?><span class="dg-lbc-item-tag"><?= $h($dgChatItemType) ?></span><?php endif; ?>
          <?php if ($dgChatItemMeta !== ''): ?><span class="dg-lbc-item-tag"><?= $h($dgChatItemMeta) ?></span><?php endif; ?>
        </div>
      </div>
      <div class="dg-lbc-item-price"><?= $h($priceFormatted) ?></div>
    </div>

    <?php if (!$dgChatAllowed): ?>
      <div class="dg-lbc-body">
        <div class="dg-lbc-empty">
          <div class="dg-lbc-empty-icon"><i class="fa-solid fa-comment-slash"></i></div>
          <div class="dg-lbc-empty-title"><?= t('Not accepting messages') ?></div>
          <div class="dg-lbc-empty-sub"><?= t('This seller is currently not accepting new chat requests.') ?></div>
        </div>
      </div>
    <?php else: ?>
      <div class="dg-lbc-guidelines">
        <div class="dg-lbc-guide-header"><i class="fa-solid fa-shield-check"></i><?= t('Messaging Guidelines') ?></div>
        <p class="dg-lbc-guide-text"><?= t('Keep all communication and payment inside the platform. Do not share external contacts or login details before purchase.') ?></p>
        <div class="dg-lbc-guide-cols">
          <div class="dg-lbc-guide-col dg-lbc-guide-col--good">
            <div class="dg-lbc-guide-col-head"><i class="fa-solid fa-circle-check"></i><?= t('Good Examples') ?></div>
            <div class="dg-lbc-guide-item"><?= t('Can you tell me if this product has any region restrictions?') ?></div>
            <div class="dg-lbc-guide-item"><?= t('How fast can you deliver after purchase?') ?></div>
            <div class="dg-lbc-guide-item"><?= t('Can you confirm the validity and stock?') ?></div>
            <div class="dg-lbc-guide-item"><?= t('Would you be open to negotiating the price?') ?></div>
          </div>
          <div class="dg-lbc-guide-col dg-lbc-guide-col--bad">
            <div class="dg-lbc-guide-col-head"><i class="fa-solid fa-circle-xmark"></i><?= t('Avoid These') ?></div>
            <div class="dg-lbc-guide-item"><?= t("Let's talk over Telegram instead.") ?></div>
            <div class="dg-lbc-guide-item"><?= t('Can I pay after you deliver the item?') ?></div>
            <div class="dg-lbc-guide-item"><?= t('Send me the code first, I will pay after.') ?></div>
            <div class="dg-lbc-guide-item"><?= t('I work for the marketplace, I need your login.') ?></div>
          </div>
        </div>
      </div>

      <div class="dg-lbc-body" id="dgLbcMessages">
        <div class="dg-lbc-empty" id="dgLbcEmpty">
          <div class="dg-lbc-empty-icon"><i class="fa-solid fa-comments"></i></div>
          <div class="dg-lbc-empty-title"><?= t('Ask') ?> <?= $h($sellerName) ?> <?= t('about this listing') ?></div>
          <div class="dg-lbc-empty-sub"><?= t('Messages stay protected inside the platform.') ?></div>
        </div>
      </div>

      <div class="dg-lbc-footer">
        <form id="dgLbcForm" autocomplete="off">
          <input type="hidden" name="action" value="client_seller_chat_send">
          <input type="hidden" name="seller_id" value="<?= (int)$dgSellerId ?>">
          <input type="hidden" name="ref_type" value="<?= $h($dgChatRefType) ?>">
          <input type="hidden" name="ref_id" value="<?= (int)$dgChatRefId ?>">
          <input type="file" name="chat_image" id="dgLbcFileInput" accept="image/*" hidden>
          <div class="dg-lbc-preview" id="dgLbcPreview">
            <img id="dgLbcPreviewThumb" src="" alt="">
            <small id="dgLbcPreviewName"></small>
            <button type="button" class="dg-lbc-preview-rm" id="dgLbcPreviewRemove">×</button>
          </div>
          <div class="dg-lbc-compose">
            <button type="button" class="dg-lbc-img-btn" id="dgLbcImgBtn" title="<?= t('Attach image') ?>"><i class="fa-solid fa-image"></i></button>
            <input type="text" name="message" id="dgLbcMsgInput" class="dg-lbc-compose-input" placeholder="<?= t('Ask') ?> <?= $h($sellerName) ?>..." autocomplete="off">
            <button type="submit" class="dg-lbc-send-btn" id="dgLbcSendBtn" disabled><i class="fa-solid fa-paper-plane"></i></button>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>


<!-- Account-view compatible auth overlay for guests -->
<style>
.dg-lbc-auth-overlay{
  position:fixed;
  inset:0;
  z-index:2147483000;
  display:none;
  align-items:center;
  justify-content:center;
  padding:clamp(16px,3vw,32px);
  background:
    radial-gradient(900px 360px at 18% 12%,rgba(109,92,255,.24),transparent 58%),
    radial-gradient(760px 360px at 92% 82%,rgba(34,211,238,.12),transparent 60%),
    rgba(3,6,18,.78);
  backdrop-filter:blur(18px);
  -webkit-backdrop-filter:blur(18px);
}
.dg-lbc-auth-overlay.is-open{display:flex}
.dg-lbc-auth-card{
  width:min(520px,calc(100vw - 28px));
  position:relative;
  isolation:isolate;
  overflow:hidden;
  color:#fff;
  border-radius:30px;
  padding:0;
  background:
    linear-gradient(180deg,rgba(22,27,52,.98),rgba(9,13,30,.98));
  border:1px solid rgba(139,92,246,.42);
  box-shadow:
    0 36px 100px rgba(0,0,0,.72),
    0 0 0 1px rgba(255,255,255,.05) inset,
    0 0 60px rgba(109,92,255,.12);
}
.dg-lbc-auth-card::before{
  content:"";
  position:absolute;
  inset:0 0 auto 0;
  height:150px;
  z-index:-1;
  background:
    radial-gradient(360px 150px at 18% 0%,rgba(124,58,237,.42),transparent 72%),
    radial-gradient(320px 140px at 88% 12%,rgba(99,102,241,.26),transparent 68%),
    linear-gradient(135deg,rgba(109,92,255,.24),rgba(124,58,237,.08));
}
.dg-lbc-auth-card::after{
  content:"";
  position:absolute;
  width:210px;
  height:210px;
  right:-86px;
  top:-90px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.08);
  box-shadow:0 0 0 38px rgba(255,255,255,.025);
  pointer-events:none;
}
.dg-lbc-auth-head{
  position:relative;
  z-index:1;
  display:flex;
  align-items:center;
  gap:14px;
  padding:30px 34px 22px;
}
.dg-lbc-auth-mark{
  width:54px;
  height:54px;
  flex:0 0 54px;
  border-radius:18px;
  display:grid;
  place-items:center;
  color:#fff;
  font-size:22px;
  background:linear-gradient(135deg,#6d5cff,#8b5cf6);
  border:1px solid rgba(255,255,255,.18);
  box-shadow:0 14px 34px rgba(109,92,255,.38),inset 0 1px 0 rgba(255,255,255,.18);
}
.dg-lbc-auth-title{
  margin:0;
  font-size:clamp(23px,2.25vw,30px);
  line-height:1.12;
  font-weight:950;
  letter-spacing:-.035em;
  color:#fff;
}
.dg-lbc-auth-sub{
  margin-top:7px;
  color:rgba(255,255,255,.62);
  font-size:14px;
  line-height:1.45;
  font-weight:650;
}
.dg-lbc-auth-close{
  position:absolute;
  right:18px;
  top:18px;
  z-index:5;
  width:42px;
  height:42px;
  border:1px solid rgba(255,255,255,.08);
  border-radius:14px;
  background:rgba(255,255,255,.08);
  color:rgba(255,255,255,.9);
  font-size:24px;
  line-height:1;
  cursor:pointer;
  display:grid;
  place-items:center;
  transition:background .18s,transform .18s,border-color .18s;
}
.dg-lbc-auth-close:hover{background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.16);transform:rotate(3deg)}
.dg-lbc-auth-inner{padding:0 34px 34px}
.dg-lbc-auth-tabs{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:6px;
  padding:6px;
  margin:0 0 22px;
  border-radius:18px;
  background:rgba(255,255,255,.045);
  border:1px solid rgba(139,92,246,.32);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05);
}
.dg-lbc-auth-tab{
  height:48px;
  border:0;
  border-radius:14px;
  background:transparent;
  color:rgba(255,255,255,.55);
  font-weight:900;
  font-size:14px;
  cursor:pointer;
  transition:background .18s,color .18s,box-shadow .18s,transform .18s;
}
.dg-lbc-auth-tab:hover{color:#fff;background:rgba(255,255,255,.055)}
.dg-lbc-auth-tab.is-active{
  color:#fff;
  background:linear-gradient(135deg,#6d5cff,#8b5cf6);
  box-shadow:0 12px 28px rgba(109,92,255,.34),inset 0 1px 0 rgba(255,255,255,.18);
}
.dg-lbc-auth-form{display:none;animation:dgAuthFade .18s ease both}
.dg-lbc-auth-form.is-active{display:block}
@keyframes dgAuthFade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.dg-lbc-auth-field{margin:15px 0 0}
.dg-lbc-auth-label{
  display:flex;
  align-items:center;
  gap:7px;
  margin:0 0 8px;
  color:rgba(255,255,255,.72);
  font-size:13px;
  font-weight:850;
}
.dg-lbc-auth-label i{color:#8b8cff;font-size:12px}
.dg-lbc-auth-input-wrap{position:relative}
.dg-lbc-auth-input-wrap i{
  position:absolute;
  left:16px;
  top:50%;
  transform:translateY(-50%);
  color:rgba(165,180,252,.72);
  font-size:14px;
  pointer-events:none;
}
.dg-lbc-auth-input{
  width:100%;
  height:56px;
  border-radius:16px;
  border:1px solid rgba(139,92,246,.30);
  background:rgba(255,255,255,.055);
  color:#fff;
  font-size:16px;
  font-weight:700;
  padding:0 16px 0 46px;
  outline:none;
  transition:border-color .18s,background .18s,box-shadow .18s;
  box-sizing:border-box;
}
.dg-lbc-auth-input::placeholder{color:rgba(255,255,255,.24);font-weight:650}
.dg-lbc-auth-input:focus{
  border-color:rgba(129,140,248,.85);
  background:rgba(109,92,255,.10);
  box-shadow:0 0 0 4px rgba(109,92,255,.14);
}
.dg-lbc-auth-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin:14px 0 10px;
}
.dg-lbc-auth-check{
  display:flex;
  align-items:center;
  gap:8px;
  font-size:13px;
  color:rgba(255,255,255,.64);
  font-weight:700;
}
.dg-lbc-auth-check input{accent-color:#7c3aed;width:16px;height:16px}
.dg-lbc-auth-link{color:#c4b5fd;font-size:13px;text-decoration:none;font-weight:800}
.dg-lbc-auth-link:hover{color:#fff;text-decoration:underline}
.dg-lbc-auth-submit{
  width:100%;
  height:56px;
  border:0;
  border-radius:16px;
  margin-top:8px;
  background:linear-gradient(135deg,#6d5cff,#8b5cf6);
  color:#fff;
  font-weight:950;
  font-size:16px;
  cursor:pointer;
  box-shadow:0 16px 36px rgba(109,92,255,.36),inset 0 1px 0 rgba(255,255,255,.18);
  transition:transform .18s,filter .18s,box-shadow .18s;
}
.dg-lbc-auth-submit:hover{filter:brightness(1.08);transform:translateY(-1px);box-shadow:0 20px 42px rgba(109,92,255,.46),inset 0 1px 0 rgba(255,255,255,.2)}
.dg-lbc-auth-error{
  display:none;
  margin:12px 0 0;
  padding:11px 13px;
  border-radius:14px;
  background:rgba(239,68,68,.11);
  border:1px solid rgba(239,68,68,.25);
  color:#fca5a5;
  font-size:13px;
  font-weight:750;
  line-height:1.45;
}
.dg-lbc-auth-error.is-open{display:block}
.dg-lbc-auth-sep{
  display:flex;
  align-items:center;
  gap:12px;
  margin:20px 0 14px;
  color:rgba(255,255,255,.32);
  font-size:12px;
  font-weight:850;
  text-transform:uppercase;
  letter-spacing:.08em;
}
.dg-lbc-auth-sep::before,.dg-lbc-auth-sep::after{content:"";height:1px;flex:1;background:rgba(255,255,255,.08)}
.dg-lbc-auth-socials{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.dg-lbc-auth-social{
  height:50px;
  border:1px solid rgba(255,255,255,.09);
  border-radius:16px;
  color:#fff;
  font-weight:900;
  font-size:14px;
  text-decoration:none;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  transition:transform .18s,filter .18s,box-shadow .18s;
}
.dg-lbc-auth-social:hover{transform:translateY(-1px);filter:brightness(1.06);color:#fff}
.dg-lbc-auth-google{background:linear-gradient(135deg,#ff4b3f,#ea4335);box-shadow:0 12px 28px rgba(234,67,53,.20)}
.dg-lbc-auth-discord{background:linear-gradient(135deg,#6473ff,#5865f2);box-shadow:0 12px 28px rgba(88,101,242,.22)}

/* Final auth modal polish: must cover navbar, readable controls, password reveal, custom checkboxes */
.dg-lbc-auth-overlay{
  z-index:2147483000 !important;
}
.dg-lbc-auth-card{
  z-index:2147483001 !important;
}
.dg-lbc-auth-input-wrap .dg-lbc-auth-pass-toggle{
  position:absolute;
  right:12px;
  top:50%;
  transform:translateY(-50%);
  width:38px;
  height:38px;
  border:1px solid rgba(139,92,246,.24);
  border-radius:12px;
  background:rgba(255,255,255,.055);
  color:rgba(255,255,255,.68);
  display:grid;
  place-items:center;
  cursor:pointer;
  transition:background .18s,color .18s,border-color .18s,box-shadow .18s;
  padding:0;
  z-index:3;
}
.dg-lbc-auth-input-wrap .dg-lbc-auth-pass-toggle i{
  position:static;
  transform:none;
  color:inherit;
  font-size:14px;
  pointer-events:auto;
}
.dg-lbc-auth-input-wrap .dg-lbc-auth-pass-toggle:hover{
  background:rgba(109,92,255,.18);
  border-color:rgba(139,92,246,.55);
  color:#fff;
  box-shadow:0 8px 18px rgba(109,92,255,.18);
}
.dg-lbc-auth-input-wrap.has-toggle .dg-lbc-auth-input{
  padding-right:58px;
}
.dg-lbc-auth-check{
  position:relative;
  cursor:pointer;
  user-select:none;
  gap:10px;
  min-height:24px;
}
.dg-lbc-auth-check input{
  position:absolute;
  opacity:0;
  width:1px;
  height:1px;
  pointer-events:none;
}
.dg-lbc-auth-check-box{
  width:22px;
  height:22px;
  flex:0 0 22px;
  border-radius:7px;
  border:1.5px solid rgba(139,92,246,.42);
  background:rgba(255,255,255,.055);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.08);
  display:grid;
  place-items:center;
  transition:background .18s,border-color .18s,box-shadow .18s,transform .18s;
}
.dg-lbc-auth-check-box::after{
  content:"";
  width:10px;
  height:6px;
  border-left:2px solid #fff;
  border-bottom:2px solid #fff;
  transform:rotate(-45deg) scale(.6);
  opacity:0;
  transition:opacity .16s,transform .16s;
  margin-top:-2px;
}
.dg-lbc-auth-check input:checked + .dg-lbc-auth-check-box{
  background:linear-gradient(135deg,#6d5cff,#8b5cf6);
  border-color:rgba(255,255,255,.22);
  box-shadow:0 10px 22px rgba(109,92,255,.30),inset 0 1px 0 rgba(255,255,255,.18);
}
.dg-lbc-auth-check input:checked + .dg-lbc-auth-check-box::after{
  opacity:1;
  transform:rotate(-45deg) scale(1);
}
.dg-lbc-auth-check:hover .dg-lbc-auth-check-box{
  border-color:rgba(167,139,250,.75);
  transform:translateY(-1px);
}


.dg-lbc-forgot-overlay{
  position:fixed;
  inset:0;
  z-index:2147483002 !important;
  display:none;
  align-items:center;
  justify-content:center;
  padding:24px;
  background:rgba(3,5,18,.88);
  backdrop-filter:blur(18px);
  -webkit-backdrop-filter:blur(18px);
}
.dg-lbc-forgot-overlay.is-open{display:flex}
.dg-lbc-forgot-card{
  width:min(470px,100%);
  position:relative;
  overflow:hidden;
  border-radius:28px;
  background:linear-gradient(180deg,rgba(20,22,42,.98),rgba(10,13,30,.98));
  border:1px solid rgba(139,92,246,.45);
  box-shadow:0 42px 110px rgba(0,0,0,.78),0 0 0 1px rgba(255,255,255,.035) inset,0 0 90px rgba(109,92,255,.14);
  color:#fff;
}
.dg-lbc-forgot-card::before{
  content:"";
  position:absolute;
  left:12%;
  right:12%;
  top:0;
  height:1px;
  background:linear-gradient(90deg,transparent,rgba(167,139,250,.75),transparent);
}
.dg-lbc-forgot-head{
  position:relative;
  display:flex;
  gap:14px;
  align-items:center;
  padding:30px 30px 20px;
  background:radial-gradient(circle at 20% 0%,rgba(109,92,255,.32),transparent 45%),linear-gradient(135deg,rgba(109,92,255,.20),rgba(139,92,246,.08));
  border-bottom:1px solid rgba(139,92,246,.22);
}
.dg-lbc-forgot-mark{
  width:54px;
  height:54px;
  border-radius:18px;
  flex:0 0 54px;
  display:grid;
  place-items:center;
  background:linear-gradient(135deg,#6d5cff,#8b5cf6);
  box-shadow:0 16px 34px rgba(109,92,255,.34),inset 0 1px 0 rgba(255,255,255,.18);
  font-size:22px;
}
.dg-lbc-forgot-title{
  font-size:24px;
  font-weight:950;
  line-height:1.1;
  letter-spacing:-.03em;
}
.dg-lbc-forgot-sub{
  margin-top:7px;
  color:rgba(255,255,255,.64);
  font-size:14px;
  font-weight:700;
  line-height:1.45;
}
.dg-lbc-forgot-close{
  position:absolute;
  right:18px;
  top:18px;
  z-index:3;
  width:38px;
  height:38px;
  border:1px solid rgba(255,255,255,.09);
  border-radius:13px;
  background:rgba(255,255,255,.075);
  color:rgba(255,255,255,.70);
  font-size:22px;
  line-height:1;
  cursor:pointer;
  transition:background .18s,color .18s,transform .18s;
}
.dg-lbc-forgot-close:hover{background:rgba(255,255,255,.14);color:#fff;transform:rotate(90deg)}
.dg-lbc-forgot-form{padding:24px 30px 30px}
.dg-lbc-forgot-note{
  margin-top:14px;
  text-align:center;
  color:rgba(255,255,255,.38);
  font-size:12px;
  font-weight:700;
  line-height:1.5;
}
@media(max-width:560px){
  .dg-lbc-forgot-overlay{align-items:flex-end;padding:12px}
  .dg-lbc-forgot-card{border-radius:26px 26px 22px 22px}
  .dg-lbc-forgot-head{padding:26px 22px 18px}
  .dg-lbc-forgot-form{padding:22px}
}

@media(max-width:560px){
  .dg-lbc-auth-overlay{align-items:flex-end;padding:12px}
  .dg-lbc-auth-card{width:100%;border-radius:26px 26px 22px 22px}
  .dg-lbc-auth-head{padding:26px 22px 18px;gap:12px}
  .dg-lbc-auth-mark{width:48px;height:48px;flex-basis:48px;border-radius:16px}
  .dg-lbc-auth-inner{padding:0 22px 24px}
  .dg-lbc-auth-title{font-size:23px}
  .dg-lbc-auth-sub{font-size:13px}
  .dg-lbc-auth-socials{grid-template-columns:1fr}
  .dg-lbc-auth-close{right:14px;top:14px}
}
</style>
<div id="dgLbcAuthOverlay" class="dg-lbc-auth-overlay" aria-hidden="true">
  <div class="dg-lbc-auth-card" role="dialog" aria-modal="true">
    <button type="button" class="dg-lbc-auth-close" data-dg-lbc-auth-close>&times;</button>
    <div class="dg-lbc-auth-head">
      <div class="dg-lbc-auth-mark"><i class="fa-solid fa-comments"></i></div>
      <div>
        <div class="dg-lbc-auth-title"><?= t('Contact seller') ?></div>
        <div class="dg-lbc-auth-sub"><?= t('Sign in or create an account to send a secure marketplace message.') ?></div>
      </div>
    </div>
    <div class="dg-lbc-auth-inner">
      <div class="dg-lbc-auth-tabs">
        <button type="button" class="dg-lbc-auth-tab is-active" data-dg-lbc-auth-tab="login"><i class="fa-solid fa-lock-open me-1"></i> Login</button>
        <button type="button" class="dg-lbc-auth-tab" data-dg-lbc-auth-tab="register"><i class="fa-solid fa-user-plus me-1"></i> Register</button>
      </div>
      <form class="dg-lbc-auth-form is-active" id="dgLbcLoginForm" autocomplete="on">
        <input type="hidden" name="action" value="auth_client_login">
        <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
        <div class="dg-lbc-auth-field">
          <label class="dg-lbc-auth-label"><i class="fa-solid fa-envelope"></i> Email</label>
          <div class="dg-lbc-auth-input-wrap"><i class="fa-solid fa-at"></i><input class="dg-lbc-auth-input" type="email" name="email" placeholder="you@example.com" required></div>
        </div>
        <div class="dg-lbc-auth-field">
          <label class="dg-lbc-auth-label"><i class="fa-solid fa-key"></i> Password</label>
          <div class="dg-lbc-auth-input-wrap has-toggle"><i class="fa-solid fa-lock"></i><input class="dg-lbc-auth-input" type="password" name="password" placeholder="••••••••" required><button type="button" class="dg-lbc-auth-pass-toggle" data-dg-lbc-password-toggle aria-label="Show password"><i class="fa-solid fa-eye"></i></button></div>
        </div>
        <div class="dg-lbc-auth-row">
          <label class="dg-lbc-auth-check"><input type="checkbox" name="remember_me" value="1"><span class="dg-lbc-auth-check-box"></span><span>Remember me</span></label>
          <a href="javascript:void(0)" class="dg-lbc-auth-link" data-dg-lbc-forgot>Forgot password?</a>
        </div>
        <button class="dg-lbc-auth-submit" type="submit"><i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Sign in</button>
        <div class="dg-lbc-auth-error" id="dgLbcLoginError"></div>
      </form>
      <form class="dg-lbc-auth-form" id="dgLbcRegisterForm" autocomplete="on">
        <input type="hidden" name="action" value="auth_client_register">
        <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
        <div class="dg-lbc-auth-field">
          <label class="dg-lbc-auth-label"><i class="fa-solid fa-user"></i> Username</label>
          <div class="dg-lbc-auth-input-wrap"><i class="fa-solid fa-user"></i><input class="dg-lbc-auth-input" type="text" name="username" placeholder="Your username" required></div>
        </div>
        <div class="dg-lbc-auth-field">
          <label class="dg-lbc-auth-label"><i class="fa-solid fa-envelope"></i> Email</label>
          <div class="dg-lbc-auth-input-wrap"><i class="fa-solid fa-at"></i><input class="dg-lbc-auth-input" type="email" name="email" placeholder="you@example.com" required></div>
        </div>
        <div class="dg-lbc-auth-field">
          <label class="dg-lbc-auth-label"><i class="fa-solid fa-key"></i> Password</label>
          <div class="dg-lbc-auth-input-wrap has-toggle"><i class="fa-solid fa-lock"></i><input class="dg-lbc-auth-input" type="password" name="password" minlength="6" placeholder="Min. 6 characters" required><button type="button" class="dg-lbc-auth-pass-toggle" data-dg-lbc-password-toggle aria-label="Show password"><i class="fa-solid fa-eye"></i></button></div>
        </div>
        <div class="dg-lbc-auth-row"><label class="dg-lbc-auth-check"><input type="checkbox" name="tos" value="1" required><span class="dg-lbc-auth-check-box"></span><span>I agree to the terms</span></label></div>
        <button class="dg-lbc-auth-submit" type="submit"><i class="fa-solid fa-user-plus me-1"></i> Create account</button>
        <div class="dg-lbc-auth-error" id="dgLbcRegisterError"></div>
      </form>
      <div class="dg-lbc-auth-sep">or continue with</div>
      <div class="dg-lbc-auth-socials">
        <a class="dg-lbc-auth-social dg-lbc-auth-google" href="<?= $h($dgBaseUrl) ?>/auth/google"><i class="fab fa-google"></i> Google</a>
        <a class="dg-lbc-auth-social dg-lbc-auth-discord" href="<?= $h($dgBaseUrl) ?>/auth/discord"><i class="fab fa-discord"></i> Discord</a>
      </div>
    </div>
  </div>
</div>

<div id="dgLbcForgotOverlay" class="dg-lbc-forgot-overlay" aria-hidden="true">
  <div class="dg-lbc-forgot-card" role="dialog" aria-modal="true">
    <button type="button" class="dg-lbc-forgot-close" data-dg-lbc-forgot-close>&times;</button>
    <div class="dg-lbc-forgot-head">
      <div class="dg-lbc-forgot-mark"><i class="fa-solid fa-envelope-circle-check"></i></div>
      <div>
        <div class="dg-lbc-forgot-title"><?= t('Forgot Password') ?></div>
        <div class="dg-lbc-forgot-sub"><?= t("Enter your email and we'll send you a reset link.") ?></div>
      </div>
    </div>
    <form class="dg-lbc-forgot-form" id="dgLbcForgotForm" autocomplete="on">
      <input type="hidden" name="action" value="client_forgot_password">
      <div class="dg-lbc-auth-field">
        <label class="dg-lbc-auth-label"><i class="fa-solid fa-envelope"></i> <?= t('Email Address') ?></label>
        <div class="dg-lbc-auth-input-wrap"><i class="fa-solid fa-at"></i><input class="dg-lbc-auth-input" type="email" name="email" placeholder="email@example.com" required></div>
      </div>
      <button class="dg-lbc-auth-submit" type="submit"><i class="fa-solid fa-paper-plane me-1"></i> <?= t('Send Reset Link') ?></button>
      <div class="dg-lbc-auth-error" id="dgLbcForgotError"></div>
      <div class="dg-lbc-forgot-note"><?= t('After submitting, please check your inbox and spam folder.') ?></div>
    </form>
  </div>
</div>

<script>
(function(){
  'use strict';
  if (window.dgLbcChatReady) return;
  window.dgLbcChatReady = true;

  const SELLER_ID = <?= (int)$dgSellerId ?>;
  const SELLER_ICO = <?= json_encode($sellerIcon) ?>;
  const SELLER_INI = <?= json_encode($dgSellerInitials) ?>;
  const AJAX_URL = <?= json_encode($dgAjaxUrl) ?>;
  const BASE_URL = <?= json_encode($dgBaseUrl) ?>;
  const REF_TYPE = <?= json_encode($dgChatRefType) ?>;
  const REF_ID = <?= (int)$dgChatRefId ?>;
  const CHAT_OK = <?= $dgChatAllowed ? 'true' : 'false' ?>;
  const LOGGED_IN = <?= $dgClientLoggedIn ? 'true' : 'false' ?>;

  const overlay = document.getElementById('dgSellerChatModal');
  const msgBox = document.getElementById('dgLbcMessages');
  const form = document.getElementById('dgLbcForm');
  const inp = document.getElementById('dgLbcMsgInput');
  const sendBtn = document.getElementById('dgLbcSendBtn');
  const fileInput = document.getElementById('dgLbcFileInput');
  const imgBtn = document.getElementById('dgLbcImgBtn');
  const preview = document.getElementById('dgLbcPreview');
  const thumb = document.getElementById('dgLbcPreviewThumb');
  const prevName = document.getElementById('dgLbcPreviewName');
  const prevRm = document.getElementById('dgLbcPreviewRemove');
  const dot = document.getElementById('dgLbcUnreadDot');
  let poll = null, sig = '', conv = null;

  function parseJson(r){return r.text().then(function(t){t=(t||'').trim();try{return JSON.parse(t);}catch(e){var a=t.indexOf('{'),b=t.lastIndexOf('}');if(a!==-1&&b>a)return JSON.parse(t.slice(a,b+1));throw new Error(t.slice(0,200)||'Invalid response');}})}
  function esc(s){return String(s||'').replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];});}
  var authOverlay=document.getElementById('dgLbcAuthOverlay');
  var forgotOverlay=document.getElementById('dgLbcForgotOverlay');
  function authOpen(tab){
    if(!authOverlay){
      if(window.lbOpenClientAuth){window.lbOpenClientAuth(tab||'login');return;}
      document.dispatchEvent(new CustomEvent('lb:open-auth',{detail:{tab:tab||'login',source:'seller-chat'}}));
      return;
    }
    authOverlay.classList.add('is-open');
    authOverlay.setAttribute('aria-hidden','false');
    document.body.classList.add('dg-lbc-lock');
    document.querySelectorAll('[data-dg-lbc-auth-tab]').forEach(function(b){b.classList.toggle('is-active',b.dataset.dgLbcAuthTab===(tab||'login'));});
    document.querySelectorAll('.dg-lbc-auth-form').forEach(function(f){f.classList.toggle('is-active',(tab==='register'&&f.id==='dgLbcRegisterForm')||(tab!=='register'&&f.id==='dgLbcLoginForm'));});
  }
  function authClose(){if(!authOverlay)return;authOverlay.classList.remove('is-open');authOverlay.setAttribute('aria-hidden','true');document.body.classList.remove('dg-lbc-lock');}
  function forgotOpen(){if(!forgotOverlay)return;forgotOverlay.classList.add('is-open');forgotOverlay.setAttribute('aria-hidden','false');document.body.classList.add('dg-lbc-lock');var i=forgotOverlay.querySelector('input[name=email]');setTimeout(function(){i&&i.focus();},80);}
  function forgotClose(){if(!forgotOverlay)return;forgotOverlay.classList.remove('is-open');forgotOverlay.setAttribute('aria-hidden','true');if(!authOverlay||!authOverlay.classList.contains('is-open'))document.body.classList.remove('dg-lbc-lock');}
  window.dgOpenClientAuth=authOpen;
  if(!window.lbOpenClientAuth) window.lbOpenClientAuth=authOpen;
  function bindAuth(formId,errId){var f=document.getElementById(formId),er=document.getElementById(errId);if(!f)return;f.addEventListener('submit',async function(e){e.preventDefault();if(er)er.classList.remove('is-open');var btn=f.querySelector('[type=submit]');if(btn)btn.disabled=true;try{var res=await fetch(BASE_URL+'/ajax',{method:'POST',body:new FormData(f),credentials:'same-origin'});var d=await parseJson(res);if(d.redirectUrl){location.href=d.redirectUrl;return;}if(d.refreshPage||d.playSound==='success'){location.reload();return;}if(er){er.textContent=d.message||d.error||'Something went wrong.';er.classList.add('is-open');}}catch(_){if(er){er.textContent='Request failed.';er.classList.add('is-open');}}finally{if(btn)btn.disabled=false;}});}
  bindAuth('dgLbcLoginForm','dgLbcLoginError');
  bindAuth('dgLbcRegisterForm','dgLbcRegisterError');
  (function(){var f=document.getElementById('dgLbcForgotForm'),er=document.getElementById('dgLbcForgotError');if(!f)return;f.addEventListener('submit',async function(e){e.preventDefault();if(er)er.classList.remove('is-open');var btn=f.querySelector('[type=submit]');if(btn)btn.disabled=true;try{var res=await fetch(AJAX_URL,{method:'POST',body:new FormData(f),credentials:'same-origin'});var d=await parseJson(res);var msg=(d.sendToast&&d.sendToast.message)||d.message||d.error||'If the email exists, a reset link has been sent.';if(er){er.textContent=msg;er.classList.add('is-open');er.style.background='rgba(34,197,94,.11)';er.style.borderColor='rgba(34,197,94,.24)';er.style.color='#86efac';}if(d.success||d.sendToast){setTimeout(forgotClose,1800);}}catch(_){if(er){er.textContent='Request failed.';er.classList.add('is-open');}}finally{if(btn)btn.disabled=false;}});})();
  document.addEventListener('click',function(e){var tab=e.target.closest('[data-dg-lbc-auth-tab]');if(tab){e.preventDefault();authOpen(tab.dataset.dgLbcAuthTab);return;}if(e.target.closest('[data-dg-lbc-forgot]')){e.preventDefault();forgotOpen();return;}if(e.target.closest('[data-dg-lbc-forgot-close]')||e.target===forgotOverlay){e.preventDefault();forgotClose();return;}if(e.target.closest('[data-dg-lbc-auth-close]')||e.target===authOverlay){e.preventDefault();authClose();}});
  document.addEventListener('click',function(e){var btn=e.target.closest('[data-dg-lbc-password-toggle]');if(!btn)return;e.preventDefault();var wrap=btn.closest('.dg-lbc-auth-input-wrap');var input=wrap?wrap.querySelector('input[type="password"],input[type="text"]'):null;var icon=btn.querySelector('i');if(!input)return;var show=input.type==='password';input.type=show?'text':'password';btn.setAttribute('aria-label',show?'Hide password':'Show password');if(icon){icon.className=show?'fa-solid fa-eye-slash':'fa-solid fa-eye';}});
  function requireAuth(){chatClose();authOpen('login');}
  function chatOpen(){if(!LOGGED_IN){requireAuth();return;} if(!overlay)return; overlay.classList.add('is-open'); overlay.setAttribute('aria-hidden','false'); document.body.classList.add('dg-lbc-lock'); if(CHAT_OK){syncSend();loadMessages();clearInterval(poll);poll=setInterval(loadMessages,4000);setTimeout(function(){inp&&inp.focus();},100);}}
  function chatClose(){if(!overlay)return; overlay.classList.remove('is-open'); overlay.setAttribute('aria-hidden','true'); document.body.classList.remove('dg-lbc-lock'); clearInterval(poll);}
  window.openSellerChatModal = function(e){ if(e) e.preventDefault(); chatOpen(); return false; };
  document.addEventListener('click',function(e){if(e.target.closest('[data-seller-chat-open]')){e.preventDefault();chatOpen();return;} if(e.target.closest('[data-seller-chat-close]')){e.preventDefault();chatClose();return;} if(e.target===overlay)chatClose();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')chatClose();});
  function syncSend(){if(sendBtn)sendBtn.disabled=false;}
  function avatar(isSeller){if(isSeller&&SELLER_ICO)return '<div class="dg-lbc-msg-av"><img src="'+esc(SELLER_ICO)+'" alt=""></div>';return '<div class="dg-lbc-msg-av">'+esc(isSeller?SELLER_INI:'ME')+'</div>';}
  // System notes are plain text. Older notes may still contain markup from before,
  // so strip any tags first, then escape and turn bare URLs into links.
  function sysHtml(raw){
    var txt = String(raw||'')
      .replace(/<br\s*\/?>/gi,'\n')
      .replace(/<[^>]*>/g,'')
      .replace(/&nbsp;/gi,' ')
      .replace(/&amp;/gi,'&').replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'")
      .replace(/&lt;/gi,'<').replace(/&gt;/gi,'>')
      .replace(/\n{3,}/g,'\n\n')
      .trim();
    return esc(txt)
      .replace(/(https?:\/\/[^\s<]+)/g,'<a href="$1" target="_blank" rel="noopener">$1</a>')
      .replace(/\n/g,'<br>');
  }
  function renderMsg(m){if(!msgBox)return;var empty=document.getElementById('dgLbcEmpty');if(empty)empty.remove();
    if(m.sender_type==='system'||m.message_type==='system'){var sys=document.createElement('div');sys.className='dg-lbc-sys';sys.innerHTML='<div class="dg-lbc-sys-box">'+sysHtml(m.body)+(m.created_at_fmt?'<div class="dg-lbc-sys-time">'+esc(m.created_at_fmt)+'</div>':'')+'</div>';msgBox.appendChild(sys);msgBox.scrollTop=msgBox.scrollHeight;return;}
    var isSeller=m.sender_type==='seller';var isImg=m.message_type==='image';var el=document.createElement('div');el.className='dg-lbc-msg'+(isSeller?'':' me');var body=isImg?'<img src="'+esc(m.body)+'" onclick="window.open(this.src,\'_blank\')" alt="">':esc(m.body).replace(/\n/g,'<br>');el.innerHTML=avatar(isSeller)+'<div><div class="dg-lbc-msg-bubble">'+body+'</div><div class="dg-lbc-msg-time">'+esc(m.created_at_fmt||'')+'</div></div>';msgBox.appendChild(el);msgBox.scrollTop=msgBox.scrollHeight;}
  function loadMessages(){if(!CHAT_OK||!msgBox)return;var fd=new FormData();fd.append('action','client_seller_chat_load');fd.append('seller_id',SELLER_ID);fd.append('ref_type',REF_TYPE);fd.append('ref_id',REF_ID);fd.append('sig',sig);if(conv)fd.append('conv_id',conv);fetch(AJAX_URL,{method:'POST',body:fd,credentials:'same-origin'}).then(parseJson).then(function(d){if(d.conv_id)conv=d.conv_id;if(d.sig&&d.sig!==sig){sig=d.sig;msgBox.innerHTML='';(d.messages||[]).forEach(renderMsg);}if(dot)dot.style.display=(d.unread_client>0)?'block':'none';}).catch(function(){});}
  if(imgBtn&&fileInput)imgBtn.onclick=function(){fileInput.click();};
  if(fileInput)fileInput.onchange=function(){var f=this.files[0];if(!f)return;var r=new FileReader();r.onload=function(ev){if(thumb)thumb.src=ev.target.result;};r.readAsDataURL(f);if(prevName)prevName.textContent=f.name;if(preview)preview.classList.add('is-open');};
  if(prevRm)prevRm.onclick=function(){if(fileInput)fileInput.value='';if(preview)preview.classList.remove('is-open');};
  if(inp)inp.addEventListener('input',syncSend);
  if(form)form.onsubmit=async function(e){e.preventDefault();if(!LOGGED_IN){requireAuth();return;}var text=(inp?inp.value:'').trim();var hasFile=fileInput&&fileInput.files[0];if(!text&&!hasFile)return;if(sendBtn)sendBtn.disabled=true;var fd=new FormData(form);fd.set('action','client_seller_chat_send');fd.set('seller_id',SELLER_ID);fd.set('ref_type',REF_TYPE);fd.set('ref_id',REF_ID);if(conv)fd.set('conv_id',conv);try{var d=await fetch(AJAX_URL,{method:'POST',body:fd,credentials:'same-origin'}).then(parseJson);if(d.success){conv=d.conv_id||conv;if(text)renderMsg({sender_type:'client',body:text,created_at_fmt:d.created_at,message_type:'text'});if(d.image_url)renderMsg({sender_type:'client',body:d.image_url,created_at_fmt:d.created_at,message_type:'image'});if(inp)inp.value='';if(fileInput)fileInput.value='';if(preview)preview.classList.remove('is-open');loadMessages();}else{var em=d.message||d.error||(d.sendToast&&d.sendToast.message)||'Could not send message.';if(d.auth_required||/log.?in|unauthorized/i.test(em))requireAuth();else alert(em);}}catch(err){alert((err&&err.message)||'Could not send message.');}finally{syncSend();}};
  if(inp)inp.onkeydown=function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();form&&form.dispatchEvent(new Event('submit',{cancelable:true}));}};
})();
</script>
<?php endif; ?>

<style id="dg-view-card-seller-footer-clean-fix">
/* More Goods slider, keep cards next to each other and use a clean seller footer */
.dg-view-page .dg-cards-row{display:grid!important;grid-auto-flow:column!important;grid-auto-columns:minmax(340px,390px)!important;grid-template-columns:none!important;align-items:stretch!important;gap:22px!important;overflow-x:auto!important;overflow-y:hidden!important;scroll-snap-type:x proximity!important;padding:6px 4px 22px!important;}
.dg-view-page .dg-cards-row .dg-card-new{width:auto!important;max-width:none!important;min-width:0!important;overflow:hidden!important;display:flex!important;flex-direction:column!important;}
.dg-view-page .dg-card-new__foot{padding:0 18px 18px!important;border-top:1px solid rgba(255,255,255,.065)!important;background:linear-gradient(180deg,rgba(255,255,255,.015),rgba(255,255,255,.035))!important;}
.dg-view-page .dg-card-new__price-wrap{display:grid!important;grid-template-columns:1fr auto!important;grid-template-areas:"price cta" "seller seller"!important;align-items:center!important;gap:14px 16px!important;padding:16px 0 0!important;background:none!important;border:0!important;box-shadow:none!important;min-height:0!important;}
.dg-view-page .dg-card-new__price-wrap:before,.dg-view-page .dg-card-new__price-wrap:after{display:none!important;}
.dg-view-page .dg-card-new__price{grid-area:price!important;font-size:1.55rem!important;line-height:1!important;font-weight:950!important;color:#fff!important;text-align:left!important;text-shadow:0 0 18px rgba(99,102,241,.24)!important;}
.dg-view-page .dg-card-new__price::before{content:'PRICE';display:block;margin-bottom:7px;font-size:.64rem;line-height:1;font-weight:900;letter-spacing:.12em;color:rgba(255,255,255,.32);text-shadow:none;}
.dg-view-page .dg-card-new__cta{grid-area:cta!important;min-width:126px!important;height:42px!important;padding:0 18px!important;border-radius:13px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:7px!important;font-size:.82rem!important;font-weight:900!important;background:linear-gradient(135deg,#6366f1,#8b5cf6)!important;box-shadow:0 12px 28px rgba(99,102,241,.28)!important;}
.dg-view-page .dg-card-new__seller-mid{grid-area:seller!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:10px!important;min-height:48px!important;margin-top:2px!important;padding:10px 11px!important;border-radius:15px!important;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.025))!important;border:1px solid rgba(255,255,255,.075)!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;}
.dg-view-page .dg-card-new__seller-ava{width:32px!important;height:32px!important;min-width:32px!important;border-radius:999px!important;object-fit:cover!important;border:2px solid rgba(99,102,241,.45)!important;box-shadow:0 0 0 3px rgba(99,102,241,.10)!important;}
.dg-view-page .dg-card-new__seller-ava--ph{display:inline-flex!important;align-items:center!important;justify-content:center!important;color:#a5b4fc!important;background:rgba(99,102,241,.18)!important;}
.dg-view-page .dg-card-new__seller-copy{min-width:0!important;flex:1 1 auto!important;display:flex!important;align-items:center!important;gap:8px!important;}
.dg-view-page .dg-card-new__seller-name{min-width:0!important;display:flex!important;align-items:center!important;gap:5px!important;font-size:.88rem!important;font-weight:900!important;color:#fff!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.dg-view-page .dg-card-new__seller-label{font-size:.58rem!important;font-weight:900!important;letter-spacing:.12em!important;color:rgba(255,255,255,.35)!important;}
.dg-view-page .dg-card-new__seller-sold{margin-left:auto!important;display:inline-flex!important;align-items:center!important;gap:5px!important;height:26px!important;padding:0 8px!important;border-radius:8px!important;background:rgba(255,255,255,.06)!important;border:1px solid rgba(255,255,255,.075)!important;font-size:.72rem!important;font-weight:850!important;color:rgba(255,255,255,.74)!important;white-space:nowrap!important;}
.dg-view-page .dg-card-new__seller-sold i{color:#22c55e!important;font-size:.72rem!important;}
@media(max-width:640px){.dg-view-page .dg-cards-row{grid-auto-columns:minmax(300px,86vw)!important;gap:16px!important}.dg-view-page .dg-card-new__price-wrap{grid-template-columns:1fr!important;grid-template-areas:"price" "cta" "seller"!important}.dg-view-page .dg-card-new__cta{width:100%!important}.dg-view-page .dg-card-new__seller-mid{justify-content:flex-start!important}.dg-view-page .dg-card-new__seller-sold{margin-left:0!important}}
</style>


<style id="dg-card-account-unified-final">
/* Final Digital Goods card unification, same visual rhythm as account cards */
.digital-goods-shop-page #accountsGrid.dg-products-grid{
  display:grid!important;
  grid-template-columns:repeat(auto-fill,minmax(340px,400px))!important;
  gap:26px!important;
  justify-content:start!important;
  align-items:stretch!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new,
.dg-view-page .dg-cards-row .dg-card-new{
  display:flex!important;
  flex-direction:column!important;
  overflow:hidden!important;
  border-radius:22px!important;
  background:linear-gradient(180deg,rgba(255,255,255,.070),rgba(255,255,255,.026))!important;
  border:1px solid rgba(126,112,255,.26)!important;
  box-shadow:0 18px 46px rgba(0,0,0,.32), inset 0 1px 0 rgba(255,255,255,.055)!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new:hover,
.dg-view-page .dg-cards-row .dg-card-new:hover{
  transform:translateY(-4px)!important;
  border-color:rgba(126,112,255,.55)!important;
  box-shadow:0 26px 70px rgba(0,0,0,.45),0 0 42px rgba(99,102,241,.16)!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__body,
.dg-view-page .dg-cards-row .dg-card-new__body{
  flex:1 1 auto!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__foot,
.dg-view-page .dg-cards-row .dg-card-new__foot{
  margin-top:auto!important;
  padding:0!important;
  background:transparent!important;
  border-top:1px solid rgba(255,255,255,.075)!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__price-wrap,
.dg-view-page .dg-cards-row .dg-card-new__price-wrap{
  display:grid!important;
  grid-template-columns:1fr auto!important;
  grid-template-areas:
    "price cta"
    "seller seller"!important;
  align-items:center!important;
  gap:14px 16px!important;
  width:100%!important;
  min-height:0!important;
  padding:18px 18px 0!important;
  margin:0!important;
  border:0!important;
  border-radius:0!important;
  background:linear-gradient(180deg,rgba(255,255,255,.018),rgba(255,255,255,.040))!important;
  box-shadow:none!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__price-wrap::before,
.digital-goods-shop-page #accountsGrid .dg-card-new__price-wrap::after,
.dg-view-page .dg-cards-row .dg-card-new__price-wrap::before,
.dg-view-page .dg-cards-row .dg-card-new__price-wrap::after{display:none!important;}
.digital-goods-shop-page #accountsGrid .dg-card-new__price,
.dg-view-page .dg-cards-row .dg-card-new__price{
  grid-area:price!important;
  text-align:left!important;
  font-size:1.55rem!important;
  line-height:1!important;
  font-weight:950!important;
  color:#fff!important;
  white-space:nowrap!important;
  text-shadow:0 0 18px rgba(99,102,241,.20)!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__price::before,
.dg-view-page .dg-cards-row .dg-card-new__price::before{
  content:'PRICE';display:block;margin-bottom:7px;font-size:.62rem;line-height:1;font-weight:900;letter-spacing:.13em;color:rgba(255,255,255,.31);text-shadow:none;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__cta,
.dg-view-page .dg-cards-row .dg-card-new__cta{
  grid-area:cta!important;
  min-width:132px!important;
  height:42px!important;
  padding:0 18px!important;
  border-radius:14px!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:7px!important;
  font-size:.82rem!important;
  font-weight:900!important;
  color:#fff!important;
  background:linear-gradient(135deg,#6366f1,#8b5cf6)!important;
  box-shadow:0 14px 32px rgba(99,102,241,.34)!important;
  white-space:nowrap!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-mid,
.dg-view-page .dg-cards-row .dg-card-new__seller-mid{
  grid-area:seller!important;
  width:calc(100% + 36px)!important;
  margin:2px -18px 0!important;
  min-height:64px!important;
  padding:12px 14px!important;
  border-radius:0 0 22px 22px!important;
  border:0!important;
  border-top:1px solid rgba(255,255,255,.085)!important;
  background:linear-gradient(180deg,rgba(255,255,255,.030),rgba(255,255,255,.055))!important;
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  gap:10px!important;
  box-shadow:none!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-ava,
.dg-view-page .dg-cards-row .dg-card-new__seller-ava{
  width:38px!important;height:38px!important;min-width:38px!important;min-height:38px!important;
  border-radius:999px!important;object-fit:cover!important;
  border:2px solid rgba(99,102,241,.45)!important;box-shadow:0 0 0 4px rgba(99,102,241,.10)!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-ava--ph,
.dg-view-page .dg-cards-row .dg-card-new__seller-ava--ph{display:inline-flex!important;align-items:center!important;justify-content:center!important;color:#a5b4fc!important;background:rgba(99,102,241,.18)!important;}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-copy,
.dg-view-page .dg-cards-row .dg-card-new__seller-copy{
  min-width:0!important;flex:1 1 auto!important;display:flex!important;align-items:center!important;gap:7px!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-name,
.dg-view-page .dg-cards-row .dg-card-new__seller-name{
  min-width:0!important;max-width:100%!important;display:inline-flex!important;align-items:center!important;gap:5px!important;
  font-size:.90rem!important;font-weight:900!important;line-height:1!important;color:#fff!important;text-align:left!important;
  white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-label,
.dg-view-page .dg-cards-row .dg-card-new__seller-label{
  font-size:.60rem!important;font-weight:900!important;letter-spacing:.13em!important;color:rgba(255,255,255,.34)!important;flex:0 0 auto!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-name::after,
.dg-view-page .dg-cards-row .dg-card-new__seller-name::after{
  content:'';width:13px;height:13px;min-width:13px;border-radius:999px;background:#9ca3af;
  box-shadow:0 0 10px rgba(148,163,184,.55);
  -webkit-mask:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="black" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 1 1 1.4-1.4l3.8 3.79 6.8-6.79a1 1 0 0 1 1.4 0Z"/></svg>') center/contain no-repeat;
  mask:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="black" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 1 1 1.4-1.4l3.8 3.79 6.8-6.79a1 1 0 0 1 1.4 0Z"/></svg>') center/contain no-repeat;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-sold,
.dg-view-page .dg-cards-row .dg-card-new__seller-sold{
  margin-left:auto!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:5px!important;
  height:28px!important;min-width:72px!important;padding:0 9px!important;border-radius:9px!important;
  background:rgba(255,255,255,.065)!important;border:1px solid rgba(255,255,255,.085)!important;
  font-size:.72rem!important;font-weight:900!important;color:rgba(255,255,255,.72)!important;white-space:nowrap!important;
}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-sold i,
.dg-view-page .dg-cards-row .dg-card-new__seller-sold i{display:none!important;}
.digital-goods-shop-page #accountsGrid .dg-card-new__seller-sold::after,
.dg-view-page .dg-cards-row .dg-card-new__seller-sold::after{
  content:'\f164';font-family:'Font Awesome 6 Free','Font Awesome 5 Free';font-weight:900;color:#22c55e;font-size:.78rem;
  width:28px;height:28px;border-radius:9px;margin-right:-9px;margin-left:6px;display:inline-flex;align-items:center;justify-content:center;
  background:rgba(34,197,94,.13);border-left:1px solid rgba(34,197,94,.22);
}
/* More from seller must stay horizontal */
.dg-view-page .dg-cards-row{
  display:flex!important;
  flex-wrap:nowrap!important;
  gap:22px!important;
  overflow-x:auto!important;
  overflow-y:hidden!important;
  scroll-snap-type:x proximity!important;
  padding:6px 4px 24px!important;
}
.dg-view-page .dg-cards-row .dg-card-new{
  flex:0 0 390px!important;
  width:390px!important;
  max-width:390px!important;
  min-width:390px!important;
  scroll-snap-align:start!important;
}
@media(max-width:640px){
  .digital-goods-shop-page #accountsGrid.dg-products-grid{grid-template-columns:1fr!important;}
  .dg-view-page .dg-cards-row .dg-card-new{flex-basis:86vw!important;width:86vw!important;min-width:86vw!important;max-width:86vw!important;}
  .digital-goods-shop-page #accountsGrid .dg-card-new__price-wrap,
  .dg-view-page .dg-cards-row .dg-card-new__price-wrap{grid-template-columns:1fr!important;grid-template-areas:"price" "cta" "seller"!important;}
  .digital-goods-shop-page #accountsGrid .dg-card-new__cta,
  .dg-view-page .dg-cards-row .dg-card-new__cta{width:100%!important;}
}
</style>

<style id="dg-card-hover-border-and-seller-footer-final-fix">
/* Keeps the hover border visible on every side and restores the account-style seller footer. */
.dg-view-page .dg-cards-row{
  overflow:visible!important;
  padding-top:8px!important;
  padding-bottom:24px!important;
}
.dg-view-page .dg-cards-row .dg-card-new{
  position:relative!important;
  overflow:visible!important;
  isolation:isolate!important;
  background:linear-gradient(180deg,rgba(255,255,255,.070),rgba(255,255,255,.026))!important;
}
.dg-view-page .dg-cards-row .dg-card-new::before{
  content:""!important;
  position:absolute!important;
  inset:0!important;
  z-index:6!important;
  pointer-events:none!important;
  border-radius:22px!important;
  border:1px solid rgba(126,112,255,.30)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.055)!important;
  opacity:1!important;
  transition:border-color .18s ease, box-shadow .18s ease!important;
}
.dg-view-page .dg-cards-row .dg-card-new:hover::before{
  border-color:rgba(126,112,255,.70)!important;
  box-shadow:0 0 0 1px rgba(126,112,255,.20),0 0 42px rgba(99,102,241,.16),inset 0 1px 0 rgba(255,255,255,.08)!important;
}
.dg-view-page .dg-cards-row .dg-card-new__banner{
  border-radius:22px 22px 0 0!important;
  overflow:hidden!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-ava{
  display:inline-flex!important;
  width:38px!important;
  height:38px!important;
  min-width:38px!important;
  min-height:38px!important;
  border-radius:999px!important;
  object-fit:cover!important;
  border:2px solid rgba(99,102,241,.45)!important;
  box-shadow:0 0 0 4px rgba(99,102,241,.10)!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-copy{
  min-width:0!important;
  flex:1 1 auto!important;
  display:flex!important;
  align-items:center!important;
  gap:8px!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-sold{
  margin-left:auto!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:6px!important;
  min-width:72px!important;
  height:28px!important;
  padding:0 9px!important;
  border-radius:9px!important;
  background:rgba(255,255,255,.065)!important;
  border:1px solid rgba(255,255,255,.085)!important;
  color:rgba(255,255,255,.76)!important;
  font-size:.72rem!important;
  font-weight:900!important;
  white-space:nowrap!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-sold i{
  display:inline-flex!important;
  color:#22c55e!important;
  font-size:.74rem!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-sold::before,
.dg-view-page .dg-cards-row .dg-card-new__seller-sold::after{
  content:none!important;
  display:none!important;
}
</style>


<style id="dg-view-seller-footer-match-shop-v4">
/* More Goods cards, same full-width seller footer as shop cards */
.dg-view-page .dg-cards-row .dg-card-new__foot{
  padding:0!important;
  background:transparent!important;
  border-top:1px solid rgba(255,255,255,.06)!important;
  margin-top:auto!important;
}
.dg-view-page .dg-cards-row .dg-card-new__price-wrap{
  display:grid!important;
  grid-template-columns:1fr auto!important;
  grid-template-areas:
    "price cta"
    "seller seller"!important;
  align-items:center!important;
  gap:14px!important;
  width:100%!important;
  min-height:0!important;
  padding:16px 0 0!important;
  margin:0!important;
  border:0!important;
  border-radius:0!important;
  background:transparent!important;
  box-shadow:none!important;
}
.dg-view-page .dg-cards-row .dg-card-new__price-wrap::before,
.dg-view-page .dg-cards-row .dg-card-new__price-wrap::after{
  content:none!important;
  display:none!important;
}
.dg-view-page .dg-cards-row .dg-card-new__price{
  grid-area:price!important;
  justify-self:start!important;
  align-self:center!important;
  margin-left:16px!important;
  text-align:left!important;
  font-size:1.55rem!important;
  line-height:1!important;
  font-weight:950!important;
  color:#fff!important;
  white-space:nowrap!important;
  text-shadow:0 0 18px rgba(99,102,241,.20)!important;
}
.dg-view-page .dg-cards-row .dg-card-new__price::before{
  content:'PRICE'!important;
  display:block!important;
  margin-bottom:7px!important;
  font-size:.62rem!important;
  line-height:1!important;
  font-weight:900!important;
  letter-spacing:.13em!important;
  color:rgba(255,255,255,.31)!important;
  text-shadow:none!important;
}
.dg-view-page .dg-cards-row .dg-card-new__cta{
  grid-area:cta!important;
  justify-self:end!important;
  align-self:center!important;
  margin-right:16px!important;
  min-width:132px!important;
  height:42px!important;
  padding:0 18px!important;
  border-radius:14px!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:7px!important;
  font-size:.82rem!important;
  font-weight:900!important;
  color:#fff!important;
  background:linear-gradient(135deg,#6366f1,#8b5cf6)!important;
  box-shadow:0 14px 32px rgba(99,102,241,.34)!important;
  white-space:nowrap!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-mid{
  grid-area:seller!important;
  width:100%!important;
  min-width:100%!important;
  max-width:none!important;
  margin:0!important;
  min-height:62px!important;
  padding:10px 16px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:10px!important;
  background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.018))!important;
  border:0!important;
  border-top:1px solid rgba(255,255,255,.08)!important;
  border-radius:0 0 22px 22px!important;
  box-sizing:border-box!important;
  box-shadow:none!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-mid::before,
.dg-view-page .dg-cards-row .dg-card-new__seller-mid::after{
  content:""!important;
  display:block!important;
  flex:1 1 28px!important;
  min-width:18px!important;
  height:1px!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-mid::before{
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.14))!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-mid::after{
  background:linear-gradient(90deg,rgba(255,255,255,.14),transparent)!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-ava{
  display:inline-flex!important;
  width:38px!important;
  height:38px!important;
  min-width:38px!important;
  min-height:38px!important;
  flex:0 0 38px!important;
  border-radius:50%!important;
  object-fit:cover!important;
  border:2px solid rgba(99,102,241,.55)!important;
  box-shadow:0 0 0 4px rgba(99,102,241,.13),0 10px 22px rgba(0,0,0,.32)!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-ava--ph{
  align-items:center!important;
  justify-content:center!important;
  color:#a5b4fc!important;
  background:rgba(99,102,241,.18)!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-copy{
  min-width:0!important;
  max-width:calc(100% - 120px)!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:7px!important;
  flex-wrap:wrap!important;
  text-align:center!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-label{
  color:rgba(255,255,255,.38)!important;
  font-size:10px!important;
  font-weight:950!important;
  text-transform:uppercase!important;
  letter-spacing:.075em!important;
  line-height:1!important;
  flex:0 0 auto!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-name{
  display:inline-flex!important;
  align-items:center!important;
  gap:0!important;
  margin:0!important;
  color:#fff!important;
  font-size:11px!important;
  font-weight:950!important;
  line-height:1.2!important;
  max-width:100%!important;
  white-space:normal!important;
  overflow:visible!important;
  text-overflow:clip!important;
  overflow-wrap:anywhere!important;
  word-break:break-word!important;
  text-align:center!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-name::before,
.dg-view-page .dg-cards-row .dg-card-new__seller-name::after,
.dg-view-page .dg-cards-row .dg-card-new__seller-sold::before,
.dg-view-page .dg-cards-row .dg-card-new__seller-sold::after{
  content:none!important;
  display:none!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-sold{
  flex:0 0 auto!important;
  display:inline-flex!important;
  align-items:center!important;
  gap:3px!important;
  margin:0!important;
  padding:6px 9px!important;
  border-radius:8px!important;
  background:rgba(255,255,255,.075)!important;
  border:1px solid rgba(255,255,255,.10)!important;
  color:rgba(255,255,255,.72)!important;
  font-size:10.5px!important;
  font-weight:850!important;
  line-height:1!important;
  white-space:nowrap!important;
}
.dg-view-page .dg-cards-row .dg-card-new__seller-sold i{
  display:inline-flex!important;
  color:#4ade80!important;
  font-size:10px!important;
}
@media(max-width:640px){
  .dg-view-page .dg-cards-row .dg-card-new__price-wrap{
    grid-template-columns:1fr!important;
    grid-template-areas:"price" "cta" "seller"!important;
  }
  .dg-view-page .dg-cards-row .dg-card-new__cta{
    width:calc(100% - 32px)!important;
    margin:0 16px!important;
  }
  .dg-view-page .dg-cards-row .dg-card-new__seller-copy{
    max-width:calc(100% - 98px)!important;
  }
}
</style>
