<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => 'One more step! Complete your payment.']) ?>
<section class="py-5">
    <div class="container">
        <form action="<?= AJAX_URL ?>" class="checkout-form ajax-form">
            <input type="hidden" name="action" value="client_checkout">
            <input type="hidden" name="invoice_uuid" value="<?= $invoice['uuid'] ?>">
            <div class="row">
                <div class="col-lg-7 col-md-6 mb-5">
                    <div class="mb-5">
                        <?php if (CLIENT_DATA == false): ?>
                            <h5 class="mb-4"><span class="badge rounded-pill bg-gradient-primary align-middle"
                                    style="width:28px;height:28px">1</span> Login or create a new account</h5>
                            <div class="d-flex flex-row gap-2 justify-content-start align-items-center flex-wrap">
                                <button type="button" class="btn btn-outline-secondary" data-bs-target="#auth_modal"
                                    data-bs-toggle="modal"><i class="fa-duotone fa-lock-keyhole-open me-2"></i> Login or
                                    Register</button>
                                <span class="fw-bold text-muted fs-xs">OR</span>
                                <button type="button" class="btn btn-outline-secondary" data-bs-target="#guest_modal"
                                    data-bs-toggle="modal"><i class="fa-duotone fa-user-secret me-2"></i> Continue as a
                                    Guest</button>
                            </div>
                        <?php else: ?>
                            <h5 class="mb-4"><span class="badge rounded-pill bg-gradient-primary align-middle"
                                    style="width:28px;height:28px">1</span> Checkout as <?= CLIENT_DATA['username'] ?></h5>
                            <div class="alert alert-primary" role="alert">
                                You're already logged in! you can directly proceed to the next step.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-5">
                        <h5 class="mb-4">
                            <span class="badge rounded-pill bg-gradient-primary align-middle"
                                style="width:28px;height:28px">2</span>
                            <span>Select your payment processor</span>
                        </h5>
                        <div class="d-flex flex-column gap-2 justify-content-center flex-wrap align-items-start">
                            <div style="width:100%;max-width:500px;">
                                <input class="form-check-input custom-radio" type="radio" value="stripe" id="stripe"
                                    name="processor" checked="">
                                <label
                                    class="form-check-label custom-check-label fs-base fw-500 card py-3 px-4 d-flex gap-4"
                                    for="stripe">
                                    <img src="<?= ASSET_URL ?>/core/main/img/checkout/stripe.svg" class="checkout-img"
                                        alt="Stripe">
                                    <img src="<?= ASSET_URL ?>/core/main/img/checkout/stripe_sub.png"
                                        class="checkout-img-sub" alt="Stripe">
                                </label>
                            </div>
                            <div style="width:100%;max-width:500px;">
                                <input class="form-check-input custom-radio" type="radio" value="stripe_paypal"
                                    id="stripe_paypal" name="processor">
                                <label
                                    class="form-check-label custom-check-label fs-base fw-500 card py-3 px-4 d-flex gap-4"
                                    for="stripe_paypal">
                                    <img src="<?= ASSET_URL ?>/core/main/img/checkout/paypal.svg" class="checkout-img"
                                        alt="paypal">
                                    <span class="badge rounded fw-bold bg-faded-info text-info">10% Fee</span>
                                </label>
                            </div>
                            <div style="width:100%;max-width:500px;">
                                <input class="form-check-input custom-radio" type="radio" value="coinbase" id="coinbase"
                                    name="processor">
                                <label
                                    class="form-check-label custom-check-label fs-base fw-500 card py-3 px-4 d-flex gap-4"
                                    for="coinbase">
                                    <img src="<?= ASSET_URL ?>/core/main/img/checkout/coinbase.svg" class="checkout-img"
                                        alt="coinbase">
                                    <img src="<?= ASSET_URL ?>/core/main/img/checkout/coinbase_sub.png?v1"
                                        class="checkout-img-sub" alt="coinbase">
                                </label>
                            </div>
                            <div style="width:100%;max-width:500px;">
                                <!--   <input class="form-check-input custom-radio" type="radio" value="payop" id="payop" name="processor">
                                <label class="form-check-label custom-check-label fs-base fw-500 card py-3 px-4 d-flex gap-4" for="payop">
                                    <img src="<?= ASSET_URL ?>/core/main/img/checkout/payop.svg" class="checkout-img" alt="payop">
                                    <img src="<?= ASSET_URL ?>/core/main/img/checkout/payop_sub.png?v1" class="checkout-img-sub" alt="payop">
                                </label>-->
                            </div>
                        </div>
                    </div>
                    <?php if (CLIENT_DATA): ?>
                        <div class="mt-4">
                            <h5 class="mb-4">
                                <span class="badge rounded-pill bg-gradient-primary align-middle"
                                    style="width:28px;height:28px">3</span>
                                <span>LB Coins</span>
                            </h5>
                            <div class="d-flex flex-column gap-2 justify-content-center flex-wrap align-items-start">
                                <div style="width:100%;max-width:500px;">
                                    <input type="hidden" name="points_used" value="0">
                                    <input class="form-check-input custom-radio" type="checkbox" id="use_points" value="1"
                                        name="points_used" <?= $invoice['coins_used'] != 0.0 ? 'checked' : '' ?>>
                                    <label
                                        class="form-check-label custom-check-label fs-base fw-500 card py-3 px-4 d-flex gap-4 align-items-center"
                                        for="use_points">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt="LB Coins"
                                                style="width:20px;height:20px;" class="me-2">
                                            <span class="fw-600 fs-5">Use LB Coins</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-5 col-md-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <?php if ($invoice['order_type'] == 'tip'): ?>
                                <h5 class="mb-0 text-center">Thank You 😊</h5>
                            <?php elseif ($invoice['order_type'] == 'invoice'): ?>
                                <h5 class="mb-0 text-center">Invoice Summary 🧾</h5>
                            <?php else: ?>
                                <h5 class="mb-0 text-center">Order Summary 🧾</h5>
                            <?php endif; ?>
                        </div>
                        <div class="card-body px-0 pt-0">
                            <?= $this->insert('main/components/checkout/' . $invoice['order_type'], ['data' => $data]) ?>
                            <div class="d-flex flex-column gap-2 px-4 pt-4">
                                <?= $this->insert('main/components/checkout/' . $invoice['order_type'] . '_list', ['data' => $data]) ?>
                            </div>
                            <div class="mx-4 mt-4">
                                <?php if ($invoice['order_type'] != 'tip' && $invoice['order_type'] != 'invoice'): ?>
                                    <?php if (!empty($invoice['discount_id']) && $invoice['discount_id'] > 0): ?>
                                        <div class="fw-500" role="alert" style="
                                        border: 1px solid #20b45b;
                                        color: #20b45b;
                                        background: hsla(142, 71%, 45%, 0.2);
                                        padding: 12px 16px;
                                        border-radius: 12px;
                                        font-size: 1rem;
                                        text-align: center;
                                        ">
                                            Great! Your discount code is now active. 🎉
                                        </div>
                                    <?php else: ?>
                                        <label for="discount_code" class="form-label">Discount Code</label>
                                        <div class="d-flex gap-1">
                                            <input class="form-control" type="text" name="discount_code" id="discount_code"
                                                placeholder="e.g: SPLIT15">
                                            <button class="btn btn-secondary" type="button" id="apply_discount">
                                                <span class="indicator-label">Apply</span>
                                                <span class="indicator-progress">
                                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                                </span>
                                            </button>
                                        </div>
                                        <div id="discount_alert" class="mt-1 text-center"></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="mx-4 mt-4">
                                <!-- Zeile 1: Total Price -->
                                <div class="d-flex flex-row justify-content-between align-items-center">
                                    <?php if ($invoice['order_type'] == 'tip'): ?>
                                        <span class="fw-600 fs-5 text-dark">Amount</span>
                                    <?php else: ?>
                                        <span class="fw-600 fs-5 text-dark">Total Price</span>
                                    <?php endif; ?>
                                    <span class="fw-bold text-gradient-primary fs-2 total-text">
                                        <?= util_format_currency_display($invoice['currency']) ?>
                                        <?= util_format_price_display($invoice['total_price']) ?>
                                    </span>
                                </div>

                                <!-- Zeile 2: Cashback Info -->
                                <?php if (CLIENT_DATA && $invoice['order_type'] != 'tip'): ?>
                                    <div class="d-flex flex-row justify-content-between align-items-center mt-2">
                                        <span class="fw-500 text-muted fs-sm">Cashback</span>
                                        <span class="fw-500 text-muted fs-sm d-flex align-items-center">
                                            <strong class="me-1">
                                                <?php
                                                $client_rank = db_get_row('loyalty_ranks', ['id' => CLIENT_DATA['loyalty_rank_id']]);
                                                $cashback_percentage = $client_rank['cashback'];
                                                $coins_earned = ($invoice['total_price'] * $cashback_percentage) / 100;
                                                echo number_format($coins_earned / 100, 2);
                                                ?>
                                            </strong>
                                            <img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt="LB Coins"
                                                style="width:18px;height:18px;vertical-align:middle;">
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                            
                                <!-- Reminder Hinweis -->
                        <?php if (!$data['is_duo'] && in_array($data['form_id'], [1,2,3,4,5,6,7,8,9,17,18,19,20])): ?>
                                                <div class="mx-4 mt-3">
                          <div class="bg-danger text-white p-3 mb-3 text-center" 
                               style="border: 1px solid #ff9999; border-radius: 12px;">
                            <i class="fa-duotone fa-triangle-exclamation me-2" style="font-size: 1.25rem;"></i>
                               <strong>IMPORTANT</strong><br>
                              <strong>Boosting</strong> goes against the rules.<br>
                              <strong>lolboost.gg</strong> is not responsible if your account gets a 
                              <strong>rank reset</strong>, <strong>suspension</strong>, or <strong>ban</strong> after the boost is finished.
                                                </div>
                                                </div>
                          <?php endif; ?>

                        <!-- /Reminder Hinweis -->
                        
                        <div class="mx-4 text-center">
                            <button type="submit" class="btn mb-2 btn-lg w-100 btn-primary" id="complete_payment">
                                <span class="indicator-label">Complete Payment</span>
                                <span class="indicator-progress">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </span>
                            </button>
                            <!-- <?= $this->insert('main/components/widgets/tp-light-lg') ?> -->
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<hr>

