<?php
$booster = $booster ?? [];
$profile = $profile ?? [];
$percent = (int)($data['percent'] ?? 0);
$missing_keys = $data['missing'] ?? [];
$steps  = $data['steps']  ?? [];

$map = [
    'discord'         => 1,
    'profile_picture' => 2,
    'banner'          => 2,
    'bio'             => 3,
    'languages'       => 3,
    'games'           => 3,
    'payout'          => 4,
];

$missing_steps = [];
foreach ($missing_keys as $k) {
    if (isset($map[$k])) $missing_steps[] = $map[$k];
}
$missing_steps = array_values(array_unique($missing_steps));
sort($missing_steps);

// Lock step navigation: only allow going to completed steps or the first missing one
$first_missing_step = count($missing_steps) ? $missing_steps[0] : 5;

$requested_step = isset($_GET['step']) ? max(1, intval($_GET['step'])) : $first_missing_step;

// A step is accessible if it's already done (not in missing_steps) OR it's the first missing
$accessible_steps = [];
for ($i = 1; $i <= 4; $i++) {
    if (!in_array($i, $missing_steps, true) || $i === $first_missing_step) {
        $accessible_steps[] = $i;
    }
}

// Clamp requested step to accessible
$step = in_array($requested_step, $accessible_steps, true) ? $requested_step : $first_missing_step;

if (!empty($data['complete'])) { redirect_url('booster-area/egirl-dashboard'); exit; }

// Media
$default_icon  = 'https://lolboost.gg/public/uploads/icons/default.png';
$icon_url      = trim((string)($booster['icon'] ?? $default_icon));
$icon_done     = ($icon_url !== '' && $icon_url !== $default_icon && strpos($icon_url, '/uploads/icons/default.png') === false);
$cover_raw     = $booster['cover'] ?? null;
$cover_done    = !(is_null($cover_raw) || (is_string($cover_raw) && trim($cover_raw) === ''));
$cover_preview = $cover_done ? (string)$cover_raw : (ASSET_URL . '/core/main/img/banners/leona.jpeg');

