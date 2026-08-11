<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your GamerGirl Account is Ready!') ?>

<?= $p("Welcome to LoLBoost.gg! We're excited to have you as part of our Gamer Girl team. Your application has been reviewed and approved. Below you'll find your login details:") ?>

<?= $hr() ?>

<?= $p("<strong>Email:</strong> {$data['email']}
        <br />
        <strong>Password:</strong> {$data['password']}") ?>

<?= $hr() ?>

<?= $btn('https://lolboost.gg/booster-area/auth/login', 'Access Your Dashboard') ?>

<?= $p('For security reasons, please store this information safely and avoid sharing it with anyone.') ?>

<?= $p("We're looking forward to working with you. If you have any questions or need help getting started, feel free to reach out to us anytime.") ?>

<?= $p('Welcome aboard!') ?>

<?= $p('Kind regards,<br>The LoLBoost Team') ?>
