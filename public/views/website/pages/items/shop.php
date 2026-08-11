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


if (!function_exists('item_shop_display_text')) {
    function item_shop_display_text($value, string $default = ''): string
    {
        $raw = ($value === null || $value === '') ? $default : (string)$value;
        return htmlspecialchars(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }
}
$items      = $items ?? $data ?? [];
$pagination = $pagination ?? ['page' => 1, 'totalPages' => 1, 'totalItems' => count($items)];
?>
<?php include_once __DIR__ . '/../../components/seller/seller-footer.php'; ?>

<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'ranked-accounts-page items-shop-page']) ?>

<?php

if (!function_exists('items_seller_rank_meta')) {
    function items_seller_rank_meta($rankName = '', $storedIcon = ''): array
    {
        $rankName = trim((string)$rankName);
        $storedIcon = trim((string)$storedIcon);
        $title = $rankName !== '' ? $rankName : 'Verified Seller';

        $iconClass = $storedIcon;
        if ($iconClass !== '' && preg_match('/class\s*=\s*["\']([^"\']+)["\']/i', $iconClass, $m)) {
            $iconClass = trim((string)$m[1]);
        }
        $iconClass = trim(strip_tags($iconClass));
        $iconClass = preg_replace('/\s+/', ' ', (string)$iconClass);
        $iconClass = trim((string)$iconClass);

        $color = '#94a3b8';
        if (stripos($storedIcon, 'text-emerald') !== false || strtolower($rankName) === 'expert seller') {
            $color = '#22c55e';
        } elseif (stripos($storedIcon, 'text-violet') !== false || strtolower($rankName) === 'pro seller') {
            $color = '#8b5cf6';
        } elseif (stripos($storedIcon, 'text-amber') !== false || strtolower($rankName) === 'mythic seller') {
            $color = '#fbbf24';
        } elseif (stripos($storedIcon, 'text-slate') !== false || strtolower($rankName) === 'beginner') {
            $color = '#94a3b8';
        }

        if ($iconClass !== '') {
            $iconClass = preg_replace('/\btext-[^\s]+\b/i', '', $iconClass);
            $iconClass = preg_replace('/\b(?:w|h|mr|ml|mt|mb|mx|my|p|px|py)-[^\s]+\b/i', '', $iconClass);
            $iconClass = preg_replace('/\s+/', ' ', (string)$iconClass);
            $iconClass = trim((string)$iconClass);
        }

        $looksLikeIconClass = (bool) preg_match('/\b(?:fa[srlbd]?|fa-[a-z0-9-]+|ri-[a-z0-9-]+|bi|bi-[a-z0-9-]+|ph|ph-[a-z0-9-]+|icon-[a-z0-9-]+)\b/i', $iconClass);
        if (!$looksLikeIconClass) {
            $iconClass = 'fa-solid fa-circle-check';
        } elseif (preg_match('/\bfa[a-z]?\b/i', $iconClass) && stripos($iconClass, 'fa-') === false) {
            $iconClass .= ' fa-circle-check';
        }

        return [
            'class' => $iconClass,
            'color' => $color,
            'title' => $title,
            'show'  => ($rankName !== '' || $storedIcon !== ''),
        ];
    }
}

