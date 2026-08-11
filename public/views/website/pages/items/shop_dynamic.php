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

if (!function_exists('lb_enrich_rows_with_seller_stats')) {
    function lb_enrich_rows_with_seller_stats(array $rows): array
    {
        $sellerIds = [];
        foreach ($rows as $row) {
            $sellerId = (int)($row['seller_id'] ?? $row['id'] ?? 0);
            if ($sellerId > 0) $sellerIds[$sellerId] = $sellerId;
        }
        if (!$sellerIds) return $rows;

        $stats = [];
        global $db;
        if (!empty($db)) {
            try {
                $ids = array_values($sellerIds);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $result = $db->run(
                    "SELECT seller_id, total_sales AS service_sales FROM seller_stats WHERE seller_id IN ($placeholders)",
                    ...$ids
                ) ?: [];
                foreach ($result as $statRow) {
                    $stats[(int)($statRow['seller_id'] ?? 0)] = max(0, (int)($statRow['service_sales'] ?? 0));
                }
            } catch (Throwable $e) {}
        }

        foreach ($rows as &$row) {
            $sellerId = (int)($row['seller_id'] ?? $row['id'] ?? 0);
            if ($sellerId > 0 && array_key_exists($sellerId, $stats)) {
                $row['seller_total_sales'] = $stats[$sellerId];
                $row['total_sales'] = $stats[$sellerId];
                $row['total_sold'] = $stats[$sellerId];
                $row['seller_sold'] = $stats[$sellerId];
            }
        }
        unset($row);
        return $rows;
    }
}
$items = lb_enrich_rows_with_seller_stats((array)$items);
$pagination = $pagination ?? ['page' => 1, 'totalPages' => 1, 'totalItems' => count($items)];
$itemShopGameSlug = strtolower(trim((string)($game ?? ($gameConfig['slug'] ?? ''))));
$itemShopGameId   = (int)($gameConfig['id'] ?? 0);
$itemShopGameShort = strtolower(trim((string)($gameShort ?? '')));
$isLeagueItemsShop = in_array($itemShopGameSlug, ['league-of-legends', 'lol', 'leagu', 'league'], true) || in_array($itemShopGameShort, ['lol', 'league-of-legends'], true);
// Views render inside a function scope, so expose the flag for the type-icon helpers below.
$GLOBALS['isLeagueItemsShop'] = $isLeagueItemsShop;
$itemShopUrlBase = $itemShopGameSlug !== '' ? '/' . rawurlencode($itemShopGameSlug) : '/league-of-legends';
$itemsConfig = is_array($itemsConfig ?? null) ? $itemsConfig : (function_exists('lb_get_items_page_config') ? lb_get_items_page_config($itemShopGameSlug) : []);
$itemSchema = is_array($itemSchema ?? null) ? $itemSchema : (function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($itemShopGameSlug) : []);
if (!empty($itemsConfig['page_title'])) $meta['h1'] = $itemsConfig['page_title'];
if (!empty($itemsConfig['page_description'])) $meta['description'] = $itemsConfig['page_description'];
?>
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
            'keys'=>'Keys','key'=>'Keys',
            'chests'=>'Chests','chest'=>'Chests','chests-keys'=>'Chests','chest-key'=>'Chests','chests-and-keys'=>'Chests',
            'skins'=>'Skins','skin'=>'Skins',
            'orbs'=>'Orbs','orb'=>'Orbs',
            'capsules'=>'Capsules','capsule'=>'Capsules',
            'passes'=>'Passes','pass'=>'Passes','event-pass'=>'Passes','battle-pass'=>'Passes',
            'bundle'=>'Bundle','bundles'=>'Bundle',
            'tft-item'=>'TFT Item','tft'=>'TFT Item',
            'gift-item'=>'Gift Item','gifting'=>'Gift Item','mystery-gift'=>'Gift Item',
            'bot-lobby'=>'Bot Lobby','camo-unlock'=>'Camo Unlock','weapon-leveling'=>'Weapon Leveling','account-leveling'=>'Account Leveling','operator-unlocks'=>'Operator Unlocks',
            'cash'=>'Cash','coins'=>'Coins','gems'=>'Gems','rerolls'=>'Rerolls','totems'=>'Totems','set'=>'Set','other'=>'Other',
        ];
        $k = strtolower(trim($type));
        $k = trim(preg_replace('/[^a-z0-9]+/', '-', $k), '-');
        return $map[$k] ?? ucwords(str_replace(['_', '-'], ' ', $type));
    }
}
if (!function_exists('items_shop_type_key')) {
    function items_shop_type_key(string $type): string {
        $label = strtolower(items_shop_type_label($type));
        return trim(preg_replace('/[^a-z0-9]+/', '-', $label), '-');
    }
}
if (!function_exists('items_shop_option_value')) {
    function items_shop_option_value($opt): string {
        if (is_array($opt)) return trim((string)($opt['value'] ?? $opt['key'] ?? $opt['label'] ?? ''));
        return trim((string)$opt);
    }
}
if (!function_exists('items_shop_option_label')) {
    function items_shop_option_label($opt): string {
        if (is_array($opt)) return trim((string)($opt['label'] ?? $opt['value'] ?? $opt['key'] ?? ''));
        return trim((string)$opt);
    }
}
if (!function_exists('items_shop_option_icon')) {
    function items_shop_option_icon($opt): string {
        if (is_array($opt)) return trim((string)($opt['icon_path'] ?? $opt['icon'] ?? ''));
        return '';
    }
}
if (!function_exists('items_shop_type_img')) {
    function items_shop_type_img(string $type, string $explicit = ''): ?string {
        $explicit = trim($explicit);
        if ($explicit !== '') {
            if (preg_match('~^https?://~i', $explicit)) return $explicit;
            return rtrim(ASSET_URL, '/') . '/' . ltrim(preg_replace('~^/public/assets/~', '', $explicit), '/');
        }
        // The bundled webp artwork is League-specific. Other games fall back to the
        // Font Awesome icon from items_shop_type_fa() unless the schema sets its own icon.
        if (empty($GLOBALS['isLeagueItemsShop'])) return null;
        $stems = [
            'skins'=>'skins-item','chests'=>'chest-item','orbs'=>'orbs-item','capsules'=>'capsules-item','passes'=>'event-pass-item','bundle'=>'bundle-item','tft-item'=>'tft-item'
        ];
        $key = items_shop_type_key($type);
        if (!array_key_exists($key, $stems)) return null;
        return rtrim(ASSET_URL, '/') . '/website/images/items/' . $stems[$key] . '.webp';
    }
}
if (!function_exists('items_shop_type_fa')) {
    function items_shop_type_fa(string $type): string {
        $fa = [
            'keys'=>'fa-solid fa-key','chests'=>'fa-solid fa-box-open','skins'=>'fa-solid fa-shirt','orbs'=>'fa-solid fa-circle-nodes',
            'capsules'=>'fa-solid fa-capsules','passes'=>'fa-solid fa-ticket','bundle'=>'fa-solid fa-gift','gift-item'=>'fa-solid fa-gift',
            'bot-lobby'=>'fa-solid fa-gamepad','camo-unlock'=>'fa-solid fa-palette','weapon-leveling'=>'fa-solid fa-gun','account-leveling'=>'fa-solid fa-arrow-up-short-wide',
            'operator-unlocks'=>'fa-solid fa-user-ninja','cash'=>'fa-solid fa-coins','coins'=>'fa-solid fa-coins','gems'=>'fa-solid fa-gem','rerolls'=>'fa-solid fa-rotate','totems'=>'fa-solid fa-cube','set'=>'fa-solid fa-layer-group','other'=>'fa-solid fa-tag'
        ];
        return $fa[items_shop_type_key($type)] ?? 'fa-solid fa-tag';
    }
}

