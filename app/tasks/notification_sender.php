<?php
// trigger_notification_sender_async() fires this script with a curl call that
// abandons the connection after 200ms ("fire and forget"). Without this, PHP's
// default behavior is to kill the script once it notices the calling connection
// was dropped — which can (and does) happen *before* this reaches the Discord
// curl_exec() call further down, especially under back-to-back triggers (e.g.
// repeatedly reposting an order). The DB claim (is_sent=1) can still land since
// it happens early, making it look "processed" even though nothing was ever sent.
ignore_user_abort(true);
set_time_limit(120);

date_default_timezone_set('Europe/Berlin');
// Dynamic path resolution: works on both production and testing
$_ns_root = dirname(dirname(dirname(__FILE__))); // /public_html
require $_ns_root . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_ns_root . '/app/core/config.php';
require $_ns_root . '/app/core/functions.php';
// functions.php calls util_format_lol_classic_division() (and other LoL Classic
// helpers) but does not define them — they live here, and app/init.php loads
// this file for web requests. This cron bootstraps without init.php, so without
// it every LoL Classic notification died with "Call to undefined function".
require_once $_ns_root . '/app/core/legacy_features.php';
require $_ns_root . '/app/core/view.php';

// This worker runs regularly throughout the day. Use it as an additional
// safety net for delivered digital-good orders whose buyer did not confirm.
if (function_exists('dg_auto_complete_overdue_purchases')) {
    try {
        dg_auto_complete_overdue_purchases(250);
    } catch (Throwable $e) {
        error_log('Digital-good auto completion failed: ' . $e->getMessage());
    }
}




$mail = null;
function get_mailer(): PHPMailer {
    global $mail;
    if ($mail !== null) return $mail;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth     = true;
    $mail->SMTPKeepAlive = true;
    $mail->Host         = SMTP_HOST;
    $mail->Port         = (int) SMTP_PORT;
    $mail->Username     = SMTP_USER;
    $mail->Password     = SMTP_PASS;
    $mail->SMTPSecure   = ($mail->Port === 465)
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Timeout      = 10;
    $mail->CharSet      = 'UTF-8';
    $mail->Encoding     = 'base64';
    $mail->isHTML(true);
    $mail->setFrom(SMTP_USER, 'LoLBoost.gg');
    $mail->addReplyTo('support@lolboost.gg', 'LoLBoost.gg Support');
    return $mail;
}



function wrapEmailHtml($innerHtml)
{
    return '<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <style>
    table{border-collapse:collapse;}
    img{border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;display:block;max-width:100%;height:auto;}
    .container{max-width:600px;margin:0 auto;}
    @media screen and (max-width:600px){
      .outer{padding:12px 8px !important;}
      .pad{padding:22px 14px !important;}
    }
  </style>
</head>
<body style="margin:0;padding:0;background:#0b1020;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0b1020">
    <tr>
      <td align="center" class="outer" style="padding:16px 10px;">
        <!--[if (gte mso 9)|(IE)]>
        <table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td>
        <![endif]-->
        <div class="container" style="max-width:600px;margin:0 auto;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">
            <tr>
              <td class="pad" style="background:#151a2f;border-radius:14px;padding:28px 18px;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#ffffff;">
                ' . $innerHtml . '
              </td>
            </tr>
          </table>
        </div>
        <!--[if (gte mso 9)|(IE)]>
            </td>
          </tr>
        </table>
        <![endif]-->
      </td>
    </tr>
  </table>
</body>
</html>';
}

function notification_email_subject_load($type, $username)
{
    $data = [
        'welcome_booster' => [
            'preheader' => 'We\'re excited to have you on board as one of our boosters!',
            'subject' => 'Welcome to LoLBoost.gg!',
        ],
        'booster_trial_invite' => [
            'preheader' => 'You have been selected for the LoLBoost.gg booster trial phase.',
            'subject' => 'LoLBoost.gg, Booster Trial Invitation',
        ],
        'application_declined' => [
            'preheader' => 'Thank you for applying — here is an update on your application.',
            'subject' => 'Update on your LoLBoost.gg Application',
        ],
        'application_onboarding_invite' => [
            'preheader' => 'Your application was accepted — complete your onboarding and join our Discord.',
            'subject' => 'Welcome to LoLBoost.gg — Complete Your Onboarding',
        ],
        'welcome_egirl' => [
            'preheader' => 'Your GamerGirl account has been approved. Here are your login details.',
            'subject' => 'Welcome to LoLBoost.gg — Your GamerGirl Account is Ready!',
        ],
        'welcome_seller' => [
            'preheader' => 'Your seller account has been approved. Here are your login details.',
            'subject' => 'Your Seller Account on LoLBoost.gg has been approved',
        ],
        'invoice_paid' => [
            'preheader' => '',
            'subject' => 'Payment received!',
        ],
        'account_sold' => [
            'preheader' => '',
            'subject' => '⚡ Your brand new LoL account is here',
        ],
        'booster_money_added' => [
            'preheader' => '',
            'subject' => 'Money added to your balance',
        ],
        'booster_money_fined' => [
            'preheader' => '',
            'subject' => 'Money removed from your balance',
        ],
        'booster_balance_withdrawn' => [
            'preheader' => '',
            'subject' => 'Money withdrawn from your balance',
        ],
        'order_completed' => [
            'preheader' => '',
            'subject' => 'Order Completed! 🎉',
        ],
        'order_refunded' => [
            'preheader' => 'Your refund has been processed.',
            'subject' => 'Your order has been refunded',
        ],
        'client_password_recovery' => [
            'preheader' => '',
            'subject' => 'There was a request to recover your password.',
        ],
        'guest_client_welcome' => [
            'preheader' => 'Here are your login details.',
            'subject' => 'Welcome to LoLBoost.gg!',
        ],
        'order_claimed' => [
            'preheader' => '',
            'subject' => 'Your order has been started.',
        ],
        'booster_ready_request' => [
            'preheader' => '',
            'subject' => 'A customer requested you for an order.',
        ],
        'egirl_booking_paid' => [
            'preheader' => '',
            'subject' => 'A client booked a new session.',
        ],
        'order_paused' => [
            'preheader' => '',
            'subject' => 'Your order has been paused ⏸️',
        ],
        'booster_tip' => [
            'preheader' => '',
            'subject' => 'Tip added to your balance.',
        ],
        'client_custom_invoice' => [
            'preheader' => '',
            'subject' => 'Regarding you recent order.',
        ],
        'custom_invoice_paid' => [
            'preheader' => '',
            'subject' => 'Custom Invoice Paid',
        ],
        'poke_client' => [
            'preheader' => '',
            'subject' => 'You Have Been Poked',
        ],
        'poke_booster' => [
            'preheader' => '',
            'subject' => 'You Have Been Poked',
        ],
        'abandoned_unpaid_order_reminder' => [
            'preheader' => "Your checkout is waiting — claim an extra 5% discount before it expires.",
            'subject' => '🎁 Unpaid order reminder — extra 5% off inside',
        ],
        'item_purchased' => [
            'preheader' => 'Your item is ready — check your delivery instructions.',
            'subject' => '✅ Your item purchase is confirmed!',
        ],
        'item_sold' => [
            'preheader' => 'A customer just bought one of your items.',
            'subject' => '🛒 You made a sale!',
        ],
        'digital_good_purchased' => [
            'preheader' => 'Your digital good is on its way — the seller has been notified.',
            'subject' => '✅ Your digital good purchase is confirmed!',
        ],
        'digital_good_delivered' => [
            'preheader' => 'Your seller has delivered your digital good.',
            'subject' => 'Your digital good has been delivered!',
        ],
        'digital_good_sold' => [
            'preheader' => 'A customer just bought one of your digital goods.',
            'subject' => '🛒 You sold a digital good!',
        ],
        'seller_account_sold' => [
            'preheader' => 'A customer just bought one of your listed accounts.',
            'subject' => '🎉 You sold an account!',
        ],
        'seller_unread_message' => [
            'preheader' => "You missed a chat with your client — check the website and reply before they start wondering where you went!",
            'subject' => '🚨 You missed a chat with your client!',
        ],
        'client_unread_message' => [
            'preheader' => "You missed a chat with the seller — head back to the website to catch up!",
            'subject' => '🚨 You missed a chat with the seller!',
        ],
        'birthday_discount' => [
            'preheader' => 'Happy birthday! Your personal 48-hour discount code is inside.',
            'subject' => '🎂 Happy Birthday — your gift from LoLBoost.gg',
        ],
        'coming_soon_listing_live' => [
            'preheader' => 'The game and service you requested are now available.',
            'subject' => 'The listings you requested are now live!',
        ],
    ];
    if (isset($data[$type])) {
        return $data[$type];
    } else {
        return false;
    }
}

function notification_email_body_load($type, $data, $username, $recipient)
{
    $hr = function () {
        echo '<table style="font-family:\'Montserrat\',sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0"><tbody><tr><td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:\'Montserrat\',sans-serif;" align="left"><table height="0px" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%"><tbody><tr style="vertical-align: top"><td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%"><span>&#160;</span></td></tr></tbody></table>
    </td></tr></tbody></table>';
    };

    $btn = function ($link, $text) {
        echo '<table style="font-family:\'Montserrat\',sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0"><tbody><tr><td style="overflow-wrap:break-word;word-break:break-word;padding:30px 10px;font-family:\'Montserrat\',sans-serif;" align="left"><!--[if mso]><style>.v-button {background: transparent !important;}</style><![endif]--><div align="center"><!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $link . '" style="height:37px; v-text-anchor:middle; width:121px;" arcsize="11%"  stroke="f" fillcolor="#6366f1"><w:anchorlock/><center style="color:#FFFFFF;font-family:\'Montserrat\',sans-serif;"><![endif]--><a href="' . $link . '" target="_blank" class="v-button" style="box-sizing: border-box;display: inline-block;font-family:\'Montserrat\',sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #FFFFFF; background-color: #6366f1; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;"><span style="display:block;padding:10px 20px;line-height:120%;"><span style="font-size: 14px; line-height: 16.8px;font-weight:500;">' . $text . '</span></span></a><!--[if mso]></center></v:roundrect><![endif]--></div></td></tr></tbody></table>';
    };

    $p = function ($content) {
        echo '<table style="font-family:\'Montserrat\',sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0"><tbody><tr><td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:\'Montserrat\',sans-serif;" align="left"><div style="line-height: 140%; text-align: left; word-wrap: break-word;"><p style="font-size: 14px; line-height: 140%;">' . $content . '</p></div></td></tr></tbody></table>';
    };

    $img = function ($url) {
        echo '<table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0"><tbody><tr><td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tbody><tr><td style="padding-right: 0px;padding-left: 0px;" align="center"><img align="center" border="0" src="' . $url . '" alt="" title="" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 100%;max-width: 480px;border-radius: 5px;" width="480"></td></tr></tbody></table></td></tr></tbody></table>';
    };

    $title = function ($title) {
        echo '<table style="font-family:\'Montserrat\',sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
        <tbody>
            <tr>
                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:\'Montserrat\',sans-serif;" align="left">
    
                    <h1 style="margin: 0px; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: \'Montserrat\',sans-serif; font-size: 22px;"><strong>' . $title . '</strong></h1>
    
                </td>
            </tr>
        </tbody>
    </table>';
    };
    $subject = notification_email_subject_load($type, $username);
    if ($subject != false) {
        foreach ($data as $key => $value) {
            $data[$key] = base64_decode($value);
        }
        $data['preheader'] = $subject['preheader'];
        $data['username'] = $username ?? 'there';

        if ($type == 'poke_client' || $type == 'poke_booster') {
            $body['body'] = view_file_store("emails/body/poke", ['data' => $data, 'hr' => $hr, 'btn' => $btn, 'p' => $p, 'title' => $title, 'img' => $img]);
        } elseif ($type == 'welcome_seller') {
            $body['body'] = view_file_store("emails/body/welcome_seller", ['data' => $data, 'hr' => $hr, 'btn' => $btn, 'p' => $p, 'title' => $title, 'img' => $img]);
        } elseif ($type == 'welcome_egirl') {
            $body['body'] = view_file_store("emails/body/welcome_egirl", ['data' => $data, 'hr' => $hr, 'btn' => $btn, 'p' => $p, 'title' => $title, 'img' => $img]);
        } else {
            $body['body'] = view_file_store("emails/body/$type", ['data' => $data, 'hr' => $hr, 'btn' => $btn, 'p' => $p, 'title' => $title, 'img' => $img]);
        }

        return array_merge($subject, $body);
    } else {
        return false;
    }
}