/* ── Type helpers ──────────────────────────────────────────────────────── */
if (!function_exists('items_shop_type_label')) {
    function items_shop_type_label(string $type): string {
        $map = [
            'skins'         => 'Skins',        'skin'          => 'Skins',
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

/* ── All known types (fixed list so filter always shows all options) ─── */
$allTypes = [
    'skins'        => 'Skins',
    'chests-keys'  => 'Chests & Keys',
    'bundles'      => 'Bundles',
    'capsules'     => 'Capsules',
    'orbs'         => 'Orbs',
    'event-pass'   => 'Event Pass',
    'mystery-gift' => 'Mystery Gift',
    'tft-item'     => 'TFT Item',
];

/* ── Servers from actual items ─────────────────────────────────────────── */
$serverOrder = ['EUW','NA','ME','EUNE','BR','OCE','RU','TR','LAN','LAS','JP','VN','PH','SG','TH','TW'];
$serverSeen  = [];
foreach ($items as $item) {
    $srv = strtoupper(trim((string)($item['server'] ?? '')));
    if ($srv !== '' && in_array($srv, $serverOrder, true)) {
        $serverSeen[$srv] = true;
    }
}
$allServers = [];
foreach ($serverOrder as $srv) {
    if (isset($serverSeen[$srv])) {
        $allServers[] = $srv;
    }
}

/* ── Price range from items ────────────────────────────────────────────── */
$maxPrice = 0;
foreach ($items as $item) {
    $p = ((int)($item['price'] ?? 0)) / 100;
    if ($p > $maxPrice) $maxPrice = $p;
}
$maxPrice = max(50, (int)ceil($maxPrice));

$assetBase = rtrim(ASSET_URL, '/');

/* ── Currency helpers (same as item view) ──────────────────────────── */
if (!function_exists('shop_item_get_currency_format')) {
    function shop_item_get_currency_format(string $c): array {
        $c = strtoupper(trim($c));
        $dec = 2; $ds = '.'; $ts = ',';
        if (in_array($c, ['EUR','BRL','TRY'], true)) { $ds = ','; $ts = '.'; }
        return [$dec, $ds, $ts];
    }
    function shop_item_format_number(float $amount, string $c): string {
        [$dec, $ds, $ts] = shop_item_get_currency_format($c);
        return number_format($amount, $dec, $ds, $ts);
    }
    function shop_item_detect_rate(string $c): float {
        $c = strtoupper(trim($c));
        if ($c === 'EUR') return 1.0;
        $keys = ['exchange_rates','currency_rates','rates','fx_rates'];
        foreach ($keys as $k) {
            if (!isset($_SESSION[$k]) || !is_array($_SESSION[$k])) continue;
            foreach ($_SESSION[$k] as $key => $val) {
                if (!is_numeric($val) || (float)$val <= 0) continue;
                if (strtoupper(trim((string)$key)) === $c) return (float)$val;
            }
        }
        if (function_exists('get_exchange_rate')) { $r = (float)get_exchange_rate(); if ($r > 0) return $r; }
        return 1.0;
    }
    function shop_item_format_price(int $cents, string $c): string {
        $eur = $cents / 100;
        $rate = shop_item_detect_rate($c);
        $converted = round($eur * $rate, 2);
        return shop_item_format_number($converted, $c);
    }
}
$_shop_currency = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$_shop_symbol   = function_exists('util_format_currency_display')
    ? util_format_currency_display($_shop_currency)
    : ($_shop_currency === 'USD' ? '$' : '€');
?>

<header>
    <div class="content">
        <h1><?= htmlspecialchars($meta['h1'] ?? 'LoL Items Shop') ?></h1>
        <p><?= htmlspecialchars($meta['description'] ?? 'Buy League of Legends items, skins, capsules and more.') ?></p>
    </div>
</header>

<div class="container">
    <div id="itemsTop" style="height:1px;"></div>

    <!-- ══════════════════════════════════════════════════════════
         FILTERBAR  –  Option B: Tile-style, no dropdowns for type/server
         ══════════════════════════════════════════════════════════ -->
    <div class="items-filterbar" id="shopFilterbar">
        <form id="shopFilters">
            <input type="hidden" name="action" value="item_shop_filters">

            <!-- Row 1: Search + Price + Sort + Clear -->
            <div class="ifb-row ifb-row--top">
                <div class="ifb-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" id="filterSearch" placeholder="<?= t('Search items...') ?>">
                </div>

                <div class="ifb-spacer"></div>

                <!-- Price dropdown (only filter that needs one) -->
                <div class="ifb-price-wrap" id="priceWrap">
                    <button type="button" class="ifb-pill" id="btnPrice">
                        <i class="fa-solid fa-dollar-sign"></i>
                        <?= t('Price') ?>
                        <span class="ifb-pill-val" id="valPrice"></span>
                        <i class="fa-solid fa-caret-down ifb-caret"></i>
                    </button>
                    <div class="ifb-price-dropdown" id="priceDropdown">
                        <div class="ifb-price-fields">
                            <div class="ifb-price-field">
                                <label><?= $_shop_symbol ?></label>
                                <input type="number" name="price_min" id="priceMin" min="0" value="0" placeholder="Min">
                            </div>
                            <span class="ifb-price-sep">–</span>
                            <div class="ifb-price-field">
                                <label><?= $_shop_symbol ?></label>
                                <input type="number" name="price_max" id="priceMax" min="0" value="<?= $maxPrice ?>" placeholder="Max">
                            </div>
                        </div>
                        <div class="ifb-range-wrap">
                            <input type="range" id="priceRangeMin" min="0" max="<?= $maxPrice ?>" value="0" step="1">
                            <input type="range" id="priceRangeMax" min="0" max="<?= $maxPrice ?>" value="<?= $maxPrice ?>" step="1">
                            <div class="ifb-range-track"><div class="ifb-range-fill" id="priceFill"></div></div>
                        </div>
                        <div class="ifb-range-labels">
                            <span id="priceLabelMin"><?= $_shop_symbol ?>0</span>
                            <span id="priceLabelMax"><?= $_shop_symbol ?><?= $maxPrice ?></span>
                        </div>
                    </div>
                </div>

                <!-- Sort -->
                <div class="ifb-sort-wrap" id="sortWrap">
                    <button type="button" class="ifb-pill" id="btnSort">
                        <i class="fa-solid fa-arrow-up-wide-short"></i>
                        <span id="sortLabel"><?= t('Recommended') ?></span>
                        <i class="fa-solid fa-caret-down ifb-caret"></i>
                    </button>
                    <div class="ifb-sort-dropdown" id="sortDropdown">
                        <button type="button" class="ifb-sort-item" data-sort="recommended"><i class="fa-solid fa-wand-magic-sparkles"></i><?= t('Recommended') ?></button>
                        <button type="button" class="ifb-sort-item" data-sort="price_asc"><i class="fa-solid fa-tag"></i><?= t('Lowest Price') ?></button>
                        <button type="button" class="ifb-sort-item" data-sort="price_desc"><i class="fa-solid fa-sack-dollar"></i><?= t('Highest Price') ?></button>
                        <button type="button" class="ifb-sort-item" data-sort="stock_desc"><i class="fa-solid fa-box"></i><?= t('Highest Stock') ?></button>
                        <button type="button" class="ifb-sort-item" data-sort="sold_desc"><i class="fa-solid fa-fire"></i><?= t('Best Selling') ?></button>
                        <button type="button" class="ifb-sort-item" data-sort="newest"><i class="fa-regular fa-clock"></i><?= t('Newest') ?></button>
                        <button type="button" class="ifb-sort-item" data-sort="oldest"><i class="fa-solid fa-clock-rotate-left"></i><?= t('Oldest') ?></button>
                    </div>
                </div>

                <button type="button" class="ifb-clear" id="btnClear"><?= t('Clear All') ?></button>
            </div>

            <!-- Active filter chips (matches account shop "No filters applied" row) -->
            <div class="ifb-chips-row" id="activeFilters">
                <span class="ifb-chips-empty" id="ifbChipsEmpty"><?= t('No filters applied') ?></span>
            </div>

            <!-- Row 2: Most popular (type + server chips, matches account shop quick-chip row) -->
            <div class="ifb-row ifb-row--popular">
                <span class="ifb-row-label"><?= t('Most popular') ?>:</span>
                <div class="ifb-popular-chips">
                    <div class="ifb-type-tiles" id="typeTiles">
                        <?php foreach ($allTypes as $tKey => $tLabel):
                            $img = items_shop_type_img($tKey);
                            $fa  = items_shop_type_fa($tKey);
                        ?>
                        <button type="button"
                                class="ifb-type-tile"
                                data-type="<?= htmlspecialchars($tKey) ?>"
                                title="<?= htmlspecialchars($tLabel) ?>">
                            <?php if ($img): ?>
                                <img src="<?= htmlspecialchars($img) ?>" alt="" class="ifb-type-img">
                            <?php else: ?>
                                <i class="<?= htmlspecialchars($fa) ?> ifb-type-fa"></i>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($tLabel) ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($allServers)): ?>
                    <div class="ifb-server-badges" id="serverBadges">
                        <?php foreach ($allServers as $srv): ?>
                        <button type="button"
                                class="ifb-server-badge"
                                data-server="<?= htmlspecialchars($srv) ?>">
                            <?= htmlspecialchars($srv) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hidden inputs written by JS before AJAX -->
            <div id="hiddenFilters" style="display:none;"></div>
        </form>
    </div>

    <?= $this->insert('website/components/accounts/shop-filter-nav') ?>

    <!-- Toolbar -->
    <div class="shop-toolbar">
        <div class="shop-toolbar__left">
            <span class="shop-count">
                <span id="itemsCountShown">0</span><span class="shop-count__sep">/</span><span id="itemsCountTotal"><?= (int)($pagination['totalItems'] ?? 0) ?></span> <?= t('Items') ?>
            </span>
        </div>
    </div>

    <!-- Grid -->
    <div class="items-grid-fixed" id="itemsGrid">
        <?php foreach ($items as $item):
            $images    = json_decode((string)($item['images'] ?? '[]'), true);
            if (!is_array($images)) $images = [];
            $cover     = $images[0] ?? ($assetBase . '/public/uploads/icons/default2.png');
            $slugOrId  = !empty($item['slug']) ? $item['slug'] : (string)(int)$item['id'];
            $priceEur  = ((int)($item['price'] ?? 0)) / 100;
            $stock     = (int)($item['stock'] ?? 1);
            $sellerTotalSales = lb_db_seller_total_sales(
                (int)($item['seller_id'] ?? 0),
                (int)($item['seller_total_sales'] ?? $item['total_sales'] ?? $item['total_sold'] ?? $item['seller_sold'] ?? 0)
            );
            $sellerName = $item['seller_username'] ?? 'Seller';
            $sellerSlug = $item['seller_slug'] ?? $sellerName;
            $sellerLink = BASE_URL . '/sellers/' . urlencode((string)$sellerSlug);
            $sellerIcon = $item['seller_icon'] ?? (ICON_URL . '/default.png');
            $sellerRank = (string)($item['seller_rank'] ?? '');
            $sellerRankIcon = (string)($item['seller_rank_icon'] ?? '');
            $sellerRankMeta = items_seller_rank_meta($sellerRank, $sellerRankIcon);
            $typeLabel  = items_shop_type_label($item['type'] ?? '');
            $typeImg    = items_shop_type_img($item['type'] ?? '');
            $typeFa     = items_shop_type_fa($item['type'] ?? '');
            $serverRaw  = (string)($item['server'] ?? 'EUW');
            $server     = function_exists('util_format_server_code') ? util_format_server_code($serverRaw) : strtoupper($serverRaw);
        ?>
        <article class="item-shop-card">
            <div class="item-shop-card__body">
                <div class="item-shop-card__top">
                    <div class="item-shop-card__info">
                        <a href="<?= BASE_URL ?>/lol/item/<?= urlencode($slugOrId) ?>" class="item-shop-card__title">
                            <?= item_shop_display_text($item['title'] ?? null, 'Untitled') ?>
                        </a>
                        <div class="item-shop-badges">
                            <span class="item-shop-badge item-shop-badge--type">
                                <?php if ($typeImg): ?><img class="item-type-img" src="<?= htmlspecialchars($typeImg) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($typeFa) ?>"></i><?php endif; ?>
                                <?= htmlspecialchars($typeLabel) ?>
                            </span>
                            <span class="item-shop-badge item-shop-badge--stock"><i class="fa-solid fa-box"></i><?= $stock ?> <?= t('in stock') ?></span>
                            <span class="item-shop-badge item-shop-badge--server"><i class="fa-solid fa-globe"></i><?= htmlspecialchars($server) ?></span>
                        </div>
                    </div>
                    <a class="item-shop-card__img" href="<?= BASE_URL ?>/lol/item/<?= urlencode($slugOrId) ?>">
                        <img src="<?= htmlspecialchars($cover) ?>" alt="<?= item_shop_display_text($item['title'] ?? null, 'Item') ?>" loading="lazy">
                    </a>
                </div>
                <div class="item-shop-bottom">
                    <div class="item-shop-price"><?= $_shop_symbol ?><?= shop_item_format_price((int)($item['price'] ?? 0), $_shop_currency) ?> <small>/ <?= t('Unit') ?></small></div>
                    <a class="item-shop-btn" href="<?= BASE_URL ?>/lol/item/<?= urlencode($slugOrId) ?>"><?= t('Buy Now') ?> <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="item-shop-seller">
                    <a class="item-shop-seller__left" href="<?= htmlspecialchars($sellerLink) ?>">
                        <img src="<?= htmlspecialchars($sellerIcon) ?>" alt="<?= htmlspecialchars($sellerName) ?>">
                        <div class="item-shop-seller__namewrap">
                            <div class="item-shop-seller__name"><?= htmlspecialchars($sellerName) ?></div>
                            <?php if (!empty($sellerRankMeta['show'])): ?>
                                <i class="<?= htmlspecialchars($sellerRankMeta['class'], ENT_QUOTES) ?> item-shop-seller__rank"
                                   style="color:<?= htmlspecialchars($sellerRankMeta['color'], ENT_QUOTES) ?>;"
                                   title="<?= htmlspecialchars($sellerRankMeta['title'], ENT_QUOTES) ?>"></i>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="item-shop-seller__stats"><?= number_format($sellerTotalSales) ?> <?= t('Sold') ?></div>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- Empty state -->
    <div class="shop-empty" id="shopEmpty" style="display:none;">
        <div class="shop-empty__inner">
            <div class="shop-empty__emoji">🥺</div>
            <div class="shop-empty__title"><?= t('No items match your search') ?></div>
            <div class="shop-empty__text"><?= t('Try adjusting the filters or contact us.') ?></div>
            <div class="shop-empty__actions">
                <button type="button" class="shop-empty__btn shop-empty__btn--primary" id="btnTalkToAgent"><i class="fa-regular fa-comment-dots"></i><span><?= t('Talk to Agent') ?></span></button>
                <button type="button" class="shop-empty__btn shop-empty__btn--ghost" id="btnResetFiltersEmpty"><i class="fa-solid fa-filter-circle-xmark"></i><span><?= t('Reset Filters') ?></span></button>
            </div>
        </div>
    </div>

    <div class="shop-pagination" id="shopPagination"></div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).ready(function () {

    const AJAX_URL_VAL = (typeof ajax_url !== 'undefined') ? ajax_url : '<?= AJAX_URL ?>';
    const ASSET_BASE   = <?= json_encode(rtrim(ASSET_URL, '/')) ?>;

    /* ── State ──────────────────────────────────────────────────────── */
    let sortMode    = 'recommended';
    let currentPage = <?= (int)($pagination['page']    ?? 1) ?>;
    let totalPages  = <?= (int)($pagination['totalPages'] ?? 1) ?>;
    let totalItems  = <?= (int)($pagination['totalItems'] ?? 0) ?>;
    const currencySymbol = <?= json_encode($_shop_symbol) ?>;
    let priceLimitMin = 0, priceLimitMax = <?= (int)$maxPrice ?>;
    let priceTouched  = false;
    let priceLimitsInitialized = false;
    let requestSeq = 0, isLoading = false;
    let filterTimer = null, searchTimer = null;
    let pendingScrollToTop = false;

    /* ── Selected sets ──────────────────────────────────────────────── */
    const selectedTypes   = new Set();
    const selectedServers = new Set();

    /* ── DOM refs ───────────────────────────────────────────────────── */
    const $grid      = $('#itemsGrid');
    const $cs        = $('#itemsCountShown');
    const $ct        = $('#itemsCountTotal');
    const $pMin      = $('#priceMin');
    const $pMax      = $('#priceMax');
    const $rMin      = $('#priceRangeMin');
    const $rMax      = $('#priceRangeMax');
    const $fill      = $('#priceFill');

    /* ════════════════════════════════════════════════════════════════
       ACTIVE FILTER CHIPS (mirrors account shop "No filters applied" row)
    ════════════════════════════════════════════════════════════════ */
    function renderActiveChips() {
        const $row = $('#activeFilters');
        const chips = [];

        selectedTypes.forEach(t => chips.push({ label: $('.ifb-type-tile[data-type="' + t + '"]').attr('title') || t, remove: () => { selectedTypes.delete(t); $('.ifb-type-tile[data-type="' + t + '"]').removeClass('is-active'); } }));
        selectedServers.forEach(s => chips.push({ label: s, remove: () => { selectedServers.delete(s); $('.ifb-server-badge[data-server="' + s + '"]').removeClass('is-active'); } }));
        const search = ($('#filterSearch').val() || '').trim();
        if (search) chips.push({ label: '"' + search + '"', remove: () => { $('#filterSearch').val(''); } });
        if (priceTouched) {
            const lo = $pMin.val() || 0, hi = $pMax.val() || priceLimitMax;
            chips.push({ label: currencySymbol + lo + ' – ' + currencySymbol + hi, remove: () => { priceTouched = false; $pMin.val(priceLimitMin); $pMax.val(priceLimitMax); $rMin.val(priceLimitMin); $rMax.val(priceLimitMax); clampPrice(); } });
        }

        if (!chips.length) {
            $row.html('<span class="ifb-chips-empty" id="ifbChipsEmpty"><?= t('No filters applied') ?></span>');
            return;
        }

        $row.empty();
        chips.forEach((c, i) => {
            const $chip = $('<span class="ifb-chip"></span>').text(c.label);
            const $x = $('<span class="ifb-chip-x">✕</span>').on('click', function () { c.remove(); renderActiveChips(); triggerFiltersDebounced(); });
            $chip.append($x);
            $row.append($chip);
        });
    }

    /* ════════════════════════════════════════════════════════════════
       TILE TOGGLES
    ════════════════════════════════════════════════════════════════ */
    $(document).on('click', '.ifb-type-tile', function () {
        const type = $(this).data('type');
        if (selectedTypes.has(type)) {
            selectedTypes.delete(type);
            $(this).removeClass('is-active');
        } else {
            selectedTypes.add(type);
            $(this).addClass('is-active');
        }
        renderActiveChips();
        triggerFiltersDebounced();
    });

    $(document).on('click', '.ifb-server-badge', function () {
        const srv = $(this).data('server');
        if (selectedServers.has(srv)) {
            selectedServers.delete(srv);
            $(this).removeClass('is-active');
        } else {
            selectedServers.add(srv);
            $(this).addClass('is-active');
        }
        renderActiveChips();
        triggerFiltersDebounced();
    });

    function refreshItemPopularAvailability() {
        const $pills = $('.ifb-type-tile, .ifb-server-badge');
        const $wrap = $('.ifb-row--popular');
        if (!$pills.length || !$wrap.length) return;
        const checks = [];
        $pills.each(function () {
            checks.push({
                kind: $(this).hasClass('ifb-type-tile') ? 'type' : 'server',
                value: String($(this).data('type') || $(this).data('server') || '')
            });
            $(this).hide();
        });
        $wrap.hide();
        $.ajax({
            url: (typeof ajax_url !== 'undefined' ? ajax_url : '<?= AJAX_URL ?>'),
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'shop_popular_filter_counts',
                entity: 'items',
                game: String($('#shopFilters input[name="game"]').val() || 'league-of-legends'),
                game_id: String($('#shopFilters input[name="game_id"]').val() || '0'),
                checks: checks
            }
        }).done(function (payload) {
            let visible = 0;
            $pills.each(function (index) {
                const show = !!(payload && payload.success && parseInt(payload.counts?.[index] || 0, 10) > 0);
                $(this).toggle(show);
                if (show) visible++;
            });
            $wrap.toggle(visible > 0);
        });
    }

    /* ════════════════════════════════════════════════════════════════
       PRICE DROPDOWN (only dropdown left)
    ════════════════════════════════════════════════════════════════ */
    function closePriceDropdown() { $('#priceDropdown').removeClass('is-open'); $('#priceWrap').removeClass('is-open'); }
    function closeSortDropdown()  { $('#sortDropdown').removeClass('is-open'); $('#sortWrap').removeClass('is-open'); }
    function closeAllDropdowns()  { closePriceDropdown(); closeSortDropdown(); }

    $('#btnPrice').on('click', function (e) {
        e.stopPropagation();
        const isOpen = $('#priceDropdown').hasClass('is-open');
        closeAllDropdowns();
        if (!isOpen) { $('#priceDropdown').addClass('is-open'); $('#priceWrap').addClass('is-open'); }
    });

    $('#btnSort').on('click', function (e) {
        e.stopPropagation();
        const isOpen = $('#sortDropdown').hasClass('is-open');
        closeAllDropdowns();
        if (!isOpen) { $('#sortDropdown').addClass('is-open'); $('#sortWrap').addClass('is-open'); }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#priceWrap, #sortWrap').length) closeAllDropdowns();
    });

    /* ── Price dual range ─────────────────────────────────────────── */
    function clampPrice() {
        let lo = parseInt($rMin.val() || 0, 10), hi = parseInt($rMax.val() || 0, 10);
        if (lo > hi - 1) lo = hi - 1;
        if (hi < lo + 1) hi = lo + 1;
        $rMin.val(lo); $rMax.val(hi); $pMin.val(lo); $pMax.val(hi);
        $('#priceLabelMin').text(currencySymbol + lo);
        $('#priceLabelMax').text(currencySymbol + hi);
        const mn = parseInt($rMin.attr('min') || 0, 10), mx = parseInt($rMin.attr('max') || priceLimitMax, 10);
        const l = ((lo - mn) / (mx - mn)) * 100, r = ((hi - mn) / (mx - mn)) * 100;
        $fill.css({ left: l + '%', width: (r - l) + '%' });
        // update pill label
        const changed = lo !== priceLimitMin || hi !== priceLimitMax;
        $('#valPrice').text(changed ? currencySymbol + lo + ' – ' + currencySymbol + hi : '');
    }

    function setActiveThumb(w) {
        const a = document.getElementById('priceRangeMin'), b = document.getElementById('priceRangeMax');
        if (!a || !b) return;
        a.style.zIndex = w === 'min' ? '6' : '5';
        b.style.zIndex = w === 'max' ? '6' : '5';
    }
    $('#priceRangeMin').on('pointerdown', () => setActiveThumb('min'));
    $('#priceRangeMax').on('pointerdown', () => setActiveThumb('max'));

    $(document).on('input', '#priceRangeMin, #priceRangeMax', function () { clampPrice(); priceTouched = true; });
    $(document).on('change', '#priceRangeMin, #priceRangeMax', function () { clampPrice(); priceTouched = true; renderActiveChips(); triggerFiltersDebounced(); });
    $(document).on('change', '#priceMin, #priceMax', function () {
        const lo = parseInt($pMin.val() || 0, 10), hi = parseInt($pMax.val() || priceLimitMax, 10);
        const mn = parseInt($rMin.attr('min') || 0, 10), mx = parseInt($rMin.attr('max') || priceLimitMax, 10);
        $rMin.val(Math.max(mn, Math.min(lo, mx))); $rMax.val(Math.max(mn, Math.min(hi, mx)));
        clampPrice(); renderActiveChips(); triggerFiltersDebounced();
    });
    clampPrice();

    /* ════════════════════════════════════════════════════════════════
       SORT
    ════════════════════════════════════════════════════════════════ */
    $(document).on('click', '.ifb-sort-item', function (e) {
        e.preventDefault();
        sortMode = $(this).data('sort') || 'recommended';
        $('#sortLabel').text($(this).text().trim());
        closeSortDropdown();
        resetToFirstPage();
        fetchItems({ page: 1 });
    });

    /* ════════════════════════════════════════════════════════════════
       SEARCH
    ════════════════════════════════════════════════════════════════ */
    $('#filterSearch').on('input', function () {
        renderActiveChips();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { resetToFirstPage(); fetchItems({ page: 1 }); updateUrl(); }, 280);
    });

    /* ════════════════════════════════════════════════════════════════
       CLEAR ALL
    ════════════════════════════════════════════════════════════════ */
    function clearAll() {
        selectedTypes.clear(); selectedServers.clear();
        $('.ifb-type-tile, .ifb-server-badge').removeClass('is-active');
        $('#filterSearch').val('');
        $pMin.val(0); $pMax.val(priceLimitMax); $rMin.val(0); $rMax.val(priceLimitMax);
        priceTouched = false; sortMode = 'recommended'; $('#sortLabel').text('<?= t('Recommended') ?>');
        clampPrice(); renderActiveChips(); resetToFirstPage(); fetchItems({ page: 1 });
    }
    $('#btnClear').on('click', clearAll);
    $('#btnResetFiltersEmpty').on('click', clearAll);
    $('#btnTalkToAgent').on('click', function () {
        if (window.Tawk_API && typeof window.Tawk_API.maximize === 'function') window.Tawk_API.maximize();
    });

    /* ════════════════════════════════════════════════════════════════
       BUILD POST DATA
    ════════════════════════════════════════════════════════════════ */
    function buildPostData(page) {
        const params = new URLSearchParams();
        params.set('action', 'item_shop_filters');
        params.set('page', String(page || 1));
        params.set('sort', sortMode);
        const search = ($('#filterSearch').val() || '').trim();
        if (search) params.set('search', search);
        selectedTypes.forEach(t => params.append('types[]', t));
        selectedServers.forEach(s => params.append('servers[]', s));
        if (priceTouched) {
            params.set('price_min', $pMin.val() || '0');
            params.set('price_max', $pMax.val() || String(priceLimitMax));
        }
        return params.toString();
    }

    /* ════════════════════════════════════════════════════════════════
       URL SYNC
    ════════════════════════════════════════════════════════════════ */
    function updateUrl() {
        const params = new URLSearchParams();
        const search = ($('#filterSearch').val() || '').trim();
        if (search) params.set('search', search);
        selectedTypes.forEach(t => params.append('type', t));
        selectedServers.forEach(s => params.append('server', s));
        if (priceTouched) { params.set('min', $pMin.val() || '0'); params.set('max', $pMax.val() || String(priceLimitMax)); }
        const sortMap = { price_asc: 'price', price_desc: '-price', newest: 'newest', oldest: 'oldest', stock_desc: 'stock', sold_desc: 'sold' };
        if (sortMap[sortMode]) params.set('sort', sortMap[sortMode]);
        if (currentPage > 1) params.set('page', String(currentPage));
        window.history.replaceState({}, '', window.location.pathname + (params.toString() ? ('?' + params.toString()) : ''));
    }

    function applyStateFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const search = params.get('search') || ''; if (search) $('#filterSearch').val(search);
        params.getAll('type').forEach(t => { selectedTypes.add(t); $('.ifb-type-tile[data-type="' + t + '"]').addClass('is-active'); });
        params.getAll('server').forEach(s => { selectedServers.add(s); $('.ifb-server-badge[data-server="' + s + '"]').addClass('is-active'); });
        const sortRevMap = { price: 'price_asc', '-price': 'price_desc', newest: 'newest', oldest: 'oldest', stock: 'stock_desc', sold: 'sold_desc' };
        const sp = params.get('sort') || ''; sortMode = sortRevMap[sp] || 'recommended';
        const lm = { recommended: '<?= t('Recommended') ?>', price_asc: '<?= t('Lowest Price') ?>', price_desc: '<?= t('Highest Price') ?>', stock_desc: '<?= t('Highest Stock') ?>', sold_desc: '<?= t('Best Selling') ?>', newest: '<?= t('Newest') ?>', oldest: '<?= t('Oldest') ?>' };
        $('#sortLabel').text(lm[sortMode] || '<?= t('Recommended') ?>');
        currentPage = Math.max(1, parseInt(params.get('page') || '1', 10) || 1);
        const minP = params.get('min'), maxP = params.get('max');
        if (minP !== null || maxP !== null) {
            priceTouched = true;
            if (minP !== null) $pMin.val(parseFloat(minP));
            if (maxP !== null) $pMax.val(parseFloat(maxP));
            const mn = parseInt($rMin.attr('min') || 0, 10), mx = parseInt($rMin.attr('max') || priceLimitMax, 10);
            $rMin.val(Math.max(mn, Math.min(parseInt($pMin.val()), mx)));
            $rMax.val(Math.max(mn, Math.min(parseInt($pMax.val()), mx)));
            clampPrice();
        }
    }

    /* ════════════════════════════════════════════════════════════════
       PAGINATION
    ════════════════════════════════════════════════════════════════ */
    function resetToFirstPage() { currentPage = 1; }

