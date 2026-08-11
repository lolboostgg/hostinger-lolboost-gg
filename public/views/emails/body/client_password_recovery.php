<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Password Reset') ?>

<?= $p("There was a request to reset your LoLBoost.gg's password.<br>Click the button below to reset your password.") ?>

<?= $btn('https://lolboost.gg/?tk='.$data['token'], 'Reset Password') ?>

<?= $p('If you have any questions or need assistance, our team is here to help via livechat or on our discord.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>