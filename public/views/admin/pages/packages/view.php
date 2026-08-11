<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Account Package #' . $data['id'] . ' - Admin Area | LoLBoost.gg', 'h1' => 'Account Package #' . $data['id'], 'description' => 'Create new LoL package.'], 'contain' => false]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
/* ── Full-width override for this page ── */
#content.main .content.container,
.content.container {
    max-width: 100% !important;
    width: 100% !important;
    padding-left: 1.5rem !important;
    padding-right: 1.5rem !important;
}
.row.justify-content-lg-center > .col-lg-10 {
    max-width: 100% !important;
    flex: 0 0 100% !important;
    width: 100% !important;
}

.pkg-ready-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.pkg-ready-option{display:flex;gap:.7rem;align-items:flex-start;padding:.8rem;border-radius:.7rem;background:#1e2022;border:1px solid #2f3235;cursor:pointer;}
.pkg-ready-option:hover{border-color:#5c4ae3;}
.pkg-ready-option input{margin-top:.18rem;}
.pkg-ready-option strong{display:block;color:#fff;font-size:.86rem;}
.pkg-ready-option small{display:block;color:#91989e;font-size:.72rem;line-height:1.35;margin-top:.15rem;}
.pkg-feature-row{display:flex;gap:.5rem;align-items:center;}
.pkg-feature-row .form-control{flex:1;}
.pkg-ready-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .55rem;border-radius:999px;font-size:.72rem;font-weight:800;border:1px solid rgba(0,201,167,.35);background:rgba(0,201,167,.12);color:#00c9a7;}
.pkg-ready-badge.not{border-color:rgba(245,202,153,.35);background:rgba(245,202,153,.12);color:#f5ca99;}
@media(max-width:768px){.pkg-ready-grid{grid-template-columns:1fr}}
</style>
<?= $this->end() ?>

<style>
/* ── Theme tokens ───────────────────────────────────────────────────────────
   body bg: #1e2022 | card bg: #25282a | border: #2f3235 | text: #c5c8cc
   teal: #00c9a7 | danger: #ed4c78 | primary: #5c4ae3 | amber: #f5ca99
   info: #09a5be | muted: #91989e
   ──────────────────────────────────────────────────────────────────────── */

/* ── Status badges ── */
.acct-status-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .6rem; border-radius: 20px;
    font-size: .73rem; font-weight: 600; white-space: nowrap;
}
.asb-available { background: rgba(0,201,167,.12);  color: #00c9a7; border: 1px solid rgba(0,201,167,.28); }
.asb-sold      { background: rgba(237,76,120,.13); color: #ed4c78; border: 1px solid rgba(237,76,120,.28); }
.asb-reserved  { background: rgba(245,202,153,.12);color: #f5ca99; border: 1px solid rgba(245,202,153,.28); }
.asb-cashflow  { background: rgba(9,165,190,.12);  color: #09a5be; border: 1px solid rgba(9,165,190,.28); }
.asb-level     { background: rgba(255,171,0,.12);  color: #ffab00; border: 1px solid rgba(255,171,0,.28); }
.asb-login     { background: rgba(237,76,120,.13); color: #ed4c78; border: 1px solid rgba(237,76,120,.28); }
.asb-other     { background: rgba(109,116,123,.10);color: #8c98a4; border: 1px solid rgba(109,116,123,.20); }

/* ── Action buttons (always visible) ── */
.btn-tbl {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .7rem; border-radius: .45rem;
    font-size: .75rem; font-weight: 600; cursor: pointer;
    border: 1px solid; transition: all .15s ease; white-space: nowrap; text-decoration: none;
}
.btn-tbl-view   { background: rgba(92,74,227,.10);  border-color: rgba(92,74,227,.35);  color: #9b8bf0; }
.btn-tbl-view:hover { background: rgba(92,74,227,.22); border-color: rgba(92,74,227,.6); color: #c4b8ff; }
.btn-tbl-delete { background: rgba(237,76,120,.10); border-color: rgba(237,76,120,.35); color: #ed4c78; }
.btn-tbl-delete:hover { background: rgba(237,76,120,.22); border-color: rgba(237,76,120,.6); color: #ff8fab; }
.btn-tbl-add    { background: #5c4ae3; border-color: #5c4ae3; color: #fff; }
.btn-tbl-add:hover { background: #6d5ef0; border-color: #6d5ef0; }

/* ── Admin badge ── */
.admin-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .75rem; font-weight: 600; color: #c5c8cc;
    background: rgba(92,74,227,.10); border: 1px solid rgba(92,74,227,.28); border-radius: 20px;
    padding: .2rem .55rem; white-space: nowrap;
}
.admin-chip .admin-icon { color: #9b8bf0; font-size: .72rem; }
.admin-chip .admin-avatar {
    width: 18px; height: 18px; border-radius: 50%; object-fit: cover;
    border: 1px solid rgba(155,139,240,.45); background: rgba(0,0,0,.20); flex-shrink: 0;
}
.admin-chip-muted { background: rgba(109,116,123,.10); color: #8c98a4; border-color: rgba(109,116,123,.20); }

/* ── Buyer badge shown below Sold accounts ── */
.buyer-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    margin-top: .35rem; padding: .18rem .5rem; border-radius: 20px;
    font-size: .72rem; font-weight: 600; color: #c5c8cc;
    background: rgba(245,202,153,.10); border: 1px solid rgba(245,202,153,.25);
    white-space: nowrap;
}
.buyer-chip .buyer-label { color: #f5ca99; }
.buyer-chip .buyer-icon { color: #f5ca99; font-size: .72rem; }


/* ── Status filter pills ── */
.status-filter-pills {
    display: inline-flex; align-items: center; gap: .45rem; flex-wrap: wrap;
}
.status-filter-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    border: 1px solid #3a3d40; background: rgba(255,255,255,.03);
    color: #c5c8cc; border-radius: 999px; padding: .32rem .75rem;
    font-size: .78rem; font-weight: 700; line-height: 1; cursor: pointer;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}
.status-filter-pill:hover { background: rgba(255,255,255,.06); border-color: #4b5055; color: #fff; }
.status-filter-pill.is-active { background: rgba(92,74,227,.18); border-color: rgba(155,139,240,.75); color: #b9adff; }
.status-filter-pill .pill-dot { width: .42rem; height: .42rem; border-radius: 50%; display: inline-block; background: #91989e; }
.status-filter-pill[data-status="Available"] .pill-dot { background: #00c9a7; }
.status-filter-pill[data-status="Sold"] .pill-dot { background: #ed4c78; }
.status-filter-pill[data-status="Banned"] .pill-dot { background: #f5ca99; }
.status-filter-pill[data-status="Cashflow"] .pill-dot { background: #09a5be; }
.status-filter-pill[data-status="Level not matching"] .pill-dot { background: #ffab00; }
.status-filter-pill[data-status="Logins not working"] .pill-dot { background: #ed4c78; }
.status-filter-pill .pill-check { display:none; }
.status-filter-pill.is-active .pill-check { display:inline-block; }

/* ── Clickable buyer profile chip ── */
.buyer-chip.buyer-profile-link {
    color: #00c9a7; text-decoration: none; background: rgba(0,201,167,.10);
    border-color: rgba(0,201,167,.28);
}
.buyer-chip.buyer-profile-link:hover { background: rgba(0,201,167,.18); border-color: rgba(0,201,167,.5); color: #5ff0d8; }
.buyer-chip .buyer-avatar {
    width: 18px; height: 18px; border-radius: 50%; object-fit: cover;
    border: 1px solid rgba(0,201,167,.4); background: rgba(0,0,0,.20); flex-shrink: 0;
}
.buyer-chip .buyer-name { color: inherit; }

/* ── Credential chip ── */
.cred-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: .78rem; color: #c5c8cc;
    background: rgba(0,0,0,.2); border: 1px solid #2f3235; border-radius: .4rem;
    padding: .2rem .55rem; cursor: pointer; transition: border-color .15s;
    max-width: none; width: max-content; overflow: visible; text-overflow: clip; white-space: nowrap;
    position: relative;
}
.cred-chip:hover { border-color: #4b5055; }
.cred-chip .copy-icon { color: #91989e; font-size: .7rem; flex-shrink: 0; }
.cred-chip.copied { border-color: rgba(0,201,167,.5); color: #00c9a7; }
#accounts_table td:nth-child(2),
#accounts_table td:nth-child(3) { min-width: 190px; white-space: nowrap; }
#accounts_table td:nth-child(4) { min-width: 420px; white-space: normal; overflow-wrap: anywhere; }

/* ── Delete confirm modal ── */
#acct-delete-modal {
    display: none; position: fixed; inset: 0; z-index: 10000;
    background: rgba(0,0,0,.65); align-items: center; justify-content: center;
}
#acct-delete-modal.is-open { display: flex; }
.acct-modal-box {
    background: #25282a; border: 1px solid #2f3235; border-radius: 1rem;
    padding: 2rem; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,.5);
}
.acct-modal-box h5 { font-size: 1rem; font-weight: 700; color: #c5c8cc; margin-bottom: .4rem; }
.acct-modal-box p  { font-size: .85rem; color: #91989e; margin-bottom: 1.5rem; }
.acct-modal-actions { display: flex; gap: .6rem; justify-content: flex-end; }

/* ── Settings form rows ── */
.pkg-form-row {
    display: grid; grid-template-columns: 220px 1fr;
    align-items: center; gap: 1rem;
    padding: .85rem 0; border-bottom: 1px solid #2f3235;
}
.pkg-form-row:last-child { border-bottom: none; }
.pkg-form-label { font-size: .82rem; font-weight: 600; color: #91989e; }
</style>

<?php
$decodeAccountEntity = static function ($value): string {
    return html_entity_decode((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
};
$hAccountEntity = static function ($value) use ($decodeAccountEntity): string {
    return htmlspecialchars($decodeAccountEntity($value), ENT_QUOTES, 'UTF-8');
};
?>

<!-- ── Account Delete Confirm Modal ── -->
<div id="acct-delete-modal" role="dialog" aria-modal="true">
    <div class="acct-modal-box">
        <h5><i class="fa-duotone fa-triangle-exclamation me-2" style="color:#ed4c78;"></i>Delete Account</h5>
        <p>Are you sure you want to delete account <strong id="del-acct-label"></strong>?
           This action cannot be undone.</p>
        <div class="acct-modal-actions">
            <button type="button" class="btn-tbl btn-tbl-view" id="del-acct-cancel">Cancel</button>
            <button type="button" class="btn-tbl btn-tbl-delete" id="del-acct-confirm">
                <i class="fa-duotone fa-trash"></i> Delete account
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     ACCOUNTS LIST CARD
     ═══════════════════════════════════════════════════════════════════════════ -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between w-100 gap-3">
            <h5 class="card-header-title mb-0">
                <i class="fa-duotone fa-table-list me-2"></i>Accounts List
            </h5>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <div id="accountStatusFilter" class="status-filter-pills" role="group" aria-label="Filter accounts by status">
                    <button type="button" class="status-filter-pill is-active" data-status="">
                        <i class="fa-duotone fa-layer-group pill-check"></i> All
                    </button>
                    <button type="button" class="status-filter-pill" data-status="Available"><span class="pill-dot"></span>Available</button>
                    <button type="button" class="status-filter-pill" data-status="Sold"><i class="fa-duotone fa-check pill-check"></i>Sold</button>
                    <button type="button" class="status-filter-pill" data-status="Banned"><span class="pill-dot"></span>Banned</button>
                    <button type="button" class="status-filter-pill" data-status="Cashflow"><span class="pill-dot"></span>Cashflow</button>
                    <button type="button" class="status-filter-pill" data-status="Level not matching"><span class="pill-dot"></span>Level issue</button>
                    <button type="button" class="status-filter-pill" data-status="Logins not working"><span class="pill-dot"></span>Login issue</button>
                </div>
                <div class="input-group input-group-merge input-group-flush" style="width:220px;">
                    <div class="input-group-prepend input-group-text">
                        <i class="fa-duotone fa-search"></i>
                    </div>
                    <input id="datatableWithSearchInput" type="search" class="form-control"
                           placeholder="Search accounts…" aria-label="Search Accounts">
                </div>
                <button type="button" class="btn-tbl btn-tbl-add" data-bs-toggle="modal" data-bs-target="#add_account_md">
                    <i class="fa-duotone fa-plus"></i> Add Account
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-align-middle card-table"
               data-hs-datatables-options='{
                   "columnDefs": [{"targets": [7], "orderable": false}],
                   "order": [[6, "desc"]],
                   "info":    {"totalQty": "#datatableEntriesInfoTotalQty"},
                   "entries": "#datatableEntries",
                   "search":  "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
               }'
               id="accounts_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Password</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Listed By</th>
                    <th class="text-end">Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['accounts'] as $row):
                $rawStatus = strtolower((string)($row['status'] ?? ''));
                $sBadge = match(true) {
                    in_array($rawStatus, ['0', 'available']) => ['cls'=>'asb-available','icon'=>'fa-circle-check','label'=>'Available'],
                    in_array($rawStatus, ['1', 'sold'])      => ['cls'=>'asb-sold',     'icon'=>'fa-circle-xmark','label'=>'Sold'],
                    in_array($rawStatus, ['2', 'banned'])    => ['cls'=>'asb-reserved', 'icon'=>'fa-ban',         'label'=>'Banned'],
                    in_array($rawStatus, ['3', 'cashflow'])  => ['cls'=>'asb-cashflow', 'icon'=>'fa-rotate',      'label'=>'Cashflow'],
                    in_array($rawStatus, ['4', 'level_not_matching', 'level not matching']) => ['cls'=>'asb-level', 'icon'=>'fa-triangle-exclamation', 'label'=>'Level not matching'],
                    in_array($rawStatus, ['5', 'logins_not_working', 'logins not working']) => ['cls'=>'asb-login', 'icon'=>'fa-key', 'label'=>'Logins not working'],
                    default                                   => ['cls'=>'asb-other',    'icon'=>'fa-circle-minus','label'=>ucfirst($rawStatus)],
                };
            ?>
            <tr>
                <!-- ID -->
                <td>
                    <a href="<?= ADMN_URL ?>/account/<?= (int)$row['id'] ?>"
                       style="color:#9b8bf0;font-weight:600;text-decoration:none;font-size:.85rem;">
                        #<?= (int)$row['id'] ?>
                    </a>
                </td>

                <!-- Login -->
                <td>
                    <span class="cred-chip js-copy" data-value="<?= $hAccountEntity($row['login'] ?? '') ?>"
                          title="Click to copy">
                        <i class="fa-duotone fa-copy copy-icon"></i>
                        <?= $hAccountEntity($row['login'] ?? '—') ?>
                    </span>
                </td>

                <!-- Password -->
                <td>
                    <span class="cred-chip js-copy" data-value="<?= $hAccountEntity($row['password'] ?? '') ?>"
                          title="Click to copy">
                        <i class="fa-duotone fa-copy copy-icon"></i>
                        <?= $hAccountEntity($row['password'] ?? '—') ?>
                    </span>
                </td>

                <!-- Data -->
                <td style="font-size:.83rem;color:#91989e;min-width:420px;white-space:normal;overflow-wrap:anywhere;">
                    <?= $hAccountEntity($row['data'] ?? '—') ?>
                </td>

                <!-- Status -->
                <td data-search="<?= htmlspecialchars($sBadge['label'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="acct-status-badge <?= $sBadge['cls'] ?>">
                        <i class="fa-duotone <?= $sBadge['icon'] ?>" style="font-size:.75rem;"></i>
                        <?= $sBadge['label'] ?>
                    </span>
                    <?php
                        $isSold       = in_array($rawStatus, ['1', 'sold'], true);
                        $buyerEmail   = trim((string)($row['buyer_email'] ?? ''));
                        $buyerId      = (int)($row['client_id'] ?? 0);
                        $buyerNameRaw = trim((string)($row['buyer_username'] ?? ''));
                        $buyerIcon    = trim((string)($row['buyer_icon'] ?? ''));
                        $buyerLabel   = $buyerId > 0 ? ($buyerNameRaw !== '' ? $buyerNameRaw : 'Guest#'.$buyerId) : 'Guest#—';
                        $buyerTitle   = $buyerEmail !== '' ? $buyerEmail : ($buyerId > 0 ? 'Client ID: '.$buyerId : $buyerLabel);
                    ?>
                    <?php if ($isSold && $buyerId > 0): ?>
                        <a class="buyer-chip buyer-profile-link"
                           href="<?= ADMN_URL ?>/client/<?= $buyerId ?>"
                           title="Open client profile — <?= htmlspecialchars($buyerTitle, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if ($buyerIcon !== ''): ?>
                                <img class="buyer-avatar"
                                     src="<?= htmlspecialchars($buyerIcon, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($buyerLabel, ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <i class="fa-duotone fa-user buyer-icon"></i>
                            <?php endif; ?>
                            <span class="buyer-label">Buyer</span>
                            <span class="buyer-name"><?= htmlspecialchars($buyerLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php elseif ($isSold): ?>
                        <div class="buyer-chip" title="No client linked">
                            <i class="fa-duotone fa-user-slash buyer-icon"></i>
                            <span class="buyer-label">Buyer</span>
                            <span>Client ID: —</span>
                        </div>
                    <?php endif; ?>
                </td>

                <!-- Listed By -->
                <td>
                    <?php
                        $adminName  = trim((string)($row['uploaded_by_admin'] ?? ''));
                        $adminEmail = trim((string)($row['uploaded_by_admin_email'] ?? ''));
                        $adminIcon  = trim((string)($row['uploaded_by_admin_icon'] ?? ''));
                        $adminId    = (int)($row['admin_id'] ?? 0);
                        $adminLabel = $adminName !== '' ? $adminName : ($adminEmail !== '' ? $adminEmail : ($adminId > 0 ? 'Admin #'.$adminId : 'Unknown'));
                    ?>
                    <span class="admin-chip <?= $adminLabel === 'Unknown' ? 'admin-chip-muted' : '' ?>"
                          title="<?= htmlspecialchars($adminEmail !== '' ? $adminEmail : $adminLabel, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($adminIcon !== ''): ?>
                            <img class="admin-avatar"
                                 src="<?= htmlspecialchars($adminIcon, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($adminLabel, ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <i class="fa-duotone fa-user-shield admin-icon"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($adminLabel, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>

                <!-- Created At -->
                <td class="text-end" data-order="<?= htmlspecialchars((string)($row['created_at']??''),ENT_QUOTES,'UTF-8') ?>"
                    style="font-size:.82rem;color:#91989e;">
                    <?= util_format_date_display($row['created_at']) ?>
                </td>

                <!-- Actions -->
                <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <a href="<?= ADMN_URL ?>/account/<?= (int)$row['id'] ?>" class="btn-tbl btn-tbl-view">
                            <i class="fa-duotone fa-eye" style="font-size:.8rem;"></i> View
                        </a>
                        <button type="button" class="btn-tbl btn-tbl-delete js-delete-acct"
                                data-acct-id="<?= (int)$row['id'] ?>"
                                data-acct-label="#<?= (int)$row['id'] ?> – <?= $hAccountEntity($row['login'] ?? '') ?>">
                            <i class="fa-duotone fa-trash" style="font-size:.8rem;"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer / pagination -->
    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                                autocomplete="off"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                        </select>
                    </div>
                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <nav id="datatableWithSearchPagination" aria-label="Accounts pagination"></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     UPDATE PACKAGE CARD
     ═══════════════════════════════════════════════════════════════════════════ -->
<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
    <input type="hidden" name="action" value="admin_update_package">
    <input type="hidden" name="id"     value="<?= (int)$data['id'] ?>">

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <h5 class="card-header-title mb-0">
                    <i class="fa-duotone fa-pen-to-square me-2"></i>Update Account Package
                </h5>
            </div>
        </div>

        <div class="card-body pt-2 pb-0">

            <!-- Name -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Name</label>
                <input type="text" class="form-control" name="name"
                       placeholder="Package name" value="<?= htmlspecialchars((string)($data['name']??''), ENT_QUOTES,'UTF-8') ?>">
            </div>

            <!-- Game -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Game</label>
                <select name="game_id" class="form-select">
                    <option value="1" <?php if ((int)($data['game_id']??1)===1) echo 'selected'; ?>>League of Legends</option>
                    <option value="2" <?php if ((int)($data['game_id']??1)===2) echo 'selected'; ?>>Valorant</option>
                </select>
            </div>

            <!-- Rank -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Rank</label>
                <?php $gameId = (int)($data['game_id']??1); ?>
                <select name="rank" class="form-select" id="rank">
                    <?php if ($gameId === 2): ?>
                        <?php foreach ([0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Ascended',8=>'Immortal',9=>'Radiant'] as $k=>$l): ?>
                            <option value="<?= $k ?>" <?php if ((int)$data['rank']===$k) echo 'selected'; ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ([0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'] as $k=>$l): ?>
                            <option value="<?= $k ?>" <?php if ((int)$data['rank']===$k) echo 'selected'; ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Server -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Server
                    <div style="font-size:.72rem;color:#555d65;font-weight:400;margin-top:.1rem;">LoL: EUW / EUNE / NA &nbsp;|&nbsp; Valorant: EU / NA</div>
                </label>
                <select name="server" class="form-select" id="serverLabel"></select>
            </div>

            <script>
            (function () {
                const gameSelect   = document.querySelector('select[name="game_id"]');
                const serverSelect = document.getElementById('serverLabel');
                const currentGameId = <?= (int)($data['game_id']??1) ?>;
                let currentServer   = '<?= addslashes($data['server']??'euw') ?>';

                function setServerOptions(gameId) {
                    serverSelect.innerHTML = '';
                    if (parseInt(gameId, 10) === 2) {
                        serverSelect.insertAdjacentHTML('beforeend',
                            '<option value="eu">EU</option><option value="na">North America</option>');
                        if (currentServer === 'euw' || currentServer === 'eune') currentServer = 'eu';
                    } else {
                        serverSelect.insertAdjacentHTML('beforeend',
                            '<option value="euw">EU-West</option>' +
                            '<option value="eune">EU-Nordic & East</option>' +
                            '<option value="na">North America</option>');
                    }
                    const opt = serverSelect.querySelector('option[value="' + currentServer + '"]');
                    if (opt) opt.selected = true;
                }
                setServerOptions(gameSelect ? gameSelect.value : currentGameId);
                if (gameSelect) {
                    gameSelect.addEventListener('change', function () { setServerOptions(this.value); });
                }
            })();
            </script>


            <!-- Ranked Ready -->
            <div class="pkg-form-row" style="align-items:flex-start;padding-top:1rem;">
                <label class="pkg-form-label" style="padding-top:.4rem;">Ranked Status
                    <div style="font-size:.72rem;color:#555d65;font-weight:400;margin-top:.1rem;">Shown clearly on package cards</div>
                </label>
                <div class="pkg-ready-grid">
                    <label class="pkg-ready-option">
                        <input type="radio" name="ranked_ready" value="1" <?php if ((int)($data['ranked_ready'] ?? 1) === 1) echo 'checked'; ?>>
                        <span><strong><i class="fa-duotone fa-circle-check me-1"></i>Ranked Ready</strong><small>Account can directly play ranked.</small></span>
                    </label>
                    <label class="pkg-ready-option">
                        <input type="radio" name="ranked_ready" value="0" <?php if ((int)($data['ranked_ready'] ?? 1) === 0) echo 'checked'; ?>>
                        <span><strong><i class="fa-duotone fa-triangle-exclamation me-1"></i>Not Ranked Ready</strong><small>Shows: requires 10 normals.</small></span>
                    </label>
                </div>
            </div>

            <!-- Price -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Price (€)</label>
                <input type="number" step="0.01" class="form-control" name="price"
                       placeholder="0.00" value="<?= htmlspecialchars((string)($data['price']??''), ENT_QUOTES,'UTF-8') ?>">
            </div>

            <!-- Features -->
            <div class="pkg-form-row" style="align-items:flex-start;padding-top:1rem;">
                <label class="pkg-form-label" style="padding-top:.4rem;">Features</label>
                <div class="d-flex flex-column gap-2" id="pkgFeaturesWrap">
                    <?php
                    $featureValues = array_values(array_filter((array)($data['features'] ?? []), static function($v){ return trim((string)$v) !== ''; }));
                    $featureCount = max(4, count($featureValues));
                    for ($fi = 0; $fi < $featureCount; $fi++):
                    ?>
                    <div class="pkg-feature-row">
                        <input type="text" class="form-control" name="features[]"
                               placeholder="Feature #<?= $fi+1 ?>"
                               value="<?= htmlspecialchars((string)($featureValues[$fi]??''), ENT_QUOTES,'UTF-8') ?>">
                        <?php if ($fi >= 4): ?>
                        <button type="button" class="btn btn-icon btn-sm btn-soft-danger pkg-remove-feature" aria-label="Remove"><i class="fa-duotone fa-xmark"></i></button>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                    <button type="button" class="btn btn-sm btn-soft-primary" style="width:max-content;" id="pkgAddFeature">
                        <i class="fa-duotone fa-plus me-1"></i>Add custom feature
                    </button>
                </div>
            </div>

            <!-- Status -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="1" <?php if ((int)($data['status']??1)===1) echo 'selected'; ?>>Enabled</option>
                    <option value="0" <?php if ((int)($data['status']??1)===0) echo 'selected'; ?>>Disabled</option>
                </select>
            </div>

        </div><!-- /card-body -->

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label"><i class="fa-duotone fa-floppy-disk me-1"></i>Update Package</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
            </button>
        </div>
    </div>
</form>

<!-- ═══════════════════════════════════════════════════════════════════════════
     ADD ACCOUNT MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="add_account_md" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded">
            <div class="modal-close">
                <button type="button" class="btn btn-ghost-light btn-icon btn-sm"
                        data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body scroll-y pb-0">
                <div class="mb-4 text-center">
                    <h2 class="mb-1" style="font-size:1.1rem;font-weight:700;color:#c5c8cc;">
                        <i class="fa-duotone fa-plus me-2" style="color:#5c4ae3;"></i>Add Account
                    </h2>
                    <div style="font-size:.8rem;color:#91989e;">to Package #<?= (int)$data['id'] ?></div>
                </div>

                <ul class="nav nav-pills gap-2 mb-4" id="addAccountTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#singleAccountPane" type="button" role="tab">
                            <i class="fa-duotone fa-user-plus me-1"></i>Single Account
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#bulkAccountPane" type="button" role="tab">
                            <i class="fa-duotone fa-layer-plus me-1"></i>Bulk Upload
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="singleAccountPane" role="tabpanel">
                    <form class="form ajax-form" action="<?= AJAX_URL ?>">
                        <input type="hidden" name="action"     value="admin_add_account">
                        <input type="hidden" name="package_id" value="<?= (int)$data['id'] ?>">
                        <input type="hidden" name="game_id"    value="<?= (int)($data['game_id'] ?? 1) ?>">
                        <div class="modal-body scroll-y pt-0">
                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <label class="form-label" style="font-size:.8rem;color:#91989e;">Login</label>
                                    <input type="text" class="form-control" name="login" placeholder="account@email.com">
                                </div>
                                <div>
                                    <label class="form-label" style="font-size:.8rem;color:#91989e;">Password</label>
                                    <input type="text" class="form-control" name="password" placeholder="••••••••">
                                </div>
                                <div>
                                    <label class="form-label" style="font-size:.8rem;color:#91989e;">Data <span style="color:#555d65;">(optional)</span></label>
                                    <input type="text" class="form-control" name="data" placeholder="Email or recovery info">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-start gap-2">
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label"><i class="fa-duotone fa-plus me-1"></i>Add Account</span>
                                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
                            </button>
                            <button type="button" data-bs-dismiss="modal" class="btn btn-light">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="bulkAccountPane" role="tabpanel">
                    <form class="form ajax-form" action="<?= AJAX_URL ?>">
                        <input type="hidden" name="action"     value="admin_bulk_add_accounts">
                        <input type="hidden" name="package_id" value="<?= (int)$data['id'] ?>">
                        <input type="hidden" name="game_id"    value="<?= (int)($data['game_id'] ?? 1) ?>">
                        <div class="modal-body scroll-y pt-0">
                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <label class="form-label" style="font-size:.8rem;color:#91989e;">Accounts</label>
                                    <textarea class="form-control" id="accountsBulkInput" name="accounts_bulk" rows="10" placeholder="login:password:data&#10;login2,password2,recovery@email.com&#10;login3:password3"></textarea>
                                    <div class="form-text mt-2" style="color:#777f87;">
                                        One account per line. Use either Login:Password:Data or Login,Password,Data. Data is optional; additional separators remain inside Data.
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label mb-0" style="font-size:.8rem;color:#91989e;">Import Preview</label>
                                        <span id="bulkPreviewCount" style="font-size:.75rem;color:#777f87;">0 accounts</span>
                                    </div>
                                    <div style="border:1px solid #2f3235;border-radius:.6rem;overflow:auto;max-height:260px;">
                                        <table class="table table-borderless table-nowrap align-middle mb-0" style="font-size:.78rem;">
                                            <thead style="position:sticky;top:0;background:#25282a;z-index:1;">
                                                <tr>
                                                    <th style="color:#91989e;">Login</th>
                                                    <th style="color:#91989e;">Password</th>
                                                    <th style="color:#91989e;">Data</th>
                                                </tr>
                                            </thead>
                                            <tbody id="bulkAccountPreview">
                                                <tr><td colspan="3" class="text-center py-4" style="color:#60676d;">Paste accounts above to preview them.</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox" name="skip_duplicates" value="1" id="skipDuplicatesBulk" checked>
                                    <label class="form-check-label" for="skipDuplicatesBulk" style="font-size:.85rem;color:#91989e;">Skip duplicate logins in this package</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-start gap-2">
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label"><i class="fa-duotone fa-upload me-1"></i>Import Accounts</span>
                                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
                            </button>
                            <button type="button" data-bs-dismiss="modal" class="btn btn-light">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {

    /* ── Bulk account comma parser and live preview ── */
    function escapeBulkPreview(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function parseBulkAccountLine(line) {
        var firstComma = line.indexOf(',');
        var firstColon = line.indexOf(':');
        var separator = firstComma === -1
            ? (firstColon === -1 ? '' : ':')
            : (firstColon === -1 || firstComma < firstColon ? ',' : ':');
        if (!separator) return null;

        var firstSeparator = line.indexOf(separator);
        var secondSeparator = line.indexOf(separator, firstSeparator + 1);
        var login = line.slice(0, firstSeparator).trim();
        var password = (secondSeparator === -1 ? line.slice(firstSeparator + 1) : line.slice(firstSeparator + 1, secondSeparator)).trim();
        var data = secondSeparator === -1 ? '' : line.slice(secondSeparator + 1).trim();

        if (!login || !password) return null;
        return { login: login, password: password, data: data };
    }

    function updateBulkAccountPreview() {
        var raw = $('#accountsBulkInput').val() || '';
        var rows = [];
        var invalid = 0;

        raw.split(/\r?\n/).forEach(function (line) {
            line = line.trim();
            if (!line) return;
            if (/^login\s*,\s*password/i.test(line)) return;

            var parsed = parseBulkAccountLine(line);
            if (parsed) rows.push(parsed);
            else invalid++;
        });

        var $preview = $('#bulkAccountPreview');
        if (!rows.length) {
            $preview.html('<tr><td colspan="3" class="text-center py-4" style="color:#60676d;">Paste accounts above to preview them.</td></tr>');
        } else {
            var html = '';
            rows.forEach(function (row) {
                html += '<tr style="border-top:1px solid #2f3235;">'
                    + '<td style="color:#c5c8cc;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;">' + escapeBulkPreview(row.login) + '</td>'
                    + '<td style="color:#c5c8cc;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;">' + escapeBulkPreview(row.password) + '</td>'
                    + '<td style="color:#91989e;white-space:pre-wrap;min-width:320px;">' + escapeBulkPreview(row.data || '—') + '</td>'
                    + '</tr>';
            });
            $preview.html(html);
        }

        var label = rows.length + (rows.length === 1 ? ' account' : ' accounts');
        if (invalid > 0) label += ', ' + invalid + ' invalid';
        $('#bulkPreviewCount').text(label);
    }

    $(document).on('input', '#accountsBulkInput', updateBulkAccountPreview);
    $('button[data-bs-target="#bulkAccountPane"]').on('shown.bs.tab', updateBulkAccountPreview);

    /* ── DataTable ── */
    HSCore.components.HSDatatables.init($('#accounts_table'), {
        language: {
            zeroRecords: `<div class="text-center p-4">
                <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="default">
                <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="dark">
                <p class="mb-0">No accounts found</p>
            </div>`
        }
    });

    /* ── Copy credential chips ── */
    $(document).on('click', '.js-copy', function () {
        var val = $(this).data('value');
        if (!val) return;
        navigator.clipboard.writeText(val).then(() => {
            $(this).addClass('copied');
            setTimeout(() => $(this).removeClass('copied'), 1400);
        });
    });

    /* ── Status filter pills ── */
    var accountTable = $('#accounts_table').DataTable();
    $('#accountStatusFilter').on('click', '.status-filter-pill', function () {
        var value = $(this).data('status') || '';
        $('#accountStatusFilter .status-filter-pill').removeClass('is-active');
        $(this).addClass('is-active');

        if (value) {
            accountTable.column(4).search('^' + $.fn.dataTable.util.escapeRegex(value) + '$', true, false).draw();
        } else {
            accountTable.column(4).search('').draw();
        }
    });

    /* ── Delete account modal ── */
    var modal      = document.getElementById('acct-delete-modal');
    var labelEl    = document.getElementById('del-acct-label');
    var btnConfirm = document.getElementById('del-acct-confirm');
    var btnCancel  = document.getElementById('del-acct-cancel');
    var pendingId  = null;
    var pendingRow = null;

    function openDelModal(id, label, row) {
        pendingId  = id;
        pendingRow = row;
        labelEl.textContent = label;
        modal.classList.add('is-open');
    }
    function closeDelModal() {
        modal.classList.remove('is-open');
        pendingId  = null;
        pendingRow = null;
    }

    $(document).on('click', '.js-delete-acct', function () {
        openDelModal(
            $(this).data('acct-id'),
            $(this).data('acct-label'),
            $(this).closest('tr')
        );
    });

    btnCancel.addEventListener('click', closeDelModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeDelModal(); });

    btnConfirm.addEventListener('click', async function () {
        if (!pendingId) return;

        var btn  = btnConfirm;
        var orig = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = '<i class="fa-duotone fa-spinner fa-spin"></i> Deleting…';

        try {
            var fd = new FormData();
            fd.append('action', 'admin_delete_package_account');
            fd.append('id',     pendingId);

            var res  = await fetch('<?= BASE_URL ?>/ajax', { method: 'POST', body: fd });
            var json = await res.json();

            if (json.status === 'success' || json.success) {
                if (pendingRow) {
                    var dt = $('#accounts_table').DataTable();
                    dt.row(pendingRow).remove().draw();
                }
                closeDelModal();
                if (json.sendToast && window.HSToastr) {
                    HSToastr[json.sendToast.type]?.(json.sendToast.message, json.sendToast.title);
                }
            } else {
                var msg = (json.sendToast && json.sendToast.message) || json.message || 'Could not delete account.';
                alert(msg);
                btn.disabled  = false;
                btn.innerHTML = orig;
            }
        } catch (err) {
            alert('Network error – account not deleted.');
            btn.disabled  = false;
            btn.innerHTML = orig;
        }
    });

});
</script>
<?= $this->end() ?>
