<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'lol-boost lb-boost-nav-only']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.css">

<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

<style>
    
        .rank-types-nav .nav-item.lb-season-highlight{ outline-offset: 2px; }

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

    /* Anchor offset for mobile/desktop fixed header stack */
    #tftPageTop,
    .rank-types-nav {
        scroll-margin-top: var(--lb-content-top, 96px);
    }

</style>
<?= $this->end('styles') ?>

<?php
$ranks = [
    0 => 'Unranked',
    1 => 'Iron',
    2 => 'Bronze',
    3 => 'Silver',
    4 => 'Gold',
    5 => 'Platinum',
    6 => 'Emerald',
    7 => 'Diamond',
    8 => 'Master',
    9 => 'GrandMaster',
    10 => 'Challenger',
];

$arenas = [
    1 => 'Wood',
    2 => 'Bronze',
    3 => 'Silver',
    4 => 'Gold',
    5 => 'Gladiator',
];

$roles = [
    'top' => 'TopLane',
    'jungle' => 'Jungle',
    'mid' => 'MidLane',
    'adc' => 'AdCarry',
    'support' => 'Support',
];

$uiGame = ($data['game'] ?? 'lol');
if ($uiGame === 'tft') { $uiGame = 'lol'; }

// TFT Double Up (form_id 24) should use rank UI (ranks + desired rank)
if ((int)($data['id'] ?? 0) === 24) {
    $data['type'] = 'rank';
}
?>

<div id="tftPageTop" style="height:1px;position:relative;"></div>
<div class="lb-boost-breadcrumbs-wrap">
    <?= $this->insert('website/components/marketplace-breadcrumbs', [
        'type' => 'boosting',
        'gameSlug' => 'tft',
        'gameName' => 'Teamfight Tactics',
        'currentTitle' => (string)($data['name_long'] ?? $data['name'] ?? ''),
    ]) ?>
</div>
<div class="rank-types-nav">
    <a href="/tft/rank-boost" class="nav-item <?= $data['id'] == 21 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/rank-boost.svg" alt="rank-boost-icon">
        <span><?= t('Rank') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/tft/win-boost" class="nav-item <?= $data['id'] == 22 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/win-boost.svg" alt="win-boost-icon">
        <span><?= t('Win') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/tft/placements-boost" class="nav-item <?= $data['id'] == 23 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/placement-boost.svg" alt="placement-boost-icon">
        <span><?= t('Placements') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/tft/double-up-boost" class="nav-item <?= $data['id'] == 24 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/clash-boost.svg" alt="double-up-boost-icon">
        <span><?= t('Double Up') ?><br><?= t('Boost') ?></span>
    </a>

    <a href="/tft/coaching" class="nav-item <?= $data['id'] == 25 ? 'active' : '' ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" alt="expert-coaching-icon">
        <span><?= t('Expert') ?><br><?= t('Coaching') ?></span>
    </a>

</div>

