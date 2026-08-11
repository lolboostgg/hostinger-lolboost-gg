<div class="boost mastery-boost">
    <div class="card">
        <div class="card-header">
            <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/2.webp" alt="rank_icon"
                class="card-header-rank current-rank-img">
            <div class="text">
                <h3><?= t('Current Rank') ?></h3>
                <p><?= t('Select your current tier and division.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="ranks">
                <input type="hidden" name="mastery_level" value="1">
                <input type="hidden" name="is_champions_roles" value="1">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <label>
                        <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>"
                            class="custom-checkbox" <?= $i == 2 ? 'checked' : ''; ?>>
                        <div class="rank-btn">
                            <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/<?= $i ?>.webp">
                            <span class="tooltip">
                                <?= $ranks[$i]; ?>
                            </span>
                        </div>
                    </label>
                <?php endfor; ?>
            </div>

            <div class="options">
                <div class="option">
                    <h6><?= t('Champions') ?></h6>
                    <select class="select2" name="champions[]">
                        <?= util_load_champions_select() ?>
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

    <div class="card">
        <div class="card-header">
            <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/6.webp" alt="rank_icon"
                class="card-header-rank desired-rank-img">
            <div class="text">
                <h3><?= t('Desired Rank') ?></h3>
                <p><?= t('Select your desired tier and division.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="ranks">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <label>
                        <input type="radio" name="end_tier" id="end_<?= $i; ?>" value="<?= $i; ?>" class="custom-checkbox"
                            <?= $i == 6 ? 'checked' : ''; ?>>
                        <div class="rank-btn">
                            <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/<?= $i ?>.webp">
                            <span class="tooltip">
                                <?= $ranks[$i]; ?>
                            </span>
                        </div>
                    </label>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>