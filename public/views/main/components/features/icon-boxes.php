<!-- <section class="container pt-5">
    <div class="row row-cols-1 row-cols-md-3 g-4 pt-2 pt-md-4">


        <div class="col">
            <a href="<?= BASE_URL ?>/lol/rank-boost" class="card d-block text-decoration-none flex-column flex-sm-row flex-md-column flex-xxl-row align-items-center border-gradient h-100">
                <div class="card-body text-center text-sm-start text-md-center d-flex flex-column justify-content-center">
                    <h3 class="h5 mb-2">LoL Boosting 🚀</h3>
                    <p class="fs-sm mb-1 text-body">Fast, cheap and high quality League of Legends elo boosting services.</p>
                    <span class="btn btn-link stretched-link px-0">
                        Buy Boosting
                        <i class="bx bx-right-arrow-alt fs-xl ms-1"></i>
                    </span>
                </div>
            </a>
        </div>


        <div class="col">
            <a href="<?= BASE_URL ?>/lol/coaching" class="card d-block text-decoration-none flex-column flex-sm-row flex-md-column flex-xxl-row align-items-center border-gradient h-100">
                <div class="card-body text-center text-sm-start text-md-center d-flex flex-column justify-content-center">
                    <h3 class="h5 mb-3">Coaching 🎓</h3>
                    <p class="fs-sm mb-1 text-body">Accelerate your learning progress, identify your weaknesses and reach your desired rank.</p>
                    <span class="btn btn-link stretched-link px-0">
                        Buy Coaching
                        <i class="bx bx-right-arrow-alt fs-xl ms-1"></i>
                    </span>
                </div>
            </a>
        </div>


        <div class="col">
            <a href="<?= BASE_URL ?>/lol/smurf-accounts" class="card d-block text-decoration-none flex-column flex-sm-row flex-md-column flex-xxl-row align-items-center border-gradient h-100">
                <div class="card-body text-center text-sm-start text-md-center d-flex flex-column justify-content-center">
                    <h3 class="h5 mb-2">LoL Smurfs 💎</h3>
                    <p class="fs-sm mb-1 text-body">Get a fresh start on your ranked journey with a brand new unranked LoL account.</p>
                    <span class="btn btn-link stretched-link px-0">
                        Buy Account
                        <i class="bx bx-right-arrow-alt fs-xl ms-1"></i>
                    </span>
                </div>
            </a>
        </div>
    </div>
</section> -->

<section class="container pt-5 text-center">
    <div class="mb-4">

        <div id="service-items" class="row row-cols-1 row-cols-md-3 g-3 pt-2 pt-md-3">
            <!-- Item -->
            <div class="col">
                <a href="<?= BASE_URL ?>/lol/rank-boost"
                    class="card d-block rounded-5 text-decoration-none h-100 service-card service-card-yellow small-card">
                    <div
                        class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-3 h-100">
                        <div class="card-image-container">
                            <div class="card-image"
                                style="background-image: url('<?= ASSET_URL ?>/core/main/img/banners/league111.jpg'); filter: brightness(1.0);">
                            </div>
                            <div class="badge"><i class="fa-solid fa-fire"></i> <?= t('50% Off') ?></div>
                        </div>
                        <img src="<?= ASSET_URL ?>/core/main/img/icons/league.svg" alt="League of Legends" class="icon">
                        <h5>
                            <?= t('League of Legends') ?>
                        </h5>
                        <button class="solid-button">
                            <i class="fad fa-gamepad"></i> <?= t('Buy Boost') ?>
                        </button>
                    </div>
                </a>
            </div>
            <!-- Item -->
            <div class="col">
                <a href="<?= BASE_URL ?>/val/rank-boost"
                    class="card d-block rounded-5 text-decoration-none h-100 service-card service-card-red small-card">
                    <div
                        class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-3 h-100">
                        <div class="card-image-container">
                            <div class="card-image"
                                style="background-image: url('<?= ASSET_URL ?>/core/main/img/banners/valorant1.jpg'); filter: brightness(1.0);">
                            </div>
                            <div class="badge"><i class="fa-solid fa-fire"></i> 20% Off</div>
                        </div>
                        <img src="<?= ASSET_URL ?>/core/main/img/icons/valorant.svg" alt="Valorant" class="icon">
                        <h5><?= t('Valorant') ?></h5>
                        <button class="solid-button">
                            <i class="fad fa-gamepad"></i> <?= t('Buy Boost') ?>
                        </button>
                    </div>
                </a>
            </div>
            <!-- Item -->
            <div class="col">
                <a href="<?= BASE_URL ?>/lol/premium-accounts"
                    class="card d-block rounded-5 text-decoration-none h-100 service-card service-card-blue small-card">
                    <div
                        class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-3 h-100">
                        <div class="card-image-container">
                            <div class="card-image"
                                style="background-image: url('<?= ASSET_URL ?>/core/main/img/banners/account111.webp'); filter: brightness(1.0);">
                            </div>
                            <div class="badge"><i class="fa-solid fa-fire"></i> <?= t('20% Off') ?></div>
                        </div>
                        <div class="icon-container d-flex align-items-center justify-content-center gap-2">
                            <img src="<?= ASSET_URL ?>/core/main/img/be.svg" alt="LoL Smurfs" class="icon">
                            <img src="<?= ASSET_URL ?>/core/main/img/rp.svg" alt="RP" class="icon">
                        </div>
                        <h4><?= t('LoL Accounts') ?></h4>
                        <button class="solid-button">
                            <i class="fad fa-gamepad"></i> <?= t('Buy Account') ?>
                        </button>
                    </div>
                </a>
            </div>
            <!-- Item -->

            <!--         
        <div class="col">
    <div class="card d-block rounded-5 text-decoration-none h-100 service-card service-card-red small-card"
         data-bs-toggle="popover"
         data-bs-placement="top"
         data-bs-trigger="hover"
         data-bs-content="This service is not available on the website, but you can buy it via Live-Chat.">
        <div class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-3 h-100">
            <div class="card-image-container">
                <div class="card-image" style="background-image: url('<?= ASSET_URL ?>/core/main/img/banners/marvel.webp'); filter: brightness(1.0);"></div>
                <div class="badge"><i class="fa-solid fa-fire"></i>COMING SOON</div>
            </div>
            <img src="<?= ASSET_URL ?>/core/main/img/icons/marvel-rivals.webp" alt="MarvelRivals" class="icon">
            <h5>Marvel Rivals</h5>
            <button class="solid-button" disabled>
                <i class="fad fa-gamepad"></i> Coming Soon
            </button>
        </div>
    </div>
</div>         -->

        </div>
        </a>
    </div>
    </div>
</section>

<div class="spacer" style="height: 100px;"></div>


<style>
    .card-image-container {
        position: relative;
        width: 192px;
        height: 256px;
    }

    .card-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 192px;
        height: 256px;
        background-size: cover;
        background-position: center;
    }

    .badge {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: #facc15;
        color: #422006;
        padding: 5px 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .icon-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .icon {
        width: 48px;
        height: 48px;
    }

    .small-card {
        max-width: 300px;
        /* Adjust the width as needed */
        margin: auto;
    }
</style>