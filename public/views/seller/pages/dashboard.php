<?php
require_once __DIR__ . '/_seller_rank.php';
// Seller Dashboard
// Variables from controller: $seller_data, $total, $sold, $pending, $earnings_total, $recent_payments
?>

<?= $this->layout('seller/layouts/main', [
  'meta' => [
    'title'       => 'Dashboard - Seller Area | LoLBoost.gg',
   
  ]
]) ?>

<?php
// ────────────────────────────────────────────
// Seller data helpers
// ────────────────────────────────────────────
$sellerId      = (int)($seller_data['id']         ?? 0);
$sellerName    = (string)($seller_data['username'] ?? 'Seller');
$sellerEmail   = (string)($seller_data['email']    ?? '');
$sellerIcon    = trim((string)($seller_data['icon'] ?? ''));
$balanceCents  = (int)($seller_data['balance']     ?? 0);
$balanceEuro   = number_format($balanceCents / 100, 2);
$earningsTotal = (int)($earnings_total ?? 0); // cents

$totalAccounts  = (int)($total   ?? 0);
$soldAccounts   = (int)($sold    ?? 0);
$listedAccounts = (int)($pending ?? 0);

// Include Items and Top Ups in seller overview KPIs.
$itemSoldCount = 0;
$itemListedCount = 0;
$topupSoldCount = 0;
$topupListedCount = 0;
try {
  global $db;
  if (isset($db) && $sellerId > 0) {
    $itemSoldCount = (int)$db->single("SELECT COALESCE(SUM(sold_count),0) FROM selling_items WHERE seller_id = {$sellerId}");
    $itemListedCount = (int)$db->single("SELECT COUNT(*) FROM selling_items WHERE seller_id = {$sellerId} AND COALESCE(active,1)=1");
    $topupSoldCount = (int)$db->single("SELECT COALESCE(SUM(sold_count),0) FROM selling_topups WHERE seller_id = {$sellerId}");
    $topupListedCount = (int)$db->single("SELECT COUNT(*) FROM selling_topups WHERE seller_id = {$sellerId} AND COALESCE(active,1)=1");
  }
} catch (Throwable $e) {}
$soldAllServices = $soldAccounts + $itemSoldCount + $topupSoldCount;
$listedAllServices = $listedAccounts + $itemListedCount + $topupListedCount;

$rankData       = seller_resolved_rank(is_array($seller_data ?? null) ? $seller_data : [], $soldAccounts);
$feePct         = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : [], $soldAccounts);
$earningsRate   = round(100 - $feePct, 2);

$isActive   = (int)($seller_data['is_active']  ?? 0) === 1;
$isBanned   = (int)($seller_data['is_banned']  ?? 0) === 1;
$onboarding = strtolower(trim((string)($seller_data['onboarding_status'] ?? 'pending')));
$isApproved = ($onboarding === 'approved');

if (!function_exists('seller_icon_url_dash')) {
  function seller_icon_url_dash($icon) {
    $icon = trim((string)$icon);
    if ($icon === '' || $icon === 'default.png') return '';
    if (preg_match('~^https?://~i', $icon)) return $icon;
    return rtrim(defined('SITE_URL') ? SITE_URL : BASE_URL, '/') . '/' . ltrim($icon, '/');
  }
}
$avatarSrc    = seller_icon_url_dash($sellerIcon);
$avatarLetter = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sellerName) ?: 'S', 0, 1));

// ────────────────────────────────────────────
// Staff / Admin list
// ────────────────────────────────────────────
if (!defined('ICON_URL')) {
  define('ICON_URL', 'https://lolboost.gg/public/uploads/icons');
}

$roleUi = [
  1 => ['label' => 'Admin',          'nameClass' => 'ov-name-red'],
  2 => ['label' => 'Owner',          'nameClass' => 'ov-name-red'],
  3 => ['label' => 'Head Support',   'nameClass' => 'ov-name-green'],
  4 => ['label' => 'Booster Helper', 'nameClass' => 'ov-name-lightgreen'],
];

// Support team is resolved dynamically by admin ID, so it always reflects the
// current username/icon/online-status from the admins table instead of a
// hardcoded name snapshot. Only these specific admin IDs are shown here.
$staffAdminIds = [
  2  => 2, // Ricardo, Owner
  3  => 2, // Kevin, Owner
  12 => 3, // Jan, Head Support
  23 => 4, // Sharlok, Support
  24 => 4, // nototakuu, Support
  51 => 1, // SKRILL, Admin
];

if (!function_exists('seller_dash_admin_icon_url')) {
  function seller_dash_admin_icon_url($icon) {
    $icon = trim((string)$icon);
    if ($icon === '' || $icon === 'default.png') return rtrim(ICON_URL, '/') . '/default.png';
    if (preg_match('~^https?://~i', $icon)) return $icon;
    return rtrim(ICON_URL, '/') . '/' . ltrim($icon, '/');
  }
}
if (!function_exists('seller_dash_is_blank')) {
  function seller_dash_is_blank($v) {
    return ($v === null || (is_string($v) && trim($v) === ''));
  }
}
if (!function_exists('seller_dash_admin_is_online')) {
  function seller_dash_admin_is_online(array $admin): bool {
    foreach (['is_online', 'online'] as $k) {
      if (array_key_exists($k, $admin)) {
        $v = $admin[$k];
        if (is_bool($v)) return $v;
        if (is_numeric($v)) return ((int)$v) === 1;
        if (is_string($v)) {
          $vv = strtolower(trim($v));
          if (in_array($vv, ['1','true','yes','online'], true)) return true;
          if (in_array($vv, ['0','false','no','offline'], true)) return false;
        }
      }
    }
    if (isset($admin['status']) && strtolower(trim((string)$admin['status'])) === 'online') return true;
    $now = time();
    foreach (['last_seen','last_activity','last_active','updated_at'] as $k) {
      if (!empty($admin[$k])) {
        $ts = is_numeric($admin[$k]) ? (int)$admin[$k] : strtotime((string)$admin[$k]);
        if ($ts && ($now - $ts) <= 600) return true;
      }
    }
    return false;
  }
}

$dbAdmins = db_get_rows('admins', []);
$dbAdmins = is_array($dbAdmins) ? $dbAdmins : [];
$admins = [];
foreach ($dbAdmins as $a) {
  $aid = (int)($a['id'] ?? 0);
  if ($aid <= 0 || !isset($staffAdminIds[$aid])) continue;
  $rid = (int)$staffAdminIds[$aid];
  if (!isset($roleUi[$rid])) continue;
  $a['role_id'] = $rid;
  $admins[] = $a;
}
$seen = [];
$admins = array_values(array_filter($admins, function($a) use (&$seen) {
  $k = (int)($a['id'] ?? 0);
  if ($k <= 0 || isset($seen[$k])) return false;
  $seen[$k] = true; return true;
}));

try {
  global $db;
  if (isset($db) && $db) {
    $adminIds = array_values(array_filter(array_map(function($a) { return (int)($a['id'] ?? 0); }, $admins), function($id) { return $id > 0; }));
    if (!empty($adminIds)) {
      $ph = implode(',', array_fill(0, count($adminIds), '?'));
      $rows = $db->run("SELECT admin_id, MAX(created_at) AS last_seen, (MAX(created_at) >= (NOW() - INTERVAL 10 MINUTE)) AS is_online FROM admin_session_logs WHERE admin_id IN ($ph) GROUP BY admin_id", ...$adminIds);
      $lsByAdmin = []; $onByAdmin = [];
      foreach ((array)$rows as $r) {
        $aid = (int)($r['admin_id'] ?? 0);
        if ($aid > 0 && !seller_dash_is_blank($r['last_seen'] ?? null)) { $lsByAdmin[$aid] = $r['last_seen']; $onByAdmin[$aid] = ((int)($r['is_online'] ?? 0) === 1); }
      }
      $rows2 = $db->run("SELECT admin_id, MAX(created_at) AS last_login, (MAX(created_at) >= (NOW() - INTERVAL 10 MINUTE)) AS is_online FROM admin_sessions WHERE admin_id IN ($ph) GROUP BY admin_id", ...$adminIds);
      $llByAdmin = []; $olByAdmin = [];
      foreach ((array)$rows2 as $r) {
        $aid = (int)($r['admin_id'] ?? 0);
        if ($aid > 0 && !seller_dash_is_blank($r['last_login'] ?? null)) { $llByAdmin[$aid] = $r['last_login']; $olByAdmin[$aid] = ((int)($r['is_online'] ?? 0) === 1); }
      }
      foreach ($admins as &$a) {
        $aid = (int)($a['id'] ?? 0); if ($aid <= 0) continue;
        if (isset($lsByAdmin[$aid])) { $a['last_seen'] = $lsByAdmin[$aid]; $a['is_online'] = $onByAdmin[$aid] ?? false; }
        elseif (isset($llByAdmin[$aid])) { $a['last_seen'] = $llByAdmin[$aid]; $a['is_online'] = $olByAdmin[$aid] ?? false; }
      }
      unset($a);
    }
  }
} catch (Throwable $e) {}

