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

$game       = $game       ?? 'lol';
$gameConfig = $gameConfig ?? [];
$pagination = $pagination ?? [];
// Map full slug to short code for sub-templates (account-cards.php expects 'lol'/'val'/'tft')
$_slugToShort = [
    'league-of-legends' => 'lol',
    'valorant'          => 'val',
    'teamfight-tactics' => 'tft',
    'call-of-duty'      => 'cod',
];
$gameShort = $_slugToShort[$game] ?? $game;

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
$data = lb_enrich_rows_with_seller_stats((array)($data ?? []));

$gameConfig     = $gameConfig ?? [];
$game           = $game ?? 'lol';

// Fallback-Defaults pro Game (greift wenn SQL-Migration noch nicht ausgeführt wurde)
$_defaults = [
    'lol' => [
        'page_title'       => 'LoL Ranked Accounts',
        'page_description' => 'Buy ranked premium League of Legends accounts with champions, skins, and included email access. Fast delivery, secure login details, and a minimum 14-day warranty—start playing instantly.',
        'filters'          => ['server', 'rank', 'roles', 'price'],
        'servers'          => ['euw','eune','na','br','tr','kr','jp','oce'],
        'ranks'            => ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'],
        'roles'            => ['TopLane','Jungle','MidLane','AdCarry','Support'],
        'show_type_cards'  => false,
    ],
    'val' => [
        'page_title'       => 'Valorant Ranked Accounts',
        'page_description' => 'Buy ranked Valorant accounts. Verified sellers, instant delivery, and secure login details.',
        'filters'          => ['server', 'rank', 'price'],
        'servers'          => ['eu','na','ap','kr','br','latam'],
        'ranks'            => ['Iron','Bronze','Silver','Gold','Platinum','Diamond','Ascendant','Immortal','Radiant'],
        'roles'            => [],
        'show_type_cards'  => false,
    ],

    'cod' => [
        'page_title'       => 'Call of Duty Accounts',
        'page_description' => 'Buy Call of Duty accounts with the right platform, games, level, unlocks, camos and CoD points.',
        'filters'          => ['platform', 'cod_titles', 'level', 'price'],
        'platforms'        => ['PC (Game Pass)', 'PlayStation', 'Xbox One', 'BattleNet', 'Steam', 'All Platforms'],
        'cod_titles'       => ['Black Ops 6', 'Black Ops 7', 'Modern Warfare', 'Modern Warfare 2', 'Modern Warfare 3', 'Black Ops / Warzone 1', 'Warzone', 'Warzone 2'],
        'level_min'        => 0,
        'level_max'        => 1000,
        'show_type_cards'  => false,
    ],
    'call-of-duty' => [
        'page_title'       => 'Call of Duty Accounts',
        'page_description' => 'Buy Call of Duty accounts with the right platform, games, level, unlocks, camos and CoD points.',
        'filters'          => ['platform', 'cod_titles', 'level', 'price'],
        'platforms'        => ['PC (Game Pass)', 'PlayStation', 'Xbox One', 'BattleNet', 'Steam', 'All Platforms'],
        'cod_titles'       => ['Black Ops 6', 'Black Ops 7', 'Modern Warfare', 'Modern Warfare 2', 'Modern Warfare 3', 'Black Ops / Warzone 1', 'Warzone', 'Warzone 2'],
        'level_min'        => 0,
        'level_max'        => 1000,
        'show_type_cards'  => false,
    ],
];
// Load from game_services config if not already loaded
if (empty($gameConfig) && function_exists('util_get_accounts_page_config')) {
    $gameConfig = util_get_accounts_page_config($game) ?: [];
}
$_fallback = $_defaults[$game] ?? [
    'page_title'       => (function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($game)['name'] ?? ucwords(str_replace('-', ' ', $game))) : ucwords(str_replace('-', ' ', $game))) . ' Accounts',
    'page_description' => '',
    'filters'          => ['server', 'rank', 'price'],
    'servers'          => [],
    'ranks'            => [],
    'roles'            => [],
    'show_type_cards'  => false,
];
// Merge: DB-Config überschreibt Fallback, aber leere DB-Werte fallen auf Fallback zurück
foreach ($_fallback as $_k => $_v) {
    if (!isset($gameConfig[$_k]) || $gameConfig[$_k] === '' || $gameConfig[$_k] === []) {
        $gameConfig[$_k] = $_v;
    }
}

$pageTitle      = $gameConfig['page_title']       ?? ($meta['h1'] ?? 'Accounts');
$pageDesc       = $gameConfig['page_description'] ?? '';
if ($pageDesc === '') {
    $pageDesc = 'Buy ' . $pageTitle . ' securely with verified sellers, fast delivery, and buyer protection.';
}
$activeFilters  = $gameConfig['filters']          ?? ['server', 'rank', 'price'];
$servers        = $gameConfig['servers']          ?? [];
$ranks          = $gameConfig['ranks']            ?? [];
$roles          = $gameConfig['roles']            ?? [];
$platforms      = $gameConfig['platforms']        ?? ['PC (Game Pass)', 'PlayStation', 'Xbox One', 'BattleNet', 'Steam', 'All Platforms'];
$codTitles      = $gameConfig['cod_titles']       ?? ['Black Ops 6', 'Black Ops 7', 'Modern Warfare', 'Modern Warfare 2', 'Modern Warfare 3', 'Black Ops / Warzone 1', 'Warzone', 'Warzone 2'];
$levelMin       = (int)($gameConfig['level_min']  ?? 0);
$levelMax       = (int)($gameConfig['level_max']  ?? 1000);
$showTypeCards  = false;

$accountSchema = function_exists('util_get_game_account_schema') ? util_get_game_account_schema($game) : [];
$schemaFilterFields = function_exists('util_account_schema_filter_fields') ? util_account_schema_filter_fields($game) : [];
$useDynamicAccountSchema = !empty($schemaFilterFields);

$hasServer   = in_array('server',     $activeFilters, true);
$hasRank     = in_array('rank',       $activeFilters, true);
$hasRoles    = in_array('roles',      $activeFilters, true) && !empty($roles);
$hasPlatform = in_array('platform',   $activeFilters, true) && !empty($platforms);
$hasCodGame  = in_array('cod_titles', $activeFilters, true) && !empty($codTitles);
$hasLevel    = in_array('level',      $activeFilters, true);
$hasPrice    = in_array('price',      $activeFilters, true);
if ($useDynamicAccountSchema) {
    // When a game has a schema, filters come from game_account_schemas instead of hardcoded PHP blocks.
    $hasServer = $hasRank = $hasRoles = $hasPlatform = $hasCodGame = $hasLevel = false;
    $hasPrice = true;
}

if (!function_exists('account_shop_field_icon_class')) {
    function account_shop_field_icon_class(array $field): string {
        $icon = trim((string)($field['icon'] ?? ''));
        if ($icon !== '') return $icon;
        $type = (string)($field['type'] ?? '');
        $key = (string)($field['key'] ?? '');
        if (($field['icon_type'] ?? '') === 'platform' || $key === 'platform') return 'fa-solid fa-desktop';
        if ($type === 'number' || ($field['filter_type'] ?? '') === 'range') return 'fa-solid fa-sliders';
        return 'fa-solid fa-filter';
    }
}

if (!function_exists('account_shop_render_dynamic_filter')) {
    function account_shop_render_dynamic_filter(array $field): string {
        $key = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($field['key'] ?? ''));
        if ($key === '' || $key === 'price') return '';
        $label = htmlspecialchars((string)($field['label'] ?? ucwords(str_replace('_',' ', $key))), ENT_QUOTES, 'UTF-8');
        $filterType = (string)($field['filter_type'] ?? $field['type'] ?? 'select');
        $type = (string)($field['type'] ?? 'text');
        $id = 'filterDyn' . preg_replace('/[^a-zA-Z0-9]/', '', ucwords(str_replace('_',' ', $key)));
        $dd = 'ddDyn' . preg_replace('/[^a-zA-Z0-9]/', '', ucwords(str_replace('_',' ', $key)));
        $icon = htmlspecialchars(account_shop_field_icon_class($field), ENT_QUOTES, 'UTF-8');
        $html = '<div class="shop-filterpill shop-filterpill--dynamic" data-dropdown="' . $dd . '" data-filter-key="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<button type="button" class="shop-filterpill__btn"><i class="' . $icon . '"></i><span class="shop-filterpill__label">' . $label . '</span><span class="shop-filterpill__value"></span><i class="fa-solid fa-caret-down"></i></button>';
        $html .= '<div class="shop-dropdown" id="' . $dd . '"><div class="shop-dropdown__head"><span>' . $label . '</span><button type="button" class="shop-dropdown__close" data-close="' . $dd . '">✕</button></div><div class="shop-dropdown__body">';
        if ($filterType === 'range' || $type === 'number') {
            $min = isset($field['min']) ? (int)$field['min'] : 0;
            $max = isset($field['max']) ? (int)$field['max'] : 1000;
            if ($max <= $min) $max = $min + 1000;
            $safeKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            $html .= '<div class="shop-price js-dual-range" data-range-key="' . $safeKey . '" data-range-label="' . $label . '">';
            $html .= '<div class="shop-price__fields">';
            $html .= '<div class="shop-price__field"><label>From</label><div class="shop-price__input"><input type="number" name="' . $safeKey . '_min" data-range-min-input min="' . $min . '" max="' . $max . '" value="' . $min . '"></div></div>';
            $html .= '<div class="shop-price__sep">-</div>';
            $html .= '<div class="shop-price__field"><label>To</label><div class="shop-price__input"><input type="number" name="' . $safeKey . '_max" data-range-max-input min="' . $min . '" max="' . $max . '" value="' . $max . '"></div></div>';
            $html .= '</div>';
            $html .= '<div class="shop-range" data-range><input type="range" data-range-min min="' . $min . '" max="' . $max . '" value="' . $min . '" step="1"><input type="range" data-range-max min="' . $min . '" max="' . $max . '" value="' . $max . '" step="1"><div class="shop-range__track"><div class="shop-range__fill"></div></div></div>';
            $html .= '<div class="shop-price__labels"><span data-range-label-min>' . $min . '</span><span data-range-label-max>' . $max . '</span></div>';
            $html .= '</div>';
        } elseif ($filterType === 'checkbox' || $type === 'checkbox') {
            $html .= '<label class="facet-check"><input type="checkbox" name="' . $key . '" value="1"><span>' . $label . '</span></label>';
        } else {
            $name = ($key === 'rank') ? 'ranks[]' : (($key === 'server') ? 'servers[]' : ($key . '[]'));
            $html .= '<select class="js-facet-source" id="' . $id . '" multiple data-facet-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
            foreach (($field['options'] ?? []) as $opt) {
                $optVal = is_array($opt) ? (string)($opt['value'] ?? $opt['label'] ?? '') : (string)$opt;
                $optLab = is_array($opt) ? (string)($opt['label'] ?? $optVal) : (string)$opt;
                $html .= '<option value="' . htmlspecialchars($optVal, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($optLab, ENT_QUOTES, 'UTF-8') . '</option>';
            }
            $html .= '</select><div class="facet-list" data-facet="' . $id . '"></div>';
        }
        $html .= '</div></div></div>';
        return $html;
    }
}
?>
<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'ranked-accounts-page']) ?>

<?php
$csAccountsGameIcon = '';
if (function_exists('util_get_game_by_slug')) {
    $csAccountsGameIcon = (string)(util_get_game_by_slug($game)['icon'] ?? '');
}
?>
<?php $lbTotalAccountsForHero = (int)($pagination['totalItems'] ?? count((array)($data ?? []))); ?>
<?php if ($lbTotalAccountsForHero > 0): ?>
<section class="lb-shop-hero">
    <div class="lb-shop-hero__inner">
        <div class="lb-shop-hero__icon" aria-hidden="true"><i class="fa-solid fa-helmet-battle"></i></div>
        <div>
            <div class="lb-shop-hero__kicker">Accounts</div>
            <h1 class="lb-shop-hero__title"><?= htmlspecialchars($pageTitle) ?></h1>
            <?php if ($pageDesc): ?><p class="lb-shop-hero__desc"><?= htmlspecialchars($pageDesc) ?></p><?php endif ?>
        </div>
    </div>
