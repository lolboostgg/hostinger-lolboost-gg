<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/two') ?>

<?= $this->insert('main/components/features/icon-boxes') ?>

<?= $this->insert('main/components/features/team', ['boosters' => $boosters]) ?>

<?= $this->insert('main/components/features/four') ?>

<?= $this->insert('main/components/features/progress') ?>

<?= $this->insert('main/components/sliders/hiw-1') ?>

<?= $this->insert('main/components/testimonials/one') ?>

<section style="width: 100%; overflow-x: hidden;">
    <div class="bg-secondary rounded-3 py-5 px-3 px-sm-4 px-md-0">
        <h2 class="text-center pt-md-1 pt-lg-3 pt-xl-4 pb-4 mt-xl-1 mb-2">
            <?= t('Frequently Asked Questions - League of Legends Elo Boosting') ?>
        </h2>
        <div class="row justify-content-center pb-lg-4 pb-xl-5">
            <div class="col-xl-8 col-lg-9 col-md-10 pb-md-2">
                <div class="accordion" id="faq">

                    <!-- Item -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header">
                            <button class="accordion-button shadow-none rounded-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#q-1" aria-expanded="true"
                                aria-controls="q-1"><?= t('What is LoL Boosting and how does it work?') ?></button>
                        </h3>
                        <div class="accordion-collapse collapse show" id="q-1" data-bs-parent="#faq">
                            <div class="accordion-body fs-sm pt-0">
                                <p><?= t('LoL Boosting (also known as Elo Boosting) is a service where a high-rank player (booster plays ranked games on your League of Legends account to help you achieve a higher division.') ?>
                                </p>
                                <p><?= t('You can also choose Duo Queue Boosting, where you play alongside one of our professional boosters. After reaching your desired rank, your account is returned safely, with your new League tier unlocked.') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header">
                            <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#q-2" aria-expanded="false"
                                aria-controls="q-2"><?= t('How safe is Elo Boosting for my League of Legends account?') ?></button>
                        </h3>
                        <div class="accordion-collapse collapse" id="q-2" data-bs-parent="#faq">
                            <div class="accordion-body fs-sm pt-0">
                                <p><?= t('Your account safety is our top priority at LolBoost.gg. We use 100% manual boosting, with no bots or scripts involved. For additional security, we offer VPN protection to match your region and keep your account activity consistent. You can also request live streaming of your boost to monitor every step in real time.') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header">
                            <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#q-3" aria-expanded="false"
                                aria-controls="q-3"><?= t('Can I choose my LoL booster?') ?></button>
                        </h3>
                        <div class="accordion-collapse collapse" id="q-3" data-bs-parent="#faq">
                            <div class="accordion-body fs-sm pt-0">
                                <p><?= t('Yes! When purchasing your LoL Elo Boost, you have the option to request a specific booster based on language, rank, or past experience. Many of our returning customers prefer to work with the same booster, and we’ll do our best to make that happen (depending on availability).') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header">
                            <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#q-4" aria-expanded="false"
                                aria-controls="q-4"><?= t('How long does a League of Legends boost take?') ?></button>
                        </h3>
                        <div class="accordion-collapse collapse" id="q-4" data-bs-parent="#faq">
                            <div class="accordion-body fs-sm pt-0">
                                <p><?= t('Most boosting orders begin within a few hours after purchase. The exact duration depends on the number of divisions or ranks you want to climb. Smaller boosts (like Gold to Platinum) are often completed within 24–48 hours. Larger orders may take several days, but you’ll receive constant progress updates through your personal dashboard.') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                        <h3 class="accordion-header">
                            <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#q-5" aria-expanded="false"
                                aria-controls="q-5"><?= t('Why should I buy a LoL Boost from LolBoost.gg?') ?></button>
                        </h3>
                        <div class="accordion-collapse collapse" id="q-5" data-bs-parent="#faq">
                            <div class="accordion-body fs-sm pt-0">
                                <p>
                                    <?= t('LolBoost.gg is one of the most trusted names in League of Legends boosting services. Here’s why thousands of players choose us:') ?>
                                </p>

                                <ul>
                                    <li>
                                        <?= t('Only verified Challenger and Grandmaster boosters') ?>
                                    </li>
                                    <li>
                                        <?= t('Fast and secure Elo Boosting with real-time updates') ?>
                                    </li>
                                    <li>
                                        <?= t('Loyalty program with cashback and exclusive rewards') ?>
                                    </li>
                                    <li>
                                        <?= t('Optional features like priority order, live stream, and premium support') ?>
                                    </li>
                                    <li>
                                        <?= t('24/7 customer support and secure payment methods') ?>
                                    </li>
                                </ul>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Item -->
                    <div class="accordion-item border-0 rounded-3 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#q-6" aria-expanded="false"
                                aria-controls="q-6"><?= t('How are the Premium Accounts leveled?') ?></button>
                        </h3>
                        <div class="accordion-collapse collapse" id="q-6" data-bs-parent="#faq">
                            <div class="accordion-body fs-sm pt-0">
                                <p><?= t('The Premium Accounts are leveled exclusively in the Aram game mode. This means that the normal MMR remains unaffected and you start higher after the ranked placements.') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <h5 class="text-center pt-md-1 pt-lg-3 pt-xl-4 pb-4 mt-xl-1 mb-2">
                <?= t('Boost your rank today and unlock a better gaming experience with LolBoost.gg – the #1 choice for safe Elo boosting in League of Legends.') ?>
            </h5>
        </div>
    </div>
