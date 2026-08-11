<?php
/**
 * Component: website/components/items/item-cards
 * Called by Ajax::item_shop_filters() via view_file()
 * Receives: $items (array of selling_items rows with seller data)
 * 
 * SELLER SALES: Uses unified seller_sales_unified.php system
 * - seller_total_sales should be in SQL query (see ajax.php)
 * - Fallback: get_seller_total_sales() if not in query
 */

$items = $items ?? [];

if (!function_exists('item_shop_display_text')) {
    function item_shop_display_text($value, string $default = ''): string
    {
        $raw = ($value === null || $value === '') ? $default : (string)$value;
        return htmlspecialchars(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lb_seller_profile_slug_from_value')) {
    function lb_seller_profile_slug_from_value($slug = '', $username = ''): string
    {
        $value = trim((string)$slug);
        if ($value === '') {
            $value = trim((string)$username);
        }
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^\pL\pN_-]+/u', '', (string)$value);
        $value = trim((string)$value, '-');
        return $value !== '' ? $value : trim((string)$username);
    }
}



if (!function_exists('lb_item_card_game_slug')) {
    function lb_item_card_game_slug(array $item, string $fallback = ''): string
    {
        $slug = strtolower(trim((string)($item['game_slug'] ?? $item['checkout_game_slug'] ?? $item['game'] ?? '')));
        $slug = str_replace('_', '-', $slug);
        if (in_array($slug, ['lol', 'league'], true)) {
            $slug = 'league-of-legends';
        }
        if ($slug === '' && !empty($item['game_id']) && function_exists('util_get_all_games')) {
            try {
                foreach ((array)util_get_all_games(true) as $_g) {
                    if ((int)($_g['id'] ?? 0) === (int)$item['game_id']) {
                        $slug = strtolower(trim((string)($_g['slug'] ?? '')));
                        break;
                    }
                }
            } catch (Throwable $e) {}
        }
        if ($slug === '') {
            $slug = strtolower(trim($fallback));
        }
        if (in_array($slug, ['lol', 'league'], true)) {
            $slug = 'league-of-legends';
        }
        return $slug !== '' ? $slug : 'league-of-legends';
    }
}

if (!function_exists('lb_item_card_data_value')) {
    function lb_item_card_data_value(array $item, string $key, string $fallbackKey = ''): string
    {
        $val = '';
        if (!empty($item['item_data'])) {
            $data = json_decode((string)$item['item_data'], true);
            if (is_array($data) && array_key_exists($key, $data)) {
                $val = trim((string)$data[$key]);
            }
        }
        if ($val === '' && $fallbackKey !== '' && isset($item[$fallbackKey])) {
            $val = trim((string)$item[$fallbackKey]);
        }
        return $val;
    }
}


if (!function_exists('lb_item_card_schema_has_field')) {
    function lb_item_card_schema_has_field(string $gameSlug, string $key): bool
    {
        if (!function_exists('lb_get_game_item_schema')) return false;
        try {
            $schema = lb_get_game_item_schema($gameSlug);
            if (empty($schema['fields']) || !is_array($schema['fields'])) return false;
            foreach ($schema['fields'] as $field) {
                if (trim((string)($field['key'] ?? '')) === $key) return true;
            }
        } catch (Throwable $e) {}
        return false;
    }
}

if (!function_exists('lb_item_card_waiting_time_label')) {
    function lb_item_card_waiting_time_label(array $item): string
    {
        $data = [];
        if (!empty($item['item_data'])) {
            $decoded = json_decode((string)$item['item_data'], true);
            if (is_array($decoded)) $data = $decoded;
        }
        $amount = isset($item['waiting_time_amount']) ? (int)$item['waiting_time_amount'] : (int)($data['waiting_time_amount'] ?? 0);
        $unit = strtolower(trim((string)($item['waiting_time_unit'] ?? ($data['waiting_time_unit'] ?? ''))));
        if (!in_array($unit, ['minutes','hours','days'], true)) $unit = 'hours';
        if ($amount <= 0 && $unit !== 'minutes') {
            $days = (int)($item['requires_friendship_days'] ?? 0);
            if ($days >= 7) { $amount = 7; $unit = 'days'; }
            elseif ($days > 1) { $amount = $days; $unit = 'days'; }
            else { $amount = 24; $unit = 'hours'; }
        }
        $labelUnit = $amount === 1 ? rtrim($unit, 's') : $unit;
        return $amount . ' ' . t($labelUnit);
    }
}


include_once __DIR__ . '/../seller/seller-footer.php';

echo '<style>
.seller-online-dot{width:9px;height:9px;min-width:9px;min-height:9px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95);animation:seller-online-pulse 1.45s ease-out infinite;display:inline-block;transform:translateY(1px);flex:0 0 9px;}
@keyframes seller-online-pulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95)}70%{box-shadow:0 0 0 7px rgba(34,197,94,0),0 0 18px rgba(34,197,94,.9)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0),0 0 14px rgba(34,197,94,.95)}}
.item-shop-seller__name{display:inline-flex;align-items:center;gap:8px;}

