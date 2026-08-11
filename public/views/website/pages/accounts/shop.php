<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'ranked-accounts-page' ]) ?>
<header>
    <div class="content">
        <h1><?= t('LoL Ranked Accounts') ?></h1>

       <p><?= t('Buy ranked premium League of Legends accounts with champions, skins, and included email access. Fast delivery, secure login details, and a minimum 14-day warranty—start playing instantly.') ?></p>
    </div>
</header>
<div class="container">
    <div class="account-type-cards" role="navigation" aria-label="Account type">
        <a href="/lol/premium-accounts" class="type-card">
            <div class="type-card__top">
                <div class="type-card__icon" aria-hidden="true"><img src="/public/uploads/icons/default2.png" alt="" style="width:32px;height:32px;border-radius:999px;display:block;"></div>
                <div class="type-card__titles">
                    <div class="type-card__title">Smurf Accounts</div>
                    <div class="type-card__subtitle">Fresh starts. Fast delivery. Hand-picked for quick climbs.</div>
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

        <a href="/lol/accounts" class="type-card is-active" aria-current="page">
            <div class="type-card__top">
                <div class="type-card__icon" aria-hidden="true"><img src="/public/uploads/icons/challenger.png" alt="" style="width:32px;height:32px;border-radius:999px;display:block;"></div>
                <div class="type-card__titles">
                    <div class="type-card__title">Ranked Accounts</div>
                    <div class="type-card__subtitle">Skip the grind. Choose your rank &amp; start playing today.</div>
                </div>
                <span class="type-card__badge type-card__badge--popular">Most popular</span>
            </div>
            
            <div class="type-card__pills">
                <span class="type-pill">Verified rank &amp; region</span>
                <span class="type-pill">Champions &amp; skins included</span>
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
            <input type="hidden" name="game" value="<?= htmlspecialchars($game ?? 'lol') ?>">

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

                                <?= util_load_server_select() ?>
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
                                // 0 = Unranked ... 10 = Challenger
                                $rankLabels = [
                                    0 => 'Unranked',
                                    1 => 'Iron',
                                    2 => 'Bronze',
                                    3 => 'Silver',
                                    4 => 'Gold',
                                    5 => 'Platinum',
                                    6 => 'Emerald',
                                    7 => 'Diamond',
                                    8 => 'Master',
                                    9 => 'Grandmaster',
                                    10 => 'Challenger',
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
                <?php
                $roles = ['TopLane','Jungle','MidLane','AdCarry','Support'];
                foreach ($roles as $role): ?>
                    <option value="<?= $role ?>"><?= $role ?></option>
                <?php endforeach; ?>
            </select>
            <div class="facet-list" data-facet="filterRoles"></div>

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

                                    <button type="button" class="mf-menuitem" data-mf-open="champions">
                                        <span class="mf-menuitem__left"><i class="fa-solid fa-helmet-battle"></i></span>
                                        <span class="mf-menuitem__label"><?= t('Champions') ?></span>
                                        <span class="mf-menuitem__right">›</span>
                                    </button>

                                    <button type="button" class="mf-menuitem" data-mf-open="skins">
                                        <span class="mf-menuitem__left"><i class="fa-solid fa-masks-theater"></i></span>
                                        <span class="mf-menuitem__label"><?= t('Skins') ?></span>
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

                            <!-- VIEW: CHAMPIONS (UI only; hook up later) -->
                            <div class="mf-view" data-view="champions">
                                <div class="mf-panelhead">
                                    <button type="button" class="mf-back" data-mf-back aria-label="Back">←</button>
                                    <div class="mf-title"><?= t('Champions') ?></div>
                                </div>
                                <div class="mf-panelbody">
                                    <div class="mf-empty"><?= t('Coming soon') ?></div>
                                </div>
                            </div>

                            <!-- VIEW: SKINS (UI only; hook up later) -->
                            <div class="mf-view" data-view="skins">
                                <div class="mf-panelhead">
                                    <button type="button" class="mf-back" data-mf-back aria-label="Back">←</button>
                                    <div class="mf-title"><?= t('Skins') ?></div>
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
</div>

<style id="lb-desktop-shop-filter-nav">
@media (min-width:1025px){
  body.ranked-accounts-page nav.navbar-top{
    transition:transform .24s ease,opacity .18s ease!important;
    will-change:transform,opacity;
  }

  /* The bar stays in the document until it reaches the desktop header. */
  body.ranked-accounts-page #shopFilterbar.shop-filterbar--sticky{
    position:relative!important;
    top:auto!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active nav.navbar-top{
    transform:translateY(calc(-100% - var(--lb-sale-h,0px)))!important;
    opacity:0!important;
    pointer-events:none!important;
  }

  /* Once inside the results, the filters become the only top navigation. */
  body.ranked-accounts-page.lb-shop-filter-nav-active #shopFilterbar{
    position:fixed!important;
    inset:0 0 auto 0!important;
    width:100%!important;
    max-width:none!important;
    margin:0!important;
    padding:11px clamp(24px,4vw,76px)!important;
    border:0!important;
    border-bottom:1px solid rgba(255,255,255,.09)!important;
    border-radius:0!important;
    background:rgba(5,8,20,.97)!important;
    box-shadow:0 14px 38px rgba(0,0,0,.34)!important;
    backdrop-filter:blur(18px)!important;
    -webkit-backdrop-filter:blur(18px)!important;
    z-index:100050!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #shopFilterbar .shop-filterbar__form{
    width:min(1760px,100%)!important;
    margin:0 auto!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #shopFilterbar .shop-filterbar__row{
    flex-wrap:nowrap!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #activeFilters:has(.active-filters__hint){
    display:none!important;
  }

  /* Replaces the height removed when the filterbar switches to fixed. */
  body.ranked-accounts-page.lb-shop-filter-nav-active #accountsTop{
    height:var(--lb-shop-filter-height,70px)!important;
  }
}
</style>

