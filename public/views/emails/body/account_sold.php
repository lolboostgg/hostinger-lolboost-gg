<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('LoL Account #' . $data['account_id']) ?>

<?= $p("Thank you for choosing us. Here's your brand new LoL Account!") ?>

<?= $p("Your account login information is as follows:") ?>

<?= $hr() ?>

<?= $p("<strong>Login:</strong> {$data['login']}
        <br />
        <strong>Password:</strong> {$data['password']}") ?>
<?php

if (!empty($data['data'])) {
    echo $p("<strong>Data:</strong> {$data['data']}");
}

if (isset($data['lol_account']) && $data['lol_account'] == 1) {
    // Marketplace account — show full credentials
    $emailLine = (!empty($data['email']) && strtolower(trim((string)$data['email'])) !== 'unverified')
        ? "<strong>Email:</strong> {$data['email']}<br>"
        : "<strong>Email:</strong> Unverified<br>";

    $emailPassLine = (!empty($data['email_password']))
        ? "<strong>Email Password:</strong> {$data['email_password']}<br>"
        : "";

    $instrLine = (!empty($data['delivery_instructions']))
        ? "<strong>Delivery Instructions:</strong> {$data['delivery_instructions']}<br>"
        : "";

    echo $p(
        $emailLine .
        $emailPassLine .
        "<strong>In-Game Name:</strong> {$data['in_game_name']}<br>" .
        $instrLine
    );
}
?>

<?= $hr() ?>

<?php
// Email access tutorial / notice for purchased marketplace accounts.
// Uses the default email layout styling only: no custom card/table styling here.
// Rules:
// - empty email, "unverified", or no @ => no extra notice
// - Getnada/Inboxes domains => tutorial for inboxes.com
// - yopmail.com => tutorial for yopmail.com
// - normal email without a real email password => pending notice
if (isset($data['lol_account']) && $data['lol_account'] == 1) {
    $emailRaw = (string)($data['email'] ?? '');
    $emailRaw = str_replace("\xC2\xA0", ' ', $emailRaw);
    $emailRaw = trim($emailRaw);
    $emailLc = strtolower($emailRaw);

    $isUnverifiedEmail = ($emailRaw === '' || $emailLc === 'unverified');
    $hasValidEmail = (!$isUnverifiedEmail && strpos($emailRaw, '@') !== false);

    $emailDomain = '';
    if ($hasValidEmail) {
        $emailDomain = strtolower(substr(strrchr($emailRaw, '@'), 1));
    }

    $inboxesDomains = [
        'blondmail.com', 'chapsmail.com', 'clowmail.com', 'dropjar.com', 'fivermail.com',
        'getairmail.com', 'getmule.com', 'getnada.com', 'gjmpmail.com', 'gjvmail.com',
        'guysmail.com', 'inboxbear.com', 'replyloop.com', 'robot-mail.com', 'spicysoda.com',
        'tafmail.com', 'temptami.com', 'tupmail.com', 'vomoto.com',
    ];

    $emailPassRaw = (string)($data['email_password'] ?? '');
    $emailPassRaw = str_replace("\xC2\xA0", ' ', $emailPassRaw);
    $emailPassRaw = trim($emailPassRaw);
    $emailPassLc = strtolower($emailPassRaw);
    $emailPassCompact = preg_replace('/[^a-z0-9]+/i', '', $emailPassLc);

    $missingPassValues = [
        '', '-', 'no', 'no password', 'none', 'null', 'unverified',
        'n/a', 'na', 'not provided', 'not available', 'no email password',
    ];
    $missingPassCompactValues = [
        '', 'no', 'nopassword', 'none', 'null', 'unverified',
        'na', 'notprovided', 'notavailable', 'noemailpassword',
    ];

    $hasEmailPassword = !(
        strlen($emailPassRaw) < 5
        || in_array($emailPassLc, $missingPassValues, true)
        || in_array($emailPassCompact, $missingPassCompactValues, true)
        || strpos($emailPassLc, 'no password') !== false
    );

    $safeEmail = htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8');

    if ($hasValidEmail && in_array($emailDomain, $inboxesDomains, true)) {
        echo $p(
            '<strong>How to access this email inbox</strong><br>' .
            'This account uses a temporary inbox provider. You can open the inbox directly without a password.<br><br>' .
            '<strong>Account email:</strong> ' . $safeEmail . '<br><br>' .
            '1. Open <a href="https://inboxes.com/" target="_blank">inboxes.com</a>.<br>' .
            '2. Click <strong>Get my first inbox</strong>.<br>' .
            '3. Enter the full account email shown above.<br>' .
            '4. You will get access to the mailbox and can receive verification emails there.'
        );
    } elseif ($hasValidEmail && $emailDomain === 'yopmail.com') {
        echo $p(
            '<strong>How to access this YOPmail inbox</strong><br>' .
            'This account uses YOPmail. You can open the inbox directly without a password.<br><br>' .
            '<strong>Account email:</strong> ' . $safeEmail . '<br><br>' .
            '1. Open <a href="https://yopmail.com/" target="_blank">yopmail.com</a>.<br>' .
            '2. Enter the account email shown above.<br>' .
            '3. Open the inbox to receive verification or transfer emails.'
        );
    } elseif ($hasValidEmail && !$hasEmailPassword) {
        echo $p(
            '<strong>Email access pending</strong><br>' .
            'No email password was provided for this account email. The seller will contact you as soon as possible to change the account email to your own address.<br><br>' .
            '<strong>Current account email:</strong> ' . $safeEmail . '<br><br>' .
            'Please keep an eye on the seller chat. The seller will guide you through the email change process there.'
        );
    }
}
?>

<?php if (isset($data['lol_account']) && $data['lol_account'] == 1 && !empty($data['account_id'])): ?>
<?= $btn('https://lolboost.gg/account/' . (int)$data['account_id'], 'View Account & Chat with Seller') ?>
<?php else: ?>
<?= $btn('https://account.riotgames.com/', 'Login to Riot Games') ?>
<?= $btn('https://lolboost.gg/profile/accounts', 'View My Accounts') ?>
<?php endif ?>

<?= $hr() ?>

<?= $p("<strong>⭐ Happy with your purchase?</strong><br>Leave us a quick review — it only takes a second and helps us a lot!") ?>

<table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="font-family:'Montserrat',sans-serif; max-width: 600px; margin: 10px auto 20px;">
    <tr>
        <td style="padding: 0 20px;">
            <?php
            $starImages = [
                5 => 'https://i.gyazo.com/2781cab48af81c1df7bec7b9a31f20f7.png',
                4 => 'https://i.gyazo.com/27d7e319b91b0e286600b3b1917b22bf.png',
                3 => 'https://i.gyazo.com/573a4441a0b3ec6ba471377ad500c267.png',
                2 => 'https://i.gyazo.com/fd1d94ea44d8886e00827127773de99b.png',
                1 => 'https://i.gyazo.com/ce7ea5def2b9ca74f086e6bb5d9efc0f.png',
            ];
            $trustpilotBaseUrl = 'https://www.trustpilot.com/evaluate/lolboost.gg?stars=';
            foreach ($starImages as $rating => $url) {
                $link = $trustpilotBaseUrl . $rating;
                echo '<p style="margin: 8px 0;">
                  <a href="' . $link . '" target="_blank" style="text-decoration:none; color: inherit;">
                    <img src="' . $url . '" alt="' . $rating . ' star rating" width="200" style="max-height: 36px; border: 0; line-height: 100%; outline: none; text-decoration: none; display: block; vertical-align: middle;">
                  </a>
                </p>';
            }
            ?>
        </td>
    </tr>
</table>

<?= $p('Please keep your credentials safe and do not share them with anyone.') ?>

<?= $p('If you need help, we\'re here via <a href="https://lolboost.gg/contact#open-chat" style="color:#ffffff;text-decoration:underline;" target="_blank">Live Chat</a> or our <a href="https://lolboost.gg/discord" style="color:#ffffff;text-decoration:underline;" target="_blank">Discord</a>.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
