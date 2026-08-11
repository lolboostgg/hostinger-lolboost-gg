<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your order has been refunded 💸') ?>

<?= $p('Hi ' . $data['username'] . ',') ?>

<?= $p('Your recent order has been refunded successfully.') ?>

<?php if (!empty($data['order_id'])): ?>
    <?= $p('Order ID: <strong>#' . $data['order_id'] . '</strong>') ?>
<?php endif; ?>

<?php if (!empty($data['refund_amount'])): ?>
    <?= $p('Refund amount: <strong>' . util_format_currency_display($data['currency'] ?? 'EUR') . util_format_price_display((int)$data['refund_amount']) . '</strong>') ?>
<?php endif; ?>

<?= $p('The refunded amount will be returned to your original payment method as quickly as possible.') ?>

<?= $btn(BASE_URL . '/profile/orders', 'View my orders') ?>

<?= $p('If you have any questions, our support team is here for you anytime.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
