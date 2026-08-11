<?php
$superAdmins = [
    'r.machmueller@gmx.de'
];

$admins = array_merge($superAdmins, [
    'duck_sauce@live.de',
    'samsayahix@gmail.com'
]);

$helpers = [
    'ziad202175@yahoo.com',
    'justsromail@freenet.de',
    'abdoazzam281@gmail.com',
    'nototakuulol@gmail.com',
    'murdermelody9@gmail.com',
    'mostafa.frag.thefox@gmail.com',
    'hesham0elkomy@gmail.com'
];

$sellers = [
    'samsayahix@gmail.com'
];

$seoUsers = [
    'primeseohub92@gmail.com'
];

$userEmail = strtolower(trim(ADMIN_DATA['email']));

$SuperAdmin = in_array($userEmail, $superAdmins, true);
$Admin      = in_array($userEmail, $admins, true);
$Helper     = in_array($userEmail, $helpers, true) || $Admin;
$Seller     = in_array($userEmail, $sellers, true) || $SuperAdmin || $Admin;
$SEO        = in_array($userEmail, $seoUsers, true);

$currentRoles = [];
if ($SuperAdmin) $currentRoles[] = 'SuperAdmin';
if ($Admin)      $currentRoles[] = 'Admin';
if ($Helper)     $currentRoles[] = 'Helper';
if ($Seller)     $currentRoles[] = 'Seller';
if ($SEO)        $currentRoles[] = 'SEO';
?>

<style>
    /* --- Aufgeräumte Sidebar: Parent-Zeile mit Unterpunkten --- */
    .navbar-vertical .nav-item-parent {
        display: flex;
        align-items: center;
    }
    .navbar-vertical .nav-item-parent > .nav-link {
        flex: 1 1 auto;
        min-width: 0;
    }
    .navbar-vertical .nav-parent-caret {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        margin-right: .25rem;
        border-radius: .4rem;
        color: inherit;
        opacity: .55;
        transition: transform .2s ease, opacity .2s ease, background-color .2s ease;
    }
    .navbar-vertical .nav-parent-caret:hover {
        opacity: 1;
        background-color: rgba(0, 0, 0, .05);
    }
    .navbar-vertical .nav-parent-caret[aria-expanded="true"] {
        transform: rotate(180deg);
        opacity: 1;
    }
    .navbar-vertical .nav-parent-caret .fa-chevron-down {
        font-size: .7rem;
    }
    /* Untermenü einrücken */
    .navbar-vertical .nav-sub-collapse .nav-link {
        padding-left: 3rem;
    }
    .navbar-vertical .nav-sub-collapse .nav-link .nav-icon {
        opacity: .8;
    }
</style>

