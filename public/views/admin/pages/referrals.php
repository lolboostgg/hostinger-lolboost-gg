<?php
/**
 * Referral Management – Admin View
 *
 * Settings are stored via lb_referral_set_setting() which writes keys WITHOUT
 * the "referral_" prefix, e.g. "enabled", "client_reward_percent", etc.
 * The ajax.php handler referral_admin_save_settings() expects these POST keys:
 *   enabled, client_reward_percent, booster_reward_percent, seller_reward_percent,
 *   min_order_eur, cookie_days, require_completed,
 *   block_same_client_account, block_same_email,
 *   allow_client_referrals, allow_booster_referrals, allow_seller_referrals
 */
global $db;

/* ── Load settings via lb_referral_get_settings() if available ── */
$settingsMap = [];
if (function_exists('lb_referral_get_settings')) {
    $settingsMap = (array) lb_referral_get_settings();
} elseif (isset($db)) {
    // Fallback: read directly from DB
    try {
        $rows = $db->run("SELECT setting_key, setting_value FROM referral_settings") ?: [];
        foreach ($rows as $r) {
            // Strip optional "referral_" prefix so keys are consistent
            $k = preg_replace('/^referral_/', '', (string)$r['setting_key']);
            $settingsMap[$k] = $r['setting_value'];
        }
    } catch (\Throwable $e) {}
}

// Typed helpers
$rs_int   = fn(string $k, int   $d=0) => (int)   ($settingsMap[$k] ?? $d);
$rs_float = fn(string $k, float $d=0) => (float) ($settingsMap[$k] ?? $d);
$rs_bool  = fn(string $k, int   $d=1) => (int)   ($settingsMap[$k] ?? $d);

/* ── Filters ── */
$ownerFilter  = isset($_GET['owner_type']) ? trim((string)$_GET['owner_type']) : '';
$statusFilter = isset($_GET['status'])     ? trim((string)$_GET['status'])     : '';
$searchFilter = isset($_GET['q'])          ? trim((string)$_GET['q'])          : '';

/* ── Conversions + stats ── */
$conversionRows = [];
$stats = ['count'=>0,'revenue_cents'=>0,'reward_cents'=>0,'reward_points'=>0.0];

$where=[]; $params=[];
if ($ownerFilter!=='' && in_array($ownerFilter,['client','booster','seller'],true)){$where[]="rc.owner_type=?";$params[]=$ownerFilter;}
if ($statusFilter!=='' && in_array($statusFilter,['approved','reversed'],true)){$where[]="rc.status=?";$params[]=$statusFilter;}
if ($searchFilter!==''){
    $where[]="(rl.code LIKE ? OR rc.buyer_email LIKE ? OR CAST(rc.order_id AS CHAR) LIKE ? OR CAST(rc.invoice_id AS CHAR) LIKE ?)";
    $like='%'.$searchFilter.'%'; $params=array_merge($params,[$like,$like,$like,$like]);
}
$whereSql=!empty($where)?' WHERE '.implode(' AND ',$where):'';

