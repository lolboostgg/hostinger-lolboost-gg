<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Welcome to LoLBoost.gg') ?>

<?= $p("We're excited to have you as a client and hope that you have a great experience with us.") ?>

<?= $p("Your login information is as follows:") ?>

<?= $hr() ?>

<?= $p("<strong>Email:</strong> {$data['login']}
        <br />
        <strong>Password:</strong> {$data['password']}") ?>

<?= $hr() ?>

<?= $btn('https://lolboost.gg/', 'Login to your Account') ?>

<?= $p('Please keep this information safe and do not share it with anyone.') ?>

<?= $p('If you have any questions or need assistance, our team is here to help via livechat or on our discord.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>