<?php
$summaryGame = isset($summary_game) ? $summary_game : ($data['game'] ?? 'lol');
$_summaryGameRaw = strtolower(trim((string)$summaryGame));
$gameAliases = [
    'tft' => 'lol', 'rival' => 'rivals', 'marvel-rival' => 'rivals', 'marvel-rivals' => 'rivals',
    'rocket-league' => 'rl', 'apex-legends' => 'apex', 'overwatch-2' => 'ow2',
];
$summaryGame = isset($gameAliases[$summaryGame]) ? $gameAliases[$summaryGame] : $summaryGame;
$jsonData = $data['json'] ?? [];
$summaryPoints = isset($summary_points) ? $summary_points : ($jsonData['points_label'] ?? (($summaryGame === 'ow2') ? 'RR' : (($summaryGame === 'apex' || $summaryGame === 'rivals') ? 'RP' : (($summaryGame === 'rl') ? 'MMR' : 'LP'))));

if (!function_exists('lb_summary_rank_label')) {
    function lb_summary_rank_label($value, $fallback) {
        if (is_array($value)) {
            foreach (['name','label','title','rank','long_name'] as $k) {
                if (isset($value[$k]) && trim((string)$value[$k]) !== '') return trim((string)$value[$k]);
            }
        } elseif (trim((string)$value) !== '') return trim((string)$value);
        return $fallback;
    }
}
if (!function_exists('lb_summary_percent_badge')) {
    function lb_summary_percent_badge($value, $fallback = '') {
        if (is_numeric($value)) {
            $v = (float)$value;
            if ($v > 0 && $v < 1) return '+' . (int)round($v * 100) . '%';
            if ($v >= 1 && $v <= 100) return '+' . (int)round($v) . '%';
        }
        return $fallback;
    }
}
if (!function_exists('lb_summary_form_options')) {
    function lb_summary_form_options(array $jsonData): array {
        foreach (['form_options','options','enabled_options','fields','enabled_fields'] as $key) {
            if (isset($jsonData[$key]) && is_array($jsonData[$key])) return $jsonData[$key];
        }
        // Support our form_config format
        if (!empty($jsonData['form_config']) && is_array($jsonData['form_config'])) {
            $cfg = $jsonData['form_config'];
            $mapped = [];
            $keyMap = [
                'show_server'         => 'server',
                'show_platform'       => 'platform',
                'show_current_points' => 'current_points',
                'show_lp_gain'        => 'lp_gain',
                'show_queue_type'     => 'queue_type',
                'show_solo_duo'       => 'solo_duo',
            ];
            foreach ($keyMap as $cfgKey => $optKey) {
                if (array_key_exists($cfgKey, $cfg)) {
                    $mapped[$optKey] = $cfg[$cfgKey];
                }
            }
            if (!empty($mapped)) return $mapped;
        }
        return [];
    }
}
if (!function_exists('lb_summary_bool_value')) {
    function lb_summary_bool_value($v): bool {
        if (is_bool($v)) return $v;
        if (is_numeric($v)) return ((float)$v) > 0;
        $s = strtolower(trim((string)$v));
        return in_array($s, ['1','true','yes','on','enabled','active','checked'], true);
    }
}
if (!function_exists('lb_summary_norm_option_key')) {
    function lb_summary_norm_option_key($key): string {
        $key = strtolower(trim((string)$key));
        $key = str_replace(['/', '-', '.', ':'], ' ', $key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        return trim($key, '_');
    }
}
if (!function_exists('lb_summary_option_enabled')) {
    function lb_summary_option_enabled(array $options, array $aliases, bool $default): bool {
        if (empty($options)) return $default;
        $normalized = [];
        foreach ($options as $key => $value) {
            if (is_int($key)) {
                if (is_string($value)) $normalized[lb_summary_norm_option_key($value)] = true;
                elseif (is_array($value)) {
                    $name = $value['key'] ?? $value['name'] ?? $value['id'] ?? $value['label'] ?? null;
                    if ($name !== null) $normalized[lb_summary_norm_option_key($name)] = $value;
                }
            } else {
                $normalized[lb_summary_norm_option_key($key)] = $value;
            }
        }
        foreach ($aliases as $alias) {
            $a = lb_summary_norm_option_key($alias);
            if (array_key_exists($a, $normalized)) {
                $v = $normalized[$a];
                if (is_array($v)) {
                    foreach (['enabled','active','checked','value','show'] as $k) {
                        if (array_key_exists($k, $v)) return lb_summary_bool_value($v[$k]);
                    }
                    return true;
                }
                return lb_summary_bool_value($v);
            }
        }
        return false;
    }
}
if (!function_exists('lb_summary_division_count_from_json')) {
    function lb_summary_division_count_from_json(array $jsonData, int $fallback = 4): int {
        foreach (['division_count','divisions_count','divisionCount','divisions','division'] as $key) {
            if (isset($jsonData[$key]) && is_numeric($jsonData[$key])) {
                $count = (int)$jsonData[$key];
                return ($count >= 0 && $count <= 5) ? $count : $fallback;
            }
        }
        if (!empty($jsonData['main']) && is_array($jsonData['main'])) {
            $keys = array_keys($jsonData['main']);
            $numericKeys = array_values(array_filter($keys, static function($k) { return is_numeric($k); }));
            if (count($numericKeys) === 1) {
                $count = (int)$numericKeys[0];
                if ($count >= 0 && $count <= 5) return $count;
            }
        }
        return $fallback;
    }
}
if (!function_exists('lb_summary_rank_icon_url')) {
    function lb_summary_rank_icon_url(array $jsonData, string $game, string $size, int $rank): string {
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
        return util_rank_img($game, $size, $rank);
    }
}

$rankLabels = isset($ranks) && is_array($ranks) ? $ranks : [];
if (empty($rankLabels)) {
    $rankSource = $jsonData['rank_names'] ?? $jsonData['rankNames'] ?? $jsonData['ranks'] ?? $jsonData['tiers'] ?? [];
    if (is_array($rankSource)) {
        foreach ($rankSource as $key => $value) {
            $idx = is_numeric($key) ? (int)$key : (count($rankLabels) + 1);
            $rankLabels[$idx] = lb_summary_rank_label($value, 'Tier ' . $idx);
        }
    }
}
if (empty($rankLabels) && !empty($jsonData['main']) && is_array($jsonData['main'])) {
    foreach (array_keys($jsonData['main']) as $key) {
        if (is_numeric($key)) $rankLabels[(int)$key] = lb_summary_rank_label($jsonData['main'][$key], 'Tier ' . (int)$key);
    }
}
if (empty($rankLabels)) $rankLabels = [1=>'Tier 1',2=>'Tier 2',3=>'Tier 3',4=>'Tier 4',5=>'Tier 5',6=>'Tier 6',7=>'Tier 7'];
ksort($rankLabels, SORT_NUMERIC);
$rankKeys = array_values(array_keys($rankLabels));
$rankCount = count($rankKeys);
$configuredSummaryTier = $jsonData['default_start_tier'] ?? null;
$defaultFromTier = $configuredSummaryTier !== null && in_array((int)$configuredSummaryTier, $rankKeys, true)
    ? (int)$configuredSummaryTier
    : ($rankKeys[max(0, min($rankCount - 1, (int)floor($rankCount * 0.25)))] ?? 1);
$defaultToTier = $rankKeys[max(0, min($rankCount - 1, array_search($defaultFromTier, $rankKeys, true) + 1))] ?? $defaultFromTier;
if ($defaultToTier <= $defaultFromTier && $rankCount > 1) $defaultToTier = $rankKeys[1];
$divisionCount = lb_summary_division_count_from_json($jsonData, 4);
$defaultDivisionFrom = max(1, $divisionCount);
$defaultDivisionTo = 1;
$formId = (int)($data['id'] ?? $data['form_id'] ?? 0);
$summaryFormSlug = strtolower(trim((string)($data['slug'] ?? '')));
$summaryFormType = strtolower(trim((string)($data['type'] ?? '')));
$summaryIsWin = $summaryFormType === 'win' || $summaryFormSlug === 'win-boost';
$summaryIsPlacement = $summaryFormType === 'placement' || in_array($summaryFormSlug, ['placement', 'placement-boost', 'placements-boost'], true);
$summaryIsGamesForm = $summaryIsWin || $summaryIsPlacement;
$summaryGamesDefault = max(1, (int)($jsonData['matches_default'] ?? ($summaryIsWin ? 2 : 5)));
// Wingman Boost shares the "counter-strike-2" game slug with Premier Rank Boost but has no
// RP/points concept — the "[ 0-20 Rating ]" bracket used for Premier's CS Rating buckets
// must not appear for Wingman's plain Silver-to-SMFC ranks.
$summaryIsWingman = ($_summaryGameRaw === 'counter-strike-2' || $summaryGame === 'counter-strike-2') && strpos($summaryFormSlug, 'wingman') !== false;
$soloId = 'solo_' . $formId;
$duoId = 'duo_' . $formId;

$formOptions = lb_summary_form_options($jsonData);
$extra = $jsonData['extra'] ?? [];
// If form_config is present, use it as source of truth (don't fall back to extra keys)
$_hasFormCfg = !empty($jsonData['form_config']) && is_array($jsonData['form_config']);
$_duoDefault = $_hasFormCfg ? (bool)($jsonData['form_config']['show_solo_duo'] ?? false) : array_key_exists('is_duo', $extra);
$hasDuo = lb_summary_option_enabled($formOptions, ['solo_duo','solo / duo','solo_duo_toggle','is_duo','duo','duo_queue','field_solo_duo','option_solo_duo'], $_duoDefault);
// Fortnite supports both account boosting and playing together. Keep this
// available even when older form JSON has no show_solo_duo flag yet.
if ($_summaryGameRaw === 'fortnite' || $summaryGame === 'fortnite') {
    $hasDuo = true;
}
if ($_summaryGameRaw === 'counter-strike-2' || $summaryGame === 'counter-strike-2') {
    $hasDuo = true;
}

$optionMeta = [
    'is_priority' => ['label'=>'Priority Boost Completion','icon'=>'priority.svg','class'=>'','fallback'=>'+25%'],
    'bonus_win_extra_fee' => ['label'=>'+1 Bonus Win','icon'=>'bonus-win1.svg','class'=>'','fallback'=>'Auto','input'=>'is_bonus_win'],
    'is_bonus_win' => ['label'=>'+1 Bonus Win','icon'=>'bonus-win1.svg','class'=>'','fallback'=>'Auto'],
    'is_solo_only' => ['label'=>'Solo Only Queue','icon'=>'solo-queue1.svg','class'=>'solo-option','fallback'=>'+20%'],
    'is_streaming' => ['label'=>'Stream Games','icon'=>'stream-games1.svg','class'=>'solo-option','fallback'=>'+15%'],
    'is_coaching' => ['label'=>'Premium Coaching','icon'=>'champs-roles1.svg','class'=>'duo-option','fallback'=>'+15%'],
    'is_champions_roles' => ['label'=>'Heroes / Roles Selection','icon'=>'champs-roles1.svg','class'=>'solo-option','fallback'=>'FREE'],
    'champions' => ['label'=>'Champions Selection','icon'=>'champs-roles1.svg','class'=>'solo-option','fallback'=>'+15%'],
    'roles' => ['label'=>'Roles Selection','icon'=>'champs-roles1.svg','class'=>'solo-option','fallback'=>'+15%'],
    'agents' => ['label'=>'Agents Selection','icon'=>'champs-roles1.svg','class'=>'solo-option','fallback'=>'+15%'],
    'is_hidden_duo' => ['label'=>'Hidden Duo','icon'=>'hidden_duo3.svg','class'=>'duo-option','fallback'=>'+50%'],
    'is_undercover_winrate' => ['label'=>'Undercover Winrate','icon'=>'priority.svg','class'=>'','fallback'=>'+40%'],
    'is_moderate_kda' => ['label'=>'Moderate KDA','icon'=>'priority.svg','class'=>'','fallback'=>'+20%'],
];
$enabledOptions = [];
// Merge extra_config (admin-defined extras) into optionMeta
$extraConfig = $jsonData['extra_config'] ?? [];
foreach ($extraConfig as $_ecKey => $_ecDef) {
    if (!isset($optionMeta[$_ecKey])) {
        $_ecIcon = $_ecDef['icon'] ?? 'priority.svg';
        // Strip "fa-solid " prefix for icon filenames, use fallback svg
        $_ecIconFile = 'priority.svg';
        $optionMeta[$_ecKey] = [
            'label'    => $_ecDef['label'] ?? $_ecKey,
            'icon'     => $_ecIconFile,
            'class'    => '',
            'fallback' => '+' . (int)round(($_ecDef['def'] ?? 0.1) * 100) . '%',
        ];
    }
}

// Auto-populate the Heroes/Roles/Legends picker from locally hosted character icons
// (public/assets/website/images/boosting/<game>/...) so it works out of the box even
// before the admin has configured "Heroes / Roles Selection" items for a form.
// Any real admin-configured extra value / selection_items always take precedence.
if (!function_exists('lb_summary_scan_selection_folder')) {
    function lb_summary_scan_selection_folder(string $relDir, array $labelOverrides = []): array {
        $absDir = SYS_PATH . 'public/assets/website/images/boosting/' . trim($relDir, '/');
        if (!is_dir($absDir)) return [];
        $items = [];
        foreach ((glob($absDir . '/*.{webp,png,jpg,jpeg,svg}', GLOB_BRACE) ?: []) as $file) {
            $slug = pathinfo($file, PATHINFO_FILENAME);
            $key = preg_replace('/^mr-/', '', $slug);
            $name = $labelOverrides[$slug] ?? $labelOverrides[$key] ?? ucwords(str_replace('-', ' ', $key));
            $items[] = ['name' => $name, 'icon' => ASSET_URL . '/website/images/boosting/' . trim($relDir, '/') . '/' . basename($file)];
        }
        usort($items, function ($a, $b) { return strcmp($a['name'], $b['name']); });
        return $items;
    }
}
if (!function_exists('lb_summary_lol_champion_list')) {
    // Wild Rift shares League's champion roster/CDN, so reuse the same source the
    // existing PC-LoL champion selects already use instead of needing local icon uploads.
    function lb_summary_lol_champion_list(): array {
        $file = SYS_PATH . 'public/uploads/lists/lol-champions.json';
        if (!is_file($file)) return [];
        $raw = json_decode((string)@file_get_contents($file), true);
        if (!is_array($raw)) return [];
        $items = [];
        foreach ($raw as $key => $name) {
            $key = (string)$key;
            if ($key === '') continue;
            $items[] = ['name' => (string)$name, 'icon' => LOL_CHAMP_URL . '/' . $key . '.png'];
        }
        usort($items, function ($a, $b) { return strcmp($a['name'], $b['name']); });
        return $items;
    }
}
if (!function_exists('lb_summary_lol_role_list')) {
    function lb_summary_lol_role_list(): array {
        $roles = ['TopLane' => 'Top Lane', 'Jungle' => 'Jungle', 'MidLane' => 'Mid Lane', 'AdCarry' => 'AD Carry', 'Support' => 'Support'];
        $items = [];
        foreach ($roles as $key => $label) {
            $items[] = ['name' => $label, 'icon' => ASSET_URL . '/core/main/img/lol/roles/' . $key . '.svg'];
        }
        return $items;
    }
}
$_charLabelOverrides = [
    'd-va' => 'D.Va', 'lucio' => 'Lúcio', 'cloak-dagger' => 'Cloak & Dagger',
    'spider-man' => 'Spider-Man', 'star-lord' => 'Star-Lord',
];
$_defaultCharGroups = null; // ['roles' => [...], 'heroes' => [...]]
$_defaultCharLabelSolo = 'Heroes / Roles Selection';
$_defaultCharLabelDuo  = 'Heroes / Roles Selection';
$_defaultCharNoun = 'Heroes';
$_defaultCharPct = 0.10;
if ($_summaryGameRaw === 'marvel-rivals' || $summaryGame === 'rivals') {
    $_mrRoles = lb_summary_scan_selection_folder('marvel-rivals/roles', $_charLabelOverrides);
    $_mrHeroes = array_values(array_filter(
        lb_summary_scan_selection_folder('marvel-rivals/heroes', $_charLabelOverrides),
        function ($it) { return stripos($it['icon'], '/mr-') !== false; }
    ));
    $_defaultCharGroups = ['roles' => $_mrRoles, 'heroes' => $_mrHeroes];
    $_defaultCharLabelSolo = 'Heroes and Roles Selection';
    $_defaultCharLabelDuo  = 'Your Heroes and Roles';
    $_defaultCharNoun = 'Heroes';
    $_defaultCharPct = 0; // Free in both Solo and Duo
} elseif ($_summaryGameRaw === 'apex-legends' || $summaryGame === 'apex') {
    $_defaultCharGroups = ['roles' => [], 'heroes' => lb_summary_scan_selection_folder('apex-legends/legends', $_charLabelOverrides)];
    $_defaultCharLabelSolo = 'Legends Selection';
    $_defaultCharLabelDuo  = 'Your Legends';
    $_defaultCharNoun = 'Legends';
    $_defaultCharPct = 0.10; // Solo only — free in Duo (nulled out in calculate_boost_pricing())
} elseif ($_summaryGameRaw === 'overwatch-2' || $summaryGame === 'ow2') {
    $_defaultCharGroups = ['roles' => [], 'heroes' => lb_summary_scan_selection_folder('overwatch-2/heroes', $_charLabelOverrides)];
    $_defaultCharLabelSolo = 'Heroes Selection';
    $_defaultCharLabelDuo  = 'Your Heroes';
    $_defaultCharNoun = 'Heroes';
    $_defaultCharPct = 0.15; // Solo only — free in Duo (nulled out in calculate_boost_pricing())
} elseif ($_summaryGameRaw === 'lol-wild-rift' || $_summaryGameRaw === 'wild-rift' || $summaryGame === 'wild-rift') {
    $_wildRiftConfiguredItems = $jsonData['selection_items']['is_champions_roles'] ?? [];
    $_wildRiftChampions = [];
    if (is_array($_wildRiftConfiguredItems)) {
        $_wildRiftChampions = $_wildRiftConfiguredItems['heroes']
            ?? $_wildRiftConfiguredItems['champions']
            ?? [];
    }
    if (empty($_wildRiftChampions)) {
        $_wildRiftChampions = lb_summary_lol_champion_list();
    }
    $_wildRiftRoles = [];
    if (is_array($_wildRiftConfiguredItems)) {
        $_wildRiftRoles = $_wildRiftConfiguredItems['roles'] ?? [];
    }
    if (empty($_wildRiftRoles)) {
        $_wildRiftRoles = lb_summary_lol_role_list();
    }
    // Old form JSON stores display labels in the icon filename ("Top Lane.svg").
    // Normalize every configured role back to the real compact asset filename.
    $_wildRiftRoleFiles = [
        'toplane' => 'TopLane',
        'jungle' => 'Jungle',
        'midlane' => 'MidLane',
        'adcarry' => 'AdCarry',
        'support' => 'Support',
    ];
    foreach ($_wildRiftRoles as &$_wildRiftRole) {
        if (!is_array($_wildRiftRole)) continue;
        $_roleKey = strtolower(preg_replace('/[^a-z0-9]+/i', '', (string)($_wildRiftRole['name'] ?? '')));
        if (isset($_wildRiftRoleFiles[$_roleKey])) {
            $_wildRiftRole['icon'] = ASSET_URL . '/core/main/img/lol/roles/' . $_wildRiftRoleFiles[$_roleKey] . '.svg';
        }
    }
    unset($_wildRiftRole);
    $_defaultCharGroups = ['roles' => $_wildRiftRoles, 'heroes' => $_wildRiftChampions];
    $jsonData['selection_items']['is_champions_roles'] = $_defaultCharGroups;
    $_defaultCharLabelSolo = 'Champions and Roles Selection';
    $_defaultCharLabelDuo  = 'Your Champions and Roles';
    $_defaultCharNoun = 'Champions';
    $_defaultCharPct = 0; // Free in both Solo and Duo
}
if ($_defaultCharGroups !== null && (!empty($_defaultCharGroups['roles']) || !empty($_defaultCharGroups['heroes']))) {
    // Item list: only fill in if empty, so a hand-curated admin list isn't clobbered.
    if (empty($jsonData['selection_items']['is_champions_roles'])) {
        $jsonData['selection_items']['is_champions_roles'] = $_defaultCharGroups;
    }
    // Price/labels: authoritative for these known games (same reasoning as the options-bar fields).
    $extra['is_champions_roles'] = $_defaultCharPct;
    $extraConfig['is_champions_roles']['class'] = ''; // visible regardless of solo/duo
    $extraConfig['is_champions_roles']['label'] = $_defaultCharLabelSolo;
    $extraConfig['is_champions_roles']['label_solo'] = $_defaultCharLabelSolo;
    $extraConfig['is_champions_roles']['label_duo'] = $_defaultCharLabelDuo;
    $extraConfig['is_champions_roles']['noun'] = $_defaultCharNoun;
}

// Standard extra options (Priority / Stream / Solo Only / Coaching / Bonus Win), per game.
// Visibility (solo-only / duo-only / always) already comes from $optionMeta's built-in classes.
$_gameStandardExtras = [
    'fortnite'        => ['is_priority' => 0.25, 'is_streaming' => 0.15, 'is_solo_only' => 0.20],
    'counter-strike-2'=> ['is_priority' => 0.25, 'is_streaming' => 0.15, 'is_solo_only' => 0.40],
    'marvel-rivals'  => ['is_priority' => 0.25, 'is_streaming' => 0.15, 'is_solo_only' => 0.20, 'is_coaching' => 0.15],
    'rocket-league'  => ['is_priority' => 0.25, 'is_streaming' => 0.15, 'is_solo_only' => 0.20, 'is_coaching' => 0.15],
    'apex-legends'   => ['is_priority' => 0.25, 'is_streaming' => 0.15, 'is_solo_only' => 0.20, 'is_coaching' => 0.15],
    'overwatch-2'    => ['is_priority' => 0.20, 'is_streaming' => 0.20, 'is_solo_only' => 0.40, 'is_coaching' => 0.15],
    'lol-wild-rift'  => ['is_priority' => 0.20, 'is_streaming' => 0.15, 'is_solo_only' => 0.20, 'is_coaching' => 0.15],
    'wild-rift'      => ['is_priority' => 0.20, 'is_streaming' => 0.15, 'is_solo_only' => 0.20, 'is_coaching' => 0.15],
];
// Authoritative for these known games — overrides whatever leftover/placeholder value
// might already be sitting in the DB from when the form was first created, same reasoning
// as the options-bar fields above (a stale DB value silently winning caused the last bug).
if (isset($_gameStandardExtras[$_summaryGameRaw])) {
    foreach ($_gameStandardExtras[$_summaryGameRaw] as $_seKey => $_sePct) {
        $extra[$_seKey] = $_sePct;
    }
    if (!in_array($_summaryGameRaw, ['fortnite', 'counter-strike-2'], true) && !array_key_exists('bonus_win_extra_fee', $extra) && !array_key_exists('is_bonus_win', $extra)) {
        $extra['bonus_win_extra_fee'] = 0; // Displays as "Auto"
    }
}

// Bonus Win is a League-specific service and must never leak into Fortnite or CS2.
// through old form JSON or the generic fallback configuration.
if (in_array($_summaryGameRaw, ['fortnite', 'counter-strike-2'], true) || in_array($summaryGame, ['fortnite', 'counter-strike-2'], true)) {
    unset($extra['bonus_win_extra_fee'], $extra['is_bonus_win']);
}

// Authoritative solo/duo visibility for CS2's extras — ignore whatever class the admin's
// saved extra_config happens to have (new/imported forms default every extra to "Both"
// unless the admin explicitly flips the radio, which caused Stream/Solo Only to keep
// showing in Duo mode). Priority always shows; Stream and Solo Only are Solo-only.
if ($_summaryGameRaw === 'counter-strike-2' || $summaryGame === 'counter-strike-2') {
    $extraConfig['is_priority']['class'] = '';
    $extraConfig['is_streaming']['class'] = 'solo-option';
    $extraConfig['is_solo_only']['class'] = 'solo-option';
}

foreach ($optionMeta as $extraKey => $meta) {
    if (!array_key_exists($extraKey, $extra)) continue;
    $value = $extra[$extraKey];
    if ($value === null || $value === false || $value === '' || (is_numeric($value) && (float)$value < 0)) continue;
    // Override class from extra_config if admin set it
    if (isset($extraConfig[$extraKey]['class'])) {
        $meta['class'] = $extraConfig[$extraKey]['class'];
    }
    if (isset($extraConfig[$extraKey]['label'])) {
        $meta['label'] = $extraConfig[$extraKey]['label'];
    }
    $enabledOptions[$extraKey] = $meta + ['value' => $value];
}
// Backwards-compatible fallback for old forms without admin-driven extra metadata.
if (empty($enabledOptions) && empty($extra)) {
    $enabledOptions = [
        'is_priority' => $optionMeta['is_priority'] + ['value'=>0.25],
        'is_solo_only' => $optionMeta['is_solo_only'] + ['value'=>0.20],
        'is_streaming' => $optionMeta['is_streaming'] + ['value'=>0.15],
    ];
}

// Some forms still have the legacy "Duo Queue" toggle (key: is_duo) enabled in their
// admin extra config — a leftover duplicate of the real Solo/Duo switch above. Hide it
// (and any other stray "Heroes/Roles"-labeled duplicate that isn't the real picker key)
// for these games, regardless of what's stored in the DB.
$_gameSuppressExtraKeys = [
    'marvel-rivals' => ['is_duo'],
    'rocket-league' => ['is_duo'],
    'apex-legends'  => ['is_duo'],
    'overwatch-2'   => ['is_duo'],
    'lol-wild-rift' => ['is_duo'],
    'wild-rift'     => ['is_duo'],
];
$_gameSuppressExtraLabels = [
    'marvel-rivals' => ['heroes and roles selection'],
];
$_suppressKeys = $_gameSuppressExtraKeys[$_summaryGameRaw] ?? [];
$_suppressLabels = $_gameSuppressExtraLabels[$_summaryGameRaw] ?? [];
foreach ($_suppressKeys as $_supKey) {
    unset($enabledOptions[$_supKey]);
}
if (!empty($_suppressLabels)) {
    foreach ($enabledOptions as $_seKey => $_seOpt) {
        if ($_seKey === 'is_champions_roles') continue; // never touch the real picker
        if (in_array(strtolower(trim((string)($_seOpt['label'] ?? ''))), $_suppressLabels, true)) {
            unset($enabledOptions[$_seKey]);
        }
    }
}

// Selection items (heroes / roles / agents) – keyed by extra key
$selectionItems = $jsonData['selection_items'] ?? [];

$completionHours = (float)($jsonData['completion_time'] ?? 24);
$completionText = $completionHours <= 0 ? 'Invalid' : ($completionHours <= 24 ? '~ ' . (int)$completionHours . ' hours' : '~ ' . (int)round($completionHours / 24) . ' day' . (round($completionHours / 24) == 1 ? '' : 's'));

// Games without a "Current Points" field (see rank-dynamic.php's per-game field config)
// shouldn't show the "[ 0-20 <points> ]" bracket in the checkout summary either.
$_gamesWithoutPoints = ['rocket-league', 'overwatch-2', 'lol-wild-rift', 'wild-rift', 'fortnite'];
$_showPointsBracket = !in_array($_summaryGameRaw, $_gamesWithoutPoints, true) && !$summaryIsWingman;
?>
<div class="summary-wrapper">
    <div class="order-summary">
        <h3><img src="<?= ASSET_URL ?>/website/images/cart.svg" alt="cart_icon"><?= t('Checkout') ?></h3>

        <div class="rank-box <?= $summaryIsGamesForm ? 'single-rank-summary' : '' ?>">
            <div class="from">
                <img src="<?= lb_summary_rank_icon_url($jsonData, $summaryGame, 'mini', (int)$defaultFromTier) ?>" alt="rank_icon" class="current-summary-rank-img" onerror="this.style.display='none'"<?= (($summaryGame === 'counter-strike-2' || $_summaryGameRaw === 'counter-strike-2') && $summaryIsPlacement && (int)$defaultFromTier === 0) ? ' style="display:none"' : '' ?>>
                <?php if (($summaryGame === 'counter-strike-2' || $_summaryGameRaw === 'counter-strike-2') && $summaryIsPlacement): ?>
                <i class="fa-solid fa-circle-question current-new-account-icon" aria-label="New Account" style="<?= (int)$defaultFromTier === 0 ? '' : 'display:none' ?>"></i>
                <?php endif; ?>
                <span class="title current-summary-rank-name"><?= htmlspecialchars($rankLabels[$defaultFromTier] ?? ('Tier ' . $defaultFromTier)) ?></span>
                <?php if ($_showPointsBracket): ?>
                <br>
                <small class="current-summary-lp"><?= t('[ 0-20 ' . $summaryPoints . ' ]') ?></small>
                <?php endif; ?>
            </div>

            <?php if (!$summaryIsGamesForm): ?>
            <img src="<?= ASSET_URL ?>/website/images/arrow-summary.svg" alt="arrow_icon">
            <div class="to">
                <img src="<?= lb_summary_rank_icon_url($jsonData, $summaryGame, 'mini', (int)$defaultToTier) ?>" alt="rank_icon" class="desired-summary-rank-img" onerror="this.style.display='none'">
                <span class="title desired-summary-rank-name"><?= htmlspecialchars($rankLabels[$defaultToTier] ?? ('Tier ' . $defaultToTier)) ?></span>
                <?php if ($_showPointsBracket && in_array($summaryGame, ['apex', 'counter-strike-2'], true)): ?>
                <br>
                <small class="desired-summary-lp"><?= t('[ 0 ' . $summaryPoints . ' ]') ?></small>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="to games-amount-summary">
                <span class="title"><span class="summary-games-count"><?= (int)$summaryGamesDefault ?></span> <?= $summaryIsWin ? t('Wins') : t('Games') ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($hasDuo): ?>
            <div class="toggle-group">
                <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" checked>
                <label for="<?= $soloId ?>" class="toggle-label" data-tooltip="<?= t('Booster plays on your Account') ?>" tabindex="0">
                    <i class="fa-duotone fa-user me"></i> <?= t('Solo (Pro on your Account)') ?>
                </label>
                <input type="radio" id="<?= $duoId ?>" name="is_duo" value="1">
                <label for="<?= $duoId ?>" class="toggle-label" data-tooltip="<?= t('Play with the Booster') ?>" tabindex="0">
                    <i class="fa-duotone fa-user-group"></i> <?= t('Duo (Play with Pro)') ?>
                    <?php if (!$summaryIsWingman && (in_array($_summaryGameRaw, ['fortnite', 'counter-strike-2'], true) || in_array($summaryGame, ['fortnite', 'counter-strike-2'], true))): ?><span class="badge primary">+50%</span><?php endif; ?>
                </label>
            </div>
        <?php else: ?>
            <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" checked hidden>
        <?php endif; ?>

        <?php if (!empty($enabledOptions)): ?>
        <div class="extra-options">
            <?php foreach ($enabledOptions as $extraKey => $opt):
                $inputName = $opt['input'] ?? $extraKey;
                $_labelSolo = $extraConfig[$extraKey]['label_solo'] ?? null;
                $_labelDuo  = $extraConfig[$extraKey]['label_duo'] ?? null;
                $_hasSoloDuoText = $_labelSolo !== null && $_labelDuo !== null;
                if ($_hasSoloDuoText) {
                    // Duo is always Free for the heroes/roles/legends picker; Solo keeps its configured price.
                    $badgeSolo = lb_summary_percent_badge($opt['value'], $opt['fallback'] ?? '');
                    $badgeDuo  = 'Free';
                    $badge = $badgeSolo;
                } else {
                    $badge = lb_summary_percent_badge($opt['value'], $opt['fallback'] ?? '');
                }
            ?>
                <?php
                $_selRaw = $selectionItems[$extraKey] ?? [];
                $_isGroupedSel = is_array($_selRaw) && (array_key_exists('roles', $_selRaw) || array_key_exists('heroes', $_selRaw));
                $_selRoleItems = $_isGroupedSel ? ($_selRaw['roles'] ?? []) : [];
                $_selHeroItems = $_isGroupedSel ? ($_selRaw['heroes'] ?? []) : $_selRaw;
                $_hasSelection = !empty($_selRoleItems) || !empty($_selHeroItems);
                ?>
                <div class="option <?= htmlspecialchars($opt['class'] ?? '') ?>" <?= $_hasSelection ? 'data-has-selection="1"' : '' ?>>
                    <div class="text" <?= $_hasSoloDuoText ? ('data-label-solo="' . htmlspecialchars($_labelSolo) . '" data-label-duo="' . htmlspecialchars($_labelDuo) . '" data-badge-solo="' . htmlspecialchars($badgeSolo) . '" data-badge-duo="' . htmlspecialchars($badgeDuo) . '"') : '' ?>>
                        <img src="<?= ASSET_URL ?>/website/images/boost-forms/<?= htmlspecialchars($opt['icon']) ?>" alt="option_icon">
                        <span class="option-label-text"><?= t($_hasSoloDuoText ? $_labelSolo : $opt['label']) ?></span>
                        <?php if ($badge !== ''): ?><span class="badge primary"><?= t($badge) ?></span><?php endif; ?>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" id="summary_extra_<?= htmlspecialchars($extraKey) ?>" name="<?= htmlspecialchars($inputName) ?>" value="1"
                               <?= $_hasSelection ? 'data-selection-key="' . htmlspecialchars($extraKey) . '" onchange="toggleSelectionPicker(this)"' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <?php if ($_hasSelection): ?>
                <!-- hidden items for form submission (kept outside the modal so they still submit with the <form> after the modal is re-parented to <body>).
                     Grouped pickers (roles+heroes, e.g. is_champions_roles) submit under the canonical "roles[]" / "champions[]"
                     names already whitelisted by load_boost_options() and already read by the booster/admin order views. -->
                <?php foreach ($_selRoleItems as $_item):
                    $_iname = is_array($_item) ? ($_item['name'] ?? '') : (string)$_item;
                    if (trim((string)$_iname) === '') continue;
                ?>
                <?php $_safeSelId = 'sel_' . preg_replace('/[^a-z0-9_]/i', '_', $extraKey . '_role_' . $_iname); ?>
                <input type="checkbox" name="roles[]" data-key="<?= htmlspecialchars($extraKey) ?>" data-group="role" value="<?= htmlspecialchars($_iname) ?>"
                       id="<?= htmlspecialchars($_safeSelId) ?>" style="display:none">
                <?php endforeach ?>
                <?php foreach ($_selHeroItems as $_item):
                    $_iname = is_array($_item) ? ($_item['name'] ?? '') : (string)$_item;
                    if (trim((string)$_iname) === '') continue;
                ?>
                <?php $_safeSelId = 'sel_' . preg_replace('/[^a-z0-9_]/i', '_', $extraKey . '_' . $_iname); ?>
                <input type="checkbox" name="<?= $_isGroupedSel ? 'champions[]' : ('selection_' . htmlspecialchars($extraKey) . '[]') ?>" data-key="<?= htmlspecialchars($extraKey) ?>" data-group="hero" value="<?= htmlspecialchars($_iname) ?>"
                       id="<?= htmlspecialchars($_safeSelId) ?>" style="display:none">
                <?php endforeach ?>
                <?php endif ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($enabledOptions) && !empty($selectionItems)): ?>
            <?php foreach ($enabledOptions as $_modalKey => $_modalOpt):
                $_modalRaw = $selectionItems[$_modalKey] ?? [];
                $_modalGrouped = is_array($_modalRaw) && (array_key_exists('roles', $_modalRaw) || array_key_exists('heroes', $_modalRaw));
                $_modalRoles = $_modalGrouped ? ($_modalRaw['roles'] ?? []) : [];
                $_modalHeroes = $_modalGrouped ? ($_modalRaw['heroes'] ?? []) : $_modalRaw;
                if (empty($_modalRoles) && empty($_modalHeroes)) continue;
                $_modalLabel = $_modalOpt['label'] ?? 'Selection';
                $_modalNoun = $extraConfig[$_modalKey]['noun'] ?? 'Heroes';
                $_modalBadge = lb_summary_percent_badge($_modalOpt['value'] ?? 0, $_modalOpt['fallback'] ?? '');
            ?>
            <div class="lb-selection-modal" id="lb_sel_modal_<?= htmlspecialchars($_modalKey) ?>" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="lb_sel_title_<?= htmlspecialchars($_modalKey) ?>">
                <div class="lb-selection-modal__overlay" data-lb-selection-close="<?= htmlspecialchars($_modalKey) ?>" onclick="window.closeSelectionModal && window.closeSelectionModal('<?= htmlspecialchars($_modalKey) ?>')"></div>
                <div class="lb-hr-panel">
                    <div class="lb-hr-panel__head">
                        <div class="lb-hr-panel__title">
                            <span class="lb-hr-panel__icon"><i class="fa-solid fa-people-group"></i></span>
                            <h4 id="lb_sel_title_<?= htmlspecialchars($_modalKey) ?>"><?= t($_modalLabel) ?></h4>
                        </div>
                        <button type="button" class="lb-hr-panel__close" data-lb-selection-close="<?= htmlspecialchars($_modalKey) ?>" onclick="window.closeSelectionModal && window.closeSelectionModal('<?= htmlspecialchars($_modalKey) ?>'); return false;" aria-label="<?= t('Close') ?>">&times;</button>
                    </div>
                    <div class="lb-hr-panel__body">
                        <?php if (!empty($_modalRoles)): ?>
                        <div class="lb-hr-block">
                            <div class="lb-hr-block__label"><?= t('Select your Roles') ?> <span class="lb-hr-tag-badge free"><?= t('Free') ?></span></div>
                            <div class="lb-hr-roles">
                                <?php foreach ($_modalRoles as $_item):
                                    $_name = is_array($_item) ? ($_item['name'] ?? '') : (string)$_item;
                                    $_icon = is_array($_item) ? ($_item['icon'] ?? '') : '';
                                    if (trim((string)$_name) === '') continue;
                                    $_safeId = 'sel_' . preg_replace('/[^a-z0-9_]/i', '_', $_modalKey . '_role_' . $_name);
                                ?>
                                <label class="lb-hr-role" for="<?= htmlspecialchars($_safeId) ?>" title="<?= htmlspecialchars($_name) ?>">
                                    <?php if (!empty($_icon)): ?><img src="<?= htmlspecialchars($_icon) ?>" alt="<?= htmlspecialchars($_name) ?>" onerror="this.style.display='none'"><?php endif; ?>
                                </label>
                                <?php endforeach ?>
                            </div>
                        </div>
                        <?php endif ?>
                        <?php if (!empty($_modalHeroes)): ?>
                        <div class="lb-hr-block">
                            <div class="lb-hr-block__label"><?= t('Select your') ?> <?= t($_modalNoun) ?> <?php if ($_modalBadge !== ''): ?><span class="lb-hr-tag-badge"><?= t($_modalBadge) ?></span><?php endif; ?></div>
                            <div class="lb-hr-search">
                                <i class="fa-solid fa-search"></i>
                                <input type="text" placeholder="<?= t('Search') ?>" autocomplete="off" data-lb-selection-search="<?= htmlspecialchars($_modalKey) ?>">
                            </div>
                            <div class="lb-hr-grid" data-selection-key="<?= htmlspecialchars($_modalKey) ?>">
                                <?php foreach ($_modalHeroes as $_item):
                                    $_name = is_array($_item) ? ($_item['name'] ?? ($_item['label'] ?? '')) : (string)$_item;
                                    $_icon = is_array($_item) ? ($_item['icon'] ?? ($_item['image'] ?? '')) : '';
                                    if (trim((string)$_name) === '') continue;
                                    $_safeId = 'sel_' . preg_replace('/[^a-z0-9_]/i', '_', $_modalKey . '_' . $_name);
                                ?>
                                <label class="lb-selection-chip lb-hr-grid__item" for="<?= htmlspecialchars($_safeId) ?>" data-lb-selection-label="<?= htmlspecialchars(strtolower($_name)) ?>" title="<?= htmlspecialchars($_name) ?>">
                                    <?php if (!empty($_icon)): ?><img src="<?= htmlspecialchars($_icon) ?>" alt="" onerror="this.style.display='none'"><?php endif; ?>
                                    <span><?= htmlspecialchars($_name) ?></span>
                                </label>
                                <?php endforeach ?>
                            </div>
                        </div>
                        <?php endif ?>
                    </div>
                    <div class="lb-hr-panel__foot">
                        <span class="lb-selection-count" data-lb-selection-count="<?= htmlspecialchars($_modalKey) ?>">0 <?= t('selected') ?></span>
                        <button type="button" class="lb-hr-btn ghost" data-lb-selection-close="<?= htmlspecialchars($_modalKey) ?>" onclick="window.closeSelectionModal && window.closeSelectionModal('<?= htmlspecialchars($_modalKey) ?>'); return false;"><?= t('Close') ?></button>
                        <button type="button" class="lb-hr-btn primary lb-selection-done" data-lb-selection-close="<?= htmlspecialchars($_modalKey) ?>" onclick="window.closeAllSelectionModals ? window.closeAllSelectionModals() : (window.closeSelectionModal && window.closeSelectionModal('<?= htmlspecialchars($_modalKey) ?>')); return false;"><?= t('Confirm') ?></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ── Booster Row ── -->
        <div class="bsr-row" id="bsr-row">
            <div class="bsr-left">
                <div class="bsr-icon-wrap">
                    <img src="https://lolboost.gg/public/uploads/icons/default.png" id="bsr-avatar" alt="">
                    <span class="bsr-dot" id="bsr-dot"></span>
                </div>
                <div class="bsr-info">
                    <span class="bsr-label"><?= t('Request Booster') ?></span>
                    <span class="bsr-name" id="bsr-name"><?= t('Any Available') ?></span>
                </div>
            </div>
            <svg class="bsr-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
        </div>
        <input type="hidden" name="booster_id" id="bsr-hidden" value="">

        <style>
        /* ── Row ── */
        .bsr-row {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 14px; margin-top:8px;
            background:rgba(255,255,255,.022); border:1.5px solid rgba(255,255,255,.045);
            border-radius:12px; cursor:pointer;
            transition:border-color .18s, background .18s; user-select:none;
        }
        .bsr-row:hover,.bsr-row.has-sel{border-color:rgba(99,102,241,.5);background:rgba(99,102,241,.07);}
        .bsr-left{display:flex;align-items:center;gap:10px;}
        .bsr-icon-wrap{position:relative;flex-shrink:0;}
        .bsr-icon-wrap img{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.1);display:block;}
        .bsr-dot{position:absolute;bottom:1px;right:1px;width:9px;height:9px;border-radius:50%;border:2px solid #0d0d1a;background:rgba(255,255,255,.15);display:none;}
        .bsr-dot.on{background:#35d07f;display:block;}
        .bsr-info{display:flex;flex-direction:column;gap:1px;}
        .bsr-label{font-size:10px;color:rgba(255,255,255,.38);font-weight:600;text-transform:uppercase;letter-spacing:.05em;}
        .bsr-name{font-size:13px;font-weight:700;color:rgba(255,255,255,.88);}
        .bsr-chev{width:14px;height:14px;color:rgba(255,255,255,.3);flex-shrink:0;}

        /* ── Overlay ── */
        #bsr-overlay{
            display:none;position:fixed;inset:0;z-index:9999999!important;
            align-items:flex-end;justify-content:center;
            background:rgba(0,0,0,0);transition:background .25s;
        }
        #bsr-overlay.bsr-open{display:flex;background:rgba(0,0,0,.78);}

        /* ── Panel ── */
        #bsr-panel{
            width:100%;max-width:720px;box-sizing:border-box;
            background:#0A0B17;border:1px solid rgba(255,255,255,.045);
            border-radius:20px 20px 0 0;padding:0;
            transform:translateY(100%);transition:transform .32s cubic-bezier(.4,0,.2,1);
            height:100%;max-height:100%;display:flex;flex-direction:column;overflow:hidden;
        }
        #bsr-overlay.bsr-open #bsr-panel{transform:translateY(0);}
        @media(min-width:720px){
            #bsr-overlay{align-items:center;}
            #bsr-panel{border-radius:18px;height:auto;max-height:82vh;}
        }

        /* ── Panel header ── */
        .bsr-ph{
            display:flex;align-items:center;justify-content:space-between;
            padding:max(20px,env(safe-area-inset-top)) 20px 0;flex-shrink:0;
        }
        .bsr-ph-title{font-size:17px;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px;}
        .bsr-pulse{width:8px;height:8px;border-radius:50%;background:#35d07f;flex-shrink:0;
            animation:_bsrpulse 1.5s ease-out infinite;display:none;}
        .bsr-pulse.vis{display:inline-block;}
        @keyframes _bsrpulse{0%{box-shadow:0 0 0 0 rgba(53,208,127,.5)}70%{box-shadow:0 0 0 8px rgba(53,208,127,0)}100%{box-shadow:0 0 0 0 rgba(53,208,127,0)}}
        .bsr-filter-pills{display:flex;align-items:center;gap:8px;margin-top:10px;flex-wrap:wrap;}
        .bsr-filter-pill{
            display:inline-flex;align-items:center;gap:7px;min-height:28px;padding:6px 12px;border-radius:999px;
            border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.045);
            color:rgba(255,255,255,.54);font-size:11px;font-weight:850;line-height:1;cursor:pointer;
            transition:background .15s,border-color .15s,color .15s,transform .15s;font-family:inherit;
        }
        .bsr-filter-pill:hover{border-color:rgba(99,102,241,.35);color:rgba(255,255,255,.82);background:rgba(99,102,241,.08);}
        .bsr-filter-pill.active{border-color:rgba(99,102,241,.62);color:#fff;background:rgba(99,102,241,.18);}
        .bsr-filter-pill-dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.32);flex-shrink:0;}
        .bsr-filter-pill[data-filter=all] .bsr-filter-pill-dot{background:rgba(99,102,241,.9);}
        .bsr-filter-pill[data-filter=online] .bsr-filter-pill-dot{background:#35d07f;}
        .bsr-filter-pill[data-filter=offline] .bsr-filter-pill-dot{background:rgba(255,255,255,.32);}
        .bsr-close{width:32px;height:32px;border-radius:50%;
            border:1px solid rgba(255,255,255,.1)!important;background:rgba(255,255,255,.05)!important;
            color:rgba(255,255,255,.6)!important;display:flex;align-items:center;justify-content:center;
            cursor:pointer;transition:.15s;padding:0!important;flex-shrink:0;}
        .bsr-close svg{width:14px;height:14px;pointer-events:none;}
        .bsr-close:hover{background:rgba(255,255,255,.12)!important;color:#fff!important;}

        /* ── Search bar ── */
        .bsr-search-wrap{
            margin:14px 20px 10px;
            background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);
            border-radius:10px;display:flex;align-items:center;gap:10px;padding:10px 14px;
            transition:border-color .15s;flex-shrink:0;
        }
        .bsr-search-wrap:focus-within{border-color:rgba(99,102,241,.5);background:rgba(99,102,241,.05);}
        .bsr-search-wrap svg{width:14px;height:14px;color:rgba(255,255,255,.35);flex-shrink:0;}
        .bsr-search-inp{
            flex:1;background:transparent;border:none!important;outline:none!important;
            color:#fff!important;font-size:16px;height:20px!important;font-family:inherit;
            box-shadow:none!important;padding:0!important;
        }
        @media(min-width:720px){
            .bsr-search-inp{font-size:13px;}
        }
        .bsr-search-inp::placeholder{color:rgba(255,255,255,.3);}
        .bsr-search-clear{background:none!important;border:none!important;padding:0!important;
            color:rgba(255,255,255,.3)!important;cursor:pointer;font-size:16px;line-height:1;
            display:none;flex-shrink:0;}
        .bsr-search-clear.vis{display:block;}

        /* ── Grid scroll area ── */
        .bsr-grid-wrap{flex:1;overflow-y:auto;padding:14px 20px 8px;}
        .bsr-grid-wrap::-webkit-scrollbar{width:4px;}
        .bsr-grid-wrap::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px;}

        /* Online section header */
        .bsr-sec-hd{
            font-size:11px;font-weight:700;color:rgba(255,255,255,.38);
            text-transform:uppercase;letter-spacing:.07em;
            margin-bottom:10px;display:flex;align-items:center;gap:6px;
        }
        .bsr-sec-hd-dot{width:7px;height:7px;border-radius:50%;background:#35d07f;
            animation:_bsrpulse 1.5s ease-out infinite;}

        /* ── Booster grid (like champion picker) ── */
        .bsr-grid{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(88px,1fr));
            gap:8px;margin-bottom:16px;
        }
        .bsr-item{
            display:flex;flex-direction:column;align-items:center;gap:5px;
            padding:10px 6px 8px;border-radius:12px;cursor:pointer;
            border:1.5px solid rgba(255,255,255,.045);background:rgba(255,255,255,.022);
            transition:border-color .16s,background .16s,transform .14s;
            position:relative;
        }
        .bsr-item:hover{border-color:rgba(99,102,241,.4);background:rgba(99,102,241,.07);transform:translateY(-1px);}
        .bsr-item.sel{border-color:rgba(99,102,241,.85)!important;background:rgba(99,102,241,.15)!important;}
        .bsr-item.hidden{display:none!important;}

        .bsr-item-av{position:relative;flex-shrink:0;}
        .bsr-item-av img{
            width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;
            border:2px solid rgba(255,255,255,.08);transition:border-color .16s;
        }
        .bsr-item.sel .bsr-item-av img{border-color:rgba(99,102,241,.7);}
        .bsr-item-dot{
            position:absolute;bottom:2px;right:2px;width:11px;height:11px;
            border-radius:50%;border:2px solid #0d0d18;background:rgba(255,255,255,.15);
        }
        .bsr-item-dot.on{background:#35d07f;}

        .bsr-item-name{
            font-size:11px;font-weight:700;color:rgba(255,255,255,.78);
            text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
            width:100%;max-width:78px;line-height:1.2;
        }
        .bsr-item.sel .bsr-item-name{color:#a5b4fc;}
        .bsr-item-meta{display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;min-width:0;}
        .bsr-rank-pill,.bsr-roles-pill,.bsr-lang-pill,.bsr-time-pill,.bsr-orders-pill,.bsr-rating-pill{
            max-width:100%;display:inline-flex;align-items:center;justify-content:center;gap:4px;
            min-height:20px;padding:3px 6px;border-radius:999px;
            background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.07);
            color:rgba(255,255,255,.58);font-size:10px;font-weight:700;line-height:1;
            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
        }
        .bsr-rank-pill img{width:15px;height:15px;object-fit:contain;flex-shrink:0;}
        .bsr-role-icons,.bsr-lang-icons{display:inline-flex;align-items:center;gap:3px;flex-shrink:0;}
        .bsr-role-icons img{width:13px;height:13px;object-fit:contain;opacity:.82;}
        .bsr-lang-icons img{width:16px;height:16px;object-fit:cover;border-radius:50%;opacity:.95;}
        .bsr-roles-label{display:none;}
        .bsr-rank-label{overflow:hidden;text-overflow:ellipsis;}
        .bsr-pill-icon{font-size:10px;color:rgba(255,255,255,.48);line-height:1;}
        .bsr-profile-btn{display:none;}

        @media(min-width:720px){
            #bsr-panel{
                width:min(1080px,calc(100vw - 44px));
                max-width:1080px;
                border-radius:22px;
            }
            .bsr-ph{padding:22px 28px 0;}
            .bsr-ph-title{font-size:18px;}
            .bsr-filter-pills{margin-top:11px;}
            .bsr-search-wrap{margin:16px 28px 14px;padding:12px 15px;border-radius:12px;}
            .bsr-search-inp{font-size:14px;height:24px!important;}
            .bsr-grid-wrap{padding:15px 28px 10px;overflow-x:hidden;}
            .bsr-sec-hd{font-size:12px;margin-bottom:12px;}
            .bsr-grid{display:flex;flex-direction:column;gap:9px;width:100%;}
            .bsr-item{
                width:100%;box-sizing:border-box;
                display:grid;
                grid-template-columns:58px minmax(105px,145px) minmax(0,1fr);
                align-items:center;gap:12px;
                padding:11px 14px;min-height:70px;border-radius:15px;
                background:rgba(255,255,255,.022);
                border:1.5px solid rgba(255,255,255,.045);
                box-shadow:0 10px 28px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.02);
            }
            .bsr-item:hover{
                background:linear-gradient(135deg,rgba(99,102,241,.16),rgba(255,255,255,.035));
                border-color:rgba(99,102,241,.42);
                transform:translateY(-1px);
            }
            .bsr-item-av img{width:50px;height:50px;border-width:2px;}
            .bsr-item-dot{width:12px;height:12px;bottom:3px;right:3px;}
            .bsr-item-name{
                text-align:left;max-width:none;width:auto;
                font-size:14px;font-weight:900;color:rgba(255,255,255,.94);
                letter-spacing:-.01em;
            }
            .bsr-item-meta{
                display:flex;
                flex-direction:row;
                align-items:center;
                justify-content:flex-start;
                gap:8px;
                width:100%;
                min-width:0;
                overflow:hidden;
                white-space:nowrap;
            }
            .bsr-rank-pill,.bsr-roles-pill,.bsr-lang-pill,.bsr-time-pill,.bsr-orders-pill,.bsr-rating-pill{
                justify-content:flex-start;font-size:11px;font-weight:850;padding:8px 10px;min-height:38px;border-radius:11px;
                color:rgba(255,255,255,.84);box-sizing:border-box;width:max-content;max-width:none;gap:7px;flex:0 0 auto;
                background:rgba(255,255,255,.065);border:1px solid rgba(255,255,255,.11);
                box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
            }
            .bsr-rank-pill{max-width:none;}
            .bsr-rank-label{overflow:hidden;text-overflow:ellipsis;}
            .bsr-roles-pill{padding-left:9px;padding-right:9px;}
            .bsr-lang-pill{padding-left:9px;padding-right:9px;}
            .bsr-rank-pill img{width:21px;height:21px;}
            .bsr-role-icons,.bsr-lang-icons{gap:6px;flex-wrap:nowrap;}
            .bsr-role-icons img{width:19px;height:19px;opacity:.92;flex:0 0 auto;}
            .bsr-lang-icons img{width:20px;height:20px;border-radius:50%;}
            .bsr-pill-icon{font-size:14px;color:rgba(255,255,255,.58);width:15px;text-align:center;flex-shrink:0;}
            .bsr-profile-btn{
                display:inline-flex;align-items:center;justify-content:center;
                min-height:38px;padding:8px 16px;border-radius:11px;width:auto;box-sizing:border-box;margin-left:auto;flex:0 0 auto;
                border:1px solid rgba(99,102,241,.6);background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(99,102,241,.07));
                color:#a5b4fc!important;text-decoration:none!important;
                font-size:10px;font-weight:950;white-space:nowrap;letter-spacing:.03em;text-transform:uppercase;
            }
            .bsr-profile-btn:hover{background:rgba(99,102,241,.22);border-color:rgba(99,102,241,.9);color:#c7d2fe!important;}
            .bsr-footer{padding:12px 28px 18px;}
            .bsr-any-btn{min-height:42px;border-radius:12px;font-size:13px;}
            .bsr-item-check{top:50%;right:12px;transform:translateY(-50%);}
        }
        @media(min-width:1180px){
            #bsr-panel{width:min(1160px,calc(100vw - 56px));max-width:1160px;}
            .bsr-ph{padding-left:36px;padding-right:36px;}
            .bsr-search-wrap{margin-left:36px;margin-right:36px;}
            .bsr-grid-wrap{padding-left:36px;padding-right:36px;}
            .bsr-footer{padding-left:36px;padding-right:36px;}
            .bsr-item{grid-template-columns:62px minmax(120px,160px) minmax(0,1fr);gap:14px;padding:12px 16px;min-height:74px;}
            .bsr-item-av img{width:54px;height:54px;}
            .bsr-item-name{font-size:14px;}
            .bsr-item-meta{gap:8px;}
            .bsr-rank-pill,.bsr-roles-pill,.bsr-lang-pill,.bsr-time-pill,.bsr-orders-pill,.bsr-rating-pill,.bsr-profile-btn{font-size:11px;min-height:39px;border-radius:12px;}
            .bsr-rank-pill img{width:22px;height:22px;}
            .bsr-role-icons img{width:20px;height:20px;flex:0 0 auto;}
            .bsr-lang-icons img{width:21px;height:21px;}
        }
        @media(min-width:1500px){
            #bsr-panel{width:min(1180px,calc(100vw - 72px));max-width:1180px;}
            .bsr-item{grid-template-columns:62px minmax(130px,175px) minmax(0,1fr);}
            .bsr-item-meta{gap:9px;}
        }

        /* selected checkmark badge */
        .bsr-item-check{
            position:absolute;top:6px;right:6px;width:16px;height:16px;border-radius:50%;
            background:rgba(99,102,241,.95);display:none;align-items:center;justify-content:center;
            font-size:9px;color:#fff;
        }
        .bsr-item.sel .bsr-item-check{display:flex;}

        /* no results */
        .bsr-no-results{
            text-align:center;padding:28px;color:rgba(255,255,255,.3);font-size:13px;display:none;
        }

        /* ── Footer ── */
        .bsr-footer{
            padding:12px 20px max(20px,env(safe-area-inset-bottom));flex-shrink:0;
            border-top:1px solid rgba(255,255,255,.06);
            display:flex;gap:10px;
        }
        .bsr-any-btn{
            flex:1;padding:11px;border-radius:10px;
            border:1px solid rgba(255,255,255,.045)!important;background:rgba(255,255,255,.022)!important;
            color:rgba(255,255,255,.5)!important;font-size:13px;font-weight:600;
            cursor:pointer;transition:.15s;font-family:inherit;
        }
        .bsr-any-btn:hover{border-color:rgba(255,255,255,.09)!important;color:rgba(255,255,255,.8)!important;background:rgba(255,255,255,.04)!important;}
        </style>

        <script>
        (function(){
            var allData  = [];
            var boostersLoaded = false;
            var boostersLoading = false;
            var orderSummaryGame = <?= json_encode($_summaryGameRaw) ?>;
            var orderSummaryFormId = <?= (int)$formId ?>;
            var defaultAv = 'https://lolboost.gg/public/uploads/icons/default.png';
            var L = {
                title:  <?= json_encode(t('Available Boosters')) ?>,
                filterAll: <?= json_encode(t('All')) ?>,
                filterOnline: <?= json_encode(t('Online')) ?>,
                filterOffline: <?= json_encode(t('Offline')) ?>,
                srch:   <?= json_encode(t('Search boosters...')) ?>,
                any:    <?= json_encode(t('Any Available')) ?>,
                anyBtn: <?= json_encode(t('Any Available Booster')) ?>,
                none:   <?= json_encode(t('No boosters found')) ?>,
                onlSec: <?= json_encode(t('Online')) ?>,
                offSec: <?= json_encode(t('Offline')) ?>,
            };

            var overlayEl = document.createElement('div');
            overlayEl.id = 'bsr-overlay';
            overlayEl.innerHTML =
                '<div id="bsr-panel">'+
                    '<div class="bsr-ph">'+
                        '<div>'+
                            '<div class="bsr-ph-title">'+
                                '<span class="bsr-pulse" id="bsr-pulse"></span>'+
                                L.title+
                            '</div>'+
                            '<div class="bsr-filter-pills" id="bsr-filter-pills">'+
                                '<button type="button" class="bsr-filter-pill active" data-filter="all"><span class="bsr-filter-pill-dot"></span>'+L.filterAll+'</button>'+
                                '<button type="button" class="bsr-filter-pill" data-filter="online"><span class="bsr-filter-pill-dot"></span>'+L.filterOnline+'</button>'+
                                '<button type="button" class="bsr-filter-pill" data-filter="offline"><span class="bsr-filter-pill-dot"></span>'+L.filterOffline+'</button>'+
                            '</div>'+
                        '</div>'+
                        '<button type="button" class="bsr-close" id="bsr-close-btn">'+
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>'+
                        '</button>'+
                    '</div>'+
                    '<div class="bsr-search-wrap">'+
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>'+
                        '<input type="text" class="bsr-search-inp" id="bsr-search-inp" placeholder="'+L.srch+'">'+
                        '<button type="button" class="bsr-search-clear" id="bsr-search-clear">&times;</button>'+
                    '</div>'+
                    '<div class="bsr-grid-wrap" id="bsr-grid-wrap">'+
                        '<div id="bsr-grid-online"></div>'+
                        '<div id="bsr-grid-offline"></div>'+
                        '<div class="bsr-no-results" id="bsr-no-results">'+L.none+'</div>'+
                    '</div>'+
                    '<div class="bsr-footer">'+
                        '<button type="button" class="bsr-any-btn" id="bsr-any-btn">'+L.anyBtn+'</button>'+
                    '</div>'+
                '</div>';

            function init(){
                document.body.appendChild(overlayEl);

                var hidInp   = document.getElementById('bsr-hidden');
                var rowEl    = document.getElementById('bsr-row');
                var rowAv    = document.getElementById('bsr-avatar');
                var rowName  = document.getElementById('bsr-name');
                var rowDot   = document.getElementById('bsr-dot');
                var closeBtn = document.getElementById('bsr-close-btn');
                var srchInp  = document.getElementById('bsr-search-inp');
                var srchClr  = document.getElementById('bsr-search-clear');
                var anyBtn   = document.getElementById('bsr-any-btn');
                var noRes    = document.getElementById('bsr-no-results');
                var onlineWrap  = document.getElementById('bsr-grid-online');
                var offlineWrap = document.getElementById('bsr-grid-offline');
                var filterPills = Array.from(document.querySelectorAll('.bsr-filter-pill'));
                var activeFilter = 'all';
                var selId    = '';
                var items    = [];

                function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
                function openModal(){
                    overlayEl.classList.add('bsr-open');
                    document.body.style.overflow='hidden';
                    loadBoostersOnce();
                }
                function closeModal() { overlayEl.classList.remove('bsr-open'); document.body.style.overflow=''; }

                rowEl.addEventListener('click', openModal);
                closeBtn.addEventListener('click', closeModal);
                anyBtn.addEventListener('click', function(){ selId=''; hidInp.value=''; deselectAll(); updateRow(null); closeModal(); });

                function getLocalTime(tz){
                    try { return new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit',timeZone:tz}); }
                    catch(e){ return '—'; }
                }
                function updateRow(b){
                    if(!b){
                        rowAv.src=defaultAv; rowName.textContent=L.any;
                        rowDot.className='bsr-dot'; rowEl.classList.remove('has-sel');
                    } else {
                        rowAv.src=b.icon||defaultAv; rowName.textContent=b.name;
                        rowDot.className='bsr-dot'+(b.online?' on':''); rowEl.classList.add('has-sel');
                    }
                }
                function deselectAll(){
                    items.forEach(function(el){ el.classList.remove('sel'); });
                }
                function selectBooster(id, b){
                    selId=String(id); hidInp.value=selId;
                    deselectAll(); updateRow(b);
                    items.forEach(function(el){ if(String(el.dataset.id)===selId) el.classList.add('sel'); });
                    setTimeout(closeModal, 220);
                }

                function buildSection(arr, wrapper, label, showDot){
                    if(!arr.length){ wrapper.style.display='none'; return; }
                    var hd = document.createElement('div');
                    hd.className = 'bsr-sec-hd';
                    hd.innerHTML = (showDot ? '<span class="bsr-sec-hd-dot"></span>' : '') + esc(label) + ' <span style="color:rgba(255,255,255,.5);font-weight:500;">('+arr.length+')</span>';
                    wrapper.appendChild(hd);
                    var grid = document.createElement('div');
                    grid.className = 'bsr-grid';
                    arr.forEach(function(b){
                        var lt = b.tz ? getLocalTime(b.tz) : '';
                        var showRoles = b.game === 'lol';
                        var roles = showRoles && Array.isArray(b.roles) ? b.roles : [];
                        var rolesText = showRoles ? (b.roles_text || roles.map(function(r){ return r ? r.charAt(0).toUpperCase()+r.slice(1) : ''; }).filter(Boolean).join(' / ')) : '';
                        var langs = Array.isArray(b.languages) ? b.languages : [];
                        var langsText = b.languages_text || langs.map(function(l){ return l ? l.charAt(0).toUpperCase()+l.slice(1) : ''; }).filter(Boolean).join(' / ');
                        var roleIcons = roles.map(function(r){
                            return '<img src="<?= ASSET_URL ?>/core/main/img/lol/roles/'+esc(r)+'.png" alt="'+esc(r)+'" title="'+esc(r ? r.charAt(0).toUpperCase()+r.slice(1) : '')+'">';
                        }).join('');
                        var langIcons = langs.slice(0, 3).map(function(l){
                            return '<img src="<?= ASSET_URL ?>/core/main/img/languages/'+esc(l)+'.png" alt="'+esc(l)+'" title="'+esc(l ? l.charAt(0).toUpperCase()+l.slice(1) : '')+'" onerror="this.style.display=\'none\'">';
                        }).join('');
                        if (langs.length > 3) { langIcons += '<span class="bsr-pill-icon" title="'+esc(langsText)+'">+'+esc(String(langs.length - 3))+'</span>'; }
                        var rankHtml = b.rank ? '<span class="bsr-rank-pill" title="'+esc(b.rank)+'">'+(b.rank_img ? '<img src="'+esc(b.rank_img)+'" alt="" onerror="this.style.display=\'none\'">' : '')+'<span class="bsr-rank-label">'+esc(b.rank)+'</span></span>' : '';
                        var rolesHtml = showRoles ? (roleIcons ? '<span class="bsr-roles-pill" title="'+esc(rolesText)+'"><span class="bsr-role-icons">'+roleIcons+'</span><span class="bsr-roles-label">'+esc(rolesText)+'</span></span>' : '') : '';
                        var langHtml = langIcons ? '<span class="bsr-lang-pill" title="'+esc(langsText)+'"><span class="bsr-lang-icons">'+langIcons+'</span></span>' : '<span class="bsr-lang-pill" title="No languages">—</span>';
                        var ordersHtml = '<span class="bsr-orders-pill" title="Completed Orders"><i class="fa-duotone fa-medal bsr-pill-icon"></i>'+esc(String(b.completed || 0))+' orders</span>';
                        var ratingValRaw = (b.rating !== undefined && b.rating !== null && b.rating !== '') ? b.rating : 5.0;
                        var ratingNum = parseFloat(ratingValRaw);
                        var ratingVal = isNaN(ratingNum) ? '5.0' : ratingNum.toFixed(1);
                        var ratingHtml = '<span class="bsr-rating-pill" title="Rating"><i class="fa-solid fa-star bsr-pill-icon"></i>'+esc(ratingVal)+'</span>';
                        var timeHtml = lt ? '<span class="bsr-time-pill" title="Local time"><i class="fa-duotone fa-clock bsr-pill-icon"></i>'+esc(lt)+'</span>' : '';
                        var profileHtml = '<a class="bsr-profile-btn" href="'+esc(b.profile || ('<?= BASE_URL ?>/boosters/'+b.id))+'" target="_blank" rel="noopener">View Profile</a>';
                        var el = document.createElement('div');
                        el.className = 'bsr-item';
                        el.dataset.id = b.id;
                        el.dataset.name = ((b.name||'')+' '+(b.rank||'')+' '+rolesText+' '+langsText).toLowerCase();
                        el.dataset.status = b.online ? 'online' : 'offline';
                        el.innerHTML =
                            '<div class="bsr-item-check">&#10003;</div>'+
                            '<div class="bsr-item-av">'+
                                '<img src="'+esc(b.icon||defaultAv)+'" alt="">'+
                                '<span class="bsr-item-dot'+(b.online?' on':'')+'"></span>'+
                            '</div>'+
                            '<div class="bsr-item-name" title="'+esc(b.name)+'">'+esc(b.name)+'</div>'+
                            '<div class="bsr-item-meta">'+rankHtml+rolesHtml+langHtml+ordersHtml+ratingHtml+timeHtml+profileHtml+'</div>';
                        var profileLink = el.querySelector('.bsr-profile-btn');
                        if (profileLink) {
                            profileLink.addEventListener('click', function(ev){ ev.stopPropagation(); });
                        }
                        el.addEventListener('click', function(){
                            if(el.classList.contains('sel')){ selId=''; hidInp.value=''; deselectAll(); updateRow(null); return; }
                            selectBooster(b.id, b);
                        });
                        grid.appendChild(el);
                        items.push(el);
                    });
                    wrapper.appendChild(grid);
                }

                function renderBoosters(){
                    items = [];
                    onlineWrap.innerHTML = '';
                    offlineWrap.innerHTML = '';
                    onlineWrap.style.display = '';
                    offlineWrap.style.display = '';
                    noRes.style.display = 'none';

                    var onlineBoosters  = allData.filter(function(b){ return  b.online; });
                    var offlineBoosters = allData.filter(function(b){ return !b.online; });
                    buildSection(onlineBoosters,  onlineWrap,  L.onlSec, true);
                    buildSection(offlineBoosters, offlineWrap, L.offSec, false);

                    filterPills.forEach(function(btn){
                        var baseLabel = btn.getAttribute('data-base-label');
                        if (!baseLabel) {
                            baseLabel = btn.textContent.replace(/\s*\(\d+\)\s*$/, '');
                            btn.setAttribute('data-base-label', baseLabel);
                        }
                        var count = allData.length;
                        if (btn.dataset.filter === 'online') count = onlineBoosters.length;
                        if (btn.dataset.filter === 'offline') count = offlineBoosters.length;
                        btn.innerHTML = '<span class="bsr-filter-pill-dot"></span>' + esc(baseLabel) + ' (' + count + ')';
                    });

                    filterItems(srchInp.value || '');
                }

                function loadBoostersOnce(){
                    if (boostersLoaded || boostersLoading) return;
                    boostersLoading = true;
                    noRes.style.display = 'block';
                    noRes.textContent = 'Loading boosters...';

                    var fd = new FormData();
                    fd.append('action', 'load_order_summary_boosters');
                    fd.append('game', orderSummaryGame);
                    fd.append('form_id', orderSummaryFormId);

                    fetch('<?= BASE_URL ?>/ajax', {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    }).then(function(r){ return r.json(); })
                      .then(function(res){
                          allData = (res && res.success && Array.isArray(res.boosters)) ? res.boosters : [];
                          boostersLoaded = true;
                          boostersLoading = false;
                          noRes.textContent = L.none;
                          renderBoosters();
                      }).catch(function(){
                          boostersLoading = false;
                          noRes.textContent = L.none;
                      });
                }

                function filterItems(q){
                    var any = false;
                    q = (q || '').toLowerCase();
                    items.forEach(function(el){
                        var matchesSearch = !q || (el.dataset.name||'').includes(q);
                        var matchesStatus = activeFilter === 'all' || (el.dataset.status || '') === activeFilter;
                        var m = matchesSearch && matchesStatus;
                        el.classList.toggle('hidden', !m);
                        if(m) any=true;
                    });
                    noRes.style.display = any ? 'none' : 'block';
                    [onlineWrap, offlineWrap].forEach(function(w){
                        var vis = Array.from(w.querySelectorAll('.bsr-item')).some(function(e){ return !e.classList.contains('hidden'); });
                        w.style.display = vis ? '' : 'none';
                    });
                }
                filterPills.forEach(function(btn){
                    btn.addEventListener('click', function(){
                        activeFilter = btn.dataset.filter || 'all';
                        filterPills.forEach(function(b){ b.classList.toggle('active', b === btn); });
                        filterItems(srchInp.value);
                    });
                });
                srchInp.addEventListener('input', function(){
                    var q = this.value;
                    srchClr.classList.toggle('vis', q.length > 0);
                    filterItems(q);
                });
                srchClr.addEventListener('click', function(){
                    srchInp.value=''; srchClr.classList.remove('vis'); filterItems('');
                });
                filterItems('');
            }

            if(document.readyState==='loading'){
                document.addEventListener('DOMContentLoaded', init);
            } else { init(); }
        })();
        </script>

        <div class="completion-box" id="hide-sticky" style="margin-top:16px;">
            <img src="<?= ASSET_URL ?>/website/images/boost-forms/estimate-clock.svg" alt="completion_icon">
            <span class="text"><?= t('Completion Time:') ?> <span id="completion-time"><?= t($completionText) ?></span></span>
        </div>

        <style>
        .trust-badges-summary {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            margin-top: 14px;
        }

        .lb-shield-trigger {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: rgba(255, 255, 255, .78);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            cursor: help;
            outline: none;
        }

        .lb-shield-trigger i {
            color: #00e6a8;
            font-size: 15px;
        }

        .lb-shield-trigger__chev {
            color: rgba(255, 255, 255, .42);
            font-size: 11px;
            transition: transform .16s ease;
        }

        .lb-shield-trigger:hover,
        .lb-shield-trigger:focus-visible {
            color: rgba(255, 255, 255, .96);
        }

        .lb-shield-trigger:hover .lb-shield-trigger__chev,
        .lb-shield-trigger:focus-visible .lb-shield-trigger__chev {
            transform: translateY(1px);
        }

        .lb-shield-tooltip {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 12px);
            width: min(340px, calc(100vw - 34px));
            transform: translateX(-50%) translateY(6px);
            padding: 18px;
            border-radius: 18px;
            background: #10111b;
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 18px 50px rgba(0, 0, 0, .38);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
            z-index: 50;
            text-align: left;
        }

        .lb-shield-tooltip:after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -7px;
            width: 14px;
            height: 14px;
            transform: translateX(-50%) rotate(45deg);
            background: #10111b;
            border-right: 1px solid rgba(255, 255, 255, .08);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .lb-shield-trigger:hover .lb-shield-tooltip,
        .lb-shield-trigger:focus-visible .lb-shield-tooltip,
        .lb-shield-trigger:focus-within .lb-shield-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .lb-shield-tooltip__brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            color: #00e6a8;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .lb-shield-tooltip__title {
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            line-height: 1.35;
            margin-bottom: 14px;
        }

        .lb-shield-tooltip__item {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .lb-shield-tooltip__itemIcon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 230, 168, .12);
            color: #00e6a8;
            flex: 0 0 28px;
            font-size: 12px;
        }

        .lb-shield-tooltip__itemTitle {
            display: block;
            color: rgba(255, 255, 255, .96);
            font-size: 13px;
            font-weight: 900;
            line-height: 1.25;
        }

        .lb-shield-tooltip__itemText {
            display: block;
            margin-top: 2px;
            color: rgba(255, 255, 255, .54);
            font-size: 11px;
            font-weight: 600;
            line-height: 1.35;
        }

        .trustpilot-banner--chip {
            display: flex;
            justify-content: center;
            text-decoration: none;
            width: 100%;
        }

        .trustpilot-banner--chip:hover,
        .trustpilot-banner--chip:active,
        .trustpilot-banner--chip:visited {
            text-decoration: none;
            color: inherit;
        }

        .tpBadge--summary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 15px;
            min-height: 42px;
            width: auto;
            max-width: 100%;
            border-radius: 999px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .10);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .14);
            transition: transform .16s ease, border-color .16s ease, background .16s ease;
        }

        .trustpilot-banner--chip:hover .tpBadge--summary {
            transform: translateY(-1px);
            border-color: rgba(0, 182, 122, .30);
            background: rgba(0, 182, 122, .08);
        }

        .tpBadge__excellent {
            font-weight: 900;
            color: rgba(255, 255, 255, .96);
            font-size: 13px;
            line-height: 1;
            white-space: nowrap;
        }

        .tpBadge__stars {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
        }

        .tpBadge__stars i {
            font-size: 11px;
            color: #00b67a;
        }

        .tpBadge__reviews {
            color: rgba(255, 255, 255, .72);
            font-weight: 800;
            font-size: 12px;
            line-height: 1;
            white-space: nowrap;
        }

        .tpBadge__tpIcon {
            width: 24px;
            height: 24px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #00b67a;
            color: #fff;
            font-size: 13px;
            font-weight: 900;
            line-height: 1;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .trust-badges-summary {
                margin-top: 16px;
                gap: 11px;
            }

            .lb-shield-trigger {
                font-size: 12px;
            }

            .tpBadge--summary {
                min-height: 42px;
                padding: 10px 13px;
                gap: 8px;
            }

            .tpBadge__excellent {
                font-size: 12px;
            }

            .tpBadge__reviews {
                font-size: 11px;
            }

            .tpBadge__stars i {
                font-size: 10px;
            }

            .tpBadge__tpIcon {
                width: 24px;
                height: 24px;
                font-size: 13px;
            }
        }

        .cashback_info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.3vw 0.25vw;
            margin-top: -0.35vw;
            margin-bottom: 0.6vw;
            background: transparent;
            border: 0;
        }

        .cashback_info p {
            display: flex;
            align-items: center;
            gap: 0.4vw;
            margin: 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9vw;
            font-weight: 600;
            line-height: 1;
        }

        .cashback_info img {
            width: 1vw;
            height: auto;
            object-fit: contain;
            flex-shrink: 0;
        }

        .cashback_info small {
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.72vw;
            font-weight: 600;
            margin-left: 0.12vw;
        }

        .cashback_info span {
            color: #00e6a8;
            font-size: 0.95vw;
            font-weight: 700;
            line-height: 1;
        }

        @media (max-width: 768px) {
            .cashback_info {
                padding: 0 2.326vw;
                margin-top: 1.163vw;
                margin-bottom: 0;
            }

            .cashback_info p {
                gap: 2.326vw;
                font-size: 3.721vw;
                font-weight: 600;
                line-height: 4.651vw;
            }

            .cashback_info img {
                width: 4.186vw;
            }

            .cashback_info small {
                font-size: 3.023vw;
                margin-left: 0.698vw;
            }

            .cashback_info span {
                font-size: 3.721vw;
                font-weight: 700;
                line-height: 4.651vw;
            }

            .cashback_info + .buy-now {
                margin-top: 4.651vw;
            }
        }
        </style>

        <hr>

        <?php
        $cashback_percent = 2;

        if (defined('CLIENT_DATA') && CLIENT_DATA != false && !empty(CLIENT_DATA['loyalty_rank_id'])) {
            $cashback_rank = db_get_row('loyalty_ranks', ['id' => CLIENT_DATA['loyalty_rank_id']], 1);
            if (!empty($cashback_rank['cashback']) && is_numeric($cashback_rank['cashback'])) {
                $cashback_percent = (float)$cashback_rank['cashback'];
            }
        }
        ?>

        <div class="totals">
            <p><img src="<?= ASSET_URL ?>/website/images/boost-forms/total.svg" alt="total_icon"><?= t('Total Price') ?></p>
            <span class="price total-price" id="total-price">€0.00</span>
        </div>

        <div class="cashback_info" data-cashback-percent="<?= htmlspecialchars((string)$cashback_percent, ENT_QUOTES, 'UTF-8') ?>">
            <p>
                <img src="https://lolboost.gg/public/assets/core/main/img/coin.png" alt="LB Coins">
                <?= t('Cashback') ?> <small>(<?= rtrim(rtrim(number_format($cashback_percent, 2, '.', ''), '0'), '.') ?>%)</small>
            </p>
            <span id="cashback_amount"><?= t('€0.00') ?></span>
        </div>

        <button type="submit" class="btn primary buy-now" id="sticky_start_boost"><?= t('Buy Now') ?></button>

        <div class="trust-badges-summary">
            <div class="lb-shield-trigger" tabindex="0" aria-label="<?= t('Your payment is safe with LOLBOOST.GG Shield') ?>">
                <i class="fa-duotone fa-shield-check" aria-hidden="true"></i>
                <span><?= t('Your payment is safe with LOLBOOST.GG Shield') ?></span>
                <i class="fa-solid fa-chevron-down lb-shield-trigger__chev" aria-hidden="true"></i>

                <div class="lb-shield-tooltip" role="tooltip">
                    <div class="lb-shield-tooltip__brand">
                        <i class="fa-duotone fa-shield-check" aria-hidden="true"></i>
                        <span><?= t('LOLBOOST.GG Shield') ?></span>
                    </div>
                    <div class="lb-shield-tooltip__title"><?= t('Your payment stays safe until the service is completed.') ?></div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('Secure payment & refund') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Refunded if the service cannot be delivered.') ?></span>
                        </span>
                    </div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-user-check" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('Verified boosters') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Performance, identity and trust checks.') ?></span>
                        </span>
                    </div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('Private & encrypted') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Secure checkout and encrypted payment handling.') ?></span>
                        </span>
                    </div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('24/7 support') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Live chat and Discord support around the clock.') ?></span>
                        </span>
                    </div>
                </div>
            </div>

            <a href="https://www.trustpilot.com/review/lolboost.gg" target="_blank" rel="noopener noreferrer" class="trustpilot-banner trustpilot-banner--chip" aria-label="Trustpilot reviews">
                <span class="tpBadge tpBadge--summary">
                    <span class="tpBadge__excellent"><?= t('Excellent') ?></span>
                    <span class="tpBadge__stars" aria-hidden="true">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </span>
                    <span class="tpBadge__reviews"><?= t('510 Reviews on') ?></span>
                    <span class="tpBadge__tpIcon" aria-hidden="true">★</span>
                </span>
            </a>
        </div>
    </div>

    <div class="payment-gateways">
        <div class="top">
            <img src="<?= ASSET_URL ?>/website/images/boost-forms/secure-payments.svg" alt="secure-payments-icon">

            <div class="text">
                <h5><?= t('Safe & Secure Payments') ?></h5>
                <p><?= t('100% secure checkout powered by Stripe & Paypal') ?></p>
            </div>
        </div>

        <img src="<?= ASSET_URL ?>/website/images/boost-forms/gateways.png" alt="gateway-logos">
    </div>