function email_is_deliverable($email)
{
    $email = trim((string)$email);
    if ($email === '') return false;

    // Syntax check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

    // Extract and normalize domain
    $atPos = strrpos($email, '@');
    if ($atPos === false) return false;
    $domain = strtolower(substr($email, $atPos + 1));

    // IDN domains (e.g. ü) -> punycode
    if (function_exists('idn_to_ascii')) {
        $ascii = idn_to_ascii($domain, 0, defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0);
        if ($ascii !== false && $ascii !== null && $ascii !== '') {
            $domain = $ascii;
        }
    }

    // DNS check: MX preferred. Some setups may accept mail via A/AAAA fallback.
    if (checkdnsrr($domain, 'MX')) return true;
    if (checkdnsrr($domain, 'A')) return true;
    if (checkdnsrr($domain, 'AAAA')) return true;

    return false;
}


function notification_email_send($email, $username, $recipient, $type, $data = [])
{
    $mail = get_mailer();

    // Prevent bounces for clearly invalid/non-existent domains
    if (!email_is_deliverable($email)) {
        return false;
    }

    $mail->addAddress($email);
    $email_data = notification_email_body_load($type, $data, $username, $recipient);
    if ($email_data != false) {
        $mail->Subject = $email_data['subject'];
        $mail->Body = wrapEmailHtml($email_data['body']);
        $mail->AltBody = strip_tags($email_data['body']);
        try {
            $mail->send();
            $sent = true;
        } catch (Exception $e) {
            error_log('Notification email failed for ' . $type . ' to ' . $email . ': ' . $e->getMessage() . ' mailer=' . $mail->ErrorInfo);
            $sent = false;
        }
        return $sent;
    } else {
        return false;
    }
}

/**
 * Sends a one-time email once the requested marketplace section contains an
 * active listing. The notification sender process lock prevents double sends.
 */
