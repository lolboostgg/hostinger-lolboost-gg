<style>
    /* Gestylter Farbpunkte */
    .theme-options .item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .theme-options .item::before {
        content: "";
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid white;
    }

    /* Farbzuweisung je nach Theme-Wert */
    .theme-options input[data-value="light"]+.item::before {
        background-color: #f5f5f5;
        border-color: #636569;
    }

    .theme-options input[data-value="blue"]+.item::before {
        background-color: #171948;
        border-color: #323539;
    }

    .theme-options input[data-value="dark"]+.item::before {
        background-color: #101223;
        border-color: #484b50;
    }

    .lang-currency-select .dropdown {
        display: grid;
    }

    .lang-currency-select .dropdown-menu {
        top: 25px;
    }
</style>

<!-- Remove "navbar-sticky" class to make navigation bar scrollable with the page -->
<header
    class="header navbar navbar-expand-lg opacity-100 <?= $inv ? 'bg-light' : 'position-absolute navbar-sticky ' ?> ease-anim">
    <div class="container-fluid mx-lg-5 px-3 px-lg-5">
        <a href="<?= BASE_URL ?>" class="navbar-brand obj-rotate-anim">
            <img src="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon.svg" alt="LOLBOOST.GG">
        </a>
        <div id="navbarNav" class="offcanvas w-100 offcanvas-end">
            <div class="offcanvas-header border-bottom border-dark">
                <h5 class="offcanvas-title text-dark sr-only">LOLBOOST.GG</h5>
                <a href="<?= BASE_URL ?>" class="logo-container">
                    <img src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png" alt="LOLBOOST.GG"
                        class="navbar-brand obj-rotate-anim original-logo" width="200">
                </a>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav me-auto mb-2 gap-2 mb-lg-0">
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa-duotone fa-circle-up fs-5 me-2 d-lg-none"></i>
                            <?= t('Boosting') ?>
                        </a>
                        <div class="dropdown-menu mega-menu-dropdown">
                            <!-- display only on lg -->
                            <div class="mega-menu">
                                <div class="mega-menu-elements d-grid">
                                    <div class="row justify-content-center align-content-center">
                                        <div class="col-12">
                                            <a href="<?= BASE_URL . '/lol/rank-boost' ?>"
                                                class="d-flex text-primary dropdown-item ms-0 ps-0 align-items-center fw-bold fs-lg"
                                                id="lol-trigger">
                                                <img src="<?= ASSET_URL ?>/core/main/img/icons/lol.svg" class="me-2" />
                                                <span><?= t('League of Legends') ?></span>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <a href="<?= BASE_URL . '/val/rank-boost' ?>"
                                                class="d-flex align-items-center dropdown-item fw-bold fs-lg ms-0 ps-0"
                                                id="val-trigger">
                                                <img src="<?= ASSET_URL ?>/core/main/img/icons/val.png" width="35"
                                                    height="35" class="me-2" />
                                                <span><?= t('Valorant') ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="mega-menu-elements-items d-none" id="lol-services">
                                    <a href="<?= BASE_URL ?>/lol/rank-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-ranking-star me-2"></i><?= t('Rank Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/win-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-medal me-2"></i><?= t('Win Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/placements-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-compass me-2"></i><?= t('Placements Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/matches-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-handshake me-2"></i><?= t('Hire Pro Teammate') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/normals-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-dice-d8 me-2"></i><?= t('Normals Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/champion-mastery" class="btn btn-secondary">
                                        <i class="fa-duotone fa-masks-theater me-2"></i><?= t('Champion Mastery') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/level-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-arrow-trend-up me-2"></i><?= t('Level Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/arena-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-trophy me-2"></i><?= t('Arena Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/lol/rank-boost" class="btn btn-warning col-span-full">
                                        <i class="fa-duotone fa-solid fa-rocket me-2"></i><?= t('Buy LoL Boosting') ?>
                                    </a>
                                </div>



                                <div class="mega-menu-elements-items" id="val-services">
                                    <a href="<?= BASE_URL ?>/val/rank-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-ranking-star me-2"></i><?= t('Rank Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/val/win-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-medal me-2"></i><?= t('Win Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/val/placements-boost" class="btn btn-secondary">
                                        <i class="fa-duotone fa-compass me-2"></i><?= t('Placements Boost') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/val/unrated-matches" class="btn btn-secondary">
                                        <i class="fa-duotone fa-dice-d8 me-2"></i><?= t('Unrated Matches') ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/val/rank-boost" class="btn btn-danger col-span-full">
                                        <i class="fa-duotone fa-solid fa-rocket me-2"></i><?= t('Buy VAL Boosting') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown d-lg-none">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png" class="me-2" />
                            <?= t('League of Legends') ?>
                        </a>
                        <ul class="dropdown-menu ps-1 pe-4">
                            <li>
                                <a href="<?= BASE_URL ?>/lol/rank-boost" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-ranking-star me-2"></i>
                                    <?= t('Rank Boost') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/lol/win-boost" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-medal me-2 "></i>
                                    <?= t('Win Boost') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/lol/placements-boost" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-compass me-2"></i>
                                    <?= t('Placements Boost') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/lol/normals-boost" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-dice-d8 me-2"></i>
                                    <?= t('Normals Boost') ?>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown d-lg-none">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?= ASSET_URL ?>/core/main/img/icons/val-dark.png" class="me-2" />
                            <?= t('Valorant') ?>
                        </a>
                        <ul class="dropdown-menu ps-1 pe-4">
                            <li>
                                <a href="<?= BASE_URL ?>/val/rank-boost" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-ranking-star me-2"></i>
                                    <?= t('Rank Boost') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/val/win-boost" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-medal me-2"></i>
                                    <?= t('Win Boost') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/val/placements-boost" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-compass me-2"></i>
                                    <?= t('Placements Boost') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>/val/unrated-matches" class="dropdown-item fw-medium">
                                    <i class="fa-regular fa-dice-d8 me-2"></i>
                                    <?= t('Unrated Matches') ?>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa-duotone fa-graduation-cap fs-5 me-2 d-lg-none"></i>
                            <?= t('Coaching') ?>
                        </a>
                        <ul class="dropdown-menu ps-1 pe-4">
                            <li class="d-flex align-items-center mb-2">
                                <!-- First image: desktop only (≥992px) -->
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/lol.svg" class="d-none d-lg-inline"
                                    width="35" height="35">

                                <!-- Second image: mobile only (<992px) -->
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png"
                                    class="d-inline d-lg-none ps-2" width="30" height="30">
                                <a href="<?= BASE_URL ?>/lol/coaching"
                                    class="dropdown-item fw-medium"><?= t('League of Legends') ?></a>
                            </li>
                            <li class="d-flex align-items-center">
                                <!-- Desktop image (≥992px) -->
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/val.png" class="d-none d-lg-inline"
                                    width="35" height="35">
                                <!-- Mobile image (<992px) -->
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/val-dark.png"
                                    class="d-inline d-lg-none ps-2" width="30" height="30">
                                <a href="<?= BASE_URL ?>/val/coaching"
                                    class="dropdown-item fw-medium"><?= t('Valorant') ?></a>
                            </li>
                        </ul>
                    </li>

                    <!-- <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa-duotone fa-gamepad fs-5 me-2 d-lg-none"></i>
                            Accounts
                        </a>
                        <ul class="dropdown-menu ps-1 pe-4">
                            <li class="d-flex align-items-center mb-2">
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/rp.svg" class="d-sm-none d-lg-inline"
                                    width="35" height="35">
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/rp.svg" class="d-lg-none ps-2" width="30"
                                    height="30">
                                <a href="<?= BASE_URL ?>/lol/premium-accounts" class="dropdown-item fw-medium">Premium
                                    Accounts</a>
                            </li>
                            <li class="d-flex align-items-center">
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/be.svg" class="d-sm-none d-lg-inline"
                                    width="35" height="35">
                                <img src="<?= ASSET_URL ?>/core/main/img/icons/be.svg" class="d-lg-none ps-2" width="30"
                                    height="30">
                                <a href="<?= BASE_URL ?>/lol/smurf-accounts" class="dropdown-item fw-medium">Smurf
                                    Accounts</a>

                            </li>
                        </ul>
                    </li> -->

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="lolDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-duotone fa-helmet-battle fs-5 me-2 d-lg-none"></i>
                            <?= t('LoL Accounts') ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="lolDropdown">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/lol/accounts">
                                    <i class="fa-solid fa-trophy me-2"></i><?= t('LoL Ranked Accounts') ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/lol/premium-accounts">
                                    <i class="fa-solid fa-user-ninja me-2"></i><?= t('LoL Smurf Accounts') ?>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/boosters" class="nav-link">
                            <i class="fa-duotone fa-rocket fs-5 me-2 d-lg-none"></i>
                            <?= t('Boosters') ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/loyalty" class="nav-link">
                            <i class="fa-duotone fa-trophy fs-5 me-2 d-lg-none"></i>
                            <?= t('Loyalty') ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/blog" class="nav-link">
                            <i class="fa-duotone fa-newspaper fs-5 me-2 d-lg-none"></i>
                            <?= t('Blog') ?>
                        </a>
                    </li>


                    <!-- DarkMode -->
                    <li class="nav-item dropdown theme-select">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-moon fs-5 me-2 hide-desktop rounded-circle d-inline-flex justify-content-center align-items-center"
                                style="width: 32px; height: 32px;"></i></a>
                        <ul class="dropdown-menu p-0 overflow-hidden">
                            <h6 class="dropdown-header">Change Theme</h6>
                            <hr>
                            <div class="theme-options" id="theme-options">
                                <label>
                                    <input type="checkbox" data-value="light">
                                    <span class="item">

                                        <span class="text"><?= t('Light') ?></span>
                                    </span>
                                </label>
                                <label>
                                    <input type="checkbox" data-value="blue">
                                    <span class="item">

                                        <span class="text"><?= t('Blue') ?></span>
                                    </span>
                                </label>
                                <label>
                                    <input type="checkbox" data-value="dark">
                                    <span class="item">

                                        <span class="text"><?= t('Dark') ?></span>
                                    </span>
                                </label>
                            </div>
                        </ul>
                    </li>
                    <!-- DarkMode ENDED-->

                    <!--  <div class="dropdown">
                    <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="selectThemeDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation></button>
                    <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless" aria-labelledby="selectThemeDropdown">
                        <a class="dropdown-item active" href="#" data-icon="fa-duotone fa-moon" data-value="dark">
                            <i class="fa-duotone fa-moon me-2"></i>
                            <span class="text-truncate" title="Dark">Dark Mode</span>
                        </a>
                        <a class="dropdown-item" href="#" data-icon="fa-duotone fa-sun" data-value="light">
                            <i class="fa-duotone fa-sun me-2"></i>
                            <span class="text-truncate" title="Light">Light Mode</span>
                        </a>
                        <a class="dropdown-item" href="#" data-icon="fa-duotone fa-sparkles" data-value="default">
                            <i class="fa-duotone fa-sparkles me-2"></i>
                            <span class="text-truncate" title="System Default">System Default</span>
                        </a>
                    </div>
                </div>  -->




                    <li class="nav-item">
                        <?php if (CLIENT_DATA != false): ?>
                            <a href="<?= BASE_URL ?>/profile/orders"
                                class="btn btn-primary rounded align-items-center d-lg-none w-100 text-truncate"><i
                                    class="fa-solid fa-user me-2"></i> <?= CLIENT_DATA['username'] ?></a>
                        <?php else: ?>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#auth_modal"
                                class="btn btn-primary rounded-pill d-lg-none w-100">
                                <i class="fa-solid fa-right-to-bracket me-2"></i><?= t('Login') ?>
                            </a>
                        <?php endif; ?>
                    </li>

                </ul>
            </div>




            <div class="offcanvas-header border-top border-dark">
                <div class="d-flex justify-content-center mx-auto">
                    <a href="#" class="btn btn-icon btn-secondary btn-discord mx-2">
                        <i class="fa-brands fa-discord"></i>
                    </a>
                    <a href="#" class="btn btn-icon btn-secondary btn-instagram mx-2">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-icon btn-secondary btn-twitter mx-2">
                        <i class="fa-brands fa-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-icon btn-secondary btn-tiktok mx-2">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="pe-lg-1 navbar-nav ms-auto me-4">
            <div
                class="d-flex align-items-center btn bg-faded-primary border-0 nav-link px-4 rounded-pill gap-4 lang-currency-select">
                <!-- <div class="nav-item dropdown">
                    <div class="text-decoration-none dropdown-toggle d-inline-flex align-items-center" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (LANG == 'en' || is_null(LANG)): ?>
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/us.svg" width="25"
                                height="25" class="me-2 rounded-2">
                        <?php elseif (LANG == 'de'): ?>
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/de.svg" width="25"
                                height="25" class="me-2 rounded-2">
                        <?php endif; ?>
                        <span class="me-2" id="selected-currency">
                            <?= array_key_exists(LANG, util_language_list())
                                ? util_language_list()[LANG]
                                : 'English' ?>
                        </span>
                    </div>

                    <div class="dropdown-menu mt-2 bg-white rounded-2">
                        <button class="dropdown-item align-items-center d-flex" onclick="changeLanguage('en')">
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/us.svg" width="25"
                                height="25" class="me-2 rounded-2">
                            <span>English</span>
                        </button>
                        <button class="dropdown-item align-items-center d-flex" onclick="changeLanguage('de')">
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/de.svg" width="25"
                                height="25" class="me-2 rounded-2">
                            <span>German</span>
                        </button>
                    </div>
                </div> -->

                <div class="nav-item dropdown">
                    <div class="dropdown-toggle d-inline-flex align-items-center" id="currency-select" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if ($_SESSION['currency'] == 'EUR' || is_null($_SESSION['currency'])): ?>
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/eu.svg" width="25"
                                height="25" class="me-2 rounded-2">
                        <?php elseif ($_SESSION['currency'] == 'USD'): ?>
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/us.svg" width="25"
                                height="25" class="me-2 rounded-2">
                        <?php endif; ?>
                        <span class="me-2" id="selected-currency"><?= $_SESSION['currency'] ?? 'EUR' ?></span>
                    </div>

                    <div class="dropdown-menu mt-2 bg-white rounded-2">
                        <button class="dropdown-item align-items-center d-flex" onclick="changeCurrency('EUR')">
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/eu.svg" width="25"
                                height="25" class="me-2 rounded-2">
                            <span>EUR</span>
                        </button>
                        <button class="dropdown-item align-items-center d-flex" onclick="changeCurrency('USD')">
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/us.svg" width="25"
                                height="25" class="me-2 rounded-2">
                            <span>USD</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <button type="button" class="navbar-toggler btn bg-faded-primary border-0 rounded-circle"
            data-bs-toggle="offcanvas" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
            aria-label="Toggle navigation">
            <i class="fa-light fa-bars"></i>
        </button>

        <?php if (CLIENT_DATA != false): ?>
            <a href="<?= BASE_URL ?>/profile/overview"
                class="btn btn-primary rounded-pill d-none d-lg-flex align-items-center text-truncate"><i
                    class="fa-solid fa-user me-2"></i> <?= CLIENT_DATA['username'] ?></a>
            <button type="button"
                class="btn btn-outline-light rounded-pill ms-2 px-3 d-none d-lg-flex align-items-center text-truncate coins-display">
                <img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt="Coin Icon" width="28" height="28"
                    class="d-inline-block me-2">
                <?= CLIENT_DATA['points'] ?>
            </button>
        <?php else: ?>
            <a href="#" data-bs-toggle="modal" data-bs-target="#auth_modal"
                class="btn btn-primary rounded-pill d-none d-lg-inline-flex">
                <i class="fa-solid fa-right-to-bracket me-2"></i><?= t('Login') ?>
            </a>
        <?php endif; ?>
    </div>
</header>



<script>
    function changeCurrency(currency) {
        $.ajax({
            url: "<?= AJAX_URL ?>",
            type: "POST",
            data: {
                action: "change_currency",
                currency: currency,
            },
            success: function (response) {
                location.reload();
            },
        });
    }

    function changeLanguage(lang) {
        let currentPath = window.location.pathname;
        let newPath = '/' + lang + currentPath.replace(/^\/(en|de)/, '');
        window.location.href = newPath;
    }

</script>