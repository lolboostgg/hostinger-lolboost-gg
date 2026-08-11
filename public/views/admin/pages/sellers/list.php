<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Sellers | Admin Area']]) ?>

<?php
$data        = $data ?? [];
$default_fee = (float)($default_fee ?? 15.0);
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

/* ── Summary stats ── */
$totalSellers   = count($data);
$activeSellers  = 0;
$bannedSellers  = 0;
$pendingSellers = 0;
$totalBalance   = 0;
$totalListed    = 0;
$totalSold      = 0;

foreach ($data as $row) {
    $isBanned = (int)($row['is_banned'] ?? 0);
    $isActive = (int)($row['is_active'] ?? 0);
    if ($isBanned)     $bannedSellers++;
    elseif ($isActive) $activeSellers++;
    else               $pendingSellers++;
    $totalBalance += (int)($row['balance'] ?? 0);
    $totalListed  += (int)($row['accounts_count'] ?? 0);
    $totalSold    += (int)($row['accounts_sold']  ?? 0);
}
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats grid ── */
.sl-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px;}
.sl-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.sl-stat:hover{transform:translateY(-2px);}
.sl-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.sl-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.sl-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}

/* ── Pills filter ── */
.al-pills{display:flex;gap:6px;flex-wrap:wrap;}
.al-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none;}
.al-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.al-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-pill[data-status="active"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80;}
.al-pill[data-status="pending"].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.35);color:#facc15;}
.al-pill[data-status="banned"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.28);color:#fb7185;}

/* ── Search ── */
.al-search-wrap{position:relative;}
.al-search-wrap input{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s;}
.al-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important;}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25)!important;}
.al-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none;}

/* ── Table ── */
.al-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:visible;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);}
.al-table{width:100%;border-collapse:collapse;display:table;}
.al-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;user-select:none;}
.al-table thead th.sortable{cursor:pointer;}
.al-table thead th.sortable:hover{color:rgba(255,255,255,.7);}
.al-table thead th .sort-icon{margin-left:4px;opacity:.35;font-size:.6rem;}
.al-table thead th.sort-asc .sort-icon,.al-table thead th.sort-desc .sort-icon{opacity:1;color:#c4b5fd;}
.al-table tbody .al-row{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;cursor:pointer;}
.al-table tbody .al-row:last-child{border-bottom:none;}
.al-table tbody .al-row:hover{background:rgba(109,92,255,.08);}
.al-table tbody td{padding:13px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8);}

/* ── Cols ── */
.al-col-id{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);}
.al-seller-wrap{display:flex;align-items:center;gap:10px;}
.al-seller-avi{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.2);display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:900;color:#c4b5fd;flex-shrink:0;overflow:hidden;}
.al-seller-avi img{width:100%;height:100%;object-fit:cover;border-radius:9px;}
.al-seller-name{font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);}
.al-seller-email{font-size:.74rem;color:rgba(255,255,255,.35);margin-top:1px;}
.al-col-discord{display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;font-weight:700;color:#c4b5fd;max-width:180px;}
.al-col-discord span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.al-col-discord i{font-size:.72rem;color:rgba(196,181,253,.6);flex-shrink:0;}
.al-col-discord--empty{color:rgba(255,255,255,.22);font-weight:600;}
.al-col-balance{font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);font-variant-numeric:tabular-nums;}
.al-col-date{font-size:.78rem;color:rgba(255,255,255,.38);}

/* ── Badges ── */
.al-badge{display:inline-flex;align-items:center;gap:.3rem;padding:4px 10px;border-radius:99px;font-size:.71rem;font-weight:800;white-space:nowrap;}
.al-badge--active{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;}
.al-badge--pending{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15;}
.al-badge--banned{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185;}

/* ── Fee badge ── */
.al-fee{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:800;}
.al-fee--override{background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.28);}
.al-fee--default{background:rgba(255,255,255,.06);color:rgba(255,255,255,.4);border:1px solid rgba(255,255,255,.10);}

