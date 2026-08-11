<?php
// Dynamic rank component for generic / new games.
// This file intentionally contains no League of Legends specific rank rules.

$_data = $data ?? [];
$_jsonData = $_data['json'] ?? [];
$_gameSlug = $_data['game'] ?? ($game ?? 'generic');
$_shortMap = ['league-of-legends' => 'lol', 'valorant' => 'val', 'teamfight-tactics' => 'tft'];
$_gameShort = $_shortMap[$_gameSlug] ?? $_gameSlug;
$_formSlug = strtolower(trim((string)($_data['slug'] ?? 'rank-boost')));
$_isWinForm = $_formSlug === 'win-boost';
$_isPlacementForm = in_array($_formSlug, ['placement', 'placement-boost', 'placements-boost'], true);
$_isGamesForm = $_isWinForm || $_isPlacementForm;
$_matchesDefault = max(1, (int)($_jsonData['matches_default'] ?? ($_isWinForm ? 2 : 5)));
$_matchesMax = max($_matchesDefault, (int)($_jsonData['matches_max'] ?? $_jsonData['games_max'] ?? 10));

if (!function_exists('lb_dynamic_pick_label')) {
    function lb_dynamic_pick_label($value, string $fallback): string {
        if (is_array($value)) {
            foreach (['name', 'label', 'title', 'rank', 'long_name'] as $key) {
                if (isset($value[$key]) && trim((string)$value[$key]) !== '') {
                    return trim((string)$value[$key]);
                }
            }
        } elseif (trim((string)$value) !== '') {
            return trim((string)$value);
        }
        return $fallback;
    }
}

if (!function_exists('lb_dynamic_rank_icon_url')) {
    function lb_dynamic_rank_icon_url(array $jsonData, string $game, string $size, int $rank): string {
        if (function_exists('lb_generic_rank_icon_url')) {
            return lb_generic_rank_icon_url($jsonData, $game, $size, $rank);
        }

        $sources = [
            $jsonData['rank_icons_' . $size] ?? null,
            $jsonData['rank_icons'] ?? null,
            $jsonData['rank_icon_urls'] ?? null,
            $jsonData['icons'] ?? null,
        ];

        foreach ($sources as $icons) {
            if (!is_array($icons)) continue;
            $raw = $icons[$rank] ?? $icons[(string)$rank] ?? null;
            if (is_array($raw)) $raw = $raw[$size] ?? $raw['url'] ?? $raw['icon'] ?? $raw['src'] ?? null;
            $raw = trim((string)$raw);
            if ($raw === '') continue;
            if (preg_match('~^https?://~i', $raw) || strpos($raw, '/') === 0) return $raw;
            return ASSET_URL . '/' . ltrim($raw, '/');
        }

        return function_exists('util_rank_img') ? util_rank_img($game, $size, $rank) : '';
    }
}

if (!function_exists('lb_dynamic_division_count')) {
    function lb_dynamic_division_count(array $jsonData, int $fallback = 4): int {
        if (function_exists('lb_generic_division_count_from_json')) {
            return lb_generic_division_count_from_json($jsonData, $fallback);
        }
        foreach (['division_count', 'divisions_count', 'divisionCount', 'divisions', 'division'] as $key) {
            if (isset($jsonData[$key]) && is_numeric($jsonData[$key])) {
                $count = (int)$jsonData[$key];
                return ($count >= 0 && $count <= 10) ? $count : $fallback;
            }
        }
        return $fallback;
    }
}


if (!function_exists('lb_dynamic_rank_division_count')) {
    function lb_dynamic_rank_division_count(array $jsonData, int $tier, int $fallback): int {
        $rankDivs = $jsonData['form_config']['rank_divs'] ?? $jsonData['rank_divs'] ?? [];
        if (is_array($rankDivs)) {
            $raw = $rankDivs[$tier] ?? $rankDivs[(string)$tier] ?? null;
            if ($raw !== null && $raw !== '') {
                $count = (int)$raw;
                return ($count >= 0 && $count <= 10) ? $count : $fallback;
            }
        }
        return $fallback;
    }
}

if (!function_exists('lb_dynamic_division_label')) {
    function lb_dynamic_division_label(int $value, int $count, array $jsonData): string {
        $custom = $jsonData['division_names'] ?? $jsonData['division_labels'] ?? $jsonData['divisionNames'] ?? null;
        if (is_array($custom)) {
            $label = $custom[$value] ?? $custom[(string)$value] ?? null;
            if ($label !== null && trim((string)$label) !== '') return trim((string)$label);
        }

        if ($count === 4) {
            $roman = [1 => 'IV', 2 => 'III', 3 => 'II', 4 => 'I'];
            return $roman[$value] ?? (string)$value;
        }
        if ($count === 3) {
            $roman = [1 => 'III', 2 => 'II', 3 => 'I'];
            return $roman[$value] ?? (string)$value;
        }
        if ($count === 5) {
            $numbers = [1 => '5', 2 => '4', 3 => '3', 4 => '2', 5 => '1'];
            return $numbers[$value] ?? (string)$value;
        }

        return (string)$value;
    }
}

if (!isset($ranks) || !is_array($ranks) || empty($ranks)) {
    $_rankSource = $_jsonData['rank_names'] ?? $_jsonData['rankNames'] ?? $_jsonData['ranks'] ?? $_jsonData['tiers'] ?? [];
    $ranks = [];
    if (is_array($_rankSource)) {
        foreach ($_rankSource as $_key => $_value) {
            $_idx = is_numeric($_key) ? (int)$_key : (count($ranks) + 1);
            if ($_idx < 0) $_idx = count($ranks) + 1;
            $ranks[$_idx] = lb_dynamic_pick_label($_value, 'Tier ' . $_idx);
        }
    }
    if (empty($ranks) && !empty($_jsonData['main']) && is_array($_jsonData['main'])) {
        foreach (array_keys($_jsonData['main']) as $_tierKey) {
            if (is_numeric($_tierKey)) $ranks[(int)$_tierKey] = 'Tier ' . (int)$_tierKey;
        }
    }
    if (empty($ranks)) $ranks = [1 => 'Tier 1', 2 => 'Tier 2', 3 => 'Tier 3', 4 => 'Tier 4', 5 => 'Tier 5'];
}

ksort($ranks, SORT_NUMERIC);
$_rankKeys = array_values(array_map('intval', array_keys($ranks)));

// Fortnite: Unreal (the top rank) is desired-only — nobody starts a boost already at the
// very top, so it's excluded from Current Rank but stays selectable as Desired Rank.
if ($_gameSlug === 'fortnite' && !isset($_jsonData['start_max_tier']) && count($_rankKeys) > 1) {
    $_jsonData['start_max_tier'] = $_rankKeys[count($_rankKeys) - 2];
}

// Fortnite divisions climb I → III within a tier (opposite of the LoL-style III → I
// used by the shared roman-numeral fallback below).
if ($_gameSlug === 'fortnite' && !isset($_jsonData['division_names'])) {
    $_jsonData['division_names'] = [1 => 'I', 2 => 'II', 3 => 'III'];
}

// Current Rank can be capped below Desired Rank's top tier (e.g. you can't currently
// "be" at a leaderboard-only rank you'd be boosting into) via start_max_tier.
$_startMaxTier = isset($_jsonData['start_max_tier']) && is_numeric($_jsonData['start_max_tier']) ? (int)$_jsonData['start_max_tier'] : null;
$_startRankKeys = $_startMaxTier !== null
    ? array_values(array_filter($_rankKeys, function ($t) use ($_startMaxTier) { return $t <= $_startMaxTier; }))
    : $_rankKeys;
if (empty($_startRankKeys)) $_startRankKeys = $_rankKeys;

$_numRanks = count($_rankKeys);
$_configuredDefaultFrom = $_jsonData['default_start_tier'] ?? null;
$_configuredDefaultIndex = $_configuredDefaultFrom !== null
    ? array_search((int)$_configuredDefaultFrom, $_rankKeys, true)
    : false;
$_defaultFromIndex = $_configuredDefaultIndex !== false
    ? (int)$_configuredDefaultIndex
    : max(0, min($_numRanks - 1, (int)floor($_numRanks * 0.25)));
$_defaultFrom = $_rankKeys[$_defaultFromIndex] ?? 1;
$_defaultTo = $_rankKeys[min($_numRanks - 1, $_defaultFromIndex + 1)] ?? $_defaultFrom;
if ($_defaultTo === $_defaultFrom && $_numRanks > 1) $_defaultTo = $_rankKeys[min($_numRanks - 1, $_defaultFromIndex + 1)];

