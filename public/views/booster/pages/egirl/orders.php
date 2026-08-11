<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>

<style>
.eg-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--bs-border-color);
}
.eg-stat-tile {
    background: var(--bs-card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-card-border-radius, 1rem);
    box-shadow: var(--bs-card-box-shadow, none);
    padding: 1rem;
    min-height: 124px;
}
.tile-icon {
    width: 32px; height: 32px; border-radius: .75rem;
    display:flex; align-items:center; justify-content:center; margin-bottom:.85rem;
}
.tile-val { font-size: 1.9rem; font-weight: 800; line-height: 1.1; color:#fff; }
.tile-lbl { margin-top:.35rem; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,.72); }
.tile-sub { margin-top:.25rem; font-size:.78rem; color:rgba(255,255,255,.58); }
.filter-bar {
    display:flex; align-items:center; gap:.6rem; flex-wrap:wrap;
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--bs-border-color);
    background: rgba(255,255,255,.02);
}
.filter-bar label { color: rgba(255,255,255,.72); font-size:.82rem; font-weight:700; margin-right:.15rem; }
.fpill {
    display:inline-flex; align-items:center; gap:.35rem; padding:.38rem .75rem; border-radius:999px;
    background: transparent; border:1px solid var(--bs-border-color); color:rgba(255,255,255,.76); font-size:.8rem; font-weight:700;
}
.fpill.active, .fpill:hover { color:#fff; background: rgba(255,255,255,.04); }
.fpill-dot { width:6px; height:6px; border-radius:50%; display:inline-block; }
.eg-orders-table thead th {
    background: rgba(255,255,255,.03) !important;
    color: rgba(255,255,255,.68);
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1px solid var(--bs-border-color) !important;
}
.eg-orders-table tbody td {
    border-bottom: 1px solid rgba(255,255,255,.05) !important;
    color: #fff;
    vertical-align: middle;
}
.eg-order-status {
    display:inline-flex; align-items:center; gap:.35rem; padding:.28rem .65rem; border-radius:999px; font-size:.76rem; font-weight:700;
    border:1px solid var(--bs-border-color); background: rgba(255,255,255,.03); color:#fff;
}
.eg-order-status.completed   { background: rgba(34,197,94,.10); border-color: rgba(34,197,94,.22); color: #4ade80; }
.eg-order-status.in_progress { background: rgba(14,165,233,.10); border-color: rgba(14,165,233,.22); color: #38bdf8; }
.eg-order-status.paid        { background: rgba(168,85,247,.12); border-color: rgba(168,85,247,.24); color: #c084fc; }
.eg-order-status.unpaid      { background: rgba(236,72,153,.10); border-color: rgba(236,72,153,.24); color: #f472b6; }
.eg-order-status.cancelled   { background: rgba(245,202,153,.10); border-color: rgba(245,202,153,.22); color: #f5ca99; }
.eg-order-status.refunded    { background: rgba(148,163,184,.10); border-color: rgba(148,163,184,.22); color: #94a3b8; }
.eg-client-avatar {
    width:32px; height:32px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:800; color:#fff;
    background: rgba(255,255,255,.06); border:1px solid var(--bs-border-color); overflow:hidden;
}
.eg-client-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; }

.eg-service-title {
    color: var(--eg-text);
    font-weight: 700;
    max-width: 260px;
    white-space: normal;
    line-height: 1.35;
}
.eg-game-label {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    margin-top: .35rem;
    color: var(--eg-muted);
    font-size: .72rem;
    font-weight: 700;
}
.eg-game-label img {
    width: 16px;
    height: 16px;
    object-fit: contain;
    display: block;
}
@media (max-width: 1199.98px) {
    .eg-stats-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .eg-stats-row { grid-template-columns: 1fr; }
}
</style>

<?php
$orders = $orders ?? [];
$detectAdminRole = static function (): bool {
    $candidates = [];
    foreach ([
        $GLOBALS['BOOSTER_DATA']['role'] ?? null,
        $GLOBALS['BOOSTER_DATA']['type'] ?? null,
        $GLOBALS['user']['role'] ?? null,
        $GLOBALS['account']['role'] ?? null,
        $GLOBALS['meta']['role'] ?? null,
        $_SESSION['role'] ?? null,
        $_SESSION['user_role'] ?? null,
    ] as $candidate) {
        if ($candidate !== null && $candidate !== '') {
            $candidates[] = strtolower((string)$candidate);
        }
    }
    foreach ($candidates as $candidate) {
        if (in_array($candidate, ['admin', 'administrator', 'superadmin', 'super_admin'], true)) {
            return true;
        }
    }
    return false;
};
$isAdminViewer = $detectAdminRole();

if (!$isAdminViewer) {
    $orders = array_values(array_filter($orders, static function(array $order): bool {
        return strtoupper((string)($order['status'] ?? '')) !== 'UNPAID';
    }));
}

/* ── Compute tile stats ── */
$statTotal     = count($orders);
$statInProg    = 0; $statDone = 0; $statUnpaid = 0;
$resolveEgirlEarningCents = static function(array $order): int {
    // Always calculate earnings in EUR cents (payout currency)
    $priceEurCents = (int)($order['price_eur'] ?? 0);
    if ($priceEurCents <= 0) {
        $priceEurCents = (int)($order['price'] ?? 0);
        if (strtoupper((string)($order['currency'] ?? 'EUR')) === 'USD') {
            $rate = (float)(function_exists('get_exchange_rate') ? get_exchange_rate() : 0);
            if ($rate > 0) $priceEurCents = (int)round($priceEurCents / $rate);
        }
    }
    $cutPct = $order['egirl_cut'] ?? null;
    $cutPct = ($cutPct === null || $cutPct === '') ? 60.0 : (float)$cutPct;
    return (int) round($priceEurCents * max(0, $cutPct) / 100);
};

$decodeEgirlAssignment = static function(array $order): array {
    $data = [];
    if (!empty($order['assignment'])) {
        if (is_array($order['assignment'])) {
            $data = $order['assignment'];
        } elseif (is_string($order['assignment'])) {
            $decoded = json_decode($order['assignment'], true);
            if (is_array($decoded)) $data = $decoded;
        }
    }
    if (empty($data) && !empty($order['client_notes']) && is_string($order['client_notes'])) {
        if (preg_match('/DATA:\s*(\{.*\})/s', $order['client_notes'], $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) $data = $decoded;
        }
    }
    return $data;
};
$pickOrderValue = static function(array $order, array $data, array $keys, string $fallback = ''): string {
    foreach ($keys as $key) {
        if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') return (string)$data[$key];
        if (array_key_exists($key, $order) && $order[$key] !== null && $order[$key] !== '') return (string)$order[$key];
    }
    return $fallback;
};
$formatEgirlOrderTitle = static function(string $server, string $mode, string $amountText): string {
    $server = strtoupper(trim($server));
    $mode = trim(preg_replace('/\s+/', ' ', $mode));
    $mode = preg_replace('/^LoL\s+GGirl:\s*/i', '', $mode);
    $mode = preg_replace('/\s+Game$/i', '', $mode);
    $amountText = trim(preg_replace('/\s+/', ' ', $amountText));
    $amountText = preg_replace_callback('/\b(game|games)\b/i', static function($m) { return ucfirst(strtolower($m[1])); }, $amountText);
    $parts = array_values(array_filter([
        $server !== '' ? $server : null,
        $mode !== '' ? $mode : null,
        $amountText !== '' ? $amountText : null,
    ]));
    return implode(' - ', $parts);
};

$egirlGameMeta = static function($game): array {
    $key = strtolower(trim((string)$game));
    if (!in_array($key, ['lol', 'val', 'tft'], true)) $key = 'lol';
    $icons = [
        'lol' => ['league-of-legends.png', 'League of Legends'],
        'val' => ['valorant.png', 'Valorant'],
        'tft' => ['teamfight-tactics.png', 'Teamfight Tactics'],
    ];
    return ['key' => $key, 'icon' => $icons[$key][0], 'label' => $icons[$key][1]];
};

$statRevCents  = 0;
foreach ($orders as $o) {
    $st = strtoupper($o['status'] ?? '');
    if ($st === 'IN_PROGRESS') $statInProg++;
    if ($st === 'COMPLETED')   { $statDone++; $statRevCents += $resolveEgirlEarningCents($o); }
    if ($st === 'UNPAID')      $statUnpaid++;
}
?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h4 class="card-header-title mb-0">
            <i class="fa-duotone fa-calendar-check me-2" style="color:var(--bs-primary)"></i>My Bookings
        </h4>
        <div class="input-group input-group-merge input-group-flush" style="max-width:220px">
            <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
            <input id="ordersSearch" type="search" class="form-control" placeholder="Search…">
        </div>
    </div>

    <!-- Stats tiles -->
    <div class="eg-stats-row">
        <div class="eg-stat-tile">
            <div class="tile-icon" style="background:rgba(168,85,247,.12);color:#c084fc"><i class="fa-duotone fa-list-check"></i></div>
            <div class="tile-val"><?= $statTotal ?></div>
            <div class="tile-lbl">Total Orders</div>
        </div>
        <div class="eg-stat-tile">
            <div class="tile-icon" style="background:rgba(14,165,233,.1);color:#38bdf8"><i class="fa-duotone fa-spinner"></i></div>
            <div class="tile-val" style="color:#38bdf8"><?= $statInProg ?></div>
            <div class="tile-lbl">In Progress</div>
        </div>
        <div class="eg-stat-tile">
            <div class="tile-icon" style="background:rgba(34,197,94,.1);color:#4ade80"><i class="fa-duotone fa-circle-check"></i></div>
            <div class="tile-val" style="color:#4ade80"><?= $statDone ?></div>
            <div class="tile-lbl">Completed</div>
        </div>
        <?php if ($isAdminViewer): ?>
        <div class="eg-stat-tile">
            <div class="tile-icon" style="background:rgba(236,72,153,.1);color:#f472b6"><i class="fa-duotone fa-triangle-exclamation"></i></div>
            <div class="tile-val" style="color:#f472b6"><?= $statUnpaid ?></div>
            <div class="tile-lbl">Unpaid</div>
        </div>
        <?php endif; ?>
        <div class="eg-stat-tile">
            <div class="tile-icon" style="background:rgba(168,85,247,.12);color:#c084fc"><i class="fa-duotone fa-coins"></i></div>
            <div class="tile-val">€<?= number_format($statRevCents / 100, 2) ?></div>
            <div class="tile-lbl">Earnings</div>
            <div class="tile-sub">from completed</div>
        </div>
    </div>

    <!-- Filter pills -->
    <div class="filter-bar">
        <label>Status</label>
        <button type="button" class="fpill active" data-order-filter="">All</button>
        <button type="button" class="fpill fpill-ip" data-order-filter="in_progress">
            <span class="fpill-dot" style="background:#0ea5e9"></span>In Progress
        </button>
        <button type="button" class="fpill fpill-done" data-order-filter="completed">
            <span class="fpill-dot" style="background:#22c55e"></span>Completed
        </button>
        <button type="button" class="fpill fpill-cancel" data-order-filter="cancelled">
            <span class="fpill-dot" style="background:#f5ca99"></span>Cancelled
        </button>
        <?php if ($isAdminViewer): ?>
        <button type="button" class="fpill fpill-unpaid" data-order-filter="unpaid">
            <span class="fpill-dot" style="background:#ec4899"></span>Unpaid
        </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($orders)): ?>
    <div class="table-responsive datatable-custom">
        <table class="js-datatable eg-orders-table table table-borderless table-nowrap table-align-middle card-table"
               id="orders_table"
               data-hs-datatables-options='{
                   "order": [[5,"desc"]],
                   "search": "#ordersSearch",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "ordersPagination",
                   "entries": "#ordersEntries",
                   "info": {"totalQty": "#ordersTotalQty"}
               }'>
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Earning</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o):
                    $stRaw    = strtolower(str_replace(' ', '_', $o['status'] ?? 'unpaid'));
                    $stLabel  = ucwords(str_replace('_', ' ', $stRaw));
                    $stIcon   = match($stRaw) {
                        'completed'   => 'fa-circle-check',
                        'in_progress' => 'fa-spinner',
                        'paid'        => 'fa-circle-dollar-to-slot',
                        'unpaid'      => 'fa-circle-xmark',
                        'cancelled'   => 'fa-ban',
                        'refunded'    => 'fa-rotate-left',
                        default       => 'fa-circle',
                    };
                    $isUnpaid      = strtoupper($o['status'] ?? '') === 'UNPAID';
                    $clientInitial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $o['client_username'] ?? 'C') ?: 'C', 0, 1));
                    $orderCurrency = strtoupper((string)($o['currency'] ?? 'EUR'));
                    $priceCents = (int)($o['price'] ?? $o['price_cents'] ?? 0);
                    $earningCents = $resolveEgirlEarningCents($o); // always EUR cents
                    $assignment = $decodeEgirlAssignment($o);
                    $modeTitle = $pickOrderValue($o, $assignment, ['mode_title', 'mode', 'service_title', 'service_type'], 'Normal Draft Game');
                    $serverText = strtoupper($pickOrderValue($o, $assignment, ['server'], 'EUW'));
                    $amountValue = (int)$pickOrderValue($o, $assignment, ['amount'], '1');
                    $amountText = $amountValue . ' ' . ($amountValue === 1 ? 'game' : 'games');
                    $serviceTitle = $formatEgirlOrderTitle($serverText, $modeTitle, $amountText);
            $gameMeta = $egirlGameMeta($o['game'] ?? 'lol');
                ?>
                    <tr data-order-status="<?= htmlspecialchars($stRaw, ENT_QUOTES) ?>">
                        <td style="color:var(--eg-muted);font-size:.82rem">#<?= $o['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!$isUnpaid): ?>
                                <div class="eg-client-avatar">
                                    <?php if (!empty($o['client_icon'])): ?>
                                        <img src="<?= htmlspecialchars($o['client_icon']) ?>" alt="">
                                    <?php else: ?>
                                        <?= $clientInitial ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <span style="color:<?= $isUnpaid ? 'var(--eg-muted)' : 'var(--eg-text)' ?>;font-weight:600">
                                    <?= $isUnpaid ? '—' : htmlspecialchars($o['client_username'] ?? '—') ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="eg-service-title">
                                <?= htmlspecialchars($serviceTitle) ?>
                            </div>
                            <?php if (!empty($o['game'])): ?>
                                <span class="eg-game-label">
                                    <img src="<?= ASSET_URL ?>/website/images/icons/<?= htmlspecialchars($gameMeta['icon'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($gameMeta['label'], ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($gameMeta['label'], ENT_QUOTES) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold" style="color:var(--eg-text)" data-order="<?= $earningCents ?>">
                            €<?= number_format($earningCents / 100, 2) ?>
                        </td>
                        <td>
                            <span class="eg-order-status <?= $stRaw ?>">
                                <i class="fa-solid <?= $stIcon ?>" style="font-size:.65rem"></i>
                                <?= $stLabel ?>
                            </span>
                        </td>
                        <td style="color:var(--eg-muted)" data-order="<?= !empty($o['created_at']) ? strtotime($o['created_at']) : 0 ?>">
                            <?= !empty($o['created_at']) ? date('d.m.Y', strtotime($o['created_at'])) : '—' ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= BSTR_URL ?>/egirl-order/<?= $o['id'] ?>"
                               class="btn btn-sm"
                               style="background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.25);color:#c084fc;font-weight:700;">
                                View
                            </a>
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
                        <select id="ordersEntries" class="js-select form-select form-select-borderless w-auto"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <span style="color:var(--eg-muted)">of</span>
                    <span id="ordersTotalQty" style="color:var(--eg-text)"></span>
                </div>
            </div>
            <div class="col-sm-auto"><nav id="ordersPagination"></nav></div>
        </div>
    </div>
    <?php else: ?>
        <div class="card-body text-center py-5" style="color:var(--eg-muted)">
            <i class="fa-duotone fa-calendar-xmark fa-3x d-block mb-3" style="color:rgba(168,85,247,.4)"></i>
            <h5 style="color:var(--eg-text)">No bookings yet</h5>
            <p class="mb-0">Once clients book your services, they'll appear here.</p>
        </div>
    <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    HSCore.components.HSDatatables.init($('#orders_table'), {
        language: {
            zeroRecords: '<div class="text-center p-4" style="color:var(--eg-muted)">No orders match the current filter.</div>'
        }
    });

    var dt = $('#orders_table').DataTable();
    var activeFilter = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'orders_table') return true;
        if (!activeFilter) return true;
        return ($(dt.row(dataIndex).node()).data('order-status') || '') === activeFilter;
    });

    $('[data-order-filter]').on('click', function () {
        $('[data-order-filter]').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('order-filter');
        dt.draw();
    });
});
</script>
<?= $this->end() ?>
