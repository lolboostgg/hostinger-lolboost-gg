<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'Blog.gif']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/main/vendor/lightgallery/css/lightgallery-bundle.min.css" />
<style>
    [data-user-theme="blue"] .badge {
        border-color: var(--blue-border-primary) !important;
    }

    [data-user-theme="blue"] .bg-light {
        background-color: var(--blue-bg-accent) !important;
        color: var(--blue-text-primary) !important;
    }

    [data-user-theme="blue"] .rank-icon {
        background-color: var(--blue-bg-accent) !important;
    }

    [data-user-theme="blue"] #champions::-webkit-scrollbar-track,
    [data-user-theme="blue"] #skins::-webkit-scrollbar-track {
        background: var(--blue-bg-accent);
    }

    [data-user-theme="blue"] #champions::-webkit-scrollbar-thumb,
    [data-user-theme="blue"] #skins::-webkit-scrollbar-thumb {
        background: var(--blue-bg-primary);
    }

    [data-user-theme="dark"] .badge {
        border-color: var(--dark-border-primary) !important;
    }

    [data-user-theme="dark"] .bg-light {
        background-color: var(--dark-bg-accent) !important;
        color: var(--dark-text-primary) !important;
    }

    [data-user-theme="dark"] .rank-icon {
        background-color: var(--dark-bg-accent) !important;
    }

    [data-user-theme="dark"] #champions::-webkit-scrollbar-track,
    [data-user-theme="dark"] #skins::-webkit-scrollbar-track {
        background: var(--dark-bg-accent);
    }

    [data-user-theme="dark"] #champions::-webkit-scrollbar-thumb,
    [data-user-theme="dark"] #skins::-webkit-scrollbar-thumb {
        background: var(--dark-bg-primary);
    }

    .rank-icon {
        width: 50px;
        height: 50px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        background-color: #f3f4f6;
        border-radius: .5rem;
        padding: 10px;
    }

    .price-eur {
        font-size: 1.8rem;
        line-height: 2rem;
    }

    .gradient-text {
        background: linear-gradient(45deg, #6366f1, #8b5cf6, #d946ef);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    #champions,
    #skins {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px;
    }

    #champions::-webkit-scrollbar,
    #skins::-webkit-scrollbar {
        width: 8px;
    }

    #champions::-webkit-scrollbar-track,
    #skins::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 10px;
    }

    #champions::-webkit-scrollbar-thumb,
    #skins::-webkit-scrollbar-thumb {
        background: #a1a1aa;
        border-radius: 10px;
    }

    .lg-outer {
        z-index: 999999999999999999999999999999999999999;
        background: #000000;
    }

    @media (max-width: 768px) {
        .fixed-bottom {
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .fixed-bottom.hidden {
            transform: translateY(100%);
        }

        /* Adjust padding to account for sticky button */
        body {
            padding-bottom: 80px;
        }

        .sticky-buy-container {
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform: translateY(100%);
            opacity: 0;
            pointer-events: none;
            /* Prevent clicks while hidden */
        }

        .sticky-buy-container.visible {
            transform: translateY(0%);
            opacity: 1;
            pointer-events: auto;
        }
    }
</style>
<?= $this->stop() ?>

<div class="container-fluid my-3 my-md-5">
    <div class="row p-3 px-md-5 py-md-0">
        <div class="col-12 col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rank-icon">
                    <img src="<?= util_rank_img('lol', 'mini', $account['current_rank']) ?>">
                </div>
                <h5 class="mb-0">
                    <?= $account['title'] ?>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
                <span class="badge bg-primary bg-opacity-25 border text-primary rounded-pill py-2 px-3">
                    <i class="fas fa-bolt"></i></i>
                    Instant Account Delivery
                </span>
                <span class="badge bg-primary bg-opacity-25 border text-primary rounded-pill py-2 px-3">
                    <i class="fas fa-shield-alt"></i>
                    Free Warranty Support
                </span>
            </div>
        </div>
    </div>
    <div class="row p-3 px-md-5 py-md-0">
        <div class="col-12 col-md-8">
            <div class="card shadow-none d-md-none">
                <div
                    class="card-header w-100 px-4 bg-secondary shadow-none d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-images me-2"></i>
                        Gallery
                    </h6>

                    <div class="controls">
                        <button type="button" id="prev"
                            class="btn btn-outline-primary btn-icon btn-sm d-none d-md-inline-flex"
                            aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" id="next"
                            class="btn btn-outline-primary  btn-icon btn-sm d-none d-md-inline-flex" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div class="swiper pt-1" data-swiper-options='{
                                "slidesPerView": 1,
                                "spaceBetween": 20,
                                "loop": true,
                                "pagination": {
                                "el": ".swiper-pagination",
                                "clickable": true
                                },
                                "navigation": {
                                "prevEl": "#prev",
                                "nextEl": "#next"
                                }
                            }'>
                            <div class="swiper-wrapper gallery" data-video="true">
                                <?php foreach (json_decode($account['images']) as $image): ?>
                                    <div class="swiper-slide">
                                        <a href="<?= $image ?>" class="gallery-item rounded-3">
                                            <img src="<?= $image ?>">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination position-relative pt-3 pt-sm-4 mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 shadow-none">
                <div class="card-header w-100 px-4 bg-secondary shadow-none">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Account Details
                    </h6>
                </div>
                <div class="card-body">
                    <?= nl2br($account['description']) ?>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <h6 class="mb-2">
                                Current Rank
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= util_rank_img('lol', 'mini', $account['current_rank']) ?>"
                                    class="img-fluid" style="width: 25px; height: auto;">
                                <?=
                                    util_get_lol_rank($account['current_rank']) . ' ' .
                                    ($account['current_lp'] !== null && $account['current_lp'] != 0
                                        ? $account['current_lp'] . 'LP'
                                        : util_format_lol_division($account['current_division'])
                                    )
                                    ?>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <h6 class="mb-2">
                                Flex Rank
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= util_rank_img('lol', 'mini', $account['flex_rank']) ?>" class="img-fluid"
                                    style="width: 25px; height: auto;">
                                <?=
                                    util_get_lol_rank($account['flex_rank']) . ' ' .
                                    ($account['flex_lp'] !== null && $account['flex_lp'] != 0
                                        ? $account['flex_lp'] . 'LP'
                                        : util_format_lol_division($account['flex_division'])
                                    )
                                    ?>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6 class="mb-2">
                                Previous Rank
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= util_rank_img('lol', 'mini', $account['previous_rank']) ?>"
                                    class="img-fluid" style="width: 25px; height: auto;">
                                <?=
                                    util_get_lol_rank($account['previous_rank']) . ' ' .
                                    ($account['previous_lp'] !== null && $account['previous_lp'] != 0
                                        ? $account['previous_lp'] . 'LP'
                                        : util_format_lol_division($account['previous_division'])
                                    )
                                    ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <h6 class="mb-2">
                                Server
                            </h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-globe"></i>
                                <?= util_format_server($account['server']) ?>
                            </span>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <h6 class="mb-2">
                                Level
                            </h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-arrow-turn-up"></i> <?= $account['level'] ?>
                            </span>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6 class="mb-2">
                                Blue Essence
                            </h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-gem"></i> <?= $account['blue_essence'] ?>
                            </span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <h6 class="mb-2">
                                Riot Points
                            </h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-hand-back-fist"></i>
                                <?= $account['riot_points'] ?>
                            </span>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <h6 class="mb-2">
                                Champions
                            </h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-helmet-battle"></i>
                                <?= count(explode('|', $account['champions'])) ?>
                            </span>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6 class="mb-2">
                                Skins
                            </h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-masks-theater"></i>
                                <?= count(explode('|', $account['skins'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 shadow-none">
                <div class="card-header w-100 px-4 bg-secondary shadow-none">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a href="#champions" class="nav-link active" data-bs-toggle="tab" role="tab"
                                aria-controls="champions" aria-selected="true">
                                <i class="fas fa-helmet-battle me-1"></i>
                                Champions
                                <span class="badge bg-light ms-2">
                                    <?= count(explode('|', $account['champions'])) ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#skins" class="nav-link" data-bs-toggle="tab" role="tab" aria-controls="skins"
                                aria-selected="false">
                                <i class="fas fa-masks-theater me-1"></i>
                                Skins
                                <span class="badge bg-light ms-2">
                                    <?= count(explode('|', $account['skins'])) ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#roles" class="nav-link" data-bs-toggle="tab" role="tab" aria-controls="roles"
                                aria-selected="false">
                                <i class="fas fa-users me-1"></i>
                                Roles
                                <span class="badge bg-light ms-2">
                                    <?= count(explode('|', $account['roles'])) ?>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="champions" role="tabpanel">
                            <div class="d-grid gap-3"
                                style="grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));">
                                <?php foreach (explode('|', $account['champions']) as $champion): ?>
                                    <div>
                                        <img src="<?= LOL_CHAMP_URL ?>/<?= $champion ?>.png" class="img-fluid rounded">
                                        <small class="mt-2"><?= ucfirst($champion) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="skins" role="tabpanel">
                            <div class="d-grid gap-3"
                                style="grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));">
                                <?php foreach (explode('|', $account['skins']) as $skin): ?>
                                    <div>
                                        <img src="<?= 'https://ddragon.leagueoflegends.com/cdn/img/champion/loading/' . $skin . '.jpg' ?>"
                                            class="img-fluid rounded" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="<?= util_get_skin_label($skin) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="roles" role="tabpanel">
                            <div class="d-flex text-center">
                                <?php foreach (explode('|', $account['roles']) as $role): ?>
                                    <div>
                                        <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= ucfirst($role) ?>.svg"
                                            class="img-fluid w-100 me-5" style="height: 4rem; width: 100%;">
                                        <p class="mt-2"><?= ucfirst($role) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card mt-4 shadow-none checkout-card">
                <div class="card-header w-100 px-4 bg-secondary shadow-none">
                    <h6 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Checkout
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($account['delivery_type'] == 'instant'): ?>
                        <p>
                            Right after checkout, your account details will be delivered. No waiting, no stress.
                        </p>
                    <?php else: ?>
                        <p>
                            Your account will be delivered manually by our team. You can claim it instantly via Live Chat or
                            receive the login details by email within 1 hour after your purchase.
                        </p>
                    <?php endif; ?>

                    <ul class="list-unstyled ms-3">
                        <?php if ($account['delivery_type'] == 'instant'): ?>
                            <li>
                                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                                Ready to play in seconds
                            </li>
                            <li>
                                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                                Full access (email & password changeable)
                            </li>
                            <li>
                                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                                Free warranty and support
                            </li>
                        <?php else: ?>
                            <li>
                                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                                Secure manual delivery process
                            </li>
                            <li>
                                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                                Claim via Live Chat for fastest access
                            </li>
                            <li>
                                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                                Login details also sent to your email within 60 minutes
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="d-flex align-items-end gap-2">
                        <span
                            class="fw-bold price-eur gradient-text">€<?= util_format_price_display($account['price']) ?></span>
                        <small class="text-dark fw-medium">EUR</small>
                    </div>

                    <form action="<?= AJAX_URL ?>" class="ajax-form">
                        <input type="hidden" name="action" value="prepare_lol_account_order">
                        <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Buy Account Now
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-4 shadow-none d-none d-md-block">
                <div
                    class="card-header w-100 px-4 bg-secondary shadow-none d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-images me-2"></i>
                        Gallery
                    </h6>

                    <div class="controls">
                        <button type="button" id="prev"
                            class="btn btn-outline-primary btn-icon btn-sm d-none d-md-inline-flex"
                            aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" id="next"
                            class="btn btn-outline-primary  btn-icon btn-sm d-none d-md-inline-flex" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div class="swiper pt-1" data-swiper-options='{
                                "slidesPerView": 1,
                                "spaceBetween": 20,
                                "loop": true,
                                "pagination": {
                                "el": ".swiper-pagination",
                                "clickable": true
                                },
                                "navigation": {
                                "prevEl": "#prev",
                                "nextEl": "#next"
                                }
                            }'>
                            <div class="swiper-wrapper gallery" data-video="true">
                                <?php foreach (json_decode($account['images']) as $image): ?>
                                    <div class="swiper-slide">
                                        <a href="<?= $image ?>" class="gallery-item rounded-3">
                                            <img src="<?= $image ?>">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination position-relative pt-3 pt-sm-4 mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/lightgallery/lightgallery.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/lightgallery/plugins/zoom/lg-zoom.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/lightgallery/plugins/fullscreen/lg-fullscreen.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/lightgallery/plugins/video/lg-video.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/lightgallery//plugins/thumbnail/lg-thumbnail.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.innerWidth <= 768) {
            const checkoutCard = document.querySelector('.checkout-card');

            // Create and insert the sticky buy button
            const stickyBuyContainer = document.createElement('div');
            stickyBuyContainer.className = 'fixed-bottom d-md-none sticky-buy-container'; // Start hidden

            stickyBuyContainer.innerHTML = `
            <div class="d-flex align-items-center justify-content-between p-3 bg-dark" style="z-index: 1000;">
                <div class="d-flex align-items-end gap-2">
                    <span class="fw-bold price-eur gradient-text">€<?= util_format_price_display($account['price']) ?></span>
                    <small class="text-light fw-medium">EUR</small>
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Buy Now
                </button>
            </div>
        `;

            document.body.appendChild(stickyBuyContainer);

            const stickyElement = stickyBuyContainer;
            const buyButton = stickyElement.querySelector('button');

            // Scroll to checkout card
            buyButton.addEventListener('click', function () {
                checkoutCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            // IntersectionObserver logic
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            stickyElement.classList.remove('visible');
                        } else {
                            stickyElement.classList.add('visible');
                        }
                    });
                }, {
                    root: null,
                    threshold: 0.1
                });

                observer.observe(checkoutCard);
            } else {
                // Fallback for old browsers
                function isInViewport(el) {
                    const rect = el.getBoundingClientRect();
                    return (
                        rect.top <= window.innerHeight &&
                        rect.bottom >= 0
                    );
                }

                function handleScroll() {
                    if (isInViewport(checkoutCard)) {
                        stickyElement.classList.add('d-none');
                    } else {
                        stickyElement.classList.remove('d-none');
                    }
                }

                window.addEventListener('scroll', handleScroll);
                handleScroll();
            }
        }
    });
</script>
<?= $this->stop() ?>