<?php
$uiGame = 'val';
?>

<div class="rank-boost">
    <div class="rank-cards">
        <div class="card">
            <div class="card-header">
                <img src="<?= util_rank_img($uiGame, 'mini', 3); ?>" alt="rank_icon" class="card-header-rank current-rank-img">
                <div class="text">
                    <h3><?= t('Current Rank') ?></h3>
                    <p><?= t('Select your current tier and division.') ?></p>
                </div>
            </div>
            <div class="card-body">
                <div class="ranks">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <label>
                            <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 3 ? 'checked' : ''; ?>>
                            <div class="rank-btn">
                                <img src="<?= util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= $ranks[$i]; ?>">
                                <span class="tooltip"><?= $ranks[$i]; ?></span>
                            </div>
                        </label>
                    <?php endfor; ?>
                </div>
                <hr>

                <div class="divisions" id="start_divisions">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <label>
                            <input type="radio" name="start_division" id="start_div_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 1 ? 'checked' : ''; ?>>
                            <div class="division-btn"><?= util_format_val_division($i); ?></div>
                        </label>
                    <?php endfor; ?>
                </div>

                <div class="lp-selector" id="start_rr_full" style="display:none">
                    <h6><?= t('Current RR:') ?></h6>
                    <div class="input-container">
                        <button type="button" onclick="decrementValue(startRRInput)"><i class="fas fa-circle-minus"></i></button>
                        <input type="text" name="start_rr_full" id="start_rr_input" value="0" min="0" max="1500" step="25">
                        <button type="button" onclick="incrementValue(startRRInput)"><i class="fas fa-circle-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <img src="<?= util_rank_img($uiGame, 'mini', 4); ?>" alt="rank_icon" class="card-header-rank desired-rank-img">
                <div class="text">
                    <h3><?= t('Desired Rank') ?></h3>
                    <p><?= t('Select your desired tier and division.') ?></p>
                </div>
            </div>
            <div class="card-body">
                <div class="ranks">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <label>
                            <input type="radio" name="end_tier" id="end_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 4 ? 'checked' : ''; ?>>
                            <div class="rank-btn">
                                <img src="<?= util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= $ranks[$i]; ?>">
                                <span class="tooltip"><?= $ranks[$i]; ?></span>
                            </div>
                        </label>
                    <?php endfor; ?>
                </div>
                <hr>

                <div class="divisions" id="end_divisions">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <label>
                            <input type="radio" name="end_division" id="end_div_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 3 ? 'checked' : ''; ?>>
                            <div class="division-btn"><?= util_format_val_division($i); ?></div>
                        </label>
                    <?php endfor; ?>
                </div>

                <div class="lp-selector" id="end_rr_full" style="display:none">
                    <h6><?= t('Desired RR:') ?></h6>
                    <div class="input-container">
                        <button type="button" onclick="decrementValue(endRRInput)"><i class="fas fa-circle-minus"></i></button>
                        <input type="text" name="end_rr_full" id="end_rr_input" value="0" min="0" max="1500" step="25">
                        <button type="button" onclick="incrementValue(endRRInput)"><i class="fas fa-circle-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="options-bar">
        <div class="option" id="start-rr-option">
            <h6><?= t('Current RR') ?></h6>
            <select class="select2" name="start_rr" data-no-search="true">
                <option value="0-20" selected><?= t('0-20 RR') ?></option>
                <option value="21-40"><?= t('21-40 RR') ?></option>
                <option value="41-60"><?= t('41-60 RR') ?></option>
                <option value="61-80"><?= t('61-80 RR') ?></option>
                <option value="81-100"><?= t('81-100 RR') ?></option>
            </select>
        </div>
        <div class="option">
            <h6><?= t('Server') ?></h6>
            <select class="select2" name="server" data-no-search="true">
                <option value="eu" selected><?= t('Europe') ?></option>
                <option value="na"><?= t('North America') ?></option>
                <option value="sea"><?= t('Southeast Asia') ?></option>
                <option value="me"><?= t('Middle East') ?></option>
                <option value="vn"><?= t('Vietnam') ?></option>
                <option value="ph"><?= t('Philippines') ?></option>
                <option value="sg"><?= t('Singapore') ?></option>
                <option value="th"><?= t('Thailand') ?></option>
                <option value="tw"><?= t('Taiwan') ?></option>
            </select>
        </div>
        <input type="hidden" name="lp_gain" value="20-24">
        <input type="hidden" name="queue_type" value="solo_/_duo">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Valorant Division-Werte: 3=III (höchste), 2=II, 1=I (niedrigste)
    // Tier-Werte: 1=Iron (niedrigste) ... 8=Immortal (höchste)

    function getMaxDivisionValue() {
        var inputs = document.querySelectorAll('input[name="start_division"]');
        var max = 0;
        inputs.forEach(function (input) {
            var val = parseInt(input.value, 10);
            if (val > max) max = val;
        });
        return max;
    }

    function setDisabled(input, disabled) {
        input.disabled = disabled;
        var label = input.closest('label');
        if (label) {
            label.style.pointerEvents = disabled ? 'none' : '';
            label.style.opacity = disabled ? '0.45' : '';
        }
    }

    function toggleRankViews() {
        var startTierInput = document.querySelector('input[name="start_tier"]:checked');
        var endTierInput = document.querySelector('input[name="end_tier"]:checked');

        var startDivisions = document.getElementById('start_divisions');
        var endDivisions = document.getElementById('end_divisions');
        var startRRFull = document.getElementById('start_rr_full');
        var endRRFull = document.getElementById('end_rr_full');
        var startRROption = document.getElementById('start-rr-option');

        var startTier = startTierInput ? parseInt(startTierInput.value, 10) : 0;
        var endTier = endTierInput ? parseInt(endTierInput.value, 10) : 0;

        var isStartHighElo = startTier >= 8;
        var isEndHighElo = endTier >= 8;

        if (startDivisions) startDivisions.style.display = isStartHighElo ? 'none' : '';
        if (startRRFull) startRRFull.style.display = isStartHighElo ? 'block' : 'none';
        if (startRROption) startRROption.style.display = isStartHighElo ? 'none' : '';

        if (endDivisions) endDivisions.style.display = isEndHighElo ? 'none' : '';
        if (endRRFull) endRRFull.style.display = isEndHighElo ? 'block' : 'none';
    }

    function syncDesiredRankOptions() {
        var currentTierInput = document.querySelector('input[name="start_tier"]:checked');
        var currentDivisionInput = document.querySelector('input[name="start_division"]:checked');

        if (!currentTierInput) return;

        var currentTier = parseInt(currentTierInput.value, 10);
        var isCurrentHighElo = currentTier >= 8;
        var currentDivision = currentDivisionInput ? parseInt(currentDivisionInput.value, 10) : 0;
        var maxDivisionValue = getMaxDivisionValue();
        var isCurrentDivMax = currentDivision === maxDivisionValue;

        var endTierInputs = Array.from(document.querySelectorAll('input[name="end_tier"]'));
        endTierInputs.forEach(function (input) {
            var tierVal = parseInt(input.value, 10);
            var shouldDisable = (tierVal < currentTier) || (!isCurrentHighElo && tierVal === currentTier && isCurrentDivMax);
            setDisabled(input, shouldDisable);
            if (input.checked && shouldDisable) input.checked = false;
        });

        var checkedEndTier = document.querySelector('input[name="end_tier"]:checked');
        var checkedTierVal = checkedEndTier ? parseInt(checkedEndTier.value, 10) : null;

        var tierInvalid = !checkedEndTier
            || checkedEndTier.disabled
            || checkedTierVal < currentTier
            || (!isCurrentHighElo && checkedTierVal === currentTier && isCurrentDivMax);

        if (tierInvalid) {
            if (checkedEndTier) checkedEndTier.checked = false;
            var firstValidTier = endTierInputs.find(function (i) { return !i.disabled; });
            if (firstValidTier) {
                firstValidTier.checked = true;
                checkedEndTier = firstValidTier;
                checkedTierVal = parseInt(firstValidTier.value, 10);
            }
        }

        var endDivInputs = Array.from(document.querySelectorAll('input[name="end_division"]'));
        endDivInputs.forEach(function (input) {
            var divVal = parseInt(input.value, 10);
            var shouldDisable = (!isCurrentHighElo && checkedTierVal === currentTier) && (divVal <= currentDivision);
            setDisabled(input, shouldDisable);
            if (input.checked && shouldDisable) input.checked = false;
        });

        var checkedEndDiv = document.querySelector('input[name="end_division"]:checked');
        var checkedDivVal = checkedEndDiv ? parseInt(checkedEndDiv.value, 10) : null;

        var divInvalid = !checkedEndDiv
            || checkedEndDiv.disabled
            || ((!isCurrentHighElo && checkedTierVal === currentTier) && checkedDivVal <= currentDivision);

        if (divInvalid) {
            if (checkedEndDiv) checkedEndDiv.checked = false;
            var firstValidDiv = endDivInputs.find(function (i) { return !i.disabled; });
            if (firstValidDiv) {
                firstValidDiv.checked = true;
                firstValidDiv.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        toggleRankViews();
    }

    document.addEventListener('change', function (e) {
        if (e.target && (
            e.target.name === 'start_tier' ||
            e.target.name === 'start_division' ||
            e.target.name === 'end_tier'
        )) {
            syncDesiredRankOptions();
        }
    });

    setTimeout(function () {
        syncDesiredRankOptions();
        toggleRankViews();
    }, 150);
});
</script>
