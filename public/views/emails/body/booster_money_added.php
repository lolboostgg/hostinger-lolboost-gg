<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Money Added to your Balance') ?>

<?php $balance = util_format_price_display(explode('|',$data['balance_update'])[1]); ?>

<?= $p("We have added <b>".util_format_price_display($data['amount'])." {$data['currency']}</b> to your balance.<br> Your new balance is <b>{$balance} EUR</b>") ?>

<?= $hr() ?>

<?= $p('If you have any questions or need assistance, our team is here to help.') ?>

<?= $p('Best regards,') ?>

<?= $p('The LBGG Team') ?>