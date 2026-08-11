<?php
// Booster: Payout Requests (compact, modal-based)
?>
<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Payout Requests - Booster Area | LoLBoost.gg'], 'contain' => true]) ?>

<?php
$balanceCents = (int)(BOOSTER_DATA['balance'] ?? 0);
$balanceDisplay = util_format_price_display($balanceCents);


$insuranceRequiredCents = booster_insurance_required_cents();
$frozenCents = booster_insurance_frozen_cents();
$availableCents = booster_available_for_payout_cents();
$insuranceDisplay = util_format_price_display($frozenCents);
$availableDisplay = util_format_price_display($availableCents);

$methods = $methods ?? [];
$requests = $requests ?? [];

// fee rules (must match backend ajax.php)
$feeBank = 3.0;   // Bank Transfer: 3%
$feeCrypto = 5.0; // Crypto: 5%

$pendingCount = 0;
$completedCount = 0;
$rejectedCount = 0;
$totalRequestedCents = 0;
$totalReceivedCents = 0;

foreach ($requests as $__r) {
  $__status = strtolower((string)($__r['status'] ?? 'pending'));
  if ($__status === 'pending' || $__status === 'processing') $pendingCount++;
  if ($__status === 'completed') $completedCount++;
  if ($__status === 'rejected') $rejectedCount++;

  $__gross = (int)($__r['requested_amount'] ?? 0);
  $__net = (int)($__r['requested_net_amount'] ?? 0);

  if ($__status === 'completed' && (int)($__r['processed_amount'] ?? 0) > 0) {
    $__gross = (int)($__r['processed_amount'] ?? $__gross);
    $__net = (int)($__r['processed_net_amount'] ?? $__net);
  }

  $totalRequestedCents += $__gross;
  $totalReceivedCents += $__net;
}
?>

