<?= $this->layout('seller/layouts/main', ['meta' => ($meta ?? ['title' => 'Payout Requests | LoLBoost.gg'])]) ?>

<?php
require_once __DIR__ . '/_seller_rank.php';
$seller_data = defined('SELLER_DATA') ? SELLER_DATA : ($seller_data ?? []);
$spageActiveTab = 'payout';
include __DIR__ . '/_shared.php';

$methods = $methods ?? [];
$requests = $requests ?? [];
$balanceCents = (int)($seller_data['balance'] ?? 0);
$balanceEur = $balanceCents / 100;

// Fee rules (must match backend ajax.php)
$seller_sales = seller_total_sales(is_array($seller_data ?? null) ? $seller_data : []);
$seller_rank  = seller_resolved_rank(is_array($seller_data ?? null) ? $seller_data : [], $seller_sales);
$platform_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : [], $seller_sales);
$feeBank   = 3.0;
$feeCrypto = 5.0;
?>

<style>
.payout-requests-page{color:rgba(255,255,255,.92)}
.payout-requests-page .text-muted{color:rgba(255,255,255,.55)!important}
.pr-card{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:18px}
.pr-overview{padding:22px}
.pr-overview-title{font-size:20px;font-weight:800;letter-spacing:-.02em;margin-bottom:4px}
.pr-overview-sub{font-size:13px;color:rgba(255,255,255,.58);max-width:560px}
.pr-actions{display:flex;gap:10px;flex-wrap:wrap}
.pr-btn-ghost{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.88)}
.pr-btn-ghost:hover{background:rgba(255,255,255,.07);color:#fff}
.pr-balance-panel{min-width:330px;max-width:360px;margin-left:auto;padding:18px;border:1px solid rgba(255,255,255,.08);border-radius:16px;background:rgba(255,255,255,.03)}
.pr-balance-label{font-size:11px;color:rgba(255,255,255,.52);text-transform:uppercase;letter-spacing:.08em}
.pr-balance-value{font-size:28px;line-height:1;font-weight:900;letter-spacing:-.03em;margin-top:6px}
.pr-balance-sub{font-size:13px;color:rgba(255,255,255,.62);margin-top:8px}
.pr-mini-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px}
.pr-mini-stat{padding:12px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)}
.pr-mini-stat .k{display:block;font-size:11px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px}
.pr-mini-stat .v{display:block;font-size:16px;font-weight:800}
.pr-table thead th{color:rgba(255,255,255,.58);font-size:12px;border-bottom-color:rgba(255,255,255,.08)!important}
.pr-table tbody td{border-bottom-color:rgba(255,255,255,.06)!important;color:rgba(255,255,255,.9)}
.pr-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .55rem;border-radius:999px;font-size:12px;font-weight:700;border:1px solid rgba(255,255,255,.1)}
.pr-badge.pending{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.24);color:rgba(253,230,138,1)}
.pr-badge.approved,.pr-badge.completed{background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.24);color:rgba(110,231,183,1)}
.pr-badge.rejected{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.24);color:rgba(252,165,165,1)}
.pr-method{display:inline-flex;align-items:center;gap:.45rem;padding:.28rem .55rem;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);font-size:12px;font-weight:700}
.pr-modal .modal-content{background:rgba(36,38,43,.98)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:16px;color:rgba(255,255,255,.92)}
.pr-modal .form-control,.pr-modal .form-select{background:rgba(255,255,255,.03)!important;border-color:rgba(255,255,255,.10)!important;color:rgba(255,255,255,.92)!important;color-scheme:dark}
.pr-modal .form-select option{background-color:#1f2126!important;color:#fff!important}
.pr-note{font-size:12px;color:rgba(255,255,255,.58)}
@media (max-width: 991.98px){.pr-balance-panel{min-width:100%;max-width:none;margin-left:0;margin-top:14px}.pr-overview{padding:18px}}
</style>

<div class="row payout-requests-page">
  <div class="col-12">
    <?php
      $totalRequests = count($requests);
      $pendingCount = 0;
      foreach ($requests as $rq) {
        if (strtoupper((string)($rq['status'] ?? '')) === 'PENDING') $pendingCount++;
      }
    ?>
    <div class="card s-card p-4 mb-4">
      <div class="row g-4 align-items-center">
        <div class="col-lg-7">
          <div class="s-section-title" style="font-size:1.1rem;">Payout Requests</div>
          <div style="font-size:.85rem;color:var(--s-muted);">Create a payout request and keep track of your history in one clean place.</div>
          <div class="pr-actions mt-3">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sellerPayoutRequestModal" <?= empty($methods) ? 'disabled' : '' ?>>
              <i class="fa-solid fa-circle-dollar-to-slot me-1"></i> Request Payout
            </button>
            <a href="<?= BASE_URL ?>/seller-area/payout" class="btn pr-btn-ghost btn-sm">
              <i class="fa-solid fa-sliders me-1"></i> Payout Settings
            </a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="pr-balance-panel">
            <div class="pr-balance-label">Available Balance</div>
            <div class="pr-balance-value"><?= number_format($balanceEur, 2) ?> <span style="font-size:.55em;font-weight:800;opacity:.88">EUR</span></div>
            <div class="pr-balance-sub"><i class="fa-solid fa-wallet me-1"></i>Available: <?= number_format($balanceEur, 2) ?> EUR · <span style="color:<?= htmlspecialchars($seller_rank['color'] ?? '#94a3b8', ENT_QUOTES) ?>"><i class="<?= htmlspecialchars($seller_rank['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?> me-1"></i><?= htmlspecialchars($seller_rank['label'] ?? 'Beginner', ENT_QUOTES) ?></span></div>
            <div class="pr-mini-stats">
              <div class="pr-mini-stat">
                <span class="k">Pending</span>
                <span class="v"><?= (int)$pendingCount ?></span>
              </div>
              <div class="pr-mini-stat">
                <span class="k">Requests</span>
                <span class="v"><?= (int)$totalRequests ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card s-card p-4 mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <h4 class="mb-1">Request Payout</h4>
          <div class="text-muted">Use the modal to submit a payout request.</div>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sellerPayoutRequestModal" <?= empty($methods) ? 'disabled' : '' ?>>
          <i class="fa-solid fa-circle-dollar-to-slot me-1"></i> Request Payout
        </button>
      </div>
      <div class="pr-note mt-3">
        <?php if (empty($methods)): ?>
          Please save a payout method first in <a href="<?= BASE_URL ?>/seller-area/payout">Payout Settings</a>.
        <?php else: ?>
          Tip: Choose <strong>Full balance</strong> in the modal to request your entire available balance.<br>
          Rank fee benefit: <strong><?= number_format($platform_fee, 0) ?>%</strong> platform fee as <strong><?= htmlspecialchars($seller_rank['label'], ENT_QUOTES) ?></strong>.<br>Withdrawal fees: Bank Transfer <strong><?= $feeBank ?>%</strong>, Crypto <strong><?= $feeCrypto ?>%</strong>.
        <?php endif; ?>
      </div>
    </div>

    <div class="card s-card p-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">History</h4>
      </div>
      <?php if (empty($requests)): ?>
        <div class="text-muted">No payout requests yet.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 pr-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Method</th>
                <th>Status</th>
                <th class="text-end">Requested</th>
                <th class="text-end">Fee</th>
                <th class="text-end">You Receive</th>
                <th>Date</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $r):
                $st = strtoupper((string)($r['status'] ?? 'PENDING'));
                $cls = 'pending';
                if (in_array($st, ['APPROVED','COMPLETED','PAID'], true)) $cls = 'completed';
                if ($st === 'REJECTED') $cls = 'rejected';
                $method = (string)($r['method'] ?? 'bank_transfer');
                $methodLabel = $method === 'crypto' ? 'Crypto' : 'Bank Transfer';

                $grossCents = (int)($r['amount_cents'] ?? 0);

                // Use stored fee/net if available, otherwise calculate from fee_percent or method
                if (!empty($r['fee_cents']) || !empty($r['net_cents'])) {
                    $feeCents = (int)($r['fee_cents'] ?? 0);
                    $netCents = (int)($r['net_cents'] ?? 0);
                    $feePct   = (float)($r['fee_percent'] ?? 0);
                } else {
                    // Legacy rows without fee columns: calculate on-the-fly
                    $feePct   = ($method === 'crypto') ? $feeCrypto : $feeBank;
                    $feeCents = (int)round($grossCents * ($feePct / 100));
                    $netCents = max(0, $grossCents - $feeCents);
                }
              ?>
              <tr>
                <td class="text-muted">#<?= (int)$r['id'] ?></td>
                <td><span class="pr-method"><?= htmlspecialchars($methodLabel) ?></span></td>
                <td><span class="pr-badge <?= $cls ?>"><?= htmlspecialchars($st) ?></span></td>
                <td class="text-end"><?= number_format($grossCents / 100, 2) ?> EUR</td>
                <td class="text-end text-muted">-<?= number_format($feeCents / 100, 2) ?> EUR <small>(<?= number_format($feePct, 0) ?>%)</small></td>
                <td class="text-end fw-semibold"><?= number_format($netCents / 100, 2) ?> EUR</td>
                <td><?= htmlspecialchars(!empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : '—') ?></td>
                <td class="text-end">
                  <?php if ($st === 'PENDING'): ?>
                    <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="post">
                      <input type="hidden" name="action" value="seller_cancel_payout_request">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash-can me-1"></i>Cancel</button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Payout Request Modal -->
