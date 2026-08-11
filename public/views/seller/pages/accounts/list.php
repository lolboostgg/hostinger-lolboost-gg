<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'My Accounts | LoLBoost.gg']]) ?>

<?php require_once dirname(__DIR__) . '/_seller_rank.php'; ?>

<?php $effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []); ?>

<?php
if (!function_exists('seller_account_roblox_experience_icon_map')) {
    function seller_account_roblox_experience_icon_map(): array
    {
        return [
            'Blox Fruits' => 'BloxFruits.webp',
            'TYPE://SOUL' => 'TypeSoul.webp',
            'Adopt Me' => 'AdoptMe.webp',
            'Steal A Brainrot' => 'StealABrainrot.webp',
            'All Star Tower Defense' => 'AllStarTowerDefense.webp',
            'King Legacy' => 'KingLegacy.webp',
            'Anime Champions Simulator' => 'AnimeChampionsSimulator.webp',
            "Barry's Prison Run V2" => 'BarrysPrisonRunV2.webp',
            'Blade Ball' => 'BladeBall.webp',
            'Cotton Obby' => 'CottonObby.webp',
            'Easy Obby' => 'EasyObby.webp',
            'Death Ball' => 'DeathBall.webp',
            'DOORS' => 'Doors.webp',
            'Dungeon Quest' => 'DungeonQuest.webp',
            'Hide and Seek Extreme' => 'HideAndSeekExtreme.webp',
            'Jailbreak' => 'Jailbreak.webp',
            'Murder Mystery 2' => 'MurderMystery2.webp',
            'Natural Disaster Survival' => 'NaturalDisasterSurvival.webp',
            'Pet Simulator 99' => 'PetSimulator99.webp',
            'Pet Simulator X' => 'PetSimulatorX.webp',
            'Piggy' => 'Piggy.webp',
            'Scuba Diving at Quill Lake' => 'ScubaDivingAtQuillLake.webp',
            'Speed Run 4' => 'SpeedRun4.webp',
            'Super Bomb Survival!!' => 'SuperBombSurvival.webp',
            'Theme Park Tycoon 2' => 'ThemeParkTycoon2.webp',
            'The Strongest Battlegrounds' => 'TheStrongestBattlegrounds.webp',
            'Work at a Pizza Place' => 'WorkAtAPizzaPlace.webp',
            'Wuthering Waves' => 'WutheringWaves.webp',
            'Anime Defenders' => 'AnimeDefenders.webp',
            'Grand Piece' => 'GrandPiece.webp',
            'Grow a Garden' => 'GrowAGarden.webp',
            'Others' => 'Others.webp',
        ];
    }
}

if (!function_exists('seller_account_scalar_value')) {
    function seller_account_scalar_value($value): string
    {
        if (is_array($value)) {
            $value = array_values(array_filter(array_map('strval', $value), static fn($v) => trim($v) !== ''));
            return trim((string)($value[0] ?? ''));
        }
        return trim((string)$value);
    }
}

if (!function_exists('seller_account_roblox_experience_name')) {
    function seller_account_roblox_experience_name(array $gameData): string
    {
        foreach (['games', 'experience_game', 'experience', 'roblox_experience'] as $key) {
            if (array_key_exists($key, $gameData)) {
                $value = seller_account_scalar_value($gameData[$key]);
                if ($value !== '') return $value;
            }
        }
        return '';
    }
}