/* Unified compact card layout: title left, thumbnail right, badges below */
.item-shop-card__body{padding:16px 18px!important;gap:14px!important;}
.item-shop-card__top{display:flex!important;align-items:flex-start!important;gap:18px!important;}
.item-shop-card__info{flex:1 1 auto!important;min-width:0!important;display:flex!important;flex-direction:column!important;gap:12px!important;}
.item-shop-card__title{margin-bottom:0!important;display:block!important;-webkit-line-clamp:unset!important;-webkit-box-orient:unset!important;overflow:visible!important;white-space:normal!important;}
.item-shop-card__img{width:104px!important;height:104px!important;flex:0 0 104px!important;background:#080a12!important;border-radius:16px!important;isolation:auto!important;overflow:hidden!important;display:flex!important;align-items:center!important;justify-content:center!important;}
.item-shop-card__img::before{display:none!important;}
.item-shop-card__img img{position:relative!important;z-index:1!important;width:100%!important;height:100%!important;object-fit:contain!important;object-position:center!important;transform:none!important;}
.item-shop-card__img::after{z-index:2!important;}
.item-shop-card:hover .item-shop-card__img img{transform:scale(1.04)!important;}
.item-shop-badges{display:flex!important;flex-wrap:wrap!important;gap:8px!important;margin:0!important;}
.item-shop-badge{min-width:0;width:auto;height:auto;display:inline-flex!important;align-items:center;justify-content:flex-start;gap:6px;padding:6px 11px!important;border-radius:9px!important;background:rgba(255,255,255,.045)!important;border:1px solid rgba(255,255,255,.075)!important;color:rgba(239,242,255,.78)!important;font-size:10.5px!important;font-weight:750!important;white-space:nowrap;}
.item-shop-badge i{width:10px!important;flex:0 0 10px!important;text-align:center;font-size:9px!important;color:#8ea5ff!important;}
.item-shop-badge .item-type-img,.item-shop-badge img{width:10px!important;height:10px!important;object-fit:contain!important;border-radius:2px!important;}
.item-shop-bottom{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;margin-top:auto!important;padding-top:16px!important;border-top:1px solid rgba(255,255,255,.075)!important;}
/* Price + "/ Unit" always on a single line */
.item-shop-price{display:inline-flex!important;align-items:baseline!important;gap:5px!important;white-space:nowrap!important;min-width:0!important;}
.item-shop-price small{white-space:nowrap!important;}
</style>';

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


/* ── Type helpers (same as shop.php — needed here too) ── */
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
            'chests'       => 'fa-solid fa-box-open',
            'keys'         => 'fa-solid fa-key',
            'orbs'         => 'fa-solid fa-circle-nodes',
            'capsules'     => 'fa-solid fa-capsules',
            'event-pass'   => 'fa-solid fa-ticket',
            'passes'       => 'fa-solid fa-ticket',
            'bundles'      => 'fa-solid fa-gift',
            'bundle'       => 'fa-solid fa-gift',
            'tft-item'     => 'fa-solid fa-chess-board',
            'mystery-gift' => 'fa-solid fa-sparkles',
            'gift-item'    => 'fa-solid fa-gift',
            // Non-League games (schema-defined types)
            'bot-lobby'        => 'fa-solid fa-gamepad',
            'camo-unlock'      => 'fa-solid fa-palette',
            'weapon-leveling'  => 'fa-solid fa-gun',
            'account-leveling' => 'fa-solid fa-arrow-up-short-wide',
            'operator-unlocks' => 'fa-solid fa-user-ninja',
            'cash'         => 'fa-solid fa-coins',
            'coins'        => 'fa-solid fa-coins',
            'credits'      => 'fa-solid fa-coins',
            'gems'         => 'fa-solid fa-gem',
            'rerolls'      => 'fa-solid fa-rotate',
            'totems'       => 'fa-solid fa-cube',
            'seeds'        => 'fa-solid fa-seedling',
            'pets'         => 'fa-solid fa-paw',
            'set'          => 'fa-solid fa-layer-group',
            'other'        => 'fa-solid fa-tag',
        ];
        return $fa[items_shop_type_key($type)] ?? 'fa-solid fa-tag';
    }
}