</section>
<?php endif; ?>
<style>
.lb-shop-hero{position:relative;border-bottom:1px solid rgba(255,255,255,.06);background:#0e0c1c;overflow:hidden;margin:0;padding:0;}
.lb-shop-hero__inner{max-width:1500px;margin:0 auto;display:flex;align-items:center;gap:22px;min-height:170px;padding:36px 28px;}
.lb-shop-hero__icon{width:74px;height:74px;min-width:74px;border-radius:20px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;box-shadow:0 18px 50px rgba(0,0,0,.28);overflow:hidden;}
.lb-shop-hero__icon i{font-size:30px;color:#7c6cff;}
.lb-shop-hero__kicker{font-size:12px;letter-spacing:.13em;text-transform:uppercase;color:#8b9bff;font-weight:900;margin-bottom:8px;}
.lb-shop-hero__title{margin:0;font-size:29px;line-height:1.12;font-weight:950;letter-spacing:-.03em;color:#fff;}
.lb-shop-hero__desc{margin:8px 0 0;color:#a9adc4;font-size:15px;max-width:640px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
@media(max-width:760px){
    .lb-shop-hero{overflow:visible!important;background:#0e0c1c!important;border-bottom:1px solid rgba(255,255,255,.06)!important;margin-bottom:0!important;}
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

<div class="container">
<div id="accountsTop" style="height:1px;"></div>

    <?php
    // Determine up front whether this game has ANY account listings at all.
    // If not, we skip rendering the filterbar / grid / toolbar entirely instead
    // of hiding them with CSS, so nothing extra gets loaded or initialized.
    $lbTotalAccounts = (int)($pagination['totalItems'] ?? count((array)($data ?? [])));
    ?>

    <?php if ($lbTotalAccounts <= 0): ?>

        <div class="lb-shop-empty-notify-offset">
            <?= $this->insert('website/pages/components/coming-soon-notify', [
                'game' => $game,
                'gameConfig' => ['name' => $pageTitle ?? 'Accounts'],
                'gameIcon' => $csAccountsGameIcon,
                'service' => 'accounts',
                'title' => 'Coming soon',
                'text' => 'There are no account listings for this game yet. Leave your email and we will notify you as soon as accounts are available.'
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

    <!-- Anchor and layout placeholder for the desktop filter navigation. -->
    <div id="accountsTop" style="height:1px;"></div>

    <div class="shop-filterbar shop-filterbar--sticky" id="shopFilterbar">
        <form id="shopFilters" class="shop-filterbar__form">
            <input type="hidden" name="action" value="account_shop_filters">
            <input type="hidden" name="game"   value="<?= htmlspecialchars($game) ?>">

            <div class="shop-filterbar__row">
                <!-- Search -->
                <div class="shop-filterbar__search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="<?= t('Search...') ?>" id="filterSearch">
                </div>

                <?php if ($useDynamicAccountSchema): ?>
                    <?php foreach ($schemaFilterFields as $_schemaFilterField): ?>
                        <?= account_shop_render_dynamic_filter($_schemaFilterField) ?>
                    <?php endforeach ?>
                <?php endif ?>


                <?php if ($hasPlatform): ?>
                <!-- PILL: Platform -->
                <div class="shop-filterpill" data-dropdown="ddPlatform">
                    <button type="button" class="shop-filterpill__btn" id="btnPlatform">
                        <i class="fa-solid fa-desktop"></i>
                        <span class="shop-filterpill__label"><?= t('Platform') ?></span>
                        <span class="shop-filterpill__value" id="valPlatform"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddPlatform">
                        <div class="shop-dropdown__head">
                            <span><?= t('Platform') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddPlatform">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <select class="js-facet-source" id="filterPlatform" multiple data-facet-name="platform[]">
                                <?php foreach ($platforms as $platform): ?>
                                <option value="<?= htmlspecialchars($platform) ?>"><?= htmlspecialchars($platform) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="facet-list" data-facet="filterPlatform"></div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if ($hasCodGame): ?>
                <!-- PILL: Game -->
                <div class="shop-filterpill" data-dropdown="ddCodTitles">
                    <button type="button" class="shop-filterpill__btn" id="btnCodTitles">
                        <i class="fa-solid fa-gamepad"></i>
                        <span class="shop-filterpill__label"><?= t('Game') ?></span>
                        <span class="shop-filterpill__value" id="valCodTitles"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddCodTitles">
                        <div class="shop-dropdown__head">
                            <span><?= t('Game') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddCodTitles">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <select class="js-facet-source" id="filterCodTitles" multiple data-facet-name="cod_titles[]">
                                <?php foreach ($codTitles as $title): ?>
                                <option value="<?= htmlspecialchars($title) ?>"><?= htmlspecialchars($title) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="facet-list" data-facet="filterCodTitles"></div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if ($hasLevel): ?>
                <!-- PILL: Level -->
                <div class="shop-filterpill" data-dropdown="ddLevel">
                    <button type="button" class="shop-filterpill__btn" id="btnLevel">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span class="shop-filterpill__label"><?= t('Level') ?></span>
                        <span class="shop-filterpill__value" id="valLevel"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddLevel">
                        <div class="shop-dropdown__head">
                            <span><?= t('Level') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddLevel">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <div class="shop-price">
                                <div class="shop-price__fields">
                                    <div class="shop-price__field">
                                        <label><?= t('From') ?></label>
                                        <input type="number" name="level_min" id="levelMin" min="<?= $levelMin ?>" max="<?= $levelMax ?>" value="<?= $levelMin ?>">
                                    </div>
                                    <div class="shop-price__sep">-</div>
                                    <div class="shop-price__field">
                                        <label><?= t('To') ?></label>
                                        <input type="number" name="level_max" id="levelMax" min="<?= $levelMin ?>" max="<?= $levelMax ?>" value="<?= $levelMax ?>">
                                    </div>
                                </div>
                                <div class="shop-range" data-range>
                                    <input type="range" id="levelRangeMin" min="<?= $levelMin ?>" max="<?= $levelMax ?>" value="<?= $levelMin ?>" step="1">
                                    <input type="range" id="levelRangeMax" min="<?= $levelMin ?>" max="<?= $levelMax ?>" value="<?= $levelMax ?>" step="1">
                                    <div class="shop-range__track"><div class="shop-range__fill" id="levelRangeFill"></div></div>
                                </div>
                                <div class="shop-price__labels">
                                    <span id="levelLabelMin"><?= $levelMin ?></span>
                                    <span id="levelLabelMax"><?= $levelMax ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if ($hasServer && !empty($servers)): ?>
                <!-- PILL: Server -->
                <div class="shop-filterpill" data-dropdown="ddServer">
                    <button type="button" class="shop-filterpill__btn" id="btnServer">
                        <i class="fa-solid fa-globe"></i>
                        <span class="shop-filterpill__label"><?= t('Server') ?></span>
                        <span class="shop-filterpill__value" id="valServer"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddServer">
                        <div class="shop-dropdown__head">
                            <span><?= t('Server') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddServer">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <div class="facet-search">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" class="facet-search__input" placeholder="<?= t('Search Server') ?>..." data-search-for="ddServer">
                            </div>
                            <select class="js-facet-source" id="filterServer" multiple data-facet-name="servers[]">
                                <?php foreach ($servers as $srv): ?>
                                <option value="<?= htmlspecialchars($srv) ?>"><?= htmlspecialchars(strtoupper($srv)) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="facet-list" data-facet="filterServer"></div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if ($hasRank && !empty($ranks)): ?>
                <!-- PILL: Rank -->
                <div class="shop-filterpill" data-dropdown="ddRank">
                    <button type="button" class="shop-filterpill__btn" id="btnRank">
                        <i class="fa-solid fa-medal"></i>
                        <span class="shop-filterpill__label"><?= t('Rank') ?></span>
                        <span class="shop-filterpill__value" id="valRank"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddRank">
                        <div class="shop-dropdown__head">
                            <span><?= t('Rank') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddRank">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <div class="facet-search">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" class="facet-search__input" placeholder="<?= t('Search Rank') ?>..." data-search-for="ddRank">
                            </div>
                            <select class="js-facet-source" id="filterRank" multiple data-facet-name="ranks[]">
                                <?php foreach ($ranks as $i => $label): ?>
                                <option value="<?= (int)$i ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="facet-list" data-facet="filterRank"></div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if ($hasRoles && !empty($roles)): ?>
                <!-- PILL: Roles -->
                <div class="shop-filterpill" data-dropdown="ddRoles">
                    <button type="button" class="shop-filterpill__btn" id="btnRoles">
                        <i class="fa-solid fa-asterisk shop-filterpill__iconfa"></i>
                        <span class="shop-filterpill__label"><?= t('Roles') ?></span>
                        <span class="shop-filterpill__value" id="valRoles"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddRoles">
                        <div class="shop-dropdown__head">
                            <span><?= t('Roles') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddRoles">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <div class="facet-search">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" class="facet-search__input" placeholder="<?= t('Search Roles') ?>..." data-search-for="filterRoles">
                            </div>
                            <select class="js-facet-source" id="filterRoles" multiple data-facet-name="roles[]">
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="facet-list" data-facet="filterRoles"></div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <?php if ($hasPrice): ?>
                <!-- PILL: Price -->
                <div class="shop-filterpill" data-dropdown="ddPrice">
                    <button type="button" class="shop-filterpill__btn" id="btnPrice">
                        <i class="fa-solid fa-dollar-sign"></i>
                        <span class="shop-filterpill__label"><?= t('Price') ?></span>
                        <span class="shop-filterpill__value" id="valPrice"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddPrice">
                        <div class="shop-dropdown__head">
                            <span><?= t('Price') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddPrice">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <div class="shop-price">
                                <div class="shop-price__fields">
                                    <div class="shop-price__field">
                                        <label><?= t('From') ?></label>
                                        <div class="shop-price__input">
                                            <span class="shop-price__prefix">€</span>
                                            <input type="number" name="price_min" id="priceMin" min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="shop-price__sep">-</div>
                                    <div class="shop-price__field">
                                        <label><?= t('To') ?></label>
                                        <div class="shop-price__input">
                                            <span class="shop-price__prefix">€</span>
                                            <input type="number" name="price_max" id="priceMax" min="0" value="7000">
                                        </div>
                                    </div>
                                </div>
                                <div class="shop-range" data-range>
                                    <input type="range" id="priceRangeMin" min="0" max="7000" value="0" step="1">
                                    <input type="range" id="priceRangeMax" min="0" max="7000" value="7000" step="1">
                                    <div class="shop-range__track">
                                        <div class="shop-range__fill" id="priceRangeFill"></div>
                                    </div>
                                </div>
                                <div class="shop-price__labels">
                                    <span id="priceLabelMin">€0</span>
                                    <span id="priceLabelMax">€7.000</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <!-- PILL: More Filters -->
                <div class="shop-filterpill" data-dropdown="ddMore">
                    <button type="button" class="shop-filterpill__btn" id="btnMore">
                        <i class="fa-solid fa-sliders"></i>
                        <span class="shop-filterpill__label"><?= t('More Filters') ?></span>
                        <span class="shop-filterpill__value" id="valMore"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddMore">
                        <div class="shop-dropdown__head">
                            <span><?= t('More Filters') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddMore">✕</button>
                        </div>
                        <div class="shop-dropdown__body">
                            <div class="mf-view is-active" data-view="menu">
                                <div class="mf-menu">
                                    <button type="button" class="mf-menuitem" data-mf-open="delivery">
                                        <span class="mf-menuitem__left"><i class="fa-solid fa-bolt"></i></span>
                                        <span class="mf-menuitem__label"><?= t('Delivery Type') ?></span>
                                        <span class="mf-menuitem__right">›</span>
                                    </button>
                                </div>
                            </div>
                            <div class="mf-view" data-view="delivery">
                                <div class="mf-panelhead">
                                    <button type="button" class="mf-back" data-mf-back aria-label="Back">←</button>
                                    <div class="mf-title"><?= t('Delivery Type') ?></div>
                                </div>
                                <div class="mf-panelbody">
                                    <select class="js-facet-source" id="filterDeliveryType" multiple data-facet-name="delivery_type[]">
                                        <option value="instant"><?= t('Instant Delivery') ?></option>
                                        <option value="manual"><?= t('Manual Delivery') ?></option>
                                    </select>
                                    <div class="facet-list" data-facet="filterDeliveryType"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="shop-filterbar__actions">
                    <button type="button" class="reset-filters reset-filters--ghost"><?= t('Clear All') ?></button>
                    <div class="shop-sort" data-dropdown="ddSort">
                        <button type="button" class="shop-sort__btn" id="btnSort" aria-expanded="false">
                            <i class="fa-solid fa-arrow-up-wide-short"></i>
                            <span id="sortLabel"><?= t('Recommended') ?></span>
                            <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="shop-dropdown shop-dropdown--menu" id="ddSort">
                            <div class="shop-dropdown__head">
                                <span><?= t('Sort By') ?></span>
                                <button type="button" class="shop-dropdown__close" data-close="ddSort">✕</button>
                            </div>
                            <div class="shop-dropdown__body">
                                <button type="button" class="shop-menuitem" data-sort="recommended"><i class="fa-solid fa-wand-magic-sparkles"></i> <?= t('Recommended') ?></button>
                                <button type="button" class="shop-menuitem" data-sort="price_asc"><i class="fa-solid fa-tag"></i> <?= t('Lowest Price') ?></button>
                                <button type="button" class="shop-menuitem" data-sort="price_desc"><i class="fa-solid fa-sack-dollar"></i> <?= t('Highest Price') ?></button>
                                <button type="button" class="shop-menuitem" data-sort="delivery_fast"><i class="fa-solid fa-bolt"></i> <?= t('Fastest Delivery') ?></button>
                                <button type="button" class="shop-menuitem" data-sort="newest"><i class="fa-regular fa-clock"></i> <?= t('Newest') ?></button>
                                <button type="button" class="shop-menuitem" data-sort="oldest"><i class="fa-solid fa-clock-rotate-left"></i> <?= t('Oldest') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shop-filterbar__chips" id="activeFilters"></div>
        </form>
    </div>

    <div class="lb-generic-popular" id="lbGenericPopular" hidden>
        <span class="lb-generic-popular__label"><?= t('Most popular') ?>:</span>
        <div class="lb-generic-popular__list"></div>
    </div>

    <?= $this->insert('website/components/accounts/shop-filter-nav') ?>

    <style id="lb-generic-shop-subnav-scroll">
    .lb-generic-popular{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:14px 0 18px}
    .lb-generic-popular[hidden]{display:none!important}
    .lb-generic-popular__label{font-size:13px;font-weight:800;color:#eef2ff}
    .lb-generic-popular__list{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .lb-generic-popular__pill{appearance:none;border:1px solid rgba(255,255,255,.11);border-radius:999px;background:#111522;color:#f4f6ff;padding:7px 14px;font:800 13px/1 inherit;cursor:pointer;transition:border-color .16s ease,background .16s ease,color .16s ease,transform .16s ease}
    .lb-generic-popular__pill:hover{border-color:rgba(104,117,255,.62);transform:translateY(-1px)}
    .lb-generic-popular__pill.is-active{background:#5865f2;border-color:#7782ff;color:#fff}
    @media (min-width:1025px){
      body.ranked-accounts-page.lb-shop-filter-nav-active .lb-game-subnav{
        display:none!important;
      }
    }
    @media (max-width:767px){
      .lb-generic-popular{margin:10px 0 16px;align-items:flex-start}
      .lb-generic-popular__list{flex-wrap:nowrap;overflow-x:auto;width:100%;padding-bottom:3px;scrollbar-width:none}
      .lb-generic-popular__list::-webkit-scrollbar{display:none}
      .lb-generic-popular__pill{white-space:nowrap}
    }

    </style>

    <div class="shop-toolbar">
        <div class="shop-toolbar__left">
            <span class="shop-count">
                <span id="accountsCountShown">0</span>
                <span class="shop-count__sep">/</span>
                <span id="accountsCountTotal"><?= (int)($pagination['totalItems'] ?? 0) ?></span>
                <?= t('Accounts') ?>
            </span>
        </div>
    </div>

    <?php
    if (!empty($data) && is_array($data)) {
        $isRobloxShopPage = in_array(strtolower((string)$game), ['roblox', 'roblox-accounts', 'roblox-account'], true)
            || in_array(strtolower((string)$gameShort), ['roblox', 'roblox-accounts', 'roblox-account'], true);

        foreach ($data as &$acc) {
            if (!is_array($acc)) {
                continue;
            }

            if (!empty($acc['title'])) {
                $acc['description'] = $acc['title'];
                $acc['desc']        = $acc['title'];
            }

            // Roblox: some shop queries do not include game_data, but the card needs
            // game_data.games to show the selected Experience/Game name and icon.
            if ($isRobloxShopPage && (empty($acc['game_data']) || trim((string)$acc['game_data']) === '{}' || trim((string)$acc['game_data']) === '[]') && !empty($acc['id'])) {
                try {
                    global $db;
                    if (!empty($db) && method_exists($db, 'cell')) {
                        $fetchedGameData = $db->cell('SELECT game_data FROM accounts WHERE id = ? LIMIT 1', (int)$acc['id']);
                        if (!empty($fetchedGameData)) {
                            $acc['game_data'] = $fetchedGameData;
                        }
                    }
                } catch (Throwable $e) {
                    // Keep the normal card fallback if the lookup is unavailable.
                }
            }
        }
        unset($acc);
    }
    ?>

    <div class="accounts-grid" id="accountsGrid">
        <?= $this->insert('website/components/accounts/account-cards', ['accounts' => $data, 'game' => $gameShort]) ?>
    </div>

    <div class="shop-empty" id="shopEmpty" style="display:none;">
        <div class="shop-empty__inner">
            <div class="shop-empty__emoji">🥺</div>
            <div class="shop-empty__title"><?= t('No accounts match your search') ?></div>
            <div class="shop-empty__text"><?= t('Try adjusting filters, or message us and we will help find what you are looking for.') ?></div>
            <div class="shop-empty__actions">
                <button type="button" class="shop-empty__btn shop-empty__btn--primary" id="btnTalkToAgent">
                    <i class="fa-regular fa-comment-dots"></i>
                    <span><?= t('Talk to Agent') ?></span>
                </button>
                <button type="button" class="shop-empty__btn shop-empty__btn--ghost" id="btnResetFiltersEmpty">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    <span><?= t('Reset Filters') ?></span>
                </button>
            </div>
        </div>
    </div>

    <div class="shop-pagination" id="shopPagination"></div>

    <?php endif; ?>

</div>

<style>
/* Dynamic account shop filterbar - LoL style compatibility */
html,body{overflow-x:hidden;}
.ranked-accounts-page{overflow-x:hidden;}
.ranked-accounts-page{overflow-x:hidden;}
.ranked-accounts-page .shop-filterbar{position:relative;z-index:50;margin:24px 0 24px;overflow:visible!important;}
.ranked-accounts-page .shop-filterbar--sticky{position:relative!important;top:auto!important;}
.ranked-accounts-page .shop-filterbar__form{width:100%;}
.ranked-accounts-page .shop-filterbar__row{display:flex;align-items:center;gap:10px;flex-wrap:nowrap;width:100%;max-width:100%;padding:10px 12px;border-radius:999px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);overflow:visible!important;}
.ranked-accounts-page .shop-filterbar__search{display:flex;align-items:center;gap:10px;flex:0 0 clamp(220px,18vw,320px);min-width:0;height:46px;padding:0 14px;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.88);}
.ranked-accounts-page .shop-filterbar__search input{width:100%;background:transparent;border:0;outline:0;color:#fff;font-weight:600;}
.ranked-accounts-page .shop-filterbar__search input::placeholder{color:rgba(255,255,255,.45);}
.ranked-accounts-page .shop-filterpill,.ranked-accounts-page .shop-sort{position:relative;flex:0 0 auto;z-index:60;}
.ranked-accounts-page .shop-filterpill__btn,.ranked-accounts-page .shop-sort__btn{height:46px;display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:0 15px;border-radius:999px;background:rgba(255,255,255,.065);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.94);font-weight:850;white-space:nowrap;line-height:1;cursor:pointer;}
.ranked-accounts-page .shop-filterpill__btn:hover,.ranked-accounts-page .shop-sort__btn:hover,.ranked-accounts-page .shop-filterpill.is-open .shop-filterpill__btn,.ranked-accounts-page .shop-sort.is-open .shop-sort__btn{background:rgba(99,102,241,.18);border-color:rgba(139,92,246,.55);}
.ranked-accounts-page .shop-filterpill__value{font-weight:900;color:#fff;max-width:86px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle;}
.ranked-accounts-page .shop-filterbar__actions{display:flex;align-items:center;gap:10px;margin-left:auto!important;flex:0 0 auto;order:90;}
.ranked-accounts-page .shop-filterbar__search{order:0;}
.ranked-accounts-page [data-dropdown="ddMore"]{order:60;}
.ranked-accounts-page .reset-filters--ghost{height:48px;display:inline-flex;align-items:center;justify-content:center;padding:0 24px;border-radius:999px;border:1px solid rgba(99,102,241,.7);background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-weight:900;box-shadow:0 14px 34px rgba(99,102,241,.28);cursor:pointer;white-space:nowrap;}
.ranked-accounts-page .shop-dropdown{position:absolute;top:calc(100% + 10px);left:0;width:320px;max-width:min(320px,calc(100vw - 24px));display:none;overflow:hidden;border-radius:16px;background:rgba(25,24,38,.96);border:1px solid rgba(255,255,255,.10);box-shadow:0 20px 60px rgba(0,0,0,.58);backdrop-filter:blur(16px);z-index:9999;color:#fff;}
.ranked-accounts-page .shop-dropdown.is-open{display:block;}
.ranked-accounts-page .shop-filterbar__actions .shop-dropdown,.ranked-accounts-page .shop-sort .shop-dropdown{left:auto;right:0;}
.ranked-accounts-page .shop-dropdown__head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 15px;border-bottom:1px solid rgba(255,255,255,.08);font-weight:950;}
.ranked-accounts-page .shop-dropdown__close{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.86);cursor:pointer;}
.ranked-accounts-page .shop-dropdown__body{padding:12px 14px;}
.ranked-accounts-page .js-facet-source.is-hidden{display:none!important;}
.ranked-accounts-page .facet-scroll{max-height:300px;overflow:auto;padding-right:4px;display:flex;flex-direction:column;gap:4px;}
.ranked-accounts-page .facet-scroll::-webkit-scrollbar{width:6px;}
.ranked-accounts-page .facet-scroll::-webkit-scrollbar-thumb{background:rgba(139,92,246,.72);border-radius:999px;}
.ranked-accounts-page .facet-item{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px;width:100%;padding:10px 10px;border-radius:12px;cursor:pointer;user-select:none;color:rgba(255,255,255,.92);font-weight:750;line-height:1.25;}
.ranked-accounts-page .facet-item:hover{background:rgba(255,255,255,.065);}
.ranked-accounts-page .facet-item__left{display:flex;align-items:center;gap:10px;min-width:0;flex:1 1 auto;}
.ranked-accounts-page .facet-item__text{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0;max-width:100%;}
.ranked-accounts-page .facet-item__icon{width:18px;text-align:center;opacity:.9;flex:0 0 18px;}
.ranked-accounts-page .facet-item__platform-icon{width:18px!important;height:18px!important;object-fit:contain;display:inline-block!important;opacity:1!important;}
.ranked-accounts-page .facet-item__check{position:absolute;opacity:0;pointer-events:none;}
.ranked-accounts-page .facet-item__box{width:18px;height:18px;border-radius:6px;border:1px solid rgba(255,255,255,.20);background:rgba(0,0,0,.25);flex:0 0 18px;}
.ranked-accounts-page .facet-item__check:checked + .facet-item__box{background:#7c5cff;border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.22);}
.ranked-accounts-page .facet-search{display:flex;align-items:center;gap:10px;margin-bottom:10px;padding:10px 12px;border-radius:12px;background:rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.08);}
.ranked-accounts-page .facet-search__input{width:100%;background:transparent;border:0;outline:0;color:#fff;}
.ranked-accounts-page .shop-price__fields{display:grid;grid-template-columns:1fr auto 1fr;gap:10px;align-items:end;}
.ranked-accounts-page .shop-price__field label{display:block;margin:0 0 7px;color:rgba(255,255,255,.72);font-size:12px;font-weight:800;}
.ranked-accounts-page .shop-price__input,.ranked-accounts-page .shop-price__field>input{height:44px;display:flex;align-items:center;gap:8px;width:100%;padding:0 12px;border-radius:12px;background:rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.08);color:#fff;}
.ranked-accounts-page .shop-price__input input{width:100%;background:transparent;border:0;outline:0;color:#fff;}
.ranked-accounts-page .shop-price__field>input{background:rgba(0,0,0,.22);outline:0;}
.ranked-accounts-page .shop-price__sep{padding-bottom:13px;color:rgba(255,255,255,.65);font-weight:900;}
.ranked-accounts-page .shop-range{position:relative;height:38px;margin:14px 4px 8px;}
.ranked-accounts-page .shop-range__track{position:absolute;left:0;right:0;top:50%;height:7px;transform:translateY(-50%);border-radius:999px;background:rgba(255,255,255,.13);}
.ranked-accounts-page .shop-range__fill{position:absolute;top:0;height:100%;border-radius:999px;background:linear-gradient(90deg,#7c5cff,#a78bfa);}
.ranked-accounts-page .shop-range input[type=range]{position:absolute;left:0;top:50%;width:100%;height:7px;margin:0;transform:translateY(-50%);appearance:none;-webkit-appearance:none;background:transparent;pointer-events:none;z-index:2;}
.ranked-accounts-page .shop-range input[type=range]::-webkit-slider-runnable-track{height:7px;background:transparent;border:0;}
.ranked-accounts-page .shop-range input[type=range]::-moz-range-track{height:7px;background:transparent;border:0;}
.ranked-accounts-page .shop-range input[type=range]::-webkit-slider-thumb{appearance:none;-webkit-appearance:none;width:24px;height:24px;border-radius:999px;background:#8b5cf6;border:3px solid rgba(20,19,30,.96);box-shadow:0 0 0 5px rgba(139,92,246,.2);pointer-events:auto;cursor:grab;margin-top:-8.5px;}
.ranked-accounts-page .shop-range input[type=range]::-moz-range-thumb{width:24px;height:24px;border-radius:999px;background:#8b5cf6;border:3px solid rgba(20,19,30,.96);box-shadow:0 0 0 5px rgba(139,92,246,.2);pointer-events:auto;cursor:grab;}
.ranked-accounts-page .shop-price__labels{display:flex;justify-content:space-between;color:rgba(255,255,255,.78);font-weight:900;font-size:13px;}
/* Active filter chips: stable row below the toolbar. Hidden when empty, so Clear All restores the original layout. */
.ranked-accounts-page .shop-filterbar__chips{
  display:none;
  flex-wrap:nowrap;
  align-items:center;
  gap:10px;
  width:100%;
  max-width:100%;
  min-height:42px;
  margin:12px 0 0;
  padding:6px 8px;
  overflow-x:auto!important;
  overflow-y:hidden!important;
  scrollbar-width:none;
  -webkit-overflow-scrolling:touch;
}
.ranked-accounts-page .shop-filterbar__chips.has-chips{display:flex;}
.ranked-accounts-page .shop-filterbar__chips::-webkit-scrollbar{height:0!important;display:none!important;}
.ranked-accounts-page .filter-chip{
  min-height:34px;
  max-width:260px;
  flex:0 0 auto;
  display:inline-flex;
  align-items:center;
  gap:9px;
  padding:8px 14px;
  border-radius:999px;
  background:rgba(255,255,255,.065);
  border:1px solid rgba(255,255,255,.12);
  color:#fff;
  font-weight:850;
  font-size:12px;
  cursor:pointer;
  white-space:nowrap;
  line-height:1;
}
.ranked-accounts-page .filter-chip:hover{background:rgba(255,255,255,.085);border-color:rgba(139,92,246,.35);}
.ranked-accounts-page .filter-chip span:first-child{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ranked-accounts-page .filter-chip__x{flex:0 0 auto;opacity:.78;font-size:14px;line-height:1;}
.ranked-accounts-page [data-dropdown="ddMore"].is-empty-more{display:none!important;}
.ranked-accounts-page [data-dropdown="ddMore"]:not(.is-empty-more){display:flex!important;}
.ranked-accounts-page .account-card .delivery-type{display:inline-flex!important;align-items:center!important;justify-content:center!important;line-height:1!important;text-align:center!important;}
.ranked-accounts-page .account-card .delivery-type::before{display:block;line-height:1;}
.ranked-accounts-page .shop-filterpill.is-more-hidden{position:absolute!important;left:-99999px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important;}
.ranked-accounts-page #ddMore .mf-menuitem.is-generated{display:flex;width:100%;}
.ranked-accounts-page #ddMore{left:0;right:auto;}
.ranked-accounts-page #ddMore .mf-menuitem__label strong{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;margin-left:6px;padding:0 7px;border-radius:999px;background:rgba(139,92,246,.28);color:#fff;font-size:11px;font-weight:950;vertical-align:middle;}
.ranked-accounts-page .shop-toolbar{position:relative;z-index:1;margin-top:20px;}
@media (min-width:1100px){.ranked-accounts-page .shop-filterbar__row{flex-wrap:nowrap;}.ranked-accounts-page .shop-filterbar__actions{margin-left:auto;}.ranked-accounts-page .shop-filterpill__btn,.ranked-accounts-page .shop-sort__btn{padding-left:12px;padding-right:12px;}.ranked-accounts-page .shop-filterbar__chips{flex-basis:auto;}}
@media (max-width:768px){.ranked-accounts-page .shop-filterbar__row{display:grid;grid-template-columns:1fr 1fr;border-radius:22px}.ranked-accounts-page .shop-filterbar__search,.ranked-accounts-page .shop-filterbar__actions{grid-column:1/-1;width:100%;}.ranked-accounts-page .shop-filterpill,.ranked-accounts-page .shop-filterpill__btn,.ranked-accounts-page .shop-sort,.ranked-accounts-page .shop-sort__btn{width:100%;}.ranked-accounts-page .shop-dropdown.is-open{position:fixed;left:12px!important;right:12px!important;top:12px!important;bottom:12px!important;width:auto!important;max-width:none!important;}.ranked-accounts-page .facet-scroll{max-height:calc(100vh - 145px)}}

/* Dynamic shop filterbar stability: keep the toolbar one line on desktop and never resize the page when filters change. */
@media (min-width:1100px){
  .ranked-accounts-page .shop-filterbar__row{min-height:68px;}
  .ranked-accounts-page .shop-filterbar__actions{margin-left:auto;}
  .ranked-accounts-page .shop-filterpill__label{display:inline-block;max-width:none;overflow:visible;text-overflow:clip;white-space:nowrap;}
  .ranked-accounts-page .shop-filterpill__btn{max-width:none;}
  .ranked-accounts-page .shop-filterpill__value{max-width:none;overflow:visible;text-overflow:clip;white-space:nowrap;}
}
.ranked-accounts-page .facet-item__left{width:calc(100% - 34px);}


/* Keep current design: filter UI may not increase the page/grid intrinsic width. */
.ranked-accounts-page .shop-filterbar,
.ranked-accounts-page .shop-filterbar__form,
.ranked-accounts-page .shop-filterbar__row,
.ranked-accounts-page .shop-filterbar__chips{
  min-width:0;
  max-width:100%;
  box-sizing:border-box;
}
.ranked-accounts-page #accountsGrid.accounts-grid{
  min-width:0;
  max-width:100%;
  box-sizing:border-box;
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(300px, 360px)) !important;
  justify-content:start;
  align-items:stretch;
  gap:24px;
}
.ranked-accounts-page #accountsGrid .account-card{
  width:100% !important;
  max-width:360px !important;
  min-width:0 !important;
  box-sizing:border-box;
  overflow:hidden;
}
.ranked-accounts-page #accountsGrid .account-card h1,
.ranked-accounts-page #accountsGrid .account-card h2,
.ranked-accounts-page #accountsGrid .account-card h3,
.ranked-accounts-page #accountsGrid .account-card h4,
.ranked-accounts-page #accountsGrid .account-card .account-title,
.ranked-accounts-page #accountsGrid .account-card .account-card__title,
.ranked-accounts-page #accountsGrid .account-card .card-title,
.ranked-accounts-page #accountsGrid .account-card [class*='title']{
  min-width:0;
  max-width:100%;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.ranked-accounts-page #accountsGrid .account-card img{
  max-width:100%;
}
@media (max-width:1199px){
  .ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)) !important;}
  .ranked-accounts-page #accountsGrid .account-card{max-width:none !important;}
}
@media (max-width:640px){
  .ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:1fr !important;gap:18px;}
}


/* Seller footer stability after AJAX/filtering:
   keep seller name, sold badge and trusted badge from overlapping on fixed-size cards. */
.ranked-accounts-page #accountsGrid .account-card .seller-info{
  min-width:0;
  overflow:hidden !important;
  gap:10px;
  padding-left:14px;
  padding-right:14px;
}
.ranked-accounts-page #accountsGrid .account-card .seller-info__left{
  flex:1 1 0;
  min-width:0;
  max-width:100%;
  overflow:hidden !important;
  gap:10px;
}
.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar{
  flex:0 0 42px;
  width:42px;
  height:42px;
  min-width:42px;
  min-height:42px;
}
.ranked-accounts-page #accountsGrid .account-card .seller-rank-trigger,
.ranked-accounts-page #accountsGrid .account-card .seller-info__name{
  flex:1 1 auto;
  min-width:0;
  max-width:100%;
  overflow:hidden !important;
}
.ranked-accounts-page #accountsGrid .account-card .seller-info__name-text{
  min-width:0;
  max-width:100%;
  overflow:hidden;
  text-overflow:clip;
  white-space:nowrap;
  font-size:clamp(12px,.82vw,15px);
  line-height:1.05;
  display:inline-block;
  transform-origin:left center;
}
.ranked-accounts-page #accountsGrid .account-card .seller-info__verified{
  flex:0 0 auto;
  font-size:15px;
  margin-left:2px;
}
.ranked-accounts-page #accountsGrid .account-card .seller-info__right{
  flex:0 0 auto;
  min-width:0;
  gap:6px;
  white-space:nowrap;
}
.ranked-accounts-page #accountsGrid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid .account-card .seller-info__rating{
  flex:0 0 auto;
  min-width:0;
  padding-left:8px;
  padding-right:8px;
  font-size:12px;
}
@media (max-width:1300px){
  .ranked-accounts-page #accountsGrid .account-card .seller-info{gap:8px;padding-left:12px;padding-right:12px;}
  .ranked-accounts-page #accountsGrid .account-card .seller-info__avatar{flex-basis:38px;width:38px;height:38px;min-width:38px;min-height:38px;}
  .ranked-accounts-page #accountsGrid .account-card .seller-info__name-text{font-size:12px;}
  .ranked-accounts-page #accountsGrid .account-card .seller-info__sold,
  .ranked-accounts-page #accountsGrid .account-card .seller-info__rating{font-size:11px;padding-left:7px;padding-right:7px;}
}


/* More Filters submenu visibility */
.ranked-accounts-page #ddMore .mf-view{display:none;}
.ranked-accounts-page #ddMore .mf-view.is-active{display:block;}
.ranked-accounts-page #ddMore .mf-menuitem,
.ranked-accounts-page #ddMore .mf-back{cursor:pointer;}


/* Robust More Filters menu for many dynamic schema filters */
.ranked-accounts-page #ddMore .shop-dropdown__body{
  max-height:min(70vh,520px);
  overflow:auto;
}
.ranked-accounts-page #ddMore .mf-menu{
  display:flex;
  flex-direction:column;
  gap:8px;
}
.ranked-accounts-page #ddMore .mf-menuitem{
  width:100%;
  min-height:44px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.055);
  color:#fff;
  font-weight:850;
  text-align:left;
}
.ranked-accounts-page #ddMore .mf-menuitem:hover{
  background:rgba(139,92,246,.18);
  border-color:rgba(139,92,246,.38);
}
.ranked-accounts-page #ddMore .mf-menuitem__left{
  width:22px;
  flex:0 0 22px;
  text-align:center;
  opacity:.92;
}
.ranked-accounts-page #ddMore .mf-menuitem__label{
  flex:1 1 auto;
  min-width:0;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.ranked-accounts-page #ddMore .mf-menuitem__right{
  flex:0 0 auto;
  opacity:.72;
  font-size:18px;
  line-height:1;
}
.ranked-accounts-page #ddMore .mf-panelhead{
  display:flex;
  align-items:center;
  gap:10px;
  margin-bottom:10px;
}
.ranked-accounts-page #ddMore .mf-back{
  width:36px;
  height:36px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius:10px;
  border:1px solid rgba(255,255,255,.10);
  background:rgba(255,255,255,.06);
  color:#fff;
}
.ranked-accounts-page #ddMore .mf-title{
  font-weight:950;
  color:#fff;
}


