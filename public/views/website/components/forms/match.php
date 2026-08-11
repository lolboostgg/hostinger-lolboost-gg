<?php
$uiGame = isset($uiGame) && $uiGame ? $uiGame : 'lol';
?>
<div class="boost win-boost">
    <div class="card">
        <div class="card-header">
            <img src="<?= util_rank_img($uiGame, 'mini', 3); ?>" alt="rank_icon"
                class="card-header-rank current-rank-img">
            <div class="text">
                <h3><?= t('Current Rank') ?></h3>
                <p><?= t('Select your current tier and division.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="ranks">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <label>
                        <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>"
                            class="custom-checkbox" <?= $i == 3 ? 'checked' : ''; ?>>
                        <div class="rank-btn">
                            <img src="<?= util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= $ranks[$i]; ?>">
                            <span class="tooltip">
                                <?= $ranks[$i]; ?>
                            </span>
                        </div>
                    </label>
                <?php endfor; ?>
            </div>
            <?php if ($uiGame === 'lol'): ?>
            <div class="divisions">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <label>
                        <input type="radio" name="start_division" id="start_div_<?= $i; ?>" value="<?= $i; ?>"
                            class="custom-checkbox" <?= $i == 3 ? 'checked' : ''; ?>>
                        <div class="division-btn">
                            <?= util_format_lol_division($i); ?>
                        </div>
                    </label>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

            <div class="options">
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
                <div class="option">
                    <h6><?= t('Queue Type') ?></h6>
                    <select class="select2" name="queue_type" data-no-search="true">
                        <option value="solo_/_duo" selected=""><?= t('Solo/Duo') ?></option>
                        <option value="flexq"><?= t('Flex') ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card count-card">
        <div class="card-header">
            <div class="count win-count"><?= t('3') ?></div>
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
