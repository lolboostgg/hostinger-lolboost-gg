<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your digital good order is complete! 🎉') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, your digital good order has been completed. Thank you for buying on LoLBoost.gg!") ?>

<?= $hr() ?>

<?php if (!empty($data['item_cover'])): ?>
<?= $img($data['item_cover']) ?>
<?php endif; ?>

<?= $p("<strong>Item:</strong> {$data['item_title']}<br>
        <strong>Quantity:</strong> {$data['quantity']}<br>
        <strong>Total:</strong> {$data['price']}<br>
        <strong>Seller:</strong> {$data['seller']}") ?>

<?= $hr() ?>

<?= $btn($data['order_url'], 'View Order') ?>

<?= $p('<strong>⭐ Happy with your purchase?</strong><br>You can leave a seller review directly from your order page.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