/* More Filters dropdown like the compact reference menu */
.ranked-accounts-page #ddMore{
  width:290px;
  border-radius:8px;
  background:rgba(30,32,39,.98);
  border:1px solid rgba(255,255,255,.10);
  box-shadow:0 16px 45px rgba(0,0,0,.50);
}
.ranked-accounts-page #ddMore .shop-dropdown__head{
  justify-content:center;
  min-height:34px;
  padding:7px 12px;
  background:rgba(255,255,255,.035);
  border-bottom:1px solid rgba(255,255,255,.10);
  font-size:15px;
  font-weight:900;
}
.ranked-accounts-page #ddMore .shop-dropdown__head .shop-dropdown__close{display:none;}
.ranked-accounts-page #ddMore .shop-dropdown__body{
  padding:10px;
  max-height:min(72vh,560px);
  overflow:auto;
}
.ranked-accounts-page #ddMore .mf-menu{gap:2px;}
.ranked-accounts-page #ddMore .mf-menuitem{
  min-height:44px;
  padding:10px 11px;
  border:0;
  border-radius:7px;
  background:transparent;
  color:rgba(255,255,255,.94);
  font-size:15px;
  font-weight:800;
}
.ranked-accounts-page #ddMore .mf-menuitem:hover{background:rgba(255,255,255,.075);border-color:transparent;}
.ranked-accounts-page #ddMore .mf-menuitem__left{width:24px;flex-basis:24px;font-size:15px;color:rgba(255,255,255,.70);}
.ranked-accounts-page #ddMore .mf-menuitem__right{font-size:28px;line-height:1;color:rgba(255,255,255,.88);}
.ranked-accounts-page #ddMore .mf-menuitem__label strong{background:rgba(255,255,255,.10);}
.ranked-accounts-page .shop-filterpill.is-more-hidden{display:none!important;}
@media (min-width:768px){
  .ranked-accounts-page [data-dropdown="ddMore"]{display:flex!important;}
}


.ranked-accounts-page #ddMore .mf-view{display:none;}
.ranked-accounts-page #ddMore .mf-view.is-active{display:block;}
.ranked-accounts-page #ddMore .mf-menu{display:flex;flex-direction:column;gap:2px;}
.ranked-accounts-page #ddMore .mf-menuitem{width:100%;min-height:44px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border:0;background:transparent;color:rgba(255,255,255,.92);font-weight:850;text-align:left;border-radius:10px;cursor:pointer;pointer-events:auto;}
.ranked-accounts-page #ddMore .mf-menuitem:hover{background:rgba(255,255,255,.07);}
.ranked-accounts-page #ddMore .mf-menuitem__left{width:22px;display:inline-flex;align-items:center;justify-content:center;color:rgba(255,255,255,.72);}
.ranked-accounts-page #ddMore .mf-menuitem__label{flex:1 1 auto;min-width:0;}
.ranked-accounts-page #ddMore .mf-menuitem__right{opacity:.9;font-size:22px;line-height:1;}
.ranked-accounts-page #ddMore .mf-panelhead{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.ranked-accounts-page #ddMore .mf-back{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#fff;cursor:pointer;pointer-events:auto;}
.ranked-accounts-page #ddMore .mf-title{font-weight:950;color:#fff;}


/* Final More Filters behavior: hide button if there are no overflow filters and force dropdown visibility when open. */
.ranked-accounts-page [data-dropdown="ddMore"].is-empty-more{display:none!important;}
.ranked-accounts-page [data-dropdown="ddMore"]:not(.is-empty-more){display:flex!important;}
.ranked-accounts-page #ddMore.is-open{display:block!important;visibility:visible!important;opacity:1!important;pointer-events:auto!important;}

/* Keep visible filter pills readable, no shortened labels like Town Hall L... */
.ranked-accounts-page .shop-filterpill:not(.is-more-hidden) .shop-filterpill__label,
.ranked-accounts-page .shop-filterpill:not(.is-more-hidden) .shop-filterpill__value{
  max-width:none!important;
  overflow:visible!important;
  text-overflow:clip!important;
  white-space:nowrap!important;
}
.ranked-accounts-page .shop-filterpill:not(.is-more-hidden) .shop-filterpill__btn{
  max-width:none!important;
  width:auto!important;
}


/* Keep the filterbar inside its rounded border after values are selected. */
.ranked-accounts-page .shop-filterbar__row{
  overflow:visible!important;
  contain:layout;
}
.ranked-accounts-page .shop-filterbar__row > .shop-filterpill,
.ranked-accounts-page .shop-filterbar__row > .shop-filterbar__actions{
  min-width:0!important;
}
.ranked-accounts-page .shop-filterbar__actions{
  flex-shrink:0!important;
}
.ranked-accounts-page .shop-filterpill.is-more-hidden{
  display:none!important;
}


/* Mobile filterbar fix: keep only a few pills visible and put the rest into More Filters. */
@media (max-width:768px){
  .ranked-accounts-page .shop-filterbar{margin-top:18px!important;}
  .ranked-accounts-page .shop-filterbar__row{
    grid-template-columns:1fr 1fr!important;
    align-items:stretch!important;
    gap:8px!important;
    padding:10px!important;
    border-radius:22px!important;
    overflow:visible!important;
    contain:none!important;
  }
  .ranked-accounts-page .shop-filterbar__search{
    grid-column:1/-1!important;
    width:100%!important;
    height:44px!important;
    flex-basis:auto!important;
  }
  .ranked-accounts-page .shop-filterbar__row > .shop-filterpill{
    width:100%!important;
    min-width:0!important;
  }
  .ranked-accounts-page .shop-filterpill__btn{
    width:100%!important;
    max-width:100%!important;
    justify-content:flex-start!important;
    padding:0 12px!important;
  }
  .ranked-accounts-page .shop-filterpill__label,
  .ranked-accounts-page .shop-filterpill__value{
    min-width:0!important;
    max-width:none!important;
    overflow:visible!important;
    text-overflow:clip!important;
    white-space:nowrap!important;
  }
  .ranked-accounts-page .shop-filterpill__btn .fa-caret-down{
    margin-left:auto!important;
  }
  .ranked-accounts-page .shop-filterbar__actions{
    grid-column:1/-1!important;
    display:grid!important;
    grid-template-columns:1fr 1fr!important;
    gap:8px!important;
    width:100%!important;
    margin-left:0!important;
  }
  .ranked-accounts-page .reset-filters--ghost,
  .ranked-accounts-page .shop-sort,
  .ranked-accounts-page .shop-sort__btn{
    width:100%!important;
    max-width:100%!important;
  }
  .ranked-accounts-page .shop-sort__btn{
    justify-content:center!important;
    overflow:hidden!important;
  }
  .ranked-accounts-page #sortLabel{
    min-width:0!important;
    overflow:hidden!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
  }
  .ranked-accounts-page [data-dropdown="ddMore"]{display:flex!important;}
  .ranked-accounts-page [data-dropdown="ddMore"].is-empty-more{display:none!important;}
  .ranked-accounts-page .shop-dropdown.is-open{
    position:fixed!important;
    left:12px!important;
    right:12px!important;
    top:12px!important;
    bottom:auto!important;
    width:auto!important;
    max-width:none!important;
    max-height:calc(100vh - 24px)!important;
    overflow:auto!important;
    z-index:99999!important;
  }
  .ranked-accounts-page .facet-scroll{max-height:calc(100vh - 170px)!important;}
}


/* Mobile bottom filter sheet: only Search + Filters button are visible, all filters live in one bottom menu. */
@media (max-width:768px){
  .ranked-accounts-page .shop-filterbar__row{
    display:grid!important;
    grid-template-columns:1fr auto!important;
    gap:8px!important;
    padding:10px!important;
    border-radius:18px!important;
    align-items:center!important;
  }
  .ranked-accounts-page .shop-filterbar__search{
    grid-column:auto!important;
    width:100%!important;
    min-width:0!important;
    height:46px!important;
  }
  .ranked-accounts-page .shop-filterbar__row > .shop-filterpill:not([data-dropdown="ddMore"]),
  .ranked-accounts-page .shop-filterbar__actions{
    display:none!important;
  }
  .ranked-accounts-page [data-dropdown="ddMore"]{
    display:flex!important;
    width:auto!important;
    min-width:112px!important;
    grid-column:auto!important;
  }
  .ranked-accounts-page [data-dropdown="ddMore"].is-empty-more{
    display:flex!important;
  }
  .ranked-accounts-page #btnMore{
    width:100%!important;
    height:46px!important;
    padding:0 16px!important;
    justify-content:center!important;
    max-width:none!important;
  }
  .ranked-accounts-page #btnMore .shop-filterpill__label{
    max-width:none!important;
    overflow:visible!important;
    text-overflow:clip!important;
  }
  .ranked-accounts-page #ddMore.is-open{
    position:fixed!important;
    left:0!important;
    right:0!important;
    bottom:0!important;
    top:auto!important;
    width:100%!important;
    max-width:none!important;
    max-height:86vh!important;
    border-radius:22px 22px 0 0!important;
    overflow:hidden!important;
    z-index:100000!important;
    background:rgba(14,16,24,.98)!important;
    border:1px solid rgba(255,255,255,.10)!important;
    box-shadow:0 -20px 70px rgba(0,0,0,.72)!important;
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__head{
    position:sticky!important;
    top:0!important;
    background:rgba(14,16,24,.98)!important;
    z-index:2!important;
    padding:18px 18px 14px!important;
    font-size:20px!important;
    justify-content:space-between!important;
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__head span{
    font-size:20px!important;
    font-weight:950!important;
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__body{
    max-height:calc(86vh - 72px)!important;
    overflow:auto!important;
    padding:12px 16px 96px!important;
  }
  .ranked-accounts-page #ddMore .mf-menu{
    display:flex!important;
    flex-direction:column!important;
    gap:6px!important;
  }
  .ranked-accounts-page #ddMore .mf-menuitem{
    width:100%!important;
    min-height:52px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    gap:12px!important;
    padding:0 14px!important;
    border-radius:14px!important;
    background:rgba(255,255,255,.045)!important;
    border:1px solid rgba(255,255,255,.08)!important;
    color:#fff!important;
    font-weight:850!important;
  }
  .ranked-accounts-page .shop-dropdown.is-open-from-more{
    position:fixed!important;
    left:0!important;
    right:0!important;
    bottom:0!important;
    top:auto!important;
    width:100%!important;
    max-width:none!important;
    max-height:86vh!important;
    border-radius:22px 22px 0 0!important;
    overflow:hidden!important;
    z-index:100001!important;
  }
  .ranked-accounts-page .shop-dropdown.is-open-from-more .shop-dropdown__body{
    max-height:calc(86vh - 72px)!important;
    overflow:auto!important;
    padding-bottom:96px!important;
  }
}


