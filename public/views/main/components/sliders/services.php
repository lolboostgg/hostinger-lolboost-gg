<section class="container py-5">
    <div class="text-center pb-4 mb-3 mx-auto" style="max-width: 530px;">
        <h2 class="h1">What we are offering 🙂</h2>
        <p class="mb-0 line-rotate-anim">Browse throught a variety of services in different games.</p>
    </div>
    <div class="row align-items-center">
        <div class="col-md-6 pb-4 pb-md-0 mb-2 mb-md-0">

            <!-- Swiper tabs -->
            <div class="position-relative px-5">
                <div class="swiper-tabs zindex-2 mx-auto" style="max-width: 384px;" data-speed="1.1">
                    <div id="boosting" class="swiper-tab active">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/boosting.png?v" class="angle-rotate-alt" alt="">
                    </div>
                    <div id="accounts" class="swiper-tab">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/accounts.png?v8turbo" class="angle-incline" alt="">
                    </div>
                    <div id="coaching" class="swiper-tab">
                        <img src="<?= ASSET_URL ?>/core/main/img/illustrations/coaching.png" class="angle-incline-alt" alt="">
                    </div>
                </div>
                <div class="bg-primary position-absolute start-0 bottom-0 w-100 opacity-15 rounded-3" style="height: 86.5%;"></div>
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
                "prevEl": "#prev-illustration",
                "nextEl": "#next-illustration"
            }
            }'>
                    <div class="swiper-wrapper">
                        <div class="swiper-slide" data-swiper-tab="#boosting">
                            <h3 class="mb-lg-4 line-rotate-anim">Boosting 🚀</h3>
                            <p class="ease-anim">We provide professional LoL and VAL boosting services to help you climb the ranks and reach your desired division quickly.</p>
                            <a href="<?= BASE_URL ?>/lol/rank-boost" class="btn btn-primary ease-anim">Buy Boost</a>
                        </div>
                        <div class="swiper-slide" data-swiper-tab="#accounts">
                            <h3 class="mb-lg-4 line-rotate-anim">Smurf Accounts 💎</h3>
                            <p class="ease-anim">Explore our wide selection of smurf accounts to enhance your gaming experience.</p>
                            <a href="<?= BASE_URL ?>/lol/smurf-accounts" class="btn btn-primary ease-anim">Browse Accounts</a>
                        </div>
                        <div class="swiper-slide" data-swiper-tab="#coaching">
                            <h3 class="mb-lg-4 line-rotate-anim">Coaching 🎓</h3>
                            <p class="ease-anim">Our experienced coaches provide personalized coaching sessions to help you improve your gameplay and strategies in League of Legends.</p>
                            <a href="<?= BASE_URL ?>/lol/coaching" class="btn btn-primary ease-anim">Book Session</a>
                        </div>
                    </div>
                </div>

                <!-- Slider controls (Prev / next) -->
                <div class="d-flex justify-content-center justify-content-md-start pt-2 pt-lg-3">
                    <button type="button" id="prev-illustration" class="btn btn-prev btn-icon btn-sm me-2">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" id="next-illustration" class="btn btn-next btn-icon btn-sm ms-2">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>