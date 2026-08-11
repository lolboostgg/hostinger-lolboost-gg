<div class="rank-box" <?php if ($data['type'] == 'coaching') {
    echo 'style="justify-content: center;"';
} ?>>
<?php
$__isValForm = in_array((int)($data['form_id'] ?? 0), [5,6,7,8]);
$__isClassicOrder = function_exists('util_is_lol_classic') && util_is_lol_classic($data['game'] ?? '');
$__game = $__isValForm ? 'val' : ($__isClassicOrder ? 'lol_classic' : 'lol');
$__apexFrom = $__isClassicOrder ? 7 : 8;

// Games added dynamically via the admin "Games" area (anything besides lol/val/tft) don't share
// LoL's rank tiers/icons. Those forms store their own rank names/icons/divisions in the boost
// form's pricing JSON (see admin Boost Form Editor) — use that instead of the hardcoded lol fallback.
$__gameSlugRaw = strtolower(trim((string)($data['game'] ?? '')));
$__formSlugRaw = strtolower(trim((string)($data['slug'] ?? '')));
// Wingman Boost shares the "counter-strike-2" game slug with Premier Rank Boost but uses
// the classic Silver-to-SMFC ladder (no RP/points) — see lb_generic_game_rank_config().
$__isWingman = $__gameSlugRaw === 'counter-strike-2' && strpos($__formSlugRaw, 'wingman') !== false;
$__knownGameSlugs = ['league-of-legends', 'lol', 'leagu', 'leag', 'valorant', 'val', 'valor', 'teamfight-tactics', 'tft', 'teamf', 'lol-classic', 'lol_classic'];
$__isDynamicGame = !$__isValForm && !$__isClassicOrder && !in_array($__gameSlugRaw, $__knownGameSlugs, true);
$__dynJson = [];
if ($__isDynamicGame) {
    if (!empty($data['json']) && is_array($data['json'])) {
        $__dynJson = $data['json'];
    } elseif (!empty($data['form_id']) && function_exists('lb_load_boost_form_json_by_id')) {
        $__dynJson = lb_load_boost_form_json_by_id($data['form_id']);
    }
    if (empty($__dynJson)) {
        $__isDynamicGame = false; // no dynamic config found, fall back to the legacy lol-style rendering
    } elseif (function_exists('lb_generic_game_rank_config')) {
        // Keep checkout rank rendering in sync with the live generic boost form. The DB JSON may
        // still contain an older division setup (notably Apex Master), while the form uses the
        // authoritative per-game configuration.
        $__authoritativeRankConfig = lb_generic_game_rank_config($__gameSlugRaw, $__formSlugRaw);
        if (is_array($__authoritativeRankConfig)) {
            $__dynJson['form_config'] = array_replace(
                is_array($__dynJson['form_config'] ?? null) ? $__dynJson['form_config'] : [],
                $__authoritativeRankConfig
            );
            if ($__gameSlugRaw === 'counter-strike-2'
                && (strtolower((string)($data['type'] ?? '')) === 'placement'
                    || in_array(strtolower((string)($data['slug'] ?? '')), ['placement', 'placement-boost', 'placements-boost'], true))) {
                $__dynJson['form_config']['ranks'] = [0 => 'New Account'] + (array)($__dynJson['form_config']['ranks'] ?? []);
                $__dynJson['form_config']['rank_divs'] = [0 => 0] + (array)($__dynJson['form_config']['rank_divs'] ?? []);
            }
            // Auto-resolve rank icons from the known local asset filenames, same as the live
            // order form (see generic.php) — only fills tiers not already set by the admin.
            if (!empty($__authoritativeRankConfig['rank_files']) && is_array($__authoritativeRankConfig['rank_files'])) {
                $__dynJson['rank_icons'] = is_array($__dynJson['rank_icons'] ?? null) ? $__dynJson['rank_icons'] : [];
                foreach ($__authoritativeRankConfig['rank_files'] as $__rfTier => $__rfFile) {
                    if (isset($__dynJson['rank_icons'][$__rfTier]) || isset($__dynJson['rank_icons'][(string)$__rfTier])) continue;
                    $__dynJson['rank_icons'][$__rfTier] = 'website/images/boosting/ranks/' . $__gameSlugRaw . '/' . $__rfFile . '.webp';
                }
            }
        }
    }
}
$__dynRank = function (int $tier, $division = null, $lp = null) use ($__dynJson, $__gameSlugRaw) {
    $ratingOnly = !empty($__dynJson['rating_only']) || !empty($__dynJson['form_config']['rating_only']);
    return [
        'icon' => lb_summary_rank_icon_url($__dynJson, $__gameSlugRaw, 'mini', $tier),
        // Points are rendered on their own line below the rank in checkout.
        'name' => lb_summary_rank_display($__dynJson, $tier, $division, $ratingOnly ? $lp : null),
        'no_divs' => !$ratingOnly && lb_summary_rank_divs_for_tier($__dynJson, $tier) <= 0,
        'points_label' => $__dynJson['points_label'] ?? ($__dynJson['form_config']['points_label'] ?? 'LP'),
    ];
};
$__ranked5sBoostersCount = 0;

// Ranked 5s booster count can arrive directly from the checkout route or be stored
// inside the order options JSON. Do not use $data['boosters'] here, because that
// key is also used for the list of available booster accounts in the checkout.
$__ranked5sCountKeys = [
    'ranked_5s_boosters_count',
    'ranked5s_boosters_count',
    'boosters_count',
    'booster_count',
    'number_of_boosters',
    'selected_boosters',
    'boosters',
];

$__readRanked5sCount = static function ($value) use (&$__readRanked5sCount, $__ranked5sCountKeys) {
    if (is_object($value)) {
        $value = (array)$value;
    }

    if (!is_array($value)) {
        return 0;
    }

    foreach ($__ranked5sCountKeys as $key) {
        if (!array_key_exists($key, $value)) {
            continue;
        }

        $candidate = $value[$key];
        if (is_numeric($candidate)) {
            $candidate = (int)$candidate;
            if ($candidate >= 1 && $candidate <= 4) {
                return $candidate;
            }
        }
    }

    foreach ($value as $nested) {
        if (is_array($nested) || is_object($nested)) {
            $candidate = $__readRanked5sCount($nested);
            if ($candidate >= 1 && $candidate <= 4) {
                return $candidate;
            }
        }
    }

    return 0;
};

$__ranked5sBoostersCount = $__readRanked5sCount($data);

if ($__ranked5sBoostersCount === 0 && !empty($data['order_id'])) {
    try {
        $__ranked5sOrder = db_get_row('orders', ['id' => (int)$data['order_id']]);
        if (!empty($__ranked5sOrder)) {
            $__ranked5sOrder = (array)$__ranked5sOrder;

            // First check normal scalar columns.
            $__ranked5sBoostersCount = $__readRanked5sCount($__ranked5sOrder);

            // Then inspect all JSON columns, regardless of their exact column name.
            if ($__ranked5sBoostersCount === 0) {
                foreach ($__ranked5sOrder as $__rawOrderValue) {
                    if (!is_string($__rawOrderValue) || $__rawOrderValue === '') {
                        continue;
                    }

                    $__decodedOrderValue = json_decode($__rawOrderValue, true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($__decodedOrderValue)) {
                        continue;
                    }

                    $__ranked5sBoostersCount = $__readRanked5sCount($__decodedOrderValue);
                    if ($__ranked5sBoostersCount > 0) {
                        break;
                    }
                }
            }
        }
    } catch (Throwable $e) {
        $__ranked5sBoostersCount = 0;
    }
}

$__ranked5sBoostersCount = max(1, min(4, (int)$__ranked5sBoostersCount));
$__isMultiRequestSummary = in_array((int)($data['form_id'] ?? 0), [4, 19, 29], true);
$__summaryRequestedRaw = $data['selected_boosters'] ?? '';
if (is_array($__summaryRequestedRaw)) {
    $__summaryRequestedRaw = implode(',', $__summaryRequestedRaw);
}
$__summaryRequestedIds = $__isMultiRequestSummary
    ? array_values(array_unique(array_filter(array_map(
        'intval',
        preg_split('/[\s,|]+/', (string)$__summaryRequestedRaw, -1, PREG_SPLIT_NO_EMPTY)
    ))))
    : [];
