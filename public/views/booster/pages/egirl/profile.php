<?php
// ── Normalize stored data ──────────────────────────────────────
$egGames = array_values(array_filter(
    explode('|', (string)($profile['games'] ?? '')), fn($v) => $v !== ''
));

// lol_rank / val_rank / tft_rank stored as plain string e.g. "Challenger" or "Diamond II"
// We keep them as plain strings for egirls (simpler than booster numeric tiers)
$egLolRank = htmlspecialchars($profile['lol_rank'] ?? '');
$egValRank = htmlspecialchars($profile['val_rank'] ?? '');
$egTftRank = htmlspecialchars($profile['tft_rank'] ?? '');

$egTimezone   = trim((string)($profile['timezone']   ?? ''));
$egLanguages  = trim((string)($profile['languages']  ?? ''));
$egVoiceChat  = !empty(BOOSTER_DATA['voice_chat'] ?? $profile['voice_chat'] ?? 0);
$egShowProfile = !empty(BOOSTER_DATA['show_profile'] ?? $profile['show_profile'] ?? 0);

$egDecodeText = static function ($text) {
    $text = (string)($text ?? '');
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) break;
        $text = $decoded;
    }
    return $text;
};
$egBioDecoded = $egDecodeText($profile['bio'] ?? '');

// Language map: code → display name + flag image filename
$LANG_MAP = [
    'en' => ['name'=>'English',    'img'=>'en.png'],
    'de' => ['name'=>'German',     'img'=>'de.png'],
    'fr' => ['name'=>'French',     'img'=>'fr.png'],
    'es' => ['name'=>'Spanish',    'img'=>'es.png'],
    'tr' => ['name'=>'Turkish',    'img'=>'tr.png'],
    'pt' => ['name'=>'Portuguese', 'img'=>'pt.png'],
    'it' => ['name'=>'Italian',    'img'=>'it.png'],
    'pl' => ['name'=>'Polish',     'img'=>'pl.png'],
    'ru' => ['name'=>'Russian',    'img'=>'ru.webp'],
    'nl' => ['name'=>'Dutch',      'img'=>'nl.png'],
    'sv' => ['name'=>'Swedish',    'img'=>'sv.png'],
    'da' => ['name'=>'Danish',     'img'=>'da.webp'],
    'no' => ['name'=>'Norwegian',  'img'=>'no.webp'],
    'fi' => ['name'=>'Finnish',    'img'=>'fi.webp'],
    'cs' => ['name'=>'Czech',      'img'=>'cz.webp'],
    'ro' => ['name'=>'Romanian',   'img'=>'ro.png'],
    'hu' => ['name'=>'Hungarian',  'img'=>'hu.webp'],
    'uk' => ['name'=>'Ukrainian',  'img'=>'uk.png'],
    'ar' => ['name'=>'Arabic',     'img'=>'ar.png'],
    'zh' => ['name'=>'Chinese',    'img'=>'chinese.png'],
    'ja' => ['name'=>'Japanese',   'img'=>'ja.webp'],
    'ko' => ['name'=>'Korean',     'img'=>'ko.png'],  // no separate file, use kr if exists
    'el' => ['name'=>'Greek',      'img'=>'el.png'],
    'hr' => ['name'=>'Croatian',   'img'=>'hr.png'],
    'bg' => ['name'=>'Bulgarian',  'img'=>'bg.webp'],
    'sr' => ['name'=>'Serbian',    'img'=>'hr.png'],
    'sk' => ['name'=>'Slovak',     'img'=>'cz.webp'],
    'vn' => ['name'=>'Vietnamese', 'img'=>'vn.webp'],
    'ph' => ['name'=>'Filipino',   'img'=>'ph.webp'],
    'th' => ['name'=>'Thai',       'img'=>'th.webp'],
];
$LANG_IMG_BASE = ASSET_URL . '/core/main/img/languages/';

// Selected languages array
$selectedLangs = array_filter(array_map('trim', explode('|', $egLanguages)));

// Rank string to parse: "Challenger" or "Diamond II"
function parseRankString(string $s): array {
    $s = trim($s);
    $parts = preg_split('/\s+/', $s, 2);
    return ['tier' => $parts[0] ?? '', 'div' => $parts[1] ?? ''];
}

// Every game that has a boosting service enabled, with its own rank ladder.
// New games added in the admin "Games" area show up here automatically.
$egGameOptions = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
$egSavedRanks  = function_exists('lb_egirl_game_ranks') ? lb_egirl_game_ranks($profile) : [];

// tier name => division labels, per game — consumed by the JS below.
$egRankConfigJs = [];
foreach ($egGameOptions as $gk => $gOpt) {
    $tiers = [];
    foreach (($gOpt['tiers'] ?? []) as $tierName => $divCount) {
        $tiers[$tierName] = function_exists('lb_egirl_division_labels')
            ? lb_egirl_division_labels((int)$divCount)
            : [];
    }
    $egRankConfigJs[$gk] = $tiers;
}

// ── Availability: build by-day lookup from DB rows ──────────────
$DAYS_FULL = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$availByDay = []; // day_of_week (0-6) => ['from'=>'18:00','to'=>'23:00']
foreach ($availability as $slot) {
    $d = (int)$slot['day_of_week'];
    $availByDay[$d] = [
        'from' => substr($slot['time_from'], 0, 5),
        'to'   => substr($slot['time_to'],   0, 5),
    ];
}
?>
<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
/* Tom Select is used for languages */

