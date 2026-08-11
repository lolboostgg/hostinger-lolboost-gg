<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Payout Requests - Admin Area | LoLBoost.gg', 'h1' => 'Seller Payout Requests', 'description' => 'Review and process seller payout requests.']]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
  :root{
    --pr-accent: rgba(124,92,255,1);
    --pr-accent-soft: rgba(124,92,255,.22);
    --pr-accent-ring: rgba(124,92,255,.45);
    --pr-border: rgba(255,255,255,.08);
    --pr-surface: rgba(255,255,255,.04);
  }

  .pr-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:nowrap}
  .pr-filters{display:flex;align-items:center;gap:14px;flex-wrap:nowrap;max-width:100%}
  .pr-filter-block{display:flex;flex-direction:row;align-items:center;gap:.55rem;min-width:max-content}
  .pr-filter-label{font-size:.65rem;letter-spacing:.10em;text-transform:uppercase;color:rgba(255,255,255,.55);padding-left:0;display:flex;align-items:center;gap:.4rem}
  .pr-filter-label i{opacity:.9}
  .pr-search{min-width:300px}

  @media (max-width:992px){
    .pr-toolbar{flex-wrap:wrap}
    .pr-filters{flex-wrap:wrap}
    .pr-search{width:100%;min-width:0}
    .pr-filter-block{flex:1 1 auto}
  }

  #invoices_table{table-layout:fixed;width:100%}
  #invoices_table th{white-space:nowrap}
  #invoices_table td{white-space:normal}
  #invoices_table td.text-end{white-space:nowrap}

  .pr-pill-group{display:flex;gap:.45rem;padding:.32rem;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);align-items:center;flex-wrap:nowrap;white-space:nowrap}
  .pr-pill-group.is-emph{background:rgba(124,92,255,.10);border-color:rgba(124,92,255,.20)}
  .pr-pill{appearance:none;border:0;background:transparent;color:rgba(255,255,255,.86);padding:.30rem .70rem;border-radius:999px;font-size:.80rem;line-height:1;opacity:.95;cursor:pointer;transition:background .15s ease,box-shadow .15s ease,transform .05s ease;white-space:nowrap}
  .pr-pill:hover{background:rgba(255,255,255,.06)}
  .pr-pill:active{transform:translateY(1px)}
  .pr-pill.is-active{background:rgba(124,92,255,.26);box-shadow:inset 0 0 0 1px rgba(124,92,255,.55)}

  #invoices_table thead th{font-weight:650;letter-spacing:.02em;text-transform:uppercase;font-size:.72rem;color:rgba(255,255,255,.62)}
  #invoices_table tbody tr{border-top:1px solid var(--pr-border)}
  #invoices_table tbody tr:hover{background:rgba(255,255,255,.02)}

  .pr-col-id{white-space:nowrap!important;word-break:keep-all!important;overflow:visible;text-overflow:clip;width:92px;min-width:92px;max-width:92px}
  .pr-id{display:inline-flex;font-size:.78rem;font-weight:650;letter-spacing:.01em;color:rgba(255,255,255,.72);white-space:nowrap!important}
  .pr-idwrap{display:flex;align-items:center;gap:.45rem;white-space:nowrap!important}

  .badge{border-radius:10px}
  .badge-soft{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.70)}
  .badge-soft-success{background:rgba(0,200,140,.16);border-color:rgba(0,200,140,.22);color:rgba(160,255,220,.95)}
  .badge-soft-danger{background:rgba(255,70,120,.16);border-color:rgba(255,70,120,.22);color:rgba(255,170,190,.95)}
  .badge-soft-warning{background:rgba(255,180,0,.16);border-color:rgba(255,180,0,.22);color:rgba(255,220,140,.95)}
  .badge-soft-primary{background:rgba(90,160,255,.16);border-color:rgba(90,160,255,.22);color:rgba(180,210,255,.95)}

  .pr-status{display:inline-flex;align-items:center;gap:.45rem;padding:.34rem .70rem;border-radius:999px;font-weight:650;font-size:.72rem;letter-spacing:.03em;text-transform:uppercase;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);white-space:nowrap}
  .pr-status::before{content:"";width:7px;height:7px;border-radius:999px;background:rgba(255,255,255,.45);box-shadow:0 0 0 3px rgba(255,255,255,.06)}
  .pr-status.is-pending{border-color:rgba(255,180,0,.28);background:rgba(255,180,0,.12);color:rgba(255,220,140,.95)}
  .pr-status.is-pending::before{background:rgba(255,180,0,.95);box-shadow:0 0 0 3px rgba(255,180,0,.18)}
  .pr-status.is-approved{border-color:rgba(0,200,140,.28);background:rgba(0,200,140,.12);color:rgba(160,255,220,.95)}
  .pr-status.is-approved::before{background:rgba(0,200,140,.95);box-shadow:0 0 0 3px rgba(0,200,140,.18)}
  .pr-status.is-rejected{border-color:rgba(255,70,120,.28);background:rgba(255,70,120,.12);color:rgba(255,170,190,.95)}
  .pr-status.is-rejected::before{background:rgba(255,70,120,.95);box-shadow:0 0 0 3px rgba(255,70,120,.18)}

  .btn-approve{background:rgba(0,200,140,.18)!important;border:1px solid rgba(0,200,140,.28)!important;color:rgba(200,255,236,.95)!important}
  .btn-approve:hover{background:rgba(0,200,140,.24)!important}

  .pr-amounts{line-height:1.1}
  .pr-money{font-variant-numeric:tabular-nums;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace}
  .pr-amounts .received{color:rgba(160,255,220,.95)}
  .pr-amt-chips{display:flex;justify-content:flex-end;gap:.35rem;margin-top:.30rem;flex-wrap:wrap}
  .pr-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.14rem .42rem;border-radius:999px;font-size:.64rem;font-weight:650;letter-spacing:.01em;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.70);white-space:nowrap}
  .pr-chip .k{opacity:.75;font-weight:700}
  .pr-chip.is-fee{background:rgba(255,70,120,.10);border-color:rgba(255,70,120,.20);color:rgba(255,170,190,.95)}
  .pr-chip.is-req{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.10)}

  .btn-compact{padding:.35rem .55rem;border-radius:12px}

  .pr-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:1rem}
  .pr-modal.is-open{display:flex}
  .pr-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px)}
  .pr-modal__panel{position:relative;width:min(560px,100%);border-radius:18px;background:rgba(38,40,44,.96);border:1px solid rgba(255,255,255,.10);box-shadow:0 20px 60px rgba(0,0,0,.45);overflow:hidden}
  .pr-modal__header{padding:1rem 1.25rem .75rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;border-bottom:1px solid rgba(255,255,255,.08)}
  .pr-modal__title{margin:0;font-size:1.05rem;color:rgba(255,255,255,.92)}
  .pr-modal__close{width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:rgba(255,255,255,.86)}
  .pr-modal__close:hover{background:rgba(255,255,255,.06)}
  .pr-modal__body{padding:1rem 1.25rem;color:rgba(255,255,255,.70)}
  .pr-modal__footer{padding:.9rem 1.25rem 1.05rem;display:flex;justify-content:flex-end;gap:.65rem;border-top:1px solid rgba(255,255,255,.08)}

  @media (max-width:576px){
    .pr-toolbar{display:flex;flex-direction:column;align-items:stretch;gap:10px}
    .pr-pill-group{display:flex;flex-wrap:wrap;gap:8px;overflow:visible;white-space:normal}
    .pr-search{width:100%}
    .datatable-custom{overflow-x:auto}
    .card-table th,.card-table td{padding:.65rem .75rem!important;font-size:12px}
  }
