<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('Your Application on LoLBoost.gg') ?>

<?= $hr() ?>

<?= $p("Hi <strong>" . htmlspecialchars($data['username'] ?? 'there') . "</strong>,") ?>

<?= $p("Thank you for applying to join the LoLBoost.gg team. After reviewing your application, we have decided not to move forward at this time.") ?>

<?= $p("Please don't be discouraged — our team receives a high volume of applications, and decisions are often based on current availability and team needs rather than your abilities alone.") ?>

<?= $hr() ?>

<?= $p("<strong>You're welcome to reapply in the future.</strong><br>If you continue improving your skills and experience, we'd love to see your application again. Many of our current team members applied more than once.") ?>

<?php if (!empty($data['note'])): ?>
<?= $hr() ?>
<?= $p("<strong>Note from our team:</strong><br>" . nl2br(htmlspecialchars($data['note']))) ?>
<?php endif; ?>

<?= $hr() ?>

<?= $p("If you have any questions, feel free to contact us at <a href='mailto:support@lolboost.gg' style='color:#6366f1;'>support@lolboost.gg</a>.") ?>

<?= $p("We wish you all the best and good luck on the rift! 🎮") ?>

<?= $p('Kind regards,<br>The LoLBoost.gg Team') ?>
