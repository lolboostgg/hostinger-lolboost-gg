<?php
$data = is_array($data ?? null) ? $data : [];
$clientLoyalty = is_array($data['loyalty'] ?? null) ? $data['loyalty'] : [];
$clientLoyaltyName = $clientLoyalty['name'] ?? 'Silver';
$isDeleted = !empty($data['is_deleted']);
$clientDisplayEmail = $isDeleted ? ($data['deleted_email'] ?? $data['email'] ?? '') : ($data['email'] ?? '');
?>
<div class="row">
    <div class="col-lg-4">

        <!-- Sticky Block Start Point -->
        <div id="accountSidebarNav"></div>

        <!-- Card -->
        <div class="js-sticky-block card mb-3 mb-lg-5" data-hs-sticky-block-options='{
            "parentSelector": "#accountSidebarNav",
            "breakpoint": "lg",
            "startPoint": "#accountSidebarNav",
            "endPoint": "#stickyBlockEndPoint",
            "stickyOffsetTop": 20
            }'>
            <!-- Header -->
            <div class="card-header">
                <h4 class="card-header-title">Overview</h4>
            </div>
            <!-- End Header -->

            <!-- Body -->
            <div class="card-body">
                <ul class="list-unstyled list-py-2 text-dark mb-0">
                    <li class="pb-0"><span class="card-subtitle">Account</span></li>
                    <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= $data['id'] ?? '' ?></li>
                    <li><i class="fa-duotone fa-fingerprint dropdown-item-icon"></i> <?= $data['oauth_provider'] ?? '' ?></li>
                    <li class="pt-4 pb-0"><span class="card-subtitle">Contact</span></li>
                    <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= $clientDisplayEmail ?><?= $isDeleted ? ' <span class="text-muted">(released)</span>' : '' ?></li>
                    <li><i class="fa-brands fa-discord dropdown-item-icon"></i> <?= $data['discord'] ?? 'None' ?></li>
                    <li class="pt-4 pb-0"><span class="card-subtitle">Loyalty</span></li>
                    <li class="d-flex align-items-center">
                        <img src="<?= ASSET_URL ?>/core/main/img/coin.png" class="me-2"
                            style="width: 20px; height: 20px;">
                        <span><?= number_format((float)($data['points'] ?? 0), 2) ?> LB Coins</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="fa-duotone fa-gift me-2" style="width:20px;text-align:center;color:#a78bfa;"></i>
                        <span><?= number_format((float)($data['reward_points'] ?? 0), 2) ?> Reward Points</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/<?= lcfirst($clientLoyaltyName) ?>_icon.svg"
                            class="me-2" style="width: 20px; height: 25px;">
                        <?= $clientLoyaltyName ?>
                    </li>
                </ul>
            </div>
            <!-- End Body -->
        </div>
        <!-- End Card -->
    </div>

    <div class="col-lg-8">
        <div class="d-grid gap-3 gap-lg-5">

            <!-- Form -->
            <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
                <input type="text" name="action" value="admin_update_client" hidden>
                <input type="text" name="id" value="<?= $data['id'] ?? '' ?>" hidden>
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">Account Settings</h4>
                    </div>
                    <!-- End Header -->
                    <div class="card-body">
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="usernameLabel" class="col-sm-3 col-form-label form-label">Username</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="username" value="<?= $data['username'] ?? '' ?>"
                                    id="usernameLabel" placeholder="Username" aria-label="Username">
                            </div>
                        </div>
                        <!-- End Form Group -->
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="discordLabel" class="col-sm-3 col-form-label form-label">Discord</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="discord" value="<?= $data['discord'] ?? '' ?>"
                                    id="discordLabel" placeholder="Discord#0000" aria-label="Discord">
                            </div>
                        </div>
                        <!-- End Form Group -->
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="emailLabel" class="col-sm-3 col-form-label form-label">Email</label>

                            <div class="col-sm-9">
                                <input type="email" class="form-control" name="email" value="<?= $clientDisplayEmail ?>"
                                    id="emailLabel" placeholder="Email address" aria-label="Email address" <?= $isDeleted ? 'readonly' : '' ?>>
                                <?php if ($isDeleted): ?>
                                    <small class="text-muted d-block mt-1">This account is deleted. The email above is kept only as historical deleted_email.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- End Form Group -->
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="referralEnabledLabel" class="col-sm-3 col-form-label form-label">Client Referrals</label>
                            <div class="col-sm-9">
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input" name="referral_enabled" value="1" id="referralEnabledLabel" <?= !empty($data['referral_enabled']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="referralEnabledLabel">Allow this client to use referrals</label>
                                </div>
                                <small class="text-muted d-block mt-1">When enabled, the client gets a referral dashboard and can set a custom referral link.</small>
                            </div>
                        </div>
                        <!-- End Form Group -->
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="referralPercentLabel" class="col-sm-3 col-form-label form-label">Client Ref %</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="referral_percent" value="<?= isset($data['referral_percent']) && $data['referral_percent'] !== null && $data['referral_percent'] !== '' ? htmlspecialchars(rtrim(rtrim(number_format((float)$data['referral_percent'], 2, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') : '' ?>" id="referralPercentLabel" placeholder="Use global default" min="0" max="100" step="0.01" aria-label="Client referral percent">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Leave empty to use the global client referral percentage from Referral Settings. This value controls how many LB Coins the client gets from completed referred orders.</small>
                            </div>
                        </div>
                        <!-- End Form Group -->
                        <!-- Form Group -->
                        <div class="row mb-2">
                            <label for="emailLabel" class="col-sm-3 col-form-label form-label">Password</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" name="password" id="passwordLabel"
                                    placeholder="Leave empty to not change" aria-label="Password">
                            </div>
                        </div>
                        <!-- End Form Group -->
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Update Settings
                            </span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </span>
                            <span class="indicator-success">
                                <i class="fa-regular fa-circle-check fs-3"></i>
                            </span>
                        </button>
                    </div>
                </div>
                <!-- End Card -->
            </form>
            <!-- End Form -->

        </div>
        <div id="stickyBlockEndPoint"></div>
    </div>
</div>