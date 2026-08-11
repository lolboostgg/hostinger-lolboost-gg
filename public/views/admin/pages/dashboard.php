<?= $this->layout('admin/layouts/main', [
    'meta' => [
        'title' => 'Dashboard - Admin Area | LoLBoost.gg',
        'h1' => 'Performance & Sales Insights',
        'description' => 'A complete overview of your sales, service activity, and customer behavior in real-time.',
    ],
]) ?>



<?php
if (!function_exists('admin_dashboard_seller_balance_cents')) {
    function admin_dashboard_seller_balance_cents(): int
    {
        foreach (['get_sellers_balance_cents', 'get_seller_balance_cents', 'get_open_sellers_balance_cents'] as $fn) {
            if (function_exists($fn)) {
                return (int)$fn();
            }
        }

        foreach (['get_sellers_balance', 'get_seller_balance', 'get_open_sellers_balance'] as $fn) {
            if (function_exists($fn)) {
                $value = $fn();
                if (is_string($value)) {
                    $value = preg_replace('/[^0-9,.-]/', '', $value);
                    $value = str_replace(',', '.', $value);
                }
                return (int)round(((float)$value) * 100);
            }
        }

        $connections = [];
        foreach (['db', 'database', 'pdo', 'conn', 'mysqli'] as $name) {
            if (isset($GLOBALS[$name]) && is_object($GLOBALS[$name])) {
                $connections[] = $GLOBALS[$name];
            }
        }

        foreach ($connections as $db) {
            foreach (['sellers', 'seller'] as $table) {
                try {
                    if (method_exists($db, 'sum')) {
                        $sum = $db->sum($table, 'balance');
                        if ($sum !== null && $sum !== false) return (int)$sum;
                    }
                    if (method_exists($db, 'query')) {
                        $result = $db->query("SELECT COALESCE(SUM(balance),0) AS total_balance FROM `{$table}`");
                        if ($result instanceof PDOStatement) {
                            $row = $result->fetch(PDO::FETCH_ASSOC);
                            return (int)($row['total_balance'] ?? 0);
                        }
                        if ($result instanceof mysqli_result) {
                            $row = $result->fetch_assoc();
                            return (int)($row['total_balance'] ?? 0);
                        }
                    }
                } catch (Throwable $e) {
                    continue;
                }
            }
        }

        return 0;
    }
}

if (!function_exists('admin_dashboard_format_cents')) {
    function admin_dashboard_format_cents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}

if (!function_exists('admin_dashboard_money_to_cents')) {
    function admin_dashboard_money_to_cents($value): int
    {
        if (is_int($value)) {
            return $value * 100;
        }

        if (is_float($value)) {
            return (int)round($value * 100);
        }

        $normalized = preg_replace('/[^0-9,.-]/', '', (string)$value);
        if ($normalized === '' || $normalized === '-') {
            return 0;
        }

        $lastComma = strrpos($normalized, ',');
        $lastDot = strrpos($normalized, '.');

        if ($lastComma !== false && $lastDot !== false) {
            $decimalSep = $lastComma > $lastDot ? ',' : '.';
            $thousandSep = $decimalSep === ',' ? '.' : ',';
            $normalized = str_replace($thousandSep, '', $normalized);
            $normalized = str_replace($decimalSep, '.', $normalized);
        } elseif ($lastComma !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace(',', '', $normalized);
        }

        return (int)round(((float)$normalized) * 100);
    }
}

$sellerOpenBalanceCents = admin_dashboard_seller_balance_cents();
$boosterOpenBalance = get_boosters_balance();
$boosterOpenBalanceCents = admin_dashboard_money_to_cents($boosterOpenBalance);
$totalOpenBalanceCents = $boosterOpenBalanceCents + $sellerOpenBalanceCents;
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
/* ===== Dashboard V2 (Modern, theme-matched) — scoped ===== */
.dash-v2{
  --r: 14px;
  --b1: rgba(255,255,255,.10);
  --b2: rgba(255,255,255,.06);
  --t1: rgba(255,255,255,.92);
  --t2: rgba(255,255,255,.72);
}

/* Controls */
.dash-v2 .dash-controls{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
  margin: .25rem 0 1rem;
}
.dash-v2 .dash-pill{
  display:flex;
  align-items:center;
  gap:.65rem;
  padding:.55rem .75rem;
  border-radius:999px;
  border:1px solid var(--b2);
  background: rgba(255,255,255,.03);
}
.dash-v2 .dash-pill .label{
  display:inline-flex;
  align-items:center;
  gap:.45rem;
  font-weight:650;
  font-size:.9rem;
  color: var(--t2);
  white-space:nowrap;
}
.dash-v2 .dash-pill .btn{ border-radius:999px; }