if ($__isMultiRequestSummary && empty($__summaryRequestedIds) && !empty($data['booster_id'])) {
    $__summaryRequestedIds[] = (int)$data['booster_id'];
}
$__summaryRequestedIds = array_slice($__summaryRequestedIds, 0, $__ranked5sBoostersCount);
$__summaryRequestedBoosters = [];
foreach ((array)($data['boosters'] ?? []) as $__summaryBooster) {
    $__summaryBoosterId = (int)($__summaryBooster['id'] ?? 0);
    if (!in_array($__summaryBoosterId, $__summaryRequestedIds, true)) {
        continue;
    }
    $__summaryRequestedBoosters[$__summaryBoosterId] = [
        'id' => $__summaryBoosterId,
        'name' => (string)($__summaryBooster['username'] ?? 'Booster'),
        'icon' => (string)($__summaryBooster['icon'] ?? 'https://lolboost.gg/public/uploads/icons/default.png'),
    ];
}
?>
    <div class="from">
        <?php switch ($data['type']) {
            case 'rank': ?>
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)$data['start_tier'], $data['start_division'] ?? null, $data['start_lp'] ?? null); ?>
                    <?php if ($__gameSlugRaw === 'counter-strike-2' && (int)$data['start_tier'] === 0): ?>
                    <i class="fa-solid fa-circle-question current-new-account-icon" aria-label="New Account" style="color:#72c8ff;font-size:32px"></i>
                    <?php else: ?>
                    <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon"
                        class="current-summary-rank-img" onerror="this.style.display='none'">
                    <?php endif; ?>
                    <?php if ((int)$data['start_tier'] !== 0): ?>
                        <span class="title current-summary-rank-name"><?= htmlspecialchars($__r['name'], ENT_QUOTES) ?></span>
                        <?php if ($__r['no_divs'] && !$__isWingman): ?>
                            <br><small class="current-summary-lp">[ <?= htmlspecialchars((string)($data['start_lp'] ?? 0), ENT_QUOTES) ?> <?= htmlspecialchars($__r['points_label'], ENT_QUOTES) ?> ]</small>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                <img src="<?= util_rank_img($__game, 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <?php if ($data['start_tier'] != 0): ?>
                    <span
                        class="title current-summary-rank-name"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], $__game) ?></span>
                    <br>
                    <small class="current-summary-lp">[ <?= $data['start_lp'] ?> LP ]</small>
                <?php endif; endif;
                break;
            case 'win': ?>
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)$data['start_tier'], $data['start_division'] ?? null, $data['start_lp'] ?? null); ?>
                    <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon" class="current-summary-rank-img" onerror="this.style.display='none'">
                    <span class="title current-summary-rank-name"><?= htmlspecialchars($__r['name'], ENT_QUOTES) ?></span>
                    <?php if ($__r['no_divs'] && isset($data['start_lp']) && $data['start_lp'] !== ''): ?>
                        <br><small class="current-summary-lp">[ <?= htmlspecialchars((string)$data['start_lp'], ENT_QUOTES) ?> <?= htmlspecialchars($__r['points_label'], ENT_QUOTES) ?> ]</small>
                    <?php endif; ?>
                <?php else: ?>
                <img src="<?= util_rank_img($__game, 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= util_format_rank_advanced($data['start_tier'], $data['start_division'], $__game) ?>
                </span>
                <?php endif; break;
            case 'placement': ?>
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)$data['start_tier'], $data['start_division'] ?? null); ?>
                    <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon" class="current-summary-rank-img" onerror="this.style.display='none'">
                    <span class="title current-summary-rank-name"><?= htmlspecialchars($__r['name'], ENT_QUOTES) ?></span>
                <?php else: ?>
                <img src="<?= util_rank_img($__game, 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= util_format_rank_advanced($data['start_tier'], $data['start_division'], $__game) ?>
                </span>
                <?php endif; break;
            case 'match': ?>
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)$data['start_tier'], $data['start_division'] ?? null, $data['start_lp'] ?? null); ?>
                    <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon" class="current-summary-rank-img" onerror="this.style.display='none'">
                    <span class="title current-summary-rank-name"><?= htmlspecialchars($__r['name'], ENT_QUOTES) ?></span>
                <?php else: ?>
                <img src="<?= util_rank_img($__game, 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= util_format_rank_advanced($data['start_tier'], $data['start_division'], $__game) ?>
                </span>
                <?php endif; break;
            case 'ranked-5s': ?>
                <img src="<?= util_rank_img('lol', 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'lol') ?>
                </span>
                <?php if ((int)($data['start_tier'] ?? 0) >= 8): ?>
                    <br><small class="current-summary-lp">[ <?= (int)($data['start_lp'] ?? 0) ?> LP ]</small>
                <?php endif; ?>
                <?php break;
            case 'normal': ?>
                <div class="game game-mode"><?= ucwords(str_replace('_', ' ', $data['queue_type'])); ?></div>
                <?php break;
            case 'coaching': ?>
                <div class="game" style="text-align: center; width: 100%;">
                    <div style="font-size:16px;font-weight:800;color:#fff;line-height:1.2;">
                        <span class="hour-count"><?= $data['hours'] ?></span>
                        <?= t(' Coaching Hours') ?>
                    </div>
                    <?php
                    $__coachRank = (int)($data['current_rank'] ?? ($__isClassicOrder ? 1 : 3));
                    if ($__isDynamicGame) {
                        $__coachR = $__dynRank($__coachRank);
                        $__coachRankIcon = $__coachR['icon'];
                        $__coachRankName = $__coachR['name'];
                    } else {
                        $__coachRankNames = $__isClassicOrder
                            ? [0=>'Unranked',1=>'Bronze',2=>'Silver',3=>'Gold',4=>'Platinum',5=>'Diamond',7=>'Challenger']
                            : [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
                        $__coachRankIcon = util_rank_img($__game, 'mini', $__coachRank);
                        $__coachRankName = $__coachRankNames[$__coachRank] ?? 'Silver';
                    }
                    ?>
                    <small style="display:inline-flex;align-items:center;justify-content:center;gap:6px;margin-top:8px;color:rgba(255,255,255,.72);font-size:13px;font-weight:800;">
                        <img src="<?= htmlspecialchars($__coachRankIcon, ENT_QUOTES) ?>" alt="rank_icon" style="width:22px;height:22px;object-fit:contain;" onerror="this.style.display='none'">
                        <span><?= t($__coachRankName) ?></span>
                    </small>
                </div>
                <?php break;
            case 'mastery': ?>
                <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/<?= $data['start_tier'] ?>.webp" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= t('Level') . ' ' . $data['start_tier'] ?>
                </span>
                <?php break;
            case 'arena': ?>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="<?= ASSET_URL ?>/core/main/img/lol/arenas/<?= $data['start_tier'] ?>.webp" alt="rank_icon"
                        class="current-summary-rank-img">
                    <span class="title current-summary-rank-name">
                        <?= util_get_lol_rank($data['start_tier']) ?>
                    </span>
                </div>
                <?php break;
            case 'level': ?>
                <div class="game current-summary-rank-name">
                    <?= t('Level') . ' ' . $data['start_tier'] ?>
                </div>
                <?php break;
            case 'clash': ?>
                <div class="game current-summary-rank-name">
                    <?= t('Tier') . ' ' . $data['start_tier'] . ' (' . $data['hours'] . ' Booster)' ?>
                </div>
                <?php break;
            case 'val_rank': ?>
                <img src="<?= util_rank_img('val', 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <?php if ($data['start_tier'] != 0): ?>
                    <span
                        class="title current-summary-rank-name"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') ?></span>
                    <br>
                    <small class="current-summary-lp">[ <?= ($data['start_rr'] ?? $data['start_lp'] ?? 0) ?> RR ]</small>
                <?php endif;
                break;
            case 'val_win': ?>
                <img src="<?= util_rank_img('val', 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') ?>
                </span>
                <?php break;
            case 'val_placement': ?>
                <img src="<?= util_rank_img('val', 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') ?>
                </span>
                <?php break;
            case 'val_match': ?>
                <img src="<?= util_rank_img('val', 'mini', $data['start_tier']) ?>" alt="rank_icon"
                    class="current-summary-rank-img">
                <span class="title current-summary-rank-name">
                    <?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') ?>
                </span>
                <?php break;
            case 'pro-games': ?>
                <img src="<?= util_rank_img($__game, 'mini', $data['start_tier']) ?>" alt="rank_icon" class="current-summary-rank-img">
                <span class="title current-summary-rank-name"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], $__game) ?></span>
                <?php break;
            case 'duo-pass': ?>
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)$data['start_tier'], $data['start_division'] ?? null); ?>
                    <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon" class="current-summary-rank-img" onerror="this.style.display='none'">
                    <span class="title current-summary-rank-name"><?= htmlspecialchars($__r['name'], ENT_QUOTES) ?></span>
                <?php else: ?>
                <img src="<?= util_rank_img($__game, 'mini', $data['start_tier']) ?>" alt="rank_icon" class="current-summary-rank-img">
                <span class="title current-summary-rank-name"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], $__game) ?></span>
                <?php endif; break;
            default: ?>
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)($data['start_tier'] ?? 0), $data['start_division'] ?? null); ?>
                <?php if ($__gameSlugRaw === 'counter-strike-2' && (int)($data['start_tier'] ?? 0) === 0): ?>
                <i class="fa-solid fa-circle-question current-new-account-icon" aria-label="New Account" style="color:#72c8ff;font-size:32px;margin-right:9px"></i>
                <span class="title"><?= t('New Account') ?></span>
                <?php else: ?>
                <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon" onerror="this.style.display='none'">
                <span class="title"><?= !empty($data['start_tier']) ? htmlspecialchars($__r['name'], ENT_QUOTES) : t('Unranked') ?></span>
                <?php endif; ?>
            <?php elseif ($__game === 'lol'): ?>
                <img src="<?= util_rank_img('lol', 'mini', 3) ?>" alt="rank_icon">
                <span class="title"><?= t('Silver I') ?></span>
            <?php else: ?>
                <img src="<?= util_rank_img('val', 'mini', (int)($data['start_tier'] ?? 0)) ?>" alt="rank_icon">
                <span class="title"><?= !empty($data['start_tier']) ? util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') : t('Unranked') ?></span>
            <?php endif; ?>
                <?php break;
        } ?>
    </div>
    <?php if ($data['type'] != 'coaching') { ?>
        <img src="<?= ASSET_URL ?>/website/images/arrow-summary.svg" alt="arrow_icon">
    <?php } ?>
    <?php switch ($data['type']) {
        case 'rank': ?>
            <div class="to">
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)$data['end_tier'], $data['end_division'] ?? null, $data['end_lp'] ?? null); ?>
                    <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon" class="desired-summary-rank-img" onerror="this.style.display='none'">
                    <span class="title desired-summary-rank-name"><?= htmlspecialchars($__r['name'], ENT_QUOTES) ?></span>
                    <br>
                    <?php if ($__r['no_divs'] && !$__isWingman): ?>
                        <small class="desired-summary-lp">[ <?= htmlspecialchars((string)($data['end_lp'] ?? 0), ENT_QUOTES) ?> <?= htmlspecialchars($__r['points_label'], ENT_QUOTES) ?> ]</small>
                    <?php endif; ?>
                <?php else: ?>
                <img src="<?= util_rank_img($__game, 'mini', $data['end_tier']) ?>" alt="rank_icon"
                    class="desired-summary-rank-img">
                <span
                    class="title desired-summary-rank-name"><?= util_format_rank_advanced($data['end_tier'], $data['end_division'], $__game) ?></span>
                <br>
                <?php if ($data['end_tier'] >= $__apexFrom): ?>
                    <small class="desired-summary-lp">[ <?= $data['end_lp'] ?> LP ]</small>
                <?php endif; endif; ?>
            </div>
            <?php break;

        case 'win': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Wins') ?>
                </div>
            </div>
            <?php break;

        case 'placement': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Matches') ?>
                </div>
            </div>
            <?php break;

        case 'match': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Matches') ?>
                </div>
            </div>
            <?php break;

        case 'ranked-5s': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= (int)($data['matches'] ?? 1) ?></span>
                    <?= t('Games') ?>
                    <small style="display:block;margin-top:4px;font-size:12px;font-weight:800;color:rgba(255,255,255,.72);">
                        <?= $__ranked5sBoostersCount ?> <?= ($__ranked5sBoostersCount === 1) ? t('Booster') : t('Boosters') ?>
                    </small>
                </div>
            </div>
            <?php break;

        case 'normal': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Matches') ?>
                </div>
            </div>
            <?php break;

        case 'coaching': ?>
            <?php break;

        case 'mastery': ?>
            <div class="to">
                <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/<?= $data['end_tier'] ?>.webp" alt="rank_icon"
                    class="desired-summary-rank-img">
                <span class="title desired-summary-rank-name">
                    <?= t('Level') . ' ' . $data['end_tier'] ?>
                </span>
            </div>
            <?php break;

        case 'arena': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Wins') ?>
                </div>
            </div>
            <?php break;

        case 'level': ?>
            <div class="to">
                <div class="count current-summary-rank-name">
                    <?= t('Level') . ' ' . $data['end_tier'] ?>
                </div>
            </div>
            <?php break;

        case 'clash': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Matches') ?>
                </div>
            </div>
            <?php break;

        case 'pro-games': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Games') ?>
                </div>
            </div>
            <?php break;

        case 'duo-pass': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= (int)($data['hours'] ?? 3) ?></span>
                    <?= t('Hours') ?>
                </div>
            </div>
            <?php break;

        case 'val_rank': ?>
            <div class="to">
                <img src="<?= util_rank_img('val', 'mini', $data['end_tier']) ?>" alt="rank_icon"
                    class="desired-summary-rank-img">
                <span
                    class="title desired-summary-rank-name"><?= util_format_rank_advanced($data['end_tier'], $data['end_division'], 'val') ?></span>
                <br>
                <?php if ($data['end_tier'] >= 8): ?>
                    <small class="desired-summary-lp">[ <?= ($data['end_rr'] ?? $data['end_lp'] ?? 0) ?> RR ]</small>
                <?php endif; ?>
            </div>
            <?php break;

        case 'val_win': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Wins') ?>
                </div>
            </div>
            <?php break;

        case 'val_placement': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Matches') ?>
                </div>
            </div>
            <?php break;

        case 'val_match': ?>
            <div class="to">
                <div class="count">
                    <span class="win-count"><?= $data['matches'] ?></span>
                    <?= t('Matches') ?>
                </div>
            </div>
            <?php break;

        default: ?>
            <div class="to">
                <?php if ($__isDynamicGame): $__r = $__dynRank((int)($data['end_tier'] ?? 0), $data['end_division'] ?? null); ?>
                <img src="<?= htmlspecialchars($__r['icon'], ENT_QUOTES) ?>" alt="rank_icon" onerror="this.style.display='none'">
                <span class="title"><?= !empty($data['end_tier']) ? htmlspecialchars($__r['name'], ENT_QUOTES) : t('Unranked') ?></span>
            <?php elseif ($__game === 'lol'): ?>
                <img src="<?= util_rank_img('lol', 'mini', 4) ?>" alt="rank_icon">
                <span class="title"><?= t('Gold IV') ?></span>
            <?php else: ?>
                <img src="<?= util_rank_img('val', 'mini', (int)($data['end_tier'] ?? 0)) ?>" alt="rank_icon">
                <span class="title"><?= !empty($data['end_tier']) ? util_format_rank_advanced($data['end_tier'], $data['end_division'], 'val') : t('Unranked') ?></span>
            <?php endif; ?>
            </div>
            <?php break;
    } ?>