/* ── Game picker chips ── */
.game-picker { display:flex; gap:10px; flex-wrap:wrap; }
.game-chip {
    display:inline-flex; align-items:center; gap:8px;
    padding:8px 16px; border-radius:10px; cursor:pointer;
    border:1px solid rgba(255,255,255,.1);
    background:rgba(255,255,255,.04);
    font-size:.88rem; font-weight:700; color:rgba(255,255,255,.5);
    transition:all .18s; user-select:none;
}
.game-chip img { width:22px; height:22px; object-fit:contain; }
.game-chip:hover { border-color:rgba(168,85,247,.5); color:rgba(255,255,255,.85); background:rgba(168,85,247,.08); }
.game-chip.selected { border-color:rgba(168,85,247,.7); background:rgba(168,85,247,.15); color:#fff; }
.game-chip[data-game="lol"].selected  { border-color:rgba(200,170,60,.7);  background:rgba(200,170,60,.12);  color:#c8aa3c; }
.game-chip[data-game="val"].selected  { border-color:rgba(255,70,85,.7);   background:rgba(255,70,85,.1);    color:#ff6b77; }
.game-chip[data-game="tft"].selected  { border-color:rgba(100,180,255,.7); background:rgba(100,180,255,.1);  color:#64b4ff; }

/* ── Rank row ── */
.rank-row { display:flex; align-items:center; gap:10px; }
.rank-row .game-label { display:flex; align-items:center; gap:7px; min-width:130px; font-weight:600; font-size:.88rem; }
.rank-row .game-label img { width:20px; height:20px; object-fit:contain; }
.rank-row select { flex:1; }
.rank-section { display:none; }
.rank-section.visible { display:block; }


/* ── Languages / Country / Timezone dropdowns ──
   All three run through Tom Select and share one look. In a single select the
   chosen value must read like plain text — chips are only for the multi select. */
.eg-select-wrap .ts-wrapper .ts-control,
.eg-select-wrap .ts-wrapper.single .ts-control {
    background: #212429 !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 10px !important;
    min-height: 44px;
    padding: 9px 34px 9px 14px !important;
    color: #fff !important;
    box-shadow: none !important;
}
.eg-select-wrap .ts-wrapper.multi .ts-control {
    padding: 6px 34px 6px 8px !important;
    gap: 6px;
}
.eg-select-wrap .ts-wrapper.focus .ts-control,
.eg-select-wrap .ts-wrapper .ts-control.focus {
    border-color: rgba(168,85,247,.55) !important;
    box-shadow: 0 0 0 .2rem rgba(168,85,247,.15) !important;
}
.eg-select-wrap .ts-wrapper.single .ts-control > .item {
    background: none !important;
    border: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    color: #fff !important;
    font-weight: 600;
    line-height: 1.3;
}
/* Empty value ("N/A (not set)") should look like a placeholder, not like a value. */
.eg-select-wrap .ts-wrapper.single .ts-control > .item[data-value=""] {
    color: rgba(255,255,255,.42) !important;
    font-weight: 500;
}
.eg-select-wrap .ts-wrapper .ts-control input {
    color: #fff !important;
}
/* Multi select (Languages): the picked values stay chips. */
.eg-select-wrap .ts-wrapper.multi .ts-control > .item {
    background: rgba(168,85,247,.16) !important;
    border: 1px solid rgba(168,85,247,.30) !important;
    border-radius: 8px !important;
    padding: 3px 8px !important;
    margin: 0 !important;
    color: rgba(255,255,255,.92) !important;
    font-weight: 600;
    font-size: .84rem;
}
.eg-select-wrap .ts-wrapper.multi .ts-control > .item .remove {
    border-left-color: rgba(168,85,247,.30) !important;
    color: rgba(255,255,255,.7) !important;
}
/* Caret */
.eg-select-wrap .ts-wrapper.single .ts-control::after {
    border-color: rgba(255,255,255,.45) transparent transparent transparent !important;
}
.eg-select-wrap .ts-dropdown {
    background: #1c1f24 !important;
    border: 1px solid rgba(255,255,255,.09) !important;
    border-radius: 10px;
    box-shadow: 0 18px 45px rgba(0,0,0,.5);
    margin-top: 4px;
}
.eg-select-wrap .ts-dropdown .option {
    padding: 9px 14px;
    color: rgba(255,255,255,.82) !important;
    border-radius: 8px;
}
.eg-select-wrap .ts-dropdown .option.active,
.eg-select-wrap .ts-dropdown .option:hover {
    background: rgba(168,85,247,.18) !important;
    color: #fff !important;
}
/* The Bootstrap-5 tom-select theme gives group headers ("Popular", "All
   timezones") a white background — that was the white bar in the dropdown. */
.eg-select-wrap .ts-dropdown .optgroup-header {
    background: transparent !important;
    color: rgba(255,255,255,.45) !important;
    padding: 10px 14px 4px;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-size: .7rem;
    font-weight: 800;
}
.eg-lang-row { display:flex; align-items:center; gap:8px; }
.eg-lang-flag { width:20px; height:14px; object-fit:cover; border-radius:2px; flex:0 0 auto; }
.eg-select-wrap .ts-dropdown .dropdown-input {
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.1) !important;
    border-radius: 8px;
    color: #fff !important;
}
.eg-select-wrap .ts-dropdown .dropdown-input:focus {
    border-color: rgba(168,85,247,.55) !important;
    box-shadow: none !important;
    outline: none !important;
}

/* Rank selects are native <select>s — same grey tone, and a dark option popup. */
#rankCardBody .form-select {
    background-color: #212429;
    border-color: rgba(255,255,255,.08);
    color: #fff;
    border-radius: 10px;
    min-height: 44px;
    color-scheme: dark;
}
#rankCardBody .form-select:focus {
    border-color: rgba(168,85,247,.55);
    box-shadow: 0 0 0 .2rem rgba(168,85,247,.15);
}
#rankCardBody .form-select option { background-color: #1c1f24; color: #e2e8f0; }

/* ── 7-day availability grid ── */
.avail-grid { display:flex; flex-direction:column; gap:8px; }
.avail-day-row {
    display:grid; grid-template-columns:120px 1fr auto;
    align-items:center; gap:12px;
    padding:10px 14px; border-radius:10px;
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07);
    transition:background .15s, border-color .15s;
}
.avail-day-row.unavailable { opacity:.45; }
.avail-day-row .day-label { font-weight:700; font-size:.9rem; }
.avail-day-row .today-badge {
    display:inline-block; font-size:.6rem; padding:1px 6px; border-radius:999px;
    background:rgba(168,85,247,.2); color:#c084fc; margin-left:5px;
    font-weight:800; text-transform:uppercase; letter-spacing:.04em;
}
.avail-day-row .time-inputs { display:flex; align-items:center; gap:8px; }
.avail-day-row .time-inputs span { color:rgba(255,255,255,.3); font-size:.8rem; }
.avail-day-row input[type="time"] {
    background:#212429 !important;
    color:#fff !important;
    border:1px solid rgba(255,255,255,.08) !important;
    color-scheme: dark;
}
.avail-day-row input[type="time"]::-webkit-calendar-picker-indicator {
    filter: invert(1) brightness(2);
    opacity: 1;
    cursor: pointer;
}
.avail-day-row input[type="time"]::-webkit-datetime-edit,
.avail-day-row input[type="time"]::-webkit-datetime-edit-hour-field,
.avail-day-row input[type="time"]::-webkit-datetime-edit-minute-field,
.avail-day-row input[type="time"]::-webkit-datetime-edit-text {
    color:#fff;
}
.avail-day-row input[type="time"]:disabled {
    background:rgba(255,255,255,.03) !important;
    color:rgba(255,255,255,.35) !important;
}
.avail-day-toggle {
    display:flex; align-items:center; gap:6px;
    font-size:.75rem; color:rgba(255,255,255,.4);
}
</style>
<?= $this->end() ?>

