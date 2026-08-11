<style>
  /* Anchor offset for mobile/desktop fixed header stack */
  #valPageTop, .rank-types-nav {
      scroll-margin-top: var(--lb-content-top, 96px);
  }
</style>
<?php
// Valorant page in NEW (LoL) boost layout

$ranks = [
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
?>

<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'lol-boost lb-boost-nav-only']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.css">
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<style>
  /* Keep LoL theme scopes active */
  .duo-option { display: none; }

  /* Fix: cs-dropdown must appear above sticky-overview. */
  .lol-boost .sticky-overview { z-index: 99 !important; }

  /* Hide the Tawk launcher on mobile while the sticky bottom overview is active. */
  @media (max-width: 1024px) {
    body.lb-sticky-overview-active #tawkchat-container,
    body.lb-sticky-overview-active .tawk-min-container,
    body.lb-sticky-overview-active iframe[src*="tawk.to"],
    body.lb-sticky-overview-active iframe[title*="chat" i] {
      display: none !important;
      visibility: hidden !important;
      opacity: 0 !important;
      pointer-events: none !important;
    }
  }
</style>
<?= $this->end('styles') ?>

<div id="valPageTop" style="height:1px;position:relative;"></div>
<div class="lb-boost-breadcrumbs-wrap">
    <?= $this->insert('website/components/marketplace-breadcrumbs', [
        'type' => 'boosting',
        'gameSlug' => 'val',
        'gameName' => 'Valorant',
        'currentTitle' => (string)($data['name_long'] ?? $data['name'] ?? ''),
    ]) ?>
</div>
<div class="rank-types-nav">
    <a href="/val/rank-boost" class="nav-item <?= ($data['id'] ?? null) == 5 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/rank-boost.svg" alt="rank-boost-icon">
        <span><?= t('Rank') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/val/win-boost" class="nav-item <?= ($data['id'] ?? null) == 6 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/win-boost.svg" alt="win-boost-icon">
        <span><?= t('Win') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/val/placements-boost" class="nav-item <?= ($data['id'] ?? null) == 7 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/placement-boost.svg" alt="placement-boost-icon">
        <span><?= t('Placement') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/val/unrated-matches" class="nav-item <?= ($data['id'] ?? null) == 8 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/normal-matches.svg" alt="unrated-matches-icon">
        <span><?= t('Unrated') ?><br><?= t('Matches') ?></span>
    </a>
    <a href="/val/coaching" class="nav-item <?= ($data['id'] ?? null) == 9 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" alt="expert-coaching-icon">
        <span><?= t('Expert') ?><br><?= t('Coaching') ?></span>
    </a>
</div>

<form class="boost-form" id="val_boost_form" action="<?= AJAX_URL ?>" autocomplete="off">
    <input type="hidden" name="action" value="get_boost_price">
    <input type="hidden" name="form_id" value="<?= $data['id'] ?>">
    <input type="hidden" name="uuid" value="<?= $data['uuid'] ?>">

    <div class="form-content">
        <div class="left">
            <div class="boost-form">
                <?php $this->insert('website/components/forms/val/' . $data['type'], ['data' => $data, 'ranks' => $ranks]) ?>
            </div>

            <div class="boost-faqs">
                <h4><?= t('Frequently Asked Questions 🤔') ?></h4>
                <?php
                // Your val FAQs are stored under website/components/faqs/forms/val/
                // Expecting files: rank.php, win.php, placement.php, normal.php, coaching.php
                $this->insert('website/components/faqs/forms/val/' . $data['type'], ['data' => $data, 'ranks' => $ranks]);
                ?>
            </div>
        </div>

        <div class="right">
            <?php $this->insert('website/components/forms/order-summary-val', ['data' => $data]) ?>

            <div class="boost-faqs-mobile">
                <h4><?= t('Frequently Asked Questions 🤔') ?></h4>
                <?php $this->insert('website/components/faqs/forms/val/' . ($data['type'] ?? 'rank'), ['data' => $data, 'ranks' => $ranks]); ?>
            </div>
        </div>
    </div>
</form>


<!-- Agents modal (Valorant) — new design -->
<input type="hidden" name="agents" id="agents_input" value="" form="val_boost_form">

