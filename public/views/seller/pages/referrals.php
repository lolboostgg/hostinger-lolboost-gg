<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<?php
$seller_id = (int)($seller_data['id'] ?? 0);
$spageActiveTab = 'referrals';
include __DIR__ . '/_shared.php';

$refSettings = function_exists('lb_referral_get_settings') ? lb_referral_get_settings() : [];
$refData = function_exists('lb_referral_get_dashboard_data')
    ? lb_referral_get_dashboard_data('seller', $seller_id)
    : [
        'share_url' => '',
        'clicks' => 0,
        'signups' => 0,
        'purchases' => 0,
        'earnings_cents' => 0,
    ];
$rewardPercent = (float)($refSettings['seller_reward_percent'] ?? $refSettings['booster_reward_percent'] ?? 5);
?>

<div class="card s-card">
  <div class="card-header"><h4 class="card-header-title">Referral Program</h4></div>
  <div class="card-body">
    <p class="text-muted mb-4">Share your personal link and earn EUR balance for every completed referred order.</p>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-xl-3"><div class="s-stat-card"><div class="s-item-key text-success">Earnings</div><div class="s-stat-value">€<?= number_format(((int)($refData['earnings_cents'] ?? 0)) / 100, 2, ',', '.') ?></div><div class="s-item-key">Seller balance</div></div></div>
      <div class="col-sm-6 col-xl-3"><div class="s-stat-card"><div class="s-item-key text-primary">Clicks</div><div class="s-stat-value"><?= (int)($refData['clicks'] ?? 0) ?></div></div></div>
      <div class="col-sm-6 col-xl-3"><div class="s-stat-card"><div class="s-item-key text-danger">Signups</div><div class="s-stat-value"><?= (int)($refData['signups'] ?? 0) ?></div></div></div>
      <div class="col-sm-6 col-xl-3"><div class="s-stat-card"><div class="s-item-key text-warning">Purchases</div><div class="s-stat-value"><?= (int)($refData['purchases'] ?? 0) ?></div></div></div>
    </div>

    <label class="form-label">Your referral link</label>
    <div class="d-flex gap-2 flex-column flex-md-row">
      <input type="text" class="form-control" id="sellerReferralLink" readonly value="<?= htmlspecialchars((string)($refData['share_url'] ?? ''), ENT_QUOTES) ?>">
      <button type="button" class="btn btn-primary" id="copySellerReferralLink">Copy Link</button>
    </div>

    <div class="alert alert-soft-primary mt-4 mb-0">
      Reward config: <strong><?= number_format($rewardPercent, 2, '.', '') ?>%</strong> of each completed referred order is credited to your seller balance.
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
  var button = document.getElementById('copySellerReferralLink');
  var input = document.getElementById('sellerReferralLink');
  if (!button || !input) return;
  button.addEventListener('click', function () {
    input.select();
    input.setSelectionRange(0, input.value.length);
    navigator.clipboard.writeText(input.value).then(function () {
      var original = button.textContent;
      button.textContent = 'Copied';
      setTimeout(function () { button.textContent = original; }, 1400);
    });
  });
})();
</script>
<style>
.s-stat-card{background:rgba(255,255,255,.03);border:1px solid #2a2f3a;border-radius:12px;padding:16px;height:100%}
.s-stat-value{font-size:2rem;font-weight:700;line-height:1.1;margin-top:6px}
.alert-soft-primary{background:rgba(124,92,255,.14);border:1px solid rgba(124,92,255,.22);color:#cfc6ff;border-radius:12px}
</style>
<?= $this->end() ?>
