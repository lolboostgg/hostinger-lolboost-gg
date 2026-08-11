<div class="boost win-boost">
    <div class="card count-card">
        <div class="card-header">
            <div class="count win-count"><?= t('2') ?></div>
            <div class="text">
                <h3><?= t('Wins Amount') ?></h3>
                <p><?= t('Select your desired amount of wins.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <input type="hidden" name="clash_boost" value="1">

            <div class="range-slider" id="matches_slider1"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" hidden>

            <div class="options">
                <div class="option">
                    <h6><?= t('Clash Tier') ?></h6>
                    <select class="select2" name="start_tier" data-no-search="true">
                        <option value="1"><?= t('Tier 1') ?></option>
                        <option value="2"><?= t('Tier 2') ?></option>
                        <option value="3"><?= t('Tier 3') ?></option>
                        <option value="4"><?= t('Tier 4') ?></option>
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
    <div class="card count-card">
        <div class="card-header">
            <div class="count booster-count"><?= t('1') ?></div>
            <div class="text">
                <h3><?= t('Boosters') ?></h3>
                <p><?= t('Select your desired amount of boosters.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="boosters_slider"></div>
            <input class="form-control range-slider-value-min" name="boosters" type="number" hidden>
        </div>
    </div>
</div>