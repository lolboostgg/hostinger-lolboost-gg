<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>

<?php $egSharedActiveTab = 'services'; ?>
<?= $this->insert('booster/pages/egirl/_shared') ?>

<?php
    $typeLabels = ['hourly'=>'Hourly','per_game'=>'Per Game','rank_boost'=>'Rank Boost','coaching'=>'Coaching','just_chat'=>'Just Chat','custom'=>'Custom'];

    // Games come from her profile ("Games you play"), not from a hardcoded list —
    // a game added in the admin area shows up here as soon as she selects it.
    $egAllGameOptions = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
    $egMyGames = array_values(array_unique(array_filter(
        array_map(
            static fn($value) => function_exists('lb_egirl_game_key')
                ? lb_egirl_game_key((string)$value)
                : strtolower(trim((string)$value)),
            explode('|', (string)($profile['games'] ?? ''))
        ),
        static fn($value) => $value !== ''
    )));

    $egGameFallbacks = [
        'lol' => ['League of Legends', 'league-of-legends'],
        'val' => ['Valorant', 'valorant'],
        'tft' => ['Teamfight Tactics', 'teamfight-tactics'],
        'apex-legends' => ['Apex Legends', 'apex-legends'],
        'fortnite' => ['Fortnite', 'fortnite'],
        'marvel-rivals' => ['Marvel Rivals', 'marvel-rivals'],
        'rocket-league' => ['Rocket League', 'rocket-league'],
        'overwatch-2' => ['Overwatch 2', 'overwatch-2'],
        'grand-theft-auto-vi' => ['Grand Theft Auto VI', 'grand-theft-auto-vi'],
        'lol-wild-rift' => ['LoL Wild Rift', 'lol-wild-rift'],
        'counter-strike-2' => ['Counter-Strike 2', 'counter-strike-2'],
    ];
    $gameIcons = $gameLabels = $gameShort = [];
    foreach ($egMyGames as $gk) {
        $opt = $egAllGameOptions[$gk] ?? null;
        $fallback = $egGameFallbacks[$gk] ?? [ucwords(str_replace('-', ' ', $gk)), $gk];
        $gameLabels[$gk] = $opt['label'] ?? $fallback[0];
        $gameShort[$gk]  = strtoupper($gk);
        $icon = trim((string)($opt['icon'] ?? ''));
        if ($icon === '' && function_exists('util_game_icon_url')) $icon = util_game_icon_url($fallback[1]);
        if ($icon !== '') $gameIcons[$gk] = $icon;
    }
    // Services saved for a game she no longer lists must still render correctly.
    foreach ($services as $s) {
        $gk = strtolower(trim((string)($s['game'] ?? '')));
        if ($gk === '' || isset($gameLabels[$gk])) continue;
        $opt = $egAllGameOptions[$gk] ?? null;
        $gameLabels[$gk] = $opt['label'] ?? strtoupper($gk);
        $gameShort[$gk]  = strtoupper($gk);
        if (!empty($opt['icon'])) $gameIcons[$gk] = $opt['icon'];
    }

    $cutRate    = isset($egirl_cut_rate) ? $egirl_cut_rate : 0.60;
    $cutPercent = round($cutRate * 100);

    $featuredCountCurrent = 0;
    $featuredServices = [];
    $allByGame = [];
    foreach (array_keys($gameLabels) as $gk) { $allByGame[$gk] = []; }
    $allByGame[''] = [];
    foreach ($services as $s) {
        if (!empty($s['is_featured'])) { $featuredCountCurrent++; $featuredServices[] = $s; }
        $g = strtolower($s['game'] ?? '');
        if (isset($allByGame[$g])) $allByGame[$g][] = $s;
        else $allByGame[''][] = $s;
    }
    $activeGames = array_filter($allByGame, fn($g) => !empty($g));
?>

<style>
/* ── Page header ── */
.svc-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: .75rem;
}
.svc-page-title { font-size: 1.35rem; font-weight: 800; color: var(--bs-heading-color, #fff); margin: 0; }
.svc-page-sub { font-size: .82rem; color: rgba(255,255,255,.4); margin-top: .15rem; }

/* ── Featured pill in header ── */
.svc-featured-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.25);
    color: #f59e0b; border-radius: 999px;
    padding: .3rem .85rem; font-size: .78rem; font-weight: 700;
}
.svc-featured-pill .svc-fp-dots span {
    display: inline-block; width: 7px; height: 7px; border-radius: 50%;
    background: rgba(245,158,11,.3); border: 1.5px solid rgba(245,158,11,.5);
    margin-right: 2px; transition: background .2s, border-color .2s;
}
.svc-featured-pill .svc-fp-dots span.on {
    background: #f59e0b; border-color: #f59e0b;
}

/* ── Featured section ── */
.svc-featured-section {
    background: linear-gradient(135deg, rgba(245,158,11,.06) 0%, rgba(245,158,11,.02) 100%);
    border: 1px solid rgba(245,158,11,.18);
    border-radius: 14px;
    padding: 1.25rem 1.4rem;
    margin-bottom: 1.75rem;
}
.svc-section-label {
    display: flex; align-items: center; gap: .5rem;
    font-size: .7rem; font-weight: 900; text-transform: uppercase; letter-spacing: .12em;
    color: rgba(255,255,255,.35); margin-bottom: 1rem;
}
.svc-section-label::before {
    content: ''; width: 3px; height: 12px; border-radius: 99px;
    background: linear-gradient(180deg, #f59e0b, #fb923c);
}
.svc-section-label.purple::before { background: linear-gradient(180deg, #a855f7, #ec4899); }
.svc-section-label::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(245,158,11,.2), transparent); }
.svc-section-label.purple::after { background: linear-gradient(90deg, rgba(168,85,247,.2), transparent); }

/* ── Filter tabs ── */
.svc-filter-tabs {
    display: flex; align-items: center; gap: .4rem;
    margin-bottom: 1.25rem; flex-wrap: wrap;
}
.svc-filter-tab {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .38rem .95rem; border-radius: 999px;
    border: 1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.45);
    font-size: .78rem; font-weight: 700; cursor: pointer;
    transition: all .15s; text-transform: uppercase; letter-spacing: .05em;
    position: relative;
}
.svc-filter-tab:hover { border-color: rgba(168,85,247,.4); color: rgba(255,255,255,.8); background: rgba(168,85,247,.08); }
.svc-filter-tab.active { border-color: #a855f7; background: rgba(168,85,247,.15); color: #e9d5ff; }
.svc-filter-tab img { width: 14px; height: 14px; object-fit: contain; }
.svc-filter-tab .svc-ft-count {
    background: rgba(168,85,247,.25); color: #c084fc;
    font-size: .65em; font-weight: 900; padding: .1em .42em;
    border-radius: 999px; line-height: 1.5;
}
.svc-filter-tab.active .svc-ft-count { background: rgba(168,85,247,.4); color: #f3e8ff; }

/* ── Category heading ── */
.svc-cat-heading {
    display: flex; align-items: center; gap: .5rem;
    margin-bottom: .85rem; margin-top: .25rem;
}
.svc-cat-heading img { width: 18px; height: 18px; object-fit: contain; }
.svc-cat-heading .svc-cat-name { font-size: .8rem; font-weight: 800; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .1em; }
.svc-cat-heading .svc-cat-line { flex: 1; height: 1px; background: rgba(255,255,255,.07); }

/* ── Service card ── */
.svc-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 12px; padding: 1rem 1.15rem;
    transition: border-color .18s, background .18s;
    position: relative; overflow: hidden;
}
.svc-card:hover { border-color: rgba(168,85,247,.25); background: rgba(168,85,247,.04); }
.svc-card.is-featured {
    border-color: rgba(245,158,11,.35);
    background: rgba(245,158,11,.04);
}
.svc-card.is-featured::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, #f59e0b, #fb923c);
}