/* Final mobile filter sheet fix: detach from header/container, make it opaque and always readable. */
@media (max-width:768px){
  body.shop-filters-open{
    overflow:hidden!important;
    touch-action:none!important;
  }
  body.shop-filters-open::before{
    content:"";
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.62);
    z-index:2147482990;
  }
  body.ranked-accounts-page #ddMore.is-open,
  .ranked-accounts-page #ddMore.is-open{
    position:fixed!important;
    left:0!important;
    right:0!important;
    bottom:0!important;
    top:auto!important;
    width:100vw!important;
    max-width:100vw!important;
    height:min(86dvh, 720px)!important;
    max-height:86dvh!important;
    margin:0!important;
    transform:none!important;
    border-radius:24px 24px 0 0!important;
    overflow:hidden!important;
    z-index:2147483000!important;
    background:#0d1018!important;
    border:1px solid rgba(255,255,255,.12)!important;
    box-shadow:0 -24px 80px rgba(0,0,0,.85)!important;
    color:#fff!important;
    backdrop-filter:none!important;
  }
  body.ranked-accounts-page #ddMore.is-open .shop-dropdown__head,
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__head{
    background:#0d1018!important;
    border-bottom:1px solid rgba(255,255,255,.10)!important;
    position:sticky!important;
    top:0!important;
    z-index:3!important;
    min-height:64px!important;
    padding:18px 20px!important;
  }
  body.ranked-accounts-page #ddMore.is-open .shop-dropdown__body,
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__body{
    background:#0d1018!important;
    height:calc(86dvh - 64px)!important;
    max-height:calc(86dvh - 64px)!important;
    overflow-y:auto!important;
    overflow-x:hidden!important;
    padding:14px 14px calc(24px + env(safe-area-inset-bottom))!important;
    -webkit-overflow-scrolling:touch!important;
  }
  body.ranked-accounts-page #ddMore .mf-menuitem,
  .ranked-accounts-page #ddMore .mf-menuitem{
    background:#171a24!important;
    border:1px solid rgba(255,255,255,.10)!important;
    color:#fff!important;
    opacity:1!important;
    visibility:visible!important;
  }
  body.ranked-accounts-page #ddMore .mf-menuitem__label,
  body.ranked-accounts-page #ddMore .mf-menuitem__left,
  body.ranked-accounts-page #ddMore .mf-menuitem__right,
  .ranked-accounts-page #ddMore .mf-menuitem__label,
  .ranked-accounts-page #ddMore .mf-menuitem__left,
  .ranked-accounts-page #ddMore .mf-menuitem__right{
    color:#fff!important;
    opacity:1!important;
  }
  body.ranked-accounts-page .shop-dropdown.is-open-from-more,
  .ranked-accounts-page .shop-dropdown.is-open-from-more{
    position:fixed!important;
    left:0!important;
    right:0!important;
    bottom:0!important;
    top:auto!important;
    width:100vw!important;
    max-width:100vw!important;
    height:min(86dvh, 720px)!important;
    max-height:86dvh!important;
    margin:0!important;
    border-radius:24px 24px 0 0!important;
    z-index:2147483001!important;
    background:#0d1018!important;
    backdrop-filter:none!important;
  }
}


