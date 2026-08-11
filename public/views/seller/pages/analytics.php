<?php
// Seller Analytics
// Variables from controller: $seller_data, $analytics, $rangeFrom, $rangeTo
?>

<?= $this->layout('seller/layouts/main', [
  'meta' => [
    'title' => 'Analytics - Seller Area | LoLBoost.gg',
  ]
]) ?>

<?php
$sellerName = (string)($seller_data['username'] ?? 'partner');
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

$profitTotal = (float)($analytics['profit_total'] ?? 0);
$profitPrev  = (float)($analytics['profit_prev'] ?? 0);
$ordersTotal = (int)($analytics['orders_total'] ?? 0);
$ordersPrev  = (int)($analytics['orders_prev'] ?? 0);

$profitDelta = $profitTotal - $profitPrev;
$profitPct   = $profitPrev > 0 ? round(($profitDelta / $profitPrev) * 100, 2) : ($profitTotal > 0 ? 100 : 0);
$ordersDelta = $ordersTotal - $ordersPrev;
$ordersPct   = $ordersPrev > 0 ? round(($ordersDelta / $ordersPrev) * 100, 2) : ($ordersTotal > 0 ? 100 : 0);

$days = $analytics['days'] ?? [];
$hasData = !empty(array_filter($days, fn($d) => ($d['orders'] ?? 0) > 0 || ($d['revenue'] ?? 0) > 0));

function sa2_fmt_date_short($d) { return date('M j', strtotime($d)); }
?>

