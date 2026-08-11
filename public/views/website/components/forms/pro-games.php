<?php
// website/components/forms/pro-games.php — v3
// Flow: Modal (rank+server) → Booster cards with per-card game selector
$uiGame = ($data['game'] ?? 'lol');
$isClassic = util_is_lol_classic($uiGame);
$rankNames = $isClassic ? [1=>'Salt',2=>'Wood',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Legend'] : [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
$defaultTier = $isClassic ? 1 : 3;
$defaultDivision = 4;
$apexFrom = $isClassic ? 7 : 8;

if (!function_exists('lol_classic_rank_asset_url')) {
    function lol_classic_rank_asset_url($tier, $division = 5) {
        return util_lol_classic_rank_img((int)$tier);
    }
}
?>

<!-- Hidden form inputs -->
<input type="hidden" name="selected_booster_id" id="pg-booster-id" value="">
<input type="hidden" name="start_tier" id="pg-start-tier" value="<?= $defaultTier ?>">
<input type="hidden" name="start_division" id="pg-start-division" value="<?= $defaultDivision ?>">
<input type="hidden" name="matches" id="pg-matches" value="3">
<input type="hidden" name="server" id="pg-server-hidden" value="euw">
<input type="hidden" name="queue_type" value="solo_/_duo">

<div class="boost win-boost">

    <!-- Modal Overlay -->
    <div id="pg-modal-overlay" class="pg-modal-overlay">
        <div class="pg-modal">
            <div class="pg-modal__header">
                <div class="pg-modal__icon">
                    <i class="fa-solid fa-gamepad-modern"></i>
                </div>
                <div>
                    <h2 class="pg-modal__title"><?= t('Set Up Your Session') ?></h2>
                    <p class="pg-modal__sub"><?= t('Choose your rank and server to see available boosters and pricing') ?></p>
                </div>
            </div>

            <div class="pg-modal__body">
                <div class="pg-modal__field">
                    <label class="pg-modal__label"><?= t('Current Rank') ?></label>
                    <?php $modalRanksTop = $isClassic ? $rankNames : array_slice($rankNames, 0, 6, true); ?>
                    <?php $modalRanksBottom = $isClassic ? [] : array_slice($rankNames, 6, null, true); ?>
                    <div class="pg-rank-grid <?= $isClassic ? 'pg-rank-grid--classic' : '' ?>" id="pg-modal-ranks">
                        <div class="pg-rank-grid__row pg-rank-grid__row--top">
                            <?php foreach ($modalRanksTop as $tier => $rankLabel): ?>
                                <button
                                    type="button"
                                    class="pg-rank-btn <?= $tier == $defaultTier ? 'active' : '' ?>"
                                    data-tier="<?= $tier ?>"
                                >
                                    <img src="<?= $isClassic ? lol_classic_rank_asset_url($tier, $defaultDivision) : util_rank_img($uiGame, 'mini', $tier) ?>" alt="<?= $rankLabel ?>">
                                    <span><?= $rankLabel ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($modalRanksBottom)): ?>
                        <div class="pg-rank-grid__row pg-rank-grid__row--bottom">
                            <?php foreach ($modalRanksBottom as $tier => $rankLabel): ?>
                                <button
                                    type="button"
                                    class="pg-rank-btn <?= $tier == $defaultTier ? 'active' : '' ?>"
                                    data-tier="<?= $tier ?>"
                                >
                                    <img src="<?= $isClassic ? lol_classic_rank_asset_url($tier, $defaultDivision) : util_rank_img($uiGame, 'mini', $tier) ?>" alt="<?= $rankLabel ?>">
                                    <span><?= $rankLabel ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pg-modal__field pg-division-field" id="pg-modal-division-field">
                    <label class="pg-modal__label"><?= t('Current Division') ?></label>
                    <div class="pg-division-grid" id="pg-modal-divisions">
                        <?php if ($isClassic): ?>
                            <button type="button" class="pg-division-btn active" data-division="4">IV</button>
                            <button type="button" class="pg-division-btn" data-division="3">III</button>
                            <button type="button" class="pg-division-btn" data-division="2">II</button>
                            <button type="button" class="pg-division-btn" data-division="1">I</button>
                        <?php else: ?>
                            <button type="button" class="pg-division-btn active" data-division="4">IV</button>
                            <button type="button" class="pg-division-btn" data-division="3">III</button>
                            <button type="button" class="pg-division-btn" data-division="2">II</button>
                            <button type="button" class="pg-division-btn" data-division="1">I</button>
                        <?php endif; ?>
                    </div>
                    <p class="pg-division-help"><?= t('Select your exact division so the booster knows your current elo.') ?></p>
                </div>

                <div class="pg-modal__field">
                    <label class="pg-modal__label"><?= t('Server') ?></label>
                    <!-- Hidden select keeps JS logic intact -->
                    <select id="pg-modal-server" style="display:none;">
                        <option value="euw">EU-West</option>
                        <option value="na">North America</option>
                        <option value="me">Middle East</option>
                        <option value="eune">EU-Nordic &amp; East</option>
                        <option value="br">Brazil</option>
                        <option value="oce">Oceania</option>
                        <option value="ru">Russia</option>
                        <option value="tr">Turkey</option>
                        <option value="lan">Latin America North</option>
                        <option value="las">Latin America South</option>
                        <option value="jp">Japan</option>
                    </select>
                    <!-- Custom dropdown with flags -->
                    <div class="pg-srv-drop" id="pg-srv-drop">
                        <div class="pg-srv-trigger" id="pg-srv-trigger">
                            <span class="pg-srv-flag"><i class="fa-solid fa-earth-europe"></i></span>
                            <span class="pg-srv-name">EU-West</span>
                            <i class="fa-solid fa-chevron-down pg-srv-arrow"></i>
                        </div>
                        <div class="pg-srv-list" id="pg-srv-list">
                            <div class="pg-srv-opt" data-value="euw"><span><i class="fa-solid fa-earth-europe"></i></span><?= t('EU-West') ?></div>
                            <div class="pg-srv-opt" data-value="na"><span><i class="fa-solid fa-earth-americas"></i></span><?= t('North America') ?></div>
                            <div class="pg-srv-opt" data-value="me"><span><i class="fa-solid fa-earth-asia"></i></span><?= t('Middle East') ?></div>
                            <div class="pg-srv-opt" data-value="eune"><span><i class="fa-solid fa-earth-europe"></i></span><?= t('EU-Nordic &amp; East') ?></div>
                            <div class="pg-srv-opt" data-value="br"><span><i class="fa-solid fa-earth-americas"></i></span><?= t('Brazil') ?></div>
                            <div class="pg-srv-opt" data-value="oce"><span><i class="fa-solid fa-earth-oceania"></i></span><?= t('Oceania') ?></div>
                            <div class="pg-srv-opt" data-value="ru"><span><i class="fa-solid fa-earth-europe"></i></span><?= t('Russia') ?></div>
                            <div class="pg-srv-opt" data-value="tr"><span><i class="fa-solid fa-earth-europe"></i></span><?= t('Turkey') ?></div>
                            <div class="pg-srv-opt" data-value="lan"><span><i class="fa-solid fa-earth-americas"></i></span><?= t('Latin America North') ?></div>
                            <div class="pg-srv-opt" data-value="las"><span><i class="fa-solid fa-earth-americas"></i></span><?= t('Latin America South') ?></div>
                            <div class="pg-srv-opt" data-value="jp"><span><i class="fa-solid fa-earth-asia"></i></span><?= t('Japan') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="pg-modal__cta" id="pg-modal-confirm">
                <?= t('Find My Boosters') ?> <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- Main Card: Booster Selection -->
    <div class="card" id="pg-booster-card" style="zoom:1;">
        <div class="card-header">
            <div class="pg-header-icon"><i class="fa-solid fa-rocket"></i></div>
            <div class="text">
                <h3><?= t('Choose Your Booster') ?></h3>
                <p id="pg-header-sub"><?= t('Pick who you want to play with') ?></p>
            </div>
            <button type="button" id="pg-change-rank-btn" class="pg-change-rank-btn" style="display:none;">
                <img id="pg-header-rank-img" src="" alt="" style="height:42px;width:42px;object-fit:contain;">
                <span id="pg-header-rank-txt" style="font-size:0.9vw;font-weight:700;"></span>
                <i class="fa-solid fa-pen-to-square" style="font-size:0.65vw;opacity:.7;"></i>
            </button>
        </div>
        <hr>
        <div class="card-body">

            <!-- Rank Pills -->
            <div class="pg-rank-pills">
                <?php $pgRanks = $isClassic ? [1=>'Salt',2=>'Wood',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Legend'] : [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger']; ?>
                <?php foreach ($pgRanks as $pgT => $pgN): ?>
                <button
                    type="button"
                    class="pg-rpill<?= (int)$pgT===(int)$defaultTier?' active':'' ?>"
                    data-tier="<?= $pgT ?>"
                >
                    <img src="<?= $isClassic ? lol_classic_rank_asset_url($pgT, $defaultDivision) : util_rank_img($uiGame, 'mini', $pgT) ?>" alt="<?= $pgN ?>">
                    <?= $pgN ?>
                </button>
                <?php endforeach; ?>
            </div>

            <div class="pg-division-pills" id="pg-main-division-field">
                <span class="pg-division-pills__label"><?= t('Division') ?></span>
                <?php if ($isClassic): ?>
                    <button type="button" class="pg-division-pill active" data-division="4">IV</button>
                    <button type="button" class="pg-division-pill" data-division="3">III</button>
                    <button type="button" class="pg-division-pill" data-division="2">II</button>
                    <button type="button" class="pg-division-pill" data-division="1">I</button>
                <?php else: ?>
                    <button type="button" class="pg-division-pill active" data-division="4">IV</button>
                    <button type="button" class="pg-division-pill" data-division="3">III</button>
                    <button type="button" class="pg-division-pill" data-division="2">II</button>
                    <button type="button" class="pg-division-pill" data-division="1">I</button>
                <?php endif; ?>
            </div>

            <div class="pg-filters">
                <input type="text" id="pg-filter-name" class="pg-filter-input"
                       placeholder="<?= t('Search by name...') ?>">
                <!-- Hidden select (used for filtering logic); UI is the custom dropdown below -->
                <select class="pg-sort-select" id="pg-lang" style="display:none;">
                    <option value="all"><?= t('Language: All') ?></option>
                </select>

                <!-- Language dropdown (custom, with flags) -->
                <div class="pg-lang-dd" id="pg-lang-dd">
                    <button type="button" class="pg-lang-dd__btn" id="pg-lang-btn" aria-expanded="false">
                        <div class="pg-lang-dd__flag">
                            <img id="pg-lang-flag" src="" alt="" style="display:none;">
                            <i id="pg-lang-globe" class="fa-solid fa-globe" style="display:block;"></i>
                        </div>
                        <div class="pg-lang-dd__text">
                            <div class="pg-lang-dd__caption"><?= t('Language') ?></div>
                            <div class="pg-lang-dd__value" id="pg-lang-value"><?= t('All') ?></div>
                        </div>
                        <i class="fa-solid fa-chevron-down pg-lang-dd__chev"></i>
                    </button>
                    <div class="pg-lang-dd__menu" id="pg-lang-menu"></div>
                </div>
                <select class="pg-sort-select" id="pg-sort">
                    <option value="default"><?= t('Sort: Default') ?></option>
                    <option value="price_asc"><?= t('Price ↑') ?></option>
                    <option value="price_desc"><?= t('Price ↓') ?></option>
                    <option value="rating"><?= t('Best Rating') ?></option>
                    <option value="online"><?= t('Online First') ?></option>
                </select>
                <button type="button" class="pg-online-btn active" id="pg-online-toggle">
                    <span class="pg-dot"></span><?= t('Online only') ?>
                </button>
            </div>

            <div class="pg-results-count" id="pg-results-count">Found 0 active pro gamers</div>

            <div class="pg-list" id="pg-boosters-grid"></div>
            <div class="pg-pagination" id="pg-pagination"></div>

        </div>
    </div>

