<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Personal Details - Seller Area | LoLBoost.gg']]) ?>

<?= $this->start('styles') ?>
<?= $this->end() ?>

<?php
    $seller_data = defined('SELLER_DATA') ? SELLER_DATA : [];
    $data = array_merge($seller_data, isset($data) && is_array($data) ? $data : []);

    $spageActiveTab = 'personal-details';
    $headerTitle    = 'Personal Details';
    $headerSubtitle = 'Manage your personal information and verification documents.';
    include __DIR__ . '/_shared.php';

    $fields = ['fullname', 'dob', 'address', 'country', 'id_front', 'id_back', 'selfie'];
    $fieldsHaveData = true;
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) { $fieldsHaveData = false; break; }
    }
    $completedFields = 0;
    foreach ($fields as $f) { if (!empty($data[$f])) $completedFields++; }
    $completionPct = round(($completedFields / count($fields)) * 100);
?>

<style>
/* Hide the sidebar Apply button */
.js-navbar-vertical-aside ~ * .btn[type="submit"]:not(#pd-form *),
body > .main .content > .row > .col-lg-10 > form:not(#pd-form) { display: none !important; }
/* Nuclear option: hide any submit button that is a direct child of the content container, not inside our form */
.content > .container-fluid > form > .btn,
.content form:not(#pd-form) .btn[type="submit"] { display: none !important; }

/* Document thumbnail cards */
.lb-doc-grid { margin-top: 14px; }
.lb-doc-thumb {
    background: #14171b;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: transform .12s ease, box-shadow .12s ease;
}
.lb-doc-thumb:hover { transform: translateY(-1px); box-shadow: 0 14px 30px rgba(0,0,0,.45); }
.lb-doc-thumb.lb-doc-thumb img, div.lb-doc-thumb img {
    width: 100%; height: 120px !important; max-height: 120px !important;
    object-fit: cover !important; display: block; background: #0f1114;
}
.lb-doc-thumb .lb-doc-meta {
    padding: 10px 12px; color: #fff; font-weight: 600; font-size: .9rem;
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
}
.lb-doc-thumb .lb-doc-meta small { color: rgba(255,255,255,.65); font-weight: 500; }

/* Dropzone */
.lb-dropzone {
    border: 2px dashed rgba(255,255,255,.15);
    border-radius: 12px;
    padding: 18px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    background: rgba(255,255,255,.02);
    position: relative;
}
.lb-dropzone:hover, .lb-dropzone.drag-over {
    border-color: rgba(109,92,255,.6);
    background: rgba(109,92,255,.06);
}
.lb-dropzone.has-file {
    border-color: rgba(74,222,128,.3);
    background: rgba(74,222,128,.03);
}
.lb-dropzone input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.lb-dropzone-icon { font-size: 1.4rem; color: rgba(109,92,255,.5); margin-bottom: 6px; }
.lb-dropzone-text { font-size: .82rem; color: rgba(255,255,255,.45); font-weight: 600; }
.lb-dropzone-hint { font-size: .74rem; color: rgba(255,255,255,.28); margin-top: 3px; }
.lb-dropzone-preview {
    width: 100%; height: 110px; border-radius: 8px; overflow: hidden;
    margin-bottom: 8px; display: none; position: relative;
}
.lb-dropzone-preview img {
    width: 100%; height: 100% !important; max-height: 110px !important;
    object-fit: cover !important; display: block;
}
.lb-dropzone-preview .lb-dz-clear {
    position: absolute; top: 6px; right: 6px;
    width: 22px; height: 22px; border-radius: 50%;
    background: rgba(0,0,0,.6); border: 1px solid rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .7rem; cursor: pointer; z-index: 10;
}
.lb-dropzone-name { font-size: .78rem; color: rgba(255,255,255,.6); margin-top: 4px; }

/* Upload preview (for existing files shown inline) */
.lb-upload-preview { margin-top: 10px; display: none; }
.lb-upload-preview img { width: 100%; max-height: 140px; object-fit: cover; border-radius: 12px; border: 1px solid rgba(255,255,255,.08); background: #0f1114; }
.lb-upload-preview .meta { margin-top: 6px; color: rgba(255,255,255,.7); font-size: .85rem; }

/* Readonly fields — visually locked */
.form-control[readonly] {
    opacity: .65;
    cursor: not-allowed !important;
    pointer-events: none;
}
.lb-dob-wrap { position: relative; }
.lb-dob-picker {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    z-index: 9999;
    background: #1a1d27;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.7);
    padding: 16px;
    width: 280px;
    user-select: none;
}
.lb-dob-picker.open { display: block; }
.lb-dob-nav {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px; gap: 8px;
}
.lb-dob-nav select {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 8px;
    color: #fff;
    padding: 4px 8px;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    flex: 1;
}
.lb-dob-nav select:focus { outline: none; border-color: rgba(109,92,255,.5); }
.lb-dob-nav select option { background: #1a1d27; }
.lb-dob-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    text-align: center;
}
.lb-dob-grid .lb-dob-dow {
    font-size: .68rem; font-weight: 800;
    color: rgba(255,255,255,.35);
    padding: 4px 0;
    text-transform: uppercase;
}
.lb-dob-day {
    width: 100%; aspect-ratio: 1;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    font-size: .8rem;
    color: rgba(255,255,255,.75);
    cursor: pointer;
    transition: background .1s;
    border: none; background: transparent;
}
.lb-dob-day:hover { background: rgba(109,92,255,.3); color: #fff; }
.lb-dob-day.today { color: #a78fff; font-weight: 800; }
.lb-dob-day.selected { background: linear-gradient(135deg,#6d5cff,#b05cff) !important; color: #fff !important; font-weight: 800; }
.lb-dob-day.other-month { color: rgba(255,255,255,.18); }
.lb-dob-day.disabled { color: rgba(255,255,255,.12) !important; cursor: default; pointer-events: none; }
</style>

<div class="row">

    <!-- LEFT: Overview -->
    <div class="col-lg-4">
        <div id="accountSidebarNav"></div>
        <div class="js-sticky-block card mb-3 mb-lg-5" data-hs-sticky-block-options='{
            "parentSelector": "#accountSidebarNav",
            "breakpoint": "lg",
            "startPoint": "#accountSidebarNav",
            "endPoint": "#stickyBlockEndPoint",
            "stickyOffsetTop": 20
        }'>
            <div class="card-header">
                <h4 class="card-header-title">Overview</h4>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Profile completion</span>
                        <strong class="small"><?= $completionPct ?>%</strong>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-primary" style="width:<?= $completionPct ?>%"></div>
                    </div>
                    <div class="small text-muted mt-1"><?= $completedFields ?> of <?= count($fields) ?> fields completed</div>
                </div>

                <ul class="list-unstyled list-py-2 mb-0">
                    <li class="pb-0"><span class="card-subtitle">Account</span></li>
                    <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= SELLER_ID ?></li>
                    <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= esc(SELLER_DATA['email']) ?></li>

                    <li class="pt-4 pb-0"><span class="card-subtitle">Personal Info</span></li>
                    <li>
                        <i class="fa-duotone fa-user dropdown-item-icon"></i>
                        <?= !empty($data['fullname']) ? esc($data['fullname']) : '<span class="text-warning small">Missing</span>' ?>
                    </li>
                    <li>
                        <i class="fa-duotone fa-calendar dropdown-item-icon"></i>
                        <?= !empty($data['dob']) ? esc($data['dob']) : '<span class="text-warning small">Missing</span>' ?>
                    </li>
                    <li>
                        <i class="fa-duotone fa-location-dot dropdown-item-icon"></i>
                        <?= !empty($data['country']) ? esc($data['country']) : '<span class="text-warning small">Missing</span>' ?>
                    </li>

                    <li class="pt-4 pb-0"><span class="card-subtitle">Documents</span></li>
                    <li>
                        <i class="fa-duotone fa-id-card dropdown-item-icon"></i>
                        Front ID &mdash;
                        <?= !empty($data['id_front']) ? '<span class="text-success">Uploaded ✓</span>' : '<span class="text-warning">Missing</span>' ?>
                    </li>
                    <li>
                        <i class="fa-duotone fa-id-card-clip dropdown-item-icon"></i>
                        Back ID &mdash;
                        <?= !empty($data['id_back']) ? '<span class="text-success">Uploaded ✓</span>' : '<span class="text-warning">Missing</span>' ?>
                    </li>
                    <li>
                        <i class="fa-duotone fa-camera dropdown-item-icon"></i>
                        Selfie &mdash;
                        <?= !empty($data['selfie']) ? '<span class="text-success">Uploaded ✓</span>' : '<span class="text-warning">Missing</span>' ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- RIGHT: Form -->
    <div class="col-lg-8">
        <div class="d-grid gap-3 gap-lg-5">

            <form id="pd-form" class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="seller_update_personals">
                <input type="hidden" name="id" value="<?= SELLER_ID ?>">

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Personal Details</h4>
                    </div>
                    <div class="card-body">

                        <div class="row mb-4">
                            <label for="fullnameLabel" class="col-sm-3 col-form-label form-label">Full Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="fullname"
                                       value="<?= esc($data['fullname'] ?? '') ?>"
                                       id="fullnameLabel" placeholder="e.g. Max Mustermann"
                                       <?= !empty($data['fullname']) ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="dobLabel" class="col-sm-3 col-form-label form-label">Date of Birth</label>
                            <div class="col-sm-9">
                                <div class="lb-dob-wrap">
                                    <input type="text" class="form-control" name="dob" id="dobLabel"
                                           value="<?= esc($data['dob'] ?? '') ?>"
                                           placeholder="DD-MM-YYYY" autocomplete="off" maxlength="10"
                                           <?= !empty($data['dob']) ? 'readonly' : '' ?>>
                                    <?php if (empty($data['dob'])): ?>
                                    <div class="lb-dob-picker" id="dobPicker">
                                        <div class="lb-dob-nav">
                                            <select id="dobMonth"></select>
                                            <select id="dobYear"></select>
                                        </div>
                                        <div class="lb-dob-grid" id="dobGrid"></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="addressLabel" class="col-sm-3 col-form-label form-label">Address</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="address"
                                       value="<?= esc($data['address'] ?? '') ?>"
                                       id="addressLabel" placeholder="Street, City, ZIP"
                                       <?= !empty($data['address']) ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="countryLabel" class="col-sm-3 col-form-label form-label">Country</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="country"
                                       value="<?= esc($data['country'] ?? '') ?>"
                                       id="countryLabel" placeholder="e.g. Germany"
                                       <?= !empty($data['country']) ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <?php if (empty($data['id_front'])): ?>
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Front ID Photo</label>
                                <div class="col-sm-9">
                                    <div class="lb-dropzone" id="dz_id_front">
                                        <input type="file" name="id_front" id="id_frontLabel" accept="image/png,image/jpeg,image/jpg,image/webp">
                                        <div class="lb-dropzone-preview" id="prev_id_front">
                                            <img src="" alt="">
                                            <span class="lb-dz-clear" data-dz="id_front" title="Remove"><i class="fa-solid fa-xmark"></i></span>
                                        </div>
                                        <div class="lb-dropzone-body" id="body_id_front">
                                            <div class="lb-dropzone-icon"><i class="fa-duotone fa-id-card"></i></div>
                                            <div class="lb-dropzone-text">Click, drag &amp; drop or paste</div>
                                            <div class="lb-dropzone-hint">PNG, JPG, WEBP — max 8 MB</div>
                                        </div>
                                        <div class="lb-dropzone-name" id="name_id_front"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($data['id_back'])): ?>
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Back ID Photo</label>
                                <div class="col-sm-9">
                                    <div class="lb-dropzone" id="dz_id_back">
                                        <input type="file" name="id_back" id="id_backLabel" accept="image/png,image/jpeg,image/jpg,image/webp">
                                        <div class="lb-dropzone-preview" id="prev_id_back">
                                            <img src="" alt="">
                                            <span class="lb-dz-clear" data-dz="id_back" title="Remove"><i class="fa-solid fa-xmark"></i></span>
                                        </div>
                                        <div class="lb-dropzone-body" id="body_id_back">
                                            <div class="lb-dropzone-icon"><i class="fa-duotone fa-id-card-clip"></i></div>
                                            <div class="lb-dropzone-text">Click, drag &amp; drop or paste</div>
                                            <div class="lb-dropzone-hint">PNG, JPG, WEBP — max 8 MB</div>
                                        </div>
                                        <div class="lb-dropzone-name" id="name_id_back"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($data['selfie'])): ?>
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Selfie</label>
                                <div class="col-sm-9">
                                    <div class="lb-dropzone" id="dz_selfie">
                                        <input type="file" name="selfie" id="selfieLabel" accept="image/png,image/jpeg,image/jpg,image/webp">
                                        <div class="lb-dropzone-preview" id="prev_selfie">
                                            <img src="" alt="">
                                            <span class="lb-dz-clear" data-dz="selfie" title="Remove"><i class="fa-solid fa-xmark"></i></span>
                                        </div>
                                        <div class="lb-dropzone-body" id="body_selfie">
                                            <div class="lb-dropzone-icon"><i class="fa-duotone fa-camera"></i></div>
                                            <div class="lb-dropzone-text">Click, drag &amp; drop or paste</div>
                                            <div class="lb-dropzone-hint">PNG, JPG, WEBP — max 8 MB</div>
                                        </div>
                                        <div class="lb-dropzone-name" id="name_selfie"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($data['id_front']) || !empty($data['id_back']) || !empty($data['selfie'])): ?>
                            <div class="row g-3 lb-doc-grid">
                                <?php if (!empty($data['id_front'])): ?>
                                    <div class="col-md-4">
                                        <div class="lb-doc-thumb lb-doc-preview-btn"
                                             data-title="ID Front" data-url="<?= esc($data['id_front']) ?>"
                                             data-bs-toggle="modal" data-bs-target="#lbDocPreviewModal">
                                            <img src="<?= esc($data['id_front']) ?>" alt="ID Front">
                                            <div class="lb-doc-meta"><span>Front ID</span><small>Uploaded ✓</small></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($data['id_back'])): ?>
                                    <div class="col-md-4">
                                        <div class="lb-doc-thumb lb-doc-preview-btn"
                                             data-title="ID Back" data-url="<?= esc($data['id_back']) ?>"
                                             data-bs-toggle="modal" data-bs-target="#lbDocPreviewModal">
                                            <img src="<?= esc($data['id_back']) ?>" alt="ID Back">
                                            <div class="lb-doc-meta"><span>Back ID</span><small>Uploaded ✓</small></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($data['selfie'])): ?>
                                    <div class="col-md-4">
                                        <div class="lb-doc-thumb lb-doc-preview-btn"
                                             data-title="Selfie" data-url="<?= esc($data['selfie']) ?>"
                                             data-bs-toggle="modal" data-bs-target="#lbDocPreviewModal">
                                            <img src="<?= esc($data['selfie']) ?>" alt="Selfie">
                                            <div class="lb-doc-meta"><span>Selfie</span><small>Uploaded ✓</small></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <?php if (!$fieldsHaveData): ?>
                        <div class="card-footer">
                            <button type="submit" form="pd-form" class="btn btn-primary">
                                <span class="indicator-label"><i class="fa-duotone fa-floppy-disk me-1"></i> Save Changes</span>
                                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                                <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Saved</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

            </form>
        </div>
        <div id="stickyBlockEndPoint"></div>
    </div>

</div>


<!-- Document Preview Modal -->
<div class="modal fade" id="lbDocPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white border-0" style="box-shadow:0 20px 60px rgba(0,0,0,.55);border-radius:18px;">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="lbDocPreviewTitle">Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="ratio ratio-16x9 d-none" id="lbDocPreviewFrameWrap">
                    <iframe id="lbDocPreviewFrame" src="" style="border:0;border-radius:12px;background:#0f1114;"></iframe>
                </div>
                <div id="lbDocPreviewImgWrap">
                    <img id="lbDocPreviewImg" src="" alt="Preview"
                         style="max-width:100%;max-height:75vh;width:auto;height:auto;display:block;margin:0 auto;border-radius:12px;box-shadow:0 16px 40px rgba(0,0,0,.45);">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a class="btn btn-outline-light" id="lbDocOpenNewTab" href="#" target="_blank" rel="noopener">
                    <i class="fa-duotone fa-arrow-up-right-from-square me-1"></i> Open in new tab
                </a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script>
$(document).on('ready', function () {

    // ── Custom DOB Picker (1970–2012) ──
    (function() {
        var MIN_YEAR = 1970, MAX_YEAR = 2012;
        var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var DAYS   = ['Mo','Tu','We','Th','Fr','Sa','Su'];

        var $input  = $('#dobLabel');
        var $picker = $('#dobPicker');
        var $month  = $('#dobMonth');
        var $year   = $('#dobYear');
        var $grid   = $('#dobGrid');

        // Parse stored DD-MM-YYYY
        var selDay = null, selMonth = null, selYear = null;
        var stored = $input.val();
        if (stored && stored.match(/^\d{2}-\d{2}-\d{4}$/)) {
            var p = stored.split('-');
            selDay = parseInt(p[0]); selMonth = parseInt(p[1]) - 1; selYear = parseInt(p[2]);
        }

        var curMonth = selMonth !== null ? selMonth : 0;
        var curYear  = selYear  !== null ? selYear  : 2000;

        // Build month select
        MONTHS.forEach(function(m, i) {
            $month.append('<option value="' + i + '">' + m + '</option>');
        });
        // Build year select (newest first for UX)
        for (var y = MAX_YEAR; y >= MIN_YEAR; y--) {
            $year.append('<option value="' + y + '">' + y + '</option>');
        }

        function render() {
            $month.val(curMonth);
            $year.val(curYear);

            var html = '';
            DAYS.forEach(function(d) { html += '<div class="lb-dob-dow">' + d + '</div>'; });

            var first = new Date(curYear, curMonth, 1).getDay(); // 0=Sun
            var startOffset = (first === 0) ? 6 : first - 1;    // Mon-based
            var daysInMonth = new Date(curYear, curMonth + 1, 0).getDate();
            var today = new Date();

            for (var i = 0; i < startOffset; i++) {
                var prevDay = new Date(curYear, curMonth, -startOffset + i + 1).getDate();
                html += '<button type="button" class="lb-dob-day other-month disabled">' + prevDay + '</button>';
            }
            for (var d2 = 1; d2 <= daysInMonth; d2++) {
                var cls = 'lb-dob-day';
                if (selDay === d2 && selMonth === curMonth && selYear === curYear) cls += ' selected';
                else if (today.getDate() === d2 && today.getMonth() === curMonth && today.getFullYear() === curYear) cls += ' today';
                html += '<button type="button" class="' + cls + '" data-d="' + d2 + '">' + d2 + '</button>';
            }
            $grid.html(html);
        }

        function setValue(d, m, y) {
            selDay = d; selMonth = m; selYear = y;
            var dd = String(d).padStart(2,'0');
            var mm = String(m + 1).padStart(2,'0');
            $input.val(dd + '-' + mm + '-' + y);
            $picker.removeClass('open');
        }

        // Open / close
        $input.on('click', function(e) {
            e.stopPropagation();
            render();
            $picker.toggleClass('open');
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.lb-dob-wrap').length) $picker.removeClass('open');
        });

        // Month / year change
        $month.on('change', function() { curMonth = parseInt($(this).val()); render(); });
        $year.on('change',  function() { curYear  = parseInt($(this).val()); render(); });

        // Day click
        $grid.on('click', '.lb-dob-day:not(.other-month):not(.disabled)', function() {
            setValue(parseInt($(this).data('d')), curMonth, curYear);
        });

        render();
    })();

    // ── Hide the sidebar "Apply" button ──
    // It's a submit button outside our form rendered by the sidebar partial
    $('body').on('DOMNodeInserted', function() {
        $('button[type="submit"], input[type="submit"]').not('#pd-form *').not('.btn-close').hide();
    });
    // Also hide immediately and on ready
    $('button[type="submit"], input[type="submit"]').not('#pd-form *').not('.btn-close').hide();

    // ── Dropzone: click / drag-drop / paste ──
    var slots = [
        { dz: 'dz_id_front',  input: 'id_frontLabel', prev: 'prev_id_front', body: 'body_id_front', name: 'name_id_front' },
        { dz: 'dz_id_back',   input: 'id_backLabel',  prev: 'prev_id_back',  body: 'body_id_back',  name: 'name_id_back'  },
        { dz: 'dz_selfie',    input: 'selfieLabel',   prev: 'prev_selfie',   body: 'body_selfie',   name: 'name_selfie'   },
    ];

    function applyFile(slot, file) {
        if (!file || !file.type.startsWith('image/')) return;
        var $dz    = $('#' + slot.dz);
        var $prev  = $('#' + slot.prev);
        var $body  = $('#' + slot.body);
        var $name  = $('#' + slot.name);
        var $input = $('#' + slot.input)[0];

        // Inject file into the hidden input
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            $input.files = dt.files;
        } catch(e) {}

        var reader = new FileReader();
        reader.onload = function(e) {
            $prev.find('img').attr('src', e.target.result);
            $prev.show();
            $body.hide();
            $name.text(file.name + ' · ' + Math.round(file.size / 1024) + ' KB');
            $dz.addClass('has-file');
        };
        reader.readAsDataURL(file);
    }

    function clearSlot(slot) {
        var $dz   = $('#' + slot.dz);
        var $prev = $('#' + slot.prev);
        var $body = $('#' + slot.body);
        var $name = $('#' + slot.name);
        var $input = $('#' + slot.input)[0];
        try { $input.value = ''; } catch(e) {}
        $prev.find('img').attr('src', '');
        $prev.hide();
        $body.show();
        $name.text('');
        $dz.removeClass('has-file');
    }

    slots.forEach(function(slot) {
        var $dz    = $('#' + slot.dz);
        var $input = $('#' + slot.input);
        if (!$dz.length || !$input.length) return;

        // File input change (click to browse)
        $input.on('change', function() {
            if (this.files && this.files[0]) applyFile(slot, this.files[0]);
        });

        // Drag over
        $dz.on('dragover dragenter', function(e) {
            e.preventDefault(); e.stopPropagation();
            $dz.addClass('drag-over');
        }).on('dragleave dragend', function(e) {
            $dz.removeClass('drag-over');
        }).on('drop', function(e) {
            e.preventDefault(); e.stopPropagation();
            $dz.removeClass('drag-over');
            var files = e.originalEvent.dataTransfer.files;
            if (files && files[0]) applyFile(slot, files[0]);
        });

        // Clear button
        $dz.on('click', '.lb-dz-clear', function(e) {
            e.preventDefault(); e.stopPropagation();
            clearSlot(slot);
        });
    });

    // Global paste — pastes into the first empty slot
    $(document).on('paste', function(e) {
        var items = (e.originalEvent.clipboardData || window.clipboardData).items;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                var file = items[i].getAsFile();
                // Find first empty slot
                for (var s = 0; s < slots.length; s++) {
                    var $input = $('#' + slots[s].input);
                    if ($input.length && (!$input[0].files || !$input[0].files[0])) {
                        applyFile(slots[s], file);
                        break;
                    }
                }
                break;
            }
        }
    });

    (function () {
        var modalEl = document.getElementById('lbDocPreviewModal');
        if (!modalEl) return;
        modalEl.addEventListener('show.bs.modal', function (evt) {
            var btn = evt.relatedTarget; if (!btn) return;
            var url = btn.getAttribute('data-url') || '';
            document.getElementById('lbDocPreviewTitle').textContent = btn.getAttribute('data-title') || 'Preview';
            document.getElementById('lbDocOpenNewTab').setAttribute('href', url || '#');
            var isPdf = (url.toLowerCase().indexOf('.pdf') !== -1);
            document.getElementById('lbDocPreviewFrameWrap').classList[isPdf ? 'remove' : 'add']('d-none');
            document.getElementById('lbDocPreviewImgWrap').classList[isPdf ? 'add' : 'remove']('d-none');
            if (isPdf) { document.getElementById('lbDocPreviewFrame').src = url; }
            else       { document.getElementById('lbDocPreviewImg').src = url; }
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('lbDocPreviewImg').src = '';
            document.getElementById('lbDocPreviewFrame').src = '';
        });
    })();

});
</script>
<?= $this->end() ?>
