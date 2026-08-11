<?php
$uiGame = ($data['game'] ?? 'lol');
$isClassic = util_is_lol_classic($uiGame);
if ($uiGame === 'tft') { $uiGame = 'lol'; }
$rankStart = $isClassic ? 1 : 1;
$rankEnd = $isClassic ? 7 : 8;
$desiredRankValues = $isClassic ? range(1, 7) : range($rankStart, $rankEnd);
$placementRankStart = $isClassic ? 0 : 0;
$placementRankEnd = $isClassic ? 7 : 10;
$divisionValues = $isClassic ? [4, 3, 2, 1] : [1, 2, 3, 4];
$defaultStartTier = $isClassic ? 1 : 3;
$defaultEndTier = $isClassic ? 2 : 4;
$defaultStartDivision = 4;
$defaultEndDivision = $isClassic ? 4 : 1;
$apexFrom = $isClassic ? 7 : 8;
if (!function_exists('lol_classic_rank_asset_url')) {
    function lol_classic_rank_asset_url($tier, $division = 5) {
        $tier = (int)$tier;
        $division = (int)$division;
        $tierNames = [
            0 => 'unranked',
            1 => 'bronze',
            2 => 'silver',
            3 => 'gold',
            4 => 'platinum',
            5 => 'diamond',
            7 => 'challenger',
        ];
        if ($tier === 0 || $tier === 7) {
            return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'unranked') . '.png';
        }
        $divisionNames = [5 => 'v', 4 => 'iv', 3 => 'iii', 2 => 'ii', 1 => 'i'];
        return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'bronze') . '-' . ($divisionNames[$division] ?? 'v') . '.png';
    }
}
// TFT Rank Boost (boost_forms.id = 21) should not show LP gain / queue type (solo only)
$isTftRank = (($data['game'] ?? '') === 'tft' && in_array((int)($data['id'] ?? 0), [21, 24], true));
?>
<div class="rank-boost">
    <div class="rank-cards">
        <div class="card">
            <div class="card-header">
                <img src="<?= $isClassic ? lol_classic_rank_asset_url($defaultStartTier, $defaultStartDivision) : util_rank_img($uiGame, 'mini', 3); ?>" alt="rank_icon"
                    class="card-header-rank current-rank-img">
                <div class="text">
                    <h3><?= t('Current Rank') ?></h3>
                    <p><?= t('Select your current tier and division.') ?></p>
                </div>
            </div>
            <div class="card-body">
                <div class="ranks">
                    <?php for ($i = $rankStart; $i <= $rankEnd; $i++): ?>
                        <label>
                            <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>"
                                class="custom-checkbox" <?= $i == $defaultStartTier ? 'checked' : ''; ?>>
                            <div class="rank-btn">
                                <img src="<?= $isClassic ? lol_classic_rank_asset_url($i, $defaultStartDivision) : util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= $ranks[$i]; ?>">
                                <span class="tooltip">
                                    <?= $ranks[$i]; ?>
                                </span>
                            </div>
                        </label>
                    <?php endfor; ?>
                </div>
                <hr>
                <div class="divisions" id="start_divisions">
                    <?php foreach ($divisionValues as $i): ?>
                        <label>
                            <input type="radio" name="start_division" id="start_div_<?= $i; ?>" value="<?= $i; ?>"
                                class="custom-checkbox" <?= $i == $defaultStartDivision ? 'checked' : ''; ?>>
                            <div class="division-btn">
                                <?= $isClassic ? util_format_lol_classic_division($i) : util_format_lol_division($i); ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="lp-selector" id="start_lp_full" style="display:none">
                    <h6><?= t('Current LP:') ?></h6>
                    <div class="input-container">
                        <button type="button" onclick="decrementValue(startLPInput)">
                            <i class="fas fa-circle-minus"></i>
                        </button>
                        <input type="text" name="start_lp_full" id="start_lp_input" value="0" min="0" max="1500" step="100">
                        <button type="button" onclick="incrementValue(startLPInput)">
                            <i class="fas fa-circle-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <img src="<?= $isClassic ? lol_classic_rank_asset_url($defaultEndTier, $defaultEndDivision) : util_rank_img($uiGame, 'mini', 4); ?>" alt="rank_icon"
                    class="card-header-rank desired-rank-img">
                <div class="text">
                    <h3><?= t('Desired Rank') ?></h3>
                    <p><?= t('Select your desired tier and division.') ?></p>
                </div>
            </div>
            <div class="card-body">
                <div class="ranks">
                    <?php foreach ($desiredRankValues as $i): ?>
                        <?php $desiredImgRank = ($isClassic && $i < $apexFrom) ? ($i . '-' . $defaultStartDivision) : $i; ?>
                        <label>
                            <input type="radio" name="end_tier" id="end_<?= $i; ?>" value="<?= $i; ?>"
                                class="custom-checkbox" <?= $i == $defaultEndTier ? 'checked' : ''; ?>>
                            <div class="rank-btn">
                                <img src="<?= $isClassic ? lol_classic_rank_asset_url($i, ($i < $apexFrom ? $defaultStartDivision : 1)) : util_rank_img($uiGame, 'mini', $desiredImgRank); ?>" alt="<?= $ranks[$i]; ?>">
                                <span class="tooltip">
                                    <?= $ranks[$i]; ?>
                                </span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="divisions" id="end_divisions">
                    <?php foreach ($divisionValues as $i): ?>
                        <label>
                            <input type="radio" name="end_division" id="end_div_<?= $i; ?>" value="<?= $i; ?>"
                                class="custom-checkbox" <?= $i == $defaultEndDivision ? 'checked' : ''; ?>>
                            <div class="division-btn">
                                <?= $isClassic ? util_format_lol_classic_division($i) : util_format_lol_division($i); ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="lp-selector" id="end_lp_full" style="display:none">
                    <h6><?= t('Desired LP:') ?></h6>
                    <div class="input-container">
                        <button type="button" onclick="decrementValue(endLPInput)">
                            <i class="fas fa-circle-minus"></i>
                        </button>
                        <input type="text" name="end_lp_full" id="end_lp_input" value="0" min="0" max="1500" step="100">
                        <button type="button" onclick="incrementValue(endLPInput)">
                            <i class="fas fa-circle-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                // Division-Werte: 4=I (höchste), 3=II, 2=III, 1=IV (niedrigste)
                // Tier-Werte:     1=Iron (niedrigste) ... 8=Master (höchste)

                function getMaxDivisionValue() {
                    var inputs = document.querySelectorAll('input[name="start_division"]');
                    var max = 0;
                    inputs.forEach(function (input) {
                        var val = parseInt(input.value, 10);
                        if (val > max) max = val;
                    });
                    return max;
                }

                function getBestDivisionValue() {
                    return <?= $isClassic ? '1' : 'getMaxDivisionValue()' ?>;
                }

                function desiredDivisionInvalid(desiredDivision, currentDivision, sameTier) {
                    if (!sameTier) return false;
                    return <?= $isClassic ? '(desiredDivision >= currentDivision)' : '(desiredDivision <= currentDivision)' ?>;
                }

                function isHighEloTier(tier) {
                    return parseInt(tier, 10) >= <?= (int)$apexFrom ?>;
                }

                function setCheckedDivision(name, value) {
                    var input = document.querySelector('input[name="' + name + '"][value="' + value + '"]');
                    if (input) {
                        input.checked = true;
                    }
                }

                // Setzt disabled + pointer-events + opacity auf label und input
                function setDisabled(input, disabled) {
                    input.disabled = disabled;
                    var label = input.closest('label');
                    if (label) {
                        label.style.pointerEvents = disabled ? 'none' : '';
                        label.style.opacity = disabled ? '0.45' : '';
                    }
                }

                function toggleStartRankInputs() {
                    var checkedTier = document.querySelector('input[name="start_tier"]:checked');
                    var startDivisions = document.getElementById('start_divisions');
                    var startLpFull = document.getElementById('start_lp_full');
                    var startLpOption = document.getElementById('start-lp-option');

                    if (!checkedTier || !startDivisions || !startLpFull || !startLpOption) return;

                    if (isHighEloTier(checkedTier.value)) {
                        startDivisions.style.display = 'none';
                        setCheckedDivision('start_division', 1);
                        startLpFull.style.display = 'block';
                        startLpOption.style.display = 'none';
                    } else {
                        startDivisions.style.display = '';
                        startLpFull.style.display = 'none';
                        startLpOption.style.display = '';
                    }
                }

                function toggleDesiredRankInputs() {
                    var checkedTier = document.querySelector('input[name="end_tier"]:checked');
                    var endDivisions = document.getElementById('end_divisions');
                    var endLpFull = document.getElementById('end_lp_full');

                    if (!checkedTier || !endDivisions || !endLpFull) return;

                    if (isHighEloTier(checkedTier.value)) {
                        endDivisions.style.display = 'none';
                        setCheckedDivision('end_division', 1);
                        endLpFull.style.display = 'block';
                        var endLpInput = document.getElementById('end_lp_input');
                        if (endLpInput && parseInt(endLpInput.value || '0', 10) <= 0) {
                            endLpInput.value = '50';
                            endLpInput.dispatchEvent(new Event('input', { bubbles: true }));
                            endLpInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    } else {
                        endDivisions.style.display = '';
                        endLpFull.style.display = 'none';
                    }
                }

                function presetApexDesiredRank() {
                    var currentTier = document.querySelector('input[name="start_tier"]:checked');
                    var apexTier = <?= (int)$apexFrom ?>;
                    if (!currentTier || parseInt(currentTier.value, 10) !== apexTier) {
                        return;
                    }

                    var desiredApex = document.querySelector('input[name="end_tier"][value="' + apexTier + '"]');
                    var desiredLp = document.getElementById('end_lp_input');

                    if (desiredApex) {
                        desiredApex.disabled = false;
                        desiredApex.checked = true;
                    }
                    if (desiredLp) {
                        desiredLp.value = '50';
                    }

                    // The summary and pricing handlers listen for changes on
                    // these exact controls. Notify them immediately so the old
                    // desired-rank icon cannot remain visible until an LP click.
                    if (desiredApex) {
                        desiredApex.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (desiredLp) {
                        desiredLp.dispatchEvent(new Event('input', { bubbles: true }));
                        desiredLp.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                function syncDesiredRankOptions() {
                    var currentTierInput = document.querySelector('input[name="start_tier"]:checked');
                    var currentDivisionInput = document.querySelector('input[name="start_division"]:checked');

                    if (!currentTierInput || !currentDivisionInput) return;

                    var currentTier = parseInt(currentTierInput.value, 10);
                    var currentDivision = parseInt(currentDivisionInput.value, 10);
                    var isCurrentDivI = (currentDivision === getBestDivisionValue());
                    var isCurrentHighElo = isHighEloTier(currentTier);

                    var desiredTierInputs = Array.from(document.querySelectorAll('input[name="end_tier"]'));

                    desiredTierInputs.forEach(function (input) {
                        var t = parseInt(input.value, 10);
                        var invalid = (t < currentTier) || (!isCurrentHighElo && t === currentTier && isCurrentDivI);
                        setDisabled(input, invalid);
                    });

                    var checkedTier = document.querySelector('input[name="end_tier"]:checked');
                    var checkedTierVal = checkedTier ? parseInt(checkedTier.value, 10) : null;

                    var tierInvalid = !checkedTier
                        || checkedTier.disabled
                        || checkedTierVal < currentTier
                        || (!isCurrentHighElo && checkedTierVal === currentTier && isCurrentDivI);

                    if (tierInvalid) {
                        if (checkedTier) checkedTier.checked = false;
                        var firstValid = desiredTierInputs.find(function (i) { return !i.disabled; });
                        if (firstValid) {
                            firstValid.checked = true;
                            checkedTier = firstValid;
                            checkedTierVal = parseInt(firstValid.value, 10);
                        }
                    }

                    var desiredDivInputs = Array.from(document.querySelectorAll('input[name="end_division"]'));
                    var isDesiredHighElo = isHighEloTier(checkedTierVal);

                    desiredDivInputs.forEach(function (input) {
                        var d = parseInt(input.value, 10);
                        var invalid = !isDesiredHighElo && desiredDivisionInvalid(d, currentDivision, checkedTierVal === currentTier);
                        setDisabled(input, invalid);
                    });

                    var checkedDiv = document.querySelector('input[name="end_division"]:checked');
                    var checkedDivVal = checkedDiv ? parseInt(checkedDiv.value, 10) : null;

                    var divInvalid = !isDesiredHighElo && (
                        !checkedDiv
                        || checkedDiv.disabled
                        || desiredDivisionInvalid(checkedDivVal, currentDivision, checkedTierVal === currentTier)
                    );

                    if (divInvalid) {
                        if (checkedDiv) checkedDiv.checked = false;
                        var firstValidDiv = desiredDivInputs.find(function (i) { return !i.disabled; });
                        if (firstValidDiv) {
                            firstValidDiv.checked = true;
                            firstValidDiv.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    toggleDesiredRankInputs();
                }

                document.addEventListener('change', function (e) {
                    if (!e.target) return;

                    if (e.target.name === 'start_tier') {
                        presetApexDesiredRank();
                    }

                    if (e.target.name === 'start_tier' || e.target.name === 'start_division' || e.target.name === 'end_tier') {
                        syncDesiredRankOptions();
                    }

                    if (e.target.name === 'start_tier') {
                        toggleStartRankInputs();
                    }

                    if (e.target.name === 'end_tier') {
                        toggleDesiredRankInputs();
                    }
                });

                setTimeout(function () {
                    syncDesiredRankOptions();
                    toggleStartRankInputs();
                    toggleDesiredRankInputs();
                }, 150);
            });
        </script>

    <div class="options-bar">
        <div class="option" id="start-lp-option">
            <h6><?= t('Current LP') ?></h6>
            <select class="select2" name="start_lp" data-no-search="true">
                <option value="0-20"><?= t('0-20 LP') ?></option>
                <option value="21-40"><?= t('21-40 LP') ?></option>
                <option value="41-60"><?= t('41-60 LP') ?></option>
                <option value="61-80"><?= t('61-80 LP') ?></option>
                <option value="81-100"><?= t('81-100 LP') ?></option>
            </select>
        </div>
        <?php if (!$isTftRank && !$isClassic): ?>
        <div class="option">
            <h6><?= t('LP Gain') ?></h6>
            <select class="select2" name="lp_gain" data-no-search="true">
                <option value="30+"><?= t('30+ LP / Win') ?></option>
                <option value="25-29"><?= t('25-29 LP / Win') ?></option>
                <option value="20-24" selected><?= t('20-24 LP / Win') ?></option>
                <option value="10-19"><?= t('10-19 LP / Win') ?></option>
            </select>
        </div>
        <?php else: ?>
            <!-- Keep defaults for JS/pricing when hidden -->
            <input type="hidden" name="lp_gain" value="20-24">
        <?php endif; ?>
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
        <?php if (!$isTftRank && !$isClassic): ?>
        <div class="option">
            <h6><?= t('Queue Type') ?></h6>
            <select class="select2" name="queue_type" data-no-search="true">
                <option value="solo_/_duo" selected=""><?= t('Ranked Solo/Duo') ?></option>
                <option value="flexq"><?= t('Ranked Flex Queue') ?></option>
            </select>
        </div>
        <?php else: ?>
            <!-- LoL Classic and TFT stay Solo/Duo only for pricing/backend -->
            <input type="hidden" name="queue_type" value="solo_/_duo">
        <?php endif; ?>
    </div>
</div>

<?php if ($isClassic): ?>
<style>
.boost-form.lol-classic-page .rank-boost .card-header-rank,
.boost-form.lol-classic-page .rank-boost .current-rank-img,
.boost-form.lol-classic-page .rank-boost .desired-rank-img{width:92px!important;height:92px!important;object-fit:contain!important;max-width:none!important;}
.boost-form.lol-classic-page .summary-wrapper .rank-box .current-summary-rank-img,
.boost-form.lol-classic-page .summary-wrapper .rank-box .desired-summary-rank-img{width:74px!important;height:74px!important;object-fit:contain!important;max-width:none!important;}
</style>
<script>
(function(){
    var base = '<?= ASSET_URL ?>/website/images/lol-classic/ranks/';
    var tierNames = {0:'Unranked',1:'Salt',2:'Wood',3:'Silver',4:'Gold',5:'Platinum',6:'Diamond',7:'Legend'};
    function file(tier, division){
        tier = parseInt(tier || 1, 10);
        var names = {0:'unranked',1:'salt',2:'wood',3:'silver',4:'gold',5:'platinum',6:'diamond',7:'legend'};
        return (names[tier] || 'salt') + '.webp';
    }
    function label(tier, division){
        tier = parseInt(tier || 1, 10);
        division = parseInt(division || 4, 10);
        var divs = {4:'IV',3:'III',2:'II',1:'I'};
        if (tier === 0 || tier === 7) return tierNames[tier] || 'Unranked';
        return (tierNames[tier] || 'Salt') + ' ' + (divs[division] || 'IV');
    }
    function checked(name, fallback){
        var input = document.querySelector('input[name="' + name + '"]:checked');
        return input ? input.value : fallback;
    }
    function setAll(selector, src){
        document.querySelectorAll(selector).forEach(function(img){ img.src = src; });
    }
    function setText(selector, text){
        document.querySelectorAll(selector).forEach(function(el){ el.textContent = text; });
    }
    function syncClassicRankIcons(){
        var startTier = checked('start_tier', 1), startDivision = checked('start_division', 5);
        var endTier = checked('end_tier', 2), endDivision = checked('end_division', 5);
        var startSrc = base + file(startTier, startDivision);
        var endSrc = base + file(endTier, endDivision);
        setAll('.current-rank-img, .current-summary-rank-img', startSrc);
        setAll('.desired-rank-img, .desired-summary-rank-img', endSrc);
        setText('.current-summary-rank-name', label(startTier, startDivision));
        setText('.desired-summary-rank-name', label(endTier, endDivision));
    }
    ['change','click','input'].forEach(function(evt){
        document.addEventListener(evt, function(e){
            if (e.target && e.target.matches('input[name="start_tier"],input[name="start_division"],input[name="end_tier"],input[name="end_division"]')) {
                requestAnimationFrame(syncClassicRankIcons);
            }
        }, true);
    });
    document.addEventListener('DOMContentLoaded', syncClassicRankIcons);
    setTimeout(syncClassicRankIcons, 150);
    setTimeout(syncClassicRankIcons, 700);
})();
</script>
<?php endif; ?>
