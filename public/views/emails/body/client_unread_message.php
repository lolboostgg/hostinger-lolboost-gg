<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader'] ?? "You missed a chat with the seller — head back to the website to catch up!"]) ?>

<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$username = trim((string)($data['username'] ?? 'there')) ?: 'there';
$seller = trim((string)($data['seller_name'] ?? 'Your seller')) ?: 'Your seller';
$kind = trim((string)($data['kind_label'] ?? 'Order')) ?: 'Order';
$listing = trim((string)($data['listing_title'] ?? 'Your order')) ?: 'Your order';
$message = trim((string)($data['message'] ?? '')) ?: '[Image]';
$url = trim((string)($data['chat_url'] ?? ''));
?>

<div class="u-row-container" style="padding:20px 0 0;background-color:transparent;">
  <div class="u-row" style="margin:0 auto;min-width:320px;max-width:500px;overflow-wrap:break-word;background-color:transparent;">
    <div style="display:table;width:100%;background-color:transparent;">
      <div class="u-col u-col-100" style="max-width:500px;min-width:320px;display:table-cell;vertical-align:top;">
        <div style="background-color:#151a2f;border-radius:14px;padding:28px 22px;font-family:'Montserrat',Arial,sans-serif;color:#fff;">
          <h1 style="margin:0 0 18px;text-align:center;font-size:24px;line-height:1.35;color:#fff;">You missed a chat with the seller 👀</h1>
          <p style="margin:0 0 18px;font-size:15px;line-height:1.65;color:#fff;">Hi <strong><?= $h($username) ?></strong>, <strong><?= $h($seller) ?></strong> sent you a new message about your order. Be sure to check the website so you don't miss anything important!</p>

          <div style="margin:20px 0;padding:18px;background:#202640;border:1px solid #343b61;border-radius:10px;">
            <p style="margin:0 0 6px;color:#aeb5d2;font-size:12px;text-transform:uppercase;letter-spacing:.5px;"><?= $h($kind) ?></p>
            <p style="margin:0 0 14px;color:#fff;font-size:16px;font-weight:700;line-height:1.45;"><?= $h($listing) ?></p>
            <div style="height:1px;background:#3a4167;margin:0 0 14px;"></div>
            <p style="margin:0;color:#fff;font-size:15px;line-height:1.6;"><?= nl2br($h($message)) ?></p>
          </div>

          <?php if ($url !== ''): ?>
          <div style="text-align:center;margin:26px 0 10px;">
            <a href="<?= $h($url) ?>" target="_blank" style="display:inline-block;background:#6366f1;color:#fff !important;text-decoration:none !important;border-radius:6px;padding:13px 24px;font-size:14px;font-weight:700;">View message</a>
          </div>
          <?php endif; ?>

          <p style="margin:24px 0 0;color:#c8cce0;font-size:13px;line-height:1.6;">If you need help, reply to this email or contact our live chat.</p>
          <p style="margin:12px 0 0;color:#fff;font-size:13px;line-height:1.6;">Best regards,<br>The LBGG Team</p>
        </div>
      </div>
    </div>
  </div>
</div>