/* Card base (keep admin colors, just modern polish) */
.dash-v2 .dash-card{
  position:relative;
  border-radius:var(--r);
  border:1px solid var(--b2);
  overflow:hidden;
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.dash-v2 .dash-card:hover{
  transform: translateY(-2px);
  border-color: rgba(255,255,255,.10);
  box-shadow: 0 18px 40px rgba(0,0,0,.35);
}
.dash-v2 .card-header{
  background: transparent !important;
  border-bottom: 1px solid var(--b2) !important;
  padding: .85rem 1rem;
}
.dash-v2 .card-header-title{
  font-size: .98rem;
  font-weight: 750;
  letter-spacing: .2px;
  margin:0;
  color: var(--t1);
}
.dash-v2 .card-body{ padding: 1rem; }

/* KPI cards */
.dash-v2 .kpi{ isolation:isolate; }
.dash-v2 .kpi::before{
  content:"";
  position:absolute;
  inset:0;
  background:
    radial-gradient(80% 120% at 0% 0%, rgba(var(--accent-rgb, 13,110,253), .30), transparent 55%),
    radial-gradient(70% 110% at 100% 0%, rgba(var(--accent-rgb, 13,110,253), .18), transparent 60%);
  opacity:.55;
  z-index:0;
  pointer-events:none;
}
.dash-v2 .kpi::after{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(180deg, rgba(0,0,0,.10), transparent 40%);
  z-index:0;
  pointer-events:none;
}
.dash-v2 .kpi > *{ position:relative; z-index:1; }

.dash-v2 .kpi-title{
  font-size:.78rem;
  letter-spacing:.35px;
  text-transform:uppercase;
  color: var(--t2);
  margin:0;
}
.dash-v2 .kpi-value{
  font-size: 1.75rem;
  font-weight: 800;
  letter-spacing: .2px;
  color: var(--t1);
  margin:.35rem 0 .2rem;
}
.dash-v2 .kpi-sub{
  display:flex;
  align-items:center;
  gap:.5rem;
  flex-wrap:wrap;
  margin-top:.35rem;
  color: var(--t2);
}
.dash-v2 .kpi-icon{
  width:40px;
  height:40px;
  border-radius: 12px;
  display:grid;
  place-items:center;
  background: rgba(var(--accent-rgb, 13,110,253), .16);
  color: rgba(255,255,255,.92);
}
.dash-v2 .kpi-icon i{ font-size: 1.05rem; margin:0; }

.dash-v2 .kpi .card-body{
  min-height: 150px;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
}
.dash-v2 .kpi-note{
  margin-top:.55rem;
  font-size:.86rem;
  line-height:1.35;
  color: var(--t2);
}
.dash-v2 .kpi-note strong{ color:var(--t1); font-weight:750; }

.dash-v2 .margin-chip{
  display:inline-flex;
  align-items:center;
  gap:.45rem;
  width:max-content;
  margin-top:.7rem;
  padding:.38rem .58rem .38rem .42rem;
  border-radius:999px;
  border:1px solid rgba(var(--accent-rgb, 25,135,84), .28);
  background:linear-gradient(135deg, rgba(var(--accent-rgb, 25,135,84), .20), rgba(255,255,255,.035));
  box-shadow:inset 0 1px 0 rgba(255,255,255,.08), 0 10px 24px rgba(0,0,0,.16);
  color:var(--t1);
  line-height:1;
}
.dash-v2 .margin-chip-icon{
  width:22px;
  height:22px;
  border-radius:50%;
  display:grid;
  place-items:center;
  background:rgba(var(--accent-rgb, 25,135,84), .22);
  color:rgba(255,255,255,.90);
  font-size:.68rem;
}
.dash-v2 .margin-chip-label{
  color:var(--t2);
  font-size:.74rem;
  font-weight:750;
  letter-spacing:.25px;
  text-transform:uppercase;
}
.dash-v2 .margin-chip-value{
  color:var(--t1);
  font-size:.95rem;
  font-weight:900;
  letter-spacing:.1px;
}
.dash-v2 .kpi-meta-line{
  display:flex;
  align-items:center;
  gap:.4rem;
  flex-wrap:wrap;
  margin-top:.45rem;
  color:var(--t2);
}
.dash-v2 .kpi-meta-line .dot{
  width:5px;
  height:5px;
  border-radius:50%;
  background:rgba(var(--accent-rgb, 13,110,253), .95);
  box-shadow:0 0 0 4px rgba(var(--accent-rgb, 13,110,253), .12);
}
.dash-v2 .breakdown{
  margin-top:.7rem;
  display:flex;
  flex-direction:column;
  gap:.28rem;
}
.dash-v2 .breakdown-item{
  min-width:0;
  display:flex;
  align-items:center;
  gap:.35rem;
  color:var(--t2);
  font-size:.78rem;
  font-weight:720;
  line-height:1.25;
  white-space:nowrap;
}
.dash-v2 .breakdown-label{
  display:flex;
  align-items:center;
  gap:.38rem;
  color:var(--t2);
  min-width:0;
}
.dash-v2 .breakdown-dot{
  width:6px;
  height:6px;
  flex:0 0 6px;
  border-radius:50%;
  background:rgba(var(--accent-rgb, 13,110,253), .95);
  box-shadow:0 0 0 4px rgba(var(--accent-rgb, 13,110,253), .12);
}
.dash-v2 .breakdown-dot.is-seller{
  background:rgba(255,255,255,.82);
  box-shadow:0 0 0 4px rgba(255,255,255,.08);
}
.dash-v2 .breakdown-sep{
  color:rgba(255,255,255,.36);
  font-weight:700;
}
.dash-v2 .breakdown-value{
  color:var(--t1);
  font-size:.8rem;
  font-weight:850;
}
.dash-v2 .breakdown-hint{
  color:rgba(255,255,255,.62);
  font-size:.73rem;
  font-weight:700;
}
@media (max-width: 420px){
  .dash-v2 .breakdown-item{ flex-wrap:wrap; }
}
.dash-v2 .metric-card .prev-value{
  color:rgba(255,255,255,.65);
}
.dash-v2 #refund_rate_card .kpi-value{
  font-size:1.9rem;
}

