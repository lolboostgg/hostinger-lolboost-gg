<?php
/**
 * Admin Payouts - Admin Area (Coins History Layout)
 * Save as: public/views/admin/pages/payouts.php
 *
 * Defensive: avoids fatals if payout helper functions are missing.
 */

if (!defined('ADMIN_ID') || ADMIN_ID === false) {
  if (function_exists('redirect_url')) {
    redirect_url('admin-area/auth/login');
  } else {
    header('Location: /admin-area/auth/login');
  }
  exit;
}

$lb_errors  = [];
$lb_success = null;

/** Money helpers (DB stores cents) */
function lb_money_cents_to_eur_str_safe($cents): string {
  $cents = (int)$cents;
  return '€' . number_format($cents / 100, 2, ',', '.');
}
function lb_money_str_to_cents_safe(string $s): int {
  $s = trim($s);
  if ($s === '') return 0;
  $s = str_replace(['€', 'EUR', ' '], '', $s);

  $lastComma = strrpos($s, ',');
  $lastDot   = strrpos($s, '.');

  if ($lastComma !== false && $lastDot !== false) {
    if ($lastComma > $lastDot) {
      $s = str_replace('.', '', $s);
      $s = str_replace(',', '.', $s);
    } else {
      $s = str_replace(',', '', $s);
    }
  } elseif ($lastComma !== false) {
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
  } else {
    $s = str_replace(',', '', $s);
  }

  return (int)round(((float)$s) * 100);
}

/** UI helpers */
function lb_status_badge_admin($st) {
  $st = (string)$st;
  if ($st === 'approved') return 'bg-soft-success text-success';
  if ($st === 'partially_approved') return 'bg-soft-warning text-warning';
  if ($st === 'declined' || $st === 'cancelled') return 'bg-soft-danger text-danger';
  return 'bg-soft-secondary text-secondary';
}
function lb_method_preview_admin($row) {
  $mtype = (string)($row['method_type'] ?? '');
  $l4    = (string)($row['method_last4'] ?? '');
  $label = (string)($row['method_label'] ?? '');

  $txt = $mtype ? strtoupper($mtype) : '—';
  if ($l4 !== '')   $txt .= ' • **' . esc($l4);
  if ($label !== '') $txt .= ' • ' . esc($label);
  return $txt;
}

/** Filter */
$status = strtolower(trim((string)($_GET['status'] ?? 'pending')));
if (!in_array($status, ['pending','approved','partially_approved','declined','cancelled'], true)) {
  $status = 'pending';
}

/** Handle actions */
if (!empty($_POST) && ($_POST['action'] ?? '') === 'decide') {

  if (function_exists('csrf_verify') && !csrf_verify($_POST['csrf_token'] ?? '')) {
    $lb_errors[] = 'Invalid session token (CSRF). Please reload the page.';
  } else {

    $request_id = (int)($_POST['request_id'] ?? 0);
    $decision   = trim((string)($_POST['decision'] ?? ''));
    $admin_note = trim((string)($_POST['admin_note'] ?? ''));

    $approve_cents = null;
    if ($decision === 'partial') {
      $approve_cents = lb_money_str_to_cents_safe((string)($_POST['approve_amount'] ?? '0'));
    }

    if (!function_exists('admin_payout_request_decide')) {
      $lb_errors[] = 'Missing function admin_payout_request_decide() in functions.php';
    } else {
      $res = admin_payout_request_decide((int)ADMIN_ID, $request_id, $decision, $approve_cents, $admin_note);
      if (!empty($res['ok'])) {
        $lb_success = 'Request #' . $request_id . ' updated.';
      } else {
        $lb_errors[] = 'Failed: ' . ($res['error'] ?? 'unknown') . (!empty($res['message']) ? (' (' . $res['message'] . ')') : '');
      }
    }
  }
}

