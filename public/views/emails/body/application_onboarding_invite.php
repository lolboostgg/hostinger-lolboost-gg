<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Welcome to the LoLBoost.gg Team!') ?>

<?= $p("Hi <strong>" . htmlspecialchars($data['username'] ?? 'there') . "</strong>,") ?>

<?= $p("Great news — your application has been <strong>accepted</strong>! We're excited to have you on board.") ?>

<?= $hr() ?>

<?= $p("<strong>Step 1 — Complete your onboarding</strong><br>Use the button below to set up your account and finish your onboarding. This link is personal and expires in 72 hours.") ?>

<?php if (!empty($data['onboarding_link'])): ?>
<?= $btn($data['onboarding_link'], 'Start Onboarding') ?>
<?php else: ?>
<?= $p("Your onboarding link will be sent to you shortly.") ?>
<?php endif; ?>

<?php if (!empty($data['discord_invite'])): ?>
<?= $hr() ?>
<?= $p("<strong>Step 2 — Join our Discord</strong><br>Join our Discord server so our team can guide you through the next steps.") ?>
<?= $btn($data['discord_invite'], 'Join Discord') ?>
<?php endif; ?>

<?php if (!empty($data['note'])): ?>
<?= $hr() ?>
<?= $p("<strong>Note from our team:</strong><br>" . nl2br(htmlspecialchars($data['note']))) ?>
<?php endif; ?>

<?= $hr() ?>

<?= $p("If you have any questions, feel free to reply to this email or contact us at <a href='mailto:support@lolboost.gg' style='color:#6366f1;'>support@lolboost.gg</a>.") ?>

<?= $p('Kind regards,<br>The LoLBoost.gg Team') ?>
