<div class="boost win-boost">
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

            <div class="options">
                <div class="option">
                    <h6><?= t('Coaching Type') ?></h6>
                    <select class="select2" name="coach_type" data-no-search="true">
                        <option value="Co-Pilot" selected data-bs-content="Co-Pilot: An expert Coach guides you while you play"><?= t('Co-Pilot') ?></option>
                        <option value="VOD-Review" data-bs-content="VOD-Review: An Expert Coach will provide in-depth analysis of your pre-recorded games"><?= t('VOD-Review') ?></option>
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
            </div>
        </div>
    </div>
</div>
