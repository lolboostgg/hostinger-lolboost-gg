<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'ranked-accounts-page' ]) ?>
<?= $this->start('head') ?>
<script id="lb-stop-offset-recursion-early">
(function(){
  if (window.__lbOffsetRecursionGuardInstalled) return;
  window.__lbOffsetRecursionGuardInstalled = true;

  var names = ['applyOffset', 'syncOffset', 'applySaleBannerOffset'];

  function guarded(name, fn){
    if (typeof fn !== 'function' || fn.__lbGuarded) return fn;
    var running = false;
    var wrapped = function(){
      if (running) return;
      running = true;
      try { return fn.apply(this, arguments); }
      finally { running = false; }
    };
    wrapped.__lbGuarded = true;
    try { Object.defineProperty(wrapped, 'name', { value: name }); } catch(e) {}
    return wrapped;
  }

  names.forEach(function(name){
    var current;
    try {
      Object.defineProperty(window, name, {
        configurable: true,
        get: function(){ return current; },
        set: function(fn){ current = guarded(name, fn); }
      });
    } catch(e) {}
  });

  window.addEventListener('error', function(e){
    var msg = String(e && (e.message || e.error && e.error.message) || '');
    var stack = String(e && e.error && e.error.stack || '');
    if (msg.indexOf('Maximum call stack size exceeded') !== -1 &&
        (stack.indexOf('applyOffset') !== -1 || stack.indexOf('syncOffset') !== -1 || stack.indexOf('applySaleBannerOffset') !== -1)) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return true;
    }
  }, true);
})();
</script>
<?= $this->stop() ?>

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

$csValGameIcon = '';
if (function_exists('util_get_game_by_slug')) {
    $csValGameIcon = (string)(util_get_game_by_slug('valorant')['icon'] ?? '');
}
?>
<section class="lb-shop-hero">
    <div class="lb-shop-hero__inner">
        <div class="lb-shop-hero__icon" aria-hidden="true"><i class="fa-solid fa-helmet-battle"></i></div>
        <div>
            <div class="lb-shop-hero__kicker">Accounts</div>
            <h1 class="lb-shop-hero__title"><?= t('Valorant Ranked Accounts') ?></h1>
            <p class="lb-shop-hero__desc"><?= t('Buy ranked Valorant accounts. Verified sellers, instant delivery, and secure login details. Start climbing immediately.') ?></p>
        </div>
    </div>
</section>
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
</style>
<div class="container">
    <div class="account-type-cards" role="navigation" aria-label="Account type">
        <a href="/val/premium-accounts" class="type-card">
            <div class="type-card__top">
                <div class="type-card__icon" aria-hidden="true"><img src="/public/uploads/icons/default2.png" alt="" style="width:32px;height:32px;border-radius:999px;display:block;"></div>
                <div class="type-card__titles">
                    <div class="type-card__title">Smurf Accounts</div>
                    <div class="type-card__subtitle">Fresh starts. Fast delivery. Ready to play.</div>
                </div>
                <span class="type-card__badge type-card__badge--fast">Fast delivery</span>
            </div>
            
            <div class="type-card__pills">
                <span class="type-pill">Unranked / low MMR options</span>
                <span class="type-pill">Ready-to-play accounts</span>
                <span class="type-pill">Instant delivery available</span>
            </div>
            <div class="type-card__cta">Browse Smurfs <i class="fa-solid fa-arrow-right"></i></div>
        </a>

        <a href="/val/accounts" class="type-card is-active" aria-current="page">
            <div class="type-card__top">
                <div class="type-card__icon" aria-hidden="true"><img src="<?= ASSET_URL ?>/core/main/img/val/ranks/mini/9.png" alt="" style="width:32px;height:32px;border-radius:999px;display:block;"></div>
                <div class="type-card__titles">
                    <div class="type-card__title">Ranked Accounts</div>
                    <div class="type-card__subtitle">Skip the grind. Choose your rank &amp; start playing today.</div>
                </div>
                <span class="type-card__badge type-card__badge--popular">Most popular</span>
            </div>
            
            <div class="type-card__pills">
                <span class="type-pill">Verified rank &amp; region</span>
                <span class="type-pill">Agents &amp; skins included</span>
                <span class="type-pill">Secure purchase &amp; support</span>
            </div>
            <div class="type-card__cta">Browse Ranked <i class="fa-solid fa-arrow-right"></i></div>
        </a>
    </div>

    <!-- Scroll target for pagination / filter changes (NOT sticky) -->
    <div id="accountsTop" style="height:1px;"></div>

    <div class="shop-filterbar shop-filterbar--sticky" id="shopFilterbar">
        <form id="shopFilters" class="shop-filterbar__form">
            <input type="hidden" name="action" value="account_shop_filters">
            <input type="hidden" name="game" value="val">

            <div class="shop-filterbar__row">
                <div class="shop-filterbar__search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Search..." id="filterSearch">
                </div>

                <!-- PILL: Server -->
                <div class="shop-filterpill" data-dropdown="ddServer">
                    <button type="button" class="shop-filterpill__btn" id="btnServer">
                        <i class="fa-solid fa-globe"></i>
                        <span class="shop-filterpill__label">Server</span>
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
                                <input type="text" class="facet-search__input" placeholder="Search Server..." data-search-for="ddServer">
                            </div>

                            <select class="js-facet-source" id="filterServer" multiple data-facet-name="servers[]">
                                <option value="eu"><?= t('Europe') ?></option>
                                <option value="na"><?= t('North America') ?></option>
                                <option value="sea"><?= t('Southeast Asia') ?></option>
                                <option value="me"><?= t('Middle East') ?></option>
                                <option value="vn"><?= t('Vietnam') ?></option>
                                <option value="ph"><?= t('Philippines') ?></option>
                                <option value="sg"><?= t('Singapore') ?></option>
                                <option value="th"><?= t('Thailand') ?></option>
                                <option value="tw"><?= t('Taiwan') ?></option>
                            </select>
<div class="facet-list" data-facet="filterServer"></div>
                        </div>
                    </div>
                </div>

                <!-- PILL: Rank -->
                <div class="shop-filterpill" data-dropdown="ddRank">
                    <button type="button" class="shop-filterpill__btn" id="btnRank">
                        <i class="fa-solid fa-medal"></i>
                        <span class="shop-filterpill__label">Rank</span>
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
                                <input type="text" class="facet-search__input" placeholder="Search Rank..." data-search-for="ddRank">
                            </div>

                            <!-- IMPORTANT: keep <option> text-only (no <img> inside options) so dropdown JS never breaks -->
                            <select class="js-facet-source" id="filterRank" multiple data-facet-name="ranks[]">
                                <?php
                                $rankLabels = [
                                    0 => 'Unranked',
                                    1 => 'Iron',
                                    2 => 'Bronze',
                                    3 => 'Silver',
                                    4 => 'Gold',
                                    5 => 'Platinum',
                                    6 => 'Diamond',
                                    7 => 'Ascendant',
                                    8 => 'Immortal',
                                    9 => 'Radiant',
                                ];
                                foreach ($rankLabels as $val => $label) {
                                    echo '<option value="' . (int)$val . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
                                }
                                ?>
                            </select>
<div class="facet-list" data-facet="filterRank"></div>
                        </div>
                    </div>
                </div>



<!-- PILL: Price (client-side only for now) -->
                <div class="shop-filterpill" data-dropdown="ddPrice">
                    <button type="button" class="shop-filterpill__btn" id="btnPrice">
                        <i class="fa-solid fa-dollar-sign"></i>
                        <span class="shop-filterpill__label">Price</span>
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

                <!-- PILL: More Filters -->
                <div class="shop-filterpill" data-dropdown="ddMore">
                    <button type="button" class="shop-filterpill__btn" id="btnMore">
                        <i class="fa-solid fa-sliders"></i>
                        <span class="shop-filterpill__label">More Filters</span>
                        <span class="shop-filterpill__value" id="valMore"></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="shop-dropdown" id="ddMore">
                        <div class="shop-dropdown__head">
                            <span><?= t('More Filters') ?></span>
                            <button type="button" class="shop-dropdown__close" data-close="ddMore">✕</button>
                        </div>
                        <div class="shop-dropdown__body">

                            <!-- VIEW: MENU -->
                            <div class="mf-view is-active" data-view="menu">
                                <div class="mf-menu">
<button type="button" class="mf-menuitem" data-mf-open="delivery">
                                        <span class="mf-menuitem__left"><i class="fa-solid fa-bolt"></i></span>
                                        <span class="mf-menuitem__label"><?= t('Delivery Type') ?></span>
                                        <span class="mf-menuitem__right">›</span>
                                    </button>

                                    <button type="button" class="mf-menuitem" data-mf-open="agents">
                                        <span class="mf-menuitem__left"><i class="fa-solid fa-user-ninja"></i></span>
                                        <span class="mf-menuitem__label"><?= t('Agents') ?></span>
                                        <span class="mf-menuitem__right">›</span>
                                    </button>

                                    <button type="button" class="mf-menuitem" data-mf-open="skins">
                                        <span class="mf-menuitem__left"><i class="fa-solid fa-masks-theater"></i></span>
                                        <span class="mf-menuitem__label"><?= t('Weapon Skins') ?></span>
                                        <span class="mf-menuitem__right">›</span>
                                    </button>
                                </div>
                            </div>
<!-- VIEW: DELIVERY -->
                            <div class="mf-view" data-view="delivery">
                                <div class="mf-panelhead">
                                    <button type="button" class="mf-back" data-mf-back aria-label="Back">←</button>
                                    <div class="mf-title"><?= t('Delivery Type') ?></div>
                                </div>
                                <div class="mf-panelbody">
                                    <div class="facet-search">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input type="text" class="facet-search__input" placeholder="<?= t('Search Delivery') ?>..." data-search-for="filterDeliveryType">
                                    </div>

                                    <select class="js-facet-source" id="filterDeliveryType" multiple data-facet-name="delivery_type[]">
                                        <option value="instant"><?= t('Instant Delivery') ?></option>
                                        <option value="manual"><?= t('Manual Delivery') ?></option>
                                    </select>
                                    <div class="facet-list" data-facet="filterDeliveryType"></div>
                                </div>
                            </div>

                            <!-- VIEW: AGENTS (UI only; hook up later) -->
                            <div class="mf-view" data-view="agents">
                                <div class="mf-panelhead">
                                    <button type="button" class="mf-back" data-mf-back aria-label="Back">←</button>
                                    <div class="mf-title"><?= t('Agents') ?></div>
                                </div>
                                <div class="mf-panelbody">
                                    <div class="mf-empty"><?= t('Coming soon') ?></div>
                                </div>
                            </div>

                            <!-- VIEW: SKINS (UI only; hook up later) -->
                            <div class="mf-view" data-view="skins">
                                <div class="mf-panelhead">
                                    <button type="button" class="mf-back" data-mf-back aria-label="Back">←</button>
                                    <div class="mf-title"><?= t('Weapon Skins') ?></div>
                                </div>
                                <div class="mf-panelbody">
                                    <div class="mf-empty"><?= t('Coming soon') ?></div>
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

    <?= $this->insert('website/components/accounts/shop-filter-nav') ?>

<?php
    $lbPopularBaseWhere = "sold = 0 AND active = 1 AND game = 'val'";
    $lbPopularPills = [
        ['label' => 'EU', 'attrs' => ['servers' => 'eu'], 'where' => "UPPER(server) = 'EU'"],
        ['label' => 'NA', 'attrs' => ['servers' => 'na'], 'where' => "UPPER(server) = 'NA'"],
        ['label' => 'SEA', 'attrs' => ['servers' => 'sea'], 'where' => "UPPER(server) = 'SEA'"],
        ['label' => 'Ascendant+', 'attrs' => ['ranks' => 'Ascendant,Immortal,Radiant'], 'where' => "rank IN (7,8,9)"],
        ['label' => 'Immortal+', 'attrs' => ['ranks' => 'Immortal,Radiant'], 'where' => "rank IN (8,9)"],
        ['label' => 'Radiant', 'attrs' => ['ranks' => 'Radiant'], 'where' => "rank = 9"],
        ['label' => 'Gold', 'attrs' => ['ranks' => 'Gold'], 'where' => "rank = 4"],
        ['label' => 'Platinum', 'attrs' => ['ranks' => 'Platinum'], 'where' => "rank = 5"],
        ['label' => 'Diamond', 'attrs' => ['ranks' => 'Diamond'], 'where' => "rank = 6"],
        ['label' => 'Instant Delivery', 'attrs' => ['delivery' => 'instant'], 'where' => "delivery_type = 'instant'"],
    ];

    $lbPopularVisible = [];
    global $db;
    $lbPopularDb = (isset($db) && is_object($db) && method_exists($db, 'run')) ? $db : null;

    foreach ($lbPopularPills as $pill) {
        $showPill = true;
        if ($lbPopularDb !== null) {
            try {
                $countRow = $lbPopularDb->run("SELECT COUNT(*) AS c FROM selling_accounts WHERE " . $lbPopularBaseWhere . " AND " . $pill['where'] . " LIMIT 1");
                $showPill = ((int)($countRow[0]['c'] ?? 0)) > 0;
            } catch (Throwable $e) {
                $showPill = true;
            }
        }
        if ($showPill) {
            $lbPopularVisible[] = $pill;
        }
    }
    ?>
    <?php if (!empty($lbPopularVisible)): ?>
    <div class="lb-popular-searches" id="lbPopularSearches" aria-label="Popular account filters">
        <span class="lb-popular-searches__title">Most popular:</span>
        <?php foreach ($lbPopularVisible as $pill): ?>
            <button type="button" class="lb-popular-pill" data-popular-label="<?= htmlspecialchars($pill['label'], ENT_QUOTES, 'UTF-8') ?>"<?php foreach ($pill['attrs'] as $attr => $value): ?> data-<?= htmlspecialchars($attr, ENT_QUOTES, 'UTF-8') ?>="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?php endforeach; ?>><?= htmlspecialchars($pill['label'], ENT_QUOTES, 'UTF-8') ?></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="shop-toolbar">
        <div class="shop-toolbar__left">
            <span class="shop-count">
                <span id="accountsCountShown">0</span>
                <span class="shop-count__sep">/</span>
                <span id="accountsCountTotal"><?= (int)($pagination['totalItems'] ?? 0) ?></span>
                <?= t('Accounts') ?>
            </span>
        </div>
        <div class="shop-toolbar__right">
            <!-- Custom sort dropdown (dark) -->
            
</div>
    </div>

<?php
// Seller footer: use the seller's total sales across all marketplace categories.
// Load all required values in one query so the shop does not execute one query per card.
if (!empty($data) && (is_array($data) || $data instanceof Traversable)) {
    $lbSellerIds = [];
    foreach ($data as $lbAccountRow) {
        $lbSellerId = is_array($lbAccountRow)
            ? (int)($lbAccountRow['seller_id'] ?? 0)
            : (int)($lbAccountRow->seller_id ?? 0);
        if ($lbSellerId > 0) {
            $lbSellerIds[$lbSellerId] = $lbSellerId;
        }
    }

    $lbSellerTotalSales = [];
    if (!empty($lbSellerIds)) {
        global $db;
        try {
            $lbSellerIdList = implode(',', array_map('intval', array_values($lbSellerIds)));
            $lbStatsRows = $db->run(
                "SELECT seller_id, total_sales FROM seller_stats WHERE seller_id IN ({$lbSellerIdList})"
            ) ?: [];
            foreach ($lbStatsRows as $lbStatsRow) {
                $lbSellerTotalSales[(int)($lbStatsRow['seller_id'] ?? 0)] = max(0, (int)($lbStatsRow['total_sales'] ?? 0));
            }
        } catch (Throwable $e) {
            // Keep the shop usable during rollout before seller_stats is available.
        }
    }

    foreach ($data as &$lbAccountRow) {
        $lbSellerId = is_array($lbAccountRow)
            ? (int)($lbAccountRow['seller_id'] ?? 0)
            : (int)($lbAccountRow->seller_id ?? 0);
        $lbTotalSales = $lbSellerTotalSales[$lbSellerId] ?? null;

        if ($lbTotalSales === null && $lbSellerId > 0 && function_exists('get_seller_total_sales')) {
            try {
                $lbTotalSales = max(0, (int)get_seller_total_sales($lbSellerId));
            } catch (Throwable $e) {
                $lbTotalSales = null;
            }
        }

        if ($lbTotalSales !== null) {
            if (is_array($lbAccountRow)) {
                $lbAccountRow['seller_total_sales'] = $lbTotalSales;
                $lbAccountRow['total_sales'] = $lbTotalSales;
                $lbAccountRow['total_sold'] = $lbTotalSales;
                $lbAccountRow['seller_sold'] = $lbTotalSales;
                $lbAccountRow['total_sales'] = $lbTotalSales;
                $lbAccountRow['seller_sales'] = $lbTotalSales;
            } else {
                $lbAccountRow->seller_total_sales = $lbTotalSales;
                $lbAccountRow->total_sales = $lbTotalSales;
                $lbAccountRow->total_sold = $lbTotalSales;
                $lbAccountRow->seller_sold = $lbTotalSales;
                $lbAccountRow->total_sales = $lbTotalSales;
                $lbAccountRow->seller_sales = $lbTotalSales;
            }
        }
    }
    unset($lbAccountRow);
}

// Shop cards sollen nur den Titel zeigen.
// Die Card-Komponente nutzt (je nach Template-Version) ggf. `description`/`desc` als Headline.
// Deshalb mappen wir hier den Titel darauf und leeren alle "Details"-Texte.
if (!empty($data) && (is_array($data) || $data instanceof Traversable)) {
    foreach ($data as &$acc) {
        if (is_array($acc)) {
            if (!empty($acc['title'])) {
                $acc['description'] = $acc['title'];
                $acc['desc']        = $acc['title'];
            }
            foreach (['subtitle','summary','details','account_details','accountDetails','short_description','notes','extra'] as $k) {
                if (isset($acc[$k])) $acc[$k] = '';
            }
        } elseif (is_object($acc)) {
            if (!empty($acc->title)) {
                $acc->description = $acc->title;
                $acc->desc        = $acc->title;
            }
            foreach (['subtitle','summary','details','account_details','accountDetails','short_description','notes','extra'] as $k) {
                if (isset($acc->$k)) $acc->$k = '';
            }
        }
    }
    unset($acc);
}
?>

<div class="accounts-grid" id="accountsGrid">
    <?= $this->insert('website/components/accounts/account-cards', ['accounts' => $data]) ?>
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
<?php if (!empty($lbPopularVisible)): ?>
<div class="lb-popular-searches lb-popular-searches--bottom" id="lbPopularSearchesBottom" aria-label="Popular account filters">
    <span class="lb-popular-searches__title">Most popular:</span>
    <?php foreach ($lbPopularVisible as $pill): ?>
        <button type="button" class="lb-popular-pill" data-popular-label="<?= htmlspecialchars($pill['label'], ENT_QUOTES, 'UTF-8') ?>"<?php foreach ($pill['attrs'] as $attr => $value): ?> data-<?= htmlspecialchars($attr, ENT_QUOTES, 'UTF-8') ?>="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?php endforeach; ?>><?= htmlspecialchars($pill['label'], ENT_QUOTES, 'UTF-8') ?></button>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).ready(function () {
  // One-time tap blocker (capture phase) for mobile click-through edge cases
  if (!window.__tapBlockerInstalled) {
    window.__tapBlockerInstalled = true;
    document.addEventListener('click', function(ev){
      if (window.__blockNextTap) {
        ev.preventDefault();
        ev.stopPropagation();
        if (ev.stopImmediatePropagation) ev.stopImmediatePropagation();
      }
    }, true);
  }


  // ---------- Dropdown open/close ----------