<div class="modal lb-modal--champs-roles" id="agents_modal" aria-hidden="true">

    <div class="lb-cr-header">
        <div class="lb-cr-header__title">
            <h4><?= t('Agent Selection') ?></h4>
        </div>
        <button class="lb-cr-close close-modal cancel-agents" type="button" aria-label="<?= t('Close') ?>">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="lb-cr-body">
        <div class="lb-cr-section lb-cr-section--champs">
            <div class="lb-cr-section__head">
                <h5 class="lb-cr-section__title"><?= t('Select Agents') ?></h5>
                <span class="lb-cr-badge lb-cr-badge--free lb-agent-free-badge"><?= t('Free') ?></span>
            </div>

            <div class="lb-cr-champ-search-wrap">
                <i class="fas fa-search lb-cr-search-icon"></i>
                <input type="text" class="lb-cr-champ-search" id="lb_agent_search" placeholder="<?= t('Search...') ?>" autocomplete="off">
            </div>

            <div class="lb-cr-champ-grid lb-agent-grid" id="lb_agent_grid"></div>

            <style>
            /* Desktop — 6 cols, fixed icon size */
            .lb-agent-grid {
                grid-template-columns: repeat(6, 1fr) !important;
                gap: 8px !important;
                max-height: 340px !important;
                overflow-y: auto !important;
            }
            .lb-agent-grid .lb-cr-champ-item {
                max-width: 80px !important;
            }
            .lb-agent-grid .lb-cr-champ-label {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 4px !important;
            }
            .lb-agent-grid .lb-cr-champ-label img {
                border-radius: 50% !important;
                width: 100% !important;
                max-width: 80px !important;
                aspect-ratio: 1 !important;
                object-fit: cover !important;
            }
            .lb-agent-grid .lb-cr-champ-name {
                font-size: 0.6rem !important;
            }
            /* Mobile */
            @media (max-width: 768px) {
                #agents_modal.lb-modal--champs-roles {
                    width: 100vw !important;
                    height: 100dvh !important;
                    max-height: 100dvh !important;
                    border-radius: 0 !important;
                    top: 0 !important;
                    left: 0 !important;
                    transform: translate(0,0) scale(1) !important;
                    display: flex !important;
                    flex-direction: column !important;
                    overflow: hidden !important;
                }
                #agents_modal.lb-modal--champs-roles.show {
                    transform: translate(0,0) scale(1) !important;
                }
                #agents_modal .lb-cr-body {
                    flex: 1 !important;
                    overflow-y: auto !important;
                    min-height: 0 !important;
                }
                #agents_modal .lb-cr-footer {
                    flex-shrink: 0 !important;
                }
                .lb-agent-grid {
                    grid-template-columns: repeat(4, 1fr) !important;
                    gap: 10px !important;
                    max-height: none !important;
                    overflow-y: visible !important;
                }
                .lb-agent-grid .lb-cr-champ-name {
                    font-size: 0.6rem !important;
                }
            }
            </style>

            <style>
            /* Das technische Select und eventuell bereits von Select2 erzeugte UI nie anzeigen. */
            #agents_modal #agents_select,
            #agents_modal #agents_select + .select2-container,
            #agents_modal .select2-container:has(+ #agents_select),
            #agents_modal .select2-container[aria-owns*="agents_select"] {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                overflow: hidden !important;
            }
            </style>

            <!-- Nur technisches Select für Form-Submission, keine sichtbare Select2-Ausgabe -->
            <div id="agents_source" hidden aria-hidden="true">
                <?php if (function_exists('util_load_agents_select')): ?>
                    <?= util_load_agents_select($data['agents'] ?? ($agents ?? [])); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="lb-cr-footer">
        <button type="button" class="lb-cr-btn lb-cr-btn--reset" id="lb_agent_reset">
            <?= t('Reset Selection') ?>
        </button>
        <button type="button" class="lb-cr-btn lb-cr-btn--save save-agents close-modal">
            <?= t('Save Selections') ?>
        </button>
    </div>

    <script>
    (function () {
        var gridBuilt = false;

        function updateAgentFreeBadge() {
            var selectedMode = document.querySelector('input[name="is_duo"]:checked');
            var isDuo = selectedMode && selectedMode.value === '1';
            document.querySelectorAll('.lb-agent-free-badge').forEach(function (badge) {
                badge.style.display = isDuo ? '' : 'none';
            });
        }

        function removeLegacyAgentSelect2() {
            var select = document.getElementById('agents_source');
            if (!select) return;

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                var $select = window.jQuery(select);
                if ($select.data('select2')) {
                    try { $select.select2('destroy'); } catch (e) {}
                }
            }

            select.classList.remove('select2', 'select2-hidden-accessible');
            select.hidden = true;
            select.style.setProperty('display', 'none', 'important');

            var next = select.nextElementSibling;
            if (next && next.classList.contains('select2-container')) next.remove();

            document.querySelectorAll('#agents_modal .select2-container').forEach(function (container) {
                if (container.querySelector('[aria-labelledby*="agents_select"], [aria-controls*="agents_select"], [aria-owns*="agents_select"]')) {
                    container.remove();
                }
            });
        }

        function buildAgentGrid() {
            removeLegacyAgentSelect2();
            if (gridBuilt) return;
            var select = document.getElementById('agents_source');
            var grid   = document.getElementById('lb_agent_grid');
            if (!select || !grid) return;

            var frag = document.createDocumentFragment();
            select.querySelectorAll('option').forEach(function(opt) {
                if (!opt.value) return;
                var val   = opt.value;
                var name  = opt.textContent.trim();
                var image = opt.getAttribute('data-image') || '';

                var item = document.createElement('div');
                item.className = 'lb-cr-champ-item';
                item.dataset.name = name.toLowerCase();
                item.title = name;

                var cb = document.createElement('input');
                cb.type = 'checkbox'; cb.name = 'agents[]'; cb.value = val;
                cb.className = 'lb-cr-champ-cb'; cb.style.display = 'none';
                cb.id = 'agent_cb_' + val;

                var lbl = document.createElement('label');
                lbl.htmlFor = 'agent_cb_' + val;
                lbl.className = 'lb-cr-champ-label';

                var img = document.createElement('img');
                img.src = image; img.alt = name; img.loading = 'lazy';

                var span = document.createElement('span');
                span.className = 'lb-cr-champ-name';
                span.textContent = name;

                lbl.appendChild(img); lbl.appendChild(span);
                item.appendChild(cb); item.appendChild(lbl);

                if (opt.selected) { item.classList.add('selected'); cb.checked = true; }

                item.addEventListener('click', function() {
                    var checked = cb.checked = !cb.checked;
                    item.classList.toggle('selected', checked);
                    
                });

                frag.appendChild(item);
            });

            grid.appendChild(frag);
            gridBuilt = true;
        }

        function initListeners() {
            var search = document.getElementById('lb_agent_search');
            var reset  = document.getElementById('lb_agent_reset');
            var grid   = document.getElementById('lb_agent_grid');

            if (search) {
                search.addEventListener('input', function() {
                    var q = this.value.toLowerCase().trim();
                    if (grid) grid.querySelectorAll('.lb-cr-champ-item').forEach(function(item) {
                        item.style.display = (q && item.dataset.name.indexOf(q) === -1) ? 'none' : '';
                    });
                });
            }
            if (reset) {
                reset.addEventListener('click', function() {
                    if (grid) grid.querySelectorAll('.lb-cr-champ-item.selected').forEach(function(item) {
                        item.classList.remove('selected');
                        item.querySelector('.lb-cr-champ-cb').checked = false;
                    });
                    
                });
            }
        }

        var modal = document.getElementById('agents_modal');
        if (modal) {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    if (m.type === 'attributes') {
                        var isOpen = modal.classList.contains('show');
                        if (isOpen) {
                            buildAgentGrid();
                            document.body.classList.add('lb-cr-modal-open');
                        } else {
                            document.body.classList.remove('lb-cr-modal-open');
                        }
                    }
                });
            });
            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        }

        document.addEventListener('change', function (event) {
            if (event.target && event.target.matches('input[name="is_duo"]')) {
                updateAgentFreeBadge();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            removeLegacyAgentSelect2();
            initListeners();
            updateAgentFreeBadge();
            window.setTimeout(removeLegacyAgentSelect2, 0);
            window.setTimeout(removeLegacyAgentSelect2, 250);
        });

        if (document.readyState !== 'loading') {
            removeLegacyAgentSelect2();
            updateAgentFreeBadge();
            window.setTimeout(removeLegacyAgentSelect2, 0);
        }
    })();
    </script>