<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">

            <!-- Logo -->
            <a class="navbar-brand" href="<?= ADMN_URL ?>/dashboard" aria-label="LoLBoost.gg">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png" alt="Logo" data-hs-theme-appearance="light">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo" data-hs-theme-appearance="dark">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo" data-hs-theme-appearance="default">
                <img class="navbar-brand-logo-mini" src="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon.svg" alt="Logo">
            </a>

            <!-- Navbar Toggle -->
            <button id="navbar-toggle" type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i id="collapse-icon" class="fa-solid fa-arrow-left-to-line"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i id="expand-icon" class="fa-solid fa-arrow-right-from-line"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Expand" style="display:none;"></i>
            </button>

            <!-- Nav Content -->
            <div class="navbar-vertical-content">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

                    <!-- ========================= OVERVIEW ========================= -->
                    <?php if ($SuperAdmin || $Helper || $Admin): ?>
                    <span class="dropdown-header mt-2">Overview</span>
                    <?php endif; ?>

                    <?php if ($SuperAdmin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/dashboard" data-link="dashboard" data-placement="left">
                            <i class="fa-duotone fa-objects-column nav-icon"></i>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($Helper): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/support-shifts" data-link="support-shifts" data-placement="left">
                            <i class="fa-duotone fa-calendar-clock nav-icon"></i>
                            <span class="nav-link-title">Support Shifts</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($Admin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/security-mode" data-link="security-mode" data-placement="left">
                            <i class="fa-duotone fa-shield-check nav-icon"></i>
                            <span class="nav-link-title">Security Mode</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/security-log" data-link="security-log" data-placement="left">
                            <i class="fa-duotone fa-shield-exclamation nav-icon"></i>
                            <span class="nav-link-title">Security Log</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/admin-logs" data-link="admin-logs" data-placement="left">
                            <i class="fa-duotone fa-clipboard-list nav-icon"></i>
                            <span class="nav-link-title">Admin Logs</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- ========================= ORDERS ========================= -->
                    <?php if ($Helper): ?>
                    <span class="dropdown-header mt-4">Orders</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/orders" data-link="orders" data-placement="left">
                            <i class="fa-duotone fa-medal nav-icon"></i>
                            <span class="nav-link-title">Orders List</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($Admin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/order-accounts" data-link="order-accounts" data-placement="left">
                            <i class="fa-duotone fa-key nav-icon"></i>
                            <span class="nav-link-title">Order Accounts</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/order-screenshots" data-link="order-screenshots" data-placement="left">
                            <i class="fa-duotone fa-images nav-icon"></i>
                            <span class="nav-link-title">Order Screenshots</span>
                        </a>
                    </div>

                    <?php endif; ?>

                    <?php if ($Helper): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/drop-requests" data-link="drop-requests" data-placement="left">
                            <i class="fa-duotone fa-minus-hexagon nav-icon"></i>
                            <span class="nav-link-title">Drop Requests</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/reviews" data-link="reviews" data-placement="left">
                            <i class="fa-duotone fa-star-half-stroke nav-icon"></i>
                            <span class="nav-link-title">Order Reviews</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/egirl/orders" data-link="egirl/orders" data-placement="left">
                            <i class="fa-duotone fa-calendar-check nav-icon"></i>
                            <span class="nav-link-title">E-Girl Bookings</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php // E-Girl Reviews removed: Order Reviews now lists boosting,
                          // seller and GG-Girl reviews together with a type filter. ?>

                    <!-- ========================= CUSTOMERS ========================= -->
                    <?php if ($Admin || $Helper): ?>
                    <span class="dropdown-header mt-4">Customers</span>
                    <?php endif; ?>

                    <?php if ($Helper): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/clients" data-link="clients" data-placement="left">
                            <i class="fa-duotone fa-user-headset nav-icon"></i>
                            <span class="nav-link-title">Clients List</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($Admin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/referrals" data-link="referrals" data-placement="left">
                            <i class="fa-duotone fa-share-nodes nav-icon"></i>
                            <span class="nav-link-title">Referrals</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/coming-soon-notifications" data-link="coming-soon-notifications" data-placement="left">
                            <i class="fa-duotone fa-bell-on nav-icon"></i>
                            <span class="nav-link-title">Coming Soon Notif</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/lootboxes" data-link="lootboxes" data-placement="left">
                            <i class="fa-duotone fa-gift nav-icon"></i>
                            <span class="nav-link-title">Lootboxes</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($Helper): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/coins-history" data-link="coins-history" data-placement="left">
                            <i class="fa-duotone fa-coin nav-icon"></i>
                            <span class="nav-link-title">Coins History</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- ========================= APPLICATIONS ========================= -->
                    <?php if (!$SEO || $Helper || $SuperAdmin || $Admin || $Seller): ?>
                    <span class="dropdown-header mt-4">Applications</span>
                    <?php endif; ?>

                    <?php if (!$SEO): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/job-applications" data-link="job-applications" data-placement="left">
                            <i class="fa-duotone fa-file-user nav-icon"></i>
                            <span class="nav-link-title">Job Applications</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($Helper || $SuperAdmin || $Admin || $Seller): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/applications" data-link="applications" data-placement="left">
                            <i class="fa-duotone fa-user-clock nav-icon"></i>
                            <span class="nav-link-title">Onboarding Applications</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- ========================= STAFF ========================= -->
                    <?php if ($Helper): ?>
                    <span class="dropdown-header mt-4">Staff</span>

                    <!-- Boosters (+ Add Booster als Unterpunkt) -->
                    <?php if ($Admin): ?>
                    <div class="nav-item nav-item-parent">
                        <a class="nav-link" href="<?= ADMN_URL ?>/boosters" data-link="boosters" data-placement="left">
                            <i class="fa-duotone fa-user-bounty-hunter nav-icon"></i>
                            <span class="nav-link-title">Boosters</span>
                        </a>
                        <a class="nav-parent-caret" role="button" data-bs-toggle="collapse" href="#navBoosters" aria-expanded="false" aria-controls="navBoosters">
                            <i class="fa-solid fa-chevron-down"></i>
                        </a>
                    </div>
                    <div id="navBoosters" class="collapse nav-sub-collapse">
                        <div class="nav-item">
                            <a class="nav-link" href="<?= ADMN_URL ?>/booster/add" data-link="booster/add" data-placement="left">
                                <i class="fa-duotone fa-user-plus nav-icon"></i>
                                <span class="nav-link-title">Add Booster</span>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/boosters" data-link="boosters" data-placement="left">
                            <i class="fa-duotone fa-user-bounty-hunter nav-icon"></i>
                            <span class="nav-link-title">Boosters</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/booster-games" data-link="booster-games" data-placement="left">
                            <i class="fa-duotone fa-swords nav-icon"></i>
                            <span class="nav-link-title">Booster Games</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/booster-leaderboard" data-link="booster-leaderboard" data-placement="left">
                            <i class="fa-duotone fa-trophy nav-icon"></i>
                            <span class="nav-link-title">Leaderboard</span>
                        </a>
                    </div>

                    <!-- E-Girls (+ Add E-Girl als Unterpunkt) -->
                    <?php if ($Admin): ?>
                    <div class="nav-item nav-item-parent">
                        <a class="nav-link" href="<?= ADMN_URL ?>/egirls" data-link="egirls" data-placement="left">
                            <i class="fa-duotone fa-stars nav-icon"></i>
                            <span class="nav-link-title">E-Girls</span>
                        </a>
                        <a class="nav-parent-caret" role="button" data-bs-toggle="collapse" href="#navEgirls" aria-expanded="false" aria-controls="navEgirls">
                            <i class="fa-solid fa-chevron-down"></i>
                        </a>
                    </div>
                    <div id="navEgirls" class="collapse nav-sub-collapse">
                        <div class="nav-item">
                            <a class="nav-link" href="<?= ADMN_URL ?>/egirl/add" data-link="egirl/add" data-placement="left">
                                <i class="fa-duotone fa-user-plus nav-icon"></i>
                                <span class="nav-link-title">Add E-Girl</span>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/egirls" data-link="egirls" data-placement="left">
                            <i class="fa-duotone fa-stars nav-icon"></i>
                            <span class="nav-link-title">E-Girls</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php endif; /* Staff */ ?>

                    <!-- ========================= MARKETPLACE ========================= -->
                    <?php if ($Helper || $Admin || $SuperAdmin || $Seller): ?>
                    <span class="dropdown-header mt-4">Marketplace</span>
                    <?php endif; ?>

                    <?php if ($Helper || $SuperAdmin || $Seller): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/sellers" data-link="sellers" data-placement="left">
                            <i class="fa-duotone fa-store nav-icon"></i>
                            <span class="nav-link-title">Sellers List</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($SuperAdmin || $Seller || $Helper): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/accounts" data-link="accounts" data-placement="left">
                            <i class="fa-duotone fa-helmet-battle nav-icon"></i>
                            <span class="nav-link-title">Account Orders</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($SuperAdmin || $Seller): ?>
                    <div class="nav-item nav-item-parent">
                        <a class="nav-link" href="<?= ADMN_URL ?>/account-packages" data-link="account-packages" data-placement="left">
                            <i class="fa-duotone fa-box-taped nav-icon"></i>
                            <span class="nav-link-title">Packages</span>
                        </a>
                        <a class="nav-parent-caret" role="button" data-bs-toggle="collapse" href="#navPackages" aria-expanded="false" aria-controls="navPackages">
                            <i class="fa-solid fa-chevron-down"></i>
                        </a>
                    </div>
                    <div id="navPackages" class="collapse nav-sub-collapse">
                        <div class="nav-item">
                            <a class="nav-link" href="<?= ADMN_URL ?>/account-package/add" data-link="account-package/add" data-placement="left">
                                <i class="fa-duotone fa-square-plus nav-icon"></i>
                                <span class="nav-link-title">Add Package</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($Admin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/item-orders" data-link="item-orders" data-placement="left">
                            <i class="fa-duotone fa-bags-shopping nav-icon"></i>
                            <span class="nav-link-title">Item Orders</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/top-up-orders" data-link="top-up-orders" data-placement="left">
                            <i class="fa-duotone fa-coins nav-icon"></i>
                            <span class="nav-link-title">Top Up Orders</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/digital-good-orders" data-link="digital-good-orders" data-placement="left">
                            <i class="fa-duotone fa-gem nav-icon"></i>
                            <span class="nav-link-title">Digital Good Orders</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- ========================= FINANCE ========================= -->
                    <?php if ($Helper): ?>
                    <span class="dropdown-header mt-4">Finance</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/transactions" data-link="transactions" data-placement="left">
                            <i class="fa-duotone fa-receipt nav-icon"></i>
                            <span class="nav-link-title">Transactions</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/invoices" data-link="invoices" data-placement="left">
                            <i class="fa-duotone fa-file-invoice-dollar nav-icon"></i>
                            <span class="nav-link-title">Invoices</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/booster/payments" data-link="booster/payments" data-placement="left">
                            <i class="fa-duotone fa-money-check-dollar nav-icon"></i>
                            <span class="nav-link-title">Booster Payments</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($SuperAdmin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/payout-requests" data-link="payout-requests" data-placement="left">
                            <i class="fa-duotone fa-building-columns nav-icon"></i>
                            <span class="nav-link-title">Payout Requests</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- ========================= MARKETING ========================= -->
                    <?php if ($Admin || $SuperAdmin): ?>
                    <span class="dropdown-header mt-4">Marketing</span>
                    <?php endif; ?>

                    <?php if ($Admin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/discounts" data-link="discounts" data-placement="left">
                            <i class="fa-duotone fa-tags nav-icon"></i>
                            <span class="nav-link-title">Discounts</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($SuperAdmin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/world-cup-predictions" data-link="world-cup-predictions" data-placement="left">
                            <i class="fa-duotone fa-futbol nav-icon"></i>
                            <span class="nav-link-title">World Cup</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/giveaways" data-link="giveaways" data-placement="left">
                            <i class="fa-duotone fa-gift nav-icon"></i>
                            <span class="nav-link-title">Giveaways</span>
                        </a>
                    </div>

                    <!-- Prizes (+ Add Prize & Redeem Requests als Unterpunkte) -->
                    <div class="nav-item nav-item-parent">
                        <a class="nav-link" href="<?= ADMN_URL ?>/prizes" data-link="prizes" data-placement="left">
                            <i class="fa-duotone fa-gift nav-icon"></i>
                            <span class="nav-link-title">Prizes</span>
                        </a>
                        <a class="nav-parent-caret" role="button" data-bs-toggle="collapse" href="#navPrizes" aria-expanded="false" aria-controls="navPrizes">
                            <i class="fa-solid fa-chevron-down"></i>
                        </a>
                    </div>
                    <div id="navPrizes" class="collapse nav-sub-collapse">
                        <div class="nav-item">
                            <a class="nav-link" href="<?= ADMN_URL ?>/prizes/add" data-link="prizes/add" data-placement="left">
                                <i class="fa-duotone fa-square-plus nav-icon"></i>
                                <span class="nav-link-title">Add Prize</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= ADMN_URL ?>/prizes/redeemed" data-link="prizes/redeemed" data-placement="left">
                                <i class="fa-duotone fa-list nav-icon"></i>
                                <span class="nav-link-title">Redeem Requests</span>
                            </a>
                        </div>
                    </div>

                    <!-- Loyalty Ranks (+ Add Loyalty Rank als Unterpunkt) -->
                    <div class="nav-item nav-item-parent">
                        <a class="nav-link" href="<?= ADMN_URL ?>/loyalty" data-link="loyalty" data-placement="left">
                            <i class="fa-duotone fa-ranking-star nav-icon"></i>
                            <span class="nav-link-title">Loyalty Ranks</span>
                        </a>
                        <a class="nav-parent-caret" role="button" data-bs-toggle="collapse" href="#navLoyalty" aria-expanded="false" aria-controls="navLoyalty">
                            <i class="fa-solid fa-chevron-down"></i>
                        </a>
                    </div>
                    <div id="navLoyalty" class="collapse nav-sub-collapse">
                        <div class="nav-item">
                            <a class="nav-link" href="<?= ADMN_URL ?>/loyalty/add" data-link="loyalty/add" data-placement="left">
                                <i class="fa-duotone fa-square-plus nav-icon"></i>
                                <span class="nav-link-title">Add Loyalty Rank</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ========================= SEO ========================= -->
                    <?php if ($SuperAdmin || $SEO): ?>
                    <span class="dropdown-header mt-4">SEO</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/articles" data-link="articles" data-placement="left">
                            <i class="fa-duotone fa-newspaper nav-icon"></i>
                            <span class="nav-link-title">Articles</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- ========================= SYSTEM ========================= -->
                    <?php if ($Admin || $SuperAdmin): ?>
                    <span class="dropdown-header mt-4">System</span>
                    <?php endif; ?>

                    <?php if ($Admin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/games" data-link="games" data-placement="left">
                            <i class="fa-duotone fa-gamepad-modern nav-icon"></i>
                            <span class="nav-link-title">Games Manager</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/manage-languages" data-link="manage-languages" data-placement="left">
                            <i class="fa-duotone fa-language nav-icon"></i>
                            <span class="nav-link-title">Languages</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/banner-settings" data-link="banner-settings" data-placement="left">
                            <i class="fa-duotone fa-rectangle-ad nav-icon"></i>
                            <span class="nav-link-title">Banner Settings</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($SuperAdmin): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= ADMN_URL ?>/boost/forms" data-link="boost/forms" data-placement="left">
                            <i class="fa-duotone fa-calculator-simple nav-icon"></i>
                            <span class="nav-link-title">Forms Pricing</span>
                        </a>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <!-- End Content -->

        </div>
    </div>
</aside>

<script>
    // --- Collapse-Icon Toggle (bestehendes Verhalten) ---
    var navbarToggler = document.getElementById('navbar-toggle');
    var collapseIcon  = document.getElementById('collapse-icon');
    var expandIcon    = document.getElementById('expand-icon');
    var navCard       = document.getElementById('nav-card');

    if (navbarToggler) {
        navbarToggler.addEventListener('click', function () {
            if (collapseIcon.style.display === 'none') {
                collapseIcon.style.display = 'inline';
                expandIcon.style.display   = 'none';
                if (navCard) navCard.style.display = 'block';
            } else {
                collapseIcon.style.display = 'none';
                expandIcon.style.display   = 'inline';
                if (navCard) navCard.style.display = 'none';
            }
        });
    }

    // --- Aktive Gruppe automatisch aufklappen ---
    (function () {
        function openActiveGroup() {
            var path = (window.location.pathname || '').replace(/\/+$/, '');
            document.querySelectorAll('.nav-sub-collapse').forEach(function (box) {
                var match = false;
                box.querySelectorAll('[data-link]').forEach(function (link) {
                    var dl = link.getAttribute('data-link');
                    if (!dl) return;
                    if (link.classList.contains('active') ||
                        path === '/' + dl ||
                        path.endsWith('/' + dl) ||
                        path.indexOf('/' + dl + '/') !== -1) {
                        match = true;
                    }
                });
                if (match) {
                    box.classList.add('show');
                    var caret = document.querySelector('[data-bs-target="#' + box.id + '"], [href="#' + box.id + '"]');
                    if (caret) caret.setAttribute('aria-expanded', 'true');
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', openActiveGroup);
        } else {
            openActiveGroup();
        }
    })();
</script>
