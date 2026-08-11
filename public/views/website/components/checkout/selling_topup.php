<?php
$topup = is_array($data ?? null) ? $data : [];
if (!function_exists('lb_tco_h')) { function lb_tco_h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('lb_topup_amount_clean')) { function lb_topup_amount_clean($value): string { $raw=trim((string)$value); if($raw==='') return ''; if(is_numeric($raw)) return rtrim(rtrim(number_format((float)$raw,2,'.',''),'0'),'.'); return $raw; } }
$title = trim((string)($topup['offer_title'] ?? $topup['title'] ?? 'Top Up'));
$gameName = trim((string)($topup['checkout_game_name'] ?? $topup['game_name'] ?? 'Game'));
$gameIcon = trim((string)($topup['checkout_game_icon'] ?? $topup['game_icon'] ?? ''));
$region = trim((string)($topup['region'] ?? 'Global'));
$platform = trim((string)($topup['platform'] ?? ''));
$qty = max(1, (int)($topup['_qty'] ?? 1));
$waitingValue = (int)($topup['waiting_time_value'] ?? 0);
$waitingUnit = strtolower(trim((string)($topup['waiting_time_unit'] ?? 'minutes')));
if ($waitingValue <= 0 && !empty($topup['waiting_time_minutes'])) { $waitingValue = (int)$topup['waiting_time_minutes']; $waitingUnit = 'minutes'; }
$waitingLabel = $waitingValue . ' ' . rtrim($waitingUnit, 's') . ($waitingValue === 1 ? '' : 's');
$unit = trim((string)($topup['offer_unit'] ?? ''));
$amount = lb_topup_amount_clean($topup['offer_amount'] ?? '');
$image = trim((string)($topup['image'] ?? ''));
$checkoutFields = isset($topup['_checkout_fields']) && is_array($topup['_checkout_fields']) ? $topup['_checkout_fields'] : [];
$isLolTopupCheckout = in_array(strtolower(trim((string)($topup['game_slug'] ?? $topup['checkout_game_slug'] ?? ''))), ['league-of-legends', 'lol', 'league'], true)
    || stripos((string)($topup['checkout_game_name'] ?? $topup['game_name'] ?? ''), 'League of Legends') !== false;
$formatLabel = static function($key) use ($isLolTopupCheckout) {
    $label = ucwords(str_replace(['_', '-'], ' ', (string)$key));
    if ($isLolTopupCheckout && (strtolower(trim((string)$key)) === 'summoner_name' || strtolower(trim($label)) === 'summoner name')) {
        return 'Riot ID';
    }
    return $label;
};

/* One flowing summary line instead of a grid of separate boxes —
   matches the "Server:euw | Rank(...) | Level 653 ..." style used by
   the account checkout summary. */
$metaParts = [];
$amountUnit = trim($amount . ' ' . $unit);
if ($amountUnit !== '') $metaParts[] = '💰 ' . $amountUnit;
if ($gameName !== '') $metaParts[] = '🎮 ' . $gameName;
if ($region !== '') $metaParts[] = '🌍 ' . $region;
if ($platform !== '') $metaParts[] = '🖥️ ' . $platform;
$metaParts[] = '🔢 x' . $qty;
if ($waitingLabel !== '') $metaParts[] = '⏱️ ' . $waitingLabel;
?>
<style>
.co-tu{color:#fff}
.co-tu *{box-sizing:border-box}
.co-tu-eyebrow{font-size:11px;text-transform:uppercase;color:#8b9bff;font-weight:900;letter-spacing:.07em;margin-bottom:12px}
.co-tu-row{display:flex;align-items:center;gap:12px}
.co-tu-icon{width:42px;height:42px;min-width:42px;border-radius:12px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.1);display:grid;place-items:center;overflow:hidden;flex-shrink:0}
.co-tu-icon img{width:100%;height:100%;object-fit:contain;padding:7px}
.co-tu-icon i{font-size:18px;color:#8b9bff}
.co-tu-text{min-width:0;flex:1 1 auto}
.co-tu-title{display:block;font-size:15px;font-weight:900;color:#fff;line-height:1.3}
.co-tu-meta{margin-top:5px;font-size:12.5px;color:#a3a7ba;font-weight:750;line-height:1.6;word-break:break-word}
.co-tu-meta b{color:#fff;font-weight:850}
.co-tu-image{width:38px;height:38px;min-width:38px;object-fit:contain;border-radius:10px;background:rgba(0,0,0,.22);padding:6px;margin-left:auto}
.co-tu-check{width:24px;height:24px;min-width:24px;border-radius:7px;background:#22c55e;color:#06210f;display:grid;place-items:center;font-size:11px;margin-left:10px}
.co-tu-divider{border:0;border-top:1px solid rgba(255,255,255,.08);margin:18px 0}
.co-tu-details-title{font-size:11px;text-transform:uppercase;color:#9197ab;font-weight:900;letter-spacing:.05em;margin-bottom:11px}
.co-tu-detail-list{display:flex;flex-direction:column;gap:10px}
.co-tu-detail-row{display:flex;align-items:baseline;justify-content:space-between;gap:14px;font-size:13px}
.co-tu-detail-row span{color:#9197ab;font-weight:750;flex:0 0 auto}
.co-tu-detail-row strong{color:#fff;font-weight:850;text-align:right;word-break:break-word}

@media(max-width:380px){
  .co-tu-image{display:none}
  .co-tu-title{font-size:14px}
  .co-tu-meta{font-size:12px}
}
</style>
<div class="co-tu">
  <div class="co-tu-eyebrow"><?= lb_tco_h($topup['service_label'] ?? 'Selected Top Up') ?></div>
  <div class="co-tu-row">
    <div class="co-tu-icon"><?php if($gameIcon): ?><img src="<?= lb_tco_h($gameIcon) ?>" alt="<?= lb_tco_h($gameName) ?>"><?php else: ?><i class="fa-duotone fa-coins"></i><?php endif; ?></div>
    <div class="co-tu-text">
      <strong class="co-tu-title"><?= lb_tco_h($title) ?></strong>
      <div class="co-tu-meta"><?= implode(' &nbsp;|&nbsp; ', array_map('lb_tco_h', $metaParts)) ?></div>
    </div>
    <?php if($image): ?><img class="co-tu-image" src="<?= lb_tco_h($image) ?>" alt="<?= lb_tco_h($title) ?>"><?php endif; ?>
    <span class="co-tu-check"><i class="fa-solid fa-check"></i></span>
  </div>

  <hr class="co-tu-divider">
  <div class="checkout-benefit-pills">
    <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span>Instant delivery straight to your account</span></div>
    <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span>100% secure &amp; encrypted checkout</span></div>
    <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span>24/7 buyer support</span></div>
  </div>

  <?php if ($checkoutFields): ?>
  <hr class="co-tu-divider">
  <div class="co-tu-details-title">Delivery Details</div>
  <div class="co-tu-detail-list">
    <?php foreach ($checkoutFields as $fieldKey => $fieldValue): ?>
      <div class="co-tu-detail-row"><span><?= lb_tco_h($formatLabel($fieldKey)) ?></span><strong><?= lb_tco_h($fieldValue) ?></strong></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