$rolePriority = [2 => 0, 1 => 1, 3 => 2, 4 => 3];
usort($admins, function($a, $b) use ($rolePriority) {
  $ao = seller_dash_admin_is_online($a) ? 1 : 0; $bo = seller_dash_admin_is_online($b) ? 1 : 0;
  if ($ao !== $bo) return $bo <=> $ao;
  $ap = $rolePriority[(int)($a['role_id'] ?? 999)] ?? 99; $bp = $rolePriority[(int)($b['role_id'] ?? 999)] ?? 99;
  if ($ap !== $bp) return $ap <=> $bp;
  return mb_strtolower($a['username'] ?? '') <=> mb_strtolower($b['username'] ?? '');
});
$adminsOnlineCount = count(array_filter($admins, function($a) { return seller_dash_admin_is_online($a); }));

if (!function_exists('seller_banner_url_dash')) {
  function seller_banner_url_dash($raw) {
    $raw = trim((string)$raw);
    if ($raw === '') return '';
    if (preg_match('~^https?://~i', $raw)) return $raw;
    return rtrim(defined('SITE_URL') ? SITE_URL : BASE_URL, '/') . '/' . ltrim($raw, '/');
  }
}
$bannerUrl      = seller_banner_url_dash($seller_data['banner'] ?? '');
$bannerPosition = trim((string)($seller_data['banner_position'] ?? '50% 50%'));
if (!preg_match('/^([\d.]+)%\s+([\d.]+)%$/', $bannerPosition)) $bannerPosition = '50% 50%';

$conversionPct    = $totalAccounts > 0 ? round(($soldAccounts / $totalAccounts) * 100, 1) : 0;
$unlistedAccounts = max(0, $totalAccounts - $soldAccounts - $listedAccounts);
$totalIn = 0; $totalOut = 0;
if (!empty($recent_payments)) {
  foreach ($recent_payments as $p) {
    $amt = (int)($p['amount_cents'] ?? 0);
    if ($amt >= 0) $totalIn += $amt; else $totalOut += abs($amt);
  }
}

if (!function_exists('seller_dash_payment_label')) {
  function seller_dash_payment_label($typeRaw, $noteRaw): string {
    $typeRaw = (string)$typeRaw;
    $noteRaw = trim((string)$noteRaw);

    if (in_array($typeRaw, ['digital_good_payout', 'dg_sale_payout'], true) || stripos($noteRaw, 'Digital Good #') !== false) {
      return 'Digital Good Sale';
    }
    if ($typeRaw === 'sale_payout' && stripos($noteRaw, 'Item #') !== false) {
      return 'Item Sale';
    }
    if (in_array($typeRaw, ['sale_payout', 'admin_account_payout'], true)) {
      return 'Account Sale';
    }
    if ($typeRaw === 'payout_withdrawal') {
      return 'Payout Withdrawal';
    }
    if (in_array($typeRaw, ['manual_adjustment', 'manual_credit', 'manual_debit'], true)) {
      return ucwords(str_replace('_', ' ', $typeRaw));
    }
    return ucwords(str_replace('_', ' ', $typeRaw ?: 'Payment'));
  }
}

// ────────────────────────────────────────────
// Sales & Revenue — daily series for the stats card (last 180 days)
// ────────────────────────────────────────────
$salesSeries = [];
try {
  global $db;
  if (isset($db) && $sellerId > 0) {
    $rows = $db->run(
      "SELECT DATE(created_at) AS d,
              COUNT(*) AS sales_count,
              COALESCE(SUM(amount_cents),0) AS revenue_cents
       FROM seller_payments
       WHERE seller_id = ?
         AND type IN ('sale_payout','admin_account_payout','digital_good_payout','dg_sale_payout')
         AND created_at >= DATE_SUB(CURDATE(), INTERVAL 179 DAY)
       GROUP BY DATE(created_at)
       ORDER BY d ASC",
      $sellerId
    ) ?: [];
    $byDate = [];
    foreach ($rows as $r) {
      $byDate[(string)$r['d']] = [
        'sales'   => (int)($r['sales_count'] ?? 0),
        'revenue' => (int)($r['revenue_cents'] ?? 0),
      ];
    }
    for ($i = 179; $i >= 0; $i--) {
      $d = date('Y-m-d', strtotime("-{$i} days"));
      $salesSeries[] = [
        'date'    => $d,
        'sales'   => $byDate[$d]['sales'] ?? 0,
        'revenue' => round(($byDate[$d]['revenue'] ?? 0) / 100, 2),
      ];
    }
  }
} catch (Throwable $e) {}
?>

<!-- ══ HERO BANNER ══ -->
<?php
// ── Bridge: map dashboard variables to shared hero variables ──
$seller_data['created_at'] = $seller_data['created_at'] ?? null;
$spageHideNav = true; // Dashboard has no profile/payout tabs
?>
<?php include __DIR__ . '/_shared.php'; ?>
<?php
// Re-use sdashHeroBanner ID for the reposition JS (dashboard uses sdashHeroBanner)
// The shared file uses id="spHeroBanner", so alias it via JS inline:
?>


