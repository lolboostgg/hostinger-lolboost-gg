<?= $this->layout('admin/layouts/main', [
    'meta' => [
        'title'       => 'World Cup Predictions | Admin',
        'h1'          => 'World Cup Predictions',
        'description' => 'Manage matches, enter results, track leaderboard.',
    ],
]) ?>

<?php
global $db;
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

/* ── Matchday groups (label + date range) ───────────────────────────── */
$matchdayMeta = [
    1  => ['label' => 'Matchday 1',          'range' => '11.06. – 18.06.'],
    2  => ['label' => 'Matchday 2',          'range' => '18.06. – 24.06.'],
    3  => ['label' => 'Matchday 3',          'range' => '24.06. – 28.06.'],
    16 => ['label' => 'Round of 32',     'range' => '28.06. – 04.07.'],
    8  => ['label' => 'Round of 16',          'range' => '04.07. – 07.07.'],
    4  => ['label' => 'Quarter-finals',         'range' => '09.07. – 12.07.'],
    2  => ['label' => 'Semi-finals',            'range' => '14.07. – 15.07.'],
    3  => ['label' => '3rd Place',      'range' => '18.07. – 18.07.'],
    0  => ['label' => 'Final',                'range' => '19.07. – 19.07.'],
];

/* Use integer matchday values that are distinct */
$matchdayTabs = [
    ['id' => 1,  'label' => 'Matchday 1',      'range' => '11.06. – 18.06.'],
    ['id' => 2,  'label' => 'Matchday 2',      'range' => '18.06. – 24.06.'],
    ['id' => 3,  'label' => 'Matchday 3',      'range' => '24.06. – 28.06.'],
    ['id' => 4,  'label' => 'Round of 32','range' => '28.06. – 04.07.'],
    ['id' => 5,  'label' => 'Round of 16',     'range' => '04.07. – 07.07.'],
    ['id' => 6,  'label' => 'Quarter-finals',    'range' => '09.07. – 12.07.'],
    ['id' => 7,  'label' => 'Semi-finals',       'range' => '14.07. – 15.07.'],
    ['id' => 8,  'label' => '3rd Place', 'range' => '18.07. – 18.07.'],
    ['id' => 9,  'label' => 'Final',           'range' => '19.07. – 19.07.'],
];

$activeMatchday = max(1, min(9, (int)($_GET['matchday'] ?? 1)));

$autoLockCutoff = (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))
    ->modify('+60 minutes')
    ->format('Y-m-d H:i:s');
$db->run(
    'UPDATE worldcup_matches
        SET is_locked = 1
      WHERE matchday = ?
        AND is_locked = 0
        AND is_finished = 0
        AND kickoff_at <= ?',
    $activeMatchday,
    $autoLockCutoff
);

$matches = $db->run(
    'SELECT * FROM worldcup_matches WHERE matchday = ? ORDER BY kickoff_at ASC, id ASC',
    $activeMatchday
) ?: [];

$leaderboard = $db->run(
    "SELECT p.participant_type, p.participant_id,
            CASE p.participant_type
                WHEN 'client'  THEN COALESCE(NULLIF(c.username,''), c.email, CONCAT('Client #', c.id))
                WHEN 'booster' THEN COALESCE(b.username, CONCAT('Booster #', b.id))
            END AS name,
            CASE p.participant_type
                WHEN 'client'  THEN c.icon
                WHEN 'booster' THEN b.icon
            END AS icon,
            COALESCE(SUM(p.points), 0) AS points, COUNT(p.id) AS tips
     FROM worldcup_predictions p
     LEFT JOIN clients c  ON p.participant_type = 'client'  AND c.id = p.participant_id
     LEFT JOIN boosters b ON p.participant_type = 'booster' AND b.id = p.participant_id AND b.is_banned = 0
     WHERE (p.participant_type = 'client' AND c.id IS NOT NULL)
        OR (p.participant_type = 'booster' AND b.id IS NOT NULL)
     GROUP BY p.participant_type, p.participant_id
     ORDER BY points DESC, tips DESC, p.participant_id ASC
     LIMIT 20"
) ?: [];

$totalParticipants = (int)($db->run(
    "SELECT COUNT(DISTINCT CONCAT(participant_type,'_',participant_id)) AS cnt FROM worldcup_predictions"
)[0]['cnt'] ?? 0);