// Profile fields
$langs_selected  = is_string($profile['languages'] ?? null) ? array_values(array_filter(explode('|', $profile['languages']))) : (is_string($booster['languages'] ?? null) ? array_values(array_filter(explode('|', $booster['languages']))) : []);
$games_selected  = is_string($profile['games'] ?? null) ? array_values(array_filter(explode('|', $profile['games']))) : (is_string($booster['games'] ?? null) ? array_values(array_filter(explode('|', $booster['games']))) : []);
$bio             = $profile['bio'] ?? $profile['description'] ?? '';
$has_voice       = !empty($profile['voice_chat']) || !empty($booster['voice_chat']);
?>
<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'GG-Girl Profile Setup | LoLBoost.gg'], 'contain' => true]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
.setup-wrap { max-width: 1100px; margin: 0 auto; }
.setup-progress {
    width: 54px; height: 54px; border-radius: 999px;
    display:flex; align-items:center; justify-content:center;
    border: 2px solid rgba(236,72,153,.35);
    background: rgba(236,72,153,.08);
    font-weight: 700; color: #f472b6;
}
.setup-step { padding: 12px 14px; border-radius: 12px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06); }
.setup-step + .setup-step { margin-top: 10px; }
.setup-step .dot { width: 10px; height: 10px; border-radius: 99px; display:inline-block; margin-right: 10px; }
.dot.no { background: #e74c3c; }
.setup-card { border-radius: 16px; overflow: visible; }
.setup-card .card-body { overflow: visible; }

/* Media upload zones */
.upload-zone {
    width: 100%; border-radius: 14px;
    background: rgba(0,0,0,.2);
    border: 2px dashed rgba(255,255,255,.12);
    overflow: hidden; position: relative;
    transition: border-color .2s, background .2s;
    cursor: pointer;
}
.upload-zone:hover, .upload-zone.drag-over {
    border-color: rgba(236,72,153,.5);
    background: rgba(236,72,153,.06);
}
.upload-zone img {
    width:100%; height:100%; object-fit: cover; display:block;
}
.upload-zone-icon {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 8px; pointer-events: none;
    color: rgba(255,255,255,.3); font-size: .8rem;
}
.upload-zone-icon i { font-size: 1.8rem; }
.upload-zone.has-image .upload-zone-icon { display: none; }
.upload-zone-hint {
    font-size: .72rem; color: rgba(255,255,255,.35); margin-top: 6px; text-align:center;
}

/* Step nav */
.step-nav-item-btn {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px; border-radius: 10px;
    text-decoration: none; border: 1px solid transparent;
    transition: background .15s, border-color .15s;
    width: 100%; background: transparent; text-align: left;
    margin-bottom: 4px;
}
.step-nav-item-btn.active {
    background: rgba(236,72,153,.12);
    border-color: rgba(236,72,153,.3);
}
.step-nav-item-btn.locked {
    opacity: .4; cursor: not-allowed; pointer-events: none;
}
.step-nav-item-btn .step-icon {
    width: 18px; text-align: center;
    color: rgba(255,255,255,.4);
}
.step-nav-item-btn.active .step-icon { color: #f472b6; }
.step-nav-item-btn .step-label {
    font-size: .88rem; font-weight: 600;
    color: rgba(255,255,255,.5);
}
.step-nav-item-btn.active .step-label { color: #fff; font-weight: 700; }
.step-nav-item-btn.done-step .step-label { color: rgba(255,255,255,.75); }

/* Progress bar */
.step-progress-bar { display:flex; gap:6px; margin-bottom:1.5rem; }
.step-progress-seg {
    flex:1; height:4px; border-radius:999px;
    background: rgba(255,255,255,.1); transition: background .3s;
}
.step-progress-seg.done { background: #ec4899; }
.step-progress-seg.active { background: rgba(236,72,153,.5); }

/* Game checkboxes */
.game-check-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:10px; }
.game-check-card {
    padding:14px 12px; border-radius:12px; border: 1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.03); cursor:pointer; text-align:center;
    transition: border-color .15s, background .15s;
}
.game-check-card input { display:none; }
.game-check-card.selected { border-color: rgba(236,72,153,.5); background: rgba(236,72,153,.08); }
.game-check-card img { width:36px; height:36px; object-fit:contain; margin-bottom:6px; display:block; margin-inline:auto; }
.game-check-card span { font-size:.8rem; font-weight:700; color:rgba(255,255,255,.7); }

/* Payout method cards */
.payout-method-card {
    padding: 16px; border-radius: 14px;
    border: 2px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    cursor: pointer; transition: border-color .15s, background .15s;
    text-align: center;
}
.payout-method-card:hover { border-color: rgba(236,72,153,.3); }
.payout-method-card.selected {
    border-color: rgba(236,72,153,.55);
    background: rgba(236,72,153,.08);
}
.payout-method-card i { font-size: 2rem; margin-bottom: 8px; display: block; }
.payout-method-card .pm-label { font-weight: 700; font-size: .9rem; }
.payout-method-card .pm-sub { font-size: .75rem; color: rgba(255,255,255,.45); margin-top: 3px; }

/* TomSelect dark */
.ts-control, .ts-wrapper.single .ts-control, .ts-wrapper.multi .ts-control {
    background: rgba(0,0,0,.25) !important; border-color: rgba(255,255,255,.10) !important;
    color: #e9e9ef !important; min-height: 44px;
}
.ts-control .item { background: rgba(236,72,153,.18) !important; border: 1px solid rgba(236,72,153,.35) !important; color:#fff !important; }
.ts-control input { color:#e9e9ef !important; }
.ts-dropdown { background: rgba(18,18,22,.98) !important; border-color: rgba(255,255,255,.10) !important; color:#e9e9ef !important; z-index: 9999 !important; }
.ts-dropdown .option { color:#e9e9ef !important; }
.ts-dropdown .active { background: rgba(236,72,153,.25) !important; }
</style>
<?= $this->end() ?>

<div class="setup-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="page-header-title mb-1">GG-Girl Profile Setup</h1>
            <div class="text-muted">Complete all steps to unlock your GG-Girl Dashboard.</div>
        </div>
        <div class="setup-progress"><?= $percent ?>%</div>
    </div>

    <!-- Progress bar -->
    <div class="step-progress-bar mb-4">
        <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="step-progress-seg <?= $i < $step ? 'done' : ($i == $step ? 'active' : '') ?>"></div>
        <?php endfor; ?>
    </div>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Checklist -->
            <div class="card setup-card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-header-title mb-0">Checklist</h4>
                        <span class="badge" style="background:rgba(236,72,153,.2);color:#f472b6;border:1px solid rgba(236,72,153,.3);"><?= count($missing_keys) ?> missing</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($missing_keys) === 0): ?>
                        <div class="text-muted">All steps completed! 🎉</div>
                    <?php else: ?>
                        <?php foreach ($missing_keys as $k): ?>
                            <?php if (!isset($steps[$k])) continue; ?>
                            <div class="setup-step">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="dot no"></span>
                                        <span class="fw-semibold"><?= esc($steps[$k]['label'] ?? $k) ?></span>
                                    </div>
                                    <span class="text-danger fw-semibold">Missing</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step navigation -->
            <div class="card setup-card mt-3">
                <div class="card-body p-2">
                    <?php
                    $stepLabels = [
                        1 => ['icon' => 'fa-brands fa-discord',       'label' => 'Discord'],
                        2 => ['icon' => 'fa-solid fa-image',           'label' => 'Photos'],
                        3 => ['icon' => 'fa-solid fa-user-pen',        'label' => 'Profile'],
                        4 => ['icon' => 'fa-solid fa-money-bill-wave', 'label' => 'Payout'],
                    ];
                    foreach ($stepLabels as $n => $info):
                        $isActive  = ($n === $step);
                        $isDone    = !in_array($n, $missing_steps, true);
                        $isLocked  = !in_array($n, $accessible_steps, true);
                        $btnClass  = 'step-nav-item-btn';
                        if ($isActive) $btnClass .= ' active';
                        elseif ($isDone) $btnClass .= ' done-step';
                        if ($isLocked) $btnClass .= ' locked';
                        $href = $isLocked ? '#' : '?step=' . $n;
                    ?>
                    <a href="<?= $href ?>" class="<?= $btnClass ?>" <?= $isLocked ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                        <i class="<?= $info['icon'] ?> step-icon"></i>
                        <span class="step-label">Step <?= $n ?>: <?= $info['label'] ?></span>
                        <?php if ($isDone && !$isActive): ?>
                            <i class="fa-solid fa-circle-check ms-auto" style="color:#22c55e;font-size:.8rem;"></i>
                        <?php elseif ($isLocked): ?>
                            <i class="fa-solid fa-lock ms-auto" style="color:rgba(255,255,255,.2);font-size:.75rem;"></i>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="alert alert-soft-primary mt-3 mb-0">
                You can't access other pages until everything is completed.
            </div>
        </div>

        <!-- Main content -->
        <div class="col-lg-8">
            <div class="card setup-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-header-title mb-0">
                        <?php
                        $titles = [
                            1 => 'Step 1: Connect Discord',
                            2 => 'Step 2: Profile Picture & Banner',
                            3 => 'Step 3: Your GG-Girl Profile',
                            4 => 'Step 4: Payout Settings',
                        ];
                        echo esc($titles[$step] ?? 'Setup');
                        ?>
                    </h4>
                    <span class="badge" style="background:rgba(236,72,153,.15);color:#f472b6;border:1px solid rgba(236,72,153,.3);"><?= $step ?>/4</span>
                </div>

                <div class="card-body">

                    <?php if ($step === 1): ?>
                    <!-- ═══ STEP 1: Discord ═══ -->
                    <p class="text-muted mb-4">Connect your Discord account. This is required to communicate with clients and the team via our official server.</p>

                    <?php if (!empty($booster['discord_id']) || !empty($booster['discord'])): ?>
                        <div class="alert alert-soft-success d-flex align-items-center gap-2 mb-4">
                            <i class="fa-brands fa-discord"></i>
                            <span>Discord connected: <strong><?= esc($booster['discord'] ?? $booster['discord_id']) ?></strong></span>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a class="btn btn-primary" href="?step=2">Next →</a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-3 p-4 rounded-3 mb-4" style="background:rgba(88,101,242,.08);border:1px solid rgba(88,101,242,.22);">
                            <i class="fa-brands fa-discord fa-2x" style="color:#818cf8;"></i>
                            <div>
                                <div class="fw-bold" style="color:#a5b4fc;">Why Discord?</div>
                                <div class="text-muted small">All voice sessions and client communication happen in our official Discord server.</div>
                            </div>
                        </div>
                        <a class="btn btn-primary" href="/auth/discord/connect?booster_id=<?= (int)BOOSTER_ID ?>">
                            <i class="fa-brands fa-discord me-2"></i>Connect Discord Account
                        </a>
                    <?php endif; ?>

                    <?php elseif ($step === 2): ?>
                    <!-- ═══ STEP 2: Photos ═══ -->
                    <p class="text-muted mb-4">Upload a profile picture and a banner. Drag & drop, paste (Ctrl+V), or click to upload.</p>

                    <div class="row g-4">
                        <!-- Profile picture -->
                        <div class="col-md-6">
                            <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between">
                                <span>Profile picture <span class="text-danger">*</span></span>
                                <span id="iconBadge" class="badge <?= $icon_done ? 'bg-success' : 'bg-danger' ?>"><?= $icon_done ? 'Done' : 'Missing' ?></span>
                            </div>
                            <div id="iconDropZone" class="upload-zone <?= $icon_done ? 'has-image' : '' ?>" style="height:160px;" title="Click, drag & drop or paste an image">
                                <img id="iconPreview" src="<?= esc($icon_url) ?>" alt="Profile picture" style="object-fit:contain;background:rgba(0,0,0,.2);<?= $icon_done ? '' : 'display:none;' ?>">
                                <div class="upload-zone-icon">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Click or drag & drop</span>
                                    <span style="font-size:.68rem;">or paste with Ctrl+V</span>
                                </div>
                            </div>
                            <input id="iconFile" class="d-none" accept="image/*" type="file">
                            <div class="upload-zone-hint">PNG/JPG. Use a clear face photo.</div>
                            <button id="uploadIconBtn" type="button" class="btn btn-primary mt-3 w-100" <?= $icon_done ? '' : 'disabled' ?> id="uploadIconBtn">
                                <span id="iconBtnTxt"><?= $icon_done ? 'Change profile picture' : 'Upload profile picture' ?></span>
                            </button>
                        </div>

                        <!-- Banner -->
                        <div class="col-md-6">
                            <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between">
                                <span>Banner <span class="text-danger">*</span></span>
                                <span id="coverBadge" class="badge <?= $cover_done ? 'bg-success' : 'bg-danger' ?>"><?= $cover_done ? 'Done' : 'Missing' ?></span>
                            </div>
                            <div id="coverDropZone" class="upload-zone <?= $cover_done ? 'has-image' : '' ?>" style="height:160px;" title="Click, drag & drop or paste an image">
                                <img id="coverPreview" src="<?= esc($cover_preview) ?>" alt="Banner" style="<?= $cover_done ? '' : 'display:none;' ?>">
                                <div class="upload-zone-icon">
                                    <i class="fa-solid fa-panorama"></i>
                                    <span>Click or drag & drop</span>
                                    <span style="font-size:.68rem;">or paste with Ctrl+V</span>
                                </div>
                            </div>
                            <input id="coverFile" class="d-none" accept="image/*" type="file">
                            <div class="upload-zone-hint">Wide image recommended (1920×480).</div>
                            <button id="uploadCoverBtn" type="button" class="btn btn-primary mt-3 w-100" <?= $cover_done ? '' : 'disabled' ?>>
                                <span id="coverBtnTxt"><?= $cover_done ? 'Change banner' : 'Upload banner' ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="?step=1">Back</a>
                        <button id="step2Next" type="button" class="btn btn-primary" <?= ($icon_done && $cover_done) ? '' : 'disabled' ?>>Next →</button>
                    </div>

                    <?php elseif ($step === 3): ?>
                    <!-- ═══ STEP 3: Profile ═══ -->
                    <p class="text-muted mb-4">Tell clients about yourself. This info appears on your public GG-Girl profile.</p>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Bio / Introduction <span class="text-danger">*</span></label>
                        <textarea id="setupBio" class="form-control" rows="4"
                            placeholder="Hey! I'm [name], a passionate gamer who loves playing with new people..."><?= esc($bio) ?></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted small">Min. 30 characters.</span>
                            <span id="bioCounter" class="small fw-semibold" style="color:rgba(255,255,255,.35);">
                                <?= strlen(strip_tags($bio)) ?> / 30
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Languages <span class="text-danger">*</span></label>
                        <select id="setupLanguages" name="languages[]" multiple class="form-select">
                            <?= util_load_languages_select($langs_selected) ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold d-block mb-2">Games you play <span class="text-danger">*</span></label>
                        <div class="game-check-grid">
                            <?php
                            // Same source as the dashboard profile: every game that has a
                            // boosting service enabled, so new games appear automatically.
                            $gameOptions = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
                            foreach ($gameOptions as $gk => $gi):
                                $sel = in_array($gk, $games_selected, true);
                            ?>
                            <label class="game-check-card <?= $sel ? 'selected' : '' ?>" id="gameCard_<?= htmlspecialchars($gk, ENT_QUOTES) ?>">
                                <input type="checkbox" value="<?= htmlspecialchars($gk, ENT_QUOTES) ?>" class="js-game-check" <?= $sel ? 'checked' : '' ?>>
                                <?php if (!empty($gi['icon'])): ?>
                                    <img src="<?= htmlspecialchars($gi['icon'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($gi['label'], ENT_QUOTES) ?>" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($gi['label']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);">
                        <div>
                            <div class="fw-semibold"><i class="fa-solid fa-microphone me-2" style="color:#4ade80;"></i>Voice Chat available</div>
                            <div class="text-muted small">Clients can filter by voice chat availability.</div>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="setupVoiceChat" <?= $has_voice ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="?step=2">Back</a>
                        <button id="step3Next" type="button" class="btn btn-primary">Next →</button>
                    </div>

                    <?php else: ?>
                    <!-- ═══ STEP 4: Payout ═══ -->
                    <p class="text-muted mb-4">Add at least one payout method so we can pay you after completed sessions.</p>

                    <!-- Method selector cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="payout-method-card selected" id="pmCardCrypto" onclick="selectPayoutMethod('crypto')">
                                <i class="fa-brands fa-bitcoin" style="color:#f7931a;"></i>
                                <div class="pm-label">Crypto</div>
                                <div class="pm-sub">USDC on Solana</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payout-method-card" id="pmCardBank" onclick="selectPayoutMethod('bank_transfer')">
                                <i class="fa-solid fa-building-columns" style="color:#f472b6;"></i>
                                <div class="pm-label">Bank Transfer</div>
                                <div class="pm-sub">IBAN / SEPA</div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="payoutMethodVal" value="crypto">

                    <div class="d-flex align-items-center justify-content-between mb-4 p-3 rounded-3" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);">
                        <span class="fw-semibold small">Set as default payout method</span>
                        <div class="form-check form-switch m-0">
                            <input id="payoutDefault" class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>

                    <!-- Crypto fields -->
                    <div id="payoutCrypto">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name</label>
                                <input id="cryptoName" class="form-control" placeholder="Your name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Wallet / Exchange</label>
                                <input id="cryptoWallet" class="form-control" placeholder="e.g. Binance">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Country</label>
                                <input id="cryptoCountry" class="form-control" placeholder="e.g. Germany">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">USDC (Solana) Address</label>
                                <input id="cryptoAddress" class="form-control" placeholder="Solana address">
                            </div>
                        </div>
                        <div class="text-muted small mt-2">We use USDC on Solana for crypto payouts.</div>
                    </div>

                    <!-- Bank fields -->
                    <div id="payoutBank" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Beneficiary</label>
                                <input id="bankBeneficiary" class="form-control" placeholder="Full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">IBAN</label>
                                <input id="bankIban" class="form-control" placeholder="DE00 0000 0000 0000 0000 00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">BIC (optional)</label>
                                <input id="bankBic" class="form-control" placeholder="BANKDEFFXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank name (optional)</label>
                                <input id="bankName" class="form-control" placeholder="Your bank">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Country</label>
                                <input id="bankCountry" class="form-control" placeholder="e.g. Germany">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Currency</label>
                                <input id="bankCurrency" class="form-control" placeholder="EUR" value="EUR">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <input id="bankAddress" class="form-control" placeholder="Your address">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="?step=3">Back</a>
                        <button id="savePayoutBtn" type="button" class="btn btn-primary">
                            <i class="fa-solid fa-check me-1"></i>Save & Finish
                        </button>
                    </div>

                    <div class="mt-3">
                        <a class="btn btn-link p-0 text-muted small" href="<?= BSTR_URL ?>/egirl-payout">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open full payout settings
                        </a>
                    </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
const AJAX = '<?= AJAX_URL ?>';

// ── TomSelect ──
const tsLanguages = (() => {
    const el = document.getElementById('setupLanguages');
    if (!el) return null;
    return new TomSelect(el, {
        plugins: ['remove_button'],
        persist: false,
        create: false,
        labelField: 'text',
        valueField: 'value',
        dataAttr: 'data-flag',
        render: {
            option: function(data, escape) {
                const flag = data['data-flag'] || el.querySelector(`option[value="${data.value}"]`)?.dataset?.flag
                    ? `<img src="${escape(data['data-flag'] || el.querySelector('option[value="'+data.value+'"]')?.dataset?.flag)}" style="width:18px;height:12px;object-fit:cover;border-radius:2px;margin-right:7px;vertical-align:middle;flex-shrink:0;" onerror="this.style.display='none'">`
                    : '';
                return `<div style="display:flex;align-items:center;">${flag}<span>${escape(data.text)}</span></div>`;
            },
            item: function(data, escape) {
                const flagSrc = data['data-flag'] || document.querySelector(`#setupLanguages option[value="${data.value}"]`)?.dataset?.flag || '';
                const flag = flagSrc
                    ? `<img src="${escape(flagSrc)}" style="width:16px;height:11px;object-fit:cover;border-radius:2px;margin-right:5px;vertical-align:middle;flex-shrink:0;" onerror="this.style.display='none'">`
                    : '';
                return `<div style="display:flex;align-items:center;">${flag}<span>${escape(data.text)}</span></div>`;
            }
        }
    });
})();

// ── Post helper ──
async function postAjax(fd) {
    const res = await fetch(AJAX, { method:'POST', body:fd });
    const txt = await res.text();
    try { return JSON.parse(txt); } catch(e) { throw new Error(txt); }
}
function mkfd(action) {
    const f = new FormData();
    f.append('action', action);
    return f;
}

// ══════════════════════════════════════════
// STEP 2 — Drag & Drop / Paste / Click upload
// ══════════════════════════════════════════

let iconFile = null;
let coverFile = null;

function setupUploadZone({ zoneId, fileInputId, previewId, badgeId, btnId, btnTxtId, onFileReady }) {
    const zone      = document.getElementById(zoneId);
    const fileInput = document.getElementById(fileInputId);
    const preview   = document.getElementById(previewId);
    const badge     = document.getElementById(badgeId);
    const btn       = document.getElementById(btnId);
    const btnTxt    = document.getElementById(btnTxtId);
    if (!zone) return;

    function loadFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = '';
            zone.classList.add('has-image');
            badge.className = 'badge bg-warning';
            badge.textContent = 'Ready to upload';
            if (btn) btn.disabled = false;
            if (btnTxt) btnTxt.textContent = 'Upload image';
            onFileReady(file);
        };
        reader.readAsDataURL(file);
    }

    // Click to open file picker
    zone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) loadFile(fileInput.files[0]);
    });

    // Drag & Drop
    zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) loadFile(file);
    });

    // Paste (Ctrl+V) — global listener, activates for whichever zone was last focused
    zone.addEventListener('mouseenter', () => { zone._focused = true; });
    zone.addEventListener('mouseleave', () => { zone._focused = false; });
}

