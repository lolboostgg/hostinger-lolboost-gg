<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Dear ' . $data['client_username']) ?>

<?= $p("Thanks for choosing LoLBoost. We kindly ask you to complete the payment below as soon as possible.") ?>

<?= $p("Amount: " . util_format_currency_display($data['currency']) . util_format_price_display($data['total_price'])) ?>

<?= $p("Details: " . $data['description']) ?>

<?= $btn("https://lolboost.gg/checkout/{$data['token']}", 'Proceed to Checkout') ?>

<?= $p('If you have any questions or need assistance, our team is here to help via livechat or on our discord.') ?>

<?= $p('Best regards,<br>The LBGG Team</b>') ?>