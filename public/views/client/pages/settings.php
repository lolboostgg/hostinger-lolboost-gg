<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$clientBirthday   = !empty(CLIENT_DATA['birthday'] ?? '') ? (string) CLIENT_DATA['birthday'] : '';
$clientBirthdayTs = $clientBirthday !== '' ? strtotime($clientBirthday) : false;
$clientBirthdayDay   = $clientBirthdayTs ? date('d', $clientBirthdayTs) : '';
$clientBirthdayMonth = $clientBirthdayTs ? date('m', $clientBirthdayTs) : '';
$clientBirthdayYear  = $clientBirthdayTs ? date('Y',  $clientBirthdayTs) : '';
$birthdayMonths = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];

// Referral data
$showReferral = function_exists('lb_referral_client_is_allowed') && lb_referral_client_is_allowed((int) CLIENT_ID);
if ($showReferral) {
    $lb_ref_settings = function_exists('lb_referral_get_settings') ? lb_referral_get_settings() : ['client_reward_percent' => 5];
    $lb_client_ref   = function_exists('lb_referral_get_dashboard_data') ? lb_referral_get_dashboard_data('client', (int) CLIENT_ID) : ['share_url'=>'','earnings_points'=>0,'clicks'=>0,'signups'=>0,'purchases'=>0];
    $lb_client_reward_percent = (float)($lb_ref_settings['client_reward_percent'] ?? 5);
    $lb_client_share_url      = (string)($lb_client_ref['share_url'] ?? '');
}
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
/* ══════════════════════════════════════════
   Settings page — dark modern style
══════════════════════════════════════════ */

/* ── Profile hero ── */
.st-profile-hero{position:relative;border-radius:20px;overflow:hidden;margin-bottom:28px;box-shadow:0 4px 32px rgba(0,0,0,.35);}
.st-banner{width:100%;height:160px;object-fit:cover;object-position:top;display:block;filter:brightness(.7);}
.st-profile-body{background:#25282a;border:1px solid rgba(255,255,255,.07);border-top:none;padding:0 28px 24px;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.st-avatar-wrap{position:relative;margin-top:-44px;}
.st-avatar{width:88px;height:88px;border-radius:50%;border:3px solid #25282a;object-fit:cover;display:block;cursor:pointer;transition:filter .15s;}
.st-avatar:hover{filter:brightness(.75);}
.st-avatar-edit{position:absolute;bottom:2px;right:2px;width:26px;height:26px;border-radius:50%;background:#35383a;border:1px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;cursor:pointer;color:rgba(255,255,255,.8);font-size:.65rem;transition:background .12s;}
.st-avatar-edit:hover{background:#4a4e52;}
.st-profile-info{flex:1;padding-bottom:4px;}
.st-profile-name{font-size:1.1rem;font-weight:900;color:rgba(255,255,255,.92);margin:0 0 2px;}
.st-profile-email{font-size:.8rem;color:rgba(255,255,255,.4);}

/* ── Section cards ── */
.st-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:visible;box-shadow:0 2px 20px rgba(0,0,0,.2);margin-bottom:20px;}
.st-card-header{padding:18px 24px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1);display:flex;align-items:center;gap:10px;border-radius:20px 20px 0 0;overflow:hidden;}
.st-card-header-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.88rem;flex-shrink:0;}
.st-card-header-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.85);margin:0;}
.st-card-header-sub{font-size:.78rem;color:rgba(255,255,255,.35);margin:2px 0 0;}
.st-card-body{padding:24px;}
.st-card-footer{padding:16px 24px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.08);display:flex;align-items:center;justify-content:flex-end;border-radius:0 0 20px 20px;overflow:hidden;}

/* ── Form fields ── */
.st-field{margin-bottom:20px;}
.st-field:last-child{margin-bottom:0;}
.st-label{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;display:flex;align-items:center;gap:6px;}
.st-label i{font-size:.8rem;color:rgba(255,255,255,.25);}
.st-input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:11px;color:#fff;font-size:.9rem;padding:10px 14px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;}
.st-input:focus{border-color:rgba(109,92,255,.5);box-shadow:0 0 0 3px rgba(109,92,255,.13);}
.st-input::placeholder{color:rgba(255,255,255,.2);}
.st-hint{font-size:.72rem;color:rgba(255,255,255,.25);margin-top:5px;}
.st-divider{height:1px;background:rgba(255,255,255,.05);margin:20px 0;}

