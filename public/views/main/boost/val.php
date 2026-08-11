<style>
    .discount-applied-message {
        background: hsla(142, 71%, 45%, 0.2);
        border-radius: 10px;
        border: 1px solid hsla(142, 71%, 45%, 0.4);
        padding: 5px 10px;
        display: flex;
        align-items: center;
    }

    .discount-applied-message span {
        margin-right: 5px;
        color: #28a745;
        /* Change color to #28a745 */
    }

    .discount-applied-message #remove-discount {
        cursor: pointer;
        color: #28a745;
        /* Change color to #28a745 */
        margin-left: 5px;
    }

    .card-header .d-flex h4 p {
        font-size: 16px;
        font-weight: normal;
    }
</style>

<?php
$ranks = [
    0 => 'Unranked',
    1 => 'Iron',
    2 => 'Bronze',
    3 => 'Silver',
    4 => 'Gold',
    5 => 'Platinum',
    6 => 'Diamond',
    7 => 'Ascendent',
    8 => 'Immortal',
    9 => 'Radiant',
];

?>
<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'Valorant.gif']) ?>

<?= $this->insert('main/components/forms/val/navigation', ['active' => $data['slug']]) ?>

<div class="container-fluid px-md-5">
    <section class="pt-4">
        <form class="boost-form" id="val_boost_form" action="<?= AJAX_URL ?>" autocomplete="off">
            <input type="hidden" name="action" value="get_boost_price">
            <input type="hidden" name="form_id" value="<?= $data['id'] ?>">
            <input type="hidden" name="uuid" value="<?= $data['uuid'] ?>">
            <div class="row">
                <?= $this->insert('main/components/forms/val/' . $data['type'], ['data' => $data, 'ranks' => $ranks]) ?>
            </div>

            <div class="modal fade" id="agents_modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Agents</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-5">
                                <h5 class="text-capitalize"><span
                                        class="badge rounded-pill bg-gradient-primary align-middle"
                                        style="width:28px;height:28px;">1</span> Select your Agents
                                    <?php if ($data['json']['extra']['agents'] == 0): ?>
                                        <span class="badge rounded fw-bold bg-faded-success text-success">FREE</span>
                                    <?php else: ?>
                                        <span
                                            class="badge rounded fw-bold bg-faded-primary text-primary"><?= util_format_price_db($data['json']['extra']['agents']) ?>%</span>
                                    <?php endif; ?>
                                </h5>
                                <select name="agents[]" placeholder="Select an agent..." multiple class="form-select"
                                    id="agents_select">
                                    <?= util_load_agents_select() ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm"
                                data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Save
                                changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>

<?= $this->insert('main/components/faq/two') ?>

<div class="mb-8"></div> <!-- Spacer -->
<div class="container my-5 bg-secondary">
    <div class="row">
        <div class="col">
            <h4 class="gradient-text-h4">About Us</h4>
            <p style="text-align: left;">Embark on an exciting expedition to enhance your League of Legends ranking
                today!</p>
            <p style="text-align: left;">At <b>LoLBoostGG</b>, we're not just about boosting ranks but about unlocking
                your full potential on the battlefield. With our bespoke Lol Boost Rank service, your climb through the
                ranks will be seamless, strategic, and, most importantly, satisfying.</p>
            <p style="text-align: left;">Tired of being stuck in the same rank, feeling as though the limitations of
                solo queuing overshadow your skills? It's a familiar story, and we understand the frustration. That's
                why we've designed our Rank Boost League of Legends service to cater specifically to players like you,
                who are ambitious, skilled, and ready to take on the world.</p>
            <p style="text-align: left;">Rest assured that our highly specialized team will provide an unmatched
                experience. Precision, competence, and a profound understanding of game dynamics assure your successful
                and enriching rise. Learn and excel from the best.</p>
        </div>
    </div>
</div>

