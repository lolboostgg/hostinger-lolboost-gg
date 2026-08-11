<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'lol-boost lb-boost-nav-only']) ?>

<?= $this->start('styles') ?>
<script>if('scrollRestoration'in history)history.scrollRestoration='manual';</script>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.css">

<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

<style>
    /* Rank-picker icons: slightly larger for rank/win/placement boost.
       Coaching keeps its own (smaller) sizing via .coaching-current-ranks.
       Adjust the width/height below to taste. */
    .rank-boost .ranks .rank-btn img,
    .win-boost .ranks:not(.coaching-current-ranks) .rank-btn img {
        width: 44px !important;
        height: 44px !important;
        object-fit: contain !important;
        max-width: none !important;
    }

    @media (min-width: 1025px) {
        .rank-boost .rank-cards > .card .ranks {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 0.75vw !important;
            width: 100% !important;
        }

        .rank-boost .rank-cards > .card .ranks label {
            display: flex !important;
            justify-content: center !important;
            width: 100% !important;
            min-width: 0 !important;
        }

        .rank-boost .rank-cards > .card .ranks .rank-btn {
            width: 4.15vw !important;
            height: 4.15vw !important;
            padding: 0.55vw !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    }

    .duo-option {
        display: none;
    }
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

    /* GGirls mobile sticky overview uses the same compact summary content as the order summary. */
    .boost-form.ggirls-page .sticky-overview .rank-box{
        justify-content:flex-start!important;
        gap:10px!important;
    }
    .boost-form.ggirls-page .sticky-overview .rank-box .from{
        width:100%!important;
        min-width:0!important;
    }
    .boost-form.ggirls-page .sticky-overview .ggl-summary-compact{
        width:100%;display:flex;align-items:center;gap:10px;min-width:0;text-align:left!important;
    }
    .boost-form.ggirls-page .sticky-overview .ggl-summary-mode-icon{
        width:40px;height:40px;flex:0 0 40px;border-radius:11px;
        display:flex;align-items:center;justify-content:center;
        background:linear-gradient(135deg,rgba(168,85,247,.35),rgba(236,72,153,.2))!important;
        border:1px solid rgba(168,85,247,.4)!important;color:#fff!important;
    }
    .boost-form.ggirls-page .sticky-overview .ggl-summary-mode-icon i{font-size:17px;line-height:1}
    .boost-form.ggirls-page .sticky-overview .ggl-summary-compact-copy{display:flex;flex-direction:column;gap:4px;min-width:0;align-items:flex-start!important}
    .boost-form.ggirls-page .sticky-overview .ggl-summary-compact-copy .title{max-width:100%;font-size:15px!important;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .boost-form.ggirls-page .sticky-overview #ggl-sticky-summary-sub{display:inline-flex;max-width:100%;font-size:11px!important;font-weight:800;color:#e9d5ff!important;background:rgba(168,85,247,.18)!important;padding:3px 9px!important;border-radius:999px!important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}


    /* Paid champion selection uses the same pill style as FREE, but in blue. */
    .lb-cr-badge--price {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 4px 10px !important;
        min-height: 24px !important;
        border-radius: 999px !important;
        border: 1px solid rgba(99, 102, 241, 0.72) !important;
        color: #aeb5ff !important;
        background: rgba(99, 102, 241, 0.14) !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        box-sizing: border-box !important;
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
?>

<div id="lolPageTop" style="height:1px;position:relative;"></div>
<div class="rank-types-nav">
    <a href="/lol/rank-boost" class="nav-item <?= $data['id'] == 1 ? 'active' : '' ?>" data-tooltip="<?= t('Our pro booster climbs from your current rank to your desired rank.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/rank-boost.svg" alt="rank-boost-icon">
        <span><?= t('Rank') ?><br><?= t('Boost') ?></span>
        <span class="lb-hot-badge">HOT</span>
    </a>
    <a href="/lol/win-boost" class="nav-item <?= $data['id'] == 2 ? 'active' : '' ?>" data-tooltip="<?= t('A booster wins a set number of games on your account.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/win-boost.svg" alt="win-boost-icon">
        <span><?= t('Win') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/lol/placements-boost" class="nav-item <?= $data['id'] == 3 ? 'active' : '' ?>" data-tooltip="<?= t('Get the best possible results in your placement matches at season start.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/placement-boost.svg"
            alt="placement-boost-icon">
        <span><?= t('Placement') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/lol/ggirls" class="nav-item nav-item--egirl <?= ($data['id'] ?? 0) == 28 ? 'active' : '' ?>" data-tooltip="<?= t('Play with a Gamer Girl.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" alt="gamer-girl-icon" class="nav-item-egirl-icon">
        <span><?= t('Gamer') ?><br><?= t('Girl') ?></span>
        <span class="lb-new-badge">NEW</span>
    </a>
    <a href="/lol/pro-games" class="nav-item <?= $data['id'] == 26 ? 'active' : '' ?>" data-tooltip="<?= t('Play alongside a Challenger booster in your ranked games as a duo.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/clash-boost.svg"
            alt="pro-games-icon">
        <span><?= t('Hire Pro') ?><br><?= t('Teammate') ?></span> 
        <span class="lb-hot-badge">HOT</span>
    </a>
    <a href="/lol/duo-pass" class="nav-item <?= $data['id'] == 27 ? 'active' : '' ?>" data-tooltip="<?= t('Book 3, 6 or 8 hours and play ranked together with a booster.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/clash-boost.svg"
            alt="duo-pass-icon">
        <span><?= t('Duo') ?><br><?= t('Pass') ?></span>
        <span class="lb-new-badge">NEW</span>
    </a>
     <a href="/league-of-legends/ranked-5s" class="nav-item <?= $data['id'] == RANKED_5S_FORM_ID ? 'active' : '' ?>" data-tooltip="<?= t('Play ranked as a 5 stack with 1 to 4 professional boosters.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/clash-boost.svg"
            alt="ranked-5s-icon">
        <span><?= t('Ranked') ?><br><?= t('5s') ?></span>
        <span class="lb-new-badge">NEW</span>
    </a>
    <a href="/lol/normals-boost" class="nav-item <?= $data['id'] == 4 ? 'active' : '' ?>" data-tooltip="<?= t('A booster plays normal matches on your account — no ranked risk.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/normal-matches.svg"
            alt="normal-matches-boost-icon">
        <span><?= t('Normal') ?><br><?= t('Matches') ?></span>
    </a>
    <a href="/lol/coaching" class="nav-item <?= $data['id'] == 15 ? 'active' : '' ?>" data-tooltip="<?= t('A Challenger coach reviews your gameplay and teaches you how to improve.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg"
            alt="expert-coaching-icon">
        <span><?= t('Expert') ?><br><?= t('Coaching') ?></span>
    </a>
    <a href="/lol/champion-mastery" class="nav-item <?= $data['id'] == 18 ? 'active' : '' ?>" data-tooltip="<?= t('Boost your champion mastery level on any champion you choose.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/champion-mastery.svg"
            alt="champion-mastery-icon">
        <span><?= t('Champion') ?><br><?= t('Mastery') ?></span>
    </a>
    <a href="/lol/arena-boost" class="nav-item <?= $data['id'] == 17 ? 'active' : '' ?>" data-tooltip="<?= t('Climb the Arena mode rankings with the help of a pro booster.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/arena-boost.svg" alt="arena-boost-icon">
        <span><?= t('Arena') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/lol/level-boost" class="nav-item <?= $data['id'] == 20 ? 'active' : '' ?>" data-tooltip="<?= t('Level up your account quickly to unlock ranked or gifting features.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/level-up-boost.svg"
            alt="level-up-boost-icon">
        <span><?= t('Level Up') ?><br><?= t('Boost') ?></span>
    </a>
    <a href="/lol/clash-boost" class="nav-item <?= $data['id'] == 19 ? 'active' : '' ?>" data-tooltip="<?= t('Win your Clash tournament matches with a pro filling in your team.') ?>">
        <img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/clash-boost.svg" alt="clash-boost-icon">
        <span><?= t('Clash') ?><br><?= t('Boost') ?></span>
    </a>
</div>

<style>
.rank-types-nav .nav-item--egirl {
    background: linear-gradient(135deg, rgba(210, 40, 180, 0.22) 0%, rgba(255, 80, 220, 0.12) 100%);
    border-color: rgba(240, 80, 210, 0.50);
    box-shadow: 0 0 18px rgba(220, 40, 200, 0.22), inset 0 0 12px rgba(255, 100, 230, 0.08);
}
.rank-types-nav .nav-item--egirl:hover {
    background: linear-gradient(135deg, rgba(210, 40, 180, 0.34) 0%, rgba(255, 80, 220, 0.20) 100%);
    border-color: rgba(240, 80, 210, 0.75);
    box-shadow: 0 0 26px rgba(220, 40, 200, 0.38), inset 0 0 16px rgba(255, 100, 230, 0.12);
}
.rank-types-nav .nav-item--egirl span {
    color: #f9b8f0;
}
.rank-types-nav .nav-item-egirl-icon {
    filter: none !important;
    width: auto !important;
    height: 56px !important;
    max-width: none !important;
    max-height: 56px !important;
    object-fit: contain;
    margin-bottom: 1.042vw;
}
.rank-types-nav .nav-item--egirl .lb-new-badge,
.rank-types-nav .nav-item .lb-new-badge {
    position: absolute !important;
    top: 10px !important;
    right: 12px !important;

    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;

    padding: 3px 9px !important;
    border-radius: 999px !important;

    font-size: 10px !important;
    line-height: 1 !important;
    font-weight: 1000 !important;
    letter-spacing: 0.12em !important;
    text-transform: uppercase !important;

    color: #0b0b0f !important;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.35), transparent 55%), linear-gradient(135deg, #ff80ff, #f000e0) !important;

    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    box-shadow: 0 10px 18px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(0, 0, 0, 0.2) inset !important;

    pointer-events: none !important;
    white-space: nowrap !important;
    z-index: 5 !important;
}
.rank-types-nav .nav-item:not(.nav-item--egirl) .lb-new-badge {
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.3), transparent 55%), linear-gradient(135deg, #00e5b0, #00b8d4) !important;
}
</style>

<form class="boost-form <?= (($data['type'] ?? '') === 'ranked-5s') ? 'ranked-5s-page' : '' ?>" id="lol_boost_form" action="<?= AJAX_URL ?>" autocomplete="off">
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

                <?php $this->insert('website/components/faqs/forms/' . $data['type'], ['data' => $data, 'ranks' => $ranks, 'arenas' => $arenas]) ?>

            </div>
        </div>
        <div class="right">
            <?php $this->insert('website/components/forms/order-summary', ['data' => $data]) ?>

            <div class="boost-faqs-mobile">
                <h4><?= t('Frequently Asked Questions 🤔') ?></h4>

                <?php $this->insert('website/components/faqs/forms/' . $data['type'], ['data' => $data, 'ranks' => $ranks, 'arenas' => $arenas]) ?>
            </div>
        </div>
    </div>

    <div class="modal lb-modal--champs-roles" id="champions_roles_modal">

        <div class="lb-cr-header">
            <div class="lb-cr-header__title">
                <h4><?= t('Roles & Champions Selection') ?></h4>
            </div>
            <button class="lb-cr-close close-modal" aria-label="<?= t('Close') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="lb-cr-body">

            <!-- ── ROLES ── -->
            <div class="lb-cr-section">
                <div class="lb-cr-section__head">
                    <h5 class="lb-cr-section__title"><?= t('Select Roles') ?></h5>
                    <span class="lb-cr-badge lb-cr-badge--free"><?= t('Free') ?></span>
                </div>
                <div class="lb-cr-roles">
                    <?php foreach ($roles as $roleKey => $roleLabel): ?>
                        <input type="checkbox" name="roles[]" id="cr_role_<?= $roleLabel ?>" value="<?= $roleLabel ?>" class="lb-cr-role-input">
                        <label class="lb-cr-role-card" for="cr_role_<?= $roleLabel ?>">
                            <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= $roleLabel ?>.png" alt="<?= $roleLabel ?>">
                            <span class="lb-cr-role-name"><?= $roleLabel ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── CHAMPIONS ── -->
            <div class="lb-cr-section lb-cr-section--champs">
                <div class="lb-cr-section__head">
                    <h5 class="lb-cr-section__title"><?= t('Select Champions') ?></h5>
                    <?php
                        $championsExtra = (float)($data['json']['extra']['champions'] ?? 0);
                        $championsExtraPercent = (int)round($championsExtra * 100);
                    ?>
                    <?php if ($championsExtraPercent > 0): ?>
                        <span class="lb-cr-badge lb-cr-badge--price">+<?= $championsExtraPercent ?>%</span>
                    <?php else: ?>
                        <span class="lb-cr-badge lb-cr-badge--free"><?= t('Free') ?></span>
                    <?php endif; ?>
                </div>

                <div class="lb-cr-champ-search-wrap">
                    <i class="fas fa-search lb-cr-search-icon"></i>
                    <input type="text" class="lb-cr-champ-search" id="lb_champ_search" placeholder="<?= t('Search...') ?>" autocomplete="off">
                </div>

                <div class="lb-cr-champ-grid" id="lb_champ_grid"></div>

                <!-- Original select2 bleibt für Form-Submission erhalten -->
                <div id="champions_source" hidden aria-hidden="true">
                    <?= util_load_champions_select() ?>
                </div>
            </div>

        </div>

        <div class="lb-cr-footer">
            <button type="button" class="lb-cr-btn lb-cr-btn--reset" id="lb_champ_reset">
                <?= t('Reset Selection') ?>
            </button>
            <button type="button" class="lb-cr-btn lb-cr-btn--save close-modal" id="lb_champ_save">
                <?= t('Save Selections') ?>
            </button>
        </div>

        <script>
        (function () {
            var gridBuilt = false;

            function buildChampGrid() {
                if (gridBuilt) return; // build only once
                var select = document.getElementById('champions_source');
                var grid   = document.getElementById('lb_champ_grid');
                if (!select || !grid) return;

                // Use DocumentFragment for one single DOM insertion
                var frag = document.createDocumentFragment();
                var allOptions = select.querySelectorAll('option');

                allOptions.forEach(function(opt) {
                    if (!opt.value) return;
                    var champValue = opt.value;
                    var champName  = opt.textContent.trim();
                    var champImage = opt.getAttribute('data-image') || '';

                    var item = document.createElement('div');
                    item.className = 'lb-cr-champ-item';
                    item.dataset.name = champName.toLowerCase();
                    item.title = champName;

                    var cb = document.createElement('input');
                    cb.type = 'checkbox'; cb.value = champValue;
                    cb.name = 'champions[]';
                    cb.className = 'lb-cr-champ-cb'; cb.style.display = 'none';
                    cb.id = 'champ_cb_' + champValue;

                    var lbl = document.createElement('label');
                    lbl.htmlFor = 'champ_cb_' + champValue;
                    lbl.className = 'lb-cr-champ-label';

                    var img = document.createElement('img');
                    img.src = champImage; img.alt = champName;
                    img.loading = 'lazy'; // don't block page load

                    var span = document.createElement('span');
                    span.className = 'lb-cr-champ-name';
                    span.textContent = champName;

                    lbl.appendChild(img); lbl.appendChild(span);
                    item.appendChild(cb); item.appendChild(lbl);

                    if (opt.selected) { item.classList.add('selected'); cb.checked = true; }

                    item.addEventListener('click', function() {
                        var checked = cb.checked = !cb.checked;
                        item.classList.toggle('selected', checked);

                        // Notify the shared pricing script immediately. The champion
                        // checkboxes are created dynamically, so the original static
                        // change listener in lol.js cannot see the first selection.
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                        
                    });

                    frag.appendChild(item);
                });

                grid.appendChild(frag);
                gridBuilt = true;
            }

            function initListeners() {
                var search = document.getElementById('lb_champ_search');
                var reset  = document.getElementById('lb_champ_reset');
                var save   = document.getElementById('lb_champ_save');
                var grid   = document.getElementById('lb_champ_grid');

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
                        
                        document.querySelectorAll('.lb-cr-role-input').forEach(function(cb) { cb.checked = false; });
                    });
                }
                if (save) {
                    save.addEventListener('click', function() {
                        // Recalculate immediately after saving the champion selection.
                        // The price listener reacts to a bubbled change event and then
                        // serializes the complete form, including champions[].
                        var form = document.getElementById('lol_boost_form');
                        var priceTrigger = document.getElementById('is_champions_roles');
                        var selectedChampion = grid ? grid.querySelector('.lb-cr-champ-cb:checked') : null;
                        var eventTarget = selectedChampion || priceTrigger || form;

                        setTimeout(function() {
                            if (!eventTarget) return;
                            eventTarget.dispatchEvent(new Event('change', { bubbles: true }));
                        }, 0);
                    });
                }
            }

            // Build grid ONLY when modal opens — not on page load
            document.addEventListener('click', function(e) {
                var trigger = e.target.closest('.option[data-tooltip], label[for="is_champions_roles"], #is_champions_roles, .lb-cr-champ-trigger');
                // Also catch the "Champs & Roles" row click via the option div
                var optionRow = e.target.closest('.option');
                if (optionRow && optionRow.querySelector('#is_champions_roles, [name="is_champions_roles"]')) {
                    setTimeout(buildChampGrid, 50);
                }
                if (trigger) { setTimeout(buildChampGrid, 50); }
            });

            // Fallback: build when modal becomes visible
            var modal = document.getElementById('champions_roles_modal');
            if (modal) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(m) {
                        if (m.type === 'attributes') {
                            var isOpen = modal.classList.contains('show');
                            if (isOpen) {
                                buildChampGrid();
                                document.body.classList.add('lb-cr-modal-open');
                            } else {
                                document.body.classList.remove('lb-cr-modal-open');
                            }
                        }
                    });
                });
                observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
            }

            document.addEventListener('DOMContentLoaded', initListeners);
        })();
        </script>

    </div>

    <div class="sticky-overview">
        <div class="rank-box" <?php if ($data['type'] == 'coaching') {
            echo 'style="justify-content: center;"';
        } ?>>
            <div class="from">
                <?php switch ($data['type']) {
                    case 'rank': ?>
                        <img src="<?= util_rank_img('lol', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <br>
                        <small class="current-summary-lp"><?= t('[ 0-20 LP ]') ?></small>
                        <?php break;
                    case 'win': ?>
                        <img src="<?= util_rank_img('lol', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'placement': ?>
                        <img src="<?= util_rank_img('lol', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'match': ?>
                        <img src="<?= util_rank_img('lol', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'ggirls': ?>
                        <div class="ggl-summary-compact">
                            <span class="ggl-summary-mode-icon"><i id="ggl-sticky-summary-mode-icon" class="fa-solid fa-gamepad"></i></span>
                            <span class="ggl-summary-compact-copy">
                                <span class="title current-summary-rank-name" id="ggl-sticky-summary-title"><?= t('Normal Draft Game') ?></span>
                                <small class="current-summary-lp" id="ggl-sticky-summary-sub"><?= t('Unranked · 1 Game') ?></small>
                            </span>
                        </div>
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
                    case 'pro-games': ?>
                        <div id="pg-sticky-booster" style="display:flex;align-items:center;gap:10px;">
                            <img id="pg-sticky-avatar" src="" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #7c5cfc;flex-shrink:0;display:none;">
                            <div>
                                <div style="font-size:.7rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;"><?= t('Selected Booster') ?></div>
                                <div id="pg-sticky-name" style="font-size:.92rem;font-weight:700;color:#e2e8f0;"><?= t('None selected') ?></div>
                            </div>
                        </div>
                        <?php break;
                    case 'duo-pass': ?>
                        <img src="<?= util_rank_img('lol', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name" id="dp-sticky-rank-name"><?= t('Silver') ?></span>
                        <?php break;
                    default: ?>
                        <img src="<?= util_rank_img('lol', 'mini', 3) ?>" alt="rank_icon">
                        <span class="title"><?= t('Silver I') ?></span>
                        <?php break;
                } ?>
            </div>
            <?php if ($data['type'] != 'coaching' && $data['type'] != 'ggirls') { ?>
                <img src="<?= ASSET_URL ?>/website/images/arrow-summary.svg" alt="arrow_icon">
            <?php } ?>
            <?php switch ($data['type']) {
                case 'ggirls': ?>
                    <?php break;
                case 'rank': ?>
                    <div class="to">
                        <img src="<?= util_rank_img('lol', 'mini', 4) ?>" alt="rank_icon" class="desired-summary-rank-img">
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

                case 'pro-games': ?>
                    <div class="to">
                        <div class="count">
                            <span id="pg-sticky-games" class="win-count">3</span><?= t('Games') ?>
                        </div>
                    </div>
                    <?php break;

                case 'duo-pass': ?>
                    <div class="to">
                        <div class="count">
                            <span id="dp-sticky-hours" class="dp-summary-hours" data-summary="hours">3</span> <?= t('Hours') ?>
                        </div>
                    </div>
                    <?php break;

                default: ?>
                    <div class="to">
                        <img src="<?= util_rank_img('lol', 'mini', 4) ?>" alt="rank_icon">
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
                <?php if ((int)($data['id'] ?? 0) !== 26): ?>
                <span class="price old-price" id="sticky-old-price"><?= t('€0.00') ?></span>
                <?php endif; ?>
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
<script src="<?= ASSET_URL ?>/website/js/boost-forms/lol.js?v=<?= time() ?>"></script>
<script>
<?php
    $lbDiscountRows = [];
    try {
        global $db;
        $lbDiscountRows = $db->run("SELECT code, amount, is_fixed FROM discounts WHERE status = 1 AND (max_uses IS NULL OR uses < max_uses) AND (starts_at IS NULL OR starts_at <= NOW()) AND (expires_at IS NULL OR expires_at >= NOW()) AND (services LIKE '%boosting%' OR services LIKE '%coaching%')") ?: [];
    } catch (Throwable $e) {
        $lbDiscountRows = [];
    }
?>

    const LB_AVAILABLE_DISCOUNTS = <?= json_encode(array_map(function ($row) {
        return [
            'code' => (string)($row['code'] ?? ''),
            'amount' => (float)($row['amount'] ?? 0),
            'is_fixed' => (int)($row['is_fixed'] ?? 0),
        ];
    }, $lbDiscountRows), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]' ?>;

    window.lbDiscountState = window.lbDiscountState || {
        basePrice: null,
        activeDiscount: null,
        applying: false
    };

    function lbEl(id) { return document.getElementById(id); }

    function lbCurrency(fromEl) {
        const text = fromEl && fromEl.textContent ? fromEl.textContent.trim() : '';
        return text.charAt(0) || '€';
    }

    function lbParsePrice(text) {
        const n = parseFloat(String(text || '').replace(/[^0-9.-]+/g, ''));
        return isNaN(n) ? 0 : n;
    }

    function lbFormatPrice(value, currency) {
        return (currency || '€') + Number(value || 0).toFixed(2);
    }

    function lbFindDiscount(code) {
        const needle = String(code || '').trim().toLowerCase();
        if (!needle) return null;
        return (LB_AVAILABLE_DISCOUNTS || []).find(function (discount) {
            return String(discount.code || '').trim().toLowerCase() === needle;
        }) || null;
    }

    function lbDisplayDiscountAmount(discount) {
        const code = String(discount && discount.code ? discount.code : '').trim().toUpperCase();
        const amount = Number(discount && discount.amount ? discount.amount : 0);
        const isFixed = Number(discount && discount.is_fixed ? discount.is_fixed : 0);

        if (isFixed) return amount;
        if (code === 'LB-TDAN3W') return 60;
        if (code.indexOf('LB-WC26-') === 0) return 70;

        return amount;
    }

    function lbDiscountLabel(discount) {
        const amount = Number(discount && discount.amount ? discount.amount : 0);
        const displayAmount = lbDisplayDiscountAmount(discount);
        return 'Special Price Applied';
    }

    function lbEffectiveDiscountAmount(discount) {
        const code = String(discount && discount.code ? discount.code : '').trim().toUpperCase();
        const amount = Number(discount && discount.amount ? discount.amount : 0);
        if (Number(discount && discount.is_fixed ? discount.is_fixed : 0)) return amount;
        if (code.indexOf('LB-REWARD-') === 0) return Math.min(90, 30 + amount);
        return amount;
    }

    function lbDiscountedFromBase(basePrice, discount) {
        const amount = lbEffectiveDiscountAmount(discount);
        if (Number(discount && discount.is_fixed ? discount.is_fixed : 0)) return Math.max(0, basePrice - amount);
        return Math.max(0, basePrice * (1 - amount / 100));
    }

    function lbBaseFromDiscounted(discountedPrice, discount) {
        const amount = lbEffectiveDiscountAmount(discount);
        if (Number(discount && discount.is_fixed ? discount.is_fixed : 0)) return discountedPrice + amount;
        if (amount >= 100) return discountedPrice;
        return discountedPrice / (1 - amount / 100);
    }

    function lbSetPriceEverywhere(price, currency) {
        const formatted = lbFormatPrice(price, currency);
        const totalPriceElement = lbEl('total-price');
        const newPriceElement = lbEl('new-price');
        const stickyTotalPrice = lbEl('sticky-total-price');
        if (totalPriceElement) totalPriceElement.textContent = formatted;
        if (newPriceElement) newPriceElement.textContent = formatted;
        if (stickyTotalPrice) stickyTotalPrice.textContent = formatted;
    }

    function lbRenderDiscountBox(basePrice, finalPrice, discount, changeTotal) {
        const totalPriceElement = lbEl('total-price');
        const oldPriceElement = lbEl('old-price');
        const newPriceElement = lbEl('new-price');
        const savedPriceElement = lbEl('saved-price');
        const discountInputBox = lbEl('discount-input');
        const discountBox = lbEl('discount-box');
        const discountMessage = lbEl('discount-message');
        const stickyOldPrice = lbEl('sticky-old-price');
        const stickyTotalPrice = lbEl('sticky-total-price');
        if (!totalPriceElement || !discountBox || !discountMessage) return;

        const currency = lbCurrency(totalPriceElement);
        const saved = Math.max(0, basePrice - finalPrice);
        window.lbDiscountState.applying = true;
        if (changeTotal) lbSetPriceEverywhere(finalPrice, currency);
        if (newPriceElement) newPriceElement.textContent = lbFormatPrice(finalPrice, currency);
        if (oldPriceElement) {
            oldPriceElement.innerHTML = lbFormatPrice(basePrice, currency);
            oldPriceElement.style.display = '';
            oldPriceElement.style.textDecoration = 'line-through';
        }
        if (savedPriceElement) savedPriceElement.textContent = lbFormatPrice(saved, currency);
        if (stickyOldPrice) {
            stickyOldPrice.innerHTML = lbFormatPrice(basePrice, currency);
            stickyOldPrice.style.display = '';
            stickyOldPrice.style.textDecoration = 'line-through';
        }
        if (stickyTotalPrice) stickyTotalPrice.textContent = lbFormatPrice(finalPrice, currency);
        if (discountInputBox) discountInputBox.style.display = 'none';
        const discountCodeElement = lbEl('discount_code');
        if (discountCodeElement) discountCodeElement.value = discount.code || '';
        const removeBtn = lbEl('remove-discount');
        if (removeBtn) removeBtn.style.display = '';
        discountMessage.textContent = 'Special Price Applied';
        discountMessage.setAttribute('data-discount', String(discount.amount));
        discountMessage.setAttribute('data-is-fixed', Number(discount.is_fixed || 0) ? '1' : '0');
        discountBox.style.display = 'flex';
        window.lbDiscountState.applying = false;
    }

    function updateOldPrice() {
        const totalPriceElement = lbEl('total-price');
        const discountBox = lbEl('discount-box');
        const discount = window.lbDiscountState.activeDiscount;
        if (!totalPriceElement || !discountBox || discountBox.style.display !== 'flex' || !discount) return;
        if (window.lbDiscountState.applying) return;
        const currentFinal = lbParsePrice(totalPriceElement.textContent);
        const basePrice = lbBaseFromDiscounted(currentFinal, discount);
        window.lbDiscountState.basePrice = basePrice;
        lbRenderDiscountBox(basePrice, currentFinal, discount, false);
    }

    function applyDiscount(discount, alreadyDiscounted) {
        if (!discount) return;
        discount = {
            code: String(discount.code || ''),
            amount: Number(discount.amount || 0),
            is_fixed: Number(discount.is_fixed || 0)
        };
        const totalPriceElement = lbEl('total-price');
        if (!totalPriceElement || !discount.amount) return;
        const currentPrice = lbParsePrice(totalPriceElement.textContent);
        let basePrice;
        let finalPrice;
        if (alreadyDiscounted) {
            finalPrice = currentPrice;
            basePrice = lbBaseFromDiscounted(finalPrice, discount);
        } else {
            basePrice = window.lbDiscountState.basePrice || currentPrice;
            finalPrice = lbDiscountedFromBase(basePrice, discount);
        }
        window.lbDiscountState.basePrice = basePrice;
        window.lbDiscountState.activeDiscount = discount;
        lbRenderDiscountBox(basePrice, finalPrice, discount, !alreadyDiscounted);
        const removeBtn = lbEl('remove-discount');
        if (removeBtn) removeBtn.style.display = '';
    }

    function removeDiscount() {
        const discountInputBox = lbEl('discount-input');
        const discountCodeElement = lbEl('discount_code');
        if (discountInputBox) discountInputBox.style.display = 'flex';
        if (discountCodeElement) {
            discountCodeElement.focus();
            if (typeof discountCodeElement.select === 'function') discountCodeElement.select();
        }
    }

    const totalPriceElementForDiscount = document.getElementById('total-price');
    if (totalPriceElementForDiscount && window.MutationObserver) {
        const totalPriceObserver = new MutationObserver(updateOldPrice);
        totalPriceObserver.observe(totalPriceElementForDiscount, { childList: true, characterData: true, subtree: true });
    }

    function lbInitDiscountControls() {
        const removeBtn = lbEl('remove-discount');
        if (removeBtn) {
            removeBtn.style.display = '';
            removeBtn.onclick = function (event) {
                event.preventDefault();
                removeDiscount();
            };
        }

        const discountCodeInput = lbEl('discount_code');
        if (discountCodeInput && !discountCodeInput.dataset.lbDiscountBound) {
            discountCodeInput.dataset.lbDiscountBound = '1';
            discountCodeInput.addEventListener('input', function () {
                const discount = lbFindDiscount(discountCodeInput.value);
                if (discount) applyDiscount(discount, false);
            });
        }
    }

    lbInitDiscountControls();

    document.addEventListener('DOMContentLoaded', function () {
        lbInitDiscountControls();
        const discountCodeElement = document.getElementById('discount_code');
        const defaultDiscount = lbFindDiscount('LB-TDAN3W');
        if (discountCodeElement && defaultDiscount) {
            applyDiscount(defaultDiscount, false);
            discountCodeElement.value = defaultDiscount.code || 'LB-TDAN3W';
            setTimeout(function () { applyDiscount(defaultDiscount, false); }, 150);
            setTimeout(function () { applyDiscount(defaultDiscount, false); }, 500);
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

    });

    // Pro Games sticky sync — use window so IIFE can also dispatch on window
    window.addEventListener('pg:boosterSelected', function(e) {
        var b = e.detail;
        var avatar = document.getElementById('pg-sticky-avatar');
        var name = document.getElementById('pg-sticky-name');
        var games = document.getElementById('pg-sticky-games');
        if (avatar && b.avatar) { avatar.src = b.avatar; avatar.style.display = 'block'; }
        if (name) name.textContent = b.name || '';
        if (games) games.textContent = b.games || 3;
        var priceEl = document.getElementById('sticky-total-price');
        if (priceEl) priceEl.textContent = '€' + ((b.totalPrice || 0) / 100).toFixed(2);
    });
    window.addEventListener('pg:cleared', function() {
        var avatar = document.getElementById('pg-sticky-avatar');
        var name = document.getElementById('pg-sticky-name');
        if (avatar) { avatar.src = ''; avatar.style.display = 'none'; }
        if (name) name.textContent = '<?= t("None selected") ?>';
        var priceEl = document.getElementById('sticky-total-price');
        if (priceEl) priceEl.textContent = '€0.00';
    });

    window.addEventListener('dp:update', function(e) {
        var d = e.detail || {};
        var rankEl = document.getElementById('dp-sticky-rank-name');
        var hoursEl = document.getElementById('dp-sticky-hours');
        var priceEl = document.getElementById('sticky-total-price');
        if (rankEl && d.rankName) rankEl.textContent = d.rankName;
        if (hoursEl && typeof d.hours !== 'undefined') hoursEl.textContent = String(d.hours);
        if (priceEl && typeof d.totalPrice !== 'undefined') priceEl.textContent = '€' + ((d.totalPrice || 0) / 100).toFixed(2);
    });

    // Scroll to top of page content (same approach as shop.php)
    // Scroll to top of page content.
    // Important: use the real fixed-header stack height, including the sale banner.
    // Otherwise /lol/rank-boost lands too high when the banner is still open.
    function getLolFixedTopOffset() {
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

