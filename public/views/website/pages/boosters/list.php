<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'boosters-list']) ?>

<style>
/* ============================================================
   BOOSTERS LIST  — styled like egirls list v6
============================================================ */
.boosters-list { background:#0b0913; color:#e5e7eb; }

/* ── HEADER — compact, shop-lol-accounts style (icon + title + description) ──
   main.css has a global "body:not(.landing) header { padding-top: ... !important }"
   rule (and its own body.boosters-list header override) that adds vertical offset
   to the OUTER <header>. Matching that exact specificity here (body.boosters-list
   header) and winning by later source order is required, otherwise this padding:0
   loses and stacks with the .content padding below, doubling the gap. */
body.boosters-list header {
    min-height: 0 !important;
    height: auto !important;
    box-sizing: border-box !important;
    background: #0e0c1c !important;
    border-bottom: 1px solid rgba(255,255,255,.06);
    display: block !important;
    position: relative;
    overflow: hidden;
    padding: 0 !important;
    padding-top: 0 !important;
}
.boosters-list header .content {
    max-width: 1500px !important;
    margin: 0 auto !important;
    padding: calc(var(--lb-content-top, 132px) + 36px) 28px 36px !important;
    display: flex;
    align-items: center;
    gap: 22px;
    position: relative;
    z-index: 2;
}
.boosters-list header .hdr-icon {
    width: 74px; height: 74px; min-width: 74px;
    border-radius: 20px;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.1);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 18px 50px rgba(0,0,0,.28);
    overflow: hidden;
}
.boosters-list header .hdr-icon i { font-size: 30px; color: #7c6cff; }
.boosters-list header h1 {
    margin: 0 !important;
    font-size: 29px !important;
    line-height: 1.12 !important;
    font-weight: 950;
    letter-spacing: -.03em;
    color: #fff;
    text-transform: none;
    font-family: 'Roboto', sans-serif;
    background: none;
    -webkit-text-fill-color: initial;
}
.boosters-list header p {
    margin: 8px 0 0;
    max-width: 640px;
    font-size: 15px;
    line-height: 1.5;
    color: #a9adc4;
}
@media(max-width:768px){
    .boosters-list header .content{
        padding: calc(var(--lb-content-top, 126px) + 20px) 16px 24px !important;
        display: grid;
        grid-template-columns: 40px minmax(0,1fr);
        align-items: flex-start;
        gap: 10px;
    }
    .boosters-list header .hdr-icon{ width:40px; height:40px; min-width:40px; border-radius:12px; margin-top:2px; }
    .boosters-list header .hdr-icon i{ font-size:19px; }
    .boosters-list header h1{ font-size: 18px !important; line-height:1.22 !important; }
    .boosters-list header p{ font-size:12.5px; margin-top:5px; }
}

/* ── MAIN LAYOUT ── */
.boosters-list .main-content {
    max-width: none !important;
    width: 100% !important;
    margin: 0 !important;
    padding: clamp(24px, 2vw, 36px) 3.75vw 88px !important;
    display:grid;
    grid-template-columns:340px minmax(0, 1fr);
    gap:30px;
}
@media(max-width:1100px){
    .boosters-list .main-content{
        grid-template-columns:1fr;
        padding:56px 16px 72px !important;
    }
}

/* ── FILTER BOX ── */
.boosters-list .filter-box {
    background:#0f0b1e;
    border:1px solid rgba(99,102,241,.18);
    border-radius:18px; padding:0;
    position:sticky; top:calc(var(--lb-content-top, 0px) + 24px); height:fit-content;
    box-shadow:0 18px 50px rgba(0,0,0,.32), inset 0 1px 0 rgba(255,255,255,.02);
    overflow:visible;
}
/* Keep the top-bar gradient using a pseudo instead of overflow:hidden */
.boosters-list .filter-box::before {
    display:none;
}
.boosters-list .filter-box .filter-inner { padding:20px; }
@media(max-width:1100px){ .boosters-list .filter-box{ position:relative; top:0; } }

.boosters-list .filter-box .filter-header {
    display:flex; align-items:center; justify-content:space-between;
    padding-bottom:14px;
    border-bottom:1px solid rgba(99,102,241,.12);
    margin-bottom:18px;
}
.boosters-list .filter-box .filter-header h2 {
    display:flex; align-items:center; gap:10px;
    font-size:22px; letter-spacing:-.2px; margin:0; color:#fff;
}
.boosters-list .filter-box .filter-header h2 i { color:#8b8cff; font-size:16px; }
.boosters-list .filter-header-actions { display:flex; align-items:center; gap:8px; }
.boosters-list .filter-toggle {
    display:none; width:34px; height:34px; padding:0;
    align-items:center; justify-content:center;
    border:1px solid rgba(129,140,248,.2); border-radius:10px;
    background:rgba(99,102,241,.09); color:#aeb7ff; cursor:pointer;
}
.boosters-list .filter-toggle i { transition:transform .18s ease; }
.boosters-list .filter-box.is-open .filter-toggle i { transform:rotate(180deg); }
.boosters-list .filter-box .filter-header .eg-count {
    font-size:13px; font-weight:700;
    color:rgba(129,140,248,.7);
    background:rgba(99,102,241,.12); border:1px solid rgba(99,102,241,.22);
    border-radius:999px; padding:4px 12px;
}

/* Section labels */
.boosters-list .filter-box .form-group { margin-bottom:16px; }
.boosters-list .filter-box .form-group > label,
.boosters-list .filter-box .availability-title {
    display:block; margin-bottom:8px;
    font-size:13px; font-weight:800;
    text-transform:uppercase; letter-spacing:.08em;
    color:rgba(129,140,248,.55);
}

/* Input */
.boosters-list .filter-box input[type="text"] {
    display:block !important; width:100% !important;
    background:#0b0918 !important;
    border:1px solid rgba(99,102,241,.24) !important;
    color:#fff !important; border-radius:10px !important;
    padding:13px 16px !important; font-size:16px !important;
    font-family:inherit !important; outline:none !important;
    transition:border-color .2s, box-shadow .2s; -webkit-appearance:none !important;
}
.boosters-list .filter-box input[type="text"]::placeholder { color:rgba(255,255,255,.28) !important; }
.boosters-list .filter-box input[type="text"]:focus {
    border-color:rgba(129,140,248,.48) !important;
    box-shadow:0 0 0 3px rgba(99,102,241,.1) !important;
    background:#0d0a20 !important;
}

/* Availability */
.boosters-list .availability { width:100%; margin:14px 0; padding:12px 0; border-top:1px solid rgba(99,102,241,.08); border-bottom:1px solid rgba(99,102,241,.08); }
.boosters-list .check-row { display:flex; align-items:center; gap:10px; user-select:none; cursor:pointer; color:rgba(229,231,235,.9); font-size:16px; font-weight:600; }
.boosters-list .check-row input { position:absolute; opacity:0; pointer-events:none; }
.boosters-list .check-ui {
    width:20px; height:20px; border-radius:5px; flex-shrink:0;
    background:rgba(99,102,241,.07); border:1px solid rgba(99,102,241,.25);
    display:inline-flex; align-items:center; justify-content:center; transition:.15s;
}
.boosters-list .check-row input:checked + .check-ui {
    background:rgba(124,58,237,.2); border-color:rgba(124,58,237,.55);
    box-shadow:0 0 0 3px rgba(124,58,237,.1);
}
.boosters-list .check-row input:checked + .check-ui::after {
    content:''; width:10px; height:10px; border-radius:2px; background:rgba(255,255,255,.9);
}

/* Clear Filters button */
.boosters-list .reset-filters {
    display:block; width:100%; min-height:48px;
    background:linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
    border:0; color:#fff; padding:12px 18px; border-radius:999px;
    font-size:16px; font-weight:700; cursor:pointer; margin-top:18px;
    box-shadow:0 6px 20px rgba(99,102,241,.3); letter-spacing:.02em;
    transition:opacity .15s, box-shadow .2s;
}
.boosters-list .reset-filters:hover { opacity:.9; box-shadow:0 10px 28px rgba(99,102,241,.45); }
.boosters-list .filter-count { font-size:12px; color:rgba(129,140,248,.45); margin-top:10px; text-align:center; font-weight:600; }

/* ── CUSTOM DROPDOWN ── */
/* Each form-group gets stacking context so open dropdown floats above next sibling */
.boosters-list .filter-box .form-group { position:relative; z-index:1; }
.boosters-list .filter-box .form-group:nth-child(1) { z-index:60; }
.boosters-list .filter-box .form-group:nth-child(2) { z-index:50; }
.boosters-list .filter-box .form-group:nth-child(3) { z-index:40; }
.boosters-list .filter-box .form-group:nth-child(4) { z-index:30; }
.boosters-list .filter-box .form-group:nth-child(5) { z-index:20; }
.boosters-list .filter-box .form-group:nth-child(6) { z-index:10; }
/* Open dropdown gets highest z-index via JS — see script below */
.boosters-list .eg-custom-drop { position:relative; }
.boosters-list .eg-custom-drop.open { z-index:1000 !important; }
.boosters-list .eg-drop-btn {
    display:flex !important; align-items:center; gap:10px; width:100%;
    background:#0b0918 !important; border:1px solid rgba(99,102,241,.24) !important;
    color:#fff !important; border-radius:10px !important; padding:13px 16px !important;
    font-size:16px !important; font-family:inherit !important;
    cursor:pointer; text-align:left; transition:border-color .2s, box-shadow .2s;
}

@media(max-width:1100px){
    .boosters-list .main-content{
        padding:20px 16px 72px !important;
        gap:20px;
    }
    .boosters-list .filter-box{
        position:relative;
        top:0;
        width:100%;
        max-width:100%;
    }
    .boosters-list .filter-box .filter-inner{ padding:16px; }
    .boosters-list .filter-box .filter-header{
        margin:0;
        padding:0;
        border:0;
    }
    .boosters-list .filter-toggle{ display:inline-flex; }
    .boosters-list .filter-box.filters-ready:not(.is-open) .filter-fields{ display:none; }
    .boosters-list .filter-box.is-open .filter-fields{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        column-gap:12px;
        margin-top:16px;
        padding-top:16px;
        border-top:1px solid rgba(129,140,248,.1);
    }
    .boosters-list .filter-box .availability,
    .boosters-list .filter-box .reset-filters,
    .boosters-list .filter-box .filter-count{ grid-column:1/-1; }
}
@media(max-width:620px){
    .boosters-list .filter-box.is-open .filter-fields{ grid-template-columns:1fr; }
    .boosters-list .filter-box .availability,
    .boosters-list .filter-box .reset-filters,
    .boosters-list .filter-box .filter-count{ grid-column:auto; }
    .boosters-list .filter-box .filter-header h2{ font-size:18px; }
    .boosters-list .filter-box .filter-header .eg-count{
        max-width:122px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
}
.boosters-list .eg-drop-btn:hover,
.boosters-list .eg-custom-drop.open .eg-drop-btn {
    border-color:rgba(129,140,248,.48) !important;
    box-shadow:0 0 0 3px rgba(99,102,241,.1) !important;
    background:#0d0a20 !important;
}
.boosters-list .eg-drop-arrow { margin-left:auto; font-size:11px; color:rgba(129,140,248,.65); transition:transform .2s; flex-shrink:0; }
.boosters-list .eg-custom-drop.open .eg-drop-arrow { transform:rotate(180deg); }
.boosters-list .eg-drop-icon { display:flex; align-items:center; flex-shrink:0; }
.boosters-list .eg-drop-icon img { width:22px; height:22px; object-fit:contain; border-radius:4px; }
.boosters-list .eg-drop-icon.is-flag img { width:26px; height:18px; object-fit:cover; border-radius:3px; }
.boosters-list .eg-drop-label { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.boosters-list .eg-drop-list {
    display:none; position:absolute; z-index:9999;
    top:calc(100% + 6px); left:0; right:0;
    background:#110d22; border:1px solid rgba(99,102,241,.3);
    border-radius:14px; padding:6px; max-height:280px; overflow-y:auto;
    box-shadow:0 20px 60px rgba(0,0,0,.85), 0 0 0 1px rgba(99,102,241,.1);
    scrollbar-width:thin; scrollbar-color:rgba(99,102,241,.3) transparent;
}
.boosters-list .eg-custom-drop.open .eg-drop-list { display:block; }
.boosters-list .eg-drop-item {
    display:flex; align-items:center; gap:10px; padding:9px 12px;
    border-radius:8px; cursor:pointer; color:rgba(255,255,255,.75);
    font-size:15px; font-weight:600; transition:background .14s, color .14s;
}
.boosters-list .eg-drop-item:hover { background:rgba(99,102,241,.15); color:#fff; }
.boosters-list .eg-drop-item--active { background:rgba(124,58,237,.15) !important; color:#818cf8 !important; }
.boosters-list .eg-drop-item-icon { display:flex; align-items:center; justify-content:center; width:26px; flex-shrink:0; }
.boosters-list .eg-drop-item-icon img { width:22px; height:22px; object-fit:contain; border-radius:4px; }
.boosters-list .eg-drop-item-icon--flag img { width:26px; height:18px; object-fit:cover; border-radius:3px; }

/* ── CARD GRID ── */
#boosters {
    display:grid; grid-template-columns:1fr; gap:24px;
    align-items:start; min-width:0; max-width:100%; width:100%;
}
#boosters > * { min-width:0; max-width:100%; }
@media(min-width:992px)  { #boosters{ grid-template-columns:repeat(2,minmax(0,1fr)); gap:28px; } }
@media(min-width:1400px) { #boosters{ grid-template-columns:repeat(3,minmax(0,1fr)); gap:32px; } }

/* ── EMPTY STATE ── */
.bc-empty { grid-column:1/-1; text-align:center; padding:80px 20px; color:rgba(255,255,255,.35); display:none; flex-direction:column; align-items:center; }
.bc-empty.visible { display:flex !important; }
.bc-empty-icon { font-size:52px; margin-bottom:16px; }
.bc-empty h4   { font-size:22px; color:rgba(255,255,255,.6); margin-bottom:8px; }
.bc-empty p    { font-size:15px; }

/* ── SPINNER ── */
#loading-spinner { grid-column:1/-1; display:none; justify-content:center; margin-top:20px; }
#loading-spinner .loader {
    width:44px; height:44px; border-radius:50%;
    border:3px solid rgba(255,255,255,.14);
    border-top-color:rgba(124,58,237,.9);
    animation:bSpin .9s linear infinite;
}
@keyframes bSpin { to{ transform:rotate(360deg); } }

.boosters-list .booster-pagination {
    grid-column:2;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    min-height:42px;
    margin-top:6px;
}
.boosters-list .booster-pagination:empty { display:none; }
.boosters-list .booster-pagination button {
    min-width:38px;
    height:38px;
    padding:0 11px;
    border:1px solid rgba(99,102,241,.2);
    border-radius:11px;
    background:#0f0b1e;
    color:rgba(255,255,255,.65);
    font:800 13px/1 inherit;
    cursor:pointer;
    transition:border-color .15s,background .15s,color .15s,transform .15s;
}
.boosters-list .booster-pagination button:hover:not(:disabled) {
    border-color:rgba(129,140,248,.5);
    background:rgba(99,102,241,.13);
    color:#fff;
    transform:translateY(-1px);
}
.boosters-list .booster-pagination button.active {
    border-color:transparent;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    color:#fff;
    box-shadow:0 6px 18px rgba(99,102,241,.28);
}
.boosters-list .booster-pagination button:disabled {
    opacity:.3;
    cursor:default;
}
.boosters-list .booster-pagination .page-dots {
    min-width:24px;
    color:rgba(255,255,255,.3);
    text-align:center;
}
@media(max-width:1100px){
    .boosters-list .booster-pagination{ grid-column:1; }
}
@media(max-width:520px){
    .boosters-list .booster-pagination{ gap:5px; }
    .boosters-list .booster-pagination button{
        min-width:34px;
        height:34px;
        padding:0 8px;
        border-radius:9px;
        font-size:12px;
    }
}

/* ═══════════════════════════════════════════════════════════
   BOOSTER CARD — redesigned to match egirl card layout
   ═══════════════════════════════════════════════════════════ */

.cover-link {
    display:block; text-decoration:none; color:inherit;
    min-width:0; max-width:100%; width:100%;
}
.cover-link:hover { text-decoration:none; color:inherit; }

.booster-card {
    display:flex; flex-direction:column;
    background:#0f0b1e;
    border:1px solid rgba(99,102,241,.18);
    border-radius:20px; overflow:visible;
    position:relative; height:100%;
    min-width:0; max-width:100%;
    transition:transform .28s cubic-bezier(.22,1,.36,1), border-color .25s, box-shadow .25s;
}
.cover-link:hover .booster-card {
    transform:translateY(-7px);
    border-color:rgba(99,102,241,.5);
    box-shadow:0 22px 52px rgba(99,102,241,.2), 0 0 0 1px rgba(124,58,237,.12);
}

/* Cover */
.booster-card .cover {
    position:relative; width:100%; height:160px; flex-shrink:0;
    border-radius:20px 20px 0 0; overflow:hidden;
    background-size:cover; background-position:center top; background-color:#1a0530;
    transition:background-position .45s ease;
}
.cover-link:hover .booster-card .cover { background-position:center 20%; }
.booster-card .cover::after {
    content:''; position:absolute; inset:0; z-index:1; pointer-events:none;
    background:linear-gradient(to bottom, rgba(15,11,30,0) 15%, rgba(15,11,30,.55) 100%);
}

/* Status pill — top left of cover (like egirls) */
.booster-card .cover-status {
    position:absolute; top:12px; left:12px; z-index:5;
    display:inline-flex; align-items:center; gap:7px;
    padding:6px 15px; border-radius:999px;
    font-size:14px; font-weight:700; letter-spacing:.03em;
    backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
    white-space:nowrap;
}
.booster-card .cover-status.online  { background:rgba(34,197,94,.15); border:1px solid rgba(34,197,94,.35); color:#4ade80; }
.booster-card .cover-status.offline { background:rgba(0,0,0,.50); border:1px solid rgba(255,255,255,.1); color:rgba(255,255,255,.4); }
.booster-card .cover-status .sdot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.booster-card .cover-status.online  .sdot { background:#22c55e; box-shadow:0 0 0 2px rgba(34,197,94,.25); animation:bcPulse 2s ease-in-out infinite; }
.booster-card .cover-status.offline .sdot { background:rgba(255,255,255,.25); }

/* Games icons + Rank — top right of cover, stacked column: games on top, rank below */
.booster-card .cover-games {
    position:absolute; top:12px; right:12px; z-index:5;
    display:flex; flex-direction:column; align-items:flex-end; gap:6px;
}
/* Rank box */
.booster-card .rank-box {
    width:50px; height:50px; border-radius:12px;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
    display:flex; align-items:center; justify-content:center;
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    transition:border-color .15s;
}
.cover-link:hover .booster-card .rank-box { border-color:rgba(99,102,241,.5); }
.booster-card .rank-box .rank_icon { width:38px; height:38px; object-fit:contain; display:block; }

/* Game icon pills */
.booster-card .cover-game-icons { display:flex; align-items:center; padding-left:8px; }
.booster-card .cover-game-icon {
    width:34px; height:34px; border-radius:50%;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
    display:inline-flex; align-items:center; justify-content:center;
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    overflow:hidden; transition:border-color .15s;
}
.booster-card .cover-game-icon + .cover-game-icon,
.booster-card .cover-game-more { margin-left:-8px; }
.booster-card .cover-game-icon:first-child { z-index:8; border-color:rgba(129,140,248,.7); box-shadow:0 0 0 2px rgba(10,8,20,.8); }
.booster-card .cover-game-icon:nth-child(2){z-index:7}.booster-card .cover-game-icon:nth-child(3){z-index:6}.booster-card .cover-game-icon:nth-child(4){z-index:5}
.booster-card .cover-game-more {
    position:relative; z-index:4; width:34px; height:34px; border-radius:50%;
    display:inline-flex; align-items:center; justify-content:center;
    background:#171329; border:1px solid rgba(255,255,255,.18); color:#c4b5fd;
    box-shadow:0 0 0 2px rgba(10,8,20,.85); font-size:11px; font-weight:900;
}
.cover-link:hover .booster-card .cover-game-icon { border-color:rgba(99,102,241,.4); }
.booster-card .cover-game-icon img { width:22px; height:22px; object-fit:contain; display:block; }

/* Tooltips — shown/hidden via JS, not CSS :hover */
.booster-card .champs-tooltip,
.booster-card .langs-tooltip {
    display:none;
    position:absolute;
    bottom:calc(100% + 10px);
    left:50%; transform:translateX(-50%);
    background:linear-gradient(160deg, #1a0f35 0%, #0f0b22 100%);
    border:1px solid rgba(99,102,241,.4);
    border-radius:14px;
    padding:12px 14px;
    z-index:200;
    min-width:180px;
    box-shadow:0 16px 48px rgba(0,0,0,.8), 0 0 0 1px rgba(99,102,241,.08);
    white-space:nowrap;
    pointer-events:auto;
    /* Arrow pointing down */
}
.booster-card .champs-tooltip::after,
.booster-card .langs-tooltip::after {
    content:'';
    position:absolute;
    bottom:-6px; left:50%; transform:translateX(-50%);
    width:10px; height:10px;
    background:#1a0f35;
    border-right:1px solid rgba(99,102,241,.4);
    border-bottom:1px solid rgba(99,102,241,.4);
    transform:translateX(-50%) rotate(45deg);
}

/* Avatar */
.booster-card .avatar {
    position:absolute; top:116px; left:18px; z-index:10;
    width:88px; height:88px; border-radius:50%;
    border:4px solid #0f0b1e; overflow:visible; background:#2a1040;
    box-shadow:0 6px 20px rgba(0,0,0,.65), 0 0 0 1px rgba(99,102,241,.3);
    transition:box-shadow .25s;
}
.cover-link:hover .booster-card .avatar { box-shadow:0 8px 28px rgba(0,0,0,.65), 0 0 0 2px rgba(99,102,241,.55); }
.booster-card .avatar img { width:100%; height:100%; object-fit:cover; object-position:center top; display:block; border-radius:50%; }

/* Avatar online dot */
.booster-card .booster-online-dot {
    position:absolute; bottom:4px; right:4px;
    width:16px; height:16px; border-radius:50%;
    border:3px solid #0f0b1e; z-index:11;
}
.booster-card .booster-online-dot.online  { background:#22c55e; }
.booster-card .booster-online-dot.offline { background:#4b5563; }

/* Details body */
.booster-card .details {
    padding:54px 20px 20px;
    display:flex; flex-direction:column; gap:10px; flex:1;
    border-radius:0 0 20px 20px; overflow:visible;
}

/* Top — name + status */
.booster-card .details .top { display:flex; flex-direction:column; gap:6px; }

.booster-card h5 {
    margin:0; display:flex; align-items:center; gap:8px; flex-wrap:wrap; min-width:0;
    font-size:22px; font-weight:800; color:#e0e7ff; letter-spacing:-.3px; line-height:1.2;
}
.booster-card .name-text { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0; }
.booster-card .verify-icon { color:#818cf8; font-size:18px; filter:drop-shadow(0 0 5px rgba(99,102,241,.5)); flex-shrink:0; }

/* Rating badge */
.booster-card .rating-badge {
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(0,0,0,.4); border:1px solid rgba(255,255,255,.08);
    border-radius:10px; padding:4px 11px;
    font-size:15px; font-weight:700; color:#e0e7ff; flex-shrink:0;
}
.booster-card .rating-badge img { width:15px; height:15px; flex-shrink:0; }
.booster-card .review-count { color:rgba(255,255,255,.4); font-weight:500; }

/* Online badge */
.booster-card h6 { margin:0; }
.booster-card .booster-online-badge {
    display:inline-flex; align-items:center; gap:7px;
    padding:5px 14px; border-radius:999px;
    font-size:14px; font-weight:700; align-self:flex-start;
}
.booster-card .booster-online-badge.online  { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#4ade80; }
.booster-card .booster-online-badge.offline { background:rgba(0,0,0,.35); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.35); }
.booster-card .booster-online-badge .dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.booster-card .booster-online-badge.online  .dot { background:#22c55e; box-shadow:0 0 0 2px rgba(34,197,94,.25); animation:bcPulse 2s ease-in-out infinite; }
.booster-card .booster-online-badge.offline .dot { background:rgba(255,255,255,.25); }
@keyframes bcPulse {
    0%,100% { box-shadow:0 0 0 2px rgba(34,197,94,.25); }
    50%      { box-shadow:0 0 0 5px rgba(34,197,94,.06); }
}

/* Mid — role icons */
.booster-card .mid { display:flex; align-items:center; flex-wrap:wrap; gap:6px; }
.booster-card .role-icon {
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:9px;
    background:rgba(10,10,24,.45); border:1px solid rgba(255,255,255,.13);
    box-shadow:0 2px 8px rgba(0,0,0,.45); overflow:hidden;
}
.booster-card .role-icon img { width:22px; height:22px; object-fit:contain; display:block; }

/* Bottom — champions + languages */
.booster-card .bottom {
    display:flex; align-items:center; justify-content:space-between;
    gap:10px; flex-wrap:wrap; margin-top:auto;
    padding-top:12px; border-top:1px solid rgba(255,255,255,.06);
}

/* Champions */
.booster-card .champions { display:flex; align-items:center; flex-wrap:wrap; gap:5px; }
.booster-card .champion-icon {
    width:34px; height:34px; border-radius:9px;
    background:rgba(10,10,24,.45); border:1px solid rgba(255,255,255,.13);
    box-shadow:0 2px 8px rgba(0,0,0,.45); padding:3px; object-fit:contain;
}
.booster-card .more-champions-icon {
    position:relative; display:inline-flex; align-items:center;
    background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3);
    border-radius:8px; padding:4px 10px; font-size:13px; font-weight:700;
    color:rgba(129,140,248,.9); cursor:default;
    transition:background .15s, border-color .15s;
}
.booster-card .more-champions-icon:hover {
    background:rgba(99,102,241,.25); border-color:rgba(99,102,241,.5);
}

/* Languages */
.booster-card .languages { display:flex; align-items:center; flex-wrap:wrap; gap:5px; }
.booster-card .lang-icon {
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:9px;
    background:rgba(10,10,24,.45); border:1px solid rgba(255,255,255,.13);
    box-shadow:0 2px 8px rgba(0,0,0,.45); overflow:hidden;
}
.booster-card .lang-icon img { width:24px; height:17px; object-fit:cover; display:block; border-radius:3px; }
.booster-card .more-lang-icon {
    position:relative; display:inline-flex; align-items:center;
    background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3);
    border-radius:8px; padding:4px 10px; font-size:13px; font-weight:700;
    color:rgba(129,140,248,.9); cursor:default;
    transition:background .15s, border-color .15s;
}
.booster-card .more-lang-icon:hover {
    background:rgba(99,102,241,.25); border-color:rgba(99,102,241,.5);
}

/* ── GLOBAL FLOATING TOOLTIP ── */
#bc-global-tooltip {
    display:none;
    position:fixed;
    z-index:99999;
    pointer-events:none;
    background:linear-gradient(160deg,#1c1040 0%,#120828 100%);
    border:1px solid rgba(99,102,241,.5);
    border-radius:14px;
    padding:12px 14px;
    min-width:260px;
    max-width:320px;
    box-shadow:0 20px 60px rgba(0,0,0,.85), 0 0 0 1px rgba(99,102,241,.1);
    transform:translateY(-8px);
    transition:opacity .15s ease, transform .15s ease;
    opacity:0;
}
#bc-global-tooltip.is-visible {
    display:block;
    opacity:1;
    transform:translateY(0);
}
/* Arrow */
#bc-global-tooltip::after {
    content:'';
    position:absolute;
    bottom:-7px; left:50%; transform:translateX(-50%) rotate(45deg);
    width:12px; height:12px;
    background:#1c1040;
    border-right:1px solid rgba(99,102,241,.5);
    border-bottom:1px solid rgba(99,102,241,.5);
}
#bc-global-tooltip .bc-tt-title {
    display:block; font-size:10px; font-weight:800;
    text-transform:uppercase; letter-spacing:.12em;
    color:rgba(129,140,248,.55); margin-bottom:10px;
    padding-bottom:8px; border-bottom:1px solid rgba(99,102,241,.12);
}
#bc-global-tooltip .bc-tt-list {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:5px;
    max-height:min(240px,45vh);
    overflow-y:auto;
    overscroll-behavior:contain;
    padding-right:4px;
    scrollbar-width:thin;
    scrollbar-color:rgba(129,140,248,.55) rgba(255,255,255,.04);
}
#bc-global-tooltip.is-below::after{top:-7px;bottom:auto;border:0;border-left:1px solid rgba(99,102,241,.5);border-top:1px solid rgba(99,102,241,.5)}
#bc-global-tooltip .bc-tt-list::-webkit-scrollbar{width:6px}
#bc-global-tooltip .bc-tt-list::-webkit-scrollbar-thumb{background:rgba(129,140,248,.55);border-radius:999px}
#bc-global-tooltip .bc-tt-item {
    display:flex; align-items:center; gap:10px;
    font-size:13px; font-weight:600; color:rgba(220,220,255,.9);
    padding:4px 5px; border-radius:8px;
}
#bc-global-tooltip .bc-tt-item img {
    width:28px; height:28px; border-radius:8px;
    object-fit:contain; background:rgba(0,0,0,.35);
    padding:2px; border:1px solid rgba(255,255,255,.08);
}
#bc-global-tooltip .bc-tt-item span { line-height:1; }

.no-boosters { grid-column:1/-1; text-align:center; padding:60px 20px; color:rgba(255,255,255,.35); font-size:17px; }

/* ── Val rank badge in mid section ── */
.booster-card .bc-val-rank-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:8px;
    font-size:12px; font-weight:800; letter-spacing:.05em; text-transform:uppercase;
    white-space:nowrap;
}
.booster-card .bc-val-rank-badge img {
    width:20px; height:20px; object-fit:contain; flex-shrink:0;
}
/* Tier colors */
.booster-card .bc-val-rank-badge.tier-0                        { background:rgba(150,150,150,.15); border:1px solid rgba(150,150,150,.3); color:rgba(200,200,200,.7); }
.booster-card .bc-val-rank-badge.tier-1                        { background:rgba(109,99,94,.2);   border:1px solid rgba(109,99,94,.4);   color:#a0938c; }
.booster-card .bc-val-rank-badge.tier-2                        { background:rgba(205,127,50,.15); border:1px solid rgba(205,127,50,.35); color:#cd7f32; }
.booster-card .bc-val-rank-badge.tier-3                        { background:rgba(192,192,192,.15);border:1px solid rgba(192,192,192,.35);color:#c0c0c0; }
.booster-card .bc-val-rank-badge.tier-4                        { background:rgba(255,215,0,.12);  border:1px solid rgba(255,215,0,.3);   color:#ffd700; }
.booster-card .bc-val-rank-badge.tier-5                        { background:rgba(0,200,150,.12);  border:1px solid rgba(0,200,150,.3);   color:#00c896; }
.booster-card .bc-val-rank-badge.tier-6                        { background:rgba(100,150,255,.12);border:1px solid rgba(100,150,255,.3); color:#6496ff; }
.booster-card .bc-val-rank-badge.tier-7                        { background:rgba(180,100,255,.12);border:1px solid rgba(180,100,255,.3); color:#b464ff; }
.booster-card .bc-val-rank-badge.tier-8                        { background:rgba(255,80,80,.12);  border:1px solid rgba(255,80,80,.3);   color:#ff5050; }
.booster-card .bc-val-rank-badge.tier-9                        { background:rgba(255,180,0,.15);  border:1px solid rgba(255,180,0,.4);   color:#ffb400; }
.booster-card .bc-val-rank-badge.tier-tft                      { background:rgba(32,191,191,.12); border:1px solid rgba(32,191,191,.3);  color:#20bfbf; }

/* Val rank text in rank-box */
.booster-card .rank-box .rank-text-badge {
    font-size:9px; font-weight:900; text-transform:uppercase;
    letter-spacing:.05em; text-align:center; line-height:1.2;
    padding:2px 4px; border-radius:5px; word-break:break-word;
}
.booster-card .rank-box .rank-text-badge.val { color:#ff4655; }

/* Game tags (fallback) */
.booster-card .bc-game-tag {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:8px; font-size:12px; font-weight:700;
    letter-spacing:.04em; text-transform:uppercase;
}
.booster-card .bc-game-tag img { width:16px; height:16px; object-fit:contain; }
.booster-card .bc-game-tag.val { background:rgba(255,70,85,.12); border:1px solid rgba(255,70,85,.3); color:#ff4655; }
.booster-card .bc-game-tag.tft { background:rgba(32,191,191,.12); border:1px solid rgba(32,191,191,.3); color:#20bfbf; }
</style>

<!-- ── GLOBAL FLOATING TOOLTIP ── -->
<div id="bc-global-tooltip">
    <span class="bc-tt-title"></span>
    <div class="bc-tt-list"></div>
</div>

<!-- ── HEADER ── -->
<header>
    <div class="content">
        <div class="hdr-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></div>
        <div>
            <h1><?= t('Meet Our Boosters') ?></h1>
            <p><?= t('The team of professional boosters at LoLBoost. Our boosters are experienced, skilled, and dedicated to helping you achieve your gaming goals.') ?></p>
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
                    <span class="eg-count" id="bCount">&nbsp;</span>
                    <button type="button" class="filter-toggle" id="boosterFilterToggle" aria-expanded="false" aria-controls="boosterFilterFields">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div class="filter-fields" id="boosterFilterFields">
            <!-- Game -->
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
                        <?php foreach (($boosterGames ?? []) as $boosterGame):
                            $gameSlug = (string)($boosterGame['slug'] ?? '');
                            $gameLabel = (string)($boosterGame['label'] ?? $gameSlug);
                            $gameIcon = (string)($boosterGame['icon'] ?? '');
                            if ($gameSlug === '') continue;
                        ?>
                        <div class="eg-drop-item"
                             data-value="<?= htmlspecialchars($gameSlug, ENT_QUOTES, 'UTF-8') ?>"
                             data-label="<?= htmlspecialchars($gameLabel, ENT_QUOTES, 'UTF-8') ?>"
                             <?php if ($gameIcon !== ''): ?>data-img="<?= htmlspecialchars($gameIcon, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>>
                            <span class="eg-drop-item-icon">
                                <?php if ($gameIcon !== ''): ?>
                                    <img src="<?= htmlspecialchars($gameIcon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($gameLabel, ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <i class="fa-solid fa-gamepad"></i>
                                <?php endif; ?>
                            </span>
                            <span><?= htmlspecialchars($gameLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="bGame" value="">
                </div>
            </div>

            <!-- Search -->
            <div class="form-group">
                <label><?= t('Search') ?></label>
                <input type="text" id="filterBooster" placeholder="<?= t('Username...') ?>">
            </div>

            <!-- Availability -->
            <div class="availability">
                <div class="availability-title"><?= t('Availability') ?></div>
                <label class="check-row">
                    <input type="checkbox" id="filterOnline" value="1">
                    <span class="check-ui"></span>
                    <span><?= t('Online Now') ?></span>
                </label>
            </div>

            <!-- Servers -->
            <div class="form-group">
                <label><?= t('Servers') ?></label>
                <div class="eg-custom-drop" id="serverDropWrap">
                    <button type="button" class="eg-drop-btn" id="serverDropBtn">
                        <span class="eg-drop-icon" id="serverDropIcon"></span>
                        <span class="eg-drop-label" id="serverDropLabel"><?= t('All Servers') ?></span>
                        <span class="eg-drop-arrow">&#9660;</span>
                    </button>
                    <div class="eg-drop-list" id="serverDropList">
                        <div class="eg-drop-item eg-drop-item--active" data-value="" data-label="<?= t('All Servers') ?>">
                            <span class="eg-drop-item-icon"></span>
                            <span><?= t('All Servers') ?></span>
                        </div>
                        <?php
                        $__servers = [
                            'EUW'  => 'EUW — EU West',
                            'EUNE' => 'EUNE — EU Nordic & East',
                            'NA'   => 'NA — North America',
                            'TR'   => 'TR — Turkey',
                            'RU'   => 'RU — Russia',
                            'BR'   => 'BR — Brazil',
                            'LAN'  => 'LAN — Latin America North',
                            'LAS'  => 'LAS — Latin America South',
                            'OCE'  => 'OCE — Oceania',
                            'KR'   => 'KR — Korea',
                            'JP'   => 'JP — Japan',
                        ];
                        foreach ($__servers as $__sv => $__sn): ?>
                        <div class="eg-drop-item" data-value="<?= $__sv ?>" data-label="<?= htmlspecialchars($__sn, ENT_QUOTES) ?>">
                            <span class="eg-drop-item-icon"></span>
                            <span><?= htmlspecialchars($__sn, ENT_QUOTES) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="bServer" value="">
                </div>
            </div>

            <!-- Language -->
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
                            'ru'=>['name'=>'Русский',    'img'=>'ru.webp'],
                            'nl'=>['name'=>'Nederlands', 'img'=>'nl.png'],
                            'sv'=>['name'=>'Svenska',    'img'=>'sv.png'],
                            'da'=>['name'=>'Dansk',      'img'=>'da.webp'],
                            'no'=>['name'=>'Norsk',      'img'=>'no.webp'],
                            'fi'=>['name'=>'Suomi',      'img'=>'fi.webp'],
                            'cs'=>['name'=>'Čeština',    'img'=>'cz.webp'],
                            'ro'=>['name'=>'Română',     'img'=>'ro.png'],
                            'hu'=>['name'=>'Magyar',     'img'=>'hu.webp'],
                            'uk'=>['name'=>'Українська', 'img'=>'uk.png'],
                            'ar'=>['name'=>'العربية',    'img'=>'ar.png'],
                            'zh'=>['name'=>'中文',        'img'=>'chinese.png'],
                            'ja'=>['name'=>'日本語',      'img'=>'ja.webp'],
                            'ko'=>['name'=>'한국어',      'img'=>'ko.png'],
                            'el'=>['name'=>'Ελληνικά',   'img'=>'el.png'],
                            'hr'=>['name'=>'Hrvatski',   'img'=>'hr.png'],
                            'bg'=>['name'=>'Български',  'img'=>'bg.webp'],
                            'vn'=>['name'=>'Tiếng Việt', 'img'=>'vn.webp'],
                            'ph'=>['name'=>'Filipino',   'img'=>'ph.webp'],
                            'th'=>['name'=>'ภาษาไทย',    'img'=>'th.webp'],
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
                    <input type="hidden" id="bLang" value="">
                </div>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label><?= t('Role') ?></label>
                <div class="eg-custom-drop" id="roleDropWrap">
                    <button type="button" class="eg-drop-btn" id="roleDropBtn">
                        <span class="eg-drop-icon" id="roleDropIcon"></span>
                        <span class="eg-drop-label" id="roleDropLabel"><?= t('All Roles') ?></span>
                        <span class="eg-drop-arrow">&#9660;</span>
                    </button>
                    <div class="eg-drop-list" id="roleDropList">
                        <div class="eg-drop-item eg-drop-item--active" data-value="" data-label="<?= t('All Roles') ?>">
                            <span class="eg-drop-item-icon"></span>
                            <span><?= t('All Roles') ?></span>
                        </div>
                        <?php
                        $__roles = [
                            'TopLane' => 'Top Lane',
                            'Jungle'  => 'Jungle',
                            'MidLane' => 'Mid Lane',
                            'AdCarry' => 'AD Carry',
                            'Support' => 'Support',
                        ];
                        foreach ($__roles as $__rv => $__rl): ?>
                        <div class="eg-drop-item"
                             data-value="<?= $__rv ?>"
                             data-label="<?= htmlspecialchars($__rl, ENT_QUOTES) ?>"
                             data-img="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= $__rv ?>.png">
                            <span class="eg-drop-item-icon">
                                <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= $__rv ?>.png"
                                     alt="<?= htmlspecialchars($__rl, ENT_QUOTES) ?>"
                                     onerror="this.style.display='none'">
                            </span>
                            <span><?= htmlspecialchars($__rl, ENT_QUOTES) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="bRole" value="">
                </div>
            </div>

            <button class="reset-filters" id="bClear"><?= t('Clear Filters') ?></button>
            <div class="filter-count" id="bFilterCount"></div>
            </div>

        </div><!-- /.filter-inner -->
    </div><!-- /.filter-box -->

    <!-- ── CARD GRID ── -->
    <div id="boosters">
        <?= $this->insert('website/components/boosters/booster-cards', ['boosters' => $boosters]) ?>

        <div class="bc-empty" id="bEmpty">
            <div class="bc-empty-icon">🎮</div>
            <h4><?= t('No Boosters found') ?></h4>
            <p><?= t('Try adjusting your filters.') ?></p>
        </div>
    </div>

    <nav class="booster-pagination" id="boosterPagination" aria-label="<?= t('Booster pagination') ?>"></nav>

    <div id="loading-spinner">
        <div class="loader"></div>
    </div>

</div><!-- /.main-content -->

<?= $this->insert('website/components/get-started', ['variation' => 'two']) ?>

<?= $this->start('scripts') ?>
<script>
(function () {

    /* ── Responsive filter toolbar ── */
    var boosterFilterBox = document.querySelector('.boosters-list .filter-box');
    var boosterFilterToggle = document.getElementById('boosterFilterToggle');
    if (boosterFilterBox && boosterFilterToggle) {
        boosterFilterBox.classList.add('filters-ready');
        boosterFilterToggle.addEventListener('click', function () {
            var isOpen = boosterFilterBox.classList.toggle('is-open');
            boosterFilterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    /* ── Pagination ── */
    var currentPage  = <?= (int)($pagination['page'] ?? 1) ?>;
    var totalPages   = <?= $pagination['totalPages'] ?>;
    var totalBoosters = <?= (int)($pagination['totalItems'] ?? count($boosters)) ?>;
    var isLoading    = false;
    var filterActive = false;
    var perPage      = 9;
    var paginationEl = document.getElementById('boosterPagination');

    function removeInlineEmptyState() {
        document.querySelectorAll('#boosters .no-boosters').forEach(function (element) {
            element.remove();
        });
    }

    function syncEmptyState() {
        var grid = document.getElementById('boosters');
        var empty = document.getElementById('bEmpty');
        if (!grid || !empty) return;
        var hasCards = !!grid.querySelector('.cover-link');
        empty.style.display = hasCards ? 'none' : 'flex';
        empty.classList.toggle('visible', !hasCards);
    }

    function renderPagination() {
        if (!paginationEl) return;
        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }
        var html = '<button type="button" data-page="prev" aria-label="Previous page"'
            + (currentPage <= 1 ? ' disabled' : '') + '><i class="fa-solid fa-chevron-left"></i></button>';
        for (var page = 1; page <= totalPages; page++) {
            if (page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1) {
                html += '<button type="button" data-page="' + page + '"'
                    + (page === currentPage ? ' class="active" aria-current="page"' : '') + '>' + page + '</button>';
            } else if (page === currentPage - 2 || page === currentPage + 2) {
                html += '<span class="page-dots">…</span>';
            }
        }
        html += '<button type="button" data-page="next" aria-label="Next page"'
            + (currentPage >= totalPages ? ' disabled' : '') + '><i class="fa-solid fa-chevron-right"></i></button>';
        paginationEl.innerHTML = html;
    }

    function scrollToBoosterGrid() {
        var grid = document.getElementById('boosters');
        if (!grid) return;
        var top = grid.getBoundingClientRect().top + window.scrollY - 110;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    function showFilteredPage(page) {
        var cards = Array.from(document.querySelectorAll('#boosters .cover-link'));
        totalPages = Math.max(1, Math.ceil(cards.length / perPage));
        currentPage = Math.max(1, Math.min(page, totalPages));
        var start = (currentPage - 1) * perPage;
        cards.forEach(function (card, index) {
            card.style.display = index >= start && index < start + perPage ? '' : 'none';
        });
        renderPagination();
    }

    function loadBoosterPage(page) {
        if (isLoading || filterActive || page < 1 || page > totalPages) return;
        isLoading = true;
        currentPage = page;
        document.getElementById('loading-spinner').style.display = 'flex';
        $.post('<?= AJAX_URL ?>', { action: 'load_boosters', page: currentPage }, function (response) {
            var grid = document.getElementById('boosters');
            grid.querySelectorAll('.cover-link').forEach(function (el) { el.remove(); });
            grid.insertAdjacentHTML('afterbegin', response);
            removeInlineEmptyState();
            syncEmptyState();
            document.getElementById('loading-spinner').style.display = 'none';
            isLoading = false;
            renderPagination();
            updateCount(totalBoosters);
            scrollToBoosterGrid();
        });
    }

    if (paginationEl) paginationEl.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-page]');
        if (!button || button.disabled) return;
        var requested = button.dataset.page;
        var page = requested === 'prev' ? currentPage - 1
            : requested === 'next' ? currentPage + 1
            : parseInt(requested, 10);
        if (filterActive) {
            showFilteredPage(page);
            scrollToBoosterGrid();
        } else {
            loadBoosterPage(page);
        }
    });

    /* ── AJAX filter ── */
    function runFilter() {
        filterActive = true;
        document.getElementById('loading-spinner').style.display = 'flex';
        $.post('<?= AJAX_URL ?>', {
            action    : 'filter_boosters',
            name      : document.getElementById('filterBooster').value || '',
            game      : document.getElementById('bGame').value    || '',
            servers   : document.getElementById('bServer').value  ? [document.getElementById('bServer').value]  : [],
            languages : document.getElementById('bLang').value    ? [document.getElementById('bLang').value]    : [],
            roles     : document.getElementById('bRole').value    ? [document.getElementById('bRole').value]    : [],
            champions : [],
            online    : document.getElementById('filterOnline').checked ? 1 : 0
        }, function (response) {
            var grid  = document.getElementById('boosters');
            var empty = document.getElementById('bEmpty');
            grid.querySelectorAll('.cover-link').forEach(function (el) { el.remove(); });
            grid.insertAdjacentHTML('afterbegin', response);
            removeInlineEmptyState();
            document.getElementById('loading-spinner').style.display = 'none';
            var hasCards = !!grid.querySelector('.cover-link');
            empty.style.display = hasCards ? 'none' : 'flex';
            empty.classList.toggle('visible', !hasCards);
            showFilteredPage(1);
            updateCount();
        });
    }

    function updateCount(total) {
        var n     = typeof total === 'number' ? total : document.querySelectorAll('#boosters .cover-link').length;
        var label = n + ' <?= t('Booster') ?>' + (n !== 1 ? 's' : '') + ' <?= t('found') ?>';
        var badge = document.getElementById('bCount');
        var count = document.getElementById('bFilterCount');
        if (badge) badge.textContent = label;
        if (count) count.textContent = label;
    }

    /* ── Custom Dropdown ── */
    function initCustomDrop(btnId, listId, hiddenId, iconId, labelId, isFlagDrop) {
        var btn    = document.getElementById(btnId);
        var list   = document.getElementById(listId);
        var hidden = document.getElementById(hiddenId);
        var icon   = document.getElementById(iconId);
        var label  = document.getElementById(labelId);
        var wrap   = btn ? btn.closest('.eg-custom-drop') : null;
        var group  = wrap ? wrap.closest('.form-group') : null;
        if (!btn || !list || !wrap) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            // Close all other dropdowns and reset their z-index
            document.querySelectorAll('.boosters-list .eg-custom-drop.open').forEach(function (d) {
                if (d !== wrap) {
                    d.classList.remove('open');
                    var g = d.closest('.form-group');
                    if (g) g.style.zIndex = '';
                }
            });
            var isOpen = wrap.classList.toggle('open');
            if (group) group.style.zIndex = isOpen ? '1000' : '';
        });

        list.querySelectorAll('.eg-drop-item').forEach(function (item) {
            item.addEventListener('click', function () {
                var val = this.dataset.value || '';
                var lbl = this.dataset.label || '';
                var img = this.dataset.img   || '';
                hidden.value = val;
                list.querySelectorAll('.eg-drop-item').forEach(function (i) { i.classList.remove('eg-drop-item--active'); });
                this.classList.add('eg-drop-item--active');
                label.textContent = lbl;
                if (img) {
                    icon.innerHTML = '<img src="' + img + '" alt="">';
                    icon.className = 'eg-drop-icon' + (isFlagDrop ? ' is-flag' : '');
                } else {
                    icon.innerHTML = ''; icon.className = 'eg-drop-icon';
                }
                wrap.classList.remove('open');
                if (group) group.style.zIndex = '';
                runFilter();
            });
        });
    }

    initCustomDrop('gameDropBtn',   'gameDropList',   'bGame',   'gameDropIcon',   'gameDropLabel',   false);
    initCustomDrop('serverDropBtn', 'serverDropList', 'bServer', 'serverDropIcon', 'serverDropLabel', false);
    initCustomDrop('langDropBtn',   'langDropList',   'bLang',   'langDropIcon',   'langDropLabel',   true);
    initCustomDrop('roleDropBtn',   'roleDropList',   'bRole',   'roleDropIcon',   'roleDropLabel',   false);

    document.addEventListener('click', function () {
        document.querySelectorAll('.boosters-list .eg-custom-drop.open').forEach(function (d) {
            d.classList.remove('open');
            var g = d.closest('.form-group');
            if (g) g.style.zIndex = '';
        });
    });

    var t2;
    document.getElementById('filterBooster').addEventListener('input', function () {
        clearTimeout(t2); t2 = setTimeout(runFilter, 280);
    });
    document.getElementById('filterOnline').addEventListener('change', runFilter);

    document.getElementById('bClear').addEventListener('click', function () {
        document.getElementById('filterBooster').value = '';
        document.getElementById('filterOnline').checked = false;

        ['gameDropList','serverDropList','langDropList','roleDropList'].forEach(function (id) {
            var l = document.getElementById(id); if (!l) return;
            l.querySelectorAll('.eg-drop-item').forEach(function (i) { i.classList.remove('eg-drop-item--active'); });
            var f = l.querySelector('.eg-drop-item'); if (f) f.classList.add('eg-drop-item--active');
        });
        ['bGame','bServer','bLang','bRole'].forEach(function (id) { var el = document.getElementById(id); if (el) el.value = ''; });
        ['gameDropIcon','serverDropIcon','langDropIcon','roleDropIcon'].forEach(function (id) {
            var el = document.getElementById(id); if (el) { el.innerHTML = ''; el.className = 'eg-drop-icon'; }
        });
        document.getElementById('gameDropLabel').textContent   = '<?= t('All Games') ?>';
        document.getElementById('serverDropLabel').textContent = '<?= t('All Servers') ?>';
        document.getElementById('langDropLabel').textContent   = '<?= t('All Languages') ?>';
        document.getElementById('roleDropLabel').textContent   = '<?= t('All Roles') ?>';

        filterActive = false;
        runFilter();
    });

    removeInlineEmptyState();
    syncEmptyState();
    updateCount(totalBoosters);
    renderPagination();

})();
</script>
<?= $this->end() ?>
