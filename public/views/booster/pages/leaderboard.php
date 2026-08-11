<?= $this->layout('booster/layouts/main', [
  'meta' => [
    'title'       => 'Leaderboard - Booster Area | LoLBoost.gg',
    'h1'          => 'Booster Leaderboard',
    'description' => 'Monthly ranking of the best boosters.',
  ]
]) ?>

<style>
/* tokens */
.abl-bar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.abl-kicker{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#f97316;}
.abl-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.88);display:flex;align-items:center;gap:8px;}
.abl-sub{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:2px;max-width:780px;}
.abl-filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
/* month picker */
.abl-month-picker{position:relative;flex-shrink:0;}
.abl-month-trigger{display:inline-flex;align-items:center;justify-content:space-between;gap:12px;min-width:184px;height:42px;padding:0 14px 0 16px;border-radius:999px;border:1px solid rgba(250,204,21,.35);background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025)),#2a2d30;color:rgba(255,255,255,.9);box-shadow:0 2px 16px rgba(0,0,0,.22),inset 0 1px 0 rgba(255,255,255,.04);font-size:.82rem;font-weight:900;cursor:pointer;transition:transform .14s,border-color .14s,box-shadow .14s;font-family:inherit;}
.abl-month-trigger:hover{transform:translateY(-1px);border-color:rgba(250,204,21,.55);box-shadow:0 8px 24px rgba(0,0,0,.28),0 0 0 3px rgba(250,204,21,.07);}
.abl-month-trigger:focus{outline:none;border-color:rgba(250,204,21,.65);box-shadow:0 0 0 3px rgba(250,204,21,.10);}
.abl-month-trigger-label{white-space:nowrap;}
.abl-month-trigger i{color:#facc15;font-size:.88rem;}
.abl-month-dropdown{position:absolute;top:calc(100% + 10px);right:0;width:292px;padding:14px;border-radius:18px;background:#1f2225;border:1px solid rgba(255,255,255,.09);box-shadow:0 24px 60px rgba(0,0,0,.5);opacity:0;visibility:hidden;transform:translateY(6px) scale(.98);transition:opacity .16s,visibility .16s,transform .16s;z-index:100;}
.abl-month-picker.open .abl-month-dropdown{opacity:1;visibility:visible;transform:translateY(0) scale(1);}
.abl-month-dropdown-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.abl-month-year{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.88);}
.abl-month-nav{width:34px;height:34px;border-radius:11px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);color:rgba(255,255,255,.68);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .14s,border-color .14s,color .14s;}
.abl-month-nav:hover{border-color:rgba(250,204,21,.32);background:rgba(250,204,21,.08);color:#facc15;}
.abl-month-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
.abl-month-item{height:40px;border-radius:12px;border:1px solid rgba(255,255,255,.065);background:rgba(255,255,255,.03);color:rgba(255,255,255,.76);font-size:.78rem;font-weight:900;cursor:pointer;transition:background .14s,border-color .14s,color .14s,transform .14s;}
.abl-month-item:hover{transform:translateY(-1px);border-color:rgba(250,204,21,.28);background:rgba(250,204,21,.08);color:#fff;}
.abl-month-item.active{border-color:rgba(250,204,21,.55);background:linear-gradient(135deg,rgba(250,204,21,.22),rgba(109,92,255,.12));color:#fff;}
.abl-month-item.current{position:relative;}
.abl-month-item.current::after{content:'';position:absolute;top:7px;right:7px;width:6px;height:6px;border-radius:50%;background:#facc15;box-shadow:0 0 8px rgba(250,204,21,.7);}
.abl-month-dropdown-footer{display:flex;gap:8px;margin-top:14px;padding-top:12px;border-top:1px solid rgba(255,255,255,.065);}
.abl-month-footer-btn{flex:1;height:36px;border-radius:12px;border:1px solid rgba(250,204,21,.2);background:rgba(250,204,21,.08);color:#facc15;font-size:.74rem;font-weight:900;cursor:pointer;transition:background .14s,border-color .14s;}
.abl-month-footer-btn:hover{background:rgba(250,204,21,.13);border-color:rgba(250,204,21,.35);}
.abl-month-footer-btn.ghost{border-color:rgba(255,255,255,.08);background:rgba(255,255,255,.035);color:rgba(255,255,255,.68);}
.abl-month-footer-btn.ghost:hover{border-color:rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:rgba(255,255,255,.86);}
/* summary */
.abl-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px;}
.abl-scard{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:14px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 12px rgba(0,0,0,.2);}
.abl-icon{width:38px;height:38px;border-radius:12px;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.30);color:#c4b5fd;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.abl-icon.gold{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.26);color:#facc15;}
.abl-icon.green{background:rgba(74,222,128,.11);border-color:rgba(74,222,128,.24);color:#4ade80;}
.abl-icon.orange{background:rgba(249,115,22,.11);border-color:rgba(249,115,22,.24);color:#f97316;}
.abl-label{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);}
.abl-value{font-size:1.05rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;margin-top:2px;}
/* panel */
.abl-panel{border:1px solid rgba(255,255,255,.07);border-radius:20px;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);overflow:hidden;}
.abl-panel-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.05);}
.abl-panel-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.88);display:flex;align-items:center;gap:8px;}
.abl-panel-title i{color:#facc15;}
.abl-note{font-size:.76rem;color:rgba(255,255,255,.35);}
.abl-pill-row{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.abl-pill{display:inline-flex;align-items:center;gap:.35rem;padding:5px 10px;border-radius:99px;font-size:.72rem;font-weight:800;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.58);}
.abl-pill.gold{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.28);color:#facc15;}
.abl-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.abl-pill.green{background:rgba(74,222,128,.11);border-color:rgba(74,222,128,.24);color:#4ade80;}
.abl-body{padding:14px 18px;}
/* table */
.abl-table-wrap{overflow-x:auto;border:1px solid rgba(255,255,255,.07);border-radius:16px;background:rgba(255,255,255,.015);}
.abl-table{width:100%;border-collapse:collapse;min-width:980px;}
.abl-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.abl-table th{padding:11px 12px;text-align:left;font-size:.67rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.35);white-space:nowrap;user-select:none;}
.abl-table th:not(:first-child):not(:nth-child(2)){text-align:center;}
.abl-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.abl-table tbody tr:last-child{border-bottom:none;}
.abl-table tbody tr:hover{background:rgba(109,92,255,.08);}
.abl-table tbody tr.abl-top5{background:linear-gradient(90deg,rgba(250,204,21,.09),rgba(109,92,255,.045));}
.abl-table tbody tr.abl-divider-row td{padding:0;border-bottom:none;}
.abl-table-divider{display:flex;align-items:center;gap:10px;padding:7px 12px;background:rgba(255,255,255,.02);border-top:1px dashed rgba(255,255,255,.07);border-bottom:1px dashed rgba(255,255,255,.07);}
.abl-table-divider span{font-size:.65rem;font-weight:900;text-transform:uppercase;letter-spacing:.09em;color:rgba(255,255,255,.26);}
.abl-table-divider::before,.abl-table-divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.06);}
.abl-table td{padding:11px 12px;color:rgba(255,255,255,.82);white-space:nowrap;font-size:.82rem;vertical-align:middle;}
.abl-table td:not(:first-child):not(:nth-child(2)){text-align:center;}
@keyframes ablFlash{0%{background:rgba(250,204,21,.15);}100%{background:transparent;}}
.abl-flash{animation:ablFlash .8s ease-out;}
/* rank + trend */
.abl-rank{font-weight:900;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:6px;}
.abl-medal{font-size:1.15rem;line-height:1;}
.abl-rank-num{font-size:.88rem;color:rgba(255,255,255,.55);}
.abl-trend{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:6px;font-size:.7rem;font-weight:900;flex-shrink:0;}
.abl-trend.up{background:rgba(74,222,128,.12);color:#4ade80;}
.abl-trend.down{background:rgba(251,113,133,.12);color:#fb7185;}
.abl-trend.same{background:rgba(255,255,255,.05);color:rgba(255,255,255,.28);}
/* booster cell */
.abl-booster{display:flex;align-items:center;gap:10px;min-width:0;}
.abl-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);flex-shrink:0;cursor:pointer;transition:border-color .14s,transform .14s;}
.abl-avatar:hover{border-color:rgba(109,92,255,.5);transform:scale(1.09);}
.abl-name{font-weight:900;color:rgba(255,255,255,.92);display:flex;align-items:center;gap:7px;max-width:330px;overflow:visible;white-space:nowrap;}
.abl-name-text{display:inline-block;max-width:155px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle;}
.abl-subline{font-size:.7rem;color:rgba(255,255,255,.32);margin-top:2px;display:flex;align-items:center;gap:5px;}
.abl-badge{display:inline-flex;align-items:center;gap:.3rem;padding:3px 8px;border-radius:99px;font-size:.64rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.28);color:#facc15;vertical-align:middle;flex-shrink:0;}
.abl-badge.bonus{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.28);color:#4ade80;}
.abl-badge.bonus i{color:#4ade80;}
.abl-you{display:inline-flex;align-items:center;padding:2px 7px;border-radius:99px;font-size:.62rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;margin-left:6px;background:rgba(109,92,255,.22);border:1px solid rgba(109,92,255,.45);color:#c4b5fd;vertical-align:middle;}
/* qualification ring */
.abl-qual-wrap{display:inline-flex;align-items:center;gap:4px;cursor:default;flex-shrink:0;}
.abl-ring-svg{width:18px;height:18px;flex-shrink:0;}
.abl-ring-bg{fill:none;stroke:rgba(255,255,255,.08);stroke-width:3;}
.abl-ring-fg{fill:none;stroke-width:3;stroke-linecap:round;transform-origin:50% 50%;transform:rotate(-90deg);}
/* stats */
.abl-main{font-size:.86rem;font-weight:900;color:rgba(255,255,255,.88);}
.abl-small{font-size:.68rem;color:rgba(255,255,255,.33);margin-top:2px;}
.abl-win{color:#4ade80;font-weight:900;}
.abl-loss{color:#fb7185;font-weight:900;}
.abl-sep{color:rgba(255,255,255,.24);font-weight:400;padding:0 2px;}
.abl-wr{display:inline-flex;align-items:center;justify-content:center;min-width:64px;padding:3px 9px;border-radius:99px;font-size:.78rem;font-weight:900;}
.abl-wr.green{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.26);color:#4ade80;}
.abl-wr.yellow{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.24);color:#facc15;}
.abl-wr.red{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.24);color:#fb7185;}
/* streak sparkline */
.abl-streak{display:inline-flex;align-items:center;gap:2px;margin-top:3px;}
.abl-s-dot{width:7px;height:7px;border-radius:2px;}
.abl-s-dot.w{background:#4ade80;}
.abl-s-dot.l{background:#fb7185;}
/* score bar */
.abl-score-wrap{display:flex;flex-direction:column;align-items:center;gap:3px;min-width:72px;}
.abl-score{font-size:.95rem;font-weight:900;color:#c4b5fd;}
.abl-score.zero{color:#fb7185;}
.abl-score-bar-track{width:100%;height:4px;border-radius:99px;background:rgba(255,255,255,.07);}
.abl-score-bar-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,#6d5cff,#c4b5fd);}
.abl-score-bar-fill.zero{background:#fb7185;}
/* "you" row glow */
.abl-me{box-shadow:inset 0 0 0 1px rgba(109,92,255,.35);}
/* empty / spinner */
.abl-spinner,.abl-empty{text-align:center;padding:32px 16px;color:rgba(255,255,255,.18);}
.abl-spinner i{animation:ablSpin .8s linear infinite;color:rgba(109,92,255,.6);font-size:1.2rem;margin-right:7px;}
.abl-empty i{font-size:1.2rem;margin-right:7px;opacity:.5;}
.abl-empty-text{font-size:.78rem;font-weight:600;letter-spacing:.03em;}
@keyframes ablSpin{to{transform:rotate(360deg);}}
/* quickview modal */
.abl-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:1rem;}
.abl-modal.is-open{display:flex;}
.abl-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(5px);}
.abl-modal__panel{position:relative;width:min(540px,100%);border-radius:22px;background:#1f2225;border:1px solid rgba(255,255,255,.1);box-shadow:0 24px 80px rgba(0,0,0,.6);overflow:hidden;}
.abl-modal__close{position:absolute;top:14px;right:14px;width:32px;height:32px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.06);color:rgba(255,255,255,.7);display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:1;}
.abl-modal__close:hover{background:rgba(255,255,255,.12);}
.abl-modal__hero{padding:22px 22px 14px;display:flex;align-items:center;gap:14px;border-bottom:1px solid rgba(255,255,255,.07);}
.abl-modal__avatar{width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid rgba(109,92,255,.4);}
.abl-modal__booster-name{font-size:1.1rem;font-weight:900;color:rgba(255,255,255,.92);}
.abl-modal__booster-sub{font-size:.76rem;color:rgba(255,255,255,.38);margin-top:3px;}
.abl-modal__body{padding:18px 22px;}
.abl-modal__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;}
.abl-modal__stat{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:13px;padding:11px 13px;}
.abl-modal__stat-label{font-size:.64rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.33);}
.abl-modal__stat-value{font-size:1rem;font-weight:900;color:rgba(255,255,255,.9);margin-top:3px;}
.abl-modal__section-title{font-size:.7rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.33);margin-bottom:8px;}
.abl-modal__row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05);}
.abl-modal__row:last-child{border-bottom:none;}
.abl-modal__row-key{font-size:.8rem;color:rgba(255,255,255,.48);}
.abl-modal__row-val{font-size:.8rem;font-weight:900;color:rgba(255,255,255,.85);}
@media(max-width:992px){.abl-summary{grid-template-columns:repeat(2,minmax(0,1fr));}.abl-name{max-width:280px;}.abl-name-text{max-width:120px;}.abl-badge.bonus{font-size:.6rem;padding:3px 7px;}}
@media(max-width:640px){.abl-summary{grid-template-columns:1fr;}.abl-bar,.abl-panel-head{align-items:flex-start;}.abl-filters{width:100%;}.abl-month-picker,.abl-month-trigger{width:100%;}.abl-month-dropdown{right:auto;left:0;width:min(292px,calc(100vw - 32px));}.abl-body{padding:10px;}}
</style>

<!-- Header bar -->
<div class="abl-bar">
  <div>
    <div class="abl-kicker">Order Dashboard</div>
    <div class="abl-title"><i class="fa-duotone fa-trophy"></i> Monthly Leaderboard</div>
    <div class="abl-sub">Top boosters ranked by monthly match performance.</div>
  </div>
  <div class="abl-filters">
    <div class="abl-month-picker" id="ablMonthPicker">
      <button type="button" class="abl-month-trigger" id="ablMonthTrigger" aria-haspopup="true" aria-expanded="false">
        <span class="abl-month-trigger-label" id="ablMonthLabel"><?= date('F Y') ?></span>
        <i class="fa-light fa-calendar"></i>
      </button>
      <div class="abl-month-dropdown" id="ablMonthDropdown">
        <div class="abl-month-dropdown-header">
          <button type="button" class="abl-month-nav" id="ablMonthPrev"><i class="fa-light fa-chevron-left"></i></button>
          <div class="abl-month-year" id="ablMonthYear"><?= date('Y') ?></div>
          <button type="button" class="abl-month-nav" id="ablMonthNext"><i class="fa-light fa-chevron-right"></i></button>
        </div>
        <div class="abl-month-grid" id="ablMonthGrid"></div>
        <div class="abl-month-dropdown-footer">
          <button type="button" class="abl-month-footer-btn" id="ablMonthCurrent">Current Month</button>
          <button type="button" class="abl-month-footer-btn ghost" id="ablMonthClose">Close</button>
        </div>
      </div>
      <input type="hidden" id="ablMonthValue" value="<?= date('Y-m') ?>">
    </div>
  </div>
</div>

<!-- Summary cards -->
<div class="abl-summary" id="ablSummary" style="display:none;">
  <div class="abl-scard">
    <div class="abl-icon gold"><i class="fa-duotone fa-crown"></i></div>
    <div><div class="abl-label">Top Booster</div><div class="abl-value" id="ablTopBooster">-</div></div>
  </div>
  <div class="abl-scard">
    <div class="abl-icon green"><i class="fa-duotone fa-users"></i></div>
    <div><div class="abl-label">Qualified</div><div class="abl-value" id="ablQualified">-</div></div>
  </div>
  <div class="abl-scard">
    <div class="abl-icon"><i class="fa-duotone fa-gamepad-modern"></i></div>
    <div><div class="abl-label">Tracked Games</div><div class="abl-value" id="ablGames">-</div></div>
  </div>
  <div class="abl-scard">
    <div class="abl-icon orange"><i class="fa-duotone fa-handshake-angle"></i></div>
    <div><div class="abl-label">Duo Bonus</div><div class="abl-value">+20%</div></div>
  </div>
</div>

<!-- Leaderboard panel -->
<p style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#f97316;margin-bottom:8px;">Performance</p>
<div class="abl-panel">
  <div class="abl-panel-head">
    <div>
      <div class="abl-panel-title"><i class="fa-duotone fa-ranking-star"></i> Booster Ranking</div>
      <div class="abl-note">Strict loss penalties. Minimum 10 games and 50% winrate required. 50–60% winrate is score-dampened.</div>
    </div>
    <div class="abl-pill-row">
      <span class="abl-pill gold"><i class="fa-solid fa-star"></i> Top 5 highlighted</span>
      <span class="abl-pill active"><i class="fa-solid fa-scale-balanced"></i> Fixed rewards</span>
      <span class="abl-pill green"><i class="fa-solid fa-user-group"></i> Duo weighted</span>
      <span class="abl-pill"><i class="fa-solid fa-shield-xmark"></i> Losses penalized</span>
    </div>
  </div>
  <div class="abl-body">
    <div class="abl-table-wrap">
      <table class="abl-table" id="ablTable">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Booster</th>
            <th>Games</th>
            <th>Record</th>
            <th>WR</th>
            <th>Solo</th>
            <th>Duo</th>
            <th>Points</th>
          </tr>
        </thead>
        <tbody id="ablTbody">
          <tr><td colspan="8"><div class="abl-spinner"><i class="fa-solid fa-spinner-third"></i><span class="abl-empty-text">Loading leaderboard...</span></div></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quickview Modal -->
<div class="abl-modal" id="ablModal" aria-hidden="true">
  <div class="abl-modal__backdrop" id="ablBackdrop"></div>
  <div class="abl-modal__panel" role="dialog" aria-modal="true">
    <button type="button" class="abl-modal__close" id="ablModalClose"><i class="fas fa-times"></i></button>
    <div class="abl-modal__hero">
      <img class="abl-modal__avatar" id="ablMAvatar" src="" alt="">
      <div>
        <div class="abl-modal__booster-name" id="ablMName">—</div>
        <div class="abl-modal__booster-sub" id="ablMSub">—</div>
      </div>
    </div>
    <div class="abl-modal__body">
      <div class="abl-modal__grid" id="ablMGrid"></div>
      <div class="abl-modal__section-title">Score Breakdown</div>
      <div id="ablMBreak"></div>
    </div>
  </div>
</div>

<script>
(function(){
'use strict';

var AJAX_ENDPOINT = '<?= defined('AJAX_URL') ? AJAX_URL : ((defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/ajax') ?>';
var ASSET_BASE    = '<?= ASSET_URL ?>';

/* ── helpers ── */
function esc(s){ return String(s||'').replace(/[&<>"']/g,function(m){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);}); }
function iconUrl(icon){ if(!icon) return ASSET_BASE+'/core/main/img/default.png'; if(/^https?:\/\//i.test(icon)) return icon; return ASSET_BASE.replace(/\/$/,'')+'/core/main/img/icons/'+encodeURIComponent(icon); }
function wrCls(wr){ wr=parseFloat(wr)||0; return wr>=60?'green':wr>=50?'yellow':'red'; }
function fmt(n){ n=parseFloat(n)||0; return n%1===0?String(n):n.toFixed(2); }
function rec(w,l){ return '<span class="abl-win">'+(parseInt(w)||0)+'W</span><span class="abl-sep">/</span><span class="abl-loss">'+(parseInt(l)||0)+'L</span>'; }
function qualText(row, minGames){
    var need=Math.max(0,(parseInt(minGames)||10)-(parseInt(row.games)||0)), wr=parseFloat(row.winrate)||0;
    if(need>0&&wr<50) return 'Needs '+need+' more games & 50%+ WR';
    if(need>0) return 'Needs '+need+' more games';
    if(wr<50) return 'Needs 50%+ winrate';
    return 'Not qualified';
}
function rankInner(p){
    p=parseInt(p)||0;
    if(p===1) return '<span class="abl-medal">🥇</span><span class="abl-rank-num">#1</span>';
    if(p===2) return '<span class="abl-medal">🥈</span><span class="abl-rank-num">#2</span>';
    if(p===3) return '<span class="abl-medal">🥉</span><span class="abl-rank-num">#3</span>';
    return '<span class="abl-rank-num">#'+p+'</span>';
}

/* ── Month picker ── */
(function(){
    var picker=document.getElementById('ablMonthPicker'),trigger=document.getElementById('ablMonthTrigger'),
        lbl=document.getElementById('ablMonthLabel'),yearLbl=document.getElementById('ablMonthYear'),
        grid=document.getElementById('ablMonthGrid'),prev=document.getElementById('ablMonthPrev'),
        next=document.getElementById('ablMonthNext'),cur=document.getElementById('ablMonthCurrent'),
        closeBt=document.getElementById('ablMonthClose'),val=document.getElementById('ablMonthValue');
    if(!picker||!trigger||!grid||!val) return;
    var MN=['January','February','March','April','May','June','July','August','September','October','November','December'];
    var now=new Date(),cy=now.getFullYear(),cm=now.getMonth()+1;
    var sel=val.value||(cy+'-'+String(cm).padStart(2,'0')),dy=parseInt(sel.split('-')[0],10)||cy;
    function fmtLbl(v){var p=String(v||'').split('-');return MN[(parseInt(p[1],10)||cm)-1]+' '+(p[0]||cy);}
    function close(){picker.classList.remove('open');trigger.setAttribute('aria-expanded','false');}
    function render(){
        if(yearLbl) yearLbl.textContent=dy; grid.innerHTML='';
        var sp=sel.split('-'),sy=parseInt(sp[0],10),sm=parseInt(sp[1],10);
        MN.forEach(function(n,i){
            var mn=i+1,v=dy+'-'+String(mn).padStart(2,'0'),b=document.createElement('button');
            b.type='button'; b.className='abl-month-item'; b.textContent=n.slice(0,3);
            if(dy===sy&&mn===sm) b.classList.add('active');
            if(dy===cy&&mn===cm) b.classList.add('current');
            b.addEventListener('click',function(){ sel=v; val.value=v; if(lbl) lbl.textContent=fmtLbl(v); render(); close(); loadLeaderboard(); });
            grid.appendChild(b);
        });
    }
    trigger.addEventListener('click',function(){var o=picker.classList.toggle('open');trigger.setAttribute('aria-expanded',o?'true':'false');});
    if(prev) prev.addEventListener('click',function(){dy--;render();});
    if(next) next.addEventListener('click',function(){dy++;render();});
    if(cur)  cur.addEventListener('click',function(){var v=cy+'-'+String(cm).padStart(2,'0');sel=v;dy=cy;val.value=v;if(lbl)lbl.textContent=fmtLbl(v);render();close();loadLeaderboard();});
    if(closeBt) closeBt.addEventListener('click',close);
    document.addEventListener('click',function(e){if(!picker.contains(e.target))close();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
    if(lbl) lbl.textContent=fmtLbl(sel);
    render();
})();

/* ── Summary ── */
function renderSummary(rows){
    var el=document.getElementById('ablSummary');
    if(!rows.length){ if(el) el.style.display='none'; return; }
    var qual=rows.filter(function(r){return !!r.qualified;}).length;
    var games=rows.reduce(function(s,r){return s+(parseInt(r.games)||0);},0);
    var top=rows[0]||null;
    var tb=document.getElementById('ablTopBooster'),qb=document.getElementById('ablQualified'),gb=document.getElementById('ablGames');
    if(tb) tb.textContent=(top&&top.username)||'-';
    if(qb) qb.textContent=qual+' / '+rows.length;
    if(gb) gb.textContent=games.toLocaleString();
    if(el)  el.style.display='grid';
}

/* ── Render rows ── */
function renderRows(rows, minGames, myId){
    var tbody=document.getElementById('ablTbody');
    if(!tbody) return;
    if(!rows.length){
        tbody.innerHTML='<tr><td colspan="8"><div class="abl-empty"><i class="fa-solid fa-ranking-star"></i><span class="abl-empty-text">No tracked games for this month.</span></div></td></tr>';
        return;
    }

    var topScore=1;
    rows.forEach(function(r){var s=parseFloat(r.score)||0;if(s>topScore)topScore=s;});

    var prevQual=null, html='';

    rows.forEach(function(row){
        var isTop  = !!row.is_top_5;
        var isQual = !!row.qualified;
        var isMe   = myId && (parseInt(row.booster_id)||parseInt(row.id||0))===parseInt(myId);
        var score  = parseFloat(row.score)||0;
        var wr     = parseFloat(row.winrate)||0;
        var games  = parseInt(row.games)||0;
        var wins   = parseInt(row.wins)||0;
        var losses = parseInt(row.losses)||0;
        var pos    = parseInt(row.position)||0;
        var prevPos= parseInt(row.prev_position)||0;
        var barPct = Math.min(100,Math.round(score/topScore*100));

        // Divider
        if(prevQual===true&&!isQual){
            html+='<tr class="abl-divider-row"><td colspan="8"><div class="abl-table-divider"><span>Not yet qualified</span></div></td></tr>';
        }
        prevQual=isQual;

        // Trend
        var tCls,tIcon,tTitle;
        if(!prevPos){tCls='same';tIcon='—';tTitle='New';}
        else if(pos<prevPos){tCls='up';tIcon='↑';tTitle='Up from #'+prevPos;}
        else if(pos>prevPos){tCls='down';tIcon='↓';tTitle='Down from #'+prevPos;}
        else{tCls='same';tIcon='—';tTitle='No change';}

        // Qual ring  r=7 circ≈43.98
        var ringR=7, ringC=2*Math.PI*ringR;
        var prog=isQual?1:Math.min(1,games/(parseInt(minGames)||10));
        var offset=(ringC*(1-prog)).toFixed(2);
        var ringClr=isQual?'#4ade80':prog>=0.6?'#facc15':'#6d5cff';
        var ringTip=isQual?'Qualified':Math.max(0,(parseInt(minGames)||10)-games)+' more games needed';

        // Sub label
        var sub=row.qualification_message||(isQual?'Qualified':qualText(row,minGames));

        // Streak sparkline
        var total=wins+losses, streak='';
        if(total>0){
            var dots=Math.min(5,total),wFrac=wins/total;
            for(var si=0;si<dots;si++) streak+='<span class="abl-s-dot '+(si/dots<(1-wFrac)?'l':'w')+'"></span>';
        }

        // Badges
        var monthlyBonusPercent = parseFloat(row.monthly_bonus_percent || 0);
        var monthlyBonusTitle = row.monthly_bonus_title || '';
        var badges=(isTop?'<span class="abl-badge"><i class="fa-solid fa-star"></i> Top 5</span>':'')
                  +(monthlyBonusPercent > 0 ? '<span class="abl-badge bonus" title="'+esc(monthlyBonusTitle)+' gets +'+esc(fmt(monthlyBonusPercent))+'% extra cut on completed orders"><i class="fa-solid fa-sack-dollar"></i> +'+esc(fmt(monthlyBonusPercent))+'% Cut</span>' : '')
                  +(isMe?'<span class="abl-you">You</span>':'');

        var rowCls=[isTop?'abl-top5':'',isMe?'abl-me':'','abl-data-row'].filter(Boolean).join(' ');

        html+=
          '<tr class="'+rowCls+'"'
            +' data-id="'+(parseInt(row.booster_id)||0)+'"'
            +' data-name="'+esc(row.username||'')+'"'
            +' data-icon="'+esc(iconUrl(row.icon||''))+'"'
            +' data-games="'+games+'"'
            +' data-wr="'+wr+'"'
            +' data-wins="'+wins+'"'
            +' data-losses="'+losses+'"'
            +' data-score="'+score+'"'
            +' data-win-pts="'+esc(fmt(row.win_points||0))+'"'
            +' data-loss-pen="'+esc(fmt(row.loss_penalty||0))+'"'
            +' data-wr-bonus="'+esc(fmt(row.winrate_rewards||0))+'"'
            +' data-extra="'+esc(fmt((parseFloat(row.activity_bonus||0)+parseFloat(row.high_elo_bonus||0)+parseFloat(row.duo_ratio_bonus||0))))+'"'
            +' data-monthly-bonus="'+esc(fmt(monthlyBonusPercent))+'"'
            +' data-monthly-bonus-title="'+esc(monthlyBonusTitle)+'"'
            +' data-sub="'+esc(sub)+'"'
            +' data-rank="'+pos+'">'
          // Rank
          +'<td><div class="abl-rank">'+rankInner(pos)+'<span class="abl-trend '+tCls+'" title="'+esc(tTitle)+'">'+tIcon+'</span></div></td>'
          // Booster
          +'<td><div class="abl-booster">'
            +'<img class="abl-avatar js-qv" src="'+esc(iconUrl(row.icon||''))+'" alt="" loading="lazy" onerror="this.style.opacity=.3">'
            +'<div>'
              +'<div class="abl-name"><span class="abl-name-text">'+esc(row.username||'Booster')+'</span>'+badges+'</div>'
              +'<div class="abl-subline">'
                +'<span class="abl-qual-wrap" title="'+esc(ringTip)+'">'
                  +'<svg class="abl-ring-svg" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">'
                    +'<circle class="abl-ring-bg" cx="9" cy="9" r="'+ringR+'"/>'
                    +'<circle class="abl-ring-fg" cx="9" cy="9" r="'+ringR+'" stroke="'+ringClr+'" stroke-dasharray="'+ringC.toFixed(2)+'" stroke-dashoffset="'+offset+'"/>'
                  +'</svg>'
                  +esc(isQual?'Qualified':Math.max(0,(parseInt(minGames)||10)-games)+' more')
                +'</span>'
              +'</div>'
            +'</div>'
          +'</div></td>'
          // Games
          +'<td>'
            +'<div class="abl-main">'+games+'</div>'
            +(streak?'<div class="abl-streak" title="Last '+Math.min(5,total)+' games">'+streak+'</div>':'')
          +'</td>'
          // Record
          +'<td><div class="abl-main">'+rec(wins,losses)+'</div><div class="abl-small">Record</div></td>'
          // WR
          +'<td><span class="abl-wr '+wrCls(wr)+'">'+fmt(wr)+'%</span></td>'
          // Solo
          +'<td><div class="abl-main">'+rec(row.solo_wins||0,row.solo_losses||0)+'</div><div class="abl-small">Solo</div></td>'
          // Duo
          +'<td><div class="abl-main">'+rec(row.duo_wins||0,row.duo_losses||0)+'</div><div class="abl-small">Duo</div></td>'
          // Score + bar
          +'<td>'
            +'<div class="abl-score-wrap">'
              +'<div class="abl-score'+(score<=0?' zero':'')+'">'+fmt(score)+'</div>'
              +'<div class="abl-score-bar-track"><div class="abl-score-bar-fill'+(score<=0?' zero':'')+'" style="width:'+barPct+'%"></div></div>'
              +'<div class="abl-small">Points</div>'
            +'</div>'
          +'</td>'
          +'</tr>';
    });

    tbody.innerHTML=html;

    // Flash rows
    setTimeout(function(){
        tbody.querySelectorAll('tr.abl-data-row').forEach(function(r,i){
            setTimeout(function(){r.classList.add('abl-flash');},i*35);
        });
    },80);
}

/* ── Quickview modal ── */
(function(){
    var modal=document.getElementById('ablModal'),backdrop=document.getElementById('ablBackdrop'),
        mClose=document.getElementById('ablModalClose'),mAvatar=document.getElementById('ablMAvatar'),
        mName=document.getElementById('ablMName'),mSub=document.getElementById('ablMSub'),
        mGrid=document.getElementById('ablMGrid'),mBreak=document.getElementById('ablMBreak');
    if(!modal) return;
    function stat(l,v){return '<div class="abl-modal__stat"><div class="abl-modal__stat-label">'+l+'</div><div class="abl-modal__stat-value">'+v+'</div></div>';}
    function mrow(k,v){return '<div class="abl-modal__row"><span class="abl-modal__row-key">'+k+'</span><span class="abl-modal__row-val">'+v+'</span></div>';}
    function open(tr){
        var d=tr.dataset;
        if(mAvatar) mAvatar.src=d.icon||'';
        if(mName)   mName.textContent=d.name||'—';
        if(mSub)    mSub.textContent='#'+d.rank+' · '+(d.sub||'—');
        if(mGrid)   mGrid.innerHTML=stat('Games',d.games)+stat('Win Rate',parseFloat(d.wr||0).toFixed(2)+'%')+stat('Record',d.wins+'W / '+d.losses+'L')+stat('Score',d.score+' pts')+stat('Rank','#'+d.rank)+stat('Status',d.sub||'—');
        if(mBreak)  mBreak.innerHTML=mrow('Win Points','+'+d.winPts)+mrow('Loss Penalty','-'+d.lossPen)+mrow('WR Bonus','+'+d.wrBonus)+mrow('Extra Bonuses','+'+d.extra)+(parseFloat(d.monthlyBonus||0)>0?mrow('Bonus Cut','+'+d.monthlyBonus+'% · '+(d.monthlyBonusTitle||'Top Booster')):'')+mrow('Final Score',d.score+' pts');
        modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false');
    }
    function close(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');}
    document.addEventListener('click',function(e){var t=e.target.closest('.js-qv');if(t){var r=t.closest('tr');if(r)open(r);}});
    if(mClose)   mClose.addEventListener('click',close);
    if(backdrop) backdrop.addEventListener('click',close);
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
})();

/* ── AJAX load ── */
function loadLeaderboard(){
    var tbody=document.getElementById('ablTbody');
    var summaryEl=document.getElementById('ablSummary');
    if(tbody) tbody.innerHTML='<tr><td colspan="8"><div class="abl-spinner"><i class="fa-solid fa-spinner-third"></i><span class="abl-empty-text">Loading leaderboard...</span></div></td></tr>';
    if(summaryEl) summaryEl.style.display='none';

    var val=document.getElementById('ablMonthValue');
    var body=new URLSearchParams({action:'get_booster_leaderboard',month:val?val.value:''});
    var ctrl=new AbortController();
    var tid=setTimeout(function(){ctrl.abort();},20000);

    fetch(AJAX_ENDPOINT,{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest'},
        body:body,signal:ctrl.signal
    })
    .then(function(r){clearTimeout(tid);return r.json();})
    .then(function(data){
        if(!data||!data.ok){
            if(tbody) tbody.innerHTML='<tr><td colspan="8"><div class="abl-empty" style="color:#fb7185"><i class="fa-solid fa-triangle-exclamation"></i><span class="abl-empty-text">'+esc((data&&data.message)||'Could not load leaderboard.')+'</span></div></td></tr>';
            return;
        }
        var rows=data.rows||[];
        var minGames=parseInt(data.minimum_games)||10;
        var myId=parseInt(data.current_booster_id)||0;
        renderSummary(rows);
        renderRows(rows,minGames,myId);
    })
    .catch(function(err){
        clearTimeout(tid);
        var msg=err&&err.name==='AbortError'?'Leaderboard request timed out. Please check the leaderboard indexes or AJAX SQL response.':'Could not load leaderboard.';
        if(tbody) tbody.innerHTML='<tr><td colspan="8"><div class="abl-empty" style="color:#fb7185"><i class="fa-solid fa-triangle-exclamation"></i><span class="abl-empty-text">'+msg+'</span></div></td></tr>';
    });
}

loadLeaderboard();
})();
</script>
