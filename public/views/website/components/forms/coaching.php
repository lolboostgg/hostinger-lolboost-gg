<?php
$uiGame = ($data['game'] ?? 'lol');
$isClassic = util_is_lol_classic($uiGame);
if ($uiGame === 'tft') { $uiGame = 'lol'; }
$ranks = $isClassic ? [
    0 => 'Unranked',
    1 => 'Salt',
    2 => 'Wood',
    3 => 'Silver',
    4 => 'Gold',
    5 => 'Platinum',
    6 => 'Diamond',
    7 => 'Legend',
] : [
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
$defaultRank = $isClassic ? 1 : 3;

if (!function_exists('lol_classic_rank_asset_url')) {
    function lol_classic_rank_asset_url($tier, $division = 5) {
        $tier = (int)$tier;
        $division = (int)$division;
        $tierNames = [0 => 'unranked', 1 => 'bronze', 2 => 'silver', 3 => 'gold', 4 => 'platinum', 5 => 'diamond', 6 => 'master', 7 => 'challenger'];
        if ($tier === 0 || $tier >= 6) {
            return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'unranked') . '.png';
        }
        $divisionNames = [5 => 'v', 4 => 'iv', 3 => 'iii', 2 => 'ii', 1 => 'i'];
        return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'bronze') . '-' . ($divisionNames[$division] ?? 'v') . '.png';
    }
}
?>
<div class="boost win-boost coaching-boost-form">
    <div class="card count-card">
        <div class="card-header">
            <div class="count hour-count"><?= t('5') ?></div>
            <div class="text">
                <h3><?= t('Coaching Hours') ?></h3>
                <p><?= t('Select your desired amount of coaching hours.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="hours_slider"></div>
            <input class="form-control range-slider-value-min" name="hours" type="number" hidden>

            <div class="coaching-rank-block">
                <div class="coaching-rank-title">
                    <h6><?= t('Current Rank') ?></h6>
                </div>
                <div class="ranks coaching-current-ranks">
                    <?php foreach ($ranks as $i => $rankName): ?>
                        <label>
                            <input type="radio" name="current_rank" id="coach_rank_<?= $i; ?>" value="<?= $i; ?>"
                                   class="custom-checkbox" <?= $i == $defaultRank ? 'checked' : ''; ?>>
                            <div class="rank-btn">
                                <img src="<?= $isClassic ? lol_classic_rank_asset_url($i, 5) : util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= htmlspecialchars($rankName); ?>">
                                <span class="tooltip"><?= t($rankName); ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="options">
                <div class="option">
                    <h6><?= t('Coaching Type') ?></h6>
                    <select class="select2" name="coach_type" data-no-search="true">
                        <option value="Co-Pilot" selected
                            data-bs-content="Co-Pilot: An expert Coach guides you while you play"><?= t('Co-Pilot') ?></option>
                        <option value="VOD-Review"
                            data-bs-content="VOD-Review: An Expert Coach will provide in-depth analysis of your pre-recorded games"><?= t('VOD-Review') ?></option>
                    </select>
                </div>
                <div class="option">
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
            </div>
        </div>
    </div>
</div>

<style>
.coaching-boost-form .coaching-rank-block {
    width: 100%;
    display: block !important;
    margin: 30px 0 24px;
    clear: both;
}

.coaching-boost-form .coaching-rank-title {
    display: block !important;
    width: 100% !important;
    margin: 0 0 14px 0 !important;
    padding: 0 !important;
    clear: both;
}

/* Titel größer */
.coaching-boost-form .coaching-rank-title h6 {
    display: block !important;
    position: static !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1.2 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    letter-spacing: .02em !important;
}

/* ── Desktop: Icons gleichmäßig als Grid ── */
.coaching-boost-form .coaching-current-ranks {
    width: 100%;
    display: grid !important;
    grid-template-columns: <?= $isClassic ? "repeat(7, 1fr)" : "repeat(11, 1fr)" ?> !important;
    gap: 8px !important;
    margin: 0 !important;
    padding: 0 !important;
    clear: both;
}

.coaching-boost-form .coaching-current-ranks label {
    margin: 0 !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn {
    width: 100% !important;
    aspect-ratio: 1 / 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-sizing: border-box !important;
    border-radius: 10px !important;
    transition: transform .14s !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn img {
    width: <?= $isClassic ? "62%" : "52%" ?> !important;
    height: <?= $isClassic ? "62%" : "52%" ?> !important;
    object-fit: contain !important;
    display: block !important;
}

/* ── Mobile: 4 Spalten, große Icons, kein Scrollen ── */
@media (max-width: 600px) {
    .coaching-boost-form .coaching-rank-title h6 {
        font-size: 16px !important;
    }

    .coaching-boost-form .coaching-current-ranks {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 10px !important;
    }

    .coaching-boost-form .coaching-current-ranks .rank-btn {
        border-radius: 14px !important;
        min-height: 64px !important;
    }

    .coaching-boost-form .coaching-current-ranks .rank-btn img {
        width: 58% !important;
        height: 58% !important;
    }
}


/* Compact coaching-only rank selector.
   This intentionally overrides the larger LoL Classic rank cards used on normal boost forms. */
.coaching-boost-form .coaching-current-ranks {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    align-items: stretch !important;
    gap: 8px !important;
    width: 100% !important;
}
.coaching-boost-form .coaching-current-ranks > label {
    flex: 0 0 calc((100% - 24px) / 4) !important;
    width: calc((100% - 24px) / 4) !important;
    max-width: calc((100% - 24px) / 4) !important;
}

.coaching-boost-form .coaching-current-ranks > label {
    width: auto !important;
    max-width: none !important;
    min-width: 0 !important;
    flex: none !important;
    margin: 0 !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn {
    width: 100% !important;
    height: <?= $isClassic ? "72px" : "68px" ?> !important;
    min-height: 0 !important;
    aspect-ratio: auto !important;
    padding: 6px 5px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 3px !important;
    border-radius: 10px !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn img {
    width: <?= $isClassic ? "38px" : "36px" ?> !important;
    height: <?= $isClassic ? "38px" : "36px" ?> !important;
    min-width: 0 !important;
    object-fit: contain !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn .tooltip {
    position: static !important;
    inset: auto !important;
    transform: none !important;
    display: block !important;
    width: auto !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    opacity: 1 !important;
    visibility: visible !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    color: rgba(255,255,255,.68) !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
    pointer-events: none !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn .tooltip::before,
.coaching-boost-form .coaching-current-ranks .rank-btn .tooltip::after {
    display: none !important;
}

.coaching-boost-form .coaching-current-ranks input:checked + .rank-btn {
    border-color: rgba(145,128,255,.9) !important;
    box-shadow: 0 0 0 1px rgba(255,255,255,.28) inset !important;
    transform: none !important;
}

.coaching-boost-form .coaching-current-ranks input:checked + .rank-btn .tooltip {
    color: #fff !important;
}

@media (max-width: 900px) {
    .coaching-boost-form .coaching-current-ranks > label {
        flex-basis: calc((100% - 16px) / 3) !important;
        width: calc((100% - 16px) / 3) !important;
        max-width: calc((100% - 16px) / 3) !important;
    }
}

@media (max-width: 600px) {
    .coaching-boost-form .coaching-current-ranks {
        gap: 7px !important;
    }

    .coaching-boost-form .coaching-current-ranks > label {
        flex-basis: calc((100% - 7px) / 2) !important;
        width: calc((100% - 7px) / 2) !important;
        max-width: calc((100% - 7px) / 2) !important;
    }

    .coaching-boost-form .coaching-current-ranks .rank-btn {
        height: 66px !important;
        border-radius: 9px !important;
    }

    .coaching-boost-form .coaching-current-ranks .rank-btn img {
        width: 34px !important;
        height: 34px !important;
    }
}



/* Final coaching rank sizing:
   Desktop keeps every rank in one row.
   Normal LoL remains readable, LoL Classic uses seven larger cards. */
.coaching-boost-form .coaching-current-ranks {
    display: flex !important;
    flex-wrap: nowrap !important;
    justify-content: center !important;
    align-items: stretch !important;
    gap: <?= $isClassic ? "10px" : "7px" ?> !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
    padding: 2px 2px 8px !important;
    scrollbar-width: thin;
}

.coaching-boost-form .coaching-current-ranks > label {
    flex: 0 0 <?= $isClassic ? "112px" : "78px" ?> !important;
    width: <?= $isClassic ? "112px" : "78px" ?> !important;
    max-width: <?= $isClassic ? "112px" : "78px" ?> !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn {
    width: 100% !important;
    height: <?= $isClassic ? "88px" : "82px" ?> !important;
    min-height: <?= $isClassic ? "88px" : "82px" ?> !important;
    padding: <?= $isClassic ? "9px 7px" : "8px 6px" ?> !important;
    border-radius: 11px !important;
    gap: 5px !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn img {
    width: <?= $isClassic ? "48px" : "42px" ?> !important;
    height: <?= $isClassic ? "48px" : "42px" ?> !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn .tooltip {
    font-size: <?= $isClassic ? "11px" : "10px" ?> !important;
}

@media (max-width: 900px) {
    .coaching-boost-form .coaching-current-ranks {
        justify-content: flex-start !important;
    }
}

@media (max-width: 600px) {
    .coaching-boost-form .coaching-current-ranks {
        flex-wrap: nowrap !important;
        justify-content: flex-start !important;
        overflow-x: auto !important;
        gap: 8px !important;
    }

    .coaching-boost-form .coaching-current-ranks > label {
        flex-basis: <?= $isClassic ? "94px" : "74px" ?> !important;
        width: <?= $isClassic ? "94px" : "74px" ?> !important;
        max-width: <?= $isClassic ? "94px" : "74px" ?> !important;
    }

    .coaching-boost-form .coaching-current-ranks .rank-btn {
        height: <?= $isClassic ? "80px" : "76px" ?> !important;
        min-height: <?= $isClassic ? "80px" : "76px" ?> !important;
    }

    .coaching-boost-form .coaching-current-ranks .rank-btn img {
        width: <?= $isClassic ? "42px" : "38px" ?> !important;
        height: <?= $isClassic ? "42px" : "38px" ?> !important;
    }
}



/* Normal LoL coaching, distribute all rank cards across the full available width. */
<?php if (!$isClassic): ?>
.coaching-boost-form .coaching-current-ranks {
    display: grid !important;
    grid-template-columns: repeat(11, minmax(0, 1fr)) !important;
    width: 100% !important;
    gap: 8px !important;
    justify-content: stretch !important;
    overflow: visible !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

.coaching-boost-form .coaching-current-ranks > label {
    width: 100% !important;
    min-width: 0 !important;
    max-width: none !important;
    flex: none !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn {
    width: 100% !important;
    height: 88px !important;
    min-height: 88px !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn img {
    width: 46px !important;
    height: 46px !important;
}

.coaching-boost-form .coaching-current-ranks .rank-btn .tooltip {
    font-size: 10px !important;
}

@media (max-width: 900px) {
    .coaching-boost-form .coaching-current-ranks {
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: flex-start !important;
        overflow-x: auto !important;
    }

    .coaching-boost-form .coaching-current-ranks > label {
        flex: 0 0 78px !important;
        width: 78px !important;
        min-width: 78px !important;
    }
}
<?php endif; ?>

</style>