var ITEMS_FILTERBAR_LIFT_UP = 30;

function getItemsFilterbarFixedTopOffset() {
    const zoomRaw = parseFloat(getComputedStyle(document.documentElement).zoom || '1');
    const zoom = Number.isFinite(zoomRaw) && zoomRaw > 0 ? zoomRaw : 1;
    const selectors = [
        '#lbSaleBanner',
        '#lbGiveawayBanner',
        '.navbar-top',
        '.navbar-mobile',
        '.lb-game-subnav',
        '.lb-mobile-gamebar'
    ];
    let bottom = 0;

    selectors.forEach(function (selector) {
        const el = document.querySelector(selector);
        if (!el) return;

        const cs = getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden') return;

        const rect = el.getBoundingClientRect();
        if (rect.width <= 0 || rect.height <= 0) return;

        bottom = Math.max(bottom, rect.bottom / zoom);
    });

    return bottom || 90;
}

function scrollToShopFilterbar(behavior) {
    const target = document.getElementById('shopFilterbar') || document.querySelector('.items-filterbar') || document.getElementById('itemsTop');
    if (!target) return;

    const offset = getItemsFilterbarFixedTopOffset();
    const y = target.getBoundingClientRect().top + window.scrollY - offset - ITEMS_FILTERBAR_LIFT_UP;
    window.scrollTo({ top: Math.max(0, y), behavior: behavior || 'instant' });
}

