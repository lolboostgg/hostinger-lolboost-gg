<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'boosters-view bv2']) ?>

<?php
/* ── Online/Offline detection ─────────────────────────────────── */
$__boosterId = (int)($booster['booster_id'] ?? $booster['id'] ?? 0);
$__isOnline  = function_exists('lb_booster_is_online') ? lb_booster_is_online($__boosterId) : false;

$__statusText  = $__isOnline ? t('Online') : t('Offline');
$__statusClass = $__isOnline ? 'online' : 'offline';

/* ── Order stats ──────────────────────────────────────────────── */
if (!function_exists('ov_booster_order_stats')) {
    function ov_booster_order_stats(int $boosterId): array {
        $stats = ['total'=>0,'completed'=>0,'in_progress'=>0,'source'=>'fallback'];
        if ($boosterId <= 0) return $stats;
        $tableCandidates      = ['orders','booster_orders','boost_orders','orders_panel'];
        $boosterColCandidates = ['booster_id','assigned_booster_id','booster','boosterId'];
        $statusColCandidates  = ['status','order_status','state'];
        global $db,$pdo,$conn,$database;
        $runSelect = function(string $sql, array $params) use ($db,$pdo,$conn,$database) {
            if (isset($db) && is_object($db) && method_exists($db,'run')) return $db->run(str_replace(':bid',(string)(int)$params[':bid'],$sql));
            $p = null;
            if (isset($pdo) && $pdo instanceof \PDO) $p=$pdo;
            elseif (isset($conn) && $conn instanceof \PDO) $p=$conn;
            elseif (isset($database) && $database instanceof \PDO) $p=$database;
            if ($p) { $st=$p->prepare($sql); $st->execute($params); return $st->fetchAll(\PDO::FETCH_ASSOC); }
            throw new \RuntimeException('No DB handle');
        };
        foreach ($tableCandidates as $table) foreach ($boosterColCandidates as $bcol) foreach ($statusColCandidates as $scol) {
            try {
                $rows = $runSelect("SELECT {$scol} AS st, COUNT(*) AS c FROM {$table} WHERE {$bcol} = :bid GROUP BY {$scol}", [':bid'=>$boosterId]);
                if (!is_array($rows)||empty($rows)) { $stats['source']="{$table}.{$bcol}.{$scol}"; return $stats; }
                $total=$completed=$inprog=0;
                foreach ($rows as $r) {
                    $st=strtolower(trim((string)($r['st']??''))); $c=(int)($r['c']??0); $total+=$c;
                    if (in_array($st,['completed','complete','finished','done','success'],true)||str_contains($st,'complete')||str_contains($st,'finish')) $completed+=$c;
                    elseif (in_array($st,['in_progress','in progress','progress','active','ongoing','running','started'],true)||str_contains($st,'progress')||str_contains($st,'active')||str_contains($st,'ongoing')) $inprog+=$c;
                }
                return ['total'=>$total,'completed'=>$completed,'in_progress'=>$inprog,'source'=>"{$table}.{$bcol}.{$scol}"];
            } catch (\Throwable $e) {}
        }
        return $stats;
    }
}
$__orderStats = ov_booster_order_stats($__boosterId);

/* ── Data helpers ─────────────────────────────────────────────── */
$bUsername   = htmlspecialchars($booster['username'] ?? 'Booster', ENT_QUOTES, 'UTF-8');
$bRankName   = htmlspecialchars($booster['rank_name'] ?? '', ENT_QUOTES, 'UTF-8');
$bIcon       = $booster['icon'] ?? '';
$bCoverDefault = ASSET_URL . '/core/main/img/banners/leona.jpeg';
$bCover      = trim((string)($booster['cover'] ?? $booster['banner_url'] ?? ''));
$bCover      = $bCover !== '' ? $bCover : $bCoverDefault;
$bCoverPosition = trim((string)($booster['cover_position'] ?? $booster['banner_position'] ?? '50% 50%'));
if ($bCoverPosition === '') $bCoverPosition = '50% 50%';
$bInitial    = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','', $bUsername) ?: 'B', 0, 2));

$reviewsLoadedCount = is_array($reviews) ? count($reviews) : 0;
$reviewsPerPage = (int)($reviewPagination['itemsPerPage'] ?? 6);
$reviewsPage = (int)($reviewPagination['page'] ?? 1);
$reviewsCount = (int)($reviewPagination['totalItems'] ?? ($booster['reviews_total_count'] ?? $reviewsLoadedCount));
$reviewsTotalPages = max(1, (int)($reviewPagination['totalPages'] ?? ceil($reviewsCount / max(1, $reviewsPerPage))));
$avgRating = (float)($booster['rating'] ?? 0);

if (!function_exists('bv_profile_page_url')) {
    function bv_profile_page_url(array $params, string $hash = ''): string {
        $path = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
        if ($path === false || $path === '') $path = '';
        $query = $_GET;
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') unset($query[$key]);
            else $query[$key] = $value;
        }
        $url = $path;
        if (!empty($query)) $url .= '?' . http_build_query($query);
        if ($hash !== '') $url .= '#' . ltrim($hash, '#');
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('bv_profile_page_numbers')) {
    function bv_profile_page_numbers(int $current, int $total): array {
        $total = max(1, $total);
        $current = max(1, min($total, $current));
        if ($total <= 7) return range(1, $total);
        $pages = [1];
        $start = max(2, $current - 1);
        $end = min($total - 1, $current + 1);
        if ($start > 2) $pages[] = '...';
        for ($i = $start; $i <= $end; $i++) $pages[] = $i;
        if ($end < $total - 1) $pages[] = '...';
        $pages[] = $total;
        return $pages;
    }
}

$descRaw     = $booster['description'] ?? '';
$descDecoded = html_entity_decode(html_entity_decode($descRaw, ENT_QUOTES,'UTF-8'), ENT_QUOTES,'UTF-8');

$roles     = !empty($booster['roles'])     ? explode('|', $booster['roles'])     : [];
$languages = !empty($booster['languages']) ? explode('|', $booster['languages']) : [];
$champions = !empty($booster['champions']) ? explode('|', $booster['champions']) : [];

$__lolRankNames = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
$__lolTier = 0;
$__lolDiv  = 0;
$lolRankImg = ASSET_URL.'/core/main/img/lol/ranks/max/0.png';
if (!empty($booster['lol_rank'])) {
    $rParts = explode('|', $booster['lol_rank']);
    $__lolTier = (int)($rParts[0] ?? 0);
    $__lolDiv  = (int)($rParts[1] ?? 0);
    $lolRankImg = ASSET_URL.'/core/main/img/lol/ranks/max/'.$__lolTier.'.png';
}
$__lolRankLabel = $__lolRankNames[$__lolTier] ?? 'Unranked';
if ($__lolTier > 0 && $__lolTier < 8 && $__lolDiv > 0) {
    $__lolRankLabel .= ' ' . (['I','II','III','IV'][max(0, min(3, $__lolDiv - 1))] ?? '');
}

/* ── Game-aware helpers ────────────────────────────────────────── */
$__gamesRaw  = strtolower(trim((string)($booster['games'] ?? 'lol')));
$__gameAliases = [
    'league' => 'lol',
    'league-of-legends' => 'lol',
    'league_of_legends' => 'lol',
    'valorant' => 'val',
    'teamfight-tactics' => 'tft',
    'teamfight_tactics' => 'tft',
];
$__gameList = [];
foreach (array_filter(array_map('trim', preg_split('/[|,]/', $__gamesRaw))) as $__gameKey) {
    $__gameKey = $__gameAliases[$__gameKey] ?? $__gameKey;
    if ($__gameKey !== '' && !in_array($__gameKey, $__gameList, true)) {
        $__gameList[] = $__gameKey;
    }
}
if (empty($__gameList)) $__gameList = ['lol'];
$__hasLol = in_array('lol', $__gameList, true);
$__hasVal = in_array('val', $__gameList, true);
$__hasTft = in_array('tft', $__gameList, true);
$__primaryGame = $__gameList[0];
$__visibleGameCount = count($__gameList);
$__dynamicGameList = array_values(array_diff($__gameList, ['lol', 'val', 'tft']));
$__dynamicGameProfiles = lb_booster_game_profiles((int)($booster['booster_id'] ?? $booster['id'] ?? 0));

// Val rank
$__valRankNames = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Ascendant',8=>'Immortal',9=>'Radiant'];
$__valTier = 0; $__valDiv = 0;
if (!empty($booster['val_rank'])) {
    $__vp = explode('|', $booster['val_rank']);
    $__valTier = (int)($__vp[0] ?? 0);
    $__valDiv  = (int)($__vp[1] ?? 0);
}
$__valRankName  = $__valRankNames[$__valTier] ?? 'Unranked';
$__valDivSuffix = ($__valTier > 0 && $__valTier < 7 && $__valDiv > 0) ? ' ' . (['I','II','III','IV'][$__valDiv - 1] ?? '') : '';
$__valRankLabel = $__valRankName . $__valDivSuffix;
$__valRankImg   = ASSET_URL . '/core/main/img/val/ranks/mini/' . $__valTier . '.png';

// TFT uses the same rank ladder and rank assets as League of Legends.
// When no dedicated TFT rank is stored, use the LoL rank instead of showing Unranked.
$__tftRankNames = $__lolRankNames;
$__tftTier = $__lolTier;
$__tftDiv  = $__lolDiv;
if (!empty($booster['tft_rank'])) {
    $__tp = explode('|', $booster['tft_rank']);
    $__storedTftTier = (int)($__tp[0] ?? 0);
    $__storedTftDiv  = (int)($__tp[1] ?? 0);
    if ($__storedTftTier > 0) {
        $__tftTier = $__storedTftTier;
        $__tftDiv  = $__storedTftDiv;
    }
}
$__tftRankName = $__tftRankNames[$__tftTier] ?? 'Unranked';
if ($__tftTier > 0 && $__tftTier < 8 && $__tftDiv > 0) {
    $__tftRankName .= ' ' . (['I','II','III','IV'][max(0, min(3, $__tftDiv - 1))] ?? '');
}
$__tftRankImg  = ASSET_URL . '/core/main/img/lol/ranks/max/' . $__tftTier . '.png';

// Agents
$__agentsData = [];
try {
    $__aj = SYS_PATH . '/public/uploads/lists/val-agents.json';
    if (file_exists($__aj)) $__agentsData = json_decode(file_get_contents($__aj), true) ?? [];
} catch (Throwable $e) {}
$agents = !empty($booster['agents']) ? array_values(array_filter(array_map('trim', explode('|', $booster['agents'])))) : [];

// Servers
$__serverNames = [
    'euw'=>'EU West','eune'=>'EU Nordic & East','na'=>'North America',
    'tr'=>'Turkey','ru'=>'Russia','br'=>'Brazil',
    'lan'=>'Latin America North','las'=>'Latin America South',
    'oce'=>'Oceania','kr'=>'Korea','jp'=>'Japan',
    'eu'=>'Europe','sea'=>'Southeast Asia','me'=>'Middle East',
    'vn'=>'Vietnam','ph'=>'Philippines','sg'=>'Singapore','th'=>'Thailand','tw'=>'Taiwan',
];
$__servers = !empty($booster['servers']) ? array_values(array_filter(array_map('trim', explode('|', strtolower($booster['servers']))))) : [];
$__activeGameCount = count($__gameList);
$__isSingleGame = $__activeGameCount === 1;

$ordersList   = is_array($booster['orders']) ? $booster['orders'] : [];
$ordersLoadedCount = count($ordersList);
$ordersCount  = (int)($pagination['totalItems'] ?? ($booster['orders_total_count'] ?? $ordersLoadedCount));
$ordersPage = (int)($pagination['page'] ?? 1);
$ordersPerPage = (int)($pagination['itemsPerPage'] ?? 10);
$ordersTotalPages = max(1, (int)($pagination['totalPages'] ?? ceil($ordersCount / max(1, $ordersPerPage))));

/* ── boost_requests flag ── */
$__boostActive   = (int)($booster['boost_requests'] ?? 1) === 1;
$__boostDisabled = !$__boostActive;

// Action chooser metadata for every assigned game. Only active marketplace
// services receive a target URL, so Play with and Coaching never lead to a
// non-existent form.
$__gameActionMeta = [];
$__actionAliases = ['league-of-legends'=>'lol', 'valorant'=>'val', 'teamfight-tactics'=>'tft', 'league-of-legends-classic'=>'lol_classic'];
try {
    global $db;
    $__actionRows = $db->run("SELECT g.slug, g.name, g.icon, gs.service_type
        FROM games g
        INNER JOIN game_services gs ON gs.game_id = g.id
        WHERE g.status = 1 AND gs.status = 1 AND gs.service_type IN ('boosting','coaching')");
    foreach ((array)$__actionRows as $__actionRow) {
        $__catalogSlug = strtolower(trim((string)($__actionRow['slug'] ?? '')));
        if ($__catalogSlug === '') continue;
        $__gameKey = $__actionAliases[$__catalogSlug] ?? $__catalogSlug;
        if (!in_array($__gameKey, $__gameList, true)) continue;
        if (!isset($__gameActionMeta[$__gameKey])) {
            $__gameActionMeta[$__gameKey] = [
                'label' => (string)($__actionRow['name'] ?? util_game_display_name($__gameKey)),
                'icon' => util_game_icon_url($__gameKey),
                'boost' => '', 'coaching' => '',
            ];
        }
        $__serviceType = (string)($__actionRow['service_type'] ?? '');
        if ($__serviceType === 'boosting') $__gameActionMeta[$__gameKey]['boost'] = '/' . $__catalogSlug . '/rank-boost';
        if ($__serviceType === 'coaching') $__gameActionMeta[$__gameKey]['coaching'] = '/' . $__catalogSlug . '/coaching';
    }
} catch (Throwable $e) {}

// Stable fallbacks for the original games when marketplace service rows are
// missing on an older installation.
$__legacyActionMeta = [
    'lol'=>['label'=>'League of Legends','icon'=>util_game_icon_url('lol'),'boost'=>'/lol/rank-boost','coaching'=>'/lol/coaching'],
    'val'=>['label'=>'Valorant','icon'=>util_game_icon_url('val'),'boost'=>'/val/rank-boost','coaching'=>'/val/coaching'],
    'tft'=>['label'=>'Teamfight Tactics','icon'=>util_game_icon_url('tft'),'boost'=>'/tft/rank-boost','coaching'=>'/tft/coaching'],
];
foreach ($__gameList as $__gameKey) {
    if (!isset($__legacyActionMeta[$__gameKey])) continue;
    if (!isset($__gameActionMeta[$__gameKey])) {
        $__gameActionMeta[$__gameKey] = $__legacyActionMeta[$__gameKey];
        continue;
    }
    // A catalog row can exist for boosting but not coaching (or vice versa).
    // Fill the missing action from the stable legacy route instead of dropping
    // that game from the chooser.
    foreach (['label', 'icon', 'boost', 'coaching'] as $__actionKey) {
        if (empty($__gameActionMeta[$__gameKey][$__actionKey])) {
            $__gameActionMeta[$__gameKey][$__actionKey] = $__legacyActionMeta[$__gameKey][$__actionKey];
        }
    }
}
?>

<style>
/* ═══════════════════════════════════════════════════════
   BV2 — Booster Profile v2  (seller/egirls style)
═══════════════════════════════════════════════════════ */
.bv2 header {
  min-height: clamp(300px, 32vh, 390px) !important;
  height: auto !important;
  box-sizing: border-box !important;
  /* boosted vertical offset: keeps Booster View clearly below the fixed navbar */
  padding: calc(var(--lb-content-top, 132px) + clamp(72px, 5.2vw, 110px)) 4.167vw clamp(54px, 5vh, 82px) !important;
  background:
    radial-gradient(ellipse 55% 75% at 74% 46%, rgba(99,102,241,.20) 0%, transparent 62%),
    radial-gradient(ellipse 38% 58% at 14% 55%, rgba(59,184,255,.10) 0%, transparent 58%),
    linear-gradient(135deg, #080716 0%, #100e24 52%, #080716 100%) !important;
  background-image:
    radial-gradient(ellipse 55% 75% at 74% 46%, rgba(99,102,241,.20) 0%, transparent 62%),
    radial-gradient(ellipse 38% 58% at 14% 55%, rgba(59,184,255,.10) 0%, transparent 58%),
    linear-gradient(135deg, #080716 0%, #100e24 52%, #080716 100%) !important;
  display: flex !important;
  align-items: flex-start !important;
  justify-content: center !important;
  flex-direction: column !important;
  position: relative;
  overflow: hidden;
}
.bv2 header::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(99,102,241,.055) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99,102,241,.055) 1px, transparent 1px);
  background-size: 44px 44px;
  mask-image: linear-gradient(to right, transparent 0%, black 25%, black 75%, transparent 100%);
  pointer-events: none;
}
.bv2 header::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at center, transparent 42%, rgba(0,0,0,.62) 100%);
  pointer-events: none;
}
.bv2 header .content {
  max-width: none !important;
  /* extra distance from the fixed navbar; matches the lower hero feeling from the boosters list */
  margin: clamp(82px, 6.5vw, 132px) 0 0 !important;
  padding: 0 !important;
  position: relative;
  z-index: 2;
}
.bv2 header h1 {
  font-size: clamp(42px, 4vw, 68px) !important;
  line-height: .96 !important;
  margin: 0 0 18px !important;
  text-transform: uppercase;
  font-family:'superchargestraight',sans-serif;
  background:linear-gradient(135deg,#fff 0%,#818cf8 55%,#6366f1 100%);
  -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
}
.bv2 header p {
  max-width: 76ch;
  font-size: clamp(14px, 1.02vw, 17px);
  line-height: 1.65;
  color: rgba(255,255,255,.8);
}

/* Main content wrap — no outer card, tabs sit directly below the hero */
.bv2 .bv-card {
  margin:2vw 4.167vw 4vw;
  padding:0; position:relative;
}

/* Cover banner */
.bv2 .bv-cover {
  height:17vw; overflow:hidden;
  margin:-4.167vw -4.167vw 0 -4.167vw;
  border-radius:1.5vw 1.5vw 0 0;
  background:linear-gradient(135deg,#0d0525 0%,#1a0a40 50%,#0d0525 100%);
  background-size:cover; background-position:center;
  position:relative;
}
.bv2 .bv-cover::after {
  content:''; position:absolute; inset:0;
  background:linear-gradient(180deg,transparent 40%,rgba(8,8,22,.85) 100%),
             radial-gradient(ellipse at 65% 50%,rgba(99,102,241,.2) 0%,transparent 65%);
}
.bv2 .bv-cover::before {
  content:''; position:absolute; inset:0; z-index:1;
  background:url('<?= ASSET_URL ?>/website/images/boosters/view-header-bg.webp') center/cover no-repeat;
  opacity:.07;
}
.bv2 .bv-cover-img {
  position:absolute; inset:0; width:100%; height:100%; object-fit:cover; z-index:0;
}

/* Avatar */
.bv2 .bv-avatar {
  width:12.5vw; height:12.5vw; border-radius:50%;
  /* visible, not hidden: the online dot sits on the rim and must not be clipped.
     The image rounds itself instead. */
  overflow:visible;
  border:.3vw solid rgba(99,102,241,.6);
  box-shadow:0 0 2.5vw rgba(99,102,241,.4),0 0 5vw rgba(129,140,248,.1);
  position:absolute; top:5.5vw; left:4vw; z-index:2;
  animation:bv-glow 3s ease-in-out infinite;
}
.bv2 .bv-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; display:block; }
.bv2 .bv-avatar-ph {
  width:12.5vw; height:12.5vw; border-radius:50%;
  background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(129,140,248,.2));
  border:.3vw solid rgba(99,102,241,.5);
  position:absolute; top:5.5vw; left:4vw; z-index:2;
  display:flex; align-items:center; justify-content:center;
  font-size:4vw; font-weight:900; color:rgba(255,255,255,.6);
  box-shadow:0 0 2.5vw rgba(99,102,241,.4);
}
/* Online dot on avatar — sits on the lower-right rim, above the glow ring */
.bv2 .bv-online-dot {
  position:absolute; bottom:.55vw; right:.55vw; z-index:10;
  width:1.35vw; height:1.35vw; min-width:12px; min-height:12px; border-radius:50%;
  border:.2vw solid #08081a;
  background:rgba(255,255,255,.2);
  pointer-events:none;
}
.bv2 .bv-online-dot.online {
  background:#22c55e;
  animation:bv-pulse 2s ease-in-out infinite;
}
@keyframes bv-glow {
  0%,100%{box-shadow:0 0 2.5vw rgba(99,102,241,.4),0 0 5vw rgba(129,140,248,.1);}
  50%{box-shadow:0 0 4vw rgba(99,102,241,.65),0 0 8vw rgba(129,140,248,.2);}
}
@keyframes bv-pulse {
  0%,100%{box-shadow:0 0 .7vw rgba(34,197,94,.7);}
  50%{box-shadow:0 0 1.6vw rgba(34,197,94,1),0 0 2.8vw rgba(34,197,94,.25);}
}

/* Details area */
.bv2 .bv-details { margin-top:3vw; }

