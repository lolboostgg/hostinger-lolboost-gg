<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your digital good has been delivered! 📦') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, your seller <strong>{$data['seller']}</strong> has marked your digital good order as delivered.") ?>

<?= $hr() ?>

<?php if (!empty($data['item_cover'])): ?>
<?= $img($data['item_cover']) ?>
<?php endif; ?>

<?= $p("<strong>Item:</strong> {$data['item_title']}<br>
        <strong>Quantity:</strong> {$data['quantity']}<br>
        <strong>Total:</strong> {$data['price']}") ?>

<?php if (!empty($data['delivery_note'])): ?>
<?= $hr() ?>
<?= $p("<strong>Delivery details:</strong><br>" . nl2br(htmlspecialchars($data['delivery_note'], ENT_QUOTES, 'UTF-8'))) ?>
<?php endif; ?>

<?= $hr() ?>

<?= $btn($data['order_url'], 'Check Delivery') ?>

<?= $p('Please check everything. If everything is correct, confirm the delivery in your order view.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
