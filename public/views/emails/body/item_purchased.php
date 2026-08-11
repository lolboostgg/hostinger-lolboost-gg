<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your purchase is confirmed! ✅') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, thank you for your purchase on LoLBoost.gg!") ?>

<?= $hr() ?>

<?php if (!empty($data['item_cover'])): ?>
<?= $img($data['item_cover']) ?>
<?php endif; ?>

<?= $p("<strong>Item:</strong> {$data['item_title']}<br>
        <strong>Quantity:</strong> {$data['quantity']}<br>
        <strong>Total paid:</strong> €{$data['price']}") ?>

<?php if (!empty($data['delivery_instructions'])): ?>
<?= $hr() ?>
<?= $p("<strong>📦 Delivery Instructions from the Seller:</strong><br>{$data['delivery_instructions']}") ?>
<?php endif; ?>

<?= $hr() ?>

<?= $btn($data['item_url'], 'View Your Order') ?>

<?= $hr() ?>

<?= $p("<strong>⭐ Happy with your purchase?</strong><br>Leave us a quick review — it only takes a second and helps us a lot!") ?>

<?= $p('If you need help, we\'re here via <a href="https://lolboost.gg/contact#open-chat" style="color:#ffffff;text-decoration:underline;" target="_blank">Live Chat</a> or our <a href="https://lolboost.gg/discord" style="color:#ffffff;text-decoration:underline;" target="_blank">Discord</a>.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
