<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Packages List - Admin Area | LoLBoost.gg', 'h1' => 'Packages List', 'description' => 'View the Packages List.']]) ?>

<style>
/* ── Theme tokens ───────────────────────────────────────────────────────────
   body bg: #1e2022 | card bg: #25282a | border: #2f3235 | text: #c5c8cc
   teal: #00c9a7 | danger: #ed4c78 | primary: #5c4ae3 | amber: #f5ca99
   info: #09a5be | muted: #91989e
   ──────────────────────────────────────────────────────────────────────── */

/* ── Filter pill bar ── */
.pkg-filter-wrap {
    display: flex; align-items: center; gap: .4rem;
    padding: .75rem 1.3125rem;
    border-bottom: .0625rem solid #2f3235;
    background: rgba(0,0,0,.10);
    flex-wrap: wrap;
}
.pkg-filter-wrap > label {
    font-size: .75rem; color: #91989e; margin: 0 .25rem 0 0;
}
.pkg-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .28rem .75rem; border-radius: 50rem;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1px solid #2f3235; background: transparent; color: #91989e;
    transition: all .15s ease; text-decoration: none;
}
.pkg-pill:hover { color: #c5c8cc; border-color: #4b5055; }
.pkg-pill.active-pill                { color: #1e2022; background: #00c9a7; border-color: #00c9a7; }
.pkg-pill.pill-enabled.active-pill   { color: #1e2022; background: #00c9a7; border-color: #00c9a7; }
.pkg-pill.pill-disabled.active-pill  { color: #fff;    background: #ed4c78; border-color: #ed4c78; }
.pkg-pill.pill-lol.active-pill       { color: #fff;    background: #f5ca99; border-color: #f5ca99; color: #1e2022; }
.pkg-pill.pill-val.active-pill       { color: #fff;    background: #ed4c78; border-color: #ed4c78; }
.pkg-pill.pill-server.active-pill    { color: #fff;    background: #5c4ae3; border-color: #5c4ae3; }
.pkg-pill .pill-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; opacity: .7; }
.pill-sep { width: 1px; height: 18px; background: #2f3235; margin: 0 .15rem; flex-shrink: 0; }

/* ── Availability badge ── */
.avail-badge {
    display: inline-flex; align-items: center;
    padding: .15rem .55rem; border-radius: 20px;
    font-size: .7rem; font-weight: 700; margin-left: 0;
    flex-shrink: 0; white-space: nowrap;
}
.pkg-name-cell { min-width: 430px; max-width: none !important; }
.pkg-name-wrap { display: inline-flex; align-items: center; gap: .55rem; flex-wrap: nowrap; width: max-content; }
.pkg-name-link { color:#c5c8cc; text-decoration:none; font-weight:600; font-size:.88rem; white-space: nowrap; }
.pkg-server-cell { min-width: 170px; white-space: nowrap; }
.avail-0  { background:#40232a; color:#ff6b6b; border:1px solid rgba(237,76,120,.25); }
.avail-low{ background:#40351f; color:#f7c948; border:1px solid rgba(245,202,153,.25); }
.avail-ok { background:#1f3b2d; color:#4ade80; border:1px solid rgba(74,222,128,.20); }

/* ── Status badge ── */
.status-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .6rem; border-radius: 20px;
    font-size: .73rem; font-weight: 600; white-space: nowrap;
}
.sb-enabled  { background:rgba(0,201,167,.12);  color:#00c9a7; border:1px solid rgba(0,201,167,.28); }
.sb-disabled { background:rgba(237,76,120,.13); color:#ed4c78; border:1px solid rgba(237,76,120,.28); }
.sb-sold-out { background:rgba(245,202,153,.12);color:#f5ca99; border:1px solid rgba(245,202,153,.28); }

/* ── Action buttons ── */
.btn-tbl {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .7rem; border-radius: .45rem;
    font-size: .75rem; font-weight: 600; cursor: pointer;
    border: 1px solid; transition: all .15s ease; white-space: nowrap; text-decoration: none;
}
.btn-tbl-view   { background:rgba(92,74,227,.10); border-color:rgba(92,74,227,.35); color:#9b8bf0; }
.btn-tbl-view:hover { background:rgba(92,74,227,.22); border-color:rgba(92,74,227,.6); color:#c4b8ff; }
.btn-tbl-delete { background:rgba(237,76,120,.10); border-color:rgba(237,76,120,.35); color:#ed4c78; }
.btn-tbl-delete:hover { background:rgba(237,76,120,.22); border-color:rgba(237,76,120,.6); color:#ff8fab; }

/* ── Game icon chip ── */
.game-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .1rem .45rem; border-radius: .35rem;
    font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); color: #91989e;
}
.game-chip img { width:14px; height:14px; object-fit:contain; }

/* ── Delete confirm modal ── */
#pkg-delete-modal {
    display: none; position: fixed; inset: 0; z-index: 10000;
    background: rgba(0,0,0,.65); align-items: center; justify-content: center;
}
#pkg-delete-modal.is-open { display: flex; }
.pkg-modal-box {
    background: #25282a; border: 1px solid #2f3235; border-radius: 1rem;
    padding: 2rem; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,.5);
}
.pkg-modal-box h5 { font-size: 1rem; font-weight: 700; color: #c5c8cc; margin-bottom: .4rem; }
.pkg-modal-box p  { font-size: .85rem; color: #91989e; margin-bottom: 1.5rem; }
.pkg-modal-actions { display: flex; gap: .6rem; justify-content: flex-end; }

.pkg-ready-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .55rem;border-radius:999px;font-size:.72rem;font-weight:800;border:1px solid rgba(0,201,167,.35);background:rgba(0,201,167,.12);color:#00c9a7;white-space:nowrap;}
.pkg-ready-badge.not{border-color:rgba(245,202,153,.35);background:rgba(245,202,153,.12);color:#f5ca99;}
</style>

<?php
/* ── Filter state ── */
$status = $_GET['status'] ?? 'enabled';
$game   = $_GET['game']   ?? 'all';
$server = $_GET['server'] ?? 'all';

function pkgPillUrl(array $overrides = []): string {
    return '?' . http_build_query(array_merge($_GET, $overrides));
}
function pkgPillActive(string $current, string $value): string {
    return $current === $value ? 'active-pill' : '';
}
function gameIconImg(string $gameKey): string {
    $src = ($gameKey === 'val')
        ? '/public/assets/website/images/icons/valorant.png'
        : '/public/assets/website/images/icons/league-of-legends.png';
    $alt = ($gameKey === 'val') ? 'Valorant' : 'League of Legends';
    return '<img src="'.htmlspecialchars($src).'" alt="'.htmlspecialchars($alt).'" style="width:16px;height:16px;object-fit:contain;vertical-align:middle;" loading="lazy">';
}
function formatServerForGame(int $gameId, string $server): array {
    $server = strtolower(trim($server));
    if ($gameId === 2) {
        return $server === 'na' ? ['North America', 'na'] : ['EU', 'eu'];
    }
    return [util_format_server($server), $server];
}

function normalizePackageStatus($status): array {
    $raw = strtolower(trim((string)$status));

    return match ($raw) {
        '1', 'active', 'enabled' => [
            'key' => 'active',
            'label' => 'ACTIVE',
            'cls' => 'sb-enabled',
            'icon' => 'fa-circle-check',
        ],
        '0', 'disabled', 'inactive' => [
            'key' => 'disabled',
            'label' => 'DISABLED',
            'cls' => 'sb-disabled',
            'icon' => 'fa-circle-xmark',
        ],
        'sold_out', 'soldout' => [
            'key' => 'sold_out',
            'label' => 'SOLD OUT',
            'cls' => 'sb-sold-out',
            'icon' => 'fa-circle-minus',
        ],
        default => [
            'key' => 'unknown',
            'label' => $raw !== '' ? strtoupper($raw) : 'UNKNOWN',
            'cls' => 'sb-disabled',
            'icon' => 'fa-circle-minus',
        ],
    };
}
?>

<!-- ── Delete confirm modal ── -->
<div id="pkg-delete-modal" role="dialog" aria-modal="true" aria-labelledby="del-modal-title">
    <div class="pkg-modal-box">
        <h5 id="del-modal-title"><i class="fa-duotone fa-triangle-exclamation me-2" style="color:#ed4c78;"></i>Delete Package</h5>
        <p id="del-modal-text">Are you sure you want to delete package <strong id="del-pkg-name"></strong>? This action cannot be undone.</p>
        <div class="pkg-modal-actions">
            <button type="button" class="btn-tbl btn-tbl-view" id="del-modal-cancel">Cancel</button>
            <button type="button" class="btn-tbl btn-tbl-delete" id="del-modal-confirm">
                <i class="fa-duotone fa-trash"></i> Delete package
            </button>
        </div>
    </div>
</div>

<!-- ── Card ── -->
<div class="card">

    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <h5 class="card-header-title">Packages List</h5>
            </div>
            <div class="col-auto d-flex align-items-center gap-2">
                <a href="<?= ADMN_URL ?>/account-package/add" class="btn btn-primary btn-sm">
                    <i class="fa-duotone fa-plus me-1"></i> Add Package
                </a>
                <form>
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                               placeholder="Search packages…" aria-label="Search Packages">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter pills – Status -->
    <div class="pkg-filter-wrap">
        <label>Status:</label>
        <a href="<?= pkgPillUrl(['status'=>'all']) ?>"
           class="pkg-pill <?= pkgPillActive($status,'all') ?>"><span class="pill-dot"></span> All</a>
        <a href="<?= pkgPillUrl(['status'=>'enabled']) ?>"
           class="pkg-pill pill-enabled <?= pkgPillActive($status,'enabled') ?>"><span class="pill-dot"></span> Active</a>
        <a href="<?= pkgPillUrl(['status'=>'disabled']) ?>"
           class="pkg-pill pill-disabled <?= pkgPillActive($status,'disabled') ?>"><span class="pill-dot"></span> Disabled</a>

        <span class="pill-sep"></span>

        <label>Game:</label>
        <a href="<?= pkgPillUrl(['game'=>'all','server'=>'all']) ?>"
           class="pkg-pill <?= pkgPillActive($game,'all') ?>"><span class="pill-dot"></span> All</a>
        <a href="<?= pkgPillUrl(['game'=>'lol','server'=>'all']) ?>"
           class="pkg-pill pill-lol <?= pkgPillActive($game,'lol') ?>">
            <?= gameIconImg('lol') ?> LoL</a>
        <a href="<?= pkgPillUrl(['game'=>'val','server'=>'all']) ?>"
           class="pkg-pill pill-val <?= pkgPillActive($game,'val') ?>">
            <?= gameIconImg('val') ?> Valorant</a>

        <span class="pill-sep"></span>

        <label>Server:</label>
        <a href="<?= pkgPillUrl(['server'=>'all']) ?>"
           class="pkg-pill <?= pkgPillActive($server,'all') ?>"><span class="pill-dot"></span> All</a>
        <?php if ($game === 'val'): ?>
            <a href="<?= pkgPillUrl(['server'=>'eu']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'eu') ?>"><span class="pill-dot"></span> EU</a>
            <a href="<?= pkgPillUrl(['server'=>'na']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'na') ?>"><span class="pill-dot"></span> NA</a>
        <?php elseif ($game === 'lol'): ?>
            <a href="<?= pkgPillUrl(['server'=>'euw']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'euw') ?>"><span class="pill-dot"></span> EUW</a>
            <a href="<?= pkgPillUrl(['server'=>'eune']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'eune') ?>"><span class="pill-dot"></span> EUNE</a>
            <a href="<?= pkgPillUrl(['server'=>'na']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'na') ?>"><span class="pill-dot"></span> NA</a>
        <?php else: ?>
            <a href="<?= pkgPillUrl(['server'=>'euw']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'euw') ?>"><span class="pill-dot"></span> EUW</a>
            <a href="<?= pkgPillUrl(['server'=>'eune']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'eune') ?>"><span class="pill-dot"></span> EUNE</a>
            <a href="<?= pkgPillUrl(['server'=>'eu']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'eu') ?>"><span class="pill-dot"></span> EU</a>
            <a href="<?= pkgPillUrl(['server'=>'na']) ?>"
               class="pkg-pill pill-server <?= pkgPillActive($server,'na') ?>"><span class="pill-dot"></span> NA</a>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               data-hs-datatables-options='{
                   "columnDefs": [{"targets": [6], "orderable": false}],
                   "order": [[5, "desc"]],
                   "info":    {"totalQty": "#datatableEntriesInfoTotalQty"},
                   "entries": "#datatableEntries",
                   "search":  "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
               }'
               id="packages_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Server</th>
                    <th>Ranked</th>
                    <th>Status</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $row):
                $gameId  = (int)($row['game_id'] ?? 1);
                $gKey    = ($gameId === 2) ? 'val' : 'lol';
                $avail   = (int)($row['available_count'] ?? 0);
                $availCls = $avail === 0 ? 'avail-0' : ($avail <= 3 ? 'avail-low' : 'avail-ok');
                [$serverName, $serverCode] = formatServerForGame($gameId, (string)($row['server'] ?? ''));

                $statusBadge = normalizePackageStatus($row['status'] ?? '');
                $sCls = $statusBadge['cls'];
                $sIcon = $statusBadge['icon'];
                $sLabel = $statusBadge['label'];
            ?>
            <tr>
                <!-- ID -->
                <td class="fw-500">
                    <a href="<?= ADMN_URL ?>/account-package/<?= (int)$row['id'] ?>" style="color:#9b8bf0;text-decoration:none;">
                        <span class="game-chip me-1"><?= gameIconImg($gKey) ?></span>
                        <span style="color:#c5c8cc;">#<?= (int)$row['id'] ?></span>
                    </a>
                </td>

                <!-- Name + availability -->
                <td class="fw-500 pkg-name-cell">
                    <div class="pkg-name-wrap">
                        <a href="<?= ADMN_URL ?>/account-package/<?= (int)$row['id'] ?>"
                           class="pkg-name-link">
                            <?= htmlspecialchars(html_entity_decode((string)($row['name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <span class="avail-badge <?= $availCls ?>">
                            <?= $avail ?> available
                        </span>
                    </div>
                </td>

                <!-- Server -->
                <td class="pkg-server-cell" style="font-size:.84rem;color:#91989e;">
                    <?= htmlspecialchars(html_entity_decode((string)$serverName, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
                    <span style="font-size:.72rem;color:#555d65;margin-left:.25rem;"><?= htmlspecialchars(html_entity_decode(strtolower((string)$serverCode), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></span>
                </td>


                <!-- Ranked Ready -->
                <td>
                    <?php if ((int)($row['ranked_ready'] ?? 1) === 1): ?>
                        <span class="pkg-ready-badge"><i class="fa-duotone fa-circle-check"></i> Ranked Ready</span>
                    <?php else: ?>
                        <span class="pkg-ready-badge not"><i class="fa-duotone fa-triangle-exclamation"></i> Not Ready</span>
                    <?php endif; ?>
                </td>

                <!-- Status -->
                <td>
                    <span class="status-badge <?= $sCls ?>">
                        <i class="fa-duotone <?= $sIcon ?>" style="font-size:.75rem;"></i>
                        <?= htmlspecialchars($sLabel, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>

                <!-- Price -->
                <td class="fw-500 text-end" style="color:#c5c8cc;">
                    €<?= util_format_price_display($row['price']) ?>
                </td>

                <!-- Created At -->
                <td class="fw-500 text-end" data-order="<?= htmlspecialchars((string)($row['created_at']??''), ENT_QUOTES, 'UTF-8') ?>"
                    style="font-size:.82rem;color:#91989e;">
                    <?= util_format_date_display($row['created_at']) ?>
                </td>

                <!-- Actions -->
                <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <a href="<?= ADMN_URL ?>/account-package/<?= (int)$row['id'] ?>" class="btn-tbl btn-tbl-view">
                            <i class="fa-duotone fa-eye" style="font-size:.8rem;"></i> View
                        </a>
                        <button type="button" class="btn-tbl btn-tbl-delete js-delete-pkg"
                                data-pkg-id="<?= (int)$row['id'] ?>"
                                data-pkg-name="<?= htmlspecialchars(html_entity_decode((string)($row['name'] ?? ('Package #'.$row['id'])), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">
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
                    <nav id="datatableWithSearchPagination" aria-label="Packages pagination"></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {

    /* ── DataTable init ── */
    HSCore.components.HSDatatables.init($('#packages_table'), {
        language: {
            zeroRecords: `<div class="text-center p-4">
                <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="default">
                <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="dark">
                <p class="mb-0">No packages found</p>
            </div>`
        }
    });

    /* ── Delete modal ── */
    var modal      = document.getElementById('pkg-delete-modal');
    var modalName  = document.getElementById('del-pkg-name');
    var btnConfirm = document.getElementById('del-modal-confirm');
    var btnCancel  = document.getElementById('del-modal-cancel');
    var pendingId  = null;
    var pendingRow = null;

    function decodeHtmlEntities(value) {
        var txt = document.createElement('textarea');
        txt.innerHTML = String(value || '');
        return txt.value;
    }

    // Open
    $(document).on('click', '.js-delete-pkg', function () {
        pendingId  = $(this).data('pkg-id');
        pendingRow = $(this).closest('tr');
        modalName.textContent = decodeHtmlEntities($(this).attr('data-pkg-name') || $(this).data('pkg-name') || '');
        modal.classList.add('is-open');
    });

    // Close
    function closeModal() {
        modal.classList.remove('is-open');
        pendingId  = null;
        pendingRow = null;
    }
    btnCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // Confirm delete
    btnConfirm.addEventListener('click', async function () {
        if (!pendingId) return;

        var btn  = btnConfirm;
        var orig = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = '<i class="fa-duotone fa-spinner fa-spin"></i> Deleting…';

        try {
            var fd = new FormData();
            fd.append('action', 'admin_delete_package');
            fd.append('id',     pendingId);

            var res  = await fetch('<?= BASE_URL ?>/ajax', { method: 'POST', body: fd });
            var json = await res.json();

            if (json.status === 'success' || json.success) {
                // Remove row from DataTable
                if (pendingRow) {
                    var dt = $('#packages_table').DataTable();
                    dt.row(pendingRow).remove().draw();
                }
                closeModal();
                // Show toast if the dashboard system supports it
                if (json.sendToast && window.HSToastr) {
                    HSToastr[json.sendToast.type]?.(json.sendToast.message, json.sendToast.title);
                }
            } else {
                var msg = (json.sendToast && json.sendToast.message) || json.message || 'Could not delete package.';
                alert(msg);
                btn.disabled  = false;
                btn.innerHTML = orig;
            }
        } catch (err) {
            alert('Network error – package not deleted.');
            btn.disabled  = false;
            btn.innerHTML = orig;
        }
    });

});
</script>
<?= $this->end() ?>
