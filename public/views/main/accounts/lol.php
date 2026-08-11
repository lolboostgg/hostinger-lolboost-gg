<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'Blog.gif']) ?>

<style>
    .be-bg {
        background-color: #DFF6F9 !important;
        width: 66.73px;
        text-align: center;
    }

    .rp-bg {
        background-color: #FFE0DF !important;
        width: 66.73px;
        height: 66.73px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .gradient-text-h2 {
        background: linear-gradient(45deg, #6366f1, #8b5cf6, #d946ef);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .gradient-text-h4 {
        background: linear-gradient(45deg, #6366f1, #8b5cf6, #d946ef);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: bold;
    }
</style>

<section class="pt-4">
    <div class="container px-0 px-md-2">
        <ul class="nav nav-tabs justify-content-center">
            <li class="nav-item">
                <a href="<?= BASE_URL ?>/lol/premium-accounts"
                    class="nav-link justify-content-center <?= $active == 'premium-accounts' ? 'active' : null ?>">
                    <i class="fa-solid fa-user-ninja me-2" style="font-size:30px; width:28px; height:28px;"></i>
                    <span>Smurf Accounts</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= BASE_URL ?>/lol/accounts"
                    class="nav-link justify-content-center <?= $active == 'ranked-accounts' ? 'active' : null ?>">
                    <i class="fa-solid fa-trophy me-2" style="font-size:30px; width:28px; height:28px;"></i>
                    <span>Ranked Accounts</span>
                </a>
            </li>
        </ul>

        <ul class="nav nav-tabs justify-content-center" role="tablist">

            <li class="nav-item">
                <a href="#euw-list" class="nav-link justify-content-center active" data-bs-toggle="tab"
                    role="tab">EUW</a>
            </li>
            <li class="nav-item">
                <a href="#eune-list" class="nav-link justify-content-center" data-bs-toggle="tab" role="tab">EUNE</a>
            </li>
            <li class="nav-item">
                <a href="#na-list" class="nav-link justify-content-center" data-bs-toggle="tab" role="tab">NA</a>
            </li>
        </ul>
        <!-- Pricing: Cards style 1 -->
        <div class="table-responsive-lg">
            <div class="tab-content">
                <?php foreach ($data as $server => $packages): ?>

                    <div class="tab-pane fade <?= ($server == 'euw') ? 'show active' : null ?>" id="<?= $server ?>-list"
                        role="tabpanel">
                        <div class="row g-3 pb-4 justify-content-center mx-auto">
                            <?php foreach ($packages as $package): ?>
                                <?php $package['features'] = explode('|', $package['features']); ?>
                                <div class="col-xl-4 col-lg-4 col-md-6  ">
                                    <div class="card h-100 border p-xxl-3" style="min-width: 18rem;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center pb-2 pb-md-3 mb-4">
                                                <div class="flex-shrink-0 <?= $package['icon'] ?>-bg p-1 rounded-circle"
                                                    style="background-color: #6366f150 !important;">
                                                    <img src="<?= ASSET_URL ?>/core/main/img/lol/ranks/mini/<?= $package['rank'] ?>.png"
                                                        style="width: 40px;" alt="Icon">
                                                </div>
                                                <div class="ps-4">
                                                    <h3 class="fs-lg fw-normal text mb-2"><?= $package['name'] ?></h3>
                                                    <?php if ($_SESSION['currency'] == 'USD'): ?>
                                                        <h4 class="h3 lh-1 mb">
                                                            $<?= round(util_format_price_display($package['price']) * get_exchange_rate(), 2) ?>
                                                        </h4>
                                                    <?php else: ?>
                                                        <h4 class="h3 lh-1 mb-0">
                                                            €<?= util_format_price_display($package['price']) ?></h4>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled">
                                                <?php foreach ($package['features'] as $feature): ?>
                                                    <li class="d-flex mb-2 fw-500 align-items-center">
                                                        <i class="fa-duotone fa-circle-check text-primary fs-xl me-2"></i>
                                                        <span><?= $feature ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div class="card-footer border-0 pt-0 pb-4">
                                            <?php if ($package['available'] == 0): ?>
                                                <button class="btn btn-primary w-100 disabled">Sold Out</button>
                                            <?php else: ?>
                                                <form class="ajax-form" action="<?= AJAX_URL ?>">
                                                    <input type="hidden" name="action" value="prepare_account_purchase">
                                                    <input type="hidden" name="id" value="<?= $package['id'] ?>">
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <span class="indicator-label">Buy Now</span>
                                                        <span class="indicator-progress">
                                                            <span class="spinner-border spinner-border-sm align-middle"></span>
                                                        </span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
</section>
<div class="py-5"></div>

<?= $this->insert('main/components/faq/lol/' . $faq) ?>

<div class="mb-8"></div> <!-- Spacer -->
<div class="container my-5 bg-secondary">
    <div class="row">
        <div class="col">
            <h4 class="gradient-text-h4">About Us</h4>
            <p style="text-align: left;"> Unlock the next level of your League of Legends journey with a unique
                opportunity just a click away! At <b>LoLBoostGG</b>, we've curated a selection of premium accounts that
                are your golden ticket to soaring through the ranks with ease and style.</p>
            <p style="text-align: left;">Imagine stepping into the arena, equipped with a powerhouse account that
                reflects your true gaming spirit and ambition. That's what we offer on our premium accounts page; a
                chance to Boost My League of Legends Account and embrace the game like never before.</p>
            <p style="text-align: left;">Be sure that our highly specialised team will provide an unmatched experience.
                Precision, competence, and a profound understanding of game dynamics assure your successful and
                enriching rise. Learn and excel from the best.</p>
        </div>
    </div>
</div>

<div class="container my-5 bg-secondary">
    <div class="row">
        <div class="col">
            <h4 class="gradient-text-h4">Have you ever thought, "I wish I could Boost My Lol Account effortlessly"?</h4>
            <p style="text-align: left;">Well, your wish is our command. Our premium accounts are handpicked, ensuring
                you get access to an experience that's not just about higher ranks but also about diving deep into the
                more prosperous, more competitive aspects of League of Legends.</p>
            <p style="text-align: left;">This isn't just a shortcut; it is a soar right into a realm where your
                abilities can shine, supported via an account that mirrors your passion for the game. </p>
            <p style="text-align: left;">With <b>LoLBoostGG</b>, you're not just getting an account; you're unlocking a
                treasure chest of possibilities, where every match is a step closer to the pinnacle of League of Legends
                glory.</p>
            <p style="text-align: left;"> Dive into our collection and select the key to your future triumphs. Don't let
                the grind hold you back any longer. It's time to elevate your recreation, show off your natural ability,
                and dominate the battlefield with self-belief and delight. Your premium League of Legends account awaits
                you; grab it now and transform your gaming journey into an epic saga of victory and courage!</p>
        </div>
    </div>
</div>

<div class="py-3"></div>

<?php
$uri = $_SERVER['REQUEST_URI'];
if ($uri == "/lol/premium-accounts") {
    ?>


    <?php
}
?>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/vanilla-tilt/dist/vanilla-tilt.min.js"></script>
<?= $this->stop() ?>


<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<?php if ($_SERVER['REQUEST_URI'] === '/lol/premium-accounts'): ?>
    <link rel="canonical" href="https://lolboost.gg/lol/premium-accounts" />
<?php endif; ?>
<?= $this->stop() ?>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-65TVKVNEEW"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-65TVKVNEEW');
</script>