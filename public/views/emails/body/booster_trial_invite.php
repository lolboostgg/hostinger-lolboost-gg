<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your Booster Trial Invite') ?>

<?= $p("Welcome to LoLBoost.gg! We reviewed your application and would like to invite you to the <strong>trial phase</strong> for our booster team.") ?>

<?= $hr() ?>

<?= $p("Please join our Discord server using the invite link below. Once you join, our team will guide you through the next steps of the trial process.") ?>

<?php if (!empty($data['discord_invite'])): ?>
<?= $btn($data['discord_invite'], 'Join Trial Discord') ?>
<?php endif; ?>

<?php if (!empty($data['note'])): ?>
<?= $hr() ?>
<?= $p("<strong>Additional note:</strong><br>" . nl2br($data['note'])) ?>
<?php endif; ?>

<?= $hr() ?>

<?= $p('Please make sure to join the Discord server as soon as possible so we can continue with your application.') ?>

<?= $p('If you have any questions, feel free to reply to this email or contact us via support.') ?>

<?= $p('Kind regards,<br>The LoLBoost Team') ?>