setupUploadZone({
    zoneId: 'iconDropZone', fileInputId: 'iconFile',
    previewId: 'iconPreview', badgeId: 'iconBadge',
    btnId: 'uploadIconBtn', btnTxtId: 'iconBtnTxt',
    onFileReady: (f) => { iconFile = f; }
});

setupUploadZone({
    zoneId: 'coverDropZone', fileInputId: 'coverFile',
    previewId: 'coverPreview', badgeId: 'coverBadge',
    btnId: 'uploadCoverBtn', btnTxtId: 'coverBtnTxt',
    onFileReady: (f) => { coverFile = f; }
});

// Global paste handler
document.addEventListener('paste', (e) => {
    const items = e.clipboardData?.items || [];
    for (const item of items) {
        if (item.type.startsWith('image/')) {
            const file = item.getAsFile();
            if (!file) continue;
            // Paste goes to whichever zone is hovered, fallback to icon
            const coverZone = document.getElementById('coverDropZone');
            const iconZone  = document.getElementById('iconDropZone');
            if (coverZone?._focused) {
                coverFile = file;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const img = document.getElementById('coverPreview');
                    img.src = ev.target.result; img.style.display = '';
                    coverZone.classList.add('has-image');
                    const badge = document.getElementById('coverBadge');
                    badge.className = 'badge bg-warning'; badge.textContent = 'Ready to upload';
                    document.getElementById('uploadCoverBtn').disabled = false;
                    document.getElementById('coverBtnTxt').textContent = 'Upload image';
                };
                reader.readAsDataURL(file);
            } else {
                iconFile = file;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const img = document.getElementById('iconPreview');
                    img.src = ev.target.result; img.style.display = '';
                    iconZone.classList.add('has-image');
                    const badge = document.getElementById('iconBadge');
                    badge.className = 'badge bg-warning'; badge.textContent = 'Ready to upload';
                    document.getElementById('uploadIconBtn').disabled = false;
                    document.getElementById('iconBtnTxt').textContent = 'Upload image';
                };
                reader.readAsDataURL(file);
            }
            break;
        }
    }
});

