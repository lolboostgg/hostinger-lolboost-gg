<section class="container py-5 my-2 my-md-4 my-lg-5">
    <div class="row pt-2 py-xl-3">
        <div class="col-lg-3 col-md-4">
        <h2 class="h1 text-center text-md-start mx-auto mx-md-0 pt-md-2" style="max-width: 300px;">What <br class="d-none d-md-inline">People Say <br class="d-none d-md-inline">About App:</h2>

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
            "500": {
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
                    <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                    <i class="fa-duotone fa-quote-left"></i>
                    </span>
                    <blockquote class="card-body pb-3 mb-0">
                    <p class="mb-0">Id mollis consectetur congue egestas egestas suspendisse blandit justo. Tellus augue commodo id quis tempus etiam pulvinar at maecenas.</p>
                    </blockquote>
                    <div class="card-footer border-0 text-nowrap pt-0">
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bx-star text-muted opacity-75"></i>
                    <i class="bx bx-star text-muted opacity-75"></i>
                    </div>
                </div>
                <figcaption class="d-flex align-items-center ps-4 pt-4">
                    <img src="assets/img/avatar/16.jpg" width="48" class="rounded-circle" alt="Robert Fox">
                    <div class="ps-3">
                    <h6 class="fs-sm fw-semibold mb-0">Robert Fox</h6>
                    <span class="fs-xs text-muted">Founder of Lorem Company</span>
                    </div>
                </figcaption>
                </figure>
            </div>

            <!-- Item -->
            <div class="swiper-slide h-auto pt-4">
                <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                    <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                    <i class="fa-duotone fa-quote-left"></i>
                    </span>
                    <blockquote class="card-body pb-3 mb-0">
                    <p class="mb-0">Phasellus luctus nisi id orci condimentum, at cursus nisl vestibulum. Orci varius natoque penatibus et magnis dis parturient montes commodo.</p>
                    </blockquote>
                    <div class="card-footer border-0 text-nowrap pt-0">
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    </div>
                </div>
                <figcaption class="d-flex align-items-center ps-4 pt-4">
                    <img src="assets/img/avatar/08.jpg" width="48" class="rounded-circle" alt="Annette Black">
                    <div class="ps-3">
                    <h6 class="fs-sm fw-semibold mb-0">Annette Black</h6>
                    <span class="fs-xs text-muted">CEO of Ipsum Company</span>
                    </div>
                </figcaption>
                </figure>
            </div>

            <!-- Item -->
            <div class="swiper-slide h-auto pt-4">
                <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                    <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                    <i class="fa-duotone fa-quote-left"></i>
                    </span>
                    <blockquote class="card-body pb-3 mb-0">
                    <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ipsum odio, bibendum ornare mi at, efficitur urna.</p>
                    </blockquote>
                    <div class="card-footer border-0 text-nowrap pt-0">
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bx-star text-muted opacity-75"></i>
                    </div>
                </div>
                <figcaption class="d-flex align-items-center ps-4 pt-4">
                    <img src="assets/img/avatar/13.jpg" width="48" class="rounded-circle" alt="Jerome Bell">
                    <div class="ps-3">
                    <h6 class="fs-sm fw-semibold mb-0">Jerome Bell</h6>
                    <span class="fs-xs text-muted">Founder of the Agency </span>
                    </div>
                </figcaption>
                </figure>
            </div>

            <!-- Item -->
            <div class="swiper-slide h-auto pt-4">
                <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                    <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                    <i class="fa-duotone fa-quote-left"></i>
                    </span>
                    <blockquote class="card-body pb-3 mb-0">
                    <p class="mb-0">Pellentesque finibus congue egestas egestas suspendisse blandit justo. Tellus augue commodo id quis tempus etiam pulvinar at maecenas.</p>
                    </blockquote>
                    <div class="card-footer border-0 text-nowrap pt-0">
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    <i class="bx bxs-star text-warning"></i>
                    </div>
                </div>
                <figcaption class="d-flex align-items-center ps-4 pt-4">
                    <img src="assets/img/avatar/09.jpg" width="48" class="rounded-circle" alt="Albert Flores">
                    <div class="ps-3">
                    <h6 class="fs-sm fw-semibold mb-0">Albert Flores</h6>
                    <span class="fs-xs text-muted">CEO of Dolor Ltd.</span>
                    </div>
                </figcaption>
                </figure>
            </div>
            </div>
        </div>
        </div>
    </div>
</section>