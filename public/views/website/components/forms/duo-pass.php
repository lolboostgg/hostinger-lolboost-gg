<?php
$uiGame = ($data['game'] ?? 'lol');
$isClassic = util_is_lol_classic($uiGame);
$ranks = $isClassic ? [
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
$defaultTier = $isClassic ? 1 : 3;
$defaultDivision = 4;
$divisionLabelsPhp = [4 => 'IV', 3 => 'III', 2 => 'II', 1 => 'I'];
$apexFrom = $isClassic ? 7 : 8;
$servers = [
    'euw'  => 'EU-West',
    'eune' => 'EU-Nordic & East',
    'me'   => 'Middle East',
    'na'   => 'North America',
    'tr'   => 'Turkey',
    'ru'   => 'Russia',
    'oce'  => 'Oceania',
    'lan'  => 'LAN',
    'las'  => 'LAS',
    'br'   => 'Brazil',
    'jp'   => 'Japan',
    'kr'   => 'Korea',
    'sg'   => 'Singapore',
    'ph'   => 'Philippines',
    'th'   => 'Thailand',
    'tw'   => 'Taiwan',
    'vn'   => 'Vietnam',
];
$hoursOptions = [3, 6, 8];

$pricing = $data['json']['main'] ?? [];

if (!function_exists('lol_classic_rank_asset_url')) {
    function lol_classic_rank_asset_url($tier, $division = 5) {
        $tier = (int)$tier;
        $division = (int)$division;
        $tierNames = [0 => 'unranked', 1 => 'bronze', 2 => 'silver', 3 => 'gold', 4 => 'platinum', 5 => 'diamond', 7 => 'challenger'];
        if ($tier === 0 || $tier === 7) {
            return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'unranked') . '.png';
        }
        $divisionNames = [5 => 'v', 4 => 'iv', 3 => 'iii', 2 => 'ii', 1 => 'i'];
        return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'bronze') . '-' . ($divisionNames[$division] ?? 'v') . '.png';
    }
}
?>


<input type="hidden" name="queue_type" value="solo_/_duo">
<input type="hidden" name="hours" id="dp-hours-input" value="3">
<input type="hidden" name="start_division" id="dp-start-division" value="<?= $defaultDivision ?>">
<input type="hidden" name="booster_id" value="0">

