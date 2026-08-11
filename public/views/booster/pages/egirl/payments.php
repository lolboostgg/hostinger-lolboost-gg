<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>
<?php $egSharedActiveTab = 'overview'; include __DIR__ . '/_shared.php'; ?>

<style>
:root {
    --eg-purple: #a855f7;
    --eg-pink: #ec4899;
}
/* ── Filter pills (seller-style, egirl colours) ── */
.filter-bar {
    display: flex; align-items: center; gap: .4rem;
    padding: .75rem 1.3125rem; border-bottom: 1px solid var(--eg-border);
    background: rgba(168,85,247,.03); flex-wrap: wrap;
}
.filter-bar label { font-size: .75rem; color: var(--eg-muted); margin: 0 .2rem 0 0; }
.fpill {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .75rem; border-radius: 50rem;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1px solid var(--eg-border); background: transparent;
    color: var(--eg-muted); transition: all .15s;
}
.fpill:hover { color: var(--eg-text); border-color: rgba(168,85,247,.35); }
.fpill.active            { color: #fff;    background: var(--eg-purple);  border-color: var(--eg-purple); }
.fpill.fpill-pos.active  { color: #fff;    background: #22c55e;           border-color: #22c55e; }
.fpill.fpill-neg.active  { color: #fff;    background: var(--eg-pink);    border-color: var(--eg-pink); }
.fpill.fpill-manual.active { color: #1e2022; background: #f5ca99;         border-color: #f5ca99; }
.fpill-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

/* ── Summary tiles ── */
.eg-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: .75rem; padding: 1rem 1.3125rem;
    border-bottom: 1px solid var(--eg-border);
}
.eg-summary-tile {
    background: var(--eg-bg); border: 1px solid var(--eg-border);
    border-radius: 12px; padding: .9rem 1rem;
}
.eg-summary-tile .val {
    font-size: 1.35rem; font-weight: 900; line-height: 1.15;
    background: linear-gradient(135deg, #fff 0%, #e879f9 60%, #f472b6 100%);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}
.eg-summary-tile .val.green { background: linear-gradient(135deg,#6ee7b7,#22c55e); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
.eg-summary-tile .val.pink  { background: linear-gradient(135deg,#f9a8d4,#ec4899); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
.eg-summary-tile .lbl { font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--eg-muted); margin-top: .3rem; }
.eg-summary-tile .st-icon { font-size: .95rem; color: rgba(168,85,247,.6); margin-bottom: .4rem; }

/* ── Table tweaks ── */
.eg-payments-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: var(--eg-muted); font-weight: 700; border-bottom: 1px solid var(--eg-border) !important; }
.eg-payments-table td { border-bottom: 1px solid rgba(168,85,247,.06) !important; vertical-align: middle; }
.eg-payments-table tbody tr:hover td { background: rgba(168,85,247,.04); }
.badge-type {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .65rem; border-radius: 20px; font-size: .75rem; font-weight: 700;
    background: rgba(168,85,247,.1); border: 1px solid rgba(168,85,247,.2); color: #c084fc;
}
.badge-type.sale   { background: rgba(34,197,94,.1);  border-color: rgba(34,197,94,.25);  color: #4ade80; }
.badge-type.deduct { background: rgba(236,72,153,.1); border-color: rgba(236,72,153,.25); color: #f472b6; }
.badge-type.manual { background: rgba(245,202,153,.1);border-color: rgba(245,202,153,.25);color: #f5ca99; }
</style>

<?php
$payments      = $payments ?? [];
$balance_cents = $balance_cents ?? (int)(BOOSTER_DATA['balance'] ?? 0);

$totalEarned = 0; $totalDeducted = 0;
foreach ($payments as $p) {
    $c = (int)($p['amount_cents'] ?? $p['amount'] ?? 0);
    if ($c > 0) $totalEarned   += $c;
    else        $totalDeducted += abs($c);
}
$countAll = count($payments);
?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h4 class="card-header-title mb-0">
            <i class="fa-duotone fa-wallet me-2" style="color:var(--eg-purple)"></i>Payment History
        </h4>
        <div class="input-group input-group-merge input-group-flush" style="max-width:220px">
            <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
            <input id="paymentsSearch" type="search" class="form-control" placeholder="Search…">
        </div>
    </div>

    <!-- Summary tiles -->
    <div class="eg-summary-grid">
        <div class="eg-summary-tile">
            <div class="st-icon"><i class="fa-duotone fa-coins"></i></div>
            <div class="val green">€<?= number_format($balance_cents / 100, 2) ?></div>
            <div class="lbl">Available for Payout</div>
        </div>
        <div class="eg-summary-tile">
            <div class="st-icon"><i class="fa-duotone fa-arrow-trend-up"></i></div>
            <div class="val">€<?= number_format($totalEarned / 100, 2) ?></div>
            <div class="lbl">Total Earned</div>
        </div>
        <div class="eg-summary-tile">
            <div class="st-icon"><i class="fa-duotone fa-arrow-trend-down"></i></div>
            <div class="val pink">€<?= number_format($totalDeducted / 100, 2) ?></div>
            <div class="lbl">Total Deducted</div>
        </div>
        <div class="eg-summary-tile">
            <div class="st-icon"><i class="fa-duotone fa-list"></i></div>
            <div class="val"><?= $countAll ?></div>
            <div class="lbl">Transactions</div>
        </div>
    </div>

    <!-- Filter pills -->
    <div class="filter-bar">
        <label>Type</label>
        <button type="button" class="fpill active" data-pay-filter="">All</button>
        <button type="button" class="fpill fpill-pos" data-pay-filter="sale_payout">
            <span class="fpill-dot" style="background:#22c55e"></span>Sale Payout
        </button>
        <button type="button" class="fpill fpill-neg" data-pay-filter="payout_deduction">
            <span class="fpill-dot" style="background:#ec4899"></span>Deduction
        </button>
        <button type="button" class="fpill fpill-manual" data-pay-filter="manual_adjustment">
            <span class="fpill-dot" style="background:#f5ca99"></span>Manual
        </button>
    </div>

    <?php if (!empty($payments)): ?>
    <div class="table-responsive datatable-custom">
        <table class="js-datatable eg-payments-table table table-borderless table-nowrap table-align-middle card-table"
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
                    $pCents = (int)($p['amount_cents'] ?? $p['amount'] ?? 0);
                    $isPos  = $pCents >= 0;
                    $typeClass = match($pType) {
                        'sale_payout'       => 'sale',
                        'payout_deduction'  => 'deduct',
                        'manual_adjustment' => 'manual',
                        default             => '',
                    };
                ?>
                    <tr data-pay-type="<?= htmlspecialchars($pType, ENT_QUOTES) ?>">
                        <td>
                            <span class="badge-type <?= $typeClass ?>">
                                <?= htmlspecialchars($pType ?: '—', ENT_QUOTES) ?>
                            </span>
                        </td>
                        <td class="text-end fw-bold <?= $isPos ? 'text-success' : 'text-danger' ?>"
                            data-order="<?= $pCents ?>">
                            <?= $isPos ? '+' : '' ?>€<?= number_format(abs($pCents) / 100, 2) ?>
                        </td>
                        <td class="text-end" style="color:var(--eg-muted)">
                            €<?= number_format((int)($p['balance_after'] ?? 0) / 100, 2) ?>
                        </td>
                        <td style="color:var(--eg-muted);max-width:300px;white-space:normal;">
                            <?= htmlspecialchars((string)($p['note'] ?? '—'), ENT_QUOTES) ?>
                        </td>
                        <td style="color:var(--eg-muted)" data-order="<?= !empty($p['created_at']) ? strtotime($p['created_at']) : 0 ?>">
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
                    <span style="color:var(--eg-muted)">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="paymentsEntries" class="js-select form-select form-select-borderless w-auto"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <span style="color:var(--eg-muted)">of</span>
                    <span id="paymentsTotalQty" style="color:var(--eg-text)"></span>
                </div>
            </div>
            <div class="col-sm-auto">
                <nav id="paymentsPagination"></nav>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="card-body text-center py-5" style="color:var(--eg-muted)">
            <i class="fa-duotone fa-wallet fa-3x d-block mb-3" style="color:rgba(168,85,247,.4)"></i>
            <h5 style="color:var(--eg-text)">No payments yet</h5>
            <p class="mb-0">Your payment history will appear here once you receive earnings.</p>
        </div>
    <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    HSCore.components.HSDatatables.init($('#payments_table'), {
        language: {
            zeroRecords: '<div class="text-center p-4" style="color:var(--eg-muted)">No payments match the current filter.</div>'
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