$assetBase = rtrim(ASSET_URL, '/');

$shopCurrency = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$shopSymbol   = function_exists('util_format_currency_display')
    ? util_format_currency_display($shopCurrency)
    : ($shopCurrency === 'USD' ? '$' : '€');
$currentGameSlug = strtolower(trim((string)($currentGameSlug ?? $game ?? '')));

foreach ($items as $item):
    $images      = json_decode((string)($item['images'] ?? '[]'), true);
    if (!is_array($images)) $images = [];
    $cover       = $images[0] ?? ($assetBase . '/public/uploads/icons/default2.png');
    $slugOrId    = !empty($item['slug']) ? $item['slug'] : (string)(int)$item['id'];
    $itemGameSlug = lb_item_card_game_slug((array)$item, $currentGameSlug);
    $itemUrl     = BASE_URL . '/' . rawurlencode($itemGameSlug) . '/item/' . rawurlencode($slugOrId);
    $priceCents  = (int)($item['price'] ?? 0);
    $priceNumber = function_exists('util_format_price_display')
        ? util_format_price_display($priceCents)
        : number_format($priceCents / 100, 2);
    $stock       = (int)($item['stock'] ?? 1);
    
    // ═══════════════════════════════════════════════════════════════════
    // SELLER SALES - UNIFIED SYSTEM
    // ═══════════════════════════════════════════════════════════════════
    // Prefer seller_total_sales from SQL query (see ajax.php)
    // Fallback to function call if not present
    // ═══════════════════════════════════════════════════════════════════
    
    $sellerTotalSales = (int)($item['seller_total_sales'] ?? 0);
    if ($sellerTotalSales <= 0 && !empty($item['seller_id']) && function_exists('get_seller_total_sales')) {
        $sellerTotalSales = get_seller_total_sales((int)$item['seller_id']);
    }
    
    $sellerName  = $item['seller_username'] ?? 'Seller';
    $sellerOnline = !empty($item['seller_is_online']);
    $sellerSlug  = lb_seller_profile_slug_from_value($item['seller_slug'] ?? '', $sellerName);
    $sellerLink  = BASE_URL . '/sellers/' . rawurlencode((string)$sellerSlug);
    $sellerIcon  = $item['seller_icon'] ?? (ICON_URL . '/default.png');
    $sellerRank  = (string)($item['seller_rank'] ?? '');
    $sellerRankIcon = (string)($item['seller_rank_icon'] ?? '');
    $sellerRankMeta = items_seller_rank_meta($sellerRank, $sellerRankIcon);
    $isLeagueCard = in_array($itemGameSlug, ['league-of-legends','lol','league'], true);
    $allowTypeBadge = lb_item_card_schema_has_field($itemGameSlug, 'type') || $isLeagueCard;
    $allowServerBadge = lb_item_card_schema_has_field($itemGameSlug, 'server') || $isLeagueCard;
    $rawType     = $allowTypeBadge ? lb_item_card_data_value((array)$item, 'type', 'type') : '';
    $typeLabel   = $rawType !== '' ? items_shop_type_label($rawType) : '';
    // The bundled type artwork is League-only; other games fall back to the Font Awesome icon.
    $typeImg     = ($rawType !== '' && $isLeagueCard) ? items_shop_type_img($rawType) : null;
    $typeFa      = $rawType !== '' ? items_shop_type_fa($rawType) : '';
    $rawServer   = $allowServerBadge ? lb_item_card_data_value((array)$item, 'server', 'server') : '';
    $server      = $rawServer !== '' && function_exists('util_format_server_code') ? util_format_server_code($rawServer) : strtoupper($rawServer);
    $deliveryLabel = lb_item_card_waiting_time_label((array)$item);

    ob_start();
    ?>
    <?php if ($typeLabel !== ''): ?>
        <span class="item-shop-badge item-shop-badge--type">
            <?php if ($typeImg): ?>
                <img class="item-type-img" src="<?= htmlspecialchars($typeImg) ?>" alt="">
            <?php else: ?>
                <i class="<?= htmlspecialchars($typeFa) ?>"></i>
            <?php endif; ?>
            <?= htmlspecialchars($typeLabel) ?>
        </span>
    <?php endif; ?>
    <span class="item-shop-badge item-shop-badge--stock"><i class="fa-solid fa-box"></i><?= $stock ?> <?= t('in stock') ?></span>
    <?php if ($deliveryLabel !== ''): ?>
        <span class="item-shop-badge item-shop-badge--waiting"><i class="fa-solid fa-clock"></i><?= htmlspecialchars(t('Waiting') . ' ' . $deliveryLabel) ?></span>
    <?php endif; ?>
    <?php if ($server !== ''): ?>
        <span class="item-shop-badge item-shop-badge--server"><i class="fa-solid fa-globe"></i><?= htmlspecialchars($server) ?></span>
    <?php endif; ?>
    <?php
    $badgesHtml = ob_get_clean();

    $sellerFooterHtml = lb_render_seller_footer([
        'id' => $item['seller_id'] ?? null,
        'username' => $sellerName,
        'slug' => $sellerSlug,
        'icon' => $sellerIcon,
        'rank' => $sellerRank,
        'rank_icon' => $sellerRankIcon,
        'is_active' => $item['seller_is_active'] ?? 1,
        'is_online' => $sellerOnline ? 1 : 0,
        'total_sold' => $sellerTotalSales,
        'seller_total_sales' => $sellerTotalSales,
    ], ['variant' => 'item-card']);
    ?>
<article class="item-shop-card item-shop-card--compact">
    <div class="item-shop-card__body">
        <div class="item-shop-card__top">
            <div class="item-shop-card__info">
                <a href="<?= htmlspecialchars($itemUrl) ?>" class="item-shop-card__title">
                    <?= item_shop_display_text($item['title'] ?? null, 'Untitled') ?>
                </a>
                <div class="item-shop-badges"><?= $badgesHtml ?></div>
            </div>
            <a class="item-shop-card__img" href="<?= htmlspecialchars($itemUrl) ?>">
                <img src="<?= htmlspecialchars($cover) ?>" alt="<?= item_shop_display_text($item['title'] ?? null, 'Item') ?>" loading="lazy">
            </a>
        </div>
        <div class="item-shop-bottom">
            <div class="item-shop-price"><?= $shopSymbol . $priceNumber ?> <small>/ <?= t('Unit') ?></small></div>
            <a class="item-shop-btn" href="<?= htmlspecialchars($itemUrl) ?>"><?= t('Buy Now') ?> <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <?= $sellerFooterHtml ?>
</article>
<?php endforeach; ?>
