<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Payment Received') ?>

<?= $p("Thank you for your recent purchase.<br>We hope you are satisfied with our service and look forward to serving you again in the future.") ?>

<?= $btn(BASE_URL.'/profile/billing', 'View Payments') ?>

<?= $p('If you have any questions or concerns, please don\'t hesitate to contact us.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>