$_globalDivisionCount = lb_dynamic_division_count($_jsonData, 4);
$_rankDivCounts = [];
foreach ($_rankKeys as $_tier) {
    $_rankDivCounts[(string)$_tier] = lb_dynamic_rank_division_count($_jsonData, (int)$_tier, $_globalDivisionCount);
}
$_maxDivisionCount = !empty($_rankDivCounts) ? max(array_map('intval', array_values($_rankDivCounts))) : $_globalDivisionCount;
$_hasAnyDivisions = $_maxDivisionCount > 0;
$_defaultFromDivCount = (int)($_rankDivCounts[(string)$_defaultFrom] ?? $_globalDivisionCount);
$_defaultToDivCount = (int)($_rankDivCounts[(string)$_defaultTo] ?? $_globalDivisionCount);
$_startDivisionDefault = $_defaultFromDivCount > 0 ? $_defaultFromDivCount : 0;
$_endDivisionDefault = $_defaultToDivCount > 0 ? 1 : 0;

// Per-game field defaults for newer generic-form games, applied only when the
// admin hasn't configured the corresponding field in $_jsonData. Keeps the
// options-bar matching each game's real server/platform/queue conventions
// instead of the generic LoL-flavored fallback (EU-West/NA, Ranked Solo/Duo, ...).
$_gameFieldDefaults = [
    'marvel-rivals' => [
        'points_label' => 'Points',
        'points_options' => ['0-20' => '0-20 Points', '21-40' => '21-40 Points', '41-60' => '41-60 Points', '61-80' => '61-80 Points', '81-100' => '81-100 Points'],
        'server_options' => ['North America' => 'North America', 'Europe' => 'Europe', 'Asia' => 'Asia', 'Middle East' => 'Middle East', 'Brazil' => 'Brazil'],
        'server_default' => 'Europe',
        'platform_options' => ['PC' => 'PC', 'PlayStation' => 'PlayStation', 'Xbox' => 'Xbox'],
        'show_platform' => true,
        'show_lp_gain' => false,
        'show_queue_type' => false,
    ],
    'rocket-league' => [
        'show_points' => false,
        'show_lp_gain' => false,
        'server_options' => ['North America' => 'North America', 'Europe' => 'Europe', 'Asia Pacific' => 'Asia Pacific', 'Middle East' => 'Middle East', 'Oceania' => 'Oceania', 'Japan' => 'Japan', 'Brazil' => 'Brazil'],
        'server_default' => 'Europe',
        'platform_options' => ['PC' => 'PC', 'PSN' => 'PSN', 'XBOX' => 'XBOX'],
        'show_platform' => true,
        'queue_type_label' => 'Queue Type',
        'queue_type_options' => ['1v1 (Solo Duels)' => '1v1 (Solo Duels)', '2v2 (Doubles)' => '2v2 (Doubles)', '3v3 (Standard)' => '3v3 (Standard)', 'Hoops' => 'Hoops', 'Rumble' => 'Rumble', 'Dropshot' => 'Dropshot', 'Snowday' => 'Snowday'],
        'queue_type_default' => '3v3 (Standard)',
    ],
    'overwatch-2' => [
        'show_points' => false,
        'show_lp_gain' => false,
        'server_options' => ['North America' => 'North America', 'Europe' => 'Europe', 'Brazil' => 'Brazil', 'Asia Pacific' => 'Asia Pacific'],
        'server_default' => 'Europe',
        'platform_options' => ['PC' => 'PC', 'PlayStation' => 'PlayStation', 'Xbox' => 'Xbox', 'Switch' => 'Switch'],
        'show_platform' => true,
        'queue_type_label' => 'Role',
        'queue_type_options' => ['Tank' => 'Tank', 'Damage' => 'Damage', 'Support' => 'Support'],
        'queue_type_default' => 'Damage',
    ],
    'apex-legends' => [
        'points_label' => 'RP',
        'points_options' => ['4800-4920' => '4800-4920 RP', '4921-5040' => '4921-5040 RP', '5041-5160' => '5041-5160 RP', '5161-5280' => '5161-5280 RP', '5281-5400' => '5281-5400 RP'],
        'show_lp_gain' => false,
        'server_options' => ['North America' => 'North America', 'Europe' => 'Europe', 'Brazil' => 'Brazil', 'Asia Pacific' => 'Asia Pacific', 'Oceania' => 'Oceania'],
        'server_default' => 'Europe',
        'platform_options' => ['PC' => 'PC', 'PSN' => 'PSN', 'XBOX' => 'XBOX'],
        'show_platform' => true,
        'show_queue_type' => false,
    ],
    'lol-wild-rift' => [
        'show_points' => false,
        'show_lp_gain' => false,
        'server_options' => ['North America' => 'North America', 'Europe' => 'Europe', 'Brazil' => 'Brazil', 'Asia Pacific' => 'Asia Pacific', 'Middle East' => 'Middle East'],
        'server_default' => 'Europe',
        'show_platform' => false,
        'queue_type_label' => 'Ranked Marks',
        'queue_type_options' => ['None' => 'None', '1' => '1 Mark', '2' => '2 Marks', '3' => '3 Marks', '4' => '4 Marks', '5' => '5 Marks', '6' => '6 Marks'],
        'queue_type_default' => 'None',
    ],
    'fortnite' => [
        'show_points' => false,
        'show_lp_gain' => false,
        'server_options' => ['North America' => 'North America', 'Europe' => 'Europe', 'Asia' => 'Asia', 'Oceania' => 'Oceania', 'Brazil' => 'Brazil', 'Middle East' => 'Middle East'],
        'server_default' => 'Europe',
        'platform_options' => ['PC' => 'PC', 'PlayStation' => 'PlayStation', 'Xbox' => 'Xbox', 'Switch' => 'Switch'],
        'show_platform' => true,
        'queue_type_label' => 'Mode',
        'queue_type_options' => ['Battle Royale' => 'Battle Royale', 'Zero Build' => 'Zero Build'],
        'queue_type_default' => 'Battle Royale',
    ],
    'counter-strike-2' => [
        'points_label' => 'Premier Rating',
        'show_points' => true,
        'show_lp_gain' => false,
        'show_server' => false,
        'server_options' => ['eu' => 'Europe', 'na' => 'North America'],
        'server_default' => 'eu',
        'show_platform' => false,
        'show_queue_type' => false,
    ],
];
// $_gameSlug can come from either the boost form's own "game" column or the routing
// slug, and those two aren't always the same string for these games (e.g. "rivals" vs
// "marvel-rivals") — normalize before looking up the per-game field config so the
// lookup doesn't silently miss and fall back to "show everything".
$_gameFieldAliases = [
    'rivals' => 'marvel-rivals', 'marvel-rival' => 'marvel-rivals', 'marvel_rivals' => 'marvel-rivals',
    'rl' => 'rocket-league', 'rocket_league' => 'rocket-league',
    'apex' => 'apex-legends', 'apex_legends' => 'apex-legends',
    'ow2' => 'overwatch-2', 'ow' => 'overwatch-2', 'overwatch' => 'overwatch-2', 'overwatch_2' => 'overwatch-2',
    'wild-rift' => 'lol-wild-rift', 'wild_rift' => 'lol-wild-rift', 'wildrift' => 'lol-wild-rift',
];
$_gameFieldKey = $_gameFieldAliases[strtolower(trim((string)$_gameSlug))] ?? $_gameSlug;
$_gameFieldOverride = $_gameFieldDefaults[$_gameFieldKey] ?? [];
if ($_gameFieldKey === 'lol-wild-rift' && $_isWinForm) {
    // Ranked Marks only applies to rank boosting. Win Boost needs Server only.
    $_gameFieldOverride['show_queue_type'] = false;
}
// Wingman Boost shares the "counter-strike-2" game slug with Premier Rank Boost but
// uses the classic Silver-to-SMFC ladder (no RP/points) plus a Map Pool picker instead
// of Premier's Current Rating field — override the shared CS2 field config for this form only.
$_isWingmanForm = ($_gameFieldKey === 'counter-strike-2' && strpos($_formSlug, 'wingman') !== false);
if ($_isWingmanForm) {
    $_gameFieldOverride['show_points'] = false;
    $_gameFieldOverride['show_lp_gain'] = false;
    $_gameFieldOverride['show_server'] = false;
    $_gameFieldOverride['show_platform'] = false;
    $_gameFieldOverride['show_queue_type'] = true;
    $_gameFieldOverride['queue_type_label'] = 'Map';
    $_gameFieldOverride['queue_type_options'] = ['Overpass' => 'Overpass', 'Vertigo' => 'Vertigo', 'Nuke' => 'Nuke', 'Inferno' => 'Inferno'];
    $_gameFieldOverride['queue_type_default'] = 'Vertigo';
}

// Games we have an explicit field config for (the 5 newer titles) are fully authoritative —
// their options-bar is driven by $_gameFieldOverride only, ignoring whatever legacy/placeholder
// values happen to be sitting in $_jsonData from when the form was first created. Games not in
// the table keep the original admin-config-first behavior untouched.
$_hasGameFieldOverride = !empty($_gameFieldOverride);

$_pointsLabel = $_hasGameFieldOverride
    ? ($_gameFieldOverride['points_label'] ?? 'Points')
    : ($_jsonData['points_label'] ?? $_jsonData['point_label'] ?? 'LP');
