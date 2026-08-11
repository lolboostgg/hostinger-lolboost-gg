<?php
$uiGame = ($data['game'] ?? 'lol');
if ($uiGame === 'tft') { $uiGame = 'lol'; }
?>
<div class="boost win-boost ranked-5s-boost">
    <div class="card">
        <div class="card-header">
            <img src="<?= util_rank_img($uiGame, 'mini', 3); ?>" alt="rank_icon" class="card-header-rank current-rank-img">
            <div class="text">
                <h3><?= t('Current Rank') ?></h3>
                <p><?= t('Select the rank you will play Ranked 5s from.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="ranks">
                <?php for ($i = 0; $i <= 8; $i++): ?>
                    <?php if ($i === 8) continue; // Master: no 5-stack allowed ?>
                    <label>
                        <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 3 ? 'checked' : ''; ?>>
                        <div class="rank-btn">
                            <img src="<?= util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= $ranks[$i] ?? 'Unranked'; ?>">
                            <span class="tooltip"><?= $ranks[$i] ?? 'Unranked'; ?></span>
                        </div>
                    </label>
                <?php endfor; ?>
            </div>
            <div class="divisions" id="start_divisions">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <label>
                        <input type="radio" name="start_division" id="start_div_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 4 ? 'checked' : ''; ?>>
                        <div class="division-btn"><?= util_format_lol_division($i); ?></div>
                    </label>
                <?php endfor; ?>
            </div>
            <div class="lp-selector" id="start_lp_full" style="display:none">
                <h6><?= t('Current LP:') ?></h6>
                <div class="input-container">
                    <button type="button" onclick="decrementValue(startLPInput)"><i class="fas fa-circle-minus"></i></button>
                    <input type="text" name="start_lp_full" id="start_lp_input" value="0" min="0" max="1500" step="100">
                    <button type="button" onclick="incrementValue(startLPInput)"><i class="fas fa-circle-plus"></i></button>
                </div>
            </div>
            <div class="options ranked-5s-server-only">
                <div class="option ranked-5s-server-option">
                    <h6><?= t('Server') ?></h6>
                    <select class="select2" name="server" data-no-search="true">
                        <option value="euw" selected=""><?= t('EU-West') ?></option>
                        <option value="na"><?= t('North America') ?></option>
                        <option value="me"><?= t('Middle East') ?></option>
                        <option value="eune"><?= t('EU-Nordic & East') ?></option>
                        <option value="br"><?= t('Brazil') ?></option>
                        <option value="oce"><?= t('Oceania') ?></option>
                        <option value="ru"><?= t('Russia') ?></option>
                        <option value="tr"><?= t('Turkey') ?></option>
                        <option value="lan"><?= t('Latin America North') ?></option>
                        <option value="las"><?= t('Latin America South') ?></option>
                        <option value="jp"><?= t('Japan') ?></option>
                        <option value="vn"><?= t('Vietnam') ?></option>
                        <option value="ph"><?= t('Philippines') ?></option>
                        <option value="sg"><?= t('Singapore') ?></option>
                        <option value="th"><?= t('Thailand') ?></option>
                        <option value="tw"><?= t('Taiwan') ?></option>
                    </select>
                </div>
                <input type="hidden" name="queue_type" value="ranked_5s">
                <input type="hidden" name="is_duo" value="1">
                <input type="hidden" name="is_hidden_duo" value="0">
            </div>
        </div>
    </div>

    <div class="card count-card">
        <div class="card-header">
            <div class="count win-count"><?= t('3') ?></div>
            <div class="text">
                <h3><?= t('Games Amount') ?></h3>
                <p><?= t('Select how many Ranked 5s games you want to play.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="matches_slider1"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" value="3" min="1" max="5" hidden>
        </div>
    </div>



    <div class="r5s-role-modal" id="ranked5sRoleModal" aria-hidden="true">
        <div class="r5s-role-modal__backdrop" data-r5s-role-close></div>
        <div class="r5s-role-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ranked5sRoleTitle">
            <button type="button" class="r5s-role-modal__close" data-r5s-role-close aria-label="<?= t('Close') ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="r5s-role-modal__hero">
                <div class="r5s-role-modal__icon">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <div class="r5s-role-modal__hero-copy">
                    <h3 id="ranked5sRoleTitle"><?= t('Choose your lane') ?></h3>
                    <p>
                        <span class="r5s-role-modal__desc-desktop"><?= t('Ranked 5s is always played with boosters. You stay on your own account, so choose the lane you will play yourself before checkout.') ?></span>
                        <span class="r5s-role-modal__desc-mobile"><?= t('Pick the lane you want to play yourself.') ?></span>
                    </p>
                </div>
            </div>

            <div class="r5s-role-modal__section-label"><?= t('YOUR LANE') ?></div>

            <div class="ranked-5s-role-grid r5s-role-modal__grid">
                <?php
                $ranked5sRoles = [
                    'TopLane' => 'TopLane',
                    'Jungle' => 'Jungle',
                    'MidLane' => 'MidLane',
                    'AdCarry' => 'AdCarry',
                    'Support' => 'Support',
                ];
                foreach ($ranked5sRoles as $roleValue => $roleLabel):
                ?>
                    <input type="radio" name="roles[]" id="r5s_role_<?= $roleValue ?>" value="<?= $roleValue ?>" class="ranked-5s-role-input">
                    <label class="ranked-5s-role-option" for="r5s_role_<?= $roleValue ?>">
                        <span class="ranked-5s-role-option__icon">
                            <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= $roleValue ?>.png" alt="<?= $roleLabel ?>" onerror="this.style.display='none';this.parentNode.classList.add('is-fallback');">
                            <i class="<?= [
                                'TopLane' => 'fa-solid fa-square',
                                'Jungle' => 'fa-solid fa-leaf',
                                'MidLane' => 'fa-solid fa-diamond',
                                'AdCarry' => 'fa-solid fa-crosshairs',
                                'Support' => 'fa-solid fa-wand-magic-sparkles',
                            ][$roleValue] ?? 'fa-solid fa-circle' ?>"></i>
                        </span>
                        <span><?= $roleLabel ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="button" class="r5s-role-modal__continue" id="ranked5sRoleContinue" disabled>
                <?= t('Continue to Checkout') ?>
                <i class="fa-solid fa-arrow-right-long"></i>
            </button>
        </div>
    </div>

    <div class="card count-card ranked-5s-boosters-card">
        <div class="card-header ranked-5s-booster-header">
            <div class="count booster-count"><?= t('1') ?></div>
            <div class="text">
                <h3><?= t('Boosters') ?></h3>
                <p><?= t('Choose your boosters. You need a full premade of 5 to play.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="boosters_slider"></div>
            <input class="form-control range-slider-value-min" name="boosters" type="number" value="1" min="1" max="4" hidden>
        </div>
    </div>
</div>

<style>

.ranked-5s-boost .ranked-5s-role-card {
    display: none !important;
}
.ranked-5s-booster-header {
    align-items: center !important;
    gap: clamp(14px, 1.1vw, 22px) !important;
}
.ranked-5s-booster-header .text {
    min-width: 0;
    flex: 1 1 auto;
}
@media (max-width: 980px) {
    .ranked-5s-booster-header {
        flex-wrap: wrap;
    }
}

.r5s-role-modal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
}
.r5s-role-modal.is-open {
    display: flex;
}
.r5s-role-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(4, 5, 16, .82);
    backdrop-filter: blur(16px);
}
.r5s-role-modal__dialog {
    position: relative;
    width: min(920px, 100%);
    padding: 44px;
    border-radius: 32px;
    border: 1px solid rgba(128, 128, 255, .18);
    background:
        radial-gradient(circle at top right, rgba(107, 91, 255, .20), transparent 34%),
        linear-gradient(180deg, rgba(25, 22, 58, .98) 0%, rgba(12, 11, 31, .98) 100%);
    box-shadow: 0 34px 110px rgba(0, 0, 0, .46), inset 0 1px 0 rgba(255,255,255,.04);
    color: #fff;
    overflow: hidden;
}
.r5s-role-modal__dialog::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    border-radius: inherit;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.02);
}
.r5s-role-modal__close {
    position: absolute;
    top: 24px;
    right: 24px;
    width: 46px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.82);
    cursor: pointer;
    transition: background .18s ease, border-color .18s ease, transform .18s ease;
}
.r5s-role-modal__close:hover {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.18);
    transform: translateY(-1px);
}
.r5s-role-modal__hero {
    display: flex;
    align-items: flex-start;
    gap: 22px;
    margin-bottom: 30px;
}
.r5s-role-modal__icon {
    width: 78px;
    height: 78px;
    flex: 0 0 78px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 22px;
    background: linear-gradient(135deg, #6670ff 0%, #8a5cff 100%);
    color: #fff;
    font-size: 32px;
    box-shadow: 0 18px 36px rgba(99,102,241,.26);
}
.r5s-role-modal__hero-copy {
    max-width: 680px;
}
.r5s-role-modal h3 {
    margin: 4px 0 12px;
    font-size: 32px;
    line-height: 1.12;
    font-weight: 1000;
    letter-spacing: -.02em;
}
.r5s-role-modal p {
    margin: 0;
    color: rgba(255,255,255,.62);
    font-size: 16px;
    line-height: 1.6;
    font-weight: 700;
}
.r5s-role-modal__desc-mobile { display: none; }
@media (max-width: 760px) {
    .r5s-role-modal__desc-desktop { display: none; }
    .r5s-role-modal__desc-mobile { display: inline; }
}
.r5s-role-modal__section-label {
    margin-bottom: 18px;
    color: #aeb3ff;
    font-size: 13px;
    line-height: 1;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.ranked-5s-duo-note {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 14px 16px;
    border: 1px solid rgba(114,120,255,.28);
    border-radius: 14px;
    background: rgba(95, 101, 241, .10);
    color: rgba(255,255,255,.86);
    font-size: 13px;
    font-weight: 800;
}
.ranked-5s-duo-note i {
    color: #98a0ff;
    font-size: 15px;
}
.r5s-role-modal__grid,
.ranked-5s-role-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}
.ranked-5s-role-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.ranked-5s-role-option {
    min-height: 142px;
    padding: 22px 12px 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 18px;
    background: rgba(255,255,255,.035);
    cursor: pointer;
    transition: transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
}
.ranked-5s-role-option img {
    width: 50px;
    height: 50px;
    object-fit: contain;
    opacity: .52;
    filter: grayscale(.20);
    transition: opacity .18s ease, filter .18s ease, transform .18s ease;
}
.ranked-5s-role-option span {
    color: rgba(255,255,255,.74);
    font-size: 15px;
    font-weight: 950;
    line-height: 1.1;
}
.ranked-5s-role-option:hover {
    transform: translateY(-1px);
    border-color: rgba(139,140,255,.46);
    background: rgba(99,102,241,.08);
}
.ranked-5s-role-input:checked + .ranked-5s-role-option {
    border-color: rgba(139,140,255,.92);
    background: linear-gradient(180deg, rgba(93,95,222,.16), rgba(66,67,150,.12));
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.06), 0 16px 30px rgba(99,102,241,.14);
}
.ranked-5s-role-input:checked + .ranked-5s-role-option img {
    opacity: 1;
    filter: none;
    transform: scale(1.06);
}
.ranked-5s-role-input:checked + .ranked-5s-role-option span {
    color: #fff;
}
.r5s-role-modal__continue {
    width: 100%;
    height: 66px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, #6366f1 0%, #8959ff 100%);
    color: #fff;
    font-size: 19px;
    font-weight: 1000;
    cursor: pointer;
    box-shadow: 0 18px 36px rgba(99,102,241,.24);
    transition: opacity .18s ease, transform .18s ease, box-shadow .18s ease;
}
.r5s-role-modal__continue:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 22px 38px rgba(99,102,241,.28);
}
.r5s-role-modal__continue:disabled {
    opacity: .45;
    cursor: not-allowed;
    box-shadow: none;
}
body.r5s-role-modal-open {
    overflow: hidden;
}