/* Top row of card */
.svc-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: .65rem; }
.svc-card-badges { display: flex; flex-wrap: wrap; gap: .3rem; align-items: center; }
.svc-card-actions { display: flex; gap: .3rem; align-items: center; flex-shrink: 0; }

/* Star button */
.svc-star-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.05);
    color: rgba(255,255,255,.3); cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: .78rem; transition: all .15s;
}
.svc-star-btn:hover { border-color: rgba(245,158,11,.5); color: #f59e0b; background: rgba(245,158,11,.1); }
.svc-star-btn.active { border-color: rgba(245,158,11,.7); color: #f59e0b; background: rgba(245,158,11,.15); }

/* Icon buttons (edit/delete) */
.svc-icon-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.45); cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: .75rem; transition: all .15s;
}
.svc-icon-btn:hover { border-color: rgba(255,255,255,.22); color: #fff; background: rgba(255,255,255,.08); }
.svc-icon-btn.delete:hover { border-color: rgba(239,68,68,.4); color: #f87171; background: rgba(239,68,68,.08); }

/* Card body */
.svc-card-title { font-size: .98rem; font-weight: 700; color: #fff; margin-bottom: .18rem; }
.svc-card-desc { font-size: .78rem; color: rgba(255,255,255,.4); margin-bottom: .5rem; line-height: 1.5; }
.svc-card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: .75rem; padding-top: .65rem; border-top: 1px solid rgba(255,255,255,.06); flex-wrap: wrap; gap: .4rem; }
.svc-card-price { font-size: 1.15rem; font-weight: 900; color: #34d399; }
.svc-card-meta { font-size: .72rem; color: rgba(255,255,255,.35); display: flex; align-items: center; gap: .35rem; }
.svc-card-earning { font-size: .72rem; color: rgba(255,255,255,.35); display: flex; align-items: center; gap: .3rem; margin-top: .35rem; }
.svc-card-earning strong { color: #34d399; font-weight: 700; }

/* Badges */
.svc-badge {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .18rem .55rem; border-radius: 999px;
    font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
}
.svc-badge-type { background: rgba(99,102,241,.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,.25); }
.svc-badge-game { background: rgba(255,255,255,.07); color: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.12); }
.svc-badge-game img { width: 11px; height: 11px; object-fit: contain; }
.svc-badge-featured { background: rgba(245,158,11,.15); color: #fbbf24; border: 1px solid rgba(245,158,11,.3); }
.svc-badge-inactive { background: rgba(255,255,255,.05); color: rgba(255,255,255,.3); border: 1px solid rgba(255,255,255,.08); }

/* Category sections */
.svc-cat-section { margin-bottom: 1.5rem; }
.svc-cat-section.hidden { display: none; }

/* Empty featured hint */
.svc-featured-empty {
    text-align: center; padding: 1.5rem 1rem;
    color: rgba(255,255,255,.3); font-size: .82rem;
}
.svc-featured-empty i { font-size: 1.5rem; display: block; margin-bottom: .5rem; opacity: .4; }

/* Toast */
#svcToast {
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
    min-width: 240px; border-radius: 10px; padding: .75rem 1rem;
    display: none; align-items: center; gap: .6rem;
    font-size: .82rem; font-weight: 600; color: #fff;
    box-shadow: 0 8px 24px rgba(0,0,0,.35);
}
#svcToast.success { background: #059669; }
#svcToast.error   { background: #dc2626; }

/* Dropdown */
.service-game-dropdown {
    /* Bootstrap resolves these from CSS vars, so overriding the vars keeps the
       menu dark regardless of the active theme. */
    --bs-dropdown-bg: #1c1f24;
    --bs-dropdown-link-color: #e2e8f0;
    --bs-dropdown-link-hover-bg: rgba(255,255,255,.08);
    --bs-dropdown-link-hover-color: #fff;
    --bs-dropdown-link-active-bg: rgba(124,58,237,.25);
    --bs-dropdown-link-active-color: #fff;
    --bs-dropdown-border-color: rgba(255,255,255,.12);
    background: #1c1f24 !important;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px;
    padding: 6px;
    max-height: 260px;
    overflow-y: auto;
    box-shadow: 0 18px 45px rgba(0,0,0,.5);
}
.service-game-dropdown .dropdown-item { color: #e2e8f0 !important; border-radius: 8px; padding: 10px 12px; }
.service-game-dropdown .dropdown-item:hover { background: rgba(255,255,255,.08) !important; color: #fff !important; }
.service-game-dropdown .dropdown-item.active { background: rgba(124,58,237,.25) !important; color: #fff !important; }
.service-game-option-icon, .service-game-selected-icon { width: 18px; height: 18px; object-fit: contain; flex: 0 0 18px; }

/* ── Service Modal – Dashboard Style ── */
#serviceModal .modal-dialog { max-width: 520px; }
#serviceModal .modal-content {
    background: #161820;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,.8);
}

/* Header */
#serviceModal .svc-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 1.3rem;
    border-bottom: 1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.015);
}
#serviceModal .svc-modal-header-left { display: flex; align-items: center; gap: .65rem; }
#serviceModal .svc-modal-icon {
    width: 34px; height: 34px; border-radius: 8px;
    background: rgba(124,58,237,.18); border: 1px solid rgba(124,58,237,.3);
    display: flex; align-items: center; justify-content: center;
    color: #9d72f5; font-size: .85rem;
}
#serviceModal .svc-modal-title { font-size: .95rem; font-weight: 700; color: #f0eeff; margin: 0; }
#serviceModal .svc-modal-subtitle { font-size: .7rem; color: rgba(255,255,255,.3); margin-top: .08rem; }
#serviceModal .svc-modal-close {
    width: 28px; height: 28px; border-radius: 7px;
    border: 1px solid rgba(255,255,255,.1); background: transparent;
    color: rgba(255,255,255,.35); display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .75rem; transition: all .15s;
}
#serviceModal .svc-modal-close:hover { border-color: rgba(255,255,255,.2); color: #fff; background: rgba(255,255,255,.06); }

/* Steps – thin bar style matching dashboard nav */
#serviceModal .svc-steps {
    display: flex; align-items: center;
    padding: .85rem 1.3rem .7rem;
    gap: 0;
    background: rgba(255,255,255,.01);
    border-bottom: 1px solid rgba(255,255,255,.05);
}
#serviceModal .svc-step {
    display: flex; align-items: center; gap: .35rem;
    font-size: .68rem; font-weight: 700; color: rgba(255,255,255,.25);
    text-transform: uppercase; letter-spacing: .08em; cursor: default;
    transition: color .2s;
}
#serviceModal .svc-step.active { color: #9d72f5; }
#serviceModal .svc-step.done  { color: #34d399; }
#serviceModal .svc-step-dot {
    width: 20px; height: 20px; border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.03);
    display: flex; align-items: center; justify-content: center;
    font-size: .6rem; font-weight: 800; color: rgba(255,255,255,.25);
    transition: all .2s;
    flex-shrink: 0;
}
#serviceModal .svc-step.active .svc-step-dot { border-color: #7c3aed; background: rgba(124,58,237,.2); color: #c4a0ff; }
#serviceModal .svc-step.done  .svc-step-dot { border-color: #34d399; background: rgba(52,211,153,.12); color: #34d399; }
#serviceModal .svc-step-line  { flex: 1; height: 1px; background: rgba(255,255,255,.06); margin: 0 .45rem; min-width: 18px; transition: background .3s; }
#serviceModal .svc-step-line.done { background: rgba(52,211,153,.25); }