/* ── Custom dropdowns (birthday) ── */
.st-selects{display:grid;grid-template-columns:1fr 1.6fr 1fr;gap:8px;position:relative;}
.st-dd{position:relative;}
.st-dd-btn{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:11px;color:rgba(255,255,255,.75);font-size:.88rem;padding:10px 36px 10px 14px;outline:none;cursor:pointer;transition:border-color .15s,box-shadow .15s;font-family:inherit;text-align:left;display:flex;align-items:center;justify-content:space-between;white-space:nowrap;overflow:hidden;}
.st-dd-btn:hover{border-color:rgba(255,255,255,.18);}
.st-dd.open .st-dd-btn{border-color:rgba(109,92,255,.5);box-shadow:0 0 0 3px rgba(109,92,255,.13);}
.st-dd-arrow{font-size:.65rem;color:rgba(255,255,255,.3);flex-shrink:0;transition:transform .15s;}
.st-dd.open .st-dd-arrow{transform:rotate(180deg);color:#c4b5fd;}
.st-dd-menu{position:absolute;top:calc(100% + 5px);left:0;right:0;background:#1e2022;border:1px solid rgba(255,255,255,.1);border-radius:12px;overflow:hidden;overflow-y:auto;max-height:200px;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,.5);display:none;animation:dd-drop .12s ease;}
@keyframes dd-drop{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
.st-dd.open .st-dd-menu{display:block;}
.st-dd-item{padding:9px 14px;font-size:.85rem;color:rgba(255,255,255,.65);cursor:pointer;transition:background .1s,color .1s;}
.st-dd-item:hover{background:rgba(109,92,255,.14);color:#c4b5fd;}
.st-dd-item.active{background:rgba(109,92,255,.18);color:#c4b5fd;font-weight:700;}
/* ── Drag & Drop zone ── */
.st-dropzone{position:relative;border:2px dashed rgba(255,255,255,.12);border-radius:14px;transition:border-color .15s,background .15s;overflow:hidden;min-height:160px;display:flex;align-items:center;justify-content:center;}
.st-dropzone.dragover{border-color:rgba(109,92,255,.6);background:rgba(109,92,255,.07);}
.st-dropzone-input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.st-dropzone-content{text-align:center;padding:28px 20px;pointer-events:none;}
.st-dropzone-icon{font-size:2rem;color:rgba(255,255,255,.2);margin-bottom:10px;}
.st-dropzone.dragover .st-dropzone-icon{color:#c4b5fd;}
.st-dropzone-title{font-size:.9rem;font-weight:800;color:rgba(255,255,255,.6);margin-bottom:4px;}
.st-dropzone-sub{font-size:.78rem;color:rgba(255,255,255,.3);margin-bottom:6px;}
.st-dropzone-sub kbd{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:5px;padding:1px 6px;font-size:.72rem;color:rgba(255,255,255,.5);font-family:inherit;}
.st-dropzone-types{font-size:.7rem;color:rgba(255,255,255,.2);}
.st-dropzone-preview{width:100%;padding:20px;display:flex;flex-direction:column;align-items:center;gap:10px;position:relative;}
.st-dropzone-preview img{width:90px;height:90px;border-radius:50%;object-fit:cover;border:2px solid rgba(109,92,255,.4);box-shadow:0 0 0 4px rgba(109,92,255,.1);}
.st-dropzone-preview-name{font-size:.75rem;color:rgba(255,255,255,.35);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.st-dropzone-clear{position:absolute;top:12px;right:12px;width:26px;height:26px;border-radius:50%;background:rgba(251,113,133,.15);border:1px solid rgba(251,113,133,.3);color:#fb7185;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.7rem;transition:background .12s;}
.st-dropzone-clear:hover{background:rgba(251,113,133,.28);}
.st-modal-submit:disabled{opacity:.4;cursor:not-allowed;}
.st-modal-submit:disabled:hover{transform:none;}

/* ── Two-column settings grid ── */
.st-settings-grid{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;}
.st-col-left{min-width:0;}
.st-col-right{min-width:0;}
@media(max-width:900px){.st-settings-grid{grid-template-columns:1fr;}}

/* ── Password fields ── */
.st-pw-wrap{position:relative;}
.st-pw-wrap .st-input{padding-right:42px;}
.st-pw-toggle{position:absolute;top:50%;right:13px;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.3);cursor:pointer;padding:0;font-size:.9rem;transition:color .12s;}
.st-pw-toggle:hover{color:rgba(255,255,255,.7);}

/* ── Referral stats ── */
.st-ref-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
.st-ref-stat{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:13px;padding:14px 16px;}
.st-ref-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
.st-ref-stat-value{font-size:1.35rem;font-weight:900;color:rgba(255,255,255,.88);}
.st-ref-link-wrap{display:flex;gap:8px;align-items:center;}
.st-ref-input{flex:1;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:11px;color:rgba(255,255,255,.6);font-size:.82rem;padding:10px 14px;outline:none;font-family:inherit;}
.st-ref-copy{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:11px;background:rgba(109,92,255,.18);border:1px solid rgba(109,92,255,.35);color:#c4b5fd;font-size:.82rem;font-weight:800;cursor:pointer;transition:all .13s;white-space:nowrap;font-family:inherit;}
.st-ref-copy:hover{background:rgba(109,92,255,.28);transform:translateY(-1px);}
.st-ref-note{margin-top:12px;padding:12px 16px;background:rgba(109,92,255,.08);border:1px solid rgba(109,92,255,.18);border-radius:11px;font-size:.8rem;color:rgba(255,255,255,.5);}
.st-ref-note strong{color:#c4b5fd;}

/* ── Save button ── */
.st-btn-save{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:11px;background:rgba(109,92,255,.2);border:1px solid rgba(109,92,255,.4);color:#c4b5fd;font-size:.875rem;font-weight:800;cursor:pointer;transition:all .13s;font-family:inherit;}
.st-btn-save:hover{background:rgba(109,92,255,.3);transform:translateY(-1px);}
.st-btn-save--green{background:rgba(74,222,128,.14);border-color:rgba(74,222,128,.35);color:#4ade80;}
.st-btn-save--green:hover{background:rgba(74,222,128,.22);}

/* ── Upload modal ── */
.st-modal .modal-content{background:#25282a;border:1px solid rgba(255,255,255,.08);border-radius:20px;overflow:hidden;}
.st-modal .modal-header{background:rgba(0,0,0,.1);border-bottom:1px solid rgba(255,255,255,.07);padding:18px 24px;}
.st-modal .modal-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.88);}
.st-modal .modal-body{padding:24px;}
.st-modal .modal-footer{border-top:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.08);padding:14px 24px;display:flex;gap:8px;justify-content:flex-end;}
.st-modal .btn-close{filter:invert(1) opacity(.5);}
.st-modal-cancel{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.5);font-size:.82rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .12s;}
.st-modal-cancel:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.8);}
.st-modal-submit{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:10px;background:rgba(109,92,255,.18);border:1px solid rgba(109,92,255,.35);color:#c4b5fd;font-size:.82rem;font-weight:800;cursor:pointer;font-family:inherit;transition:all .12s;}
.st-modal-submit:hover{background:rgba(109,92,255,.28);}


/* ── Force custom upload modal, overrides Bootstrap defaults ── */
#upload-icon-modal .modal-dialog{max-width:560px !important;}
#upload-icon-modal .modal-content{background:#25282a !important;border:1px solid rgba(255,255,255,.08) !important;border-radius:22px !important;box-shadow:0 24px 80px rgba(0,0,0,.55) !important;overflow:hidden !important;color:#fff !important;}
#upload-icon-modal .modal-header{background:linear-gradient(135deg,rgba(109,92,255,.16),rgba(255,255,255,.02)) !important;border-bottom:1px solid rgba(255,255,255,.07) !important;padding:20px 24px !important;}
#upload-icon-modal .modal-title{font-size:1rem !important;font-weight:900 !important;color:rgba(255,255,255,.92) !important;display:flex !important;align-items:center !important;gap:10px !important;}
#upload-icon-modal .modal-body{padding:24px !important;background:#25282a !important;}
#upload-icon-modal .modal-footer{border-top:1px solid rgba(255,255,255,.07) !important;background:rgba(0,0,0,.10) !important;padding:16px 24px !important;display:flex !important;gap:10px !important;justify-content:flex-end !important;}
#upload-icon-modal .btn-close{filter:invert(1) opacity(.5) !important;box-shadow:none !important;}
#upload-icon-modal input[type="file"].st-dropzone-input{position:absolute !important;inset:0 !important;opacity:0 !important;width:100% !important;height:100% !important;cursor:pointer !important;}
#upload-icon-modal .st-dropzone{background:rgba(255,255,255,.025) !important;border:2px dashed rgba(255,255,255,.13) !important;border-radius:18px !important;min-height:190px !important;}
#upload-icon-modal .st-dropzone.dragover{border-color:rgba(109,92,255,.75) !important;background:rgba(109,92,255,.09) !important;}
#upload-icon-modal .st-dropzone-title{color:rgba(255,255,255,.82) !important;}
#upload-icon-modal .st-dropzone-sub{color:rgba(255,255,255,.42) !important;}
#upload-icon-modal .st-modal-cancel,#upload-icon-modal .st-modal-submit{border:0 !important;box-shadow:none !important;}
#upload-icon-modal .st-modal-cancel{background:rgba(255,255,255,.08) !important;color:rgba(255,255,255,.72) !important;}
#upload-icon-modal .st-modal-submit{background:#6d5cff !important;color:#fff !important;}
#upload-icon-modal .st-modal-submit:disabled{background:rgba(109,92,255,.35) !important;color:rgba(255,255,255,.55) !important;}

/* ── Search inside birthday dropdowns ── */
.st-dd-search-wrap{position:sticky;top:0;background:#1e2022;padding:8px;border-bottom:1px solid rgba(255,255,255,.08);z-index:2;}
.st-dd-search{width:100%;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.10);border-radius:9px;color:#fff;font-size:.8rem;padding:8px 10px;outline:none;font-family:inherit;}
.st-dd-search:focus{border-color:rgba(109,92,255,.55);box-shadow:0 0 0 3px rgba(109,92,255,.12);}
.st-dd-search::placeholder{color:rgba(255,255,255,.25);}
.st-dd-empty{padding:10px 14px;font-size:.78rem;color:rgba(255,255,255,.28);display:none;}

</style>
<?= $this->stop() ?>

<!-- ── Profile Hero ── -->
<div class="st-profile-hero">
    <img class="st-banner" src="<?= ASSET_URL ?>/core/main/img/banners/leona.jpeg" alt="Banner">
    <div class="st-profile-body">
        <div class="st-avatar-wrap">
            <img class="st-avatar" src="<?= $h(CLIENT_DATA['icon']) ?>" alt="<?= $h(CLIENT_DATA['username']) ?>"
                data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
            <button class="st-avatar-edit" data-bs-toggle="modal" data-bs-target="#upload-icon-modal" type="button">
                <i class="fa-solid fa-pen"></i>
            </button>
        </div>
        <div class="st-profile-info">
            <div class="st-profile-name"><?= $h(CLIENT_DATA['username']) ?></div>
            <div class="st-profile-email"><?= $h(CLIENT_DATA['email']) ?></div>
        </div>
    </div>
</div>

<!-- ── Two-column layout ── -->
<div class="st-settings-grid">

<!-- LEFT: Account + Password -->
<div class="st-col-left">

    <!-- ── Account Settings ── -->
    <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" novalidate>
        <input type="hidden" name="action" value="client_update_profile">
        <div class="st-card">
            <div class="st-card-header">
                <div class="st-card-header-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.25);color:#c4b5fd;">
                    <i class="fa-duotone fa-user"></i>
                </div>
                <div>
                    <div class="st-card-header-title">Account Settings</div>
                    <div class="st-card-header-sub">Update your profile information</div>
                </div>
            </div>
            <div class="st-card-body">

                <div class="st-field">
                    <label for="usernameLabel" class="st-label"><i class="fa-duotone fa-at"></i> Username</label>
                    <input type="text" class="st-input" name="username" id="usernameLabel"
                        placeholder="Your username" value="<?= $h(CLIENT_DATA['username']) ?>">
                </div>

                <div class="st-divider"></div>

                <div class="st-field">
                    <label for="discordLabel" class="st-label"><i class="fa-brands fa-discord"></i> Discord</label>
                    <input type="text" class="st-input" name="discord" id="discordLabel"
                        placeholder="e.g. username#0000" value="<?= $h(CLIENT_DATA['discord'] ?? '') ?>">
                </div>

                <div class="st-divider"></div>

                <div class="st-field">
                    <label for="emailLabel" class="st-label"><i class="fa-duotone fa-envelope"></i> Email</label>
                    <input type="email" class="st-input" name="email" id="emailLabel"
                        placeholder="your@email.com" value="<?= $h(CLIENT_DATA['email']) ?>">
                </div>

                <div class="st-divider"></div>

                <div class="st-field">
                    <label class="st-label"><i class="fa-duotone fa-cake-candles"></i> Birthday</label>
                    <input type="hidden" name="birthday" id="birthdayLabel" value="<?= $h($clientBirthday) ?>">
                    <div class="st-selects">
                        <!-- Day -->
                        <div class="st-dd" id="ddDay" data-value="<?= $h($clientBirthdayDay) ?>">
                            <button type="button" class="st-dd-btn" id="ddDayBtn">
                                <span id="ddDayLabel"><?= $clientBirthdayDay ?: 'Day' ?></span>
                                <i class="fa-solid fa-chevron-down st-dd-arrow"></i>
                            </button>
                            <div class="st-dd-menu" id="ddDayMenu">
                                <div class="st-dd-search-wrap"><input type="text" class="st-dd-search" placeholder="Search day..." autocomplete="off"></div>
                                <div class="st-dd-item <?= !$clientBirthdayDay ? 'active' : '' ?>" data-value="">Day</div>
                                <?php for ($day = 1; $day <= 31; $day++):
                                    $dv = str_pad((string)$day, 2, '0', STR_PAD_LEFT); ?>
                                    <div class="st-dd-item <?= $clientBirthdayDay === $dv ? 'active' : '' ?>" data-value="<?= $dv ?>"><?= $dv ?></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <!-- Month -->
                        <div class="st-dd" id="ddMonth" data-value="<?= $h($clientBirthdayMonth) ?>">
                            <button type="button" class="st-dd-btn" id="ddMonthBtn">
                                <span id="ddMonthLabel"><?= $clientBirthdayMonth ? $birthdayMonths[$clientBirthdayMonth] : 'Month' ?></span>
                                <i class="fa-solid fa-chevron-down st-dd-arrow"></i>
                            </button>
                            <div class="st-dd-menu" id="ddMonthMenu">
                                <div class="st-dd-search-wrap"><input type="text" class="st-dd-search" placeholder="Search month..." autocomplete="off"></div>
                                <div class="st-dd-item <?= !$clientBirthdayMonth ? 'active' : '' ?>" data-value="">Month</div>
                                <?php foreach ($birthdayMonths as $mv => $ml): ?>
                                    <div class="st-dd-item <?= $clientBirthdayMonth === $mv ? 'active' : '' ?>" data-value="<?= $mv ?>"><?= $ml ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Year -->
                        <div class="st-dd" id="ddYear" data-value="<?= $h($clientBirthdayYear) ?>">
                            <button type="button" class="st-dd-btn" id="ddYearBtn">
                                <span id="ddYearLabel"><?= $clientBirthdayYear ?: 'Year' ?></span>
                                <i class="fa-solid fa-chevron-down st-dd-arrow"></i>
                            </button>
                            <div class="st-dd-menu" id="ddYearMenu">
                                <div class="st-dd-search-wrap"><input type="text" class="st-dd-search" placeholder="Search year..." autocomplete="off"></div>
                                <div class="st-dd-item <?= !$clientBirthdayYear ? 'active' : '' ?>" data-value="">Year</div>
                                <?php for ($year = (int)date('Y'); $year >= 1920; $year--): ?>
                                    <div class="st-dd-item <?= $clientBirthdayYear === (string)$year ? 'active' : '' ?>" data-value="<?= $year ?>"><?= $year ?></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="st-hint">We'll send you a personal birthday discount once per year 🎂</div>
                </div>

            </div>
            <div class="st-card-footer">
                <button type="submit" class="st-btn-save">
                    <span class="indicator-label"><i class="fa-duotone fa-floppy-disk"></i> Save Changes</span>
                    <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                    <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Saved!</span>
                </button>
            </div>
        </div>
    </form>

    <!-- ── Password Settings ── -->
    <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" novalidate>
        <input type="hidden" name="action" value="client_update_password">
        <div class="st-card">
            <div class="st-card-header">
                <div class="st-card-header-icon" style="background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.25);color:#fb7185;">
                    <i class="fa-duotone fa-lock"></i>
                </div>
                <div>
                    <div class="st-card-header-title">Password</div>
                    <div class="st-card-header-sub">Change your account password</div>
                </div>
            </div>
            <div class="st-card-body">
                <div class="st-field">
                    <label for="np" class="st-label"><i class="fa-duotone fa-lock-keyhole"></i> Current Password</label>
                    <div class="st-pw-wrap">
                        <input type="password" class="st-input" name="password" id="np" placeholder="••••••••">
                        <button type="button" class="st-pw-toggle" onclick="togglePassword('np')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="st-divider"></div>
                <div class="st-field" style="margin-bottom:0;">
                    <label for="cnp" class="st-label"><i class="fa-duotone fa-lock-keyhole-open"></i> New Password</label>
                    <div class="st-pw-wrap">
                        <input type="password" class="st-input" name="new_password" id="cnp" placeholder="••••••••">
                        <button type="button" class="st-pw-toggle" onclick="togglePassword('cnp')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
            <div class="st-card-footer">
                <button type="submit" class="st-btn-save st-btn-save--green">
                    <span class="indicator-label"><i class="fa-duotone fa-key"></i> Update Password</span>
                    <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                    <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Updated!</span>
                </button>
            </div>
        </div>
    </form>

</div><!-- end st-col-left -->

<!-- RIGHT: Referral -->
<div class="st-col-right">
    <?php if ($showReferral): ?>
    <div class="st-card">
        <div class="st-card-header">
            <div class="st-card-header-icon" style="background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.22);color:#facc15;">
                <i class="fa-duotone fa-user-plus"></i>
            </div>
            <div>
                <div class="st-card-header-title">Invite Friends</div>
                <div class="st-card-header-sub">Earn coins for every referred purchase</div>
            </div>
        </div>
        <div class="st-card-body">
            <div class="st-ref-stats">
                <div class="st-ref-stat">
                    <div class="st-ref-stat-label" style="color:rgba(250,204,21,.6);">Earnings</div>
                    <div class="st-ref-stat-value" style="color:#facc15;"><?= number_format((float)($lb_client_ref['earnings_points'] ?? 0), 2) ?></div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.25);margin-top:2px;">LB Coins</div>
                </div>
                <div class="st-ref-stat">
                    <div class="st-ref-stat-label" style="color:rgba(109,92,255,.7);">Clicks</div>
                    <div class="st-ref-stat-value"><?= (int)($lb_client_ref['clicks'] ?? 0) ?></div>
                </div>
                <div class="st-ref-stat">
                    <div class="st-ref-stat-label" style="color:rgba(74,222,128,.6);">Signups</div>
                    <div class="st-ref-stat-value"><?= (int)($lb_client_ref['signups'] ?? 0) ?></div>
                </div>
                <div class="st-ref-stat">
                    <div class="st-ref-stat-label" style="color:rgba(251,191,36,.6);">Purchases</div>
                    <div class="st-ref-stat-value"><?= (int)($lb_client_ref['purchases'] ?? 0) ?></div>
                </div>
            </div>
            <label class="st-label" style="margin-bottom:8px;"><i class="fa-duotone fa-link"></i> Your referral link</label>
            <div class="st-ref-link-wrap" style="flex-direction:column;gap:8px;">
                <input type="text" readonly class="st-ref-input" id="clientReferralLink" value="<?= $h($lb_client_share_url) ?>">
                <button type="button" class="st-ref-copy" style="width:100%;justify-content:center;" onclick="lbCopyReferralLink('clientReferralLink', this)">
                    <i class="fa-regular fa-copy"></i> Copy Link
                </button>
            </div>
            <div class="st-ref-note">
                You earn <strong><?= rtrim(rtrim(number_format($lb_client_reward_percent, 2, '.', ''), '0'), '.') ?>%</strong> of each completed referred order as LB Coins.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div><!-- end st-col-right -->

</div><!-- end st-settings-grid -->

<!-- ── Upload Avatar Modal ── -->
<form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" id="uploadIconForm">
    <input type="hidden" name="action" value="client_upload_profile_picture">
    <div id="upload-icon-modal" class="modal fade st-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-duotone fa-camera me-2" style="color:#c4b5fd;"></i>Upload Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="st-dropzone" id="stDropzone">
                        <input type="file" name="image_url" id="image_url" accept="image/*" class="st-dropzone-input">
                        <div class="st-dropzone-content" id="stDropContent">
                            <div class="st-dropzone-icon"><i class="fa-duotone fa-cloud-arrow-up"></i></div>
                            <div class="st-dropzone-title">Drag & drop or click to upload</div>
                            <div class="st-dropzone-sub">Or paste an image with <kbd>Ctrl+V</kbd></div>
                            <div class="st-dropzone-types">JPG · PNG · WebP · max 5 MB</div>
                        </div>
                        <div class="st-dropzone-preview" id="stDropPreview" style="display:none;">
                            <img id="stPreviewImg" src="" alt="">
                            <button type="button" class="st-dropzone-clear" id="stDropClear" title="Remove">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <div class="st-dropzone-preview-name" id="stPreviewName"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="st-modal-cancel" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Cancel</button>
                    <button type="submit" class="st-modal-submit" id="stUploadBtn" disabled>
                        <span class="indicator-label"><i class="fa-duotone fa-cloud-arrow-up"></i> Upload</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                        <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Done!</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->start('scripts') ?>
<script>
function togglePassword(id) {
    var inp  = document.getElementById(id);
    var icon = document.querySelector('button[onclick="togglePassword(\'' + id + '\')"] i');
    if (!inp) return;
    if (inp.type === 'password') {
        inp.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        inp.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
}


/* ── Custom Birthday Dropdowns ── */
(function() {
    var hidden = document.getElementById('birthdayLabel');
    function setupDropdown(ddId, btnId, labelId, menuId) {
        var dd = document.getElementById(ddId);
        var btn = document.getElementById(btnId);
        var label = document.getElementById(labelId);
        var menu = document.getElementById(menuId);
        if (!dd || !btn || !menu) return;
        var search = menu.querySelector('.st-dd-search');
        function filterItems() {
            if (!search) return;
            var q = search.value.toLowerCase().trim();
            var visible = 0;
            menu.querySelectorAll('.st-dd-item').forEach(function(item) {
                var match = item.textContent.toLowerCase().indexOf(q) !== -1;
                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            var empty = menu.querySelector('.st-dd-empty');
            if (!empty) {
                empty = document.createElement('div');
                empty.className = 'st-dd-empty';
                empty.textContent = 'No results found';
                menu.appendChild(empty);
            }
            empty.style.display = visible ? 'none' : 'block';
        }
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            ['ddDay','ddMonth','ddYear'].forEach(function(id) {
                if (id !== ddId) { var el=document.getElementById(id); if(el) el.classList.remove('open'); }
            });
            dd.classList.toggle('open');
            if (dd.classList.contains('open') && search) {
                search.value = '';
                filterItems();
                setTimeout(function(){ search.focus(); }, 20);
            }
        });
        if (search) {
            search.addEventListener('click', function(e) { e.stopPropagation(); });
            search.addEventListener('input', filterItems);
            search.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') dd.classList.remove('open');
            });
        }
        menu.addEventListener('click', function(e) {
            var item = e.target.closest('.st-dd-item');
            if (!item) return;
            menu.querySelectorAll('.st-dd-item').forEach(function(i) { i.classList.remove('active'); });
            item.classList.add('active');
            label.textContent = item.textContent;
            dd.setAttribute('data-value', item.getAttribute('data-value'));
            dd.classList.remove('open');
            updateBirthday();
        });
    }
    function updateBirthday() {
        var day   = document.getElementById('ddDay')?.getAttribute('data-value') || '';
        var month = document.getElementById('ddMonth')?.getAttribute('data-value') || '';
        var year  = document.getElementById('ddYear')?.getAttribute('data-value') || '';
        if (!hidden) return;
        if (!day || !month || !year) { hidden.value = ''; return; }
        var d = new Date(+year, +month - 1, +day);
        hidden.value = (d.getFullYear() === +year && d.getMonth() === +month - 1 && d.getDate() === +day)
            ? year + '-' + month + '-' + day : '';
    }
    setupDropdown('ddDay',   'ddDayBtn',   'ddDayLabel',   'ddDayMenu');
    setupDropdown('ddMonth', 'ddMonthBtn', 'ddMonthLabel', 'ddMonthMenu');
    setupDropdown('ddYear',  'ddYearBtn',  'ddYearLabel',  'ddYearMenu');
    document.addEventListener('click', function() {
        ['ddDay','ddMonth','ddYear'].forEach(function(id) {
            var el = document.getElementById(id); if(el) el.classList.remove('open');
        });
    });
})();

/* ── Drag & Drop / Paste Upload ── */
(function() {
    var dropzone    = document.getElementById('stDropzone');
    var fileInput   = document.getElementById('image_url');
    var content     = document.getElementById('stDropContent');
    var preview     = document.getElementById('stDropPreview');
    var previewImg  = document.getElementById('stPreviewImg');
    var previewName = document.getElementById('stPreviewName');
    var clearBtn    = document.getElementById('stDropClear');
    var uploadBtn   = document.getElementById('stUploadBtn');
    if (!dropzone) return;

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        previewImg.src = URL.createObjectURL(file);
        previewName.textContent = file.name;
        content.style.display = 'none';
        preview.style.display = 'flex';
        try { var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; } catch(e) {}
        if (uploadBtn) uploadBtn.disabled = false;
    }
    function clearPreview() {
        previewImg.src = ''; previewName.textContent = '';
        content.style.display = ''; preview.style.display = 'none';
        fileInput.value = '';
        if (uploadBtn) uploadBtn.disabled = true;
    }

    dropzone.addEventListener('click', function(e) {
        if (clearBtn && (e.target === clearBtn || clearBtn.contains(e.target))) return;
        if (preview.style.display !== 'none') return;
        fileInput.click();
    });
    fileInput.addEventListener('change', function() {
        if (fileInput.files[0]) showPreview(fileInput.files[0]);
    });
    dropzone.addEventListener('dragover', function(e) { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', function() { dropzone.classList.remove('dragover'); });
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault(); dropzone.classList.remove('dragover');
        var file = e.dataTransfer.files[0]; if (file) showPreview(file);
    });
    document.addEventListener('paste', function(e) {
        var modal = document.getElementById('upload-icon-modal');
        if (!modal || !modal.classList.contains('show')) return;
        var items = e.clipboardData?.items; if (!items) return;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type.startsWith('image/')) { showPreview(items[i].getAsFile()); break; }
        }
    });
    if (clearBtn) clearBtn.addEventListener('click', function(e) { e.stopPropagation(); clearPreview(); });
    var modalEl = document.getElementById('upload-icon-modal');
    if (modalEl) modalEl.addEventListener('hidden.bs.modal', clearPreview);
})();

function lbCopyReferralLink(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.select(); input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        var old = btn.innerHTML;
        btn.innerHTML = '<i class="fa-regular fa-circle-check"></i> Copied!';
        setTimeout(function() { btn.innerHTML = old; }, 1800);
    });
}
</script>
<?= $this->stop() ?>