<div class="seller-analytics-v1">
  <div class="sa2-topbar">
    <div>
      <h1 class="sa2-greeting"><?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($sellerName) ?></h1>
    </div>
    <div class="sa2-daterange" id="sa2DateRange">
      <button type="button" class="sa2-daterange-trigger" id="sa2DateRangeTrigger">
        <i class="fa-duotone fa-calendar-days"></i>
        <span id="sa2DateRangeLabel"><?= htmlspecialchars(sa2_fmt_date_short($rangeFrom)) ?> – <?= htmlspecialchars(sa2_fmt_date_short($rangeTo)) ?></span>
        <span class="sa2-daterange-tz">GMT<?= htmlspecialchars(date('P') === '+00:00' ? '+0' : date('O')) ?></span>
      </button>

      <form method="get" id="sa2RangeForm" class="sa2-daterange-panel">
        <div class="sa2-dr-body">
          <div class="sa2-dr-cal">
            <div class="sa2-dr-cal-head">
              <span id="sa2DrMonthLabel">July 2026</span>
              <div class="sa2-dr-cal-nav">
                <button type="button" id="sa2DrPrev"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" id="sa2DrNext"><i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </div>
            <div class="sa2-dr-cal-dow">
              <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
            </div>
            <div class="sa2-dr-cal-grid" id="sa2DrCalGrid"></div>
          </div>
          <div class="sa2-dr-presets">
            <button type="button" data-days="1">Last 24 hours</button>
            <button type="button" data-days="7">Last 7 days</button>
            <button type="button" data-days="14">Last 14 days</button>
            <button type="button" data-days="30">Last 30 days</button>
            <button type="button" data-days="90">Last 90 days</button>
            <button type="button" data-month="this">This month</button>
            <button type="button" data-month="last">Last month</button>
          </div>
        </div>
        <div class="sa2-dr-footer">
          <div class="sa2-dr-field">
            <label>Start</label>
            <input type="text" id="sa2DrStartDisplay" readonly value="<?= htmlspecialchars(date('d.m.Y', strtotime($rangeFrom))) ?>">
            <input type="hidden" name="from" id="sa2DrStart" value="<?= htmlspecialchars($rangeFrom) ?>">
          </div>
          <div class="sa2-dr-field">
            <label>End</label>
            <input type="text" id="sa2DrEndDisplay" readonly value="<?= htmlspecialchars(date('d.m.Y', strtotime($rangeTo))) ?>">
            <input type="hidden" name="to" id="sa2DrEnd" value="<?= htmlspecialchars($rangeTo) ?>">
          </div>
          <span class="sa2-dr-tzlabel"><?= htmlspecialchars(date('T')) ?> (GMT<?= htmlspecialchars(date('P') === '+00:00' ? '+0' : date('O')) ?>)</span>
          <button type="submit" class="sa2-dr-apply">Apply</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Last 24h -->
  <div class="card sa2-card mb-3">
    <div class="card-body p-4">
      <div class="sa2-section-top">
        <span class="sa2-section-title">Last 24h</span>
        <span class="sa2-info-note"><i class="fa-solid fa-circle-info"></i> Based on your local server time</span>
      </div>
      <div class="sa2-kpi-row mt-3">
        <div class="sa2-kpi">
          <div class="sa2-kpi-label sa2-c-green">Orders</div>
          <div class="sa2-kpi-value"><?= (int)($analytics['orders_24h'] ?? 0) ?></div>
        </div>
        <div class="sa2-kpi">
          <div class="sa2-kpi-label sa2-c-blue">Profit</div>
          <div class="sa2-kpi-value">€<?= number_format(((int)($analytics['profit_24h'] ?? 0)) / 100, 2) ?></div>
        </div>
        <div class="sa2-kpi">
          <div class="sa2-kpi-label sa2-c-amber">Listed Offers</div>
          <div class="sa2-kpi-value"><?= (int)($analytics['listed_offers'] ?? 0) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Profits -->
  <div class="card sa2-card mb-3">
    <div class="card-body p-4">
      <div class="sa2-section-top">
        <div>
          <span class="sa2-section-title">Total Profits</span>
          <div class="sa2-big-row">
            <div class="sa2-big-value">€<?= number_format($profitTotal, 2) ?></div>
            <span class="sa2-badge <?= $profitDelta >= 0 ? 'is-up' : 'is-down' ?>"><i class="fa-solid fa-arrow-<?= $profitDelta >= 0 ? 'up' : 'down' ?>"></i><?= number_format(abs($profitPct), 2) ?>%</span>
          </div>
          <div class="sa2-trend-sub"><?= $profitDelta >= 0 ? '+' : '−' ?>€<?= number_format(abs($profitDelta), 2) ?> vs previous period</div>
        </div>
      </div>
      <?php if ($hasData): ?>
        <div class="sa2-chart-wrap mt-3"><canvas id="sa2ProfitChart"></canvas></div>
      <?php else: ?>
        <div class="sa2-empty-state mt-2">
          <i class="fa-duotone fa-chart-column"></i>
          <div class="sa2-empty-title">No profit data in this range</div>
          <div class="sa2-empty-text">Try a wider date range or check back after your next sale.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Orders -->
  <div class="card sa2-card">
    <div class="card-body p-4">
      <div class="sa2-section-top">
        <div>
          <span class="sa2-section-title">Orders</span>
          <div class="sa2-big-row">
            <div class="sa2-big-value"><?= $ordersTotal ?></div>
            <span class="sa2-badge <?= $ordersDelta >= 0 ? 'is-up' : 'is-down' ?>"><i class="fa-solid fa-arrow-<?= $ordersDelta >= 0 ? 'up' : 'down' ?>"></i><?= number_format(abs($ordersPct), 2) ?>%</span>
          </div>
          <div class="sa2-trend-sub"><?= $ordersDelta >= 0 ? '+' : '−' ?><?= abs($ordersDelta) ?> vs previous period</div>
        </div>
        <div class="sa2-legend">
          <span><i class="sa2-dot sa2-dot--accounts"></i>Accounts</span>
          <span><i class="sa2-dot sa2-dot--dg"></i>Items &amp; Digital Goods</span>
        </div>
      </div>
      <?php if ($hasData): ?>
        <div class="sa2-chart-wrap mt-3"><canvas id="sa2OrdersChart"></canvas></div>
      <?php else: ?>
        <div class="sa2-empty-state mt-2">
          <i class="fa-duotone fa-cart-shopping"></i>
          <div class="sa2-empty-title">No orders in this range</div>
          <div class="sa2-empty-text">Try a wider date range or check back after your next sale.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
