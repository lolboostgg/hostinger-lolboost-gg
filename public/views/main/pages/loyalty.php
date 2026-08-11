<?= $this->layout('main/layouts/main', ['meta' => $meta]); ?>

<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'lol.gif']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<style>
    [data-user-theme="blue"] .loyalty-table {
        background-color: var(--blue-bg-secondary);
        border-color: var(--blue-border-primary);
    }

    [data-user-theme='blue'] .nav-tabs .nav-link {
        color: var(--blue-text-primary);
        background-color: var(--blue-bg-accent) !important;
    }

    [data-user-theme="dark"] .loyalty-table {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border-primary);
    }

    [data-user-theme='dark'] .nav-tabs .nav-link {
        color: var(--dark-text-primary);
        background-color: var(--dark-bg-accent) !important;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
    }

    .rank-icon {
        max-width: fit-content;
        width: 100%;
        height: auto;

        filter: contrast(120%) brightness(95%);
        image-rendering: crisp-edges;
    }

    .text-white {
        font-size: 1.1rem !important;

        gap: 0.5rem;
        display: flex;
        align-items: center;
    }

    .spent {
        background-color: #7a959c;
        color: #fff;
        font-size: 16px;
        padding: 0.7rem 1.7rem;
        border-radius: 20px;
        font-weight: 600;
        margin-bottom: 2.5rem;
    }

    .loyalty-table {
        font-size: 0.8rem;
        background-color: #f3f6ff;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #d1d1d1;
    }

    .loyalty-table th {
        font-weight: 600;
        padding: 1rem 1.5rem !important;
    }

    .loyalty-table th div {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .loyalty-table th div h5 {
        font-size: 0.9rem;
        margin: 0;
    }

    .loyalty-table th div img {
        max-width: 100%;
        width: auto;
        height: 35px;
    }

    .loyalty-table th:nth-child(2) div img {
        width: 15px;
    }

    .loyalty-table th:nth-child(1) div {
        justify-content: flex-start;
        align-items: flex-start;
    }

    .loyalty-table th div h5 {
        margin-top: 0.5rem;
    }

    .loyalty-table tbody tr td {
        font-weight: 600;
        padding: 1rem 1.5rem !important;
        vertical-align: middle;
        text-align: center;
    }

    .loyalty-table tbody tr td:nth-child(1) div {
        justify-content: flex-start;
    }

    .loyalty-table tbody tr td:nth-child(1) div img {
        width: 30px;
        display: block;
        margin-right: 5px;
    }

    .loyalty-table tbody tr td div {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loyalty-table tbody tr td h5 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 500;
        text-align: center;
    }

    .loyalty-table tbody tr td:nth-child(1) div i {
        font-size: 20px;
        width: 30px;
        margin-right: 5px;
    }

    .custom-witdh {
        max-width: 60%;
    }

    .table:not(.table-dark) thead:not(.thead-dark) th,
    .table:not(.table-dark) tbody th {
        color: unset !important;
    }

    @media (max-width: 768px) {
        .custom-witdh {
            max-width: 100%;
        }
    }
</style>
<?= $this->end('styles') ?>

<section class="container-fluid mx-auto my-5">
    <div class="row mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-center loyalty-slider">
                <div class="me-1 position-relative">
                    <img src="<?= ASSET_URL ?>/core/main/img/loyalty/silver_bg.png" class="img-fluid">
                    <div class="overlay">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/silver_icon.svg" class="rank-icon">
                        <p class="text-white text-center mt-3">
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/silver_dot.png" class="dot">
                            Silver
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/silver_dot.png" class="dot">
                        </p>

                        <span class="spent">
                            €<?= number_format(get_loyalty_target_price(1), 0) ?> Spent
                        </span>
                    </div>
                </div>
                <div class="me-1 position-relative">
                    <img src="<?= ASSET_URL ?>/core/main/img/loyalty/gold_bg.png" class="img-fluid">
                    <div class="overlay">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/gold_icon.svg" class="rank-icon">
                        <p class="text-white text-center mt-3">
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/gold_dot.png" class="dot">
                            Gold
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/gold_dot.png" class="dot">
                        </p>

                        <span class="spent" style="background-color: #f29421;">
                            €<?= number_format(get_loyalty_target_price(2), 0) ?> Spent
                        </span>
                    </div>
                </div>
                <div class="me-1 position-relative">
                    <img src="<?= ASSET_URL ?>/core/main/img/loyalty/platinum_bg.png" class="img-fluid">
                    <div class="overlay">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/platinum_icon.svg" class="rank-icon">
                        <p class="text-white text-center mt-3">
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/platinum_dot.png" class="dot">
                            Platinum
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/platinum_dot.png" class="dot">
                        </p>

                        <span class="spent" style="background-color: #10aacb;">
                            €<?= number_format(get_loyalty_target_price(3), 0) ?> Spent
                        </span>
                    </div>
                </div>
                <div class="me-1 position-relative">
                    <img src="<?= ASSET_URL ?>/core/main/img/loyalty/diamond_bg.png" class="img-fluid">
                    <div class="overlay">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/diamond_icon.svg" class="rank-icon">
                        <p class="text-white text-center mt-3">
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/diamond_dot.png" class="dot">
                            Diamond
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/diamond_dot.png" class="dot">
                        </p>

                        <span class="spent" style="background-color: #595cf2;">
                            €<?= number_format(get_loyalty_target_price(4), 0) ?> Spent
                        </span>
                    </div>
                </div>
                <div class="me-1 position-relative">
                    <img src="<?= ASSET_URL ?>/core/main/img/loyalty/master_bg.png" class="img-fluid">
                    <div class="overlay">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/master_icon.svg" class="rank-icon">
                        <p class="text-white text-center mt-3">
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/master_dot.png" class="dot">
                            Master
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/master_dot.png" class="dot">
                        </p>

                        <span class="spent" style="background-color: #9c3ae7;">
                            €<?= number_format(get_loyalty_target_price(5), 0) ?> Spent
                        </span>
                    </div>
                </div>
                <div class="me-1 position-relative">
                    <img src="<?= ASSET_URL ?>/core/main/img/loyalty/grandmaster_bg.png" class="img-fluid">
                    <div class="overlay">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/grandmaster_icon.svg" class="rank-icon">
                        <p class="text-white text-center mt-3">
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/grandmaster_dot.png" class="dot">
                            Grandmaster
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/grandmaster_dot.png" class="dot">
                        </p>

                        <span class="spent" style="background-color: #ed1c1c;">
                            €<?= number_format(get_loyalty_target_price(6), 0) ?> Spent
                        </span>
                    </div>
                </div>
                <div class="me-1 position-relative">
                    <img src="<?= ASSET_URL ?>/core/main/img/loyalty/challenger_bg.png" class="img-fluid">
                    <div class="overlay">
                        <img src="<?= ASSET_URL ?>/core/main/img/loyalty/challenger_icon.svg" class="rank-icon">
                        <p class="text-white text-center mt-3">
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/challenger_dot.png" class="dot">
                            Challenger
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/challenger_dot.png" class="dot">
                        </p>

                        <span class="spent" style="background-color: #f2cc6d;">
                            €<?= number_format(get_loyalty_target_price(7), 0) ?> Spent
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container my-5">
    <h2 class="text-center mb-2 text-gradient-primary">
        Loyalty System Overview
    </h2>
    <h4 class="text-center mb-2">
        Earn More with Every Purchase on lolboost.gg!
    </h4>
    <h6 class="text-center w-75 mx-auto mb-5">
        Discover the benefits of our unique Loyalty System — cashback, exclusive rewards, and premium perks tailored
        just for you.
    </h6>

    <div class="table-responsive">
        <table class="table table-bordered loyalty-table">
            <thead>
                <tr>
                    <th>
                        <div>
                            <i class="fa-duotone fa-chart-line fa-2x text-primary"></i>
                            <h5>Loyalty Level</h5>
                        </div>
                    </th>
                    <th>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/silver_icon.svg">
                            <h5 class="text-center">Silver</h5>
                        </div>
                    </th>
                    <th>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/gold_icon.svg">
                            <h5 class="text-center">Gold</h5>
                        </div>
                    </th>
                    <th>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/platinum_icon.svg">
                            <h5 class="text-center">Platinum</h5>
                        </div>
                    </th>
                    <th>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/diamond_icon.svg">
                            <h5 class="text-center">Diamond</h5>
                        </div>
                    </th>
                    <th>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/master_icon.svg">
                            <h5 class="text-center">Master</h5>
                        </div>
                    </th>
                    <th>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/grandmaster_icon.svg">
                            <h5 class="text-center">Grandmaster</h5>
                        </div>
                    </th>
                    <th>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/loyalty/challenger_icon.svg">
                            <h5 class="text-center">Challenger</h5>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div>
                            <img src="<?= ASSET_URL ?>/core/main/img/coin.png">
                            <h5>Cashback</h5>
                        </div>
                    </td>
                    <td>
                        <h5>2%</h5>
                    </td>
                    <td>
                        <h5>3%</h5>
                    </td>
                    <td>
                        <h5>4%</h5>
                    </td>
                    <td>
                        <h5>5%</h5>
                    </td>
                    <td>
                        <h5>6%</h5>
                    </td>
                    <td>
                        <h5>7%</h5>
                    </td>
                    <td>
                        <h5>8%</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>
                            <i class="fa-duotone fa-gift text-primary"></i>
                            <h5>Community Giveaways</h5>
                        </div>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>
                            <i class="fa-duotone fa-gifts text-primary"></i>
                            <h5>Exclusive Giveaways</h5>
                        </div>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>
                            <i class="fa-duotone fa-headset text-primary"></i>
                            <h5>Priority Support</h5>
                        </div>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>
                            <i class="fa-duotone fa-light fa-bolt-lightning text-primary"></i>
                            <h5>Free Priority Option</h5>
                        </div>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>
                            <i class="fa-duotone fa-solid fa-popcorn text-primary"></i>
                            <h5>Free Streaming Option</h5>
                        </div>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fas fa-x text-danger"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                    <td>
                        <i class="fa-duotone fa-check text-primary"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="bg-dark py-5 bg-size-cover bg-repeat-0 bg-position-center skew-scroll"
    style="background-image: url(/public/assets/core/main/img/banners/Booster.gif)">
    <div class="container text-center py-3 py-md-4 py-lg-5">
        <h2 class="h1 mb-4 text-light">
            Unlock Exclusive Rewards! 🎉
        </h2>
        <p class="lead mb-3 fw-500 text-muted">
            Redeem your points in the Points Store for valuable rewards—from in-game items to unique experiences.
        </p>
        <p class="mb-4 text-muted">
            Earn points through our services and explore a wide range of perks. Don't let your points go to waste—claim
            your rewards today! 🚀
        </p>
        <a href="/points-store" class="btn btn-primary obj-rotate-anim shadow-primary btn-lg">
            Explore the Points Store Now
        </a>
    </div>
</section>

<section style="width: 100%; overflow-x: hidden;">
    <div class="bg-secondary rounded-3 py-5 px-3 px-sm-4 px-md-0">
        <h2 class="text-center pt-md-1 pb-2 mt-xl-1">
            Frequently Asked Questions 🤔
        </h2>
        <h5 class="text-center mx-auto mb-5 custom-witdh">
            Quick answers to the most common questions you have about our loyalty program. Can't find what you're
            looking for? <a href="/contact" class="text-primary">Contact us!</a>
        </h5>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <ul class="nav nav-tabs justify-content-center align-items-stretch" style="justify-self: center;">
                    <li class="nav-item">
                        <button class="nav-link active" id="cashback-tab" data-bs-toggle="tab"
                            data-bs-target="#cashback" type="button" role="tab" aria-controls="cashback"
                            aria-selected="true">
                            <i class="fa-duotone fa-hand-holding-dollar me-2 fs-5"></i>
                            Cashback
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="loyalty-tab" data-bs-toggle="tab" data-bs-target="#loyalty"
                            type="button" role="tab" aria-controls="loyalty" aria-selected="false">
                            <i class="fa-duotone fa-medal me-2 fs-5"></i>
                            Loyalty
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="store-tab" data-bs-toggle="tab" data-bs-target="#store"
                            type="button" role="tab" aria-controls="store" aria-selected="false">
                            <img src="<?= ASSET_URL ?>/core/main/img/coin.png" class="me-2 fs-5" style="width: 30px;">
                            LB Coins Store
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row justify-content-center pb-lg-4 pb-xl-5">
            <div class="col-xl-8 col-lg-9 col-md-10 pb-md-2">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="cashback" role="tabpanel" aria-labelledby="cashback-tab">
                        <div class="accordion" id="faq">

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-1" aria-expanded="true"
                                        aria-controls="q-1">What is the lolboost.gg Cashback System?</button>
                                </h3>
                                <div class="accordion-collapse collapse show" id="q-1" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>Our Cashback System rewards you with LB Coins for every purchase you make on
                                            our website. These coins can be used as payment or exchanged for exclusive
                                            items and vouchers in our LB Coins Store.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-2" aria-expanded="false"
                                        aria-controls="q-2">How many LB Coins do I earn per purchase?</button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-2" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>The amount of LB Coins you earn depends on your loyalty level:
                                        <ul>
                                            <li>Silver: 2%</li>
                                            <li>Gold: 3%</li>
                                            <li>Platinum: 4%</li>
                                            <li>Diamond: 5%</li>
                                            <li>Master: 6%</li>
                                            <li>Grandmaster: 7%</li>
                                            <li>Challenger: 8%</li>
                                        </ul>
                                        Example: If you spend €10 on a boost and have Gold Loyalty, you’ll receive 0.3
                                        LB Coins.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-3" aria-expanded="false"
                                        aria-controls="q-3">What is the value of LB Coins?</button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-3" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>LB Coins are directly equivalent to euros:
                                        <ul>
                                            <li>1 LB Coin = €1.00</li>
                                            <li>10 LB Coins = €10.00</li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-4" aria-expanded="false"
                                        aria-controls="q-4">How can I use my LB Coins?</button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-4" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>You can:
                                        <ul>
                                            <li>Apply them directly to your next purchase.</li>
                                            <li>Exchange them in the LB Coins Store for rewards like gaming accessories
                                                or vouchers (e.g., Amazon or Steam).</li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-5" aria-expanded="false"
                                        aria-controls="q-5">Do my LB Coins expire?
                                    </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-5" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>
                                            No, your LB Coins do not expire. They remain in your account until you
                                            decide to use them.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-6" aria-expanded="false"
                                        aria-controls="q-6">How do I reach a higher loyalty level?</button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-6" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>Your loyalty level is determined by your total spending on lolboost.gg. The
                                            more you spend, the higher your loyalty level, unlocking greater cashback
                                            percentages!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="loyalty" role="tabpanel" aria-labelledby="loyalty-tab">
                        <div class="accordion">
                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-7" aria-expanded="true"
                                        aria-controls="q-7">What is the lolboost.gg Loyalty System?</button>
                                </h3>
                                <div class="accordion-collapse collapse show" id="q-7" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>The Loyalty System rewards loyal customers by granting higher cashback
                                            percentages and exclusive perks as they progress through different loyalty
                                            levels. The more you spend, the higher your level, and the greater your
                                            benefits!
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-8" aria-expanded="false"
                                        aria-controls="q-8">What are the loyalty levels and their cashback rates?
                                    </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-8" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>The loyalty levels and corresponding cashback rates are:
                                        <ul>
                                            <li><strong>Silver:</strong> 2%</li>
                                            <li><strong>Gold:</strong> 3%</li>
                                            <li><strong>Platinum:</strong> 4%</li>
                                            <li><strong>Diamond:</strong> 5%</li>
                                            <li><strong>Master:</strong> 6%</li>
                                            <li><strong>Grandmaster:</strong> 7%</li>
                                            <li><strong>Challenger:</strong> 8%</li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-9" aria-expanded="false"
                                        aria-controls="q-9">What additional rewards do I get with higher loyalty
                                        levels?</button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-9" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>Each loyalty level unlocks unique perks:
                                        <ul>
                                            <li><strong>Silver:</strong> Cashback only (2%)</li>
                                            <li><strong>Gold:</strong> Cashback (3%) and access to occasional giveaways
                                            </li>
                                            <li><strong>Platinum:</strong> Cashback (4%) and Special Giveaways</li>
                                            <li><strong>Diamond:</strong> Cashback (5%) and Premium Support</li>
                                            <li><strong>Master:</strong> Cashback (6%), Premium Support, and Free
                                                Priority Option</li>
                                            <li><strong>Grandmaster:</strong> Cashback (7%), Premium Support, Free
                                                Priority Option, and Free Streaming Option</li>
                                            <li><strong>Challenger:</strong> Cashback (8%) and access to all rewards
                                            </li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-10" aria-expanded="false"
                                        aria-controls="q-10">How do I advance to a higher loyalty level?
                                    </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-10" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>Your loyalty level is determined by your total spending on lolboost.gg. The
                                            more you spend, the higher your level becomes, unlocking greater cashback
                                            rewards and perks.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-11" aria-expanded="false"
                                        aria-controls="q-11">How can I check my current loyalty level and benefits?
                                    </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-11" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>You can view your current loyalty level and all associated benefits in your
                                            customer account dashboard on lolboost.gg.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-12" aria-expanded="false"
                                        aria-controls="q-12">Do I lose my loyalty level if I stop purchasing?
                                    </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-12" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>No, your loyalty level is permanent and does not decrease, even if you take a
                                            break from making purchases.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="store" role="tabpanel" aria-labelledby="store-tab">
                        <div class="accordion">
                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-13" aria-expanded="true"
                                        aria-controls="q-13">What is the LB Coins Store? </button>
                                </h3>
                                <div class="accordion-collapse collapse show" id="q-13" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>The LB Coins Store is a special section on lolboost.gg where you can exchange
                                            your earned LB Coins for exciting rewards, including gaming accessories,
                                            vouchers, unranked smurf accounts, and more.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-14" aria-expanded="false"
                                        aria-controls="q-14">What rewards are available in the LB Coins Store? </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-14" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>Current rewards include:
                                        <ul>
                                            <li><strong>Gaming mousepad (lolboost.gg design):</strong> 60 LB Coins</li>
                                            <li><strong>35€ voucher (Amazon or Steam or which voucher you
                                                    prefer):</strong> 40
                                                LB Coins</li>
                                            <li><strong>lolboost.gg voucher (15€):</strong> 20 LB Coins</li>
                                            <li><strong>Unranked Smurf Account:</strong> 20 LB Coins</li>
                                            <li><strong>Gaming headset:</strong> 100 LB Coins</li>
                                            <li><strong>Mechanical gaming keyboard:</strong> 150 LB Coins</li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-15" aria-expanded="false"
                                        aria-controls="q-15">How do I exchange my LB Coins for rewards? </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-15" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>Log in to your lolboost.gg account, visit the LB Coins Store, select the item
                                            you want, and confirm your exchange. Rewards are processed quickly and
                                            efficiently.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-16" aria-expanded="false"
                                        aria-controls="q-16">Can I combine LB Coins with other payment methods in the
                                        store? </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-16" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>No, you must have enough LB Coins to cover the full cost of the reward you
                                            want to redeem. Partial payments with LB Coins are not supported in the
                                            store.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-17" aria-expanded="false"
                                        aria-controls="q-17">Are there any restrictions on the rewards I can claim?
                                    </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-17" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>There are no restrictions, as long as you have enough LB Coins in your
                                            account. All rewards are available to everyone regardless of loyalty level.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-18" aria-expanded="false"
                                        aria-controls="q-18">How long does it take to receive my rewards? </button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-18" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>
                                        <ul>
                                            <li>Digital rewards (e.g., vouchers, smurf accounts) are delivered instantly
                                                to your email after redemption.</li>
                                            <li>Physical rewards (e.g., gaming hardware) are shipped within 5–7 business
                                                days.</li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="accordion-item border-0 rounded-3 shadow-sm">
                                <h3 class="accordion-header">
                                    <button class="accordion-button shadow-none rounded-3 collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#q-19" aria-expanded="false"
                                        aria-controls="q-19">I’m concerned about sharing my personal data or address.
                                        How can I receive my rewards?</button>
                                </h3>
                                <div class="accordion-collapse collapse" id="q-19" data-bs-parent="#faq">
                                    <div class="accordion-body fs-sm pt-0">
                                        <p>If you prefer not to provide your address, we offer anonymous delivery to a
                                            package shop near you. Simply select this option during checkout, and you’ll
                                            receive the necessary information to pick up your reward.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-dark py-5 bg-size-cover bg-repeat-0 bg-position-center skew-scroll"
    style="background-image: url(/public/assets/core/main/img/banners/Booster.gif)">
    <div class="container text-center py-1 py-md-4 py-lg-5">
        <h2 class="h1 mb-4 text-light">Ready to get your desired rank? 🔥</h2>
        <p class="lead pb-3 mb-3 line-rotate-anim fw-500 text-muted">Stop hesitating and reach your dream rank in a
            nutshell!</p>
        <a href="/lol/rank-boost" class="btn btn-primary obj-rotate-anim shadow-primary btn-lg mb-1">Let's crush it!</a>
    </div>
</section>

<?php $this->start('scripts') ?>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script>
    $(document).ready(function () {
        $('.loyalty-slider').slick({
            dots: false,
            infinite: true,
            speed: 300,
            slidesToShow: 1,
            centerMode: true,
            variableWidth: true,
            arrows: false,
            mobileFirst: true,
            autoplay: true,
            responsive: [{
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    infinite: true,
                    dots: false
                }
            },
            {
                breakpoint: 992,
                settings: 'unslick'
            }]
        });
    });
</script>
<?php $this->end() ?>