<form class="boost-form" id="lol_boost_form" action="<?= AJAX_URL ?>" autocomplete="off">
    <input type="hidden" name="action" value="get_boost_price">
    <input type="hidden" name="form_id" value="<?= $data['id'] ?>">
    <input type="hidden" name="uuid" value="<?= $data['uuid'] ?>">

    <div class="form-content">
        <div class="left">
            <div class="boost-form">
                <?php $this->insert('website/components/forms/' . $data['type'], ['data' => $data, 'ranks' => $ranks, 'arenas' => $arenas]) ?>
            </div>
            <div class="boost-faqs">
                <h4><?= t('Frequently Asked Questions 🤔') ?></h4>

                <?php
                // TFT Double Up (form_id 24) uses its own FAQ component
                if ((int)($data['id'] ?? 0) === 24) {
                    $this->insert('website/components/faqs/forms/tft/double_up', ['data' => $data, 'ranks' => $ranks, 'arenas' => $arenas]);
                } else {
                    $this->insert('website/components/faqs/forms/tft/' . $data['type'], ['data' => $data, 'ranks' => $ranks, 'arenas' => $arenas]);
                }
                ?>

            </div>
        </div>
        <div class="right">
            <?php $this->insert('website/components/forms/order-summary', ['data' => $data]) ?>

            <div class="boost-faqs-mobile">
                <h4><?= t('Frequently Asked Questions 🤔') ?></h4>

                <?php
                // Keep mobile FAQ in sync with desktop
                if ((int)($data['id'] ?? 0) === 24) {
                    $this->insert('website/components/faqs/forms/tft/double_up', ['data' => $data, 'ranks' => $ranks, 'arenas' => $arenas]);
                } else {
                    $this->insert('website/components/faqs/forms/tft/' . $data['type'], ['data' => $data, 'ranks' => $ranks, 'arenas' => $arenas]);
                }
                ?>
            </div>
        </div>
    </div>

    <div class="modal" id="champions_roles_modal">
        <div class="modal-header">
            <h4><?= t('Roles & Champions') ?></h4>
            <button class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-content">
            <h4 class="heading">
                <div class="bullet"><?= t('1') ?></div><?= t('Select your Roles') ?>
                <div class="badge success"><?= t('FREE') ?></div>
            </h4>
            <div class="icon-checkboxes">
                <?php foreach ($roles as $role): ?>
                    <input type="checkbox" name="roles[]" id="role_<?= $role ?>" value="<?= $role ?>">
                    <label class="icon-checkbox" for="role_<?= $role ?>" data-tooltip="<?= $role ?>">
                        <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= $role ?>.png" alt="<?= $role ?>">
                    </label>
                <?php endforeach; ?>
            </div>
            <h4 class="heading">
                <div class="bullet"><?= t('2') ?></div>
                Select your Champions
                <?php if ($data['json']['extra']['champions'] == 0): ?>
                    <div class="badge success"><?= t('FREE') ?></div>
                <?php else: ?>
                    <div class="badge primary" id="free-champs" style="display: none;">
                        <?= util_format_price_db($data['json']['extra']['champions']) ?>%
                    </div>
                <?php endif; ?>
            </h4>
            <select name="champions[]" class="select2" id="champions_select" multiple>
                <?= util_load_champions_select() ?>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn secondary close-modal"><?= t('Close') ?></button>
            <button class="btn primary close-modal"><?= t('Save Changes') ?></button>
        </div>
    </div>

    <div class="sticky-overview">
        <div class="rank-box" <?php if ($data['type'] == 'coaching') {
            echo 'style="justify-content: center;"';
        } ?>>
            <div class="from">
                <?php switch ($data['type']) {
                    case 'rank': ?>
                        <img src="<?= util_rank_img($uiGame, 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <br>
                        <small class="current-summary-lp"><?= t('[ 0-20 LP ]') ?></small>
                        <?php break;
                    case 'win': ?>
                        <img src="<?= util_rank_img($uiGame, 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'placement': ?>
                        <img src="<?= util_rank_img($uiGame, 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'match': ?>
                        <img src="<?= util_rank_img($uiGame, 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'normal': ?>
                        <div class="game game-mode"><?= t('Summoner\'s Rift') ?></div>
                        <?php break;
                    case 'coaching': ?>
                        <div class="game" style="text-align: center; width: 100%;">
                            <span class="hour-count"><?= t('5') ?></span><?= t('Coaching Hours') ?>
                        </div>
                        <?php break;
                    case 'mastery': ?>
                        <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/2.webp" alt="rank_icon"
                            class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Level 1') ?></span>
                        <?php break;
                    case 'arena': ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?= ASSET_URL ?>/core/main/img/lol/arenas/3.webp" alt="rank_icon"
                                class="current-summary-rank-img">
                            <span class="title current-summary-rank-name"><?= t('Silver') ?></span>
                        </div>
                        <?php break;
                    case 'level': ?>
                        <div class="game current-summary-rank-name"><?= t('Level 1') ?></div>
                        <?php break;
                    case 'clash': ?>
                        <div class="game current-summary-rank-name"><?= t('Tier 1 (1 Booster)') ?></div>
                        <?php break;
                    default: ?>
                        <img src="<?= util_rank_img($uiGame, 'mini', 3) ?>" alt="rank_icon">
                        <span class="title"><?= t('Silver I') ?></span>
                        <?php break;
                } ?>
            </div>
            <?php if ($data['type'] != 'coaching') { ?>
                <img src="<?= ASSET_URL ?>/website/images/arrow-summary.svg" alt="arrow_icon">
            <?php } ?>
            <?php switch ($data['type']) {
                case 'rank': ?>
                    <div class="to">
                        <img src="<?= util_rank_img($uiGame, 'mini', 4) ?>" alt="rank_icon" class="desired-summary-rank-img">
                        <span class="title desired-summary-rank-name"><?= t('Gold IV') ?></span>
                        <br>
                        <small class="desired-summary-lp"></small>
                    </div>
                    <?php break;

                case 'win': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('2') ?></span><?= t('Wins') ?>
                        </div>
                    </div>
                    <?php break;

                case 'placement': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('3') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                case 'match': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('3') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                case 'normal': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('3') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                case 'coaching': ?>
                    <?php break;

                case 'mastery': ?>
                    <div class="to">
                        <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/6.webp" alt="rank_icon"
                            class="desired-summary-rank-img">
                        <span class="title desired-summary-rank-name"><?= t('Level 6') ?></span>
                    </div>
                    <?php break;

                case 'arena': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('2') ?></span><?= t('Wins') ?>
                        </div>
                    </div>
                    <?php break;

                case 'level': ?>
                    <div class="to">
                        <div class="count current-summary-rank-name"><?= t('Level 2') ?></div>
                    </div>
                    <?php break;

                case 'clash': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('2') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                default: ?>
                    <div class="to">
                        <img src="<?= util_rank_img($uiGame, 'mini', 4) ?>" alt="rank_icon">
                        <span class="title"><?= t('Gold IV') ?></span>
                    </div>
                    <?php break;
            } ?>
        </div>

        <div class="totals">
            <p>
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/total.svg"
                    alt="total_icon"><?= t('Total Price') ?>
            </p>

            <div>
                <span class="price old-price" id="sticky-old-price"><?= t('€0.00') ?></span>
                <span class="price total-price" id="sticky-total-price"><?= t('€0.00') ?></span>
            </div>
        </div>

        <button type="submit" class="btn primary buy-now" id="sticky_start_boost"><?= t('Buy Now') ?></button>
    </div>
