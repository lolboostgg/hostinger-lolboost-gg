<?php
/**
 * Booster Dashboard → My Performance
 * File: booster/pages/performance.php
 */
$boosterId = (int)(defined('BOOSTER_ID') ? BOOSTER_ID : ($booster_id ?? 0));
?>

<?= $this->layout('booster/layouts/main', [
  'meta' => [
    'title'       => 'My Performance - Booster Area | LoLBoost.gg',
    'h1'          => 'My Performance',
    'description' => 'Your champion stats, rank breakdown and all played games.',
  ]
]) ?>

<style>
/* ── Booster Performance Page — aligned to dashboard design system ── */

/* ── Filter bar — toolbar card style ── */
.bp-bar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.bp-year-filters,.bp-mode-filters{display:flex;gap:6px;flex-wrap:wrap;}

/* ── Pills — al-pill style ── */
.bp-year-btn,.bp-mode-btn{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none;font-family:inherit;}
.bp-year-btn:hover,.bp-mode-btn:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.bp-year-btn.active,.bp-mode-btn.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}

/* ── Rank cards slider ── */
.bp-rank-wrap{position:relative;margin-bottom:24px;}
.bp-rank-cards{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;padding:4px 2px 8px;cursor:grab;user-select:none;}
.bp-rank-cards.is-dragging{cursor:grabbing;}
.bp-rank-cards::-webkit-scrollbar{display:none;}
.bp-rank-card{flex:0 0 155px;scroll-snap-align:start;background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 12px;display:flex;flex-direction:column;align-items:center;gap:8px;transition:border-color .15s;box-shadow:0 2px 12px rgba(0,0,0,.2);}
.bp-rank-card:hover{border-color:rgba(109,92,255,.35);}
.bp-rank-card.empty{opacity:.4;}
.bp-rank-card.empty img{filter:grayscale(1);}
.bp-rank-card img{width:52px;height:52px;object-fit:contain;}
.bp-rank-card-name{font-size:.9rem;font-weight:800;color:rgba(255,255,255,.9);}
.bp-rank-card-rows{width:100%;display:flex;flex-direction:column;gap:5px;}
.bp-rank-card-row{display:flex;justify-content:space-between;font-size:.76rem;}
.bp-rank-card-row span{color:rgba(255,255,255,.38);}
.bp-rank-card-row strong{font-weight:800;color:rgba(255,255,255,.85);}
.bp-rank-card-row strong.win{color:#4ade80;}
.bp-rank-card-row strong.wr-green{color:#4ade80;}
.bp-rank-card-row strong.wr-yellow{color:#facc15;}
.bp-rank-card-row strong.wr-red{color:#fb7185;}

/* ── Rank nav arrows ── */
.bp-rank-arrow{position:absolute;top:50%;transform:translateY(-50%);z-index:2;width:28px;height:28px;border-radius:50%;background:#25282a;border:1px solid rgba(109,92,255,.35);color:#c4b5fd;font-size:.75rem;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;box-shadow:0 3px 10px rgba(0,0,0,.4);}
.bp-rank-arrow:hover{background:rgba(109,92,255,.25);color:#fff;}
.bp-rank-arrow:disabled{opacity:.25;cursor:default;pointer-events:none;}
.bp-rank-arrow-l{left:-10px;}
.bp-rank-arrow-r{right:-10px;}

/* ── Champion table — al-table style ── */
.bp-champ-wrap{overflow-x:auto;border:1px solid rgba(255,255,255,.07);border-radius:20px;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);margin-bottom:24px;}
.bp-champ-wrap.is-empty{border-color:rgba(255,255,255,.04);background:rgba(255,255,255,.015);box-shadow:none;}
.bp-champ-table{width:100%;border-collapse:collapse;}
.bp-champ-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.bp-champ-table th{padding:11px 16px;text-align:left;font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.35);white-space:nowrap;}
.bp-champ-table th:not(:first-child){text-align:center;}
.bp-champ-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.bp-champ-table tbody tr:last-child{border-bottom:none;}
.bp-champ-table tbody tr:hover{background:rgba(109,92,255,.08);}
.bp-champ-table td{padding:11px 16px;color:rgba(255,255,255,.8);white-space:nowrap;font-size:.85rem;}
.bp-champ-table td:not(:first-child){text-align:center;}
.bp-champ-cell{display:flex;align-items:center;gap:8px;}
.bp-champ-cell img{width:28px;height:28px;border-radius:6px;object-fit:cover;}
.bp-champ-cell span{font-weight:800;color:rgba(255,255,255,.9);}
.bp-kda-good,.bp-kda-great{color:#f97316;font-weight:900;}
.bp-wr-green{color:#4ade80;font-weight:800;}
.bp-wr-yellow{color:#facc15;font-weight:800;}
.bp-wr-red{color:#fb7185;font-weight:800;}

/* ── Match list + rows ── */
.bp-match-list{display:flex;flex-direction:column;gap:6px;}
.bp-match-summary{display:flex;flex-wrap:wrap;gap:8px 14px;margin:-4px 0 10px;font-size:.78rem;color:rgba(255,255,255,.4);}
.bp-match-summary strong{color:rgba(255,255,255,.82);}
.bp-match-row{display:grid;grid-template-columns:5px 48px minmax(0,1fr) 130px 96px 100px 90px 90px;align-items:center;gap:12px;padding:10px 14px;border-radius:13px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025);transition:background .12s;}
.bp-match-row:hover{background:rgba(109,92,255,.07);}
.bp-match-stripe{width:5px;height:46px;border-radius:3px;}
.bp-match-stripe.win{background:#4ade80;}
.bp-match-stripe.loss{background:#fb7185;}
.bp-match-stripe.remake{background:rgba(255,255,255,.25);}
.bp-match-champ{width:48px;height:48px;border-radius:9px;overflow:hidden;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);}
.bp-match-champ img{width:100%;height:100%;object-fit:cover;}
.bp-match-info{min-width:0;}
.bp-match-name{font-size:.92rem;font-weight:800;color:rgba(255,255,255,.92);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.bp-match-queue{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:2px;}
/* ── Result badges — al-badge style ── */
.bp-result-badge{display:inline-flex;align-items:center;gap:.3rem;padding:3px 9px;border-radius:99px;font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;margin-top:3px;}
.bp-result-badge.win{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;}
.bp-result-badge.loss{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185;}
.bp-result-badge.remake{background:rgba(148,163,184,.12);border:1px solid rgba(148,163,184,.25);color:#94a3b8;}
.bp-match-kda{text-align:center;}
.bp-match-kda-val{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.9);}
.bp-match-kda-val .k{color:#4ade80;}
.bp-match-kda-val .d{color:#fb7185;}
.bp-match-kda-val .sep{color:rgba(255,255,255,.25);font-weight:400;}
.bp-match-kda-ratio{font-size:.72rem;color:rgba(255,255,255,.38);}
.bp-match-kda-ratio span{color:#c4b5fd;font-weight:700;}
.bp-match-dur{text-align:center;font-size:.8rem;color:rgba(255,255,255,.4);}
.bp-match-dur strong{display:block;font-size:.88rem;font-weight:800;color:rgba(255,255,255,.82);}
.bp-match-rank{text-align:center;font-size:.76rem;color:rgba(255,255,255,.4);}
.bp-match-rank strong{display:block;font-size:.8rem;font-weight:800;color:rgba(255,255,255,.82);white-space:nowrap;}
.bp-match-date{text-align:right;font-size:.76rem;color:rgba(255,255,255,.35);}

/* ── Empty + spinner ── */
.bp-empty{text-align:center;padding:20px 16px;color:rgba(255,255,255,.18);}
.bp-empty i{font-size:1.1rem;display:inline-block;margin-right:6px;opacity:.5;}
.bp-empty-text{font-size:.76rem;font-weight:600;letter-spacing:.03em;}
.bp-spinner{display:flex;align-items:center;justify-content:center;padding:20px;color:rgba(109,92,255,.4);}
.bp-spinner i{font-size:1.1rem;animation:bpSpin .8s linear infinite;}
/* ── Recent badge ── */
.bp-recent-badge{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;padding:3px 9px;border-radius:99px;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.3);color:#c4b5fd;margin-left:10px;vertical-align:middle;}

@keyframes bpSpin{to{transform:rotate(360deg);}}

/* ── Games toolbar ── */
.bp-games-card{border:1px solid rgba(255,255,255,.07);border-radius:20px;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);overflow:hidden;}
.bp-games-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.05);}
.bp-games-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.88);display:flex;align-items:center;gap:8px;}
.bp-games-body{padding:14px 18px;}
.bp-games-toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;}
.bp-filter-pills{display:flex;gap:6px;flex-wrap:wrap;}
.bp-filter-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.75rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.5);transition:background .12s,border-color .12s,color .12s;user-select:none;}
.bp-filter-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.bp-filter-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.bp-filter-pill[data-f="win"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80;}
.bp-filter-pill[data-f="loss"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.30);color:#fb7185;}
.bp-filter-pill[data-f="duo"].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.30);color:#facc15;}
.bp-search-wrap{position:relative;}
.bp-search-wrap input{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 32px!important;font-size:.82rem!important;width:210px;outline:none;transition:border-color .15s,box-shadow .15s;}
.bp-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;}
.bp-search-wrap input::placeholder{color:rgba(255,255,255,.22)!important;}
.bp-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);font-size:.78rem;pointer-events:none;}
.bp-games-summary{font-size:.76rem;color:rgba(255,255,255,.35);margin-bottom:12px;}
.bp-games-summary strong{color:rgba(255,255,255,.7);}