<div class="seller-dashboard-v3">
  <div class="container-fluid" style="padding-left:0!important;padding-right:0!important;">

    <!-- ══ Stat strip ══ -->
    <div class="sd3-stats mb-3">
      <div class="sd3-stat">
        <div class="sd3-stat-icon sd3-stat-icon--purple"><i class="fa-duotone fa-wallet"></i></div>
        <div>
          <div class="sd3-stat-label">Available Balance</div>
          <div class="sd3-stat-value"><?= $balanceEuro ?> €</div>
        </div>
      </div>
      <div class="sd3-stat">
        <div class="sd3-stat-icon sd3-stat-icon--green"><i class="fa-duotone fa-sack-dollar"></i></div>
        <div>
          <div class="sd3-stat-label">Lifetime Earned</div>
          <div class="sd3-stat-value"><?= number_format($earningsTotal / 100, 2) ?> €</div>
        </div>
      </div>
      <div class="sd3-stat">
        <div class="sd3-stat-icon sd3-stat-icon--amber"><i class="fa-duotone fa-cart-shopping"></i></div>
        <div>
          <div class="sd3-stat-label">Total Sales</div>
          <div class="sd3-stat-value"><?= $soldAllServices ?></div>
        </div>
      </div>
      <div class="sd3-stat">
        <div class="sd3-stat-icon sd3-stat-icon--blue"><i class="fa-duotone fa-shop"></i></div>
        <div>
          <div class="sd3-stat-label">Listed Now</div>
          <div class="sd3-stat-value"><?= $listedAllServices ?></div>
        </div>
      </div>
    </div>

    <!-- ══ Sales & Revenue ══ -->
    <div class="card sd3-card mb-3">
      <div class="card-body p-4">
        <div class="sd3-section-top">
          <span class="sd3-section-title">Sales &amp; Revenue</span>
          <span class="sd3-chart-hint">Daily revenue — hover a point to see the sales count for that day.</span>
          <div class="sd3-range-tabs" id="sd3RangeTabs">
            <button type="button" class="sd3-range-tab" data-range="7">7D</button>
            <button type="button" class="sd3-range-tab is-active" data-range="30">30D</button>
            <button type="button" class="sd3-range-tab" data-range="90">90D</button>
            <button type="button" class="sd3-range-tab" data-range="180">All</button>
          </div>
        </div>

        <div class="sd3-mini-stats mt-3 mb-3">
          <div class="sd3-mini-stat">
            <div class="sd3-mini-label">Sales</div>
            <div class="sd3-mini-value" id="sd3Sales">0</div>
          </div>
          <div class="sd3-mini-stat">
            <div class="sd3-mini-label">Revenue</div>
            <div class="sd3-mini-value" id="sd3Revenue">€0.00</div>
          </div>
          <div class="sd3-mini-stat">
            <div class="sd3-mini-label">Avg. order value</div>
            <div class="sd3-mini-value" id="sd3Avg">€0.00</div>
          </div>
          <div class="sd3-mini-stat">
            <div class="sd3-mini-label">Most revenue</div>
            <div class="sd3-mini-value" id="sd3Best">—</div>
          </div>
        </div>

        <?php if (!empty(array_filter($salesSeries, fn($d) => $d['sales'] > 0))): ?>
          <div class="sd3-chart-wrap"><canvas id="sd3Chart"></canvas></div>
        <?php else: ?>
          <div class="sd3-empty-state mt-2">
            <i class="fa-duotone fa-chart-line"></i>
            <div class="sd3-empty-title">No sales data yet</div>
            <div class="sd3-empty-text">Your sales and revenue trend will appear here once you make your first sale.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ══ Activity + Quick Actions + Rank ══ -->
    <div class="row g-3 align-items-stretch">

      <div class="col-12 col-xl-7">
        <div class="card sd3-card h-100">
          <div class="card-body p-4">
            <div class="sd3-section-top">
              <span class="sd3-section-title">Recent activity</span>
              <a href="<?= BASE_URL ?>/seller-area/payments" class="sd3-link">View all</a>
            </div>

            <?php if (!empty($recent_payments)): ?>
              <div class="sd3-feed mt-3">
                <?php foreach ($recent_payments as $p):
                  $amtCents  = (int)($p['amount_cents'] ?? 0);
                  $isPos     = $amtCents >= 0;
                  $amtStr    = ($isPos ? '+' : '−') . '€' . number_format(abs($amtCents) / 100, 2);
                  $typeRaw   = (string)($p['type'] ?? 'payment');
                  $typeLabel = seller_dash_payment_label($typeRaw, $p['note'] ?? '');
                  $note      = htmlspecialchars(trim((string)($p['note'] ?? '')));
                  $dateStr   = !empty($p['created_at']) ? date('d.m.Y · H:i', strtotime($p['created_at'])) : '—';
                ?>
                  <div class="sd3-feed-item">
                    <div class="sd3-feed-dot <?= $isPos ? 'is-pos' : 'is-neg' ?>"></div>
                    <div class="sd3-feed-body">
                      <div class="sd3-feed-title"><?= $typeLabel ?></div>
                      <div class="sd3-feed-meta"><?= $dateStr ?><?= $note !== '' ? ' · ' . $note : '' ?></div>
                    </div>
                    <span class="sd3-feed-badge <?= $isPos ? 'is-pos' : 'is-neg' ?>"><?= $amtStr ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="sd3-empty-state mt-4">
                <i class="fa-duotone fa-inbox"></i>
                <div class="sd3-empty-title">No activity yet</div>
                <div class="sd3-empty-text">Your first sale or payout will appear here.</div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-5 d-flex flex-column gap-3">

        <div class="card sd3-card">
          <div class="card-body p-4">
            <div class="sd3-section-top mb-3">
              <span class="sd3-section-title">Quick actions</span>
            </div>
            <div class="sd3-actions">
              <a href="<?= BASE_URL ?>/seller-area/accounts#open-add-account" class="sd3-action is-primary">
                <div class="sd3-action-icon"><i class="fa-duotone fa-circle-plus"></i></div>
                <div>
                  <div class="sd3-action-title">List account</div>
                  <div class="sd3-action-text">Add to marketplace</div>
                </div>
              </a>
              <a href="<?= BASE_URL ?>/seller-area/payout" class="sd3-action">
                <div class="sd3-action-icon"><i class="fa-duotone fa-money-check-dollar"></i></div>
                <div>
                  <div class="sd3-action-title">Request payout</div>
                  <div class="sd3-action-text">Withdraw balance</div>
                </div>
              </a>
              <a href="<?= BASE_URL ?>/seller-area/accounts" class="sd3-action">
                <div class="sd3-action-icon"><i class="fa-duotone fa-store"></i></div>
                <div>
                  <div class="sd3-action-title">My listings</div>
                  <div class="sd3-action-text">Manage accounts</div>
                </div>
              </a>
              <a href="<?= BASE_URL ?>/seller-area/profile" class="sd3-action">
                <div class="sd3-action-icon"><i class="fa-duotone fa-user-gear"></i></div>
                <div>
                  <div class="sd3-action-title">Edit profile</div>
                  <div class="sd3-action-text">Avatar &amp; banner</div>
                </div>
              </a>
            </div>
          </div>
        </div>

        <div class="card sd3-card flex-grow-1">
          <div class="card-body p-4">
            <div class="sd3-section-top mb-3">
              <span class="sd3-section-title">Seller rank</span>
              <a href="<?= BASE_URL ?>/seller-area/payments" class="sd3-link">History</a>
            </div>

            <?php $dRank = $rankData; ?>

            <div class="sd3-rank-badge" style="border-color:<?= $dRank['color'] ?>;background:<?= $dRank['color'] ?>1a;">
              <i class="<?= htmlspecialchars($dRank['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?>" style="color:<?= htmlspecialchars($dRank['color'] ?? '#94a3b8', ENT_QUOTES) ?>;font-size:1.6rem;"></i>
              <div>
                <div style="font-size:1.2rem;font-weight:950;color:<?= htmlspecialchars($dRank['color'] ?? '#94a3b8', ENT_QUOTES) ?>;"><?= htmlspecialchars($dRank['label'] ?? 'Beginner', ENT_QUOTES) ?></div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.4);margin-top:1px;"><?= (int)($dRank['sales'] ?? 0) ?> total sales · <?= number_format((float)($dRank['fee_percent'] ?? 0), 0) ?>% fee</div>
              </div>
            </div>

            <?php if ($dRank['next'] !== null): ?>
            <div class="sd3-divider"></div>
            <?php
            $nextRankData = seller_rank_from_sales((int)$dRank['next_sales']);
            $pct = (int)round((float)($dRank['progress_percent'] ?? 100));
            ?>
            <div style="font-size:.72rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.09em;margin-bottom:10px;">
              Progress to <?= $dRank['next'] ?>
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
              <div style="flex:1;height:8px;border-radius:99px;background:rgba(255,255,255,.08);overflow:hidden;">
                <div style="height:100%;width:<?= $pct ?>%;border-radius:99px;background:linear-gradient(90deg,<?= $dRank['color'] ?>,<?= $nextRankData['color'] ?>);transition:width .4s;"></div>
              </div>
              <span style="font-size:.78rem;font-weight:800;color:rgba(255,255,255,.5);white-space:nowrap;"><?= $pct ?>%</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.74rem;color:rgba(255,255,255,.35);">
              <span><?= (int)$dRank['sales'] ?> sales</span>
              <span><?= (int)$dRank['next_sales'] ?> needed</span>
            </div>
            <div style="margin-top:10px;font-size:.76rem;color:rgba(255,255,255,.45);">
              <?= (int)$dRank['sales_to_next'] ?> more sales to unlock <?= htmlspecialchars($dRank['next'], ENT_QUOTES) ?> and <?= number_format((float)$nextRankData['fee_percent'], 0) ?>% fee.
            </div>
            <?php else: ?>
            <div class="sd3-divider"></div>
            <div style="text-align:center;padding:8px 0;font-size:.84rem;color:rgba(255,255,255,.45);">You have reached the highest seller rank! <span style="color:#ec4899;">&#x1F451;</span></div>
            <?php endif; ?>

            <div class="sd3-divider"></div>
            <div style="display:flex;justify-content:space-between;gap:4px;">
              <?php
              $rankMeta = seller_rank_rules();
              foreach ($rankMeta as $rm):
                $isActive  = ($rm['label'] === $dRank['label']);
                $isUnlocked= ($dRank['sales'] >= (int)$rm['min_sales']);
              ?>
              <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;opacity:<?= $isUnlocked ? '1' : '.3' ?>;">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;background:<?= $isActive ? $rm['color'] . '33' : 'rgba(255,255,255,.05)' ?>;border:1px solid <?= $isActive ? $rm['color'] : 'rgba(255,255,255,.1)' ?>;">
                  <i class="<?= htmlspecialchars($rm['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?>" style="color:<?= $isUnlocked ? htmlspecialchars($rm['color'] ?? '#94a3b8', ENT_QUOTES) : 'rgba(255,255,255,.3)' ?>;"></i>
                </div>
                <span style="font-size:.6rem;font-weight:800;color:<?= $isActive ? $rm['color'] : 'rgba(255,255,255,.3)' ?>;text-align:center;"><?= $rm['label'] ?></span>
                <span style="font-size:.56rem;color:rgba(255,255,255,.28);text-align:center;"><?= (int)$rm['min_sales'] ?>+ sales</span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ Support Team + Recent Payments ══ -->
    <div class="row g-3 sd3-section-gap">

      <div class="col-12 col-xl-6">
        <div class="card sd3-card h-100">
          <div class="card-body p-4">
            <div class="sd3-section-top">
              <span class="sd3-section-title">Support team</span>
              <a class="sd3-link" href="https://discord.com/channels/926928301807771708/1207383976239702087" target="_blank" rel="noopener">Open ticket</a>
            </div>
            <div class="sd3-muted-note mt-1 mb-3">
              <span class="sd3-online-pulse"></span>
              <?= (int)$adminsOnlineCount ?> member<?= $adminsOnlineCount === 1 ? '' : 's' ?> online right now
            </div>
            <div class="sd3-support-list">
              <?php foreach ($admins as $a):
                $rid      = (int)($a['role_id'] ?? 0);
                $ui       = $roleUi[$rid] ?? ['label' => 'Support', 'nameClass' => ''];
                $name     = htmlspecialchars((string)($a['username'] ?? 'Admin'));
                $icon     = seller_dash_admin_icon_url($a['icon'] ?? '');
                $isOnline = seller_dash_admin_is_online($a);
              ?>
                <div class="sd3-support-row <?= $isOnline ? 'is-online' : 'is-offline' ?>">
                  <div class="sd3-support-left">
                    <img src="<?= htmlspecialchars($icon) ?>" alt="<?= $name ?>" class="sd3-support-avatar">
                    <div>
                      <div class="sd3-support-name <?= htmlspecialchars($ui['nameClass']) ?>"><?= $name ?></div>
                      <div class="sd3-support-meta"><?= htmlspecialchars($ui['label']) ?>, <?= $isOnline ? 'Online' : 'Offline' ?></div>
                    </div>
                  </div>
                  <a class="sd3-support-btn <?= $isOnline ? '' : 'is-disabled' ?>"
                     href="<?= $isOnline ? 'https://discord.com/channels/926928301807771708/1207383976239702087' : '#' ?>"
                     <?= $isOnline ? 'target="_blank" rel="noopener"' : 'tabindex="-1" aria-disabled="true"' ?>>
                    <i class="fa-brands fa-discord"></i>
                  </a>
                </div>
              <?php endforeach; ?>
              <?php if (empty($admins)): ?>
                <div class="sd3-empty-inline">No support members available right now.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-6">
        <div class="card sd3-card h-100">
          <div class="card-body p-4">
            <div class="sd3-section-top">
              <span class="sd3-section-title">Recent payments</span>
              <a class="sd3-link" href="<?= BASE_URL ?>/seller-area/payments">View all</a>
            </div>

            <?php if (!empty($recent_payments)): ?>
              <div class="sd3-payments-list mt-3">
                <?php foreach ($recent_payments as $p):
                  $amtCents  = (int)($p['amount_cents'] ?? 0);
                  $isPos     = $amtCents >= 0;
                  $amtStr    = ($isPos ? '+' : '−') . '€' . number_format(abs($amtCents) / 100, 2);
                  $typeRaw   = (string)($p['type'] ?? 'payment');
                  $typeLabel = seller_dash_payment_label($typeRaw, $p['note'] ?? '');
                  $note      = htmlspecialchars((string)($p['note'] ?? ''));
                  $dateStr   = !empty($p['created_at']) ? date('d.m.Y', strtotime($p['created_at'])) : '—';
                ?>
                  <div class="sd3-payment-row">
                    <div class="sd3-payment-left">
                      <div class="sd3-payment-icon <?= $isPos ? 'is-pos' : 'is-neg' ?>">
                        <i class="fa-duotone <?= $isPos ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?>"></i>
                      </div>
                      <div>
                        <div class="sd3-payment-title"><?= $typeLabel ?></div>
                        <div class="sd3-payment-note"><?= $note !== '' ? $note : 'Payment activity on your seller account.' ?></div>
                      </div>
                    </div>
                    <div class="sd3-payment-right">
                      <div class="sd3-payment-amount <?= $isPos ? 'is-pos' : 'is-neg' ?>"><?= $amtStr ?></div>
                      <div class="sd3-payment-date"><?= $dateStr ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="sd3-divider"></div>
              <div class="sd3-pay-totals">
                <div class="sd3-pay-total is-in">
                  <div class="sd3-pay-total-label">Total in</div>
                  <div class="sd3-pay-total-val">+€<?= number_format($totalIn / 100, 2) ?></div>
                </div>
                <div class="sd3-pay-total is-out">
                  <div class="sd3-pay-total-label">Total out</div>
                  <div class="sd3-pay-total-val">−€<?= number_format($totalOut / 100, 2) ?></div>
                </div>
              </div>
            <?php else: ?>
              <div class="sd3-empty-state mt-4">
                <i class="fa-duotone fa-inbox"></i>
                <div class="sd3-empty-title">No payments yet</div>
                <div class="sd3-empty-text">Your first sale or payout activity will appear here.</div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>