</form>

<div class="bottom-sec">
    <?= $this->insert('website/components/testimonials') ?>

<div class="choose-us">
        <h4><?= t('Why Choose Us?') ?></h4>
        <div class="tiles">
            <div class="tile">
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/empowerment.svg" alt="empowerment-that-lasts">
                <h5><?= t('Results That Last') ?></h5>
                <p><?= t('We do more than push your rank. Our pros share simple tips so you play smarter and keep winning
                    after the boost.
                    Improve today and keep the gains tomorrow.') ?></p>
            </div>
            <div class="tile">
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/climb.svg" alt="your-climb-starts-here">
                <h5><?= t('Start Your Climb Today') ?></h5>
                <p><?= t('Choose a League of Legends boost or coaching session. Reach new divisions fast and safely, with
                    clear tracking and support.') ?></p>
            </div>
            <div class="tile">
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/victory.svg" alt="victory-is-a-journey">
                <h5><?= t('Win More, Stress Less') ?></h5>
                <p><?= t('Climbing is a journey. We boost or duo with you, keep it secure with VPN and manual play, and update
                    you until your goal is reached.') ?></p>
            </div>
        </div>
    </div>

    <div class="about-us">
        <div class="content">
            <h4><?= t('About Us') ?></h4>
            <p><?= t('LolBoost.gg offers professional League of Legends services: Elo boosting, Duo Queue, placement matches,
                coaching,
                and hand-leveled LoL accounts. All boosts are manual, handled by verified Challenger and Grandmaster
                players,
                with region-matched VPN for safety.') ?><br><br><?= t('Stuck in the same rank or tired of solo queue? We help you climb faster and learn along the way. Track
                progress
                in your dashboard, chat with support 24/7, and enjoy a smooth, secure experience from start to finish.') ?>
            </p>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wnumb/1.2.0/wNumb.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="<?= ASSET_URL ?>/website/js/boost-forms/lol.js"></script>
<script>
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
    window.lbDefaultDiscountCode = 'LB-TDAN3W';

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

        function getSavedPrice(currentFinalPrice, discount) {
            var amount = parseFloat(discount && discount.amount ? discount.amount : 0);
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

    var $stickySection = $(".sticky-overview");
    var $hideSticky = $("#hide-sticky");
    var $paymentGateways = $(".payment-gateways").first();
    var tawkHiddenBySticky = false;

    function isStickyMobileView() {
        return window.matchMedia("(max-width: 1024px)").matches;
    }

    function setTawkVisibility(shouldHide) {
        if (tawkHiddenBySticky === shouldHide) return;
        tawkHiddenBySticky = shouldHide;

        if (window.Tawk_API) {
            if (shouldHide && typeof window.Tawk_API.hideWidget === "function") {
                window.Tawk_API.hideWidget();
            } else if (!shouldHide && typeof window.Tawk_API.showWidget === "function") {
                window.Tawk_API.showWidget();
            }
        }
    }

    function isElementInViewport($element, scrollTop, windowHeight) {
        if (!$element.length) return false;

        var elementTop = $element.offset().top;
        var elementBottom = elementTop + $element.outerHeight();

        return scrollTop + windowHeight > elementTop && scrollTop < elementBottom;
    }

    function hasReachedPaymentGateways(scrollTop, windowHeight) {
        if (!$paymentGateways.length) return true;

        // Show the sticky bottom menu once the payment gateway section becomes visible.
        return scrollTop + windowHeight >= $paymentGateways.offset().top;
    }

    function setStickyVisible(isVisible) {
        $stickySection.css({
            transform: isVisible ? "translateY(0)" : "translateY(100%)",
            transition: "transform 0.3s ease-in-out"
        });

        var shouldHideTawk = isStickyMobileView() && isVisible;
        document.body.classList.toggle("lb-sticky-overview-active", shouldHideTawk);
        setTawkVisibility(shouldHideTawk);
    }

    function checkVisibility() {
        if (!$stickySection.length) return;

        var windowHeight = $(window).height();
        var scrollTop = $(window).scrollTop();
        var reachedPaymentGateways = hasReachedPaymentGateways(scrollTop, windowHeight);
        var hiddenByStopSection = isElementInViewport($hideSticky, scrollTop, windowHeight);

        setStickyVisible(reachedPaymentGateways && !hiddenByStopSection);
    }

    $(window).on("scroll resize", checkVisibility);
    checkVisibility();
</script>
<?= $this->end('scripts') ?>