if ($_hasGameFieldOverride) {
    $_pointsOptions = $_gameFieldOverride['points_options'] ?? ['0-20' => '0-20 ' . $_pointsLabel, '21-40' => '21-40 ' . $_pointsLabel, '41-60' => '41-60 ' . $_pointsLabel, '61-80' => '61-80 ' . $_pointsLabel, '81-100' => '81-100 ' . $_pointsLabel];
} else {
    $_pointsOptions = $_jsonData['points_options'] ?? $_jsonData['lp_options'] ?? $_jsonData['start_lp_options'] ?? [];
    if (!is_array($_pointsOptions) || empty($_pointsOptions)) {
        $_pointsOptions = ['0-20' => '0-20 ' . $_pointsLabel, '21-40' => '21-40 ' . $_pointsLabel, '41-60' => '41-60 ' . $_pointsLabel, '61-80' => '61-80 ' . $_pointsLabel, '81-100' => '81-100 ' . $_pointsLabel];
    }
}
$_showPoints = array_key_exists('show_points', $_gameFieldOverride) ? (bool)$_gameFieldOverride['show_points'] : true;

$_pointStep = (int)($_jsonData['points_step'] ?? $_jsonData['point_step'] ?? $_jsonData['lp_step'] ?? 25);
if ($_pointStep <= 0) $_pointStep = 25;
$_pointMin = (int)($_jsonData['points_min'] ?? $_jsonData['point_min'] ?? $_jsonData['lp_min'] ?? 0);
$_pointMax = (int)($_jsonData['points_max'] ?? $_jsonData['point_max'] ?? $_jsonData['lp_max'] ?? 9999);
if ($_pointMax <= $_pointMin) $_pointMax = $_pointMin + 9999;

$_lpGainOptions = $_jsonData['lp_gain_options'] ?? $_jsonData['points_gain_options'] ?? [];
if (!is_array($_lpGainOptions) || empty($_lpGainOptions)) {
    $_lpGainOptions = ['30+' => '30+ ' . $_pointsLabel . ' / Win', '25-29' => '25-29 ' . $_pointsLabel . ' / Win', '20-24' => '20-24 ' . $_pointsLabel . ' / Win', '10-19' => '10-19 ' . $_pointsLabel . ' / Win'];
}
$_showLpGain = array_key_exists('show_lp_gain', $_gameFieldOverride) ? (bool)$_gameFieldOverride['show_lp_gain'] : true;

if ($_hasGameFieldOverride) {
    $_serverOptions = $_gameFieldOverride['server_options'] ?? ['euw' => 'EU-West', 'na' => 'North America'];
} else {
    $_serverOptions = $_jsonData['server_options'] ?? $_jsonData['servers'] ?? [];
    if (!is_array($_serverOptions) || empty($_serverOptions)) {
        $_serverOptions = ['euw' => 'EU-West', 'na' => 'North America'];
    }
}
$_serverDefault = $_gameFieldOverride['server_default'] ?? $_jsonData['server_default'] ?? null;
$_showServer = array_key_exists('show_server', $_gameFieldOverride) ? (bool)$_gameFieldOverride['show_server'] : true;

$_showPlatform = array_key_exists('show_platform', $_gameFieldOverride)
    ? (bool)$_gameFieldOverride['show_platform']
    : (!empty($_jsonData['form_config']['show_platform']) || !empty($_jsonData['platform_options']) || !empty($_jsonData['platforms']));
if ($_hasGameFieldOverride) {
    $_platformOptions = $_gameFieldOverride['platform_options'] ?? ['pc' => 'PC', 'playstation' => 'PlayStation', 'xbox' => 'Xbox'];
} else {
    $_platformOptions = $_jsonData['platform_options'] ?? $_jsonData['platforms'] ?? [];
    if (!is_array($_platformOptions)) $_platformOptions = [];
    if ($_showPlatform && empty($_platformOptions)) {
        $_platformOptions = ['pc' => 'PC', 'playstation' => 'PlayStation', 'xbox' => 'Xbox'];
    }
}

if ($_hasGameFieldOverride) {
    $_queueOptions = $_gameFieldOverride['queue_type_options'] ?? ['solo_/_duo' => 'Ranked Solo/Duo'];
} else {
    $_queueOptions = $_jsonData['queue_type_options'] ?? $_jsonData['queue_types'] ?? [];
    if (!is_array($_queueOptions) || empty($_queueOptions)) {
        $_queueOptions = ['solo_/_duo' => 'Ranked Solo/Duo'];
    }
}
$_showQueueType = array_key_exists('show_queue_type', $_gameFieldOverride) ? (bool)$_gameFieldOverride['show_queue_type'] : true;
$_queueTypeLabel = $_gameFieldOverride['queue_type_label'] ?? $_jsonData['queue_type_label'] ?? 'Queue Type';
$_queueTypeDefault = $_gameFieldOverride['queue_type_default'] ?? $_jsonData['queue_type_default'] ?? null;

