<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Money Withdrawn from your Balance') ?>

<?php $balance = util_format_price_display(explode('|',$data['balance_update'])[1]); ?>

<?= $p("We have withdrawn <b>".util_format_price_display($data['amount'])." {$data['currency']}</b> from your balance.<br> Your new balance is <b>{$balance} EUR</b>") ?>

<?= $hr() ?>

<?= $p('If you have any questions or need assistance, our team is here to help.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>