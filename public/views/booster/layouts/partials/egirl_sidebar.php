<?php
// views/booster/layouts/partials/egirl_sidebar.php
// Wird geladen statt sidebar.php wenn IS_EGIRL === true
?>
<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <a class="navbar-brand" href="<?= BSTR_URL ?>/egirl-dashboard" aria-label="LoLBoost.gg">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png" alt="Logo" data-hs-theme-appearance="light">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo" data-hs-theme-appearance="dark">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo" data-hs-theme-appearance="default">
                <img class="navbar-brand-logo-mini" src="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon.svg" alt="Logo">
            </a>

            <button id="navbar-toggle" type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i id="collapse-icon" class="fa-solid fa-arrow-left-to-line" data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i id="expand-icon" class="fa-solid fa-arrow-right-from-line" data-bs-toggle="tooltip" data-bs-placement="right" title="Expand" style="display:none;"></i>
            </button>

            <div class="navbar-vertical-content">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

                    <?php if (CLIENT_DATA): ?>
                        <div class="nav-item mb-3">
                            <a class="btn btn-primary w-100" href="/profile/overview">
                                <i class="fa-duotone fa-arrows-repeat nav-icon"></i>
                                <span class="nav-link-title">Switch to Client</span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/egirl-dashboard" data-link="egirl-dashboard">
                            <i class="fa-duotone fa-objects-column nav-icon"></i>
                            <span class="nav-link-title">Overview</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Bookings</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/egirl-panel" data-link="egirl-panel">
                            <i class="fa-duotone fa-rocket-launch nav-icon"></i>
                            <span class="nav-link-title">Booking Panel</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/egirl-orders" data-link="egirl-orders">
                            <i class="fa-duotone fa-calendar-check nav-icon"></i>
                            <span class="nav-link-title">My Bookings</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">My Profile</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/egirl-services" data-link="egirl-services">
                            <i class="fa-duotone fa-stars nav-icon"></i>
                            <span class="nav-link-title">My Services</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/egirl-profile" data-link="egirl-profile">
                            <i class="fa-duotone fa-cog nav-icon"></i>
                            <span class="nav-link-title">My Profile</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Payments</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/egirl-payments" data-link="egirl-payments">
                            <i class="fa-duotone fa-wallet nav-icon"></i>
                            <span class="nav-link-title">My Payments</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/egirl-payout" data-link="egirl-payout">
                            <i class="fa-duotone fa-money-check-dollar nav-icon"></i>
                            <span class="nav-link-title">Payout Requests</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">My Balance</span>
                    <?php
                        global $db;
                        $lb_bal = (int)($db ? $db->cell("SELECT balance FROM egirl_balance WHERE egirl_id = ?", BOOSTER_ID) : 0);
                    ?>
                    <div class="nav-item">
                        <a class="nav-link" href="#" onclick="return false;" data-bs-toggle="tooltip" data-bs-placement="right" title="Available for payout">
                            <i class="fa-duotone fa-sack-dollar nav-icon"></i>
                            <span class="nav-link-title">Available&nbsp;&nbsp;<b><?= function_exists('util_format_price_display') ? util_format_price_display($lb_bal) : number_format($lb_bal/100,2) ?> EUR</b></span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</aside>
<script>
    var navbarToggler = document.getElementById('navbar-toggle');
    var collapseIcon  = document.getElementById('collapse-icon');
    var expandIcon    = document.getElementById('expand-icon');
    navbarToggler.addEventListener('click', function () {
        if (collapseIcon.style.display === 'none') {
            collapseIcon.style.display = 'inline'; expandIcon.style.display = 'none';
        } else {
            collapseIcon.style.display = 'none'; expandIcon.style.display = 'inline';
        }
    });
</script>