@media(max-width:768px){
  .bp-match-row{grid-template-columns:4px 40px minmax(0,1fr) 90px;}
  .bp-match-dur,.bp-match-date,.bp-match-rank{display:none;}
  .bp-rank-card{flex:0 0 130px;}
  .bp-search-wrap input{width:100%;}
}

/* ── Report: checkbox on rows ── */
.bp-match-row{position:relative;cursor:default;}
.bp-match-chk{appearance:none;-webkit-appearance:none;width:16px;height:16px;border-radius:4px;border:1.5px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);cursor:pointer;flex-shrink:0;position:relative;transition:background .12s,border-color .12s;display:inline-block;vertical-align:middle;}
.bp-match-chk:hover{border-color:rgba(251,113,133,.6);background:rgba(251,113,133,.10);}
.bp-match-chk:checked{background:#fb7185;border-color:#fb7185;}
.bp-match-chk:checked::after{content:'';position:absolute;left:3px;top:1px;width:5px;height:8px;border:2px solid #fff;border-top:0;border-left:0;transform:rotate(45deg);}
.bp-match-row.is-selected{border-color:rgba(251,113,133,.3);background:rgba(251,113,133,.06);}
/* ── Report toolbar ── */
.bp-report-bar{display:none;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:9px 14px;border-radius:12px;border:1px solid rgba(251,113,133,.25);background:rgba(251,113,133,.07);margin-bottom:12px;}
.bp-report-bar.visible{display:flex;}
.bp-report-info{font-size:.8rem;font-weight:800;color:#fb7185;}
.bp-report-actions{display:flex;gap:7px;}
.bp-report-cancel-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.45);font-size:.76rem;font-weight:700;cursor:pointer;transition:background .12s;}
.bp-report-cancel-btn:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.8);}
.bp-report-submit-btn{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:9px;border:1px solid rgba(251,113,133,.35);background:rgba(251,113,133,.14);color:#fb7185;font-size:.76rem;font-weight:800;cursor:pointer;transition:background .12s,border-color .12s;}
.bp-report-submit-btn:hover{background:rgba(251,113,133,.22);border-color:rgba(251,113,133,.55);}
/* ── Report modal ── */
.bp-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9990;align-items:center;justify-content:center;backdrop-filter:blur(6px);padding:16px;}
.bp-modal-overlay.open{display:flex;}
.bp-modal{background:#1c1e21;border:1px solid rgba(255,255,255,.08);border-radius:24px;width:100%;max-width:460px;box-shadow:0 32px 80px rgba(0,0,0,.8);overflow:hidden;}
.bp-modal-head{background:linear-gradient(135deg,rgba(251,113,133,.12),rgba(251,113,133,.04));border-bottom:1px solid rgba(251,113,133,.15);padding:20px 22px 18px;}
.bp-modal-head-icon{width:38px;height:38px;border-radius:11px;background:rgba(251,113,133,.15);border:1px solid rgba(251,113,133,.25);display:flex;align-items:center;justify-content:center;font-size:.95rem;color:#fb7185;margin-bottom:10px;}
.bp-modal-title{font-size:1rem;font-weight:900;color:rgba(255,255,255,.92);margin:0 0 3px;}
.bp-modal-sub{font-size:.76rem;color:rgba(255,255,255,.38);margin:0;}
.bp-modal-body{padding:18px 22px;}
.bp-modal-reasons{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;}
.bp-modal-reason{display:flex;flex-direction:column;align-items:flex-start;gap:5px;padding:12px 13px;border-radius:14px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.03);cursor:pointer;transition:border-color .14s,background .14s;user-select:none;position:relative;}
.bp-modal-reason:hover{border-color:rgba(109,92,255,.3);background:rgba(109,92,255,.06);}
.bp-modal-reason.selected{border-color:rgba(109,92,255,.5);background:rgba(109,92,255,.10);}
.bp-modal-reason.selected::after{content:'';position:absolute;top:8px;right:8px;width:8px;height:8px;border-radius:50%;background:#6d5cff;}
.bp-modal-reason input[type=radio]{display:none;}
.bp-modal-reason-icon{font-size:.95rem;color:rgba(255,255,255,.35);transition:color .14s;}
.bp-modal-reason.selected .bp-modal-reason-icon{color:#c4b5fd;}
.bp-modal-reason strong{display:block;font-size:.8rem;font-weight:800;color:rgba(255,255,255,.82);line-height:1.3;}
.bp-modal-reason span{font-size:.7rem;color:rgba(255,255,255,.32);line-height:1.3;}
.bp-modal-reason-full{grid-column:1/-1;}
.bp-modal-note{width:100%;box-sizing:border-box;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:11px;color:rgba(255,255,255,.82);padding:10px 13px;font-size:.82rem;resize:none;min-height:60px;outline:none;font-family:inherit;transition:border-color .15s,box-shadow .15s;}
.bp-modal-note:focus{border-color:rgba(109,92,255,.45);box-shadow:0 0 0 3px rgba(109,92,255,.10);}
.bp-modal-note::placeholder{color:rgba(255,255,255,.18);}
.bp-modal-footer{display:flex;gap:8px;justify-content:flex-end;padding:14px 22px 18px;border-top:1px solid rgba(255,255,255,.05);}
.bp-modal-btn-cancel{padding:8px 16px;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:transparent;color:rgba(255,255,255,.4);font-size:.8rem;font-weight:700;cursor:pointer;transition:background .12s,color .12s;}
.bp-modal-btn-cancel:hover{background:rgba(255,255,255,.06);color:rgba(255,255,255,.75);}
.bp-modal-btn-send{padding:8px 20px;border-radius:10px;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;color:#fff;font-size:.82rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:opacity .15s,transform .12s;box-shadow:0 4px 16px rgba(109,92,255,.35);}
.bp-modal-btn-send:hover:not(:disabled){opacity:.9;transform:translateY(-1px);}
.bp-modal-btn-send:disabled{opacity:.35;cursor:not-allowed;transform:none;box-shadow:none;}
</style>

<!-- Filter Bar -->
<div class="bp-bar" id="bpStatsHeader" style="display:none;">
  <div class="bp-year-filters" id="bpYearFilters"></div>
  <div class="bp-mode-filters">
    <button class="bp-mode-btn" data-mode="solo">Solo</button>
    <button class="bp-mode-btn" data-mode="duo">Duo</button>
    <button class="bp-mode-btn active" data-mode="all">All</button>
  </div>
</div>

<!-- Competitive Progression -->
<div id="bpRankSection" style="display:none; margin-bottom:32px;">
  <p class="mb-0" style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#f97316;">Rank History</p>
  <h3 class="mb-3">Competitive Progression</h3>
  <div class="bp-rank-wrap">
    <button class="bp-rank-arrow bp-rank-arrow-l" id="bpRankPrev"><i class="fa-solid fa-chevron-left"></i></button>
    <div class="bp-rank-cards" id="bpRankCards"></div>
    <button class="bp-rank-arrow bp-rank-arrow-r" id="bpRankNext"><i class="fa-solid fa-chevron-right"></i></button>
  </div>
</div>

<!-- Champion Statistics -->
<div id="bpChampSection" style="display:none; margin-bottom:32px;">
  <p class="mb-0" style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#f97316;">Performance</p>
  <h3 class="mb-3">Champion Statistics <small style="font-size:.8rem;color:rgba(255,255,255,.4);font-weight:400;">(Own games)</small></h3>
  <div class="bp-champ-wrap">
    <table class="bp-champ-table">
      <thead>
        <tr>
          <th>Champion</th>
          <th>Total Games</th>
          <th>Kills</th>
          <th>Deaths</th>
          <th>Assists</th>
          <th>KDA Ratio</th>
          <th>Winrate</th>
        </tr>
      </thead>
      <tbody id="bpChampTbody"></tbody>
    </table>
  </div>
</div>

<!-- All Games -->
<div id="bpRecentSection">
  <p class="mb-0" style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#f97316;margin-bottom:6px;">Order Dashboard</p>
  <div class="bp-games-card">
    <div class="bp-games-header">
      <div class="bp-games-title">
        <i class="fa-duotone fa-swords" style="color:#c4b5fd;"></i>
        All Games
        <span class="bp-recent-badge" id="bpRecentBadge">All Games</span>
      </div>
    </div>
    <div class="bp-games-body">
      <!-- Toolbar: filters + search -->
      <div class="bp-games-toolbar">
        <div class="bp-filter-pills">
          <span class="bp-filter-pill active" data-f="all">All</span>
          <span class="bp-filter-pill" data-f="solo"><i class="fa-solid fa-user" style="font-size:.65rem;"></i> Solo</span>
          <span class="bp-filter-pill" data-f="duo"><i class="fa-solid fa-users" style="font-size:.65rem;"></i> Duo</span>
          <span class="bp-filter-pill" data-f="win"><i class="fa-solid fa-check" style="font-size:.65rem;"></i> Wins</span>
          <span class="bp-filter-pill" data-f="loss"><i class="fa-solid fa-xmark" style="font-size:.65rem;"></i> Losses</span>
        </div>
        <div class="bp-search-wrap">
          <i class="fa-solid fa-magnifying-glass bp-search-icon"></i>
          <input type="search" id="bpMatchSearch" placeholder="Search champion, queue...">
        </div>
      </div>
      <div class="bp-games-summary" id="bpMatchSummary"></div>
      <!-- Report bar (visible when rows are selected) -->
      <div class="bp-report-bar" id="bpReportBar">
        <span class="bp-report-info"><i class="fa-solid fa-circle-exclamation" style="margin-right:4px;"></i><span id="bpReportCount">0</span> game(s) selected</span>
        <div class="bp-report-actions">
          <button type="button" class="bp-report-cancel-btn" id="bpReportCancelBtn">
            <i class="fa-solid fa-xmark"></i> Cancel
          </button>
          <button type="button" class="bp-report-submit-btn" id="bpReportOpenBtn">
            <i class="fa-solid fa-flag"></i> Report games
          </button>
        </div>
      </div>
      <div class="bp-match-list" id="bpMatchList">
        <div class="bp-spinner"><i class="fa-solid fa-spinner-third"></i></div>
      </div>
    </div>
  </div>
</div>

<!-- Report Modal -->
<div class="bp-modal-overlay" id="bpReportModal">
  <div class="bp-modal">
    <!-- Header -->
    <div class="bp-modal-head">
      <div class="bp-modal-head-icon"><i class="fa-solid fa-flag"></i></div>
      <h3 class="bp-modal-title">Report Games</h3>
      <p class="bp-modal-sub" id="bpReportModalSub">Select a reason why these games should be removed.</p>
    </div>
    <!-- Body -->
    <div class="bp-modal-body">
      <div class="bp-modal-reasons">
        <label class="bp-modal-reason">
          <input type="radio" name="bpReportReason" value="not_my_game">
          <i class="fa-duotone fa-circle-question bp-modal-reason-icon"></i>
          <strong>Not my game</strong>
          <span>Doesn't belong to my order history</span>
        </label>
        <label class="bp-modal-reason">
          <input type="radio" name="bpReportReason" value="client_played_himself">
          <i class="fa-duotone fa-user-slash bp-modal-reason-icon"></i>
          <strong>Client played himself</strong>
          <span>Client logged in &amp; played during boost</span>
        </label>
        <label class="bp-modal-reason">
          <input type="radio" name="bpReportReason" value="client_afk">
          <i class="fa-duotone fa-person-walking-arrow-right bp-modal-reason-icon"></i>
          <strong>Client AFK </strong>
          <span>Client AFKed during the game</span>
        </label>
        <label class="bp-modal-reason">
          <input type="radio" name="bpReportReason" value="duplicate_error">
          <i class="fa-duotone fa-copy bp-modal-reason-icon"></i>
          <strong>Duplicate / data error</strong>
          <span>Recorded twice or wrong data</span>
        </label>
        <label class="bp-modal-reason bp-modal-reason-full">
          <input type="radio" name="bpReportReason" value="other">
          <i class="fa-duotone fa-ellipsis bp-modal-reason-icon"></i>
          <strong>Other reason</strong>
          <span>Explain in the note below</span>
        </label>
      </div>
      <textarea class="bp-modal-note" id="bpReportNote" placeholder="Additional notes for the admin (optional)..." rows="2"></textarea>
    </div>
    <!-- Footer -->
    <div class="bp-modal-footer">
      <button type="button" class="bp-modal-btn-cancel" id="bpModalBtnCancel">Cancel</button>
      <button type="button" class="bp-modal-btn-send" id="bpModalBtnSend" disabled>
        <i class="fa-solid fa-paper-plane"></i> Send Report
      </button>
    </div>
  </div>
</div>

<script>
(function(){
  'use strict';

  var BOOSTER_ID = <?= $boosterId ?>;
  var AJAX_URL   = '<?= defined('BASE_URL') ? rtrim(BASE_URL,'/') : '' ?>/ajax';
  var ASSET_BASE = '<?= ASSET_URL ?>';
  var LOL_CHAMP  = '<?= defined('LOL_CHAMP_URL') ? LOL_CHAMP_URL : (ASSET_URL.'/core/main/img/lol/champs') ?>';

  var currentYear = 0;
  var currentMode = 'all';

  var $header      = document.getElementById('bpStatsHeader');
  var $yearFilters = document.getElementById('bpYearFilters');
  var $rankSection = document.getElementById('bpRankSection');
  var $rankCards   = document.getElementById('bpRankCards');
  var $champSec    = document.getElementById('bpChampSection');
  var $champTbody  = document.getElementById('bpChampTbody');
  var $recentSec   = document.getElementById('bpRecentSection');
  var $matchList   = document.getElementById('bpMatchList');
  var $recentBadge   = document.getElementById('bpRecentBadge');
  var $matchSummary  = document.getElementById('bpMatchSummary');
  var $matchSearch   = document.getElementById('bpMatchSearch');

  var allGameRows    = [];
  var gameFilter     = 'all';
  var gameSearch     = '';
  var selectedReport = new Set();

  // Report UI elements
  var $reportBar    = document.getElementById('bpReportBar');
  var $reportCount  = document.getElementById('bpReportCount');
  var $reportModal  = document.getElementById('bpReportModal');
  var $reportNote   = document.getElementById('bpReportNote');
  var $modalBtnSend = document.getElementById('bpModalBtnSend');

  function updateReportBar(){
    if(!$reportBar || !$reportCount) return;
    if(selectedReport.size > 0){
      $reportBar.classList.add('visible');
      $reportCount.textContent = selectedReport.size;
    } else {
      $reportBar.classList.remove('visible');
    }
  }

  function clearReportSelection(){
    selectedReport.clear();
    document.querySelectorAll('.bp-match-chk').forEach(function(cb){ cb.checked = false; });
    document.querySelectorAll('.bp-match-row.is-selected').forEach(function(r){ r.classList.remove('is-selected'); });
    updateReportBar();
  }

  // Cancel button
  var $cancelBtn = document.getElementById('bpReportCancelBtn');
  if($cancelBtn) $cancelBtn.addEventListener('click', clearReportSelection);

  // Open modal button
  var $openBtn = document.getElementById('bpReportOpenBtn');
  if($openBtn){
    $openBtn.addEventListener('click', function(){
      if(!selectedReport.size) return;
      document.getElementById('bpReportModalSub').textContent = 'Reporting ' + selectedReport.size + ' game(s). Select a reason:';
      document.querySelectorAll('input[name="bpReportReason"]').forEach(function(r){ r.checked = false; });
      document.querySelectorAll('.bp-modal-reason').forEach(function(l){ l.classList.remove('selected'); });
      if($reportNote) $reportNote.value = '';
      if($modalBtnSend) $modalBtnSend.disabled = true;
      if($reportModal) $reportModal.classList.add('open');
    });
  }

  // Close modal on backdrop click or cancel
  if($reportModal){
    $reportModal.addEventListener('click', function(e){ if(e.target===$reportModal) $reportModal.classList.remove('open'); });
  }
  var $btnCancel2 = document.getElementById('bpModalBtnCancel');
  if($btnCancel2) $btnCancel2.addEventListener('click', function(){ if($reportModal) $reportModal.classList.remove('open'); });

  // Enable send when reason selected
  document.querySelectorAll('input[name="bpReportReason"]').forEach(function(radio){
    radio.addEventListener('change', function(){
      document.querySelectorAll('.bp-modal-reason').forEach(function(l){ l.classList.remove('selected'); });
      var lbl = radio.closest('.bp-modal-reason');
      if(lbl) lbl.classList.add('selected');
      if($modalBtnSend) $modalBtnSend.disabled = false;
    });
  });

  // Send report
  if($modalBtnSend){
    $modalBtnSend.addEventListener('click', function(){
      var ids = Array.from(selectedReport);
      var reasonEl = document.querySelector('input[name="bpReportReason"]:checked');
      if(!ids.length || !reasonEl) return;
      $modalBtnSend.disabled = true;
      $modalBtnSend.innerHTML = '<i class="fa-solid fa-spinner-third" style="animation:bpSpin .8s linear infinite;margin-right:6px;"></i>Sending...';
      var body = new URLSearchParams({
        action: 'booster_report_games',
        match_ids: ids.join(','),
        reason: reasonEl.value,
        note: ($reportNote ? $reportNote.value.trim() : '')
      });
      fetch(AJAX_URL, {method:'POST', body:body, headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(data){
          if($reportModal) $reportModal.classList.remove('open');
          clearReportSelection();
          $modalBtnSend.disabled = false;
          $modalBtnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report';
          if(typeof create_toast === 'function'){
            if(data.ok || data.success){
              create_toast('success', 'Reported', 'Your report was sent to the admins.');
            } else {
              create_toast('danger', 'Error', data.message || 'Could not send report.');
            }
          }
        })
        .catch(function(){
          if($reportModal) $reportModal.classList.remove('open');
          $modalBtnSend.disabled = false;
          $modalBtnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report';
          if(typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not send report.');
        });
    });
  }

  function getIsDuo(r){
    return parseInt(r.is_duo) === 1;
  }

  function applyLocalFilter(){
    var term = gameSearch.toLowerCase();
    var filtered = allGameRows.filter(function(r){
      var res = r.result||(parseInt(r.won)?'win':'loss');
      var isDuo = getIsDuo(r);
      if(gameFilter === 'solo' && isDuo) return false;
      if(gameFilter === 'duo'  && !isDuo) return false;
      if(gameFilter === 'win'  && res !== 'win') return false;
      if(gameFilter === 'loss' && res !== 'loss') return false;
      if(term){
        var hay = ((r.champion||'')+' '+(QUEUES[parseInt(r.queue_id)]||'')+' '+res).toLowerCase();
        if(hay.indexOf(term) === -1) return false;
      }
      return true;
    });

    var wins   = filtered.filter(function(r){ return (r.result||(parseInt(r.won)?'win':'loss'))==='win'; }).length;
    var losses = filtered.filter(function(r){ return (r.result||(parseInt(r.won)?'win':'loss'))==='loss'; }).length;
    var wr     = filtered.length > 0 ? Math.round((wins/filtered.length)*100) : 0;

    if($matchSummary){
      if(!filtered.length){
        $matchSummary.innerHTML = '<span>No games match your filter</span>';
      } else {
        $matchSummary.innerHTML = '<strong>'+filtered.length+'</strong> game'+(filtered.length!==1?'s':'')+
          ' &nbsp;·&nbsp; <strong style="color:#4ade80;">'+wins+'W</strong> <strong style="color:#fb7185;">'+losses+'L</strong>'+
          (filtered.length > 0 ? ' &nbsp;·&nbsp; <strong>'+wr+'%</strong> WR' : '');
      }
    }

    renderMatchRows(filtered);
  }

  function renderMatchRows(rows){
    if(!rows||rows.length===0){
      $matchList.innerHTML='<div class="bp-empty"><i class="fa-solid fa-gamepad"></i><span class="bp-empty-text">No games found</span></div>';
      return;
    }
    $matchList.innerHTML=rows.map(function(r){
      var matchId = parseInt(r.id)||0;
      var res=r.result||(parseInt(r.won)?'win':'loss');
      var k=parseInt(r.kills)||0,d=parseInt(r.deaths)||0,a=parseInt(r.assists)||0;
      var kdaStr=kda(k,d,a);
      var q=QUEUES[parseInt(r.queue_id)]||'Game';
      if(getIsDuo(r)) q += ' · Duo';
      var img=champUrl(r.champion);
      var lbl=res==='remake'?'Remake':(res==='win'?'WIN':'LOSS');
      var isSel = selectedReport.has(String(matchId));
      return '<div class="bp-match-row'+(isSel?' is-selected':'')+'" data-match-id="'+matchId+'" style="cursor:pointer;grid-template-columns:20px 5px 48px minmax(0,1fr) 130px 96px 100px 90px 90px;">'
        +'<input type="checkbox" class="bp-match-chk" data-mid="'+matchId+'"'+(isSel?' checked':'')+' onclick="event.stopPropagation()">'
        +'<div class="bp-match-stripe '+res+'"></div>'
        +'<div class="bp-match-champ">'+(img?'<img src="'+img+'" alt="'+(r.champion||'')+'" onerror="this.style.opacity=0.3">':'')+'</div>'
        +'<div class="bp-match-info">'
        +'<div class="bp-match-name">'+(r.champion||'Unknown')+'</div>'
        +'<div class="bp-match-queue">'+q+'</div>'
        +'<span class="bp-result-badge '+res+'">'+lbl+'</span>'
        +'</div>'
        +'<div class="bp-match-kda">'
        +'<div class="bp-match-kda-val"><span class="k">'+k+'</span><span class="sep"> / </span><span class="d">'+d+'</span><span class="sep"> / </span>'+a+'</div>'
        +'<div class="bp-match-kda-ratio"><span>'+kdaStr+'</span> KDA</div>'
        +'</div>'
        +'<div class="bp-match-dur"><strong>'+fmtDur(r.duration)+'</strong>DURATION</div>'
        +'<div class="bp-match-rank"><strong>'+(r.rank_snapshot ? r.rank_snapshot.split(' · ')[0] : '—')+'</strong>'+(r.rank_snapshot && r.rank_snapshot.split(' · ')[1] ? r.rank_snapshot.split(' · ')[1] : 'RANK')+'</div>'
        +'<div class="bp-match-date">'+fmtDate(r.played_at)+'</div>'
        +'</div>';
    }).join('');

    // Row click = toggle selection
    $matchList.querySelectorAll('.bp-match-row').forEach(function(row){
      row.addEventListener('click', function(e){
        if(e.target.classList.contains('bp-match-chk')) return;
        var cb = row.querySelector('.bp-match-chk');
        if(!cb) return;
        cb.checked = !cb.checked;
        var id = String(parseInt(row.dataset.matchId)||0);
        if(cb.checked){ selectedReport.add(id); row.classList.add('is-selected'); }
        else { selectedReport.delete(id); row.classList.remove('is-selected'); }
        updateReportBar();
      });
      var cb = row.querySelector('.bp-match-chk');
      if(cb) cb.addEventListener('change', function(e){
        e.stopPropagation();
        var id = String(parseInt(row.dataset.matchId)||0);
        if(cb.checked){ selectedReport.add(id); row.classList.add('is-selected'); }
        else { selectedReport.delete(id); row.classList.remove('is-selected'); }
        updateReportBar();
      });
    });
  }

  // Wire up filter pills
  document.querySelectorAll('.bp-filter-pill').forEach(function(pill){
    pill.addEventListener('click', function(){
      document.querySelectorAll('.bp-filter-pill').forEach(function(p){ p.classList.remove('active'); });
      pill.classList.add('active');
      gameFilter = pill.dataset.f || 'all';
      applyLocalFilter();
    });
  });

  // Wire up search
  if($matchSearch){
    $matchSearch.addEventListener('input', function(){
      gameSearch = this.value.trim();
      applyLocalFilter();
    });
  }

  var ALL_RANKS = [
    {tier:0,name:'Unranked'},{tier:1,name:'Iron'},{tier:2,name:'Bronze'},
    {tier:3,name:'Silver'},{tier:4,name:'Gold'},{tier:5,name:'Platinum'},
    {tier:6,name:'Emerald'},{tier:7,name:'Diamond'},{tier:8,name:'Master'},
    {tier:9,name:'Grandmaster'},{tier:10,name:'Challenger'}
  ];

  var QUEUES={0:'Custom',2:'Blind Pick',4:'Ranked Solo',6:'Ranked Premade',7:'Co-op vs AI',8:'3v3 Normal',9:'3v3 Ranked Flex',14:'Draft Pick',16:'Dominion Blind',17:'Dominion Draft',25:'Dominion Co-op vs AI',31:'Co-op vs AI Intro',32:'Co-op vs AI Beginner',33:'Co-op vs AI Intermediate',41:'3v3 Ranked Team',42:'5v5 Ranked Team',52:'3v3 Co-op vs AI',61:'Team Builder',65:'ARAM',67:'ARAM Co-op vs AI',70:'One for All',72:'1v1 Snowdown',73:'2v2 Snowdown',75:'Hexakill',76:'URF',78:'One For All: Mirror',83:'Co-op vs AI URF',91:'Doom Bots Rank 1',92:'Doom Bots Rank 2',93:'Doom Bots Rank 5',96:'Ascension',98:'3v3 Hexakill',100:'ARAM',300:'Poro King',310:'Nemesis',313:'Black Market Brawlers',315:'Nexus Siege',317:'Definitely Not Dominion',318:'ARURF',325:'All Random',400:'Normal Draft',410:'Ranked Dynamic',420:'Ranked Solo/Duo',430:'Normal Blind',440:'Ranked Flex',450:'ARAM',460:'3v3 Blind Pick',470:'3v3 Ranked Flex',480:'Swiftplay',490:'Quickplay',600:'Blood Hunt Assassin',610:'Dark Star: Singularity',700:'Clash',720:'ARAM Clash',800:'3v3 Co-op vs AI Intermediate',810:'3v3 Co-op vs AI Intro',820:'3v3 Co-op vs AI Beginner',830:'Co-op vs AI Intro',840:'Co-op vs AI Beginner',850:'Co-op vs AI Intermediate',870:'Co-op vs AI Intro',880:'Co-op vs AI Beginner',890:'Co-op vs AI Intermediate',900:'ARURF',910:'Ascension',920:'Poro King',940:'Nexus Siege',950:'Doom Bots Voting',960:'Doom Bots Standard',980:'Star Guardian: Normal',990:'Star Guardian: Onslaught',1000:'PROJECT: Hunters',1010:'Snow ARURF',1020:'One for All',1030:'Odyssey: Intro',1040:'Odyssey: Cadet',1050:'Odyssey: Crewmember',1060:'Odyssey: Captain',1070:'Odyssey: Onslaught',1090:'TFT',1100:'Ranked TFT',1110:'TFT Tutorial',1111:'TFT Test',1200:'Nexus Blitz',1210:"TFT Choncc's Treasure",1300:'Nexus Blitz',1400:'Ultimate Spellbook',1700:'Arena',1710:'Arena',1810:'Swarm Solo',1820:'Swarm Duo',1830:'Swarm Trio',1840:'Swarm Squad',1900:'Pick URF',2000:'Tutorial 1',2010:'Tutorial 2',2020:'Tutorial 3',2300:'Brawl',2400:'ARAM: Mayhem'};

  function fmtDur(s){s=parseInt(s)||0;return Math.floor(s/60)+':'+String(s%60).padStart(2,'0');}
  function fmtDate(iso){if(!iso)return '';try{var d=new Date(iso.replace(' ','T'));return String(d.getDate()).padStart(2,'0')+'.'+String(d.getMonth()+1).padStart(2,'0')+'.'+d.getFullYear();}catch(e){return '';}}
  function kda(k,d,a){k=parseInt(k)||0;d=parseInt(d)||0;a=parseInt(a)||0;if(d===0)return (k+a)===0?'0.00':'Perfect';return ((k+a)/d).toFixed(2);}
  function champUrl(n){if(!n)return '';var fix={'Wukong':'MonkeyKing'};return LOL_CHAMP+'/'+(fix[n]||n.replace(/[^a-zA-Z0-9]/g,''))+'.png';}

  function renderRanks(ranks){
    $rankSection.style.display='block';
    var map={}; (ranks||[]).forEach(function(r){map[r.tier]=r;});
    $rankCards.innerHTML=ALL_RANKS.map(function(rank){
      var r=map[rank.tier];
      var w=r?r.wins:0,l=r?r.losses:0,wr=r?r.winrate:0;
      var isEmpty=(w===0&&l===0);
      var wrCls=isEmpty?'':( wr>=60?'wr-green':(wr>=50?'wr-yellow':'wr-red') );
      return '<div class="bp-rank-card'+(isEmpty?' empty':'')+'">'
        +'<img src="'+ASSET_BASE+'/core/main/img/lol/ranks/max/'+rank.tier+'.png" alt="'+rank.name+'" onerror="this.style.display=\'none\'">'
        +'<div class="bp-rank-card-name">'+rank.name+'</div>'
        +'<div class="bp-rank-card-rows">'
        +'<div class="bp-rank-card-row"><span>Win</span><strong class="win">'+w+'</strong></div>'
        +'<div class="bp-rank-card-row"><span>Lose</span><strong>'+l+'</strong></div>'
        +'<div class="bp-rank-card-row"><span>Winrate</span><strong class="'+wrCls+'">'+wr.toFixed(isEmpty?0:2)+'%</strong></div>'
        +'</div></div>';
    }).join('');
    initDrag();
  }

  function initDrag(){
    var isDown=false,startX,scrollLeft;
    $rankCards.addEventListener('mousedown',function(e){isDown=true;$rankCards.classList.add('is-dragging');startX=e.pageX-$rankCards.offsetLeft;scrollLeft=$rankCards.scrollLeft;});
    document.addEventListener('mouseup',function(){isDown=false;$rankCards.classList.remove('is-dragging');});
    $rankCards.addEventListener('mouseleave',function(){isDown=false;$rankCards.classList.remove('is-dragging');});
    $rankCards.addEventListener('mousemove',function(e){if(!isDown)return;e.preventDefault();$rankCards.scrollLeft=scrollLeft-(e.pageX-$rankCards.offsetLeft-startX)*1.2;});
    var prev=document.getElementById('bpRankPrev'),next=document.getElementById('bpRankNext');
    prev.onclick=function(){$rankCards.scrollBy({left:-$rankCards.offsetWidth*.6,behavior:'smooth'});};
    next.onclick=function(){$rankCards.scrollBy({left:$rankCards.offsetWidth*.6,behavior:'smooth'});};
    function ua(){prev.disabled=$rankCards.scrollLeft<5;next.disabled=$rankCards.scrollLeft>=$rankCards.scrollWidth-$rankCards.clientWidth-5;}
    $rankCards.addEventListener('scroll',ua);setTimeout(ua,120);
  }

  function renderChamps(champs){
    $champSec.style.display='block';
    if(!champs||champs.length===0){
      document.querySelector('.bp-champ-wrap') && document.querySelector('.bp-champ-wrap').classList.add('is-empty');
      $champTbody.innerHTML='<tr><td colspan="7" class="bp-empty"><i class="fa-solid fa-chart-bar"></i><span class="bp-empty-text">No champion statistics yet</span></td></tr>';
      return;
    }
    document.querySelector('.bp-champ-wrap') && document.querySelector('.bp-champ-wrap').classList.remove('is-empty');
    $champTbody.innerHTML=champs.map(function(c){
      var kv=parseFloat(c.kda)||0;
      var kCls=kv>=5?'bp-kda-great':(kv>=3?'bp-kda-good':'');
      var wCls=c.winrate>=60?'bp-wr-green':(c.winrate>=50?'bp-wr-yellow':'bp-wr-red');
      var img=champUrl(c.champion);
      return '<tr>'
        +'<td><div class="bp-champ-cell">'+(img?'<img src="'+img+'" alt="'+c.champion+'" onerror="this.style.display=\'none\'">':'')+'<span>'+c.champion+'</span></div></td>'
        +'<td>'+c.games+'</td>'
        +'<td>'+c.avg_kills.toFixed(2)+'</td>'
        +'<td>'+c.avg_deaths.toFixed(2)+'</td>'
        +'<td>'+c.avg_assists.toFixed(2)+'</td>'
        +'<td class="'+kCls+'">'+kv.toFixed(2)+'</td>'
        +'<td class="'+wCls+'">'+c.winrate.toFixed(2)+'%</td>'
        +'</tr>';
    }).join('');
  }

  function renderMatches(rows){
    allGameRows = rows || [];
    gameFilter = 'all';
    gameSearch = '';
    if($matchSearch) $matchSearch.value = '';
    document.querySelectorAll('.bp-filter-pill').forEach(function(p){
      p.classList.toggle('active', p.dataset.f === 'all');
    });
    if(!rows||rows.length===0){
      if($matchSummary) $matchSummary.innerHTML = '';
      $matchList.innerHTML='<div class="bp-empty"><i class="fa-solid fa-gamepad"></i><span class="bp-empty-text">No games found</span></div>';
      return;
    }
    applyLocalFilter();
  }

  function renderYears(years){
    if(!years||years.length===0) return;
    $header.style.display='flex';
    if(currentYear===0&&years.length>0) currentYear=years[0];
    var all=[{val:0,lbl:'All'}].concat(years.map(function(y){return {val:y,lbl:y};}));
    $yearFilters.innerHTML=all.map(function(item){
      return '<button class="bp-year-btn'+(currentYear===item.val?' active':'')+'" data-year="'+item.val+'">'+item.lbl+'</button>';
    }).join('');
    $yearFilters.querySelectorAll('[data-year]').forEach(function(btn){
      btn.addEventListener('click',function(){
        var y=parseInt(btn.dataset.year);if(y===currentYear)return;
        currentYear=y;
        $yearFilters.querySelectorAll('[data-year]').forEach(function(b){b.classList.remove('active');});
        btn.classList.add('active');
        loadStats(currentYear,currentMode);
      });
    });
    document.querySelectorAll('.bp-mode-btn').forEach(function(btn){
      btn.addEventListener('click',function(){
        var m=btn.dataset.mode;if(m===currentMode)return;
        currentMode=m;
        document.querySelectorAll('.bp-mode-btn').forEach(function(b){b.classList.remove('active');});
        btn.classList.add('active');
        loadStats(currentYear,m);
        $recentSec.style.display='block';
        if($recentBadge) $recentBadge.textContent='All Games';
        loadRecentGames();
      });
    });
  }

  function loadStats(year,mode){
    var body=new URLSearchParams({action:'get_booster_stats',booster_id:BOOSTER_ID,year:year||0,mode:mode||'all',champ_mode:'all'});
    fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(data){
        if(!data.ok){console.warn('[bp]',data.message);return;}
        if(!$yearFilters.children.length) renderYears(data.years||[]);
        renderRanks(data.ranks||[]);
        renderChamps(data.champions||[]);
      })
      .catch(function(e){console.error('[bp]',e);});
  }

  function loadRecentGames(){
    $recentSec.style.display='block';
    if($recentBadge) $recentBadge.textContent='All Games';
    $matchList.innerHTML='<div class="bp-spinner"><i class="fa-solid fa-spinner-third"></i></div>';
    var body=new URLSearchParams({action:'get_booster_performance',booster_id:BOOSTER_ID,page:1,per_page:5000,mode:'all',queue:'all',all:1,scope:'booster_order_dashboard'});
    fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(data){
        if(!data.ok) throw new Error(data.message);
        renderMatches(data.matches.rows||[]);
      })
      .catch(function(e){
        $matchList.innerHTML='<div class="bp-empty"><i class="fa-solid fa-triangle-exclamation"></i><span class="bp-empty-text">Could not load games</span></div>';
        console.error('[bp]',e);
      });
  }

  loadStats(0,'all');
  loadRecentGames();
})();
</script>