</div>

<script>
(function(){
    var box = document.querySelector('.cashback_info');
    var priceEl = document.getElementById('total-price');
    var amount = document.getElementById('cashback_amount');
    if (!box || !priceEl || !amount) return;
    var percent = parseFloat(box.getAttribute('data-cashback-percent')) || 2;
    function update(){
        var priceText = (priceEl.textContent || '').replace(/[^0-9.,]/g, '').replace(',', '.');
        var currency = (priceEl.textContent || '').replace(/[0-9.,\s]/g, '') || '€';
        var price = parseFloat(priceText) || 0;
        var cashback = price * percent / 100;
        amount.textContent = currency + cashback.toFixed(2);
    }
    update();
    new MutationObserver(update).observe(priceEl, { childList: true, characterData: true, subtree: true });
})();
</script>

<style>
.lb-selection-modal{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;padding:18px;opacity:0;visibility:hidden;pointer-events:none}
.lb-selection-modal.show{display:flex!important;visibility:visible!important;pointer-events:auto!important;opacity:1!important}
.lb-selection-modal:not(.show){display:none!important;visibility:hidden!important;pointer-events:none!important;opacity:0!important}
.lb-selection-modal:not(.show) .lb-selection-modal__overlay{display:none!important}
html.lb-selection-modal-open,body.lb-selection-modal-open{overflow:hidden}
.lb-selection-modal__overlay{position:absolute;inset:0;z-index:1;background:rgba(0,0,0,.6);backdrop-filter:none}
.lb-selection-count{font-size:12px;font-weight:800;color:rgba(255,255,255,.55)}