<div class="container my-5 bg-secondary">
    <div class="row">
        <div class="col">
            <h4 class="gradient-text-h4">Why Choose Us?</h4>
            <p style="text-align: left;">We believe in more than just rank gains. We empower you, improve your gameplay,
                and make every match afterward a monument to your growth and our quality. Kickstart your League of
                Legends transformation.</p>
            <p style="text-align: left;">Visit us to begin your climb. We can help you conquer new leagues or improve
                your gameplay. Unleash your inner champion with us.</p>
            <p style="text-align: left;">Remember, winning is about the journey, progress, and glory of overcoming your
                battles, not just the rank. Let <b>LoLBoostGG</b> help you on this journey. Join us as we rise in League
                of Legends one victory at a time.</p>
        </div>
    </div>
</div>

<div class="py-3"></div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/vanilla-tilt/dist/vanilla-tilt.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/cjs/tom-select.complete.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<!-- <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet"> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/wnumb/1.2.0/wNumb.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/forms/val.js?v=<?= rand(0, 9340) ?>"></script>
<script>
    new TomSelect("#agents_select", {
        allowEmptyOption: true,
        plugins: {
            remove_button: {
                title: 'Remove this item',
            }
        },
        valueField: "value",
        labelField: "text",
        searchField: "text",

        onInitialize: function () {
            let select = this;
            this.options = {}; document.querySelectorAll("#agents_select option").forEach(function (option) {
                let value = option.value;
                let text = option.textContent;
                let img = option.getAttribute("data-image");
                select.addOption({
                    value: value,
                    text: text,
                    img: img
                });
            });
        },

        render: {
            option: function (data, escape) {
                return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: 30px; height: 30px; border-radius: 5px; margin-right: 8px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
            },
            item: function (data, escape) {
                return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: 20px; height: 20px; border-radius: 5px; margin-right: 5px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
            }
        }
    });

    function updateOldPrice() {
        const totalPriceElement = document.getElementById('total-price');
        const oldPriceElement = document.getElementById('old-price');
        const discountApplied = document.getElementById('discount-applied');

        const stickyOldPrice = document.getElementById('sticky-old-price');
        const stickyTotalPrice = document.getElementById('sticky-total-price');

        if (discountApplied.style.display === 'flex') {
            const discountMessage = document.getElementById('discount-message').textContent;
            const discount = parseInt(discountMessage.split('%')[0]);
            const totalPrice = parseFloat(totalPriceElement.textContent.replace(/[^0-9.-]+/g, ''));
            const oldPrice = (totalPrice / (1 - discount / 100)).toFixed(2);
            const currencySymbol = totalPriceElement.textContent[0];
            oldPriceElement.innerHTML = `<del style="font-size: 14px;">${currencySymbol}${oldPrice}</del>`;
            oldPriceElement.style.display = 'inline';

            stickyOldPrice.innerHTML = `<del style="font-size: 14px;">${currencySymbol}${oldPrice}</del>`;
            stickyOldPrice.style.display = 'inline';

            stickyTotalPrice.textContent = `${currencySymbol}${totalPrice}`;
        } else {
            oldPriceElement.style.display = 'none';
            stickyOldPrice.style.display = 'none';
        }
    }

    function applyDiscount(discount) {
        const totalPriceElement = document.getElementById('total-price');
        const discountText = document.getElementById('discount-text');
        const discountApplied = document.getElementById('discount-applied');
        const discountMessage = document.getElementById('discount-message');
        const oldPriceElement = document.getElementById('old-price');

        const stickyTotalPrice = document.getElementById('sticky-total-price');

        let totalPrice = parseFloat(totalPriceElement.textContent.replace(/[^0-9.-]+/g, ''));
        totalPrice = (totalPrice * (1 - discount / 100)).toFixed(2);
        const currencySymbol = totalPriceElement.textContent[0];
        totalPriceElement.textContent = `${currencySymbol}${totalPrice}`;
        stickyTotalPrice.textContent = `${currencySymbol}${totalPrice}`;
        discountText.style.display = 'none';
        discountMessage.textContent = `${discount}% discount applied successfully 🎉`;
        discountApplied.style.display = 'flex';

        updateOldPrice();
        document.getElementById('remove-discount').addEventListener('click', function () {
            removeDiscount(discount);
        });
    }

    function removeDiscount(discount) {
        const totalPriceElement = document.getElementById('total-price');
        const discountText = document.getElementById('discount-text');
        const discountApplied = document.getElementById('discount-applied');
        const oldPriceElement = document.getElementById('old-price');
        const discountCodeElement = document.getElementById('discount_code');

        const stickyOldPrice = document.getElementById('sticky-old-price');
        const stickyTotalPrice = document.getElementById('sticky-total-price');

        let discountedPrice = parseFloat(totalPriceElement.textContent.replace(/[^0-9.-]+/g, ''));
        let originalPrice = (discountedPrice / (1 - discount / 100)).toFixed(2);
        const currencySymbol = totalPriceElement.textContent[0];
        totalPriceElement.textContent = `${currencySymbol}${originalPrice}`;
        stickyTotalPrice.textContent = `${currencySymbol}${originalPrice}`;
        discountText.style.display = 'block';
        discountApplied.style.display = 'none';
        oldPriceElement.style.display = 'none';
        stickyOldPrice.style.display = 'none';

        discountCodeElement.value = '';
    }

    const totalPriceObserver = new MutationObserver(updateOldPrice);
    const totalPriceElement = document.getElementById('total-price');
    totalPriceObserver.observe(totalPriceElement, { childList: true });

    document.getElementById('discount_code').addEventListener('input', function () {
        const discountCode = document.getElementById('discount_code').value.toLowerCase(); switch (discountCode) {
            case 'boost10':
            case 'trustpilot10':
                applyDiscount(10); break;
            case 'split15':
            case 'back15':
            case 'new15':
                applyDiscount(15); break;
            case 'today14':
            case 'season14':
                applyDiscount(14); break;
            default:
                break;
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const discountCodeElement = document.getElementById('discount_code');
        discountCodeElement.value = 'BOOST10'; applyDiscount(10);
    });

    $(document).ready(function () {
        var $stickySection = $(".sticky-overview");
        var $hideSticky = $("#hide-sticky");

        function checkVisibility() {
            var windowHeight = $(window).height();
            var elementTop = $hideSticky.offset().top;
            var elementBottom = elementTop + $hideSticky.outerHeight();
            var scrollTop = $(window).scrollTop();

            if (scrollTop + windowHeight > elementTop && scrollTop < elementBottom) {
                $stickySection.css({
                    transform: "translateY(100%)",
                    transition: "transform 0.3s ease-in-out"
                });
            } else {
                $stickySection.css({
                    transform: "translateY(0)",
                    transition: "transform 0.3s ease-in-out"
                });
            }
        }

        $(window).on("scroll", checkVisibility);
        checkVisibility();
    });

    $(document).ready(function () {
        $('.nav-slider').slick({
            slidesToShow: 2,
            slidesToScroll: 1,
            autoplay: false,
            infinite: false,
            arrows: true,
            prevArrow: $('#slick-prev'),
            nextArrow: $('#slick-next'),
            dots: false,
            variableWidth: true,
            mobileFirst: true,
            responsive: [
                {
                    breakpoint: 767,
                    settings: "unslick" // ✅ Correct placement
                }
            ]
        });
    });
</script>
<?= $this->stop() ?>


<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<!-- <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script> -->
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

<style>
    [data-user-theme="blue"] input:checked+.toggle-label {
        background-color: var(--blue-bg-primary) !important;
    }

    [data-user-theme="blue"] .slider {
        background-color: var(--blue-bg-accent) !important;
    }

    [data-user-theme="blue"] input:checked+.slider {
        background-color: #6366f1 !important;
    }

    [data-user-theme='blue'] .bg-light {
        background-color: var(--blue-bg-accent) !important;
    }

    [data-user-theme='blue'] .rounded {
        border-color: transparent !important;
    }

    [data-user-theme='blue'] .card-body .rank-sec,
    [data-user-theme='blue'] .completion-time {
        border-bottom: none !important;
    }

    [data-user-theme="blue"] .sticky-overview .rank-sec {
        border-color: #5363a9 !important;
    }

    [data-user-theme="blue"] .nav-link.text-nowrap i {
        color: #fff;
    }

    [data-user-theme="dark"] input:checked+.toggle-label {
        background-color: var(--dark-bg-primary) !important;
    }

    [data-user-theme="dark"] .slider {
        background-color: var(--dark-bg-accent) !important;
    }

    [data-user-theme="dark"] input:checked+.slider {
        background-color: #6366f1 !important;
    }

    [data-user-theme='dark'] .bg-light {
        background-color: var(--dark-bg-accent) !important;
    }

    [data-user-theme='dark'] .rounded {
        border-color: transparent !important;
    }

    [data-user-theme='dark'] .card-body .rank-sec,
    [data-user-theme='dark'] .completion-time {
        border-bottom: none !important;
    }

    [data-user-theme="dark"] .sticky-overview .rank-sec {
        border-color: #4b5066 !important;
    }

    [data-user-theme="dark"] .nav-link.text-nowrap i {
        color: #fff;
    }

    .toggle-group {
        display: flex;
        border-radius: 8px;
        padding: 5px;
        width: 200px;
        position: relative;
    }

    .toggle-group input {
        display: none;
    }

    .toggle-label {
        flex: 1;
        text-align: center;
        padding: 10px;
        color: #bbb;
        font-weight: 500;
        cursor: pointer;
        transition: 0.3s;
    }

    input:checked+.toggle-label {
        background-color: #6366f1;
        color: #fff;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 24px;
    }

    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.3s;
        border-radius: 24px;
    }

    .slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    /* Checked state */
    input:checked+.slider {
        background-color: #6366f1;
    }

    input:checked+.slider:before {
        transform: translateX(16px);
    }

    .rank-icon-mini {
        width: 30px !important;
    }

    .current-summary-rank-name,
    .desired-summary-rank-name,
    .current-summary-lp,
    .desired-summary-lp,
    .overview-count,
    .game-mode {
        font-size: 14px;
    }

    .fa-duotone.fa-solid.fa-arrow-right {
        margin-top: 4px;
    }

    #slick-prev,
    #slick-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        background: #fff;
        border: none;
        color: #fff;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 50px;
        width: 40px;
        height: 40px;
        background: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        display: none;
    }

    #slick-prev.slick-disabled,
    #slick-next.slick-disabled {
        display: none !important;
    }

    #slick-prev {
        left: -35px;
    }

    #slick-next {
        right: -35px;
    }

    .nav-slider {
        justify-content: center;
    }

    .nav-link.text-nowrap {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 12px;
        min-width: 110px;
        border-radius: 10px;
        font-size: 12px;
    }

    .nav-link.text-nowrap i {
        font-size: 30px;
        margin-right: 0 !important;
        margin-bottom: 8px;
        color: #6366f1;
    }

    .nav-link.text-nowrap.active,
    .nav-link.text-nowrap:hover {
        background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%) !important;
        color: #fff !important;
    }

    .nav-link.text-nowrap.active i,
    .nav-link.text-nowrap:hover i {
        color: #fff !important;
    }

    .nav-link.text-nowrap:hover .nav-link.text-nowrap i:hover {
        color: red !important;
    }

    @media only screen and (max-width: 430px) {
        .rank-icon-mini {
            width: 25px !important;
        }

        .current-summary-rank-name,
        .desired-summary-rank-name,
        .current-summary-lp,
        .desired-summary-lp,
        .overview-count,
        .game-mode {
            font-size: 12px;
        }

        .fa-duotone.fa-solid.fa-arrow-right {
            margin-top: 4px;
        }

        #slick-prev,
        #slick-next {
            color: #fff;
            background: #6366f1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            display: block;
        }

        .nav-slider {
            justify-content: unset;
        }

        #slick-prev {
            left: 0;
        }

        #slick-next {
            right: 0;
        }
    }
</style>
<?= $this->stop() ?>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-65TVKVNEEW"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-65TVKVNEEW');
</script>