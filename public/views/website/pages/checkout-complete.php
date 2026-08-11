<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'imprint-page thankyou checkout-complete-page']) ?>

<?php
$orderType = (string)($invoice['order_type'] ?? '');
$orderId = (int)($invoice['order_id'] ?? 0);
$isAccount = $orderType === 'lol_account';

$page = [
    'icon' => 'fa-circle-check',
    'eyebrow' => 'Payment confirmed',
    'title' => 'Your order is confirmed',
    'lead' => 'Everything went through successfully. Use the overview below to continue.',
    'primary_url' => BASE_URL . '/' . rawurlencode($orderType) . '/' . $orderId,
    'primary_label' => 'View Order',
    'primary_icon' => 'fa-arrow-right',
    'secondary_url' => BASE_URL . '/profile/orders',
    'secondary_label' => 'My Orders',
    'steps' => [
        ['fa-rectangle-list', 'Open your order', 'View all order details and the current status in your dashboard.'],
        ['fa-user-plus', 'Complete required details', 'Add any requested account or service information on the order page.'],
        ['fa-bell', 'Follow the progress', 'Updates and messages will appear directly inside your order.'],
    ],
];

if ($orderType === 'invoice') {
    $page['primary_url'] = BASE_URL . '/order/' . $orderId;
} elseif ($isAccount) {
    $accountId = (int)($lol_account_id ?? $orderId);
    $page = array_merge($page, [
        'icon' => 'fa-key',
        'title' => 'Your gaming account is ready',
        'lead' => 'Your purchase is complete. Open the account page to securely view the delivered login and account details.',
        'primary_url' => $accountId > 0 ? BASE_URL . '/profile/account/' . $accountId : BASE_URL . '/profile/accounts',
        'primary_label' => 'View Account & Login Details',
        'primary_icon' => 'fa-key',
        'secondary_url' => BASE_URL . '/profile/accounts',
        'secondary_label' => 'My Purchased Accounts',
        'steps' => [
            ['fa-arrow-pointer', 'Open your purchased account', 'Click the button above. Your purchase is also permanently saved under “My Purchased Accounts”.'],
            ['fa-eye', 'View the delivered details', 'The account page contains the available login credentials and all delivery information.'],
            ['fa-shield-check', 'Secure the account', 'Follow the delivery notes and update changeable security details after you have successfully logged in.'],
        ],
    ]);
} elseif ($orderType === 'egirl_session') {
    $page = array_merge($page, [
        'icon' => 'fa-headset',
        'title' => 'Your session is booked',
        'lead' => 'Your booking is confirmed. Open your session page to see updates and coordinate the session.',
        'primary_url' => BASE_URL . '/profile/orders',
        'primary_label' => 'View My Sessions',
        'primary_icon' => 'fa-calendar-check',
        'secondary_url' => BASE_URL . '/profile/orders',
        'secondary_label' => 'My Orders',
        'steps' => [
            ['fa-rectangle-list', 'Open your session', 'Your booked session and its current status are shown in your account.'],
            ['fa-comments', 'Check for messages', 'Coordination and important updates will appear with your booking.'],
            ['fa-gamepad', 'Join at the agreed time', 'Use the confirmed session information when it is time to play.'],
        ],
    ]);
} elseif ($orderType === 'selling_item') {
    $purchaseId = (int)($selling_item_purchase_id ?? 0);
    $page = array_merge($page, [
        'icon' => 'fa-gift',
        'title' => 'Your item order is confirmed',
        'lead' => 'Open the item order to follow delivery and contact the seller if needed.',
        'primary_url' => $purchaseId > 0 ? BASE_URL . '/profile/item/' . $purchaseId : BASE_URL . '/profile/items',
        'primary_label' => 'View Item Order',
        'primary_icon' => 'fa-gift',
        'secondary_url' => BASE_URL . '/profile/items',
        'secondary_label' => 'My Item Orders',
        'steps' => [
            ['fa-rectangle-list', 'Open the item order', 'Your purchase details and current delivery status are shown there.'],
            ['fa-comments', 'Stay available for delivery', 'The seller can contact you if information is required.'],
            ['fa-circle-check', 'Confirm completion', 'Review the delivered item and follow the instructions on the order page.'],
        ],
    ]);
} elseif ($orderType === 'selling_topup') {
    $purchaseId = (int)($selling_topup_purchase_id ?? 0);
    $page = array_merge($page, [
        'icon' => 'fa-coins',
        'title' => 'Your top-up is confirmed',
        'lead' => 'Open the top-up order to track delivery and communicate with the seller.',
        'primary_url' => $purchaseId > 0 ? BASE_URL . '/profile/top-up/' . $purchaseId : BASE_URL . '/profile/top-ups',
        'primary_label' => 'View Top-Up Order',
        'primary_icon' => 'fa-coins',
        'secondary_url' => BASE_URL . '/profile/top-ups',
        'secondary_label' => 'My Top-Up Orders',
    ]);
} elseif ($orderType === 'digital_good') {
    $purchaseId = (int)($digital_good_purchase_id ?? 0);
    $page = array_merge($page, [
        'icon' => 'fa-box-open',
        'title' => 'Your digital purchase is ready',
        'lead' => 'Open the purchase page to view its delivery details and contact the seller if required.',
        'primary_url' => $purchaseId > 0 ? BASE_URL . '/profile/digital-goods/' . $purchaseId : BASE_URL . '/profile/orders',
        'primary_label' => 'View Digital Purchase',
        'primary_icon' => 'fa-box-open',
    ]);
} elseif ($orderType === 'tip') {
    $page = array_merge($page, [
        'icon' => 'fa-heart',
        'title' => 'Thank you for your tip!',
        'lead' => 'Your payment was completed successfully. We appreciate your support.',
        'primary_url' => BASE_URL . '/profile/billing',
        'primary_label' => 'View Billing',
        'primary_icon' => 'fa-receipt',
        'secondary_url' => BASE_URL . '/profile/orders',
        'secondary_label' => 'Back to Profile',
        'steps' => [],
    ]);
}
?>