<div class="content container-fluid payout-requests-page">

  <div class="lb-pr-hero mb-4">
    <div class="lb-pr-hero__main">
      <div class="lb-pr-hero__icon"><i class="fa-solid fa-money-bill-transfer"></i></div>
      <div>
        <h1 class="page-header-title mb-1">Payout Requests</h1>
        <p class="lb-pr-muted mb-0">Create payout requests and track your payout history.</p>
      </div>
    </div>

    <div class="lb-pr-hero__side">
      <div class="lb-pr-balance-card">
        <div>
          <div class="lb-pr-kicker">Total funds</div>
          <div class="lb-pr-balance" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="Total balance includes your available payout amount and your insurance reserve. Insurance reserve: held as security and paid out when you leave the company.">
            <?= $balanceDisplay ?> EUR
          </div>
        </div>
        <div class="lb-pr-balance-meta">
          <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Insurance reserve: held as security and paid out when you leave the company."><i class="fa-solid fa-shield-check"></i> <?= $insuranceDisplay ?> EUR insurance</span>
          <span><i class="fa-solid fa-wallet"></i> <?= $availableDisplay ?> EUR available</span>
        </div>
      </div>

      <div class="lb-pr-actions">
        <a class="lb-pr-btn is-ghost" href="/booster-area/payout">
          <i class="fa-solid fa-sliders"></i> Payout Settings
        </a>
        <button type="button" class="lb-pr-btn is-primary" data-bs-toggle="modal" data-bs-target="#payoutRequestModal" <?= empty($methods) ? 'disabled' : '' ?>>
          <i class="fa-solid fa-paper-plane"></i> Request Payout
        </button>
      </div>
    </div>
  </div>

  <div class="lb-pr-stats mb-4">
    <div class="lb-pr-stat-card">
      <span class="lb-pr-stat-icon is-available"><i class="fa-solid fa-wallet"></i></span>
      <div>
        <div class="lb-pr-stat-label">Available</div>
        <div class="lb-pr-stat-value"><?= $availableDisplay ?> EUR</div>
      </div>
    </div>
    <div class="lb-pr-stat-card">
      <span class="lb-pr-stat-icon is-pending"><i class="fa-solid fa-clock"></i></span>
      <div>
        <div class="lb-pr-stat-label">Open requests</div>
        <div class="lb-pr-stat-value"><?= (int)$pendingCount ?></div>
      </div>
    </div>
    <div class="lb-pr-stat-card">
      <span class="lb-pr-stat-icon is-completed"><i class="fa-solid fa-circle-check"></i></span>
      <div>
        <div class="lb-pr-stat-label">Completed</div>
        <div class="lb-pr-stat-value"><?= (int)$completedCount ?></div>
      </div>
    </div>
    <div class="lb-pr-stat-card">
      <span class="lb-pr-stat-icon is-total"><i class="fa-solid fa-chart-line"></i></span>
      <div>
        <div class="lb-pr-stat-label">Total received</div>
        <div class="lb-pr-stat-value"><?= util_format_price_display($totalReceivedCents) ?> EUR</div>
      </div>
    </div>
  </div>

  <?php if (empty($methods)): ?>
    <div class="lb-pr-notice is-warning mb-4">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div>You have not saved a payout method yet. Please save one first under <b>Payout Settings</b>.</div>
    </div>
  <?php else: ?>
    <div class="lb-pr-notice mb-4">
      <i class="fa-solid fa-circle-info"></i>
      <div>Choose <b>Full available amount</b> in the payout modal to request your full available balance. Payouts are processed every <b>1st</b> and <b>15th</b>.</div>
    </div>
  <?php endif; ?>

  <div class="lb-pr-history-card">
    <div class="lb-pr-history-head">
      <div>
        <h4 class="mb-1">Payout History</h4>
        <p class="lb-pr-muted mb-0">All payout requests, fees, received amounts and current status.</p>
      </div>
      <div class="lb-pr-history-count">
        <i class="fa-solid fa-list-check"></i>
        <?= is_array($requests) ? count($requests) : 0 ?> requests
      </div>
    </div>

    <?php if (empty($requests)): ?>
      <div class="lb-pr-empty">
        <div class="lb-pr-empty__icon"><i class="fa-solid fa-inbox"></i></div>
        <h5>No payout requests yet</h5>
        <p>Create your first payout request once you have an available balance.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive lb-pr-table-wrap">
        <table class="table table-borderless align-middle mb-0 lb-pr-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Method</th>
              <th>Status</th>
              <th class="text-end">Requested</th>
              <th class="text-end">Fee</th>
              <th class="text-end">Received</th>
              <th>Created</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $r): ?>
              <?php
                $st = strtolower((string)($r['status'] ?? 'pending'));
                $statusLabel = ucfirst($st);
                $statusClass = 'is-neutral';
                if ($st === 'completed') $statusClass = 'is-completed';
                if ($st === 'rejected') $statusClass = 'is-rejected';
                if ($st === 'pending' || $st === 'processing') $statusClass = 'is-pending';

                $isCrypto = (($r['method_type'] ?? '') === 'crypto');
                $methodType = $isCrypto ? 'Crypto' : 'Bank Transfer';
                $methodIcon = $isCrypto ? 'fa-coins' : 'fa-building-columns';

                $grossCents = (int)($r['requested_amount'] ?? 0);
                $feeCents = (int)($r['requested_fee_amount'] ?? 0);
                $netCents = (int)($r['requested_net_amount'] ?? 0);

                if ($st === 'completed' && (int)($r['processed_amount'] ?? 0) > 0) {
                  $grossCents = (int)($r['processed_amount'] ?? $grossCents);
                  $feeCents   = (int)($r['processed_fee_amount'] ?? $feeCents);
                  $netCents   = (int)($r['processed_net_amount'] ?? $netCents);
                }

                $noteRaw = (string)($r['note'] ?? '');
                $isFullBalance = stripos($noteRaw, '[FULL_BALANCE]') !== false;

                if ($isFullBalance && in_array($st, ['pending', 'processing'], true)) {
                  $feePercent = (($r['method_type'] ?? '') === 'crypto') ? $feeCrypto : $feeBank;
                  $grossCents = (int)$availableCents;
                  $feeCents = (int) round($grossCents * ($feePercent / 100));
                  $netCents = max(0, $grossCents - $feeCents);
                }
              ?>
              <tr>
                <td><span class="lb-pr-id">#<?= (int)$r['id'] ?></span></td>
                <td>
                  <span class="lb-pr-method">
                    <span class="lb-pr-method__icon <?= $isCrypto ? 'is-crypto' : 'is-bank' ?>"><i class="fa-solid <?= $methodIcon ?>"></i></span>
                    <span><?= esc($methodType) ?></span>
                  </span>
                </td>
                <td><span class="lb-pr-status <?= $statusClass ?>"><span></span><?= esc($statusLabel) ?></span></td>
                <td class="text-end lb-pr-money"><?= util_format_price_display($grossCents) ?> EUR</td>
                <td class="text-end lb-pr-money is-fee"><?= util_format_price_display($feeCents) ?> EUR</td>
                <td class="text-end lb-pr-money is-received"><?= util_format_price_display($netCents) ?> EUR</td>
                <td class="lb-pr-date"><?= util_format_date_display($r['created_at'] ?? '') ?></td>
                <td class="text-end">
                  <?php if ($st === 'pending'): ?>
                    <form class="ajax-form d-inline js-confirm" action="<?= AJAX_URL ?>" method="post"
                          data-confirm-title="Cancel payout request"
                          data-confirm-text="Are you sure you want to cancel this payout request?">
                      <input type="hidden" name="action" value="booster_delete_payout_request">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button class="lb-pr-cancel-btn">
                        <i class="fa-solid fa-trash-can"></i> Cancel
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="lb-pr-locked"><i class="fa-solid fa-lock"></i></span>
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