<?= $this->start('scripts') ?>
<script>
$(document).ready(function () {
  // Desktop shop mode: after the filterbar reaches the header, it replaces the
  // global navbar until the visitor scrolls back above the results.
  (function initDesktopShopFilterNav(){
    const body = document.body;
    const bar = document.getElementById('shopFilterbar');
    const nav = document.querySelector('nav.navbar-top');
    const desktop = window.matchMedia('(min-width:1025px)');
    if (!body || !bar || !nav) return;

    let triggerY = 0;
    let active = false;
    let ticking = false;

    function setActive(next){
      if (active === next) return;
      active = next;
      body.classList.toggle('lb-shop-filter-nav-active', active);
      if (!active) document.documentElement.style.removeProperty('--lb-shop-filter-height');
    }

    function measure(){
      if (!desktop.matches) {
        setActive(false);
        return;
      }

      const wasActive = active;
      if (wasActive) body.classList.remove('lb-shop-filter-nav-active');

      const listings = document.getElementById('accountsGrid');
      const anchor = listings || bar;
      const barTop = anchor.getBoundingClientRect().top + window.scrollY;
      const navHeight = Math.max(0, nav.getBoundingClientRect().height || nav.offsetHeight || 0);
      triggerY = Math.max(0, barTop - navHeight);
      document.documentElement.style.setProperty('--lb-shop-filter-height', Math.ceil(bar.offsetHeight) + 'px');

      if (wasActive) body.classList.add('lb-shop-filter-nav-active');
    }

    function update(){
      ticking = false;
      if (!desktop.matches) {
        setActive(false);
        return;
      }
      setActive(window.scrollY >= triggerY);
    }

    function requestUpdate(){
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    }

    measure();
    update();
    window.addEventListener('scroll', requestUpdate, {passive:true});
    window.addEventListener('resize', function(){
      measure();
      requestUpdate();
    }, {passive:true});
    if (desktop.addEventListener) desktop.addEventListener('change', function(){
      measure();
      requestUpdate();
    });
  })();

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

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (m) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);
    });
  }

  // ---------- Facet lists (checkbox list like your screenshot) ----------
  const ICONS = {
    // servers (match option values if possible)
    'NA': 'fa-solid fa-flag-usa',
    'EUW': 'fa-solid fa-globe',
    'EUNE': 'fa-solid fa-globe',
    'BR': 'fa-solid fa-flag',
    'TR': 'fa-solid fa-globe',
    'RU': 'fa-solid fa-globe',
    'LAN': 'fa-solid fa-globe',
    'LAS': 'fa-solid fa-globe',
    'ME': 'fa-solid fa-globe',
    // roles
    'TopLane': 'fa-solid fa-shield-halved',
    'Jungle': 'fa-solid fa-tree',
    'MidLane': 'fa-solid fa-hat-wizard',
    'AdCarry': 'fa-solid fa-crosshairs',
    'Support': 'fa-solid fa-hand-holding-heart',
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
  'EUW': 'gb',
  'EU-WEST': 'gb',
  'EUNE': 'pl',
  'EU-NORDIC & EAST': 'pl',
  'NA': 'us',
  'NORTH AMERICA': 'us',
  'BR': 'br',
  'BRAZIL': 'br',
  'LAN': 'mx',
  'LATIN AMERICA NORTH': 'mx',
  'LAS': 'ar',
  'LATIN AMERICA SOUTH': 'ar',
  'OCE': 'au',
  'OCEANIA': 'au',
  'RU': 'ru',
  'RUSSIA': 'ru',
  'TR': 'tr',
  'TURKEY': 'tr',
  'JP': 'jp',
  'JAPAN': 'jp',
  'KR': 'kr',
  'KOREA': 'kr'
};

