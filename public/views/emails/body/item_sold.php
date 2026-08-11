<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('You made a sale! 🛒') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, great news — someone just bought one of your items on LoLBoost.gg!") ?>

<?= $hr() ?>

<?php if (!empty($data['item_cover'])): ?>
<?= $img($data['item_cover']) ?>
<?php endif; ?>

<?= $p("<strong>Item:</strong> {$data['item_title']}<br>
        <strong>Quantity:</strong> {$data['quantity']}<br>
        <strong>Revenue:</strong> €{$data['price']}<br>
        <strong>Buyer:</strong> {$data['buyer']}") ?>

<?= $hr() ?>

<?= $btn($data['order_url'], 'View Order Details') ?>

<?= $hr() ?>

<?= $p('Log in to your seller dashboard to manage the order and communicate with the buyer.') ?>

<?= $p('If you need help, we\'re here via <a href="https://lolboost.gg/contact#open-chat" style="color:#ffffff;text-decoration:underline;" target="_blank">Live Chat</a> or our <a href="https://lolboost.gg/discord" style="color:#ffffff;text-decoration:underline;" target="_blank">Discord</a>.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
