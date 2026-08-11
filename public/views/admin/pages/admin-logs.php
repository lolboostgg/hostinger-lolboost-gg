<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Admin Logs - Admin Area | LoLBoost.gg', 'h1' => 'Admin Logs', 'description' => 'View the Admin Logs.']]) ?>

<style>
/* ── Theme tokens ───────────────────────────────────────────────────────────
   body bg: #1e2022 | card bg: #25282a | border: #2f3235 | text: #c5c8cc
   teal: #00c9a7 | danger: #ed4c78 | primary: #5c4ae3 | amber: #f5ca99
   info: #09a5be | muted: #91989e
   ──────────────────────────────────────────────────────────────────────── */

/* ── Summary Cards ── */
.log-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
    gap: .85rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #25282a;
    border: .0625rem solid #2f3235;
    border-radius: .75rem;
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: .9rem;
    transition: transform .15s ease, box-shadow .15s ease;
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.2);
}
.stat-card.is-clickable { cursor: pointer; }
.stat-card.is-clickable:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.35); }
.stat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.stat-icon.c-teal  { background: rgba(0,201,167,.13);   color: #00c9a7; }
.stat-icon.c-blue  { background: rgba(9,165,190,.13);   color: #09a5be; }
.stat-icon.c-red   { background: rgba(237,76,120,.13);  color: #ed4c78; }
.stat-icon.c-amber { background: rgba(245,202,153,.13); color: #f5ca99; }
.stat-label { font-size: .7rem; color: #91989e; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .1rem; }
.stat-value { font-size: 1.3rem; font-weight: 700; color: #c5c8cc; line-height: 1.2; }

/* ── Filter pills ── */
.action-filter-wrap {
    display: flex; align-items: center; gap: .4rem;
    padding: .75rem 1.3125rem;
    border-bottom: .0625rem solid #2f3235;
    background: rgba(0,0,0,.10);
    flex-wrap: wrap;
}
.action-filter-wrap > label { font-size: .75rem; color: #91989e; margin: 0 .2rem 0 0; }
.action-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .28rem .75rem; border-radius: 50rem;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1px solid #2f3235; background: transparent; color: #91989e;
    transition: all .15s ease;
}
.action-pill:hover { color: #c5c8cc; border-color: #4b5055; }
.action-pill.active-pill              { color: #1e2022; background: #00c9a7; border-color: #00c9a7; }
.action-pill.pill-edit.active-pill    { color: #fff;    background: #5c4ae3; border-color: #5c4ae3; }
.action-pill.pill-delete.active-pill  { color: #fff;    background: #ed4c78; border-color: #ed4c78; }
.action-pill.pill-complete.active-pill{ color: #1e2022; background: #f5ca99; border-color: #f5ca99; }
.action-pill.pill-create.active-pill  { color: #1e2022; background: #00c9a7; border-color: #00c9a7; }
.action-pill .pill-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; opacity: .7; }

/* ── Action badge ── */
.action-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .22rem .6rem; border-radius: 20px;
    font-size: .73rem; font-weight: 600; white-space: nowrap;
}
.ab-edit     { background: rgba(92,74,227,.13);   color: #9b8bf0; border: 1px solid rgba(92,74,227,.30); }
.ab-delete   { background: rgba(237,76,120,.13);  color: #ed4c78; border: 1px solid rgba(237,76,120,.28); }
.ab-done     { background: rgba(9,165,190,.12);   color: #09a5be; border: 1px solid rgba(9,165,190,.28); }
.ab-create   { background: rgba(0,201,167,.12);   color: #00c9a7; border: 1px solid rgba(0,201,167,.28); }
.ab-other    { background: rgba(109,116,123,.10); color: #8c98a4; border: 1px solid rgba(109,116,123,.20); }

/* ── Changes button ── */
.btn-changes {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .25rem .7rem; border-radius: 20px;
    font-size: .73rem; font-weight: 700;
    border: 1px solid rgba(92,74,227,.35);
    background: rgba(92,74,227,.10); color: #9b8bf0;
    cursor: pointer; transition: all .15s ease;
}
.btn-changes:hover { background: rgba(92,74,227,.22); border-color: rgba(92,74,227,.55); color: #c4b8ff; }
.btn-changes .chg-count {
    background: rgba(92,74,227,.30); border-radius: 20px;
    padding: 0 .4rem; font-size: .65rem; line-height: 1.5;
}

/* ── Changes Modal ── */
.chlog-section {
    border: 1px solid rgba(255,255,255,.07);
    border-radius: .85rem;
    overflow: hidden;
    margin-bottom: 1rem;
    background: rgba(255,255,255,.02);
}
[data-theme="light"] .chlog-section {
    border-color: rgba(0,0,0,.08);
    background: rgba(0,0,0,.02);
}
.chlog-section-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .55rem 1rem;
    background: rgba(92,74,227,.08);
    border-bottom: 1px solid rgba(255,255,255,.06);
}
[data-theme="light"] .chlog-section-header {
    background: rgba(92,74,227,.05);
    border-bottom-color: rgba(0,0,0,.06);
}
.chlog-section-title {
    font-weight: 800; font-size: .78rem;
    letter-spacing: .07em; text-transform: uppercase; color: #9b8bf0;
}
.chlog-section-ts { font-size: .72rem; color: #91989e; }
.chlog-rows { padding: .5rem .65rem; display: flex; flex-direction: column; gap: .35rem; }
.chlog-row {
    display: grid;
    grid-template-columns: 160px 1fr 22px 1fr;
    gap: .55rem;
    align-items: center;
    padding: .5rem .65rem;
    border-radius: .55rem;
    border: 1px solid rgba(255,255,255,.05);
    background: rgba(255,255,255,.025);
    transition: background .12s;
}
.chlog-row:hover { background: rgba(255,255,255,.05); }
[data-theme="light"] .chlog-row { border-color: rgba(0,0,0,.06); background: rgba(0,0,0,.02); }
.chlog-field {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: .76rem; font-weight: 700; color: #c5c8cc;
}
.chlog-val { font-size: .84rem; word-break: break-word; }
.chlog-val-label { font-size: .67rem; color: #91989e; margin-bottom: .1rem; }
.chlog-val-old { color: #ed4c78; }
.chlog-val-new { color: #00c9a7; }
.chlog-arrow { text-align: center; color: #555d65; font-size: .75rem; }
.chlog-scroll { max-height: 68vh; overflow-y: auto; padding: 1rem; }

/* ── Admin filter pills ── */
.admin-filter-wrap {
    display: flex; align-items: center; gap: .4rem;
    padding: .6rem 1.3125rem;
    border-bottom: .0625rem solid #2f3235;
    background: rgba(0,0,0,.06);
    flex-wrap: wrap;
}
.admin-filter-wrap > label { font-size: .75rem; color: #91989e; margin: 0 .2rem 0 0; }
.admin-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .22rem .65rem .22rem .28rem;
    border-radius: 50rem;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1px solid #2f3235; background: transparent; color: #91989e;
    transition: all .15s ease;
}
.admin-pill:hover { color: #c5c8cc; border-color: #4b5055; }
.admin-pill.active-pill { color: #fff; background: #5c4ae3; border-color: #5c4ae3; }
.admin-pill img {
    width: 20px; height: 20px; border-radius: 50%; object-fit: cover;
    border: 1px solid rgba(255,255,255,.15);
}
.admin-pill .admin-initial {
    width: 20px; height: 20px; border-radius: 50%;
    background: rgba(92,74,227,.25); color: #9b8bf0;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .65rem; font-weight: 800;
}
</style>

<?php
// Load admins by fixed IDs
$lb_admins = [];
global $db;
if (isset($db)) {
    $lb_admins = $db->run("SELECT id, username, icon FROM admins WHERE id IN (2,3,12,20,23,24,51) ORDER BY username ASC") ?: [];
}
?>

<!-- ── Stat Cards ─────────────────────────────────────────────────────────── -->
<div class="log-stats-grid">
    <div class="stat-card">
        <div class="stat-icon c-teal"><i class="fa-duotone fa-list-check"></i></div>
        <div>
            <div class="stat-label">Total Logs</div>
            <div class="stat-value" id="stat-total">—</div>
        </div>
    </div>
    <div class="stat-card is-clickable" id="sc-edit">
        <div class="stat-icon c-blue"><i class="fa-duotone fa-pen-to-square"></i></div>
        <div>
            <div class="stat-label">Filter</div>
            <div class="stat-value" style="font-size:1rem;color:#9b8bf0;">Edits</div>
        </div>
    </div>
    <div class="stat-card is-clickable" id="sc-delete">
        <div class="stat-icon c-red"><i class="fa-duotone fa-trash"></i></div>
        <div>
            <div class="stat-label">Filter</div>
            <div class="stat-value" style="font-size:1rem;color:#ed4c78;">Deletes</div>
        </div>
    </div>
    <div class="stat-card is-clickable" id="sc-complete">
        <div class="stat-icon c-amber"><i class="fa-duotone fa-check-circle"></i></div>
        <div>
            <div class="stat-label">Filter</div>
            <div class="stat-value" style="font-size:1rem;color:#f5ca99;">Completions</div>
        </div>
    </div>
</div>

<!-- ── Card ───────────────────────────────────────────────────────────────── -->
<div class="card">

    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <h5 class="card-header-title">Admin Logs</h5>
            </div>
            <div class="col-auto">
                <form>
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                               placeholder="Search" aria-label="Search">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter pills -->
    <div class="action-filter-wrap">
        <label>Filter:</label>
        <button type="button" class="action-pill active-pill" data-filter="">
            <span class="pill-dot"></span> All
        </button>
        <button type="button" class="action-pill pill-edit" data-filter="edit">
            <span class="pill-dot"></span> Edits
        </button>
        <button type="button" class="action-pill pill-delete" data-filter="delete">
            <span class="pill-dot"></span> Deletes
        </button>
        <button type="button" class="action-pill pill-complete" data-filter="complete">
            <span class="pill-dot"></span> Completions
        </button>
        <button type="button" class="action-pill pill-create" data-filter="create">
            <span class="pill-dot"></span> Creates
        </button>
    </div>

    <!-- Admin filter pills -->
    <?php if (!empty($lb_admins)): ?>
    <div class="admin-filter-wrap">
        <label>Admin:</label>
        <button type="button" class="admin-pill active-admin-pill" data-admin-id="">
            <span class="admin-initial">All</span>
        </button>
        <?php foreach ($lb_admins as $adm):
            $aid  = (int)($adm['id'] ?? 0);
            $aname = htmlspecialchars($adm['username'] ?? '', ENT_QUOTES);
            $aicon = $adm['icon'] ?? '';
            $initial = mb_strtoupper(mb_substr($adm['username'] ?? '?', 0, 1));
        ?>
        <button type="button" class="admin-pill" data-admin-id="<?= $aid ?>">
            <?php if ($aicon): ?>
                <img src="<?= htmlspecialchars($aicon, ENT_QUOTES) ?>" alt="<?= $aname ?>">
            <?php else: ?>
                <span class="admin-initial"><?= $initial ?></span>
            <?php endif; ?>
            <?= $aname ?>
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               id="logs_table">
            <thead class="thead-light">
                <tr>
                    <th style="display:none;">Admin ID</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Changes</th>
                    <th class="text-end">Date &amp; Time</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                                autocomplete="off"
                                data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8" selected>8</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Changes Modal ──────────────────────────────────────────────────────── -->
<div id="logChangesModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.07);">
                <div>
                    <h5 class="modal-title fw-700">Changes</h5>
                    <div class="text-muted small mt-1" id="logChangesMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="chlog-scroll" id="logChangesBody">
                <div class="text-center py-5 text-muted">
                    <i class="fa-duotone fa-spinner fa-spin fs-3 mb-2 d-block"></i>Loading…
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.07);">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {

    /* ── Helpers ─────────────────────────────────────────────────── */
    function actionCat(str) {
        var s = (str || '').toLowerCase();
        if (s.indexOf('edit') !== -1 || s.indexOf('updated') !== -1) return 'edit';
        if (s.indexOf('delet') !== -1 || s.indexOf('remov') !== -1) return 'delete';
        if (s.indexOf('complet') !== -1) return 'complete';
        if (s.indexOf('creat') !== -1 || s.indexOf('add') !== -1) return 'create';
        return 'other';
    }

    var BADGE = {
        edit:     { cls: 'ab-edit',   icon: 'fa-pen-to-square', label: 'Edit' },
        delete:   { cls: 'ab-delete', icon: 'fa-trash',         label: 'Delete' },
        complete: { cls: 'ab-done',   icon: 'fa-check-circle',  label: 'Complete' },
        create:   { cls: 'ab-create', icon: 'fa-plus-circle',   label: 'Create' },
        other:    { cls: 'ab-other',  icon: 'fa-bolt',          label: 'Action' },
    };

    function actionBadge(cat) {
        var b = BADGE[cat] || BADGE.other;
        return '<span class="action-badge ' + b.cls + '"><i class="fa-duotone ' + b.icon + '"></i>' + b.label + '</span>';
    }

    function adminCell(nameHtml, idHtml, iconUrl) {
        var name    = (nameHtml || '').replace(/<[^>]*>/g, '').trim();
        var id      = (idHtml  || '').replace(/<[^>]*>/g, '').trim();
        var initial = name ? name.charAt(0).toUpperCase() : '?';
        var av = iconUrl
            ? '<img src="' + iconUrl + '" alt="' + name + '" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1.5px solid rgba(92,74,227,.35);">'
            : '<span style="width:30px;height:30px;border-radius:50%;background:rgba(92,74,227,.18);display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#9b8bf0;flex-shrink:0;">' + initial + '</span>';
        return '<div class="d-flex align-items-center gap-2">' + av
             + '<div><div class="fw-600" style="font-size:.85rem;line-height:1.2;">' + (name || '—') + '</div>'
             + '<div class="text-muted" style="font-size:.72rem;">' + id + '</div></div></div>';
    }

    function changesBtn(html) {
        if (!html || !html.trim() || html.trim() === '—') {
            return '<span class="text-muted" style="font-size:.8rem;">—</span>';
        }
        var mId  = html.match(/data-log-id=["\'](\d+)["\']/);
        var mCnt = html.match(/\((\d+)\)/);
        if (mId) {
            var cnt = mCnt ? mCnt[1] : '';
            return '<button class="btn-changes js-view-changes" data-log-id="' + mId[1] + '">'
                 + '<i class="fa-duotone fa-eye" style="font-size:.8rem;"></i> View'
                 + (cnt ? '<span class="chg-count">' + cnt + '</span>' : '')
                 + '</button>';
        }
        return html;
    }

    /* ── Filter state ────────────────────────────────────────────── */
    var activeAction = '';
    var activeAdmin  = '';

    function setFilter(val) {
        activeAction = val || '';
        $('.action-pill').removeClass('active-pill');
        $('.action-pill[data-filter="' + activeAction + '"]').addClass('active-pill');
        $('#sc-edit, #sc-delete, #sc-complete').css('outline', '');
        if (activeAction === 'edit')     $('#sc-edit').css('outline',     '2px solid #5c4ae3');
        if (activeAction === 'delete')   $('#sc-delete').css('outline',   '2px solid #ed4c78');
        if (activeAction === 'complete') $('#sc-complete').css('outline', '2px solid #f5ca99');
        reload();
    }

    function setAdminFilter(id) {
        activeAdmin = id || '';
        $('.admin-pill').removeClass('active-admin-pill');
        $('.admin-pill[data-admin-id="' + activeAdmin + '"]').addClass('active-admin-pill');
        reload();
    }

    function reload() {
        var api = $.fn.dataTable.isDataTable('#logs_table') ? $('#logs_table').DataTable() : null;
        if (api) api.ajax.reload();
    }

    /* ── DataTable (server-side) ─────────────────────────────────── */
    HSCore.components.HSDatatables.init($('#logs_table'), {
        serverSide: true,
        processing: true,
        ajax: {
            url: '<?= ADMN_URL ?>/admin-logs/data',
            data: function (d) {
                d.action_filter = activeAction || '';
                d.admin_filter  = activeAdmin  || '';
                return d;
            }
        },
        columns: [
            { data: 'admin_id_html', visible: false },
            {
                data: 'admin_name_html',
                render: function (v, t, row) {
                    return adminCell(v, row.admin_id_html, row.admin_icon || '');
                }
            },
            {
                data: 'log_action',
                render: function (v, t, row) {
                    var cat = actionCat(v);
                    return '<div class="d-flex align-items-center gap-2 flex-wrap">'
                         + actionBadge(cat)
                         + '<span style="font-size:.85rem;">' + (row.action_html || v) + '</span>'
                         + '</div>';
                }
            },
            { data: 'changes_html', orderable: false, render: function (v) { return changesBtn(v); } },
            { data: 'created_at',   render: function (v, t, row) { return row.created_html || v; } },
        ],
        order: [[4, 'desc']],
        info:       { totalQty: '#datatableEntriesInfoTotalQty' },
        entries:    '#datatableEntries',
        search:     '#datatableWithSearchInput',
        isResponsive: false,
        isShowPaging: true,
        pagination: 'datatableWithSearchPagination',
        language: {
            zeroRecords: '<div class="text-center p-4">'
                + '<img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="default">'
                + '<img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="dark">'
                + '<p class="mb-0">No data to show</p></div>'
        }
    });

    var dt = $('#logs_table').DataTable();

    /* ── Total counter ───────────────────────────────────────────── */
    $('#logs_table').on('draw.dt', function () {
        var api = $('#logs_table').DataTable();
        var el  = document.getElementById('stat-total');
        if (el) el.textContent = (api.page.info().recordsTotal || 0).toLocaleString();
    });

    /* ── Pill clicks ─────────────────────────────────────────────── */
    $('.action-pill').on('click', function () {
        setFilter($(this).data('filter'));
    });

    $('.admin-pill').on('click', function () {
        setAdminFilter($(this).data('admin-id'));
    });

    /* ── Stat card shortcuts (toggle on second click) ────────────── */
    $('#sc-edit').on('click',     function () { setFilter(activeAction === 'edit'     ? '' : 'edit'); });
    $('#sc-delete').on('click',   function () { setFilter(activeAction === 'delete'   ? '' : 'delete'); });
    $('#sc-complete').on('click', function () { setFilter(activeAction === 'complete' ? '' : 'complete'); });

    /* ── Changes modal ───────────────────────────────────────────── */
    var $modal    = $('#logChangesModal');
    var $meta     = $('#logChangesMeta');
    var $body     = $('#logChangesBody');
    var modalInst = null;

    function showModal() {
        if (!modalInst) modalInst = new bootstrap.Modal($modal[0]);
        modalInst.show();
    }

    $(document).on('click', '.js-view-changes', function () {
        var logId = this.getAttribute('data-log-id');
        if (!logId) return;

        $meta.html('');
        $body.html('<div class="text-center py-5 text-muted"><i class="fa-duotone fa-spinner fa-spin fs-3 mb-2 d-block"></i>Loading…</div>');
        showModal();

        fetch('<?= ADMN_URL ?>/admin-logs/' + logId + '/changes')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                $meta.html(
                    '<strong>' + (d.admin_name || '—') + '</strong>'
                    + ' <span class="text-muted">(#' + d.admin_id + ')</span>'
                    + ' &bull; ' + (d.action || '')
                );
                $body.html(d.body_html ? renderChanges(d.body_html)
                    : '<div class="text-center py-4 text-muted">No changes recorded.</div>');
            })
            .catch(function () {
                $body.html('<div class="text-danger p-3">Failed to load changes.</div>');
            });
    });

    /**
     * Re-renders the backend lb-chlog-row HTML into the new styled sections.
     */
    function renderChanges(raw) {
        var tmp = document.createElement('div');
        tmp.innerHTML = raw;

        var out      = '';
        var rows     = [];
        var curTitle = '';
        var curTs    = '';

        function flush() {
            if (!curTitle && !rows.length) return;
            out += '<div class="chlog-section">'
                 + '<div class="chlog-section-header">'
                 + '<span class="chlog-section-title"><i class="fa-duotone fa-table me-1"></i>' + curTitle + '</span>'
                 + '<span class="chlog-section-ts">' + curTs + '</span>'
                 + '</div>'
                 + '<div class="chlog-rows">' + rows.join('') + '</div>'
                 + '</div>';
            rows = [];
        }

        var nodes = tmp.childNodes;
        for (var i = 0; i < nodes.length; i++) {
            var node = nodes[i];
            if (node.nodeType !== 1) continue;

            // Section header (contains lb-chlog-badge)
            if (node.querySelector && node.querySelector('.lb-chlog-badge')) {
                flush();
                var badge = node.querySelector('.lb-chlog-badge');
                var ts    = node.querySelector('.text-muted');
                curTitle = badge ? badge.textContent.trim() : '';
                curTs    = ts    ? ts.textContent.trim()    : '';
                continue;
            }

            // HR divider — skip
            if (node.tagName === 'HR') continue;

            // Change row
            if ((node.className || '').indexOf('lb-chlog-row') !== -1) {
                var field  = node.querySelector('.lb-chlog-field');
                var vals   = node.querySelectorAll('.lb-chlog-val');
                var fName  = field ? field.textContent.trim() : '';
                var oldEl  = vals[0] ? vals[0].querySelector('div:last-child') : null;
                var newEl  = vals[1] ? vals[1].querySelector('div:last-child') : null;
                rows.push(
                    '<div class="chlog-row">'
                  + '<div class="chlog-field">' + fName + '</div>'
                  + '<div class="chlog-val"><div class="chlog-val-label">old</div>'
                  + '<div class="chlog-val-old">' + (oldEl ? oldEl.innerHTML : '') + '</div></div>'
                  + '<div class="chlog-arrow"><i class="fa-solid fa-arrow-right"></i></div>'
                  + '<div class="chlog-val"><div class="chlog-val-label">new</div>'
                  + '<div class="chlog-val-new">' + (newEl ? newEl.innerHTML : '') + '</div></div>'
                  + '</div>'
                );
                continue;
            }

            // "No field diff" note
            if ((node.className || '').indexOf('text-muted') !== -1) {
                rows.push('<div class="text-muted small px-1 py-2">' + node.textContent + '</div>');
            }
        }
        flush();

        return out || '<div class="text-center py-4 text-muted">No changes recorded.</div>';
    }

});
</script>
<?= $this->end() ?>
