<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller - Admin Area | LoLBoost.gg', 'h1' => 'Seller', 'description' => 'Manage marketplace seller.']]) ?>

<?php
  $fee = $data['fee_percent'] ?? null;
  $effectiveFee = ($fee !== null && $fee !== '') ? (float)$fee : (float)$default_fee;

  $sellerId = (int)($data['id'] ?? 0);
  $usernameRaw = (string)($data['username'] ?? '');
  $username = htmlspecialchars($usernameRaw, ENT_QUOTES);
  $email = htmlspecialchars((string)($data['email'] ?? ''), ENT_QUOTES);
  $discord = htmlspecialchars((string)($data['discord'] ?? ''), ENT_QUOTES);
  $balanceCents = (int)($data['balance'] ?? 0);
  $balanceEuro = number_format($balanceCents / 100, 2);

  $isBanned = (int)($data['is_banned'] ?? 0);
  $isActive = (int)($data['is_active'] ?? 0);
  $onboardingStatus = strtolower(trim((string)($data['onboarding_status'] ?? 'pending')));
  $isApproved = ($onboardingStatus === 'approved');

  $statusLabel = 'Pending Approval';
  $statusClass = 'bg-soft-warning text-warning';
  $statusIcon = 'fa-clock';
  $statusHelp = 'Seller cannot log in until the application is approved.';

  if ($isBanned) {
    $statusLabel = 'Banned';
    $statusClass = 'bg-soft-danger text-danger';
    $statusIcon = 'fa-ban';
    $statusHelp = 'Seller is banned and cannot access the seller area.';
  } elseif (!$isApproved) {
    $statusLabel = 'Pending Approval';
    $statusClass = 'bg-soft-warning text-warning';
    $statusIcon = 'fa-clock';
    $statusHelp = 'Seller is still pending approval and cannot log in yet.';
  } elseif ($isActive) {
    $statusLabel = 'Active';
    $statusClass = 'bg-soft-success text-success';
    $statusIcon = 'fa-circle-check';
    $statusHelp = 'Seller is approved and can log in normally.';
  } else {
    $statusLabel = 'Inactive';
    $statusClass = 'bg-soft-secondary text-secondary';
    $statusIcon = 'fa-circle-minus';
    $statusHelp = 'Seller is approved but currently deactivated.';
  }

  $primaryStatusActionLabel = ($isApproved && $isActive && !$isBanned) ? 'Deactivate' : 'Approve';
  $primaryStatusActionClass = ($isApproved && $isActive && !$isBanned) ? 'btn-outline-danger' : 'btn-success';
  $primaryStatusActionIcon = ($isApproved && $isActive && !$isBanned) ? 'fa-ban' : 'fa-check';

  $accounts = $accounts ?? [];
  $payouts = $payouts ?? [];
  $payments = $payments ?? [];

  $accountCount = count($accounts);
  $soldCount = count(array_filter($accounts, fn($a) => (int)($a['sold'] ?? 0) === 1));
  $refundedCount = count(array_filter($accounts, fn($a) => (int)($a['sold'] ?? 0) === 2));
  $listedCount = count(array_filter($accounts, fn($a) => (int)($a['sold'] ?? 0) === 0));
  $pendingPayoutCount = count(array_filter($payouts, fn($r) => strtoupper((string)($r['status'] ?? '')) === 'PENDING'));
  $paymentCount = count($payments);
  $applicationNote = trim((string)($data['application_note'] ?? ''));

  $avatarLetters = preg_replace('/[^A-Za-z0-9]/', '', $usernameRaw ?: (string)($data['email'] ?? 'S'));
  $avatarLetters = strtoupper(substr($avatarLetters ?: 'S', 0, 1));
?>

