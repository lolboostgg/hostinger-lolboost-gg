<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Custom Invoice Paid') ?>

<?= $p("The issued invoice #{$data['invoice_id']} has been paid.") ?>

<?= $p("<b>Details:</b>") ?>

<?= $p("Amount: " . util_format_currency_display($data['currency']) . util_format_price_display($data['amount'])) ?>

<?= $p("Client: #{$data['client_id']} - {$data['client_username']}.") ?>

<?= $p("Descripton: {$data['description']}") ?>

<?= $p('Best regards,<br>The LBGG Team') ?>