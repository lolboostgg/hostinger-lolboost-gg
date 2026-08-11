<aside
    class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white  ">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <!-- Logo -->

            <a class="navbar-brand" href="<?= BSTR_URL ?>/dashboard" aria-label="LoLBoost.gg">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png" alt="Logo"
                    data-hs-theme-appearance="light">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo"
                    data-hs-theme-appearance="dark">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo"
                    data-hs-theme-appearance="default">

                <img class="navbar-brand-logo-mini" src="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon.svg" alt="Logo">
            </a>

            <!-- End Logo -->

             <!-- Navbar Vertical Toggle -->
            <button id="navbar-toggle" type="button"
                class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i id="collapse-icon" class="fa-solid fa-arrow-left-to-line"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i id="expand-icon" class="fa-solid fa-arrow-right-from-line"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Expand" style="display: none;"></i>
            </button>
            <!-- End Navbar Vertical Toggle -->

            <!-- Content -->
            <div class="navbar-vertical-content">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

                    <!-- <div class="nav-item">
                        <a class="nav-link dropdown-toggle active" href="#navbarVerticalMenuDashboards" role="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalMenuDashboards" aria-expanded="true" aria-controls="navbarVerticalMenuDashboards">
                            <i class="bi-house-door nav-icon"></i>
                            <span class="nav-link-title">Dashboards</span>
                        </a>

                        <div id="navbarVerticalMenuDashboards" class="nav-collapse collapse show" data-bs-parent="#navbarVerticalMenu">
                            <a class="nav-link active" href="<?= BSTR_URL ?>/dashboard">Default</a>
                            <a class="nav-link " href="../dashboard-alternative.html">Alternative</a>
                        </div>
                    </div> -->

                    <?php if (CLIENT_DATA): ?>
                        <div class="nav-item mb-3">
                            <a class="btn btn-primary w-100" href="/profile/overview">
                                <i class="fa-duotone fa-arrows-repeat nav-icon"></i>
                                <span class="nav-link-title">Switch to Client</span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Collapse -->
                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/dashboard" data-link="dashboard"
                            data-placement="left">
                            <!-- <i class="fa-duotone fa-chart-line nav-icon"></i> -->
                            <i class="fa-duotone fa-objects-column nav-icon"></i>
                            <span class="nav-link-title">Overview</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Boost Panel</span>

                    <div class="nav-item">
                        <a class="nav-link " href="<?= BSTR_URL ?>/orders-panel" data-link="orders-panel"
                            data-placement="left">
                            <i class="fa-duotone fa-rocket-launch nav-icon"></i>
                            <span class="nav-link-title">Order Dashboard</span>
                        </a>
                    </div>
                    <span class="dropdown-header mt-4">Orders</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/orders" data-link="orders" data-placement="left">
                            <i class="fa-duotone fa-medal nav-icon"></i>
                            <span class="nav-link-title">My Orders</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/performance" data-link="performance" data-placement="left">
                            <i class="fa-duotone fa-chart-line nav-icon"></i>
                            <span class="nav-link-title">My Performance</span>
                        </a>
                    </div>



                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/leaderboard" data-link="leaderboard" data-placement="left">
                            <i class="fa-duotone fa-trophy nav-icon"></i>
                            <span class="nav-link-title">Leaderboard</span>
                        </a>
                    </div>
                    <span class="dropdown-header mt-4">Others</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/rules-and-fines" data-link="rules-and-fines"
                            data-placement="left">
                            <i class="fa-duotone fa-book nav-icon"></i>
                            <span class="nav-link-title">Rules & Fines</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Payments</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/payments" data-link="payments" data-placement="left">
                            <i class="fa-duotone fa-wallet nav-icon"></i>
                            <span class="nav-link-title">My Payments</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/payout-requests" data-link="payout-requests" data-placement="left">
                            <i class="fa-duotone fa-money-check-dollar nav-icon"></i>
                            <span class="nav-link-title">Payout Requests</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/payout" data-link="payout" data-placement="left">
                            <i class="fa-duotone fa-building-columns nav-icon"></i>
                            <span class="nav-link-title">Payout Settings</span>
                        </a>
                    </div>


                    <span class="dropdown-header mt-4">Settings</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BSTR_URL ?>/profile" data-link="profile" data-placement="left">
                            <i class="fa-duotone fa-cog nav-icon"></i>
                            <span class="nav-link-title">My Profile</span>
                        </a>
                    </div>


                    <span class="dropdown-header mt-4">My Booster Funds</span>

                    <?php
                      // Some pages may have BOOSTER_DATA without a fresh balance value.
                      // To avoid showing 0.00 EUR incorrectly in the sidebar, we reload the booster row once here.
                      $lb_booster_for_balance = BOOSTER_DATA;
                      if (function_exists('get_booster_data') && !empty($lb_booster_for_balance['id'])) {
                          $tmp = get_booster_data($lb_booster_for_balance['id']);
                          if (is_array($tmp) && !empty($tmp)) {
                              $lb_booster_for_balance = $tmp;
                          }
                      }

                      // These helpers are defined in functions_insurance_frozen.php
                      // insurance reserve = min(balance, insurance_required) and available payout = max(balance - insurance_required, 0)
                      $lb_frozen_cents = function_exists('booster_insurance_frozen_cents')
                        ? booster_insurance_frozen_cents($lb_booster_for_balance)
                        : 0;
                      $lb_available_cents = function_exists('booster_available_for_payout_cents')
                        ? booster_available_for_payout_cents($lb_booster_for_balance)
                        : (int)($lb_booster_for_balance['balance'] ?? 0);
                    ?>

                    <div class="nav-item">
                        <a class="nav-link" data-link="available-balance" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right"
                           title="Available for payout: <b><?= util_format_price_display($lb_available_cents) ?> EUR</b><br>Insurance: <b><?= util_format_price_display($lb_frozen_cents) ?> EUR</b>"
                           href="#" onclick="return false;">
                          <i class="fa-duotone fa-sack-dollar nav-icon"></i>
                          <span class="nav-link-title">
                            Available&nbsp;&nbsp;<b><?= util_format_price_display($lb_available_cents) ?> EUR</b>
                          </span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" data-link="insurance-balance" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right"
                           title="Insurance: <b><?= util_format_price_display($lb_frozen_cents) ?> EUR</b><br>Held as security and paid out when you leave the company.<br>Available for payout: <b><?= util_format_price_display($lb_available_cents) ?> EUR</b>"
                           href="#" onclick="return false;">
                          <i class="fa-duotone fa-lock nav-icon"></i>
                          <span class="nav-link-title">
                            Insurance&nbsp;&nbsp;<b><?= util_format_price_display($lb_frozen_cents) ?> EUR</b>
                          </span>
                        </a>
                    </div>

                    
                </div>

            </div>
            <!-- End Content -->

        </div>
    </div>
</aside>

<script>
    var navbarToggler = document.getElementById('navbar-toggle');
    var collapseIcon = document.getElementById('collapse-icon');
    var expandIcon = document.getElementById('expand-icon');
    var navCard = document.getElementById('nav-card');

    navbarToggler.addEventListener('click', function () {
        if (collapseIcon.style.display === 'none') {
            collapseIcon.style.display = 'inline';
            expandIcon.style.display = 'none';
            navCard.style.display = 'block';

        } else {
            collapseIcon.style.display = 'none';
            expandIcon.style.display = 'inline';
            navCard.style.display = 'none';
        }
    });
</script>