</div>

<div class="order-options">
    <div class="option">
        <div class="title">
            <img src="<?= ASSET_URL ?>/website/images/checkout/boost-type.svg" alt="boost_type"><?= t('Boost Type') ?>
        </div>
        <div class="value">
            <?= $data['name'] ?>
        </div>
    </div>
    <?php if ($__gameSlugRaw !== 'counter-strike-2'): ?>
    <div class="option">
        <div class="title">
            <img src="<?= ASSET_URL ?>/website/images/checkout/server.svg" alt="server"><?= t('Server') ?>
        </div>
        <div class="value">
            <?= util_format_server($data['server']) ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if ((int)($data['form_id'] ?? 0) === RANKED_5S_FORM_ID): ?>
    <div class="option">
        <div class="title">
            <i class="fa-solid fa-gamepad" style="color:#a7a4ff;margin-right:8px;"></i><?= t('Games') ?>
        </div>
        <div class="value">
            <?= (int)($data['matches'] ?? 1) ?> <?= t('Games') ?>
        </div>
    </div>
    <div class="option">
        <div class="title">
            <i class="fa-solid fa-users" style="color:#77e0b5;margin-right:8px;"></i><?= t('Boosters') ?>
        </div>
        <div class="value">
            <?= $__ranked5sBoostersCount ?>
        </div>
    </div>
    <div class="option">
        <div class="title">
            <i class="fa-solid fa-street-view" style="color:#a7a4ff;margin-right:8px;"></i><?= t('Your Role') ?>
        </div>
        <div class="value">
            <?php
            $__ranked5sRoleLabels = [
                'TopLane' => 'TopLane',
                'Jungle' => 'Jungle',
                'MidLane' => 'MidLane',
                'AdCarry' => 'AdCarry',
                'Support' => 'Support',
            ];
            $__ranked5sRole = trim((string)($data['roles'] ?? 'TopLane'));
            echo t($__ranked5sRoleLabels[$__ranked5sRole] ?? $__ranked5sRole ?: 'TopLane');
            ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!in_array((int)($data['form_id'] ?? 0), [15, 16, 25, 29])): ?>
    <div class="option">
        <div class="title">
            <img src="<?= ASSET_URL ?>/website/images/checkout/queue.svg" alt="queue"><?= t('Queue') ?>
        </div>
        <div class="value">
            <?= util_format_default_type($data['queue_type']) ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($data['coach_type'])): ?>
        <div class="option">
            <div class="title">
                <img src="<?= ASSET_URL ?>/website/images/checkout/server.svg" alt="coach_type"><?= t('Coach Type') ?>
            </div>
            <div class="value">
                <?= util_format_default_type($data['coach_type']) ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array((int)($data['form_id'] ?? 0), [15, 16, 25, 29])): ?>
    <div class="option">
        <div class="title">
            <img src="<?= ASSET_URL ?>/website/images/checkout/duo.svg" alt="duo"><?= t('Duo') ?>
        </div>
        <div class="value">
            <?= util_format_yes_no($data['is_duo']) ?>
        </div>
    </div>
    <?php endif; ?>
    <?php $__couponDisplay = util_format_discount_display($data['id']); ?>
    <div class="option option-coupon<?= $__couponDisplay !== 'No Discount' ? ' option-coupon--active' : '' ?>">
        <div class="title">
            <img src="<?= ASSET_URL ?>/website/images/checkout/coupon.svg" alt="coupon"><?= t('Coupon') ?>
        </div>
        <div class="value">
            <?= $__couponDisplay ?>
        </div>
    </div>

    <?php if ($__isMultiRequestSummary): ?>
    <div class="option checkout-requested-boosters<?= empty($__summaryRequestedBoosters) ? ' is-empty' : '' ?>" id="checkoutRequestedBoostersSummary">
        <div class="title">
            <i class="fa-solid fa-user-group" aria-hidden="true"></i><?= t('Requested Boosters') ?>
        </div>
        <div class="value checkout-requested-boosters__list">
            <?php if (!empty($__summaryRequestedBoosters)): ?>
                <?php foreach ($__summaryRequestedIds as $__summaryRequestedId):
                    if (empty($__summaryRequestedBoosters[$__summaryRequestedId])) continue;
                    $__summaryRequestedBooster = $__summaryRequestedBoosters[$__summaryRequestedId];
                ?>
                <span class="checkout-requested-booster" data-id="<?= (int)$__summaryRequestedBooster['id'] ?>">
                    <img src="<?= htmlspecialchars($__summaryRequestedBooster['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                    <span><?= htmlspecialchars($__summaryRequestedBooster['name'], ENT_QUOTES, 'UTF-8') ?></span>
                </span>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="checkout-requested-boosters__any"><?= t('Any available') ?></span>
            <?php endif; ?>
        </div>
    </div>
    <style>
    .checkout-requested-boosters{align-items:flex-start!important}
    .checkout-requested-boosters>.title{padding-top:5px}
    .checkout-requested-boosters__list{display:flex!important;flex-wrap:wrap;justify-content:flex-end;gap:6px;max-width:62%}
    .checkout-requested-booster{display:inline-flex;align-items:center;gap:6px;padding:4px 8px 4px 5px;border:1px solid rgba(113,101,255,.28);border-radius:999px;background:rgba(113,101,255,.1);color:#fff;font-size:11px;font-weight:700}
    .checkout-requested-booster img{width:22px;height:22px;border-radius:50%;object-fit:cover}
    .checkout-requested-boosters__any{color:rgba(255,255,255,.55);font-size:12px}
    </style>
    <?php endif; ?>

    <?php
    $invoice = db_get_row('invoices', ['order_id' => $data['order_id']]);

    if ($invoice['coins_used'] != 0.00): ?>
        <div class="option coins-list">
            <div class="title">
                <img src="<?= ASSET_URL ?>/core/main/img/coin.png"><?= t('LB Coins Spent') ?>
            </div>
            <div class="value">
                <?= $invoice['coins_used'] ?>
            </div>
        </div>
<?php endif; ?>
</div>

<?php if (isset($data['form_id'])): ?>
<?php
    $__isCoachForm = in_array((int)($data['form_id'] ?? 0), [15, 16]);
    $__isProGames  = (int)($data['form_id'] ?? 0) === 26;
    $__isRanked5s = (int)($data['form_id'] ?? 0) === RANKED_5S_FORM_ID;
    $__isMultiRequestCheckout = in_array((int)($data['form_id'] ?? 0), [4, 19, 29], true);
?>
<details class="checkout-optional-extras">
    <summary>
        <span><i class="fa-solid fa-user-plus" aria-hidden="true"></i> <?= $__isCoachForm ? t('Request a coach &amp; add a note') : t('Request a booster &amp; add a note') ?></span>
        <span class="checkout-optional-extras__badge"><?= t('Optional') ?></span>
        <i class="fa-solid fa-chevron-down checkout-optional-extras__chevron" aria-hidden="true"></i>
    </summary>
    <div class="checkout-optional-extras__body">
<?php if (!$__isMultiRequestCheckout && !$__isRanked5s && !empty($data['booster_id'])): ?>
    <?php
        $__pgBooster = db_get_row('boosters', ['id' => (int)$data['booster_id']]);
        $__pgAvatar  = !empty($__pgBooster['icon']) ? $__pgBooster['icon'] : 'https://lolboost.gg/public/uploads/icons/default.png';
        $__pgName    = htmlspecialchars($__pgBooster['username'] ?? 'Booster', ENT_QUOTES);
    ?>
    <div class="form-group">
        <label><?= t('Your Booster') ?></label>
        <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 16px;">
            <img src="<?= $__pgAvatar ?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover;">
            <div>
                <div style="font-weight:700;color:#fff;"><?= $__pgName ?></div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.5);"><?= t('Your requested booster') ?></div>
            </div>
            <div style="margin-left:auto;">
                <span style="background:rgba(124,92,252,.2);color:#a78bfa;padding:4px 10px;border-radius:20px;font-size:.8rem;font-weight:600;"><?= t('Confirmed') ?></span>
            </div>
        </div>
        <input type="hidden" name="booster_id" value="<?= (int)$data['booster_id'] ?>">
    </div>
