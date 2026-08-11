<?= $this->layout('website/layouts/master', ['meta' => $meta ?? [], 'bodyClass' => 'ranked-accounts-page digital-goods-shop-page']) ?>

<?php
$h = static function ($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$category   = is_array($category ?? null) ? $category : [];
$listings   = is_array($listings ?? null) ? $listings : [];
$brands     = is_array($brands ?? null) ? $brands : [];
$regions    = is_array($regions ?? null) ? $regions : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];

$categoryName = (string)($category['name'] ?? ($meta['h1'] ?? 'Digital Goods'));
$categorySlug = (string)($category['slug'] ?? '');
$description  = (string)($category['description'] ?? ($meta['description'] ?? 'Buy digital goods instantly with secure checkout and fast delivery.'));

$page       = max(1, (int)($page ?? ($pagination['page'] ?? ($_GET['page'] ?? 1))));
$totalPages = max(1, (int)($pagination['totalPages'] ?? 1));
$totalItems = (int)($pagination['totalItems'] ?? count($listings));
$perPage    = max(1, (int)($perPage ?? 24));

$brand    = (string)($brand ?? ($_GET['brand'] ?? ''));
$region   = (string)($region ?? ($_GET['region'] ?? ''));
$search   = (string)($search ?? ($_GET['search'] ?? ''));
$sort     = (string)($sort ?? ($_GET['sort'] ?? 'recommended'));
$priceMin = (int)($priceMin ?? ($_GET['price_min'] ?? 0));
$priceMax = (int)($priceMax ?? ($_GET['price_max'] ?? 0));

$baseUrl  = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
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


$brandRows = [];
foreach ($brands as $row) {
    $value = is_array($row) ? (string)($row['brand'] ?? '') : (string)$row;
    if ($value !== '') $brandRows[] = $value;
}
$brandRows = array_values(array_unique($brandRows));

$regionRows = [];
foreach ($regions as $row) {
    $value = is_array($row) ? (string)($row['region'] ?? '') : (string)$row;
    if ($value !== '') $regionRows[] = $value;
}
$regionRows = array_values(array_unique($regionRows));

$getImages = static function (array $listing): array {
    foreach (['images', 'item_images'] as $key) {
        if (!empty($listing[$key])) {
            $decoded = json_decode((string)$listing[$key], true);
            if (is_array($decoded)) {
                $decoded = array_values(array_filter(array_map('strval', $decoded)));
                if (!empty($decoded)) return $decoded;
            }
        }
    }
    if (!empty($listing['image'])) return [(string)$listing['image']];
    return [];
};

$normalizeAssetPath = static function ($path) use ($assetUrl): string {
    $path = trim((string)($path ?? ''));
    if ($path === '') return '';

    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
        return $path;
    }

    // Fix legacy/broken stored paths like /public/assets/public/uploads/...
    $path = preg_replace('#^/public/assets#', '', $path);
    $path = preg_replace('#/public/assets/#', '/', $path);
    $path = '/' . ltrim((string)$path, '/');

    return $assetUrl . $path;
};

$getBrandIcon = static function (array $listing) use ($normalizeAssetPath): string {
    return $normalizeAssetPath($listing['brand_icon'] ?? '');
};

$getImage = static function (array $listing) use ($getImages, $assetUrl, $getBrandIcon): string {
    $images = $getImages($listing);
    if (!empty($images[0])) return $images[0];

    $brandIcon = $getBrandIcon($listing);
    if ($brandIcon !== '') return $brandIcon;

    return $assetUrl . '/public/uploads/icons/default2.png';
};

$getUrl = static function (array $listing) use ($baseUrl): string {
    if (function_exists('dg_listing_url')) {
        return dg_listing_url($listing);
    }

    $id = (int)($listing['id'] ?? 0);
    $slug = trim((string)($listing['slug'] ?? ''), '/');

    if ($slug === '') {
        $title = trim((string)($listing['title'] ?? 'digital-good'));
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
            if ($converted !== false) $title = $converted;
        }
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim((string)$slug, '-');
    }

    if ($slug === '' && $id > 0) {
        $slug = (string)$id;
    }

    if ($id > 0 && !preg_match('/-' . preg_quote((string)$id, '/') . '$/', $slug)) {
        $slug .= '-' . $id;
    }

    return $baseUrl . '/digital-good/' . rawurlencode($slug);
};

$getPrice = static function (array $listing): string {
    // Prices are stored as EUR cents. Display them in the selected site currency.
    return lb_dg_format_display_price((int)($listing['price'] ?? 0));
};

$buildPageUrl = static function (int $targetPage) use ($categorySlug, $baseUrl, $brand, $region, $search, $sort, $priceMin, $priceMax): string {
    $params = [];
    if ($brand !== '') $params['brand'] = $brand;
    if ($region !== '') $params['region'] = $region;
    if ($search !== '') $params['search'] = $search;
    if ($sort !== '' && $sort !== 'recommended') $params['sort'] = $sort;
    if ($priceMin > 0) $params['price_min'] = $priceMin;
    if ($priceMax > 0) $params['price_max'] = $priceMax;
    if ($targetPage > 1) $params['page'] = $targetPage;
    $query = http_build_query($params);
    return $baseUrl . '/digital-goods/' . rawurlencode($categorySlug) . ($query ? '?' . $query : '');
};

$sortLabels = [
    'recommended' => 'Recommended',
    'price_asc' => 'Lowest Price',
    'price_desc' => 'Highest Price',
    'newest' => 'Newest',
    'oldest' => 'Oldest',
    'sold_desc' => 'Best Selling',
];

$sellerTotalSales = [];
$sellerIds = [];
foreach ($listings as $listingRow) {
    $sid = (int)($listingRow['seller_id'] ?? 0);
    if ($sid > 0) $sellerIds[$sid] = $sid;
}
if ($sellerIds) {
    try {
        global $db;
        $ids = array_values($sellerIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        foreach (($db->run("SELECT seller_id, total_sales AS service_sales FROM seller_stats WHERE seller_id IN ($placeholders)", ...$ids) ?: []) as $sellerSalesRow) {
            $sellerTotalSales[(int)($sellerSalesRow['seller_id'] ?? 0)] = max(0, (int)($sellerSalesRow['service_sales'] ?? 0));
        }
    } catch (Throwable $e) {}
}
?>

<section class="lb-shop-hero">
    <nav class="dg-breadcrumb" aria-label="<?= t('Breadcrumb') ?>">
        <a href="<?= $h($baseUrl . '/digital-goods/') ?>"><?= t('Digital Goods') ?></a>
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        <a href="<?= $h($baseUrl . '/digital-goods/' . rawurlencode($categorySlug)) ?>" aria-current="page"><?= $h($categoryName) ?></a>
    </nav>
    <div class="lb-shop-hero__inner">
        <div class="lb-shop-hero__icon" aria-hidden="true"><i class="<?= $h($category['icon'] ?? 'fa-solid fa-layer-group') ?>"></i></div>
        <div>
            <div class="lb-shop-hero__kicker">Digital Goods</div>
            <h1 class="lb-shop-hero__title"><?= $h($categoryName) ?></h1>
            <?php if ($description !== ''): ?><p class="lb-shop-hero__desc"><?= $h($description) ?></p><?php endif ?>
        </div>
    </div>
</section>
<style>
.lb-shop-hero{position:relative;border-bottom:0;background:transparent;overflow:hidden;margin:0;padding:0;}
.dg-breadcrumb{width:min(1500px,100%);margin:0 auto;padding:22px 28px 0;display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.42);font-size:12px;font-weight:750;}
.dg-breadcrumb a{color:rgba(255,255,255,.58);text-decoration:none;transition:color .16s ease;}
.dg-breadcrumb a:hover,.dg-breadcrumb a[aria-current="page"]{color:#a5b4fc;}
.dg-breadcrumb i{font-size:8px;color:rgba(255,255,255,.24);}
main > .lb-shop-hero:first-child,
.page-zoom > main > .lb-shop-hero:first-child{
  margin-top:var(--lb-content-top, 0px)!important;
}
/* main.css gives .ranked-accounts-page .container a big margin:4.167vw auto
   (~80px top+bottom) meant to sit below the old account-type-cards row.
   Digital goods has no such row, so that top margin just reads as a dead gap. */
body.digital-goods-shop-page .container,
body.digital-goods-shop-page .dg-shop-container{
  margin-top:20px!important;
  padding-top:0!important;
}
body.digital-goods-shop-page #accountsTop{
  height:0!important;
  margin:0!important;
  padding:0!important;
}
body.digital-goods-shop-page .shop-filterbar{
  margin-top:0!important;
}
body.digital-goods-shop-page .shop-filterbar__row{
  border:0!important;
  background:none!important;
}
/* filterbar_addon.css forces a sticky top offset sitewide; digital goods
   doesn't want the filterbar sticky at all, so drop it entirely here. */
