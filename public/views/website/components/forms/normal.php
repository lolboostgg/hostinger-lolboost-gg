<div class="boost win-boost">
    <div class="card count-card">
        <div class="card-header">
            <div class="count win-count"><?= t('5') ?></div>
            <div class="text">
                <h3><?= t('Matches Amount') ?></h3>
                <p><?= t('Select your desired amount of matches.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="matches_slider"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" id="matches_count" hidden>

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
                    <h6><?= t('Game Mode') ?></h6>
                    <select class="select2" name="queue_type" data-no-search="true">
                        <option value="summoners_rift" selected=""><?= t('Summoner\'s Rift') ?></option>
                        <option value="aram"><?= t('ARAM') ?></option>
                        <option value="featured"><?= t('Featured') ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card count-card normal-boosters-card">
        <div class="card-header">
            <div class="count booster-count"><?= t('1') ?></div>
            <div class="text">
                <h3><?= t('Boosters') ?></h3>
                <p><?= t('Choose how many boosters you want for this order.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="boosters_slider"></div>
            <input class="form-control range-slider-value-min" name="boosters" type="number" value="1" min="1" max="4" hidden>
        </div>
    </div>
</div>
