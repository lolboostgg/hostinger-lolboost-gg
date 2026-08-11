<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'worldcup-page']) ?>

<?php
if (!function_exists('lb_wc_client_id')) {
    function lb_wc_client_id(): int {
        if (defined('CLIENT_ID')) return (int)CLIENT_ID;
        if (defined('CLIENT_DATA') && is_array(CLIENT_DATA)) return (int)(CLIENT_DATA['id'] ?? 0);
        if (isset($GLOBALS['client']) && is_array($GLOBALS['client'])) return (int)($GLOBALS['client']['id'] ?? 0);
        return 0;
    }
}
if (!function_exists('lb_wc_db_run')) {
    function lb_wc_db_run($db, string $sql, ...$params): array {
        if (!$db || !method_exists($db, 'run')) return [];
        try { $rows = $db->run($sql, ...$params); return is_array($rows) ? $rows : []; }
        catch (Throwable $e) { return []; }
    }
}
?>

<?php
global $db;

$clientId  = lb_wc_client_id();
$boosterId = defined('BOOSTER_ID') ? (int)BOOSTER_ID : (int)(BOOSTER_DATA['id'] ?? 0);

$participantType = '';
$participantId   = 0;
if ($clientId > 0)       { $participantType = 'client';  $participantId = $clientId; }
elseif ($boosterId > 0)  { $participantType = 'booster'; $participantId = $boosterId; }

$isClient      = $participantType === 'client';
$isBooster     = $participantType === 'booster';
$isParticipant = $participantId > 0;

$activeMatchday = max(1, min(3, (int)($_GET['matchday'] ?? 1)));

$matches = lb_wc_db_run($db, 'SELECT * FROM worldcup_matches WHERE matchday = ? ORDER BY kickoff_at ASC, id ASC', $activeMatchday);
$predictions = [];
$reward = null;

if ($isParticipant) {
    $rows = [];
    if ($isClient) {
        $rows = lb_wc_db_run($db, "SELECT *, 'client' AS participant_type, client_id AS participant_id FROM worldcup_predictions WHERE client_id = ?", $clientId);
    } else {
        $rows = lb_wc_db_run($db, 'SELECT * FROM worldcup_predictions WHERE participant_type = ? AND participant_id = ?', $participantType, $participantId);
    }
    foreach ($rows as $row) {
        $predictions[(int)$row['match_id']] = $row;
    }

    if ($isClient) {
        $rewardRows = lb_wc_db_run($db, 'SELECT * FROM worldcup_rewards WHERE client_id = ? LIMIT 1', $clientId);
        $reward = $rewardRows[0] ?? null;
    }
}

$leaderboard = lb_wc_db_run($db, "
    SELECT 'client' AS participant_type, p.client_id AS participant_id,
           COALESCE(NULLIF(c.username,''), c.email, CONCAT('Client#', c.id)) AS name,
           c.icon AS icon,
           COALESCE(SUM(p.points), 0) AS points,
           COUNT(p.id) AS tips
    FROM worldcup_predictions p
    LEFT JOIN clients c ON c.id = p.client_id
    WHERE c.id IS NOT NULL
    GROUP BY p.client_id
    ORDER BY points DESC, tips DESC, p.client_id ASC
    LIMIT 20");
if (!$leaderboard) { $leaderboard = []; }

$totalParticipants = (int)(lb_wc_db_run($db, 'SELECT COUNT(DISTINCT client_id) AS cnt FROM worldcup_predictions')[0]['cnt'] ?? 0);

$flagMap = [
    'Mexico' => 'mx', 'South Africa' => 'za', 'South Korea' => 'kr', 'Czechia' => 'cz',
    'Canada' => 'ca', 'Bosnia and Herzegovina' => 'ba', 'USA' => 'us', 'Paraguay' => 'py',
    'Qatar' => 'qa', 'Switzerland' => 'ch', 'Haiti' => 'ht', 'Scotland' => 'gb-sct',
    'Australia' => 'au', 'Turkey' => 'tr', 'Brazil' => 'br', 'Morocco' => 'ma',
    'Germany' => 'de', 'Curacao' => 'cw', 'Netherlands' => 'nl', 'Japan' => 'jp',
    'Ivory Coast' => 'ci', 'Ecuador' => 'ec', 'Sweden' => 'se', 'Tunisia' => 'tn',
    'Spain' => 'es', 'Cape Verde' => 'cv', 'Belgium' => 'be', 'Egypt' => 'eg',
    'Saudi Arabia' => 'sa', 'Uruguay' => 'uy', 'Iran' => 'ir', 'New Zealand' => 'nz',
    'France' => 'fr', 'Senegal' => 'sn', 'Norway' => 'no', 'Argentina' => 'ar',
    'Algeria' => 'dz', 'Austria' => 'at', 'Jordan' => 'jo', 'Portugal' => 'pt',
    'DR Congo' => 'cd', 'England' => 'gb', 'Croatia' => 'hr', 'Ghana' => 'gh',
    'Panama' => 'pa', 'Uzbekistan' => 'uz', 'Colombia' => 'co',
    'Iraq' => 'iq', 'DR Congo' => 'cd', 'Austria' => 'at',
    'New Zealand' => 'nz', 'Egypt' => 'eg', 'Jordan' => 'jo',
    'Algeria' => 'dz', 'Haiti' => 'ht', 'Cape Verde' => 'cv',
    'Curacao' => 'cw', 'Paraguay' => 'py', 'Ghana' => 'gh',
    'Croatia' => 'hr', 'Scotland' => 'gb-sct', 'Norway' => 'no',
];

$flagUrl = function (string $team) use ($flagMap): string {
    $code = $flagMap[$team] ?? '';
    if ($code === '') return '';
    return ASSET_URL . '/website/images/flags/' . $code . '.svg';
};

$flagInitials = function (string $team): string {
    $team = trim($team);
    if ($team === '') return '??';
    $words = preg_split('/\s+/', $team);
    if (count($words) >= 2) {
        return strtoupper(mb_substr($words[0], 0, 1, 'UTF-8') . mb_substr($words[1], 0, 1, 'UTF-8'));
    }
    return strtoupper(mb_substr($team, 0, 2, 'UTF-8'));
};


$maskClientName = function ($name): string {
    $name = trim((string)$name);
    if ($name === '') return 'G****t';
    $name = preg_replace('/\s+/', '', $name);
    if (function_exists('mb_strlen')) {
        $len = mb_strlen($name, 'UTF-8');
        $first = mb_substr($name, 0, 1, 'UTF-8');
        $last = $len > 1 ? mb_substr($name, -1, 1, 'UTF-8') : '';
    } else {
        $len = strlen($name);
        $first = substr($name, 0, 1);
        $last = $len > 1 ? substr($name, -1) : '';
    }
    return strtoupper($first) . '****' . $last;
};

$clientAvatarUrl = function ($icon, $name = ''): string {
    $icon = trim((string)$icon);
    if ($icon !== '') return $icon;
    return ASSET_URL . '/core/main/img/logos/PNG/icon-bg-64x64.png';
};

$fmtDate = function ($date): string {
    $ts = strtotime((string)$date);
    return $ts ? date('d.m.Y H:i', $ts) : '';
};

$kickoffIso = function ($date): string {
    try {
        $dt = new DateTime((string)$date, new DateTimeZone('Europe/Berlin'));
        return $dt->format(DateTime::ATOM);
    } catch (Throwable $e) {
        return '';
    }
};


$totalTips = count($predictions);
$totalMatches = count($matches);
$myPoints = 0;
if ($isParticipant) {
    $allPredictions = $isClient
        ? lb_wc_db_run($db, 'SELECT points FROM worldcup_predictions WHERE client_id = ?', $clientId)
        : lb_wc_db_run($db, 'SELECT points FROM worldcup_predictions WHERE participant_type = ? AND participant_id = ?', $participantType, $participantId);
    foreach ($allPredictions as $p) {
        $myPoints += (int)($p['points'] ?? 0);
    }
}

$nowBerlin = new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'));
$nextKickoffIso = '';
foreach ($matches as $m) {
    try {
        $matchKickoff = new DateTimeImmutable((string)$m['kickoff_at'], new DateTimeZone('Europe/Berlin'));
        if ((int)($m['is_finished'] ?? 0) !== 1 && $matchKickoff > $nowBerlin) {
            $nextKickoffIso = $matchKickoff->format(DateTime::ATOM);
            break;
        }
    } catch (Throwable $e) {}
}
?>

<?php $this->start('styles') ?>
<style>
.worldcup-page main,.worldcup-page .page-content{padding-top:0!important;overflow:hidden}.wc2{position:relative;min-height:100vh;color:#fff;padding-top:128px;background:radial-gradient(circle at 12% 8%,rgba(91,92,255,.28),transparent 30%),radial-gradient(circle at 86% 15%,rgba(0,214,255,.14),transparent 28%)}.wc2-wrap{width:min(1440px,calc(100vw - 48px));margin:0 auto;padding:48px 0 90px}.wc2-hero{display:grid;grid-template-columns:minmax(0,1fr) 430px;gap:34px;align-items:end;margin-bottom:34px}.wc2-badge{display:inline-flex;gap:10px;align-items:center;height:38px;padding:0 15px;border-radius:999px;background:rgba(96,92,255,.16);border:1px solid rgba(122,133,255,.28);font-size:13px;font-weight:900;letter-spacing:.07em;text-transform:uppercase;color:#cfd6ff}.wc2-title{font-size:clamp(44px,5vw,86px);line-height:.92;margin:18px 0 18px;font-weight:950;letter-spacing:-.06em}.wc2-title span{background:linear-gradient(90deg,#fff 0%,#b9c5ff 50%,#66e8ff 100%);background-clip:text;-webkit-background-clip:text;color:transparent}.wc2-lead{max-width:780px;margin:0;color:rgba(235,238,255,.74);font-size:19px;line-height:1.75}.wc2-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:30px}.wc2-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:48px;padding:0 20px;border-radius:15px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.065);color:#fff;text-decoration:none;font-weight:950;cursor:pointer;transition:.16s ease}.wc2-btn:hover{transform:translateY(-1px);background:rgba(255,255,255,.10);border-color:rgba(125,220,255,.34)}.wc2-btn-main{background:linear-gradient(135deg,#625cff,#31d4ff);box-shadow:0 16px 42px rgba(52,115,255,.30)}.wc2-hero-card{border-radius:32px;padding:26px;background:linear-gradient(135deg,rgba(22,27,53,.78),rgba(7,20,39,.88));border:1px solid rgba(125,220,255,.16);box-shadow:0 24px 70px rgba(0,0,0,.30)}.wc2-progress{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px}.wc2-ring{width:96px;height:96px;border-radius:999px;display:grid;place-items:center;background:conic-gradient(#38d5ff var(--p),rgba(255,255,255,.10) 0);position:relative}.wc2-ring:before{content:"";position:absolute;inset:9px;border-radius:999px;background:#111528}.wc2-ring strong{position:relative;font-size:25px}.wc2-progress h3{margin:0 0 7px;font-size:22px}.wc2-progress p{margin:0;color:rgba(235,238,255,.65);line-height:1.55}.wc2-mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.wc2-mini{border-radius:18px;padding:15px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08)}.wc2-mini strong{display:block;font-size:25px;line-height:1}.wc2-mini span{display:block;margin-top:7px;color:rgba(235,238,255,.62);font-size:12px;font-weight:800}.wc2-story{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:28px}.wc2-story article{min-height:154px;border-radius:28px;padding:24px;background:linear-gradient(145deg,rgba(255,255,255,.07),rgba(255,255,255,.035));border:1px solid rgba(255,255,255,.09)}.wc2-story .icon{width:44px;height:44px;border-radius:16px;display:grid;place-items:center;background:rgba(99,102,241,.18);color:#9fb3ff;margin-bottom:16px}.wc2-story h3{margin:0 0 9px;font-size:18px}.wc2-story p{margin:0;color:rgba(235,238,255,.68);line-height:1.62;font-size:14px}.wc2-login-strip{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:26px;padding:18px 20px;border-radius:22px;background:linear-gradient(90deg,rgba(49,212,255,.13),rgba(98,92,255,.14));border:1px solid rgba(125,220,255,.22)}.wc2-login-strip strong{display:block;margin-bottom:5px}.wc2-login-strip span{color:rgba(235,238,255,.68)}.wc2-main{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:26px;align-items:start}.wc2-section-head{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:18px}.wc2-section-head h2{font-size:30px;margin:0;letter-spacing:-.03em}.wc2-section-head p{margin:6px 0 0;color:rgba(235,238,255,.62)}.wc2-days{display:grid;gap:22px}.wc2-day-label{display:flex;align-items:center;gap:12px;margin:4px 0 12px;color:#c9d2ff;font-weight:950}.wc2-day-label:after{content:"";height:1px;flex:1;background:linear-gradient(90deg,rgba(255,255,255,.16),transparent)}.wc2-match{position:relative;display:grid;grid-template-columns:104px minmax(0,1fr) 160px;align-items:center;gap:18px;min-height:116px;padding:18px 20px;border-radius:26px;background:rgba(13,16,31,.68);border:1px solid rgba(255,255,255,.08);overflow:hidden;transition:.16s ease}.wc2-match:before{content:"";position:absolute;inset:0 auto 0 0;width:5px;background:linear-gradient(180deg,#625cff,#31d4ff);opacity:.8}.wc2-match:hover{transform:translateY(-1px);border-color:rgba(125,220,255,.23);background:rgba(18,22,43,.80)}.wc2-time{text-align:center}.wc2-time strong{display:block;font-size:22px}.wc2-time span{display:block;margin-top:7px;color:rgba(235,238,255,.54);font-size:12px;font-weight:800}.wc2-versus{display:grid;grid-template-columns:minmax(0,1fr) 44px minmax(0,1fr);gap:18px;align-items:center}.wc2-team{display:flex;align-items:center;gap:13px;min-width:0;font-weight:950}.wc2-team.away{justify-content:flex-end;text-align:right}.wc2-team-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.wc2-flag{width:38px;height:38px;border-radius:999px;object-fit:cover;background:#111827;border:1px solid rgba(255,255,255,.14);box-shadow:0 8px 18px rgba(0,0,0,.28)}.wc2-vs{width:44px;height:44px;border-radius:16px;display:grid;place-items:center;background:rgba(255,255,255,.055);color:rgba(235,238,255,.54);font-size:12px;font-weight:950}.wc2-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}.wc2-chip{display:inline-flex;align-items:center;gap:7px;min-height:28px;padding:0 10px;border-radius:999px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.07);color:rgba(235,238,255,.64);font-size:12px;font-weight:850}.wc2-chip.locked{color:#ffc1c1;background:rgba(239,68,68,.10);border-color:rgba(239,68,68,.22)}.wc2-pick{text-align:right}.wc2-score{display:inline-flex;align-items:center;justify-content:center;min-width:74px;height:42px;border-radius:15px;background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.10);font-size:18px;font-weight:950;margin-bottom:9px}.wc2-pick .wc2-btn{min-height:40px;padding:0 14px;border-radius:13px;font-size:13px}.wc2-sidebar{position:sticky;top:112px;display:grid;gap:16px}.wc2-side-card{border-radius:28px;padding:22px;background:linear-gradient(145deg,rgba(20,24,47,.82),rgba(8,12,25,.90));border:1px solid rgba(255,255,255,.09)}.wc2-side-card h3{margin:0 0 14px;font-size:20px}.wc2-board{display:grid;gap:4px}.wc2-board-row{display:grid;grid-template-columns:38px 1fr auto;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.065)}.wc2-board-row:last-child{border-bottom:0}.wc2-rank{width:30px;height:30px;border-radius:11px;background:rgba(255,255,255,.065);display:grid;place-items:center;color:#cfd6ff;font-weight:950}.wc2-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:850;color:rgba(255,255,255,.88)}.wc2-points{font-weight:950;color:#6ee7ff}.wc2-side-card p{margin:0;color:rgba(235,238,255,.68);line-height:1.65}.wc2-code{margin-top:14px;border-radius:18px;padding:15px;text-align:center;background:rgba(0,0,0,.24);border:1px dashed rgba(110,231,255,.45);font-weight:950;letter-spacing:.08em}.wc2-modal-backdrop{position:fixed;inset:0;z-index:99990;background:rgba(4,6,18,.76);backdrop-filter:blur(12px);display:none;align-items:center;justify-content:center;padding:18px}.wc2-modal-backdrop.is-open{display:flex}.wc2-modal{width:min(640px,100%);border-radius:34px;background:linear-gradient(145deg,#171b35,#080b16);border:1px solid rgba(125,220,255,.20);box-shadow:0 34px 100px rgba(0,0,0,.68);overflow:hidden}.wc2-modal-head{display:flex;align-items:center;justify-content:space-between;padding:22px 24px;border-bottom:1px solid rgba(255,255,255,.08)}.wc2-modal-head h3{margin:0;font-size:24px}.wc2-close{width:42px;height:42px;border-radius:15px;border:1px solid rgba(255,255,255,.11);background:rgba(255,255,255,.06);color:#fff;cursor:pointer}.wc2-modal-body{padding:24px}.wc2-slip{display:grid;grid-template-columns:1fr 86px 1fr;gap:18px;align-items:center}.wc2-slip-team{text-align:center}.wc2-slip-team img{width:72px;height:72px;border-radius:999px;object-fit:cover;border:1px solid rgba(255,255,255,.14);box-shadow:0 12px 24px rgba(0,0,0,.30);margin-bottom:12px}.wc2-slip-team strong{display:block;font-size:18px}.wc2-scorebox{display:grid;gap:10px;justify-items:center}.wc2-scoreline{display:flex;align-items:center;gap:10px}.wc2-scoreline input{width:72px;height:62px;border-radius:18px;background:rgba(0,0,0,.30);border:1px solid rgba(255,255,255,.12);color:#fff;text-align:center;font-size:27px;font-weight:950}.wc2-scoreline span{font-size:26px;font-weight:950;color:rgba(235,238,255,.45)}.wc2-step{display:flex;gap:8px}.wc2-step button{width:34px;height:34px;border-radius:12px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.07);color:#fff;cursor:pointer}.wc2-quick{margin-top:22px}.wc2-quick-label{font-size:12px;font-weight:950;text-transform:uppercase;color:rgba(235,238,255,.54);letter-spacing:.07em;margin-bottom:10px}.wc2-quick-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:8px}.wc2-quick button{min-height:40px;border-radius:13px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.06);color:#fff;font-weight:950;cursor:pointer}.wc2-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:24px}.wc2-toast{position:fixed;right:18px;bottom:22px;z-index:99999;max-width:360px;padding:14px 16px;border-radius:16px;background:#111827;border:1px solid rgba(125,220,255,.34);box-shadow:0 18px 46px rgba(0,0,0,.44);color:#fff;font-weight:850;opacity:0;transform:translateY(10px);pointer-events:none;transition:.2s}.wc2-toast.show{opacity:1;transform:translateY(0)}@media(max-width:1180px){.wc2-hero,.wc2-main{grid-template-columns:1fr}.wc2-sidebar{position:static;grid-template-columns:1fr 1fr}.wc2-wrap{width:min(100% - 34px,980px)}}@media(max-width:820px){.wc2{padding-top:108px}.wc2-wrap{width:calc(100vw - 24px);padding-top:26px}.wc2-title{font-size:44px}.wc2-lead{font-size:16px}.wc2-hero-card,.wc2-story article,.wc2-side-card{border-radius:24px}.wc2-story,.wc2-sidebar{grid-template-columns:1fr}.wc2-login-strip{align-items:flex-start;flex-direction:column}.wc2-section-head{display:block}.wc2-match{grid-template-columns:1fr;gap:14px;min-height:0;padding:18px}.wc2-time{text-align:left;display:flex;align-items:center;gap:10px}.wc2-time strong{font-size:18px}.wc2-versus{grid-template-columns:1fr;gap:10px}.wc2-team,.wc2-team.away{justify-content:flex-start;text-align:left}.wc2-vs{width:auto;height:32px}.wc2-pick{text-align:left;display:flex;align-items:center;gap:10px;justify-content:space-between}.wc2-mini-stats{grid-template-columns:1fr}.wc2-slip{grid-template-columns:1fr;gap:16px}.wc2-quick-grid{grid-template-columns:repeat(3,1fr)}.wc2-modal-actions{flex-direction:column}.wc2-modal-actions .wc2-btn{width:100%}}@media(max-width:480px){.wc2{padding-top:94px}.wc2-title{font-size:38px}.wc2-actions .wc2-btn{width:100%}.wc2-progress{align-items:flex-start;flex-direction:column}.wc2-hero-card{padding:20px}.wc2-story article{min-height:0}.wc2-scoreline input{width:62px;height:56px}.wc2-quick-grid{grid-template-columns:repeat(2,1fr)}}


/* Ricardo fix: keep the page clearly below the fixed LoLBoost header and center the prediction modal */
.worldcup-page .wc2{
    padding-top:240px!important;
}
.worldcup-page .wc2-wrap{
    padding-top:28px!important;
}
.wc2-modal-backdrop{
    position:fixed!important;
    top:0!important;
    right:0!important;
    bottom:0!important;
    left:0!important;
    width:100vw!important;
    height:100vh!important;
    height:100dvh!important;
    z-index:2147483000!important;
    display:none!important;
    align-items:center!important;
    justify-content:center!important;
    padding:24px!important;
    box-sizing:border-box!important;
}
.wc2-modal-backdrop.is-open{
    display:flex!important;
}
.wc2-modal{
    position:relative!important;
    margin:auto!important;
    width:min(680px,calc(100vw - 32px))!important;
    max-height:calc(100dvh - 48px)!important;
    overflow:auto!important;
    transform:none!important;
}
.wc2-slip{
    grid-template-columns:minmax(0,1fr) 190px minmax(0,1fr)!important;
}
.wc2-scoreline{
    width:190px!important;
    display:grid!important;
    grid-template-columns:78px 18px 78px!important;
    gap:8px!important;
    align-items:center!important;
    justify-content:center!important;
}
.wc2-scoreline input{
    width:78px!important;
    box-sizing:border-box!important;
    text-align:center!important;
    padding:0!important;
}
.wc2-step{
    width:190px!important;
    display:grid!important;
    grid-template-columns:repeat(4,1fr)!important;
    gap:8px!important;
}
body:has(.wc2-modal-backdrop.is-open){
    overflow:hidden!important;
}
@media(max-width:1180px){
    .worldcup-page .wc2{padding-top:220px!important;}
}
@media(max-width:820px){
    .worldcup-page .wc2{padding-top:185px!important;}
    .worldcup-page .wc2-wrap{padding-top:20px!important;}
    .wc2-modal-backdrop{align-items:flex-start!important;padding:18px 12px!important;overflow-y:auto!important;}
    .wc2-modal{width:100%!important;max-height:none!important;margin:24px auto!important;border-radius:26px!important;}
    .wc2-slip{grid-template-columns:1fr!important;gap:18px!important;}
    .wc2-scorebox{order:2;width:100%!important;}
    .wc2-scoreline{width:min(260px,100%)!important;grid-template-columns:1fr 18px 1fr!important;}
    .wc2-scoreline input{width:100%!important;}
    .wc2-step{width:min(260px,100%)!important;}
    .wc2-slip-team{display:grid!important;justify-items:center!important;}
}
@media(max-width:480px){
    .worldcup-page .wc2{padding-top:170px!important;}
    .wc2-title{font-size:40px!important;line-height:.96!important;}
    .wc2-lead{font-size:15px!important;line-height:1.6!important;}
}



/* Final layout pass for real LoLBoost header, browser zoom around 88 percent and mobile */
.worldcup-page main,
.worldcup-page .page-content{
    overflow:visible!important;
}
.worldcup-page .wc2{
    padding-top:300px!important;
    isolation:isolate!important;
}
.worldcup-page .wc2-wrap{
    width:min(1320px,calc(100vw - 64px))!important;
    padding-top:0!important;
}
.wc2-hero{
    align-items:center!important;
}
.wc2-board-row{
    grid-template-columns:34px minmax(0,1fr) auto!important;
    gap:12px!important;
}
.wc2-player{
    display:flex!important;
    align-items:center!important;
    gap:10px!important;
    min-width:0!important;
}
.wc2-avatar{
    width:34px!important;
    height:34px!important;
    flex:0 0 34px!important;
    border-radius:999px!important;
    object-fit:cover!important;
    background:#14182d!important;
    border:1px solid rgba(255,255,255,.14)!important;
    box-shadow:0 8px 18px rgba(0,0,0,.25)!important;
}
.wc2-modal-backdrop{
    position:fixed!important;
    inset:0!important;
    width:100vw!important;
    height:100vh!important;
    height:100dvh!important;
    z-index:2147483000!important;
    display:none!important;
    place-items:center!important;
    padding:22px!important;
    overflow:auto!important;
}
.wc2-modal-backdrop.is-open{
    display:grid!important;
}
.wc2-modal{
    width:min(620px,calc(100vw - 36px))!important;
    max-height:min(760px,calc(100dvh - 44px))!important;
    overflow:auto!important;
    margin:auto!important;
    border-radius:30px!important;
}
.wc2-modal-body{
    padding:24px!important;
}
.wc2-slip{
    grid-template-columns:minmax(0,1fr) 190px minmax(0,1fr)!important;
    gap:18px!important;
}
.wc2-scorebox{
    width:190px!important;
    justify-self:center!important;
}
.wc2-scoreline{
    width:190px!important;
    display:grid!important;
    grid-template-columns:78px 18px 78px!important;
    gap:8px!important;
    align-items:center!important;
}
.wc2-scoreline input{
    width:78px!important;
    padding:0!important;
    box-sizing:border-box!important;
}
.wc2-step{
    width:190px!important;
    display:grid!important;
    grid-template-columns:repeat(4,1fr)!important;
    gap:8px!important;
}
@media(max-width:1180px){
    .worldcup-page .wc2{padding-top:270px!important;}
    .worldcup-page .wc2-wrap{width:min(980px,calc(100vw - 40px))!important;}
}
@media(max-width:820px){
    .worldcup-page .wc2{padding-top:230px!important;}
    .worldcup-page .wc2-wrap{width:calc(100vw - 24px)!important;}
    .wc2-hero{gap:22px!important;margin-bottom:24px!important;}
    .wc2-title{font-size:42px!important;line-height:.98!important;letter-spacing:-.045em!important;}
    .wc2-lead{font-size:15px!important;line-height:1.62!important;}
    .wc2-story{gap:12px!important;}
    .wc2-story article{padding:18px!important;border-radius:22px!important;}
    .wc2-match{border-radius:22px!important;padding:16px!important;}
    .wc2-sidebar{gap:12px!important;}
    .wc2-modal-backdrop{align-items:start!important;place-items:start center!important;padding:14px 10px!important;}
    .wc2-modal{width:100%!important;max-height:none!important;margin:20px auto!important;border-radius:24px!important;}
    .wc2-modal-head{padding:18px!important;}
    .wc2-modal-body{padding:18px!important;}
    .wc2-slip{grid-template-columns:1fr!important;gap:16px!important;}
    .wc2-scorebox{width:min(270px,100%)!important;order:2!important;}
    .wc2-scoreline{width:100%!important;grid-template-columns:1fr 18px 1fr!important;}
    .wc2-scoreline input{width:100%!important;height:58px!important;}
    .wc2-step{width:100%!important;}
    .wc2-slip-team{display:grid!important;justify-items:center!important;}
    .wc2-quick-grid{grid-template-columns:repeat(3,1fr)!important;}
}
@media(max-width:520px){
    .worldcup-page .wc2{padding-top:205px!important;}
    .wc2-title{font-size:36px!important;}
    .wc2-badge{height:auto!important;min-height:34px!important;padding:8px 12px!important;font-size:11px!important;}
    .wc2-actions .wc2-btn{width:100%!important;}
    .wc2-hero-card{padding:18px!important;border-radius:22px!important;}
    .wc2-mini-stats{grid-template-columns:1fr!important;}
    .wc2-pick{flex-direction:column!important;align-items:stretch!important;}
    .wc2-pick .wc2-btn,.wc2-score{width:100%!important;}
    .wc2-quick-grid{grid-template-columns:repeat(2,1fr)!important;}
    .wc2-board-row{grid-template-columns:30px minmax(0,1fr) auto!important;gap:9px!important;}
    .wc2-avatar{width:30px!important;height:30px!important;flex-basis:30px!important;}
}


/* FINAL MODAL VIEWPORT FIX, covers pages rendered with 0.88 zoom */
:root{--wc2-page-zoom:.88;}
body.wc2-modal-open{overflow:hidden!important;}
#wcPredictionModal.wc2-modal-backdrop{
    position:fixed!important;
    top:0!important;
    left:0!important;
    right:auto!important;
    bottom:auto!important;
    width:calc(100vw / var(--wc2-page-zoom))!important;
    height:calc(100vh / var(--wc2-page-zoom))!important;
    height:calc(100dvh / var(--wc2-page-zoom))!important;
    min-width:calc(100vw / var(--wc2-page-zoom))!important;
    min-height:calc(100vh / var(--wc2-page-zoom))!important;
    z-index:2147483646!important;
    display:none!important;
    align-items:center!important;
    justify-content:center!important;
    padding:24px!important;
    box-sizing:border-box!important;
    overflow:auto!important;
    background:rgba(4,6,18,.82)!important;
    backdrop-filter:blur(14px)!important;
    -webkit-backdrop-filter:blur(14px)!important;
}
#wcPredictionModal.wc2-modal-backdrop.is-open{display:flex!important;}
#wcPredictionModal .wc2-modal{
    width:min(620px,calc((100vw / var(--wc2-page-zoom)) - 42px))!important;
    max-height:calc((100dvh / var(--wc2-page-zoom)) - 48px)!important;
    margin:auto!important;
    overflow:auto!important;
    transform:none!important;
}
@media(max-width:820px){
    #wcPredictionModal.wc2-modal-backdrop{
        align-items:flex-start!important;
        justify-content:center!important;
        padding:16px 12px!important;
    }
    #wcPredictionModal .wc2-modal{
        width:calc((100vw / var(--wc2-page-zoom)) - 24px)!important;
        max-height:none!important;
        margin:18px auto!important;
    }
}
@media(max-width:520px){
    #wcPredictionModal.wc2-modal-backdrop{padding:12px 10px!important;}
    #wcPredictionModal .wc2-modal{width:calc((100vw / var(--wc2-page-zoom)) - 20px)!important;margin:10px auto!important;}
}