/* Body */
#serviceModal .svc-modal-body {
    padding: 1.1rem 1.3rem;
    display: flex; flex-direction: column; gap: .8rem;
    min-height: 260px;
}
#serviceModal .svc-field-group { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; }
#serviceModal .svc-field label {
    display: flex; align-items: center; gap: .3rem;
    font-size: .68rem; font-weight: 700;
    color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .08em;
    margin-bottom: .38rem;
}
#serviceModal .svc-field label .req { color: #f87171; }

/* Validation error state */
#serviceModal .svc-field.has-error .form-control,
#serviceModal .svc-field.has-error .form-select,
#serviceModal .svc-field.has-error .svc-type-grid {
    outline: 1.5px solid rgba(248,113,113,.5);
    border-radius: 8px;
}
#serviceModal .svc-field .svc-field-error {
    display: none; font-size: .68rem; color: #f87171;
    margin-top: .3rem; align-items: center; gap: .25rem;
}
#serviceModal .svc-field.has-error .svc-field-error { display: flex; }

/* Type pills */
#serviceModal .svc-type-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .35rem; }
#serviceModal .svc-type-pill {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .18rem; padding: .5rem .35rem;
    border-radius: 8px; border: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.02);
    cursor: pointer; transition: all .12s;
    font-size: .65rem; font-weight: 700; color: rgba(255,255,255,.35);
    text-transform: uppercase; letter-spacing: .04em;
    user-select: none;
}
#serviceModal .svc-type-pill:hover { border-color: rgba(124,58,237,.35); color: rgba(255,255,255,.7); background: rgba(124,58,237,.07); }
#serviceModal .svc-type-pill.active { border-color: #7c3aed; background: rgba(124,58,237,.15); color: #d4b8ff; box-shadow: inset 0 0 0 1px rgba(124,58,237,.2); }
#serviceModal .svc-type-pill .svc-tp-icon { font-size: 1rem; line-height: 1; }

/* Inputs – match dashboard card style */
#serviceModal .form-control,
#serviceModal .form-select {
    background-color: #212429;
    border: 1px solid rgba(255,255,255,.09);
    color: #e8e4ff; border-radius: 8px;
    font-size: .84rem; padding: .5rem .8rem;
    transition: border-color .15s, background-color .15s;
}
#serviceModal .form-control:focus,
#serviceModal .form-select:focus {
    background-color: #262a31;
    border-color: rgba(124,58,237,.55);
    box-shadow: 0 0 0 3px rgba(124,58,237,.1);
    color: #fff;
    outline: none;
}
/* Native <select> popups (Unit) render in the OS light theme by default —
   color-scheme + explicit option colors keep them dark. */
#serviceModal .form-select {
    color-scheme: dark;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-opacity='.45' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    /* The rule above uses the `background` shorthand, which resets repeat/size —
       without these the caret tiles across the whole field. */
    background-repeat: no-repeat;
    background-position: right .75rem center;
    background-size: 14px 10px;
    padding-right: 2.1rem;
}
#serviceModal .form-select option,
#serviceModal .form-select optgroup {
    background-color: #1c1f24;
    color: #e2e8f0;
}
#serviceModal .form-control::placeholder { color: rgba(255,255,255,.18); }
#serviceModal textarea.form-control { resize: none; min-height: 72px; }
#serviceModal .input-group .input-group-text {
    background-color: #1c1f24; border: 1px solid rgba(255,255,255,.09); border-right: 0;
    color: rgba(255,255,255,.4); border-radius: 8px 0 0 8px; font-size: .84rem;
}
#serviceModal .input-group .form-control { border-left: 0; border-radius: 0 8px 8px 0; }

/* Earning strip */
#serviceModal .svc-earning-strip {
    display: none;
    background: rgba(52,211,153,.05);
    border: 1px solid rgba(52,211,153,.18);
    border-radius: 8px; padding: .6rem .9rem;
    align-items: center; gap: .65rem;
}
#serviceModal .svc-earning-strip.visible { display: flex; }
#serviceModal .svc-earn-icon {
    width: 26px; height: 26px; border-radius: 7px;
    background: rgba(52,211,153,.1); border: 1px solid rgba(52,211,153,.2);
    display: flex; align-items: center; justify-content: center;
    color: #34d399; font-size: .72rem; flex-shrink: 0;
}
#serviceModal .svc-earn-label { font-size: .68rem; color: rgba(255,255,255,.35); }
#serviceModal .svc-earn-amount { font-size: 1rem; font-weight: 800; color: #34d399; }
#serviceModal .svc-earn-cut { font-size: .67rem; color: rgba(52,211,153,.55); }