function closeAllDropdowns() {
  // restore portalized dropdowns
  $('.shop-dropdown.is-portal').each(function(){
    const $el = $(this);
    const $parent = $el.data('portal-parent');
    const $next = $el.data('portal-next');
    if ($parent && $parent.length) {
      if ($next && $next.length) $el.insertBefore($next);
      else $parent.append($el);
    }
    $el.removeClass('is-portal').data('portalized', false);
  });

  $('.shop-dropdown').removeClass('is-open');
  $('.shop-filterpill__btn, .shop-sort__btn').attr('aria-expanded', "false");
  if (typeof mfShow === 'function') mfShow('menu');
  document.body.classList.remove('filter-dd-open');
  document.body.classList.remove('sort-dd-open');
}

function mfShow(view){
  const $dd = $('#ddMore');
  if (!$dd.length) return;
  $dd.find('.mf-view').removeClass('is-active');
  $dd.find('.mf-view[data-view="'+view+'"]').addClass('is-active');
  $dd.find('.facet-search__input').val('').trigger('input');
}

$(document).on('pointerdown', '#ddMore [data-mf-open]', function(e){
  e.preventDefault();
  e.stopPropagation();
  mfShow($(this).data('mf-open'));
});

$(document).on('pointerdown', '#ddMore [data-mf-back]', function(e){
  e.preventDefault();
  e.stopPropagation();
  mfShow('menu');
});

// Use pointer events to avoid click/zoom/select2 interference
$(document).on('pointerdown', '.shop-filterpill__btn, .shop-sort__btn', function (e) {
  e.preventDefault();
  e.stopPropagation();
  if (e.stopImmediatePropagation) e.stopImmediatePropagation();

  const id = $(this).closest('[data-dropdown]').data('dropdown');
  if (!id) return;

  const $dd = $('#' + id);
  const wasOpen = $dd.hasClass('is-open');

  closeAllDropdowns();

  if (!wasOpen) {
    $dd.addClass('is-open');
      document.body.classList.add('filter-dd-open');
      document.body.classList.toggle('sort-dd-open', id === 'ddSort');

      // Mobile: portal dropdown to <body> to escape any transformed/stacking ancestors (prevents click-through)
      try{
        if (window.matchMedia && window.matchMedia('(max-width: 768px)').matches) {
          if (!$dd.data('portalized')) {
            $dd.data('portalized', true);
            $dd.data('portal-parent', $dd.parent());
            $dd.data('portal-next', $dd.next().length ? $dd.next() : null);
            $dd.addClass('is-portal');
            $('body').append($dd);
          }
        }
      }catch(err){}
    if (id === 'ddMore') mfShow('menu');
    $('[data-dropdown="' + id + '"] .shop-filterpill__btn, [data-dropdown="' + id + '"] .shop-sort__btn')
      .attr('aria-expanded', 'true');
  }
});

$(document).on('click', '[data-close], #closeMore', function (e) {
  e.preventDefault();
  e.stopPropagation();
  if (e.stopImmediatePropagation) e.stopImmediatePropagation();

  // Block the very next click/tap (prevents "click-through" into the mobile sidebar menu)
  window.__blockNextTap = true;
  setTimeout(function(){ window.__blockNextTap = false; }, 300);

  // Close on next tick so the same gesture can't hit elements underneath
  setTimeout(function(){ closeAllDropdowns(); }, 0);
  return false;
});

// Click outside closes
$(document).on('pointerdown', function (e) {
  if ($(e.target).closest('.shop-dropdown, [data-dropdown]').length) return;
  closeAllDropdowns();
});

// Keep clicks inside dropdown
$(document).on('pointerdown', '.shop-dropdown', function (e) {
  e.stopPropagation();
});

$(document).on('keydown', function (e) {
  if (e.key === 'Escape') closeAllDropdowns();
});

// ---------- Grid helpers ----------

  const $grid  = $('#accountsGrid');
  const $countShown = $('#accountsCountShown');
  const $countTotal = $('#accountsCountTotal');
  const $active = $('#activeFilters');
  let sortMode = 'recommended';
  let priceTouched = false;
  let priceLimitMin = 0;
  let priceLimitMax = 7000;

  // pagination state
  let currentPage = <?= (int)($pagination['page'] ?? 1) ?>;
  let totalPages  = <?= (int)($pagination['totalPages'] ?? 1) ?>;
  let totalItems  = <?= (int)($pagination['totalItems'] ?? 0) ?>;

  function updateCount() {
    const shown = $grid.find('.account-card').length;
    $countShown.text(shown);
    $countTotal.text(totalItems);
  }

  // sticky offset (fix: bar was hidden behind header)
  function setStickyTop() {
    // The global header is inserted BEFORE <main> (inside .page-zoom). This avoids grabbing the hero <header>.
    const $nav = $('.page-zoom > header').first();
    const h = ($nav && $nav.length) ? ($nav.outerHeight() || 88) : 88;
    document.documentElement.style.setProperty('--lb-sticky-top', (h + 10) + 'px');
  }
  setStickyTop();
  $(window).on('resize', function(){ setStickyTop(); });

  // Shop focus mode: hide the desktop game subnav as soon as the user scrolls.
  // Mobile keeps the existing compact filter experience.
  function updateDesktopGameSubnav() {
    const isDesktop = window.matchMedia && window.matchMedia('(min-width: 1025px)').matches;
    const shouldHide = isDesktop && window.scrollY > 8;
    document.body.classList.toggle('lb-desktop-subnav-hidden', shouldHide);
  }
  updateDesktopGameSubnav();
  window.addEventListener('scroll', updateDesktopGameSubnav, { passive: true });
  window.addEventListener('resize', updateDesktopGameSubnav);

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (m) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);
    });
  }


  // Performance: lazy-load account images and decode them asynchronously, especially helpful on mobile.
  function optimizeAccountImages(scope) {
    const $scope = scope ? $(scope) : $grid;
    $scope.find('img').each(function(index){
      if (!this.hasAttribute('decoding')) this.setAttribute('decoding', 'async');
      if (!this.hasAttribute('loading')) this.setAttribute('loading', index < 4 ? 'eager' : 'lazy');
      if (!this.hasAttribute('fetchpriority') && index < 2) this.setAttribute('fetchpriority', 'high');
    });
  }

  // ---------- Facet lists (checkbox list like your screenshot) ----------
  const ICONS = {
    // servers
    'EU': 'fa-solid fa-globe',
    'NA': 'fa-solid fa-flag-usa',
    'SEA': 'fa-solid fa-globe',
    'ME': 'fa-solid fa-globe',
    'VN': 'fa-solid fa-globe',
    'PH': 'fa-solid fa-globe',
    'SG': 'fa-solid fa-globe',
    'TH': 'fa-solid fa-globe',
    'TW': 'fa-solid fa-globe',
    // delivery
    'instant': 'fa-solid fa-bolt',
    'manual': 'fa-solid fa-hand'
  };

  function iconFor(facetName, value, text) {
    const key = String(value || '').toUpperCase();
    // ranks are rendered as REAL images in buildFacetFromSelect()
    return ICONS[value] || ICONS[key] || 'fa-solid fa-circle-dot';
  }

  function buildFacetFromSelect($select) {
    const facetName = $select.data('facet-name');
    const selectId = $select.attr('id');
    const $list = $('.facet-list[data-facet="' + selectId + '"]');
    if (!$list.length) return;

    // prevent duplicate
    if ($list.data('built')) return;
    $list.data('built', true);

    const items = [];
    $select.find('option').each(function(){
      const $opt = $(this);
      const val = $opt.attr('value');
      const txt = ($opt.text() || '').trim();
      if (!val || !txt) return;

      const ico = iconFor(facetName, val, txt);
      const id = selectId + '_' + val.replace(/[^a-zA-Z0-9_-]/g,'_');

// Rank: use the real LoL tier icon images instead of FontAwesome
const __ASSET_BASE__ = <?= json_encode(rtrim(ASSET_URL, '/')) ?>;

// Server flags mapping (expects files in /core/main/img/flags/)
const SERVER_FLAGS = {
  'EU': 'eu',
  'EUROPE': 'eu',
  'NA': 'us',
  'NORTH AMERICA': 'us',
  'SEA': 'sea',
  'SOUTHEAST ASIA': 'sea',
  'ME': 'me',
  'MIDDLE EAST': 'me',
  'VN': 'vn',
  'VIETNAM': 'vn',
  'PH': 'ph',
  'PHILIPPINES': 'ph',
  'SG': 'sg',
  'SINGAPORE': 'sg',
  'TH': 'th',
  'THAILAND': 'th',
  'TW': 'tw',
  'TAIWAN': 'tw'
};

const isRank = (facetName === 'ranks[]' && /^\d+$/.test(String(val)));
const isServer = (facetName === 'servers[]');
const isRole = false;

const serverVal = String(val || '').trim();
const serverTxt = String(txt || '').trim();

const serverKey = serverVal ? serverVal.toUpperCase() : serverTxt.toUpperCase();

// Badge label: keep it short & consistent
const SERVER_BADGES = {
  'EUW':'EUW',
  'EU-WEST':'EUW',
  'EU WEST':'EUW',
  'EUNE':'EUNE',
  'EU-NORTH EAST':'EUNE',
  'EU NORTHEAST':'EUNE',
  'NA':'NA',
  'NORTH AMERICA':'NA',
  'US':'US',
  'UNITED STATES':'US',
  'BR':'BR',
  'BRAZIL':'BR',
  'LAN':'LAN',
  'LATIN AMERICA NORTH':'LAN',
  'LAS':'LAS',
  'LATIN AMERICA SOUTH':'LAS',
  'OCE':'OCE',
  'OCEANIA':'OCE',
  'RU':'RU',
  'RUSSIA':'RU',
  'TR':'TR',
  'TURKEY':'TR',
  'JP':'JP',
  'JAPAN':'JP',
  'KR':'KR',
  'KOREA':'KR',
  'EU':'EU',
  'EUROPE':'EU',
  'SEA':'SEA',
  'SOUTHEAST ASIA':'SEA',
  'ME':'ME',
  'MIDDLE EAST':'ME',
  'VN':'VN',
  'VIETNAM':'VN',
  'PH':'PH',
  'PHILIPPINES':'PH',
  'SG':'SG',
  'SINGAPORE':'SG',
  'TH':'TH',
  'THAILAND':'TH',
  'TW':'TW',
  'TAIWAN':'TW'
};

const serverBadge = isServer ? (SERVER_BADGES[serverKey] || (serverKey.length <= 4 ? serverKey : serverKey.split(' ')[0].slice(0,4))) : null;

const iconHtml = isRank
  ? `<img class="facet-item__rank" src="${__ASSET_BASE__}/core/main/img/val/ranks/mini/${escapeHtml(val)}.png" alt="">`
  : (isServer && serverBadge)
      ? `<span class="facet-item__badge" aria-hidden="true">${escapeHtml(serverBadge)}</span>`
      : `<i class="${ico} facet-item__icon"></i>`;



      items.push(`
        <label class="facet-item" for="${id}" data-value="${escapeHtml(val)}">
          <span class="facet-item__left">
            ${iconHtml}
            <span class="facet-item__text">${escapeHtml(txt)}</span>
          </span>
          <input class="facet-item__check" type="checkbox" id="${id}" name="${facetName}" value="${escapeHtml(val)}">
          <span class="facet-item__box"></span>
        </label>
      `);
    });

    $list.html(`<div class="facet-scroll">${items.join('')}</div>`);
    // hide source select to avoid "double" UI and duplicate serialization
    $select.prop('disabled', true).addClass('is-hidden');
  }

  $('.js-facet-source').each(function(){ buildFacetFromSelect($(this)); });

  // search within facet list
  $(document).on('input', '.facet-search__input', function(){
    const q = ($(this).val() || '').toLowerCase();
    const $dd = $(this).closest('.shop-dropdown');
    const $items = $dd.find('.facet-item');
    $items.each(function(){
      const txt = ($(this).text() || '').toLowerCase();
      $(this).toggle(txt.indexOf(q) !== -1);
    });
  });

  // ---------- Price dual range ----------
  const $rMin = $('#priceRangeMin');
  const $rMax = $('#priceRangeMax');
  const $pMin = $('#priceMin');
  const $pMax = $('#priceMax');
  const $fill = $('#priceRangeFill');

  function clampPrice() {
    let minV = parseInt($rMin.val() || 0, 10);
    let maxV = parseInt($rMax.val() || 0, 10);
    if (minV > maxV - 1) minV = maxV - 1;
    if (maxV < minV + 1) maxV = minV + 1;
    $rMin.val(minV);
    $rMax.val(maxV);

    $pMin.val(minV);
    $pMax.val(maxV);

    $('#priceLabelMin').text('€' + minV.toLocaleString('de-DE'));
    $('#priceLabelMax').text('€' + maxV.toLocaleString('de-DE'));

    const min = parseInt($rMin.attr('min') || 0, 10);
    const max = parseInt($rMin.attr('max') || priceLimitMax, 10);
    const left = ((minV - min) / (max - min)) * 100;
    const right = ((maxV - min) / (max - min)) * 100;
    $fill.css({ left: left + '%', width: (right - left) + '%' });
  }

  // init
    // Bring active thumb above the other (easier to grab when close)
  function setActiveThumb(which) {
    // lower index stays below
    const minEl = document.getElementById('priceRangeMin');
    const maxEl = document.getElementById('priceRangeMax');
    if (!minEl || !maxEl) return;
    if (which === 'min') {
      minEl.style.zIndex = '6';
      maxEl.style.zIndex = '5';
    } else {
      maxEl.style.zIndex = '6';
      minEl.style.zIndex = '5';
    }
  }

  $(document).on('pointerdown', '#priceRangeMin', function(){ setActiveThumb('min'); });
  $(document).on('pointerdown', '#priceRangeMax', function(){ setActiveThumb('max'); });

  // Click on the track to move the nearest thumb
  $(document).on('pointerdown', '.shop-range__track', function(e){
    const minEl = document.getElementById('priceRangeMin');
    const maxEl = document.getElementById('priceRangeMax');
    if (!minEl || !maxEl) return;

    const rect = this.getBoundingClientRect();
    const ratio = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
    const min = parseInt(minEl.min || '0', 10);
    const max = parseInt(minEl.max || '0', 10);
    const val = Math.round(min + ratio * (max - min));

    const curMin = parseInt(minEl.value || '0', 10);
    const curMax = parseInt(maxEl.value || '0', 10);

    if (Math.abs(val - curMin) <= Math.abs(val - curMax)) {
      minEl.value = val;
      setActiveThumb('min');
    } else {
      maxEl.value = val;
      setActiveThumb('max');
    }
    clampPrice();
    priceTouched = true;
    // apply only on release-like interaction
    triggerFiltersDebounced();
  });

  if ($rMin.length && $rMax.length) clampPrice();

  function setRangeFromInputs() {
    let minV = parseInt($pMin.val() || 0, 10);
    let maxV = parseInt($pMax.val() || priceLimitMax, 10);
    const min = parseInt($rMin.attr('min') || 0, 10);
    const max = parseInt($rMin.attr('max') || priceLimitMax, 10);
    minV = Math.max(min, Math.min(minV, max));
    maxV = Math.max(min, Math.min(maxV, max));
    $rMin.val(minV);
    $rMax.val(maxV);
    clampPrice();
  }

  
  // ---------- URL sync ----------
  function getFilterState(pageOverride) {
    const state = {
      page: pageOverride || currentPage || 1,
      search: ($('#filterSearch').val() || '').trim(),
      servers: [],
      ranks: [],
      delivery: [],
      price_min: parseFloat($pMin.val() || 0),
      price_max: parseFloat($pMax.val() || priceLimitMax),
      sort: sortMode || 'recommended'
    };
    $('[name="servers[]"]:checked').each(function(){ state.servers.push($(this).val()); });
    $('[name="ranks[]"]:checked').each(function(){ state.ranks.push($(this).closest('.facet-item').find('.facet-item__text').text().trim() || $(this).val()); });
    $('input[name="delivery_type[]"]:checked').each(function(){ state.delivery.push($(this).val()); });
    return state;
  }

  function sortToParam(sort) {
    if (!sort || sort === 'recommended') return '';
    if (sort === 'price_asc') return 'price';
    if (sort === 'price_desc') return '-price';
    if (sort === 'rank_desc') return '-rank';
    if (sort === 'rank_asc') return 'rank';
    if (sort === 'newest') return 'newest';
    if (sort === 'oldest') return 'oldest';
    if (sort === 'delivery_fast') return 'delivery';
    return sort;
  }
  function paramToSort(p) {
    if (!p) return 'recommended';
    if (p === 'price') return 'price_asc';
    if (p === '-price') return 'price_desc';
    if (p === 'rank') return 'rank_asc';
    if (p === '-rank') return 'rank_desc';
    if (p === 'newest') return 'newest';
    if (p === 'oldest') return 'oldest';
    if (p === 'delivery') return 'delivery_fast';
    return 'recommended';
  }

  function updateUrl(state) {
    const params = new URLSearchParams();

    if (state.search) params.set('search', state.search);

    state.servers.forEach(v => params.append('server', v));
    state.ranks.forEach(v => params.append('rank', v));
    state.delivery.forEach(v => params.append('delivery', v));

    // only include price if user touched or it's not full range
    if (priceTouched || state.price_min !== priceLimitMin || state.price_max !== priceLimitMax) {
      params.set('min', String(state.price_min));
      params.set('max', String(state.price_max));
    }

    const sp = sortToParam(state.sort);
    if (sp) params.set('sort', sp);

    if (state.page && state.page > 1) params.set('page', String(state.page));

    const newUrl = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '');
    window.history.replaceState({}, '', newUrl);
  }

  function matchFacetByTextOrValue(inputName, values) {
    values.forEach(function(v){
      const needle = String(v).toLowerCase();
      // try by value
      let $cb = $('input[name="' + inputName + '"][value="' + v + '"]');
      if ($cb.length) { $cb.prop('checked', true); return; }

      // try by label text
      $('input[name="' + inputName + '"]').each(function(){
        const label = ($(this).closest('.facet-item').find('.facet-item__text').text() || '').trim().toLowerCase();
        if (label === needle) $(this).prop('checked', true);
      });
    });
  }

  function applyStateFromUrl() {
    const params = new URLSearchParams(window.location.search);

    const search = params.get('search') || '';
    if (search) $('#filterSearch').val(search);

    const servers = params.getAll('server');
    const ranks   = params.getAll('rank');
    const deliv   = params.getAll('delivery');

    if (servers.length) matchFacetByTextOrValue('servers[]', servers);
    if (ranks.length) matchFacetByTextOrValue('ranks[]', ranks);
    if (deliv.length) matchFacetByTextOrValue('delivery_type[]', deliv);

    const sortParam = params.get('sort') || '';
    sortMode = paramToSort(sortParam);
    // set label
    const labelMap = {
      'default': '<?= t('Recommended') ?>',
      'price_asc': '<?= t('Lowest Price') ?>',
      'price_desc': '<?= t('Highest Price') ?>',
      'delivery_fastest': '<?= t('Fastest Delivery') ?>',
      'newest': '<?= t('Newest') ?>',
      'oldest': '<?= t('Oldest') ?>',
      'rank_desc': '<?= t('Rank') ?>: <?= t('High to Low') ?>',
      'rank_asc': '<?= t('Rank') ?>: <?= t('Low to High') ?>'
    };
    $('#sortLabel').text(labelMap[sortMode] || '<?= t('Recommended') ?>');

    const p = parseInt(params.get('page') || '1', 10) || 1;
    currentPage = Math.max(1, p);

    // price params in EUR
    const minP = params.get('min');
    const maxP = params.get('max');
    if (minP !== null || maxP !== null) {
      priceTouched = true;
      if (minP !== null) $pMin.val(parseFloat(minP));
      if (maxP !== null) $pMax.val(parseFloat(maxP));
      setRangeFromInputs();
    }
  }

  // ---------- Price limits (dynamic) ----------
  function setPriceLimits(minEur, maxEur) {
    // convert EUR float to ints for the slider inputs (we use whole EUR steps)
    const minInt = Math.max(0, Math.floor(minEur || 0));
    const maxInt = Math.max(minInt + 1, Math.ceil(maxEur || 0));

    priceLimitMin = minInt;
    priceLimitMax = maxInt;

    // update attributes
    $pMin.attr('min', minInt);
    $pMax.attr('min', minInt);
    $pMin.attr('max', maxInt);
    $pMax.attr('max', maxInt);

    $rMin.attr('min', minInt).attr('max', maxInt);
    $rMax.attr('min', minInt).attr('max', maxInt);

    // if user never touched price, snap to full range
    if (!priceTouched) {
      $pMin.val(minInt); $pMax.val(maxInt);
      $rMin.val(minInt); $rMax.val(maxInt);
    } else {
      // clamp current values into new limits
      let curMin = parseFloat($pMin.val() || minInt);
      let curMax = parseFloat($pMax.val() || maxInt);
      curMin = Math.max(minInt, Math.min(curMin, maxInt));
      curMax = Math.max(minInt, Math.min(curMax, maxInt));
      if (curMin > curMax) { const t = curMin; curMin = curMax; curMax = t; }
      $pMin.val(curMin); $pMax.val(curMax);
      $rMin.val(Math.floor(curMin)); $rMax.val(Math.ceil(curMax));
    }

    clampPrice();
    updatePillSummaries();
    renderActiveFilters();
  }

  let filterTimer = null;
  function triggerFiltersDebounced() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(function(){
      resetToFirstPage();
      fetchAccounts({ page:1 });
    updateUrl(getFilterState(1));
      renderActiveFilters();
      updatePillSummaries();
    }, 220);
  }

  // Range slider: smooth dragging (no ajax on every pixel)