<div class="modal fade pr-modal" id="sellerPayoutRequestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08)">
        <h5 class="modal-title d-flex align-items-center gap-2 mb-0">
          <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:34px;height:34px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);">
            <i class="fa-solid fa-wallet" style="color:rgba(99,102,241,1);"></i>
          </span>
          Request Payout
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= AJAX_URL ?>" method="post" id="sellerPayoutRequestForm" class="ajax-form" autocomplete="off">
        <input type="hidden" name="action" value="seller_request_payout">
        <input type="hidden" name="full_balance" value="0" id="sellerFullBalanceHidden">
        <div class="modal-body">
          <div class="alert mb-4" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.75);">
            <div><strong>Normal payout</strong> will be processed by the admin after review.</div>
            <div class="small text-muted mt-1">Rank fee benefit: <strong><?= number_format($platform_fee, 0) ?>%</strong> platform fee as <strong><?= htmlspecialchars($seller_rank['label'], ENT_QUOTES) ?></strong>.<br>Withdrawal fees: Bank Transfer <strong><?= $feeBank ?>%</strong>, Crypto <strong><?= $feeCrypto ?>%</strong>.</div>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Withdrawal amount</label>
              <div class="input-group">
                <span class="input-group-text">€</span>
                <input type="text" inputmode="decimal" class="form-control" name="amount" id="sellerPayoutAmount" placeholder="0.00" autocomplete="off">
                <span class="input-group-text">EUR</span>
              </div>
              <div class="text-muted small mt-1">Available: <span id="sellerAvailableBalanceText"><?= number_format($balanceEur, 2) ?> EUR</span></div>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="sellerFullBalanceCheck">
                <label class="form-check-label" for="sellerFullBalanceCheck">Full balance</label>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Payout method</label>
              <select class="form-select" name="payout_method_id" id="sellerPayoutMethod" required>
                <?php foreach ($methods as $m): ?>
                  <?php $isCrypto = ((string)$m['method'] === 'crypto'); ?>
                  <option value="<?= (int)$m['id'] ?>" data-fee="<?= $isCrypto ? $feeCrypto : $feeBank ?>" <?= !empty($m['is_default']) ? 'selected' : '' ?>>
                    <?= $isCrypto ? 'Crypto' : 'Bank Transfer' ?><?= !empty($m['is_default']) ? ' (Default)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Estimated fee</label>
              <div class="form-control d-flex align-items-center" id="sellerPayoutFeePreview">0.00 EUR</div>
            </div>
            <div class="col-12">
              <label class="form-label">Estimated net amount (you receive)</label>
              <div class="form-control d-flex align-items-center fw-semibold" id="sellerPayoutNetPreview">0.00 EUR</div>
            </div>
          </div>

          <hr style="border-color:rgba(255,255,255,.08);margin:1.25rem 0;">

          <div class="row g-1" style="font-size:.875rem;">
            <div class="col-8 text-muted">Original amount</div>
            <div class="col-4 text-end" id="calcGross">€0.00</div>
            <div class="col-8 text-muted">Payout fee (<span id="calcFeePercent">0%</span>)</div>
            <div class="col-4 text-end" id="calcFeeAmt">-€0.00</div>
            <div class="col-8 fw-semibold">Amount you will receive</div>
            <div class="col-4 text-end fw-semibold" id="calcNet">€0.00</div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08)">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>Submit request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  const amountInput  = document.getElementById('sellerPayoutAmount');
  const fullCheck    = document.getElementById('sellerFullBalanceCheck');
  const fullHidden   = document.getElementById('sellerFullBalanceHidden');
  const methodSelect = document.getElementById('sellerPayoutMethod');
  const feePreview   = document.getElementById('sellerPayoutFeePreview');
  const netPreview   = document.getElementById('sellerPayoutNetPreview');
  const calcGross    = document.getElementById('calcGross');
  const calcFeeAmt   = document.getElementById('calcFeeAmt');
  const calcNet      = document.getElementById('calcNet');
  const calcFeePct   = document.getElementById('calcFeePercent');
  const available    = <?= json_encode((float)$balanceEur) ?>;

  function fmt(n){ return '€' + (isNaN(n) ? 0 : n).toFixed(2); }

  function parseAmount(){
    if (fullCheck && fullCheck.checked) return available;
    const raw = ((amountInput && amountInput.value) || '0').replace(',', '.').replace(/[^0-9.]/g, '');
    const val = parseFloat(raw || '0');
    return isNaN(val) ? 0 : val;
  }

  function currentFeePct(){
    if (!methodSelect) return 0;
    const opt = methodSelect.options[methodSelect.selectedIndex];
    return parseFloat(opt?.dataset?.fee || '0') || 0;
  }

  function updatePreview(){
    const gross   = parseAmount();
    const feePct  = currentFeePct();
    const fee     = Math.max(0, gross * feePct / 100);
    const net     = Math.max(0, gross - fee);

    if (feePreview)  feePreview.textContent  = fee.toFixed(2) + ' EUR (' + feePct.toFixed(0) + '%)';
    if (netPreview)  netPreview.textContent  = net.toFixed(2) + ' EUR';
    if (calcGross)   calcGross.textContent   = fmt(gross);
    if (calcFeeAmt)  calcFeeAmt.textContent  = '-' + fmt(fee);
    if (calcNet)     calcNet.textContent     = fmt(net);
    if (calcFeePct)  calcFeePct.textContent  = feePct.toFixed(0) + '%';

    if (amountInput) amountInput.disabled = !!(fullCheck && fullCheck.checked);
    if (fullHidden)  fullHidden.value = (fullCheck && fullCheck.checked) ? '1' : '0';
  }

  if (amountInput)  amountInput.addEventListener('input', updatePreview);
  if (fullCheck)    fullCheck.addEventListener('change', updatePreview);
  if (methodSelect) methodSelect.addEventListener('change', updatePreview);

  // Recalc when modal opens
  const modalEl = document.getElementById('sellerPayoutRequestModal');
  if (modalEl) modalEl.addEventListener('show.bs.modal', updatePreview);

  updatePreview();
})();
</script>