/** Load rows */
$data = [];
if (!function_exists('admin_payout_requests_list')) {
  $lb_errors[] = 'Missing function admin_payout_requests_list() in functions.php';
} else {
  $data = admin_payout_requests_list($status, 500);
  if (!is_array($data)) $data = [];
}
?>

<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Payouts - Admin Area | LoLBoost.gg', 'h1' => 'Payouts', 'description' => 'Manage booster payout requests.']]) ?>

<?php if ($lb_success): ?>
  <div class="alert alert-soft-success mb-3">
    <i class="fa-duotone fa-check-circle me-1"></i> <?= esc($lb_success) ?>
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

<!-- Card -->
<div class="card">
  <!-- Header -->
  <div class="card-header">
    <div class="row justify-content-between align-items-center flex-grow-1">
      <div class="col-12 col-md">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-header-title">Payouts</h5>
        </div>
      </div>

      <div class="col-auto">
        <!-- Filter -->
        <form method="get" class="d-flex align-items-center gap-2">
          <!-- Status (same row, still Coins History layout style) -->
          <div class="tom-select-custom">
            <select name="status" class="js-select form-select form-select-borderless w-auto"
              autocomplete="off" data-hs-tom-select-options='{
                "searchInDropdown": false,
                "hideSearch": true
              }'>
              <?php foreach (['pending','approved','partially_approved','declined','cancelled'] as $st): ?>
                <option value="<?= esc($st) ?>" <?= $status===$st?'selected':'' ?>><?= esc($st) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Search -->
          <div class="input-group input-group-merge input-group-flush">
            <div class="input-group-prepend input-group-text">
              <i class="fa-duotone fa-search"></i>
            </div>
            <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search"
              aria-label="Search">
          </div>
          <!-- End Search -->
        </form>
        <!-- End Filter -->
      </div>
    </div>
  </div>
  <!-- End Header -->

  <!-- Table -->
  <div class="table-responsive datatable-custom">
    <table
      class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
      data-hs-datatables-options='{
        "order": [
          [7, "desc"]
        ],
        "info": {
          "totalQty": "#datatableEntriesInfoTotalQty"
        },
        "entries": "#datatableEntries",
        "search": "#datatableWithSearchInput",
        "isResponsive": false,
        "isShowPaging": false,
        "pagination": "datatableWithSearchPagination"
      }' id="payments_table">

      <thead class="thead-light">
        <tr>
          <th>Request</th>
          <th>Booster</th>
          <th>Status</th>
          <th>Requested</th>
          <th>Approved</th>
          <th>Balance</th>
          <th>Method</th>
          <th class="text-end">Created At</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($data as $row): ?>
          <?php
            $rid         = (int)($row['id'] ?? 0);
            $boosterId   = (int)($row['booster_id'] ?? 0);
            $boosterName = (string)($row['booster_username'] ?? ('Booster #' . $boosterId));

            $st          = (string)($row['status'] ?? 'pending');
            $badge       = lb_status_badge_admin($st);

            $requested   = (int)($row['requested_amount'] ?? 0);
            $approved    = $row['approved_amount'] ?? null;
            $balance     = (int)($row['booster_balance'] ?? 0);

            $canAct      = ($st === 'pending');
            $maxPartial  = min($requested, $balance);

            // Optional: method details payload (requires your list query to include method_blob or method_payload)
            $payload = [];
            if (!empty($row['method_payload']) && is_array($row['method_payload'])) {
              $payload = $row['method_payload'];
            } elseif (function_exists('lb_decrypt_payment_blob')) {
              $payload = lb_decrypt_payment_blob($row['method_blob'] ?? '');
              if (!is_array($payload)) $payload = [];
            }
            $payload_b64 = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
            $type        = (string)($row['method_type'] ?? '');
          ?>

          <tr>
            <td class="fw-500">#<?= $rid ?></td>

            <td class="fw-500">
              <?= esc($boosterName) ?>
              <div class="text-muted">Booster #<?= $boosterId ?></div>
            </td>

            <td class="fw-500">
              <span class="badge <?= esc($badge) ?>"><?= esc($st) ?></span>
            </td>

            <td class="fw-500"><?= esc(lb_money_cents_to_eur_str_safe($requested)) ?></td>

            <td class="fw-500">
              <?= ($approved === null) ? '—' : esc(lb_money_cents_to_eur_str_safe((int)$approved)) ?>
            </td>

            <td class="fw-500"><?= esc(lb_money_cents_to_eur_str_safe($balance)) ?></td>

            <td class="fw-500 text-muted">
              <?= lb_method_preview_admin($row) ?>
              <div class="mt-1">
                <button type="button"
                  class="btn btn-ghost-secondary btn-sm"
                  data-open-method="1"
                  data-type="<?= esc($type) ?>"
                  data-payload="<?= esc($payload_b64) ?>">
                  View details
                </button>
              </div>
            </td>

            <td class="fw-500 text-end" data-order="<?= esc((string)($row['created_at'] ?? '')) ?>">
              <?= !empty($row['created_at']) ? util_format_date_display($row['created_at']) : '—' ?>
            </td>

            <td class="text-end">
              <?php if (!$canAct): ?>
                <span class="text-muted">—</span>
              <?php else: ?>
                <div class="d-flex justify-content-end gap-2 flex-wrap">

                  <!-- Approve -->
                  <form method="post" action="" class="d-inline">
                    <?php if (function_exists('csrf_token')): ?>
                      <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                    <?php endif; ?>
                    <input type="hidden" name="action" value="decide">
                    <input type="hidden" name="request_id" value="<?= $rid ?>">
                    <input type="hidden" name="decision" value="approve">
                    <button class="btn btn-soft-success btn-sm" type="submit">Approve</button>
                  </form>

                  <!-- Partial -->
                  <button
                    class="btn btn-primary btn-sm"
                    type="button"
                    data-open-partial="1"
                    data-request-id="<?= $rid ?>"
                    data-requested="<?= esc(number_format($requested/100, 2, ',', '')) ?>"
                    data-balance="<?= esc(number_format($balance/100, 2, ',', '')) ?>"
                    data-max="<?= esc(number_format($maxPartial/100, 2, ',', '')) ?>"
                  >
                    Partial
                  </button>

                  <!-- Decline -->
                  <button
                    class="btn btn-soft-danger btn-sm"
                    type="button"
                    data-open-decline="1"
                    data-request-id="<?= $rid ?>"
                  >
                    Decline
                  </button>

                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- End Table -->

  <!-- Footer -->
  <div class="card-footer">
    <!-- Pagination -->
    <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
      <div class="col-sm mb-2 mb-sm-0">
        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
          <span class="me-2">Showing:</span>

          <!-- Select -->
          <div class="tom-select-custom">
            <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
              autocomplete="off" data-hs-tom-select-options='{
                "searchInDropdown": false,
                "hideSearch": true
              }'>
              <option value="4">4</option>
              <option value="6">6</option>
              <option value="8" selected>8</option>
              <option value="12">12</option>
            </select>
          </div>
          <!-- End Select -->

          <span class="text-secondary me-2">of</span>

          <!-- Pagination Quantity -->
          <span id="datatableEntriesInfoTotalQty"></span>
        </div>
      </div>

      <div class="col-sm-auto">
        <div class="d-flex justify-content-center justify-content-sm-end">
          <!-- Pagination -->
          <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
        </div>
      </div>
    </div>
    <!-- End Pagination -->
  </div>
  <!-- End Footer -->
