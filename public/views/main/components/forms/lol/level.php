<?= $this->start('styles') ?>
<link rel="canonical" href="https://lolboost.gg/lol/rank-boost" />
<?= $this->stop() ?>

<div class="col-md-8">
    <div class="card p-0 bg-secondary shadow-none mb-5">
        <div class="card-header p-4 w-100">
            <div class="d-flex align-items-center">
                <h4 class="mb-0 ms-3">
                    <?= t('Level Selection') ?>
                    <p class="mb-0 text-muted me-2">
                        <?= t('Select your current and desired level.') ?>
                    </p>
                </h4>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="mb-5">
                <div class="row">
                    <input type="hidden" name="level_boost" value="1">
                    <div class="col-sm-12 col-md-6 text-start mt-3">
                        <p><b><?= t('Current Level:') ?></b></p>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-dark"
                                onclick="decrementLevel(startLevel)">-</button>
                            <input type="number" id="start_level" value="1" name="start_tier"
                                class="form-control text-center rounded-0 no-arr" placeholder="<?= t('Current LP') ?>"
                                style="max-height:52px;" min="0">
                            <button type="button" class="btn btn-outline-dark"
                                onclick="incrementLevel(startLevel)">+</button>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 text-start mt-3">
                        <p><b><?= t('Desired Level:') ?></b></p>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-dark"
                                onclick="decrementLevel(endLevel)">-</button>
                            <input type="number" id="end_level" value="2" name="end_tier"
                                class="form-control text-center rounded-0 no-arr" placeholder="<?= t('Desired LP') ?>"
                                style="max-height:52px;" min="0">
                            <button type="button" class="btn btn-outline-dark"
                                onclick="incrementLevel(endLevel)">+</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="">
                <div class="row g-2">
                    <div class="col form-floating">
                        <select class="form-select" style="min-width:180px" name="server">
                            <option value="euw" selected="">EU-West</option>
                            <option value="na">North America</option>
                            <option value="me">Middle East</option>
                            <option value="eune">EU-Nordic & East</option>
                            <option value="br">Brazil</option>
                            <option value="oce">Oceania</option>
                            <option value="ru">Russia</option>
                            <option value="tr">Turkey</option>
                            <option value="lan">Latin America North</option>
                            <option value="las">Latin America South</option>
                            <option value="jp">Japan</option>
                            <option value="vn">Vietnam</option>
                            <option value="ph">Philippines</option>
                            <option value="sg">Singapore</option>
                            <option value="th">Thailand</option>
                            <option value="tw">Taiwan</option>
                        </select>
                        <label for="fl-select"><?= t('Server') ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="card p-0 bg-secondary shadow-none mb-5">
        <div class="card-header p-4 w-100">
            <h4 class="mb-0">
                <i class="fa-duotone fa-solid fa-cart-shopping"></i>
                <?= t('Order Summary') ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="rank-sec bg-light p-3 mb-3 mx-n4 mt-n4 border-bottom">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <p class="mb-0">
                        <span class="fw-bold">
                            <span class="current-summary-rank-name">Level 1</span>
                            <small class="text-muted ms-1 ms-md-2 fw-normal current-summary-lp"></small>
                        </span>
                    </p>
                    <i class="fa-duotone fa-solid fa-arrow-right"></i>
                    <p class="mb-0">
                        <span class="fw-bold">
                            <span class="desired-summary-rank-name">
                                Level 2
                            </span>
                            <small class="text-muted ms-1 ms-md-2 fw-normal desired-summary-lp"></small>
                        </span>
                    </p>
                </div>
            </div>
            <input type="hidden" name="is_duo" value="0">
            <!-- <div class="toggle-group bg-light border w-100 rounded mb-3">
                <input type="radio" id="solo" name="is_duo" value="0" checked>
                <label for="solo" class="toggle-label rounded">
                    <i class="fa-duotone fa-user me-2"></i>
                    Solo
                </label>

                <input type="radio" id="duo" name="is_duo" value="1">
                <label for="duo" class="toggle-label rounded">
                    <i class="fa-duotone fa-user-group me-2"></i>
                    Duo
                </label>
            </div> -->

            <div class="options-list mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="mb-0 text-dark">
                        <i class="fa-regular fa-circle-info text-muted me-2" data-bs-toggle="popover"
                            data-bs-placement="top" data-bs-trigger="hover"
                            data-bs-content="<?= t('Your boost order will be completed around 2x faster than regular ones.') ?>"></i>
                        <?= t('Priority Boost') ?>
                        <span class="badge rounded fw-bold bg-faded-primary ms-2 text-primary">+25%</span>
                    </p>
                    <label class="switch">
                        <input type="checkbox" value="1" id="is_priority" name="is_priority">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="mb-0 text-dark">
                        <i class="fa-regular fa-circle-info text-muted me-2" data-bs-toggle="popover"
                            data-bs-placement="top" data-bs-trigger="hover"
                            data-bs-content="<?= t('Your booster will win an additional game after you reach your desired rank.') ?>"></i>
                        <?= t('+1 Bonus Win') ?>
                        <span class="badge rounded fw-bold bg-faded-primary ms-2 text-primary"><?= t('AUTO') ?></span>
                    </p>
                    <label class="switch">
                        <input type="checkbox" value="1" id="is_bonus_win" name="is_bonus_win">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 solo-option">
                    <p class="mb-0 text-dark">
                        <i class="fa-regular fa-circle-info text-muted me-2" data-bs-toggle="popover"
                            data-bs-placement="top" data-bs-trigger="hover"
                            data-bs-content="<?= t('Your booster will play solo only on your account and will not duo with any other account.') ?>"></i>
                        <?= t('Solo Queue Only') ?>
                        <span class="badge rounded fw-bold bg-faded-primary ms-2 text-primary">+20%</span>
                    </p>
                    <label class="switch">
                        <input type="checkbox" value="1" id="is_solo_only" name="is_solo_only">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 solo-option">
                    <p class="mb-0 text-dark">
                        <i class="fa-regular fa-circle-info text-muted me-2" data-bs-toggle="popover"
                            data-bs-placement="top" data-bs-trigger="hover"
                            data-bs-content="<?= t('Your booster will privately stream you the games while he is playing.') ?>"></i>
                        <?= t('Stream Games') ?>
                        <span class="badge rounded fw-bold bg-faded-primary ms-2 text-primary">+15%</span>
                    </p>
                    <label class="switch">
                        <input type="checkbox" value="1" id="is_streaming" name="is_streaming">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="d-flex justify-content-between align-items-center solo-option">
                    <p class="mb-0 text-dark">
                        <i class="fa-regular fa-circle-info text-muted me-2" data-bs-toggle="popover"
                            data-bs-placement="top" data-bs-trigger="hover"
                            data-bs-content="<?= t('Pick your champions and roles so the booster knows your preferences.') ?>"></i>
                        <?= t('Champs & Roles') ?>
                        <span class="badge rounded fw-bold bg-faded-success ms-2 text-success"><?= t('FREE') ?></span>
                    </p>
                    <label class="switch">
                        <input type="checkbox" value="1" id="is_champions_roles" name="is_champions_roles">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="d-flex justify-content-between align-items-center duo-option">
                    <p class="mb-0 text-dark">
                        <i class="fa-regular fa-circle-info text-muted me-2" data-bs-toggle="popover"
                            data-bs-placement="bottom" data-bs-trigger="hover"
                            data-bs-content="<?= t('Your booster will be in a call with you and point out your mistakes and give you guidance.') ?>"></i>
                        <?= t('Premium Coaching') ?>
                        <span class="badge rounded fw-bold bg-faded-primary text-primary">+25%</span>
                    </p>
                    <label class="switch">
                        <input type="checkbox" value="1" id="is_coaching" name="is_coaching">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="card-footer p-2 p-md-4 w-100" id="hide-sticky" id="hide-sticky">
            <div class="bg-light p-3 mb-3 mx-n2 mt-n2 mx-md-n4 mt-md-n4 border-bottom text-center completion-time">
                <i class="fa duotone fa-timer text-gradient-primary"></i>
                <?= t('Completion Time:') ?>
                <span id="completion-time">~24 <?= t('Hours') ?></span>
            </div>

            <div class="d-flex align-items-center justify-content-between w-100">
                <div id="discount-text" class="btn-group dropdown w-100 text-center">
                    <a href="#" class="fw-500 me-1 text-decoration-none lnk-dark" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><i class="fa-duotone fa-tags"></i> <?= t('Do you
                        have a discount code?') ?></a>
                    <!-- Forms inside dropdown -->
                    <div class="dropdown-menu p-2 discount-bg" style="width: 20rem;">
                        <div class="p-1">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="discount_code" id="discount_code"
                                    placeholder="SEASON14">
                                <label for="discount_code">NEW40</label>
                            </div>
                            <div class="mt-1 text-start text-danger fs-14" id="discount_alert">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="discount-applied" class="discount-applied-message justify-content-between w-100"
                    style="display: none;">
                    <span id="discount-message">40% <?= t('discount applied successfully') ?>
                        🎉</span>
                    <span id="remove-discount">✖</span>
                </div>
            </div>

            <div class="totals-container">
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <p class="mb-0 text-dark">
                        <i class="fa-duotone fa-coins text-muted me-2 d-none d-md-inline"></i>
                        <?= t('Total Price') ?>
                    </p>
                    <div class="prices d-flex align-items-end gap-2">
                        <?php if (CLIENT_DATA != false): ?>
                            <div
                                class="badge rounded fw-bold bg-faded-warning text-warning border border-2 border-warning p-2">
                                <img src="<?= ASSET_URL ?>/core/main/img/coin.png" width="20px" height="20px">
                                <?= get_user_cashback_percentage(CLIENT_DATA['loyalty_rank_id']) ?>% <?= t('Cashback') ?>
                            </div>
                        <?php endif; ?>
                        <h4 class="text-muted mb-0" id="old-price" style="display: none;">
                            <del style="font-size: 14px;">€11.63</del>
                        </h4>
                        <h4 class="mb-0 fw-bold text-gradient-primary" id="total-price">$ 0.00</h4>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-lg" id="start_boost">
                    <i class="fa-duotone fa-cart-arrow-down me-2"></i>
                    <?= t('Buy Now') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="sticky-overview fixed-bottom w-100 bg-light pb-3 d-md-none">
    <div class="rank-sec bg-light p-3 mb-3 mx-n4 mt-n4 border">
        <div class="d-flex align-items-center justify-content-center gap-3">
            <p class="mb-0">
                <span class="fw-bold">
                    <span class="current-summary-rank-name">Level 1</span>
                    <small class="text-muted ms-1 ms-md-2 fw-normal current-summary-lp"></small>
                </span>
            </p>
            <i class="fa-duotone fa-solid fa-arrow-right"></i>
            <p class="mb-0">
                <span class="fw-bold">
                    <span class="desired-summary-rank-name">
                        Level 2
                    </span>
                    <small class="text-muted ms-1 ms-md-2 fw-normal desired-summary-lp"></small>
                </span>
            </p>
        </div>
    </div>

    <div class="totals-container">
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="mb-0 text-dark">
                <?= t('Total Price') ?>
            </p>
            <div class="prices d-flex align-items-end gap-2">
                <?php if (CLIENT_DATA != false): ?>
                    <div class="badge rounded fw-bold bg-faded-warning text-warning border border-2 border-warning p-2">
                        <img src="<?= ASSET_URL ?>/core/main/img/coin.png" width="20px" height="20px">
                        <?= get_user_cashback_percentage(CLIENT_DATA['loyalty_rank_id']) ?>% <?= t('Cashback') ?>
                    </div>
                <?php endif; ?>
                <h4 class="text-muted mb-0" id="sticky-old-price" style="display: none;">
                    <del style="font-size: 14px;">€11.63</del>
                </h4>
                <h4 class="mb-0 fw-bold text-gradient-primary" id="sticky-total-price">$ 0.00</h4>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 mt-2">
        <button type="button" class="btn btn-primary btn-lg" id="sticky_start_boost">
            <i class="fa-duotone fa-cart-arrow-down me-2"></i>
            <?= t('Buy Now') ?>
        </button>
    </div>
</div>

<?= $this->start('styles') ?>
<link rel="canonical" href="https://lolboost.gg/lol/rank-boost" />
<?= $this->stop() ?>