/* Status list */
.dash-v2 .status-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding:.75rem .25rem;
  border-bottom:1px solid var(--b2);
}
.dash-v2 .status-row:last-child{ border-bottom:0; }
.dash-v2 .status-left{
  display:flex;
  align-items:center;
  gap:.75rem;
  min-width:0;
}
.dash-v2 .status-dot{
  width:36px;
  height:36px;
  border-radius: 12px;
  display:grid;
  place-items:center;
  background: rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.08);
}
.dash-v2 .status-label{ font-weight:650; color: var(--t1); line-height:1.1; }
.dash-v2 .status-hint{ font-size:.8rem; color: var(--t2); }
.dash-v2 .status-value{ font-weight:850; font-size:1.05rem; color: var(--t1); }
.dash-v2 .status-section-label{
  padding:.75rem .25rem .25rem;
  color:rgba(255,255,255,.55);
  font-size:.72rem;
  font-weight:800;
  letter-spacing:.35px;
  text-transform:uppercase;
}

/* Chart containers */
.dash-v2 .chart-wrap{ position:relative; width:100%; }
.dash-v2 .chart-muted{ font-size:.85rem; color: var(--t2); }

/* Loading overlay */
.dash-v2 .dash-card.loading{ pointer-events:none; }
.dash-v2 .dash-card.loading::before{
  content:"";
  position:absolute;
  inset:0;
  background: rgba(0,0,0,.28);
  backdrop-filter: blur(2px);
  z-index: 10;
}
.dash-v2 .dash-card.loading::after{
  content:"";
  position:absolute;
  top: 18px;
  right: 18px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 3px solid rgba(255,255,255,.22);
  border-top-color: rgba(255,255,255,.90);
  animation: dashSpin .85s linear infinite;
  z-index: 11;
}
@keyframes dashSpin { to { transform: rotate(360deg); } }

/* Small tweaks */
.dash-v2 .badge{ font-weight:700; }
.dash-v2 .chip{
  border:1px solid var(--b2);
  background: rgba(255,255,255,.03);
  color: var(--t2);
}
</style>
<?= $this->end() ?>

