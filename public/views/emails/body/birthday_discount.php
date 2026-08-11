<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader'] ?? 'Happy birthday! Your personal LoLBoost.gg discount is ready.']) ?>

<?php
$username = htmlspecialchars((string)($data['username'] ?? 'there'), ENT_QUOTES, 'UTF-8');
$code = htmlspecialchars((string)($data['discount_code'] ?? ''), ENT_QUOTES, 'UTF-8');
$discount = (int)($data['discount_percent'] ?? 30);
$discountUrl = $data['discount_url'] ?? 'https://lolboost.gg/lol-boosting';

$expiresRaw = (string)($data['expires_at'] ?? '');
$expiresTs = $expiresRaw !== '' ? strtotime($expiresRaw) : false;
$expiresFmt = $expiresTs ? date('d-m-y H:i', $expiresTs) : $expiresRaw;
?>

<?= $title('🎉 Happy Birthday, ' . $username . '!') ?>

<?= $p("We hope you have an amazing day. As a small birthday gift from <strong>LoLBoost.gg</strong>, here is your personal discount code:") ?>

<?= $p("<strong style='font-size: 22px; letter-spacing: 1px;'>{$code}</strong>") ?>

<?= $p("Use it to get <strong>{$discount}% off</strong> your next boosting or coaching order.") ?>

<?= $btn($discountUrl, 'Claim your birthday discount') ?>

<?= $p("⏳ This code is valid for <strong>48 hours</strong> and expires at <strong>{$expiresFmt}</strong>.") ?>

<?= $hr() ?>

<?= $p('If you have any questions, just reply to this email or contact us via website live chat.') ?>

<?= $p('Best regards,<br>The LBGG Team') ?>
