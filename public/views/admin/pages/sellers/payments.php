<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Payments - Admin Area | LoLBoost.gg', 'h1' => 'Seller Payments', 'description' => 'View seller payment history.']]) ?>
<?php $activeTab = 'payments'; include __DIR__ . '/_shared.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h4 class="card-header-title mb-0">Payment History</h4>
            <div class="input-group input-group-merge input-group-flush" style="max-width:220px">
                <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
                <input id="paymentsSearch" type="search" class="form-control" placeholder="Search payments">
            </div>
        </div>
    </div>

    <!-- Filter pills -->
    <div class="filter-bar">
        <label>Type</label>
        <button type="button" class="fpill active" data-pay-filter="">All</button>
        <button type="button" class="fpill" data-pay-filter="sale_payout">
            <span class="fpill-dot" style="background:#00c9a7"></span>Sale Payout
        </button>
        <button type="button" class="fpill" data-pay-filter="manual_adjustment">
            <span class="fpill-dot" style="background:#f5ca99"></span>Manual
        </button>
        <button type="button" class="fpill fpill-deduct" data-pay-filter="payout_deduction">
            <span class="fpill-dot" style="background:#ed4c78"></span>Deduction
        </button>
    </div>

    <?php if (!empty($payments)): ?>
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               id="payments_table"
               data-hs-datatables-options='{
                   "order": [[4, "desc"]],
                   "search": "#paymentsSearch",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "paymentsPagination",
                   "entries": "#paymentsEntries",
                   "info": {"totalQty": "#paymentsTotalQty"}
               }'>
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
                <?php foreach ($payments as $p):
                    $pType  = strtolower(trim((string)($p['type'] ?? '')));
                    $pCents = (int)($p['amount_cents'] ?? 0);
                    $isPos  = $pCents >= 0;
                ?>
                    <tr data-pay-type="<?= htmlspecialchars($pType, ENT_QUOTES) ?>">
                        <td>
                            <span class="badge bg-soft-secondary text-secondary">
                                <?= htmlspecialchars($pType ?: '—', ENT_QUOTES) ?>
                            </span>
                        </td>
                        <td class="text-end fw-500 <?= $isPos ? 'text-success' : 'text-danger' ?>"
                            data-order="<?= $pCents ?>">
                            <?= $isPos ? '+' : '' ?>€<?= number_format(abs($pCents) / 100, 2) ?>
                        </td>
                        <td class="text-end text-muted">
                            €<?= number_format((int)($p['balance_after'] ?? 0) / 100, 2) ?>
                        </td>
                        <td class="text-muted" style="max-width:320px;white-space:normal;">
                            <?= htmlspecialchars((string)($p['note'] ?? '—'), ENT_QUOTES) ?>
                        </td>
                        <td class="text-muted" data-order="<?= !empty($p['created_at']) ? strtotime($p['created_at']) : 0 ?>">
                            <?= !empty($p['created_at']) ? date('d.m.Y', strtotime($p['created_at'])) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex align-items-center gap-2">
                    <span>Showing:</span>
                    <div class="tom-select-custom">
                        <select id="paymentsEntries" class="js-select form-select form-select-borderless w-auto"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <span class="text-secondary">of</span>
                    <span id="paymentsTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto">
                <nav id="paymentsPagination"></nav>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="card-body text-muted">No payment history available.</div>
    <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    HSCore.components.HSDatatables.init($('#payments_table'), {
        language: {
            zeroRecords: '<div class="text-center p-4 text-muted">No payments match the current filter.</div>'
        }
    });

    var dt = $('#payments_table').DataTable();
    var activeFilter = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'payments_table') return true;
        if (!activeFilter) return true;
        return ($(dt.row(dataIndex).node()).data('pay-type') || '') === activeFilter;
    });

    $('[data-pay-filter]').on('click', function () {
        $('[data-pay-filter]').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('pay-filter');
        dt.draw();
    });
});
</script>
<?= $this->end() ?>
