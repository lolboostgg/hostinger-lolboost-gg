<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'E-Girls - Admin Area | LoLBoost.gg', 'h1' => 'E-Girls', 'description' => 'Manage E-Girls.']]) ?>

<style>
/* Wider egirl admin pages, matched to booster admin layout. */
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
</style>

<style>
.seller-stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(175px,1fr)); gap:.85rem; margin-bottom:1.5rem; }
.stat-card { background:#25282a; border:.0625rem solid #2f3235; border-radius:.75rem; padding:1.1rem 1.25rem; display:flex; align-items:center; gap:.9rem; transition:transform .15s,box-shadow .15s; box-shadow:0 .375rem .75rem rgba(30,32,34,.2); }
.stat-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.35); }
.stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.stat-icon.c-teal   { background:rgba(0,201,167,.13);   color:#00c9a7; }
.stat-icon.c-blue   { background:rgba(9,165,190,.13);   color:#09a5be; }
.stat-icon.c-red    { background:rgba(237,76,120,.13);  color:#ed4c78; }
.stat-icon.c-amber  { background:rgba(245,202,153,.13); color:#f5ca99; }
.stat-icon.c-purple { background:rgba(92,74,227,.13);   color:#9b8bf0; }
.stat-label { font-size:.7rem; color:#91989e; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.1rem; }
.stat-value { font-size:1.3rem; font-weight:700; color:#c5c8cc; line-height:1.2; }
.status-filter-wrap { display:flex; align-items:center; gap:.4rem; padding:.75rem 1.3125rem; border-bottom:.0625rem solid #2f3235; background:rgba(0,0,0,.10); }
.status-filter-wrap label { font-size:.75rem; color:#91989e; margin:0 .2rem 0 0; }
.status-pill { display:inline-flex; align-items:center; gap:.3rem; padding:.28rem .75rem; border-radius:50rem; font-size:.78rem; font-weight:600; cursor:pointer; border:1px solid #2f3235; transition:all .15s; background:transparent; color:#91989e; }
.status-pill:hover { color:#c5c8cc; border-color:#4b5055; }
.status-pill.active-pill              { color:#1e2022; background:#00c9a7; border-color:#00c9a7; }
.status-pill.pending-pill.active-pill { color:#000;   background:#f5ca99; border-color:#f5ca99; }
.status-pill.banned-pill.active-pill  { color:#fff;   background:#ed4c78; border-color:#ed4c78; }
.pill-dot { width:7px; height:7px; border-radius:50%; background:currentColor; opacity:.85; }
.btn-add-egirl { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; background:linear-gradient(135deg,#5c4ae3 0%,#7c6af0 100%); border:none; border-radius:.5rem; color:#fff; font-size:.82rem; font-weight:600; cursor:pointer; transition:all .15s; box-shadow:0 4px 14px rgba(92,74,227,.35); white-space:nowrap; text-decoration:none; }
.btn-add-egirl:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(92,74,227,.5); color:#fff; }
.btn-add-egirl .add-icon { width:22px; height:22px; background:rgba(255,255,255,.2); border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.75rem; }
</style>

<?php
$total    = count($egirls ?? []);
$active   = count(array_filter($egirls ?? [], fn($e) => !$e['is_banned'] && ($e['verified'] ?? 0)));
$banned   = count(array_filter($egirls ?? [], fn($e) => $e['is_banned'] && ($e['verified'] ?? 0)));
$pending  = count(array_filter($egirls ?? [], fn($e) => !($e['verified'] ?? 0)));

$totalBal = array_sum(array_column($egirls ?? [], 'balance'));

/*
 * Discord fallback for the E-Girls list.
 * Some admin egirl list queries do not select boosters.discord, even though the
 * value exists in the boosters table. This small fallback loads the Discord
 * names by booster ID so the list can show them without changing the controller.
 */
$egirlDiscordMap = [];
try {
    global $db;
    $egirlIds = array_values(array_filter(array_map('intval', array_column($egirls ?? [], 'id'))));
    if (!empty($egirlIds) && isset($db)) {
        $placeholders = implode(',', array_fill(0, count($egirlIds), '?'));
        $discordRows = $db->run("SELECT id, discord FROM boosters WHERE id IN ($placeholders)", ...$egirlIds) ?: [];
        foreach ($discordRows as $discordRow) {
            $egirlDiscordMap[(int)$discordRow['id']] = (string)($discordRow['discord'] ?? '');
        }
    }
} catch (\Throwable $e) {
    $egirlDiscordMap = [];
}
?>


<div class="seller-stats-grid">
    <div class="stat-card"><div class="stat-icon c-blue"><i class="fa-duotone fa-star-shooting"></i></div><div><div class="stat-label">Total E-Girls</div><div class="stat-value"><?= $total ?></div></div></div>
    <div class="stat-card"><div class="stat-icon c-teal"><i class="fa-duotone fa-circle-check"></i></div><div><div class="stat-label">Active</div><div class="stat-value"><?= $active ?></div></div></div>
    <div class="stat-card"><div class="stat-icon c-amber"><i class="fa-duotone fa-clock"></i></div><div><div class="stat-label">Pending</div><div class="stat-value"><?= $pending ?></div></div></div>
    <div class="stat-card"><div class="stat-icon c-red"><i class="fa-duotone fa-ban"></i></div><div><div class="stat-label">Banned</div><div class="stat-value"><?= $banned ?></div></div></div>
    <div class="stat-card"><div class="stat-icon c-teal"><i class="fa-duotone fa-euro-sign"></i></div><div><div class="stat-label">Total Balance</div><div class="stat-value">€<?= number_format($totalBal / 100, 2) ?></div></div></div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md"><h5 class="card-header-title">E-Girls</h5></div>
            <div class="col-auto d-flex align-items-center gap-2">
                <div class="input-group input-group-merge input-group-flush">
                    <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
                    <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search E-Girls">
                </div>
            </div>
        </div>
    </div>

    <div class="status-filter-wrap">
        <label>Status</label>
        <button type="button" class="status-pill active-pill" data-status="">All</button>
        <button type="button" class="status-pill" data-status="active"><span class="pill-dot" style="background:#00c9a7"></span>Active</button>
        <button type="button" class="status-pill pending-pill" data-status="pending"><span class="pill-dot" style="background:#f5ca99"></span>Pending</button>
        <button type="button" class="status-pill banned-pill" data-status="banned"><span class="pill-dot" style="background:#ed4c78"></span>Banned</button>
    </div>

    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               data-hs-datatables-options='{
                   "columnDefs": [{"targets": [8], "orderable": false}],
                   "order": [[6, "desc"]],
                   "info": {"totalQty": "#datatableEntriesInfoTotalQty"},
                   "entries": "#datatableEntries",
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
               }' id="egirls_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th><th>E-Girl</th><th>Discord</th><th>Games</th><th>Sessions</th><th>Rating</th>
                    <th class="text-end">Balance</th><th>Status</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($egirls)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-5">No E-Girls yet. <a href="<?= ADMN_URL ?>/egirl/add">Add one</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($egirls as $eg):
                        $isVerified = (int)($eg['verified'] ?? 0);
                        $isBanned   = (int)($eg['is_banned'] ?? 0);
                        // is_banned=1 + verified=0 = pending onboarding (system flag, not real ban)
                        // is_banned=1 + verified=1 = actually banned
                        if ($isBanned && $isVerified) $statusAttr = 'banned';
                        elseif (!$isVerified)          $statusAttr = 'pending';
                        else                           $statusAttr = 'active';
                    ?>
                    <tr data-status="<?= $statusAttr ?>">
                        <td class="fw-500"><a href="<?= ADMN_URL ?>/egirl/<?= $eg['id'] ?>">#<?= $eg['id'] ?></a></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($eg['icon']): ?>
                                    <img src="<?= htmlspecialchars($eg['icon']) ?>" class="avatar avatar-sm avatar-circle" alt="">
                                <?php else: ?>
                                    <span class="avatar avatar-sm avatar-circle avatar-soft-primary fw-bold" style="display:flex;align-items:center;justify-content:center;font-size:.75rem"><?= strtoupper(substr($eg['username'],0,1)) ?></span>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-500"><?= htmlspecialchars($eg['username']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($eg['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="fw-500">
                            <?php
                                $egirlDiscord = trim((string)($eg['discord'] ?? ($egirlDiscordMap[(int)$eg['id']] ?? '')));
                                if ($egirlDiscord === '' && isset($egirlDiscordMap[(int)$eg['id']])) {
                                    $egirlDiscord = trim((string)$egirlDiscordMap[(int)$eg['id']]);
                                }
                            ?>
                            <?php if ($egirlDiscord !== ''): ?>
                                <span class="d-inline-flex align-items-center gap-1"><i class="fa-brands fa-discord text-primary"></i><?= htmlspecialchars($egirlDiscord) ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                <?php foreach (explode('|', $eg['games'] ?? '') as $g):
                                    $gameKey = strtolower(trim((string)$g));
                                    if ($gameKey === '') continue;
                                    // util_game_icon_url() knows the static lol/val/tft/… assets and falls
                                    // back to the icon set in the admin Games area, so newly added games
                                    // (Fortnite, CS2, GTA VI, …) get a real icon instead of a text badge.
                                    $gameIcon = function_exists('util_game_icon_url') ? util_game_icon_url($gameKey) : '';
                                    $gameLabel = function_exists('util_game_display_name')
                                        ? util_game_display_name($gameKey)
                                        : ucwords(str_replace(['-', '_'], ' ', $gameKey));
                                    $gameLabel = htmlspecialchars($gameLabel, ENT_QUOTES, 'UTF-8');
                                ?>
                                    <?php if ($gameIcon !== ''): ?>
                                        <img src="<?= htmlspecialchars($gameIcon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $gameLabel ?>" title="<?= $gameLabel ?>" style="width:24px;height:24px;object-fit:contain;border-radius:6px;">
                                    <?php else: ?>
                                        <span class="badge bg-soft-secondary text-secondary" title="<?= $gameLabel ?>"><?= $gameLabel ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td><?= (int)($eg['total_sessions'] ?? 0) ?></td>
                        <td>
                            <?php if (($eg['review_count'] ?? 0) > 0): ?>
                                <span class="text-warning">★</span> <?= number_format($eg['review_avg'], 1) ?>
                                <small class="text-muted">(<?= (int)$eg['review_count'] ?>)</small>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <?php // data-order keeps the raw cents so DataTables sorts numerically, not by the formatted "€1,234.50" string. ?>
                        <td class="text-end fw-500" data-order="<?= (int)($eg['balance'] ?? 0) ?>">€<?= number_format(($eg['balance'] ?? 0) / 100, 2) ?></td>
                        <td>
                            <?php if ($isBanned && $isVerified): ?>
                                <span class="badge bg-soft-danger text-danger">Banned</span>
                            <?php elseif (!$isVerified): ?>
                                <span class="badge bg-soft-warning text-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge bg-soft-success text-success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= ADMN_URL ?>/egirl/<?= $eg['id'] ?>" class="btn btn-white btn-sm">
                                    <i class="fa-duotone fa-eye me-1 fs-6"></i> View
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="4">4</option><option value="6">6</option><option value="12" selected>12</option><option value="25">25</option>
                        </select>
                    </div>
                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto"><nav id="datatableWithSearchPagination"></nav></div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    HSCore.components.HSDatatables.init($('#egirls_table'), {
        language: { zeroRecords: '<div class="text-center p-4 text-muted">No data to show</div>' }
    });
    var dt = $('#egirls_table').DataTable();
    var activeStatus = '';
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'egirls_table') return true;
        if (!activeStatus) return true;
        return ($(dt.row(dataIndex).node()).data('status') || '') === activeStatus;
    });
    $('.status-pill').on('click', function () {
        $('.status-pill').removeClass('active-pill');
        $(this).addClass('active-pill');
        activeStatus = $(this).data('status');
        dt.draw();
    });
});
</script>
<?= $this->end() ?>
