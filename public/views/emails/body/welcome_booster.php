<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your Booster Account Details') ?>

<?= $p("Welcome to LoLBoost.gg! We're glad to have you as part of our booster team. Below you’ll find your account login details:") ?>

<?= $hr() ?>

<?= $p("<strong>Email:</strong> {$data['email']}
        <br />
        <strong>Password:</strong> {$data['password']}") ?>

<?= $hr() ?>

<?= $btn('https://lolboost.gg/booster-area/auth/login', 'Access Booster Dashboard') ?>

<?= $p('For security reasons, please store this information safely and avoid sharing it with anyone.') ?>

<?= $p('We’re looking forward to working with you. If you need any assistance or have questions about the platform, feel free to contact us anytime.') ?>

<?= $p('Welcome aboard!') ?>

<?= $p('Kind regards,<br>The LoLBoost Team') ?>