<div class="boost win-boost duo-pass-form duo-pass-redesign">
    <div class="dp-shell">
        <div class="dp-setup-card">
            <div class="dp-head">
                <div class="dp-head__icon">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="dp-head__text">
                    <h3><?= t('Set Up Your Duo Pass') ?></h3>
                    <p><?= t('Choose your current rank, play time, and server.') ?></p>
                </div>
            </div>

            <div class="dp-section">
                <div class="dp-label"><?= t('Current Rank') ?></div>
                <?php $topRanks = $isClassic ? $ranks : array_slice($ranks, 0, 6, true); ?>
                <?php $bottomRanks = $isClassic ? [] : array_slice($ranks, 6, null, true); ?>
                <div class="dp-rank-grid <?= $isClassic ? 'dp-rank-grid--classic' : '' ?>">
                    <div class="dp-rank-row dp-rank-row--top">
                        <?php foreach ($topRanks as $tier => $label): ?>
                            <label class="dp-rank-item">
                                <input type="radio" name="start_tier" value="<?= $tier ?>" class="custom-checkbox" <?= $tier === $defaultTier ? 'checked' : '' ?>>
                                <span class="dp-rank-btn">
                                    <img src="<?= $isClassic ? lol_classic_rank_asset_url($tier, $defaultDivision) : util_rank_img($uiGame, 'mini', $tier); ?>" alt="<?= htmlspecialchars($label); ?>">
                                    <strong><?= htmlspecialchars($label); ?></strong>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($bottomRanks)): ?>
                    <div class="dp-rank-row dp-rank-row--bottom">
                        <?php foreach ($bottomRanks as $tier => $label): ?>
                            <label class="dp-rank-item">
                                <input type="radio" name="start_tier" value="<?= $tier ?>" class="custom-checkbox">
                                <span class="dp-rank-btn">
                                    <img src="<?= $isClassic ? lol_classic_rank_asset_url($tier, $defaultDivision) : util_rank_img($uiGame, 'mini', $tier); ?>" alt="<?= htmlspecialchars($label); ?>">
                                    <strong><?= htmlspecialchars($label); ?></strong>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dp-division-wrap" id="dp-division-wrap">
                <div class="dp-label"><?= t('Current Division') ?></div>
                <div class="dp-division-pills" id="dp-division-pills">
                    <?php foreach ($divisionLabelsPhp as $divisionValue => $divisionLabel): ?>
                        <button type="button" class="dp-division-pill <?= $divisionValue === $defaultDivision ? 'active' : '' ?>" data-division="<?= $divisionValue ?>"><?= $divisionLabel ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dp-config-grid">
                <div class="dp-config-card">
                    <div class="dp-label"><?= t('Duration') ?></div>
                    <div class="dp-duration-pills">
                        <?php foreach ($hoursOptions as $hours): ?>
                            <button type="button" class="dp-duration-pill <?= $hours === 3 ? 'active' : '' ?>" data-hours="<?= $hours ?>">
                                <span class="dp-duration-pill__value"><?= $hours ?>h</span>
                                <span class="dp-duration-pill__sub"><?= t('Duo Pass') ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dp-config-card">
                    <div class="dp-label"><?= t('Server') ?></div>
                    <input type="hidden" name="server" id="dp-server-select" value="euw">
                    <div class="dp-custom-select" id="dp-custom-server">
                        <button type="button" class="dp-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="dp-custom-select__label">EU-West</span>
                            <svg class="dp-custom-select__arrow" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <ul class="dp-custom-select__dropdown" role="listbox">
                            <?php foreach ($servers as $value => $label): ?>
                                <li class="dp-custom-select__option <?= $value === 'euw' ? 'is-selected' : '' ?>" role="option" data-value="<?= $value ?>" aria-selected="<?= $value === 'euw' ? 'true' : 'false' ?>"><?= t($label) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.duo-pass-redesign,
