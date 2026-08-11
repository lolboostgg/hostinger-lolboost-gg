<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Booster Games | Admin Area']]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

// Preselect from URL params (e.g. when arriving from the booster performance tab)
$preBooster = (int)($booster ?? ($_GET['booster'] ?? 0));
$preMode    = in_array(($mode ?? ($_GET['mode'] ?? '')), ['solo', 'duo', 'all'], true) ? ($mode ?? ($_GET['mode'] ?? 'all')) : 'all';
$preSearch  = (string)($search ?? ($_GET['search'] ?? ''));

$reportedIds = [];
$reportedRaw = $reportedGameIds ?? ($_GET['reported_games'] ?? ($_GET['ids'] ?? ''));
if (is_array($reportedRaw)) {
    foreach ($reportedRaw as $rid) {
        $rid = (int)$rid;
        if ($rid > 0) $reportedIds[$rid] = $rid;
    }
} else {
    foreach (preg_split('/[^0-9]+/', (string)$reportedRaw) as $rid) {
        $rid = (int)$rid;
        if ($rid > 0) $reportedIds[$rid] = $rid;
    }
}
$reportedIds = array_values($reportedIds);
$reportedSet = array_flip($reportedIds);

$currentPage = max(1, (int)($page ?? ($_GET['page'] ?? 1)));
$perPage = max(10, min(100, (int)($limit ?? ($_GET['limit'] ?? 30))));
$totalRows = max(0, (int)($totalRows ?? count($rows ?? [])));
$totalPages = max(1, (int)($totalPages ?? ceil(max(1, $totalRows) / $perPage)));


// Self-contained enrich for rank/source fields when the controller query did not select them yet.
// Duo source logic: compare match PUUID against client PUUID and saved booster duo-account PUUID.
// Client-tracked duo rows match order_progress.puuid; booster duo-account rows match order_progress.booster_puuid.
// If the row PUUID is different from the client PUUID, treat it as booster stats even when booster_puuid is missing/stale.
// Templates do not always receive $db as a local variable, so also pull the global DB handle.
if (!isset($db) || !$db) {
    global $db;
}

if (!empty($rows) && isset($db) && $db) {
    $matchIds = [];
    $orderIds = [];
    foreach ($rows as $r) {
        $mid = (int)($r['id'] ?? 0);
        $oid = (int)($r['order_id'] ?? 0);
        if ($mid > 0) $matchIds[] = $mid;
        if ($oid > 0) $orderIds[] = $oid;
    }
    $matchIds = array_values(array_unique($matchIds));
    $orderIds = array_values(array_unique($orderIds));

    $matchExtra = [];
    if ($matchIds) {
        try {
            $cols = function_exists('riot_order_matches_columns') ? riot_order_matches_columns($db) : [];
            $select = ['id'];
            foreach (['rank_snapshot', 'play_mode', 'booster_id', 'is_hidden'] as $col) {
                if (!empty($cols[$col])) $select[] = $col;
            }
            if (!empty($cols['puuid'])) $select[] = 'puuid AS match_puuid';
            $ph = implode(',', array_fill(0, count($matchIds), '?'));
            $res = $db->run('SELECT ' . implode(', ', $select) . " FROM order_matches WHERE id IN ($ph)", ...$matchIds);
            foreach ((is_array($res) ? $res : []) as $m) {
                $matchExtra[(int)($m['id'] ?? 0)] = $m;
            }
        } catch (\Throwable $e) {}
    }

    $orderExtra = [];
    if ($orderIds) {
        try {
            $ph = implode(',', array_fill(0, count($orderIds), '?'));
            $res = $db->run(
                "SELECT oo.order_id,
                        oo.start_tier,
                        oo.start_division,
                        COALESCE(oo.is_duo, 0) AS is_duo,
                        op.puuid AS client_puuid,
                        op.booster_puuid,
                        op.booster_ign
                   FROM order_options oo
                   LEFT JOIN order_progress op ON op.order_id = oo.order_id
                  WHERE oo.order_id IN ($ph)",
                ...$orderIds
            );
            foreach ((is_array($res) ? $res : []) as $o) {
                $orderExtra[(int)($o['order_id'] ?? 0)] = $o;
            }
        } catch (\Throwable $e) {}
    }

    foreach ($rows as &$r) {
        $mid = (int)($r['id'] ?? 0);
        $oid = (int)($r['order_id'] ?? 0);
        if ($mid > 0 && isset($matchExtra[$mid])) {
            foreach ($matchExtra[$mid] as $k => $v) {
                if (in_array($k, ['match_puuid', 'rank_snapshot', 'play_mode', 'booster_id', 'is_hidden'], true)) {
                    $r[$k] = $v;
                } elseif (!array_key_exists($k, $r) || $r[$k] === null || $r[$k] === '') {
                    $r[$k] = $v;
                }
            }
        }
        if ($oid > 0 && isset($orderExtra[$oid])) {
            foreach ($orderExtra[$oid] as $k => $v) {
                if (!array_key_exists($k, $r) || $r[$k] === null || $r[$k] === '') $r[$k] = $v;
            }
        }
    }
    unset($r);

    // Fetch booster icons from boosters table in one query
    $boosterIds = [];
    foreach ($rows as $r) {
        $bid = (int)($r['booster_id'] ?? 0);
        if ($bid > 0) $boosterIds[] = $bid;
    }
    $boosterIds = array_values(array_unique($boosterIds));
    $boosterIcons = [];
    if ($boosterIds) {
        try {
            $ph = implode(',', array_fill(0, count($boosterIds), '?'));
            $res = $db->run("SELECT id, icon FROM boosters WHERE id IN ($ph)", ...$boosterIds);
            foreach ((is_array($res) ? $res : []) as $b) {
                $boosterIcons[(int)$b['id']] = (string)($b['icon'] ?? '');
            }
        } catch (\Throwable $e) {}
    }
    foreach ($rows as &$r) {
        $bid = (int)($r['booster_id'] ?? 0);
        $r['booster_icon'] = $boosterIcons[$bid] ?? '';
    }
    unset($r);
}

// Soft-hidden matches must not be shown in Booster Games.
// The backend action keeps the row for audit/duplicate prevention and sets is_hidden=1.
if (!empty($rows) && is_array($rows)) {
    $rows = array_values(array_filter($rows, function($r) {
        return (int)($r['is_hidden'] ?? 0) !== 1;
    }));
}

$rankTierNames = [
    0 => 'Unranked', 1 => 'Iron', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold',
    5 => 'Platinum', 6 => 'Emerald', 7 => 'Diamond', 8 => 'Master', 9 => 'Grandmaster', 10 => 'Challenger',
];

