<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?php
  $discount = (int)($data['discount_percent'] ?? 5);
  $uuid = $data['invoice_uuid'] ?? '';
  $token = $data['ab_token'] ?? '';
  $resumeUrl = 'https://lolboost.gg/checkout/' . $uuid . '?ab=' . $token;

  // Format expires as dd-mm-yy HH:MM (fallback to raw if parsing fails)
  $expiresRaw = $data['expires_at'] ?? '';
  $expiresTs = $expiresRaw ? strtotime($expiresRaw) : false;
  $expiresFmt = $expiresTs ? date('d-m-y H:i', $expiresTs) : $expiresRaw;

  $orderId = $data['order_id'] ?? '';
?>

<?= $title('🎁 Your extra ' . $discount . '% is waiting') ?>

<?= $p("Looks like your checkout on <strong>LoLBoost.gg</strong> wasn’t completed.") ?>

<?= $p("🎁 We’ve added an <strong>extra {$discount}% bonus discount</strong> to your order — it will be applied automatically when you continue.") ?>

<?= $btn($resumeUrl, '🚀 Resume checkout') ?>

<?= $p("⏳ This link is valid until <strong>{$expiresFmt}</strong>.") ?>

<?= $hr() ?>

<?= $p("Questions? Just reply to this email or reach us via website live chat. Please include <strong>Order ID: {$orderId}</strong>.") ?>

<?= $p('Best regards,<br>The LBGG Team') ?>