/* GameBoost style mobile filter sheet: all filters are directly visible in one bottom sheet. */
@media (max-width:768px){
  .ranked-accounts-page #ddMore.is-open{
    height:min(92dvh, 760px)!important;
    max-height:92dvh!important;
    background:#0b0e15!important;
    border-radius:18px 18px 0 0!important;
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__head{
    min-height:72px!important;
    padding:22px 18px 14px!important;
    background:#0b0e15!important;
    border-bottom:0!important;
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__head:before{
    content:"";
    position:absolute;
    left:50%;
    top:10px;
    width:92px;
    height:7px;
    transform:translateX(-50%);
    border-radius:999px;
    background:rgba(255,255,255,.08);
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__head span{
    font-size:22px!important;
    font-weight:950!important;
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__close{
    display:none!important;
  }
  .ranked-accounts-page #ddMore.is-open .shop-dropdown__body{
    height:calc(92dvh - 72px)!important;
    max-height:calc(92dvh - 72px)!important;
    padding:0!important;
    background:#0b0e15!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-sheet-content{
    padding:0 18px 96px!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-section{
    margin:0 0 22px!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-title{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    margin:0 0 12px!important;
    color:rgba(255,255,255,.58)!important;
    font-size:15px!important;
    font-weight:850!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-title span{
    display:flex!important;
    align-items:center!important;
    gap:10px!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-title i{
    width:18px!important;
    text-align:center!important;
    color:rgba(255,255,255,.66)!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .facet-scroll,
  .ranked-accounts-page #ddMore .mobile-filter-options{
    display:flex!important;
    flex-wrap:wrap!important;
    gap:8px!important;
    max-height:none!important;
    overflow:visible!important;
    padding:0!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options--scroll{
    flex-wrap:nowrap!important;
    overflow-x:auto!important;
    overflow-y:hidden!important;
    padding-bottom:2px!important;
    scrollbar-width:none!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options--scroll::-webkit-scrollbar{display:none!important;}
  .ranked-accounts-page #ddMore .facet-search,
  .ranked-accounts-page #ddMore select.js-facet-source,
  .ranked-accounts-page #ddMore .js-facet-source.is-hidden{
    display:none!important;
  }
  .ranked-accounts-page #ddMore .facet-item,
  .ranked-accounts-page #ddMore .mobile-filter-choice{
    width:auto!important;
    min-height:38px!important;
    flex:0 0 auto!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:8px!important;
    padding:0 13px!important;
    border-radius:999px!important;
    background:#11151f!important;
    border:1px solid rgba(255,255,255,.10)!important;
    color:rgba(255,255,255,.66)!important;
    font-size:14px!important;
    font-weight:750!important;
  }
  .ranked-accounts-page #ddMore .facet-item__left{
    width:auto!important;
    flex:0 0 auto!important;
    gap:7px!important;
  }
  .ranked-accounts-page #ddMore .facet-item__text{
    overflow:visible!important;
    text-overflow:clip!important;
    white-space:nowrap!important;
  }
  .ranked-accounts-page #ddMore .facet-item__box{
    display:none!important;
  }
  .ranked-accounts-page #ddMore .facet-item:has(.facet-item__check:checked),
  .ranked-accounts-page #ddMore .mobile-sort-choice.is-active{
    background:rgba(37,99,235,.20)!important;
    border-color:rgba(59,130,246,.55)!important;
    color:#60a5fa!important;
  }
  .ranked-accounts-page #ddMore .shop-price{
    width:100%!important;
  }
  .ranked-accounts-page #ddMore .shop-price__fields,
  .ranked-accounts-page #ddMore .shop-range,
  .ranked-accounts-page #ddMore .shop-price__labels{
    display:none!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-apply-wrap{
    position:sticky!important;
    left:0!important;
    right:0!important;
    bottom:0!important;
    padding:12px 18px calc(16px + env(safe-area-inset-bottom))!important;
    background:linear-gradient(180deg, rgba(11,14,21,.78), #0b0e15 35%)!important;
    border-top:1px solid rgba(255,255,255,.06)!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-apply{
    width:100%!important;
    height:50px!important;
    border:0!important;
    border-radius:7px!important;
    background:#2563eb!important;
    color:#fff!important;
    font-size:16px!important;
    font-weight:900!important;
    cursor:pointer!important;
  }
}



/* Mobile range filters: show each filter title with its slider directly below it. */
@media (max-width:768px){
  .ranked-accounts-page #ddMore .mobile-filter-section{
    margin:0 0 28px!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-title{
    margin:0 0 12px!important;
    justify-content:flex-start!important;
    color:rgba(255,255,255,.86)!important;
    font-size:15px!important;
    font-weight:900!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options{
    width:100%!important;
    display:block!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price{
    width:100%!important;
    display:block!important;
    padding:0!important;
    margin:0!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__fields{
    display:grid!important;
    grid-template-columns:1fr auto 1fr!important;
    gap:10px!important;
    align-items:end!important;
    margin:0 0 12px!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__field label{
    display:block!important;
    margin:0 0 7px!important;
    color:rgba(255,255,255,.55)!important;
    font-size:12px!important;
    font-weight:850!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__input,
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__field > input{
    height:42px!important;
    display:flex!important;
    align-items:center!important;
    width:100%!important;
    border-radius:12px!important;
    background:#11151f!important;
    border:1px solid rgba(255,255,255,.10)!important;
    color:#fff!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__input input,
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__field > input{
    color:#fff!important;
    font-weight:850!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__sep{
    display:block!important;
    padding-bottom:13px!important;
    color:rgba(255,255,255,.50)!important;
    font-weight:900!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-range{
    display:block!important;
    width:100%!important;
    height:38px!important;
    margin:10px 0 6px!important;
  }
  .ranked-accounts-page #ddMore .mobile-filter-options .shop-price__labels{
    display:flex!important;
    justify-content:space-between!important;
    color:rgba(255,255,255,.62)!important;
    font-size:13px!important;
    font-weight:900!important;
  }
}


/* Mobile sticky compact toolbar: while scrolling, only Search and Filters stay visible at the top. */
@media (max-width:768px){
  .ranked-accounts-page .shop-filterbar,
  body.ranked-accounts-page .shop-filterbar{
    position:sticky!important;
    top:0!important;
    z-index:2147482500!important;
    margin:0!important;
    padding:8px 10px!important;
    background:rgba(9,10,18,.96)!important;
    border-bottom:1px solid rgba(255,255,255,.08)!important;
    backdrop-filter:blur(14px)!important;
  }
  .ranked-accounts-page .shop-filterbar__form{
    width:100%!important;
  }
  .ranked-accounts-page .shop-filterbar__row{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) auto!important;
    gap:8px!important;
    width:100%!important;
    max-width:100%!important;
    padding:0!important;
    border:0!important;
    border-radius:0!important;
    background:transparent!important;
    box-shadow:none!important;
  }
  .ranked-accounts-page .shop-filterbar__search{
    grid-column:auto!important;
    height:48px!important;
    width:100%!important;
    min-width:0!important;
    margin:0!important;
    border-radius:8px!important;
    background:#0b0f17!important;
    border:1px solid rgba(255,255,255,.10)!important;
  }
  .ranked-accounts-page [data-dropdown="ddMore"]{
    display:flex!important;
    width:auto!important;
    min-width:88px!important;
    margin:0!important;
  }
  .ranked-accounts-page #btnMore{
    height:48px!important;
    width:auto!important;
    min-width:88px!important;
    padding:0 12px!important;
    border-radius:8px!important;
    background:#0b0f17!important;
    border:1px solid rgba(255,255,255,.10)!important;
    justify-content:center!important;
    gap:8px!important;
  }
  .ranked-accounts-page #btnMore .fa-caret-down,
  .ranked-accounts-page #btnMore .shop-filterpill__value{
    display:none!important;
  }
  .ranked-accounts-page .shop-filterbar__row > .shop-filterpill:not([data-dropdown="ddMore"]),
  .ranked-accounts-page .shop-filterbar__actions,
  .ranked-accounts-page .shop-filterbar__chips{
    display:none!important;
  }
  .ranked-accounts-page .shop-toolbar{
    margin-top:16px!important;
  }
}


/* Mobile full-width search + filter bar */
@media (max-width: 768px){
  .ranked-accounts-page .shop-filterbar,
  .ranked-accounts-page .shop-filterbar--sticky,
  .ranked-accounts-page .shop-filterbar.is-mobile-scroll-header{
    width:100% !important;
    max-width:100% !important;
    margin:14px 0 18px !important;
    padding:0 12px !important;
    left:auto !important;
    right:auto !important;
    transform:none !important;
    box-sizing:border-box !important;
  }

  .ranked-accounts-page .shop-filterbar__form{
    width:100% !important;
    max-width:100% !important;
  }

  .ranked-accounts-page .shop-filterbar__row{
    width:100% !important;
    max-width:100% !important;
    display:grid !important;
    grid-template-columns:minmax(0,1fr) auto !important;
    align-items:center !important;
    gap:10px !important;
    padding:10px !important;
    border-radius:22px !important;
    background:rgba(255,255,255,.045) !important;
    border:1px solid rgba(255,255,255,.08) !important;
    box-sizing:border-box !important;
    overflow:visible !important;
  }

  .ranked-accounts-page .shop-filterbar__search{
    grid-column:auto !important;
    width:100% !important;
    min-width:0 !important;
    max-width:none !important;
    height:48px !important;
    flex:none !important;
    padding:0 14px !important;
    border-radius:14px !important;
    background:rgba(6,10,18,.72) !important;
    border:1px solid rgba(255,255,255,.10) !important;
    box-sizing:border-box !important;
  }

  .ranked-accounts-page [data-dropdown="ddMore"]{
    grid-column:auto !important;
    width:auto !important;
    min-width:110px !important;
    flex:none !important;
    display:block !important;
    position:relative !important;
    left:auto !important;
    top:auto !important;
    opacity:1 !important;
    pointer-events:auto !important;
  }

  .ranked-accounts-page [data-dropdown="ddMore"] .shop-filterpill__btn{
    width:100% !important;
    height:48px !important;
    min-width:110px !important;
    padding:0 14px !important;
    border-radius:14px !important;
    background:rgba(6,10,18,.72) !important;
    border:1px solid rgba(255,255,255,.12) !important;
    box-shadow:none !important;
  }

  .ranked-accounts-page .shop-filterpill:not([data-dropdown="ddMore"]),
  .ranked-accounts-page .shop-filterbar__actions{
    display:none !important;
  }

  .ranked-accounts-page .shop-filterbar__chips{
    display:none !important;
  }

  .ranked-accounts-page .shop-filterbar.is-mobile-scroll-header{
    position:sticky !important;
    top:0 !important;
    z-index:9998 !important;
    margin:0 !important;
    padding:10px 12px !important;
    background:rgba(12,10,24,.96) !important;
    backdrop-filter:blur(16px) !important;
    border-bottom:1px solid rgba(255,255,255,.08) !important;
  }

  .ranked-accounts-page .shop-filterbar.is-mobile-scroll-header .shop-filterbar__row{
    border-radius:18px !important;
  }
}


.ranked-accounts-page .facet-item__roblox-icon{width:22px!important;height:22px!important;border-radius:6px;object-fit:cover;display:inline-block!important;opacity:1!important;box-shadow:0 2px 8px rgba(0,0,0,.28);}
.ranked-accounts-page .shop-filterpill__btn .facet-item__roblox-icon{width:18px!important;height:18px!important;}

</style>

<script>
(function(){
  const AJAX_ENDPOINT = (typeof ajax_url !== 'undefined') ? ajax_url : <?= json_encode(AJAX_URL) ?>;
  const form = document.getElementById('shopFilters');
  const grid = document.getElementById('accountsGrid');
  const countShown = document.getElementById('accountsCountShown');
  const countTotal = document.getElementById('accountsCountTotal');
  const emptyBox = document.getElementById('shopEmpty');
  const activeBox = document.getElementById('activeFilters');
  const pagination = document.getElementById('shopPagination');
  if (!form || !grid) return;

  let sortMode = 'recommended';
  let timer = null;
  let requestSeq = 0;

  function esc(v){ return String(v ?? '').replace(/[&<>'"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[s])); }
  function labelize(key){ return String(key || '').replace(/\[\]$/,'').replace(/_/g,' ').replace(/\b\w/g, m => m.toUpperCase()); }
  function formatNumber(v){ return Number(v || 0).toLocaleString('de-DE'); }


  // URL sync: keep filters shareable like /accounts?server=EUW&server=NA&price_min=5&price_max=50
  let applyingUrlState = false;
  let initialPageFromUrl = 1;

  const urlKeyAliases = {
    servers: ['server', 'servers'],
    ranks: ['rank', 'ranks'],
    roles: ['role', 'roles'],
    platform: ['platform'],
    cod_titles: ['cod_titles', 'cod_title'],
    delivery_type: ['delivery_type'],
    search: ['search', 'q'],
    sort: ['sort'],
    page: ['page']
  };

  function baseName(name){ return String(name || '').replace(/\[\]$/,''); }
  function primaryUrlKey(base){ return (urlKeyAliases[base] && urlKeyAliases[base][0]) || base; }
  function allUrlKeys(base){ return urlKeyAliases[base] || [base]; }
  function normForCompare(v){ return String(v ?? '').trim().toLowerCase(); }
  function canonicalParamValue(base, value){
    const v = normForCompare(value).replace(/\+/g, ' ');
    if (base === 'servers') {
      const serverAliases = {
        'eu nordic & east':'eune', 'eu nordic and east':'eune', 'eune':'eune',
        'euw':'euw', 'eu west':'euw', 'europe west':'euw',
        'north america':'na', 'na':'na',
        'latin america north':'lan', 'lan':'lan',
        'latin america south':'las', 'las':'las',
        'brazil':'br', 'br':'br',
        'turkey':'tr', 'tr':'tr',
        'russia':'ru', 'ru':'ru',
        'oceania':'oce', 'oce':'oce',
        'korea':'kr', 'kr':'kr',
        'japan':'jp', 'jp':'jp',
        'southeast asia':'sea', 'sea':'sea',
        'middle east':'me', 'me':'me',
        'vietnam':'vn', 'vn':'vn',
        'philippines':'ph', 'ph':'ph',
        'singapore':'sg', 'sg':'sg',
        'thailand':'th', 'th':'th',
        'taiwan':'tw', 'tw':'tw'
      };
      return serverAliases[v] || v;
    }
    return v;
  }

  function getParamValues(params, base){
    const values = [];
    allUrlKeys(base).forEach(key => params.getAll(key).forEach(v => values.push(v)));
    return values;
  }

  function getFirstParam(params, base){
    for (const key of allUrlKeys(base)) {
      const v = params.get(key);
      if (v !== null && v !== '') return v;
    }
    return '';
  }

  function setRangeValuesFromUrl(root, minValue, maxValue){
    const nums = root.querySelectorAll('input[type="number"]');
    const nMin = root.querySelector('[data-range-min-input]') || nums[0];
    const nMax = root.querySelector('[data-range-max-input]') || nums[1];
    const ranges = root.querySelectorAll('input[type="range"]');
    const rMin = root.querySelector('[data-range-min]') || ranges[0];
    const rMax = root.querySelector('[data-range-max]') || ranges[1];
    if (!nMin || !nMax) return;
    if (minValue !== '') nMin.value = minValue;
    if (maxValue !== '') nMax.value = maxValue;
    if (rMin && minValue !== '') rMin.value = minValue;
    if (rMax && maxValue !== '') rMax.value = maxValue;
    refreshRangeVisual(root);
  }

  function applyStateFromUrl(){
    const params = new URLSearchParams(window.location.search || '');
    applyingUrlState = true;

    const searchInput = form.querySelector('[name="search"]');
    const searchValue = getFirstParam(params, 'search');
    if (searchInput && searchValue !== '') searchInput.value = searchValue;

    const sortValue = getFirstParam(params, 'sort');
    if (sortValue !== '') {
      const sortBtn = document.querySelector('.shop-menuitem[data-sort="' + CSS.escape(sortValue) + '"]');
      if (sortBtn) {
        sortMode = sortValue;
        const sortLabel = document.getElementById('sortLabel');
        if (sortLabel) sortLabel.textContent = sortBtn.textContent.trim() || 'Recommended';
      }
    }

    form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      const base = baseName(cb.name);
      const values = getParamValues(params, base);
      if (!values.length) return;
      const label = (cb.closest('.facet-item')?.querySelector('.facet-item__text')?.textContent || '').trim();
      cb.checked = values.some(v => canonicalParamValue(base, v) === canonicalParamValue(base, cb.value) || (label && canonicalParamValue(base, v) === canonicalParamValue(base, label)));
    });

    document.querySelectorAll('.shop-price').forEach(root => {
      const base = getRangeKeyFromRoot(root);
      if (!base) return;
      const minValue = params.get(base + '_min') ?? params.get('min_' + base) ?? '';
      const maxValue = params.get(base + '_max') ?? params.get('max_' + base) ?? '';
      setRangeValuesFromUrl(root, minValue, maxValue);
    });

    const pageValue = parseInt(params.get('page') || '1', 10);
    initialPageFromUrl = Number.isFinite(pageValue) && pageValue > 0 ? pageValue : 1;

    applyingUrlState = false;
    updatePillSummaries();
    renderChips();
    return initialPageFromUrl;
  }

  function getFilterState(page){
    const params = new URLSearchParams();
    const searchInput = form.querySelector('[name="search"]');
    if (searchInput && searchInput.value.trim()) params.set('search', searchInput.value.trim());

    form.querySelectorAll('.shop-dropdown input[type="checkbox"]:checked').forEach(cb => {
      const base = baseName(cb.name);
      params.append(primaryUrlKey(base), cb.value);
    });

    form.querySelectorAll('.shop-price').forEach(root => {
      const nums = root.querySelectorAll('input[type="number"]');
      if (nums.length < 2) return;
      if (root.closest('#ddMore') && root.querySelector('[data-mobile-filter-clone]')) return;
      const min = nums[0], max = nums[1];
      const base = min.name.replace(/_min$/,'');
      const fullMin = String(min.getAttribute('min') || '0');
      const fullMax = String(max.getAttribute('max') || max.value || '');
      if (String(min.value) !== fullMin) params.set(base + '_min', min.value);
      if (String(max.value) !== fullMax) params.set(base + '_max', max.value);
    });

    if (sortMode && sortMode !== 'recommended') params.set('sort', sortMode);
    const p = parseInt(page || 1, 10);
    if (Number.isFinite(p) && p > 1) params.set('page', String(p));
    return params;
  }

  function updateUrl(page, replace){
    if (applyingUrlState || !window.history || !window.history.pushState) return;
    const params = getFilterState(page || 1);
    const qs = params.toString();
    const nextUrl = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
    const currentUrl = window.location.pathname + window.location.search + window.location.hash;
    if (nextUrl === currentUrl) return;
    window.history[replace ? 'replaceState' : 'pushState']({shopFilters:true}, '', nextUrl);
  }

  const iconMap = {
    platform:'fa-solid fa-desktop', platforms:'fa-solid fa-desktop', level:'fa-solid fa-arrow-up', skins:'fa-solid fa-palette', pickaxes:'fa-solid fa-hammer', emotes:'fa-solid fa-face-smile', v_bucks:'fa-solid fa-coins', vbucks:'fa-solid fa-coins', cod_points:'fa-solid fa-coins'
  };
  const platformIconBase = <?= json_encode(rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/accounts/platforms/') ?>;
  const robloxIconBase = <?= json_encode(rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/public/assets'), '/') . '/website/images/roblox-icons/') ?>;
  const robloxExperienceIconMap = {
    bloxfruits:'BloxFruits.webp', typesoul:'TypeSoul.webp', adoptme:'AdoptMe.webp', stealabrainrot:'StealABrainrot.webp', allstartowerdefense:'AllStarTowerDefense.webp', kinglegacy:'KingLegacy.webp', animechampionssimulator:'AnimeChampionsSimulator.webp', barrysprisonrunv2:'BarrysPrisonRunV2.webp', bladeball:'BladeBall.webp', cottonobby:'CottonObby.webp', easyobby:'EasyObby.webp', deathball:'DeathBall.webp', doors:'Doors.webp', dungeonquest:'DungeonQuest.webp', hideandseekextreme:'HideAndSeekExtreme.webp', jailbreak:'Jailbreak.webp', murdermystery2:'MurderMystery2.webp', naturaldisastersurvival:'NaturalDisasterSurvival.webp', petsimulator99:'PetSimulator99.webp', petsimulatorx:'PetSimulatorX.webp', piggy:'Piggy.webp', scubadivingatquilllake:'ScubaDivingAtQuillLake.webp', speedrun4:'SpeedRun4.webp', superbombsurvival:'SuperBombSurvival.webp', themeparktycoon2:'ThemeParkTycoon2.webp', thestrongestbattlegrounds:'TheStrongestBattlegrounds.webp', workatapizzaplace:'WorkAtAPizzaPlace.webp', wutheringwaves:'WutheringWaves.webp', animedefenders:'AnimeDefenders.webp', grandpiece:'GrandPiece.webp', growagarden:'GrowAGarden.webp', others:'Others.webp'
  };
  function robloxExperienceKey(label){ return String(label || '').toLowerCase().replace(/[^a-z0-9]+/g, ''); }
  function iconForName(name){ const key = String(name || '').replace('[]','').toLowerCase(); return iconMap[key] || 'fa-solid fa-circle-dot'; }
  function platformIconFile(label){
    const raw = String(label || '').trim().toLowerCase();
    const value = raw.replace(/[.\-_/\s]/g, '');
    if (!raw) return '';
    if (raw.includes('playstation') || /\bps[345]?\b/.test(raw)) return 'playstation.webp';
    if (value.includes('xbox')) return 'xbox.webp';
    if (value.includes('battle') || value.includes('bnet') || value.includes('battlenet')) return 'battlenet.webp';
    if (value.includes('steam')) return 'steam.webp';
    if (value.includes('switch') || value.includes('nintendo')) return 'switch.webp';
    if (value.includes('android')) return 'android.webp';
    if (value === 'ios' || value.includes('iphone') || value.includes('ipad') || value.includes('apple')) return 'ios.webp';
    if (value.includes('pc') || value.includes('gamepass') || value.includes('windows')) return 'pc.webp';
    return '';
  }
  function facetIconHtml(name, label){
    const key = String(name || '').replace('[]','').toLowerCase();
    if (key === 'platform' || key === 'platforms') {
      const file = platformIconFile(label);
      if (file) {
        return `<img class="facet-item__icon facet-item__platform-icon" src="${esc(platformIconBase + file)}" alt="" loading="lazy">`;
      }
    }
    if (key === 'games' || key === 'experience' || key === 'experience_game' || key === 'main_experience' || key === 'roblox_experience' || key === 'roblox_game' || key === 'game_experience') {
      const file = robloxExperienceIconMap[robloxExperienceKey(label)];
      if (file) {
        return `<img class="facet-item__icon facet-item__roblox-icon" src="${esc(robloxIconBase + file)}" alt="" loading="lazy">`;
      }
    }
    return `<i class="${esc(iconForName(name))} facet-item__icon"></i>`;
  }

  function buildFacets(){
    form.querySelectorAll('select.js-facet-source').forEach(select => {
      const id = select.id;
      const list = form.querySelector('.facet-list[data-facet="' + CSS.escape(id) + '"]');
      if (!list || list.dataset.built === '1') return;
      list.dataset.built = '1';
      const name = select.dataset.facetName || select.name || (id + '[]');
      const html = Array.from(select.options).map((opt, index) => {
        const val = opt.value;
        const txt = opt.textContent.trim();
        if (!txt) return '';
        const cid = id + '_' + index + '_' + String(val).replace(/[^a-zA-Z0-9_-]/g,'_');
        return `<label class="facet-item" for="${esc(cid)}">
          <span class="facet-item__left">${facetIconHtml(name, txt)}<span class="facet-item__text">${esc(txt)}</span></span>
          <input class="facet-item__check" type="checkbox" id="${esc(cid)}" name="${esc(name)}" value="${esc(val)}">
          <span class="facet-item__box"></span>
        </label>`;
      }).join('');
      list.innerHTML = '<div class="facet-scroll">' + html + '</div>';
      select.disabled = true;
      select.classList.add('is-hidden');
    });
  }

  function syncPopularPills(){
    document.querySelectorAll('#lbGenericPopular .lb-generic-popular__pill').forEach(function(button){
      const input = Array.from(form.querySelectorAll('.facet-item__check')).find(function(candidate){
        return candidate.name === button.dataset.filterName &&
          String(candidate.value) === button.dataset.filterValue;
      });
      button.classList.toggle('is-active', !!(input && input.checked));
    });
  }

  function buildPopularPills(){
    const wrap = document.getElementById('lbGenericPopular');
    const list = wrap && wrap.querySelector('.lb-generic-popular__list');
    if (!wrap || !list) return;
    const groups = [];

    form.querySelectorAll('.facet-item__check').forEach(function(input){
      const label = input.closest('.facet-item');
      const text = label && label.querySelector('.facet-item__text');
      if (!text || !input.value) return;
      let group = groups.find(function(row){ return row.name === input.name; });
      if (!group) {
        group = {name:input.name, items:[]};
        groups.push(group);
      }
      if (!group.items.some(function(item){ return item.value === String(input.value); })) {
        group.items.push({input:input,value:String(input.value),label:text.textContent.trim()});
      }
    });

    const picked = [];
    groups.forEach(function(group){
      group.items.slice(0, group.items.length <= 4 ? 4 : 3).forEach(function(item){
        if (picked.length < 12) picked.push(item);
      });
    });

    list.innerHTML = '';
    picked.forEach(function(item){
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'lb-generic-popular__pill';
      button.textContent = item.label;
      button.dataset.filterName = item.input.name;
      button.dataset.filterValue = item.value;
      button.hidden = true;
      button.addEventListener('click', function(){
        item.input.checked = !item.input.checked;
        item.input.dispatchEvent(new Event('change', {bubbles:true}));
        syncPopularPills();
      });
      list.appendChild(button);
    });
    wrap.hidden = true;
    syncPopularPills();
    refreshPopularPillAvailability();
  }

  function refreshPopularPillAvailability(){
    const wrap = document.getElementById('lbGenericPopular');
    const buttons = Array.from(document.querySelectorAll('#lbGenericPopular .lb-generic-popular__pill'));
    if (!wrap || !buttons.length) return;
    const body = new URLSearchParams();
    body.set('action', 'shop_popular_filter_counts');
    body.set('entity', 'accounts');
    body.set('game', String(form.querySelector('[name="game"]')?.value || ''));
    buttons.forEach(function(button, index){
      body.set('checks[' + index + '][kind]', button.dataset.filterName || '');
      body.set('checks[' + index + '][value]', button.dataset.filterValue || '');
    });
    fetch(AJAX_ENDPOINT, {method:'POST', body:body, credentials:'same-origin'})
      .then(function(response){ return response.json(); })
      .then(function(payload){
        if (!payload || !payload.success) return;
        let visible = 0;
        buttons.forEach(function(button, index){
          button.hidden = !(Number(payload.counts?.[index] || 0) > 0);
          if (!button.hidden) visible++;
        });
        wrap.hidden = visible === 0;
      })
      .catch(function(){ wrap.hidden = true; });
  }

  form.addEventListener('change', function(event){
    if (event.target && event.target.classList.contains('facet-item__check')) {
      syncPopularPills();
    }
  });

  function closeDropdowns(exceptId){
    document.querySelectorAll('.shop-dropdown.is-open').forEach(dd => {
      if (!exceptId || dd.id !== exceptId) {
        dd.classList.remove('is-open');
        dd.classList.remove('is-open-from-more');
        dd.style.left = '';
        dd.style.top = '';
        dd.style.right = '';
        dd.style.width = '';
        dd.style.zIndex = '';
        dd.style.pointerEvents = '';
        dd.style.position = '';
        dd.style.removeProperty('display');
        dd.style.removeProperty('visibility');
        dd.style.removeProperty('opacity');
      }
    });
    document.querySelectorAll('.shop-filterpill.is-open,.shop-sort.is-open').forEach(p => {
      if (!exceptId || p.dataset.dropdown !== exceptId) p.classList.remove('is-open');
    });
    if (!exceptId || exceptId !== 'ddMore') document.body.classList.remove('shop-filters-open');
  }

  function buildMobileFilterSheet(overflow){
    const moreMenu = document.querySelector('#ddMore .mf-menu');
    if (!moreMenu) return;
    const sections = [];

    const sortDropdown = document.getElementById('ddSort');
    if (sortDropdown) {
      const sortItems = Array.from(sortDropdown.querySelectorAll('.shop-menuitem[data-sort]')).map(btn => {
        const sort = btn.dataset.sort || 'recommended';
        const active = sort === sortMode ? ' is-active' : '';
        return '<button type="button" class="mobile-filter-choice mobile-sort-choice' + active + '" data-sort="' + esc(sort) + '">' + btn.innerHTML + (active ? '<i class="fa-solid fa-check"></i>' : '') + '</button>';
      }).join('');
      sections.push('<div class="mobile-filter-section mobile-filter-section--sort"><div class="mobile-filter-title"><span><i class="fa-solid fa-arrow-up-wide-short"></i> Sort by</span></div><div class="mobile-filter-options mobile-filter-options--scroll">' + sortItems + '</div></div>');
    }

    const orderedMobilePills = (() => {
      const price = overflow.find(pill => pill.dataset.dropdown === 'ddPrice');
      const rest = overflow.filter(pill => pill.dataset.dropdown !== 'ddPrice');
      return price ? [price, ...rest] : rest;
    })();

    orderedMobilePills.forEach(pill => {
      const ddId = pill.dataset.dropdown || '';
      const dd = document.getElementById(ddId);
      if (!dd) return;
      const label = (pill.querySelector('.shop-filterpill__label')?.textContent || '').trim() || 'Filter';
      const icon = pill.querySelector('.shop-filterpill__btn > i:first-child')?.className || 'fa-solid fa-filter';
      const body = dd.querySelector('.shop-dropdown__body');
      if (!body) return;
      const clone = body.cloneNode(true);

      // cloneNode(true) can keep old value attributes although the desktop/source sliders
      // already received the real min/max from the current offers. Copy live values from
      // the source inputs into attributes before rendering the mobile sheet.
      const sourceInputs = Array.from(body.querySelectorAll('input, textarea, select'));
      const clonedInputs = Array.from(clone.querySelectorAll('input, textarea, select'));
      clonedInputs.forEach((el, i) => {
        const src = sourceInputs[i];
        if (!src) return;
        if (src.matches('input[type=checkbox], input[type=radio]')) {
          el.checked = src.checked;
          if (src.checked) el.setAttribute('checked', 'checked');
          else el.removeAttribute('checked');
        } else if ('value' in src) {
          el.value = src.value;
          el.setAttribute('value', src.value);
        }
        ['min','max','step','name'].forEach(attr => {
          if (src.getAttribute(attr) !== null) el.setAttribute(attr, src.getAttribute(attr));
        });
      });

      clone.querySelectorAll('[id]').forEach((el, i) => {
        const oldId = el.id;
        const newId = 'mobile_' + ddId + '_' + i + '_' + oldId;
        clone.querySelectorAll('label[for="' + CSS.escape(oldId) + '"]').forEach(l => l.setAttribute('for', newId));
        el.id = newId;
      });
      clone.querySelectorAll('input,select,textarea,button').forEach(el => {
        el.dataset.mobileFilterClone = '1';
        el.dataset.sourceDropdown = ddId;
        if (el.tagName === 'SELECT') el.disabled = true;
      });
      sections.push('<div class="mobile-filter-section" data-mobile-section="' + esc(ddId) + '"><div class="mobile-filter-title"><span><i class="' + esc(icon) + '"></i> ' + esc(label) + '</span></div><div class="mobile-filter-options">' + clone.innerHTML + '</div></div>');
    });

    moreMenu.innerHTML = '<div class="mobile-filter-sheet-content">' + sections.join('') + '</div>';
  }

  function refreshMoreFilters(){
    const row = document.querySelector('.shop-filterbar__row');
    const morePill = document.querySelector('[data-dropdown="ddMore"]');
    const moreMenu = document.querySelector('#ddMore .mf-menu');
    if (!row || !morePill || !moreMenu) return;

    const actions = row.querySelector('.shop-filterbar__actions');
    const search = row.querySelector('.shop-filterbar__search');
    if (search) search.style.order = '0';
    if (actions) actions.style.order = '90';
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    const moreLabelEl = morePill.querySelector('.shop-filterpill__label');
    if (moreLabelEl) moreLabelEl.textContent = isMobile ? 'Filters' : 'More Filters';

    moreMenu.innerHTML = '';

    const allFilterPills = Array.from(row.children).filter(el =>
      el.classList &&
      el.classList.contains('shop-filterpill') &&
      el.dataset.dropdown &&
      el.dataset.dropdown !== 'ddMore'
    );

    const pricePill = allFilterPills.find(el => el.dataset.dropdown === 'ddPrice') || null;
    const candidates = isMobile ? allFilterPills : allFilterPills.filter(el => el.dataset.dropdown !== 'ddPrice');

    candidates.forEach((el, index) => {
      el.classList.remove('is-more-hidden');
      el.style.order = String(10 + index);
    });

    if (pricePill && !isMobile) {
      pricePill.classList.remove('is-more-hidden');
      pricePill.style.order = '49';
    }

    morePill.classList.remove('is-empty-more');
    morePill.style.order = '50';
    if (actions) actions.style.marginLeft = 'auto';

    const overflow = [];
    const maxVisibleFilters = isMobile ? 0 : 4;

    function addOverflow(pill){
      if (!pill || overflow.includes(pill)) return;
      overflow.push(pill);
      pill.classList.add('is-more-hidden');
      pill.style.order = '999';
    }

    candidates.forEach((pill, index) => {
      if (index >= maxVisibleFilters) addOverflow(pill);
    });

    if (!isMobile) {
      const fits = () => row.scrollWidth <= row.clientWidth + 2;
      let guard = 0;
      while (!fits() && guard < 20) {
        const visible = candidates.filter(pill => !pill.classList.contains('is-more-hidden'));
        if (!visible.length) break;
        addOverflow(visible[visible.length - 1]);
        guard++;
      }
    }

    if (isMobile) {
      buildMobileFilterSheet(overflow);
      morePill.classList.remove('is-empty-more');
      return;
    }

    overflow.forEach(pill => {
      const ddId = pill.dataset.dropdown || '';
      if (!ddId || !document.getElementById(ddId)) return;

      const label = (pill.querySelector('.shop-filterpill__label')?.textContent || '').trim() || 'Filter';
      const icon = pill.querySelector('.shop-filterpill__btn > i:first-child')?.className || 'fa-solid fa-filter';
      const value = (pill.querySelector('.shop-filterpill__value')?.textContent || '').trim();

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'mf-menuitem is-generated';
      btn.dataset.moreOpenDropdown = ddId;
      btn.innerHTML = '<span class="mf-menuitem__left"><i class="' + esc(icon) + '"></i></span><span class="mf-menuitem__label">' + esc(label) + (value ? ' <strong>' + esc(value) + '</strong>' : '') + '</span><span class="mf-menuitem__right">›</span>';
      moreMenu.appendChild(btn);
    });

    if (overflow.length === 0) {
      morePill.classList.add('is-empty-more');
    } else {
      morePill.classList.remove('is-empty-more');
    }
  }

  function openDropdownFromMore(ddId){
    const dd = document.getElementById(ddId);
    const pill = document.querySelector('[data-dropdown="' + CSS.escape(ddId) + '"]');
    const moreBtn = document.getElementById('btnMore');
    const page = document.querySelector('.ranked-accounts-page') || document.body;
    if (!dd || !pill || !moreBtn) return;

    closeDropdowns(ddId);

    const moreDropdown = document.getElementById('ddMore');
    const morePill = document.querySelector('[data-dropdown="ddMore"]');
    if (moreDropdown) moreDropdown.classList.remove('is-open');
    if (morePill) morePill.classList.remove('is-open');

    const isMobileSheet = window.matchMedia('(max-width: 768px)').matches;
    // Move the original dropdown out of the hidden pill/container, otherwise mobile headers can cover it.
    const targetParent = isMobileSheet ? document.body : page;
    if (dd.parentElement !== targetParent) {
      targetParent.appendChild(dd);
    }

    const r = moreBtn.getBoundingClientRect();
    const dropdownWidth = Math.min(320, window.innerWidth - 24);
    dd.classList.add('is-open', 'is-open-from-more');
    pill.classList.add('is-open');
    dd.style.position = 'fixed';
    dd.style.zIndex = isMobileSheet ? '100001' : '99999';
    dd.style.pointerEvents = 'auto';
    if (isMobileSheet) {
      document.body.classList.add('shop-filters-open');
      dd.style.width = '100vw';
      dd.style.left = '0';
      dd.style.right = '0';
      dd.style.top = 'auto';
      dd.style.bottom = '0';
    } else {
      dd.style.width = dropdownWidth + 'px';
      dd.style.top = Math.round(r.bottom + 10) + 'px';
      dd.style.left = Math.max(12, Math.min(Math.round(r.left), window.innerWidth - dropdownWidth - 12)) + 'px';
      dd.style.right = 'auto';
      dd.style.bottom = '';
    }
  }

  function openMoreDropdown(){
    const dd = document.getElementById('ddMore');
    const pill = document.querySelector('[data-dropdown="ddMore"]');
    const btn = document.getElementById('btnMore');
    if (!dd || !pill || pill.classList.contains('is-empty-more')) return;

    const isMobileSheet = window.matchMedia('(max-width: 768px)').matches;
    const willOpen = !dd.classList.contains('is-open');
    closeDropdowns('ddMore');
    if (isMobileSheet && dd.parentElement !== document.body) {
      document.body.appendChild(dd);
    }

    dd.style.left = '';
    dd.style.top = '';
    dd.style.right = '';
    dd.style.width = '';
    dd.style.zIndex = '';
    dd.style.pointerEvents = '';
    dd.style.position = '';
    dd.style.bottom = '';

    if (willOpen) {
      dd.classList.add('is-open');
      pill.classList.add('is-open');
      dd.style.setProperty('display', 'block', 'important');
      dd.style.setProperty('visibility', 'visible', 'important');
      dd.style.setProperty('opacity', '1', 'important');
      dd.style.pointerEvents = 'auto';
      if (isMobileSheet) {
        document.body.classList.add('shop-filters-open');
        dd.style.position = 'fixed';
        dd.style.left = '0';
        dd.style.right = '0';
        dd.style.top = 'auto';
        dd.style.bottom = '0';
        dd.style.width = '100vw';
        dd.style.zIndex = '2147483000';
      }
      if (btn) btn.setAttribute('aria-expanded', 'true');
      dd.querySelectorAll('.mf-view').forEach(v => {
        v.classList.toggle('is-active', v.dataset.view === 'menu');
      });
    } else {
      dd.classList.remove('is-open');
      pill.classList.remove('is-open');
      document.body.classList.remove('shop-filters-open');
      dd.style.removeProperty('display');
      dd.style.removeProperty('visibility');
      dd.style.removeProperty('opacity');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    }
  }

  window.addEventListener('resize', function(){
    window.clearTimeout(window.__shopMoreTimer);
    window.__shopMoreTimer = window.setTimeout(function(){
      refreshMoreFilters();
      if (document.getElementById('ddMore')?.classList.contains('is-open')) openMoreDropdown();
    }, 120);
  }, {passive:true});


  // Robust More Filters handling: use capture phase so GTM/other global click handlers cannot block the shop dropdown.
  function handleMoreFiltersClick(e){
    const target = e.target;
    if (!target || !target.closest) return false;

    const moreButton = target.closest('#btnMore');
    if (moreButton) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      openMoreDropdown();
      return true;
    }

    const mfOpen = target.closest('[data-mf-open]');
    if (mfOpen) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      const view = mfOpen.dataset.mfOpen;
      const ddMore = document.getElementById('ddMore');
      if (!ddMore || !view) return true;
      ddMore.querySelectorAll('.mf-view').forEach(v => {
        v.classList.toggle('is-active', v.dataset.view === view);
      });
      return true;
    }

    const mfBack = target.closest('[data-mf-back]');
    if (mfBack) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      const ddMore = document.getElementById('ddMore');
      if (!ddMore) return true;
      ddMore.querySelectorAll('.mf-view').forEach(v => {
        v.classList.toggle('is-active', v.dataset.view === 'menu');
      });
      return true;
    }

    const moreOpen = target.closest('[data-more-open-dropdown]');
    if (moreOpen) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      openDropdownFromMore(moreOpen.dataset.moreOpenDropdown);
      return true;
    }

    return false;
  }

  const shopFilterbarEl = document.getElementById('shopFilterbar') || form;
  if (shopFilterbarEl) {
    shopFilterbarEl.addEventListener('click', handleMoreFiltersClick, true);
  }

  document.addEventListener('click', function(e){
    const mfOpen = e.target.closest('[data-mf-open]');
    if (mfOpen) {
      e.preventDefault();
      e.stopPropagation();

      const view = mfOpen.dataset.mfOpen;
      const ddMore = document.getElementById('ddMore');
      if (!ddMore || !view) return;

      ddMore.querySelectorAll('.mf-view').forEach(v => {
        v.classList.toggle('is-active', v.dataset.view === view);
      });

      return;
    }

    const mfBack = e.target.closest('[data-mf-back]');
    if (mfBack) {
      e.preventDefault();
      e.stopPropagation();

      const ddMore = document.getElementById('ddMore');
      if (!ddMore) return;

      ddMore.querySelectorAll('.mf-view').forEach(v => {
        v.classList.toggle('is-active', v.dataset.view === 'menu');
      });

      return;
    }

    const moreOpen = e.target.closest('[data-more-open-dropdown]');
    if (moreOpen) {
      e.preventDefault();
      e.stopPropagation();
      openDropdownFromMore(moreOpen.dataset.moreOpenDropdown);
      return;
    }

    const btn = e.target.closest('.shop-filterpill__btn, .shop-sort__btn');
    if (btn) {
      const wrap = btn.closest('[data-dropdown]');
      const id = wrap ? wrap.dataset.dropdown : '';
      const dd = id ? document.getElementById(id) : null;
      if (!dd) return;
      e.preventDefault();
      if (id === 'ddMore') {
        openMoreDropdown();
        return;
      }
      const willOpen = !dd.classList.contains('is-open');
      closeDropdowns(id);
      dd.classList.toggle('is-open', willOpen);
      wrap.classList.toggle('is-open', willOpen);
      return;
    }
    const close = e.target.closest('.shop-dropdown__close');
    if (close) {
      e.preventDefault();
      const id = close.dataset.close || (close.closest('.shop-dropdown') || {}).id;
      const dd = id ? document.getElementById(id) : close.closest('.shop-dropdown');
      if (dd) dd.classList.remove('is-open');
      document.querySelectorAll('[data-dropdown="' + CSS.escape(id || '') + '"]').forEach(w => w.classList.remove('is-open'));
      return;
    }
    if (!e.target.closest('.shop-dropdown') && !e.target.closest('[data-dropdown]')) closeDropdowns();
  });

  function initRange(root){
    const rangeInputs = root.querySelectorAll('input[type="range"]');
    if (rangeInputs.length < 2) return;
    const rMin = rangeInputs[0], rMax = rangeInputs[1];
    const nums = root.querySelectorAll('input[type="number"]');
    const nMin = root.querySelector('[data-range-min-input]') || nums[0];
    const nMax = root.querySelector('[data-range-max-input]') || nums[1];
    const fill = root.querySelector('.shop-range__fill');
    const labelMin = root.querySelector('[data-range-label-min]') || root.querySelector('.shop-price__labels span:first-child');
    const labelMax = root.querySelector('[data-range-label-max]') || root.querySelector('.shop-price__labels span:last-child');
    const track = root.querySelector('.shop-range__track');
    if (!nMin || !nMax || !fill) return;

    function bounds(){ return {min:Number(rMin.min || 0), max:Number(rMin.max || 100)}; }
    function sync(fromNums){
      const b = bounds();
      let minV = Number((fromNums ? nMin.value : rMin.value) || b.min);
      let maxV = Number((fromNums ? nMax.value : rMax.value) || b.max);
      minV = Math.max(b.min, Math.min(minV, b.max));
      maxV = Math.max(b.min, Math.min(maxV, b.max));
      if (minV > maxV) { const t = minV; minV = maxV; maxV = t; }
      rMin.value = minV; rMax.value = maxV; nMin.value = minV; nMax.value = maxV;
      [rMin, rMax, nMin, nMax].forEach(input => input.setAttribute('value', input.value));
      const left = ((minV - b.min) / Math.max(1, b.max - b.min)) * 100;
      const right = ((maxV - b.min) / Math.max(1, b.max - b.min)) * 100;
      fill.style.left = left + '%'; fill.style.width = Math.max(0, right - left) + '%';
      if (labelMin) labelMin.textContent = (nMin.name === 'price_min' ? '€' : '') + formatNumber(minV);
      if (labelMax) labelMax.textContent = (nMax.name === 'price_max' ? '€' : '') + formatNumber(maxV);
      updatePillSummaries();
    }
    function apply(){ sync(false); trigger(); }
    rMin.addEventListener('input', () => sync(false));
    rMax.addEventListener('input', () => sync(false));
    rMin.addEventListener('change', apply);
    rMax.addEventListener('change', apply);
    nMin.addEventListener('change', () => { sync(true); trigger(); });
    nMax.addEventListener('change', () => { sync(true); trigger(); });
    if (track) {
      let draggingHandle = null;
      const setFromPointer = (e, commit) => {
        const rect = track.getBoundingClientRect();
        const b = bounds();
        const ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / Math.max(1, rect.width)));
        const val = Math.round(b.min + ratio * (b.max - b.min));
        const curMin = Number(rMin.value || b.min), curMax = Number(rMax.value || b.max);
        if (!draggingHandle) draggingHandle = Math.abs(val - curMin) <= Math.abs(val - curMax) ? 'min' : 'max';
        if (draggingHandle === 'min') rMin.value = Math.min(val, Number(rMax.value || b.max));
        else rMax.value = Math.max(val, Number(rMin.value || b.min));
        sync(false);
        if (commit) trigger();
      };
      track.addEventListener('pointerdown', e => {
        e.preventDefault();
        draggingHandle = null;
        track.setPointerCapture?.(e.pointerId);
        setFromPointer(e, false);
      });
      track.addEventListener('pointermove', e => {
        if (e.buttons !== 1 || draggingHandle === null) return;
        setFromPointer(e, false);
      });
      track.addEventListener('pointerup', e => {
        if (draggingHandle !== null) setFromPointer(e, true);
        draggingHandle = null;
        track.releasePointerCapture?.(e.pointerId);
      });
      track.addEventListener('pointercancel', () => { draggingHandle = null; });
    }
    sync(false);
  }

  function initRanges(){
    document.querySelectorAll('.shop-price').forEach(initRange);
  }

  function getRangeKeyFromRoot(root){
    const explicit = root.getAttribute('data-range-key');
    if (explicit) return explicit;
    const minInput = root.querySelector('[data-range-min-input], input[type=number]');
    if (!minInput || !minInput.name) return '';
    return minInput.name.replace(/_min$/, '');
  }

  function refreshRangeVisual(root){
    const ranges = root.querySelectorAll('input[type="range"]');
    const rMin = root.querySelector('[data-range-min]') || ranges[0];
    const rMax = root.querySelector('[data-range-max]') || ranges[1];
    const nums = root.querySelectorAll('input[type="number"]');
    const nMin = root.querySelector('[data-range-min-input]') || nums[0];
    const nMax = root.querySelector('[data-range-max-input]') || nums[1];
    const fill = root.querySelector('.shop-range__fill');
    if (!rMin || !rMax || !nMin || !nMax || !fill) return;
    const bMin = Number(rMin.min || 0);
    const bMax = Number(rMin.max || 100);
    const minV = Number(nMin.value || rMin.value || bMin);
    const maxV = Number(nMax.value || rMax.value || bMax);
    rMin.value = String(minV);
    rMax.value = String(maxV);
    const left = ((minV - bMin) / Math.max(1, bMax - bMin)) * 100;
    const right = ((maxV - bMin) / Math.max(1, bMax - bMin)) * 100;
    fill.style.left = left + '%';
    fill.style.width = Math.max(0, right - left) + '%';
  }

  function setRangeBounds(root, minBound, maxBound){
    const nums = root.querySelectorAll('input[type="number"]');
    const nMin = root.querySelector('[data-range-min-input]') || nums[0];
    const nMax = root.querySelector('[data-range-max-input]') || nums[1];
    const ranges = root.querySelectorAll('input[type="range"]');
    const rMin = root.querySelector('[data-range-min]') || ranges[0];
    const rMax = root.querySelector('[data-range-max]') || ranges[1];
    if (!nMin || !nMax || !rMin || !rMax) return;

    let minV = Number(minBound);
    let maxV = Number(maxBound);
    if (!Number.isFinite(minV)) minV = 0;
    if (!Number.isFinite(maxV)) maxV = minV;
    minV = Math.floor(minV);
    maxV = Math.ceil(maxV);
    if (maxV < minV) { const tmp = minV; minV = maxV; maxV = tmp; }

    const oldMin = String(nMin.getAttribute('min') || rMin.getAttribute('min') || '0');
    const oldMax = String(nMax.getAttribute('max') || rMax.getAttribute('max') || nMax.value || rMax.value || '');
    const wasFullRange = String(nMin.value) === oldMin && String(nMax.value) === oldMax;

    [nMin, nMax, rMin, rMax].forEach(input => {
      input.min = String(minV);
      input.max = String(maxV);
    });

    if (wasFullRange) {
      nMin.value = String(minV);
      nMax.value = String(maxV);
    } else {
      const currentMin = Math.max(minV, Math.min(Number(nMin.value || minV), maxV));
      const currentMax = Math.max(minV, Math.min(Number(nMax.value || maxV), maxV));
      nMin.value = String(Math.min(currentMin, currentMax));
      nMax.value = String(Math.max(currentMin, currentMax));
    }
    rMin.value = nMin.value;
    rMax.value = nMax.value;

    // Important for mobile: cloneNode(true) copies HTML attributes, not only live input properties.
    // Keep attributes synced so the mobile bottom sheet never shows stale fallback values like 1000.
    [nMin, nMax, rMin, rMax].forEach(input => {
      input.setAttribute('min', input.min);
      input.setAttribute('max', input.max);
      input.setAttribute('value', input.value);
    });

    const labelMin = root.querySelector('[data-range-label-min]') || root.querySelector('.shop-price__labels span:first-child');
    const labelMax = root.querySelector('[data-range-label-max]') || root.querySelector('.shop-price__labels span:last-child');
    const prefix = nMin.name === 'price_min' ? '€' : '';
    if (labelMin) labelMin.textContent = prefix + formatNumber(minV);
    if (labelMax) labelMax.textContent = prefix + formatNumber(maxV);

    refreshRangeVisual(root);
  }

  function applyFilterRanges(ranges){
    if (!ranges || typeof ranges !== 'object') return;
    document.querySelectorAll('.shop-price').forEach(root => {
      const key = getRangeKeyFromRoot(root);
      if (!key || !ranges[key]) return;
      setRangeBounds(root, ranges[key].min, ranges[key].max);
    });
    updatePillSummaries();
    renderChips();
    refreshMoreFilters();
  }

  function updatePillSummaries(){
    document.querySelectorAll('.shop-filterpill').forEach(pill => {
      const value = pill.querySelector('.shop-filterpill__value');
      if (!value) return;
      const dd = document.getElementById(pill.dataset.dropdown || '');
      if (!dd) return;
      const checked = dd.querySelectorAll('input[type="checkbox"]:checked');
      if (checked.length) { value.textContent = checked.length; return; }
      const nums = dd.querySelectorAll('input[type="number"]');
      if (nums.length >= 2) {
        const min = nums[0], max = nums[1];
        const fullMin = String(min.getAttribute('min') || '0');
        const fullMax = String(max.getAttribute('max') || '');
        const prefix = min.name === 'price_min' ? '€' : '';
        value.textContent = (String(min.value) !== fullMin || String(max.value) !== fullMax) ? (prefix + min.value + ' – ' + prefix + max.value) : '';
      } else value.textContent = '';
    });
  }

  function renderChips(){
    if (!activeBox) return;
    const chips = [];
    const search = form.querySelector('[name="search"]');
    if (search && search.value.trim()) chips.push({kind:'search', label:'Search: ' + search.value.trim()});
    form.querySelectorAll('.shop-dropdown input[type="checkbox"]:checked').forEach(cb => {
      const txt = (cb.closest('.facet-item')?.querySelector('.facet-item__text')?.textContent || cb.value).trim();
      chips.push({kind:'checkbox', name:cb.name, value:cb.value, label:labelize(cb.name) + ': ' + txt});
    });
    form.querySelectorAll('.shop-price').forEach(root => {
      const nums = root.querySelectorAll('input[type="number"]');
      if (nums.length < 2) return;
      if (root.closest('#ddMore') && root.querySelector('[data-mobile-filter-clone]')) return;
      const min = nums[0], max = nums[1];
      if (String(min.value) === String(min.getAttribute('min') || '0') && String(max.value) === String(max.getAttribute('max') || max.value)) return;
      const base = min.name.replace(/_min$/,'');
      const prefix = base === 'price' ? '€' : '';
      chips.push({kind:'range', base, label:labelize(base) + ': ' + prefix + min.value + ' – ' + prefix + max.value});
    });
    activeBox.innerHTML = chips.map(c => `<button type="button" class="filter-chip" data-kind="${esc(c.kind)}" data-name="${esc(c.name||'')}" data-value="${esc(c.value||'')}" data-base="${esc(c.base||'')}"><span>${esc(c.label)}</span><span class="filter-chip__x">✕</span></button>`).join('');
    activeBox.classList.toggle('has-chips', chips.length > 0);
  }

  function trigger(){
    clearTimeout(timer);
    updateUrl(1, false);
    timer = setTimeout(() => fetchAccounts(1), 180);
    updatePillSummaries();
    renderChips();
  }

  function resetFilters(){
    clearTimeout(timer);
    closeDropdowns();

    if (form) form.reset();
    form.querySelectorAll('input[type="checkbox"]').forEach(cb => { cb.checked = false; });
    form.querySelectorAll('.facet-search__input').forEach(inp => { inp.value = ''; });
    form.querySelectorAll('.facet-item').forEach(item => { item.style.display = ''; });

    form.querySelectorAll('.shop-price').forEach(root => {
      const nums = root.querySelectorAll('input[type="number"]');
      if (nums[0]) nums[0].value = nums[0].getAttribute('min') || '0';
      if (nums[1]) nums[1].value = nums[1].getAttribute('max') || nums[1].value;
      const ranges = root.querySelectorAll('input[type="range"]');
      if (ranges[0]) ranges[0].value = ranges[0].getAttribute('min') || '0';
      if (ranges[1]) ranges[1].value = ranges[1].getAttribute('max') || ranges[1].value;
    });

    sortMode = 'recommended';
    const sortLabel = document.getElementById('sortLabel');
    if (sortLabel) sortLabel.textContent = 'Recommended';

    initRanges();
    updatePillSummaries();
    renderChips();

    if (activeBox) {
      activeBox.innerHTML = '';
      activeBox.classList.remove('has-chips');
      activeBox.scrollLeft = 0;
    }
    if (emptyBox) emptyBox.style.display = 'none';
    if (grid) grid.style.display = '';
    if (pagination) pagination.innerHTML = '';

    updateUrl(1, false);
    fetchAccounts(1);
  }

  function normalizeGridAfterRender(){
    if (!grid) return;
    grid.scrollLeft = 0;
    grid.style.removeProperty('transform');
    grid.style.removeProperty('left');
    grid.style.removeProperty('right');
  }


  function fitSellerNames(){
    if (!grid) return;
    grid.querySelectorAll('.seller-info__name-text').forEach(function(el){
      const wrap = el.closest('.seller-rank-trigger, .seller-info__name') || el.parentElement;
      if (!wrap) return;
      el.style.fontSize = '';
      el.style.transform = '';
      el.style.maxWidth = '100%';
      el.style.textOverflow = 'clip';
      const available = Math.max(0, wrap.clientWidth - 2);
      if (!available) return;
      const computed = window.getComputedStyle(el);
      const start = parseFloat(computed.fontSize) || 15;
      const min = 9;
      let size = start;
      el.style.fontSize = size + 'px';
      let guard = 0;
      while (el.scrollWidth > available && size > min && guard < 16) {
        size = Math.max(min, size - 0.5);
        el.style.fontSize = size + 'px';
        guard++;
      }
      if (el.scrollWidth > available) {
        const scale = Math.max(0.72, available / Math.max(1, el.scrollWidth));
        el.style.transform = 'scaleX(' + scale.toFixed(3) + ')';
        el.style.width = (100 / scale).toFixed(3) + '%';
      } else {
        el.style.width = '';
      }
    });
  }

  function fetchAccounts(page){
    const seq = ++requestSeq;
    const body = new FormData(form);
    // Open dropdowns can be portalled outside the form. Include every live facet.
    document.querySelectorAll('.facet-item__check:checked').forEach(function(input){
      body.delete(input.name);
    });
    const appendedNames = new Set();
    document.querySelectorAll('.facet-item__check:checked').forEach(function(input){
      if (!appendedNames.has(input.name)) {
        body.delete(input.name);
        appendedNames.add(input.name);
      }
      body.append(input.name, input.value);
    });
    const searchInput = document.getElementById('filterSearch');
    body.set('search', searchInput ? searchInput.value.trim() : '');
    body.set('page', String(page || 1));
    body.set('sort', sortMode);
    fetch(AJAX_ENDPOINT, {method:'POST', body, credentials:'same-origin'})
      .then(r => r.json())
      .then(data => {
        if (seq !== requestSeq || !data) return;
        if (data.filterRanges) {
          applyFilterRanges(data.filterRanges);
        } else if (data.priceRange) {
          applyFilterRanges({price: data.priceRange});
        }
        if (typeof data.html === 'string') {
          grid.innerHTML = data.html;
          normalizeGridAfterRender();
          fitSellerNames();
        }
        const total = Number(data.totalItems ?? data.total ?? grid.querySelectorAll('.account-card, [data-account-card]').length);
        if (countTotal) countTotal.textContent = total;
        if (countShown) countShown.textContent = total;
        if (emptyBox) emptyBox.style.display = total > 0 ? 'none' : '';
        if (grid) grid.style.display = total > 0 ? '' : 'none';
        if (pagination && data.paginationHtml) pagination.innerHTML = data.paginationHtml;
        requestAnimationFrame(function(){
          normalizeGridAfterRender();
          fitSellerNames();
        });
      })
      .catch(err => console.error('Account filter error:', err));
  }

  form.addEventListener('change', function(e){
    if (e.target.matches('input[type="checkbox"]')) trigger();
  });
  form.addEventListener('input', function(e){
    if (e.target.name === 'search') trigger();
  });
  document.addEventListener('input', function(e){
    if (e.target.matches('.facet-search__input')) {
      const q = e.target.value.toLowerCase();
      e.target.closest('.shop-dropdown')?.querySelectorAll('.facet-item').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    }
  });

  let mobileAutoApplyTimer = null;
  function mobileAutoApply(){
    clearTimeout(mobileAutoApplyTimer);
    mobileAutoApplyTimer = setTimeout(function(){
      trigger();
    }, 220);
  }

  function syncMobileCloneToSource(el){
    const ddId = el.dataset.sourceDropdown;
    if (!ddId) return;
    const source = document.getElementById(ddId);
    if (!source) return;
    if (el.matches('input[type="checkbox"]')) {
      source.querySelectorAll('input[type="checkbox"][name="' + CSS.escape(el.name) + '"][value="' + CSS.escape(el.value) + '"]').forEach(cb => cb.checked = el.checked);
    } else if (el.name) {
      source.querySelectorAll('[name="' + CSS.escape(el.name) + '"]').forEach(src => {
        if (src !== el) src.value = el.value;
      });
      const root = source.querySelector('.shop-price');
      if (root) initRange(root);
    }
  }

  document.addEventListener('change', function(e){
    if (!e.target.matches('[data-mobile-filter-clone]')) return;
    syncMobileCloneToSource(e.target);
    updatePillSummaries();
    renderChips();
    mobileAutoApply();
  });
  document.addEventListener('input', function(e){
    if (!e.target.matches('[data-mobile-filter-clone]')) return;
    syncMobileCloneToSource(e.target);
    updatePillSummaries();
    renderChips();
    mobileAutoApply();
  });
  document.addEventListener('click', function(e){
    const apply = e.target.closest('[data-mobile-apply]');
    if (apply) {
      e.preventDefault();
      closeDropdowns();
      trigger();
    }
    const mobileSort = e.target.closest('.mobile-sort-choice[data-sort]');
    if (mobileSort) {
      e.preventDefault();
      sortMode = mobileSort.dataset.sort || 'recommended';
      const sortLabel = document.getElementById('sortLabel');
      const sourceSort = document.querySelector('.shop-menuitem[data-sort="' + CSS.escape(sortMode) + '"]');
      if (sortLabel && sourceSort) sortLabel.textContent = sourceSort.textContent.trim() || 'Recommended';
      document.querySelectorAll('.mobile-sort-choice').forEach(btn => btn.classList.toggle('is-active', btn.dataset.sort === sortMode));
      updatePillSummaries();
      renderChips();
      mobileAutoApply();
    }
  });

  document.addEventListener('click', function(e){
    const sort = e.target.closest('.shop-menuitem[data-sort]');
    if (sort) {
      sortMode = sort.dataset.sort || 'recommended';
      const label = sort.textContent.trim();
      const sortLabel = document.getElementById('sortLabel');
      if (sortLabel) sortLabel.textContent = label || 'Recommended';
      closeDropdowns();
      trigger();
    }
    const chip = e.target.closest('.filter-chip');
    if (chip) {
      if (chip.dataset.kind === 'search') form.querySelector('[name="search"]').value = '';
      if (chip.dataset.kind === 'checkbox') form.querySelectorAll('input[name="' + CSS.escape(chip.dataset.name) + '"][value="' + CSS.escape(chip.dataset.value) + '"]').forEach(cb => cb.checked = false);
      if (chip.dataset.kind === 'range') {
        const base = chip.dataset.base;
        const min = form.querySelector('[name="' + CSS.escape(base + '_min') + '"]');
        const max = form.querySelector('[name="' + CSS.escape(base + '_max') + '"]');
        if (min) min.value = min.getAttribute('min') || 0;
        if (max) max.value = max.getAttribute('max') || max.value;
        document.querySelectorAll('.shop-price').forEach(initRange);
      }
      trigger();
    }
    if (e.target.closest('.reset-filters--ghost') || e.target.closest('#btnResetFiltersEmpty')) {
      e.preventDefault();
      resetFilters();
    }
  });


  document.addEventListener('click', function(e){
    const btn = e.target.closest('#shopPagination .page-btn, #shopPagination [data-page]');
    if (!btn) return;
    if (btn.classList.contains('is-disabled') || btn.classList.contains('is-active')) return;
    const page = parseInt(btn.dataset.page || btn.getAttribute('data-page') || btn.textContent, 10) || 1;
    e.preventDefault();
    closeDropdowns();
    updateUrl(page, false);
    fetchAccounts(page);
  });

  window.addEventListener('popstate', function(){
    buildFacets();
    initRanges();
    const page = applyStateFromUrl();
    fetchAccounts(page || 1);
  });

  buildFacets();
  buildPopularPills();
  initRanges();
  refreshMoreFilters();
  const initialFetchPage = applyStateFromUrl();
  syncPopularPills();
  updatePillSummaries();
  renderChips();
  if (countShown) countShown.textContent = grid.querySelectorAll('.account-card, [data-account-card]').length || countShown.textContent || '0';
  requestAnimationFrame(fitSellerNames);

  requestAnimationFrame(function(){
    fetchAccounts(initialFetchPage || 1);
  });
})();
</script>

<style>
/* Mobile scroll header: after scrolling, only Search + Filters stay as the top navbar. */
@media (max-width:768px){
  body.shop-mobile-filterbar-fixed .ranked-accounts-page .shop-filterbar,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page .shop-filterbar{
    position:fixed!important;
    top:0!important;
    left:0!important;
    right:0!important;
    width:100%!important;
    max-width:100%!important;
    z-index:2147483000!important;
    margin:0!important;
    padding:8px 10px!important;
    background:rgba(9,10,18,.98)!important;
    border-bottom:1px solid rgba(255,255,255,.10)!important;
    box-shadow:0 10px 28px rgba(0,0,0,.45)!important;
    backdrop-filter:blur(14px)!important;
  }
  body.shop-mobile-filterbar-fixed .ranked-accounts-page .shop-filterbar__row,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page .shop-filterbar__row{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) auto!important;
    gap:8px!important;
    width:100%!important;
    max-width:100%!important;
    padding:0!important;
    border:0!important;
    border-radius:0!important;
    background:transparent!important;
  }
  body.shop-mobile-filterbar-fixed .ranked-accounts-page .shop-filterbar__search,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page .shop-filterbar__search{
    height:48px!important;
    width:100%!important;
    min-width:0!important;
    margin:0!important;
    border-radius:8px!important;
    background:#0b0f17!important;
    border:1px solid rgba(255,255,255,.10)!important;
  }
  body.shop-mobile-filterbar-fixed .ranked-accounts-page [data-dropdown="ddMore"],
  body.shop-mobile-filterbar-fixed.ranked-accounts-page [data-dropdown="ddMore"]{
    display:flex!important;
    width:auto!important;
    min-width:88px!important;
    margin:0!important;
  }
  body.shop-mobile-filterbar-fixed .ranked-accounts-page #btnMore,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page #btnMore{
    height:48px!important;
    width:auto!important;
    min-width:88px!important;
    padding:0 12px!important;
    border-radius:8px!important;
    background:#0b0f17!important;
    border:1px solid rgba(255,255,255,.10)!important;
  }
  body.shop-mobile-filterbar-fixed .ranked-accounts-page .shop-filterbar__chips,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page .shop-filterbar__chips{
    display:none!important;
  }
  .shop-mobile-filterbar-spacer{
    display:none;
  }
  body.shop-mobile-filterbar-fixed .shop-mobile-filterbar-spacer{
    display:block;
    height:64px;
  }
}
</style>
<script>
(function(){
  const mq = window.matchMedia('(max-width: 768px)');
  const bar = document.getElementById('shopFilterbar');
  if (!bar) return;

  let triggerY = 0;
  let spacer = null;

  function ensureSpacer(){
    if (spacer) return spacer;
    spacer = document.createElement('div');
    spacer.className = 'shop-mobile-filterbar-spacer';
    bar.parentNode.insertBefore(spacer, bar);
    return spacer;
  }

  function recalc(){
    if (!mq.matches) {
      document.body.classList.remove('shop-mobile-filterbar-fixed');
      return;
    }
    const wasFixed = document.body.classList.contains('shop-mobile-filterbar-fixed');
    if (wasFixed) document.body.classList.remove('shop-mobile-filterbar-fixed');
    triggerY = bar.getBoundingClientRect().top + window.pageYOffset;
    if (wasFixed) document.body.classList.add('shop-mobile-filterbar-fixed');
    ensureSpacer().style.height = Math.ceil(bar.offsetHeight) + 'px';
    onScroll();
  }

  function onScroll(){
    if (!mq.matches) {
      document.body.classList.remove('shop-mobile-filterbar-fixed', 'shop-mobile-page-scrolled');
      return;
    }

    const y = window.pageYOffset || document.documentElement.scrollTop || 0;
    document.body.classList.toggle('shop-mobile-page-scrolled', y > 8);

    const isFixed = document.body.classList.contains('shop-mobile-filterbar-fixed');
    const fixAt = triggerY + 6;
    const unfixAt = Math.max(0, triggerY - 48);

    if (!isFixed && y >= fixAt) {
      document.body.classList.add('shop-mobile-filterbar-fixed');
    } else if (isFixed && y <= unfixAt) {
      document.body.classList.remove('shop-mobile-filterbar-fixed');
    }
  }

  ensureSpacer();
  window.addEventListener('scroll', onScroll, {passive:true});
  window.addEventListener('resize', recalc, {passive:true});
  window.addEventListener('orientationchange', function(){ setTimeout(recalc, 250); }, {passive:true});
  setTimeout(recalc, 50);
  setTimeout(recalc, 500);
})();
</script>

<style>
/* Final mobile scroll header correction: keep the original rounded filterbar look and hide the old mobile nav/gamebar while scrolling. */
@media (max-width:768px){
  .ranked-accounts-page .shop-filterbar,
  body.ranked-accounts-page .shop-filterbar{
    position:relative!important;
    top:auto!important;
    left:auto!important;
    right:auto!important;
    width:auto!important;
    max-width:100%!important;
    z-index:80!important;
    margin:18px 0 16px!important;
    padding:0!important;
    background:transparent!important;
    border:0!important;
    box-shadow:none!important;
    backdrop-filter:none!important;
  }

  .ranked-accounts-page .shop-filterbar__row,
  body.ranked-accounts-page .shop-filterbar__row{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) auto!important;
    align-items:center!important;
    gap:8px!important;
    width:100%!important;
    max-width:100%!important;
    padding:10px!important;
    border-radius:22px!important;
    background:rgba(255,255,255,.045)!important;
    border:1px solid rgba(255,255,255,.08)!important;
    box-shadow:none!important;
    overflow:visible!important;
  }

  .ranked-accounts-page .shop-filterbar__search,
  body.ranked-accounts-page .shop-filterbar__search{
    grid-column:auto!important;
    width:100%!important;
    min-width:0!important;
    height:46px!important;
    margin:0!important;
    padding:0 14px!important;
    border-radius:999px!important;
    background:rgba(255,255,255,.06)!important;
    border:1px solid rgba(255,255,255,.08)!important;
  }

  .ranked-accounts-page [data-dropdown="ddMore"],
  body.ranked-accounts-page [data-dropdown="ddMore"]{
    display:flex!important;
    width:auto!important;
    min-width:112px!important;
    margin:0!important;
  }

  .ranked-accounts-page #btnMore,
  body.ranked-accounts-page #btnMore{
    height:46px!important;
    width:auto!important;
    min-width:112px!important;
    padding:0 16px!important;
    border-radius:999px!important;
    background:rgba(255,255,255,.065)!important;
    border:1px solid rgba(255,255,255,.10)!important;
    justify-content:center!important;
    gap:8px!important;
  }

  .ranked-accounts-page #btnMore .fa-caret-down,
  body.ranked-accounts-page #btnMore .fa-caret-down{
    display:inline-flex!important;
  }

  .ranked-accounts-page #btnMore .shop-filterpill__value,
  body.ranked-accounts-page #btnMore .shop-filterpill__value{
    display:none!important;
  }

  body.shop-mobile-filterbar-fixed .ranked-accounts-page .shop-filterbar,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page .shop-filterbar{
    position:fixed!important;
    top:0!important;
    left:0!important;
    right:0!important;
    width:100%!important;
    max-width:100%!important;
    z-index:2147483000!important;
    margin:0!important;
    padding:8px 10px!important;
    background:rgba(12,10,25,.96)!important;
    border:0!important;
    border-bottom:1px solid rgba(255,255,255,.08)!important;
    box-shadow:0 12px 28px rgba(0,0,0,.42)!important;
    backdrop-filter:blur(14px)!important;
  }

  body.shop-mobile-filterbar-fixed .ranked-accounts-page .shop-filterbar__row,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page .shop-filterbar__row{
    padding:10px!important;
    border-radius:22px!important;
    background:rgba(255,255,255,.045)!important;
    border:1px solid rgba(255,255,255,.08)!important;
  }

  body.shop-mobile-filterbar-fixed .navbar-mobile,
  body.shop-mobile-filterbar-fixed .navbar-mobile.scrolled,
  body.shop-mobile-filterbar-fixed .lb-mobile-gamebar,
  body.shop-mobile-filterbar-fixed .lb-mobile-gamebar.scrolled,
  body.shop-mobile-filterbar-fixed .mobile-gamebar,
  body.shop-mobile-filterbar-fixed .mobile-gamebar.scrolled{
    display:none!important;
    visibility:hidden!important;
    opacity:0!important;
    pointer-events:none!important;
  }

  body.shop-mobile-filterbar-fixed .shop-mobile-filterbar-spacer{
    display:block!important;
    height:74px!important;
  }
}
</style>