if (!function_exists('seller_account_roblox_experience_icon_url')) {
    function seller_account_roblox_experience_icon_url(string $experienceName): string
    {
        $experienceName = trim($experienceName);
        if ($experienceName === '') return '';

        $map = seller_account_roblox_experience_icon_map();
        $file = $map[$experienceName] ?? '';

        if ($file === '') {
            $normalizedWanted = strtolower(preg_replace('/[^a-z0-9]+/i', '', $experienceName));
            foreach ($map as $label => $mappedFile) {
                $normalizedLabel = strtolower(preg_replace('/[^a-z0-9]+/i', '', $label));
                if ($normalizedWanted === $normalizedLabel) {
                    $file = $mappedFile;
                    break;
                }
            }
        }

        if ($file === '') return '';

        return rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/public/assets'), '/') . '/website/images/roblox-icons/' . $file;
    }
}
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
    .ts-wrapper {
        min-height: 42px !important;
    }

    .custom-offcanvas {
        width: 50vw !important;
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
    }
    .custom-offcanvas .offcanvas-header {
        flex-shrink: 0;
    }
    /* Fixed earnings bar */
    .oc-earnings-bar {
        flex-shrink: 0;
        padding: 10px 22px;
        background: rgba(109,92,255,.08);
        border-bottom: 1px solid rgba(109,92,255,.18);
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .83rem;
        color: rgba(255,255,255,.7);
    }
    /* Custom step indicator */
    .oc-steps {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        padding: 14px 22px;
        border-bottom: 1px solid var(--bs-card-border-color);
        gap: 0;
    }
    .oc-step {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    .oc-step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
        font-weight: 900;
        flex-shrink: 0;
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(255,255,255,.12);
        color: rgba(255,255,255,.5);
        transition: all .2s;
    }
    .oc-step.active .oc-step-num {
        background: linear-gradient(135deg,#6d5cff,#b05cff);
        border-color: transparent;
        color: #fff;
    }
    .oc-step.done .oc-step-num {
        background: rgba(74,222,128,.15);
        border-color: rgba(74,222,128,.3);
        color: #4ade80;
    }
    .oc-step-label {
        font-size: .8rem;
        font-weight: 700;
        color: rgba(255,255,255,.4);
        transition: color .2s;
    }
    .oc-step.active .oc-step-label { color: #c4b5fd; font-weight: 900; }
    .oc-step.done  .oc-step-label  { color: rgba(255,255,255,.6); }
    .oc-step-line {
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.08);
        margin: 0 8px;
        flex-shrink: 0;
    }
    .oc-step-line.done { background: rgba(74,222,128,.3); }
    /* Compact searchable game selector */
    .oc-game-picker {
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 16px;
        background: rgba(255,255,255,.025);
        overflow: hidden;
    }
    .oc-game-picker__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        background: rgba(255,255,255,.025);
    }
    .oc-game-search {
        position: relative;
        flex: 1;
        min-width: 0;
    }
    .oc-game-search i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255,255,255,.34);
        font-size: .82rem;
        pointer-events: none;
    }
    .oc-game-search input {
        width: 100%;
        height: 40px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.16);
        color: #fff;
        padding: 0 14px 0 38px;
        font-size: .86rem;
        outline: none;
    }
    .oc-game-search input:focus {
        border-color: rgba(109,92,255,.55);
        box-shadow: 0 0 0 3px rgba(109,92,255,.12);
    }
    .oc-game-selected-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        max-width: 260px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(109,92,255,.35);
        background: rgba(109,92,255,.14);
        color: #fff;
        font-size: .8rem;
        font-weight: 900;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .oc-game-selected-chip img {
        width: 18px;
        height: 18px;
        object-fit: contain;
        flex: 0 0 18px;
    }
    .oc-game-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        max-height: 430px;
        overflow-y: auto;
        padding: 12px;
    }
    .oc-game-card {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 52px;
        padding: 9px 11px;
        border-radius: 13px;
        border: 1px solid rgba(255,255,255,.09);
        background: rgba(255,255,255,.035);
        cursor: pointer;
        transition: border-color .15s, background .15s, box-shadow .15s, transform .12s;
        width: 100%;
        text-align: left;
    }
    .oc-game-card:hover { border-color: rgba(109,92,255,.42); background: rgba(109,92,255,.08); transform: translateY(-1px); }
    .oc-game-card.selected { border-color: #6d5cff; background: rgba(109,92,255,.16); box-shadow: 0 0 0 2px rgba(109,92,255,.13); }
    .oc-game-card__icon { width: 32px; height: 32px; display:flex; align-items:center; justify-content:center; flex: 0 0 32px; }
    .oc-game-card__icon img { width: 30px; height: 30px; object-fit: contain; display:block; filter: drop-shadow(0 4px 10px rgba(0,0,0,.22)); }
    .oc-game-card__name { font-size: .86rem; font-weight: 900; color: #fff; line-height: 1.15; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .oc-game-card__sub  { font-size: .68rem; color: rgba(255,255,255,.36); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .oc-game-card.is-hidden { display: none !important; }
    .oc-game-empty {
        display: none;
        padding: 28px 14px;
        text-align: center;
        color: rgba(255,255,255,.36);
        font-size: .86rem;
    }
    .oc-game-empty.is-visible { display: block; }
    @media (max-width: 767px) {
        .oc-game-picker__top { flex-direction: column; align-items: stretch; }
        .oc-game-selected-chip { max-width: 100%; justify-content: center; }
        .oc-game-list { grid-template-columns: 1fr; max-height: 58vh; }
    }
    .al-game-pills { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .al-game-pill { display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.04); color:rgba(255,255,255,.82); font-size:.82rem; font-weight:800; cursor:pointer; transition:all .15s ease; user-select:none; }
    .al-game-pill:hover { border-color: rgba(109,92,255,.34); background: rgba(109,92,255,.08); }
    .al-game-pill.active { color:#fff; border-color:#6d5cff; background:rgba(109,92,255,.16); box-shadow:0 0 0 3px rgba(109,92,255,.14); }
    .al-game-pill img { width:16px; height:16px; object-fit:contain; display:block; }
    /* Scrollable content */
    .custom-offcanvas .offcanvas-body {
        flex: 1 !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        padding: 0 !important;
    }
    .custom-offcanvas .offcanvas-body > form {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 0;
    }
    .oc-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 18px 22px;
        scroll-behavior: smooth;
    }
    /* Compact form fields */
    .oc-scroll .mb-3 { margin-bottom: .85rem !important; }
    .oc-scroll .form-label { font-size: .78rem; font-weight: 700; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
    .oc-scroll .form-control, .oc-scroll .form-select { padding: 8px 12px !important; font-size: .87rem !important; }
    .oc-scroll .row { --bs-gutter-y: .6rem; }
    /* Fixed footer */
    .oc-footer {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 22px;
        border-top: 1px solid var(--bs-card-border-color);
        background: var(--bs-offcanvas-bg, #1e2028);
    }
    .oc-btn-next {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        background: linear-gradient(135deg,#6d5cff,#b05cff);
        border: none;
        border-radius: 11px;
        padding: 8px 20px;
        font-size: .87rem;
        font-weight: 900;
        color: #fff;
        cursor: pointer;
        transition: opacity .15s, transform .12s;
    }
    .oc-btn-next:hover { opacity: .88; transform: translateY(-1px); }
    .oc-btn-prev {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 11px;
        padding: 8px 16px;
        font-size: .87rem;
        font-weight: 700;
        color: rgba(255,255,255,.65);
        cursor: pointer;
        transition: background .12s;
    }
    .oc-btn-prev:hover { background: rgba(255,255,255,.08); color: #fff; }

    .toggle-group {
        display: flex;
        border-radius: 8px;
        padding: 5px;
        width: 200px;
        position: relative;
    }

    .toggle-group input {
        display: none;
    }

    .toggle-label {
        flex: 1;
        text-align: center;
        padding: 10px;
        color: #bbb;
        font-weight: 500;
        cursor: pointer;
        transition: 0.3s;
    }

    /* Manual Delivery disabled */
    .toggle-label--disabled {
        opacity: .55;
        cursor: not-allowed !important;
    }
    .toggle-label--disabled:hover {
        opacity: .65;
        filter: brightness(1.05);
    }

    input:checked+.toggle-label {
        background-color: #6366f1;
        color: #fff;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.3s;
        border-radius: 24px;
    }

    .slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #6366f1;
    }

    input:checked+.slider:before {
        transform: translateX(16px);
    }

    .ts-control .item {
        color: #fff;
    }

    .ts-wrapper.multi .ts-control>div {
        background: #35383bff !important;
        color: #fff !important;
    }

    .ts-wrapper.plugin-remove_button .item .remove {
        border-left-color: #35383bff !important;
    }

    @media only screen and (max-width: 576px) {
        .custom-offcanvas {
            width: 100vw !important;
        }
    }

    /* Pretty validation inside modal */
    .js-validation-alert { border-radius: 12px; }
    .js-step-form.was-validated .form-control:invalid,
    .js-step-form.was-validated .form-select:invalid {
        border-color: var(--bs-danger) !important;
    }
    .ts-wrapper.is-invalid .ts-control {
        border-color: var(--bs-danger) !important;
        box-shadow: 0 0 0 .25rem rgba(220,53,69,.25);
    }
    .form-check.is-invalid .form-check-input {
        border-color: var(--bs-danger) !important;
    }

    /* Section labels inside offcanvas */
    .oc-section-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .09em;
        color: rgba(255,255,255,.3);
        margin: 14px 0 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .oc-section-label:first-child { margin-top: 0; }
    .oc-section-label i { color: #6d5cff; font-size: .65rem; }

    /* Required asterisk */
    .oc-required { color: #f87171; font-size: .75rem; vertical-align: super; }

    /* Hint text */
    .oc-hint {
        font-size: .72rem;
        color: rgba(255,255,255,.28);
        margin-top: 4px;
        line-height: 1.4;
    }

    /* Toggle row (2fa) */
    .oc-toggle-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        background: rgba(255,255,255,.03);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 9px;
        min-height: 42px;
    }

    /* Tag button (All champions) */
    .oc-tag-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid rgba(109,92,255,.35);
        background: rgba(109,92,255,.12);
        color: #c4b5fd;
        font-size: .72rem;
        font-weight: 800;
        cursor: pointer;
        transition: background .12s;
    }
    .oc-tag-btn:hover { background: rgba(109,92,255,.25); }

    /* ── Delivery method card toggle ── */
    .oc-delivery-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .oc-delivery-toggle input[type="radio"] { display: none; }
    .oc-delivery-opt {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,.09);
        background: rgba(255,255,255,.03);
        cursor: pointer;
        transition: border-color .15s, background .15s;
        user-select: none;
    }
    .oc-delivery-opt:hover { border-color: rgba(109,92,255,.35); background: rgba(109,92,255,.06); }
    input[type="radio"]:checked + .oc-delivery-opt {
        border-color: rgba(109,92,255,.6);
        background: rgba(109,92,255,.14);
        box-shadow: 0 0 0 1px rgba(109,92,255,.3);
    }
    .oc-delivery-opt--disabled { opacity: .45; cursor: not-allowed; pointer-events: none; }
    .oc-delivery-icon {
        width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(109,92,255,.15); border: 1px solid rgba(109,92,255,.2);
        color: #c4b5fd; font-size: .85rem;
    }
    input[type="radio"]:checked + .oc-delivery-opt .oc-delivery-icon {
        background: rgba(109,92,255,.3); border-color: rgba(109,92,255,.5);
    }
    .oc-delivery-text { display: flex; flex-direction: column; gap: 2px; }
    .oc-delivery-title { font-size: .82rem; font-weight: 800; color: rgba(255,255,255,.75); display: flex; align-items: center; gap: 6px; }
    input[type="radio"]:checked + .oc-delivery-opt .oc-delivery-title { color: #c4b5fd; }
    .oc-delivery-sub { font-size: .7rem; color: rgba(255,255,255,.32); font-weight: 500; }
    .oc-soon-badge {
        display: inline-flex; align-items: center;
        padding: 1px 6px; border-radius: 99px;
        font-size: .62rem; font-weight: 800;
        background: rgba(251,191,36,.12); border: 1px solid rgba(251,191,36,.25); color: #fbbf24;
    }

    /* ── Credentials global reveal btn ── */
    .oc-reveal-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 7px;
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(255,255,255,.05);
        color: rgba(255,255,255,.45);
        font-size: .7rem; font-weight: 800;
        cursor: pointer; transition: background .12s, color .12s, border-color .12s;
    }
    .oc-reveal-btn:hover { background: rgba(109,92,255,.15); border-color: rgba(109,92,255,.35); color: #c4b5fd; }
    .oc-reveal-btn.revealed { background: rgba(109,92,255,.18); border-color: rgba(109,92,255,.4); color: #c4b5fd; }

    /* Blur sensitive fields when hidden */
    #ocCredsBlock.oc-hidden .oc-sensitive,
    #add_email_wrap.oc-hidden .oc-sensitive,
    #add_email_password_wrap.oc-hidden .oc-sensitive { filter: blur(4px); user-select: none; transition: filter .2s; }
    #ocCredsBlock .oc-sensitive,
    #add_email_wrap .oc-sensitive,
    #add_email_password_wrap .oc-sensitive { transition: filter .2s; }

    /* Upload box */
    .account-upload-box {
        border: 2px dashed rgba(255,255,255,.12);
        border-radius: 12px;
        transition: all .2s ease;
        background: rgba(255,255,255,.02);
        cursor: pointer;
    }
    .account-upload-box:hover {
        border-color: #6366f1;
        background: rgba(99,102,241,.05);
    }
    .account-upload-box.dragover {
        border-color: #6366f1;
        background: rgba(99,102,241,.08);
    }

    /* Gallery preview tiles */
    .gallery-preview-tile {
        position: relative;
        overflow: hidden;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        background: rgba(255,255,255,.02);
        cursor: grab;
    }

    .gallery-preview-tile:active { cursor: grabbing; }

    .gallery-preview-tile.is-main {
        outline: 2px solid rgba(99, 102, 241, .9);
        outline-offset: 2px;
        box-shadow: 0 0 0 .25rem rgba(99, 102, 241, .15);
    }

    .gallery-preview-badge {
        position: absolute;
        top: .5rem;
        left: .5rem;
        padding: .25rem .5rem;
        border-radius: 999px;
        background: rgba(99, 102, 241, .95);
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.25);
    }

    .gallery-preview-hint {
        position: absolute;
        bottom: .5rem;
        left: .5rem;
        right: .5rem;
        padding: .25rem .5rem;
        border-radius: .5rem;
        background: rgba(0,0,0,.35);
        color: rgba(255,255,255,.9);
        font-size: .75rem;
        z-index: 2;
        opacity: 1;
        transition: opacity .2s ease;
    }

    .gallery-preview-tile img {
        width: 100% !important;
        height: 150px !important;
        object-fit: cover;
        transition: transform 0.3s ease;
        border-radius: inherit;
        display: block;
    }

    .gallery-preview-tile:hover img {
        transform: scale(1.05);
    }

    .gallery-preview-overlay {
        position: absolute;
        inset: 0;
        background-color: rgba(220, 53, 69, 0.30);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .gallery-preview-tile:hover .gallery-preview-overlay {
        opacity: 1;
    }

    .gallery-preview-remove {
        border: 0;
        background: rgba(220, 53, 69, .95);
        color: #fff;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.25);
        transition: transform .15s ease, opacity .15s ease;
    }

    .gallery-preview-remove:hover {
        transform: scale(1.05);
        opacity: .95;
    }

    /* TomSelect: Multi-select chips max 2 rows */
    .ts-wrapper.multi .ts-control {
        max-height: calc(2 * 2.2rem);
        overflow-y: auto;
        overflow-x: hidden;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .ts-wrapper .ts-dropdown { pointer-events: none; }
    .ts-wrapper.dropdown-active .ts-dropdown { pointer-events: auto; z-index: 2000; }

    .ts-wrapper.ts-dropup.dropdown-active .ts-dropdown {
        top: auto !important;
        bottom: calc(100% + 6px) !important;
    }

    /* Status filter pills */
    #accountsStatusPills .nav-link { border-radius: 999px; padding: .375rem .75rem; }

    .content.container {
        max-width: 100% !important;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
</style>
<?= $this->end() ?>

<!-- ══ ACCOUNTS LIST ══ -->
<style>
/* ── List page design ── */
.al-page .card { background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important; }
.al-page .card::before { display:none!important; }


.al-add-btn {
  display:inline-flex;align-items:center;gap:.5rem;
  background:linear-gradient(135deg,#6d5cff,#b05cff);
  border:none;border-radius:13px;padding:.6rem 1.4rem;
  font-weight:900;font-size:.9rem;color:#fff;cursor:pointer;
  transition:opacity .15s,transform .12s;text-decoration:none;
}
.al-add-btn:hover { opacity:.88;transform:translateY(-1px);color:#fff; }

/* Pills filter */
.al-pills { display:flex;gap:6px;flex-wrap:wrap; }
/* ── Custom checkbox ── */
.al-chk {
    appearance: none;
    -webkit-appearance: none;
    width: 17px;
    height: 17px;
    border-radius: 5px;
    border: 1.5px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.06);
    cursor: pointer;
    flex-shrink: 0;
    position: relative;
    transition: background .12s, border-color .12s;
    display: inline-block;
    vertical-align: middle;
}
.al-chk:hover {
    border-color: rgba(109,92,255,.6);
    background: rgba(109,92,255,.12);
}
.al-chk:checked {
    background: #6d5cff;
    border-color: #6d5cff;
}
.al-chk:checked::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 1.5px;
    width: 5px;
    height: 9px;
    border: 2px solid #fff;
    border-top: 0;
    border-left: 0;
    transform: rotate(45deg);
}
.al-chk:indeterminate {
    background: rgba(109,92,255,.4);
    border-color: rgba(109,92,255,.7);
}
.al-chk:indeterminate::after {
    content: '';
    position: absolute;
    left: 3px;
    top: 6.5px;
    width: 9px;
    height: 2px;
    background: #fff;
    border-radius: 1px;
}
.al-chk:disabled {
    opacity: .3;
    cursor: not-allowed;
}
.al-pill { display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none; }
.al-pill:hover { background:rgba(255,255,255,.08);color:rgba(255,255,255,.85); }
.al-pill.active { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-pill[data-status="Active"].active   { background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80; }
.al-pill[data-status="Sold"].active     { background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.30);color:#fb7185; }
.al-pill[data-status="Unlisted"].active { background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.35);color:#facc15; }

.al-search-wrap { position:relative; }
.al-search-wrap input { background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s; }
.al-search-wrap input:focus { border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important; }
.al-search-wrap input::placeholder { color:rgba(255,255,255,.25)!important; }
.al-search-icon { position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none; }

/* ── List table ── */
.al-table-wrap {
  border:1px solid rgba(255,255,255,.07);
  border-radius:20px;
  overflow:visible;
  background:#25282a;
  box-shadow:0 4px 32px rgba(0,0,0,.28);
  position:relative;
}
.al-table {
  width:100%;
  border-collapse:collapse;
  border-radius:20px;
  overflow:hidden;
  display:table;
}

/* Head */
.al-table thead tr {
  background:rgba(255,255,255,.03);
  border-bottom:1px solid rgba(255,255,255,.06);
}
.al-table thead th {
  padding:11px 16px;
  font-size:.68rem;
  font-weight:900;
  color:rgba(255,255,255,.35);
  text-transform:uppercase;
  letter-spacing:.07em;
  white-space:nowrap;
  user-select:none;
}
.al-table thead th.sortable { cursor:pointer; }
.al-table thead th.sortable:hover { color:rgba(255,255,255,.7); }
.al-table thead th .sort-icon { margin-left:4px;opacity:.35;font-size:.6rem; }
.al-table thead th.sort-asc  .sort-icon,
.al-table thead th.sort-desc .sort-icon { opacity:1;color:#c4b5fd; }

/* Body rows */
.al-table tbody .al-row {
  border-bottom:1px solid rgba(255,255,255,.04);
  transition:background .12s;
  cursor:pointer;
}
.al-table tbody .al-row:last-child { border-bottom:none; }
.al-table tbody .al-row:hover { background:rgba(109,92,255,.08); }

.al-table tbody td {
  padding:13px 16px;
  vertical-align:middle;
  font-size:.85rem;
  color:rgba(255,255,255,.8);
}

/* ID col */
.al-col-id { font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);font-variant-numeric:tabular-nums; }

/* Account col */
.al-acc-wrap  { display:flex;align-items:center;gap:11px; }
.al-acc-media { position:relative;width:34px;height:34px;flex:0 0 34px; }
.al-acc-img,
.al-acc-platform-box { width:34px;height:34px;border-radius:9px;object-fit:contain;background:rgba(255,255,255,.04);padding:2px;flex-shrink:0; }
.al-acc-platform-box { display:flex;align-items:center;justify-content:center;gap:2px;border:1px solid rgba(255,255,255,.08); }
.al-acc-platform-box .account-platform-icons { display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;line-height:0; }
.al-acc-platform-icon { width:18px;height:18px;object-fit:contain;display:block; }
.al-acc-platform-box .al-acc-platform-icon:nth-child(n+4) { display:none; }
.al-acc-title-line { display:flex;align-items:center;gap:6px;min-width:0; }
.al-game-corner-icon { position:absolute;right:-4px;bottom:-4px;width:16px;height:16px;border-radius:5px;object-fit:contain;background:#24282b;border:1px solid rgba(255,255,255,.12);padding:1px;box-shadow:0 2px 6px rgba(0,0,0,.35); }
.al-acc-name  { font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.al-acc-sub   { font-size:.74rem;color:rgba(255,255,255,.38);margin-top:1px; }

/* Price col */
.al-col-price { font-size:.9rem;font-weight:800;color:rgba(255,255,255,.9);font-variant-numeric:tabular-nums; }

/* Earnings col */
.al-col-earnings { font-size:.85rem;font-weight:700;color:#4ade80;font-variant-numeric:tabular-nums; }

/* Status badges */
.al-badge {
  display:inline-flex;align-items:center;gap:.3rem;
  padding:4px 10px;border-radius:99px;
  font-size:.71rem;font-weight:800;white-space:nowrap;
}
.al-badge--active   { background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80; }
.al-badge--sold     { background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185; }
.al-badge--unlisted { background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15; }

/* Buyer col */
.al-buyer-wrap { display:inline-flex;align-items:center;gap:.4rem; }
.al-buyer-avatar {
  width:22px;height:22px;border-radius:50%;
  background:rgba(99,102,241,.2);color:#818cf8;
  display:inline-flex;align-items:center;justify-content:center;
  font-size:.6rem;font-weight:900;flex-shrink:0;
  border:1px solid rgba(99,102,241,.25);
}
.al-buyer-avatar img { width:100%;height:100%;border-radius:50%;object-fit:cover; }
.al-buyer-name { font-size:.8rem;font-weight:700;color:rgba(255,255,255,.75); }

/* Date col */
.al-col-date { font-size:.78rem;color:rgba(255,255,255,.38);font-variant-numeric:tabular-nums; }

/* View btn */
.al-view-btn {
  display:inline-flex;align-items:center;gap:.35rem;
  padding:7px 14px;border-radius:9px;
  font-size:.79rem;font-weight:800;
  background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.28);
  color:#c4b5fd;transition:background .12s,border-color .12s;
  text-decoration:none;white-space:nowrap;
}
.al-view-btn:hover { background:rgba(109,92,255,.28);border-color:rgba(109,92,255,.55);color:#fff; }

/* ── Actions dropdown ── */
.al-actions-wrap { position:relative;display:inline-block; }
.al-actions-btn {
  width:32px;height:32px;border-radius:9px;
  border:1px solid rgba(255,255,255,.09);
  background:rgba(255,255,255,.04);
  color:rgba(255,255,255,.5);
  font-size:.8rem;cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;
  transition:background .12s,color .12s;
}
.al-actions-btn:hover { background:rgba(255,255,255,.09);color:rgba(255,255,255,.9); }
.al-actions-btn.is-open { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.4);color:#c4b5fd; }
.al-actions-menu {
  display:none;position:fixed;
  min-width:190px;z-index:9999;
  background:#2a2d35;border:1px solid rgba(255,255,255,.1);
  border-radius:13px;padding:5px;
  box-shadow:0 8px 32px rgba(0,0,0,.6);
  animation:alMenuIn .12s ease;
}
.al-actions-menu.is-open { display:block; }
@keyframes alMenuIn { from{opacity:0;transform:scale(.97)} to{opacity:1;transform:scale(1)} }
.al-actions-menu { transform-origin: top right; }
.al-action-item {
  display:flex;align-items:center;gap:9px;
  width:100%;padding:8px 11px;border-radius:8px;
  font-size:.8rem;font-weight:700;
  color:rgba(255,255,255,.72);
  background:none;border:none;cursor:pointer;
  text-decoration:none;text-align:left;
  transition:background .1s,color .1s;
}
.al-action-item:hover { background:rgba(255,255,255,.06);color:#fff; }
.al-action-item i { width:14px;text-align:center;color:rgba(255,255,255,.3);font-size:.78rem;flex-shrink:0; }
.al-action-item:hover i { color:rgba(255,255,255,.6); }
.al-action-danger { color:#fb7185 !important; }
.al-action-danger:hover { background:rgba(251,113,133,.08) !important; }
.al-action-danger i { color:#fb7185 !important; }
.al-action-divider { height:1px;background:rgba(255,255,255,.06);margin:4px 0; }

/* Meta pills inside rows */
.al-row-pills { display:flex;gap:4px;flex-wrap:wrap;margin-top:4px; }
.al-mini-pill {
  display:inline-flex;align-items:center;gap:.22rem;
  padding:2px 7px;border-radius:99px;
  font-size:.66rem;font-weight:700;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
  color:rgba(255,255,255,.5);
}

/* empty state */
.al-empty { text-align:center;padding:64px 24px;color:rgba(255,255,255,.35); }
.al-empty i { font-size:3rem;margin-bottom:12px;display:block;opacity:.3; }

/* pagination */
.al-footer { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0; }
.al-pg-btn { width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s; }
.al-pg-btn:hover:not(:disabled) { background:rgba(255,255,255,.09); }
.al-pg-btn.al-pg-active { background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-pg-btn:disabled { opacity:.35;cursor:not-allowed; }

/* Buyer dash */
.al-dash { color:rgba(255,255,255,.2); }

/* ── Credentials col ── */
.al-creds-wrap { display:flex;flex-direction:column;gap:3px; }
.al-cred-row { display:inline-flex;align-items:center;gap:5px;font-size:.76rem;color:rgba(255,255,255,.5); }
.al-cred-row i { color:rgba(255,255,255,.25);font-size:.7rem;width:12px;text-align:center;flex-shrink:0; }
.al-cred-val { font-weight:700;color:rgba(255,255,255,.72);font-family:monospace;font-size:.77rem;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;transition:filter .2s; }
.al-cred-none { font-size:.74rem;color:rgba(255,255,255,.2); }
/* Global hide: blur all cred values when body has class */
body.al-creds-hidden .al-cred-val { filter:blur(5px);user-select:none; }

/* default: start hidden */

/* Hide level/BE/RP pills */
.al-row-pills { display:none!important; }

/* ── Hero card ── */
.al-hero {
  border-radius:20px;
  border:1px solid rgba(255,255,255,.07);
  background:#25282a;
  padding:20px 24px;
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;
  margin-bottom:14px;
  box-shadow:0 2px 20px rgba(0,0,0,.22);
}
.al-hero-left { display:flex;align-items:center;gap:14px; }
.al-hero-icon {
  width:44px;height:44px;border-radius:13px;
  background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));
  border:1px solid rgba(109,92,255,.25);
  display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;color:#c4b5fd;flex-shrink:0;
}
.al-hero-title { font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0; }
.al-hero-sub   { font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0; }

/* ── Toolbar card ── */
.al-toolbar-card {
  border-radius:16px;
  border:1px solid rgba(255,255,255,.07);
  background:#25282a;
  padding:12px 16px;
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
  margin-bottom:16px;
  box-shadow:0 2px 16px rgba(0,0,0,.18);
}


/* Compact game filter dropdown */
.al-toolbar-left { display:flex; align-items:center; gap:10px; flex-wrap:wrap; flex:1; min-width:0; }
.al-toolbar-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.al-game-filter { position:relative; min-width:240px; }
.al-game-filter__btn {
  width:100%; min-height:36px; border-radius:12px; border:1px solid rgba(255,255,255,.10);
  background:rgba(255,255,255,.04); color:rgba(255,255,255,.86); padding:7px 12px;
  display:flex; align-items:center; justify-content:space-between; gap:10px; cursor:pointer;
  font-size:.82rem; font-weight:850; transition:border-color .15s, background .15s, box-shadow .15s;
}
.al-game-filter__btn:hover,
.al-game-filter.is-open .al-game-filter__btn { border-color:rgba(109,92,255,.48); background:rgba(109,92,255,.10); box-shadow:0 0 0 3px rgba(109,92,255,.10); }
.al-game-filter__selected { display:flex; align-items:center; gap:8px; min-width:0; }
.al-game-filter__selected img { width:18px; height:18px; object-fit:contain; border-radius:4px; flex:0 0 18px; }
.al-game-filter__selected span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.al-game-filter__panel {
  display:none; position:absolute; left:0; top:calc(100% + 8px); width:min(360px, 90vw); z-index:50;
  padding:10px; border-radius:16px; border:1px solid rgba(255,255,255,.10); background:#2a2d35;
  box-shadow:0 18px 45px rgba(0,0,0,.55);
}
.al-game-filter.is-open .al-game-filter__panel { display:block; }
.al-game-filter__search { position:relative; margin-bottom:8px; }
.al-game-filter__search i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,.34); font-size:.75rem; }
.al-game-filter__search input {
  width:100%; border-radius:11px; border:1px solid rgba(255,255,255,.09); background:rgba(255,255,255,.05);
  color:#fff; padding:8px 10px 8px 32px; font-size:.82rem; outline:none;
}
.al-game-filter__search input:focus { border-color:rgba(109,92,255,.50); box-shadow:0 0 0 3px rgba(109,92,255,.10); }
.al-game-filter__list { max-height:300px; overflow-y:auto; padding-right:3px; display:flex; flex-direction:column; gap:4px; }
.al-game-option {
  width:100%; border:0; border-radius:11px; background:transparent; color:rgba(255,255,255,.78);
  display:flex; align-items:center; gap:9px; padding:8px 9px; cursor:pointer; text-align:left;
  font-size:.82rem; font-weight:800; transition:background .12s, color .12s;
}
.al-game-option:hover { background:rgba(255,255,255,.06); color:#fff; }
.al-game-option.active { background:rgba(109,92,255,.18); color:#c4b5fd; }
.al-game-option img { width:18px; height:18px; object-fit:contain; border-radius:4px; flex:0 0 18px; }
.al-game-option span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.al-game-option__icon-placeholder { width:18px; height:18px; border-radius:4px; display:inline-flex; align-items:center; justify-content:center; background:rgba(109,92,255,.16); color:#c4b5fd; font-size:.7rem; flex:0 0 18px; }
@media (max-width: 768px) {
  .al-toolbar-card { align-items:stretch; }
  .al-toolbar-left, .al-toolbar-actions { width:100%; }
  .al-game-filter, .al-search-wrap, .al-search-wrap input { width:100%; min-width:0; }
}
</style>

<div class="al-page">

  <!-- Hero Card -->
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-duotone fa-store"></i></div>
      <div>
        <h2 class="al-hero-title">My Accounts</h2>
        <p class="al-hero-sub"><?= count($accounts ?? []) ?> account<?= count($accounts ?? []) !== 1 ? 's' : '' ?> total</p>
      </div>
    </div>
    <button type="button" class="al-add-btn" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasAddAccount" aria-controls="offcanvasAddAccount">
      <i class="fa-solid fa-plus"></i> Add Account
    </button>
  </div>

  <!-- Toolbar Card -->
  <div class="al-toolbar-card">
    <div class="al-toolbar-left">
      <div class="al-pills">
        <span class="al-pill active" data-status="all">All</span>
        <span class="al-pill" data-status="Active"><i class="fa-solid fa-circle" style="font-size:.45rem;"></i> Active</span>
        <span class="al-pill" data-status="Unlisted">Unlisted</span>
        <span class="al-pill" data-status="Sold"><i class="fa-solid fa-check"></i> Sold</span>
      </div>
      <div class="al-game-filter" id="alGameFilter">
        <button type="button" class="al-game-filter__btn" id="alGameFilterBtn">
          <span class="al-game-filter__selected" id="alGameFilterSelected"><span class="al-game-option__icon-placeholder"><i class="fa-solid fa-gamepad"></i></span><span>All Games</span></span>
          <i class="fa-solid fa-caret-down"></i>
        </button>
        <div class="al-game-filter__panel" id="alGameFilterPanel">
          <div class="al-game-filter__search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="alGameFilterSearch" placeholder="Search game…"></div>
          <div class="al-game-filter__list" id="alGameFilterList">
            <button type="button" class="al-game-option active" data-game="all" data-label="All Games" data-icon=""><span class="al-game-option__icon-placeholder"><i class="fa-solid fa-gamepad"></i></span><span>All Games</span></button>
            <?php
            $_slugToShort = ['league-of-legends'=>'lol','valorant'=>'val','teamfight-tactics'=>'tft'];
            $_iconMap     = ['lol'=>'league-of-legends.png','val'=>'valorant.png','tft'=>'teamfight-tactics.png'];
            $_allowedRaw3   = trim((string)($seller_data['allowed_games'] ?? ''));
            $_allowedSlugs3 = $_allowedRaw3 !== '' ? array_filter(array_map('trim', explode(',', $_allowedRaw3))) : [];
            $_pillGames     = util_get_all_games(true);
            if (!empty($_allowedSlugs3)) {
                $_pillGames = array_values(array_filter($_pillGames, function($g) use ($_allowedSlugs3) {
                    return in_array($g['slug'], $_allowedSlugs3, true);
                }));
            }
            foreach ($_pillGames as $_fg):
                $_fgs  = $_slugToShort[$_fg['slug']] ?? $_fg['slug'];
                $_fIcon = !empty($_fg['icon']) ? $_fg['icon']
                        : ASSET_URL . '/website/images/icons/' . ($_iconMap[$_fgs] ?? $_fg['slug'] . '.png');
                $_fName = (string)($_fg['name'] ?? $_fgs);
            ?>
            <button type="button" class="al-game-option" data-game="<?= htmlspecialchars($_fgs) ?>" data-label="<?= htmlspecialchars($_fName) ?>" data-icon="<?= htmlspecialchars($_fIcon) ?>">
              <img src="<?= htmlspecialchars($_fIcon) ?>" alt="">
              <span><?= htmlspecialchars($_fName) ?></span>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <!-- Bulk delete button (hidden until rows selected) -->
      <button type="button" id="alBulkDeleteBtn"
              style="display:none;align-items:center;gap:.4rem;padding:6px 14px;border-radius:10px;background:rgba(251,113,133,.14);border:1px solid rgba(251,113,133,.28);color:#fb7185;font-size:.8rem;font-weight:800;cursor:pointer;transition:background .12s;">
        <i class="fa-duotone fa-trash"></i>
        Delete selected (<span id="alBulkCount">0</span>)
      </button>
    </div>
    <div class="al-toolbar-actions">
      <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="search" id="alSearch" placeholder="Search accounts…">
      </div>
    </div>
  </div>

  <!-- List Table -->
  <div class="al-table-wrap" id="alTableWrap">
    <table class="al-table" id="alGrid">
      <thead>
        <tr>
          <th style="width:36px;padding:10px 8px;">
            <input type="checkbox" id="alChkAll" class="al-chk"
                   aria-label="Select all">
          </th>
          <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Account</th>
          <th class="sortable" data-col="price">Price <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="earnings">Earnings <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Status</th>
          <th>
            <span style="display:inline-flex;align-items:center;gap:6px;">
              Credentials
              <button id="alCredsToggleAll" onclick="event.stopPropagation();(function(btn){var masked=document.body.classList.toggle('al-creds-hidden');btn.querySelector('i').className=masked?'fa-solid fa-eye':'fa-solid fa-eye-slash';})(this)" style="width:18px;height:18px;border-radius:5px;border:none;background:rgba(255,255,255,.08);color:rgba(255,255,255,.4);font-size:.62rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;" title="Show/hide all credentials">
                <i class="fa-solid fa-eye-slash"></i>
              </button>
            </span>
          </th>
          <th>Buyer</th>
          <th class="sortable" data-col="date">Created <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody id="alTbody">
        <?php if (!empty($accounts)): foreach ($accounts as $acc):
          $soldState  = (int)($acc['sold'] ?? 0);
          $sold       = $soldState === 1;
          $refunded   = $soldState === 2;
          $active     = (int)($acc['active'] ?? 1) === 1;
          $priceRaw   = (float)($acc['price'] ?? 0);
          $earningsPct = 1 - ($effective_fee / 100);
          $earningsRaw = $priceRaw * $earningsPct;
          $status     = $refunded ? 'Refunded' : ($sold ? 'Sold' : ($active ? 'Active' : 'Unlisted'));
          $badgeCls   = $refunded ? 'al-badge--unlisted' : ($sold ? 'al-badge--sold' : ($active ? 'al-badge--active' : 'al-badge--unlisted'));
          $createdAtRaw = $acc['created_at'] ?? '';
          $createdAtTs  = $createdAtRaw ? strtotime((string)$createdAtRaw) : false;
          $soldAtTs  = !empty($acc['sold_at']) ? strtotime((string)$acc['sold_at']) : 0;
          $soldAtFmt = $soldAtTs ? date('d.m.Y', $soldAtTs) : '—';
          if ($createdAtTs) {
            $diffSec = time() - $createdAtTs;
            $diffDays = (int)floor($diffSec / 86400);
            if ($diffDays < 1) $createdAtFmt = 'today';
            elseif ($diffDays === 1) $createdAtFmt = '1 day ago';
            elseif ($diffDays < 7) $createdAtFmt = $diffDays . ' days ago';
            elseif ($diffDays < 14) $createdAtFmt = '1 week ago';
            elseif ($diffDays < 30) $createdAtFmt = (int)floor($diffDays/7) . ' weeks ago';
            elseif ($diffDays < 60) $createdAtFmt = '1 month ago';
            else $createdAtFmt = (int)floor($diffDays/30) . ' months ago';
          } else {
            $createdAtFmt = (string)$createdAtRaw;
          }
          $_accGameRaw = strtolower(trim((string)($acc['game'] ?? 'lol')));
          $_s2s = [
              'league-of-legends'=>'lol','leagu'=>'lol','leag'=>'lol',
              'valorant'=>'val','valor'=>'val','valo'=>'val',
              'teamfight-tactics'=>'tft','teamf'=>'tft','teamfi'=>'tft',
              'call-of-duty'=>'cod','callofduty'=>'cod',
              'pokemon-go'=>'pokemon','pokemongo'=>'pokemon','pokemon'=>'pokemon',
              'roblox'=>'roblox',
          ];
          $accGame = $_s2s[$_accGameRaw] ?? $_accGameRaw;
          $accGameSlug = function_exists('util_account_normalize_game_slug') ? util_account_normalize_game_slug($_accGameRaw) : $_accGameRaw;
          $gameDataArr = json_decode((string)($acc['game_data'] ?? '{}'), true);
          if (!is_array($gameDataArr)) $gameDataArr = [];
          $isRobloxAccount = in_array($accGameSlug, ['roblox'], true) || $accGame === 'roblox' || $_accGameRaw === 'roblox';
          $robloxExperienceName = $isRobloxAccount && function_exists('seller_account_roblox_experience_name') ? seller_account_roblox_experience_name($gameDataArr) : '';
          $robloxExperienceIcon = $robloxExperienceName !== '' && function_exists('seller_account_roblox_experience_icon_url') ? seller_account_roblox_experience_icon_url($robloxExperienceName) : '';

          static $__alGameMeta = null;
          if ($__alGameMeta === null) {
              $__alGameMeta = [];
              try {
                  foreach ((function_exists('util_get_all_games') ? util_get_all_games(true) : []) as $__g) {
                      $__slug = strtolower((string)($__g['slug'] ?? ''));
                      if ($__slug === '') continue;
                      $__short = function_exists('util_account_short_game_code') ? util_account_short_game_code($__slug) : $__slug;
                      $__icon = !empty($__g['icon']) ? $__g['icon'] : (rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $__slug . '.png');
                      $__name = (string)($__g['name'] ?? $__slug);
                      $__alGameMeta[$__slug] = ['name' => $__name, 'icon' => $__icon];
                      $__alGameMeta[$__short] = ['name' => $__name, 'icon' => $__icon];
                  }
              } catch (Throwable $e) { $__alGameMeta = []; }
          }
          $accGameName = $__alGameMeta[$accGameSlug]['name'] ?? $__alGameMeta[$accGame]['name'] ?? ucwords(str_replace('-', ' ', $accGameSlug));
          $accGameIcon = $__alGameMeta[$accGameSlug]['icon'] ?? $__alGameMeta[$accGame]['icon'] ?? '';

          $accRank   = (int)($acc['rank'] ?? $acc['current_rank'] ?? 0);
          $rankLabel = 'Unranked';
          $rankImgSrc = '';
          if ($accGame === 'val' || in_array($_accGameRaw, ['valorant','valor'], true)) {
              $rankLabel = function_exists('util_get_val_rank')
                  ? util_get_val_rank($accRank)
                  : ($acc['rank_label'] ?? 'Unranked');
              $rankImgSrc = '/public/assets/core/main/img/val/ranks/mini/' . ((int)$accRank) . '.png';
          } elseif ($accGame === 'lol' || in_array($_accGameRaw, ['league-of-legends','leagu','leag'], true)) {
              $rankLabel = util_get_lol_rank($acc['current_rank'] ?? $accRank);
              if (($acc['current_rank'] ?? 0) > 0 && ($acc['current_rank'] ?? 0) < 8) $rankLabel .= ' '.util_format_lol_division($acc['current_division'] ?? 0);
              elseif (($acc['current_rank'] ?? 0) >= 8 && !empty($acc['current_lp'])) $rankLabel .= ' '.(int)$acc['current_lp'].'LP';
              $rankImgSrc = util_rank_img('lol', 'mini', $acc['current_rank'] ?? 0);
          }

          $schema = function_exists('util_get_game_account_schema') ? util_get_game_account_schema($accGameSlug) : [];
          $headlineFieldKey = (string)($schema['headline_icon_field'] ?? '');
          $headlineField = null;
          foreach (($schema['fields'] ?? []) as $__field) {
              if (($__field['key'] ?? '') === $headlineFieldKey) { $headlineField = $__field; break; }
          }
          $headlineValue = ($headlineField && function_exists('util_account_schema_value')) ? util_account_schema_value($acc, $gameDataArr, $headlineField) : null;
          $accountMediaHtml = '';
          if ($headlineField && (($headlineField['icon_type'] ?? '') === 'platform') && function_exists('util_account_platform_icons_html')) {
              $platformIcons = util_account_platform_icons_html($headlineValue, 'al-acc-platform-icon');
              if ($platformIcons !== '') $accountMediaHtml = '<div class="al-acc-platform-box">' . $platformIcons . '</div>';
          }
          if ($isRobloxAccount && $robloxExperienceIcon !== '') {
              $accountMediaHtml = '<img class="al-acc-img" src="' . htmlspecialchars($robloxExperienceIcon, ENT_QUOTES, 'UTF-8') . '" alt="">';
          }
          if ($accountMediaHtml === '' && $rankImgSrc !== '') {
              $accountMediaHtml = '<img class="al-acc-img" src="' . htmlspecialchars($rankImgSrc, ENT_QUOTES, 'UTF-8') . '" alt="">';
          } elseif ($accountMediaHtml === '') {
              $fallbackIcon = $accGameIcon !== '' ? $accGameIcon : (rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $accGameSlug . '.png');
              $accountMediaHtml = '<img class="al-acc-img" src="' . htmlspecialchars($fallbackIcon, ENT_QUOTES, 'UTF-8') . '" alt="">';
          }

          $accountTitle = '';
          // LoL and Valorant keep their own rank/server headline below. Their schemas use
          // title_field "rank", which resolves against the numeric rank column and would
          // render a bare "0" for every unranked account instead of a readable label.
          $useSchemaTitle = !in_array($accGame, ['lol', 'val'], true);
          if ($useSchemaTitle && !empty($schema) && !empty($schema['title_field'])) {
              foreach (($schema['fields'] ?? []) as $__field) {
                  if (($__field['key'] ?? '') === $schema['title_field']) {
                      $accountTitle = function_exists('util_account_format_schema_value') ? util_account_format_schema_value(util_account_schema_value($acc, $gameDataArr, $__field)) : (string)($gameDataArr[$schema['title_field']] ?? '');
                      break;
                  }
              }
              // A rank-like field that resolved to a raw number carries no meaning in the list.
              if (is_numeric($accountTitle) && (float)$accountTitle <= 0) $accountTitle = '';
          }
          if ($isRobloxAccount && $robloxExperienceName !== '') {
              $accountTitle = $robloxExperienceName;
          }
          if ($accountTitle === '') {
              $accountTitle = ($accGame === 'lol' || $accGame === 'val')
                  ? trim(strtoupper((string)($acc['server'] ?? '')) . ' — ' . $rankLabel, ' —')
                  : (string)($acc['title'] ?? $accGameName);
          }
          $accountDisplayName = $isRobloxAccount && $robloxExperienceName !== ''
              ? $robloxExperienceName
              : (($accGame === 'lol' || $accGame === 'val') ? $accountTitle : trim($accGameName . ' — ' . $accountTitle, ' —'));
        ?>
        <tr class="al-row"
            data-status="<?= htmlspecialchars($status) ?>"
            data-search="<?= htmlspecialchars(strtolower(($acc['title'] ?? '') . ' ' . ($acc['server'] ?? '') . ' ' . ($acc['buyer_username'] ?? '') . ' ' . ($robloxExperienceName ?? ''))) ?>"
            data-price="<?= $priceRaw ?>"
            data-earnings="<?= $earningsRaw ?>"
            data-date="<?= $createdAtTs ? $createdAtTs : 0 ?>"
            data-id="<?= (int)$acc['id'] ?>"
            data-game="<?= htmlspecialchars($accGame) ?>">
          <td style="padding:10px 8px;vertical-align:middle;" onclick="event.stopPropagation()">
            <input type="checkbox" class="al-row-chk al-chk"
                   value="<?= (int)$acc['id'] ?>"
                   <?= $sold ? 'disabled title="Sold accounts cannot be deleted"' : '' ?>>
          </td>
          <td class="al-row-link" onclick="window.location='<?= BASE_URL ?>/seller-area/account/<?= (int)$acc['id'] ?>'"><span class="al-col-id">#<?= (int)$acc['id'] ?></span></td>
          <td class="al-row-link" onclick="window.location='<?= BASE_URL ?>/seller-area/account/<?= (int)$acc['id'] ?>'">
            <div class="al-acc-wrap">
              <div class="al-acc-media">
                <?= $accountMediaHtml ?>
                <?php if (!empty($accGameIcon)): ?><img class="al-game-corner-icon" src="<?= htmlspecialchars($accGameIcon) ?>" alt=""><?php endif ?>
              </div>
              <div style="min-width:0;">
                <div class="al-acc-title-line">
                  <div class="al-acc-name" title="<?= htmlspecialchars($accountDisplayName) ?>"><?= htmlspecialchars($accountDisplayName) ?></div>
                </div>
                <?php if (!empty($acc['title']) && (string)$acc['title'] !== (string)$accountTitle): ?>
                  <div class="al-acc-sub" style="max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($acc['title']) ?>"><?= htmlspecialchars($acc['title']) ?></div>
                <?php endif ?>
                <div class="al-row-pills">
                  <?php if (!empty($acc['level'])): ?><span class="al-mini-pill"><i class="fa-solid fa-bolt" style="color:#fbbf24;"></i> Lvl <?= (int)$acc['level'] ?></span><?php endif ?>
                  <?php if (!empty($acc['blue_essence'])): ?><span class="al-mini-pill"><i class="fa-solid fa-gem" style="color:#60a5fa;"></i> <?= number_format((int)$acc['blue_essence']) ?> BE</span><?php endif ?>
                  <?php if (!empty($acc['riot_points'])): ?><span class="al-mini-pill"><i class="fa-solid fa-coins" style="color:#a78fff;"></i> <?= number_format((int)$acc['riot_points']) ?> RP</span><?php endif ?>
                  <?php
                    // Show champion/skin count pills when no manual list was selected
                    $hasManualChamps = !empty($acc['champions']);
                    $hasManualSkins  = !empty($acc['skins']);
                    $champCount      = isset($acc['champion_count']) && $acc['champion_count'] !== null && $acc['champion_count'] !== '' ? (int)$acc['champion_count'] : null;
                    $skinCount       = isset($acc['skin_count'])     && $acc['skin_count']     !== null && $acc['skin_count']     !== '' ? (int)$acc['skin_count']     : null;
                  ?>
                  <?php if (!$hasManualChamps && $champCount !== null): ?><span class="al-mini-pill"><i class="fa-solid fa-shield-halved" style="color:#34d399;"></i> <?= $champCount ?> Champs</span><?php endif ?>
                  <?php if (!$hasManualSkins  && $skinCount  !== null): ?><span class="al-mini-pill"><i class="fa-solid fa-palette" style="color:#f472b6;"></i> <?= $skinCount ?> Skins</span><?php endif ?>
                  <?php if ($accGame === 'val'):
                    // Valorant: count agents from game_data, fall back to val_agent_count
                    $valAgentsList = isset($gameDataArr['agents']) && is_array($gameDataArr['agents'])
                        ? array_filter($gameDataArr['agents'], fn($v) => $v !== null && $v !== '')
                        : [];
                    $valAgentCount = count($valAgentsList) > 0
                        ? count($valAgentsList)
                        : (isset($acc['val_agent_count']) && $acc['val_agent_count'] !== null && $acc['val_agent_count'] !== '' ? (int)$acc['val_agent_count'] : null);
                  ?>
                  <?php if ($valAgentCount !== null): ?><span class="al-mini-pill"><i class="fa-solid fa-crosshairs" style="color:#fb923c;"></i> <?= $valAgentCount ?> Agents</span><?php endif ?>
                  <?php endif ?>
                </div>
              </div>
            </div>
          </td>
          <td><span class="al-col-price">€<?= util_format_price_display($acc['price']) ?></span></td>
          <td><span class="al-col-earnings">€<?= util_format_price_display((int)round($earningsRaw)) ?></span></td>
          <td>
            <span class="al-badge <?= $badgeCls ?>">
              <?php if ($sold): ?><i class="fa-solid fa-check"></i><?php elseif($active): ?><i class="fa-solid fa-circle" style="font-size:.4rem;"></i><?php else: ?><i class="fa-solid fa-eye-slash"></i><?php endif ?>
              <?= $status ?>
            </span>
          </td>
          <td>
            <?php
require_once dirname(__DIR__) . '/_seller_rank.php';
              $hasUser = !empty($acc['login']);
              $hasPass = !empty($acc['password']);
            ?>
            <?php if ($hasUser || $hasPass): ?>
              <div class="al-creds-wrap">
                <?php if ($hasUser): ?>
                  <div class="al-cred-row"><i class="fa-solid fa-user"></i><span class="al-cred-val"><?= htmlspecialchars($acc['login']) ?></span></div>
                <?php endif ?>
                <?php if ($hasPass): ?>
                  <div class="al-cred-row"><i class="fa-solid fa-key"></i><span class="al-cred-val"><?= htmlspecialchars($acc['password']) ?></span></div>
                <?php endif ?>
              </div>
            <?php else: ?>
              <span class="al-cred-none">—</span>
            <?php endif ?>
          </td>
          <td>
            <?php if (!empty($acc['buyer_username'])): ?>
              <div class="al-buyer-wrap">
                <span class="al-buyer-avatar">
                  <?php if (!empty($acc['buyer_icon'])): ?>
                    <img src="<?= htmlspecialchars($acc['buyer_icon']) ?>" alt="">
                  <?php else: ?>
                    <?= strtoupper(substr($acc['buyer_username'],0,1)) ?>
                  <?php endif ?>
                </span>
                <span class="al-buyer-name"><?= htmlspecialchars($acc['buyer_username']) ?></span>
              </div>
            <?php else: ?>
              <span class="al-dash">—</span>
            <?php endif ?>
          </td>
          
          <td><span class="al-col-date"><?= htmlspecialchars($createdAtFmt) ?></span></td>
          <td class="text-end" onclick="event.stopPropagation()">
            <div class="al-actions-wrap">
              <button type="button" class="al-actions-btn" onclick="event.stopPropagation();alToggleMenu(this)" title="Actions">
                <i class="fa-solid fa-ellipsis"></i>
              </button>
              <div class="al-actions-menu">
                <button type="button" class="al-action-item al-action-btn js-edit-account" onclick="return alEditAccount(this, event)" data-acc="<?= htmlspecialchars(json_encode([
                  'id'                 => (int)$acc['id'],
                  'game'               => (function($g) {
                    $m = ['league-of-legends'=>'lol','leagu'=>'lol','valorant'=>'val','valor'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft'];
                    return $m[$g] ?? $g;
                  })($acc['game'] ?? 'lol'),
                  'title'              => $acc['title'] ?? '',
                  'price'              => $acc['price'] ?? 0,
                  'description'        => $acc['description'] ?? '',
                  'current_rank'       => $acc['current_rank'] ?? 0,
                  'current_division'   => $acc['current_division'] ?? 0,
                  'current_lp'         => $acc['current_lp'] ?? 0,
                  'flex_rank'          => $acc['flex_rank'] ?? 0,
                  'flex_division'      => $acc['flex_division'] ?? 0,
                  'flex_lp'            => $acc['flex_lp'] ?? 0,
                  'previous_rank'      => $acc['previous_rank'] ?? 0,
                  'previous_division'  => $acc['previous_division'] ?? 0,
                  'previous_lp'        => $acc['previous_lp'] ?? 0,
                  'rank'               => $acc['rank'] ?? $acc['current_rank'] ?? 0,
                  'rank_label'         => $acc['rank_label'] ?? '',
                  'game_data'          => $acc['game_data'] ?? '{}',
                  'server'             => $acc['server'] ?? '',
                  'level_up_method'    => $acc['level_up_method'] ?? 'by_hand',
                  'level'              => $acc['level'] ?? '',
                  'blue_essence'       => $acc['blue_essence'] ?? '',
                  'riot_points'        => $acc['riot_points'] ?? '',
                  'winrate_percent'    => $acc['winrate_percent'] ?? '',
                  'champions'          => $acc['champions'] ?? '',
                  'skins'              => $acc['skins'] ?? '',
                  'champion_count'     => $acc['champion_count'] ?? '',
                  'skin_count'         => $acc['skin_count'] ?? '',
                  'val_agent_count'    => $acc['val_agent_count'] ?? '',
                  'roles'              => $acc['roles'] ?? '',
                  'login'              => $acc['login'] ?? '',
                  'password'           => $acc['password'] ?? '',
                  'in_game_name'       => $acc['in_game_name'] ?? '',
                  'email'              => $acc['email'] ?? '',
                  'email_password'     => $acc['email_password'] ?? '',
                  'email_verified'     => $acc['email_verified'] ?? 'verified',
                  'has_2fa'            => $acc['has_2fa'] ?? 0,
                  'delivery_type'      => $acc['delivery_type'] ?? 'instant',
                  'delivery_instructions' => $acc['delivery_instructions'] ?? '',
                  'images'             => $acc['images'] ?? '[]',
                ]), ENT_QUOTES, 'UTF-8') ?>">
                  <i class="fa-duotone fa-pen-to-square"></i> Edit Account
                </button>
                <a class="al-action-item" href="<?= BASE_URL ?>/seller-area/account/<?= (int)$acc['id'] ?>">
                  <i class="fa-duotone fa-eye"></i> View Account
                </a>
                <?php if (!$sold): ?>
                <button type="button" class="al-action-item al-action-btn js-duplicate-account" onclick="return alDuplicateAccount(this, event)" data-id="<?= (int)$acc['id'] ?>"
            data-game="<?= htmlspecialchars($accGame) ?>">
                  <i class="fa-duotone fa-copy"></i> Duplicate Account
                </button>
                <?php endif ?>
                <button type="button" class="al-action-item al-action-btn al-action-copy-url" onclick="return alCopyPublicUrl(this, event)"
                  data-url="https://lolboost.gg/lol/account/<?= htmlspecialchars($acc['slug'] ?? '') ?>">
                  <i class="fa-duotone fa-link"></i> Copy Public URL
                </button>
                <?php if (!$sold): ?>
                <button type="button" class="al-action-item al-action-btn js-toggle-list-account" onclick="return alToggleListAccount(this, event)" data-id="<?= (int)$acc['id'] ?>"
                  data-action="<?= $active ? 'seller_mark_unlist' : 'seller_mark_active' ?>">
                  <?php if ($active): ?>
                    <i class="fa-duotone fa-eye-slash"></i> Unlist Account
                  <?php else: ?>
                    <i class="fa-duotone fa-eye"></i> Re-list Account
                  <?php endif ?>
                </button>
                <div class="al-action-divider"></div>
                <button type="button" class="al-action-item al-action-btn al-action-danger js-delete-account" onclick="return alDeleteAccount(this, event)" data-id="<?= (int)$acc['id'] ?>"
            data-game="<?= htmlspecialchars($accGame) ?>">
                  <i class="fa-duotone fa-trash"></i> Delete Account
                </button>
                <?php endif ?>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        <?php if (empty($accounts)): ?>
        <tr><td colspan="10">
          <div class="al-empty">
            <i class="fa-duotone fa-store"></i>
            <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No accounts yet</div>
            <div style="font-size:.85rem;">Click "Add Account" to list your first account.</div>
          </div>
        </td></tr>
        <?php endif ?>
      </tbody>
    </table>
  </div>
  <!-- End List Table -->

  <!-- Footer / Pagination -->
  <div class="al-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
      Showing <span id="alShowing">—</span> of <span id="alTotal">—</span>
    </div>
    <div style="display:flex;gap:5px;flex-wrap:wrap;" id="alPagination"></div>
  </div>

</div>

<!-- Add Account Offcanvas -->
<div class="offcanvas offcanvas-end custom-offcanvas" tabindex="-1" id="offcanvasAddAccount"
    aria-labelledby="offcanvasAddAccountLabel" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="offcanvas-header" style="border-bottom:1px solid var(--bs-card-border-color);padding:18px 22px;">
        <div class="d-flex align-items-center gap-3">
          <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;font-size:.95rem;color:#c4b5fd;">
            <i class="fa-duotone fa-store"></i>
          </div>
          <div>
            <h5 class="offcanvas-title mb-0" id="offcanvasAddAccountLabel" style="font-weight:900;font-size:1rem;">Add Account</h5>
            <div style="font-size:.75rem;color:rgba(255,255,255,.45);margin-top:1px;">List a new account on the marketplace</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Fixed: Earnings bar -->
        <div class="oc-earnings-bar">
          <i class="fa-duotone fa-sack-dollar" style="color:#9f8cff;flex-shrink:0;"></i>
          You earn <strong style="color:#c4b5fd;margin:0 3px;"><?= number_format(100 - $effective_fee, 1) ?>%</strong>&nbsp;·&nbsp;Platform fee: <strong><?= number_format($effective_fee, 1) ?>%</strong>
        </div>

        <!-- Fixed: Custom step indicator -->
        <div class="oc-steps">
          <div class="oc-step active" id="ocStep0">
            <div class="oc-step-num">1</div>
            <div class="oc-step-label">Game</div>
          </div>
          <div class="oc-step-line" id="ocLine0"></div>
          <div class="oc-step" id="ocStep1">
            <div class="oc-step-num">2</div>
            <div class="oc-step-label">Listing Info</div>
          </div>
          <div class="oc-step-line" id="ocLine1"></div>
          <div class="oc-step" id="ocStep2">
            <div class="oc-step-num">3</div>
            <div class="oc-step-label">Game Data</div>
          </div>
          <div class="oc-step-line" id="ocLine2"></div>
          <div class="oc-step" id="ocStep3">
            <div class="oc-step-num">4</div>
            <div class="oc-step-label">Credentials</div>
          </div>
        </div>

        <form class="js-step-form ajax-form" action="<?= AJAX_URL ?>" novalidate data-hs-step-form-options='{
            "progressSelector": "#StepFormProgress",
            "stepsSelector": "#StepFormContent",
            "endSelector": "#createProjectFinishBtn"
        }'>
            <input type="hidden" name="action" value="seller_create_account">
            <input type="hidden" name="game" id="selectedGame" value="<?= htmlspecialchars($__firstGame ?? 'lol') ?>">
            <!-- Hidden original progress (required by HSStepForm) -->
            <ul id="StepFormProgress" style="display:none;">
                <li class="step-item"><a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#StepGame"}'><span class="step-icon step-icon-soft-dark">1</span><div class="step-content"><span class="step-title">Game</span></div></a></li>
                <li class="step-item"><a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#StepListing"}'><span class="step-icon step-icon-soft-dark">2</span><div class="step-content"><span class="step-title">Listing Info</span></div></a></li>
                <li class="step-item"><a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#StepData"}'><span class="step-icon step-icon-soft-dark">3</span><div class="step-content"><span class="step-title">Game Data</span></div></a></li>
                <li class="step-item"><a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#StepCreds"}'><span class="step-icon step-icon-soft-dark">4</span><div class="step-content"><span class="step-title">Credentials</span></div></a></li>
            </ul>

            <!-- Scrollable step content -->
            <div class="oc-scroll" id="ocScrollArea">
            <div id="StepFormContent">

                <!-- Step 0: Game Selection — DB-driven -->
                <div id="StepGame" class="active">
                    <div class="oc-section-label"><i class="fa-solid fa-gamepad"></i> Select Game</div>
                    <div class="oc-game-picker mb-3">
                        <div class="oc-game-picker__top">
                            <div class="oc-game-search">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" id="ocGameSearch" placeholder="Search game..." autocomplete="off">
                            </div>
                            <div class="oc-game-selected-chip" id="ocGameSelectedChip">
                                <span>Choose game</span>
                            </div>
                        </div>
                        <div class="oc-game-list" id="ocGameList">
                        <?php
                        $_allGames2    = util_get_all_games(true);
                        $_allowedRaw2  = trim((string)($seller_data['allowed_games'] ?? ''));
                        $_allowedSlugs2 = $_allowedRaw2 !== '' ? array_filter(array_map('trim', explode(',', $_allowedRaw2))) : [];
                        $_sellerGames  = empty($_allowedSlugs2) ? $_allGames2
                            : array_values(array_filter($_allGames2, function($g) use ($_allowedSlugs2) {
                                return in_array($g['slug'], $_allowedSlugs2, true);
                            }));
                        $_slugToShort = ['league-of-legends'=>'lol','valorant'=>'val','teamfight-tactics'=>'tft'];
                        $_iconMap = ['lol'=>'league-of-legends.png','val'=>'valorant.png','tft'=>'teamfight-tactics.png'];
                        $_first = true;
                        foreach ($_sellerGames as $_sg):
                            $_gs = $_slugToShort[$_sg['slug']] ?? $_sg['slug'];
                            $_schemaSlugForCard = function_exists('util_get_game_account_schema') && !empty(util_get_game_account_schema($_sg['slug'] ?? '')) ? ($_sg['slug'] ?? $_gs) : (preg_match('/(call[- ]?of[- ]?duty|black[- ]?ops|warzone|modern[- ]?warfare|bo[0-9]+)/i', (($_sg['slug'] ?? '') . ' ' . ($_sg['name'] ?? ''))) ? 'call-of-duty' : ($_sg['slug'] ?? $_gs));
                            $_icon = !empty($_sg['icon']) ? $_sg['icon']
                                   : ASSET_URL . '/website/images/icons/' . ($_iconMap[$_gs] ?? $_gs . '.png');
                            $_name = (string)($_sg['name'] ?? $_gs);
                            $_sub  = $_name . ' Accounts';
                            $_searchText = strtolower($_name . ' ' . $_gs . ' ' . ($_sg['slug'] ?? ''));
                        ?>
                            <label class="oc-game-card <?= $_first ? 'selected' : '' ?>"
                                   id="gamecard<?= htmlspecialchars(ucfirst($_gs)) ?>"
                                   data-game="<?= htmlspecialchars($_gs) ?>"
                                   data-schema="<?= htmlspecialchars($_schemaSlugForCard) ?>"
                                   data-name="<?= htmlspecialchars($_name) ?>"
                                   data-icon="<?= htmlspecialchars($_icon) ?>"
                                   data-search="<?= htmlspecialchars($_searchText) ?>"
                                   onclick="selectGame('<?= htmlspecialchars($_gs) ?>')">
                                <input type="radio" name="_game_ui" value="<?= htmlspecialchars($_gs) ?>"
                                       <?= $_first ? 'checked' : '' ?> style="display:none">
                                <div class="oc-game-card__icon">
                                    <img src="<?= htmlspecialchars($_icon) ?>" alt="<?= htmlspecialchars($_name) ?>">
                                </div>
                                <div style="min-width:0;flex:1;">
                                    <div class="oc-game-card__name"><?= htmlspecialchars($_name) ?></div>
                                    <div class="oc-game-card__sub"><?= htmlspecialchars($_sub) ?></div>
                                </div>
                            </label>
                        <?php $_first = false; endforeach; ?>
                        </div>
                        <div class="oc-game-empty" id="ocGameEmpty">No games found.</div>
                    </div>
                </div>

                <!-- Step 1: Listing Info -->
                <div id="StepListing" class="active">
                    <!-- Section: Basic Info -->
                    <div class="oc-section-label"><i class="fa-solid fa-tag"></i> Basic Info</div>
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <label class="form-label">Account Title <span class="oc-required">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="Account title shown to buyers" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Price <span class="oc-required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.5);">€</span>
                                <input type="text" class="form-control" placeholder="0.00" name="price" id="sellerAccountPrice" required>
                                <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.35);font-size:.78rem;">EUR</span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small style="color:rgba(255,255,255,.3);font-size:.74rem;">Platform fee: <?= number_format($effective_fee, 1) ?>%</small>
                                <small id="sellerPriceEarningsHelp" style="font-size:.74rem;color:#4ade80;">You earn: <span class="fw-semibold">€0.00</span></small>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description <span class="oc-required">*</span></label>
                            <textarea class="form-control" rows="3" name="description" placeholder="Describe the account and its most important features" required></textarea>
                        </div>
                    </div>

                    <!-- Section: Gallery -->
                    <div class="oc-section-label"><i class="fa-solid fa-images"></i> Image Gallery</div>
                    <div id="galleryDropzone" class="account-upload-box text-center p-3">
                        <div class="mb-2"><i class="fa-duotone fa-images fa-xl text-primary"></i></div>
                        <h6 class="mb-1" style="font-size:.88rem;font-weight:800;">Upload Images</h6>
                        <p class="text-muted small mb-2" style="font-size:.78rem;">Upload screenshots or images of the account</p>
                        <button type="button" class="btn btn-primary btn-sm" id="selectImagesBtn">Select Images</button>
                        <input class="form-control d-none" name="images[]" type="file" id="galleryUpload" multiple accept="image/*" required>
                        <input type="hidden" name="images_order" id="galleryOrderInput" value="[]">
                    </div>
                    <small class="d-block mt-1" style="color:rgba(255,255,255,.25);font-size:.72rem;">PNG, JPEG, WEBP, GIF · Max 8 MB each</small>
                    <div id="previewGallery" class="row mt-3 g-2"></div>
                </div>

                <!-- Step 2: Game Data -->
                <div id="StepData" style="display: none;">

                    <!-- LoL-specific fields -->
                    <div id="lolGameData">
                        <!-- Section: Rank -->
                        <div class="oc-section-label"><i class="fa-solid fa-trophy"></i> Rank</div>
                        <div class="row g-2 mb-3">
                            <!-- Current Rank & Division -->
                            <div class="col-12">
                                <div class="mb-2">
                                    <label class="form-label">Current Rank & Division <span class="oc-required">*</span></label>
                                    <div class="row g-2">
                                        <div class="col-12 current-rank">
                                            <select class="form-select" name="current_rank"
                                                data-placeholder="Select Current Rank">
                                                <?= util_load_lol_tier_select(0, 10, 0) ?>
                                            </select>
                                        </div>
                                        <div class="col-3 current-division d-none">
                                            <select class="form-select" name="current_division"
                                                data-placeholder="Select Current Division">
                                                <?= util_load_lol_division_select() ?>
                                            </select>
                                        </div>
                                        <div class="col-3 current-lp d-none">
                                            <input type="text" class="form-control" name="current_lp" placeholder="LP">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Flex + Previous in a 2-col row -->
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label" style="color:rgba(255,255,255,.4);">Flex Rank</label>
                                    <div class="row g-2">
                                        <div class="col-12 flex-rank">
                                            <select class="form-select" name="flex_rank" data-placeholder="Select Flex Rank">
                                                <?= util_load_lol_tier_select(0, 10, 0) ?>
                                            </select>
                                        </div>
                                        <div class="col-4 flex-division d-none">
                                            <select class="form-select" name="flex_division" data-placeholder="Division">
                                                <?= util_load_lol_division_select() ?>
                                            </select>
                                        </div>
                                        <div class="col-4 flex-lp d-none">
                                            <input type="text" class="form-control" name="flex_lp" placeholder="LP">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label" style="color:rgba(255,255,255,.4);">Previous Rank</label>
                                    <div class="row g-2">
                                        <div class="col-12 previous-rank">
                                            <select class="form-select" name="previous_rank" data-placeholder="Select Previous Rank">
                                                <?= util_load_lol_tier_select(0, 10, 0) ?>
                                            </select>
                                        </div>
                                        <div class="col-4 previous-division d-none">
                                            <select class="form-select" name="previous_division" data-placeholder="Division">
                                                <?= util_load_lol_division_select() ?>
                                            </select>
                                        </div>
                                        <div class="col-4 previous-lp d-none">
                                            <input type="text" class="form-control" name="previous_lp" placeholder="LP">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Account Details (LoL) -->
                        <div class="oc-section-label"><i class="fa-solid fa-server"></i> Account Details</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Server <span class="oc-required">*</span></label>
                                <select class="form-select" name="server" id="serverLol">
                                    <?= util_load_server_select('euw') ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Level Up Method <span class="oc-required">*</span></label>
                                <select class="form-select" name="level_up_method">
                                    <option value="by_hand">By Hand</option>
                                    <option value="botted">Botted</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Level</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-arrow-up" style="font-size:.7rem;color:rgba(255,255,255,.4);"></i></span>
                                    <input type="number" class="form-control" name="level" placeholder="30">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Win %</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);font-size:.75rem;color:rgba(255,255,255,.4);">%</span>
                                    <input type="number" class="form-control" name="winrate_percent" placeholder="55">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Blue Essence</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-gem" style="font-size:.7rem;color:#60a5fa;"></i></span>
                                    <input type="number" class="form-control" name="blue_essence" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Riot Points</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-coins" style="font-size:.7rem;color:#a78bfa;"></i></span>
                                    <input type="number" class="form-control" name="riot_points" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Valorant-specific fields -->
                    <div id="valGameData" style="display:none;">

                        <div class="oc-section-label"><i class="fa-solid fa-trophy"></i> Rank</div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Rank</label>
                                <select class="form-select" name="val_rank">
                                    <?php foreach ([0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Ascendant',8=>'Immortal',9=>'Radiant'] as $v=>$l): ?>
                                        <option value="<?= $v ?>"><?= $l ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Peak Rank</label>
                                <select class="form-select" name="val_peak_rank">
                                    <?php foreach ([0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Ascendant',8=>'Immortal',9=>'Radiant'] as $v=>$l): ?>
                                        <option value="<?= $v ?>"><?= $l ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>

                        <div class="oc-section-label"><i class="fa-solid fa-server"></i> Account Details</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Platform</label>
                                <select class="form-select" name="val_platform">
                                    <option value="">Select Platform</option>
                                    <option value="PC">PC</option>
                                    <option value="Console">Console</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Server <span class="oc-required">*</span></label>
                                <select class="form-select" name="server" id="serverVal">
                                    <?php foreach ([
                                        'eu'  => 'Europe',
                                        'na'  => 'North America',
                                        'sea' => 'Southeast Asia',
                                        'me'  => 'Middle East',
                                        'vn'  => 'Vietnam',
                                        'ph'  => 'Philippines',
                                        'sg'  => 'Singapore',
                                        'th'  => 'Thailand',
                                        'tw'  => 'Taiwan',
                                    ] as $v=>$l): ?>
                                        <option value="<?= $v ?>" <?= $v === 'eu' ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Level</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-arrow-up" style="font-size:.7rem;color:rgba(255,255,255,.4);"></i></span>
                                    <input type="number" class="form-control" name="level" placeholder="e.g. 120" min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Valorant Points</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-coins" style="font-size:.7rem;color:#a78bfa;"></i></span>
                                    <input type="number" class="form-control" name="val_points" placeholder="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Radianite Points</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-gem" style="font-size:.7rem;color:#60a5fa;"></i></span>
                                    <input type="number" class="form-control" name="val_radianite" placeholder="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Winrate %</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);font-size:.75rem;color:rgba(255,255,255,.4);">%</span>
                                    <input type="number" class="form-control" name="val_winrate" placeholder="55" min="0" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weapon Skins</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-gun" style="font-size:.7rem;color:rgba(255,255,255,.4);"></i></span>
                                    <input type="number" class="form-control" name="val_weapon_skins" placeholder="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ranked Ready</label>
                                <div class="oc-toggle-row" style="margin-top:6px;">
                                    <label class="switch">
                                        <input type="hidden" name="val_ranked_ready" value="0">
                                        <input type="checkbox" role="switch" name="val_ranked_ready" value="1">
                                        <span class="slider"></span>
                                    </label>
                                    <span style="font-size:.82rem;color:rgba(255,255,255,.5);">Account is ranked-ready</span>
                                </div>
                            </div>
                        </div>

                        <div class="oc-section-label"><i class="fa-solid fa-crosshairs"></i> Agents</div>
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <select class="form-select" name="val_agents[]" id="valAgentsSelect" data-placeholder="Select Agents" multiple>
                                    <?= util_load_agents_select() ?>
                                </select>
                                <!-- Count-only fallback: shown only when no agents are manually selected -->
                                <div id="valAgentCountWrap" class="mt-2" style="display:none;">
                                    <label class="form-label mb-1" style="font-size:.78rem;opacity:.65;">Or just enter the total number of agents</label>
                                    <input type="number" class="form-control form-control-sm" name="val_agent_count" id="val_agent_count" placeholder="e.g. 15" min="0" max="999">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Schema-based fields: every DB game can render its own Game Data from game_account_schemas -->
                    <?php
                        $_schemaRendered = [];
                        $_schemaFallbackHtml = '<div class="oc-section-label"><i class="fa-solid fa-sliders"></i> Account Attributes</div><div class="alert alert-dark border-0" style="background:rgba(255,255,255,.04);color:rgba(255,255,255,.65);font-size:.85rem;">No custom upload fields are configured for this game yet. Add fields in the Admin Accounts Builder and enable <strong>Upload Field</strong>.</div>';
                        $_schemaSlugForSellerGame = static function(array $gameRow): string {
                            $slug = strtolower(trim((string)($gameRow['slug'] ?? '')));
                            $name = strtolower(trim((string)($gameRow['name'] ?? '')));
                            if ($slug === '') return '';
                            if (function_exists('util_get_game_account_schema') && !empty(util_get_game_account_schema($slug))) return $slug;
                            if (preg_match('/(call[- ]?of[- ]?duty|black[- ]?ops|warzone|modern[- ]?warfare|bo[0-9]+)/i', $slug . ' ' . $name)) return 'call-of-duty';
                            return $slug;
                        };
                    ?>
                    <?php if (!empty($_sellerGames)): ?>
                        <?php foreach ($_sellerGames as $_schemaGame): ?>
                            <?php
                                $_gameSlug = strtolower(trim((string)($_schemaGame['slug'] ?? '')));
                                $_schemaSlug = $_schemaSlugForSellerGame($_schemaGame);
                                if ($_schemaSlug === '') continue;
                                $_schemaShort = function_exists('util_account_short_game_code') ? util_account_short_game_code($_schemaSlug) : ($_slugToShort[$_schemaSlug] ?? $_schemaSlug);
                                $_gameShort = function_exists('util_account_short_game_code') ? util_account_short_game_code($_gameSlug) : ($_slugToShort[$_gameSlug] ?? $_gameSlug);
                                if (in_array($_schemaShort, ['lol','val'], true)) continue;
                                if (isset($_schemaRendered[$_schemaSlug])) continue;
                                $_schema = function_exists('util_get_game_account_schema') ? util_get_game_account_schema($_schemaSlug) : [];
                                if (empty($_schema) || (isset($_schema['enabled']) && empty($_schema['enabled']))) continue;
                                $_schemaHtml = function_exists('util_render_account_upload_fields') ? util_render_account_upload_fields($_schemaSlug) : '';
                                if (trim($_schemaHtml) === '') $_schemaHtml = $_schemaFallbackHtml;
                                $_schemaAliases = array_values(array_unique(array_filter([$_schemaSlug, $_schemaShort, $_gameSlug, $_gameShort])));
                                $_schemaRendered[$_schemaSlug] = true;
                            ?>
                            <div id="schemaGameData_<?= htmlspecialchars($_schemaShort) ?>" class="schemaGameData" data-schema-game="<?= htmlspecialchars($_schemaSlug) ?>" data-schema-aliases="<?= htmlspecialchars(implode(',', $_schemaAliases)) ?>" style="display:none;">
                                <?= $_schemaHtml ?>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>

                    <!-- LoL-only: Champions & Content -->
                    <div id="lolChampionsContent">
                    <div class="oc-section-label"><i class="fa-solid fa-shield-halved"></i> Champions & Content</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label mb-0">Champions</label>
                                <button type="button" class="oc-tag-btn" id="btnAllChampions">
                                    <i class="fa-solid fa-list-check"></i> All champions
                                </button>
                            </div>
                            <select class="form-select" name="champions[]" id="champions" data-placeholder="Select Champions" multiple>
                                <?= util_load_champions_select() ?>
                            </select>
                            <!-- Count-only fallback: shown only when no champions are manually selected -->
                            <div id="championCountWrap" class="mt-2" style="display:none;">
                                <label class="form-label mb-1" style="font-size:.78rem;opacity:.65;">Or just enter the total number of champions</label>
                                <input type="number" class="form-control form-control-sm" name="champion_count" id="champion_count" placeholder="e.g. 120" min="0" max="999">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Skins</label>
                            <select class="form-select" name="skins[]" id="skins" data-placeholder="Select Skins" multiple>
                                <?= util_get_lol_skins() ?>
                            </select>
                            <!-- Count-only fallback -->
                            <div id="skinCountWrap" class="mt-2" style="display:none;">
                                <label class="form-label mb-1" style="font-size:.78rem;opacity:.65;">Or just enter the total number of skins</label>
                                <input type="number" class="form-control form-control-sm" name="skin_count" id="skin_count" placeholder="e.g. 60" min="0" max="9999">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Roles</label>
                            <select class="form-select" name="roles[]" id="roles" data-placeholder="Select Roles" multiple>
                                <?= util_load_roles_select() ?>
                            </select>
                        </div>
                    </div>
                    </div>

                <!-- Generic game data — shown for all games except lol/val -->
                <div id="genericGameData" style="display:none;">
                    <div class="oc-section-label"><i class="fa-solid fa-trophy"></i> Rank</div>
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <label class="form-label">Rank / Rating</label>
                            <input type="text" class="form-control" name="generic_rank" placeholder="e.g. Diamond, 2500 MMR">
                        </div>
                    </div>
                    <div class="oc-section-label"><i class="fa-solid fa-server"></i> Account Details</div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Server / Region</label>
                            <input type="text" class="form-control" name="generic_server" placeholder="e.g. EU, NA, Global">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Level</label>
                            <input type="number" class="form-control" name="level" placeholder="e.g. 100">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Details</label>
                            <textarea class="form-control" name="generic_details" rows="3"
                                      placeholder="Skins, currency, special items..."></textarea>
                        </div>
                    </div>
                </div>
                </div>

                                <!-- Step 3: Credentials -->
                <div id="StepCreds" style="display: none;">
                    <!-- Delivery Type -->
                    <div class="oc-section-label"><i class="fa-solid fa-bolt"></i> Delivery Method</div>
                    <div class="oc-delivery-toggle mb-3">
                        <input type="radio" id="instant" name="delivery_type" value="instant" checked>
                        <label for="instant" class="oc-delivery-opt">
                            <span class="oc-delivery-icon"><i class="fa-solid fa-bolt"></i></span>
                            <span class="oc-delivery-text">
                                <span class="oc-delivery-title">Instant Delivery</span>
                                <span class="oc-delivery-sub">Buyer gets access immediately</span>
                            </span>
                        </label>
                        <input type="radio" id="manual" name="delivery_type" value="manual" disabled>
                        <label for="manual" class="oc-delivery-opt oc-delivery-opt--disabled" title="Coming soon">
                            <span class="oc-delivery-icon"><i class="fa-solid fa-truck"></i></span>
                            <span class="oc-delivery-text">
                                <span class="oc-delivery-title">Manual Delivery <span class="oc-soon-badge">Soon</span></span>
                                <span class="oc-delivery-sub">You send credentials manually</span>
                            </span>
                        </label>
                    </div>

                    <!-- Login Details -->
                    <div class="col-12 login-details">
                        <!-- Section: Account Access with global reveal toggle -->
                        <div class="oc-section-label" style="justify-content:space-between;">
                            <span><i class="fa-solid fa-lock"></i> Account Access</span>
                        </div>
                        <div class="row g-2 mb-3" id="ocCredsBlock">
                            <div class="col-md-6">
                                <label class="form-label">Login <span class="oc-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-user" style="font-size:.7rem;color:rgba(255,255,255,.35);"></i></span>
                                    <input type="text" class="form-control oc-sensitive" name="login" placeholder="Login username" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="oc-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-solid fa-key" style="font-size:.7rem;color:rgba(255,255,255,.35);"></i></span>
                                    <input type="text" class="form-control oc-sensitive" name="password" placeholder="Login password" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">In-Game Name <span class="oc-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);"><i class="fa-brands fa-battle-net" style="font-size:.7rem;color:rgba(255,255,255,.35);"></i></span>
                                    <input type="text" class="form-control oc-sensitive" name="in_game_name" placeholder="In game name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Has 2FA</label>
                                <div class="oc-toggle-row">
                                    <label class="switch">
                                        <input type="hidden" name="has_2fa" value="0">
                                        <input type="checkbox" role="switch" id="has_2fa" name="has_2fa" value="1">
                                        <span class="slider"></span>
                                    </label>
                                    <span style="font-size:.82rem;color:rgba(255,255,255,.5);">Two-factor authentication enabled</span>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Email -->
                        <div class="oc-section-label"><i class="fa-solid fa-envelope"></i> Email Details</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Verification Status</label>
                                <select class="form-select" id="add_email_verified" name="email_verified" onchange="toggleAddEmailFields(this.value)">
                                    <option value="verified">✓ Verified</option>
                                    <option value="unverified">✗ Unverified</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="add_email_wrap">
                                <label class="form-label">Account Email <span class="oc-required">*</span></label>
                                <input type="text" class="form-control oc-sensitive" name="email" id="add_email" placeholder="Email address" required>
                            </div>
                            <div class="col-md-4" id="add_email_password_wrap">
                                <label class="form-label">Email Password <span class="oc-required">*</span></label>
                                <input type="text" class="form-control oc-sensitive" name="email_password" id="add_email_password" placeholder="Email account password" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Delivery Instructions -->
                    <div class="oc-section-label"><i class="fa-solid fa-note-sticky"></i> Delivery Instructions</div>
                    <textarea class="form-control" rows="3" name="delivery_instructions"
                        placeholder="Optional instructions for the buyer after purchase"></textarea>
                    <div class="oc-hint">These instructions are shown to the buyer after purchase. Leave blank if not needed.</div>
                </div>

            </div><!-- /StepFormContent -->
            </div><!-- /oc-scroll -->

            <!-- Fixed footer -->
            <div class="oc-footer">
                <button type="button" class="oc-btn-prev" id="ocPrevBtn" style="display:none;">
                    <i class="fa-solid fa-arrow-left"></i> Previous
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="oc-btn-next" id="ocNextBtn">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button type="button" class="oc-btn-next" id="ocSubmitBtn" style="display:none;">
                        <i class="fa-duotone fa-cloud-arrow-up me-1"></i> List Account
                    </button>
                    <button type="button" class="btn btn-primary create-account d-none" id="createProjectFinishBtn">
                        <i class="fa-duotone fa-cloud-arrow-up me-2"></i> List Account for Sale
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Add Account Offcanvas -->


<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-step-form/dist/hs-step-form.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
    $(document).on('ready', function () {
        window.addEventListener('error', function(e){ console.error('Global JS error:', e.message, e.filename+':'+e.lineno); });

        // Pretty validation
        function showValidationErrors($form) {
            if (!$form || !$form.length || !$form[0]) return;
            $form.addClass('was-validated');
            const $activeStep = $form.find('#StepFormContent > .active');
            const $scope = $activeStep.length ? $activeStep : $form;
            const invalidEls = $scope.find(':input').toArray().filter(el => {
                const $el = $(el);
                if ($el.attr('type') === 'hidden') return false;
                if (!$el.is(':visible')) return false;
                return el.willValidate && !el.checkValidity();
            });
            const items = invalidEls.map(el => {
                const $el = $(el);
                const id = $el.attr('id');
                let label = '';
                if (id) label = $form.find('label[for="' + id + '"]').first().text().trim();
                if (!label) label = $el.closest('.mb-3, .form-group, .col-md-6, .col-12').find('label.form-label').first().text().trim();
                if (!label) label = ($el.attr('name') || 'Field').replace(/\[\]$/,'');
                return label;
            }).filter((v,i,a) => v && a.indexOf(v)===i);
            let $alert = $form.find('.js-validation-alert');
            if (!$alert.length) {
                $alert = $('<div class="alert alert-danger js-validation-alert mb-3" role="alert"></div>');
                const $content = $form.find('#StepFormContent');
                ($content.length ? $content : $form).before($alert);
            }
            const listHtml = items.length
                ? '<div class="fw-semibold mb-1">Please complete the following fields:</div><ul class="mb-0 ps-3"><li>' + items.join('</li><li>') + '</li></ul>'
                : '<div class="fw-semibold">Please fill out all required fields.</div>';
            $alert.html(listHtml).removeClass('d-none');
            invalidEls.forEach(el => {
                const $el = $(el);
                if (el.tagName === 'SELECT') {
                    const $ts = $el.next('.ts-wrapper');
                    if ($ts.length) $ts.addClass('is-invalid');
                }
                if ($el.is(':checkbox,:radio')) $el.closest('.form-check').addClass('is-invalid');
            });
            if (invalidEls.length) {
                const first = invalidEls[0];
                const $first = $(first);
                if (first.tagName === 'SELECT') {
                    const $ts = $first.next('.ts-wrapper');
                    const $input = $ts.find('input').first();
                    if ($input.length) $input.trigger('focus');
                } else {
                    $first.trigger('focus');
                }
            }
            if (typeof create_toast === 'function') create_toast('danger', 'Missing information', 'Please check the highlighted fields in the form.');
        }

        $(document).on('input change', '.js-step-form :input', function () {
            const $el = $(this);
            const $form = $el.closest('.js-step-form');
            $el.removeClass('is-invalid');
            if (this.tagName === 'SELECT') $el.next('.ts-wrapper').removeClass('is-invalid');
            $el.closest('.form-check').removeClass('is-invalid');
            $form.find('.js-validation-alert').addClass('d-none');
        });

        // DataTable
        HSCore.components.HSDatatables.init($('#accounts_table'), {
            language: {
                zeroRecords: `<div class="text-center p-4">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width: 10rem;" data-hs-theme-appearance="default">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width: 10rem;" data-hs-theme-appearance="dark">
            <p class="mb-0">No accounts listed yet</p>
            </div>`
            }
        });

        const dtAccounts = $('#accounts_table').DataTable();

        // Status filter pills
        $(document).on('click', '.js-status-pill', function (e) {
            e.preventDefault();
            const status = $(this).data('status');
            $('#accountsStatusPills .js-status-pill').removeClass('active');
            $(this).addClass('active');
            if (!status || status === 'all') {
                dtAccounts.column(4).search('').draw();
                return;
            }
            dtAccounts.column(4).search('^' + status + '$', true, false).draw();
        });

        // TomSelect smart dropup
        function enableSmartDropup(selector) {
            const el = document.querySelector(selector);
            if (!el || !el.tomselect) return;
            const ts = el.tomselect;
            const wrapper = ts.wrapper;
            const update = () => {
                const rect = wrapper.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                wrapper.classList.toggle('ts-dropup', spaceBelow < 320);
            };
            ts.on('dropdown_open', update);
            ts.on('dropdown_close', () => wrapper.classList.remove('ts-dropup'));
            window.addEventListener('resize', update);
            window.addEventListener('scroll', update, true);
        }

        HSCore.components.HSTomSelect.init('select:not(#champions):not(#skins):not(#roles)', {
            maxOptions: null,
            shouldLoad: function () { return true; },
            loadThrottle: 0,
            firstUrl: null,
            hideSelected: true,
        });

        HSCore.components.HSTomSelect.init('#champions', {
            maxOptions: null,
            shouldLoad: function () { return true; },
            loadThrottle: 0,
            firstUrl: null,
            hideSelected: true,
            onItemAdd: function () {
                try { this.setTextboxValue(''); this.refreshOptions(false); } catch (e) {}
            },
            onInitialize: function () {
                let select = this;
                this.options = {};
                document.querySelectorAll("#champions option").forEach(function (option) {
                    select.addOption({ value: option.value, text: option.textContent, img: option.getAttribute("data-image") });
                });
            },
            render: {
                option: function (data, escape) {
                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: 30px; height: 30px; border-radius: 5px; margin-right: 8px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
                },
                item: function (data, escape) {
                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: 20px; height: 20px; border-radius: 5px; margin-right: 5px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
                }
            }
        });

        // "All champions" shortcut
        (function () {
            const btn = document.getElementById('btnAllChampions');
            const sel = document.getElementById('champions');
            if (!btn || !sel) return;
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const ts = sel.tomselect;
                if (!ts) return;
                ts.setValue(Object.keys(ts.options || []), true);
                try { ts.setTextboxValue(''); ts.refreshOptions(false); } catch (err) {}
            });
        })();

        HSCore.components.HSTomSelect.init('#skins', {
            maxOptions: null,
            shouldLoad: function () { return true; },
            loadThrottle: 0,
            firstUrl: null,
            hideSelected: true,
            onItemAdd: function () {
                try { this.setTextboxValue(''); this.refreshOptions(false); } catch (e) {}
            },
            onInitialize: function () {
                let select = this;
                this.options = {};
                document.querySelectorAll("#skins option").forEach(function (option) {
                    select.addOption({ value: option.value, text: option.textContent, img: option.getAttribute("data-image") });
                });
            },
            render: {
                option: function (data, escape) {
                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: auto; height: 30px; border-radius: 5px; margin-right: 8px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
                },
                item: function (data, escape) {
                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: auto; height: 20px; border-radius: 5px; margin-right: 5px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
                }
            }
        });

        HSCore.components.HSTomSelect.init('#roles', {
            maxOptions: null,
            shouldLoad: function () { return true; },
            loadThrottle: 0,
            firstUrl: null,
            hideSelected: true,
            onItemAdd: function () {
                try { this.setTextboxValue(''); this.refreshOptions(false); } catch (e) {}
            }
        });

        enableSmartDropup('#champions');
        enableSmartDropup('#skins');
        enableSmartDropup('#roles');

        // Count-only fields: show when no manual selection, hide when items are chosen
        (function () {
            function initCountToggle(selId, wrapId, countId) {
                var selEl = document.querySelector('[name="' + selId + '[]"]');
                var wrap  = document.getElementById(wrapId);
                var countEl = document.getElementById(countId);
                if (!selEl || !wrap) return;
                function update() {
                    var ts = selEl.tomselect;
                    var hasVals = ts ? ts.getValue().length > 0 : (selEl.selectedOptions && selEl.selectedOptions.length > 0);
                    wrap.style.display = hasVals ? 'none' : '';
                    if (hasVals && countEl) countEl.value = '';
                }
                update();
                if (selEl.tomselect) {
                    selEl.tomselect.on('change', update);
                } else {
                    selEl.addEventListener('change', update);
                }
            }
            initCountToggle('champions', 'championCountWrap', 'champion_count');
            initCountToggle('skins', 'skinCountWrap', 'skin_count');
            initCountToggle('val_agents', 'valAgentCountWrap', 'val_agent_count');
        })();

        // Price -> Earnings
        (function () {
            const priceEl = document.getElementById('sellerAccountPrice');
            const outEl   = document.getElementById('sellerPriceEarningsHelp');
            if (!priceEl || !outEl) return;
            const fee = <?= $effective_fee / 100 ?>;
            const fmt = (n) => '€' + (Number.isFinite(n) ? n.toFixed(2) : '0.00');
            const parsePrice = (v) => {
                const s = String(v || '').trim().replace(/\s+/g, '').replace(',', '.');
                const num = parseFloat(s);
                return Number.isFinite(num) ? num : NaN;
            };
            const update = () => {
                const p = parsePrice(priceEl.value);
                const earnings = Number.isFinite(p) ? Math.max(0, p * (1 - fee)) : 0;
                outEl.innerHTML = `Your Earnings: <span class="fw-semibold">${fmt(earnings)}</span>`;
            };
            priceEl.addEventListener('input', update);
            priceEl.addEventListener('change', update);
            update();
        })();

        // Gallery: drag/drop/paste/click uploader
        (function () {
            const dropzone = document.getElementById('galleryDropzone');
            const input = document.getElementById('galleryUpload');
            const btn = document.getElementById('selectImagesBtn');
            const preview = document.getElementById('previewGallery');
            const orderInput = document.getElementById('galleryOrderInput');
            if (!dropzone || !input || !preview || !orderInput) return;

            let galleryItems = [];
            let dragFromIndex = null;
            let tempSeq = 0;
            const fileKey = (f) => `${f.name}__${f.size}__${f.lastModified}`;
            const escHtml = (s) => String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            function getNewFiles() {
                return galleryItems.filter(item => item && item.type === 'new' && item.file).map(item => item.file);
            }

            function syncInputAndRender() {
                const dt = new DataTransfer();
                getNewFiles().forEach(f => dt.items.add(f));
                input.files = dt.files;

                const order = galleryItems.map(function (item) {
                    return item.type === 'existing' ? item.url : item.tempId;
                });
                orderInput.value = JSON.stringify(order);

                const hasAnyImages = galleryItems.length > 0;
                if (typeof input.setCustomValidity === 'function') {
                    input.setCustomValidity((input.required && !hasAnyImages) ? 'Please upload at least one image.' : '');
                }

                preview.innerHTML = '';
                galleryItems.forEach(function (item, i) {
                    const isMain = (i === 0);
                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-3';

                    let src = '';
                    let objectUrl = null;
                    if (item.type === 'existing') {
                        src = item.url;
                    } else if (item.file) {
                        objectUrl = URL.createObjectURL(item.file);
                        src = objectUrl;
                    }

                    const badge = item.type === 'existing'
                        ? '<div class="gallery-preview-badge" style="top:auto;bottom:.5rem;background:rgba(59,130,246,.85);">LIVE</div>'
                        : '<div class="gallery-preview-badge" style="top:auto;bottom:.5rem;background:rgba(168,85,247,.85);">NEW</div>';

                    col.innerHTML = `
                        <div class="gallery-preview-tile ${isMain ? 'is-main' : ''}" draggable="true" data-index="${i}">
                            <div class="gallery-preview-badge" style="left:auto; right:.5rem; background: rgba(0,0,0,.45);">#${i+1}</div>
                            ${isMain ? '<div class="gallery-preview-badge">MAIN</div>' : ''}
                            ${badge}
                            <img src="${escHtml(src)}" alt="Preview">
                            <div class="gallery-preview-hint">Drag to reorder</div>
                            <div class="gallery-preview-overlay">
                                <button type="button" class="gallery-preview-remove" data-remove-index="${i}" aria-label="Remove">
                                    <i class="bi-trash"></i>
                                </button>
                            </div>
                        </div>`;
                    preview.appendChild(col);

                    if (objectUrl) {
                        const imgEl = col.querySelector('img');
                        if (imgEl) imgEl.addEventListener('load', function () { try { URL.revokeObjectURL(objectUrl); } catch (e) {} }, { once: true });
                    }
                });
            }

            function addFiles(files) {
                const incoming = (files || []).filter(f => f && f.type && f.type.startsWith('image/'));
                if (!incoming.length) return;
                const existing = new Set(
                    galleryItems
                        .filter(item => item && item.type === 'new' && item.file)
                        .map(item => fileKey(item.file))
                );
                incoming.forEach(f => {
                    const k = fileKey(f);
                    if (!existing.has(k)) {
                        galleryItems.push({ type: 'new', file: f, tempId: `__new__${tempSeq++}` });
                        existing.add(k);
                    }
                });
                syncInputAndRender();
            }

            function removeAt(idx) {
                if (!Number.isFinite(idx) || idx < 0 || idx >= galleryItems.length) return;
                galleryItems.splice(idx, 1);
                syncInputAndRender();
            }

            function move(from, to) {
                if (from === to || from < 0 || to < 0 || from >= galleryItems.length || to >= galleryItems.length) return;
                const item = galleryItems.splice(from, 1)[0];
                galleryItems.splice(to, 0, item);
                syncInputAndRender();
            }

            function parseExistingImages(raw) {
                if (Array.isArray(raw)) return raw.filter(Boolean);
                if (typeof raw === 'string') {
                    const trimmed = raw.trim();
                    if (!trimmed) return [];
                    try {
                        const parsed = JSON.parse(trimmed);
                        return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
                    } catch (e) {
                        return [];
                    }
                }
                return [];
            }

            window.alGalleryManager = {
                reset: function () {
                    galleryItems = [];
                    tempSeq = 0;
                    try { input.value = ''; } catch (e) {}
                    syncInputAndRender();
                },
                loadExisting: function (images) {
                    galleryItems = parseExistingImages(images).map(function (url) {
                        return { type: 'existing', url: String(url) };
                    });
                    tempSeq = 0;
                    try { input.value = ''; } catch (e) {}
                    syncInputAndRender();
                }
            };

            input.addEventListener('change', function () { addFiles(input.files ? Array.from(input.files) : []); });

            const openPicker = () => { try { input.value = ''; } catch (e) {} input.click(); };
            if (btn) btn.addEventListener('click', function (e) { e.preventDefault(); openPicker(); });
            dropzone.addEventListener('click', function (e) {
                if (e.target && (e.target.id === 'selectImagesBtn' || e.target.closest('#selectImagesBtn'))) return;
                openPicker();
            });

            preview.addEventListener('click', function (e) {
                const btn = e.target && e.target.closest ? e.target.closest('.gallery-preview-remove') : null;
                if (!btn) return;
                e.preventDefault();
                const idx = parseInt(btn.getAttribute('data-remove-index'), 10);
                if (!Number.isNaN(idx)) removeAt(idx);
            });

            preview.addEventListener('dragstart', function (e) {
                const tile = e.target && e.target.closest ? e.target.closest('.gallery-preview-tile') : null;
                if (!tile) return;
                dragFromIndex = parseInt(tile.getAttribute('data-index'), 10);
                if (e.dataTransfer) { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', String(dragFromIndex)); }
            });
            preview.addEventListener('dragover', function (e) { if (dragFromIndex === null) return; e.preventDefault(); if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'; });
            preview.addEventListener('drop', function (e) {
                if (dragFromIndex === null) return;
                e.preventDefault();
                const tile = e.target && e.target.closest ? e.target.closest('.gallery-preview-tile') : null;
                if (!tile) return;
                const to = parseInt(tile.getAttribute('data-index'), 10);
                const from = dragFromIndex;
                dragFromIndex = null;
                if (!Number.isNaN(from) && !Number.isNaN(to)) move(from, to);
            });
            preview.addEventListener('dragend', function () { dragFromIndex = null; });

            ['dragenter', 'dragover'].forEach(evtName => dropzone.addEventListener(evtName, function (e) {
                e.preventDefault(); e.stopPropagation(); dropzone.classList.add('dragover');
            }));
            ['dragleave', 'drop'].forEach(evtName => dropzone.addEventListener(evtName, function (e) {
                e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('dragover');
            }));
            dropzone.addEventListener('drop', function (e) {
                const dropped = e.dataTransfer && e.dataTransfer.files ? Array.from(e.dataTransfer.files) : [];
                addFiles(dropped);
            });

            document.addEventListener('paste', function (e) {
                const offcanvas = document.getElementById('offcanvasAddAccount');
                if (offcanvas && !offcanvas.classList.contains('show')) return;
                const items = e.clipboardData && e.clipboardData.items ? e.clipboardData.items : [];
                const files = [];
                for (const item of items) {
                    if (item.kind === 'file') {
                        const blob = item.getAsFile();
                        if (blob && blob.type && blob.type.startsWith('image/')) {
                            const ext = (blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
                            files.push(new File([blob], `paste-${Date.now()}.${ext}`, { type: blob.type }));
                        }
                    }
                }
                if (files.length) { e.preventDefault(); addFiles(files); }
            });

            const offcanvasEl = document.getElementById('offcanvasAddAccount');
            if (offcanvasEl) offcanvasEl.addEventListener('shown.bs.offcanvas', function () { syncInputAndRender(); });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                const offcanvasEl = document.getElementById('offcanvasAddAccount');
                if (!offcanvasEl || !offcanvasEl.classList.contains('show')) return;
                const instance = bootstrap.Offcanvas.getInstance(offcanvasEl) || bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                instance.hide();
            });

            syncInputAndRender();
        })();

        // Submit
        $('.create-account').on('click', function (e) {
            e.preventDefault();
            const $form = $(this).closest('.js-step-form');
            stepSelectors.forEach(function(sel){ $(sel).find(':input').prop('disabled', false); });

            // Only submit fields for the selected game.
            // Without this, hidden game sections get re-enabled above and duplicate
            // fields like name="server" can overwrite the selected LoL server with Valorant's default EU.
            const selectedGame = document.getElementById('selectedGame')?.value || 'lol';
            const lolData = document.getElementById('lolGameData');
            const valData = document.getElementById('valGameData');
            const codData = document.getElementById('codGameData');
            const lolChampionsContent = document.getElementById('lolChampionsContent');

            if (lolData) {
                lolData.querySelectorAll('input,select,textarea').forEach(function(f){
                    f.disabled = selectedGame !== 'lol';
                });
            }
            if (valData) {
                valData.querySelectorAll('input,select,textarea').forEach(function(f){
                    f.disabled = selectedGame !== 'val';
                });
            }
            if (codData) {
                codData.querySelectorAll('input,select,textarea').forEach(function(f){
                    f.disabled = selectedGame !== 'cod';
                });
            }
            document.querySelectorAll('.schemaGameData').forEach(function(panel){
                const panelGame = panel.getAttribute('data-schema-game') || '';
                panel.querySelectorAll('input,select,textarea').forEach(function(f){
                    f.disabled = panelGame !== selectedGame;
                });
            });
            if (lolChampionsContent) {
                lolChampionsContent.querySelectorAll('input,select,textarea,button').forEach(function(f){
                    f.disabled = selectedGame !== 'lol';
                });
            }

            if (!$form[0].checkValidity()) {
                goToFirstInvalidStep($form[0], $form);
                return;
            }

            // Last line of defence: FormData reads the native selects, so any single
            // select whose TomSelect shows something else is corrected here first.
            $form[0].querySelectorAll('select').forEach(function(el){
                if (!el.tomselect || el.multiple || el.disabled) return;
                const tsValue = el.tomselect.getValue();
                if (typeof tsValue === 'string' && tsValue !== '' && el.value !== tsValue) el.value = tsValue;
            });

            const formData = new FormData($form[0]);

            // Compatibility: mirror selected dynamic schema fields into game_data JSON.
            // This is important for Roblox Experience/Game, key: games.
            const selectedSchemaPanel = (typeof window.sellerAccountGetSelectedSchemaPanel === 'function') ? window.sellerAccountGetSelectedSchemaPanel() : document.querySelector('.schemaGameData[data-schema-game="' + selectedGame + '"]');
            function alSchemaKeyFromName(name) {
                name = String(name || '').trim();
                if (!name) return '';
                name = name.replace(/\[\]$/, '');
                let m = name.match(/^schema\[([^\]]+)\]$/) || name.match(/^game_data\[([^\]]+)\]$/);
                if (m) return m[1];
                if (name.indexOf('schema_') === 0) return name.substring(7);
                return name;
            }
            function alSchemaFieldValue(field) {
                if (!field) return '';
                if (field.type === 'checkbox') return field.checked ? '1' : '0';
                if (field.tomselect) return field.tomselect.getValue();
                if (field.tagName === 'SELECT' && field.multiple) {
                    return Array.from(field.selectedOptions).map(function(opt){ return opt.value; });
                }
                return field.value;
            }
            if (selectedSchemaPanel) {
                const dynamicGameData = {};
                selectedSchemaPanel.querySelectorAll('input[name],select[name],textarea[name]').forEach(function(field) {
                    if (field.disabled) return;
                    const key = alSchemaKeyFromName(field.getAttribute('name') || '');
                    if (!key || ['action','id','game','game_data'].includes(key)) return;
                    dynamicGameData[key] = alSchemaFieldValue(field);
                });
                if (Object.keys(dynamicGameData).length) {
                    formData.set('game_data', JSON.stringify(dynamicGameData));
                    Object.keys(dynamicGameData).forEach(function(key) {
                        const value = dynamicGameData[key];
                        formData.set('schema_' + key, Array.isArray(value) ? value.join('|') : value);
                    });
                }
            }
            $.ajax({
                type: 'post',
                url: '<?= AJAX_URL ?>',
                data: formData,
                dataType: 'text',
                cache: false,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $form.find('button[type="button"].create-account').attr('data-indicator', 'on');
                    $form.find('*:not(.disabled)').prop('disabled', true);
                },
                error: function () {
                    $form.find('button').removeAttr('data-indicator');
                    $form.find('*:not(.disabled)').prop('disabled', false);
                    create_toast('danger', 'Error', 'Something went wrong. Please try again.');
                },
                success: function (response) {
                    $form.find('button').removeAttr('data-indicator');
                    $form.find('*:not(.disabled)').prop('disabled', false);
                    response = JSON.parse(response);
                    ajax_response_handler(response);
                },
            });
        });

        // Step selectors
        const stepSelectors = ['#StepGame', '#StepListing', '#StepData', '#StepCreds'];

        function getVisibleStepIndex() {
            for (let i = 0; i < stepSelectors.length; i++) {
                const el = document.querySelector(stepSelectors[i]);
                if (el && el.offsetParent !== null) return i;
            }
            return 0;
        }

        function parseTargetSelector(raw) {
            if (!raw) return null;
            try { const opts = JSON.parse(raw); return (opts && opts.targetSelector) ? opts.targetSelector : null; } catch (e) { return null; }
        }

        function getInvalidInputs(formEl, scopeEl) {
            if (!formEl) return [];
            const scope = scopeEl || formEl;
            return Array.from(scope.querySelectorAll('input, select, textarea')).filter(function(el) {
                if (el.disabled) return false;
                if (el.type === 'hidden') return false;
                return el.willValidate && !el.checkValidity();
            });
        }

        function isStepValid(formEl, stepSelector) {
            const stepEl = document.querySelector(stepSelector);
            return getInvalidInputs(formEl, stepEl).length === 0;
        }

        function goToFirstInvalidStep(formEl, $form) {
            for (let i = 0; i < stepOrder.length; i++) {
                const stepEl = document.querySelector(stepOrder[i]);
                if (!stepEl) continue;
                if (getInvalidInputs(formEl, stepEl).length) {
                    setActiveStep(stepOrder[i]);
                    showValidationErrors($form);
                    return false;
                }
            }
            showValidationErrors($form);
            return false;
        }

        // Capture-phase step guard (validates before advancing)
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-hs-step-form-next-options]');
            if (!trigger) return;
            const formEl = trigger.closest('.js-step-form');
            if (!formEl) return;
            const target = parseTargetSelector(trigger.getAttribute('data-hs-step-form-next-options'));
            if (!target) return;
            const currentIndex = getVisibleStepIndex();
            const targetIndex = stepSelectors.indexOf(target);
            if (targetIndex > currentIndex) {
                const $form = $(formEl);
                const gallery = formEl.querySelector('#galleryUpload');
                if (gallery) {
                    gallery.setCustomValidity((gallery.required && document.getElementById('previewGallery') && !document.getElementById('previewGallery').children.length) ? 'Please upload at least one image.' : '');
                }
                if (!isStepValid(formEl, stepOrder[currentIndex] || stepSelectors[currentIndex])) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
                    showValidationErrors($form);
                    return;
                }
            }
        }, true);

        new HSStepForm('.js-step-form', {
            finish($el) {}
        });

        const stepOrder  = ['#StepGame', '#StepListing', '#StepData', '#StepCreds'];
        const stepEls    = {
            '#StepGame':    ['ocStep0', null,      'ocLine0'],
            '#StepListing': ['ocStep1', 'ocLine0', 'ocLine1'],
            '#StepData':    ['ocStep2', 'ocLine1', 'ocLine2'],
            '#StepCreds':   ['ocStep3', 'ocLine2', null],
        };
        const ocPrevBtn  = document.getElementById('ocPrevBtn');
        const ocNextBtn  = document.getElementById('ocNextBtn');
        const ocSubmitBtn= document.getElementById('ocSubmitBtn');
        const ocScroll   = document.getElementById('ocScrollArea');

        function updateStepUI(stepSelector) {
            const idx = stepOrder.indexOf(stepSelector);
            stepOrder.forEach(function(s, i) {
                const id = stepEls[s][0];
                const el = id ? document.getElementById(id) : null;
                if (!el) return;
                el.classList.remove('active','done');
                if (i < idx) el.classList.add('done');
                if (i === idx) el.classList.add('active');
                const num = el.querySelector('.oc-step-num');
                if (num) num.innerHTML = i < idx ? '<i class="fa-solid fa-check" style="font-size:.7rem;"></i>' : (i+1);
            });
            ['ocLine0','ocLine1','ocLine2'].forEach(function(lineId, i) {
                const el = document.getElementById(lineId);
                if (!el) return;
                el.classList.toggle('done', idx > i);
            });
            // Show prev only after first step; submit only on last step
            if (ocPrevBtn)   ocPrevBtn.style.display   = idx > 0 ? '' : 'none';
            if (ocNextBtn)   ocNextBtn.style.display   = idx < stepOrder.length - 1 ? '' : 'none';
            if (ocSubmitBtn) ocSubmitBtn.style.display = idx === stepOrder.length - 1 ? '' : 'none';
            if (ocScroll) ocScroll.scrollTop = 0;
        }

        function setActiveStep(stepSelector) {
            stepSelectors.forEach(function(sel){
                const $step = $(sel);
                const isActive = (sel === stepSelector);
                if (isActive) {
                    $step.show().addClass('active');
                    $step.find(':input').prop('disabled', false);
                } else {
                    $step.hide().removeClass('active');
                    $step.find(':input').prop('disabled', true);
                }
            });
            $('[name="delivery_type"]:checked').trigger('change');
            updateStepUI(stepSelector);
            // After a step change, keep only the selected game's data fields enabled.
            // setActiveStep enables all inputs inside StepData, so hidden game panels
            // must be disabled again before validation/submission.
            if (typeof window.selectGame === 'function') {
                var sg = document.getElementById('selectedGame') ? document.getElementById('selectedGame').value : '';
                if (sg) window.selectGame(sg);
            }
        }

        // Wire footer buttons
        if (ocNextBtn) {
            ocNextBtn.addEventListener('click', function() {
                const idx = stepOrder.findIndex(s => {
                    const el = document.querySelector(s);
                    return el && el.classList.contains('active');
                });
                if (idx < 0 || idx >= stepOrder.length - 1) return;
                const formEl = document.querySelector('.js-step-form');
                const $form  = $(formEl);
                const gallery = formEl ? formEl.querySelector('#galleryUpload') : null;
                if (gallery) {
                    gallery.setCustomValidity((gallery.required && document.getElementById('previewGallery') && !document.getElementById('previewGallery').children.length) ? 'Please upload at least one image.' : '');
                }
                if (formEl && !isStepValid(formEl, stepOrder[idx])) {
                    showValidationErrors($form);
                    return;
                }
                setActiveStep(stepOrder[idx + 1]);
            });
        }
        if (ocPrevBtn) {
            ocPrevBtn.addEventListener('click', function() {
                const idx = stepOrder.findIndex(s => {
                    const el = document.querySelector(s);
                    return el && el.classList.contains('active');
                });
                if (idx > 0) setActiveStep(stepOrder[idx - 1]);
            });
        }
        if (ocSubmitBtn) {
            ocSubmitBtn.addEventListener('click', function() {
                const btn = document.querySelector('.create-account');
                if (btn) btn.click();
            });
        }

        setActiveStep('#StepGame');

        // Game selector logic
        window.sellerAccountPanelMatchesGame = function(panel, game, schemaGame) {
            if (!panel) return false;
            var aliases = (panel.getAttribute('data-schema-aliases') || panel.getAttribute('data-schema-game') || '')
                .split(',').map(function(v){ return String(v || '').trim(); }).filter(Boolean);
            var panelGame = panel.getAttribute('data-schema-game') || '';
            if (panelGame) aliases.push(panelGame);
            return aliases.indexOf(game) !== -1 || aliases.indexOf(schemaGame) !== -1;
        };
        window.sellerAccountGetSelectedSchemaPanel = function() {
            var game = document.getElementById('selectedGame') ? document.getElementById('selectedGame').value : '';
            var card = document.querySelector('.oc-game-card.selected') || document.querySelector('.oc-game-card[data-game="' + game + '"]');
            var schemaGame = card ? (card.getAttribute('data-schema') || game) : game;
            var panels = Array.prototype.slice.call(document.querySelectorAll('.schemaGameData'));
            return panels.find(function(panel){ return window.sellerAccountPanelMatchesGame(panel, game, schemaGame); }) || null;
        };
        window.selectGame = function(game) {
            // Keep the real game slug in the form. Only LoL, Valorant and TFT keep their legacy short aliases.
            var _legacyMap = {'league-of-legends':'lol','leagu':'lol','leag':'lol','valorant':'val','valor':'val','valo':'val','teamfight-tactics':'tft','teamf':'tft'};
            game = _legacyMap[game] || game;
            var selectedInput = document.getElementById('selectedGame');
            var previousGame = selectedInput ? (_legacyMap[selectedInput.value] || selectedInput.value) : '';
            if (selectedInput) selectedInput.value = game;

            var selectedCard = null;
            document.querySelectorAll('.oc-game-card').forEach(function(card) {
                var radio = card.querySelector('input[type="radio"]');
                var cardGame = (radio && radio.value) || card.getAttribute('data-game') || '';
                var isSelected = cardGame === game;
                if (radio) radio.checked = !!isSelected;
                card.classList.toggle('selected', !!isSelected);
                if (isSelected) selectedCard = card;
            });
            if (!selectedCard) {
                selectedCard = document.querySelector('.oc-game-card[data-game="' + game + '"]');
                if (selectedCard) selectedCard.classList.add('selected');
            }
            var schemaGame = selectedCard ? (selectedCard.getAttribute('data-schema') || game) : game;

            if (selectedCard) {
                var chip = document.getElementById('ocGameSelectedChip');
                var icon = selectedCard.getAttribute('data-icon') || '';
                var name = selectedCard.getAttribute('data-name') || game;
                if (chip) {
                    chip.innerHTML = (icon ? '<img src="' + icon.replace(/"/g, '&quot;') + '" alt="">' : '') + '<span>' + name.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                }
            }

            var lolData  = document.getElementById('lolGameData');
            var valData  = document.getElementById('valGameData');
            var genData  = document.getElementById('genericGameData');
            var lolChamp = document.getElementById('lolChampionsContent');
            var gameKey = _legacyMap[game] || game;

            if (lolData)  { lolData.style.display  = gameKey === 'lol' ? '' : 'none'; }
            if (valData)  { valData.style.display  = gameKey === 'val' ? '' : 'none'; }

            var hasSchemaPanel = false;
            document.querySelectorAll('.schemaGameData').forEach(function(panel) {
                var match = window.sellerAccountPanelMatchesGame(panel, game, schemaGame);
                panel.style.display = match ? '' : 'none';
                if (match) hasSchemaPanel = true;
            });
            if (genData)  { genData.style.display = (gameKey !== 'lol' && gameKey !== 'val' && !hasSchemaPanel) ? '' : 'none'; }
            if (lolChamp) { lolChamp.style.display = gameKey === 'lol' ? '' : 'none'; }

            if (lolData)  lolData.querySelectorAll('input,select,textarea').forEach(function(f) { f.disabled = gameKey !== 'lol'; });
            if (valData)  valData.querySelectorAll('input,select,textarea').forEach(function(f) { f.disabled = gameKey !== 'val'; });
            document.querySelectorAll('.schemaGameData').forEach(function(panel) {
                var match = window.sellerAccountPanelMatchesGame(panel, game, schemaGame);
                panel.querySelectorAll('input,select,textarea').forEach(function(f) { f.disabled = !match; });
            });
            if (genData)  genData.querySelectorAll('input,select,textarea').forEach(function(f) { f.disabled = (gameKey === 'lol' || gameKey === 'val' || hasSchemaPanel); });
            if (lolChamp) lolChamp.querySelectorAll('input,select,textarea,button').forEach(function(f) { f.disabled = gameKey !== 'lol'; });

            // A fresh LoL listing must always start at Unranked on EUW. Keep the
            // native selects and their TomSelect controls in sync; edit mode is
            // excluded so existing account values are never overwritten.
            var accountForm = selectedInput && selectedInput.form;
            var actionInput = accountForm && accountForm.querySelector('[name="action"]');
            if (gameKey === 'lol' && previousGame !== gameKey && actionInput && actionInput.value === 'seller_create_account') {
                [
                    ['current_rank', '0'],
                    ['flex_rank', '0'],
                    ['previous_rank', '0'],
                    ['server', 'euw']
                ].forEach(function(defaultEntry) {
                    var field = accountForm.querySelector('[name="' + defaultEntry[0] + '"]');
                    if (!field) return;
                    field.value = defaultEntry[1];
                    if (field.tomselect) field.tomselect.setValue(defaultEntry[1], true);
                });
                if (window.jQuery) {
                    $(accountForm).find('[name="current_rank"],[name="flex_rank"],[name="previous_rank"]').trigger('change');
                }
            }
        };
        // Search inside compact game selector
        (function initOffcanvasGameSearch() {
            var search = document.getElementById('ocGameSearch');
            var empty = document.getElementById('ocGameEmpty');
            var cards = Array.prototype.slice.call(document.querySelectorAll('.oc-game-card'));
            function applyGameSearch() {
                var q = (search && search.value ? search.value : '').toLowerCase().trim();
                var visible = 0;
                cards.forEach(function(card) {
                    var haystack = (card.getAttribute('data-search') || card.textContent || '').toLowerCase();
                    var match = !q || haystack.indexOf(q) !== -1;
                    card.classList.toggle('is-hidden', !match);
                    if (match) visible++;
                });
                if (empty) empty.classList.toggle('is-visible', visible === 0);
            }
            if (search) search.addEventListener('input', applyGameSearch);
            applyGameSearch();
        })();

        // Select first available game on load
        <?php $__firstGame = isset($_sellerGames[0]) ? ($_slugToShort[$_sellerGames[0]['slug']] ?? $_sellerGames[0]['slug']) : 'lol'; ?>
        selectGame('<?= htmlspecialchars($__firstGame) ?>');

        $(document).on('click', '[data-hs-step-form-next-options]', function (e) {
            const target = parseTargetSelector($(this).attr('data-hs-step-form-next-options'));
            if (target) setTimeout(() => setActiveStep(target), 0);
        });

        $(document).on('click', '[data-hs-step-form-prev-options]', function () {
            const target = parseTargetSelector($(this).attr('data-hs-step-form-prev-options'));
            if (target) setTimeout(() => setActiveStep(target), 0);
        });

        // Rank change handlers
        $('[name="current_rank"]').on('change', function () {
            const val = $(this).val();
            const rank = $('.current-rank'), division = $('.current-division'), lp = $('.current-lp');
            const division_select = $('[name="current_division"]'), lp_input = $('[name="current_lp"]');
            if (val == 0) {
                rank.removeClass('col-9').addClass('col-12');
                division.addClass('d-none'); lp.addClass('d-none');
                division_select.attr('required', false); lp_input.attr('required', false);
            } else if (val > 0 && val < 8) {
                rank.removeClass('col-12').addClass('col-9');
                division.removeClass('d-none'); lp.addClass('d-none');
                division_select.attr('required', true); lp_input.attr('required', false);
            } else if (val >= 8) {
                rank.removeClass('col-12').addClass('col-9');
                division.addClass('d-none'); lp.removeClass('d-none');
                division_select.attr('required', false); lp_input.attr('required', false);
            }
        });

        $('[name="flex_rank"]').on('change', function () {
            const val = $(this).val();
            const rank = $('.flex-rank'), division = $('.flex-division'), lp = $('.flex-lp');
            const division_select = $('[name="flex_division"]'), lp_input = $('[name="flex_lp"]');
            if (val == 0) {
                rank.removeClass('col-8').addClass('col-12');
                division.addClass('d-none'); lp.addClass('d-none');
                division_select.attr('required', false); lp_input.attr('required', false);
            } else if (val > 0 && val < 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.removeClass('d-none'); lp.addClass('d-none');
                division_select.attr('required', true); lp_input.attr('required', false);
            } else if (val >= 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.addClass('d-none'); lp.removeClass('d-none');
                division_select.attr('required', false); lp_input.attr('required', false);
            }
        });

        $('[name="previous_rank"]').on('change', function () {
            const val = $(this).val();
            const rank = $('.previous-rank'), division = $('.previous-division'), lp = $('.previous-lp');
            const division_select = $('[name="previous_division"]'), lp_input = $('[name="previous_lp"]');
            if (val == 0) {
                rank.removeClass('col-8').addClass('col-12');
                division.addClass('d-none'); lp.addClass('d-none');
                division_select.attr('required', false); lp_input.attr('required', false);
            } else if (val > 0 && val < 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.removeClass('d-none'); lp.addClass('d-none');
                division_select.attr('required', true); lp_input.attr('required', false);
            } else if (val >= 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.addClass('d-none'); lp.removeClass('d-none');
                division_select.attr('required', false); lp_input.attr('required', false);
            }
        });

        // Email verification toggle
        window.toggleAddEmailFields = function(val) {
            const emailWrap    = document.getElementById('add_email_wrap');
            const emailPwWrap  = document.getElementById('add_email_password_wrap');
            const emailInput   = document.getElementById('add_email');
            const emailPwInput = document.getElementById('add_email_password');
            if (val === 'unverified') {
                emailInput.value = 'unverified';
                emailPwInput.value = '';
                emailPwInput.removeAttribute('required');
                emailPwWrap.style.display = 'none';
                emailWrap.style.display = 'none';
            } else {
                if (emailInput.value === 'unverified') emailInput.value = '';
                emailPwInput.setAttribute('required', 'required');
                emailPwWrap.style.display = '';
                emailWrap.style.display = '';
            }
        };

        // Delivery type toggle
        $('[name="delivery_type"]').on('change', function () {
            const deliveryType = $(this).val();
            const loginDetails = $('.login-details');
            if (deliveryType === 'instant') {
                loginDetails.find('input[type="text"], input[type="email"], input[type="password"]').attr('required', true);
                loginDetails.show();
            } else {
                loginDetails.find('input[type="text"], input[type="email"], input[type="password"]').val('');
                loginDetails.find('input[type="checkbox"]').prop('checked', false);
                loginDetails.find('input[type="hidden"][name="has_2fa"]').val('0');
                loginDetails.find('input[type="text"], input[type="email"], input[type="password"]').removeAttr('required');
                loginDetails.hide();
            }
        });

        // Credentials always visible (no reveal button)
        (function(){
            var block = document.getElementById('ocCredsBlock');
            var emailWrap = document.getElementById('add_email_wrap');
            var emailPwWrap = document.getElementById('add_email_password_wrap');
            // Remove hidden class so fields are always visible
            if (block) block.classList.remove('oc-hidden');
            if (emailWrap) emailWrap.classList.remove('oc-hidden');
            if (emailPwWrap) emailPwWrap.classList.remove('oc-hidden');
        })();

        // Enforce Instant Delivery only
        (function(){
            const instant = document.getElementById("instant");
            const manual = document.getElementById("manual");
            const manualLabel = document.querySelector("label[for=\"manual\"]");
            if (!instant || !manual) return;
            manual.disabled = true;
            manual.setAttribute("aria-disabled", "true");
            if (manualLabel) {
                manualLabel.addEventListener("click", function(e){
                    e.preventDefault(); e.stopPropagation();
                    instant.checked = true; manual.checked = false;
                    $(instant).trigger("change");
                }, true);
            }
            manual.addEventListener("change", function(){
                instant.checked = true; manual.checked = false;
                $(instant).trigger("change");
            });
            instant.checked = true; manual.checked = false;
        })();

    });

    // ── Auto-open offcanvas when arriving from Dashboard "List account" button ──
    (function () {
        if (window.location.hash !== '#open-add-account') return;
        function openAddAccountOffcanvas() {
            var el = document.getElementById('offcanvasAddAccount');
            if (!el) return;
            if (typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
                bootstrap.Offcanvas.getOrCreateInstance(el).show();
            } else {
                var btn = document.querySelector('[data-bs-target="#offcanvasAddAccount"]');
                if (btn) btn.click();
            }
            history.replaceState(null, '', window.location.pathname);
        }
        setTimeout(openAddAccountOffcanvas, 300);
    })();

    // ── Credentials: hidden by default ──
    document.body.classList.add('al-creds-hidden');

    // ── Actions dropdown ──
    // Smart positioning: opens upward when not enough space below
    function alPositionMenu(menu, btn) {
        var rect = btn.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;
        var left = Math.max(8, rect.right - 190);
        if (spaceBelow < 220) {
            menu.style.top    = '';
            menu.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
        } else {
            menu.style.bottom = '';
            menu.style.top    = (rect.bottom + 6) + 'px';
        }
        menu.style.left = left + 'px';
    }

    window.alToggleMenu = function(btn) {
        var menu = btn.nextElementSibling;
        var isOpen = menu.classList.contains('is-open');
        document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); m.style.top=''; m.style.bottom=''; m.previousElementSibling.classList.remove('is-open'); });
        if (!isOpen) {
            alPositionMenu(menu, btn);
            menu.classList.add('is-open');
            btn.classList.add('is-open');
        }
    };
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.al-actions-wrap')) {
            document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); m.style.top=''; m.style.bottom=''; m.previousElementSibling.classList.remove('is-open'); });
        }
    });
    // Reposition on scroll
    window.addEventListener('scroll', function() {
        document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
            var btn = m.previousElementSibling;
            alPositionMenu(m, btn);
        });
    }, true);

    function alCloseMenus() {
        document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
            m.classList.remove('is-open');
            if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open');
        });
    }

    // Add and Edit share one form (#offcanvasAddAccount), and every select in it is
    // a TomSelect. TomSelect keeps its own value; the native select is what FormData
    // submits. Any code path that touches only one of the two makes the form show one
    // server and submit another, so both writes always go through these two helpers.
    window.alSetSelectValue = function(el, value) {
        if (!el) return;
        el.value = value;
        if (el.tomselect) {
            el.tomselect.setValue(value === null || value === undefined ? '' : String(value), true);
        }
    };
    window.alResyncSelects = function(form) {
        if (!form) return;
        form.querySelectorAll('select').forEach(function(el){
            if (!el.tomselect || el.multiple) return;
            el.tomselect.setValue(el.value, true);
        });
    };
    // form.reset() restores whichever option TomSelect last marked as selected, which
    // is the previously edited account's value rather than the form default. For a new
    // listing every single select is therefore forced back to its first option.
    window.alResetSelectsToDefault = function(form) {
        if (!form) return;
        form.querySelectorAll('select').forEach(function(el){
            if (el.multiple) return;
            var first = el.options && el.options.length ? el.options[0].value : '';
            el.value = first;
            if (el.tomselect) {
                el.tomselect.clear(true);
                el.tomselect.setValue(first, true);
                el.tomselect.refreshOptions(false);
            }
        });
    };
    window.alApplyNewLolDefaults = function(form) {
        if (!form) return;
        [
            ['current_rank', '0'],
            ['flex_rank', '0'],
            ['previous_rank', '0']
        ].forEach(function(entry){
            window.alSetSelectValue(form.querySelector('[name="' + entry[0] + '"]'), entry[1]);
        });
        window.alSetSelectValue(form.querySelector('#serverLol'), 'euw');
        if (window.jQuery) {
            $(form).find('[name="current_rank"],[name="flex_rank"],[name="previous_rank"]').trigger('change');
        }
    };
    // Every entry point into "Add Account" runs through the offcanvas, so the reset is
    // bound here instead of only on the toolbar button (the dashboard deep link and the
    // #open-add-account hash open it directly).
    (function(){
        var ocEl = document.getElementById('offcanvasAddAccount');
        if (!ocEl) return;

        // Closing is the only moment at which nothing else writes into the form, so the
        // panel is put back into a clean create state here. Resetting on open alone was
        // unreliable: TomSelect re-renders asynchronously and the edit filler runs on
        // delayed timers, so either could land after the reset.
        ocEl.addEventListener('hidden.bs.offcanvas', function(){
            var form = ocEl.querySelector('form');
            if (!form) return;

            form.reset();
            window.alResetSelectsToDefault(form);

            var actionInput = form.querySelector('[name="action"]');
            if (actionInput) actionInput.value = 'seller_create_account';
            var idInput = form.querySelector('[name="id"]');
            if (idInput) idInput.value = '';
            window.alApplyNewLolDefaults(form);

            var titleEl = document.getElementById('offcanvasAddAccountLabel');
            if (titleEl) titleEl.textContent = 'Add Account';
            var subEl = ocEl.querySelector('.offcanvas-header div[style*=".75rem"]');
            if (subEl) subEl.textContent = 'List a new account on the marketplace';

            var galleryInput = form.querySelector('#galleryUpload');
            if (galleryInput) {
                galleryInput.required = true;
                galleryInput.setCustomValidity('Please upload at least one image.');
            }
            if (window.alGalleryManager && typeof window.alGalleryManager.reset === 'function') {
                window.alGalleryManager.reset();
            }
            form.querySelectorAll('select[multiple]').forEach(function(el){
                if (el.tomselect) el.tomselect.clear(true);
            });
            // The rank selects drive the visibility of their division/LP inputs.
            if (window.jQuery) {
                $(form).find('[name="current_rank"],[name="flex_rank"],[name="previous_rank"]').trigger('change');
            }
        });

        ocEl.addEventListener('shown.bs.offcanvas', function(){
            var form = ocEl.querySelector('form');
            if (!form) return;
            var actionInput = form.querySelector('[name="action"]');
            if (!actionInput || actionInput.value !== 'seller_create_account') return;
            window.alResetSelectsToDefault(form);
            window.alApplyNewLolDefaults(form);
        });
    })();

    window.alEditAccount = function(btn, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        var raw = btn.getAttribute('data-acc') || '{}';
        var txt = document.createElement('textarea');
        txt.innerHTML = raw;
        var data;
        try { data = JSON.parse(txt.value); } catch(err) { console.error('Edit parse error', err, txt.value); return false; }

        var form = document.querySelector('#offcanvasAddAccount form');
        if (!form) return false;

        alCloseMenus();

        var titleEl = document.getElementById('offcanvasAddAccountLabel');
        if (titleEl) titleEl.textContent = 'Edit Account';
        var subEl = document.querySelector('#offcanvasAddAccount .offcanvas-header div[style*=".75rem"]');
        if (subEl) subEl.textContent = 'Update your listing';

        var idInput = form.querySelector('[name="id"]');
        if (!idInput) { idInput = document.createElement('input'); idInput.type='hidden'; idInput.name='id'; form.prepend(idInput); }
        idInput.value = data.id || '';

        var actionInput = form.querySelector('[name="action"]');
        if (actionInput) actionInput.value = 'seller_update_account';

        ['title','description','login','password','in_game_name','level','blue_essence','riot_points','winrate_percent','delivery_instructions'].forEach(function(n){
            var el = form.querySelector('[name="'+n+'"]');
            if (el) el.value = data[n] !== null && data[n] !== undefined ? data[n] : '';
        });

        var selectedGameInput = form.querySelector('[name="game"]');
        if (selectedGameInput) selectedGameInput.value = data.game || 'lol';
        if (typeof window.selectGame === 'function') {
            var _gMap = {'league-of-legends':'lol','valorant':'val','teamfight-tactics':'tft'};
            window.selectGame(_gMap[data.game] || data.game || 'lol');
        }

        var gameData = {};
        try {
            gameData = typeof data.game_data === 'string' ? JSON.parse(data.game_data || '{}') : (data.game_data || {});
        } catch (err) {
            gameData = {};
        }


        function alFillDynamicSchemaFields() {
            // Fill dynamic schema fields from game_data when editing accounts.
            // Supports normal names, schema_*, schema[key], and game_data[key].
            function keyFromName(name) {
                name = String(name || '').trim().replace(/\[\]$/, '');
                var m = name.match(/^schema\[([^\]]+)\]$/) || name.match(/^game_data\[([^\]]+)\]$/);
                if (m) return m[1];
                if (name.indexOf('schema_') === 0) return name.substring(7);
                return name;
            }
            function setFieldValue(el, value) {
                if (!el) return;
                if (el.disabled) el.disabled = false;
                if (el.type === 'checkbox') {
                    el.checked = !!Number(Array.isArray(value) ? value[0] : value || 0);
                    if (window.jQuery) { $(el).trigger('change'); }
                    return;
                }
                if (el.tagName === 'SELECT') {
                    var values = Array.isArray(value) ? value.map(String) : String(value || '').split('|').filter(Boolean);
                    if (!el.multiple && values.length > 1) values = [values[0]];
                    Array.from(el.options).forEach(function(opt){ opt.selected = values.indexOf(opt.value) !== -1; });
                    if (el.tomselect) {
                        el.tomselect.clear(true);
                        if (values.length) el.tomselect.setValue(el.multiple ? values : values[0], true);
                        el.tomselect.refreshOptions(false);
                    }
                    if (window.jQuery) { $(el).trigger('change'); }
                    return;
                }
                if (!Array.isArray(value) && value !== undefined && value !== null) {
                    el.value = value;
                    if (window.jQuery) { $(el).trigger('input').trigger('change'); }
                }
            }
            Object.keys(gameData || {}).forEach(function(key){
                var value = gameData[key];
                form.querySelectorAll('input[name],select[name],textarea[name]').forEach(function(el){
                    if (keyFromName(el.getAttribute('name')) === key) setFieldValue(el, value);
                });
            });
        }
        alFillDynamicSchemaFields();
        setTimeout(alFillDynamicSchemaFields, 150);
        setTimeout(alFillDynamicSchemaFields, 400);

        var priceEl = form.querySelector('[name="price"]');
        if (priceEl) {
            var rawPrice = Number(data.price || 0);
            if (Number.isFinite(rawPrice) && rawPrice > 0) {
                // DB stores price in cents; edit form should display euros.
                priceEl.value = (rawPrice / 100).toFixed(2);
            } else {
                priceEl.value = '';
            }
            if (window.jQuery) { $(priceEl).trigger('input'); }
        }

        [['current_rank', data.current_rank], ['flex_rank', data.flex_rank], ['previous_rank', data.previous_rank], ['server', data.server], ['level_up_method', data.level_up_method], ['val_rank', gameData.val_rank], ['val_peak_rank', gameData.val_peak_rank], ['val_platform', gameData.val_platform]].forEach(function(pair){
            var el = form.querySelector('[name="'+pair[0]+'"]');
            if (el && pair[1] !== undefined && pair[1] !== null) {
                // These selects are TomSelect controls. Writing only el.value updates the
                // native select (what FormData submits) but not the visible TomSelect,
                // so the dropdown and the submitted value drift apart.
                window.alSetSelectValue(el, pair[1]);
                if (window.jQuery) { $(el).trigger('change'); } else { el.dispatchEvent(new Event('change', { bubbles: true })); }
            }
        });

        setTimeout(function(){
            ['current_division','current_lp','flex_division','flex_lp','previous_division','previous_lp'].forEach(function(n){
                var el = form.querySelector('[name="'+n+'"]');
                if (el) el.value = data[n] || '';
            });

            ['val_points','val_radianite','val_winrate','val_weapon_skins','val_act'].forEach(function(n){
                var el = form.querySelector('[name="'+n+'"]');
                if (el) el.value = gameData[n] !== undefined && gameData[n] !== null ? gameData[n] : '';
            });

            var rankedReadyHidden = form.querySelector('input[type="hidden"][name="val_ranked_ready"]');
            if (rankedReadyHidden) rankedReadyHidden.value = gameData.val_ranked_ready ? '1' : '0';
            var rankedReadyCheckbox = form.querySelector('input[type="checkbox"][name="val_ranked_ready"]');
            if (rankedReadyCheckbox) rankedReadyCheckbox.checked = !!Number(gameData.val_ranked_ready || 0);
        }, 50);

        var evEl = form.querySelector('[name="email_verified"]');
        if (evEl) { evEl.value = data.email_verified || 'verified'; if (window.jQuery) { $(evEl).trigger('change'); } }

        // Set email fields AFTER the email_verified trigger so they are not cleared
        var emailEl = form.querySelector('[name="email"]');
        if (emailEl) emailEl.value = data.email || '';
        var emailPwEl = form.querySelector('[name="email_password"]');
        if (emailPwEl) emailPwEl.value = data.email_password || '';

        var dtEl = form.querySelector('[name="delivery_type"][value="'+(data.delivery_type||'instant')+'"]');
        if (dtEl) { dtEl.checked = true; if (window.jQuery) { $(dtEl).trigger('change'); } else { dtEl.dispatchEvent(new Event('change', { bubbles: true })); } }

        setTimeout(function(){
            ['champions','skins','roles'].forEach(function(n){
                var el = form.querySelector('[name="'+n+'[]"]');
                if (!el) return;
                var ts = el.tomselect;
                var vals = (data[n] || '').split('|').filter(Boolean);
                if (ts) { ts.clear(); if (vals.length) ts.setValue(vals); }
            });
            // Populate count-only fields and toggle their visibility
            var champCountEl = form.querySelector('[name="champion_count"]');
            var champCountWrap = form.querySelector('#championCountWrap');
            var skinCountEl  = form.querySelector('[name="skin_count"]');
            var skinCountWrap  = form.querySelector('#skinCountWrap');
            var hasManualChamps = (data.champions || '').split('|').filter(Boolean).length > 0;
            var hasManualSkins  = (data.skins || '').split('|').filter(Boolean).length > 0;
            if (champCountEl) champCountEl.value = data.champion_count || '';
            if (skinCountEl)  skinCountEl.value  = data.skin_count  || '';
            if (champCountWrap) champCountWrap.style.display = hasManualChamps ? 'none' : '';
            if (skinCountWrap)  skinCountWrap.style.display  = hasManualSkins  ? 'none' : '';
            // Live toggle: hide count field when a manual selection is made
            var champSel = form.querySelector('[name="champions[]"]');
            var skinSel  = form.querySelector('[name="skins[]"]');
            function watchSel(selEl, wrapEl, countEl) {
                if (!selEl || !selEl.tomselect) return;
                selEl.tomselect.on('change', function() {
                    var hasVals = selEl.tomselect.getValue().length > 0;
                    if (wrapEl) wrapEl.style.display = hasVals ? 'none' : '';
                    if (hasVals && countEl) countEl.value = '';
                });
            }
            watchSel(champSel, champCountWrap, champCountEl);
            watchSel(skinSel, skinCountWrap, skinCountEl);

            var valAgentsEl = form.querySelector('[name="val_agents[]"]');
            var valAgentCountEl  = form.querySelector('[name="val_agent_count"]');
            var valAgentCountWrap = form.querySelector('#valAgentCountWrap');
            if (valAgentsEl && valAgentsEl.tomselect) {
                valAgentsEl.tomselect.clear();
                var valAgents = Array.isArray(gameData.agents) ? gameData.agents : [];
                if (valAgents.length) valAgentsEl.tomselect.setValue(valAgents);
            }
            // Populate val_agent_count and toggle visibility
            var hasManualAgents = Array.isArray(gameData.agents) && gameData.agents.filter(Boolean).length > 0;
            if (valAgentCountEl) valAgentCountEl.value = data.val_agent_count || '';
            if (valAgentCountWrap) valAgentCountWrap.style.display = hasManualAgents ? 'none' : '';
            // Live toggle for agents
            if (valAgentsEl) watchSel(valAgentsEl, valAgentCountWrap, valAgentCountEl);
        }, 200);

        var fa2 = form.querySelector('[name="has_2fa"][type="checkbox"]');
        if (fa2) fa2.checked = (data.has_2fa == 1 || data.has_2fa === '1');

        var galleryInput = form.querySelector('#galleryUpload');
        if (galleryInput) {
            galleryInput.required = false;
            galleryInput.setCustomValidity('');
        }
        if (window.alGalleryManager && typeof window.alGalleryManager.loadExisting === 'function') {
            window.alGalleryManager.loadExisting(data.images || []);
        }

        var ocEl = document.getElementById('offcanvasAddAccount');
        if (window.bootstrap && bootstrap.Offcanvas) {
            bootstrap.Offcanvas.getOrCreateInstance(ocEl).show();
        }
        return false;
    };

    document.addEventListener('click', function(e) {
        var addBtn = e.target && e.target.closest ? e.target.closest('.al-add-btn') : null;
        if (!addBtn) return;
        var form = document.querySelector('#offcanvasAddAccount form');
        if (!form) return;

        form.reset();
        // form.reset() only restores the native selects. Without this the TomSelect
        // controls keep the values of the account that was opened in Edit before,
        // and a new listing gets created with that stale server/rank.
        window.alResetSelectsToDefault(form);
        var actionInput = form.querySelector('[name="action"]');
        if (actionInput) actionInput.value = 'seller_create_account';
        var idInput = form.querySelector('[name="id"]');
        if (idInput) idInput.value = '';

        var titleEl = document.getElementById('offcanvasAddAccountLabel');
        if (titleEl) titleEl.textContent = 'Add Account';
        var subEl = document.querySelector('#offcanvasAddAccount .offcanvas-header div[style*=".75rem"]');
        if (subEl) subEl.textContent = 'Create your listing';

        var galleryInput = form.querySelector('#galleryUpload');
        if (galleryInput) {
            galleryInput.required = true;
            galleryInput.setCustomValidity('Please upload at least one image.');
        }
        if (window.alGalleryManager && typeof window.alGalleryManager.reset === 'function') {
            window.alGalleryManager.reset();
        }
        var selectedGameInput = form.querySelector('[name="game"]');
        var _firstGame = (document.querySelector('.al-game-option[data-game]:not([data-game="all"])') || {dataset:{}}).dataset.game || 'lol';
        if (selectedGameInput) selectedGameInput.value = _firstGame;
        if (typeof window.selectGame === 'function') {
            window.selectGame(_firstGame);
        }

        setTimeout(function(){
            // Clear val_agent_count when agents are cleared
            var vacEl = form.querySelector('[name="val_agent_count"]');
            var vacWrap = form.querySelector('#valAgentCountWrap');
            if (vacEl) vacEl.value = '';
            if (vacWrap) vacWrap.style.display = '';
            ['champions','skins','roles','val_agents'].forEach(function(n){
                var el = form.querySelector('[name="'+n+'[]"]');
                if (el && el.tomselect) el.tomselect.clear();
            });
        }, 0);
    });

    window.alCopyPublicUrl = function(btn, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        alCloseMenus();
        var url = btn.getAttribute('data-url') || '';
        if (!url) return false;

        var done = function(){
            var i = btn.querySelector('i');
            var orig = i ? i.className : '';
            if (i) i.className = 'fa-solid fa-check';
            btn.style.color = '#4ade80';
            setTimeout(function(){ if (i) i.className = orig; btn.style.color = ''; }, 1500);
            if (typeof create_toast === 'function') create_toast('success', 'Copied', 'Public URL copied to clipboard.');
        };

        var fallbackCopy = function(value){
            var ta = document.createElement('textarea');
            ta.value = value;
            ta.setAttribute('readonly', 'readonly');
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try { var ok = document.execCommand('copy'); document.body.removeChild(ta); return ok; }
            catch (err) { document.body.removeChild(ta); return false; }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(done).catch(function(){
                if (fallbackCopy(url)) done();
                else if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not copy the public URL.');
            });
        } else if (fallbackCopy(url)) {
            done();
        } else if (typeof create_toast === 'function') {
            create_toast('danger', 'Error', 'Could not copy the public URL.');
        }
        return false;
    };

    window.alDuplicateAccount = function(btn, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        alCloseMenus();
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Duplicating...';
        $.post('<?= AJAX_URL ?>', { action: 'seller_duplicate_account', id: btn.getAttribute('data-id') }, function(resp) {
            var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(err) {}
            if (d && d.refreshPage) { window.location.reload(); return; }
            if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-duotone fa-copy"></i> Duplicate Account';
        }).fail(function(){
            if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not duplicate the account.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-duotone fa-copy"></i> Duplicate Account';
        });
        return false;
    };

    window.alToggleListAccount = function(btn, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        alCloseMenus();
        var action = btn.getAttribute('data-action');
        var isUnlist = action === 'seller_mark_unlist';
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ' + (isUnlist ? 'Unlisting...' : 'Re-listing...');
        $.post('<?= AJAX_URL ?>', { action: action, id: btn.getAttribute('data-id') }, function(resp) {
            var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(err) {}
            if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
            if (d && d.refreshPage) { window.location.reload(); return; }
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }).fail(function(){
            if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not update the listing status.');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
        return false;
    };

    window.alDeleteAccount = function(btn, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        alCloseMenus();
        if (!confirm('Delete this account? This cannot be undone.')) return false;
        btn.disabled = true;
        $.post('<?= AJAX_URL ?>', { action: 'seller_delete_account', id: btn.getAttribute('data-id') }, function(resp) {
            var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(err) {}
            if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
            if (d && d.refreshPage) window.location.reload();
            else btn.disabled = false;
        }).fail(function(){
            if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete the account.');
            btn.disabled = false;
        });
        return false;
    };

    // ── Bulk select & delete ──────────────────────────────────────
    (function () {
        var selected = new Set();
        var $bulkBtn  = $('#alBulkDeleteBtn');
        var $bulkCnt  = $('#alBulkCount');
        var $chkAll   = $('#alChkAll');

        function updateUI() {
            var n = selected.size;
            $bulkCnt.text(n);
            if (n > 0) {
                $bulkBtn.css('display', 'inline-flex');
            } else {
                $bulkBtn.css('display', 'none');
            }
            // Update select-all state
            var $rows = $('.al-row-chk:not(:disabled)');
            var total = $rows.length;
            var checked = $rows.filter(function(){ return selected.has(String(this.value)); }).length;
            if (total === 0 || checked === 0) {
                $chkAll.prop('checked', false).prop('indeterminate', false);
            } else if (checked === total) {
                $chkAll.prop('checked', true).prop('indeterminate', false);
            } else {
                $chkAll.prop('checked', false).prop('indeterminate', true);
            }
        }

        // Per-row checkbox
        $(document).on('change', '.al-row-chk', function(e) {
            e.stopPropagation();
            var id = String(this.value);
            if (this.checked) selected.add(id);
            else selected.delete(id);
            updateUI();
        });

        // Select all (visible, non-disabled rows)
        $chkAll.on('change', function() {
            var shouldCheck = this.checked;
            $('.al-row-chk:not(:disabled)').each(function() {
                var id = String(this.value);
                // Only affect visible rows
                var $row = $(this).closest('tr.al-row');
                if ($row.is(':visible')) {
                    this.checked = shouldCheck;
                    if (shouldCheck) selected.add(id);
                    else selected.delete(id);
                }
            });
            updateUI();
        });

        // Sync checkboxes when filter/search changes (re-render)
        var observer = new MutationObserver(function() {
            $('.al-row-chk').each(function() {
                this.checked = !this.disabled && selected.has(String(this.value));
            });
            updateUI();
        });
        var tbody = document.getElementById('alTbody');
        if (tbody) observer.observe(tbody, { childList: true, subtree: false });

        // Bulk delete click
        $bulkBtn.on('click', function() {
            if (!selected.size) return;
            var ids = Array.from(selected).map(function(v){ return parseInt(v, 10); }).filter(function(n){ return isFinite(n); });
            if (!ids.length) return;
            if (!confirm('Delete ' + ids.length + ' selected account(s)? This cannot be undone.')) return;

            $bulkBtn.prop('disabled', true);

            $.ajax({
                type: 'post',
                url: '<?= AJAX_URL ?>',
                // ids_json, because traditional serialization sends ids=1&ids=2
                // and PHP keeps only the last value (so only one account was deleted).
                data: { action: 'seller_bulk_delete_accounts', ids_json: JSON.stringify(ids) },
                dataType: 'text',
                success: function(response) {
                    var d = response;
                    try { if (typeof response === 'string') d = JSON.parse(response); } catch(err) {}
                    if (d && d.sendToast && typeof create_toast === 'function') {
                        create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
                    }
                    selected.clear();
                    if (d && d.refreshPage) window.location.reload();
                    else { $bulkBtn.prop('disabled', false); updateUI(); }
                },
                error: function() {
                    if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete accounts.');
                    $bulkBtn.prop('disabled', false);
                }
            });
        });

        updateUI();
    })();

    document.addEventListener('click', function(e) {
        var btn;
        btn = e.target.closest('.js-edit-account');
        if (btn) return alEditAccount(btn, e);
        btn = e.target.closest('.al-action-copy-url');
        if (btn) return alCopyPublicUrl(btn, e);
        btn = e.target.closest('.js-duplicate-account');
        if (btn) return alDuplicateAccount(btn, e);
        btn = e.target.closest('.js-delete-account');
        if (btn) return alDeleteAccount(btn, e);
    });
    (function(){
        var PER_PAGE  = 20;
        var filter    = 'all';
        var gameFilter= 'all';
        var search    = '';
        var page      = 1;
        var sortCol   = 'date';
        var sortDir   = 'desc';
        var tbody     = document.getElementById('alTbody');
        var allRows   = tbody ? Array.from(tbody.querySelectorAll('.al-row')) : [];
        var showEl    = document.getElementById('alShowing');
        var totEl     = document.getElementById('alTotal');
        var pageEl    = document.getElementById('alPagination');
        var srchEl    = document.getElementById('alSearch');
        var pills     = document.querySelectorAll('.al-pill');
        var gameOptions = document.querySelectorAll('.al-game-option');
        var gameFilterBox = document.getElementById('alGameFilter');
        var gameFilterBtn = document.getElementById('alGameFilterBtn');
        var gameFilterSelected = document.getElementById('alGameFilterSelected');
        var gameFilterSearch = document.getElementById('alGameFilterSearch');
        var ths       = document.querySelectorAll('.al-table thead th.sortable');

        function getSorted(arr){
            return arr.slice().sort(function(a,b){
                var av=a.dataset[sortCol]||'', bv=b.dataset[sortCol]||'';
                var an=parseFloat(av), bn=parseFloat(bv);
                var cmp = isNaN(an)||isNaN(bn) ? av.localeCompare(bv) : an-bn;
                return sortDir==='asc' ? cmp : -cmp;
            });
        }

        function getFiltered(){
            return allRows.filter(function(c){
                var okStatus = filter === 'all' || c.dataset.status === filter;
                var okGame   = gameFilter === 'all' || (c.dataset.game || 'lol') === gameFilter;
                var okSearch = !search || (c.dataset.search||'').indexOf(search) !== -1;
                return okStatus && okGame && okSearch;
            });
        }

        function render(){
            var filtered = getSorted(getFiltered());
            var total    = filtered.length;
            var pages    = Math.max(1, Math.ceil(total / PER_PAGE));
            if(page > pages) page = pages;
            var start = (page-1)*PER_PAGE, end = start+PER_PAGE;
            // Hide all first
            allRows.forEach(function(c){ c.style.display='none'; });
            // Re-append in sorted order so DOM order matches sort
            var visible = filtered.slice(start,end);
            visible.forEach(function(c){ tbody.appendChild(c); c.style.display=''; });
            if(showEl) showEl.textContent = total>0 ? (start+1)+'–'+Math.min(end,total) : '0';
            if(totEl)  totEl.textContent  = total;
            // Sort indicators
            ths.forEach(function(th){
                th.classList.remove('sort-asc','sort-desc');
                if(th.dataset.col===sortCol) th.classList.add('sort-'+sortDir);
            });
            if(!pageEl) return;
            pageEl.innerHTML='';
            if(pages<=1) return;
            function btn(label,p,disabled,active){
                var b=document.createElement('button');
                b.className='al-pg-btn'+(active?' al-pg-active':'');
                b.innerHTML=label; b.disabled=!!disabled;
                if(!disabled) b.addEventListener('click',function(){page=p;render();});
                return b;
            }
            pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>',page-1,page===1,false));
            for(var i=1;i<=pages;i++){
                if(pages>7&&i>2&&i<pages-1&&Math.abs(i-page)>1){
                    if(i===3||i===pages-2){var d=document.createElement('span');d.style.cssText='color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;';d.textContent='…';pageEl.appendChild(d);}
                    continue;
                }
                pageEl.appendChild(btn(i,i,false,i===page));
            }
            pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>',page+1,page===pages,false));
        }

        pills.forEach(function(p){
            p.addEventListener('click',function(){
                pills.forEach(function(x){x.classList.remove('active');});
                p.classList.add('active');
                filter=p.dataset.status; page=1; render();
            });
        });
        if (gameFilterBtn && gameFilterBox) {
            gameFilterBtn.addEventListener('click', function(e){
                e.stopPropagation();
                gameFilterBox.classList.toggle('is-open');
                if (gameFilterBox.classList.contains('is-open') && gameFilterSearch) {
                    setTimeout(function(){ gameFilterSearch.focus(); }, 20);
                }
            });
            document.addEventListener('click', function(e){
                if (!gameFilterBox.contains(e.target)) gameFilterBox.classList.remove('is-open');
            });
        }
        function setSelectedGameOption(option){
            gameOptions.forEach(function(x){x.classList.remove('active');});
            option.classList.add('active');
            gameFilter = option.dataset.game || 'all';
            var label = option.dataset.label || option.textContent.trim() || 'All Games';
            var icon = option.dataset.icon || '';
            if (gameFilterSelected) {
                gameFilterSelected.innerHTML = icon
                    ? '<img src="' + icon.replace(/"/g, '&quot;') + '" alt=""><span>' + label + '</span>'
                    : '<span class="al-game-option__icon-placeholder"><i class="fa-solid fa-gamepad"></i></span><span>' + label + '</span>';
            }
            if (gameFilterBox) gameFilterBox.classList.remove('is-open');
            page = 1;
            render();
        }
        gameOptions.forEach(function(p){
            p.addEventListener('click', function(){ setSelectedGameOption(p); });
        });
        if (gameFilterSearch) {
            gameFilterSearch.addEventListener('input', function(){
                var q = gameFilterSearch.value.trim().toLowerCase();
                gameOptions.forEach(function(opt){
                    var label = (opt.dataset.label || opt.textContent || '').toLowerCase();
                    opt.style.display = !q || label.indexOf(q) !== -1 ? '' : 'none';
                });
            });
        }
        if(srchEl) srchEl.addEventListener('input',function(){
            search=srchEl.value.trim().toLowerCase(); page=1; render();
        });
        ths.forEach(function(th){
            th.addEventListener('click',function(){
                var col=th.dataset.col;
                if(sortCol===col) sortDir = sortDir==='asc'?'desc':'asc';
                else { sortCol=col; sortDir = col === 'date' ? 'desc' : 'desc'; }
                page=1; render();
            });
        });
        render();
    })();

</script>
<?= $this->end() ?>