// Upload buttons
async function doUpload(action, file, btnId, badgeId, btnTxtId) {
    if (!file) { create_toast('danger', 'No image', 'Please select or paste an image first.'); return false; }
    const btn = document.getElementById(btnId);
    const badge = document.getElementById(badgeId);
    const btnTxt = document.getElementById(btnTxtId);
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading…'; }
    const fd = new FormData();
    fd.append('action', action);
    fd.append('image_url', file);
    try {
        await fetch(AJAX, { method:'POST', body:fd });
        create_toast('success', 'Uploaded', 'Your image was uploaded.');
        if (badge) { badge.className = 'badge bg-success'; badge.textContent = 'Done'; }
        if (btnTxt) btnTxt.textContent = 'Change image';
        if (btn) { btn.disabled = false; btn.innerHTML = btnTxt?.outerHTML || 'Change image'; }
        // Check if both done to enable Next
        checkStep2Done();
        return true;
    } catch(e) {
        create_toast('danger', 'Upload failed', 'Could not upload the image. Please try again.');
        if (btn) { btn.disabled = false; btn.innerHTML = 'Upload failed – try again'; }
        return false;
    }
}

document.getElementById('uploadIconBtn')?.addEventListener('click', async () => {
    const ok = await doUpload('booster_upload_profile_picture', iconFile, 'uploadIconBtn', 'iconBadge', 'iconBtnTxt');
    if (ok) { iconFile = null; checkStep2Done(); }
});
document.getElementById('uploadCoverBtn')?.addEventListener('click', async () => {
    const ok = await doUpload('booster_upload_cover', coverFile, 'uploadCoverBtn', 'coverBadge', 'coverBtnTxt');
    if (ok) { coverFile = null; checkStep2Done(); }
});