.duo-pass-redesign .dp-shell,
.duo-pass-redesign .dp-setup-card {width:100%;max-width:100%;}
.duo-pass-redesign .dp-setup-card {
    background: rgba(255,255,255,.022);
    border: .05vw solid rgba(255,255,255,.045);
    box-shadow: none;
    border-radius: 1.5vw;
    padding: 1.35vw;
    position: relative;
    overflow: visible;
}
.duo-pass-redesign .dp-setup-card:before {
    content:none;
    display:none;
}
.duo-pass-redesign .dp-head {
    display:flex;align-items:center;gap:1vw;padding-bottom:1.15vw;margin-bottom:1.2vw;border-bottom:.05vw solid rgba(255,255,255,.08);position:relative;z-index:1;
}
.duo-pass-redesign .dp-head__icon {
    width:2.55vw;height:2.55vw;border-radius:.72vw;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(180deg, rgba(124,92,252,.34) 0%, rgba(124,92,252,.18) 100%);
    color:#fff;font-size:1vw;box-shadow:none;
}
.duo-pass-redesign .dp-head__text h3 {margin:0;color:#fff;font-size:1.4vw;font-weight:800;line-height:1.1;}
.duo-pass-redesign .dp-head__text p {margin:.28vw 0 0;color:rgba(255,255,255,.55);font-size:.84vw;font-weight:500;}
.duo-pass-redesign .dp-section,.duo-pass-redesign .dp-config-grid {position:relative;z-index:1;}
.duo-pass-redesign .dp-label {margin-bottom:.7vw;color:#9ea0d2;font-size:.78vw;font-weight:800;letter-spacing:.08em;text-transform:uppercase;}
.duo-pass-redesign .dp-rank-grid {display:grid;gap:.75vw;}
.duo-pass-redesign .dp-rank-row {display:grid;gap:.72vw;}
.duo-pass-redesign .dp-rank-row--top {grid-template-columns:repeat(6,minmax(0,1fr));}
.duo-pass-redesign .dp-rank-grid--classic .dp-rank-row--top {grid-template-columns:repeat(6,minmax(0,1fr)) !important;}
.duo-pass-redesign .dp-rank-row--bottom {grid-template-columns:repeat(5,minmax(0,1fr));width:calc(100% - 7.2vw);margin:0 auto;}
.duo-pass-redesign .dp-rank-item {margin:0;display:block;}
.duo-pass-redesign .dp-rank-item input[type="radio"] {position:absolute;width:1px;height:1px;margin:-1px;padding:0;border:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;}
.duo-pass-redesign .dp-rank-btn {
    min-height:5.2vw;border-radius:.85vw;border:.05vw solid rgba(255,255,255,.06);
    background:rgba(255,255,255,.022);
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.42vw;
    transition:all .18s ease;color:#fff;padding:.55vw .45vw;cursor:pointer;
}
.duo-pass-redesign .dp-rank-btn img {width:2.5vw;height:2.5vw;object-fit:contain;display:block;}
.duo-pass-redesign .dp-rank-btn strong {font-size:.62vw;line-height:1.1;font-weight:700;color:rgba(255,255,255,.84);}
/* Disabled ranks (Master / Grandmaster / Challenger) */
.duo-pass-redesign .dp-rank-item--disabled {cursor:not-allowed;position:relative;}
.duo-pass-redesign .dp-rank-item--disabled input[type="radio"] {pointer-events:none;}
.duo-pass-redesign .dp-rank-item--disabled .dp-rank-btn {
    opacity:1;filter:none;cursor:not-allowed;pointer-events:none;
}
.duo-pass-redesign .dp-rank-item--disabled .dp-rank-btn > img,
.duo-pass-redesign .dp-rank-item--disabled .dp-rank-btn > strong {
    opacity:.38;filter:grayscale(.55);
}
.duo-pass-redesign .dp-rank-item--disabled:hover .dp-rank-btn {
    border-color:rgba(255,80,80,.35) !important;background:linear-gradient(180deg, rgba(40,10,10,.78) 0%, rgba(50,12,12,.96) 100%) !important;
    box-shadow:none !important;opacity:1 !important;filter:none !important;
}
/* Tooltip */
.duo-pass-redesign .dp-rank-tooltip {
    display:none;position:absolute;bottom:calc(100% + .6vw);left:50%;transform:translateX(-50%);
    background:#160b0f;border:2px solid #ff4040;border-radius:.7vw;
    color:#fff;font-size:.7vw;font-weight:600;line-height:1.4;
    padding:.55vw .8vw;white-space:nowrap;z-index:9999;pointer-events:none;
    box-shadow:none;opacity:1;text-align:center;
}
.duo-pass-redesign .dp-rank-tooltip::after {
    content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);
    border:5px solid transparent;border-top-color:rgba(255,80,80,.95);
}
.duo-pass-redesign .dp-rank-item--disabled:hover .dp-rank-tooltip {display:block;}

.duo-pass-redesign .dp-rank-item input:checked + .dp-rank-btn {
    border-color:#8f74ff;
    background:rgba(99,102,241,.14);
    box-shadow:none;
}
.duo-pass-redesign .dp-rank-item input:checked + .dp-rank-btn strong {color:#fff;}
.duo-pass-redesign .dp-division-wrap {margin-top:1vw;position:relative;z-index:1;}
.duo-pass-redesign .dp-division-wrap.is-hidden {display:none !important;}
.duo-pass-redesign .dp-division-pills {display:grid;grid-template-columns:repeat(<?= count($divisionLabelsPhp) ?>,minmax(0,1fr));gap:.62vw;}
.duo-pass-redesign .dp-division-pill {
    min-height:2.6vw;border-radius:.72vw;border:.05vw solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.022);
    color:rgba(255,255,255,.82);font-size:.9vw;font-weight:800;cursor:pointer;transition:all .18s ease;
}
.duo-pass-redesign .dp-division-pill:hover,
.duo-pass-redesign .dp-division-pill.active {
    border-color:#8f74ff;background:rgba(99,102,241,.14);
    box-shadow:none;color:#fff;
}
.duo-pass-redesign .dp-division-help {margin:.55vw 0 0;color:rgba(255,255,255,.5);font-size:.72vw;font-weight:600;}
.duo-pass-redesign .dp-config-grid {display:grid;grid-template-columns:1fr 1fr;gap:1vw;margin-top:1.3vw;}
.duo-pass-redesign .dp-config-card {
    border:.05vw solid rgba(255,255,255,.045);
    background:rgba(255,255,255,.022);
    border-radius:.9vw;padding:.8vw .85vw;
}
.duo-pass-redesign .dp-duration-pills {display:grid;grid-template-columns:repeat(3,1fr);gap:.62vw;}
.duo-pass-redesign .dp-duration-pill {
    min-height:3.3vw;border-radius:.75vw;border:.05vw solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.022);
    display:flex;flex-direction:column;align-items:flex-start;justify-content:center;
    padding:.9vw 1vw;color:#fff;transition:all .18s ease;cursor:pointer;
}
.duo-pass-redesign .dp-duration-pill__value {font-size:1.15vw;font-weight:800;line-height:1;}
.duo-pass-redesign .dp-duration-pill__sub {margin-top:.22vw;font-size:.72vw;font-weight:700;color:rgba(255,255,255,.56);}
.duo-pass-redesign .dp-duration-pill.active {
    border-color:#8f74ff;background:rgba(99,102,241,.14);
    box-shadow:none;
}
.duo-pass-redesign .dp-duration-pill.active .dp-duration-pill__sub {color:rgba(255,255,255,.88);}
/* Custom Server Dropdown */
.duo-pass-redesign .dp-custom-select {position:relative;width:100%;}
.duo-pass-redesign .dp-custom-select__trigger {
    width:100%;height:3.3vw;display:flex;align-items:center;justify-content:space-between;
    border-radius:.9vw;border:.05vw solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.022);
    padding:0 1vw;cursor:pointer;color:#fff;font-size:.95vw;font-weight:700;
    transition:border-color .18s ease, box-shadow .18s ease;
}
.duo-pass-redesign .dp-custom-select__trigger:hover {border-color:rgba(143,116,255,.35);}
.duo-pass-redesign .dp-custom-select.is-open .dp-custom-select__trigger {
    border-color:#8f74ff;box-shadow:none;
}
.duo-pass-redesign .dp-custom-select__label {flex:1;text-align:left;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.duo-pass-redesign .dp-custom-select__arrow {
    width:.85vw;height:.85vw;color:rgba(255,255,255,.7);flex-shrink:0;margin-left:.5vw;
    transition:transform .2s ease;
}
.duo-pass-redesign .dp-custom-select.is-open .dp-custom-select__arrow {transform:rotate(180deg);}
.duo-pass-redesign .dp-custom-select__dropdown {
    display:none;position:absolute;top:calc(100% + .4vw);left:0;right:0;z-index:9999;
    background:#100d2b;border:1px solid rgba(124,92,252,.35);
    border-radius:.9vw;box-shadow:none;
    overflow:hidden auto;max-height:16vw;list-style:none;margin:0;padding:.3vw 0;
    scrollbar-width:thin;scrollbar-color:rgba(143,116,255,.3) transparent;
}
.duo-pass-redesign .dp-custom-select.is-open .dp-custom-select__dropdown {display:block;}
.duo-pass-redesign .dp-custom-select__option {
    padding:.62vw 1vw;color:#e8e8f4;font-size:.9vw;font-weight:600;cursor:pointer;
    transition:background .12s ease;
}
.duo-pass-redesign .dp-custom-select__option:hover {background:rgba(124,92,252,.2);color:#fff;}
.duo-pass-redesign .dp-custom-select__option.is-selected {background:rgba(124,92,252,.28);color:#c5b4ff;}

/* Match the shared boost-card surface on the Duo Pass summary/payment side. */
.lol-boost .form-content:has(.duo-pass-redesign) .order-summary,
.lol-boost .form-content:has(.duo-pass-redesign) .payment-gateways {
    background:rgba(255,255,255,.022) !important;
    border-color:rgba(255,255,255,.045) !important;
    box-shadow:none !important;
}

@media (max-width: 1024px) {
    .duo-pass-redesign .dp-setup-card {padding:22px;border-radius:24px;}
    .duo-pass-redesign .dp-head {gap:14px;padding-bottom:16px;margin-bottom:18px;}
    .duo-pass-redesign .dp-head__icon {width:40px;height:40px;border-radius:12px;font-size:16px;}
    .duo-pass-redesign .dp-head__text h3 {font-size:28px;}
    .duo-pass-redesign .dp-head__text p {font-size:13px;margin-top:4px;}
    .duo-pass-redesign .dp-label {font-size:12px;margin-bottom:10px;}
    .duo-pass-redesign .dp-rank-row--top {grid-template-columns:repeat(4,minmax(0,1fr));}
    .duo-pass-redesign .dp-rank-row--bottom {grid-template-columns:repeat(4,minmax(0,1fr));width:100%;}
    .duo-pass-redesign .dp-rank-btn {min-height:92px;border-radius:16px;padding:10px 8px;gap:8px;}
    .duo-pass-redesign .dp-rank-btn img {width:42px;height:42px;}
    .duo-pass-redesign .dp-rank-btn strong {font-size:12px;}
    .duo-pass-redesign .dp-division-wrap {margin-top:14px;}
    .duo-pass-redesign .dp-division-pills {gap:10px;}
    .duo-pass-redesign .dp-division-pill {min-height:48px;border-radius:14px;font-size:14px;}
    .duo-pass-redesign .dp-division-help {font-size:11px;margin-top:6px;}
    .duo-pass-redesign .dp-config-grid {grid-template-columns:1fr;gap:14px;margin-top:18px;}
    .duo-pass-redesign .dp-config-card {padding:16px;border-radius:18px;}
    .duo-pass-redesign .dp-duration-pills {gap:10px;}
    .duo-pass-redesign .dp-duration-pill {min-height:68px;border-radius:14px;padding:14px 16px;}
    .duo-pass-redesign .dp-duration-pill__value {font-size:20px;}
    .duo-pass-redesign .dp-duration-pill__sub {font-size:11px;margin-top:4px;}
    .duo-pass-redesign .dp-custom-select__trigger {height:58px;border-radius:14px;padding:0 14px;font-size:14px;}
    .duo-pass-redesign .dp-custom-select__arrow {width:13px;height:13px;}
    .duo-pass-redesign .dp-custom-select__dropdown {border-radius:14px;max-height:260px;top:calc(100% + 6px);}
    .duo-pass-redesign .dp-custom-select__option {padding:14px 18px;font-size:16px;font-weight:700;}
    .duo-pass-redesign .dp-rank-tooltip {font-size:11px;border-radius:10px;padding:8px 12px;bottom:calc(100% + 8px);}
    .duo-pass-redesign .dp-rank-tooltip::after {border-width:4px;}
}

@media (max-width: 640px) {
    .duo-pass-redesign .dp-rank-row--top,
    .duo-pass-redesign .dp-rank-row--bottom {grid-template-columns:repeat(3,minmax(0,1fr));}
    .duo-pass-redesign .dp-duration-pills {grid-template-columns:1fr;}
    .duo-pass-redesign .dp-division-pills {grid-template-columns:repeat(<?= count($divisionLabelsPhp) ?>,minmax(0,1fr));}
}

/* Arrow summary icon mobile size */
@media (max-width: 1024px) {
    img[alt="arrow_icon"] { width: 22px !important; height: 22px !important; }
}
@media (max-width: 640px) {
    img[alt="arrow_icon"] { width: 20px !important; height: 20px !important; }
}
</style>

<script>
(function () {
    const rankNames = <?= json_encode($ranks) ?>;
    const hoursOptions = <?= json_encode($hoursOptions) ?>;
    const pricing = <?= json_encode($pricing, JSON_UNESCAPED_SLASHES) ?>;
    const serverBuckets = {
        euw: 'eu', eune: 'eu', me: 'eu', tr: 'eu', ru: 'eu',
        na: 'na', br: 'na', lan: 'na', las: 'na', oce: 'na', jp: 'na', kr: 'na',
        sg: 'na', ph: 'na', th: 'na', tw: 'na', vn: 'na'
    };

    const hoursInput = document.getElementById('dp-hours-input');
    const divisionInput = document.getElementById('dp-start-division');
    const durationButtons = document.querySelectorAll('.dp-duration-pill');
    const divisionButtons = document.querySelectorAll('.dp-division-pill');
    const divisionLabels = <?= json_encode($divisionLabelsPhp) ?>;
    let currentDivision = parseInt(divisionInput && divisionInput.value ? divisionInput.value : <?= (int)$defaultDivision ?>, 10) || <?= (int)$defaultDivision ?>;
    let loadTimer = null;

    function euro(cents) {
        return '€' + (Math.max(0, parseInt(cents || 0, 10)) / 100).toFixed(2);
    }

    function selectedTier() {
        const checked = document.querySelector('input[name="start_tier"]:checked');
        return checked ? parseInt(checked.value, 10) : 3;
    }

    function hasDivision(tier) {
        return tier >= 1 && tier < <?= (int)$apexFrom ?>;
    }

    function classicRankImage(tier, division) {
        const names = {0:'unranked',1:'salt',2:'wood',3:'silver',4:'gold',5:'platinum',6:'diamond',7:'legend'};
        return (names[tier] || 'salt') + '.webp';
    }

    function rankDisplay(tier) {
        const name = rankNames[tier] || '';
        return hasDivision(tier) ? (name + ' ' + (divisionLabels[currentDivision] || 'IV')) : name;
    }

    function syncDivisionVisibility() {
        const tier = selectedTier();
        const visible = hasDivision(tier);
        const wrap = document.getElementById('dp-division-wrap');
        if (wrap) wrap.classList.toggle('is-hidden', !visible);
        if (divisionInput) divisionInput.value = visible ? String(currentDivision) : '0';
    }

    function syncDivisionButtons() {
        divisionButtons.forEach(function (btn) {
            btn.classList.toggle('active', parseInt(btn.dataset.division, 10) === currentDivision);
        });
    }

    function selectedHours() {
        return parseInt(hoursInput && hoursInput.value ? hoursInput.value : 3, 10) || 3;
    }

    function selectedServer() {
        const hidden = document.getElementById('dp-server-select');
        return hidden && hidden.value ? hidden.value : 'euw';
    }

    function currentPrice() {
        const tier = selectedTier();
        const hours = selectedHours();
        const server = selectedServer();
        const bucket = serverBuckets[server] || 'eu';
        return (((pricing[tier] || {})[hours] || {})[bucket]) || 0;
    }

    function dispatchLoadVariables() {
        if (loadTimer) clearTimeout(loadTimer);
        loadTimer = setTimeout(function () {
            if (typeof window.load_variables === 'function') {
                try { window.load_variables(); } catch (e) {}
            }
            // Re-sync hours display after load_variables (which may overwrite it)
            var h = selectedHours();
            var el = document.getElementById('dp-sticky-hours');
            if (el) el.textContent = String(h);
            var compl = document.getElementById('completion-time');
            if (compl) compl.textContent = '~ ' + h + ' Hours';
            document.querySelectorAll('[data-summary="hours"], .summary-hours, .order-hours, .sticky-hours, .dp-summary-hours').forEach(function(e) {
                e.textContent = String(h);
            });
            // Re-fire dp:update so lol.php sticky bar stays in sync, even if load_variables() overwrites generic counters.
            window.dispatchEvent(new CustomEvent('dp:update', { detail: {
                hours: String(h),
                rankName: rankDisplay(selectedTier()),
                totalPrice: currentPrice()
            }}));
        }, 120);
    }

    function updateSummary() {
        const tier = selectedTier();
        const hours = selectedHours();
        const price = currentPrice();
        const img = <?= $isClassic ? "'" . ASSET_URL . "/website/images/lol-classic/ranks/' + classicRankImage(tier, currentDivision)" : "'" . ASSET_URL . "/core/main/img/lol/ranks/mini/' + tier + '.png'" ?>;
        const label = rankNames[tier] || '';
        const displayLabel = rankDisplay(tier);

        document.querySelectorAll('.current-summary-rank-img').forEach(function (el) {
            el.src = img;
            el.alt = label;
            el.style.display = 'block';
        });
        document.querySelectorAll('.current-summary-rank-name').forEach(function (el) {
            el.textContent = displayLabel;
        });
        document.querySelectorAll('.current-summary-lp').forEach(function (el) {
            el.style.display = 'none';
        });
        var dpWinCount = document.getElementById('dp-sticky-hours');
        if (dpWinCount) dpWinCount.textContent = String(hours);
        // Broadcast hours to any other bottom-bar elements
        document.querySelectorAll('[data-summary="hours"], .summary-hours, .order-hours, .sticky-hours').forEach(function(el) {
            el.textContent = String(hours);
        });

        const total = document.getElementById('total-price');
        const sticky = document.getElementById('sticky-total-price');
        const oldPrice = document.getElementById('old-price');
        const newPrice = document.getElementById('new-price');
        const savedPrice = document.getElementById('saved-price');
        const stickyOld = document.getElementById('sticky-old-price');
        const completion = document.getElementById('completion-time');

        if (total) total.textContent = euro(price);
        if (sticky) sticky.textContent = euro(price);
        if (oldPrice) { oldPrice.textContent = ''; oldPrice.style.display = 'none'; }
        if (newPrice) newPrice.textContent = euro(price);
        if (savedPrice) savedPrice.textContent = euro(0);
        if (stickyOld) stickyOld.style.display = 'none';
        if (completion) completion.textContent = '~ ' + hours + ' Hours';

        // Keep hidden division input in sync so the order title uses IV / III / II / I instead of the tier id.
        syncDivisionVisibility();

        // Update sticky rank image for duo-pass
        var stickyImg = document.querySelector('#dp-sticky-rank-name') ? document.querySelector('.current-summary-rank-img') : null;
        if (stickyImg) { stickyImg.src = img; stickyImg.alt = label; }
        var stickyRankName = document.getElementById('dp-sticky-rank-name');
        if (stickyRankName) stickyRankName.textContent = displayLabel;
        var stickyHours = document.getElementById('dp-sticky-hours');
        if (stickyHours) stickyHours.textContent = String(hours);
        // Also update completion-time and any hours displays in bottom bar
        document.querySelectorAll('[data-summary="hours"], .summary-hours, .order-hours, .sticky-hours, .dp-summary-hours').forEach(function(el) {
            el.textContent = String(hours);
        });
        // Fire dp:update event that lol.php sticky-overview listens to
        window.dispatchEvent(new CustomEvent('dp:update', { detail: {
            hours: String(hours),
            rankName: displayLabel,
            totalPrice: price
        }}));
    }

    function selectHours(hours) {
        if (hoursInput) hoursInput.value = String(hours);
        durationButtons.forEach(function (btn) {
            btn.classList.toggle('active', parseInt(btn.dataset.hours, 10) === parseInt(hours, 10));
        });
        updateSummary();
        dispatchLoadVariables();
    }

    function selectDivision(division) {
        currentDivision = parseInt(division, 10) || 4;
        if (divisionInput) divisionInput.value = String(currentDivision);
        syncDivisionButtons();
        updateSummary();
        dispatchLoadVariables();
    }

    divisionButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            selectDivision(this.dataset.division);
        });
    });

    durationButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            selectHours(this.dataset.hours);
        });
    });

    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'start_tier') {
            updateSummary();
            dispatchLoadVariables();
        }
    });

    // Custom server dropdown
    (function () {
        const wrap = document.getElementById('dp-custom-server');
        const hidden = document.getElementById('dp-server-select');
        if (!wrap || !hidden) return;
        const trigger = wrap.querySelector('.dp-custom-select__trigger');
        const label = wrap.querySelector('.dp-custom-select__label');
        const options = wrap.querySelectorAll('.dp-custom-select__option');

        function closeDropdown() {
            wrap.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = wrap.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        options.forEach(function (opt) {
            opt.addEventListener('click', function () {
                options.forEach(function (o) { o.classList.remove('is-selected'); o.setAttribute('aria-selected', 'false'); });
                opt.classList.add('is-selected');
                opt.setAttribute('aria-selected', 'true');
                hidden.value = opt.dataset.value;
                label.textContent = opt.textContent;
                closeDropdown();
                updateSummary();
                dispatchLoadVariables();
            });
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) closeDropdown();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDropdown();
        });
    })();

    function init() {
        syncDivisionButtons();
        syncDivisionVisibility();
        selectHours(selectedHours());
        updateSummary();
        setTimeout(updateSummary, 180);
        setTimeout(updateSummary, 700);
        dispatchLoadVariables();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
