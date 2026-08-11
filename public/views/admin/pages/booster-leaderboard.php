<?= $this->layout('admin/layouts/main', [
    'meta' => [
        'title' => 'Booster Leaderboard | Admin Area',
        'h1' => 'Booster Leaderboard',
        'description' => 'Monthly booster leaderboard ranking and scoring overview.',
    ]
]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$month = $month ?? date('Y-m');
$rows = is_array($rows ?? null) ? $rows : [];
$summary = is_array($summary ?? null) ? $summary : [];

$monthLabel = date('F Y', strtotime($month . '-01'));

$monthlyBonusAwards = [];
try {
    global $db;
    if (isset($db) && is_object($db)) {
        $awardRows = $db->run(
            "SELECT booster_id, position, bonus_percent, title, source_month
             FROM booster_monthly_bonus_awards
             WHERE award_month = ?
             ORDER BY position ASC",
            $month
        ) ?: [];

        foreach ($awardRows as $awardRow) {
            $awardBoosterId = (int)($awardRow['booster_id'] ?? 0);
            if ($awardBoosterId > 0) {
                $monthlyBonusAwards[$awardBoosterId] = $awardRow;
            }
        }
    }
} catch (Throwable $e) {
    $monthlyBonusAwards = [];
}


$fmt = function ($n, int $decimals = 2): string {
    $n = (float)$n;
    if (abs($n - round($n)) < 0.005) return number_format($n, 0, '.', ',');
    return number_format($n, $decimals, '.', ',');
};

$wrClass = function ($wr): string {
    $wr = (float)$wr;
    if ($wr >= 60) return 'green';
    if ($wr >= 50) return 'yellow';
    return 'red';
};

$rankHtml = function ($pos): string {
    $pos = (int)$pos;
    if ($pos === 1) return '<span class="abl-medal">🥇</span><span class="abl-rank-num">#1</span>';
    if ($pos === 2) return '<span class="abl-medal">🥈</span><span class="abl-rank-num">#2</span>';
    if ($pos === 3) return '<span class="abl-medal">🥉</span><span class="abl-rank-num">#3</span>';
    return '<span class="abl-rank-num">#' . $pos . '</span>';
};

$boosterIcon = function ($icon): string {
    $icon = (string)($icon ?? '');
    if ($icon === '') return ASSET_URL . '/core/main/img/default.png';
    if (preg_match('~^https?://~i', $icon)) return $icon;
    return rtrim(ASSET_URL, '/') . '/core/main/img/icons/' . rawurlencode($icon);
};

// Top score for score-bar scaling
$topScore = 1;
foreach ($rows as $r) {
    $s = (float)($r['score'] ?? 0);
    if ($s > $topScore) $topScore = $s;
}

$minGames = 10; // games needed to qualify
?>

<?= $this->start('styles') ?>
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
.abl-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:14px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 12px rgba(0,0,0,.2);}
.abl-icon{width:38px;height:38px;border-radius:12px;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.30);color:#c4b5fd;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.abl-icon.gold{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.26);color:#facc15;}
.abl-icon.green{background:rgba(74,222,128,.11);border-color:rgba(74,222,128,.24);color:#4ade80;}
.abl-icon.red{background:rgba(251,113,133,.11);border-color:rgba(251,113,133,.24);color:#fb7185;}
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
/* export btn */
.abl-export-btn{display:inline-flex;align-items:center;gap:7px;height:36px;padding:0 14px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.045);color:rgba(255,255,255,.72);font-size:.76rem;font-weight:800;cursor:pointer;transition:background .14s,border-color .14s,color .14s;}
.abl-export-btn:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.18);color:rgba(255,255,255,.92);}
.abl-export-btn i{font-size:.8rem;color:#4ade80;}
/* table */
.abl-table-wrap{overflow-x:auto;border:1px solid rgba(255,255,255,.07);border-radius:16px;background:rgba(255,255,255,.015);}
.abl-table{width:100%;border-collapse:collapse;min-width:1320px;}
.abl-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.abl-table th{padding:11px 12px;text-align:left;font-size:.67rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.35);white-space:nowrap;user-select:none;}
.abl-table th:not(:first-child):not(:nth-child(2)){text-align:center;}
.abl-table th.sortable{cursor:pointer;}
.abl-table th.sortable:hover{color:rgba(255,255,255,.7);}
.abl-table th .sort-icon::after{content:'';margin-left:4px;}
.abl-table th.sort-asc .sort-icon::after{content:'↑';color:#facc15;}
.abl-table th.sort-desc .sort-icon::after{content:'↓';color:#facc15;}
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
.abl-name{font-weight:900;color:rgba(255,255,255,.92);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.abl-name-link{color:inherit;text-decoration:none;}
.abl-name-link:hover{color:#c4b5fd;text-decoration:underline;}
.abl-subline{font-size:.7rem;color:rgba(255,255,255,.32);margin-top:2px;display:flex;align-items:center;gap:5px;}
.abl-badge{display:inline-flex;align-items:center;gap:.3rem;padding:3px 8px;border-radius:99px;font-size:.64rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;margin-left:7px;background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.28);color:#facc15;vertical-align:middle;}
.abl-bonus-badge{display:inline-flex;align-items:center;gap:.3rem;padding:3px 8px;border-radius:99px;font-size:.64rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;margin-left:7px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;vertical-align:middle;}
.abl-bonus-badge i{font-size:.68rem;}
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
/* breakdown */
.abl-breakdown{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:8px;margin-bottom:14px;}
.abl-break{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:9px 10px;}
.abl-break-label{font-size:.64rem;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.34);font-weight:900;}
.abl-break-value{font-size:.88rem;color:rgba(255,255,255,.86);font-weight:900;margin-top:2px;}
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
@media(max-width:992px){.abl-summary{grid-template-columns:repeat(2,minmax(0,1fr));}.abl-breakdown{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:640px){.abl-summary{grid-template-columns:1fr;}.abl-bar,.abl-panel-head{align-items:flex-start;}.abl-filters{width:100%;}.abl-month-picker,.abl-month-trigger{width:100%;}.abl-month-dropdown{right:auto;left:0;width:min(292px,calc(100vw - 32px));}.abl-body{padding:10px;}}
</style>
<?= $this->stop() ?>

<div class="abl-bar">
    <div>
        <div class="abl-kicker">Admin Area</div>
        <div class="abl-title"><i class="fa-duotone fa-trophy"></i> Monthly Booster Leaderboard</div>
        <div class="abl-sub">Admin overview for the fixed reward leaderboard. Below 50% winrate = 0 points. Between 50% and 60% winrate the score is dampened.</div>
    </div>
    <div class="abl-filters">
        <button type="button" class="abl-export-btn" id="ablExportBtn">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </button>
        <div class="abl-month-picker" id="ablMonthPicker">
            <button type="button" class="abl-month-trigger" id="ablMonthTrigger" aria-haspopup="true" aria-expanded="false">
                <span class="abl-month-trigger-label" id="ablMonthLabel"><?= $h($monthLabel) ?></span>
                <i class="fa-light fa-calendar"></i>
            </button>
            <div class="abl-month-dropdown" id="ablMonthDropdown">
                <div class="abl-month-dropdown-header">
                    <button type="button" class="abl-month-nav" id="ablMonthPrev"><i class="fa-light fa-chevron-left"></i></button>
                    <div class="abl-month-year" id="ablMonthYear"><?= $h(date('Y', strtotime($month . '-01'))) ?></div>
                    <button type="button" class="abl-month-nav" id="ablMonthNext"><i class="fa-light fa-chevron-right"></i></button>
                </div>
                <div class="abl-month-grid" id="ablMonthGrid"></div>
                <div class="abl-month-dropdown-footer">
                    <button type="button" class="abl-month-footer-btn" id="ablMonthCurrent">Current Month</button>
                    <button type="button" class="abl-month-footer-btn ghost" id="ablMonthClose">Close</button>
                </div>
            </div>
            <input type="hidden" id="ablMonthValue" value="<?= $h($month) ?>">
        </div>
    </div>
</div>

<div class="abl-summary">
    <div class="abl-card">
        <div class="abl-icon gold"><i class="fa-duotone fa-crown"></i></div>
        <div><div class="abl-label">Top Booster</div><div class="abl-value"><?= $h($summary['top_booster'] ?? '-') ?></div></div>
    </div>
    <div class="abl-card">
        <div class="abl-icon green"><i class="fa-duotone fa-users"></i></div>
        <div><div class="abl-label">Qualified</div><div class="abl-value"><?= (int)($summary['qualified'] ?? 0) ?> / <?= (int)($summary['boosters'] ?? 0) ?></div></div>
    </div>
    <div class="abl-card">
        <div class="abl-icon"><i class="fa-duotone fa-gamepad-modern"></i></div>
        <div><div class="abl-label">Tracked Games</div><div class="abl-value"><?= number_format((int)($summary['games'] ?? 0)) ?></div></div>
    </div>
    <div class="abl-card">
        <div class="abl-icon red"><i class="fa-duotone fa-circle-exclamation"></i></div>
        <div><div class="abl-label">Below 50% WR</div><div class="abl-value"><?= (int)($summary['below_50'] ?? 0) ?></div></div>
    </div>
</div>

<div class="abl-breakdown">
    <div class="abl-break"><div class="abl-break-label">System</div><div class="abl-break-value">Fixed Rewards</div></div>
    <div class="abl-break"><div class="abl-break-label">Top 5 Min Games</div><div class="abl-break-value">10</div></div>
    <div class="abl-break"><div class="abl-break-label">Min Winrate</div><div class="abl-break-value">50%</div></div>
    <div class="abl-break"><div class="abl-break-label">Full Score</div><div class="abl-break-value">60%+ WR</div></div>
    <div class="abl-break"><div class="abl-break-label">Duo Wins</div><div class="abl-break-value">Higher Reward</div></div>
    <div class="abl-break"><div class="abl-break-label">Solo Losses</div><div class="abl-break-value">Harder Penalty</div></div>
</div>

<div class="abl-panel">
    <div class="abl-panel-head">
        <div>
            <div class="abl-panel-title"><i class="fa-duotone fa-ranking-star"></i> Booster Ranking — <?= $h($monthLabel) ?></div>
            <div class="abl-note">Sorted by qualified status, score, winrate, and games played. Click column headers to sort. Click avatar for quick view.</div>
        </div>
        <div class="abl-pill-row">
            <span class="abl-pill gold"><i class="fa-solid fa-star"></i> Top 5 highlighted</span>
            <span class="abl-pill active"><i class="fa-solid fa-scale-balanced"></i> Fixed rewards</span>
            <span class="abl-pill green"><i class="fa-solid fa-user-group"></i> Duo rewarded</span>
            <span class="abl-pill"><i class="fa-solid fa-shield-xmark"></i> Losses penalized</span>
        </div>
    </div>

    <div class="abl-body">
        <div class="abl-table-wrap">
            <table class="abl-table" id="ablTable">
                <thead>
                    <tr>
                        <th class="sortable" data-col="0" data-type="num">Rank<span class="sort-icon"></span></th>
                        <th>Booster</th>
                        <th class="sortable" data-col="2" data-type="num">Games<span class="sort-icon"></span></th>
                        <th>Record</th>
                        <th class="sortable" data-col="4" data-type="num">WR<span class="sort-icon"></span></th>
                        <th>Solo</th>
                        <th>Duo</th>
                        <th class="sortable" data-col="7" data-type="num">Win Pts<span class="sort-icon"></span></th>
                        <th class="sortable" data-col="8" data-type="num">Loss<span class="sort-icon"></span></th>
                        <th>WR Bonus</th>
                        <th>Extra</th>
                        <th class="sortable" data-col="11" data-type="num">Score<span class="sort-icon"></span></th>
                    </tr>
                </thead>
                <tbody id="ablTbody">
<?php if (empty($rows)): ?>
                    <tr><td colspan="12">
                        <div style="text-align:center;padding:36px;color:rgba(255,255,255,.28);">
                            <i class="fa-solid fa-ranking-star" style="font-size:2.2rem;display:block;margin-bottom:10px;opacity:.25;"></i>
                            No tracked games for this month.
                        </div>
                    </td></tr>
<?php else: ?>
    <?php
    $prevQualified = null;
    foreach ($rows as $row):
        $isTop     = !empty($row['is_top_5']);
        $isQual    = !empty($row['qualified']);
        $extra     = (float)($row['activity_bonus'] ?? 0) + (float)($row['high_elo_bonus'] ?? 0) + (float)($row['duo_ratio_bonus'] ?? 0);
        $score     = (float)($row['score'] ?? 0);
        $wr        = (float)($row['winrate'] ?? 0);
        $games     = (int)($row['games'] ?? 0);
        $wins      = (int)($row['wins'] ?? 0);
        $losses    = (int)($row['losses'] ?? 0);
        $boosterId = (int)($row['booster_id'] ?? 0);
        $monthlyBonusAward = $monthlyBonusAwards[$boosterId] ?? null;
        $hasMonthlyBonus = is_array($monthlyBonusAward);
        $monthlyBonusPercent = $hasMonthlyBonus ? (float)($monthlyBonusAward['bonus_percent'] ?? 0) : 0;
        $monthlyBonusTitle = $hasMonthlyBonus ? (string)($monthlyBonusAward['title'] ?? 'Top Booster') : '';
        $monthlyBonusPosition = $hasMonthlyBonus ? (int)($monthlyBonusAward['position'] ?? 0) : 0;
        $monthlyBonusSourceMonth = $hasMonthlyBonus ? (string)($monthlyBonusAward['source_month'] ?? '') : '';
        $monthlyBonusTooltip = $hasMonthlyBonus
            ? trim($monthlyBonusTitle . ' · +' . $fmt($monthlyBonusPercent) . '% cut' . ($monthlyBonusSourceMonth !== '' ? ' from ' . date('F Y', strtotime($monthlyBonusSourceMonth . '-01')) : ''))
            : '';
        $barPct    = min(100, $topScore > 0 ? round($score / $topScore * 100) : 0);
        $position  = (int)($row['position'] ?? 0);
        $prevPos   = (int)($row['prev_position'] ?? 0);

        // Trend
        if ($prevPos === 0)              { $trendClass = 'same'; $trendIcon = '—'; $trendTitle = 'New'; }
        elseif ($position < $prevPos)    { $trendClass = 'up';   $trendIcon = '↑'; $trendTitle = 'Up from #' . $prevPos; }
        elseif ($position > $prevPos)    { $trendClass = 'down'; $trendIcon = '↓'; $trendTitle = 'Down from #' . $prevPos; }
        else                             { $trendClass = 'same'; $trendIcon = '—'; $trendTitle = 'No change'; }

        // Sub label
        $sub = $isQual ? 'Qualified' : ($wr < 50 ? 'Needs 50%+ WR' : 'Needs more games');

        // Qualification ring  r=7 => circumference ≈ 43.98
        $neededGames  = max(0, $minGames - $games);
        $ringR        = 7;
        $ringC        = 2 * M_PI * $ringR;
        $ringProgress = $isQual ? 1.0 : min(1.0, $games / $minGames);
        $ringOffset   = round($ringC * (1 - $ringProgress), 2);
        $ringColor    = $isQual ? '#4ade80' : ($ringProgress >= 0.6 ? '#facc15' : '#6d5cff');
        $ringTitle    = $isQual ? 'Qualified' : ($neededGames . ' more games needed');

        // Streak sparkline (simplified from total W/L, max 5 dots)
        $streak = [];
        $total  = $wins + $losses;
        if ($total > 0) {
            $dotCount = min(5, $total);
            $wFrac    = $wins / $total;
            for ($si = 0; $si < $dotCount; $si++) {
                $streak[] = (($si / $dotCount) < (1 - $wFrac)) ? 'l' : 'w';
            }
        }

        // Divider between qualified / unqualified
        $insertDivider = ($prevQualified === true && !$isQual);
        $prevQualified = $isQual;
    ?>
    <?php if ($insertDivider): ?>
                    <tr class="abl-divider-row">
                        <td colspan="12">
                            <div class="abl-table-divider"><span>Not yet qualified</span></div>
                        </td>
                    </tr>
    <?php endif; ?>
                    <tr class="<?= $isTop ? 'abl-top5' : '' ?> abl-data-row"
                        data-id="<?= $boosterId ?>"
                        data-name="<?= $h($row['username'] ?? '') ?>"
                        data-icon="<?= $h($boosterIcon($row['icon'] ?? '')) ?>"
                        data-games="<?= $games ?>"
                        data-wr="<?= $wr ?>"
                        data-wins="<?= $wins ?>"
                        data-losses="<?= $losses ?>"
                        data-score="<?= $score ?>"
                        data-win-pts="<?= $h($fmt($row['win_points'] ?? 0)) ?>"
                        data-loss-pen="<?= $h($fmt($row['loss_penalty'] ?? 0)) ?>"
                        data-wr-bonus="<?= $h($fmt($row['winrate_rewards'] ?? 0)) ?>"
                        data-extra="<?= $h($fmt($extra)) ?>"
                        data-sub="<?= $h($sub) ?>"
                        data-rank="<?= $position ?>">

                        <!-- Rank + Trend -->
                        <td>
                            <div class="abl-rank">
                                <?= $rankHtml($position) ?>
                                <span class="abl-trend <?= $trendClass ?>" title="<?= $h($trendTitle) ?>"><?= $trendIcon ?></span>
                            </div>
                        </td>

                        <!-- Booster -->
                        <td>
                            <div class="abl-booster">
                                <img class="abl-avatar js-qv"
                                     src="<?= $h($boosterIcon($row['icon'] ?? '')) ?>"
                                     alt="" title="Quick view">
                                <div>
                                    <div class="abl-name">
                                        <a href="<?= ADMN_URL ?>/booster/<?= $boosterId ?>" class="abl-name-link"><?= $h($row['username'] ?? 'Booster') ?></a>
                                        <?php if ($isTop): ?><span class="abl-badge"><i class="fa-solid fa-star"></i> Top 5</span><?php endif; ?>
                                        <?php if ($hasMonthlyBonus): ?>
                                            <span class="abl-bonus-badge" title="<?= $h($monthlyBonusTooltip) ?>"><i class="fa-solid fa-sack-dollar"></i> +<?= $h($fmt($monthlyBonusPercent)) ?>% Cut</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="abl-subline">
                                        <span class="abl-qual-wrap" title="<?= $h($ringTitle) ?>">
                                            <svg class="abl-ring-svg" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                                <circle class="abl-ring-bg" cx="9" cy="9" r="<?= $ringR ?>"/>
                                                <circle class="abl-ring-fg"
                                                        cx="9" cy="9" r="<?= $ringR ?>"
                                                        stroke="<?= $ringColor ?>"
                                                        stroke-dasharray="<?= round($ringC, 2) ?>"
                                                        stroke-dashoffset="<?= $ringOffset ?>"/>
                                            </svg>
                                            <?= $isQual ? 'Qualified' : ($neededGames . ' more') ?>
                                        </span>
                                        <span>· ID <?= $boosterId ?></span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Games -->
                        <td>
                            <div class="abl-main"><?= $games ?></div>
                            <div class="abl-small">Games</div>
                        </td>

                        <!-- Record + Streak -->
                        <td>
                            <div class="abl-main">
                                <span class="abl-win"><?= $wins ?>W</span>
                                <span class="abl-sep">/</span>
                                <span class="abl-loss"><?= $losses ?>L</span>
                            </div>
                            <?php if (!empty($streak)): ?>
                            <div class="abl-streak" title="Last <?= count($streak) ?> games (approx)">
                                <?php foreach ($streak as $s): ?><span class="abl-s-dot <?= $s ?>"></span><?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- WR -->
                        <td><span class="abl-wr <?= $wrClass($wr) ?>"><?= $fmt($wr) ?>%</span></td>

                        <!-- Solo -->
                        <td>
                            <div class="abl-main">
                                <span class="abl-win"><?= (int)($row['solo_wins'] ?? 0) ?>W</span>
                                <span class="abl-sep">/</span>
                                <span class="abl-loss"><?= (int)($row['solo_losses'] ?? 0) ?>L</span>
                            </div>
                        </td>

                        <!-- Duo -->
                        <td>
                            <div class="abl-main">
                                <span class="abl-win"><?= (int)($row['duo_wins'] ?? 0) ?>W</span>
                                <span class="abl-sep">/</span>
                                <span class="abl-loss"><?= (int)($row['duo_losses'] ?? 0) ?>L</span>
                            </div>
                        </td>

                        <!-- Win Pts -->
                        <td><div class="abl-main" style="color:#4ade80;">+<?= $fmt($row['win_points'] ?? 0) ?></div></td>

                        <!-- Loss -->
                        <td><div class="abl-main" style="color:#fb7185;">-<?= $fmt($row['loss_penalty'] ?? 0) ?></div></td>

                        <!-- WR Bonus -->
                        <td><div class="abl-main">+<?= $fmt($row['winrate_rewards'] ?? 0) ?></div></td>

                        <!-- Extra -->
                        <td><div class="abl-main">+<?= $fmt($extra) ?></div></td>

                        <!-- Score + bar -->
                        <td>
                            <div class="abl-score-wrap">
                                <div class="abl-score <?= $score <= 0 ? 'zero' : '' ?>"><?= $fmt($score) ?></div>
                                <div class="abl-score-bar-track">
                                    <div class="abl-score-bar-fill <?= $score <= 0 ? 'zero' : '' ?>" style="width:<?= $barPct ?>%"></div>
                                </div>
                                <?php if (isset($row['raw_score']) && (float)$row['raw_score'] != $score): ?>
                                    <div class="abl-small">Raw: <?= $fmt($row['raw_score']) ?></div>
                                <?php else: ?>
                                    <div class="abl-small">Points</div>
                                <?php endif; ?>
                            </div>
                        </td>

                    </tr>
    <?php endforeach; ?>
<?php endif; ?>
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

/* ── Month picker ── */
(function(){
    var picker=document.getElementById('ablMonthPicker'),trigger=document.getElementById('ablMonthTrigger'),
        lbl=document.getElementById('ablMonthLabel'),yearLbl=document.getElementById('ablMonthYear'),
        grid=document.getElementById('ablMonthGrid'),prev=document.getElementById('ablMonthPrev'),
        next=document.getElementById('ablMonthNext'),cur=document.getElementById('ablMonthCurrent'),
        closeBt=document.getElementById('ablMonthClose'),val=document.getElementById('ablMonthValue');
    if(!picker||!trigger||!grid||!val)return;
    var MN=['January','February','March','April','May','June','July','August','September','October','November','December'];
    var now=new Date(),cy=now.getFullYear(),cm=now.getMonth()+1;
    var sel=val.value||(cy+'-'+String(cm).padStart(2,'0')),dy=parseInt(sel.split('-')[0],10)||cy;
    function fmt(v){var p=String(v||'').split('-'),y=p[0]||cy,m=parseInt(p[1],10)||cm;return MN[m-1]+' '+y;}
    function close(){picker.classList.remove('open');trigger.setAttribute('aria-expanded','false');}
    function go(v){var u=new URL(window.location.href);u.searchParams.set('month',v);window.location.href=u.toString();}
    function render(){
        if(yearLbl)yearLbl.textContent=dy;grid.innerHTML='';
        var sp=sel.split('-'),sy=parseInt(sp[0],10),sm=parseInt(sp[1],10);
        MN.forEach(function(n,i){
            var mn=i+1,v=dy+'-'+String(mn).padStart(2,'0'),b=document.createElement('button');
            b.type='button';b.className='abl-month-item';b.textContent=n.slice(0,3);
            if(dy===sy&&mn===sm)b.classList.add('active');
            if(dy===cy&&mn===cm)b.classList.add('current');
            b.addEventListener('click',function(){sel=v;val.value=v;if(lbl)lbl.textContent=fmt(v);close();go(v);});
            grid.appendChild(b);
        });
    }
    trigger.addEventListener('click',function(){var o=picker.classList.toggle('open');trigger.setAttribute('aria-expanded',o?'true':'false');});
    if(prev)prev.addEventListener('click',function(){dy--;render();});
    if(next)next.addEventListener('click',function(){dy++;render();});
    if(cur)cur.addEventListener('click',function(){var v=cy+'-'+String(cm).padStart(2,'0');sel=v;dy=cy;val.value=v;if(lbl)lbl.textContent=fmt(v);close();go(v);});
    if(closeBt)closeBt.addEventListener('click',close);
    document.addEventListener('click',function(e){if(!picker.contains(e.target))close();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
    if(lbl)lbl.textContent=fmt(sel);render();
})();

/* ── Sortable columns ── */
(function(){
    var table=document.getElementById('ablTable');
    if(!table)return;
    var state={col:null,dir:'asc'};
    table.querySelectorAll('th.sortable').forEach(function(th){
        th.addEventListener('click',function(){
            var col=parseInt(th.dataset.col,10),type=th.dataset.type||'str';
            if(state.col===col){state.dir=state.dir==='asc'?'desc':'asc';}else{state.col=col;state.dir='asc';}
            table.querySelectorAll('th.sortable').forEach(function(t){t.classList.remove('sort-asc','sort-desc');});
            th.classList.add('sort-'+state.dir);
            var tbody=table.querySelector('tbody');
            var rows=Array.from(tbody.querySelectorAll('tr.abl-data-row'));
            rows.sort(function(a,b){
                var av=a.cells[col]?a.cells[col].textContent.trim():'';
                var bv=b.cells[col]?b.cells[col].textContent.trim():'';
                if(type==='num'){
                    av=parseFloat(av.replace(/[^0-9.\-]/g,''))||0;
                    bv=parseFloat(bv.replace(/[^0-9.\-]/g,''))||0;
                    return state.dir==='asc'?av-bv:bv-av;
                }
                return state.dir==='asc'?av.localeCompare(bv):bv.localeCompare(av);
            });
            tbody.querySelectorAll('tr.abl-divider-row').forEach(function(d){d.remove();});
            rows.forEach(function(r){tbody.appendChild(r);});
        });
    });
})();

/* ── Row flash on load ── */
setTimeout(function(){
    var rows=document.querySelectorAll('.abl-data-row');
    rows.forEach(function(r,i){setTimeout(function(){r.classList.add('abl-flash');},i*35);});
},150);

/* ── Quickview modal ── */
(function(){
    var modal=document.getElementById('ablModal'),backdrop=document.getElementById('ablBackdrop'),
        mClose=document.getElementById('ablModalClose'),mAvatar=document.getElementById('ablMAvatar'),
        mName=document.getElementById('ablMName'),mSub=document.getElementById('ablMSub'),
        mGrid=document.getElementById('ablMGrid'),mBreak=document.getElementById('ablMBreak');
    if(!modal)return;
    function stat(l,v){return '<div class="abl-modal__stat"><div class="abl-modal__stat-label">'+l+'</div><div class="abl-modal__stat-value">'+v+'</div></div>';}
    function row(k,v){return '<div class="abl-modal__row"><span class="abl-modal__row-key">'+k+'</span><span class="abl-modal__row-val">'+v+'</span></div>';}
    function open(tr){
        var d=tr.dataset;
        if(mAvatar)mAvatar.src=d.icon||'';
        if(mName)mName.textContent=d.name||'—';
        if(mSub)mSub.textContent='#'+d.rank+' · '+(d.sub||'—');
        if(mGrid)mGrid.innerHTML=stat('Games',d.games)+stat('Win Rate',parseFloat(d.wr||0).toFixed(2)+'%')+stat('Record',d.wins+'W / '+d.losses+'L')+stat('Score',d.score+' pts')+stat('Rank','#'+d.rank);
        if(mBreak)mBreak.innerHTML=row('Win Points','+'+d.winPts)+row('Loss Penalty','-'+d.lossPen)+row('WR Bonus','+'+d.wrBonus)+row('Extra Bonuses','+'+d.extra)+row('Final Score',d.score+' pts');
        modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');
    }
    function close(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');}
    document.addEventListener('click',function(e){
        var t=e.target.closest('.js-qv');
        if(t){var r=t.closest('tr');if(r)open(r);}
    });
    if(mClose)mClose.addEventListener('click',close);
    if(backdrop)backdrop.addEventListener('click',close);
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
})();

/* ── CSV Export ── */
(function(){
    var btn=document.getElementById('ablExportBtn'),table=document.getElementById('ablTable');
    if(!btn||!table)return;
    btn.addEventListener('click',function(){
        var lines=[['Rank','Booster','ID','Games','Wins','Losses','WR%','Solo W','Solo L','Duo W','Duo L','Win Pts','Loss Pen','WR Bonus','Extra','Score'].join(',')];
        table.querySelectorAll('tr.abl-data-row').forEach(function(r){
            var d=r.dataset,cells=r.querySelectorAll('td');
            var soloM=(cells[5]?cells[5].textContent:'').match(/(\d+)W\s*\/\s*(\d+)L/);
            var duoM =(cells[6]?cells[6].textContent:'').match(/(\d+)W\s*\/\s*(\d+)L/);
            lines.push([d.rank,'"'+(d.name||'')+'"',d.id,d.games,d.wins,d.losses,parseFloat(d.wr||0).toFixed(2),
                soloM?soloM[1]:'',soloM?soloM[2]:'',duoM?duoM[1]:'',duoM?duoM[2]:'',
                d.winPts,d.lossPen,d.wrBonus,d.extra,d.score].join(','));
        });
        var blob=new Blob([lines.join('\n')],{type:'text/csv;charset=utf-8;'});
        var url=URL.createObjectURL(blob),a=document.createElement('a');
        a.href=url;a.download='booster-leaderboard-<?= $h($month) ?>.csv';
        document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(url);
    });
})();

})();
</script>