// - on `input`: update UI only
// - on `change` (mouseup/touchend): apply filters
$(document).on('input', '#priceRangeMin, #priceRangeMax', function(){
  clampPrice();
  priceTouched = true;
});
$(document).on('change', '#priceRangeMin, #priceRangeMax', function(){
  clampPrice();
  priceTouched = true;
  triggerFiltersDebounced();
});
  $(document).on('change', '#priceMin, #priceMax', function(){
    setRangeFromInputs();
    triggerFiltersDebounced();
  });

  // ---------- Active filter chips ----------
  function renderActiveFilters() {
    const __ASSET_BASE__ = <?= json_encode(rtrim(ASSET_URL, '/')) ?>;
    const chips = [];

    const searchVal = $('#filterSearch').val();
    if (searchVal && searchVal.trim().length) {
      chips.push({ type:'search', label:'Search: ' + searchVal.trim() });
    }

    // price
    const minV = $pMin.val();
    const maxV = $pMax.val();
    if (minV && String(minV) !== '0') chips.push({ type:'price', key:'min', label:'From: €' + minV });
    if (maxV && String(maxV) !== String(priceLimitMax)) chips.push({ type:'price', key:'max', label:'To: €' + maxV });

    // checkbox facets (group servers/ranks/roles into one chip each)
    const ICONS = {
      servers: `<i class="fa-solid fa-globe shop-filterpill__iconfa" aria-hidden="true"></i>`,
      ranks:   `<i class="fa-solid fa-shield shop-filterpill__iconfa" aria-hidden="true"></i>`
    };

    const grouped = { servers: [], ranks: [] };

    $('input[type="checkbox"][name$="[]"]:checked').each(function(){
      const name = $(this).attr('name');
      const val = $(this).val();
      const txt = $(this).closest('.facet-item').find('.facet-item__text').text().trim() || val;

      if (name === 'servers[]') { grouped.servers.push({ val, txt }); return; }
      if (name === 'ranks[]') { grouped.ranks.push({ val, txt }); return; }

      // everything else stays as-is (single chip)
      const group = name.replace('[]','');
      let icon = '';
      if (name === 'ranks[]' && /^[0-9]+$/.test(String(val))) {
        icon = `<img src="${__ASSET_BASE__}/core/main/img/val/ranks/mini/${escapeHtml(val)}.png" alt="">`;
      }
      chips.push({ type:'facet', name:name, value:val, icon: icon, label: group.charAt(0).toUpperCase() + group.slice(1) + ': ' + txt });
    });

    function pushGroupedChip(key, labelPrefix) {
      const arr = grouped[key];
      if (!arr.length) return;

      const names = arr.map(x => x.txt);
      const shown = names.slice(0, 2);
      const more = names.length - shown.length;
      const label = labelPrefix + ': ' + shown.join(', ') + (more > 0 ? (' +' + more) : '');
      const title = labelPrefix + ': ' + names.join(', ');

      chips.push({ type:'group', group:key, icon: ICONS[key], label: label, title: title });
    }

    pushGroupedChip('servers', 'Servers');
    pushGroupedChip('ranks', 'Ranks');
    if (!chips.length) {
      $active.html('<span class="active-filters__hint"><?= t('No filters applied') ?></span>');
      return;
    }

    const html = chips.map(function(c){
      let data = '';
      if (c.type === 'search') data = 'data-type="search"';
      else if (c.type === 'price') data = 'data-type="price" data-key="' + escapeHtml(c.key) + '"';
      else if (c.type === 'group') data = 'data-type="group" data-group="' + escapeHtml(c.group) + '"';
      else data = 'data-type="facet" data-name="' + escapeHtml(c.name) + '" data-value="' + escapeHtml(c.value) + '"';

      const title = c.title || '<?= t('Remove filter') ?>';

      return '<button type="button" class="filter-chip" '+data+' title="' + escapeHtml(title) + '">' +
        (c.icon ? '<span class="filter-chip__icon">' + c.icon + '</span>' : '') +
        '<span class="filter-chip__label">' + escapeHtml(c.label) + '</span>' +
        '<span class="filter-chip__x">✕</span>' +
      '</button>';
    }).join('');

    $active.html(html);
  }

  $(document).on('click', '.filter-chip', function(){
    const type = $(this).data('type');
    if (type === 'search') {
      $('#filterSearch').val('');
    } else if (type === 'price') {
      const key = $(this).data('key');
      if (key === 'min') { $pMin.val(0); $rMin.val(0); }
      if (key === 'max') { $pMax.val(priceLimitMax); $rMax.val(priceLimitMax); }
      clampPrice();
    } else if (type === 'group') {
      const g = $(this).data('group');
      if (g === 'servers') $('input[type="checkbox"][name="servers[]"]').prop('checked', false);
      if (g === 'ranks') $('input[type="checkbox"][name="ranks[]"]').prop('checked', false);
    } else if (type === 'facet') {
      const name = $(this).data('name');
      const val = $(this).data('value');
      $('input[type="checkbox"][name="' + name + '"][value="' + val + '"]').prop('checked', false);
    }
    triggerFiltersDebounced();
  });

  // ---------- Pill summaries ----------
  function updatePillSummaries() {
    const servers = $('input[name="servers[]"]:checked').length;
    const ranks = $('input[name="ranks[]"]:checked').length;
    const deliv = $('input[name="delivery_type[]"]:checked').length;

    $('#valServer').text(servers ? '(' + servers + ')' : '');
    $('#valRank').text(ranks ? '(' + ranks + ')' : '');
    $('#valMore').text(deliv ? '(' + deliv + ')' : '');

    const minV = parseInt($pMin.val() || 0, 10);
    const maxV = parseInt($pMax.val() || priceLimitMax, 10);
    if (minV !== priceLimitMin || maxV !== priceLimitMax) {
      $('#valPrice').text('€' + minV + ' – €' + maxV);
    } else {
      $('#valPrice').text('');
    }
  }

  
$(document).on('click', '.shop-menuitem', function(e){
  e.preventDefault();
  sortMode = $(this).data('sort') || 'recommended';
  $('#sortLabel').text($(this).text().trim());
  closeAllDropdowns();
  resetToFirstPage();
  fetchAccounts({ page:1 });
});

// ---------- Fetch accounts (AJAX) ----------

  let isLoading = false;
  let requestSeq = 0;
  let priceLimitsInitialized = false;
  let pendingScrollToTop = false;

  function scrollToResultsTop() {
    // IMPORTANT: scroll to a non-sticky anchor, otherwise the computed top won't move far enough.
    const anchor = document.getElementById('accountsTop');
    // Use CSS var set by setStickyTop(); fallback to ~90px.
    const stickyVar = getComputedStyle(document.documentElement)
      .getPropertyValue('--lb-sticky-top')
      .trim();
    const stickyTop = parseInt(stickyVar || '90', 10) || 90;
    const y = anchor
      ? (anchor.getBoundingClientRect().top + window.scrollY - stickyTop - 16)
      : 0;
    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
  }

  function buildPageBar(page, total) {
    const isMobile = (window.matchMedia && window.matchMedia('(max-width: 900px)').matches) || (window.visualViewport && window.visualViewport.width <= 900) || (document.documentElement && document.documentElement.clientWidth <= 900);
    const maxBtns = isMobile ? 3 : 7;
    const btns = [];
    const push = (p, label, cls='') => {
      btns.push(`<button type="button" class="page-btn ${cls}" data-page="${p}">${label}</button>`);
    };

    // Mobile layout: one compact row like Valorant shop cards need: previous, current, ellipsis, last, next.
    if (isMobile) {
      push(Math.max(1, page-1), '<i class="fa-solid fa-chevron-left"></i>', page === 1 ? 'is-disabled is-nav' : 'is-nav');
      push(page, page, 'is-active');
      if (total > page + 1) btns.push('<span class="page-ellipsis">…</span>');
      if (total > 1 && total !== page) push(total, total, '');
      push(Math.min(total, page+1), '<i class="fa-solid fa-chevron-right"></i>', page === total ? 'is-disabled is-nav' : 'is-nav');
      return `<div class="page-bar">${btns.join('')}</div>`;
    }

    // prev
    push(Math.max(1, page-1), '&laquo; Prev', page === 1 ? 'is-disabled is-nav' : 'is-nav');

    if (total <= maxBtns) {
      for (let i=1;i<=total;i++) push(i, i, i===page?'is-active':'');
    } else {
      // always show first, last, and window around current
      const windowSize = 2;
      const start = Math.max(2, page - windowSize);
      const end   = Math.min(total-1, page + windowSize);

      push(1, 1, page===1?'is-active':'');
      if (start > 2) btns.push('<span class="page-ellipsis">…</span>');
      for (let i=start;i<=end;i++) push(i, i, i===page?'is-active':'');
      if (end < total-1) btns.push('<span class="page-ellipsis">…</span>');
      push(total, total, page===total?'is-active':'');
    }

    // next
    push(Math.min(total, page+1), 'Next &raquo;', page === total ? 'is-disabled is-nav' : 'is-nav');
    return `<div class="page-bar">${btns.join('')}</div>`;
  }

  function renderPagination() {
    const $wrap = $('#shopPagination');
    if (!$wrap.length) return;
    if (!totalPages || totalPages <= 1) {
      $wrap.empty();
      return;
    }
    $wrap.html(buildPageBar(currentPage, totalPages));
  }

  
function fetchAccounts({ page=1 } = {}) {
  requestSeq += 1;
  const mySeq = requestSeq;

  isLoading = true;
  $('#shopFilterbar').addClass('is-loading');

  const sort = sortMode || 'recommended';
  // Dropdowns may be moved below <body> while open. Rebuild checkbox facets
  // from the live DOM so search + selected filters always reach the backend.
  const facetNames = ['servers[]', 'ranks[]', 'roles[]', 'delivery_type[]'];
  const requestFields = $('#shopFilters').serializeArray().filter(function(field){
    return facetNames.indexOf(field.name) === -1;
  });
  facetNames.forEach(function(name){
    $('input[name="' + name + '"]:checked').each(function(){
      requestFields.push({name:name, value:String($(this).val() || '')});
    });
  });
  const base = $.param(requestFields);
  const data = base + '&page=' + encodeURIComponent(page) + '&sort=' + encodeURIComponent(sort);

  $.ajax({
    url: (typeof ajax_url !== 'undefined' ? ajax_url : '<?= AJAX_URL ?>'),
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function (payload) {
      if (mySeq !== requestSeq) return;
      if (!payload) return;

      if (payload.html !== undefined) {
        $grid.html(payload.html);
        optimizeAccountImages($grid);
      }

      currentPage = payload.page || page || 1;
      totalPages  = payload.totalPages || 1;
      totalItems  = payload.totalItems || 0;


// Empty state (no results)
if (totalItems === 0) {
  $('#shopEmpty').show();
  $grid.hide();
  $('#shopPagination').hide();
} else {
  $('#shopEmpty').hide();
  $grid.show();
  $('#shopPagination').show();
}

      if (payload.priceRange && !priceLimitsInitialized) {
        setPriceLimits(payload.priceRange.min, payload.priceRange.max);
        priceLimitsInitialized = true;
      }

      updateCount();
      renderActiveFilters();
      updatePillSummaries();
      renderPagination();
      updateUrl(getFilterState(currentPage));

      // If user changed page, scroll back to the top of results AFTER render.
      if (pendingScrollToTop) {
        pendingScrollToTop = false;
        scrollToResultsTop();
      }
    },
    complete: function(){
      if (mySeq !== requestSeq) return;
      isLoading = false;
      $('#shopFilterbar').removeClass('is-loading');
    },
    error: function(xhr, status, error){
      if (mySeq !== requestSeq) return;
      console.error('Error fetching accounts:', error);
      isLoading = false;
      $('#shopFilterbar').removeClass('is-loading');
    }
  });
}

function resetToFirstPage() {
    currentPage = 1;
  }

  // checkbox facets trigger (also works when dropdowns are portalized to <body> on mobile)
  $(document).on('change', '#shopFilterbar input[type="checkbox"], .shop-dropdown input[type="checkbox"]', function () {
    sortMode = 'recommended';
    $('#sortLabel').text('<?= t('Recommended') ?>');
    resetToFirstPage();
    fetchAccounts({ page:1 });
    updateUrl(getFilterState(1));
    renderActiveFilters();
    updatePillSummaries();
    updateUrl(getFilterState(1));
  });

  // search input debounce
  let searchTimer = null;
  $('#shopFilters').on('input', 'input[name="search"]', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () {
      resetToFirstPage();
      fetchAccounts({ page: 1 });
      renderActiveFilters();
      updatePillSummaries();
      updateUrl(getFilterState(1));
    }, 250);
  });

  // clear filters
  $('.reset-filters').on('click', function (e) {
    e.preventDefault();
    const $form = $('#shopFilters');
    $form[0].reset();
    $form.find('input[type="checkbox"]').prop('checked', false);

    // reset price
    $pMin.val(0); $pMax.val(priceLimitMax);
    $rMin.val(0); $rMax.val(priceLimitMax);
    clampPrice();

    sortMode = 'recommended';
    $('#sortLabel').text('<?= t('Recommended') ?>');

    resetToFirstPage();
    fetchAccounts({ page:1 });
  });

  // Empty state actions
  $('#btnResetFiltersEmpty').on('click', function(){
  // exactly the same as the top Clear button
  $('.shop-filterbar__actions .reset-filters').first().trigger('click');
});

  $('#btnTalkToAgent').on('click', function(){
  // Open Tawk.to chat
  if (window.Tawk_API) {
    if (typeof window.Tawk_API.maximize === 'function') { window.Tawk_API.maximize(); return; }
    if (typeof window.Tawk_API.toggle === 'function') { window.Tawk_API.toggle(); return; }
    if (typeof window.Tawk_API.popup === 'function') { window.Tawk_API.popup(); return; }
  }

  // Fallback: try clicking launcher
  const $tawk = $('#tawkchat-container, .tawk-min-container, iframe[title*="chat"]').first();
  if ($tawk.length) { $tawk.trigger('click'); }
});

  // pagination click
  $(document).on('click', '#shopPagination .page-btn', function(){
    if ($(this).hasClass('is-disabled') || $(this).hasClass('is-active')) return;
    const p = parseInt($(this).data('page'), 10) || 1;
    closeAllDropdowns();
    pendingScrollToTop = true;
    fetchAccounts({ page:p });
    updateUrl(getFilterState(p));
  });



  // Popular filter pills, uses the existing shop filters and AJAX endpoint.
  $(document).on('click', '.lb-popular-searches .lb-popular-pill', function(e){
    e.preventDefault();
    closeAllDropdowns();

    const $pill = $(this);
    const servers = String($pill.data('servers') || '').split(',').map(v => v.trim()).filter(Boolean);
    const ranks = String($pill.data('ranks') || '').split(',').map(v => v.trim()).filter(Boolean);
    const roles = String($pill.data('roles') || '').split(',').map(v => v.trim()).filter(Boolean);
    const delivery = String($pill.data('delivery') || '').split(',').map(v => v.trim()).filter(Boolean);

    $('#filterSearch').val('');
    $('input[name="servers[]"], input[name="ranks[]"], input[name="roles[]"], input[name="delivery_type[]"]').prop('checked', false);

    servers.forEach(function(v){ $('input[name="servers[]"][value="' + v + '"]').prop('checked', true); });
    ranks.forEach(function(v){ $('input[name="ranks[]"][value="' + v + '"]').prop('checked', true); });
    roles.forEach(function(v){ $('input[name="roles[]"][value="' + v + '"]').prop('checked', true); });
    delivery.forEach(function(v){ $('input[name="delivery_type[]"][value="' + v + '"]').prop('checked', true); });

    $pMin.val(priceLimitMin); $pMax.val(priceLimitMax);
    $rMin.val(priceLimitMin); $rMax.val(priceLimitMax);
    priceTouched = false;
    clampPrice();

    sortMode = 'recommended';
    $('#sortLabel').text('<?= t('Recommended') ?>');
    resetToFirstPage();
    fetchAccounts({ page: 1 });
    renderActiveFilters();
    updatePillSummaries();
    updateUrl(getFilterState(1));
  });

  function refreshPopularPillAvailability() {
    const $wraps = $('.lb-popular-searches');
    if (!$wraps.length) return;

    const $source = $wraps.first();
    const checks = [];
    $source.find('.lb-popular-pill').each(function(index){
      const $pill = $(this);
      checks.push({
        index: index,
        label: String($pill.data('popular-label') || $pill.text() || '').trim(),
        servers: String($pill.data('servers') || ''),
        ranks: String($pill.data('ranks') || ''),
        roles: String($pill.data('roles') || ''),
        delivery: String($pill.data('delivery') || '')
      });
    });
    if (!checks.length) return;

    $.ajax({
      url: (typeof ajax_url !== 'undefined' ? ajax_url : '<?= AJAX_URL ?>'),
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'account_shop_popular_counts',
        game: ($('#shopFilters input[name="game"]').val() || 'lol'),
        checks: checks
      },
      success: function(payload){
        if (!payload || !payload.success || !payload.counts) return;
        const counts = payload.counts || {};
        $wraps.each(function(){
          const $wrap = $(this);
          $wrap.find('.lb-popular-pill').each(function(index){
            const count = parseInt(counts[index] || 0, 10);
            $(this).toggle(count > 0);
          });
          const visible = $wrap.find('.lb-popular-pill:visible').length;
          $wrap.toggle(visible > 0);
        });
      }
    });
  }

  refreshPopularPillAvailability();


  // initial UI
  applyStateFromUrl();
  updateCount();
  renderActiveFilters();
  updatePillSummaries();
  renderPagination();
  optimizeAccountImages($grid);

  // Performance: do not refetch the already server-rendered first page.
  // Only sync via AJAX when the URL contains filters, search, sorting or a later page.
  const __initialParams = new URLSearchParams(window.location.search);
  const __needsInitialFetch = Array.from(__initialParams.keys()).some(function(key){
    return ['search','server','rank','role','delivery','min','max','sort','page'].indexOf(key) !== -1;
  });

  if (__needsInitialFetch) {
    fetchAccounts({ page: currentPage || 1 });
  } else {
    priceLimitsInitialized = true;
  }
});
// Mobile/Overlay: Close button should not "click through" to elements behind
$(document).on('pointerdown', '.shop-dropdown__close', function(e){
  e.preventDefault();
  e.stopPropagation();
  e.stopImmediatePropagation();
  closeAllDropdowns();
  return false;
});