$flagMap = [
    'Mexico'=>'mx','South Africa'=>'za','South Korea'=>'kr','Czechia'=>'cz',
    'Canada'=>'ca','Bosnia and Herzegovina'=>'ba','USA'=>'us','Paraguay'=>'py',
    'Qatar'=>'qa','Switzerland'=>'ch','Haiti'=>'ht','Scotland'=>'gb-sct',
    'Australia'=>'au','Turkey'=>'tr','Brazil'=>'br','Morocco'=>'ma',
    'Germany'=>'de','Curacao'=>'cw','Netherlands'=>'nl','Japan'=>'jp',
    'Ivory Coast'=>'ci','Ecuador'=>'ec','Sweden'=>'se','Tunisia'=>'tn',
    'Spain'=>'es','Cape Verde'=>'cv','Belgium'=>'be','Egypt'=>'eg',
    'Saudi Arabia'=>'sa','Uruguay'=>'uy','Iran'=>'ir','New Zealand'=>'nz',
    'France'=>'fr','Senegal'=>'sn','Norway'=>'no','Argentina'=>'ar',
    'Algeria'=>'dz','Austria'=>'at','Jordan'=>'jo','Portugal'=>'pt',
    'DR Congo'=>'cd','England'=>'gb','Croatia'=>'hr','Ghana'=>'gh',
    'Panama'=>'pa','Uzbekistan'=>'uz','Colombia'=>'co','Iraq'=>'iq',
];
$flagUrl = function(string $team) use ($flagMap): string {
    $code = $flagMap[$team] ?? '';
    return $code !== '' ? ASSET_URL . '/website/images/flags/' . $code . '.svg' : '';
};
$flagInitials = function(string $team): string {
    $words = preg_split('/\s+/', trim($team));
    return strtoupper(count($words) >= 2
        ? mb_substr($words[0],0,1,'UTF-8') . mb_substr($words[1],0,1,'UTF-8')
        : mb_substr($team,0,2,'UTF-8'));
};
$clientAvatar = function(string $icon): string {
    $icon = trim($icon);
    return $icon !== '' ? $icon : ASSET_URL . '/core/main/img/logos/PNG/icon-bg-64x64.png';
};

/* Count stats */
$totalMatches  = count($matches);
$finishedCount = 0;
$lockedCount   = 0;
foreach ($matches as $m) {
    if ((int)$m['is_finished']) $finishedCount++;
    if ((int)$m['is_locked'])   $lockedCount++;
}

$nowBerlin = new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'));
$fmtKickoff = function ($dt) {
    try {
        $d = new DateTimeImmutable((string)$dt, new DateTimeZone('Europe/Berlin'));
        return $d->format('d.m.Y H:i');
    } catch (Throwable $e) { return '—'; }
};
?>

<?= $this->start('styles') ?>
<style>
/* ── layout ── */
.wca-wrap{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:22px;align-items:start;}
@media(max-width:1100px){.wca-wrap{grid-template-columns:1fr;}}

/* ── tabs ── */
.wca-tabs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;}
.wca-tab{display:inline-flex;flex-direction:column;align-items:flex-start;padding:8px 14px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.78rem;font-weight:800;cursor:pointer;text-decoration:none;transition:.14s ease;white-space:nowrap;}
.wca-tab small{font-size:.68rem;font-weight:600;color:rgba(255,255,255,.38);margin-top:2px;}
.wca-tab:hover{border-color:rgba(99,102,241,.45);background:rgba(99,102,241,.10);color:#fff;}
.wca-tab.active{border-color:rgba(99,102,241,.7);background:rgba(99,102,241,.18);color:#fff;}
.wca-tab.active small{color:rgba(165,180,252,.7);}

/* ── stat strip ── */
.wca-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;}
.wca-stat{border-radius:14px;padding:14px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);}
.wca-stat strong{display:block;font-size:1.5rem;font-weight:900;color:#fff;}
.wca-stat span{display:block;font-size:.72rem;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.07em;margin-top:3px;}

/* ── card ── */
.wca-card{border-radius:18px;border:1px solid rgba(255,255,255,.08);background:#1a1d22;overflow:hidden;margin-bottom:14px;}
.wca-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.07);}
.wca-card-head h3{margin:0;font-size:.9rem;font-weight:900;display:flex;align-items:center;gap:8px;}
.wca-card-body{padding:16px;}