</div>
<!-- End Card -->

<!-- Method Details Modal -->
<div class="modal fade" id="methodModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payout Method Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="methodDetails" class="d-grid gap-2"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Partial Modal -->
<div class="modal fade" id="partialModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Partial Approve</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="post" action="">
        <?php if (function_exists('csrf_token')): ?>
          <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
        <?php endif; ?>
        <input type="hidden" name="action" value="decide">
        <input type="hidden" name="decision" value="partial">
        <input type="hidden" name="request_id" id="partial_request_id" value="">

        <div class="modal-body">
          <div class="mb-2 text-muted" id="partialMeta">—</div>

          <div class="mb-3">
            <label class="form-label">Approve Amount (EUR)</label>
            <input name="approve_amount" id="partial_amount" class="form-control" inputmode="decimal" placeholder="e.g. 50,00">
            <small class="text-muted">Max: <span id="partial_max">—</span></small>
          </div>

          <div class="mb-0">
            <label class="form-label">Admin Note (optional)</label>
            <textarea name="admin_note" class="form-control" rows="3" placeholder="Reason / comment"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Confirm Partial</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Decline Modal -->
<div class="modal fade" id="declineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Decline Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="post" action="">
        <?php if (function_exists('csrf_token')): ?>
          <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
        <?php endif; ?>
        <input type="hidden" name="action" value="decide">
        <input type="hidden" name="decision" value="decline">
        <input type="hidden" name="request_id" id="decline_request_id" value="">

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Admin Note (optional)</label>
            <textarea name="admin_note" class="form-control" rows="3" placeholder="Reason / comment"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-soft-danger">Decline</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
  $(document).on('ready', function () {
    // INITIALIZATION OF DATATABLES (Coins History style)
    HSCore.components.HSDatatables.init($('#payments_table'), {
      language: {
        zeroRecords: `<div class="text-center p-4">
          <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
          <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
          <p class="mb-0">No data to show</p>
        </div>`
      }
    });

    // Method details modal
    const methodModal = new bootstrap.Modal(document.getElementById('methodModal'));

    function escHtml(s){
      return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    $(document).on('click', '[data-open-method="1"]', function(){
      const type = $(this).data('type');
      const payloadB64 = $(this).data('payload') || '';
      let payload = {};
      try { payload = JSON.parse(atob(payloadB64)); } catch(e){ payload = {}; }

      let html = '';
      if (type === 'paypal') {
        html += `<div><strong>PayPal Email</strong><div class="text-muted">${escHtml(payload.email)}</div></div>`;
      } else if (type === 'bank') {
        html += `<div><strong>Account Holder</strong><div class="text-muted">${escHtml(payload.account_holder)}</div></div>`;
        html += `<div><strong>IBAN</strong><div class="text-muted">${escHtml(payload.iban)}</div></div>`;
        html += `<div><strong>BIC</strong><div class="text-muted">${escHtml(payload.bic)}</div></div>`;
      } else if (type === 'crypto') {
        html += `<div><strong>Network</strong><div class="text-muted">${escHtml(payload.network)}</div></div>`;
        html += `<div><strong>Address</strong><div class="text-muted" style="word-break:break-all;">${escHtml(payload.address)}</div></div>`;
      } else {
        html = `<div class="text-muted">No details available.</div>`;
      }

      $('#methodDetails').html(html);
      methodModal.show();
    });

    // Partial modal
    const partialModal = new bootstrap.Modal(document.getElementById('partialModal'));
    $(document).on('click', '[data-open-partial="1"]', function(){
      const id = $(this).data('request-id');
      const requested = $(this).data('requested');
      const balance = $(this).data('balance');
      const max = $(this).data('max');

      $('#partial_request_id').val(id);
      $('#partial_amount').val(requested);
      $('#partialMeta').text(`Request #${id} • Requested: €${requested} • Balance: €${balance}`);
      $('#partial_max').text('€' + max);

      partialModal.show();
      setTimeout(() => document.getElementById('partial_amount').focus(), 150);
    });

    // Decline modal
    const declineModal = new bootstrap.Modal(document.getElementById('declineModal'));
    $(document).on('click', '[data-open-decline="1"]', function(){
      const id = $(this).data('request-id');
      $('#decline_request_id').val(id);
      declineModal.show();
    });
  });
</script>
<?= $this->end() ?>
