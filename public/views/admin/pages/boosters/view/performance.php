<?php
/**
 * Admin → Booster → Performance Tab
 * File: admin/pages/boosters/view/performance.php
 *
 * Reuses the same Ajax endpoints as the public booster profile:
 *   - get_booster_stats   (champion table + rank breakdown)
 *   - get_booster_performance (recent games)
 */
$boosterId = (int)($data['id'] ?? 0);
$boosterName = htmlspecialchars($data['username'] ?? 'Booster', ENT_QUOTES, 'UTF-8');
?>

<style>
/* ═══════════════════════════════════════════════════
   Admin Performance Tab — Design System
   ═══════════════════════════════════════════════════ */

/* ── Shared animation ── */
@keyframes apSpin{to{transform:rotate(360deg);}}

/* ── Top toolbar card ── */
.ap-toolbar{
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:10px;
  background:#25282a;border:1px solid rgba(255,255,255,.07);
  border-radius:20px;padding:14px 18px;
  margin-bottom:18px;box-shadow:0 4px 24px rgba(0,0,0,.22);
}
.ap-toolbar-left,.ap-toolbar-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}

/* ── Pills ── */
.ap-pill{
  display:inline-flex;align-items:center;gap:.3rem;
  padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;
  cursor:pointer;border:1px solid rgba(255,255,255,.09);
  background:rgba(255,255,255,.04);color:rgba(255,255,255,.55);
  transition:background .12s,border-color .12s,color .12s;
  user-select:none;font-family:inherit;
}
.ap-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.ap-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.ap-pill-sep{width:1px;height:20px;background:rgba(255,255,255,.09);margin:0 2px;}

