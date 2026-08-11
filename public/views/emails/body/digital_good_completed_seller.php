<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Digital good order completed ✅') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, the buyer has confirmed delivery and the digital good order is now completed.") ?>

<?= $hr() ?>

<?php if (!empty($data['item_cover'])): ?>
<?= $img($data['item_cover']) ?>
<?php endif; ?>

<?= $p("<strong>Item:</strong> {$data['item_title']}<br>
        <strong>Quantity:</strong> {$data['quantity']}<br>
        <strong>Total:</strong> {$data['price']}<br>
        <strong>Buyer:</strong> {$data['buyer']}") ?>

<?= $hr() ?>

<?= $btn($data['order_url'], 'Open Order') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