<style>
/* ════════════════════════════════════════════
   HERO BANNER (1:1 original)
════════════════════════════════════════════ */
.sdash-hero-card { background:var(--bs-card-bg) !important; }
.sdash-hero-banner {
  min-height:160px; background-color:#111a44;
  background-image:radial-gradient(circle at 8% 0%,rgba(255,255,255,.10),transparent 22%),radial-gradient(circle at 92% 100%,rgba(56,189,248,.12),transparent 26%),linear-gradient(90deg,#111a44 0%,#312e81 48%,#0f172a 100%);
  background-size:cover; background-position:center; position:relative; overflow:hidden;
}
.sdash-hero-banner-glow { position:absolute;inset:0;background:linear-gradient(90deg,rgba(129,140,248,.18),rgba(139,92,246,.12),rgba(14,165,233,.12));mix-blend-mode:screen;pointer-events:none; }
.sdash-hero-banner-noise { position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,14,29,.0),rgba(10,14,29,.28) 100%);pointer-events:none; }
.sdash-banner-edit-btn { position:absolute;bottom:12px;right:14px;display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;font-size:.8rem;font-weight:800;background:rgba(0,0,0,.45);border:1px solid rgba(255,255,255,.18);border-radius:8px;color:rgba(255,255,255,.85);cursor:pointer;backdrop-filter:blur(8px);transition:background .15s,border-color .15s,color .15s; }
.sdash-banner-edit-btn:hover { background:rgba(109,92,255,.35);border-color:rgba(109,92,255,.55);color:#fff; }
.sdash-hero-body { background:var(--bs-card-bg); }
.sdash-avatar-wrap { position:relative;cursor:pointer;display:inline-block;flex-shrink:0; }
.sdash-avatar { width:72px;height:72px;border-radius:50%;border:3px solid var(--bs-card-bg,#1e2028);background:linear-gradient(135deg,#2c3450,#22283a);display:flex;align-items:center;justify-content:center;font-size:1.7rem;font-weight:800;color:#60a5fa;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.30);transition:transform .12s ease; }
.sdash-avatar-wrap:hover .sdash-avatar { transform:translateY(-2px); }
.sdash-avatar img { width:100%;height:100%;object-fit:cover;border-radius:50%; }
.sdash-avatar-pen { position:absolute;bottom:0;right:0;width:22px;height:22px;border-radius:50%;background:#35383a;border:2px solid var(--bs-card-bg,#1e2028);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.6rem;pointer-events:none; }
.sdash-hero-name { font-size:1.2rem;font-weight:950;color:rgba(255,255,255,.92); }
.sdash-hero-sub  { font-size:.82rem;color:rgba(255,255,255,.55); }
.sdash-stat-badge { display:inline-flex;flex-direction:column;align-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:12px;padding:.55rem .9rem;min-width:78px; }
.sdash-stat-val { font-size:1rem;font-weight:900;line-height:1.2;color:rgba(255,255,255,.92); }
.sdash-stat-lbl { font-size:.67rem;font-weight:700;color:rgba(255,255,255,.50);text-transform:uppercase;letter-spacing:.06em;margin-top:.2rem; }
.sdash-chip { display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.92);font-weight:900;font-size:.82rem; }
.sdash-chip--accent  { background:linear-gradient(135deg,rgba(109,92,255,.22),rgba(176,92,255,.14));border-color:rgba(109,92,255,.35); }
.sdash-chip--danger  { background:rgba(255,82,163,.10);border-color:rgba(255,82,163,.28); }
.sdash-chip--warning { background:rgba(251,191,36,.10);border-color:rgba(251,191,36,.28); }
/* Seller ID badge next to name */
.sdash-id-badge { display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:99px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);font-size:.72rem;font-weight:800;color:rgba(255,255,255,.45);margin-left:8px;vertical-align:middle; }


/* ════════════════════════════════════════════
   SELLER DASHBOARD V3 — rebuilt to match the
   Analytics page's visual language
════════════════════════════════════════════ */
.seller-dashboard-v3 { --sd3-text:rgba(255,255,255,.94); --sd3-muted:rgba(255,255,255,.55); }
.seller-dashboard-v3 .sd3-card { background:var(--bs-card-bg) !important; border:var(--bs-card-border-width) solid var(--bs-card-border-color) !important; border-radius:22px !important; box-shadow:none !important; }
.seller-dashboard-v3 .sd3-card::before { display:none !important; content:none !important; }
.seller-dashboard-v3 .sd3-section-gap { margin-top:16px; }
.seller-dashboard-v3 .sd3-section-top { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.seller-dashboard-v3 .sd3-section-title { color:var(--sd3-text); font-weight:900; font-size:.88rem; }
.seller-dashboard-v3 .sd3-link { color:rgba(255,255,255,.84); text-decoration:none; font-weight:800; font-size:.84rem; }
.seller-dashboard-v3 .sd3-link:hover { color:#fff; }
.seller-dashboard-v3 .sd3-muted-note  { color:var(--sd3-muted); font-size:.84rem; display:flex; align-items:center; gap:6px; }
.seller-dashboard-v3 .sd3-divider     { height:1px; background:rgba(255,255,255,.07); margin:18px 0; }

/* Stat strip */
.sd3-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
@media(max-width:900px) { .sd3-stats { grid-template-columns:repeat(2,1fr); } }
.sd3-stat { display:flex; align-items:center; gap:14px; padding:16px 18px; border-radius:18px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sd3-stat-icon { width:44px; height:44px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#fff; }
.sd3-stat-icon--purple { background:linear-gradient(135deg,#6d5cff,#b05cff); }
.sd3-stat-icon--green  { background:linear-gradient(135deg,#22c55e,#4ade80); }
.sd3-stat-icon--amber  { background:linear-gradient(135deg,#f59e0b,#fbbf24); }
.sd3-stat-icon--blue   { background:linear-gradient(135deg,#0ea5e9,#38bdf8); }
.sd3-stat-label { color:var(--sd3-muted); font-size:.76rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
.sd3-stat-value { color:var(--sd3-text); font-size:1.5rem; font-weight:900; margin-top:4px; line-height:1; }

/* Sales & Revenue — range tabs + mini stats (shared look with Analytics) */
.sd3-range-tabs { display:flex; gap:6px; flex-shrink:0; }
.sd3-range-tab { border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.03); color:var(--sd3-muted); font-weight:800; font-size:.76rem; padding:6px 12px; border-radius:10px; cursor:pointer; transition:.15s ease; }
.sd3-range-tab:hover { border-color:rgba(109,92,255,.35); color:#fff; }
.sd3-range-tab.is-active { background:linear-gradient(135deg,rgba(109,92,255,.30),rgba(176,92,255,.18)); border-color:rgba(109,92,255,.45); color:#fff; }
.sd3-mini-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
@media(max-width:767.98px) { .sd3-mini-stats { grid-template-columns:repeat(2,1fr); } }
.sd3-mini-stat { padding:14px 16px; border-radius:14px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sd3-mini-label { font-size:.72rem; font-weight:800; color:var(--sd3-muted); text-transform:uppercase; letter-spacing:.08em; }
.sd3-mini-value { font-size:1.25rem; font-weight:900; color:var(--sd3-text); margin-top:6px; }
.sd3-chart-hint { flex:1 1 100%; order:3; color:rgba(255,255,255,.38); font-size:.74rem; font-weight:600; margin-top:4px; }
.sd3-chart-wrap { position:relative; width:100%; height:280px; }
@media(max-width:600px) { .sd3-chart-wrap { height:220px; } }

/* Activity feed */
.sd3-feed { display:grid; gap:12px; }
.sd3-feed-item { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; border:1px solid rgba(255,255,255,.08); border-radius:16px; background:rgba(255,255,255,.02); }
.sd3-feed-dot  { width:9px; height:9px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.sd3-feed-dot.is-pos { background:#4ade80; box-shadow:0 0 7px rgba(74,222,128,.5); }
.sd3-feed-dot.is-neg { background:#fb7185; box-shadow:0 0 7px rgba(251,113,133,.4); }
.sd3-feed-body  { flex:1; min-width:0; }
.sd3-feed-title { font-size:.86rem; font-weight:900; color:var(--sd3-text); line-height:1.3; }
.sd3-feed-meta  { font-size:.76rem; color:var(--sd3-muted); margin-top:3px; }
.sd3-feed-badge { flex-shrink:0; font-size:.76rem; font-weight:900; padding:5px 10px; border-radius:10px; white-space:nowrap; }
.sd3-feed-badge.is-pos { background:rgba(74,222,128,.10); border:1px solid rgba(74,222,128,.22); color:#4ade80; }
.sd3-feed-badge.is-neg { background:rgba(251,113,133,.10); border:1px solid rgba(251,113,133,.22); color:#fb7185; }

/* Quick actions */
.sd3-actions { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.sd3-action { display:flex; gap:14px; align-items:flex-start; text-decoration:none; color:inherit; padding:18px; border-radius:18px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); transition:.18s ease; }
.sd3-action:hover { transform:translateY(-2px); border-color:rgba(109,92,255,.30); box-shadow:0 18px 36px rgba(109,92,255,.10); }
.sd3-action.is-primary { background:linear-gradient(135deg,rgba(109,92,255,.18),rgba(255,255,255,.02)); border-color:rgba(109,92,255,.26); }
.sd3-action-icon { width:44px; height:44px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,rgba(109,92,255,.26),rgba(176,92,255,.16)); border:1px solid rgba(109,92,255,.22); color:#fff; font-size:1rem; }
.sd3-action-title { color:var(--sd3-text); font-weight:900; font-size:.88rem; }
.sd3-action-text  { color:var(--sd3-muted); margin-top:4px; font-size:.82rem; }

/* Rank badge */
.sd3-rank-badge { display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:14px;border:1px solid;margin-bottom:4px; }

/* Online pulse */
.sd3-online-pulse { display:inline-block; width:8px; height:8px; border-radius:50%; background:#4ade80; flex-shrink:0; box-shadow:0 0 0 0 rgba(74,222,128,.5); animation:sd3-pulse 2s infinite; }
@keyframes sd3-pulse { 0%{box-shadow:0 0 0 0 rgba(74,222,128,.5)} 70%{box-shadow:0 0 0 6px rgba(74,222,128,0)} 100%{box-shadow:0 0 0 0 rgba(74,222,128,0)} }

/* Support list */
.sd3-support-list { display:grid; gap:12px; max-height:540px; overflow:auto; padding-right:2px; }
.sd3-support-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-radius:16px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sd3-support-row.is-online  { border-color:rgba(74,222,128,.16); }
.sd3-support-row.is-offline { opacity:.78; }
.sd3-support-left   { display:flex; align-items:center; gap:12px; min-width:0; }
.sd3-support-avatar { width:42px; height:42px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.12); }
.sd3-support-name   { color:var(--sd3-text); font-weight:900; font-size:.88rem; }
.sd3-support-meta   { color:var(--sd3-muted); font-size:.78rem; margin-top:2px; }
.sd3-support-btn { width:40px; height:40px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; text-decoration:none; color:#fff; background:linear-gradient(135deg,rgba(109,92,255,.26),rgba(176,92,255,.16)); border:1px solid rgba(109,92,255,.22); }
.sd3-support-btn.is-disabled { opacity:.38; pointer-events:none; }
.ov-name-red        { color:#f87171 !important; }
.ov-name-green      { color:#34d399 !important; }
.ov-name-lightgreen { color:#6ee7b7 !important; }

/* Payments */
.sd3-payments-list { display:grid; gap:12px; }
.sd3-payment-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-radius:16px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sd3-payment-left { display:flex; align-items:center; gap:12px; min-width:0; }
.sd3-payment-icon { width:44px; height:44px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,rgba(109,92,255,.26),rgba(176,92,255,.16)); border:1px solid rgba(109,92,255,.22); color:#fff; font-size:1rem; }
.sd3-payment-icon.is-pos { border-color:rgba(74,222,128,.22); background:rgba(74,222,128,.12); }
.sd3-payment-icon.is-neg { border-color:rgba(251,113,133,.22); background:rgba(251,113,133,.12); }
.sd3-payment-title  { color:var(--sd3-text); font-weight:900; font-size:.88rem; }
.sd3-payment-note   { color:var(--sd3-muted); font-size:.8rem; margin-top:2px; }
.sd3-payment-right  { text-align:right; flex-shrink:0; }
.sd3-payment-amount { font-weight:900; font-size:.92rem; }
.sd3-payment-amount.is-pos { color:#4ade80; }
.sd3-payment-amount.is-neg { color:#fb7185; }
.sd3-payment-date   { color:var(--sd3-muted); font-size:.82rem; margin-top:4px; }

/* Payment totals */
.sd3-pay-totals { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.sd3-pay-total  { padding:14px 16px; border-radius:16px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sd3-pay-total.is-in  { background:rgba(74,222,128,.04);  border-color:rgba(74,222,128,.14); }
.sd3-pay-total.is-out { background:rgba(251,113,133,.04); border-color:rgba(251,113,133,.14); }
.sd3-pay-total-label { font-size:.72rem; font-weight:800; color:var(--sd3-muted); text-transform:uppercase; letter-spacing:.09em; }
.sd3-pay-total-val   { font-size:1.15rem; font-weight:900; margin-top:6px; }
.sd3-pay-total.is-in  .sd3-pay-total-val { color:#4ade80; }
.sd3-pay-total.is-out .sd3-pay-total-val { color:#fb7185; }

/* Empty states */
.sd3-empty-state { text-align:center; padding:44px 20px 20px; }
.sd3-empty-state i { font-size:2.4rem; color:rgba(255,255,255,.22); display:block; margin-bottom:14px; }
.sd3-empty-title { color:var(--sd3-text); font-weight:900; font-size:.9rem; }
.sd3-empty-text  { color:var(--sd3-muted); font-size:.84rem; margin-top:4px; }
.sd3-empty-inline { color:var(--sd3-muted); padding:6px 0; font-size:.84rem; }

@media(max-width:767.98px)  { .sd3-payment-row,.sd3-section-top{flex-direction:column;align-items:flex-start;} .sd3-payment-right{text-align:left;} .sd3-actions{grid-template-columns:1fr;} }
</style>


<!-- ══ AVATAR UPLOAD MODAL ══ -->
<form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="action" value="seller_upload_profile_picture">
  <div id="sdashUploadAvatarModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;width:calc(100% - 2rem);">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-duotone fa-image me-2"></i>Change Profile Picture</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="max-height:70vh;overflow:auto;">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="sdash-modal-preview-wrap">
              <?php if ($avatarSrc !== ''): ?>
                <img id="sdashAvatarPreview" src="<?= htmlspecialchars($avatarSrc) ?>" alt="preview" class="sdash-modal-preview-img">
              <?php else: ?>
                <div class="sdash-modal-preview-letter"><?= $avatarLetter ?></div>
              <?php endif; ?>
            </div>
            <div>
              <div style="font-weight:900;color:rgba(255,255,255,.92);">Upload your file</div>
              <div style="font-size:.88rem;color:rgba(255,255,255,.55);">PNG / JPG / WEBP — max 5 MB</div>
            </div>
          </div>
          <label class="sdash-dropzone" for="sdashAvatarFileInput">
            <i class="fa-duotone fa-cloud-arrow-up sdash-dropzone-icon"></i>
            <div class="sdash-dropzone-title">Drag &amp; drop or click to choose</div>
            <div class="sdash-dropzone-hint">Recommended: square image</div>
          </label>
          <input class="form-control d-none" accept="image/*" type="file" name="image_url" id="sdashAvatarFileInput">
          <div id="sdashAvatarFileName" class="text-muted small mt-2" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button id="sdashAvatarSubmit" type="submit" class="btn btn-primary" disabled>
            <i class="fa-duotone fa-cloud-arrow-up me-1"></i>Upload
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- ══ BANNER UPLOAD + REPOSITION MODAL ══ -->
<div class="modal fade" id="sdashUploadBannerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:560px;width:calc(100% - 2rem);">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-panorama me-2"></i>Change Banner</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="sdashBannerStep1">
          <div class="sdash-banner-preview-wrap mb-4" id="sdashBannerPreviewWrap">
            <?php if ($bannerUrl): ?>
              <img id="sdashBannerPreviewImg" src="<?= htmlspecialchars($bannerUrl, ENT_QUOTES) ?>" alt="banner" class="sdash-banner-preview-img" style="object-position:<?= htmlspecialchars($bannerPosition, ENT_QUOTES) ?>">
            <?php else: ?>
              <div class="sdash-banner-preview-placeholder">
                <i class="fa-duotone fa-panorama"></i><span>No banner set</span>
              </div>
            <?php endif; ?>
          </div>
          <label class="sdash-dropzone sdash-dropzone--banner" for="sdashBannerFileInput">
            <i class="fa-duotone fa-cloud-arrow-up sdash-dropzone-icon"></i>
            <div class="sdash-dropzone-title">Drag &amp; drop or click to choose</div>
            <div class="sdash-dropzone-hint">PNG / JPG / WEBP — Recommended: 1400×350px</div>
          </label>
          <input class="form-control d-none" accept="image/*" type="file" id="sdashBannerFileInput">
          <div id="sdashBannerFileName" class="text-muted small mt-2" style="display:none;"></div>
        </div>
        <div id="sdashBannerStep2" style="display:none;">
          <div style="font-size:.82rem;color:rgba(255,255,255,.55);font-weight:600;display:flex;align-items:center;margin-bottom:.75rem;">
            <i class="fa-duotone fa-arrows-up-down-left-right me-2"></i>Drag the image to adjust position, then save.
          </div>
          <div class="sdash-reposition-stage" id="sdashRepositionStage">
            <img id="sdashRepositionImg" src="" alt="banner" class="sdash-reposition-img" draggable="false">
            <div style="position:absolute;inset:0;pointer-events:none;"><div class="sdash-reposition-crosshair"></div></div>
          </div>
          <div class="d-flex align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-outline-light btn-sm" id="sdashBannerBack"><i class="fa-solid fa-arrow-left me-1"></i>Change image</button>
            <span class="text-muted small ms-auto" id="sdashRepositionCoords">50% 50%</span>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="gap:8px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="sdashBannerNextBtn"     type="button" class="btn btn-primary" disabled style="display:none;"><i class="fa-solid fa-arrow-right me-1"></i>Adjust Position</button>
        <button id="sdashBannerSavePosOnly" type="button" class="btn btn-primary" style="display:none;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;"><i class="fa-duotone fa-crosshairs me-1"></i>Save Position</button>
        <button id="sdashBannerSaveAll"     type="button" class="btn btn-success" style="display:none;"><i class="fa-duotone fa-cloud-arrow-up me-1"></i>Upload &amp; Save</button>
      </div>
    </div>
  </div>
</div>
<form class="ajax-form" id="sdashBannerUploadForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" style="display:none;">
  <input type="hidden" name="action" value="seller_upload_banner">
  <input type="hidden" name="banner_position" id="sdashBannerPositionInput" value="50% 50%">
  <input type="file" name="banner_image" id="sdashBannerFileTransfer" accept="image/*">
</form>
<form class="ajax-form" id="sdashBannerPosOnlyForm" action="<?= AJAX_URL ?>" method="POST" style="display:none;">
  <input type="hidden" name="action" value="seller_save_banner_position">
  <input type="hidden" name="banner_position" id="sdashBannerPosOnlyInput" value="50% 50%">
</form>

<style>
.sdash-modal-preview-wrap { flex-shrink:0; }
.sdash-modal-preview-img  { width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.15); }
.sdash-modal-preview-letter { width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#2c3450,#22283a);border:2px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:#60a5fa; }
.sdash-dropzone { display:block;border:1px dashed rgba(255,255,255,.20);border-radius:14px;padding:20px 18px;cursor:pointer;background:rgba(255,255,255,.025);text-align:center;transition:border-color .12s,background .12s,transform .08s;user-select:none; }
.sdash-dropzone:hover { border-color:rgba(109,92,255,.65);background:rgba(109,92,255,.08);transform:translateY(-1px); }
.sdash-dropzone-icon  { font-size:1.7rem;color:rgba(109,92,255,.75);display:block;margin-bottom:7px; }
.sdash-dropzone-title { font-weight:900;color:rgba(255,255,255,.9); }
.sdash-dropzone-hint  { font-size:.83rem;color:rgba(255,255,255,.42);margin-top:4px; }
.sdash-dropzone--banner { padding:18px; }
.sdash-banner-preview-wrap { border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.08); }
.sdash-banner-preview-img  { width:100%;aspect-ratio:4/1;object-fit:cover;display:block;min-height:80px; }
.sdash-banner-preview-placeholder { aspect-ratio:4/1;min-height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;color:rgba(255,255,255,.25);font-size:.85rem;background:rgba(255,255,255,.02); }
.sdash-banner-preview-placeholder i { font-size:2rem; }
.sdash-reposition-stage { position:relative;width:100%;aspect-ratio:4/1;min-height:80px;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.12);cursor:grab;user-select:none;background:#0d0f1a; }
.sdash-reposition-stage:active { cursor:grabbing; }
.sdash-reposition-img { position:absolute;width:100%;height:100%;object-fit:cover;pointer-events:none; }
.sdash-reposition-crosshair { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:32px;height:32px; }
.sdash-reposition-crosshair::before,.sdash-reposition-crosshair::after { content:'';position:absolute;background:rgba(255,255,255,.55);border-radius:1px; }
.sdash-reposition-crosshair::before { width:1px;height:100%;left:50%;top:0; }
.sdash-reposition-crosshair::after  { height:1px;width:100%;top:50%;left:0; }
</style>

<script>
(function () {
  'use strict';

  (function () {
    var fileInput = document.getElementById('sdashAvatarFileInput');
    var submitBtn = document.getElementById('sdashAvatarSubmit');
    var dropzone  = document.querySelector('#sdashUploadAvatarModal .sdash-dropzone');
    var modalEl   = document.getElementById('sdashUploadAvatarModal');
    var preview   = document.getElementById('sdashAvatarPreview');
    var fileNameEl= document.getElementById('sdashAvatarFileName');
    if (!fileInput || !submitBtn) return;
    function applyFile(file) {
      if (!file || !file.type.startsWith('image/')) return;
      var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
      var url = URL.createObjectURL(file);
      if (preview && preview.tagName === 'IMG') preview.src = url;
      if (fileNameEl) { fileNameEl.textContent = file.name; fileNameEl.style.display = 'block'; }
      submitBtn.disabled = false;
    }
    fileInput.addEventListener('change', function () { if (fileInput.files[0]) applyFile(fileInput.files[0]); });
    if (dropzone) {
      dropzone.addEventListener('dragover',  function(e){e.preventDefault();dropzone.style.borderColor='rgba(109,92,255,.9)';});
      dropzone.addEventListener('dragleave', function(){dropzone.style.borderColor='';});
      dropzone.addEventListener('drop',      function(e){e.preventDefault();dropzone.style.borderColor='';var f=e.dataTransfer.files&&e.dataTransfer.files[0];if(f)applyFile(f);});
    }
    if (modalEl) {
      modalEl.addEventListener('hidden.bs.modal', function(){fileInput.value='';submitBtn.disabled=true;if(fileNameEl){fileNameEl.textContent='';fileNameEl.style.display='none';}if(dropzone)dropzone.style.borderColor='';});
    }
  })();

  (function () {
    var modalEl=document.getElementById('sdashUploadBannerModal'),fileInput=document.getElementById('sdashBannerFileInput'),fileNameEl=document.getElementById('sdashBannerFileName'),dropzone=document.querySelector('#sdashUploadBannerModal .sdash-dropzone'),step1=document.getElementById('sdashBannerStep1'),step2=document.getElementById('sdashBannerStep2'),nextBtn=document.getElementById('sdashBannerNextBtn'),backBtn=document.getElementById('sdashBannerBack'),saveAllBtn=document.getElementById('sdashBannerSaveAll'),savePosBtn=document.getElementById('sdashBannerSavePosOnly'),reposStage=document.getElementById('sdashRepositionStage'),reposImg=document.getElementById('sdashRepositionImg'),coordsLabel=document.getElementById('sdashRepositionCoords'),uploadForm=document.getElementById('sdashBannerUploadForm'),posOnlyForm=document.getElementById('sdashBannerPosOnlyForm'),posInput=document.getElementById('sdashBannerPositionInput'),posOnlyInput=document.getElementById('sdashBannerPosOnlyInput'),fileTransfer=document.getElementById('sdashBannerFileTransfer'),heroBanner=document.getElementById('spHeroBanner');
    if(!modalEl)return;
    var currentPos={x:50,y:50},selectedFile=null,isDragging=false,dragStart={x:0,y:0},posAtStart={x:50,y:50};
    var initRaw='<?= addslashes($bannerPosition) ?>',im=initRaw.match(/^([\d.]+)%\s+([\d.]+)%$/);
    if(im){currentPos.x=parseFloat(im[1]);currentPos.y=parseFloat(im[2]);}
    function posStr(){return Math.round(currentPos.x)+'% '+Math.round(currentPos.y)+'%';}
    function applyPosToImg(){if(reposImg)reposImg.style.objectPosition=posStr();if(coordsLabel)coordsLabel.textContent=posStr();}
    var phpBannerUrlDash='<?= addslashes($bannerUrl) ?>';
    function heroHasBanner(){if(phpBannerUrlDash)return true;var bi=heroBanner&&heroBanner.style.backgroundImage;return bi&&bi!=='none'&&bi!=='';}
    function heroGetBannerSrc(){if(heroBanner&&heroBanner.style.backgroundImage&&heroBanner.style.backgroundImage!=='none'&&heroBanner.style.backgroundImage!==''){return heroBanner.style.backgroundImage.replace(/^url\(["']?/,'').replace(/["']?\)$/,'');}return phpBannerUrlDash;}
    function goToStep1(){if(step1)step1.style.display='';if(step2)step2.style.display='none';if(nextBtn){nextBtn.style.display=selectedFile?'':'none';nextBtn.disabled=false;}if(saveAllBtn)saveAllBtn.style.display='none';if(savePosBtn)savePosBtn.style.display=heroHasBanner()?'':'none';if(heroHasBanner()&&!selectedFile){var src=heroGetBannerSrc();if(reposImg&&src){reposImg.src=src;applyPosToImg();}}}
    function goToStep2(src){if(reposImg){reposImg.src=src;reposImg.style.objectPosition=posStr();}if(coordsLabel)coordsLabel.textContent=posStr();if(step1)step1.style.display='none';if(step2)step2.style.display='';if(nextBtn)nextBtn.style.display='none';if(saveAllBtn)saveAllBtn.style.display=selectedFile?'':'none';if(savePosBtn)savePosBtn.style.display=heroHasBanner()?'':'none';}
    function applyFile(file){if(!file||!file.type.startsWith('image/'))return;selectedFile=file;var url=URL.createObjectURL(file),pw=document.getElementById('sdashBannerPreviewWrap');if(pw)pw.innerHTML='<img src="'+url+'" class="sdash-banner-preview-img" style="object-fit:cover;">';if(fileNameEl){fileNameEl.textContent=file.name;fileNameEl.style.display='block';}if(nextBtn){nextBtn.style.display='';nextBtn.disabled=false;}}
    if(fileInput)fileInput.addEventListener('change',function(){if(fileInput.files[0])applyFile(fileInput.files[0]);});
    if(dropzone){dropzone.addEventListener('dragover',function(e){e.preventDefault();dropzone.style.borderColor='rgba(109,92,255,.9)';});dropzone.addEventListener('dragleave',function(){dropzone.style.borderColor='';});dropzone.addEventListener('drop',function(e){e.preventDefault();dropzone.style.borderColor='';var f=e.dataTransfer.files&&e.dataTransfer.files[0];if(f)applyFile(f);});}
    if(nextBtn)nextBtn.addEventListener('click',function(){if(selectedFile)goToStep2(URL.createObjectURL(selectedFile));});
    if(backBtn)backBtn.addEventListener('click',goToStep1);
    function startDrag(cx,cy){isDragging=true;dragStart={x:cx,y:cy};posAtStart={x:currentPos.x,y:currentPos.y};}
    function moveDrag(cx,cy){if(!isDragging||!reposStage||!reposImg)return;var sw=reposStage.offsetWidth,sh=reposStage.offsetHeight,iw=reposImg.naturalWidth||sw*1.5,ih=reposImg.naturalHeight||sh*1.5,sc=Math.max(sw/iw,sh/ih),rw=iw*sc,rh=ih*sc;currentPos.x=Math.max(0,Math.min(100,posAtStart.x-(cx-dragStart.x)/Math.max(rw-sw,1)*100));currentPos.y=Math.max(0,Math.min(100,posAtStart.y-(cy-dragStart.y)/Math.max(rh-sh,1)*100));applyPosToImg();}
    function endDrag(){isDragging=false;}
    if(reposStage){reposStage.addEventListener('mousedown',function(e){e.preventDefault();startDrag(e.clientX,e.clientY);});window.addEventListener('mousemove',function(e){if(isDragging)moveDrag(e.clientX,e.clientY);});window.addEventListener('mouseup',endDrag);reposStage.addEventListener('touchstart',function(e){var t=e.touches[0];startDrag(t.clientX,t.clientY);},{passive:true});reposStage.addEventListener('touchmove',function(e){var t=e.touches[0];moveDrag(t.clientX,t.clientY);e.preventDefault();},{passive:false});reposStage.addEventListener('touchend',endDrag);}
    if(saveAllBtn){saveAllBtn.addEventListener('click',function(){if(!selectedFile||!uploadForm||!posInput||!fileTransfer)return;posInput.value=posStr();var dt=new DataTransfer();dt.items.add(selectedFile);fileTransfer.files=dt.files;if(heroBanner){heroBanner.style.backgroundImage='url('+URL.createObjectURL(selectedFile)+')';heroBanner.style.backgroundPosition=posStr();}if(typeof uploadForm.requestSubmit==='function')uploadForm.requestSubmit();else uploadForm.submit();});}
    if(savePosBtn){savePosBtn.addEventListener('click',function(){if(!posOnlyForm||!posOnlyInput)return;posOnlyInput.value=posStr();if(heroBanner)heroBanner.style.backgroundPosition=posStr();if(typeof posOnlyForm.requestSubmit==='function')posOnlyForm.requestSubmit();else posOnlyForm.submit();var bm=bootstrap.Modal.getInstance(modalEl);if(bm)bm.hide();});}
    if(modalEl){modalEl.addEventListener('show.bs.modal',function(){goToStep1();if(heroHasBanner()&&!selectedFile){goToStep2(heroGetBannerSrc());}});modalEl.addEventListener('hidden.bs.modal',function(){selectedFile=null;isDragging=false;if(fileInput)fileInput.value='';if(fileNameEl){fileNameEl.textContent='';fileNameEl.style.display='none';}if(dropzone)dropzone.style.borderColor='';goToStep1();});}
  })();
})();
</script>

<?php if (!empty(array_filter($salesSeries, fn($d) => $d['sales'] > 0))): ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/chart.js/dist/chart.min.js"></script>
<script>
(function () {
  'use strict';
  var series = <?= json_encode($salesSeries, JSON_UNESCAPED_SLASHES) ?>;
  var canvas = document.getElementById('sd3Chart');
  var tabs   = document.querySelectorAll('#sd3RangeTabs .sd3-range-tab');
  var elSales = document.getElementById('sd3Sales');
  var elRev   = document.getElementById('sd3Revenue');
  var elAvg   = document.getElementById('sd3Avg');
  var elBest  = document.getElementById('sd3Best');
  if (!canvas || typeof Chart === 'undefined') return;

  // One revenue area instead of a bar/line combo on two axes: the second axis
  // and the lone tall bar made a single sales day look like a glitch. The sales
  // count now lives in the tooltip, where it belongs.
  var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  function fmtEuro(n) { return '\u20ac' + (Math.round(n * 100) / 100).toFixed(2); }
  function fmtDay(d) { var p = d.split('-'); return parseInt(p[2], 10) + ' ' + MONTHS[parseInt(p[1], 10) - 1]; }
  function fmtDayFull(d) { var p = d.split('-'); return fmtDay(d) + ' ' + p[0]; }

  function pointRadii(values) { return values.map(function (v) { return v > 0 ? 5 : 0; }); }

  function revenueGradient(ctx, area) {
    if (!area) return 'rgba(167,139,250,.18)';
    var g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
    g.addColorStop(0, 'rgba(167,139,250,.45)');
    g.addColorStop(1, 'rgba(167,139,250,0)');
    return g;
  }

  // Kept in sync with the rendered slice so the tooltip can show the sale count.
  var visible = [];

  var chart = new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'Revenue',
        data: [],
        borderColor: '#a78bfa',
        backgroundColor: function (context) {
          return revenueGradient(context.chart.ctx, context.chart.chartArea);
        },
        pointBackgroundColor: '#a78bfa',
        pointBorderColor: '#15161b',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: '#a78bfa',
        pointBorderWidth: 2,
        pointRadius: [],
        pointHoverRadius: 7,
        pointHitRadius: 20,
        borderWidth: 2.5,
        tension: .35,
        fill: true,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      layout: { padding: { top: 8 } },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(20,21,26,.96)',
          borderColor: 'rgba(255,255,255,.10)',
          borderWidth: 1,
          cornerRadius: 10,
          padding: 12,
          titleColor: '#f1f5f9',
          titleFont: { size: 12, weight: '700' },
          bodyColor: 'rgba(233,236,239,.82)',
          bodyFont: { size: 12 },
          bodySpacing: 4,
          displayColors: false,
          callbacks: {
            title: function (items) {
              var row = visible[items[0].dataIndex];
              return row ? fmtDayFull(row.date) : '';
            },
            label: function (item) {
              var row = visible[item.dataIndex] || { sales: 0, revenue: 0 };
              return [
                'Revenue: ' + fmtEuro(row.revenue),
                'Sales: ' + row.sales,
              ];
            },
          },
        },
      },
      scales: {
        x: {
          ticks: { color: 'rgba(255,255,255,.38)', maxTicksLimit: 7, font: { size: 11 }, maxRotation: 0 },
          grid: { display: false },
          border: { color: 'rgba(255,255,255,.08)' },
        },
        y: {
          beginAtZero: true,
          ticks: {
            color: 'rgba(255,255,255,.38)',
            font: { size: 11 },
            padding: 8,
            maxTicksLimit: 5,
            callback: function (value) { return '\u20ac' + value; },
          },
          grid: { color: 'rgba(255,255,255,.05)' },
          border: { display: false },
        },
      },
    },
  });

  function render(days) {
    var slice = series.slice(Math.max(0, series.length - days));
    var totalSales = 0, totalRevenue = 0, bestDay = null, bestRevenue = -1;

    slice.forEach(function (d) {
      totalSales += d.sales;
      totalRevenue += d.revenue;
      if (d.revenue > bestRevenue) { bestRevenue = d.revenue; bestDay = d.date; }
    });

    visible = slice;
    var revenueData = slice.map(function (d) { return d.revenue; });

    chart.data.labels = slice.map(function (d) { return fmtDay(d.date); });
    chart.data.datasets[0].data = revenueData;
    chart.data.datasets[0].pointRadius = pointRadii(revenueData);
    chart.update();

    if (elSales) elSales.textContent = totalSales;
    if (elRev) elRev.textContent = fmtEuro(totalRevenue);
    if (elAvg) elAvg.textContent = totalSales > 0 ? fmtEuro(totalRevenue / totalSales) : '\u20ac0.00';
    // Spelled-out month so "27 Jul 2026" can't be misread as a day/month swap.
    if (elBest) elBest.textContent = (bestDay && bestRevenue > 0) ? (fmtDayFull(bestDay) + ' · ' + fmtEuro(bestRevenue)) : '—';
  }

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('is-active'); });
      tab.classList.add('is-active');
      render(parseInt(tab.getAttribute('data-range'), 10) || 30);
    });
  });

  render(30);
})();
</script>
<?php endif; ?>

<?php
// Only inject the auto-open script when we are on the accounts listing page
// (i.e. this dashboard template is never on that page, so this block is
// intentionally left empty here — see list.php for the matching snippet).
// The dashboard just sets the hash: /seller-area/accounts#open-add-account
?>
