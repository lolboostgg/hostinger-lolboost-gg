<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'E-Girl Payout Requests - Admin Area | LoLBoost.gg', 'h1' => 'E-Girl Payout Requests']]) ?>

<style>
/* Wider egirl admin pages, matched to booster admin layout. */
@media (min-width: 992px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container-fluid {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (min-width: 1400px) {
  body .container,
  body .container-lg,
  body .container-xl,
  body .container-xxl {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (max-width: 991.98px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container,
  body .container-fluid {
    max-width: 100% !important;
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>


<?= $this->start('styles') ?>
<style>
:root { --pr-accent:rgba(124,92,255,1); }
.pr-toolbar { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:nowrap; }
.pr-filters { display:flex; align-items:center; gap:14px; flex-wrap:nowrap; }
.pr-filter-block { display:flex; flex-direction:row; align-items:center; gap:.55rem; min-width:max-content; }
.pr-filter-label { font-size:.65rem; letter-spacing:.10em; text-transform:uppercase; color:rgba(255,255,255,.55); display:flex; align-items:center; gap:.4rem; }
.pr-search { min-width:240px; }
.pr-pill-group { display:flex; gap:.45rem; padding:.32rem; border-radius:999px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.10); align-items:center; }
.pr-pill-group.is-emph { background:rgba(124,92,255,.10); border-color:rgba(124,92,255,.20); }
.pr-pill { appearance:none; border:0; background:transparent; color:rgba(255,255,255,.86); padding:.30rem .70rem; border-radius:999px; font-size:.80rem; line-height:1; cursor:pointer; transition:background .15s; white-space:nowrap; }
.pr-pill:hover { background:rgba(255,255,255,.06); }
.pr-pill.is-active { background:rgba(124,92,255,.26); box-shadow:inset 0 0 0 1px rgba(124,92,255,.55); }
.pr-status { display:inline-flex; align-items:center; gap:.45rem; padding:.34rem .70rem; border-radius:999px; font-weight:650; font-size:.72rem; letter-spacing:.03em; text-transform:uppercase; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); white-space:nowrap; }
.pr-status::before { content:""; width:7px; height:7px; border-radius:999px; background:rgba(255,255,255,.45); }
.pr-status.is-pending  { border-color:rgba(255,180,0,.28);   background:rgba(255,180,0,.12);   color:rgba(255,220,140,.95); }
.pr-status.is-pending::before  { background:rgba(255,180,0,.95);  box-shadow:0 0 0 3px rgba(255,180,0,.18); }
.pr-status.is-approved { border-color:rgba(0,200,140,.28);   background:rgba(0,200,140,.12);   color:rgba(160,255,220,.95); }
.pr-status.is-approved::before { background:rgba(0,200,140,.95); box-shadow:0 0 0 3px rgba(0,200,140,.18); }
.pr-status.is-paid     { border-color:rgba(0,200,140,.28);   background:rgba(0,200,140,.12);   color:rgba(160,255,220,.95); }
.pr-status.is-paid::before     { background:rgba(0,200,140,.95); }
.pr-status.is-completed { border-color:rgba(0,200,140,.28); background:rgba(0,200,140,.12); color:rgba(160,255,220,.95); }
.pr-status.is-completed::before { background:rgba(0,200,140,.95); }
.pr-status.is-rejected { border-color:rgba(255,70,120,.28);  background:rgba(255,70,120,.12);  color:rgba(255,170,190,.95); }
.pr-status.is-rejected::before { background:rgba(255,70,120,.95); box-shadow:0 0 0 3px rgba(255,70,120,.18); }
.badge-soft { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); color:rgba(255,255,255,.70); }
.btn-complete { background:rgba(0,200,140,.18)!important; border:1px solid rgba(0,200,140,.28)!important; color:rgba(200,255,236,.95)!important; }
.btn-complete:hover { background:rgba(0,200,140,.24)!important; }
.pr-money { font-variant-numeric:tabular-nums; font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace; }
.pr-modal { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:1rem; }
.pr-modal.is-open { display:flex; }
.pr-modal__backdrop { position:absolute; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(4px); }
.pr-modal__panel { position:relative; width:min(520px,100%); border-radius:18px; background:rgba(38,40,44,.96); border:1px solid rgba(255,255,255,.10); box-shadow:0 20px 60px rgba(0,0,0,.45); overflow:hidden; }
.pr-modal__header { padding:1rem 1.25rem .75rem; display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; border-bottom:1px solid rgba(255,255,255,.08); }
.pr-modal__title { margin:0; font-size:1.05rem; color:rgba(255,255,255,.92); }
.pr-modal__close { width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); color:rgba(255,255,255,.86); cursor:pointer; }
.pr-modal__close:hover { background:rgba(255,255,255,.06); }
.pr-modal__body { padding:1rem 1.25rem; color:rgba(255,255,255,.70); }
.pr-modal__footer { padding:.9rem 1.25rem 1.05rem; display:flex; justify-content:flex-end; gap:.65rem; border-top:1px solid rgba(255,255,255,.08); }
@media(max-width:992px) { .pr-toolbar{flex-wrap:wrap} .pr-search{width:100%;min-width:0} }
</style>
<?= $this->end() ?>

<?php
$requests = $requests ?? [];
$pending  = array_filter($requests, fn($r) => $r['status'] === 'PENDING');
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>

<?php if (!empty($pending)): ?>
<div class="alert alert-soft-warning d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-clock"></i>
    <strong><?= count($pending) ?></strong> pending payout request<?= count($pending) > 1 ? 's' : '' ?> awaiting review.
</div>
<?php else: ?>
<div class="alert alert-soft-success d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-circle-check"></i> No pending payout requests — all caught up!
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md"><h5 class="card-header-title">E-Girl Payout Requests</h5></div>
            <div class="col-12 col-md-auto mt-2 mt-md-0">
                <div class="pr-toolbar">
                    <div class="pr-filters">
                        <div class="pr-filter-block">
                            <div class="pr-filter-label"><i class="fa-solid fa-circle-info"></i> Status</div>
                            <div class="pr-pill-group">
                                <button type="button" class="pr-pill js-status-pill" data-status="">Any</button>
                                <button type="button" class="pr-pill js-status-pill is-active" data-status="Pending">Pending</button>
                                <button type="button" class="pr-pill js-status-pill" data-status="Approved">Approved</button>
                                <button type="button" class="pr-pill js-status-pill" data-status="Completed">Completed</button>
                                <button type="button" class="pr-pill js-status-pill" data-status="Rejected">Rejected</button>
                            </div>
                        </div>
                        <div class="pr-filter-block">
                            <div class="pr-filter-label"><i class="fa-solid fa-credit-card"></i> Method</div>
                            <div class="pr-pill-group is-emph">
                                <button type="button" class="pr-pill js-method-pill is-active" data-method="">All</button>
                                <button type="button" class="pr-pill js-method-pill" data-method="bank_transfer">Bank</button>
                                <button type="button" class="pr-pill js-method-pill" data-method="crypto">Crypto</button>
                            </div>
                        </div>
                    </div>
                    <div class="input-group input-group-merge input-group-flush pr-search">
                        <div class="input-group-prepend input-group-text"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <input id="payoutsSearchInput" type="search" class="form-control" placeholder="Search requests">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-align-middle card-table"
               id="egirl_payouts_table"
               data-hs-datatables-options='{
                   "order": [[5,"desc"]],
                   "info": {"totalQty":"#payoutsInfoQty"},
                   "entries": "#payoutsEntries",
                   "search": "#payoutsSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "payoutsPagination"
               }'>
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>E-Girl</th><th>Method</th>
                    <th class="text-end">Amount</th><th>Status</th><th>Date</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">No payout requests yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $r):
                        $statusRaw   = strtolower(trim((string)($r['status'] ?? 'pending')));
                        $statusLabel = ucfirst($statusRaw);
                        $method      = strtolower(trim((string)($r['method'] ?? '')));
                        $methodLabel = str_contains($method, 'crypto') ? 'Crypto' : 'Bank Transfer';
                        $amountCents = (int)($r['amount_cents'] ?? 0);
                        $createdTs   = !empty($r['created_at']) ? strtotime($r['created_at']) : 0;
                    ?>
                    <tr data-pay-status="<?= $h($statusLabel) ?>" data-pay-method="<?= $h($method) ?>">
                        <td class="fw-500 text-muted">#<?= (int)$r['id'] ?></td>
                        <td class="fw-500"><?= $h($r['egirl_username'] ?? '—') ?></td>
                        <td>
                            <a href="javascript:void(0)" class="js-open-method text-decoration-none fw-semibold"
                               style="color:rgba(255,255,255,.88)"
                               data-method-label="<?= $h($methodLabel) ?>"
                               data-method-details="<?= $h($r['details'] ?? '{}') ?>"
                               data-seller="<?= $h($r['egirl_username'] ?? '') ?>">
                                <?= $h($methodLabel) ?>
                            </a>
                        </td>
                        <td class="text-end fw-semibold pr-money" data-order="<?= $amountCents ?>">
                            €<?= number_format($amountCents / 100, 2) ?>
                        </td>
                        <td data-order="<?= $h($statusRaw) ?>">
                            <span class="pr-status is-<?= $h($statusRaw) ?>"><?= $h($statusLabel) ?></span>
                        </td>
                        <td class="text-muted" data-order="<?= (int)$createdTs ?>">
                            <?= $createdTs ? date('d.m.Y H:i', $createdTs) : '—' ?>
                        </td>
                        <td class="text-end">
                            <?php if ($statusRaw === 'pending'): ?>
                                <form class="ajax-form d-inline me-1" method="POST" action="<?= AJAX_URL ?>">
                                    <input type="hidden" name="action" value="admin_egirl_payout_update">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="status" value="COMPLETED">
                                    <button type="submit" class="btn btn-sm btn-complete">
                                        <i class="fa-solid fa-check me-1"></i>Complete
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger js-open-reject"
                                        data-id="<?= (int)$r['id'] ?>"
                                        data-seller="<?= $h($r['egirl_username'] ?? '') ?>"
                                        data-amount="€<?= number_format($amountCents / 100, 2) ?>">
                                    <i class="fa-solid fa-xmark me-1"></i>Reject
                                </button>
                            <?php elseif ($statusRaw === 'approved'): ?>
                                <form class="ajax-form d-inline" method="POST" action="<?= AJAX_URL ?>">
                                    <input type="hidden" name="action" value="admin_egirl_payout_update">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="status" value="PAID">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-check-double me-1"></i>Mark Paid
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-soft px-2"><i class="fa-solid fa-lock me-1"></i>Locked</span>
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
                <div class="d-flex align-items-center gap-2">
                    <span>Showing:</span>
                    <div class="tom-select-custom">
                        <select id="payoutsEntries" class="js-select form-select form-select-borderless w-auto"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="10" selected>10</option><option value="25">25</option><option value="50">50</option>
                        </select>
                    </div>
                    <span class="text-secondary">of</span><span id="payoutsInfoQty"></span>
                </div>
            </div>
            <div class="col-sm-auto"><nav id="payoutsPagination"></nav></div>
        </div>
    </div>
</div>

<!-- Method modal -->
<div class="pr-modal" id="prMethodModal">
    <div class="pr-modal__backdrop" data-pr-close></div>
    <div class="pr-modal__panel">
        <div class="pr-modal__header">
            <div><h3 class="pr-modal__title">Payout Method Details</h3><div class="small text-muted" id="methodSeller">—</div></div>
            <button type="button" class="pr-modal__close" data-pr-close><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="pr-modal__body">
            <div class="mb-3"><div class="small text-muted">Method</div><div class="fw-semibold" id="methodName">—</div></div>
            <div class="border rounded-3" style="border-color:rgba(255,255,255,.08)!important;background:rgba(255,255,255,.05)"><div class="p-3" id="methodDetails">—</div></div>
        </div>
        <div class="pr-modal__footer"><button type="button" class="btn btn-white" data-pr-close>Close</button></div>
    </div>
</div>

<!-- Reject modal -->
<div class="pr-modal" id="prRejectModal">
    <div class="pr-modal__backdrop" data-pr-close></div>
    <div class="pr-modal__panel">
        <form class="ajax-form" method="POST" action="<?= AJAX_URL ?>">
            <div class="pr-modal__header">
                <h3 class="pr-modal__title">Reject Payout Request</h3>
                <button type="button" class="pr-modal__close" data-pr-close><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="pr-modal__body">
                <input type="hidden" name="action" value="admin_egirl_payout_update">
                <input type="hidden" name="status" value="REJECTED">
                <input type="hidden" name="request_id" id="rejectId" value="">
                <div class="row g-2 mb-3">
                    <div class="col-6"><div class="small text-muted">E-Girl</div><div class="fw-semibold" id="rejectSeller">—</div></div>
                    <div class="col-6"><div class="small text-muted">Amount</div><div class="fw-semibold" id="rejectAmount">—</div></div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Rejection note (optional)</label>
                    <textarea class="form-control" name="note" rows="3" placeholder="Reason for rejection…"></textarea>
                </div>
            </div>
            <div class="pr-modal__footer">
                <button type="button" class="btn btn-white" data-pr-close>Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban me-1"></i>Reject</button>
            </div>
        </form>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(function () {
    HSCore.components.HSDatatables.init($('#egirl_payouts_table'));
    let dt = null;
    function getDt() { try { return $('#egirl_payouts_table').DataTable(); } catch(e) { return null; } }

    function applyFilters() {
        dt = dt || getDt(); if (!dt) return;
        const status = $('.js-status-pill.is-active').data('status') || '';
        const method = $('.js-method-pill.is-active').data('method') || '';
        dt.column(4).search(status).draw();
        dt.column(2).search(method).draw();
    }
    setTimeout(function(){ dt = getDt(); applyFilters(); }, 300);
    $(document).on('click', '.js-status-pill', function(){ $('.js-status-pill').removeClass('is-active'); $(this).addClass('is-active'); applyFilters(); });
    $(document).on('click', '.js-method-pill', function(){ $('.js-method-pill').removeClass('is-active'); $(this).addClass('is-active'); applyFilters(); });

    const methodModal = document.getElementById('prMethodModal');
    const rejectModal = document.getElementById('prRejectModal');
    function openModal(el) { if(el){ el.classList.add('is-open'); document.body.style.overflow='hidden'; } }
    function closeModal(el) { if(el){ el.classList.remove('is-open'); document.body.style.overflow=''; } }
    $(document).on('click','[data-pr-close]', function(){ closeModal(methodModal); closeModal(rejectModal); });
    $(document).on('keydown', function(e){ if(e.key==='Escape'){ closeModal(methodModal); closeModal(rejectModal); } });

    function formatDetails(raw) {
        if (!raw) return '<span class="text-muted">No details.</span>';
        try {
            const obj = JSON.parse(raw);
            if (obj && typeof obj === 'object') {
                let html = '<div class="d-grid" style="gap:.55rem">';
                Object.keys(obj).forEach(k => { const v = obj[k] != null ? String(obj[k]) : ''; if (!v) return; html += `<div><div class="small text-muted">${$('<div>').text(k).html()}</div><div class="fw-semibold" style="word-break:break-word">${$('<div>').text(v).html()}</div></div>`; });
                return html + '</div>';
            }
        } catch(e){}
        return $('<div>').text(raw).html();
    }
    $(document).on('click', '.js-open-method', function(e){ e.preventDefault(); $('#methodSeller').text($(this).data('seller')||'—'); $('#methodName').text($(this).data('method-label')||'—'); $('#methodDetails').html(formatDetails($(this).attr('data-method-details')||'')); openModal(methodModal); });
    $(document).on('click', '.js-open-reject', function(){ $('#rejectId').val($(this).data('id')||''); $('#rejectSeller').text($(this).data('seller')||'—'); $('#rejectAmount').text($(this).data('amount')||'—'); openModal(rejectModal); });
});
</script>
<?= $this->end() ?>