/* Toggle rows */
#serviceModal .svc-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .6rem .8rem;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 8px; gap: .5rem;
}
#serviceModal .svc-toggle-info { display: flex; align-items: center; gap: .55rem; }
#serviceModal .svc-toggle-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: rgba(255,255,255,.05);
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; color: rgba(255,255,255,.4);
    flex-shrink: 0;
}
#serviceModal .svc-toggle-icon.voice { background: rgba(52,211,153,.08); color: #34d399; }
#serviceModal .svc-toggle-label { font-size: .82rem; font-weight: 600; color: rgba(255,255,255,.75); }
#serviceModal .svc-toggle-desc { font-size: .68rem; color: rgba(255,255,255,.28); margin-top: .04rem; }

/* Footer */
#serviceModal .svc-modal-footer {
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; padding: .85rem 1.3rem;
    border-top: 1px solid rgba(255,255,255,.05);
    background: rgba(255,255,255,.01);
}
#serviceModal .svc-footer-left { display: flex; gap: .4rem; }
#serviceModal .svc-btn-ghost {
    padding: .48rem 1rem; border-radius: 8px;
    border: 1px solid rgba(255,255,255,.09);
    background: transparent;
    color: rgba(255,255,255,.45); font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .12s; display: flex; align-items: center; gap: .35rem;
}
#serviceModal .svc-btn-ghost:hover { border-color: rgba(255,255,255,.18); color: rgba(255,255,255,.8); background: rgba(255,255,255,.05); }
#serviceModal .svc-btn-primary {
    padding: .48rem 1.25rem; border-radius: 8px;
    background: #7c3aed;
    border: 1px solid rgba(124,58,237,.6); color: #fff; font-size: .82rem; font-weight: 700;
    cursor: pointer; transition: all .12s; display: flex; align-items: center; gap: .35rem;
}
#serviceModal .svc-btn-primary:hover { background: #6d28d9; }
#serviceModal .svc-btn-primary:active { transform: scale(.98); }
#serviceModal .svc-btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }
/* Shake animation for blocked next */
@keyframes svc-shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-5px)} 40%{transform:translateX(5px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)} }
.svc-shake { animation: svc-shake .35s ease; }
</style>