function checkStep2Done() {
    const iconDone  = document.getElementById('iconBadge')?.textContent?.trim() === 'Done';
    const coverDone = document.getElementById('coverBadge')?.textContent?.trim() === 'Done';
    const nextBtn   = document.getElementById('step2Next');
    if (nextBtn) nextBtn.disabled = !(iconDone && coverDone);
}

document.getElementById('step2Next')?.addEventListener('click', () => {
    window.location.href = '?step=3';
});

// ══════════════════════════════════════════
// STEP 3 — Game card toggle + save
// ══════════════════════════════════════════
document.querySelectorAll('.js-game-check').forEach(cb => {
    cb.addEventListener('change', () => {
        const card = document.getElementById('gameCard_' + cb.value);
        if (card) card.classList.toggle('selected', cb.checked);
    });
});

// ── Bio character counter ──
(function() {
    const bio     = document.getElementById('setupBio');
    const counter = document.getElementById('bioCounter');
    if (!bio || !counter) return;
    function update() {
        const len = bio.value.trim().length;
        counter.textContent = len + ' / 30';
        if (len >= 30) {
            counter.style.color = '#22c55e';
        } else {
            counter.style.color = len > 20 ? '#f59e0b' : 'rgba(255,255,255,.35)';
        }
    }
    bio.addEventListener('input', update);
    update();
})();