<div class="card mb-4 overflow-hidden seller-profile-header">
  <div class="seller-profile-banner position-relative">
    <div class="seller-profile-banner-glow"></div>
  </div>

  <div class="card-body pt-0">
    <div class="d-flex justify-content-end mt-3 mb-0">
      <div class="d-flex flex-wrap gap-2">
        <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
          <input type="hidden" name="action" value="admin_toggle_seller_status">
          <input type="hidden" name="id" value="<?= $sellerId ?>">
          <button type="submit" class="btn <?= $primaryStatusActionClass ?> btn-sm">
            <i class="fa-duotone <?= $primaryStatusActionIcon ?> me-1"></i>
            <?= $primaryStatusActionLabel ?>
          </button>
        </form>

        <button type="button" class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#adjustBalanceModal">
          <i class="fa-duotone fa-wallet me-1"></i> Adjust Balance
        </button>
      </div>
    </div>

    <div class="text-center seller-profile-meta">
      <div class="seller-profile-avatar mx-auto">
        <span><?= $avatarLetters ?></span>
      </div>

      <div class="d-inline-flex align-items-center justify-content-center gap-2 flex-wrap mt-3">
        <h2 class="page-header-title mb-0"><?= $username ?: 'Seller' ?></h2>
        <span class="badge <?= $statusClass ?>"><i class="fa-duotone <?= $statusIcon ?> me-1"></i><?= $statusLabel ?></span>
      </div>

      <div class="text-muted mt-2 small d-flex align-items-center justify-content-center gap-3 flex-wrap">
        <span><i class="fa-duotone fa-store me-1"></i>Seller Account</span>
        <span><i class="fa-duotone fa-hashtag me-1"></i><?= $sellerId ?></span>
        <?php if ($email): ?>
          <span><i class="fa-duotone fa-envelope me-1"></i><?= $email ?></span>
        <?php endif; ?>
        <?php if (!empty($data['client_id'])): ?>
          <span><i class="fa-duotone fa-user me-1"></i>Client <a href="<?= ADMN_URL ?>/client/<?= (int)$data['client_id'] ?>">#<?= (int)$data['client_id'] ?></a></span>
        <?php else: ?>
          <span><i class="fa-duotone fa-user-slash me-1"></i>No linked client</span>
        <?php endif; ?>
      </div>

      <div class="row g-2 justify-content-center mt-4 seller-stat-row">
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value">€<?= $balanceEuro ?></div>
            <div class="seller-stat-label">Balance</div>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value text-info"><?= $accountCount ?></div>
            <div class="seller-stat-label">Accounts</div>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value text-success"><?= $soldCount ?></div>
            <div class="seller-stat-label">Sold</div>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value"><?= $effectiveFee ?>%</div>
            <div class="seller-stat-label">Platform Fee</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="border-bottom mb-4 seller-tab-wrap">
  <ul class="nav nav-tabs seller-nav-tabs" id="sellerViewTabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" href="#seller-overview">Profile</a></li>
    <li class="nav-item"><a class="nav-link" href="#seller-settings">Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="#seller-accounts">Accounts</a></li>
    <li class="nav-item"><a class="nav-link" href="#seller-payouts">Payouts</a></li>
    <li class="nav-item"><a class="nav-link" href="#seller-payments">Payments</a></li>
  </ul>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="d-grid gap-4">
      <div class="card" id="seller-overview">
        <div class="card-header">
          <h4 class="card-header-title">Overview</h4>
        </div>
        <div class="card-body">
          <ul class="list-unstyled list-py-2 text-dark mb-0">
            <li class="pb-0"><span class="card-subtitle">Account</span></li>
            <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= $sellerId ?></li>
            <li><i class="fa-duotone fa-user dropdown-item-icon"></i> <?= $username ?: '—' ?></li>
            <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= $email ?: '—' ?></li>
            <li><i class="fa-duotone fa-wallet dropdown-item-icon"></i> <?= $balanceEuro ?> EUR</li>
            <li><i class="fa-duotone fa-percent dropdown-item-icon"></i> <?= $effectiveFee ?>% platform fee</li>
            <li><i class="fa-duotone <?= $statusIcon ?> dropdown-item-icon"></i> <?= $statusLabel ?></li>

            <li class="pt-4 pb-0"><span class="card-subtitle">Contact</span></li>
            <?php if ($discord): ?>
              <li><i class="fa-brands fa-discord dropdown-item-icon"></i> <?= $discord ?></li>
            <?php endif; ?>
            <?php if (!empty($data['client_id'])): ?>
              <li><i class="fa-duotone fa-user dropdown-item-icon"></i> Client #<?= (int)$data['client_id'] ?></li>
            <?php else: ?>
              <li><i class="fa-duotone fa-user-slash dropdown-item-icon"></i> No linked client</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <?php if ($applicationNote !== ''): ?>
        <div class="card">
          <div class="card-header">
            <h4 class="card-header-title">Application Note</h4>
          </div>
          <div class="card-body">
            <div class="text-muted small" style="white-space: pre-wrap;"><?= htmlspecialchars($applicationNote, ENT_QUOTES) ?></div>
          </div>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h4 class="card-header-title">Quick Stats</h4>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">Listed accounts</span>
            <span class="fw-semibold"><?= $listedCount ?></span>
          </div>
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">Sold accounts</span>
            <span class="fw-semibold"><?= $soldCount ?></span>
          </div>
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">Payout requests</span>
            <span class="fw-semibold"><?= count($payouts) ?></span>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2">
            <span class="text-muted">Payment entries</span>
            <span class="fw-semibold"><?= $paymentCount ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="d-grid gap-4">
      <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" id="seller-settings">
        <input type="hidden" name="action" value="admin_update_seller">
        <input type="hidden" name="id" value="<?= $sellerId ?>">

        <div class="card">
          <div class="card-header">
            <h4 class="card-header-title">Account Settings</h4>
          </div>

          <div class="card-body">
            <div class="row mb-4">
              <label class="col-sm-3 col-form-label form-label">Username</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="username" value="<?= $username ?>">
              </div>
            </div>

            <div class="row mb-4">
              <label class="col-sm-3 col-form-label form-label">Email</label>
              <div class="col-sm-9">
                <input type="email" class="form-control" name="email" value="<?= $email ?>">
              </div>
            </div>

            <div class="row mb-4">
              <label class="col-sm-3 col-form-label form-label">Status</label>
              <div class="col-sm-9">
                <select class="form-select" name="status_state">
                  <option value="pending" <?= $onboardingStatus !== 'approved' && !$isBanned ? 'selected' : '' ?>>Pending Approval</option>
                  <option value="active" <?= $isApproved && $isActive && !$isBanned ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $isApproved && !$isActive && !$isBanned ? 'selected' : '' ?>>Inactive</option>
                  <option value="banned" <?= $isBanned ? 'selected' : '' ?>>Banned / Disabled</option>
                </select>
                <div class="form-text"><?= htmlspecialchars($statusHelp, ENT_QUOTES) ?></div>
              </div>
            </div>

            <div class="row mb-0">
              <label class="col-sm-3 col-form-label form-label">
                Fee percent
                <div class="text-muted small">Leave empty to use default (<?= (float)$default_fee ?>%).</div>
              </label>
              <div class="col-sm-9">
                <input type="number" step="0.01" min="0" max="100" class="form-control" name="fee_percent" value="<?= ($fee !== null && $fee !== '') ? htmlspecialchars((string)$fee, ENT_QUOTES) : '' ?>" placeholder="<?= (float)$default_fee ?>">
              </div>
            </div>
          </div>

          <div class="card-footer d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">
              <i class="fa-duotone fa-floppy-disk me-1"></i> Save Changes
            </button>
          </div>
        </div>
      </form>

      <div class="card" id="seller-accounts">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title">Accounts</h4>
          <div class="d-flex gap-2">
            <span class="badge bg-soft-secondary text-secondary"><?= $accountCount ?> total</span>
            <span class="badge bg-soft-success text-success"><?= $soldCount ?> sold</span>
          </div>
        </div>

        <?php if (!empty($accounts)): ?>
          <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Title</th>
                  <th>Server</th>
                  <th class="text-end">Price</th>
                  <th>Status</th>
                  <th>Payout</th>
                  <th>Listed</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($accounts as $a): ?>
                  <tr>
                    <td><a href="<?= ADMN_URL ?>/selling-account/<?= (int)$a['id'] ?>">#<?= (int)$a['id'] ?></a></td>
                    <td><a href="<?= ADMN_URL ?>/selling-account/<?= (int)$a['id'] ?>" class="fw-500"><?= htmlspecialchars((string)($a['title'] ?? ''), ENT_QUOTES) ?></a></td>
                    <td class="text-uppercase text-muted"><?= htmlspecialchars((string)($a['server'] ?? '—'), ENT_QUOTES) ?></td>
                    <td class="text-end fw-500">€<?= number_format((int)($a['price'] ?? 0) / 100, 2) ?></td>
                    <td>
                      <?php if ((int)($a['sold'] ?? 0) === 1): ?>
                        <span class="badge bg-soft-success text-success">Sold</span>
                      <?php else: ?>
                        <span class="badge bg-soft-warning text-warning">Listed</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ((int)($a['seller_paid'] ?? 0) === 1): ?>
                        <i class="fa-solid fa-check text-success"></i>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= !empty($a['created_at']) ? date('d.m.Y', strtotime($a['created_at'])) : '—' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="card-body text-muted">No seller accounts found.</div>
        <?php endif; ?>
      </div>

      <div class="card" id="seller-payouts">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title">Payout Requests</h4>
          <span class="badge bg-soft-warning text-warning"><?= $pendingPayoutCount ?> pending</span>
        </div>

        <?php if (!empty($payouts)): ?>
          <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th class="text-end">Amount</th>
                  <th>Method</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payouts as $r): ?>
                  <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td class="text-end fw-500">€<?= number_format((int)($r['amount_cents'] ?? 0) / 100, 2) ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars((string)($r['method'] ?? '—'), ENT_QUOTES) ?></td>
                    <td>
                      <?php if (strtoupper((string)($r['status'] ?? '')) === 'PENDING'): ?>
                        <span class="badge bg-soft-warning text-warning">Pending</span>
                      <?php elseif (strtoupper((string)($r['status'] ?? '')) === 'APPROVED'): ?>
                        <span class="badge bg-soft-success text-success">Approved</span>
                      <?php else: ?>
                        <span class="badge bg-soft-danger text-danger">Rejected</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= !empty($r['created_at']) ? date('d.m.Y', strtotime($r['created_at'])) : '—' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="card-body text-muted">No payout requests yet.</div>
        <?php endif; ?>
      </div>

      <div class="card" id="seller-payments">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title">Payment History</h4>
          <span class="badge bg-soft-secondary text-secondary"><?= $paymentCount ?> entries</span>
        </div>

        <?php if (!empty($payments)): ?>
          <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
              <thead class="thead-light">
                <tr>
                  <th>Type</th>
                  <th class="text-end">Amount</th>
                  <th class="text-end">Balance After</th>
                  <th>Note</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payments as $p): ?>
                  <tr>
                    <td><span class="badge bg-soft-secondary text-secondary"><?= htmlspecialchars((string)($p['type'] ?? '—'), ENT_QUOTES) ?></span></td>
                    <td class="text-end fw-500 <?= (int)($p['amount_cents'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                      <?= (int)($p['amount_cents'] ?? 0) >= 0 ? '+' : '' ?>€<?= number_format(abs((int)($p['amount_cents'] ?? 0)) / 100, 2) ?>
                    </td>
                    <td class="text-end text-muted">€<?= number_format((int)($p['balance_after'] ?? 0) / 100, 2) ?></td>
                    <td class="text-muted" style="max-width: 320px; white-space: normal;"><?= htmlspecialchars((string)($p['note'] ?? '—'), ENT_QUOTES) ?></td>
                    <td class="text-muted"><?= !empty($p['created_at']) ? date('d.m.Y', strtotime($p['created_at'])) : '—' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="card-body text-muted">No payment history available.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="adjustBalanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="hidden" name="action" value="admin_adjust_seller_balance">
        <input type="hidden" name="seller_id" value="<?= $sellerId ?>">
        <div class="modal-header">
          <h5 class="modal-title">Adjust Balance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-soft-info mb-4">
            Current balance: <strong>€<?= $balanceEuro ?></strong>. Use positive values to add, negative values to deduct.
          </div>
          <div class="mb-3">
            <label class="form-label">Amount (€)</label>
            <input type="number" step="0.01" class="form-control" name="amount" placeholder="e.g. 10.00 or -5.00" required>
          </div>
          <div class="mb-0">
            <label class="form-label">Note</label>
            <input type="text" class="form-control" name="note" placeholder="Reason for adjustment">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Apply Adjustment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .seller-profile-banner {
    min-height: 120px;
    background:
      radial-gradient(circle at top left, rgba(255,255,255,.12), transparent 24%),
      radial-gradient(circle at bottom right, rgba(59,130,246,.20), transparent 28%),
      linear-gradient(90deg, #0b1020 0%, #1d2b64 52%, #111827 100%);
  }

  .seller-profile-banner-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(99,102,241,.32), rgba(124,58,237,.15), rgba(14,165,233,.22));
  }

  .seller-profile-meta {
    margin-top: -48px;
  }

  .seller-profile-avatar {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 800;
    color: #60a5fa;
    background: linear-gradient(180deg, #2b3146 0%, #232938 100%);
    border: 4px solid var(--bs-card-bg, #1f2937);
    box-shadow: 0 12px 30px rgba(0, 0, 0, .28);
  }

  .seller-stat-pill {
    height: 100%;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: .85rem;
    background: rgba(255,255,255,.03);
    padding: .85rem 1rem;
    text-align: center;
  }

  .seller-stat-value {
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.1;
  }

  .seller-stat-label {
    font-size: .72rem;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--bs-secondary-color, #9ca3af);
    margin-top: .25rem;
  }

  .seller-nav-tabs {
    gap: .6rem;
    border-bottom: 0;
  }

  .seller-nav-tabs .nav-link {
    border: 0;
    border-bottom: 2px solid transparent;
    color: var(--bs-secondary-color, #9ca3af);
    padding: .7rem .15rem;
    border-radius: 0;
    font-weight: 500;
    background: transparent;
  }

  .seller-nav-tabs .nav-link.active,
  .seller-nav-tabs .nav-link:hover {
    color: var(--bs-white, #fff);
    border-bottom-color: var(--bs-primary, #6c5ce7);
    background: transparent;
  }

  @media (max-width: 991.98px) {
    .seller-profile-meta {
      margin-top: -36px;
    }

    .seller-profile-avatar {
      width: 84px;
      height: 84px;
      font-size: 1.7rem;
    }
  }
</style>

<script>
  (function () {
    function ready(fn) {
      if (document.readyState !== 'loading') fn();
      else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
      var tabs = document.querySelectorAll('#sellerViewTabs .nav-link');

      function setActive(link) {
        tabs.forEach(function (tab) {
          tab.classList.remove('active');
        });
        if (link) link.classList.add('active');
      }

      tabs.forEach(function (link) {
        link.addEventListener('click', function (e) {
          var targetId = link.getAttribute('href');
          if (!targetId || targetId.charAt(0) !== '#') return;
          var target = document.querySelector(targetId);
          if (!target) return;
          e.preventDefault();
          setActive(link);
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          if (history.replaceState) history.replaceState(null, '', targetId);
        });
      });

      var sections = ['#seller-overview', '#seller-settings', '#seller-accounts', '#seller-payouts', '#seller-payments']
        .map(function (selector) { return document.querySelector(selector); })
        .filter(Boolean);

      if ('IntersectionObserver' in window && sections.length) {
        var observer = new IntersectionObserver(function (entries) {
          var visible = entries
            .filter(function (entry) { return entry.isIntersecting; })
            .sort(function (a, b) { return b.intersectionRatio - a.intersectionRatio; });

          if (!visible.length) return;

          var id = '#' + visible[0].target.id;
          var activeLink = document.querySelector('#sellerViewTabs .nav-link[href="' + id + '"]');
          setActive(activeLink);
        }, {
          rootMargin: '-18% 0px -65% 0px',
          threshold: [0.1, 0.25, 0.5]
        });

        sections.forEach(function (section) {
          observer.observe(section);
        });
      }

      if (window.location.hash) {
        var initialLink = document.querySelector('#sellerViewTabs .nav-link[href="' + window.location.hash + '"]');
        if (initialLink) setActive(initialLink);
      }
    });
  })();
</script>
