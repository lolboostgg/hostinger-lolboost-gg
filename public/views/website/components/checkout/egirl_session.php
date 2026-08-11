<?php
$order = $data['order'] ?? [];
$rawNotes = (string)($order['client_notes'] ?? '');
$details = [];

if (preg_match('/(?:^|\R)DATA:(\{.*\})\s*$/s', $rawNotes, $match)) {
    $decoded = json_decode($match[1], true);
    if (is_array($decoded)) {
        $details = $decoded;
    }
}

// Legacy bookings may not have the JSON payload yet.
foreach (preg_split('/\R+/', $rawNotes) as $line) {
    if (preg_match('/^(Mode|Server|Rank|Amount|Assignment|Notes)\s*:\s*(.*)$/i', trim($line), $match)) {
        $key = strtolower($match[1]);
        if (!isset($details[$key]) || $details[$key] === '') {
            $details[$key] = trim($match[2]);
        }
    }
}

$girlName = trim((string)($order['egirl_username'] ?? $details['egirl_name'] ?? $details['assignment'] ?? ''));
if ($girlName === '' || strtolower($girlName) === 'any available') {
    $girlName = t('Any Available');
}

$icon = trim((string)($order['egirl_icon'] ?? ''));
if ($icon === '') {
    $avatar = ASSET_URL . '/website/images/gg-girl.svg';
} elseif (preg_match('~^https?://~i', $icon)) {
    $avatar = $icon;
} elseif (strpos($icon, 'uploads/') === 0) {
    $avatar = ASSET_URL . '/' . ltrim($icon, '/');
} else {
    $avatar = ASSET_URL . '/uploads/' . ltrim($icon, '/');
}

$service = trim((string)($order['service_title'] ?? 'Session Booking'));
$mode = trim((string)($details['mode_title'] ?? $details['mode'] ?? ''));
$server = strtoupper(trim((string)($details['server'] ?? $details['region'] ?? '')));
$rank = trim((string)($details['rank_label'] ?? $details['rank'] ?? ''));
$clientNote = trim((string)($details['notes'] ?? ''));
$duration = (int)($order['unit_value'] ?? $details['amount'] ?? 1);
$unit = trim((string)($order['unit_type'] ?? 'sessions'));
?>

<style>
.eg-checkout-summary{display:grid;gap:18px}
.eg-checkout-person{display:flex;align-items:center;gap:15px;padding:4px 0 18px;border-bottom:1px solid rgba(255,255,255,.08)}
.eg-checkout-avatar{width:66px;height:66px;flex:0 0 66px;border-radius:50%;object-fit:cover;border:2px solid #a855f7;box-shadow:0 0 0 4px rgba(168,85,247,.12)}
.eg-checkout-kicker{display:block;margin-bottom:5px;color:#d18cff;font-size:11px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}
.eg-checkout-name{display:block;color:#fff;font-size:21px;font-weight:900;line-height:1.15}
.eg-checkout-service{display:block;margin-top:6px;color:rgba(255,255,255,.48);font-size:13px;font-weight:600}
.eg-checkout-tags{display:flex;flex-wrap:wrap;gap:8px}
.eg-checkout-tag{padding:6px 11px;border:1px solid rgba(255,255,255,.1);border-radius:999px;background:rgba(255,255,255,.05);color:rgba(255,255,255,.7);font-size:10px;font-weight:900;letter-spacing:.05em;text-transform:uppercase}
.eg-checkout-tag:first-child{border-color:rgba(234,179,8,.28);background:rgba(234,179,8,.08);color:#e7c84d}
.eg-checkout-details{display:grid;gap:0}
.eg-checkout-detail{display:flex;justify-content:space-between;align-items:flex-start;gap:24px;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.06)}
.eg-checkout-detail:last-child{border-bottom:0}
.eg-checkout-detail span{color:rgba(255,255,255,.42);font-size:13px;font-weight:700}
.eg-checkout-detail strong{max-width:70%;color:rgba(255,255,255,.86);font-size:13px;font-weight:800;text-align:right;overflow-wrap:anywhere}
.eg-checkout-client-note strong{white-space:pre-wrap}
@media(max-width:575px){
  .eg-checkout-avatar{width:56px;height:56px;flex-basis:56px}
  .eg-checkout-name{font-size:18px}
  .eg-checkout-detail{gap:14px}
}
</style>

<div class="eg-checkout-summary">
    <div class="eg-checkout-person">
        <img class="eg-checkout-avatar" src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($girlName, ENT_QUOTES, 'UTF-8') ?>">
        <div>
            <span class="eg-checkout-kicker"><?= t('GG-Girl Session') ?></span>
            <strong class="eg-checkout-name"><?= htmlspecialchars($girlName) ?></strong>
            <span class="eg-checkout-service"><?= htmlspecialchars($service) ?></span>
        </div>
    </div>

    <div class="eg-checkout-tags">
        <?php if (!empty($order['game'])): ?><span class="eg-checkout-tag"><?= htmlspecialchars(strtoupper((string)$order['game'])) ?></span><?php endif; ?>
        <?php if (!empty($order['service_type'])): ?><span class="eg-checkout-tag"><?= htmlspecialchars(strtoupper((string)$order['service_type'])) ?></span><?php endif; ?>
    </div>

    <div class="eg-checkout-details">
        <div class="eg-checkout-detail">
            <span><?= t('Duration') ?></span>
            <strong><?= $duration ?> <?= htmlspecialchars($unit) ?></strong>
        </div>
        <?php if ($mode !== ''): ?>
        <div class="eg-checkout-detail">
            <span><?= t('Mode') ?></span>
            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $mode))) ?></strong>
        </div>
        <?php endif; ?>
        <?php if ($server !== ''): ?>
        <div class="eg-checkout-detail">
            <span><?= t('Server') ?></span>
            <strong><?= htmlspecialchars($server) ?></strong>
        </div>
        <?php endif; ?>
        <?php if ($rank !== ''): ?>
        <div class="eg-checkout-detail">
            <span><?= t('Rank') ?></span>
            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $rank))) ?></strong>
        </div>
        <?php endif; ?>
        <?php if ($clientNote !== ''): ?>
        <div class="eg-checkout-detail eg-checkout-client-note">
            <span><?= t('Your note') ?></span>
            <strong><?= htmlspecialchars($clientNote) ?></strong>
        </div>
        <?php endif; ?>
    </div>
</div>