document.getElementById('step3Next')?.addEventListener('click', async () => {
    const bio = document.getElementById('setupBio')?.value?.trim() || '';
    if (bio.length < 30) { create_toast('danger', 'Bio too short', 'Please write at least 30 characters for your bio.'); return; }

    // TomSelect stores values in its own instance — read from tsLanguages
    const langs = tsLanguages ? tsLanguages.getValue() : [];
    if (!langs.length) { create_toast('danger', 'Languages missing', 'Please select at least one language.'); return; }

    const games = Array.from(document.querySelectorAll('.js-game-check:checked')).map(c=>c.value);
    if (!games.length) { create_toast('danger', 'Games missing', 'Please select at least one game.'); return; }

    const voiceChat = document.getElementById('setupVoiceChat')?.checked ? 1 : 0;

    try {
        let f, res;

        f = mkfd('egirl_profile_quick_update'); f.append('field','bio'); f.append('value', bio);
        res = await postAjax(f);
        if (!res.success) throw new Error(res.sendToast?.message || 'Bio save failed');

        f = mkfd('egirl_profile_quick_update'); f.append('field','languages');
        langs.forEach(v => f.append('languages[]', v));
        res = await postAjax(f);
        if (!res.success) throw new Error(res.sendToast?.message || 'Languages save failed');

        f = mkfd('egirl_profile_quick_update'); f.append('field','games');
        games.forEach(v => f.append('games[]', v));
        res = await postAjax(f);
        if (!res.success) throw new Error(res.sendToast?.message || 'Games save failed');

        f = mkfd('egirl_profile_quick_update'); f.append('field','voice_chat');
        f.append('value', document.getElementById('setupVoiceChat')?.checked ? 1 : 0);
        await postAjax(f); // non-critical

        create_toast('success', 'Saved', 'Your profile details were saved.');
        setTimeout(function(){ window.location.href = '?step=4'; }, 700);
    } catch(e) { console.error(e); create_toast('danger', 'Error', e.message || 'Something went wrong. Please try again.'); }
});

