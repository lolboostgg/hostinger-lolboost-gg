<?php
$uiGame = 'val';
?>

<div class="boost win-boost">
    <div class="card">
        <div class="card-header">
            <img src="<?= util_rank_img($uiGame, 'mini', 3); ?>" alt="rank_icon" class="card-header-rank current-rank-img">
            <div class="text">
                <h3><?= t('Last Season Rank') ?></h3>
                <p><?= t('Select your last season tier and division.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="ranks">
                <?php for ($i = 0; $i <= 9; $i++): ?>
                    <label>
                        <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 3 ? 'checked' : ''; ?>>
                        <div class="rank-btn">
                            <img src="<?= util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= $ranks[$i]; ?>">
                            <span class="tooltip"><?= $ranks[$i]; ?></span>
                        </div>
                    </label>
                <?php endfor; ?>
            </div>

            <div class="divisions" id="start_divisions">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <label>
                        <input type="radio" name="start_division" id="start_div_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox" <?= $i == 1 ? 'checked' : ''; ?>>
                        <div class="division-btn"><?= util_format_val_division($i); ?></div>
                    </label>
                <?php endfor; ?>
            </div>

            <div class="options">
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
                <input type="hidden" name="queue_type" value="solo_/_duo">
                <input type="hidden" name="lp_gain" value="20-24">
            </div>
        </div>
    </div>

    <div class="card count-card">
        <div class="card-header">
            <div class="count win-count">5</div>
            <div class="text">
                <h3><?= t('Matches Amount') ?></h3>
                <p><?= t('Select your desired amount of matches.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="matches_slider"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" id="matches_count" hidden>
        </div>
    </div>
</div>