</section>

<?= $this->insert('main/components/cta/two') ?>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script>

</script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/vanilla-tilt/dist/vanilla-tilt.min.js"></script>
<?php if (isset($meta['reset_password'])): ?>
    <script>
        // on document ready open reset_password_md modal
        $(document).ready(function () {
            $('#reset_password_md').modal('show');
        });
    </script>
<?php endif ?>

<?= $this->stop() ?>


<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<?= $this->stop() ?>

<meta name="seobility" content="3d22f1a69adae32e4713530827524bab">
<link rel="canonical" href="https://lolboost.gg/" />

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-65TVKVNEEW"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-65TVKVNEEW');
</script>

<style>
    #banner {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        margin-bottom: 0;
    }

    #banner .alert {
        border: none;
        border-radius: 0;
        padding: 0.75rem 1.25rem;
        margin-bottom: 0;
        text-align: center;
        background: linear-gradient(to right, #6366f1, #8b5cf6, #d946ef);
        color: #fff;
        position: relative;
    }

    #banner .btn-close {
        position: absolute;
        top: 25%;
        transform: translateY(-50%);
        left: 1rem;
        font-size: 2em;
        font-weight: bold;
        color: #fff;
        padding: 0.2rem 0.5rem;
        background: none;
        border: none;
        box-shadow: none;
        cursor: pointer;
    }

    #banner .fresh-deals {
        font-weight: bold;
        text-transform: uppercase;
    }

    #banner .rocket-icon {
        font-weight: bold;
        margin-right: 5px;
        font-size: 1.2em;
    }

    #banner .btn-boost {
        color: #fff;
        font-weight: bold;
        border: 1px solid #fff;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        text-decoration: none;
        white-space: nowrap;
    }

    #banner .btn-boost:hover {
        background-color: #6666f2;
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        #banner .alert {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            /* Text kleiner */
        }

        #banner .fresh-deals {
            font-size: 0.85rem;
            /* "Ranked Accounts" kleiner */
        }

        #banner span {
            font-size: 0.85rem;
            /* "LIVE NOW" kleiner */
        }

        #banner .btn-boost {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        #banner .btn-close {
            font-size: 1.5em;
            /* ❌ kleiner */
        }
    }
</style>
</head>

<body>
    <div id="banner">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" aria-label="Close">X</button>
            <span class="fresh-deals">Ranked Accounts Live</span>
            <a href="https://lolboost.gg/lol/accounts" class="btn-boost">
                <i class="fa-solid fa-helmet-battle rocket-icon"></i> Buy Now
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>