/* ── Types + dynamic schema filters ─────────────────────────────── */
$allTypes = [];
$dynamicFilterRows = [];
if (!empty($itemSchema['fields']) && is_array($itemSchema['fields'])) {
    foreach ($itemSchema['fields'] as $_field) {
        $_key = (string)($_field['key'] ?? '');
        if (in_array($_key, ['type','item_type'], true) && !empty($_field['options']) && is_array($_field['options'])) {
            foreach ($_field['options'] as $_opt) {
                $_label = items_shop_option_label($_opt);
                $_value = items_shop_option_value($_opt);
                $_icon  = items_shop_option_icon($_opt);
                $_dataKey = items_shop_type_key($_value !== '' ? $_value : $_label);
                if ($_dataKey !== '') $allTypes[$_dataKey] = ['label'=>($_label !== '' ? $_label : items_shop_type_label($_value)), 'value'=>$_dataKey, 'icon'=>$_icon];
            }
        }
    }
}
if (empty($allTypes) && $isLeagueItemsShop && !empty($items)) {
    foreach ($items as $_item) {
        $_rawType = trim((string)($_item['type'] ?? ''));
        if ($_rawType === '' && !empty($_item['item_data'])) {
            $_tmpData = json_decode((string)$_item['item_data'], true);
            if (is_array($_tmpData)) $_rawType = trim((string)($_tmpData['type'] ?? $_tmpData['item_type'] ?? ''));
        }
        if ($_rawType === '') continue;
        $_key = items_shop_type_key($_rawType);
        if ($_key !== '') $allTypes[$_key] = ['label'=>items_shop_type_label($_rawType), 'value'=>$_key, 'icon'=>''];
    }
}
$hasTypeFilterSchema = !empty($allTypes);

$serverOptions = [];
if (!empty($itemSchema['fields']) && is_array($itemSchema['fields'])) {
    foreach ($itemSchema['fields'] as $_field) {
        $_key = (string)($_field['key'] ?? '');
        if ($_key === 'server' && !empty($_field['options']) && is_array($_field['options'])) {
            foreach ($_field['options'] as $_opt) {
                $_value = strtoupper(trim(items_shop_option_value($_opt)));
                $_label = items_shop_option_label($_opt);
                if ($_value !== '') $serverOptions[$_value] = $_label !== '' ? $_label : $_value;
            }
        } elseif (!in_array($_key, ['title','type','item_type','server','price','stock','delivery_time'], true) && !empty($_field['options']) && is_array($_field['options'])) {
            $_opts = [];
            foreach ($_field['options'] as $_opt) {
                $_value = items_shop_option_value($_opt);
                $_label = items_shop_option_label($_opt);
                if ($_value !== '') $_opts[] = ['value'=>$_value, 'label'=>($_label !== '' ? $_label : $_value)];
            }
            if ($_opts) $dynamicFilterRows[] = ['key'=>$_key, 'label'=>(string)($_field['label'] ?? ucwords(str_replace('_',' ',$_key))), 'options'=>$_opts];
        }
    }
}
if (empty($serverOptions) && $isLeagueItemsShop && !empty($items)) {
    foreach ($items as $_item) {
        $_srv = strtoupper(trim((string)($_item['server'] ?? '')));
        if ($_srv === '' && !empty($_item['item_data'])) {
            $_tmpData = json_decode((string)$_item['item_data'], true);
            if (is_array($_tmpData)) $_srv = strtoupper(trim((string)($_tmpData['server'] ?? '')));
        }
        if ($_srv !== '') $serverOptions[$_srv] = $_srv;
    }
}
$hasServerFilterSchema = !empty($serverOptions);
$allServers = $serverOptions;