.ranked-5s-role-option__icon {
    width: 58px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ranked-5s-role-option__icon i {
    display: none;
    color: rgba(255,255,255,.48);
    font-size: 34px;
}
.ranked-5s-role-option__icon.is-fallback i {
    display: inline-flex;
}
.ranked-5s-role-option__icon.is-fallback img {
    display: none !important;
}
.ranked-5s-role-input:checked + .ranked-5s-role-option .ranked-5s-role-option__icon i {
    color: #ffffff;
}

/* ==========================================================
   Ranked 5s — Lane modal (mobile)
   Compact, auto-height, centered. Sized in vw for scaling.
   ========================================================== */
@media (max-width: 760px) {
    .r5s-role-modal {
        padding: 3vw;
        align-items: center;
        justify-content: center;
    }
    .r5s-role-modal__backdrop {
        background: rgba(5, 6, 18, .88);
        backdrop-filter: blur(14px);
    }
    .r5s-role-modal__dialog {
        width: 100%;
        max-width: 94vw;
        min-height: 0;
        max-height: calc(100dvh - 6vw);
        border-radius: 5vw;
        padding: 6vw 5vw 5vw;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        gap: 4.5vw;
        background:
            radial-gradient(circle at top right, rgba(107, 91, 255, .22), transparent 44%),
            linear-gradient(180deg, rgba(24, 21, 58, .99) 0%, rgba(10, 9, 27, .99) 100%);
    }
    .r5s-role-modal__close {
        top: 3.5vw;
        right: 3.5vw;
        width: 9.5vw;
        height: 9.5vw;
        border-radius: 3vw;
        font-size: 4vw;
    }
    .r5s-role-modal__hero {
        flex: 0 0 auto;
        flex-direction: row;
        align-items: center;
        gap: 3.5vw;
        margin: 0;
        padding-right: 11vw;
    }
    .r5s-role-modal__icon {
        width: 13vw;
        height: 13vw;
        flex: 0 0 13vw;
        border-radius: 3.8vw;
        font-size: 5.6vw;
    }
    .r5s-role-modal h3 {
        margin: 0 0 1vw;
        font-size: 5.6vw;
        line-height: 1.15;
    }
    .r5s-role-modal p {
        font-size: 3.6vw;
        line-height: 1.45;
    }
    .r5s-role-modal__section-label {
        flex: 0 0 auto;
        margin: 0;
        font-size: 3.2vw;
        letter-spacing: .10em;
    }
    .ranked-5s-role-grid,
    .r5s-role-modal__grid {
        flex: 0 0 auto;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 3vw;
        margin: 0;
    }
    /* Odd-one-out (Support) spans full width */
    .ranked-5s-role-grid > label:last-child:nth-child(odd),
    .r5s-role-modal__grid > label:last-child:nth-child(odd) {
        grid-column: 1 / -1;
    }
    .ranked-5s-role-option {
        min-height: 0;
        height: auto;
        padding: 4.5vw 3vw;
        gap: 2.5vw;
        border-radius: 4vw;
    }
    .ranked-5s-role-option__icon,
    .ranked-5s-role-option img {
        width: 11vw;
        height: 11vw;
    }
    .ranked-5s-role-option__icon i {
        font-size: 6vw;
    }
    .ranked-5s-role-option span:last-child {
        font-size: 3.8vw;
    }
    .r5s-role-modal__continue {
        flex: 0 0 auto;
        height: 14vw;
        border-radius: 4vw;
        font-size: 4.2vw;
    }
}

@media (max-width: 420px) {
    /* Kept lightweight — mostly a hint that dialog stays comfortable */
    .r5s-role-modal__dialog {
        padding: 6vw 4.5vw 5vw;
    }
}

</style>

<script>
(function(){

    function syncRanked5sRankSections(){
        var checked = document.querySelector('.ranked-5s-boost input[name="start_tier"]:checked');
        var tier = checked ? parseInt(checked.value, 10) : 3;
        var divisions = document.getElementById('start_divisions');
        var lpFull = document.getElementById('start_lp_full');

        if (divisions) {
            divisions.style.display = (tier === 0 || tier >= 8) ? 'none' : '';
        }
        if (lpFull) {
            lpFull.style.display = tier >= 8 ? '' : 'none';
        }
    }

    function syncRanked5sUnrankedSummary(){
        var checked = document.querySelector('.ranked-5s-boost input[name="start_tier"]:checked');
        var tier = checked ? parseInt(checked.value, 10) : 3;
        if (tier !== 0) return;

        document.querySelectorAll('.current-summary-rank-name').forEach(function(el){
            el.textContent = 'Unranked';
        });
        document.querySelectorAll('.current-summary-lp, .r5s-current-summary-lp').forEach(function(el){
            el.style.display = 'none';
        });
    }

    document.addEventListener('change', function(e){
        if (e.target && e.target.name === 'start_tier') {
            syncRanked5sRankSections();
            setTimeout(syncRanked5sUnrankedSummary, 0);
            setTimeout(syncRanked5sUnrankedSummary, 80);
        }
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function(){
            syncRanked5sRankSections();
            syncRanked5sUnrankedSummary();
            setTimeout(syncRanked5sUnrankedSummary, 250);
        });
    } else {
        syncRanked5sRankSections();
        syncRanked5sUnrankedSummary();
        setTimeout(syncRanked5sUnrankedSummary, 250);
    }

    function initRanked5sRoleModal(){
        var form = document.getElementById('lol_boost_form');
        var modal = document.getElementById('ranked5sRoleModal');
        var continueBtn = document.getElementById('ranked5sRoleContinue');
        if (!form || !modal || !continueBtn) return;

        var lastSubmitter = null;
        var isSubmittingAfterRole = false;
        var allowCheckoutClick = false;

        function selectedRole(){
            return modal.querySelector('input[name="roles[]"]:checked');
        }

        function syncContinue(){
            continueBtn.disabled = !selectedRole();
        }

        function openModal(){
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('r5s-role-modal-open');
            syncContinue();
        }

        function closeModal(){
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('r5s-role-modal-open');
        }

        document.addEventListener('click', function(e){
            var btn = e.target && e.target.closest ? e.target.closest('#start_boost, #sticky_start_boost, .buy-now') : null;
            if (btn && form.contains(btn)) {
                lastSubmitter = btn;

                if (!allowCheckoutClick && !selectedRole()) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
                    openModal();
                    return false;
                }

                if (allowCheckoutClick) {
                    allowCheckoutClick = false;
                }
            }

            if (e.target && e.target.closest && e.target.closest('[data-r5s-role-close]')) {
                e.preventDefault();
                closeModal();
            }
        }, true);

        modal.querySelectorAll('input[name="roles[]"]').forEach(function(input){
            input.addEventListener('change', syncContinue);
        });

        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        form.addEventListener('submit', function(e){
            if (isSubmittingAfterRole) return;
            if (!selectedRole()) {
                e.preventDefault();
                e.stopPropagation();
                openModal();
            }
        }, true);

        continueBtn.addEventListener('click', function(){
            if (!selectedRole()) {
                syncContinue();
                return;
            }
            closeModal();
            isSubmittingAfterRole = true;
            allowCheckoutClick = true;

            var targetBtn = lastSubmitter || form.querySelector('#start_boost') || form.querySelector('#sticky_start_boost') || form.querySelector('button[type="submit"]');
            if (targetBtn && typeof targetBtn.click === 'function') {
                targetBtn.click();
            } else if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                var ev = new Event('submit', { bubbles: true, cancelable: true });
                form.dispatchEvent(ev);
            }

            setTimeout(function(){
                isSubmittingAfterRole = false;
                allowCheckoutClick = false;
            }, 1200);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRanked5sRoleModal);
    } else {
        initRanked5sRoleModal();
    }

    function setNativeInputEvents(input){
        if (!input) return;
        try {
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) {}
    }

    function clampRanked5sGames(){
        var slider = document.getElementById('matches_slider1');
        var input = document.querySelector('input[name="matches0"]');
        var count = document.querySelector('.ranked-5s-boost .count-card .win-count');
        if (input) { input.min = '1'; input.max = '5'; }
        if (!slider || !slider.noUiSlider) return false;
        try {
            slider.noUiSlider.updateOptions({ range: { min: 1, max: 5 }, step: 1 }, false);
            var value = parseInt(slider.noUiSlider.get(), 10) || 3;

            if (!slider.dataset.r5sDefaultApplied) {
                value = 3;
                slider.dataset.r5sDefaultApplied = '1';
            }

            value = Math.max(1, Math.min(5, value));
            slider.noUiSlider.set(value);
            if (input) { input.value = value; setNativeInputEvents(input); }
            if (count) count.textContent = value;
        } catch (e) {}
        return true;
    }

    function updateRanked5sBoosterHint(){}

    function clampRanked5sBoosters(){
        var slider = document.getElementById('boosters_slider');
        var input = document.querySelector('input[name="boosters"]');
        var count = document.querySelector('.booster-count');
        if (input) { input.min = '1'; input.max = '4'; }
        if (!slider || !slider.noUiSlider) {
            updateRanked5sBoosterHint();
            return false;
        }
        try {
            slider.noUiSlider.updateOptions({ range: { min: 1, max: 4 }, step: 1 }, false);

            if (!slider.dataset.r5sHintBound) {
                slider.noUiSlider.on('update', function(){
                    var liveValue = parseInt(slider.noUiSlider.get(), 10) || 1;
                    liveValue = Math.max(1, Math.min(4, liveValue));
                    if (input) {
                        input.value = liveValue;
                        setNativeInputEvents(input);
                    }
                    if (count) count.textContent = liveValue;
                    updateRanked5sBoosterHint();
                });
                slider.dataset.r5sHintBound = '1';
            }

            var value = parseInt(slider.noUiSlider.get(), 10) || 1;
            value = Math.max(1, Math.min(4, value));
            slider.noUiSlider.set(value);
            if (input) { input.value = value; setNativeInputEvents(input); }
            if (count) count.textContent = value;
            updateRanked5sBoosterHint();
        } catch (e) {}
        return true;
    }
    var tries = 0;
    var timer = setInterval(function(){
        tries++;
        var gamesReady = clampRanked5sGames();
        var boostersReady = clampRanked5sBoosters();
        if ((gamesReady && boostersReady) || tries > 40) clearInterval(timer);
    }, 100);
    document.addEventListener('DOMContentLoaded', function(){
        clampRanked5sGames();
        clampRanked5sBoosters();
    });
})();
</script>