/* Score stepper redesign and match slider */
.wc2-scoreline{gap:14px!important;align-items:center!important;}
.wc2-score-control{position:relative;width:86px;height:66px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.035));border:1px solid rgba(125,220,255,.24);box-shadow:inset 0 0 0 1px rgba(255,255,255,.04),0 14px 30px rgba(0,0,0,.26);overflow:hidden;}
.wc2-score-control input{width:100%!important;height:100%!important;padding:0 28px 0 10px!important;border:0!important;background:transparent!important;box-shadow:none!important;font-size:30px!important;line-height:66px!important;-moz-appearance:textfield!important;appearance:textfield!important;}
.wc2-score-control input::-webkit-outer-spin-button,.wc2-score-control input::-webkit-inner-spin-button{-webkit-appearance:none!important;margin:0!important;}
.wc2-score-spin{position:absolute;right:7px;width:22px;height:24px;border:0!important;border-radius:9px!important;background:rgba(255,255,255,.08)!important;color:#e9eeff!important;display:grid!important;place-items:center!important;cursor:pointer!important;transition:.15s ease!important;font-size:11px!important;line-height:1!important;}
.wc2-score-spin:hover{background:linear-gradient(135deg,#625cff,#31d4ff)!important;color:#fff!important;transform:translateY(-1px);}
.wc2-score-spin.up{top:7px}.wc2-score-spin.down{bottom:7px}.wc2-scoreline span{font-size:28px!important;color:rgba(185,198,255,.55)!important;}
.wc2-step{display:none!important;}
.wc2-modal-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:-4px 0 18px;color:rgba(235,238,255,.62);font-weight:850;font-size:13px;}
.wc2-modal-nav{display:flex;gap:8px;align-items:center;}
.wc2-nav-btn{width:38px;height:38px;border-radius:13px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.06);color:#fff;cursor:pointer;display:grid;place-items:center;transition:.15s ease;}
.wc2-nav-btn:hover:not(:disabled){background:rgba(99,102,241,.20);border-color:rgba(125,220,255,.25)}.wc2-nav-btn:disabled{opacity:.38;cursor:not-allowed;}
.wc2-save-next{background:linear-gradient(135deg,#625cff,#31d4ff)!important;}
.wc2-modal-actions{align-items:center!important;}
@media(max-width:620px){.wc2-score-control{width:76px;height:60px}.wc2-score-control input{font-size:27px!important;line-height:60px!important}.wc2-score-spin{right:6px;width:20px;height:22px}.wc2-modal-meta{align-items:flex-start;flex-direction:column}.wc2-modal-actions{display:grid!important;grid-template-columns:1fr 1fr!important}.wc2-modal-actions .js-save-next{grid-column:1 / -1}.wc2-modal-actions .js-close-wc-modal{order:4;grid-column:1 / -1}.wc2-slip-team img{width:62px!important;height:62px!important}.wc2-slip-team strong{font-size:15px!important}}



/* MOBILE FINAL, fullscreen prediction slip, compact layout, no inner up/down scrolling */
@media(max-width:720px){
    html:has(body.wc2-modal-open),
    body.wc2-modal-open{
        overflow:hidden!important;
        height:100%!important;
        touch-action:none!important;
    }
    #wcPredictionModal.wc2-modal-backdrop{
        position:fixed!important;
        inset:0!important;
        width:calc(100vw / var(--wc2-page-zoom))!important;
        height:calc(100dvh / var(--wc2-page-zoom))!important;
        min-width:calc(100vw / var(--wc2-page-zoom))!important;
        min-height:calc(100dvh / var(--wc2-page-zoom))!important;
        padding:0!important;
        margin:0!important;
        overflow:hidden!important;
        align-items:stretch!important;
        justify-content:stretch!important;
        place-items:stretch!important;
        background:rgba(4,6,18,.92)!important;
    }
    #wcPredictionModal .wc2-modal{
        width:100%!important;
        height:100%!important;
        max-width:none!important;
        max-height:none!important;
        margin:0!important;
        border-radius:0!important;
        display:flex!important;
        flex-direction:column!important;
        overflow:hidden!important;
        background:linear-gradient(180deg,#171b35 0%,#080b16 100%)!important;
        border:0!important;
    }
    #wcPredictionModal .wc2-modal-head{
        flex:0 0 auto!important;
        min-height:60px!important;
        padding:12px 16px!important;
    }
    #wcPredictionModal .wc2-modal-head h3{
        font-size:20px!important;
    }
    #wcPredictionModal .wc2-close{
        width:40px!important;
        height:40px!important;
    }
    #wcPredictionModal .wc2-modal-body{
        flex:1 1 auto!important;
        min-height:0!important;
        overflow:hidden!important;
        padding:12px 16px calc(14px + env(safe-area-inset-bottom))!important;
        display:flex!important;
        flex-direction:column!important;
        gap:10px!important;
    }
    #wcPredictionModal .wc2-modal-meta{
        flex:0 0 auto!important;
        margin:0!important;
        flex-direction:row!important;
        align-items:center!important;
        justify-content:space-between!important;
        font-size:12px!important;
    }
    #wcPredictionModal .wc2-modal-nav{
        margin-left:auto!important;
    }
    #wcPredictionModal .wc2-nav-btn{
        width:36px!important;
        height:36px!important;
        border-radius:12px!important;
    }
    #wcPredictionModal .wc2-slip{
        flex:0 0 auto!important;
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:10px!important;
        align-items:center!important;
    }
    #wcPredictionModal .wc2-slip-team{
        display:flex!important;
        align-items:center!important;
        justify-content:center!important;
        gap:10px!important;
        text-align:left!important;
    }
    #wcPredictionModal .wc2-slip-team:first-child{order:1!important;}
    #wcPredictionModal .wc2-scorebox{order:2!important;}
    #wcPredictionModal .wc2-slip-team:last-child{order:3!important;}
    #wcPredictionModal .wc2-slip-team img{
        width:46px!important;
        height:46px!important;
        margin:0!important;
        flex:0 0 46px!important;
    }
    #wcPredictionModal .wc2-slip-team strong{
        font-size:16px!important;
        line-height:1.2!important;
        max-width:210px!important;
        overflow:hidden!important;
        text-overflow:ellipsis!important;
        white-space:nowrap!important;
    }
    #wcPredictionModal .wc2-scorebox{
        width:100%!important;
        justify-self:stretch!important;
    }
    #wcPredictionModal .wc2-scoreline{
        width:min(286px,100%)!important;
        margin:0 auto!important;
        grid-template-columns:1fr 16px 1fr!important;
        gap:10px!important;
    }
    #wcPredictionModal .wc2-score-control{
        width:100%!important;
        height:58px!important;
        border-radius:18px!important;
    }
    #wcPredictionModal .wc2-score-control input{
        height:58px!important;
        line-height:58px!important;
        font-size:28px!important;
    }
    #wcPredictionModal .wc2-score-spin{
        width:22px!important;
        height:22px!important;
        right:7px!important;
    }
    #wcPredictionModal .wc2-score-spin.up{top:6px!important;}
    #wcPredictionModal .wc2-score-spin.down{bottom:6px!important;}
    #wcPredictionModal .wc2-quick{
        flex:1 1 auto!important;
        min-height:0!important;
        margin:4px 0 0!important;
        display:flex!important;
        flex-direction:column!important;
    }
    #wcPredictionModal .wc2-quick-label{
        flex:0 0 auto!important;
        margin-bottom:8px!important;
        font-size:11px!important;
    }
    #wcPredictionModal .wc2-quick-grid{
        flex:1 1 auto!important;
        display:grid!important;
        grid-template-columns:repeat(5,1fr)!important;
        gap:7px!important;
        align-content:start!important;
    }
    #wcPredictionModal .wc2-quick button{
        min-height:36px!important;
        height:36px!important;
        border-radius:12px!important;
        font-size:13px!important;
    }
    #wcPredictionModal .wc2-modal-actions{
        flex:0 0 auto!important;
        margin-top:4px!important;
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:8px!important;
    }
    #wcPredictionModal .wc2-modal-actions .wc2-btn{
        width:100%!important;
        min-height:44px!important;
        height:44px!important;
        border-radius:14px!important;
        padding:0 10px!important;
        font-size:13px!important;
    }
    #wcPredictionModal .wc2-modal-actions .js-save-next{
        grid-column:1 / -1!important;
    }
    #wcPredictionModal .wc2-modal-actions .js-close-wc-modal{
        grid-column:auto!important;
        order:0!important;
    }
}
@media(max-width:420px){
    #wcPredictionModal .wc2-modal-body{padding-left:12px!important;padding-right:12px!important;gap:8px!important;}
    #wcPredictionModal .wc2-quick-grid{grid-template-columns:repeat(2,1fr)!important;gap:7px!important;}
    #wcPredictionModal .wc2-quick button{height:34px!important;min-height:34px!important;}
    #wcPredictionModal .wc2-slip-team img{width:42px!important;height:42px!important;flex-basis:42px!important;}
    #wcPredictionModal .wc2-slip-team strong{font-size:15px!important;max-width:180px!important;}
}
@media(max-height:680px) and (max-width:720px){
    #wcPredictionModal .wc2-modal-head{min-height:52px!important;padding:8px 14px!important;}
    #wcPredictionModal .wc2-modal-head h3{font-size:18px!important;}
    #wcPredictionModal .wc2-close{width:36px!important;height:36px!important;}
    #wcPredictionModal .wc2-modal-body{padding-top:8px!important;padding-bottom:8px!important;gap:7px!important;}
    #wcPredictionModal .wc2-slip{gap:7px!important;}
    #wcPredictionModal .wc2-slip-team img{width:38px!important;height:38px!important;flex-basis:38px!important;}
    #wcPredictionModal .wc2-slip-team strong{font-size:14px!important;}
    #wcPredictionModal .wc2-score-control{height:50px!important;}
    #wcPredictionModal .wc2-score-control input{height:50px!important;line-height:50px!important;font-size:24px!important;}
    #wcPredictionModal .wc2-score-spin{height:19px!important;width:20px!important;}
    #wcPredictionModal .wc2-quick button{height:30px!important;min-height:30px!important;font-size:12px!important;}
    #wcPredictionModal .wc2-modal-actions .wc2-btn{height:39px!important;min-height:39px!important;}
}



/* MOBILE HARD FIX, true viewport fullscreen, no horizontal cut, no modal scroll */
@media (max-width:720px){
    body.wc2-modal-open{
        overflow:hidden!important;
        position:fixed!important;
        width:100%!important;
        height:100%!important;
    }
    #wcPredictionModal.wc2-modal-backdrop,
    #wcPredictionModal.wc2-modal-backdrop.is-open{
        position:fixed!important;
        top:0!important;
        left:0!important;
        right:0!important;
        bottom:0!important;
        inset:0!important;
        width:100vw!important;
        height:100vh!important;
        height:100dvh!important;
        min-width:0!important;
        min-height:0!important;
        max-width:100vw!important;
        max-height:100dvh!important;
        padding:0!important;
        margin:0!important;
        overflow:hidden!important;
        align-items:stretch!important;
        justify-content:stretch!important;
        box-sizing:border-box!important;
    }
    #wcPredictionModal .wc2-modal{
        width:100vw!important;
        height:100vh!important;
        height:100dvh!important;
        max-width:100vw!important;
        max-height:100dvh!important;
        min-width:0!important;
        min-height:0!important;
        margin:0!important;
        border-radius:0!important;
        box-sizing:border-box!important;
        overflow:hidden!important;
        display:flex!important;
        flex-direction:column!important;
    }
    #wcPredictionModal .wc2-modal-head{
        height:58px!important;
        min-height:58px!important;
        padding:10px 14px!important;
        box-sizing:border-box!important;
    }
    #wcPredictionModal .wc2-modal-head h3{
        font-size:19px!important;
    }
    #wcPredictionModal .wc2-close{
        width:38px!important;
        height:38px!important;
        flex:0 0 38px!important;
    }
    #wcPredictionModal .wc2-modal-body{
        flex:1 1 auto!important;
        min-height:0!important;
        width:100%!important;
        max-width:100%!important;
        overflow:hidden!important;
        padding:10px 14px calc(10px + env(safe-area-inset-bottom))!important;
        box-sizing:border-box!important;
        display:grid!important;
        grid-template-rows:auto auto minmax(0,1fr) auto!important;
        gap:8px!important;
    }
    #wcPredictionModal .wc2-modal-meta{
        margin:0!important;
        min-height:34px!important;
        flex-direction:row!important;
        align-items:center!important;
        justify-content:space-between!important;
    }
    #wcPredictionModal .wc2-nav-btn{
        width:34px!important;
        height:34px!important;
    }
    #wcPredictionModal .wc2-slip{
        width:100%!important;
        min-width:0!important;
        display:grid!important;
        grid-template-columns:minmax(0,1fr) auto minmax(0,1fr)!important;
        gap:8px!important;
        align-items:center!important;
    }
    #wcPredictionModal .wc2-slip-team{
        min-width:0!important;
        display:grid!important;
        justify-items:center!important;
        gap:6px!important;
        text-align:center!important;
    }
    #wcPredictionModal .wc2-slip-team img{
        width:48px!important;
        height:48px!important;
        margin:0!important;
    }
    #wcPredictionModal .wc2-slip-team strong{
        max-width:110px!important;
        font-size:13px!important;
        line-height:1.15!important;
        white-space:normal!important;
        overflow:hidden!important;
        display:-webkit-box!important;
        -webkit-line-clamp:2!important;
        -webkit-box-orient:vertical!important;
    }
    #wcPredictionModal .wc2-scorebox{
        min-width:110px!important;
        width:110px!important;
    }
    #wcPredictionModal .wc2-scoreline{
        width:110px!important;
        display:grid!important;
        grid-template-columns:46px 10px 46px!important;
        gap:4px!important;
        align-items:center!important;
        margin:0!important;
    }
    #wcPredictionModal .wc2-score-control{
        width:46px!important;
        height:52px!important;
        border-radius:16px!important;
    }
    #wcPredictionModal .wc2-score-control input{
        height:52px!important;
        line-height:52px!important;
        padding:0 18px 0 4px!important;
        font-size:23px!important;
    }
    #wcPredictionModal .wc2-score-spin{
        width:17px!important;
        height:18px!important;
        right:4px!important;
        border-radius:7px!important;
        font-size:9px!important;
    }
    #wcPredictionModal .wc2-score-spin.up{top:5px!important;}
    #wcPredictionModal .wc2-score-spin.down{bottom:5px!important;}
    #wcPredictionModal .wc2-scoreline span{
        font-size:20px!important;
    }
    #wcPredictionModal .wc2-quick{
        margin:0!important;
        min-height:0!important;
        overflow:hidden!important;
        display:flex!important;
        flex-direction:column!important;
    }
    #wcPredictionModal .wc2-quick-label{
        margin:0 0 6px!important;
        font-size:10px!important;
    }
    #wcPredictionModal .wc2-quick-grid{
        min-height:0!important;
        display:grid!important;
        grid-template-columns:repeat(2,minmax(0,1fr))!important;
        gap:6px!important;
        align-content:start!important;
    }
    #wcPredictionModal .wc2-quick button{
        min-width:0!important;
        height:34px!important;
        min-height:34px!important;
        padding:0 6px!important;
        border-radius:11px!important;
        font-size:12px!important;
    }
    #wcPredictionModal .wc2-modal-actions{
        margin:0!important;
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:8px!important;
    }
    #wcPredictionModal .wc2-modal-actions .wc2-btn{
        width:100%!important;
        min-width:0!important;
        height:42px!important;
        min-height:42px!important;
        padding:0 8px!important;
        border-radius:13px!important;
        font-size:12px!important;
        white-space:nowrap!important;
    }
    #wcPredictionModal .wc2-modal-actions .js-save-next{
        grid-column:1 / -1!important;
    }
}
@media (max-width:360px), (max-height:680px) and (max-width:720px){
    #wcPredictionModal .wc2-modal-head{height:50px!important;min-height:50px!important;padding:8px 12px!important;}
    #wcPredictionModal .wc2-modal-head h3{font-size:17px!important;}
    #wcPredictionModal .wc2-close{width:34px!important;height:34px!important;flex-basis:34px!important;}
    #wcPredictionModal .wc2-modal-body{padding:7px 10px calc(7px + env(safe-area-inset-bottom))!important;gap:6px!important;}
    #wcPredictionModal .wc2-modal-meta{min-height:30px!important;font-size:11px!important;}
    #wcPredictionModal .wc2-nav-btn{width:30px!important;height:30px!important;}
    #wcPredictionModal .wc2-slip-team img{width:38px!important;height:38px!important;}
    #wcPredictionModal .wc2-slip-team strong{font-size:12px!important;max-width:92px!important;}
    #wcPredictionModal .wc2-scorebox{width:104px!important;min-width:104px!important;}
    #wcPredictionModal .wc2-scoreline{width:104px!important;grid-template-columns:43px 10px 43px!important;}
    #wcPredictionModal .wc2-score-control{width:43px!important;height:46px!important;border-radius:14px!important;}
    #wcPredictionModal .wc2-score-control input{height:46px!important;line-height:46px!important;font-size:21px!important;}
    #wcPredictionModal .wc2-score-spin{height:16px!important;width:16px!important;right:3px!important;}
    #wcPredictionModal .wc2-score-spin.up{top:4px!important;}
    #wcPredictionModal .wc2-score-spin.down{bottom:4px!important;}
    #wcPredictionModal .wc2-quick button{height:29px!important;min-height:29px!important;font-size:11px!important;border-radius:10px!important;}
    #wcPredictionModal .wc2-modal-actions .wc2-btn{height:36px!important;min-height:36px!important;font-size:11px!important;}
}



/* FINAL MOBILE POLISH, compact slip, distinct actions */
#wcPredictionModal .wc2-modal-actions .js-close-wc-modal{background:rgba(255,255,255,.045)!important;border-color:rgba(255,255,255,.12)!important;color:rgba(255,255,255,.82)!important;box-shadow:none!important;}
#wcPredictionModal .wc2-modal-actions .js-next-match{background:rgba(255,255,255,.075)!important;border-color:rgba(125,220,255,.16)!important;color:#fff!important;box-shadow:none!important;}
#wcPredictionModal .wc2-modal-actions .js-save-prediction{background:rgba(56,189,248,.12)!important;border-color:rgba(56,189,248,.42)!important;color:#8eeaff!important;box-shadow:none!important;}
#wcPredictionModal .wc2-modal-actions .js-save-next{background:linear-gradient(135deg,#6d5cff 0%,#25d4ff 100%)!important;border-color:rgba(255,255,255,.14)!important;color:#fff!important;box-shadow:0 12px 28px rgba(49,212,255,.18)!important;}
#wcPredictionModal .wc2-modal-actions .wc2-btn:hover{transform:translateY(-1px)!important;filter:brightness(1.06)!important;}
@media (max-width:720px){
    #wcPredictionModal .wc2-modal-body{
        display:flex!important;
        flex-direction:column!important;
        justify-content:flex-start!important;
        gap:7px!important;
        padding:8px 14px calc(10px + env(safe-area-inset-bottom))!important;
    }
    #wcPredictionModal .wc2-modal-meta{min-height:30px!important;}
    #wcPredictionModal .wc2-slip{flex:0 0 auto!important;}
    #wcPredictionModal .wc2-quick{flex:0 0 auto!important;margin-top:2px!important;}
    #wcPredictionModal .wc2-quick-label{margin-bottom:5px!important;}
    #wcPredictionModal .wc2-quick-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:6px!important;}
    #wcPredictionModal .wc2-quick button{height:31px!important;min-height:31px!important;font-size:12px!important;border-radius:10px!important;}
    #wcPredictionModal .wc2-modal-actions{
        margin-top:8px!important;
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:8px!important;
    }
    #wcPredictionModal .wc2-modal-actions .wc2-btn{height:39px!important;min-height:39px!important;border-radius:13px!important;font-size:12px!important;}
    #wcPredictionModal .wc2-modal-actions .js-save-prediction{grid-column:1 / 2!important;}
    #wcPredictionModal .wc2-modal-actions .js-save-next{grid-column:2 / 3!important;}
    #wcPredictionModal .wc2-modal-actions .js-close-wc-modal{grid-column:1 / 2!important;}
    #wcPredictionModal .wc2-modal-actions .js-next-match{grid-column:2 / 3!important;}
}
@media (max-width:390px),(max-height:700px) and (max-width:720px){
    #wcPredictionModal .wc2-modal-head{height:48px!important;min-height:48px!important;}
    #wcPredictionModal .wc2-modal-body{gap:5px!important;padding-top:6px!important;padding-bottom:7px!important;}
    #wcPredictionModal .wc2-slip-team img{width:36px!important;height:36px!important;}
    #wcPredictionModal .wc2-slip-team strong{font-size:12px!important;}
    #wcPredictionModal .wc2-score-control{height:43px!important;}
    #wcPredictionModal .wc2-score-control input{height:43px!important;line-height:43px!important;font-size:20px!important;}
    #wcPredictionModal .wc2-score-spin{height:15px!important;width:15px!important;}
    #wcPredictionModal .wc2-quick button{height:27px!important;min-height:27px!important;font-size:11px!important;}
    #wcPredictionModal .wc2-modal-actions{margin-top:6px!important;gap:6px!important;}
    #wcPredictionModal .wc2-modal-actions .wc2-btn{height:34px!important;min-height:34px!important;font-size:11px!important;}
}