$_rankIconMap = [];
$_divisionLabelMap = [];
foreach ($_rankKeys as $_key) {
    $_rankIconMap[(string)$_key] = lb_dynamic_rank_icon_url($_jsonData, $_gameShort, 'mini', (int)$_key);
    $_tierDivisionCount = (int)($_rankDivCounts[(int)$_key] ?? 0);
    for ($_divisionValue = 1; $_divisionValue <= $_tierDivisionCount; $_divisionValue++) {
        $_divisionLabelMap[(string)$_key][(string)$_divisionValue] = lb_dynamic_division_label($_divisionValue, $_tierDivisionCount, $_jsonData);
    }
}
// Wingman's 17-rank ladder is too many icons for the classic grid picker — use a
// compact custom dropdown (big current icon + searchless select) instead. The grid
// markup still renders (hidden) underneath so every existing sync/pricing script
// keeps working unmodified against the same start_tier/end_tier radio inputs.
$_useRankDropdown = $_isWingmanForm;
?>
<?php if ($_useRankDropdown): ?>
<style>
.rank-dropdown-hero { display:flex; align-items:center; justify-content:center; padding:8px 0 18px; }
.rank-dropdown-hero-icon { width:128px; height:128px; object-fit:contain; filter:drop-shadow(0 10px 20px rgba(0,0,0,.4)); }
.rank-dropdown-picker { position:relative; }
.rank-dropdown-toggle {
    width:100%; display:flex; align-items:center; gap:12px;
    background:rgba(124,105,245,.10); border:1.5px solid rgba(124,105,245,.35);
    border-radius:12px; padding:11px 16px; cursor:pointer; font-family:inherit;
    transition:border-color .15s, background .15s;
}
.rank-dropdown-toggle:hover { border-color:rgba(124,105,245,.6); background:rgba(124,105,245,.14); }
.rank-dropdown-picker.open .rank-dropdown-toggle { border-color:#7c69f5; background:rgba(124,105,245,.16); }
.rank-dropdown-name { flex:1; text-align:left; font-size:14.5px; font-weight:700; color:#e9e6ff; }
.rank-dropdown-caret { color:#f4c542; font-size:13px; transition:transform .15s; }
.rank-dropdown-picker.open .rank-dropdown-caret { transform:rotate(180deg); }
.rank-dropdown-list {
    position:absolute; left:0; right:0; top:calc(100% + 8px); z-index:20;
    max-height:320px; overflow-y:auto; padding:6px;
    background:#181a2b; border:1px solid rgba(124,105,245,.28); border-radius:14px;
    box-shadow:0 18px 45px rgba(0,0,0,.35);
}
.rank-dropdown-item {
    display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:10px;
    cursor:pointer; transition:background .12s;
}
.rank-dropdown-item:hover { background:rgba(124,105,245,.14); }
.rank-dropdown-item.active { background:rgba(124,105,245,.22); }
.rank-dropdown-item.disabled { opacity:.35; pointer-events:none; }
.rank-dropdown-item img { width:26px; height:26px; object-fit:contain; flex-shrink:0; }
.rank-dropdown-item span { font-size:13.5px; font-weight:700; color:#e2e8f0; }
.rank-dropdown-hero ~ .dynamic-ranks, .rank-dropdown-picker ~ .dynamic-ranks {
    position:absolute !important; width:1px !important; height:1px !important;
    overflow:hidden !important; clip:rect(0,0,0,0) !important; white-space:nowrap !important;
    border:0 !important; padding:0 !important; margin:-1px !important;
}
</style>
<?php endif; ?>
<div class="rank-boost rank-boost-dynamic <?= $_isGamesForm ? 'win-boost' : '' ?>" data-dynamic-rank-form="1" data-game="<?= htmlspecialchars((string)$_gameFieldKey) ?>" data-boost-mode="<?= $_isWinForm ? 'win' : ($_isPlacementForm ? 'placement' : 'rank') ?>" data-new-account="<?= ($_gameFieldKey === 'counter-strike-2' && $_isPlacementForm && $_defaultFrom === 0) ? '1' : '0' ?>">
    <div class="rank-cards">
        <div class="card">
            <div class="card-header">
                <img src="<?= htmlspecialchars($_rankIconMap[(string)$_defaultFrom] ?? '') ?>" alt="rank_icon" class="card-header-rank current-rank-img" onerror="this.style.display='none'"<?= ($_gameFieldKey === 'counter-strike-2' && $_isPlacementForm && $_defaultFrom === 0) ? ' style="display:none"' : '' ?>>
                <?php if ($_gameFieldKey === 'counter-strike-2' && $_isPlacementForm): ?>
                <i class="fa-solid fa-circle-question current-new-account-icon" aria-label="New Account" style="<?= $_defaultFrom === 0 ? '' : 'display:none' ?>"></i>
                <?php endif; ?>
                <div class="text">
                    <h3><?= $_isPlacementForm ? t('Last Season Rank') : t('Current Rank') ?></h3>
                    <p><?= $_isPlacementForm ? t('Select your last season tier and division.') : t('Select your current tier and division.') ?></p>
                </div>
            </div>
            <div class="card-body">
                <?php if ($_useRankDropdown): ?>
                <div class="rank-dropdown-hero">
                    <img class="rank-dropdown-hero-icon" data-dropdown-hero="start" src="<?= htmlspecialchars($_rankIconMap[(string)$_defaultFrom] ?? '') ?>" alt="" onerror="this.style.display='none'">
                </div>
                <div class="rank-dropdown-picker" data-dynamic-dropdown="start">
                    <button type="button" class="rank-dropdown-toggle" data-dropdown-toggle>
                        <span class="rank-dropdown-name"><?= htmlspecialchars($ranks[$_defaultFrom] ?? ('Tier ' . $_defaultFrom)) ?></span>
                        <i class="fa-solid fa-chevron-down rank-dropdown-caret"></i>
                    </button>
                    <div class="rank-dropdown-list" hidden>
                        <?php foreach ($_startRankKeys as $_tier): ?>
                        <div class="rank-dropdown-item<?= $_tier === $_defaultFrom ? ' active' : '' ?>" data-tier="<?= (int)$_tier ?>">
                            <img src="<?= htmlspecialchars($_rankIconMap[(string)$_tier] ?? '') ?>" alt="" onerror="this.style.display='none'">
                            <span><?= htmlspecialchars($ranks[$_tier] ?? ('Tier ' . $_tier)) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="ranks dynamic-ranks" data-dynamic-ranks="start" <?= $_useRankDropdown ? 'aria-hidden="true" style="position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important;padding:0!important;margin:-1px!important;"' : '' ?>>
                    <?php foreach ($_startRankKeys as $_tier): ?>
                        <label>
                            <input type="radio" name="start_tier" id="start_<?= (int)$_tier ?>" value="<?= (int)$_tier ?>" class="custom-checkbox" <?= $_tier === $_defaultFrom ? 'checked' : '' ?>>
                            <div class="rank-btn">
                                <?php if ($_gameFieldKey === 'counter-strike-2' && $_isPlacementForm && $_tier === 0): ?>
                                <i class="fa-solid fa-circle-question dynamic-new-account-icon" aria-label="New Account"></i>
                                <?php else: ?>
                                <img src="<?= htmlspecialchars($_rankIconMap[(string)$_tier] ?? '') ?>" alt="<?= htmlspecialchars($ranks[$_tier] ?? ('Tier ' . $_tier)) ?>" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <span class="tooltip"><?= htmlspecialchars($ranks[$_tier] ?? ('Tier ' . $_tier)) ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if ($_hasAnyDivisions): ?>
                    <hr class="dynamic-division-separator" id="start_divisions_hr">
                    <div class="divisions" id="start_divisions">
                        <?php for ($_div = 1; $_div <= $_maxDivisionCount; $_div++): ?>
                            <label data-dynamic-division-label="<?= $_div ?>">
                                <input type="radio" name="start_division" id="start_div_<?= $_div ?>" value="<?= $_div ?>" class="custom-checkbox" <?= $_div === $_startDivisionDefault ? 'checked' : '' ?>>
                                <div class="division-btn"><?= htmlspecialchars(lb_dynamic_division_label($_div, $_maxDivisionCount, $_jsonData)) ?></div>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="start_division" value="0" data-dynamic-no-division="start" disabled>
                <?php else: ?>
                    <input type="hidden" name="start_division" value="0">
                <?php endif; ?>
                <div class="lp-selector dynamic-points-selector" id="start_points_full" style="display:none" data-dynamic-points="start">
                    <h6><?= htmlspecialchars($_pointsLabel === 'LP' ? t('Current LP:') : t('Current') . ' ' . $_pointsLabel . ':') ?></h6>
                    <div class="input-container">
                        <button type="button" data-dynamic-point-step="start" data-delta="-1"><i class="fas fa-circle-minus"></i></button>
                        <input type="text" name="start_lp_full" id="dynamic_start_points_input" value="0" inputmode="numeric" min="<?= (int)$_pointMin ?>" max="<?= (int)$_pointMax ?>" data-point-step="<?= (int)$_pointStep ?>">
                        <button type="button" data-dynamic-point-step="start" data-delta="1"><i class="fas fa-circle-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$_isGamesForm): ?>
        <div class="card">
            <div class="card-header">
                <img src="<?= htmlspecialchars($_rankIconMap[(string)$_defaultTo] ?? '') ?>" alt="rank_icon" class="card-header-rank desired-rank-img" onerror="this.style.display='none'">
                <div class="text">
                    <h3><?= t('Desired Rank') ?></h3>
                    <p><?= t('Select your desired tier and division.') ?></p>
                </div>
            </div>
            <div class="card-body">
                <?php if ($_useRankDropdown): ?>
                <div class="rank-dropdown-hero">
                    <img class="rank-dropdown-hero-icon" data-dropdown-hero="end" src="<?= htmlspecialchars($_rankIconMap[(string)$_defaultTo] ?? '') ?>" alt="" onerror="this.style.display='none'">
                </div>
                <div class="rank-dropdown-picker" data-dynamic-dropdown="end">
                    <button type="button" class="rank-dropdown-toggle" data-dropdown-toggle>
                        <span class="rank-dropdown-name"><?= htmlspecialchars($ranks[$_defaultTo] ?? ('Tier ' . $_defaultTo)) ?></span>
                        <i class="fa-solid fa-chevron-down rank-dropdown-caret"></i>
                    </button>
                    <div class="rank-dropdown-list" hidden>
                        <?php foreach ($_rankKeys as $_tier): ?>
                        <div class="rank-dropdown-item<?= $_tier === $_defaultTo ? ' active' : '' ?>" data-tier="<?= (int)$_tier ?>">
                            <img src="<?= htmlspecialchars($_rankIconMap[(string)$_tier] ?? '') ?>" alt="" onerror="this.style.display='none'">
                            <span><?= htmlspecialchars($ranks[$_tier] ?? ('Tier ' . $_tier)) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="ranks dynamic-ranks" data-dynamic-ranks="end" <?= $_useRankDropdown ? 'aria-hidden="true" style="position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important;padding:0!important;margin:-1px!important;"' : '' ?>>
                    <?php foreach ($_rankKeys as $_tier): ?>
                        <label>
                            <input type="radio" name="end_tier" id="end_<?= (int)$_tier ?>" value="<?= (int)$_tier ?>" class="custom-checkbox" <?= $_tier === $_defaultTo ? 'checked' : '' ?>>
                            <div class="rank-btn">
                                <img src="<?= htmlspecialchars($_rankIconMap[(string)$_tier] ?? '') ?>" alt="<?= htmlspecialchars($ranks[$_tier] ?? ('Tier ' . $_tier)) ?>" onerror="this.style.display='none'">
                                <span class="tooltip"><?= htmlspecialchars($ranks[$_tier] ?? ('Tier ' . $_tier)) ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if ($_hasAnyDivisions): ?>
                    <hr class="dynamic-division-separator" id="end_divisions_hr">
                    <div class="divisions" id="end_divisions">
                        <?php for ($_div = 1; $_div <= $_maxDivisionCount; $_div++): ?>
                            <label data-dynamic-division-label="<?= $_div ?>">
                                <input type="radio" name="end_division" id="end_div_<?= $_div ?>" value="<?= $_div ?>" class="custom-checkbox" <?= $_div === $_endDivisionDefault ? 'checked' : '' ?>>
                                <div class="division-btn"><?= htmlspecialchars(lb_dynamic_division_label($_div, $_maxDivisionCount, $_jsonData)) ?></div>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="end_division" value="0" data-dynamic-no-division="end" disabled>
                <?php else: ?>
                    <input type="hidden" name="end_division" value="0">
                <?php endif; ?>
                <div class="lp-selector dynamic-points-selector" id="end_points_full" style="display:none" data-dynamic-points="end">
                    <h6><?= htmlspecialchars($_pointsLabel === 'LP' ? t('Desired LP:') : t('Desired') . ' ' . $_pointsLabel . ':') ?></h6>
                    <div class="input-container">
                        <button type="button" data-dynamic-point-step="end" data-delta="-1"><i class="fas fa-circle-minus"></i></button>
                        <input type="text" name="end_lp_full" id="dynamic_end_points_input" value="0" inputmode="numeric" min="<?= (int)$_pointMin ?>" max="<?= (int)$_pointMax ?>" data-point-step="<?= (int)$_pointStep ?>">
                        <button type="button" data-dynamic-point-step="end" data-delta="1"><i class="fas fa-circle-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($_isGamesForm): ?>
    <div class="card count-card dynamic-games-card">
        <div class="card-header">
            <div class="count win-count" id="dynamic_games_count"><?= (int)$_matchesDefault ?></div>
            <div class="text">
                <h3><?= $_isWinForm ? t('Wins Amount') : t('Games Amount') ?></h3>
                <p><?= $_isWinForm ? t('Select your desired amount of wins.') : t('Select your desired amount of games.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="dynamic_matches_slider"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" id="dynamic_matches_input" value="<?= (int)$_matchesDefault ?>" min="1" max="<?= (int)$_matchesMax ?>" hidden>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($_gameFieldKey !== 'counter-strike-2' || $_isWingmanForm): ?>
    <div class="options-bar">
        <?php if ($_showPoints): ?>
        <div class="option" id="start-lp-option">
            <h6><?= htmlspecialchars($_pointsLabel === 'LP' ? t('Current LP') : t('Current') . ' ' . $_pointsLabel) ?></h6>
            <select class="select2" name="start_lp" data-no-search="true">
                <?php foreach ($_pointsOptions as $_value => $_label): ?>
                    <?php if (is_array($_label)) { $_value = $_label['value'] ?? $_value; $_label = $_label['label'] ?? $_value; } ?>
                    <option value="<?= htmlspecialchars((string)$_value) ?>"><?= htmlspecialchars((string)$_label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if ($_showLpGain): ?>
        <div class="option">
            <h6><?= htmlspecialchars($_pointsLabel === 'LP' ? t('LP Gain') : $_pointsLabel . ' ' . t('Gain')) ?></h6>
            <select class="select2" name="lp_gain" data-no-search="true">
                <?php $_firstGain = true; foreach ($_lpGainOptions as $_value => $_label): ?>
                    <?php if (is_array($_label)) { $_value = $_label['value'] ?? $_value; $_label = $_label['label'] ?? $_value; } ?>
                    <option value="<?= htmlspecialchars((string)$_value) ?>" ><?= htmlspecialchars((string)$_label) ?></option>
                <?php $_firstGain = false; endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if ($_showServer): ?>
        <div class="option">
            <h6><?= t('Server') ?></h6>
            <select class="select2" name="server" data-no-search="true">
                <?php $_firstServer = true; foreach ($_serverOptions as $_value => $_label): ?>
                    <?php if (is_array($_label)) { $_value = $_label['value'] ?? $_value; $_label = $_label['label'] ?? $_value; } ?>
                    <?php $_serverSelected = $_serverDefault !== null ? ((string)$_value === (string)$_serverDefault) : $_firstServer; ?>
                    <option value="<?= htmlspecialchars((string)$_value) ?>" <?= $_serverSelected ? 'selected' : '' ?>><?= htmlspecialchars((string)$_label) ?></option>
                <?php $_firstServer = false; endforeach; ?>
            </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="server" value="<?= htmlspecialchars((string)($_serverDefault ?? array_key_first($_serverOptions) ?? 'eu')) ?>">
        <?php endif; ?>
        <?php if ($_showPlatform): ?>
        <div class="option">
            <h6><?= t('Platform') ?></h6>
            <select class="select2" name="platform" data-no-search="true">
                <?php $_firstPlatform = true; foreach ($_platformOptions as $_value => $_label): ?>
                    <?php if (is_array($_label)) { $_value = $_label['value'] ?? $_value; $_label = $_label['label'] ?? $_value; } ?>
                    <option value="<?= htmlspecialchars((string)$_value) ?>" <?= $_firstPlatform ? 'selected' : '' ?>><?= htmlspecialchars((string)$_label) ?></option>
                <?php $_firstPlatform = false; endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if ($_showQueueType): ?>
        <div class="option">
            <h6><?= htmlspecialchars(t($_queueTypeLabel)) ?></h6>
            <select class="select2" name="queue_type" data-no-search="true">
                <?php $_firstQueue = true; foreach ($_queueOptions as $_value => $_label): ?>
                    <?php if (is_array($_label)) { $_value = $_label['value'] ?? $_value; $_label = $_label['label'] ?? $_value; } ?>
                    <?php $_queueSelected = $_queueTypeDefault !== null ? ((string)$_value === (string)$_queueTypeDefault) : $_firstQueue; ?>
                    <option value="<?= htmlspecialchars((string)$_value) ?>" <?= $_queueSelected ? 'selected' : '' ?>><?= htmlspecialchars((string)$_label) ?></option>
                <?php $_firstQueue = false; endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <input type="hidden" name="server" value="<?= htmlspecialchars((string)($_serverDefault ?? array_key_first($_serverOptions) ?? 'eu')) ?>">
    <?php endif; ?>
</div>

<script>
(function () {
    var rankMap = <?= json_encode($ranks, JSON_UNESCAPED_UNICODE) ?>;
    var iconMap = <?= json_encode($_rankIconMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    var divisionLabelMap = <?= json_encode($_divisionLabelMap, JSON_UNESCAPED_UNICODE) ?>;
    var rankKeys = <?= json_encode(array_values(array_map('strval', $_rankKeys))) ?>;
    var rankDivCounts = <?= json_encode($_rankDivCounts, JSON_UNESCAPED_UNICODE) ?>;
    var hasAnyDivisions = <?= $_hasAnyDivisions ? 'true' : 'false' ?>;
    var defaultEndTier = <?= json_encode((string)$_defaultTo) ?>;
    var pointMin = <?= (int)$_pointMin ?>;
    var pointMax = <?= (int)$_pointMax ?>;
    var pointStep = <?= (int)$_pointStep ?>;
    var pointsLabel = <?= json_encode((string)$_pointsLabel, JSON_UNESCAPED_UNICODE) ?>;
    var ratingOnly = <?= (!empty($_jsonData['rating_only']) || !empty($_jsonData['form_config']['rating_only'])) ? 'true' : 'false' ?>;
    var showPoints = <?= $_showPoints ? 'true' : 'false' ?>;
    var pointsMinStart = <?= json_encode($_jsonData['points_min_start'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var pointsMinEnd = <?= json_encode($_jsonData['points_min_end'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var pointsMaxStart = <?= json_encode($_jsonData['points_max_start'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var pointsMaxEnd = <?= json_encode($_jsonData['points_max_end'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var flatTiers = <?= json_encode(array_map('strval', $_jsonData['flat_tiers'] ?? []), JSON_UNESCAPED_UNICODE) ?>;
    var isGamesForm = <?= $_isGamesForm ? 'true' : 'false' ?>;
    var isPointProgressionForm = <?= (in_array($_gameFieldKey, ['apex-legends', 'counter-strike-2'], true) && !$_isWingmanForm) ? 'true' : 'false' ?>;
    function isFlatTier(tier) { return flatTiers.indexOf(String(tier)) !== -1; }

    function q(sel) { return document.querySelector(sel); }
    function qa(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); }
    function checkedValue(name, fallback) {
        var input = q('input[name="' + name + '"]:checked');
        return input ? input.value : fallback;
    }
    function tierDivCount(value) {
        var key = String(value);
        var count = parseInt(rankDivCounts[key] !== undefined ? rankDivCounts[key] : rankDivCounts[parseInt(value, 10)], 10);
        return isNaN(count) ? 0 : count;
    }
    function rankName(value) { return rankMap[value] || rankMap[parseInt(value, 10)] || ('Tier ' + value); }
    function rankIcon(value) { return iconMap[value] || iconMap[parseInt(value, 10)] || ''; }
    function rankNameWithDivision(prefix, tier) {
        var name = rankName(tier);
        if (tierDivCount(tier) <= 0) return name;
        var divisionValue = String(checkedValue(prefix + '_division', '0'));
        var tierLabels = divisionLabelMap[String(tier)] || divisionLabelMap[parseInt(tier, 10)] || {};
        var division = tierLabels[divisionValue] || tierLabels[parseInt(divisionValue, 10)] || '';
        return division ? name + ' ' + division : name;
    }
    function setDisabled(input, disabled) {
        input.disabled = !!disabled;
        var label = input.closest('label');
        if (label) {
            label.style.pointerEvents = disabled ? 'none' : '';
            label.style.opacity = disabled ? '0.42' : '';
        }
    }
    function setImage(selector, tier) {
        qa(selector).forEach(function(img) {
            var src = rankIcon(tier);
            if (!src) return;
            img.style.display = '';
            if (img.getAttribute('src') !== src) img.setAttribute('src', src);
        });
    }
    function triggerChange(el) {
        if (!el) return;
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function pointSelector(prefix) {
        return q('#' + prefix + '_points_full');
    }
    function pointInput(prefix) {
        return q(prefix === 'start' ? '#dynamic_start_points_input' : '#dynamic_end_points_input');
    }
    function formattedPoints(value) {
        var number = parseInt(value || '0', 10);
        return (isNaN(number) ? 0 : number).toLocaleString('en-US') + ' ' + pointsLabel;
    }
    function tierForPoint(prefix, value) {
        var minMap = prefix === 'start' ? pointsMinStart : pointsMinEnd;
        var maxMap = prefix === 'start' ? pointsMaxStart : pointsMaxEnd;
        var point = parseInt(value, 10);
        if (isNaN(point)) return null;
        for (var i = 0; i < rankKeys.length; i++) {
            var key = String(rankKeys[i]);
            var min = minMap[key] !== undefined ? parseInt(minMap[key], 10) : pointMin;
            var max = maxMap[key] !== undefined ? parseInt(maxMap[key], 10) : pointMax;
            if (point >= min && point <= max) return key;
        }
        return null;
    }
    function syncTierFromPoint(prefix, input) {
        if (!ratingOnly || !input) return;
        var tier = tierForPoint(prefix, input.value);
        var radio = tier ? q('input[name="' + prefix + '_tier"][value="' + tier + '"]') : null;
        if (radio && !radio.disabled) radio.checked = true;
    }
    function normalizePointInput(input) {
        if (!input) return;
        var min = parseInt(input.getAttribute('min') || pointMin, 10);
        var max = parseInt(input.getAttribute('max') || pointMax, 10);
        var value = parseInt(String(input.value || '0').replace(/[^0-9]/g, ''), 10);
        if (isNaN(value)) value = min;
        value = Math.max(min, Math.min(max, value));
        input.value = value;
    }
    function changePoint(prefix, direction) {
        var input = pointInput(prefix);
        if (!input) return;
        normalizePointInput(input);
        var min = parseInt(input.getAttribute('min') || pointMin, 10);
        var max = parseInt(input.getAttribute('max') || pointMax, 10);
        var step = parseInt(input.getAttribute('data-point-step') || pointStep, 10);
        if (isNaN(step) || step <= 0) step = pointStep || 25;
        var current = parseInt(input.value || min, 10);
        input.value = Math.max(min, Math.min(max, current + (direction * step)));
        triggerChange(input);
    }

    function syncDivisionBlock(prefix, tier) {
        if (!hasAnyDivisions) {
            var pointsOnly = pointSelector(prefix);
            var pointsOnlyInput = pointInput(prefix);
            if (showPoints && !isFlatTier(tier)) {
                if (pointsOnly) pointsOnly.style.display = '';
                if (pointsOnlyInput) {
                    var minOnlyMap = prefix === 'start' ? pointsMinStart : pointsMinEnd;
                    var maxOnlyMap = prefix === 'start' ? pointsMaxStart : pointsMaxEnd;
                    var minOnly = minOnlyMap[tier] !== undefined ? minOnlyMap[tier] : minOnlyMap[String(tier)];
                    var maxOnly = maxOnlyMap[tier] !== undefined ? maxOnlyMap[tier] : maxOnlyMap[String(tier)];
                    pointsOnlyInput.disabled = false;
                    pointsOnlyInput.setAttribute('min', minOnly !== undefined ? parseInt(minOnly, 10) : pointMin);
                    pointsOnlyInput.setAttribute('max', maxOnly !== undefined ? parseInt(maxOnly, 10) : pointMax);
                    normalizePointInput(pointsOnlyInput);
                }
            } else {
                if (pointsOnly) pointsOnly.style.display = 'none';
                if (pointsOnlyInput) pointsOnlyInput.disabled = true;
            }
            var pointsOnlyOption = prefix === 'start' ? q('#start-lp-option') : null;
            if (pointsOnlyOption) pointsOnlyOption.style.display = 'none';
            return;
        }
        var count = tierDivCount(tier);
        var wrap = q('#' + prefix + '_divisions');
        var hr = q('#' + prefix + '_divisions_hr');
        var hidden = q('input[data-dynamic-no-division="' + (prefix === 'start' ? 'start' : 'end') + '"]');
        var radios = qa('input[name="' + prefix + '_division"]:not([data-dynamic-no-division])');
        var labels = qa('#' + prefix + '_divisions [data-dynamic-division-label]');

        if (!wrap) return;

        if (count <= 0) {
            wrap.style.display = 'none';
            if (hr) hr.style.display = 'none';
            radios.forEach(function(input) { input.checked = false; input.disabled = true; });
            labels.forEach(function(label) { label.style.display = 'none'; });
            if (hidden) hidden.disabled = false;
            var points = pointSelector(prefix);
            var pInput = pointInput(prefix);
            if (isFlatTier(tier)) {
                // Truly flat tier (e.g. Eternity/Master/SupersonicLegend/Top500): no divisions
                // AND no points input at all — just the tier selection itself.
                if (points) points.style.display = 'none';
                if (pInput) pInput.disabled = true;
            } else if (showPoints) {
                if (points) points.style.display = '';
                if (pInput) {
                    pInput.disabled = false;
                    var minMap = prefix === 'start' ? pointsMinStart : pointsMinEnd;
                    var maxMap = prefix === 'start' ? pointsMaxStart : pointsMaxEnd;
                    var tierMin = minMap && (minMap[tier] !== undefined ? minMap[tier] : minMap[String(tier)]);
                    var tierMax = maxMap && (maxMap[tier] !== undefined ? maxMap[tier] : maxMap[String(tier)]);
                    if (tierMin !== undefined) {
                        pInput.setAttribute('min', parseInt(tierMin, 10));
                        if (parseInt(pInput.value || '0', 10) < parseInt(tierMin, 10)) pInput.value = tierMin;
                    } else {
                        pInput.setAttribute('min', pointMin);
                    }
                    pInput.setAttribute('max', tierMax !== undefined ? parseInt(tierMax, 10) : pointMax);
                    normalizePointInput(pInput);
                }
            } else {
                // Games without rank points (notably Fortnite's Unreal tier) must
                // not revive the shared full-points selector for divisionless tiers.
                if (points) points.style.display = 'none';
                if (pInput) pInput.disabled = true;
            }
            if (prefix === 'start') {
                var startLpOption = q('#start-lp-option');
                if (startLpOption) startLpOption.style.display = 'none';
            }
            return;
        }

        wrap.style.display = '';
        if (hr) hr.style.display = '';
        if (hidden) hidden.disabled = true;
        var points = pointSelector(prefix);
        if (points) points.style.display = 'none';
        var pInput = pointInput(prefix);
        if (pInput) pInput.disabled = true;
        if (prefix === 'start') {
            var startLpOption = q('#start-lp-option');
            if (startLpOption) startLpOption.style.display = '';
        }

        radios.forEach(function(input) {
            var div = parseInt(input.value, 10);
            var within = div <= count;
            input.disabled = !within;
            var label = input.closest('label');
            if (label) {
                label.style.display = within ? '' : 'none';
                label.style.pointerEvents = within ? '' : 'none';
                label.style.opacity = within ? '' : '0.42';
            }
        });

        var checked = q('input[name="' + prefix + '_division"]:checked:not([data-dynamic-no-division])');
        if (!checked || checked.disabled || parseInt(checked.value, 10) > count) {
            if (checked) checked.checked = false;
            var fallback = radios.slice().reverse().find(function(input) { return !input.disabled; }) || radios.find(function(input) { return !input.disabled; });
            if (fallback) fallback.checked = true;
        }
    }

    function syncDesiredOptions() {
        // Current Rank has no eligibility restriction — any tier is always selectable.
        // Guard against anything (e.g. sitewide "disable inputs in hidden containers"
        // scripts reacting to the visually-hidden legacy grid) leaving these disabled.
        qa('input[name="start_tier"]').forEach(function(input) { input.disabled = false; });
        var startTier = parseInt(checkedValue('start_tier', rankKeys[0] || '1'), 10);
        syncDivisionBlock('start', startTier);
        var startDiv = parseInt(checkedValue('start_division', '0'), 10) || 0;
        var startHasDivs = tierDivCount(startTier) > 0;
        var startIsMaxDivision = startHasDivs && startDiv >= tierDivCount(startTier);
        var startPointInput = pointInput('start');
        var startIsMaxPoints = !startHasDivs && showPoints && startPointInput && !startPointInput.disabled
            && parseInt(startPointInput.value || '0', 10) >= parseInt(startPointInput.getAttribute('max') || pointMax, 10);
        var endInputs = qa('input[name="end_tier"]');
        // Games with no divisions AND no in-tier points (e.g. Wingman's flat Silver-to-SMFC
        // ladder) have zero granularity within a tier — picking the same tier for both
        // Current and Desired would be a meaningless zero-progress order, so require
        // Desired to be strictly higher. Apex/CS2 Premier's RP ladder is unaffected since
        // those have showPoints=true and are handled by the startIsMaxPoints check instead.
        var noInTierProgression = !hasAnyDivisions && !showPoints;

        endInputs.forEach(function(input) {
            var tier = parseInt(input.value, 10);
            // Equal tier is allowed even when divisionless (e.g. Apex Master as both Current
            // and Desired) — real progression there is enforced by the RP-minimum inputs
            // (points_min_start/points_min_end), not by forcing a different tier.
            // Once the current rank is already at the highest division of its tier,
            // the desired rank must start at the next tier (e.g. Fortnite Elite III
            // can only progress to Champion I or higher).
            var invalid = tier < startTier || (tier === startTier && (startIsMaxDivision || startIsMaxPoints || noInTierProgression));
            setDisabled(input, invalid);
        });

        var endChecked = q('input[name="end_tier"]:checked');
        var autoAdvancedTier = false;
        if (!endChecked || endChecked.disabled) {
            if (endChecked) endChecked.checked = false;
            var firstValid = endInputs.find(function(input) { return !input.disabled; }) || q('input[name="end_tier"][value="' + defaultEndTier + '"]') || endInputs[0];
            if (firstValid) {
                firstValid.checked = true;
                endChecked = firstValid;
                autoAdvancedTier = true;
            }
        }

        var endTier = parseInt(checkedValue('end_tier', defaultEndTier), 10);
        syncDivisionBlock('end', endTier);
        if (hasAnyDivisions && tierDivCount(endTier) > 0) {
            // The custom radio inputs themselves are visually hidden by CSS, so
            // offsetParent cannot be used to decide whether a division is active.
            // syncDivisionBlock already disables divisions that do not belong to
            // the selected tier.
            var endDivInputs = qa('input[name="end_division"]:not([data-dynamic-no-division])').filter(function(input) { return !input.disabled; });
            if (autoAdvancedTier && endTier > startTier) {
                var nearestDivision = endDivInputs.find(function(input) { return parseInt(input.value, 10) === 1; }) || endDivInputs[0];
                endDivInputs.forEach(function(input) { input.checked = input === nearestDivision; });
            }
            endDivInputs.forEach(function(input) {
                var div = parseInt(input.value, 10);
                setDisabled(input, endTier === startTier && startHasDivs && div <= startDiv);
            });
            var endDivChecked = q('input[name="end_division"]:checked:not([data-dynamic-no-division])');
            if (!endDivChecked || endDivChecked.disabled) {
                if (endDivChecked) endDivChecked.checked = false;
                var firstValidDiv = endDivInputs.find(function(input) { return !input.disabled; }) || endDivInputs[0];
                if (firstValidDiv) { firstValidDiv.checked = true; triggerChange(firstValidDiv); }
            }
        }
    }
    function syncSummary(changedName) {
        var startTier = checkedValue('start_tier', rankKeys[0] || '1');
        var endTier = checkedValue('end_tier', defaultEndTier);
        var startPointsInput = pointInput('start');
        var endPointsInput = pointInput('end');
        qa('.current-summary-rank-name').forEach(function(el) { el.textContent = String(startTier) === '0' ? rankName(startTier) : (ratingOnly && startPointsInput ? formattedPoints(startPointsInput.value) : rankNameWithDivision('start', startTier)); });
        qa('.desired-summary-rank-name').forEach(function(el) { el.textContent = ratingOnly && endPointsInput ? formattedPoints(endPointsInput.value) : rankNameWithDivision('end', endTier); });
        if (ratingOnly) {
            qa('.current-summary-lp, .desired-summary-lp').forEach(function(el) { el.style.display = 'none'; });
        }
        if (tierDivCount(startTier) <= 0 && startPointsInput) {
            qa('.current-summary-lp').forEach(function(el) { el.textContent = '[ ' + (startPointsInput.value || '0') + ' ' + pointsLabel + ' ]'; });
        }
        if (tierDivCount(endTier) <= 0 && endPointsInput) {
            qa('.desired-summary-lp').forEach(function(el) { el.textContent = '[ ' + (endPointsInput.value || '0') + ' ' + pointsLabel + ' ]'; });
        }
        setImage('.current-rank-img, .current-summary-rank-img', startTier);
        setImage('.desired-rank-img, .desired-summary-rank-img', endTier);
        var newAccountSelected = String(startTier) === '0';
        var formRoot = q('[data-dynamic-rank-form="1"]');
        if (formRoot) formRoot.setAttribute('data-new-account', newAccountSelected ? '1' : '0');
        qa('.order-summary').forEach(function(el) { el.classList.toggle('is-new-account', newAccountSelected); });
        qa('.current-new-account-icon').forEach(function(el) { el.style.display = newAccountSelected ? '' : 'none'; });
        if (newAccountSelected) {
            qa('.current-rank-img, .current-summary-rank-img').forEach(function(el) { el.style.display = 'none'; });
        }
        // Always redraw both dropdowns from their own actual current value. This is safe
        // (each instance only reads/writes its own cached DOM nodes, so one can never echo
        // the other) and is required because changing Current Rank can silently auto-advance
        // Desired Rank's underlying tier (see syncDesiredOptions) — that side effect must be
        // reflected visually even though the user only interacted with the other dropdown.
        syncDropdown('start', startTier);
        syncDropdown('end', endTier);
    }
    // Current Rank and Desired Rank are built as two fully independent instances below —
    // each caches its OWN dom nodes once (wrap/toggle/list/hero/name/items) in its own
    // closure, so there is no shared selector or shared state that could ever make one
    // dropdown echo the other's selection.
    var rankDropdowns = {};
    function setupRankDropdown(prefix) {
        var wrap = q('.rank-dropdown-picker[data-dynamic-dropdown="' + prefix + '"]');
        if (!wrap) return null;
        var toggle = wrap.querySelector('[data-dropdown-toggle]');
        var list = wrap.querySelector('.rank-dropdown-list');
        var hero = q('.rank-dropdown-hero-icon[data-dropdown-hero="' + prefix + '"]');
        var nameEl = wrap.querySelector('.rank-dropdown-name');
        var items = list ? Array.prototype.slice.call(list.querySelectorAll('.rank-dropdown-item')) : [];
        var inst = { prefix: prefix, wrap: wrap, toggle: toggle, list: list, hero: hero, nameEl: nameEl, items: items };

        inst.render = function (tier) {
            if (inst.hero) {
                var src = rankIcon(tier);
                if (src) { inst.hero.style.display = ''; inst.hero.setAttribute('src', src); }
            }
            if (inst.nameEl) inst.nameEl.textContent = rankNameWithDivision(inst.prefix, tier);
            // Current Rank (prefix "start") has no eligibility restriction — every tier
            // must always stay selectable, so its items never get the disabled treatment,
            // regardless of whatever the underlying (visually-hidden) radio's .disabled reads as.
            inst.items.forEach(function (item) {
                var itemTier = item.getAttribute('data-tier');
                item.classList.toggle('active', String(itemTier) === String(tier));
                if (inst.prefix === 'start') {
                    item.classList.remove('disabled');
                } else {
                    var radio = q('input[name="' + inst.prefix + '_tier"][value="' + itemTier + '"]');
                    item.classList.toggle('disabled', !!(radio && radio.disabled));
                }
            });
        };

        if (toggle && list) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (inst.prefix === 'start') {
                    // Belt-and-suspenders: force every Current Rank option enabled and
                    // un-disabled right as the list opens, regardless of anything that
                    // may have flipped .disabled on the underlying radios since the last sync.
                    qa('input[name="start_tier"]').forEach(function (r) { r.disabled = false; });
                    inst.items.forEach(function (it) { it.classList.remove('disabled'); });
                }
                var isOpen = inst.wrap.classList.contains('open');
                Object.keys(rankDropdowns).forEach(function (key) {
                    var other = rankDropdowns[key];
                    if (other && other !== inst) { other.wrap.classList.remove('open'); other.list.hidden = true; }
                });
                inst.wrap.classList.toggle('open', !isOpen);
                inst.list.hidden = isOpen;
            });
            items.forEach(function (item) {
                item.addEventListener('click', function () {
                    var tier = item.getAttribute('data-tier');
                    var radio = q('input[name="' + inst.prefix + '_tier"][value="' + tier + '"]');
                    if (!radio) return;
                    if (inst.prefix === 'start') {
                        // Always selectable — force-clear any external disabling before use.
                        radio.disabled = false;
                    } else if (item.classList.contains('disabled') || radio.disabled) {
                        return;
                    }
                    radio.checked = true;
                    inst.wrap.classList.remove('open');
                    inst.list.hidden = true;
                    // Update THIS dropdown's own icon/name immediately, from the exact
                    // tier just clicked — using this instance's own cached nodes only.
                    inst.render(tier);
                    triggerChange(radio);
                });
            });
        }

        if (inst.prefix === 'start' && typeof MutationObserver !== 'undefined') {
            // Last-resort guard: if anything else on the page (e.g. a sitewide "disable
            // inputs inside hidden containers" script reacting to the visually-hidden
            // legacy grid) ever flips a Current Rank radio back to disabled, revert it.
            qa('input[name="start_tier"]').forEach(function (input) {
                new MutationObserver(function () {
                    if (input.disabled) input.disabled = false;
                }).observe(input, { attributes: true, attributeFilter: ['disabled'] });
            });
        }

        return inst;
    }
    function syncDropdown(prefix, tier) {
        var inst = rankDropdowns[prefix];
        if (inst) inst.render(tier);
    }
    function initRankDropdowns() {
        rankDropdowns.start = setupRankDropdown('start');
        rankDropdowns.end = setupRankDropdown('end');
        document.addEventListener('click', function (e) {
            Object.keys(rankDropdowns).forEach(function (key) {
                var inst = rankDropdowns[key];
                if (inst && inst.wrap.classList.contains('open') && !inst.wrap.contains(e.target)) {
                    inst.wrap.classList.remove('open');
                    inst.list.hidden = true;
                }
            });
        });
    }
    function enforcePointOrder(changedName) {
        if (!isPointProgressionForm || isGamesForm) return;
        var startTier = parseInt(checkedValue('start_tier', '0'), 10);
        var endTier = parseInt(checkedValue('end_tier', '0'), 10);
        var startInput = pointInput('start');
        var endInput = pointInput('end');
        if (!startInput || !endInput || startInput.disabled || endInput.disabled || startTier !== endTier || tierDivCount(startTier) > 0) return;

        normalizePointInput(startInput);
        normalizePointInput(endInput);
        var startValue = parseInt(startInput.value || '0', 10);
        var tierMax = parseInt(startInput.getAttribute('max') || pointMax, 10);
        var maxStart = Math.max(parseInt(startInput.getAttribute('min') || pointMin, 10), tierMax - Math.max(1, pointStep));
        startInput.setAttribute('max', maxStart);
        if (startValue > maxStart) {
            startValue = maxStart;
            startInput.value = maxStart;
        }
        var endMax = parseInt(endInput.getAttribute('max') || pointMax, 10);
        var requiredEnd = Math.min(endMax, startValue + Math.max(1, pointStep));
        var configuredMin = parseInt((pointsMinEnd && (pointsMinEnd[endTier] !== undefined ? pointsMinEnd[endTier] : pointsMinEnd[String(endTier)])) || pointMin, 10);
        var minimum = Math.max(requiredEnd, isNaN(configuredMin) ? pointMin : configuredMin);
        endInput.setAttribute('min', minimum);
        if ((parseInt(endInput.value || '0', 10) || 0) < minimum) endInput.value = minimum;
    }
    function syncAll(changedName) {
        // Changing anything else (Desired Rank, Map, etc.) must never move which
        // Current Rank is selected — capture it up front and force it back if the
        // sync pipeline below ends up shifting it for any reason.
        var preservedStart = (changedName && changedName !== 'start_tier') ? checkedValue('start_tier', null) : null;
        syncDesiredOptions();
        enforcePointOrder(changedName);
        syncSummary(changedName);
        if (preservedStart !== null && checkedValue('start_tier', null) !== preservedStart) {
            var preservedRadio = q('input[name="start_tier"][value="' + preservedStart + '"]');
            if (preservedRadio) {
                preservedRadio.disabled = false;
                preservedRadio.checked = true;
                syncSummary();
            }
        }
    }
    // The generic order summary is rendered after this form on some page layouts.
    // Re-run the initial sync once its markup exists so preselected divisions are
    // visible immediately, without requiring the first manual rank change.
    document.addEventListener('lb:order-summary-ready', function() { syncAll(); });
    function initGamesSlider() {
        if (!isGamesForm) return;
        var slider = document.getElementById('dynamic_matches_slider');
        var input = document.getElementById('dynamic_matches_input');
        var count = document.getElementById('dynamic_games_count');
        if (!slider || !input || slider.noUiSlider || typeof noUiSlider === 'undefined') return;
        noUiSlider.create(slider, {
            start: [parseInt(input.value, 10) || <?= (int)$_matchesDefault ?>], step: 1,
            connect: [true, false], range: { min: 1, max: <?= (int)$_matchesMax ?> },
            format: { to: function(v) { return Math.round(v); }, from: function(v) { return Number(v); } }
        });
        slider.noUiSlider.on('update', function(values) {
            var value = parseInt(values[0], 10) || 1;
            input.value = value;
            if (count) count.textContent = value;
        });
        slider.noUiSlider.on('change', function() {
            input.dispatchEvent(new Event('change', { bubbles: true }));
            document.dispatchEvent(new CustomEvent('lb:generic-form-changed'));
        });
    }

    document.addEventListener('click', function(e) {
        var btn = e.target && e.target.closest ? e.target.closest('[data-dynamic-point-step]') : null;
        if (!btn) return;
        e.preventDefault();
        changePoint(btn.getAttribute('data-dynamic-point-step'), parseInt(btn.getAttribute('data-delta') || '0', 10));
    });

    document.addEventListener('input', function(e) {
        if (!e.target || e.target.name !== 'start_lp_full' && e.target.name !== 'end_lp_full') return;
        e.target.value = String(e.target.value || '').replace(/[^0-9]/g, '');
        // Keep the field freely editable while the user is typing. In particular,
        // do not run syncAll() here: it normalizes an empty/partial value back to
        // the selected tier minimum, which made replacing e.g. 10000 with 5000
        // impossible. Full validation still runs on the change/blur event below.
        if (e.target.value !== '') {
            syncTierFromPoint(e.target.name === 'start_lp_full' ? 'start' : 'end', e.target);
        }
        syncSummary();
        document.dispatchEvent(new CustomEvent('lb:generic-form-changed'));
    });

    document.addEventListener('change', function(e) {
        if (!e.target) return;
        if (['start_tier', 'start_division', 'end_tier', 'end_division', 'start_lp', 'start_lp_full', 'end_lp_full', 'lp_gain', 'server', 'platform', 'queue_type'].indexOf(e.target.name) !== -1) {
            if (e.target.name === 'start_lp_full' || e.target.name === 'end_lp_full') {
                syncTierFromPoint(e.target.name === 'start_lp_full' ? 'start' : 'end', e.target);
                normalizePointInput(e.target);
            }
            syncAll(e.target.name);
            document.dispatchEvent(new CustomEvent('lb:generic-form-changed'));
        }
    });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function() { syncAll(); initGamesSlider(); initRankDropdowns(); }); else { syncAll(); initGamesSlider(); initRankDropdowns(); }
    setTimeout(syncAll, 100);

    // ── Apex Legends: "Current RP" bracket list depends on the selected Current Rank
    // tier + division (each division covers a fixed RP band, band width grows per rank:
    // Rookie 50 RP/bracket up to Diamond 180 RP/bracket, reaching 15000 total at Diamond I —
    // matching Master's 15000 RP floor). Master itself has no divisions, so it uses the
    // plain LP-number input above instead of this bracket select.
    if (<?= json_encode($_gameFieldKey === 'apex-legends') ?>) {
        var apexBucketByTier = { '1': 50, '2': 80, '3': 115, '4': 150, '5': 175, '6': 180 }; // Rookie..Diamond
        var apexTierOrder = ['1', '2', '3', '4', '5', '6'];
        var apexDivsPerTier = 4;
        var apexBucketsPerDiv = 5;
        function apexTierStartRp(tier) {
            var total = 0;
            for (var i = 0; i < apexTierOrder.length; i++) {
                if (parseInt(apexTierOrder[i], 10) >= tier) break;
                total += apexBucketByTier[apexTierOrder[i]] * apexDivsPerTier * apexBucketsPerDiv;
            }
            return total;
        }
        function apexBuildOptions(tier, div) {
            var bucket = apexBucketByTier[String(tier)];
            if (!bucket) return null; // Master (or unknown tier) — no bracket list, uses RP number input
            var start = apexTierStartRp(tier) + (Math.max(1, div) - 1) * bucket * apexBucketsPerDiv;
            var opts = [];
            for (var i = 0; i < apexBucketsPerDiv; i++) {
                var lo = start + i * bucket;
                var hi = lo + bucket - 1;
                opts.push({ value: lo + '-' + hi, label: lo + '-' + hi + ' RP' });
            }
            return opts;
        }
        function apexRefreshPointsSelect() {
            var select = q('select[name="start_lp"]');
            if (!select) return;
            var tier = parseInt(checkedValue('start_tier', rankKeys[0] || '1'), 10);
            var div = parseInt(checkedValue('start_division', '1'), 10) || 1;
            var opts = apexBuildOptions(tier, div);
            if (!opts) return;
            select.innerHTML = '';
            opts.forEach(function (o) {
                var el = document.createElement('option');
                el.value = o.value;
                el.textContent = o.label;
                select.appendChild(el);
            });
            select.value = opts[0].value;
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && window.jQuery(select).data('select2')) {
                window.jQuery(select).trigger('change.select2');
            }
            triggerChange(select);
        }
        document.addEventListener('change', function (e) {
            if (e.target && (e.target.name === 'start_tier' || e.target.name === 'start_division')) {
                setTimeout(apexRefreshPointsSelect, 0);
            }
        });
        setTimeout(apexRefreshPointsSelect, 60);
    }
})();
</script>