<!-- Modal -->
<div class="modal fade" id="payoutRequestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg lb-payout-dialog">
    <div class="modal-content lb-payout-modal">
      <div class="modal-header lb-payout-modal__header">
        <div class="d-flex align-items-center gap-3">
          <span class="lb-payout-modal__icon"><i class="fa-solid fa-wallet"></i></span>
          <div>
            <h5 class="modal-title mb-0">Request Payout</h5>
            <div class="lb-payout-modal__subtitle">Normal payout only, processed every 1st and 15th.</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form class="ajax-form" action="<?= AJAX_URL ?>" method="post" id="payoutRequestForm">
        <input type="hidden" name="action" value="booster_request_payout">
        <input type="hidden" name="full_balance" value="0" id="fullBalanceHidden">
        <input type="hidden" name="payout_speed" value="normal" id="payoutSpeed">
        <input type="hidden" name="payout_method_id" id="payoutMethodHidden" value="<?= !empty($methods[0]['id']) ? (int)$methods[0]['id'] : '' ?>">

        <div class="modal-body lb-payout-modal__body">
          <div class="lb-payout-info mb-4">
            <div class="lb-payout-info__icon"><i class="fa-solid fa-calendar-days"></i></div>
            <div>
              <div class="lb-payout-info__title">Normal payout schedule</div>
              <div class="lb-payout-info__text">Payouts are processed twice a month, every <strong>1st</strong> and <strong>15th</strong>.</div>
              <div class="lb-payout-info__fees">Fees: Bank Transfer <strong>3%</strong>, Crypto <strong>5%</strong>.</div>
            </div>
          </div>

          <div class="lb-payout-grid">
            <div class="lb-payout-section">
              <div class="lb-payout-section__head">
                <span><i class="fa-solid fa-money-bill-transfer me-1"></i> Amount</span>
                <span class="lb-payout-mini">Available: <?= $availableDisplay ?> EUR</span>
              </div>

              <label class="form-label">Withdrawal amount</label>
              <div class="lb-money-input">
                <span>€</span>
                <input type="text" inputmode="decimal" name="amount" id="payoutAmount" placeholder="0.00" autocomplete="off">
                <span>EUR</span>
              </div>
              <div class="text-muted small mt-2">
                Insurance reserve: <?= $insuranceDisplay ?> EUR
              </div>

              <label class="lb-check-row mt-3" for="fullBalanceCheck">
                <input class="form-check-input" type="checkbox" id="fullBalanceCheck">
                <span>
                  <strong>Full available amount</strong>
                  <small>Use your complete available payout balance.</small>
                </span>
              </label>
            </div>

            <div class="lb-payout-section">
              <div class="lb-payout-section__head">
                <span><i class="fa-solid fa-credit-card me-1"></i> Method</span>
                <a href="/booster-area/payout" class="lb-payout-mini text-decoration-none">Manage methods</a>
              </div>

              <label class="form-label">Payout method</label>
              <div class="lb-method-select" id="payoutMethodSelect">
                <button type="button" class="lb-method-select__button" id="payoutMethodButton" aria-expanded="false">
                  <span class="lb-method-select__selected">
                    <span class="lb-method-icon"><i class="fa-solid fa-building-columns"></i></span>
                    <span>
                      <strong>Select method</strong>
                      <small>Choose payout method</small>
                    </span>
                  </span>
                  <i class="fa-solid fa-chevron-down lb-method-chevron"></i>
                </button>

                <div class="lb-method-select__menu" id="payoutMethodMenu">
                  <?php foreach ($methods as $m): ?>
                    <?php
                      $mDetails = [];
                      if (!empty($m['details'])) {
                        $d = json_decode($m['details'], true);
                        if (is_array($d)) $mDetails = $d;
                      }
                      $isCrypto = ((string)$m['method'] === 'crypto');
                      $label = $isCrypto ? 'Crypto' : 'Bank Transfer';
                      $small = '';
                      if ($isCrypto) {
                        $coin = $mDetails['coin'] ?? 'USDC';
                        $net = $mDetails['network'] ?? 'Solana';
                        $addr = $mDetails['address'] ?? '';
                        $small = trim($coin . ' · ' . $net . ($addr ? ' · ' . substr($addr,0,6) . '...' . substr($addr,-4) : ''));
                      } else {
                        $iban = preg_replace('/\s+/', '', (string)($mDetails['iban'] ?? ''));
                        $small = $iban ? ('IBAN · ****' . substr($iban, -6)) : 'IBAN / Bank Transfer';
                      }
                      $fee = $isCrypto ? $feeCrypto : $feeBank;
                    ?>
                    <button type="button"
                            class="lb-method-option"
                            data-value="<?= (int)$m['id'] ?>"
                            data-method="<?= $isCrypto ? 'crypto' : 'bank_transfer' ?>"
                            data-fee="<?= $fee ?>"
                            data-label="<?= esc($label) ?><?= !empty($m['is_default']) ? ' (Default)' : '' ?>"
                            data-small="<?= esc($small) ?>"
                            data-icon="<?= $isCrypto ? 'fa-coins' : 'fa-building-columns' ?>">
                      <span class="lb-method-icon <?= $isCrypto ? 'is-crypto' : 'is-bank' ?>"><i class="fa-solid <?= $isCrypto ? 'fa-coins' : 'fa-building-columns' ?>"></i></span>
                      <span class="flex-grow-1">
                        <strong><?= esc($label) ?><?= !empty($m['is_default']) ? ' <span class=&quot;lb-default-label&quot;>Default</span>' : '' ?></strong>
                        <small><?= esc($small) ?></small>
                      </span>
                      <span class="lb-fee-chip"><?= $fee ?>% fee</span>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="lb-payout-section mt-3">
            <label class="form-label">Note (optional)</label>
            <input class="form-control lb-note-input" name="note" placeholder="Optional note for admin">
          </div>

          <div class="lb-payout-summary mt-4">
            <div class="lb-payout-summary__row">
              <span>Original amount</span>
              <strong id="calcGross">€0.00</strong>
            </div>
            <div class="lb-payout-summary__row">
              <span>Payout fee (<span id="calcFeePercent">0%</span>)</span>
              <strong id="calcFee">-€0.00</strong>
            </div>
            <div class="lb-payout-summary__row is-total">
              <span>Amount you will receive</span>
              <strong id="calcNet">€0.00</strong>
            </div>
          </div>
        </div>

        <div class="modal-footer lb-payout-modal__footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary lb-submit-btn">
            <i class="fa-solid fa-paper-plane me-1"></i> Request Payout
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Nice confirm modal (replaces browser confirm) -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
        <h5 class="modal-title" id="confirmActionTitle">Confirm</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0" id="confirmActionText">Are you sure?</p>
      </div>
      <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" id="confirmActionBtn">
          <i class="fa-solid fa-check me-1"></i> OK
        </button>
      </div>
    </div>
  </div>
