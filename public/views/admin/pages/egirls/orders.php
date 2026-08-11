<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'E-Girl Bookings - Admin Area | LoLBoost.gg', 'h1' => 'E-Girl Bookings']]) ?>

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
.pr-pill-group { display:flex; gap:.45rem; padding:.32rem; border-radius:999px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.10); align-items:center; flex-wrap:nowrap; }
.pr-pill { appearance:none; border:0; background:transparent; color:rgba(255,255,255,.86); padding:.30rem .70rem; border-radius:999px; font-size:.80rem; line-height:1; cursor:pointer; transition:background .15s; white-space:nowrap; }
.pr-pill:hover { background:rgba(255,255,255,.06); }
.pr-pill.is-active { background:rgba(124,92,255,.26); box-shadow:inset 0 0 0 1px rgba(124,92,255,.55); }
.pr-status { display:inline-flex; align-items:center; gap:.45rem; padding:.34rem .70rem; border-radius:999px; font-weight:650; font-size:.72rem; letter-spacing:.03em; text-transform:uppercase; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); white-space:nowrap; }
.pr-status::before { content:""; width:7px; height:7px; border-radius:999px; background:rgba(255,255,255,.45); }
.pr-status.is-paid        { border-color:rgba(92,74,227,.28);  background:rgba(92,74,227,.12);  color:rgba(180,170,255,.95); }
.pr-status.is-paid::before        { background:rgba(92,74,227,.95); }
.pr-status.is-in_progress { border-color:rgba(9,165,190,.28);  background:rgba(9,165,190,.12);  color:rgba(140,230,255,.95); }
.pr-status.is-in_progress::before { background:rgba(9,165,190,.95); box-shadow:0 0 0 3px rgba(9,165,190,.18); }
.pr-status.is-completed   { border-color:rgba(0,200,140,.28);  background:rgba(0,200,140,.12);  color:rgba(160,255,220,.95); }
.pr-status.is-completed::before   { background:rgba(0,200,140,.95); }
.pr-status.is-unpaid      { border-color:rgba(237,76,120,.28); background:rgba(237,76,120,.12); color:rgba(255,180,200,.95); }
.pr-status.is-unpaid::before      { background:rgba(237,76,120,.95); }
.pr-status.is-cancelled   { border-color:rgba(245,202,153,.28);background:rgba(245,202,153,.12);color:rgba(255,225,180,.95); }
.pr-status.is-cancelled::before   { background:rgba(245,202,153,.95); }
.pr-status.is-refunded    { border-color:rgba(145,152,158,.28);background:rgba(145,152,158,.12);color:rgba(200,205,210,.95); }
.pr-status.is-refunded::before    { background:rgba(145,152,158,.95); }
.pr-money { font-variant-numeric:tabular-nums; font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace; }
@media(max-width:992px) { .pr-toolbar{flex-wrap:wrap} .pr-filters{flex-wrap:wrap} .pr-search{width:100%;min-width:0} }
</style>
<?= $this->end() ?>

<?php $orders = $orders ?? []; ?>

<div class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md"><h5 class="card-header-title">E-Girl Bookings</h5></div>
            <div class="col-12 col-md-auto mt-2 mt-md-0">
                <div class="pr-toolbar">
                    <div class="pr-filters">
                        <div class="pr-filter-block">
                            <div class="pr-filter-label"><i class="fa-solid fa-circle-info"></i> Status</div>
                            <div class="pr-pill-group">
                                <button type="button" class="pr-pill js-status-pill is-active" data-status="">Any</button>
                                <button type="button" class="pr-pill js-status-pill" data-status="IN_PROGRESS">In Progress</button>
                                <button type="button" class="pr-pill js-status-pill" data-status="PAID">Paid</button>
                                <button type="button" class="pr-pill js-status-pill" data-status="COMPLETED">Completed</button>
                                <button type="button" class="pr-pill js-status-pill" data-status="UNPAID">Unpaid</button>
                            </div>
                        </div>
                    </div>
                    <div class="input-group input-group-merge input-group-flush pr-search">
                        <div class="input-group-prepend input-group-text"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <input id="ordersSearchInput" type="search" class="form-control" placeholder="Search bookings">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               id="egirl_orders_table"
               data-hs-datatables-options='{
                   "columnDefs": [{"targets": [8], "orderable": false}],
                   "order": [[7, "desc"]],
                   "info": {"totalQty": "#ordersInfoQty"},
                   "entries": "#ordersEntries",
                   "search": "#ordersSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "ordersPagination"
               }'>
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Client</th><th>E-Girl</th><th>Service</th>
                    <th class="text-end">Price</th><th>Voice</th><th>Status</th><th>Date</th><th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-5">No bookings yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o):
                        $sk = strtolower(str_replace([' ','-'], '_', $o['status']));
                        $hasVoice = !empty($o['voice_chat']);
                    ?>
                    <tr data-status="<?= htmlspecialchars($o['status']) ?>">
                        <td class="fw-500 text-muted">#<?= $o['id'] ?></td>
                        <td class="fw-500"><?= htmlspecialchars($o['client_username'] ?? '—') ?></td>
                        <td class="fw-500"><?= htmlspecialchars($o['egirl_username'] ?? '—') ?></td>
                        <td class="text-muted"><?= htmlspecialchars($o['service_title'] ?? $o['service_type'] ?? '—') ?></td>
                        <td class="text-end fw-500 pr-money">€<?= number_format(($o['price'] ?? 0)/100, 2) ?></td>
                        <td>
                            <?php if ($hasVoice): ?>
                                <span class="badge bg-soft-success text-success"><i class="fa-solid fa-microphone me-1"></i>Yes</span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td><span class="pr-status is-<?= $sk ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                        <td class="text-muted" data-order="<?= strtotime($o['created_at']) ?>"><?= date('d.m.Y', strtotime($o['created_at'])) ?></td>
                        <td class="text-end">
                            <a href="<?= ADMN_URL ?>/egirl/order/<?= $o['id'] ?>" class="btn btn-white btn-sm">
                                <i class="fa-duotone fa-eye me-1 fs-6"></i> View
                            </a>
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
                        <select id="ordersEntries" class="js-select form-select form-select-borderless w-auto"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="10" selected>10</option><option value="25">25</option><option value="50">50</option>
                        </select>
                    </div>
                    <span class="text-secondary">of</span><span id="ordersInfoQty"></span>
                </div>
            </div>
            <div class="col-sm-auto"><nav id="ordersPagination"></nav></div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(function () {
    HSCore.components.HSDatatables.init($('#egirl_orders_table'));
    let dt = null;
    try { dt = $('#egirl_orders_table').DataTable(); } catch(e) {}

    function applyFilters() {
        if (!dt) return;
        const status = $('.js-status-pill.is-active').data('status') || '';
        dt.column(6).search(status).draw();
    }
    $(document).on('click', '.js-status-pill', function () {
        $('.js-status-pill').removeClass('is-active');
        $(this).addClass('is-active');
        applyFilters();
    });
    setTimeout(function () { try { dt = $('#egirl_orders_table').DataTable(); } catch(e){} applyFilters(); }, 300);
});
</script>
<?= $this->end() ?>