$rankTierFromSnapshot = function(string $snapshot): int {
    $s = strtoupper(trim($snapshot));
    if ($s === '' || str_starts_with($s, 'UNRANKED')) return 0;
    return match (true) {
        str_starts_with($s, 'IRON')        => 1,
        str_starts_with($s, 'BRONZE')      => 2,
        str_starts_with($s, 'SILVER')      => 3,
        str_starts_with($s, 'GOLD')        => 4,
        str_starts_with($s, 'PLATINUM')    => 5,
        str_starts_with($s, 'EMERALD')     => 6,
        str_starts_with($s, 'DIAMOND')     => 7,
        str_starts_with($s, 'MASTER')      => 8,
        str_starts_with($s, 'GRANDMASTER') => 9,
        str_starts_with($s, 'CHALLENGER')  => 10,
        default => 0,
    };
};

$rankDivisionFromSnapshot = function(string $snapshot): int {
    $s = strtoupper(trim($snapshot));
    if (preg_match('/\\b(IV|III|II|I)\\b/', $s, $m)) {
        return ['IV' => 1, 'III' => 2, 'II' => 3, 'I' => 4][$m[1]] ?? 1;
    }
    return 1;
};

$rankLabel = function(array $gm) use ($rankTierNames): string {
    $snapshot = trim((string)($gm['rank_snapshot'] ?? ''));
    if ($snapshot !== '') {
        $snapshot = str_replace('_', ' ', $snapshot);
        return ucwords(strtolower($snapshot));
    }

    $tier = null;
    foreach (['start_tier', 'tier', 'rank_tier'] as $key) {
        if (isset($gm[$key]) && $gm[$key] !== '') {
            $tier = (int)$gm[$key];
            break;
        }
    }

    return $tier !== null ? ($rankTierNames[$tier] ?? 'Unknown') : '—';
};

$rankSort = function(array $gm) use ($rankTierFromSnapshot): int {
    $snapshot = trim((string)($gm['rank_snapshot'] ?? ''));
    if ($snapshot !== '') return $rankTierFromSnapshot($snapshot);
    foreach (['start_tier', 'tier', 'rank_tier'] as $key) {
        if (isset($gm[$key]) && $gm[$key] !== '') return (int)$gm[$key];
    }
    return -1;
};

$rankIconUrl = function(array $gm) use ($rankTierFromSnapshot, $rankDivisionFromSnapshot): string {
    $snapshot = trim((string)($gm['rank_snapshot'] ?? ''));
    $tier = $snapshot !== '' ? $rankTierFromSnapshot($snapshot) : null;
    $division = $snapshot !== '' ? $rankDivisionFromSnapshot($snapshot) : null;

    if ($tier === null) {
        foreach (['start_tier', 'tier', 'rank_tier'] as $key) {
            if (isset($gm[$key]) && $gm[$key] !== '') {
                $tier = (int)$gm[$key];
                break;
            }
        }
    }
    if ($division === null) {
        foreach (['start_division', 'division', 'rank_division'] as $key) {
            if (isset($gm[$key]) && $gm[$key] !== '') {
                $division = (int)$gm[$key];
                break;
            }
        }
    }

    $tier = (int)($tier ?? 0);
    $division = max(1, min(4, (int)($division ?? 1)));
    if ($tier < 0) return '';

    if (function_exists('util_format_rank_img')) {
        return (string)util_format_rank_img($tier, $division, 'lol', 'mini');
    }
    return defined('ASSET_URL') ? ASSET_URL . '/core/main/img/lol/ranks/mini/' . $tier . '.png' : '';
};

$statSource = function(array $gm, bool $isDuo): string {
    if (!$isDuo) return 'booster';

    $explicit = strtolower(trim((string)($gm['stat_subject'] ?? '')));
    $matchPuuid   = strtolower(trim((string)($gm['match_puuid'] ?? $gm['puuid'] ?? '')));
    $clientPuuid  = strtolower(trim((string)($gm['client_puuid'] ?? $gm['order_puuid'] ?? '')));
    $boosterPuuid = strtolower(trim((string)($gm['booster_puuid'] ?? '')));

    // Source of truth for Duo display:
    // - Client stats only when the match PUUID is the client/order PUUID.
    // - Saved booster duo account is booster stats when it matches.
    // - Older booster duo accounts are also booster stats because their match PUUID differs from the client PUUID.
    if ($matchPuuid !== '' && $clientPuuid !== '' && hash_equals($clientPuuid, $matchPuuid)) {
        return 'client';
    }

    if ($matchPuuid !== '' && $boosterPuuid !== '' && hash_equals($boosterPuuid, $matchPuuid)) {
        return 'booster';
    }

    if ($matchPuuid !== '' && $clientPuuid !== '' && !hash_equals($clientPuuid, $matchPuuid)) {
        return 'booster';
    }

    // Only trust an explicit backend value when PUUID comparison was not possible.
    if (in_array($explicit, ['client', 'booster'], true)) return $explicit;

    // Legacy fallback: old duo rows without PUUID context were client-tracked.
    return 'client';
};
// Champion icon helper (same pattern used elsewhere in admin)
$champIcon = function(string $champ): string {
    if ($champ === '' || $champ === '—') return '';

    // Riot/Data Dragon uses the champion key "MonkeyKing" for Wukong.
    // Keep that key for the icon URL so the image still loads correctly.
    if ($champ === 'Wukong' || $champ === 'Monkey King') {
        $champ = 'MonkeyKing';
    }

    $slug = preg_replace('/[^a-zA-Z0-9]/', '', $champ);
    return 'https://ddragon.leagueoflegends.com/cdn/14.9.1/img/champion/' . $slug . '.png';
};

$champDisplayName = function(string $champ): string {
    return in_array($champ, ['MonkeyKing', 'Monkey King'], true) ? 'Wukong' : $champ;
};