function scrollToTop() {
    scrollToShopFilterbar('smooth');
}

    function buildPageBar(page, total) {
        const btns = [];
        const push = (p, l, c = '') => btns.push(`<button type="button" class="page-btn ${c}" data-page="${p}">${l}</button>`);
        push(Math.max(1, page - 1), '&laquo; Prev', page === 1 ? 'is-disabled is-nav' : 'is-nav');
        if (total <= 7) { for (let i = 1; i <= total; i++) push(i, i, i === page ? 'is-active' : ''); }
        else {
            const s = Math.max(2, page - 2), e = Math.min(total - 1, page + 2);
            push(1, 1, page === 1 ? 'is-active' : '');
            if (s > 2) btns.push('<span class="page-ellipsis">…</span>');
            for (let i = s; i <= e; i++) push(i, i, i === page ? 'is-active' : '');
            if (e < total - 1) btns.push('<span class="page-ellipsis">…</span>');
            push(total, total, page === total ? 'is-active' : '');
        }
        push(Math.min(total, page + 1), 'Next &raquo;', page === total ? 'is-disabled is-nav' : 'is-nav');
        return `<div class="page-bar">${btns.join('')}</div>`;
    }

    function renderPagination() {
        const $w = $('#shopPagination');
        if (!$w.length) return;
        if (!totalPages || totalPages <= 1) { $w.empty(); return; }
        $w.html(buildPageBar(currentPage, totalPages));
    }

    $(document).on('click', '#shopPagination .page-btn', function () {
        if ($(this).hasClass('is-disabled') || $(this).hasClass('is-active')) return;
        const p = parseInt($(this).data('page'), 10) || 1;
        closeAllDropdowns(); pendingScrollToTop = true; fetchItems({ page: p });
    });

    /* ════════════════════════════════════════════════════════════════
       AJAX FETCH
    ════════════════════════════════════════════════════════════════ */
    function fetchItems({ page = 1 } = {}) {
        requestSeq++; const mySeq = requestSeq;
        isLoading = true; $('#shopFilterbar').addClass('is-loading');

        $.ajax({
            url: AJAX_URL_VAL, type: 'POST',
            data: buildPostData(page),
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            dataType: 'json',
            success: function (payload) {
                if (mySeq !== requestSeq || !payload) return;
                if (payload.html !== undefined) $grid.html(payload.html);
                currentPage  = payload.page       || page || 1;
                totalPages   = payload.totalPages  || 1;
                totalItems   = payload.totalItems  || 0;

                if (totalItems === 0) { $('#shopEmpty').show(); $grid.hide(); $('#shopPagination').hide(); }
                else { $('#shopEmpty').hide(); $grid.show(); $('#shopPagination').show(); }

                if (payload.priceRange && !priceLimitsInitialized) {
                    setPriceLimits(payload.priceRange.min, payload.priceRange.max);
                    priceLimitsInitialized = true;
                }

                $cs.text($grid.find('.item-shop-card').length);
                $ct.text(totalItems);
                renderPagination();
                updateUrl();
                if (pendingScrollToTop) { pendingScrollToTop = false; scrollToTop(); }
            },
            complete: function () { if (mySeq !== requestSeq) return; isLoading = false; $('#shopFilterbar').removeClass('is-loading'); },
            error:    function () { if (mySeq !== requestSeq) return; isLoading = false; $('#shopFilterbar').removeClass('is-loading'); }
        });
    }

    function setPriceLimits(minEur, maxEur) {
        const mn = Math.max(0, Math.floor(minEur || 0)), mx = Math.max(mn + 1, Math.ceil(maxEur || 0));
        priceLimitMin = mn; priceLimitMax = mx;
        $pMin.attr('min', mn).attr('max', mx); $pMax.attr('min', mn).attr('max', mx);
        $rMin.attr('min', mn).attr('max', mx); $rMax.attr('min', mn).attr('max', mx);
        if (!priceTouched) { $pMin.val(mn); $pMax.val(mx); $rMin.val(mn); $rMax.val(mx); }
        clampPrice();
    }

    function triggerFiltersDebounced() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function () { resetToFirstPage(); fetchItems({ page: 1 }); updateUrl(); }, 220);
    }

    /* ── Seller name links ──────────────────────────────────────── */
    (function () {
        function process() {
            document.querySelectorAll('.item-shop-seller__name').forEach(function (el) {
                if (el.dataset.linked) return; el.dataset.linked = '1';
                const u = el.textContent.trim(); if (!u) return;
                el.style.cursor = 'pointer';
                el.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); window.location.href = '/sellers/' + encodeURIComponent(u); });
                el.addEventListener('mouseenter', function () { el.style.color = '#a5b4fc'; });
                el.addEventListener('mouseleave', function () { el.style.color = ''; });
            });
        }
        process();
        const g = document.getElementById('itemsGrid');
        if (g) new MutationObserver(process).observe(g, { childList: true, subtree: true });
    })();

    /* ── Init ───────────────────────────────────────────────────── */
    applyStateFromUrl();
    renderActiveChips();
    $cs.text($grid.find('.item-shop-card').length);
    $ct.text(totalItems);
    renderPagination();
    refreshItemPopularAvailability();
    fetchItems({ page: currentPage || 1 });

    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    scrollToShopFilterbar('instant');
    setTimeout(function () { scrollToShopFilterbar('instant'); }, 100);
    setTimeout(function () { scrollToShopFilterbar('instant'); }, 400);
    setTimeout(function () { scrollToShopFilterbar('instant'); }, 900);
    window.addEventListener('load', function () {
        setTimeout(function () { scrollToShopFilterbar('instant'); }, 50);
    });
});
</script>
<?= $this->stop() ?>

