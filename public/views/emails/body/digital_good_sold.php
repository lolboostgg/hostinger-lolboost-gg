<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('You sold a digital good! 🛒') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, great news — a customer just bought one of your digital goods on LoLBoost.gg.") ?>

<?= $hr() ?>

<?php if (!empty($data['item_cover'])): ?>
<?= $img($data['item_cover']) ?>
<?php endif; ?>

<?= $p("<strong>Item:</strong> {$data['item_title']}<br>
        <strong>Quantity:</strong> {$data['quantity']}<br>
        <strong>Revenue:</strong> {$data['price']}<br>
        <strong>Buyer:</strong> {$data['buyer']}") ?>

<?= $hr() ?>

<?= $btn($data['order_url'], 'Open Order') ?>

<?= $p('Please deliver the order as soon as possible from your seller dashboard.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
