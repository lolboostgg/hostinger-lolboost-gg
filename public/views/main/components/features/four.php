<!-- Fetures -->
<section class="container py-5">
    <div class="row align-items-center my-3 py-md-3 py-lg-5">
        <div class="col-md-6 order-md-2 mb-2 mb-md-0 pb-4 pb-md-0">
            <div class="position-relative mx-auto" style="max-width: 469px;">
                <div class="tilt-3d position-relative mx-auto angle-rotate">
                    <div class="tilt-3d-inner ">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/history.png?v6" class="d-block" alt=""
                            data-speed="1.1">
                    </div>
                </div>
                <div class="position-absolute top-50 w-75 left-20">
                    <div class="tilt-3d position-relative mx-auto angle-rotate">
                        <div class="tilt-3d-inner ">
                            <img src="<?= ASSET_URL ?>/core/main/img/illustrations/congrats.png" alt=""
                                data-speed="1.15">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 order-md-1 pb-md-4">
            <h2 class="pb-3 line-rotate-anim">
                <?= t('Take some time off the rift and we are doing the rest for you') ?> 🤩
            </h2>
            <ul class="list-unstyled pb-3 mb-3 clip-y-anim">
                <li class="d-flex align-items-center fw-500 mb-2">
                    <i class="fa-duotone fa-circle-check fs-xl text-primary me-2"></i>
                    <?= t('Real time livechat with our booster working on your order') ?>
                </li>
                <li class="d-flex align-items-center fw-500 mb-2">
                    <i class="fa-duotone fa-circle-check fs-xl text-primary me-2"></i>
                    <?= t('Track your order in real time and experience the results & monitor the entire progress') ?>
                </li>
                <li class="d-flex align-items-center fw-500 mb-2">
                    <i class="fa-duotone fa-circle-check fs-xl text-primary me-2"></i>
                    <?= t('Keep your account secure with an active VPN used by our boosters throughout the entire boosting progress') ?>
                </li>
            </ul>
            <a href="<?= BASE_URL ?>/lol/rank-boost" class="btn btn-primary ease-anim">
                <?= t("Let's do it!") ?>
                <i class="bx bx-right-arrow-alt fs-xl ms-2 me-n1"></i>
            </a>
        </div>
    </div>
    <div class="row align-items-center mt-2 mt-md-0 pt-5 pt-md-4 pt-lg-0 pb-md-3 pb-lg-5">
        <div class="col-md-6 col-lg-5 mb-2 mb-md-0 pb-4 pb-md-0">
            <div class="position-relative mx-auto" style="max-width: 462px;">
                <div class="tilt-3d position-relative mx-auto angle-rotate-alt">
                    <div class="tilt-3d-inner ">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/chat.png?v8" class="d-block" alt=""
                            data-speed="1.1">
                    </div>
                </div>
                <!-- <div class="position-absolute top-0 end-0 w-100 h-100 me-md-n3" data-percentage="0.5" data-disable-parallax-down="md">
                <img src="<?= ASSET_URL ?>/origin/main/img/landing/app-showcase/features/04.png" alt="">
            </div> -->
            </div>
        </div>
        <div class="col-md-6 col-xl-5 offset-lg-1">
            <h2 class="pb-3 line-rotate-anim"><?= t('Premium 24/7 support in multiple languages') ?> 💬</h2>
            <div class="d-inline-block bg-secondary rounded-3 bg-faded-primary-hover flex-shrink-0 p-3 mb-3">
                <img src="<?= ASSET_URL ?>/core/main/img/flags/uk.png" width="32px" alt="United Kingdom Flag">
            </div>
            <div class="d-inline-block bg-secondary rounded-3 bg-faded-primary-hover flex-shrink-0 p-3 mb-3">
                <img src="<?= ASSET_URL ?>/core/main/img/flags/de.png" width="32px" alt="German Flag">
            </div>
            <div class="d-inline-block bg-secondary rounded-3 bg-faded-primary-hover flex-shrink-0 p-3 mb-3">
                <img src="<?= ASSET_URL ?>/core/main/img/flags/sp.png" width="32px" alt="Spanish Flag">
            </div>
            <div class="d-inline-block bg-secondary rounded-3 bg-faded-primary-hover flex-shrink-0 p-3 mb-3">
                <img src="<?= ASSET_URL ?>/core/main/img/flags/gr.png" width="32px" alt="Greek Flag">
            </div>
            <div class="d-inline-block bg-secondary rounded-3 bg-faded-primary-hover flex-shrink-0 p-3 mb-3">
                <img src="<?= ASSET_URL ?>/core/main/img/flags/pr.png" width="32px" alt="Portgual Flag">
            </div>
            <p class="mb-3 clip-y-anim">
                <?= t('Our support team is available 24/7 to help you with any questions or issues you may have.') ?>
            </p>
            <a href="#open-chat" class="btn open-chat btn-primary ease-anim">
                <i class="fa-solid fa-comment-dots d8 me-2"></i>
                <?= t("Let's Chat") ?>
                <i class="bx bx-right-arrow-alt fs-xl ms-2 me-n1"></i>
            </a>
            <a href="discord" class="btn discord gradient-bg btn-primary ease-anim">
                <i class="fa-brands fa-discord d8 me-2"></i>
                <?= t('Join Discord') ?>
                <i class="bx bx-right-arrow-alt fs-xl ms-2 me-n1"></i>
            </a>
        </div>
    </div>
</section>