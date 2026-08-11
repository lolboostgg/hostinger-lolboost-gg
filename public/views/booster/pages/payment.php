<?php
/**
 * Booster Payment (Method + Payout Request)
 * Path: public/views/booster/pages/payment.php
 */

if (!defined('BOOSTER_ID') || BOOSTER_ID === false) {
    redirect_url('booster-area/auth/login');
    exit;
}

$lb_errors = [];
$lb_success = null;

$LB_MIN_PAYOUT_CENTS = 2000; // €20,00

$booster_id = (int)BOOSTER_ID;

// Always fetch fresh booster (balance can change)
$booster = db_get_row('boosters', ['id' => $booster_id], true);
$balance_cents = (int)($booster['balance'] ?? 0);
$available_cents = max(0, $balance_cents);

// Flags
$flagSaved = !empty($_GET['saved']);
$flagRequested = !empty($_GET['requested']);

// POST handlers
if (!empty($_POST)) {
    $action = $_POST['action'] ?? '';

    if (function_exists('csrf_verify')) {
        if (!csrf_verify($_POST['csrf_token'] ?? '')) {
            $lb_errors[] = 'Invalid session token (CSRF). Please reload the page.';
            $action = '';
        }
    }

    if ($action === 'save_method') {
        $type = trim((string)($_POST['type'] ?? 'paypal'));
        $label = trim((string)($_POST['label'] ?? ''));
        $set_default = true;

        $payload = [];

        if ($type === 'paypal') {
            $email = trim((string)($_POST['paypal_email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $lb_errors[] = 'Please enter a valid PayPal email.';
            } else {
                $payload = ['email' => $email];
            }
        } elseif ($type === 'bank') {
            $holder = trim((string)($_POST['bank_holder'] ?? ''));
            $iban = trim((string)($_POST['bank_iban'] ?? ''));
            $bic = trim((string)($_POST['bank_bic'] ?? ''));

            if ($holder === '' || $iban === '') {
                $lb_errors[] = 'Please enter account holder and IBAN.';
            } else {
                $payload = ['account_holder' => $holder, 'iban' => $iban, 'bic' => $bic];
            }
        } elseif ($type === 'crypto') {
            $network = trim((string)($_POST['crypto_network'] ?? 'USDT-TRC20'));
            $address = trim((string)($_POST['crypto_address'] ?? ''));
            if ($address === '') {
                $lb_errors[] = 'Please enter your wallet address.';
            } else {
                $payload = ['network' => $network, 'address' => $address];
            }
        } else {
            $lb_errors[] = 'Invalid payment method type.';
        }

        if (empty($lb_errors)) {
            $res = booster_payment_method_save($booster_id, $type, $payload, $label, $set_default);
            if (!empty($res['ok'])) {
                redirect_url('booster-area/payment?saved=1');
                exit;
            } else {
                $lb_errors[] = 'Could not save method: ' . ($res['error'] ?? 'unknown');
            }
        }
    }

    if ($action === 'request_payout') {
        $amount_input = (string)($_POST['amount'] ?? '');
        $note = trim((string)($_POST['note'] ?? ''));

        $amount_cents = lb_money_str_to_cents($amount_input);

        if ($amount_cents <= 0) $lb_errors[] = 'Please enter a valid payout amount.';
        if ($amount_cents < $LB_MIN_PAYOUT_CENTS) $lb_errors[] = 'Minimum payout is ' . lb_money_cents_to_str($LB_MIN_PAYOUT_CENTS) . '.';
        if ($available_cents <= 0) $lb_errors[] = 'Your available amount is not enough.';
        if ($amount_cents > $available_cents) $lb_errors[] = 'Amount exceeds your available amount.';

        if (empty($lb_errors)) {
            $res = booster_payout_request_create($booster_id, $amount_cents, $note);
            if (!empty($res['ok'])) {
                redirect_url('booster-area/payment?requested=1');
                exit;
            } else {
                $err = $res['error'] ?? 'unknown';
                if ($err === 'pending_exists') $lb_errors[] = 'You already have a pending payout request.';
                elseif ($err === 'no_payment_method') $lb_errors[] = 'Please save a payment method first.';
                else $lb_errors[] = 'Could not create request: ' . $err;
            }
        }
    }
}

// Load UI data
$default_method = booster_payment_method_get_default($booster_id);

// Pending sum
$pending_sum = 0;
$pending_rows = db_run_query("SELECT requested_amount FROM booster_payout_requests WHERE booster_id=" . intval($booster_id) . " AND status='pending'");
if (is_array($pending_rows)) foreach ($pending_rows as $r) $pending_sum += (int)($r['requested_amount'] ?? 0);

// Recent requests
$recent = db_run_query("
    SELECT pr.*,
           pm.type AS method_type,
           pm.data_last4 AS method_last4,
           pm.label AS method_label
    FROM booster_payout_requests pr
    LEFT JOIN booster_payment_methods pm ON pm.id = pr.payment_method_id
    WHERE pr.booster_id = " . intval($booster_id) . "
    ORDER BY pr.id DESC
    LIMIT 200
");
if (!is_array($recent)) $recent = [];

// Badge helper
function lb_badge_class($st) {
    if ($st === 'approved') return 'bg-soft-success text-success';
    if ($st === 'declined' || $st === 'cancelled') return 'bg-soft-danger text-danger';
    if ($st === 'partially_approved') return 'bg-soft-warning text-warning';
    return 'bg-soft-secondary text-secondary';
}
?>

<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Payment - Booster Area | LoLBoost.gg', 'h1' => 'Payment', 'description' => 'Set payout method and request payouts.']]) ?>

<?php if ($flagSaved): ?>
  <div class="alert alert-soft-success mb-3">
    <i class="fa-duotone fa-check-circle me-1"></i> Payment method saved.
  </div>
<?php endif; ?>

<?php if ($flagRequested): ?>
  <div class="alert alert-soft-success mb-3">
    <i class="fa-duotone fa-check-circle me-1"></i> Your payout request has been created.
  </div>
<?php endif; ?>

<?php if (!empty($lb_errors)): ?>
  <div class="alert alert-soft-danger mb-3">
    <strong class="d-block mb-2">Error</strong>
    <ul class="mb-0">
      <?php foreach ($lb_errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-4">
  <!-- LEFT: Wallet + Request -->
  <div class="col-12 col-xl-7">
    <div class="card">
      <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
          <div class="col-12 col-md">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-header-title">Wallet</h5>
              <span class="badge bg-soft-warning text-warning">Min: <?= esc(lb_money_cents_to_str($LB_MIN_PAYOUT_CENTS)) ?></span>
            </div>
            <small class="text-muted d-block mt-1">Available for payout & payout request</small>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-12 col-md-6">
            <div class="card card-sm card-borderless bg-soft-dark">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-muted">Available for Payout</div>
                    <div class="h2 mb-0"><?= esc(lb_money_cents_to_str($available_cents)) ?></div>
                  </div>
                  <div class="avatar avatar-sm avatar-soft-primary">
                    <span class="avatar-initials"><i class="fa-duotone fa-wallet"></i></span>
                  </div>
                </div>
                <small class="text-muted d-block mt-2">Stored as cents in DB (boosters.balance).</small>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="card card-sm card-borderless bg-soft-dark">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-muted">Pending Requests</div>
                    <div class="h2 mb-0"><?= esc(lb_money_cents_to_str($pending_sum)) ?></div>
                  </div>
                  <div class="avatar avatar-sm avatar-soft-warning">
                    <span class="avatar-initials"><i class="fa-duotone fa-hourglass-half"></i></span>
                  </div>
                </div>
                <small class="text-muted d-block mt-2">Pending will be paid after admin approval.</small>
              </div>
            </div>
          </div>
        </div>

        <form method="post" action="">
          <?php if (function_exists('csrf_token')): ?>
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
          <?php endif; ?>
          <input type="hidden" name="action" value="request_payout">

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Payout Amount (EUR)</label>
              <input name="amount" class="form-control" placeholder="e.g. 50,00" inputmode="decimal">
              <small class="text-muted">Max: <?= esc(lb_money_cents_to_str($available_cents)) ?></small>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Default Method</label>
              <input class="form-control" value="<?= esc($default_method['preview'] ?? 'No method saved') ?>" disabled>
              <small class="text-muted">Save a method on the right if empty.</small>
            </div>

            <div class="col-12">
              <label class="form-label">Note (optional)</label>
              <textarea name="note" class="form-control" rows="3" placeholder="Optional message to admin"></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary"
                      type="submit"
                      <?= (!$default_method || $available_cents < $LB_MIN_PAYOUT_CENTS) ? 'disabled' : '' ?>>
                Request Payout
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Recent Requests (datatable style like Payments page) -->
    <div class="card mt-4">
      <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
          <div class="col-12 col-md">
            <h5 class="card-header-title">Recent Requests</h5>
          </div>

          <div class="col-auto">
            <form>
              <div class="input-group input-group-merge input-group-flush">
                <div class="input-group-prepend input-group-text">
                  <i class="fa-duotone fa-search"></i>
                </div>
                <input id="datatableWithSearchInput" type="search" class="form-control"
                       placeholder="Search requests" aria-label="Search requests">
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="table-responsive datatable-custom">
        <table
          class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
          data-hs-datatables-options='{
            "order": [[0, "desc"]],
            "info": { "totalQty": "#datatableEntriesInfoTotalQty" },
            "entries": "#datatableEntries",
            "search": "#datatableWithSearchInput",
            "isResponsive": false,
            "isShowPaging": false,
            "pagination": "datatableWithSearchPagination"
          }'
          id="requests_table"
        >
          <thead class="thead-light">
            <tr>
              <th>ID</th>
              <th>Status</th>
              <th>Requested</th>
              <th>Approved</th>
              <th>Method</th>
              <th class="text-end">Created At</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($recent as $r): ?>
              <?php
                $st = (string)($r['status'] ?? '');
                $badge = lb_badge_class($st);

                $requested = (int)($r['requested_amount'] ?? 0);
                $approved = $r['approved_amount'];

                $mt = (string)($r['method_type'] ?? '');
                $ml4 = (string)($r['method_last4'] ?? '');
                $mLabel = (string)($r['method_label'] ?? '');

                $method_txt = $mt ? strtoupper($mt) : '—';
                if ($ml4 !== '') $method_txt .= ' • **' . esc($ml4);
                if ($mLabel !== '') $method_txt .= ' • ' . esc($mLabel);
              ?>
              <tr>
                <td class="fw-500">#<?= (int)$r['id'] ?></td>
                <td><span class="badge <?= esc($badge) ?>"><?= esc($st) ?></span></td>
                <td class="fw-500"><?= esc(lb_money_cents_to_str($requested)) ?></td>
                <td class="fw-500"><?= ($approved === null) ? '—' : esc(lb_money_cents_to_str((int)$approved)) ?></td>
                <td class="text-muted"><?= $method_txt ?></td>
                <td class="fw-500 text-end" data-order="<?= esc((string)($r['created_at'] ?? '')) ?>">
                  <?= esc((string)($r['created_at'] ?? '')) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
          <div class="col-sm mb-2 mb-sm-0">
            <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
              <span class="me-2">Showing:</span>

              <div class="tom-select-custom">
                <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                        autocomplete="off" data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
                  <option value="4">4</option>
                  <option value="6">6</option>
                  <option value="8" selected>8</option>
                  <option value="12">12</option>
                </select>
              </div>

              <span class="text-secondary me-2">of</span>
              <span id="datatableEntriesInfoTotalQty"></span>
            </div>
          </div>

          <div class="col-sm-auto">
            <div class="d-flex justify-content-center justify-content-sm-end">
              <nav id="datatableWithSearchPagination" aria-label="Requests pagination"></nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: Payment Method -->
  <div class="col-12 col-xl-5">
    <div class="card">
      <div class="card-header">
        <h5 class="card-header-title">Payment Method</h5>
        <small class="text-muted d-block mt-1">Save your payout method</small>
      </div>

      <div class="card-body">
        <ul class="nav nav-pills mb-3" id="payTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-paypal" type="button" role="tab">PayPal</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-bank" type="button" role="tab">Bank (SEPA)</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-crypto" type="button" role="tab">Crypto</button>
          </li>
        </ul>

        <form method="post" action="">
          <?php if (function_exists('csrf_token')): ?>
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
          <?php endif; ?>
          <input type="hidden" name="action" value="save_method">
          <input type="hidden" name="type" id="pm_type" value="paypal">

          <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-paypal" role="tabpanel">
              <div class="mb-3">
                <label class="form-label">PayPal Email</label>
                <input name="paypal_email" type="email" class="form-control" placeholder="name@mail.com">
              </div>
              <div class="mb-3">
                <label class="form-label">Label (optional)</label>
                <input name="label" class="form-control" placeholder="e.g. Main PayPal">
              </div>
            </div>

            <div class="tab-pane fade" id="tab-bank" role="tabpanel">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label">Account Holder</label>
                  <input name="bank_holder" class="form-control" placeholder="Max Mustermann">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">IBAN</label>
                  <input name="bank_iban" class="form-control" placeholder="DE00 0000 0000 0000 0000 00">
                </div>
                <div class="col-12">
                  <label class="form-label">BIC (optional)</label>
                  <input name="bank_bic" class="form-control" placeholder="COBADEFFXXX">
                </div>
                <div class="col-12">
                  <label class="form-label">Label (optional)</label>
                  <input name="label" class="form-control" placeholder="e.g. SEPA">
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-crypto" role="tabpanel">
              <div class="mb-3">
                <label class="form-label">Network</label>
                <select class="form-select" name="crypto_network">
                  <option value="USDT-TRC20">USDT (TRC20)</option>
                  <option value="USDT-ERC20">USDT (ERC20)</option>
                  <option value="BTC">BTC</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Wallet Address</label>
                <input name="crypto_address" class="form-control" placeholder="TQxx... / 0x...">
              </div>
              <div class="mb-3">
                <label class="form-label">Label (optional)</label>
                <input name="label" class="form-control" placeholder="e.g. TRC20">
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary" type="submit">Save Method</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
  $(document).on('ready', function () {
    // Datatable (same as other pages)
    HSCore.components.HSDatatables.init($('#requests_table'), {
      language: {
        zeroRecords: `<div class="text-center p-4">
          <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
          <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
          <p class="mb-0">No data to show</p>
        </div>`
      }
    });

    // Keep hidden type in sync with active tab
    function setType(type) { $('#pm_type').val(type); }
    $('button[data-bs-target="#tab-paypal"]').on('click', function(){ setType('paypal'); });
    $('button[data-bs-target="#tab-bank"]').on('click', function(){ setType('bank'); });
    $('button[data-bs-target="#tab-crypto"]').on('click', function(){ setType('crypto'); });
  });
</script>
<?= $this->end() ?>