/* FINAL MODAL SCALE AND CENTERING, bigger desktop and mobile */
#wcPredictionModal .wc2-modal{
    width:min(820px,calc((100vw / var(--wc2-page-zoom)) - 56px))!important;
    border-radius:38px!important;
}
#wcPredictionModal .wc2-modal-head{
    padding:28px 32px!important;
}
#wcPredictionModal .wc2-modal-head h3{
    font-size:30px!important;
    letter-spacing:-.03em!important;
}
#wcPredictionModal .wc2-close{
    width:50px!important;
    height:50px!important;
    border-radius:18px!important;
    font-size:18px!important;
}
#wcPredictionModal .wc2-modal-body{
    padding:30px 32px 32px!important;
}
#wcPredictionModal .wc2-slip{
    grid-template-columns:minmax(0,1fr) 220px minmax(0,1fr)!important;
    gap:32px!important;
    align-items:center!important;
}
#wcPredictionModal .wc2-slip-team{
    display:flex!important;
    flex-direction:column!important;
    align-items:center!important;
    justify-content:center!important;
    text-align:center!important;
    gap:14px!important;
    min-width:0!important;
}
#wcPredictionModal .wc2-slip-team img{
    width:96px!important;
    height:96px!important;
    margin:0!important;
}
#wcPredictionModal .wc2-slip-team strong{
    display:block!important;
    width:100%!important;
    max-width:210px!important;
    text-align:center!important;
    font-size:22px!important;
    line-height:1.2!important;
    white-space:normal!important;
}
#wcPredictionModal .wc2-scorebox{
    min-width:220px!important;
    width:220px!important;
}
#wcPredictionModal .wc2-scoreline{
    width:220px!important;
    display:grid!important;
    grid-template-columns:88px 20px 88px!important;
    gap:12px!important;
    margin:0 auto!important;
}
#wcPredictionModal .wc2-score-control{
    width:88px!important;
    height:74px!important;
    border-radius:22px!important;
}
#wcPredictionModal .wc2-score-control input{
    height:74px!important;
    line-height:74px!important;
    font-size:36px!important;
    padding:0 30px 0 10px!important;
}
#wcPredictionModal .wc2-score-spin{
    width:24px!important;
    height:25px!important;
    right:8px!important;
}
#wcPredictionModal .wc2-scoreline span{
    font-size:34px!important;
}
#wcPredictionModal .wc2-quick{
    margin-top:30px!important;
}
#wcPredictionModal .wc2-quick-label{
    font-size:13px!important;
    margin-bottom:12px!important;
}
#wcPredictionModal .wc2-quick-grid{
    gap:10px!important;
}
#wcPredictionModal .wc2-quick button{
    height:48px!important;
    min-height:48px!important;
    border-radius:16px!important;
    font-size:16px!important;
}
#wcPredictionModal .wc2-modal-actions{
    margin-top:30px!important;
    gap:12px!important;
}
#wcPredictionModal .wc2-modal-actions .wc2-btn{
    height:52px!important;
    min-height:52px!important;
    border-radius:17px!important;
    font-size:15px!important;
    padding:0 20px!important;
}

@media (max-width:720px){
    #wcPredictionModal .wc2-modal{
        width:100vw!important;
        height:100dvh!important;
        border-radius:0!important;
    }
    #wcPredictionModal .wc2-modal-head{
        height:70px!important;
        min-height:70px!important;
        padding:14px 20px!important;
    }
    #wcPredictionModal .wc2-modal-head h3{
        font-size:24px!important;
    }
    #wcPredictionModal .wc2-close{
        width:46px!important;
        height:46px!important;
        flex:0 0 46px!important;
        border-radius:16px!important;
    }
    #wcPredictionModal .wc2-modal-body{
        padding:14px 20px calc(16px + env(safe-area-inset-bottom))!important;
        gap:14px!important;
        display:flex!important;
        flex-direction:column!important;
        overflow:hidden!important;
    }
    #wcPredictionModal .wc2-modal-meta{
        min-height:42px!important;
        font-size:14px!important;
        margin:0!important;
    }
    #wcPredictionModal .wc2-nav-btn{
        width:42px!important;
        height:42px!important;
        border-radius:15px!important;
        font-size:16px!important;
    }
    #wcPredictionModal .wc2-slip{
        width:100%!important;
        display:grid!important;
        grid-template-columns:minmax(0,1fr) 150px minmax(0,1fr)!important;
        gap:10px!important;
        align-items:center!important;
        flex:0 0 auto!important;
    }
    #wcPredictionModal .wc2-slip-team{
        display:flex!important;
        flex-direction:column!important;
        align-items:center!important;
        justify-content:center!important;
        text-align:center!important;
        gap:9px!important;
        min-width:0!important;
    }
    #wcPredictionModal .wc2-slip-team img{
        width:62px!important;
        height:62px!important;
        margin:0!important;
        flex:0 0 62px!important;
    }
    #wcPredictionModal .wc2-slip-team strong{
        width:100%!important;
        max-width:100px!important;
        font-size:15px!important;
        line-height:1.18!important;
        text-align:center!important;
        white-space:normal!important;
        display:-webkit-box!important;
        -webkit-line-clamp:2!important;
        -webkit-box-orient:vertical!important;
        overflow:hidden!important;
    }
    #wcPredictionModal .wc2-scorebox{
        width:150px!important;
        min-width:150px!important;
    }
    #wcPredictionModal .wc2-scoreline{
        width:150px!important;
        display:grid!important;
        grid-template-columns:62px 14px 62px!important;
        gap:6px!important;
        align-items:center!important;
        margin:0 auto!important;
    }
    #wcPredictionModal .wc2-score-control{
        width:62px!important;
        height:62px!important;
        border-radius:18px!important;
    }
    #wcPredictionModal .wc2-score-control input{
        height:62px!important;
        line-height:62px!important;
        font-size:28px!important;
        padding:0 22px 0 6px!important;
    }
    #wcPredictionModal .wc2-score-spin{
        width:19px!important;
        height:20px!important;
        right:5px!important;
        border-radius:8px!important;
        font-size:10px!important;
    }
    #wcPredictionModal .wc2-score-spin.up{top:7px!important;}
    #wcPredictionModal .wc2-score-spin.down{bottom:7px!important;}
    #wcPredictionModal .wc2-scoreline span{
        font-size:23px!important;
    }
    #wcPredictionModal .wc2-quick{
        flex:0 0 auto!important;
        margin:0!important;
    }
    #wcPredictionModal .wc2-quick-label{
        font-size:11px!important;
        margin-bottom:8px!important;
    }
    #wcPredictionModal .wc2-quick-grid{
        display:grid!important;
        grid-template-columns:repeat(2,minmax(0,1fr))!important;
        gap:8px!important;
    }
    #wcPredictionModal .wc2-quick button{
        height:39px!important;
        min-height:39px!important;
        border-radius:13px!important;
        font-size:14px!important;
    }
    #wcPredictionModal .wc2-modal-actions{
        margin-top:auto!important;
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:10px!important;
    }
    #wcPredictionModal .wc2-modal-actions .wc2-btn{
        height:46px!important;
        min-height:46px!important;
        border-radius:15px!important;
        font-size:13px!important;
        padding:0 10px!important;
    }
}

@media (max-width:390px),(max-height:700px) and (max-width:720px){
    #wcPredictionModal .wc2-modal-head{height:62px!important;min-height:62px!important;padding:11px 16px!important;}
    #wcPredictionModal .wc2-modal-head h3{font-size:21px!important;}
    #wcPredictionModal .wc2-close{width:42px!important;height:42px!important;flex-basis:42px!important;}
    #wcPredictionModal .wc2-modal-body{padding:10px 16px calc(12px + env(safe-area-inset-bottom))!important;gap:10px!important;}
    #wcPredictionModal .wc2-modal-meta{min-height:38px!important;font-size:13px!important;}
    #wcPredictionModal .wc2-nav-btn{width:38px!important;height:38px!important;}
    #wcPredictionModal .wc2-slip{grid-template-columns:minmax(0,1fr) 132px minmax(0,1fr)!important;gap:8px!important;}
    #wcPredictionModal .wc2-slip-team img{width:54px!important;height:54px!important;flex-basis:54px!important;}
    #wcPredictionModal .wc2-slip-team strong{font-size:14px!important;max-width:88px!important;}
    #wcPredictionModal .wc2-scorebox{width:132px!important;min-width:132px!important;}
    #wcPredictionModal .wc2-scoreline{width:132px!important;grid-template-columns:55px 12px 55px!important;gap:5px!important;}
    #wcPredictionModal .wc2-score-control{width:55px!important;height:56px!important;border-radius:16px!important;}
    #wcPredictionModal .wc2-score-control input{height:56px!important;line-height:56px!important;font-size:25px!important;}
    #wcPredictionModal .wc2-score-spin{width:17px!important;height:18px!important;}
    #wcPredictionModal .wc2-quick-grid{gap:7px!important;}
    #wcPredictionModal .wc2-quick button{height:35px!important;min-height:35px!important;font-size:13px!important;}
    #wcPredictionModal .wc2-modal-actions{gap:8px!important;}
    #wcPredictionModal .wc2-modal-actions .wc2-btn{height:42px!important;min-height:42px!important;font-size:12px!important;}
}


/* V2 mobile match cards and prediction modal polish */
@media (max-width:720px){
    .wc2-wrap{width:100%!important;padding-left:14px!important;padding-right:14px!important;box-sizing:border-box!important;}
    .wc2-days{gap:16px!important;}
    .wc2-day-label{margin:10px 0 10px!important;font-size:14px!important;}
    .wc2-match{
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:12px!important;
        min-height:0!important;
        padding:15px!important;
        border-radius:24px!important;
        background:linear-gradient(145deg,rgba(17,22,49,.92),rgba(8,13,31,.96))!important;
        border:1px solid rgba(125,220,255,.15)!important;
        box-shadow:0 16px 44px rgba(0,0,0,.22)!important;
    }
    .wc2-match:before{width:4px!important;border-radius:999px!important;inset:14px auto 14px 0!important;}
    .wc2-time{
        display:flex!important;
        align-items:center!important;
        justify-content:space-between!important;
        text-align:left!important;
        padding-left:8px!important;
    }
    .wc2-time strong{font-size:20px!important;line-height:1!important;}
    .wc2-time span{margin:0!important;font-size:11px!important;color:rgba(235,238,255,.68)!important;}
    .wc2-versus{
        display:grid!important;
        grid-template-columns:1fr auto 1fr!important;
        align-items:center!important;
        gap:10px!important;
        padding:0 2px!important;
    }
    .wc2-team,
    .wc2-team.away{
        display:flex!important;
        flex-direction:column!important;
        align-items:center!important;
        justify-content:center!important;
        gap:8px!important;
        text-align:center!important;
        min-width:0!important;
    }
    .wc2-team.away{flex-direction:column-reverse!important;}
    .wc2-team-name{
        max-width:110px!important;
        white-space:normal!important;
        line-height:1.15!important;
        text-align:center!important;
        font-size:14px!important;
        font-weight:950!important;
        display:-webkit-box!important;
        -webkit-line-clamp:2!important;
        -webkit-box-orient:vertical!important;
        overflow:hidden!important;
    }
    .wc2-flag{width:46px!important;height:46px!important;box-shadow:0 10px 20px rgba(0,0,0,.30)!important;}
    .wc2-vs{width:44px!important;height:34px!important;border-radius:999px!important;font-size:11px!important;background:rgba(255,255,255,.075)!important;}
    .wc2-meta{justify-content:center!important;margin-top:2px!important;}
    .wc2-chip{min-height:28px!important;font-size:11px!important;}
    .wc2-pick{
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:10px!important;
        text-align:center!important;
        justify-items:stretch!important;
    }
    .wc2-score{
        width:100%!important;
        height:46px!important;
        margin:0!important;
        border-radius:16px!important;
        background:rgba(0,0,0,.28)!important;
        border-color:rgba(125,220,255,.16)!important;
        font-size:19px!important;
    }
    .wc2-pick .wc2-btn{width:100%!important;height:46px!important;min-height:46px!important;border-radius:16px!important;font-size:14px!important;}

    #wcPredictionModal.wc2-modal-backdrop,
    #wcPredictionModal.wc2-modal-backdrop.is-open{
        align-items:stretch!important;
        justify-content:stretch!important;
        padding:0!important;
        overflow:hidden!important;
        background:rgba(4,6,18,.88)!important;
    }
    #wcPredictionModal .wc2-modal{
        width:100vw!important;
        height:100dvh!important;
        max-width:none!important;
        margin:0!important;
        border-radius:0!important;
        border:0!important;
        background:radial-gradient(circle at 50% 0%,rgba(50,87,190,.34),transparent 36%),linear-gradient(180deg,#151a34 0%,#090d1c 100%)!important;
        display:flex!important;
        flex-direction:column!important;
    }
    #wcPredictionModal .wc2-modal-head{
        height:72px!important;
        min-height:72px!important;
        padding:14px 20px!important;
        border-bottom:1px solid rgba(255,255,255,.08)!important;
    }
    #wcPredictionModal .wc2-modal-head h3{font-size:24px!important;}
    #wcPredictionModal .wc2-close{width:46px!important;height:46px!important;border-radius:16px!important;}
    #wcPredictionModal .wc2-modal-body{
        flex:1 1 auto!important;
        min-height:0!important;
        padding:14px 18px calc(16px + env(safe-area-inset-bottom))!important;
        display:grid!important;
        grid-template-rows:auto auto auto 1fr auto!important;
        gap:14px!important;
        overflow:hidden!important;
    }
    #wcPredictionModal .wc2-modal-meta{
        min-height:42px!important;
        margin:0!important;
        display:flex!important;
        align-items:center!important;
        justify-content:space-between!important;
        flex-direction:row!important;
        font-size:14px!important;
    }
    #wcPredictionModal .wc2-modal-nav{margin-left:auto!important;}
    #wcPredictionModal .wc2-nav-btn{width:42px!important;height:42px!important;border-radius:15px!important;}
    #wcPredictionModal .wc2-slip{
        width:100%!important;
        display:grid!important;
        grid-template-columns:minmax(0,1fr) 148px minmax(0,1fr)!important;
        gap:10px!important;
        align-items:center!important;
        padding:12px 0 6px!important;
    }
    #wcPredictionModal .wc2-slip-team{
        gap:9px!important;
        align-items:center!important;
        justify-content:center!important;
        text-align:center!important;
    }
    #wcPredictionModal .wc2-slip-team img{width:66px!important;height:66px!important;flex:0 0 66px!important;margin:0!important;}
    #wcPredictionModal .wc2-slip-team strong{
        max-width:112px!important;
        min-height:34px!important;
        display:flex!important;
        align-items:flex-start!important;
        justify-content:center!important;
        text-align:center!important;
        font-size:15px!important;
        line-height:1.13!important;
    }
    #wcPredictionModal .wc2-scorebox{width:148px!important;min-width:148px!important;align-self:center!important;}
    #wcPredictionModal .wc2-scoreline{width:148px!important;grid-template-columns:62px 14px 62px!important;gap:5px!important;}
    #wcPredictionModal .wc2-score-control{width:62px!important;height:62px!important;border-radius:18px!important;background:rgba(18,31,59,.90)!important;border-color:rgba(125,220,255,.25)!important;}
    #wcPredictionModal .wc2-score-control input{height:62px!important;line-height:62px!important;font-size:29px!important;padding:0 22px 0 7px!important;}
    #wcPredictionModal .wc2-scoreline span{font-size:24px!important;}
    #wcPredictionModal .wc2-score-spin{width:19px!important;height:20px!important;right:5px!important;border-radius:8px!important;}
    #wcPredictionModal .wc2-quick{margin:0!important;align-self:start!important;}
    #wcPredictionModal .wc2-quick-label{font-size:11px!important;margin-bottom:8px!important;}
    #wcPredictionModal .wc2-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:8px!important;}
    #wcPredictionModal .wc2-quick button{height:39px!important;min-height:39px!important;border-radius:14px!important;font-size:14px!important;}
    #wcPredictionModal .wc2-modal-actions{
        align-self:end!important;
        margin:0!important;
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:9px!important;
        width:100%!important;
    }
    #wcPredictionModal .wc2-modal-actions .wc2-btn{height:46px!important;min-height:46px!important;border-radius:15px!important;font-size:13px!important;}
}
@media (max-width:390px){
    #wcPredictionModal .wc2-modal-head{height:64px!important;min-height:64px!important;padding:10px 16px!important;}
    #wcPredictionModal .wc2-modal-head h3{font-size:21px!important;}
    #wcPredictionModal .wc2-close{width:42px!important;height:42px!important;}
    #wcPredictionModal .wc2-modal-body{padding:10px 14px calc(12px + env(safe-area-inset-bottom))!important;gap:10px!important;}
    #wcPredictionModal .wc2-modal-meta{min-height:38px!important;}
    #wcPredictionModal .wc2-nav-btn{width:38px!important;height:38px!important;}
    #wcPredictionModal .wc2-slip{grid-template-columns:minmax(0,1fr) 132px minmax(0,1fr)!important;gap:7px!important;padding-top:8px!important;}
    #wcPredictionModal .wc2-slip-team img{width:58px!important;height:58px!important;flex-basis:58px!important;}
    #wcPredictionModal .wc2-slip-team strong{font-size:13px!important;max-width:88px!important;min-height:30px!important;}
    #wcPredictionModal .wc2-scorebox{width:132px!important;min-width:132px!important;}
    #wcPredictionModal .wc2-scoreline{width:132px!important;grid-template-columns:55px 12px 55px!important;gap:5px!important;}
    #wcPredictionModal .wc2-score-control{width:55px!important;height:56px!important;}
    #wcPredictionModal .wc2-score-control input{height:56px!important;line-height:56px!important;font-size:25px!important;}
    #wcPredictionModal .wc2-quick button{height:35px!important;min-height:35px!important;}
    #wcPredictionModal .wc2-modal-actions .wc2-btn{height:42px!important;min-height:42px!important;font-size:12px!important;}
}



/* Rules, prizes and local timezone timer */
.wc2-info-panel{display:grid;grid-template-columns:380px 1fr;gap:16px;margin:0 0 28px}.wc2-countdown-card,.wc2-rules-grid article,.wc2-prizes{border-radius:28px;background:linear-gradient(145deg,rgba(255,255,255,.075),rgba(255,255,255,.035));border:1px solid rgba(255,255,255,.10);box-shadow:0 18px 50px rgba(0,0,0,.18)}.wc2-countdown-card{padding:24px}.wc2-kicker{display:inline-flex;align-items:center;gap:9px;color:#91dcff;font-size:12px;font-weight:950;text-transform:uppercase;letter-spacing:.08em}.wc2-countdown-card h3{font-size:24px;margin:12px 0}.wc2-countdown{display:grid;grid-template-columns:auto 1fr auto 1fr auto 1fr;align-items:end;gap:8px;margin:12px 0}.wc2-countdown strong{font-size:36px;line-height:1;font-weight:950;background:linear-gradient(135deg,#fff,#6ee7ff);background-clip:text;-webkit-background-clip:text;color:transparent}.wc2-countdown span{color:rgba(235,238,255,.58);font-weight:850;padding-bottom:4px}.wc2-countdown-card p{margin:12px 0 0;color:rgba(235,238,255,.66);line-height:1.55}.wc2-rules-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.wc2-rules-grid article{padding:22px}.wc2-rules-grid .icon{width:42px;height:42px;border-radius:16px;display:grid;place-items:center;background:rgba(99,102,241,.20);color:#9fe8ff;margin-bottom:14px}.wc2-rules-grid h3{margin:0 0 8px;font-size:18px}.wc2-rules-grid p{margin:0;color:rgba(235,238,255,.68);line-height:1.6;font-size:14px}.wc2-prizes{padding:20px;margin-bottom:30px;background:linear-gradient(135deg,rgba(91,92,255,.12),rgba(49,212,255,.08))}.wc2-prize-head{display:flex;align-items:end;justify-content:space-between;gap:14px;margin-bottom:14px}.wc2-prize-head span{color:#91dcff;font-weight:950;text-transform:uppercase;font-size:12px;letter-spacing:.08em}.wc2-prize-head strong{font-size:22px}.wc2-prize-track{display:grid;grid-template-columns:1.35fr repeat(4,1fr);gap:10px}.wc2-prize-track article{min-height:104px;border-radius:20px;padding:16px;background:rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.09)}.wc2-prize-track b{display:inline-grid;place-items:center;width:36px;height:36px;border-radius:14px;background:linear-gradient(135deg,#625cff,#31d4ff);margin-bottom:10px}.wc2-prize-track strong{display:block}.wc2-prize-track span{display:block;margin-top:4px;color:rgba(235,238,255,.58);font-size:12px;font-weight:800}.wc2-count-chip{display:block;margin-top:6px;font-style:normal;color:#6ee7ff;font-size:11px;font-weight:950}.wc2-day-label small{font-size:11px;color:rgba(235,238,255,.46);font-weight:800;margin-left:4px}.wc2-side-rules .wc2-btn{margin-top:14px;width:100%}@media(max-width:1180px){.wc2-info-panel{grid-template-columns:1fr}.wc2-prize-track{grid-template-columns:repeat(2,1fr)}.wc2-prize-track article:first-child{grid-column:span 2}}@media(max-width:720px){.wc2-info-panel{gap:12px;margin-bottom:22px}.wc2-countdown-card{padding:18px;border-radius:24px}.wc2-countdown-card h3{font-size:21px}.wc2-countdown strong{font-size:30px}.wc2-rules-grid{grid-template-columns:1fr;gap:10px}.wc2-rules-grid article{padding:16px;border-radius:22px}.wc2-prizes{padding:16px;border-radius:24px}.wc2-prize-head{display:block}.wc2-prize-head strong{display:block;margin-top:4px;font-size:20px}.wc2-prize-track{grid-template-columns:1fr}.wc2-prize-track article:first-child{grid-column:auto}.wc2-prize-track article{min-height:82px}.wc2-day-label{flex-wrap:wrap}.wc2-day-label:after{min-width:100%}}


/* ─── HERO V4: Cinematic split-screen ─────────────────────────────────── */

/* Decorative grid overlay */
.wc2-hero-v4{
    position:relative;
    margin:0 0 34px;
    border-radius:38px;
    overflow:hidden;
    border:1px solid rgba(125,220,255,.12);
    background:#080f1e;
    box-shadow:0 40px 120px rgba(0,0,0,.40);
}
/* Ambient light blobs */
.wc2-hero-v4::before{
    content:"";
    position:absolute;
    inset:0;
    background:
        radial-gradient(ellipse 70% 50% at -10% 50%,rgba(98,92,255,.32),transparent),
        radial-gradient(ellipse 50% 60% at 110% 20%,rgba(49,212,255,.22),transparent),
        radial-gradient(ellipse 40% 40% at 60% 95%,rgba(98,92,255,.14),transparent);
    pointer-events:none;
}
/* Subtle grid texture */
.wc2-hero-v4::after{
    content:"";
    position:absolute;
    inset:0;
    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
    background-size:52px 52px;
    pointer-events:none;
    mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 30%,transparent 100%);
}

/* ─── Top copy band ─────────────────────────────────────────── */
.wc2-hero-v4-top{
    position:relative;
    z-index:1;
    padding:52px 52px 0;
    display:grid;
    grid-template-columns:minmax(0,1fr) 390px;
    gap:40px;
    align-items:start;
}
.wc2-hero-v4-copy{}

/* Badge */
.wc2-badge-v4{
    display:inline-flex;
    align-items:center;
    gap:9px;
    height:34px;
    padding:0 14px;
    border-radius:999px;
    background:rgba(98,92,255,.15);
    border:1px solid rgba(122,133,255,.22);
    font-size:11px;
    font-weight:900;
    letter-spacing:.09em;
    text-transform:uppercase;
    color:#b8c4ff;
    margin-bottom:22px;
}

/* Giant editorial title */
.wc2-title-v4{
    font-size:clamp(52px,6vw,98px);
    line-height:.88;
    margin:0 0 22px;
    font-weight:950;
    letter-spacing:-.07em;
    color:#fff;
}
.wc2-title-v4 .wc2-title-line2{
    display:block;
    background:linear-gradient(100deg,#a8b8ff 0%,#58e8ff 55%,#a8b8ff 100%);
    background-clip:text;
    -webkit-background-clip:text;
    color:transparent;
    background-size:200% 100%;
    animation:wc2-shimmer 4s linear infinite;
}
@keyframes wc2-shimmer{
    0%{background-position:100% 0}
    100%{background-position:-100% 0}
}

.wc2-lead-v4{
    font-size:17px;
    line-height:1.78;
    color:rgba(220,228,255,.72);
    max-width:680px;
    margin:0 0 28px;
}
.wc2-hero-v4-actions{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}

/* ─── Right panel: countdown + ring ───────────────────────── */
.wc2-live-v4{
    position:relative;
    border-radius:28px;
    padding:28px;
    background:rgba(5,12,30,.55);
    border:1px solid rgba(255,255,255,.09);
    backdrop-filter:blur(18px);
    display:flex;
    flex-direction:column;
    gap:22px;
}

/* Kickoff label + ring row */
.wc2-live-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
}
.wc2-live-label{
    font-size:11px;
    font-weight:900;
    letter-spacing:.10em;
    text-transform:uppercase;
    color:#6edbff;
    margin-bottom:6px;
}
.wc2-live-title{
    font-size:20px;
    font-weight:950;
    line-height:1.2;
    color:#fff;
}