<?php $egSharedActiveTab = 'profile'; ?>
<?= $this->insert('booster/pages/egirl/_shared') ?>

<div class="content container-fluid">
    <div class="row g-4">

        <!-- ── LEFT: Forms ── -->
        <div class="col-lg-8">

            <!-- ── Card: Profile Details ── -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-header-title">Profile Details</h5></div>
                <div class="card-body">

                    <!-- Bio -->
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Bio</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="profileBio" rows="4"
                                placeholder="Tell clients about yourself..."><?= htmlspecialchars($egBioDecoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <!-- Languages — identical to booster (util_load_languages_select + Tom Select) -->
                    <div class="row mb-4">
                        <label for="profileLanguages" class="col-sm-3 col-form-label form-label">Languages</label>
                        <div class="col-sm-9 tom-select-custom eg-select-wrap">
                            <select class="js-select-multi form-select" id="profileLanguages" name="languages[]" multiple autocomplete="off"
                                data-hs-tom-select-options='{"placeholder": "Search languages..."}'>
                                <?= util_load_languages_select($egLanguages) ?>
                            </select>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="row mb-4">
                        <label for="profileCountry" class="col-sm-3 col-form-label form-label">Country</label>
                        <div class="col-sm-9 eg-select-wrap">
                            <select class="form-select js-select-single" id="profileCountry" autocomplete="off"
                                data-hs-tom-select-options='{"placeholder": "Search country..."}'>
                                <?php
                                if (function_exists('util_load_countries_select')) {
                                    echo util_load_countries_select($profile['country'] ?? '');
                                } else {
                                    $curCountry = htmlspecialchars($profile['country'] ?? '');
                                    echo '<option value="' . $curCountry . '" selected>' . ($curCountry ?: 'N/A (not set)') . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Timezone -->
                    <div class="row mb-4">
                        <label for="profileTimezone" class="col-sm-3 col-form-label form-label">Timezone</label>
                        <div class="col-sm-9 eg-select-wrap">
                            <select class="form-select js-select-single" id="profileTimezone" autocomplete="off"
                                data-hs-tom-select-options='{"placeholder": "Search timezone..."}'>
                                <?php
                                if (function_exists('util_load_timezones_select')) {
                                    echo util_load_timezones_select($egTimezone);
                                } else {
                                    echo '<option value="" ' . ($egTimezone === '' ? 'selected' : '') . '>N/A</option>';
                                    foreach (\DateTimeZone::listIdentifiers() as $tz) {
                                        $sel = ($tz === $egTimezone) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($tz) . '" ' . $sel . '>' . htmlspecialchars($tz) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Games — chip picker ── -->
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Games you play</label>
                        <div class="col-sm-9">
                            <div class="game-picker" id="gamePicker">
                                <?php foreach ($egGameOptions as $gk => $gOpt): ?>
                                    <div class="game-chip <?= in_array($gk, $egGames, true) ? 'selected' : '' ?>"
                                         data-game="<?= htmlspecialchars($gk, ENT_QUOTES) ?>"
                                         onclick="toggleGame(this)">
                                        <?php if (!empty($gOpt['icon'])): ?>
                                            <img src="<?= htmlspecialchars($gOpt['icon'], ENT_QUOTES) ?>" alt="" onerror="this.style.display='none'">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($gOpt['label']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted d-block mt-2">Select every game you play — set your rank for each one below.</small>
                            <input type="hidden" id="profileGames" value="<?= htmlspecialchars(implode('|', $egGames)) ?>">
                        </div>
                    </div>

                    <!-- Voice Chat toggle -->
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Voice Chat</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch">
                                <input type="hidden" id="voiceChatHidden" value="<?= $egVoiceChat ? '1' : '0' ?>">
                                <input class="form-check-input" type="checkbox" role="switch" id="voiceChatToggle"
                                    <?= $egVoiceChat ? 'checked' : '' ?>
                                    onchange="document.getElementById('voiceChatHidden').value=this.checked?'1':'0';
                                              document.getElementById('previewVoice').style.display=this.checked?'':'none';">
                                <label class="form-check-label" for="voiceChatToggle">
                                    Offer Voice Chat in sessions
                                    <small class="text-muted d-block">Clients can opt-in to voice chat when booking.</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Visibility toggle -->
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Visibility</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch">
                                <input type="hidden" id="showProfileHidden" value="<?= $egShowProfile ? '1' : '0' ?>">
                                <input class="form-check-input" type="checkbox" role="switch" id="showProfileToggle"
                                    <?= $egShowProfile ? 'checked' : '' ?>
                                    onchange="document.getElementById('showProfileHidden').value=this.checked?'1':'0'">
                                <label class="form-check-label" for="showProfileToggle">Show profile on GG-Girls page</label>
                            </div>
                        </div>
                    </div>

                <div class="mt-3">
    <button class="btn btn-primary w-100" id="btnSaveProfile">
        <span class="indicator-label"><i class="fa-solid fa-floppy-disk me-1"></i>Save Profile</span>
        <span class="indicator-progress" style="display:none"><span class="spinner-border spinner-border-sm align-middle me-1"></span>Saving...</span>
        <span class="indicator-success" style="display:none"><i class="fa-regular fa-circle-check me-1"></i>Saved!</span>
    </button>
</div>
</div>
            </div>

            <!-- ── Card: Rank Info ── -->
            <div class="card mb-4" id="rankCard">
                <div class="card-header"><h5 class="card-header-title">Rank Info <span class="text-muted fw-normal small">(optional)</span></h5></div>
                <div class="card-body" id="rankCardBody">

                    <?php foreach ($egGameOptions as $gk => $gOpt): ?>
                        <?php
                        $parsed = parseRankString((string)($egSavedRanks[$gk] ?? ''));
                        $tiers = $gOpt['tiers'] ?? [];
                        // A tier with 0 divisions (Master, Radiant, Top500, …) hides the division select.
                        $divCount = (int)($tiers[$parsed['tier']] ?? 0);
                        $divLabels = function_exists('lb_egirl_division_labels') ? lb_egirl_division_labels($divCount) : [];
                        ?>
                        <div class="rank-section mb-4 <?= in_array($gk, $egGames, true) ? 'visible' : '' ?>"
                             data-rank-game="<?= htmlspecialchars($gk, ENT_QUOTES) ?>">
                            <label class="form-label d-flex align-items-center gap-2">
                                <?php if (!empty($gOpt['icon'])): ?>
                                    <img src="<?= htmlspecialchars($gOpt['icon'], ENT_QUOTES) ?>" style="width:18px;height:18px;object-fit:contain;" alt="" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <?= htmlspecialchars($gOpt['label']) ?> Rank
                            </label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <select class="form-select" data-rank-tier="<?= htmlspecialchars($gk, ENT_QUOTES) ?>">
                                        <option value="">— Not set —</option>
                                        <?php foreach (array_keys($tiers) as $t): ?>
                                            <option value="<?= htmlspecialchars($t, ENT_QUOTES) ?>" <?= $parsed['tier'] === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-6" data-rank-div-wrap="<?= htmlspecialchars($gk, ENT_QUOTES) ?>" style="<?= empty($divLabels) ? 'display:none' : '' ?>">
                                    <select class="form-select" data-rank-div="<?= htmlspecialchars($gk, ENT_QUOTES) ?>">
                                        <option value="">— Division —</option>
                                        <?php foreach ($divLabels as $d): ?>
                                            <option value="<?= htmlspecialchars($d, ENT_QUOTES) ?>" <?= $parsed['div'] === $d ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <p id="rankNoGame" class="text-muted small <?= empty($egGames)?'':'d-none' ?>">Select games above to set ranks.</p>

                </div>
            </div>

            
            <!-- ── Card: Availability (all 7 days) ── -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-title mb-0">Availability</h5>
                    <small class="text-muted">Toggle days on/off. Times are saved in the selected timezone.</small>
                </div>
                <div class="card-body">
                    <div class="avail-grid" id="availGrid">
                        <?php
                        $todayDow = (int)date('w');
                        for ($d = 0; $d < 7; $d++):
                            $isToday  = ($d === $todayDow);
                            $hasSlot  = isset($availByDay[$d]);
                            $fromVal  = $hasSlot ? $availByDay[$d]['from'] : '18:00';
                            $toVal    = $hasSlot ? $availByDay[$d]['to']   : '23:00';
                        ?>
                        <div class="avail-day-row <?= $hasSlot?'':'unavailable' ?>" data-day="<?= $d ?>">
                            <div class="day-label">
                                <?= $DAYS_FULL[$d] ?>
                                <?php if ($isToday): ?><span class="today-badge">Today</span><?php endif; ?>
                            </div>
                            <div class="time-inputs">
                                <input type="time" class="form-control form-control-sm slot-from"
                                    value="<?= $fromVal ?>"
                                    <?= $hasSlot?'':'disabled' ?>
                                    style="max-width:120px;">
                                <span>→</span>
                                <input type="time" class="form-control form-control-sm slot-to"
                                    value="<?= $toVal ?>"
                                    <?= $hasSlot?'':'disabled' ?>
                                    style="max-width:120px;">
                            </div>
                            <div class="avail-day-toggle">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input day-toggle" type="checkbox" role="switch"
                                        id="dayToggle<?= $d ?>"
                                        <?= $hasSlot ? 'checked' : '' ?>
                                        onchange="toggleDay(<?= $d ?>, this.checked)">
                                    <label class="form-check-label small" for="dayToggle<?= $d ?>">
                                        <?= $hasSlot ? 'Available' : 'Off' ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <button class="btn btn-primary mt-4" id="btnSaveAvailability">
                        <span class="indicator-label"><i class="fa-solid fa-calendar-check me-1"></i>Save Availability</span>
                        <span class="indicator-progress" style="display:none"><span class="spinner-border spinner-border-sm align-middle me-1"></span>Saving...</span>
                        <span class="indicator-success" style="display:none"><i class="fa-regular fa-circle-check me-1"></i>Saved!</span>
                    </button>
                </div>
            </div>

            <!-- ── Card: Change Password ── -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="card-header-title"><i class="fa-solid fa-lock me-2"></i>Change Password</h5></div>
                <div class="card-body">
                    <div class="row mb-4">
                        <label for="egCurrentPassword" class="col-sm-3 col-form-label form-label">Current Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="egCurrentPassword"
                                    placeholder="Current password" aria-label="Current password"
                                    autocomplete="current-password">
                                <button class="btn btn-outline-secondary js-toggle-password" type="button"
                                    data-target="#egCurrentPassword" aria-label="Show password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="egNewPassword" class="col-sm-3 col-form-label form-label">New Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="egNewPassword"
                                    placeholder="New password" aria-label="New password"
                                    autocomplete="new-password" minlength="6">
                                <button class="btn btn-outline-secondary js-toggle-password" type="button"
                                    data-target="#egNewPassword" aria-label="Show password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Minimum 6 characters.</small>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="egConfirmPassword" class="col-sm-3 col-form-label form-label">Confirm Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="egConfirmPassword"
                                    placeholder="Confirm new password" aria-label="Confirm new password"
                                    autocomplete="new-password" minlength="6">
                                <button class="btn btn-outline-secondary js-toggle-password" type="button"
                                    data-target="#egConfirmPassword" aria-label="Show password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary" id="btnUpdatePassword">
                        <span class="indicator-label"><i class="fa-solid fa-lock me-1"></i>Update Password</span>
                        <span class="indicator-progress" style="display:none"><span class="spinner-border spinner-border-sm align-middle me-1"></span>Updating...</span>
                        <span class="indicator-success" style="display:none"><i class="fa-regular fa-circle-check me-1"></i>Updated!</span>
                    </button>
                </div>
            </div>

        </div><!-- /.col-lg-8 -->

        <!-- ── RIGHT: Overview (sticky, single card) ── -->
        <div class="col-lg-4">

            <!-- Sticky Block Start Point -->
            <div id="egAccountSidebarNav"></div>

            <div class="js-sticky-block card" data-hs-sticky-block-options='{
                "parentSelector": "#egAccountSidebarNav",
                "breakpoint": "lg",
                "startPoint": "#egAccountSidebarNav",
                "endPoint": "#egStickyBlockEndPoint",
                "stickyOffsetTop": 20
                }'>
                <div class="card-header"><h5 class="card-header-title"><i class="fa-solid fa-heart me-2" style="color:#ec4899;"></i>Overview</h5></div>

                <div class="card-body text-center py-4">
                    <?php if (!empty(BOOSTER_DATA['icon'])): ?>
                        <img src="<?= htmlspecialchars(BOOSTER_DATA['icon']) ?>" class="avatar avatar-xl rounded-circle mb-3" alt="" style="box-shadow:0 0 0 3px rgba(168,85,247,.35);">
                    <?php else: ?>
                        <div class="avatar avatar-xl rounded-circle bg-soft-primary mb-3 d-flex align-items-center justify-content-center mx-auto" style="font-size:2rem;box-shadow:0 0 0 3px rgba(168,85,247,.35);">👧</div>
                    <?php endif; ?>
                    <h5 class="mb-1"><?= htmlspecialchars(BOOSTER_DATA['username']) ?></h5>
                    <p class="text-muted small mb-3" id="previewBio"><?= htmlspecialchars(mb_substr($egBioDecoded ?: 'No bio yet.', 0, 120), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>

                    <!-- Language flags preview -->
                    <div id="previewFlags" class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                        <?php foreach ($selectedLangs as $lc):
                            $lc = trim($lc); if(!$lc) continue;
                            $li = $LANG_MAP[$lc] ?? null; ?>
                            <?php if ($li): ?>
                                <img src="<?= $LANG_IMG_BASE . $li['img'] ?>"
                                     title="<?= $li['name'] ?>"
                                     style="width:22px;height:16px;object-fit:cover;border-radius:2px;"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div id="previewVoice" <?= $egVoiceChat ? '' : 'style="display:none"' ?>>
                        <span class="badge bg-soft-success text-success mb-3">
                            <i class="fa-solid fa-microphone me-1"></i>Voice Chat available
                        </span>
                    </div>

                    <a href="/egirls/<?= BOOSTER_ID ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View Public Profile
                    </a>
                </div>

                <div class="card-body pt-0">
                    <ul class="list-unstyled list-py-2 mb-0">
                        <li class="pb-0"><span class="card-subtitle">Account</span></li>
                        <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= (int)BOOSTER_ID ?></li>
                        <li>
                            <i class="fa-duotone fa-wallet dropdown-item-icon"></i>
                            <span class="fw-semibold"><?= function_exists('util_format_price_display') ? util_format_price_display((int)(BOOSTER_DATA['balance'] ?? 0)) : number_format((int)(BOOSTER_DATA['balance'] ?? 0)/100,2) ?> EUR</span>
                            <span class="text-muted"> available for payout</span>
                        </li>

                        <li class="pt-4 pb-0"><span class="card-subtitle">Contact</span></li>
                        <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= htmlspecialchars(BOOSTER_DATA['email'] ?? 'N/A') ?></li>
                        <li>
                            <i class="fa-brands fa-discord dropdown-item-icon"></i>
                            <?= !empty(BOOSTER_DATA['discord']) ? htmlspecialchars(BOOSTER_DATA['discord']) : 'N/A' ?>
                        </li>
                        <li>
                            <i class="fa-brands fa-discord dropdown-item-icon"></i>
                            <?= !empty(BOOSTER_DATA['discord_id']) ? htmlspecialchars(BOOSTER_DATA['discord_id']) : 'N/A' ?>
                        </li>
                    </ul>

                    <?php if (empty(BOOSTER_DATA['discord'])): ?>
                        <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= (int)BOOSTER_ID ?>"
                            class="btn btn-primary btn-sm w-100 mt-4">
                            <i class="fa-brands fa-discord me-1"></i> Connect to Discord
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= (int)BOOSTER_ID ?>"
                            class="btn btn-primary btn-sm w-100 mt-4">
                            <i class="fa-brands fa-discord me-1"></i> Reconnect to Discord
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /.row -->
    <div id="egStickyBlockEndPoint"></div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script>
(function () {
    const AJAX = '<?= AJAX_URL ?>';
    // game key => { tierName: [division labels] }. Empty array = tier has no divisions.
    const RANK_CONFIG = <?= json_encode($egRankConfigJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const LANG_IMG_BASE = '<?= $LANG_IMG_BASE ?>';
    const LANG_MAP = <?= json_encode($LANG_MAP) ?>;

    // ── Language reading helper (Tom Select) ──
    function getLangCodes() {
        // Works with both native <select multiple> and TomSelect
        const sel = document.getElementById('profileLanguages');
        if (!sel) return [];
        return [...sel.options].filter(o => o.selected).map(o => o.value);
    }

    function updateLangPreview() {
        const codes = getLangCodes();
        const pf = document.getElementById('previewFlags');
        if (!pf) return;
        pf.innerHTML = codes.map(c => {
            const l = LANG_MAP[c];
            return l ? `<img src="${LANG_IMG_BASE}${l.img}" title="${l.name}"
                style="width:22px;height:16px;object-fit:cover;border-radius:2px;"
                onerror="this.style.display='none'">` : '';
        }).join('');
    }

    // ── Init Tom Select — Languages, Country and Timezone share one config ──
    // Not routed through HSCore on purpose: its defaults keep already picked
    // options in the list and render the single selects as removable chips.
    function egPlaceholder(el, fallback) {
        try { return JSON.parse(el.dataset.hsTomSelectOptions || '{}').placeholder || fallback; }
        catch (e) { return fallback; }
    }

    function egLangRow(data, escape) {
        var flag = data.flag
            ? '<img src="' + escape(data.flag) + '" class="eg-lang-flag" alt="" onerror="this.style.display=\'none\'">'
            : '';
        return '<div class="eg-lang-row">' + flag + '<span>' + escape(data.text || '') + '</span></div>';
    }

    if (typeof TomSelect !== 'undefined') {
        document.querySelectorAll('.js-select-single').forEach(function (el) {
            if (el.tomselect) return;
            new TomSelect(el, {
                create: false,
                maxItems: 1,
                allowEmptyOption: true,
                // Search box lives inside the dropdown panel, so the closed
                // control always shows the plain selected value.
                plugins: ['dropdown_input'],
                placeholder: egPlaceholder(el, 'Search...'),
                render: {
                    no_results: function () { return '<div class="no-results">No results found</div>'; }
                }
            });
        });

        document.querySelectorAll('.js-select-multi').forEach(function (el) {
            if (el.tomselect) return;
            new TomSelect(el, {
                create: false,
                // Already selected languages disappear from the list.
                hideSelected: true,
                // Stays open so several languages can be picked in one go.
                closeAfterSelect: false,
                plugins: ['remove_button'],
                placeholder: egPlaceholder(el, 'Search...'),
                render: {
                    no_results: function () { return '<div class="no-results">No results found</div>'; },
                    // The <option> tags carry data-flag — keep the flag visible.
                    option: function (data, escape) { return egLangRow(data, escape); },
                    item: function (data, escape) { return egLangRow(data, escape); }
                },
                onChange: function () { updateLangPreview(); }
            });
        });
    }
    // Update preview after any change on language select
    document.getElementById('profileLanguages')?.addEventListener('change', updateLangPreview);

    // ── Game chip toggle ──
    window.toggleGame = function(chip) {
        chip.classList.toggle('selected');
        const games = [...document.querySelectorAll('#gamePicker .game-chip.selected')].map(c => c.dataset.game);
        document.getElementById('profileGames').value = games.join('|');

        // Only the picked games get a rank block.
        document.querySelectorAll('[data-rank-game]').forEach(sec => {
            sec.classList.toggle('visible', games.includes(sec.dataset.rankGame));
        });
        const noGame = document.getElementById('rankNoGame');
        if (noGame) noGame.classList.toggle('d-none', games.length > 0);
    };

    // ── Division options follow the selected tier (per game) ──
    function syncDivisions(gameKey, keepValue) {
        const tierSel = document.querySelector('[data-rank-tier="' + CSS.escape(gameKey) + '"]');
        const divSel  = document.querySelector('[data-rank-div="' + CSS.escape(gameKey) + '"]');
        const divWrap = document.querySelector('[data-rank-div-wrap="' + CSS.escape(gameKey) + '"]');
        if (!tierSel || !divSel || !divWrap) return;

        const labels = (RANK_CONFIG[gameKey] || {})[tierSel.value] || [];
        const previous = keepValue ? divSel.value : '';

        divSel.innerHTML = '<option value="">— Division —</option>'
            + labels.map(l => '<option value="' + l + '">' + l + '</option>').join('');
        if (previous && labels.includes(previous)) divSel.value = previous;

        divWrap.style.display = labels.length ? '' : 'none';
    }

    document.querySelectorAll('[data-rank-tier]').forEach(sel => {
        sel.addEventListener('change', function () { syncDivisions(this.dataset.rankTier, false); });
    });

    // ── Day availability toggle ──
    window.toggleDay = function(day, enabled) {
        const row = document.querySelector(`.avail-day-row[data-day="${day}"]`);
        if (!row) return;
        row.classList.toggle('unavailable', !enabled);
        row.querySelectorAll('input[type="time"]').forEach(i => i.disabled = !enabled);
        const lbl = row.querySelector('.day-toggle + label');
        if (lbl) lbl.textContent = enabled ? 'Available' : 'Off';
    };

    // ── Live bio preview ──
    document.getElementById('profileBio')?.addEventListener('input', function() {
        const el = document.getElementById('previewBio');
        if (el) el.textContent = this.value.slice(0,120) || 'No bio yet.';
    });

    // ── Button state helpers ──
    function btnLoading(btn) {
        btn.disabled = true;
        btn.querySelector('.indicator-label')    && (btn.querySelector('.indicator-label').style.display    = 'none');
        btn.querySelector('.indicator-progress') && (btn.querySelector('.indicator-progress').style.display = 'inline');
    }
    function btnSuccess(btn) {
        btn.querySelector('.indicator-progress') && (btn.querySelector('.indicator-progress').style.display = 'none');
        btn.querySelector('.indicator-success')  && (btn.querySelector('.indicator-success').style.display  = 'inline');
        setTimeout(() => {
            btn.disabled = false;
            btn.querySelector('.indicator-label')   && (btn.querySelector('.indicator-label').style.display   = 'inline');
            btn.querySelector('.indicator-success') && (btn.querySelector('.indicator-success').style.display = 'none');
        }, 2200);
    }
    function btnReset(btn) {
        btn.disabled = false;
        btn.querySelector('.indicator-label')    && (btn.querySelector('.indicator-label').style.display    = 'inline');
        btn.querySelector('.indicator-progress') && (btn.querySelector('.indicator-progress').style.display = 'none');
        btn.querySelector('.indicator-success')  && (btn.querySelector('.indicator-success').style.display  = 'none');
    }

    // ── Build rank string from selects ("Diamond II" / "Challenger") ──
    function getRank(game) {
        const tierSel = document.querySelector('[data-rank-tier="' + CSS.escape(game) + '"]');
        const tier = tierSel ? tierSel.value : '';
        if (!tier) return '';
        const labels = (RANK_CONFIG[game] || {})[tier] || [];
        if (!labels.length) return tier;
        const divSel = document.querySelector('[data-rank-div="' + CSS.escape(game) + '"]');
        const div = divSel ? divSel.value : '';
        return div ? tier + ' ' + div : tier;
    }

    // Ranks of every game, keyed by game — the server keeps lol/val/tft in their
    // own columns and stores the rest as JSON.
    function getAllRanks() {
        const out = {};
        document.querySelectorAll('[data-rank-game]').forEach(sec => {
            const key = sec.dataset.rankGame;
            const rank = getRank(key);
            if (rank) out[key] = rank;
        });
        return out;
    }

    // ── Save Profile ──
    document.getElementById('btnSaveProfile').addEventListener('click', function() {
        const btn = this;
        btnLoading(btn);
        $.post(AJAX, {
            action:       'egirl_save_profile',
            bio:          document.getElementById('profileBio').value,
            // Send as pipe-string; ajax handler supports both array and string
            languages:    getLangCodes().join('|'),
            country:      document.getElementById('profileCountry').value,
            timezone:     document.getElementById('profileTimezone').value,
            games:        document.getElementById('profileGames').value,
            lol_rank:     getRank('lol'),
            val_rank:     getRank('val'),
            tft_rank:     getRank('tft'),
            game_ranks:   JSON.stringify(getAllRanks()),
            voice_chat:   document.getElementById('voiceChatHidden').value,
            show_profile: document.getElementById('showProfileHidden').value,
        }, function(res) {
            toast_from_response(res, 'Profile updated.') ? btnSuccess(btn) : btnReset(btn);
        }).fail(() => { toast_request_failed('Could not save your profile.'); btnReset(btn); });
    });

    // ── Save Availability ──
    document.getElementById('btnSaveAvailability').addEventListener('click', function() {
        const btn = this;
        const tz  = document.getElementById('profileTimezone').value || 'Europe/Berlin';
        const slots = [];
        document.querySelectorAll('.avail-day-row').forEach(row => {
            const toggle = row.querySelector('.day-toggle');
            if (!toggle || !toggle.checked) return; // skip disabled days
            slots.push({
                day:      row.dataset.day,
                from:     row.querySelector('.slot-from').value,
                to:       row.querySelector('.slot-to').value,
                timezone: tz,
            });
        });
        btnLoading(btn);
        $.post(AJAX, { action: 'egirl_save_availability', availability: JSON.stringify(slots) }, function(res) {
            toast_from_response(res, 'Availability updated.') ? btnSuccess(btn) : btnReset(btn);
        }).fail(() => { toast_request_failed('Could not save your availability.'); btnReset(btn); });
    });

    // ── Toggle password visibility ──
    document.querySelectorAll('.js-toggle-password').forEach(function(btn){
        btn.addEventListener('click', function(){
            const target = document.querySelector(this.dataset.target);
            const icon = this.querySelector('i');
            if (!target) return;
            const isHidden = target.type === 'password';
            target.type = isHidden ? 'text' : 'password';
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            if (icon) {
                icon.classList.toggle('fa-eye', !isHidden);
                icon.classList.toggle('fa-eye-slash', isHidden);
            }
        });
    });

    // ── Update Password ──
    document.getElementById('btnUpdatePassword').addEventListener('click', function() {
        const btn = this;
        const currentEl = document.getElementById('egCurrentPassword');
        const newEl     = document.getElementById('egNewPassword');
        const confirmEl = document.getElementById('egConfirmPassword');
        const current = currentEl.value;
        const next    = newEl.value;
        const confirm = confirmEl.value;

        if (!current || !next || !confirm) {
            create_toast('danger', 'Missing fields', 'Please fill in all password fields.');
            return;
        }
        if (next.length < 6) {
            create_toast('danger', 'Password too short', 'New password must be at least 6 characters.');
            return;
        }
        if (next !== confirm) {
            create_toast('danger', 'Passwords do not match', 'New password and confirmation do not match.');
            return;
        }

        btnLoading(btn);
        $.post(AJAX, {
            action: 'booster_update_password',
            current_password: current,
            new_password: next,
            confirm_password: confirm,
        }, function(res) {
            if (!toast_from_response(res, 'Password updated.')) { btnReset(btn); return; }
            btnSuccess(btn);
            currentEl.value = '';
            newEl.value = '';
            confirmEl.value = '';
            [currentEl, newEl, confirmEl].forEach(function(el){ el.type = 'password'; });
            document.querySelectorAll('.js-toggle-password i').forEach(function(icon){
                icon.classList.add('fa-eye');
                icon.classList.remove('fa-eye-slash');
            });
        }).fail(function(){ toast_request_failed('Could not update password.'); btnReset(btn); });
    });

    // ── Sticky Overview card ──
    if (typeof HSStickyBlock !== 'undefined') {
        new HSStickyBlock('.js-sticky-block', {
            targetSelector: document.getElementById('header') && document.getElementById('header').classList.contains('navbar-fixed') ? '#header' : null
        });
    }

})();
</script>
<?= $this->end() ?>