<div class="checkout-success">
    <section class="checkout-success__hero">
        <div class="checkout-success__icon"><i class="fa-solid <?= htmlspecialchars($page['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></div>
        <span class="checkout-success__eyebrow"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($page['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
        <h1><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($page['lead'], ENT_QUOTES, 'UTF-8') ?></p>

        <div class="checkout-success__actions">
            <a href="<?= htmlspecialchars($page['primary_url'], ENT_QUOTES, 'UTF-8') ?>" class="checkout-success__button checkout-success__button--primary">
                <i class="fa-solid <?= htmlspecialchars($page['primary_icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                <?= htmlspecialchars($page['primary_label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a href="<?= htmlspecialchars($page['secondary_url'], ENT_QUOTES, 'UTF-8') ?>" class="checkout-success__button checkout-success__button--secondary">
                <?= htmlspecialchars($page['secondary_label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>

        <div class="checkout-success__meta">
            <span><i class="fa-solid fa-shield-check"></i> Payment secured</span>
            <?php if ($orderId > 0): ?><span><i class="fa-solid fa-hashtag"></i> Order <?= $orderId ?></span><?php endif; ?>
            <span><i class="fa-solid fa-envelope"></i> Confirmation sent</span>
        </div>
    </section>

    <?php if ($isAccount): ?>
        <aside class="checkout-success__notice">
            <i class="fa-solid fa-key"></i>
            <div>
                <strong>Where are my account login details?</strong>
                <p>Click <b>“View Account & Login Details”</b> above. Your delivered credentials and account information are securely available on that page.</p>
            </div>
        </aside>
    <?php endif; ?>

    <?php if (!empty($page['steps'])): ?>
        <section class="checkout-success__next">
            <div class="checkout-success__section-head">
                <span>What happens next?</span>
                <h2>Your next steps</h2>
            </div>
            <div class="checkout-success__steps">
                <?php foreach ($page['steps'] as $index => $step): ?>
                    <article class="checkout-success__step">
                        <span class="checkout-success__step-number"><?= $index + 1 ?></span>
                        <i class="fa-solid <?= htmlspecialchars($step[0], ENT_QUOTES, 'UTF-8') ?>"></i>
                        <div>
                            <h3><?= htmlspecialchars($step[1], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($step[2], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <p class="checkout-success__help">Need help with your purchase? <a href="#" data-tawk-open="1" onclick="return window.lbOpenLiveChat ? window.lbOpenLiveChat() : false;">Contact our support team</a>.</p>
</div>

<style>
.checkout-complete-page { background:#050817; }
.checkout-complete-page .header { display:none; }
.checkout-complete-page .container { max-width:none; }
.checkout-success { width:min(1160px,calc(100% - 40px)); margin:clamp(220px,13vw,260px) auto 64px; color:#f8f8ff; }
.checkout-success__hero,.checkout-success__next,.checkout-success__notice { border:1px solid #252b49; background:#101425; box-shadow:0 18px 50px rgba(0,0,0,.22); }
.checkout-success__hero { position:relative; overflow:hidden; border-radius:18px; padding:30px 32px 22px; text-align:center; }
.checkout-success__hero:before { content:""; position:absolute; inset:0 0 auto; height:3px; background:linear-gradient(90deg,#6366f1,#a855f7,#ec4899); pointer-events:none; }
.checkout-success__icon { position:relative; width:58px; height:58px; margin:0 auto 13px; display:flex; align-items:center; justify-content:center; border:1px solid rgba(236,72,153,.38); border-radius:16px; color:#fff; font-size:1.35rem; background:linear-gradient(135deg,#7059ed,#d14da4); box-shadow:0 10px 26px rgba(196,67,166,.2); }
.checkout-success__eyebrow { position:relative; display:inline-flex; align-items:center; gap:7px; color:#e879cf; font-size:.7rem; font-weight:900; letter-spacing:.09em; text-transform:uppercase; }
.checkout-success__eyebrow i { color:#4ee1bf; }
.checkout-success__hero h1 { position:relative; margin:7px 0 7px; color:#fff; font-size:clamp(1.65rem,3vw,2.15rem); line-height:1.12; }
.checkout-success__hero>p { position:relative; max-width:680px; margin:0 auto!important; color:#9299b3; font-size:.88rem!important; line-height:1.55; }
.checkout-success__actions { position:relative; display:flex; justify-content:center; flex-wrap:wrap; gap:9px; margin-top:19px; }
.checkout-success__button { min-height:43px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:10px; padding:0 19px; text-decoration:none!important; font-size:.82rem; font-weight:900; transition:.18s ease; }
.checkout-success__button--primary { color:#fff!important; background:linear-gradient(100deg,#745cff,#d84fa8); box-shadow:0 10px 24px rgba(177,69,183,.2); }
.checkout-success__button--primary:hover { transform:translateY(-1px); box-shadow:0 13px 28px rgba(177,69,183,.3); }
.checkout-success__button--secondary { color:#c3c7d8!important; border:1px solid #303653; background:#171b2d; }
.checkout-success__button--secondary:hover { color:#fff!important; border-color:#4a5278; background:#1c2137; }
.checkout-success__meta { position:relative; display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:14px 25px; margin-top:20px; padding-top:16px; border-top:1px solid #232840; color:#747c99; font-size:.69rem; font-weight:750; }
.checkout-success__meta span { display:inline-flex; align-items:center; gap:6px; }
.checkout-success__meta i { color:#a66cf3; }
.checkout-success__notice { display:flex; align-items:center; gap:14px; margin-top:12px; border-color:#294a55; border-radius:14px; padding:15px 18px; background:#0d2028; text-align:left; }
.checkout-success__notice>i { width:38px; height:38px; flex:0 0 auto; display:flex; align-items:center; justify-content:center; border-radius:10px; color:#55e2c1; background:#14353a; }
.checkout-success__notice strong { display:block; color:#fff; font-size:.88rem; margin-bottom:2px; }
.checkout-success__notice p { margin:0!important; color:#8ea7ad; font-size:.77rem!important; line-height:1.45; }
.checkout-success__next { margin-top:12px; border-radius:16px; padding:20px 22px 22px; text-align:left; }
.checkout-success__section-head { display:flex; align-items:baseline; gap:11px; margin-bottom:14px; }
.checkout-success__section-head span { color:#df64bf; font-size:.65rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
.checkout-success__section-head h2 { margin:0; color:#fff; font-size:1.08rem; }
.checkout-success__steps { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
.checkout-success__step { position:relative; min-height:118px; display:grid; grid-template-columns:36px 1fr; align-content:start; gap:0 12px; border:1px solid #282e49; border-radius:13px; padding:16px; background:#14192b; }
.checkout-success__step>i { grid-row:1 / span 2; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:9px; color:#e377c8; background:#272044; }
.checkout-success__step-number { position:absolute; right:13px; top:9px; color:#363c59; font-size:1.25rem; font-weight:950; }
.checkout-success__step h3 { margin:1px 28px 5px 0; color:#fff; font-size:.82rem; }
.checkout-success__step p { margin:0!important; color:#7f879f; font-size:.71rem!important; line-height:1.45; }
.checkout-success__help { margin:15px 0 0!important; text-align:center; color:#6f7792!important; font-size:.74rem!important; }
.checkout-success__help a { color:#dd6bc3!important; font-weight:850; }
@media(max-width:760px){.checkout-success{width:min(100% - 24px,1160px);margin-top:145px}.checkout-success__hero{padding:27px 17px 20px}.checkout-success__steps{grid-template-columns:1fr}.checkout-success__step{min-height:0}.checkout-success__actions{flex-direction:column}.checkout-success__button{width:100%}.checkout-success__meta{align-items:flex-start;flex-direction:column;gap:8px}.checkout-success__notice{align-items:flex-start;padding:14px}.checkout-success__next{padding:18px}.checkout-success__section-head{display:block}.checkout-success__section-head h2{margin-top:3px}}

/* Open completion layout: content flows on the marketplace background instead
   of being nested inside large dashboard cards. */
.checkout-success__hero { overflow:visible; border:0; border-radius:0; background:transparent; box-shadow:none; padding:18px 24px 25px; }
.checkout-success__hero:before { width:190px; height:190px; inset:-38px auto auto 50%; transform:translateX(-50%); border-radius:50%; background:radial-gradient(circle,rgba(182,72,193,.14),transparent 68%); }
.checkout-success__icon { border-radius:50%; }
.checkout-success__meta { max-width:720px; margin-left:auto; margin-right:auto; }
.checkout-success__next { border:0; border-top:1px solid #252b49; border-radius:0; background:transparent; box-shadow:none; padding:28px 0 0; }
.checkout-success__section-head { justify-content:center; margin-bottom:25px; }
.checkout-success__steps { position:relative; gap:46px; }
.checkout-success__steps:before { content:""; position:absolute; height:2px; left:12%; right:12%; top:19px; background:linear-gradient(90deg,#725fff,#bd55d3,#ec4899); opacity:.48; }
.checkout-success__step { min-height:0; display:block; border:0; border-radius:0; padding:0 12px; background:transparent; text-align:center; }
.checkout-success__step>i { position:relative; z-index:1; width:40px; height:40px; margin:0 auto 13px; border:1px solid #4a3970; border-radius:50%; color:#f18bd5; background:#17172e; }
.checkout-success__step-number { display:none; }
.checkout-success__step h3 { margin:0 0 6px; font-size:.86rem; }
.checkout-success__step p { max-width:280px; margin:0 auto!important; }
.checkout-success__notice { max-width:820px; margin:12px auto 26px; }
.checkout-success__help { margin-top:25px!important; }
.checkout-success__icon { width:64px; height:64px; font-size:1.5rem; }
.checkout-success__hero h1 { font-size:clamp(1.9rem,3.2vw,2.45rem); }
.checkout-success__hero>p { font-size:.96rem!important; }
.checkout-success__button { min-height:46px; padding:0 22px; font-size:.88rem; }
.checkout-success__eyebrow { font-size:.74rem; }
.checkout-success__section-head h2 { font-size:1.22rem; }
.checkout-success__section-head span { font-size:.69rem; }
.checkout-success__steps:before { display:none; }
.checkout-success__step:before { content:""; position:absolute; z-index:0; height:2px; left:calc(50% + 30px); top:22px; width:calc(100% + 46px - 60px); background:linear-gradient(90deg,#825ff5,#d44fb3); opacity:.52; }
.checkout-success__step:last-child:before { display:none; }
.checkout-success__step>i { width:46px; height:46px; font-size:1rem; }
.checkout-success__step h3 { font-size:.94rem; margin-bottom:8px; }
.checkout-success__step p { font-size:.78rem!important; line-height:1.55; }
.checkout-success__help { font-size:.82rem!important; }
@media(max-width:760px){
  .checkout-success__hero { padding:12px 4px 22px; }
  .checkout-success__next { padding:24px 4px 0; }
  .checkout-success__section-head { display:block; text-align:center; margin-bottom:22px; }
  .checkout-success__steps { gap:0; }
  .checkout-success__steps:before { display:none; }
  .checkout-success__step { display:grid; grid-template-columns:46px 1fr; gap:0 14px; padding:0 0 28px; text-align:left; }
  .checkout-success__step:before { width:2px; height:calc(100% - 38px); left:22px; top:50px; }
  .checkout-success__step>i { grid-row:1 / span 2; margin:0; }
  .checkout-success__step h3 { margin-top:2px; }
  .checkout-success__step p { max-width:none!important; margin:0!important; }
}
</style>

<script>
gtag('event', 'conversion', {
    'send_to': 'AW-11473081744/FrCbCOGzyb0aEJCr5d4q',
    'value': 1.0,
    'currency': 'EUR'
});
</script>