<style>
/* Anti flicker fix: hide the old mobile header/gamebar as soon as the page is scrolled, not only after the filterbar becomes fixed. */
@media (max-width:768px){
  body.shop-mobile-page-scrolled .navbar-mobile,
  body.shop-mobile-page-scrolled .navbar-mobile.scrolled,
  body.shop-mobile-page-scrolled .lb-mobile-gamebar,
  body.shop-mobile-page-scrolled .lb-mobile-gamebar.scrolled,
  body.shop-mobile-page-scrolled .mobile-gamebar,
  body.shop-mobile-page-scrolled .mobile-gamebar.scrolled{
    display:none!important;
    visibility:hidden!important;
    opacity:0!important;
    pointer-events:none!important;
  }

  body.shop-mobile-filterbar-fixed .ranked-accounts-page .shop-filterbar,
  body.shop-mobile-filterbar-fixed.ranked-accounts-page .shop-filterbar{
    transform:translateZ(0)!important;
    will-change:transform!important;
  }
}
</style>


<style>

/* Pokémon filterbar fix: an older global #activeFilters rule was overriding .shop-filterbar__chips display:none.
   Keep the chip row completely hidden until real filter chips exist. */
.ranked-accounts-page #activeFilters.shop-filterbar__chips:not(.has-chips){
  display:none!important;
  min-height:0!important;
  height:0!important;
  margin:0!important;
  padding:0!important;
  border:0!important;
  overflow:hidden!important;
}
.ranked-accounts-page #activeFilters.shop-filterbar__chips.has-chips{
  display:flex!important;
  min-height:34px!important;
  height:auto!important;
  margin:12px 0 0!important;
  padding:0!important;
  background:transparent!important;
  border:0!important;
}