/* Top row */
.bv2 .bv-top {
  display:flex; align-items:flex-start; justify-content:space-between;
  gap:2vw; padding-bottom:1.8vw;
  border-bottom:.104vw solid rgba(99,102,241,.1);
  flex-wrap:wrap;
}
.bv2 .bv-info h5 {
  font-size:3vw !important; font-weight:900;
  font-family:'superchargestraight',sans-serif;
  background:linear-gradient(135deg,#fff 0%,#a5b4fc 55%,#818cf8 100%);
  -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
  display:flex; align-items:center; gap:.5vw; margin-bottom:.4vw;
}
.bv2 .bv-info h5 .bv-check { -webkit-text-fill-color:#6366f1; font-size:.7em; }
.bv2 .bv-info h6 {
  font-size:1.1vw; color:rgba(255,255,255,.45); font-weight:400;
  margin-bottom:.8vw; display:flex; align-items:center; gap:.6vw; flex-wrap:wrap;
}

/* Chips */
.bv2 .bv-chip {
  display:inline-flex; align-items:center; gap:.28vw;
  font-size:.82vw; font-weight:800; text-transform:uppercase; letter-spacing:.05em;
  padding:.32vw .82vw; border-radius:999px;
  background:rgba(255,255,255,.07); border:.052vw solid rgba(255,255,255,.12);
  color:rgba(255,255,255,.7);
}
.bv2 .bv-chip.rank   { background:rgba(99,102,241,.12); border-color:rgba(99,102,241,.3); color:#a5b4fc; }
.bv2 .bv-chip.verified { background:rgba(34,197,94,.1); border-color:rgba(34,197,94,.3); color:#22c55e; }
.bv2 .bv-chip.online-chip  { background:rgba(34,197,94,.1); border-color:rgba(34,197,94,.3); color:#22c55e; }
.bv2 .bv-chip.offline-chip { background:rgba(255,255,255,.05); border-color:rgba(255,255,255,.1); color:rgba(255,255,255,.45); }
.bv2 .bv-chip--lang { gap:.4vw; padding-left:.5vw; }

/* Local time of the booster — sits between the chips and the action buttons */
.bv-hero__tz{
  display:inline-flex; align-items:center; gap:.45vw;
  margin-top:.7vw;
  font-size:.82vw; font-weight:700; letter-spacing:.02em;
  color:rgba(255,255,255,.52);
}
.bv-hero__tz i{ font-size:.95em; color:#818cf8; }
.bv-hero__tz-time{ color:#fff; font-weight:900; font-variant-numeric:tabular-nums; }
.bv-hero__tz-sep{ opacity:.4; }
.bv-hero__tz-zone{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:18vw; }
.bv2 .bv-chip__flag {
  width:1.05vw; height:1.05vw; min-width:14px; min-height:14px;
  border-radius:999px; object-fit:cover; flex:0 0 auto;
  border:.052vw solid rgba(255,255,255,.14);
}

/* Action buttons — unified style */
.bv2 .bv-actions {
  display:flex; gap:.6vw; flex-wrap:wrap; align-items:center; flex-shrink:0;
}
.bv2 .bv-btn {
  display:inline-flex; align-items:center; gap:.4vw;
  font-size:.82vw; font-weight:900; text-transform:uppercase; letter-spacing:.06em;
  padding:.55vw 1.2vw; border-radius:999px; cursor:pointer;
  border:none; font-family:inherit; transition:all .18s; text-decoration:none;
}
/* All active buttons — same indigo style */
.bv2 .bv-btn-action {
  color:#eef2ff;
  background:linear-gradient(135deg,rgba(99,102,241,.9),rgba(139,92,246,.9));
  box-shadow:0 .25vw .9vw rgba(99,102,241,.35);
}
.bv2 .bv-btn-action:hover { box-shadow:0 .4vw 1.4vw rgba(99,102,241,.55); transform:translateY(-.05vw); color:#fff; }
/* Disabled buttons — grey, no pointer */
.bv2 .bv-btn-disabled {
  color:rgba(255,255,255,.35);
  background:rgba(255,255,255,.06);
  box-shadow:none; cursor:not-allowed;
  border:1px solid rgba(255,255,255,.08);
}
.bv2 .bv-btn-disabled:hover { transform:none; box-shadow:none; }
/* Keep old classes for compatibility */
.bv2 .bv-btn-primary {
  color:#eef2ff;
  background:linear-gradient(135deg,rgba(99,102,241,.9),rgba(139,92,246,.9));
  box-shadow:0 .25vw .9vw rgba(99,102,241,.35);
}
.bv2 .bv-btn-primary:hover { box-shadow:0 .4vw 1.4vw rgba(99,102,241,.55); transform:translateY(-.05vw); color:#fff; }
.bv2 .bv-btn-ghost {
  color:#eef2ff;
  background:linear-gradient(135deg,rgba(99,102,241,.9),rgba(139,92,246,.9));
  box-shadow:0 .25vw .9vw rgba(99,102,241,.35);
  border:none;
}
.bv2 .bv-btn-ghost:hover { box-shadow:0 .4vw 1.4vw rgba(99,102,241,.55); transform:translateY(-.05vw); color:#fff; }

/* Nav tabs */
.bv2 .bv-nav-tabs {
  display:flex; gap:0;
  border-bottom:.104vw solid rgba(99,102,241,.1);
  margin-bottom:2vw; overflow-x:auto; scrollbar-width:none;
}
.bv2 .bv-nav-tabs::-webkit-scrollbar { display:none; }
.bv2 .bv-nav-tabs a {
  display:flex; align-items:center; gap:.4vw;
  font-size:1.05vw; font-weight:700; color:rgba(255,255,255,.4);
  padding:.9vw 1.4vw; border-bottom:.18vw solid transparent;
  text-decoration:none; white-space:nowrap; cursor:pointer;
  transition:color .15s,border-color .15s,background .15s;
}
.bv2 .bv-nav-tabs a:hover { color:rgba(255,255,255,.7); }
.bv2 .bv-nav-tabs a.active {
  color:#818cf8; border-bottom-color:#818cf8;
  background:linear-gradient(180deg,transparent,rgba(99,102,241,.04));
}
.bv2 .bv-nav-tabs a i { font-size:.95vw; }
.bv2 .bv-tab-count {
  background:rgba(99,102,241,.25); color:#a5b4fc;
  font-size:.68vw; font-weight:900; padding:.15vw .52vw;
  border-radius:999px; margin-left:.2vw;
}
.bv2 .bv-nav-tabs a.active .bv-tab-count { background:rgba(255,255,255,.22); color:#fff; }

/* Tab layout: main + sidebar */
.bv2 .bv-tab-layout { display:grid; grid-template-columns:minmax(0,1fr) 17vw; gap:1vw; align-items:start; }
.bv2 .bv-tab-pane { display:none; }
.bv2 .bv-tab-pane.active { display:block; }

/* Section label */
.bv2 .bv-section-label {
  font-size:.88vw; font-weight:900; text-transform:uppercase;
  letter-spacing:.14em; color:#818cf8; margin-bottom:.85vw;
  display:flex; align-items:center; gap:.5vw;
}
.bv2 .bv-section-label::before {
  content:''; width:.2vw; height:.85vw; border-radius:999px; flex-shrink:0;
  background:linear-gradient(180deg,#6366f1,#818cf8);
}
.bv2 .bv-section-label::after {
  content:''; flex:1; height:1px;
  background:linear-gradient(90deg,rgba(99,102,241,.2),transparent);
}

/* Stat cards row */
.bv2 .bv-stats-row { display:flex; gap:1vw; margin-bottom:2vw; flex-wrap:wrap; }
.bv2 .bv-stat-card {
  flex:1; min-width:10vw;
  background:rgba(99,102,241,.07); border:.052vw solid rgba(99,102,241,.15);
  border-radius:.75vw; padding:1vw 1.2vw;
  display:flex; align-items:center; gap:.75vw; transition:all .2s;
}
.bv2 .bv-stat-card:hover { border-color:rgba(99,102,241,.3); background:rgba(99,102,241,.1); }
.bv2 .bv-stat-icon {
  width:2.4vw; height:2.4vw; border-radius:.5vw;
  background:rgba(99,102,241,.15); border:.052vw solid rgba(99,102,241,.25);
  display:flex; align-items:center; justify-content:center;
  font-size:1.2vw; flex-shrink:0; color:#a5b4fc;
}
.bv2 .bv-stat-val { font-size:2vw; font-weight:900; color:#fff; line-height:1; }
.bv2 .bv-stat-lbl { font-size:.78vw; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.06em; margin-top:.18vw; }

/* Description */
.bv2 .bv-description {
  color:rgba(255,255,255,.74); font-size:.92vw; line-height:1.72;
  margin-bottom:2vw;
}

/* Info tiles grid */
.bv2 .bv-tiles {
  display:grid; grid-template-columns:repeat(4, minmax(0,1fr));
  gap:1vw; margin-bottom:2vw;
}
@media(max-width:1400px){
  .bv2 .bv-tiles { grid-template-columns:repeat(3, minmax(0,1fr)); }
}
.bv2 .bv-tile {
  background:rgba(255,255,255,.03); border:.052vw solid rgba(255,255,255,.08);
  border-radius:.95vw; padding:1.15vw 1.2vw; transition:border-color .2s;
}
.bv2 .bv-tile:hover { border-color:rgba(99,102,241,.2); }
.bv2 .bv-tile-head {
  display:flex; align-items:center; gap:.5vw;
  font-size:.78vw; font-weight:900; text-transform:uppercase;
  letter-spacing:.08em; color:rgba(255,255,255,.4); margin-bottom:.8vw;
}
.bv2 .bv-tile-head img { width:1.1vw; height:1.1vw; object-fit:contain; opacity:.7; }
.bv2 .bv-tile-head i { font-size:.9vw; color:#818cf8; }
.bv2 .bv-rank-img { width:5vw; height:5vw; object-fit:contain; }
.bv2 .bv-roles { display:flex; flex-wrap:wrap; gap:.5vw; }
/* ── Game rank rows inside single tile ── */
.bv2 .bv-game-ranks { display:flex; flex-direction:column; gap:.6vw; }

/* ── +N more tooltip ── */
.bv2 .bv-more-icon {
    position:relative; display:inline-flex; align-items:center;
    background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3);
    border-radius:8px; padding:4px 10px; font-size:13px; font-weight:700;
    color:rgba(129,140,248,.9); cursor:default; user-select:none;
    transition:background .15s, border-color .15s;
}
.bv2 .bv-more-icon:hover { background:rgba(99,102,241,.25); border-color:rgba(99,102,241,.5); }
.bv2 .bv-more-tooltip {
    display:none; position:absolute;
    bottom:calc(100% + 10px); left:50%; transform:translateX(-50%);
    background:linear-gradient(160deg,#1c1040 0%,#120828 100%);
    border:1px solid rgba(99,102,241,.45); border-radius:14px;
    padding:12px 14px; z-index:9999; min-width:220px;
    box-shadow:0 20px 60px rgba(0,0,0,.85);
    pointer-events:none; white-space:nowrap;
}
.bv2 .bv-more-tooltip::after {
    content:''; position:absolute;
    bottom:-7px; left:50%; transform:translateX(-50%) rotate(45deg);
    width:12px; height:12px; background:#1c1040;
    border-right:1px solid rgba(99,102,241,.45);
    border-bottom:1px solid rgba(99,102,241,.45);
}
.bv2 .bv-more-icon:hover .bv-more-tooltip { display:block; }
.bv2 .bv-tt-title {
    display:block; font-size:10px; font-weight:800;
    text-transform:uppercase; letter-spacing:.12em;
    color:rgba(129,140,248,.55); margin-bottom:10px;
    padding-bottom:8px; border-bottom:1px solid rgba(99,102,241,.12);
}
.bv2 .bv-tt-grid {
    display:grid; grid-template-columns:1fr 1fr; gap:6px;
}
.bv2 .bv-tt-item {
    display:flex; align-items:center; gap:8px;
    font-size:12px; font-weight:600; color:rgba(220,220,255,.9);
    padding:3px 5px; border-radius:7px;
}
.bv2 .bv-tt-item img {
    width:26px; height:26px; border-radius:7px;
    object-fit:contain; background:rgba(0,0,0,.35);
    padding:2px; border:1px solid rgba(255,255,255,.08);
    flex-shrink:0;
}
.bv2 .bv-game-rank-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:.4vw .2vw;
    border-bottom:1px solid rgba(255,255,255,.05);
}
.bv2 .bv-game-rank-row:last-child { border-bottom:none; }
.bv2 .bv-game-label {
    display:flex; align-items:center; gap:.4vw;
    font-size:.75vw; font-weight:700; color:rgba(255,255,255,.45);
    text-transform:uppercase; letter-spacing:.06em;
}
.bv2 .bv-game-label img { width:16px; height:16px; object-fit:contain; border-radius:4px; }
.bv2 .bv-rank-img-sm { width:52px; height:52px; object-fit:contain; }
/* ── Server tags ── */
.bv2 .bv-server-tags { display:flex; flex-wrap:wrap; gap:6px; }
.bv2 .bv-server-tag {
    background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3);
    border-radius:6px; padding:3px 10px;
    font-size:.72vw; font-weight:700; color:#818cf8; text-transform:uppercase;
}
.bv2 .bv-role-icon { width:2.2vw; height:2.2vw; border-radius:50%; overflow:hidden; border:.052vw solid rgba(99,102,241,.2); }
.bv2 .bv-role-icon img { width:100%; height:100%; object-fit:cover; }
.bv2 .bv-langs { display:flex; flex-wrap:wrap; gap:.4vw; }
.bv2 .bv-lang-flag { width:1.6vw; height:1.6vw; border-radius:999px; object-fit:cover; border:.052vw solid rgba(255,255,255,.12); }
.bv2 .bv-champs { display:flex; flex-wrap:wrap; gap:.35vw; }
.bv2 .bv-champ-icon { width:2vw; height:2vw; border-radius:.4vw; object-fit:cover; border:.052vw solid rgba(99,102,241,.15); }


/* Compact booster expertise overview */
.bv2 .bv-expertise-wrap {
  display:grid;
  grid-template-columns:minmax(0,1fr) 15.5vw;
  gap:1vw;
  margin-bottom:2vw;
  align-items:stretch;
}
.bv2 .bv-expertise-main,
.bv2 .bv-expertise-side-card {
  border:.052vw solid rgba(255,255,255,.08);
  background:linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.018));
  border-radius:1vw;
  overflow:hidden;
}
.bv2 .bv-expertise-main { padding:.35vw 1.05vw; }
.bv2 .bv-game-row {
  display:grid;
  grid-template-columns:minmax(10vw,1.1fr) minmax(10vw,.9fr) minmax(14vw,1.7fr);
  align-items:center;
  gap:1vw;
  min-height:6.1vw;
  padding:1vw .2vw;
  border-bottom:1px solid rgba(255,255,255,.065);
}
.bv2 .bv-game-row:last-child { border-bottom:0; }
.bv2 .bv-game-identity { display:flex; align-items:center; gap:.7vw; min-width:0; }
.bv2 .bv-game-logo {
  width:2.35vw; height:2.35vw; min-width:2.35vw;
  border-radius:.65vw; padding:.42vw; object-fit:contain;
  background:rgba(99,102,241,.08); border:.052vw solid rgba(99,102,241,.16);
}
.bv2 .bv-game-name { font-size:.92vw; font-weight:900; color:#fff; line-height:1.15; }
.bv2 .bv-game-sub { font-size:.64vw; color:rgba(255,255,255,.38); margin-top:.2vw; }
.bv2 .bv-main-game-tag {
  display:inline-flex; margin-top:.38vw; padding:.18vw .45vw; border-radius:999px;
  font-size:.56vw; font-weight:900; text-transform:uppercase; letter-spacing:.09em;
  color:#c7d2fe; background:rgba(99,102,241,.14); border:.052vw solid rgba(99,102,241,.28);
}
.bv2 .bv-rank-compact { display:flex; align-items:center; gap:.65vw; min-width:0; }
.bv2 .bv-rank-compact img { width:3.7vw; height:3.7vw; min-width:3.7vw; object-fit:contain; }
.bv2 .bv-rank-caption { font-size:.6vw; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:rgba(255,255,255,.35); }
.bv2 .bv-rank-name { font-size:1vw; font-weight:900; color:#fff; margin-top:.16vw; }
.bv2 .bv-specialties { display:flex; flex-direction:column; gap:.58vw; min-width:0; }
.bv2 .bv-specialty-line { display:flex; align-items:center; gap:.65vw; min-width:0; }
.bv2 .bv-specialty-label {
  width:4.7vw; flex:0 0 4.7vw; font-size:.58vw; font-weight:800;
  text-transform:uppercase; letter-spacing:.1em; color:rgba(255,255,255,.34);
}
.bv2 .bv-specialty-icons { display:flex; align-items:center; flex-wrap:wrap; gap:.35vw; min-width:0; }
.bv2 .bv-specialty-icons .bv-role-icon { width:1.8vw; height:1.8vw; }
.bv2 .bv-specialty-icons .bv-champ-icon { width:1.8vw; height:1.8vw; border-radius:.38vw; }
.bv2 .bv-more-count {
  height:1.8vw; min-width:1.8vw; padding:0 .42vw; display:inline-flex; align-items:center; justify-content:center;
  border-radius:.42vw; background:rgba(99,102,241,.14); border:.052vw solid rgba(99,102,241,.24);
  color:#c7d2fe; font-size:.65vw; font-weight:900;
}

.bv2 .bv-expertise-wrap,
.bv2 .bv-expertise-main,
.bv2 .bv-game-row,
.bv2 .bv-specialties,
.bv2 .bv-specialty-line,
.bv2 .bv-specialty-icons { overflow:visible !important; }

.bv2 .bv-more-count--tooltip { position:relative; cursor:default; z-index:30; }
.bv2 .bv-champion-tooltip {
  position:absolute;
  right:0;
  bottom:calc(100% + 10px);
  width:max-content;
  min-width:250px;
  max-width:420px;
  padding:12px;
  border-radius:12px;
  background:linear-gradient(160deg,#1c1040 0%,#120828 100%);
  border:1px solid rgba(99,102,241,.45);
  box-shadow:0 20px 60px rgba(0,0,0,.85);
  opacity:0;
  visibility:hidden;
  transform:translateY(8px);
  pointer-events:none;
  transition:opacity .16s ease,transform .16s ease,visibility .16s ease;
  z-index:999999;
}
.bv2 .bv-champion-tooltip::after {
  content:'';
  position:absolute;
  right:14px;
  top:100%;
  width:12px;
  height:12px;
  transform:translateY(-6px) rotate(45deg);
  background:#120828;
  border-right:1px solid rgba(99,102,241,.45);
  border-bottom:1px solid rgba(99,102,241,.45);
}
.bv2 .bv-more-count--tooltip:hover .bv-champion-tooltip,
.bv2 .bv-more-count--tooltip:focus .bv-champion-tooltip,
.bv2 .bv-more-count--tooltip:focus-within .bv-champion-tooltip {
  opacity:1;
  visibility:visible;
  transform:translateY(0);
}
.bv2 .bv-champion-tooltip__title {
  display:block;
  margin-bottom:9px;
  padding-bottom:8px;
  border-bottom:1px solid rgba(99,102,241,.16);
  color:#a5b4fc;
  font-size:10px;
  font-weight:900;
  text-transform:uppercase;
  letter-spacing:.1em;
}
.bv2 .bv-champion-tooltip__grid {
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:7px;
}
.bv2 .bv-champion-tooltip__item {
  display:flex;
  align-items:center;
  gap:7px;
  min-width:0;
  color:#eef2ff;
  font-size:11px;
  font-weight:700;
}
.bv2 .bv-champion-tooltip__item img {
  width:28px;
  height:28px;
  min-width:28px;
  border-radius:6px;
  object-fit:cover;
}
.bv2 .bv-champion-tooltip__item span {
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}

/* One game: game, languages and servers stay in one row. */
.bv2 .bv-expertise-wrap--single {
  grid-template-columns:minmax(0,2.4fr) minmax(0,1fr) minmax(0,1fr);
  align-items:stretch;
}
.bv2 .bv-expertise-wrap--single .bv-expertise-main,
.bv2 .bv-expertise-wrap--single .bv-expertise-side { display:contents; }
.bv2 .bv-expertise-wrap--single .bv-game-row { height:100%; border-bottom:0; }
.bv2 .bv-expertise-wrap--single .bv-expertise-side-card { height:100%; box-sizing:border-box; }

@media(max-width:1400px){
  .bv2 .bv-expertise-wrap--single { grid-template-columns:minmax(0,2.2fr) minmax(0,1fr) minmax(0,1fr); }
  .bv2 .bv-expertise-wrap--single .bv-expertise-side { display:contents; }
}
@media(max-width:1000px){
  .bv2 .bv-expertise-wrap--single { grid-template-columns:1fr 1fr; }
  .bv2 .bv-expertise-wrap--single .bv-expertise-main { grid-column:1/-1; display:block; }
  .bv2 .bv-expertise-wrap--single .bv-expertise-side { display:contents; }
}
@media(max-width:767px){
  .bv2 .bv-expertise-wrap--single { display:block; }
  .bv2 .bv-champion-tooltip {
    right:auto;
    left:0;
    min-width:240px;
    max-width:min(360px,calc(100vw - 48px));
  }
  .bv2 .bv-champion-tooltip::after { right:auto; left:14px; }
  .bv2 .bv-champion-tooltip__grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
.bv2 .bv-no-specialty { font-size:.68vw; color:rgba(255,255,255,.35); }
.bv2 .bv-expertise-side { display:flex; flex-direction:column; gap:1vw; }
.bv2 .bv-expertise-side-card { padding:1vw; }
.bv2 .bv-side-title {
  display:flex; align-items:center; gap:.5vw; margin-bottom:.8vw;
  font-size:.78vw; font-weight:900; color:#fff;
}
.bv2 .bv-side-title img,.bv2 .bv-side-title i { width:1vw; height:1vw; color:#818cf8; object-fit:contain; }
.bv2 .bv-side-list { display:flex; flex-wrap:wrap; gap:.42vw; }
.bv2 .bv-side-pill {
  display:inline-flex; align-items:center; gap:.38vw; min-height:1.9vw; padding:0 .62vw;
  border-radius:999px; background:rgba(99,102,241,.08); border:.052vw solid rgba(99,102,241,.18);
  color:#dbe2ff; font-size:.68vw; font-weight:800;
}
.bv2 .bv-side-pill .bv-lang-flag { width:1.15vw; height:1.15vw; }
@media(max-width:1400px){
  .bv2 .bv-expertise-wrap { grid-template-columns:1fr; }
  .bv2 .bv-expertise-side { display:grid; grid-template-columns:1fr 1fr; }
}
@media(max-width:900px){
  .bv2 .bv-game-row { grid-template-columns:1fr 1fr; }
  .bv2 .bv-specialties { grid-column:1/-1; padding-left:3.05vw; }
}
@media(max-width:767px){
  .bv2 .bv-expertise-wrap { display:block; margin-bottom:18px; }
  .bv2 .bv-expertise-main { padding:0 14px; border-radius:12px; }
  .bv2 .bv-game-row { display:block; min-height:0; padding:16px 0; }
  .bv2 .bv-game-identity { gap:10px; margin-bottom:12px; }
  .bv2 .bv-game-logo { width:38px; height:38px; min-width:38px; padding:7px; border-radius:10px; }
  .bv2 .bv-game-name { font-size:.92rem; }
  .bv2 .bv-game-sub { font-size:.67rem; }
  .bv2 .bv-main-game-tag { font-size:.55rem; padding:3px 7px; margin-top:5px; }
  .bv2 .bv-rank-compact { gap:10px; margin-bottom:12px; padding:10px; border-radius:10px; background:rgba(99,102,241,.055); }
  .bv2 .bv-rank-compact img { width:52px; height:52px; min-width:52px; }
  .bv2 .bv-rank-caption { font-size:.56rem; }
  .bv2 .bv-rank-name { font-size:.95rem; }
  .bv2 .bv-specialties { gap:9px; padding-left:0; }
  .bv2 .bv-specialty-line { align-items:flex-start; flex-direction:column; gap:6px; }
  .bv2 .bv-specialty-label { width:auto; flex:none; font-size:.56rem; }
  .bv2 .bv-specialty-icons { gap:6px; }
  .bv2 .bv-specialty-icons .bv-role-icon,.bv2 .bv-specialty-icons .bv-champ-icon { width:30px; height:30px; }
  .bv2 .bv-more-count { height:30px; min-width:30px; padding:0 8px; border-radius:7px; font-size:.72rem; }
  .bv2 .bv-no-specialty { font-size:.72rem; }
  .bv2 .bv-expertise-side { display:grid; grid-template-columns:1fr; gap:10px; margin-top:10px; }
  .bv2 .bv-expertise-side-card { border-radius:12px; padding:14px; }
  .bv2 .bv-side-title { font-size:.82rem; gap:8px; margin-bottom:10px; }
  .bv2 .bv-side-title img,.bv2 .bv-side-title i { width:15px; height:15px; }
  .bv2 .bv-side-list { gap:6px; }
  .bv2 .bv-side-pill { min-height:32px; padding:0 11px; font-size:.72rem; }
  .bv2 .bv-side-pill .bv-lang-flag { width:20px; height:20px; }
}

/* Recent orders (overview) */
.bv2 .bv-recent-orders {
  margin-bottom:2vw;
  max-width:none;
  width:100%;
}

.bv2 .bv-order-item {
  display:flex; align-items:center; gap:1vw;
  padding:.85vw 1vw; border-radius:.75vw;
  background:rgba(255,255,255,.02); border:.052vw solid rgba(255,255,255,.06);
  margin-bottom:.55vw; transition:border-color .15s;
}

.bv2 .bv-order-item:hover { border-color:rgba(99,102,241,.2); }

.bv2 .bv-order-icon {
  width:2.2vw; height:2.2vw; border-radius:.55vw; flex-shrink:0;
  background:rgba(99,102,241,.1); border:.052vw solid rgba(99,102,241,.18);
  display:flex; align-items:center; justify-content:center;
}

.bv2 .bv-order-icon img,
.bv2 .bv-order-icon i { width:1.05vw; height:1.05vw; object-fit:contain; filter:brightness(0) invert(1); opacity:.9; font-size:.85vw; color:#a5b4fc; }

.bv2 .bv-order-meta { flex:1; min-width:0; }
.bv2 .bv-order-name { font-size:.9vw; font-weight:800; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bv2 .bv-order-detail { font-size:.75vw; color:rgba(255,255,255,.4); margin-top:.15vw; }

.bv2 .bv-order-rank-icons {
  flex-shrink:0;
  display:flex; align-items:center; justify-content:flex-end;
  max-width:8vw; overflow:hidden;
}

.bv2 .bv-order-rank-icons img {
  max-height:2.2vw;
  width:auto;
  object-fit:contain;
}

.bv2 .bv-order-rank-icons > * {
  transform:scale(.82);
  transform-origin:center right;
}

.bv2 .bv-order-badge {
  font-size:.68vw; font-weight:900; text-transform:uppercase; letter-spacing:.04em;
  padding:.2vw .6vw; border-radius:999px; flex-shrink:0;
  background:rgba(99,102,241,.15); border:.052vw solid rgba(99,102,241,.3); color:#a5b4fc;
}
.bv2 .bv-order-badge.completed { background:rgba(34,197,94,.1); border-color:rgba(34,197,94,.3); color:#22c55e; }
.bv2 .bv-order-badge.in_progress,.bv2 .bv-order-badge.in\ progress { background:rgba(251,191,36,.1); border-color:rgba(251,191,36,.3); color:#fbbf24; }

/* Orders tab full list */
.bv2 .bv-orders-list {
  display:flex; flex-direction:column; gap:.6vw;
  max-width:none;
  width:100%;
}

.bv2 .bv-order-card {
  display:flex; align-items:center; gap:1vw;
  padding:.95vw 1.1vw; border-radius:.95vw;
  background:rgba(255,255,255,.025); border:.052vw solid rgba(255,255,255,.07);
  transition:border-color .15s;
}
.bv2 .bv-order-card:hover { border-color:rgba(99,102,241,.2); }
.bv2 .bv-order-card .bv-order-icon { width:2.2vw; height:2.2vw; border-radius:.55vw; }
.bv2 .bv-order-card .bv-order-icon img,
.bv2 .bv-order-card .bv-order-icon i { width:1.05vw; height:1.05vw; font-size:.85vw; }
.bv2 .bv-order-boost {
  flex-shrink:0;
  overflow:hidden;
  max-width:8.5vw;
  display:flex; align-items:center; justify-content:flex-end;
}

.bv2 .bv-order-boost img {
  max-height:2.2vw;
  width:auto;
  object-fit:contain;
}

.bv2 .bv-order-boost > * {
  transform:scale(.82);
  transform-origin:center right;
}

/* ── Hard override: prevent any img inside order cards from blowing up ── */
.bv2 .bv-order-card img,
.bv2 .bv-order-item img {
  max-height:2.2vw !important;
  width:auto !important;
  max-width:none !important;
  display:inline-block !important;
}
/* But icons inside the icon-box keep their own size */
.bv2 .bv-order-card .bv-order-icon img,
.bv2 .bv-order-item .bv-order-icon img {
  max-height:1.05vw !important;
  width:1.05vw !important;
}
/* Imgs in bv-order-boost (both static and dynamically loaded) */
.bv2 .bv-order-boost img,
.bv2 .bv-order-card .bv-boost img,
.bv2 img.bv-boost-img-fix {
  max-height:2.2vw !important;
  width:auto !important;
  max-width:none !important;
  display:inline-block !important;
}
@media(max-width:900px){
  .bv2 .bv-order-card img,
  .bv2 .bv-order-item img { max-height:32px !important; }
  .bv2 .bv-order-card .bv-order-icon img,
  .bv2 .bv-order-item .bv-order-icon img { max-height:18px !important; width:18px !important; }
  .bv2 .bv-order-boost img,
  .bv2 .bv-order-card .bv-boost img,
  .bv2 img.bv-boost-img-fix { max-height:32px !important; }
}
@media(max-width:480px){
  .bv2 .bv-order-card img,
  .bv2 .bv-order-item img { max-height:26px !important; }
  .bv2 .bv-order-boost img,
  .bv2 .bv-order-card .bv-boost img,
  .bv2 img.bv-boost-img-fix { max-height:26px !important; }
}

/* Feedback slider */
.bv2 .bv-feedback-head {
  display:flex; align-items:center; justify-content:space-between; gap:1vw; margin-bottom:1vw;
}
.bv2 .bv-feedback-nav { display:flex; gap:.4vw; }
.bv2 .bv-feedback-btn {
  width:2.2vw; height:2.2vw; border-radius:.55vw;
  border:.052vw solid rgba(255,255,255,.1); background:rgba(255,255,255,.04);
  color:rgba(255,255,255,.78); display:flex; align-items:center; justify-content:center;
  cursor:pointer; transition:.15s ease; font-size:.8vw;
}
.bv2 .bv-feedback-btn:hover { background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.18); }
.bv2 .bv-feedback-track {
  display:flex; gap:1vw; overflow-x:auto; overflow-y:hidden;
  scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch;
  padding:4px 2px 14px; scrollbar-width:none;
  max-width:none;
  width:100%;
}
.bv2 .bv-feedback-track::-webkit-scrollbar { display:none; }
.bv2 .bv-feedback-slide { flex:0 0 28vw; max-width:28vw; scroll-snap-align:start; display:flex; }

/* Review cards (feedback tab + slider) */
.bv2 .bv-reviews-list { display:flex; flex-direction:column; gap:1vw; }
.bv2 .bv-review-card {
  background:rgba(255,255,255,.03); border:.052vw solid rgba(255,255,255,.08);
  border-radius:.95vw; padding:1.15vw 1.2vw;
  display:flex; flex-direction:column; width:100%; height:100%;
  min-height:10.5vw;
}
.bv2 .bv-review-top {
  display:flex; align-items:flex-start; justify-content:space-between;
  gap:1vw; margin-bottom:.7vw; flex-wrap:wrap;
}
.bv2 .bv-review-left h5 {
  font-size:.92vw; font-weight:800; color:#fff; margin:0 0 .25vw;
  display:flex; align-items:center; gap:.5vw; flex-wrap:wrap;
}
.bv2 .bv-review-left h5 span { color:rgba(255,255,255,.35); font-weight:600; }
.bv2 .bv-review-left small { font-size:.75vw; color:rgba(255,255,255,.38); }
.bv2 .bv-review-stars {
  display:inline-flex; align-items:center; gap:.18vw;
  color:#fbbf24; font-size:.88vw; flex-shrink:0;
}
.bv2 .bv-review-pills {
  display:flex; flex-wrap:wrap; gap:.5vw; margin-bottom:.6vw;
}
.bv2 .bv-review-pill {
  display:inline-flex; align-items:center; gap:.35vw;
  font-size:.72vw; font-weight:700; padding:.22vw .65vw;
  border-radius:999px; background:rgba(99,102,241,.08);
  border:.052vw solid rgba(99,102,241,.15); color:rgba(255,255,255,.65);
}
.bv2 .bv-review-tags { display:flex; flex-wrap:wrap; gap:.4vw; margin-bottom:.55vw; }
.bv2 .bv-review-tag {
  display:inline-flex; align-items:center; gap:.4vw;
  font-size:.72vw; font-weight:600; color:rgba(255,255,255,.65);
}
.bv2 .bv-review-tag::before {
  content:''; width:.45vw; height:.45vw; border-radius:999px;
  background:#6366f1; box-shadow:0 0 0 .25vw rgba(99,102,241,.12);
  flex-shrink:0;
}
.bv2 .bv-review-comment {
  background:rgba(255,255,255,.025); border:.052vw solid rgba(99,102,241,.12);
  border-radius:.65vw; padding:.75vw .9vw; margin-top:.55vw;
  margin-top:auto;
}
.bv2 .bv-review-comment-label {
  font-size:.62vw; font-weight:900; text-transform:uppercase; letter-spacing:.08em;
  color:rgba(255,255,255,.3); margin-bottom:.4vw;
}
.bv2 .bv-review-comment-text {
  font-size:.82vw; color:rgba(255,255,255,.7); line-height:1.65;
  display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:5; overflow:hidden;
}
.bv2 .bv-review-comment-text.is-expanded { display:block; overflow:visible; }
.bv2 .bv-review-toggle {
  background:none; border:none; color:#818cf8; font-size:.72vw; font-weight:700;
  cursor:pointer; padding:.25vw 0 0; display:block; transition:color .12s;
}
.bv2 .bv-review-toggle:hover { color:#a5b4fc; }

/* Load more */
.bv2 .bv-load-more {
  text-align:center; margin-top:1.5vw;
}
.bv2 .bv-load-more-btn {
  display:inline-flex; align-items:center; gap:.4vw;
  font-size:.82vw; font-weight:800; text-transform:uppercase; letter-spacing:.06em;
  padding:.5vw 1.4vw; border-radius:999px; cursor:pointer;
  border:.052vw solid rgba(99,102,241,.3); background:rgba(99,102,241,.08);
  color:#a5b4fc; font-family:inherit; transition:all .15s;
}
.bv2 .bv-load-more-btn:hover { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.5); color:#fff; }

/* AJAX pagination for booster orders and reviews */
.bv2 .bv-reviews-list.is-paginated {
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:1vw;
}
.bv2 .bv-reviews-list.is-paginated .bv-review-card {
  min-height:0;
  background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.022));
  border-color:rgba(99,102,241,.16);
  box-shadow:0 .7vw 2vw rgba(0,0,0,.16);
}
.bv2 .bv-review-pagination {
  display:flex; align-items:center; justify-content:center; gap:.5vw;
  margin-top:1.5vw; flex-wrap:wrap;
}
.bv2 .bv-page-btn,
.bv2 .bv-page-arrow {
  height:2.35vw; min-width:2.35vw; padding:0 .85vw;
  border-radius:999px;
  border:.052vw solid rgba(99,102,241,.28);
  background:rgba(99,102,241,.065);
  color:#a5b4fc;
  font-size:.82vw; font-weight:900; font-family:inherit;
  cursor:pointer; transition:all .15s ease; text-decoration:none;
  display:inline-flex; align-items:center; justify-content:center; gap:.35vw;
}
.bv2 .bv-page-btn:hover,
.bv2 .bv-page-arrow:hover { background:rgba(99,102,241,.16); color:#fff; border-color:rgba(99,102,241,.5); }
.bv2 .bv-page-btn.is-active {
  color:#fff;
  background:linear-gradient(135deg,rgba(99,102,241,.95),rgba(139,92,246,.9));
  border-color:rgba(255,255,255,.12);
  box-shadow:0 .35vw 1.2vw rgba(99,102,241,.25);
}
.bv2 .bv-page-arrow:disabled,
.bv2 .bv-page-arrow.is-disabled { opacity:.35; cursor:not-allowed; background:rgba(255,255,255,.04); color:rgba(255,255,255,.35); pointer-events:none; }
.bv2 .bv-page-ellipsis { color:rgba(255,255,255,.32); font-size:.9vw; font-weight:900; padding:0 .15vw; }
.bv2 .bv-page-status { color:rgba(255,255,255,.38); font-size:.78vw; font-weight:700; padding:0 .35vw; }
.bv2 .bv-review-pagination.is-loading { opacity:.65; pointer-events:none; }
@media(max-width:768px){
  .bv2 .bv-reviews-list.is-paginated { grid-template-columns:1fr; gap:12px; }
  .bv2 .bv-review-pagination { gap:7px; margin-top:18px; }
  .bv2 .bv-page-btn,
  .bv2 .bv-page-arrow { height:36px; min-width:36px; padding:0 12px; font-size:.82rem; }
  .bv2 .bv-page-status { width:100%; text-align:center; font-size:.78rem; padding-top:2px; }
}


/* ── Sidebar ─────────────────────────────────────────── */
.bv2 .bv-sidebar-wrap { position:sticky !important; top:1.25vw; align-self:start; z-index:5; max-height:none; overflow:visible; scrollbar-width:none; }
.bv2 .bv-sidebar-wrap::-webkit-scrollbar { display:none; }
.bv2 .bv-sidebar {
  background:linear-gradient(160deg,#08071a 0%,#110d30 100%);
  border:.052vw solid rgba(99,102,241,.28); border-radius:1vw; overflow:hidden;
  box-shadow:0 .5vw 2.5vw rgba(99,102,241,.1); transition:box-shadow .25s;
}
.bv2 .bv-sidebar:hover { box-shadow:0 .9vw 3.8vw rgba(99,102,241,.2); }
.bv2 .bv-sidebar-bar { height:.2vw; background:linear-gradient(90deg,#6366f1,#818cf8,#6366f1); }
.bv2 .bv-sidebar-body { padding:1.4vw; }
.bv2 .bv-sidebar-prev {
  display:flex; align-items:center; gap:.8vw;
  padding-bottom:1vw; margin-bottom:1vw;
  border-bottom:.052vw solid rgba(99,102,241,.08);
}
.bv2 .bv-sidebar-prev img,
.bv2 .bv-sidebar-prev .bv-sb-ph {
  width:2.95vw; height:2.95vw; border-radius:50%; object-fit:cover;
  border:.12vw solid rgba(99,102,241,.45); flex-shrink:0;
}
.bv2 .bv-sb-ph {
  background:linear-gradient(135deg,rgba(99,102,241,.22),rgba(129,140,248,.12));
  display:flex; align-items:center; justify-content:center;
  font-size:.9vw; color:rgba(255,255,255,.72);
}
.bv2 .bv-sb-name { font-size:1.05vw; font-weight:900; color:#fff; margin-bottom:.18vw; }
.bv2 .bv-sb-meta { display:flex; flex-wrap:wrap; gap:.35vw; }
.bv2 .bv-sb-chip {
  display:inline-flex; align-items:center; gap:.28vw;
  font-size:.62vw; font-weight:900; text-transform:uppercase; letter-spacing:.06em;
  padding:.22vw .5vw; border-radius:999px;
  border:.052vw solid rgba(255,255,255,.08); color:rgba(255,255,255,.76); background:rgba(255,255,255,.04);
}
.bv2 .bv-sb-chip.online  { color:#22c55e; background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.22); }
.bv2 .bv-sb-chip.rank    { color:#c7d2fe; background:rgba(99,102,241,.12); border-color:rgba(99,102,241,.26); }
.bv2 .bv-sb-title { font-size:1.35vw; font-weight:900; color:#fff; margin-bottom:.18vw; }
.bv2 .bv-sb-sub { font-size:.84vw; color:rgba(255,255,255,.42); margin-bottom:.75vw; }
.bv2 .bv-sb-div { height:1px; background:linear-gradient(90deg,rgba(99,102,241,.18),rgba(99,102,241,.04),transparent); margin:.8vw 0; }
.bv2 .bv-sb-stats { display:flex; flex-direction:column; gap:.6vw; }
.bv2 .bv-sb-row { display:flex; align-items:center; justify-content:space-between; font-size:.88vw; }
.bv2 .bv-sb-row span { color:rgba(255,255,255,.52); display:flex; align-items:center; gap:.35vw; }
.bv2 .bv-sb-row strong { color:#fff; font-weight:800; }
.bv2 .bv-sb-cta {
  display:flex; align-items:center; justify-content:center; gap:.4vw;
  width:100%; margin-top:.2vw; padding:.82vw 1vw; border-radius:.8vw;
  text-decoration:none; font-size:.78vw; font-weight:900; text-transform:uppercase; letter-spacing:.06em;
  color:#eef2ff; background:linear-gradient(135deg,rgba(99,102,241,.96),rgba(139,92,246,.96));
  box-shadow:0 .6vw 1.8vw rgba(99,102,241,.22); transition:transform .18s,box-shadow .18s;
  border:none; cursor:pointer; font-family:inherit;
}
.bv2 .bv-sb-cta:hover { color:#fff; text-decoration:none; transform:translateY(-.08vw); box-shadow:0 .85vw 2.2vw rgba(99,102,241,.3); }
.bv2 .bv-sb-trust {
  display:flex; align-items:center; justify-content:center; gap:.28vw;
  font-size:.72vw; color:rgba(255,255,255,.34); margin-top:.65vw;
}

.bv2 .bv-sb-progression { display:flex; flex-direction:column; gap:.5vw; }
.bv2 .bv-sb-prog-head,
.bv2 .bv-sb-recent-head { display:flex; align-items:center; justify-content:space-between; gap:.6vw; margin-bottom:.75vw; }
.bv2 .bv-sb-prog-title,
.bv2 .bv-sb-recent-title { display:flex; align-items:center; gap:.38vw; font-size:.82vw; font-weight:900; text-transform:uppercase; letter-spacing:.07em; color:#fff; }
.bv2 .bv-sb-prog-title i { color:#f97316; }
.bv2 .bv-sb-recent-title i { color:#818cf8; }
.bv2 .bv-sb-prog-badge,
.bv2 .bv-sb-recent-badge { font-size:.55vw; font-weight:900; text-transform:uppercase; letter-spacing:.07em; padding:.16vw .45vw; border-radius:999px; white-space:nowrap; }
.bv2 .bv-sb-prog-badge { background:rgba(249,115,22,.12); border:.052vw solid rgba(249,115,22,.25); color:#fdba74; }
.bv2 .bv-sb-recent-badge { background:rgba(99,102,241,.14); border:.052vw solid rgba(99,102,241,.28); color:#a5b4fc; }
.bv2 .bv-sb-recent-note { display:flex; gap:.35vw; align-items:flex-start; font-size:.68vw; line-height:1.35; color:rgba(255,255,255,.42); margin:-.35vw 0 .75vw; }
.bv2 .bv-sb-recent-note i { color:#818cf8; margin-top:.08vw; }
.bv2 .bv-sb-prog-list,
.bv2 .bv-sb-recent-list { display:flex; flex-direction:column; gap:.45vw; }
.bv2 .bv-sb-prog-item { display:grid; grid-template-columns:2vw minmax(0,1fr) auto; align-items:center; gap:.55vw; padding:.52vw .58vw; border-radius:.65vw; background:rgba(255,255,255,.025); border:.052vw solid rgba(255,255,255,.065); }
.bv2 .bv-sb-prog-item:hover { border-color:rgba(249,115,22,.22); background:rgba(249,115,22,.045); }
.bv2 .bv-sb-prog-item.is-empty { opacity:.42; }
.bv2 .bv-sb-prog-item img { width:2vw; height:2vw; object-fit:contain; filter:drop-shadow(0 .18vw .45vw rgba(0,0,0,.35)); }
.bv2 .bv-sb-prog-rank { font-size:.76vw; font-weight:900; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bv2 .bv-sb-prog-record { font-size:.62vw; color:rgba(255,255,255,.38); margin-top:.05vw; white-space:nowrap; }
.bv2 .bv-sb-prog-wr { text-align:right; font-size:.72vw; font-weight:900; color:#fff; white-space:nowrap; }
.bv2 .bv-sb-prog-wr.good { color:#4ade80; }
.bv2 .bv-sb-prog-wr.mid { color:#fbbf24; }
.bv2 .bv-sb-prog-wr.low { color:#f87171; }
.bv2 .bv-sb-game { display:grid; grid-template-columns:.18vw 2.15vw minmax(0,1fr) auto; align-items:center; gap:.55vw; padding:.55vw .6vw; border-radius:.65vw; background:rgba(255,255,255,.025); border:.052vw solid rgba(255,255,255,.065); }
.bv2 .bv-sb-game:hover { border-color:rgba(99,102,241,.22); background:rgba(99,102,241,.045); }
.bv2 .bv-sb-game-stripe { width:.18vw; height:100%; min-height:2.2vw; border-radius:999px; background:rgba(255,255,255,.14); }
.bv2 .bv-sb-game-stripe.win { background:#22c55e; }
.bv2 .bv-sb-game-stripe.loss { background:#ef4444; }
.bv2 .bv-sb-game-stripe.remake { background:#f59e0b; }
.bv2 .bv-sb-game-champ { width:2.15vw; height:2.15vw; border-radius:.5vw; overflow:hidden; background:rgba(255,255,255,.05); border:.052vw solid rgba(255,255,255,.08); }
.bv2 .bv-sb-game-champ img { width:100%; height:100%; object-fit:cover; display:block; }
.bv2 .bv-sb-game-name { font-size:.78vw; font-weight:900; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bv2 .bv-sb-game-meta { font-size:.64vw; color:rgba(255,255,255,.38); margin-top:.08vw; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bv2 .bv-sb-mode-badge { display:inline-flex; align-items:center; gap:.22vw; margin-left:.25vw; padding:.08vw .32vw; border-radius:999px; font-size:.5vw; font-weight:900; letter-spacing:.05em; text-transform:uppercase; vertical-align:middle; }
.bv2 .bv-sb-mode-badge.solo { background:rgba(99,102,241,.16); border:.052vw solid rgba(99,102,241,.28); color:#c4b5fd; }
.bv2 .bv-sb-mode-badge.duo { background:rgba(234,179,8,.14); border:.052vw solid rgba(234,179,8,.32); color:#fde047; }
.bv2 .bv-sb-game-kda { text-align:right; font-size:.72vw; font-weight:900; color:rgba(255,255,255,.9); white-space:nowrap; }
.bv2 .bv-sb-game-kda span { display:block; font-size:.56vw; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.32); margin-top:.05vw; }
.bv2 .bv-sb-prog-empty,
.bv2 .bv-sb-prog-loading,
.bv2 .bv-sb-recent-empty,
.bv2 .bv-sb-recent-loading { display:flex; align-items:center; gap:.45vw; padding:.75vw; border-radius:.65vw; background:rgba(255,255,255,.025); border:.052vw dashed rgba(255,255,255,.08); font-size:.72vw; color:rgba(255,255,255,.42); }
.bv2 .bv-sb-prog-loading i { animation:bvSpin .8s linear infinite; color:#f97316; }
.bv2 .bv-sb-recent-loading i { animation:bvSpin .8s linear infinite; color:#818cf8; }
@media(max-width:900px) {
  .bv2 .bv-sidebar-wrap { position:relative !important; top:auto; max-height:none; overflow:visible; }
  .bv2 .bv-sb-prog-title,.bv2 .bv-sb-recent-title { font-size:.84rem; }
  .bv2 .bv-sb-prog-badge,.bv2 .bv-sb-recent-badge { font-size:.64rem; padding:3px 8px; }
  .bv2 .bv-sb-recent-note { font-size:.74rem; }
  .bv2 .bv-sb-prog-item { grid-template-columns:32px minmax(0,1fr) auto; gap:9px; padding:9px 10px; border-radius:10px; }
  .bv2 .bv-sb-prog-item img { width:32px; height:32px; }
  .bv2 .bv-sb-prog-rank,.bv2 .bv-sb-game-name { font-size:.84rem; }
  .bv2 .bv-sb-prog-record,.bv2 .bv-sb-game-meta { font-size:.72rem; }
  .bv2 .bv-sb-prog-wr,.bv2 .bv-sb-game-kda { font-size:.78rem; }
  .bv2 .bv-sb-game { grid-template-columns:3px 34px minmax(0,1fr) auto; gap:9px; padding:9px 10px; border-radius:10px; }
  .bv2 .bv-sb-game-stripe { width:3px; min-height:34px; }
  .bv2 .bv-sb-game-champ { width:34px; height:34px; border-radius:8px; }
  .bv2 .bv-sb-game-kda span { font-size:.62rem; }
  .bv2 .bv-sb-prog-empty,.bv2 .bv-sb-prog-loading,.bv2 .bv-sb-recent-empty,.bv2 .bv-sb-recent-loading { font-size:.78rem; padding:10px; }
}/* Disabled sidebar CTA */
.bv2 .bv-sb-cta-disabled {
  background:rgba(255,255,255,.06) !important;
  color:rgba(255,255,255,.3) !important;
  box-shadow:none !important; cursor:not-allowed !important;
  border:1px solid rgba(255,255,255,.08) !important;
}
.bv2 .bv-sb-cta-disabled:hover { transform:none !important; box-shadow:none !important; }
.bv2 .bv-sb-notice {
  display:flex; align-items:flex-start; gap:.4vw;
  font-size:.72vw; color:rgba(255,165,0,.7);
  margin-top:.5vw; line-height:1.5;
}

/* ── Tip Modal (Forgot Password style) ── */
#send_tip_modal {
  width: min(640px, calc(100vw - 32px)) !important;
  max-width: min(640px, calc(100vw - 32px)) !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
  overflow: visible !important;
}
#send_tip_modal .bv-tip-modal {
  position: relative;
  width: 100%;
  max-width: 640px;
  margin: 0 auto;
  overflow: hidden;
  border-radius: 30px;
  border: 1px solid rgba(79, 101, 255, .35);
  background:
    radial-gradient(circle at 86% 22%, rgba(76, 127, 248, 0.18) 0%, rgba(76, 127, 248, 0) 26%),
    linear-gradient(180deg, #0d1230 0%, #060a21 100%);
  box-shadow:
    0 34px 90px rgba(0,0,0,.75),
    inset 0 1px 0 rgba(255,255,255,.04),
    0 0 0 1px rgba(79, 101, 255, .08);
}
#send_tip_modal .bv-tip-modal::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  background: linear-gradient(90deg, rgba(255,255,255,.03) 0%, rgba(255,255,255,0) 35%);
}
#send_tip_modal .bv-tip-close {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 3;
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.08);
  color: rgba(255,255,255,.72);
  cursor: pointer;
  transition: all .18s ease;
  backdrop-filter: blur(8px);
}
#send_tip_modal .bv-tip-close:hover {
  background: rgba(255,255,255,.14);
  color: #fff;
  transform: translateY(-1px);
}
#send_tip_modal .bv-tip-header {
  position: relative;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 112px;
  gap: 24px;
  align-items: center;
  padding: 30px 38px 22px;
}
#send_tip_modal .bv-tip-copy { min-width: 0; }
#send_tip_modal .bv-tip-kicker {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.48rem 1rem;
  margin-bottom: 1rem;
  border-radius: 999px;
  border: 1px solid rgba(129,140,248,.32);
  background: rgba(99,102,241,.12);
  color: #d1d5ff;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}
#send_tip_modal .bv-tip-title {
  margin: 0 0 12px;
  font-size: clamp(32px, 3vw, 40px);
  line-height: 1.03;
  letter-spacing: -.02em;
  font-weight: 800;
  color: #fff;
}
#send_tip_modal .bv-tip-desc {
  margin: 0;
  max-width: 380px;
  font-size: 15px;
  line-height: 1.65;
  color: rgba(255,255,255,.68);
}
#send_tip_modal .bv-tip-recipient {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  margin-top: 18px;
}
#send_tip_modal .bv-tip-avatar {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(99, 102, 241, .34);
  background: linear-gradient(180deg, rgba(99,102,241,.18), rgba(8,11,35,.85));
  box-shadow: 0 10px 28px rgba(0,0,0,.28);
}
#send_tip_modal .bv-tip-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}
#send_tip_modal .bv-tip-avatar span {
  font-size: 19px;
  font-weight: 800;
  color: #dbe1ff;
}
#send_tip_modal .bv-tip-recipient-copy {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
#send_tip_modal .bv-tip-recipient-copy small {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .07em;
  text-transform: uppercase;
  color: rgba(160, 176, 255, .62);
}
#send_tip_modal .bv-tip-recipient-copy strong {
  color: #fff;
  font-size: 16px;
  font-weight: 800;
}
#send_tip_modal .bv-tip-icon-box {
  width: 76px;
  height: 76px;
  justify-self: end;
  margin-right: 28px;
  border-radius: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(180deg, #6d7cff 0%, #4a7ef8 100%);
  box-shadow: 0 22px 42px rgba(45, 101, 255, .24), inset 0 1px 0 rgba(255,255,255,.22);
  transform: rotate(-8deg);
}
#send_tip_modal .bv-tip-icon-box i {
  font-size: 30px;
  color: #fff;
}
#send_tip_modal .bv-tip-body {
  padding: 0 38px 34px;
  display: flex;
  flex-direction: column;
  gap: 22px;
}
#send_tip_modal .bv-tip-quick {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
}
#send_tip_modal .bv-tip-quick-btn {
  min-height: 54px;
  border-radius: 14px;
  border: 1px solid rgba(99,102,241,.24);
  background: rgba(255,255,255,.04);
  color: rgba(255,255,255,.72);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
  padding: 12px 10px;
  font-family: inherit;
  font-size: 17px;
  font-weight: 800;
  cursor: pointer;
  transition: all .18s ease;
}
#send_tip_modal .bv-tip-quick-btn:hover,
#send_tip_modal .bv-tip-quick-btn.active {
  background: rgba(99,102,241,.16);
  border-color: rgba(129,140,248,.45);
  color: #fff;
  box-shadow: 0 14px 26px rgba(99,102,241,.16);
}
#send_tip_modal .bv-tip-quick-sym {
  font-size: 12px;
  opacity: .58;
  margin-right: 1px;
}
#send_tip_modal .bv-tip-section {
  display: flex;
  flex-direction: column;
  padding-top: 4px;
}
#send_tip_modal .bv-tip-amount-wrap {
  padding-top: 14px;
  margin-bottom: 6px;
}
#send_tip_modal .bv-tip-amount-wrap .bv-tip-label {
  margin-bottom: 20px;
}
#send_tip_modal .bv-tip-label {
  display: block;
  margin: 0 0 14px;
  color: rgba(164, 180, 255, .8);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}
#send_tip_modal .bv-tip-optional {
  text-transform: none;
  font-weight: 600;
  opacity: .72;
}
#send_tip_modal .bv-tip-input-row {
  display: flex;
  align-items: center;
  gap: 10px;
}
#send_tip_modal .bv-tip-stepper {
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  border-radius: 14px;
  border: 1px solid rgba(99,102,241,.28);
  background: rgba(99,102,241,.08);
  color: #9aa7ff;
  font-size: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all .18s ease;
}
#send_tip_modal .bv-tip-stepper:hover {
  background: rgba(99,102,241,.16);
  border-color: rgba(129,140,248,.42);
  color: #fff;
}
#send_tip_modal .bv-tip-input-box {
  min-height: 56px;
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 18px;
  border-radius: 18px;
  border: 1px solid rgba(99,102,241,.18);
  background: rgba(255,255,255,.04);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
}
#send_tip_modal .bv-tip-input-box:focus-within,
#send_tip_modal .bv-tip-textarea:focus {
  border-color: rgba(79, 101, 255, .48);
  background: rgba(255,255,255,.05);
}
#send_tip_modal .bv-tip-currency {
  flex-shrink: 0;
  color: rgba(255,255,255,.58);
  font-size: 16px;
  font-weight: 700;
}
#send_tip_modal .bv-tip-input-box input {
  flex: 1;
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  text-align: center;
  color: #fff;
  font-size: 18px;
  font-weight: 800;
  font-family: inherit;
}
#send_tip_modal .bv-tip-textarea {
  width: 100%;
  min-height: 116px;
  resize: none;
  border-radius: 18px;
  border: 1px solid rgba(99,102,241,.18);
  background: rgba(255,255,255,.04);
  padding: 16px 18px;
  color: #fff;
  font-size: 14px;
  line-height: 1.5;
  font-family: inherit;
  outline: none;
  transition: all .18s ease;
}
#send_tip_modal .bv-tip-textarea::placeholder {
  color: rgba(255,255,255,.28);
}
#send_tip_modal .bv-tip-submit {
  width: 100%;
  min-height: 56px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: none;
  border-radius: 18px;
  padding: 15px 18px;
  background: linear-gradient(90deg, #6571ff 0%, #367cff 100%);
  color: #fff;
  font-family: inherit;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 20px 40px rgba(54, 124, 255, .24);
  transition: all .18s ease;
}
#send_tip_modal .bv-tip-submit:hover {
  transform: translateY(-1px);
  opacity: .98;
  box-shadow: 0 24px 44px rgba(54, 124, 255, .3);
}
#send_tip_modal .form-error {
  margin: 0 !important;
  font-size: 13px !important;
  line-height: 1.5 !important;
}
@media (max-width: 640px) {
  #send_tip_modal {
    width: calc(100vw - 24px) !important;
    max-width: calc(100vw - 24px) !important;
  }
  #send_tip_modal .bv-tip-modal {
    border-radius: 24px;
  }
  #send_tip_modal .bv-tip-header {
    grid-template-columns: 1fr 72px;
    gap: 14px;
    padding: 24px 18px 18px;
  }
  #send_tip_modal .bv-tip-kicker {
    margin-bottom: .8rem;
    font-size: 11px;
  }
  #send_tip_modal .bv-tip-title {
    font-size: 22px;
    margin-bottom: 8px;
  }
  #send_tip_modal .bv-tip-desc {
    font-size: 13px;
  }
  #send_tip_modal .bv-tip-recipient {
    margin-top: 14px;
    gap: 10px;
  }
  #send_tip_modal .bv-tip-avatar {
    width: 44px;
    height: 44px;
  }
  #send_tip_modal .bv-tip-recipient-copy strong {
    font-size: 14px;
  }
  #send_tip_modal .bv-tip-icon-box {
    width: 58px;
    height: 58px;
    margin-right: 34px;
    border-radius: 18px;
  }
  #send_tip_modal .bv-tip-icon-box i {
    font-size: 22px;
  }
  #send_tip_modal .bv-tip-close {
    top: 16px;
    right: 16px;
    width: 36px;
    height: 36px;
  }
  #send_tip_modal .bv-tip-body {
    padding: 0 18px 20px;
    gap: 20px;
  }
  #send_tip_modal .bv-tip-section {
    padding-top: 3px;
  }
  #send_tip_modal .bv-tip-amount-wrap {
    padding-top: 10px;
    margin-bottom: 4px;
  }
  #send_tip_modal .bv-tip-amount-wrap .bv-tip-label {
    margin-bottom: 16px;
  }
  #send_tip_modal .bv-tip-label {
    margin-bottom: 12px;
  }
  #send_tip_modal .bv-tip-quick {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  #send_tip_modal .bv-tip-quick-btn {
    min-height: 48px;
    font-size: 16px;
  }
  #send_tip_modal .bv-tip-input-row {
    gap: 8px;
  }
  #send_tip_modal .bv-tip-stepper {
    width: 42px;
    height: 42px;
    border-radius: 12px;
  }
  #send_tip_modal .bv-tip-input-box {
    min-height: 46px;
    padding: 8px 12px;
    border-radius: 14px;
  }
  #send_tip_modal .bv-tip-input-box input {
    font-size: 17px;
  }
  #send_tip_modal .bv-tip-textarea {
    min-height: 92px;
    padding: 14px 14px;
    border-radius: 14px;
  }
  #send_tip_modal .bv-tip-submit {
    min-height: 50px;
    border-radius: 16px;
    font-size: 15px;
  }
}


