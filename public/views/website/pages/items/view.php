
<?php

if (!function_exists('lb_db_seller_total_sales')) {
    function lb_db_seller_total_sales(int $sellerId, int $fallback = 0): int
    {
        static $cache = [];

        if ($sellerId <= 0) {
            return max(0, $fallback);
        }
        if (array_key_exists($sellerId, $cache)) {
            return $cache[$sellerId];
        }

        global $db;
        if (!empty($db)) {
            try {
                $value = $db->cell(
                    "SELECT total_sales FROM seller_stats WHERE seller_id = ? LIMIT 1",
                    $sellerId
                );
                if ($value !== false && $value !== null) {
                    return $cache[$sellerId] = max(0, (int)$value);
                }
            } catch (Throwable $e) {
            }
        }

        if (function_exists('get_seller_total_sales')) {
            try {
                return $cache[$sellerId] = max(0, (int)get_seller_total_sales($sellerId));
            } catch (Throwable $e) {
            }
        }

        return $cache[$sellerId] = max(0, $fallback);
    }
}


if (!function_exists('item_display_text')) {
    function item_display_text($value, string $default = ''): string
    {
        $raw = ($value === null || $value === '') ? $default : (string)$value;
        return htmlspecialchars(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('seller_detail_is_online')) {
    function seller_detail_is_online($seller): bool
    {
        if (empty($seller) || !is_array($seller)) return false;
        if (array_key_exists('is_online', $seller)) return (int)$seller['is_online'] === 1;
        if (array_key_exists('seller_is_online', $seller)) return (int)$seller['seller_is_online'] === 1;
        $sellerId = (int)($seller['id'] ?? 0);
        if ($sellerId <= 0) return false;
        try {
            global $db;
            if (empty($db)) return false;
            $table = $db->cell("SHOW TABLES LIKE 'seller_session_logs'");
            if (empty($table)) return false;
            $row = $db->row(
                "SELECT 1 AS online FROM seller_session_logs sslog WHERE sslog.seller_id = ? AND sslog.created_at >= (NOW() - INTERVAL 5 MINUTE) ORDER BY sslog.id DESC LIMIT 1",
                $sellerId
            );
            return !empty($row);
        } catch (Throwable $e) {
            return false;
        }
    }
}
?>
<?php include_once __DIR__ . '/../../components/seller/seller-footer.php'; ?>
<?php

$item = $item ?? [];
$seller = $seller ?? null;
$seller_items = $seller_items ?? [];

$images = json_decode((string)($item['images'] ?? '[]'), true);
if (!is_array($images)) $images = [];
if (empty($images)) $images = [ASSET_URL . '/public/uploads/icons/default2.png'];

$priceEur = ((int)($item['price'] ?? 0)) / 100;
$stock = max(0, (int)($item['stock'] ?? 1));
$minQty = max(1, (int)($item['min_purchase_qty'] ?? 1));
$initialQty = min(max($minQty, 1), max($stock, $minQty));


if (!function_exists('seller_rank_icon_meta')) {
    function seller_rank_icon_meta($rank, $rankIcon = ''): array
    {
        $rank = trim((string)$rank);
        $rankIcon = trim((string)$rankIcon);

        $map = [
            'beginner'      => ['class' => 'fa-solid fa-badge-check', 'color' => '#94a3b8'],
            'expert seller' => ['class' => 'fa-solid fa-badge-check', 'color' => '#22c55e'],
            'pro seller'    => ['class' => 'fa-solid fa-badge-check', 'color' => '#8b5cf6'],
            'mythic seller' => ['class' => 'fa-solid fa-badge-check', 'color' => '#fbbf24'],
        ];

        $key = strtolower($rank);
        $meta = $map[$key] ?? $map['beginner'];

        if ($rankIcon !== '') {
            $meta['raw'] = $rankIcon;
            if (stripos($rankIcon, 'text-emerald') !== false) $meta['color'] = '#22c55e';
            elseif (stripos($rankIcon, 'text-violet') !== false) $meta['color'] = '#8b5cf6';
            elseif (stripos($rankIcon, 'text-amber') !== false) $meta['color'] = '#fbbf24';
            elseif (stripos($rankIcon, 'text-slate') !== false || stripos($rankIcon, 'text-gray') !== false) $meta['color'] = '#94a3b8';
        }

        $meta['label'] = $rank !== '' ? $rank : 'Seller Rank';
        return $meta;
    }
}

if (!function_exists('items_shop_type_label')) {
    function items_shop_type_label(string $type): string {
        $map = [
            'skins'         => 'Skins',         'skin'          => 'Skins',
            'chests-keys'   => 'Chests & Keys', 'chest-key'     => 'Chests & Keys',
            'chest'         => 'Chests & Keys',
            'orbs'          => 'Orbs',          'orb'           => 'Orbs',
            'capsules'      => 'Capsules',      'capsule'       => 'Capsules',
            'event-pass'    => 'Event Pass',    'event pass'    => 'Event Pass',
            'pass'          => 'Event Pass',
            'bundles'       => 'Bundles',       'bundle'        => 'Bundles',
            'tft-item'      => 'TFT Item',      'tft item'      => 'TFT Item',
            'tft'           => 'TFT Item',
            'mystery-gift'  => 'Mystery Gift',  'mystery gift'  => 'Mystery Gift',
            'gifting'       => 'Mystery Gift',
        ];
        $k = strtolower(trim($type));
        return $map[$k] ?? ucwords(str_replace(['_', '-'], ' ', $type));
    }
}
if (!function_exists('items_shop_type_key')) {
    function items_shop_type_key(string $type): string {
        $label = strtolower(items_shop_type_label($type));
        return trim(preg_replace('/[^a-z0-9]+/', '-', $label), '-');
    }
}
if (!function_exists('items_shop_type_img')) {
    function items_shop_type_img(string $type): ?string {
        $stems = [
            'skins'        => 'skins-item',
            'chests-keys'  => 'chest-item',
            'orbs'         => 'orbs-item',
            'capsules'     => 'capsules-item',
            'event-pass'   => 'event-pass-item',
            'bundles'      => 'bundle-item',
            'tft-item'     => 'tft-item',
            'mystery-gift' => null,
        ];
        $key = items_shop_type_key($type);
        if (!array_key_exists($key, $stems) || $stems[$key] === null) return null;
        return rtrim(ASSET_URL, '/') . '/website/images/items/' . $stems[$key] . '.webp';
    }
}
if (!function_exists('items_shop_type_fa')) {
    function items_shop_type_fa(string $type): string {
        $fa = [
            'skins'        => 'fa-solid fa-shirt',
            'chests-keys'  => 'fa-solid fa-key',
            'orbs'         => 'fa-solid fa-circle-nodes',
            'capsules'     => 'fa-solid fa-capsules',
            'event-pass'   => 'fa-solid fa-ticket',
            'bundles'      => 'fa-solid fa-gift',
            'tft-item'     => 'fa-solid fa-chess-board',
            'mystery-gift' => 'fa-solid fa-sparkles',
        ];
        return $fa[items_shop_type_key($type)] ?? 'fa-solid fa-tag';
    }
}

$typeLabel = items_shop_type_label((string)($item['type'] ?? ''));
$typeImg   = items_shop_type_img((string)($item['type'] ?? ''));
$typeFa    = items_shop_type_fa((string)($item['type'] ?? ''));
$itemSlugOrId = !empty($item['slug']) ? $item['slug'] : (string)(int)($item['id'] ?? 0);

if (!function_exists('item_price_numeric_from_formatted')) {
    function item_price_numeric_from_formatted(string $formatted): float {
        $s = preg_replace('/[^0-9,.\-]/', '', $formatted);
        if ($s === null || $s === '') return 0.0;
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            $s = str_replace(',', '', $s);
        } elseif (strpos($s, ',') !== false) {
            $s = str_replace(',', '.', $s);
        }
        return (float)$s;
    }
}

if (!function_exists('item_get_currency_number_format')) {
    function item_get_currency_number_format(string $currencyCode): array {
        $currencyCode = strtoupper(trim($currencyCode));
        $decimals = 2;
        $decimalSeparator = '.';
        $thousandsSeparator = ',';
        if (in_array($currencyCode, ['EUR', 'BRL', 'TRY'], true)) {
            $decimalSeparator = ',';
            $thousandsSeparator = '.';
        }
        return [$decimals, $decimalSeparator, $thousandsSeparator];
    }
}

if (!function_exists('item_format_number_for_currency')) {
    function item_format_number_for_currency(float $amount, string $currencyCode): string {
        [$decimals, $decimalSeparator, $thousandsSeparator] = item_get_currency_number_format($currencyCode);
        return number_format($amount, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}

if (!function_exists('item_detect_currency_rate')) {
    function item_detect_currency_rate(string $currencyCode): ?float {
        $currencyCode = strtoupper(trim($currencyCode));
        if ($currencyCode === 'EUR') return 1.0;
        $sessionKeys = ['exchange_rates', 'currency_rates', 'rates', 'fx_rates', 'conversion_rates'];
        foreach ($sessionKeys as $sessionKey) {
            if (!isset($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) continue;
            foreach ($_SESSION[$sessionKey] as $key => $value) {
                if (!is_numeric($value) || (float)$value <= 0) continue;
                $normalizedKey = strtoupper(trim((string)$key));
                if ($normalizedKey === $currencyCode) return (float)$value;
                if ($currencyCode === 'USD' && in_array($normalizedKey, ['USDT', 'USDTUSD'], true)) return (float)$value;
            }
        }
        $singleRateKeys = ['currency_rate', 'currency_multiplier', 'exchange_rate', 'fx_rate'];
        foreach ($singleRateKeys as $singleRateKey) {
            if (isset($_SESSION[$singleRateKey]) && is_numeric($_SESSION[$singleRateKey]) && (float)$_SESSION[$singleRateKey] > 0) {
                return (float)$_SESSION[$singleRateKey];
            }
        }
        if (function_exists('get_exchange_rate')) {
            $fallbackRate = (float)get_exchange_rate();
            if ($fallbackRate > 0) return $fallbackRate;
        }
        return null;
    }
}

if (!function_exists('item_format_price_for_currency')) {
    function item_format_price_for_currency(int $priceCents, string $currencyCode): array {
        $currencyCode = strtoupper(trim($currencyCode));
        $baseEur = round($priceCents / 100, 2);
        $numeric = $baseEur;
        if ($currencyCode !== 'EUR') {
            $rate = item_detect_currency_rate($currencyCode);
            if ($rate !== null) {
                $numeric = round($baseEur * $rate, 2);
            } else {
                $fallbackFormatted = function_exists('util_format_price_display')
                    ? (string) util_format_price_display($priceCents)
                    : item_format_number_for_currency($baseEur, $currencyCode);
                $fallbackNumeric = item_price_numeric_from_formatted($fallbackFormatted);
                if ($fallbackNumeric > 0 && abs($fallbackNumeric - $baseEur) > 0.0001) {
                    $numeric = round($fallbackNumeric, 2);
                }
            }
        }
        return [
            'formatted' => item_format_number_for_currency($numeric, $currencyCode),
            'numeric'   => $numeric,
        ];
    }
}

$currencyCode    = $_SESSION['currency'] ?? 'EUR';
$currencySymbol  = function_exists('util_format_currency_display')
    ? util_format_currency_display($currencyCode)
    : ($currencyCode === 'USD' ? '$' : '€');
$unitPriceParts  = item_format_price_for_currency((int)($item['price'] ?? 0), $currencyCode);
$unitDisplayFormatted        = $unitPriceParts['formatted'];
$unitDisplayNumeric          = $unitPriceParts['numeric'];
$initialTotalDisplayFormatted = item_format_number_for_currency($unitDisplayNumeric * $initialQty, $currencyCode);
$initialUnitPriceWithCurrency  = $currencySymbol . $unitDisplayFormatted;
$initialTotalPriceWithCurrency = $currencySymbol . $initialTotalDisplayFormatted;
?>

<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'view-account-page view-item-page']) ?>

<?php
$sellerTotalSoldDisplay = lb_db_seller_total_sales(
    (int)($seller['id'] ?? $seller['seller_id'] ?? 0),
    (int)($seller['seller_total_sales'] ?? $seller['total_sales'] ?? $seller['total_sold'] ?? $seller['seller_sold'] ?? 0)
);
?>
<?= $this->start('styles') ?>

<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<style>
.seller-online-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    display: inline-flex;
    margin-left: 2px;
    background: #22c55e;
    box-shadow: 0 0 0 0 rgba(34,197,94,.7), 0 0 12px rgba(34,197,94,.95);
    animation: sellerOnlinePulse 1.35s ease-in-out infinite;
    flex: 0 0 auto;
}
@keyframes sellerOnlinePulse {
    0% { transform: scale(.9); box-shadow: 0 0 0 0 rgba(34,197,94,.7), 0 0 10px rgba(34,197,94,.75); }
    70% { transform: scale(1.18); box-shadow: 0 0 0 7px rgba(34,197,94,0), 0 0 16px rgba(34,197,94,.95); }
    100% { transform: scale(.9); box-shadow: 0 0 0 0 rgba(34,197,94,0), 0 0 10px rgba(34,197,94,.75); }
}
/* Highlights badges */
.view-item-page .highlights .badge    { font-size: 13px; }
.view-item-page .highlights .badge i  { font-size: 13px; }

/* Description */
.item-description                     { font-size: 14px; }
.item-desc-more                       { font-size: 13px; }
.item-desc-more i                     { font-size: 11px; }

/* Feature grid */
.item-feature__label                  { font-size: 10px; }
.item-feature__value                  { font-size: 17px; }

/* Seller card */
.seller-profile-card__label           { font-size: 10px; }
.seller-profile-card__name            { font-size: 14px; }
.seller-profile-card__name .verified  { font-size: 12px; }
.seller-profile-card__rank{display:inline-flex;align-items:center;justify-content:center;margin-left:6px;font-size:14px;line-height:1;}
.seller-profile-card__rank i{filter:drop-shadow(0 0 10px rgba(34,197,94,.22));}
.seller-stat__value                   { font-size: 15px; }
.seller-stat__label                   { font-size: 10px; }

/* Checkout card */
.item-checkout-card .tagline          { font-size: 13px; }
.item-checkout-card .checkout-features li { font-size: 13px; }
.item-checkout-card .qty-btn          { font-size: 26px; }
.item-checkout-card .qty-input        { font-size: 30px; }
.item-checkout-card .qty-meta         { font-size: 11px; }
.item-checkout-card .total-label      { font-size: 15px; }
.item-checkout-card .cashback-badge   { font-size: 12px; }
.item-checkout-card .price-main       { font-size: 30px; }
.item-checkout-card .price-main small { font-size: 14px; }
.item-checkout-card .btn              { font-size: 18px; }
.item-checkout-card .trust-badge      { font-size: 12px; }

/* FAQ */
.item-faq__title                      { font-size: 15px; }
.item-faq__q                          { font-size: 15px; }
.item-faq__q i                        { font-size: 12px; }
.item-faq__a                          { font-size: 14px; }

/* Testimonials */
.account-testimonials .section-title  { font-size: 16px; }
.testimonial-card__stars              { font-size: 13px; }
.testimonial-card__text               { font-size: 14px; }
.testimonial-card__author-name        { font-size: 13px; }
.testimonial-card__author-rank        { font-size: 11px; }
.trev-btn                             { font-size: 11px; }
.trev-viewall                         { font-size: 11px; }

/* Seller accounts fullwidth section */
.seller-accounts-fullwidth__title     { font-size: 1.15vw; }
.saf-prev, .saf-next                  { font-size: 0.729vw; }
/* Nuclear fix: any button Slick injects that isn't prev/next arrow */
.saf-slider .slick-slider > button:not(.slick-prev):not(.slick-next),
.gallery-desktop .slick-slider > button:not(.slick-prev):not(.slick-next),
.gallery-mobile .slick-slider > button:not(.slick-prev):not(.slick-next) {
    display: none !important; font-size: 0 !important; width: 0 !important; height: 0 !important; overflow: hidden !important;
}
.slick-slide .slick-sr-only,
.slick-list .slick-sr-only { display: none !important; }
/* The '...' is Slick's autoplay pause button - hide it completely */
.slick-slider button.slick-autoplay-toggle-button,
.slick-autoplay-toggle-button { display: none !important; }
/* Also hide any stray text nodes Slick injects */
.saf-slider > .slick-list ~ *:not(.slick-arrow),
.saf-slider > *:not(.slick-list):not(.slick-arrow):not(.slick-track) { display: none !important; }
/* Slick wrapper must not clip flex children */
.saf-slider .slick-slide > div { overflow: visible !important; }
.saf-slider .slick-slide .saf-slide { overflow: visible !important; }
.saf-slider .slick-track { overflow: visible !important; }

/* ── Header removed: container needs its own offset below the fixed lb-game-subnav.
   --lb-content-top is measured live via JS (navbar + sale-banner + subnav/mobile-gamebar,
   whichever are actually visible) so content always starts right below whatever bar
   is lowest, on both mobile and desktop, whether the sale banner is shown or not. ── */
.view-item-page .container {
    padding-top: calc(var(--lb-content-top, 96px) + 10px);
    box-sizing: border-box;
}

/* ── Layout ── */
.view-item-page .layout { align-items: stretch; }
.view-item-page .layout .left  { min-width: 0; overflow: hidden; }
.view-item-page .layout .right {
    flex-shrink: 0; width: 26.042vw; min-width: 0;
    display: flex; flex-direction: column;
}

/* ---- Slim guarantee ribbon (same as accounts view_generic) ---- */
.lbv-ribbon{
  display:flex; flex-wrap:wrap; align-items:center; justify-content:center;
  gap:6px 4px; margin:16px 0 22px; padding:13px 16px;
  border-top:1px solid rgba(255,255,255,.07);
  border-bottom:1px solid rgba(255,255,255,.07);
}
.lbv-ribbon__item{
  display:inline-flex; align-items:center; gap:8px;
  font-size:13px; font-weight:750; color:#d4d8ea; white-space:nowrap;
}
.lbv-ribbon__item i{color:#8ea5ff; font-size:14px;}
.lbv-ribbon__dot{width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,.18); margin:0 14px;}

/* ── Highlights ── */
.view-item-page .highlights { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
.view-item-page .highlights .badge {
display: inline-flex; align-items: center; gap: 7px;
padding: 8px 14px; border-radius: 999px;
background: rgba(99,102,241,.18); border: 1px solid rgba(99,102,241,.34);
color: #fff; font-size: 13px; font-weight: 700;
}
.view-item-page .highlights .badge img { width: 15px; height: 15px; object-fit: contain; }
.view-item-page .highlights .badge i   { font-size: 13px; }

/* ── Item description & features ── */
.item-description-wrap { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,.06); }
.item-description {
color: rgba(255,255,255,.82); line-height: 1.8; font-size: 14px;
max-height: 120px; overflow: hidden; transition: max-height 0.4s ease;
}
.item-description.expanded { max-height: 2000px; }
.item-desc-more {
display: none; align-items: center; justify-content: center; gap: 6px;
margin-top: 12px; width: 100%;
background: rgba(99,102,241,.12); border: 1px solid rgba(99,102,241,.3);
border-radius: 8px; padding: 9px 16px;
color: #a5b4fc; font-size: 13px; font-weight: 700;
cursor: pointer; transition: background .2s, color .2s;
}
.item-desc-more:hover { background: rgba(99,102,241,.22); color: #fff; }
.item-desc-more i { transition: transform .3s; font-size: 11px; }
.item-desc-more.open i { transform: rotate(180deg); }

/* ── Mobile below-checkout block ── */
.mobile-below-checkout { display: none; }
.item-feature-grid {
/* auto-fit so 3 features fill one row on desktop instead of leaving a hole in a 2-col grid */
display: grid;
grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
gap: 10px;
}
.item-feature {
background: rgba(255,255,255,.03);
border: 1px solid rgba(255,255,255,.07);
border-radius: 12px; padding: 12px 14px;
}
.item-feature__label {
font-size: 10px; color: rgba(255,255,255,.42);
text-transform: uppercase; letter-spacing: .08em;
font-weight: 700; margin-bottom: 8px;
}
.item-feature__value {
display: flex; align-items: center; gap: 8px;
color: #fff; font-weight: 800; font-size: 17px;
}
.item-feature__value img { width: 16px; height: 16px; object-fit: contain; }
.item-feature__value i   { color: #8ea5ff; width: 16px; text-align: center; }

/* ── Gallery (Item) ── */
.view-item-page .card.gallery-mobile { display: none; }
.view-item-page .card.gallery-desktop { display: block; }
.view-item-page .gallery .slide img {
width: 100%; border-radius: 10px; object-fit: cover;
max-height: 280px;
}



/* ── Seller Profile Card (right col) ── */
.seller-profile-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.seller-profile-card__left { display: flex; align-items: center; gap: 14px; }
.seller-profile-card__avatar {
    width: 52px; height: 52px; border-radius: 50%; object-fit: cover;
    border: 2px solid rgba(99,102,241,.5);
    box-shadow: 0 0 0 4px rgba(99,102,241,.1);
    flex-shrink: 0;
}
.seller-profile-card__avatar-placeholder {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, rgba(99,102,241,.3), rgba(139,92,246,.2));
    border: 2px solid rgba(99,102,241,.4);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 20px; color: rgba(255,255,255,.6);
}
.seller-profile-card__info  { display: flex; flex-direction: column; gap: 4px; }
.seller-profile-card__label { font-size: 11px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }
.seller-profile-card__name  { font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px; }
.seller-profile-card__name .verified { color: #6366f1; font-size: 13px; }
.seller-profile-card__rank  { display: inline-flex; align-items: center; justify-content: center; margin-left: 8px; font-size: 18px; line-height: 1; filter: drop-shadow(0 0 10px currentColor); transform: translateY(1px); }
.seller-profile-card__stats { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.seller-stat {
    display: flex; flex-direction: column; align-items: center; gap: 2px;
    padding: 8px 14px; background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07); border-radius: 10px; text-align: center;
}
.seller-stat__value { font-size: 16px; font-weight: 800; color: #fff; line-height: 1; }
.seller-stat__label { font-size: 10px; color: rgba(255,255,255,.4); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
.seller-stat--green .seller-stat__value { color: #4caf7d; }

/* ── Checkout Card ── */
.item-checkout-card .tagline {
color: rgba(255,255,255,.75); font-size: 13px; line-height: 1.7;
margin-bottom: 14px; font-style: italic;
}
.item-checkout-card .checkout-features {
list-style: none; padding: 0; margin: 0 0 14px;
display: flex; flex-direction: column; gap: 7px;
}
.item-checkout-card .checkout-features li {
color: rgba(255,255,255,.72); font-size: 13px;
display: flex; align-items: flex-start; gap: 10px;
}
.item-checkout-card .checkout-features i { color: #8ea5ff; margin-top: 2px; }

/* ── Qty Stepper ── */
.item-checkout-card .qty-wrap {
border: 1px solid rgba(255,255,255,.10); border-radius: 14px;
overflow: hidden; background: rgba(255,255,255,.04); margin-bottom: 16px;
}
.item-checkout-card .qty-controls {
display: grid; grid-template-columns: 56px 1fr 56px; align-items: center;
}
.item-checkout-card .qty-btn {
height: 64px; border: 0; background: transparent; color: #fff;
font-size: 26px; font-weight: 300; cursor: pointer;
transition: background .15s; line-height: 1;
}
.item-checkout-card .qty-btn:hover { background: rgba(99,102,241,.15); }
.item-checkout-card .qty-input {
height: 64px; border: 0; background: transparent; color: #fff;
font-size: 30px; font-weight: 700; text-align: center; width: 100%;
outline: none;
}
.item-checkout-card .qty-input:focus { outline: none; box-shadow: none; }
.item-checkout-card .qty-input::-webkit-inner-spin-button,
.item-checkout-card .qty-input::-webkit-outer-spin-button { -webkit-appearance: none; }
.item-checkout-card .qty-meta {
display: flex; justify-content: space-between;
padding: 8px 16px; background: rgba(255,255,255,.03);
color: rgba(255,255,255,.45); font-size: 11px; font-weight: 500;
border-top: 1px solid rgba(255,255,255,.06);
}

/* ── Total row ── */
.item-checkout-card .total-row {
display: flex; align-items: center; justify-content: space-between;
margin-bottom: 14px; gap: 10px;
}
.item-checkout-card .total-label {
font-size: 15px; font-weight: 700; color: #fff;
}
.item-checkout-card .total-right {
display: flex; align-items: center; gap: 10px;
}
.item-checkout-card .cashback-badge {
display: inline-flex; align-items: center; gap: 5px;
background: rgba(245,166,35,.15); border: 1px solid rgba(245,166,35,.35);
border-radius: 999px; padding: 4px 10px;
font-size: 12px; font-weight: 700; color: #f5a623;
white-space: nowrap;
}
.item-checkout-card .price-main {
font-size: 30px; font-weight: 900; color: #fff; line-height: 1;
display: flex; align-items: baseline; gap: 5px;
}
.item-checkout-card .price-main small {
font-size: 14px; color: rgba(255,255,255,.5); font-weight: 600;
}

/* ── Buy Button ── */
.item-checkout-card .btn {
width: 100%; justify-content: center; min-height: 54px;
border-radius: 14px; font-weight: 800; background: linear-gradient(135deg,#7c83ff,#5b57ff 55%,#4f46e5);
border: 1px solid rgba(124,146,255,.40); color: #fff; padding: 0 20px; font-size: 18px;
display: flex; align-items: center; gap: 8px;
box-shadow: 0 14px 34px rgba(91,87,255,.38);
}
.item-checkout-card .btn:hover { filter: brightness(1.06); }

/* Checkout card = hero element: elevated + accent + dynamic (same as accounts view_generic) */
.right .card#hide-sticky {
    position: relative; overflow: hidden;
    background: linear-gradient(180deg,rgba(26,30,60,.55),rgba(11,14,30,.82));
    border: 1px solid rgba(124,146,255,.24);
    box-shadow: 0 24px 60px rgba(5,6,20,.55);
    border-radius: 22px;
}
.right .card#hide-sticky::before {
    content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg,#6366f1,#8b5cf6,#6366f1);
    background-size: 200% 100%; animation: lbvAccentSlide 6s linear infinite;
}
@keyframes lbvAccentSlide { 0% { background-position: 0 0; } 100% { background-position: 200% 0; } }
.item-checkout-card .btn-row { display: flex; flex-direction: column; align-items: stretch; gap: 0; }
.item-checkout-card .trust-badges {
display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; justify-content: center;
}
.item-checkout-card .trust-badge {
display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px;
border-radius: 999px; background: rgba(255,255,255,.035);
border: 1px solid rgba(255,255,255,.07); color: #c4c9df;
font-size: 12px; font-weight: 700;
}
.item-checkout-card .trust-badge i { color: #8ea5ff; }

/* ── FAQ ── */
.item-faq {
    margin-top: 18px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.item-faq__title {
font-size: 15px; font-weight: 700; color: rgba(255,255,255,.5);
text-transform: uppercase; letter-spacing: .08em;
display: flex; align-items: center; gap: 8px;
margin-bottom: 12px;
}
.item-faq__title i { color: #6366f1; }
.item-faq__item {
border: 1px solid rgba(255,255,255,.07);
border-radius: 12px; overflow: hidden; margin-bottom: 8px;
background: rgba(255,255,255,.02);
transition: border-color .2s;
}
.item-faq__item.open { border-color: rgba(99,102,241,.35); }
.item-faq__q {
width: 100%; display: flex; align-items: center; justify-content: space-between;
gap: 12px; padding: 16px 18px;
background: none; border: none; color: #fff;
font-size: 15px; font-weight: 600; text-align: left; cursor: pointer;
line-height: 1.4;
}
.item-faq__q i {
flex-shrink: 0; font-size: 12px; color: rgba(255,255,255,.4);
transition: transform .25s;
}
.item-faq__item.open .item-faq__q i { transform: rotate(180deg); }
.item-faq__a {
max-height: 0; overflow: hidden;
font-size: 14px; color: rgba(255,255,255,.65); line-height: 1.7;
padding: 0 18px; transition: max-height .3s ease, padding .3s ease;
}
.item-faq__item.open .item-faq__a { max-height: 300px; padding: 0 18px 16px; }

/* ── Testimonials ── */
.account-testimonials { margin-top: 48px; padding-top: 40px; border-top: 1px solid rgba(255,255,255,.06); }
.account-testimonials .section-head {
display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;
}
.account-testimonials .section-title {
font-size: 16px; font-weight: 700; color: #fff;
display: flex; align-items: center; gap: 10px; margin-bottom: 0;
}
.account-testimonials .section-title i { color: #f5a623; }
.testimonials-controls { display: flex; align-items: center; gap: 6px; }
.testimonials-controls .trev-btn {
width: 30px; height: 30px; border-radius: 50%;
border: 1px solid rgba(255,255,255,.15); background: rgba(255,255,255,.05);
color: #fff; cursor: pointer; display: flex; align-items: center;
justify-content: center; font-size: 11px; transition: background .2s, border-color .2s;
}
.testimonials-controls .trev-btn:hover { background: rgba(99,102,241,.25); border-color: rgba(99,102,241,.6); }
.trev-viewall {
display: inline-flex; align-items: center; gap: 7px; padding: 6px 12px;
background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(139,92,246,.1));
border: 1px solid rgba(99,102,241,.35); border-radius: 100px; color: #a5b4fc;
font-size: 11px; font-weight: 600; text-decoration: none; letter-spacing: .04em; white-space: nowrap;
}
.testimonials-slider-wrap { overflow: hidden; position: relative; width: 100%; }
.testimonials-slider {
display: flex; flex-wrap: nowrap; gap: 14px;
will-change: transform; user-select: none;
}
.testimonials-slider .testimonials-slider .testimonial-card { flex-shrink: 0; min-width: 0; box-sizing: border-box; }
.testimonial-card {
background: linear-gradient(145deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
border: 1px solid rgba(255,255,255,.08);
border-radius: 20px; padding: 20px; display: flex; flex-direction: column;
gap: 12px; position: relative; overflow: hidden;
transition: border-color .25s, transform .25s, box-shadow .25s;
box-shadow: 0 4px 24px rgba(0,0,0,.2);
transform: translateZ(0);
-webkit-mask-image: -webkit-radial-gradient(white, black);
}
.testimonial-card::before {
content: "\201C"; position: absolute; top: -10px; right: 16px;
font-size: 80px; line-height: 1; color: rgba(99,102,241,.1);
font-family: Georgia, serif; pointer-events: none;
}
.testimonial-card:hover {
border-color: rgba(99,102,241,.35); transform: translateY(-3px);
box-shadow: 0 8px 32px rgba(99,102,241,.15);
border-top-color: rgba(99,102,241,.6);
}
.testimonial-card__stars { display: flex; gap: 4px; color: #f5a623; font-size: 13px; }
.testimonial-card__text { font-size: 14px; color: rgba(255,255,255,.8); line-height: 1.7; flex: 1; font-style: italic; }
.testimonial-card__author {
display: flex; align-items: center; gap: 12px;
padding-top: 14px; border-top: 1px solid rgba(255,255,255,.07); margin-top: auto;
}
.testimonial-card__author-avatar {
width: 38px; height: 38px; border-radius: 50%; object-fit: cover;
flex-shrink: 0; border: 2px solid rgba(99,102,241,.4);
box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.testimonial-card__author-name { font-size: 13px; font-weight: 700; color: #fff; }
.testimonial-card__author-rank { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; }
.testimonials-dots { display: flex; justify-content: center; gap: 6px; margin-top: 16px; }
.testimonials-dots .dot {
width: 6px; height: 6px; border-radius: 50%;
background: rgba(255,255,255,.15); cursor: pointer; transition: background .2s, transform .2s;
}
.testimonials-dots .dot.active { background: #6366f1; transform: scale(1.3); }

/* ── Seller Accounts Fullwidth Slider ── */
.seller-accounts-fullwidth {
background: rgba(255,255,255,.02);
border-top: 1px solid rgba(255,255,255,.06);
border-bottom: 1px solid rgba(255,255,255,.06);
padding: 2.5vw 0;
margin-top: 0;
}
.seller-accounts-fullwidth__inner { max-width: 85%; margin: 0 auto; }
.seller-accounts-fullwidth__head {
display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25vw;
}
.seller-accounts-fullwidth__title {
font-size: 1.15vw; font-weight: 700; color: #fff;
display: flex; align-items: center; gap: 0.625vw;
}
.seller-accounts-fullwidth__title i { color: #6366f1; }
.seller-accounts-fullwidth__title a { color: #6366f1; text-decoration: none; }
.seller-accounts-fullwidth__controls { display: flex; align-items: center; gap: 0.521vw; }
.saf-prev, .saf-next {
width: 2.083vw; height: 2.083vw; border-radius: 50%;
border: 1px solid rgba(255,255,255,.15); background: rgba(255,255,255,.06);
color: #fff; cursor: pointer; display: flex; align-items: center;
justify-content: center; font-size: 0.729vw; transition: background .2s, border-color .2s;
}
.saf-prev:hover, .saf-next:hover { background: rgba(99,102,241,.25); border-color: rgba(99,102,241,.6); }
.saf-viewall {
display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px;
background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(139,92,246,.1));
border: 1px solid rgba(99,102,241,.35); border-radius: 100px; color: #a5b4fc;
font-size: 12px; font-weight: 600; text-decoration: none; letter-spacing: .04em;
white-space: nowrap; margin-left: 6px; opacity: 1;
transition: background .2s, border-color .2s, color .2s, transform .15s;
}
.saf-viewall:hover {
background: linear-gradient(135deg, rgba(99,102,241,.28), rgba(139,92,246,.2));
border-color: rgba(99,102,241,.65); color: #c7d2fe; transform: translateY(-1px);
}
.saf-slider:not(.slick-initialized) {
display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 14px;
}
.saf-slider .saf-slide { height: 100%; }
.saf-slider .slick-slide { padding: 0 0.521vw; height: auto; }
.saf-slider .slick-list  { margin: 0 -0.521vw; cursor: grab; }
.saf-slider .slick-list:active { cursor: grabbing; }
.saf-slider .slick-slide,
.saf-slider .slick-slide:focus { outline: none; }
.saf-slider.slick-initialized .slick-slide a { pointer-events: auto; }
.saf-slider.dragging .slick-slide a,
.saf-slider.dragging .slick-slide .cover-link { pointer-events: none; }
.seller-accounts-fullwidth–single .saf-slider { display: flex; justify-content: flex-start; }
.seller-accounts-fullwidth–single .saf-slide  { width: 100%; max-width: 24rem; }
.seller-accounts-fullwidth–single .slick-list,
.seller-accounts-fullwidth–single .slick-track { width: auto !important; transform: none !important; }

/* ── SAF Slide: Account Card ── */
.saf-slide .account-card {
border-radius: 1.042vw; border: 0.156vw solid rgba(114,110,142,.1);
overflow: visible; background-color: rgba(255,255,255,.06);
padding: 1.25vw; position: relative; display: flex; flex-direction: column;
transition: border-color .2s ease, transform .2s ease;
}
.saf-slide .account-card:hover { border-color: rgba(99,102,241,.6) !important; }
.saf-slide .account-card .cover-link {
flex: 1; display: flex; flex-direction: column; gap: 0.7vw; text-decoration: none; color: inherit;
}
.saf-slide .account-card .top-row {
display: flex; align-items: flex-start; gap: 0.9vw;
}
.saf-slide .account-card .info-col {
flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 0.55vw;
}
.saf-slide .account-card .title {
font-size: 0.833vw; font-weight: 700; display: flex; align-items: center;
gap: 0.417vw; color: #fff; margin-bottom: 0;
}
.saf-slide .account-card .title .rank-icon,
.saf-slide .account-card .title img {
height: 1.563vw !important; width: auto !important;
display: inline-block !important; visibility: visible !important; opacity: 1 !important;
}
.saf-slide .account-card .title .rank-icon {
background-color: transparent !important; padding: 0 !important;
border-radius: 0 !important; width: auto !important; height: auto !important;
}
.saf-slide .account-card .excerpt {
font-size: 0.677vw; color: rgba(255,255,255,.5); margin-bottom: 0.3vw;
display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.saf-slide .account-card .image-box { position: relative; width: 6.2vw; height: 6.2vw; flex: 0 0 6.2vw; margin: 0; border-radius: 0.83vw; overflow: hidden; background: #0c1020; display: flex; align-items: center; justify-content: center; }
.saf-slide .account-card .image-box > img { width: 100%; height: 100%; object-fit: contain; display: block; }
.saf-slide .account-card .image-box .badge {
position: absolute; right: 0.521vw; bottom: 0.521vw; border-radius: 0.26vw;
padding: 0.2vw 0.417vw; gap: 0.3vw; display: flex; align-items: center;
background: rgba(0,0,0,.65); color: #fff; font-size: 0.6vw;
}
.saf-slide .account-card .highlights {
gap: 0.55vw; display: flex; flex-direction: row !important;
align-items: center; flex-wrap: wrap !important;
overflow: hidden; margin-bottom: 0;
}
.saf-slide .account-card .highlights .badge {
font-size: 0.677vw; background-color: rgba(99,102,241,.3); color: #fff;
gap: 0.3vw; display: inline-flex; align-items: center; border-radius: 0.26vw; padding: 0.3vw 0.55vw;
}
.saf-slide .account-card .totals {
display: flex !important; align-items: center !important; justify-content: space-between !important;
flex-wrap: nowrap !important; gap: 6px !important; margin-top: auto !important;
}
.saf-slide .account-card .totals .price-eur {
font-size: 1.25vw; font-weight: 800; color: #fff; flex-shrink: 0; white-space: nowrap;
}
.saf-slide .account-card .totals .price-eur small { font-size: 0.677vw; color: rgba(255,255,255,.65); font-weight: 700; }
.saf-slide .account-card .totals .btn {
padding: 0.417vw 0.833vw; font-size: 0.729vw; flex-shrink: 0; width: auto !important; white-space: nowrap;
}
.saf-slide .account-card .delivery-type { font-size: 0.938vw; position: absolute; top: 1.042vw; right: 1.042vw; }
.saf-slide .seller-info {
display: flex; align-items: center; justify-content: space-between; gap: 0.417vw;
padding: 0.417vw 0.625vw; margin-top: 0.625vw;
background: rgba(255,255,255,.04); border: 0.078vw solid rgba(255,255,255,.07); border-radius: 0.417vw;
}
.saf-slide .seller-info__left  { display: flex; align-items: center; gap: 0.417vw; }
.saf-slide .seller-info__avatar {
width: 1.302vw; height: 1.302vw; border-radius: 50%; object-fit: cover;
border: 0.078vw solid rgba(99,102,241,.5);
}
.saf-slide .seller-info__name  { font-size: 0.677vw; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 0.26vw; }
.saf-slide .seller-info__verified { font-size: 0.78vw; line-height: 1; }
.saf-slide .seller-info__right { display: flex; align-items: center; gap: 0.417vw; }
.saf-slide .seller-info__sold  {
font-size: 0.573vw; font-weight: 600; color: rgba(255,255,255,.5);
padding: 0.13vw 0.35vw; background: rgba(255,255,255,.05); border-radius: 0.26vw;
}

/* ── Desktop: testimonials split ── */
@media (min-width: 768px) {
.account-testimonials--left  { display: block !important; }
.right .seller-profile-card  { display: flex !important; }
.mobile-below-checkout       { display: none !important; }
.item-desc-more              { display: none !important; }
.item-description            { max-height: none !important; }
.item-highlights .badge      { width: auto !important; }
}

/* ────────────────────────────────────────────────────────────
MOBILE  ≤ 767px
──────────────────────────────────────────────────────────── */
@media (max-width: 767px) {

/* Stack columns */
.view-item-page .layout { flex-direction: column !important; }
.view-item-page .layout .left,
.view-item-page .layout .right {
    width: 100% !important; max-width: 100% !important;
    min-width: 0 !important; flex-shrink: unset !important; overflow: visible !important;
}

/* Slim guarantee ribbon: 2-col grid on mobile (same as accounts view_generic) */
body.view-account-page .lbv-ribbon{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:8px;
    margin:14px 0 20px;
    padding:12px 0;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
}
body.view-account-page .lbv-ribbon__item{
    min-width:0;
    min-height:42px;
    padding:9px 10px;
    gap:8px;
    border:1px solid rgba(126,145,255,.14);
    border-radius:11px;
    background:linear-gradient(135deg,rgba(116,105,255,.10),rgba(255,255,255,.025));
    color:#e4e7f4;
    font-size:11px !important;
    font-weight:800;
    line-height:1.25;
    white-space:normal;
}
body.view-account-page .lbv-ribbon__item i{
    width:25px;
    height:25px;
    flex:0 0 25px;
    display:grid;
    place-items:center;
    border-radius:8px;
    background:rgba(111,126,255,.14);
    color:#9cadff;
    font-size:12px !important;
}
body.view-account-page .lbv-ribbon__item:last-of-type{
    grid-column:1 / -1;
    justify-content:center;
}
body.view-account-page .lbv-ribbon__dot{display:none;}

/* Mobile sticky buy bar + chat button side-by-side: same mechanism as accounts view pages. */
.view-account-page .sticky-button {
    position: fixed !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 999990 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 9px !important;
    padding: 9px 10px calc(9px + env(safe-area-inset-bottom)) !important;
    background: #0e111a !important;
    border-top: 1px solid rgba(116,105,255,.48) !important;
    box-shadow: 0 -10px 28px rgba(0,0,0,.45) !important;
    transform: translateY(110%) !important;
    opacity: 0 !important;
    pointer-events: none !important;
    transition: transform .22s ease, opacity .16s ease !important;
}
.view-account-page.view-sticky-buy-visible .sticky-button,
body.view-sticky-buy-visible .sticky-button {
    transform: translateY(0) !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}
.view-account-page .sticky-button form {
    flex: 1 1 auto !important;
    width: auto !important;
    min-width: 0 !important;
    margin: 0 !important;
}
.view-account-page .sticky-button .btn {
    width: 100% !important;
    min-height: 48px !important;
    border-radius: 7px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    padding: 0 12px !important;
    background: linear-gradient(135deg,#6366f1,#4f46e5) !important;
    border: 1px solid rgba(124,146,255,.40) !important;
    color: #fff !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    letter-spacing: -.01em !important;
    box-shadow: 0 -2px 24px rgba(79,70,229,.28) !important;
    white-space: nowrap !important;
}
.view-account-page .sticky-button .btn i {
    font-size: 15px !important;
    margin-right: 2px !important;
}

/* Seller chat button becomes the right button inside the sticky bottom area on mobile. */
.view-account-page .lbc-floating,
body.view-account-page .lbc-floating {
    display: none !important;
    position: fixed !important;
    right: 10px !important;
    bottom: calc(9px + env(safe-area-inset-bottom)) !important;
    z-index: 999991 !important;
    width: 48px !important;
    height: 48px !important;
    border-radius: 7px !important;
    background: #242936 !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: #d7dbe7 !important;
    box-shadow: none !important;
    font-size: 17px !important;
    transform: none !important;
}
.view-account-page.view-sticky-buy-visible .lbc-floating,
body.view-sticky-buy-visible .lbc-floating {
    display: grid !important;
    place-items: center !important;
}
.view-account-page.view-sticky-buy-visible .sticky-button form,
body.view-sticky-buy-visible .sticky-button form {
    padding-right: 57px !important;
}

/* Highlights: 2 per row on mobile – override main.css */
.item-highlights,
.view-item-page .item-highlights,
.view-account-page .item-highlights {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 6px !important;
    overflow-x: visible !important;
    padding-bottom: 0 !important;
    margin: 0 0 18px !important;
}
.item-highlights .badge,
.view-item-page .item-highlights .badge,
.view-account-page .item-highlights .badge {
    font-size: 11px !important; padding: 5px 9px !important; gap: 4px !important;
    white-space: nowrap !important; flex-shrink: 0 !important;
    width: calc(50% - 3px) !important; box-sizing: border-box !important;
    display: inline-flex !important; flex-direction: row !important;
}



/* Item feature grid: single column */
.item-feature-grid { grid-template-columns: 1fr; }
.item-feature__value { font-size: 15px; }

/* Checkout card: full width */
.view-item-page .card#hide-sticky {
    width: 100% !important; box-sizing: border-box !important;
}

/* Qty stepper: scaled down for mobile */
.item-checkout-card .qty-controls { grid-template-columns: 48px 1fr 48px; }
.item-checkout-card .qty-btn   { height: 56px; font-size: 22px; }
.item-checkout-card .qty-input { height: 56px; font-size: 26px; }
.item-checkout-card .price-main { font-size: 24px; }
.item-checkout-card .price-main small { font-size: 13px; }
.item-checkout-card .btn { min-height: 48px; font-size: 15px; }
.item-checkout-card .trust-badges {
display: flex; gap: 8px; flex-wrap: nowrap; margin-top: 10px; justify-content: center;
}
.item-checkout-card .trust-badge { font-size: 11px; padding: 6px 12px; flex: 1; justify-content: center; }

/* Testimonials */
.account-testimonials { overflow: hidden; }

/* On mobile: hide desktop left-testimonials and right seller card, show mobile block */
.account-testimonials--left { display: none !important; }
.right .seller-profile-card { display: none !important; }
.mobile-below-checkout { display: block; padding: 0 0 20px; }

/* Mobile spacing fixes */
.mobile-below-checkout .seller-profile-card { margin-top: 20px; margin-bottom: 20px; }
.mobile-below-checkout .account-testimonials { margin-top: 0; padding-top: 24px; }
.item-faq { margin-top: 20px; }
.item-faq__item { margin-bottom: 10px; }
.item-faq__q { padding: 14px 16px; font-size: 14px; }
.item-faq__a { font-size: 13px; }

/* Show View More button on mobile when text is clamped */
.item-desc-more { display: flex; }



/* SAF Slider: mobile */
.seller-accounts-fullwidth { padding: 20px 0; }
.seller-accounts-fullwidth__inner { max-width: 92%; }
.seller-accounts-fullwidth__title { font-size: 14px; gap: 7px; }
.saf-prev, .saf-next { width: 30px; height: 30px; font-size: 11px; }
.saf-viewall { font-size: 11px; }
.saf-slider .slick-slide { padding: 0 6px; }
.saf-slider .slick-list  { margin: 0 -6px; }
.saf-slider:not(.slick-initialized) { grid-template-columns: 1fr; }
.seller-accounts-fullwidth--single .saf-slide { max-width: 100%; }

/* SAF Account Card: mobile style (matching view_7) */
.saf-slide .account-card {
    display: flex !important; flex-direction: column !important;
    border-radius: 14px !important;
    border: 1px solid rgba(99,102,241,.18) !important;
    background: linear-gradient(145deg, rgba(255,255,255,.06), rgba(99,102,241,.04)) !important;
    padding: 14px !important;
    overflow: visible !important; position: relative !important;
    box-shadow: 0 2px 20px rgba(0,0,0,.3) !important;
}
.saf-slide .account-card .cover-link { display: flex !important; flex-direction: column !important; gap: 12px !important; }
.saf-slide .account-card .top-row { display: flex !important; align-items: flex-start !important; gap: 12px !important; }
.saf-slide .account-card .info-col { flex: 1 !important; min-width: 0 !important; display: flex !important; flex-direction: column !important; gap: 8px !important; }
.saf-slide .account-card .title {
    font-size: 12px !important; font-weight: 700 !important;
    display: flex !important; align-items: center !important; gap: 6px !important;
    margin-bottom: 0 !important; white-space: normal !important;
}
.saf-slide .account-card .title img,
.saf-slide .account-card .title .rank-icon img { height: 20px !important; width: auto !important; }
.saf-slide .account-card .excerpt {
    margin: 6px 0 4px !important; font-size: 11px !important;
}
.saf-slide .account-card .image-box {
    position: relative !important; margin: 0 !important;
    width: 82px !important; height: 82px !important; flex: 0 0 82px !important;
    border-radius: 12px !important; overflow: hidden !important;
    background: #0c1020 !important; display: flex !important; align-items: center !important; justify-content: center !important;
}
.saf-slide .account-card .image-box > img {
    width: 100% !important; height: 100% !important; object-fit: contain !important;
}
.saf-slide .account-card .highlights {
    gap: 6px !important;
    display: flex !important; flex-direction: row !important;
    flex-wrap: wrap !important;
    overflow: hidden !important;
    scrollbar-width: none !important; -ms-overflow-style: none !important;
    margin-bottom: 0 !important; margin-top: 0 !important;
}
.saf-slide .account-card .highlights::-webkit-scrollbar { display: none !important; }
.saf-slide .account-card .highlights .badge {
    font-size: 10px !important; padding: 4px 8px !important;
    border-radius: 6px !important; white-space: nowrap !important; flex-shrink: 0 !important;
    background-color: rgba(99,102,241,.25) !important;
    border: 1px solid rgba(99,102,241,.3) !important;
}
.saf-slide .account-card .totals {
    display: flex !important; align-items: center !important;
    justify-content: space-between !important; flex-direction: row !important;
    flex-wrap: nowrap !important; margin-top: auto !important;
    padding-top: 10px !important;
    border-top: 1px solid rgba(255,255,255,.06) !important;
}
.saf-slide .account-card .totals .price-eur {
    font-size: 18px !important; font-weight: 900 !important;
    white-space: nowrap !important; flex-shrink: 0 !important;
}
.saf-slide .account-card .totals .btn {
    padding: 9px 16px !important; font-size: 13px !important;
    width: auto !important; flex-shrink: 0 !important;
    border-radius: 12px !important; font-weight: 800 !important;
    background: linear-gradient(135deg,#6366f1,#4f46e5) !important;
    border: 1px solid rgba(124,146,255,.40) !important;
    color: #fff !important;
    box-shadow: 0 8px 20px rgba(79,70,229,.26) !important;
}
.saf-slide .account-card .totals .btn:hover { filter: brightness(1.06) !important; }
.saf-slide .seller-info {
    padding: 8px 10px !important; border-radius: 8px !important; margin-top: 10px !important;
    background: rgba(255,255,255,.03) !important;
    border: 1px solid rgba(255,255,255,.06) !important;
}
.saf-slide .seller-info__avatar { width: 22px !important; height: 22px !important; }
.saf-slide .seller-info__name   { font-size: 11px !important; }
.saf-slide .seller-info__sold   { font-size: 10px !important; }
.seller-accounts-fullwidth__head { flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.seller-accounts-fullwidth__controls { gap: 6px; }

}

@media (max-width: 1199px) {
.saf-slider:not(.slick-initialized) { grid-template-columns: repeat(3, minmax(0,1fr)); }
}
@media (max-width: 900px) {
.saf-slider:not(.slick-initialized) { grid-template-columns: repeat(2, minmax(0,1fr)); }
}

/* Seller chat inline button */
.seller-profile-card__chat {
    display:inline-flex; align-items:center; justify-content:center; gap:9px;
    width:100%; min-height:54px; padding:14px 20px; border-radius:999px;
    background: rgba(109,92,255,.15);
    border:1.5px solid rgba(109,92,255,.5); color:#fff;
    font-weight:800; font-size:18px; line-height:1; white-space:nowrap;
    cursor:pointer; transition: background .2s, border-color .2s, box-shadow .2s;
    margin-top:10px;
}
.seller-profile-card__chat:hover {
    background: rgba(109,92,255,.28);
    border-color: rgba(109,92,255,.8);
    box-shadow: 0 0 20px rgba(109,92,255,.25);
    color:#fff;
}
.seller-profile-card__chat:disabled { opacity:.45; cursor:not-allowed; }
@media (max-width:767px) {
    .seller-profile-card__chat { font-size:16px; }
}
/* Chat-with-seller (secondary) — same recolor as accounts view_generic */
body.view-account-page .seller-profile-card__chat{
  background:rgba(255,255,255,.045)!important;
  border:1px solid rgba(124,146,255,.22)!important;
  color:#c7d2fe!important; font-weight:800!important;
}
body.view-account-page .seller-profile-card__chat:hover{
  background:rgba(99,102,241,.16)!important;
  border-color:rgba(124,146,255,.40)!important;
  color:#fff!important;
}

/* ---- Flat dark panel system, same as accounts view_lol/view_generic ---- */
body.view-item-page{
  background:
    radial-gradient(1100px 620px at 18% -6%,rgba(79,110,247,.12),transparent 60%),
    radial-gradient(900px 560px at 88% 2%,rgba(99,102,241,.08),transparent 58%),
    #070815 !important;
}
body.view-item-page .card{
  background:#0d1021!important;
  border:1px solid rgba(255,255,255,.07)!important;
  box-shadow:0 14px 40px rgba(0,0,0,.24)!important;
  color:#f7f8ff!important;
}
body.view-item-page .card .card-header{
  background:transparent!important;
  border-bottom:1px solid rgba(255,255,255,.06)!important;
}
body.view-item-page .card .card-header h4{
  color:#fff!important; font-weight:900!important;
}
body.view-item-page .card .card-header h4 i{color:#8ea5ff!important;}
body.view-item-page .item-description{color:#aab0c9!important;}
body.view-item-page .item-feature{
  background:rgba(255,255,255,.03)!important;
  border:1px solid rgba(255,255,255,.06)!important;
  border-radius:14px!important;
}
body.view-item-page .item-feature__value i{color:#8ea5ff!important;}

</style>






<?= $this->stop() ?>

<div class="container">
    <h3 class="title">
        <div class="rank-icon">
            <?php if ($typeImg): ?>
                <img src="<?= htmlspecialchars($typeImg) ?>" alt="<?= htmlspecialchars($typeLabel) ?>">
            <?php else: ?>
                <i class="<?= htmlspecialchars($typeFa) ?>"></i>
            <?php endif; ?>
        </div>
        <span class="account-view-title-text"><?= item_display_text($item['title'] ?? null, 'Item') ?></span>
    </h3>

    <div class="lbv-ribbon">
        <span class="lbv-ribbon__item"><i class="fas fa-bolt"></i><?= t('Fast Delivery') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-user-check"></i><?= t('Verified Sellers') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-shield-halved"></i><?= t('Buyer Protection') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-lock"></i><?= t('Secure Checkout') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-headset"></i><?= t('24/7 Support') ?></span>
    </div>

<div class="layout">
    <div class="left">



        <!-- Item Details -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-info-circle me-2"></i><?= t('Item Details') ?></h4>
            </div>
            <div class="card-body">
                <?php if (!empty($item['description'])): ?>
                <div class="item-description-wrap">
                    <div class="item-description" id="itemDesc"><?= nl2br(htmlspecialchars($item['description'])) ?></div>
                    <button type="button" class="item-desc-more" id="itemDescMore">
                        <i class="fas fa-chevron-down"></i> <?= t('View More') ?>
                    </button>
                </div>
                <?php endif; ?>
                <div class="item-feature-grid">
                    <?php if (!empty($item['requires_friendship_days'])): ?>
                    <div class="item-feature">
                        <div class="item-feature__label"><?= t('Delivery Time') ?></div>
                        <div class="item-feature__value">
                            <i class="fa-solid fa-clock"></i>
                            <?= (int)$item['requires_friendship_days'] ?> <?= t('days') ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($item['server'])): ?>
                    <div class="item-feature">
                        <div class="item-feature__label"><?= t('Server') ?></div>
                        <div class="item-feature__value">
                            <i class="fa-solid fa-earth-europe"></i>
                            <?= htmlspecialchars(function_exists('util_format_server_code') ? util_format_server_code($item['server']) : strtoupper((string)$item['server'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="item-feature">
                        <div class="item-feature__label"><?= t('Type') ?></div>
                        <div class="item-feature__value">
                            <?php if ($typeImg): ?>
                                <img src="<?= htmlspecialchars($typeImg) ?>" alt="<?= htmlspecialchars($typeLabel) ?>">
                            <?php else: ?>
                                <i class="<?= htmlspecialchars($typeFa) ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($typeLabel) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials LEFT (desktop) -->
        <div class="account-testimonials account-testimonials--left">
            <div class="section-head">
                <div class="section-title"><i class="fas fa-star"></i><?= t('What our customers say') ?></div>
                <div class="testimonials-controls">
                    <button type="button" class="trev-btn trev-prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                    <button type="button" class="trev-btn trev-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                    <a href="/reviews" class="trev-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="testimonials-slider-wrap">
                <div class="testimonials-slider">
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Division boost with role/champ prefs. Booster picked up in 20 minutes and finished under 24h. Clean execution and constant dashboard updates.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">J****</div><div class="testimonial-card__author-rank">Gold II ➠ Platinum III · EUW</div></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Bought a verified ranked account. Instant delivery, hand-leveled, clean history. Support helped with email link + 2FA in minutes.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">S*****</div><div class="testimonial-card__author-rank">Ranked Account · NA</div></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Duo with a Challenger mid. Great shot-calls around drakes and herald. We finished a day earlier than planned. Would book again.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/ca88cc14-4318-4b08-b5c8-b54d026a6692.jpeg" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">M******</div><div class="testimonial-card__author-rank">Silver II ➠ Platinum IV · Duo Queue</div></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Transparent guarantee. After a loss they added extra games automatically. Placed one division higher than expected.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">R******</div><div class="testimonial-card__author-rank">Placements · 4/5 Wins Guaranteed</div></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Live session fixed early-game pathing and timers. Immediate impact on my win rate. Worth it if you want long-term improvement.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">K*****</div><div class="testimonial-card__author-rank">Co-Pilot Coaching · Jungle Pathing</div></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Needed a clean smurf for duo. ARAM leveled, normal MMR intact, quick delivery. Perfect for fresh placements.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">T*****</div><div class="testimonial-card__author-rank">Hand-Leveled Smurf · EUW</div></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Clear net-win logic and great execution. Extra games were played until the target was met. Fast replies in order chat.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">A****</div><div class="testimonial-card__author-rank">Net Win Boost · 6 Net Wins · EUNE</div></div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="testimonial-card__text"><?= t('Booster respected champ requests and played with VPN. Multiple wins per day, zero issues with safety or comms.') ?></div>
                        <div class="testimonial-card__author">
                            <img src="<?= ICON_URL ?>/ca88cc14-4318-4b08-b5c8-b54d026a6692.jpeg" alt="" class="testimonial-card__author-avatar">
                            <div class="testimonial-card__author-info"><div class="testimonial-card__author-name">L******</div><div class="testimonial-card__author-rank">Arena · 10 Wins · Top-4 Rule</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonials-dots"></div>
        </div>

    </div><!-- /.left -->

    <div class="right">

        <?php if (!empty($seller)): ?>
        <?php
            echo lb_render_seller_footer(array_merge((array)$seller, [
                'total_sold' => $sellerTotalSoldDisplay,
                'seller_total_sales' => $sellerTotalSoldDisplay,
                'is_online' => seller_detail_is_online((array)$seller) ? 1 : 0,
            ]), ['variant' => 'profile']);
        ?>
        <?php endif; ?>

        <!-- Checkout Card -->
        <div class="card item-checkout-card" id="hide-sticky">
            <div class="card-body">

                <div class="qty-wrap">
                    <div class="qty-controls">
                        <button type="button" class="qty-btn" id="qtyMinus">−</button>
                        <input type="number" id="qtyInput" class="qty-input"
                               min="<?= $minQty ?>" max="<?= max($stock, $minQty) ?>"
                               value="<?= $initialQty ?>">
                        <button type="button" class="qty-btn" id="qtyPlus">+</button>
                    </div>
                    <div class="qty-meta">
                        <span><?= t('Min Qty') ?>: <?= $minQty ?></span>
                        <span><?= t('In Stock') ?>: <?= $stock ?></span>
                    </div>
                </div>

                <div class="total-row">
                    <div class="total-label"><?= t('Total') ?></div>
                    <div class="total-right">
                        <div class="price-main">
                            <span id="itemTotalPrice"><?= htmlspecialchars($currencySymbol . $initialTotalDisplayFormatted) ?></span>
                            <small id="itemCurrencyCode"><?= htmlspecialchars($currencyCode) ?></small>
                        </div>
                    </div>
                </div>

                <form action="<?= AJAX_URL ?>" class="ajax-form">
                    <input type="hidden" name="action"   value="prepare_lol_item_order">
                    <input type="hidden" name="item_id"  value="<?= (int)($item['id'] ?? 0) ?>">
                    <input type="hidden" name="quantity" id="itemQtyHidden" value="<?= $initialQty ?>">
                    <div class="btn-row">
                        <button type="submit" class="btn" <?= $stock <= 0 ? 'disabled' : '' ?>>
                            <span class="indicator-label">
                                <i class="fas fa-shopping-cart me-2"></i>
                                <?= $stock > 0 ? t('Buy Item Now') : t('Out of Stock') ?>
                            </span>
                            <span class="indicator-progress"><span class="loader"></span></span>
                        </button>
                        <?php if (!empty($seller) && ($sellerChatAllowedInline ?? true)): ?>
                        <button type="button"
                                class="seller-profile-card__chat"
                                style="width:100%;margin-top:10px;"
                                title="<?= t('Message Seller') ?>"
                                data-seller-chat-open>
                            <i class="fa-solid fa-comment-dots"></i>
                            <span><?= t('Chat with seller') ?></span>
                        </button>
                        <?php elseif (!empty($seller)): ?>
                        <button type="button"
                                class="seller-profile-card__chat"
                                style="width:100%;margin-top:10px;opacity:.45;cursor:not-allowed;"
                                title="<?= t('Chat not available') ?>"
                                disabled>
                            <i class="fa-solid fa-comment-dots"></i>
                            <span><?= t('Chat with seller') ?></span>
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="trust-badges">
                        <div class="trust-badge"><i class="fa-solid fa-shield-halved"></i><?= t('Secure Checkout') ?></div>
                        <div class="trust-badge"><i class="fa-solid fa-gift"></i><?= t('Safe Gifting') ?></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- FAQ -->
        <div class="item-faq">
            <div class="item-faq__title"><i class="fas fa-question-circle"></i><?= t('FAQ') ?></div>
            <?php
            $faqs = [
                [t('How do I choose the right LoL item?'), t('Browse our selection including orbs, skins, keys, capsules and more. Each item shows the type, server and delivery time — pick what fits your champion or playstyle.')],
                [t('How fast is the delivery?'), t('Most items are delivered within minutes to a few hours after purchase. Items that require gifting may take up to the displayed delivery time due to Riot\'s friendship policy.')],
                [t('What server do I need?'), t('Make sure your account is on the same server as the item. The server is clearly displayed on each item — e.g. EUW, TR, NA.')],
                [t('Is it safe to buy here?'), t('Yes. All items are sold by verified sellers. We use secure checkout and your account details are never required for item purchases.')],
                [t('What if I have an issue with my order?'), t('Contact our support via live chat. We offer full assistance and warranty on every purchase.')],
            ];
            foreach ($faqs as $i => $faq): ?>
            <div class="item-faq__item" id="faq-item-<?= $i ?>">
                <button type="button" class="item-faq__q" onclick="(function(b){var p=b.parentElement,o=p.classList.contains('open');document.querySelectorAll('.item-faq__item.open').forEach(function(x){x.classList.remove('open')});if(!o)p.classList.add('open');})(this)">
                    <span><?= $faq[0] ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="item-faq__a"><?= $faq[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

    </div><!-- /.right -->
</div>

<!-- Mobile only: seller + testimonials below checkout -->
<div class="mobile-below-checkout">
    <?php if (!empty($seller)): ?>
    <?php
        echo lb_render_seller_footer(array_merge((array)$seller, [
            'total_sold' => $sellerTotalSoldDisplay,
            'seller_total_sales' => $sellerTotalSoldDisplay,
            'is_online' => seller_detail_is_online((array)$seller) ? 1 : 0,
        ]), ['variant' => 'mobile-profile']);
    ?>
    <?php endif; ?>

    <div class="account-testimonials">
        <div class="section-head">
            <div class="section-title"><i class="fas fa-star"></i><?= t('What our customers say') ?></div>
            <div class="testimonials-controls">
                <button type="button" class="trev-btn trev-prev-m" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="trev-btn trev-next-m" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                <a href="/reviews" class="trev-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        <div class="testimonials-slider-wrap">
            <div class="testimonials-slider testimonials-slider-m">
                <div class="testimonial-card"><div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><div class="testimonial-card__text"><?= t('Division boost with role/champ prefs. Booster picked up in 20 minutes and finished under 24h. Clean execution and constant dashboard updates.') ?></div><div class="testimonial-card__author"><img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar"><div class="testimonial-card__author-info"><div class="testimonial-card__author-name">J****</div><div class="testimonial-card__author-rank">Gold II ➠ Platinum III · EUW</div></div></div></div>
                <div class="testimonial-card"><div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><div class="testimonial-card__text"><?= t('Bought a verified ranked account. Instant delivery, hand-leveled, clean history. Support helped with email link + 2FA in minutes.') ?></div><div class="testimonial-card__author"><img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar"><div class="testimonial-card__author-info"><div class="testimonial-card__author-name">S*****</div><div class="testimonial-card__author-rank">Ranked Account · NA</div></div></div></div>
                <div class="testimonial-card"><div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><div class="testimonial-card__text"><?= t('Duo with a Challenger mid. Great shot-calls around drakes and herald. We finished a day earlier than planned. Would book again.') ?></div><div class="testimonial-card__author"><img src="<?= ICON_URL ?>/ca88cc14-4318-4b08-b5c8-b54d026a6692.jpeg" alt="" class="testimonial-card__author-avatar"><div class="testimonial-card__author-info"><div class="testimonial-card__author-name">M******</div><div class="testimonial-card__author-rank">Silver II ➠ Platinum IV · Duo Queue</div></div></div></div>
                <div class="testimonial-card"><div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><div class="testimonial-card__text"><?= t('Super smooth transaction. Got my item within 5 minutes of payment. The seller was very responsive and helpful throughout.') ?></div><div class="testimonial-card__author"><img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar"><div class="testimonial-card__author-info"><div class="testimonial-card__author-name">M*****</div><div class="testimonial-card__author-rank">Skin Bundle · EUW</div></div></div></div>
                <div class="testimonial-card"><div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><div class="testimonial-card__text"><?= t('Second time buying here. Always fast, always clean. The warranty feature is great – had a small issue and support fixed it same day.') ?></div><div class="testimonial-card__author"><img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar"><div class="testimonial-card__author-info"><div class="testimonial-card__author-name">R*****</div><div class="testimonial-card__author-rank">Chest & Key Bundle · EUNE</div></div></div></div>
            </div>
        </div>
        <div class="testimonials-dots testimonials-dots-m"></div>
    </div>
</div>

</div>

<?php if (!empty($seller_items)):
    $sellerItemsFiltered = array_values(array_filter($seller_items, function($more) use ($item) {
        return (int)($more['id'] ?? 0) !== (int)($item['id'] ?? 0);
    }));
    $sellerItemsCount = count($sellerItemsFiltered);
?>

<?php if ($sellerItemsCount > 0): ?>

<div class="seller-accounts-fullwidth <?= $sellerItemsCount === 1 ? 'seller-accounts-fullwidth--single' : '' ?>">
    <div class="seller-accounts-fullwidth__inner">
        <div class="seller-accounts-fullwidth__head">
            <div class="seller-accounts-fullwidth__title">
                <i class="fas fa-layer-group"></i>
                <?= t('More from') ?> <a href="/sellers/<?= htmlspecialchars($seller['username'] ?? '') ?>"><?= htmlspecialchars($seller['username'] ?? '') ?></a>
            </div>
            <?php if ($sellerItemsCount > 1): ?>
            <div class="seller-accounts-fullwidth__controls">
                <button type="button" class="saf-prev"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="saf-next"><i class="fas fa-chevron-right"></i></button>
                <a href="/lol/items" class="saf-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php endif; ?>
        </div>

    <div class="saf-slider">
        <?php foreach ($sellerItemsFiltered as $more):
            $moreImages   = json_decode((string)($more['images'] ?? '[]'), true);
            if (!is_array($moreImages)) $moreImages = [];
            $moreCover    = $moreImages[0] ?? (ASSET_URL . '/public/uploads/icons/default2.png');
            $moreSlugOrId = !empty($more['slug']) ? $more['slug'] : (string)(int)($more['id'] ?? 0);
            $moreTypeLabel = items_shop_type_label((string)($more['type'] ?? ''));
            $moreTypeImg   = items_shop_type_img((string)($more['type'] ?? ''));
            $moreTypeFa    = items_shop_type_fa((string)($more['type'] ?? ''));
            $morePriceParts   = item_format_price_for_currency((int)($more['price'] ?? 0), $_SESSION['currency'] ?? 'EUR');
            $morePriceDisplay = (function_exists('util_format_currency_display') ? util_format_currency_display($_SESSION['currency'] ?? 'EUR') : $currencySymbol) . $morePriceParts['formatted'];
            $moreSellerRankMeta = seller_rank_icon_meta($seller['rank'] ?? '', $seller['rank_icon'] ?? '');
        ?>
        <div class="saf-slide">
            <div class="account-card">
                <a class="cover-link" href="<?= BASE_URL ?>/lol/item/<?= urlencode($moreSlugOrId) ?>">
                    <div class="top-row">
                        <div class="info-col">
                            <div class="title">
                                <span class="rank-icon">
                                    <?php if ($moreTypeImg): ?>
                                        <img src="<?= htmlspecialchars($moreTypeImg) ?>" alt="">
                                    <?php else: ?>
                                        <i class="<?= htmlspecialchars($moreTypeFa) ?>"></i>
                                    <?php endif; ?>
                                </span>
                                <?= item_display_text($more['title'] ?? null, 'Item') ?>
                            </div>
                            <div class="highlights">
                                <span class="badge">
                                    <?php if ($moreTypeImg): ?><img src="<?= htmlspecialchars($moreTypeImg) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($moreTypeFa) ?>"></i><?php endif; ?>
                                    <?= htmlspecialchars($moreTypeLabel) ?>
                                </span>
                                <span class="badge"><i class="fa-solid fa-box"></i><?= (int)($more['stock'] ?? 0) ?> <?= t('Stock') ?></span>
                                <?php if (!empty($more['requires_friendship_days'])): ?>
                                    <span class="badge"><i class="fa-solid fa-clock"></i><?= (int)$more['requires_friendship_days'] ?> <?= t('days') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="image-box">
                            <img src="<?= htmlspecialchars($moreCover) ?>" alt="<?= item_display_text($more['title'] ?? null, 'Item') ?>">
                        </div>
                    </div>
                    <div class="totals">
                        <div class="price-eur"><?= htmlspecialchars($morePriceDisplay) ?></div>
                        <span class="btn"><i class="fas fa-arrow-right"></i><?= t('Buy Now') ?></span>
                    </div>
                </a>
                <?php
                    echo lb_render_seller_footer(array_merge((array)$seller, [
                        'total_sold' => $sellerTotalSoldDisplay,
                        'seller_total_sales' => $sellerTotalSoldDisplay,
                        'is_online' => seller_detail_is_online((array)$seller) ? 1 : 0,
                    ]), ['variant' => 'account-card']);
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</div>
<?php endif; ?>
<?php endif; ?>

<div class="sticky-button">
    <form action="<?= AJAX_URL ?>" class="ajax-form">
        <input type="hidden" name="action"   value="prepare_lol_item_order">
        <input type="hidden" name="item_id"  value="<?= (int)($item['id'] ?? 0) ?>">
        <input type="hidden" name="quantity" id="itemQtyHiddenSticky" value="<?= $initialQty ?>">
        <button type="submit" class="btn">
            <i class="fas fa-shopping-cart me-2"></i>
            <?= t('Buy Item Now') ?> - <span id="stickyTotalPrice"><?= htmlspecialchars($initialTotalPriceWithCurrency) ?></span>
        </button>
    </form>
</div>



<?php
// Seller direct chat modal variables
$seller_id        = (int)($seller['id'] ?? 0);
$seller_name_raw  = (string)($seller['username'] ?? 'Seller');
$seller_name      = htmlspecialchars($seller_name_raw, ENT_QUOTES, 'UTF-8');
$seller_icon_raw  = (string)($seller['icon'] ?? '');
$seller_icon      = htmlspecialchars($seller_icon_raw, ENT_QUOTES, 'UTF-8');
$ref_type         = 'item_purchase';
$ref_id           = (int)($item['id'] ?? 0);
$chat_allowed     = (bool)(!empty($seller['allow_chat_requests']) || !array_key_exists('allow_chat_requests', (array)$seller));
$seller_initials  = strtoupper(substr(trim($seller_name_raw) ?: 'S', 0, 2));
$ajax_url_inline  = defined('AJAX_URL') ? AJAX_URL : (defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/ajax' : '/ajax');
$client_logged_in_inline = (defined('CLIENT_ID') && (int)CLIENT_ID > 0);
$base_url_inline  = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

// Item info for the modal header
$item_title  = item_display_text($item['title'] ?? null, '');
$item_type   = htmlspecialchars(items_shop_type_label((string)($item['type'] ?? '')), ENT_QUOTES, 'UTF-8');
$item_server_raw = (string)($item['server'] ?? 'EUW');
$item_server = htmlspecialchars(function_exists('util_format_server_code') ? util_format_server_code($item_server_raw) : strtoupper($item_server_raw), ENT_QUOTES, 'UTF-8');
$item_price  = $initialUnitPriceWithCurrency ?? '';
$item_img    = $typeImg ?? '';
$item_fa     = $typeFa ?? 'fa-solid fa-tag';
?>
<?php if ($seller_id): ?>
<style>
/* ════════════════════════════════════
   SELLER CHAT MODAL – komplett neu
   ════════════════════════════════════ */

/* Overlay */
.lbc-overlay {
    position: fixed; inset: 0; z-index: 999999;
    display: none; align-items: center; justify-content: center;
    padding: 16px;
    background: rgba(4, 6, 18, 0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.lbc-overlay.is-open { display: flex; }

/* Panel */
.lbc-panel {
    width: min(620px, 100%);
    max-height: calc(100vh - 32px);
    display: flex; flex-direction: column;
    border-radius: 20px;
    background: #0d1226;
    border: 1px solid rgba(109, 92, 255, 0.3);
    box-shadow:
        0 0 0 1px rgba(109, 92, 255, 0.12),
        0 32px 80px rgba(0, 0, 0, 0.7),
        0 8px 24px rgba(109, 92, 255, 0.12);
    overflow: hidden;
    color: #fff;
}

/* ── Header ── */
.lbc-head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 18px 20px;
    background: linear-gradient(135deg, rgba(109,92,255,.4) 0%, rgba(99,60,220,.25) 100%);
    border-bottom: 1px solid rgba(109, 92, 255, 0.25);
    flex-shrink: 0;
}
.lbc-head-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
.lbc-avatar {
    width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
    object-fit: cover; display: grid; place-items: center;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    font-weight: 900; font-size: 15px; color: #fff;
    border: 1.5px solid rgba(255,255,255,.18);
    box-shadow: 0 4px 14px rgba(109,92,255,.45);
}
.lbc-head-info { min-width: 0; }
.lbc-head-name {
    font-size: 15px; font-weight: 800; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    display: block;
}
.lbc-head-sub {
    font-size: 11px; color: rgba(255,255,255,.55); margin-top: 2px;
    display: flex; align-items: center; gap: 5px;
}
.lbc-head-sub i { font-size: 10px; color: #6d5cff; }
.lbc-close {
    width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
    border: 0; background: rgba(255,255,255,.1); color: #fff;
    font-size: 18px; font-weight: 700; cursor: pointer;
    display: grid; place-items: center;
    transition: background .18s;
}
.lbc-close:hover { background: rgba(255,255,255,.2); }

/* ── Item preview strip ── */
.lbc-item-strip {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 20px;
    background: rgba(109, 92, 255, 0.07);
    border-bottom: 1px solid rgba(109, 92, 255, 0.15);
    flex-shrink: 0;
}
.lbc-item-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    background: rgba(109,92,255,.15);
    border: 1px solid rgba(109,92,255,.25);
    display: grid; place-items: center;
    font-size: 15px; color: #a5b4fc;
}
.lbc-item-icon img { width: 20px; height: 20px; object-fit: contain; }
.lbc-item-meta { min-width: 0; flex: 1; }
.lbc-item-title {
    font-size: 13px; font-weight: 700; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    display: block;
}
.lbc-item-tags { display: flex; gap: 6px; margin-top: 3px; flex-wrap: wrap; }
.lbc-item-tag {
    font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 999px;
    background: rgba(109,92,255,.18); border: 1px solid rgba(109,92,255,.3);
    color: #c4b5fd;
}
.lbc-item-price {
    font-size: 15px; font-weight: 900; color: #fff;
    white-space: nowrap; flex-shrink: 0;
}

/* ── Guidelines ── */
/* ── Guidelines – always visible, no toggle ── */
.lbc-guidelines {
    padding: 14px 20px 16px;
    border-bottom: 1px solid rgba(109, 92, 255, 0.18);
    background: rgba(109, 92, 255, 0.06);
    flex-shrink: 0;
}
.lbc-guide-header {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 800; letter-spacing: .06em;
    text-transform: uppercase; color: rgba(255,255,255,.55);
    margin-bottom: 10px;
}
.lbc-guide-header i { color: #6d5cff; font-size: 13px; }

.lbc-guide-text {
    font-size: 12.5px; color: rgba(255,255,255,.6); line-height: 1.6;
    margin-bottom: 12px;
}

/* Good / Avoid columns */
.lbc-guide-cols {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
.lbc-guide-col {
    border-radius: 10px; padding: 11px 13px;
}
.lbc-guide-col--good {
    background: rgba(34,197,94,.09);
    border: 1px solid rgba(34,197,94,.22);
}
.lbc-guide-col--bad {
    background: rgba(239,68,68,.08);
    border: 1px solid rgba(239,68,68,.2);
}
.lbc-guide-col-head {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 800; margin-bottom: 9px;
    text-transform: uppercase; letter-spacing: .05em;
}
.lbc-guide-col--good .lbc-guide-col-head { color: #4ade80; }
.lbc-guide-col--bad .lbc-guide-col-head { color: #f87171; }
.lbc-guide-col-head i { font-size: 12px; }
.lbc-guide-items { display: flex; flex-direction: column; gap: 6px; }
.lbc-guide-item {
    font-size: 12.5px; color: rgba(255,255,255,.75); line-height: 1.45;
    padding: 2px 0;
}
.lbc-guide-col--bad .lbc-guide-item {
    text-decoration: line-through;
    color: rgba(248,113,113,.65);
}

/* ── Messages ── */
.lbc-body {
    flex: 1; min-height: 200px; max-height: 320px;
    overflow-y: auto; padding: 16px 20px;
    background: #080c1c;
    display: flex; flex-direction: column; gap: 10px;
    scrollbar-width: thin; scrollbar-color: rgba(109,92,255,.3) transparent;
}
.lbc-body::-webkit-scrollbar { width: 4px; }
.lbc-body::-webkit-scrollbar-thumb { background: rgba(109,92,255,.35); border-radius: 4px; }

.lbc-empty {
    margin: auto; text-align: center; padding: 16px 0;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.lbc-empty-icon {
    width: 52px; height: 52px; border-radius: 16px;
    background: rgba(109,92,255,.12); border: 1px solid rgba(109,92,255,.2);
    display: grid; place-items: center;
    font-size: 22px; color: #6d5cff;
}
.lbc-empty-title { font-size: 14px; font-weight: 700; color: rgba(255,255,255,.7); }
.lbc-empty-sub { font-size: 12px; color: rgba(255,255,255,.3); }

/* Messages */
.lbc-msg { display: flex; gap: 8px; align-items: flex-end; }
.lbc-msg.me { flex-direction: row-reverse; }
.lbc-msg-av {
    width: 28px; height: 28px; border-radius: 9px; flex-shrink: 0;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    color: #fff; font-size: 9px; font-weight: 900;
    display: grid; place-items: center; overflow: hidden;
}
.lbc-msg-av img { width: 100%; height: 100%; object-fit: cover; }
.lbc-msg-bubble {
    max-width: 75%; padding: 9px 13px;
    border-radius: 16px 16px 16px 4px;
    background: rgba(109,92,255,.2);
    border: 1px solid rgba(109,92,255,.25);
    font-size: 13px; line-height: 1.5; color: #fff; word-break: break-word;
}
.lbc-msg.me .lbc-msg-bubble {
    border-radius: 16px 16px 4px 16px;
    background: linear-gradient(135deg, rgba(109,92,255,.55), rgba(124,58,237,.45));
    border-color: rgba(109,92,255,.45);
}
.lbc-msg-time { font-size: 10px; color: rgba(255,255,255,.28); margin-top: 3px; }
.lbc-msg.me .lbc-msg-time { text-align: right; }
.lbc-msg-bubble img { max-width: 190px; border-radius: 10px; cursor: pointer; display: block; margin-top: 4px; }

/* ── Footer / Composer ── */
.lbc-footer {
    padding: 12px 16px 14px;
    background: #0a0e1e;
    border-top: 1px solid rgba(109, 92, 255, 0.15);
    flex-shrink: 0;
}
.lbc-preview {
    display: none; align-items: center; gap: 8px;
    margin-bottom: 10px; padding: 7px 10px; border-radius: 10px;
    background: rgba(109,92,255,.1); border: 1px solid rgba(109,92,255,.2);
    color: rgba(255,255,255,.7);
}
.lbc-preview.is-open { display: flex; }
.lbc-preview img { width: 38px; height: 30px; object-fit: cover; border-radius: 7px; }
.lbc-preview small { flex: 1; font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lbc-preview-rm {
    border: 0; background: rgba(255,255,255,.1); color: #fff;
    border-radius: 7px; width: 22px; height: 22px; cursor: pointer;
    display: grid; place-items: center; font-size: 14px;
}
.lbc-compose { display: flex; gap: 8px; align-items: center; }
.lbc-compose-input {
    flex: 1; min-width: 0;
    border: 1px solid rgba(109, 92, 255, 0.25);
    background: rgba(255, 255, 255, 0.05);
    color: #fff; border-radius: 999px;
    padding: 11px 16px; font-size: 13px; outline: none;
    transition: border-color .2s, background .2s;
}
.lbc-compose-input::placeholder { color: rgba(255,255,255,.3); }
.lbc-compose-input:focus {
    border-color: rgba(109, 92, 255, 0.65);
    background: rgba(109, 92, 255, 0.08);
}
.lbc-img-btn, .lbc-send-btn {
    border: 0; display: grid; place-items: center; cursor: pointer; flex-shrink: 0;
    border-radius: 12px; transition: background .18s, filter .18s;
}
.lbc-img-btn {
    width: 40px; height: 40px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    color: rgba(255,255,255,.45); font-size: 15px;
}
.lbc-img-btn:hover { background: rgba(109,92,255,.2); color: #a5b4fc; }
.lbc-send-btn {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    color: #fff; font-size: 15px;
    box-shadow: 0 4px 14px rgba(109,92,255,.45);
}
.lbc-send-btn:hover { filter: brightness(1.1); }
.lbc-send-btn:disabled { opacity: .38; cursor: not-allowed; box-shadow: none; }

/* ── Floating bubble ── */
.lbc-floating {
    position: fixed; right: 22px; bottom: 22px; z-index: 999998;
    width: 52px; height: 52px; border: 0; border-radius: 16px;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    color: #fff; font-size: 20px;
    box-shadow: 0 12px 32px rgba(109,92,255,.5);
    cursor: pointer; display: grid; place-items: center;
    transition: transform .18s, box-shadow .18s;
}
.lbc-floating:hover { transform: translateY(-2px); box-shadow: 0 16px 40px rgba(109,92,255,.6); }
.lbc-dot {
    position: absolute; right: -3px; top: -3px;
    width: 12px; height: 12px; border-radius: 50%;
    background: #ef4444; border: 2px solid #0d1226; display: none;
}
body.lbc-lock { overflow: hidden; }

@media (max-width: 660px) {
    .lbc-overlay { padding: 0; align-items: flex-end; }
    .lbc-panel { width: 100%; border-radius: 20px 20px 0 0; max-height: 93vh; }
    .lbc-body { max-height: 45vh; }
    .lbc-guide-cols { grid-template-columns: 1fr; }
    .lbc-floating { right: 14px; bottom: 14px; }
}
</style>

<!-- Floating trigger -->
<button type="button" class="lbc-floating" id="lbcTrigger" data-seller-chat-open <?= !$chat_allowed ? 'disabled' : '' ?>>
    <i class="fa-solid fa-comment-dots"></i>
    <span class="lbc-dot" id="lbcUnreadDot"></span>
</button>

<!-- Modal overlay -->
<div id="sellerChatModal" class="lbc-overlay" aria-hidden="true">
  <div class="lbc-panel" role="dialog" aria-modal="true">

    <!-- Header -->
    <div class="lbc-head">
      <div class="lbc-head-left">
        <?php if ($seller_icon): ?>
          <img class="lbc-avatar" src="<?= $seller_icon ?>" alt="<?= $seller_name ?>">
        <?php else: ?>
          <div class="lbc-avatar"><?= htmlspecialchars($seller_initials, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <div class="lbc-head-info">
          <span class="lbc-head-name"><?= $seller_name ?></span>
          <span class="lbc-head-sub">
            <i class="fa-solid fa-shield-check"></i>
            <?= $chat_allowed ? t('Ask a question before buying') : t('Not accepting messages') ?>
          </span>
        </div>
      </div>
      <button type="button" class="lbc-close" data-seller-chat-close aria-label="Close">×</button>
    </div>

    <!-- Item preview strip -->
    <?php if (!empty($item_title)): ?>
    <div class="lbc-item-strip">
      <div class="lbc-item-icon">
        <?php if ($item_img): ?><img src="<?= htmlspecialchars($item_img, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($item_fa, ENT_QUOTES, 'UTF-8') ?>"></i><?php endif; ?>
      </div>
      <div class="lbc-item-meta">
        <span class="lbc-item-title"><?= $item_title ?></span>
        <div class="lbc-item-tags">
          <?php if ($item_type): ?><span class="lbc-item-tag"><?= $item_type ?></span><?php endif; ?>
          <?php if ($item_server): ?><span class="lbc-item-tag"><?= $item_server ?></span><?php endif; ?>
        </div>
      </div>
      <?php if ($item_price): ?><div class="lbc-item-price"><?= htmlspecialchars($item_price, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!$chat_allowed): ?>
      <div class="lbc-body">
        <div class="lbc-empty">
          <div class="lbc-empty-icon"><i class="fa-solid fa-comment-slash"></i></div>
          <div class="lbc-empty-title"><?= t('Not accepting messages') ?></div>
          <div class="lbc-empty-sub"><?= t('This seller is currently not accepting new chat requests.') ?></div>
        </div>
      </div>
    <?php else: ?>

      <!-- Messaging Guidelines – always visible -->
      <div class="lbc-guidelines" id="lbcGuidelines">
        <div class="lbc-guide-header">
          <i class="fa-solid fa-shield-check"></i>
          <?= t('Messaging Guidelines') ?>
        </div>
        <p class="lbc-guide-text"><?= t('Keep all communication and payment inside the platform. Do not share external contacts or login details before purchase.') ?></p>
        <div class="lbc-guide-cols">
          <div class="lbc-guide-col lbc-guide-col--good">
            <div class="lbc-guide-col-head"><i class="fa-solid fa-circle-check"></i><?= t('Good Examples') ?></div>
            <div class="lbc-guide-items">
              <div class="lbc-guide-item"><?= t('Can you tell me if this account has any restrictions?') ?></div>
              <div class="lbc-guide-item"><?= t('Does this include full access and original email?') ?></div>
              <div class="lbc-guide-item"><?= t('Can you show me in-game screenshots?') ?></div>
              <div class="lbc-guide-item"><?= t('Would you be open to negotiating the price?') ?></div>
            </div>
          </div>
          <div class="lbc-guide-col lbc-guide-col--bad">
            <div class="lbc-guide-col-head"><i class="fa-solid fa-circle-xmark"></i><?= t('Avoid These') ?></div>
            <div class="lbc-guide-items">
              <div class="lbc-guide-item"><?= t("Let's talk over Telegram instead.") ?></div>
              <div class="lbc-guide-item"><?= t('Can I pay after you deliver the item?') ?></div>
              <div class="lbc-guide-item"><?= t('Send me login details first, I\'ll pay after.') ?></div>
              <div class="lbc-guide-item"><?= t('I work for the marketplace, I need your login.') ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Message body -->
      <div class="lbc-body" id="lbcMessages">
        <div class="lbc-empty" id="lbcEmpty">
          <div class="lbc-empty-icon"><i class="fa-solid fa-comments"></i></div>
          <div class="lbc-empty-title"><?= t('Ask') ?> <?= $seller_name ?> <?= t('about this listing') ?></div>
          <div class="lbc-empty-sub"><?= t('Messages stay protected inside the platform.') ?></div>
        </div>
      </div>

      <!-- Footer / Compose -->
      <div class="lbc-footer">
        <form id="lbcForm" autocomplete="off">
          <input type="hidden" name="action"   value="client_seller_chat_send">
          <input type="hidden" name="seller_id" value="<?= $seller_id ?>">
          <input type="hidden" name="ref_type"  value="<?= htmlspecialchars($ref_type, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="ref_id"    value="<?= $ref_id ?>">
          <input type="file"   name="chat_image" id="lbcFileInput" accept="image/*" hidden>
          <div class="lbc-preview" id="lbcPreview">
            <img id="lbcPreviewThumb" src="" alt="">
            <small id="lbcPreviewName"></small>
            <button type="button" class="lbc-preview-rm" id="lbcPreviewRemove">×</button>
          </div>
          <div class="lbc-compose">
            <button type="button" class="lbc-img-btn" id="lbcImgBtn" title="<?= t('Attach image') ?>">
              <i class="fa-solid fa-image"></i>
            </button>
            <input type="text" name="message" id="lbcMsgInput"
                   class="lbc-compose-input"
                   placeholder="<?= t('Ask') ?> <?= $seller_name ?>..."
                   autocomplete="off">
            <button type="submit" class="lbc-send-btn" id="lbcSendBtn" disabled>
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </div>
        </form>
      </div>

    <?php endif; ?>
  </div>
</div>

<!-- Auth overlay (dark-themed) -->
<style>
.lbc-auth-overlay{position:fixed;inset:0;z-index:1000000;background:rgba(4,6,18,.85);backdrop-filter:blur(16px);display:none;align-items:center;justify-content:center;padding:20px}
.lbc-auth-overlay.is-open{display:flex}
.lbc-auth-card{width:min(460px,calc(100vw - 24px));background:#0d1226;border:1px solid rgba(109,92,255,.3);border-radius:20px;box-shadow:0 32px 80px rgba(0,0,0,.7);color:#fff;padding:32px;position:relative}
.lbc-auth-close{position:absolute;right:18px;top:18px;width:34px;height:34px;border:0;border-radius:10px;background:rgba(255,255,255,.1);color:#fff;font-size:20px;cursor:pointer;display:grid;place-items:center}
.lbc-auth-title{font-size:20px;font-weight:900;margin-bottom:22px;color:#fff}
.lbc-auth-tabs{display:grid;grid-template-columns:1fr 1fr;border:1px solid rgba(109,92,255,.3);border-radius:999px;padding:3px;background:rgba(109,92,255,.08);margin-bottom:20px}
.lbc-auth-tab{height:36px;border:0;background:transparent;color:rgba(255,255,255,.55);border-radius:999px;font-weight:700;font-size:13px;cursor:pointer;transition:.18s}
.lbc-auth-tab.is-active{background:linear-gradient(135deg,#6d5cff,#7c3aed);color:#fff;box-shadow:0 4px 14px rgba(109,92,255,.4)}
.lbc-auth-form{display:none}.lbc-auth-form.is-active{display:block}
.lbc-auth-label{display:block;font-size:13px;font-weight:700;margin:14px 0 6px;color:rgba(255,255,255,.65)}
.lbc-auth-input{width:100%;height:48px;border-radius:12px;border:1px solid rgba(109,92,255,.25);background:rgba(255,255,255,.05);color:#fff;font-size:14px;padding:0 14px;outline:none;transition:.18s;box-sizing:border-box}
.lbc-auth-input:focus{border-color:rgba(109,92,255,.65);background:rgba(109,92,255,.09)}
.lbc-auth-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:12px 0}
.lbc-auth-check{display:flex;align-items:center;gap:7px;font-size:12px;color:rgba(255,255,255,.55)}
.lbc-auth-check input{accent-color:#6d5cff;width:14px;height:14px}
.lbc-auth-submit{width:100%;height:48px;border:0;border-radius:999px;background:linear-gradient(135deg,#6d5cff,#7c3aed);color:#fff;font-weight:800;font-size:15px;cursor:pointer;margin-top:6px;box-shadow:0 4px 18px rgba(109,92,255,.4);transition:.18s}
.lbc-auth-submit:hover{filter:brightness(1.1)}
.lbc-auth-error{display:none;margin:10px 0 0;padding:9px 12px;border-radius:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.22);color:#fca5a5;font-size:12px}
.lbc-auth-error.is-open{display:block}
.lbc-auth-socials{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,.07)}
.lbc-auth-social{height:44px;border:0;border-radius:12px;color:#fff;font-weight:700;font-size:13px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:7px}
.lbc-auth-google{background:#ea4335}.lbc-auth-discord{background:#5865f2}
@media(max-width:480px){.lbc-auth-card{padding:24px 18px}.lbc-auth-socials{grid-template-columns:1fr}}
</style>

<div id="lbcAuthOverlay" class="lbc-auth-overlay" aria-hidden="true">
  <div class="lbc-auth-card" role="dialog" aria-modal="true">
    <button type="button" class="lbc-auth-close" data-lbc-auth-close>&times;</button>
    <div class="lbc-auth-title"><?= t('Sign in to message seller') ?></div>
    <div class="lbc-auth-tabs">
      <button type="button" class="lbc-auth-tab is-active" data-lbc-auth-tab="login"><i class="fa-solid fa-lock-open me-1"></i> Login</button>
      <button type="button" class="lbc-auth-tab" data-lbc-auth-tab="register"><i class="fa-solid fa-user-plus me-1"></i> Register</button>
    </div>
    <form class="lbc-auth-form is-active" id="lbcLoginForm" autocomplete="on">
      <input type="hidden" name="action" value="auth_client_login">
      <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
      <label class="lbc-auth-label">Email</label>
      <input class="lbc-auth-input" type="email" name="email" required>
      <label class="lbc-auth-label">Password</label>
      <input class="lbc-auth-input" type="password" name="password" required>
      <div class="lbc-auth-row">
        <label class="lbc-auth-check"><input type="checkbox" name="remember_me" value="1"> Remember me</label>
        <a href="<?= $base_url_inline ?>/forgot-password" style="color:#a5b4fc;font-size:12px;text-decoration:none">Forgot password?</a>
      </div>
      <button class="lbc-auth-submit" type="submit">Sign in</button>
      <div class="lbc-auth-error" id="lbcLoginError"></div>
    </form>
    <form class="lbc-auth-form" id="lbcRegisterForm" autocomplete="on">
      <input type="hidden" name="action" value="auth_client_register">
      <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
      <label class="lbc-auth-label">Username</label>
      <input class="lbc-auth-input" type="text" name="username" required>
      <label class="lbc-auth-label">Email</label>
      <input class="lbc-auth-input" type="email" name="email" required>
      <label class="lbc-auth-label">Password</label>
      <input class="lbc-auth-input" type="password" name="password" minlength="6" required>
      <div class="lbc-auth-row">
        <label class="lbc-auth-check"><input type="checkbox" name="tos" value="1" required> I agree to the terms</label>
      </div>
      <button class="lbc-auth-submit" type="submit">Create account</button>
      <div class="lbc-auth-error" id="lbcRegisterError"></div>
    </form>
    <div class="lbc-auth-socials">
      <a class="lbc-auth-social lbc-auth-google" href="<?= $base_url_inline ?>/auth/google"><i class="fab fa-google"></i> Google</a>
      <a class="lbc-auth-social lbc-auth-discord" href="<?= $base_url_inline ?>/auth/discord"><i class="fab fa-discord"></i> Discord</a>
    </div>
  </div>
</div>

<script>
(function () {
    'use strict';
    if (window.lbcChatReady) return;
    window.lbcChatReady = true;

    /* ── Config ── */
    const SELLER_ID  = <?= (int)$seller_id ?>;
    const SELLER_ICO = <?= json_encode($seller_icon_raw) ?>;
    const SELLER_INI = <?= json_encode($seller_initials) ?>;
    const AJAX_URL   = <?= json_encode($ajax_url_inline) ?>;
    const CHAT_OK    = <?= $chat_allowed ? 'true' : 'false' ?>;
    const LOGGED_IN  = <?= $client_logged_in_inline ? 'true' : 'false' ?>;
    const BASE_URL   = <?= json_encode($base_url_inline) ?>;
    const AGREED_KEY = 'lbcAgreed_' + SELLER_ID;

    /* ── Elements ── */
    const overlay    = document.getElementById('sellerChatModal');
    const msgBox     = document.getElementById('lbcMessages');
    const form       = document.getElementById('lbcForm');
    const inp        = document.getElementById('lbcMsgInput');
    const sendBtn    = document.getElementById('lbcSendBtn');
    const fileInput  = document.getElementById('lbcFileInput');
    const imgBtn     = document.getElementById('lbcImgBtn');
    const preview    = document.getElementById('lbcPreview');
    const thumb      = document.getElementById('lbcPreviewThumb');
    const prevName   = document.getElementById('lbcPreviewName');
    const prevRm     = document.getElementById('lbcPreviewRemove');
    const dot        = document.getElementById('lbcUnreadDot');
    const guideWrap  = document.getElementById('lbcGuidelines');
    const guideToggle= document.getElementById('lbcGuideToggle');
    const agreeChk   = null; // no checkbox in this version

    /* ── State ── */
    let poll = null, sig = '', conv = null;
    let agreed = true; // no checkbox needed, guidelines are always visible

    /* ── Auth modal ── */
    const authOverlay = document.getElementById('lbcAuthOverlay');
    function authOpen(tab) {
        if (!authOverlay) return;
        authOverlay.classList.add('is-open');
        authOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lbc-lock');
        document.querySelectorAll('[data-lbc-auth-tab]').forEach(b =>
            b.classList.toggle('is-active', b.dataset.lbcAuthTab === (tab || 'login')));
        document.querySelectorAll('.lbc-auth-form').forEach(f =>
            f.classList.toggle('is-active',
                (tab === 'register' && f.id === 'lbcRegisterForm') ||
                (tab !== 'register' && f.id === 'lbcLoginForm')));
    }
    function authClose() {
        if (!authOverlay) return;
        authOverlay.classList.remove('is-open');
        authOverlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lbc-lock');
    }
    window.lbOpenClientAuth = authOpen;
    window.openLoginModal   = () => authOpen('login');

    document.addEventListener('click', e => {
        if (e.target.closest('[data-lbc-auth-tab]')) {
            authOpen(e.target.closest('[data-lbc-auth-tab]').dataset.lbcAuthTab);
        }
        if (e.target.closest('[data-lbc-auth-close]') || e.target === authOverlay) authClose();
    });

    /* Auth form submit */
    function bindAuth(formId, errId) {
        const f = document.getElementById(formId);
        const er = document.getElementById(errId);
        if (!f) return;
        f.addEventListener('submit', async e => {
            e.preventDefault();
            if (er) er.classList.remove('is-open');
            const btn = f.querySelector('[type=submit]');
            if (btn) btn.disabled = true;
            try {
                const res = await fetch(BASE_URL + '/ajax', { method: 'POST', body: new FormData(f), credentials: 'same-origin' });
                const d = await parseJson(res);
                if (d.redirectUrl) { location.href = d.redirectUrl; return; }
                if (d.refreshPage || d.playSound === 'success') { location.reload(); return; }
                if (er) { er.textContent = d.message || d.error || 'Something went wrong.'; er.classList.add('is-open'); }
            } catch { if (er) { er.textContent = 'Request failed.'; er.classList.add('is-open'); } }
            finally { if (btn) btn.disabled = false; }
        });
    }
    bindAuth('lbcLoginForm', 'lbcLoginError');
    bindAuth('lbcRegisterForm', 'lbcRegisterError');

    /* ── Helper: JSON parse ── */
    function parseJson(r) {
        return r.text().then(t => {
            t = (t || '').trim();
            try { return JSON.parse(t); } catch (_) {
                const a = t.indexOf('{'), b = t.lastIndexOf('}');
                if (a !== -1 && b > a) return JSON.parse(t.slice(a, b + 1));
                throw new Error(t.slice(0, 200) || 'Invalid response');
            }
        });
    }

    /* ── Trigger auth if not logged in ── */
    function requireAuth() {
        chatClose();
        if (window.lbOpenClientAuth) { window.lbOpenClientAuth('login'); return; }
        /* fallbacks */
        const ids = ['authModal', 'loginModal', 'clientAuthModal'];
        for (const id of ids) {
            const el = document.getElementById(id);
            if (!el) continue;
            try { if (window.bootstrap?.Modal) { window.bootstrap.Modal.getOrCreateInstance(el).show(); return; } } catch (_) {}
            try { if (window.jQuery?.fn?.modal) { jQuery(el).modal('show'); return; } } catch (_) {}
        }
        document.dispatchEvent(new CustomEvent('lb:open-auth', { detail: { tab: 'login', source: 'seller-chat' } }));
    }

    /* ── Chat open / close ── */
    function chatOpen() {
        if (!LOGGED_IN) { requireAuth(); return; }
        if (!overlay) return;
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lbc-lock');
        if (CHAT_OK) {
            syncSend();
            loadMessages();
            clearInterval(poll);
            poll = setInterval(loadMessages, 4000);
            setTimeout(() => inp && inp.focus(), 100);
        }
    }
    function chatClose() {
        if (!overlay) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lbc-lock');
        clearInterval(poll);
    }

    /* ── Click delegation ── */
    document.addEventListener('click', e => {
        if (e.target.closest('[data-seller-chat-open]')) { e.preventDefault(); chatOpen(); return; }
        if (e.target.closest('[data-seller-chat-close]')) { e.preventDefault(); chatClose(); return; }
        if (e.target === overlay) chatClose();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { chatClose(); authClose(); } });

    /* ── Guidelines always visible, no toggle needed ── */
    function syncSend() {
        if (sendBtn) sendBtn.disabled = false; // always enabled
    }
    syncSend();

    /* ── Escape HTML ── */
    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    /* ── Avatar ── */
    function avatar(isSeller) {
        if (isSeller && SELLER_ICO) return `<div class="lbc-msg-av"><img src="${esc(SELLER_ICO)}" alt=""></div>`;
        return `<div class="lbc-msg-av">${esc(isSeller ? SELLER_INI : 'ME')}</div>`;
    }

    /* ── Render message ── */
    function renderMsg(m) {
        if (!msgBox) return;
        const empty = document.getElementById('lbcEmpty');
        if (empty) empty.remove();
        const isSeller = m.sender_type === 'seller';
        const isImg    = m.message_type === 'image';
        const el = document.createElement('div');
        el.className = 'lbc-msg' + (isSeller ? '' : ' me');
        const body = isImg
            ? `<img src="${esc(m.body)}" onclick="window.open(this.src,'_blank')" alt="">`
            : esc(m.body).replace(/\n/g, '<br>');
        el.innerHTML = `${avatar(isSeller)}<div><div class="lbc-msg-bubble">${body}</div><div class="lbc-msg-time">${esc(m.created_at_fmt || '')}</div></div>`;
        msgBox.appendChild(el);
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    /* ── Load messages ── */
    function loadMessages() {
        if (!CHAT_OK || !msgBox) return;
        const fd = new FormData();
        fd.append('action', 'client_seller_chat_load');
        fd.append('seller_id', SELLER_ID);
        fd.append('sig', sig);
        if (conv) fd.append('conv_id', conv);
        fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(parseJson)
            .then(d => {
                if (d.conv_id) conv = d.conv_id;
                if (d.sig && d.sig !== sig) {
                    sig = d.sig;
                    msgBox.innerHTML = '';
                    (d.messages || []).forEach(renderMsg);
                    if ((d.messages || []).length) {
                        agreed = true;
                        syncSend();
                    }
                }
                if (dot) dot.style.display = (d.unread_client > 0) ? 'block' : 'none';
            })
            .catch(() => {});
    }

    /* ── Image attach ── */
    if (imgBtn && fileInput) imgBtn.onclick = () => fileInput.click();
    if (fileInput) fileInput.onchange = function () {
        const f = this.files[0];
        if (!f) return;
        const r = new FileReader();
        r.onload = ev => { if (thumb) thumb.src = ev.target.result; };
        r.readAsDataURL(f);
        if (prevName) prevName.textContent = f.name;
        if (preview) preview.classList.add('is-open');
    };
    if (prevRm) prevRm.onclick = () => {
        if (fileInput) fileInput.value = '';
        if (preview) preview.classList.remove('is-open');
    };

    /* ── Compose: enable send when has text ── */
    if (inp) inp.addEventListener('input', syncSend);

    /* ── Send message ── */
    if (form) form.onsubmit = async function (e) {
        e.preventDefault();
        if (!LOGGED_IN) { requireAuth(); return; }
        const text = (inp ? inp.value : '').trim();
        const hasFile = fileInput && fileInput.files[0];
        if (!text && !hasFile) return;
        if (sendBtn) sendBtn.disabled = true;
        const fd = new FormData(form);
        fd.set('action', 'client_seller_chat_send');
        fd.set('seller_id', SELLER_ID);
        if (conv) fd.set('conv_id', conv);
        try {
            const d = await fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' }).then(parseJson);
            if (d.success) {
                conv = d.conv_id || conv;
                if (text) renderMsg({ sender_type: 'client', body: text, created_at_fmt: d.created_at, message_type: 'text' });
                if (d.image_url) renderMsg({ sender_type: 'client', body: d.image_url, created_at_fmt: d.created_at, message_type: 'image' });
                if (inp) inp.value = '';
                if (fileInput) fileInput.value = '';
                if (preview) preview.classList.remove('is-open');
                // Redirect to chat page after successful send
                setTimeout(function() { window.location.href = BASE_URL + '/profile/chat'; }, 600);
            } else {
                const em = d.message || d.error || (d.sendToast && d.sendToast.message) || 'Could not send message.';
                if (d.auth_required || /log.?in|unauthorized/i.test(em)) requireAuth();
                else alert(em);
            }
        } catch (err) {
            alert(err?.message || 'Could not send message.');
        } finally {
            syncSend();
        }
    };

    /* Enter to send */
    if (inp) inp.onkeydown = e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form && form.dispatchEvent(new Event('submit', { cancelable: true })); }
    };

})();
</script>
<?php endif; ?>

<?= $this->start('scripts') ?>

<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
(function(){
    const qtyInput     = document.getElementById('qtyInput');
    const minusBtn     = document.getElementById('qtyMinus');
    const plusBtn      = document.getElementById('qtyPlus');
    const hidden       = document.getElementById('itemQtyHidden');
    const hiddenSticky = document.getElementById('itemQtyHiddenSticky');
    const total        = document.getElementById('itemTotalPrice');
    const stickyTotal  = document.getElementById('stickyTotalPrice');
    const currencySymbol = <?= json_encode($currencySymbol) ?>;
    const currencyCode   = <?= json_encode($currencyCode) ?>;
    const unit           = <?= json_encode((float)$unitDisplayNumeric) ?>;
    const min            = <?= json_encode($minQty) ?>;
    const max            = <?= json_encode(max($stock, $minQty)) ?>;

    function formatCurrencyAmount(amount) {
        const n = Number(amount || 0);
        let locale = 'en-US';
        if (currencyCode === 'EUR') locale = 'de-DE';
        if (currencyCode === 'BRL') locale = 'pt-BR';
        if (currencyCode === 'TRY') locale = 'tr-TR';
        return n.toLocaleString(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function clamp(v) {
        v = parseInt(v, 10);
        if (isNaN(v)) v = min;
        if (v < min)  v = min;
        if (v > max)  v = max;
        return v;
    }
    function updatePrice(rawVal) {
        // Show 0 price if empty or below minimum
        const v = parseInt(rawVal, 10);
        const isValid = !isNaN(v) && v >= min;
        const q = isValid ? Math.min(v, max) : 0;
        const formatted = isValid ? currencySymbol + formatCurrencyAmount(unit * q) : currencySymbol + formatCurrencyAmount(0);
        if (total)       total.textContent       = formatted;
        if (stickyTotal) stickyTotal.textContent = formatted;
        // Only update hidden qty if valid
        if (isValid) {
            if (hidden)       hidden.value       = q;
            if (hiddenSticky) hiddenSticky.value = q;
        }
    }
    function render() {
        if (!qtyInput) return;
        const q = clamp(qtyInput.value);
        qtyInput.value = q;
        if (hidden)       hidden.value       = q;
        if (hiddenSticky) hiddenSticky.value = q;
        const formatted = currencySymbol + formatCurrencyAmount(unit * q);
        if (total)       total.textContent       = formatted;
        if (stickyTotal) stickyTotal.textContent = formatted;
    }
    // +/- buttons: clamp immediately
    if (minusBtn) minusBtn.addEventListener('click', function(){
        qtyInput.value = clamp((parseInt(qtyInput.value, 10) || min) - 1);
        render();
    });
    if (plusBtn) plusBtn.addEventListener('click', function(){
        qtyInput.value = clamp((parseInt(qtyInput.value, 10) || min) + 1);
        render();
    });
    // While typing: just update price display, don't snap
    if (qtyInput) qtyInput.addEventListener('input', function(){
        updatePrice(this.value);
    });
    // On blur: snap to valid value
    if (qtyInput) qtyInput.addEventListener('blur', function(){
        render();
    });
    render();
})();

$(document).ready(function () {

    // ── Description View More ──
    var descEl   = document.getElementById('itemDesc');
    var descMore = document.getElementById('itemDescMore');
    if (descEl && descMore) {
        descMore.addEventListener('click', function() {
            var expanded = descEl.classList.toggle('expanded');
            descMore.classList.toggle('open', expanded);
            descMore.innerHTML = expanded
                ? '<i class="fas fa-chevron-up"></i> <?= t('View Less') ?>'
                : '<i class="fas fa-chevron-down"></i> <?= t('View More') ?>';
        });
    }

    // ── Sticky button visibility (same mechanism as accounts view pages) ──
    var $stickySection = $('.sticky-button');
    var $hideSticky    = $('#hide-sticky');
    function checkVisibility() {
        if (!$stickySection.length || !$hideSticky.length) return;

        var isMobile = window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
        var windowHeight = $(window).height();
        var elementTop = $hideSticky.offset().top;
        var elementBottom = elementTop + $hideSticky.outerHeight();
        var scrollTop = $(window).scrollTop();
        var checkoutVisible = (scrollTop + windowHeight > elementTop && scrollTop < elementBottom);
        var shouldShowSticky = isMobile && !checkoutVisible;

        $('body').toggleClass('view-sticky-buy-visible', shouldShowSticky);
        $('.view-account-page').toggleClass('view-sticky-buy-visible', shouldShowSticky);

        if (!isMobile) {
            $stickySection.css({ transform: '', transition: '' });
        }
    }
    $(window).on('scroll', checkVisibility);
    checkVisibility();

    // ── SAF Slider ──
    if ($('.saf-slider').length && $('.saf-slider .saf-slide').length > 1) {
        var safDragging = false;
        $('.saf-slider').on('mousedown touchstart', function(){ safDragging = false; })
                        .on('mousemove touchmove',  function(){ safDragging = true; });
        $(document).on('click', '.saf-slider .cover-link', function(e){
            if (safDragging) { e.preventDefault(); safDragging = false; }
        });
        $('.saf-slider').slick({
            slidesToShow: 4, slidesToScroll: 1,
            arrows: true, infinite: false, dots: false,
            autoplay: false, accessibility: false,
            draggable: true, swipe: true, swipeToSlide: true,
            touchMove: true, touchThreshold: 5,
            speed: 400, cssEase: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
            prevArrow: $('.saf-prev'), nextArrow: $('.saf-next'),
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: 3 } },
                { breakpoint: 900,  settings: { slidesToShow: 2 } },
                { breakpoint: 600,  settings: { slidesToShow: 1 } }
            ]
        });
        // Remove any stray Slick-injected buttons (autoplay toggle = "...")
        $('.saf-slider').find('button.slick-autoplay-toggle-button').remove();
        $('.saf-slider ~ button, .saf-slider + button').remove();
    }

    // ── Testimonial Slider ──
    function initTestimonialSlider(sliderEl, dotsEl, prevBtn, nextBtn) {
        if (!sliderEl) return;
        const cards = sliderEl.querySelectorAll('.testimonial-card');
        if (!cards.length) return;

        var current = 0, currentTranslate = 0, GAP = 14;
        var isDragging = false, dragMoved = false, startX = 0, startTranslate = 0;

        function getWrapWidth() {
            var wrap = sliderEl.parentElement;
            return wrap ? wrap.getBoundingClientRect().width : 300;
        }
        function getVisibleCount() {
            var w = getWrapWidth();
            if (w >= 860) return 3;
            if (w >= 540) return 2;
            return 1;
        }
        function getCardWidth() {
            var vc = getVisibleCount(), w = getWrapWidth();
            if (vc === 1) return w * 0.88;
            return (w - GAP * (vc - 1)) / vc;
        }
        function maxIndex() { return Math.max(0, cards.length - getVisibleCount()); }
        function buildDots() {
            if (!dotsEl) return;
            var vc = getVisibleCount();
            var count = Math.ceil(cards.length / vc);
            dotsEl.innerHTML = '';
            for (var i = 0; i < count; i++) {
                (function(idx) {
                    var d = document.createElement('span');
                    d.className = 'dot' + (idx === 0 ? ' active' : '');
                    d.addEventListener('click', function(){ goTo(idx * vc); });
                    dotsEl.appendChild(d);
                })(i);
            }
        }
        function updateDots() {
            if (!dotsEl) return;
            var vc = getVisibleCount(), dotIdx = Math.round(current / vc);
            dotsEl.querySelectorAll('.dot').forEach(function(d, i){ d.classList.toggle('active', i === dotIdx); });
        }
        function applyTransform(tx, animate) {
            sliderEl.style.transition = animate ? 'transform 0.42s cubic-bezier(0.25,0.46,0.45,0.94)' : 'none';
            sliderEl.style.transform  = 'translateX(' + tx + 'px)';
        }
        function goTo(idx) {
            current = Math.max(0, Math.min(idx, maxIndex()));
            var cw = getCardWidth();
            currentTranslate = -current * (cw + GAP);
            applyTransform(currentTranslate, true);
            updateDots();
        }
        function setup() {
            var cw = getCardWidth();
            cards.forEach(function(c){
                c.style.flex = '0 0 ' + cw + 'px';
                c.style.width = cw + 'px';
                c.style.maxWidth = cw + 'px';
                c.style.minWidth = '0';
                c.style.boxSizing = 'border-box';
            });
            sliderEl.style.gap = GAP + 'px';
            current = Math.min(current, maxIndex());
            currentTranslate = -current * (cw + GAP);
            applyTransform(currentTranslate, false);
            buildDots(); updateDots();
        }

        if (prevBtn) prevBtn.addEventListener('click', function(){ goTo(current - getVisibleCount()); });
        if (nextBtn) nextBtn.addEventListener('click', function(){ goTo(current + getVisibleCount()); });

        sliderEl.style.cursor = 'grab';

        sliderEl.addEventListener('mousedown', function(e){ isDragging = true; dragMoved = false; startX = e.clientX; startTranslate = currentTranslate; sliderEl.style.transition = 'none'; sliderEl.style.cursor = 'grabbing'; });
        window.addEventListener('mousemove', function(e){ if (!isDragging) return; if (Math.abs(e.clientX - startX) > 4) dragMoved = true; applyTransform(startTranslate + (e.clientX - startX), false); });
        window.addEventListener('mouseup', function(e){
            if (!isDragging) return; isDragging = false; sliderEl.style.cursor = 'grab';
            var diff = e.clientX - startX, thresh = (getCardWidth() + GAP) * 0.18;
            if (diff < -thresh) goTo(current + 1); else if (diff > thresh) goTo(current - 1); else goTo(current);
        });
        sliderEl.addEventListener('mouseleave', function(e){ if (isDragging) { isDragging = false; sliderEl.style.cursor = 'grab'; goTo(current); } });
        sliderEl.addEventListener('touchstart', function(e){ if (!e.touches.length) return; isDragging = true; dragMoved = false; startX = e.touches[0].clientX; startTranslate = currentTranslate; sliderEl.style.transition = 'none'; }, { passive: true });
        sliderEl.addEventListener('touchmove',  function(e){ if (!isDragging || !e.touches.length) return; if (Math.abs(e.touches[0].clientX - startX) > 4) dragMoved = true; applyTransform(startTranslate + (e.touches[0].clientX - startX), false); }, { passive: true });
        sliderEl.addEventListener('touchend',   function(e){
            isDragging = false;
            var x = e.changedTouches.length ? e.changedTouches[0].clientX : startX;
            var diff = x - startX, thresh = (getCardWidth() + GAP) * 0.18;
            if (diff < -thresh) goTo(current + 1); else if (diff > thresh) goTo(current - 1); else goTo(current);
        });
        sliderEl.addEventListener('click', function(e){ if (dragMoved) { e.preventDefault(); e.stopPropagation(); dragMoved = false; } }, true);

        var resizeTimer;
        window.addEventListener('resize', function(){ clearTimeout(resizeTimer); resizeTimer = setTimeout(setup, 80); });
        setup();
    }

    setTimeout(function() {
        var sliderL = document.querySelector('.account-testimonials--left  .testimonials-slider');
        var dotsL   = document.querySelector('.account-testimonials--left  .testimonials-dots');
        var prevL   = document.querySelector('.account-testimonials--left  .trev-prev');
        var nextL   = document.querySelector('.account-testimonials--left  .trev-next');
        initTestimonialSlider(sliderL, dotsL, prevL, nextL);

        var sliderM = document.querySelector('.mobile-below-checkout .testimonials-slider-m');
        var dotsM   = document.querySelector('.mobile-below-checkout .testimonials-dots-m');
        var prevM   = document.querySelector('.mobile-below-checkout .trev-prev-m');
        var nextM   = document.querySelector('.mobile-below-checkout .trev-next-m');
        initTestimonialSlider(sliderM, dotsM, prevM, nextM);
    }, 50);

});
</script>

<?= $this->stop() ?>
