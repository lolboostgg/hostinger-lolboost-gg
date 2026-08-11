<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Booster Payments List - Admin Area | LoLBoost.gg']]) ?>

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

.lb-pay-stats{
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

.lb-pay-card{
  border:1px solid rgba(255,255,255,.10);
  border-radius:14px;
  overflow:visible;
  background:rgba(255,255,255,.025);
  box-shadow:0 18px 50px rgba(0,0,0,.18);
}
.lb-pay-card .card-header{
  padding:1.05rem 1.2rem;
  border-bottom:1px solid rgba(255,255,255,.08);
}
.lb-card-title-wrap{
  display:flex;
  flex-direction:column;
  gap:.25rem;
}
.lb-card-title-wrap .card-header-title{
  font-weight:900;
}
.lb-card-desc{
  color:rgba(255,255,255,.56);
  font-size:.84rem;
}

.payments-search{
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
.payments-search .input-group-text{
  border:0 !important;
  background:transparent !important;
  color:rgba(255,255,255,.55);
  padding:0 .35rem 0 .55rem;
}
.payments-search .form-control{
  border:0 !important;
  background:transparent !important;
  color:rgba(255,255,255,.92);
  padding:0;
  border-radius:999px !important;
}
.payments-search .form-control::placeholder{ color:rgba(255,255,255,.45); }
.payments-search:focus-within{
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
.lb-pay-pills{
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
.lb-pay-pills::-webkit-scrollbar{ height:0; }
.booster-fpill{
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
.booster-fpill:hover{
  background:rgba(255,255,255,.06);
  color:rgba(255,255,255,.92);
}
.booster-fpill:active{ transform:translateY(1px); }
.booster-fpill.active{
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
.booster-fpill[data-pay-filter=""] .lb-pill-dot{ display:none; }

.lb-filter-divider{
  width:1px;
  height:24px;
  background:rgba(255,255,255,.12);
  margin:0 .15rem;
}
.lb-sender-wrap{ position:relative; }
.lb-sender-btn{
  display:inline-flex;
  align-items:center;
  gap:.5rem;
  padding:.46rem .85rem;
  border-radius:999px;
  border:1px solid var(--lb-control-border);
  background:var(--lb-control-bg);
  box-shadow:var(--lb-control-inset);
  color:rgba(255,255,255,.88);
  font-size:.82rem;
  font-weight:800;
  cursor:pointer;
}
.lb-sender-btn.is-active{
  color:#fff;
  border-color:rgba(var(--bs-primary-rgb), .45);
  background:linear-gradient(180deg, rgba(var(--bs-primary-rgb), .55), rgba(var(--bs-primary-rgb), .22));
}
.lb-sender-menu{
  display:none;
  position:absolute;
  top:calc(100% + 8px);
  left:0;
  min-width:220px;
  max-height:320px;
  overflow:auto;
  background:rgba(25,28,33,.98);
  border:1px solid rgba(255,255,255,.12);
  border-radius:14px;
  padding:.4rem;
  z-index:9999;
  box-shadow:0 18px 44px rgba(0,0,0,.55);
}
.sender-option{
  padding:.5rem .7rem;
  border-radius:10px;
  font-size:.82rem;
  color:rgba(255,255,255,.88);
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:.55rem;
  transition:background-color .12s ease;
}
.sender-option:hover{ background:rgba(255,255,255,.07); }
.sender-option img{
  width:22px;
  height:22px;
  border-radius:50%;
  object-fit:cover;
}

#payments_table thead th{
  font-size:.72rem;
  letter-spacing:.06em;
  text-transform:uppercase;
  color:rgba(255,255,255,.70);
}
#payments_table tbody td{ padding-top:1rem; padding-bottom:1rem; }
#payments_table tbody tr{ transition:background-color .15s ease; }
#payments_table tbody tr:hover{ background:rgba(255,255,255,.03); }
.table-responsive.datatable-custom{
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
}
.lb-pay-card .card-footer{
  border-top:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.012);
}

.lb-pay-type,
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
.lb-pay-type .dot{
  width:7px;
  height:7px;
  border-radius:50%;
  background:currentColor;
  opacity:.95;
}
.lb-pay-type.type-order_completion{ color:#4ea1ff; border-color:rgba(78,161,255,.25); background:rgba(78,161,255,.12); }
.lb-pay-type.type-other{ color:#1fe6c6; border-color:rgba(31,230,198,.22); background:rgba(31,230,198,.10); }
.lb-pay-type.type-fine{ color:#ff6b6b; border-color:rgba(255,107,107,.20); background:rgba(255,107,107,.10); }
.lb-pay-type.type-payment_error{ color:#ffc44d; border-color:rgba(255,196,77,.22); background:rgba(255,196,77,.10); }
.lb-amount-pill{
  justify-content:flex-end;
  min-width:82px;
  letter-spacing:0;
  text-transform:none;
}
.lb-amount-pill.negative{ color:#ff6b6b; border-color:rgba(255,107,107,.20); background:rgba(255,107,107,.10); }
.lb-amount-pill.positive{ color:#1fe6c6; border-color:rgba(31,230,198,.22); background:rgba(31,230,198,.10); }

@media (max-width: 991.98px){
  .lb-page-head{ align-items:flex-start; flex-direction:column; }
  .lb-pay-stats{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
  .payments-search{ width:100%; }
}
@media (max-width: 575.98px){
  .lb-pay-stats{ grid-template-columns:1fr; }
  .lb-filter-divider{ display:none; }
}
</style>
<?= $this->end() ?>

<div class="lb-page-head">
  <div>
    <div class="lb-page-kicker"><i class="fa-duotone fa-wallet"></i> Booster Finance</div>
    <h2 class="lb-page-title">Booster Payments</h2>
    <p class="lb-page-subtitle">Review booster payouts, fines, adjustments and payment issues.</p>
  </div>
</div>

<div class="lb-pay-stats">
  <div class="lb-stat-card">
    <div class="lb-stat-icon"><i class="fa-duotone fa-receipt"></i></div>
    <div>
      <div class="lb-stat-label">Total Entries</div>
      <div class="lb-stat-value" id="paymentsStatTotal">0</div>
      <div class="lb-stat-note">All loaded records</div>
    </div>
  </div>
  <div class="lb-stat-card">
    <div class="lb-stat-icon success"><i class="fa-duotone fa-filter-list"></i></div>
    <div>
      <div class="lb-stat-label">Filtered</div>
      <div class="lb-stat-value" id="paymentsStatFiltered">0</div>
      <div class="lb-stat-note">Current result set</div>
    </div>
  </div>
  <div class="lb-stat-card">
    <div class="lb-stat-icon warning"><i class="fa-duotone fa-tags"></i></div>
    <div>
      <div class="lb-stat-label">Type Filter</div>
      <div class="lb-stat-value" id="paymentsStatType">All</div>
      <div class="lb-stat-note">Selected payment type</div>
    </div>
  </div>
  <div class="lb-stat-card">
    <div class="lb-stat-icon danger"><i class="fa-duotone fa-user-shield"></i></div>
    <div>
      <div class="lb-stat-label">Sender</div>
      <div class="lb-stat-value" id="paymentsStatSender">All</div>
      <div class="lb-stat-note">Selected sender</div>
    </div>
  </div>
</div>

<div class="card lb-pay-card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="lb-card-title-wrap">
        <h4 class="card-header-title mb-0">Payment History</h4>
        <div class="lb-card-desc">Search and filter all booster payment entries.</div>
      </div>
      <div class="input-group input-group-merge payments-search">
        <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
        <input id="paymentsSearch" type="search" class="form-control" placeholder="Search payments" aria-label="Search payments">
      </div>
    </div>
  </div>

  <div class="lb-filter-bar">
    <span class="lb-filter-label">Type</span>
    <div class="lb-pay-pills" role="tablist" aria-label="Filter by payment type">
      <button type="button" class="booster-fpill active" data-pay-filter="" data-label="All" aria-pressed="true"><span class="lb-pill-dot"></span>All</button>
      <button type="button" class="booster-fpill" data-pay-filter="order_completion" data-label="Order Completion" style="--dot:#4ea1ff" aria-pressed="false"><span class="lb-pill-dot"></span>Order Completion</button>
      <button type="button" class="booster-fpill" data-pay-filter="other" data-label="Other" style="--dot:#1fe6c6" aria-pressed="false"><span class="lb-pill-dot"></span>Other</button>
      <button type="button" class="booster-fpill" data-pay-filter="fine" data-label="Fine" style="--dot:#ff6b6b" aria-pressed="false"><span class="lb-pill-dot"></span>Fine</button>
      <button type="button" class="booster-fpill" data-pay-filter="payment_error" data-label="Payment Error" style="--dot:#ffc44d" aria-pressed="false"><span class="lb-pill-dot"></span>Payment Error</button>
    </div>

    <span class="lb-filter-divider"></span>

    <span class="lb-filter-label">Sender</span>
    <div id="senderDropdownWrap" class="lb-sender-wrap">
      <button type="button" id="senderDropdownBtn" class="lb-sender-btn" aria-expanded="false">
        <i class="fa-duotone fa-user-shield"></i>
        <span id="senderDropdownLabel">All Senders</span>
        <i class="fa-solid fa-chevron-down" style="font-size:.7rem;opacity:.55"></i>
      </button>
      <div id="senderDropdownMenu" class="lb-sender-menu">
        <div class="sender-option" data-value="">All Senders</div>
      </div>
    </div>
  </div>

  <div class="table-responsive datatable-custom">
    <table
      class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
      id="payments_table"
      data-hs-datatables-options='{
        "order": [[6, "desc"]],
        "search": "#paymentsSearch",
        "isResponsive": false,
        "isShowPaging": false,
        "pagination": "paymentsPagination",
        "entries": "#paymentsEntries",
        "info": {"totalQty": "#paymentsTotalQty"}
      }'>
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Booster</th>
          <th>Sender</th>
          <th>Type</th>
          <th>Note</th>
          <th class="text-end">Amount</th>
          <th>Date</th>
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
            <select id="paymentsEntries" class="js-select form-select form-select-borderless w-auto"
                    data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <span class="text-secondary">of</span>
          <span id="paymentsTotalQty"></span>
        </div>
      </div>
      <div class="col-sm-auto">
        <div class="d-flex justify-content-center justify-content-sm-end">
          <nav id="paymentsPagination" aria-label="Payments pagination"></nav>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    var activeFilter = '';
    var activeSender = '';
    var lastJson = null;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function formatType(raw) {
        var value = String(raw || '').replace(/<[^>]*>/g, '').trim();
        var normalized = value.toLowerCase().replace(/\s+/g, '_').replace(/-/g, '_');
        var labels = {
            order_completion: 'Order Completion',
            other: 'Other',
            fine: 'Fine',
            payment_error: 'Payment Error'
        };
        var label = labels[normalized] || value.replace(/_/g, ' ').replace(/\b\w/g, function (m) { return m.toUpperCase(); }) || 'Unknown';
        var cls = ['order_completion', 'other', 'fine', 'payment_error'].indexOf(normalized) !== -1 ? normalized : 'other';
        return '<span class="lb-pay-type type-' + cls + '"><span class="dot" aria-hidden="true"></span><span>' + escapeHtml(label) + '</span></span>';
    }

    function formatAmount(raw) {
        var text = String(raw == null ? '' : raw).replace(/<[^>]*>/g, '').trim();
        var numeric = parseFloat(text.replace(/[^0-9,.-]/g, '').replace(',', '.'));
        var cls = !isNaN(numeric) && numeric < 0 ? 'negative' : 'positive';
        return '<span class="lb-amount-pill ' + cls + '">' + (raw || '0') + '</span>';
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

        $('#paymentsStatTotal').text(total);
        $('#paymentsStatFiltered').text(filtered);
        $('#paymentsStatType').text($('.booster-fpill.active').data('label') || 'All');
        $('#paymentsStatSender').text($('#senderDropdownLabel').text().trim() || 'All');
    }

    HSCore.components.HSDatatables.init($('#payments_table'), {
        serverSide: true,
        processing: true,
        ajax: {
            url: '<?= ADMN_URL ?>/booster/payments/data',
            data: function (d) {
                if (activeFilter) d.type_filter = activeFilter;
                if (activeSender) d.sender_filter = activeSender;
            },
            dataSrc: function (json) {
                lastJson = json;
                setTimeout(updateStats, 0);
                return json.data || [];
            }
        },
        order: [[6, 'desc']],
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3, render: function (data, type, row) { return type === 'display' ? formatType(row[7] || data) : (row[7] || data); } },
            { data: 4, defaultContent: '—' },
            { data: 5, className: 'text-end', render: function (data, type) { return type === 'display' ? formatAmount(data) : data; } },
            { data: 6 },
            { data: 7, visible: false },
            { data: 8, visible: false }
        ],
        language: {
            zeroRecords: '<div class="text-center p-4"><img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="No data" style="width:10rem;"><p class="mb-0 text-muted">No payments match the current filter.</p></div>'
        }
    });

    var dt = $('#payments_table').DataTable();

    dt.on('draw xhr', function () {
        updateStats();
    });

    $.getJSON('<?= ADMN_URL ?>/booster/payments/senders', function (senders) {
        var $menu = $('#senderDropdownMenu');
        $.each(senders, function (i, s) {
            var $item = $('<div>', {
                class: 'sender-option',
                'data-value': s.username
            }).text(s.label);
            if (s.icon) {
                $item.prepend($('<img>', { src: s.icon, alt: '' }));
            }
            $menu.append($item);
        });
    });

    $('#senderDropdownBtn').on('click', function (e) {
        e.stopPropagation();
        var $menu = $('#senderDropdownMenu');
        var isOpen = $menu.is(':visible');
        $('.lb-sender-menu').hide();
        $menu.toggle(!isOpen);
        $(this).attr('aria-expanded', !isOpen ? 'true' : 'false');
    });

    $(document).on('click', '.sender-option', function () {
        var val = $(this).data('value') || '';
        var label = $(this).text().trim() || 'All Senders';
        activeSender = val;
        $('#senderDropdownLabel').text(label);
        $('#senderDropdownBtn').toggleClass('is-active', val !== '').attr('aria-expanded', 'false');
        $('#senderDropdownMenu').hide();
        updateStats();
        dt.ajax.reload();
    });

    $(document).on('click', function () {
        $('#senderDropdownMenu').hide();
        $('#senderDropdownBtn').attr('aria-expanded', 'false');
    });

    $('[data-pay-filter]').on('click', function () {
        $('.booster-fpill').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        activeFilter = $(this).data('pay-filter') || '';
        updateStats();
        dt.ajax.reload();
    });

    updateStats();
});
</script>
<?= $this->end() ?>
