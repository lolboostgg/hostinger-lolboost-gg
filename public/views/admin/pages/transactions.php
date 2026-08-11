<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Transactions List - Admin Area | LoLBoost.gg']]) ?>

<?= $this->start('styles') ?>
<style>
:root{
  --lb-control-bg: rgba(255,255,255,.06);
  --lb-control-border: rgba(255,255,255,.10);
  --lb-control-inset: 0 10px 26px rgba(0,0,0,.35) inset;
}

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

.lb-page-head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:1rem;
  margin-bottom:1.35rem;
}
.lb-page-kicker{
  display:inline-flex;
  align-items:center;
  gap:.45rem;
  color:rgba(31,230,198,.9);
  font-weight:800;
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.12em;
  margin-bottom:.45rem;
}
.lb-page-title{
  font-size:1.55rem;
  font-weight:900;
  margin:0;
}
.lb-page-subtitle{
  margin:.4rem 0 0;
  color:rgba(255,255,255,.68);
}

.lb-tx-stats{
  display:grid;
  grid-template-columns:repeat(4, minmax(0, 1fr));
  gap:.85rem;
  margin-bottom:1.35rem;
}
.lb-stat-card{
  position:relative;
  overflow:hidden;
  border:1px solid rgba(255,255,255,.10);
  border-radius:14px;
  background:linear-gradient(180deg, rgba(255,255,255,.055), rgba(255,255,255,.025));
  box-shadow:0 18px 44px rgba(0,0,0,.16);
  padding:1rem 1.05rem;
  min-height:92px;
  display:flex;
  align-items:center;
  gap:.85rem;
}
.lb-stat-card:after{
  content:"";
  position:absolute;
  inset:auto -30px -45px auto;
  width:115px;
  height:115px;
  border-radius:999px;
  background:rgba(var(--bs-primary-rgb), .10);
  filter:blur(1px);
}
.lb-stat-icon{
  width:42px;
  height:42px;
  border-radius:12px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  background:rgba(var(--bs-primary-rgb), .14);
  color:rgba(255,255,255,.92);
  flex:0 0 auto;
}
.lb-stat-icon.success{ background:rgba(31,230,198,.12); color:#1fe6c6; }
.lb-stat-icon.warning{ background:rgba(255,196,77,.12); color:#ffc44d; }
.lb-stat-icon.danger{ background:rgba(255,107,107,.12); color:#ff6b6b; }
.lb-stat-label{
  font-size:.72rem;
  line-height:1;
  letter-spacing:.08em;
  color:rgba(255,255,255,.52);
  text-transform:uppercase;
  font-weight:800;
  margin-bottom:.35rem;
}
.lb-stat-value{
  color:#fff;
  font-weight:950;
  font-size:1.25rem;
  line-height:1.1;
}
.lb-stat-note{
  color:rgba(255,255,255,.58);
  font-size:.75rem;
  margin-top:.25rem;
}

.lb-tx-card{
  border:1px solid rgba(255,255,255,.10);
  border-radius:14px;
  overflow:visible;
  background:rgba(255,255,255,.025);
  box-shadow:0 18px 50px rgba(0,0,0,.18);
}
.lb-tx-card .card-header{
  padding:1.05rem 1.2rem;
  border-bottom:1px solid rgba(255,255,255,.08);
}
.lb-card-title-wrap{
  display:flex;
  flex-direction:column;
  gap:.25rem;
}
.lb-card-title-wrap .card-header-title{ font-weight:900; }
.lb-card-desc{
  color:rgba(255,255,255,.56);
  font-size:.84rem;
}
.transactions-search{
  display:flex;
  align-items:center;
  gap:.35rem;
  border:1px solid var(--lb-control-border);
  border-radius:999px !important;
  background:var(--lb-control-bg);
  box-shadow:var(--lb-control-inset);
  padding:.18rem .55rem;
  min-height:2.45rem;
  min-width:min(100%, 280px);
  overflow:hidden;
}
.transactions-search .input-group-text{
  border:0 !important;
  background:transparent !important;
  color:rgba(255,255,255,.55);
  padding:0 .35rem 0 .55rem;
}
.transactions-search .form-control{
  border:0 !important;
  background:transparent !important;
  color:rgba(255,255,255,.92);
  padding:0;
  border-radius:999px !important;
}
.transactions-search .form-control::placeholder{ color:rgba(255,255,255,.45); }
.transactions-search:focus-within{
  border-color:rgba(var(--bs-primary-rgb), .55);
  box-shadow:var(--lb-control-inset), 0 0 0 .20rem rgba(var(--bs-primary-rgb), .15);
}

.lb-filter-bar{
  display:flex;
  align-items:center;
  gap:.75rem;
  padding:.95rem 1.2rem;
  flex-wrap:wrap;
  border-bottom:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.015);
}
.lb-filter-label{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:rgba(255,255,255,.50);
  font-weight:850;
}
.lb-tx-pills{
  display:inline-flex;
  align-items:center;
  gap:.25rem;
  padding:.28rem;
  border-radius:999px;
  background:var(--lb-control-bg);
  border:1px solid var(--lb-control-border);
  box-shadow:var(--lb-control-inset);
  max-width:100%;
  overflow-x:auto;
  -webkit-overflow-scrolling:touch;
}
.lb-tx-pills::-webkit-scrollbar{ height:0; }
.tx-fpill{
  appearance:none;
  border:0;
  background:transparent;
  color:rgba(255,255,255,.70);
  border-radius:999px;
  padding:.42rem .78rem;
  font-size:.80rem;
  line-height:1;
  font-weight:800;
  white-space:nowrap;
  display:inline-flex;
  align-items:center;
  gap:.45rem;
  transition:background-color .15s ease, color .15s ease, box-shadow .15s ease, transform .05s ease;
}
.tx-fpill:hover{ background:rgba(255,255,255,.06); color:rgba(255,255,255,.92); }
.tx-fpill:active{ transform:translateY(1px); }
.tx-fpill.active{
  color:#fff;
  background:linear-gradient(180deg, rgba(var(--bs-primary-rgb), .55), rgba(var(--bs-primary-rgb), .22));
  box-shadow:0 0 0 1px rgba(var(--bs-primary-rgb), .45) inset, 0 10px 26px rgba(0,0,0,.30);
}
.lb-pill-dot{
  width:7px;
  height:7px;
  border-radius:999px;
  display:inline-block;
  background:var(--dot, rgba(255,255,255,.35));
  box-shadow:0 0 0 2px rgba(0,0,0,.25);
}
.tx-fpill[data-tx-status=""], .tx-fpill[data-tx-processor=""] .lb-pill-dot{ display:inline-flex; }
.tx-fpill[data-tx-status=""] .lb-pill-dot,
.tx-fpill[data-tx-processor=""] .lb-pill-dot{ display:none; }
.lb-filter-divider{
  width:1px;
  height:24px;
  background:rgba(255,255,255,.12);
  margin:0 .15rem;
}

#transactions_table thead th{
  font-size:.72rem;
  letter-spacing:.06em;
  text-transform:uppercase;
  color:rgba(255,255,255,.70);
}
#transactions_table tbody td{ padding-top:1rem; padding-bottom:1rem; }
#transactions_table tbody tr{ transition:background-color .15s ease; }
#transactions_table tbody tr:hover{ background:rgba(255,255,255,.03); }
.table-responsive.datatable-custom{
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
}
.lb-tx-card .card-footer{
  border-top:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.012);
}
.lb-tx-status,
.lb-processor-pill,
.lb-amount-pill{
  display:inline-flex;
  align-items:center;
  gap:.45rem;
  padding:.34rem .70rem;
  border-radius:999px;
  font-weight:900;
  font-size:.72rem;
  letter-spacing:.06em;
  text-transform:uppercase;
  border:1px solid rgba(255,255,255,.10);
  background:rgba(255,255,255,.04);
  color:rgba(255,255,255,.85);
  white-space:nowrap;
}
.lb-tx-status .dot,
.lb-processor-pill .dot{
  width:7px;
  height:7px;
  border-radius:50%;
  background:currentColor;
  opacity:.95;
}
.lb-tx-status.status-paid,
.lb-tx-status.status-completed,
.lb-tx-status.status-success,
.lb-tx-status.status-succeeded{ color:#1fe6c6; border-color:rgba(31,230,198,.22); background:rgba(31,230,198,.10); }
.lb-tx-status.status-unpaid,
.lb-tx-status.status-pending{ color:#ffc44d; border-color:rgba(255,196,77,.22); background:rgba(255,196,77,.10); }
.lb-tx-status.status-failed,
.lb-tx-status.status-canceled,
.lb-tx-status.status-cancelled,
.lb-tx-status.status-refunded{ color:#ff6b6b; border-color:rgba(255,107,107,.20); background:rgba(255,107,107,.10); }
.lb-processor-pill.processor-stripe{ color:#8e7dff; border-color:rgba(142,125,255,.22); background:rgba(142,125,255,.10); }
.lb-processor-pill.processor-paypal{ color:#4ea1ff; border-color:rgba(78,161,255,.25); background:rgba(78,161,255,.12); }
.lb-processor-pill.processor-coinbase,
.lb-processor-pill.processor-crypto{ color:#ffc44d; border-color:rgba(255,196,77,.22); background:rgba(255,196,77,.10); }
.lb-amount-pill{
  justify-content:flex-end;
  min-width:86px;
  letter-spacing:0;
  text-transform:none;
}
.lb-amount-pill.negative{ color:#ff6b6b; border-color:rgba(255,107,107,.20); background:rgba(255,107,107,.10); }
.lb-amount-pill.positive{ color:#1fe6c6; border-color:rgba(31,230,198,.22); background:rgba(31,230,198,.10); }
.lb-token-text{
  display:inline-block;
  max-width:220px;
  overflow:hidden;
  text-overflow:ellipsis;
  vertical-align:middle;
}

@media (max-width: 991.98px){
  .lb-page-head{ align-items:flex-start; flex-direction:column; }
  .lb-tx-stats{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
  .transactions-search{ width:100%; }
}
@media (max-width: 575.98px){
  .lb-tx-stats{ grid-template-columns:1fr; }
  .lb-filter-divider{ display:none; }
}
</style>
<?= $this->end() ?>

<div class="lb-page-head">
  <div>
    <div class="lb-page-kicker"><i class="fa-duotone fa-credit-card"></i> Admin Finance</div>
    <h2 class="lb-page-title">Transactions</h2>
    <p class="lb-page-subtitle">Review customer payments, processors, statuses and invoice references.</p>
  </div>
</div>

<div class="lb-tx-stats">
  <div class="lb-stat-card">
    <div class="lb-stat-icon"><i class="fa-duotone fa-receipt"></i></div>
    <div>
      <div class="lb-stat-label">Total Entries</div>
      <div class="lb-stat-value" id="transactionsStatTotal">0</div>
      <div class="lb-stat-note">All loaded records</div>
    </div>
  </div>
  <div class="lb-stat-card">
    <div class="lb-stat-icon success"><i class="fa-duotone fa-filter-list"></i></div>
    <div>
      <div class="lb-stat-label">Filtered</div>
      <div class="lb-stat-value" id="transactionsStatFiltered">0</div>
      <div class="lb-stat-note">Current result set</div>
    </div>
  </div>
  <div class="lb-stat-card">
    <div class="lb-stat-icon warning"><i class="fa-duotone fa-badge-check"></i></div>
    <div>
      <div class="lb-stat-label">Status Filter</div>
      <div class="lb-stat-value" id="transactionsStatStatus">All</div>
      <div class="lb-stat-note">Selected payment status</div>
    </div>
  </div>
  <div class="lb-stat-card">
    <div class="lb-stat-icon danger"><i class="fa-duotone fa-building-columns"></i></div>
    <div>
      <div class="lb-stat-label">Processor</div>
      <div class="lb-stat-value" id="transactionsStatProcessor">All</div>
      <div class="lb-stat-note">Selected processor</div>
    </div>
  </div>
</div>

<div class="card lb-tx-card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="lb-card-title-wrap">
        <h4 class="card-header-title mb-0">Transaction History</h4>
        <div class="lb-card-desc">Search and filter all customer transaction entries.</div>
      </div>
      <div class="input-group input-group-merge transactions-search">
        <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
        <input id="transactionsSearch" type="search" class="form-control" placeholder="Search transactions" aria-label="Search transactions">
      </div>
    </div>
  </div>

  <div class="lb-filter-bar">
    <span class="lb-filter-label">Status</span>
    <div class="lb-tx-pills" role="tablist" aria-label="Filter by transaction status">
      <button type="button" class="tx-fpill active" data-tx-status="" data-label="All" aria-pressed="true"><span class="lb-pill-dot"></span>All</button>
      <button type="button" class="tx-fpill" data-tx-status="PAID" data-label="Paid" style="--dot:#1fe6c6" aria-pressed="false"><span class="lb-pill-dot"></span>Paid</button>
      <button type="button" class="tx-fpill" data-tx-status="UNPAID" data-label="Unpaid" style="--dot:#ffc44d" aria-pressed="false"><span class="lb-pill-dot"></span>Unpaid</button>
      <button type="button" class="tx-fpill" data-tx-status="PENDING" data-label="Pending" style="--dot:#ffc44d" aria-pressed="false"><span class="lb-pill-dot"></span>Pending</button>
      <button type="button" class="tx-fpill" data-tx-status="FAILED" data-label="Failed" style="--dot:#ff6b6b" aria-pressed="false"><span class="lb-pill-dot"></span>Failed</button>
      <button type="button" class="tx-fpill" data-tx-status="REFUNDED" data-label="Refunded" style="--dot:#ff6b6b" aria-pressed="false"><span class="lb-pill-dot"></span>Refunded</button>
    </div>

    <span class="lb-filter-divider"></span>

    <span class="lb-filter-label">Processor</span>
    <div class="lb-tx-pills" role="tablist" aria-label="Filter by processor">
      <button type="button" class="tx-fpill active" data-tx-processor="" data-label="All" aria-pressed="true"><span class="lb-pill-dot"></span>All</button>
      <button type="button" class="tx-fpill" data-tx-processor="stripe" data-label="Stripe" style="--dot:#8e7dff" aria-pressed="false"><span class="lb-pill-dot"></span>Stripe</button>
      <button type="button" class="tx-fpill" data-tx-processor="paypal" data-label="PayPal" style="--dot:#4ea1ff" aria-pressed="false"><span class="lb-pill-dot"></span>PayPal</button>
      <button type="button" class="tx-fpill" data-tx-processor="coinbase" data-label="Coinbase" style="--dot:#ffc44d" aria-pressed="false"><span class="lb-pill-dot"></span>Coinbase</button>
    </div>
  </div>

  <div class="table-responsive datatable-custom">
    <table
      class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
      id="transactions_table"
      data-hs-datatables-options='{
        "order": [[7, "desc"]],
        "search": "#transactionsSearch",
        "isResponsive": false,
        "isShowPaging": false,
        "pagination": "transactionsPagination",
        "entries": "#transactionsEntries",
        "info": {"totalQty": "#transactionsTotalQty"}
      }'>
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Client</th>
          <th>Processor</th>
          <th>Invoice ID</th>
          <th>Token</th>
          <th>Status</th>
          <th class="text-end">Amount</th>
          <th class="text-end">Created At</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="card-footer">
    <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
      <div class="col-sm mb-2 mb-sm-0">
        <div class="d-flex align-items-center gap-2 justify-content-center justify-content-sm-start">
          <span>Showing:</span>
          <div class="tom-select-custom">
            <select id="transactionsEntries" class="js-select form-select form-select-borderless w-auto"
                    data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <span class="text-secondary">of</span>
          <span id="transactionsTotalQty"></span>
        </div>
      </div>
      <div class="col-sm-auto">
        <div class="d-flex justify-content-center justify-content-sm-end">
          <nav id="transactionsPagination" aria-label="Transactions pagination"></nav>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    var activeStatus = '';
    var activeProcessor = '';
    var lastJson = null;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function normalize(raw) {
        return String(raw || '').replace(/<[^>]*>/g, '').trim().toLowerCase().replace(/\s+/g, '_').replace(/-/g, '_');
    }

    function pretty(raw) {
        var text = String(raw || '').replace(/<[^>]*>/g, '').trim();
        if (!text) return 'Unknown';
        return text.replace(/_/g, ' ').replace(/\b\w/g, function (m) { return m.toUpperCase(); });
    }

    function formatStatus(raw) {
        var cls = normalize(raw);
        return '<span class="lb-tx-status status-' + escapeHtml(cls || 'unknown') + '"><span class="dot" aria-hidden="true"></span><span>' + escapeHtml(pretty(raw)) + '</span></span>';
    }

    function formatProcessor(raw) {
        var cls = normalize(raw);
        return '<span class="lb-processor-pill processor-' + escapeHtml(cls || 'unknown') + '"><span class="dot" aria-hidden="true"></span><span>' + escapeHtml(pretty(raw)) + '</span></span>';
    }

    function formatAmount(raw) {
        var text = String(raw == null ? '' : raw).replace(/<[^>]*>/g, '').trim();
        var numeric = parseFloat(text.replace(/[^0-9,.-]/g, '').replace(',', '.'));
        var cls = !isNaN(numeric) && numeric < 0 ? 'negative' : 'positive';
        return '<span class="lb-amount-pill ' + cls + '">' + (raw || '0') + '</span>';
    }

    function formatToken(raw) {
        var safe = escapeHtml(String(raw || '').replace(/<[^>]*>/g, '').trim() || '—');
        return '<span class="lb-token-text" title="' + safe + '">' + safe + '</span>';
    }

    function updateStats() {
        var total = 0;
        var filtered = 0;
        if (lastJson) {
            total = lastJson.recordsTotal || 0;
            filtered = lastJson.recordsFiltered || total || 0;
        } else if (dt) {
            var info = dt.page.info();
            total = info.recordsTotal || 0;
            filtered = info.recordsDisplay || 0;
        }

        $('#transactionsStatTotal').text(total);
        $('#transactionsStatFiltered').text(filtered);
        $('#transactionsStatStatus').text($('[data-tx-status].active').data('label') || 'All');
        $('#transactionsStatProcessor').text($('[data-tx-processor].active').data('label') || 'All');
    }

    HSCore.components.HSDatatables.init($('#transactions_table'), {
        serverSide: true,
        processing: true,
        ajax: {
            url: '<?= ADMN_URL ?>/transactions/data',
            data: function (d) {
                if (activeStatus) d.status_filter = activeStatus;
                if (activeProcessor) d.processor_filter = activeProcessor;
            },
            dataSrc: function (json) {
                lastJson = json;
                setTimeout(updateStats, 0);
                return json.data || [];
            }
        },
        order: [[7, 'desc']],
        columns: [
            { data: 0, defaultContent: '' },
            { data: 1, defaultContent: '—' },
            { data: 2, defaultContent: '—', render: function (data, type, row) {
                var raw = row && row.length > 8 ? row[8] : data;
                return type === 'display' ? formatProcessor(raw || data) : (raw || data || '');
            } },
            { data: 3, defaultContent: '—' },
            { data: 4, defaultContent: '—', render: function (data, type) { return type === 'display' ? formatToken(data) : data; } },
            { data: 5, defaultContent: '—', render: function (data, type, row) {
                var raw = row && row.length > 9 ? row[9] : data;
                return type === 'display' ? formatStatus(raw || data) : (raw || data || '');
            } },
            { data: 6, defaultContent: '0', className: 'text-end', render: function (data, type) { return type === 'display' ? formatAmount(data) : data; } },
            { data: 7, defaultContent: '—', className: 'text-end' }
        ],
        language: {
            processing: '<div class="text-center p-4">Loading transactions...</div>',
            zeroRecords: '<div class="text-center p-4"><img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="No data" style="width:10rem;"><p class="mb-0 text-muted">No transactions match the current filter.</p></div>'
        }
    });

    var dt = $('#transactions_table').DataTable();

    dt.on('draw xhr', function () {
        updateStats();
    });

    $('[data-tx-status]').on('click', function () {
        $('[data-tx-status]').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        activeStatus = $(this).data('tx-status') || '';
        updateStats();
        dt.ajax.reload();
    });

    $('[data-tx-processor]').on('click', function () {
        $('[data-tx-processor]').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        activeProcessor = $(this).data('tx-processor') || '';
        updateStats();
        dt.ajax.reload();
    });

    updateStats();
});
</script>
<?= $this->end() ?>
