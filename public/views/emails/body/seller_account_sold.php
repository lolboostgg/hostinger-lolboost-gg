<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('You sold an account! 🎉') ?>

<?= $p("Hi <strong>{$data['username']}</strong>, great news — one of your listed accounts has just been sold on LoLBoost.gg!") ?>

<?= $hr() ?>

<?= $p("<strong>Account:</strong> {$data['account_title']}<br>
        <strong>Sale Price:</strong> €{$data['price']}<br>
        <strong>Your Earnings:</strong> €{$data['earnings']}<br>
        <strong>Buyer:</strong> {$data['buyer']}<br>
        <strong>Server:</strong> {$data['server']}<br>
        <strong>Rank:</strong> {$data['rank']}") ?>

<?= $hr() ?>

<?= $btn($data['account_url'], 'View Account') ?>

<?= $hr() ?>

<?= $p('Log in to your seller dashboard to review the sold account and manage any follow-up.') ?>

<?= $p('If you need help, we\'re here via <a href="https://lolboost.gg/contact#open-chat" style="color:#ffffff;text-decoration:underline;" target="_blank">Live Chat</a> or our <a href="https://lolboost.gg/discord" style="color:#ffffff;text-decoration:underline;" target="_blank">Discord</a>.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