<div class="modal fade" id="guest_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Modal header with nav tabs -->
            <div class="modal-header">
                <h5 class="modal-title">Continue as a Guest</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal body with tab panes -->
            <div class="modal-body py-4">

                <!-- Sign in form -->
                <form class="ajax-form" action="<?= AJAX_URL ?>" autocomplete="off">
                    <input type="hidden" name="action" value="auth_client_guest_login">
                    <div class="mb-3">
                        <label class="form-label" for="email5">Email address</label>
                        <input class="form-control" type="email" name="email" id="email5"
                            placeholder="mail@example.com">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" name="tos" type="checkbox" id="guest_tos">
                        <label class="form-check-label" for="guest_tos">I agree to the <a
                                href="<?= BASE_URL ?>/legal/terms" class="text-decoration-none">terms of service</a>
                            and
                            <a href="<?= BASE_URL ?>/legal/privacy" class="text-decoration-none">privacy
                                policy</a></label>
                    </div>
                    <div class="alert alert-danger form-error" style="display:none" role="alert">
                    </div>
                    <button class="btn btn-primary d-block w-100" type="submit">
                        <span class="indicator-label">Continue as a Guest</span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<script>
    $("#apply_discount").on('click', function () {
        var discount_code = $("#discount_code").val();
        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: {
                action: 'client_discount_invoice',
                discount_code: discount_code,
                invoice_id: <?= $invoice['id'] ?>,
            },
            beforeSend: function () {
                $("#apply_discount").attr('data-indicator', 'on');
                $("#apply_discount").prop('disabled', true);
            },
            success: function (response) {

                $("#apply_discount").removeAttr('data-indicator');
                $("#apply_discount").prop('disabled', false);
                response = JSON.parse(response);
                if (response.discount_status) {
                    $('#discount_alert').text(response.discount_msg);
                    $('#discount_alert').removeClass('text-danger');
                    $('#discount_alert').addClass('text-success');
                    location.reload();
                } else if (response.discount_msg != null) {
                    $('#discount_alert').text(response.discount_msg);
                    $('#discount_alert').removeClass('text-success');
                    $('#discount_alert').addClass('text-danger');
                    // refresh page
                } else {
                    $('#discount_alert').text('');
                }
            }
        });
    });

    // if stripe_paypal is selected then update the total price
    $('input[name="processor"]').on('change', function () {
        var processor = $('input[name="processor"]:checked').val();
        if (processor == 'stripe_paypal') {
            var total_price = <?= $invoice['total_price'] ?>;
            var fee = total_price * 0.1;
            var new_total = (total_price + fee) / 100;
            $('.fs-2').text('<?= util_format_currency_display($invoice['currency']) ?>' + new_total.toFixed(2));
        } else {
            $('.fs-2').text('<?= util_format_currency_display($invoice['currency']) ?>' +
                <?= util_format_price_display($invoice['total_price']) ?>);
        }
    });

    $('#use_points').on('change', function () {
        let checkbox = $(this);
        var currency = '<?= util_format_currency_display($invoice['currency']) ?>';

        $.ajax({
            url: '<?= AJAX_URL ?>',
            type: 'POST',
            data: {
                action: 'checkout_coins_toggle',
                invoice_id: '<?= $uuid ?>',
                use_coins: checkbox.is(':checked')
            },
            beforeSend: function () {
                $('#complete_payment').attr('data-indicator', 'on');
                $('#complete_payment').prop('disabled', true);
            },
            success: function (response) {
                response = JSON.parse(response);

                // Update total price
                $('.total-text').text(currency + parseFloat(response.total_price / 100).toFixed(2));

                // Calculate cashback earned if CLIENT_DATA is available
                if (<?= CLIENT_DATA ? 'true' : 'false' ?>) {
                    const cashbackPercentage =
                        <?= CLIENT_DATA ? (CLIENT_DATA['loyalty_rank_id'] ? 'parseFloat(' . json_encode(db_get_row('loyalty_ranks', ['id' => CLIENT_DATA['loyalty_rank_id']])['cashback']) . ')' : '0') : '0' ?>;
                    const coinsEarned = (response.total_price * cashbackPercentage) / 100;

                    // Update coins earned
                    $('.coins-earned').text((coinsEarned / 100).toFixed(2));
                }

                // Update coins used
                if (checkbox.is(':checked')) {
                    $('.coins-list').html(`
                <span class="fw-600">LB Coins Discount</span>
                <span class="fw-500 text-primary">${response.coins_used}</span>
            `);
                } else {
                    $('.coins-list').html('');
                }

                // Reset complete payment button
                $('#complete_payment').removeAttr('data-indicator');
                $('#complete_payment').prop('disabled', false);
            }
        });
    });
</script>
<?= $this->stop() ?>



<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<?= $this->stop() ?>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-65TVKVNEEW"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-65TVKVNEEW');

    var sessionBooster = sessionStorage.getItem('booster');

    if (sessionBooster) {
        document.getElementById('booster_id').value = sessionBooster;
    }

    document.getElementById('complete_payment').addEventListener('click', function () {
        sessionStorage.removeItem('booster');
    });
</script>