</script>

<script>
// Seller-Namen in Account-Karten zu Links machen
(function linkSellerNames() {
    function process() {
        document.querySelectorAll('.seller-info__name').forEach(function (el) {
            if (el.dataset.sellerLinked) return;
            el.dataset.sellerLinked = '1';

            // Username from dedicated attribute/text span so badges or tooltip text do not affect the URL
            var sellerSlug = (el.getAttribute('data-seller-slug') || '').trim();
            var username = (el.getAttribute('data-seller-name') || '').trim();
            if (!username) {
                var nameTextEl = el.querySelector('.seller-info__name-text');
                username = nameTextEl ? nameTextEl.textContent.trim() : '';
            }
            username = username.replace(/[✔✓]/g, '').trim();
            sellerSlug = sellerSlug || username;
            sellerSlug = sellerSlug.replace(/[✔✓]/g, '').trim().replace(/\s+/g, '-').replace(/[^\p{L}\p{N}_-]+/gu, '').replace(/^-+|-+$/g, '');
            if (!sellerSlug) return;

            el.style.cursor = 'pointer';

            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = '/sellers/' + encodeURIComponent(sellerSlug);
            });

            // Hover-Effekt
            el.style.transition = 'color .15s';
            el.addEventListener('mouseenter', function () { el.style.color = '#a5b4fc'; });
            el.addEventListener('mouseleave', function () { el.style.color = ''; });
        });
    }

    // Initial + nach Ajax-Nachladen (MutationObserver)
    process();
    var grid = document.getElementById('accountsGrid');
    if (grid) {
        new MutationObserver(process).observe(grid, { childList: true, subtree: true });
    }
})();
</script>

<?= $this->stop() ?>