<div class="content container-fluid">

    <!-- Page header -->
    <div class="svc-page-header">
        <div>
            <h1 class="svc-page-title">My Services</h1>
            <p class="svc-page-sub">Manage your offerings &amp; control what clients see.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="svc-featured-pill">
                <i class="fa-solid fa-star fa-xs"></i>
                <div class="svc-fp-dots">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <span class="<?= $i <= $featuredCountCurrent ? 'on' : '' ?>"></span>
                    <?php endfor; ?>
                </div>
                <?= $featuredCountCurrent ?>/3 Featured
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal" id="btnNewService">
                <i class="fa-solid fa-plus me-1"></i> Add Service
            </button>
        </div>
    </div>

    <?php if (empty($services)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa-duotone fa-stars fa-3x text-muted mb-3 d-block"></i>
                <h5>No services yet</h5>
                <p class="text-muted mb-3">Add your first service so clients can book you.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal">
                    <i class="fa-solid fa-plus me-1"></i> Add Service
                </button>
            </div>
        </div>
    <?php else: ?>

        <!-- ── Featured section ── -->
        <div class="svc-featured-section">
            <div class="svc-section-label">
                <i class="fa-solid fa-star text-warning" style="font-size:.7rem;margin-left:.1rem;"></i>
                Featured Services
                <span style="font-size:.65rem;color:rgba(255,255,255,.25);font-weight:600;text-transform:none;letter-spacing:0;">shown on your public profile</span>
            </div>
            <?php if (empty($featuredServices)): ?>
                <div class="svc-featured-empty">
                    <i class="fa-regular fa-star"></i>
                    Click the ★ on any service to feature it (max 3)
                </div>
            <?php else: ?>
                <div class="row g-2">
                    <?php foreach ($featuredServices as $s): ?>
                    <?php
                        $gameKey = strtolower($s['game'] ?? '');
                        $earningFormatted = number_format(round($s['price_cents'] * $cutRate) / 100, 2);
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="svc-card is-featured">
                            <div class="svc-card-top">
                                <div class="svc-card-badges">
                                    <span class="svc-badge svc-badge-type"><?= $typeLabels[$s['type']] ?? $s['type'] ?></span>
                                    <?php if ($gameKey && isset($gameIcons[$gameKey])): ?>
                                    <span class="svc-badge svc-badge-game">
                                        <img src="<?= $gameIcons[$gameKey] ?>" alt=""> <?= $gameShort[$gameKey] ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="svc-badge svc-badge-featured"><i class="fa-solid fa-star fa-xs"></i> Featured</span>
                                </div>
                                <div class="svc-card-actions">
                                    <button class="svc-star-btn active js-toggle-featured" data-id="<?= $s['id'] ?>" data-featured="1" title="Remove from Featured">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    <button class="svc-icon-btn js-edit-service"
                                        data-id="<?= $s['id'] ?>"
                                        data-type="<?= htmlspecialchars($s['type']) ?>"
                                        data-title="<?= htmlspecialchars(html_entity_decode($s['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>"
                                        data-description="<?= htmlspecialchars(html_entity_decode($s['description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>"
                                        data-game="<?= htmlspecialchars($s['game'] ?? '') ?>"
                                        data-price="<?= $s['price_cents'] ?>"
                                        data-unit-value="<?= $s['unit_value'] ?>"
                                        data-unit-type="<?= htmlspecialchars($s['unit_type']) ?>"
                                        data-voice-chat="<?= !empty($s['includes_voice']) ? '1' : '0' ?>"
                                        data-bs-toggle="modal" data-bs-target="#serviceModal">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <button class="svc-icon-btn delete js-delete-service" data-id="<?= $s['id'] ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="svc-card-title"><?= html_entity_decode($s['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                            <?php if ($s['description']): ?>
                            <div class="svc-card-desc"><?= html_entity_decode($s['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <div class="svc-card-footer">
                                <div>
                                    <div class="svc-card-price"><?= function_exists('util_format_price_display') ? util_format_price_display($s['price_cents']) : number_format($s['price_cents']/100,2) ?> <?= htmlspecialchars($s['currency']) ?></div>
                                    <div class="svc-card-earning"><i class="fa-solid fa-wallet fa-xs"></i> Your cut: <strong><?= $earningFormatted ?> <?= htmlspecialchars($s['currency']) ?></strong> <span>(<?= $cutPercent ?>%)</span></div>
                                </div>
                                <div class="svc-card-meta">
                                    <?= (int)$s['unit_value'] ?> <?= htmlspecialchars($s['unit_type']) ?>
                                    <?php if (!empty($s['includes_voice'])): ?>
                                    &bull; <i class="fa-solid fa-microphone text-success"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── All services with filter tabs ── -->
        <div class="svc-section-label purple" style="margin-bottom:1rem;">
            All Services
        </div>

        <!-- Filter tabs — only rendered if more than one game category exists -->
        <?php if (count($activeGames) > 1): ?>
        <div class="svc-filter-tabs" id="svcFilterTabs">
            <button class="svc-filter-tab active" data-filter="all">
                <i class="fa-solid fa-border-all"></i> All
                <span class="svc-ft-count"><?= count($services) ?></span>
            </button>
            <?php foreach ($activeGames as $gKey => $gSvcs): ?>
            <button class="svc-filter-tab" data-filter="<?= $gKey === '' ? 'other' : $gKey ?>">
                <?php if ($gKey && isset($gameIcons[$gKey])): ?>
                    <img src="<?= $gameIcons[$gKey] ?>" alt="">
                <?php else: ?>
                    <i class="fa-solid fa-gamepad"></i>
                <?php endif; ?>
                <?= $gKey ? ($gameLabels[$gKey] ?? strtoupper($gKey)) : 'General' ?>
                <span class="svc-ft-count"><?= count($gSvcs) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Service cards grouped by category -->
        <?php foreach ($activeGames as $gKey => $gSvcs): ?>
        <div class="svc-cat-section" data-category="<?= $gKey === '' ? 'other' : $gKey ?>">
            <?php if (count($activeGames) > 1): ?>
            <div class="svc-cat-heading">
                <?php if ($gKey && isset($gameIcons[$gKey])): ?>
                    <img src="<?= $gameIcons[$gKey] ?>" alt="">
                <?php else: ?>
                    <i class="fa-solid fa-gamepad" style="color:rgba(255,255,255,.3);font-size:.82rem;"></i>
                <?php endif; ?>
                <span class="svc-cat-name"><?= $gKey ? ($gameLabels[$gKey] ?? strtoupper($gKey)) : 'General / Other' ?></span>
                <div class="svc-cat-line"></div>
                <span style="font-size:.7rem;color:rgba(255,255,255,.25);font-weight:700;"><?= count($gSvcs) ?></span>
            </div>
            <?php endif; ?>
            <div class="row g-2">
            <?php foreach ($gSvcs as $s): ?>
            <?php
                $gameKey = strtolower($s['game'] ?? '');
                $isFeatured = !empty($s['is_featured']);
                $earningFormatted = number_format(round($s['price_cents'] * $cutRate) / 100, 2);
            ?>
            <div class="col-md-6 col-xl-4" id="service-card-<?= $s['id'] ?>">
                <div class="svc-card <?= $isFeatured ? 'is-featured' : '' ?>">
                    <div class="svc-card-top">
                        <div class="svc-card-badges">
                            <span class="svc-badge svc-badge-type"><?= $typeLabels[$s['type']] ?? $s['type'] ?></span>
                            <?php if ($gameKey && isset($gameIcons[$gameKey])): ?>
                            <span class="svc-badge svc-badge-game">
                                <img src="<?= $gameIcons[$gameKey] ?>" alt=""> <?= $gameShort[$gameKey] ?? strtoupper($gameKey) ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($isFeatured): ?>
                            <span class="svc-badge svc-badge-featured"><i class="fa-solid fa-star fa-xs"></i> Featured</span>
                            <?php endif; ?>
                            <?php if (!$s['is_active']): ?>
                            <span class="svc-badge svc-badge-inactive">Inactive</span>
                            <?php endif; ?>
                        </div>
                        <div class="svc-card-actions">
                            <button class="svc-star-btn <?= $isFeatured ? 'active' : '' ?> js-toggle-featured"
                                data-id="<?= $s['id'] ?>"
                                data-featured="<?= $isFeatured ? '1' : '0' ?>"
                                title="<?= $isFeatured ? 'Remove from Featured' : 'Add to Featured' ?>">
                                <i class="fa-solid fa-star"></i>
                            </button>
                            <button class="svc-icon-btn js-edit-service"
                                data-id="<?= $s['id'] ?>"
                                data-type="<?= htmlspecialchars($s['type']) ?>"
                                data-title="<?= htmlspecialchars(html_entity_decode($s['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>"
                                data-description="<?= htmlspecialchars(html_entity_decode($s['description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>"
                                data-game="<?= htmlspecialchars($s['game'] ?? '') ?>"
                                data-price="<?= $s['price_cents'] ?>"
                                data-unit-value="<?= $s['unit_value'] ?>"
                                data-unit-type="<?= htmlspecialchars($s['unit_type']) ?>"
                                data-voice-chat="<?= !empty($s['includes_voice']) ? '1' : '0' ?>"
                                data-bs-toggle="modal" data-bs-target="#serviceModal">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <button class="svc-icon-btn delete js-delete-service" data-id="<?= $s['id'] ?>">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="svc-card-title"><?= html_entity_decode($s['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                    <?php if ($s['description']): ?>
                    <div class="svc-card-desc"><?= html_entity_decode($s['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div class="svc-card-footer">
                        <div>
                            <div class="svc-card-price"><?= function_exists('util_format_price_display') ? util_format_price_display($s['price_cents']) : number_format($s['price_cents']/100,2) ?> <?= htmlspecialchars($s['currency']) ?></div>
                            <div class="svc-card-earning"><i class="fa-solid fa-wallet fa-xs"></i> Your cut: <strong><?= $earningFormatted ?> <?= htmlspecialchars($s['currency']) ?></strong> <span>(<?= $cutPercent ?>%)</span></div>
                        </div>
                        <div class="svc-card-meta">
                            <?= (int)$s['unit_value'] ?> <?= htmlspecialchars($s['unit_type']) ?>
                            <?php if (!empty($s['includes_voice'])): ?>
                            &bull; <i class="fa-solid fa-microphone text-success"></i> Voice
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <input type="hidden" id="editServiceId" value="0">

            <!-- Header -->
            <div class="svc-modal-header">
                <div class="svc-modal-header-left">
                    <div class="svc-modal-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="svc-modal-title" id="serviceModalTitle">Add Service</div>
                        <div class="svc-modal-subtitle">Fill in your service details below</div>
                    </div>
                </div>
                <button type="button" class="svc-modal-close" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Steps -->
            <div class="svc-steps">
                <div class="svc-step active" id="stepIndicator1">
                    <div class="svc-step-dot">1</div>
                    <span>Basics</span>
                </div>
                <div class="svc-step-line" id="stepLine1"></div>
                <div class="svc-step" id="stepIndicator2">
                    <div class="svc-step-dot">2</div>
                    <span>Pricing</span>
                </div>
                <div class="svc-step-line" id="stepLine2"></div>
                <div class="svc-step" id="stepIndicator3">
                    <div class="svc-step-dot">3</div>
                    <span>Options</span>
                </div>
            </div>

            <!-- Body -->
            <div class="svc-modal-body">

                <!-- STEP 1: Basics -->
                <div id="svcStep1">
                    <div class="svc-field" id="fieldType">
                        <label>Service Type <span class="req">*</span></label>
                        <div class="svc-type-grid" id="svcTypePills">
                            <div class="svc-type-pill active" data-value="hourly"><span class="svc-tp-icon">⏰</span>Hourly</div>
                            <div class="svc-type-pill" data-value="per_game"><span class="svc-tp-icon">🎮</span>Per Game</div>
                            <div class="svc-type-pill" data-value="rank_boost"><span class="svc-tp-icon">🏆</span>Rank Boost</div>
                            <div class="svc-type-pill" data-value="coaching"><span class="svc-tp-icon">🎓</span>Coaching</div>
                            <div class="svc-type-pill" data-value="just_chat"><span class="svc-tp-icon">💬</span>Just Chat</div>
                            <div class="svc-type-pill" data-value="custom"><span class="svc-tp-icon">✨</span>Custom</div>
                        </div>
                        <select class="d-none" id="serviceType">
                            <option value="hourly">Hourly Session</option>
                            <option value="per_game">Per Game</option>
                            <option value="rank_boost">Rank Boost (together)</option>
                            <option value="coaching">Coaching</option>
                            <option value="just_chat">Just Chat / Voice Only</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div class="svc-field mt-2" id="fieldTitle">
                        <label>Title <span class="req">*</span></label>
                        <input type="text" class="form-control" id="serviceTitle" placeholder="e.g. 1 Hour Gaming Session" maxlength="80">
                        <div class="svc-field-error"><i class="fa-solid fa-circle-exclamation fa-xs"></i> Please enter a title</div>
                    </div>

                    <div class="svc-field mt-2" id="fieldDesc">
                        <label>Description <span style="color:rgba(255,255,255,.2);font-weight:500;text-transform:none;letter-spacing:0;font-size:.67rem;">(optional)</span></label>
                        <textarea class="form-control" id="serviceDescription" rows="3" placeholder="Tell clients what they'll get..."></textarea>
                    </div>
                </div>

                <!-- STEP 2: Pricing -->
                <div id="svcStep2" style="display:none">
                    <div class="svc-field-group">
                        <div class="svc-field" id="fieldGame">
                            <label>Game</label>
                            <input type="hidden" id="serviceGame" value="">
                            <div class="dropdown">
                                <button class="form-select text-start d-flex align-items-center justify-content-between" type="button" id="serviceGameButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="d-flex align-items-center gap-2" id="serviceGameButtonLabel"><span>Any / All Games</span></span>
                                </button>
                                <ul class="dropdown-menu w-100 service-game-dropdown" aria-labelledby="serviceGameButton">
                                    <li><button class="dropdown-item d-flex align-items-center gap-2 js-service-game-option active" type="button" data-value="" data-label="Any / All Games"><span>Any / All Games</span></button></li>
                                    <?php foreach ($gameLabels as $gk => $gLabel):
                                        $gIcon = $gameIcons[$gk] ?? ''; ?>
                                    <li><button class="dropdown-item d-flex align-items-center gap-2 js-service-game-option" type="button"
                                            data-value="<?= htmlspecialchars($gk, ENT_QUOTES) ?>"
                                            data-label="<?= htmlspecialchars($gLabel, ENT_QUOTES) ?>"
                                            data-icon="<?= htmlspecialchars($gIcon, ENT_QUOTES) ?>">
                                        <?php if ($gIcon): ?><img src="<?= htmlspecialchars($gIcon, ENT_QUOTES) ?>" class="service-game-option-icon" alt="" onerror="this.style.display='none'"><?php endif; ?>
                                        <span><?= htmlspecialchars($gLabel) ?></span>
                                    </button></li>
                                    <?php endforeach; ?>
                                    <?php if (empty($gameLabels)): ?>
                                    <li><span class="dropdown-item-text small text-muted">Add games in your profile first.</span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="svc-field" id="fieldPrice">
                            <label>Price (EUR) <span class="req">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="servicePrice" min="0.50" step="0.50" placeholder="5.00">
                            </div>
                            <div class="svc-field-error"><i class="fa-solid fa-circle-exclamation fa-xs"></i> Please enter a price (min. €0.50)</div>
                        </div>
                    </div>

                    <div class="svc-earning-strip" id="earningPreview">
                        <div class="svc-earn-icon"><i class="fa-solid fa-wallet"></i></div>
                        <div>
                            <div class="svc-earn-label">Your estimated earning</div>
                            <div style="display:flex;align-items:baseline;gap:.35rem;">
                                <span class="svc-earn-amount" id="earningPreviewAmount">—</span>
                                <span class="svc-earn-cut" id="earningPreviewCut"></span>
                            </div>
                        </div>
                    </div>

                    <div class="svc-field-group mt-2">
                        <div class="svc-field">
                            <label>Amount</label>
                            <input type="number" class="form-control" id="serviceUnitValue" value="1" min="1">
                        </div>
                        <div class="svc-field">
                            <label>Unit</label>
                            <select class="form-select" id="serviceUnitType">
                                <option value="hours">Hours</option>
                                <option value="games">Games</option>
                                <option value="sessions">Sessions</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Options -->
                <div id="svcStep3" style="display:none">
                    <div class="svc-toggle-row">
                        <div class="svc-toggle-info">
                            <div class="svc-toggle-icon"><i class="fa-solid fa-eye"></i></div>
                            <div>
                                <div class="svc-toggle-label">Active</div>
                                <div class="svc-toggle-desc">Visible to clients on your profile</div>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="serviceActive" checked>
                        </div>
                    </div>
                    <div class="svc-toggle-row mt-2">
                        <div class="svc-toggle-info">
                            <div class="svc-toggle-icon voice"><i class="fa-solid fa-microphone"></i></div>
                            <div>
                                <div class="svc-toggle-label">Include Voice Chat</div>
                                <div class="svc-toggle-desc">Live voice via Discord or in-game</div>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="serviceVoiceChat" checked>
                        </div>
                    </div>
                </div>

            </div><!-- /body -->

            <!-- Footer -->
            <div class="svc-modal-footer">
                <div class="svc-footer-left">
                    <button type="button" class="svc-btn-ghost" id="svcModalBack" style="display:none">
                        <i class="fa-solid fa-arrow-left fa-xs"></i> Back
                    </button>
                    <button type="button" class="svc-btn-ghost" data-bs-dismiss="modal" id="svcModalCancel">Cancel</button>
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    <button type="button" class="svc-btn-primary" id="btnNextStep">
                        Next <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </button>
                    <button type="button" class="svc-btn-primary d-none" id="btnSaveService">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveSpinner"></span>
                        <i class="fa-solid fa-check fa-xs"></i> Save Service
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Toast -->
<div id="svcToast" style="display:none">
    <i class="fa-solid fa-circle-check" id="svcToastIcon"></i>
    <span id="svcToastMsg"></span>
</div>

<script>
(function () {
    const AJAX = '<?= AJAX_URL ?>';
    const CUT_RATE = <?= isset($egirl_cut_rate) ? (float)$egirl_cut_rate : 0.60 ?>;
    const CUT_PCT  = Math.round(CUT_RATE * 100);
    const serviceGameInput = document.getElementById('serviceGame');
    const serviceGameButtonLabel = document.getElementById('serviceGameButtonLabel');

    // ── Validation ──
    function clearErrors() {
        document.querySelectorAll('#serviceModal .svc-field.has-error').forEach(f => f.classList.remove('has-error'));
    }
    function setError(fieldId) {
        const el = document.getElementById(fieldId);
        if (el) el.classList.add('has-error');
    }
    function validateStep(step) {
        clearErrors();
        let ok = true;
        if (step === 1) {
            const title = document.getElementById('serviceTitle').value.trim();
            if (!title) { setError('fieldTitle'); ok = false; }
        }
        if (step === 2) {
            const price = parseFloat(document.getElementById('servicePrice').value || 0);
            if (!price || price < 0.50) { setError('fieldPrice'); ok = false; }
        }
        return ok;
    }

    // ── Steps ──
    let currentStep = 1;
    const totalSteps = 3;

    function goToStep(step) {
        currentStep = step;
        clearErrors();
        document.getElementById('svcStep1').style.display = step === 1 ? '' : 'none';
        document.getElementById('svcStep2').style.display = step === 2 ? '' : 'none';
        document.getElementById('svcStep3').style.display = step === 3 ? '' : 'none';
        document.getElementById('btnNextStep').classList.toggle('d-none', step === totalSteps);
        document.getElementById('btnSaveService').classList.toggle('d-none', step !== totalSteps);
        document.getElementById('svcModalBack').style.display = step > 1 ? '' : 'none';
        document.getElementById('svcModalCancel').style.display = step === 1 ? '' : 'none';
        for (let i = 1; i <= totalSteps; i++) {
            const ind = document.getElementById('stepIndicator' + i);
            ind.classList.remove('active', 'done');
            if (i < step) ind.classList.add('done');
            else if (i === step) ind.classList.add('active');
            const dot = ind.querySelector('.svc-step-dot');
            if (i < step) dot.innerHTML = '<i class="fa-solid fa-check" style="font-size:.52rem"></i>';
            else dot.textContent = i;
            if (i < totalSteps) {
                document.getElementById('stepLine' + i).classList.toggle('done', i < step);
            }
        }
    }

    document.getElementById('btnNextStep').addEventListener('click', function() {
        if (!validateStep(currentStep)) {
            this.classList.add('svc-shake');
            setTimeout(() => this.classList.remove('svc-shake'), 400);
            return;
        }
        if (currentStep < totalSteps) goToStep(currentStep + 1);
    });

    document.getElementById('svcModalBack').addEventListener('click', function() {
        if (currentStep > 1) goToStep(currentStep - 1);
    });

    // Clear error on input
    document.getElementById('serviceTitle').addEventListener('input', function() {
        if (this.value.trim()) document.getElementById('fieldTitle')?.classList.remove('has-error');
    });
    document.getElementById('servicePrice').addEventListener('input', function() {
        const v = parseFloat(this.value || 0);
        if (v >= 0.50) document.getElementById('fieldPrice')?.classList.remove('has-error');
        updateEarningPreview();
    });

    // ── Type pills ──
    document.querySelectorAll('#svcTypePills .svc-type-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('#svcTypePills .svc-type-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('serviceType').value = this.dataset.value;
        });
    });

    /**
     * Feedback for an AJAX response using this page's own toast style.
     * Endpoints answer with { success, sendToast:{type,message} } or { message }.
     * Returns whether the request succeeded.
     */
    function showResponseToast(res, successMessage) {
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) { res = null; } }
        const toast = (res && res.sendToast) ? res.sendToast : null;
        const ok = !!(res && res.success) && !(toast && (toast.type === 'danger' || toast.type === 'error'));
        // Field validation answers with { validationErrors: { field: 'message' } }.
        const firstValidationError = (res && res.validationErrors && typeof res.validationErrors === 'object')
            ? Object.values(res.validationErrors)[0] : null;
        const message = (toast && toast.message) || (res && res.message) || firstValidationError
            || (ok ? successMessage : 'Something went wrong. Please try again.');
        showToast(message, ok ? 'success' : 'error');
        return ok;
    }

    function showToast(msg, type) {
        const t = document.getElementById('svcToast');
        const i = document.getElementById('svcToastIcon');
        document.getElementById('svcToastMsg').textContent = msg;
        t.className = type === 'error' ? 'error' : 'success';
        i.className = type === 'error' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-circle-check';
        t.style.display = 'flex';
        setTimeout(() => t.style.display = 'none', 3000);
    }

    function setServiceGame(value, label, icon = '') {
        serviceGameInput.value = value;
        serviceGameButtonLabel.innerHTML = icon
            ? '<img src="' + icon + '" alt="" class="service-game-selected-icon"><span>' + label + '</span>'
            : '<span>' + label + '</span>';
        document.querySelectorAll('.js-service-game-option').forEach(o => o.classList.toggle('active', o.dataset.value === value));
    }

    function updateEarningPreview() {
        const price = parseFloat(document.getElementById('servicePrice').value || 0);
        const preview = document.getElementById('earningPreview');
        if (price > 0) {
            document.getElementById('earningPreviewAmount').textContent = '€' + (price * CUT_RATE).toFixed(2);
            document.getElementById('earningPreviewCut').textContent = '(' + CUT_PCT + '% your cut)';
            preview.classList.add('visible');
        } else {
            preview.classList.remove('visible');
        }
    }

    document.querySelectorAll('.js-service-game-option').forEach(o => {
        o.addEventListener('click', function() { setServiceGame(this.dataset.value, this.dataset.label, this.dataset.icon || ''); });
    });

    function resetModal() {
        document.getElementById('editServiceId').value = '0';
        document.getElementById('serviceType').value = 'hourly';
        document.querySelectorAll('#svcTypePills .svc-type-pill').forEach(p => p.classList.toggle('active', p.dataset.value === 'hourly'));
        document.getElementById('serviceTitle').value = '';
        document.getElementById('serviceDescription').value = '';
        setServiceGame('', 'Any / All Games');
        document.getElementById('servicePrice').value = '';
        document.getElementById('serviceUnitValue').value = '1';
        document.getElementById('serviceUnitType').value = 'hours';
        document.getElementById('serviceActive').checked = true;
        document.getElementById('serviceVoiceChat').checked = true;
        document.getElementById('earningPreview').classList.remove('visible');
        goToStep(1);
    }

    document.getElementById('btnNewService')?.addEventListener('click', function() {
        document.getElementById('serviceModalTitle').textContent = 'Add Service';
        resetModal();
    });

    document.querySelectorAll('.js-edit-service').forEach(btn => {
        btn.addEventListener('click', function() {
            function decodeHtml(str) { const t = document.createElement('textarea'); t.innerHTML = str; return t.value; }
            document.getElementById('serviceModalTitle').textContent = 'Edit Service';
            document.getElementById('editServiceId').value      = this.dataset.id;
            const typeVal = this.dataset.type;
            document.getElementById('serviceType').value = typeVal;
            document.querySelectorAll('#svcTypePills .svc-type-pill').forEach(p => p.classList.toggle('active', p.dataset.value === typeVal));
            document.getElementById('serviceTitle').value       = decodeHtml(this.dataset.title);
            document.getElementById('serviceDescription').value = decodeHtml(this.dataset.description);
            const opt = document.querySelector('.js-service-game-option[data-value="' + this.dataset.game + '"]');
            opt ? setServiceGame(opt.dataset.value, opt.dataset.label, opt.dataset.icon || '') : setServiceGame('', 'Any / All Games');
            document.getElementById('servicePrice').value      = (parseInt(this.dataset.price) / 100).toFixed(2);
            document.getElementById('serviceUnitValue').value  = this.dataset.unitValue;
            document.getElementById('serviceUnitType').value   = this.dataset.unitType;
            document.getElementById('serviceVoiceChat').checked = this.dataset.voiceChat === '1';
            updateEarningPreview();
            goToStep(1);
        });
    });

    document.getElementById('btnSaveService').addEventListener('click', function() {
        const spinner = document.getElementById('saveSpinner');
        spinner.classList.remove('d-none');
        this.disabled = true;
        $.post(AJAX, {
            action:      'egirl_save_service',
            service_id:  document.getElementById('editServiceId').value,
            type:        document.getElementById('serviceType').value,
            title:       document.getElementById('serviceTitle').value,
            description: document.getElementById('serviceDescription').value,
            game:        document.getElementById('serviceGame').value,
            price_cents: Math.round(parseFloat(document.getElementById('servicePrice').value || 0) * 100),
            unit_value:  document.getElementById('serviceUnitValue').value,
            unit_type:   document.getElementById('serviceUnitType').value,
            is_active:   document.getElementById('serviceActive').checked ? 1 : 0,
            voice_chat:  document.getElementById('serviceVoiceChat').checked ? 1 : 0,
        }, function(res) {
            spinner.classList.add('d-none');
            document.getElementById('btnSaveService').disabled = false;
            if (!showResponseToast(res, 'Service saved.')) return;
            bootstrap.Modal.getInstance(document.getElementById('serviceModal'))?.hide();
            // Always return to the canonical absolute route. A relative reload
            // can resolve against a nested/legacy URL and land on the 404 page.
            const servicesUrl = (res && res.redirect)
                ? res.redirect
                : '<?= BASE_URL ?>/booster-area/egirl-services';
            setTimeout(() => window.location.assign(servicesUrl), 900);
        }).fail(function() {
            spinner.classList.add('d-none');
            document.getElementById('btnSaveService').disabled = false;
            showToast('Could not save the service. Please try again.', 'error');
        });
    });

    // Featured toggle
    let featuredCount = <?= $featuredCountCurrent ?>;
    document.querySelectorAll('.js-toggle-featured').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const isFeatured = this.dataset.featured === '1';
            const newVal = isFeatured ? 0 : 1;
            if (newVal === 1 && featuredCount >= 3) { showToast('Max. 3 services can be featured. Remove one first.', 'error'); return; }
            $.post(AJAX, { action: 'egirl_toggle_featured', service_id: id, is_featured: newVal }, function(res) {
                if (!showResponseToast(res, newVal === 1 ? 'Service featured.' : 'Service no longer featured.')) return;
                setTimeout(() => location.reload(), 900);
            }).fail(function() {
                showToast('Could not update the featured status. Please try again.', 'error');
            });
        });
    });

    // Delete service
    document.querySelectorAll('.js-delete-service').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this service?')) return;
            const id = this.dataset.id;
            $.post(AJAX, { action: 'egirl_delete_service', service_id: id }, function(res) {
                if (!showResponseToast(res, 'Service deleted.')) return;
                document.getElementById('service-card-' + id)?.remove();
            }).fail(function() {
                showToast('Could not delete the service. Please try again.', 'error');
            });
        });
    });

    // Filter tabs
    const filterTabs = document.getElementById('svcFilterTabs');
    if (filterTabs) {
        filterTabs.querySelectorAll('.svc-filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                filterTabs.querySelectorAll('.svc-filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;
                document.querySelectorAll('.svc-cat-section').forEach(sec => {
                    sec.classList.toggle('hidden', filter !== 'all' && sec.dataset.category !== filter);
                });
            });
        });
    }
})();
</script>