/* ── Section headers ── */
.ap-section-header{display:flex;align-items:baseline;gap:8px;margin-bottom:14px;}
.ap-section-eyebrow{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.1em;color:#f97316;}
.ap-section-title{font-size:1.05rem;font-weight:900;color:rgba(255,255,255,.9);margin:0;}
.ap-section-sub{font-size:.76rem;color:rgba(255,255,255,.35);font-weight:500;}

/* ── Rank slider ── */
.ap-rank-wrap{position:relative;margin-bottom:28px;}
.ap-rank-cards{
  display:flex;gap:10px;overflow-x:auto;
  scroll-snap-type:x mandatory;scrollbar-width:none;
  padding:4px 2px 8px;cursor:grab;user-select:none;
}
.ap-rank-cards.dragging{cursor:grabbing;}
.ap-rank-cards::-webkit-scrollbar{display:none;}
.ap-rank-card{
  flex:0 0 148px;scroll-snap-align:start;
  background:#25282a;border:1px solid rgba(255,255,255,.07);
  border-radius:18px;padding:14px 12px;
  display:flex;flex-direction:column;align-items:center;gap:8px;
  transition:border-color .15s;box-shadow:0 2px 14px rgba(0,0,0,.22);
}
.ap-rank-card:hover{border-color:rgba(109,92,255,.35);}
.ap-rank-card.empty{opacity:.38;}
.ap-rank-card.empty img{filter:grayscale(1);}
.ap-rank-card img{width:50px;height:50px;object-fit:contain;}
.ap-rank-name{font-size:.88rem;font-weight:900;color:rgba(255,255,255,.9);}
.ap-rank-rows{width:100%;display:flex;flex-direction:column;gap:4px;}
.ap-rank-row{display:flex;justify-content:space-between;font-size:.75rem;}
.ap-rank-row span{color:rgba(255,255,255,.38);}
.ap-rank-row strong{font-weight:800;color:rgba(255,255,255,.85);}
.ap-rank-row strong.c-win{color:#4ade80;}
.ap-rank-row strong.c-green{color:#4ade80;}
.ap-rank-row strong.c-yellow{color:#facc15;}
.ap-rank-row strong.c-red{color:#fb7185;}

/* Slider arrows */
.ap-arrow{
  position:absolute;top:50%;transform:translateY(-50%);z-index:2;
  width:28px;height:28px;border-radius:50%;
  background:#25282a;border:1px solid rgba(109,92,255,.35);
  color:#c4b5fd;font-size:.75rem;
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:all .15s;box-shadow:0 3px 10px rgba(0,0,0,.4);
}
.ap-arrow:hover{background:rgba(109,92,255,.25);color:#fff;}
.ap-arrow:disabled{opacity:.22;cursor:default;pointer-events:none;}
.ap-arrow-l{left:-8px;}
.ap-arrow-r{right:-8px;}

/* ── Champion table card ── */
.ap-champ-card{
  background:#25282a;border:1px solid rgba(255,255,255,.07);
  border-radius:20px;overflow:hidden;
  box-shadow:0 4px 32px rgba(0,0,0,.26);margin-bottom:28px;
}
.ap-champ-table{width:100%;border-collapse:collapse;}
.ap-champ-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.ap-champ-table th{
  padding:11px 16px;text-align:left;
  font-size:.67rem;font-weight:900;text-transform:uppercase;
  letter-spacing:.07em;color:rgba(255,255,255,.32);white-space:nowrap;
}
.ap-champ-table th:not(:first-child){text-align:center;}
.ap-champ-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.ap-champ-table tbody tr:last-child{border-bottom:none;}
.ap-champ-table tbody tr:hover{background:rgba(109,92,255,.07);}
.ap-champ-table td{padding:11px 16px;color:rgba(255,255,255,.8);font-size:.85rem;}
.ap-champ-table td:not(:first-child){text-align:center;}
.ap-champ-cell{display:flex;align-items:center;gap:9px;}
.ap-champ-cell img{width:28px;height:28px;border-radius:7px;object-fit:cover;}
.ap-champ-cell span{font-weight:800;color:rgba(255,255,255,.9);}
.ap-kda-good{color:#f97316;font-weight:900;}
.ap-kda-great{color:#facc15;font-weight:900;}
.ap-wr-green{color:#4ade80;font-weight:800;}
.ap-wr-yellow{color:#facc15;font-weight:800;}
.ap-wr-red{color:#fb7185;font-weight:800;}

/* ── Games card ── */
.ap-games-card{
  background:#25282a;border:1px solid rgba(255,255,255,.07);
  border-radius:20px;padding:18px 18px 14px;
  box-shadow:0 4px 32px rgba(0,0,0,.26);
}
.ap-games-head{
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:10px;margin-bottom:14px;
}
.ap-games-title{font-size:1rem;font-weight:900;color:rgba(255,255,255,.9);display:flex;align-items:center;gap:8px;}
.ap-games-badge{
  font-size:.67rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;
  padding:3px 9px;border-radius:99px;
  background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.3);color:#c4b5fd;
}
.ap-games-link{
  display:inline-flex;align-items:center;gap:6px;
  padding:6px 14px;border-radius:10px;
  background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.28);
  color:#a5b4fc;font-size:.78rem;font-weight:800;text-decoration:none;
  transition:background .13s,border-color .13s;
}
.ap-games-link:hover{background:rgba(99,102,241,.22);border-color:rgba(99,102,241,.5);color:#c4b5fd;}

/* ── Match list filter pills inside card ── */
.ap-games-filter-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px;}

/* ── Match summary ── */
.ap-match-summary{
  display:flex;flex-wrap:wrap;gap:6px 14px;
  font-size:.77rem;color:rgba(255,255,255,.4);margin-bottom:10px;
}
.ap-match-summary strong{color:rgba(255,255,255,.82);}

/* ── Toolbar: search + bulk ── */
.ap-match-toolbar{
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:10px;margin-bottom:12px;
}
.ap-search-wrap{position:relative;flex:1 1 260px;max-width:420px;}
.ap-search-wrap i{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);font-size:.8rem;pointer-events:none;}
.ap-search-wrap input{
  width:100%;background:rgba(255,255,255,.04)!important;
  border:1px solid rgba(255,255,255,.09)!important;
  border-radius:10px!important;color:rgba(255,255,255,.85)!important;
  padding:7px 12px 7px 34px!important;font-size:.83rem!important;
  outline:none;transition:border-color .15s,box-shadow .15s;
}
.ap-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;}
.ap-search-wrap input::placeholder{color:rgba(255,255,255,.22)!important;}
.ap-bulk-wrap{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.ap-select-all-label{
  display:inline-flex;align-items:center;gap:7px;
  font-size:.78rem;color:rgba(255,255,255,.45);cursor:pointer;user-select:none;
}
.ap-bulk-delete-btn{
  min-height:34px;border-radius:10px;
  border:1px solid rgba(251,113,133,.28);background:rgba(251,113,133,.10);
  color:#fb7185;display:inline-flex;align-items:center;gap:7px;
  padding:0 14px;font-size:.78rem;font-weight:800;
  cursor:pointer;transition:background .12s,border-color .12s;
}
.ap-bulk-delete-btn:hover:not(:disabled){background:rgba(251,113,133,.18);border-color:rgba(251,113,133,.45);}
.ap-bulk-delete-btn:disabled{opacity:.38;cursor:not-allowed;}

/* ── Match rows ── */
.ap-match-list{display:flex;flex-direction:column;gap:6px;}
.ap-match-row{
  display:grid;
  grid-template-columns:24px 5px 46px minmax(0,1fr) 120px 90px 100px 90px 90px 36px;
  align-items:center;gap:10px;
  padding:10px 14px;border-radius:13px;
  border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025);
  transition:background .12s;
}
.ap-match-row:hover{background:rgba(109,92,255,.07);}
.ap-stripe{width:5px;height:44px;border-radius:3px;}
.ap-stripe.win{background:#4ade80;}
.ap-stripe.loss{background:#fb7185;}
.ap-stripe.remake{background:rgba(255,255,255,.25);}
.ap-champ-icon{width:46px;height:46px;border-radius:9px;overflow:hidden;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);}
.ap-champ-icon img{width:100%;height:100%;object-fit:cover;}
.ap-match-info{min-width:0;}
.ap-match-name{font-size:.9rem;font-weight:800;color:rgba(255,255,255,.92);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ap-match-queue{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:2px;}
.ap-result-badge{
  display:inline-flex;align-items:center;gap:.3rem;
  padding:3px 9px;border-radius:99px;
  font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;margin-top:3px;
}
.ap-result-badge.win{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;}
.ap-result-badge.loss{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185;}
.ap-result-badge.remake{background:rgba(148,163,184,.12);border:1px solid rgba(148,163,184,.25);color:#94a3b8;}
.ap-kda-block{text-align:center;}
.ap-kda-val{font-size:.92rem;font-weight:900;color:rgba(255,255,255,.9);}
.ap-kda-val .k{color:#4ade80;}
.ap-kda-val .d{color:#fb7185;}
.ap-kda-val .sep{color:rgba(255,255,255,.22);font-weight:400;}
.ap-kda-ratio{font-size:.72rem;color:rgba(255,255,255,.38);}
.ap-kda-ratio span{color:#c4b5fd;font-weight:700;}
.ap-dur-block{text-align:center;font-size:.8rem;color:rgba(255,255,255,.38);}
.ap-dur-block strong{display:block;font-size:.88rem;font-weight:800;color:rgba(255,255,255,.8);}
.ap-rank-block{text-align:center;font-size:.76rem;color:rgba(255,255,255,.38);}
.ap-rank-block strong{display:block;font-size:.8rem;font-weight:800;color:rgba(255,255,255,.8);white-space:nowrap;}
.ap-date-block{text-align:right;font-size:.76rem;color:rgba(255,255,255,.32);}
.ap-del-btn{
  width:32px;height:32px;border-radius:9px;
  border:1px solid rgba(251,113,133,.2);background:rgba(251,113,133,.07);
  color:#fb7185;display:inline-flex;align-items:center;justify-content:center;
  cursor:pointer;transition:background .12s,border-color .12s;font-size:.78rem;
}
.ap-del-btn:hover{background:rgba(251,113,133,.16);border-color:rgba(251,113,133,.4);}
.ap-del-btn:disabled{opacity:.38;cursor:wait;}

/* ── Checkbox ── */
.ap-chk{
  appearance:none;-webkit-appearance:none;
  width:17px;height:17px;border-radius:5px;
  border:1.5px solid rgba(255,255,255,.18);
  background:rgba(255,255,255,.06);
  cursor:pointer;flex-shrink:0;position:relative;
  transition:background .12s,border-color .12s;
  display:inline-block;vertical-align:middle;
}
.ap-chk:hover{border-color:rgba(109,92,255,.6);background:rgba(109,92,255,.12);}
.ap-chk:checked{background:#6d5cff;border-color:#6d5cff;}
.ap-chk:checked::after{content:'';position:absolute;left:4px;top:1.5px;width:5px;height:9px;border:2px solid #fff;border-top:0;border-left:0;transform:rotate(45deg);}
.ap-chk:indeterminate{background:rgba(109,92,255,.4);border-color:rgba(109,92,255,.7);}
.ap-chk:indeterminate::after{content:'';position:absolute;left:3px;top:6.5px;width:9px;height:2px;background:#fff;border-radius:1px;}
.ap-chk:disabled{opacity:.3;cursor:not-allowed;}

/* ── Pagination ── */
.ap-pager{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:5px;margin-top:14px;}
.ap-page-btn{
  width:32px;height:32px;border-radius:8px;
  border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);
  color:rgba(255,255,255,.65);font-size:.8rem;font-weight:700;
  display:inline-flex;align-items:center;justify-content:center;
  cursor:pointer;transition:background .12s;
}
.ap-page-btn:hover:not(:disabled),.ap-page-btn.active{background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.ap-page-btn:disabled{opacity:.3;cursor:default;}

/* ── Empty + spinner ── */
.ap-empty{text-align:center;padding:44px 24px;color:rgba(255,255,255,.3);}
.ap-empty i{font-size:2.2rem;display:block;margin-bottom:10px;opacity:.3;}
.ap-spinner{display:flex;align-items:center;justify-content:center;padding:40px;color:#6d5cff;}
.ap-spinner i{font-size:1.6rem;animation:apSpin .8s linear infinite;}

/* ── Responsive ── */
@media(max-width:992px){
  .ap-match-row{grid-template-columns:24px 5px 42px minmax(0,1fr) 90px 36px;}
  .ap-dur-block,.ap-date-block,.ap-kda-block,.ap-rank-block{display:none;}
}
</style>

<div class="row">
  <div class="col-12">

    <!-- ── Top toolbar: Year + Mode pills ── -->
    <div class="ap-toolbar" id="apToolbar" style="display:none;">
      <div class="ap-toolbar-left" id="apYearPills">
        <!-- year pills injected by JS -->
      </div>
      <div class="ap-toolbar-right">
        <div class="ap-pill-sep"></div>
        <button type="button" class="ap-pill" data-mode="solo"><i class="fa-solid fa-user" style="font-size:.65rem;"></i> Solo</button>
        <button type="button" class="ap-pill" data-mode="duo"><i class="fa-solid fa-users" style="font-size:.65rem;"></i> Duo</button>
        <button type="button" class="ap-pill active" data-mode="all">All</button>
      </div>
    </div>

    <!-- ── Competitive Progression ── -->
    <div id="apRankSection" style="display:none;margin-bottom:28px;">
      <div class="ap-section-header">
        <span class="ap-section-eyebrow">Rank History</span>
        <h4 class="ap-section-title">Competitive Progression</h4>
      </div>
      <div class="ap-rank-wrap">
        <button class="ap-arrow ap-arrow-l" id="apRankPrev"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="ap-rank-cards" id="apRankCards"></div>
        <button class="ap-arrow ap-arrow-r" id="apRankNext"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- ── Champion Statistics ── -->
    <div id="apChampSection" style="display:none;margin-bottom:28px;">
      <div class="ap-section-header" style="margin-bottom:12px;">
        <span class="ap-section-eyebrow">Performance</span>
        <h4 class="ap-section-title">Champion Statistics</h4>
        <span class="ap-section-sub">(Own games)</span>
      </div>
      <div class="ap-champ-card">
        <table class="ap-champ-table">
          <thead>
            <tr>
              <th>Champion</th>
              <th>Games</th>
              <th>Kills</th>
              <th>Deaths</th>
              <th>Assists</th>
              <th>KDA Ratio</th>
              <th>Winrate</th>
            </tr>
          </thead>
          <tbody id="apChampTbody"></tbody>
        </table>
      </div>
    </div>

    <!-- ── Recent Games card ── -->
    <div class="ap-games-card" id="apRecentSection">
      <!-- Card header -->
      <div class="ap-games-head">
        <div class="ap-games-title">
          Recent Games
          <span class="ap-games-badge" id="apRecentBadge">All Games</span>
        </div>
        <a id="apGamesLink"
           href="<?= ADMN_URL ?>/booster-games?booster=<?= $boosterId ?>"
           class="ap-games-link">
          <i class="fa-duotone fa-swords" style="font-size:.82rem;"></i>
          Open in Booster Games
        </a>
      </div>

      <!-- Local filter pills -->
      <div class="ap-games-filter-bar">
        <button type="button" class="ap-pill active" data-lf="all">All</button>
        <button type="button" class="ap-pill" data-lf="solo"><i class="fa-solid fa-user" style="font-size:.63rem;"></i> Solo</button>
        <button type="button" class="ap-pill" data-lf="duo"><i class="fa-solid fa-users" style="font-size:.63rem;"></i> Duo</button>
        <button type="button" class="ap-pill" data-lf="win"><i class="fa-solid fa-check" style="font-size:.63rem;"></i> Wins</button>
        <button type="button" class="ap-pill" data-lf="loss"><i class="fa-solid fa-xmark" style="font-size:.63rem;"></i> Losses</button>
      </div>

      <!-- Summary -->
      <div class="ap-match-summary" id="apMatchSummary"></div>

      <!-- Search + bulk toolbar -->
      <div class="ap-match-toolbar" id="apMatchToolbar" style="display:none;">
        <div class="ap-search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="search" id="apMatchSearch" placeholder="Search champion, order ID, match ID, result…">
        </div>
        <div class="ap-bulk-wrap">
          <label class="ap-select-all-label">
            <input type="checkbox" class="ap-chk" id="apSelectAll">
            Select page
          </label>
          <button type="button" class="ap-bulk-delete-btn" id="apBulkDelete" disabled>
            <i class="fa-solid fa-trash"></i> Delete selected
          </button>
        </div>
      </div>

      <!-- Match list -->
      <div class="ap-match-list" id="apMatchList">
        <div class="ap-spinner"><i class="fa-solid fa-spinner-third"></i></div>
      </div>

      <!-- Pagination -->
      <div class="ap-pager" id="apPager" style="display:none;"></div>
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

  // ── State ──
  var currentYear = 0;
  var currentMode = 'all';   // global Solo/Duo/All filter (controls API call)
  var localFilter = 'all';   // client-side filter: all/solo/duo/win/loss
  var searchTerm  = '';
  var recentPage  = 1;
  var perPage     = 10;
  var allRows     = [];      // all rows from last API call
  var filteredRows= [];      // after local filter + search
  var selectedIds = {};

  // ── DOM refs ──
  var $toolbar   = document.getElementById('apToolbar');
  var $yearPills = document.getElementById('apYearPills');
  var $rankSec   = document.getElementById('apRankSection');
  var $rankCards = document.getElementById('apRankCards');
  var $champSec  = document.getElementById('apChampSection');
  var $champBody = document.getElementById('apChampTbody');
  var $matchList = document.getElementById('apMatchList');
  var $badge     = document.getElementById('apRecentBadge');
  var $summary   = document.getElementById('apMatchSummary');
  var $toolbar2  = document.getElementById('apMatchToolbar');
  var $search    = document.getElementById('apMatchSearch');
  var $selectAll = document.getElementById('apSelectAll');
  var $bulkDel   = document.getElementById('apBulkDelete');
  var $pager     = document.getElementById('apPager');
  var $gamesLink = document.getElementById('apGamesLink');

  // ── Lookup data ──
  var ALL_RANKS = [
    {t:0,n:'Unranked'},{t:1,n:'Iron'},{t:2,n:'Bronze'},
    {t:3,n:'Silver'},{t:4,n:'Gold'},{t:5,n:'Platinum'},
    {t:6,n:'Emerald'},{t:7,n:'Diamond'},{t:8,n:'Master'},
    {t:9,n:'Grandmaster'},{t:10,n:'Challenger'}
  ];
  var QUEUES={0:'Custom',2:'Blind Pick',4:'Ranked Solo',6:'Ranked Premade',7:'Co-op vs AI',8:'3v3 Normal',9:'3v3 Ranked Flex',14:'Draft Pick',16:'Dominion Blind',17:'Dominion Draft',25:'Dominion Co-op vs AI',31:'Co-op vs AI Intro',32:'Co-op vs AI Beginner',33:'Co-op vs AI Intermediate',41:'3v3 Ranked Team',42:'5v5 Ranked Team',52:'3v3 Co-op vs AI',61:'Team Builder',65:'ARAM',67:'ARAM Co-op vs AI',70:'One for All',72:'1v1 Snowdown',73:'2v2 Snowdown',75:'Hexakill',76:'URF',78:'One For All: Mirror',83:'Co-op vs AI URF',91:'Doom Bots Rank 1',92:'Doom Bots Rank 2',93:'Doom Bots Rank 5',96:'Ascension',98:'3v3 Hexakill',100:'ARAM',300:'Poro King',310:'Nemesis',313:'Black Market Brawlers',315:'Nexus Siege',317:'Definitely Not Dominion',318:'ARURF',325:'All Random',400:'Normal Draft',410:'Ranked Dynamic',420:'Ranked Solo/Duo',430:'Normal Blind',440:'Ranked Flex',450:'ARAM',460:'3v3 Blind Pick',470:'3v3 Ranked Flex',480:'Swiftplay',490:'Quickplay',600:'Blood Hunt Assassin',610:'Dark Star: Singularity',700:'Clash',720:'ARAM Clash',800:'3v3 Co-op vs AI Intermediate',810:'3v3 Co-op vs AI Intro',820:'3v3 Co-op vs AI Beginner',830:'Co-op vs AI Intro',840:'Co-op vs AI Beginner',850:'Co-op vs AI Intermediate',870:'Co-op vs AI Intro',880:'Co-op vs AI Beginner',890:'Co-op vs AI Intermediate',900:'ARURF',910:'Ascension',920:'Poro King',940:'Nexus Siege',950:'Doom Bots Voting',960:'Doom Bots Standard',980:'Star Guardian: Normal',990:'Star Guardian: Onslaught',1000:'PROJECT: Hunters',1010:'Snow ARURF',1020:'One for All',1030:'Odyssey: Intro',1040:'Odyssey: Cadet',1050:'Odyssey: Crewmember',1060:'Odyssey: Captain',1070:'Odyssey: Onslaught',1090:'TFT',1100:'Ranked TFT',1110:'TFT Tutorial',1111:'TFT Test',1200:'Nexus Blitz',1210:"TFT Choncc's Treasure",1300:'Nexus Blitz',1400:'Ultimate Spellbook',1700:'Arena',1710:'Arena',1810:'Swarm Solo',1820:'Swarm Duo',1830:'Swarm Trio',1840:'Swarm Squad',1900:'Pick URF',2000:'Tutorial 1',2010:'Tutorial 2',2020:'Tutorial 3',2300:'Brawl',2400:'ARAM: Mayhem'};

  // ── Helpers ──
  function h(s){ var d=document.createElement('div'); d.textContent=String(s); return d.innerHTML; }
  function fmtDur(s){ s=parseInt(s)||0; return Math.floor(s/60)+':'+String(s%60).padStart(2,'0'); }
  function fmtDate(iso){
    if(!iso) return '';
    try{ var d=new Date(iso.replace(' ','T')); return String(d.getDate()).padStart(2,'0')+'.'+String(d.getMonth()+1).padStart(2,'0')+'.'+d.getFullYear(); }catch(e){return '';}
  }
  function kdaStr(k,d,a){ k=parseInt(k)||0;d=parseInt(d)||0;a=parseInt(a)||0; if(d===0) return (k+a)===0?'0.00':'Perfect'; return ((k+a)/d).toFixed(2); }
  function champUrl(n){ if(!n) return ''; var fix={'Wukong':'MonkeyKing'}; return LOL_CHAMP+'/'+(fix[n]||n.replace(/[^a-zA-Z0-9]/g,''))+'.png'; }

  // ── Sync "Open in Booster Games" link ──
  function syncLink(){
    if(!$gamesLink) return;
    var base = $gamesLink.href.split('?')[0];
    $gamesLink.href = base + '?booster=' + BOOSTER_ID + '&mode=' + currentMode;
  }

  // ── Render rank slider ──
  function renderRanks(ranks){
    $rankSec.style.display = 'block';
    var map = {};
    (ranks||[]).forEach(function(r){ map[r.tier] = r; });
    $rankCards.innerHTML = ALL_RANKS.map(function(rank){
      var r = map[rank.t];
      var w=r?r.wins:0, l=r?r.losses:0, wr=r?r.winrate:0;
      var empty = (w===0 && l===0);
      var wrCls = empty ? '' : (wr>=60 ? 'c-green' : (wr>=50 ? 'c-yellow' : 'c-red'));
      return '<div class="ap-rank-card'+(empty?' empty':'')+'">'
        +'<img src="'+ASSET_BASE+'/core/main/img/lol/ranks/max/'+rank.t+'.png" alt="'+rank.n+'" onerror="this.style.opacity=0">'
        +'<div class="ap-rank-name">'+rank.n+'</div>'
        +'<div class="ap-rank-rows">'
        +'<div class="ap-rank-row"><span>Win</span><strong class="c-win">'+w+'</strong></div>'
        +'<div class="ap-rank-row"><span>Lose</span><strong>'+l+'</strong></div>'
        +'<div class="ap-rank-row"><span>WR</span><strong class="'+wrCls+'">'+wr.toFixed(empty?0:2)+'%</strong></div>'
        +'</div></div>';
    }).join('');
    initDrag();
  }

  function initDrag(){
    var down=false, sx, sl;
    $rankCards.addEventListener('mousedown',function(e){ down=true; $rankCards.classList.add('dragging'); sx=e.pageX-$rankCards.offsetLeft; sl=$rankCards.scrollLeft; });
    document.addEventListener('mouseup',function(){ down=false; $rankCards.classList.remove('dragging'); });
    $rankCards.addEventListener('mouseleave',function(){ down=false; $rankCards.classList.remove('dragging'); });
    $rankCards.addEventListener('mousemove',function(e){ if(!down) return; e.preventDefault(); $rankCards.scrollLeft=sl-(e.pageX-$rankCards.offsetLeft-sx)*1.2; });
    var prev=document.getElementById('apRankPrev'), next=document.getElementById('apRankNext');
    if(prev) prev.onclick=function(){ $rankCards.scrollBy({left:-$rankCards.offsetWidth*.6,behavior:'smooth'}); };
    if(next) next.onclick=function(){ $rankCards.scrollBy({left:$rankCards.offsetWidth*.6,behavior:'smooth'}); };
    function ua(){ if(prev) prev.disabled=$rankCards.scrollLeft<5; if(next) next.disabled=$rankCards.scrollLeft>=$rankCards.scrollWidth-$rankCards.clientWidth-5; }
    $rankCards.addEventListener('scroll',ua); setTimeout(ua,120);
  }

  // ── Render champ table ──
  function renderChamps(champs){
    $champSec.style.display = 'block';
    if(!champs||!champs.length){
      $champBody.innerHTML='<tr><td colspan="7" class="ap-empty"><i class="fa-solid fa-chart-bar"></i>No champion stats yet.</td></tr>';
      return;
    }
    $champBody.innerHTML = champs.map(function(c){
      var kv = parseFloat(c.kda)||0;
      var kCls = kv>=5 ? 'ap-kda-great' : (kv>=3 ? 'ap-kda-good' : '');
      var wCls = c.winrate>=60 ? 'ap-wr-green' : (c.winrate>=50 ? 'ap-wr-yellow' : 'ap-wr-red');
      var img = champUrl(c.champion);
      return '<tr>'
        +'<td><div class="ap-champ-cell">'+(img?'<img src="'+img+'" alt="'+h(c.champion)+'" onerror="this.style.opacity=0">':'')
        +'<span>'+h(c.champion)+'</span></div></td>'
        +'<td>'+c.games+'</td>'
        +'<td>'+c.avg_kills.toFixed(2)+'</td>'
        +'<td>'+c.avg_deaths.toFixed(2)+'</td>'
        +'<td>'+c.avg_assists.toFixed(2)+'</td>'
        +'<td class="'+kCls+'">'+kv.toFixed(2)+'</td>'
        +'<td class="'+wCls+'">'+c.winrate.toFixed(2)+'%</td>'
        +'</tr>';
    }).join('');
  }

  // ── Local filter + search on loaded rows ──
  function applyLocalFilter(){
    filteredRows = allRows.filter(function(r){
      var res = r.result || (parseInt(r.won) ? 'win' : 'loss');
      var isDuo = parseInt(r.is_duo)===1;
      if(localFilter==='solo' && isDuo) return false;
      if(localFilter==='duo' && !isDuo) return false;
      if(localFilter==='win' && res!=='win') return false;
      if(localFilter==='loss' && res!=='loss') return false;
      if(searchTerm){
        var q = QUEUES[parseInt(r.queue_id)]||'Game';
        var txt = [r.id, r.order_id, r.match_id, r.champion, q, res, r.kills, r.deaths, r.assists, r.played_at].join(' ').toLowerCase();
        if(txt.indexOf(searchTerm)===-1) return false;
      }
      return true;
    });
    recentPage = 1;
    renderPage();
  }

  function getPageRows(){ var s=(recentPage-1)*perPage; return filteredRows.slice(s,s+perPage); }

  function renderPage(){
    var pageRows = getPageRows();
    var total = filteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total/perPage));
    if(recentPage>totalPages) recentPage=totalPages;
    pageRows = getPageRows();

    // Update badge
    var badgeMap={all:'All Games',solo:'Solo Games',duo:'Duo Games',win:'Wins only',loss:'Losses only'};
    if($badge) $badge.textContent = badgeMap[localFilter]||'All Games';

    // Summary
    if($summary){
      var wins=0,losses=0;
      filteredRows.forEach(function(r){ var res=r.result||(parseInt(r.won)?'win':'loss'); if(res==='win') wins++; else if(res==='loss') losses++; });
      var wr = total>0 ? Math.round((wins/(wins+losses||1))*100) : 0;
      $summary.innerHTML = '<span><strong>'+total+'</strong> games</span>'
        +'<span><strong style="color:#4ade80;">'+wins+'W</strong> <strong style="color:#fb7185;">'+losses+'L</strong></span>'
        +(total>0?'<span><strong style="color:#c4b5fd;">'+wr+'%</strong> WR</span>':'')
        +(total!==allRows.length?'<span>(filtered from '+allRows.length+')</span>':'');
    }

    if(!allRows.length){
      if($toolbar2) $toolbar2.style.display='none';
      if($pager) $pager.style.display='none';
      $matchList.innerHTML='<div class="ap-empty"><i class="fa-solid fa-gamepad"></i>No games found.</div>';
      updateBulkUi();
      return;
    }

    if($toolbar2) $toolbar2.style.display='flex';

    if(!filteredRows.length){
      if($pager) $pager.style.display='none';
      $matchList.innerHTML='<div class="ap-empty"><i class="fa-solid fa-magnifying-glass"></i>No games match your filter / search.</div>';
      updateBulkUi();
      return;
    }

    $matchList.innerHTML = pageRows.map(function(r){
      var res = r.result||(parseInt(r.won)?'win':'loss');
      var k=parseInt(r.kills)||0, d=parseInt(r.deaths)||0, a=parseInt(r.assists)||0;
      var kd = kdaStr(k,d,a);
      var q = QUEUES[parseInt(r.queue_id)]||'Game';
      if(parseInt(r.is_duo)===1) q+=' · Duo';
      if(r.order_id) q+=' · Order #'+r.order_id;
      var img = champUrl(r.champion);
      var lbl = res==='remake'?'Remake':(res==='win'?'WIN':'LOSS');
      var mid = parseInt(r.id)||0;
      var chk = selectedIds[String(mid)] ? ' checked' : '';
      return '<div class="ap-match-row" data-match-id="'+mid+'">'
        +'<div style="display:flex;justify-content:center;align-items:center;"><input type="checkbox" class="ap-chk ap-row-chk" data-mid="'+mid+'"'+chk+'></div>'
        +'<div class="ap-stripe '+res+'"></div>'
        +'<div class="ap-champ-icon">'+(img?'<img src="'+img+'" alt="'+h(r.champion||'')+'" onerror="this.style.opacity=0.3">':'')+'</div>'
        +'<div class="ap-match-info">'
        +'<div class="ap-match-name">'+h(r.champion||'Unknown')+'</div>'
        +'<div class="ap-match-queue">'+h(q)+'</div>'
        +'<span class="ap-result-badge '+res+'">'+lbl+'</span>'
        +'</div>'
        +'<div class="ap-kda-block">'
        +'<div class="ap-kda-val"><span class="k">'+k+'</span><span class="sep"> / </span><span class="d">'+d+'</span><span class="sep"> / </span>'+a+'</div>'
        +'<div class="ap-kda-ratio"><span>'+kd+'</span> KDA</div>'
        +'</div>'
        +'<div class="ap-dur-block"><strong>'+fmtDur(r.duration)+'</strong>DURATION</div>'
        +'<div class="ap-rank-block"><strong>'+(r.rank_snapshot ? r.rank_snapshot.split(' · ')[0] : '—')+'</strong>'+(r.rank_snapshot && r.rank_snapshot.split(' · ')[1] ? r.rank_snapshot.split(' · ')[1] : 'RANK')+'</div>'
        +'<div class="ap-date-block">'+fmtDate(r.played_at)+'</div>'
        +'<div style="display:flex;justify-content:flex-end;align-items:center;"><button type="button" class="ap-del-btn" data-mid="'+mid+'"><i class="fa-solid fa-trash"></i></button></div>'
        +'</div>';
    }).join('');

    renderPager(totalPages);
    updateBulkUi();
  }

  function renderPager(totalPages){
    if(!$pager) return;
    if(totalPages<=1){ $pager.style.display='none'; $pager.innerHTML=''; return; }
    $pager.style.display='flex';
    var html = '<button class="ap-page-btn" data-p="prev" '+(recentPage<=1?'disabled':'')+'><i class="fa-solid fa-chevron-left"></i></button>';
    var from=Math.max(1,recentPage-2), to=Math.min(totalPages,recentPage+2);
    if(from>1){ html+='<button class="ap-page-btn" data-p="1">1</button>'; if(from>2) html+='<button class="ap-page-btn" disabled>…</button>'; }
    for(var p=from;p<=to;p++) html+='<button class="ap-page-btn'+(p===recentPage?' active':'')+'" data-p="'+p+'">'+p+'</button>';
    if(to<totalPages){ if(to<totalPages-1) html+='<button class="ap-page-btn" disabled>…</button>'; html+='<button class="ap-page-btn" data-p="'+totalPages+'">'+totalPages+'</button>'; }
    html+='<button class="ap-page-btn" data-p="next" '+(recentPage>=totalPages?'disabled':'')+'><i class="fa-solid fa-chevron-right"></i></button>';
    $pager.innerHTML=html;
  }

  function updateBulkUi(){
    var ids=Object.keys(selectedIds).filter(function(id){ return selectedIds[id]; });
    if($bulkDel){ $bulkDel.disabled=ids.length===0; $bulkDel.innerHTML='<i class="fa-solid fa-trash"></i> Delete selected'+(ids.length?' ('+ids.length+')':''); }
    if($selectAll){
      var pageIds=getPageRows().map(function(r){ return String(parseInt(r.id)||0); }).filter(function(id){return id!=='0';});
      var checked=pageIds.filter(function(id){ return !!selectedIds[id]; }).length;
      $selectAll.checked=pageIds.length>0&&checked===pageIds.length;
      $selectAll.indeterminate=checked>0&&checked<pageIds.length;
    }
  }

  // ── Year pills ──
  function renderYears(years){
    if(!years||!years.length) return;
    $toolbar.style.display='flex';
    if(currentYear===0&&years.length>0) currentYear=years[0];
    var items=[{v:0,l:'All'}].concat(years.map(function(y){return {v:y,l:y};}));
    $yearPills.innerHTML=items.map(function(item){
      return '<button type="button" class="ap-pill'+(currentYear===item.v?' active':'')+'" data-year="'+item.v+'">'+item.l+'</button>';
    }).join('');
    $yearPills.querySelectorAll('[data-year]').forEach(function(btn){
      btn.addEventListener('click',function(){
        var y=parseInt(btn.dataset.year);
        if(y===currentYear) return;
        currentYear=y;
        $yearPills.querySelectorAll('[data-year]').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        loadStats(currentYear,currentMode);
      });
    });
  }

  // Wire mode pills (Solo/Duo/All in toolbar)
  document.querySelectorAll('[data-mode]').forEach(function(btn){
    btn.addEventListener('click',function(){
      var m=btn.dataset.mode;
      if(m===currentMode) return;
      currentMode=m;
      document.querySelectorAll('[data-mode]').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      loadStats(currentYear,currentMode);
      loadGames();
      syncLink();
    });
  });

  // Wire local filter pills (All/Solo/Duo/Win/Loss inside games card)
  document.querySelectorAll('[data-lf]').forEach(function(btn){
    btn.addEventListener('click',function(){
      var f=btn.dataset.lf;
      if(f===localFilter) return;
      localFilter=f;
      document.querySelectorAll('[data-lf]').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      applyLocalFilter();
    });
  });

  // Search
  if($search) $search.addEventListener('input',function(){ searchTerm=this.value.trim().toLowerCase(); recentPage=1; applyLocalFilter(); });

  // Pagination
  if($pager) $pager.addEventListener('click',function(e){
    var btn=e.target.closest ? e.target.closest('[data-p]') : null;
    if(!btn||btn.disabled) return;
    var totalPages=Math.max(1,Math.ceil(filteredRows.length/perPage));
    var p=btn.getAttribute('data-p');
    if(p==='prev') recentPage=Math.max(1,recentPage-1);
    else if(p==='next') recentPage=Math.min(totalPages,recentPage+1);
    else recentPage=Math.max(1,Math.min(totalPages,parseInt(p)||1));
    renderPage();
  });

  // Select all on page
  if($selectAll) $selectAll.addEventListener('change',function(){
    getPageRows().forEach(function(r){
      var id=String(parseInt(r.id)||0);
      if(id==='0') return;
      if($selectAll.checked) selectedIds[id]=true;
      else delete selectedIds[id];
    });
    renderPage();
  });

  // Row checkbox delegation
  if($matchList){
    $matchList.addEventListener('change',function(e){
      var cb=e.target&&e.target.classList&&e.target.classList.contains('ap-row-chk')?e.target:null;
      if(!cb) return;
      var id=String(parseInt(cb.getAttribute('data-mid'))||0);
      if(id==='0') return;
      if(cb.checked) selectedIds[id]=true; else delete selectedIds[id];
      updateBulkUi();
    });
    // Delete button
    $matchList.addEventListener('click',function(e){
      var btn=e.target.closest ? e.target.closest('.ap-del-btn') : null;
      if(!btn) return;
      deleteOne(parseInt(btn.getAttribute('data-mid'))||0, btn);
    });
  }

  // Bulk delete
  if($bulkDel) $bulkDel.addEventListener('click',deleteBulk);

  // ── API calls ──
  function loadStats(year,mode){
    var body=new URLSearchParams({action:'get_booster_stats',booster_id:BOOSTER_ID,year:year||0,mode:mode||'all',champ_mode:'all'});
    fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(data){
        if(!data.ok){console.warn('[ap]',data.message);return;}
        if(!$yearPills.children.length) renderYears(data.years||[]);
        renderRanks(data.ranks||[]);
        renderChamps(data.champions||[]);
      })
      .catch(function(e){console.error('[ap]',e);});
  }

  function loadGames(){
    var modeParam=['solo','duo','all'].indexOf(currentMode)!==-1?currentMode:'all';
    $matchList.innerHTML='<div class="ap-spinner"><i class="fa-solid fa-spinner-third"></i></div>';
    var body=new URLSearchParams({action:'get_booster_performance',booster_id:BOOSTER_ID,page:1,per_page:5000,mode:modeParam,queue:'all',all:1,scope:'booster_order_dashboard'});
    fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(data){
        if(!data.ok) throw new Error(data.message);
        allRows=Array.isArray(data.matches.rows)?data.matches.rows:[];
        selectedIds={};
        searchTerm='';
        if($search) $search.value='';
        localFilter='all';
        document.querySelectorAll('[data-lf]').forEach(function(b){ b.classList.toggle('active',b.dataset.lf==='all'); });
        recentPage=1;
        applyLocalFilter();
      })
      .catch(function(e){
        $matchList.innerHTML='<div class="ap-empty"><i class="fa-solid fa-triangle-exclamation"></i>Could not load games.</div>';
        console.error('[ap]',e);
      });
  }

  function deleteOne(matchId, btn){
    if(!matchId) return;
    if(!confirm('Delete this game from the booster performance history?')) return;
    if(btn) btn.disabled=true;
    var body=new URLSearchParams({action:'admin_delete_order_match',match_id:matchId});
    fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(data){
        if(!data.ok) throw new Error(data.message||'Could not delete game.');
        loadStats(currentYear,currentMode);
        loadGames();
      })
      .catch(function(e){ if(btn) btn.disabled=false; alert(e.message||'Error'); });
  }

  function deleteBulk(){
    var ids=Object.keys(selectedIds).filter(function(id){return selectedIds[id];});
    if(!ids.length) return;
    if(!confirm('Delete '+ids.length+' selected game(s) from the booster performance history?')) return;
    if($bulkDel) $bulkDel.disabled=true;
    var body=new URLSearchParams({action:'admin_delete_order_matches',match_ids:ids.join(',')});
    fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(data){
        if(!data.ok) throw new Error(data.message||'Could not delete games.');
        selectedIds={};
        loadStats(currentYear,currentMode);
        loadGames();
      })
      .catch(function(e){ if($bulkDel) $bulkDel.disabled=false; alert(e.message||'Error'); });
  }

  // ── Init ──
  syncLink();
  loadStats(0,'all');
  loadGames();
})();
</script>