<?php elseif (!$__isRanked5s || $__isMultiRequestCheckout): ?>
    <div class="form-group" id="booster_select_wrap">
    <label><?= $__isCoachForm ? t('Request Coach (Optional)') : t('Request Booster (Optional)') ?></label>

    <?php
        global $db;
        $__lbOnline = function_exists('lb_booster_online_map') ? lb_booster_online_map() : [];
        $__lbProf = []; $__lbOng = [];
        // Fetch profile per booster using db_get_row (known to work on this platform)
        foreach ($data['boosters'] as $__pb) {
            $__pbid = (int)($__pb['id'] ?? 0);
            if ($__pbid <= 0) continue;
            try {
                $__prof_row = db_get_row('booster_profiles', ['booster_id' => $__pbid], true);
                if (!empty($__prof_row)) {
                    $__prof_row = (array)$__prof_row;
                    $__lbProf[$__pbid] = [
                        'rank'     => $__prof_row['rank'] ?? '',
                        'timezone' => $__prof_row['timezone'] ?? '',
                    ];
                }
            } catch (Throwable $e) {}
        }
        try {
            $__ong = $db->run("SELECT booster_id, COUNT(*) as cnt FROM orders WHERE status = 'IN_PROGRESS' AND booster_id IS NOT NULL AND booster_id > 0 GROUP BY booster_id");
            if (!empty($__ong)) {
                foreach ($__ong as $__o) {
                    if (is_object($__o)) {
                        $__key2 = (int)($__o->booster_id ?? 0);
                        if ($__key2 > 0) $__lbOng[$__key2] = (int)($__o->cnt ?? 0);
                    } else {
                        $__o = (array)$__o;
                        $__key2 = (int)($__o['booster_id'] ?? 0);
                        if ($__key2 > 0) $__lbOng[$__key2] = (int)($__o['cnt'] ?? 0);
                    }
                }
            }
        } catch (Throwable $e) {}
        $__lbIcons = [];
        try {
            $__ir = db_get_rows('boosters', ['select' => 'id,icon'], true);
            if (!empty($__ir)) foreach ($__ir as $__r) { $__id2=(int)($__r['id']??0); if($__id2>0) $__lbIcons[$__id2]=$__r['icon']??'';}
        } catch (Throwable $e) {}

        $__lbAll = [];
        foreach ($data['boosters'] as $__b) {
            $__bid  = (int)($__b['id']??0);
            $__icon = !empty($__b['icon']) ? $__b['icon'] : (!empty($__lbIcons[$__bid]) ? $__lbIcons[$__bid] : 'https://lolboost.gg/public/uploads/icons/default.png');
            $__prof = $__lbProf[$__bid] ?? [];
            $__tz   = trim($__prof['timezone'] ?? '');
            $__isOn = !empty($__lbOnline[$__bid]);
            $__lbAll[] = [
                'id'      => $__bid,
                'name'    => $__b['username'] ?? '',
                'icon'    => $__icon,
                'online'  => $__isOn,
                'rank'    => ucfirst(strtolower($__prof['rank'] ?? '')),
                'tz'      => $__tz,
                'ongoing' => $__lbOng[$__bid] ?? 0,
                'selected'=> !empty($data['booster_id']) && ((int)$data['booster_id'] === $__bid),
            ];
        }
        // Only show boosters with timezone set
        $__lbAll = array_values(array_filter($__lbAll, fn($b)=>!empty($b['tz'])));
        $__lbFeatured = array_values(array_filter($__lbAll, fn($b)=>$b['online']));
        usort($__lbFeatured, fn($a,$b)=>$a['ongoing']<=>$b['ongoing']);
        $__isOnlineSlider = !empty($__lbFeatured);
        if (empty($__lbFeatured)) {
            $__lbFeatured = array_slice($__lbAll, 0, 8);
        }
    ?>

    <?php if ($__isMultiRequestCheckout):
        $__checkoutRequestedIds = array_values(array_unique(array_filter(array_map(
            'intval',
            preg_split('/[\s,|]+/', (string)($data['selected_boosters'] ?? ''), -1, PREG_SPLIT_NO_EMPTY)
        ))));
        if (empty($__checkoutRequestedIds) && !empty($data['booster_id'])) {
            $__checkoutRequestedIds[] = (int)$data['booster_id'];
        }
        $__checkoutRequestLimit = max(1, min(4, (int)$__ranked5sBoostersCount));
        $__checkoutRequestedIds = array_slice($__checkoutRequestedIds, 0, $__checkoutRequestLimit);
    ?>
    <div class="lb-multi-request" data-limit="<?= $__checkoutRequestLimit ?>">
        <div class="lb-multi-request__head">
            <strong><?= t('Request your boosters') ?></strong>
            <span><b id="lbMultiRequestCount"><?= count($__checkoutRequestedIds) ?></b>/<?= $__checkoutRequestLimit ?> <?= t('selected') ?></span>
        </div>
        <input type="hidden" name="selected_boosters" id="lbMultiRequestedBoosters" value="<?= htmlspecialchars(implode(',', $__checkoutRequestedIds), ENT_QUOTES, 'UTF-8') ?>">
        <div class="lb-multi-request__grid">
            <?php foreach ($__lbAll as $__requestBooster):
                $__requestId = (int)$__requestBooster['id'];
                $__requestSelected = in_array($__requestId, $__checkoutRequestedIds, true);
            ?>
            <button type="button" class="lb-multi-request__card<?= $__requestSelected ? ' is-selected' : '' ?>" data-id="<?= $__requestId ?>">
                <img src="<?= htmlspecialchars($__requestBooster['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                <span><?= htmlspecialchars($__requestBooster['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <i class="fa-solid fa-check"></i>
            </button>
            <?php endforeach; ?>
        </div>
        <small><?= t('Pick up to the number of boosters booked. Leave empty for any available booster.') ?></small>
    </div>
    <style>
    .lb-multi-request{margin-top:8px}.lb-multi-request__head{display:flex;justify-content:space-between;gap:12px;margin-bottom:10px;color:#fff}.lb-multi-request__head span{font-size:12px;color:rgba(255,255,255,.55)}.lb-multi-request__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;max-height:250px;overflow:auto;padding-right:3px}.lb-multi-request__card{display:flex!important;align-items:center;gap:9px;padding:9px!important;border-radius:11px!important;border:1px solid rgba(255,255,255,.08)!important;background:#101221!important;color:#fff!important;text-align:left}.lb-multi-request__card img{width:34px;height:34px;border-radius:50%;object-fit:cover}.lb-multi-request__card span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;font-weight:700}.lb-multi-request__card i{margin-left:auto;opacity:0;color:#8b7cff}.lb-multi-request__card.is-selected{border-color:#7165ff!important;background:rgba(113,101,255,.13)!important}.lb-multi-request__card.is-selected i{opacity:1}.lb-multi-request>small{display:block;margin-top:9px;color:rgba(255,255,255,.42)}
    </style>
    <script>
    document.addEventListener('DOMContentLoaded',function(){
        var wrap=document.querySelector('.lb-multi-request');
        if(!wrap)return;
        var input=document.getElementById('lbMultiRequestedBoosters');
        var count=document.getElementById('lbMultiRequestCount');
        var limit=parseInt(wrap.dataset.limit||'1',10);
        var boosterData=<?= json_encode($__lbAll, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
        function ids(){return (input.value||'').split(',').filter(Boolean);}
        function syncCheckoutSummary(){
            var summary=document.getElementById('checkoutRequestedBoostersSummary');
            if(!summary)return;
            var list=summary.querySelector('.checkout-requested-boosters__list');
            var selectedIds=ids();
            list.innerHTML='';
            selectedIds.forEach(function(id){
                var booster=boosterData.find(function(item){return String(item.id)===String(id);});
                if(!booster)return;
                var chip=document.createElement('span');
                chip.className='checkout-requested-booster';
                chip.dataset.id=String(booster.id);
                var avatar=document.createElement('img');
                avatar.src=booster.icon||'';
                avatar.alt='';
                var name=document.createElement('span');
                name.textContent=booster.name||'Booster';
                chip.appendChild(avatar);
                chip.appendChild(name);
                list.appendChild(chip);
            });
            if(!list.children.length){
                var any=document.createElement('span');
                any.className='checkout-requested-boosters__any';
                any.textContent='<?= addslashes(t('Any available')) ?>';
                list.appendChild(any);
            }
            summary.classList.toggle('is-empty',selectedIds.length===0);
        }
        wrap.querySelectorAll('.lb-multi-request__card').forEach(function(card){
            card.addEventListener('click',function(){
                var list=ids(),id=String(card.dataset.id),pos=list.indexOf(id);
                if(pos>=0)list.splice(pos,1);
                else if(list.length<limit)list.push(id);
                else { if(window.toastr)toastr.warning('You can request up to '+limit+' boosters.'); return; }
                input.value=list.join(',');
                card.classList.toggle('is-selected',list.indexOf(id)>=0);
                count.textContent=String(list.length);
                syncCheckoutSummary();
            });
        });
        syncCheckoutSummary();
    });
    </script>
    <?php else: ?>
    <select name="booster_id" id="booster_id" style="display:none!important;visibility:hidden;">
        <option value=""></option>
        <?php foreach ($data['boosters'] as $__b):
            $__sel = !empty($data['booster_id']) && ((int)$data['booster_id']==(int)$__b['id']); ?>
        <option value="<?= (int)$__b['id'] ?>" <?= $__sel?'selected':'' ?>><?= htmlspecialchars($__b['username']??'',ENT_QUOTES) ?></option>
        <?php endforeach; ?>
    </select>

    <div id="lbbp-v5">

        <!-- Slider header -->
        <div class="lbv5-hd">
            <div class="lbv5-title">
                <?php if ($__isOnlineSlider): ?>
                    <span class="lbv5-pulse"></span><?= $__isCoachForm ? t('Online Coaches') : t('Online Boosters') ?>
                <?php else: ?>
                    <?= $__isCoachForm ? t('Top Coaches') : t('Top Boosters') ?>
                <?php endif; ?>
            </div>
            <div class="lbv5-nav">
                <button type="button" class="lbv5-arr" id="lbv5-prev">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" class="lbv5-arr" id="lbv5-next">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- Slider track -->
        <div class="lbv5-viewport" id="lbv5-vp">
            <div class="lbv5-track" id="lbv5-track"></div>
        </div>

        <!-- Search all boosters -->
        <div class="lbv5-search-wrap" id="lbv5-sw">
            <div class="lbv5-search-row">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" id="lbv5-search" placeholder="<?= $__isCoachForm ? t('Search all coaches...') : t('Search all boosters...') ?>">
                <svg class="lbv5-chevron" id="lbv5-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <div class="lbv5-selected" id="lbv5-selected" hidden>
                <img id="lbv5-selected-avatar" src="" alt="">
                <span>
                    <small><?= t('Requested booster') ?></small>
                    <strong id="lbv5-selected-name"></strong>
                </span>
                <button type="button" id="lbv5-selected-clear" aria-label="<?= t('Remove selected booster') ?>">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <div class="lbv5-dd" id="lbv5-dd"></div>
        </div>

    </div>

    <style>
    #lbbp-v5 { margin-top:8px; }
    #lbbp-v5 * { box-sizing:border-box; }
    body.lbv5-noscroll { overflow-x:hidden !important; }




    /* Header */
    .lbv5-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
    .lbv5-title { font-size:13px; font-weight:700; color:rgba(255,255,255,.75); display:flex; align-items:center; gap:8px; }
    .lbv5-pulse { display:inline-block; width:8px; height:8px; border-radius:50%; background:#35d07f; flex:0 0 auto; animation:lbv5p 1.5s ease-out infinite; }
    @keyframes lbv5p { 0%{box-shadow:0 0 0 0 rgba(53,208,127,.5)} 70%{box-shadow:0 0 0 7px rgba(53,208,127,0)} 100%{box-shadow:0 0 0 0 rgba(53,208,127,0)} }
    .lbv5-nav { display:flex; gap:6px; }
    .lbv5-arr { width:30px; height:30px; border-radius:50%; border:1px solid rgba(255,255,255,.12)!important; background:rgba(255,255,255,.04)!important; color:rgba(255,255,255,.7)!important; cursor:pointer; display:flex!important; align-items:center; justify-content:center; padding:0!important; transition:.15s; }
    .lbv5-arr svg { width:14px; height:14px; }
    .lbv5-arr:hover { border-color:rgba(255,255,255,.3)!important; background:rgba(255,255,255,.1)!important; color:#fff!important; }
    .lbv5-arr:disabled { opacity:.3; cursor:default; }

    /* Viewport + track */
    .lbv5-viewport { overflow:hidden; width:100%; margin-bottom:14px; }
    @media (max-width:768px) {
        .lbv5-hd { margin-bottom:10px; }
        .lbv5-arr { width:28px!important; height:28px!important; }
        .lbv5-nav { display:none; }
        /* 1 card fully visible + small peek of next card */
        .lbv5-viewport { overflow-x:auto; -webkit-overflow-scrolling:touch; scroll-snap-type:x mandatory; scrollbar-width:none; }
        .lbv5-viewport::-webkit-scrollbar { display:none; }
        .lbv5-track { transition:none!important; }
        .lbv5-card { scroll-snap-align:start; flex:0 0 calc(100% - 30px) !important; }
    }
    .lbv5-track { display:flex; gap:10px; transition:transform .3s cubic-bezier(.4,0,.2,1); will-change:transform; }

    /* Card */
    .lbv5-card { flex:0 0 calc(25% - 7.5px); background:rgba(255,255,255,.04); border:1.5px solid rgba(255,255,255,.07); border-radius:14px; padding:13px; cursor:pointer; transition:border-color .18s,background .18s,transform .15s; position:relative; }
    .lbv5-card:hover { border-color:rgba(120,103,255,.5); background:rgba(120,103,255,.06); transform:translateY(-1px); }
    .lbv5-card.sel { border-color:rgba(120,103,255,.9)!important; background:rgba(120,103,255,.1)!important; }
    .lbv5-check { position:absolute; top:9px; right:9px; width:18px; height:18px; border-radius:50%; background:rgba(120,103,255,.9); display:none; align-items:center; justify-content:center; font-size:10px; color:#fff; }
    .lbv5-card.sel .lbv5-check { display:flex; }
    .lbv5-card-top { display:flex; align-items:center; gap:9px; margin-bottom:10px; }
    .lbv5-av { position:relative; flex:0 0 auto; }
    .lbv5-av img { width:40px; height:40px; border-radius:50%; object-fit:cover; display:block; border:2px solid rgba(255,255,255,.07); }
    .lbv5-card.sel .lbv5-av img { border-color:rgba(120,103,255,.5); }
    .lbv5-dot { position:absolute; bottom:1px; right:1px; width:10px; height:10px; border-radius:50%; border:2px solid #0d0d1a; background:rgba(255,255,255,.18); }
    .lbv5-dot.on { background:#35d07f; }
    .lbv5-info { flex:1; min-width:0; }
    .lbv5-name { font-size:13px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:2px; }
    .lbv5-status { font-size:11px; font-weight:600; color:rgba(255,255,255,.3); }
    .lbv5-status.on { color:#35d07f; }
    .lbv5-stats { border-top:1px solid rgba(255,255,255,.06); padding-top:9px; display:flex; flex-direction:column; gap:4px; margin-bottom:10px; }
    .lbv5-stat { display:flex; justify-content:space-between; }
    .lbv5-sk { font-size:11px; color:rgba(255,255,255,.35); }
    .lbv5-sv { font-size:11px; font-weight:600; color:rgba(255,255,255,.75); }
    .lbv5-btn { width:100%; padding:7px 0; border-radius:8px; border:1px solid rgba(255,255,255,.13)!important; background:rgba(255,255,255,.05)!important; color:rgba(255,255,255,.75)!important; font-size:11px; font-weight:700; cursor:pointer; transition:.15s; font-family:inherit; }
    .lbv5-btn:hover { border-color:rgba(120,103,255,.5)!important; background:rgba(120,103,255,.15)!important; color:#fff!important; }
    .lbv5-btn.sel { border-color:rgba(120,103,255,.9)!important; background:rgba(120,103,255,.28)!important; color:#c4b5fd!important; }

    /* Search */
    .lbv5-search-wrap { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.09); border-radius:12px; overflow:hidden; transition:border-color .15s; }
    .lbv5-search-wrap.open { border-color:rgba(120,103,255,.4); }
    .lbv5-search-row { display:flex; align-items:center; gap:10px; padding:10px 14px; }
    .lbv5-search-row > svg:first-child { width:14px; height:14px; color:rgba(255,255,255,.35); flex:0 0 auto; }
    .lbv5-search-row input { flex:1; background:transparent; border:none!important; outline:none!important; color:#fff!important; font-size:13px; height:22px!important; font-family:inherit; box-shadow:none!important; padding:0!important; }
    .lbv5-search-row input::placeholder { color:rgba(255,255,255,.3); }
    .lbv5-chevron { width:14px; height:14px; color:rgba(255,255,255,.3); flex:0 0 auto; transition:transform .2s; cursor:pointer; }
    .lbv5-search-wrap.open .lbv5-chevron { transform:rotate(180deg); }
    .lbv5-dd { display:none; border-top:1px solid rgba(255,255,255,.07); max-height:230px; overflow-y:auto; }
    .lbv5-search-wrap.open .lbv5-dd { display:block; }
    .lbv5-dd::-webkit-scrollbar { width:4px; }
    .lbv5-dd::-webkit-scrollbar-thumb { background:rgba(255,255,255,.12); border-radius:4px; }
    .lbv5-dd-row { display:flex; align-items:center; gap:10px; padding:8px 14px; cursor:pointer; transition:background .1s; }
    .lbv5-dd-row:hover { background:rgba(255,255,255,.05); }
    .lbv5-dd-row.sel { background:rgba(120,103,255,.12); }
    .lbv5-dd-row.hidden { display:none!important; }
    .lbv5-dd-row img { width:28px; height:28px; border-radius:50%; object-fit:cover; flex:0 0 auto; }
    .lbv5-dd-info { flex:1; min-width:0; }
    .lbv5-dd-name { font-size:13px; font-weight:600; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .lbv5-dd-sub { font-size:11px; color:rgba(255,255,255,.35); margin-top:1px; }
    .lbv5-dd-sub .on { color:#35d07f; font-weight:700; }
    .lbv5-dd-time { font-size:11px; color:rgba(255,255,255,.4); white-space:nowrap; }
    .lbv5-dd-check { color:#c4b5fd; font-size:13px; flex:0 0 auto; visibility:hidden; }
    .lbv5-dd-row.sel .lbv5-dd-check { visibility:visible; }
    .lbv5-dd-empty { padding:14px; font-size:13px; color:rgba(255,255,255,.3); text-align:center; display:none; }
    </style>

    <script>
    (function(){
        var featured = <?= json_encode($__lbFeatured, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
        var allData  = <?= json_encode($__lbAll,      JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
        var hidSel   = document.getElementById('booster_id');
        var track    = document.getElementById('lbv5-track');
        var vp       = document.getElementById('lbv5-vp');
        var prev     = document.getElementById('lbv5-prev');
        var next     = document.getElementById('lbv5-next');
        var sw       = document.getElementById('lbv5-sw');
        var searchEl = document.getElementById('lbv5-search');
        var dd       = document.getElementById('lbv5-dd');
        var selected = document.getElementById('lbv5-selected');
        var selectedAvatar = document.getElementById('lbv5-selected-avatar');
        var selectedName = document.getElementById('lbv5-selected-name');
        var selectedClear = document.getElementById('lbv5-selected-clear');
        var selId    = <?= json_encode((string)($data['booster_id'] ?? '')) ?>;
        var idx      = 0; // current slide offset (in cards)
        document.body.classList.add('lbv5-noscroll');
        // Debug: log first 3 boosters to verify tz data
        if(window.console && allData.length) {
            console.log('[LB] Sample booster tz data:', allData.slice(0,3).map(function(b){ return {name:b.name, tz:b.tz, ongoing:b.ongoing}; }));
        }
        var PER_PAGE = 4;

        function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
        function syncHidden(id){
            selId=String(id);
            for(var i=0;i<hidSel.options.length;i++) hidSel.options[i].selected=(String(hidSel.options[i].value)===selId);

            var selectedBooster = allData.find(function(b){ return String(b.id)===selId; });
            selected.hidden = !selectedBooster;
            sw.classList.toggle('has-selection', !!selectedBooster);
            if(selectedBooster){
                selectedAvatar.src = selectedBooster.icon || '';
                selectedName.textContent = selectedBooster.name || '';
            } else {
                selectedAvatar.src = '';
                selectedName.textContent = '';
            }
        }

        selectedClear.addEventListener('click', function(event){
            event.preventDefault();
            event.stopPropagation();
            deselectAll();
            syncHidden('');
        });

        syncHidden(selId);

        /* ---- BUILD SLIDER CARDS ---- */
        featured.forEach(function(b){
            var c = document.createElement('div');
            c.className = 'lbv5-card'+(String(b.id)===selId?' sel':'');
            c.dataset.id = b.id;
            c.innerHTML =
                '<div class="lbv5-check">&#10003;</div>'+
                '<div class="lbv5-card-top">'+
                    '<div class="lbv5-av"><img src="'+b.icon+'" alt=""><span class="lbv5-dot'+(b.online?' on':'')+'"></span></div>'+
                    '<div class="lbv5-info">'+
                        '<div class="lbv5-name">'+esc(b.name)+'</div>'+
                        '<div class="lbv5-status'+(b.online?' on':'')+'">&#9679; '+(b.online?'Online':'Offline')+'</div>'+
                    '</div>'+
                '</div>'+
                '<div class="lbv5-stats">'+
                    '<div class="lbv5-stat"><span class="lbv5-sk">Local Time</span><span class="lbv5-sv lbv5-gt" data-tz="'+esc(b.tz||'')+'">'+'&mdash;'+'</span></div>'+
                    '<div class="lbv5-stat"><span class="lbv5-sk">Ongoing</span><span class="lbv5-sv">'+b.ongoing+'</span></div>'+
                '</div>'+
                '<button type="button" class="lbv5-btn'+(String(b.id)===selId?' sel':'')+'">'+
                    (String(b.id)===selId?'&#10003; Requested':'+ Request')+
                '</button>';
            c.addEventListener('click', function(){
                var was=c.classList.contains('sel');
                deselectAll();
                if(!was){ selectCard(c,b.id); slideToCard(b.id); } else { syncHidden(''); }
            });
            track.appendChild(c);
        });

        /* ---- SLIDE LOGIC ---- */
        function cardW(){ return track.children.length ? track.children[0].offsetWidth+10 : 0; }
        function maxIdx(){ return Math.max(0, featured.length - PER_PAGE); }

        function slide(){
            idx = Math.min(Math.max(idx,0), maxIdx());
            track.style.transform = 'translateX(-'+(idx * cardW())+'px)';
            prev.disabled = idx <= 0;
            next.disabled = idx >= maxIdx();
        }
        function buildCard(b){
            var c = document.createElement('div');
            c.className = 'lbv5-card';
            c.dataset.id = b.id;
            c.innerHTML =
                '<div class="lbv5-check">&#10003;</div>'+
                '<div class="lbv5-card-top">'+
                    '<div class="lbv5-av"><img src="'+b.icon+'" alt=""><span class="lbv5-dot'+(b.online?' on':'')+'"></span></div>'+
                    '<div class="lbv5-info">'+
                        '<div class="lbv5-name">'+esc(b.name)+'</div>'+
                        '<div class="lbv5-status'+(b.online?' on':'')+'">&#9679; '+(b.online?'Online':'Offline')+'</div>'+
                    '</div>'+
                '</div>'+
                '<div class="lbv5-stats">'+
                    '<div class="lbv5-stat"><span class="lbv5-sk">Local Time</span><span class="lbv5-sv lbv5-gt" data-tz="'+esc(b.tz||'')+'">&mdash;</span></div>'+
                    '<div class="lbv5-stat"><span class="lbv5-sk">Ongoing</span><span class="lbv5-sv">'+b.ongoing+'</span></div>'+
                '</div>'+
                '<button type="button" class="lbv5-btn">+ Request</button>';
            c.addEventListener('click', function(){
                var was=c.classList.contains('sel');
                deselectAll();
                if(!was){ selectCard(c,b.id); } else { syncHidden(''); }
            });
            return c;
        }

        // Store original inner HTML of each card for restoration
        var _cardOriginals = {};
        Array.from(track.children).forEach(function(el){
            _cardOriginals[el.dataset.id] = el.innerHTML;
        });

        function slideToCard(id){
            // Restore all cards to original content
            Array.from(track.children).forEach(function(el){
                var origId = el.dataset.origId || el.dataset.id;
                el.dataset.origId = origId;
                if(_cardOriginals[origId]) el.innerHTML = _cardOriginals[origId];
                el.dataset.id = origId;
                el.classList.remove('sel','lbv5-temp');
                // re-attach click
                (function(origB){
                    if(!origB) return;
                    el.onclick = function(){
                        var was=el.classList.contains('sel');
                        deselectAll();
                        if(!was){ selectCard(el,origB.id); slideToCard(origB.id); } else { syncHidden(''); }
                    };
                })(allData.find(function(x){ return String(x.id)===String(origId); }));
            });

            // Find booster data
            var b = null;
            allData.forEach(function(x){ if(String(x.id)===String(id)) b=x; });
            if(!b) return;

            // Use first card slot to show selected booster
            var firstCard = track.children[0];
            if(!firstCard) return;

            // Save origId if not already saved
            if(!firstCard.dataset.origId) firstCard.dataset.origId = firstCard.dataset.id;

            // Overwrite first card's content with selected booster
            firstCard.dataset.id = b.id;
            firstCard.classList.add('sel');
            firstCard.innerHTML =
                '<div class="lbv5-check" style="display:flex">&#10003;</div>'+
                '<div class="lbv5-card-top">'+
                    '<div class="lbv5-av"><img src="'+b.icon+'" alt=""><span class="lbv5-dot'+(b.online?' on':'')+'"></span></div>'+
                    '<div class="lbv5-info">'+
                        '<div class="lbv5-name">'+esc(b.name)+'</div>'+
                        '<div class="lbv5-status'+(b.online?' on':'')+'">&#9679; '+(b.online?'Online':'Offline')+'</div>'+
                    '</div>'+
                '</div>'+
                '<div class="lbv5-stats">'+
                    '<div class="lbv5-stat"><span class="lbv5-sk">Local Time</span><span class="lbv5-sv lbv5-gt" data-tz="'+esc(b.tz||'')+'">&mdash;</span></div>'+
                    '<div class="lbv5-stat"><span class="lbv5-sk">Ongoing</span><span class="lbv5-sv">'+b.ongoing+'</span></div>'+
                '</div>'+
                '<button type="button" class="lbv5-btn sel">&#10003; Selected</button>';
            firstCard.onclick = function(){
                var was = firstCard.classList.contains('sel');
                deselectAll();
                if(!was){ selectCard(firstCard, b.id); slideToCard(b.id); } else { syncHidden(''); }
            };

            syncHidden(b.id);
            tick();

            // Sync dropdown highlight
            Array.from(dd.children).forEach(function(r){ 
                r.classList.toggle('sel', String(r.dataset.id)===String(b.id));
            });

            idx = 0;
            slide();
        }
        prev.addEventListener('click', function(){ idx--; slide(); });
        next.addEventListener('click', function(){ idx++; slide(); });
        window.addEventListener('load', slide);
        window.addEventListener('resize', slide);

        /* ---- BUILD DROPDOWN ---- */
        allData.forEach(function(b){
            var row = document.createElement('div');
            row.className = 'lbv5-dd-row'+(String(b.id)===selId?' sel':'');
            row.dataset.id   = b.id;
            row.dataset.name = (b.name||'').toLowerCase();
            row.innerHTML =
                '<img src="'+b.icon+'" alt="">'+
                '<div class="lbv5-dd-info">'+
                    '<div class="lbv5-dd-name">'+esc(b.name)+'</div>'+
                    '<div class="lbv5-dd-sub">'+(b.online?'<span class="on">&#9679; Online</span>':(b.rank?esc(b.rank):'Offline'))+'</div>'+
                '</div>'+
                '<span class="lbv5-dd-time lbv5-dt" data-tz="'+esc(b.tz||'')+'">&mdash;</span>'+
                '<span class="lbv5-dd-check">&#10003;</span>';
            row.addEventListener('click', function(){
                var was=row.classList.contains('sel');
                deselectAll();
                if(!was){
                    row.classList.add('sel');
                    syncHidden(b.id);
                    highlightCard(b.id);
                    slideToCard(b.id);
                    sw.classList.remove('open');
                } else { syncHidden(''); }
            });
            dd.appendChild(row);
        });
        var emptyRow = document.createElement('div');
        emptyRow.className='lbv5-dd-empty'; dd.appendChild(emptyRow);

        /* ---- HELPERS ---- */
        function deselectAll(){
            // Restore all cards to original content
            Array.from(track.children).forEach(function(el){
                var origId = el.dataset.origId || el.dataset.id;
                if(origId && _cardOriginals[origId]){
                    el.innerHTML = _cardOriginals[origId];
                    el.dataset.id = origId;
                    el.dataset.origId = origId;
                }
                el.classList.remove('sel','lbv5-temp');
                el.style.display = '';
                // re-attach click
                (function(origB){
                    if(!origB) return;
                    el.onclick = function(){
                        var was=el.classList.contains('sel');
                        deselectAll();
                        if(!was){ selectCard(el,origB.id); slideToCard(origB.id); } else { syncHidden(''); }
                    };
                })(allData.find(function(x){ return String(x.id)===String(origId); }));
            });
            Array.from(dd.children).forEach(function(r){ r.classList.remove('sel'); });
        }
        function selectCard(card,id){
            card.classList.add('sel');
            var b=card.querySelector('.lbv5-btn'); if(b){b.classList.add('sel');b.innerHTML='&#10003; Requested';}
            syncHidden(id);
            Array.from(dd.children).forEach(function(r){ if(String(r.dataset.id)===String(id)) r.classList.add('sel'); });
        }
        function highlightCard(id){
            Array.from(track.children).forEach(function(c){
                if(String(c.dataset.id)===String(id)){
                    c.classList.add('sel');
                    var b=c.querySelector('.lbv5-btn'); if(b){b.classList.add('sel');b.innerHTML='&#10003; Requested';}
                }
            });
        }

        /* ---- SEARCH ---- */
        searchEl.addEventListener('focus', function(){ sw.classList.add('open'); });
        searchEl.addEventListener('input', function(){
            sw.classList.add('open');
            var q=searchEl.value.trim().toLowerCase();
            var vis=0;
            Array.from(dd.children).forEach(function(r){
                if(r.classList.contains('lbv5-dd-empty')) return;
                var m=!q||(r.dataset.name||'').indexOf(q)!==-1;
                r.classList.toggle('hidden',!m);
                if(m) vis++;
            });
            emptyRow.style.display = vis===0 ? 'block' : 'none';
            emptyRow.textContent = 'No results found';
        });
        document.addEventListener('click', function(e){
            if(!sw.contains(e.target)) sw.classList.remove('open');
        });

        /* ---- LIVE CLOCK ---- */
        function fmtTz(tz){
            try {
                var now = new Date();
                // Get current time in that timezone
                var timeStr = now.toLocaleTimeString('en-GB',{timeZone:tz,hour:'2-digit',minute:'2-digit',hour12:false});
                // Get UTC offset label
                var offsetMin = -new Date(now.toLocaleString('en-US',{timeZone:tz})).getTimezoneOffset();
                // Use Intl to get the actual offset for the given tz
                var fmt = new Intl.DateTimeFormat('en',{timeZone:tz,timeZoneName:'short'});
                var parts = fmt.formatToParts(now);
                var tzName = '';
                parts.forEach(function(p){ if(p.type==='timeZoneName') tzName=p.value; });
                return timeStr + ' ' + tzName;
            } catch(e){ return ''; }
        }
        function tick(){
            var now = new Date();
            document.querySelectorAll('#lbbp-v5 [data-tz]').forEach(function(el){
                var tz = (el.dataset.tz||'').trim();
                if(!tz){ el.innerHTML='&mdash;'; return; }
                var result = fmtTz(tz);
                if(result) el.textContent = result;
                else el.innerHTML = '&mdash;';
            });
        }
        // Run immediately and every 60s
        tick();
        setInterval(tick, 60000);
    })();
    </script>
    <?php endif; ?>

</div>


<?php endif; // pro-games vs normal booster select ?>
<div class="form-group">
    <label><?= $__isCoachForm ? t('Note for the Coach') : t('Note for the Booster') ?></label>
        <textarea name="order_note" id="order-note" class="form-control" rows="3"
            placeholder="<?= $__isCoachForm ? 'What should the coach know before he claims your order?' : 'What should the booster know before he claims your order?' ?>"><?= db_get_row('order_notes', ['order_id' => $data['order_id']])['order_note'] ?? '' ?></textarea>
    </div>
    </div>
</details>
<?php endif; ?>