const isRank = (facetName === 'ranks[]' && /^\d+$/.test(String(val)));
const isServer = (facetName === 'servers[]');
const isRole = (facetName === 'roles[]');

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
  ? `<img class="facet-item__rank" src="${__ASSET_BASE__}/core/main/img/lol/ranks/mini/${escapeHtml(val)}.png" alt="">`
  : (isRole)
    ? `<img class="facet-item__role" src="${__ASSET_BASE__}/core/main/img/lol/roles/${escapeHtml(val)}.png" alt="">`
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
      roles: [],
      delivery: [],
      price_min: parseFloat($pMin.val() || 0),
      price_max: parseFloat($pMax.val() || priceLimitMax),
      sort: sortMode || 'recommended'
    };
    $('input[name="servers[]"]:checked').each(function(){ state.servers.push($(this).closest('.facet-item').find('.facet-item__text').text().trim() || $(this).val()); });
    $('input[name="ranks[]"]:checked').each(function(){ state.ranks.push($(this).closest('.facet-item').find('.facet-item__text').text().trim() || $(this).val()); });
    $('input[name="roles[]"]:checked').each(function(){ state.roles.push($(this).val()); });
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
    state.roles.forEach(v => params.append('role', v));
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
    const roles   = params.getAll('role');
    const deliv   = params.getAll('delivery');

    if (servers.length) matchFacetByTextOrValue('servers[]', servers);
    if (ranks.length) matchFacetByTextOrValue('ranks[]', ranks);
    if (roles.length) matchFacetByTextOrValue('roles[]', roles);
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
      ranks:   `<i class="fa-solid fa-shield shop-filterpill__iconfa" aria-hidden="true"></i>`,
      roles:   `<i class="fa-solid fa-asterisk shop-filterpill__iconfa" aria-hidden="true"></i>`
    };

    const grouped = { servers: [], ranks: [], roles: [] };

    $('input[type="checkbox"][name$="[]"]:checked').each(function(){
      const name = $(this).attr('name');
      const val = $(this).val();
      const txt = $(this).closest('.facet-item').find('.facet-item__text').text().trim() || val;

      if (name === 'servers[]') { grouped.servers.push({ val, txt }); return; }
      if (name === 'ranks[]') { grouped.ranks.push({ val, txt }); return; }
      if (name === 'roles[]') { grouped.roles.push({ val, txt }); return; }

      // everything else stays as-is (single chip)
      const group = name.replace('[]','');
      let icon = '';
      if (name === 'ranks[]' && /^[0-9]+$/.test(String(val))) {
        icon = `<img src="${__ASSET_BASE__}/core/main/img/lol/ranks/mini/${escapeHtml(val)}.png" alt="">`;
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
    pushGroupedChip('roles', 'Roles');
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
      if (g === 'roles') $('input[type="checkbox"][name="roles[]"]').prop('checked', false);
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
    const roles = $('input[name="roles[]"]:checked').length;
    const deliv = $('input[name="delivery_type[]"]:checked').length;

    $('#valServer').text(servers ? '(' + servers + ')' : '');
    $('#valRank').text(ranks ? '(' + ranks + ')' : '');
    $('#valMore').text((roles + deliv) ? '(' + (roles + deliv) + ')' : '');

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
    const maxBtns = 7;
    const btns = [];
    const push = (p, label, cls='') => {
      btns.push(`<button type="button" class="page-btn ${cls}" data-page="${p}">${label}</button>`);
    };

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
  const base = $('#shopFilters').serialize();
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

  // initial UI
  applyStateFromUrl();
  updateCount();
  renderActiveFilters();
  updatePillSummaries();
  renderPagination();
  // initial fetch reflects URL state (and sets dynamic price limits)
  fetchAccounts({ page: currentPage || 1 });
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
            var username = (el.getAttribute('data-seller-name') || '').trim();
            if (!username) {
                var nameTextEl = el.querySelector('.seller-info__name-text');
                username = nameTextEl ? nameTextEl.textContent.trim() : '';
            }
            username = username.replace(/[✔✓]/g, '').trim();
            if (!username) return;

            el.style.cursor = 'pointer';

            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = '/sellers/' + encodeURIComponent(username);
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