</div>


<style>
  .payout-requests-page{ max-width: 1320px; margin: 0 auto; }
  .lb-pr-muted{ color: rgba(255,255,255,.48); }
  .lb-pr-hero{
    display:flex; align-items:center; justify-content:space-between; gap:1.25rem; flex-wrap:wrap;
    padding: 1.25rem; border-radius: 22px;
    background: linear-gradient(135deg, rgba(109,94,252,.10), rgba(255,255,255,.025));
    border:1px solid rgba(255,255,255,.08);
    box-shadow: 0 18px 50px rgba(0,0,0,.18);
  }
  .lb-pr-hero__main{ display:flex; align-items:center; gap:1rem; }
  .lb-pr-hero__icon{
    width:52px; height:52px; border-radius:17px; display:flex; align-items:center; justify-content:center;
    color:#c4b5fd; background:rgba(109,94,252,.15); border:1px solid rgba(109,94,252,.28); font-size:1.2rem;
  }
  .lb-pr-hero__side{ display:flex; align-items:center; gap:.85rem; flex-wrap:wrap; justify-content:flex-end; }
  .lb-pr-balance-card{
    min-width: 300px; padding:.85rem 1rem; border-radius:18px;
    background:rgba(0,0,0,.16); border:1px solid rgba(255,255,255,.08);
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
  }
  .lb-pr-kicker{ font-size:.68rem; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.42); }
  .lb-pr-balance{ font-size:1.45rem; line-height:1.1; font-weight:850; color:rgba(255,255,255,.95); }
  .lb-pr-balance-meta{ display:flex; flex-direction:column; gap:.25rem; font-size:.78rem; color:rgba(255,255,255,.46); white-space:nowrap; }
  .lb-pr-balance-meta i{ opacity:.75; margin-right:.25rem; }
  .lb-pr-actions{ display:flex; gap:.55rem; flex-wrap:wrap; }
  .lb-pr-btn{
    border:0; border-radius:12px; padding:.68rem .9rem; display:inline-flex; align-items:center; gap:.5rem;
    font-weight:750; text-decoration:none; transition:transform .08s ease, opacity .12s ease, background .12s ease;
  }
  .lb-pr-btn:hover{ transform:translateY(-1px); text-decoration:none; }
  .lb-pr-btn.is-primary{ background:linear-gradient(135deg,#6d5efc,#8b5cf6); color:#fff; }
  .lb-pr-btn.is-ghost{ background:rgba(255,255,255,.05); color:rgba(255,255,255,.78); border:1px solid rgba(255,255,255,.09); }
  .lb-pr-btn:disabled{ opacity:.45; cursor:not-allowed; }
  .lb-pr-stats{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.9rem; }
  .lb-pr-stat-card{
    display:flex; align-items:center; gap:.9rem; padding:1rem; border-radius:18px;
    background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.08);
  }
  .lb-pr-stat-icon{
    width:42px; height:42px; border-radius:14px; display:flex; align-items:center; justify-content:center;
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.09);
  }
  .lb-pr-stat-icon.is-available{ color:#c4b5fd; background:rgba(109,94,252,.12); border-color:rgba(109,94,252,.22); }
  .lb-pr-stat-icon.is-pending{ color:#fcd34d; background:rgba(245,158,11,.10); border-color:rgba(245,158,11,.20); }
  .lb-pr-stat-icon.is-completed{ color:#6ee7b7; background:rgba(16,185,129,.10); border-color:rgba(16,185,129,.20); }
  .lb-pr-stat-icon.is-total{ color:#93c5fd; background:rgba(59,130,246,.10); border-color:rgba(59,130,246,.20); }
  .lb-pr-stat-label{ color:rgba(255,255,255,.45); font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; }
  .lb-pr-stat-value{ color:rgba(255,255,255,.92); font-size:1.05rem; font-weight:850; }
  .lb-pr-notice{
    display:flex; gap:.75rem; align-items:flex-start; padding:.85rem 1rem; border-radius:16px;
    color:rgba(255,255,255,.68); background:rgba(109,94,252,.08); border:1px solid rgba(109,94,252,.16);
  }
  .lb-pr-notice i{ color:#a78bfa; margin-top:.12rem; }
  .lb-pr-notice.is-warning{ background:rgba(245,158,11,.10); border-color:rgba(245,158,11,.18); }
  .lb-pr-notice.is-warning i{ color:#fcd34d; }
  .lb-pr-history-card{
    border-radius:22px; overflow:hidden; background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.08);
    box-shadow:0 18px 50px rgba(0,0,0,.18);
  }
  .lb-pr-history-head{
    display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
    padding:1.15rem 1.35rem; border-bottom:1px solid rgba(255,255,255,.07);
  }
  .lb-pr-history-count{
    display:inline-flex; align-items:center; gap:.45rem; padding:.45rem .75rem; border-radius:999px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.62); font-weight:700; font-size:.82rem;
  }
  .lb-pr-table-wrap{ padding:1rem 1.2rem 1.2rem; }
  .lb-pr-table{ border-collapse:separate; border-spacing:0 .45rem; }
  .lb-pr-table thead th{
    padding:.65rem .75rem; color:rgba(255,255,255,.55); font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; font-weight:800;
    background:rgba(255,255,255,.045); border:0;
  }
  .lb-pr-table thead th:first-child{ border-radius:12px 0 0 12px; }
  .lb-pr-table thead th:last-child{ border-radius:0 12px 12px 0; }
  .lb-pr-table tbody tr{ background:rgba(0,0,0,.10); transition:background .12s ease, transform .08s ease; }
  .lb-pr-table tbody tr:hover{ background:rgba(255,255,255,.045); transform:translateY(-1px); }
  .lb-pr-table tbody td{ padding:.8rem .75rem; border-top:1px solid rgba(255,255,255,.05); border-bottom:1px solid rgba(255,255,255,.05); }
  .lb-pr-table tbody td:first-child{ border-left:1px solid rgba(255,255,255,.05); border-radius:14px 0 0 14px; }
  .lb-pr-table tbody td:last-child{ border-right:1px solid rgba(255,255,255,.05); border-radius:0 14px 14px 0; }
  .lb-pr-id{ color:#9db7d8; font-weight:800; }
  .lb-pr-method{ display:inline-flex; align-items:center; gap:.55rem; color:rgba(255,255,255,.85); font-weight:650; }
  .lb-pr-method__icon{ width:30px; height:30px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; }
  .lb-pr-method__icon.is-bank{ color:#a78bfa; background:rgba(109,94,252,.12); }
  .lb-pr-method__icon.is-crypto{ color:#fcd34d; background:rgba(245,158,11,.12); }
  .lb-pr-status{
    display:inline-flex; align-items:center; gap:.45rem; padding:.32rem .62rem; border-radius:999px; font-size:.72rem; font-weight:850;
    text-transform:uppercase; letter-spacing:.04em; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04);
  }
  .lb-pr-status span{ width:7px; height:7px; border-radius:50%; background:currentColor; }
  .lb-pr-status.is-completed{ color:#10d9b2; background:rgba(16,185,129,.10); border-color:rgba(16,185,129,.20); }
  .lb-pr-status.is-rejected{ color:#ff5f8f; background:rgba(255,70,120,.10); border-color:rgba(255,70,120,.20); }
  .lb-pr-status.is-pending{ color:#fcd34d; background:rgba(245,158,11,.10); border-color:rgba(245,158,11,.20); }
  .lb-pr-status.is-neutral{ color:rgba(255,255,255,.62); }
  .lb-pr-money{ font-variant-numeric:tabular-nums; font-weight:700; color:rgba(255,255,255,.80); }
  .lb-pr-money.is-fee{ color:rgba(255,190,120,.85); }
  .lb-pr-money.is-received{ color:rgba(210,240,255,.96); font-weight:900; }
  .lb-pr-date{ color:rgba(255,255,255,.45); white-space:nowrap; }
  .lb-pr-cancel-btn{
    border:1px solid rgba(255,70,120,.22); color:#fda4af; background:rgba(255,70,120,.08); border-radius:10px; padding:.42rem .62rem; font-weight:750;
  }
  .lb-pr-cancel-btn:hover{ background:rgba(255,70,120,.14); }
  .lb-pr-locked{ color:rgba(255,255,255,.30); }
  .lb-pr-empty{ text-align:center; padding:3.5rem 1rem; color:rgba(255,255,255,.55); }
  .lb-pr-empty__icon{ width:68px; height:68px; border-radius:22px; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; background:rgba(109,94,252,.10); color:#a78bfa; font-size:1.5rem; }
  .lb-pr-empty h5{ color:rgba(255,255,255,.90); }
  @media (max-width: 991.98px){
    .lb-pr-hero__side{ justify-content:flex-start; width:100%; }
    .lb-pr-balance-card{ width:100%; min-width:0; }
    .lb-pr-stats{ grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media (max-width: 575.98px){
    .lb-pr-hero{ padding:1rem; }
    .lb-pr-hero__main{ align-items:flex-start; }
    .lb-pr-hero__icon{ width:44px; height:44px; }
    .lb-pr-balance-card{ align-items:flex-start; flex-direction:column; }
    .lb-pr-actions{ width:100%; }
    .lb-pr-btn{ flex:1 1 auto; justify-content:center; }
    .lb-pr-stats{ grid-template-columns:1fr; }
    .lb-pr-table-wrap{ padding:.7rem; }
  }
</style>

<style>
  /* --- Compact inline balance block --- */
  .lb-balance-inline{ min-width: 200px; }
  .lb-balance-inline-total{
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.2;
  }
  .lb-balance-inline-sub{ font-size: .78rem; opacity: .8; }
  .lb-balance-inline-sub i{ opacity: .75; }

  @media (max-width: 991.98px){
    .lb-balance-inline{ text-align: left !important; }
  }

  /* --- Payout Request Modal look & feel (neutral grey) --- */
  #payoutRequestModal .modal-content,
  #confirmActionModal .modal-content{
    background: rgba(36,38,43,.98) !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 16px;
    box-shadow: 0 18px 60px rgba(0,0,0,.55);
    color: rgba(255,255,255,.92);
  }
  #payoutRequestModal .modal-body,
  #confirmActionModal .modal-body{
    color: rgba(255,255,255,.86);
  }
  #payoutRequestModal .form-control,
  #payoutRequestModal .form-select{
    background: rgba(0,0,0,.20) !important;
    border-color: rgba(255,255,255,.06) !important;
    color: rgba(255,255,255,.92) !important;
  }
  #payoutRequestModal .form-control::placeholder{ color: rgba(255,255,255,.35) !important; }

  /* --- Fix light dropdown (options) on dark UI --- */
  #payoutRequestModal .form-select{
    color-scheme: dark;
  }
  #payoutRequestModal .form-select option{
    background-color: #1f2126 !important;
    color: #ffffff !important;
  }
  #payoutRequestModal .form-select:focus{
    box-shadow: 0 0 0 .2rem rgba(110, 89, 255, .20) !important;
    border-color: rgba(110, 89, 255, .55) !important;
  }

</style>

<style>
  .lb-payout-dialog{ max-width: 860px; }
  .lb-payout-modal{
    background: linear-gradient(180deg, rgba(38,40,46,.98), rgba(31,33,39,.98)) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 22px !important;
    box-shadow: 0 28px 90px rgba(0,0,0,.62) !important;
    overflow: visible;
  }
  .lb-payout-modal__header,
  .lb-payout-modal__footer{ border-color: rgba(255,255,255,.08) !important; }
  .lb-payout-modal__header{ padding: 1.15rem 1.35rem; }
  .lb-payout-modal__body{ padding: 1.35rem; }
  .lb-payout-modal__footer{ padding: 1rem 1.35rem; }
  .lb-payout-modal__icon{
    width: 42px; height: 42px; border-radius: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(109,94,252,.16); border: 1px solid rgba(109,94,252,.28); color: #a78bfa;
  }
  .lb-payout-modal__subtitle{ color: rgba(255,255,255,.50); font-size: .82rem; margin-top: .15rem; }
  .lb-payout-info{
    display: flex; gap: .9rem; align-items: flex-start;
    padding: 1rem; border-radius: 16px;
    background: rgba(109,94,252,.08); border: 1px solid rgba(109,94,252,.18);
  }
  .lb-payout-info__icon{
    width: 36px; height: 36px; border-radius: 12px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(109,94,252,.16); color: #a78bfa; flex: 0 0 auto;
  }
  .lb-payout-info__title{ font-weight: 750; color: rgba(255,255,255,.92); margin-bottom: .2rem; }
  .lb-payout-info__text{ color: rgba(255,255,255,.72); font-size: .9rem; }
  .lb-payout-info__fees{ color: rgba(255,255,255,.46); font-size: .8rem; margin-top: .2rem; }
  .lb-payout-grid{ display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  @media (max-width: 767.98px){ .lb-payout-grid{ grid-template-columns: 1fr; } }
  .lb-payout-section{
    background: rgba(255,255,255,.035); border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px; padding: 1rem;
  }
  .lb-payout-section__head{
    display:flex; align-items:center; justify-content:space-between; gap: .75rem;
    margin-bottom: .9rem; color: rgba(255,255,255,.90); font-weight: 700;
  }
  .lb-payout-mini{ color: rgba(255,255,255,.45); font-size: .76rem; font-weight: 600; }
  .lb-money-input{
    display:grid; grid-template-columns: 46px 1fr 64px; align-items:center;
    border: 1px solid rgba(255,255,255,.10); border-radius: 12px;
    background: rgba(0,0,0,.18); overflow:hidden;
  }
  .lb-money-input span{
    height: 46px; display:flex; align-items:center; justify-content:center;
    color: rgba(255,255,255,.70); background: rgba(255,255,255,.035);
  }
  .lb-money-input input{
    height: 46px; border:0; outline:0; background: transparent; color:#fff; padding: 0 .9rem; font-weight: 700;
  }
  .lb-money-input:focus-within{ border-color: rgba(109,94,252,.55); box-shadow: 0 0 0 3px rgba(109,94,252,.12); }
  .lb-check-row{
    display:flex; gap:.7rem; align-items:flex-start; cursor:pointer;
    color: rgba(255,255,255,.82);
  }
  .lb-check-row small{ display:block; color: rgba(255,255,255,.45); margin-top:.1rem; }
  .lb-method-select{ position: relative; }
  .lb-method-select__button{
    width:100%; border:1px solid rgba(255,255,255,.10); background: rgba(0,0,0,.18);
    color:#fff; border-radius: 12px; padding:.72rem .8rem;
    display:flex; align-items:center; justify-content:space-between; gap:.8rem; text-align:left;
  }
  .lb-method-select__selected{ display:flex; align-items:center; gap:.75rem; min-width:0; }
  .lb-method-select__selected strong{ display:block; font-weight:750; }
  .lb-method-select__selected small, .lb-method-option small{ display:block; color:rgba(255,255,255,.48); margin-top:.05rem; }
  .lb-method-icon{
    width:38px; height:38px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center;
    background: rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); color:#c4b5fd; flex:0 0 auto;
  }
  .lb-method-icon.is-crypto{ color:#fcd34d; background:rgba(245,158,11,.12); border-color:rgba(245,158,11,.20); }
  .lb-method-icon.is-bank{ color:#a78bfa; background:rgba(109,94,252,.12); border-color:rgba(109,94,252,.22); }
  .lb-method-chevron{ color: rgba(255,255,255,.42); transition: transform .15s ease; }
  .lb-method-select.is-open .lb-method-chevron{ transform: rotate(180deg); }
  .lb-method-select__menu{
    position:absolute; z-index:1058; left:0; right:0; top:calc(100% + 8px);
    display:none; padding:.45rem; border-radius:16px;
    background: rgba(29,31,37,.98); border:1px solid rgba(255,255,255,.10);
    box-shadow:0 18px 55px rgba(0,0,0,.55); max-height:280px; overflow:auto;
  }
  .lb-method-select.is-open .lb-method-select__menu{ display:block; }
  .lb-method-option{
    width:100%; border:0; background:transparent; color:#fff; border-radius:12px; padding:.7rem;
    display:flex; align-items:center; gap:.75rem; text-align:left;
  }
  .lb-method-option:hover, .lb-method-option.is-active{ background: rgba(109,94,252,.12); }
  .lb-method-option strong{ display:block; font-weight: 750; }
  .lb-default-label{ color:#a78bfa; font-size:.72rem; margin-left:.25rem; }
  .lb-fee-chip{
    border-radius:999px; padding:.22rem .48rem; font-size:.68rem; font-weight:750;
    color:#fcd34d; background:rgba(245,158,11,.10); border:1px solid rgba(245,158,11,.18); white-space:nowrap;
  }
  .lb-note-input{ border-radius:12px !important; background:rgba(0,0,0,.18) !important; border-color:rgba(255,255,255,.10) !important; }
  .lb-payout-summary{
    border-radius: 18px; overflow:hidden; border:1px solid rgba(255,255,255,.08);
    background: rgba(0,0,0,.14);
  }
  .lb-payout-summary__row{
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    padding:.78rem 1rem; border-bottom:1px solid rgba(255,255,255,.06); color:rgba(255,255,255,.62);
  }
  .lb-payout-summary__row:last-child{ border-bottom:0; }
  .lb-payout-summary__row strong{ color:rgba(255,255,255,.88); font-variant-numeric: tabular-nums; }
  .lb-payout-summary__row.is-total{
    background: rgba(16,185,129,.08); color:rgba(255,255,255,.90); font-weight:750;
  }
  .lb-payout-summary__row.is-total strong{ color:#6ee7b7; font-size:1.05rem; }
  .lb-submit-btn{ border-radius:12px; padding:.65rem 1.05rem; }
</style>

<script>
(function () {
  const balance = <?= json_encode($availableCents / 100) ?>;

  const amountEl = document.getElementById('payoutAmount');
  const methodHidden = document.getElementById('payoutMethodHidden');
  const methodSelect = document.getElementById('payoutMethodSelect');
  const methodButton = document.getElementById('payoutMethodButton');
  const methodMenu = document.getElementById('payoutMethodMenu');
  const fullCheck = document.getElementById('fullBalanceCheck');
  const fullHidden = document.getElementById('fullBalanceHidden');

  const grossOut = document.getElementById('calcGross');
  const feeOut = document.getElementById('calcFee');
  const netOut = document.getElementById('calcNet');
  const feePctOut = document.getElementById('calcFeePercent');

  let selectedFee = 0;

  function fmt(n) {
    const v = (isNaN(n) ? 0 : n);
    return '€' + v.toFixed(2);
  }

  function parseAmount(raw) {
    if (!raw) return 0;
    const cleaned = String(raw).replace(',', '.').replace(/[^0-9.]/g, '');
    const v = parseFloat(cleaned);
    return isNaN(v) ? 0 : v;
  }

  function setSelectedMethod(option) {
    if (!option || !methodHidden || !methodButton) return;
    methodHidden.value = option.dataset.value || '';
    selectedFee = parseFloat(option.dataset.fee || '0') || 0;

    document.querySelectorAll('.lb-method-option').forEach(el => el.classList.toggle('is-active', el === option));

    const icon = option.dataset.icon || 'fa-building-columns';
    const isCrypto = option.dataset.method === 'crypto';
    const label = option.dataset.label || 'Payout method';
    const small = option.dataset.small || '';

    const selected = methodButton.querySelector('.lb-method-select__selected');
    if (selected) {
      selected.innerHTML =
        '<span class="lb-method-icon ' + (isCrypto ? 'is-crypto' : 'is-bank') + '"><i class="fa-solid ' + icon + '"></i></span>' +
        '<span><strong>' + label + '</strong><small>' + small + '</small></span>';
    }

    methodSelect.classList.remove('is-open');
    methodButton.setAttribute('aria-expanded', 'false');
    calc();
  }

  function effectiveAmount() {
    let v = parseAmount(amountEl.value);

    if (fullCheck.checked) {
      v = balance;
      amountEl.value = balance.toFixed(2);
      amountEl.setAttribute('disabled', 'disabled');
      fullHidden.value = '1';
      return v;
    }

    amountEl.removeAttribute('disabled');
    fullHidden.value = '0';

    if (v > balance) {
      v = balance;
      amountEl.value = balance.toFixed(2);
    }

    return v;
  }

  function calc() {
    const gross = effectiveAmount();
    const feePct = selectedFee;
    const fee = gross * (feePct / 100);
    const net = Math.max(0, gross - fee);

    grossOut.textContent = fmt(gross);
    feePctOut.textContent = feePct.toFixed(1).replace('.0','') + '%';
    feeOut.textContent = '-' + fmt(fee);
    netOut.textContent = fmt(net);
  }

  methodButton?.addEventListener('click', function (e) {
    e.preventDefault();
    const open = !methodSelect.classList.contains('is-open');
    methodSelect.classList.toggle('is-open', open);
    methodButton.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  methodMenu?.addEventListener('click', function (e) {
    const option = e.target.closest('.lb-method-option');
    if (!option) return;
    e.preventDefault();
    setSelectedMethod(option);
  });

  document.addEventListener('click', function (e) {
    if (!methodSelect || methodSelect.contains(e.target)) return;
    methodSelect.classList.remove('is-open');
    methodButton?.setAttribute('aria-expanded', 'false');
  });

  amountEl.addEventListener('focus', function () {
    if (!amountEl.value || amountEl.value === '0' || amountEl.value === '0.0' || amountEl.value === '0.00') {
      amountEl.value = '';
    } else {
      try { amountEl.select(); } catch (e) {}
    }
  });

  amountEl.addEventListener('blur', function () {
    if (fullCheck.checked) return;
    const v = parseAmount(amountEl.value);
    if (!amountEl.value) return;
    amountEl.value = Math.min(v, balance).toFixed(2);
  });

  amountEl.addEventListener('input', calc);
  fullCheck.addEventListener('change', calc);

  const firstOption = document.querySelector('.lb-method-option');
  if (firstOption) setSelectedMethod(firstOption);
  calc();

  const modal = document.getElementById('payoutRequestModal');
  if (modal) {
    modal.addEventListener('show.bs.modal', function () { calc(); });
  }
})();
</script>



<!-- Pretty confirm modal (replaces browser confirm) -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmActionTitle">Confirm action</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmActionBody">Are you sure?</div>
      <div class="modal-footer" style="justify-content: space-between;">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmActionOk">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const modalEl = document.getElementById('confirmActionModal');
  if (!modalEl || typeof bootstrap === 'undefined') return;
  const bs = new bootstrap.Modal(modalEl);
  const titleEl = document.getElementById('confirmActionTitle');
  const bodyEl = document.getElementById('confirmActionBody');
  const okBtn = document.getElementById('confirmActionOk');
  let pendingSubmit = null;

  function showConfirm(opts){
    titleEl.textContent = opts.title || 'Confirm action';
    bodyEl.textContent = opts.message || 'Are you sure?';
    okBtn.textContent = opts.okText || 'OK';
    okBtn.className = 'btn ' + (opts.okClass || 'btn-primary');
    pendingSubmit = opts.onConfirm || null;
    bs.show();
  }

  okBtn.addEventListener('click', function(){
    bs.hide();
    if (typeof pendingSubmit === 'function') {
      const fn = pendingSubmit;
      pendingSubmit = null;
      fn();
    }
  });

  document.addEventListener('submit', function(e){
    const form = e.target;
    if (!form || !form.classList || !form.classList.contains('js-confirm')) return;
    e.preventDefault();
    showConfirm({
      title: form.getAttribute('data-confirm-title') || 'Confirm action',
      message: form.getAttribute('data-confirm-message') || 'Are you sure?',
      okText: form.getAttribute('data-confirm-ok') || 'OK',
      okClass: form.getAttribute('data-confirm-ok-class') || 'btn-primary',
      onConfirm: () => form.submit()
    });
  }, true);
})();
</script>