// Queue label
// Prefer the central helper from functions.php when available. The fallback mirrors Riot queue IDs
// so Booster Games shows the same queue names as Performance/Leaderboard.
$queueLabel = function(int $qid): string {
    if (function_exists('util_format_lol_queue')) {
        return util_format_lol_queue($qid);
    }

    $queues = [
        0 => 'Custom',
        2 => 'Blind Pick',
        4 => 'Ranked Solo',
        6 => 'Ranked Premade',
        7 => 'Co-op vs AI',
        8 => '3v3 Normal',
        9 => '3v3 Ranked Flex',
        14 => 'Draft Pick',
        16 => 'Dominion Blind',
        17 => 'Dominion Draft',
        25 => 'Dominion Co-op vs AI',
        31 => 'Co-op vs AI Intro',
        32 => 'Co-op vs AI Beginner',
        33 => 'Co-op vs AI Intermediate',
        41 => '3v3 Ranked Team',
        42 => '5v5 Ranked Team',
        52 => '3v3 Co-op vs AI',
        61 => 'Team Builder',
        65 => 'ARAM',
        67 => 'ARAM Co-op vs AI',
        70 => 'One for All',
        72 => '1v1 Snowdown',
        73 => '2v2 Snowdown',
        75 => 'Hexakill',
        76 => 'URF',
        78 => 'One For All: Mirror',
        83 => 'Co-op vs AI URF',
        91 => 'Doom Bots Rank 1',
        92 => 'Doom Bots Rank 2',
        93 => 'Doom Bots Rank 5',
        96 => 'Ascension',
        98 => '3v3 Hexakill',
        100 => 'ARAM',
        300 => 'Poro King',
        310 => 'Nemesis',
        313 => 'Black Market Brawlers',
        315 => 'Nexus Siege',
        317 => 'Definitely Not Dominion',
        318 => 'ARURF',
        325 => 'All Random',
        400 => 'Normal Draft',
        410 => 'Ranked Dynamic',
        420 => 'Ranked Solo/Duo',
        430 => 'Normal Blind',
        440 => 'Ranked Flex',
        450 => 'ARAM',
        460 => '3v3 Blind Pick',
        470 => '3v3 Ranked Flex',
        480 => 'Swiftplay',
        490 => 'Quickplay',
        600 => 'Blood Hunt Assassin',
        610 => 'Dark Star: Singularity',
        700 => 'Clash',
        720 => 'ARAM Clash',
        800 => '3v3 Co-op vs AI Intermediate',
        810 => '3v3 Co-op vs AI Intro',
        820 => '3v3 Co-op vs AI Beginner',
        830 => 'Co-op vs AI Intro',
        840 => 'Co-op vs AI Beginner',
        850 => 'Co-op vs AI Intermediate',
        870 => 'Co-op vs AI Intro',
        880 => 'Co-op vs AI Beginner',
        890 => 'Co-op vs AI Intermediate',
        900 => 'ARURF',
        910 => 'Ascension',
        920 => 'Poro King',
        940 => 'Nexus Siege',
        950 => 'Doom Bots Voting',
        960 => 'Doom Bots Standard',
        980 => 'Star Guardian: Normal',
        990 => 'Star Guardian: Onslaught',
        1000 => 'PROJECT: Hunters',
        1010 => 'Snow ARURF',
        1020 => 'One for All',
        1030 => 'Odyssey: Intro',
        1040 => 'Odyssey: Cadet',
        1050 => 'Odyssey: Crewmember',
        1060 => 'Odyssey: Captain',
        1070 => 'Odyssey: Onslaught',
        1090 => 'TFT',
        1100 => 'Ranked TFT',
        1110 => 'TFT Tutorial',
        1111 => 'TFT Test',
        1200 => 'Nexus Blitz',
        1210 => "TFT Choncc's Treasure",
        1300 => 'Nexus Blitz',
        1400 => 'Ultimate Spellbook',
        1700 => 'Arena',
        1710 => 'Arena',
        1810 => 'Swarm Solo',
        1820 => 'Swarm Duo',
        1830 => 'Swarm Trio',
        1840 => 'Swarm Squad',
        1900 => 'Pick URF',
        2000 => 'Tutorial 1',
        2010 => 'Tutorial 2',
        2020 => 'Tutorial 3',
        2300 => 'Brawl',
        2400 => 'ARAM: Mayhem',
    ];

    return $queues[$qid] ?? ($qid > 0 ? 'Queue ' . $qid : '—');
};

$totalGames = $totalRows;
?>

<?= $this->start('styles') ?>
<style>
/* ── Shared checkbox ── */
.bg-chk{appearance:none;-webkit-appearance:none;width:17px;height:17px;border-radius:5px;border:1.5px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);cursor:pointer;flex-shrink:0;position:relative;transition:background .12s,border-color .12s;display:inline-block;vertical-align:middle;}
.bg-chk:hover{border-color:rgba(109,92,255,.6);background:rgba(109,92,255,.12);}
.bg-chk:checked{background:#6d5cff;border-color:#6d5cff;}
.bg-chk:checked::after{content:'';position:absolute;left:4px;top:1.5px;width:5px;height:9px;border:2px solid #fff;border-top:0;border-left:0;transform:rotate(45deg);}
.bg-chk:indeterminate{background:rgba(109,92,255,.4);border-color:rgba(109,92,255,.7);}
.bg-chk:indeterminate::after{content:'';position:absolute;left:3px;top:6.5px;width:9px;height:2px;background:#fff;border-radius:1px;}

/* ── Pills (Solo/Duo/All) ── */
.bg-pills{display:flex;gap:6px;flex-wrap:wrap;}
.bg-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none;}
.bg-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.bg-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.bg-pill[data-mode="solo"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80;}
.bg-pill[data-mode="duo"].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.35);color:#facc15;}
.bg-pill[data-mode="all"].active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}

/* ── Search ── */
.bg-search-wrap{position:relative;}
.bg-search-wrap input{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:240px;transition:border-color .15s,box-shadow .15s;}
.bg-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important;}
.bg-search-wrap input::placeholder{color:rgba(255,255,255,.25)!important;}
.bg-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none;}

/* ── Booster custom dropdown ── */
.bg-booster-dd{position:relative;flex-shrink:0;}
.bg-booster-dd-trigger{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:rgba(255,255,255,.75);padding:7px 12px;font-size:.84rem;cursor:pointer;transition:border-color .15s,background .15s;white-space:nowrap;min-width:160px;font-family:inherit;}
.bg-booster-dd-trigger:hover{border-color:rgba(109,92,255,.35);background:rgba(109,92,255,.07);}
.bg-booster-dd-trigger:focus{outline:none;border-color:rgba(109,92,255,.45);box-shadow:0 0 0 3px rgba(109,92,255,.10);}
.bg-booster-dd-trigger.is-open{border-color:rgba(109,92,255,.45);background:rgba(109,92,255,.08);}
.bg-booster-dd-icon-wrap{width:22px;height:22px;border-radius:6px;background:rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
.bg-booster-dd-icon-wrap img{width:100%;height:100%;object-fit:cover;border-radius:6px;}
.bg-booster-dd-chevron{margin-left:auto;font-size:.6rem;color:rgba(255,255,255,.3);transition:transform .18s;}
.bg-booster-dd-trigger.is-open .bg-booster-dd-chevron{transform:rotate(180deg);}
.bg-booster-dd-menu{position:absolute;top:calc(100% + 6px);left:0;z-index:9999;background:#1e2124;border:1px solid rgba(255,255,255,.11);border-radius:14px;width:230px;box-shadow:0 12px 40px rgba(0,0,0,.5),0 2px 8px rgba(0,0,0,.3);overflow:hidden;opacity:0;transform:translateY(-6px) scale(.97);pointer-events:none;transition:opacity .15s,transform .15s;}
.bg-booster-dd-menu.is-open{opacity:1;transform:translateY(0) scale(1);pointer-events:auto;}
.bg-booster-dd-search-wrap{position:relative;padding:10px 10px 6px;}
.bg-booster-dd-search{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:rgba(255,255,255,.85);padding:7px 10px 7px 36px;font-size:.8rem;font-family:inherit;transition:border-color .15s;box-sizing:border-box;}
.bg-booster-dd-search:focus{outline:none;border-color:rgba(109,92,255,.4);box-shadow:0 0 0 2px rgba(109,92,255,.10);}
.bg-booster-dd-search::placeholder{color:rgba(255,255,255,.22);}
.bg-booster-dd-search-icon{position:absolute;left:20px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);font-size:.75rem;pointer-events:none;line-height:1;}
.bg-booster-dd-list{max-height:240px;overflow-y:auto;padding:4px 6px 8px;display:flex;flex-direction:column;gap:1px;}
.bg-booster-dd-list::-webkit-scrollbar{width:4px;}
.bg-booster-dd-list::-webkit-scrollbar-track{background:transparent;}
.bg-booster-dd-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:99px;}
.bg-booster-dd-item{display:flex;align-items:center;gap:9px;padding:7px 9px;border-radius:8px;cursor:pointer;transition:background .1s;color:rgba(255,255,255,.7);font-size:.83rem;}
.bg-booster-dd-item:hover{background:rgba(255,255,255,.06);color:rgba(255,255,255,.9);}
.bg-booster-dd-item--active{background:rgba(109,92,255,.15);color:#c4b5fd;}
.bg-booster-dd-item--active:hover{background:rgba(109,92,255,.22);}
.bg-booster-dd-item--hidden{display:none;}
.bg-booster-dd-item-icon{width:24px;height:24px;border-radius:7px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
.bg-booster-dd-item-icon img{width:100%;height:100%;object-fit:cover;border-radius:7px;}
.bg-booster-dd-item-name{font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.bg-booster-dd-empty{padding:20px;text-align:center;color:rgba(255,255,255,.3);font-size:.8rem;}

/* ── Hero card ── */
.bg-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.bg-hero-left{display:flex;align-items:center;gap:14px;}
.bg-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.bg-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.bg-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}

/* ── Stat chips ── */
.bg-stat-row{display:flex;gap:10px;flex-wrap:wrap;}
.bg-stat{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.03);font-size:.78rem;color:rgba(255,255,255,.45);}
.bg-stat strong{color:rgba(255,255,255,.85);font-weight:800;}

/* ── Toolbar ── */
.bg-toolbar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}

