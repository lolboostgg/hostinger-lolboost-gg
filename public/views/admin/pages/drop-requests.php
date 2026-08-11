<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Order Drop Request History - Admin Area | LoLBoost.gg', 'h1' => 'Order Drop Request History', 'description' => 'View the Order Drop Request History.']]) ?>
<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<?= $this->end() ?>

<?php
// Stats
$total    = count($data ?? []);
$byType   = [];
foreach (($data ?? []) as $r) {
    $t = $r['reason_type'] ?? 'Other';
    $byType[$t] = ($byType[$t] ?? 0) + 1;
}
arsort($byType);
$topType     = array_key_first($byType) ?? '—';
$topTypeCount = $byType[$topType] ?? 0;
?>

<style>
/* ── Theme tokens ───────────────────────────────────────────
   body: #1e2022 | card: #25282a | border: #2f3235
   text: #c5c8cc | teal: #00c9a7 | danger: #ed4c78
   primary: #5c4ae3 | amber: #f5ca99 | info: #09a5be
   muted: #91989e
   ──────────────────────────────────────────────────────── */

/* ── Stat Cards ── */
.dr-stats-grid {
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
    display: flex; align-items: center; gap: .9rem;
    transition: transform .15s, box-shadow .15s;
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.2);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.35); }
.stat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.stat-icon.c-teal  { background: rgba(0,201,167,.13);   color: #00c9a7; }
.stat-icon.c-red   { background: rgba(237,76,120,.13);  color: #ed4c78; }
.stat-icon.c-amber { background: rgba(245,202,153,.13); color: #f5ca99; }
.stat-icon.c-blue  { background: rgba(9,165,190,.13);   color: #09a5be; }
.stat-label { font-size: .7rem; color: #91989e; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .1rem; }
.stat-value { font-size: 1.3rem; font-weight: 700; color: #c5c8cc; line-height: 1.2; }
.stat-sub   { font-size: .72rem; color: #91989e; margin-top: .1rem; }

/* ── Reason type badge ── */
.reason-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .22rem .65rem; border-radius: 20px;
    font-size: .72rem; font-weight: 600; white-space: nowrap;
}
.rb-technical   { background: rgba(9,165,190,.12);   color: #09a5be; border: 1px solid rgba(9,165,190,.28); }
.rb-personal    { background: rgba(245,202,153,.12); color: #f5ca99; border: 1px solid rgba(245,202,153,.28); }
.rb-customer    { background: rgba(92,74,227,.12);   color: #9b8bf0; border: 1px solid rgba(92,74,227,.28); }
.rb-wrong       { background: rgba(237,76,120,.12);  color: #ed4c78; border: 1px solid rgba(237,76,120,.28); }
.rb-difficult   { background: rgba(255,165,0,.12);   color: #ffa500; border: 1px solid rgba(255,165,0,.28); }
.rb-other       { background: rgba(109,116,123,.10); color: #8c98a4; border: 1px solid rgba(109,116,123,.20); }

/* ── Reason type filter pills ── */
.reason-filter-wrap {
    display: flex; align-items: center; gap: .4rem;
    padding: .75rem 1.3125rem;
    border-bottom: .0625rem solid #2f3235;
    background: rgba(0,0,0,.10);
    flex-wrap: wrap;
}
.reason-filter-wrap > label { font-size: .75rem; color: #91989e; margin: 0 .2rem 0 0; }
.reason-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .28rem .75rem; border-radius: 50rem;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1px solid #2f3235; background: transparent; color: #91989e;
    transition: all .15s;
}
.reason-pill:hover { color: #c5c8cc; border-color: #4b5055; }
.reason-pill.active-pill { color: #1e2022; background: #00c9a7; border-color: #00c9a7; }
.reason-pill .pill-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; opacity: .7; }

/* ── View details modal ── */
.dr-modal-field label {
    font-size: .72rem; color: #91989e;
    text-transform: uppercase; letter-spacing: .05em; margin-bottom: .3rem;
}
.dr-modal-field .form-control,
.dr-modal-field .form-select {
    background: rgba(255,255,255,.04);
    border-color: rgba(255,255,255,.08);
    color: #c5c8cc;
    font-size: .88rem;
}
[data-theme="light"] .dr-modal-field .form-control,
[data-theme="light"] .dr-modal-field .form-select {
    background: rgba(0,0,0,.03);
    border-color: rgba(0,0,0,.1);
}
.dr-modal-section {
    border: 1px solid rgba(255,255,255,.07);
    border-radius: .75rem; padding: .9rem 1rem;
    background: rgba(255,255,255,.02);
    margin-bottom: .75rem;
}
[data-theme="light"] .dr-modal-section {
    border-color: rgba(0,0,0,.08);
    background: rgba(0,0,0,.02);
}
.dr-modal-section-title {
    font-size: .7rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .07em;
    color: #9b8bf0; margin-bottom: .75rem;
}
</style>

<!-- ── Stat Cards ─────────────────────────────────────────────── -->
<div class="dr-stats-grid">
    <div class="stat-card">
        <div class="stat-icon c-teal"><i class="fa-duotone fa-hand-holding-box"></i></div>
        <div>
            <div class="stat-label">Total Requests</div>
            <div class="stat-value"><?= number_format($total) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-red"><i class="fa-duotone fa-triangle-exclamation"></i></div>
        <div>
            <div class="stat-label">Top Reason</div>
            <div class="stat-value" style="font-size:.9rem;"><?= htmlspecialchars($topType) ?></div>
            <div class="stat-sub"><?= $topTypeCount ?> requests</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-amber"><i class="fa-duotone fa-wrench"></i></div>
        <div>
            <div class="stat-label">Technical Issues</div>
            <div class="stat-value"><?= $byType['Technical issues'] ?? 0 ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-blue"><i class="fa-duotone fa-user-slash"></i></div>
        <div>
            <div class="stat-label">Personal Reasons</div>
            <div class="stat-value"><?= $byType['Unavailable due to personal reasons'] ?? 0 ?></div>
        </div>
    </div>
</div>

<!-- ── Card ──────────────────────────────────────────────────── -->
<div class="card">

    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <h5 class="card-header-title">Drop Requests</h5>
            </div>
            <div class="col-auto">
                <form>
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                               placeholder="Search Requests" aria-label="Search Requests">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reason-type filter pills -->
    <div class="reason-filter-wrap">
        <label>Filter:</label>
        <button type="button" class="reason-pill active-pill" data-reason-filter="">
            <span class="pill-dot"></span> All
        </button>
        <button type="button" class="reason-pill" data-reason-filter="Technical issues">
            <span class="pill-dot"></span> Technical
        </button>
        <button type="button" class="reason-pill" data-reason-filter="Unavailable due to personal reasons">
            <span class="pill-dot"></span> Personal
        </button>
        <button type="button" class="reason-pill" data-reason-filter="Customer is playing on the account">
            <span class="pill-dot"></span> Customer Playing
        </button>
        <button type="button" class="reason-pill" data-reason-filter="Wrong order type assigned">
            <span class="pill-dot"></span> Wrong Type
        </button>
        <button type="button" class="reason-pill" data-reason-filter="Order too difficult or not as described">
            <span class="pill-dot"></span> Too Difficult
        </button>
        <button type="button" class="reason-pill" data-reason-filter="Other">
            <span class="pill-dot"></span> Other
        </button>
    </div>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               data-hs-datatables-options='{
                   "order": [[4, "desc"]],
                   "info":  {"totalQty": "#datatableEntriesInfoTotalQty"},
                   "entries": "#datatableEntries",
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
               }'
               id="invoices_table">
            <thead class="thead-light">
                <tr>
                    <th>Order</th>
                    <th>Booster</th>
                    <th>Assigned</th>
                    <th>Reason Type</th>
                    <th>Submitted</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row):
                    $rt = $row['reason_type'] ?? 'Other';
                    // Map reason type → badge class
                    $rbClass = match(true) {
                        str_contains($rt, 'Technical')   => 'rb-technical',
                        str_contains($rt, 'personal')    => 'rb-personal',
                        str_contains($rt, 'Customer')    => 'rb-customer',
                        str_contains($rt, 'Wrong')       => 'rb-wrong',
                        str_contains($rt, 'difficult')   => 'rb-difficult',
                        default                          => 'rb-other',
                    };
                    $rbIcon = match(true) {
                        str_contains($rt, 'Technical')   => 'fa-wrench',
                        str_contains($rt, 'personal')    => 'fa-user-clock',
                        str_contains($rt, 'Customer')    => 'fa-gamepad',
                        str_contains($rt, 'Wrong')       => 'fa-circle-xmark',
                        str_contains($rt, 'difficult')   => 'fa-mountain',
                        default                          => 'fa-ellipsis',
                    };
                    $rbLabel = match(true) {
                        str_contains($rt, 'Technical')   => 'Technical',
                        str_contains($rt, 'personal')    => 'Personal',
                        str_contains($rt, 'Customer')    => 'Customer Playing',
                        str_contains($rt, 'Wrong')       => 'Wrong Type',
                        str_contains($rt, 'difficult')   => 'Too Difficult',
                        default                          => 'Other',
                    };
                    $booster = db_get_row('boosters', ['id' => $row['booster_id']]);
                ?>
                <tr data-reason-type="<?= htmlspecialchars($rt, ENT_QUOTES) ?>"
                    data-progress="<?= htmlspecialchars($row['progress'] ?? '', ENT_QUOTES) ?>"
                    data-progress-url="<?= htmlspecialchars($row['progress_url'] ?? '', ENT_QUOTES) ?>"
                    data-reason="<?= htmlspecialchars($row['reason'] ?? '', ENT_QUOTES) ?>"
                    data-notes="<?= htmlspecialchars($row['notes'] ?? '', ENT_QUOTES) ?>"
                    data-order-date="<?= htmlspecialchars($row['order_date'] ?? '', ENT_QUOTES) ?>">

                    <!-- Order ID -->
                    <td class="fw-500">
                        <a href="<?= ADMN_URL ?>/order/<?= $row['order_id'] ?>"
                           style="font-weight:700;color:#9b8bf0;">
                            #<?= $row['order_id'] ?>
                        </a>
                    </td>

                    <!-- Booster -->
                    <td class="fw-500">
                        <a href="<?= ADMN_URL ?>/booster/<?= $row['booster_id'] ?>"
                           class="d-flex align-items-center gap-2 text-decoration-none">
                            <?php if (!empty($booster['icon'])): ?>
                                <img src="<?= htmlspecialchars($booster['icon']) ?>"
                                     alt="<?= htmlspecialchars($booster['username']) ?>"
                                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1.5px solid rgba(92,74,227,.35);">
                            <?php else: ?>
                                <span style="width:28px;height:28px;border-radius:50%;background:rgba(92,74,227,.18);display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#9b8bf0;flex-shrink:0;">
                                    <?= strtoupper(substr($booster['username'] ?? '?', 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                            <span style="font-size:.88rem;color:#c5c8cc;">
                                <?= htmlspecialchars($booster['username'] ?? '—') ?>
                            </span>
                        </a>
                    </td>

                    <!-- Assigned date -->
                    <td class="fw-500" style="color:#91989e;font-size:.85rem;">
                        <?= util_format_date_display($row['order_date']) ?>
                    </td>

                    <!-- Reason badge -->
                    <td>
                        <span class="reason-badge <?= $rbClass ?>">
                            <i class="fa-duotone <?= $rbIcon ?>"></i>
                            <?= $rbLabel ?>
                        </span>
                    </td>

                    <!-- Submitted date -->
                    <td class="fw-500" data-order="<?= $row['created_at'] ?>"
                        style="color:#91989e;font-size:.85rem;">
                        <?= util_format_date_display($row['created_at']) ?>
                    </td>

                    <!-- View button -->
                    <td class="text-end">
                        <button type="button" class="btn btn-white btn-sm js-view-drop-request">
                            <i class="fa-duotone fa-eye me-1 fs-6"></i> View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
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

<!-- ── Details Modal ─────────────────────────────────────────── -->
<div id="view_details_md" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.07);">
                <div>
                    <h5 class="modal-title fw-700">Drop Request Details</h5>
                    <div class="text-muted small mt-1" id="dr-modal-badge-wrap"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:1.25rem;">

                <!-- Progress section -->
                <div class="dr-modal-section">
                    <div class="dr-modal-section-title"><i class="fa-duotone fa-chart-line me-1"></i> Progress</div>
                    <div class="row g-3">
                        <div class="col-md-6 dr-modal-field">
                            <label>Assigned Date</label>
                            <input type="text" class="form-control" name="order_date" disabled>
                        </div>
                        <div class="col-md-6 dr-modal-field">
                            <label>Progress Made</label>
                            <input type="text" class="form-control" name="progress" disabled>
                        </div>
                        <div class="col-12 dr-modal-field">
                            <label>OP.GG / Profile Link</label>
                            <input type="text" class="form-control" name="progress_url" disabled>
                        </div>
                    </div>
                </div>

                <!-- Reason section -->
                <div class="dr-modal-section">
                    <div class="dr-modal-section-title"><i class="fa-duotone fa-comment-exclamation me-1"></i> Reason</div>
                    <div class="row g-3">
                        <div class="col-12 dr-modal-field">
                            <label>Reason Type</label>
                            <select class="form-select" name="reason_type" disabled>
                                <option value="Technical issues">Technical issues</option>
                                <option value="Unavailable due to personal reasons">Unavailable due to personal reasons</option>
                                <option value="Customer is playing on the account">Customer is playing on the account</option>
                                <option value="Wrong order type assigned">Wrong order type assigned</option>
                                <option value="Order too difficult or not as described">Order too difficult or not as described</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-12 dr-modal-field">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason" rows="3" disabled></textarea>
                        </div>
                        <div class="col-12 dr-modal-field">
                            <label>Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="2" disabled></textarea>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.07);">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
$(document).on('ready', function () {

    HSCore.components.HSTomSelect.init('.js-select');

    HSCore.components.HSDatatables.init($('#invoices_table'), {
        language: {
            zeroRecords: '<div class="text-center p-4">'
                + '<img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="default">'
                + '<img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="dark">'
                + '<p class="mb-0">No data to show</p></div>'
        }
    });

    var dt = $('#invoices_table').DataTable();

    /* ── Reason-type filter ──────────────────────────────────── */
    var activeReason = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'invoices_table') return true;
        if (!activeReason) return true;
        var $row = $(dt.row(dataIndex).node());
        return ($row.data('reason-type') || '') === activeReason;
    });

    $('.reason-pill').on('click', function () {
        $('.reason-pill').removeClass('active-pill');
        $(this).addClass('active-pill');
        activeReason = $(this).data('reason-filter') || '';
        dt.draw();
    });

    /* ── View modal ──────────────────────────────────────────── */
    var reasonBadgeMap = {
        'Technical issues':                          'rb-technical',
        'Unavailable due to personal reasons':       'rb-personal',
        'Customer is playing on the account':        'rb-customer',
        'Wrong order type assigned':                 'rb-wrong',
        'Order too difficult or not as described':   'rb-difficult',
    };

    $(document).on('click', '.js-view-drop-request', function () {
        var $tr  = $(this).closest('tr');
        var rt   = $tr.data('reason-type') || 'Other';
        var cls  = reasonBadgeMap[rt] || 'rb-other';

        // Badge in header
        $('#dr-modal-badge-wrap').html(
            '<span class="reason-badge ' + cls + '" style="font-size:.78rem;">' + rt + '</span>'
        );

        // Fill fields
        var $m = $('#view_details_md');
        $m.find('[name="order_date"]').val($tr.data('order-date') || '');
        $m.find('[name="progress"]').val($tr.data('progress') || '');
        $m.find('[name="progress_url"]').val($tr.data('progress-url') || '');
        $m.find('[name="reason_type"] option').each(function () {
            $(this).prop('selected', $(this).val() === rt);
        });
        $m.find('[name="reason"]').val($tr.data('reason') || '');
        $m.find('[name="notes"]').val($tr.data('notes') || '');

        new bootstrap.Modal($m[0]).show();
    });
});
</script>
<?= $this->end() ?>