<script>
/* ===== Smart Search Addon v3: rank keywords only (price untouched) ===== */
(function(){
  function ready(fn){
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    if (!window.jQuery) return;
    var $ = window.jQuery;

    var $search = $('#shopFilters input[name="search"]');
    if (!$search.length) return;

    var RANK_KEYWORDS = ['unranked','iron','bronze','silver','gold','platinum','emerald','diamond','master','grandmaster','challenger'];
    var prevRankSnapshot = null;
    var autoApplied = new Set();

    function snapshotRanks(){
      var checked = [];
      $('input[type="checkbox"][name="ranks[]"]').each(function(){
        if (this.checked) checked.push(String($(this).val()));
      });
      return checked;
    }

    function restoreRanks(snapshot){
      $('input[type="checkbox"][name="ranks[]"]').prop('checked', false);
      (snapshot || []).forEach(function(v){
        $('input[type="checkbox"][name="ranks[]"][value="'+v.replace(/"/g,'\\"')+'"]').prop('checked', true);
      });
    }

    function applyRankFromQuery(q){
      var query = (q || '').toLowerCase();
      var hit = null;
      for (var i=0;i<RANK_KEYWORDS.length;i++){
        if (query.indexOf(RANK_KEYWORDS[i]) !== -1){ hit = RANK_KEYWORDS[i]; break; }
      }
      if (!hit) return false;

      if (prevRankSnapshot === null) prevRankSnapshot = snapshotRanks();

      // clear previous auto-applied
      autoApplied.forEach(function(v){
        $('input[type="checkbox"][name="ranks[]"][value="'+v.replace(/"/g,'\\"')+'"]').prop('checked', false);
      });
      autoApplied = new Set();

      // match by label text (safe even if values are numeric)
      $('input[type="checkbox"][name="ranks[]"]').each(function(){
        var $cb = $(this);
        var txt = $cb.closest('.facet-item').find('.facet-item__text').text().trim().toLowerCase();
        if (txt.indexOf(hit) !== -1){
          $cb.prop('checked', true);
          autoApplied.add(String($cb.val()));
        }
      });

      return autoApplied.size > 0;
    }

    function clearAutoRank(){
      if (prevRankSnapshot === null) return;
      restoreRanks(prevRankSnapshot);
      prevRankSnapshot = null;
      autoApplied = new Set();
    }

    function triggerRefresh(){
      try{
        if (typeof resetToFirstPage === 'function') resetToFirstPage();
        if (typeof fetchAccounts === 'function') fetchAccounts({ page: 1 });
        if (typeof renderActiveFilters === 'function') renderActiveFilters();
        if (typeof updatePillSummaries === 'function') updatePillSummaries();
        if (typeof updateUrl === 'function' && typeof getFilterState === 'function') updateUrl(getFilterState(1));
      }catch(e){}
    }

    // Only modify Rank filters based on keyword in search.
    // Price is intentionally untouched.
    $search.on('input.smartRank', function(){
      var v = ($search.val() || '').trim();

      if (v.length === 0){
        // If search is cleared, we ONLY revert auto rank selection.
        clearAutoRank();
        setTimeout(triggerRefresh, 0);
        return;
      }

      var changed = applyRankFromQuery(v);
      if (changed) setTimeout(triggerRefresh, 0);
    });
  });
})();
</script>




<script>
/* ===== Champion Quick Filter Addon: typing a champion name filters visible cards (client-side) =====
   - Works without backend changes (matches text inside each card).
   - Does NOT touch Price.
   - Skips rank keywords (gold/silver/etc.) to avoid conflicts with rank smart filter.
*/
(function(){
  function ready(fn){
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    if (!window.jQuery) return;
    var $ = window.jQuery;

    var $search = $('#shopFilters input[name="search"]');
    var grid = document.getElementById('accountsGrid');
    if (!$search.length || !grid) return;

    var RANK_KEYWORDS = new Set(['unranked','iron','bronze','silver','gold','platinum','emerald','diamond','master','grandmaster','challenger']);

    var activeToken = ''; // lowercased champion token
    var lastAppliedToken = '';

    function getToken(){
      var v = ($search.val() || '').trim().toLowerCase();
      if (!v) return '';
      // use first word only (simple + predictable)
      var t = v.split(/\s+/)[0] || '';
      if (t.length < 3) return '';
      if (RANK_KEYWORDS.has(t)) return '';
      if (!/^[a-z]+$/.test(t)) return '';
      return t;
    }

    function applyFilter(){
      var token = activeToken;
      if (!token){
        // show all
        $('#accountsGrid .account-card, #accountsGrid .shop-account, #accountsGrid .account-item').each(function(){
          this.style.display = '';
        });
        // restore default meta (leave as-is; server is source of truth)
        return;
      }

      // Find card nodes (try several selectors to be safe)
      var $cards = $('#accountsGrid .account-card');
      if (!$cards.length) $cards = $('#accountsGrid .shop-account');
      if (!$cards.length) $cards = $('#accountsGrid .account-item');
      if (!$cards.length) return;

      var anyMatch = false;
      $cards.each(function(){
        var hay = (this.innerText || this.textContent || '').toLowerCase();
        var match = hay.indexOf(token) !== -1;
        if (match) anyMatch = true;
        this.style.display = match ? '' : 'none';
      });

      // If nothing matches, don't hide everything (avoid confusing empty state)
      if (!anyMatch){
        $cards.each(function(){ this.style.display = ''; });
        return;
      }

      // Update the "X / Y Accounts" line if present (best-effort)
      var visible = $cards.filter(function(){ return this.style.display !== 'none'; }).length;
      var $meta = $('.shop-results-meta, .results-meta, .shop-meta').first();
      if ($meta.length){
        var txt = $meta.text();
        // Try replace patterns like "12 / 30 Accounts"
        $meta.text(txt.replace(/(\d+)\s*\/\s*(\d+)\s*Accounts/i, visible + ' / ' + visible + ' Accounts'));
      }
    }

    function syncAndApply(){
      activeToken = getToken();
      if (activeToken === lastAppliedToken) return;
      lastAppliedToken = activeToken;
      applyFilter();
    }

    // Watch for results re-render (AJAX)
    var obs = new MutationObserver(function(){
      if (!activeToken) return;
      // small delay lets images/text settle
      setTimeout(applyFilter, 0);
    });
    obs.observe(grid, { childList: true, subtree: true });

    // On typing: recompute token + apply
    $search.on('input.championQuickFilter', function(){
      syncAndApply();
    });

    // Initial apply (in case page loads with ?search=aatrox)
    syncAndApply();
  });
})();
</script>


<!-- Removed forced mobile auto-scroll: it prevented scrolling back to the real top/content. -->



<style id="lb-mobile-shop-filter-redesign">
@media (max-width: 767px){
  body.ranked-accounts-page .navbar-mobile,
  body.ranked-accounts-page .lb-mobile-gamebar{
    transition: transform .22s ease, opacity .18s ease, visibility .18s ease;
    will-change: transform, opacity;
  }
  body.ranked-accounts-page.lb-shop-bars-hidden .navbar-mobile{
    transform: translateY(-115%) !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
  }
  body.ranked-accounts-page.lb-shop-bars-hidden .lb-mobile-gamebar{
    transform: translateY(-115%) !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
  }

  body.ranked-accounts-page header{
    display:block !important;
    padding:22px 16px 10px !important;
  }
  body.ranked-accounts-page header .content{
    display:block !important;
  }
  body.ranked-accounts-page header h1{
    font-size:24px !important;
    line-height:1.1 !important;
    margin:0 0 8px !important;
  }
  body.ranked-accounts-page header p{
    display:block !important;
    font-size:13px !important;
    line-height:1.45 !important;
    margin:0 !important;
    opacity:.78 !important;
  }
  body.ranked-accounts-page .container{
    width:100% !important;
    max-width:100% !important;
    padding:18px 16px 96px !important;
    box-sizing:border-box !important;
  }

  body.ranked-accounts-page .account-type-cards{
    display:flex !important;
    gap:10px !important;
    overflow-x:auto !important;
    padding:2px 0 12px !important;
    margin:0 0 12px !important;
    scrollbar-width:none !important;
  }
  body.ranked-accounts-page .account-type-cards::-webkit-scrollbar{
    display:none !important;
  }
  body.ranked-accounts-page .type-card{
    min-width:236px !important;
    padding:13px !important;
    border-radius:18px !important;
    background:rgba(255,255,255,.045) !important;
    border:1px solid rgba(255,255,255,.08) !important;
  }
  body.ranked-accounts-page .type-card.is-active{
    border-color:rgba(108,92,255,.62) !important;
    background:linear-gradient(180deg,rgba(108,92,255,.15),rgba(255,255,255,.045)) !important;
  }
  body.ranked-accounts-page .type-card__top{
    align-items:center !important;
    gap:10px !important;
  }
  body.ranked-accounts-page .type-card__subtitle,
  body.ranked-accounts-page .type-card__badge,
  body.ranked-accounts-page .type-card__cta{
    display:none !important;
  }
  body.ranked-accounts-page .type-card__title{
    font-size:13px !important;
    line-height:1.15 !important;
    margin-bottom:8px !important;
  }
  body.ranked-accounts-page .type-card__pills{
    display:flex !important;
    flex-wrap:wrap !important;
    gap:6px !important;
    margin-top:8px !important;
  }
  body.ranked-accounts-page .type-pill{
    font-size:10px !important;
    line-height:1 !important;
    padding:7px 9px !important;
    border-radius:999px !important;
    color:rgba(255,255,255,.72) !important;
    background:rgba(255,255,255,.06) !important;
    border:1px solid rgba(255,255,255,.09) !important;
    white-space:nowrap !important;
  }

  body.ranked-accounts-page #shopFilterbar.shop-filterbar{
    position:sticky !important;
    top:10px !important;
    z-index:90 !important;
    margin:0 0 16px !important;
    padding:0 !important;
    border:0 !important;
    background:transparent !important;
    box-shadow:none !important;
    backdrop-filter:none !important;
  }
  body.ranked-accounts-page #shopFilters{
    display:block !important;
  }
  body.ranked-accounts-page .shop-filterbar__row{
    display:grid !important;
    grid-template-columns:minmax(0,1fr) auto !important;
    gap:10px !important;
    align-items:center !important;
    padding:0 !important;
    background:transparent !important;
    border:0 !important;
  }
  body.ranked-accounts-page .shop-filterbar__search{
    height:48px !important;
    border-radius:12px !important;
    background:rgba(13,16,28,.92) !important;
    border:1px solid rgba(255,255,255,.11) !important;
    box-shadow:none !important;
  }
  body.ranked-accounts-page .shop-filterbar__search input{
    font-size:14px !important;
  }
  body.ranked-accounts-page .lb-mobile-filter-trigger{
    height:48px !important;
    min-width:92px !important;
    padding:0 14px !important;
    border-radius:12px !important;
    border:1px solid rgba(255,255,255,.10) !important;
    background:rgba(13,16,28,.92) !important;
    color:#fff !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px !important;
    font-size:14px !important;
    font-weight:800 !important;
  }

  body.ranked-accounts-page #shopFilterbar .shop-filterpill,
  body.ranked-accounts-page #shopFilterbar .shop-filterbar__actions{
    display:none !important;
  }
  body.ranked-accounts-page #activeFilters.shop-filterbar__chips{
    display:flex !important;
    flex-wrap:wrap !important;
    gap:8px !important;
    margin:12px 0 0 !important;
    padding:0 !important;
    min-height:0 !important;
    background:transparent !important;
    border:0 !important;
  }
  body.ranked-accounts-page #activeFilters .active-filters__hint{
    display:none !important;
  }
  body.ranked-accounts-page #activeFilters .filter-chip{
    height:30px !important;
    padding:0 10px !important;
    border-radius:999px !important;
    background:rgba(255,255,255,.075) !important;
    border:1px solid rgba(255,255,255,.10) !important;
    color:#fff !important;
    font-size:12px !important;
    font-weight:750 !important;
  }

  body.ranked-accounts-page .shop-toolbar{
    margin:12px 0 14px !important;
  }
  body.ranked-accounts-page .shop-count{
    font-size:15px !important;
    font-weight:850 !important;
  }

  body.ranked-accounts-page.lb-shop-filters-open{
    overflow:hidden !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open::before{
    content:"" !important;
    position:fixed !important;
    inset:0 !important;
    z-index:99970 !important;
    background:rgba(0,0,0,.58) !important;
    backdrop-filter:blur(4px) !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar{
    position:fixed !important;
    inset:0 !important;
    z-index:99980 !important;
    top:0 !important;
    margin:0 !important;
    pointer-events:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilters{
    position:absolute !important;
    left:0 !important;
    right:0 !important;
    bottom:0 !important;
    max-height:88vh !important;
    overflow-y:auto !important;
    overscroll-behavior:contain !important;
    pointer-events:auto !important;
    padding:12px 16px 24px !important;
    border-radius:24px 24px 0 0 !important;
    background:#0b0e16 !important;
    border:1px solid rgba(255,255,255,.10) !important;
    box-shadow:0 -24px 60px rgba(0,0,0,.55) !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilters::before{
    content:"" !important;
    display:block !important;
    width:96px !important;
    height:5px !important;
    border-radius:999px !important;
    background:rgba(255,255,255,.10) !important;
    margin:0 auto 18px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-head{
    display:flex !important;
  }
  body.ranked-accounts-page .lb-mobile-filter-sheet-head{
    display:none;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    margin:0 0 18px;
  }
  body.ranked-accounts-page .lb-mobile-filter-sheet-title{
    font-size:22px;
    line-height:1;
    font-weight:900;
    color:#fff;
  }
  body.ranked-accounts-page .lb-mobile-filter-sheet-clear{
    border:0;
    background:transparent;
    color:rgba(255,255,255,.58);
    font-size:14px;
    font-weight:800;
    padding:8px 0;
  }
  body.ranked-accounts-page .lb-mobile-filter-sheet-close{
    width:38px;
    height:38px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.06);
    color:#fff;
    display:inline-flex;
    align-items:center;
    justify-content:center;
  }

  body.ranked-accounts-page.lb-shop-filters-open .shop-filterbar__row{
    display:block !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-filterbar__search,
  body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-trigger{
    display:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterpill,
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterbar__actions{
    display:block !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterpill{
    margin:0 0 20px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-filterpill__btn{
    display:flex !important;
    height:auto !important;
    padding:0 !important;
    margin:0 0 12px !important;
    background:transparent !important;
    border:0 !important;
    color:rgba(255,255,255,.62) !important;
    justify-content:flex-start !important;
    pointer-events:none !important;
    font-size:15px !important;
    font-weight:850 !important;
    gap:10px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-filterpill__btn .fa-caret-down,
  body.ranked-accounts-page.lb-shop-filters-open .shop-filterpill__value{
    display:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-dropdown{
    display:block !important;
    position:static !important;
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
    pointer-events:auto !important;
    width:100% !important;
    min-width:0 !important;
    max-width:none !important;
    padding:0 !important;
    margin:0 !important;
    background:transparent !important;
    border:0 !important;
    box-shadow:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-dropdown__head,
  body.ranked-accounts-page.lb-shop-filters-open .facet-search,
  body.ranked-accounts-page.lb-shop-filters-open #ddMore .mf-view[data-view="menu"],
  body.ranked-accounts-page.lb-shop-filters-open #ddMore .mf-panelhead{
    display:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-dropdown__body{
    padding:0 !important;
    max-height:none !important;
    overflow:visible !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-scroll{
    display:flex !important;
    flex-wrap:wrap !important;
    gap:9px !important;
    max-height:none !important;
    overflow:visible !important;
    padding:0 !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item{
    width:auto !important;
    min-height:38px !important;
    padding:0 12px !important;
    border-radius:999px !important;
    background:rgba(255,255,255,.035) !important;
    border:1px solid rgba(255,255,255,.09) !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item__left{
    display:inline-flex !important;
    align-items:center !important;
    gap:8px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item__text{
    font-size:14px !important;
    color:rgba(255,255,255,.62) !important;
    font-weight:700 !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item__rank,
  body.ranked-accounts-page.lb-shop-filters-open .facet-item__role{
    width:22px !important;
    height:22px !important;
    object-fit:contain !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item__badge{
    min-width:24px !important;
    height:24px !important;
    border-radius:999px !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    font-size:10px !important;
    font-weight:900 !important;
    color:#fff !important;
    background:rgba(255,255,255,.08) !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item__check,
  body.ranked-accounts-page.lb-shop-filters-open .facet-item__box{
    position:absolute !important;
    opacity:0 !important;
    pointer-events:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item:has(.facet-item__check:checked){
    border-color:rgba(43,128,255,.65) !important;
    background:rgba(43,128,255,.14) !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .facet-item:has(.facet-item__check:checked)::after{
    content:"\f00c" !important;
    font-family:"Font Awesome 6 Pro","Font Awesome 6 Free" !important;
    font-weight:900 !important;
    color:#61a5ff !important;
    font-size:12px !important;
    margin-left:2px !important;
  }

  body.ranked-accounts-page.lb-shop-filters-open .shop-filterbar__actions{
    margin-top:6px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-sort{
    display:block !important;
    margin:0 0 20px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-sort__btn{
    display:flex !important;
    height:auto !important;
    padding:0 !important;
    margin:0 0 12px !important;
    background:transparent !important;
    border:0 !important;
    color:rgba(255,255,255,.62) !important;
    pointer-events:none !important;
    font-size:15px !important;
    font-weight:850 !important;
    justify-content:flex-start !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-sort__btn .fa-caret-down{
    display:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #ddSort{
    display:block !important;
    position:static !important;
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
    width:100% !important;
    padding:0 !important;
    background:transparent !important;
    border:0 !important;
    box-shadow:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #ddSort .shop-dropdown__body{
    display:flex !important;
    flex-wrap:wrap !important;
    gap:9px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-menuitem{
    width:auto !important;
    min-height:38px !important;
    padding:0 12px !important;
    border-radius:999px !important;
    border:1px solid rgba(255,255,255,.09) !important;
    background:rgba(255,255,255,.035) !important;
    color:rgba(255,255,255,.62) !important;
    font-size:14px !important;
    font-weight:750 !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .reset-filters{
    width:100% !important;
    height:48px !important;
    border-radius:14px !important;
    margin-top:4px !important;
    background:linear-gradient(135deg,#6567ff,#8a5cff) !important;
    color:#fff !important;
    font-size:15px !important;
    font-weight:900 !important;
    border:0 !important;
  }

  body.ranked-accounts-page.lb-shop-filters-open #ddPrice .shop-price{
    padding:12px !important;
    border-radius:18px !important;
    background:rgba(255,255,255,.035) !important;
    border:1px solid rgba(255,255,255,.08) !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #ddMore .mf-view{
    display:block !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #ddMore .mf-view[data-view="agents"],
  body.ranked-accounts-page.lb-shop-filters-open #ddMore .mf-view[data-view="champions"],
  body.ranked-accounts-page.lb-shop-filters-open #ddMore .mf-view[data-view="skins"]{
    display:none !important;
  }
}

/* Mobile shop top bar final: GameBoost-like search navbar */
@media (max-width: 767px){
  body.ranked-accounts-page #shopFilterbar.shop-filterbar{
    position:sticky !important;
    top:0 !important;
    z-index:250 !important;
    margin:0 -16px 14px !important;
    padding:12px 16px 10px !important;
    background:#0b0e16 !important;
    border-bottom:1px solid rgba(255,255,255,.075) !important;
    box-shadow:0 10px 26px rgba(0,0,0,.26) !important;
    backdrop-filter:none !important;
  }
  body.ranked-accounts-page #shopFilters{
    display:block !important;
    width:100% !important;
  }
  body.ranked-accounts-page .shop-filterbar__row{
    display:flex !important;
    grid-template-columns:none !important;
    flex-wrap:nowrap !important;
    align-items:center !important;
    gap:12px !important;
    width:100% !important;
    padding:0 !important;
    margin:0 !important;
    background:transparent !important;
    border:0 !important;
  }
  body.ranked-accounts-page .shop-filterbar__search{
    flex:1 1 auto !important;
    min-width:0 !important;
    width:auto !important;
    height:50px !important;
    border-radius:7px !important;
    background:#0f131d !important;
    border:1px solid rgba(255,255,255,.105) !important;
    box-shadow:none !important;
  }
  body.ranked-accounts-page .shop-filterbar__search i{
    font-size:17px !important;
    color:rgba(255,255,255,.72) !important;
  }
  body.ranked-accounts-page .shop-filterbar__search input{
    height:100% !important;
    font-size:16px !important;
    font-weight:500 !important;
  }
  body.ranked-accounts-page .lb-mobile-filter-trigger{
    flex:0 0 auto !important;
    width:auto !important;
    min-width:78px !important;
    height:50px !important;
    padding:0 2px !important;
    border-radius:0 !important;
    border:0 !important;
    background:transparent !important;
    box-shadow:none !important;
    color:#ffffff !important;
    gap:8px !important;
    font-size:15px !important;
    font-weight:850 !important;
  }
  body.ranked-accounts-page .lb-mobile-filter-trigger i{
    font-size:15px !important;
    color:#ffffff !important;
  }
  body.ranked-accounts-page #activeFilters.shop-filterbar__chips{
    margin:10px 0 0 !important;
    padding:0 !important;
    display:flex !important;
    flex-wrap:nowrap !important;
    gap:8px !important;
    overflow-x:auto !important;
    scrollbar-width:none !important;
  }
  body.ranked-accounts-page #activeFilters.shop-filterbar__chips::-webkit-scrollbar{
    display:none !important;
  }
  body.ranked-accounts-page #activeFilters .filter-chip{
    flex:0 0 auto !important;
    height:30px !important;
    padding:0 10px !important;
    border-radius:999px !important;
  }
  body.ranked-accounts-page .shop-toolbar{
    margin-top:12px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar{
    margin:0 !important;
    padding:0 !important;
    background:transparent !important;
    border-bottom:0 !important;
    box-shadow:none !important;
  }
}

</style>


<style id="lb-mobile-hide-active-filter-chips">
@media (max-width: 767px){
  .ranked-accounts-page .shop-filterbar__chips#activeFilters{
    display:none !important;
    visibility:hidden !important;
    height:0 !important;
    min-height:0 !important;
    margin:0 !important;
    padding:0 !important;
    overflow:hidden !important;
  }
}
</style>

<script id="lb-mobile-shop-filter-redesign-js">
(function(){
  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    var body = document.body;
    if(!body || !body.classList.contains('ranked-accounts-page')) return;

    var filterbar = document.getElementById('shopFilterbar');
    var form = document.getElementById('shopFilters');
    var row = filterbar ? filterbar.querySelector('.shop-filterbar__row') : null;
    if(!filterbar || !form || !row) return;

    if(!row.querySelector('.lb-mobile-filter-trigger')){
      var trigger = document.createElement('button');
      trigger.type = 'button';
      trigger.className = 'lb-mobile-filter-trigger';
      trigger.innerHTML = '<i class="fa-solid fa-filter-list"></i><span>Filters</span>';
      row.appendChild(trigger);
      trigger.addEventListener('click', function(e){
        e.preventDefault();
        body.classList.add('lb-shop-filters-open');
      });
    }

    if(!form.querySelector('.lb-mobile-filter-sheet-head')){
      var head = document.createElement('div');
      head.className = 'lb-mobile-filter-sheet-head';
      head.innerHTML = '<div class="lb-mobile-filter-sheet-title">Filters</div><button type="button" class="lb-mobile-filter-sheet-clear">Clear Filters</button><button type="button" class="lb-mobile-filter-sheet-close" aria-label="Close filters"><i class="fa-solid fa-xmark"></i></button>';
      form.insertBefore(head, form.firstChild);

      head.querySelector('.lb-mobile-filter-sheet-close').addEventListener('click', function(){
        body.classList.remove('lb-shop-filters-open');
      });
      head.querySelector('.lb-mobile-filter-sheet-clear').addEventListener('click', function(){
        var reset = form.querySelector('.reset-filters');
        if(reset) reset.click();
      });
    }

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape') body.classList.remove('lb-shop-filters-open');
    });

    document.addEventListener('click', function(e){
      if(!body.classList.contains('lb-shop-filters-open')) return;
      if(e.target.closest('#shopFilters')) return;
      body.classList.remove('lb-shop-filters-open');
    }, true);

    function updateBars(){
      if(window.innerWidth > 767){
        body.classList.remove('lb-shop-bars-hidden');
        return;
      }
      body.classList.toggle('lb-shop-bars-hidden', window.scrollY > 8);
    }
    updateBars();
    window.addEventListener('scroll', updateBars, {passive:true});
    window.addEventListener('resize', updateBars);
  });
})();
</script>



<style id="lb-mobile-filter-final-desktop-guard">
/* Final guard: mobile filter UI must never be visible on desktop. */
.lb-mobile-filter-trigger,
.lb-mobile-filter-sheet-head{
  display:none !important;
}

@media (min-width:768px){
  body.ranked-accounts-page.lb-shop-filters-open{
    overflow:auto !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open::before{
    display:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar{
    position:relative !important;
    inset:auto !important;
    z-index:1000000 !important;
    pointer-events:auto !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilters{
    position:static !important;
    max-height:none !important;
    overflow:visible !important;
    padding:0 !important;
    border-radius:0 !important;
    background:transparent !important;
    border:0 !important;
    box-shadow:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilters::before{
    display:none !important;
  }
}

@media (max-width:767px){
  body.ranked-accounts-page .lb-mobile-filter-trigger{
    display:inline-flex !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-head{
    display:flex !important;
  }

  /* Mobile top bar, GameBoost like: search left, filters right. */
  body.ranked-accounts-page #shopFilterbar.shop-filterbar{
    position:sticky !important;
    top:0 !important;
    z-index:250 !important;
    margin:0 -16px 14px !important;
    padding:12px 16px 10px !important;
    background:#0b0e16 !important;
    border-bottom:1px solid rgba(255,255,255,.075) !important;
    box-shadow:0 10px 26px rgba(0,0,0,.26) !important;
  }
  body.ranked-accounts-page .shop-filterbar__row{
    display:flex !important;
    flex-wrap:nowrap !important;
    align-items:center !important;
    gap:12px !important;
    width:100% !important;
    padding:0 !important;
    margin:0 !important;
    background:transparent !important;
    border:0 !important;
  }
  body.ranked-accounts-page .shop-filterbar__search{
    flex:1 1 auto !important;
    min-width:0 !important;
    width:auto !important;
    height:50px !important;
    border-radius:7px !important;
    background:#0f131d !important;
    border:1px solid rgba(255,255,255,.105) !important;
    box-shadow:none !important;
  }
  body.ranked-accounts-page .lb-mobile-filter-trigger{
    flex:0 0 auto !important;
    min-width:78px !important;
    height:50px !important;
    padding:0 !important;
    border-radius:0 !important;
    border:0 !important;
    background:transparent !important;
    color:#fff !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px !important;
    font-size:15px !important;
    font-weight:850 !important;
  }
  body.ranked-accounts-page #shopFilterbar .shop-filterpill,
  body.ranked-accounts-page #shopFilterbar .shop-filterbar__actions{
    display:none !important;
  }
  body.ranked-accounts-page #activeFilters.shop-filterbar__chips{
    display:none !important;
  }

  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar{
    position:fixed !important;
    inset:0 !important;
    z-index:99980 !important;
    margin:0 !important;
    padding:0 !important;
    background:transparent !important;
    border:0 !important;
    box-shadow:none !important;
    pointer-events:none !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilters{
    position:absolute !important;
    left:0 !important;
    right:0 !important;
    bottom:0 !important;
    max-height:88vh !important;
    overflow-y:auto !important;
    overscroll-behavior:contain !important;
    pointer-events:auto !important;
    padding:12px 16px 24px !important;
    border-radius:24px 24px 0 0 !important;
    background:#0b0e16 !important;
    border:1px solid rgba(255,255,255,.10) !important;
    box-shadow:0 -24px 60px rgba(0,0,0,.55) !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterpill,
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterbar__actions{
    display:block !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterpill{
    margin:0 0 20px !important;
  }
  body.ranked-accounts-page.lb-shop-filters-open .shop-filterbar__search,
  body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-trigger{
    display:none !important;
  }
}
</style>


<style id="lb-mobile-filter-final-fix">
/* Final mobile filter behavior: no desktop mobile UI, no duplicated bottom actions. */
@media (min-width: 768px){
  html body.ranked-accounts-page .lb-mobile-filter-trigger,
  html body.ranked-accounts-page .lb-mobile-filter-sheet-head,
  html body.ranked-accounts-page .lb-mobile-sort-section{
    display:none !important;
    visibility:hidden !important;
    height:0 !important;
    overflow:hidden !important;
  }
}

@media (max-width: 767px){
  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-sort-section{
    display:block !important;
    margin:0 0 22px !important;
    padding:0 !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section{
    display:none !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section__title{
    display:flex !important;
    align-items:center !important;
    gap:10px !important;
    margin:0 0 12px !important;
    color:rgba(255,255,255,.62) !important;
    font-size:15px !important;
    font-weight:850 !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section__title i{
    font-size:15px !important;
    color:rgba(255,255,255,.58) !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section__options{
    display:flex !important;
    align-items:center !important;
    gap:9px !important;
    overflow-x:auto !important;
    overflow-y:hidden !important;
    padding:0 0 2px !important;
    scrollbar-width:none !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section__options::-webkit-scrollbar{
    display:none !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section__options .shop-menuitem{
    width:auto !important;
    flex:0 0 auto !important;
    min-height:38px !important;
    padding:0 13px !important;
    border-radius:999px !important;
    border:1px solid rgba(255,255,255,.10) !important;
    background:rgba(255,255,255,.035) !important;
    color:rgba(255,255,255,.62) !important;
    font-size:14px !important;
    font-weight:750 !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px !important;
    white-space:nowrap !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section__options .shop-menuitem.is-active,
  html body.ranked-accounts-page .lb-mobile-sort-section__options .shop-menuitem[aria-current="true"]{
    border-color:rgba(43,128,255,.65) !important;
    background:rgba(43,128,255,.14) !important;
    color:#61a5ff !important;
  }
  html body.ranked-accounts-page .lb-mobile-sort-section__options .shop-menuitem.is-active::after,
  html body.ranked-accounts-page .lb-mobile-sort-section__options .shop-menuitem[aria-current="true"]::after{
    content:"\f00c" !important;
    font-family:"Font Awesome 6 Pro","Font Awesome 6 Free" !important;
    font-weight:900 !important;
    color:#61a5ff !important;
    font-size:12px !important;
  }

  /* Keep original actions hidden inside the mobile sheet. Sort is rendered once at the top. */
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterbar__actions,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .reset-filters,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-sort,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar #ddSort,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterpill[data-dropdown="ddMore"],
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar #ddMore{
    display:none !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .shop-filterpill__btn{
    margin-bottom:12px !important;
  }
}
</style>

<script id="lb-mobile-filter-final-js">
(function(){
  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }
  ready(function(){
    var body = document.body;
    if(!body || !body.classList.contains('ranked-accounts-page')) return;
    var form = document.getElementById('shopFilters');
    if(!form) return;

    function syncActiveSort(){
      var label = document.getElementById('sortLabel');
      var current = label ? (label.textContent || '').trim().toLowerCase() : '';
      var section = form.querySelector('.lb-mobile-sort-section');
      if(!section) return;
      section.querySelectorAll('.shop-menuitem').forEach(function(btn){
        var text = (btn.textContent || '').trim().toLowerCase();
        var active = current && text.indexOf(current) !== -1;
        btn.classList.toggle('is-active', active);
        if(active) btn.setAttribute('aria-current','true');
        else btn.removeAttribute('aria-current');
      });
    }

    function ensureMobileSortSection(){
      var head = form.querySelector('.lb-mobile-filter-sheet-head');
      if(!head) return;
      var existing = form.querySelector('.lb-mobile-sort-section');
      if(existing){
        syncActiveSort();
        return;
      }
      var originalItems = form.querySelectorAll('#ddSort .shop-menuitem');
      if(!originalItems.length) return;
      var section = document.createElement('div');
      section.className = 'lb-mobile-sort-section';
      section.innerHTML = '<div class="lb-mobile-sort-section__title"><i class="fa-solid fa-arrow-up-down"></i><span>Sort by</span></div><div class="lb-mobile-sort-section__options"></div>';
      var options = section.querySelector('.lb-mobile-sort-section__options');
      originalItems.forEach(function(item){
        var clone = item.cloneNode(true);
        clone.type = 'button';
        clone.addEventListener('click', function(e){
          var mode = clone.getAttribute('data-sort');
          if(mode){
            var real = form.querySelector('#ddSort .shop-menuitem[data-sort="' + mode.replace(/"/g,'') + '"]');
            if(real) real.click();
          }
          setTimeout(syncActiveSort, 80);
        });
        options.appendChild(clone);
      });
      head.insertAdjacentElement('afterend', section);
      syncActiveSort();
    }

    ensureMobileSortSection();
    document.addEventListener('click', function(e){
      if(e.target.closest('.lb-mobile-filter-trigger')) setTimeout(ensureMobileSortSection, 30);
      if(e.target.closest('#ddSort .shop-menuitem') || e.target.closest('.lb-mobile-sort-section .shop-menuitem')) setTimeout(syncActiveSort, 80);
    }, true);
  });
})();
</script>

<style id="lb-mobile-filter-polish-final">


/* ===== Mobile polish: Lolboost search/filter bar + clear filters CTA ===== */
@media (max-width: 767px){
  html body.ranked-accounts-page #shopFilterbar.shop-filterbar{
    padding:12px 14px 11px!important;
    background:linear-gradient(180deg,#0c0f19 0%,#090c14 100%)!important;
    border-bottom:1px solid rgba(118,103,255,.18)!important;
    box-shadow:0 14px 34px rgba(0,0,0,.38)!important;
  }

  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
    gap:10px!important;
  }

  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{
    height:50px!important;
    border-radius:10px!important;
    padding:0 15px!important;
    background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.018))!important;
    border:1px solid rgba(255,255,255,.10)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.035),0 8px 20px rgba(0,0,0,.18)!important;
  }

  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search:focus-within{
    border-color:rgba(113,97,255,.62)!important;
    box-shadow:0 0 0 3px rgba(113,97,255,.16),inset 0 1px 0 rgba(255,255,255,.04)!important;
  }

  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search i{
    color:rgba(255,255,255,.68)!important;
    font-size:16px!important;
  }

  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search input{
    color:#fff!important;
    font-size:15px!important;
    font-weight:650!important;
  }

  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search input::placeholder{
    color:rgba(255,255,255,.46)!important;
    font-weight:600!important;
  }

  html body.ranked-accounts-page #shopFilterbar .lb-mobile-filter-trigger{
    height:50px!important;
    min-width:96px!important;
    padding:0 14px!important;
    border-radius:12px!important;
    background:linear-gradient(180deg,rgba(255,255,255,.07),rgba(255,255,255,.025))!important;
    border:1px solid rgba(255,255,255,.10)!important;
    color:#fff!important;
    font-size:15px!important;
    font-weight:900!important;
    letter-spacing:.01em!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 8px 20px rgba(0,0,0,.20)!important;
  }

  html body.ranked-accounts-page #shopFilterbar .lb-mobile-filter-trigger i{
    color:#8b7cff!important;
    font-size:16px!important;
    filter:drop-shadow(0 0 10px rgba(139,124,255,.45))!important;
  }

  html body.ranked-accounts-page #shopFilterbar .lb-mobile-filter-trigger:active{
    transform:translateY(1px)!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters{
    background:linear-gradient(180deg,#0d1018 0%,#080b12 100%)!important;
    border-color:rgba(118,103,255,.22)!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-head{
    align-items:center!important;
    gap:10px!important;
    margin:0 0 22px!important;
    padding:0 0 4px!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-title{
    flex:1 1 auto!important;
    font-size:23px!important;
    font-weight:950!important;
    color:#fff!important;
    letter-spacing:-.02em!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-clear{
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    height:36px!important;
    padding:0 13px!important;
    border-radius:999px!important;
    background:rgba(139,124,255,.14)!important;
    border:1px solid rgba(139,124,255,.32)!important;
    color:#c7c2ff!important;
    font-size:12px!important;
    font-weight:900!important;
    line-height:1!important;
    white-space:nowrap!important;
    text-shadow:none!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-clear::before{
    content:"\f2ed"!important;
    font-family:"Font Awesome 6 Pro","Font Awesome 6 Free"!important;
    font-weight:900!important;
    margin-right:7px!important;
    font-size:11px!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-clear:active{
    transform:translateY(1px)!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-close{
    width:40px!important;
    height:40px!important;
    border-radius:13px!important;
    background:rgba(255,255,255,.06)!important;
    border:1px solid rgba(255,255,255,.12)!important;
    color:#fff!important;
  }
}

</style>



<style id="lb-mobile-top-content-restore-final">
@media (max-width: 767px){
  html,
  body.ranked-accounts-page{
    scroll-behavior:auto !important;
    overflow-y:auto !important;
  }

  body.ranked-accounts-page header{
    display:block !important;
    visibility:visible !important;
    opacity:1 !important;
  }

  body.ranked-accounts-page header .content,
  body.ranked-accounts-page .account-type-cards,
  body.ranked-accounts-page .accounts-grid,
  body.ranked-accounts-page #accountsGrid{
    display:flex !important;
    visibility:visible !important;
    opacity:1 !important;
  }

  body.ranked-accounts-page header .content{
    display:block !important;
  }

  body.ranked-accounts-page .accounts-grid,
  body.ranked-accounts-page #accountsGrid{
    display:grid !important;
  }

  body.ranked-accounts-page .container{
    padding-top:12px !important;
  }

  body.ranked-accounts-page:not(.lb-shop-filters-open),
  body.ranked-accounts-page:not(.lb-shop-filters-open) .page-zoom,
  body.ranked-accounts-page:not(.lb-shop-filters-open) main,
  body.ranked-accounts-page:not(.lb-shop-filters-open) .container{
    transform:none !important;
    overflow:visible !important;
    height:auto !important;
  }
}
</style>

<style id="lb-mobile-scroll-stability-final">
/* Final fix: no scroll-lock unless the mobile filter sheet is actually open. */
@media (max-width: 767px){
  html,
  body.ranked-accounts-page{
    position: static !important;
    height: auto !important;
    min-height: 100% !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    touch-action: pan-y !important;
    overscroll-behavior-y: auto !important;
  }

  body.ranked-accounts-page:not(.lb-shop-filters-open),
  body.ranked-accounts-page:not(.lb-shop-filters-open) .page-zoom,
  body.ranked-accounts-page:not(.lb-shop-filters-open) main,
  body.ranked-accounts-page:not(.lb-shop-filters-open) .container{
    position: static !important;
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
    transform: none !important;
    touch-action: pan-y !important;
  }

  body.ranked-accounts-page:not(.lb-shop-filters-open)::before{
    display:none !important;
    content:none !important;
    pointer-events:none !important;
  }

  body.ranked-accounts-page.lb-shop-filters-open{
    overflow: hidden !important;
    touch-action: none !important;
  }

  body.ranked-accounts-page.lb-shop-filters-open #shopFilters{
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    touch-action: pan-y !important;
  }

  body.ranked-accounts-page #shopFilterbar{
    pointer-events:auto !important;
  }
}
</style>

<script id="lb-mobile-scroll-stability-final-js">
(function(){
  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    var body = document.body;
    var root = document.documentElement;
    if(!body || !body.classList.contains('ranked-accounts-page')) return;

    var lastScrollY = 0;

    function clearInlineLocks(){
      body.classList.remove('filter-dd-open');
      body.classList.remove('sort-dd-open');
      body.style.overflow = '';
      body.style.position = '';
      body.style.height = '';
      body.style.top = '';
      body.style.width = '';
      root.style.overflow = '';
      root.style.height = '';
    }

    function closeSheet(){
      body.classList.remove('lb-shop-filters-open');
      clearInlineLocks();
      if(lastScrollY > 0) window.scrollTo(0, lastScrollY);
    }

    document.addEventListener('click', function(e){
      var trigger = e.target.closest('.lb-mobile-filter-trigger');
      var close = e.target.closest('.lb-mobile-filter-sheet-close');
      var clear = e.target.closest('.lb-mobile-filter-sheet-clear');

      if(trigger){
        lastScrollY = window.scrollY || window.pageYOffset || 0;
        body.classList.add('lb-shop-filters-open');
        body.classList.remove('filter-dd-open');
        body.classList.remove('sort-dd-open');
        return;
      }

      if(close){
        e.preventDefault();
        closeSheet();
        return;
      }

      if(clear){
        setTimeout(function(){
          body.classList.remove('filter-dd-open');
          body.classList.remove('sort-dd-open');
        }, 0);
      }
    }, true);

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape') closeSheet();
    });

    window.addEventListener('resize', function(){
      if(window.innerWidth > 767) closeSheet();
      else if(!body.classList.contains('lb-shop-filters-open')) clearInlineLocks();
    }, {passive:true});

    window.addEventListener('pageshow', function(){
      if(!body.classList.contains('lb-shop-filters-open')) clearInlineLocks();
    }, {passive:true});

    clearInlineLocks();
  });
})();
</script>

<style id="lb-mobile-header-safe-offset-final">
@media (max-width: 767px){
  body.ranked-accounts-page header{
    padding-top:128px !important;
    padding-bottom:18px !important;
  }
  body.ranked-accounts-page header .content{
    margin-top:0 !important;
    position:relative !important;
    z-index:1 !important;
  }
  body.ranked-accounts-page header h1{
    margin-top:0 !important;
  }
}
</style>


<style id="lb-shop-header-overlap-fix">
/* Desktop: hide the game subnav on ranked shop pages after the first scroll,
   so the item grid gets more visual focus. */
@media (min-width: 1025px){
  body.ranked-accounts-page.lb-desktop-subnav-hidden .lb-game-subnav{
    display:none !important;
  }
}

/* Mobile: keep the sticky search/filter bar below the dynamic mobile header
   while the header is visible. Once the shop scrolls and the header is hidden,
   the bar moves back to the top for maximum space. */
@media (max-width: 767px){
  body.ranked-accounts-page #shopFilterbar.shop-filterbar{
    position: sticky !important;
    top: 0 !important;
    z-index: 99999 !important;
    margin-top: 0 !important;
  }
  body.ranked-accounts-page.lb-shop-bars-hidden #shopFilterbar.shop-filterbar,
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar.shop-filterbar{
    top: 0 !important;
  }
}
</style>


<style id="lb-force-filterbar-top-final">
@media (max-width: 767px){
  body.ranked-accounts-page #shopFilterbar.shop-filterbar.shop-filterbar--sticky{
    position: sticky !important;
    top: 0 !important;
    z-index: 99999 !important;
    margin-top: 0 !important;
  }

  body.ranked-accounts-page.lb-shop-bars-hidden #shopFilterbar.shop-filterbar,
  body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar.shop-filterbar{
    top: 0 !important;
  }

  body.ranked-accounts-page .container{
    padding-top: 0 !important;
  }
}
</style>

<style id="lb-mobile-pagination-size-final">
@media (max-width: 767px){
  body.ranked-accounts-page #shopPagination.shop-pagination{
    width: 100% !important;
    margin: 22px 0 110px !important;
    padding: 0 8px !important;
    box-sizing: border-box !important;
  }

  body.ranked-accounts-page #shopPagination .page-bar{
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    flex-wrap: nowrap !important;
    width: 100% !important;
    padding: 10px 8px !important;
    border-radius: 18px !important;
    box-sizing: border-box !important;
    overflow-x: auto !important;
    scrollbar-width: none !important;
  }

  body.ranked-accounts-page #shopPagination .page-bar::-webkit-scrollbar{
    display: none !important;
  }

  body.ranked-accounts-page #shopPagination .page-btn,
  body.ranked-accounts-page #shopPagination .page-ellipsis{
    min-width: 42px !important;
    height: 42px !important;
    padding: 0 13px !important;
    border-radius: 14px !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    line-height: 42px !important;
    flex: 0 0 auto !important;
    white-space: nowrap !important;
    text-align: center !important;
  }

  body.ranked-accounts-page #shopPagination .page-btn.is-nav{
    min-width: 82px !important;
    padding: 0 14px !important;
    font-size: 13px !important;
    letter-spacing: .01em !important;
  }

  body.ranked-accounts-page #shopPagination .page-btn.is-disabled{
    opacity: .55 !important;
  }
}
</style>


<style id="lb-mobile-pagination-valorant-row-final">
@media (max-width: 767px){
  body.ranked-accounts-page #shopPagination.shop-pagination{
    width: 100% !important;
    max-width: 100% !important;
    margin: 18px 0 110px !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    overflow: visible !important;
  }

  body.ranked-accounts-page #shopPagination .page-bar{
    display: grid !important;
    grid-template-columns: minmax(68px, 1fr) 48px 28px minmax(56px, .8fr) minmax(68px, 1fr) !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    width: 100% !important;
    max-width: 100% !important;
    padding: 8px 0 !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
    overflow: visible !important;
  }

  body.ranked-accounts-page #shopPagination .page-btn,
  body.ranked-accounts-page #shopPagination .page-ellipsis{
    min-width: 0 !important;
    width: 100% !important;
    height: 42px !important;
    padding: 0 !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    line-height: 42px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: none !important;
    white-space: nowrap !important;
    text-align: center !important;
  }

  body.ranked-accounts-page #shopPagination .page-btn.is-nav{
    min-width: 0 !important;
    width: 100% !important;
    padding: 0 !important;
    font-size: 16px !important;
  }

  body.ranked-accounts-page #shopPagination .page-btn.is-disabled{
    opacity: .45 !important;
  }
}
</style>

<style id="lb-mobile-filter-sheet-visible-v5">
@media (max-width: 767px){
  html body.ranked-accounts-page.lb-shop-filters-open{
    overflow: hidden !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open::before{
    z-index: 2147483000 !important;
    background: rgba(0,0,0,.72) !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar.shop-filterbar{
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100dvh !important;
    max-height: 100dvh !important;
    z-index: 2147483010 !important;
    margin: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    pointer-events: none !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters{
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: auto !important;
    width: 100vw !important;
    height: 100dvh !important;
    max-height: 100dvh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    overscroll-behavior: contain !important;
    pointer-events: auto !important;
    padding: 14px 16px calc(26px + env(safe-area-inset-bottom)) !important;
    border-radius: 0 !important;
    background: linear-gradient(180deg,#0d1018 0%,#070a11 100%) !important;
    border: 0 !important;
    box-shadow: none !important;
    transform: none !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters::before{
    display: none !important;
    content: none !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-filter-sheet-head{
    position: sticky !important;
    top: 0 !important;
    z-index: 5 !important;
    display: flex !important;
    align-items: center !important;
    margin: -14px -16px 18px !important;
    padding: calc(14px + env(safe-area-inset-top)) 16px 14px !important;
    background: linear-gradient(180deg,#0f1320 0%,#0b0e16 100%) !important;
    border-bottom: 1px solid rgba(118,103,255,.18) !important;
    box-shadow: 0 10px 28px rgba(0,0,0,.34) !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .shop-filterbar__row{
    display: block !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar .shop-filterpill:first-of-type{
    margin-top: 0 !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .shop-dropdown,
  html body.ranked-accounts-page.lb-shop-filters-open .shop-dropdown.is-portal{
    position: static !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    inset: auto !important;
    width: 100% !important;
    max-width: none !important;
    pointer-events: auto !important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open .navbar-mobile,
  html body.ranked-accounts-page.lb-shop-filters-open .lb-mobile-gamebar{
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }
}
</style>

<script id="lb-mobile-filter-open-scrolltop-v5">
(function(){
  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    var body = document.body;
    if(!body || !body.classList.contains('ranked-accounts-page')) return;

    function resetFilterSheetScroll(){
      var form = document.getElementById('shopFilters');
      if(form) form.scrollTop = 0;
      var filterbar = document.getElementById('shopFilterbar');
      if(filterbar) filterbar.scrollTop = 0;
      document.querySelectorAll('.shop-dropdown').forEach(function(dd){ dd.scrollTop = 0; });
    }

    document.addEventListener('click', function(e){
      if(e.target.closest('.lb-mobile-filter-trigger')){
        setTimeout(resetFilterSheetScroll, 0);
        setTimeout(resetFilterSheetScroll, 60);
      }
    }, true);

    var observer = new MutationObserver(function(){
      if(body.classList.contains('lb-shop-filters-open')) resetFilterSheetScroll();
    });
    observer.observe(body, { attributes: true, attributeFilter: ['class'] });
  });
})();
</script>


<style id="lb-popular-searches-style">
.lb-popular-searches{
  display:flex;
  align-items:center;
  gap:10px;
  margin:10px 0 18px;
  padding:2px 0;
  overflow-x:auto;
  scrollbar-width:none;
  -webkit-overflow-scrolling:touch;
}
.lb-popular-searches::-webkit-scrollbar{display:none;}
.lb-popular-searches__title{
  flex:0 0 auto;
  font-size:14px;
  font-weight:700;
  color:rgba(255,255,255,.72);
}
.lb-popular-pill{
  flex:0 0 auto;
  border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.06);
  color:rgba(255,255,255,.82);
  border-radius:999px;
  padding:8px 13px;
  font-size:14px;
  font-weight:800;
  line-height:1;
  cursor:pointer;
  transition:transform .16s ease, border-color .16s ease, background .16s ease, color .16s ease;
}
.lb-popular-pill:hover{
  transform:translateY(-1px);
  border-color:rgba(124,92,255,.55);
  background:rgba(124,92,255,.18);
  color:#fff;
}
@media (max-width:767px){
  body.ranked-accounts-page .lb-popular-searches{
    margin:8px -16px 14px;
    padding:0 16px 2px;
  }
  body.ranked-accounts-page .lb-popular-searches__title{
    font-size:13px;
  }
  body.ranked-accounts-page .lb-popular-pill{
    padding:9px 12px;
    font-size:13px;
  }
}
</style>


<style id="lb-val-account-type-cards-final">
@media (max-width: 767px){
  body.ranked-accounts-page .account-type-cards{
    display:grid !important;
    grid-template-columns:1fr 1fr !important;
    gap:10px !important;
    overflow:visible !important;
    padding:4px 0 14px !important;
    margin:0 0 10px !important;
  }

  body.ranked-accounts-page .type-card{
    min-width:0 !important;
    width:100% !important;
    position:relative !important;
    padding:12px 10px 11px !important;
    border-radius:18px !important;
    background:linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.025)) !important;
    border:1px solid rgba(255,255,255,.10) !important;
    box-shadow:0 12px 28px rgba(0,0,0,.22) !important;
    overflow:hidden !important;
  }

  body.ranked-accounts-page .type-card:before{
    content:"" !important;
    position:absolute !important;
    inset:0 !important;
    background:radial-gradient(circle at 24% 0%, rgba(124,92,255,.28), transparent 46%) !important;
    opacity:.7 !important;
    pointer-events:none !important;
  }

  body.ranked-accounts-page .type-card.is-active{
    border-color:rgba(124,92,255,.85) !important;
    background:linear-gradient(180deg, rgba(124,92,255,.22), rgba(255,255,255,.04)) !important;
    box-shadow:0 0 0 1px rgba(124,92,255,.18), 0 16px 34px rgba(0,0,0,.28) !important;
  }

  body.ranked-accounts-page .type-card__top{
    position:relative !important;
    display:flex !important;
    align-items:center !important;
    gap:9px !important;
  }

  body.ranked-accounts-page .type-card__icon{
    width:36px !important;
    height:36px !important;
    min-width:36px !important;
    border-radius:14px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    background:rgba(0,0,0,.22) !important;
    border:1px solid rgba(255,255,255,.10) !important;
  }

  body.ranked-accounts-page .type-card__icon img{
    width:28px !important;
    height:28px !important;
    object-fit:contain !important;
  }

  body.ranked-accounts-page .type-card__titles{
    min-width:0 !important;
  }

  body.ranked-accounts-page .type-card__title{
    font-size:12.5px !important;
    line-height:1.15 !important;
    margin:0 !important;
    white-space:normal !important;
  }

  body.ranked-accounts-page .type-card__subtitle{
    display:block !important;
    margin-top:4px !important;
    font-size:10px !important;
    line-height:1.25 !important;
    color:rgba(255,255,255,.58) !important;
    max-height:26px !important;
    overflow:hidden !important;
  }

  body.ranked-accounts-page .type-card__badge,
  body.ranked-accounts-page .type-card__pills,
  body.ranked-accounts-page .type-card__cta{
    display:none !important;
  }

  body.ranked-accounts-page .type-card.is-active:after{
    content:"Active" !important;
    position:absolute !important;
    right:9px !important;
    bottom:8px !important;
    font-size:9px !important;
    line-height:1 !important;
    padding:5px 7px !important;
    border-radius:999px !important;
    color:#fff !important;
    background:rgba(124,92,255,.72) !important;
    border:1px solid rgba(255,255,255,.16) !important;
  }
}
</style>


<style id="lb-mobile-typecards-title-only-final">
@media (max-width: 767px){
  body.ranked-accounts-page .account-type-cards{
    gap:10px !important;
  }

  body.ranked-accounts-page .type-card{
    min-height:66px !important;
    padding:14px 16px !important;
    align-items:center !important;
  }

  body.ranked-accounts-page .type-card__top{
    align-items:center !important;
    gap:12px !important;
  }

  body.ranked-accounts-page .type-card__titles{
    display:flex !important;
    align-items:center !important;
    min-width:0 !important;
  }

  body.ranked-accounts-page .type-card__title{
    font-size:15px !important;
    line-height:1.1 !important;
    font-weight:900 !important;
    letter-spacing:-.02em !important;
    margin:0 !important;
    white-space:nowrap !important;
  }

  body.ranked-accounts-page .type-card__subtitle,
  body.ranked-accounts-page .type-card__badge,
  body.ranked-accounts-page .type-card__pills,
  body.ranked-accounts-page .type-card__cta{
    display:none !important;
  }

  body.ranked-accounts-page .type-card.is-active:after{
    content:none !important;
    display:none !important;
  }
}
</style>

<style id="lb-popular-searches-bottom-style">
.lb-popular-searches--bottom{
  margin-top:14px !important;
  margin-bottom:34px !important;
  justify-content:center !important;
}
@media (max-width:767px){
  body.ranked-accounts-page .lb-popular-searches--bottom{
    margin-top:12px !important;
    margin-bottom:28px !important;
    padding:0 2px 2px !important;
    justify-content:flex-start !important;
  }
}
</style>


<style id="lb-bottom-popular-balanced-final">
/* Balanced bottom pagination spacing: desktop has breathing room, mobile stays compact. */
body.ranked-accounts-page #shopPagination.shop-pagination{
  margin-top: 26px !important;
  margin-bottom: 0 !important;
}
body.ranked-accounts-page .lb-popular-searches--bottom{
  margin-top: 12px !important;
  margin-bottom: 54px !important;
  padding-top: 0 !important;
}
@media (max-width: 900px){
  body.ranked-accounts-page #shopPagination.shop-pagination{
    width: 100% !important;
    max-width: 100% !important;
    margin-top: 18px !important;
    margin-bottom: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
  }
  body.ranked-accounts-page #shopPagination .page-bar{
    display: grid !important;
    grid-template-columns: minmax(72px, 1fr) 46px 22px minmax(56px, .8fr) minmax(72px, 1fr) !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    width: 100% !important;
    max-width: 100% !important;
    padding: 6px 0 0 !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
    overflow: visible !important;
  }
  body.ranked-accounts-page #shopPagination .page-btn:not(.is-nav):not(.is-active):not(:nth-last-child(2)){
    display: none !important;
  }
  body.ranked-accounts-page #shopPagination .page-btn,
  body.ranked-accounts-page #shopPagination .page-ellipsis{
    min-width: 0 !important;
    width: 100% !important;
    height: 38px !important;
    padding: 0 !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    line-height: 38px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: none !important;
    white-space: nowrap !important;
    text-align: center !important;
  }
  body.ranked-accounts-page #shopPagination .page-btn.is-nav{
    font-size: 0 !important;
  }
  body.ranked-accounts-page #shopPagination .page-btn.is-nav:first-child::before{
    content: '‹' !important;
    font-size: 24px !important;
    line-height: 1 !important;
  }
  body.ranked-accounts-page #shopPagination .page-btn.is-nav:last-child::before{
    content: '›' !important;
    font-size: 24px !important;
    line-height: 1 !important;
  }
  body.ranked-accounts-page #shopPagination .page-btn.is-disabled{
    opacity: .45 !important;
  }
  body.ranked-accounts-page .lb-popular-searches--bottom{
    margin-top: 10px !important;
    margin-bottom: 24px !important;
    padding: 0 2px !important;
    min-height: 0 !important;
  }
}
</style>

<style id="lb-responsive-account-grid-final">
/* Final shop grid fix: keep a stable amount of cards per row, even when browser zoom changes. */
body.ranked-accounts-page #accountsGrid.accounts-grid{
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

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card{
  width:100% !important;
  min-width:0 !important;
  max-width:none !important;
  box-sizing:border-box !important;
  overflow:hidden !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .cover-link{
  min-width:0 !important;
  width:100% !important;
  box-sizing:border-box !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .title,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .excerpt{
  min-width:0 !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals{
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

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .price-eur{
  min-width:0 !important;
  max-width:100% !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  white-space:nowrap !important;
  font-size:clamp(20px, 1.12vw, 27px) !important;
  line-height:1.05 !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn.primary{
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
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn.primary i{
  margin-left:0 !important;
  font-size:13px !important;
  flex:0 0 auto !important;
}

@media (max-width: 1199px){
  body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(3, minmax(0, 1fr)) !important;
    gap:20px !important;
  }
}

@media (max-width: 940px){
  body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(2, minmax(0, 1fr)) !important;
  }
}

@media (max-width: 767px){
  body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:1fr !important;
    gap:18px !important;
    width:100% !important;
  }

  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card{
    width:100% !important;
    max-width:none !important;
    min-width:0 !important;
  }

  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .price-eur{
    font-size:clamp(22px, 6vw, 28px) !important;
  }

  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn,
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .totals .btn.primary{
    min-width:112px !important;
    max-width:132px !important;
    padding:11px 18px !important;
    font-size:14px !important;
  }
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

@media(max-width:767px){
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
  body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
  .ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
  .ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
  body.ranked-accounts-page #accountsGrid .account-card .seller-info,
  .ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
  .ranked-accounts-page #accountsGrid .account-card .seller-info{
    width:calc(100% + 36px) !important;
    margin-left:-18px !important;
    margin-right:-18px !important;
    margin-bottom:-18px !important;
    padding-left:18px !important;
    padding-right:18px !important;
  }
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


<style id="lb-val-shop-redesign-2026">
body.ranked-accounts-page{
  background:
    radial-gradient(1100px 620px at 18% 0%,rgba(79,110,247,.13),transparent 60%),
    radial-gradient(900px 560px at 86% 8%,rgba(99,102,241,.09),transparent 58%),
    linear-gradient(180deg,#04050f 0%,#070a1a 52%,#04050f 100%)!important;
}
body.ranked-accounts-page main{overflow:visible!important;}
body.ranked-accounts-page .lb-shop-hero{
  position:relative!important;
  isolation:isolate!important;
  background:
    linear-gradient(90deg,rgba(4,5,15,.98),rgba(7,10,26,.94)),
    radial-gradient(700px 280px at 18% 30%,rgba(79,110,247,.20),transparent 68%)!important;
  border-bottom:1px solid rgba(255,255,255,.07)!important;
  overflow:hidden!important;
}
body.ranked-accounts-page .lb-shop-hero:before{
  content:"";
  position:absolute;
  inset:0;
  z-index:-1;
  opacity:.18;
  background-image:linear-gradient(to right,rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(to bottom,rgba(255,255,255,.05) 1px,transparent 1px);
  background-size:56px 56px;
  mask-image:linear-gradient(90deg,#000,transparent 84%);
}
body.ranked-accounts-page .lb-shop-hero__inner{
  width:min(1460px,calc(100% - 48px))!important;
  max-width:1460px!important;
  min-height:210px!important;
  margin:0 auto!important;
  padding:42px 0!important;
  display:flex!important;
  align-items:center!important;
  gap:24px!important;
}
body.ranked-accounts-page .lb-shop-hero__icon{
  width:78px!important;
  height:78px!important;
  min-width:78px!important;
  border-radius:22px!important;
  background:linear-gradient(145deg,rgba(79,110,247,.20),rgba(124,159,255,.06))!important;
  border:1px solid rgba(124,159,255,.22)!important;
  box-shadow:0 18px 44px rgba(0,0,0,.30),inset 0 1px 0 rgba(255,255,255,.08)!important;
}
body.ranked-accounts-page .lb-shop-hero__icon i{
  color:#8ea5ff!important;
  font-size:31px!important;
}
body.ranked-accounts-page .lb-shop-hero__kicker{
  margin:0 0 8px!important;
  color:#92a7ff!important;
  font-size:11px!important;
  font-weight:950!important;
  letter-spacing:.18em!important;
}
body.ranked-accounts-page .lb-shop-hero__title{
  margin:0!important;
  color:#fff!important;
  font-size:clamp(30px,3vw,46px)!important;
  font-weight:950!important;
  line-height:1.08!important;
  letter-spacing:-.035em!important;
}
body.ranked-accounts-page .lb-shop-hero__desc{
  max-width:760px!important;
  margin:12px 0 0!important;
  color:rgba(226,231,255,.62)!important;
  font-size:15px!important;
  font-weight:500!important;
  line-height:1.65!important;
  white-space:normal!important;
  overflow:visible!important;
}
body.ranked-accounts-page>.container,
body.ranked-accounts-page main>.container{
  width:min(1460px,calc(100% - 48px))!important;
  max-width:1460px!important;
  margin-inline:auto!important;
  padding:30px 0 76px!important;
}
body.ranked-accounts-page .account-type-cards{
  display:grid!important;
  grid-template-columns:repeat(2,minmax(0,1fr))!important;
  gap:14px!important;
  margin:0 0 20px!important;
}
body.ranked-accounts-page .type-card{
  position:relative!important;
  min-width:0!important;
  padding:18px!important;
  border:1px solid rgba(255,255,255,.075)!important;
  border-radius:20px!important;
  background:linear-gradient(145deg,rgba(13,17,36,.90),rgba(8,10,24,.92))!important;
  box-shadow:0 14px 40px rgba(0,0,0,.24),inset 0 1px 0 rgba(255,255,255,.035)!important;
  color:#fff!important;
  text-decoration:none!important;
  transition:transform .2s ease,border-color .2s ease,background .2s ease!important;
}
body.ranked-accounts-page .type-card:hover{
  transform:translateY(-2px)!important;
  border-color:rgba(124,159,255,.24)!important;
}
body.ranked-accounts-page .type-card.is-active{
  border-color:rgba(124,159,255,.30)!important;
  background:radial-gradient(460px 180px at 0% 0%,rgba(79,110,247,.17),transparent 70%),linear-gradient(145deg,rgba(14,19,43,.98),rgba(8,11,28,.98))!important;
  box-shadow:0 18px 48px rgba(0,0,0,.30),inset 0 1px 0 rgba(255,255,255,.05)!important;
}
body.ranked-accounts-page .type-card__top{display:grid!important;grid-template-columns:46px minmax(0,1fr) auto!important;align-items:center!important;gap:13px!important;}
body.ranked-accounts-page .type-card__icon{
  width:46px!important;
  height:46px!important;
  min-width:46px!important;
  border-radius:14px!important;
  display:grid!important;
  place-items:center!important;
  background:rgba(255,255,255,.045)!important;
  border:1px solid rgba(255,255,255,.08)!important;
}
body.ranked-accounts-page .type-card__icon img{width:34px!important;height:34px!important;object-fit:contain!important;}
body.ranked-accounts-page .type-card__title{font-size:16px!important;font-weight:900!important;letter-spacing:-.015em!important;}
body.ranked-accounts-page .type-card__subtitle{margin-top:3px!important;color:rgba(227,231,255,.52)!important;font-size:11px!important;line-height:1.45!important;}
body.ranked-accounts-page .type-card__badge{
  padding:6px 9px!important;
  border-radius:999px!important;
  border:1px solid rgba(124,159,255,.18)!important;
  background:rgba(79,110,247,.10)!important;
  color:#aebdff!important;
  font-size:9px!important;
  font-weight:900!important;
  letter-spacing:.03em!important;
}
body.ranked-accounts-page .type-card__pills{display:flex!important;flex-wrap:wrap!important;gap:6px!important;margin:14px 0 0!important;}
body.ranked-accounts-page .type-pill{
  padding:6px 8px!important;
  border-radius:9px!important;
  border:1px solid rgba(255,255,255,.06)!important;
  background:rgba(255,255,255,.035)!important;
  color:rgba(231,235,255,.64)!important;
  font-size:10px!important;
  font-weight:700!important;
}
body.ranked-accounts-page .type-card__cta{margin-top:14px!important;color:#9fb0ff!important;font-size:11px!important;font-weight:900!important;}
body.ranked-accounts-page .shop-toolbar,
body.ranked-accounts-page .accounts-toolbar{
  color:rgba(235,238,255,.72)!important;
}
body.ranked-accounts-page .pagination{margin-top:28px!important;}
body.ranked-accounts-page .pagination a,
body.ranked-accounts-page .pagination span{
  border-color:rgba(255,255,255,.08)!important;
  background:rgba(255,255,255,.04)!important;
  color:rgba(242,244,255,.78)!important;
}
body.ranked-accounts-page .pagination .active,
body.ranked-accounts-page .pagination a:hover{
  background:linear-gradient(135deg,#4f6ef7,#3b58e8)!important;
  border-color:rgba(124,159,255,.32)!important;
  color:#fff!important;
}
@media(max-width:760px){
  body.ranked-accounts-page .lb-shop-hero__inner{width:calc(100% - 30px)!important;min-height:0!important;padding:24px 0 26px!important;display:grid!important;grid-template-columns:50px minmax(0,1fr)!important;gap:13px!important;}
  body.ranked-accounts-page .lb-shop-hero__icon{width:50px!important;height:50px!important;min-width:50px!important;border-radius:15px!important;}
  body.ranked-accounts-page .lb-shop-hero__icon i{font-size:21px!important;}
  body.ranked-accounts-page .lb-shop-hero__title{font-size:23px!important;}
  body.ranked-accounts-page .lb-shop-hero__desc{font-size:12.5px!important;line-height:1.5!important;margin-top:7px!important;}
  body.ranked-accounts-page>.container,
  body.ranked-accounts-page main>.container{width:calc(100% - 30px)!important;padding:20px 0 54px!important;}
  body.ranked-accounts-page .account-type-cards{grid-template-columns:1fr!important;gap:10px!important;}
  body.ranked-accounts-page .type-card{padding:15px!important;border-radius:17px!important;}
  body.ranked-accounts-page .type-card__top{grid-template-columns:42px minmax(0,1fr)!important;}
  body.ranked-accounts-page .type-card__badge{grid-column:2!important;justify-self:start!important;}
  body.ranked-accounts-page .type-card__pills{display:none!important;}
  body.ranked-accounts-page .type-card__cta{margin-top:10px!important;}
}
</style>

<style id="lb-shop-visual-correction-final">
/* Final visual correction, compact shop header, simplified account navigation, no oversized empty zones. */
body.ranked-accounts-page{
  --lb-shop-bg:#070815;
  --lb-shop-panel:#0d1021;
  --lb-shop-panel-2:#10142a;
  --lb-shop-border:rgba(142,160,255,.14);
  --lb-shop-border-strong:rgba(124,146,255,.30);
  --lb-shop-text:#f7f8ff;
  --lb-shop-muted:#8f97b5;
  --lb-shop-primary:#6366f1;
  background:#070815!important;
}
body.ranked-accounts-page .lb-shop-hero{
  background:linear-gradient(180deg,#0b0d1c 0%,#080916 100%)!important;
  border-bottom:1px solid rgba(255,255,255,.055)!important;
}
body.ranked-accounts-page .lb-shop-hero:before{display:none!important;}
body.ranked-accounts-page .lb-shop-hero__inner{
  width:min(1320px,calc(100% - 40px))!important;
  min-height:148px!important;
  padding:28px 0!important;
  gap:18px!important;
}
body.ranked-accounts-page .lb-shop-hero__icon{
  width:62px!important;height:62px!important;min-width:62px!important;border-radius:18px!important;
  background:#11152a!important;border:1px solid rgba(124,146,255,.18)!important;box-shadow:none!important;
}
body.ranked-accounts-page .lb-shop-hero__icon i{font-size:25px!important;color:#7f8cff!important;}
body.ranked-accounts-page .lb-shop-hero__title{font-size:32px!important;letter-spacing:-.025em!important;}
body.ranked-accounts-page .lb-shop-hero__desc{max-width:700px!important;margin-top:7px!important;font-size:14px!important;line-height:1.5!important;color:#9299b5!important;}
body.ranked-accounts-page>.container,
body.ranked-accounts-page main>.container{
  width:min(1320px,calc(100% - 40px))!important;
  max-width:1320px!important;
  padding:22px 0 64px!important;
}
body.ranked-accounts-page .account-type-cards{
  display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:12px!important;
  margin:0 0 16px!important;
}
body.ranked-accounts-page .type-card{
  min-height:76px!important;padding:15px 17px!important;border-radius:16px!important;
  display:flex!important;align-items:center!important;
  background:#0d1021!important;border:1px solid rgba(255,255,255,.07)!important;
  box-shadow:none!important;transform:none!important;
}
body.ranked-accounts-page .type-card:hover{background:#10142a!important;border-color:rgba(124,146,255,.24)!important;transform:none!important;}
body.ranked-accounts-page .type-card.is-active{background:#11162d!important;border-color:rgba(124,146,255,.34)!important;box-shadow:inset 3px 0 0 #6366f1!important;}
body.ranked-accounts-page .type-card__top{width:100%!important;display:grid!important;grid-template-columns:42px minmax(0,1fr) auto!important;gap:12px!important;align-items:center!important;}
body.ranked-accounts-page .type-card__icon{width:42px!important;height:42px!important;min-width:42px!important;border-radius:13px!important;background:#090b18!important;border:1px solid rgba(255,255,255,.08)!important;}
body.ranked-accounts-page .type-card__icon img{width:30px!important;height:30px!important;}
body.ranked-accounts-page .type-card__title{font-size:15px!important;font-weight:850!important;}
body.ranked-accounts-page .type-card__subtitle{font-size:11px!important;line-height:1.35!important;color:#858daa!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
body.ranked-accounts-page .type-card__badge{padding:5px 8px!important;font-size:9px!important;background:#151a34!important;border-color:rgba(124,146,255,.17)!important;color:#9eabff!important;}
body.ranked-accounts-page .type-card__pills,
body.ranked-accounts-page .type-card__cta{display:none!important;}
body.ranked-accounts-page #accountsTop{height:0!important;}
body.ranked-accounts-page #shopFilterbar.shop-filterbar{
  position:sticky!important;
  top:var(--lb-sticky-top,96px)!important;
  margin:0 0 18px!important;padding:9px!important;border-radius:17px!important;
  background:rgba(10,12,27,.94)!important;border:1px solid rgba(255,255,255,.075)!important;
  box-shadow:0 12px 34px rgba(0,0,0,.24)!important;backdrop-filter:blur(18px)!important;
}
body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{gap:8px!important;min-height:44px!important;}
body.ranked-accounts-page #shopFilterbar .shop-filterbar__search,
body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn,
body.ranked-accounts-page #shopFilterbar .shop-sort__btn{
  height:42px!important;border-radius:12px!important;background:#151827!important;border:1px solid rgba(255,255,255,.075)!important;box-shadow:none!important;
}
body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{flex:1 1 250px!important;min-width:220px!important;}
body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn{padding:0 13px!important;}
body.ranked-accounts-page #shopFilterbar .reset-filters{height:42px!important;padding:0 18px!important;border-radius:12px!important;background:#6366f1!important;box-shadow:none!important;}
}
body.ranked-accounts-page #activeFilters.shop-filterbar__chips:empty{display:none!important;}
body.ranked-accounts-page .shop-toolbar{margin:0 0 14px!important;}
@media(max-width:760px){
  body.ranked-accounts-page .lb-shop-hero__inner{width:calc(100% - 28px)!important;padding:20px 0!important;grid-template-columns:46px minmax(0,1fr)!important;}
  body.ranked-accounts-page .lb-shop-hero__icon{width:46px!important;height:46px!important;min-width:46px!important;border-radius:14px!important;}
  body.ranked-accounts-page .lb-shop-hero__title{font-size:21px!important;}
  body.ranked-accounts-page>.container,body.ranked-accounts-page main>.container{width:calc(100% - 24px)!important;padding-top:14px!important;}
  body.ranked-accounts-page .account-type-cards{grid-template-columns:1fr 1fr!important;gap:8px!important;}
  body.ranked-accounts-page .type-card{min-height:58px!important;padding:10px!important;}
  body.ranked-accounts-page .type-card__top{grid-template-columns:34px minmax(0,1fr)!important;gap:8px!important;}
  body.ranked-accounts-page .type-card__icon{width:34px!important;height:34px!important;min-width:34px!important;border-radius:10px!important;}
  body.ranked-accounts-page .type-card__icon img{width:24px!important;height:24px!important;}
  body.ranked-accounts-page .type-card__subtitle,body.ranked-accounts-page .type-card__badge{display:none!important;}
}
</style>


<style id="lb-account-shop-fullwidth-focus-v3">
/* Full width, card first shop layout, tuned for the site's 0.88 desktop zoom. */
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
  margin-bottom:0 !important;
  border-bottom:1px solid rgba(255,255,255,.04) !important;
}
html body.ranked-accounts-page .lb-shop-hero__inner{
  min-height:126px !important;
  padding:24px 0 18px !important;
  gap:18px !important;
}
html body.ranked-accounts-page .lb-shop-hero__icon{
  width:64px !important;
  height:64px !important;
  min-width:64px !important;
  border-radius:18px !important;
}
html body.ranked-accounts-page .lb-shop-hero__title{
  font-size:34px !important;
  letter-spacing:-.035em !important;
}
html body.ranked-accounts-page .lb-shop-hero__desc{
  max-width:980px !important;
  font-size:15px !important;
  white-space:normal !important;
  overflow:visible !important;
  text-overflow:clip !important;
}

html body.ranked-accounts-page .account-type-cards{
  display:grid !important;
  grid-template-columns:repeat(2, minmax(0, 1fr)) !important;
  gap:14px !important;
  margin:8px 0 14px !important;
}
html body.ranked-accounts-page .type-card{
  min-height:110px !important;
  padding:16px 18px !important;
  border-radius:18px !important;
  background:linear-gradient(180deg, rgba(17,20,37,.96) 0%, rgba(11,14,28,.98) 100%) !important;
  border:1px solid rgba(255,255,255,.07) !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page .type-card__top{
  grid-template-columns:42px minmax(0,1fr) auto !important;
  gap:12px !important;
  align-items:start !important;
}
html body.ranked-accounts-page .type-card__icon{
  width:42px !important;
  height:42px !important;
  min-width:42px !important;
  border-radius:13px !important;
}
html body.ranked-accounts-page .type-card__icon img{
  width:28px !important;
  height:28px !important;
}
html body.ranked-accounts-page .type-card__title{
  font-size:18px !important;
  font-weight:900 !important;
}
html body.ranked-accounts-page .type-card__subtitle{
  margin-top:3px !important;
  font-size:12.5px !important;
  line-height:1.45 !important;
  color:#8e96b2 !important;
}
html body.ranked-accounts-page .type-card__badge{
  align-self:start !important;
}
html body.ranked-accounts-page .type-card__pills{
  margin-top:10px !important;
  gap:6px !important;
}
html body.ranked-accounts-page .type-pill{
  min-height:28px !important;
  padding:4px 9px !important;
  border-radius:999px !important;
  font-size:11px !important;
  background:#151827 !important;
  border:1px solid rgba(255,255,255,.06) !important;
  color:#b0b7d0 !important;
}
html body.ranked-accounts-page .type-card__cta{
  margin-top:12px !important;
  padding-top:10px !important;
  font-size:13px !important;
}

html body.ranked-accounts-page #shopFilterbar.shop-filterbar{
  margin:0 0 16px !important;
  padding:14px !important;
  border-radius:20px !important;
  background:rgba(10,12,27,.92) !important;
  border:1px solid rgba(255,255,255,.07) !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
  gap:10px !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{
  flex:1 1 360px !important;
  min-width:320px !important;
  height:46px !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn,
html body.ranked-accounts-page #shopFilterbar .shop-sort__btn,
html body.ranked-accounts-page #shopFilterbar .reset-filters,
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{
  height:46px !important;
  border-radius:13px !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__actions{
  margin-left:auto !important;
  gap:10px !important;
}
html body.ranked-accounts-page #activeFilters.shop-filterbar__chips{
  margin-top:10px !important;
}

html body.ranked-accounts-page #accountsGrid.accounts-grid{
  display:grid !important;
  grid-template-columns:repeat(5, minmax(0, 1fr)) !important;
  gap:18px !important;
  align-items:stretch !important;
  margin-top:0 !important;
}
html body.ranked-accounts-page #accountsGrid .account-card{
  height:100% !important;
  min-width:0 !important;
  border-radius:18px !important;
  background:linear-gradient(180deg, rgba(14,17,33,.98) 0%, rgba(10,12,24,.99) 100%) !important;
  border:1px solid rgba(255,255,255,.075) !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page #accountsGrid .account-card:hover{
  transform:translateY(-2px) !important;
  border-color:rgba(99,102,241,.28) !important;
}
html body.ranked-accounts-page #accountsGrid .account-card > .cover-link{
  padding:16px 16px 0 !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .title{
  min-height:42px !important;
  padding-right:78px !important;
  font-size:16px !important;
  line-height:1.25 !important;
  margin-bottom:6px !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .account-card-title-text{
  font-size:16px !important;
  line-height:1.25 !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .excerpt{
  min-height:40px !important;
  max-height:40px !important;
  margin:0 0 12px !important;
  font-size:12px !important;
  line-height:1.5 !important;
  color:#9aa3c0 !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .image-box{
  height:200px !important;
  min-height:200px !important;
  max-height:200px !important;
  border-radius:14px !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .highlights{
  display:grid !important;
  grid-template-columns:repeat(3, minmax(0, 1fr)) !important;
  gap:6px !important;
  margin:12px 0 0 !important;
  min-height:68px !important;
  max-height:none !important;
  overflow:visible !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .highlights .badge{
  min-width:0 !important;
  min-height:31px !important;
  padding:6px 7px !important;
  border-radius:10px !important;
  font-size:10.5px !important;
  justify-content:flex-start !important;
  background:#151827 !important;
  border:1px solid rgba(255,255,255,.065) !important;
  color:#c0c7dc !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .totals{
  margin-top:auto !important;
  min-height:66px !important;
  padding:13px 0 !important;
  border-top:1px solid rgba(255,255,255,.06) !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .totals .price-eur{
  font-size:21px !important;
  letter-spacing:-.02em !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .totals .btn.primary{
  min-width:116px !important;
  height:40px !important;
  padding:0 14px !important;
  border-radius:11px !important;
  font-size:12.5px !important;
  background:linear-gradient(135deg,#4f6ef7 0%,#6366f1 100%) !important;
}
html body.ranked-accounts-page #accountsGrid .account-card > .seller-info,
html body.ranked-accounts-page #accountsGrid .account-card > .lb-seller-footer{
  min-height:56px !important;
  margin-top:0 !important;
  padding:10px 16px !important;
  border-radius:0 0 18px 18px !important;
  background:#101322 !important;
  border-top:1px solid rgba(255,255,255,.06) !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .delivery-type{
  top:16px !important;
  right:16px !important;
}
html body.ranked-accounts-page #accountsGrid .account-card .delivery-type.account-card__recommended-icon{
  right:54px !important;
}
html body.ranked-accounts-page #shopPagination.shop-pagination{
  width:100% !important;
  max-width:none !important;
}

@media (max-width: 1700px){
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(4, minmax(0, 1fr)) !important;
  }
}
@media (max-width: 1320px){
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(3, minmax(0, 1fr)) !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{
    min-width:260px !important;
  }
}
@media (max-width: 980px){
  html body.ranked-accounts-page .account-type-cards{
    grid-template-columns:1fr !important;
  }
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(2, minmax(0, 1fr)) !important;
  }
}
@media (max-width: 767px){
  html body.ranked-accounts-page .lb-shop-hero__inner,
  html body.ranked-accounts-page > .container,
  html body.ranked-accounts-page main > .container,
  html body.ranked-accounts-page .container{
    width:calc(100% - 24px) !important;
    max-width:calc(100% - 24px) !important;
  }
  html body.ranked-accounts-page .lb-shop-hero__inner{
    min-height:0 !important;
    padding:16px 0 14px !important;
    grid-template-columns:42px minmax(0,1fr) !important;
  }
  html body.ranked-accounts-page .lb-shop-hero__title{
    font-size:20px !important;
  }
  html body.ranked-accounts-page .account-type-cards{
    grid-template-columns:1fr 1fr !important;
    gap:8px !important;
    margin:6px 0 12px !important;
  }
  html body.ranked-accounts-page .type-card{
    min-height:68px !important;
    padding:10px !important;
  }
  html body.ranked-accounts-page .type-card__top{
    grid-template-columns:34px minmax(0,1fr) !important;
    gap:8px !important;
  }
  html body.ranked-accounts-page .type-card__subtitle,
  html body.ranked-accounts-page .type-card__badge,
  html body.ranked-accounts-page .type-card__pills{
    display:none !important;
  }
  html body.ranked-accounts-page .type-card__cta{
    margin-top:8px !important;
    padding-top:8px !important;
  }
  html body.ranked-accounts-page #shopFilterbar.shop-filterbar{
    padding:10px !important;
    border-radius:16px !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search,
  html body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn,
  html body.ranked-accounts-page #shopFilterbar .shop-sort__btn,
  html body.ranked-accounts-page #shopFilterbar .reset-filters{
    height:42px !important;
  }
  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search{
    min-width:0 !important;
  }
  html body.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:1fr !important;
    gap:14px !important;
  }
  html body.ranked-accounts-page #accountsGrid .account-card .image-box{
    height:188px !important;
    min-height:188px !important;
    max-height:188px !important;
  }
}
</style>


<style id="lb-filterbar-cleanup-v4">
/* Remove nested panel look, keep only one outer filter surface. */
html body.ranked-accounts-page #shopFilterbar.shop-filterbar{
  padding:10px 12px !important;
  background:#0b0e1c !important;
  border:1px solid rgba(255,255,255,.07) !important;
  border-radius:18px !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__form,
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
  background:transparent !important;
  border:0 !important;
  border-radius:0 !important;
  box-shadow:none !important;
  outline:0 !important;
  padding:0 !important;
  margin:0 !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
  min-height:44px !important;
  gap:8px !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search,
html body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn,
html body.ranked-accounts-page #shopFilterbar .shop-sort__btn,
html body.ranked-accounts-page #shopFilterbar .reset-filters{
  height:44px !important;
  background:#151827 !important;
  border:1px solid rgba(255,255,255,.075) !important;
  border-radius:11px !important;
  box-shadow:none !important;
}
html body.ranked-accounts-page #shopFilterbar .shop-filterbar__search:focus-within,
html body.ranked-accounts-page #shopFilterbar .shop-filterpill__btn:hover,
html body.ranked-accounts-page #shopFilterbar .shop-sort__btn:hover{
  border-color:rgba(99,102,241,.34) !important;
  background:#181c2e !important;
}
html body.ranked-accounts-page #shopFilterbar .reset-filters{
  background:#6366f1 !important;
  border-color:#6366f1 !important;
}

html body.ranked-accounts-page #activeFilters.shop-filterbar__chips:empty{
  display:none !important;
}
html body.ranked-accounts-page .popular-searches,
html body.ranked-accounts-page .shop-popular-searches{
  margin-top:10px !important;
}
</style>


<style id="lb-shop-readable-type-v7">
/* Readability pass for the global desktop zoom of 0.88. */
@media (min-width:768px){
  html body.ranked-accounts-page .lb-shop-hero__kicker{font-size:13.5px!important;}
  html body.ranked-accounts-page .lb-shop-hero__title{font-size:38px!important;line-height:1.08!important;}
  html body.ranked-accounts-page .lb-shop-hero__desc{font-size:17px!important;line-height:1.55!important;max-width:1040px!important;}

  html body.ranked-accounts-page .type-card__title{font-size:19px!important;line-height:1.25!important;}
  html body.ranked-accounts-page .type-card__subtitle{font-size:14px!important;line-height:1.45!important;}
  html body.ranked-accounts-page .type-card__badge{font-size:11.5px!important;}
  html body.ranked-accounts-page .type-pill{font-size:12px!important;}
  html body.ranked-accounts-page .type-card__cta{font-size:14px!important;}

  html body.ranked-accounts-page .popular-searches,
  html body.ranked-accounts-page .shop-popular-searches,
  html body.ranked-accounts-page .popular-searches__label,
  html body.ranked-accounts-page .shop-popular-searches__label{font-size:14px!important;}
  html body.ranked-accounts-page .popular-searches a,
  html body.ranked-accounts-page .popular-searches button,
  html body.ranked-accounts-page .shop-popular-searches a,
  html body.ranked-accounts-page .shop-popular-searches button,
  html body.ranked-accounts-page .popular-pill{font-size:13.5px!important;min-height:32px!important;padding:0 13px!important;}

  html body.ranked-accounts-page .accounts-count,
  html body.ranked-accounts-page .shop-results-count,
  html body.ranked-accounts-page [class*="account-count"]{font-size:15px!important;}
}
</style>

<style id="lb-account-type-buttons-centered-v8">
/* Center the Smurf / Ranked account selection cards. */
body.ranked-accounts-page .account-type-cards .type-card{
  position:relative !important;
  display:flex !important;
  flex-direction:column !important;
  align-items:center !important;
  justify-content:center !important;
  text-align:center !important;
}
body.ranked-accounts-page .account-type-cards .type-card__top{
  width:100% !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:12px !important;
  text-align:left !important;
}
body.ranked-accounts-page .account-type-cards .type-card__titles{
  flex:0 1 auto !important;
  min-width:0 !important;
}
body.ranked-accounts-page .account-type-cards .type-card__badge{
  position:absolute !important;
  top:14px !important;
  right:14px !important;
  margin:0 !important;
}
@media(max-width:767px){
  body.ranked-accounts-page .account-type-cards .type-card__top{
    gap:8px !important;
  }
  body.ranked-accounts-page .account-type-cards .type-card__badge{
    display:none !important;
  }
}
</style>


<style id="lb-account-type-row-centered-over-filter-v9">
/* Keep the two account type cards centered as one row above the full width filter bar. */
@media (min-width:768px){
  html body.ranked-accounts-page .account-type-cards{
    width:min(1120px, 100%) !important;
    max-width:1120px !important;
    margin-left:auto !important;
    margin-right:auto !important;
  }
}
@media (max-width:767px){
  html body.ranked-accounts-page .account-type-cards{
    width:100% !important;
    max-width:none !important;
    margin-left:0 !important;
    margin-right:0 !important;
  }
}
</style>

<style id="lb-account-type-icon-buttons-v10">
/* Compact account type selector, icon above label, content width only. */
html body.ranked-accounts-page .account-type-cards{
  display:flex !important;
  align-items:stretch !important;
  justify-content:center !important;
  flex-wrap:wrap !important;
  gap:14px !important;
  width:max-content !important;
  max-width:100% !important;
  margin:16px auto 18px !important;
}

html body.ranked-accounts-page .account-type-cards .type-card{
  position:relative !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  width:158px !important;
  min-width:158px !important;
  max-width:158px !important;
  min-height:126px !important;
  padding:18px 16px !important;
  border-radius:18px !important;
  text-align:center !important;
  background:linear-gradient(180deg,rgba(15,18,36,.98),rgba(10,13,27,.98)) !important;
  border:1px solid rgba(255,255,255,.085) !important;
  box-shadow:none !important;
  overflow:hidden !important;
}

html body.ranked-accounts-page .account-type-cards .type-card:hover{
  transform:translateY(-2px) !important;
  border-color:rgba(99,102,241,.38) !important;
  background:linear-gradient(180deg,rgba(20,24,47,.98),rgba(12,15,31,.98)) !important;
}

html body.ranked-accounts-page .account-type-cards .type-card.is-active{
  border-color:rgba(99,102,241,.78) !important;
  background:linear-gradient(180deg,rgba(31,37,78,.96),rgba(18,22,48,.98)) !important;
  box-shadow:0 0 0 1px rgba(99,102,241,.22),0 14px 34px rgba(30,35,90,.24) !important;
}

html body.ranked-accounts-page .account-type-cards .type-card__top{
  display:flex !important;
  flex-direction:column !important;
  align-items:center !important;
  justify-content:center !important;
  gap:11px !important;
  width:100% !important;
  margin:0 !important;
}

html body.ranked-accounts-page .account-type-cards .type-card__icon{
  display:grid !important;
  place-items:center !important;
  width:58px !important;
  height:58px !important;
  min-width:58px !important;
  min-height:58px !important;
  margin:0 !important;
  border-radius:17px !important;
  background:rgba(7,9,20,.72) !important;
  border:1px solid rgba(255,255,255,.09) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.035) !important;
}

html body.ranked-accounts-page .account-type-cards .type-card.is-active .type-card__icon{
  background:rgba(99,102,241,.11) !important;
  border-color:rgba(99,102,241,.28) !important;
}

html body.ranked-accounts-page .account-type-cards .type-card__icon img{
  width:42px !important;
  height:42px !important;
  max-width:42px !important;
  max-height:42px !important;
  object-fit:contain !important;
  border-radius:12px !important;
}

html body.ranked-accounts-page .account-type-cards .type-card__titles{
  display:block !important;
  width:100% !important;
  min-width:0 !important;
  text-align:center !important;
}

html body.ranked-accounts-page .account-type-cards .type-card__title{
  margin:0 !important;
  font-size:16px !important;
  line-height:1.2 !important;
  font-weight:900 !important;
  color:#fff !important;
  white-space:nowrap !important;
}

html body.ranked-accounts-page .account-type-cards .type-card__subtitle,
html body.ranked-accounts-page .account-type-cards .type-card__badge,
html body.ranked-accounts-page .account-type-cards .type-card__pills,
html body.ranked-accounts-page .account-type-cards .type-card__cta{
  display:none !important;
}

@media(max-width:767px){
  html body.ranked-accounts-page .account-type-cards{
    width:100% !important;
    flex-wrap:nowrap !important;
    gap:10px !important;
    margin:12px auto 14px !important;
  }

  html body.ranked-accounts-page .account-type-cards .type-card{
    flex:1 1 0 !important;
    width:auto !important;
    min-width:0 !important;
    max-width:none !important;
    min-height:108px !important;
    padding:14px 10px !important;
    border-radius:16px !important;
  }

  html body.ranked-accounts-page .account-type-cards .type-card__icon{
    width:50px !important;
    height:50px !important;
    min-width:50px !important;
    min-height:50px !important;
    border-radius:15px !important;
  }

  html body.ranked-accounts-page .account-type-cards .type-card__icon img{
    width:36px !important;
    height:36px !important;
  }

  html body.ranked-accounts-page .account-type-cards .type-card__title{
    font-size:14px !important;
  }
}
</style>

<style id="lb-shop-hero-spacing-v11">
/* Give the shop intro more breathing room below the game navigation. */
@media (min-width:768px){
  html body.ranked-accounts-page .lb-shop-hero__inner{
    min-height:150px !important;
    padding-top:42px !important;
    padding-bottom:28px !important;
  }
}
@media (max-width:767px){
  html body.ranked-accounts-page .lb-shop-hero__inner{
    padding-top:24px !important;
    padding-bottom:18px !important;
  }
}
</style>


<style id="lb-shop-bottom-spacing-v12">
/* More breathing room below the account grid and below the bottom popular filters. */
html body.ranked-accounts-page #shopPagination.shop-pagination{
  margin-top:42px !important;
  margin-bottom:0 !important;
  padding-bottom:0 !important;
}
html body.ranked-accounts-page #shopPagination .page-bar{
  margin-left:auto !important;
  margin-right:auto !important;
}
html body.ranked-accounts-page #lbPopularSearchesBottom.lb-popular-searches--bottom{
  margin-top:28px !important;
  margin-bottom:72px !important;
  padding-bottom:0 !important;
}
@media(max-width:767px){
  html body.ranked-accounts-page #shopPagination.shop-pagination{
    margin-top:30px !important;
  }
  html body.ranked-accounts-page #lbPopularSearchesBottom.lb-popular-searches--bottom{
    margin-top:22px !important;
    margin-bottom:96px !important;
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