/* Progress ring (SVG-based, no :before tricks) */
.wc2-ring-svg{flex:0 0 auto}
.wc2-ring-track{fill:none;stroke:rgba(255,255,255,.08);stroke-width:6}
.wc2-ring-fill{fill:none;stroke:#38d5ff;stroke-width:6;stroke-linecap:round;transition:stroke-dasharray .6s ease}
.wc2-ring-text{font-size:14px;font-weight:900;fill:#38d5ff;dominant-baseline:central;text-anchor:middle}

/* Countdown units */
.wc2-cd-row{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8px;
}
.wc2-cd-unit{
    text-align:center;
    border-radius:16px;
    padding:14px 8px 12px;
    background:rgba(0,0,0,.26);
    border:1px solid rgba(125,220,255,.10);
    position:relative;
    overflow:hidden;
}
.wc2-cd-unit::before{
    content:"";
    position:absolute;
    inset:0 0 auto 0;
    height:1px;
    background:linear-gradient(90deg,transparent,rgba(125,220,255,.35),transparent);
}
.wc2-cd-num{
    display:block;
    font-size:34px;
    font-weight:950;
    line-height:1;
    letter-spacing:-.04em;
    color:#fff;
}
.wc2-cd-lbl{
    display:block;
    margin-top:5px;
    font-size:10px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:rgba(200,215,255,.50);
}

/* ─── Horizontal stats strip ──────────────────────────────── */
.wc2-stats-strip{
    display:flex;
    gap:0;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
    background:rgba(0,0,0,.20);
}
.wc2-stat-item{
    flex:1;
    display:flex;
    align-items:center;
    gap:14px;
    padding:18px 22px;
    border-right:1px solid rgba(255,255,255,.06);
}
.wc2-stat-item:last-child{border-right:none}
.wc2-stat-icon{
    width:40px;
    height:40px;
    border-radius:14px;
    display:grid;
    place-items:center;
    font-size:18px;
    flex:0 0 auto;
}
.wc2-stat-icon.games{background:rgba(98,92,255,.18);color:#9fa8ff}
.wc2-stat-icon.points{background:rgba(49,212,255,.14);color:#6ee7ff}
.wc2-stat-icon.prize{background:rgba(255,190,50,.12);color:#ffd166}
.wc2-stat-text strong{
    display:block;
    font-size:22px;
    font-weight:950;
    line-height:1;
    color:#fff;
}
.wc2-stat-text span{
    display:block;
    margin-top:4px;
    font-size:11px;
    font-weight:850;
    letter-spacing:.04em;
    color:rgba(200,215,255,.50);
    text-transform:uppercase;
}

/* ─── Bottom info row: rules + prizes ─────────────────────── */
.wc2-info-row{
    position:relative;
    z-index:1;
    display:grid;
    grid-template-columns:1.1fr .9fr;
    gap:0;
    border-top:1px solid rgba(255,255,255,.06);
}
.wc2-rules-col,.wc2-prizes-col{
    padding:34px 52px;
}
.wc2-rules-col{
    border-right:1px solid rgba(255,255,255,.06);
}
.wc2-col-heading{
    display:flex;
    align-items:center;
    gap:10px;
    margin:0 0 22px;
    font-size:13px;
    font-weight:900;
    letter-spacing:.09em;
    text-transform:uppercase;
    color:#6edbff;
}
.wc2-col-heading i{
    width:32px;
    height:32px;
    border-radius:11px;
    display:grid;
    place-items:center;
    background:rgba(98,92,255,.18);
    color:#9fa8ff;
    font-size:14px;
}

/* Rules: horizontal score line */
.wc2-rules-line{
    display:flex;
    align-items:stretch;
    gap:0;
}
.wc2-rule-node{
    flex:1;
    padding:16px 14px 14px;
    border-radius:0;
    border-right:1px solid rgba(255,255,255,.06);
    position:relative;
}
.wc2-rule-node:last-child{border-right:none}
.wc2-rule-node::after{
    content:"";
    position:absolute;
    top:0;left:0;right:0;
    height:2px;
    border-radius:2px 2px 0 0;
}
.wc2-rule-node.pts5::after{background:linear-gradient(90deg,#625cff,#31d4ff)}
.wc2-rule-node.pts3::after{background:rgba(98,92,255,.55)}
.wc2-rule-node.pts2::after{background:rgba(98,92,255,.30)}
.wc2-rule-node.pts0::after{background:rgba(255,255,255,.10)}
.wc2-rule-pts{
    font-size:28px;
    font-weight:950;
    line-height:1;
    color:#fff;
    margin-bottom:6px;
}
.wc2-rule-pts sup{font-size:12px;vertical-align:super}
.wc2-rule-name{
    display:block;
    font-size:12px;
    font-weight:850;
    color:rgba(235,240,255,.85);
    margin-bottom:4px;
}
.wc2-rule-desc{
    font-size:11px;
    line-height:1.55;
    color:rgba(200,215,255,.48);
}

/* Prizes: podium */
.wc2-podium{
    display:grid;
    grid-template-columns:1fr 1.2fr 1fr;
    gap:8px;
    align-items:end;
}
.wc2-podium-place{
    border-radius:20px;
    padding:16px 14px 14px;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.07);
    display:flex;
    flex-direction:column;
    justify-content:flex-end;
    min-height:96px;
    position:relative;
    overflow:hidden;
}
.wc2-podium-place.gold{
    min-height:130px;
    background:linear-gradient(160deg,rgba(255,190,50,.10),rgba(98,92,255,.10));
    border-color:rgba(255,200,80,.18);
}
.wc2-podium-place.gold::before{
    content:"";
    position:absolute;
    inset:0 0 auto 0;
    height:1px;
    background:linear-gradient(90deg,transparent,rgba(255,200,80,.50),transparent);
}
.wc2-podium-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:30px;
    height:30px;
    border-radius:10px;
    font-size:11px;
    font-weight:950;
    margin-bottom:10px;
    flex:0 0 auto;
}
.wc2-podium-place.gold .wc2-podium-badge{background:linear-gradient(135deg,#ffd166,#f4a00a);color:#3d2200}
.wc2-podium-place:not(.gold) .wc2-podium-badge{background:rgba(255,255,255,.10);color:#cdd5ff}
.wc2-podium-strong{display:block;font-weight:950;font-size:14px;margin-bottom:3px}
.wc2-podium-sub{font-size:11px;color:rgba(200,215,255,.46)}

.wc2-podium-mini{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
    margin-top:8px;
}
.wc2-podium-mini article{
    border-radius:14px;
    padding:12px;
    background:rgba(0,0,0,.18);
    border:1px solid rgba(255,255,255,.06);
    display:flex;
    align-items:center;
    gap:10px;
}
.wc2-podium-mini b{
    width:28px;
    height:28px;
    border-radius:9px;
    background:rgba(98,92,255,.22);
    display:grid;
    place-items:center;
    font-size:11px;
    font-weight:950;
    color:#b8c4ff;
    flex:0 0 auto;
}
.wc2-podium-mini strong{font-size:12px;font-weight:950}

/* ─── Hide old panels ─────────────────────────────────────── */
.wc2-info-panel,.wc2-prizes{display:none!important}

/* ─── Local note ──────────────────────────────────────────── */
.wc2-local-note-v4{
    font-size:11px;
    color:rgba(200,215,255,.40);
    margin:0;
    line-height:1.5;
}

/* ─── Responsive ──────────────────────────────────────────── */
@media(max-width:1180px){
    .wc2-hero-v4-top{grid-template-columns:1fr;padding:38px 38px 0}
    .wc2-live-v4{max-width:none}
    .wc2-info-row{grid-template-columns:1fr}
    .wc2-rules-col{border-right:none;border-bottom:1px solid rgba(255,255,255,.06)}
    .wc2-rules-col,.wc2-prizes-col{padding:28px 38px}
}
@media(max-width:820px){
    .wc2-hero-v4{border-radius:26px;margin-bottom:22px}
    .wc2-hero-v4-top{padding:26px 22px 0;gap:24px}
    .wc2-title-v4{font-size:46px}
    .wc2-lead-v4{font-size:15px}
    .wc2-stats-strip{flex-direction:column}
    .wc2-stat-item{border-right:none;border-bottom:1px solid rgba(255,255,255,.06)}
    .wc2-stat-item:last-child{border-bottom:none}
    .wc2-rules-col,.wc2-prizes-col{padding:22px}
    .wc2-rules-line{flex-direction:column;gap:0}
    .wc2-rule-node{border-right:none;border-bottom:1px solid rgba(255,255,255,.06);padding:14px 12px}
    .wc2-rule-node:last-child{border-bottom:none}
    .wc2-rule-node::after{width:2px;height:auto;top:0;bottom:0;left:0;right:auto;border-radius:0 2px 2px 0}
    .wc2-podium{grid-template-columns:1fr}
    .wc2-podium-place,.wc2-podium-place.gold{min-height:80px}
    .wc2-hero-v4-actions .wc2-btn{width:100%}
}
@media(max-width:480px){
    .wc2-title-v4{font-size:38px}
    .wc2-cd-num{font-size:28px}
    .wc2-hero-v4-top{padding:20px 16px 0}
    .wc2-rules-col,.wc2-prizes-col{padding:18px 16px}
    .wc2-podium-mini{grid-template-columns:1fr}
}



/* HERO V5, full width premium stadium layout */
.worldcup-page main,
.worldcup-page .page-content{
    overflow-x:hidden!important;
}
.worldcup-page .wc2{
    background:
        radial-gradient(circle at 18% 0%,rgba(98,92,255,.28),transparent 34%),
        radial-gradient(circle at 84% 8%,rgba(49,212,255,.20),transparent 30%),
        linear-gradient(180deg,#071326 0%,#060917 46%,#090d1d 100%)!important;
}
.wc2-hero-v4{
    width:100vw!important;
    max-width:100vw!important;
    margin-left:calc(50% - 50vw)!important;
    margin-right:calc(50% - 50vw)!important;
    margin-top:0!important;
    margin-bottom:42px!important;
    border-radius:0!important;
    border-left:0!important;
    border-right:0!important;
    border-color:rgba(125,220,255,.14)!important;
    background:
        linear-gradient(115deg,rgba(9,13,30,.96) 0%,rgba(12,18,44,.94) 43%,rgba(5,20,38,.98) 100%)!important;
    box-shadow:none!important;
    min-height:680px!important;
}
.wc2-hero-v4::before{
    background:
        radial-gradient(circle at 16% 30%,rgba(105,92,255,.34),transparent 28%),
        radial-gradient(circle at 75% 18%,rgba(49,212,255,.24),transparent 24%),
        radial-gradient(circle at 58% 110%,rgba(99,102,241,.20),transparent 36%),
        linear-gradient(90deg,rgba(6,10,25,.25),transparent 42%,rgba(49,212,255,.06))!important;
}
.wc2-hero-v4::after{
    background-image:
        linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px),
        linear-gradient(120deg,transparent 0%,rgba(125,220,255,.08) 48%,transparent 54%)!important;
    background-size:64px 64px,64px 64px,100% 100%!important;
    mask-image:linear-gradient(90deg,black 0%,black 72%,transparent 100%)!important;
    opacity:.75!important;
}
.wc2-hero-v4-top,
.wc2-stats-strip,
.wc2-info-row{
    width:min(1480px,calc(100vw - 72px))!important;
    margin-left:auto!important;
    margin-right:auto!important;
}
.wc2-hero-v4-top{
    padding:78px 0 0!important;
    grid-template-columns:minmax(0,1.08fr) minmax(360px,.62fr)!important;
    gap:72px!important;
    align-items:center!important;
}
.wc2-badge-v4{
    height:40px!important;
    padding:0 18px!important;
    margin-bottom:26px!important;
    background:rgba(49,212,255,.10)!important;
    border-color:rgba(125,220,255,.28)!important;
    color:#9fe8ff!important;
    box-shadow:0 0 0 6px rgba(49,212,255,.035)!important;
}
.wc2-title-v4{
    font-size:clamp(64px,7.2vw,126px)!important;
    line-height:.84!important;
    max-width:980px!important;
    margin-bottom:28px!important;
    letter-spacing:-.075em!important;
}
.wc2-title-v4 .wc2-title-line2{
    background:linear-gradient(92deg,#8fa7ff 0%,#6ee7ff 42%,#ffffff 72%,#8fa7ff 100%)!important;
    background-size:220% 100%!important;
    background-clip:text!important;
    -webkit-background-clip:text!important;
}
.wc2-lead-v4{
    max-width:740px!important;
    font-size:19px!important;
    line-height:1.72!important;
    color:rgba(231,238,255,.76)!important;
    margin-bottom:34px!important;
}
.wc2-hero-v4-actions{
    gap:14px!important;
}
.wc2-hero-v4-actions .wc2-btn{
    min-height:54px!important;
    padding:0 24px!important;
    border-radius:18px!important;
}
.wc2-live-v4{
    border-radius:34px!important;
    padding:30px!important;
    background:linear-gradient(180deg,rgba(8,15,35,.78),rgba(4,10,24,.92))!important;
    border-color:rgba(125,220,255,.18)!important;
    box-shadow:0 28px 90px rgba(0,0,0,.40),inset 0 1px 0 rgba(255,255,255,.08)!important;
}
.wc2-live-v4::before{
    content:"";
    position:absolute;
    inset:1px;
    border-radius:33px;
    pointer-events:none;
    background:linear-gradient(135deg,rgba(255,255,255,.08),transparent 40%,rgba(49,212,255,.08));
}
.wc2-live-header,
.wc2-cd-row,
.wc2-local-note-v4{
    position:relative;
    z-index:1;
}
.wc2-live-title{
    font-size:24px!important;
}
.wc2-cd-row{
    gap:12px!important;
}
.wc2-cd-unit{
    border-radius:20px!important;
    padding:18px 10px 15px!important;
    background:rgba(0,0,0,.32)!important;
    border-color:rgba(125,220,255,.16)!important;
}
.wc2-cd-num{
    font-size:40px!important;
}
.wc2-stats-strip{
    position:relative!important;
    z-index:1!important;
    margin-top:56px!important;
    border:1px solid rgba(255,255,255,.08)!important;
    border-radius:26px!important;
    overflow:hidden!important;
    background:rgba(2,7,20,.50)!important;
    backdrop-filter:blur(16px)!important;
}
.wc2-stat-item{
    padding:22px 28px!important;
}
.wc2-info-row{
    position:relative!important;
    z-index:1!important;
    margin-top:16px!important;
    margin-bottom:52px!important;
    border:1px solid rgba(255,255,255,.08)!important;
    border-radius:30px!important;
    overflow:hidden!important;
    background:rgba(4,10,25,.58)!important;
    backdrop-filter:blur(16px)!important;
    grid-template-columns:1.08fr .92fr!important;
}
.wc2-rules-col,
.wc2-prizes-col{
    padding:30px!important;
}
@media(max-width:1180px){
    .wc2-hero-v4{min-height:0!important;margin-bottom:32px!important;}
    .wc2-hero-v4-top{grid-template-columns:1fr!important;gap:28px!important;padding-top:54px!important;}
    .wc2-live-v4{max-width:640px!important;}
    .wc2-info-row{grid-template-columns:1fr!important;}
}
@media(max-width:820px){
    .wc2-hero-v4-top,
    .wc2-stats-strip,
    .wc2-info-row{width:calc(100vw - 28px)!important;}
    .wc2-hero-v4{margin-bottom:26px!important;}
    .wc2-hero-v4-top{padding-top:34px!important;gap:22px!important;}
    .wc2-title-v4{font-size:clamp(44px,13vw,70px)!important;line-height:.90!important;letter-spacing:-.06em!important;}
    .wc2-lead-v4{font-size:16px!important;line-height:1.62!important;margin-bottom:24px!important;}
    .wc2-live-v4{padding:22px!important;border-radius:26px!important;}
    .wc2-live-v4::before{border-radius:25px!important;}
    .wc2-stats-strip{margin-top:24px!important;border-radius:22px!important;}
    .wc2-info-row{border-radius:24px!important;margin-bottom:30px!important;}
    .wc2-rules-col,.wc2-prizes-col{padding:22px!important;}
}
@media(max-width:520px){
    .wc2-hero-v4-top,
    .wc2-stats-strip,
    .wc2-info-row{width:calc(100vw - 24px)!important;}
    .wc2-badge-v4{width:100%!important;justify-content:center!important;font-size:10px!important;padding:0 10px!important;}
    .wc2-title-v4{font-size:42px!important;}
    .wc2-live-header{align-items:flex-start!important;}
    .wc2-ring-svg{width:64px!important;height:64px!important;}
    .wc2-cd-num{font-size:30px!important;}
    .wc2-cd-row{gap:7px!important;}
    .wc2-cd-unit{padding:14px 6px 12px!important;border-radius:16px!important;}
}

/* Dynamic local time and countdown polish */
.wc2-count-chip{
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    margin-top:7px!important;
    min-height:24px!important;
    padding:0 9px!important;
    border-radius:999px!important;
    background:rgba(49,212,255,.10)!important;
    border:1px solid rgba(49,212,255,.22)!important;
    color:#8eeaff!important;
    font-size:11px!important;
    font-style:normal!important;
    font-weight:950!important;
    white-space:nowrap!important;
}



/* Ricardo final: clean World Cup hero v4 */
.wc2-hero-v4-clean{
    position:relative!important;
    overflow:hidden!important;
    margin:0 0 34px!important;
    padding:34px!important;
    border-radius:34px!important;
    border:1px solid rgba(255,255,255,.10)!important;
    background:
        radial-gradient(circle at 18% 18%,rgba(255,255,255,.12),transparent 22%),
        radial-gradient(circle at 82% 10%,rgba(49,212,255,.24),transparent 28%),
        linear-gradient(135deg,#071328 0%,#0b1632 48%,#06101f 100%)!important;
    box-shadow:0 28px 90px rgba(0,0,0,.34)!important;
}
.wc2-hero-v4-clean::before,
.wc2-hero-v4-clean::after{display:none!important;content:none!important;}
.wc2-hero-v4-bgball{position:absolute!important;border-radius:999px!important;pointer-events:none!important;filter:blur(.2px)!important;opacity:.95!important;}
.wc2-hero-v4-bgball.ball-a{width:280px!important;height:280px!important;right:19%!important;top:-96px!important;background:radial-gradient(circle at 34% 32%,rgba(255,255,255,.92),rgba(49,212,255,.24) 24%,rgba(98,92,255,.10) 52%,transparent 70%)!important;}
.wc2-hero-v4-bgball.ball-b{width:190px!important;height:190px!important;left:-56px!important;bottom:-70px!important;background:radial-gradient(circle at 36% 30%,rgba(255,255,255,.50),rgba(255,209,102,.24) 28%,rgba(98,92,255,.09) 58%,transparent 72%)!important;}
.wc2-hero-v4-clean-grid{position:relative!important;z-index:2!important;display:grid!important;grid-template-columns:minmax(0,1fr) 360px!important;gap:26px!important;align-items:center!important;}
.wc2-hero-v4-main{min-width:0!important;}
.wc2-hero-v4-clean .wc2-badge-v4{height:32px!important;margin:0 0 18px!important;background:rgba(255,255,255,.08)!important;border-color:rgba(255,255,255,.13)!important;color:#dce7ff!important;}
.wc2-hero-v4-clean .wc2-title-v4{font-size:clamp(46px,6vw,88px)!important;line-height:.88!important;margin:0 0 16px!important;letter-spacing:-.065em!important;}
.wc2-hero-v4-clean .wc2-title-v4 span{display:block!important;background:linear-gradient(90deg,#ffffff 0%,#6ee7ff 54%,#ffd166 100%)!important;background-clip:text!important;-webkit-background-clip:text!important;color:transparent!important;}
.wc2-hero-v4-clean .wc2-lead-v4{max-width:560px!important;margin:0 0 22px!important;font-size:16px!important;line-height:1.55!important;color:rgba(235,242,255,.70)!important;}
.wc2-hero-v4-clean .wc2-hero-v4-actions{gap:10px!important;}
.wc2-hero-v4-clean .wc2-live-v4{padding:22px!important;border-radius:26px!important;background:rgba(2,8,20,.54)!important;border:1px solid rgba(255,255,255,.12)!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 18px 48px rgba(0,0,0,.22)!important;}
.wc2-hero-v4-clean .wc2-live-title{font-size:18px!important;}
.wc2-hero-v4-clean .wc2-cd-row{gap:8px!important;}
.wc2-hero-v4-clean .wc2-cd-unit{padding:12px 6px!important;border-radius:15px!important;background:rgba(255,255,255,.06)!important;}
.wc2-hero-v4-clean .wc2-cd-num{font-size:30px!important;}
.wc2-hero-v4-clean .wc2-local-note-v4{font-size:11px!important;color:rgba(235,242,255,.52)!important;}
.wc2-hero-v4-clean .wc2-stats-strip{position:relative!important;z-index:2!important;width:100%!important;margin:24px 0 0!important;display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:10px!important;background:transparent!important;border:0!important;padding:0!important;}
.wc2-hero-v4-clean .wc2-stat-item{min-height:74px!important;padding:15px 17px!important;border-radius:20px!important;background:rgba(255,255,255,.065)!important;border:1px solid rgba(255,255,255,.10)!important;box-shadow:none!important;}
@media(max-width:980px){.wc2-hero-v4-clean{padding:26px!important}.wc2-hero-v4-clean-grid{grid-template-columns:1fr!important}.wc2-hero-v4-clean .wc2-live-v4{max-width:none!important}}
@media(max-width:620px){.wc2-hero-v4-clean{padding:22px 16px!important;border-radius:26px!important;margin-bottom:24px!important}.wc2-hero-v4-clean .wc2-title-v4{font-size:40px!important}.wc2-hero-v4-clean .wc2-lead-v4{font-size:14px!important}.wc2-hero-v4-clean .wc2-stats-strip{grid-template-columns:1fr!important}.wc2-hero-v4-clean .wc2-hero-v4-actions .wc2-btn{width:100%!important}.wc2-hero-v4-clean .wc2-live-header{align-items:flex-start!important}.wc2-hero-v4-bgball.ball-a{right:-90px!important;top:-90px!important}.wc2-hero-v4-bgball.ball-b{display:none!important}}

/* Reward card, clean sidebar style */
.wc2-reward-card{
    position:relative!important;
    overflow:hidden!important;
    border-radius:28px!important;
    padding:24px!important;
    background:linear-gradient(145deg,rgba(18,22,43,.92),rgba(8,12,25,.96))!important;
    border:1px solid rgba(255,255,255,.10)!important;
    box-shadow:none!important;
}
.wc2-reward-card:before,
.wc2-reward-card:after{
    display:none!important;
    content:none!important;
}
.wc2-reward-card>*{
    position:relative;
    z-index:1;
}
.wc2-reward-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    margin-bottom:18px;
}
.wc2-reward-head h3{
    margin:0!important;
    font-size:22px!important;
    line-height:1.1!important;
    letter-spacing:-.04em;
}
.wc2-reward-kicker{
    display:none!important;
}
.wc2-reward-icon{
    width:42px;
    height:42px;
    border-radius:15px;
    display:grid;
    place-items:center;
    color:#fff;
    background:rgba(255,255,255,.075);
    border:1px solid rgba(255,255,255,.10);
    box-shadow:none;
    flex:0 0 42px;
}
.wc2-reward-progress{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:14px;
    padding:0 0 14px;
    border-radius:0;
    background:transparent;
    border:0;
    border-bottom:1px solid rgba(255,255,255,.075);
    color:rgba(235,238,255,.62);
    font-size:13px;
    font-weight:850;
}
.wc2-reward-progress strong{
    color:#fff;
    font-size:13px;
    font-weight:950;
}
.wc2-reward-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    min-height:30px;
    padding:0 11px;
    margin:0 0 12px;
    border-radius:11px;
    color:#7cf0ff;
    background:rgba(14,165,233,.14);
    border:1px solid rgba(14,165,233,.34);
    box-shadow:none;
    font-size:13px;
    font-weight:950;
}
.wc2-reward-badge i{
    font-size:12px;
}
.wc2-copy-code{
    width:100%;
    min-height:58px;
    display:flex!important;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    cursor:pointer;
    color:#fff;
    transition:.16s ease;
    font-family:inherit;
    border-radius:18px!important;
    background:rgba(255,255,255,.045)!important;
    border:1px solid rgba(255,255,255,.13)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04)!important;
    letter-spacing:.035em!important;
}
.wc2-copy-code span{
    min-width:0;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    font-weight:950;
}
.wc2-copy-code i{
    width:34px;
    height:34px;
    border-radius:12px;
    display:grid;
    place-items:center;
    color:#fff;
    background:rgba(255,255,255,.075);
    border:1px solid rgba(255,255,255,.10);
    flex:0 0 34px;
    font-size:14px;
}
.wc2-copy-code:hover{
    transform:translateY(-1px);
    border-color:rgba(110,231,255,.38)!important;
    background:rgba(255,255,255,.065)!important;
}
.wc2-copy-code.is-copied{
    border-color:rgba(74,222,128,.55)!important;
    background:rgba(74,222,128,.08)!important;
}
.wc2-copy-code.is-copied i{
    background:rgba(74,222,128,.18);
    border-color:rgba(74,222,128,.34);
    color:#86efac;
}
.wc2-copy-hint{
    margin-top:10px;
    color:rgba(235,238,255,.50);
    font-size:12px;
    font-weight:800;
    text-align:center;
}
.wc2-copy-hint.is-copied{
    color:#86efac;
}
.wc2-reward-empty{
    display:flex;
    align-items:flex-start;
    gap:13px;
    padding:15px;
    border-radius:18px;
    background:rgba(255,255,255,.045);
    border:1px solid rgba(255,255,255,.09);
}
.wc2-reward-empty p{
    margin:0!important;
    color:rgba(235,238,255,.68)!important;
    line-height:1.55!important;
    font-weight:850!important;
}
.wc2-reward-empty-icon{
    width:38px;
    height:38px;
    border-radius:14px;
    display:grid;
    place-items:center;
    color:#cfd6ff;
    background:rgba(255,255,255,.07);
    border:1px solid rgba(255,255,255,.10);
    flex:0 0 38px;
}
@media(max-width:520px){
    .wc2-reward-card{padding:22px!important;border-radius:26px!important;}
    .wc2-reward-head h3{font-size:21px!important;}
    .wc2-reward-icon{width:40px;height:40px;flex-basis:40px;}
    .wc2-copy-code{min-height:54px;font-size:13px;}
}

</style>
<?php $this->stop() ?>

<?php
$groupedMatches = [];
foreach ($matches as $m) {
    $dayKey = date('Y-m-d', strtotime((string)$m['kickoff_at']));
    $groupedMatches[$dayKey][] = $m;
}
$progressPct = $totalMatches > 0 ? min(100, round(($totalTips / $totalMatches) * 100)) : 0;
?>

<div class="wc2">
    <div class="wc2-wrap">
        <section class="wc2-hero-v5" id="wc2-rules">
            <div class="wc2-hero-v5-glow glow-a"></div>
            <div class="wc2-hero-v5-glow glow-b"></div>
            <div class="wc2-hero-v5-trophy"><i class="fa-solid fa-trophy"></i></div>

            <div class="wc2-hero-v5-inner">
                <div class="wc2-hero-v5-kicker"><i class="fa-solid fa-earth-americas"></i><?= t('World Cup Predictions') ?></div>
                <h1 class="wc2-hero-v5-title"><?= t('Predict the World Cup') ?></h1>

                <div class="wc2-hero-v5-live">
                    <div class="wc2-hero-v5-live-head">
                        <span><i class="fa-regular fa-clock"></i><?= t('Next kickoff') ?></span>
                        <em><?= t('Your local time') ?></em>
                    </div>
                    <div class="wc2-cd-row" id="wcCountdown" data-next-kickoff="<?= htmlspecialchars($nextKickoffIso, ENT_QUOTES) ?>">
                        <div class="wc2-cd-unit"><span class="wc2-cd-num" data-unit="days">00</span><span class="wc2-cd-lbl"><?= t('days') ?></span></div>
                        <div class="wc2-cd-unit"><span class="wc2-cd-num" data-unit="hours">00</span><span class="wc2-cd-lbl"><?= t('hours') ?></span></div>
                        <div class="wc2-cd-unit"><span class="wc2-cd-num" data-unit="minutes">00</span><span class="wc2-cd-lbl"><?= t('min') ?></span></div>
                        <div class="wc2-cd-unit"><span class="wc2-cd-num" data-unit="seconds">00</span><span class="wc2-cd-lbl"><?= t('sec') ?></span></div>
                    </div>
                    <p class="wc2-local-note-v4"><?= t('Berlin time is the reference. Your timezone is used automatically.') ?></p>
                </div>

                <div class="wc2-hero-v5-actions">
                    <?php if ($isParticipant): ?>
                        <a class="wc2-btn wc2-btn-main" href="<?= BASE_URL ?>/world-cup-predictions" data-wc-scroll="wc2-matches"><i class="fa-solid fa-futbol"></i><?= t('Make predictions') ?></a>
                    <?php else: ?>
                        <button type="button" class="wc2-btn wc2-btn-main js-wc-login"><i class="fa-solid fa-right-to-bracket"></i><?= t('Login to join') ?></button>
                    <?php endif; ?>
                    <a class="wc2-btn" href="<?= BASE_URL ?>/world-cup-predictions" data-wc-scroll="wc2-matches"><i class="fa-solid fa-calendar-days"></i><?= t('Match schedule') ?></a>
                </div>
            </div>

            <div class="wc2-stats-strip wc2-stats-strip-v5">
                <div class="wc2-stat-item">
                    <div class="wc2-stat-icon games"><i class="fa-solid fa-futbol"></i></div>
                    <div class="wc2-stat-text"><strong><?= (int)$totalMatches ?></strong><span><?= t('Matches') ?></span></div>
                </div>
                <div class="wc2-stat-item">
                    <div class="wc2-stat-icon points"><i class="fa-solid fa-star"></i></div>
                    <div class="wc2-stat-text"><strong><?= (int)$myPoints ?></strong><span><?= t('Your points') ?></span></div>
                </div>
                <div class="wc2-stat-item">
                    <div class="wc2-stat-icon prize"><i class="fa-solid fa-trophy"></i></div>
                    <div class="wc2-stat-text"><strong>Top 5</strong><span><?= t('Rewards') ?></span></div>
                </div>
            </div>
        </section>

        <?php if (!$isParticipant): ?>
            <div class="wc2-login-strip">
                <div><strong><?= t('Login required before participating') ?></strong><span><?= t('You can view all games now. To submit predictions, login without leaving this page.') ?></span></div>
                <button type="button" class="wc2-btn wc2-btn-main js-wc-login"><i class="fa-solid fa-right-to-bracket"></i><?= t('Open login') ?></button>
            </div>
        <?php endif; ?>

        <section class="wc2-prizes-section" id="wc2-prizes">
            <div class="wc2-prizes-section-head">
                <h2><i class="fa-solid fa-trophy" style="color:#ffd166;"></i><?= t('Prizes – Top 5') ?></h2>
                <p><?= t('The highest-scoring players at the end of the tournament win.') ?></p>
            </div>
            <div class="wc2-prizes-grid">
                <div class="wc2-prize-card wc2-prize-card--gold">
                    <div class="wc2-prize-rank-wrap">
                        <span class="wc2-prize-rank-num">1</span>
                    </div>
                    <div class="wc2-prize-rank-label"><?= t('1st place') ?></div>
                    <div class="wc2-prize-amount">
                        <img src="https://lolboost.gg/public/assets/core/main/img/coin.png" alt="LB Coin">
                        50 LB Coins
                    </div>
                    <p class="wc2-prize-desc"><?= t('= €50 Store Credits') ?></p>
                </div>
                <div class="wc2-prize-card wc2-prize-card--silver">
                    <div class="wc2-prize-rank-wrap">
                        <span class="wc2-prize-rank-num">2</span>
                    </div>
                    <div class="wc2-prize-rank-label"><?= t('2nd place') ?></div>
                    <div class="wc2-prize-amount">
                        <img src="https://lolboost.gg/public/assets/core/main/img/coin.png" alt="LB Coin">
                        30 LB Coins
                    </div>
                    <p class="wc2-prize-desc"><?= t('= €30 Store Credits') ?></p>
                </div>
                <div class="wc2-prize-card wc2-prize-card--bronze">
                    <div class="wc2-prize-rank-wrap">
                        <span class="wc2-prize-rank-num">3</span>
                    </div>
                    <div class="wc2-prize-rank-label"><?= t('3rd place') ?></div>
                    <div class="wc2-prize-amount">
                        <img src="https://lolboost.gg/public/assets/core/main/img/coin.png" alt="LB Coin">
                        20 LB Coins
                    </div>
                    <p class="wc2-prize-desc"><?= t('= €20 Store Credits') ?></p>
                </div>
                <div class="wc2-prize-card wc2-prize-card--4th">
                    <div class="wc2-prize-rank-wrap">
                        <span class="wc2-prize-rank-num">4</span>
                    </div>
                    <div class="wc2-prize-rank-label"><?= t('4th place') ?></div>
                    <div class="wc2-prize-amount">
                        <img src="https://lolboost.gg/public/assets/core/main/img/coin.png" alt="LB Coin">
                        10 LB Coins
                    </div>
                    <p class="wc2-prize-desc"><?= t('= €10 Store Credits') ?></p>
                </div>
                <div class="wc2-prize-card wc2-prize-card--5th">
                    <div class="wc2-prize-rank-wrap">
                        <span class="wc2-prize-rank-num">5</span>
                    </div>
                    <div class="wc2-prize-rank-label"><?= t('5th place') ?></div>
                    <div class="wc2-prize-amount">
                        <img src="https://lolboost.gg/public/assets/core/main/img/coin.png" alt="LB Coin">
                        5 LB Coins
                    </div>
                    <p class="wc2-prize-desc"><?= t('= €5 Store Credits') ?></p>
                </div>
            </div>
            <div class="wc2-prizes-note"><i class="fa-solid fa-circle-info"></i> <?= t('In case of a tie, the player with fewer predictions submitted wins. Prizes are awarded after the final match.') ?></div>
        </section>

        <section class="wc2-main" id="wc2-matches">
            <div>
                <div class="wc2-section-head">
                    <div>
                        <h2 id="wc2-matchday-title"><?= t('Matchday') ?> <?= $activeMatchday ?></h2>
                        <p><?= t('Tap a match to open the prediction slip.') ?></p>
                    </div>
                </div>
                <!-- Matchday Tabs -->
                <div class="wc2-matchday-tabs">
                    <button type="button" class="wc2-matchday-tab <?= $activeMatchday === 1 ? 'active' : '' ?>" data-matchday="1">
                        <?= t('Matchday 1') ?><small>11.06 – 18.06</small>
                    </button>
                    <button type="button" class="wc2-matchday-tab <?= $activeMatchday === 2 ? 'active' : '' ?>" data-matchday="2">
                        <?= t('Matchday 2') ?><small>18.06 – 24.06</small>
                    </button>
                    <button type="button" class="wc2-matchday-tab <?= $activeMatchday === 3 ? 'active' : '' ?>" data-matchday="3">
                        <?= t('Matchday 3') ?><small>24.06 – 28.06</small>
                    </button>
                </div>
                <div id="wc2-days-container" class="wc2-days">
                    <?php foreach ($groupedMatches as $day => $dayMatches): ?>
                        <div class="wc2-day">
                            <div class="wc2-day-label"><i class="fa-regular fa-calendar"></i><?= t(date('l', strtotime($day))) ?>, <?= htmlspecialchars(date('d', strtotime($day)), ENT_QUOTES) ?> <?= t(date('F', strtotime($day))) ?> <?= htmlspecialchars(date('Y', strtotime($day)), ENT_QUOTES) ?> <small><?= t('Local time') ?></small></div>
                            <?php foreach ($dayMatches as $match):
                                $mid = (int)$match['id'];
                                $pred = $predictions[$mid] ?? null;
                                $locked = (int)$match['is_locked'] === 1 || strtotime((string)$match['kickoff_at']) <= time();
                                $finished = (int)$match['is_finished'] === 1;
                                $home = (string)$match['home_team'];
                                $away = (string)$match['away_team'];
                                $predText = $pred ? ((int)$pred['home_score'] . ':' . (int)$pred['away_score']) : '—';
                                $kickoffIsoStr = $kickoffIso($match['kickoff_at']);
                                $homeFlagUrl = $flagUrl($home);
                                $awayFlagUrl = $flagUrl($away);
                                $homeInitials = $flagInitials($home);
                                $awayInitials = $flagInitials($away);
                            ?>
                                <article class="wc2-match" data-match-id="<?= $mid ?>" data-home="<?= htmlspecialchars($home, ENT_QUOTES) ?>" data-away="<?= htmlspecialchars($away, ENT_QUOTES) ?>" data-home-flag="<?= htmlspecialchars($homeFlagUrl, ENT_QUOTES) ?>" data-away-flag="<?= htmlspecialchars($awayFlagUrl, ENT_QUOTES) ?>" data-home-initials="<?= htmlspecialchars($homeInitials, ENT_QUOTES) ?>" data-away-initials="<?= htmlspecialchars($awayInitials, ENT_QUOTES) ?>" data-home-score="<?= htmlspecialchars((string)($pred['home_score'] ?? ''), ENT_QUOTES) ?>" data-away-score="<?= htmlspecialchars((string)($pred['away_score'] ?? ''), ENT_QUOTES) ?>" data-kickoff-iso="<?= htmlspecialchars($kickoffIsoStr, ENT_QUOTES) ?>" data-locked="<?= $locked ? '1' : '0' ?>">
                                    <div class="wc2-time"><strong class="js-local-time" data-kickoff-iso="<?= htmlspecialchars($kickoffIsoStr, ENT_QUOTES) ?>">--:--</strong><span class="js-local-date">...</span><em class="wc2-count-chip js-match-countdown"></em></div>
                                    <div>
                                        <div class="wc2-versus">
                                            <div class="wc2-team">
                                                <?php if ($homeFlagUrl !== ''): ?>
                                                    <img class="wc2-flag" src="<?= htmlspecialchars($homeFlagUrl, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($home, ENT_QUOTES) ?>">
                                                <?php else: ?>
                                                    <span class="wc2-flag wc2-flag-initials"><?= htmlspecialchars($homeInitials, ENT_QUOTES) ?></span>
                                                <?php endif; ?>
                                                <span class="wc2-team-name"><?= htmlspecialchars($home, ENT_QUOTES) ?></span>
                                            </div>
                                            <div class="wc2-vs">VS</div>
                                            <div class="wc2-team away">
                                                <span class="wc2-team-name"><?= htmlspecialchars($away, ENT_QUOTES) ?></span>
                                                <?php if ($awayFlagUrl !== ''): ?>
                                                    <img class="wc2-flag" src="<?= htmlspecialchars($awayFlagUrl, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($away, ENT_QUOTES) ?>">
                                                <?php else: ?>
                                                    <span class="wc2-flag wc2-flag-initials"><?= htmlspecialchars($awayInitials, ENT_QUOTES) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="wc2-meta">
                                            <span class="wc2-chip"><i class="fa-solid fa-users"></i><?= t('Group') ?> <?= htmlspecialchars((string)$match['group_name'], ENT_QUOTES) ?></span>
                                            <?php if ($locked): ?><span class="wc2-chip locked"><i class="fa-solid fa-lock"></i><?= t('Locked') ?></span><?php endif; ?>
                                            <?php if ($finished): ?><span class="wc2-chip"><i class="fa-solid fa-check"></i><?= t('Result') ?> <?= (int)$match['home_score'] ?>:<?= (int)$match['away_score'] ?>, <?= (int)($pred['points'] ?? 0) ?> pts</span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="wc2-pick">
                                        <div class="wc2-score"><?= htmlspecialchars($predText, ENT_QUOTES) ?></div><br>
                                        <?php if ($isParticipant && !$locked): ?>
                                            <button type="button" class="wc2-btn js-open-prediction"><i class="fa-solid fa-pen-to-square"></i><?= $pred ? t('Edit pick') : t('Pick score') ?></button>
                                        <?php elseif (!$isParticipant): ?>
                                            <button type="button" class="wc2-btn js-wc-login"><i class="fa-solid fa-lock"></i><?= t('Login') ?></button>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="wc2-sidebar">
                <div class="wc2-side-card">
                    <h3><?= t('Leaderboard') ?></h3>
                    <?php if (empty($leaderboard)): ?>
                        <p><?= t('No predictions yet. Be the first client on the leaderboard.') ?></p>
                    <?php else: ?>
                        <div class="wc2-board">
                        <?php foreach ($leaderboard as $i => $row):
                            $isBoosterRow = ($row['participant_type'] ?? 'client') === 'booster';
                            $displayName  = $isBoosterRow
                                ? htmlspecialchars($row['name'] ?? 'Booster', ENT_QUOTES)
                                : htmlspecialchars($maskClientName($row['name'] ?? ''), ENT_QUOTES);
                        ?>
                            <div class="wc2-board-row">
                                <span class="wc2-rank"><?= $i + 1 ?></span>
                                <span class="wc2-player">
                                    <img class="wc2-avatar" src="<?= htmlspecialchars($clientAvatarUrl($row['icon'] ?? '', $row['name'] ?? ''), ENT_QUOTES) ?>" alt="">
                                    <span class="wc2-name"><?= $displayName ?></span>
                                    <?php if ($isBoosterRow): ?>
                                        <span class="wc2-booster-badge"><?= t('Booster') ?></span>
                                    <?php endif; ?>
                                </span>
                                <strong class="wc2-points"><?= (int)$row['points'] ?> pts</strong>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <?php if ($totalParticipants > 20): ?>
                            <div class="wc2-more-participants">
                                <span>···</span>
                                <span><?= number_format($totalParticipants - 20) ?> more participant<?= ($totalParticipants - 20) !== 1 ? 's' : '' ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="wc2-side-card wc2-reward-card">
                    <div class="wc2-reward-head">
                        <div>
                            <span class="wc2-reward-kicker"><?= t('Participation bonus') ?></span>
                            <h3><?= t('Your reward') ?></h3>
                        </div>
                        <span class="wc2-reward-icon"><i class="fa-solid fa-gift"></i></span>
                    </div>

                    <?php if ($isClient): ?>
                        <div class="wc2-reward-progress">
                            <span><?= t('Submitted predictions') ?></span>
                            <strong><?= (int)$totalTips ?>/<?= (int)$totalMatches ?></strong>
                        </div>

                        <div id="wcRewardBox">
                            <?php if ($reward): ?>
                                <div class="wc2-reward-badge">
                                    <i class="fa-solid fa-bolt"></i>
                                    <span><?= t('Up to 70% Discount') ?></span>
                                </div>
                                <button type="button" class="wc2-code wc2-copy-code" id="wcRewardCode" data-copy-code="<?= htmlspecialchars((string)$reward['discount_code'], ENT_QUOTES) ?>" aria-label="<?= t('Copy discount code') ?>">
                                    <span><?= htmlspecialchars((string)$reward['discount_code'], ENT_QUOTES) ?></span>
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                                <div class="wc2-copy-hint" id="wcRewardCopyHint"><?= t('Click the code to copy it.') ?></div>
                            <?php else: ?>
                                <div class="wc2-reward-empty">
                                    <span class="wc2-reward-empty-icon"><i class="fa-solid fa-lock"></i></span>
                                    <p><?= t('Submit your first prediction to unlock your participation discount code.') ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="wc2-reward-empty">
                            <span class="wc2-reward-empty-icon"><i class="fa-solid fa-user-lock"></i></span>
                            <p><?= t('Login as client to submit predictions and unlock your participation discount code.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="wc2-side-card wc2-side-rules">
                    <h3><?= t('Point system') ?></h3>
                    <div class="wc2-pts-list">
                        <div class="wc2-pts-row">
                            <div class="wc2-pts-badge pts-5">5 pts</div>
                            <div class="wc2-pts-info">
                                <strong><?= t('Exact score') ?></strong>
                                <span><?= t('You predicted the exact final score.') ?></span>
                                <em><?= t('e.g. you tip 2:1 → result is 2:1') ?></em>
                            </div>
                        </div>
                        <div class="wc2-pts-row">
                            <div class="wc2-pts-badge pts-3">3 pts</div>
                            <div class="wc2-pts-info">
                                <strong><?= t('Winner + goal difference') ?></strong>
                                <span><?= t('Right winner and correct goal difference, but wrong score.') ?></span>
                                <em><?= t('e.g. you tip 3:1 → result is 2:0 (both win by 2)') ?></em>
                            </div>
                        </div>
                        <div class="wc2-pts-row">
                            <div class="wc2-pts-badge pts-2">2 pts</div>
                            <div class="wc2-pts-info">
                                <strong><?= t('Correct trend') ?></strong>
                                <span><?= t('Right winner or draw, but different goal difference.') ?></span>
                                <em><?= t('e.g. you tip 2:0 → result is 3:1') ?></em>
                            </div>
                        </div>
                        <div class="wc2-pts-row">
                            <div class="wc2-pts-badge pts-0">0 pts</div>
                            <div class="wc2-pts-info">
                                <strong><?= t('Wrong prediction') ?></strong>
                                <span><?= t('Wrong winner or draw predicted.') ?></span>
                                <em><?= t('e.g. you tip 2:0 → result is 0:1') ?></em>
                            </div>
                        </div>
                    </div>
                    <a class="wc2-btn" style="margin-top:14px;" href="<?= BASE_URL ?>/world-cup-predictions" data-wc-scroll="wc2-prizes"><i class="fa-solid fa-gift"></i><?= t('View prizes') ?></a>
                </div>
                <div class="wc2-side-card wc2-side-prizes">
                    <h3><i class="fa-solid fa-trophy" style="color:#ffd166;margin-right:8px;"></i><?= t('Prizes') ?></h3>
                    <div class="wc2-prizes-mini">
                        <div class="wc2-prize-mini-row wc2-prize-gold">
                            <span class="wc2-prize-medal">🥇</span>
                            <span class="wc2-prize-place"><?= t('1st place') ?></span>
                            <strong class="wc2-prize-val">50 LB Coins</strong>
                        </div>
                        <div class="wc2-prize-mini-row wc2-prize-silver">
                            <span class="wc2-prize-medal">🥈</span>
                            <span class="wc2-prize-place"><?= t('2nd place') ?></span>
                            <strong class="wc2-prize-val">30 LB Coins</strong>
                        </div>
                        <div class="wc2-prize-mini-row wc2-prize-bronze">
                            <span class="wc2-prize-medal">🥉</span>
                            <span class="wc2-prize-place"><?= t('3rd place') ?></span>
                            <strong class="wc2-prize-val">20 LB Coins</strong>
                        </div>
                        <div class="wc2-prize-mini-row">
                            <span class="wc2-prize-medal">4️⃣</span>
                            <span class="wc2-prize-place"><?= t('4th place') ?></span>
                            <strong class="wc2-prize-val">10 LB Coins</strong>
                        </div>
                        <div class="wc2-prize-mini-row">
                            <span class="wc2-prize-medal">5️⃣</span>
                            <span class="wc2-prize-place"><?= t('5th place') ?></span>
                            <strong class="wc2-prize-val">5 LB Coins</strong>
                        </div>
                    </div>
                    <a class="wc2-btn" style="margin-top:14px;width:100%;justify-content:center;" href="<?= BASE_URL ?>/world-cup-predictions" data-wc-scroll="wc2-prizes"><i class="fa-solid fa-arrow-down"></i><?= t('See full prizes') ?></a>
                </div>
            </aside>
        </section>

    </div>
</div>

<div class="wc2-modal-backdrop" id="wcPredictionModal" aria-hidden="true">
    <div class="wc2-modal" role="dialog" aria-modal="true">
        <div class="wc2-modal-head">
            <h3><?= t('Prediction slip') ?></h3>
            <button type="button" class="wc2-close js-close-wc-modal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="wcPredictionForm" class="wc2-modal-body">
            <input type="hidden" name="match_id" id="wcMatchId">
            <div class="wc2-modal-meta">
                <span id="wcMatchCounter">1 / 24</span>
                <div class="wc2-modal-nav">
                    <button type="button" class="wc2-nav-btn js-prev-match" aria-label="Previous match"><i class="fa-solid fa-chevron-left"></i></button>
                    <button type="button" class="wc2-nav-btn js-next-match" aria-label="Next match"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="wc2-slip">
                <div class="wc2-slip-team"><img id="wcHomeFlag" src="" alt=""><strong id="wcHomeName"></strong></div>
                <div class="wc2-scorebox">
                    <div class="wc2-scoreline">
                        <div class="wc2-score-control"><input type="number" inputmode="numeric" pattern="[0-9]*" min="0" max="30" name="home_score" id="wcHomeScore" required><button type="button" class="wc2-score-spin up" data-target="home" data-step="1"><i class="fa-solid fa-chevron-up"></i></button><button type="button" class="wc2-score-spin down" data-target="home" data-step="-1"><i class="fa-solid fa-chevron-down"></i></button></div>
                        <span>:</span>
                        <div class="wc2-score-control"><input type="number" inputmode="numeric" pattern="[0-9]*" min="0" max="30" name="away_score" id="wcAwayScore" required><button type="button" class="wc2-score-spin up" data-target="away" data-step="1"><i class="fa-solid fa-chevron-up"></i></button><button type="button" class="wc2-score-spin down" data-target="away" data-step="-1"><i class="fa-solid fa-chevron-down"></i></button></div>
                    </div>
                </div>
                <div class="wc2-slip-team"><img id="wcAwayFlag" src="" alt=""><strong id="wcAwayName"></strong></div>
            </div>
            <label id="wcHomeLabel" hidden></label><label id="wcAwayLabel" hidden></label>
            <div class="wc2-quick">
                <div class="wc2-quick-label"><?= t('Quick picks') ?></div>
                <div class="wc2-quick-grid">
                    <button type="button" data-score="1:0">1:0</button><button type="button" data-score="2:0">2:0</button><button type="button" data-score="2:1">2:1</button><button type="button" data-score="1:1">1:1</button><button type="button" data-score="0:0">0:0</button><button type="button" data-score="0:1">0:1</button><button type="button" data-score="0:2">0:2</button><button type="button" data-score="1:2">1:2</button><button type="button" data-score="3:1">3:1</button><button type="button" data-score="1:3">1:3</button>
                </div>
            </div>
            <div class="wc2-modal-actions">
                <button type="button" class="wc2-btn js-close-wc-modal"><?= t('Cancel') ?></button>
                <button type="button" class="wc2-btn js-next-match"><i class="fa-solid fa-arrow-right"></i><?= t('Next match') ?></button>
                <button type="submit" class="wc2-btn wc2-btn-main"><i class="fa-solid fa-floppy-disk"></i><?= t('Save') ?></button>
                <button type="button" class="wc2-btn wc2-save-next js-save-next"><i class="fa-solid fa-floppy-disk"></i><?= t('Save & next') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="wc2-toast" id="wcToast"></div>

<?php $this->start('scripts') ?>
<script>
(function(){
    const isClient = <?= $isParticipant ? 'true' : 'false' ?>;
    const modal = document.getElementById('wcPredictionModal');
    const form = document.getElementById('wcPredictionForm');
    const toast = document.getElementById('wcToast');
    const homeInput = document.getElementById('wcHomeScore');
    const awayInput = document.getElementById('wcAwayScore');
    const matchCounter = document.getElementById('wcMatchCounter');
    let matchCards = Array.from(document.querySelectorAll('.wc2-match')).filter(card => card.dataset.locked !== '1');
    let activeIndex = 0;
    if (modal && modal.parentElement !== document.body) { document.body.appendChild(modal); }

    /* ── Matchday AJAX switcher ─────────────────────────────── */
    let currentMatchday = <?= $activeMatchday ?>;

    async function loadMatchday(day) {
        if (day === currentMatchday) return;
        const container = document.getElementById('wc2-days-container');
        const titleEl   = document.getElementById('wc2-matchday-title');
        if (!container) return;

        // Update tabs
        document.querySelectorAll('.wc2-matchday-tab').forEach(tab => {
            tab.classList.toggle('active', parseInt(tab.dataset.matchday) === day);
        });

        // Show skeleton
        container.classList.add('wc2-loading');
        container.innerHTML = '<div class="wc2-skeleton">'
            + '<div class="wc2-skeleton-row"></div>'.repeat(4)
            + '</div>';

        if (titleEl) titleEl.textContent = '<?= t('Matchday') ?> ' + day;

        try {
            const fd = new FormData();
            fd.append('action', 'worldcup_load_matchday');
            fd.append('matchday', day);
            const res  = await fetch('<?= BASE_URL ?>/ajax', { method: 'POST', body: fd, credentials: 'same-origin' });
            const data = await res.json();

            if (data.success && data.html) {
                container.innerHTML = data.html;
                currentMatchday = day;
                // Re-bind match cards for modal
                matchCards = Array.from(container.querySelectorAll('.wc2-match')).filter(c => c.dataset.locked !== '1');
                bindMatchCards();
                // Re-run time formatting
                window.setTimeout(() => { wcFormatLocalTimes && wcFormatLocalTimes(); }, 10);
            } else {
                container.innerHTML = '<p style="color:rgba(235,238,255,.5);padding:20px 0;"><?= t('No matches found for this matchday.') ?></p>';
            }
        } catch(e) {
            container.innerHTML = '<p style="color:rgba(235,238,255,.5);padding:20px 0;"><?= t('Failed to load matches.') ?></p>';
        }
        container.classList.remove('wc2-loading');
    }

    document.querySelectorAll('.wc2-matchday-tab').forEach(tab => {
        tab.addEventListener('click', () => loadMatchday(parseInt(tab.dataset.matchday)));
    });

    function showToast(message){
        if (!toast) return alert(message);
        toast.textContent = message;
        toast.classList.add('show');
        window.setTimeout(() => toast.classList.remove('show'), 2600);
    }

    function wcSetLoginReturnUrl(){
        const currentUrl = window.location.href;
        try { sessionStorage.setItem('wc2_after_login_url', currentUrl); localStorage.setItem('wc2_after_login_url', currentUrl); } catch(e) {}
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const actionInput = form.querySelector('input[name="action"]');
            const actionValue = actionInput ? String(actionInput.value || '') : '';
            const hasEmailPassword = !!(form.querySelector('input[name="email"], input[type="email"], input[name="login"]') && form.querySelector('input[name="password"], input[type="password"]'));
            const looksLikeClientLogin = (actionValue === 'auth_client_login' || actionValue === 'auth_unified_login') || form.id === 'clientLoginForm' || form.classList.contains('client-login-form') || hasEmailPassword;
            if (!looksLikeClientLogin) return;
            ['redirectUrl','redirect_after_login','redirect_url','return_url','returnUrl','back_url','current_url','stay_on_page'].forEach(name => {
                let input = form.querySelector('input[name="' + name + '"]');
                if (!input) { input = document.createElement('input'); input.type = 'hidden'; input.name = name; form.appendChild(input); }
                input.value = currentUrl;
            });
            form.dataset.redirectUrl = currentUrl;
            form.dataset.redirect = currentUrl;
            form.setAttribute('data-redirect-url', currentUrl);
        });
    }

    function openLogin(){
        wcSetLoginReturnUrl();
        const headerBtn = document.getElementById('login-btn') || document.getElementById('login-btn-mobile-header') || document.querySelector('[data-bs-target="#login_modal"], [data-target="#login_modal"], .login-btn, .js-login-btn');
        if (headerBtn) { headerBtn.click(); window.setTimeout(wcSetLoginReturnUrl, 80); window.setTimeout(wcSetLoginReturnUrl, 300); return; }
        const loginModal = document.getElementById('login_modal') || document.getElementById('loginModal');
        if (loginModal) { loginModal.classList.add('show','active','is-open'); loginModal.style.display = 'block'; document.body.classList.add('modal-open','auth-modal-open','login-modal-open'); window.setTimeout(wcSetLoginReturnUrl, 80); return; }
        window.location.href = '<?= BASE_URL ?>/login?redirectUrl=' + encodeURIComponent(window.location.href);
    }

    wcSetLoginReturnUrl();
    document.addEventListener('focusin', wcSetLoginReturnUrl, true);
    document.addEventListener('click', function(e){
        if (e.target && e.target.closest && e.target.closest('form')) wcSetLoginReturnUrl();
    }, true);
    document.addEventListener('submit', function(e){
        const loginForm = e.target;
        if (!(loginForm instanceof HTMLFormElement)) return;
        const actionInput = loginForm.querySelector('input[name="action"]');
        const hasEmailPassword = !!(loginForm.querySelector('input[name="email"], input[type="email"], input[name="login"]') && loginForm.querySelector('input[name="password"], input[type="password"]'));
        if ((actionInput && (actionInput.value === 'auth_client_login' || actionInput.value === 'auth_unified_login')) || hasEmailPassword) wcSetLoginReturnUrl();
    }, true);

    document.querySelectorAll('.js-wc-login').forEach(btn => btn.addEventListener('click', openLogin));

    document.querySelectorAll('[data-wc-scroll]').forEach(link => link.addEventListener('click', function(e){
        e.preventDefault();
        const target = document.getElementById(this.dataset.wcScroll || '');
        if (!target) return;
        target.scrollIntoView({behavior:'smooth', block:'start'});
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, document.title, window.location.pathname + window.location.search);
        }
    }));

    function setFlagOrInitials(imgEl, flagUrl, initials) {
        if (flagUrl) {
            imgEl.style.display = '';
            imgEl.src = flagUrl;
            imgEl.nextElementSibling && imgEl.nextElementSibling.classList.contains('wc2-flag-initials-modal') && (imgEl.nextElementSibling.style.display = 'none');
        } else {
            imgEl.style.display = 'none';
            let span = imgEl.nextElementSibling;
            if (!span || !span.classList.contains('wc2-flag-initials-modal')) {
                span = document.createElement('span');
                span.className = 'wc2-flag wc2-flag-initials wc2-flag-initials-modal';
                imgEl.parentNode.insertBefore(span, imgEl.nextSibling);
            }
            span.textContent = initials;
            span.style.display = '';
        }
    }

    function fillModalFromCard(card){
        if (!card) return;
        activeIndex = Math.max(0, matchCards.indexOf(card));
        document.getElementById('wcMatchId').value = card.dataset.matchId || '';
        document.getElementById('wcHomeName').textContent = card.dataset.home || '';
        document.getElementById('wcAwayName').textContent = card.dataset.away || '';
        document.getElementById('wcHomeLabel').textContent = card.dataset.home || '';
        document.getElementById('wcAwayLabel').textContent = card.dataset.away || '';
        setFlagOrInitials(document.getElementById('wcHomeFlag'), card.dataset.homeFlag || '', card.dataset.homeInitials || '??');
        setFlagOrInitials(document.getElementById('wcAwayFlag'), card.dataset.awayFlag || '', card.dataset.awayInitials || '??');
        homeInput.value = card.dataset.homeScore || '';
        awayInput.value = card.dataset.awayScore || '';
        if (matchCounter) matchCounter.textContent = (activeIndex + 1) + ' / ' + matchCards.length;
        document.querySelectorAll('.js-prev-match').forEach(btn => btn.disabled = activeIndex <= 0);
        document.querySelectorAll('.js-next-match').forEach(btn => btn.disabled = activeIndex >= matchCards.length - 1);
    }

    function openPrediction(card){
        if (!isClient) return openLogin();
        if (!card || card.dataset.locked === '1') return showToast('<?= t('This match is already locked.') ?>');
        fillModalFromCard(card);
        modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false');
        document.body.classList.add('wc2-modal-open');
        if (window.matchMedia('(min-width: 721px)').matches) { window.setTimeout(() => homeInput.focus(), 60); }
    }

    function bindMatchCards() {
        document.querySelectorAll('.js-open-prediction').forEach(btn => {
            btn.addEventListener('click', function(){ openPrediction(this.closest('.wc2-match')); });
        });
        document.querySelectorAll('.js-wc-login').forEach(btn => btn.addEventListener('click', openLogin));
    }

    document.querySelectorAll('.js-open-prediction').forEach(btn => {
        btn.addEventListener('click', function(){ openPrediction(this.closest('.wc2-match')); });
    });

    function closeModal(){ modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); document.body.classList.remove('wc2-modal-open'); }
    document.querySelectorAll('.js-close-wc-modal').forEach(btn => btn.addEventListener('click', closeModal));
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal?.classList.contains('is-open')) closeModal(); });

    document.querySelectorAll('.wc2-score-spin, .wc2-step button').forEach(btn => btn.addEventListener('click', function(){
        const input = this.dataset.target === 'home' ? homeInput : awayInput;
        const current = parseInt(input.value || '0', 10);
        const next = Math.max(0, Math.min(30, current + parseInt(this.dataset.step || '0', 10)));
        input.value = String(next);
    }));

    document.querySelectorAll('.wc2-quick button[data-score]').forEach(btn => btn.addEventListener('click', function(){
        const parts = String(this.dataset.score || '0:0').split(':');
        homeInput.value = parts[0] || '0';
        awayInput.value = parts[1] || '0';
    }));



    function goToMatch(delta){
        const nextIndex = activeIndex + delta;
        if (nextIndex < 0 || nextIndex >= matchCards.length) return;
        fillModalFromCard(matchCards[nextIndex]);
        if (window.matchMedia('(min-width: 721px)').matches) { window.setTimeout(() => homeInput.focus(), 30); }
    }
    document.querySelectorAll('.js-prev-match').forEach(btn => btn.addEventListener('click', () => goToMatch(-1)));
    document.querySelectorAll('.js-next-match').forEach(btn => btn.addEventListener('click', () => goToMatch(1)));

    async function saveCurrentPrediction(goNext){
        if (!isClient) { openLogin(); return; }
        const matchId = document.getElementById('wcMatchId').value;
        const fd = new FormData();
        fd.append('action', 'worldcup_save_predictions');
        fd.append('predictions[' + matchId + '][home]', homeInput.value);
        fd.append('predictions[' + matchId + '][away]', awayInput.value);
        const buttons = form.querySelectorAll('button[type="submit"], .js-save-next');
        buttons.forEach(btn => btn.disabled = true);
        try {
            const res = await fetch('<?= BASE_URL ?>/ajax', {method:'POST', body:fd, credentials:'same-origin'});
            const data = await res.json();
            if (data.success) {
                const card = matchCards[activeIndex];
                if (card) {
                    card.dataset.homeScore = homeInput.value;
                    card.dataset.awayScore = awayInput.value;
                    const score = card.querySelector('.wc2-score');
                    if (score) score.textContent = homeInput.value + ':' + awayInput.value;
                    const btn = card.querySelector('.js-open-prediction');
                    if (btn) btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i><?= t('Edit pick') ?>';
                }
                if (data.reward_code) {
                    const rewardBox = document.getElementById('wcRewardBox');
                    if (rewardBox) {
                        const rewardLabel = data.reward_label || 'Up to 70% Discount';
                        rewardBox.innerHTML = '<div class="wc2-reward-badge"><i class="fa-solid fa-bolt"></i><span></span></div><button type="button" class="wc2-code wc2-copy-code" id="wcRewardCode" aria-label="Copy discount code"><span></span><i class="fa-regular fa-copy"></i></button><div class="wc2-copy-hint" id="wcRewardCopyHint">Click the code to copy it.</div>';
                        const rewardBadgeText = rewardBox.querySelector('.wc2-reward-badge span');
                        if (rewardBadgeText) rewardBadgeText.textContent = rewardLabel;
                        const rewardCode = document.getElementById('wcRewardCode');
                        if (rewardCode) {
                            rewardCode.dataset.copyCode = data.reward_code;
                            const rewardCodeText = rewardCode.querySelector('span');
                            if (rewardCodeText) rewardCodeText.textContent = data.reward_code;
                        }
                    }
                }
                showToast(data.message || '<?= t('Prediction saved.') ?>');
                if (goNext && activeIndex < matchCards.length - 1) goToMatch(1);
                else if (!goNext) closeModal();
            } else { showToast(data.message || '<?= t('Could not save prediction.') ?>'); }
        } catch(err) { showToast('<?= t('Could not save prediction.') ?>'); }
        finally { buttons.forEach(btn => btn.disabled = false); }
    }
    document.querySelector('.js-save-next')?.addEventListener('click', () => saveCurrentPrediction(true));

    form?.addEventListener('submit', async function(e){
        e.preventDefault();
        await saveCurrentPrediction(false);
    });


    const wcI18n = {
        locale: navigator.language || 'en-US',
        kickoffTitle: <?= json_encode(t('Kickoff reference: Berlin time, shown in your timezone'), JSON_UNESCAPED_UNICODE) ?>,
        timezoneNote: <?= json_encode(t('All times shown in your local timezone:'), JSON_UNESCAPED_UNICODE) ?>,
        locked: <?= json_encode(t('Locked'), JSON_UNESCAPED_UNICODE) ?>,
        months: [<?= implode(',', array_map(fn($m) => json_encode(t($m), JSON_UNESCAPED_UNICODE), ['January','February','March','April','May','June','July','August','September','October','November','December'])) ?>],
        daysLong: [<?= implode(',', array_map(fn($d) => json_encode(t($d), JSON_UNESCAPED_UNICODE), ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])) ?>],
        daysShort: [<?= implode(',', array_map(fn($d) => json_encode(t($d), JSON_UNESCAPED_UNICODE), ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'])) ?>]
    };

    function wcPad2(value){ return String(value).padStart(2, '0'); }
    function wcFormatCardDate(d){ return wcI18n.daysShort[d.getDay()] + ' ' + wcPad2(d.getDate()) + '.' + wcPad2(d.getMonth() + 1); }
    function wcFormatDayLabel(d){ return wcI18n.daysLong[d.getDay()] + ', ' + wcPad2(d.getDate()) + ' ' + wcI18n.months[d.getMonth()] + ' ' + d.getFullYear(); }

    // Returns {year, month (0-based), day, hours, minutes} in a given IANA timezone
    function wcPartsInTz(date, timeZone) {
        try {
            const f = new Intl.DateTimeFormat('en-US', {
                timeZone, year:'numeric', month:'numeric', day:'numeric',
                hour:'numeric', minute:'numeric', hour12: false
            });
            const parts = f.formatToParts(date);
            const get = type => parseInt((parts.find(p => p.type === type) || {value:'0'}).value, 10);
            return {
                year: get('year'), month: get('month') - 1, day: get('day'),
                hours: get('hour') % 24, minutes: get('minute'),
                weekday: new Date(get('year'), get('month') - 1, get('day')).getDay()
            };
        } catch(e) {
            return { year: date.getFullYear(), month: date.getMonth(), day: date.getDate(),
                     hours: date.getHours(), minutes: date.getMinutes(), weekday: date.getDay() };
        }
    }

    function wcGetUserTimeContext(){
        const locale = wcI18n.locale;
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
        let tzShort = '';
        let tzOffset = '';
        try {
            const parts = new Intl.DateTimeFormat(locale, {timeZoneName:'short', hour:'2-digit', minute:'2-digit', hour12:false, hourCycle:'h23'}).formatToParts(new Date());
            tzShort = (parts.find(p => p.type === 'timeZoneName') || {}).value || '';
        } catch(e) {}
        try {
            // Get UTC offset string like "UTC+2" or "UTC-5"
            const off = -new Date().getTimezoneOffset();
            const sign = off >= 0 ? '+' : '-';
            const h = String(Math.floor(Math.abs(off) / 60)).padStart(2, '0');
            const m = String(Math.abs(off) % 60).padStart(2, '0');
            tzOffset = 'UTC' + sign + h + ':' + m;
        } catch(e) {}
        return {locale, timeZone, tzShort, tzOffset};
    }

    function wcFormatLocalTimes(){
        const ctx = wcGetUserTimeContext();
        document.querySelectorAll('.wc2-match[data-kickoff-iso]').forEach(card => {
            const iso = card.dataset.kickoffIso || '';
            if (!iso) return;
            const d = new Date(iso);
            if (Number.isNaN(d.getTime())) return;
            const timeEl = card.querySelector('.js-local-time');
            const dateEl = card.querySelector('.js-local-date');
            if (timeEl) {
                timeEl.textContent = d.toLocaleTimeString(ctx.locale, {
                    hour: '2-digit', minute: '2-digit', hour12: false, hourCycle: 'h23',
                    timeZone: ctx.timeZone
                });
                timeEl.title = wcI18n.kickoffTitle + ': ' + ctx.timeZone;
            }
            if (dateEl) {
                const p = wcPartsInTz(d, ctx.timeZone);
                const fakeD = { getDay: () => p.weekday, getDate: () => p.day, getMonth: () => p.month, getFullYear: () => p.year };
                const datePart = wcFormatCardDate(fakeD);
                const tzLabel = ctx.tzShort || ctx.tzOffset || ctx.timeZone;
                dateEl.textContent = datePart + (tzLabel ? ' · ' + tzLabel : '');
            }
        });

        document.querySelectorAll('.wc2-day-label').forEach(function(label){
            const firstCard = label.closest('.wc2-day') && label.closest('.wc2-day').querySelector('.wc2-match[data-kickoff-iso]');
            if (!firstCard) return;
            const d = new Date(firstCard.dataset.kickoffIso || '');
            if (Number.isNaN(d.getTime())) return;
            const p = wcPartsInTz(d, ctx.timeZone);
            const fakeD = { getDay: () => p.weekday, getDate: () => p.day, getMonth: () => p.month, getFullYear: () => p.year };
            const dayStr = wcFormatDayLabel(fakeD);
            const icon = label.querySelector('i');
            const small = label.querySelector('small');
            label.textContent = '';
            if (icon) label.appendChild(icon);
            label.appendChild(document.createTextNode(' ' + dayStr + ' '));
            if (small) label.appendChild(small);
        });

        // Update hero note with user's actual timezone
        document.querySelectorAll('.wc2-local-note-v4').forEach(note => {
            const tzLabel = ctx.tzShort || ctx.tzOffset || ctx.timeZone;
            note.textContent = wcI18n.timezoneNote + ' ' + tzLabel + ' (' + ctx.timeZone + ')';
        });
    }

    function wcCountdownParts(ms){
        ms = Math.max(0, ms);
        const days = Math.floor(ms / 86400000);
        const hours = Math.floor((ms % 86400000) / 3600000);
        const minutes = Math.floor((ms % 3600000) / 60000);
        const seconds = Math.floor((ms % 60000) / 1000);
        return {days, hours, minutes, seconds};
    }

    function wcFindNextKickoff(){
        const future = Array.from(document.querySelectorAll('.wc2-match[data-kickoff-iso]'))
            .map(card => new Date(card.dataset.kickoffIso || '').getTime())
            .filter(ts => Number.isFinite(ts) && ts > Date.now())
            .sort((a,b) => a - b);
        return future.length ? future[0] : null;
    }

    function wcUpdateCountdowns(){
        const now = Date.now();
        const main = document.getElementById('wcCountdown');
        if (main) {
            let nextTs = main.dataset.nextKickoff ? new Date(main.dataset.nextKickoff).getTime() : NaN;
            if (!Number.isFinite(nextTs) || nextTs <= now) nextTs = wcFindNextKickoff();
            const set = (u,v) => { const el = main.querySelector('[data-unit="'+u+'"]'); if (el) el.textContent = String(v).padStart(2,'0'); };
            if (nextTs) {
                const p = wcCountdownParts(nextTs - now);
                set('days', p.days); set('hours', p.hours); set('minutes', p.minutes); set('seconds', p.seconds);
            } else {
                set('days', 0); set('hours', 0); set('minutes', 0); set('seconds', 0);
            }
        }
        document.querySelectorAll('.wc2-match[data-kickoff-iso]').forEach(card => {
            const chip = card.querySelector('.js-match-countdown');
            if (!chip) return;
            const kickoffTs = new Date(card.dataset.kickoffIso || '').getTime();
            const diff = kickoffTs - now;
            if (!Number.isFinite(diff)) { chip.textContent = ''; return; }
            if (diff <= 0) { chip.textContent = wcI18n.locked; return; }
            const p = wcCountdownParts(diff);
            chip.textContent = p.days > 0 ? (p.days + 'd ' + p.hours + 'h') : (p.hours + 'h ' + p.minutes + 'm ' + p.seconds + 's');
        });
    }
    wcFormatLocalTimes();
    wcUpdateCountdowns();
    window.setInterval(wcUpdateCountdowns, 1000);

})();

    document.addEventListener('click', function (event) {
        const copyButton = event.target.closest('.wc2-copy-code');
        if (!copyButton) return;

        const code = copyButton.dataset.copyCode || copyButton.textContent.trim();
        if (!code) return;

        const hint = document.getElementById('wcRewardCopyHint');
        const icon = copyButton.querySelector('i');

        const markCopied = function () {
            copyButton.classList.add('is-copied');
            if (hint) {
                hint.textContent = 'Copied to clipboard.';
                hint.classList.add('is-copied');
            }
            if (icon) {
                icon.className = 'fa-solid fa-check';
            }
            showToast('Discount code copied.');
            setTimeout(function () {
                copyButton.classList.remove('is-copied');
                if (hint) {
                    hint.textContent = 'Click the code to copy it.';
                    hint.classList.remove('is-copied');
                }
                if (icon) {
                    icon.className = 'fa-regular fa-copy';
                }
            }, 1600);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(markCopied).catch(function () {
                const textarea = document.createElement('textarea');
                textarea.value = code;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try { document.execCommand('copy'); markCopied(); } catch (e) {}
                textarea.remove();
            });
            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = code;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try { document.execCommand('copy'); markCopied(); } catch (e) {}
        textarea.remove();
    });

</script>
<?php $this->stop() ?>

<style>
/* Ricardo update: hero wrapper background and outer border removed */
.wc2-hero-v4{
    background:transparent!important;
    border:0!important;
    box-shadow:none!important;
}
.wc2-hero-v4::before,
.wc2-hero-v4::after{
    display:none!important;
    content:none!important;
}


/* Ricardo update: lower info area redesigned to match the new full width hero */
.wc2-stats-strip{
    width:min(1320px,calc(100vw - 64px))!important;
    margin:42px auto 0!important;
    display:grid!important;
    grid-template-columns:repeat(3,minmax(0,1fr))!important;
    gap:14px!important;
    padding:0!important;
    border:0!important;
    border-radius:0!important;
    background:transparent!important;
    backdrop-filter:none!important;
    overflow:visible!important;
}
.wc2-stat-item{
    position:relative!important;
    min-height:86px!important;
    padding:20px 24px!important;
    border:1px solid rgba(125,220,255,.11)!important;
    border-radius:24px!important;
    background:linear-gradient(145deg,rgba(13,19,43,.78),rgba(4,10,24,.54))!important;
    box-shadow:0 18px 50px rgba(0,0,0,.18),inset 0 1px 0 rgba(255,255,255,.04)!important;
    overflow:hidden!important;
}
.wc2-stat-item::before{
    content:""!important;
    position:absolute!important;
    inset:0!important;
    background:radial-gradient(circle at 18% 20%,rgba(98,92,255,.18),transparent 44%),radial-gradient(circle at 92% 0%,rgba(49,212,255,.12),transparent 42%)!important;
    pointer-events:none!important;
}
.wc2-stat-item > *{position:relative!important;z-index:1!important;}
.wc2-stat-icon{
    width:46px!important;
    height:46px!important;
    border-radius:16px!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.10),0 12px 26px rgba(0,0,0,.24)!important;
}
.wc2-stat-text strong{font-size:26px!important;letter-spacing:-.03em!important;}
.wc2-stat-text span{font-size:11px!important;color:rgba(205,216,255,.58)!important;}

.wc2-info-row{
    width:min(1320px,calc(100vw - 64px))!important;
    margin:16px auto 56px!important;
    display:grid!important;
    grid-template-columns:1.04fr .96fr!important;
    gap:18px!important;
    border:0!important;
    border-radius:0!important;
    background:transparent!important;
    backdrop-filter:none!important;
    overflow:visible!important;
}
.wc2-rules-col,
.wc2-prizes-col{
    position:relative!important;
    min-height:250px!important;
    padding:28px!important;
    border:1px solid rgba(125,220,255,.12)!important;
    border-radius:28px!important;
    background:linear-gradient(145deg,rgba(12,18,42,.82),rgba(4,10,24,.58))!important;
    box-shadow:0 22px 60px rgba(0,0,0,.20),inset 0 1px 0 rgba(255,255,255,.05)!important;
    overflow:hidden!important;
}
.wc2-rules-col{border-right:1px solid rgba(125,220,255,.12)!important;}
.wc2-rules-col::before,
.wc2-prizes-col::before{
    content:""!important;
    position:absolute!important;
    inset:0!important;
    background:radial-gradient(circle at 0% 0%,rgba(98,92,255,.18),transparent 38%),radial-gradient(circle at 92% 15%,rgba(49,212,255,.10),transparent 42%)!important;
    pointer-events:none!important;
}
.wc2-rules-col > *,
.wc2-prizes-col > *{position:relative!important;z-index:1!important;}
.wc2-col-heading{
    margin-bottom:24px!important;
    color:#75e6ff!important;
}
.wc2-col-heading i{
    width:38px!important;
    height:38px!important;
    border-radius:14px!important;
    background:linear-gradient(135deg,rgba(98,92,255,.28),rgba(49,212,255,.13))!important;
    color:#9eeeff!important;
}
.wc2-rules-line{
    display:grid!important;
    grid-template-columns:repeat(4,minmax(0,1fr))!important;
    gap:12px!important;
}
.wc2-rule-node{
    min-height:132px!important;
    padding:18px 16px!important;
    border:1px solid rgba(255,255,255,.075)!important;
    border-radius:20px!important;
    background:rgba(255,255,255,.035)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
    overflow:hidden!important;
}
.wc2-rule-node::after{
    height:3px!important;
    left:16px!important;
    right:16px!important;
    top:0!important;
    border-radius:999px!important;
}
.wc2-rule-node:last-child{border-right:1px solid rgba(255,255,255,.075)!important;}
.wc2-rule-pts{font-size:30px!important;margin-bottom:10px!important;}
.wc2-rule-name{font-size:13px!important;color:rgba(255,255,255,.91)!important;margin-bottom:8px!important;}
.wc2-rule-desc{font-size:12px!important;color:rgba(205,216,255,.55)!important;}

.wc2-podium{
    grid-template-columns:1fr 1.08fr 1fr!important;
    gap:12px!important;
}
.wc2-podium-place{
    min-height:124px!important;
    padding:18px!important;
    border-radius:22px!important;
    background:rgba(255,255,255,.045)!important;
    border:1px solid rgba(255,255,255,.08)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04)!important;
}
.wc2-podium-place.gold{
    min-height:150px!important;
    background:linear-gradient(160deg,rgba(255,192,64,.16),rgba(98,92,255,.12),rgba(49,212,255,.06))!important;
    border-color:rgba(255,200,80,.30)!important;
    box-shadow:0 16px 42px rgba(255,190,50,.08),inset 0 1px 0 rgba(255,255,255,.07)!important;
}
.wc2-podium-badge{
    width:34px!important;
    height:34px!important;
    border-radius:12px!important;
    margin-bottom:14px!important;
}
.wc2-podium-strong{font-size:14px!important;line-height:1.25!important;}
.wc2-podium-sub{font-size:11px!important;color:rgba(205,216,255,.50)!important;}
.wc2-podium-mini{
    display:grid!important;
    grid-template-columns:1fr 1fr!important;
    gap:12px!important;
    margin-top:12px!important;
}
.wc2-podium-mini article{
    min-height:54px!important;
    border-radius:18px!important;
    background:rgba(255,255,255,.035)!important;
    border:1px solid rgba(255,255,255,.075)!important;
}
/* ── Timezone pill shown in match time cell ───────────────────── */
.js-local-date{
    display:block!important;
    margin-top:6px!important;
    font-size:11px!important;
    color:rgba(200,215,255,.55)!important;
    font-weight:700!important;
    letter-spacing:.01em!important;
}
/* Loading shimmer while JS hasn't run yet */
.js-local-time[data-kickoff-iso]{
    min-width:54px!important;
    display:inline-block!important;
}
/* Subtle accent on match time block */
.wc2-time strong{
    color:#e8f4ff!important;
    letter-spacing:-.02em!important;
}
/* Slightly soften the match card left bar */
.wc2-match:before{
    opacity:.65!important;
}
/* Countdown card: sharper number color */
.wc2-cd-num{
    background:linear-gradient(135deg,#fff 0%,#7ee8ff 100%)!important;
    background-clip:text!important;
    -webkit-background-clip:text!important;
    color:transparent!important;
}
.wc2-podium-mini b{
    width:28px!important;
    height:28px!important;
    border-radius:10px!important;
}
@media(max-width:1180px){
    .wc2-stats-strip,
    .wc2-info-row{width:min(980px,calc(100vw - 40px))!important;}
    .wc2-info-row{grid-template-columns:1fr!important;}
    .wc2-rules-col,.wc2-prizes-col{min-height:0!important;}
}
@media(max-width:820px){
    .wc2-stats-strip,
    .wc2-info-row{width:calc(100vw - 28px)!important;}
    .wc2-stats-strip{grid-template-columns:1fr!important;gap:10px!important;margin-top:28px!important;}
    .wc2-stat-item{min-height:74px!important;padding:16px 18px!important;border-radius:20px!important;}
    .wc2-info-row{gap:12px!important;margin-top:12px!important;margin-bottom:34px!important;}
    .wc2-rules-col,.wc2-prizes-col{padding:20px!important;border-radius:24px!important;}
    .wc2-rules-line{grid-template-columns:1fr 1fr!important;gap:10px!important;}
    .wc2-rule-node{min-height:126px!important;padding:16px 14px!important;}
    .wc2-podium{grid-template-columns:1fr!important;align-items:stretch!important;}
    .wc2-podium-place,.wc2-podium-place.gold{min-height:98px!important;}
}
@media(max-width:520px){
    .wc2-stats-strip,
    .wc2-info-row{width:calc(100vw - 24px)!important;}
    .wc2-rules-line{grid-template-columns:1fr!important;}
    .wc2-podium-mini{grid-template-columns:1fr!important;}
}
</style>


<style>
/* Ricardo rebuild: World Cup hero v5, fixed layout, compact copy, responsive */
.wc2-wrap{
    width:min(1220px,calc(100vw - 48px))!important;
    overflow:visible!important;
}
.wc2-hero-v4-clean,
.wc2-hero-v4{
    display:none!important;
}
.wc2-hero-v5{
    position:relative!important;
    width:100%!important;
    max-width:1220px!important;
    margin:0 auto 34px!important;
    padding:42px!important;
    border-radius:34px!important;
    overflow:hidden!important;
    isolation:isolate!important;
    border:1px solid rgba(255,255,255,.12)!important;
    background:
        linear-gradient(115deg,rgba(12,22,62,.96) 0%,rgba(9,30,54,.94) 52%,rgba(4,14,31,.98) 100%)!important;
    box-shadow:0 26px 88px rgba(0,0,0,.34),inset 0 1px 0 rgba(255,255,255,.06)!important;
}
.wc2-hero-v5::before{
    content:""!important;
    position:absolute!important;
    inset:auto 0 0!important;
    height:150px!important;
    background:
        linear-gradient(to top,rgba(18,185,129,.18),transparent),
        repeating-linear-gradient(90deg,rgba(255,255,255,.075) 0 1px,transparent 1px 86px)!important;
    opacity:.72!important;
    z-index:-2!important;
}
.wc2-hero-v5::after{
    content:""!important;
    position:absolute!important;
    right:42px!important;
    top:34px!important;
    width:380px!important;
    height:380px!important;
    border-radius:999px!important;
    background:
        radial-gradient(circle at 35% 30%,rgba(255,255,255,.95),rgba(255,255,255,.18) 10%,transparent 11%),
        radial-gradient(circle at 50% 50%,rgba(255,255,255,.10),transparent 56%),
        repeating-conic-gradient(from 18deg,rgba(255,255,255,.10) 0 7deg,transparent 7deg 20deg)!important;
    border:1px solid rgba(255,255,255,.09)!important;
    opacity:.34!important;
    transform:rotate(-16deg)!important;
    z-index:-1!important;
}
.wc2-hero-v5-glow{position:absolute!important;border-radius:999px!important;pointer-events:none!important;z-index:-3!important;filter:blur(2px)!important;}
.wc2-hero-v5-glow.glow-a{width:360px!important;height:360px!important;left:-120px!important;top:-120px!important;background:rgba(98,92,255,.34)!important;}
.wc2-hero-v5-glow.glow-b{width:420px!important;height:420px!important;right:-130px!important;bottom:-180px!important;background:rgba(49,212,255,.25)!important;}
.wc2-hero-v5-trophy{
    position:absolute!important;
    right:84px!important;
    bottom:78px!important;
    font-size:150px!important;
    line-height:1!important;
    color:rgba(255,209,102,.12)!important;
    text-shadow:0 0 60px rgba(255,209,102,.20)!important;
    z-index:-1!important;
}
.wc2-hero-v5-inner{
    width:min(720px,100%)!important;
    position:relative!important;
    z-index:2!important;
    display:grid!important;
    gap:18px!important;
}
.wc2-hero-v5-kicker{
    width:max-content!important;
    max-width:100%!important;
    display:inline-flex!important;
    align-items:center!important;
    gap:9px!important;
    min-height:34px!important;
    padding:0 14px!important;
    border-radius:999px!important;
    background:rgba(255,255,255,.09)!important;
    border:1px solid rgba(255,255,255,.14)!important;
    color:#e7efff!important;
    font-size:12px!important;
    font-weight:950!important;
    letter-spacing:.08em!important;
    text-transform:uppercase!important;
}
.wc2-hero-v5-title{
    margin:0!important;
    max-width:680px!important;
    font-size:clamp(48px,7.2vw,92px)!important;
    line-height:.89!important;
    letter-spacing:-.07em!important;
    font-weight:950!important;
    color:#fff!important;
    text-wrap:balance!important;
}
.wc2-hero-v5-title::after{
    content:""!important;
    display:block!important;
    width:112px!important;
    height:5px!important;
    margin-top:18px!important;
    border-radius:999px!important;
    background:linear-gradient(90deg,#31d4ff,#ffd166)!important;
}
.wc2-hero-v5-live{
    width:min(560px,100%)!important;
    padding:16px!important;
    border-radius:24px!important;
    background:rgba(3,10,24,.58)!important;
    border:1px solid rgba(125,220,255,.16)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 16px 44px rgba(0,0,0,.18)!important;
}
.wc2-hero-v5-live-head{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    gap:12px!important;
    margin-bottom:12px!important;
}
.wc2-hero-v5-live-head span{
    display:inline-flex!important;
    align-items:center!important;
    gap:8px!important;
    color:#7ee8ff!important;
    font-size:12px!important;
    font-weight:950!important;
    text-transform:uppercase!important;
    letter-spacing:.07em!important;
}
.wc2-hero-v5-live-head em{
    color:rgba(235,242,255,.55)!important;
    font-size:12px!important;
    font-style:normal!important;
    font-weight:850!important;
}
.wc2-hero-v5 .wc2-cd-row{
    display:grid!important;
    grid-template-columns:repeat(4,minmax(0,1fr))!important;
    gap:10px!important;
}
.wc2-hero-v5 .wc2-cd-unit{
    min-width:0!important;
    padding:12px 8px!important;
    border-radius:16px!important;
    text-align:center!important;
    background:linear-gradient(180deg,rgba(255,255,255,.095),rgba(255,255,255,.045))!important;
    border:1px solid rgba(255,255,255,.10)!important;
}
.wc2-hero-v5 .wc2-cd-num{
    display:block!important;
    font-size:32px!important;
    line-height:1!important;
    font-weight:950!important;
}
.wc2-hero-v5 .wc2-cd-lbl{
    display:block!important;
    margin-top:6px!important;
    font-size:10px!important;
    font-weight:950!important;
    color:rgba(235,242,255,.50)!important;
    text-transform:uppercase!important;
}
.wc2-hero-v5 .wc2-local-note-v4{
    margin:12px 0 0!important;
    color:rgba(235,242,255,.54)!important;
    font-size:12px!important;
    line-height:1.45!important;
}
.wc2-hero-v5-actions{
    display:flex!important;
    flex-wrap:wrap!important;
    gap:10px!important;
    margin-top:2px!important;
}
.wc2-hero-v5 .wc2-btn{
    min-height:46px!important;
    border-radius:15px!important;
}
.wc2-hero-v5 .wc2-btn-main{
    background:linear-gradient(135deg,#6d5cff,#25d4ff)!important;
    box-shadow:0 14px 34px rgba(49,212,255,.22)!important;
}
.wc2-stats-strip-v5,
.wc2-hero-v5 .wc2-stats-strip{
    position:relative!important;
    z-index:2!important;
    width:100%!important;
    margin:30px 0 0!important;
    display:grid!important;
    grid-template-columns:repeat(3,minmax(0,1fr))!important;
    gap:12px!important;
    padding:0!important;
    background:transparent!important;
    border:0!important;
}
.wc2-hero-v5 .wc2-stat-item{
    min-width:0!important;
    min-height:78px!important;
    padding:16px 18px!important;
    border-radius:20px!important;
    background:rgba(255,255,255,.075)!important;
    border:1px solid rgba(255,255,255,.12)!important;
    box-shadow:none!important;
}
@media(max-width:980px){
    .wc2-wrap{width:min(980px,calc(100vw - 36px))!important;}
    .wc2-hero-v5{padding:34px!important;}
    .wc2-hero-v5::after{right:-90px!important;top:30px!important;}
    .wc2-hero-v5-trophy{right:38px!important;bottom:40px!important;font-size:110px!important;}
}
@media(max-width:720px){
    .wc2-wrap{width:calc(100vw - 24px)!important;padding-left:0!important;padding-right:0!important;}
    .wc2-hero-v5{padding:24px 18px!important;border-radius:28px!important;margin-bottom:24px!important;}
    .wc2-hero-v5::after{width:240px!important;height:240px!important;right:-96px!important;top:-36px!important;opacity:.24!important;}
    .wc2-hero-v5-trophy{display:none!important;}
    .wc2-hero-v5-inner{gap:15px!important;}
    .wc2-hero-v5-kicker{min-height:31px!important;font-size:10px!important;padding:0 11px!important;}
    .wc2-hero-v5-title{font-size:42px!important;letter-spacing:-.055em!important;}
    .wc2-hero-v5-title::after{width:84px!important;height:4px!important;margin-top:14px!important;}
    .wc2-hero-v5-live{padding:13px!important;border-radius:20px!important;}
    .wc2-hero-v5-live-head{align-items:flex-start!important;flex-direction:column!important;gap:4px!important;margin-bottom:10px!important;}
    .wc2-hero-v5 .wc2-cd-row{gap:7px!important;}
    .wc2-hero-v5 .wc2-cd-unit{padding:10px 4px!important;border-radius:13px!important;}
    .wc2-hero-v5 .wc2-cd-num{font-size:25px!important;}
    .wc2-hero-v5 .wc2-cd-lbl{font-size:9px!important;}
    .wc2-hero-v5-actions .wc2-btn{width:100%!important;}
    .wc2-hero-v5 .wc2-stats-strip{grid-template-columns:1fr!important;gap:9px!important;margin-top:22px!important;}
    .wc2-hero-v5 .wc2-stat-item{min-height:70px!important;padding:14px!important;border-radius:18px!important;}
}
@media(max-width:390px){
    .wc2-hero-v5{padding:22px 14px!important;}
    .wc2-hero-v5-title{font-size:37px!important;}
    .wc2-hero-v5 .wc2-cd-row{grid-template-columns:repeat(2,minmax(0,1fr))!important;}
}


/* Ricardo final: full width hero and bigger layout for 0.88 page zoom */
.wc2-wrap{
    width:min(1760px,calc(100vw - 128px))!important;
    max-width:none!important;
}
.wc2-hero-v5{
    width:100%!important;
    max-width:none!important;
    min-height:610px!important;
    margin:0 0 54px!important;
    padding:64px 72px!important;
    border-radius:42px!important;
}
.wc2-hero-v5::before{
    height:210px!important;
    background:
        linear-gradient(to top,rgba(18,185,129,.20),transparent),
        repeating-linear-gradient(90deg,rgba(255,255,255,.085) 0 1px,transparent 1px 95px)!important;
}
.wc2-hero-v5::after{
    right:70px!important;
    top:52px!important;
    width:520px!important;
    height:520px!important;
    opacity:.30!important;
}
.wc2-hero-v5-glow.glow-a{width:520px!important;height:520px!important;left:-170px!important;top:-170px!important;}
.wc2-hero-v5-glow.glow-b{width:620px!important;height:620px!important;right:-180px!important;bottom:-240px!important;}
.wc2-hero-v5-trophy{
    right:130px!important;
    bottom:74px!important;
    font-size:220px!important;
    color:rgba(255,209,102,.13)!important;
}
.wc2-hero-v5-inner{
    width:min(900px,58%)!important;
    gap:24px!important;
}
.wc2-hero-v5-kicker{
    min-height:40px!important;
    padding:0 18px!important;
    font-size:14px!important;
}
.wc2-hero-v5-title{
    max-width:880px!important;
    font-size:clamp(78px,6.2vw,128px)!important;
    line-height:.87!important;
}
.wc2-hero-v5-title::after{
    width:150px!important;
    height:6px!important;
    margin-top:26px!important;
}
.wc2-hero-v5-live{
    width:min(710px,100%)!important;
    padding:22px!important;
    border-radius:28px!important;
}
.wc2-hero-v5-live-head{margin-bottom:16px!important;}
.wc2-hero-v5-live-head span{font-size:14px!important;}
.wc2-hero-v5-live-head em{font-size:13px!important;}
.wc2-hero-v5 .wc2-cd-row{gap:12px!important;}
.wc2-hero-v5 .wc2-cd-unit{
    min-height:88px!important;
    padding:16px 10px!important;
    border-radius:18px!important;
}
.wc2-hero-v5 .wc2-cd-num{font-size:40px!important;}
.wc2-hero-v5 .wc2-cd-lbl{font-size:11px!important;margin-top:8px!important;}
.wc2-hero-v5 .wc2-local-note-v4{font-size:13px!important;margin-top:14px!important;}
.wc2-hero-v5-actions{gap:14px!important;margin-top:2px!important;}
.wc2-hero-v5 .wc2-btn,
.wc2-btn{
    min-height:56px!important;
    padding:0 24px!important;
    border-radius:18px!important;
    font-size:15px!important;
}
.wc2-hero-v5 .wc2-stats-strip{
    margin-top:34px!important;
    gap:16px!important;
}
.wc2-hero-v5 .wc2-stat-item,
.wc2-stat-item{
    min-height:92px!important;
    padding:20px 24px!important;
    border-radius:24px!important;
}
.wc2-stat-icon{width:52px!important;height:52px!important;border-radius:17px!important;font-size:20px!important;}
.wc2-stat-value{font-size:31px!important;line-height:1!important;}
.wc2-stat-label{font-size:12px!important;margin-top:7px!important;}
.wc2-main{
    grid-template-columns:minmax(0,1fr) 420px!important;
    gap:34px!important;
}
.wc2-section-head{margin-bottom:26px!important;}
.wc2-section-head h2{font-size:40px!important;line-height:1.05!important;}
.wc2-section-head p{font-size:17px!important;}
.wc2-days{gap:30px!important;}
.wc2-day-label{font-size:16px!important;margin:8px 0 16px!important;}
.wc2-match{
    grid-template-columns:150px minmax(0,1fr) 180px!important;
    min-height:136px!important;
    padding:24px 28px!important;
    border-radius:30px!important;
    gap:24px!important;
}
.wc2-match:before{width:6px!important;}
.wc2-time strong{font-size:30px!important;}
.wc2-time span,.js-local-date{font-size:13px!important;}
.wc2-chip{min-height:32px!important;padding:0 12px!important;font-size:13px!important;}
.wc2-versus{grid-template-columns:minmax(0,1fr) 54px minmax(0,1fr)!important;gap:24px!important;}
.wc2-vs{width:54px!important;height:54px!important;border-radius:18px!important;font-size:13px!important;}
.wc2-team{gap:16px!important;font-size:16px!important;}
.wc2-team-name{font-size:16px!important;}
.wc2-flag{width:50px!important;height:50px!important;}
.wc2-score{min-width:82px!important;height:48px!important;font-size:22px!important;border-radius:16px!important;}
.wc2-pick .wc2-btn{min-height:46px!important;font-size:14px!important;border-radius:15px!important;}
.wc2-side-card{
    padding:28px!important;
    border-radius:30px!important;
}
.wc2-side-card h3{font-size:24px!important;margin-bottom:18px!important;}
.wc2-board-row{grid-template-columns:44px minmax(0,1fr) auto!important;padding:14px 0!important;gap:14px!important;}
.wc2-rank{width:36px!important;height:36px!important;border-radius:13px!important;font-size:14px!important;}
.wc2-avatar{width:40px!important;height:40px!important;flex-basis:40px!important;}
.wc2-name,.wc2-points,.wc2-side-card p{font-size:15px!important;}
.wc2-code{padding:18px!important;border-radius:20px!important;font-size:16px!important;}
@media(max-width:1400px){
    .wc2-wrap{width:min(1320px,calc(100vw - 72px))!important;}
    .wc2-hero-v5{min-height:560px!important;padding:54px!important;}
    .wc2-hero-v5-inner{width:min(780px,62%)!important;}
    .wc2-hero-v5-title{font-size:clamp(70px,6.2vw,108px)!important;}
    .wc2-hero-v5::after{width:440px!important;height:440px!important;}
    .wc2-hero-v5-trophy{font-size:180px!important;right:90px!important;}
}
@media(max-width:1180px){
    .wc2-wrap{width:min(980px,calc(100vw - 40px))!important;}
    .wc2-hero-v5{min-height:0!important;padding:44px!important;}
    .wc2-hero-v5-inner{width:100%!important;max-width:760px!important;}
    .wc2-hero-v5::after{right:-120px!important;}
    .wc2-hero-v5-trophy{right:30px!important;bottom:36px!important;font-size:130px!important;}
    .wc2-main{grid-template-columns:1fr!important;}
}
@media(max-width:720px){
    .wc2-wrap{width:calc(100vw - 24px)!important;}
    .wc2-hero-v5{padding:28px 20px!important;border-radius:30px!important;margin-bottom:28px!important;}
    .wc2-hero-v5-inner{gap:16px!important;}
    .wc2-hero-v5-title{font-size:46px!important;}
    .wc2-hero-v5-live{padding:14px!important;}
    .wc2-hero-v5 .wc2-cd-unit{min-height:70px!important;}
    .wc2-hero-v5 .wc2-cd-num{font-size:27px!important;}
    .wc2-hero-v5 .wc2-stats-strip{grid-template-columns:1fr!important;}
    .wc2-section-head h2{font-size:31px!important;}
    .wc2-match{grid-template-columns:1fr!important;min-height:0!important;padding:17px!important;}
    .wc2-time strong{font-size:22px!important;}
    .wc2-versus{grid-template-columns:1fr auto 1fr!important;gap:10px!important;}
    .wc2-flag{width:48px!important;height:48px!important;}
    .wc2-team-name{font-size:14px!important;}
}
</style>


<style>
/* Ricardo final: make wc2-hero-v5 break out and use almost full viewport width */
.wc2-hero-v5{
    width:calc(100vw - 96px)!important;
    max-width:1840px!important;
    margin-left:50%!important;
    margin-right:0!important;
    transform:translateX(-50%)!important;
    padding-left:86px!important;
    padding-right:86px!important;
}
.wc2-hero-v5-inner{
    width:min(980px,60%)!important;
}
.wc2-hero-v5::after{
    right:96px!important;
}
.wc2-hero-v5-trophy{
    right:160px!important;
}
@media(max-width:1400px){
    .wc2-hero-v5{
        width:calc(100vw - 56px)!important;
        padding-left:62px!important;
        padding-right:62px!important;
    }
    .wc2-hero-v5-inner{width:min(860px,64%)!important;}
}
@media(max-width:1180px){
    .wc2-hero-v5{
        width:calc(100vw - 40px)!important;
        padding-left:44px!important;
        padding-right:44px!important;
    }
    .wc2-hero-v5-inner{width:100%!important;max-width:780px!important;}
}
@media(max-width:720px){
    .wc2-hero-v5{
        width:calc(100vw - 20px)!important;
        padding-left:20px!important;
        padding-right:20px!important;
    }
}
</style>

<style>
/* Ricardo final: compact but extra wide hero for 0.88 zoom */
.wc2-wrap{
    width:min(1840px,calc(100vw - 96px))!important;
    max-width:none!important;
}
.wc2-hero-v5{
    width:calc(100vw - 72px)!important;
    max-width:1880px!important;
    min-height:0!important;
    height:auto!important;
    margin:0 0 38px!important;
    padding:38px 76px 36px!important;
    border-radius:36px!important;
    display:block!important;
}
.wc2-hero-v5-inner{
    width:100%!important;
    max-width:none!important;
    display:grid!important;
    grid-template-columns:minmax(390px,.72fr) minmax(520px,.95fr)!important;
    grid-template-areas:
        "kicker live"
        "title live"
        "actions live"!important;
    column-gap:56px!important;
    row-gap:14px!important;
    align-items:center!important;
}
.wc2-hero-v5-kicker{grid-area:kicker!important;min-height:36px!important;font-size:13px!important;padding:0 16px!important;}
.wc2-hero-v5-title{
    grid-area:title!important;
    max-width:620px!important;
    font-size:clamp(64px,4.9vw,98px)!important;
    line-height:.86!important;
    letter-spacing:-.072em!important;
}
.wc2-hero-v5-title::after{width:132px!important;height:5px!important;margin-top:22px!important;}
.wc2-hero-v5-live{
    grid-area:live!important;
    width:100%!important;
    max-width:720px!important;
    justify-self:start!important;
    padding:18px!important;
    border-radius:24px!important;
}
.wc2-hero-v5-live-head{margin-bottom:12px!important;}
.wc2-hero-v5 .wc2-cd-row{gap:10px!important;}
.wc2-hero-v5 .wc2-cd-unit{min-height:72px!important;padding:12px 8px!important;border-radius:16px!important;}
.wc2-hero-v5 .wc2-cd-num{font-size:34px!important;}
.wc2-hero-v5 .wc2-cd-lbl{font-size:10px!important;margin-top:6px!important;}
.wc2-hero-v5 .wc2-local-note-v4{font-size:12px!important;margin-top:11px!important;}
.wc2-hero-v5-actions{grid-area:actions!important;margin-top:4px!important;gap:12px!important;}
.wc2-hero-v5 .wc2-btn{min-height:48px!important;padding:0 22px!important;border-radius:16px!important;font-size:14px!important;}
.wc2-hero-v5 .wc2-stats-strip{
    margin-top:26px!important;
    gap:14px!important;
}
.wc2-hero-v5 .wc2-stat-item{
    min-height:72px!important;
    padding:14px 18px!important;
    border-radius:20px!important;
}
.wc2-hero-v5 .wc2-stat-icon{width:46px!important;height:46px!important;border-radius:15px!important;font-size:18px!important;}
.wc2-hero-v5 .wc2-stat-text strong,
.wc2-hero-v5 .wc2-stat-value{font-size:28px!important;line-height:1!important;}
.wc2-hero-v5 .wc2-stat-text span,
.wc2-hero-v5 .wc2-stat-label{font-size:11px!important;margin-top:5px!important;}
.wc2-hero-v5::before{height:118px!important;}
.wc2-hero-v5::after{width:360px!important;height:360px!important;right:86px!important;top:18px!important;opacity:.22!important;}
.wc2-hero-v5-trophy{font-size:160px!important;right:120px!important;bottom:22px!important;opacity:.82!important;}
.wc2-hero-v5-glow.glow-a{width:360px!important;height:360px!important;left:-110px!important;top:-130px!important;}
.wc2-hero-v5-glow.glow-b{width:430px!important;height:430px!important;right:-120px!important;bottom:-190px!important;}
@media(max-width:1400px){
    .wc2-wrap{width:min(1320px,calc(100vw - 56px))!important;}
    .wc2-hero-v5{width:calc(100vw - 48px)!important;padding:34px 52px!important;}
    .wc2-hero-v5-inner{grid-template-columns:minmax(340px,.78fr) minmax(440px,1fr)!important;column-gap:36px!important;}
    .wc2-hero-v5-title{font-size:clamp(58px,5vw,82px)!important;}
    .wc2-hero-v5::after{width:300px!important;height:300px!important;right:44px!important;}
    .wc2-hero-v5-trophy{right:78px!important;font-size:130px!important;}
}
@media(max-width:1180px){
    .wc2-hero-v5{width:calc(100vw - 36px)!important;padding:34px!important;}
    .wc2-hero-v5-inner{grid-template-columns:1fr!important;grid-template-areas:"kicker" "title" "live" "actions"!important;max-width:760px!important;}
    .wc2-hero-v5-title{max-width:680px!important;}
    .wc2-hero-v5-live{max-width:680px!important;}
}
@media(max-width:720px){
    .wc2-wrap{width:calc(100vw - 20px)!important;}
    .wc2-hero-v5{width:calc(100vw - 16px)!important;padding:22px 16px!important;border-radius:26px!important;margin-bottom:24px!important;}
    .wc2-hero-v5-inner{gap:12px!important;}
    .wc2-hero-v5-title{font-size:42px!important;}
    .wc2-hero-v5 .wc2-cd-unit{min-height:62px!important;}
    .wc2-hero-v5 .wc2-cd-num{font-size:25px!important;}
    .wc2-hero-v5 .wc2-stats-strip{grid-template-columns:1fr!important;margin-top:18px!important;}
}
</style>


<style>
/* Ricardo fix: aligned compact wide hero, no viewport breakout */
.worldcup-page main,
.worldcup-page .page-content{
    overflow:visible!important;
}
.worldcup-page .wc2{
    padding-top:210px!important;
}
.worldcup-page .wc2-wrap{
    width:min(1720px,calc(100vw - 72px))!important;
    max-width:none!important;
    margin:0 auto!important;
    padding:30px 0 90px!important;
    box-sizing:border-box!important;
    overflow:visible!important;
}
.wc2-hero-v5{
    width:100%!important;
    max-width:none!important;
    margin:0 0 34px!important;
    transform:none!important;
    left:auto!important;
    right:auto!important;
    min-height:0!important;
    height:auto!important;
    padding:34px 48px 30px!important;
    border-radius:34px!important;
    box-sizing:border-box!important;
    overflow:hidden!important;
}
.wc2-hero-v5-inner{
    width:100%!important;
    max-width:none!important;
    display:grid!important;
    grid-template-columns:minmax(360px,520px) minmax(520px,760px)!important;
    grid-template-areas:
        "kicker live"
        "title live"
        "actions live"!important;
    column-gap:52px!important;
    row-gap:12px!important;
    align-items:center!important;
    justify-content:start!important;
}
.wc2-hero-v5-kicker{
    grid-area:kicker!important;
    min-height:34px!important;
    padding:0 15px!important;
    font-size:12px!important;
}
.wc2-hero-v5-title{
    grid-area:title!important;
    max-width:520px!important;
    font-size:clamp(54px,4.6vw,84px)!important;
    line-height:.88!important;
    letter-spacing:-.068em!important;
}
.wc2-hero-v5-title::after{
    width:118px!important;
    height:5px!important;
    margin-top:18px!important;
}
.wc2-hero-v5-live{
    grid-area:live!important;
    width:100%!important;
    max-width:720px!important;
    justify-self:start!important;
    padding:16px!important;
    border-radius:24px!important;
}
.wc2-hero-v5 .wc2-cd-row{
    gap:10px!important;
}
.wc2-hero-v5 .wc2-cd-unit{
    min-height:68px!important;
    padding:11px 8px!important;
    border-radius:16px!important;
}
.wc2-hero-v5 .wc2-cd-num{
    font-size:32px!important;
}
.wc2-hero-v5 .wc2-cd-lbl{
    font-size:10px!important;
    margin-top:6px!important;
}
.wc2-hero-v5 .wc2-local-note-v4{
    font-size:12px!important;
    margin-top:10px!important;
}
.wc2-hero-v5-actions{
    grid-area:actions!important;
    margin-top:2px!important;
    gap:12px!important;
}
.wc2-hero-v5 .wc2-btn{
    min-height:46px!important;
    padding:0 21px!important;
    border-radius:16px!important;
    font-size:14px!important;
}
.wc2-hero-v5 .wc2-stats-strip,
.wc2-stats-strip-v5{
    width:100%!important;
    margin:24px 0 0!important;
    display:grid!important;
    grid-template-columns:repeat(3,minmax(0,1fr))!important;
    gap:14px!important;
    transform:none!important;
    padding:0!important;
    box-sizing:border-box!important;
}
.wc2-hero-v5 .wc2-stat-item{
    min-height:68px!important;
    padding:13px 18px!important;
    border-radius:20px!important;
}
.wc2-hero-v5 .wc2-stat-icon{
    width:44px!important;
    height:44px!important;
    border-radius:15px!important;
}
.wc2-hero-v5 .wc2-stat-value,
.wc2-hero-v5 .wc2-stat-text strong{
    font-size:27px!important;
}
.wc2-hero-v5 .wc2-stat-label,
.wc2-hero-v5 .wc2-stat-text span{
    font-size:11px!important;
}
.wc2-hero-v5::before{
    height:108px!important;
}
.wc2-hero-v5::after{
    width:330px!important;
    height:330px!important;
    right:58px!important;
    top:18px!important;
    opacity:.20!important;
}
.wc2-hero-v5-trophy{
    font-size:140px!important;
    right:92px!important;
    bottom:20px!important;
}
.wc2-main{
    width:100%!important;
    margin:0 auto!important;
    grid-template-columns:minmax(0,1fr) 420px!important;
    gap:34px!important;
}
@media(max-width:1400px){
    .worldcup-page .wc2-wrap{width:min(1320px,calc(100vw - 56px))!important;}
    .wc2-hero-v5{padding:32px 42px 28px!important;}
    .wc2-hero-v5-inner{grid-template-columns:minmax(330px,470px) minmax(460px,680px)!important;column-gap:34px!important;}
    .wc2-hero-v5-title{font-size:clamp(50px,4.8vw,74px)!important;}
    .wc2-hero-v5::after{width:280px!important;height:280px!important;right:34px!important;}
    .wc2-hero-v5-trophy{right:62px!important;font-size:120px!important;}
}
@media(max-width:1180px){
    .worldcup-page .wc2-wrap{width:calc(100vw - 40px)!important;}
    .wc2-hero-v5-inner{grid-template-columns:1fr!important;grid-template-areas:"kicker" "title" "live" "actions"!important;max-width:760px!important;}
    .wc2-hero-v5-live{max-width:700px!important;}
    .wc2-main{grid-template-columns:1fr!important;}
}
@media(max-width:720px){
    .worldcup-page .wc2{padding-top:190px!important;}
    .worldcup-page .wc2-wrap{width:calc(100vw - 20px)!important;padding-top:22px!important;}
    .wc2-hero-v5{padding:22px 16px!important;border-radius:26px!important;margin-bottom:24px!important;}
    .wc2-hero-v5-title{font-size:42px!important;}
    .wc2-hero-v5 .wc2-cd-row{grid-template-columns:repeat(2,minmax(0,1fr))!important;}
    .wc2-hero-v5 .wc2-stats-strip{grid-template-columns:1fr!important;margin-top:18px!important;}
}
</style>

<style>
/* Ricardo final: use WM 2026 banner as hero background */
.wc2-hero-v5{
    background:
        linear-gradient(90deg,rgba(5,9,27,.94) 0%,rgba(7,18,39,.88) 42%,rgba(7,20,44,.70) 100%),
        linear-gradient(180deg,rgba(5,9,27,.20),rgba(5,9,27,.78)),
        url('/public/assets/website/images/wm-2026/wm-banner.webp') center right / cover no-repeat!important;
    background-color:#071426!important;
}
.wc2-hero-v5::after{
    opacity:.08!important;
}
.wc2-hero-v5-trophy,
.wc2-hero-v5-glow{
    opacity:.55!important;
}
.wc2-hero-v5-live,
.wc2-hero-v5 .wc2-stat-item{
    backdrop-filter:blur(12px)!important;
    -webkit-backdrop-filter:blur(12px)!important;
    background:rgba(4,10,24,.58)!important;
}
</style>


<style>
/* Matchday tabs – segmented pill control */
.wc2-matchday-tabs{display:inline-flex;gap:0;margin-bottom:28px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.10);border-radius:20px;padding:4px;}
.wc2-matchday-tab{display:inline-flex;flex-direction:column;align-items:center;justify-content:center;padding:10px 22px;border-radius:16px;border:none;background:transparent;color:rgba(235,238,255,.55);font-weight:850;font-size:14px;cursor:pointer;transition:.18s ease;gap:2px;position:relative;}
.wc2-matchday-tab small{font-size:11px;font-weight:700;color:rgba(235,238,255,.30);transition:.18s ease;}
.wc2-matchday-tab:hover{color:rgba(235,238,255,.85);}
.wc2-matchday-tab:hover small{color:rgba(235,238,255,.50);}
.wc2-matchday-tab.active{background:linear-gradient(135deg,#625cff,#31d4ff);color:#fff;box-shadow:0 4px 18px rgba(52,115,255,.35);}
.wc2-matchday-tab.active small{color:rgba(255,255,255,.75);}
@media(max-width:600px){
  .wc2-matchday-tabs{display:flex;width:100%;}
  .wc2-matchday-tab{flex:1;padding:9px 10px;}
}
/* prizes note handled below */
#wc2-days-container{transition:opacity .18s ease;}
#wc2-days-container.wc2-loading{opacity:.35;pointer-events:none;}
.wc2-skeleton{display:flex;flex-direction:column;gap:14px;}
.wc2-skeleton-row{height:116px;border-radius:26px;background:linear-gradient(90deg,rgba(255,255,255,.05) 0%,rgba(255,255,255,.10) 50%,rgba(255,255,255,.05) 100%);background-size:200% 100%;animation:wc2-shimmer 1.2s infinite;}
@keyframes wc2-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* Flag initials fallback */
.wc2-flag-initials{
    display:inline-flex;align-items:center;justify-content:center;
    width:38px;height:38px;border-radius:999px;
    background:linear-gradient(135deg,rgba(98,92,255,.38),rgba(49,212,255,.28));
    border:1px solid rgba(125,220,255,.22);
    font-size:11px;font-weight:950;letter-spacing:.04em;
    color:#fff;flex-shrink:0;
}
/* More participants */
.wc2-more-participants{display:flex;align-items:center;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.07);color:rgba(235,238,255,.38);font-size:12px;font-weight:800;}
/* Booster badge in leaderboard */
.wc2-booster-badge{display:inline-flex;align-items:center;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;background:rgba(251,146,60,.15);border:1px solid rgba(251,146,60,.35);color:#fed7aa;margin-left:5px;}

/* Point system */
.wc2-pts-list{display:grid;gap:13px;margin-bottom:4px;}
.wc2-pts-row{display:flex;align-items:flex-start;gap:14px;}
.wc2-pts-badge{min-width:54px;height:32px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:14px;font-weight:950;flex-shrink:0;margin-top:2px;}
.pts-5{background:rgba(250,204,21,.15);border:1px solid rgba(250,204,21,.35);color:#fde68a;}
.pts-3{background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.35);color:#a5b4fc;}
.pts-2{background:rgba(49,212,255,.10);border:1px solid rgba(49,212,255,.28);color:#6ee7ff;}
.pts-0{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);color:rgba(235,238,255,.38);}
.wc2-pts-info{display:flex;flex-direction:column;gap:3px;}
.wc2-pts-info strong{font-size:15px;font-weight:900;color:rgba(235,238,255,.9);}
.wc2-pts-info span{font-size:13px;color:rgba(235,238,255,.58);line-height:1.45;}
.wc2-pts-info em{font-size:12px;color:rgba(235,238,255,.38);font-style:normal;}
/* Prizes mini sidebar */
.wc2-prize-mini-row{display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:14px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08);transition:.14s ease;}
.wc2-prize-medal{font-size:22px;line-height:1;flex:0 0 26px;}
.wc2-prize-place{flex:1;color:rgba(235,238,255,.74);font-size:15px;font-weight:850;}
.wc2-prize-val{font-size:15px;font-weight:950;color:#6ee7ff;white-space:nowrap;}
/* Mobile overrides */
@media(max-width:820px){
.wc2-pts-badge{min-width:50px;height:30px;font-size:13px;}
.wc2-pts-info strong{font-size:14px;}
.wc2-pts-info span{font-size:13px;}
.wc2-pts-info em{font-size:12px;}
.wc2-prize-medal{font-size:20px;}
.wc2-prize-place,.wc2-prize-val{font-size:14px;}
}

/* Prizes sidebar mini card */
.wc2-prizes-mini{display:grid;gap:8px;margin-top:4px;}
.wc2-prize-mini-row:hover{background:rgba(255,255,255,.085);}
.wc2-prize-mini-row.wc2-prize-gold{background:rgba(255,209,102,.10);border-color:rgba(255,209,102,.28);}
.wc2-prize-mini-row.wc2-prize-silver{background:rgba(200,210,230,.09);border-color:rgba(200,210,230,.22);}
.wc2-prize-mini-row.wc2-prize-bronze{background:rgba(205,127,50,.10);border-color:rgba(205,127,50,.26);}
.wc2-prize-mini-row.wc2-prize-gold .wc2-prize-val{color:#ffd166;}
.wc2-prize-mini-row.wc2-prize-silver .wc2-prize-val{color:#c8d2e6;}
.wc2-prize-mini-row.wc2-prize-bronze .wc2-prize-val{color:#e8a97e;}

/* Prizes full section */
/* ── Prizes section ─────────────────────────────────────── */
.wc2-prizes-section{margin-top:52px;padding-bottom:8px;}
.wc2-prizes-section-head{margin-bottom:28px;}
.wc2-prizes-section-head h2{font-size:28px;font-weight:950;letter-spacing:-.03em;margin:0 0 6px;display:flex;align-items:center;gap:10px;}
.wc2-prizes-section-head p{margin:0;font-size:14px;color:rgba(235,238,255,.58);}

.wc2-prizes-grid{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:12px;
    margin-top:0;
}

/* Base card */
.wc2-prize-card{
    position:relative;
    border-radius:20px;
    padding:28px 18px 24px;
    text-align:center;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(10,14,32,.85);
    overflow:hidden;
    transition:transform .18s ease,box-shadow .18s ease;
    display:flex;flex-direction:column;align-items:center;
}
.wc2-prize-card:hover{transform:translateY(-4px);}

/* Top glow overlay */
.wc2-prize-card::before{
    content:"";position:absolute;inset:0;border-radius:inherit;
    background:radial-gradient(ellipse 90% 50% at 50% 0%,var(--pc-glow,rgba(99,102,241,.10)),transparent 70%);
    pointer-events:none;
}
/* Top accent line */
.wc2-prize-card::after{
    content:"";position:absolute;top:0;left:16px;right:16px;height:1px;
    background:var(--pc-line,rgba(255,255,255,.10));
    border-radius:0 0 4px 4px;
}

/* Gold */
.wc2-prize-card--gold{
    border-color:rgba(255,209,102,.32);
    background:linear-gradient(170deg,rgba(38,28,4,.92),rgba(12,9,1,.96));
    --pc-glow:rgba(255,209,102,.22);
    --pc-line:rgba(255,209,102,.50);
    box-shadow:0 2px 40px rgba(255,180,0,.10);
}
.wc2-prize-card--gold:hover{box-shadow:0 16px 52px rgba(255,180,0,.22);}

/* Silver */
.wc2-prize-card--silver{
    border-color:rgba(200,210,230,.22);
    background:linear-gradient(170deg,rgba(22,26,42,.92),rgba(9,11,20,.96));
    --pc-glow:rgba(200,210,230,.12);
    --pc-line:rgba(200,210,230,.30);
}
.wc2-prize-card--silver:hover{box-shadow:0 16px 52px rgba(200,210,230,.08);}

/* Bronze */
.wc2-prize-card--bronze{
    border-color:rgba(205,127,50,.24);
    background:linear-gradient(170deg,rgba(30,18,6,.92),rgba(12,7,1,.96));
    --pc-glow:rgba(205,127,50,.14);
    --pc-line:rgba(205,127,50,.40);
}
.wc2-prize-card--bronze:hover{box-shadow:0 16px 52px rgba(205,127,50,.12);}

/* 4th / 5th – subtle indigo */
.wc2-prize-card--4th,
.wc2-prize-card--5th{
    --pc-glow:rgba(99,102,241,.08);
    --pc-line:rgba(99,102,241,.22);
}

/* Rank badge wrapper */
.wc2-prize-rank-wrap{
    position:relative;
    width:62px;height:62px;
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 14px;
    background:rgba(0,0,0,.28);
    border:2px solid var(--pc-ring,rgba(255,255,255,.10));
}
.wc2-prize-card--gold   .wc2-prize-rank-wrap{--pc-ring:rgba(255,209,102,.40);background:rgba(255,180,0,.08);}
.wc2-prize-card--silver .wc2-prize-rank-wrap{--pc-ring:rgba(200,210,230,.32);background:rgba(200,210,230,.06);}
.wc2-prize-card--bronze .wc2-prize-rank-wrap{--pc-ring:rgba(205,127,50,.34);background:rgba(205,127,50,.07);}
.wc2-prize-card--4th    .wc2-prize-rank-wrap,
.wc2-prize-card--5th    .wc2-prize-rank-wrap{--pc-ring:rgba(99,102,241,.30);background:rgba(99,102,241,.10);}

/* The rank number / icon inside */
.wc2-prize-rank-num{
    font-size:26px;font-weight:950;letter-spacing:-.03em;color:#fff;line-height:1;
}
.wc2-prize-card--gold   .wc2-prize-rank-num{color:#ffd166;}
.wc2-prize-card--silver .wc2-prize-rank-num{color:#c8d2e6;}
.wc2-prize-card--bronze .wc2-prize-rank-num{color:#e8a97e;}
.wc2-prize-card--4th    .wc2-prize-rank-num,
.wc2-prize-card--5th    .wc2-prize-rank-num{color:#a5b4fc;}

.wc2-prize-rank-label{
    font-size:10px;font-weight:900;text-transform:uppercase;
    letter-spacing:.10em;color:rgba(235,238,255,.45);
    margin-bottom:16px;
}
.wc2-prize-card--gold   .wc2-prize-rank-label{color:rgba(255,209,102,.65);}
.wc2-prize-card--silver .wc2-prize-rank-label{color:rgba(200,210,230,.58);}
.wc2-prize-card--bronze .wc2-prize-rank-label{color:rgba(225,155,100,.58);}

/* Coin amount row */
.wc2-prize-amount{
    display:flex;align-items:center;justify-content:center;gap:7px;
    font-size:22px;font-weight:950;letter-spacing:-.03em;color:#fff;
    margin-bottom:8px;line-height:1;
}
.wc2-prize-amount img{width:22px;height:22px;object-fit:contain;flex-shrink:0;}
.wc2-prize-card--gold   .wc2-prize-amount{color:#ffd166;}
.wc2-prize-card--silver .wc2-prize-amount{color:#c8d2e6;}
.wc2-prize-card--bronze .wc2-prize-amount{color:#e8a97e;}

.wc2-prize-desc{
    margin:0;font-size:12px;
    color:rgba(235,238,255,.38);line-height:1.5;
}

/* Note */
.wc2-prizes-note{
    margin-top:20px;font-size:13px;color:rgba(235,238,255,.55);
    display:flex;align-items:flex-start;gap:10px;line-height:1.6;
    background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.16);
    border-radius:14px;padding:14px 18px;
}
.wc2-prizes-note i{margin-top:2px;flex:0 0 auto;color:#6ee7ff;font-size:14px;}

@media(max-width:1180px){.wc2-prizes-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:720px){.wc2-prizes-grid{grid-template-columns:1fr 1fr;}.wc2-prize-card--5th{grid-column:1/-1;}}
@media(max-width:480px){.wc2-prizes-grid{grid-template-columns:1fr;}.wc2-prize-card--5th{grid-column:auto;}}
</style>

<style>
/* Ricardo final: move hero timer to the right so the WM 2026 banner center stays visible */
.wc2-hero-v5{
    background:
        linear-gradient(90deg,rgba(5,9,27,.96) 0%,rgba(5,9,27,.82) 30%,rgba(5,9,27,.22) 52%,rgba(5,9,27,.46) 72%,rgba(5,9,27,.84) 100%),
        linear-gradient(180deg,rgba(5,9,27,.08),rgba(5,9,27,.64)),
        url('/public/assets/website/images/wm-2026/wm-banner.webp') center center / cover no-repeat!important;
}
.wc2-hero-v5-inner{
    grid-template-columns:minmax(330px,500px) minmax(220px,1fr) minmax(520px,680px)!important;
    grid-template-areas:
        "kicker . live"
        "title . live"
        "actions . live"!important;
    column-gap:34px!important;
}
.wc2-hero-v5-live{
    justify-self:end!important;
    max-width:680px!important;
    background:rgba(4,10,24,.66)!important;
}
.wc2-hero-v5::after,
.wc2-hero-v5-trophy,
.wc2-hero-v5-glow{
    display:none!important;
}
.wc2-hero-v5 .wc2-stats-strip,
.wc2-stats-strip-v5{
    max-width:100%!important;
}
@media(max-width:1400px){
    .wc2-hero-v5-inner{
        grid-template-columns:minmax(300px,440px) minmax(130px,1fr) minmax(460px,620px)!important;
        column-gap:24px!important;
    }
    .wc2-hero-v5-live{max-width:620px!important;}
}
@media(max-width:1180px){
    .wc2-hero-v5-inner{
        grid-template-columns:1fr!important;
        grid-template-areas:"kicker" "title" "live" "actions"!important;
        max-width:760px!important;
    }
    .wc2-hero-v5-live{
        justify-self:start!important;
        max-width:700px!important;
    }
}
</style>

<style>
/* Ricardo: sidebar prizes redesign, keeps the top Prizes – Top 5 section unchanged */
.wc2-side-card.wc2-side-prizes{
    padding:24px!important;
    border-radius:26px!important;
    background:linear-gradient(145deg,rgba(16,21,42,.94),rgba(9,13,29,.96))!important;
    border:1px solid rgba(255,255,255,.08)!important;
    box-shadow:0 22px 56px rgba(0,0,0,.26), inset 0 1px 0 rgba(255,255,255,.035)!important;
    overflow:hidden!important;
}
.wc2-side-card.wc2-side-prizes h3{
    display:flex!important;
    align-items:center!important;
    gap:9px!important;
    margin:0 0 18px!important;
    font-size:22px!important;
    line-height:1!important;
    font-weight:950!important;
    color:#fff!important;
    letter-spacing:-.02em!important;
}
.wc2-side-card.wc2-side-prizes h3 i{
    margin-right:0!important;
    color:#ffd166!important;
    filter:drop-shadow(0 6px 12px rgba(255,209,102,.22))!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prizes-mini{
    display:grid!important;
    gap:8px!important;
    margin:0!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-mini-row{
    position:relative!important;
    display:grid!important;
    grid-template-columns:34px minmax(0,1fr) auto!important;
    align-items:center!important;
    gap:10px!important;
    min-height:48px!important;
    padding:8px 12px!important;
    border-radius:13px!important;
    background:linear-gradient(135deg,rgba(255,255,255,.06),rgba(255,255,255,.035))!important;
    border:1px solid rgba(255,255,255,.075)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
    transition:.16s ease!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-mini-row:hover{
    transform:translateY(-1px)!important;
    background:linear-gradient(135deg,rgba(255,255,255,.085),rgba(255,255,255,.045))!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-mini-row.wc2-prize-gold{
    background:linear-gradient(135deg,rgba(255,209,102,.12),rgba(255,255,255,.045))!important;
    border-color:rgba(255,209,102,.28)!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-mini-row.wc2-prize-silver{
    background:linear-gradient(135deg,rgba(185,198,255,.10),rgba(255,255,255,.04))!important;
    border-color:rgba(185,198,255,.18)!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-mini-row.wc2-prize-bronze{
    background:linear-gradient(135deg,rgba(205,127,50,.11),rgba(255,255,255,.04))!important;
    border-color:rgba(205,127,50,.22)!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-medal{
    width:28px!important;
    height:28px!important;
    display:grid!important;
    place-items:center!important;
    font-size:21px!important;
    line-height:1!important;
    flex:0 0 28px!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-place{
    min-width:0!important;
    overflow:hidden!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
    color:rgba(235,238,255,.74)!important;
    font-size:14px!important;
    font-weight:900!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-val{
    color:#6ee7ff!important;
    font-size:14px!important;
    font-weight:950!important;
    white-space:nowrap!important;
    text-shadow:0 0 18px rgba(110,231,255,.18)!important;
}
.wc2-side-card.wc2-side-prizes .wc2-prize-gold .wc2-prize-val{color:#ffd166!important;text-shadow:0 0 18px rgba(255,209,102,.18)!important;}
.wc2-side-card.wc2-side-prizes .wc2-prize-silver .wc2-prize-val{color:#d8e0ff!important;}
.wc2-side-card.wc2-side-prizes .wc2-prize-bronze .wc2-prize-val{color:#e8a97e!important;}
.wc2-side-card.wc2-side-prizes > .wc2-btn{
    margin-top:16px!important;
    width:100%!important;
    min-height:48px!important;
    border-radius:16px!important;
    justify-content:center!important;
    background:linear-gradient(135deg,rgba(255,255,255,.07),rgba(255,255,255,.04))!important;
    border:1px solid rgba(255,255,255,.09)!important;
    color:#fff!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
}
.wc2-side-card.wc2-side-prizes > .wc2-btn:hover{
    background:linear-gradient(135deg,rgba(98,92,255,.20),rgba(49,212,255,.12))!important;
    border-color:rgba(125,220,255,.24)!important;
}
@media(max-width:520px){
    .wc2-side-card.wc2-side-prizes{padding:20px!important;border-radius:24px!important;}
    .wc2-side-card.wc2-side-prizes h3{font-size:21px!important;margin-bottom:16px!important;}
    .wc2-side-card.wc2-side-prizes .wc2-prize-mini-row{grid-template-columns:32px minmax(0,1fr) auto!important;min-height:46px!important;padding:8px 10px!important;}
    .wc2-side-card.wc2-side-prizes .wc2-prize-place,.wc2-side-card.wc2-side-prizes .wc2-prize-val{font-size:13px!important;}
}
</style>