/* Keep dropdowns above the cards and prevent the hidden chip row from stretching the filter area. */
.ranked-accounts-page .shop-filterbar{
  isolation:isolate;
}
.ranked-accounts-page .shop-dropdown.is-open{
  z-index:99999!important;
}

</style>

<style id="pokemon-chips-dropdown-zindex-fix">
/* Keep selected filter chips below opened dropdown menus. */
.ranked-accounts-page #activeFilters.shop-filterbar__chips{
  position:relative!important;
  z-index:1!important;
}
.ranked-accounts-page .shop-filterpill,
.ranked-accounts-page .shop-sort{
  z-index:20!important;
}
.ranked-accounts-page .shop-filterpill.is-open,
.ranked-accounts-page .shop-sort.is-open{
  z-index:200000!important;
}
.ranked-accounts-page .shop-dropdown.is-open{
  z-index:200001!important;
}
</style>


<style id="pokemon-dropdown-over-chips-final-fix">
/* Final stacking fix: the opened dropdown is inside .shop-filterbar__row, while the chips row is a later sibling.
   Give the toolbar row its own higher stack level so later chips cannot paint over dropdowns. */
.ranked-accounts-page .shop-filterbar{
  position:relative!important;
  overflow:visible!important;
  isolation:isolate!important;
}
.ranked-accounts-page .shop-filterbar__form{
  position:relative!important;
  overflow:visible!important;
}
.ranked-accounts-page .shop-filterbar__row{
  position:relative!important;
  z-index:10000!important;
  overflow:visible!important;
}
.ranked-accounts-page #activeFilters.shop-filterbar__chips{
  position:relative!important;
  z-index:1!important;
}
.ranked-accounts-page .shop-filterpill,
.ranked-accounts-page .shop-sort{
  position:relative!important;
  z-index:10001!important;
}
.ranked-accounts-page .shop-filterpill.is-open,
.ranked-accounts-page .shop-sort.is-open{
  z-index:10002!important;
}
.ranked-accounts-page .shop-dropdown.is-open{
  z-index:10003!important;
}
</style>