/* ── Table ── */
.bg-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:visible;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);}
.bg-table{width:100%;border-collapse:collapse;border-radius:20px;overflow:hidden;display:table;}
.bg-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.bg-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;user-select:none;}
.bg-table thead th.sortable{cursor:pointer;}
.bg-table thead th.sortable:hover{color:rgba(255,255,255,.7);}
.bg-table thead th .sort-icon{margin-left:4px;opacity:.35;font-size:.6rem;}
.bg-table thead th.sort-asc .sort-icon,.bg-table thead th.sort-desc .sort-icon{opacity:1;color:#c4b5fd;}
.bg-table tbody .bg-row{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.bg-table tbody .bg-row:last-child{border-bottom:none;}
.bg-table tbody .bg-row:hover{background:rgba(109,92,255,.06);}
.bg-table tbody td{padding:11px 16px;vertical-align:middle;font-size:.84rem;color:rgba(255,255,255,.8);}

/* ── Cell styles ── */
.bg-col-id{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);font-variant-numeric:tabular-nums;}
.bg-champ-wrap{display:flex;align-items:center;gap:8px;}
.bg-champ-img{width:28px;height:28px;border-radius:6px;object-fit:cover;background:rgba(255,255,255,.04);flex-shrink:0;}
.bg-champ-name{font-size:.84rem;font-weight:700;color:rgba(255,255,255,.85);}
.bg-kda{font-size:.8rem;font-variant-numeric:tabular-nums;color:rgba(255,255,255,.6);}
.bg-kda strong{color:rgba(255,255,255,.88);font-weight:800;}
.bg-result-win{display:inline-flex;align-items:center;gap:.3rem;padding:3px 9px;border-radius:99px;font-size:.71rem;font-weight:800;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;}
.bg-result-loss{display:inline-flex;align-items:center;gap:.3rem;padding:3px 9px;border-radius:99px;font-size:.71rem;font-weight:800;background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185;}
.bg-result-remake{display:inline-flex;align-items:center;gap:.3rem;padding:3px 9px;border-radius:99px;font-size:.71rem;font-weight:800;background:rgba(148,163,184,.12);border:1px solid rgba(148,163,184,.25);color:#94a3b8;}
.bg-duo-badge{display:inline-flex;align-items:center;gap:.25rem;padding:2px 7px;border-radius:99px;font-size:.65rem;font-weight:800;background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.25);color:#facc15;margin-left:4px;}
.bg-rank-cell{display:flex;align-items:center;gap:7px;flex-wrap:nowrap;white-space:nowrap;min-width:max-content;}
.bg-rank-badge{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;padding:0;border-radius:10px;background:rgba(109,92,255,.10);border:1px solid rgba(109,92,255,.24);white-space:nowrap;}
.bg-rank-badge.empty{background:rgba(148,163,184,.08);border-color:rgba(148,163,184,.18);color:rgba(255,255,255,.32);font-size:.75rem;}
.bg-rank-icon{width:23px;height:23px;object-fit:contain;display:block;filter:drop-shadow(0 2px 4px rgba(0,0,0,.25));}
.bg-table td:nth-child(7){white-space:nowrap;}
.bg-source-badge{display:inline-flex;align-items:center;gap:.25rem;padding:2px 7px;border-radius:99px;font-size:.64rem;font-weight:850;white-space:nowrap;}
.bg-source-client{background:rgba(96,165,250,.10);border:1px solid rgba(96,165,250,.24);color:#93c5fd;}
.bg-source-booster{background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.24);color:#86efac;}
.bg-booster-link{color:#c4b5fd;text-decoration:none;font-weight:700;font-size:.82rem;}
.bg-booster-link:hover{color:#fff;text-decoration:underline;}
.bg-order-link{color:rgba(255,255,255,.5);text-decoration:none;font-size:.78rem;font-weight:700;}
.bg-order-link:hover{color:#c4b5fd;}
.bg-date{font-size:.78rem;color:rgba(255,255,255,.38);font-variant-numeric:tabular-nums;}
.bg-duration{font-size:.78rem;color:rgba(255,255,255,.38);font-variant-numeric:tabular-nums;}
.bg-queue{font-size:.74rem;color:rgba(255,255,255,.38);}

/* ── Hide button ── */
.bg-del-btn{width:30px;height:30px;border-radius:8px;border:1px solid rgba(251,113,133,.2);background:rgba(251,113,133,.07);color:#fb7185;font-size:.75rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .12s,border-color .12s;}
.bg-del-btn:hover{background:rgba(251,113,133,.16);border-color:rgba(251,113,133,.4);}
.bg-del-btn:disabled{opacity:.4;cursor:not-allowed;}

/* ── Bulk hide bar ── */
#bgBulkBar{display:none;align-items:center;gap:.4rem;padding:6px 14px;border-radius:10px;background:rgba(251,113,133,.14);border:1px solid rgba(251,113,133,.28);color:#fb7185;font-size:.8rem;font-weight:800;cursor:pointer;}
.bg-report-notice{display:flex;align-items:center;gap:.55rem;padding:10px 14px;margin:0 0 14px;border-radius:12px;background:rgba(251,113,133,.10);border:1px solid rgba(251,113,133,.24);color:#fecdd3;font-size:.84rem;font-weight:800;}
.bg-report-notice strong{color:#fff;}
.bg-row.is-reported{background:rgba(251,113,133,.045);}
.bg-row.is-reported td:first-child{box-shadow:4px 0 0 rgba(251,113,133,.9) inset;}
.bg-reported-badge{display:inline-flex;align-items:center;gap:.25rem;margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.25);color:#fb7185;font-size:.62rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;}

/* ── Empty ── */
.bg-empty{text-align:center;padding:64px 24px;color:rgba(255,255,255,.35);}
.bg-empty i{font-size:3rem;margin-bottom:12px;display:block;opacity:.3;}

/* ── Pagination ── */
.bg-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0;}
.bg-pg-btn{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s;}
.bg-pg-btn:hover:not(:disabled){background:rgba(255,255,255,.09);}
.bg-pg-btn.bg-pg-active{background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.bg-pg-btn:disabled{opacity:.35;cursor:not-allowed;}

@media only screen and (max-width:1200px){.bg-table-wrap{overflow-x:auto;}.bg-table{min-width:1160px;}}
</style>
<?= $this->end() ?>


<div class="al-page">

  <!-- Hero -->
  <div class="bg-hero">
    <div class="bg-hero-left">
      <div class="bg-hero-icon"><i class="fa-duotone fa-swords"></i></div>
      <div>
        <h2 class="bg-hero-title">Booster Games</h2>
        <p class="bg-hero-sub">All played games from solo &amp; duo orders</p>
      </div>
    </div>
    <div class="bg-stat-row">
      <div class="bg-stat"><i class="fa-solid fa-gamepad-modern" style="color:#6d5cff;"></i> Total: <strong id="bgStatTotal"><?= $totalGames ?></strong></div>
      <div class="bg-stat"><i class="fa-solid fa-check" style="color:#4ade80;"></i> Wins: <strong id="bgStatWins"><?= array_sum(array_column(array_filter($rows, fn($r) => (int)($r['won']??0)===1 && !(int)($r['is_remake']??0)), 'won')) ?></strong></div>
      <div class="bg-stat"><i class="fa-solid fa-xmark" style="color:#fb7185;"></i> Losses: <strong id="bgStatLosses"><?= count(array_filter($rows, fn($r) => (int)($r['won']??0)===0 && !(int)($r['is_remake']??0))) ?></strong></div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="bg-toolbar">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1;">
      <!-- Mode pills -->
      <div class="bg-pills" id="bgModePills">
        <span class="bg-pill <?= $preMode === 'all' ? 'active' : '' ?>" data-mode="all"><i class="fa-solid fa-layer-group" style="font-size:.7rem;"></i> All</span>
        <span class="bg-pill <?= $preMode === 'solo' ? 'active' : '' ?>" data-mode="solo"><i class="fa-solid fa-user" style="font-size:.7rem;"></i> Solo</span>
        <span class="bg-pill <?= $preMode === 'duo' ? 'active' : '' ?>" data-mode="duo"><i class="fa-solid fa-users" style="font-size:.7rem;"></i> Duo</span>
      </div>

      <!-- Custom Booster Dropdown -->
      <div class="bg-booster-dd" id="bgBoosterDd">
        <button type="button" class="bg-booster-dd-trigger" id="bgBoosterDdTrigger" aria-haspopup="listbox" aria-expanded="false">
          <span class="bg-booster-dd-icon-wrap" id="bgBoosterDdIconWrap">
            <i class="fa-solid fa-users-line" style="font-size:.72rem;color:rgba(255,255,255,.4);"></i>
          </span>
          <span id="bgBoosterDdLabel">All Boosters</span>
          <i class="fa-solid fa-chevron-down bg-booster-dd-chevron" id="bgBoosterDdChevron"></i>
        </button>
        <div class="bg-booster-dd-menu" id="bgBoosterDdMenu" role="listbox" aria-label="Filter by booster">
          <div class="bg-booster-dd-search-wrap">
            <i class="fa-solid fa-magnifying-glass bg-booster-dd-search-icon"></i>
            <input type="text" class="bg-booster-dd-search" id="bgBoosterDdSearch" placeholder="Search booster…" autocomplete="off" spellcheck="false">
          </div>
          <div class="bg-booster-dd-list" id="bgBoosterDdList" role="group">
            <div class="bg-booster-dd-item <?= $preBooster === 0 ? 'bg-booster-dd-item--active' : '' ?>" data-value="all" role="option" aria-selected="<?= $preBooster === 0 ? 'true' : 'false' ?>">
              <span class="bg-booster-dd-item-icon">
                <i class="fa-solid fa-users-line" style="font-size:.75rem;color:rgba(255,255,255,.5);"></i>
              </span>
              <span class="bg-booster-dd-item-name">All Boosters</span>
            </div>
            <?php foreach (($boosters ?? []) as $b):
              $bid = (int)($b['id'] ?? 0);
              if ($bid <= 0) continue;
              $bName = (string)($b['username'] ?? ('Booster #' . $bid));
              $bIcon = (string)($b['icon'] ?? '');
            ?>
              <div class="bg-booster-dd-item <?= $preBooster === $bid ? 'bg-booster-dd-item--active' : '' ?>" data-value="<?= $bid ?>" data-icon="<?= $h($bIcon) ?>" role="option" aria-selected="<?= $preBooster === $bid ? 'true' : 'false' ?>">
                <span class="bg-booster-dd-item-icon">
                  <?php if ($bIcon !== ''): ?>
                    <img src="<?= $h($bIcon) ?>" alt="<?= $h($bName) ?>" onerror="this.parentNode.innerHTML = '<i class=&quot;fa-solid fa-user&quot; style=&quot;font-size:.7rem;color:rgba(255,255,255,.45);&quot;></i>'">
                  <?php else: ?>
                    <i class="fa-solid fa-user" style="font-size:.7rem;color:rgba(255,255,255,.45);"></i>
                  <?php endif; ?>
                </span>
                <span class="bg-booster-dd-item-name"><?= $h($bName) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="bg-booster-dd-empty" id="bgBoosterDdEmpty" style="display:none;">No boosters found</div>
        </div>
      </div>

      <!-- Bulk hide button -->
      <button type="button" id="bgBulkBar">
        <i class="fa-duotone fa-eye-slash"></i> Hide selected (<span id="bgBulkCount">0</span>)
      </button>
    </div>

    <!-- Search -->
    <div class="bg-search-wrap">
      <i class="fa-solid fa-magnifying-glass bg-search-icon"></i>
      <input type="search" id="bgSearch" placeholder="Search champion, booster, order…" value="<?= $h($preSearch) ?>">
    </div>
  </div>

  <?php if (!empty($reportedIds)): ?>
    <div class="bg-report-notice">
      <i class="fa-duotone fa-flag"></i>
      <span><strong><?= count($reportedIds) ?></strong> reported game<?= count($reportedIds) === 1 ? '' : 's' ?> opened from Discord, already selected below.</span>
    </div>
  <?php endif; ?>

  <!-- Table -->
  <div class="bg-table-wrap">
    <table class="bg-table" id="bgGrid">
      <thead>
        <tr>
          <th style="width:36px;padding:10px 8px;">
            <input type="checkbox" id="bgChkAll" class="bg-chk" aria-label="Select all">
          </th>
          <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Champion</th>
          <th class="sortable" data-col="kda">KDA <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Result</th>
          <th>Queue</th>
          <th class="sortable" data-col="rank">Rank <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="duration">Duration <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Booster</th>
          <th>Order</th>
          <th class="sortable" data-col="date">Played At <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody id="bgTbody">
        <?php if (!empty($rows)): foreach ($rows as $gm):
          $playMode   = strtolower(trim((string)($gm['play_mode'] ?? '')));
          $isDuo      = ((int)($gm['is_duo'] ?? 0) === 1) || $playMode === 'duo';
          $source     = $statSource($gm, $isDuo);
          $sourceLabel= $source === 'booster' ? 'Booster stats' : 'Client stats';
          $sourceIcon = $source === 'booster' ? 'fa-user-check' : 'fa-user';
          $rankText   = $rankLabel($gm);
          $rankIcon   = $rankIconUrl($gm);
          $rankSortVal= $rankSort($gm);
          $isRemake   = (int)($gm['is_remake'] ?? 0) === 1;
          $won        = (int)($gm['won'] ?? 0) === 1;
          $kills      = (int)($gm['kills'] ?? 0);
          $deaths     = (int)($gm['deaths'] ?? 0);
          $assists    = (int)($gm['assists'] ?? 0);
          $kdaVal     = $deaths > 0 ? round(($kills + $assists) / $deaths, 2) : ($kills + $assists);
          $champion   = $gm['champion'] ?? '';
          $championDisplay = $champDisplayName((string)$champion);
          $boosterId  = (int)($gm['booster_id'] ?? 0);
          $boosterName= $gm['booster_username'] ?? '—';
          $boosterIcon= !empty($gm['booster_icon']) ? $gm['booster_icon'] : '';
          $orderId    = (int)($gm['order_id'] ?? 0);
          $queueId    = (int)($gm['queue_id'] ?? 0);
          $queueText  = $queueLabel($queueId);
          $playedTs   = !empty($gm['played_at']) ? strtotime($gm['played_at']) : 0;
          $playedFmt  = $playedTs ? date('d.m.Y H:i', $playedTs) : '—';
          $durSec     = (int)($gm['duration'] ?? 0);
          $durFmt     = $durSec > 0 ? sprintf('%d:%02d', intdiv($durSec,60), $durSec%60) : '—';
          $imgUrl     = $champIcon($champion);
          $searchStr  = strtolower(($gm['match_id']??'').' '.($champion).' '.($championDisplay).' '.($boosterName).' '.$orderId.' '.$rankText.' '.$sourceLabel.' '.$queueText.' '.$queueId);
          $isReported = isset($reportedSet[(int)$gm['id']]);
        ?>
        <tr class="bg-row <?= $isReported ? 'is-reported' : '' ?>"
            data-id="<?= (int)$gm['id'] ?>"
            data-mode="<?= $isDuo ? 'duo' : 'solo' ?>"
            data-booster="<?= $boosterId ?>"
            data-booster-name="<?= $h($boosterName) ?>"
            data-booster-icon="<?= $h($boosterIcon) ?>"
            data-search="<?= $h($searchStr) ?>"
            data-kda="<?= $kdaVal ?>"
            data-duration="<?= $durSec ?>"
            data-rank="<?= $rankSortVal ?>"
            data-date="<?= $playedTs ?>">

          <td style="padding:10px 8px;vertical-align:middle;" onclick="event.stopPropagation()">
            <input type="checkbox" class="bg-row-chk bg-chk" value="<?= (int)$gm['id'] ?>" <?= $isReported ? 'checked' : '' ?>>
          </td>

          <td><span class="bg-col-id">#<?= (int)$gm['id'] ?></span><?php if ($isReported): ?><span class="bg-reported-badge"><i class="fa-solid fa-flag"></i> Reported</span><?php endif; ?></td>

          <td>
            <div class="bg-champ-wrap">
              <?php if ($imgUrl): ?>
                <img class="bg-champ-img" src="<?= $h($imgUrl) ?>" alt="<?= $h($championDisplay) ?>" onerror="this.style.display='none'">
              <?php endif; ?>
              <div>
                <span class="bg-champ-name"><?= $championDisplay !== '' ? $h($championDisplay) : '—' ?></span>
                <?php if ($isDuo): ?>
                  <span class="bg-duo-badge"><i class="fa-solid fa-users" style="font-size:.6rem;"></i> Duo</span>
                <?php endif; ?>
              </div>
            </div>
          </td>

          <td>
            <span class="bg-kda">
              <strong><?= $kills ?></strong>/<strong><?= $deaths ?></strong>/<strong><?= $assists ?></strong>
              <span style="margin-left:5px;color:rgba(255,255,255,.28);"><?= number_format($kdaVal, 2) ?> KDA</span>
            </span>
          </td>

          <td>
            <?php if ($isRemake): ?>
              <span class="bg-result-remake"><i class="fa-solid fa-rotate-left" style="font-size:.65rem;"></i> Remake</span>
            <?php elseif ($won): ?>
              <span class="bg-result-win"><i class="fa-solid fa-check" style="font-size:.65rem;"></i> Win</span>
            <?php else: ?>
              <span class="bg-result-loss"><i class="fa-solid fa-xmark" style="font-size:.65rem;"></i> Loss</span>
            <?php endif; ?>
          </td>

          <td><span class="bg-queue"><?= $h($queueText) ?></span></td>

          <td>
            <div class="bg-rank-cell">
              <?php if ($rankIcon): ?>
                <span class="bg-rank-badge" title="<?= $h($rankText) ?>" aria-label="<?= $h($rankText) ?>">
                  <img class="bg-rank-icon" src="<?= $h($rankIcon) ?>" alt="<?= $h($rankText) ?>" onerror="var p=this.closest('.bg-rank-badge');p.classList.add('empty');p.textContent='<?= $rankSortVal === 0 ? 'UR' : '—' ?>';this.remove();">
                </span>
              <?php else: ?>
                <span class="bg-rank-badge empty" title="<?= $h($rankText) ?>" aria-label="<?= $h($rankText) ?>">—</span>
              <?php endif; ?>
              <?php if ($isDuo): ?>
                <span class="bg-source-badge <?= $source === 'booster' ? 'bg-source-booster' : 'bg-source-client' ?>" title="<?= $source === 'booster' ? 'Duo game from booster duo account' : 'Duo game from client Riot ID/account' ?>">
                  <i class="fa-solid <?= $sourceIcon ?>" style="font-size:.58rem;"></i> <?= $h($sourceLabel) ?>
                </span>
              <?php endif; ?>
            </div>
          </td>

          <td><span class="bg-duration"><?= $h($durFmt) ?></span></td>

          <td>
            <?php if ($boosterId && $boosterName !== '—'): ?>
              <a class="bg-booster-link" href="<?= ADMN_URL ?>/booster/<?= $boosterId ?>/profile">
                <?= $h($boosterName) ?>
              </a>
            <?php else: ?>
              <span style="color:rgba(255,255,255,.2);">—</span>
            <?php endif; ?>
          </td>

          <td>
            <?php if ($orderId): ?>
              <a class="bg-order-link" href="<?= ADMN_URL ?>/order/<?= $orderId ?>">
                #<?= $orderId ?>
              </a>
            <?php else: ?>
              <span style="color:rgba(255,255,255,.2);">—</span>
            <?php endif; ?>
          </td>

          <td><span class="bg-date"><?= $h($playedFmt) ?></span></td>

          <td class="text-end">
            <button type="button" class="bg-del-btn js-bg-delete" data-id="<?= (int)$gm['id'] ?>" title="Hide game from stats">
              <i class="fa-solid fa-eye-slash"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="12">
          <div class="bg-empty">
            <i class="fa-duotone fa-swords"></i>
            <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No games found</div>
            <div style="font-size:.82rem;">Games are recorded automatically when boosters submit match results.</div>
          </div>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Footer / Pagination -->
  <div class="bg-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
      Showing <span id="bgShowing">—</span> of <span id="bgTotal">—</span>
    </div>
    <div style="display:flex;gap:5px;flex-wrap:wrap;" id="bgPagination"></div>
  </div>

</div>


<?= $this->start('scripts') ?>
<script>
// ── Single hide ───────────────────────────────────────────
document.addEventListener('click', function(e) {
  var btn = e.target.closest('.js-bg-delete');
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  if (!confirm('Hide this game from match history, leaderboards and booster stats?')) return;

  btn.disabled = true;

  $.ajax({
    type: 'POST',
    url: '<?= AJAX_URL ?>',
    data: {
      action: 'admin_delete_order_match',
      match_id: btn.dataset.id
    },
    success: function(resp) {
      var d = resp;

      try {
        if (typeof resp === 'string') d = JSON.parse(resp);
      } catch (err) {
        console.error('Invalid hide JSON response:', resp);
        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Invalid AJAX response.');
        btn.disabled = false;
        return;
      }

      if (d && d.sendToast && typeof create_toast === 'function') {
        create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
      }

      if (d && (d.ok || d.success)) {
        var row = btn.closest('tr.bg-row');
        if (row) row.remove();
        if (window.bgFilter && typeof bgFilter.refresh === 'function') bgFilter.refresh();
      } else {
        btn.disabled = false;
      }
    },
    error: function(xhr) {
      console.error('Hide game AJAX error:', xhr.responseText);
      if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not hide game.');
      btn.disabled = false;
    }
  });
}, true);

// ── Bulk + server-side filtering + pagination ─────────────
var bgFilter = (function() {
  var CURRENT_PAGE = <?= (int)$currentPage ?>;
  var TOTAL_ROWS   = <?= (int)$totalRows ?>;
  var TOTAL_PAGES  = <?= (int)$totalPages ?>;
  var PER_PAGE     = <?= (int)$perPage ?>;
  var mode         = <?= json_encode($preMode) ?>;
  var boosterId    = <?= json_encode($preBooster > 0 ? (string)$preBooster : 'all') ?>;
  var search       = <?= json_encode($preSearch) ?>;
  var selected     = new Set((<?= json_encode(array_map('strval', $reportedIds)) ?> || []).map(String));
  var reportedIds  = <?= json_encode(array_values($reportedIds)) ?>;

  var tbody     = document.getElementById('bgTbody');
  var showEl    = document.getElementById('bgShowing');
  var totEl     = document.getElementById('bgTotal');
  var pageEl    = document.getElementById('bgPagination');
  var srchEl    = document.getElementById('bgSearch');
  var pills     = document.querySelectorAll('#bgModePills .bg-pill');
  var $bulkBar  = document.getElementById('bgBulkBar');
  var $bulkCnt  = document.getElementById('bgBulkCount');
  var $chkAll   = document.getElementById('bgChkAll');

  function allRows() {
    return tbody ? Array.from(tbody.querySelectorAll('.bg-row')) : [];
  }

  function goToPage(targetPage) {
    var params = new URLSearchParams(window.location.search);
    params.set('page', Math.max(1, parseInt(targetPage || 1, 10)));
    params.set('limit', PER_PAGE);
    params.set('mode', mode || 'all');

    if (boosterId && boosterId !== 'all') params.set('booster', boosterId);
    else params.delete('booster');

    if (search && search.trim() !== '') params.set('search', search.trim());
    else params.delete('search');

    if (reportedIds && reportedIds.length) params.set('reported_games', reportedIds.join(','));
    else params.delete('reported_games');

    window.location.href = window.location.pathname + '?' + params.toString();
  }

  function updateCounters() {
    var rows = allRows();
    var start = TOTAL_ROWS > 0 ? ((CURRENT_PAGE - 1) * PER_PAGE) + 1 : 0;
    var end = TOTAL_ROWS > 0 ? Math.min(CURRENT_PAGE * PER_PAGE, TOTAL_ROWS) : 0;

    if (showEl) showEl.textContent = TOTAL_ROWS > 0 ? (start + '–' + end) : '0';
    if (totEl)  totEl.textContent = TOTAL_ROWS;

    document.getElementById('bgStatTotal').textContent = TOTAL_ROWS;
    document.getElementById('bgStatWins').textContent = rows.filter(function(r){ return r.querySelector('.bg-result-win'); }).length;
    document.getElementById('bgStatLosses').textContent = rows.filter(function(r){ return r.querySelector('.bg-result-loss'); }).length;
  }

  function renderPagination() {
    if (!pageEl) return;
    pageEl.innerHTML = '';
    if (TOTAL_PAGES <= 1) return;

    function btn(label, p, disabled, active) {
      var b = document.createElement('button');
      b.className = 'bg-pg-btn' + (active ? ' bg-pg-active' : '');
      b.innerHTML = label;
      b.disabled = !!disabled;
      if (!disabled && !active) b.addEventListener('click', function(){ goToPage(p); });
      return b;
    }

    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>', CURRENT_PAGE - 1, CURRENT_PAGE === 1, false));
    for (var i = 1; i <= TOTAL_PAGES; i++) {
      if (TOTAL_PAGES > 7 && i > 2 && i < TOTAL_PAGES - 1 && Math.abs(i - CURRENT_PAGE) > 1) {
        if (i === 3 || i === TOTAL_PAGES - 2) {
          var d = document.createElement('span');
          d.style.cssText = 'color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;';
          d.textContent = '…';
          pageEl.appendChild(d);
        }
        continue;
      }
      pageEl.appendChild(btn(i, i, false, i === CURRENT_PAGE));
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>', CURRENT_PAGE + 1, CURRENT_PAGE === TOTAL_PAGES, false));
  }

  function pruneSelection() {
    Array.from(selected).forEach(function(id){
      if (!tbody.querySelector('tr.bg-row[data-id="' + id + '"]')) selected.delete(String(id));
    });
  }

  function updateBulk() {
    pruneSelection();
    var n = selected.size;
    if ($bulkCnt) $bulkCnt.textContent = n;
    if ($bulkBar) $bulkBar.style.display = n > 0 ? 'inline-flex' : 'none';

    var visible = allRows();
    var chkable = visible.filter(function(r) { return !r.querySelector('.bg-row-chk').disabled; });
    var checkedN = chkable.filter(function(r) { return selected.has(String(r.dataset.id)); }).length;
    if (!$chkAll) return;
    if (!chkable.length || checkedN === 0) { $chkAll.checked = false; $chkAll.indeterminate = false; }
    else if (checkedN === chkable.length) { $chkAll.checked = true; $chkAll.indeterminate = false; }
    else { $chkAll.checked = false; $chkAll.indeterminate = true; }
  }

  // Custom Booster Dropdown
  (function(){
    var ddWrap    = document.getElementById('bgBoosterDd');
    var trigger   = document.getElementById('bgBoosterDdTrigger');
    var menu      = document.getElementById('bgBoosterDdMenu');
    var list      = document.getElementById('bgBoosterDdList');
    var searchInp = document.getElementById('bgBoosterDdSearch');
    var labelEl   = document.getElementById('bgBoosterDdLabel');
    var iconWrap  = document.getElementById('bgBoosterDdIconWrap');
    var emptyEl   = document.getElementById('bgBoosterDdEmpty');
    if (!ddWrap || !trigger || !menu || !list) return;

    function openMenu() {
      trigger.classList.add('is-open');
      menu.classList.add('is-open');
      trigger.setAttribute('aria-expanded','true');
      searchInp.value = '';
      filterList('');
      setTimeout(function(){ searchInp.focus(); }, 50);
    }
    function closeMenu() {
      trigger.classList.remove('is-open');
      menu.classList.remove('is-open');
      trigger.setAttribute('aria-expanded','false');
    }
    function updateTrigger(value, name, iconUrl) {
      labelEl.textContent = name;
      if (value === 'all') {
        iconWrap.innerHTML = '<i class="fa-solid fa-users-line" style="font-size:.72rem;color:rgba(255,255,255,.4);"></i>';
      } else if (iconUrl) {
        iconWrap.innerHTML = '';
        var img = document.createElement('img');
        img.src = iconUrl; img.alt = name;
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:6px;';
        img.onerror = function(){ iconWrap.innerHTML = '<i class="fa-solid fa-user" style="font-size:.72rem;color:rgba(255,255,255,.45);"></i>'; };
        iconWrap.appendChild(img);
      } else {
        iconWrap.innerHTML = '<i class="fa-solid fa-user" style="font-size:.72rem;color:rgba(255,255,255,.45);"></i>';
      }
    }
    function filterList(q) {
      var items = Array.from(list.querySelectorAll('.bg-booster-dd-item'));
      var any = false;
      items.forEach(function(it){
        if (it.dataset.value === 'all') { it.classList.remove('bg-booster-dd-item--hidden'); return; }
        var name = (it.querySelector('.bg-booster-dd-item-name')||{}).textContent || '';
        var match = !q || name.toLowerCase().indexOf(q.toLowerCase()) !== -1;
        it.classList.toggle('bg-booster-dd-item--hidden', !match);
        if (match) any = true;
      });
      emptyEl.style.display = (!any && q) ? '' : 'none';
    }

    trigger.addEventListener('click', function(e){ e.stopPropagation(); menu.classList.contains('is-open') ? closeMenu() : openMenu(); });
    document.addEventListener('click', function(e){ if (!ddWrap.contains(e.target)) closeMenu(); });
    searchInp.addEventListener('input', function(){ filterList(this.value.trim()); });
    searchInp.addEventListener('click', function(e){ e.stopPropagation(); });
    list.addEventListener('click', function(e){
      var item = e.target.closest('.bg-booster-dd-item');
      if (!item || item.classList.contains('bg-booster-dd-item--hidden')) return;
      boosterId = item.dataset.value || 'all';
      closeMenu();
      goToPage(1);
    });

    var selectedItem = list.querySelector('.bg-booster-dd-item--active') || list.querySelector('.bg-booster-dd-item[data-value="all"]');
    if (selectedItem) {
      var selectedName = (selectedItem.querySelector('.bg-booster-dd-item-name') || {}).textContent || 'All Boosters';
      var selectedIcon = selectedItem.dataset.icon || '';
      updateTrigger(selectedItem.dataset.value || 'all', selectedName, selectedIcon);
    }
  })();

  pills.forEach(function(p){
    p.addEventListener('click', function(){
      mode = p.dataset.mode || 'all';
      goToPage(1);
    });
  });

  var searchTimer = null;
  if (srchEl) srchEl.addEventListener('input', function(){
    search = this.value || '';
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function(){ goToPage(1); }, 350);
  });

  if ($chkAll) $chkAll.addEventListener('change', function(){
    var shouldCheck = this.checked;
    allRows().forEach(function(r){
      var chk = r.querySelector('.bg-row-chk');
      if (!chk || chk.disabled) return;
      chk.checked = shouldCheck;
      if (shouldCheck) selected.add(String(r.dataset.id)); else selected.delete(String(r.dataset.id));
    });
    updateBulk();
  });

  document.addEventListener('change', function(e){
    if (!e.target.classList.contains('bg-row-chk')) return;
    e.stopPropagation();
    var id = String(e.target.value);
    if (e.target.checked) selected.add(id); else selected.delete(id);
    updateBulk();
  });

  if ($bulkBar) $bulkBar.addEventListener('click', function(){
    if ($bulkBar.disabled) return;
    pruneSelection();
    var ids = Array.from(selected).map(Number).filter(Number.isFinite);
    if (!ids.length) { updateBulk(); return; }
    if (!confirm('Hide '+ids.length+' game(s) from match history, leaderboards and booster stats?')) return;

    $bulkBar.disabled = true;
    $.ajax({
      type: 'POST',
      url: '<?= AJAX_URL ?>',
      data: { action: 'admin_delete_order_matches', match_ids: ids.join(',') },
      success: function(resp){
        var d = resp;
        try { if (typeof resp === 'string') d = JSON.parse(resp); }
        catch (err) {
          console.error('Invalid bulk hide JSON response:', resp);
          if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Invalid AJAX response.');
          $bulkBar.disabled = false;
          return;
        }
        if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
        if (d && (d.ok || d.success)) window.location.reload();
        else { updateBulk(); $bulkBar.disabled = false; }
      },
      error: function(xhr){
        console.error('Bulk hide AJAX error:', xhr.responseText);
        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not hide games.');
        $bulkBar.disabled = false;
      }
    });
  });

  updateCounters();
  updateBulk();
  renderPagination();

  return { refresh: function(){ updateCounters(); updateBulk(); renderPagination(); } };
})();
</script>
<?= $this->end() ?>