<div class="dash-v2">
  <div class="dash-controls">
    <div class="dash-pill">
      <span class="label"><i class="bi-calendar-week"></i> Date range</span>
      <button id="js-daterangepicker-predefined" class="btn btn-ghost-secondary btn-sm dropdown-toggle">
        <span class="js-daterangepicker-predefined-preview"></span>
      </button>
    </div>

    <div class="d-flex align-items-center gap-2">
      <span class="badge chip">Live</span>
      <span class="badge chip">Insights</span>
    </div>
  </div>

  <div class="row g-3">
    <!-- KPI Row -->
    <div class="col-12">
      <div class="row g-3">
        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100 metric-card" id="total_revenue" style="--accent-rgb: var(--bs-primary-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Total revenue</p>
                  <div class="current-value kpi-value">€0</div>
                  <div class="kpi-sub">
                    <span class="badge bg-soft-success text-success"><i class="fas fa-chart-line-up me-1"></i><span class="percentage">0%</span></span>
                    <span class="small prev-value">from €0</span>
                  </div>
                </div>
                <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100 metric-card" id="total_expense" style="--accent-rgb: var(--bs-danger-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Total expense</p>
                  <div class="current-value kpi-value">€0</div>
                  <div class="kpi-sub">
                    <span class="badge bg-soft-success text-success"><i class="fas fa-chart-line-up me-1"></i><span class="percentage">0%</span></span>
                    <span class="small prev-value">from €0</span>
                  </div>
                </div>
                <div class="kpi-icon"><i class="fas fa-wallet"></i></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100 metric-card" id="profit" style="--accent-rgb: var(--bs-success-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Profit</p>
                  <div class="current-value kpi-value">€0</div>
                  <div class="kpi-sub">
                    <span class="badge bg-soft-success text-success"><i class="fas fa-chart-line-up me-1"></i><span class="percentage">0%</span></span>
                    <span class="small prev-value">from €0</span>
                  </div>
                  <div class="margin-chip" id="profit_margin" aria-label="Profit margin">
                    <span class="margin-chip-icon"><i class="fas fa-percent"></i></span>
                    <span class="margin-chip-label">Margin</span>
                    <span class="margin-chip-value">0%</span>
                  </div>
                </div>
                <div class="kpi-icon"><i class="fas fa-coins"></i></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100" style="--accent-rgb: var(--bs-info-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Open balance</p>
                  <div class="kpi-value">€<?= admin_dashboard_format_cents($totalOpenBalanceCents) ?></div>
                  <div class="kpi-meta-line mt-2">
                    <span class="dot"></span>
                    <span>Booster balance: <strong>€<?= htmlspecialchars((string)$boosterOpenBalance, ENT_QUOTES, 'UTF-8') ?></strong></span>
                  </div>
                  <div class="kpi-meta-line">
                    <span class="dot"></span>
                    <span>Seller balance: <strong>€<?= admin_dashboard_format_cents($sellerOpenBalanceCents) ?></strong></span>
                  </div>
                </div>
                <div class="kpi-icon"><i class="fas fa-euro-sign"></i></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Secondary KPIs -->
    <div class="col-12">
      <div class="row g-3">
        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100 metric-card" id="boosting_revenue" style="--accent-rgb: var(--bs-primary-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Boosting revenue</p>
                  <div class="current-value kpi-value">€0</div>
                  <div class="kpi-sub">
                    <span class="badge bg-soft-success text-success"><i class="fas fa-chart-line-up me-1"></i><span class="percentage">0%</span></span>
                    <span class="small prev-value">from €0</span>
                  </div>
                </div>
                <div class="kpi-icon"><i class="fas fa-rocket"></i></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100 metric-card" id="account_sales_revenue" style="--accent-rgb: var(--bs-info-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Account sales</p>
                  <div class="current-value kpi-value">€0</div>
                  <div class="kpi-sub">
                    <span class="badge bg-soft-success text-success"><i class="fas fa-chart-line-up me-1"></i><span class="percentage">0%</span></span>
                    <span class="small prev-value">from €0</span>
                  </div>
                </div>
                <div class="kpi-icon"><i class="fas fa-user-shield"></i></div>
              </div>
              <div class="breakdown">
                <div class="breakdown-item">
                  <span class="breakdown-label"><span class="breakdown-dot"></span>Smurf accounts</span>
                  <span class="breakdown-sep">-</span>
                  <span class="breakdown-value" id="smurf_accounts_value">€0</span>
                  <span class="breakdown-sep">-</span>
                  <span class="breakdown-hint" id="smurf_accounts_meta">0 sold · 0%</span>
                </div>
                <div class="breakdown-item">
                  <span class="breakdown-label"><span class="breakdown-dot is-seller"></span>Seller fees</span>
                  <span class="breakdown-sep">-</span>
                  <span class="breakdown-value" id="seller_accounts_value">€0</span>
                  <span class="breakdown-sep">-</span>
                  <span class="breakdown-hint" id="seller_accounts_meta">0 sold · 0%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100 metric-card" id="tips_revenue" style="--accent-rgb: var(--bs-warning-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Tips received</p>
                  <div class="current-value kpi-value">€0</div>
                  <div class="kpi-sub">
                    <span class="badge bg-soft-success text-success"><i class="fas fa-chart-line-up me-1"></i><span class="percentage">0%</span></span>
                    <span class="small prev-value">from €0</span>
                  </div>
                  <div class="kpi-meta-line"><span class="dot"></span><span id="tips_platform_share">Platform share: €0.00</span></div>
                </div>
                <div class="kpi-icon"><i class="fas fa-gift"></i></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="card dash-card kpi h-100" id="refund_rate_card" style="--accent-rgb: var(--bs-danger-rgb);">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                  <p class="kpi-title">Refund rate</p>
                  <div class="kpi-value" id="refund_rate_value">0%</div>
                  <div class="kpi-meta-line"><span class="dot"></span><span id="refund_rate_note">0 refunds from 0 orders</span></div>
                </div>
                <div class="kpi-icon"><i class="fas fa-rotate-left"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="col-xl-8">
      <div class="card dash-card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-header-title">Orders overview</h2>
          <div class="d-flex gap-2">
            <span class="badge bg-primary">Current</span>
            <span class="badge bg-info">Previous</span>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
              <div class="chart-muted small">Weekly paid activity</div>
              <div class="h3 mb-0" id="orders_graph_title">0 Orders</div>
            </div>
          </div>
          <div class="chart-wrap" style="height: 26rem;">
            <canvas id="weekly_orders"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card dash-card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-header-title">Sales activity</h2>
          <span class="badge chip">Summary</span>
        </div>
        <div class="card-body">
          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-layer-group text-primary"></i></div>
              <div>
                <div class="status-label">Total sales</div>
                <div class="status-hint">Orders + account sales</div>
              </div>
            </div>
            <div class="status-value" id="total_sales_activity">0</div>
          </div>

          <div class="status-section-label">Boosting orders</div>

          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-boxes text-primary"></i></div>
              <div>
                <div class="status-label">Boosting orders</div>
                <div class="status-hint">Paid orders</div>
              </div>
            </div>
            <div class="status-value" id="total_orders">0</div>
          </div>

          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-check-circle text-success"></i></div>
              <div>
                <div class="status-label">Completed</div>
                <div class="status-hint">Successfully delivered</div>
              </div>
            </div>
            <div class="status-value" id="completed_orders">0</div>
          </div>

          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-pause-circle text-warning"></i></div>
              <div>
                <div class="status-label">Paused</div>
                <div class="status-hint">On hold</div>
              </div>
            </div>
            <div class="status-value" id="paused_orders">0</div>
          </div>

          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-spinner text-info"></i></div>
              <div>
                <div class="status-label">In progress</div>
                <div class="status-hint">Currently running</div>
              </div>
            </div>
            <div class="status-value" id="in_progress_orders">0</div>
          </div>

          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-undo-alt text-danger"></i></div>
              <div>
                <div class="status-label">Refunded</div>
                <div class="status-hint">Shown separately, not counted as revenue</div>
              </div>
            </div>
            <div class="status-value" id="refunded_orders">0</div>
          </div>

          <div class="status-section-label">Account sales</div>

          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-store text-secondary"></i></div>
              <div>
                <div class="status-label">Smurf accounts</div>
                <div class="status-hint">Normal account sales</div>
              </div>
            </div>
            <div class="status-value" id="smurf_account_sales">0</div>
          </div>

          <div class="status-row">
            <div class="status-left">
              <div class="status-dot"><i class="fas fa-user-shield text-info"></i></div>
              <div>
                <div class="status-label">Seller accounts</div>
                <div class="status-hint">Marketplace account sales</div>
              </div>
            </div>
            <div class="status-value" id="seller_account_sales">0</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payments + Customers -->
    <div class="col-xl-6">
      <div class="card dash-card h-100">
        <div class="card-header"><h2 class="card-header-title">Payments</h2></div>
        <div class="card-body">
          <div class="row g-3 align-items-center">
            <div class="col-md-7">
              <div class="chart-wrap" style="height: 18.5rem;"><canvas id="doughnut-chart"></canvas></div>
            </div>
            <div class="col-md-5">
              <div class="p-2">
                <div class="mb-3"><div class="chart-muted small">Orders completed</div><div class="h3 text-primary mb-0" id="orders_completed">0</div></div>
                <div class="mb-3"><div class="chart-muted small">Private orders</div><div class="h3 text-info mb-0" id="private_orders">0</div></div>
                <div><div class="chart-muted small">Fines</div><div class="h3 text-warning mb-0" id="fines">0</div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-6">
      <div class="card dash-card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-header-title">Customers</h2>
          <div class="d-flex gap-2"><span class="badge bg-primary">New</span><span class="badge bg-info">Old</span></div>
        </div>
        <div class="card-body">
          <div class="chart-wrap" style="height: 16rem;"><canvas id="customers_chart"></canvas></div>
          <div class="row text-center mt-4">
            <div class="col-6"><div class="chart-muted small">New customers</div><div class="h3 text-primary mb-0" id="new_customers">0</div></div>
            <div class="col-6"><div class="chart-muted small">Returning customers</div><div class="h3 text-info mb-0" id="old_customers">0</div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Full width charts -->
    <div class="col-12">
      <div class="card dash-card">
        <div class="card-header"><h2 class="card-header-title">Boost types revenue</h2></div>
        <div class="card-body"><div class="chart-wrap" style="height: 300px;"><canvas id="monthly_form_chart"></canvas></div></div>
      </div>
    </div>

    <div class="col-12">
      <div class="card dash-card">
        <div class="card-header"><h2 class="card-header-title">Monthly revenue trend</h2></div>
        <div class="card-body"><div class="chart-wrap" style="height: 350px;"><canvas id="updatingLineChart"></canvas></div></div>
      </div>
    </div>

  </div>
</div>


<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/chart.js/dist/chart.min.js"></script>
<script>
    (function () {
        function hexToRgb(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }

            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            return `${r}, ${g}, ${b}`;
        }

        $('.js-daterangepicker').daterangepicker();
        $('.js-daterangepicker-times').daterangepicker({
            timePicker: true,
            startDate: moment().startOf('hour'),
            endDate: moment().startOf('hour').add(32, 'hour'),
            locale: {
                format: 'M/DD hh:mm A'
            }
        });

        var start = moment().startOf('month');
        var end = moment().endOf('month');

        function cb(start, end) {
            $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(
                start.format('MMM D') + ' - ' + end.format('MMM D, YYYY')
            );

            loadDashboardData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        }

        $('#js-daterangepicker-predefined').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last 60 Days': [moment().subtract(59, 'days'), moment()],
                'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                    .endOf('month')
                ],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year')
                    .endOf('year')
                ]
            }
        }, cb);

        cb(start, end);

        function loadDashboardData(startDate, endDate) {
            $('.dash-card').addClass('loading');

            $.ajax({
                url: '<?= AJAX_URL ?>',
                method: 'POST',
                data: {
                    action: 'dashboard_data',
                    start_date: startDate,
                    end_date: endDate,
                    // Ensure backend can interpret the date range in the admin's local timezone
                    timezone: (Intl && Intl.DateTimeFormat) ? Intl.DateTimeFormat().resolvedOptions().timeZone : 'Europe/Berlin',
                    tz_offset_minutes: new Date().getTimezoneOffset()
                },
                success: function (response) {
                    try {
                        const data = JSON.parse(response);
                        updateCharts(data);
                        $('.dash-card').removeClass('loading');
                    } catch (e) {
                        console.error('Error parsing response', e);
                        $('.dash-card').removeClass('loading');
                    }
                },
                error: function (xhr) {
                    console.error('Error loading dashboard data');
                    $('.dash-card').removeClass('loading');
                }
            });
        }

        function updateCharts(data) {
            const revenueChart = Chart.getChart("updatingLineChart");
            const ordersChart = Chart.getChart("weekly_orders");
            const formsChart = Chart.getChart("monthly_form_chart");
            const doughnutChart = Chart.getChart("doughnut-chart");
            const customersChart = Chart.getChart("customers_chart");

            if (revenueChart) revenueChart.destroy();
            if (ordersChart) ordersChart.destroy();
            if (formsChart) formsChart.destroy();
            if (doughnutChart) doughnutChart.destroy();
            if (customersChart) customersChart.destroy();

            HSCore.components.HSChartJS.init(document.querySelector('#updatingLineChart'), {
                type: "line",
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov",
                        "Dec"
                    ],
                    datasets: [{
                        data: data.monthly_revenue.values,
                        backgroundColor: ["rgba(55, 125, 255, .5)", "rgba(255, 255, 255, .2)"],
                        borderColor: "#377dff",
                        borderWidth: 2,
                        pointRadius: 0,
                        hoverBorderColor: "#377dff",
                        pointBackgroundColor: "#377dff",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 0,
                        tension: 0.4
                    }]
                },
                options: {
                    scales: {
                        y: {
                            grid: {
                                color: "#2F3235",
                                drawBorder: false,
                                zeroLineColor: "#e7eaf3"
                            },
                            ticks: {
                                min: 0,
                                beginAtZero: true,
                                stepSize: Math.round(data.monthly_revenue.max / 100),
                                color: "#97a4af",
                                font: {
                                    family: "Open Sans, sans-serif"
                                },
                                padding: 10,
                                postfix: "€"
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: "#97a4af",
                                font: {
                                    size: 12,
                                    family: "Open Sans, sans-serif"
                                },
                                padding: 5
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            prefix: "€",
                            hasIndicator: true,
                            mode: "index",
                            intersect: false,
                            lineMode: true,
                            lineWithLineColor: "rgba(19, 33, 68, 0.075)"
                        }
                    },
                }
            });

            HSCore.components.HSChartJS.init(document.querySelector('#weekly_orders'), {
                type: "line",
                data: {
                    labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                    datasets: [{
                        data: data.weekly_orders.current.values,
                        backgroundColor: ["rgba(55, 125, 255, .5)", "rgba(55, 125, 255, .1)"],
                        borderColor: "#377dff",
                        borderWidth: 2,
                        pointRadius: 0,
                        hoverBorderColor: "#377dff",
                        pointBackgroundColor: "#377dff",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 0,
                        tension: 0.4
                    },
                    {
                        data: data.weekly_orders.previous.values,
                        backgroundColor: ["rgba(0, 201, 219, .5)", "rgba(0, 201, 219, .1)"],
                        borderColor: "#00c9db",
                        borderWidth: 2,
                        pointRadius: 0,
                        hoverBorderColor: "#00c9db",
                        pointBackgroundColor: "#00c9db",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 0,
                        tension: 0.4
                    }]
                },
                options: {
                    gradientPosition: { "y1": 200 },
                    scales: {
                        y: {
                            grid: {
                                color: "#e7eaf330",
                                drawBorder: false,
                                zeroLineColor: "#e7eaf330"
                            },
                            ticks: {
                                min: 0,
                                max: 100,
                                stepSize: 20,
                                color: "#97a4af",
                                font: {
                                    family: "Open Sans, sans-serif"
                                },
                                padding: 10,
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: "#97a4af",
                                font: {
                                    size: 12,
                                    family: "Open Sans, sans-serif"
                                },
                                padding: 5
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            hasIndicator: true,
                            mode: "index",
                            intersect: false,
                            lineMode: true,
                            lineWithLineColor: "rgba(19, 33, 68, 0.075)",
                            postfix: " Orders",
                            yearStamp: false
                        }
                    },
                    hover: {
                        mode: "nearest",
                        intersect: true
                    }
                }
            });

            HSCore.components.HSChartJS.init(document.querySelector('#monthly_form_chart'), {
                type: "line",
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov",
                        "Dec"
                    ],
                    datasets: data.monthly_forms.map(form => ({
                        label: form.name,
                        data: form.data,
                        backgroundColor: `rgba(${hexToRgb(form.color)}, 0.1)`,
                        borderColor: form.borderColor,
                        borderWidth: 2,
                        pointBackgroundColor: form.color,
                        pointBorderColor: "#fff",
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: form.color,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHitRadius: 30,
                        pointBorderWidth: 1,
                        tension: 0.3,
                        fill: true
                    }))
                },
                options: {
                    scales: {
                        y: {
                            grid: {
                                color: "#2F3235",
                                drawBorder: false,
                                zeroLineColor: "#e7eaf3"
                            },
                            ticks: {
                                beginAtZero: true,
                                stepSize: Math.round(data.monthly_revenue.max / 100),
                                fontSize: 12,
                                fontColor: "#97a4af",
                                fontFamily: "Open Sans, sans-serif",
                                padding: 10,
                                prefix: "€"
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    family: "Open Sans, sans-serif"
                                },
                                color: "#97a4af",
                                padding: 5
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            prefix: "€",
                            hasIndicator: true,
                            mode: "index",
                            intersect: false
                        }
                    }
                }
            });

            HSCore.components.HSChartJS.init(document.querySelector('#doughnut-chart'), {
                type: "doughnut",
                data: {
                    labels: ["Orders Completed", "Private Orders", "Fines"],
                    datasets: [{
                        data: [
                            data.payments_summary.order_completed,
                            data.payments_summary.private_orders,
                            data.payments_summary.fines
                        ],
                        backgroundColor: ["#377dff", "#00c9db", "#00c9a7"],
                        borderWidth: 5,
                        borderColor: "#34373a",
                        hoverBorderColor: "#34373a"
                    }]
                },
                options: {
                    cutout: "80%",
                    plugins: {
                        tooltip: {
                            postfix: "€",
                            hasIndicator: true,
                            mode: "index",
                            intersect: false
                        }
                    },
                    hover: {
                        mode: "nearest",
                        intersect: true
                    }
                }
            });

            HSCore.components.HSChartJS.init(document.querySelector('#customers_chart'), {
                type: "bar",
                data: {
                    labels: data.customers_summary.labels,
                    datasets: [
                        {
                            data: data.customers_summary.current,
                            label: "New Customers",
                            backgroundColor: "#377dff",
                            borderWidth: 0,
                            borderRadius: 4
                        },
                        {
                            data: data.customers_summary.previous,
                            label: "Old Customers",
                            backgroundColor: "#00c9db",
                            borderWidth: 0,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: "#97a4af",
                                fontSize: 12,
                                fontFamily: "Open Sans, sans-serif",
                                padding: 10
                            }
                        },
                        x: {
                            type: "category",
                            ticks: {
                                color: "#97a4af",
                                fontSize: 12,
                                fontFamily: "Open Sans, sans-serif",
                                padding: 5
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            hasIndicator: true,
                            mode: "index",
                            intersect: false,
                            yearStamp: false
                        }
                    }
                }
            });

            $('#orders_completed').text('€' + data.payments_summary.order_completed);
            $('#private_orders').text('€' + data.payments_summary.private_orders);
            $('#fines').text('€' + data.payments_summary.fines);

            // sum of array
            $('#new_customers').text(data.customers_summary.current.reduce((a, b) => a + b, 0));
            $('#old_customers').text(data.customers_summary.previous.reduce((a, b) => a + b, 0));

            $('#orders_graph_title').text(data.weekly_orders.current.sum + ' Orders');

            function rawMetric(metric) {
                if (!metric) return 0;
                if (metric.current_raw !== undefined && metric.current_raw !== null && metric.current_raw !== '') {
                    return Number(metric.current_raw) || 0;
                }
                if (metric.current !== undefined && metric.current !== null) {
                    const normalized = String(metric.current).replace(/[^0-9,.-]/g, '').replace(',', '.');
                    return Math.round((Number(normalized) || 0) * 100);
                }
                return 0;
            }

            function formatMoneyFromCents(cents) {
                cents = Number(cents || 0);
                return (cents / 100).toFixed(2);
            }

            function formatPercent(value) {
                value = Number(value || 0);
                return value.toFixed(1) + '%';
            }

            $('#total_revenue .current-value').text('€' + data.total_revenue.current);
            $('#total_revenue .prev-value').text('from €' + data.total_revenue.previous);
            $('#total_revenue .percentage').text(data.total_revenue.change + '%');
            data.total_revenue.is_up ?
                $('#total_revenue .badge').removeClass('bg-soft-danger text-danger').addClass(
                    'bg-soft-success text-success') :
                $('#total_revenue .badge').removeClass('bg-soft-success text-success').addClass(
                    'bg-soft-danger text-danger');

            $('#total_expense .current-value').text('€' + data.expenses.current);
            $('#total_expense .prev-value').text('from €' + data.expenses.previous);
            $('#total_expense .percentage').text(data.expenses.change + '%');
            data.expenses.is_up == false ?
                $('#total_expense .badge').removeClass('bg-soft-danger text-danger').addClass(
                    'bg-soft-success text-success') :
                $('#total_expense .badge').removeClass('bg-soft-success text-success').addClass(
                    'bg-soft-danger text-danger');

            function updateMoneyMetric(selector, metric, invertGood) {
                if (!metric) return;
                $(selector + ' .current-value').text('€' + metric.current);
                $(selector + ' .prev-value').text('from €' + metric.previous);
                $(selector + ' .percentage').text(metric.change + '%');

                const isGood = invertGood ? !metric.is_up : metric.is_up;
                isGood ?
                    $(selector + ' .badge').removeClass('bg-soft-danger text-danger').addClass(
                        'bg-soft-success text-success') :
                    $(selector + ' .badge').removeClass('bg-soft-success text-success').addClass(
                        'bg-soft-danger text-danger');
            }

            updateMoneyMetric('#boosting_revenue', data.boosting_revenue);
            updateMoneyMetric('#account_sales_revenue', data.account_sales_revenue);

            const accountRaw = rawMetric(data.account_sales_revenue);
            const smurfRaw = rawMetric(data.smurf_accounts_revenue);
            const sellerRaw = rawMetric(data.selling_accounts_fee_revenue);
            const smurfCount = Number(data.order_stats && data.order_stats.smurf_account_sales ? data.order_stats.smurf_account_sales : 0);
            const sellerCount = Number(data.order_stats && data.order_stats.seller_account_sales ? data.order_stats.seller_account_sales : 0);
            const smurfShare = accountRaw > 0 ? (smurfRaw / accountRaw) * 100 : 0;
            const sellerShare = accountRaw > 0 ? (sellerRaw / accountRaw) * 100 : 0;

            $('#smurf_accounts_value').text('€' + formatMoneyFromCents(smurfRaw));
            $('#seller_accounts_value').text('€' + formatMoneyFromCents(sellerRaw));
            $('#smurf_accounts_meta').text(smurfCount + ' sold · ' + formatPercent(smurfShare));
            $('#seller_accounts_meta').text(sellerCount + ' sold · ' + formatPercent(sellerShare));

            $('#tips_revenue .current-value').text('€' + data.tips_revenue.current);
            $('#tips_platform_share').text('Platform share: €' + formatMoneyFromCents(rawMetric(data.tips_revenue) * 0.2));
            $('#tips_revenue .prev-value').text('from €' + data.tips_revenue.previous);
            $('#tips_revenue .percentage').text(data.tips_revenue.change + '%');
            data.tips_revenue.is_up ?
                $('#tips_revenue .badge').removeClass('bg-soft-danger text-danger').addClass(
                    'bg-soft-success text-success') :
                $('#tips_revenue .badge').removeClass('bg-soft-success text-success').addClass(
                    'bg-soft-danger text-danger');

            $('#profit .current-value').text('€' + data.profit.current);
            $('#profit .prev-value').text('from €' + data.profit.previous);
            $('#profit .percentage').text(data.profit.change + '%');
            data.profit.is_up ?
                $('#profit .badge').removeClass('bg-soft-danger text-danger').addClass(
                    'bg-soft-success text-success') :
                $('#profit .badge').removeClass('bg-soft-success text-success').addClass(
                    'bg-soft-danger text-danger');

            const totalRevenueRaw = rawMetric(data.total_revenue);
            const profitRaw = rawMetric(data.profit);
            const margin = totalRevenueRaw > 0 ? (profitRaw / totalRevenueRaw) * 100 : 0;
            $('#profit_margin .margin-chip-value').text(formatPercent(margin));

            const totalOrders = Number(data.order_stats && data.order_stats.total_orders ? data.order_stats.total_orders : 0);
            const refundedOrders = Number(data.order_stats && data.order_stats.refunded_orders ? data.order_stats.refunded_orders : 0);
            const totalAccountSales = smurfCount + sellerCount;
            const refundBase = totalOrders + refundedOrders;
            const refundRate = refundBase > 0 ? (refundedOrders / refundBase) * 100 : 0;
            $('#refund_rate_value').text(formatPercent(refundRate));
            $('#refund_rate_note').text(refundedOrders + ' refunds from ' + refundBase + ' orders');
            $('#total_sales_activity').text(totalOrders + totalAccountSales);

            $('#total_orders').text(data.order_stats.total_orders == false ? '0' : data.order_stats.total_orders);
            $('#completed_orders').text(data.order_stats.completed_orders == false ? '0' : data.order_stats.completed_orders);
            $('#paused_orders').text(data.order_stats.paused_orders == false ? '0' : data.order_stats.paused_orders);
            $('#in_progress_orders').text(data.order_stats.in_progress_orders == false ? '0' : data.order_stats.in_progress_orders);
            $('#refunded_orders').text(data.order_stats.refunded_orders == false ? '0' : data.order_stats.refunded_orders);
            $('#smurf_account_sales').text(data.order_stats.smurf_account_sales == false ? '0' : data.order_stats.smurf_account_sales);
            $('#seller_account_sales').text(data.order_stats.seller_account_sales == false ? '0' : data.order_stats.seller_account_sales);
        }
    })();
</script>
<?= $this->end() ?>