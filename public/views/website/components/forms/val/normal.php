<?php
$uiGame = 'val';
?>

<div class="boost normal-boost">
    <div class="card">
        <div class="card-header">
            <img src="<?= util_rank_img($uiGame, 'mini', 0); ?>" alt="rank_icon" class="card-header-rank current-rank-img">
            <div class="text">
                <h3><?= t('Unrated Matches') ?></h3>
                <p><?= t('Select the amount of matches you want.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
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
                <input type="hidden" name="queue_type" value="unrated">
                <input type="hidden" name="lp_gain" value="20-24">
            </div>

            <div class="range-slider" id="matches_slider"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" id="matches_count" hidden>
        </div>
    </div>
</div>
