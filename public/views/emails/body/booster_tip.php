<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Tip Added to your Balance') ?>

<?= $p("You have received <b>" . util_format_price_display($data['amount']) . " {$data['currency']}</b> as a tip from {$data['client_username']}.") ?>

<?= $p($data['description']) ?>

<?= $hr() ?>

<?= $p('If you have any questions or need assistance, our team is here to help.') ?>

<?= $p('Best regards,') ?>

<?= $p('The LBGG Team') ?>