$deliveryTimeOptions = [
    'within_30_minutes' => t('Within 30 minutes'),
    'within_1_hour' => t('Within 1 hour'),
    'within_1_2_hours' => t('Within 1-2 hours'),
    'within_2_6_hours' => t('Within 2-6 hours'),
    'within_6_24_hours' => t('Within 6-24 hours'),
    'more_than_24_hours' => t('More than 24 hours'),
];
if (!empty($itemSchema['fields']) && is_array($itemSchema['fields'])) {
    foreach ($itemSchema['fields'] as $_field) {
        if (($_field['key'] ?? '') !== 'delivery_time' || empty($_field['options']) || !is_array($_field['options'])) continue;
        $customDeliveryOptions = [];
        foreach ($_field['options'] as $_opt) {
            $_label = is_array($_opt) ? (string)($_opt['label'] ?? $_opt['value'] ?? '') : (string)$_opt;
            $_value = is_array($_opt) ? (string)($_opt['value'] ?? $_label) : $_label;
            $_value = strtolower(trim($_value));
            if ($_value !== '') $customDeliveryOptions[$_value] = $_label !== '' ? $_label : ucwords(str_replace('_', ' ', $_value));
        }
        if (!empty($customDeliveryOptions)) $deliveryTimeOptions = $customDeliveryOptions;
        break;
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

if (!function_exists('items_shop_render_filter_dropdown')) {
    function items_shop_render_filter_dropdown(string $id, string $label, string $kind, array $options, string $icon = 'fa-solid fa-sliders'): void {
        if (empty($options)) return;
        ?>
        <div class="ifb-filter-wrap" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>Wrap" data-filter-wrap="<?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') ?>">
            <button type="button" class="ifb-pill ifb-filter-btn" data-filter-btn="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
                <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="ifb-pill-val" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>Val"></span>
                <i class="fa-solid fa-caret-down ifb-caret"></i>
            </button>
            <div class="ifb-generic-dropdown" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>Dropdown">
                <div class="ifb-generic-head">
                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    <button type="button" class="ifb-generic-close" data-filter-close="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">×</button>
                </div>
                <div class="ifb-generic-list">
                    <?php foreach ($options as $opt):
                        $value = is_array($opt) ? (string)($opt['value'] ?? $opt['key'] ?? $opt['label'] ?? '') : (string)$opt;
                        $optLabel = is_array($opt) ? (string)($opt['label'] ?? $opt['value'] ?? $opt['key'] ?? '') : (string)$opt;
                        if ($value === '' || $optLabel === '') continue;
                    ?>
                    <label class="ifb-check-option">
                        <input type="checkbox" class="ifb-check" data-filter-kind="<?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') ?>" data-filter-value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="ifb-check-box"><i class="fa-solid fa-check"></i></span>
                        <span class="ifb-check-label"><?= htmlspecialchars($optLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
?>

<?php
$csItemsGameIcon = (string)($gameConfig['icon'] ?? '');
if ($csItemsGameIcon === '' && function_exists('util_get_game_by_slug')) {
    $csItemsGameIcon = (string)(util_get_game_by_slug($game)['icon'] ?? '');
}
?>
<?php $lbTotalItemsForHero = (int)($pagination['totalItems'] ?? count((array)($items ?? []))); ?>
<?php if ($lbTotalItemsForHero > 0): ?>
<section class="lb-shop-hero">
    <div class="lb-shop-hero__inner">
        <div class="lb-shop-hero__icon">
            <i class="fa-solid fa-box-open"></i>
        </div>
        <div>
            <div class="lb-shop-hero__kicker">Items</div>
            <h1 class="lb-shop-hero__title"><?= htmlspecialchars($meta['h1'] ?? 'LoL Items Shop') ?></h1>
            <p class="lb-shop-hero__desc"><?= htmlspecialchars($meta['description'] ?? ('Buy ' . ($meta['h1'] ?? 'items') . ' securely with verified sellers, fast delivery, and buyer protection.')) ?></p>
        </div>
    </div>
</section>
<?php endif; ?>
<?= $this->start('styles') ?>
<style>
.lb-shop-hero{position:relative;border-bottom:1px solid rgba(255,255,255,.06);overflow:hidden;}
.lb-shop-hero__inner{max-width:1500px;margin:0 auto;display:flex;align-items:center;gap:22px;min-height:170px;padding:36px 28px;}
.lb-shop-hero__icon{width:74px;height:74px;min-width:74px;border-radius:20px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;box-shadow:0 18px 50px rgba(0,0,0,.28);overflow:hidden;}
.lb-shop-hero__icon img{width:46px;height:46px;border-radius:12px;object-fit:cover;display:block;}
.lb-shop-hero__icon i{font-size:30px;color:#7c6cff;}
.lb-shop-hero__kicker{font-size:12px;letter-spacing:.13em;text-transform:uppercase;color:#8b9bff;font-weight:900;margin-bottom:8px;}
.lb-shop-hero__title{margin:0;font-size:29px;line-height:1.12;font-weight:950;letter-spacing:-.03em;color:#fff;}
.lb-shop-hero__desc{margin:8px 0 0;color:#a9adc4;font-size:15px;max-width:640px;font-weight:600;}
@media(max-width:760px){
    .lb-shop-hero{overflow:visible!important;background:transparent!important;border:0!important;margin-bottom:22px!important;}
    .lb-shop-hero__inner{width:100%!important;max-width:100%!important;min-width:0!important;display:grid!important;grid-template-columns:42px minmax(0,1fr)!important;align-items:flex-start!important;gap:10px!important;padding:14px 16px 24px!important;margin:0!important;min-height:0!important;overflow:visible!important;}
    .lb-shop-hero__inner > div:last-child{min-width:0!important;width:100%!important;max-width:100%!important;overflow:visible!important;}
    .lb-shop-hero__icon{width:40px!important;height:40px!important;min-width:40px!important;border-radius:12px!important;margin-top:2px!important;}
    .lb-shop-hero__icon img{width:25px!important;height:25px!important;border-radius:8px!important;}
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

.lb-shop-empty-notify-offset{
    --lb-empty-extra-top:0px;
    --lb-empty-top-gap:112px;
    --lb-empty-bottom-gap:112px;
    padding-top:calc(var(--lb-empty-top-gap) + var(--lb-empty-extra-top))!important;
    padding-bottom:var(--lb-empty-bottom-gap)!important;
    min-height:calc(100svh - var(--lb-empty-page-chrome, 360px));
    display:flex;
    align-items:center;
    justify-content:center;
}
.lb-shop-empty-notify-offset > .lb-cs2{margin:0 auto!important;}
@media(max-width:1180px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:100px;--lb-empty-bottom-gap:100px;}}
@media(max-width:920px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:88px;--lb-empty-bottom-gap:88px;}}
@media(max-width:760px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:58px;--lb-empty-bottom-gap:68px;min-height:auto;}}
@media(max-width:420px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:50px;--lb-empty-bottom-gap:60px;}}
</style>
<?= $this->stop() ?>

<div class="container">
    <div id="itemsTop" style="height:1px;"></div>

    <?php
    // Determine up front whether this game has ANY item listings at all.
    // If not, skip the filterbar / grid / toolbar entirely instead of hiding
    // them with CSS, so nothing extra gets loaded or initialized.
    $lbTotalItems = (int)($pagination['totalItems'] ?? count((array)($items ?? [])));
    ?>

    <?php if ($lbTotalItems <= 0): ?>

        <div class="lb-shop-empty-notify-offset">
            <?= $this->insert('website/pages/components/coming-soon-notify', [
                'game' => $game,
                'gameConfig' => $gameConfig ?? [],
                'gameIcon' => $csItemsGameIcon,
                'service' => 'items',
                'title' => 'Coming soon',
                'text' => 'There are no item listings for this game yet. Leave your email and we will notify you as soon as items are available.'
            ]) ?>
        </div>

<script>
(function(){
  if(window.lbShopEmptyNotifySyncLoaded) return;
  window.lbShopEmptyNotifySyncLoaded = true;

  function getGameNavBottom(){
    var selectors = ['.lb-game-subnav', '.game-subnav', '[class*="game-subnav"]'];
    var best = 0;
    selectors.forEach(function(selector){
      Array.prototype.forEach.call(document.querySelectorAll(selector), function(el){
        var cs = window.getComputedStyle(el);
        if(cs.display === 'none' || cs.visibility === 'hidden') return;
        var r = el.getBoundingClientRect();
        if(r.width < 120 || r.height < 20) return;
        if(r.top > window.innerHeight || r.bottom < 0) return;
        best = Math.max(best, r.bottom);
      });
    });
    return best;
  }

  function syncEmptyNotify(){
    var blocks = document.querySelectorAll('.lb-shop-empty-notify-offset');
    if(!blocks.length) return;

    window.requestAnimationFrame(function(){
      var navBottom = getGameNavBottom();
      if(!navBottom) return;

      var isMobile = window.matchMedia('(max-width:760px)').matches;
      var gap = isMobile ? 76 : 82;
      var desiredTop = Math.round(navBottom + gap);
      var viewportBottom = window.innerHeight || document.documentElement.clientHeight || 0;

      blocks.forEach(function(block){
        block.style.setProperty('--lb-empty-extra-top', '0px');

        var target = block.firstElementChild || block;
        var currentTop = Math.round(target.getBoundingClientRect().top);
        var extra = Math.max(0, desiredTop - currentTop);

        block.style.setProperty('--lb-empty-extra-top', extra + 'px');

        var afterTop = Math.round((target.getBoundingClientRect().top || currentTop) + extra);
        var bottomGap = Math.max(isMobile ? 72 : 96, Math.round(viewportBottom - afterTop - (isMobile ? 430 : 520)));
        block.style.setProperty('--lb-empty-bottom-gap', bottomGap + 'px');
        block.style.setProperty('--lb-empty-page-chrome', Math.max(260, Math.round(navBottom + bottomGap)) + 'px');
      });
    });
  }

  syncEmptyNotify();
  window.addEventListener('load', syncEmptyNotify, {once:true});
  window.addEventListener('resize', syncEmptyNotify, {passive:true});
  window.addEventListener('orientationchange', syncEmptyNotify, {passive:true});
  setTimeout(syncEmptyNotify, 120);
  setTimeout(syncEmptyNotify, 450);
})();
</script>

    <?php else: ?>

    <!-- ══════════════════════════════════════════════════════════
         FILTERBAR  –  Option B: Tile-style, no dropdowns for type/server
         ══════════════════════════════════════════════════════════ -->
    <div class="items-filterbar" id="shopFilterbar">
        <form id="shopFilters">
            <input type="hidden" name="action" value="item_shop_filters">
            <input type="hidden" name="game" value="<?= htmlspecialchars($itemShopGameSlug) ?>">
            <input type="hidden" name="game_slug" id="itemShopGameSlug" value="<?= htmlspecialchars($itemShopGameSlug) ?>">
            <input type="hidden" name="game_id" id="itemShopGameId" value="<?= (int)$itemShopGameId ?>">

            <!-- Row 1: Search + Price + Sort + Clear -->
            <div class="ifb-row ifb-row--top">
                <div class="ifb-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" id="filterSearch" placeholder="<?= t('Search items...') ?>">
                </div>

                <?php if ($hasTypeFilterSchema): ?>
                    <?php items_shop_render_filter_dropdown('typeFilter', t('Type'), 'type', array_values($allTypes), 'fa-solid fa-tag'); ?>
                <?php endif; ?>

                <?php if ($hasServerFilterSchema && !empty($allServers)): ?>
                    <?php
                    $serverDropdownOptions = [];
                    foreach ($allServers as $srvValue => $srvLabel) {
                        $serverDropdownOptions[] = ['value' => (string)$srvValue, 'label' => (string)$srvLabel];
                    }
                    items_shop_render_filter_dropdown('serverFilter', t('Server'), 'server', $serverDropdownOptions, 'fa-solid fa-globe');
                    ?>
                <?php endif; ?>

                <?php if (!empty($dynamicFilterRows)): ?>
                    <?php foreach ($dynamicFilterRows as $df): ?>
                        <?php items_shop_render_filter_dropdown('dynFilter' . preg_replace('/[^a-zA-Z0-9_]/', '', (string)$df['key']), (string)$df['label'], 'dynamic:' . (string)$df['key'], (array)($df['options'] ?? []), 'fa-solid fa-sliders'); ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($deliveryTimeOptions)): ?>
                    <?php
                    $deliveryDropdownOptions = [];
                    foreach ($deliveryTimeOptions as $delValue => $delLabel) {
                        $deliveryDropdownOptions[] = ['value' => (string)$delValue, 'label' => (string)$delLabel];
                    }
                    items_shop_render_filter_dropdown('deliveryFilter', t('Delivery Time'), 'delivery', $deliveryDropdownOptions, 'fa-regular fa-clock');
                    ?>
                <?php endif; ?>

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
                        <button type="button" class="ifb-sort-item" data-sort="waiting_asc"><i class="fa-regular fa-clock"></i><?= t('Lowest Waiting Time') ?></button>
                        <button type="button" class="ifb-sort-item" data-sort="waiting_desc"><i class="fa-solid fa-clock-rotate-left"></i><?= t('Highest Waiting Time') ?></button>
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

            <!-- Hidden inputs written by JS before AJAX -->
            <div id="hiddenFilters" style="display:none;"></div>
        </form>
    </div>

    <?= $this->insert('website/components/accounts/shop-filter-nav') ?>

    <?php if (!empty($allTypes) || !empty($allServers)): ?>
    <div class="ifb-row ifb-row--popular lb-items-popular">
        <span class="ifb-popular-label"><?= t('Most popular') ?>:</span>
        <?php foreach (array_slice(array_values($allTypes), 0, 7) as $typeOption):
            $typeValue = is_array($typeOption) ? (string)($typeOption['value'] ?? '') : (string)$typeOption;
            $typeLabel = is_array($typeOption) ? (string)($typeOption['label'] ?? $typeValue) : $typeValue;
            if ($typeValue === '') continue; ?>
            <button type="button" class="ifb-quick-pill" data-quick-kind="type" data-quick-value="<?= htmlspecialchars($typeValue) ?>" hidden><?= htmlspecialchars($typeLabel) ?></button>
        <?php endforeach; ?>
        <?php foreach (array_slice($allServers, 0, 5, true) as $serverValue => $serverLabel): ?>
            <button type="button" class="ifb-quick-pill" data-quick-kind="server" data-quick-value="<?= htmlspecialchars((string)$serverValue) ?>" hidden><?= htmlspecialchars((string)$serverLabel) ?></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

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
            $rawType    = trim((string)($item['type'] ?? ''));
            if ($rawType === '' && !empty($item['item_data'])) { $__tmpTypeData = json_decode((string)$item['item_data'], true); if (is_array($__tmpTypeData)) $rawType = trim((string)($__tmpTypeData['type'] ?? $__tmpTypeData['item_type'] ?? '')); }
            if ($rawType === '' && !empty($item['item_data'])) {
                $tmpData = json_decode((string)$item['item_data'], true);
                if (is_array($tmpData)) $rawType = trim((string)($tmpData['type'] ?? ''));
            }
            $typeLabel  = $rawType !== '' ? items_shop_type_label($rawType) : '';
            $typeImg    = $rawType !== '' ? items_shop_type_img($rawType) : null;
            $typeFa     = $rawType !== '' ? items_shop_type_fa($rawType) : '';
            $serverRaw  = (string)($item['server'] ?? '');
            $server     = function_exists('util_format_server_code') ? util_format_server_code($serverRaw) : strtoupper($serverRaw);
            if ($server === '' && !empty($item['item_data'])) {
                $tmpData = json_decode((string)$item['item_data'], true);
                if (is_array($tmpData)) {
                    $serverRaw = trim((string)($tmpData['server'] ?? ''));
                    $server = function_exists('util_format_server_code') ? util_format_server_code($serverRaw) : strtoupper($serverRaw);
                }
            }
            $waitingAmount = isset($item['waiting_time_amount']) ? (int)$item['waiting_time_amount'] : 0;
            $waitingUnit = strtolower(trim((string)($item['waiting_time_unit'] ?? '')));
            if (!in_array($waitingUnit, ['minutes','hours','days'], true)) $waitingUnit = 'hours';
            if ($waitingAmount <= 0 && $waitingUnit !== 'minutes') {
                $days = (int)($item['requires_friendship_days'] ?? 0);
                if ($days >= 7) { $waitingAmount = 7; $waitingUnit = 'days'; }
                elseif ($days > 1) { $waitingAmount = $days; $waitingUnit = 'days'; }
                else { $waitingAmount = 24; $waitingUnit = 'hours'; }
            }
            $waitingLabel = $waitingAmount . ' ' . t($waitingAmount === 1 ? rtrim($waitingUnit, 's') : $waitingUnit);
        ?>
        <article class="item-shop-card">
            <div class="item-shop-card__body">
                <div class="item-shop-card__top">
                    <div class="item-shop-card__info">
                        <a href="<?= BASE_URL . $itemShopUrlBase ?>/item/<?= urlencode($slugOrId) ?>" class="item-shop-card__title">
                            <?= item_shop_display_text($item['title'] ?? null, 'Untitled') ?>
                        </a>
                        <div class="item-shop-badges">
                            <?php if ($hasTypeFilterSchema && $typeLabel !== ''): ?>
                            <span class="item-shop-badge item-shop-badge--type">
                                <?php if ($typeImg): ?><img class="item-type-img" src="<?= htmlspecialchars($typeImg) ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($typeFa) ?>"></i><?php endif; ?>
                                <?= htmlspecialchars($typeLabel) ?>
                            </span>
                            <?php endif; ?>
                            <span class="item-shop-badge item-shop-badge--stock"><i class="fa-solid fa-box"></i><?= $stock ?> <?= t('in stock') ?></span>
                            <?php if ($waitingLabel !== ''): ?><span class="item-shop-badge item-shop-badge--waiting"><i class="fa-solid fa-clock"></i><?= htmlspecialchars(t('Waiting') . ' ' . $waitingLabel) ?></span><?php endif; ?>
                            <?php if ($hasServerFilterSchema && trim((string)$server) !== ''): ?><span class="item-shop-badge item-shop-badge--server"><i class="fa-solid fa-globe"></i><?= htmlspecialchars($server) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <a class="item-shop-card__img" href="<?= BASE_URL . $itemShopUrlBase ?>/item/<?= urlencode($slugOrId) ?>">
                        <img src="<?= htmlspecialchars($cover) ?>" alt="<?= item_shop_display_text($item['title'] ?? null, 'Item') ?>" loading="lazy">
                    </a>
                </div>
                <div class="item-shop-bottom">
                    <div class="item-shop-price"><?= $_shop_symbol ?><?= shop_item_format_price((int)($item['price'] ?? 0), $_shop_currency) ?> <small>/ <?= t('Unit') ?></small></div>
                    <a class="item-shop-btn" href="<?= BASE_URL . $itemShopUrlBase ?>/item/<?= urlencode($slugOrId) ?>"><?= t('Buy Now') ?> <i class="fa-solid fa-arrow-right ms-1"></i></a>
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

    <?php endif; ?>

</div>

<?= $this->start('scripts') ?>
<script>
$(document).ready(function () {

    const AJAX_URL_VAL = (typeof ajax_url !== 'undefined') ? ajax_url : '<?= AJAX_URL ?>';
    const ASSET_BASE   = <?= json_encode(rtrim(ASSET_URL, '/')) ?>;
    const itemShopHasLolFilters = true;

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
    const selectedDynamic = {};
    const selectedDeliveries = new Set();

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
       DROPDOWN FILTERS
    ════════════════════════════════════════════════════════════════ */
    function getSetForKind(kind) {
        kind = String(kind || '');
        if (kind === 'type') return selectedTypes;
        if (kind === 'server') return selectedServers;
        if (kind === 'delivery') return selectedDeliveries;
        if (kind.indexOf('dynamic:') === 0) {
            const key = kind.substring(8);
            if (!selectedDynamic[key]) selectedDynamic[key] = new Set();
            return selectedDynamic[key];
        }
        return null;
    }

    function updateFilterPillLabels() {
        $('.ifb-filter-wrap').each(function () {
            const kind = String($(this).data('filter-wrap') || '');
            const set = getSetForKind(kind);
            const count = set ? set.size : 0;
            $(this).find('.ifb-pill-val').text(count ? '(' + count + ')' : '');
        });
    }

    /* ════════════════════════════════════════════════════════════════
       ACTIVE FILTER CHIPS (mirrors account shop "No filters applied" row)
    ════════════════════════════════════════════════════════════════ */
    function renderActiveChips() {
        const $row = $('#activeFilters');
        const chips = [];

        function pushKindChips(kind, set) {
            set.forEach(v => {
                const $cb = $('.ifb-check[data-filter-kind="' + kind + '"][data-filter-value="' + v + '"]');
                const label = $cb.closest('.ifb-check-option').find('.ifb-check-label').text().trim() || v;
                chips.push({ label: label, remove: () => { set.delete(v); $cb.prop('checked', false); } });
            });
        }
        pushKindChips('type', selectedTypes);
        pushKindChips('server', selectedServers);
        pushKindChips('delivery', selectedDeliveries);
        Object.keys(selectedDynamic).forEach(k => pushKindChips('dynamic:' + k, selectedDynamic[k]));

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
        chips.forEach(c => {
            const $chip = $('<span class="ifb-chip"></span>').text(c.label);
            const $x = $('<span class="ifb-chip-x">✕</span>').on('click', function () { c.remove(); updateFilterPillLabels(); renderActiveChips(); triggerFiltersDebounced(); });
            $chip.append($x);
            $row.append($chip);
        });
    }

    $(document).on('change', '.ifb-check', function () {
        const kind = String($(this).data('filter-kind') || '');
        const value = String($(this).data('filter-value') || '');
        const set = getSetForKind(kind);
        if (!set || !value) return;
        if (this.checked) set.add(value); else set.delete(value);
        updateFilterPillLabels();
        renderActiveChips();
        triggerFiltersDebounced();
        syncQuickPills();
    });

    function syncQuickPills() {
        $('.ifb-quick-pill').each(function () {
            const set = getSetForKind(String($(this).data('quick-kind') || ''));
            $(this).toggleClass('is-active', !!set && set.has(String($(this).data('quick-value') || '')));
        });
    }

    function refreshQuickPillAvailability() {
        const $pills = $('.ifb-quick-pill');
        const $wrap = $('.lb-items-popular');
        if (!$pills.length || !$wrap.length) return;
        const request = {
            action: 'shop_popular_filter_counts',
            entity: 'items',
            game: String($('#itemShopGameSlug').val() || ''),
            game_id: String($('#itemShopGameId').val() || '0'),
            checks: []
        };
        $pills.each(function () {
            request.checks.push({
                kind: String($(this).data('quick-kind') || ''),
                value: String($(this).data('quick-value') || '')
            });
        });
        $.ajax({
            url: AJAX_URL_VAL,
            type: 'POST',
            dataType: 'json',
            data: request
        }).done(function (payload) {
            let visible = 0;
            $pills.each(function (index) {
                const show = !!(payload && payload.success && parseInt(payload.counts?.[index] || 0, 10) > 0);
                $(this).prop('hidden', !show).toggle(show);
                if (show) visible++;
            });
            $wrap.toggle(visible > 0);
        }).fail(function () {
            $wrap.hide();
        });
    }

    $(document).on('click', '.ifb-quick-pill', function () {
        const kind = String($(this).data('quick-kind') || '');
        const value = String($(this).data('quick-value') || '');
        const set = getSetForKind(kind);
        if (!set || !value) return;
        if (set.has(value)) set.delete(value); else set.add(value);
        $('.ifb-check[data-filter-kind="' + kind + '"][data-filter-value="' + value + '"]').prop('checked', set.has(value));
        syncQuickPills();
        updateFilterPillLabels();
        renderActiveChips();
        triggerFiltersDebounced();
    });

    $(document).on('click', '.ifb-filter-btn', function (e) {
        e.stopPropagation();
        const id = String($(this).data('filter-btn') || '');
        const $dd = $('#' + id + 'Dropdown');
        const wasOpen = $dd.hasClass('is-open');
        closeAllDropdowns();
        if (!wasOpen) {
            $dd.addClass('is-open');
            $(this).closest('.ifb-filter-wrap').addClass('is-open');
        }
    });

    $(document).on('click', '.ifb-generic-dropdown', function (e) { e.stopPropagation(); });
    $(document).on('click', '.ifb-generic-close', function (e) {
        e.preventDefault();
        $(this).closest('.ifb-generic-dropdown').removeClass('is-open');
        $(this).closest('.ifb-filter-wrap').removeClass('is-open');
    });

    function updateDeliveryLabel() { updateFilterPillLabels(); }

    /* ════════════════════════════════════════════════════════════════
       PRICE DROPDOWN (only dropdown left)
    ════════════════════════════════════════════════════════════════ */
    function closePriceDropdown() { $('#priceDropdown').removeClass('is-open'); $('#priceWrap').removeClass('is-open'); }
    function closeDeliveryDropdown() { $('.ifb-generic-dropdown').removeClass('is-open'); $('.ifb-filter-wrap').removeClass('is-open'); }
    function closeSortDropdown()  { $('#sortDropdown').removeClass('is-open'); $('#sortWrap').removeClass('is-open'); }
    function closeAllDropdowns()  { closePriceDropdown(); closeDeliveryDropdown(); closeSortDropdown(); }

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
        if (!$(e.target).closest('#priceWrap, #sortWrap, .ifb-filter-wrap').length) closeAllDropdowns();
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
        selectedTypes.clear(); selectedServers.clear(); selectedDeliveries.clear(); Object.keys(selectedDynamic).forEach(k => selectedDynamic[k].clear());
        $('.ifb-check').prop('checked', false); updateFilterPillLabels();
        $('#filterSearch').val('');
        $pMin.val(0); $pMax.val(priceLimitMax); $rMin.val(0); $rMax.val(priceLimitMax);
        priceTouched = false; sortMode = 'recommended'; $('#sortLabel').text('<?= t('Recommended') ?>');
        clampPrice(); renderActiveChips(); syncQuickPills(); resetToFirstPage(); fetchItems({ page: 1 });
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
        const gameSlug = ($('#itemShopGameSlug').val() || '').trim();
        const gameId = ($('#itemShopGameId').val() || '').trim();
        if (gameSlug) params.set('game_slug', gameSlug);
        if (gameId && gameId !== '0') params.set('game_id', gameId);
        params.set('page', String(page || 1));
        params.set('sort', sortMode);
        const search = ($('#filterSearch').val() || '').trim();
        if (search) params.set('search', search);
        if (itemShopHasLolFilters) {
            selectedTypes.forEach(t => params.append('types[]', t));
            selectedServers.forEach(s => params.append('servers[]', s));
            selectedDeliveries.forEach(d => params.append('delivery_times[]', d));
            Object.keys(selectedDynamic).forEach(k => { (selectedDynamic[k] || new Set()).forEach(v => params.append('schema_filters[' + k + '][]', v)); });
        }
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
        if (itemShopHasLolFilters) {
            selectedTypes.forEach(t => params.append('type', t));
            selectedServers.forEach(s => params.append('server', s));
            Object.keys(selectedDynamic).forEach(k => { (selectedDynamic[k] || new Set()).forEach(v => params.append(k, v)); });
            selectedDeliveries.forEach(d => params.append('delivery', d));
        }
        if (priceTouched) { params.set('min', $pMin.val() || '0'); params.set('max', $pMax.val() || String(priceLimitMax)); }
        const sortMap = { price_asc: 'price', price_desc: '-price', waiting_asc: 'waiting', waiting_desc: '-waiting', newest: 'newest', oldest: 'oldest', stock_desc: 'stock', sold_desc: 'sold' };
        if (sortMap[sortMode]) params.set('sort', sortMap[sortMode]);
        if (currentPage > 1) params.set('page', String(currentPage));
        window.history.replaceState({}, '', window.location.pathname + (params.toString() ? ('?' + params.toString()) : ''));
    }

    function applyStateFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const search = params.get('search') || ''; if (search) $('#filterSearch').val(search);
        if (itemShopHasLolFilters) {
            params.getAll('type').forEach(t => { selectedTypes.add(t); $('.ifb-type-tile[data-type="' + t + '"]').addClass('is-active'); });
            params.getAll('server').forEach(s => { selectedServers.add(s); $('.ifb-server-badge[data-server="' + s + '"]').addClass('is-active'); });
            $('.ifb-dynamic-option').each(function(){ const k=String($(this).data('filter-key')||''); const v=String($(this).data('filter-value')||''); if(k && v && params.getAll(k).indexOf(v)!==-1){ if(!selectedDynamic[k]) selectedDynamic[k]=new Set(); selectedDynamic[k].add(v); $(this).addClass('is-active'); } });
            params.getAll('delivery').forEach(d => { selectedDeliveries.add(d); $('.ifb-delivery-option[data-delivery="' + d + '"]').addClass('is-active'); });
            updateDeliveryLabel();
        }
        const sortRevMap = { price: 'price_asc', '-price': 'price_desc', waiting: 'waiting_asc', '-waiting': 'waiting_desc', newest: 'newest', oldest: 'oldest', stock: 'stock_desc', sold: 'sold_desc' };
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
    refreshQuickPillAvailability();
    fetchItems({ page: currentPage || 1 });

    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    // Do NOT force-scroll to the filterbar on page load — scrollToShopFilterbar()
    // is still used for the legitimate case (pagination changes page, see
    // pendingScrollToTop above), but calling it unconditionally here made every
    // fresh visit to this page jump down the page instead of loading at the top.
});
</script>
<?= $this->stop() ?>

<?= $this->push('styles') ?>
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
    position:relative;
    top:auto;
    z-index:100000;
    overflow:visible!important;
    isolation:isolate;
    box-shadow:none;
}
.lb-items-popular{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:13px 0 18px}
.ifb-popular-label{font-size:13px;font-weight:800;color:#eef2ff;margin-right:2px}
.ifb-quick-pill{appearance:none;border:1px solid rgba(255,255,255,.11);border-radius:999px;background:#111522;color:#f4f6ff;padding:7px 14px;font:800 13px/1 inherit;cursor:pointer;transition:border-color .16s ease,background .16s ease,color .16s ease,transform .16s ease}
.ifb-quick-pill:hover{border-color:rgba(104,117,255,.62);transform:translateY(-1px)}
.ifb-quick-pill.is-active{background:#5865f2;border-color:#7782ff;color:#fff}
@media(max-width:767px){
  .lb-items-popular{flex-wrap:nowrap;overflow-x:auto;scrollbar-width:none}
  .lb-items-popular::-webkit-scrollbar{display:none}
  .ifb-popular-label,.ifb-quick-pill{white-space:nowrap}
}
.items-filterbar:has(.ifb-generic-dropdown.is-open),
.items-filterbar:has(.ifb-price-dropdown.is-open),
.items-filterbar:has(.ifb-sort-dropdown.is-open){z-index:2147483000;}
.items-filterbar.is-loading{opacity:.7;pointer-events:none;}

/* Rows */
.ifb-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;overflow:visible!important;}
.ifb-row--types,.ifb-row--servers{padding-top:12px;border-top:1px solid rgba(255,255,255,.06);}
.ifb-row-label{font-size:12px;color:rgba(255,255,255,.45);white-space:nowrap;min-width:46px;}

/* Active filter chips row — mirrors the account shop's "No filters applied" strip */
.ifb-chips-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-height:20px;}
.ifb-chips-empty{font-size:13px;color:rgba(255,255,255,.4);}
.ifb-chip{display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:#151827;border:1px solid rgba(255,255,255,.075);color:rgba(255,255,255,.92);font-size:12px;font-weight:600;white-space:nowrap;}
.ifb-chip:hover{border-color:rgba(124,146,255,.30);}
.ifb-chip-x{opacity:.85;cursor:pointer;font-size:11px;}
.ifb-chip-x:hover{opacity:1;}

.items-shop-page .ifb-filter-wrap{position:relative;display:flex;align-items:center;flex:0 0 auto;z-index:2147483000;}
.items-shop-page .ifb-filter-btn{gap:8px;}
.items-shop-page .ifb-filter-wrap.is-open .ifb-filter-btn{background:#11162d;border-color:rgba(124,146,255,.34);}
.items-shop-page .ifb-generic-dropdown{position:absolute;top:calc(100% + 10px);left:0;z-index:2147483000;display:none;width:300px;max-width:min(300px,calc(100vw - 24px));overflow:hidden;border-radius:14px;background:#0d1021;border:1px solid rgba(255,255,255,.075);box-shadow:0 18px 60px rgba(0,0,0,.45);}
.items-shop-page .ifb-generic-dropdown.is-open{display:block;}
.items-shop-page .shop-empty,
.items-shop-page .items-grid-fixed,
.items-shop-page #shopPagination{position:relative;z-index:1;}
.items-shop-page .ifb-filter-wrap.is-open{z-index:2147483000;}
.items-shop-page .ifb-generic-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.06);font-weight:800;color:rgba(255,255,255,.92);letter-spacing:.2px;}
.items-shop-page .ifb-generic-close{width:34px;height:30px;border-radius:10px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.06);color:rgba(255,255,255,.9);cursor:pointer;}
.items-shop-page .ifb-generic-list{display:flex;flex-direction:column;gap:2px;max-height:340px;overflow:auto;padding:12px 14px;}
.items-shop-page .ifb-generic-list::-webkit-scrollbar{width:6px;}
.items-shop-page .ifb-generic-list::-webkit-scrollbar-thumb{background:rgba(124,146,255,.55);border-radius:999px;}
.items-shop-page .ifb-check-option{display:flex;align-items:center;gap:10px;min-height:40px;padding:10px 10px;border-radius:12px;border:none;background:transparent;color:rgba(255,255,255,.92);font-weight:600;font-size:13px;cursor:pointer;transition:.12s ease;user-select:none;}
.items-shop-page .ifb-check-option:hover{background:rgba(255,255,255,.04);}
.items-shop-page .ifb-check-option input{display:none;}
.items-shop-page .ifb-check-box{width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid rgba(255,255,255,.20);background:#151827;color:transparent;font-size:10px;flex:0 0 auto;accent-color:#6366f1;}
.items-shop-page .ifb-check-option input:checked + .ifb-check-box{background:#6366f1;border-color:#6366f1;color:#fff;}
.items-shop-page .ifb-check-label{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}


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

/* Clear */
.ifb-clear{height:46px;padding:0 18px;border-radius:13px;border:none;background:#6366f1;color:#fff;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;}
.ifb-clear:hover{opacity:.88;}

/* ── TYPE TILES ──────────────────────────────────────────────────────── */
.ifb-type-tiles{display:flex;flex-wrap:wrap;gap:8px;}
.ifb-type-tiles{display:flex;flex-wrap:wrap;gap:8px;}
.ifb-type-tile{
    display:inline-flex;align-items:center;gap:10px;
    padding:9px 16px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.1);
    background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.75);
    font-size:13px;font-weight:600;
    cursor:pointer;
    transition:border-color .15s,background .15s,color .15s;
    white-space:nowrap;
}
.ifb-type-tile:hover{border-color:rgba(109,92,255,.5);background:rgba(109,92,255,.12);color:#fff;}
.ifb-type-tile.is-active{border-color:#7c6fff;background:rgba(109,92,255,.28);color:#c4baff;}
.ifb-type-img{width:30px;height:30px;object-fit:contain;border-radius:4px;flex-shrink:0;display:block;}
.ifb-type-fa{font-size:18px;display:block;}

/* ── SERVER BADGES ───────────────────────────────────────────────────── */

.ifb-delivery-dropdown{min-width:260px;padding:0;overflow:hidden;}
.ifb-dropdown-title{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.08);font-weight:900;color:#fff;font-size:15px;}
.ifb-check-row{width:100%;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:transparent;border:0;color:rgba(255,255,255,.88);font-weight:800;text-align:left;}
.ifb-check-row:hover{background:rgba(255,255,255,.05);color:#fff;}
.ifb-check-box{width:18px;height:18px;border-radius:5px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.18);position:relative;flex:0 0 auto;}
.ifb-check-row.is-active .ifb-check-box{background:#6d5cff;border-color:#6d5cff;}
.ifb-check-row.is-active .ifb-check-box:after{content:'';position:absolute;left:5px;top:2px;width:5px;height:9px;border:2px solid #fff;border-top:0;border-left:0;transform:rotate(45deg);}
.ifb-server-badges{display:flex;flex-wrap:wrap;gap:6px;}
.ifb-server-badge{
    display:inline-flex;align-items:center;justify-content:center;
    min-width:42px;padding:6px 10px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.1);
    background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.6);
    font-size:12px;font-weight:600;
    cursor:pointer;
    transition:border-color .15s,background .15s,color .15s;
}
.ifb-server-badge:hover{border-color:rgba(109,92,255,.5);background:rgba(109,92,255,.12);color:#fff;}
.ifb-server-badge.is-active{border-color:#7c6fff;background:rgba(109,92,255,.28);color:#c4baff;}

/* ── PRICE DROPDOWN ──────────────────────────────────────────────────── */
.ifb-price-wrap,.ifb-delivery-wrap,.ifb-sort-wrap{position:relative;z-index:2147483000;}
.ifb-price-dropdown,.ifb-delivery-dropdown,.ifb-sort-dropdown{
    display:none;
    position:absolute;top:calc(100% + 10px);right:0;
    background:#0d1021;
    border:1px solid rgba(255,255,255,.075);
    border-radius:14px;
    padding:14px 16px;
    z-index:2147483000;
    min-width:260px;
    box-shadow:0 18px 60px rgba(0,0,0,.45);
}
.ifb-price-dropdown.is-open,.ifb-delivery-dropdown.is-open,.ifb-sort-dropdown.is-open{display:block;}

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

/* Open state on the shared Price/Sort pill wraps */
#priceWrap.is-open .ifb-pill,
#sortWrap.is-open .ifb-pill{background:#11162d;border-color:rgba(124,146,255,.34);}

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

/* ── Card image: airy right-side thumbnail, title never truncated ── */
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
.item-shop-badge{min-width:0;width:auto;height:auto;background:rgba(255,255,255,.045)!important;color:rgba(239,242,255,.78)!important;border:1px solid rgba(255,255,255,.075)!important;display:flex;align-items:center;gap:6px;border-radius:9px;padding:6px 11px;font-size:10.5px!important;font-weight:750;white-space:nowrap;}
.item-shop-badge i{width:10px;flex:0 0 10px;text-align:center;font-size:9px!important;color:#8ea5ff!important}.item-shop-badge .item-type-img{width:10px!important;height:10px!important}
.item-shop-badge--waiting{background:rgba(99,102,241,.16);border:1px solid rgba(129,140,248,.38);color:#e5e7ff;}
.item-shop-badge--waiting i{color:#a5b4fc;}
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
    .items-filterbar{position:relative;top:unset;z-index:100000;overflow:visible!important;}
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


@media (max-width:768px){
    .items-shop-page .ifb-row--top{display:grid;grid-template-columns:1fr 1fr;}
    .items-shop-page .ifb-search,.items-shop-page .ifb-spacer{grid-column:1/-1;width:100%;}
    .items-shop-page .ifb-filter-wrap,.items-shop-page .ifb-filter-btn,.items-shop-page .ifb-price-wrap,.items-shop-page .ifb-sort-wrap,.items-shop-page .ifb-clear{width:100%;}
    .items-shop-page .ifb-generic-dropdown.is-open{position:fixed;left:12px!important;right:12px!important;top:12px!important;bottom:12px!important;width:auto!important;max-width:none!important;}
    .items-shop-page .ifb-generic-list{max-height:calc(100vh - 86px);}
}

</style>
<?= $this->stop() ?>

<style id="lb-empty-bg-cleanup-final">
/* Remove empty state grid and glow backgrounds on marketplace coming soon pages. */
.lb-shop-empty-notify-offset,
.lb-shop-empty-notify-offset::before,
.lb-shop-empty-notify-offset::after,
.lb-cs2,
.lb-cs2::before,
.lb-cs2::after,
.lb-cs2__grid,
.lb-cs2__grid::before,
.lb-cs2__grid::after,
.lb-cs2__aurora,
.lb-cs2__aurora::before,
.lb-cs2__aurora::after,
.lb-topups--empty,
.lb-topups--empty::before,
.lb-topups--empty::after{
  background-image:none !important;
  -webkit-mask-image:none !important;
  mask-image:none !important;
}
.lb-cs2__grid,
.lb-cs2__aurora{
  display:none !important;
}
</style>


<style id="lb-mobile-hero-notify-final-fix">
@media(max-width:760px){
  body .lb-shop-hero{background:#0f0c1f!important;border-bottom:1px solid rgba(255,255,255,.06)!important;margin-bottom:0!important;}
  body .lb-shop-hero__inner{padding:12px 16px 18px!important;gap:10px!important;align-items:flex-start!important;}
  body .lb-shop-hero__icon{width:38px!important;height:38px!important;min-width:38px!important;border-radius:12px!important;}
  body .lb-shop-hero__icon img{width:25px!important;height:25px!important;border-radius:8px!important;}
  body .lb-shop-hero__title{font-size:17px!important;line-height:1.18!important;}
  body .lb-shop-hero__desc{font-size:12px!important;line-height:1.32!important;margin-top:5px!important;}
  body .container{padding-top:14px!important;}
  body .shop-filterbar{margin-top:0!important;}
  body .lb-shop-empty-notify-offset{
    --lb-empty-top-gap:74px!important;
    --lb-empty-bottom-gap:74px!important;
    min-height:calc(100svh - var(--lb-empty-page-chrome, 250px))!important;
    padding-left:16px!important;
    padding-right:16px!important;
  }
}
@media(max-width:420px){
  body .lb-shop-hero__inner{padding:12px 14px 16px!important;}
  body .container{padding-top:12px!important;}
  body .lb-shop-empty-notify-offset{--lb-empty-top-gap:78px!important;--lb-empty-bottom-gap:72px!important;}
}
</style>


<style id="lb-dynamic-header-seat-final-v2">
html{scroll-padding-top:calc(var(--lb-content-top, 0px) + 18px)!important;}
main > .lb-shop-hero:first-child,
.page-zoom > main > .lb-shop-hero:first-child{
  margin-top:var(--lb-content-top, 0px)!important;
}
body.ranked-accounts-page .lb-shop-hero,
body.items-shop-page .lb-shop-hero{
  background:#0e0c1c!important;
  border:0!important;
  border-bottom:0!important;
}
body.ranked-accounts-page .lb-shop-empty-notify-offset,
body.items-shop-page .lb-shop-empty-notify-offset{
  padding-top:calc(var(--lb-content-top, 0px) + 42px)!important;
  padding-bottom:72px!important;
  min-height:calc(100svh - var(--lb-content-top, 0px))!important;
}
@media(max-width:760px){
  main > .lb-shop-hero:first-child,
  .page-zoom > main > .lb-shop-hero:first-child{
    margin-top:var(--lb-content-top, 0px)!important;
  }
  body.ranked-accounts-page .lb-shop-empty-notify-offset,
  body.items-shop-page .lb-shop-empty-notify-offset{
    padding-top:calc(var(--lb-content-top, 0px) + 22px)!important;
    padding-bottom:108px!important;
    min-height:calc(100svh - var(--lb-content-top, 0px))!important;
  }
}
</style>

<style id="lb-coming-soon-unified-position-final">
/* Final coming-soon seat: one shared position for accounts, items and top-ups.
   Uses the measured header/gamebar height, so Sale Banner changes and mobile bars cannot overlap it. */
body.ranked-accounts-page .lb-shop-empty-notify-offset,
body.items-shop-page .lb-shop-empty-notify-offset{
  padding-top:calc(var(--lb-content-top, 0px) + 42px)!important;
  padding-bottom:88px!important;
  min-height:calc(100svh - var(--lb-content-top, 0px))!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
}
body.ranked-accounts-page .lb-shop-empty-notify-offset > .lb-cs2,
body.items-shop-page .lb-shop-empty-notify-offset > .lb-cs2{
  margin:0 auto!important;
}
@media(max-width:760px){
  body.ranked-accounts-page .lb-shop-empty-notify-offset,
  body.items-shop-page .lb-shop-empty-notify-offset{
    padding-top:calc(var(--lb-content-top, 0px) + 30px)!important;
    padding-bottom:128px!important;
    min-height:calc(100svh - var(--lb-content-top, 0px))!important;
    padding-left:16px!important;
    padding-right:16px!important;
  }
}
</style>

<style id="lb-shop-hero-no-background">
/* Hero has no colour of its own — it shows the page background. */
html body .lb-shop-hero,
html body.items-shop-page .lb-shop-hero,
html body.lol-items-page .lb-shop-hero,
html body main > .lb-shop-hero:first-child,
html body .page-zoom > main > .lb-shop-hero:first-child{
  background:transparent !important;
  background-color:transparent !important;
  background-image:none !important;
  box-shadow:none !important;
}
html body .lb-shop-hero::before,
html body .lb-shop-hero::after{
  content:none !important;
  display:none !important;
  background:none !important;
}
</style>
