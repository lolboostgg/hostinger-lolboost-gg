<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Order Paused ⏸️') ?>

<?= $p("Your order has been paused. You can resume it at any moment through your dashboard.") ?>

<?= $btn(BASE_URL.'/order/'.$data['order_id'], 'View Order') ?>

<?= $p('If you have any questions or concerns, please don\'t hesitate to contact us.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>