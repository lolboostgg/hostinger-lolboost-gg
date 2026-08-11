<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your digital good purchase is confirmed! ✅') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, thank you for your purchase on LoLBoost.gg! The seller has been notified and will deliver your digital good shortly.") ?>

<?= $hr() ?>

<?php if (!empty($data['item_cover'])): ?>
<?= $img($data['item_cover']) ?>
<?php endif; ?>

<?= $p("<strong>Item:</strong> {$data['item_title']}<br>
        <strong>Quantity:</strong> {$data['quantity']}<br>
        <strong>Total paid:</strong> {$data['price']}<br>
        <strong>Seller:</strong> {$data['seller']}") ?>

<?= $hr() ?>

<?= $btn($data['order_url'], 'View Your Order') ?>

<?= $p('You will receive another email when the seller marks your order as delivered.') ?>

<?= $p('If you need help, we\'re here via <a href="https://lolboost.gg/contact#open-chat" style="color:#ffffff;text-decoration:underline;" target="_blank">Live Chat</a> or our <a href="https://lolboost.gg/discord" style="color:#ffffff;text-decoration:underline;" target="_blank">Discord</a>.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