function coming_soon_send_available_listing_emails(): void
{
    global $db;

    try {
        $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS client_id INT UNSIGNED DEFAULT NULL AFTER email");
        $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS notified_at DATETIME DEFAULT NULL AFTER updated_at");
        $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS notification_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER notified_at");
        $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS notification_error VARCHAR(255) DEFAULT NULL AFTER notification_attempts");
        $rows = $db->run(
            "SELECT csn.*, c.username AS client_username
             FROM coming_soon_notifications csn
             LEFT JOIN clients c ON c.id = csn.client_id
             WHERE csn.notified_at IS NULL AND COALESCE(csn.notification_attempts, 0) < 3
             ORDER BY csn.id ASC LIMIT 100"
        ) ?: [];
    } catch (\Throwable $e) {
        error_log('[coming-soon-mail] Could not prepare registrations: ' . $e->getMessage());
        return;
    }

    foreach ($rows as $row) {
        $slug = strtolower(trim((string)($row['game_slug'] ?? '')));
        $service = strtolower(trim((string)($row['service_type'] ?? '')));
        if ($slug === '' || $service === '') continue;

        try {
            $game = $db->row("SELECT id, slug, name FROM games WHERE LOWER(slug) = LOWER(?) LIMIT 1", $slug);
            $gameId = (int)($game['id'] ?? 0);
            $available = false;

            if ($service === 'accounts') {
                $available = (bool)$db->row(
                    "SELECT id FROM selling_accounts
                     WHERE active = 1 AND sold = 0 AND LOWER(TRIM(game)) = LOWER(?) LIMIT 1",
                    $slug
                );
            } elseif ($service === 'items') {
                $available = (bool)$db->row(
                    "SELECT id FROM selling_items
                     WHERE active = 1 AND COALESCE(stock, 0) > 0
                       AND ((? > 0 AND game_id = ?) OR LOWER(TRIM(game)) = LOWER(?)) LIMIT 1",
                    $gameId,
                    $gameId,
                    $slug
                );
            } elseif (in_array($service, ['top-ups', 'topups', 'top_ups'], true)) {
                $service = 'top-ups';
                $available = (bool)$db->row(
                    "SELECT id FROM selling_topups
                     WHERE active = 1 AND COALESCE(stock, 0) > 0
                       AND ((? > 0 AND game_id = ?) OR LOWER(TRIM(game_slug)) = LOWER(?)) LIMIT 1",
                    $gameId,
                    $gameId,
                    $slug
                );
            }

            if (!$available) continue;

            $email = trim((string)($row['email'] ?? ''));
            $username = trim((string)($row['client_username'] ?? '')) ?: 'there';
            $labels = ['accounts' => 'Accounts', 'items' => 'Items', 'top-ups' => 'Top Ups'];
            $gameName = trim((string)($row['game_name'] ?? $game['name'] ?? $slug));
            $listingUrl = rtrim((string)BASE_URL, '/') . '/' . rawurlencode($slug) . '/' . $service;
            $payload = [
                'game_name' => base64_encode($gameName),
                'service_name' => base64_encode($labels[$service] ?? ucwords(str_replace(['-', '_'], ' ', $service))),
                'listing_url' => base64_encode($listingUrl),
            ];

            $sent = notification_email_send($email, $username, 'client', 'coming_soon_listing_live', $payload);
            get_mailer()->clearAddresses();

            if ($sent) {
                $db->run(
                    "UPDATE coming_soon_notifications
                     SET notified_at = NOW(), notification_error = NULL
                     WHERE id = ? AND notified_at IS NULL",
                    (int)$row['id']
                );
            } else {
                $db->run(
                    "UPDATE coming_soon_notifications
                     SET notification_attempts = notification_attempts + 1, notification_error = ?
                     WHERE id = ? AND notified_at IS NULL",
                    'Email delivery failed',
                    (int)$row['id']
                );
            }
        } catch (\Throwable $e) {
            get_mailer()->clearAddresses();
            try {
                $db->run(
                    "UPDATE coming_soon_notifications
                     SET notification_attempts = notification_attempts + 1, notification_error = ?
                     WHERE id = ? AND notified_at IS NULL",
                    substr($e->getMessage(), 0, 255),
                    (int)$row['id']
                );
            } catch (\Throwable $ignored) {}
            error_log('[coming-soon-mail] Registration #' . (int)($row['id'] ?? 0) . ' failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('notification_discord_game_route')) {
function notification_discord_game_route($gameRaw)
{
    global $db;
    static $config = [
        'lol'         => ['role_id' => '926928458490212412',  'thread_id' => '1528087731891343570', 'icon' => 'league-of-legends'],
        'lol_classic' => ['role_id' => '926928458490212412',  'thread_id' => '1528087671522459880', 'icon' => 'lol-classic'],
        'tft'         => ['role_id' => '1476574350735048704', 'thread_id' => '1528087538827395142', 'icon' => 'teamfight-tactics'],
        'val'         => ['role_id' => '1149987241284878416', 'thread_id' => '1528087576894902334', 'icon' => 'valorant'],
        'wild-rift'   => ['role_id' => '1528351346137501777', 'thread_id' => '1528087459924279336', 'icon' => 'lol-wild-rift'],
        'ow2'         => ['role_id' => '1528351318207369279', 'thread_id' => '1528087282358419626', 'icon' => 'overwatch-2'],
        'rl'          => ['role_id' => '1528351285890519192', 'thread_id' => '1528087225949224970', 'icon' => 'rocket-league'],
        'apex'        => ['role_id' => '1528351266445459526', 'thread_id' => '1528087157074427934', 'icon' => 'apex-legends'],
        'rivals'      => ['role_id' => '1528351186602688633', 'thread_id' => '1528087090611486751', 'icon' => 'marvel-rivals'],
        'fortnite'    => ['role_id' => '1529540249947082862', 'thread_id' => '1529515122610143312', 'icon' => 'fortnite'],
        'cs2'         => ['role_id' => '1529540037153132816', 'thread_id' => '1529538564885315654', 'icon' => 'counter-strike-2'],
    ];
    // Base webhook of the #boost-panel channel. Each game gets its own thread via ?thread_id=.
    static $webhookBase = 'https://discord.com/api/webhooks/1528353465800196146/kceuOj9RBUVb0Iy6h3i37eyGepb0SaSwGrAk66CVyx7wodRRyxev7lWYiCoB3lv6IjFL';
    static $aliases = [
        'league-of-legends' => 'lol',
        'lol-classic' => 'lol_classic', 'league-of-legends-classic' => 'lol_classic',
        'teamfight-tactics' => 'tft', 'teamf' => 'tft', 'teamfi' => 'tft',
        'valorant' => 'val', 'valor' => 'val', 'valo' => 'val',
        'wildrift' => 'wild-rift', 'lol-wild-rift' => 'wild-rift', 'lol_wild_rift' => 'wild-rift',
        'overwatch' => 'ow2', 'overwatch-2' => 'ow2', 'overwatch2' => 'ow2',
        'rocket-league' => 'rl', 'rocketleague' => 'rl', 'rocket_league' => 'rl',
        'apex-legends' => 'apex', 'apexlegends' => 'apex',
        'marvel-rivals' => 'rivals', 'marvel_rivals' => 'rivals',
        'counter-strike-2' => 'cs2', 'counterstrike2' => 'cs2', 'counter-strike' => 'cs2', 'csgo' => 'cs2',
    ];

    $g = strtolower(trim((string) $gameRaw));
    $gameRow = [];

    // Resolve the order game through the games table. boost_forms can contain
    // a slug, a display name or (for newer generic forms) the numeric game ID.
    try {
        if ($g !== '') {
            if (ctype_digit($g)) {
                $rows = $db->run('SELECT * FROM games WHERE id = ? LIMIT 1', (int)$g);
            } else {
                $variants = array_values(array_unique([$g, str_replace('_', '-', $g), str_replace('-', '_', $g)]));
                $placeholders = implode(',', array_fill(0, count($variants), '?'));
                $rows = $db->run(
                    "SELECT * FROM games
                      WHERE LOWER(slug) IN ($placeholders)
                         OR LOWER(name) IN ($placeholders)
                      LIMIT 1",
                    ...[...$variants, ...$variants]
                );
            }
            if (!empty($rows[0]) && is_array($rows[0])) {
                $gameRow = $rows[0];
                $dbSlug = strtolower(trim((string)($gameRow['slug'] ?? '')));
                if ($dbSlug !== '') $g = $dbSlug;
            }
        }
    } catch (\Throwable $e) {
        error_log('[notification_discord_game_route] Could not resolve game from database: ' . $e->getMessage());
    }

    if (isset($aliases[$g])) {
        $g = $aliases[$g];
    }
    $hasConfiguredRoute = isset($config[$g]);
    $route = $config[$g] ?? $config['val'];

    // These columns are optional so the change works immediately with the
    // current schema and automatically supports newly configured DB games.
    foreach (['discord_role_id', 'discord_ping_role_id', 'role_discord_id'] as $column) {
        if (!empty($gameRow[$column])) {
            $route['role_id'] = trim((string)$gameRow[$column]);
            break;
        }
    }
    foreach (['discord_thread_id', 'boost_panel_thread_id', 'order_thread_id'] as $column) {
        if (!empty($gameRow[$column])) {
            $route['thread_id'] = trim((string)$gameRow[$column]);
            break;
        }
    }
    // Fortnite New Order notifications have a dedicated fixed forum thread.
    if ($g === 'fortnite') {
        $route['thread_id'] = '1529515122610143312';
        $route['role_id'] = '1529540249947082862';
    }
    if ($g === 'cs2') {
        $route['thread_id'] = '1529538564885315654';
        $route['role_id'] = '1529540037153132816';
    }
    // Discord sender icons deliberately use the canonical filename in
    // /public/assets/website/images/icons/<game>.png. A DB icon may be an
    // admin-relative upload path and is not necessarily fetchable by Discord.
    if (!$hasConfiguredRoute && !empty($gameRow['slug'])) {
        $route['icon'] = trim((string)$gameRow['slug']);
    }
    // Prefer a plain per-game channel once its URL is configured in
    // lb_discord_channel_webhook(); otherwise keep posting into the forum thread.
    $gameChannel = function_exists('lb_discord_channel_webhook') ? lb_discord_channel_webhook('new_order', $g) : '';
    if ($gameChannel !== '') {
        $route['webhook_url'] = $gameChannel;
    } else {
        // The webhook belongs to the boost-panel forum/channel, but each game must be
        // posted into its configured thread. notification_discord_send() appends
        // `wait=true` with `&`, because this URL already contains a query string.
        $route['webhook_url'] = $webhookBase . '?thread_id=' . rawurlencode((string)$route['thread_id']);
    }
    // util_game_icon_url() also checks the games table (single source of truth used everywhere
    // else on the site), so this stays correct even if a game's icon changes there. The "?v=2"
    // cache-bust guards against Discord having cached a broken fetch of this URL from before —
    // once Discord caches "no image" for a given avatar_url, it won't retry that exact URL.
    $iconUrl = function_exists('util_game_icon_url') ? util_game_icon_url($route['icon']) : '';
    if ($iconUrl === '') $iconUrl = ASSET_URL . '/website/images/icons/' . $route['icon'] . '.png';
    if (strpos($iconUrl, '/') === 0) {
        $iconUrl = rtrim(BASE_URL, '/') . $iconUrl;
    }
    // Keep the current installation host (e.g. progress.lolboost.gg) so Discord
    // fetches the same icon that is actually deployed with this installation.
    $route['icon_url'] = $iconUrl . (str_contains($iconUrl, '?') ? '&' : '?') . 'v=6';
    return $route;
}
}

if (!function_exists('lb_format_discord_server')) {
    // Legacy forms store short region codes (euw, na, eune, ...) which read well
    // uppercased. Newer forms store full, already-proper-case names (Europe,
    // North America, Asia Pacific, ...) which strtoupper() would mangle into
    // "EUROPE". Only uppercase values that look like a short legacy code.
    function lb_format_discord_server($value): string {
        $value = trim((string)$value);
        if ($value === '') return '';
        if (preg_match('/^[a-z]{2,5}$/', $value)) {
            return strtoupper($value);
        }
        return $value;
}
}

if (!function_exists('lb_discord_lol_classic_rank')) {
    function lb_discord_lol_classic_rank($tier, $division = null, $lp = null): string {
        $tier = max(0, min(7, (int)$tier));
        $label = function_exists('util_lol_classic_rank_name')
            ? util_lol_classic_rank_name($tier)
            : ([0 => 'Unranked', 1 => 'Salt', 2 => 'Wood', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum', 6 => 'Diamond', 7 => 'Legend'][$tier] ?? 'Unranked');

        $division = (int)$division;
        if ($tier > 0 && $tier < 7 && $division >= 1 && $division <= 4) {
            $label .= ' ' . (function_exists('util_format_lol_classic_division')
                ? util_format_lol_classic_division($division)
                : ([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'][$division] ?? ''));
        }
        if ($tier === 7 && $lp !== null && $lp !== '' && is_numeric($lp)) {
            $label .= ' (' . max(0, (int)$lp) . ' LP)';
        }
        return trim($label);
    }
}

if (!function_exists('lb_discord_add_lol_classic_fields')) {
    function lb_discord_add_lol_classic_fields(array &$body, array $order): void {
        $type = strtolower(str_replace('-', '_', trim((string)($order['type'] ?? ''))));
        $formId = (int)($order['form_id'] ?? 0);
        if ($formId === 35) $type = 'pro_games';
        elseif ($formId === 36) $type = 'duo_pass';

        $add = static function (string $name, string $value) use (&$body): void {
            if ($value === '') return;
            $body['embeds'][0]['fields'][] = ['name' => $name, 'value' => $value, 'inline' => true];
        };
        $startRank = lb_discord_lol_classic_rank($order['start_tier'] ?? 0, $order['start_division'] ?? null, $order['start_lp'] ?? null);
        $endRank = lb_discord_lol_classic_rank($order['end_tier'] ?? 0, $order['end_division'] ?? null, $order['end_lp'] ?? null);
        $matches = max(0, (int)($order['matches'] ?? 0));
        $hours = max(0, (int)($order['hours'] ?? 0));

        if ($type === 'rank') {
            $add('Start Rank', $startRank);
            $add('Target Rank', $endRank);
        } elseif ($type === 'win') {
            $add('Start Rank', $startRank);
            $add('Wins Amount', $matches . ' Wins');
        } elseif ($type === 'placement') {
            $add('Last Season Rank', $startRank);
            $add('Placements Amount', $matches . ' Placements');
        } elseif ($type === 'pro_games' || $type === 'progames') {
            $add('Start Rank', $startRank);
            $add('Amount', $matches . ' Pro Games');
        } elseif ($type === 'duo_pass') {
            $add('Start Rank', $startRank);
            $add('Amount', $hours . ' Hours');
        } elseif ($type === 'coaching') {
            $add('Current Rank', $startRank);
            $add('Coaching Amount', $hours . ' Hours');
        } elseif ($type === 'level') {
            $add('Start Level', (string)($order['start_level'] ?? $order['level'] ?? ''));
            $add('Target Level', (string)($order['end_level'] ?? $order['target_level'] ?? ''));
        } else {
            $add('Start Rank', $startRank);
            if ($matches > 0) $add('Amount', $matches . ' Games');
            elseif ($hours > 0) $add('Amount', $hours . ' Hours');
        }

        if (!empty($order['queue_type'])) {
            $add('Queue Type', util_format_default_type($order['queue_type']));
        }
        if (!empty($order['server'])) {
            $add('Server', lb_format_discord_server($order['server']));
        }
    }
}

function notification_discord_body_load($type, $data = [], $notif = [])
{
    $body = [];
    $webhook_url = '';
    $fallback_webhook_url = '';
    $empty_row = [
        "name" => "EMPTY_ROW",
        "value" => "EMPTY_ROW",
        "inline" => false,
    ];
    $empty_row_inl = [
        "name" => "EMPTY_ROW",
        "value" => "EMPTY_ROW",
        "inline" => true,
    ];
    switch ($type) {
        case 'order_ping':
            $order = db_get_row('orders', ['id' => base64_decode($data['order_id'])]);
            if ($order != false) {
                $order_form = db_get_row('boost_forms', ['id' => $order['form_id']]);
                $order_options = db_get_row('order_options', ['order_id' => $order['id']]);
                $note = db_get_row('order_notes', ['order_id' => $order['id']]);
                // Older/reposted orders do not always have both related rows.
                // array_merge(false, ...) throws on PHP 8 and used to make the
                // queued Discord notification disappear without being sent.
                $order_form = is_array($order_form) ? $order_form : [];
                $order_options = is_array($order_options) ? $order_options : [];
                $orderGame = trim((string)($order['game'] ?? ''));
                if ($orderGame === '') $orderGame = trim((string)($order_form['game'] ?? ''));
                if ($orderGame === '') $orderGame = trim((string)($order['game_id'] ?? $order_form['game_id'] ?? ''));
                $order = array_merge($order_form, $order_options, $order);

                // LoL Classic uses the existing League of Legends Discord webhook/thread.
                // Only the displayed ranks are formatted with the Classic rank system.
                $orderFormId = (int)($order['form_id'] ?? 0);
                $isLolClassic = in_array($orderFormId, [30, 31, 32], true);
                $order['game'] = $orderGame;
                // Discord routing per game (role ping + thread webhook + icon)
                $route = notification_discord_game_route($order['game'] ?? '');
                $isCounterStrikeOrder = in_array(strtolower(trim((string)($order['game'] ?? ''))), ['cs2', 'counter-strike-2', 'counterstrike2', 'counter-strike', 'csgo'], true);
                $mention = !empty($route['role_id']) ? '<@&' . $route['role_id'] . '>' : '';
                // New orders and manual reposts use the same per-game thread.
                $webhook_url = $route['webhook_url'];
                $duo = $order['is_duo'] == 1 ? 'Duo' : 'Solo';
                $url = BSTR_URL . "/order/" . $order['id'];
                $body['username'] = 'New Order';
                // No avatar_url: each per-game channel webhook carries its own icon,
                // configured in Discord. Sending avatar_url here would override it.
                $newOrderRegion = $isCounterStrikeOrder ? '' : trim((string)lb_format_discord_server($order['server'] ?? ''));
                $body['content'] = ($mention !== '' ? $mention . ' ' : '') . 'New' . ($newOrderRegion !== '' ? ' ' . $newOrderRegion : '') . ' Order - [Claim Order](' . $url . ')';
                if ($isLolClassic) {
                    $classicStart = lb_discord_lol_classic_rank(
                        $order['start_tier'] ?? 0,
                        $order['start_division'] ?? null,
                        $order['start_lp'] ?? null
                    );
                    $classicEnd = lb_discord_lol_classic_rank(
                        $order['end_tier'] ?? 0,
                        $order['end_division'] ?? null,
                        $order['end_lp'] ?? null
                    );
                    $body['embeds'][0]['title'] = $classicStart . ' > ' . $classicEnd;
                } else {
                    $body['embeds'][0]['title'] = util_format_boost_overview($order['game'], $order['type'], $order);
                }
                $body['embeds'][0]['author']['name'] = $order['name'];
                $body['embeds'][0]['color'] = 5793266;
                $body['embeds'][0]['fields'][] = [
                    "name" => "Order ID",
                    "value" => "#" . $order['id'],
                    "inline" => true
                ];
                if (in_array((int)($order['form_id'] ?? 0), [4, 19, 29], true)) {
                    $teamSize = max(1, min(4, (int)($order['boosters'] ?? $order['hours'] ?? 1)));
                    $body['embeds'][0]['fields'][] = [
                        "name" => "Boosters",
                        "value" => (string)$teamSize,
                        "inline" => true
                    ];
                }
                if ($note != false) {
                $body['embeds'][0]['fields'][] = [
                    "name" => "Note",
                    "value" => decode_entities($note['order_note']),
                    "inline" => false
                ];
            }
                $notificationGame = strtolower(trim((string)($order['game'] ?? '')));
                if ($isLolClassic) {
                    lb_discord_add_lol_classic_fields($body, $order);
                } elseif ($notificationGame === 'lol') {
                    switch ($order['type']) {
                        case 'rank':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('lol', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] != 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End Rank",
                                    "value" => util_format_rank_display('lol', $order["end_tier"] ?? null, $order["end_division"] ?? null, $order['end_lp'] ?? null),
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start LP",
                                "value" => ($order['start_lp'] ?? null) . ' LP',
                                "inline" => true
                            ];
                            if ($order['end_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End LP",
                                    "value" => ($order['end_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "LP Gain",
                                "value" => ($order['lp_gain'] ?? null) . ' LP',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'win':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('lol', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Start LP",
                                    "value" => ($order['end_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Wins Amount",
                                "value" => $order['matches'] . ' Wins',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'placement':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Last Season Rank",
                                "value" => util_format_rank_display('lol', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Placements Amount",
                                "value" => $order['matches'] . ' Placements',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'normal':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Queue Type",
                                "value" => util_format_default_type($order['queue_type']),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Normals Amount",
                                "value" => $order['matches'] . ' Normals',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                    }
                } elseif ($order['game'] === 'tft') {
                    // TFT uses the same tier/division/LP concepts (Master+ is LP based)
                    switch ($order['type']) {
                        case 'rank':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('tft', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if (($order['start_tier'] ?? null) != 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End Rank",
                                    "value" => util_format_rank_display('tft', $order["end_tier"] ?? null, $order["end_division"] ?? null, $order['end_lp'] ?? null),
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start LP",
                                "value" => ($order['start_lp'] ?? null) . ' LP',
                                "inline" => true
                            ];
                            if (($order['end_tier'] ?? null) == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End LP",
                                    "value" => ($order['end_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            if (isset($order['lp_gain'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "LP Gain",
                                    "value" => ($order['lp_gain'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'win':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('tft', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if (($order['start_tier'] ?? null) == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Start LP",
                                    "value" => ($order['start_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Wins Amount",
                                "value" => ($order['matches'] ?? 0) . ' Wins',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'placement':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Last Season Rank",
                                "value" => util_format_rank_display('tft', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Placements Amount",
                                "value" => ($order['matches'] ?? 0) . ' Placements',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                    }
                } elseif ($order['game'] === 'val') {
                    switch ($order['type']) {
                        case 'rank':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('val', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_rr'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] != 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End Rank",
                                    "value" => util_format_rank_display('val', $order["end_tier"] ?? null, $order["end_division"] ?? null, $order['end_rr'] ?? null),
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start RR",
                                "value" => ($order['start_rr'] ?? null) . ' RR',
                                "inline" => true
                            ];
                            if ($order['end_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End RR",
                                    "value" => ($order['end_rr'] ?? null) . ' RR',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                        case 'win':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('val', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_rr'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Start RR",
                                    "value" => ($order['end_rr'] ?? null) . ' RR',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Wins Amount",
                                "value" => $order['matches'] . ' Wins',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                        case 'placement':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Last Season Rank",
                                "value" => util_format_rank_display('val', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_rr'] ?? null),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Placements Amount",
                                "value" => $order['matches'] . ' Placements',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                        case 'normal':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Normals Amount",
                                "value" => $order['matches'] . ' Normals',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                    }
                } else {
                    // Games outside lol/tft/val (Rocket League, Apex, Marvel Rivals, Wild Rift, Overwatch 2, ...)
                    // use their own rank terminology from the boost form JSON instead of LP/RR.
                    $jsonData = !empty($order['form_id']) ? lb_load_boost_form_json_by_id($order['form_id']) : [];
                    if (!empty($jsonData)) {
                        $rankConfig = function_exists('lb_generic_game_rank_config')
                            ? lb_generic_game_rank_config((string)($order['game'] ?? ''))
                            : null;
                        if (is_array($rankConfig)) {
                            $jsonData['form_config'] = array_replace(
                                is_array($jsonData['form_config'] ?? null) ? $jsonData['form_config'] : [],
                                $rankConfig
                            );
                        }
                        $isGamesService = in_array((int)($order['form_id'] ?? 0), [38, 43, 44, 46, 49, 50, 52], true)
                            || in_array(strtolower(trim((string)($order['type'] ?? ''))), ['win', 'placement'], true)
                            || in_array(strtolower(trim((string)($order['slug'] ?? ''))), ['win-boost', 'placement', 'placement-boost', 'placements-boost'], true);
                        $isPlacementService = in_array((int)($order['form_id'] ?? 0), [44, 50], true)
                            || strtolower(trim((string)($order['type'] ?? ''))) === 'placement'
                            || in_array(strtolower(trim((string)($order['slug'] ?? ''))), ['placement', 'placement-boost', 'placements-boost'], true);
                        $startTier = (int)($order['start_tier'] ?? 0);
                        $endTier = (int)($order['end_tier'] ?? 0);
                        if ($startTier > 0 || $isPlacementService) {
                            $body['embeds'][0]['fields'][] = [
                                "name" => $isPlacementService ? "Last Season Rank" : "Start Rank",
                                "value" => lb_summary_rank_display($jsonData, $startTier, $order['start_division'] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                        }
                        if (!$isGamesService && $endTier > 0) {
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Target Rank",
                                "value" => lb_summary_rank_display($jsonData, $endTier, $order['end_division'] ?? null, $order['end_lp'] ?? null),
                                "inline" => true
                            ];
                        }
                    }
                    if (!empty($order['matches'])) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => "Amount",
                            "value" => $order['matches'] . ' ' . ucfirst((string)$order['type']),
                            "inline" => true
                        ];
                    }
                    $isOverwatchOrder = in_array((int)($order['form_id'] ?? 0), [48, 49, 50], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['ow2', 'overwatch', 'overwatch-2', 'overwatch2'], true);
                    $isMarvelOrder = in_array((int)($order['form_id'] ?? 0), [37, 38, 40, 41], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['rivals', 'marvel-rivals', 'marvel_rivals', 'marvelrivals'], true);
                    $isRocketLeagueOrder = in_array((int)($order['form_id'] ?? 0), [42, 43, 44], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['rl', 'rocket-league', 'rocket_league', 'rocketleague'], true);
                    $isWildRiftOrder = in_array((int)($order['form_id'] ?? 0), [51, 52], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['wild-rift', 'wildrift', 'lol-wild-rift', 'lol_wild_rift'], true);
                    if (!empty($order['queue_type']) && !($isWildRiftOrder && $isGamesService && !$isPlacementService)) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => $isOverwatchOrder ? "Role" : ($isWildRiftOrder ? "Ranked Marks" : "Queue Type"),
                            "value" => $isWildRiftOrder ? util_format_ranked_marks($order['queue_type']) : util_format_default_type($order['queue_type']),
                            "inline" => true
                        ];
                    }
                    if (($isOverwatchOrder || $isMarvelOrder || $isRocketLeagueOrder) && !empty($order['platform'])) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => "Platform",
                            "value" => util_format_platform($order['platform']),
                            "inline" => true
                        ];
                    }
                    if (!$isCounterStrikeOrder && !empty($order['server'])) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => "Server",
                            "value" => lb_format_discord_server($order['server']),
                            "inline" => true
                        ];
                    }
                }
                if ($order['is_duo'] == 1) {
                    $body['embeds'][0]['fields'][] = [
                        "name" => "Duo",
                        "value" => ":green_circle: Yes",
                        "inline" => true
                    ];
                }
                $options = load_boost_extra_options();
                $option_count = 0;
                foreach ($options as $option) {
                    if (!empty($order[$option]) && $order[$option] != 0) {
                        $option_count++;
                    }
                }
                if ($option_count > 0) {
                    $body['embeds'][0]['fields'][] = [
                        "name" => "**__Options:__ :**",
                        "value" => "EMPTY_ROW"
                    ];
                    foreach ($options as $option) {
                        if (!empty($order[$option]) && $order[$option] != 0) {
                            $option_data = util_format_option_inline($option, $order[$option]);
                            $body['embeds'][0]['fields'][] = [
                                "name" => $option_data[0],
                                "value" => $option_data[1],
                                "inline" => true
                            ];
                        }
                    }
                }
            }
            // TFT: remove LP Gain and Queue Type from embed
            $is_tft_order = ($order['game'] === 'tft') || in_array((int)($order['form_id'] ?? 0), [21,22,23,24,25], true);
            if ($is_tft_order && isset($body['embeds'][0]['fields']) && is_array($body['embeds'][0]['fields'])) {
                $body['embeds'][0]['fields'] = array_values(array_filter($body['embeds'][0]['fields'], function ($f) {
                    $name = strtolower(trim($f['name'] ?? ''));
                    return $name !== 'lp gain' && $name !== 'queue type';
                }));
            }

            break;
        case 'booster_request':
        case 'booster_ready_request':
            $order = db_get_row('orders', ['id' => base64_decode($data['order_id'])]);
            if ($order != false) {
                $order_form = db_get_row('boost_forms', ['id' => $order['form_id']]);
                $order_options = db_get_row('order_options', ['order_id' => $order['id']]);
                $note = db_get_row('order_notes', ['order_id' => $order['id']]);
                $order = array_merge($order_form, $order_options, $order);
                // Aggregate all explicitly requested boosters into one Discord post.
                $boosterIds = [];
                if (!empty($data['booster_ids'])) {
                    $rawBoosterIds = base64_decode((string)$data['booster_ids'], true);
                    if ($rawBoosterIds !== false) {
                        $boosterIds = array_values(array_unique(array_filter(array_map(
                            'intval',
                            preg_split('/[\s,|]+/', (string)$rawBoosterIds, -1, PREG_SPLIT_NO_EMPTY)
                        ))));
                    }
                }
                $boosterId = 0;
                if (!empty($data['booster_id'])) {
                    $rawBoosterId = base64_decode((string) $data['booster_id'], true);
                    $boosterId = (int) ($rawBoosterId !== false ? $rawBoosterId : 0);
                }
                if ($boosterId <= 0 && !empty($order['booster_id'])) {
                    $boosterId = (int) $order['booster_id'];
                }
                if (empty($boosterIds) && $boosterId > 0) {
                    $boosterIds[] = $boosterId;
                }

                // Live lookup of every selected booster's current Discord ID.
                $mentions = [];
                $mentionUserIds = [];
                foreach ($boosterIds as $requestedBoosterId) {
                    $booster = db_get_row('boosters', ['id' => (int)$requestedBoosterId]);
                    if ($booster != false) {
                        $did = trim((string) ($booster['discord_id'] ?? ''));
                        if ($did !== '' && ctype_digit($did)) {
                            $mentions[] = "<@{$did}>";
                            $mentionUserIds[] = $did;
                        } else {
                            $display = trim((string)($booster['username'] ?? $booster['discord'] ?? 'Booster'));
                            $mentions[] = '**' . ($display !== '' ? $display : 'Booster') . '**';
                        }
                    }
                }
                $mention = trim(implode(' ', array_unique($mentions)));

                // Legacy fallback for older notifications that stored booster_discord_id in notif data
                if ($mention === '' && !empty($data['booster_discord_id'])) {
                    $legacy = base64_decode((string) $data['booster_discord_id'], true);
                    $legacy = trim((string) ($legacy !== false ? $legacy : ''));
                    if ($legacy !== '' && ctype_digit($legacy)) {
                        $mention = "<@{$legacy}>";
                    }
                }

                // If no ping possible, show booster name instead (no mention)
                if ($mention === '') {
                    $mention = '**Booster**';
                }
                // Discord routing per game (thread webhook + icon)
                $route = notification_discord_game_route($order['game'] ?? '');
                $isCounterStrikeOrder = in_array(strtolower(trim((string)($order['game'] ?? ''))), ['cs2', 'counter-strike-2', 'counterstrike2', 'counter-strike', 'csgo'], true);
                $webhook_url = $route['webhook_url'];
                $duo = $order['is_duo'] == 1 ? 'Duo' : 'Solo';
                $url = BSTR_URL . "/order/" . $order['id'];
                $body['username'] = 'Booster Request';
                // No avatar_url — the channel webhook's own icon is used.
                $requestRegion = $isCounterStrikeOrder ? '' : trim((string)lb_format_discord_server($order['server'] ?? ''));
                $body['content'] = $mention . ' You were requested for an' . ($requestRegion !== '' ? ' ' . $requestRegion : '') . ' Order - [View Order](' . $url . ')';
                if (!empty($mentionUserIds)) {
                    $body['allowed_mentions'] = ['users' => array_values(array_unique($mentionUserIds))];
                }
                $body['embeds'][0]['title'] = util_format_boost_overview($order['game'], $order['type'], $order);
                $body['embeds'][0]['author']['name'] = $order['name'];
                $body['embeds'][0]['color'] = 5793266;
                $body['embeds'][0]['fields'][] = [
                    "name" => "Order ID",
                    "value" => "#" . $order['id'],
                    "inline" => true
                ];
                if (in_array((int)($order['form_id'] ?? 0), [4, 19, 29], true)) {
                    $teamSize = max(1, min(4, (int)($order['boosters'] ?? $order['hours'] ?? 1)));
                    $body['embeds'][0]['fields'][] = [
                        "name" => "Boosters",
                        "value" => (string)$teamSize,
                        "inline" => true
                    ];
                }
                if ($note != false) {
                $body['embeds'][0]['fields'][] = [
                    "name" => "Note",
                    "value" => decode_entities($note['order_note']),
                    "inline" => false
                ];
            }
                $notificationGame = strtolower(trim((string)($order['game'] ?? '')));
                if (in_array($notificationGame, ['lol_classic', 'lol-classic', 'league-of-legends-classic'], true)) {
                    lb_discord_add_lol_classic_fields($body, $order);
                } elseif ($notificationGame === 'lol') {
                    switch ($order['type']) {
                        case 'rank':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('lol', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] != 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End Rank",
                                    "value" => util_format_rank_display('lol', $order["end_tier"] ?? null, $order["end_division"] ?? null, $order['end_lp'] ?? null),
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start LP",
                                "value" => ($order['start_lp'] ?? null) . ' LP',
                                "inline" => true
                            ];
                            if ($order['end_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End LP",
                                    "value" => ($order['end_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "LP Gain",
                                "value" => ($order['lp_gain'] ?? null) . ' LP',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'win':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('lol', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Start LP",
                                    "value" => ($order['end_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Wins Amount",
                                "value" => $order['matches'] . ' Wins',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'placement':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Last Season Rank",
                                "value" => util_format_rank_display('lol', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Placements Amount",
                                "value" => $order['matches'] . ' Placements',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'normal':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Queue Type",
                                "value" => util_format_default_type($order['queue_type']),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Normals Amount",
                                "value" => $order['matches'] . ' Normals',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                    }
                } elseif ($order['game'] === 'tft') {
                    switch ($order['type']) {
                        case 'rank':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('tft', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if (($order['start_tier'] ?? null) != 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End Rank",
                                    "value" => util_format_rank_display('tft', $order["end_tier"] ?? null, $order["end_division"] ?? null, $order['end_lp'] ?? null),
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start LP",
                                "value" => ($order['start_lp'] ?? null) . ' LP',
                                "inline" => true
                            ];
                            if (($order['end_tier'] ?? null) == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End LP",
                                    "value" => ($order['end_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            if (isset($order['lp_gain'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "LP Gain",
                                    "value" => ($order['lp_gain'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'win':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('tft', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            if (($order['start_tier'] ?? null) == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Start LP",
                                    "value" => ($order['start_lp'] ?? null) . ' LP',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Wins Amount",
                                "value" => ($order['matches'] ?? 0) . ' Wins',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                        case 'placement':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Last Season Rank",
                                "value" => util_format_rank_display('tft', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Placements Amount",
                                "value" => ($order['matches'] ?? 0) . ' Placements',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            if (!empty($order['queue_type'])) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Queue Type",
                                    "value" => util_format_default_type($order['queue_type']),
                                    "inline" => true
                                ];
                            }
                            break;
                    }
                } elseif ($order['game'] === 'val') {
                    switch ($order['type']) {
                        case 'rank':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('val', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_rr'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] != 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End Rank",
                                    "value" => util_format_rank_display('val', $order["end_tier"] ?? null, $order["end_division"] ?? null, $order['end_rr'] ?? null),
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start RR",
                                "value" => ($order['start_rr'] ?? null) . ' RR',
                                "inline" => true
                            ];
                            if ($order['end_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "End RR",
                                    "value" => ($order['end_rr'] ?? null) . ' RR',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                        case 'win':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Start Rank",
                                "value" => util_format_rank_display('val', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_rr'] ?? null),
                                "inline" => true
                            ];
                            if ($order['start_tier'] == 8) {
                                $body['embeds'][0]['fields'][] = [
                                    "name" => "Start RR",
                                    "value" => ($order['end_rr'] ?? null) . ' RR',
                                    "inline" => true
                                ];
                            }
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Games Amount",
                                "value" => $order['matches'] . ' Wins',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                        case 'placement':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Last Season Rank",
                                "value" => util_format_rank_display('val', $order["start_tier"] ?? null, $order["start_division"] ?? null, $order['start_rr'] ?? null),
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = $empty_row_inl;
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Games Amount",
                                "value" => $order['matches'] . ' Games',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                        case 'normal':
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Games Amount",
                                "value" => $order['matches'] . ' Games',
                                "inline" => true
                            ];
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Server",
                                "value" => lb_format_discord_server($order['server']),
                                "inline" => true
                            ];
                            break;
                    }
                } else {
                    // Games outside lol/tft/val (Rocket League, Apex, Marvel Rivals, Wild Rift, Overwatch 2, ...)
                    // use their own rank terminology from the boost form JSON instead of LP/RR.
                    $jsonData = !empty($order['form_id']) ? lb_load_boost_form_json_by_id($order['form_id']) : [];
                    if (!empty($jsonData)) {
                        $rankConfig = function_exists('lb_generic_game_rank_config')
                            ? lb_generic_game_rank_config((string)($order['game'] ?? ''))
                            : null;
                        if (is_array($rankConfig)) {
                            $jsonData['form_config'] = array_replace(
                                is_array($jsonData['form_config'] ?? null) ? $jsonData['form_config'] : [],
                                $rankConfig
                            );
                        }
                        $isGamesService = in_array((int)($order['form_id'] ?? 0), [38, 43, 44, 46, 49, 50, 52], true)
                            || in_array(strtolower(trim((string)($order['type'] ?? ''))), ['win', 'placement'], true)
                            || in_array(strtolower(trim((string)($order['slug'] ?? ''))), ['win-boost', 'placement', 'placement-boost', 'placements-boost'], true);
                        $isPlacementService = in_array((int)($order['form_id'] ?? 0), [44, 50], true)
                            || strtolower(trim((string)($order['type'] ?? ''))) === 'placement'
                            || in_array(strtolower(trim((string)($order['slug'] ?? ''))), ['placement', 'placement-boost', 'placements-boost'], true);
                        $startTier = (int)($order['start_tier'] ?? 0);
                        $endTier = (int)($order['end_tier'] ?? 0);
                        if ($startTier > 0 || $isPlacementService) {
                            $body['embeds'][0]['fields'][] = [
                                "name" => $isPlacementService ? "Last Season Rank" : "Start Rank",
                                "value" => lb_summary_rank_display($jsonData, $startTier, $order['start_division'] ?? null, $order['start_lp'] ?? null),
                                "inline" => true
                            ];
                        }
                        if (!$isGamesService && $endTier > 0) {
                            $body['embeds'][0]['fields'][] = [
                                "name" => "Target Rank",
                                "value" => lb_summary_rank_display($jsonData, $endTier, $order['end_division'] ?? null, $order['end_lp'] ?? null),
                                "inline" => true
                            ];
                        }
                    }
                    if (!empty($order['matches'])) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => "Amount",
                            "value" => $order['matches'] . ' ' . ucfirst((string)$order['type']),
                            "inline" => true
                        ];
                    }
                    $isOverwatchOrder = in_array((int)($order['form_id'] ?? 0), [48, 49, 50], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['ow2', 'overwatch', 'overwatch-2', 'overwatch2'], true);
                    $isMarvelOrder = in_array((int)($order['form_id'] ?? 0), [37, 38, 40, 41], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['rivals', 'marvel-rivals', 'marvel_rivals', 'marvelrivals'], true);
                    $isRocketLeagueOrder = in_array((int)($order['form_id'] ?? 0), [42, 43, 44], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['rl', 'rocket-league', 'rocket_league', 'rocketleague'], true);
                    $isWildRiftOrder = in_array((int)($order['form_id'] ?? 0), [51, 52], true)
                        || in_array(strtolower(trim((string)($order['game'] ?? ''))), ['wild-rift', 'wildrift', 'lol-wild-rift', 'lol_wild_rift'], true);
                    if (!empty($order['queue_type']) && !($isWildRiftOrder && $isGamesService && !$isPlacementService)) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => $isOverwatchOrder ? "Role" : ($isWildRiftOrder ? "Ranked Marks" : "Queue Type"),
                            "value" => $isWildRiftOrder ? util_format_ranked_marks($order['queue_type']) : util_format_default_type($order['queue_type']),
                            "inline" => true
                        ];
                    }
                    if (($isOverwatchOrder || $isMarvelOrder || $isRocketLeagueOrder) && !empty($order['platform'])) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => "Platform",
                            "value" => util_format_platform($order['platform']),
                            "inline" => true
                        ];
                    }
                    if (!$isCounterStrikeOrder && !empty($order['server'])) {
                        $body['embeds'][0]['fields'][] = [
                            "name" => "Server",
                            "value" => lb_format_discord_server($order['server']),
                            "inline" => true
                        ];
                    }
                }
                if ($order['is_duo'] == 1) {
                    $body['embeds'][0]['fields'][] = [
                        "name" => "Duo",
                        "value" => ":green_circle: Yes",
                        "inline" => true
                    ];
                }
                $options = load_boost_extra_options();
                $option_count = 0;
                foreach ($options as $option) {
                    if (!empty($order[$option]) && $order[$option] != 0) {
                        $option_count++;
                    }
                }
                if ($option_count > 0) {
                    $body['embeds'][0]['fields'][] = [
                        "name" => "**__Options:__ :**",
                        "value" => "EMPTY_ROW"
                    ];
                    foreach ($options as $option) {
                        if (!empty($order[$option]) && $order[$option] != 0) {
                            $option_data = util_format_option_inline($option, $order[$option]);
                            $body['embeds'][0]['fields'][] = [
                                "name" => $option_data[0],
                                "value" => $option_data[1],
                                "inline" => true
                            ];
                        }
                    }
                }
            }
            break;
        case 'egirl_booking_paid':
            $orderId = (int) base64_decode((string)($data['order_id'] ?? ''), true);
            $egirlId = (int) base64_decode((string)($data['egirl_id'] ?? ''), true);
            $clientId = (int) base64_decode((string)($data['client_id'] ?? ''), true);

            $order = db_get_row('egirl_orders', ['id' => $orderId]);
            if ($order != false) {
                $client = db_get_row('clients', ['id' => $clientId]);
                $priceEurCents = (int)($order['price_eur'] ?? 0);
                $orderUrl = BASE_URL . '/booster-area/egirl-order/' . (int)$order['id'];
                $webhook_url = 'https://discord.com/api/webhooks/1517969257386872932/Wj1R4m1VsHVbhm0bVuKimbpkEYQ_rMJuqFZ955yPKSDu6RUIHLw7vEIbQBOdaSUGNE8y';

                if ((string)($order['service_type'] ?? '') === 'lol_ggirl_boost') {
                    $details = [];
                    $clientNotesRaw = (string)($order['client_notes'] ?? '');
                    if (preg_match('/DATA:(\{.*\})/s', $clientNotesRaw, $m)) {
                        $decodedDetails = json_decode(trim($m[1]), true);
                        if (is_array($decodedDetails)) {
                            $details = $decodedDetails;
                        }
                    }

                    $server = strtoupper((string)($details['server'] ?? '-'));
                    $modeTitle = (string)($details['mode_title'] ?? ($order['service_title'] ?? 'GGirl Order'));
                    $rankLabel = (string)($details['rank_label'] ?? '-');
                    $amount = (string)($details['amount'] ?? ($order['unit_value'] ?? ''));
                    $unitLabel = (string)($order['unit_type'] ?? ($details['unit_type'] ?? ''));
                    $priceText = '€' . number_format($priceEurCents / 100, 2, '.', '');
                    $ggirlRoleId = '1486269047539765248';

                    $body['username'] = 'New Order Notif';
                    $body['avatar_url'] = 'https://lolboost.gg/public/assets/website/images/gg-girl.png';
                    $body['content'] = '<@&' . $ggirlRoleId . '> New ' . $server . ' GGirl Order - [Claim Order](' . BASE_URL . '/booster-area/egirl-panel)';
                    $body['allowed_mentions'] = ['roles' => [$ggirlRoleId]];
                    $body['embeds'][0]['title'] = $modeTitle;
                    $body['embeds'][0]['color'] = hexdec('EC4899');
                    $body['embeds'][0]['fields'][] = ['name' => 'Server', 'value' => $server, 'inline' => false];
                    $body['embeds'][0]['fields'][] = ['name' => 'Rank', 'value' => $rankLabel, 'inline' => false];
                    $body['embeds'][0]['fields'][] = ['name' => 'Amount', 'value' => trim($amount . ' ' . $unitLabel), 'inline' => false];
                    $body['embeds'][0]['fields'][] = ['name' => 'Price', 'value' => $priceText, 'inline' => false];
                    $body['embeds'][0]['fields'][] = ['name' => 'Claim Order', 'value' => '[Claim Order](' . BASE_URL . '/egirl-panel)', 'inline' => false];
                    $body['embeds'][0]['footer'] = ['text' => 'LoLBoost.gg GGirl Order'];
                    $body['embeds'][0]['timestamp'] = date('c');
                    break;
                }

                $boosterHelperRoleId = '1295032857907171339';
                $ggirlRoleId = '1486269047539765248';
                $egirlDiscordId = '';
                $egirlDisplay = 'GGirl';

                if ($egirlId > 0) {
                    $egirl = db_get_row('boosters', ['id' => $egirlId]);
                    if ($egirl != false) {
                        $egirlDisplay = trim((string)($egirl['username'] ?? $egirl['discord'] ?? 'GGirl'));
                        $egirlDiscordId = trim((string)($egirl['discord_id'] ?? ''));

                        if (($egirlDiscordId === '' || !ctype_digit($egirlDiscordId)) && !empty($egirl['discord'])) {
                            $legacyDiscordId = trim((string)$egirl['discord']);
                            if (ctype_digit($legacyDiscordId)) {
                                $egirlDiscordId = $legacyDiscordId;
                            }
                        }
                    }
                }

                $body['username'] = 'New Booking';
                $body['avatar_url'] = 'https://lolboost.gg/public/assets/website/images/gg-girl.png';

                if ($egirlId > 0 && $egirlDiscordId !== '' && ctype_digit($egirlDiscordId)) {
                    $body['content'] = '<@&' . $boosterHelperRoleId . '> <@' . $egirlDiscordId . '> You are requested for a new paid E-Girl booking - [Open Booking](' . $orderUrl . ')';
                    $body['allowed_mentions'] = [
                        'roles' => [$boosterHelperRoleId],
                        'users' => [$egirlDiscordId],
                    ];
                } elseif ($egirlId > 0) {
                    $safeEgirlDisplay = $egirlDisplay !== '' ? $egirlDisplay : 'GGirl';
                    $body['content'] = '<@&' . $boosterHelperRoleId . '> **' . $safeEgirlDisplay . '** You are requested for a new paid E-Girl booking - [Open Booking](' . $orderUrl . ')';
                    $body['allowed_mentions'] = ['roles' => [$boosterHelperRoleId]];
                } else {
                    $body['content'] = '<@&' . $boosterHelperRoleId . '> <@&' . $ggirlRoleId . '> New paid E-Girl booking - [Open Booking](' . $orderUrl . ')';
                    $body['allowed_mentions'] = ['roles' => [$boosterHelperRoleId, $ggirlRoleId]];
                }

                $body['embeds'][0]['title'] = '💖 New E-Girl Booking';
                $body['embeds'][0]['description'] = '**' . ($order['service_title'] ?? 'Session') . '**';
                $body['embeds'][0]['color'] = 0x8b5cf6;
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Client',
                    'value' => !empty($client['username']) ? $client['username'] : 'Guest',
                    'inline' => true,
                ];
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Game',
                    'value' => strtoupper((string)($order['game'] ?? '-')),
                    'inline' => true,
                ];
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Price',
                    'value' => '€' . number_format($priceEurCents / 100, 2),
                    'inline' => true,
                ];
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Booking ID',
                    'value' => '#' . (int)$order['id'],
                    'inline' => true,
                ];
                if (!empty($order['unit_value']) || !empty($order['unit_type'])) {
                    $body['embeds'][0]['fields'][] = [
                        'name' => 'Session',
                        'value' => trim((string)($order['unit_value'] ?? '') . ' ' . (string)($order['unit_type'] ?? '')),
                        'inline' => true,
                    ];
                }
                if (!empty($order['client_notes'])) {
                    $body['embeds'][0]['fields'][] = [
                        'name' => 'Notes',
                        'value' => decode_entities((string)$order['client_notes']),
                        'inline' => false,
                    ];
                }
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Open Booking',
                    'value' => '[Open in E-Girl Area](' . $orderUrl . ')',
                    'inline' => false,
                ];
                $body['embeds'][0]['footer'] = ['text' => 'LoLBoost.gg E-Girl'];
                $body['embeds'][0]['timestamp'] = date('c');
            }
            break;
        case 'item_sold':
            $webhook_url = 'https://discord.com/api/webhooks/1060285854951690330/BTco7MVqrzFxLE-DXtGZ9r2rbUsfFIBBo17ZOGZ3vgHMZmMk4xTUBsqMzE4NUM0J-uiO';
            $mention = '<@&1083064775623327784> <@&1295032857907171339>';

            $itemTitle = trim((string) base64_decode((string)($data['item_title'] ?? ''), true));
            $buyer = trim((string) base64_decode((string)($data['buyer'] ?? ''), true));
            $price = trim((string) base64_decode((string)($data['price'] ?? ''), true));
            $quantity = trim((string) base64_decode((string)($data['quantity'] ?? ''), true));
            $orderUrl = trim((string) base64_decode((string)($data['order_url'] ?? ''), true));
            $itemCover = trim((string) base64_decode((string)($data['item_cover'] ?? ''), true));
            $purchaseId = (int) base64_decode((string)($data['purchase_id'] ?? ''), true);
            $sellerId = (int) base64_decode((string)($data['seller_id'] ?? ''), true);
            $seller = $sellerId > 0 ? db_get_row('sellers', ['id' => $sellerId]) : false;

            $body['content'] = $mention . ' New item order received' . ($orderUrl !== '' ? ' - [Open Order](' . $orderUrl . ')' : '');
            $body['embeds'][0]['title'] = '🛒 New Item Order';
            $body['embeds'][0]['description'] = $itemTitle !== '' ? ('**' . $itemTitle . '**') : 'A customer purchased an item.';
            $body['embeds'][0]['color'] = 0x4ade80;
            if ($purchaseId > 0) {
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Purchase ID',
                    'value' => '#' . $purchaseId,
                    'inline' => true,
                ];
            }
            if (!empty($seller['username'])) {
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Seller',
                    'value' => $seller['username'],
                    'inline' => true,
                ];
            }
            if ($buyer !== '') {
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Buyer',
                    'value' => $buyer,
                    'inline' => true,
                ];
            }
            if ($quantity !== '') {
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Quantity',
                    'value' => $quantity,
                    'inline' => true,
                ];
            }
            if ($price !== '') {
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Revenue',
                    'value' => '€' . $price,
                    'inline' => true,
                ];
            }
            if ($orderUrl !== '') {
                $body['embeds'][0]['fields'][] = [
                    'name' => 'Open Order',
                    'value' => '[View Order Details](' . $orderUrl . ')',
                    'inline' => false,
                ];
            }
            if ($itemCover !== '') {
                $body['embeds'][0]['thumbnail'] = ['url' => $itemCover];
            }
            $body['embeds'][0]['footer'] = ['text' => 'LoLBoost.gg Item Shop'];
            $body['embeds'][0]['timestamp'] = date('c');
            break;
        case 'order_completion_request':
            $order = db_get_row('orders', ['id' => base64_decode($data['order_id'])]);
            if ($order != false) {
                $order_form = db_get_row('boost_forms', ['id' => $order['form_id']]);
                $order_options = db_get_row('order_options', ['order_id' => $order['id']]);
                $note = db_get_row('order_notes', ['order_id' => $order['id']]);
                $order_account = db_get_row('order_accounts', ['order_id' => $order['id']], 1);
                $order_ss = db_get_row('order_screenshots', ['order_id' => $order['id']], 1);
                $order = array_merge($order_form, $order_options, $order_account, $order_ss, $order);
                $booster = db_get_row('boosters', ['id' => $order['booster_id']]);
                $webhook_url = lb_booster_notification_webhook_url('order_completion');
                $mention = '<@&1295032857907171339>';
                $url = ADMN_URL . '/order/' . $order['id'];
                $body['content'] = $mention . ' ' . $booster['username'] . ' has completed a ' . strtoupper($order['game']) . ' ' . $order['name'] . ' - [Complete Order](' . $url . ')';
                ;
                $body['embeds'][0]['title'] = $booster['username'] . ' has completed a ' . strtoupper($order['game']) . ' ' . $order['name'];
                $body['embeds'][0]['author']['name'] = '#' . $order['id'];
                $body['embeds'][0]['color'] = 5793266;
                $body['embeds'][0]['fields'][] = [
                    "name" => "Order Details",
                    "value" => util_format_boost_overview($order['game'], $order['type'], $order),
                    "inline" => true
                ];
                $body['embeds'][0]['fields'][] = [
                    "name" => "In Game Name",
                    "value" => (empty($order['ign']) ? 'N/A' : $order['ign']),
                    "inline" => true
                ];
                $body['embeds'][0]['image']['url'] = $order['file_url'];
            } else {
                echo base64_decode($data['order_id']);
            }
            break;
        case 'order_paused':
            $order = db_get_row('orders', ['id' => base64_decode($data['order_id'])]);
            if ($order != false) {
                $booster = db_get_row('boosters', ['id' => $order['booster_id']]);
                $webhook_url = 'https://discord.com/api/webhooks/1270351901531176991/qsnc338pu_fweDorJnYZZnWbfltyIdBHhCtyMOuJmvBvdFRzR963TMg76n7b72hydXHK';
                $mention = '<@' . $booster['discord_id'] . '>';
                $url = BSTR_URL . '/order/' . $order['id'];
                $body['content'] = $mention . ' Order #' . $order['id'] . ' has been paused - [View Order](' . $url . ')';
            } else {
                echo base64_decode($data['order_id']);
            }
            break;
        
        case 'booster_tip_discord':
            // decode base64 data
foreach ($data as $key => $val) {
    $data[$key] = base64_decode($val);
}

// TODO: paste your (newly generated) Discord webhook URL here
$webhook_url = 'https://discord.com/api/webhooks/1467962406155325510/xtzuWHvxjrAJtk3ikikY7f5ZzZ-i3Bvh0jv4CzckwxMDUCRfDrWFrkL8aMS9zXqhFeRA';

$tip_id     = isset($data['tip_id']) ? (int)$data['tip_id'] : 0;
$client_id  = isset($data['client_id']) ? (int)$data['client_id'] : 0;
$booster_id = isset($data['booster_id']) ? (int)$data['booster_id'] : 0;

// Fetch client (icon + username), fallback to provided username
$client_username_raw = $data['client_username'] ?? 'Client';
$client_icon = '';
if ($client_id > 0) {
    $client_row = db_get_row('clients', ['id' => $client_id]);
    if ($client_row != false) {
        if (!empty($client_row['username'])) $client_username_raw = $client_row['username'];
        if (!empty($client_row['icon'])) $client_icon = $client_row['icon'];
    }
}

// Mask the client name for privacy (e.g. "Guest#27895" -> "Gu***95")
$len_fn = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
$sub_fn = function_exists('mb_substr') ? 'mb_substr' : 'substr';
$client_username = $client_username_raw;
if ($client_username_raw !== '' && $len_fn($client_username_raw) >= 2) {
    $first = $sub_fn($client_username_raw, 0, 2);
    $last  = $len_fn($client_username_raw) >= 4 ? $sub_fn($client_username_raw, -2) : '';
    $client_username = $first . '***' . $last;
}

// Fetch booster (icon/cover + username), fallback to provided username
$booster_username = $data['booster_username'] ?? 'Booster';
$booster_icon_url = '';
$booster_thumb_url = '';
if ($booster_id > 0) {
    $booster_row = db_get_row('boosters', ['id' => $booster_id]);
    if ($booster_row != false) {
        if (!empty($booster_row['username'])) $booster_username = $booster_row['username'];

        // Round icon (author/footer) should prefer 'icon' (usually square), fallback to cover
        if (!empty($booster_row['icon'])) {
            $booster_icon_url = $booster_row['icon'];
        } elseif (!empty($booster_row['cover'])) {
            $booster_icon_url = $booster_row['cover'];
        }

        // Thumbnail can prefer cover (often nicer), fallback to icon
        if (!empty($booster_row['cover'])) {
            $booster_thumb_url = $booster_row['cover'];
        } elseif (!empty($booster_row['icon'])) {
            $booster_thumb_url = $booster_row['icon'];
        }
    }
}

$currency = $data['currency'] ?? 'EUR';

// We only display the booster amount (credited) in Discord
$booster_cents = isset($data['amount_booster']) ? (int)$data['amount_booster'] : 0;

$symbol = function_exists('util_format_currency_display') ? util_format_currency_display($currency) : '€';
$amount = $symbol . (function_exists('util_format_price_display')
    ? util_format_price_display($booster_cents)
    : number_format($booster_cents / 100, 2, '.', ''));

$desc = trim((string)($data['description'] ?? ''));
$desc = $desc !== '' ? decode_entities($desc) : '';

$body['username'] = 'LoLBoost.gg';

// Build "Message" block (matches your screenshot)
$message_block = "Message";
if ($desc !== '') {
    $message_block .= "\n" . $desc;
}

// Variant A: Booster is the author (round icon directly before booster name)
$embed = [
    'author' => [
        'name' => $booster_username,
    ],
    'title' => 'New ' . $amount . ' Tip!',
    'color' => 5793266,
    // Requested layout: use money-with-wings icon and remove inline client line (client stays in footer)
    'description' => "💸 Tip #" . $tip_id . "\n\n" . $message_block,
];

// Booster round icon (top-left)
if (!empty($booster_icon_url)) {
    $embed['author']['icon_url'] = $booster_icon_url;
}

// Footer shows client (also supports round icon)
$embed['footer'] = [
    'text' => $client_username . ' (Client)',
];
if (!empty($client_icon)) {
    $embed['footer']['icon_url'] = $client_icon;
}

// No right-side thumbnail (requested)

$body['embeds'][0] = $embed;

            break;

case 'account_ping':
    break;

case 'account_low_stock':
    foreach ($data as $key => $val) {
        $data[$key] = base64_decode($val);
    }
    $webhook_url = lb_seller_notification_webhook_url('account_low_stock');
    $package = db_get_row('account_packages', ['id' => (int)$data['package_id']]);
    $remaining = (int)($data['remaining_stock'] ?? 0);

    $body['embeds'][0]['title'] = 'Low stock warning';
    $body['embeds'][0]['color'] = 16766720;
    $body['embeds'][0]['description'] = 'Only ' . $remaining . ' account left for package: ' . ($package['name'] ?? 'Unknown Package');
    $body['embeds'][0]['fields'][] = [
        "name" => "Package ID",
        "value" => '#' . ((int)($package['id'] ?? 0)),
        "inline" => true
    ];
    $body['embeds'][0]['fields'][] = [
        "name" => "Server",
        "value" => strtoupper($package['server'] ?? '-'),
        "inline" => true
    ];
    $body['embeds'][0]['fields'][] = [
        "name" => "Remaining",
        "value" => (string)$remaining,
        "inline" => true
    ];
    break;
}
return [$webhook_url, $body, $fallback_webhook_url];
}

function notification_discord_send($webhook_url, $data)
{
    // Discord rejects the complete webhook when a single embed value is empty,
    // too long, or when more than 25 fields are present. Legacy orders can
    // contain exactly those values, while a plain webhook/thread test succeeds.
    $cut = static function ($value, int $limit): string {
        $value = trim((string)$value);
        if ($value === '') return "\u{200B}";
        return function_exists('mb_substr') ? mb_substr($value, 0, $limit, 'UTF-8') : substr($value, 0, $limit);
    };
    if (isset($data['content'])) {
        $data['content'] = $cut($data['content'], 2000);
    }
    if (!empty($data['embeds']) && is_array($data['embeds'])) {
        $data['embeds'] = array_slice($data['embeds'], 0, 10);
        foreach ($data['embeds'] as &$embed) {
            if (isset($embed['title'])) $embed['title'] = $cut($embed['title'], 256);
            if (isset($embed['description'])) $embed['description'] = $cut($embed['description'], 4096);
            if (isset($embed['author']['name'])) $embed['author']['name'] = $cut($embed['author']['name'], 256);
            if (!empty($embed['fields']) && is_array($embed['fields'])) {
                $embed['fields'] = array_slice($embed['fields'], 0, 25);
                foreach ($embed['fields'] as &$field) {
                    $field['name'] = $cut($field['name'] ?? '', 256);
                    $field['value'] = $cut($field['value'] ?? '', 1024);
                    $field['inline'] = !empty($field['inline']);
                }
                unset($field);
            }
        }
        unset($embed);
    }
    $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $data = str_replace('EMPTY_ROW', '\u200B', $data);
    $ch = curl_init();
    $separator = strpos($webhook_url, '?') === false ? '?' : '&';
    curl_setopt($ch, CURLOPT_URL, $webhook_url . $separator . 'wait=true');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $server_output = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Discord webhook posts fail silently otherwise (e.g. deleted/invalid thread_id,
    // missing "Send Messages in Threads" permission, rate limiting). Without this,
    // a notification can be claimed (is_sent=1) and still never actually arrive in the
    // channel, with zero trace of why.
    $success = $server_output !== false && $http_code >= 200 && $http_code < 300;
    if (!$success) {
        error_log(sprintf(
            '[notification_discord_send] FAILED url=%s http_code=%d curl_errno=%d curl_error=%s response=%s',
            $webhook_url,
            $http_code,
            $curl_errno,
            $curl_error,
            is_string($server_output) ? substr($server_output, 0, 500) : '(false)'
        ));
    }

    return $success;
}

/**
 * Send a Discord DM to a booster when they get requested for an order.
 * Uses a Bot token (DS_BOT_TOKEN) and the booster's stored discord_id.
 *
 * This is intentionally "best effort": webhook sending is the primary path.
 * DM errors will not fail the notification_sender run.
 */
function notification_discord_send_booster_request_dm(array $notif, array $webhookBody): bool
{
    // Only for booster_request
    if (!in_array(($notif['type'] ?? ''), ['booster_request','booster_ready_request'], true)) {
        return false;
    }

    // Require bot token
    if (!defined('DS_BOT_TOKEN') || !DS_BOT_TOKEN) {
        return false;
    }

    $data = $notif['data'] ?? [];
    if (!is_array($data) || empty($data['order_id'])) {
        return false;
    }

    $orderId = (int) base64_decode((string) $data['order_id']);
    if ($orderId <= 0) {
        return false;
    }

    // Determine booster id (new notifications may include booster_id; fallback to order->booster_id)
    $boosterId = 0;
    if (!empty($data['booster_id'])) {
        $rawBoosterId = base64_decode((string) $data['booster_id'], true);
        $boosterId = (int) ($rawBoosterId !== false ? $rawBoosterId : 0);
    }

    $order = db_get_row('orders', ['id' => $orderId]);
    if (!$order) {
        return false;
    }
    if ($boosterId <= 0 && !empty($order['booster_id'])) {
        $boosterId = (int) $order['booster_id'];
    }
    if ($boosterId <= 0) {
        return false;
    }

    $booster = db_get_row('boosters', ['id' => $boosterId]);
    $discordUserId = trim((string) ($booster['discord_id'] ?? ''));
    if ($discordUserId === '' || !ctype_digit($discordUserId)) {
        return false;
    }

    // Build DM payload – mimic webhook design as close as possible
    $orderUrl = BSTR_URL . "/order/" . $orderId;

    // Client name for DM header (prefer real client username from DB)
$clientName = '';
if (!empty($order['client_id'])) {
    $clientRow = db_get_row('clients', ['id' => (int) $order['client_id']]);
    if (!empty($clientRow['username'])) {
        $clientName = (string) $clientRow['username'];
    }
}
// Fallbacks (in case client record is missing)
if ($clientName === '' && !empty($webhookBody['embeds'][0]['author']['name'])) {
    $clientName = (string) $webhookBody['embeds'][0]['author']['name'];
}
if ($clientName === '' && !empty($order['name'])) {
    $clientName = (string) $order['name'];
}
if ($clientName === '') {
    $clientName = 'Client';
}
$payload = [
        'content' => $clientName . " has requested you for a boost!",
        'embeds' => $webhookBody['embeds'] ?? [],
        'components' => [[
            'type' => 1,
            'components' => [[
                'type' => 2,
                'style' => 5,
                'label' => 'View Order',
                'url' => $orderUrl,
            ]]
        ]],
    ];

    // Replace EMPTY_ROW placeholders for Discord rendering
    array_walk_recursive($payload, function (&$v) {
        if ($v === 'EMPTY_ROW') {
            $v = "\u{200B}";
        }
    });



    // DM open + send
    $openDebug = [];
    $channelId = discord_dm_channel_id(DS_BOT_TOKEN, $discordUserId, $openDebug);
    if (!$channelId) {
        return false;
    }

    $sendDebug = [];
    return discord_send_message(DS_BOT_TOKEN, $channelId, $payload, $sendDebug);
}

function notification_discord_send_egirl_booking_dm(array $notif, array $webhookBody): bool
{
    if (($notif['type'] ?? '') !== 'egirl_booking_paid') {
        return false;
    }

    if (!defined('DS_BOT_TOKEN') || !DS_BOT_TOKEN) {
        return false;
    }

    $data = $notif['data'] ?? [];
    if (!is_array($data) || empty($data['order_id'])) {
        return false;
    }

    $orderId = (int) base64_decode((string)($data['order_id'] ?? ''), true);
    $egirlId = (int) base64_decode((string)($data['egirl_id'] ?? ''), true);
    if ($orderId <= 0 || $egirlId <= 0) {
        return false;
    }

    $egirl = db_get_row('boosters', ['id' => $egirlId]);
    if (!$egirl) {
        return false;
    }

    $discordUserId = trim((string)($egirl['discord_id'] ?? ''));
    if ($discordUserId === '') {
        $legacyDiscord = trim((string)($egirl['discord'] ?? ''));
        if ($legacyDiscord !== '' && ctype_digit($legacyDiscord)) {
            $discordUserId = $legacyDiscord;
        }
    }
    if ($discordUserId === '' || !ctype_digit($discordUserId)) {
        return false;
    }

    $payload = [
        'content' => 'You received a new paid booking 💖',
        'embeds' => $webhookBody['embeds'] ?? [],
        'components' => [[
            'type' => 1,
            'components' => [[
                'type' => 2,
                'style' => 5,
                'label' => 'Open Booking',
                'url' => BASE_URL . '/booster-area/egirl-order/' . $orderId,
            ]]
        ]],
    ];

    array_walk_recursive($payload, function (&$v) {
        if ($v === 'EMPTY_ROW') {
            $v = "\\u{200B}";
        }
    });

    $openDebug = [];
    $channelId = discord_dm_channel_id(DS_BOT_TOKEN, $discordUserId, $openDebug);
    if (!$channelId) {
        return false;
    }

    $sendDebug = [];
    return discord_send_message(DS_BOT_TOKEN, $channelId, $payload, $sendDebug);
}

// --- Minimal Discord Bot API helpers (kept local so notification_sender can send DMs) ---
if (!function_exists('discord_dm_channel_id')) {
    function discord_dm_channel_id(string $botToken, string $discordUserId, array &$debugOut = []): ?string
    {
        $res = discord_api('POST', 'https://discord.com/api/v10/users/@me/channels', $botToken, [
            'recipient_id' => $discordUserId,
        ]);

        $debugOut = is_array($res) ? $res : ['raw' => $res];
        $http = (int)($debugOut['_http_code'] ?? 0);

        if ($http >= 200 && $http < 300 && !empty($debugOut['id'])) {
            return (string)$debugOut['id'];
        }
        return null;
    }
}

if (!function_exists('discord_send_message')) {
    function discord_send_message(string $botToken, string $channelId, array $payload, array &$debugOut = []): bool
    {
        $res = discord_api('POST', "https://discord.com/api/v10/channels/{$channelId}/messages", $botToken, $payload);

        $debugOut = is_array($res) ? $res : ['raw' => $res];
        $http = (int)($debugOut['_http_code'] ?? 0);

        return ($http >= 200 && $http < 300 && !empty($debugOut['id']));
    }
}

if (!function_exists('discord_api')) {
    function discord_api(string $method, string $url, string $botToken, array $payload)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bot ' . $botToken,
            'Content-Type: application/json',
            'User-Agent: LoLBoostGG (https://lolboost.gg, 1.0)',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));

        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return ['_http_code' => $code, 'curl_error' => $err];
        }

        $decoded = json_decode($body, true);
        if ($decoded === null) {
            return ['_http_code' => $code, 'raw' => $body];
        }

        $decoded['_http_code'] = $code;
        return $decoded;
    }
}


// ============================================================
// ATOMIC CLAIM: Verhindert Race Conditions und Doppel-Posts.
//
// Problem vorher:
//   1. Kein Locking → wenn notification_sender.php mehrfach
//      gleichzeitig läuft (z.B. mehrere Requests triggern es),
//      holen beide denselben Batch und posten doppelt.
//   2. `sleep(1)` + `usleep(300000)` im Email-Block verzögern
//      den gesamten Discord-Block um ~1.3s pro Email-Notification.
//   3. is_sent wird NACH dem Senden gesetzt → bei Absturz/Timeout
//      wird dieselbe Notification beim nächsten Lauf nochmal gesendet.
//
// Fix:
//   - Prozess-Lock via flock() → nur eine Instanz läuft gleichzeitig
//   - Atomares Claim: UPDATE is_sent=1 VOR dem Senden (claim-then-send)
//   - sleep() komplett entfernt
// ============================================================

// Prozess-Lock: verhindert parallele Ausführung
$lockFile = sys_get_temp_dir() . '/lolboost_notification_sender.lock';
$lockFp = fopen($lockFile, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'BUSY';
    // Eine andere Instanz läuft bereits → sofort beenden
    exit();
}

// Atomares Claim: alle ungesendeten Notifications in einem UPDATE als "in Bearbeitung" markieren.
// So kann eine zweite parallele Instanz (falls der Lock doch umgangen wird) sie nicht mehr holen.
global $db;

// Drain the whole queue in a loop instead of a single pass. trigger_notification_sender_async()
// is fire-and-forget with LOCK_NB, so a "repost order" (or any other) notification created while
// this instance is already running would otherwise just sit at is_sent=0 — its own trigger call
// found the lock busy and exited immediately, and nothing re-sends it until some unrelated later
// event happens to trigger the sender again. That's the "arrives, but after a very long time"
// behavior, and it's most visible on high-volume games (LoL) simply because a concurrent insert
// is far more likely while a batch for that game is still being processed. Looping here means the
// already-running instance picks up anything inserted mid-run before it releases the lock.
$drainIterations = 0;
while (true) {
    // Safety valve: a pathological stream of inserts shouldn't hold the lock forever.
    if (++$drainIterations > 50) break;
    $claimTs = date('Y-m-d H:i:s');

    // Nur Notifications holen die noch nicht geclaimt sind (is_sent = 0, is_processing = 0)
    // Sicherheitshalber: is_processing-Spalte existiert möglicherweise nicht → fallback auf is_sent
    $notifications_list = $db->run(
        "SELECT * FROM notifications
         WHERE is_sent = 0
         ORDER BY is_discord DESC, id ASC
         LIMIT 200"
    );

    if (empty($notifications_list)) {
        break;
    }

    // Alle IDs sofort als is_sent=1 markieren BEVOR wir anfangen zu senden.
    // Dadurch kann keine andere Instanz dieselben Notifications verarbeiten.
    $ids = array_column($notifications_list, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $db->run(
        "UPDATE notifications SET is_sent = 1, sent_at = ? WHERE id IN ($placeholders) AND is_sent = 0",
        $claimTs,
        ...$ids
    );

    // Nur die Rows verarbeiten, die wir tatsächlich geclaimt haben
    // (affected_rows = Anzahl die wirklich von 0 auf 1 gesetzt wurden)
    // Zur Sicherheit nochmal frisch laden mit is_sent=1 und sent_at = claimTs
    $notifications_list = $db->run(
        "SELECT * FROM notifications WHERE id IN ($placeholders) AND sent_at = ?
         ORDER BY is_discord DESC, id ASC",
        ...[...$ids, $claimTs]
    );

    if (empty($notifications_list)) {
        break;
    }

    foreach ($notifications_list as $notif) {
    $notif['data'] = json_decode($notif['data'], true);

    // ── DISCORD zuerst ───────────────────────────────────────
    // Discord-Notifications werden immer zuerst verarbeitet, auch wenn
    // in derselben Batch auch Emails dabei sind. So kommt der Webhook
    // sofort ohne durch Email-Verarbeitung verzögert zu werden.
    if ($notif['is_discord']) {
        // The atomic claim above already marked this row is_sent=1, so an uncaught
        // exception/fatal here (e.g. a reposted order with unexpected/legacy field
        // state tripping the embed-building logic) would kill the whole PHP process
        // right here: this notification silently never reaches Discord, and every
        // other still-unprocessed row in this batch is lost too, with zero error
        // surfaced anywhere (this runs via a fire-and-forget curl trigger). Catching
        // here keeps one bad notification from taking the rest of the batch down.
        try {
            $discord_data = notification_discord_body_load($notif['type'], $notif['data'], $notif);

            if (!empty($discord_data[0])) {
                $discordSent = notification_discord_send($discord_data[0], $discord_data[1]);
                if (!$discordSent && !empty($discord_data[2])) {
                    error_log('[notification_sender] Direct webhook rejected notification #' . $notif['id'] . '; retrying via the regular New Order game thread.');
                    $discordSent = notification_discord_send($discord_data[2], $discord_data[1]);
                }
                if (
                    !$discordSent &&
                    ($notif['type'] ?? '') === 'egirl_booking_paid'
                ) {
                    $fallbackWebhook = 'https://discord.com/api/webhooks/1517969257386872932/Wj1R4m1VsHVbhm0bVuKimbpkEYQ_rMJuqFZ955yPKSDu6RUIHLw7vEIbQBOdaSUGNE8y';
                    $fallbackBody = [
                        'username' => 'New Booking',
                        'avatar_url' => 'https://lolboost.gg/public/assets/website/images/gg-girl.png',
                        'content' => (string)($discord_data[1]['content'] ?? 'New paid E-Girl booking'),
                        'allowed_mentions' => $discord_data[1]['allowed_mentions'] ?? [
                            'parse' => ['roles', 'users'],
                        ],
                    ];
                    $discordSent = notification_discord_send($fallbackWebhook, $fallbackBody);
                }

                if (!$discordSent) {
                    throw new \RuntimeException('Discord rejected the webhook request; see notification_discord_send log for the HTTP response.');
                }
            } else {
                error_log('[notification_sender] Discord notification #' . $notif['id'] . ' (type=' . $notif['type'] . ') produced no webhook_url — nothing was sent.');
            }

            if (in_array($notif['type'], ['booster_request', 'booster_ready_request'], true)) {
                @notification_discord_send_booster_request_dm($notif, $discord_data[1]);
            }
            if (($notif['type'] ?? '') === 'egirl_booking_paid') {
                @notification_discord_send_egirl_booking_dm($notif, $discord_data[1]);
            }
        } catch (\Throwable $e) {
            error_log('[notification_sender] Discord send crashed for notification #' . $notif['id'] . ' (type=' . $notif['type'] . '): ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $recovered = false;

            // A legacy order can contain a field combination which breaks the
            // full New Order embed. Do not lose the repost: send a compact,
            // always-valid New Order card to the same game thread instead.
            if (($notif['type'] ?? '') === 'order_ping') {
                try {
                    $fallbackOrderId = (int)base64_decode((string)($notif['data']['order_id'] ?? ''));
                    $fallbackOrder = $fallbackOrderId > 0 ? db_get_row('orders', ['id' => $fallbackOrderId]) : false;
                    $fallbackForm = $fallbackOrder ? db_get_row('boost_forms', ['id' => $fallbackOrder['form_id'] ?? 0]) : false;
                    $fallbackOptions = $fallbackOrder ? db_get_row('order_options', ['order_id' => $fallbackOrderId]) : false;
                    $fallbackForm = is_array($fallbackForm) ? $fallbackForm : [];
                    $fallbackOptions = is_array($fallbackOptions) ? $fallbackOptions : [];
                    $fallbackOrder = is_array($fallbackOrder) ? array_merge($fallbackForm, $fallbackOptions, $fallbackOrder) : [];
                    $fallbackGame = trim((string)($fallbackOrder['game'] ?? $fallbackForm['game'] ?? 'val'));
                    $fallbackFormId = (int)($fallbackOrder['form_id'] ?? 0);
                    if ($fallbackFormId >= 30 && $fallbackFormId <= 36) {
                        $fallbackGame = 'lol_classic';
                    }
                    $fallbackOrder['game'] = $fallbackGame;
                    $fallbackRoute = notification_discord_game_route($fallbackGame);
                    $fallbackServer = strtoupper(trim((string)($fallbackOrder['server'] ?? '')));
                    if (in_array(strtolower(trim((string)$fallbackGame)), ['cs2', 'counter-strike-2', 'counterstrike2', 'counter-strike', 'csgo'], true)) {
                        $fallbackServer = '';
                    }
                    $fallbackName = trim((string)($fallbackForm['name'] ?? 'Order'));
                    if (!empty($fallbackOrder)) {
                        $formattedFallbackTitle = trim((string)util_format_boost_overview(
                            $fallbackGame,
                            (string)($fallbackOrder['type'] ?? 'rank'),
                            $fallbackOrder
                        ));
                        if ($formattedFallbackTitle !== '') {
                            $fallbackName = $formattedFallbackTitle;
                        }
                    }
                    $fallbackClaimUrl = BSTR_URL . '/order/' . $fallbackOrderId;
                    $fallbackBody = [
                        'username' => 'New Order',
                        // No avatar_url — the channel webhook's own icon is used.
                        'content' => '<@&' . $fallbackRoute['role_id'] . '> New ' . ($fallbackServer !== '' ? $fallbackServer . ' ' : '') . 'Order - [Claim Order](' . $fallbackClaimUrl . ')',
                        'embeds' => [[
                            'title' => $fallbackName,
                            'color' => 5793266,
                            'fields' => [[
                                'name' => 'Order ID',
                                'value' => '#' . $fallbackOrderId,
                                'inline' => true,
                            ]],
                        ]],
                    ];
                    $recovered = notification_discord_send($fallbackRoute['webhook_url'], $fallbackBody);
                } catch (\Throwable $fallbackError) {
                    error_log('[notification_sender] Compact order fallback failed for notification #' . $notif['id'] . ': ' . $fallbackError->getMessage());
                }
            }

            if ($recovered) {
                db_update_row('notifications', ['id' => $notif['id']], [
                    'is_fail' => 0,
                    'is_sent' => 1,
                    'sent_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $nextFail = ((int)($notif['is_fail'] ?? 0)) + 1;
                db_update_row('notifications', ['id' => $notif['id']], [
                    'is_fail' => $nextFail,
                    'is_sent' => $nextFail >= 3 ? 1 : 0,
                    'sent_at' => $nextFail >= 3 ? date('Y-m-d H:i:s') : null,
                ]);
            }
        }
    }

    // ── EMAIL (lazy — SMTP nur verbinden wenn wirklich nötig) ──
    if ($notif['is_email']) {
        $recipientTable = ($notif['recipient'] === 'job_application') ? 'job_applications' : ($notif['recipient'] . 's');
        $user = db_get_row($recipientTable, ['id' => $notif['recipient_id']]);

        $email = trim((string)($user['email'] ?? ''));
        $displayName = trim((string)($user['username'] ?? $user['fullname'] ?? 'there'));

        if (filter_var($email ?: null, FILTER_VALIDATE_EMAIL)) {
            $mail_status = notification_email_send($email, $displayName, $notif['recipient'], $notif['type'], $notif['data']);
        } else {
            $mail_status = false;
        }
        if (!$mail_status) {
            $nextFail = ((int)($notif['is_fail'] ?? 0)) + 1;
            db_update_row('notifications', ['id' => $notif['id']], [
                'is_fail' => $nextFail,
                // A claim is not a successful send. Retry transient SMTP/DNS
                // failures on the next sender run, up to three attempts.
                'is_sent' => $nextFail >= 3 ? 1 : 0,
                'sent_at' => $nextFail >= 3 ? date('Y-m-d H:i:s') : null,
            ]);
        } else {
            // sent_at was initially used as the atomic claim timestamp. Replace
            // it with the real successful delivery-to-SMTP timestamp.
            db_update_row('notifications', ['id' => $notif['id']], [
                'is_sent' => 1,
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
        }
        get_mailer()->clearAddresses();
    }
    }
}

coming_soon_send_available_listing_emails();

if ($mail !== null) $mail->smtpClose();
flock($lockFp, LOCK_UN);
fclose($lockFp);


if (!function_exists('send_application_declined_email')) {
    /**
     * Send the "application declined" email directly to an applicant.
     *
     * Since applicants are not stored in the users/boosters/clients tables,
     * we bypass the notifications queue and call notification_email_send() directly.
     *
     * Usage (in your AJAX handler for decline_job_application):
     *
     *   $note = $_POST['decline_note'] ?? '';
     *   send_application_declined_email(
     *       $application['email'],
     *       $application['fullname'] ?? 'there',
     *       $note
     *   );
     *
     * @param string $email     Applicant's email address
     * @param string $name      Applicant's name (used as greeting)
     * @param string $note      Optional personal note from the admin (plain text)
     * @return bool             true if email was sent successfully
     */
    function send_application_declined_email(string $email, string $name = 'there', string $note = ''): bool
    {
        return notification_email_send(
            $email,
            $name,
            'client', // recipient type — used for logging only, applicants are not in the users table
            'application_declined',
            [
                // notification_email_body_load expects base64-encoded values
                'note' => base64_encode($note),
            ]
        );
    }
}


if (!function_exists('send_discord_apply_webhook')) {
    function send_discord_apply_webhook($role, array $application = []): bool
    {
        $webhooks = [
            'lol_booster' => 'https://discord.com/api/webhooks/1338109320814133339/N8Kgwjgv1kqdvt7uTMJ9a0bQOtBWVQfQnJNoLFrCG5toW88zo30aX0i11136xKzGN2KS',
            'tft_booster' => 'https://discord.com/api/webhooks/1483949024326451201/9QgXoYjX8QGjnu93dLpHgeyu7uOA4gmXGMNHg-7YK1x2FHYV-D_dBIwms16iPrgy7cBK',
            'val_booster' => 'https://discord.com/api/webhooks/1492908194349977691/zVdmfSo02RKzITKd-VgtDWckG9aUGGt8i0VAVl6EjnCl3UHf6eS1ez1lCOnJLbiaJT2f',
            'gg_girl'     => 'https://discord.com/api/webhooks/1492908335303757966/zbeViGHncnNseWKbZrC4ym9Wl7Ulkx43cHEiFByrTlkSfkvaxqc4jxAo7DEVgYhPekfj',
            'seller'      => 'https://discord.com/api/webhooks/1485037015769546943/lrDYyjbyUMAhTXca4ZQkAFOjUn--fEOKxDBrJPDXvxfTI_mIESIBcGdZGSwbH2lL1ZrF',
        ];

        $titles = [
            'lol_booster' => 'NEW LoL Booster Apply',
            'tft_booster' => 'NEW TFT Booster Apply',
            'val_booster' => 'NEW VAL Booster Apply',
            'gg_girl'     => 'NEW GG Girl Apply',
            'seller'      => 'NEW Seller Apply',
        ];

        if (empty($webhooks[$role])) {
            return false;
        }

        $id = (int)($application['id'] ?? 0);
        $roleLabel = $titles[$role] ?? 'NEW Job Apply';
        $adminUrl = defined('ADMN_URL') ? (ADMN_URL . '/job-applications') : '';

        $fields = [
            ['name' => 'Applicant', 'value' => (string)($application['fullname'] ?? '-'), 'inline' => true],
            ['name' => 'Email', 'value' => (string)($application['email'] ?? '-'), 'inline' => true],
            ['name' => 'Discord', 'value' => (string)($application['discord_tag'] ?? '-'), 'inline' => true],
            ['name' => 'Country', 'value' => (string)($application['country'] ?? '-'), 'inline' => true],
            ['name' => 'Peak Rank', 'value' => (string)($application['peak_rank'] ?? '-'), 'inline' => true],
            ['name' => 'Current Rank', 'value' => (string)($application['current_rank'] ?? '-'), 'inline' => true],
        ];

        $optionalMap = [
            'ingame_name' => 'In Game Name',
            'server_region' => 'Server',
            'languages' => 'Languages',
            'availability' => 'Availability',
            'experience' => 'Experience',
            'motivation' => 'Motivation',
            'referral' => 'Referral',
        ];

        foreach ($optionalMap as $key => $label) {
            $value = trim((string)($application[$key] ?? ''));
            if ($value === '') {
                continue;
            }
            if (mb_strlen($value) > 1000) {
                $value = mb_substr($value, 0, 997) . '...';
            }
            $fields[] = ['name' => $label, 'value' => $value, 'inline' => false];
        }

        $payload = [
            'username' => 'LoLBoost Apply System',
            'embeds' => [[
                'title' => $roleLabel,
                'color' => 0x5865F2,
                'fields' => $fields,
                'footer' => ['text' => $id > 0 ? ('Application #' . $id) : 'LoLBoost.gg'],
                'timestamp' => date('c'),
            ]],
        ];

        if ($adminUrl !== '') {
            $payload['content'] = 'Open admin panel: ' . $adminUrl;
        }

        notification_discord_send($webhooks[$role], $payload);
        return true;
    }
}
