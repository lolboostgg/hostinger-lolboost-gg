<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'egirls-list']) ?>

<?php // Rendered into <head> via the layout's styles section — inline in the body
      // it would only apply after the markup had already painted (FOUC). ?>
<?= $this->start('styles') ?>
<style>
/* ============================================================
   GG-GIRLS LIST  v6
============================================================ */
/* No own page background — inherit the site's so the page blends in. */
.egirls-list { color:#e5e7eb; }

/* ── HEADER — compact, shop-lol-accounts style (icon + title + description) ──
   main.css has its own .egirls-list header rule with !important background AND
   a global "body:not(.landing) header { padding-top: ... !important }" rule that
   adds vertical offset to the OUTER <header>. Matching that exact specificity
   here (body.egirls-list header) is required to win both, otherwise the padding
   stacks with the .content padding below, doubling the gap. */
body.egirls-list header {
    height: auto !important;
    min-height: 0 !important;
    background: #0e0c1c !important;
    background-image: none !important;
    border-bottom: 1px solid rgba(255,255,255,.06);
    display: block !important;
    position: relative;
    overflow: hidden;
    padding-top: 0 !important;
}
body.egirls-list header::before,
body.egirls-list header::after {
    content: none !important;
    display: none !important;
}
.egirls-list header .content {
    max-width: 1500px;
    margin: 0 auto;
    padding: calc(var(--lb-content-top, 132px) + 36px) 28px 36px;
    display: flex;
    align-items: center;
    gap: 22px;
    position: relative;
    z-index: 1;
}
.egirls-list header .hdr-icon {
    width: 74px; height: 74px; min-width: 74px;
    border-radius: 20px;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.1);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 18px 50px rgba(0,0,0,.28);
    overflow: hidden;
}
.egirls-list header .hdr-icon i { font-size: 30px; color: #e879f9; }
.egirls-list header h1 {
    margin: 0;
    font-size: 29px;
    line-height: 1.12;
    font-weight: 950;
    letter-spacing: -.03em;
    color: #fff;
    text-transform: none;
    font-family: 'Roboto', sans-serif;
    background: none;
    -webkit-text-fill-color: initial;
}
.egirls-list header p { margin: 8px 0 0; max-width: 640px; font-size: 15px; line-height: 1.5; color: #a9adc4; }
@media(max-width:768px){
    .egirls-list header .content{
        padding: calc(var(--lb-content-top, 126px) + 20px) 16px 24px;
        display: grid;
        grid-template-columns: 40px minmax(0,1fr);
        align-items: flex-start;
        gap: 10px;
    }
    .egirls-list header .hdr-icon{ width:40px; height:40px; min-width:40px; border-radius:12px; margin-top:2px; }
    .egirls-list header .hdr-icon i{ font-size:19px; }
    .egirls-list header h1{ font-size: 18px; line-height:1.22; }
    .egirls-list header p{ font-size:12.5px; margin-top:5px; }
}

/* ── MAIN LAYOUT ── */
.egirls-list .main-content {
    max-width:1500px; margin:0 auto;
    padding:24px 20px 88px;
    display:grid; grid-template-columns:340px 1fr; gap:30px;
}
@media(min-width:1700px){ .egirls-list .main-content{ max-width:1800px; } }
@media(max-width:1100px){ .egirls-list .main-content{ grid-template-columns:1fr; } }

/* ── FILTER BOX — same surface language as the GG-Girl cards (.egc) ── */
.egirls-list .filter-box {
    background:#0f0b1e;
    border:1px solid rgba(236,72,153,.18);
    border-radius:20px; padding:0;
    /* Offset by the fixed header so the panel never slides under the navbar. */
    position:sticky; top:calc(var(--lb-content-top, 0px) + 24px); height:fit-content;
    box-shadow:0 18px 50px rgba(0,0,0,.32), inset 0 1px 0 rgba(255,255,255,.02);
    /* No overflow:hidden — it would clip the custom dropdown panels. */
    overflow:visible;
    z-index:20;
}
.egirls-list .filter-box .filter-inner { padding:18px; }
@media(max-width:1100px){ .egirls-list .filter-box{ position:relative; top:0; } }

.egirls-list .filter-box .filter-header {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding-bottom:11px;
    border-bottom:1px solid rgba(236,72,153,.14);
    margin-bottom:14px;
}
.egirls-list .filter-box .filter-header h2 {
    display:flex; align-items:center; gap:9px;
    font-size:18px; font-weight:800; letter-spacing:-.3px; margin:0; color:#fce7f3;
}
.egirls-list .filter-box .filter-header h2 i { color:#ec4899; font-size:14px; }
.egirls-list .filter-header-actions { display:flex; align-items:center; gap:8px; }
.egirls-list .filter-toggle {
    display:none; width:34px; height:34px; padding:0;
    align-items:center; justify-content:center;
    border:1px solid rgba(236,72,153,.24); border-radius:10px;
    background:rgba(236,72,153,.10); color:#f9a8d4; cursor:pointer;
}
.egirls-list .filter-toggle i { transition:transform .18s ease; }
.egirls-list .filter-box.is-open .filter-toggle i { transform:rotate(180deg); }
.egirls-list .filter-box .filter-header .eg-count {
    font-size:12px; font-weight:800;
    color:#f9a8d4;
    background:linear-gradient(135deg,rgba(236,72,153,.20),rgba(168,85,247,.12));
    border:1px solid rgba(236,72,153,.35);
    border-radius:999px; padding:4px 11px; white-space:nowrap;
}

/* ── Search field + live preview ── */
.egirls-list .eg-search-wrap { position:relative; }
.egirls-list .eg-search-group { position:relative; z-index:600; }
.egirls-list .eg-search-preview {
    display:none; position:absolute; z-index:600;
    top:calc(100% + 6px); left:0; right:0;
    max-height:280px; overflow-y:auto; padding:6px;
    background:#10131f; border:1px solid rgba(236,72,153,.28);
    border-radius:14px; box-shadow:0 18px 44px rgba(0,0,0,.6);
    scrollbar-width:thin;
}
.egirls-list .eg-search-preview.is-open { display:block; }
.egirls-list .eg-preview-item {
    display:flex; align-items:center; gap:10px;
    padding:8px 10px; border-radius:10px;
    color:#fff; text-decoration:none;
    transition:background .14s;
}
.egirls-list .eg-preview-item:hover,
.egirls-list .eg-preview-item.is-active { background:rgba(236,72,153,.14); color:#fff; }
.egirls-list .eg-preview-av {
    width:34px; height:34px; border-radius:50%; object-fit:cover; flex-shrink:0;
    background:#2a1040; border:1px solid rgba(236,72,153,.28);
}
.egirls-list .eg-preview-body { min-width:0; flex:1; }
.egirls-list .eg-preview-name {
    display:block; font-size:14px; font-weight:800; line-height:1.2;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.egirls-list .eg-preview-meta {
    display:block; margin-top:2px; font-size:11.5px; font-weight:700;
    color:rgba(255,255,255,.42);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.egirls-list .eg-preview-price { font-size:13px; font-weight:900; color:#f9a8d4; flex-shrink:0; }
.egirls-list .eg-preview-empty {
    padding:12px 10px; text-align:center;
    font-size:13px; font-weight:700; color:rgba(255,255,255,.35);
}

/* Section labels */
.egirls-list .filter-box .form-group { margin-bottom:12px; }
.egirls-list .filter-box .form-group > label,
.egirls-list .filter-box .availability-title {
    display:block; margin-bottom:6px;
    font-size:11px; font-weight:900;
    text-transform:uppercase; letter-spacing:.12em;
    color:rgba(255,255,255,.38);
}

/* Input */
.egirls-list .filter-box input[type="text"] {
    display:block !important;
    width:100% !important;
    background:rgba(255,255,255,.04) !important;
    border:1px solid rgba(255,255,255,.10) !important;
    color:#fff !important;
    border-radius:11px !important;
    padding:10px 13px !important;
    font-size:14px !important;
    font-family:inherit !important;
    outline:none !important;
    transition:border-color .2s, box-shadow .2s;
    -webkit-appearance:none !important;
}
.egirls-list .filter-box input[type="text"]::placeholder { color:rgba(255,255,255,.28) !important; }
.egirls-list .filter-box input[type="text"]:focus {
    border-color:rgba(236,72,153,.45) !important;
    box-shadow:0 0 0 3px rgba(236,72,153,.10) !important;
    background:rgba(255,255,255,.06) !important;
}

/* Selects — full custom, override all global styles */
.egirls-list .filter-box .eg-select-wrap {
    position:relative;
    display:block;
}
.egirls-list .filter-box .eg-select-wrap::after {
    content:'' !important; pointer-events:none; z-index:3;
    position:absolute; right:14px; top:50%; transform:translateY(-60%);
    width:0; height:0;
    border-left:5px solid transparent;
    border-right:5px solid transparent;
    border-top:6px solid rgba(244,114,182,.65);
    display:block !important;
}
.egirls-list .filter-box .eg-select-wrap select,
.egirls-list .filter-box select {
    display:block !important;
    width:100% !important;
    background:#1e0a30 !important;
    border:1px solid rgba(168,85,247,.35) !important;
    color:#fff !important;
    border-radius:10px !important;
    padding:13px 36px 13px 16px !important;
    font-size:16px !important;
    font-family:inherit !important;
    outline:none !important;
    cursor:pointer !important;
    -webkit-appearance:none !important;
    -moz-appearance:none !important;
    appearance:none !important;
    transition:border-color .2s, background .2s, box-shadow .2s;
    box-shadow:none !important;
    line-height:1.4 !important;
}
.egirls-list .filter-box .eg-select-wrap select:hover,
.egirls-list .filter-box select:hover {
    border-color:rgba(168,85,247,.6) !important;
    background:#230d38 !important;
}
.egirls-list .filter-box .eg-select-wrap select:focus,
.egirls-list .filter-box select:focus {
    border-color:rgba(236,72,153,.6) !important;
    background:#230d38 !important;
    box-shadow:0 0 0 3px rgba(236,72,153,.12) !important;
    outline:none !important;
}
.egirls-list .filter-box select option {
    background:#1e0a30 !important;
    color:#fff !important;
}

/* Availability */
.egirls-list .availability { width:100%; margin:10px 0; padding:10px 0; border-top:1px solid rgba(255,255,255,.06); border-bottom:1px solid rgba(255,255,255,.06); }
.egirls-list .check-row { display:flex; align-items:center; gap:10px; user-select:none; cursor:pointer; color:rgba(229,231,235,.85); font-size:14px; font-weight:700; }
.egirls-list .check-row input { position:absolute; opacity:0; pointer-events:none; }
.egirls-list .check-ui {
    width:20px; height:20px; border-radius:6px; flex-shrink:0;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.14);
    display:inline-flex; align-items:center; justify-content:center; transition:.15s;
}
.egirls-list .check-row input:checked + .check-ui {
    background:linear-gradient(135deg,#ec4899,#a855f7); border-color:rgba(236,72,153,.6);
    box-shadow:0 5px 14px rgba(168,85,247,.28);
}
.egirls-list .check-row input:checked + .check-ui::after {
    content:''; width:9px; height:9px; border-radius:2px; background:#fff;
}

/* Clear Filters button — same gradient as view.php book btn */
.egirls-list .reset-filters {
    display:block; width:100%; min-height:42px;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.12); color:rgba(255,255,255,.82);
    padding:10px 16px; border-radius:11px;
    font-size:13px; font-weight:800; cursor:pointer; margin-top:14px;
    box-shadow:none; letter-spacing:.02em;
    transition:background .16s, border-color .16s, color .16s;
}
.egirls-list .reset-filters:hover {
    background:rgba(236,72,153,.12); border-color:rgba(236,72,153,.32); color:#fff;
}

/* Filter count */
.egirls-list .filter-count { font-size:12px; color:rgba(255,255,255,.32); margin-top:10px; text-align:center; font-weight:700; }

/* ── CARD GRID ── */
/* align-items:stretch (default) + full-height children keeps every card in a row
   the same height regardless of bio length or the extra "Online" badge. */
#egirls-grid {
    display:grid; grid-template-columns:1fr; gap:24px;
    align-items:stretch; overflow:visible;
    min-width:0; max-width:100%; width:100%;
    position:relative; z-index:1;
}
#egirls-grid > * { min-width:0; max-width:100%; height:100%; }
#egirls-grid .egc-wrap { display:flex; }
#egirls-grid .egc { width:100%; height:100%; display:flex; flex-direction:column; }
@media(min-width:992px)  { #egirls-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); gap:28px; } }
@media(min-width:1400px) { #egirls-grid{ grid-template-columns:repeat(3,minmax(0,1fr)); gap:32px; } }

/* ── EMPTY STATE ── */
.egc-empty { grid-column:1/-1; text-align:center; padding:80px 20px; color:rgba(255,255,255,.35); display:none; flex-direction:column; align-items:center; }
.egc-empty.visible { display:flex !important; }
.egc-empty-icon { font-size:52px; margin-bottom:16px; }
.egc-empty h4    { font-size:22px; color:rgba(255,255,255,.6); margin-bottom:8px; }
.egc-empty p     { font-size:15px; }

/* ── CUSTOM DROPDOWN (game + language) ── */
.egirls-list .eg-custom-drop {
    position: relative;
}
/* The open drop must paint above the sibling form-groups AND the card grid. */
.egirls-list .eg-custom-drop.open { z-index: 400; }
.egirls-list .filter-box .form-group:has(.eg-custom-drop.open) { position: relative; z-index: 400; }
.egirls-list .eg-drop-btn {
    display: flex !important;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: #fff !important;
    border-radius: 11px !important;
    padding: 10px 13px !important;
    font-size: 14px !important;
    font-family: inherit !important;
    cursor: pointer;
    text-align: left;
    transition: border-color .2s, box-shadow .2s;
}
.egirls-list .eg-drop-btn:hover,
.egirls-list .eg-custom-drop.open .eg-drop-btn {
    border-color: rgba(236,72,153,.42) !important;
    box-shadow: 0 0 0 3px rgba(236,72,153,.10) !important;
    background: rgba(255,255,255,.06) !important;
}
.egirls-list .eg-drop-arrow {
    margin-left: auto;
    font-size: 11px;
    color: rgba(244,114,182,.65);
    transition: transform .2s;
    flex-shrink: 0;
}
.egirls-list .eg-custom-drop.open .eg-drop-arrow { transform: rotate(180deg); }
.egirls-list .eg-drop-icon { display: flex; align-items: center; flex-shrink: 0; }
.egirls-list .eg-drop-icon img { width: 22px; height: 22px; object-fit: contain; border-radius: 4px; }
.egirls-list .eg-drop-icon.is-flag img { width: 26px; height: 18px; object-fit: cover; border-radius: 3px; }
.egirls-list .eg-drop-label { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.egirls-list .eg-drop-list {
    display: none;
    position: absolute;
    z-index: 500;
    top: calc(100% + 6px);
    left: 0; right: 0;
    background: #10131f;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 14px;
    padding: 6px;
    max-height: 240px;
    overflow-y: auto;
    box-shadow: 0 18px 44px rgba(0,0,0,.55);
    scrollbar-width: thin;
    scrollbar-color: rgba(168,85,247,.3) transparent;
}
.egirls-list .eg-custom-drop.open .eg-drop-list { display: block; }

.egirls-list .eg-drop-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: 8px;
    cursor: pointer;
    color: rgba(255,255,255,.75);
    font-size: 15px;
    font-weight: 600;
    transition: background .14s, color .14s;
}
.egirls-list .eg-drop-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.egirls-list .eg-drop-item--active { background: rgba(236,72,153,.13) !important; color: #f9a8d4 !important; }

.egirls-list .eg-drop-item-icon {
    display: flex; align-items: center; justify-content: center;
    width: 26px; flex-shrink: 0;
}
.egirls-list .eg-drop-item-icon img { width: 22px; height: 22px; object-fit: contain; border-radius: 4px; }
.egirls-list .eg-drop-item-icon--flag img { width: 26px; height: 18px; object-fit: cover; border-radius: 3px; }

/* ── Collapsible filter panel on tablet/mobile (same pattern as boosters list) ── */
@media(max-width:1100px){
    .egirls-list .main-content{ padding:20px 16px 72px; gap:20px; }
    .egirls-list .filter-box{ position:relative; top:0; width:100%; max-width:100%; }
    .egirls-list .filter-box .filter-inner{ padding:16px; }
    .egirls-list .filter-box .filter-header{ margin:0; padding:0; border:0; }
    .egirls-list .filter-toggle{ display:inline-flex; }
    /* filters-ready is set by JS so the fields stay visible without JS. */
    .egirls-list .filter-box.filters-ready:not(.is-open) .filter-fields{ display:none; }
    .egirls-list .filter-box.is-open .filter-fields{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        column-gap:12px;
        margin-top:16px;
        padding-top:16px;
        border-top:1px solid rgba(236,72,153,.12);
    }
    .egirls-list .filter-box .eg-search-group,
    .egirls-list .filter-box .availability,
    .egirls-list .filter-box .reset-filters,
    .egirls-list .filter-box .filter-count{ grid-column:1/-1; }
}
@media(max-width:620px){
    .egirls-list .filter-box.is-open .filter-fields{ grid-template-columns:1fr; }
    .egirls-list .filter-box .filter-header h2{ font-size:16px; }
    .egirls-list .filter-box .filter-header .eg-count{ font-size:11px; padding:3px 9px; }
}
</style>
<?= $this->end() ?>

<!-- ── HEADER ── -->
<header>
    <div class="content">
        <div class="hdr-icon" aria-hidden="true"><i class="fa-solid fa-heart"></i></div>
        <div>
            <h1><?= t('Meet Our Gaming Girls') ?></h1>
            <p><?= t('Book a session with our verified GG-Girls — play, vibe, and level up together.') ?></p>
        </div>
    </div>
</header>

<!-- ── MAIN ── -->
<div class="main-content">

    <!-- ── FILTER SIDEBAR ── -->
    <div class="filter-box">
        <div class="filter-inner">

            <div class="filter-header">
                <h2><i class="fa-solid fa-sliders"></i> <?= t('Filters') ?></h2>
                <div class="filter-header-actions">
                    <span class="eg-count" id="egCount">&nbsp;</span>
                    <button type="button" class="filter-toggle" id="egFilterToggle" aria-expanded="false" aria-controls="egFilterFields">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div class="filter-fields" id="egFilterFields">

            <!-- Search — live preview of matching GG-Girls while typing -->
            <div class="form-group eg-search-group">
                <label><?= t('Search') ?></label>
                <div class="eg-search-wrap">
                    <input type="text" id="egSearch" autocomplete="off" placeholder="<?= t('Username...') ?>">
                    <div class="eg-search-preview" id="egSearchPreview"></div>
                </div>
            </div>

            <!-- Availability -->
            <div class="availability">
                <div class="availability-title"><?= t('Availability') ?></div>
                <label class="check-row">
                    <input type="checkbox" id="egOnline" value="1">
                    <span class="check-ui"></span>
                    <span><?= t('Online Now') ?></span>
                </label>
            </div>

            <!-- Game — custom dropdown with icons -->
            <div class="form-group">
                <label><?= t('Game') ?></label>
                <div class="eg-custom-drop" id="gameDropWrap">
                    <button type="button" class="eg-drop-btn" id="gameDropBtn">
                        <span class="eg-drop-icon" id="gameDropIcon"></span>
                        <span class="eg-drop-label" id="gameDropLabel"><?= t('All Games') ?></span>
                        <span class="eg-drop-arrow">&#9660;</span>
                    </button>
                    <div class="eg-drop-list" id="gameDropList">
                        <div class="eg-drop-item eg-drop-item--active" data-value="" data-label="<?= t('All Games') ?>">
                            <span class="eg-drop-item-icon"></span>
                            <span><?= t('All Games') ?></span>
                        </div>
                        <?php
                        // Only games that at least one listed GG-Girl actually offers,
                        // so the filter grows automatically with the games catalogue.
                        $__filterGameOpts = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
                        $__filterLegacyIcon = [
                            'lol' => 'league-of-legends', 'val' => 'valorant', 'tft' => 'teamfight-tactics',
                        ];
                        $__filterGames = [];
                        foreach (($egirls ?? []) as $__fg) {
                            foreach (explode('|', (string)($__fg['games'] ?? '')) as $__gk) {
                                $__gk = strtolower(trim($__gk));
                                if ($__gk === '' || isset($__filterGames[$__gk])) continue;
                                $__opt = $__filterGameOpts[$__gk] ?? null;
                                $__ico = $__opt['icon'] ?? '';
                                if ($__ico === '' && isset($__filterLegacyIcon[$__gk])) {
                                    $__ico = ASSET_URL . '/website/images/icons/' . $__filterLegacyIcon[$__gk] . '.png';
                                }
                                if ($__ico === '' && function_exists('util_game_icon_url')) {
                                    $__ico = (string)util_game_icon_url($__gk);
                                }
                                $__filterGames[$__gk] = [
                                    'label' => $__opt['label'] ?? strtoupper($__gk),
                                    'icon'  => $__ico,
                                ];
                            }
                        }
                        uasort($__filterGames, fn($a, $b) => strcasecmp($a['label'], $b['label']));
                        foreach ($__filterGames as $__gk => $__gv): ?>
                        <div class="eg-drop-item" data-value="<?= htmlspecialchars($__gk, ENT_QUOTES) ?>"
                             data-label="<?= htmlspecialchars($__gv['label'], ENT_QUOTES) ?>"
                             data-img="<?= htmlspecialchars($__gv['icon'], ENT_QUOTES) ?>">
                            <span class="eg-drop-item-icon">
                                <?php if ($__gv['icon'] !== ''): ?>
                                    <img src="<?= htmlspecialchars($__gv['icon'], ENT_QUOTES) ?>" alt="" onerror="this.style.display='none'">
                                <?php endif; ?>
                            </span>
                            <span><?= htmlspecialchars($__gv['label']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="egGame" value="">
                </div>
            </div>

            <!-- Language — custom dropdown with flags -->
            <div class="form-group">
                <label><?= t('Language') ?></label>
                <div class="eg-custom-drop" id="langDropWrap">
                    <button type="button" class="eg-drop-btn" id="langDropBtn">
                        <span class="eg-drop-icon" id="langDropIcon"></span>
                        <span class="eg-drop-label" id="langDropLabel"><?= t('All Languages') ?></span>
                        <span class="eg-drop-arrow">&#9660;</span>
                    </button>
                    <div class="eg-drop-list" id="langDropList">
                        <div class="eg-drop-item eg-drop-item--active" data-value="" data-label="<?= t('All Languages') ?>">
                            <span class="eg-drop-item-icon"></span>
                            <span><?= t('All Languages') ?></span>
                        </div>
                        <?php
                        $__filterLangs = [
                            'en'=>['name'=>'English',    'img'=>'en.png'],
                            'de'=>['name'=>'Deutsch',    'img'=>'de.png'],
                            'fr'=>['name'=>'Français',  'img'=>'fr.png'],
                            'es'=>['name'=>'Español',   'img'=>'es.png'],
                            'tr'=>['name'=>'Türkçe',    'img'=>'tr.png'],
                            'pt'=>['name'=>'Português', 'img'=>'pt.png'],
                            'it'=>['name'=>'Italiano',   'img'=>'it.png'],
                            'pl'=>['name'=>'Polski',     'img'=>'pl.png'],
                            'ru'=>['name'=>'Русский',     'img'=>'ru.webp'],
                            'nl'=>['name'=>'Nederlands', 'img'=>'nl.png'],
                            'sv'=>['name'=>'Svenska',    'img'=>'sv.png'],
                            'da'=>['name'=>'Dansk',      'img'=>'da.webp'],
                            'no'=>['name'=>'Norsk',      'img'=>'no.webp'],
                            'fi'=>['name'=>'Suomi',      'img'=>'fi.webp'],
                            'cs'=>['name'=>'Čeština',    'img'=>'cz.webp'],
                            'ro'=>['name'=>'Română',     'img'=>'ro.png'],
                            'hu'=>['name'=>'Magyar',     'img'=>'hu.webp'],
                            'uk'=>['name'=>'Українська', 'img'=>'uk.png'],
                            'ar'=>['name'=>'العربية',     'img'=>'ar.png'],
                            'zh'=>['name'=>'中文',    'img'=>'chinese.png'],
                            'ja'=>['name'=>'日本語',   'img'=>'ja.webp'],
                            'ko'=>['name'=>'한국어',     'img'=>'ko.png'],
                            'el'=>['name'=>'Ελληνικά',      'img'=>'el.png'],
                            'hr'=>['name'=>'Hrvatski',   'img'=>'hr.png'],
                            'bg'=>['name'=>'Български',  'img'=>'bg.webp'],
                            'vn'=>['name'=>'Tiếng Việt', 'img'=>'vn.webp'],
                            'ph'=>['name'=>'Filipino',   'img'=>'ph.webp'],
                            'th'=>['name'=>'ภาษาไทย',       'img'=>'th.webp'],
                        ];
                        $__langImgBase = ASSET_URL . '/core/main/img/languages/';
                        foreach ($__filterLangs as $__lc => $__ld): ?>
                        <div class="eg-drop-item" data-value="<?= $__lc ?>" data-label="<?= htmlspecialchars($__ld['name'], ENT_QUOTES) ?>" data-img="<?= $__langImgBase . $__ld['img'] ?>">
                            <span class="eg-drop-item-icon eg-drop-item-icon--flag">
                                <img src="<?= $__langImgBase . $__ld['img'] ?>" alt="<?= htmlspecialchars($__ld['name'], ENT_QUOTES) ?>" onerror="this.closest('.eg-drop-item').style.display='none'">
                            </span>
                            <span><?= htmlspecialchars($__ld['name'], ENT_QUOTES) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="egLang" value="">
                </div>
            </div>

            <!-- Voice Chat — custom dropdown with icon -->
            <div class="form-group">
                <label><?= t('Voice Chat') ?></label>
                <div class="eg-custom-drop" id="voiceDropWrap">
                    <button type="button" class="eg-drop-btn" id="voiceDropBtn">
                        <span class="eg-drop-icon" id="voiceDropIcon"></span>
                        <span class="eg-drop-label" id="voiceDropLabel"><?= t('All') ?></span>
                        <span class="eg-drop-arrow">&#9660;</span>
                    </button>
                    <div class="eg-drop-list" id="voiceDropList">
                        <div class="eg-drop-item eg-drop-item--active" data-value="" data-label="<?= t('All') ?>">
                            <span class="eg-drop-item-icon"></span>
                            <span><?= t('All') ?></span>
                        </div>
                        <div class="eg-drop-item" data-value="1" data-label="<?= t('Voice Chat Only') ?>" data-fa="fa-solid fa-microphone">
                            <span class="eg-drop-item-icon eg-drop-item-icon--fa">
                                <i class="fa-solid fa-microphone" style="color:#22c55e;font-size:15px;"></i>
                            </span>
                            <span><?= t('Voice Chat Only') ?></span>
                        </div>
                    </div>
                    <input type="hidden" id="egVoice" value="">
                </div>
            </div>

            <button class="reset-filters" id="egClear"><?= t('Clear Filters') ?></button>
            <div class="filter-count" id="egFilterCount"></div>

            </div><!-- /.filter-fields -->
        </div><!-- /.filter-inner -->
    </div><!-- /.filter-box -->

    <!-- ── CARD GRID ── -->
    <div id="egirls-grid">
        <?= $this->insert('website/components/egirls/egirl-cards', ['egirls' => $egirls]) ?>

        <div class="egc-empty" id="egEmpty">
            <div class="egc-empty-icon">💜</div>
            <h4><?= t('No GG-Girls found') ?></h4>
            <p><?= t('Try adjusting your filters.') ?></p>
        </div>
    </div>

</div><!-- /.main-content -->

<?= $this->insert('website/components/get-started', ['variation' => 'two']) ?>

<?= $this->start('scripts') ?>
<script>
(function () {
    var cards     = Array.from(document.querySelectorAll('#egirls-grid .egc-wrap'));
    var empty     = document.getElementById('egEmpty');
    var countBadge= document.getElementById('egCount');
    var filterCnt = document.getElementById('egFilterCount');

    /* ── Collapsible filter panel (mobile/tablet) ── */
    var egFilterBox    = document.querySelector('.egirls-list .filter-box');
    var egFilterToggle = document.getElementById('egFilterToggle');
    if (egFilterBox && egFilterToggle) {
        egFilterBox.classList.add('filters-ready');
        egFilterToggle.addEventListener('click', function () {
            var isOpen = egFilterBox.classList.toggle('is-open');
            egFilterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    /* ── Search preview ──
       Built from the cards already on the page, so it needs no extra request. */
    var previewEl = document.getElementById('egSearchPreview');
    var previewIndex = cards.map(function (wrap) {
        var card = wrap.querySelector('.egc');
        var avatar = wrap.querySelector('.egc__avatar img');
        var flags  = Array.from(wrap.querySelectorAll('.egc__flag img'))
                          .map(function (f) { return (f.getAttribute('alt') || '').trim(); })
                          .filter(Boolean);
        return {
            name:   (wrap.querySelector('.egc__name') || {}).textContent || '',
            key:    (card && card.dataset.name) || '',
            href:   wrap.getAttribute('href') || '#',
            avatar: avatar ? avatar.getAttribute('src') : '',
            price:  ((wrap.querySelector('.egc__price') || {}).textContent || '').trim(),
            online: !!(card && card.dataset.online === '1'),
            langs:  flags.slice(0, 3).join(' · ')
        };
    });

    function closePreview() {
        if (previewEl) previewEl.classList.remove('is-open');
    }

    function renderPreview(term) {
        if (!previewEl) return;
        term = (term || '').toLowerCase().trim();
        if (term.length < 1) { closePreview(); return; }

        var hits = previewIndex.filter(function (p) { return p.key.indexOf(term) !== -1; }).slice(0, 6);
        previewEl.innerHTML = '';

        if (!hits.length) {
            var none = document.createElement('div');
            none.className = 'eg-preview-empty';
            none.textContent = <?= json_encode(t('No GG-Girls found')) ?>;
            previewEl.appendChild(none);
            previewEl.classList.add('is-open');
            return;
        }

        hits.forEach(function (p) {
            var a = document.createElement('a');
            a.className = 'eg-preview-item';
            a.href = p.href;

            if (p.avatar) {
                var img = document.createElement('img');
                img.className = 'eg-preview-av';
                img.src = p.avatar; img.alt = '';
                img.onerror = function () { this.style.display = 'none'; };
                a.appendChild(img);
            }

            var body = document.createElement('span');
            body.className = 'eg-preview-body';
            var nm = document.createElement('span');
            nm.className = 'eg-preview-name';
            nm.textContent = p.name;
            body.appendChild(nm);
            var meta = document.createElement('span');
            meta.className = 'eg-preview-meta';
            meta.textContent = [p.online ? '● Online' : '', p.langs].filter(Boolean).join('  ·  ');
            if (meta.textContent) body.appendChild(meta);
            a.appendChild(body);

            if (p.price && p.price !== '—') {
                var pr = document.createElement('span');
                pr.className = 'eg-preview-price';
                pr.textContent = p.price;
                a.appendChild(pr);
            }

            previewEl.appendChild(a);
        });

        previewEl.classList.add('is-open');
    }

    function filter() {
        var search = (document.getElementById('egSearch').value || '').toLowerCase().trim();
        var game   = (document.getElementById('egGame').value  || '').toLowerCase();
        var lang   = (document.getElementById('egLang').value  || '').toLowerCase();
        // Normalize: map short codes to full names (for DB entries that store full names)
        var langAliases = {
            'en':'english','de':'german|deutsch','fr':'french|français','es':'spanish|español',
            'tr':'turkish|türkçe','pt':'portuguese|português','it':'italian|italiano',
            'pl':'polish|polski','ru':'russian|русский','nl':'dutch|nederlands',
            'sv':'swedish|svenska','da':'danish|dansk','no':'norwegian|norsk',
            'fi':'finnish|suomi','cs':'czech|čeština','ro':'romanian|română',
            'hu':'hungarian|magyar','uk':'ukrainian|українська','ar':'arabic|العربية',
            'zh':'chinese|中文','ja':'japanese|日本語','ko':'korean|한국어',
            'el':'greek|ελληνικά','hr':'croatian|hrvatski','bg':'bulgarian|български',
            'vn':'vietnamese|tiếng việt','ph':'filipino','th':'thai|ภาษาไทย'
        };
        var voice  = document.getElementById('egVoice').value;
        var online = document.getElementById('egOnline').checked;

        var visible = 0;
        cards.forEach(function (wrap) {
            var d  = wrap.querySelector('.egc').dataset;
            var ok = true;
            if (search && !(d.name  || '').includes(search)) ok = false;
            // Exact token match — "lol" must not also match "lol-classic".
            if (game && (d.games || '').split('|').map(function (g) { return g.trim(); }).indexOf(game) === -1) ok = false;
            if (lang) {
                var dLangs = (d.langs || '').toLowerCase();
                var aliases = langAliases[lang] ? (lang + '|' + langAliases[lang]) : lang;
                var matched = aliases.split('|').some(function(a) { return dLangs.includes(a); });
                if (!matched) ok = false;
            }
            if (voice === '1' && d.voice  !== '1')           ok = false;
            if (online         && d.online !== '1')          ok = false;
            wrap.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        var none = visible === 0;
        empty.style.display = none ? 'flex' : 'none';
        empty.classList.toggle('visible', none);
        var label = visible + ' <?= t('GG-Girl') ?>' + (visible !== 1 ? 's' : '') + ' <?= t('found') ?>';
        if (countBadge) countBadge.textContent = label;
        if (filterCnt)  filterCnt.textContent  = label;
    }

    // ── Custom Dropdown logic ──
    function initCustomDrop(btnId, listId, hiddenId, iconId, labelId, isFlagDrop) {
        var btn    = document.getElementById(btnId);
        var list   = document.getElementById(listId);
        var hidden = document.getElementById(hiddenId);
        var icon   = document.getElementById(iconId);
        var label  = document.getElementById(labelId);
        var wrap   = btn ? btn.closest('.eg-custom-drop') : null;
        if (!btn || !list || !wrap) return;

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            // close all other custom drops
            document.querySelectorAll('.egirls-list .eg-custom-drop.open').forEach(function(d) {
                if (d !== wrap) d.classList.remove('open');
            });
            wrap.classList.toggle('open');
        });

        list.querySelectorAll('.eg-drop-item').forEach(function(item) {
            item.addEventListener('click', function() {
                var val = this.dataset.value || '';
                var lbl = this.dataset.label || '';
                var img = this.dataset.img   || '';
                hidden.value = val;

                // update active state
                list.querySelectorAll('.eg-drop-item').forEach(function(i) { i.classList.remove('eg-drop-item--active'); });
                this.classList.add('eg-drop-item--active');

                // update button display
                label.textContent = lbl;
                var fa = this.dataset.fa || '';
                if (img) {
                    icon.innerHTML = '<img src="' + img + '" alt="">';
                    icon.className = 'eg-drop-icon' + (isFlagDrop ? ' is-flag' : '');
                } else if (fa) {
                    icon.innerHTML = '<i class="' + fa + '" style="color:#22c55e;font-size:15px;"></i>';
                    icon.className = 'eg-drop-icon';
                } else {
                    icon.innerHTML = '';
                    icon.className = 'eg-drop-icon';
                }

                wrap.classList.remove('open');
                filter();
            });
        });
    }

    initCustomDrop('gameDropBtn', 'gameDropList', 'egGame', 'gameDropIcon', 'gameDropLabel', false);
    initCustomDrop('langDropBtn', 'langDropList', 'egLang', 'langDropIcon', 'langDropLabel', true);
    initCustomDrop('voiceDropBtn', 'voiceDropList', 'egVoice', 'voiceDropIcon', 'voiceDropLabel', false);

    // Pre-select filters from URL params (server-side state)
    <?php if (!empty($voice) && $voice === '1'): ?>
    (function() {
        var vHidden = document.getElementById('egVoice');
        if (vHidden) vHidden.value = '1';
        var vList = document.getElementById('voiceDropList');
        var vLbl  = document.getElementById('voiceDropLabel');
        var vIcon = document.getElementById('voiceDropIcon');
        if (vList) {
            vList.querySelectorAll('.eg-drop-item').forEach(function(i) { i.classList.remove('eg-drop-item--active'); });
            var vItem = vList.querySelector('[data-value="1"]');
            if (vItem) {
                vItem.classList.add('eg-drop-item--active');
                if (vLbl) vLbl.textContent = vItem.dataset.label || 'Voice Chat Only';
                var fa = vItem.dataset.fa || '';
                if (vIcon && fa) vIcon.innerHTML = '<i class="' + fa + '" style="color:#22c55e;font-size:15px;"></i>';
            }
        }
    })();
    <?php endif; ?>

    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        document.querySelectorAll('.egirls-list .eg-custom-drop.open').forEach(function(d) { d.classList.remove('open'); });
    });

    var egOnlineEl = document.getElementById('egOnline'); if (egOnlineEl) egOnlineEl.addEventListener('change', filter);

    // Sync voice filter to URL so server delivers correct cover images on reload
    function syncVoiceToUrl() {
        var v = (document.getElementById('egVoice') || {}).value || '';
        var url = new URL(window.location.href);
        if (v === '1') { url.searchParams.set('voice', '1'); }
        else { url.searchParams.delete('voice'); }
        window.history.replaceState({}, '', url.toString());
    }
    // Hook into voice dropdown item clicks
    var vList2 = document.getElementById('voiceDropList');
    if (vList2) {
        vList2.querySelectorAll('.eg-drop-item').forEach(function(item) {
            item.addEventListener('click', function() { setTimeout(syncVoiceToUrl, 0); });
        });
    }
    var t2;
    var egSearchEl = document.getElementById('egSearch');
    egSearchEl?.addEventListener('input', function () {
        renderPreview(this.value);
        clearTimeout(t2); t2 = setTimeout(filter, 280);
    });
    egSearchEl?.addEventListener('focus', function () { renderPreview(this.value); });
    egSearchEl?.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePreview();
    });
    // The document click handler above already closes the dropdowns; keep the
    // preview from closing when a result itself is clicked.
    previewEl?.addEventListener('mousedown', function (e) { e.stopPropagation(); });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.eg-search-wrap')) closePreview();
    });
    document.getElementById('egClear')?.addEventListener('click', function () {
        document.getElementById('egSearch').value = '';
        closePreview();
        // Reset custom dropdowns
        ['gameDropList','langDropList'].forEach(function(listId) {
            var list = document.getElementById(listId);
            if (!list) return;
            list.querySelectorAll('.eg-drop-item').forEach(function(i) { i.classList.remove('eg-drop-item--active'); });
            var first = list.querySelector('.eg-drop-item');
            if (first) first.classList.add('eg-drop-item--active');
        });
        var gIcon = document.getElementById('gameDropIcon'); if (gIcon) { gIcon.innerHTML=''; gIcon.className='eg-drop-icon'; }
        var lIcon = document.getElementById('langDropIcon'); if (lIcon) { lIcon.innerHTML=''; lIcon.className='eg-drop-icon'; }
        var gLbl = document.getElementById('gameDropLabel'); if (gLbl) gLbl.textContent = gLbl.closest('.eg-custom-drop').querySelector('.eg-drop-item').dataset.label;
        var lLbl = document.getElementById('langDropLabel'); if (lLbl) lLbl.textContent = lLbl.closest('.eg-custom-drop').querySelector('.eg-drop-item').dataset.label;
        document.getElementById('egGame').value = '';
        document.getElementById('egLang').value = '';
        var voice = document.getElementById('egVoice'); if (voice) voice.value = '';
        // Reset voice dropdown UI
        var vList = document.getElementById('voiceDropList');
        if (vList) {
            vList.querySelectorAll('.eg-drop-item').forEach(function(i){i.classList.remove('eg-drop-item--active');});
            var vFirst = vList.querySelector('.eg-drop-item'); if(vFirst) vFirst.classList.add('eg-drop-item--active');
        }
        var vIcon = document.getElementById('voiceDropIcon'); if(vIcon){vIcon.innerHTML='';vIcon.className='eg-drop-icon';}
        var vLbl = document.getElementById('voiceDropLabel'); if(vLbl) vLbl.textContent='<?= t("All") ?>';
        document.getElementById('egOnline').checked = false;
        filter();
    });
    filter();
})();
</script>
<?= $this->end() ?>