</div>


<?= $this->insert('website/components/testimonials') ?>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wnumb/1.2.0/wNumb.min.js"></script>
<script src="<?= ASSET_URL ?>/website/js/boost-forms/val.js"></script>

<script>
    // Scroll boost pages to the section anchor above the service cards.
    // This keeps enough space between the fixed header stack and .rank-types-nav,
    // especially when the sale/gamer-girl banner is open on mobile.
    function getValFixedTopOffset() {
        var root = document.documentElement;
        var styles = getComputedStyle(root);
        var contentTop = parseFloat(styles.getPropertyValue('--lb-content-top')) || 0;

        if (contentTop > 0) {
            return contentTop;
        }

        var zoom = parseFloat(styles.zoom) || 1;
        var selectors = [
            '#lbSaleBanner',
            '#lbGiveawayBanner',
            '.navbar-top',
            '.navbar-mobile',
            '.lb-game-subnav',
            '.lb-mobile-gamebar'
        ];
        var bottom = 0;

        selectors.forEach(function(selector) {
            var el = document.querySelector(selector);
            if (!el) return;

            var cs = getComputedStyle(el);
            if (cs.display === 'none' || cs.visibility === 'hidden') return;

            var rect = el.getBoundingClientRect();
            if (rect.width <= 0 || rect.height <= 0) return;

            bottom = Math.max(bottom, rect.bottom / zoom);
        });

        return bottom || 90;
    }

    var stickyOverview = document.querySelector('.sticky-overview');
    var hideSticky = document.getElementById('hide-sticky');
    var paymentGateways = document.querySelector('.payment-gateways');
    var tawkHiddenBySticky = false;

    function isStickyMobileView() {
        return window.matchMedia('(max-width: 1024px)').matches;
    }

    function setTawkVisibility(shouldHide) {
        if (tawkHiddenBySticky === shouldHide) return;
        tawkHiddenBySticky = shouldHide;

        if (window.Tawk_API) {
            if (shouldHide && typeof window.Tawk_API.hideWidget === 'function') {
                window.Tawk_API.hideWidget();
            } else if (!shouldHide && typeof window.Tawk_API.showWidget === 'function') {
                window.Tawk_API.showWidget();
            }
        }
    }

    function isElementInViewport(element, scrollTop, windowHeight) {
        if (!element) return false;

        var elementTop = element.getBoundingClientRect().top + window.scrollY;
        var elementBottom = elementTop + element.offsetHeight;

        return scrollTop + windowHeight > elementTop && scrollTop < elementBottom;
    }

    function hasReachedPaymentGateways(scrollTop, windowHeight) {
        if (!paymentGateways) return true;

        // Show the sticky bottom menu once the payment gateway section becomes visible.
        return scrollTop + windowHeight >= paymentGateways.getBoundingClientRect().top + window.scrollY;
    }

    function setStickyVisible(isVisible) {
        if (!stickyOverview) return;

        stickyOverview.style.transform = isVisible ? 'translateY(0)' : 'translateY(100%)';
        stickyOverview.style.transition = 'transform 0.3s ease-in-out';

        var shouldHideTawk = isStickyMobileView() && isVisible;
        document.body.classList.toggle('lb-sticky-overview-active', shouldHideTawk);
        setTawkVisibility(shouldHideTawk);
    }

    function checkStickyVisibility() {
        if (!stickyOverview) return;

        var windowHeight = window.innerHeight || document.documentElement.clientHeight;
        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        var reachedPaymentGateways = hasReachedPaymentGateways(scrollTop, windowHeight);
        var hiddenByStopSection = isElementInViewport(hideSticky, scrollTop, windowHeight);

        setStickyVisible(reachedPaymentGateways && !hiddenByStopSection);
    }

    window.addEventListener('scroll', checkStickyVisibility);
    window.addEventListener('resize', checkStickyVisibility);
    checkStickyVisibility();