<?= $this->start('styles') ?>
<style>
/* ══ FILTERBAR ══════════════════════════════════════════════════════════ */
/* Exact values pulled from the account shop's #shopFilterbar override. */
.items-filterbar{
    background:rgba(10,12,27,.92);
    border:1px solid rgba(255,255,255,.07);
    border-radius:20px;
    padding:14px;
    display:flex;flex-direction:column;gap:14px;
    margin-bottom:24px;
    margin-top:22px;
    position:static;
    top:auto;
    z-index:1;
    box-shadow:none;
}
.items-filterbar.is-loading{opacity:.7;pointer-events:none;}

/* Rows */
.ifb-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.ifb-row--popular{padding-top:2px;}
.ifb-row-label{font-size:12px;color:rgba(255,255,255,.45);white-space:nowrap;min-width:46px;}
.ifb-popular-chips{display:flex;flex-wrap:wrap;gap:8px;flex:1;}

/* Active filter chips row — mirrors the account shop's "No filters applied" strip */
.ifb-chips-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-height:20px;}
.ifb-chips-empty{font-size:13px;color:rgba(255,255,255,.4);}
.ifb-chip{display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:#151827;border:1px solid rgba(255,255,255,.075);color:rgba(255,255,255,.92);font-size:12px;font-weight:600;white-space:nowrap;}
.ifb-chip:hover{border-color:rgba(124,146,255,.30);}
.ifb-chip-x{opacity:.85;cursor:pointer;font-size:11px;}
.ifb-chip-x:hover{opacity:1;}

/* Search */
.ifb-search{display:flex;align-items:center;gap:9px;background:#151827;border:1px solid rgba(255,255,255,.075);border-radius:13px;padding:0 14px;flex:1;max-width:340px;height:46px;}
.ifb-search i{color:rgba(255,255,255,.5);font-size:13px;}
.ifb-search input{background:none;border:none;outline:none;color:rgba(255,255,255,.92);font-size:14px;width:100%;}
.ifb-search input::placeholder{color:rgba(255,255,255,.35);}

.ifb-spacer{flex:1;}

/* Shared pill (Price + Sort) */
.ifb-pill{display:inline-flex;align-items:center;gap:7px;height:46px;padding:0 14px;border-radius:13px;border:1px solid rgba(255,255,255,.075);background:#151827;color:rgba(255,255,255,.92);font-size:13px;cursor:pointer;white-space:nowrap;position:relative;}
.ifb-pill:hover{border-color:rgba(255,255,255,.14);}
.ifb-pill-val{color:#a5b4fc;font-size:12px;font-weight:700;}
.ifb-caret{font-size:10px;opacity:.6;}
#priceWrap.is-open .ifb-pill,
#sortWrap.is-open .ifb-pill{background:#11162d;border-color:rgba(124,146,255,.34);}

/* Clear */
.ifb-clear{height:46px;padding:0 18px;border-radius:13px;border:none;background:#6366f1;color:#fff;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;}
.ifb-clear:hover{opacity:.88;}

/* ── TYPE TILES (chip-style, matches account shop "Most popular" pills) ── */
.ifb-type-tiles{display:flex;flex-wrap:wrap;gap:8px;}
.ifb-type-tile{
    display:inline-flex;align-items:center;gap:7px;
    padding:8px 14px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.065);
    background:#151827;
    color:#c0c7dc;
    font-size:12.5px;font-weight:700;
    cursor:pointer;
    transition:border-color .15s,background .15s,color .15s;
    white-space:nowrap;
}
.ifb-type-tile:hover{border-color:rgba(124,146,255,.30);background:#10142a;color:#fff;}
.ifb-type-tile.is-active{border-color:rgba(124,146,255,.34);background:#11162d;color:#9eabff;}
.ifb-type-img{width:16px;height:16px;object-fit:contain;border-radius:3px;flex-shrink:0;display:block;}
.ifb-type-fa{font-size:12px;display:block;}

/* ── SERVER BADGES (same pill treatment) ─────────────────────────────── */
.ifb-server-badges{display:flex;flex-wrap:wrap;gap:8px;}
.ifb-server-badge{
    display:inline-flex;align-items:center;justify-content:center;
    padding:8px 14px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.065);
    background:#151827;
    color:#c0c7dc;
    font-size:12.5px;font-weight:700;
    cursor:pointer;
    transition:border-color .15s,background .15s,color .15s;
}
.ifb-server-badge:hover{border-color:rgba(124,146,255,.30);background:#10142a;color:#fff;}
.ifb-server-badge.is-active{border-color:rgba(124,146,255,.34);background:#11162d;color:#9eabff;}

/* ── PRICE DROPDOWN ──────────────────────────────────────────────────── */
.ifb-price-wrap,.ifb-sort-wrap{position:relative;}
.ifb-price-dropdown,.ifb-sort-dropdown{
    display:none;
    position:absolute;top:calc(100% + 10px);right:0;
    background:#0d1021;
    border:1px solid rgba(255,255,255,.075);
    border-radius:14px;
    padding:14px 16px;
    z-index:200;
    min-width:260px;
    box-shadow:0 18px 60px rgba(0,0,0,.45);
}
.ifb-price-dropdown.is-open,.ifb-sort-dropdown.is-open{display:block;}

.ifb-price-fields{display:flex;align-items:center;gap:10px;margin-bottom:16px;}
.ifb-price-field{display:flex;align-items:center;gap:6px;flex:1;}
.ifb-price-field label{font-size:14px;color:rgba(255,255,255,.5);}
.ifb-price-field input{background:#151827;border:1px solid rgba(255,255,255,.075);border-radius:8px;color:rgba(255,255,255,.92);font-size:14px;padding:6px 10px;width:100%;outline:none;}
.ifb-price-sep{color:rgba(255,255,255,.4);font-size:14px;}

/* Range */
.ifb-range-wrap{position:relative;height:36px;display:flex;align-items:center;}
.ifb-range-wrap input[type=range]{position:absolute;width:100%;height:4px;appearance:none;-webkit-appearance:none;background:transparent;pointer-events:none;z-index:3;}
.ifb-range-wrap input[type=range]::-webkit-slider-thumb{appearance:none;-webkit-appearance:none;width:18px;height:18px;border-radius:50%;background:#6366f1;border:2px solid #fff;cursor:pointer;pointer-events:all;}
.ifb-range-track{position:absolute;left:0;right:0;height:4px;background:rgba(255,255,255,.12);border-radius:2px;z-index:1;}
.ifb-range-fill{position:absolute;height:100%;background:#6366f1;border-radius:2px;}
.ifb-range-labels{display:flex;justify-content:space-between;font-size:12px;color:rgba(255,255,255,.5);margin-top:4px;}

/* Sort dropdown */
.ifb-sort-dropdown{min-width:200px;padding:8px;}
.ifb-sort-item{display:flex;align-items:center;gap:10px;width:100%;padding:9px 12px;border-radius:10px;border:none;background:none;color:rgba(255,255,255,.8);font-size:14px;cursor:pointer;text-align:left;}
.ifb-sort-item:hover{background:rgba(255,255,255,.06);color:#fff;}
.ifb-sort-item i{font-size:13px;width:16px;text-align:center;color:rgba(255,255,255,.5);}

@media(max-width:540px){
    .ifb-price-dropdown{
        left:0;
        right:auto;
        min-width:0;
        width:min(260px, calc(100vw - 48px));
        max-width:calc(100vw - 48px);
        padding:16px;
    }
    .ifb-sort-dropdown{
        right:0;
        left:auto;
        min-width:min(200px, calc(100vw - 48px));
        max-width:calc(100vw - 48px);
    }
}

/* ══ ITEM GRID & CARDS ══════════════════════════════════════════════════ */
.items-grid-fixed{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;}
.item-shop-card{border-radius:18px;border:1px solid rgba(255,255,255,.075);overflow:hidden;background:linear-gradient(180deg, rgba(14,17,33,.98) 0%, rgba(10,12,24,.99) 100%);display:flex;flex-direction:column;transition:.18s ease;}
.item-shop-card:hover{transform:translateY(-2px);border-color:rgba(99,102,241,.28);}

/* ── Card head: title left, thumbnail right ── */
.item-shop-card__top{display:flex;align-items:flex-start;gap:18px;}
.item-shop-card__info{flex:1;min-width:0;display:flex;flex-direction:column;gap:12px;}
.item-shop-card__img{width:104px;height:104px;flex:0 0 104px;overflow:hidden;background:#080a12;border-radius:16px;display:flex;align-items:center;justify-content:center;position:relative;}
.item-shop-card__img::before{display:none;}
.item-shop-card__img img{position:relative;z-index:1;width:100%;height:100%;object-fit:contain;object-position:center;display:block;transition:transform .3s ease;}
.item-shop-card:hover .item-shop-card__img img{transform:scale(1.04);}

.item-shop-card__body{padding:16px 18px;display:flex;flex-direction:column;flex:1;gap:14px;}
.item-shop-card__title{color:#fff;font-size:14px;font-weight:800;line-height:1.35;text-decoration:none;display:block;}
.item-shop-card__title:hover{color:#d5ccff;}
.item-shop-card__desc{color:rgba(255,255,255,.5);font-size:12px;line-height:1.5;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:8px;}
.item-shop-badges{display:flex;flex-wrap:wrap;gap:8px;margin:0;}
.item-shop-badge{min-width:0;width:auto;height:auto;background:rgba(255,255,255,.045)!important;color:rgba(239,242,255,.78)!important;border:1px solid rgba(255,255,255,.075)!important;display:inline-flex;align-items:center;gap:6px;border-radius:9px;padding:6px 11px;font-size:10.5px!important;font-weight:750;white-space:nowrap;}
.item-shop-badge i{width:10px;flex:0 0 10px;text-align:center;font-size:9px!important;color:#8ea5ff!important}.item-shop-badge .item-type-img{width:10px!important;height:10px!important}
.item-type-img{width:14px;height:14px;object-fit:contain;border-radius:2px;}
.item-shop-bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:auto;padding-top:16px;border-top:1px solid rgba(255,255,255,.075);}
.item-shop-price{font-size:22px;font-weight:900;color:#fff;line-height:1;display:inline-flex;align-items:baseline;gap:5px;white-space:nowrap;min-width:0;}
.item-shop-price small{font-size:11px;color:rgba(255,255,255,.55);font-weight:600;white-space:nowrap;}
.item-shop-btn{padding:9px 14px;border-radius:11px;background:linear-gradient(135deg,#4f6ef7 0%,#6366f1 100%);color:#fff;text-decoration:none;font-weight:800;white-space:nowrap;font-size:13px;}
.item-shop-btn:hover{color:#fff;opacity:.9;}
.item-shop-seller{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:14px;padding:16px 18px;border-top:1px solid rgba(255,255,255,.06);border-radius:0 0 18px 18px;background:#101322;min-height:78px;}
.item-shop-seller__left{display:flex;align-items:center;gap:14px;min-width:0;flex:1;overflow:visible;text-decoration:none;}
.item-shop-seller__left img{width:46px;height:46px;min-width:46px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid rgba(150,109,255,.35);box-shadow:0 0 0 4px rgba(122,92,255,.08);}
.item-shop-seller__namewrap{display:flex;align-items:center;gap:6px;min-width:0;flex:1;overflow:hidden;}
.item-shop-seller__rank{display:inline-flex;align-items:center;justify-content:center;margin-left:1px;font-size:18px;line-height:1;flex-shrink:0;filter:drop-shadow(0 0 10px currentColor);transform:translateY(1px);}
.item-shop-seller__name{color:#fff;font-weight:800;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;text-transform:uppercase;letter-spacing:-.015em;}
.item-shop-seller__left:hover .item-shop-seller__name{color:#a5b4fc;}
.item-shop-seller__stats{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:0 10px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.06);color:#fff;font-weight:700;font-size:13px;white-space:nowrap;flex-shrink:0;}

@media(max-width:1500px){.items-grid-fixed{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:1100px){.items-grid-fixed{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:900px){
    .ifb-row--top{flex-wrap:wrap;}
    .ifb-search{max-width:100%;flex:1 0 100%;}
    .ifb-type-tile span{display:none;}
    .ifb-type-tile{padding:8px 10px;gap:0;justify-content:center;}
    .ifb-type-img{width:26px;height:26px;}
    .ifb-type-fa{font-size:16px;}
    .items-filterbar{position:static;top:unset;}
}
@media(max-width:540px){
    .items-grid-fixed{grid-template-columns:1fr;gap:14px;}
    .item-shop-price{font-size:20px;}
}

/* ── Manual font size boost for 0.88 zoom pages ── */
.ifb-row-label                { font-size: 15px; }
.ifb-search i                 { font-size: 16px; }
.ifb-search input             { font-size: 17px; }
.ifb-pill                     { font-size: 16px; }
.ifb-pill-val                 { font-size: 14px; }
.ifb-caret                    { font-size: 12px; }
.ifb-clear                    { font-size: 16px; }
.ifb-type-tile                { font-size: 15px; }
.ifb-type-fa                  { font-size: 22px; }
.ifb-server-badge             { font-size: 14px; }
.ifb-price-field label        { font-size: 16px; }
.ifb-price-field input        { font-size: 16px; }
.ifb-price-sep                { font-size: 16px; }
.ifb-range-labels             { font-size: 14px; }
.ifb-sort-item                { font-size: 16px; }
.ifb-sort-item i              { font-size: 15px; }
.shop-count                   { font-size: 16px; }
.item-shop-card__title        { font-size: 17px; line-height: 1.35; }
.item-shop-card__desc         { font-size: 14px; line-height: 1.55; }
.item-shop-badge              { font-size: 13px; }
.item-shop-price              { font-size: 28px; }
.item-shop-price small        { font-size: 13px; }
.item-shop-btn                { font-size: 15px; }
.item-shop-seller__name       { font-size: 14px; }
.item-shop-seller__stats      { font-size: 13px; }
.shop-empty__title            { font-size: 22px; }
.shop-empty__text             { font-size: 15px; }
.shop-empty__btn span         { font-size: 15px; }
.page-btn                     { font-size: 14px; }
.page-ellipsis                { font-size: 16px; }
@media(max-width:900px){
    .ifb-row-label            { font-size: 14px; }
    .ifb-search input         { font-size: 16px; }
    .ifb-pill                 { font-size: 15px; }
    .ifb-clear                { font-size: 15px; }
    .item-shop-card__title    { font-size: 16px; }
    .item-shop-card__desc     { font-size: 14px; }
    .item-shop-price          { font-size: 25px; }
    .item-shop-btn            { font-size: 14px; }
}
@media(max-width:540px){
    .item-shop-card__title    { font-size: 15px; }
    .item-shop-card__desc     { font-size: 13px; }
    .item-shop-price          { font-size: 23px; }
    .item-shop-btn            { font-size: 14px; }
}

</style>
<?= $this->stop() ?>