try {
    if (isset($db)) {
        $sql = "
            SELECT rc.*,
                   rl.code        AS referral_code,
                   c.username     AS client_username,  c.email AS client_email,
                   b.username     AS booster_username, b.email AS booster_email,
                   s.username     AS seller_username,  s.email AS seller_email,
                   buyer.username AS buyer_username
            FROM referral_conversions rc
            LEFT JOIN referral_links rl ON rl.id = rc.referral_link_id
            LEFT JOIN clients  c     ON rc.owner_type='client'  AND c.id  = rc.owner_id
            LEFT JOIN boosters b     ON rc.owner_type='booster' AND b.id  = rc.owner_id
            LEFT JOIN sellers  s     ON rc.owner_type='seller'  AND s.id  = rc.owner_id
            LEFT JOIN clients  buyer ON buyer.id = rc.buyer_client_id
            $whereSql ORDER BY rc.created_at DESC LIMIT 200
        ";
        $conversionRows = (!empty($params) ? $db->run($sql, ...$params) : $db->run($sql)) ?: [];

        $sRow=$db->run("SELECT COUNT(*) AS total_count,
            COALESCE(SUM(invoice_total_cents),0) AS total_revenue_cents,
            COALESCE(SUM(reward_cents),0)        AS total_reward_cents,
            COALESCE(SUM(reward_points),0)       AS total_reward_points
            FROM referral_conversions WHERE status='approved'");
        if (!empty($sRow)){
            $r=is_array($sRow)&&isset($sRow[0])?$sRow[0]:$sRow;
            $stats['count']=(int)($r['total_count']??0);
            $stats['revenue_cents']=(int)($r['total_revenue_cents']??0);
            $stats['reward_cents']=(int)($r['total_reward_cents']??0);
            $stats['reward_points']=(float)($r['total_reward_points']??0);
        }
        if (!is_array($conversionRows)) $conversionRows=[];
    }
} catch (\Throwable $e){ $conversionRows=[]; $queryError=$e->getMessage(); }
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Referrals - Admin Area | LoLBoost.gg', 'h1' => 'Referral Management', 'description' => 'Manage referral settings and conversions.']]) ?>

<style>
/* ── Theme tokens ───────────────────────────────────────────────────────────
   body: #1e2022 | card: #25282a | border: #2f3235 | text: #c5c8cc
   teal: #00c9a7 | danger: #ed4c78 | primary: #5c4ae3 | amber: #f5ca99
   info: #09a5be | muted: #91989e
   ──────────────────────────────────────────────────────────────────────── */

.ref-stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:.85rem;margin-bottom:1.5rem;}
.stat-card{background:#25282a;border:.0625rem solid #2f3235;border-radius:.75rem;padding:1.1rem 1.25rem;display:flex;align-items:center;gap:.9rem;box-shadow:0 .375rem .75rem rgba(30,32,34,.2);}
.stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.stat-icon.c-teal {background:rgba(0,201,167,.13);color:#00c9a7;}
.stat-icon.c-blue {background:rgba(9,165,190,.13);color:#09a5be;}
.stat-icon.c-red  {background:rgba(237,76,120,.13);color:#ed4c78;}
.stat-icon.c-amber{background:rgba(245,202,153,.13);color:#f5ca99;}
.stat-label{font-size:.7rem;color:#91989e;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.1rem;}
.stat-value{font-size:1.3rem;font-weight:700;color:#c5c8cc;line-height:1.2;}

.ref-section-title{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#91989e;margin-bottom:.85rem;padding-bottom:.4rem;border-bottom:1px solid #2f3235;display:flex;align-items:center;gap:.4rem;}

/* Settings read-only grid */
.settings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:.65rem;}
.setting-item{background:rgba(0,0,0,.18);border:1px solid #2f3235;border-radius:.6rem;padding:.65rem .9rem;}
.setting-item-label{font-size:.68rem;color:#91989e;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;}
.setting-item-value{font-size:.92rem;font-weight:700;color:#c5c8cc;}
.setting-item-value.is-on {color:#00c9a7;}
.setting-item-value.is-off{color:#ed4c78;}

/* Action buttons – always visible */
.btn-action{display:inline-flex;align-items:center;gap:.35rem;padding:.32rem .85rem;border-radius:.5rem;font-size:.78rem;font-weight:600;border:1px solid #3a3e42;background:#2f3235;color:#c5c8cc;cursor:pointer;transition:background .15s,border-color .15s,color .15s;white-space:nowrap;text-decoration:none;}
.btn-action:hover{background:#3a3e42;border-color:#4b5055;color:#fff;}
.btn-action.btn-primary-action{background:#5c4ae3;border-color:#5c4ae3;color:#fff;}
.btn-action.btn-primary-action:hover{background:#6d5ef0;border-color:#6d5ef0;}
.btn-action.btn-teal-action{background:rgba(0,201,167,.14);border-color:rgba(0,201,167,.45);color:#00c9a7;}
.btn-action.btn-teal-action:hover{background:rgba(0,201,167,.26);border-color:rgba(0,201,167,.7);}
.btn-action:disabled{opacity:.55;cursor:not-allowed;}

/* Filter pills */
.ref-filter-wrap{display:flex;align-items:center;gap:.4rem;padding:.75rem 1.3125rem;border-bottom:.0625rem solid #2f3235;background:rgba(0,0,0,.10);flex-wrap:wrap;}
.ref-filter-wrap>label{font-size:.75rem;color:#91989e;margin:0 .1rem 0 0;flex-shrink:0;}
.ref-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:50rem;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid #2f3235;background:transparent;color:#91989e;transition:all .15s ease;text-decoration:none;}
.ref-pill:hover{color:#c5c8cc;border-color:#4b5055;}
.ref-pill.active-pill              {color:#1e2022;background:#00c9a7;border-color:#00c9a7;}
.ref-pill.pill-client.active-pill  {color:#fff;background:#09a5be;border-color:#09a5be;}
.ref-pill.pill-booster.active-pill {color:#fff;background:#5c4ae3;border-color:#5c4ae3;}
.ref-pill.pill-seller.active-pill  {color:#fff;background:#f59e0b;border-color:#f59e0b;}
.ref-pill.pill-approved.active-pill{color:#1e2022;background:#00c9a7;border-color:#00c9a7;}
.ref-pill.pill-reversed.active-pill{color:#fff;background:#ed4c78;border-color:#ed4c78;}
.ref-pill .pill-dot{width:7px;height:7px;border-radius:50%;background:currentColor;opacity:.7;}
.pill-sep{width:1px;height:18px;background:#2f3235;margin:0 .2rem;flex-shrink:0;}

/* Badges */
.status-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .6rem;border-radius:20px;font-size:.73rem;font-weight:600;white-space:nowrap;}
.sb-approved{background:rgba(0,201,167,.12);color:#00c9a7;border:1px solid rgba(0,201,167,.28);}
.sb-reversed{background:rgba(237,76,120,.13);color:#ed4c78;border:1px solid rgba(237,76,120,.28);}
.sb-pending {background:rgba(245,202,153,.12);color:#f5ca99;border:1px solid rgba(245,202,153,.28);}
.owner-badge{display:inline-flex;align-items:center;padding:.15rem .5rem;border-radius:20px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
.ob-client  {background:rgba(9,165,190,.12);color:#09a5be;border:1px solid rgba(9,165,190,.25);}
.ob-booster {background:rgba(92,74,227,.12);color:#9b8bf0;border:1px solid rgba(92,74,227,.25);}
.ob-seller  {background:rgba(245,158,11,.12);color:#f5ca99;border:1px solid rgba(245,158,11,.25);}
.ref-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:.76rem;font-weight:700;background:rgba(92,74,227,.10);color:#9b8bf0;border:1px solid rgba(92,74,227,.25);border-radius:.4rem;padding:.15rem .45rem;}

/* Toast */
#ref-toast{position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;padding:.75rem 1.25rem;border-radius:.6rem;font-size:.85rem;font-weight:600;display:none;align-items:center;gap:.5rem;box-shadow:0 4px 20px rgba(0,0,0,.4);}
#ref-toast.toast-success{background:#1a3a30;border:1px solid rgba(0,201,167,.4);color:#00c9a7;}
#ref-toast.toast-error  {background:#3a1a22;border:1px solid rgba(237,76,120,.4);color:#ed4c78;}
</style>

<div id="ref-toast"></div>

<!-- ── Stat Cards ─────────────────────────────────────────────────────────── -->
<div class="ref-stats-grid">
    <div class="stat-card">
        <div class="stat-icon c-teal"><i class="fa-duotone fa-share-nodes"></i></div>
        <div><div class="stat-label">Approved Conversions</div><div class="stat-value"><?= number_format((int)$stats['count']) ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-blue"><i class="fa-duotone fa-money-bill-wave"></i></div>
        <div><div class="stat-label">Referred Revenue</div><div class="stat-value">€<?= number_format($stats['revenue_cents']/100,2,',','.') ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-red"><i class="fa-duotone fa-gift"></i></div>
        <div><div class="stat-label">Booster Rewards</div><div class="stat-value">€<?= number_format($stats['reward_cents']/100,2,',','.') ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-amber"><i class="fa-duotone fa-coins"></i></div>
        <div><div class="stat-label">Client Rewards</div><div class="stat-value"><?= number_format((float)$stats['reward_points'],2,',','.') ?> <span style="font-size:.8rem;color:#91989e;">Coins</span></div></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     SETTINGS CARD
     ═══════════════════════════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between w-100 gap-3">
            <h5 class="card-header-title mb-0"><i class="fa-duotone fa-sliders me-2"></i>Referral Settings</h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn-action btn-primary-action" id="btn-toggle-edit">
                    <i class="fa-duotone fa-pen-to-square"></i> Edit settings
                </button>
                <button type="button" class="btn-action" onclick="window.location.reload()">
                    <i class="fa-duotone fa-rotate"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="card-body pt-3 pb-4">

        <!-- READ-ONLY (always visible, live from DB) -->
        <div id="settings-readonly">
            <div class="ref-section-title"><i class="fa-duotone fa-eye"></i> Current values (live from DB)</div>
            <div class="settings-grid">
                <?php
                $boolItem = function(string $label, string $key, int $def=1) use ($rs_bool): array {
                    $v = $rs_bool($key, $def);
                    return ['label'=>$label,'html'=>$v
                        ? '<span class="is-on">✓ Yes</span>'
                        : '<span class="is-off">✗ No</span>'];
                };
                $readItems = [
                    ['label'=>'Referral enabled',
                     'html'=>$rs_bool('enabled',1) ? '<span class="is-on">✓ Enabled</span>' : '<span class="is-off">✗ Disabled</span>'],
                    ['label'=>'Booster reward',
                     'html'=>number_format($rs_float('booster_reward_percent'),2,',','.').' %'],
                    ['label'=>'Client reward',
                     'html'=>number_format($rs_float('client_reward_percent'),2,',','.')],
                    ['label'=>'Seller reward',
                     'html'=>number_format($rs_float('seller_reward_percent'),2,',','.').' %'],
                    ['label'=>'Min. order value',
                     'html'=>'€'.number_format($rs_int('min_order_cents')/100,2,',','.')],
                    ['label'=>'Cookie lifetime',
                     'html'=>$rs_int('cookie_days',30).' days'],
                    $boolItem('Reward after completed','require_completed',1),
                    $boolItem('Block same account','block_same_client_account',1),
                    $boolItem('Block same email','block_same_email',1),
                    $boolItem('Client referrals','allow_client_referrals',1),
                    $boolItem('Booster referrals','allow_booster_referrals',1),
                    $boolItem('Seller referrals','allow_seller_referrals',1),
                ];
                foreach ($readItems as $item): ?>
                <div class="setting-item">
                    <div class="setting-item-label"><?= htmlspecialchars($item['label']) ?></div>
                    <div class="setting-item-value"><?= $item['html'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- EDIT FORM (hidden by default) -->
        <div id="settings-edit" style="display:none;">
            <div class="ref-section-title"><i class="fa-duotone fa-pen-to-square"></i> Edit settings</div>

            <!--
                POST keys must match exactly what ajax.php referral_admin_save_settings() expects:
                enabled, client_reward_percent, booster_reward_percent, seller_reward_percent, min_order_eur,
                cookie_days, require_completed, block_same_client_account,
                block_same_email, allow_client_referrals, allow_booster_referrals, allow_seller_referrals
            -->
            <form id="referral-settings-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Referral enabled</label>
                        <select name="enabled" class="form-select">
                            <option value="1" <?= $rs_bool('enabled',1)?'selected':'' ?>>Yes</option>
                            <option value="0" <?= !$rs_bool('enabled',1)?'selected':'' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Booster reward (%)</label>
                        <input type="number" step="0.01" min="0" max="100"
                               name="booster_reward_percent" class="form-control"
                               value="<?= number_format($rs_float('booster_reward_percent'),2,'.','')?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client reward (Coins/€)</label>
                        <input type="number" step="0.01" min="0"
                               name="client_reward_percent" class="form-control"
                               value="<?= number_format($rs_float('client_reward_percent'),2,'.','')?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Seller reward (%)</label>
                        <input type="number" step="0.01" min="0" max="100"
                               name="seller_reward_percent" class="form-control"
                               value="<?= number_format($rs_float('seller_reward_percent'),2,'.','')?>">
                    </div>
                    <div class="col-md-3">
                        <!-- ajax.php expects min_order_eur (euros, NOT cents) – it converts internally -->
                        <label class="form-label">Min. order value (€)</label>
                        <input type="number" step="0.01" min="0"
                               name="min_order_eur" class="form-control"
                               value="<?= number_format($rs_int('min_order_cents')/100,2,'.','')?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cookie lifetime (days)</label>
                        <input type="number" step="1" min="1" max="365"
                               name="cookie_days" class="form-control"
                               value="<?= $rs_int('cookie_days',30)?>">
                    </div>
                    <?php foreach([
                        'require_completed'        => 'Reward only after completed',
                        'block_same_client_account'=> 'Block same client account',
                        'block_same_email'         => 'Block same email',
                        'allow_client_referrals'   => 'Client referrals allowed',
                        'allow_booster_referrals'  => 'Booster referrals allowed',
                        'allow_seller_referrals'   => 'Seller referrals allowed',
                    ] as $k => $l): ?>
                    <div class="col-md-3">
                        <label class="form-label"><?= htmlspecialchars($l) ?></label>
                        <select name="<?= htmlspecialchars($k) ?>" class="form-select">
                            <option value="1" <?= $rs_bool($k,1)?'selected':''?>>Yes</option>
                            <option value="0" <?= !$rs_bool($k,1)?'selected':''?>>No</option>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn-action btn-teal-action" id="btn-save-settings">
                        <i class="fa-duotone fa-floppy-disk"></i> Save settings
                    </button>
                    <button type="button" class="btn-action" id="btn-cancel-edit">
                        <i class="fa-duotone fa-xmark"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     CONVERSIONS CARD
     ═══════════════════════════════════════════════════════════════════════════ -->
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between w-100 gap-3">
            <h5 class="card-header-title mb-0"><i class="fa-duotone fa-table-list me-2"></i>Referral Conversions</h5>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/admin-area/referrals" class="btn-action">
                    <i class="fa-duotone fa-rotate-left"></i> Reset filters
                </a>
                <button type="button" class="btn-action" onclick="window.location.reload()">
                    <i class="fa-duotone fa-rotate"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Filter pills -->
    <div class="ref-filter-wrap">
        <label>Owner:</label>
        <a href="<?= BASE_URL ?>/admin-area/referrals?status=<?= urlencode($statusFilter) ?>&q=<?= urlencode($searchFilter) ?>"
           class="ref-pill <?= $ownerFilter===''?'active-pill':'' ?>"><span class="pill-dot"></span> All</a>
        <a href="<?= BASE_URL ?>/admin-area/referrals?owner_type=client&status=<?= urlencode($statusFilter) ?>&q=<?= urlencode($searchFilter) ?>"
           class="ref-pill pill-client <?= $ownerFilter==='client'?'active-pill':'' ?>"><span class="pill-dot"></span> Clients</a>
        <a href="<?= BASE_URL ?>/admin-area/referrals?owner_type=booster&status=<?= urlencode($statusFilter) ?>&q=<?= urlencode($searchFilter) ?>"
           class="ref-pill pill-booster <?= $ownerFilter==='booster'?'active-pill':'' ?>"><span class="pill-dot"></span> Boosters</a>
        <a href="<?= BASE_URL ?>/admin-area/referrals?owner_type=seller&status=<?= urlencode($statusFilter) ?>&q=<?= urlencode($searchFilter) ?>"
           class="ref-pill pill-seller <?= $ownerFilter==='seller'?'active-pill':'' ?>"><span class="pill-dot"></span> Sellers</a>
        <span class="pill-sep"></span>
        <label>Status:</label>
        <a href="<?= BASE_URL ?>/admin-area/referrals?owner_type=<?= urlencode($ownerFilter) ?>&q=<?= urlencode($searchFilter) ?>"
           class="ref-pill <?= $statusFilter===''?'active-pill':'' ?>"><span class="pill-dot"></span> All</a>
        <a href="<?= BASE_URL ?>/admin-area/referrals?owner_type=<?= urlencode($ownerFilter) ?>&status=approved&q=<?= urlencode($searchFilter) ?>"
           class="ref-pill pill-approved <?= $statusFilter==='approved'?'active-pill':'' ?>"><span class="pill-dot"></span> Approved</a>
        <a href="<?= BASE_URL ?>/admin-area/referrals?owner_type=<?= urlencode($ownerFilter) ?>&status=reversed&q=<?= urlencode($searchFilter) ?>"
           class="ref-pill pill-reversed <?= $statusFilter==='reversed'?'active-pill':'' ?>"><span class="pill-dot"></span> Reversed</a>

        <form method="get" action="<?= BASE_URL ?>/admin-area/referrals" class="ms-auto d-flex align-items-center gap-2">
            <input type="hidden" name="owner_type" value="<?= htmlspecialchars($ownerFilter,ENT_QUOTES,'UTF-8') ?>">
            <input type="hidden" name="status"     value="<?= htmlspecialchars($statusFilter,ENT_QUOTES,'UTF-8') ?>">
            <div class="input-group input-group-merge input-group-flush" style="width:240px;">
                <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
                <input type="text" name="q" class="form-control" placeholder="Code, email, order, invoice…"
                       value="<?= htmlspecialchars($searchFilter,ENT_QUOTES,'UTF-8') ?>">
            </div>
            <button type="submit" class="btn-action btn-primary-action">Filter</button>
            <?php if ($searchFilter!==''||$ownerFilter!==''||$statusFilter!==''): ?>
                <a href="<?= BASE_URL ?>/admin-area/referrals" class="btn-action">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <?php if (!empty($queryError ?? '')): ?>
            <div class="alert alert-danger m-3" style="font-size:.82rem;">
                <strong>Query error:</strong> <?= htmlspecialchars($queryError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif ?>
        <?php if (empty($conversionRows)): ?>
            <div class="text-center p-5">
                <i class="fa-duotone fa-inbox fs-2 mb-3 d-block" style="color:#2f3235;"></i>
                <span class="text-muted">No conversions found for the current filter.</span>
            </div>
        <?php else: ?>
        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
            <thead class="thead-light">
                <tr><th>#</th><th>Date</th><th>Code</th><th>Owner</th><th>Buyer</th><th>Order</th><th>Invoice</th><th>Revenue</th><th>Reward</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($conversionRows as $row):
                $ownerType=(string)($row['owner_type']??'');
                if($ownerType==='booster'){$ownerName=trim((string)($row['booster_username']??''));$ownerEmail=trim((string)($row['booster_email']??''));}
                elseif($ownerType==='seller'){$ownerName=trim((string)($row['seller_username']??''));$ownerEmail=trim((string)($row['seller_email']??''));}
                else{$ownerName=trim((string)($row['client_username']??''));$ownerEmail=trim((string)($row['client_email']??''));}
                $buyerName=trim((string)($row['buyer_username']??''));
                $buyerEmail=trim((string)($row['buyer_email']??''));
                $rewardDisplay='—';
                if($ownerType==='booster' || $ownerType==='seller') $rewardDisplay='€'.number_format(((int)($row['reward_cents']??0))/100,2,',','.');
                elseif((float)($row['reward_points']??0)>0) $rewardDisplay=number_format((float)$row['reward_points'],2,',','.').' Coins';
                $status=(string)($row['status']??'');
                $sCls=match($status){'approved'=>'sb-approved','reversed'=>'sb-reversed',default=>'sb-pending'};
                $sIco=match($status){'approved'=>'fa-check-circle','reversed'=>'fa-rotate-left',default=>'fa-clock'};
                $oBdg=match($ownerType){'booster'=>'ob-booster','seller'=>'ob-seller',default=>'ob-client'};
                $init=mb_strtoupper(mb_substr($ownerName?:'?',0,1));
            ?>
            <tr>
                <td><span style="font-size:.8rem;color:#91989e;">#<?= (int)($row['id']??0) ?></span></td>
                <td style="font-size:.82rem;color:#91989e;"><?= htmlspecialchars((string)($row['created_at']??'—'),ENT_QUOTES,'UTF-8') ?></td>
                <td><span class="ref-code"><?= htmlspecialchars((string)($row['referral_code']??'—'),ENT_QUOTES,'UTF-8') ?></span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:30px;height:30px;border-radius:50%;background:rgba(92,74,227,.18);display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#9b8bf0;flex-shrink:0;"><?= htmlspecialchars($init,ENT_QUOTES,'UTF-8') ?></span>
                        <div>
                            <div class="fw-600" style="font-size:.85rem;line-height:1.2;"><?= htmlspecialchars($ownerName?:('ID '.(int)($row['owner_id']??0)),ENT_QUOTES,'UTF-8') ?></div>
                            <?php if($ownerEmail!==''): ?><div style="font-size:.72rem;color:#91989e;"><?= htmlspecialchars($ownerEmail,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
                            <span class="owner-badge <?= $oBdg ?> mt-1"><?= htmlspecialchars($ownerType,ENT_QUOTES,'UTF-8') ?></span>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if($buyerName!==''): ?>
                        <div class="fw-600" style="font-size:.85rem;"><?= htmlspecialchars($buyerName,ENT_QUOTES,'UTF-8') ?></div>
                        <?php if($buyerEmail!==''): ?><div style="font-size:.72rem;color:#91989e;"><?= htmlspecialchars($buyerEmail,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
                    <?php else: ?><span class="text-muted" style="font-size:.8rem;">—</span><?php endif; ?>
                </td>
                <td style="font-size:.83rem;color:#c5c8cc;">#<?= (int)($row['order_id']??0) ?></td>
                <td style="font-size:.83rem;color:#c5c8cc;">#<?= (int)($row['invoice_id']??0) ?></td>
                <td style="font-size:.85rem;font-weight:600;color:#c5c8cc;">€<?= number_format(((int)($row['invoice_total_cents']??0))/100,2,',','.') ?></td>
                <td style="font-size:.85rem;font-weight:600;color:#f5ca99;"><?= htmlspecialchars($rewardDisplay,ENT_QUOTES,'UTF-8') ?></td>
                <td><span class="status-badge <?= $sCls ?>"><i class="fa-duotone <?= $sIco ?>"></i> <?= htmlspecialchars($status?:'pending',ENT_QUOTES,'UTF-8') ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="card-footer">
            <span class="text-muted" style="font-size:.78rem;"><i class="fa-duotone fa-circle-info me-1"></i>Showing latest 200 matching entries.</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    /* ── Edit / readonly toggle ── */
    var btnToggle = document.getElementById('btn-toggle-edit');
    var btnCancel = document.getElementById('btn-cancel-edit');
    var viewRO    = document.getElementById('settings-readonly');
    var viewEdit  = document.getElementById('settings-edit');
    var isEditing = false;

    function showEdit() {
        isEditing = true;
        viewRO.style.display   = 'none';
        viewEdit.style.display = 'block';
        btnToggle.innerHTML    = '<i class="fa-duotone fa-eye"></i> View settings';
    }
    function showRO() {
        isEditing = false;
        viewRO.style.display   = 'block';
        viewEdit.style.display = 'none';
        btnToggle.innerHTML    = '<i class="fa-duotone fa-pen-to-square"></i> Edit settings';
    }
    btnToggle.addEventListener('click', function(){ isEditing ? showRO() : showEdit(); });
    if (btnCancel) btnCancel.addEventListener('click', showRO);

    /* ── Toast ── */
    var toast = document.getElementById('ref-toast');
    function showToast(msg, type) {
        toast.className = 'toast-' + type;
        toast.innerHTML = '<i class="fa-duotone ' + (type==='success'?'fa-check-circle':'fa-triangle-exclamation') + '"></i> ' + msg;
        toast.style.display = 'flex';
        clearTimeout(toast._t);
        toast._t = setTimeout(function(){ toast.style.display='none'; }, 3500);
    }

    /* ── Settings save via real ajax.php endpoint ── */
    document.getElementById('referral-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        var fd = new FormData(this);
        fd.set('action', 'referral_admin_save_settings');
        // Field names already match ajax.php exactly (enabled, booster_reward_percent, etc.)
        // min_order_eur is passed as euros – ajax.php converts to cents internally

        var btn  = document.getElementById('btn-save-settings');
        var orig = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = '<i class="fa-duotone fa-spinner fa-spin"></i> Saving…';

        try {
            var res  = await fetch('<?= BASE_URL ?>/ajax', { method: 'POST', body: fd });
            var json = await res.json();

            if (json.status === 'success') {
                showToast(json.message || 'Settings saved!', 'success');
                setTimeout(function(){ window.location.reload(); }, 1200);
            } else {
                var msg = (json.sendToast && json.sendToast.message) || json.message || 'Could not save.';
                showToast(msg, 'error');
                btn.disabled  = false;
                btn.innerHTML = orig;
            }
        } catch (err) {
            showToast('Network error – settings not saved.', 'error');
            btn.disabled  = false;
            btn.innerHTML = orig;
        }
    });
})();
</script>
