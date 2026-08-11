<!-- Testimonials -->
<section class=""
    style="background: radial-gradient(ellipse at 50% 100%, rgba(241, 244, 253, 0.07) 0%, rgba(218, 70, 239, 0.05) 30%, rgba(99, 102, 241, 0.1) 70%);">
    <div class="container py-5 mt-sm-2 mt-md-4 mt-lg-5">
        <div class="row pt-2 py-xl-3">
            <div class="col-lg-2 col-md-3">
                <h2 class="fs-2 text-center text-md-start mx-auto mx-md-0 pt-md-2" style="max-width: 300px;">
                    <?= t('Customer') ?>
                    <br class="d-none d-md-inline"><?= t('Reviews') ?>⭐
                </h2>


                <!-- <div class="d-flex justify-content-center">
                <?= $this->insert('main/components/widgets/tp-light-lg') ?>
            </div> -->
                <!-- Slider controls (Prev / next buttons) -->
                <div class="d-flex justify-content-center justify-content-md-start pb-4 mb-2 pt-2 pt-md-4 mt-md-5">
                    <button type="button" id="prev-testimonial" class="btn btn-prev btn-icon btn-sm me-2">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" id="next-testimonial" class="btn btn-next btn-icon btn-sm ms-2">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">
                <div class="swiper mx-n2" data-swiper-options='{
                "slidesPerView": 1,
                "spaceBetween": 8,
                "loop": true,
                "navigation": {
                "prevEl": "#prev-testimonial",
                "nextEl": "#next-testimonial"
                },
                "breakpoints": {
                "400": {
                    "slidesPerView": 1
                },
                "650": {
                    "slidesPerView": 2
                },
                "1000": {
                    "slidesPerView": 2
                },
                "1200": {
                    "slidesPerView": 3
                }
                }
            }'>
                    <div class="swiper-wrapper">

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="fa-duotone fa-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">
                                            <?= t('This boost site is unparalleled in every aspect. From its service to its support and boosters, it\'s a flawless 10/10 experience.') ?>
                                        </p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" width="48"
                                        class="rounded-circle" alt="">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">J****</h6>
                                        <span class="fs-xs text-muted">Gold II<i
                                                class="fa-duotone fa-right-long align-middle"></i> Platinum III</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="fa-duotone fa-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">
                                            <?= t('Simply super. The logins were sent directly via email, and whenever I had questions, the live chat support was there to assist me promptly.') ?>
                                        </p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" width="48"
                                        class="rounded-circle" alt="">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">S******</h6>
                                        <span class="fs-xs text-muted">Smurf Account</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="fa-duotone fa-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">
                                            <?= t('Leveled Shen from level 1 to 7 in a very, very short time. So if you want to level up your champions, I can only recommend lolboost.gg.') ?>
                                        </p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" width="48"
                                        class="rounded-circle" alt="">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">X****</h6>
                                        <span class="fs-xs text-muted">LoL Champion Mastery </span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="fa-duotone fa-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">
                                            <?= t('Great service. By far the best. The booster won all the games and completed the boost in a short time. Wow, I\'d gladly do it again.') ?>
                                        </p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                        <i class="bx bxs-star text-gradient-primary"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="<?= ICON_URL ?>/ca88cc14-4318-4b08-b5c8-b54d026a6692.jpeg" width="48"
                                        class="rounded-circle" alt="">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">M*****</h6>
                                        <span class="fs-xs text-muted">Silver II <i
                                                class="fa-duotone fa-right-long align-middle"></i> Platinum IV.</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>