<section class="position-relative overflow-hidden">
    <div class="position-relative bg-[#50d71e] bg-size-cover bg-repeat-0 bg-position-top zindex-4 pt-lg-3 pt-xl-5"
        style="background-image: url(<?= ASSET_URL ?>/core/main/img/banners/background.gif);">

        <!-- Text -->
        <div class="container zindex-4 pt-4">
            <div class="row align-items-center justify-content-center text-center pt-3 pb-sm-2 py-lg-5"
                style="height: 100vh;">
                <div class="col-xl-8 col-lg-9 col-md-10 py-5">
                    <div class="tilt-3d position-relative mx-auto" data-tilt data-tilt-glare data-tilt-max-glare="0">
                        <div class="tilt-3d-inner">
                            <h1 class="display-4 text-light pt-sm-2 pb-1 pb-sm-3 mb-3 line-rotate-heading">
                                <?= t('Premium') ?> <br> <span
                                    class="text-gradient-primary d-inline-block"><?= t('LoL Boosting Services') ?></span>
                                <br> <?= t('Boost Your Rank') ?>
                            </h1>
                        </div>
                        <h2 class="h5 text-light mb-3"><?= t('Premium LoL Boosting, Coaching & Accounts') ?></h2>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <!-- Buy Boosting Button -->
                        <a href="https://lolboost.gg/lol/rank-boost"
                            class="btn btn-lg shadow-primary d-flex align-items-center justify-content-center gap-2"
                            style="background-color: #6366f1; color: #fff; border-radius: 999px;">
                            <?= t('Buy Boosting') ?>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                        <!-- LoL Accounts Button -->
                        <a href="https://lolboost.gg/lol/accounts"
                            class="btn btn-lg shadow-primary d-flex align-items-center justify-content-center gap-2"
                            style="background-color: #262c62; border: 2px solid #262c62; color: #fff; border-radius: 999px;">
                            <?= t('LoL Accounts') ?>
                            <i class="fa-solid fa-helmet-battle"></i>
                        </a>
                    </div>

                    <!-- Reviews Box -->
                    <div class="mt-4 d-flex justify-content-center">
                        <div style="
                        border: 2px solid rgba(26, 29, 72, 0.6);
                        border-radius: 1rem;
                        padding: 0.6rem 1rem;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        background-color: rgba(0, 0, 0, 0.2);
                        flex-wrap: nowrap;
                    " class="review-box">
                            <!-- Stars -->
                            <div style="font-size: 18px; color: #04da8d;" class="stars">
                                ★★★★★
                            </div>
                            <!-- Rating Text -->
                            <div style="color: #fff; font-size: 15px;" class="rating-text">
                                <?= t('Rated 4.9 by over 360 reviews') ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Responsive Styles -->
<style>
    @media (max-width: 576px) {
        .review-box {
            padding: 0.5rem 0.75rem;
            gap: 6px;
        }

        .review-box .stars {
            font-size: 16px;
        }

        .review-box .rating-text {
            font-size: 14px;
        }
    }
</style>