<?php
$lbDiscountCodes = [];
try {
    global $db;
    $lbDiscountRows = $db->run("SELECT code, amount, is_fixed FROM discounts WHERE status = 1 AND (max_uses IS NULL OR uses < max_uses) AND (starts_at IS NULL OR starts_at <= NOW()) AND (expires_at IS NULL OR expires_at >= NOW()) AND (services LIKE '%boosting%' OR services LIKE '%coaching%')");
    if (!empty($lbDiscountRows)) {
        foreach ($lbDiscountRows as $row) {
            $code = trim((string)($row['code'] ?? ''));
            if ($code === '') continue;
            $key = function_exists('mb_strtolower') ? mb_strtolower($code, 'UTF-8') : strtolower($code);
            $lbDiscountCodes[$key] = [
                'code' => $code,
                'amount' => (float)($row['amount'] ?? 0),
                'is_fixed' => (int)($row['is_fixed'] ?? 0) === 1,
            ];
        }
    }
} catch (Throwable $e) {}
?>

    window.lbDiscountCodes = <?= json_encode($lbDiscountCodes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?> || {};
    window.lbDefaultDiscountCode = 'LB-TDAVAL';

    (function () {
        var activeDiscount = null;
        var basePrice = null;
        var isRenderingDiscount = false;

        function el(id) { return document.getElementById(id); }

        function getCurrencySymbol() {
            var totalPriceElement = el('total-price');
            if (!totalPriceElement || !totalPriceElement.textContent) return '€';
            return totalPriceElement.textContent.trim().charAt(0) || '€';
        }

        function parsePrice(value) {
            var number = parseFloat(String(value || '').replace(/[^0-9.-]+/g, ''));
            return isNaN(number) ? 0 : number;
        }

        function formatPrice(value) {
            return getCurrencySymbol() + Number(value || 0).toFixed(2);
        }

        function normalizeCode(code) {
            return String(code || '').trim().toLowerCase();
        }

        function findDiscount(code) {
            return window.lbDiscountCodes ? window.lbDiscountCodes[normalizeCode(code)] : null;
        }

        function getEffectiveDiscountAmount(discount) {
            var code = String(discount && discount.code ? discount.code : '').trim().toUpperCase();
            var amount = parseFloat(discount && discount.amount ? discount.amount : 0);
            if (discount && discount.is_fixed) return amount;
            if (code.indexOf('LB-REWARD-') === 0) return Math.min(90, 30 + amount);
            return amount;
        }

        function getSavedPrice(currentFinalPrice, discount) {
            var amount = getEffectiveDiscountAmount(discount);
            if (!amount) return 0;
            if (discount.is_fixed) return Math.max(0, amount);
            return Math.max(0, currentFinalPrice * (amount / 100));
        }

        function getDisplayDiscountAmount(discount) {
            var code = String(discount && discount.code ? discount.code : '').trim().toUpperCase();
            var amount = parseFloat(discount && discount.amount ? discount.amount : 0);
            if (discount && discount.is_fixed) return amount;
            if (code === 'LB-TDAN3W') return 60;
            if (code.indexOf('LB-WC26-') === 0) return 70;
            return amount;
        }

        function getDiscountLabel(discount) {
            return 'Special Price Applied';
        }

        function renderDiscount(currentFinalPrice, discount) {
            var totalPriceElement = el('total-price');
            var newPriceElement = el('new-price');
            var savedPriceElement = el('saved-price');
            var discountInputWrap = el('discount-input');
            var discountBox = el('discount-box');
            var discountMessage = el('discount-message');
            var oldPriceElement = el('old-price');
            var stickyOldPrice = el('sticky-old-price');
            var stickyTotalPrice = el('sticky-total-price');

            if (!totalPriceElement || !discountBox || !discountMessage || !discount) return;

            activeDiscount = discount;
            basePrice = parseFloat(currentFinalPrice) || 0;

            var savedPrice = getSavedPrice(basePrice, discount);
            var originalPrice = basePrice + savedPrice;

            isRenderingDiscount = true;
            totalPriceElement.textContent = formatPrice(basePrice);
            if (newPriceElement) newPriceElement.textContent = formatPrice(basePrice);
            if (oldPriceElement) {
                oldPriceElement.textContent = formatPrice(originalPrice);
                oldPriceElement.style.display = '';
                oldPriceElement.style.textDecoration = 'line-through';
            }
            if (savedPriceElement) savedPriceElement.textContent = formatPrice(savedPrice);
            if (stickyTotalPrice) stickyTotalPrice.textContent = formatPrice(basePrice);
            if (stickyOldPrice) {
                stickyOldPrice.textContent = formatPrice(originalPrice);
                stickyOldPrice.style.display = '';
                stickyOldPrice.style.textDecoration = 'line-through';
            }

            discountMessage.textContent = getDiscountLabel(discount);
            discountMessage.setAttribute('data-discount', discount.amount || 0);
            discountMessage.setAttribute('data-fixed', discount.is_fixed ? '1' : '0');

            if (discountInputWrap) discountInputWrap.style.display = 'none';
            var discountCodeElement = el('discount_code');
            if (discountCodeElement) discountCodeElement.value = discount.code || '';
            var removeBtn = el('remove-discount');
            if (removeBtn) removeBtn.style.display = '';
            discountBox.style.display = 'flex';

            setTimeout(function () { isRenderingDiscount = false; }, 0);
        }

        function applyDiscount(discount) {
            var totalPriceElement = el('total-price');
            if (!totalPriceElement || !discount) return;

            var currentVisiblePrice = parsePrice(totalPriceElement.textContent);
            var originalPrice = basePrice !== null ? basePrice : currentVisiblePrice;
            renderDiscount(originalPrice, discount);
        }

        function removeDiscount() {
            var discountInputWrap = el('discount-input');
            var discountCodeElement = el('discount_code');
            if (discountInputWrap) discountInputWrap.style.display = 'flex';
            if (discountCodeElement) {
                discountCodeElement.focus();
                if (typeof discountCodeElement.select === 'function') discountCodeElement.select();
            }
        }

        function syncDiscountAfterPriceChange() {
            var totalPriceElement = el('total-price');
            if (isRenderingDiscount || !activeDiscount || !totalPriceElement) return;

            var visiblePrice = parsePrice(totalPriceElement.textContent);
            if (basePrice !== null && Math.abs(visiblePrice - basePrice) < 0.01) return;

            renderDiscount(visiblePrice, activeDiscount);
        }

        function initDiscounts() {
            var totalPriceElement = el('total-price');
            var discountCodeElement = el('discount_code');
            var removeBtn = el('remove-discount');

            if (removeBtn) {
                removeBtn.style.display = '';
                removeBtn.onclick = function (event) {
                    event.preventDefault();
                    removeDiscount();
                };
            }

            if (discountCodeElement) {
                discountCodeElement.addEventListener('input', function () {
                    var discount = findDiscount(discountCodeElement.value);
                    if (discount) applyDiscount(discount);
                });
            }

            if (totalPriceElement && window.MutationObserver) {
                var totalPriceObserver = new MutationObserver(syncDiscountAfterPriceChange);
                totalPriceObserver.observe(totalPriceElement, { childList: true, characterData: true, subtree: true });
            }

            var defaultDiscount = findDiscount(window.lbDefaultDiscountCode);
            if (discountCodeElement && defaultDiscount) {
                applyDiscount(defaultDiscount);
                discountCodeElement.value = defaultDiscount.code || window.lbDefaultDiscountCode;
                setTimeout(function () { applyDiscount(defaultDiscount); }, 150);
                setTimeout(function () { applyDiscount(defaultDiscount); }, 500);
            }

            if (window.jQuery) {
                $('input:radio[name="is_duo"]').on('change', function () {
                    if ($(this).val() == '0') {
                        $('#free-champs').show();
                    } else {
                        $('#free-champs').hide();
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDiscounts);
        } else {
            initDiscounts();
        }
    })();
</script>
<?= $this->end('scripts') ?>