<style id="lb-subdomain-dynamic-accounts-grid-final">
/* Subdomain accounts page final layout: 4 stable cards on desktop, no auto-fill zoom collapse. */
body.ranked-accounts-page #accountsGrid.accounts-grid,
.ranked-accounts-page #accountsGrid.accounts-grid{
  display:grid !important;
  grid-template-columns:repeat(4, minmax(0, 1fr)) !important;
  justify-content:stretch !important;
  align-items:stretch !important;
  gap:24px !important;
  width:100% !important;
  max-width:100% !important;
  min-width:0 !important;
  box-sizing:border-box !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card{
  width:100% !important;
  min-width:0 !important;
  max-width:none !important;
  min-height:0 !important;
  box-sizing:border-box !important;
  overflow:hidden !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .cover-link,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .cover-link{
  min-width:0 !important;
  width:100% !important;
  box-sizing:border-box !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .title,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .excerpt,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .title,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .excerpt{
  min-width:0 !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals{
  display:grid !important;
  grid-template-columns:minmax(0, 1fr) auto !important;
  align-items:center !important;
  justify-content:stretch !important;
  gap:10px !important;
  width:100% !important;
  min-width:0 !important;
  overflow:hidden !important;
  box-sizing:border-box !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .price-eur,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .price-eur{
  min-width:0 !important;
  max-width:100% !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  white-space:nowrap !important;
  font-size:clamp(20px, 1.12vw, 27px) !important;
  line-height:1.05 !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn.primary,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn.primary{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  width:auto !important;
  min-width:104px !important;
  max-width:124px !important;
  flex:0 0 auto !important;
  white-space:nowrap !important;
  padding:10px 16px !important;
  font-size:13px !important;
  line-height:1 !important;
  box-sizing:border-box !important;
  position:relative !important;
  right:auto !important;
  transform:none !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn i,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn.primary i,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn i,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn.primary i{
  margin-left:0 !important;
  font-size:13px !important;
  flex:0 0 auto !important;
}

@media (max-width:1199px){
  body.ranked-accounts-page #accountsGrid.accounts-grid,
  .ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:repeat(3, minmax(0, 1fr)) !important;gap:20px !important;}
}
@media (max-width:940px){
  body.ranked-accounts-page #accountsGrid.accounts-grid,
  .ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:repeat(2, minmax(0, 1fr)) !important;}
}
@media (max-width:767px){
  body.ranked-accounts-page #accountsGrid.accounts-grid,
  .ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:1fr !important;gap:18px !important;width:100% !important;}
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
  .ranked-accounts-page #accountsGrid.accounts-grid .account-card{width:100% !important;max-width:none !important;min-width:0 !important;}
}
</style>


<style id="lb-subdomain-seller-footer-harmonisch-v3">

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__avatar,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__avatar,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__avatar,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__avatar,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__avatar,
body.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__avatar,
.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar{
  width:36px !important;
  height:36px !important;
  min-width:36px !important;
  min-height:36px !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__stat,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__stat,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__stat,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__stat,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__sold,
body.ranked-accounts-page #accountsGrid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid .account-card .seller-info__sold{
  background:rgba(255,255,255,.045) !important;
  border-color:rgba(255,255,255,.06) !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
body.ranked-accounts-page #accountsGrid .account-card,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
.ranked-accounts-page #accountsGrid .account-card{
  overflow:hidden !important;
}

</style>

<style id="lb-account-shop-hero-redesign-final">
body.ranked-accounts-page .lb-shop-hero{
  position:relative!important;
  overflow:hidden!important;
  background:#0e0c1c!important;
  border:0!important;
  border-bottom:1px solid rgba(255,255,255,.06)!important;
  margin-bottom:0!important;
  padding:0!important;
}
body.ranked-accounts-page .lb-shop-hero::before{display:none!important;content:none!important;}
body.ranked-accounts-page .lb-shop-hero__inner{
  width:100%!important;
  max-width:1500px!important;
  min-height:170px!important;
  margin:0 auto!important;
  padding:36px 28px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:flex-start!important;
  gap:22px!important;
  border-radius:0!important;
  background:transparent!important;
  border:0!important;
  box-shadow:none!important;
  overflow:visible!important;
}
body.ranked-accounts-page .lb-shop-hero__icon{
  width:74px!important;
  height:74px!important;
  min-width:74px!important;
  border-radius:20px!important;
  background:rgba(255,255,255,.045)!important;
  border:1px solid rgba(255,255,255,.10)!important;
  box-shadow:0 18px 50px rgba(0,0,0,.28)!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  overflow:hidden!important;
}
body.ranked-accounts-page .lb-shop-hero__icon i{font-size:30px!important;color:#7c6cff!important;}
body.ranked-accounts-page .lb-shop-hero__kicker{display:block!important;font-size:12px!important;letter-spacing:.13em!important;text-transform:uppercase!important;color:#8b9bff!important;font-weight:900!important;margin:0 0 8px!important;line-height:1.15!important;}
body.ranked-accounts-page .lb-shop-hero__title{margin:0!important;font-size:29px!important;line-height:1.12!important;font-weight:950!important;letter-spacing:-.03em!important;color:#fff!important;}
body.ranked-accounts-page .lb-shop-hero__desc{margin:8px 0 0!important;max-width:640px!important;font-size:15px!important;line-height:1.45!important;color:#a9adc4!important;font-weight:600!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
body.ranked-accounts-page .container{padding-top:34px!important;}
body.ranked-accounts-page .account-type-cards{margin-top:0!important;margin-bottom:26px!important;position:relative!important;z-index:1!important;}
body.ranked-accounts-page .shop-filterbar{margin-top:0!important;}
@media(max-width:760px){
  body.ranked-accounts-page .lb-shop-hero{overflow:visible!important;background:#0e0c1c!important;border-bottom:1px solid rgba(255,255,255,.06)!important;}
  body.ranked-accounts-page .lb-shop-hero__inner{width:100%!important;max-width:100%!important;min-width:0!important;display:grid!important;grid-template-columns:42px minmax(0,1fr)!important;align-items:flex-start!important;gap:10px!important;min-height:0!important;padding:14px 16px 24px!important;margin:0!important;overflow:visible!important;}
  body.ranked-accounts-page .lb-shop-hero__inner > div:last-child{min-width:0!important;width:100%!important;max-width:100%!important;overflow:visible!important;}
  body.ranked-accounts-page .lb-shop-hero__icon{width:40px!important;height:40px!important;min-width:40px!important;border-radius:12px!important;margin-top:2px!important;}
  body.ranked-accounts-page .lb-shop-hero__icon i{font-size:19px!important;}
  body.ranked-accounts-page .lb-shop-hero__kicker{display:block!important;margin:0 0 4px!important;font-size:10px!important;line-height:1.15!important;white-space:normal!important;overflow:visible!important;}
  body.ranked-accounts-page .lb-shop-hero__title{display:block!important;width:100%!important;max-width:none!important;margin:0!important;font-size:18px!important;line-height:1.22!important;white-space:normal!important;overflow:visible!important;text-overflow:clip!important;overflow-wrap:break-word!important;word-break:normal!important;}
  body.ranked-accounts-page .lb-shop-hero__desc{display:block!important;width:100%!important;max-width:none!important;margin:5px 0 0!important;font-size:12.5px!important;line-height:1.35!important;white-space:normal!important;overflow:visible!important;text-overflow:clip!important;}
  body.ranked-accounts-page .container{padding-top:22px!important;}
}
@media(max-width:380px){
  body.ranked-accounts-page .lb-shop-hero__inner{grid-template-columns:38px minmax(0,1fr)!important;padding-left:14px!important;padding-right:14px!important;}
  body.ranked-accounts-page .lb-shop-hero__icon{width:36px!important;height:36px!important;min-width:36px!important;}
  body.ranked-accounts-page .lb-shop-hero__title{font-size:17px!important;}
  body.ranked-accounts-page .lb-shop-hero__desc{font-size:12px!important;}
}
</style>

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
  body.ranked-accounts-page main,
  body.ranked-accounts-page .page-zoom main{overflow:visible!important;}
  body.ranked-accounts-page .lb-shop-hero{background:#0f0c1f!important;border-bottom:1px solid rgba(255,255,255,.06)!important;margin-bottom:0!important;}
  body.ranked-accounts-page .lb-shop-hero__inner{padding:12px 16px 18px!important;gap:10px!important;align-items:flex-start!important;}
  body.ranked-accounts-page .lb-shop-hero__icon{width:38px!important;height:38px!important;min-width:38px!important;border-radius:12px!important;}
  body.ranked-accounts-page .lb-shop-hero__title{font-size:17px!important;line-height:1.18!important;}
  body.ranked-accounts-page .lb-shop-hero__desc{font-size:12px!important;line-height:1.32!important;margin-top:5px!important;}
  body.ranked-accounts-page .container{padding-top:14px!important;}
  body.ranked-accounts-page .account-type-cards{margin-top:0!important;margin-bottom:14px!important;}
  body.ranked-accounts-page .shop-filterbar{margin-top:0!important;}
  body.ranked-accounts-page .lb-shop-empty-notify-offset{
    --lb-empty-top-gap:74px!important;
    --lb-empty-bottom-gap:74px!important;
    min-height:calc(100svh - var(--lb-empty-page-chrome, 250px))!important;
    padding-left:16px!important;
    padding-right:16px!important;
  }
}
@media(max-width:420px){
  body.ranked-accounts-page .lb-shop-hero__inner{padding:12px 14px 16px!important;}
  body.ranked-accounts-page .container{padding-top:12px!important;}
  body.ranked-accounts-page .lb-shop-empty-notify-offset{--lb-empty-top-gap:78px!important;--lb-empty-bottom-gap:72px!important;}
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


<style id="lb-dynamic-shop-redesign-final-v1">
/* Dynamic accounts shop, aligned with the current LoL and Valorant shops. */
html body.ranked-accounts-page{
  background:#050713 !important;
}

html body.ranked-accounts-page .lb-shop-hero__inner,
html body.ranked-accounts-page > .container,
html body.ranked-accounts-page main > .container,
html body.ranked-accounts-page .container{
  width:min(1760px, calc(100vw - 42px)) !important;
  max-width:min(1760px, calc(100vw - 42px)) !important;
  margin-left:auto !important;
  margin-right:auto !important;
}
html body.ranked-accounts-page > .container,
html body.ranked-accounts-page main > .container,
html body.ranked-accounts-page .container{
  padding-left:0 !important;
  padding-right:0 !important;
}

html body.ranked-accounts-page .lb-shop-hero{
  margin:0 !important;
  padding:0 !important;
  background:#080a17 !important;
  border-bottom:1px solid rgba(255,255,255,.055) !important;
  overflow:visible !important;
}
html body.ranked-accounts-page .lb-shop-hero__inner{
  min-height:148px !important;
  padding:34px 0 28px !important;
  gap:18px !important;
  align-items:center !important;
}
html body.ranked-accounts-page .lb-shop-hero__icon{
  width:66px !important;
  height:66px !important;
  min-width:66px !important;
  border-radius:18px !important;
  background:linear-gradient(145deg,rgba(99,102,241,.16),rgba(255,255,255,.035)) !important;
  border:1px solid rgba(124,146,255,.20) !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page .lb-shop-hero__icon i{
  font-size:28px !important;
  color:#7f8cff !important;
}
html body.ranked-accounts-page .lb-shop-hero__kicker{
  margin:0 0 7px !important;
  font-size:12px !important;
  line-height:1 !important;
  letter-spacing:.14em !important;
  color:#8b9bff !important;
}
html body.ranked-accounts-page .lb-shop-hero__title{
  margin:0 !important;
  font-size:36px !important;
  line-height:1.08 !important;
  letter-spacing:-.035em !important;
  font-weight:950 !important;
}
html body.ranked-accounts-page .lb-shop-hero__desc{
  max-width:980px !important;
  margin:9px 0 0 !important;
  font-size:16px !important;
  line-height:1.5 !important;
  color:#9fa8c4 !important;
  white-space:normal !important;
  overflow:visible !important;
  text-overflow:clip !important;
}

html body.ranked-accounts-page #shopFilterbar.shop-filterbar{
  position:relative !important;
  top:auto !important;
  z-index:100 !important;
  margin:22px 0 18px !important;
  padding:12px !important;
  border-radius:20px !important;
  background:rgba(10,12,27,.96) !important;
  border:1px solid rgba(255,255,255,.07) !important;
  box-shadow:none !important;
  overflow:visible !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__form{
  width:100% !important;
  background:transparent !important;
  border:0 !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
  display:flex !important;
  align-items:center !important;
  flex-wrap:nowrap !important;
  gap:9px !important;
  width:100% !important;
  min-height:48px !important;
  padding:0 !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  overflow:visible !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{
  order:0 !important;
  flex:1 1 340px !important;
  min-width:260px !important;
  height:46px !important;
  padding:0 15px !important;
  border-radius:13px !important;
  background:#151827 !important;
  border:1px solid rgba(255,255,255,.075) !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search input{
  font-size:14px !important;
  font-weight:650 !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterpill,
html body.ranked-accounts-page #shopFilterbar .shop-sort{
  flex:0 0 auto !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn,
html body.ranked-accounts-page #shopFilterbar .shop-sort__btn{
  height:46px !important;
  padding:0 14px !important;
  gap:8px !important;
  border-radius:13px !important;
  background:#151827 !important;
  border:1px solid rgba(255,255,255,.075) !important;
  box-shadow:none !important;
  font-size:13.5px !important;
  font-weight:850 !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn:hover,
html body.ranked-accounts-page #shopFilterbar .shop-sort__btn:hover,
html body.ranked-accounts-page #shopFilterbar .shop-filterpill.is-open .shop-filterpill__btn,
html body.ranked-accounts-page #shopFilterbar .shop-sort.is-open .shop-sort__btn{
  background:#1a1e31 !important;
  border-color:rgba(99,102,241,.32) !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__actions{
  order:90 !important;
  display:flex !important;
  align-items:center !important;
  gap:9px !important;
  margin-left:auto !important;
}
html body.ranked-accounts-page #shopFilterbar .reset-filters--ghost{
  height:46px !important;
  padding:0 20px !important;
  border-radius:13px !important;
  border:0 !important;
  background:linear-gradient(135deg,#596ff5,#7161ef) !important;
  box-shadow:none !important;
  font-size:13.5px !important;
  font-weight:900 !important;
}
html body.ranked-accounts-page #activeFilters.shop-filterbar__chips{
  min-height:0 !important;
  margin:10px 0 0 !important;
  padding:8px 10px !important;
  border-radius:11px !important;
  background:#0d1020 !important;
  border:1px solid rgba(255,255,255,.055) !important;
}
html body.ranked-accounts-page #activeFilters.shop-filterbar__chips:not(.has-chips){
  display:none !important;
}
html body.ranked-accounts-page .filter-chip{
  min-height:34px !important;
  padding:8px 12px !important;
  font-size:12.5px !important;
  background:#171a2a !important;
  border-color:rgba(255,255,255,.075) !important;
}

html body.ranked-accounts-page .shop-toolbar{
  margin:4px 0 15px !important;
}
html body.ranked-accounts-page .shop-count{
  font-size:14px !important;
  font-weight:850 !important;
  color:#aeb6cf !important;
}

html body.ranked-accounts-page #accountsGrid.accounts-grid{
  display:grid !important;
  grid-template-columns:repeat(5,minmax(0,1fr)) !important;
  gap:18px !important;
  width:100% !important;
  max-width:none !important;
  align-items:stretch !important;
  margin:0 !important;
}
html body.ranked-accounts-page #accountsGrid .account-card{
  width:100% !important;
  min-width:0 !important;
  max-width:none !important;
  height:100% !important;
  border-radius:18px !important;
  background:linear-gradient(180deg,rgba(14,17,33,.99),rgba(10,12,24,.99)) !important;
  border:1px solid rgba(255,255,255,.075) !important;
  box-shadow:none !important;
  overflow:hidden !important;
}
html body.ranked-accounts-page #accountsGrid .account-card:hover{
  transform:translateY(-2px) !important;
  border-color:rgba(99,102,241,.28) !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .title,
html body.ranked-accounts-page #accountsGrid .account-card .account-card-title-text{
  font-size:16px !important;
  line-height:1.25 !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .excerpt{
  font-size:12px !important;
  line-height:1.5 !important;
  color:#9aa3c0 !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .highlights .badge{
  font-size:10.5px !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .totals .price-eur{
  font-size:21px !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .totals .btn,
html body.ranked-accounts-page #accountsGrid .account-card .totals .btn.primary{
  min-width:112px !important;
  height:40px !important;
  padding:0 14px !important;
  border-radius:11px !important;
  font-size:12.5px !important;
}

html body.ranked-accounts-page #shopPagination.shop-pagination{
  width:100% !important;
  margin:44px 0 0 !important;
  padding:0 !important;
}
html body.ranked-accounts-page #shopPagination .page-bar{
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  flex-wrap:wrap !important;
  margin:0 auto !important;
}
html body.ranked-accounts-page #shopPagination .page-btn{
  min-width:42px !important;
  height:42px !important;
  padding:0 13px !important;
  border-radius:11px !important;
  font-size:13px !important;
  font-weight:850 !important;
}
html body.ranked-accounts-page #shopPagination .page-btn.is-nav{
  min-width:76px !important;
}
html body.ranked-accounts-page .container{
  padding-bottom:90px !important;
}

html body.ranked-accounts-page .shop-dropdown{
  border-radius:16px !important;
  background:rgba(15,17,31,.98) !important;
  border:1px solid rgba(255,255,255,.09) !important;
  box-shadow:0 22px 60px rgba(0,0,0,.52) !important;
}
html body.ranked-accounts-page .shop-dropdown__head{
  font-size:14px !important;
}
html body.ranked-accounts-page .facet-item,
html body.ranked-accounts-page .shop-menuitem,
html body.ranked-accounts-page .mf-menuitem{
  font-size:13.5px !important;
}

@media(max-width:1700px){
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(4,minmax(0,1fr)) !important;
  }
}
@media(max-width:1320px){
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(3,minmax(0,1fr)) !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
    flex-wrap:wrap !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{
    flex:1 1 420px !important;
  }
}
@media(max-width:980px){
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(2,minmax(0,1fr)) !important;
  }
}
@media(max-width:767px){
  html body.ranked-accounts-page .lb-shop-hero__inner,
  html body.ranked-accounts-page > .container,
  html body.ranked-accounts-page main > .container,
  html body.ranked-accounts-page .container{
    width:calc(100% - 24px) !important;
    max-width:calc(100% - 24px) !important;
  }
  html body.ranked-accounts-page .lb-shop-hero__inner{
    min-height:0 !important;
    padding:22px 0 18px !important;
    grid-template-columns:44px minmax(0,1fr) !important;
    align-items:flex-start !important;
    gap:11px !important;
  }
  html body.ranked-accounts-page .lb-shop-hero__icon{
    width:44px !important;
    height:44px !important;
    min-width:44px !important;
    border-radius:13px !important;
  }
  html body.ranked-accounts-page .lb-shop-hero__icon i{
    font-size:20px !important;
  }
  html body.ranked-accounts-page .lb-shop-hero__title{
    font-size:22px !important;
  }
  html body.ranked-accounts-page .lb-shop-hero__desc{
    font-size:13px !important;
    line-height:1.4 !important;
  }
  html body.ranked-accounts-page #shopFilterbar.shop-filterbar{
    margin-top:14px !important;
    padding:10px !important;
    border-radius:16px !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
    display:grid !important;
    grid-template-columns:1fr 1fr !important;
    gap:8px !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search,
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__actions{
    grid-column:1 / -1 !important;
    width:100% !important;
    min-width:0 !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterpill,
  html body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn,
  html body.ranked-accounts-page #shopFilterbar .shop-sort,
  html body.ranked-accounts-page #shopFilterbar .shop-sort__btn{
    width:100% !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__actions{
    display:grid !important;
    grid-template-columns:1fr 1fr !important;
    margin-left:0 !important;
  }
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:1fr !important;
    gap:14px !important;
  }
  html body.ranked-accounts-page #shopPagination.shop-pagination{
    margin-top:30px !important;
  }
  html body.ranked-accounts-page .container{
    padding-bottom:72px !important;
  }
}
</style>

<style id="lb-shop-hero-no-background">
/* Hero has no colour of its own — it shows the page background.
   Overrides every earlier background/gradient/overlay rule for this section. */
html body .lb-shop-hero,
html body.ranked-accounts-page .lb-shop-hero,
html body.items-shop-page .lb-shop-hero,
html body main > .lb-shop-hero:first-child,
html body .page-zoom > main > .lb-shop-hero:first-child{
  background:transparent !important;
  background-color:transparent !important;
  background-image:none !important;
  box-shadow:none !important;
}
html body .lb-shop-hero::before,
html body .lb-shop-hero::after,
html body.ranked-accounts-page .lb-shop-hero::before,
html body.ranked-accounts-page .lb-shop-hero::after{
  content:none !important;
  display:none !important;
  background:none !important;
}
</style>

<style id="lb-generic-card-highlights-full-label">
/* Keep complete highlight labels instead of shortening them in equal grid cells. */
html body.ranked-accounts-page #accountsGrid .account-card .highlights{
  display:flex!important;
  grid-template-columns:none!important;
  flex-wrap:wrap!important;
  align-items:flex-start!important;
}
html body.ranked-accounts-page #accountsGrid .account-card .highlights .badge{
  width:auto!important;
  max-width:100%!important;
  min-width:max-content!important;
  flex:0 0 auto!important;
  overflow:visible!important;
  text-overflow:clip!important;
  white-space:nowrap!important;
}
</style>