/* ── Heroes & Roles panel ─────────────────────────────────────────────── */
.lb-hr-panel{position:relative;z-index:2;width:min(600px,100%);max-height:min(680px,88vh);overflow:hidden;border:1px solid rgba(124,92,255,.28);border-radius:18px;background:#15131f;box-shadow:0 24px 80px rgba(0,0,0,.55);color:#fff;display:flex;flex-direction:column;opacity:1!important}
.lb-hr-panel__head{display:flex;align-items:center;gap:12px;padding:20px 20px 16px}
.lb-hr-panel__title{display:flex;align-items:center;gap:12px;flex:1;min-width:0}
.lb-hr-panel__icon{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,#3a3550,#25223a);border:1px solid rgba(255,255,255,.1);color:#fff;font-size:14px}
.lb-hr-panel__head h4{margin:0;font-size:17px;font-weight:800;color:#fff;opacity:1}
.lb-hr-panel__close{width:32px;height:32px;flex-shrink:0;border:0;border-radius:9px;background:rgba(255,255,255,.08);color:#fff;font-size:18px;line-height:1;cursor:pointer;transition:background .15s}
.lb-hr-panel__close:hover{background:rgba(255,255,255,.16)}
.lb-hr-panel__body{padding:0 20px 18px;overflow-y:auto;display:flex;flex-direction:column;gap:18px}
.lb-hr-block__label{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:800;color:rgba(255,255,255,.85);margin-bottom:10px}
.lb-hr-tag-badge{display:inline-flex;align-items:center;font-size:11px;font-weight:800;padding:3px 9px;border-radius:6px;background:rgba(92,74,227,.22);color:#c7d2fe}
.lb-hr-tag-badge.free{background:rgba(34,197,94,.16);color:#4ade80}

/* Roles: icon-only button row */
.lb-hr-roles{display:grid;grid-template-columns:repeat(auto-fit,minmax(0,1fr));gap:8px}
.lb-hr-role{display:flex;align-items:center;justify-content:center;min-height:52px;border-radius:12px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.05);cursor:pointer;transition:background .15s,border-color .15s,transform .15s}
.lb-hr-role:hover{transform:translateY(-1px);border-color:rgba(92,74,227,.36)}
.lb-hr-role.is-selected{background:rgba(92,74,227,.24);border-color:rgba(124,92,255,.6)}
.lb-hr-role img{width:22px;height:22px;object-fit:contain;filter:brightness(1.6)}

/* Heroes/Legends: always-visible search + icon grid (matches the LoL champions picker) */
.lb-hr-search{display:flex;align-items:center;gap:10px;height:42px;padding:0 14px;margin-bottom:12px;border:1px solid rgba(255,255,255,.1);border-radius:12px;background:rgba(255,255,255,.05);color:rgba(255,255,255,.4)}
.lb-hr-search input{flex:1;height:100%;border:0;outline:0;background:transparent;color:#fff;font-size:13px;font-weight:600}
.lb-hr-search input::placeholder{color:rgba(255,255,255,.35)}
.lb-hr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(66px,1fr));gap:12px 8px;max-height:280px;overflow-y:auto;padding:4px 2px}
.lb-hr-grid .lb-selection-chip{display:flex;flex-direction:column;align-items:center;gap:6px;min-height:auto;padding:4px;border:none;background:transparent;border-radius:10px;font-size:11px;font-weight:600;color:rgba(255,255,255,.75);text-align:center}
.lb-hr-grid .lb-selection-chip:hover{transform:none;background:rgba(255,255,255,.06)}
.lb-hr-grid .lb-selection-chip.is-selected{background:rgba(92,74,227,.2);color:#fff}
.lb-hr-grid .lb-selection-chip img{width:48px;height:48px;border-radius:50%;object-fit:cover;background:rgba(255,255,255,.08);border:2px solid transparent}
.lb-hr-grid .lb-selection-chip.is-selected img{border-color:#7c5cfc}
.lb-hr-grid .lb-selection-chip span{display:block;width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

.lb-hr-panel__foot{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:14px 20px;border-top:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.02)}
.lb-hr-panel__foot .lb-selection-count{margin-right:auto}
.lb-hr-btn{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;border:1px solid transparent}
.lb-hr-btn.ghost{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);color:#e2e8f0}
.lb-hr-btn.ghost:hover{background:rgba(255,255,255,.1)}
.lb-hr-btn.primary{background:#5c4ae3;color:#fff}
.lb-hr-btn.primary:hover{background:#6c5af0}

@media(max-width:560px){.lb-hr-panel{max-height:92vh}}
</style>

<script>
(function () {
    document.dispatchEvent(new CustomEvent('lb:order-summary-ready'));
    // ── Heroes/Roles/Agents picker ───────────────────────────────────────────
    function selectionKeyFromCheckbox(cb) {
        return cb.getAttribute('data-selection-key') || cb.id || cb.name;
    }
    function cleanupSelectionModalState() {
        // Close/remove possible legacy modal/backdrop opened by lol.js (#is_champions_roles handler).
        // The order-summary checkbox keeps the original name for form submission, but uses a unique id,
        // so the legacy jQuery selector no longer opens the old modal. This cleanup also fixes pages
        // where the old handler already created a dark backdrop before this script loaded.
        var legacyModal = document.getElementById('champions_roles_modal');
        if (legacyModal) {
            legacyModal.classList.remove('show', 'active', 'is-active', 'open');
            legacyModal.setAttribute('aria-hidden', 'true');
            legacyModal.style.setProperty('display', 'none', 'important');
            legacyModal.style.setProperty('visibility', 'hidden', 'important');
            legacyModal.style.setProperty('pointer-events', 'none', 'important');
            legacyModal.style.setProperty('opacity', '0', 'important');
        }
        document.querySelectorAll('.modal-backdrop, .modal-backdrop.show, .iziModal-overlay, .tingle-modal-box__footer, .modal-overlay, .modal-mask, .modal-bg').forEach(function (el) {
            if (el && el.parentNode) el.parentNode.removeChild(el);
        });
        var anyOpen = document.querySelector('.lb-selection-modal.show');
        if (!anyOpen) {
            document.documentElement.classList.remove('lb-selection-modal-open');
            document.body.classList.remove('lb-selection-modal-open');
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            document.querySelectorAll('.modal-backdrop, .modal-backdrop.show').forEach(function (el) {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            });
        }
    }
    function closeSelectionModal(key) {
        var modal = document.getElementById('lb_sel_modal_' + key);
        if (!modal) return;
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        modal.style.setProperty('display', 'none', 'important');
        modal.style.setProperty('visibility', 'hidden', 'important');
        modal.style.setProperty('pointer-events', 'none', 'important');
        modal.style.setProperty('opacity', '0', 'important');
        cleanupSelectionModalState();
    }
    function closeAllSelectionModals() {
        document.querySelectorAll('.lb-selection-modal').forEach(function (modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.style.setProperty('display', 'none', 'important');
            modal.style.setProperty('visibility', 'hidden', 'important');
            modal.style.setProperty('pointer-events', 'none', 'important');
            modal.style.setProperty('opacity', '0', 'important');
        });
        cleanupSelectionModalState();
    }
    window.closeSelectionModal = closeSelectionModal;
    window.closeAllSelectionModals = closeAllSelectionModals;
    function openSelectionModal(key) {
        cleanupSelectionModalState();
        var modal = document.getElementById('lb_sel_modal_' + key);
        if (!modal) return;
        if (modal.parentNode !== document.body) document.body.appendChild(modal);
        modal.style.removeProperty('display');
        modal.style.removeProperty('visibility');
        modal.style.removeProperty('pointer-events');
        modal.style.removeProperty('opacity');
        modal.style.display = 'flex';
        modal.style.visibility = 'visible';
        modal.style.pointerEvents = 'auto';
        modal.style.opacity = '1';
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('show');
        document.documentElement.classList.add('lb-selection-modal-open');
        document.body.classList.add('lb-selection-modal-open');
        updateSelectionState(key);
    }
    function selectionInputs(key, group) {
        var sel = 'input[data-key="' + key + '"]' + (group ? '[data-group="' + group + '"]' : '');
        return document.querySelectorAll(sel);
    }
    function updateSelectionState(key) {
        var checkedCount = Array.prototype.filter.call(selectionInputs(key), function (i) { return i.checked; }).length;
        document.querySelectorAll('[data-lb-selection-count="' + key + '"]').forEach(function(el){
            el.textContent = checkedCount + ' <?= addslashes(t('selected')) ?>';
        });
        document.querySelectorAll('#lb_sel_modal_' + key + ' .lb-selection-chip').forEach(function(label){
            var input = document.getElementById(label.getAttribute('for'));
            label.classList.toggle('is-selected', !!(input && input.checked));
        });
        document.querySelectorAll('#lb_sel_modal_' + key + ' .lb-hr-role').forEach(function(label){
            var input = document.getElementById(label.getAttribute('for'));
            label.classList.toggle('is-selected', !!(input && input.checked));
        });
    }
    window.toggleSelectionPicker = function(cb) {
        var key = selectionKeyFromCheckbox(cb);
        if (!cb.checked) {
            selectionInputs(key).forEach(function(i){ i.checked = false; });
            updateSelectionState(key);
            closeSelectionModal(key);
            return;
        }
        openSelectionModal(key);
    };
    function handleSelectionCloseEvent(e) {
        var closeBtn = e.target && e.target.closest ? e.target.closest('[data-lb-selection-close]') : null;
        if (!closeBtn) return false;
        var key = closeBtn.getAttribute('data-lb-selection-close');
        if (closeBtn.classList && closeBtn.classList.contains('lb-selection-done')) closeAllSelectionModals();
        else closeSelectionModal(key);
        e.preventDefault();
        e.stopPropagation();
        return true;
    }
    document.addEventListener('click', function(e) {
        handleSelectionCloseEvent(e);
    }, true);
    document.addEventListener('click', function(e) {
        if (handleSelectionCloseEvent(e)) return;

        var role = e.target && e.target.closest ? e.target.closest('.lb-hr-role') : null;
        if (role) {
            var roleInput = document.getElementById(role.getAttribute('for'));
            if (!roleInput) return;
            roleInput.checked = !roleInput.checked;
            if (roleInput.dataset.key) updateSelectionState(roleInput.dataset.key);
            document.dispatchEvent(new CustomEvent('lb:addons-updated'));
            e.preventDefault();
            return;
        }

        var chip = e.target && e.target.closest ? e.target.closest('.lb-selection-chip') : null;
        if (chip) {
            var input = document.getElementById(chip.getAttribute('for'));
            if (!input) return;
            input.checked = !input.checked;
            if (input.dataset.key) updateSelectionState(input.dataset.key);
            document.dispatchEvent(new CustomEvent('lb:addons-updated'));
            e.preventDefault();
            return;
        }
    });
    document.addEventListener('input', function(e) {
        if (!e.target || !e.target.matches('[data-lb-selection-search]')) return;
        var key = e.target.getAttribute('data-lb-selection-search');
        var term = (e.target.value || '').toLowerCase().trim();
        document.querySelectorAll('#lb_sel_modal_' + key + ' .lb-selection-chip').forEach(function(label){
            var haystack = label.getAttribute('data-lb-selection-label') || label.textContent.toLowerCase();
            label.style.display = (!term || haystack.indexOf(term) !== -1) ? '' : 'none';
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.lb-selection-modal.show').forEach(function(modal){
            closeSelectionModal(modal.id.replace('lb_sel_modal_', ''));
        });
    });
    document.addEventListener('change', function(e) {
        if (e.target && e.target.dataset && e.target.dataset.key) updateSelectionState(e.target.dataset.key);
    });

    function updateDuoVisibility() {
        var duo = document.querySelector('input[name="is_duo"]:checked');
        var isDuo = duo && duo.value === '1';
        document.querySelectorAll('.solo-option').forEach(function (el) { el.style.display = isDuo ? 'none' : ''; var input = el.querySelector('input[type="checkbox"]'); if (isDuo && input) input.checked = false; });
        document.querySelectorAll('.duo-option').forEach(function (el) { el.style.display = isDuo ? '' : 'none'; var input = el.querySelector('input[type="checkbox"]'); if (!isDuo && input) input.checked = false; });
        document.querySelectorAll('[data-label-solo]').forEach(function (el) {
            var label = isDuo ? el.getAttribute('data-label-duo') : el.getAttribute('data-label-solo');
            var badge = isDuo ? el.getAttribute('data-badge-duo') : el.getAttribute('data-badge-solo');
            var labelEl = el.querySelector('.option-label-text');
            if (labelEl && label) labelEl.textContent = label;
            var badgeEl = el.querySelector('.badge');
            if (badgeEl && badge) badgeEl.textContent = badge;
        });
    }
    document.addEventListener('change', function (e) {
        var t = e.target;
        if (t && t.name === 'is_duo') { updateDuoVisibility(); document.dispatchEvent(new CustomEvent('lb:addons-updated')); }
        if (t && t.matches && t.matches('.extra-options input[type="checkbox"]')) document.dispatchEvent(new CustomEvent('lb:addons-updated'));
        if (t && t.name === 'start_lp') {
            document.querySelectorAll('.current-summary-lp').forEach(function (el) { el.textContent = '[ ' + t.value + ' <?= addslashes($summaryPoints) ?> ]'; });
        }
        if (t && (t.name === 'matches0' || t.name === 'matches')) {
            document.querySelectorAll('.summary-games-count').forEach(function (el) { el.textContent = t.value || '1'; });
        }
    });
    document.addEventListener('lb:generic-form-changed', function () {
        var input = document.getElementById('dynamic_matches_input');
        if (!input) return;
        document.querySelectorAll('.summary-games-count').forEach(function (el) { el.textContent = input.value || '1'; });
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { updateDuoVisibility(); document.dispatchEvent(new CustomEvent('lb:addons-updated')); });
    else { updateDuoVisibility(); document.dispatchEvent(new CustomEvent('lb:addons-updated')); }
})();
</script>
