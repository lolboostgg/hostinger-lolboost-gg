<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Order Started') ?>

<?= $p("We are pleased to inform you that your order has been claimed by our booster <b>{$data['booster_username']}</b>.") ?>

<?= $btn(BASE_URL.'/order/'.$data['order_id'], 'View Order') ?>

<?= $p('Thank you for choosing our website. If you have any questions or need assistance, our team is here to help via livechat or on our discord.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>