.seller-analytics-v1 { --sa2-text:rgba(255,255,255,.94); --sa2-muted:rgba(255,255,255,.55); }
.seller-analytics-v1 .sa2-card { background:var(--bs-card-bg) !important; border:var(--bs-card-border-width) solid var(--bs-card-border-color) !important; border-radius:22px !important; box-shadow:none !important; }
.seller-analytics-v1 .sa2-card::before { display:none !important; content:none !important; }

.sa2-topbar { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
.sa2-greeting { font-size:1.4rem; font-weight:900; color:var(--sa2-text); margin:0; }

/* Date range picker */
.sa2-daterange { position:relative; }
.sa2-daterange-trigger { display:flex; align-items:center; gap:9px; padding:9px 14px; border-radius:12px; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.03); color:var(--sa2-text); font-weight:800; font-size:.84rem; cursor:pointer; transition:border-color .15s ease, background .15s ease; }
.sa2-daterange-trigger:hover { border-color:rgba(109,92,255,.4); background:rgba(109,92,255,.08); }
.sa2-daterange-trigger i:first-child { color:#a78bfa; }
.sa2-daterange-tz { color:var(--sa2-muted); font-weight:700; font-size:.76rem; padding-left:6px; border-left:1px solid rgba(255,255,255,.10); margin-left:2px; }

.sa2-daterange-panel { display:none; position:absolute; top:calc(100% + 10px); right:0; z-index:200; width:min(560px, 92vw); border-radius:18px; border:1px solid rgba(255,255,255,.12); background:#121018; box-shadow:0 30px 80px rgba(0,0,0,.55); overflow:hidden; }
.sa2-daterange.is-open .sa2-daterange-panel { display:block; }

.sa2-dr-body { display:grid; grid-template-columns:1fr 170px; }
@media(max-width:560px) { .sa2-dr-body { grid-template-columns:1fr; } }

.sa2-dr-cal { padding:16px 18px; border-right:1px solid rgba(255,255,255,.08); }
@media(max-width:560px) { .sa2-dr-cal { border-right:none; border-bottom:1px solid rgba(255,255,255,.08); } }
.sa2-dr-cal-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.sa2-dr-cal-head span { font-weight:900; color:var(--sa2-text); font-size:.9rem; }
.sa2-dr-cal-nav { display:flex; gap:4px; }
.sa2-dr-cal-nav button { width:26px; height:26px; border-radius:8px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.03); color:var(--sa2-muted); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.72rem; }
.sa2-dr-cal-nav button:hover { color:#fff; border-color:rgba(109,92,255,.4); }
.sa2-dr-cal-dow { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px; }
.sa2-dr-cal-dow span { text-align:center; font-size:.68rem; font-weight:800; color:var(--sa2-muted); padding:4px 0; }
.sa2-dr-cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
.sa2-dr-day { text-align:center; font-size:.78rem; font-weight:700; color:var(--sa2-text); padding:7px 0; border-radius:8px; cursor:pointer; background:transparent; border:none; }
.sa2-dr-day:hover { background:rgba(109,92,255,.18); }
.sa2-dr-day.is-muted { color:rgba(255,255,255,.22); }
.sa2-dr-day.is-in-range { background:rgba(109,92,255,.16); border-radius:0; }
.sa2-dr-day.is-range-start { background:#fff; color:#0d0b14; border-radius:8px 0 0 8px; }
.sa2-dr-day.is-range-end { background:#fff; color:#0d0b14; border-radius:0 8px 8px 0; }
.sa2-dr-day.is-range-start.is-range-end { border-radius:8px; }

.sa2-dr-presets { padding:14px 10px; display:flex; flex-direction:column; }
.sa2-dr-presets button { text-align:left; background:transparent; border:none; color:var(--sa2-text); font-size:.82rem; font-weight:700; padding:9px 12px; border-radius:10px; cursor:pointer; }
.sa2-dr-presets button:hover { background:rgba(109,92,255,.14); }

.sa2-dr-footer { display:flex; align-items:center; gap:12px; padding:14px 18px; border-top:1px solid rgba(255,255,255,.08); flex-wrap:wrap; }
.sa2-dr-field { display:flex; flex-direction:column; gap:4px; }
.sa2-dr-field label { font-size:.68rem; font-weight:800; color:var(--sa2-muted); text-transform:uppercase; letter-spacing:.06em; }
.sa2-dr-field input[type="text"] { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.10); border-radius:9px; padding:6px 8px; color:var(--sa2-text); font-size:.8rem; font-weight:700; width:92px; cursor:default; caret-color:transparent; }
.sa2-dr-field input[type="text"]:focus { outline:none; border-color:rgba(109,92,255,.5); }
.sa2-dr-tzlabel { color:var(--sa2-muted); font-size:.76rem; font-weight:600; margin-right:auto; }
.sa2-dr-apply { padding:9px 20px; border-radius:10px; border:none; background:linear-gradient(135deg,#6d5cff,#b05cff); color:#fff; font-weight:800; font-size:.82rem; cursor:pointer; }
.sa2-dr-apply:hover { filter:brightness(1.08); }

.sa2-section-top { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.sa2-section-title { color:var(--sa2-text); font-weight:900; font-size:.88rem; }
.sa2-info-note { color:var(--sa2-muted); font-size:.78rem; display:flex; align-items:center; gap:6px; }
.sa2-big-row { display:flex; align-items:center; gap:10px; margin-top:6px; }
.sa2-big-value { font-size:1.9rem; font-weight:900; color:var(--sa2-text); line-height:1; }
.sa2-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:999px; font-size:.72rem; font-weight:800; }
.sa2-badge.is-up   { color:#4ade80; background:rgba(74,222,128,.12); }
.sa2-badge.is-down { color:#fb7185; background:rgba(251,113,133,.12); }
.sa2-trend-sub { margin-top:6px; font-size:.78rem; font-weight:600; color:var(--sa2-muted); }

.sa2-kpi-row { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
@media(max-width:767.98px) { .sa2-kpi-row { grid-template-columns:1fr; } }
.sa2-kpi { padding:14px 16px; border-radius:14px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sa2-kpi-label { font-size:.78rem; font-weight:800; }
.sa2-kpi-value { font-size:1.6rem; font-weight:900; color:var(--sa2-text); margin-top:6px; }
.sa2-c-green { color:#4ade80; } .sa2-c-blue { color:#7dd3fc; } .sa2-c-amber { color:#fbbf24; }

.sa2-legend { display:flex; gap:14px; flex-wrap:wrap; }
.sa2-legend span { display:flex; align-items:center; gap:6px; font-size:.78rem; color:var(--sa2-muted); font-weight:700; }
.sa2-dot { width:9px; height:9px; border-radius:3px; display:inline-block; }
.sa2-dot--accounts { background:#818cf8; }
.sa2-dot--dg { background:#fbbf24; }

.sa2-chart-wrap { position:relative; width:100%; height:300px; }
@media(max-width:600px) { .sa2-chart-wrap { height:220px; } }

.sa2-empty-state { text-align:center; padding:44px 20px 20px; }
.sa2-empty-state i { font-size:2.2rem; color:rgba(255,255,255,.22); display:block; margin-bottom:14px; }
.sa2-empty-title { color:var(--sa2-text); font-weight:900; font-size:.9rem; }
.sa2-empty-text  { color:var(--sa2-muted); font-size:.84rem; margin-top:4px; }
</style>

<script>
(function () {
  'use strict';

  var wrap    = document.getElementById('sa2DateRange');
  var trigger = document.getElementById('sa2DateRangeTrigger');
  var panel   = document.getElementById('sa2RangeForm');
  var startInput = document.getElementById('sa2DrStart');
  var endInput   = document.getElementById('sa2DrEnd');
  var startDisplay = document.getElementById('sa2DrStartDisplay');
  var endDisplay   = document.getElementById('sa2DrEndDisplay');
  var monthLabel = document.getElementById('sa2DrMonthLabel');
  var calGrid    = document.getElementById('sa2DrCalGrid');
  var prevBtn = document.getElementById('sa2DrPrev');
  var nextBtn = document.getElementById('sa2DrNext');
  if (!wrap || !trigger || !panel) return;

  var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

  function parseISO(s) { var p = s.split('-'); return new Date(parseInt(p[0],10), parseInt(p[1],10) - 1, parseInt(p[2],10)); }
  function toISO(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
  function toDisplay(d) { return String(d.getDate()).padStart(2, '0') + '.' + String(d.getMonth() + 1).padStart(2, '0') + '.' + d.getFullYear(); }
  function syncInputs() {
    startInput.value = toISO(rangeStart);
    endInput.value = toISO(rangeEnd);
    if (startDisplay) startDisplay.value = toDisplay(rangeStart);
    if (endDisplay) endDisplay.value = toDisplay(rangeEnd);
  }

  var rangeStart = parseISO(startInput.value);
  var rangeEnd   = parseISO(endInput.value);
  var viewMonth  = new Date(rangeEnd.getFullYear(), rangeEnd.getMonth(), 1);
  var pickingEnd = false;

  function open() { wrap.classList.add('is-open'); renderCalendar(); }
  function close() { wrap.classList.remove('is-open'); }
  trigger.addEventListener('click', function (e) {
    e.stopPropagation();
    wrap.classList.contains('is-open') ? close() : open();
  });
  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) close();
  });
  panel.addEventListener('click', function (e) { e.stopPropagation(); });

  function renderCalendar() {
    monthLabel.textContent = MONTHS[viewMonth.getMonth()] + ' ' + viewMonth.getFullYear();
    calGrid.innerHTML = '';

    var firstOfMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth(), 1);
    var startWeekday = (firstOfMonth.getDay() + 6) % 7; // Monday = 0
    var gridStart = new Date(firstOfMonth);
    gridStart.setDate(gridStart.getDate() - startWeekday);

    for (var i = 0; i < 42; i++) {
      var d = new Date(gridStart);
      d.setDate(gridStart.getDate() + i);

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'sa2-dr-day';
      btn.textContent = d.getDate();
      if (d.getMonth() !== viewMonth.getMonth()) btn.classList.add('is-muted');

      var isEnd   = (new Date(d)).setHours(0,0,0,0) === (new Date(rangeEnd.getFullYear(), rangeEnd.getMonth(), rangeEnd.getDate())).getTime();
      var isStartExact = (new Date(d)).setHours(0,0,0,0) === (new Date(rangeStart.getFullYear(), rangeStart.getMonth(), rangeStart.getDate())).getTime();
      var isInRange = d >= new Date(rangeStart.getFullYear(), rangeStart.getMonth(), rangeStart.getDate()) && d <= new Date(rangeEnd.getFullYear(), rangeEnd.getMonth(), rangeEnd.getDate());

      if (isInRange) btn.classList.add('is-in-range');
      if (isStartExact) btn.classList.add('is-range-start');
      if (isEnd) btn.classList.add('is-range-end');

      (function (day) {
        btn.addEventListener('click', function () {
          if (!pickingEnd) {
            rangeStart = day; rangeEnd = day; pickingEnd = true;
          } else {
            if (day < rangeStart) { rangeEnd = rangeStart; rangeStart = day; } else { rangeEnd = day; }
            pickingEnd = false;
          }
          syncInputs();
          renderCalendar();
        });
      })(d);

      calGrid.appendChild(btn);
    }
  }

  prevBtn.addEventListener('click', function () { viewMonth.setMonth(viewMonth.getMonth() - 1); renderCalendar(); });
  nextBtn.addEventListener('click', function () { viewMonth.setMonth(viewMonth.getMonth() + 1); renderCalendar(); });

  function setRange(start, end) {
    rangeStart = start; rangeEnd = end; pickingEnd = false;
    viewMonth = new Date(end.getFullYear(), end.getMonth(), 1);
    syncInputs();
    renderCalendar();
  }

  panel.querySelectorAll('.sa2-dr-presets button').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var today = new Date();
      if (btn.dataset.days) {
        var n = parseInt(btn.dataset.days, 10);
        var start = new Date(today); start.setDate(start.getDate() - (n - 1));
        setRange(start, today);
      } else if (btn.dataset.month === 'this') {
        setRange(new Date(today.getFullYear(), today.getMonth(), 1), today);
      } else if (btn.dataset.month === 'last') {
        var lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
        var lastMonthStart = new Date(lastMonthEnd.getFullYear(), lastMonthEnd.getMonth(), 1);
        setRange(lastMonthStart, lastMonthEnd);
      }
    });
  });
})();
</script>

<?php if ($hasData): ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/chart.js/dist/chart.min.js"></script>
<script>
(function () {
  'use strict';
  var days = <?= json_encode($days, JSON_UNESCAPED_SLASHES) ?>;
  var labels = days.map(function (d) {
    var p = d.date.split('-');
    return p[2] + '.' + p[1];
  });

  // Sparse date ranges make thin, single-day bars almost invisible, so both
  // charts use a filled line with visible dots on non-zero days instead —
  // a lone sale still reads clearly even across a 90-day window.
  function pointRadii(values) { return values.map(function (v) { return v > 0 ? 4 : 0; }); }

  var profitEl = document.getElementById('sa2ProfitChart');
  if (profitEl && typeof Chart !== 'undefined') {
    var revenueData = days.map(function (d) { return d.revenue; });
    new Chart(profitEl.getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Profit (€)',
          data: revenueData,
          borderColor: '#4ade80',
          backgroundColor: 'rgba(74,222,128,.14)',
          pointBackgroundColor: '#4ade80',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: '#4ade80',
          pointBorderWidth: 2,
          pointRadius: pointRadii(revenueData),
          pointHoverRadius: 6,
          pointHitRadius: 20,
          borderWidth: 2,
          tension: .3,
          fill: true,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: function (ctx) { return 'Profit: €' + ctx.parsed.y.toFixed(2); } } },
        },
        scales: {
          x: { ticks: { color: 'rgba(255,255,255,.4)', maxTicksLimit: 12 }, grid: { display: false } },
          y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,.4)', callback: function (v) { return '€' + v; } }, grid: { color: 'rgba(255,255,255,.06)' } },
        },
      },
    });
  }

  var ordersEl = document.getElementById('sa2OrdersChart');
  if (ordersEl && typeof Chart !== 'undefined') {
    var accountsData = days.map(function (d) { return d.accounts; });
    var dgData = days.map(function (d) { return d.dg; });
    new Chart(ordersEl.getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Accounts',
            data: accountsData,
            borderColor: '#818cf8',
            backgroundColor: 'rgba(129,140,248,.14)',
            pointBackgroundColor: '#818cf8',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#818cf8',
            pointBorderWidth: 2,
            pointRadius: pointRadii(accountsData),
            pointHoverRadius: 6,
            pointHitRadius: 20,
            borderWidth: 2,
            tension: .3,
            fill: true,
          },
          {
            label: 'Items & Digital Goods',
            data: dgData,
            borderColor: '#fbbf24',
            backgroundColor: 'rgba(251,191,36,.14)',
            pointBackgroundColor: '#fbbf24',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#fbbf24',
            pointBorderWidth: 2,
            pointRadius: pointRadii(dgData),
            pointHoverRadius: 6,
            pointHitRadius: 20,
            borderWidth: 2,
            tension: .3,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: 'rgba(255,255,255,.4)', maxTicksLimit: 12 }, grid: { display: false } },
          y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,.4)', precision: 0 }, grid: { color: 'rgba(255,255,255,.06)' } },
        },
      },
    });
  }
})();
</script>
<?php endif; ?>
