<style>
    .avatar {
        position: relative;
    }

    .edit-icon-container {
        width: 30px;
        height: 30px;
        background-color: #35383a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        bottom: 5px;
        right: 5px;
        border: 1px solid #ccc;
        cursor: pointer;
        border: none;
        outline: none;
        padding: 0;
    }

    .edit-icon-container i {
        color: white;
    }

    .edit-cover-container {
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.6);
        border: none;
        color: #fff;
        border-radius: 50%;
        padding: 8px;
        cursor: pointer;
    }

/* --- Dark Datepicker + document thumbs (Booster) --- */
.daterangepicker{background:#14171b;border:1px solid rgba(255,255,255,.08);color:#fff;box-shadow:0 18px 50px rgba(0,0,0,.55)}
.daterangepicker:before,.daterangepicker:after{display:none}
.daterangepicker .calendar-table{background:#14171b;border:0}
.daterangepicker .calendar-table th,.daterangepicker .calendar-table td{color:#fff}
.daterangepicker td.available:hover,.daterangepicker th.available:hover{background:rgba(255,255,255,.08)}
.daterangepicker td.off,.daterangepicker td.off.in-range,.daterangepicker td.off.start-date,.daterangepicker td.off.end-date{background:#0f1114;color:rgba(255,255,255,.35)}
.daterangepicker td.in-range{background:rgba(108,93,211,.22);color:#fff}
.daterangepicker td.active,.daterangepicker td.active:hover{background:#6c5dd3;border-color:#6c5dd3;color:#fff}
.daterangepicker .drp-buttons{border-top:1px solid rgba(255,255,255,.08)}
.daterangepicker .drp-buttons .btn{background:#6c5dd3;border-color:#6c5dd3;color:#fff}
.daterangepicker select.monthselect,.daterangepicker select.yearselect{background:#0f1114;border:1px solid rgba(255,255,255,.12);color:#fff}
.daterangepicker .drp-calendar.left,.daterangepicker .drp-calendar.right{border:0}
.daterangepicker .drp-selected{color:rgba(255,255,255,.75)}
/* (Fallback) bootstrap-datepicker if used somewhere */
.datepicker,.datepicker-dropdown{background:#14171b;border:1px solid rgba(255,255,255,.08);color:#fff}
.datepicker table tr td,.datepicker table tr th{color:#fff}
.datepicker table tr td.day:hover{background:rgba(255,255,255,.08)}
.datepicker table tr td.active.active,.datepicker table tr td.active:hover{background:#6c5dd3;border-color:#6c5dd3;color:#fff}
.datepicker table tr td.old,.datepicker table tr td.new,.datepicker table tr td.disabled{color:rgba(255,255,255,.35)}
.datepicker-dropdown:before,.datepicker-dropdown:after{display:none}

/* Inline document thumbnails */
.lb-doc-grid{margin-top:14px}
.lb-doc-thumb{background:#14171b;border:1px solid rgba(255,255,255,.08);border-radius:12px;overflow:hidden;cursor:pointer;transition:transform .12s ease, box-shadow .12s ease}
.lb-doc-thumb:hover{transform:translateY(-1px);box-shadow:0 14px 30px rgba(0,0,0,.45)}
.lb-doc-thumb img{width:100%;height:120px;object-fit:cover;display:block;background:#0f1114}
.lb-doc-thumb .lb-doc-meta{padding:10px 12px;color:#fff;font-weight:600;font-size:.9rem;display:flex;align-items:center;justify-content:space-between;gap:10px}
.lb-doc-thumb .lb-doc-meta small{color:rgba(255,255,255,.65);font-weight:500}
.lb-upload-preview{margin-top:10px;display:none}
.lb-upload-preview img{width:100%;max-height:140px;object-fit:cover;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:#0f1114}
.lb-upload-preview .meta{margin-top:6px;color:rgba(255,255,255,.7);font-size:.85rem}

</style>



<?php
// Normalize games and server display so Personal Details uses the same overview as Profile.
if (!isset($data['servers']) || $data['servers'] === null) {
    $data['servers'] = [];
} elseif (is_string($data['servers'])) {
    $data['servers'] = array_values(array_filter(explode('|', $data['servers'])));
} elseif (!is_array($data['servers'])) {
    $data['servers'] = [];
}

if (!isset($data['games']) || $data['games'] === null) {
    $data['games'] = [];
} elseif (!is_array($data['games'])) {
    $data['games'] = array_values(array_filter(explode('|', (string)$data['games']), fn($v) => $v !== ''));
}

$lb_servers_display = 'N/A';
if (!empty($data['servers'])) {
    $lb_servers_display = implode(', ', array_map(function ($s) {
        return function_exists('util_format_server') ? util_format_server($s) : $s;
    }, $data['servers']));
}

$lb_timezone_raw = trim((string) ($data['timezone'] ?? ''));
if (function_exists('util_format_timezone_display')) {
    $lb_timezone_display = util_format_timezone_display($lb_timezone_raw);
} else {
    $lb_timezone_display = ($lb_timezone_raw !== '') ? $lb_timezone_raw : 'N/A';
}
?>

<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Personal Details - Booster Area | LoLBoost.gg'], 'contain' => false]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
    .avatar-upload {
        backdrop-filter: blur(5px);
        cursor: pointer;
    }
</style>
<style>

/* --- LoLBoost compact profile polish --- */
.lb-profile-compact .card,
.lb-personal-compact .card{
  border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.025);
  border-radius:16px;
  box-shadow:0 18px 42px rgba(0,0,0,.18);
  overflow:hidden;
}
.lb-profile-compact .card-header,
.lb-personal-compact .card-header{
  min-height:auto;
  padding:1rem 1.25rem;
  border-bottom:1px solid rgba(255,255,255,.07);
  background:linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,0));
}
.lb-profile-compact .card-body,
.lb-personal-compact .card-body{ padding:1.15rem 1.25rem; }
.lb-profile-compact .card-footer,
.lb-personal-compact .card-footer{
  padding:1rem 1.25rem;
  border-top:1px solid rgba(255,255,255,.07);
  background:rgba(0,0,0,.08);
}
.lb-profile-compact .form-label,
.lb-personal-compact .form-label{
  font-size:.72rem;
  letter-spacing:.06em;
  text-transform:uppercase;
  color:rgba(255,255,255,.56);
  font-weight:800;
}
.lb-profile-compact .form-control,
.lb-profile-compact .form-select,
.lb-personal-compact .form-control,
.lb-personal-compact .form-select{
  border-radius:12px;
  background:rgba(0,0,0,.18);
  border-color:rgba(255,255,255,.09);
}
.lb-profile-compact .form-control:focus,
.lb-profile-compact .form-select:focus,
.lb-personal-compact .form-control:focus,
.lb-personal-compact .form-select:focus{
  border-color:rgba(124,92,255,.55);
  box-shadow:0 0 0 .2rem rgba(124,92,255,.14);
}
.lb-profile-compact .row.mb-4,
.lb-personal-compact .row.mb-4{ margin-bottom:1rem!important; }
.lb-section-title{
  display:flex;align-items:center;gap:.55rem;
  margin:1.35rem 0 .95rem;
  padding:.65rem .8rem;
  border-radius:12px;
  background:rgba(255,255,255,.035);
  border:1px solid rgba(255,255,255,.07);
  font-size:.82rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
  color:rgba(255,255,255,.82);
}
.lb-section-title:first-child{margin-top:0;}
.lb-overview-list li:not(.pb-0):not(.pt-4){
  display:flex;align-items:center;gap:.55rem;
  padding:.52rem .65rem;
  margin:.18rem 0;
  border-radius:11px;
  color:rgba(255,255,255,.84);
}
.lb-overview-list .card-subtitle{
  font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.48);
}
.lb-overview-list .dropdown-item-icon{color:#8b5cf6!important;opacity:.95;}
.lb-profile-compact .btn-primary,
.lb-personal-compact .btn-primary{
  border-radius:12px;
  background:linear-gradient(135deg,#6d5efc,#8b5cf6);
  border-color:transparent;
  font-weight:700;
}
.lb-doc-grid{padding:0 1.25rem 1.25rem;}
.lb-doc-thumb{border-radius:14px;background:rgba(255,255,255,.03);border-color:rgba(255,255,255,.08);}
.lb-doc-thumb img{height:150px;}
.lb-doc-thumb .lb-doc-meta{background:rgba(0,0,0,.12);}
.lb-upload-preview img{max-height:170px;}
.lb-profile-compact .border.rounded,
.lb-profile-compact .card .border.rounded{
  border-color:rgba(255,255,255,.08)!important;
  background:rgba(255,255,255,.03);
  border-radius:14px!important;
}
.lb-profile-compact #boosterReferralLink{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-size:.86rem;}
.lb-profile-compact .lb-ip-box{border-radius:12px!important;}
@media (max-width:991.98px){
  .lb-profile-compact .js-sticky-block,
  .lb-personal-compact .js-sticky-block{position:static!important;}
}

.lb-title-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:10px;margin-right:.65rem;background:rgba(124,92,255,.14);border:1px solid rgba(124,92,255,.28);color:#a78bfa;vertical-align:middle;}
.card-header-title{display:flex;align-items:center;}
</style>
<?= $this->end() ?>

<?php
require_once __DIR__ . '/_shared.php';
ob_start();
lb_render_booster_area_profile_header('personal-details');
$__lb_profile_header = ob_get_clean();
$__lb_profile_header = str_replace([' lb-profile-hero', 'lb-profile-hero '], ['', ''], $__lb_profile_header);
echo $__lb_profile_header;
?>

<!-- Content -->
<div class="row lb-personal-compact">
    <div class="col-lg-4">

        <!-- Sticky Block Start Point -->
        <div id="accountSidebarNav"></div>

        <!-- Card -->
        <div class="js-sticky-block card mb-3 mb-lg-5" data-hs-sticky-block-options='{
            "parentSelector": "#accountSidebarNav",
            "breakpoint": "lg",
            "startPoint": "#accountSidebarNav",
            "endPoint": "#stickyBlockEndPoint",
            "stickyOffsetTop": 20
            }'>
            <!-- Header -->
            <div class="card-header">
                <h4 class="card-header-title"><span class="lb-title-icon"><i class="fa-solid fa-grid-2"></i></span>Overview</h4>
            </div>
            <!-- End Header -->

            <!-- Body -->
            <div class="card-body">
                <ul class="list-unstyled list-py-2 text-dark mb-0 lb-overview-list">
                    <li class="pb-0"><span class="card-subtitle">Account</span></li>
                    <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= BOOSTER_ID ?></li>
                    <?php
                        $lb_balance_cents = (int)(BOOSTER_DATA['balance'] ?? 0);
                        $lb_insurance_required_cents = function_exists('booster_insurance_required_cents') ? booster_insurance_required_cents(BOOSTER_DATA) : 0;
                        $lb_frozen_cents = function_exists('booster_insurance_frozen_cents') ? booster_insurance_frozen_cents(BOOSTER_DATA) : 0;
                        $lb_available_cents = function_exists('booster_available_for_payout_cents') ? booster_available_for_payout_cents(BOOSTER_DATA) : max($lb_balance_cents - $lb_insurance_required_cents, 0);
                    ?>
                    <li>
                      <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center gap-2">
                          <i class="fa-duotone fa-wallet dropdown-item-icon"></i>
                          <span data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right" title="Total balance includes your available payout amount and your insurance reserve. Insurance reserve: held as security and paid out when you leave the company.">
                            <span class="fw-semibold"><?= util_format_price_display($lb_balance_cents) ?> EUR</span>
                          </span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                          <i class="fa-duotone fa-shield-check dropdown-item-icon"></i>
                          <span class="fw-semibold" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right"
                                title="Total balance includes your available payout amount and your insurance reserve. Insurance reserve: held as security and paid out when you leave the company.">
                            <span class="text-muted fw-normal">Insurance:</span> <?= util_format_price_display($lb_frozen_cents) ?> EUR
                          </span>
                        </div>

                        <div class="ms-4 small text-muted">
                          Available for payout: <span class="text-dark fw-semibold"><?= util_format_price_display($lb_available_cents) ?> EUR</span>
                        </div>
                        <div class="ms-4 small text-muted">
                          Insurance is held as security and paid out when you leave the company.
                        </div>
                      </div>
                    </li>

                    <li class="pt-4 pb-0"><span class="card-subtitle">Contact</span></li>
                    <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= BOOSTER_DATA['email'] ?></li>
                    <li>
                        <i class="fa-brands fa-discord dropdown-item-icon"></i>
                        <?= !empty(BOOSTER_DATA['discord']) ? BOOSTER_DATA['discord'] : 'N/A' ?>
                    </li>
                    <li>
                        <i class="fa-brands fa-discord dropdown-item-icon"></i>
                        <?= !empty(BOOSTER_DATA['discord_id']) ? BOOSTER_DATA['discord_id'] : 'N/A' ?>
                    </li>

                   <li class="pt-4 pb-0"><span class="card-subtitle">Limits</span></li>
                
                <?php if (in_array('lol', $data['games'])): ?>
                  <li class="d-flex align-items-center">
                    <img
                      src="/public/assets/website/images/icons/league-of-legends.png"
                      alt="LoL"
                      class="dropdown-item-icon"
                      style="width:16px;height:16px;object-fit:contain;"
                    >
                    <span class="ms-2">LoL:
                      <?= util_format_rank_advanced($data['lol_tier_limit'], $data['lol_division_limit'], 'lol') ?>
                    </span>
                  </li>
                <?php endif; ?>
                
                <?php if (in_array('tft', $data['games'])): ?>
                  <li class="d-flex align-items-center">
                    <img
                      src="/public/assets/website/images/icons/teamfight-tactics.png"
                      alt="TFT"
                      class="dropdown-item-icon"
                      style="width:16px;height:16px;object-fit:contain;"
                    >
                    <span class="ms-2">TFT:
                      <?= util_format_rank_advanced($data['tft_tier_limit'], $data['tft_division_limit'], 'tft') ?>
                    </span>
                  </li>
                <?php endif; ?>
                
                <?php if (in_array('val', $data['games'])): ?>
                  <li class="d-flex align-items-center">
                    <img
                      src="/public/assets/website/images/icons/valorant.png"
                      alt="VAL"
                      class="dropdown-item-icon"
                      style="width:16px;height:16px;object-fit:contain;"
                    >
                    <span class="ms-2">VAL:
                      <?= util_format_rank_advanced($data['val_tier_limit'], $data['val_division_limit'], 'val') ?>
                    </span>
                  </li>
                <?php endif; ?>
                
                <li><i class="fa-duotone fa-timer dropdown-item-icon"></i> <?= (int)($data['solo_order_limit'] ?? 0) ?> Solo, <?= (int)($data['duo_order_limit'] ?? 0) ?> Duo</li>

                    <!-- ✅ Servers -->
                    <li class="pt-4 pb-0"><span class="card-subtitle">Servers</span></li>
                    <li>
                        <i class="fa-duotone fa-globe dropdown-item-icon"></i>
                        <?= $lb_servers_display ?>
                    </li>
                    <!-- ✅ Timezone -->
                    <li class="pt-4 pb-0"><span class="card-subtitle">Timezone</span></li>
                    <li>
                        <i class="fa-duotone fa-clock dropdown-item-icon"></i>
                        <?= esc($lb_timezone_display) ?>
                    </li>
                    <!-- ✅ END -->
                </ul>

                <?php if (BOOSTER_DATA['discord'] == null): ?>
                    <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= BOOSTER_DATA['id'] ?>"
                        class="btn btn-primary btn-sm mt-4 btn-block w-100">
                        <i class="fa-brands fa-discord me-1"></i> Connect to Discord
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= BOOSTER_DATA['id'] ?>"
                        class="btn btn-primary btn-sm mt-4 btn-block w-100">
                        <i class="fa-brands fa-discord me-1"></i> Reconnect to Discord
                    </a>
                <?php endif; ?>
            </div>
            <!-- End Body -->
        </div>
        <!-- End Card -->
    </div>

    <div class="col-lg-8">
        <div class="d-grid gap-3 gap-lg-5">

            <!-- Form -->
            <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
                <input type="text" name="action" value="booster_update_booster_personals" hidden>
                <input type="text" name="id" value="<?= BOOSTER_ID ?>" hidden>
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between w-100 gap-3">
                            <h4 class="card-header-title mb-0"><span class="lb-title-icon"><i class="fa-solid fa-id-card"></i></span>Personal Details</h4>
                            <?php
                              $lb_personal_complete = !empty($data['fullname']) && !empty($data['dob']) && !empty($data['address']) && !empty($data['country']) && !empty($data['id_front']) && !empty($data['id_back']) && !empty($data['selfie']);
                            ?>
                            <span class="badge <?= $lb_personal_complete ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' ?>">
                              <i class="fa-solid <?= $lb_personal_complete ? 'fa-check-circle' : 'fa-circle-exclamation' ?> me-1"></i><?= $lb_personal_complete ? 'Verified' : 'Incomplete' ?>
                            </span>
                        </div>
                    </div>
                    <!-- End Header -->
                    <div class="card-body">
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="fullnameLabel" class="col-sm-3 col-form-label form-label">Full Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="fullname"
                                    value="<?= $data['fullname'] ?? '' ?>" id="fullnameLabel" placeholder="fullname"
                                    aria-label="fullname">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="dobLabel" class="col-sm-3 col-form-label form-label">Date of Birth</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="dob" value="<?= $data['dob'] ?? '' ?>"
                                    id="dobLabel" placeholder="Date of Birth" aria-label="Date of Birth">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="addressLabel" class="col-sm-3 col-form-label form-label">Address</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="address"
                                    value="<?= $data['address'] ?? '' ?>" id="addressLabel" placeholder="Address"
                                    aria-label="Address">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="countryLabel" class="col-sm-3 col-form-label form-label">Country</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="country"
                                    value="<?= $data['country'] ?? '' ?>" id="countryLabel" placeholder="Country"
                                    aria-label="Country">
                            </div>
                        </div>
                        <?php if (empty($data['id_front'])): ?>
                            <div class="row mb-4">
                                <label for="id_frontLabel" class="col-sm-3 col-form-label form-label">Front ID Photo</label>
                                <div class="col-sm-9">
                                    <input type="file" name="id_front" id="id_frontLabel" class="form-control"
                                        accept="image/*" aria-label="Front ID Photo">
                                    <div class="lb-upload-preview" id="id_front_preview">
                                        <img src="" alt="id_front preview">
                                        <div class="meta"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($data['id_back'])): ?>
                            <div class="row mb-4">
                                <label for="id_backLabel" class="col-sm-3 col-form-label form-label">Back ID Photo</label>
                                <div class="col-sm-9">
                                    <input type="file" name="id_back" id="id_backLabel" class="form-control"
                                        accept="image/*" aria-label="Back ID Photo">
                                    <div class="lb-upload-preview" id="id_back_preview">
                                        <img src="" alt="id_back preview">
                                        <div class="meta"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($data['selfie'])): ?>
                            <div class="row mb-4">
                                <label for="selfieLabel" class="col-sm-3 col-form-label form-label">Selfie</label>
                                <div class="col-sm-9">
                                    <input type="file" name="selfie" id="selfieLabel" class="form-control" accept="image/*"
                                        aria-label="Selfie">
                                    <div class="lb-upload-preview" id="selfie_preview">
                                        <img src="" alt="selfie preview">
                                        <div class="meta"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

<div class="lb-section-title mx-3 mb-3"><i class="fa-solid fa-id-card"></i>Documents</div>
<div class="row g-3 lb-doc-grid">
    <?php if (!empty($data['id_front'])): ?>
        <div class="col-md-4">
            <div class="lb-doc-thumb lb-doc-preview-btn" data-title="ID Front" data-url="<?= $data['id_front'] ?>">
                <img src="<?= $data['id_front'] ?>" alt="ID Front">
                <div class="lb-doc-meta"><span>ID Front</span><small>Preview</small></div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['id_back'])): ?>
        <div class="col-md-4">
            <div class="lb-doc-thumb lb-doc-preview-btn" data-title="ID Back" data-url="<?= $data['id_back'] ?>">
                <img src="<?= $data['id_back'] ?>" alt="ID Back">
                <div class="lb-doc-meta"><span>ID Back</span><small>Preview</small></div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['selfie'])): ?>
        <div class="col-md-4">
            <div class="lb-doc-thumb lb-doc-preview-btn" data-title="Selfie" data-url="<?= $data['selfie'] ?>">
                <img src="<?= $data['selfie'] ?>" alt="Selfie">
                <div class="lb-doc-meta"><span>Selfie</span><small>Preview</small></div>
            </div>
        </div>
    <?php endif; ?>
</div>


                        <!-- Document Preview Modal -->
                        <div class="modal fade" id="lbDocPreviewModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content bg-dark text-white border-0" style="box-shadow:0 20px 60px rgba(0,0,0,.55);">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title" id="lbDocPreviewTitle">Preview</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body pt-0">
                                        <div class="d-none" id="lbDocPreviewImageWrap">
                                            <img id="lbDocPreviewImage" src="" alt="Preview" style="width:100%;max-height:78vh;object-fit:contain;border-radius:12px;background:#0f1114;">
                                        </div>
                                        <div class="ratio ratio-16x9 d-none" id="lbDocPreviewFrameWrap">
                                            <iframe id="lbDocPreviewFrame" src="" style="border:0;border-radius:12px;background:#0f1114;"></iframe>
                                        </div>
                                        <div class="text-center" id="lbDocPreviewImgWrap">
                                            <img id="lbDocPreviewImg" src="" alt="Preview" style="max-width:100%;max-height:75vh;border-radius:12px;box-shadow:0 16px 40px rgba(0,0,0,.45);">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <a class="btn btn-outline-light" id="lbDocOpenNewTab" href="#" target="_blank" rel="noopener">Open in new tab</a>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Document Preview Modal -->

                        <script>
                            (function(){
                                function isPdf(url){
                                    try{
                                        var u = (url || '').toLowerCase();
                                        return u.indexOf('.pdf') !== -1 || u.startsWith('data:application/pdf');
                                    }catch(e){ return false; }
                                }
                                var modalEl = document.getElementById('lbDocPreviewModal');
                                if(!modalEl) return;

                                modalEl.addEventListener('show.bs.modal', function(evt){
                                    var btn = evt.relatedTarget;
                                    if(!btn) return;
                                    var title = btn.getAttribute('data-title') || 'Preview';
                                    var url = btn.getAttribute('data-url') || '';
                                    document.getElementById('lbDocPreviewTitle').textContent = title;
                                    var open = document.getElementById('lbDocOpenNewTab');
                                    open.setAttribute('href', url || '#');

                                    var imgWrap = document.getElementById('lbDocPreviewImgWrap');
                                    var frameWrap = document.getElementById('lbDocPreviewFrameWrap');
                                    var img = document.getElementById('lbDocPreviewImg');
                                    var frame = document.getElementById('lbDocPreviewFrame');

                                    if(isPdf(url)){
                                        imgWrap.classList.add('d-none');
                                        frameWrap.classList.remove('d-none');
                                        frame.src = url;
                                        img.src = '';
                                    } else {
                                        frameWrap.classList.add('d-none');
                                        imgWrap.classList.remove('d-none');
                                        img.src = url;
                                        frame.src = '';
                                    }
                                });

                                modalEl.addEventListener('hidden.bs.modal', function(){
                                    var img = document.getElementById('lbDocPreviewImg');
                                    var frame = document.getElementById('lbDocPreviewFrame');
                                    if(img) img.src = '';
                                    if(frame) frame.src = '';
                                });
                            })();
                        </script>


                    <?php
                    $fields = [
                        'fullname',
                        'dob',
                        'address',
                        'country',
                        'id_front',
                        'id_back',
                        'selfie'
                    ];

                    $fieldsHaveData = true;

                    foreach ($fields as $field) {
                        if (isset($data[$field]) && !empty($data[$field])) {
                            continue;
                        } else {
                            $fieldsHaveData = false;
                            break;
                        }
                    }

                    if (!$fieldsHaveData):
                        ?>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">
                                    Update Settings
                                </span>
                                <span class="indicator-progress">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </span>
                                <span class="indicator-success">
                                    <i class="fa-regular fa-circle-check fs-3"></i>
                                </span>
                            </button>
                        </div>

                    <?php endif; ?>
                </div>
                <!-- End Card -->
            </form>
            <!-- End Form -->

        </div>
        <div id="stickyBlockEndPoint"></div>
    </div>
</div>
<!-- End Content -->

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-nav-scroller/dist/hs-nav-scroller.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script>
    $(document).on('ready', function () {

        $('.avatar').mouseover(function () {
            $('.avatar-upload').stop().fadeIn(100);
            $('.avatar-upload').removeClass('d-none');
        });

        $('.avatar').mouseout(function () {
            $('.avatar-upload').stop().fadeOut(200, function () {
                $(this).addClass('d-none');
            });
        });

        $('#dobLabel').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY',
            }
        });


        // Open preview modal from both buttons and inline thumbnails
        $(document).on('click', '.lb-doc-preview-btn', function (e) {
            var title = $(this).data('title') || 'Preview';
            var url = $(this).data('url') || '';
            $('#lbDocPreviewTitle').text(title);

            // Prefer image preview for uploaded docs (images)
            $('#lbDocPreviewImage').attr('src', url);
            $('#lbDocPreviewImageWrap').removeClass('d-none');
            $('#lbDocPreviewFrameWrap').addClass('d-none');

            // If this element isn't already wired with data-bs-toggle, open the modal manually
            if (!$(this).attr('data-bs-toggle')) {
                var modalEl = document.getElementById('lbDocPreviewModal');
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            }
        });

        // Live preview only when upload inputs exist (i.e., onboarding / first upload)
 only when upload inputs exist (i.e., onboarding / first upload)
        function bindUploadPreview(inputId, previewId){
            var $input = $('#' + inputId);
            if(!$input.length) return;
            $input.on('change', function(){
                var file = this.files && this.files[0] ? this.files[0] : null;
                var $wrap = $('#' + previewId);
                if(!file){
                    $wrap.hide();
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e){
                    $wrap.find('img').attr('src', e.target.result);
                    var sizeKb = Math.round(file.size / 1024);
                    $wrap.find('.meta').text((file.name || 'selected') + ' • ' + sizeKb + ' KB');
                    $wrap.show();
                };
                reader.readAsDataURL(file);
            });
        }
        bindUploadPreview('id_frontLabel','id_front_preview');
        bindUploadPreview('id_backLabel','id_back_preview');
        bindUploadPreview('selfieLabel','selfie_preview');

    });
</script>
<?= $this->end() ?>