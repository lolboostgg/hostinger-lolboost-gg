<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'lol-boost generic-boost lb-boost-nav-only']) ?>

<?= $this->start('styles') ?>
<script>if('scrollRestoration'in history)history.scrollRestoration='manual';</script>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.css">
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<style>
    .duo-option { display: none; }
    .lol-boost .sticky-overview { z-index: 99 !important; }
    .generic-boost .rank-btn img[src=""],
    .generic-boost .card-header-rank[src=""] { display:none; }
    .generic-boost .rank-boost-dynamic[data-boost-mode="win"] .rank-cards > .card,
    .generic-boost .rank-boost-dynamic[data-boost-mode="placement"] .rank-cards > .card { width:100%; flex:1 1 100%; }
    .generic-boost .rank-boost-dynamic .dynamic-games-card { margin-top:1.042vw; }
    .generic-boost .rank-boost-dynamic[data-game="counter-strike-2"][data-boost-mode="placement"] .dynamic-games-card .range-slider {
        width:calc(100% - 36px);
        margin-left:18px;
        margin-right:18px;
    }
    .generic-boost .rank-boost-dynamic[data-game="counter-strike-2"][data-boost-mode="placement"] .dynamic-new-account-icon {
        color:#72c8ff;
        font-size:30px;
        line-height:1;
        filter:drop-shadow(0 0 8px rgba(59,130,246,.34));
    }
    .generic-boost .rank-boost-dynamic[data-game="counter-strike-2"][data-boost-mode="placement"] .current-new-account-icon,
    .generic-boost .order-summary .current-new-account-icon {
        color:#72c8ff;
        font-size:32px;
        line-height:1;
        filter:drop-shadow(0 0 8px rgba(59,130,246,.34));
    }
    .generic-boost .order-summary .current-new-account-icon {
        margin-right:9px;
    }
    .generic-boost .rank-boost-dynamic[data-game="counter-strike-2"][data-boost-mode="placement"][data-new-account="1"] .current-rank-img,
    .generic-boost .order-summary.is-new-account .current-summary-rank-img {
        display:none !important;
    }
    .lol-boost .form-content .boost-form .range-slider.noUi-target,
    .lol-boost .form-content .boost-form .range-slider.noUi-target .noUi-base,
    .lol-boost .form-content .boost-form .range-slider.noUi-target .noUi-connects {
        background-color:#171c33 !important;
    }
    /* CS2 groups its nav pills into Premier / Wingman / Faceit sub-categories,
       each with its own coloured label above a row of pills, separated from its
       neighbours by a vertical divider. Other games keep the original flat
       centered row untouched. */
    .generic-boost .rank-types-nav.rank-types-nav--grouped {
        flex-wrap: wrap;
        align-items: stretch;
        gap: 0;
    }
    .generic-boost .rank-types-nav .nav-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .7vw;
        position: relative;
        padding: 0 1.6vw;
    }
    .generic-boost .rank-types-nav .nav-group:first-child { padding-left: 0; }
    .generic-boost .rank-types-nav .nav-group:last-child { padding-right: 0; }
    .generic-boost .rank-types-nav .nav-group:not(:first-child)::before {
        content: '';
        position: absolute;
        left: 0;
        top: 10%;
        bottom: 10%;
        width: 1px;
        background: linear-gradient(180deg, transparent, rgba(139,124,255,.35) 25%, rgba(139,124,255,.35) 75%, transparent);
    }
    .generic-boost .rank-types-nav .nav-group__label {
        display: inline-flex;
        align-items: center;
        gap: .45vw;
        font-size: .82vw;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .09em;
        color: rgba(255,255,255,.55);
    }
    .generic-boost .rank-types-nav .nav-group__label::before {
        content: '';
        width: .5vw;
        height: .5vw;
        min-width: 6px;
        min-height: 6px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 10px currentColor;
    }
    .generic-boost .rank-types-nav .nav-group[data-cat="premier"] .nav-group__label { color:#a08bff; }
    .generic-boost .rank-types-nav .nav-group[data-cat="wingman"] .nav-group__label { color:#4fc6ff; }
    .generic-boost .rank-types-nav .nav-group[data-cat="faceit"] .nav-group__label  { color:#ff8b4d; }
    .generic-boost .rank-types-nav .nav-group__row {
        display: flex;
        gap: 1.042vw;
    }
    @media (max-width:768px) {
        .generic-boost .rank-types-nav.rank-types-nav--grouped {
            flex-direction: column;
            align-items: center;
            padding-left: 0;
        }
        .generic-boost .rank-types-nav .nav-group {
            padding: 3vw 0;
        }
        .generic-boost .rank-types-nav .nav-group:first-child { padding-top: 0; }
        .generic-boost .rank-types-nav .nav-group:last-child { padding-bottom: 0; }
        .generic-boost .rank-types-nav .nav-group:not(:first-child)::before {
            left: 10%;
            right: 10%;
            top: 0;
            bottom: auto;
            width: auto;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(139,124,255,.35) 25%, rgba(139,124,255,.35) 75%, transparent);
        }
        .generic-boost .rank-types-nav .nav-group__label {
            font-size: 3.4vw;
        }
        .generic-boost .rank-types-nav .nav-group__label::before {
            width: 1.6vw;
            height: 1.6vw;
        }
        .generic-boost .rank-types-nav .nav-group__row {
            gap: 4.651vw;
        }
    }
    .generic-boost .rank-types-nav .nav-item.coming-soon {
        cursor:not-allowed;
        opacity:.48;
        pointer-events:none;
        position:relative;
    }
    .generic-boost .rank-types-nav .nav-item.coming-soon::after {
        content:'Coming Soon';
        position:absolute;
        top:6px;
        right:6px;
        padding:2px 6px;
        border:1px solid rgba(139,124,255,.48);
        border-radius:999px;
        background:rgba(91,72,190,.22);
        color:#b9afff;
        font-size:9px;
        font-weight:800;
        line-height:1.2;
        text-transform:uppercase;
        letter-spacing:.04em;
    }
    @media (max-width:768px) {
        .generic-boost .rank-boost-dynamic .dynamic-games-card { margin-top:4.651vw; }
    }
</style>
<?= $this->end('styles') ?>

<?php
// Generic boost pages are driven by the Boost Form JSON edited in the admin panel.
// Keep the page free of hardcoded game/form IDs so newly created games update immediately.
$_gameSlug  = $game ?? ($data['game'] ?? 'generic');
$_shortMap  = ['league-of-legends' => 'lol', 'valorant' => 'val', 'teamfight-tactics' => 'tft'];
$_gameShort = $_shortMap[$_gameSlug] ?? $_gameSlug;
$_jsonData  = $data['json'] ?? [];
$_genericFormSlug = strtolower(trim((string)($data['slug'] ?? 'rank-boost')));
$_genericIsWin = $_genericFormSlug === 'win-boost';
$_genericIsPlacement = in_array($_genericFormSlug, ['placement', 'placement-boost', 'placements-boost'], true);
// Some imported multigame rows still say type=rank. The public form and summary must
// follow the route/form slug, which is the authoritative service kind.
if ($_genericIsWin) $data['type'] = 'win';
elseif ($_genericIsPlacement) $data['type'] = 'placement';

// Authoritative rank tier/division list for the newer generic-form games — overrides
// whatever placeholder rank data the form's DB row already has. Written back into
// $data['json'] so rank-dynamic.php and order-summary-multigame.php (which each read
// their own copy of $data['json']) see the exact same tiers/divisions.
if (function_exists('lb_generic_game_rank_config')) {
    $_gameRankCfg = lb_generic_game_rank_config($_gameSlug, $_genericFormSlug);
    if ($_gameRankCfg !== null) {
        if ($_gameSlug === 'overwatch-2' && $_genericIsPlacement) {
            $_gameRankCfg['ranks'] = [0 => 'Unranked'] + $_gameRankCfg['ranks'];
            $_gameRankCfg['rank_divs'] = [0 => 0] + $_gameRankCfg['rank_divs'];
            $_gameRankCfg['flat_tiers'][] = 0;
            $_gameRankCfg['start_max_tier'] = 9;
        } elseif ($_gameSlug === 'overwatch-2' && $_genericIsWin) {
            $_gameRankCfg['start_max_tier'] = 9;
        } elseif ($_gameSlug === 'rocket-league' && $_genericIsPlacement) {
            $_gameRankCfg['ranks'] = [0 => 'Unranked'] + $_gameRankCfg['ranks'];
            $_gameRankCfg['rank_divs'] = [0 => 0] + $_gameRankCfg['rank_divs'];
            $_gameRankCfg['flat_tiers'][] = 0;
            $_gameRankCfg['start_max_tier'] = 8;
        } elseif ($_gameSlug === 'counter-strike-2' && $_genericIsPlacement) {
            $_gameRankCfg['ranks'] = [0 => 'New Account'] + $_gameRankCfg['ranks'];
            $_gameRankCfg['ranks'][1] = '0 - 4,999';
            $_gameRankCfg['rank_divs'] = [0 => 0] + $_gameRankCfg['rank_divs'];
            $_gameRankCfg['flat_tiers'][] = 0;
            $_gameRankCfg['start_max_tier'] = 7;
        } elseif ($_gameSlug === 'lol-wild-rift') {
            if ($_genericIsWin) {
                // Grandmaster and Challenger are exclusive to Wild Rift Win Boost.
                $_gameRankCfg['start_max_tier'] = 10;
            } else {
                // Rank Boost ends at Master; Master has no division and cannot be
                // selected as the current rank when boosting toward it.
                unset($_gameRankCfg['ranks'][9], $_gameRankCfg['ranks'][10]);
                unset($_gameRankCfg['rank_divs'][9], $_gameRankCfg['rank_divs'][10]);
                $_gameRankCfg['flat_tiers'] = [8];
                $_gameRankCfg['start_max_tier'] = 7;
            }
        }
        $_jsonData['rank_names'] = $_gameRankCfg['ranks'];
        $_jsonData['rank_divs'] = $_gameRankCfg['rank_divs'];
        if (isset($_gameRankCfg['points_min_start'])) $_jsonData['points_min_start'] = $_gameRankCfg['points_min_start'];
        if (isset($_gameRankCfg['points_min_end'])) $_jsonData['points_min_end'] = $_gameRankCfg['points_min_end'];
        if (isset($_gameRankCfg['points_max_start'])) $_jsonData['points_max_start'] = $_gameRankCfg['points_max_start'];
        if (isset($_gameRankCfg['points_max_end'])) $_jsonData['points_max_end'] = $_gameRankCfg['points_max_end'];
        if (isset($_gameRankCfg['start_max_tier'])) $_jsonData['start_max_tier'] = $_gameRankCfg['start_max_tier'];
        if (isset($_gameRankCfg['points_max'])) $_jsonData['points_max'] = $_gameRankCfg['points_max'];
        if (isset($_gameRankCfg['points_step'])) $_jsonData['points_step'] = $_gameRankCfg['points_step'];
        $_jsonData['flat_tiers'] = $_gameRankCfg['flat_tiers'] ?? [];
        // Auto-resolve rank icons from the known local asset filenames (rank_files) so forms
        // work out of the box without the admin having to paste 17+ icon URLs by hand. Only
        // fills in tiers the admin hasn't already set explicitly in rank_icons — a deliberate
        // admin-set icon always wins.
        if (!empty($_gameRankCfg['rank_files']) && is_array($_gameRankCfg['rank_files'])) {
            $_jsonData['rank_icons'] = is_array($_jsonData['rank_icons'] ?? null) ? $_jsonData['rank_icons'] : [];
            foreach ($_gameRankCfg['rank_files'] as $_rfTier => $_rfFile) {
                if (isset($_jsonData['rank_icons'][$_rfTier]) || isset($_jsonData['rank_icons'][(string)$_rfTier])) continue;
                $_jsonData['rank_icons'][$_rfTier] = 'website/images/boosting/ranks/' . $_gameSlug . '/' . $_rfFile . '.webp';
            }
        }
        // Clear any stale custom division labels/count from the DB (e.g. plain "1..5" numbers)
        // so our roman-numeral division count/labels are the ones actually rendered.
        unset($_jsonData['division_names'], $_jsonData['division_labels'], $_jsonData['divisionNames']);
        unset($_jsonData['division_count'], $_jsonData['divisions_count'], $_jsonData['divisionCount'], $_jsonData['divisions'], $_jsonData['division']);
        // Rocket League and Fortnite progress I -> II -> III. The shared fallback
        // is LoL-style III -> II -> I, which reverses both the displayed ranks and
        // their price-row association for these two games.
        if (in_array($_gameSlug, ['rocket-league', 'fortnite'], true)) {
            $_jsonData['division_names'] = [1 => 'I', 2 => 'II', 3 => 'III'];
        }
        // lb_dynamic_rank_division_count() checks form_config.rank_divs BEFORE the top-level
        // rank_divs we just set above — a leftover nested value there would silently win for
        // every tier (not just the top one). Clear it so our top-level rank_divs is authoritative.
        if (isset($_jsonData['form_config']) && is_array($_jsonData['form_config'])) {
            unset($_jsonData['form_config']['rank_divs']);
        }
        $data['json'] = $_jsonData;
    }
}

$_mainPricing = $_jsonData['main'] ?? [];

if (!function_exists('lb_generic_pick_rank_label')) {
    function lb_generic_pick_rank_label($value, $fallback) {
        if (is_array($value)) {
            foreach (['name','label','title','rank','long_name'] as $k) {
                if (isset($value[$k]) && trim((string)$value[$k]) !== '') return trim((string)$value[$k]);
            }
        } elseif (trim((string)$value) !== '') {
            return trim((string)$value);
        }
        return $fallback;
    }
}

if (!function_exists('lb_generic_rank_icon_url')) {
    function lb_generic_rank_icon_url(array $jsonData, string $game, string $size, int $rank): string {
        $normalizedGame = strtolower(trim($game));
        if (in_array($normalizedGame, ['rocket-league', 'rocket_league', 'rl'], true) && $rank === 0) {
            return '/public/assets/website/images/boosting/ranks/rocket-league/unranked.webp';
        }
        if (in_array($normalizedGame, ['marvel-rivals', 'marvel_rivals', 'rivals'], true) && $rank === 6) {
            return '/public/assets/website/images/boosting/ranks/marvel-rivals/grandmaster.webp';
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
        return util_rank_img($game, $size, $rank);
    }
}

if (!function_exists('lb_generic_division_count_from_json')) {
    function lb_generic_division_count_from_json(array $jsonData, int $fallback = 4): int {
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

// Rank names can be stored by the editor as rank_names/ranks/tiers, or inferred from main keys.
$_rankSource = $_jsonData['rank_names'] ?? $_jsonData['rankNames'] ?? $_jsonData['ranks'] ?? $_jsonData['tiers'] ?? [];
$ranks = [];
if (is_array($_rankSource) && !empty($_rankSource)) {
    foreach ($_rankSource as $_key => $_value) {
        $_idx = is_numeric($_key) ? (int)$_key : (count($ranks) + 1);
        if ($_idx < 0) $_idx = count($ranks) + 1;
        $ranks[$_idx] = lb_generic_pick_rank_label($_value, 'Tier ' . $_idx);
    }
}
if (empty($ranks) && !empty($_mainPricing) && is_array($_mainPricing)) {
    $_mainKeys = array_keys($_mainPricing);
    $_singleDivisionWrapper = count($_mainKeys) === 1 && is_numeric($_mainKeys[0]) && (int)$_mainKeys[0] >= 0 && (int)$_mainKeys[0] <= 5;
    if (!$_singleDivisionWrapper) {
        foreach ($_mainKeys as $_tierKey) {
            if (!is_numeric($_tierKey)) continue;
            $_idx = (int)$_tierKey;
            $ranks[$_idx] = lb_generic_pick_rank_label($_mainPricing[$_tierKey], 'Tier ' . $_idx);
        }
    }
}
if (empty($ranks)) {
    $ranks = [1=>'Tier 1', 2=>'Tier 2', 3=>'Tier 3', 4=>'Tier 4', 5=>'Tier 5', 6=>'Tier 6', 7=>'Tier 7'];
}
ksort($ranks, SORT_NUMERIC);
$_rankKeys    = array_values(array_keys($ranks));
$_numRanks    = count($_rankKeys);
$_defaultFrom = $_rankKeys[max(0, min($_numRanks - 1, (int)floor($_numRanks * 0.25)))] ?? 1;
$_defaultTo   = $_rankKeys[max(0, min($_numRanks - 1, array_search($_defaultFrom, $_rankKeys, true) + 1))] ?? $_defaultFrom;
if ($_defaultTo <= $_defaultFrom && $_numRanks > 1) $_defaultTo = $_rankKeys[1];

$_navForms = util_load_game_boost_forms($_gameSlug);
if ($_gameSlug === 'marvel-rivals') {
    // Achievement Boost may not have a DB form yet. Keep it visible alongside the
    // other planned services, but render all three as disabled Coming Soon cards.
    $_existingNavSlugs = array_map(static function ($form) {
        return strtolower(trim((string)($form['slug'] ?? '')));
    }, $_navForms);
    if (!in_array('achievement-boost', $_existingNavSlugs, true)) {
        $_navForms[] = [
            'slug' => 'achievement-boost',
            'name' => 'Achievement Boost',
            'description' => 'Coming Soon',
            'icon' => 'champion-mastery.svg',
        ];
    }
}
if ($_gameSlug === 'apex-legends') {
    // Kills Boost stays visible in the service selector while its dedicated
    // pricing/form is unfinished, but it must not navigate to an active order page.
    $_existingNavSlugs = array_map(static function ($form) {
        return strtolower(trim((string)($form['slug'] ?? '')));
    }, $_navForms);
    if (!in_array('kills-boost', $_existingNavSlugs, true)) {
        $_navForms[] = [
            'slug' => 'kills-boost',
            'name' => 'Kills Boost',
            'description' => 'Coming Soon',
            'icon' => 'normal-matches.svg',
        ];
    }
}

// CS2 runs three separate boosting services under one game (Premier / Wingman / Faceit)
// that share the "counter-strike-2" game slug — group the nav pills into these
// sub-categories, each under its own label, and add non-clickable "coming soon"
// placeholders for pieces that don't have a real boost_forms row yet. Once the real
// form is created with a matching slug it's picked up automatically here.
$_cs2NavGroups = null;
if ($_gameSlug === 'counter-strike-2') {
    $_cs2Buckets = ['premier' => [], 'wingman' => [], 'faceit' => []];
    foreach ($_navForms as $_cs2Form) {
        $_cs2Slug = strtolower(trim((string)($_cs2Form['slug'] ?? '')));
        if (strpos($_cs2Slug, 'wingman') !== false) {
            $_cs2Buckets['wingman'][] = $_cs2Form;
        } elseif (strpos($_cs2Slug, 'faceit') !== false) {
            $_cs2Buckets['faceit'][] = $_cs2Form;
        } else {
            $_cs2Buckets['premier'][] = $_cs2Form;
        }
    }
    $_cs2HasSlugContaining = static function (array $forms, string $needle): bool {
        foreach ($forms as $f) {
            if (strpos(strtolower((string)($f['slug'] ?? '')), $needle) !== false) return true;
        }
        return false;
    };
    if (!$_cs2HasSlugContaining($_cs2Buckets['wingman'], 'placement')) {
        $_cs2Buckets['wingman'][] = ['slug' => '', 'name' => 'Wingman Placement', 'icon' => 'placement-boost.svg', '_soon' => true];
    }
    if (empty($_cs2Buckets['faceit'])) {
        $_cs2Buckets['faceit'][] = ['slug' => '', 'name' => 'Faceit Boosting', 'icon' => 'rank-boost.svg', '_soon' => true];
        $_cs2Buckets['faceit'][] = ['slug' => '', 'name' => 'Faceit Placements', 'icon' => 'placement-boost.svg', '_soon' => true];
    }
    $_cs2NavGroups = [];
    if (!empty($_cs2Buckets['premier'])) $_cs2NavGroups['premier'] = ['label' => 'Premier Boosting', 'forms' => $_cs2Buckets['premier']];
    $_cs2NavGroups['wingman'] = ['label' => 'Wingman Boosting', 'forms' => $_cs2Buckets['wingman']];
    $_cs2NavGroups['faceit']  = ['label' => 'Faceit Boosting', 'forms' => $_cs2Buckets['faceit']];
}

// Shared renderer for one nav pill — used both by the flat list (every other game)
// and the CS2 per-category groups above.
if (!function_exists('lb_generic_render_nav_item')) {
    function lb_generic_render_nav_item(array $_nf, string $_gameSlug, string $_activeSlug): void {
        $_nfIcon = !empty($_nf['icon']) ? ASSET_URL . '/website/images/boost-forms/boost-type-icons/' . $_nf['icon'] : ASSET_URL . '/website/images/boost-forms/boost-type-icons/rank-boost.svg';
        $_nfActive = ($_activeSlug === ($_nf['slug'] ?? ''));
        $_nfSlug = strtolower(trim((string)($_nf['slug'] ?? '')));
        $_nfComingSoon = !empty($_nf['_soon'])
            || ($_gameSlug === 'marvel-rivals' && in_array($_nfSlug, ['achievement-boost', 'unlock-competitive', 'proficiency-boost'], true))
            || ($_gameSlug === 'apex-legends' && $_nfSlug === 'kills-boost');
        if ($_nfComingSoon) {
            echo '<div class="nav-item coming-soon" aria-disabled="true" data-tooltip="Coming Soon">';
        } else {
            echo '<a href="/' . htmlspecialchars($_gameSlug) . '/' . htmlspecialchars($_nf['slug'] ?? '') . '" class="nav-item ' . ($_nfActive ? 'active' : '') . '" data-tooltip="' . htmlspecialchars($_nf['description'] ?? '') . '">';
        }
        echo '<img src="' . htmlspecialchars($_nfIcon) . '" alt="' . htmlspecialchars($_nf['name'] ?? '') . '-icon">';
        echo '<span>' . htmlspecialchars($_nf['name'] ?? '') . '</span>';
        echo $_nfComingSoon ? '</div>' : '</a>';
    }
}

// FAQs are unique per game + boost form, following the existing forms/{game}/{form}.php
// layout (see forms/val/win.php, forms/tft/rank.php, forms/lol-classic/rank.php).
// Look for a game-specific file first, then the shared per-form file, then fall
// back to the generic rank FAQ so every combination always renders something.
$_faqFormMap = [
    'rank-boost' => 'rank',
    'win-boost' => 'win',
    'placement' => 'placement',
    'placement-boost' => 'placement',
    'placements-boost' => 'placement',
    'arena-boost' => 'arena',
    'clash-boost' => 'clash',
    'level-up-boost' => 'level',
    'champion-mastery' => 'mastery',
    'normal-matches' => 'normal',
    'expert-coaching' => 'coaching',
    'pay-per-games' => 'pro-games',
    'wingman-boosting' => 'wingman',
];
$_faqFormSlug = $_faqFormMap[$_genericFormSlug] ?? 'rank';
$_faqCandidates = [
    $_gameShort . '/' . $_faqFormSlug,
    $_faqFormSlug,
    'rank',
];
$faqFile = 'website/components/faqs/forms/rank';
foreach ($_faqCandidates as $_faqCand) {
    $_faqCandFile = 'website/components/faqs/forms/' . $_faqCand;
    if (file_exists(SYS_PATH . '/public/views/' . $_faqCandFile . '.php')) {
        $faqFile = $_faqCandFile;
        break;
    }
}
?>

<div id="genericPageTop" style="height:1px;position:relative;"></div>

<?php if (count($_navForms) > 1 || $_cs2NavGroups !== null): ?>
<div class="rank-types-nav<?= $_cs2NavGroups !== null ? ' rank-types-nav--grouped' : '' ?>">
    <?php if ($_cs2NavGroups !== null): ?>
        <?php foreach ($_cs2NavGroups as $_cs2GroupKey => $_cs2Group): ?>
        <div class="nav-group" data-cat="<?= htmlspecialchars($_cs2GroupKey) ?>">
            <div class="nav-group__label"><?= htmlspecialchars($_cs2Group['label']) ?></div>
            <div class="nav-group__row">
                <?php foreach ($_cs2Group['forms'] as $_nf): lb_generic_render_nav_item($_nf, $_gameSlug, (string)($data['slug'] ?? '')); endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php foreach ($_navForms as $_nf): lb_generic_render_nav_item($_nf, $_gameSlug, (string)($data['slug'] ?? '')); endforeach; ?>
    <?php endif; ?>
</div>
<?php endif ?>

<form class="boost-form" id="generic_boost_form" action="<?= AJAX_URL ?>" autocomplete="off">
    <input type="hidden" name="action" value="get_boost_price">
    <input type="hidden" name="form_id" value="<?= (int)$data['id'] ?>">
    <input type="hidden" name="uuid" value="<?= htmlspecialchars($data['uuid'] ?? '') ?>">

    <div class="form-content">
        <div class="left">
            <div class="boost-form">
                <?php $this->insert('website/components/forms/rank-dynamic', [
                    'data'  => $data,
                    'ranks' => $ranks,
                    'is_generic_boost' => true,
                ]) ?>
            </div>
            <div class="boost-faqs">
                <h4><?= t('Frequently Asked Questions 🤔') ?></h4>
                <?php $this->insert($faqFile, ['data' => $data, 'ranks' => $ranks]) ?>
            </div>
        </div>
        <div class="right">
            <?php $this->insert('website/components/forms/order-summary-multigame', [
                'data'           => $data,
                'summary_game'   => $_gameShort,
                'summary_points' => $_jsonData['points_label'] ?? 'LP',
                'ranks'          => $ranks,
                'is_generic_boost' => true,
            ]) ?>
            <div class="boost-faqs-mobile">
                <h4><?= t('Frequently Asked Questions 🤔') ?></h4>
                <?php $this->insert($faqFile, ['data' => $data, 'ranks' => $ranks]) ?>
            </div>
        </div>
    </div>

    <div class="sticky-overview">
        <div class="rank-box <?= ($_genericIsWin || $_genericIsPlacement) ? 'generic-games-summary' : '' ?>">
            <div class="from">
                <img src="<?= lb_generic_rank_icon_url($_jsonData, $_gameShort, 'mini', (int)$_defaultFrom) ?>"
                     alt="current_rank" class="current-summary-rank-img"
                     onerror="this.style.display='none'">
                <span class="title current-summary-rank-name"><?= htmlspecialchars($ranks[$_defaultFrom] ?? 'Tier ' . $_defaultFrom) ?></span>
            </div>
            <?php if (!$_genericIsWin && !$_genericIsPlacement): ?>
            <img src="<?= ASSET_URL ?>/website/images/arrow-summary.svg" alt="arrow_icon">
            <div class="to">
                <img src="<?= lb_generic_rank_icon_url($_jsonData, $_gameShort, 'mini', (int)$_defaultTo) ?>"
                     alt="desired_rank" class="desired-summary-rank-img"
                     onerror="this.style.display='none'">
                <span class="title desired-summary-rank-name"><?= htmlspecialchars($ranks[$_defaultTo] ?? 'Tier ' . $_defaultTo) ?></span>
            </div>
            <?php else: ?>
            <div class="to generic-games-count-summary">
                <span class="title"><span id="sticky-games-count"><?= $_genericIsWin ? 2 : 5 ?></span> <?= $_genericIsWin ? t('Wins') : t('Games') ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="totals">
            <p>
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/total.svg" alt="total_icon">
                <?= t('Total Price') ?>
            </p>
            <div>
                <span class="price old-price" id="sticky-old-price"><?= t('€0.00') ?></span>
                <span class="price total-price" id="sticky-total-price"><?= t('€0.00') ?></span>
            </div>
        </div>

        <button type="submit" class="btn primary buy-now" id="sticky_start_boost"><?= t('Buy Now') ?></button>
    </div>
</form>

<div class="bottom-sec">
    <?= $this->insert('website/components/testimonials') ?>

    <div class="choose-us">
        <h4><?= t('Why Choose Us?') ?></h4>
        <div class="tiles">
            <div class="tile"><img src="<?= ASSET_URL ?>/website/images/boost-forms/empowerment.svg" alt="results-that-last"><h5><?= t('Results That Last') ?></h5><p><?= t('Our pros push your rank and share tips so you keep climbing after the boost. Improve today and keep the gains tomorrow.') ?></p></div>
            <div class="tile"><img src="<?= ASSET_URL ?>/website/images/boost-forms/climb.svg" alt="start-your-climb"><h5><?= t('Start Your Climb Today') ?></h5><p><?= t('Choose a boost or coaching session. Reach your desired rank fast and safely, with clear progress tracking and support.') ?></p></div>
            <div class="tile"><img src="<?= ASSET_URL ?>/website/images/boost-forms/victory.svg" alt="win-more"><h5><?= t('Win More, Stress Less') ?></h5><p><?= t('We boost securely with VPN and manual play, keeping you updated until your goal is reached.') ?></p></div>
        </div>
    </div>
    <div class="about-us"><div class="content"><h4><?= t('About Us') ?></h4><p><?= t('LoLBoost.gg offers professional boosting services across multiple games. All boosts are manual, handled by verified top-ranked players, with region-matched VPN for safety.') ?><br><br><?= t('Stuck at the same rank? We help you climb faster. Track your progress in your dashboard, chat with support 24/7, and enjoy a smooth experience from start to finish.') ?></p></div></div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/main/vendor/nouislider/dist/nouislider.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wnumb/1.2.0/wNumb.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script>
(function () {
    var rankMap = <?= json_encode($ranks, JSON_UNESCAPED_UNICODE) ?>;
    var rankIconMap = <?= json_encode(array_map(function($_tier) use ($_jsonData, $_gameShort) { return lb_generic_rank_icon_url($_jsonData, $_gameShort, 'mini', (int)$_tier); }, array_combine($_rankKeys, $_rankKeys)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    function getRankName(tierValue) { return rankMap[String(tierValue)] || rankMap[parseInt(tierValue, 10)] || ('Tier ' + tierValue); }
    function expectedRankIcon(tierValue) {
        return rankIconMap[String(tierValue)] || rankIconMap[parseInt(tierValue, 10)] || '';
    }
    function swapRankImage(img, tierValue) {
        if (!img) return;
        var icon = expectedRankIcon(tierValue);
        if (!icon) return;
        img.style.display = '';
        if (img.getAttribute('src') !== icon) img.setAttribute('src', icon);
    }
    function currentStartTier() { var i = document.querySelector('input[name="start_tier"]:checked'); return i ? i.value : <?= json_encode((string)$_defaultFrom) ?>; }
    function currentEndTier() { var i = document.querySelector('input[name="end_tier"]:checked'); return i ? i.value : <?= json_encode((string)$_defaultTo) ?>; }
    function syncGenericRankUi() {
        var st = currentStartTier(), et = currentEndTier();
        // Rank names (including the selected division) are owned by rank-dynamic.php.
        // Do not replace values such as "Platinum IV" with the tier-only "Platinum"
        // during this delayed image repair pass.
        document.querySelectorAll('.current-summary-rank-img, .current-rank-img').forEach(function (img) { swapRankImage(img, st); });
        document.querySelectorAll('.desired-summary-rank-img, .desired-rank-img').forEach(function (img) { swapRankImage(img, et); });
    }
    function syncGenericGamesCount() {
        var input = document.getElementById('dynamic_matches_input');
        var output = document.getElementById('sticky-games-count');
        if (input && output) output.textContent = input.value || '1';
    }
    function scheduleRankUiSync() { [0, 50, 150, 400, 900].forEach(function(ms){ setTimeout(syncGenericRankUi, ms); }); }
    document.addEventListener('change', function (e) {
        if (e.target && (e.target.name === 'start_tier' || e.target.name === 'end_tier')) scheduleRankUiSync();
        if (e.target && (e.target.name === 'matches0' || e.target.name === 'matches')) syncGenericGamesCount();
    });
    document.addEventListener('lb:generic-form-changed', syncGenericGamesCount);
    document.addEventListener('click', function (e) {
        if (e.target && e.target.closest && e.target.closest('.rank-btn')) scheduleRankUiSync();
    });
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function (mutations) {
            var needsSync = mutations.some(function (m) {
                return m.type === 'attributes' && m.attributeName === 'src' && m.target && m.target.matches && m.target.matches('.current-summary-rank-img, .desired-summary-rank-img, .current-rank-img, .desired-rank-img');
            });
            if (needsSync) setTimeout(syncGenericRankUi, 0);
        });
        observer.observe(document.documentElement, {subtree:true, attributes:true, attributeFilter:['src']});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scheduleRankUiSync); else scheduleRankUiSync();

    function updateOldPrice() {
        var totalPriceEl   = document.getElementById('total-price');
        var oldPriceEl     = document.getElementById('old-price');
        var discountBox    = document.getElementById('discount-box');
        var savedPriceEl   = document.getElementById('saved-price');
        var stickyOldPrice = document.getElementById('sticky-old-price');
        var stickyTotal    = document.getElementById('sticky-total-price');
        if (!totalPriceEl || !discountBox) return;
        if (discountBox.style.display === 'flex') {
            var discountMsg = document.getElementById('discount-message');
            var discount    = parseInt(discountMsg.getAttribute('data-discount'));
            var totalPrice  = parseFloat(totalPriceEl.textContent.replace(/[^0-9.-]+/g, ''));
            var oldPrice    = (totalPrice / (1 - discount / 100)).toFixed(2);
            var sym         = totalPriceEl.textContent[0];
            if (oldPriceEl) oldPriceEl.innerHTML = sym + oldPrice;
            if (savedPriceEl) savedPriceEl.textContent = sym + (oldPrice - totalPrice).toFixed(2);
            if (stickyOldPrice) stickyOldPrice.innerHTML = sym + oldPrice;
            if (stickyTotal) stickyTotal.textContent = sym + totalPrice;
        }
    }
    function applyDiscount(discount) {
        var totalPriceEl = document.getElementById('total-price');
        var newPriceEl   = document.getElementById('new-price');
        var savedPriceEl = document.getElementById('saved-price');
        var discountText = document.getElementById('discount-input');
        var discountBox  = document.getElementById('discount-box');
        var discountMsg  = document.getElementById('discount-message');
        var oldPriceEl   = document.getElementById('old-price');
        var stickyTotal  = document.getElementById('sticky-total-price');
        if (!totalPriceEl) return;
        var totalPrice  = parseFloat(totalPriceEl.textContent.replace(/[^0-9.-]+/g, ''));
        totalPrice = (totalPrice * (1 - discount / 100)).toFixed(2);
        var sym = totalPriceEl.textContent[0];
        totalPriceEl.textContent = sym + totalPrice;
        if (newPriceEl) newPriceEl.textContent = sym + totalPrice;
        if (savedPriceEl && oldPriceEl) savedPriceEl.textContent = sym + (parseFloat(oldPriceEl.textContent.replace(/[^0-9.-]+/g,'')) - totalPrice).toFixed(2);
        if (stickyTotal) stickyTotal.textContent = sym + totalPrice;
        if (discountText) discountText.style.display = 'none';
        var appliedText = "<?= t('Discount Applied') ?>";
        if (discountMsg) { discountMsg.textContent = discount + '% ' + appliedText; discountMsg.setAttribute('data-discount', discount); }
        if (discountBox) discountBox.style.display = 'flex';
        updateOldPrice();
        var removeBtn = document.getElementById('remove-discount');
        if (removeBtn) removeBtn.addEventListener('click', function () { removeDiscount(discount); });
    }
    function removeDiscount(discount) {
        var totalPriceEl   = document.getElementById('total-price');
        var newPriceEl     = document.getElementById('new-price');
        var savedPriceEl   = document.getElementById('saved-price');
        var discountText   = document.getElementById('discount-input');
        var discountBox    = document.getElementById('discount-box');
        var discountCodeEl = document.getElementById('discount_code');
        var stickyOldPrice = document.getElementById('sticky-old-price');
        var stickyTotal    = document.getElementById('sticky-total-price');
        if (!totalPriceEl) return;
        var discountedPrice = parseFloat(totalPriceEl.textContent.replace(/[^0-9.-]+/g,''));
        var originalPrice   = (discountedPrice / (1 - discount / 100)).toFixed(2);
        var sym = totalPriceEl.textContent[0];
        totalPriceEl.textContent = sym + originalPrice;
        if (newPriceEl) newPriceEl.textContent = sym + originalPrice;
        if (savedPriceEl) savedPriceEl.textContent = sym + '0.00';
        if (stickyTotal) stickyTotal.textContent = sym + originalPrice;
        if (discountText) discountText.style.display = 'flex';
        if (discountBox) discountBox.style.display = 'none';
        if (stickyOldPrice) stickyOldPrice.style.display = 'none';
        if (discountCodeEl) discountCodeEl.value = '';
    }
    var totalPriceEl = document.getElementById('total-price');
    if (totalPriceEl) new MutationObserver(updateOldPrice).observe(totalPriceEl, { childList: true, characterData: true, subtree: true });
    var discountCodeEl = document.getElementById('discount_code');
    if (discountCodeEl) discountCodeEl.addEventListener('input', function () { switch (this.value.toLowerCase()) { case 'sale50': applyDiscount(50); break; case 'new40': applyDiscount(40); break; } });

    var $stickySection = jQuery('.sticky-overview'), $hideSticky = jQuery('#hide-sticky'), $paymentGateways = jQuery('.payment-gateways').first();
    var tawkHiddenBySticky = false;
    function isMobile() { return window.matchMedia('(max-width:1024px)').matches; }
    function setTawkVisibility(hide) { if (tawkHiddenBySticky === hide) return; tawkHiddenBySticky = hide; if (window.Tawk_API) { if (hide && typeof window.Tawk_API.hideWidget === 'function') window.Tawk_API.hideWidget(); else if (!hide && typeof window.Tawk_API.showWidget === 'function') window.Tawk_API.showWidget(); } }
    function setStickyVisible(visible) { $stickySection.css({ transform: visible ? 'translateY(0)' : 'translateY(100%)', transition: 'transform 0.3s ease-in-out' }); var hideTawk = isMobile() && visible; document.body.classList.toggle('lb-sticky-overview-active', hideTawk); setTawkVisibility(hideTawk); }
    function checkVisibility() { if (!$stickySection.length) return; var wh = jQuery(window).height(), st = jQuery(window).scrollTop(); var reachedGateways = $paymentGateways.length ? (st + wh >= $paymentGateways.offset().top) : true; var inHideZone = $hideSticky.length && (st + wh > $hideSticky.offset().top) && (st < $hideSticky.offset().top + $hideSticky.outerHeight()); setStickyVisible(reachedGateways && !inHideZone); }
    jQuery(window).on('scroll resize', checkVisibility); checkVisibility();

    // ── Generic Selection Modal JS ───────────────────────────────────────────
    window.closeLbSelModal = function(key) {
        var modal = document.getElementById('lb_sel_modal_' + key);
        if (!modal) return;
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden','true');
        // Uncheck the toggle
        var toggle = document.getElementById(key);
        if (toggle && toggle.checked) {
            toggle.checked = false;
            toggle.dispatchEvent(new Event('change', {bubbles:true}));
        }
    };
    window.resetSelModal = function(key) {
        var modal = document.getElementById('lb_sel_modal_' + key);
        if (!modal) return;
        modal.querySelectorAll('input[type=checkbox]').forEach(function(cb){ cb.checked = false; });
        modal.querySelectorAll('.lb-cr-champ-item.selected').forEach(function(el){ el.classList.remove('selected'); });
        modal.querySelectorAll('.lb-cr-role-card').forEach(function(l){ l.classList.remove('selected'); });
        // Also uncheck the hidden form inputs
        document.querySelectorAll('input[name^="selection_'+key+'"]').forEach(function(i){ i.checked=false; });
    };
    window.toggleSelItem = function(itemEl, key, name) {
        var cb = itemEl.querySelector('.lb-cr-champ-cb');
        if (!cb) return;
        cb.checked = !cb.checked;
        itemEl.classList.toggle('selected', cb.checked);
        syncSelInput(key, name, cb.checked);
    };
    window.syncSelInput = function(key, name, checked) {
        // Sync back to the hidden input outside the modal
        var hiddenId = 'sel_' + key + '_' + name.replace(/[^a-z0-9]/gi,'_');
        var hidden = document.getElementById(hiddenId);
        if (hidden) hidden.checked = checked;
        // Dispatch change event for price calculation
        document.dispatchEvent(new CustomEvent('lb:addons-updated'));
    };
    window.filterSelGrid = function(key, query) {
        var grid = document.getElementById('lb_sel_grid_' + key);
        if (!grid) return;
        var q = query.toLowerCase();
        grid.querySelectorAll('.lb-cr-champ-item').forEach(function(el) {
            el.style.display = (!q || el.dataset.name.indexOf(q) >= 0) ? '' : 'none';
        });
    };
    // Close modal on backdrop click
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('lb-sel-modal')) {
            var key = e.target.id.replace('lb_sel_modal_','');
            closeLbSelModal(key);
            // Uncheck the toggle if nothing selected
            var anyChecked = e.target.querySelectorAll('input[type=checkbox]:checked').length > 0;
            if (!anyChecked) {
                var toggle = document.getElementById(key);
                if (toggle) { toggle.checked = false; toggle.dispatchEvent(new Event('change', {bubbles:true})); }
            }
        }
    });
})();
</script>

<script>
(function () {
    var form = document.getElementById('generic_boost_form');
    if (!form || typeof jQuery === 'undefined') return;

    var timer = null;
    var isSending = false;

    function priceText(currency, price) {
        var amount = parseFloat(price || 0);
        if (typeof window.util_price === 'function') return (currency || '€') + window.util_price(amount);
        return (currency || '€') + amount.toFixed(2);
    }

    function setPrice(currency, price) {
        var text = priceText(currency, price);
        document.querySelectorAll('.total-price').forEach(function (el) { el.textContent = text; });
        var sticky = document.getElementById('sticky-total-price');
        if (sticky) sticky.textContent = text;
    }

    function setBuyDisabled(disabled, label) {
        form.querySelectorAll('button[type="submit"], .buy-now').forEach(function (btn) {
            btn.disabled = !!disabled;
            var labelEl = btn.querySelector('.indicator-label');
            if (labelEl && label) labelEl.textContent = label;
        });
    }

    function ensureBuyNowField(enabled) {
        var old = form.querySelector('input[name="buy_now"]');
        if (old) old.remove();
        if (enabled) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'buy_now';
            input.value = '1';
            form.appendChild(input);
        }
    }

    function updatePricing(buyNow) {
        if (isSending) return;
        isSending = true;
        ensureBuyNowField(!!buyNow);
        var formData = new FormData(form);

        jQuery.ajax({
            type: 'post',
            url: form.getAttribute('action'),
            data: formData,
            dataType: 'text',
            cache: false,
            processData: false,
            contentType: false,
            beforeSend: function () {
                if (buyNow) {
                    jQuery(form).find('button[type="submit"]').attr('data-kt-indicator', 'on').prop('disabled', true);
                }
            },
            complete: function () {
                isSending = false;
                if (!buyNow) ensureBuyNowField(false);
                jQuery(form).find('button[type="submit"]').removeAttr('data-kt-indicator').prop('disabled', false);
            },
            success: function (response) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    var match = String(response).match(/(\{[\s\S]*\})/);
                    if (!match) return;
                    try { response = JSON.parse(match[1]); } catch (e2) { return; }
                }

                if (response.redirectUrl) {
                    window.location.href = response.redirectUrl;
                    return;
                }
                if (response.refreshPage) {
                    window.location.reload();
                    return;
                }
                if (response.updatePricing || response.price !== undefined) {
                    setPrice(response.currency || '€', response.price || 0);
                    setBuyDisabled(parseFloat(response.price || 0) <= 0, parseFloat(response.price || 0) <= 0 ? 'Invalid Selection' : 'Buy Now');
                }
                if (response.completion_time != null) {
                    var ct = document.getElementById('completion-time');
                    if (ct) ct.textContent = response.completion_time;
                }
                if (response.discount_msg != null) {
                    var alert = document.getElementById('discount_alert');
                    if (alert) {
                        alert.textContent = response.discount_msg || '';
                        alert.classList.toggle('text-success', !!response.discount_status);
                        alert.classList.toggle('text-danger', !response.discount_status);
                    }
                }
            }
        });
    }

    function scheduleUpdate() {
        clearTimeout(timer);
        timer = setTimeout(function () { updatePricing(false); }, 250);
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        updatePricing(!!form.querySelector('input[name="buy_now"]'));
        return false;
    });

    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('#start_boost, #sticky_start_boost, .buy-now') : null;
        if (!btn || !form.contains(btn)) return;
        e.preventDefault();
        ensureBuyNowField(true);
        updatePricing(true);
    });

    form.addEventListener('change', scheduleUpdate);
    form.addEventListener('input', function (e) {
        if (e.target && (e.target.matches('input[type="text"], input[type="number"], input[name="discount_code"]'))) scheduleUpdate();
    });
    document.addEventListener('lb:generic-form-changed', scheduleUpdate);
    document.addEventListener('lb:addons-updated', scheduleUpdate);

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scheduleUpdate);
    else scheduleUpdate();
})();
</script>
<?= $this->end('scripts') ?>