</style>
<?= $this->end() ?>

<?php
$rows = is_array($data ?? null) ? $data : [];
global $db;
$__sellerIconCache = [];
function __spr_icon_url($path){
  if (!$path) return '';
  $p = (string)$path;
  if (preg_match('~^https?://~i', $p) || str_starts_with($p, '//')) return $p;
  if (defined('BASE_URL')) return rtrim(BASE_URL,'/') . '/' . ltrim($p,'/');
  if (defined('SITE_URL')) return rtrim(SITE_URL,'/') . '/' . ltrim($p,'/');
  return '/' . ltrim($p,'/');
}
?>

<div class="card">
  <div class="card-header">
    <div class="row justify-content-between align-items-center flex-grow-1">
      <div class="col-12 col-md">
        <h5 class="card-header-title">Seller Payout Requests</h5>
      </div>
      <div class="col-12 col-md-auto mt-3 mt-md-0">
        <div class="pr-toolbar">
          <div class="pr-filters">

            <div class="pr-filter-block">
              <div class="pr-filter-label"><i class="fa-solid fa-circle-info"></i> Status</div>
              <div class="pr-pill-group" role="tablist">
                <button type="button" class="pr-pill js-status-pill" data-status="">Any</button>
                <button type="button" class="pr-pill js-status-pill is-active" data-status="Pending">Pending</button>
                <button type="button" class="pr-pill js-status-pill" data-status="Approved">Approved</button>
                <button type="button" class="pr-pill js-status-pill" data-status="Rejected">Rejected</button>
              </div>
            </div>

            <div class="pr-filter-block">
              <div class="pr-filter-label"><i class="fa-solid fa-credit-card"></i> Method</div>
              <div class="pr-pill-group is-emph" role="tablist">
                <button type="button" class="pr-pill js-method-pill is-active" data-method="">All</button>
                <button type="button" class="pr-pill js-method-pill" data-method="Bank Transfer">Bank</button>
                <button type="button" class="pr-pill js-method-pill" data-method="Crypto">Crypto</button>
              </div>
            </div>

          </div>

          <div class="input-group input-group-merge input-group-flush pr-search">
            <div class="input-group-prepend input-group-text"><i class="fa-solid fa-magnifying-glass"></i></div>
            <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search payout requests">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive datatable-custom">
    <table
      class="js-datatable table table-borderless table-thead-bordered table-align-middle card-table"
      data-hs-datatables-options='{
        "order": [[5, "desc"]],
        "info": {"totalQty": "#datatableEntriesInfoTotalQty"},
        "entries": "#datatableEntries",
        "search": "#datatableWithSearchInput",
        "isResponsive": false,
        "pageLength": 25,
        "isShowPaging": true,
        "pagination": "datatableWithSearchPagination"
      }'
      id="invoices_table">

      <thead class="thead-light">
        <tr>
          <th class="pr-col-id">ID</th>
          <th style="width:280px;">Seller</th>
          <th style="width:160px;">Method &amp; Details</th>
          <th style="width:130px;">Status</th>
          <th class="text-end" style="width:170px;">Amounts</th>
          <th style="width:155px;">Created</th>
          <th class="text-end" style="width:210px;">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" class="text-muted py-4">No payout requests found.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r):
            $id         = (int)($r['id'] ?? 0);
            $sellerId   = (int)($r['seller_id'] ?? 0);
            $sellerName = (string)($r['seller_username'] ?? $r['username'] ?? 'Seller');
            $sellerEmail = (string)($r['seller_email'] ?? '');

            // Avatar
            $sellerAvatar = (string)($r['seller_icon'] ?? $r['icon'] ?? '');
            if ($sellerAvatar === '' && $sellerId > 0 && isset($db)) {
              if (!array_key_exists($sellerId, $__sellerIconCache)) {
                $rowIcon = $db->row("SELECT icon FROM sellers WHERE id=? LIMIT 1", $sellerId);
                $__sellerIconCache[$sellerId] = (string)($rowIcon['icon'] ?? '');
              }
              $sellerAvatar = $__sellerIconCache[$sellerId];
            }
            $sellerAvatar = __spr_icon_url($sellerAvatar);

            // Method
            $methodRaw   = strtolower(trim((string)($r['method'] ?? '')));
            $methodLabel = str_contains($methodRaw, 'crypto') ? 'Crypto' : 'Bank Transfer';
            $methodDetails = (string)($r['details'] ?? '');

            // Status
            $statusRaw   = strtolower((string)($r['status'] ?? 'pending'));
            $statusLabel = ucfirst($statusRaw);

            // Amounts (cents)
            $grossCents = (int)($r['amount_cents'] ?? 0);
            if (!empty($r['fee_cents']) || !empty($r['net_cents'])) {
              $feePct   = (float)($r['fee_percent'] ?? 0);
              $feeCents = (int)($r['fee_cents'] ?? 0);
              $netCents = (int)($r['net_cents']  ?? 0);
            } else {
              // Legacy rows without fee columns
              $feePct   = str_contains($methodRaw, 'crypto') ? 5.0 : 3.0;
              $feeCents = (int)round($grossCents * ($feePct / 100));
              $netCents = max(0, $grossCents - $feeCents);
            }

            $gross = $grossCents / 100;
            $fee   = $feeCents  / 100;
            $net   = $netCents  / 100;

            // Date
            $created       = (string)($r['created_at'] ?? '-');
            $createdTs     = ($created && $created !== '-') ? (int)strtotime($created) : 0;
            $createdDisplay = $createdTs ? date('d-m-y · H:i', $createdTs) : '-';
          ?>
          <tr>
            <td class="pr-col-id">
              <div class="pr-idwrap"><span class="pr-id">#<?= $id ?></span></div>
            </td>

            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm avatar-circle me-3">
                  <?php if ($sellerAvatar): ?>
                    <img class="avatar-img" src="<?= htmlspecialchars($sellerAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
                  <?php else: ?>
                    <span class="avatar-initials"><?= htmlspecialchars(strtoupper(substr($sellerName, 0, 2)), ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                  <a class="d-block h5 mb-0" href="<?= ADMN_URL ?>/seller/<?= $sellerId ?>">
                    <?= htmlspecialchars($sellerName, ENT_QUOTES, 'UTF-8') ?>
                  </a>
                  <?php if ($sellerEmail): ?>
                    <div class="small text-muted"><?= htmlspecialchars($sellerEmail, ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <td>
              <a href="javascript:void(0)"
                 class="js-open-method text-decoration-none"
                 data-method-label="<?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?>"
                 data-method-details="<?= htmlspecialchars($methodDetails, ENT_QUOTES, 'UTF-8') ?>"
                 data-seller="<?= htmlspecialchars($sellerName, ENT_QUOTES, 'UTF-8') ?>">
                <span class="fw-semibold" style="color:rgba(255,255,255,.88);">
                  <?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
              </a>
            </td>

            <td>
              <span class="pr-status is-<?= htmlspecialchars($statusRaw, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
              </span>
            </td>

            <td class="text-end">
              <div class="pr-amounts">
                <div class="fw-semibold received pr-money"><?= number_format($net, 2, ',', '.') ?>€</div>
                <div class="pr-amt-chips">
                  <span class="pr-chip is-req"><span class="k">Req</span> <span class="pr-money"><?= number_format($gross, 2, ',', '.') ?>€</span></span>
                  <span class="pr-chip is-fee"><span class="k">Fee</span> <span class="pr-money"><?= number_format($fee, 2, ',', '.') ?>€</span></span>
                </div>
              </div>
            </td>

            <td class="text-muted" data-order="<?= (int)$createdTs ?>"><?= htmlspecialchars($createdDisplay, ENT_QUOTES, 'UTF-8') ?></td>

            <td class="text-end">
              <?php if ($statusRaw === 'pending'): ?>
                <form class="ajax-form d-inline" method="post" action="<?= AJAX_URL ?>" style="margin-right:.35rem;">
                  <input type="hidden" name="action" value="admin_process_seller_payout">
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <input type="hidden" name="status" value="APPROVED">
                  <button type="submit" class="btn btn-sm btn-approve btn-compact">
                    <i class="fa-solid fa-check me-1"></i> Approve
                  </button>
                </form>
                <button type="button"
                        class="btn btn-sm btn-outline-danger btn-compact js-open-reject"
                        data-id="<?= $id ?>"
                        data-seller="<?= htmlspecialchars($sellerName, ENT_QUOTES, 'UTF-8') ?>"
                        data-method="<?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?>"
                        data-amount="<?= number_format($gross, 2) ?> EUR">
                  <i class="fa-solid fa-xmark me-1"></i> Reject
                </button>
              <?php else: ?>
                <span class="badge badge-soft" style="padding:.38rem .65rem;border-radius:999px;">
                  <i class="fa-solid fa-lock me-1"></i> Locked
                </span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-footer">
    <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
      <div class="col-sm mb-2 mb-sm-0">
        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
          <span class="me-2">Showing:</span>
          <div class="tom-select-custom">
            <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off"
                    data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
              <option value="10">10</option>
              <option value="25" selected>25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <span class="text-secondary me-2">of</span>
          <span id="datatableEntriesInfoTotalQty"></span>
        </div>
      </div>
      <div class="col-sm-auto">
        <div class="d-flex justify-content-center justify-content-sm-end">
          <nav id="datatableWithSearchPagination"></nav>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Method details modal -->
<div class="pr-modal" id="prMethodModal" aria-hidden="true">
  <div class="pr-modal__backdrop" data-pr-close></div>
  <div class="pr-modal__panel" role="dialog" aria-modal="true" aria-labelledby="prMethodTitle">
    <div class="pr-modal__header">
      <div>
        <h3 class="pr-modal__title" id="prMethodTitle">Payout Method Details</h3>
        <div class="small text-muted" id="prMethodSub" style="margin-top:.15rem;">—</div>
      </div>
      <button type="button" class="pr-modal__close" data-pr-close><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="pr-modal__body">
      <div class="mb-3">
        <div class="small text-muted">Seller</div>
        <div class="fw-semibold" id="methodSeller">—</div>
      </div>
      <div class="mb-3">
        <div class="small text-muted">Method</div>
        <div class="fw-semibold" id="methodName">—</div>
      </div>
      <div class="border rounded-3" style="border-color:rgba(255,255,255,.08)!important;background:rgba(255,255,255,.05);">
        <div class="p-3" id="methodDetails">—</div>
      </div>
    </div>
    <div class="pr-modal__footer">
      <button type="button" class="btn btn-white" data-pr-close>Close</button>
    </div>
  </div>
</div>

<!-- Reject modal -->
<div class="pr-modal" id="prRejectModal" aria-hidden="true">
  <div class="pr-modal__backdrop" data-pr-close></div>
  <div class="pr-modal__panel" role="dialog" aria-modal="true" aria-labelledby="prRejectTitle">
    <form class="ajax-form" method="post" action="<?= AJAX_URL ?>">
      <div class="pr-modal__header">
        <div>
          <h3 class="pr-modal__title" id="prRejectTitle">Reject payout request</h3>
          <div class="small text-muted" id="prRejectSub" style="margin-top:.15rem;">—</div>
        </div>
        <button type="button" class="pr-modal__close" data-pr-close><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="pr-modal__body">
        <input type="hidden" name="action" value="admin_process_seller_payout">
        <input type="hidden" name="status" value="REJECTED">
        <input type="hidden" name="id" id="rejectId" value="">
        <div class="mb-3">
          <div class="small text-muted">Seller</div>
          <div class="fw-semibold" id="rejectSeller">—</div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <div class="small text-muted">Method</div>
            <div class="fw-semibold" id="rejectMethod">—</div>
          </div>
          <div class="col-6">
            <div class="small text-muted">Amount</div>
            <div class="fw-semibold" id="rejectAmount">—</div>
          </div>
        </div>
        <div class="mb-0">
          <label class="form-label">Rejection note (optional)</label>
          <textarea class="form-control" name="admin_note" rows="3" placeholder="E.g. Missing payout details, suspicious activity…"></textarea>
        </div>
      </div>
      <div class="pr-modal__footer">
        <button type="button" class="btn btn-white" data-pr-close>Cancel</button>
        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban me-1"></i> Reject</button>
      </div>
    </form>
  </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
$(function () {
  HSCore.components.HSTomSelect.init('.js-select');

  HSCore.components.HSDatatables.init($('#invoices_table'), {
    language: {
      zeroRecords: `<div class="text-center p-4">
        <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="default">
        <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="dark">
        <p class="mb-0">No data to show</p>
      </div>`
    }
  });

  let dt = null;
  function getDt() { try { return $('#invoices_table').DataTable(); } catch(e){ return null; } }

  function setActivePill(sel, el) { $(sel).removeClass('is-active'); $(el).addClass('is-active'); }

  function applyFilters() {
    const status = $('.js-status-pill.is-active').data('status') || '';
    const method = $('.js-method-pill.is-active').data('method') || '';
    if (dt) {
      dt.column(3).search(status).draw(); // Status col = 3
      dt.column(2).search(method).draw(); // Method col = 2
    }
  }

  (function bootstrap(){
    dt = getDt();
    if (dt) { requestAnimationFrame(applyFilters); return; }
    let tries = 0;
    const t = setInterval(function(){
      dt = getDt();
      if (dt) { clearInterval(t); applyFilters(); }
      else if (++tries >= 20) clearInterval(t);
    }, 150);
  })();

  $(document).on('click', '.js-status-pill', function(){ setActivePill('.js-status-pill', this); applyFilters(); });
  $(document).on('click', '.js-method-pill', function(){ setActivePill('.js-method-pill', this); applyFilters(); });

  // Modals
  const methodModalEl = document.getElementById('prMethodModal');
  const rejectModalEl = document.getElementById('prRejectModal');

  function openModal(el)  { if(!el) return; el.classList.add('is-open'); document.body.style.overflow='hidden'; el.setAttribute('aria-hidden','false'); }
  function closeModal(el) { if(!el) return; el.classList.remove('is-open'); document.body.style.overflow=''; el.setAttribute('aria-hidden','true'); }

  $(document).on('click', '[data-pr-close]', function(){ closeModal(methodModalEl); closeModal(rejectModalEl); });
  $(document).on('keydown', function(e){ if(e.key==='Escape'){ closeModal(methodModalEl); closeModal(rejectModalEl); } });

  function safeText(s) {
    if (s === null || typeof s === 'undefined') return '';
    if (typeof s === 'object') { try { return JSON.stringify(s); } catch(e){ return String(s); } }
    return String(s);
  }

  function formatDetails(raw) {
    const txt = safeText(raw).trim();
    if (!txt) return '<span class="text-muted">No payout details saved.</span>';
    try {
      const obj = JSON.parse(txt);
      if (obj && typeof obj === 'object') {
        let html = '<div class="d-grid" style="gap:.55rem;">';
        Object.keys(obj).forEach(k => {
          const v = (obj[k] === null || typeof obj[k] === 'undefined') ? '' : String(obj[k]);
          html += `<div><div class="small text-muted">${$('<div>').text(k).html()}</div><div class="fw-semibold" style="word-break:break-word;">${$('<div>').text(v).html()}</div></div>`;
        });
        html += '</div>';
        return html;
      }
    } catch(e) {}
    return '<pre class="mb-0" style="white-space:pre-wrap;color:rgba(255,255,255,.85);">' + $('<div>').text(txt).html() + '</pre>';
  }

  $(document).on('click', '.js-open-method', function(e){
    e.preventDefault();
    const seller  = $(this).data('seller') || '—';
    const label   = $(this).data('method-label') || '—';
    const details = $(this).attr('data-method-details') || '';
    $('#prMethodSub').text(seller);
    $('#methodSeller').text(seller);
    $('#methodName').text(label);
    $('#methodDetails').html(formatDetails(details));
    openModal(methodModalEl);
  });

  $(document).on('click', '.js-open-reject', function(){
    $('#rejectId').val($(this).data('id') || '');
    $('#rejectSeller').text($(this).data('seller') || '—');
    $('#rejectMethod').text($(this).data('method') || '—');
    $('#rejectAmount').text($(this).data('amount') || '—');
    $('#prRejectSub').text($(this).data('seller') || '—');
    openModal(rejectModalEl);
  });

  // Prevent double submits
  $(document).on('submit', '.ajax-form', function(){
    const $btn = $(this).find('button[type="submit"]').first();
    if (!$btn.length || $btn.data('loading')) return false;
    $btn.data('loading', true).prop('disabled', true);
    const label = $btn.hasClass('btn-approve') ? 'Approving' : ($btn.hasClass('btn-danger') ? 'Rejecting' : 'Processing');
    $btn.html('<i class="fa-solid fa-circle-notch fa-spin me-1"></i> ' + label);
    return true;
  });
});
</script>
<?= $this->end() ?>