</div>

<style>
/* ── Modal ───────────────────────────────────── */
/* ── Card wrapper ────────────────────────────── */
#pg-booster-card .card-header {
    padding-bottom: 0;
    align-items: center !important;
}
.pg-header-icon {
    display: flex; align-items: center; justify-content: center;
    width: 2.6vw; height: 2.6vw; border-radius: 0.6vw; flex-shrink: 0;
    background: linear-gradient(135deg, rgba(124,92,252,.25), rgba(99,102,241,.15));
    border: 0.05vw solid rgba(124,92,252,.3);
    box-shadow: 0 0 1vw rgba(124,92,252,.2);
}
.pg-header-icon i {
    font-size: 1.2vw; color: #a78bfa;
    background: linear-gradient(135deg, #a78bfa, #6366f1);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}
.pg-modal-overlay {
    position: fixed; inset: 0; z-index: 9999999;
    background: rgba(0,0,0,.75);
    backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.pg-modal-overlay.hidden { display: none; }

.pg-modal {
    background: #13112a;
    border: 1px solid rgba(124,92,252,.25);
    border-radius: 20px;
    padding: 32px;
    width: 100%; max-width: 520px;
    box-shadow: 0 24px 64px rgba(0,0,0,.6), 0 0 0 1px rgba(124,92,252,.1);
}

.pg-modal__header {
    display: flex; gap: 16px; align-items: flex-start;
    margin-bottom: 28px;
}
.pg-modal__icon {
    width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #fff;
}
.pg-modal__title { font-size: 1.8rem; font-weight: 800; color: #fff; margin: 0 0 6px; }
.pg-modal__sub { font-size: 1.05rem; color: #726e8e; margin: 0; line-height: 1.5; }

@media (min-width: 1024px) {
    .pg-modal {
        max-width: 780px;
        padding: 30px 36px;
        border-radius: 22px;
    }
    .pg-modal__icon {
        width: 56px; height: 56px; border-radius: 16px;
        font-size: 1.55rem;
    }
    .pg-modal__header { gap: 16px; margin-bottom: 24px; align-items:center; }
    .pg-modal__title { font-size: 1.75rem; }
    .pg-modal__sub { font-size: .95rem; }
    .pg-modal__label { font-size: .9rem; }
    .pg-modal__body { gap: 18px; }
    .pg-rank-grid { gap: 10px; }
    .pg-rank-btn { padding: 10px 6px; border-radius: 12px; }
    .pg-rank-btn img { width: 52px; height: 52px; }
    .pg-rank-btn span { font-size: .9rem; }
    .pg-modal__select { font-size: 1rem; padding: 13px 16px; }
    .pg-modal__cta { padding: 15px; font-size: 1.05rem; margin-top: 22px; border-radius: 12px; }
    .pg-srv-trigger { padding: 12px 15px; font-size: 1rem; }
}

.pg-modal__body { display: flex; flex-direction: column; gap: 20px; }
.pg-modal__field { display: flex; flex-direction: column; gap: 10px; }
.pg-modal__label { font-size: 1.05rem; font-weight: 600; color: #a5b4fc; text-transform: uppercase; letter-spacing: .05em; }

.pg-rank-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.pg-rank-grid__row {
    display: grid;
    gap: 8px;
}
.pg-rank-grid__row--top {
    grid-template-columns: repeat(6, minmax(0, 1fr));
}
.pg-rank-grid__row--bottom {
    grid-template-columns: repeat(5, minmax(0, 1fr));
    width: calc((100% - 8px * 5) * 5 / 6 + 8px * 4);
    max-width: 100%;
    margin: 0 auto;
}
.pg-rank-btn {
    display: flex; flex-direction: column; align-items: center; gap: 5px;
    padding: 10px 4px;
    background: rgba(255,255,255,.04);
    border: 2px solid rgba(255,255,255,.07);
    border-radius: 12px; cursor: pointer;
    transition: all .18s;
}
.pg-rank-btn:hover { border-color: rgba(124,92,252,.4); background: rgba(124,92,252,.08); }
.pg-rank-btn.active { border-color: #7c5cfc; background: rgba(124,92,252,.15); }
.pg-rank-btn img { width: 60px; height: 60px; object-fit: contain; object-position: center; }
.pg-rank-btn span { font-size: 1rem; color: #9ca3af; font-weight: 700; }
.pg-rank-btn.active span { color: #a78bfa; }

.pg-rank-disabled {
    position: relative;
    cursor: not-allowed !important;
    filter: grayscale(.35);
}
.pg-rank-disabled img,
.pg-rank-disabled span {
    opacity: .45;
}
.pg-rank-disabled:hover {
    border-color: rgba(239,68,68,.45) !important;
    background: rgba(239,68,68,.10) !important;
}
.pg-rank-disabled::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    bottom: calc(100% + 12px);
    transform: translateX(-50%) translateY(4px);
    width: 300px;
    max-width: min(300px, 78vw);
    padding: 12px 14px;
    border-radius: 12px;
    background: #070613;
    border: 1px solid rgba(167,139,250,.55);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.45;
    text-align: center;
    white-space: normal;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    z-index: 99999;
    box-shadow: 0 14px 36px rgba(0,0,0,.65), 0 0 0 1px rgba(255,255,255,.06) inset;
    transition: opacity .15s, visibility .15s, transform .15s;
}
.pg-rank-disabled::before {
    content: '';
    position: absolute;
    left: 50%;
    bottom: calc(100% + 5px);
    transform: translateX(-50%);
    border: 7px solid transparent;
    border-top-color: #070613;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    z-index: 100000;
    transition: opacity .15s, visibility .15s;
}
.pg-rank-disabled:hover::after,
.pg-rank-disabled:focus-visible::after,
.pg-rank-disabled:hover::before,
.pg-rank-disabled:focus-visible::before {
    opacity: 1;
    visibility: visible;
}
.pg-rank-disabled:hover::after,
.pg-rank-disabled:focus-visible::after {
    transform: translateX(-50%) translateY(0);
}
.pg-rank-pills .pg-rpill.pg-rank-disabled::after {
    width: 320px;
    max-width: min(320px, 78vw);
    font-size: 13px;
}


.pg-rank-grid--classic .pg-rank-grid__row--top {
    grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
}
.pg-rank-grid--classic .pg-rank-btn {
    min-height: 88px !important;
    padding: 10px 6px !important;
}
.pg-rank-grid--classic .pg-rank-btn img {
    width: 58px !important;
    height: 58px !important;
}
.pg-rank-grid--classic .pg-rank-btn span {
    font-size: .88rem !important;
}
@media (max-width: 900px) {
    .pg-rank-grid--classic .pg-rank-grid__row--top { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
}

.pg-modal__select {
    width: 100%; padding: 12px 16px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 12px; color: #e2e8f0;
    font-size: .9rem; outline: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23726e8e' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center;
    cursor: pointer;
}
.pg-modal__select:focus { border-color: rgba(124,92,252,.5); }
.pg-modal__select option {
    background: #13112a;
    color: #e2e8f0;
}

.pg-modal__cta {
    width: 100%; margin-top: 32px;
    padding: 18px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none; border-radius: 14px;
    color: #fff; font-size: 1.25rem; font-weight: 700;
    cursor: pointer; transition: opacity .2s, transform .15s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.pg-modal__cta:hover { opacity: .9; transform: translateY(-1px); }

/* ── Custom Server Dropdown ──────────────────── */
.pg-srv-drop { position: relative; user-select: none; }
.pg-srv-trigger {
    display: flex; align-items: center; gap: 12px;
    width: 100%; padding: 14px 18px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 12px; color: #e2e8f0;
    font-size: 1.15rem; cursor: pointer;
    transition: border-color .18s, background .18s;
}
.pg-srv-trigger:hover { border-color: rgba(124,92,252,.4); background: rgba(124,92,252,.06); }
.pg-srv-drop.open .pg-srv-trigger { border-color: rgba(124,92,252,.5); background: rgba(124,92,252,.08); }
.pg-srv-flag { font-size: 1.3rem; line-height: 1; flex-shrink: 0; color: #a78bfa; width: 24px; text-align: center; }
.pg-srv-name { flex: 1; font-weight: 500; }
.pg-srv-arrow { color: #726e8e; font-size: .85rem; transition: transform .2s; }
.pg-srv-drop.open .pg-srv-arrow { transform: rotate(180deg); }
.pg-srv-list {
    display: none;
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 9999;
    background: #1a1733;
    border: 1px solid rgba(124,92,252,.25);
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 12px 40px rgba(0,0,0,.5);
    max-height: 320px; overflow-y: auto;
}
.pg-srv-drop.open .pg-srv-list { display: block; }
.pg-srv-opt {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 18px;
    font-size: 1.1rem; color: #c4bfe8; cursor: pointer;
    transition: background .15s, color .15s;
}
.pg-srv-opt span { font-size: 1.2rem; line-height: 1; flex-shrink: 0; color: #a78bfa; width: 24px; text-align: center; }
.pg-srv-opt:hover { background: rgba(124,92,252,.12); color: #fff; }
.pg-srv-opt.active { background: rgba(124,92,252,.2); color: #a78bfa; font-weight: 600; }
.pg-srv-list::-webkit-scrollbar { width: 4px; }
.pg-srv-list::-webkit-scrollbar-track { background: transparent; }
.pg-srv-list::-webkit-scrollbar-thumb { background: rgba(124,92,252,.3); border-radius: 4px; }
@media (min-width: 1024px) {
    .pg-modal__body { gap: 18px; }
    .pg-modal__label { font-size: .9rem; }
    .pg-srv-trigger { font-size: 1rem; padding: 12px 15px; }
    .pg-srv-flag { font-size: 1.05rem; }
    .pg-srv-opt { font-size: .95rem; padding: 11px 15px; gap: 12px; }
    .pg-srv-opt span { font-size: 1.05rem; }
}

/* ── Change rank button in header ────────────── */
.pg-change-rank-btn {
    margin-left: auto; flex-shrink: 0;
    display: flex; align-items: center; gap: 0.5vw;
    background: rgba(124,92,252,.12); border: 0.05vw solid rgba(124,92,252,.35);
    border-radius: 0.7vw; padding: 0.5vw 1vw; cursor: pointer;
    color: #a78bfa; font-size: 0.85vw; font-weight: 600;
    transition: all .18s;
}
.pg-change-rank-btn img { height: 1.8vw !important; width: 1.8vw !important; }
.pg-change-rank-btn span { font-size: 0.85vw; }
.pg-change-rank-btn i { font-size: 0.6vw !important; }
.pg-change-rank-btn:hover { background: rgba(124,92,252,.25); border-color: rgba(124,92,252,.6); }

/* ── Rank Pills ──────────────────────────────── */
.pg-rank-pills { display:flex !important; gap:0.4vw; flex-wrap:wrap; margin-bottom:0.9vw; }
.pg-rank-pills .pg-rpill {
    display:flex !important; align-items:center !important; flex-direction:row !important;
    gap:0.4vw; padding:0.3vw 0.85vw !important; border-radius:2vw !important;
    border:0.05vw solid rgba(255,255,255,.1) !important;
    background:rgba(255,255,255,.05) !important;
    color:rgba(255,255,255,.5) !important; font-size:0.75vw !important; font-weight:600;
    cursor:pointer; transition:all .18s; width:auto !important; height:auto !important;
    text-align:left !important; min-width:unset !important;
}
.pg-rank-pills .pg-rpill img { width:1.3vw !important; height:1.3vw !important; object-fit:contain !important; filter:none !important; }
.pg-rank-pills .pg-rpill:hover { border-color:rgba(124,92,252,.4) !important; color:#c4b5fd !important; background:rgba(124,92,252,.08) !important; }
.pg-rank-pills .pg-rpill.active { border-color:#7c5cfc !important; background:rgba(124,92,252,.18) !important; color:#fff !important; box-shadow:0 0 0.6vw rgba(124,92,252,.25); }
.pg-division-field.is-hidden,
.pg-division-pills.is-hidden { display:none !important; }
.pg-division-grid {
    display:grid; grid-template-columns:repeat(4, 1fr); gap:0.5vw;
}
.pg-division-btn {
    border:0.05vw solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); color:#b9b7cf;
    border-radius:0.6vw; padding:0.75vw 0.5vw; font-size:0.95vw; font-weight:800; cursor:pointer;
    transition:.18s ease;
}
.pg-division-btn:hover,
.pg-division-btn.active {
    border-color:#7c5cfc; background:rgba(124,92,252,.18); color:#fff; box-shadow:0 0 0.55vw rgba(124,92,252,.22);
}
.pg-division-help {
    margin:0.45vw 0 0; color:#8f8aa6; font-size:0.72vw; line-height:1.35;
}
.pg-division-pills {
    display:flex; align-items:center; gap:0.4vw; flex-wrap:wrap; margin:-0.35vw 0 0.9vw;
}
.pg-division-pills__label {
    color:#aab5ff; font-weight:800; font-size:0.72vw; text-transform:uppercase; letter-spacing:.04em; margin-right:0.25vw;
}
.pg-division-pill {
    border:0.05vw solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); color:#b9b7cf;
    border-radius:999px; padding:0.38vw 0.8vw; font-size:0.78vw; font-weight:800; cursor:pointer; transition:.18s ease;
}
.pg-division-pill:hover,
.pg-division-pill.active {
    border-color:#7c5cfc; background:rgba(124,92,252,.18); color:#fff;
}


/* ── Filters ─────────────────────────────────── */
.pg-filters { display:flex; gap:0.6vw; flex-wrap:wrap; align-items:center; margin-bottom:0.7vw; }
.pg-results-count { margin-bottom: 1.2vw; color: #9ca3af; font-size: 0.9vw; font-weight: 600; }
.pg-sort-select,
.pg-lang-dd__btn,
.pg-online-btn {
    min-height: 2.8vw;
    box-sizing: border-box;
}
.pg-sort-select {
    background:rgba(255,255,255,.06); border:0.05vw solid rgba(255,255,255,.1);
    border-radius:2vw; color:rgba(255,255,255,.7); font-size:0.8vw;
    padding:0 2vw 0 1vw; outline:none; cursor:pointer; transition:all .2s;
    appearance:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%239ca3af' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 0.7vw center;
}
.pg-sort-select:hover { border-color:rgba(124,92,252,.4); background-color:rgba(124,92,252,.06); }
.pg-sort-select:focus { border-color:rgba(124,92,252,.5); outline:none; }
.pg-sort-select option { background:#13112a; color:#e2e8f0; }

/* ── Language dropdown (custom) ─────────────── */
.pg-lang-dd { position: relative; }
.pg-lang-dd__btn {
    display:flex; align-items:center; gap:0.65vw;
    background:rgba(255,255,255,.06);
    border:0.05vw solid rgba(255,255,255,.1);
    border-radius: 1.25vw;
    padding: 0 0.9vw;
    color: rgba(255,255,255,.85);
    cursor:pointer;
    transition: all .2s;
    min-width: 13vw;
    user-select: none;
}
.pg-lang-dd__btn:hover { border-color:rgba(124,92,252,.4); background-color:rgba(124,92,252,.06); }
.pg-lang-dd__btn:focus { outline:none; border-color:rgba(124,92,252,.55); box-shadow: 0 0 0 0.12vw rgba(124,92,252,.28); }
.pg-lang-dd__flag {
    width: 1.9vw; height: 1.9vw;
    border-radius: 0.65vw;
    background: rgba(0,0,0,.25);
    border: 0.05vw solid rgba(255,255,255,.08);
    display:flex; align-items:center; justify-content:center;
    overflow:hidden;
    flex: 0 0 auto;
}
.pg-lang-dd__flag img { width: 100%; height: 100%; object-fit: cover; display:block; }
.pg-lang-dd__flag i { font-size: 1vw; color: rgba(255,255,255,.7); display:none; }
.pg-lang-dd__text { display:flex; flex-direction:column; line-height:1.1; text-align:left; }
.pg-lang-dd__caption { font-size: 0.62vw; color: rgba(255,255,255,.45); font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
.pg-lang-dd__value { font-size: 0.85vw; font-weight: 800; color: rgba(255,255,255,.92); }
.pg-lang-dd__chev { margin-left: auto; font-size: 0.7vw; color: rgba(255,255,255,.55); }

.pg-lang-dd__menu {
    position:absolute;
    top: calc(100% + 0.5vw);
    left: 0;
    width: 100%;
    max-height: 18vw;
    overflow: auto;
    background: #0f0d1e;
    border: 0.05vw solid rgba(124,92,252,.25);
    border-radius: 1.1vw;
    box-shadow: 0 1.2vw 3vw rgba(0,0,0,.55);
    padding: 0.35vw;
    z-index: 50;
    display:none;
}
.pg-lang-dd.open .pg-lang-dd__menu { display:block; }
.pg-lang-dd__opt {
    display:flex; align-items:center; gap:0.7vw;
    padding: 0.55vw 0.65vw;
    border-radius: 0.85vw;
    cursor:pointer;
    color: rgba(255,255,255,.85);
    transition: background .15s, color .15s;
}
.pg-lang-dd__opt:hover { background: rgba(124,92,252,.14); }
.pg-lang-dd__opt.active { background: rgba(124,92,252,.22); }
.pg-lang-dd__opt .flag {
    width: 1.75vw; height: 1.25vw;
    border-radius: 0.4vw;
    border: 0.05vw solid rgba(255,255,255,.08);
    overflow:hidden;
    flex: 0 0 auto;
    background: rgba(0,0,0,.25);
}
.pg-lang-dd__opt .flag img { width:100%; height:100%; object-fit:cover; display:block; }
.pg-lang-dd__opt .label { font-weight: 800; font-size: 0.85vw; }
.pg-filter-input {
    flex: 1 1 11vw;
    background:rgba(255,255,255,.06); border:0.05vw solid rgba(255,255,255,.1);
    border-radius:2vw; color:#e2e8f0; font-size:0.95vw; padding:0.7vw 1.4vw; outline:none;
    transition: border-color .2s;
}
.pg-filter-input::placeholder { color: rgba(255,255,255,.3); }
.pg-filter-input:focus { border-color:rgba(124,92,252,.5); background: rgba(124,92,252,.06); }
.pg-online-btn {
    display:flex; align-items:center; gap:0.4vw; padding:0 1.3vw;
    border-radius:2vw; border:0.05vw solid rgba(255,255,255,.1);
    background:rgba(255,255,255,.05); color:rgba(255,255,255,.5);
    font-size:0.9vw; font-weight:600; cursor:pointer; transition:all .2s; white-space:nowrap;
}
.pg-online-btn.active { border-color:#22c55e; background:rgba(34,197,94,.12); color:#22c55e; }
.pg-dot { width:0.45vw; height:0.45vw; border-radius:50%; background:currentColor; flex:0 0 auto; }

/* ── Booster List ────────────────────────────── */
.pg-list { display:flex; flex-direction:column; gap:0.8vw; min-height:3vw; }

/* ── Booster Card ────────────────────────────── */
.pg-bc2 {
    display: grid;
    grid-template-columns: 12vw 1fr auto;
    border-radius: 1.1vw;
    border: 0.1vw solid #1e1b38;
    background: #110f1f;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s, background .2s;
    position: relative;
    min-height: 11vw;
}
.pg-bc2:hover { border-color: rgba(99,102,241,.4); background: #15122a; }
.pg-bc2 + .pg-bc2 { border-top-color: rgba(99,102,241,.08); }
.pg-bc2.selected {
    border-color: #7c5cfc;
    box-shadow: 0 0 0 0.15vw rgba(124,92,252,.3), 0 0.5vw 2vw rgba(124,92,252,.12);
    background: #15122a;
}
.pg-bc2.selected .pg-bc2__left {
    background: linear-gradient(160deg, #120e2a, #1e1545);
    border-right: 0.15vw solid rgba(124,92,252,.25);
}

/* Avatar col */
.pg-bc2__left {
    background: linear-gradient(160deg,#0d0b1a,#1a1535);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.55vw; padding: 1.2vw 1vw;
}
.pg-bc2__avatar-wrap {
    position: relative; display: block; border-radius: 50%; overflow: hidden; flex-shrink: 0;
    text-decoration: none;
}
.pg-bc2__avatar-overlay {
    position: absolute; inset: 0; border-radius: 50%;
    background: rgba(124,92,252,.6); display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .2s; color: #fff; font-size: 1.2vw;
}
.pg-bc2__avatar-wrap:hover .pg-bc2__avatar-overlay { opacity: 1; }
.pg-bc2__avatar-wrap:hover .pg-bc2__avatar { filter: brightness(0.6); }
.pg-bc2__avatar {
    width: 6.8vw; height: 6.8vw; border-radius: 50%;
    border: 0.15vw solid rgba(124,92,252,.5);
    object-fit: cover; object-position: center top; display: block;
    box-shadow: 0 0 1.5vw rgba(124,92,252,.35);
}
.pg-bc2__status-row { display:flex; flex-direction:column; gap:0.3vw; align-items:center; }
.pg-bc2__online-badge {
    display: flex; align-items: center; gap: 0.3vw;
    font-size: 0.82vw; font-weight: 600; color: #6b7280;
    background: rgba(255,255,255,.05); border: 0.05vw solid rgba(255,255,255,.08);
    border-radius: 1vw; padding: 0.2vw 0.6vw; white-space: nowrap;
}
.pg-bc2__online-badge.online { color: #22c55e; border-color: rgba(34,197,94,.3); background: rgba(34,197,94,.1); }
.pg-bc2__tz-left {
    display: flex; align-items: center; gap: 0.3vw;
    font-size: 0.75vw; color: #818cf8; font-weight: 600;
    background: rgba(99,102,241,.09); border: 0.05vw solid rgba(99,102,241,.22);
    border-radius: 0.45vw; padding: 0.25vw 0.6vw; white-space: nowrap;
    max-width: 9vw; overflow: hidden; text-overflow: ellipsis;
    margin-top: 0.3vw;
}
.pg-bc2__tz-left i { font-size: 0.68vw; flex-shrink: 0; }
.pg-bc2__online-dot {
    width: 0.4vw; height: 0.4vw; border-radius: 50%; background: currentColor; flex-shrink: 0;
}

/* Info col */
.pg-bc2__body {
    padding: 1.1vw 1.5vw 1.1vw 1.3vw;
    display: flex; flex-direction: column; gap: 0.5vw;
    justify-content: center; min-width: 0;
}
.pg-bc2__section { display: flex; flex-direction: column; gap: 0.25vw; }
.pg-bc2__section-label {
    font-size: 0.6vw; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: #4b5563;
}
.pg-bc2__empty-tag { font-size: 0.7vw; color: #374151; }
/* Top row: name+rating LEFT, rank RIGHT */
.pg-bc2__top-row {
    display: flex; align-items: center; justify-content: space-between; gap: 0.6vw;
}
.pg-bc2__name-line {
    display: flex; align-items: center; gap: 0.7vw; flex-wrap: wrap; margin-bottom: 0;
}
.pg-bc2__name {
    font-size: 1.45vw; font-weight: 800;
    background: linear-gradient(to right, #6366f1, #8b5cf6, #d946ef);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}
.pg-bc2__rating-inline { font-size: 1.05vw; color: #f59e0b; font-weight: 700; display: flex; align-items: center; gap: 4px; }
.pg-bc2__rating-val { font-size: 1.05vw; color: #f59e0b; font-weight: 700; }
.pg-bc2__review-count { font-size: 0.85vw; color: rgba(255,255,255,0.45); font-weight: 500; }
.pg-bc2__ppg { font-size: 0.8vw; color: #9ca3af; text-align: center; margin-top: 0.1vw; font-weight: 500; }
/* Rank box top-right */
.pg-bc2__rank-box {
    background: rgba(255,255,255,.06); border: 0.05vw solid rgba(99,102,241,.2);
    border-radius: 0.7vw; padding: 0.5vw 0.8vw;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.pg-bc2__rank-box img { height: 3.9vw; width: auto; object-fit: contain; }
/* Icon boxes row (roles) */
.pg-bc2__icons-row { display: flex; gap: 0.35vw; flex-wrap: wrap; align-items: center; }
.pg-bc2__icon-box {
    width: 2.7vw; height: 2.7vw; border-radius: 0.55vw;
    background: rgba(255,255,255,.06); border: 0.05vw solid rgba(255,255,255,.09);
    display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.pg-bc2__icon-box img { width: 1.55vw; height: 1.55vw; object-fit: contain; filter: brightness(1.8); }
/* Champs + langs bottom row */
.pg-bc2__bottom-row { display: flex; gap: 0.5vw; align-items: center; flex-wrap: wrap; }
/* Description + timezone footer */
.pg-bc2__footer {
    padding-top: 0.45vw;
    border-top: 0.05vw solid rgba(255,255,255,.07);
}
.pg-bc2__tz {
    display: flex; align-items: center; gap: 0.3vw;
    font-size: 0.75vw; color: #818cf8; font-weight: 600;
    background: rgba(99,102,241,.08); border: 0.05vw solid rgba(99,102,241,.2);
    border-radius: 0.4vw; padding: 0.22vw 0.55vw; white-space: nowrap;
}
.pg-bc2__tz i { font-size: 0.65vw; }
.pg-bc2__desc {
    font-size: 0.92vw; color: #c4c9d4; line-height: 1.6;
    overflow: hidden;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
}
.pg-bc2__champs-row { display: flex; gap: 0.3vw; flex-wrap: wrap; }
.pg-bc2__champ-icon {
    width: 2.6vw; height: 2.6vw; border-radius: 0.55vw;
    border: 0.05vw solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.04); overflow: hidden;
}
.pg-bc2__champ-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pg-bc2__lang-row { display: flex; gap: 0.3vw; flex-wrap: wrap; align-items: center; margin-left: auto; }
.pg-bc2__icon-box.lang {
    width: 2.4vw; height: 2.4vw; border-radius: 0.4vw;
}
.pg-bc2__icon-box.lang img { width: 1.6vw; height: 1.6vw; filter: none; border-radius: 0.1vw; }

/* Right col: game selector + price */
.pg-bc2__right {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 1.3vw 1.6vw; gap: 0.7vw;
    background: rgba(124,92,252,.04);
    border-left: 0.05vw solid rgba(255,255,255,.05);
    min-width: 12vw;
}
.pg-bc2.selected .pg-bc2__right::after {
    content: '✓ Selected';
    display: block;
    background: #7c5cfc;
    color: #fff;
    text-align: center;
    padding: 0.4vw 0.8vw;
    font-size: 0.72vw;
    font-weight: 700;
    border-radius: 0.5vw;
    width: 100%;
    box-sizing: border-box;
}

/* Game counter */
.pg-game-counter {
    display: flex; align-items: center; gap: 0.6vw;
}
.pg-game-btn {
    width: 2.5vw; height: 2.5vw; border-radius: 50%;
    background: rgba(255,255,255,.06); border: 0.05vw solid rgba(255,255,255,.1);
    color: #e2e8f0; font-size: 1.2vw; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s; line-height: 1;
}
.pg-game-btn:hover { background: rgba(124,92,252,.2); border-color: #7c5cfc; color: #a78bfa; }
.pg-game-btn:disabled { opacity: .3; cursor: not-allowed; }
.pg-game-count {
    font-size: 1.9vw; font-weight: 800; color: #fff;
    min-width: 1.5vw; text-align: center;
}
.pg-game-label { font-size: 0.9vw; color: #9ca3af; text-align: center; margin-top: -0.1vw; }

/* Price display */
.pg-bc2__price { text-align: center; }
.pg-bc2__price-val {
    font-size: 1.9vw; font-weight: 900; color: #a78bfa; line-height: 1; white-space: nowrap;
}
.pg-bc2__price-cur { font-size: 0.8vw; font-weight: 700; vertical-align: super; }
.pg-bc2__price-sub { font-size: 0.6vw; color: #6b7280; margin-top: 0.2vw; }


/* Empty / loading */
.pg-bc__none {
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:clamp(10px,0.8vw,14px);
    text-align:center; padding:clamp(24px,3vw,48px); margin:clamp(8px,0.8vw,16px) auto;
    max-width:min(720px,100%); border:1px solid rgba(167,139,250,.24);
    border-radius:clamp(18px,1.1vw,24px); background:linear-gradient(180deg, rgba(124,92,252,.14), rgba(255,255,255,.03));
    box-shadow:0 12px 30px rgba(0,0,0,.18), inset 0 1px 0 rgba(255,255,255,.04);
}
.pg-bc__none-icon {
    width:clamp(46px,2.8vw,58px); height:clamp(46px,2.8vw,58px); border-radius:999px;
    display:grid; place-items:center; font-size:clamp(22px,1.4vw,28px); font-weight:900;
    color:#f5f3ff; background:radial-gradient(circle at 30% 30%, #a78bfa, #7c5cfc 65%, #5b38eb 100%);
    box-shadow:0 10px 24px rgba(124,92,252,.35);
}
.pg-bc__none-title { color:#ffffff; font-size:clamp(18px,1.15vw,22px); font-weight:900; line-height:1.2; }
.pg-bc__none-text { color:#cbd5e1; font-size:clamp(13px,0.85vw,16px); line-height:1.55; max-width:42ch; }
.pg-loading { display:flex; justify-content:center; padding:3vw; }
.pg-spinner { width:32px; height:32px; border:3px solid rgba(255,255,255,.08); border-top-color:#7c5cfc; border-radius:50%; animation:pg-spin .7s linear infinite; }
@keyframes pg-spin { to { transform:rotate(360deg); } }

/* Pagination */
.pg-pagination { display:flex; align-items:center; justify-content:center; gap:clamp(14px,1vw,22px); margin-top:clamp(22px,1.4vw,34px); flex-wrap:wrap; }
.pg-page-btn {
    min-width:clamp(70px,3.2vw,92px); height:clamp(70px,3.2vw,92px); padding:0 clamp(18px,1.2vw,28px); border-radius:clamp(18px,0.9vw,24px);
    border:clamp(1px,0.07vw,2px) solid rgba(255,255,255,.10); background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.68); font-size:clamp(18px,1.2vw,22px); font-weight:850;
    cursor:pointer; transition:all .18s; display:grid; place-items:center;
}
.pg-page-btn:hover { border-color:rgba(124,92,252,.5); color:#fff; background:rgba(124,92,252,.08); }
.pg-page-btn.active { border-color:#7c5cfc; background:rgba(124,92,252,.2); color:#fff; }
.pg-page-btn:disabled { opacity:.35; cursor:not-allowed; }

/* ── Order Summary Selected Booster ─────────── */
#pg-selected-booster-summary {
    gap: 12px; align-items: center;
    padding: 14px 16px;
    background: rgba(124,92,252,.07);
    border: 1px solid rgba(124,92,252,.2);
    border-radius: 12px; margin: -4px 0 4px;
}
.pg-sum__avatar-wrap { position: relative; flex-shrink: 0; }
.pg-sum__avatar {
    width: 44px; height: 44px; border-radius: 50%;
    object-fit: cover; border: 2px solid #7c5cfc;
    display: block;
}
.pg-sum__dot {
    position: absolute; bottom: 1px; right: 1px;
    width: 11px; height: 11px; border-radius: 50%;
    border: 2px solid #13112a;
}
.pg-sum__dot.online  { background: #22c55e; }
.pg-sum__dot.offline { background: #4b5563; }
.pg-sum__info { flex: 1; min-width: 0; }
.pg-sum__label { font-size: .68rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
.pg-sum__name { font-size: .95rem; font-weight: 700; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pg-sum__meta { display: flex; align-items: center; gap: 8px; margin-top: 3px; }
.pg-sum__games { font-size: .75rem; color: #9ca3af; }
.pg-sum__price { font-size: .8rem; color: #a78bfa; font-weight: 700; }
.pg-sum__change {
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
    border-radius: 8px; padding: 6px 8px; color: #6b7280;
    cursor: pointer; flex-shrink: 0; font-size: .8rem;
    transition: all .18s;
}
.pg-sum__change:hover { border-color: #7c5cfc; color: #a78bfa; background: rgba(124,92,252,.1); }

/* Darker Pro Games surfaces — aligned with the checkout sidebar. */
.pg-modal {
    background:#090b16;
    border-color:rgba(124,92,252,.22);
    box-shadow:0 26px 72px rgba(0,0,0,.72), inset 0 1px 0 rgba(255,255,255,.025);
}
.pg-rank-btn,
.pg-srv-trigger,
.pg-modal__select {
    background:#0d0f1c;
    border-color:rgba(255,255,255,.075);
}
.pg-rank-btn:hover,
.pg-srv-trigger:hover {
    background:#111426;
}
.pg-rank-btn.active {
    background:#15142a;
}
.pg-srv-list {
    background:#0b0d18;
    border-color:rgba(124,92,252,.2);
}
.pg-change-rank-btn {
    background:#0c0e1a;
    border-color:rgba(124,92,252,.3);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.025);
}
.pg-change-rank-btn:hover {
    background:#121426;
}
.pg-rank-pills .pg-rpill {
    background:#0b0d17 !important;
    border-color:rgba(255,255,255,.085) !important;
}
.pg-rank-pills .pg-rpill.active {
    background:#151329 !important;
}
.pg-bc2 {
    background:#090b16;
    border-color:#201d39;
}
.pg-bc2:hover,
.pg-bc2.selected {
    background:#0d0f1c;
}
.pg-bc2.selected {
    border-color:#6851da;
    box-shadow:0 0 0 1px rgba(104,81,218,.2), 0 14px 38px rgba(0,0,0,.28);
}
.pg-bc2__left,
.pg-bc2.selected .pg-bc2__left {
    background:#0a0c17;
    border-right-color:rgba(124,92,252,.16);
}
.pg-bc2__body {
    background:#0d0f1c;
}
.pg-bc2__right {
    background:#0a0c17;
    border-left-color:rgba(255,255,255,.045);
}
.pg-bc2__rank-box,
.pg-bc2__icon-box,
.pg-bc2__champ-icon,
.pg-game-btn {
    background:#101321;
    border-color:rgba(255,255,255,.075);
}
#pg-selected-booster-summary {
    background:#0b0d18;
    border-color:rgba(124,92,252,.22);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.02);
}
.pg-sum__change {
    background:#10121e;
    border-color:rgba(255,255,255,.075);
}

@media (max-width: 900px) {
    /* Header */
    .pg-header-icon { width: 30px !important; height: 30px !important; border-radius: 7px !important; }
    .pg-header-icon i { font-size: 13px !important; }
    #pg-booster-card .card-header { align-items: center !important; gap: 10px !important; }
    #pg-booster-card .card-header h3 { font-size: 18px !important; }
    #pg-booster-card .card-header p { font-size: 12px !important; }
    .pg-change-rank-btn { font-size: 14px !important; padding: 9px 14px !important; border-radius: 10px !important; gap: 7px !important; }
    .pg-change-rank-btn img { height: 22px !important; width: 22px !important; }
    .pg-change-rank-btn span { font-size: 14px !important; }
    /* Modal - slide up from bottom */
    .pg-modal-overlay { padding: 10px !important; align-items: flex-end !important; }
    .pg-modal { padding: 22px !important; border-radius: 18px 18px 12px 12px !important; max-width: 100% !important; max-height: 82vh; overflow-y: auto; }
    .pg-rank-grid { gap: 8px !important; }
    .pg-rank-grid__row { width: 100% !important; margin: 0 !important; grid-template-columns: repeat(4, 1fr) !important; }
    .pg-rank-btn { padding: 10px 4px !important; border-radius: 10px !important; }
    .pg-rank-btn img { width: 40px !important; height: 40px !important; }
    .pg-rank-btn span { font-size: 11px !important; }
    /* Rank Pills */
    .pg-rank-pills { gap: 6px; margin-bottom: 12px; flex-wrap: wrap; }
    .pg-rank-pills .pg-rpill { font-size: 12px !important; padding: 6px 11px !important; }
    .pg-rank-pills .pg-rpill img { width: 17px !important; height: 17px !important; }
    /* Filters */
    .pg-filters { gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
    .pg-results-count { margin-bottom: 12px; font-size: 13px; }
    .pg-filter-input { font-size: 14px; padding: 10px 14px; flex: 1 1 100%; }
    .pg-sort-select, .pg-lang-dd__btn, .pg-online-btn { min-height: 44px; box-sizing: border-box; }
    .pg-sort-select { font-size: 13px; padding: 0 14px; flex: 1; }
    .pg-lang-dd__btn { min-width: 170px; border-radius: 14px; padding: 0 12px; }
    .pg-lang-dd__flag { width: 34px; height: 34px; border-radius: 10px; }
    .pg-lang-dd__flag i { font-size: 16px; }
    .pg-lang-dd__caption { font-size: 10px; }
    .pg-lang-dd__value { font-size: 13px; }
    .pg-lang-dd__chev { font-size: 12px; }
    .pg-lang-dd__menu { top: calc(100% + 8px); max-height: 260px; border-radius: 16px; padding: 6px; }
    .pg-lang-dd__opt { padding: 10px 10px; border-radius: 12px; }
    .pg-lang-dd__opt .flag { width: 28px; height: 20px; border-radius: 6px; }
    .pg-lang-dd__opt .label { font-size: 13px; }
    .pg-online-btn { font-size: 13px !important; padding: 0 14px !important; white-space: nowrap; }
    /* Card list spacing */
    .pg-list { gap: 16px !important; }
    /* Card stacked */
    .pg-bc2 { display: flex !important; flex-direction: column !important; min-height: unset; }
    /* Left col: avatar + [online | tz] inline */
    .pg-bc2__left {
        display: flex !important; flex-direction: row !important;
        align-items: center !important; gap: 12px !important;
        padding: 14px 16px !important; border-right: none !important;
        border-bottom: 1px solid rgba(255,255,255,.05) !important;
        min-width: unset !important; width: auto !important;
    }
    .pg-bc2__avatar-wrap { width: 54px !important; height: 54px !important; flex-shrink: 0 !important; }
    .pg-bc2__avatar { width: 54px !important; height: 54px !important; }
    /* Avatar overlay: shift icon left so it's not cut off */
    .pg-bc2__avatar-overlay { font-size: 13px !important; padding-right: 4px !important; justify-content: center !important; }
    /* Online + tz: horizontal inline */
    .pg-bc2__left > div:not(.pg-bc2__avatar-wrap) { display: flex; flex-direction: row; align-items: center; gap: 8px; flex-wrap: wrap; }
    .pg-bc2__status-row { flex-direction: row !important; gap: 6px !important; align-items: center !important; flex-wrap: wrap !important; }
    .pg-bc2__online-badge { font-size: 12px !important; padding: 4px 9px !important; }
    .pg-bc2__tz-left { font-size: 12px !important; padding: 4px 8px !important; margin-top: 0 !important; max-width: unset !important; }
    /* Hide selected text badge - border glow enough */
    .pg-bc2__selected-badge { display: none !important; }
    .pg-bc2.selected .pg-bc2__right::after { display: none !important; }
    /* Body */
    .pg-bc2__body { padding: 14px 16px !important; gap: 9px !important; border-right: none !important; }
    .pg-bc2__name { font-size: 17px !important; -webkit-text-fill-color: transparent; }
    .pg-bc2__rating-inline, .pg-bc2__rating-val { font-size: 14px !important; }
    .pg-bc2__ppg { font-size: 11px !important; }
    .pg-bc2__section-label { font-size: 10px !important; }
    .pg-bc2__icon-box { width: 32px !important; height: 32px !important; border-radius: 6px !important; }
    .pg-bc2__icon-box img { width: 18px !important; height: 18px !important; }
    .pg-bc2__champ-icon { width: 32px !important; height: 32px !important; border-radius: 6px !important; }
    .pg-bc2__rank-box { padding: 6px 8px !important; }
    .pg-bc2__rank-box img { height: 36px !important; }
    .pg-bc2__desc { font-size: 13px !important; -webkit-line-clamp: 2 !important; }
    /* Right col bottom bar */
    .pg-bc2__right {
        display: flex !important; flex-direction: row !important;
        align-items: center !important; justify-content: space-between !important;
        border-left: none !important; border-top: 1px solid rgba(255,255,255,.06) !important;
        min-width: unset !important; padding: 14px 16px !important; gap: 14px !important;
        background: rgba(0,0,0,.12) !important; border-radius: 0 0 11px 11px !important;
    }
    .pg-game-counter { flex-direction: row !important; gap: 12px !important; align-items: center !important; }
    .pg-game-count { font-size: 26px !important; min-width: unset !important; }
    .pg-game-label { font-size: 13px !important; display: block !important; }
    .pg-game-btn { width: 36px !important; height: 36px !important; font-size: 17px !important; }
    .pg-bc2__price { align-items: flex-end !important; }
    .pg-bc2__price-val { font-size: 26px !important; }
    .pg-bc2__price-sub { font-size: 12px !important; }
    /* Pagination bigger on mobile */
    .pg-pagination { gap: 10px !important; margin-top: 16px !important; }
    .pg-page-btn { min-width: 52px !important; height: 52px !important; padding: 0 16px !important; border-radius: 14px !important; font-size: 15px !important; font-weight: 800 !important; }

}
@media (max-width: 480px) {
    .pg-page-btn { min-width: 48px !important; height: 48px !important; padding: 0 14px !important; border-radius: 13px !important; font-size: 14px !important; }
.pg-rank-pills .pg-rpill { font-size: 11px !important; padding: 5px 8px !important; }
    .pg-rank-pills .pg-rpill img { width: 15px !important; height: 15px !important; }
    .pg-bc2__name { font-size: 15px !important; }
    .pg-bc2__avatar-wrap, .pg-bc2__avatar { width: 48px !important; height: 48px !important; }
    .pg-game-count, .pg-bc2__price-val { font-size: 22px !important; }
}

@media (max-width: 900px) {
    .pg-division-grid { gap: 8px !important; }
    .pg-division-btn { font-size: 13px !important; padding: 10px 6px !important; border-radius: 10px !important; }
    .pg-division-help { font-size: 11px !important; margin-top: 6px !important; }
    .pg-division-pills { gap: 6px !important; margin-bottom: 12px !important; }
    .pg-division-pills__label { font-size: 11px !important; }
    .pg-division-pill { font-size: 12px !important; padding: 6px 12px !important; }
}

</style>

<script>
(function () {
    // Scroll to booster card on page load
    function scrollToCard() {
        var card = document.getElementById('pg-booster-card');
        if (!card) return;
        var stickyTop = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--lb-sticky-top').trim() || '90', 10) || 90;
        var y = card.getBoundingClientRect().top + window.scrollY - stickyTop - 16;
        window.scrollTo({ top: Math.max(0, y), behavior: 'instant' });
    }
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    scrollToCard();
    setTimeout(scrollToCard, 150);
    setTimeout(scrollToCard, 500);
    const AJAX_URL = '<?= AJAX_URL ?>';
    const PER_PAGE = window.innerWidth <= 900 ? 5 : 6;
    const RANK_NAMES = <?= json_encode($isClassic ? [1=>'Salt',2=>'Wood',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Legend'] : [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger']) ?>;
    const ASSET = '<?= rtrim(ASSET_URL, "/") ?>';
    const BASE = '<?= rtrim(BASE_URL, "/") ?>';
    <?php $rankImgMap = []; foreach (array_keys($rankNames) as $tierKey) { $rankImgMap[$tierKey] = $isClassic ? lol_classic_rank_asset_url($tierKey, $defaultDivision) : util_rank_img($uiGame, 'mini', $tierKey); } ?>
    const RANK_IMGS = <?= json_encode($rankImgMap) ?>;
    const CURRENCY_CODE = <?= json_encode($_SESSION['currency'] ?? 'EUR') ?>;
    const CURRENCY_SYMBOL = <?= json_encode(util_format_currency_display($_SESSION['currency'] ?? 'EUR')) ?>;
    const EXCHANGE_RATE = <?= json_encode((float) get_exchange_rate()) ?>;

    function pgConvertEurCentsToDisplayCents(eurCents) {
        const cents = parseInt(eurCents || 0, 10);
        if (!cents) return 0;
        if (CURRENCY_CODE === 'EUR') return cents;
        return Math.round(cents * EXCHANGE_RATE);
    }

    function pgFormatMoneyFromEurCents(eurCents) {
        const displayCents = pgConvertEurCentsToDisplayCents(eurCents);
        return CURRENCY_SYMBOL + (displayCents / 100).toFixed(2);
    }

    let selectedTier = <?= (int)$defaultTier ?>;
    let selectedDivision = <?= (int)$defaultDivision ?>;
    let selectedServer = 'euw';
    let onlineOnly = true;
    let selectedId = null;
    let allBoosters = [];
    let currentPage = 1;
    let debounceTimer;
    let searchQ = '';
    let gameCounts = {};
    let selectedLang = 'all';

    // DOM refs
    const $grid    = document.getElementById('pg-boosters-grid');
    const $pag     = document.getElementById('pg-pagination');
    const $results = document.getElementById('pg-results-count');
    const $input   = document.getElementById('pg-booster-id');
    const $matchIn = document.getElementById('pg-matches');
    const $tierIn  = document.getElementById('pg-start-tier');
    const $divIn   = document.getElementById('pg-start-division');
    const $srvIn   = document.getElementById('pg-server-hidden');
    const $toggle  = document.getElementById('pg-online-toggle');
    const $nameInp = document.getElementById('pg-filter-name');
    const $langSel = document.getElementById('pg-lang');
    const $langDD  = document.getElementById('pg-lang-dd');
    const $langBtn = document.getElementById('pg-lang-btn');
    const $langMenu= document.getElementById('pg-lang-menu');
    const $langFlag= document.getElementById('pg-lang-flag');
    const $langGlobe= document.getElementById('pg-lang-globe');
    const $langValue= document.getElementById('pg-lang-value');
    const $overlay = document.getElementById('pg-modal-overlay');
    const $confirm = document.getElementById('pg-modal-confirm');
    const $changeBtn = document.getElementById('pg-change-rank-btn');
    const $headerRankImg = document.getElementById('pg-header-rank-img');
    const $headerRankTxt = document.getElementById('pg-header-rank-txt');
    const $headerSub = document.getElementById('pg-header-sub');

    // ── Modal ─────────────────────────────────
    let modalTier = <?= (int)$defaultTier ?>;
    let modalDivision = <?= (int)$defaultDivision ?>;
    const DIVISION_LABELS = <?= json_encode($isClassic ? [4=>'IV',3=>'III',2=>'II',1=>'I'] : [1=>'I',2=>'II',3=>'III',4=>'IV']) ?>;

    function pgRankHasDivision(tier) {
        tier = parseInt(tier, 10);
        return <?= $isClassic ? '(tier >= 1 && tier <= 6)' : '(tier >= 1 && tier <= 7)' ?>;
    }

    function pgRankDisplay(tier, division) {
        const name = RANK_NAMES[tier] || '';
        if (!pgRankHasDivision(tier)) return name;
        return name + ' ' + (DIVISION_LABELS[division] || 'IV');
    }

    function pgSyncDivisionVisibility(tier) {
        const visible = pgRankHasDivision(tier);
        document.getElementById('pg-modal-division-field')?.classList.toggle('is-hidden', !visible);
        document.getElementById('pg-main-division-field')?.classList.toggle('is-hidden', !visible);
        if (!visible) {
            modalDivision = <?= (int)$defaultDivision ?>;
            selectedDivision = <?= (int)$defaultDivision ?>;
        }
        pgSyncDivisionButtons(pgRankHasDivision(tier) ? selectedDivision : <?= (int)$defaultDivision ?>);
    }

    function pgSyncDivisionButtons(division) {
        document.querySelectorAll('.pg-division-btn, .pg-division-pill').forEach(btn => {
            btn.classList.toggle('active', parseInt(btn.dataset.division, 10) === parseInt(division, 10));
        });
    }
    document.querySelectorAll('.pg-rank-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('pg-rank-disabled') || this.disabled) return;
            document.querySelectorAll('.pg-rank-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            modalTier = parseInt(this.dataset.tier);
            pgSyncDivisionVisibility(modalTier);
            pgUpdateSummaryRank(modalTier, modalDivision);
        });
    });

    document.querySelectorAll('.pg-division-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            modalDivision = parseInt(this.dataset.division, 10) || 4;
            selectedDivision = modalDivision;
            pgSyncDivisionButtons(modalDivision);
            pgUpdateSummaryRank(modalTier, modalDivision);
        });
    });

    document.querySelectorAll('.pg-division-pill').forEach(btn => {
        btn.addEventListener('click', function() {
            selectedDivision = parseInt(this.dataset.division, 10) || 4;
            modalDivision = selectedDivision;
            if ($divIn) $divIn.value = selectedDivision;
            pgSyncDivisionButtons(selectedDivision);
            if ($headerRankTxt) $headerRankTxt.textContent = pgRankDisplay(selectedTier, selectedDivision);
            if ($headerSub) $headerSub.textContent = '<?= t("Showing boosters & prices for") ?> ' + pgRankDisplay(selectedTier, selectedDivision);
            pgUpdateSummaryRank(selectedTier, selectedDivision);
            triggerPriceUpdate();
        });
    });

    pgSyncDivisionVisibility(modalTier);

    // Custom Server Dropdown JS
    (function() {
        const drop    = document.getElementById('pg-srv-drop');
        const trigger = document.getElementById('pg-srv-trigger');
        const list    = document.getElementById('pg-srv-list');
        const select  = document.getElementById('pg-modal-server');
        const flagEl  = trigger ? trigger.querySelector('.pg-srv-flag') : null;
        const nameEl  = trigger ? trigger.querySelector('.pg-srv-name') : null;
        if (!drop || !trigger || !list || !select) return;

        function setServer(val, label, flag) {
            select.value = val;
            if (flagEl) flagEl.innerHTML = flag;
            if (nameEl) nameEl.textContent = label;
            list.querySelectorAll('.pg-srv-opt').forEach(o => o.classList.toggle('active', o.dataset.value === val));
            drop.classList.remove('open');
        }

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            drop.classList.toggle('open');
        });

        list.querySelectorAll('.pg-srv-opt').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                const iconHtml = opt.querySelector('span').innerHTML;
                const label = opt.querySelector('span').nextSibling
                    ? opt.querySelector('span').nextSibling.textContent.trim()
                    : opt.textContent.trim();
                setServer(opt.dataset.value, label, iconHtml);
            });
        });

        document.addEventListener('click', function() { drop.classList.remove('open'); });

        // Mark default active
        list.querySelectorAll('.pg-srv-opt').forEach(o => {
            if (o.dataset.value === select.value) {
                o.classList.add('active');
                if (flagEl) flagEl.innerHTML = o.querySelector('span').innerHTML;
            }
        });
    })();

    $confirm.addEventListener('click', function() {
        selectedTier = modalTier;
        selectedDivision = pgRankHasDivision(selectedTier) ? modalDivision : <?= (int)$defaultDivision ?>;
        selectedServer = document.getElementById('pg-modal-server').value;
        $tierIn.value = selectedTier;
        if ($divIn) $divIn.value = selectedDivision;
        $srvIn.value = selectedServer;

        // Update header
        $headerRankImg.src = RANK_IMGS[selectedTier];
        $headerRankImg.alt = RANK_NAMES[selectedTier];
        $headerRankTxt.textContent = pgRankDisplay(selectedTier, selectedDivision);
        $changeBtn.style.display = 'flex';
        $headerSub.textContent = '<?= t("Showing boosters & prices for") ?> ' + pgRankDisplay(selectedTier, selectedDivision);

        $overlay.classList.add('hidden');

        // Update Order Summary rank (overrides lol.js NaN issue)
        pgUpdateSummaryRank(selectedTier, selectedDivision);
        $tierIn.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof load_variables === 'function') {
            load_variables();
            setTimeout(() => pgUpdateSummaryRank(selectedTier, selectedDivision), 80);
        }

        document.querySelectorAll('.pg-rpill').forEach(p=>p.classList.toggle('active',parseInt(p.dataset.tier)===selectedTier));
        currentPage = 1;
        load();
    });

    $changeBtn.addEventListener('click', function() {
        $overlay.classList.remove('hidden');
    });

    // ── Filters ───────────────────────────────
    $toggle.addEventListener('click', function() {
        onlineOnly = !onlineOnly;
        this.classList.toggle('active', onlineOnly);
        currentPage = 1; renderPage();
    });

    $nameInp.addEventListener('input', function() {
        searchQ = this.value.toLowerCase().trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => { currentPage = 1; renderPage(); }, 300);
    });

    // ── Language filter ─────────────────────
    $langSel?.addEventListener('change', function() {
        selectedLang = this.value || 'all';
        // sync custom UI
        pgSetLangUI(selectedLang);
        currentPage = 1;
        renderPage();
    });

    // Custom dropdown interactions
    function pgLangDisplayName(code) {
        const c = String(code || '').toLowerCase();
        const map = {
            all: '<?= t("All") ?>',
            en: 'English',
            de: 'Deutsch',
            fr: 'Français',
            es: 'Español',
            pt: 'Português',
            it: 'Italiano',
            tr: 'Türkçe',
            ar: 'العربية',
            ru: 'Русский',
            pl: 'Polski',
            nl: 'Nederlands',
        };
        if (map[c]) return map[c];
        // If booster provides full language name already, keep it
        if (c.length > 2) return String(code);
        return (String(code || '').toUpperCase());
    }

    function pgLangFlagSrc(code) {
        return ASSET + '/core/main/img/flags/' + String(code).toLowerCase() + '.png';
    }

    function pgSetLangUI(code) {
        if (!$langValue) return;
        const c = (code || 'all');
        $langValue.textContent = pgLangDisplayName(c);

        if (!$langFlag || !$langGlobe) return;
        if (c === 'all') {
            $langFlag.style.display = 'none';
            $langGlobe.style.display = 'block';
        } else {
            $langFlag.src = pgLangFlagSrc(c);
            $langFlag.style.display = 'block';
            $langGlobe.style.display = 'none';
        }
    }

    function pgCloseLangMenu() {
        if (!$langDD || !$langBtn) return;
        $langDD.classList.remove('open');
        $langBtn.setAttribute('aria-expanded', 'false');
    }
    function pgToggleLangMenu() {
        if (!$langDD || !$langBtn) return;
        const isOpen = $langDD.classList.toggle('open');
        $langBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (isOpen) {
            // ensure active state is reflected
            const cur = ($langSel?.value || selectedLang || 'all');
            $langMenu?.querySelectorAll('.pg-lang-dd__opt').forEach(o => {
                o.classList.toggle('active', o.dataset.value === cur);
            });
        }
    }

    $langBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        pgToggleLangMenu();
    });
    document.addEventListener('click', function(e) {
        if (!$langDD) return;
        if (!$langDD.contains(e.target)) pgCloseLangMenu();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') pgCloseLangMenu();
    });

    // initial state
    pgSetLangUI(selectedLang);

    // ── Sort ──────────────────────────────────
    document.getElementById('pg-sort')?.addEventListener('change', function() {
        currentPage = 1;
        renderPage();
    });

    // ── Rank Pills ────────────────────────────
    document.querySelectorAll('.pg-rpill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            if (this.classList.contains('pg-rank-disabled') || this.disabled) return;
            const tier = parseInt(this.dataset.tier);
            selectedTier = tier;
            modalTier = tier;
            selectedDivision = pgRankHasDivision(tier) ? selectedDivision : <?= (int)$defaultDivision ?>;
            modalDivision = selectedDivision;
            if ($divIn) $divIn.value = selectedDivision;
            pgSyncDivisionVisibility(tier);
            document.querySelectorAll('.pg-rpill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.pg-rank-btn').forEach(b => b.classList.toggle('active', parseInt(b.dataset.tier) === tier));
            if ($headerRankImg) { $headerRankImg.src = RANK_IMGS[tier]; $headerRankImg.alt = RANK_NAMES[tier]; }
            if ($headerRankTxt) $headerRankTxt.textContent = pgRankDisplay(tier, selectedDivision);
            if ($changeBtn) $changeBtn.style.display = 'flex';
            if ($headerSub) $headerSub.textContent = '<?= t("Showing boosters & prices for") ?> ' + pgRankDisplay(tier, selectedDivision);
            if ($tierIn) $tierIn.value = tier;
            pgUpdateSummaryRank(tier, selectedDivision);
            // Prices per tier are local — just re-render, no AJAX needed
            currentPage = 1;
            renderPage();
        });
    });

    // ── Load boosters ─────────────────────────
    function load() {
        $grid.innerHTML = '<div class="pg-loading"><div class="pg-spinner"></div></div>';
        $pag.innerHTML = '';

        const fd = new FormData();
        fd.append('action', 'filter_boosters');
        // Always load all boosters; online toggle is handled client-side so offline boosters stay available when toggled off
        fd.append('online', '0');
        fd.append('name', $nameInp.value || '');
        fd.append(<?= $isClassic ? "'has_classic_pg_prices'" : "'has_pg_prices'" ?>, '1');
        <?php if ($isClassic): ?>
        // Classic Pro Games uses separate prices, but the booster card should
        // show the booster's normal LoL profile rank, champions and lanes.
        // display_game only changes what the card renders - it must not filter,
        // otherwise Classic would show fewer boosters than normal Pro Games.
        fd.append('display_game', 'lol');
        <?php endif; ?>
        if (selectedServer && selectedServer !== 'all') {
            fd.append('servers[]', selectedServer);
        }

        fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(r => r.text())
            .then(html => { parse(html); populateLangFilter(); renderPage(); })
            .catch(() => { $grid.innerHTML = '<div class="pg-bc__none"><?= t("Failed to load boosters.") ?></div>'; });
    }

    // ── Parse booster-cards HTML ──────────────
    function parse(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        allBoosters = [];
        tmp.querySelectorAll('.booster-card').forEach(card => {
            const link = card.closest('a');
            const id = link ? link.href.split('/').pop() : null;
            if (!id) return;
            // Classic and current LoL keep independent per-rank Pro Games prices.
            let pgPricesRaw = card.dataset.pgPrices || card.querySelector('[data-pg-prices]')?.dataset.pgPrices || '{}';
            <?php if ($isClassic): ?>
            // Classic has its own rank ladder (Salt..Legend), so only the dedicated
            // Classic price block applies — normal pg_prices are not compatible.
            try {
                const servicePrices = JSON.parse(card.dataset.servicePrices || '{}');
                pgPricesRaw = JSON.stringify(servicePrices.lol_classic_pro_games || {});
            } catch (e) {
                pgPricesRaw = '{}';
            }
            <?php endif; ?>
            let pgPrices = {};
            try { pgPrices = JSON.parse(pgPricesRaw); } catch(e) {}
            if (Object.values(pgPrices).every(v => !v || v == 0)) return;
            // Rank from max/ image (booster's own rank)
            const rankSrc = card.querySelector('.rank-box img')?.src || '';
            const rankMatch = rankSrc.match(/ranks\/(?:max|mini)\/(\d+)\.png/);
            const rankTier = rankMatch ? parseInt(rankMatch[1]) : 0;
            const lolProfileRankNames = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
            // Convert max/ to mini/ for display
            const rankMiniSrc = rankSrc.replace('/ranks/max/', '/ranks/mini/');
            // Languages
            const langNodes = Array.from(card.querySelectorAll('.lang-icon img, .languages img'));
            const langs = langNodes
                .map(i => (i.getAttribute('alt') || i.getAttribute('title') || '').trim())
                .filter(Boolean);
            const langImgs = langNodes.slice(0,4)
                .map(i => `<img src="${i.src}" alt="${i.alt}" title="${i.alt}">`).join('');
            allBoosters.push({
                id,
                name:        (card.querySelector('h5')?.textContent || '').trim().split('\n')[0].trim(),
                avatar:      card.querySelector('.avatar img')?.src || '',
                rankSrc:     rankMiniSrc,
                rankMax:     rankSrc,
                rankName:    lolProfileRankNames[rankTier] || 'Unranked',
                online:      card.querySelector('.booster-online-dot')?.classList.contains('online'),
                rating:      (() => { const badge = card.querySelector('.rating-badge'); if (!badge) return '5.0'; const clone = badge.cloneNode(true); clone.querySelectorAll('.review-count').forEach(el => el.remove()); const v = clone.textContent.replace(/[^\d.]/g,''); return v ? parseFloat(v).toFixed(1) : '5.0'; })(),
                reviewCount: (card.querySelector('.rating-badge .review-count')?.textContent || '').replace(/[^\d]/g,''),
                pgPrices,
                champImgs:   Array.from(card.querySelectorAll('.champion-icon')).slice(0,4)
                    .map(i => `<img src="${i.src}" alt="${i.alt}">`).join(''),
                roleImgs:    Array.from(card.querySelectorAll('.role-icon img'))
                    .map(i => `<img src="${i.src}" alt="${i.alt}">`).join(''),
                langImgs,
                langs,
                description: (s => { const t = document.createElement('textarea'); t.innerHTML = s; return t.value; })((card.dataset.description || '').trim()),
                timezone:    (card.dataset.timezone || '').trim(),
            });
        });
    }

    function populateLangFilter() {
        if (!$langSel) return;
        const prev = $langSel.value || selectedLang || 'all';
        const set = new Set();
        allBoosters.forEach(b => (b.langs || []).forEach(l => set.add(l)));
        const langsRaw = Array.from(set);
        const langs = langsRaw.sort((a,b)=>String(a).localeCompare(String(b)));

        // hidden <select> for logic
        $langSel.innerHTML = `<option value="all"><?= t('Language: All') ?></option>` +
            langs.map(l => `<option value="${String(l).replace(/"/g,'&quot;')}">${pgLangDisplayName(l)}</option>`).join('');
        $langSel.value = langs.includes(prev) ? prev : 'all';
        selectedLang = $langSel.value;

        // custom menu
        if ($langMenu) {
            const items = ['all', ...langs];
            $langMenu.innerHTML = items.map(v => {
                const isAll = v === 'all';
                const flag = isAll
                    ? `<div class="flag" style="display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-globe" style="font-size:0.9vw;color:rgba(255,255,255,.75)"></i></div>`
                    : `<div class="flag"><img src="${pgLangFlagSrc(v)}" alt="${pgLangDisplayName(v)}" loading="lazy"></div>`;
                return `
                    <div class="pg-lang-dd__opt ${v===selectedLang?'active':''}" data-value="${String(v).replace(/"/g,'&quot;')}">
                        ${flag}
                        <div class="label">${isAll ? '<?= t("All") ?>' : pgLangDisplayName(v)}</div>
                    </div>
                `;
            }).join('');

            $langMenu.querySelectorAll('.pg-lang-dd__opt').forEach(opt => {
                opt.addEventListener('click', function(e) {
                    e.preventDefault();
                    const v = this.dataset.value || 'all';
                    if ($langSel) {
                        $langSel.value = v;
                        $langSel.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    pgCloseLangMenu();
                });
            });
        }

        pgSetLangUI(selectedLang);
    }

    // ── Render ────────────────────────────────
    function renderPage() {
        $grid.innerHTML = '';
        let visible = allBoosters.filter(b => parseInt(b.pgPrices[selectedTier]) > 0);
        if (onlineOnly) visible = visible.filter(b => b.online);
        if (searchQ)    visible = visible.filter(b => b.name.toLowerCase().includes(searchQ));
        if (selectedLang && selectedLang !== 'all') {
            visible = visible.filter(b => (b.langs || []).includes(selectedLang));
        }
        const sv = document.getElementById('pg-sort')?.value||'default';
        if(sv==='price_asc')  visible.sort((a,b)=>(parseInt(a.pgPrices[selectedTier])||9999)-(parseInt(b.pgPrices[selectedTier])||9999));
        if(sv==='price_desc') visible.sort((a,b)=>(parseInt(b.pgPrices[selectedTier])||0)-(parseInt(a.pgPrices[selectedTier])||0));
        if(sv==='rating')     visible.sort((a,b)=>parseFloat(b.rating)-parseFloat(a.rating));
        if(sv==='online')     visible.sort((a,b)=>(b.online?1:0)-(a.online?1:0));
        const total = visible.length;
        if ($results) $results.textContent = `Found ${total} active pro gamers`;
        const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
        if (currentPage > totalPages) currentPage = 1;

        if (!total) {
            $grid.innerHTML = '<div class="pg-bc__none"><div class="pg-bc__none-icon">!</div><div class="pg-bc__none-title"><?= t("No boosters available") ?></div><div class="pg-bc__none-text"><?= t("No boosters are currently available for this rank. Try disabling Online Only or choose a different rank to see more results.") ?></div></div>';
            $pag.innerHTML = '';
            return;
        }

        visible.slice((currentPage-1)*PER_PAGE, currentPage*PER_PAGE).forEach(b => {
            if (!gameCounts[b.id]) gameCounts[b.id] = 3;
            const games = gameCounts[b.id];
            const pricePerGame = parseInt(b.pgPrices[selectedTier]) || 0;
            const totalPrice = pricePerGame * games;
            const priceStr = totalPrice ? pgFormatMoneyFromEurCents(totalPrice) : '–';
            // Build role icons
            const roleIcons = b.roleImgs
                ? b.roleImgs.replace(/<img ([^>]+)>/g, '<div class="pg-bc2__icon-box"><img $1></div>')
                : '';
            // Build champ icons
            const champIcons = b.champImgs
                ? b.champImgs.replace(/<img ([^>]+)>/g, '<div class="pg-bc2__champ-icon"><img $1></div>')
                : '';
            // Build language icons
            const langIcons = b.langImgs
                ? b.langImgs.replace(/<img ([^>]+)>/g, '<div class="pg-bc2__icon-box lang"><img $1></div>')
                : '';

            const el = document.createElement('div');
            el.className = 'pg-bc2' + (selectedId == b.id ? ' selected' : '');
            el.dataset.id = b.id;
            el.innerHTML = `
                <div class="pg-bc2__left">
                    <a class="pg-bc2__avatar-wrap" href="${BASE}/boosters/${b.id}" target="_blank" title="View ${b.name}'s profile" onclick="event.stopPropagation()">
                        <img class="pg-bc2__avatar" src="${b.avatar}" alt="${b.name}">
                        <div class="pg-bc2__avatar-overlay"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
                    </a>
                    <div class="pg-bc2__status-row">
                        <div class="pg-bc2__online-badge ${b.online ? 'online' : 'offline'}">
                            <span class="pg-bc2__online-dot"></span>
                            ${b.online ? '<?= t("Online") ?>' : '<?= t("Offline") ?>'}
                        </div>
                        ${b.timezone ? `<div class="pg-bc2__tz-left"><i class="fa-solid fa-earth-europe"></i>${b.timezone.split('/').pop().trim().replace('_',' ')}</div>` : ''}
                    </div>
                </div>
                <div class="pg-bc2__body">
                    <div class="pg-bc2__top-row">
                        <div>
                            <div class="pg-bc2__name-line">
                                <div class="pg-bc2__name">${b.name}</div>
                                <div class="pg-bc2__rating-inline">⭐ <span class="pg-bc2__rating-val">${b.rating}</span>${b.reviewCount ? `<span class="pg-bc2__review-count">(${b.reviewCount})</span>` : ''}</div>
                            </div>
                        </div>
                        <div class="pg-bc2__rank-box">
                            <img src="${b.rankMax}" alt="${b.rankName}">
                        </div>
                    </div>
                    <div class="pg-bc2__section">
                        <div class="pg-bc2__section-label"><?= t('Roles') ?></div>
                        <div class="pg-bc2__icons-row">${roleIcons || '<span class="pg-bc2__empty-tag">—</span>'}</div>
                    </div>
                    <div class="pg-bc2__section">
                        <div class="pg-bc2__section-label"><?= t('Champions') ?></div>
                        <div class="pg-bc2__bottom-row">
                            <div class="pg-bc2__champs-row">${champIcons || '<span class="pg-bc2__empty-tag">—</span>'}</div>
                            <div class="pg-bc2__lang-row">${langIcons}</div>
                        </div>
                    </div>
                    ${b.description ? `
                    <div class="pg-bc2__footer">
                        <div class="pg-bc2__desc" title="${b.description}">${b.description.length > 240 ? b.description.slice(0,240) + '…' : b.description}</div>
                    </div>` : ''}
                </div>
                <div class="pg-bc2__right">
                    <div class="pg-game-counter">
                        <button type="button" class="pg-game-btn" data-action="minus" data-id="${b.id}" ${games<=1?'disabled':''}>−</button>
                        <div>
                            <div class="pg-game-count">${games}</div>
                            <div class="pg-game-label"><?= t('games') ?></div>
                        </div>
                        <button type="button" class="pg-game-btn" data-action="plus" data-id="${b.id}" ${games>=5?'disabled':''}>+</button>
                    </div>
                    <div class="pg-bc2__price">
                        <div class="pg-bc2__price-val">${priceStr}</div>
                        <div class="pg-bc2__price-sub"><?= t('total') ?></div>
                        ${pricePerGame ? `<div class="pg-bc2__ppg">${pgFormatMoneyFromEurCents(pricePerGame)}/game</div>` : ''}
                    </div>

                </div>`;

            // Game counter events
            el.querySelectorAll('.pg-game-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const bid = this.dataset.id;
                    const action = this.dataset.action;
                    gameCounts[bid] = Math.min(5, Math.max(1, (gameCounts[bid] || 3) + (action === 'plus' ? 1 : -1)));
                    if (selectedId == bid) {
                        $matchIn.value = gameCounts[bid];
                        triggerPriceUpdate();
                        // Update sticky games count
                        const sGames = document.getElementById('pg-sticky-games');
                        const sPrice = document.getElementById('sticky-total-price');
                        const booster = allBoosters.find(b => b.id == bid);
                        if (sGames) sGames.textContent = gameCounts[bid];
                        if (sPrice && booster) sPrice.textContent = pgFormatMoneyFromEurCents((parseInt(booster.pgPrices[selectedTier]) || 0) * gameCounts[bid]);
                        updateSummary(booster || null);
                    }
                    renderPage();
                });
            });



            // Click on card body = select booster
            el.addEventListener('click', function(e) {
                if (!e.target.closest('.pg-game-btn')) {
                    selectBooster(b);
                }
            });
            $grid.appendChild(el);
        });

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        $pag.innerHTML = '';
        if (totalPages <= 1) return;
        $pag.appendChild(mkPageBtn('‹', currentPage===1, ()=>{ currentPage--; renderPage(); scrollToCard(); }));
        for (let i=1; i<=totalPages; i++) {
            $pag.appendChild(mkPageBtn(i, false, ()=>{ currentPage=i; renderPage(); scrollToCard(); }, i===currentPage));
        }
        $pag.appendChild(mkPageBtn('›', currentPage===totalPages, ()=>{ currentPage++; renderPage(); scrollToCard(); }));
    }

    function mkPageBtn(label, disabled, onClick, active=false) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pg-page-btn' + (active?' active':'');
        btn.textContent = label; btn.disabled = disabled;
        btn.addEventListener('click', onClick);
        return btn;
    }

    function selectBooster(booster) {
        selectedId = (selectedId == booster.id) ? null : booster.id;
        $input.value = selectedId || '';
        $matchIn.value = selectedId ? (gameCounts[booster.id] || 3) : 3;
        $tierIn.value = selectedTier;
        if ($divIn) $divIn.value = selectedDivision;
        updateSummary(selectedId ? booster : null);
        triggerPriceUpdate();
        renderPage();
        // Sync sticky overview for pro-games
        if (selectedId) {
            const gms = gameCounts[booster.id] || 3;
            const pgPrice = parseInt(booster.pgPrices[selectedTier]) || 0;
            const total = pgPrice * gms;
            // Direct DOM update (sticky is in lol.php)
            const sAvatar = document.getElementById('pg-sticky-avatar');
            const sName   = document.getElementById('pg-sticky-name');
            const sGames  = document.getElementById('pg-sticky-games');
            const sPrice  = document.getElementById('sticky-total-price');
            if (sAvatar) { sAvatar.src = booster.avatar || ''; sAvatar.style.display = booster.avatar ? 'block' : 'none'; }
            if (sName)   sName.textContent = booster.name || '';
            if (sGames)  sGames.textContent = gms;
            if (sPrice)  sPrice.textContent = CURRENCY_SYMBOL + (total / 100).toFixed(2);
            window.dispatchEvent(new CustomEvent('pg:boosterSelected', { detail: { name: booster.name, avatar: booster.avatar, games: gms, totalPrice: total }}));
        } else {
            const sAvatar = document.getElementById('pg-sticky-avatar');
            const sName   = document.getElementById('pg-sticky-name');
            const sPrice  = document.getElementById('sticky-total-price');
            if (sAvatar) { sAvatar.src = ''; sAvatar.style.display = 'none'; }
            if (sName)   sName.textContent = 'None selected';
            if (sPrice)  sPrice.textContent = CURRENCY_SYMBOL + '0.00';
            window.dispatchEvent(new CustomEvent('pg:cleared'));
        }
    }

    function updateSummary(booster) {
        const $sidePanel = document.getElementById('pg-selected-booster-summary');
        if (!$sidePanel) return;
        if (!booster) {
            $sidePanel.style.display = 'none';
            return;
        }
        const games = gameCounts[booster.id] || 3;
        const price = (parseInt(booster.pgPrices[selectedTier]) || 0) * games;
        $sidePanel.style.display = 'flex';
        $sidePanel.innerHTML = `
            <div class="pg-sum__avatar-wrap">
                <img class="pg-sum__avatar" src="${booster.avatar}" alt="${booster.name}">
                <span class="pg-sum__dot ${booster.online ? 'online' : 'offline'}"></span>
            </div>
            <div class="pg-sum__info">
                <div class="pg-sum__label"><?= t('Selected Booster') ?></div>
                <div class="pg-sum__name">${booster.name}</div>
                <div class="pg-sum__meta">
                    <span class="pg-sum__games">${games} <?= t('Games') ?></span>
                    ${price ? `<span class="pg-sum__price">${pgFormatMoneyFromEurCents(price)}</span>` : ''}
                </div>
            </div>
            <button type="button" class="pg-sum__change" onclick="document.getElementById('pg-change-rank-btn').click()">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>
        `;
    }

    function triggerPriceUpdate() {
        if ($input) $input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // ── Order Summary Rank Fix ─────────────────────
    // lol.js reads radio[name=start_tier] → NaN for Pro Games hidden input → broken img
    // We override the summary after every update
    function classicPgRankImage(tier, division) {
        const names = {0:'unranked',1:'salt',2:'wood',3:'silver',4:'gold',5:'platinum',6:'diamond',7:'legend'};
        return (names[tier] || 'salt') + '.webp';
    }

    function pgUpdateSummaryRank(tier, division = selectedDivision) {
        const img = <?= $isClassic ? "ASSET + '/website/images/lol-classic/ranks/' + classicPgRankImage(tier, division)" : "ASSET + '/core/main/img/lol/ranks/mini/' + tier + '.png'" ?>;
        document.querySelectorAll('.current-summary-rank-img').forEach(el => {
            el.src = img;
            el.alt = pgRankDisplay(tier, division);
            el.onerror = null;
        });
        document.querySelectorAll('.current-summary-rank-name').forEach(el => {
            el.textContent = pgRankDisplay(tier, division);
        });
        document.querySelectorAll('.current-summary-lp').forEach(el => {
            el.style.display = 'none';
        });
    }

    // Apply immediately + after lol.js fires on window load
    pgUpdateSummaryRank(selectedTier, selectedDivision);
    window.addEventListener('load', function() {
        pgUpdateSummaryRank(selectedTier, selectedDivision);
        // lol.js may overwrite after load — patch again after short delay
        setTimeout(() => pgUpdateSummaryRank(selectedTier, selectedDivision), 200);
    });

    // Don't auto-load — wait for modal confirm
})();
</script>