/* ── Account stats ── */
.al-acc-stats{display:flex;flex-direction:column;gap:2px;}
.al-acc-line{display:inline-flex;align-items:center;gap:.3rem;font-size:.75rem;font-weight:700;}
.al-acc-listed{color:#60a5fa;}
.al-acc-sold{color:#4ade80;}

/* ── Payout pending badge ── */
.al-payout-badge{display:inline-flex;align-items:center;gap:.3rem;padding:2px 7px;border-radius:99px;font-size:.68rem;font-weight:700;background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.25);}

/* ── Manage btn ── */
.al-manage-btn{display:inline-flex;align-items:center;gap:.35rem;padding:7px 14px;border-radius:9px;font-size:.79rem;font-weight:800;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.28);color:#c4b5fd;transition:background .12s,border-color .12s;text-decoration:none;white-space:nowrap;}
.al-manage-btn:hover{background:rgba(109,92,255,.28);border-color:rgba(109,92,255,.55);color:#fff;}

/* ── Add btn ── */
.al-add-btn{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;border-radius:13px;padding:.6rem 1.4rem;font-weight:900;font-size:.9rem;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;text-decoration:none;white-space:nowrap;}
.al-add-btn:hover{opacity:.88;transform:translateY(-1px);color:#fff;}

/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}

/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}

/* ── Empty / Pagination ── */
.al-empty{text-align:center;padding:64px 24px;color:rgba(255,255,255,.35);}
.al-empty i{font-size:3rem;margin-bottom:12px;display:block;opacity:.3;}
.al-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0;}
.al-pg-btn{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s;}
.al-pg-btn:hover:not(:disabled){background:rgba(255,255,255,.09);}
.al-pg-btn.al-pg-active{background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-pg-btn:disabled{opacity:.35;cursor:not-allowed;}

/* ── Modal ── */
.al-modal .modal-content{background:#25282a;border:1px solid rgba(255,255,255,.08);border-radius:18px;overflow:hidden;}
.al-modal .modal-header{background:linear-gradient(135deg,rgba(109,92,255,.18),rgba(109,92,255,.04));border-bottom:1px solid rgba(255,255,255,.07);padding:20px 22px 16px;}
.al-modal .modal-icon{width:42px;height:42px;background:rgba(109,92,255,.18);border-radius:11px;display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.1rem;flex-shrink:0;}
.al-modal .modal-title{font-size:1rem;font-weight:800;color:#fff;margin:0;}
.al-modal .modal-subtitle{font-size:.75rem;color:rgba(255,255,255,.4);margin:0;}
.al-modal .modal-body{padding:20px 22px;}
.al-modal .modal-footer{padding:14px 22px;border-top:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.08);}
.al-modal .form-label{font-size:.78rem;font-weight:700;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;}
.al-modal .form-control{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.875rem;padding:.6rem 1rem;}
.al-modal .form-control:focus{border-color:rgba(109,92,255,.5);box-shadow:0 0 0 3px rgba(109,92,255,.15);outline:none;}
.al-modal .field-group{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:14px;margin-bottom:10px;}
.al-modal .field-group:last-child{margin-bottom:0;}
.al-modal .field-icon{width:28px;height:28px;background:rgba(109,92,255,.12);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:.78rem;flex-shrink:0;}
.al-modal .info-bar{background:rgba(109,92,255,.08);border:1px solid rgba(109,92,255,.2);border-radius:10px;padding:10px 14px;font-size:.78rem;color:rgba(255,255,255,.7);margin-bottom:14px;display:flex;align-items:flex-start;gap:.5rem;}
.al-modal .info-bar i{color:#c4b5fd;margin-top:.05rem;flex-shrink:0;}

@media only screen and (max-width:1200px){.al-table-wrap{overflow-x:auto;}.al-table{min-width:900px;}}
</style>
<?= $this->end() ?>


<!-- Stats Grid -->
<div class="sl-stats">
  <div class="sl-stat">
    <div class="sl-stat-icon" style="background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.2);color:#c4b5fd;"><i class="fa-duotone fa-store"></i></div>
    <div><div class="sl-stat-label">Total Sellers</div><div class="sl-stat-value"><?= $totalSellers ?></div></div>
  </div>
  <div class="sl-stat">
    <div class="sl-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-circle-check"></i></div>
    <div><div class="sl-stat-label">Active</div><div class="sl-stat-value"><?= $activeSellers ?></div></div>
  </div>
  <div class="sl-stat">
    <div class="sl-stat-icon" style="background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.2);color:#facc15;"><i class="fa-duotone fa-clock"></i></div>
    <div><div class="sl-stat-label">Pending</div><div class="sl-stat-value"><?= $pendingSellers ?></div></div>
  </div>
  <div class="sl-stat">
    <div class="sl-stat-icon" style="background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.2);color:#fb7185;"><i class="fa-duotone fa-ban"></i></div>
    <div><div class="sl-stat-label">Banned</div><div class="sl-stat-value"><?= $bannedSellers ?></div></div>
  </div>
  <div class="sl-stat">
    <div class="sl-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-euro-sign"></i></div>
    <div><div class="sl-stat-label">Total Balance</div><div class="sl-stat-value">€<?= number_format($totalBalance / 100, 2) ?></div></div>
  </div>
  <div class="sl-stat">
    <div class="sl-stat-icon" style="background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.2);color:#c4b5fd;"><i class="fa-duotone fa-percent"></i></div>
    <div><div class="sl-stat-label">Default Fee</div><div class="sl-stat-value"><?= $default_fee ?>%</div></div>
  </div>
</div>

<!-- Hero -->
<div class="al-hero">
  <div class="al-hero-left">
    <div class="al-hero-icon"><i class="fa-duotone fa-store"></i></div>
    <div>
      <h2 class="al-hero-title">Sellers</h2>
      <p class="al-hero-sub"><?= $totalSellers ?> seller<?= $totalSellers !== 1 ? 's' : '' ?> total</p>
    </div>
  </div>
  <button type="button" class="al-add-btn" data-bs-toggle="modal" data-bs-target="#addSellerModal">
    <i class="fa-solid fa-plus"></i> Add Seller
  </button>
</div>

<!-- Toolbar -->
<div class="al-toolbar-card">
  <div class="al-pills" id="alStatusFilters">
    <span class="al-pill" data-status="all">All</span>
    <span class="al-pill active" data-status="active"><i class="fa-solid fa-circle" style="font-size:.45rem;"></i> Active</span>
    <span class="al-pill" data-status="pending"><i class="fa-solid fa-clock" style="font-size:.7rem;"></i> Pending</span>
    <span class="al-pill" data-status="banned"><i class="fa-solid fa-ban" style="font-size:.7rem;"></i> Banned</span>
  </div>
  <div class="al-search-wrap">
    <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
    <input type="search" id="alSearch" placeholder="Search name, email or Discord…">
  </div>
</div>

<!-- Table -->
<div class="al-table-wrap">
  <table class="al-table" id="alGrid">
    <thead>
      <tr>
        <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort sort-icon"></i></th>
        <th>Seller</th>
        <th>Discord</th>
        <th>Status</th>
        <th class="sortable" data-col="fee">Fee <i class="fa-solid fa-sort sort-icon"></i></th>
        <th class="sortable" data-col="balance">Balance <i class="fa-solid fa-sort sort-icon"></i></th>
        <th class="sortable" data-col="listed">Accounts <i class="fa-solid fa-sort sort-icon"></i></th>
        <th class="sortable" data-col="sold">Sold <i class="fa-solid fa-sort sort-icon"></i></th>
        <th class="sortable" data-col="date">Joined <i class="fa-solid fa-sort sort-icon"></i></th>
        <th class="text-end">Action</th>
      </tr>
    </thead>
    <tbody id="alTbody">
      <?php if (!empty($data)): foreach ($data as $row):
        $isBanned      = (int)($row['is_banned'] ?? 0);
        $isActive      = (int)($row['is_active'] ?? 0);
        $fee           = $row['fee_percent'];
        $effectiveFee  = ($fee !== null && $fee !== '') ? (float)$fee : $default_fee;
        $isOverride    = ($fee !== null && $fee !== '');
        $accListed     = (int)($row['accounts_count'] ?? 0);
        $accSold       = (int)($row['accounts_sold']  ?? 0);
        $balance       = (int)($row['balance'] ?? 0);
        $pendingPayout = (int)($row['pending_payout'] ?? 0);
        $icon          = $row['icon'] ?? null;
        $username      = $row['username'] ?? '—';
        $email         = $row['email'] ?? '';
        // Admins search for sellers by their Discord tag, so it needs its own column
        // and has to be part of the client-side search index.
        $discord       = trim((string)($row['discord'] ?? ''));
        $createdAtTs   = !empty($row['created_at']) ? strtotime($row['created_at']) : 0;
        $createdAtFmt  = $createdAtTs ? date('d.m.Y', $createdAtTs) : '—';

        if ($isBanned)     $status = 'banned';
        elseif ($isActive) $status = 'active';
        else               $status = 'pending';

        $badgeCls  = $status === 'active' ? 'al-badge--active' : ($status === 'banned' ? 'al-badge--banned' : 'al-badge--pending');
        $badgeIcon = $status === 'active' ? 'fa-circle-check' : ($status === 'banned' ? 'fa-ban' : 'fa-clock');
        $badgeLabel= ucfirst($status);
      ?>
      <tr class="al-row"
          data-status="<?= $status ?>"
          data-search="<?= $h(strtolower($username . ' ' . $email . ' ' . $discord)) ?>"
          data-id="<?= (int)$row['id'] ?>"
          data-fee="<?= $effectiveFee ?>"
          data-balance="<?= $balance ?>"
          data-listed="<?= $accListed ?>"
          data-sold="<?= $accSold ?>"
          data-date="<?= $createdAtTs ?>"
          onclick="window.location='<?= ADMN_URL ?>/seller/<?= (int)$row['id'] ?>'">
        <td><span class="al-col-id">#<?= (int)$row['id'] ?></span></td>
        <td>
          <div class="al-seller-wrap">
            <div class="al-seller-avi">
              <?php if ($icon): ?>
                <img src="<?= $h($icon) ?>" alt="">
              <?php else: ?>
                <?= strtoupper(substr($username, 0, 1)) ?>
              <?php endif; ?>
            </div>
            <div>
              <div class="al-seller-name">
                <?= $h($username) ?>
                <?php if ($pendingPayout > 0): ?>
                  <span class="al-payout-badge" style="margin-left:5px;">
                    <i class="fa-duotone fa-money-bill-transfer" style="font-size:.65rem;"></i>
                    €<?= number_format($pendingPayout / 100, 2) ?>
                  </span>
                <?php endif; ?>
              </div>
              <div class="al-seller-email"><?= $h($email) ?></div>
            </div>
          </div>
        </td>
        <td onclick="event.stopPropagation()">
          <?php if ($discord !== ''): ?>
            <span class="al-col-discord" title="Click to copy" style="cursor:pointer;"
                  onclick="navigator.clipboard&&navigator.clipboard.writeText('<?= $h($discord) ?>')">
              <i class="fa-brands fa-discord"></i>
              <span><?= $h($discord) ?></span>
            </span>
          <?php else: ?>
            <span class="al-col-discord al-col-discord--empty">—</span>
          <?php endif; ?>
        </td>
        <td>
          <span class="al-badge <?= $badgeCls ?>">
            <i class="fa-solid <?= $badgeIcon ?>" style="font-size:.5rem;"></i>
            <?= $badgeLabel ?>
          </span>
        </td>
        <td>
          <span class="al-fee <?= $isOverride ? 'al-fee--override' : 'al-fee--default' ?>">
            <?= $effectiveFee ?>%<?= $isOverride ? ' <i class="fa-solid fa-star" style="font-size:.55rem;margin-left:2px;" title="Custom fee"></i>' : '' ?>
          </span>
        </td>
        <td><span class="al-col-balance">€<?= number_format($balance / 100, 2) ?></span></td>
        <td>
          <?php if ($accListed > 0): ?>
            <span class="al-acc-line al-acc-listed">
              <i class="fa-duotone fa-layer-group" style="font-size:.7rem;"></i> <?= $accListed ?>
            </span>
          <?php else: ?><span style="color:rgba(255,255,255,.2);">—</span><?php endif; ?>
        </td>
        <td>
          <?php if ($accSold > 0): ?>
            <span class="al-acc-line al-acc-sold">
              <i class="fa-duotone fa-check-circle" style="font-size:.7rem;"></i> <?= $accSold ?>
            </span>
          <?php else: ?><span style="color:rgba(255,255,255,.2);">—</span><?php endif; ?>
        </td>
        <td><span class="al-col-date"><?= $h($createdAtFmt) ?></span></td>
        <td class="text-end" onclick="event.stopPropagation()">
          <a class="al-manage-btn" href="<?= ADMN_URL ?>/seller/<?= (int)$row['id'] ?>">
            <i class="fa-duotone fa-eye"></i> Manage
          </a>
        </td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="10">
        <div class="al-empty">
          <i class="fa-duotone fa-store"></i>
          <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No sellers yet</div>
        </div>
      </td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Footer / Pagination -->
<div class="al-footer">
  <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
    Showing <span id="alShowing">—</span> of <span id="alTotal">—</span>
  </div>
  <div style="display:flex;gap:5px;flex-wrap:wrap;" id="alPagination"></div>
</div>


<!-- Add Seller Modal -->
<div class="modal fade al-modal" id="addSellerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
    <div class="modal-content">
      <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="hidden" name="action" value="admin_add_seller_from_client">

        <div class="modal-header">
          <div style="display:flex;align-items:center;gap:12px;">
            <div class="modal-icon"><i class="fa-duotone fa-store"></i></div>
            <div>
              <div class="modal-title">Add Seller</div>
              <div class="modal-subtitle">Create a new marketplace seller account</div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="info-bar">
            <i class="fa-duotone fa-circle-info"></i>
            <span>The seller logs in at <strong style="color:#c4b5fd;">/seller-area/auth/login</strong> using their email and password.</span>
          </div>

          <div class="field-group">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
              <div class="field-icon"><i class="fa-duotone fa-user"></i></div>
              <label class="form-label mb-0">Username <span style="color:#f87171;">*</span></label>
            </div>
            <input type="text" class="form-control" name="username" placeholder="e.g. SellerName" required>
          </div>

          <div class="field-group">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
              <div class="field-icon"><i class="fa-duotone fa-envelope"></i></div>
              <label class="form-label mb-0">Email <span style="color:#f87171;">*</span></label>
            </div>
            <input type="email" class="form-control" name="email" placeholder="seller@example.com" required>
          </div>

          <div class="field-group">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
              <div class="field-icon"><i class="fa-duotone fa-lock"></i></div>
              <label class="form-label mb-0">Password <span style="color:#f87171;">*</span></label>
            </div>
            <input type="text" class="form-control" name="password" placeholder="Set a secure password" required>
          </div>

          <div class="field-group" style="margin-bottom:0;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
              <div class="field-icon"><i class="fa-duotone fa-percent"></i></div>
              <label class="form-label mb-0">Fee Override <span style="font-size:.72rem;color:rgba(255,255,255,.3);font-weight:400;">(optional)</span></label>
            </div>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="fee_percent"
                   placeholder="Leave empty = default <?= $default_fee ?>%">
            <div class="form-text" style="margin-top:5px;font-size:.72rem;color:rgba(255,255,255,.3);">
              Leave empty to apply the platform default of <strong style="color:rgba(255,255,255,.5);"><?= $default_fee ?>%</strong>.
            </div>
          </div>
        </div>

        <div class="modal-footer" style="justify-content:space-between;">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="al-add-btn" style="padding:.55rem 1.4rem;font-size:.87rem;">
            <i class="fa-duotone fa-check"></i> Create Seller
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<?= $this->start('scripts') ?>
<script>
(function () {
  var PER_PAGE = 25;
  var filter   = 'active';
  var search   = '';
  var page     = 1;
  var sortCol  = 'balance';
  var sortDir  = 'desc';

  var tbody   = document.getElementById('alTbody');
  var allRows = tbody ? Array.from(tbody.querySelectorAll('.al-row')) : [];
  var showEl  = document.getElementById('alShowing');
  var totEl   = document.getElementById('alTotal');
  var pageEl  = document.getElementById('alPagination');
  var srchEl  = document.getElementById('alSearch');
  var pills   = document.querySelectorAll('#alStatusFilters .al-pill');
  var ths     = document.querySelectorAll('.al-table thead th.sortable');

  function getSorted(arr) {
    return arr.slice().sort(function (a, b) {
      var av = a.dataset[sortCol] || '', bv = b.dataset[sortCol] || '';
      var an = parseFloat(av), bn = parseFloat(bv);
      var cmp = (isNaN(an) || isNaN(bn)) ? String(av).localeCompare(String(bv), undefined, {numeric:true}) : an - bn;
      return sortDir === 'asc' ? cmp : -cmp;
    });
  }
  function getFiltered() {
    return allRows.filter(function (c) {
      var okStatus = filter === 'all' || c.dataset.status === filter;
      var okSearch = !search || (c.dataset.search || '').indexOf(search) !== -1;
      return okStatus && okSearch;
    });
  }
  function render() {
    var filtered = getSorted(getFiltered());
    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / PER_PAGE));
    if (page > pages) page = pages;
    var start = (page - 1) * PER_PAGE, end = start + PER_PAGE;

    allRows.forEach(function (c) { c.style.display = 'none'; });
    filtered.slice(start, end).forEach(function (c) { tbody.appendChild(c); c.style.display = ''; });

    if (showEl) showEl.textContent = total > 0 ? (start + 1) + '–' + Math.min(end, total) : '0';
    if (totEl)  totEl.textContent  = total;

    ths.forEach(function (th) {
      th.classList.remove('sort-asc', 'sort-desc');
      if (th.dataset.col === sortCol) th.classList.add('sort-' + sortDir);
    });

    if (!pageEl) return;
    pageEl.innerHTML = '';
    if (pages <= 1) return;
    function btn(label, p, disabled, active) {
      var b = document.createElement('button');
      b.className = 'al-pg-btn' + (active ? ' al-pg-active' : '');
      b.innerHTML = label; b.disabled = !!disabled;
      if (!disabled) b.addEventListener('click', function () { page = p; render(); });
      return b;
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>', page - 1, page === 1, false));
    for (var i = 1; i <= pages; i++) {
      if (pages > 7 && i > 2 && i < pages - 1 && Math.abs(i - page) > 1) {
        if (i === 3 || i === pages - 2) { var d = document.createElement('span'); d.style.cssText = 'color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;'; d.textContent = '…'; pageEl.appendChild(d); }
        continue;
      }
      pageEl.appendChild(btn(i, i, false, i === page));
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>', page + 1, page === pages, false));
  }

  pills.forEach(function (p) {
    p.addEventListener('click', function () {
      pills.forEach(function (x) { x.classList.remove('active'); });
      p.classList.add('active');
      filter = p.dataset.status || 'all';
      page = 1; render();
    });
  });
  ths.forEach(function (th) {
    th.addEventListener('click', function () {
      var col = th.dataset.col;
      if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      else { sortCol = col; sortDir = 'desc'; }
      page = 1; render();
    });
  });
  if (srchEl) srchEl.addEventListener('input', function () {
    search = srchEl.value.trim().toLowerCase(); page = 1; render();
  });

  render();
})();
</script>
<?= $this->end() ?>
