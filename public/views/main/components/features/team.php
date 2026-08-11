<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" />

<style>
    .team-section {
        margin-top: 60px;
        background: #ffffff;
        padding-bottom: 60px;
        /* Added padding at the bottom */
        overflow-y: visible !important;
        /* Ensures the section does not overflow horizontally */
        overflow-x: hidden !important;
    }

    .swiper-container-unique {
        max-width: 100%;
        /* Maximal so breit wie der Container */
        overflow-y: visible !important;
        /* Ensures the section does not overflow horizontally */
        overflow-x: hidden !important;
    }

    .rounded-circle {
        border-radius: 50%;
    }

    .border-5 {
        border-width: 5px;
    }

    .border-white {
        border-opacity: 1;
        border-color: rgba(255, 255, 255, 1);
    }

    .shadow {
        box-shadow: 0 0.375rem 1.5rem 0 rgba(0, 0, 0, 0.3);
    }

    img.avatar {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }

    .icon-sm {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 75%;
        line-height: normal;
        background-color: #e8e8fd;
        border-radius: 50%;
    }

    .me-1 {
        margin-right: 0.25rem;
    }

    .fw-700 {
        font-weight: 700;
    }

    .mb-1 {
        margin-bottom: 0.25rem;
    }

    .z-index-1 {
        z-index: 1;
    }

    .pt-6 {
        padding-top: 2.5rem;
    }

    .p-4 {
        padding: 1.5rem;
    }

    .mt-n4 {
        margin-top: -1.5rem;
    }

    .px-5 {
        padding-right: 2rem;
        padding-left: 2rem;
    }

    .position-relative {
        position: relative;
    }

    @keyframes shine {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .rank-symbol {
        width: 50px;
        height: 50px;
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .rating-badge {
        background-color: #f3f6ff;
        border: 1px solid gold;
        border-radius: 10px;
        padding: 3px 8px;
        position: absolute;
        top: 10px;
        left: 10px;
        display: flex;
        align-items: center;
        font-weight: bold;
        font-size: 0.75rem;
    }

    .rating-badge i {
        margin-right: 3px;
        color: gold;
    }

    @keyframes gradientAnimation {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .nova-booster {
        background: linear-gradient(270deg, #2f3ebf, #47339b, #9f0aa3);
        /* Dunkleres Gradient */
        background-size: 400% 400%;
        animation: gradientAnimation 8s ease infinite;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .custom-card {
        background: #f3f6ff;
    }

    .champion-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        margin-right: 0.25rem;
    }

    .hero-booster {
        background: linear-gradient(270deg, #a3bffa, #b18afc, #f08bf7);
        /* Helleres Gradient */
        background-size: 400% 400%;
        animation: gradientAnimation 8s ease infinite;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
        overflow-y: visible !important;
        /* Ensures the section does not overflow horizontally */
    }

    [data-user-theme="blue"] .role-icon {
        background-color: var(--blue-bg-accent);
    }

    [data-user-theme="blue"] .role-icon img {
        filter: contrast(0.5) brightness(2);
    }

    [data-user-theme="dark"] .role-icon {
        background-color: var(--dark-bg-accent);
    }

    [data-user-theme="dark"] .role-icon img {
        filter: contrast(0.5) brightness(2);
    }

    .role-icon img {
        filter: contrast(0.5) brightness(2) invert(1);
    }
</style>

<section id="team" class="section bg-gray-100 team-section">
    <div class="container">
        <div class="row section-heading justify-content-center text-center" data-wow-duration="0.3s"
            data-wow-delay="0.3s">
            <div class="col-lg-8 col-xl-6">
                <div class="text-center pb-4 mb-3 mx-auto" style="max-width: 530px;">
                    <h2 class="h1"><?= t('Our Boosters') ?></h2>
                    <p class="mb-0 line-rotate-anim">
                        <?= t('Meet the best of the') ?> <b><?= t('best') ?></b>
                        <?= t('League of Legends Booster and Coaches out there.') ?>
                    </p>
                </div>
            </div>
            <div class="swiper-container swiper-container-unique">
                <div class="swiper-wrapper">
                    <?php foreach ($boosters as $booster): ?>
                        <div class="swiper-slide">
                            <div class="text-center">
                                <div class="z-index-1 position-relative px-5">
                                    <img class="rounded-circle border border-5 border-white avatar"
                                        src="<?= $booster['icon'] ?>" title="" alt="">
                                </div>
                                <div
                                    class="mx-2 mx-xl-3 shadow rounded-3 position-relative mt-n4 custom-card p-4 pt-6 mx-4 text-center">
                                    <div class="rating-badge">
                                        <i class="bi bi-star-fill"></i>
                                        <?= $booster['rating'] ?>
                                    </div>
                                    <h6 class="fw-700 hero-booster mb-1">
                                        <?= $booster['username'] ?>
                                        <?php $rank = explode('|', $booster['lol_rank']); ?>
                                        <img class="rank-symbol"
                                            src="<?= ASSET_URL ?>/core/main/img/lol/ranks/max/<?= $rank[0] ?>.png"
                                            alt="rank">
                                    </h6>
                                    <small class="fw-700 dark-color mb-1"><?= t($booster['rank_name']) ?>
                                        <?= t('Booster') ?></small>
                                    <div class="pt-2">
                                        <?php $roles = explode('|', $booster['roles']); ?>
                                        <?php foreach ($roles as $role): ?>
                                            <a class="icon-sm me-1 text-white role-icon">
                                                <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= $role ?>.png"
                                                    alt="<?= $role ?>">
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="pt-2">
                                        <?php
                                        $champions = explode('|', $booster['champions']);

                                        foreach ($champions as $champion): ?>
                                            <img class="champion-icon" src="<?= LOL_CHAMP_URL . '/' . $champion ?>.png"
                                                alt="<?= $champion ?>">
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-12 text-center mt-4">
                <a href="<?= BASE_URL ?>/boosters" class="btn btn-primary"><?= t('View All Boosters') ?></a>
            </div>
        </div>
</section>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script>
    var swiperUnique = new Swiper('.swiper-container-unique', {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: false, // Looping deaktiviert
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        autoplay: {
            delay: 3000, // 2 seconds
        },
        breakpoints: {
            400: {
                slidesPerView: 1,
            },
            650: {
                slidesPerView: 2,
            },
            1000: {
                slidesPerView: 2,
            },
            1200: {
                slidesPerView: 3,
            },
            1400: {
                slidesPerView: 4,
            }
        },
    });
</script>