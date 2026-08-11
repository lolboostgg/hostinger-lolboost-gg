<section class="container py-5">
    <div class="text-center pb-4 mb-3 mx-auto" style="max-width: 530px;">
        <h2 class="h1"><?= t('How does it work?') ?> 🤔</h2>
        <p class="mb-0 line-rotate-anim"><?= t('Get set up and start your LoL boost order in less than') ?>
            <b><?= t('2 minutes') ?></b>
        </p>
    </div>
    <div class="row align-items-center">
        <div class="col-md-6 pb-4 pb-md-0 mb-2 mb-md-0">

            <!-- Swiper tabs -->
            <div class="position-relative px-5">
                <div class="swiper-tabs zindex-2 mx-auto" style="max-width: 384px;" data-speed="1.1">
                    <div id="screen-1" class="swiper-tab active">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/hiw-1.png?v" class="angle-rotate-alt"
                            alt="">
                    </div>
                    <div id="screen-2" class="swiper-tab">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/hiw-2.png?v8turbo" class="angle-incline"
                            alt="">
                    </div>
                    <div id="screen-3" class="swiper-tab">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/hiw-3.png" class="angle-incline-alt"
                            alt="">
                    </div>
                </div>
                <div class="bg-primary position-absolute start-0 bottom-0 w-100 opacity-15 rounded-3"
                    style="height: 86.5%;"></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5 offset-lg-1">
            <div class="ps-md-4 ps-lg-0">

                <!-- Swiper slider -->
                <div class="swiper text-center text-md-start mt-auto" data-swiper-options='{
            "spaceBetween": 30,
            "loop": true,
            "tabs": true,
            "navigation": {
                "prevEl": "#prev-screen",
                "nextEl": "#next-screen"
            }
            }'>
                    <div class="swiper-wrapper">
                        <div class="swiper-slide" data-swiper-tab="#screen-1">
                            <div class="fs-xl text-primary fw-bold mb-3 mb-lg-4"><?= t('STEP #1') ?></div>
                            <h3 class="mb-lg-4 line-rotate-anim"><?= t('Select your boost method') ?> ✍️</h3>
                            <p class="ease-anim">
                                <?= t('We offer various boosting services, select the one most suited to your needs, add in your prefered customizations and extra-options then proceed to the next step.') ?>
                            </p>
                            <a href="<?= BASE_URL ?>/lol/rank-boost"
                                class="btn btn-primary ease-anim"><?= t('Get Started') ?></a>
                        </div>
                        <div class="swiper-slide" data-swiper-tab="#screen-2">
                            <div class="fs-xl text-primary fw-bold mb-3 mb-lg-4"><?= t('STEP #2') ?></div>
                            <h3 class="mb-lg-4 line-rotate-anim"><?= t('Complete your payment') ?> 💳</h3>
                            <p class="ease-anim">
                                <?= t("You're just one step away from your desired rank! Simply choose your preferred payment processor, apply any discount codes, if applicable, and complete your payment securely on the third-party website.") ?>
                            </p>
                        </div>
                        <div class="swiper-slide" data-swiper-tab="#screen-3">
                            <div class="fs-xl text-primary fw-bold mb-3 mb-lg-4"><?= t('STEP #3') ?></div>
                            <h3 class="mb-lg-4 line-rotate-anim"><?= t('Enjoy your new rank') ?> 🎉</h3>
                            <p class="ease-anim">
                                <?= t("Congratulations! You're all set to chat live with your designated booster and witness your account climb up the ranks. We'll notify you as soon as your desired rank is achieved.") ?>
                            </p>
                            <a href="<?= BASE_URL ?>/lol/rank-boost"
                                class="btn btn-primary ease-anim"><?= t("Let's do it!") ?></a>
                        </div>
                    </div>
                </div>

                <!-- Slider controls (Prev / next) -->
                <div class="slider-controls">
                    <button type="button" id="prev-screen" class="btn btn-prev btn-icon btn-sm me-2">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" id="next-screen" class="btn btn-next btn-icon btn-sm ms-2">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>