// ══════════════════════════════════════════
// STEP 4 — Payout method cards
// ══════════════════════════════════════════
function selectPayoutMethod(method) {
    document.getElementById('payoutMethodVal').value = method;
    document.getElementById('pmCardCrypto').classList.toggle('selected', method === 'crypto');
    document.getElementById('pmCardBank').classList.toggle('selected', method === 'bank_transfer');
    document.getElementById('payoutCrypto').style.display = (method === 'crypto') ? '' : 'none';
    document.getElementById('payoutBank').style.display   = (method === 'bank_transfer') ? '' : 'none';
}

document.getElementById('savePayoutBtn')?.addEventListener('click', async () => {
    const method      = document.getElementById('payoutMethodVal')?.value || 'crypto';
    const makeDefault = document.getElementById('payoutDefault')?.checked ? 1 : 0;
    const f = mkfd('booster_save_payout_method');
    f.append('method', method);
    f.append('make_default', String(makeDefault));
    if (method === 'crypto') {
        f.append('name',    document.getElementById('cryptoName')?.value    || '');
        f.append('wallet',  document.getElementById('cryptoWallet')?.value  || '');
        f.append('country', document.getElementById('cryptoCountry')?.value || '');
        f.append('address', document.getElementById('cryptoAddress')?.value || '');
    } else {
        f.append('beneficiary', document.getElementById('bankBeneficiary')?.value || '');
        f.append('iban',        document.getElementById('bankIban')?.value        || '');
        f.append('bic',         document.getElementById('bankBic')?.value         || '');
        f.append('bank_name',   document.getElementById('bankName')?.value        || '');
        f.append('country',     document.getElementById('bankCountry')?.value     || '');
        f.append('currency',    document.getElementById('bankCurrency')?.value    || 'EUR');
        f.append('address',     document.getElementById('bankAddress')?.value     || '');
    }
    try {
        const res = await postAjax(f);
        if (!res.success) {
            create_toast('danger', 'Error', res.sendToast?.message || 'Payout method could not be saved.');
            return;
        }
        create_toast('success', res.sendToast?.title || 'Saved', res.sendToast?.message || 'Payout method saved.');
        setTimeout(function(){ window.location.reload(); }, 700);
    } catch(e) {
        console.error(e);
        create_toast('danger', 'Error', e.message || 'Something went wrong. Please try again.');
    }
});
</script>
<?= $this->end() ?>