/* ── Mobile ─────────────────────────────────────────── */
@media(max-width:900px){
  html, body { overflow-x:hidden; }
  .bv2, .bv2 * { box-sizing:border-box; }
  .bv2 { overflow-x:hidden; }
  .bv2 header {
    min-height: auto !important;
    padding: calc(var(--lb-content-top, 126px) + 78px) 16px 48px !important;
    width: 100%;
  }
  .bv2 header .content {
    max-width: none !important;
    width: 100% !important;
    /* mobile: push the title under the navbar/buttons */
    margin: 52px 0 0 !important;
  }
  .bv2 header h1 {
    font-size: clamp(36px, 11vw, 50px) !important;
    line-height: .98 !important;
  }
  .bv2 header p {
    font-size: .9rem;
    line-height: 1.55;
  }
  .bv2 .bv-card {
    width:calc(100% - 20px);
    max-width:calc(100vw - 20px);
    margin:12px 10px;
    border-radius:14px;
    padding:14px;
    overflow:hidden;
  }
  .bv2 .bv-cover { height:120px; border-radius:10px 10px 0 0; }
  .bv2 .bv-avatar,.bv2 .bv-avatar-ph { width:72px; height:72px; top:80px; left:14px; }
  .bv2 .bv-avatar { border-width:2px; } .bv2 .bv-avatar-ph { font-size:2rem; border-width:2px; }
  .bv2 .bv-online-dot { width:14px; height:14px; border-width:2px; bottom:3px; right:3px; }
  .bv2 .bv-details { margin-top:50px; }
  .bv2 .bv-top { flex-direction:column; gap:10px; }
  .bv2 .bv-info h5 { font-size:1.65rem !important; }
  .bv2 .bv-info h6 { font-size:.82rem; }
  .bv2 .bv-chip,.bv2 .bv-sb-chip { font-size:.72rem; padding:4px 9px; }
  .bv2 .bv-actions { gap:6px; }
  .bv2 .bv-btn { font-size:.78rem; padding:8px 14px; }
  .bv2 .bv-nav-tabs a { font-size:.8rem; padding:10px 11px; }
  .bv2 .bv-nav-tabs {
    width:100%;
    max-width:100%;
  }
  .bv2 .bv-nav-tabs a { font-size:.8rem; padding:10px 11px; }
  .bv2 .bv-nav-tabs a i { font-size:.8rem; }
  .bv2 .bv-tab-count { font-size:.65rem; padding:2px 6px; }
  .bv2 .bv-tab-layout {
    grid-template-columns:minmax(0,1fr);
    gap:0;
    width:100%;
  }
  .bv2 .bv-tab-layout > div,
  .bv2 .bv-tab-pane,
  .bv2 .bv-sidebar-wrap,
  .bv2 .bv-sidebar,
  .bv2 .bv-sidebar-body,
  .bv2 .bv-info,
  .bv2 .bv-top {
    min-width:0;
    max-width:100%;
  }
  .bv2 .bv-stats-row { gap:8px; }
  .bv2 .bv-stat-card { min-width:calc(50% - 4px); border-radius:10px; padding:12px; gap:8px; }
  .bv2 .bv-stat-icon { width:32px; height:32px; font-size:.9rem; border-radius:7px; }
  .bv2 .bv-stat-val { font-size:.88rem !important; }
  .bv2 .bv-stat-lbl { font-size:.65rem !important; }
  .bv2 .bv-section-label { font-size:.8rem !important; }
  .bv2 .bv-section-label::before { width:3px; height:12px; }
  .bv2 .bv-description { font-size:.86rem; margin-bottom:18px; }
  .bv2 .bv-tiles { grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px; }
  .bv2 .bv-tile { border-radius:10px; padding:12px; }
  .bv2 .bv-tile-head { font-size:.72rem; margin-bottom:8px; }
  .bv2 .bv-tile-head img,.bv2 .bv-tile-head i { width:14px; height:14px; font-size:.8rem; }
  .bv2 .bv-rank-img { width:52px; height:52px; }
  .bv2 .bv-role-icon { width:32px; height:32px; }
  .bv2 .bv-lang-flag { width:22px; height:22px; }
  .bv2 .bv-champ-icon { width:28px; height:28px; border-radius:5px; }
  .bv2 .bv-recent-orders,
  .bv2 .bv-orders-list,
  .bv2 .bv-feedback-track { max-width:100%; }
  .bv2 .bv-order-item {
    padding:10px 12px; border-radius:10px; margin-bottom:8px; gap:8px;
    display:grid; grid-template-columns:34px minmax(0,1fr) auto; align-items:center;
  }
  .bv2 .bv-order-icon { width:34px; height:34px; border-radius:8px; }
  .bv2 .bv-order-icon img,.bv2 .bv-order-icon i { width:18px; height:18px; font-size:.9rem; }
  .bv2 .bv-order-meta { min-width:0; }
  .bv2 .bv-order-name { font-size:.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .bv2 .bv-order-detail { font-size:.72rem; line-height:1.45; }
  .bv2 .bv-order-rank-icons {
    max-width:56px; min-width:56px; justify-content:center;
  }
  .bv2 .bv-order-rank-icons > * {
    transform:scale(.68);
    transform-origin:center center;
  }
  .bv2 .bv-order-badge {
    grid-column:2 / 4; justify-self:start; margin-top:2px;
    font-size:.68rem; padding:2px 8px;
  }
  .bv2 .bv-order-card {
    padding:10px 12px; border-radius:10px; gap:8px;
    display:grid; grid-template-columns:34px minmax(0,1fr) auto; align-items:center;
  }
  .bv2 .bv-order-card .bv-order-icon { width:34px; height:34px; border-radius:8px; }
  .bv2 .bv-order-card .bv-order-icon img,.bv2 .bv-order-card .bv-order-icon i { width:18px; height:18px; font-size:.9rem; }
  .bv2 .bv-order-card .bv-order-meta { min-width:0; }
  .bv2 .bv-order-card .bv-order-name { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .bv2 .bv-order-boost {
    max-width:60px; min-width:60px; justify-content:center;
  }
  .bv2 .bv-order-boost > * {
    transform:scale(.68);
    transform-origin:center center;
  }
  .bv2 .bv-order-card .bv-order-badge {
    grid-column:2 / 4; justify-self:start; margin-top:2px;
  }
  .bv2 .bv-feedback-head {
    align-items:center;
    width:100%;
    max-width:100%;
  }
  .bv2 .bv-feedback-nav { flex-shrink:0; }
  .bv2 .bv-feedback-track {
    gap:10px;
    padding-right:2px;
    width:100%;
    max-width:100%;
  }
  .bv2 .bv-feedback-slide {
    flex:0 0 calc(100vw - 40px);
    max-width:calc(100vw - 40px);
  }
  .bv2 .bv-feedback-btn { width:32px; height:32px; font-size:.78rem; }
  .bv2 .bv-review-top { gap:8px; }
  .bv2 .bv-review-left { min-width:0; }
  .bv2 .bv-review-left h5 { line-height:1.35; }
  .bv2 .bv-review-left small { display:block; line-height:1.5; }
  .bv2 .bv-reviews-list { gap:10px; }
  .bv2 .bv-review-card { padding:14px; border-radius:10px; }
  .bv2 .bv-review-left h5 { font-size:.88rem; }
  .bv2 .bv-review-left small { font-size:.72rem; }
  .bv2 .bv-review-stars { font-size:.85rem; }
  .bv2 .bv-review-pill { font-size:.72rem; padding:3px 8px; }
  .bv2 .bv-review-tag { font-size:.72rem; }
  .bv2 .bv-review-tag::before { width:6px; height:6px; }
  .bv2 .bv-review-comment { padding:10px 12px; border-radius:8px; }
  .bv2 .bv-review-comment-label { font-size:.62rem; }
  .bv2 .bv-review-comment-text { font-size:.82rem; }
  .bv2 .bv-review-toggle { font-size:.72rem; }
  .bv2 .bv-load-more { margin-top:14px; }
  .bv2 .bv-load-more-btn { font-size:.78rem; padding:8px 16px; }
  .bv2 .bv-sidebar-wrap {
    position:static;
    max-height:none;
    overflow:visible;
    margin-top:20px;
    width:100%;
    max-width:100%;
  }
  .bv2 .bv-sidebar {
    border-radius:14px;
    width:100%;
    max-width:100%;
  }
  .bv2 .bv-sidebar-bar { height:3px; }
  .bv2 .bv-sidebar-body { padding:15px; }
  .bv2 .bv-sidebar-prev { gap:10px; padding-bottom:12px; margin-bottom:12px; }
  .bv2 .bv-sidebar-prev img,.bv2 .bv-sb-ph { width:42px; height:42px; }
  .bv2 .bv-sb-ph { font-size:.88rem; }
  .bv2 .bv-sb-name {
    font-size:.95rem;
    word-break:break-word;
  }
  .bv2 .bv-sb-meta {
    width:100%;
    max-width:100%;
  }
  .bv2 .bv-sb-chip {
    max-width:100%;
    white-space:normal;
  }
  .bv2 .bv-sb-title { font-size:1.1rem; }
  .bv2 .bv-sb-sub { font-size:.8rem; }
  .bv2 .bv-sb-row {
    font-size:.88rem;
    gap:8px;
    align-items:flex-start;
  }
  .bv2 .bv-sb-row span,
  .bv2 .bv-sb-row strong {
    min-width:0;
    word-break:break-word;
  }
  .bv2 .bv-sb-cta {
    font-size:.8rem;
    padding:12px 14px;
    border-radius:10px;
    width:100%;
    max-width:100%;
  }
  .bv2 .bv-sb-trust { font-size:.72rem; margin-top:8px; }
}

@media(max-width:768px){
  .bv2 .bv-card {
    width: calc(100% - 16px);
    max-width: calc(100vw - 16px);
    margin: 8px;
    padding: 12px;
    border-radius: 12px;
  }
  .bv2 .bv-cover {
    height: 96px;
    margin: -12px -12px 0 -12px;
    border-radius: 12px 12px 0 0;
  }
  .bv2 .bv-avatar,
  .bv2 .bv-avatar-ph {
    width: 62px;
    height: 62px;
    top: 58px;
    left: 12px;
  }
  .bv2 .bv-details { margin-top: 40px; }
  .bv2 .bv-top { gap: 12px; }
  .bv2 .bv-info h5 {
    font-size: 1.95rem !important;
    line-height: 1;
    margin-bottom: 8px;
    flex-wrap: wrap;
    row-gap: 6px;
  }
  .bv2 .bv-info h6 {
    font-size: .8rem;
    gap: 6px;
    margin-bottom: 0;
  }
  .bv2 .bv-chip,
  .bv2 .bv-sb-chip {
    font-size: .62rem;
    padding: 5px 10px;
    gap: 4px;
  }
  .bv2 .bv-actions {
    gap: 8px;
    width: 100%;
  }
  .bv2 .bv-btn {
    font-size: .72rem;
    padding: 10px 14px;
    min-height: 40px;
    justify-content: center;
  }
  .bv2 .bv-btn-primary { flex: 1 1 calc(60% - 4px); }
  .bv2 .bv-btn-ghost { flex: 1 1 calc(40% - 4px); }
  .bv2 .bv-nav-tabs a {
    font-size: .76rem;
    padding: 10px 10px;
  }
  .bv2 .bv-tab-count {
    font-size: .58rem;
    padding: 2px 5px;
  }
  .bv2 .bv-stats-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 8px;
  }
  .bv2 .bv-stat-card {
    min-width: 0;
    padding: 10px;
  }
  .bv2 .bv-stat-icon {
    width: 30px;
    height: 30px;
    font-size: .9rem;
  }
  .bv2 .bv-stat-val {
    font-size: 1rem !important;
  }
  .bv2 .bv-stat-lbl {
    font-size: .62rem !important;
  }
  .bv2 .bv-section-label {
    font-size: .78rem !important;
    margin-bottom: 10px;
  }
  .bv2 .bv-description {
    font-size: .82rem;
    line-height: 1.6;
  }
  .bv2 .bv-tiles {
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 8px;
  }
  .bv2 .bv-tile {
    padding: 10px;
  }
  .bv2 .bv-tile-head {
    font-size: .66rem;
    line-height: 1.3;
  }
  .bv2 .bv-rank-img { width: 46px; height: 46px; }
  .bv2 .bv-role-icon { width: 28px; height: 28px; }
  .bv2 .bv-lang-flag { width: 20px; height: 20px; }
  .bv2 .bv-champ-icon { width: 24px; height: 24px; }
  .bv2 .bv-order-item,
  .bv2 .bv-order-card {
    grid-template-columns: 36px minmax(0,1fr) 52px;
    gap: 10px;
    padding: 12px;
    align-items: start;
  }
  .bv2 .bv-order-icon,
  .bv2 .bv-order-card .bv-order-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
  }
  .bv2 .bv-order-icon img,
  .bv2 .bv-order-icon i,
  .bv2 .bv-order-card .bv-order-icon img,
  .bv2 .bv-order-card .bv-order-icon i {
    width: 18px;
    height: 18px;
    font-size: .88rem;
  }
  .bv2 .bv-order-name {
    font-size: .98rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .bv2 .bv-order-detail {
    font-size: .82rem;
    line-height: 1.45;
  }
  .bv2 .bv-order-rank-icons,
  .bv2 .bv-order-boost {
    min-width: 52px;
    max-width: 52px;
    align-self: center;
    justify-content: center;
  }
  .bv2 .bv-order-rank-icons > *,
  .bv2 .bv-order-boost > * {
    transform: scale(.95);
    transform-origin: center center;
  }
  .bv2 .bv-order-badge,
  .bv2 .bv-order-card .bv-order-badge {
    grid-column: 2 / 4;
    margin-top: 8px;
    font-size: .72rem;
    padding: 5px 10px;
  }
  .bv2 .bv-feedback-head { margin-bottom: 10px; }
  .bv2 .bv-feedback-btn {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    font-size: .92rem;
  }
  .bv2 .bv-feedback-track {
    gap: 8px;
    padding: 2px 0 10px;
  }
  .bv2 .bv-feedback-slide {
    flex: 0 0 100%;
    max-width: 100%;
  }
  .bv2 .bv-review-card {
    padding: 12px;
    border-radius: 12px;
  }
  .bv2 .bv-review-top {
    gap: 8px;
    margin-bottom: 8px;
  }
  .bv2 .bv-review-left h5 {
    font-size: .98rem;
    line-height: 1.35;
  }
  .bv2 .bv-review-left h5 span {
    display: inline;
    font-size: .9em;
  }
  .bv2 .bv-review-left small {
    font-size: .8rem;
    line-height: 1.45;
  }
  .bv2 .bv-review-stars {
    font-size: .78rem;
    gap: 1px;
  }
  .bv2 .bv-review-comment {
    padding: 10px 12px;
  }
  .bv2 .bv-review-comment-label {
    font-size: .64rem;
  }
  .bv2 .bv-review-comment-text {
    font-size: .9rem;
    line-height: 1.55;
  }
  .bv2 .bv-sidebar-wrap { margin-top: 18px; }
  .bv2 .bv-sidebar-body { padding: 14px; }
  .bv2 .bv-sidebar-prev {
    gap: 12px;
    align-items: center;
  }
  .bv2 .bv-sidebar-prev img,
  .bv2 .bv-sb-ph {
    width: 48px;
    height: 48px;
  }
  .bv2 .bv-sb-name { font-size: 1.6rem; }
  .bv2 .bv-sb-meta { gap: 6px; }
  .bv2 .bv-sb-title { font-size: 1.05rem; }
  .bv2 .bv-sb-sub {
    font-size: .84rem;
    line-height: 1.45;
  }
  .bv2 .bv-sb-row {
    font-size: .95rem;
    align-items: center;
  }
  .bv2 .bv-sb-cta {
    font-size: .86rem;
    min-height: 44px;
  }
}

@media(max-width:480px){
  .bv2 .bv-card {
    width: calc(100% - 12px);
    max-width: calc(100vw - 12px);
    margin: 6px;
    padding: 10px;
  }
  .bv2 .bv-cover {
    height: 82px;
    margin: -10px -10px 0 -10px;
  }
  .bv2 .bv-avatar,
  .bv2 .bv-avatar-ph {
    width: 56px;
    height: 56px;
    top: 50px;
    left: 10px;
  }
  .bv2 .bv-details { margin-top: 34px; }
  .bv2 .bv-info h5 { font-size: 1.75rem !important; }
  .bv2 .bv-chip,
  .bv2 .bv-sb-chip {
    font-size: .58rem;
    padding: 5px 8px;
  }
  .bv2 .bv-btn {
    font-size: .68rem;
    padding: 9px 12px;
  }
  .bv2 .bv-btn-primary { flex-basis: 100%; }
  .bv2 .bv-btn-ghost { flex-basis: calc(50% - 4px); }
  .bv2 .bv-nav-tabs a {
    font-size: .72rem;
    padding: 9px 8px;
  }
  .bv2 .bv-tab-count { font-size: .54rem; }
  .bv2 .bv-stats-row { grid-template-columns: 1fr 1fr; }
  .bv2 .bv-stat-card {
    padding: 9px;
    gap: 7px;
  }
  .bv2 .bv-stat-val { font-size: .92rem !important; }
  .bv2 .bv-tiles { grid-template-columns: 1fr 1fr; }
  .bv2 .bv-order-item,
  .bv2 .bv-order-card {
    grid-template-columns: 32px minmax(0,1fr) 44px;
    padding: 10px;
    gap: 8px;
  }
  .bv2 .bv-order-name { font-size: .92rem; }
  .bv2 .bv-order-detail { font-size: .77rem; }
  .bv2 .bv-order-rank-icons,
  .bv2 .bv-order-boost {
    min-width: 44px;
    max-width: 44px;
  }
  .bv2 .bv-order-rank-icons > *,
  .bv2 .bv-order-boost > * {
    transform: scale(.82);
  }
  .bv2 .bv-feedback-btn {
    width: 34px;
    height: 34px;
    font-size: .84rem;
  }
  .bv2 .bv-review-card { padding: 11px; }
  .bv2 .bv-review-left h5 {
    font-size: .9rem;
    display: block;
  }
  .bv2 .bv-review-left h5 span {
    display: block;
    margin-top: 2px;
  }
  .bv2 .bv-review-left small { font-size: .76rem; }
  .bv2 .bv-review-comment-text { font-size: .84rem; }
  .bv2 .bv-sidebar-body { padding: 12px; }
  .bv2 .bv-sidebar-prev img,
  .bv2 .bv-sb-ph {
    width: 42px;
    height: 42px;
  }
  .bv2 .bv-sb-name { font-size: 1.35rem; }
  .bv2 .bv-sb-row { font-size: .88rem; }
  .bv2 .bv-sb-cta { font-size: .8rem; }
}


@media(max-width:768px){
  .bv2 .bv-info h5 { font-size: 2.15rem !important; }
  .bv2 .bv-info h6 {
    font-size: .92rem;
    gap: 8px;
    line-height: 1.45;
  }
  .bv2 .bv-info h6 > img {
    width: 18px !important;
    height: 18px !important;
  }
  .bv2 .bv-chip,
  .bv2 .bv-sb-chip {
    font-size: .74rem;
    padding: 6px 12px;
    gap: 5px;
  }
  .bv2 .bv-btn {
    font-size: .78rem;
    padding: 9px 12px;
    min-height: 40px;
  }
  .bv2 .bv-nav-tabs a {
    font-size: .96rem;
    padding: 12px 12px;
  }
  .bv2 .bv-nav-tabs a i { font-size: .86rem; }
  .bv2 .bv-tab-count {
    font-size: .72rem;
    padding: 3px 7px;
  }
  .bv2 .bv-stat-card { padding: 12px; }
  .bv2 .bv-stat-icon {
    width: 34px;
    height: 34px;
    font-size: 1rem;
  }
  .bv2 .bv-stat-val { font-size: 1.15rem !important; }
  .bv2 .bv-stat-lbl { font-size: .7rem !important; }
  .bv2 .bv-section-label { font-size: .86rem !important; }
  .bv2 .bv-description {
    font-size: .95rem;
    line-height: 1.7;
  }
  .bv2 .bv-tile { padding: 12px; }
  .bv2 .bv-tile-head {
    font-size: .74rem;
    line-height: 1.35;
  }
  .bv2 .bv-rank-img { width: 52px; height: 52px; }
  .bv2 .bv-role-icon { width: 30px; height: 30px; }
  .bv2 .bv-lang-flag { width: 22px; height: 22px; }
  .bv2 .bv-champ-icon { width: 28px; height: 28px; }

  .bv2 .bv-order-item,
  .bv2 .bv-order-card {
    grid-template-columns: 36px minmax(0,1fr) 68px;
    gap: 10px;
    padding: 13px;
  }
  .bv2 .bv-order-icon,
  .bv2 .bv-order-card .bv-order-icon {
    width: 36px;
    height: 36px;
  }
  .bv2 .bv-order-icon img,
  .bv2 .bv-order-icon i,
  .bv2 .bv-order-card .bv-order-icon img,
  .bv2 .bv-order-card .bv-order-icon i {
    width: 18px;
    height: 18px;
    font-size: .9rem;
  }
  .bv2 .bv-order-name { font-size: 1rem; }
  .bv2 .bv-order-detail {
    font-size: .9rem;
    line-height: 1.5;
  }
  .bv2 .bv-order-rank-icons,
  .bv2 .bv-order-boost {
    min-width: 68px;
    max-width: 68px;
    align-self: center;
    justify-content: center;
  }
  .bv2 .bv-order-rank-icons img,
  .bv2 .bv-order-boost img {
    max-height: 18px;
    width: auto;
  }
  .bv2 .bv-order-rank-icons i,
  .bv2 .bv-order-boost i {
    font-size: .95rem !important;
  }
  .bv2 .bv-order-rank-icons > *,
  .bv2 .bv-order-boost > * {
    transform: scale(1.08);
    transform-origin: center center;
  }
  .bv2 .bv-order-badge,
  .bv2 .bv-order-card .bv-order-badge {
    font-size: .78rem;
    padding: 6px 11px;
    margin-top: 10px;
  }

  .bv2 .bv-feedback-btn {
    width: 40px;
    height: 40px;
    font-size: .96rem;
  }
  .bv2 .bv-review-card { padding: 14px; }
  .bv2 .bv-review-left h5 {
    font-size: 1.04rem;
    line-height: 1.35;
  }
  .bv2 .bv-review-left small {
    font-size: .86rem;
    line-height: 1.5;
  }
  .bv2 .bv-review-stars {
    font-size: .9rem;
    gap: 2px;
  }
  .bv2 .bv-review-stars i { font-size: .9rem !important; }
  .bv2 .bv-review-comment-label { font-size: .68rem; }
  .bv2 .bv-review-comment-text {
    font-size: .95rem;
    line-height: 1.6;
  }

  .bv2 .bv-sidebar-prev img,
  .bv2 .bv-sb-ph {
    width: 52px;
    height: 52px;
  }
  .bv2 .bv-sb-name { font-size: 1.8rem; }
  .bv2 .bv-sb-title { font-size: 1.12rem; }
  .bv2 .bv-sb-sub {
    font-size: .9rem;
    line-height: 1.5;
  }
  .bv2 .bv-sb-row { font-size: .98rem; }
  .bv2 .bv-sb-cta {
    font-size: .9rem;
    min-height: 46px;
  }
}


@media(max-width:900px){
  .bv2 .bv-info h6 img {
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    min-height: 20px !important;
    border-width: 1px !important;
    border-radius: 999px !important;
    object-fit: cover !important;
    display: inline-block !important;
    vertical-align: middle !important;
  }
  .bv2 .bv-info h6 {
    gap: 8px;
  }
}

@media(max-width:480px){
  .bv2 .bv-info h6 img {
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    min-height: 18px !important;
  }
}

@media(max-width:900px){
  .bv2 .bv-server-tags {
    gap: 8px;
  }
  .bv2 .bv-server-tag {
    font-size: .76rem;
    padding: 6px 12px;
    border-radius: 8px;
    line-height: 1;
  }
  .bv2 .bv-langs {
    gap: 8px;
  }
  .bv2 .bv-lang-flag {
    width: 26px;
    height: 26px;
    border-width: 1px;
  }
  .bv2 .bv-info h6 > img {
    width: 22px !important;
    height: 22px !important;
    border-width: 1px !important;
  }
}

@media(max-width:480px){
  .bv2 .bv-server-tags {
    gap: 8px;
  }
  .bv2 .bv-server-tag {
    font-size: .74rem;
    padding: 7px 12px;
    border-radius: 8px;
    line-height: 1;
  }
  .bv2 .bv-langs {
    gap: 8px;
  }
  .bv2 .bv-lang-flag {
    width: 24px;
    height: 24px;
    border-width: 1px;
  }
  .bv2 .bv-info h6 > img {
    width: 20px !important;
    height: 20px !important;
    border-width: 1px !important;
  }
}

@media(max-width:480px){
  .bv2 .bv-card {
    width: calc(100% - 10px);
    max-width: calc(100vw - 10px);
    margin: 5px;
    padding: 12px;
  }
  .bv2 .bv-cover {
    height: 88px;
    margin: -12px -12px 0 -12px;
  }
  .bv2 .bv-avatar,
  .bv2 .bv-avatar-ph {
    width: 58px;
    height: 58px;
    top: 52px;
    left: 12px;
  }
  .bv2 .bv-details { margin-top: 38px; }
  .bv2 .bv-info h5 { font-size: 1.95rem !important; }
  .bv2 .bv-info h6 {
    font-size: .88rem;
    gap: 7px;
  }
  .bv2 .bv-info h6 > img {
    width: 16px !important;
    height: 16px !important;
  }
  .bv2 .bv-chip,
  .bv2 .bv-sb-chip {
    font-size: .7rem;
    padding: 6px 10px;
  }
  .bv2 .bv-btn {
    font-size: .74rem;
    padding: 8px 11px;
    min-height: 38px;
  }
  .bv2 .bv-nav-tabs a {
    font-size: .9rem;
    padding: 11px 10px;
  }
  .bv2 .bv-tab-count {
    font-size: .68rem;
    padding: 3px 6px;
  }
  .bv2 .bv-stat-card {
    padding: 10px;
    gap: 8px;
  }
  .bv2 .bv-stat-icon {
    width: 32px;
    height: 32px;
  }
  .bv2 .bv-stat-val { font-size: 1.02rem !important; }
  .bv2 .bv-stat-lbl { font-size: .68rem !important; }
  .bv2 .bv-section-label { font-size: .82rem !important; }
  .bv2 .bv-description { font-size: .9rem; }
  .bv2 .bv-tile-head { font-size: .7rem; }
  .bv2 .bv-order-item,
  .bv2 .bv-order-card {
    grid-template-columns: 42px minmax(0,1fr) 60px;
    padding: 12px 11px;
    gap: 9px;
  }
  .bv2 .bv-order-name { font-size: .98rem; }
  .bv2 .bv-order-detail {
    font-size: .85rem;
    line-height: 1.5;
  }
  .bv2 .bv-order-rank-icons,
  .bv2 .bv-order-boost {
    min-width: 60px;
    max-width: 60px;
  }
  .bv2 .bv-order-rank-icons img,
  .bv2 .bv-order-boost img {
    max-height: 17px;
  }
  .bv2 .bv-order-rank-icons i,
  .bv2 .bv-order-boost i {
    font-size: .9rem !important;
  }
  .bv2 .bv-order-rank-icons > *,
  .bv2 .bv-order-boost > * {
    transform: scale(1);
  }
  .bv2 .bv-order-badge,
  .bv2 .bv-order-card .bv-order-badge {
    font-size: .76rem;
    padding: 6px 10px;
  }
  .bv2 .bv-feedback-btn {
    width: 38px;
    height: 38px;
    font-size: .9rem;
  }
  .bv2 .bv-review-card { padding: 13px; }
  .bv2 .bv-review-left h5 {
    font-size: .98rem;
    line-height: 1.35;
  }
  .bv2 .bv-review-left small {
    font-size: .82rem;
    line-height: 1.5;
  }
  .bv2 .bv-review-stars {
    font-size: .84rem;
    gap: 2px;
  }
  .bv2 .bv-review-stars i { font-size: .84rem !important; }
  .bv2 .bv-review-comment-text {
    font-size: .9rem;
    line-height: 1.58;
  }
  .bv2 .bv-sidebar-body { padding: 13px; }
  .bv2 .bv-sidebar-prev img,
  .bv2 .bv-sb-ph {
    width: 46px;
    height: 46px;
  }
  .bv2 .bv-sb-name { font-size: 1.55rem; }
  .bv2 .bv-sb-title { font-size: 1.08rem; }
  .bv2 .bv-sb-sub { font-size: .88rem; }
  .bv2 .bv-sb-row { font-size: .94rem; }
  .bv2 .bv-sb-cta {
    font-size: .86rem;
    min-height: 44px;
  }
}

/* ═══════════════════════════════════════════════════════
   BV-HERO — full-bleed banner hero. Replaces the generic
   "Booster Profile" text header + the separate cover-in-card;
   the booster's own banner is now the header, with avatar,
   name, chips and actions overlaid directly on it.
═══════════════════════════════════════════════════════ */
.bv-hero{
  position:relative;
  width:100%;
  margin-top:var(--lb-content-top, 132px);
  height:clamp(300px, 30vw, 420px);
  overflow:hidden;
  isolation:isolate;
}
.bv-hero__banner-img{
  position:absolute; inset:0; z-index:0;
  width:100%; height:100%; object-fit:cover; display:block;
}
.bv-hero__scrim{
  position:absolute; inset:0; z-index:1;
  background:
    linear-gradient(180deg, rgba(7,8,21,.05) 0%, rgba(7,8,21,.35) 55%, #070815 100%),
    linear-gradient(90deg, rgba(7,8,21,.7) 0%, transparent 45%);
}
.bv-hero__content{
  position:absolute; left:0; right:0; bottom:0; z-index:2;
  padding:0 4.167vw 2vw;
  display:flex; align-items:flex-end; gap:1.6vw; flex-wrap:wrap;
}
.bv-hero__content .bv-avatar,
.bv-hero__content .bv-avatar-ph{
  position:relative !important; top:auto !important; left:auto !important;
  width:8vw; height:8vw;
  flex-shrink:0;
}
.bv-hero__info{ flex:1; min-width:0; padding-bottom:.3vw; }
.bv-hero__name{
  font-size:clamp(28px, 2.6vw, 42px) !important; font-weight:900;
  line-height:1.05 !important; margin:0 0 .5vw !important;
  display:flex; align-items:center; gap:.5vw; flex-wrap:wrap;
  font-family:'superchargestraight',sans-serif;
  background:linear-gradient(135deg,#fff 0%,#a5b4fc 55%,#818cf8 100%);
  -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
  text-shadow:0 12px 34px rgba(0,0,0,.4);
}
.bv-hero__name .bv-check{ -webkit-text-fill-color:#6366f1; font-size:.7em; }
.bv-hero__chips{ display:flex; align-items:center; gap:.6vw; flex-wrap:wrap; }
.bv-hero__actions{ display:flex; gap:.6vw; flex-wrap:wrap; align-items:center; flex-shrink:0; padding-bottom:.3vw; }
.bv-hero__actions .bv-btn-txt-short{ display:none; }

/* Card now starts directly with the nav tabs — no avatar overlap left to clear */
.bv2 .bv-details{ margin-top:1.2vw !important; }

/* Mobile: banner stays a plain decorative strip — avatar/name/chips/buttons
   move below it onto a solid panel instead of overlaying the image. */
@media(max-width:900px){
  .bv-hero{
    height:auto;
    isolation:auto;
    overflow:visible;
  }
  .bv-hero__banner-img{
    position:static;
    height:130px;
    width:100%;
  }
  .bv-hero__scrim{ display:none; }
  .bv-hero__content{
    position:static;
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:12px;
    padding:16px;
    background:#0d1021;
    border-bottom:1px solid rgba(255,255,255,.07);
  }
  .bv-hero__content .bv-avatar,
  .bv-hero__content .bv-avatar-ph{ width:56px; height:56px; }
  .bv-hero__info{ flex:1; min-width:0; padding-bottom:0; }
  .bv-hero__name{ font-size:clamp(20px,6.5vw,26px) !important; }
  .bv-hero__actions{ width:100%; order:3; padding-bottom:0; gap:8px; flex-wrap:nowrap; }
  .bv-hero__actions .bv-btn{
    flex:1 1 0; min-width:0; justify-content:center;
    font-size:12px; padding:10px 6px; gap:5px;
    white-space:nowrap;
  }
  .bv-hero__actions .bv-btn i{ font-size:.95em; }
  .bv-hero__actions .bv-btn-txt-full{ display:none; }
  .bv-hero__actions .bv-btn-txt-short{ display:inline; }
  .bv2 .bv-details{ margin-top:16px !important; }

  /* Chips stay on one line and scroll horizontally instead of stacking */
  .bv-hero__chips{
    flex-wrap:nowrap;
    overflow-x:auto;
    overflow-y:hidden;
    gap:6px;
    scroll-snap-type:x proximity;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:none;
    -ms-overflow-style:none;
    /* bleed past the padded container so the row can scroll edge to edge */
    margin-inline:-16px;
    padding-inline:16px;
    padding-bottom:2px;
    mask-image:linear-gradient(to right, transparent 0, #000 12px, #000 calc(100% - 18px), transparent 100%);
    -webkit-mask-image:linear-gradient(to right, transparent 0, #000 12px, #000 calc(100% - 18px), transparent 100%);
  }
  .bv-hero__chips::-webkit-scrollbar{ display:none; }
  .bv-hero__chips > *{ flex:0 0 auto; scroll-snap-align:start; }
  .bv2 .bv-chip--lang{ gap:5px; padding-left:6px; }
  .bv2 .bv-chip__flag{ width:15px; height:15px; border-width:1px; }
  .bv-hero__tz{ margin-top:8px; gap:6px; font-size:11.5px; }
  .bv-hero__tz-zone{ max-width:52vw; }
}

/* ---- Re-skin cards/buttons to the sitewide flat dark panel system
   (same tokens as accounts/items/digital-goods view pages) ---- */
.bv2 .bv-card{
  margin:2vw 4.167vw 4vw !important;
  padding:0 !important;
  background:transparent !important;
  border:none !important;
  box-shadow:none !important;
  border-radius:0 !important;
}
@media(max-width:900px){
  .bv2 .bv-card{ margin:1.2vw 16px 32px !important; padding:0 !important; }
}
.bv2 .bv-stat-card,
.bv2 .bv-tile,
.bv2 .bv-expertise-main,
.bv2 .bv-expertise-side-card,
.bv2 .bv-order-item,
.bv2 .bv-order-card,
.bv2 .bv-review-card{
  background:rgba(255,255,255,.03) !important;
  border-color:rgba(255,255,255,.06) !important;
}
.bv2 .bv-btn-action,
.bv2 .bv-btn-primary,
.bv2 .bv-btn-ghost{
  background:linear-gradient(135deg,#7c83ff,#5b57ff 55%,#4f46e5) !important;
  box-shadow:0 14px 34px rgba(91,87,255,.38) !important;
}

/* ---- Booster Info — unified card grid (replaces the mismatched
   game-row + boxed-sidebar split: every block is now the same card) ---- */
.bv2 .bv-info-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(15vw,1fr));
  gap:1vw;
  margin-bottom:2vw;
}
.bv2 .bv-info-card{
  border:.052vw solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.03);
  border-radius:1vw;
  padding:1.1vw 1.2vw;
  min-width:0;
}
.bv2 .bv-info-card__head{
  display:flex; align-items:center; gap:.5vw;
  font-size:.72vw; font-weight:900; text-transform:uppercase; letter-spacing:.09em;
  color:rgba(255,255,255,.4); margin-bottom:.9vw;
}
.bv2 .bv-info-card__head i{ color:#818cf8; font-size:.85vw; }
.bv2 .bv-info-card__icon{ width:1.2vw; height:1.2vw; object-fit:contain; border-radius:.28vw; flex-shrink:0; }
.bv2 .bv-info-card--game .bv-info-card__head{
  font-size:.95vw; font-weight:900; letter-spacing:0; text-transform:none; color:#fff;
}
.bv2 .bv-info-card--game .bv-info-card__icon{ width:1.7vw; height:1.7vw; }
.bv2 .bv-info-rank{ display:flex; align-items:center; gap:.7vw; }
.bv2 .bv-info-rank img{ width:3.4vw; height:3.4vw; object-fit:contain; flex-shrink:0; }
.bv2 .bv-info-card__pills{ display:flex; flex-wrap:wrap; align-items:center; gap:.45vw; }

/* ---- Per-game grouping — when a booster has several games, each game's
   rank/roles/champions/agents live inside their own labeled, accent-bordered
   box instead of floating loose in one flat grid ---- */
.bv2 .bv-info-groups{ display:flex; flex-direction:column; gap:1vw; margin-bottom:2vw; }
.bv2 .bv-info-group{
  border:.052vw solid rgba(255,255,255,.08);
  border-radius:1vw;
  overflow:hidden;
  background:rgba(255,255,255,.015);
}
.bv2 .bv-info-group__head{
  display:flex; align-items:center; gap:.6vw;
  padding:.75vw 1.2vw;
  font-size:.95vw; font-weight:900; color:#fff;
  background:rgba(255,255,255,.035);
  border-bottom:.052vw solid rgba(255,255,255,.07);
  border-left:.22vw solid var(--bv-game-accent, #818cf8);
}
.bv2 .bv-info-group__head img{ width:1.6vw; height:1.6vw; object-fit:contain; flex-shrink:0; }
.bv2 .bv-info-group--lol{ --bv-game-accent:#c8aa3c; }
.bv2 .bv-info-group--val{ --bv-game-accent:#ff4655; }
.bv2 .bv-info-group--tft{ --bv-game-accent:#64b4ff; }
.bv2 .bv-info-group__grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(13vw,1fr));
  gap:1vw;
  padding:1.2vw;
}
.bv2 .bv-info-group__grid .bv-info-card{
  border-color:rgba(255,255,255,.06);
  background:rgba(255,255,255,.025);
}

/* Dense game profile rows: many games stay readable without making the page
   unnecessarily tall. */
.bv2 .bv-info-groups{gap:8px;margin-bottom:20px}
.bv2 .bv-info-group{border-radius:10px}
.bv2 .bv-info-group__head{gap:8px;padding:7px 10px;font-size:12px;border-left-width:3px}
.bv2 .bv-info-group__head img{width:18px;height:18px}
.bv2 .bv-info-group__grid{grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:7px;padding:8px}
.bv2 .bv-info-group__grid .bv-info-card{padding:8px 10px;border-radius:8px;min-height:58px}
.bv2 .bv-info-group__grid .bv-info-card__head{font-size:9px;gap:5px;margin-bottom:5px}
.bv2 .bv-info-group__grid .bv-info-card__head i{font-size:10px}
.bv2 .bv-info-group__grid .bv-info-rank{gap:8px}
.bv2 .bv-info-group__grid .bv-info-rank img{width:30px;height:30px}
.bv2 .bv-info-group__grid .bv-rank-name{font-size:12px}
.bv2 .bv-info-group__grid .bv-champ-icon,.bv2 .bv-info-group__grid .bv-role-icon{width:28px;height:28px}

@media(max-width:900px){
  .bv2 .bv-info-grid{ grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:20px; }
  .bv2 .bv-info-card{ border-radius:12px; padding:14px; }
  .bv2 .bv-info-card__head{ font-size:.68rem; margin-bottom:10px; gap:6px; }
  .bv2 .bv-info-card__icon{ width:16px; height:16px; }
  .bv2 .bv-info-card--game .bv-info-card__head{ font-size:.95rem; }
  .bv2 .bv-info-card--game .bv-info-card__icon{ width:24px; height:24px; }
  .bv2 .bv-info-rank img{ width:44px; height:44px; }
  .bv2 .bv-info-card__pills{ gap:6px; }
  .bv2 .bv-info-groups{ gap:12px; margin-bottom:20px; }
  .bv2 .bv-info-group{ border-radius:12px; }
  .bv2 .bv-info-group__head{ font-size:.95rem; padding:12px 14px; gap:8px; border-left-width:3px; }
  .bv2 .bv-info-group__head img{ width:22px; height:22px; }
  .bv2 .bv-info-group__grid{ grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:10px; padding:12px; }
}

</style>

<div class="bv-hero">
  <img class="bv-hero__banner-img"
       src="<?= htmlspecialchars($bCover, ENT_QUOTES,'UTF-8') ?>"
       style="object-position: <?= htmlspecialchars($bCoverPosition, ENT_QUOTES,'UTF-8') ?>;"
       alt="<?= $bUsername ?>">
  <div class="bv-hero__scrim"></div>
  <div class="bv-hero__content">

    <!-- Avatar + online dot -->
    <?php if ($bIcon): ?>
      <div class="bv-avatar">
        <img src="<?= htmlspecialchars($bIcon, ENT_QUOTES,'UTF-8') ?>" alt="<?= $bUsername ?>">
        <span class="bv-online-dot <?= $__statusClass ?>"></span>
      </div>
    <?php else: ?>
      <div class="bv-avatar-ph">
        <?= $bInitial ?>
        <span class="bv-online-dot <?= $__statusClass ?>"></span>
      </div>
    <?php endif; ?>

    <div class="bv-hero__info">
      <h1 class="bv-hero__name">
        <?= $bUsername ?>
        <i class="fa-solid fa-badge-check bv-check"></i>
      </h1>
      <div class="bv-hero__chips">
        <?php if ($bRankName): ?>
          <span class="bv-chip rank">
            <i class="fa-solid fa-trophy" style="font-size:.8em;"></i> <?= $bRankName ?>
          </span>
        <?php endif; ?>
        <?php if ($avgRating > 0): ?>
          <span class="bv-chip" style="background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.3);color:#fbbf24;gap:.35vw;">
            <i class="fa-solid fa-star" style="font-size:.8em;"></i>
            <span><?= number_format($avgRating, 1) ?> <span style="color:rgba(255,255,255,.38);font-weight:600;">(<?= $reviewsCount ?>)</span></span>
          </span>
        <?php endif; ?>
        <span class="bv-chip <?= $__isOnline ? 'online-chip' : 'offline-chip' ?>">
          <span style="width:.8vw;height:.8vw;min-width:8px;min-height:8px;border-radius:50%;background:<?= $__isOnline ? '#22c55e' : 'rgba(255,255,255,.3)' ?>;display:inline-block;"></span>
          <?= $__statusText ?>
        </span>
        <?php foreach ($languages as $lang): ?>
          <span class="bv-chip bv-chip--lang" title="<?= htmlspecialchars($lang,ENT_QUOTES,'UTF-8') ?>">
            <img class="bv-chip__flag" src="<?= ASSET_URL ?>/core/main/img/languages/<?= htmlspecialchars($lang,ENT_QUOTES,'UTF-8') ?>.png"
                 alt="<?= htmlspecialchars($lang,ENT_QUOTES,'UTF-8') ?>">
            <?= htmlspecialchars(strtoupper($lang),ENT_QUOTES,'UTF-8') ?>
          </span>
        <?php endforeach; ?>
      </div>

      <?php
        // Local time of the booster, rendered live in the visitor's browser.
        $__bvTimezone = trim((string)($booster['timezone'] ?? ''));
      ?>
      <?php if ($__bvTimezone !== ''): ?>
        <div class="bv-hero__tz" data-bv-tz="<?= htmlspecialchars($__bvTimezone, ENT_QUOTES, 'UTF-8') ?>">
          <i class="fa-regular fa-clock" aria-hidden="true"></i>
          <span class="bv-hero__tz-time" data-bv-tz-time>--:--</span>
          <span class="bv-hero__tz-sep" aria-hidden="true">·</span>
          <span class="bv-hero__tz-zone"><?= htmlspecialchars(str_replace('_', ' ', $__bvTimezone), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      <?php endif; ?>
    </div>

    <div class="bv-hero__actions">
      <?php if ($__boostDisabled): ?>
        <button class="bv-btn bv-btn-disabled" disabled title="<?= t('This booster is not accepting new assignments at the moment') ?>">
          <i class="fa-solid fa-bolt"></i> <span class="bv-btn-txt-full">Play with <?= $bUsername ?></span><span class="bv-btn-txt-short">Play</span>
        </button>
        <button class="bv-btn bv-btn-disabled" disabled title="<?= t('Not accepting assignments at the moment') ?>">
          <i class="fa-solid fa-chalkboard-teacher"></i> Coaching
        </button>
      <?php else: ?>
        <button class="bv-btn bv-btn-action js-request-boost" data-booster="<?= $__boosterId ?>" data-games="<?= htmlspecialchars(implode('|', $__gameList), ENT_QUOTES, 'UTF-8') ?>" data-username="<?= $bUsername ?>">
          <i class="fa-solid fa-bolt"></i> <span class="bv-btn-txt-full">Play with <?= $bUsername ?></span><span class="bv-btn-txt-short">Play</span>
        </button>
        <button class="bv-btn bv-btn-action js-request-coaching" data-booster="<?= $__boosterId ?>" data-games="<?= htmlspecialchars(implode('|', $__gameList), ENT_QUOTES, 'UTF-8') ?>" data-username="<?= $bUsername ?>">
          <i class="fa-solid fa-chalkboard-teacher"></i> Coaching
        </button>
      <?php endif; ?>
      <button class="bv-btn bv-btn-action js-send-tip" data-booster="<?= $__boosterId ?>">
        <i class="fa-solid fa-heart"></i> <span class="bv-btn-txt-full"><?= t('Send Tip') ?></span><span class="bv-btn-txt-short"><?= t('Tip') ?></span>
      </button>
    </div>

  </div>
</div>

<div class="bv-card">

  <div class="bv-details">

    <!-- Nav tabs -->
    <div class="bv-nav-tabs" id="bvNavTabs">
      <a href="#bv-overview" class="active" data-bvtab="bv-overview">
        <i class="fa-solid fa-gauge-high"></i> Overview
      </a>
      <a href="#bv-orders" data-bvtab="bv-orders">
        <i class="fa-solid fa-clipboard-list"></i> Orders
        <span class="bv-tab-count"><?= $ordersCount ?></span>
      </a>
      <a href="#bv-feedback" data-bvtab="bv-feedback">
        <i class="fa-solid fa-star"></i> Reviews
        <span class="bv-tab-count"><?= $reviewsCount ?></span>
      </a>
      <a href="#bv-performance" data-bvtab="bv-performance">
        <i class="fa-solid fa-chart-line"></i> Performance
      </a>
    </div>

    <!-- Tab layout -->
    <div class="bv-tab-layout">

      <div>

        <!-- ── OVERVIEW TAB ────────────────────────── -->
        <div class="bv-tab-pane active" id="bv-overview">

          <!-- Stat cards -->
          <div class="bv-stats-row">
            <div class="bv-stat-card">
              <div class="bv-stat-icon"><i class="fa-solid fa-check-circle"></i></div>
              <div>
                <div class="bv-stat-val"><?= (int)($__orderStats['completed'] ?? 0) ?></div>
                <div class="bv-stat-lbl">Completed</div>
              </div>
            </div>
            <div class="bv-stat-card">
              <div class="bv-stat-icon"><i class="fa-solid fa-wave-pulse"></i></div>
              <div>
                <div class="bv-stat-val"><?= (int)($__orderStats['in_progress'] ?? 0) ?></div>
                <div class="bv-stat-lbl">In Progress</div>
              </div>
            </div>
            <?php if ($avgRating > 0): ?>
            <div class="bv-stat-card" style="border-color:rgba(251,191,36,.2);background:rgba(251,191,36,.05);">
              <div class="bv-stat-icon" style="background:rgba(251,191,36,.12);border-color:rgba(251,191,36,.25);color:#fbbf24;">
                <i class="fa-solid fa-star"></i>
              </div>
              <div>
                <div class="bv-stat-val" style="color:#fbbf24;"><?= number_format($avgRating,1) ?></div>
                <div class="bv-stat-lbl">Avg Rating</div>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($__isOnline): ?>
            <div class="bv-stat-card" style="border-color:rgba(34,197,94,.2);background:rgba(34,197,94,.05);">
              <div class="bv-stat-icon" style="background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.25);color:#22c55e;">
                <i class="fa-solid fa-circle"></i>
              </div>
              <div>
                <div class="bv-stat-val" style="font-size:1.1vw;color:#22c55e;padding-top:.2vw;">Online</div>
                <div class="bv-stat-lbl">Status</div>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Description -->
          <?php if (trim($descDecoded) !== '' && trim($descDecoded) !== 'No description provided.'): ?>
          <div style="margin-bottom:2vw;">
            <div class="bv-section-label">Description</div>
            <div class="bv-description"><?= nl2br(htmlspecialchars($descDecoded, ENT_QUOTES,'UTF-8')) ?></div>
          </div>
          <?php endif; ?>
<!-- Booster Info — each game gets its own labeled, accent-bordered group so
     rank/roles/champions/agents can't be mixed up when a booster plays several games -->
<div class="bv-section-label">Booster Info</div>
<div class="bv-info-groups">

  <?php if ($__hasLol): ?>
    <div class="bv-info-group bv-info-group--lol">
      <div class="bv-info-group__head"><img src="<?= ASSET_URL ?>/website/images/icons/league-of-legends.png" alt=""><span>League of Legends</span></div>
      <div class="bv-info-group__grid">
        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-trophy"></i><span>Current Rank</span></div>
          <div class="bv-info-rank">
            <img src="<?= htmlspecialchars($lolRankImg, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($__lolRankLabel, ENT_QUOTES) ?>">
            <div class="bv-rank-name"><?= htmlspecialchars($__lolRankLabel, ENT_QUOTES) ?></div>
          </div>
        </div>

        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-people-group"></i><span>Roles</span></div>
          <div class="bv-info-card__pills">
            <?php if (!empty($roles)): foreach ($roles as $role): ?>
              <span class="bv-role-icon" title="<?= htmlspecialchars($role, ENT_QUOTES) ?>"><img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= htmlspecialchars($role, ENT_QUOTES) ?>.png" alt="<?= htmlspecialchars($role, ENT_QUOTES) ?>"></span>
            <?php endforeach; else: ?><span class="bv-no-specialty">No roles selected</span><?php endif; ?>
          </div>
        </div>

        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-chess-board"></i><span>Champions</span></div>
          <div class="bv-info-card__pills">
            <?php $__champLimit=5; if (!empty($champions)): foreach (array_slice($champions,0,$__champLimit) as $champ): $__safeChamp=htmlspecialchars($champ,ENT_QUOTES); ?>
              <img class="bv-champ-icon" src="<?= LOL_CHAMP_URL ?>/<?= $__safeChamp ?>.png" alt="<?= $__safeChamp ?>" title="<?= $__safeChamp ?>">
            <?php endforeach; if (count($champions)>$__champLimit): ?>
              <span class="bv-more-count bv-more-count--tooltip" tabindex="0">+<?= count($champions)-$__champLimit ?>
                <span class="bv-champion-tooltip">
                  <span class="bv-champion-tooltip__title">More Champions</span>
                  <span class="bv-champion-tooltip__grid">
                    <?php foreach (array_slice($champions, $__champLimit) as $__moreChamp): $__moreChampSafe = htmlspecialchars($__moreChamp, ENT_QUOTES); ?>
                      <span class="bv-champion-tooltip__item">
                        <img src="<?= LOL_CHAMP_URL ?>/<?= $__moreChampSafe ?>.png" alt="<?= $__moreChampSafe ?>">
                        <span><?= $__moreChampSafe ?></span>
                      </span>
                    <?php endforeach; ?>
                  </span>
                </span>
              </span>
            <?php endif; else: ?><span class="bv-no-specialty">No champions selected</span><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($__hasVal): ?>
    <div class="bv-info-group bv-info-group--val">
      <div class="bv-info-group__head"><img src="<?= ASSET_URL ?>/website/images/icons/valorant.png" alt=""><span>Valorant</span></div>
      <div class="bv-info-group__grid">
        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-trophy"></i><span>Current Rank</span></div>
          <div class="bv-info-rank">
            <img src="<?= htmlspecialchars($__valRankImg, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($__valRankLabel, ENT_QUOTES) ?>" onerror="this.style.display='none'">
            <div class="bv-rank-name"><?= htmlspecialchars($__valRankLabel, ENT_QUOTES) ?></div>
          </div>
        </div>

        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-user-astronaut"></i><span>Agents</span></div>
          <div class="bv-info-card__pills">
            <?php $__agentLimit=6; $__agentShown=0; if (!empty($agents)): foreach ($agents as $__ag): if ($__agentShown >= $__agentLimit) break; $__agKey=trim($__ag); $__agIcon=$__agentsData[$__agKey]['icon']??''; $__agName=$__agentsData[$__agKey]['name']??$__agKey; if (!$__agIcon) continue; $__agentShown++; ?>
              <img class="bv-champ-icon" src="<?= htmlspecialchars($__agIcon,ENT_QUOTES) ?>" alt="<?= htmlspecialchars($__agName,ENT_QUOTES) ?>" title="<?= htmlspecialchars($__agName,ENT_QUOTES) ?>">
            <?php endforeach; if (count($agents)>$__agentLimit): ?><span class="bv-more-count">+<?= count($agents)-$__agentLimit ?></span><?php endif; else: ?><span class="bv-no-specialty">No agents selected</span><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($__hasTft): ?>
    <div class="bv-info-group bv-info-group--tft">
      <div class="bv-info-group__head"><img src="<?= ASSET_URL ?>/website/images/icons/teamfight-tactics.png" alt=""><span>Teamfight Tactics</span></div>
      <div class="bv-info-group__grid">
        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-trophy"></i><span>Current Rank</span></div>
          <div class="bv-info-rank">
            <img src="<?= htmlspecialchars($__tftRankImg, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($__tftRankName, ENT_QUOTES) ?>">
            <div class="bv-rank-name"><?= htmlspecialchars($__tftRankName, ENT_QUOTES) ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php foreach ($__dynamicGameList as $__dynamicGame):
    $__dynamicLabel = util_game_display_name($__dynamicGame);
    $__dynamicIcon = util_game_icon_url($__dynamicGame);
    $__dynamicProfile = (array)($__dynamicGameProfiles[$__dynamicGame] ?? []);
    $__dynamicTier = (int)($__dynamicProfile['rank_tier'] ?? 0);
    $__dynamicDivision = (int)($__dynamicProfile['rank_division'] ?? 0);
    $__dynamicConfig = lb_generic_game_rank_config($__dynamicGame) ?? [];
    $__dynamicRankName = (string)(($__dynamicConfig['ranks'] ?? [])[$__dynamicTier] ?? 'Unranked');
    if ($__dynamicDivision > 0) $__dynamicRankName .= ' ' . (['','I','II','III','IV','V'][$__dynamicDivision] ?? '');
    $__dynamicRankImg = lb_booster_game_rank_icon_url($__dynamicGame, $__dynamicTier);
    $__dynamicSpecialtyOptions = lb_booster_game_specialty_options($__dynamicGame);
    $__dynamicSpecialtyMap = array_column($__dynamicSpecialtyOptions, null, 'key');
    $__dynamicSpecialties = (array)($__dynamicProfile['specialties'] ?? []);
    $__dynamicSpecialtyLabel = (string)($__dynamicSpecialtyOptions[0]['label'] ?? 'Specialties');
  ?>
    <div class="bv-info-group bv-info-group--dynamic">
      <div class="bv-info-group__head">
        <?php if ($__dynamicIcon): ?><img src="<?= htmlspecialchars($__dynamicIcon, ENT_QUOTES) ?>" alt=""><?php endif; ?>
        <span><?= htmlspecialchars($__dynamicLabel, ENT_QUOTES) ?></span>
      </div>
      <div class="bv-info-group__grid">
        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-trophy"></i><span>Current Rank</span></div>
          <div class="bv-info-rank">
            <?php if ($__dynamicRankImg): ?><img src="<?= htmlspecialchars($__dynamicRankImg, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($__dynamicRankName, ENT_QUOTES) ?>" onerror="this.style.display='none'"><?php endif; ?>
            <div class="bv-rank-name"><?= htmlspecialchars($__dynamicRankName, ENT_QUOTES) ?></div>
          </div>
        </div>
        <?php if ($__dynamicSpecialtyOptions): ?>
        <div class="bv-info-card">
          <div class="bv-info-card__head"><i class="fa-solid fa-user-astronaut"></i><span><?= htmlspecialchars($__dynamicSpecialtyLabel, ENT_QUOTES) ?></span></div>
          <div class="bv-info-card__pills">
            <?php if ($__dynamicSpecialties): foreach (array_slice($__dynamicSpecialties, 0, 8) as $__specialtyKey): $__specialty = $__dynamicSpecialtyMap[$__specialtyKey] ?? null; if (!$__specialty) continue; ?>
              <img class="bv-champ-icon" src="<?= htmlspecialchars($__specialty['icon'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($__specialty['name'], ENT_QUOTES) ?>" title="<?= htmlspecialchars($__specialty['name'], ENT_QUOTES) ?>">
            <?php endforeach; if (count($__dynamicSpecialties) > 8): ?><span class="bv-more-count">+<?= count($__dynamicSpecialties) - 8 ?></span><?php endif; else: ?><span class="bv-no-specialty">None selected</span><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

</div>

<?php if (!empty($languages) || !empty($__servers)): ?>
<div class="bv-info-grid">
  <?php if (!empty($languages)): ?>
    <div class="bv-info-card">
      <div class="bv-info-card__head"><img class="bv-info-card__icon" src="<?= ASSET_URL ?>/website/images/boosters/lang.svg" alt=""><span>Languages</span></div>
      <div class="bv-info-card__pills">
        <?php foreach ($languages as $lang): ?><span class="bv-side-pill"><img class="bv-lang-flag" src="<?= ASSET_URL ?>/core/main/img/languages/<?= htmlspecialchars($lang,ENT_QUOTES) ?>.png" alt=""><span><?= htmlspecialchars(strtoupper($lang),ENT_QUOTES) ?></span></span><?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($__servers)): ?>
    <div class="bv-info-card">
      <div class="bv-info-card__head"><i class="fa-solid fa-server"></i><span>Servers</span></div>
      <div class="bv-info-card__pills">
        <?php foreach ($__servers as $__sv): ?><span class="bv-side-pill" title="<?= htmlspecialchars($__serverNames[$__sv]??strtoupper($__sv),ENT_QUOTES) ?>"><?= htmlspecialchars(strtoupper($__sv),ENT_QUOTES) ?></span><?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Recent orders preview -->
          <?php if (!empty($ordersList)): ?>
          <div class="bv-recent-orders">
            <div class="bv-section-label">Recent Orders</div>
            <?php foreach (array_slice($ordersList, 0, 4) as $order): if ($order === null) continue; ?>
              <div class="bv-order-item">
                <div class="bv-order-icon">
                  <?= util_boost_form_icon_html($order['icon'] ?? '', 2.2, '') ?>
                </div>

                <div class="bv-order-meta">
                  <div class="bv-order-name"><?= htmlspecialchars($order['name'] ?? '', ENT_QUOTES,'UTF-8') ?></div>
                  <div class="bv-order-detail"><?= util_format_boost_overview($order['game'], $order['type'], $order) ?></div>
                </div>

                <div class="bv-order-rank-icons">
                  <?= util_format_boost_icons_new($order['game'], $order['type'], $order) ?>
                </div>

                <?php $ost = strtolower(str_replace('_',' ', $order['status'] ?? '')); ?>
                <span class="bv-order-badge <?= htmlspecialchars(str_replace(' ','_',$ost),ENT_QUOTES,'UTF-8') ?>">
                  <?= htmlspecialchars(ucwords($ost),ENT_QUOTES,'UTF-8') ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Recent feedbacks slider -->
          <?php if (!empty($reviews)): ?>
          <div>
            <div class="bv-feedback-head">
              <div class="bv-section-label" style="margin-bottom:0;">Recent Feedback</div>
              <div class="bv-feedback-nav">
                <button class="bv-feedback-btn" id="bvFbPrev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="bv-feedback-btn" id="bvFbNext" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </div>
            <div class="bv-feedback-track" id="bvFbTrack">
              <?php foreach (array_slice($reviews, 0, 10) as $review):
                $rDate = !empty($review['order']['created_at']) ? date('d.m.Y', strtotime($review['order']['created_at'])) : '';
                $rComment = trim((string)($review['comments'] ?? ''));
              ?>
              <div class="bv-feedback-slide">
                <div class="bv-review-card">
                  <div class="bv-review-top">
                    <div class="bv-review-left">
                      <h5>
                        <?= t('Customer') ?> <?= function_exists('mask_guest_style') ? mask_guest_style($review['order']['client']) : htmlspecialchars($review['order']['client'] ?? 'Guest', ENT_QUOTES,'UTF-8') ?>
                        <span>| <?= t('Order') ?> #<?= (int)($review['order']['id'] ?? 0) ?></span>
                      </h5>
                      <small>
                        <?= util_format_boost_overview($review['order']['game'], $review['order']['type'], $review['order']) ?>
                        <?= $rDate ? ' · ' . $rDate : '' ?>
                      </small>
                    </div>
                    <div class="bv-review-stars">
                      <?php for ($s=1;$s<=5;$s++): ?>
                        <i class="fa-solid fa-star" style="opacity:<?= $s <= (int)($review['overall'] ?? 0) ? '1' : '.2' ?>;font-size:.85vw;"></i>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <?php if ($rComment !== ''): ?>
                  <div class="bv-review-comment">
                    <div class="bv-review-comment-label">Comment</div>
                    <div class="bv-review-comment-text"><?= htmlspecialchars($rComment, ENT_QUOTES,'UTF-8') ?></div>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

        </div><!-- /bv-overview -->

        <!-- ── ORDERS TAB ──────────────────────────── -->
        <div class="bv-tab-pane" id="bv-orders">
          <?php if (empty($ordersList)): ?>
            <div style="text-align:center;padding:3vw 0;color:rgba(255,255,255,.3);font-size:1.1vw;">
              <i class="fa-solid fa-clipboard-list" style="font-size:3vw;display:block;margin-bottom:1vw;"></i>
              <?= t('This booster has no completed orders yet.') ?>
            </div>
          <?php else: ?>
            <div class="bv-orders-list" id="bvOrdersList" data-per-page="<?= $ordersPerPage ?>">
              <?php foreach ($ordersList as $order): if ($order === null) continue; ?>
                <?php $ost = strtolower(str_replace('_',' ', $order['status'] ?? '')); ?>
                <div class="bv-order-card">
                  <div class="bv-order-icon">
                    <?= util_boost_form_icon_html($order['icon'] ?? '', 2.2, '') ?>
                  </div>
                  <div class="bv-order-meta">
                    <div class="bv-order-name"><?= htmlspecialchars($order['name'] ?? '', ENT_QUOTES,'UTF-8') ?></div>
                    <div class="bv-order-detail"><?= util_format_boost_overview($order['game'], $order['type'], $order) ?></div>
                  </div>
                  <div class="bv-order-boost">
                    <?= util_format_boost_icons_new($order['game'], $order['type'], $order) ?>
                  </div>
                  <span class="bv-order-badge <?= htmlspecialchars(str_replace(' ','_',$ost),ENT_QUOTES,'UTF-8') ?>">
                    <?= htmlspecialchars(ucwords($ost),ENT_QUOTES,'UTF-8') ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if ($ordersTotalPages > 1): ?>
            <div class="bv-review-pagination" id="bvOrdersPagination" data-booster="<?= (int)$__boosterId ?>" data-page="<?= $ordersPage ?>" data-total-pages="<?= $ordersTotalPages ?>" data-per-page="<?= $ordersPerPage ?>">
              <?php if ($ordersPage > 1): ?>
                <button type="button" class="bv-page-arrow" data-order-page-btn="<?= $ordersPage - 1 ?>" data-page-prev aria-label="Previous orders page"><i class="fa-solid fa-chevron-left"></i></button>
              <?php else: ?>
                <button type="button" class="bv-page-arrow is-disabled" disabled aria-disabled="true"><i class="fa-solid fa-chevron-left"></i></button>
              <?php endif; ?>

              <?php foreach (bv_profile_page_numbers($ordersPage, $ordersTotalPages) as $pageNo): ?>
                <?php if ($pageNo === '...'): ?>
                  <span class="bv-page-ellipsis">…</span>
                <?php else: ?>
                  <button type="button" class="bv-page-btn<?= (int)$pageNo === $ordersPage ? ' is-active' : '' ?>" data-order-page-btn="<?= (int)$pageNo ?>"><?= (int)$pageNo ?></button>
                <?php endif; ?>
              <?php endforeach; ?>

              <?php if ($ordersPage < $ordersTotalPages): ?>
                <button type="button" class="bv-page-arrow" data-order-page-btn="<?= $ordersPage + 1 ?>" data-page-next aria-label="Next orders page"><i class="fa-solid fa-chevron-right"></i></button>
              <?php else: ?>
                <button type="button" class="bv-page-arrow is-disabled" disabled aria-disabled="true"><i class="fa-solid fa-chevron-right"></i></button>
              <?php endif; ?>

              <span class="bv-page-status" id="bvOrdersPageStatus"><?= $ordersPage ?> / <?= $ordersTotalPages ?></span>
            </div>
            <?php endif; ?>
          <?php endif; ?>
        </div><!-- /bv-orders -->

        <!-- ── FEEDBACK TAB ────────────────────────── -->
        <div class="bv-tab-pane" id="bv-feedback">
          <?php if (empty($reviews)): ?>
            <div style="text-align:center;padding:3vw 0;color:rgba(255,255,255,.3);font-size:1.1vw;">
              <i class="fa-solid fa-star" style="font-size:3vw;display:block;margin-bottom:1vw;opacity:.3;"></i>
              <?= t('This booster has no reviews yet.') ?>
            </div>
          <?php else: ?>
            <div class="bv-reviews-list is-paginated" id="bvReviewsList" data-per-page="<?= $reviewsPerPage ?>">
              <?php foreach ($reviews as $review):
                $rDate = !empty($review['order']['created_at']) ? date('d.m.Y', strtotime($review['order']['created_at'])) : '';
                $rComment = trim((string)($review['comments'] ?? ''));
                $highlights = (array) json_decode($review['highlights'] ?? '[]', true);
              ?>
              <div class="bv-review-card">
                <div class="bv-review-top">
                  <div class="bv-review-left">
                    <h5>
                      <?= t('Customer') ?> <?= function_exists('mask_guest_style') ? mask_guest_style($review['order']['client']) : htmlspecialchars($review['order']['client'] ?? 'Guest', ENT_QUOTES,'UTF-8') ?>
                      <span>| <?= t('Order') ?> #<?= (int)($review['order']['id'] ?? 0) ?></span>
                    </h5>
                    <small>
                      <?= util_format_boost_overview($review['order']['game'], $review['order']['type'], $review['order']) ?>
                      <?= $rDate ? ' · ' . $rDate : '' ?>
                    </small>
                  </div>
                  <div class="bv-review-stars">
                    <?php for ($s=1;$s<=5;$s++): ?>
                      <i class="fa-solid fa-star" style="opacity:<?= $s <= (int)($review['overall'] ?? 0) ? '1' : '.2' ?>;"></i>
                    <?php endfor; ?>
                  </div>
                </div>

                <div class="bv-review-pills">
                  <?php if (!empty($review['communication'])): ?>
                    <span class="bv-review-pill"><i class="fa-solid fa-comments" style="font-size:.7em;"></i> Communication <?= function_exists('process_stars') ? process_stars($review['communication']) : '' ?></span>
                  <?php endif; ?>
                  <?php if (!empty($review['skill'])): ?>
                    <span class="bv-review-pill"><i class="fa-solid fa-bolt" style="font-size:.7em;"></i> Skill <?= function_exists('process_stars') ? process_stars($review['skill']) : '' ?></span>
                  <?php endif; ?>
                  <?php if (!empty($review['speed'])): ?>
                    <span class="bv-review-pill"><i class="fa-solid fa-gauge-high" style="font-size:.7em;"></i> Speed <?= function_exists('process_stars') ? process_stars($review['speed']) : '' ?></span>
                  <?php endif; ?>
                </div>

                <?php if (!empty($highlights)): ?>
                <div class="bv-review-tags">
                  <?php foreach ($highlights as $hl): ?>
                    <span class="bv-review-tag"><?= htmlspecialchars(ucwords(str_replace('-',' ',$hl)),ENT_QUOTES,'UTF-8') ?></span>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($rComment !== ''): ?>
                <div class="bv-review-comment">
                  <div class="bv-review-comment-label">Comment</div>
                  <div class="bv-review-comment-text js-review-text"><?= htmlspecialchars($rComment, ENT_QUOTES,'UTF-8') ?></div>
                  <button class="bv-review-toggle js-review-toggle" hidden aria-expanded="false">Show more</button>
                </div>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <?php if ($reviewsTotalPages > 1): ?>
            <div class="bv-review-pagination" id="bvReviewPagination" data-booster="<?= $__boosterId ?>" data-page="<?= $reviewsPage ?>" data-total-pages="<?= $reviewsTotalPages ?>" data-per-page="<?= $reviewsPerPage ?>">
              <?php if ($reviewsPage > 1): ?>
                <button type="button" class="bv-page-arrow" data-review-page-btn="<?= $reviewsPage - 1 ?>" data-page-prev aria-label="Previous reviews page"><i class="fa-solid fa-chevron-left"></i></button>
              <?php else: ?>
                <button type="button" class="bv-page-arrow is-disabled" disabled aria-disabled="true"><i class="fa-solid fa-chevron-left"></i></button>
              <?php endif; ?>

              <?php foreach (bv_profile_page_numbers($reviewsPage, $reviewsTotalPages) as $pageNo): ?>
                <?php if ($pageNo === '...'): ?>
                  <span class="bv-page-ellipsis">…</span>
                <?php else: ?>
                  <button type="button" class="bv-page-btn<?= (int)$pageNo === $reviewsPage ? ' is-active' : '' ?>" data-review-page-btn="<?= (int)$pageNo ?>"><?= (int)$pageNo ?></button>
                <?php endif; ?>
              <?php endforeach; ?>

              <?php if ($reviewsPage < $reviewsTotalPages): ?>
                <button type="button" class="bv-page-arrow" data-review-page-btn="<?= $reviewsPage + 1 ?>" data-page-next aria-label="Next reviews page"><i class="fa-solid fa-chevron-right"></i></button>
              <?php else: ?>
                <button type="button" class="bv-page-arrow is-disabled" disabled aria-disabled="true"><i class="fa-solid fa-chevron-right"></i></button>
              <?php endif; ?>

              <span class="bv-page-status" id="bvReviewPageStatus"><?= $reviewsPage ?> / <?= $reviewsTotalPages ?></span>
            </div>
            <?php endif; ?>
          <?php endif; ?>
        </div><!-- /bv-feedback -->

        <!-- ══════════════════════════════════════════════════════════
             PERFORMANCE TAB  —  Match History des Boosters
             ══════════════════════════════════════════════════════════ -->

        <style>
        /* ── Performance / Match-History ─────────────────────────── */
        .bv2 .bv-perf-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 1.2vw;
          flex-wrap: wrap;
          gap: .6vw;
        }
        .bv2 .bv-perf-filters {
          display: flex;
          gap: .4vw;
          flex-wrap: wrap;
        }
        .bv2 .bv-perf-filter {
          font-size: .7vw;
          font-weight: 800;
          text-transform: uppercase;
          letter-spacing: .06em;
          padding: .3vw .8vw;
          border-radius: 999px;
          border: .052vw solid rgba(99,102,241,.25);
          background: rgba(99,102,241,.07);
          color: rgba(255,255,255,.55);
          cursor: pointer;
          transition: all .14s;
          font-family: inherit;
        }
        .bv2 .bv-perf-filter.active,
        .bv2 .bv-perf-filter:hover {
          background: rgba(99,102,241,.22);
          border-color: rgba(99,102,241,.5);
          color: #fff;
        }
        /* Match row */
        .bv2 .bv-match-list { display: flex; flex-direction: column; gap: .5vw; }
        .bv2 .bv-match-row {
          display: grid;
          grid-template-columns: 5px 46px minmax(0,1fr) 110px 80px 90px 80px;
          align-items: center;
          gap: .8vw;
          padding: .7vw 1vw;
          border-radius: .8vw;
          border: .052vw solid rgba(255,255,255,.06);
          background: rgba(255,255,255,.025);
          transition: background .12s;
        }
        .bv2 .bv-match-row:hover { background: rgba(99,102,241,.07); }
        /* Result stripe */
        .bv2 .bv-match-stripe {
          width: 5px; height: 44px; border-radius: 3px; flex-shrink: 0;
        }
        .bv2 .bv-match-stripe.win  { background: #22c55e; }
        .bv2 .bv-match-stripe.loss { background: #ef4444; }
        .bv2 .bv-match-stripe.remake { background: #a3a3a3; }
        /* Champion icon */
        .bv2 .bv-match-champ {
          width: 46px; height: 46px; border-radius: .5vw; overflow: hidden; flex-shrink: 0;
          background: rgba(0,0,0,.25);
          border: .052vw solid rgba(255,255,255,.08);
          position: relative;
        }
        .bv2 .bv-match-champ img { width: 100%; height: 100%; object-fit: cover; }
        .bv2 .bv-match-champ .bv-match-pos {
          position: absolute; bottom: 0; right: 0;
          width: 16px; height: 16px; border-radius: 3px 0 4px 0;
          background: rgba(0,0,0,.7);
          display: flex; align-items: center; justify-content: center;
        }
        .bv2 .bv-match-champ .bv-match-pos img { width: 11px; height: 11px; border-radius: 1px; }
        /* Info column */
        .bv2 .bv-match-info { min-width: 0; }
        .bv2 .bv-match-name {
          font-size: .9vw; font-weight: 800; color: #fff;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .bv2 .bv-match-queue {
          font-size: .72vw; color: rgba(255,255,255,.42); margin-top: .15vw;
        }
        .bv2 .bv-match-result-badge {
          display: inline-block;
          font-size: .64vw; font-weight: 900; text-transform: uppercase; letter-spacing: .05em;
          padding: .18vw .5vw; border-radius: 999px; margin-top: .25vw;
        }
        .bv2 .bv-match-result-badge.win  { background: rgba(34,197,94,.15); color: #4ade80; border: 1px solid rgba(34,197,94,.25); }
        .bv2 .bv-match-result-badge.loss { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.22); }
        .bv2 .bv-match-result-badge.remake { background: rgba(163,163,163,.12); color: #d4d4d4; border: 1px solid rgba(163,163,163,.2); }
        /* KDA */
        .bv2 .bv-match-kda { text-align: center; }
        .bv2 .bv-match-kda-val {
          font-size: 1vw; font-weight: 900; color: #fff; letter-spacing: .02em;
        }
        .bv2 .bv-match-kda-val .bv-k { color: #4ade80; }
        .bv2 .bv-match-kda-val .bv-d { color: #f87171; }
        .bv2 .bv-match-kda-val .bv-sep { color: rgba(255,255,255,.3); font-weight: 400; }
        .bv2 .bv-match-kda-ratio {
          font-size: .68vw; color: rgba(255,255,255,.42); margin-top: .1vw;
        }
        .bv2 .bv-match-kda-ratio span { color: #c7d2fe; font-weight: 700; }
        /* Duration */
        .bv2 .bv-match-dur { text-align: center; font-size: .82vw; color: rgba(255,255,255,.55); }
        .bv2 .bv-match-dur strong { display: block; font-size: .9vw; font-weight: 800; color: rgba(255,255,255,.85); }
        /* Date */
        .bv2 .bv-match-date { text-align: right; font-size: .75vw; color: rgba(255,255,255,.38); }
        /* Empty / loading states */
        .bv2 .bv-perf-empty {
          text-align: center; padding: 3vw 0; color: rgba(255,255,255,.3);
        }
        .bv2 .bv-perf-empty i { font-size: 3vw; display: block; margin-bottom: .8vw; }
        .bv2 .bv-perf-spinner {
          display: flex; align-items: center; justify-content: center;
          padding: 2.5vw 0; color: rgba(99,102,241,.7);
        }
        .bv2 .bv-perf-spinner i { font-size: 1.8vw; animation: bvSpin .8s linear infinite; }
        /* Pagination */
        .bv2 .bv-perf-pagination {
          display: flex; align-items: center; justify-content: center; gap: .5vw;
          margin-top: 1.2vw;
        }
        .bv2 .bv-perf-page-btn {
          min-width: 2.2vw; height: 2.2vw; border-radius: .5vw;
          font-size: .78vw; font-weight: 800;
          background: rgba(99,102,241,.08); border: .052vw solid rgba(99,102,241,.2);
          color: rgba(255,255,255,.65); cursor: pointer; transition: all .13s;
          display: flex; align-items: center; justify-content: center;
          font-family: inherit;
        }
        .bv2 .bv-perf-page-btn.active,
        .bv2 .bv-perf-page-btn:hover:not(:disabled) {
          background: rgba(99,102,241,.25); border-color: rgba(99,102,241,.5); color: #fff;
        }
        .bv2 .bv-perf-page-btn:disabled { opacity: .3; cursor: not-allowed; }
        /* Summary bar */
        .bv2 .bv-perf-summary {
          display: flex; gap: 1.5vw; flex-wrap: wrap;
          margin-bottom: 1vw; padding: .8vw 1.2vw;
          border-radius: .8vw; background: rgba(99,102,241,.05);
          border: .052vw solid rgba(99,102,241,.12);
        }
        .bv2 .bv-perf-sum-item { display: flex; flex-direction: column; align-items: center; gap: .15vw; }
        .bv2 .bv-perf-sum-val { font-size: 1.1vw; font-weight: 900; color: #fff; }
        .bv2 .bv-perf-sum-lbl { font-size: .65vw; text-transform: uppercase; letter-spacing: .07em; color: rgba(255,255,255,.38); font-weight: 700; }
        .bv2 .bv-perf-sum-wr  { color: #4ade80; }
        @keyframes bvSpin { to { transform: rotate(360deg); } }

        /* ── Top filter bar ─────────────────────────────── */
        .bv2 .bv-perf-top-bar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          flex-wrap: wrap;
          gap: .6vw;
          margin-bottom: 1.4vw;
          padding: .7vw 1vw;
          border-radius: .8vw;
          background: rgba(255,255,255,.025);
          border: .052vw solid rgba(255,255,255,.07);
        }
        .bv2 .bv-perf-data-note {
          display: flex; align-items: center; gap: .5vw;
          margin-bottom: 1vw; padding: .65vw .9vw;
          border-radius: .75vw;
          border: .052vw solid rgba(249,115,22,.22);
          background: rgba(249,115,22,.08);
          color: rgba(255,255,255,.72);
          font-size: .76vw; font-weight: 700; line-height: 1.45;
        }
        .bv2 .bv-perf-data-note i { color: #f97316; font-size: .85vw; flex-shrink: 0; }
        .bv2 .bv-perf-year-filters { display: flex; gap: .4vw; flex-wrap: wrap; }
        .bv2 .bv-perf-mode-filters { display: flex; gap: .3vw; }
        .bv2 .bv-perf-mode-btn {
          font-size: .7vw; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
          padding: .28vw .75vw; border-radius: 999px;
          border: .052vw solid rgba(255,255,255,.12);
          background: transparent; color: rgba(255,255,255,.4);
          cursor: pointer; transition: all .14s; font-family: inherit;
        }
        .bv2 .bv-perf-mode-btn.active { background: rgba(99,102,241,.22); border-color: rgba(99,102,241,.5); color: #fff; }
        .bv2 .bv-perf-mode-btn:hover:not(.active) { background: rgba(255,255,255,.05); color: rgba(255,255,255,.7); }

        /* ── Recent games badge ─────────────────────────── */
        .bv2 .bv-recent-mode-badge {
          font-size: .62vw; font-weight: 900; text-transform: uppercase; letter-spacing: .07em;
          padding: .18vw .55vw; border-radius: 999px;
          background: rgba(99,102,241,.15); border: .052vw solid rgba(99,102,241,.3);
          color: #a5b4fc;
        }

        @media(max-width:900px) {
          .bv2 .bv-perf-top-bar { gap: 10px; padding: 10px 12px; }
          .bv2 .bv-perf-mode-btn { font-size: .78rem; padding: 5px 12px; }
          .bv2 .bv-perf-data-note { font-size: .82rem; padding: 9px 12px; gap: 8px; border-radius: 10px; }
          .bv2 .bv-perf-data-note i { font-size: .9rem; }
          .bv2 .bv-recent-mode-badge { font-size: .72rem; padding: 3px 9px; }
        }
        .bv2 .bv-perf-year-btn {
          font-size: .7vw; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
          padding: .3vw .9vw; border-radius: 999px;
          border: .052vw solid rgba(255,255,255,.18);
          background: transparent; color: rgba(255,255,255,.45);
          cursor: pointer; transition: all .14s; font-family: inherit;
        }
        .bv2 .bv-perf-year-btn.active,
        .bv2 .bv-perf-year-btn:hover {
          border-color: #f97316; color: #fff; background: rgba(249,115,22,.12);
        }

        /* ── Rank breakdown cards ───────────────────────────── */
        .bv2 .bv-rank-cards-wrap {
          position: relative;
          margin-bottom: .5vw;
        }
        .bv2 .bv-rank-cards {
          display: flex;
          gap: .8vw;
          overflow-x: auto;
          scroll-snap-type: x mandatory;
          scrollbar-width: none;
          padding: .2vw .3vw .5vw;
          cursor: grab;
          user-select: none;
        }
        .bv2 .bv-rank-cards.is-dragging { cursor: grabbing; }
        .bv2 .bv-rank-cards::-webkit-scrollbar { display: none; }
        .bv2 .bv-rank-card {
          flex: 0 0 calc(20% - .64vw);
          min-width: 9vw;
          scroll-snap-align: start;
          background: rgba(255,255,255,.03);
          border: .052vw solid rgba(255,255,255,.08);
          border-radius: .9vw;
          padding: 1.2vw 1vw;
          display: flex; flex-direction: column; align-items: center; gap: .6vw;
          transition: border-color .15s, background .15s;
        }
        .bv2 .bv-rank-card:hover {
          border-color: rgba(99,102,241,.3); background: rgba(99,102,241,.05);
        }
        .bv2 .bv-rank-card img {
          width: 3.5vw; height: 3.5vw; object-fit: contain;
        }
        .bv2 .bv-rank-card-name {
          font-size: .95vw; font-weight: 900; color: #fff;
        }
        .bv2 .bv-rank-card-rows { width: 100%; display: flex; flex-direction: column; gap: .3vw; }
        .bv2 .bv-rank-card-row {
          display: flex; justify-content: space-between; align-items: center;
          font-size: .75vw;
        }
        .bv2 .bv-rank-card-row span { color: rgba(255,255,255,.5); }
        .bv2 .bv-rank-card-row strong { font-weight: 800; color: #fff; }
        .bv2 .bv-rank-card-row strong.win  { color: #4ade80; }
        .bv2 .bv-rank-card-row strong.wr   { color: #fff; }

        /* ── Champion stats table ───────────────────────────── */
        .bv2 .bv-champ-table-wrap {
          overflow-x: auto; border-radius: .8vw;
          border: .052vw solid rgba(255,255,255,.07);
        }
        .bv2 .bv-champ-table {
          width: 100%; border-collapse: collapse; font-size: .82vw;
        }
        .bv2 .bv-champ-table thead tr {
          background: rgba(255,255,255,.04);
          border-bottom: .052vw solid rgba(255,255,255,.08);
        }
        .bv2 .bv-champ-table th {
          padding: .7vw 1vw; text-align: left;
          font-size: .68vw; font-weight: 800; text-transform: uppercase;
          letter-spacing: .07em; color: rgba(255,255,255,.4);
          white-space: nowrap;
        }
        .bv2 .bv-champ-table th:not(:first-child) { text-align: center; }
        .bv2 .bv-champ-table tbody tr {
          border-bottom: .052vw solid rgba(255,255,255,.04);
          transition: background .1s;
        }
        .bv2 .bv-champ-table tbody tr:last-child { border-bottom: none; }
        .bv2 .bv-champ-table tbody tr:hover { background: rgba(99,102,241,.06); }
        .bv2 .bv-champ-table td {
          padding: .65vw 1vw; color: rgba(255,255,255,.85);
          white-space: nowrap;
        }
        .bv2 .bv-champ-table td:not(:first-child) { text-align: center; }
        .bv2 .bv-champ-table .bv-ct-champ {
          display: flex; align-items: center; gap: .6vw;
        }
        .bv2 .bv-champ-table .bv-ct-champ img {
          width: 2vw; height: 2vw; border-radius: .35vw; object-fit: cover;
          border: .052vw solid rgba(255,255,255,.1);
        }
        .bv2 .bv-champ-table .bv-ct-champ span { font-weight: 800; color: #fff; }
        .bv2 .bv-ct-kda { font-weight: 900; }
        .bv2 .bv-ct-kda.good  { color: #f97316; }
        .bv2 .bv-ct-kda.great { color: #f97316; }
        .bv2 .bv-ct-wr.green  { color: #4ade80; font-weight: 800; }
        .bv2 .bv-ct-wr.yellow { color: #fbbf24; font-weight: 800; }
        .bv2 .bv-ct-wr.red    { color: #f87171; font-weight: 800; }

        .bv2 .bv-rank-card-empty {
          opacity: .45;
        }
        .bv2 .bv-rank-card-empty img { filter: grayscale(1); }

        .bv2 .bv-rank-arrow {
          position: absolute; top: 50%; transform: translateY(-50%);
          z-index: 2;
          width: 2vw; height: 2vw; border-radius: 50%;
          background: rgba(20,14,45,.92);
          border: .052vw solid rgba(99,102,241,.35);
          color: #a5b4fc; font-size: .75vw;
          display: flex; align-items: center; justify-content: center;
          cursor: pointer; transition: all .15s;
          box-shadow: 0 .3vw 1vw rgba(0,0,0,.4);
        }
        .bv2 .bv-rank-arrow:hover { background: rgba(99,102,241,.3); border-color: rgba(99,102,241,.6); color: #fff; }
        .bv2 .bv-rank-arrow:disabled { opacity: .25; cursor: default; pointer-events: none; }
        .bv2 .bv-rank-arrow-l { left: -.8vw; }
        .bv2 .bv-rank-arrow-r { right: -.8vw; }

        @media(max-width:900px) {
          .bv2 .bv-rank-arrow { width: 28px; height: 28px; font-size: .8rem; }
          .bv2 .bv-rank-arrow-l { left: -6px; }
          .bv2 .bv-rank-arrow-r { right: -6px; }
        }

        @media(max-width:900px) {
          .bv2 .bv-rank-card { flex: 0 0 140px; min-width: 140px; border-radius: 10px; padding: 14px 12px; gap: 8px; }
          .bv2 .bv-rank-card img { width: 44px; height: 44px; }
          .bv2 .bv-rank-card-name { font-size: 1rem; }
          .bv2 .bv-rank-card-row { font-size: .82rem; }
          .bv2 .bv-champ-table { font-size: .88rem; }
          .bv2 .bv-champ-table th { font-size: .72rem; padding: 9px 10px; }
          .bv2 .bv-champ-table td { padding: 8px 10px; }
          .bv2 .bv-champ-table .bv-ct-champ img { width: 26px; height: 26px; }
          .bv2 .bv-perf-year-btn { font-size: .78rem; padding: 5px 12px; }
        }
        @media(max-width:480px) {
          .bv2 .bv-rank-cards { gap: 8px; }
          .bv2 .bv-rank-card img { width: 36px; height: 36px; }
        }
        @media(max-width:900px) {
          .bv2 .bv-match-row { grid-template-columns: 4px 40px minmax(0,1fr) 80px 64px; }
          .bv2 .bv-match-dur,.bv2 .bv-match-date { display: none; }
          .bv2 .bv-match-name { font-size: .95rem; }
          .bv2 .bv-match-queue { font-size: .78rem; }
          .bv2 .bv-match-kda-val { font-size: 1rem; }
          .bv2 .bv-match-kda-ratio { font-size: .72rem; }
          .bv2 .bv-match-result-badge { font-size: .68rem; }
          .bv2 .bv-perf-filter { font-size: .78rem; padding: 5px 12px; }
          .bv2 .bv-perf-sum-val { font-size: 1.1rem; }
          .bv2 .bv-perf-sum-lbl { font-size: .68rem; }
          .bv2 .bv-perf-page-btn { min-width: 36px; height: 36px; font-size: .82rem; }
          .bv2 .bv-perf-spinner i { font-size: 1.8rem; }
          .bv2 .bv-perf-empty i { font-size: 2.5rem; }
          .bv2 .bv-match-champ { width: 40px; height: 40px; }
        }
        @media(max-width:480px) {
          .bv2 .bv-match-row { grid-template-columns: 4px 36px minmax(0,1fr) 72px; gap: 8px; padding: 10px; }
          .bv2 .bv-match-kda-val { font-size: .92rem; }
          .bv2 .bv-match-champ { width: 36px; height: 36px; }
        }
        </style>

        <div class="bv-tab-pane" id="bv-performance">

          <div class="bv-perf-data-note">
            <i class="fa-solid fa-circle-info"></i>
            <span>Only games played from 20.04.2026 onward are counted here. Earlier games are not displayed.</span>
          </div>

          <!-- ── TOP FILTER BAR (Year + Solo/Duo) ── -->
          <div class="bv-perf-top-bar" id="bvStatsHeader" style="display:none;">
            <div class="bv-perf-year-filters" id="bvYearFilters"></div>
            <div class="bv-perf-mode-filters" id="bvModeFilters">
              <button class="bv-perf-mode-btn" data-mode="solo">Solo</button>
              <button class="bv-perf-mode-btn" data-mode="duo">Duo</button>
              <button class="bv-perf-mode-btn active" data-mode="all">All</button>
            </div>
          </div>

          <!-- ── RANK BREAKDOWN ── -->
          <div id="bvRankSection" style="display:none;">
            <div class="bv-section-label" style="margin-bottom:.8vw;">
              <span style="font-size:.6vw;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#f97316;display:block;margin-bottom:.2vw;">Rank History</span>
              Competitive Progression
            </div>
            <div class="bv-rank-cards-wrap">
              <button class="bv-rank-arrow bv-rank-arrow-l" id="bvRankPrev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
              <div class="bv-rank-cards" id="bvRankCards"></div>
              <button class="bv-rank-arrow bv-rank-arrow-r" id="bvRankNext" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
          </div>

          <!-- ── CHAMPION STATS ── -->
          <div id="bvChampSection" style="display:none; margin-top:2vw;">
            <div class="bv-section-label" style="margin-bottom:.8vw;">
              <span style="font-size:.6vw;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#f97316;display:block;margin-bottom:.2vw;">Performance</span>
              Champion Statistics
            </div>
            <div class="bv-champ-table-wrap">
              <table class="bv-champ-table">
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
                <tbody id="bvChampTbody"></tbody>
              </table>
            </div>
          </div>

          <div style="height:2vw;"></div>

          <!-- Recent Games -->
          <div id="bvRecentSection">
            <div style="display:flex;align-items:center;gap:.6vw;margin-bottom:.8vw;">
              <div class="bv-section-label" style="margin-bottom:0;">Recent Games</div>
              <span class="bv-recent-mode-badge" id="bvRecentBadge">All</span>
            </div>
            <div class="bv-match-list" id="bvMatchList">
              <div class="bv-perf-spinner"><i class="fa-solid fa-spinner-third"></i></div>
            </div>
          </div>

        </div><!-- /bv-performance -->

        <script>
        (function () {
          'use strict';
          var BOOSTER_ID  = <?= (int)$__boosterId ?>;
          var AJAX_URL    = '<?= defined('BASE_URL') ? rtrim(BASE_URL,'/') : '' ?>/ajax';
          var ASSET_BASE  = '<?= ASSET_URL ?>';
          var LOL_CHAMP   = '<?= defined('LOL_CHAMP_URL') ? LOL_CHAMP_URL : (ASSET_URL.'/core/main/img/lol/champs') ?>';

          var currentYear = 0;
          var currentMode = 'all'; // 'solo' | 'duo' | 'all'
          var loaded      = false;

          var $list          = document.getElementById('bvMatchList');
          var $recentSection = document.getElementById('bvRecentSection');
          var $yearFilters   = document.getElementById('bvYearFilters');
          var $statsHeader   = document.getElementById('bvStatsHeader');
          var $rankSection   = document.getElementById('bvRankSection');
          var $rankCards     = document.getElementById('bvRankCards');
          var $champSection  = document.getElementById('bvChampSection');
          var $champTbody    = document.getElementById('bvChampTbody');
          var $modeFilters   = document.getElementById('bvModeFilters');
          var $recentBadge   = document.getElementById('bvRecentBadge');

          var ALL_RANKS = [
            {tier:0,name:'Unranked'},{tier:1,name:'Iron'},{tier:2,name:'Bronze'},
            {tier:3,name:'Silver'},{tier:4,name:'Gold'},{tier:5,name:'Platinum'},
            {tier:6,name:'Emerald'},{tier:7,name:'Diamond'},{tier:8,name:'Master'},
            {tier:9,name:'Grandmaster'},{tier:10,name:'Challenger'}
          ];

          // ── Drag-to-scroll for rank cards ──
          (function(){
            var slider, isDown=false, startX, scrollLeft;
            function initDrag(){
              slider=document.getElementById('bvRankCards');
              if(!slider) return;
              slider.addEventListener('mousedown',function(e){
                isDown=true; slider.classList.add('is-dragging');
                startX=e.pageX-slider.offsetLeft; scrollLeft=slider.scrollLeft;
              });
              document.addEventListener('mouseup',function(){ isDown=false; if(slider) slider.classList.remove('is-dragging'); });
              slider.addEventListener('mouseleave',function(){ isDown=false; slider.classList.remove('is-dragging'); });
              slider.addEventListener('mousemove',function(e){
                if(!isDown) return; e.preventDefault();
                var x=e.pageX-slider.offsetLeft;
                slider.scrollLeft=scrollLeft-(x-startX)*1.2;
              });
              // Arrow buttons
              var prev=document.getElementById('bvRankPrev');
              var next=document.getElementById('bvRankNext');
              if(prev) prev.addEventListener('click',function(){ slider.scrollBy({left:-slider.offsetWidth*0.6,behavior:'smooth'}); });
              if(next) next.addEventListener('click',function(){ slider.scrollBy({left:slider.offsetWidth*0.6,behavior:'smooth'}); });
              // Update arrow states on scroll
              function updateArrows(){
                if(!prev||!next) return;
                prev.disabled = slider.scrollLeft < 5;
                next.disabled = slider.scrollLeft >= slider.scrollWidth - slider.clientWidth - 5;
              }
              slider.addEventListener('scroll', updateArrows);
              setTimeout(updateArrows, 100);
            }
            // Init after first render
            window._bvInitRankDrag = initDrag;
          })();

          var QUEUES = {
            420:'Ranked Solo/Duo',440:'Ranked Flex',430:'Blind Pick',
            400:'Draft Pick',450:'ARAM',480:'Swiftplay',490:'Quickplay',900:'ARURF',1900:'URF',
            700:'Clash',720:'ARAM Clash',1700:'Arena',1710:'Arena',0:'Custom',
          };

          function fmtDuration(sec) {
            sec=parseInt(sec)||0;
            return Math.floor(sec/60)+':'+String(sec%60).padStart(2,'0');
          }
          function fmtDate(iso) {
            if(!iso) return '';
            try {
              var d=new Date(iso.replace(' ','T'));
              return String(d.getDate()).padStart(2,'0')+'.'+String(d.getMonth()+1).padStart(2,'0')+'.'+d.getFullYear();
            } catch(e){ return ''; }
          }
          function kda(k,d,a) {
            k=parseInt(k)||0;d=parseInt(d)||0;a=parseInt(a)||0;
            if(d===0) return (k+a)===0?'0.00':'Perfect';
            return ((k+a)/d).toFixed(2);
          }
          function champImgUrl(name) {
            if(!name) return '';
            var fix={'Wukong':'MonkeyKing'};
            return LOL_CHAMP+'/'+(fix[name]||name.replace(/[^a-zA-Z0-9]/g,''))+'.png';
          }
          function roleImgUrl(pos) {
            if(!pos) return '';
            return ASSET_BASE+'/core/main/img/lol/roles/'+pos.toLowerCase()+'.png';
          }
          function modeBadge(row) {
            var isDuo = parseInt(row.is_duo || 0) === 1;
            return '<span class="bv-sb-mode-badge '+(isDuo?'duo':'solo')+'">'+(isDuo?'Duo':'Solo')+'</span>';
          }
          function recentLabel(mode) {
            return mode === 'duo' ? 'Duo' : (mode === 'solo' ? 'Solo' : 'All');
          }

          /* ── Render 5 recent match rows ── */
          function renderMatches(rows) {
            if(!rows||rows.length===0){
              $list.innerHTML='<div class="bv-perf-empty"><i class="fa-solid fa-chart-line"></i><span>No recent games found.</span></div>';
              return;
            }
            $list.innerHTML=rows.slice(0,5).map(function(r){
              var result=r.result||(parseInt(r.won)?'win':'loss');
              var k=parseInt(r.kills)||0,d=parseInt(r.deaths)||0,a=parseInt(r.assists)||0;
              var kdaStr=kda(k,d,a);
              var queue=QUEUES[parseInt(r.queue_id)]||'Ranked Solo';
              var dur=fmtDuration(r.duration);
              var date=fmtDate(r.played_at);
              var champUrl=champImgUrl(r.champion);
              var roleUrl=roleImgUrl(r.position);
              var lbl=result==='remake'?'Remake':(result==='win'?'WIN':'LOSS');
              return '<div class="bv-match-row">'
                +'<div class="bv-match-stripe '+result+'"></div>'
                +'<div class="bv-match-champ">'
                +(champUrl?'<img src="'+champUrl+'" alt="'+(r.champion||'')+'" onerror="this.style.opacity=\'.3\'">':'')
                +(roleUrl?'<div class="bv-match-pos"><img src="'+roleUrl+'" alt="'+(r.position||'')+'" onerror="this.parentElement.style.display=\'none\'"></div>':'')
                +'</div>'
                +'<div class="bv-match-info">'
                +'<div class="bv-match-name">'+(r.champion||'Unknown')+'</div>'
                +'<div class="bv-match-queue">'+queue+' '+modeBadge(r)+'</div>'
                +'<span class="bv-match-result-badge '+result+'">'+lbl+'</span>'
                +'</div>'
                +'<div class="bv-match-kda">'
                +'<div class="bv-match-kda-val"><span class="bv-k">'+k+'</span><span class="bv-sep"> / </span><span class="bv-d">'+d+'</span><span class="bv-sep"> / </span>'+a+'</div>'
                +'<div class="bv-match-kda-ratio"><span>'+kdaStr+'</span> KDA</div>'
                +'</div>'
                +'<div class="bv-match-dur"><strong>'+dur+'</strong>DURATION</div>'
                +'<div class="bv-match-date">'+date+'</div>'
                +'</div>';
            }).join('');
          }

          /* ── Render rank cards — always show all tiers ── */
          function renderRanks(ranks) {
            $rankSection.style.display='block';
            var dataMap={};
            (ranks||[]).forEach(function(r){ dataMap[r.tier]=r; });
            $rankCards.innerHTML=ALL_RANKS.map(function(rank){
              var r=dataMap[rank.tier];
              var wins  = r ? r.wins   : 0;
              var losses= r ? r.losses : 0;
              var wr    = r ? r.winrate: 0;
              var wrCls = wr>=60 ? 'wr green' : (wr>=50 ? 'wr yellow' : 'wr red');
              var isEmpty = (wins===0 && losses===0);
              return '<div class="bv-rank-card'+(isEmpty?' bv-rank-card-empty':'')+'">'
                +'<img src="'+ASSET_BASE+'/core/main/img/lol/ranks/max/'+rank.tier+'.png" alt="'+rank.name+'" onerror="this.style.display=\'none\'">'
                +'<div class="bv-rank-card-name">'+rank.name+'</div>'
                +'<div class="bv-rank-card-rows">'
                +'<div class="bv-rank-card-row"><span>Win</span><strong class="win">'+wins+'</strong></div>'
                +'<div class="bv-rank-card-row"><span>Lose</span><strong>'+losses+'</strong></div>'
                +'<div class="bv-rank-card-row"><span>Winrate</span><strong class="'+(isEmpty?'wr':wrCls)+'">'+wr.toFixed(isEmpty?0:2)+'%</strong></div>'
                +'</div></div>';
            }).join('');
            // Scroll to first card with data, init drag
            var firstWithData = ALL_RANKS.findIndex(function(rank){ return dataMap[rank.tier]; });
            if(firstWithData > 0) {
              setTimeout(function(){
                var cards=$rankCards.querySelectorAll('.bv-rank-card');
                if(cards[firstWithData]) cards[firstWithData].scrollIntoView({block:'nearest',inline:'start',behavior:'smooth'});
              }, 80);
            }
            if(window._bvInitRankDrag) { window._bvInitRankDrag(); window._bvInitRankDrag=null; }
          }

          /* ── Render champion table ── */
          function renderChamps(champs) {
            $champSection.style.display='block';
            if(!champs||champs.length===0){
              $champTbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:2vw;color:rgba(255,255,255,.35);font-size:.85vw;">'
                +'<i class="fa-solid fa-chart-bar" style="display:block;font-size:2vw;margin-bottom:.5vw;opacity:.3;"></i>'
                +'No champion statistics yet.</td></tr>';
              return;
            }
            $champTbody.innerHTML=champs.map(function(c){
              var kdaVal=parseFloat(c.kda)||0;
              var kdaCls=kdaVal>=5?'bv-ct-kda great':(kdaVal>=3?'bv-ct-kda good':'bv-ct-kda');
              var wrCls=c.winrate>=60?'bv-ct-wr green':(c.winrate>=50?'bv-ct-wr yellow':'bv-ct-wr red');
              var img=champImgUrl(c.champion);
              return '<tr>'
                +'<td><div class="bv-ct-champ">'
                +(img?'<img src="'+img+'" alt="'+c.champion+'" onerror="this.style.display=\'none\'">':'')
                +'<span>'+c.champion+'</span></div></td>'
                +'<td>'+c.games+'</td>'
                +'<td>'+c.avg_kills.toFixed(2)+'</td>'
                +'<td>'+c.avg_deaths.toFixed(2)+'</td>'
                +'<td>'+c.avg_assists.toFixed(2)+'</td>'
                +'<td class="'+kdaCls+'">'+kdaVal.toFixed(2)+'</td>'
                +'<td class="'+wrCls+'">'+c.winrate.toFixed(2)+'%</td>'
                +'</tr>';
            }).join('');
          }

          /* ── Render year buttons + wire mode buttons ── */
          function renderYears(years) {
            if(!years||years.length===0) return;
            $statsHeader.style.display='flex';
            if(currentYear===0&&years.length>0) currentYear=years[0];
            var allYears=[{val:0,lbl:'All'}].concat(years.map(function(y){return {val:y,lbl:y};}));
            $yearFilters.innerHTML=allYears.map(function(item){
              return '<button class="bv-perf-year-btn'+(currentYear===item.val?' active':'')+'" data-year="'+item.val+'">'+item.lbl+'</button>';
            }).join('');
            $yearFilters.querySelectorAll('[data-year]').forEach(function(btn){
              btn.addEventListener('click',function(){
                var y=parseInt(btn.dataset.year);
                if(y===currentYear) return;
                currentYear=y;
                $yearFilters.querySelectorAll('[data-year]').forEach(function(b){b.classList.remove('active');});
                btn.classList.add('active');
                loadStats(currentYear, currentMode);
              });
            });
            // Wire mode buttons once
            $modeFilters.querySelectorAll('[data-mode]').forEach(function(btn){
              btn.addEventListener('click',function(){
                var m=btn.dataset.mode;
                if(m===currentMode) return;
                currentMode=m;
                $modeFilters.querySelectorAll('[data-mode]').forEach(function(b){b.classList.remove('active');});
                btn.classList.add('active');
                loadStats(currentYear, m);
                $recentSection.style.display='block';
                loadRecentGames(m);
              });
            });
          }

          /* ── Fetch stats ── */
          function loadStats(year, mode) {
            // Champion stats use booster-owned matches only:
            // solo = solo orders, duo = duo-account matches from the booster, all = both.
            var champMode = (mode === 'duo') ? 'duo' : (mode === 'all' ? 'all' : 'solo');
            var body=new URLSearchParams({action:'get_booster_stats',booster_id:BOOSTER_ID,year:year||0,mode:mode||'all',champ_mode:champMode});
            fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
              .then(function(r){return r.json();})
              .then(function(data){
                if(!data.ok){console.warn('[bv-stats]',data.message);return;}
                if(!$yearFilters.children.length) renderYears(data.years||[]);
                renderRanks(data.ranks||[]);
                renderChamps(data.champions||[]);
              })
              .catch(function(err){console.error('[bv-stats]',err);});
          }

          /* ── Fetch last 5 matches for current mode. Duo uses booster-owned rows only. ── */
          function loadRecentGames(mode) {
            mode = mode || 'all';
            $recentSection.style.display='block';
            if($recentBadge) $recentBadge.textContent=recentLabel(mode);
            $list.innerHTML='<div class="bv-perf-spinner"><i class="fa-solid fa-spinner-third"></i></div>';
            var body=new URLSearchParams({action:'get_booster_performance',booster_id:BOOSTER_ID,page:1,per_page:5,mode:mode,booster_owned_only:1});
            fetch(AJAX_URL,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}})
              .then(function(r){return r.json();})
              .then(function(data){
                if(!data.ok) throw new Error(data.message||'Error');
                var rows=data.matches.rows||[];
                if(rows.length===0){
                  $list.innerHTML='<div class="bv-perf-empty"><i class="fa-solid fa-gamepad"></i><span>No recent games found.</span></div>';
                  return;
                }
                renderMatches(rows);
              })
              .catch(function(err){
                $list.innerHTML='<div class="bv-perf-empty"><i class="fa-solid fa-triangle-exclamation"></i><span>Could not load games.</span></div>';
                console.error('[bv-performance]',err);
              });
          }

          /* ── Tab activation ── */
          function init() {
            if(loaded) return;
            loaded=true;
            loadStats(currentYear, currentMode);
            loadRecentGames(currentMode);
          }

          document.addEventListener('click',function(e){
            var tab=e.target.closest('[data-bvtab="bv-performance"]');
            if(tab) init();
          });
          if(window.location.hash==='#bv-performance') init();

        })();
        </script>

      </div>

      <!-- ── Sidebar ─────────────────────────────── -->
      <div class="bv-sidebar-wrap">
        <div class="bv-sidebar">
          <div class="bv-sidebar-bar"></div>
          <div class="bv-sidebar-body">

            <div class="bv-sidebar-prev">
              <?php if ($bIcon): ?>
                <img src="<?= htmlspecialchars($bIcon,ENT_QUOTES,'UTF-8') ?>" alt="<?= $bUsername ?>">
              <?php else: ?>
                <div class="bv-sb-ph"><?= $bInitial ?></div>
              <?php endif; ?>
              <div>
                <div class="bv-sb-name"><?= $bUsername ?></div>
                <div class="bv-sb-meta">
                  <span class="bv-sb-chip rank"><i class="fa-solid fa-trophy"></i> <?= $bRankName ?: 'Booster' ?></span>
                  <span class="bv-sb-chip <?= $__isOnline ? 'online' : '' ?>">
                    <i class="fa-solid fa-circle" style="font-size:.5em;"></i> <?= $__statusText ?>
                  </span>
                </div>
              </div>
            </div>
            <div class="bv-sb-title">Overview</div>
            <div class="bv-sb-sub">Latest competitive activity from <?= htmlspecialchars($bUsername, ENT_QUOTES, 'UTF-8') ?></div>

            <div class="bv-sb-div"></div>

            <div class="bv-sb-progression" id="bvOverviewProgression">
              <div class="bv-sb-prog-head">
                <div class="bv-sb-prog-title"><i class="fa-solid fa-trophy"></i> Competitive Progression</div>
                <span class="bv-sb-prog-badge">All</span>
              </div>
              <div class="bv-sb-prog-list" id="bvOverviewProgressionList">
                <div class="bv-sb-prog-loading"><i class="fa-solid fa-spinner-third"></i> Loading progression…</div>
              </div>
            </div>

            <div class="bv-sb-div"></div>

            <div class="bv-sb-recent" id="bvOverviewRecentGames">
              <div class="bv-sb-recent-head">
                <div class="bv-sb-recent-title"><i class="fa-solid fa-gamepad"></i> Recent Games</div>
                <span class="bv-sb-recent-badge" id="bvOverviewRecentBadge">All</span>
              </div>
              <div class="bv-sb-recent-note">
                <i class="fa-solid fa-circle-info"></i>
                <span>Only games played from 20.04.2026 onward are shown.</span>
              </div>
              <div class="bv-sb-recent-list" id="bvOverviewRecentGamesList">
                <div class="bv-sb-recent-loading"><i class="fa-solid fa-spinner-third"></i> Loading recent games…</div>
              </div>
            </div>

            <div class="bv-sb-div"></div>
<?php if ($__boostDisabled): ?>
              <button class="bv-sb-cta bv-sb-cta-disabled" disabled>
                <i class="fa-solid fa-ban"></i> Not accepting orders
              </button>
              <div class="bv-sb-notice">
                <i class="fa-solid fa-circle-info"></i> <?= t('This booster is currently not accepting new assignments.') ?>
              </div>
            <?php else: ?>
              <button class="bv-sb-cta js-request-boost" data-booster="<?= $__boosterId ?>" data-games="<?= htmlspecialchars(implode('|', $__gameList), ENT_QUOTES, 'UTF-8') ?>" data-username="<?= $bUsername ?>">
                <i class="fa-solid fa-bolt"></i> Play with <?= $bUsername ?>
              </button>
            <?php endif; ?>

            <div class="bv-sb-trust">
              <i class="fa-solid fa-shield-halved"></i> Verified booster profile
            </div>

          </div>
        </div>
      </div><!-- /sidebar -->

    </div><!-- /bv-tab-layout -->
  </div><!-- /bv-details -->

</div><!-- /bv-card -->

<!-- Booster game chooser -->
<div class="bv-game-modal" id="bvGameChooser" aria-hidden="true">
  <div class="bv-game-modal__backdrop" data-bv-game-close></div>
  <div class="bv-game-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="bvGameChooserTitle">
    <button type="button" class="bv-game-modal__close" data-bv-game-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <div class="bv-game-modal__head">
      <div class="bv-game-modal__icon"><i class="fa-solid fa-gamepad-modern"></i></div>
      <div>
        <div class="bv-game-modal__eyebrow">Choose game</div>
        <h3 id="bvGameChooserTitle">Play with <?= $bUsername ?></h3>
        <p id="bvGameChooserDescription">Select the game first, then we will open the matching boost form.</p>
      </div>
    </div>
    <div class="bv-game-modal__body" id="bvGameChooserOptions"></div>
  </div>
</div>

<style>
.bv-game-modal{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;padding:24px}.bv-game-modal.is-open{display:flex}.bv-game-modal__backdrop{position:absolute;inset:0;background:rgba(3,8,22,.78);backdrop-filter:blur(10px)}.bv-game-modal__dialog{position:relative;width:min(680px,calc(100vw - 36px));border-radius:26px;background:linear-gradient(180deg,rgba(18,27,56,.98) 0%,rgba(11,18,42,.99) 100%);border:1px solid rgba(96,165,250,.22);box-shadow:0 32px 110px rgba(2,8,28,.62),0 0 0 1px rgba(59,130,246,.08) inset;overflow:hidden}.bv-game-modal__dialog:before{content:"";position:absolute;inset:-120px auto auto -90px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(59,130,246,.23) 0%,rgba(59,130,246,0) 72%);pointer-events:none}.bv-game-modal__dialog:after{content:"";position:absolute;inset:auto -90px -120px auto;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.18) 0%,rgba(37,99,235,0) 72%);pointer-events:none}.bv-game-modal__close{position:absolute;right:18px;top:18px;width:42px;height:42px;border:1px solid rgba(255,255,255,.08);border-radius:13px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.68);display:flex;align-items:center;justify-content:center;z-index:2;transition:background .14s,border-color .14s,color .14s}.bv-game-modal__close:hover{background:rgba(59,130,246,.18);border-color:rgba(96,165,250,.3);color:#fff}.bv-game-modal__head{position:relative;z-index:1;display:flex;gap:18px;align-items:center;padding:30px 34px 20px;border-bottom:1px solid rgba(148,163,184,.12);background:linear-gradient(180deg,rgba(255,255,255,.025),rgba(255,255,255,.01))}.bv-game-modal__icon{width:60px;height:60px;border-radius:18px;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(59,130,246,.22),rgba(37,99,235,.16));border:1px solid rgba(96,165,250,.3);color:#bfdbfe;font-size:1.35rem;flex-shrink:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.08)}.bv-game-modal__eyebrow{font-size:.77rem;font-weight:950;text-transform:uppercase;letter-spacing:.13em;color:#93c5fd;margin-bottom:4px}.bv-game-modal h3{font-size:1.7rem;font-weight:950;color:#f8fbff;margin:0;line-height:1.08}.bv-game-modal p{font-size:.95rem;color:rgba(226,232,240,.68);margin:8px 0 0;max-width:440px;line-height:1.5}.bv-game-modal__body{position:relative;z-index:1;display:grid;gap:14px;padding:22px 34px 32px}.bv-game-choice{width:100%;display:flex;align-items:center;gap:16px;text-align:left;border:1px solid rgba(148,163,184,.14);border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.02));padding:18px 18px;color:#fff;cursor:pointer;transition:background .14s,border-color .14s,transform .14s,box-shadow .14s;min-height:84px}.bv-game-choice:hover{background:linear-gradient(180deg,rgba(59,130,246,.16),rgba(37,99,235,.11));border-color:rgba(96,165,250,.35);transform:translateY(-1px);box-shadow:0 12px 28px rgba(30,64,175,.18)}.bv-game-choice img{width:42px;height:42px;object-fit:contain;border-radius:12px;flex-shrink:0}.bv-game-choice b{display:block;font-size:1.08rem;font-weight:950;color:#f8fbff;line-height:1.15}.bv-game-choice span{display:block;font-size:.88rem;color:rgba(226,232,240,.6);margin-top:4px;line-height:1.35}.bv-game-choice i{margin-left:auto;color:rgba(191,219,254,.5);font-size:1rem}.bv-game-choice:hover i{color:#dbeafe}.bv-game-choice > div{min-width:0}@media (max-width:900px){.bv-game-modal{padding:18px}.bv-game-modal__dialog{width:min(620px,calc(100vw - 28px))}.bv-game-modal__head{padding:26px 24px 18px}.bv-game-modal__body{padding:20px 24px 26px}.bv-game-modal h3{font-size:1.48rem}.bv-game-choice{min-height:78px;padding:16px}}@media (max-width:575px){.bv-game-modal{padding:14px}.bv-game-modal__dialog{width:calc(100vw - 16px);border-radius:22px}.bv-game-modal__head{gap:14px;padding:22px 18px 16px}.bv-game-modal__icon{width:52px;height:52px;border-radius:16px;font-size:1.16rem}.bv-game-modal h3{font-size:1.25rem}.bv-game-modal p{font-size:.88rem;max-width:none}.bv-game-modal__body{padding:18px 18px 22px;gap:10px}.bv-game-choice{gap:12px;min-height:72px;padding:15px 14px;border-radius:16px}.bv-game-choice img{width:36px;height:36px;border-radius:10px}.bv-game-choice b{font-size:.98rem}.bv-game-choice span{font-size:.8rem}.bv-game-modal__close{right:12px;top:12px;width:38px;height:38px;border-radius:12px}}

/* Compact, viewport-safe game grid. */
.bv-game-modal{padding:16px;overflow:hidden}
.bv-game-modal__dialog{width:min(1160px,calc(100vw - 32px));max-height:calc(100dvh - 32px);display:flex;flex-direction:column}
.bv-game-modal__head{flex:0 0 auto;padding:22px 28px 18px}
.bv-game-modal__body{grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:18px 28px 24px;overflow-y:auto;overscroll-behavior:contain;min-height:0;scrollbar-width:thin;scrollbar-color:rgba(96,165,250,.45) rgba(255,255,255,.04)}
.bv-game-modal__body::-webkit-scrollbar{width:8px}.bv-game-modal__body::-webkit-scrollbar-track{background:rgba(255,255,255,.04)}.bv-game-modal__body::-webkit-scrollbar-thumb{background:rgba(96,165,250,.42);border-radius:999px}
.bv-game-choice{min-width:0;min-height:76px;padding:14px;border-radius:16px;gap:12px}
.bv-game-choice img{width:40px;height:40px}.bv-game-choice b{font-size:1rem}.bv-game-choice span{font-size:.79rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bv-game-choice i{flex-shrink:0}
@media(max-width:900px){.bv-game-modal__dialog{width:min(760px,calc(100vw - 24px));max-height:calc(100dvh - 24px)}.bv-game-modal__body{grid-template-columns:repeat(2,minmax(0,1fr));padding:16px 20px 20px}}
@media(max-width:575px){.bv-game-modal{padding:8px}.bv-game-modal__dialog{width:calc(100vw - 16px);max-height:calc(100dvh - 16px)}.bv-game-modal__head{padding:18px 58px 14px 16px}.bv-game-modal__body{grid-template-columns:1fr;padding:14px 16px 18px}.bv-game-choice{min-height:68px}}
</style>

<script>
(function(){
  'use strict';
  var root = document.getElementById('bvOverviewRecentGames');
  var list = document.getElementById('bvOverviewRecentGamesList');
  var overviewRecentBadge = document.getElementById('bvOverviewRecentBadge');
  var progRoot = document.getElementById('bvOverviewProgression');
  var progList = document.getElementById('bvOverviewProgressionList');
  if (!root || !list) return;

  var BOOSTER_ID = <?= (int)$__boosterId ?>;
  var AJAX_URL   = '<?= defined('BASE_URL') ? rtrim(BASE_URL,'/') : '' ?>/ajax';
  var ASSET_BASE = '<?= ASSET_URL ?>';
  var LOL_CHAMP  = '<?= defined('LOL_CHAMP_URL') ? LOL_CHAMP_URL : (ASSET_URL.'/core/main/img/lol/champs') ?>';

  var ALL_RANKS = [
    {tier:0,name:'Unranked'},{tier:1,name:'Iron'},{tier:2,name:'Bronze'},
    {tier:3,name:'Silver'},{tier:4,name:'Gold'},{tier:5,name:'Platinum'},
    {tier:6,name:'Emerald'},{tier:7,name:'Diamond'},{tier:8,name:'Master'},
    {tier:9,name:'Grandmaster'},{tier:10,name:'Challenger'}
  ];
  var QUEUES = {420:'Ranked Solo/Duo',440:'Ranked Flex',430:'Blind Pick',400:'Draft Pick',450:'ARAM',480:'Swiftplay',490:'Quickplay',900:'ARURF',1900:'URF',700:'Clash',720:'ARAM Clash',1700:'Arena',1710:'Arena',0:'Custom'};

  function esc(value) { return String(value == null ? '' : value).replace(/[&<>'\"]/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[ch]; }); }
  function champImgUrl(name) { if (!name) return ''; var fix = {'Wukong':'MonkeyKing'}; return LOL_CHAMP + '/' + (fix[name] || String(name).replace(/[^a-zA-Z0-9]/g,'')) + '.png'; }
  function kda(k,d,a) { k=parseInt(k)||0; d=parseInt(d)||0; a=parseInt(a)||0; if (d===0) return (k+a)===0 ? '0.00' : 'Perfect'; return ((k+a)/d).toFixed(2); }
  function fmtDate(iso) { if (!iso) return ''; try { var d = new Date(String(iso).replace(' ','T')); if (isNaN(d.getTime())) return ''; return String(d.getDate()).padStart(2,'0')+'.'+String(d.getMonth()+1).padStart(2,'0')+'.'+d.getFullYear(); } catch(e) { return ''; } }
  function modeBadge(row) { var isDuo = parseInt(row.is_duo || 0) === 1; return '<span class="bv-sb-mode-badge '+(isDuo?'duo':'solo')+'">'+(isDuo?'Duo':'Solo')+'</span>'; }
  function renderProgression(ranks) {
    if (!progRoot || !progList) return;
    ranks = ranks || [];
    var dataMap = {};
    ranks.forEach(function(r){ dataMap[parseInt(r.tier)] = r; });
    var active = ALL_RANKS.filter(function(rank){ var r = dataMap[rank.tier]; return r && ((parseInt(r.wins)||0) + (parseInt(r.losses)||0)) > 0; });
    var shown = (active.length ? active : ALL_RANKS.slice(0,4)).slice(-4);
    if (!shown.length) { progList.innerHTML = '<div class="bv-sb-prog-empty"><i class="fa-solid fa-chart-line"></i> No progression data yet.</div>'; return; }
    progList.innerHTML = shown.map(function(rank){
      var r = dataMap[rank.tier] || {};
      var wins = parseInt(r.wins)||0, losses = parseInt(r.losses)||0, total = wins + losses;
      var wr = total ? (wins / total * 100) : 0;
      var wrCls = total ? (wr >= 60 ? 'good' : (wr >= 50 ? 'mid' : 'low')) : '';
      return '<div class="bv-sb-prog-item '+(total ? '' : 'is-empty')+'">'
        + '<img src="'+esc(ASSET_BASE+'/core/main/img/lol/ranks/max/'+rank.tier+'.png')+'" alt="'+esc(rank.name)+'" onerror="this.style.display=\'none\'">'
        + '<div style="min-width:0;"><div class="bv-sb-prog-rank">'+esc(rank.name)+'</div><div class="bv-sb-prog-record">'+wins+'W · '+losses+'L</div></div>'
        + '<div class="bv-sb-prog-wr '+wrCls+'">'+(total ? wr.toFixed(1) : '0')+'%</div>'
      + '</div>';
    }).join('');
  }
  function renderGames(rows) {
    rows = rows || [];
    if (!rows.length) { list.innerHTML = '<div class="bv-sb-recent-empty"><i class="fa-solid fa-gamepad"></i> No recent games found.</div>'; return; }
    list.innerHTML = rows.slice(0,5).map(function(r){
      var result = r.result || (parseInt(r.won) ? 'win' : 'loss');
      var k = parseInt(r.kills)||0, d = parseInt(r.deaths)||0, a = parseInt(r.assists)||0;
      var champ = r.champion || 'Unknown';
      var img = champImgUrl(champ);
      var queue = QUEUES[parseInt(r.queue_id)] || 'Ranked Solo';
      var date = fmtDate(r.played_at);
      return '<div class="bv-sb-game">'
        + '<div class="bv-sb-game-stripe '+esc(result)+'"></div>'
        + '<div class="bv-sb-game-champ">'+(img ? '<img src="'+esc(img)+'" alt="'+esc(champ)+'" onerror="this.style.opacity=\'.25\'">' : '')+'</div>'
        + '<div style="min-width:0;"><div class="bv-sb-game-name">'+esc(champ)+'</div><div class="bv-sb-game-meta">'+esc(queue)+(date ? ' · '+esc(date) : '')+'</div></div>'
        + '<div class="bv-sb-game-kda">'+k+'/'+d+'/'+a+'<span>'+esc(kda(k,d,a))+' KDA</span></div>'
      + '</div>';
    }).join('');
  }

  if (progRoot && progList) {
    fetch(AJAX_URL, {method:'POST', body:new URLSearchParams({action:'get_booster_stats',booster_id:BOOSTER_ID,year:0,mode:'all',champ_mode:'all'}), headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(function(data){ if (!data.ok) throw new Error(data.message || 'Error'); renderProgression(data.ranks || []); })
      .catch(function(err){ progList.innerHTML = '<div class="bv-sb-prog-empty"><i class="fa-solid fa-triangle-exclamation"></i> Could not load progression.</div>'; console.error('[bv-overview-progression]', err); });
  }

  fetch(AJAX_URL, {method:'POST', body:new URLSearchParams({action:'get_booster_performance',booster_id:BOOSTER_ID,page:1,per_page:5,mode:'all',booster_owned_only:1}), headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){ return r.json(); })
    .then(function(data){ if (!data.ok) throw new Error(data.message || 'Error'); renderGames((data.matches && data.matches.rows) ? data.matches.rows : []); })
    .catch(function(err){ list.innerHTML = '<div class="bv-sb-recent-empty"><i class="fa-solid fa-triangle-exclamation"></i> Could not load games.</div>'; console.error('[bv-overview-recent-games]', err); });
})();
</script>
<?= $this->insert('website/components/get-started', ['variation' => 'one']) ?>

<!-- Send Tip Modal -->
<div class="modal" id="send_tip_modal">
  <div class="bv-tip-modal">
    <button type="button" class="bv-tip-close close-modal"><i class="fas fa-times"></i></button>

    <div class="bv-tip-header">
      <div class="bv-tip-copy">
        <span class="bv-tip-kicker"><?= t('Booster Support') ?></span>
        <div class="bv-tip-title"><?= t('Send a tip') ?></div>
        <p class="bv-tip-desc"><?= t('Show your appreciation with a quick tip and add an optional message for your booster.') ?></p>

        <div class="bv-tip-recipient">
          <div class="bv-tip-avatar">
            <?php if ($bIcon): ?>
              <img src="<?= htmlspecialchars($bIcon, ENT_QUOTES) ?>" alt="<?= $bUsername ?>">
            <?php else: ?>
              <span><?= $bInitial ?></span>
            <?php endif; ?>
          </div>
          <div class="bv-tip-recipient-copy">
            <small><?= t('Recipient') ?></small>
            <strong><?= $bUsername ?></strong>
          </div>
        </div>
      </div>

      <div class="bv-tip-icon-box" aria-hidden="true">
        <i class="fa-solid fa-heart"></i>
      </div>
    </div>

    <div class="bv-tip-body">
      <form class="bv-tip-checkout-form" id="send_tip_form" action="<?= AJAX_URL ?>" method="post">
        <input type="hidden" name="action" value="client_send_tip">
        <input type="hidden" name="order_id" value="">
        <input type="hidden" name="booster_id" value="<?= $__boosterId ?>">

        <div class="bv-tip-quick">
          <button type="button" class="bv-tip-quick-btn quick-tip-btn active" data-amount="5.00">
            <span class="bv-tip-quick-sym"><?= util_format_currency_display($_SESSION['currency'] ?? 'EUR') ?></span>5
          </button>
          <button type="button" class="bv-tip-quick-btn quick-tip-btn" data-amount="10.00">
            <span class="bv-tip-quick-sym"><?= util_format_currency_display($_SESSION['currency'] ?? 'EUR') ?></span>10
          </button>
          <button type="button" class="bv-tip-quick-btn quick-tip-btn" data-amount="20.00">
            <span class="bv-tip-quick-sym"><?= util_format_currency_display($_SESSION['currency'] ?? 'EUR') ?></span>20
          </button>
          <button type="button" class="bv-tip-quick-btn quick-tip-btn" data-amount="50.00">
            <span class="bv-tip-quick-sym"><?= util_format_currency_display($_SESSION['currency'] ?? 'EUR') ?></span>50
          </button>
        </div>

        <div class="bv-tip-section bv-tip-amount-wrap">
          <label class="bv-tip-label"><?= t('Tip Amount') ?></label>
          <div class="bv-tip-input-row">
            <button type="button" class="bv-tip-stepper" onclick="decrementTip()"><i class="fas fa-minus"></i></button>
            <div class="bv-tip-input-box">
              <span class="bv-tip-currency"><?= util_format_currency_display($_SESSION['currency'] ?? 'EUR') ?></span>
              <input type="number" name="amount" id="bvTipAmount" value="5.00" min="1" step="0.50" required>
            </div>
            <button type="button" class="bv-tip-stepper" onclick="incrementTip()"><i class="fas fa-plus"></i></button>
          </div>
        </div>

        <div class="bv-tip-section bv-tip-msg-wrap">
          <label class="bv-tip-label"><?= t('Message') ?> <span class="bv-tip-optional">(<?= t('optional') ?>)</span></label>
          <textarea name="note" class="bv-tip-textarea" placeholder="<?= t('Write a message...') ?>"></textarea>
        </div>

        <button class="bv-tip-submit submit-btn" type="submit">
          <span class="indicator-label"><i class="fa-solid fa-heart"></i> <?= t('Send Tip') ?></span>
          <span class="indicator-progress"><span class="loader"></span></span>
        </button>
      </form>
      <div class="alert danger form-error" style="display:none"></div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){

  /* ── Tab switching ── */
  document.querySelectorAll('.bv-nav-tabs a[data-bvtab]').forEach(function(a){
    a.addEventListener('click', function(e){
      e.preventDefault();
      var target = this.dataset.bvtab;
      document.querySelectorAll('.bv-nav-tabs a').forEach(function(x){ x.classList.remove('active'); });
      document.querySelectorAll('.bv-tab-pane').forEach(function(p){ p.classList.remove('active'); });
      this.classList.add('active');
      var pane = document.getElementById(target);
      if (pane) pane.classList.add('active');
    });
  });

  /* ── Hash-based open ── */
  (function(){
    var hash = window.location.hash.replace('#','');
    if (!hash) return;
    var link = document.querySelector('.bv-nav-tabs a[data-bvtab="'+hash+'"]');
    var pane = document.getElementById(hash);
    if (link && pane) {
      document.querySelectorAll('.bv-nav-tabs a').forEach(function(x){ x.classList.remove('active'); });
      document.querySelectorAll('.bv-tab-pane').forEach(function(p){ p.classList.remove('active'); });
      link.classList.add('active'); pane.classList.add('active');
    }
  })();

  /* ── Feedback slider ── */
  (function(){
    var track = document.getElementById('bvFbTrack');
    var prev  = document.getElementById('bvFbPrev');
    var next  = document.getElementById('bvFbNext');
    if (!track||!prev||!next) return;
    function slideAmt(){ var s=track.querySelector('.bv-feedback-slide'); if(!s) return 400; return s.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap||'18')||400; }
    prev.addEventListener('click', function(){ track.scrollBy({left:-slideAmt(),behavior:'smooth'}); });
    next.addEventListener('click', function(){ track.scrollBy({left:slideAmt(),behavior:'smooth'}); });
  })();

  /* ── Expand long review comments ── */
  function bindToggles(root){
    (root||document).querySelectorAll('.bv-review-comment').forEach(function(box){
      if (box.dataset.bound) return;
      box.dataset.bound='1';
      var text   = box.querySelector('.js-review-text');
      var toggle = box.querySelector('.js-review-toggle');
      if (!text||!toggle) return;
      var len = (text.textContent || '').trim().length;
      var words = (text.textContent || '').trim().split(/\s+/).filter(Boolean).length;

      // Only show the toggle for genuinely long comments.
      // This prevents "Show more" from appearing on normal/short reviews.
      if (len < 400 && words < 70) {
        toggle.hidden = true;
        toggle.style.display = 'none';
        return;
      }

      // For long comments, show the toggle only if the clamped text actually overflows.
      requestAnimationFrame(function(){
        if (text.scrollHeight > text.clientHeight + 8) {
          toggle.hidden = false;
          toggle.style.display = '';
        } else {
          toggle.hidden = true;
          toggle.style.display = 'none';
        }
      });
      toggle.addEventListener('click', function(){
        var expanded = text.classList.toggle('is-expanded');
        text.classList.toggle('bv-review-comment-text', !expanded);
        toggle.textContent = expanded ? 'Show less' : 'Show more';
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      });
    });
  }
  bindToggles();
  if (window.jQuery) $(document).ajaxComplete(function(){ bindToggles(); });

  /* ── Action buttons ── */
  var bvGameMeta = <?= json_encode($__gameActionMeta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var bvGameModal = document.getElementById('bvGameChooser');
  var bvGameModalOptions = document.getElementById('bvGameChooserOptions');
  function bvNormalizeGames(raw){
    var aliases = {'league':'lol','league-of-legends':'lol','league_of_legends':'lol','valorant':'val','teamfight-tactics':'tft','teamfight_tactics':'tft'};
    var out = [];
    String(raw || 'lol').split(/[|,]/).forEach(function(g){
      g = String(g || '').trim().toLowerCase();
      g = aliases[g] || g;
      if (bvGameMeta[g] && out.indexOf(g) === -1) out.push(g);
    });
    return out;
  }
  function bvGoToBoosterForm(btn, kind, game){
    sessionStorage.setItem('booster', btn.dataset.booster || '<?= $__boosterId ?>');
    sessionStorage.setItem('booster_game', game);
    var meta = bvGameMeta[game];
    if (!meta) return;
    var target = kind === 'coaching' ? meta.coaching : meta.boost;
    if (target) window.location.href = target;
  }
  function bvOpenGameChooser(btn, kind, games){
    if (!bvGameModal || !bvGameModalOptions) { bvGoToBoosterForm(btn, kind, games[0] || 'lol'); return; }
    bvGameModalOptions.innerHTML = '';
    var chooserTitle = document.getElementById('bvGameChooserTitle');
    var chooserDescription = document.getElementById('bvGameChooserDescription');
    if (chooserTitle) chooserTitle.textContent = (kind === 'coaching' ? 'Coach with ' : 'Play with ') + (btn.dataset.username || '<?= addslashes($bUsername) ?>');
    if (chooserDescription) chooserDescription.textContent = kind === 'coaching'
      ? 'Select the game first, then we will open the matching coaching form.'
      : 'Select the game first, then we will open the matching boost form.';
    games.forEach(function(game){
      var meta = bvGameMeta[game];
      if (!meta) return;
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'bv-game-choice';
      button.innerHTML = '<img src="'+meta.icon+'" alt="">'
        + '<div><b>'+meta.label+'</b><span>Open '+meta.label+(kind === 'coaching' ? ' Coaching' : ' Rank Boost')+'</span></div>'
        + '<i class="fa-solid fa-arrow-right"></i>';
      button.addEventListener('click', function(){
        bvGameModal.classList.remove('is-open');
        bvGameModal.setAttribute('aria-hidden', 'true');
        bvGoToBoosterForm(btn, kind, game);
      });
      bvGameModalOptions.appendChild(button);
    });
    bvGameModal.classList.add('is-open');
    bvGameModal.setAttribute('aria-hidden', 'false');
  }
  document.querySelectorAll('[data-bv-game-close]').forEach(function(el){
    el.addEventListener('click', function(){
      if (!bvGameModal) return;
      bvGameModal.classList.remove('is-open');
      bvGameModal.setAttribute('aria-hidden', 'true');
    });
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && bvGameModal) {
      bvGameModal.classList.remove('is-open');
      bvGameModal.setAttribute('aria-hidden', 'true');
    }
  });
  document.querySelectorAll('.js-request-boost').forEach(function(btn){
    btn.addEventListener('click', function(){
      var games = bvNormalizeGames(this.dataset.games).filter(function(game){ return !!bvGameMeta[game].boost; });
      if (!games.length) return;
      if (games.length === 1) bvGoToBoosterForm(this, 'boost', games[0]);
      else bvOpenGameChooser(this, 'boost', games);
    });
  });
  document.querySelectorAll('.js-request-coaching').forEach(function(btn){
    btn.addEventListener('click', function(){
      var games = bvNormalizeGames(this.dataset.games).filter(function(game){ return !!bvGameMeta[game].coaching; });
      if (!games.length) return;
      if (games.length === 1) bvGoToBoosterForm(this, 'coaching', games[0]);
      else bvOpenGameChooser(this, 'coaching', games);
    });
  });
  document.querySelectorAll('.js-send-tip').forEach(function(btn){
    btn.addEventListener('click', function(){
      var modal = document.getElementById('send_tip_modal');
      if (modal && window.Modal) new Modal('#send_tip_modal').show();
      else if (modal && window.$) $('#send_tip_modal').show();
    });
  });

  /* ── AJAX pagination for orders and reviews, keeps the profile URL unchanged ── */
  function cleanBoosterPagingParams(){
    if (!window.history || !window.history.replaceState) return;
    try {
      var url = new URL(window.location.href);
      var changed = false;
      ['opage', 'rpage', 'path'].forEach(function(param){
        if (url.searchParams.has(param)) {
          url.searchParams.delete(param);
          changed = true;
        }
      });
      if (changed) window.history.replaceState(null, document.title, url.pathname + url.search + url.hash);
    } catch (e) {}
  }
  cleanBoosterPagingParams();

  (function(){
    var list = document.getElementById('bvOrdersList');
    var pager = document.getElementById('bvOrdersPagination');
    if (!list || !pager) return;

    var boosterId = pager.dataset.booster || '<?= $__boosterId ?>';
    var perPage = parseInt(pager.dataset.perPage || list.dataset.perPage || '<?= $ordersPerPage ?>', 10) || 10;
    var busy = false;

    function setLoading(state){
      busy = !!state;
      pager.classList.toggle('is-loading', busy);
      Array.prototype.forEach.call(pager.querySelectorAll('button'), function(btn){
        if (busy) btn.disabled = true;
        else if (!btn.classList.contains('is-disabled')) btn.disabled = false;
      });
    }

    function loadPage(page){
      page = Math.max(1, parseInt(page || 1, 10));
      if (busy) return;
      setLoading(true);

      var body = new URLSearchParams();
      body.append('action', 'load_booster_orders_page');
      body.append('booster_id', boosterId);
      body.append('page', page);
      body.append('limit', perPage);

      fetch('<?= AJAX_URL ?>', {
        method: 'POST',
        body: body,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(function(response){ return response.json(); })
      .then(function(data){
        if (!data || !data.success) throw new Error((data && data.message) || 'Could not load orders.');
        list.innerHTML = data.html || '';
        if (data.pagination_html) {
          pager.outerHTML = data.pagination_html;
          pager = document.getElementById('bvOrdersPagination');
        }
        cleanBoosterPagingParams();
      })
      .catch(function(error){ console.error('[booster orders pagination]', error); })
      .finally(function(){ setLoading(false); });
    }

    document.addEventListener('click', function(event){
      var button = event.target.closest('#bvOrdersPagination [data-order-page-btn]');
      if (!button) return;
      event.preventDefault();
      loadPage(button.dataset.orderPageBtn);
    });
  })();

  (function(){
    var list = document.getElementById('bvReviewsList');
    var pager = document.getElementById('bvReviewPagination');
    if (!list || !pager) return;

    var boosterId = pager.dataset.booster || '<?= $__boosterId ?>';
    var perPage = parseInt(pager.dataset.perPage || list.dataset.perPage || '<?= $reviewsPerPage ?>', 10) || 6;
    var busy = false;

    function setLoading(state){
      busy = !!state;
      pager.classList.toggle('is-loading', busy);
      Array.prototype.forEach.call(pager.querySelectorAll('button'), function(btn){
        if (busy) btn.disabled = true;
        else if (!btn.classList.contains('is-disabled')) btn.disabled = false;
      });
    }

    function loadPage(page){
      page = Math.max(1, parseInt(page || 1, 10));
      if (busy) return;
      setLoading(true);

      var body = new URLSearchParams();
      body.append('action', 'load_booster_reviews_page');
      body.append('booster_id', boosterId);
      body.append('page', page);
      body.append('limit', perPage);

      fetch('<?= AJAX_URL ?>', {
        method: 'POST',
        body: body,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(function(response){ return response.json(); })
      .then(function(data){
        if (!data || !data.success) throw new Error((data && data.message) || 'Could not load reviews.');
        list.innerHTML = data.html || '';
        if (data.pagination_html) {
          pager.outerHTML = data.pagination_html;
          pager = document.getElementById('bvReviewPagination');
        }
        bindToggles(list);
        cleanBoosterPagingParams();
      })
      .catch(function(error){ console.error('[booster reviews pagination]', error); })
      .finally(function(){ setLoading(false); });
    }

    document.addEventListener('click', function(event){
      var button = event.target.closest('#bvReviewPagination [data-review-page-btn]');
      if (!button) return;
      event.preventDefault();
      loadPage(button.dataset.reviewPageBtn);
    });
  })();

})();

/* ── Tip modal helpers ── */
function bvTipInput(){ return document.getElementById('bvTipAmount'); }
function bvTipSet(value){
  var input = bvTipInput();
  if (!input) return;
  var n = parseFloat(String(value || '').replace(',', '.'));
  if (isNaN(n) || n < 1) n = 1;
  input.value = n.toFixed(2);
}
function incrementTip(){ var input = bvTipInput(); bvTipSet((parseFloat(input && input.value ? input.value : '5') || 5) + 1); }
function decrementTip(){ var input = bvTipInput(); bvTipSet(Math.max(1, (parseFloat(input && input.value ? input.value : '5') || 5) - 1)); }

(function(){
  if (window.jQuery) {
    $('.quick-tip-btn').off('click.bvTip').on('click.bvTip', function(){
      var amt = $(this).data('amount');
      bvTipSet(amt);
      $('.bv-tip-quick-btn').removeClass('active');
      $(this).addClass('active');
    });
    $('.js-send-tip').off('click.bvTipOpen').on('click.bvTipOpen', function(){
      new Modal('#send_tip_modal').show();
    });
  }

  var form = document.getElementById('send_tip_form');
  if (!form || form.dataset.bvTipSubmitBound === '1') return;
  form.dataset.bvTipSubmitBound = '1';

  form.addEventListener('submit', function(e){
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

    var button = form.querySelector('.submit-btn');
    var errorBox = document.querySelector('#send_tip_modal .form-error');
    if (errorBox) { errorBox.style.display = 'none'; errorBox.textContent = ''; }
    if (button) button.setAttribute('data-indicator', 'on');

    fetch(form.getAttribute('action') || '<?= AJAX_URL ?>', {
      method: 'POST',
      body: new FormData(form),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(function(response){ return response.text(); })
    .then(function(text){
      var data = null;
      try { data = JSON.parse(text); }
      catch (err) {
        console.error('[send-tip] invalid JSON response:', text);
        throw new Error('Checkout could not be created. Please try again.');
      }

      if (data && data.redirectUrl) {
        window.location.href = data.redirectUrl;
        return;
      }

      var message = 'Checkout could not be created. Please try again.';
      if (data && data.sendToast && data.sendToast.message) message = data.sendToast.message;
      throw new Error(message);
    })
    .catch(function(err){
      if (errorBox) {
        errorBox.textContent = err && err.message ? err.message : 'Checkout could not be created. Please try again.';
        errorBox.style.display = 'block';
      }
      if (window.create_toast) create_toast('danger', 'Error', err && err.message ? err.message : 'Checkout could not be created.');
    })
    .finally(function(){
      if (button) button.removeAttribute('data-indicator');
    });
  }, true);
})();
</script>

<script>
// Booster local time — ticks in the visitor's browser, no server round-trip.
(function () {
  var box = document.querySelector('[data-bv-tz]');
  if (!box) return;

  var zone = box.getAttribute('data-bv-tz');
  var out = box.querySelector('[data-bv-tz-time]');
  if (!zone || !out) return;

  var fmt;
  try {
    fmt = new Intl.DateTimeFormat([], { timeZone: zone, hour: '2-digit', minute: '2-digit', hour12: false });
  } catch (e) {
    // Unknown/invalid timezone string — hide instead of showing a broken value.
    box.style.display = 'none';
    return;
  }

  function tick() {
    try { out.textContent = fmt.format(new Date()); } catch (e) {}
  }

  tick();
  setInterval(tick, 30000);
})();
</script>
<?= $this->end() ?>