/* ── match row ── */
.wca-match{display:grid;grid-template-columns:140px 1fr auto;gap:14px;align-items:center;padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.055);}
.wca-match:last-child{border-bottom:0;}
.wca-match.finished{background:rgba(34,197,94,.04);}
.wca-match.locked:not(.finished){background:rgba(239,68,68,.04);}
.wca-kickoff{font-size:.78rem;color:rgba(255,255,255,.5);}
.wca-kickoff strong{display:block;font-size:.84rem;color:rgba(255,255,255,.85);font-weight:800;}
.wca-teams{font-weight:800;font-size:.9rem;}
.wca-teams small{display:block;font-size:.72rem;font-weight:600;color:rgba(255,255,255,.42);margin-top:3px;}
.wca-result-form{display:flex;align-items:center;gap:8px;}
.wca-result-form input[type=number]{width:52px;height:36px;border-radius:10px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.28);color:#fff;text-align:center;font-size:1rem;font-weight:900;padding:0;}
.wca-result-form .sep{font-size:1.1rem;font-weight:900;color:rgba(255,255,255,.4);}
.wca-save-btn{min-height:36px;padding:0 14px;border-radius:10px;border:1px solid rgba(99,102,241,.45);background:rgba(99,102,241,.15);color:#a5b4fc;font-size:.78rem;font-weight:800;cursor:pointer;transition:.14s ease;white-space:nowrap;}
.wca-save-btn:hover{background:rgba(99,102,241,.30);color:#fff;}
.wca-save-btn.success{border-color:rgba(34,197,94,.5);background:rgba(34,197,94,.12);color:#86efac;}
.wca-reset-btn{min-height:36px;width:36px;border-radius:10px;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.08);color:#fca5a5;font-size:.82rem;cursor:pointer;transition:.14s ease;display:inline-flex;align-items:center;justify-content:center;}
.wca-reset-btn:hover{background:rgba(239,68,68,.22);color:#fff;border-color:rgba(239,68,68,.6);}
.wca-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;}
.wca-badge.done{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.28);color:#86efac;}
.wca-badge.locked{background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.22);color:#fca5a5;}
.wca-badge.open{background:rgba(250,204,21,.08);border:1px solid rgba(250,204,21,.22);color:#fde68a;}

/* ── add match form ── */
.wca-add-form{display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end;padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);}
.wca-add-form label{font-size:.72rem;font-weight:800;text-transform:uppercase;color:rgba(255,255,255,.45);letter-spacing:.06em;display:block;margin-bottom:5px;}
.wca-add-form input,.wca-add-form select{width:100%;height:38px;border-radius:10px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.22);color:#fff;padding:0 10px;font-size:.82rem;}
.wca-add-btn{min-height:38px;padding:0 16px;border-radius:10px;border:1px solid rgba(99,102,241,.5);background:rgba(99,102,241,.20);color:#a5b4fc;font-weight:800;cursor:pointer;font-size:.82rem;transition:.14s ease;white-space:nowrap;}
.wca-add-btn:hover{background:rgba(99,102,241,.35);color:#fff;}
@media(max-width:900px){.wca-add-form{grid-template-columns:1fr 1fr;}}
@media(max-width:600px){.wca-add-form{grid-template-columns:1fr;}}

/* ── sidebar cards ── */
.wca-side-card{border-radius:18px;border:1px solid rgba(255,255,255,.08);background:#1a1d22;padding:18px;margin-bottom:14px;}
.wca-side-card h3{margin:0 0 14px;font-size:.9rem;font-weight:900;}
.wca-board{display:grid;gap:4px;}
.wca-board-row{display:grid;grid-template-columns:36px 1fr auto;gap:8px;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.055);}
.wca-board-row:last-child{border-bottom:0;}
.wca-rank-num{width:28px;height:28px;border-radius:9px;background:rgba(255,255,255,.06);display:grid;place-items:center;font-size:.75rem;font-weight:900;color:rgba(255,255,255,.6);}
.wca-rank-num.gold{background:rgba(255,209,102,.15);color:#ffd166;}
.wca-rank-num.silver{background:rgba(200,210,230,.10);color:#c8d2e6;}
.wca-rank-num.bronze{background:rgba(205,127,50,.12);color:#e8a97e;}
.wca-client-name{font-size:.82rem;font-weight:800;color:rgba(255,255,255,.85);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wca-client-pts{font-size:.82rem;font-weight:900;color:#6ee7ff;}

/* ── match flags ── */
.wca-flag{width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.12);flex-shrink:0;}
.wca-flag-init{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,rgba(99,102,241,.5),rgba(49,212,255,.3));border:1px solid rgba(125,220,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:#fff;flex-shrink:0;}
.wca-teams-inner{display:flex;align-items:center;gap:8px;}
.wca-teams-vs{color:rgba(255,255,255,.3);font-size:.75rem;font-weight:700;margin:0 2px;}

/* ── leaderboard avatar ── */
.wca-board-row{display:grid;grid-template-columns:28px 28px 1fr auto;gap:8px;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.055);}
.wca-board-row:last-child{border-bottom:0;}
.wca-avatar{width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.10);}
.wca-client-link{font-size:.82rem;font-weight:800;color:rgba(255,255,255,.85);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-decoration:none;}
.wca-client-link:hover{color:#6ee7ff;text-decoration:underline;}
.wca-lock-info{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:12px;background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.18);margin-bottom:12px;}
.wca-lock-info i{color:#facc15;margin-top:2px;flex-shrink:0;}
.wca-lock-info p{margin:0;font-size:.78rem;color:rgba(255,255,255,.68);line-height:1.5;}

/* ── bulk lock btn ── */
.wca-danger-btn{min-height:36px;padding:0 14px;border-radius:10px;border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.10);color:#fca5a5;font-size:.78rem;font-weight:800;cursor:pointer;transition:.14s ease;white-space:nowrap;}
.wca-danger-btn:hover{background:rgba(239,68,68,.22);color:#fff;}
.wca-green-btn{min-height:36px;padding:0 14px;border-radius:10px;border:1px solid rgba(34,197,94,.4);background:rgba(34,197,94,.10);color:#86efac;font-size:.78rem;font-weight:800;cursor:pointer;transition:.14s ease;white-space:nowrap;}
.wca-green-btn:hover{background:rgba(34,197,94,.22);color:#fff;}
</style>
<?= $this->stop() ?>

<!-- Matchday Tabs -->
<div class="wca-tabs">
    <?php foreach ($matchdayTabs as $tab): ?>
        <a href="?matchday=<?= $tab['id'] ?>"
           class="wca-tab <?= $activeMatchday === $tab['id'] ? 'active' : '' ?>">
            <?= $h($tab['label']) ?>
            <small><?= $h($tab['range']) ?></small>
        </a>
    <?php endforeach; ?>
</div>

<!-- Stats -->
<div class="wca-stats">
    <div class="wca-stat">
        <strong><?= $totalMatches ?></strong>
        <span>Matches total</span>
    </div>
    <div class="wca-stat">
        <strong><?= $finishedCount ?></strong>
        <span>Results entered</span>
    </div>
    <div class="wca-stat">
        <strong><?= $totalMatches - $finishedCount ?></strong>
        <span>Still pending</span>
    </div>
</div>

<div class="wca-wrap">
    <!-- Left: Matches -->
    <div>
        <div class="wca-card">
            <div class="wca-card-head">
                <h3><i class="fa-solid fa-futbol"></i> Matches – <?= $h($matchdayTabs[$activeMatchday - 1]['label'] ?? '') ?></h3>
                <div style="display:flex;gap:8px;">
                    <button class="wca-green-btn" id="btnAutoLock" data-matchday="<?= $activeMatchday ?>">
                        <i class="fa-solid fa-clock"></i> Check auto-lock
                    </button>
                    <button class="wca-danger-btn" id="btnLockAll" data-matchday="<?= $activeMatchday ?>">
                        <i class="fa-solid fa-lock"></i> Lock all
                    </button>
                    <button class="wca-green-btn" id="btnUnlockAll" data-matchday="<?= $activeMatchday ?>">
                        <i class="fa-solid fa-lock-open"></i> Unlock all
                    </button>
                </div>
            </div>

            <?php if (empty($matches)): ?>
                <div class="wca-card-body" style="color:rgba(255,255,255,.45);font-size:.85rem;">
                    No matches for this matchday yet. Add one below.
                </div>
            <?php else: ?>
                <?php foreach ($matches as $m):
                    $isFinished = (int)$m['is_finished'] === 1;
                    $isLocked   = (int)$m['is_locked'] === 1;
                    $kickoffTs  = strtotime((string)$m['kickoff_at']);
                    $autoLocked = $kickoffTs && $kickoffTs <= (time() + 3600); // 1h before
                    $rowClass   = $isFinished ? 'finished' : ($isLocked ? 'locked' : '');
                ?>
                    <div class="wca-match <?= $rowClass ?>" id="match-row-<?= (int)$m['id'] ?>">
                        <div class="wca-kickoff">
                            <strong><?= $h($fmtKickoff($m['kickoff_at'])) ?></strong>
                            <?php if ($isFinished): ?>
                                <span class="wca-badge done"><i class="fa-solid fa-check"></i> Done</span>
                            <?php elseif ($isLocked || $autoLocked): ?>
                                <span class="wca-badge locked"><i class="fa-solid fa-lock"></i> Locked</span>
                            <?php else: ?>
                                <span class="wca-badge open"><i class="fa-solid fa-pen"></i> Open</span>
                            <?php endif; ?>
                        </div>
                        <div class="wca-teams">
                            <?php
                                $hFlag = $flagUrl($m['home_team']);
                                $aFlag = $flagUrl($m['away_team']);
                                $hInit = $flagInitials($m['home_team']);
                                $aInit = $flagInitials($m['away_team']);
                            ?>
                            <div class="wca-teams-inner">
                                <?php if ($hFlag): ?><img class="wca-flag" src="<?= $h($hFlag) ?>" alt=""><?php else: ?><span class="wca-flag-init"><?= $h($hInit) ?></span><?php endif; ?>
                                <span><?= $h($m['home_team']) ?></span>
                                <span class="wca-teams-vs">vs</span>
                                <span><?= $h($m['away_team']) ?></span>
                                <?php if ($aFlag): ?><img class="wca-flag" src="<?= $h($aFlag) ?>" alt=""><?php else: ?><span class="wca-flag-init"><?= $h($aInit) ?></span><?php endif; ?>
                            </div>
                            <small>Group <?= $h($m['group_name'] ?? '') ?></small>
                        </div>
                        <div class="wca-result-form">
                            <input type="number" min="0" max="30"
                                   value="<?= $isFinished ? (int)$m['home_score'] : '' ?>"
                                   placeholder="–" id="hs-<?= (int)$m['id'] ?>">
                            <span class="sep">:</span>
                            <input type="number" min="0" max="30"
                                   value="<?= $isFinished ? (int)$m['away_score'] : '' ?>"
                                   placeholder="–" id="as-<?= (int)$m['id'] ?>">
                            <button class="wca-save-btn"
                                    data-match-id="<?= (int)$m['id'] ?>"
                                    onclick="saveResult(this, <?= (int)$m['id'] ?>)">
                                <?= $isFinished ? 'Update' : 'Save result' ?>
                            </button>
                            <?php if ($isFinished): ?>
                            <button class="wca-reset-btn"
                                    title="Reset result"
                                    onclick="resetResult(this, <?= (int)$m['id'] ?>)">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Add Match Form -->
            <div class="wca-add-form" id="addMatchForm">
                <div>
                    <label>Home team</label>
                    <input type="text" id="newHome" placeholder="z.B. Germany">
                </div>
                <div>
                    <label>Away team</label>
                    <input type="text" id="newAway" placeholder="z.B. France">
                </div>
                <div>
                    <label>Kickoff (Berlin time)</label>
                    <input type="datetime-local" id="newKickoff">
                </div>
                <div>
                    <label>Group</label>
                    <input type="text" id="newGroup" placeholder="z.B. A">
                </div>
                <button class="wca-add-btn" onclick="addMatch()">
                    <i class="fa-solid fa-plus"></i> Add match
                </button>
            </div>
        </div>
    </div>

    <!-- Right: Sidebar -->
    <div>
        <!-- Lock info -->
        <div class="wca-side-card">
            <h3><i class="fa-solid fa-clock" style="color:#facc15;margin-right:6px;"></i> Auto-Lock Rule</h3>
            <div class="wca-lock-info">
                <i class="fa-solid fa-circle-info"></i>
                <p>Predictions are automatically locked <strong>1 hour before kickoff</strong>. The "Check auto-lock" button locks all matches kicking off within the next hour. Results can still be entered at any time.</p>
            </div>
            <button class="wca-green-btn" style="width:100%;justify-content:center;" id="btnAutoLock2" data-matchday="<?= $activeMatchday ?>">
                <i class="fa-solid fa-rotate"></i> Run auto-lock now
            </button>
        </div>

        <!-- Leaderboard -->
        <div class="wca-side-card">
            <h3><i class="fa-solid fa-trophy" style="color:#ffd166;margin-right:6px;"></i> Leaderboard (overall)</h3>
            <?php if (empty($leaderboard)): ?>
                <p style="font-size:.82rem;color:rgba(255,255,255,.42);">No predictions submitted yet.</p>
            <?php else: ?>
                <div class="wca-board">
                    <?php foreach ($leaderboard as $i => $row):
                        $rankClass    = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
                        $isBoosterRow = ($row['participant_type'] ?? 'client') === 'booster';
                        $clientUrl    = $isBoosterRow
                            ? ADMN_URL . '/booster/' . (int)$row['participant_id']
                            : ADMN_URL . '/client/' . (int)$row['participant_id'];
                    ?>
                        <div class="wca-board-row">
                            <span class="wca-rank-num <?= $rankClass ?>"><?= $i + 1 ?></span>
                            <img class="wca-avatar" src="<?= $h($clientAvatar((string)($row['icon'] ?? ''))) ?>" alt="">
                            <a href="<?= $h($clientUrl) ?>" class="wca-client-link" target="_blank">
                                <?= $h($row['name']) ?>
                                <?php if ($isBoosterRow): ?>
                                    <span style="font-size:.68rem;color:#fed7aa;background:rgba(251,146,60,.15);border:1px solid rgba(251,146,60,.3);border-radius:999px;padding:1px 6px;margin-left:4px;font-weight:900;">Booster</span>
                                <?php endif; ?>
                            </a>
                            <strong class="wca-client-pts"><?= (int)$row['points'] ?> pts</strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalParticipants > 20): ?>
                    <p style="margin:10px 0 0;font-size:.75rem;color:rgba(255,255,255,.38);text-align:center;">
                        ··· <?= number_format($totalParticipants - 20) ?> more participant<?= ($totalParticipants - 20) !== 1 ? 's' : '' ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Prize reminder -->
        <div class="wca-side-card">
            <h3><i class="fa-solid fa-gift" style="color:#a78bfa;margin-right:6px;"></i> Prizes Top 5</h3>
            <div style="display:grid;gap:6px;font-size:.82rem;">
                <div style="display:flex;justify-content:space-between;"><span>🥇 1st place</span><strong style="color:#ffd166;">50 LB Coins</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>🥈 2nd place</span><strong style="color:#c8d2e6;">30 LB Coins</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>🥉 3rd place</span><strong style="color:#e8a97e;">20 LB Coins</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>4️⃣ 4th place</span><strong>10 LB Coins</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>5️⃣ 5th place</span><strong>5 LB Coins</strong></div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
const BASE = '<?= BASE_URL ?>';
const activeMatchday = <?= $activeMatchday ?>;

function showToast(msg, ok = true) {
    const c = document.querySelector('.toast-container');
    if (!c) { alert(msg); return; }
    const el = document.createElement('div');
    el.className = 'toast align-items-center text-white border-0 show ' + (ok ? 'bg-success' : 'bg-danger');
    el.setAttribute('role','alert');
    el.innerHTML = `<div class="d-flex"><div class="toast-body fw-bold">${msg}</div></div>`;
    c.appendChild(el);
    setTimeout(() => el.remove(), 3200);
}

async function saveResult(btn, matchId) {
    const hs = document.getElementById('hs-' + matchId);
    const as = document.getElementById('as-' + matchId);
    if (!hs || !as || hs.value === '' || as.value === '') {
        showToast('Please enter both scores.', false); return;
    }
    btn.disabled = true;
    btn.textContent = '…';
    const fd = new FormData();
    fd.append('action', 'worldcup_admin_save_result');
    fd.append('match_id', matchId);
    fd.append('home_score', hs.value);
    fd.append('away_score', as.value);
    try {
        const res  = await fetch(BASE + '/ajax', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        showToast(data.message || 'Saved', data.success);
        if (data.success) {
            btn.classList.add('success');
            btn.textContent = 'Update';
            const row = document.getElementById('match-row-' + matchId);
            if (row) { row.classList.remove('locked'); row.classList.add('finished'); }
            // Show reset button if not already there
            if (!row.querySelector('.wca-reset-btn')) {
                const resetBtn = document.createElement('button');
                resetBtn.className = 'wca-reset-btn';
                resetBtn.title = 'Reset result';
                resetBtn.innerHTML = '<i class="fa-solid fa-rotate-left"></i>';
                resetBtn.onclick = () => resetResult(resetBtn, matchId);
                btn.parentNode.appendChild(resetBtn);
            }
        }
    } catch(e) { showToast('Error saving result.', false); }
    btn.disabled = false;
}

async function resetResult(btn, matchId) {
    if (!confirm('Reset this result? The match will be marked as not finished and all player points for this match will be removed.')) return;
    btn.disabled = true;
    const fd = new FormData();
    fd.append('action', 'worldcup_admin_reset_result');
    fd.append('match_id', matchId);
    try {
        const res  = await fetch(BASE + '/ajax', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        showToast(data.message || 'Result reset', data.success);
        if (data.success) {
            const row = document.getElementById('match-row-' + matchId);
            if (row) {
                row.classList.remove('finished', 'locked');
                // Clear score inputs
                const hs = document.getElementById('hs-' + matchId);
                const as = document.getElementById('as-' + matchId);
                if (hs) hs.value = '';
                if (as) as.value = '';
                // Reset save btn
                const saveBtn = row.querySelector('.wca-save-btn');
                if (saveBtn) { saveBtn.classList.remove('success'); saveBtn.textContent = 'Save result'; }
                // Remove reset btn
                btn.remove();
                // Update badge
                const badge = row.querySelector('.wca-badge');
                if (badge) { badge.className = 'wca-badge open'; badge.innerHTML = '<i class="fa-solid fa-pen"></i> Open'; }
            }
        }
    } catch(e) { showToast('Error resetting result.', false); }
    btn.disabled = false;
}

async function addMatch() {
    const home    = document.getElementById('newHome').value.trim();
    const away    = document.getElementById('newAway').value.trim();
    const kickoff = document.getElementById('newKickoff').value;
    const group   = document.getElementById('newGroup').value.trim();
    if (!home || !away || !kickoff) { showToast('Home, Away and Kickoff are required.', false); return; }
    const fd = new FormData();
    fd.append('action', 'worldcup_admin_add_match');
    fd.append('matchday', activeMatchday);
    fd.append('home_team', home);
    fd.append('away_team', away);
    fd.append('kickoff_at', kickoff);
    fd.append('group_name', group);
    try {
        const res  = await fetch(BASE + '/ajax', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        showToast(data.message || 'Match added', data.success);
        if (data.success) setTimeout(() => location.reload(), 800);
    } catch(e) { showToast('An error occurred.', false); }
}

async function runAutoLock(matchday) {
    const fd = new FormData();
    fd.append('action', 'worldcup_admin_auto_lock');
    fd.append('matchday', matchday);
    try {
        const res  = await fetch(BASE + '/ajax', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        showToast(data.message || 'Auto-lock applied', data.success);
        if (data.success) setTimeout(() => location.reload(), 900);
    } catch(e) { showToast('An error occurred.', false); }
}

async function lockAll(matchday) {
    if (!confirm('Lock all matches for this matchday? Players will no longer be able to submit predictions.')) return;
    const fd = new FormData();
    fd.append('action', 'worldcup_admin_lock_all');
    fd.append('matchday', matchday);
    try {
        const res  = await fetch(BASE + '/ajax', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        showToast(data.message || 'All locked', data.success);
        if (data.success) setTimeout(() => location.reload(), 900);
    } catch(e) { showToast('An error occurred.', false); }
}

async function unlockAll(matchday) {
    if (!confirm('Unlock all non-finished matches for this matchday? Players will be able to submit predictions again.')) return;
    const fd = new FormData();
    fd.append('action', 'worldcup_admin_unlock_all');
    fd.append('matchday', matchday);
    try {
        const res  = await fetch(BASE + '/ajax', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        showToast(data.message || 'All unlocked', data.success);
        if (data.success) setTimeout(() => location.reload(), 900);
    } catch(e) { showToast('An error occurred.', false); }
}

document.querySelectorAll('#btnAutoLock, #btnAutoLock2').forEach(btn =>
    btn.addEventListener('click', () => runAutoLock(btn.dataset.matchday))
);
document.getElementById('btnLockAll')?.addEventListener('click', function() {
    lockAll(this.dataset.matchday);
});
document.getElementById('btnUnlockAll')?.addEventListener('click', function() {
    unlockAll(this.dataset.matchday);
});
</script>
<?= $this->stop() ?>