body.digital-goods-shop-page .shop-filterbar--sticky{
  position:relative!important;
  top:auto!important;
}
.lb-shop-hero__inner{max-width:1500px;margin:0 auto;display:flex;align-items:center;gap:22px;min-height:170px;padding:36px 28px;}
.lb-shop-hero__icon{width:74px;height:74px;min-width:74px;border-radius:20px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;box-shadow:0 18px 50px rgba(0,0,0,.28);overflow:hidden;}
.lb-shop-hero__icon i{font-size:30px;color:#7c6cff;}
.lb-shop-hero__kicker{font-size:12px;letter-spacing:.13em;text-transform:uppercase;color:#8b9bff;font-weight:900;margin-bottom:8px;}
.lb-shop-hero__title{margin:0;font-size:29px;line-height:1.12;font-weight:950;letter-spacing:-.03em;color:#fff;}
.lb-shop-hero__desc{margin:8px 0 0;color:#a9adc4;font-size:15px;max-width:640px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
@media(max-width:760px){
    .lb-shop-hero{overflow:visible!important;background:transparent!important;border-bottom:0!important;margin-bottom:0!important;}
    .dg-breadcrumb{padding:14px 16px 0;font-size:11px;gap:8px;}
    .lb-shop-hero__inner{width:100%!important;max-width:100%!important;min-width:0!important;display:grid!important;grid-template-columns:42px minmax(0,1fr)!important;align-items:flex-start!important;gap:10px!important;padding:14px 16px 24px!important;margin:0!important;min-height:0!important;overflow:visible!important;}
    .lb-shop-hero__inner > div:last-child{min-width:0!important;width:100%!important;max-width:100%!important;overflow:visible!important;}
    .lb-shop-hero__icon{width:40px!important;height:40px!important;min-width:40px!important;border-radius:12px!important;margin-top:2px!important;}
    .lb-shop-hero__icon i{font-size:19px!important;}
    .lb-shop-hero__kicker{display:block!important;margin:0 0 4px!important;font-size:10px!important;line-height:1.15!important;white-space:normal!important;overflow:visible!important;}
    .lb-shop-hero__title{display:block!important;width:100%!important;max-width:none!important;margin:0!important;font-size:18px!important;line-height:1.22!important;white-space:normal!important;overflow:visible!important;text-overflow:clip!important;overflow-wrap:break-word!important;word-break:normal!important;}
    .lb-shop-hero__desc{display:block!important;width:100%!important;max-width:none!important;margin:5px 0 0!important;font-size:12.5px!important;line-height:1.35!important;white-space:normal!important;overflow:visible!important;text-overflow:clip!important;}
}
@media(max-width:380px){
    .lb-shop-hero__inner{grid-template-columns:38px minmax(0,1fr)!important;padding-left:14px!important;padding-right:14px!important;}
    .lb-shop-hero__icon{width:36px!important;height:36px!important;min-width:36px!important;}
    .lb-shop-hero__title{font-size:17px!important;}
    .lb-shop-hero__desc{font-size:12px!important;}
}
</style>

<div class="container dg-shop-container">
    <div id="accountsTop" style="height:1px;"></div>

    <div class="shop-filterbar shop-filterbar--sticky" id="shopFilterbar">
        <form id="shopFilters" class="shop-filterbar__form" method="get" action="<?= $h($baseUrl . '/digital-goods/' . rawurlencode($categorySlug)) ?>">
            <div class="shop-filterbar__row">
                <div class="shop-filterbar__search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="<?= $h($search) ?>" placeholder="<?= t('Search...') ?>" id="filterSearch">
                </div>

                <?php if (!empty($brandRows)): ?>
                <div class="shop-filterpill" data-dropdown="ddBrand">
                    <button type="button" class="shop-filterpill__btn" id="btnBrand">
                        <i class="fa-solid fa-tag"></i>
                        <span class="shop-filterpill__label"><?= t('Brand') ?></span>
                        <?php if ($brand !== ''): ?><span class="shop-filterpill__value"><?= $h($brand) ?></span><?php endif ?>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddBrand">
                        <div class="shop-dropdown__head"><span><?= t('Brand') ?></span><button type="button" class="shop-dropdown__close" data-close="ddBrand">✕</button></div>
                        <div class="shop-dropdown__body">
                            <label class="facet-item"><span class="facet-item__left"><span class="facet-item__text"><?= t('All brands') ?></span></span><input class="facet-item__check js-radio-submit" type="radio" name="brand" value="" <?= $brand === '' ? 'checked' : '' ?>><span class="facet-item__box"></span></label>
                            <?php foreach ($brandRows as $value): ?>
                                <label class="facet-item"><span class="facet-item__left"><span class="facet-item__text"><?= $h($value) ?></span></span><input class="facet-item__check js-radio-submit" type="radio" name="brand" value="<?= $h($value) ?>" <?= $brand === $value ? 'checked' : '' ?>><span class="facet-item__box"></span></label>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if (!empty($regionRows)): ?>
                <div class="shop-filterpill" data-dropdown="ddRegion">
                    <button type="button" class="shop-filterpill__btn" id="btnRegion">
                        <i class="fa-solid fa-globe"></i>
                        <span class="shop-filterpill__label"><?= t('Region') ?></span>
                        <?php if ($region !== ''): ?><span class="shop-filterpill__value"><?= $h($region) ?></span><?php endif ?>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddRegion">
                        <div class="shop-dropdown__head"><span><?= t('Region') ?></span><button type="button" class="shop-dropdown__close" data-close="ddRegion">✕</button></div>
                        <div class="shop-dropdown__body">
                            <label class="facet-item"><span class="facet-item__left"><span class="facet-item__text"><?= t('All regions') ?></span></span><input class="facet-item__check js-radio-submit" type="radio" name="region" value="" <?= $region === '' ? 'checked' : '' ?>><span class="facet-item__box"></span></label>
                            <?php foreach ($regionRows as $value): ?>
                                <label class="facet-item"><span class="facet-item__left"><span class="facet-item__text"><?= $h($value) ?></span></span><input class="facet-item__check js-radio-submit" type="radio" name="region" value="<?= $h($value) ?>" <?= $region === $value ? 'checked' : '' ?>><span class="facet-item__box"></span></label>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <div class="shop-filterpill" data-dropdown="ddPrice">
                    <button type="button" class="shop-filterpill__btn" id="btnPrice">
                        <i class="fa-solid fa-dollar-sign"></i>
                        <span class="shop-filterpill__label"><?= t('Price') ?></span>
                        <?php if ($priceMin > 0 || $priceMax > 0): ?><span class="shop-filterpill__value"><?= $priceMin ?><?= $priceMax > 0 ? '-' . $priceMax : '+' ?></span><?php endif ?>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddPrice">
                        <div class="shop-dropdown__head"><span><?= t('Price') ?></span><button type="button" class="shop-dropdown__close" data-close="ddPrice">✕</button></div>
                        <div class="shop-dropdown__body">
                            <div class="shop-price">
                                <div class="shop-price__fields">
                                    <div class="shop-price__field"><label><?= t('From') ?></label><div class="shop-price__input"><span class="shop-price__prefix">€</span><input type="number" name="price_min" min="0" value="<?= (int)$priceMin ?>"></div></div>
                                    <div class="shop-price__sep">-</div>
                                    <div class="shop-price__field"><label><?= t('To') ?></label><div class="shop-price__input"><span class="shop-price__prefix">€</span><input type="number" name="price_max" min="0" value="<?= (int)$priceMax ?>"></div></div>
                                </div>
                                <button type="submit" class="shop-apply-btn"><?= t('Apply') ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="shop-filterbar__actions">
                    <a class="reset-filters reset-filters--ghost" href="<?= $h($baseUrl . '/digital-goods/' . rawurlencode($categorySlug)) ?>"><?= t('Clear All') ?></a>
                    <div class="shop-sort" data-dropdown="ddSort">
                        <button type="button" class="shop-sort__btn" id="btnSort" aria-expanded="false">
                            <i class="fa-solid fa-arrow-up-wide-short"></i>
                            <span id="sortLabel"><?= $h($sortLabels[$sort] ?? 'Recommended') ?></span>
                            <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="shop-dropdown shop-dropdown--menu" id="ddSort">
                            <div class="shop-dropdown__head"><span><?= t('Sort By') ?></span><button type="button" class="shop-dropdown__close" data-close="ddSort">✕</button></div>
                            <div class="shop-dropdown__body">
                                <?php foreach ($sortLabels as $key => $label): ?>
                                    <button type="submit" name="sort" value="<?= $h($key) ?>" class="shop-menuitem <?= $sort === $key ? 'is-active' : '' ?>"><i class="fa-solid fa-arrow-up-wide-short"></i> <?= $h($label) ?></button>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shop-filterbar__chips" id="activeFilters">
                <?php if ($search !== ''): ?><span class="filter-chip"><?= t('Search') ?>: <?= $h($search) ?></span><?php endif ?>
                <?php if ($brand !== ''): ?><span class="filter-chip"><?= t('Brand') ?>: <?= $h($brand) ?></span><?php endif ?>
                <?php if ($region !== ''): ?><span class="filter-chip"><?= t('Region') ?>: <?= $h($region) ?></span><?php endif ?>
            </div>
        </form>
    </div>

    <div class="shop-toolbar">
        <div class="shop-toolbar__left">
            <span class="shop-count">
                <span><?= count($listings) ?></span>
                <span class="shop-count__sep">/</span>
                <span><?= (int)$totalItems ?></span>
                <?= t('Products') ?>
            </span>
        </div>
    </div>

    <?php if (!empty($listings)): ?>
        <div class="accounts-grid dg-products-grid" id="accountsGrid">
            <?php foreach ($listings as $listing): ?>
                <?php
                    $url       = $getUrl($listing);
                    $brandIcon = $getBrandIcon($listing);
                    $title     = (string)($listing['title'] ?? 'Digital Good');
                    $brand     = trim((string)($listing['brand'] ?? ''));
                    $delivery  = trim((string)($listing['delivery_type'] ?? 'manual'));
                    $isInstant = $delivery === 'instant';
                    $stock     = (int)($listing['stock'] ?? 0);
                    $region    = trim((string)($listing['region'] ?? ''));
                    $validity  = (int)($listing['validity_days'] ?? 0) > 0 ? (int)$listing['validity_days'] . ' ' . t('days') : t('Lifetime');
                    $avgRating = round((float)($listing['avg_rating'] ?? 0), 1);
                    $reviewCnt = (int)($listing['review_count'] ?? 0);
                    $sellerId  = (int)($listing['seller_id'] ?? 0);
                    $sellerTotalSold = $sellerId > 0 ? (int)($sellerTotalSales[$sellerId] ?? 0) : 0;
                    $sellerName = (string)($listing['seller_username'] ?? 'Seller');
                    $sellerIconUrl = (string)($listing['seller_icon'] ?? '');

                    // Brand-aware gradient
                    $dgBannerPalettes = [
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
                    ];
                    $dgbp = ['#0f0e27','#1e1b4b','#312e81'];
                    $dgSeed = strtolower($brand);
                    foreach ($dgBannerPalettes as $k => $v) {
                        if (str_contains($dgSeed, $k)) { $dgbp = $v; break; }
                    }
                    $cardBannerGrad = "linear-gradient(135deg,{$dgbp[0]} 0%,{$dgbp[1]} 50%,{$dgbp[2]} 100%)";
                ?>
                <a class="dg-card-new" href="<?= $h($url) ?>">
                    <!-- Top: banner strip with centered icon -->
                    <div class="dg-card-new__banner" style="background:<?= $cardBannerGrad ?>">
                        <div class="dg-card-new__banner-rings">
                            <span></span><span></span>
                        </div>
                        <?php if ((int)($listing['is_featured'] ?? 0) === 1): ?>
                        <span class="dg-card-new__featured"><i class="fa-solid fa-star"></i> <?= t('Featured') ?></span>
                        <?php endif ?>
                        <div class="dg-card-new__icon">
                            <?php if ($brandIcon !== ''): ?>
                                <img src="<?= $h($brandIcon) ?>" alt="<?= $h($brand ?: $title) ?>">
                            <?php else: ?>
                                <i class="<?= $h($category['icon'] ?? 'fa-solid fa-layer-group') ?>"></i>
                            <?php endif ?>
                        </div>
                        <?php if ($avgRating > 0): ?>
                        <div class="dg-card-new__rating">
                            <i class="fa-solid fa-star"></i> <?= number_format($avgRating, 1) ?>
                            <span>(<?= $reviewCnt ?>)</span>
                        </div>
                        <?php endif ?>
                    </div>

                    <!-- Body -->
                    <div class="dg-card-new__body">
                        <?php if ($brand !== ''): ?>
                        <div class="dg-card-new__brand"><?= $h($brand) ?></div>
                        <?php endif ?>
                        <div class="dg-card-new__title"><?= $h($title) ?></div>

                        <!-- Key info pills -->
                        <div class="dg-card-new__pills">
                            <?php if ($region !== ''): ?>
                            <span class="dg-card-new__pill dg-card-new__pill--blue"><i class="fa-solid fa-globe"></i><?= $h($region) ?></span>
                            <?php endif ?>
                            <span class="dg-card-new__pill <?= $isInstant ? 'dg-card-new__pill--green' : 'dg-card-new__pill--yellow' ?>">
                                <i class="fa-solid fa-<?= $isInstant ? 'bolt' : 'clock' ?>"></i><?= $isInstant ? t('Instant') : t('Manual') ?>
                            </span>
                            <span class="dg-card-new__pill dg-card-new__pill--purple"><i class="fa-solid fa-calendar-days"></i><?= $h($validity) ?></span>
                            <span class="dg-card-new__pill dg-card-new__pill--grey"><i class="fa-solid fa-box"></i><?= $stock ?> <?= t('in stock') ?></span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="dg-card-new__foot">
                        <div class="dg-card-new__price-wrap">
                            <div class="dg-card-new__price"><?= $h($getPrice($listing)) ?></div>

                            <div class="dg-card-new__seller-mid">
                                <?php if ($sellerIconUrl !== ''): ?>
                                <img src="<?= $h($sellerIconUrl) ?>" alt="<?= $h($sellerName) ?>" class="dg-card-new__seller-ava">
                                <?php else: ?>
                                <span class="dg-card-new__seller-ava dg-card-new__seller-ava--ph"><i class="fa-solid fa-user"></i></span>
                                <?php endif ?>
                                <div class="dg-card-new__seller-copy">
                                    <div class="dg-card-new__seller-name"><span class="dg-card-new__seller-label"><?= strtoupper(t('Sold by')) ?></span>&nbsp;<?= $h($sellerName) ?></div>
                                    <div class="dg-card-new__seller-sold"><i class="fa-solid fa-thumbs-up"></i> <?= number_format($sellerTotalSold) ?> <?= t('Sold') ?></div>
                                </div>
                            </div>

                            <div class="dg-card-new__cta"><?= t('Buy Now') ?> <i class="fa-solid fa-arrow-right"></i></div>
                        </div>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="shop-empty" id="shopEmpty">
            <div class="shop-empty__inner">
                <div class="shop-empty__emoji">🥺</div>
                <div class="shop-empty__title"><?= t('No products found') ?></div>
                <div class="shop-empty__text"><?= t('Try adjusting filters or check back later.') ?></div>
                <a class="shop-empty__btn shop-empty__btn--primary" href="<?= $h($baseUrl . '/digital-goods/' . rawurlencode($categorySlug)) ?>"><?= t('Reset Filters') ?></a>
            </div>
        </div>
    <?php endif ?>

    <?php if ($totalPages > 1): ?>
        <nav class="shop-pagination" id="shopPagination" aria-label="Pagination">
            <?php if ($page > 1): ?><a href="<?= $h($buildPageUrl($page - 1)) ?>">‹ <?= t('Prev') ?></a><?php else: ?><span class="is-disabled">‹ <?= t('Prev') ?></span><?php endif ?>
            <?php
                $from = max(1, $page - 2);
                $to = min($totalPages, $page + 2);
                if ($from > 1) echo '<a href="' . $h($buildPageUrl(1)) . '">1</a><span>...</span>';
                for ($i = $from; $i <= $to; $i++):
            ?>
                <?php if ($i === $page): ?><span class="is-active"><?= $i ?></span><?php else: ?><a href="<?= $h($buildPageUrl($i)) ?>"><?= $i ?></a><?php endif ?>
            <?php endfor; if ($to < $totalPages) echo '<span>...</span><a href="' . $h($buildPageUrl($totalPages)) . '">' . $totalPages . '</a>'; ?>
            <?php if ($page < $totalPages): ?><a href="<?= $h($buildPageUrl($page + 1)) ?>"><?= t('Next') ?> ›</a><?php else: ?><span class="is-disabled"><?= t('Next') ?> ›</span><?php endif ?>
        </nav>
    <?php endif ?>
</div>

<style>
html,body{overflow-x:hidden}.ranked-accounts-page{overflow-x:hidden}.dg-shop-container{position:relative;z-index:2}.ranked-accounts-page .shop-filterbar{position:relative;z-index:50;margin:24px 0 22px;overflow:visible!important}.ranked-accounts-page .shop-filterbar--sticky{position:relative!important;top:auto!important}.ranked-accounts-page .shop-filterbar__form{width:100%}.ranked-accounts-page .shop-filterbar__row{display:flex;align-items:center;gap:10px;flex-wrap:nowrap;width:100%;max-width:100%;padding:10px 12px;border-radius:999px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);overflow:visible!important}.ranked-accounts-page .shop-filterbar__search{display:flex;align-items:center;gap:10px;flex:0 0 clamp(220px,18vw,320px);min-width:0;height:46px;padding:0 14px;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.88)}.ranked-accounts-page .shop-filterbar__search input{width:100%;background:transparent;border:0;outline:0;color:#fff;font-weight:600}.ranked-accounts-page .shop-filterbar__search input::placeholder{color:rgba(255,255,255,.45)}.ranked-accounts-page .shop-filterpill,.ranked-accounts-page .shop-sort{position:relative;flex:0 0 auto;z-index:60}.ranked-accounts-page .shop-filterpill__btn,.ranked-accounts-page .shop-sort__btn{height:46px;display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:0 15px;border-radius:999px;background:rgba(255,255,255,.065);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.94);font-weight:850;white-space:nowrap;line-height:1;cursor:pointer}.ranked-accounts-page .shop-filterpill__btn:hover,.ranked-accounts-page .shop-sort__btn:hover,.ranked-accounts-page .shop-filterpill.is-open .shop-filterpill__btn,.ranked-accounts-page .shop-sort.is-open .shop-sort__btn{background:rgba(99,102,241,.18);border-color:rgba(139,92,246,.55)}.ranked-accounts-page .shop-filterpill__value{font-weight:900;color:#fff;max-width:92px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block}.ranked-accounts-page .shop-filterbar__actions{display:flex;align-items:center;gap:10px;margin-left:auto;flex:0 0 auto}.ranked-accounts-page .reset-filters--ghost{height:48px;display:inline-flex;align-items:center;justify-content:center;padding:0 24px;border-radius:999px;border:1px solid rgba(99,102,241,.7);background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-weight:900;box-shadow:0 14px 34px rgba(99,102,241,.28);cursor:pointer;white-space:nowrap;text-decoration:none}.ranked-accounts-page .shop-dropdown{position:absolute;top:calc(100% + 10px);left:0;width:320px;max-width:min(320px,calc(100vw - 24px));display:none;overflow:hidden;border-radius:16px;background:rgba(25,24,38,.96);border:1px solid rgba(255,255,255,.10);box-shadow:0 20px 60px rgba(0,0,0,.58);backdrop-filter:blur(16px);z-index:9999;color:#fff}.ranked-accounts-page .shop-dropdown.is-open{display:block}.ranked-accounts-page .shop-filterbar__actions .shop-dropdown,.ranked-accounts-page .shop-sort .shop-dropdown{left:auto;right:0}.ranked-accounts-page .shop-dropdown__head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 15px;border-bottom:1px solid rgba(255,255,255,.08);font-weight:950}.ranked-accounts-page .shop-dropdown__close{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.86);cursor:pointer}.ranked-accounts-page .shop-dropdown__body{padding:12px 14px}.ranked-accounts-page .facet-item{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px;width:100%;padding:10px;border-radius:12px;cursor:pointer;user-select:none;color:rgba(255,255,255,.92);font-weight:750;line-height:1.25}.ranked-accounts-page .facet-item:hover{background:rgba(255,255,255,.065)}.ranked-accounts-page .facet-item__left{display:flex;align-items:center;gap:10px;min-width:0;flex:1 1 auto}.ranked-accounts-page .facet-item__text{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0;max-width:100%}.ranked-accounts-page .facet-item__check{position:absolute;opacity:0;pointer-events:none}.ranked-accounts-page .facet-item__box{width:18px;height:18px;border-radius:6px;border:1px solid rgba(255,255,255,.20);background:rgba(0,0,0,.25);flex:0 0 18px}.ranked-accounts-page .facet-item__check:checked + .facet-item__box{background:#7c5cff;border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.22)}.ranked-accounts-page .shop-price__fields{display:grid;grid-template-columns:1fr auto 1fr;gap:10px;align-items:end}.ranked-accounts-page .shop-price__field label{display:block;margin:0 0 7px;color:rgba(255,255,255,.72);font-size:12px;font-weight:800}.ranked-accounts-page .shop-price__input{height:44px;display:flex;align-items:center;gap:8px;width:100%;padding:0 12px;border-radius:12px;background:rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.08);color:#fff}.ranked-accounts-page .shop-price__input input{width:100%;background:transparent;border:0;outline:0;color:#fff}.ranked-accounts-page .shop-price__sep{padding-bottom:13px;color:rgba(255,255,255,.65);font-weight:900}.shop-apply-btn{width:100%;height:42px;margin-top:14px;border:0;border-radius:999px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-weight:900;cursor:pointer}.ranked-accounts-page .shop-menuitem{width:100%;display:flex;align-items:center;gap:10px;border:0;background:transparent;color:rgba(255,255,255,.9);font-weight:850;text-align:left;padding:11px 10px;border-radius:12px;cursor:pointer}.ranked-accounts-page .shop-menuitem:hover,.ranked-accounts-page .shop-menuitem.is-active{background:rgba(99,102,241,.18);color:#fff}.shop-filterbar__chips{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:12px}.filter-chip{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.78);font-size:12px;font-weight:800}.shop-toolbar{display:flex;align-items:center;justify-content:space-between;margin:0 0 16px}.shop-count{color:rgba(255,255,255,.9);font-weight:900}.shop-count__sep{color:rgba(255,255,255,.35);margin:0 4px}

/* ── NEW DG PRODUCT CARDS ── */
.dg-products-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}

.dg-card-new{
  display:flex;flex-direction:column;text-decoration:none;color:#fff;
  border-radius:20px;overflow:hidden;position:relative;
  background:linear-gradient(180deg,rgba(255,255,255,.075),rgba(255,255,255,.028));
  border:1px solid rgba(255,255,255,.10);
  box-shadow:0 14px 40px rgba(0,0,0,.28),inset 0 1px 0 rgba(255,255,255,.06);
  transition:transform .2s cubic-bezier(.22,1,.36,1),box-shadow .2s,border-color .2s;
}
.dg-card-new::before{
  content:"";position:absolute;inset:-1px;z-index:1;pointer-events:none;opacity:0;
  background:radial-gradient(500px 160px at 18% 0%,rgba(34,211,238,.14),transparent 62%),
             radial-gradient(500px 160px at 88% 0%,rgba(217,70,239,.16),transparent 64%);
  transition:opacity .2s;
}
.dg-card-new:hover{transform:translateY(-5px);border-color:rgba(168,85,247,.42);box-shadow:0 24px 70px rgba(0,0,0,.42),0 0 0 1px rgba(168,85,247,.14),0 0 44px rgba(139,92,246,.14)}
.dg-card-new:hover::before{opacity:1}

/* Banner strip */
.dg-card-new__banner{
  width:100%;height:130px;position:relative;overflow:hidden;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.dg-card-new__banner::after{
  content:"";position:absolute;left:0;right:0;bottom:0;height:55%;z-index:1;pointer-events:none;
  background:linear-gradient(to top,rgba(5,5,15,.9) 0%,transparent 100%);
}
.dg-card-new__banner-rings{position:absolute;inset:0;pointer-events:none}
.dg-card-new__banner-rings span{position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.07)}
.dg-card-new__banner-rings span:nth-child(1){width:200px;height:200px;right:-50px;top:-80px}
.dg-card-new__banner-rings span:nth-child(2){width:100px;height:100px;left:20px;bottom:-50px}
.dg-card-new__icon{
  position:relative;z-index:2;
  width:64px;height:64px;border-radius:18px;overflow:hidden;
  display:flex;align-items:center;justify-content:center;
  background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.2);
  box-shadow:0 8px 24px rgba(0,0,0,.45);
}
.dg-card-new__icon img{width:44px;height:44px;object-fit:contain;display:block}
.dg-card-new__icon i{font-size:1.7rem;color:rgba(255,255,255,.82)}
.dg-card-new__featured{
  position:absolute;top:10px;left:10px;z-index:3;
  display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:99px;
  font-size:10px;font-weight:950;letter-spacing:.06em;text-transform:uppercase;color:#fff;
  background:linear-gradient(90deg,#d946ef,#8b5cf6,#22d3ee);
  box-shadow:0 8px 20px rgba(139,92,246,.28);
}
.dg-card-new__rating{
  position:absolute;bottom:10px;right:10px;z-index:3;
  display:inline-flex;align-items:center;gap:4px;
  padding:3px 8px;border-radius:99px;
  font-size:11px;font-weight:900;color:#fff;
  background:rgba(0,0,0,.55);border:1px solid rgba(255,255,255,.12);
  backdrop-filter:blur(4px);
}
.dg-card-new__rating i{color:#fbbf24;font-size:10px}
.dg-card-new__rating span{color:rgba(255,255,255,.45);font-size:10px}

/* Body */
.dg-card-new__body{padding:14px 16px 0;flex:1;min-width:0}
.dg-card-new__brand{font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.1em;color:rgba(168,85,247,.8);margin-bottom:4px}
.dg-card-new__title{font-size:14px;font-weight:950;color:#fff;line-height:1.3;letter-spacing:-.01em;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:10px}

/* Pills */
.dg-card-new__pills{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px}
.dg-card-new__pill{display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:99px;font-size:10px;font-weight:800;white-space:nowrap}
.dg-card-new__pill i{font-size:9px}
.dg-card-new__pill--blue  {background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.2);color:#7dd3fc}
.dg-card-new__pill--green {background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80}
.dg-card-new__pill--yellow{background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);color:#fde68a}
.dg-card-new__pill--purple{background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);color:#a5b4fc}
.dg-card-new__pill--grey  {background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.45)}

/* Footer */
.dg-card-new__foot{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 16px 14px;
  border-top:1px solid rgba(255,255,255,.06);
  margin-top:auto;
}
.dg-card-new__seller{display:flex;align-items:center;gap:8px;min-width:0;flex:1}
.dg-card-new__seller-ava{width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid rgba(99,102,241,.35)}
.dg-card-new__seller-ava--ph{display:inline-flex;align-items:center;justify-content:center;background:rgba(99,102,241,.18);color:#a5b4fc;font-size:.7rem}
.dg-card-new__seller-name{font-size:11px;font-weight:850;color:rgba(255,255,255,.75);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:90px}
.dg-card-new__seller-sold{font-size:10px;color:rgba(255,255,255,.3);font-weight:700;margin-top:1px}
.dg-card-new__price-wrap{display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0}
.dg-card-new__price{font-size:18px;font-weight:950;color:#fff;line-height:1;letter-spacing:-.02em}
.dg-card-new__cta{
  display:inline-flex;align-items:center;gap:5px;
  padding:5px 12px;border-radius:999px;
  font-size:11px;font-weight:900;color:#fff;white-space:nowrap;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  box-shadow:0 6px 16px rgba(99,102,241,.32);
  transition:opacity .15s,box-shadow .15s;
}
.dg-card-new:hover .dg-card-new__cta{opacity:.9;box-shadow:0 8px 22px rgba(99,102,241,.45)}
.dg-card-new__cta i{font-size:9px}

/* Empty / pagination — unchanged */
.shop-empty{padding:54px 20px;text-align:center;border-radius:24px;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.08)}.shop-empty__emoji{font-size:42px;margin-bottom:14px}.shop-empty__title{font-size:22px;color:#fff;font-weight:950;margin-bottom:6px}.shop-empty__text{color:rgba(255,255,255,.62)}.shop-empty__btn{display:inline-flex;margin-top:18px;align-items:center;justify-content:center;min-height:42px;padding:0 18px;border-radius:999px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;text-decoration:none;font-weight:900}.shop-pagination{display:flex;align-items:center;justify-content:center;gap:8px;margin:34px 0 20px}.shop-pagination a,.shop-pagination span{min-width:38px;height:38px;padding:0 12px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:850}.shop-pagination .is-active{background:linear-gradient(135deg,#6366f1,#8b5cf6)}.shop-pagination .is-disabled{opacity:.45}

@media(max-width:1200px){.dg-products-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.ranked-accounts-page .shop-filterbar__row{border-radius:24px;flex-wrap:wrap}.ranked-accounts-page .shop-filterbar__search{flex:1 1 260px}.ranked-accounts-page .shop-filterbar__actions{margin-left:0}}
@media(max-width:900px){.dg-products-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ranked-accounts-page .shop-filterbar__actions{width:100%;justify-content:space-between}}
@media(max-width:620px){.dg-products-grid{grid-template-columns:1fr}.ranked-accounts-page .shop-filterbar__row{align-items:stretch}.ranked-accounts-page .shop-filterpill,.ranked-accounts-page .shop-sort{width:100%}.ranked-accounts-page .shop-filterpill__btn,.ranked-accounts-page .shop-sort__btn,.ranked-accounts-page .reset-filters--ghost{width:100%}.ranked-accounts-page .shop-dropdown{left:0!important;right:auto!important;width:100%}}
.dg-shop-container.is-ajax-loading{opacity:.58;pointer-events:none;transition:opacity .18s ease}.dg-shop-container.is-ajax-loading::after{content:"";position:fixed;right:24px;bottom:24px;width:36px;height:36px;border-radius:50%;border:3px solid rgba(255,255,255,.18);border-top-color:#8b5cf6;z-index:99999;animation:dgAjaxSpin .75s linear infinite}@keyframes dgAjaxSpin{to{transform:rotate(360deg)}}


/* ── FIXED LARGER DIGITAL GOODS CARDS ── */
.digital-goods-shop-page .dg-products-grid,
.ranked-accounts-page .dg-products-grid{
  grid-template-columns:repeat(3,minmax(300px,1fr)) !important;
  gap:26px !important;
  align-items:stretch !important;
}
.digital-goods-shop-page .dg-card-new,
.ranked-accounts-page .dg-card-new{
  min-height:430px !important;
  border-radius:26px !important;
  background:linear-gradient(180deg,rgba(255,255,255,.085),rgba(255,255,255,.032)) !important;
  border:1px solid rgba(255,255,255,.12) !important;
  box-shadow:0 18px 58px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.07) !important;
  overflow:hidden !important;
}
.digital-goods-shop-page .dg-card-new:hover,
.ranked-accounts-page .dg-card-new:hover{
  transform:translateY(-6px) !important;
  border-color:rgba(168,85,247,.46) !important;
  box-shadow:0 28px 78px rgba(0,0,0,.46),0 0 0 1px rgba(168,85,247,.16),0 0 46px rgba(139,92,246,.16) !important;
}
.digital-goods-shop-page .dg-card-new__banner,
.ranked-accounts-page .dg-card-new__banner{
  height:170px !important;
}
.digital-goods-shop-page .dg-card-new__icon,
.ranked-accounts-page .dg-card-new__icon{
  width:82px !important;
  height:82px !important;
  border-radius:22px !important;
  border-width:2px !important;
}
.digital-goods-shop-page .dg-card-new__icon img,
.ranked-accounts-page .dg-card-new__icon img{
  width:58px !important;
  height:58px !important;
}
.digital-goods-shop-page .dg-card-new__body,
.ranked-accounts-page .dg-card-new__body{
  padding:18px 20px 0 !important;
}
.digital-goods-shop-page .dg-card-new__brand,
.ranked-accounts-page .dg-card-new__brand{
  font-size:11px !important;
  letter-spacing:.11em !important;
  margin-bottom:7px !important;
}
.digital-goods-shop-page .dg-card-new__title,
.ranked-accounts-page .dg-card-new__title{
  font-size:18px !important;
  line-height:1.28 !important;
  margin-bottom:15px !important;
}
.digital-goods-shop-page .dg-card-new__pills,
.ranked-accounts-page .dg-card-new__pills{
  display:grid !important;
  grid-template-columns:repeat(2,minmax(0,1fr)) !important;
  gap:8px !important;
  margin-bottom:18px !important;
}
.digital-goods-shop-page .dg-card-new__pill,
.ranked-accounts-page .dg-card-new__pill{
  justify-content:flex-start !important;
  min-width:0 !important;
  padding:8px 10px !important;
  border-radius:13px !important;
  font-size:12px !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
}
.digital-goods-shop-page .dg-card-new__pill i,
.ranked-accounts-page .dg-card-new__pill i{
  font-size:11px !important;
  width:14px !important;
  text-align:center !important;
  flex:0 0 auto !important;
}
.digital-goods-shop-page .dg-card-new__foot,
.ranked-accounts-page .dg-card-new__foot{
  padding:16px 20px 18px !important;
  gap:14px !important;
  background:rgba(0,0,0,.12) !important;
}
.digital-goods-shop-page .dg-card-new__seller-ava,
.ranked-accounts-page .dg-card-new__seller-ava{
  width:40px !important;
  height:40px !important;
}
.digital-goods-shop-page .dg-card-new__seller-name,
.ranked-accounts-page .dg-card-new__seller-name{
  font-size:13px !important;
  max-width:150px !important;
  color:rgba(255,255,255,.86) !important;
}
.digital-goods-shop-page .dg-card-new__seller-sold,
.ranked-accounts-page .dg-card-new__seller-sold{
  font-size:12px !important;
  color:rgba(255,255,255,.45) !important;
}
.digital-goods-shop-page .dg-card-new__price,
.ranked-accounts-page .dg-card-new__price{
  font-size:24px !important;
}
.digital-goods-shop-page .dg-card-new__cta,
.ranked-accounts-page .dg-card-new__cta{
  padding:8px 15px !important;
  font-size:12px !important;
  border-radius:13px !important;
}
@media(max-width:1200px){
  .digital-goods-shop-page .dg-products-grid,
  .ranked-accounts-page .dg-products-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;}
}
@media(max-width:680px){
  .digital-goods-shop-page .dg-products-grid,
  .ranked-accounts-page .dg-products-grid{grid-template-columns:1fr !important;gap:18px !important;}
  .digital-goods-shop-page .dg-card-new__pills,
  .ranked-accounts-page .dg-card-new__pills{grid-template-columns:1fr !important;}
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

/* =========================================================
   FINAL DIGITAL GOODS SHOP CARDS, 4 COLUMNS, VIEW CARD STYLE
   ========================================================= */
@media (min-width: 1280px){
  .digital-goods-shop-page .dg-shop-container{
    width:min(1540px, calc(100vw - 72px)) !important;
    max-width:none !important;
  }
  .digital-goods-shop-page .dg-products-grid,
  .digital-goods-shop-page .accounts-grid.dg-products-grid{
    display:grid !important;
    grid-template-columns:repeat(4, minmax(0, 1fr)) !important;
    gap:22px !important;
    align-items:stretch !important;
  }
}

@media (min-width: 900px) and (max-width: 1279px){
  .digital-goods-shop-page .dg-products-grid,
  .digital-goods-shop-page .accounts-grid.dg-products-grid{
    display:grid !important;
    grid-template-columns:repeat(3, minmax(0, 1fr)) !important;
    gap:20px !important;
  }
}

@media (min-width: 620px) and (max-width: 899px){
  .digital-goods-shop-page .dg-products-grid,
  .digital-goods-shop-page .accounts-grid.dg-products-grid{
    display:grid !important;
    grid-template-columns:repeat(2, minmax(0, 1fr)) !important;
    gap:16px !important;
  }
}

@media (max-width: 619px){
  .digital-goods-shop-page .dg-products-grid,
  .digital-goods-shop-page .accounts-grid.dg-products-grid{
    display:grid !important;
    grid-template-columns:1fr !important;
    gap:16px !important;
  }
}

.digital-goods-shop-page .dg-products-grid .dg-card-new{
  width:100% !important;
  min-width:0 !important;
  max-width:none !important;
  height:auto !important;
  min-height:0 !important;
  display:flex !important;
  flex-direction:column !important;
  border-radius:22px !important;
  overflow:hidden !important;
  background:linear-gradient(180deg, rgba(255,255,255,.070), rgba(255,255,255,.026)) !important;
  border:1px solid rgba(255,255,255,.105) !important;
  box-shadow:0 16px 44px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.055) !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new:hover{
  transform:translateY(-4px) !important;
  border-color:rgba(139,92,246,.46) !important;
  box-shadow:0 24px 70px rgba(0,0,0,.42), 0 0 0 1px rgba(139,92,246,.14), 0 0 44px rgba(139,92,246,.14) !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__banner{
  height:170px !important;
  min-height:170px !important;
  flex:0 0 170px !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__icon{
  width:76px !important;
  height:76px !important;
  border-radius:22px !important;
  background:rgba(255,255,255,.12) !important;
  border:2px solid rgba(255,255,255,.20) !important;
  box-shadow:0 8px 24px rgba(0,0,0,.45) !important;
}
.digital-goods-shop-page .dg-products-grid .dg-card-new__icon img{
  width:54px !important;
  height:54px !important;
  object-fit:contain !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__body{
  padding:18px 18px 16px !important;
  min-height:174px !important;
  display:flex !important;
  flex-direction:column !important;
  gap:0 !important;
  background:rgba(12,11,24,.42) !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__brand{
  margin:0 0 8px !important;
  font-size:11px !important;
  line-height:1 !important;
  font-weight:950 !important;
  letter-spacing:.14em !important;
  text-transform:uppercase !important;
  color:#a855f7 !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__title{
  min-height:44px !important;
  margin:0 0 14px !important;
  font-size:16px !important;
  line-height:1.28 !important;
  font-weight:950 !important;
  letter-spacing:-.025em !important;
  color:#fff !important;
  display:-webkit-box !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
  overflow:hidden !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__pills{
  margin-top:auto !important;
  display:grid !important;
  grid-template-columns:1fr 1fr !important;
  gap:8px !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__pill{
  min-width:0 !important;
  height:32px !important;
  padding:0 10px !important;
  border-radius:10px !important;
  justify-content:center !important;
  font-size:11.5px !important;
  font-weight:900 !important;
  white-space:nowrap !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__foot{
  margin-top:auto !important;
  padding:0 !important;
  min-height:0 !important;
  background:linear-gradient(180deg, rgba(255,255,255,.050), rgba(255,255,255,.025)) !important;
  border-top:1px solid rgba(255,255,255,.075) !important;
  border-radius:0 !important;
  overflow:hidden !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__price-wrap{
  width:100% !important;
  box-sizing:border-box !important;
  padding:16px 18px 0 !important;
  min-height:0 !important;
  display:grid !important;
  grid-template-columns:minmax(0,1fr) auto !important;
  grid-template-areas:
    "price cta"
    "seller seller" !important;
  gap:12px 14px !important;
  align-items:center !important;
  background:transparent !important;
  border:0 !important;
  border-radius:0 !important;
  box-shadow:none !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__price{
  grid-area:price !important;
  align-self:center !important;
  justify-self:start !important;
  font-size:22px !important;
  line-height:1 !important;
  font-weight:1000 !important;
  color:#fff !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__price::before{
  content:"PRICE" !important;
  display:block !important;
  margin:0 0 6px !important;
  font-size:9px !important;
  line-height:1 !important;
  font-weight:950 !important;
  letter-spacing:.14em !important;
  color:rgba(255,255,255,.32) !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__cta{
  grid-area:cta !important;
  justify-self:end !important;
  align-self:center !important;
  min-width:118px !important;
  height:42px !important;
  padding:0 17px !important;
  border-radius:13px !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  font-size:12px !important;
  font-weight:950 !important;
  color:#fff !important;
  background:linear-gradient(135deg,#6366f1,#8b5cf6) !important;
  box-shadow:0 12px 30px rgba(99,102,241,.28) !important;
  border:0 !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-mid{
  grid-area:seller !important;
  width:calc(100% + 36px) !important;
  max-width:none !important;
  margin:0 -18px !important;
  padding:12px 18px !important;
  min-height:58px !important;
  box-sizing:border-box !important;
  display:flex !important;
  align-items:center !important;
  justify-content:flex-start !important;
  gap:11px !important;
  background:linear-gradient(180deg,rgba(12,13,25,.50),rgba(8,9,18,.86)) !important;
  border-top:1px solid rgba(255,255,255,.075) !important;
  border-radius:0 !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-mid::before,
.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-mid::after{
  content:none !important;
  display:none !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-ava{
  width:36px !important;
  height:36px !important;
  flex:0 0 36px !important;
  display:block !important;
  border-radius:50% !important;
  object-fit:cover !important;
  border:2px solid rgba(139,92,246,.52) !important;
  box-shadow:0 0 0 4px rgba(139,92,246,.10), 0 8px 20px rgba(0,0,0,.28) !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-copy{
  flex:1 !important;
  min-width:0 !important;
  display:grid !important;
  grid-template-columns:minmax(0,1fr) auto !important;
  align-items:center !important;
  gap:10px !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-name{
  min-width:0 !important;
  max-width:none !important;
  font-size:12px !important;
  line-height:1.1 !important;
  font-weight:950 !important;
  color:#fff !important;
  white-space:nowrap !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-label{
  font-size:9px !important;
  font-weight:950 !important;
  letter-spacing:.12em !important;
  color:rgba(255,255,255,.36) !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-sold{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:6px !important;
  min-height:28px !important;
  padding:0 10px !important;
  border-radius:9px !important;
  background:rgba(255,255,255,.065) !important;
  border:1px solid rgba(255,255,255,.10) !important;
  color:rgba(255,255,255,.76) !important;
  font-size:11.5px !important;
  font-weight:900 !important;
  white-space:nowrap !important;
}

.digital-goods-shop-page .dg-products-grid .dg-card-new__seller-sold i{
  color:#22c55e !important;
  font-size:11px !important;
}

@media (max-width: 760px){
  .digital-goods-shop-page .dg-products-grid .dg-card-new__banner{
    height:150px !important;
    min-height:150px !important;
    flex-basis:150px !important;
  }
  .digital-goods-shop-page .dg-products-grid .dg-card-new__body{
    min-height:160px !important;
    padding:16px !important;
  }
  .digital-goods-shop-page .dg-products-grid .dg-card-new__price-wrap{
    padding:14px 16px 0 !important;
  }
  .digital-goods-shop-page .dg-products-grid .dg-card-new__seller-mid{
    width:calc(100% + 32px) !important;
    margin:0 -16px !important;
    padding:11px 16px !important;
  }
}

</style>

<script>
(function(){
    var activeRequest = null;
    var searchTimer = null;

    function getContainer(){
        return document.querySelector('.dg-shop-container');
    }

    function getPills(){
        return document.querySelectorAll('[data-dropdown]');
    }

    function closeDropdowns(except){
        getPills().forEach(function(p){
            var id = p.getAttribute('data-dropdown');
            var dd = document.getElementById(id);
            if (!dd || dd === except) return;
            dd.classList.remove('is-open');
            p.classList.remove('is-open');
        });
    }

    function cleanParams(params){
        Array.from(params.keys()).forEach(function(key){
            var values = params.getAll(key);
            params.delete(key);

            values.forEach(function(value){
                value = String(value || '').trim();

                if (value === '') return;
                if (key === 'sort' && value === 'recommended') return;
                if (key === 'page' && value === '1') return;
                if ((key === 'price_min' || key === 'price_max') && (value === '0' || value === '')) return;

                params.append(key, value);
            });
        });

        return params;
    }

    function buildUrlFromForm(form, submitter){
        var action = form.getAttribute('action') || window.location.pathname;
        var url = new URL(action, window.location.origin);
        var formData;

        try {
            formData = submitter ? new FormData(form, submitter) : new FormData(form);
        } catch (e) {
            formData = new FormData(form);
            if (submitter && submitter.name) {
                formData.set(submitter.name, submitter.value || '');
            }
        }

        var params = new URLSearchParams(formData);
        cleanParams(params);
        url.search = params.toString();

        return url.toString();
    }

    function setLoading(isLoading){
        var container = getContainer();
        if (!container) return;

        container.classList.toggle('is-ajax-loading', !!isLoading);

        var controls = container.querySelectorAll('button, input, select, a');
        controls.forEach(function(el){
            if (isLoading) {
                el.setAttribute('data-ajax-was-disabled', el.disabled ? '1' : '0');
                if (el.tagName === 'BUTTON' || el.tagName === 'INPUT' || el.tagName === 'SELECT') {
                    el.disabled = true;
                }
            } else if (el.getAttribute('data-ajax-was-disabled') === '0') {
                el.disabled = false;
                el.removeAttribute('data-ajax-was-disabled');
            }
        });
    }

    function updateFromHtml(html){
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        var newContainer = doc.querySelector('.dg-shop-container');
        var container = getContainer();

        if (!newContainer || !container) {
            window.location.reload();
            return;
        }

        container.innerHTML = newContainer.innerHTML;
        bindShopEvents();
    }

    function ajaxLoad(url, push){
        if (!url) return;

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();
        setLoading(true);
        closeDropdowns(null);

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: activeRequest.signal
        })
        .then(function(response){
            if (!response.ok) throw new Error('Request failed');
            return response.text();
        })
        .then(function(html){
            updateFromHtml(html);

            if (push !== false) {
                window.history.pushState({dgAjaxShop: true}, '', url);
            }

            var top = document.getElementById('accountsTop');
            if (top) {
                top.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        })
        .catch(function(error){
            if (error && error.name === 'AbortError') return;
            window.location.href = url;
        })
        .finally(function(){
            activeRequest = null;
            setLoading(false);
        });
    }

    function bindDropdowns(){
        getPills().forEach(function(p){
            var btn = p.querySelector('button');
            var dd = document.getElementById(p.getAttribute('data-dropdown'));
            if (!btn || !dd) return;

            btn.addEventListener('click', function(e){
                e.preventDefault();

                var isOpen = dd.classList.contains('is-open');
                closeDropdowns(dd);
                dd.classList.toggle('is-open', !isOpen);
                p.classList.toggle('is-open', !isOpen);
            });
        });

        document.querySelectorAll('[data-close]').forEach(function(btn){
            btn.addEventListener('click', function(){
                var dd = document.getElementById(btn.getAttribute('data-close'));
                if (dd) dd.classList.remove('is-open');
                closeDropdowns(null);
            });
        });
    }

    function bindShopEvents(){
        var form = document.getElementById('shopFilters');

        bindDropdowns();

        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                ajaxLoad(buildUrlFromForm(form, e.submitter || document.activeElement), true);
            });

            document.querySelectorAll('.js-radio-submit').forEach(function(input){
                input.addEventListener('change', function(){
                    ajaxLoad(buildUrlFromForm(form), true);
                });
            });

            var search = document.getElementById('filterSearch');
            if (search) {
                search.addEventListener('input', function(){
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function(){
                        ajaxLoad(buildUrlFromForm(form), true);
                    }, 350);
                });

                search.addEventListener('keydown', function(e){
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(searchTimer);
                        ajaxLoad(buildUrlFromForm(form), true);
                    }
                });
            }
        }

        document.querySelectorAll('.reset-filters, .shop-pagination a').forEach(function(link){
            link.addEventListener('click', function(e){
                var href = link.getAttribute('href');
                if (!href || href === '#') return;

                e.preventDefault();
                ajaxLoad(new URL(href, window.location.origin).toString(), true);
            });
        });
    }

    document.addEventListener('click', function(e){
        if (!e.target.closest('[data-dropdown]')) closeDropdowns(null);
    });

    window.addEventListener('popstate', function(){
        ajaxLoad(window.location.href, false);
    });

    bindShopEvents();
})();
</script>

