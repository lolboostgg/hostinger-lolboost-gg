<?php

if (!function_exists('lb_db_seller_total_sales')) {
    function lb_db_seller_total_sales(int $sellerId, int $fallback = 0): int
    {
        static $cache = [];

        if ($sellerId <= 0) {
            return max(0, $fallback);
        }
        if (array_key_exists($sellerId, $cache)) {
            return $cache[$sellerId];
        }

        global $db;
        if (!empty($db)) {
            try {
                $value = $db->cell(
                    "SELECT total_sales FROM seller_stats WHERE seller_id = ? LIMIT 1",
                    $sellerId
                );
                if ($value !== false && $value !== null) {
                    return $cache[$sellerId] = max(0, (int)$value);
                }
            } catch (Throwable $e) {
            }
        }

        if (function_exists('get_seller_total_sales')) {
            try {
                return $cache[$sellerId] = max(0, (int)get_seller_total_sales($sellerId));
            } catch (Throwable $e) {
            }
        }

        return $cache[$sellerId] = max(0, $fallback);
    }
}


if (!function_exists('account_card_platform_icon_files')) {
    function account_card_platform_icon_files($platform): array
    {
        if (is_array($platform)) {
            $rawValues = $platform;
        } else {
            $raw = trim((string)$platform);
            $rawValues = preg_split('/\s*(?:,|\||\/)\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        }

        $files = [];
        $addFile = static function (string $file) use (&$files): void {
            if ($file !== '' && !in_array($file, $files, true)) {
                $files[] = $file;
            }
        };

        foreach ($rawValues as $rawValue) {
            $label = strtolower(trim((string)$rawValue));
            if ($label === '') {
                continue;
            }

            $value = str_replace(['.', '-', '_', ' '], '', $label);

            if (strpos($label, 'all platforms') !== false || $value === 'allplatforms') {
                foreach (['pc.webp', 'playstation.webp', 'xbox.webp', 'battlenet.webp', 'steam.webp', 'switch.webp', 'android.webp', 'ios.webp'] as $file) {
                    $addFile($file);
                }
                continue;
            }

            if (strpos($value, 'playstation') !== false || preg_match('/\bps[345]?\b/', $label)) {
                $addFile('playstation.webp');
                continue;
            }
            if (strpos($value, 'xbox') !== false) {
                $addFile('xbox.webp');
                continue;
            }
            if (strpos($value, 'battle') !== false || strpos($value, 'bnet') !== false || strpos($value, 'battlenet') !== false) {
                $addFile('battlenet.webp');
                continue;
            }
            if (strpos($value, 'steam') !== false) {
                $addFile('steam.webp');
                continue;
            }
            if (strpos($value, 'switch') !== false || strpos($value, 'nintendo') !== false) {
                $addFile('switch.webp');
                continue;
            }
            if (strpos($value, 'android') !== false) {
                $addFile('android.webp');
                continue;
            }
            if ($value === 'ios' || strpos($value, 'iphone') !== false || strpos($value, 'ipad') !== false || strpos($value, 'apple') !== false) {
                $addFile('ios.webp');
                continue;
            }
            if (strpos($value, 'pc') !== false || strpos($value, 'gamepass') !== false || strpos($value, 'windows') !== false) {
                $addFile('pc.webp');
                continue;
            }
        }

        return $files;
    }
}

if (!function_exists('account_card_platform_icons_html')) {
    function account_card_platform_icons_html($platform): string
    {
        $files = account_card_platform_icon_files($platform);
        if (empty($files)) return '';
        $base = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/platforms/';
        $html = '<span class="account-card-platform-icons" aria-label="Platforms">';
        foreach ($files as $file) {
            $alt = ucfirst(str_replace(['.webp', '-'], ['', ' '], $file));
            $html .= '<img src="' . htmlspecialchars($base . $file, ENT_QUOTES) . '" alt="" title="' . htmlspecialchars($alt, ENT_QUOTES) . '" loading="lazy" class="account-card-platform-icon">';
        }
        $html .= '</span>';
        return $html;
    }
}


if (!function_exists('account_card_game_icon_url')) {
    function account_card_game_icon_url(string $gameSlug, string $gameShort = '', array $account = []): string
    {
        foreach (['game_icon', 'icon', 'gameIcon'] as $key) {
            if (!empty($account[$key])) {
                return (string)$account[$key];
            }
        }

        $gameSlug = strtolower(trim($gameSlug));
        $gameShort = strtolower(trim($gameShort));

        static $gameIconMap = null;
        if ($gameIconMap === null) {
            $gameIconMap = [];
            if (function_exists('util_get_all_games')) {
                try {
                    foreach (util_get_all_games(true) as $gameRow) {
                        $slug = strtolower(trim((string)($gameRow['slug'] ?? '')));
                        if ($slug === '') {
                            continue;
                        }
                        $short = function_exists('util_account_short_game_code')
                            ? strtolower((string)util_account_short_game_code($slug))
                            : strtolower(trim((string)($gameRow['short_code'] ?? '')));

                        $icon = trim((string)($gameRow['icon'] ?? ''));
                        if ($icon === '') {
                            $icon = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $slug . '.png';
                        }

                        $gameIconMap[$slug] = $icon;
                        if ($short !== '') {
                            $gameIconMap[$short] = $icon;
                        }
                    }
                } catch (Throwable $e) {
                    $gameIconMap = [];
                }
            }
        }

        if ($gameSlug !== '' && !empty($gameIconMap[$gameSlug])) {
            return $gameIconMap[$gameSlug];
        }
        if ($gameShort !== '' && !empty($gameIconMap[$gameShort])) {
            return $gameIconMap[$gameShort];
        }

        if ($gameSlug === '' && $gameShort !== '') {
            $gameSlug = $gameShort;
        }

        return $gameSlug !== ''
            ? rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $gameSlug . '.png'
            : '';
    }
}



if (!function_exists('account_card_roblox_experience_map')) {
    function account_card_roblox_experience_map(): array
    {
        return [
            'bloxfruits' => ['label' => 'Blox Fruits', 'file' => 'BloxFruits.webp'],
            'typesoul' => ['label' => 'TYPE://SOUL', 'file' => 'TypeSoul.webp'],
            'adoptme' => ['label' => 'Adopt Me', 'file' => 'AdoptMe.webp'],
            'stealabrainrot' => ['label' => 'Steal A Brainrot', 'file' => 'StealABrainrot.webp'],
            'allstartowerdefense' => ['label' => 'All Star Tower Defense', 'file' => 'AllStarTowerDefense.webp'],
            'kinglegacy' => ['label' => 'King Legacy', 'file' => 'KingLegacy.webp'],
            'animechampionssimulator' => ['label' => 'Anime Champions Simulator', 'file' => 'AnimeChampionsSimulator.webp'],
            'barrysprisonrunv2' => ['label' => "Barry's Prison Run V2", 'file' => 'BarrysPrisonRunV2.webp'],
            'bladeball' => ['label' => 'Blade Ball', 'file' => 'BladeBall.webp'],
            'cottonobby' => ['label' => 'Cotton Obby', 'file' => 'CottonObby.webp'],
            'easyobby' => ['label' => 'Easy Obby', 'file' => 'EasyObby.webp'],
            'deathball' => ['label' => 'Death Ball', 'file' => 'DeathBall.webp'],
            'doors' => ['label' => 'DOORS', 'file' => 'Doors.webp'],
            'dungeonquest' => ['label' => 'Dungeon Quest', 'file' => 'DungeonQuest.webp'],
            'hideandseekextreme' => ['label' => 'Hide and Seek Extreme', 'file' => 'HideAndSeekExtreme.webp'],
            'jailbreak' => ['label' => 'Jailbreak', 'file' => 'Jailbreak.webp'],
            'murdermystery2' => ['label' => 'Murder Mystery 2', 'file' => 'MurderMystery2.webp'],
            'naturaldisastersurvival' => ['label' => 'Natural Disaster Survival', 'file' => 'NaturalDisasterSurvival.webp'],
            'petsimulator99' => ['label' => 'Pet Simulator 99', 'file' => 'PetSimulator99.webp'],
            'petsimulatorx' => ['label' => 'Pet Simulator X', 'file' => 'PetSimulatorX.webp'],
            'piggy' => ['label' => 'Piggy', 'file' => 'Piggy.webp'],
            'scubadivingatquilllake' => ['label' => 'Scuba Diving at Quill Lake', 'file' => 'ScubaDivingAtQuillLake.webp'],
            'speedrun4' => ['label' => 'Speed Run 4', 'file' => 'SpeedRun4.webp'],
            'superbombsurvival' => ['label' => 'Super Bomb Survival!!', 'file' => 'SuperBombSurvival.webp'],
            'themeparktycoon2' => ['label' => 'Theme Park Tycoon 2', 'file' => 'ThemeParkTycoon2.webp'],
            'thestrongestbattlegrounds' => ['label' => 'The Strongest Battlegrounds', 'file' => 'TheStrongestBattlegrounds.webp'],
            'workatapizzaplace' => ['label' => 'Work at a Pizza Place', 'file' => 'WorkAtAPizzaPlace.webp'],
            'wutheringwaves' => ['label' => 'Wuthering Waves', 'file' => 'WutheringWaves.webp'],
            'animedefenders' => ['label' => 'Anime Defenders', 'file' => 'AnimeDefenders.webp'],
            'grandpiece' => ['label' => 'Grand Piece', 'file' => 'GrandPiece.webp'],
            'growagarden' => ['label' => 'Grow a Garden', 'file' => 'GrowAGarden.webp'],
            'others' => ['label' => 'Others', 'file' => 'Others.webp'],
        ];
    }
}

if (!function_exists('account_card_roblox_key')) {
    function account_card_roblox_key($value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }
        $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strtolower(trim($value));
        return preg_replace('/[^a-z0-9]+/', '', $value);
    }
}

if (!function_exists('account_card_roblox_experience_meta')) {
    function account_card_roblox_experience_meta(array $account, array $gameData): array
    {
        foreach (['games', 'experience', 'experience_game', 'main_experience', 'roblox_experience', 'roblox_game', 'game_experience'] as $key) {
            $value = $gameData[$key] ?? $account[$key] ?? null;
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }
            $normalized = account_card_roblox_key($value);
            $map = account_card_roblox_experience_map();
            if (isset($map[$normalized])) {
                $base = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/public/assets'), '/') . '/website/images/roblox-icons/';
                return [
                    'name' => $map[$normalized]['label'],
                    'icon' => $base . $map[$normalized]['file'],
                ];
            }
            $label = is_array($value) ? implode(', ', array_filter(array_map('strval', $value))) : (string)$value;
            return ['name' => trim($label), 'icon' => ''];
        }
        return ['name' => '', 'icon' => ''];
    }
}

if (!function_exists('account_card_game_icon_html')) {
    function account_card_game_icon_html(string $iconUrl): string
    {
        $iconUrl = trim($iconUrl);
        if ($iconUrl === '') {
            return '';
        }
        return '<span class="account-card-game-icon-fallback" aria-label="Game"><img src="' . htmlspecialchars($iconUrl, ENT_QUOTES) . '" alt="" loading="lazy"></span>';
    }
}

/**
 * Component: website/components/accounts/account-cards
 * 
 * SELLER SALES: Uses unified seller_sales_unified.php system
 * - seller_total_sales should be in SQL query (see ajax.php)
 * - Fallback: get_seller_total_sales() if not in query
 */


if (!function_exists('lb_seller_profile_slug_from_value')) {
    function lb_seller_profile_slug_from_value($slug = '', $username = ''): string
    {
        $value = trim((string)$slug);
        if ($value === '') {
            $value = trim((string)$username);
        }
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^\pL\pN_-]+/u', '', (string)$value);
        $value = trim((string)$value, '-');
        return $value !== '' ? $value : trim((string)$username);
    }
}

if (!function_exists('lol_rank_hides_division')) {
    function lol_rank_hides_division($rank): bool
    {
        return in_array((int)$rank, [0, 8, 9, 10], true);
    }
}

if (!function_exists('lol_rank_display_text')) {
    function lol_rank_display_text($rank, $division, $lp): string
    {
        $label = util_get_lol_rank($rank);
        $lp = is_null($lp) ? null : (int)$lp;

        if ($lp !== null && $lp !== 0) {
            return $label . ' ' . $lp . 'LP';
        }

        if (lol_rank_hides_division($rank)) {
            return $label;
        }

        return $label . ' ' . util_format_lol_division($division);
    }
}

if (!function_exists('seller_card_rank_meta')) {
    function seller_card_rank_meta($rankName = '', $storedIcon = ''): array
    {
        $rankName = trim((string)$rankName);
        $storedIcon = trim((string)$storedIcon);
        $title = $rankName !== '' ? $rankName : 'Verified Seller';

        if ($storedIcon !== '') {
            $class = $storedIcon;
            if (strpos($class, 'fa-') !== false && strpos($class, 'fa-solid') === false && strpos($class, 'fa-regular') === false && strpos($class, 'fa-light') === false && strpos($class, 'fa-duotone') === false && strpos($class, 'fa-brands') === false) {
                $class = 'fa-solid ' . $class;
            }
            return [
                'class' => $class,
                'color' => '#22c55e',
                'title' => $title,
            ];
        }

        switch (strtolower($rankName)) {
            case 'mythic seller':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#fbbf24', 'title' => $title];
            case 'pro seller':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#8b5cf6', 'title' => $title];
            case 'expert seller':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#22c55e', 'title' => $title];
            case 'beginner':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#94a3b8', 'title' => $title];
            default:
                return ['class' => 'fa-solid fa-circle-check', 'color' => '#94a3b8', 'title' => $title];
        }
    }
}

if (!function_exists('account_card_rank_img')) {
    function account_card_rank_img(string $game, int $rank): string
    {
        $game = strtolower(trim($game));
        $rank = max(0, (int)$rank);

        if ($game === 'val') {
            return ASSET_URL . "/core/main/img/val/ranks/mini/{$rank}.png";
        }

        if (function_exists('util_get_rank_img')) {
            return util_get_rank_img($game, 'mini', $rank);
        }

        if (function_exists('util_rank_img')) {
            return util_rank_img($game === 'tft' ? 'lol' : $game, 'mini', $rank);
        }

        return ASSET_URL . "/core/main/img/lol/ranks/mini/{$rank}.png";
    }
}

?>

<style>

/* Wider and equal account cards inside the shop grid. */
.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns: repeat(auto-fill, minmax(340px, 400px)) !important;
    gap: 26px !important;
}
.ranked-accounts-page #accountsGrid .account-card{
    max-width: 400px !important;
    min-height: 650px !important;
}

.account-card {
    overflow: visible;
    display: flex;
    flex-direction: column;
}
.account-card > .cover-link {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
}
.account-card .highlights {
    min-height: 58px;
    align-content: flex-start;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 7px !important;
}
.account-card .highlights .badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex: 0 0 calc(50% - 4px) !important;
    min-width: 0 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    box-sizing: border-box !important;
}
.account-card .totals {
    overflow: hidden !important;
}
.account-card .totals .price-eur {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}
.account-card .totals .btn.primary {
    flex: 0 0 auto !important;
}
.account-card .highlights:empty {
    display: flex !important;
    visibility: hidden;
}
.account-card .totals {
    margin-top: auto;
}
.account-card > .seller-info {
    flex: 0 0 auto;
}
.account-card__recommended-icon {
    right: 56px !important;
    z-index: 8;
    color: #ffd54a !important;
}
.account-card__recommended-icon:hover {
    color: #ffe27a !important;
}
.account-card__recommended-icon .recommended-tooltip {
    position: absolute;
    right: 0;
    bottom: calc(100% + 10px);
    transform: translateY(6px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    white-space: nowrap;
    padding: 8px 12px;
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(26,31,59,0.98) 0%, rgba(11,14,30,0.98) 100%);
    border: 1px solid rgba(255,255,255,0.16);
    box-shadow: 0 18px 34px rgba(0,0,0,0.34);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
    z-index: 20;
}
.account-card__recommended-icon .recommended-tooltip::after {
    content: "";
    position: absolute;
    right: 12px;
    top: 100%;
    border-width: 6px;
    border-style: solid;
    border-color: rgba(15,18,38,0.98) transparent transparent transparent;
}
.account-card__recommended-icon:hover .recommended-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.seller-online-dot{width:9px;height:9px;min-width:9px;min-height:9px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95);animation:seller-online-pulse 1.45s ease-out infinite;display:inline-block;transform:translateY(1px);flex:0 0 9px;}
@keyframes seller-online-pulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95)}70%{box-shadow:0 0 0 7px rgba(34,197,94,0),0 0 18px rgba(34,197,94,.9)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0),0 0 14px rgba(34,197,94,.95)}}
.seller-info {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    min-height: 78px;
    margin-top: 14px;
    padding: 16px 18px;
    border-top: 1px solid rgba(255,255,255,0.09);
    border-radius: 0 0 18px 18px;
    background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.045) 100%);
    cursor: default;
    overflow: visible;
}
.seller-info__left {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
    flex: 1 1 auto;
    overflow: visible;
}
.seller-info__avatar {
    width: 46px;
    height: 46px;
    min-width: 46px;
    min-height: 46px;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
    display: block;
    flex: 0 0 46px;
    aspect-ratio: 1 / 1;
    border: 2px solid rgba(150, 109, 255, 0.35);
    box-shadow: 0 0 0 4px rgba(122, 92, 255, 0.08);
}
.seller-info__name {
    position: relative;
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    gap: 9px;
    cursor: help;
    overflow: visible !important;
    z-index: 30;
    isolation: isolate;
}
.seller-info__name-text {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 1vw;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.015em;
    text-transform: uppercase;
    color: #ffffff;
    text-shadow: 0 2px 14px rgba(0, 0, 0, 0.28);
}
@media (max-width: 767px) {
.seller-info__name-text {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 4vw;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.015em;
    text-transform: uppercase;
    color: #ffffff;
    text-shadow: 0 2px 14px rgba(0, 0, 0, 0.28);
}
}
.seller-info__verified {
    position: relative;
    z-index: 2;
    flex: 0 0 auto;
    font-size: 18px;
    line-height: 1;
    transform: translateY(1px);
    filter: drop-shadow(0 0 10px currentColor);
}
.seller-rank-trigger {
    position: relative;
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    gap: 9px;
    overflow: visible !important;
}
.seller-info__right {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex: 0 0 auto;
    white-space: nowrap;
}
.seller-info__sold,
.seller-info__rating {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: 0 10px;
    border-radius: 10px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.06);
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    line-height: 1;
}
.seller-info__rating i {
    font-size: 12px;
}
.seller-rank-tooltip {
    position: absolute;
    left: 0;
    bottom: calc(100% + 10px);
    transform: translateY(8px) scale(.96);
    transform-origin: left bottom;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
    padding: 9px 13px;
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(20,24,48,0.98) 0%, rgba(10,13,30,0.98) 100%);
    border: 1px solid rgba(255,255,255,0.14);
    box-shadow: 0 14px 30px rgba(0,0,0,0.32);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
    z-index: 999;
}
.seller-rank-tooltip::after {
    content: "";
    position: absolute;
    left: 18px;
    top: 100%;
    border-width: 6px;
    border-style: solid;
    border-color: rgba(10,13,30,0.98) transparent transparent transparent;
}
.seller-rank-trigger:hover .seller-rank-tooltip,
.seller-rank-trigger:focus-within .seller-rank-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}
.seller-rank-tooltip__dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    flex: 0 0 8px;
    box-shadow: 0 0 12px currentColor;
}

.account-card-platform-icons {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-right: 8px;
    flex: 0 0 auto;
    vertical-align: middle;
}
.account-card-platform-icon {
    width: 20px;
    height: 20px;
    min-width: 20px;
    object-fit: contain;
    filter: drop-shadow(0 0 7px rgba(59,130,246,.28));
}
.account-card-game-icon-fallback {
    width: 26px;
    height: 26px;
    min-width: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
    border-radius: 8px;
    background: rgba(139, 92, 246, .12);
    border: 1px solid rgba(139, 92, 246, .25);
    box-shadow: 0 6px 16px rgba(0,0,0,.22);
    overflow: hidden;
}
.account-card-game-icon-fallback img {
    width: 20px;
    height: 20px;
    object-fit: contain;
    display: block;
    filter: drop-shadow(0 0 7px rgba(139,92,246,.24));
}
.account-card .title {
    display: flex;
    align-items: center;
    gap: 0;
    min-width: 0;
}
.account-card-title-text {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Stable shop card layout: keep price/CTA directly above seller box,
   even when a game has no selected card badges. */
.account-card {
    height: 100%;
    min-height: 650px;
}
.account-card > .cover-link {
    height: 100%;
}
.account-card .title {
    min-height: 34px;
}
.account-card .excerpt {
    min-height: 36px;
}
.account-card .image-box {
    flex: 0 0 auto;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    border-radius: 0 !important;
    overflow: visible !important;
}

.account-card .image-box img{
    width:100%;
    border-radius:16px;
    display:block;
    object-fit:cover;
    box-shadow:none !important;
    border:none !important;
}

.account-card .image-box::before,
.account-card .image-box::after{
    display:none !important;
}
.account-card .totals {
    flex: 0 0 auto;
}
.account-card .totals .btn.primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

@media (min-width: 641px) {
    .account-card .highlights {
        height: 112px;
        min-height: 112px;
        max-height: 112px;
        overflow-y: auto;
        overflow-x: hidden;
        align-content: flex-start;
        padding-right: 8px;
        scrollbar-width: thin;
        scrollbar-color: rgba(139, 92, 246, .9) rgba(255,255,255,.06);
        -webkit-overflow-scrolling: touch;
    }

    .account-card .highlights:empty {
        height: 112px;
        min-height: 112px;
        visibility: hidden;
    }

    .account-card .highlights::-webkit-scrollbar {
        width: 7px;
    }

    .account-card .highlights::-webkit-scrollbar-track {
        background: rgba(255,255,255,.055);
        border-radius: 999px;
    }

    .account-card .highlights::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(124,92,255,.95), rgba(91,87,255,.75));
        border-radius: 999px;
        border: 2px solid rgba(22,20,34,.96);
    }

    .account-card .highlights::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, rgba(167,139,250,1), rgba(124,92,255,.95));
    }
}

@media (max-width: 640px) {
    .account-card {
        min-height: 0;
    }

    .account-card .highlights {
        min-height: 0;
        height: 74px;
        min-height: 74px;
        max-height: 74px;
        overflow-y: auto;
        overflow-x: hidden;
        align-content: flex-start;
        padding-right: 8px;
        scrollbar-width: thin;
        scrollbar-color: rgba(139, 92, 246, .9) rgba(255,255,255,.06);
        -webkit-overflow-scrolling: touch;
    }

    .account-card .highlights::-webkit-scrollbar {
        width: 7px;
    }

    .account-card .highlights::-webkit-scrollbar-track {
        background: rgba(255,255,255,.055);
        border-radius: 999px;
    }

    .account-card .highlights::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(124,92,255,.95), rgba(91,87,255,.75));
        border-radius: 999px;
        border: 2px solid rgba(22,20,34,.96);
    }
}

/* Cleaner seller footer */
.account-card .seller-info{
    min-height:64px !important;
    margin-top:14px !important;
    padding:14px 16px !important;
    gap:12px !important;
    border-top:1px solid rgba(255,255,255,.06) !important;
    border-radius:0 0 18px 18px !important;
    background:rgba(255,255,255,.032) !important;
    box-shadow:none !important;
    backdrop-filter:blur(8px);
    overflow:hidden !important;
}

.account-card .seller-info__left{
    flex:1 1 auto !important;
    min-width:0 !important;
    gap:10px !important;
    overflow:hidden !important;
}

.account-card .seller-info__avatar{
    width:40px !important;
    height:40px !important;
    min-width:40px !important;
    min-height:40px !important;
    flex:0 0 40px !important;
    border:none !important;
    box-shadow:none !important;
}

.account-card .seller-info__name,
.account-card .seller-rank-trigger{
    min-width:0 !important;
    max-width:100% !important;
    overflow:hidden !important;
    gap:6px !important;
}

.account-card .seller-info__name-text{
    max-width:100% !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
    font-size:14px !important;
    line-height:1.05 !important;
    font-weight:800 !important;
    letter-spacing:0 !important;
    text-transform:none !important;
}

.account-card .seller-info__verified{
    flex:0 0 auto !important;
    font-size:14px !important;
    filter:none !important;
    transform:none !important;
}

.account-card .seller-info__right{
    flex:0 0 auto !important;
    min-width:0 !important;
    gap:6px !important;
    white-space:nowrap !important;
}

.account-card .seller-info__sold,
.account-card .seller-info__rating{
    min-height:26px !important;
    height:26px !important;
    padding:0 8px !important;
    gap:5px !important;
    border-radius:8px !important;
    font-size:11px !important;
    font-weight:800 !important;
    line-height:1 !important;
    background:rgba(255,255,255,.045) !important;
    border:1px solid rgba(255,255,255,.08) !important;
}

.account-card .seller-info__rating{
    color:#22c55e !important;
    border-color:rgba(34,197,94,.28) !important;
    background:rgba(34,197,94,.08) !important;
}

@media (max-width:640px){
    .account-card .seller-info{
        min-height:60px !important;
        padding:12px 14px !important;
    }

    .account-card .seller-info__avatar{
        width:36px !important;
        height:36px !important;
        min-width:36px !important;
        min-height:36px !important;
        flex-basis:36px !important;
    }

    .account-card .seller-info__name-text{
        font-size:13px !important;
    }

    .account-card .seller-info__sold,
    .account-card .seller-info__rating{
        height:24px !important;
        min-height:24px !important;
        padding:0 7px !important;
        font-size:10px !important;
    }
}


/* Trusted icon only */
.account-card .seller-info__rating{
    width:30px !important;
    min-width:30px !important;
    padding:0 !important;
    justify-content:center !important;
}

.account-card .seller-info__rating i{
    font-size:13px !important;
}

@media (max-width:640px){
    .account-card .seller-info__rating{
        width:34px !important;
        min-width:34px !important;
        height:28px !important;
    }

    .account-card .seller-info__rating i{
        font-size:15px !important;
    }
}


/* icon only trusted badge */
.account-card .seller-info__rating{
    font-size:0 !important;
}

.account-card .seller-info__rating i{
    font-size:14px !important;
    margin:0 !important;
}

@media (max-width:640px){
    .account-card .seller-info__rating i{
        font-size:16px !important;
    }
}


/* Seller rank tooltip visible again */
.account-card .seller-info,
.account-card .seller-info__name,
.account-card .seller-rank-trigger{
    overflow:visible !important;
}

.account-card .seller-rank-tooltip{
    z-index:99999 !important;
}

.account-card .seller-info__left{
    overflow:visible !important;
}


/* Fixed equal card layout, stable price, CTA and seller position */
.account-card{
    height:100% !important;
    min-height:650px !important;
    display:flex !important;
    flex-direction:column !important;
}

.account-card > .cover-link{
    flex:1 1 auto !important;
    display:grid !important;
    grid-template-rows:44px 58px 210px 112px 76px !important;
    gap:14px !important;
    min-height:0 !important;
}

.account-card .title{
    min-height:44px !important;
    max-height:44px !important;
    margin:0 !important;
    overflow:hidden !important;
    align-items:center !important;
}

.account-card .account-card-title-text{
    display:block !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
}

.account-card .excerpt{
    min-height:58px !important;
    max-height:58px !important;
    margin:0 !important;
    overflow:hidden !important;
    display:-webkit-box !important;
    -webkit-line-clamp:2 !important;
    -webkit-box-orient:vertical !important;
}

.account-card .image-box{
    width:100% !important;
    height:210px !important;
    min-height:210px !important;
    max-height:210px !important;
    margin:0 !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    overflow:hidden !important;
}

.account-card .image-box > img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    border-radius:16px !important;
}

.account-card .highlights{
    height:112px !important;
    min-height:112px !important;
    max-height:112px !important;
    margin:0 !important;
    overflow-y:auto !important;
    overflow-x:hidden !important;
    align-content:flex-start !important;
}

.account-card .highlights:empty{
    display:flex !important;
    visibility:hidden !important;
}

.account-card .totals{
    height:76px !important;
    min-height:76px !important;
    max-height:76px !important;
    margin:0 !important;
    padding-top:18px !important;
    border-top:1px solid rgba(255,255,255,.08) !important;
    display:flex !important;
    align-items:center !important;
    justify-content:space-between !important;
    gap:14px !important;
}

.account-card .totals .price-eur{
    min-width:0 !important;
    white-space:nowrap !important;
}

.account-card .totals .btn.primary{
    flex:0 0 auto !important;
    white-space:nowrap !important;
}

.account-card > .seller-info{
    flex:0 0 64px !important;
    height:64px !important;
    min-height:64px !important;
    max-height:64px !important;
    margin-top:14px !important;
}

@media (max-width:640px){
    .account-card{
        min-height:0 !important;
    }

    .account-card > .cover-link{
        display:grid !important;
        grid-template-rows:auto auto 190px 74px auto !important;
        gap:12px !important;
    }

    .account-card .title{
        min-height:38px !important;
        max-height:38px !important;
    }

    .account-card .excerpt{
        min-height:44px !important;
        max-height:44px !important;
    }

    .account-card .image-box{
        height:190px !important;
        min-height:190px !important;
        max-height:190px !important;
    }

    .account-card .highlights{
        height:74px !important;
        min-height:74px !important;
        max-height:74px !important;
    }

    .account-card .totals{
        height:auto !important;
        min-height:64px !important;
        max-height:none !important;
    }

    .account-card > .seller-info{
        height:60px !important;
        min-height:60px !important;
        max-height:60px !important;
        flex-basis:60px !important;
    }
}


/* Wider, less tall account cards */
.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(auto-fill, minmax(430px, 1fr)) !important;
    gap:24px !important;
}

.ranked-accounts-page #accountsGrid .account-card{
    max-width:none !important;
    min-height:590px !important;
}

.account-card{
    min-height:590px !important;
}

.account-card > .cover-link{
    grid-template-rows:40px 48px 185px 92px 68px !important;
    gap:12px !important;
}

.account-card .title{
    min-height:40px !important;
    max-height:40px !important;
}

.account-card .excerpt{
    min-height:48px !important;
    max-height:48px !important;
    -webkit-line-clamp:2 !important;
}

.account-card .image-box{
    height:185px !important;
    min-height:185px !important;
    max-height:185px !important;
}

.account-card .highlights{
    height:92px !important;
    min-height:92px !important;
    max-height:92px !important;
}

.account-card .totals{
    height:68px !important;
    min-height:68px !important;
    max-height:68px !important;
    padding-top:14px !important;
}

.account-card > .seller-info{
    height:60px !important;
    min-height:60px !important;
    max-height:60px !important;
    flex-basis:60px !important;
}

@media (min-width:1500px){
    .ranked-accounts-page #accountsGrid.accounts-grid{
        grid-template-columns:repeat(auto-fill, minmax(450px, 1fr)) !important;
    }
}

@media (max-width:1100px){
    .ranked-accounts-page #accountsGrid.accounts-grid{
        grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)) !important;
    }
}

@media (max-width:640px){
    .ranked-accounts-page #accountsGrid.accounts-grid{
        grid-template-columns:1fr !important;
    }

    .account-card{
        min-height:0 !important;
    }

    .account-card > .cover-link{
        grid-template-rows:auto auto 190px 74px auto !important;
    }
}


/* Keep title text away from icon and delivery badge */
.account-card .title{
    gap:12px !important;
    padding-right:44px !important;
}

.account-card .title > img.rank-icon,
.account-card .title > .rank-icon,
.account-card .title > .account-card-game-icon-fallback,
.account-card .title > .account-card-platform-icons{
    flex:0 0 auto !important;
    margin-right:0 !important;
}

.account-card .account-card-title-text{
    flex:1 1 auto !important;
    min-width:0 !important;
    max-width:100% !important;
    display:block !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
}

.account-card .delivery-type{
    z-index:10 !important;
}


/* Cleaner highlights scrollbar */
.account-card .highlights{
    padding-right:10px !important;
    scrollbar-width:thin !important;
    scrollbar-color:rgba(255,255,255,.22) transparent !important;
}

.account-card .highlights::-webkit-scrollbar{
    width:4px !important;
}

.account-card .highlights::-webkit-scrollbar-track{
    background:transparent !important;
    border-radius:999px !important;
    margin:4px 0 !important;
}

.account-card .highlights::-webkit-scrollbar-thumb{
    background:linear-gradient(180deg, rgba(255,255,255,.34), rgba(139,92,246,.62)) !important;
    border-radius:999px !important;
    border:0 !important;
    box-shadow:0 0 8px rgba(139,92,246,.22) !important;
}

.account-card .highlights::-webkit-scrollbar-thumb:hover{
    background:linear-gradient(180deg, rgba(255,255,255,.48), rgba(139,92,246,.82)) !important;
}

.account-card .highlights::-webkit-scrollbar-corner{
    background:transparent !important;
}


/* Center rank/game icons inside account card titles */
.account-card .title{
    display:flex !important;
    align-items:center !important;
    gap:12px !important;
}

.account-card .title > img.rank-icon,
.account-card .title > img,
.account-card .title .rank-icon,
.account-card .title .account-card-game-icon-fallback,
.account-card .title .account-card-platform-icons{
    width:34px !important;
    height:34px !important;
    min-width:34px !important;
    flex:0 0 34px !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    object-fit:contain !important;
    object-position:center center !important;
    margin:0 !important;
    padding:0 !important;
    vertical-align:middle !important;
    line-height:1 !important;
}

.account-card .title .rank-icon img,
.account-card .title .account-card-game-icon-fallback img,
.account-card .title .account-card-platform-icon{
    width:28px !important;
    height:28px !important;
    min-width:28px !important;
    max-width:28px !important;
    object-fit:contain !important;
    object-position:center center !important;
    display:block !important;
    margin:auto !important;
    padding:0 !important;
}

.account-card .title > img.rank-icon,
.account-card .title > img:not(.account-card-platform-icon){
    width:34px !important;
    height:34px !important;
    min-width:34px !important;
    max-width:34px !important;
    object-fit:contain !important;
    object-position:center center !important;
}

.account-card .account-card-title-text{
    align-self:center !important;
    line-height:1.2 !important;
}


/* Center top-right delivery/recommended icons in their round buttons */
.account-card .delivery-type{
    position:absolute !important;
    top:22px !important;
    right:22px !important;
    width:34px !important;
    height:34px !important;
    min-width:34px !important;
    min-height:34px !important;
    border-radius:999px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    line-height:1 !important;
    font-size:18px !important;
    padding:0 !important;
    margin:0 !important;
    background:rgba(7,8,20,.78) !important;
    border:1px solid rgba(255,255,255,.06) !important;
    box-shadow:0 8px 20px rgba(0,0,0,.28) !important;
    text-align:center !important;
    z-index:12 !important;
}

.account-card .delivery-type::before{
    display:block !important;
    width:auto !important;
    height:auto !important;
    line-height:1 !important;
    margin:0 !important;
    transform:none !important;
}

.account-card__recommended-icon{
    right:56px !important;
    color:#ffd54a !important;
}

.account-card .delivery-type:not(.account-card__recommended-icon){
    right:22px !important;
}

.account-card .title{
    padding-right:86px !important;
}

@media (max-width:640px){
    .account-card .delivery-type{
        top:18px !important;
        right:18px !important;
        width:32px !important;
        height:32px !important;
        min-width:32px !important;
        min-height:32px !important;
        font-size:17px !important;
    }

    .account-card__recommended-icon{
        right:52px !important;
    }

    .account-card .delivery-type:not(.account-card__recommended-icon){
        right:18px !important;
    }

    .account-card .title{
        padding-right:82px !important;
    }
}


/* Restore recommended badge next to delivery badge */
.account-card .delivery-type.account-card__recommended-icon{
    display:flex !important;
    opacity:1 !important;
    visibility:visible !important;
    pointer-events:auto !important;
    right:62px !important;
    color:#ffd54a !important;
    background:rgba(7,8,20,.78) !important;
}

.account-card .delivery-type.account-card__recommended-icon::before{
    color:#ffd54a !important;
}

.account-card .delivery-type:not(.account-card__recommended-icon){
    right:22px !important;
    color:#ffffff !important;
}

.account-card.has-recommended .title,
.account-card .title{
    padding-right:104px !important;
}

@media (max-width:640px){
    .account-card .delivery-type.account-card__recommended-icon{
        right:56px !important;
    }

    .account-card .delivery-type:not(.account-card__recommended-icon){
        right:18px !important;
    }

    .account-card .title{
        padding-right:98px !important;
    }
}


/* Same card width on LoL, Valorant and generic shops */
.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(auto-fill, minmax(430px, 1fr)) !important;
    gap:24px !important;
    align-items:stretch !important;
}

.ranked-accounts-page #accountsGrid .account-card{
    width:100% !important;
    max-width:none !important;
    min-height:590px !important;
}

/* Delivery tooltip, works for bolt, truck and recommended star */
.account-card .delivery-type[data-tooltip],
.account-card .delivery-type.account-card__recommended-icon{
    overflow:visible !important;
}

.account-card .delivery-type[data-tooltip]::after,
.account-card .account-card__recommended-icon .recommended-tooltip{
    position:absolute !important;
    right:0 !important;
    bottom:calc(100% + 10px) !important;
    padding:8px 12px !important;
    border-radius:12px !important;
    background:linear-gradient(180deg, rgba(20,24,48,.98), rgba(10,13,30,.98)) !important;
    border:1px solid rgba(255,255,255,.14) !important;
    color:#fff !important;
    font-size:12px !important;
    font-weight:800 !important;
    line-height:1 !important;
    white-space:nowrap !important;
    box-shadow:0 14px 30px rgba(0,0,0,.32) !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
    transform:translateY(6px) !important;
    transition:opacity .18s ease, transform .18s ease, visibility .18s ease !important;
    z-index:99999 !important;
}

.account-card .delivery-type[data-tooltip]::after{
    content:attr(data-tooltip) !important;
}

.account-card .delivery-type[data-tooltip]::before{
    z-index:2 !important;
}

.account-card .delivery-type[data-tooltip]:hover::after,
.account-card .account-card__recommended-icon:hover .recommended-tooltip{
    opacity:1 !important;
    visibility:visible !important;
    transform:translateY(0) !important;
}

/* Small arrow under tooltip */
.account-card .delivery-type[data-tooltip]::before{
    line-height:1 !important;
}

.account-card .delivery-type[data-tooltip]:hover{
    z-index:99998 !important;
}

@media (max-width:1100px){
    .ranked-accounts-page #accountsGrid.accounts-grid{
        grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)) !important;
    }
}

@media (max-width:640px){
    .ranked-accounts-page #accountsGrid.accounts-grid{
        grid-template-columns:1fr !important;
    }
}


/* Single tooltip system for delivery and recommended icons */
.account-card .delivery-type[data-tooltip]::after{
    content:none !important;
    display:none !important;
}

.account-card .delivery-type{
    overflow:visible !important;
}

.account-card .delivery-tooltip,
.account-card__recommended-icon .recommended-tooltip{
    position:absolute !important;
    right:0 !important;
    bottom:calc(100% + 10px) !important;
    transform:translateY(6px) !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
    white-space:nowrap !important;
    padding:8px 12px !important;
    border-radius:12px !important;
    background:linear-gradient(180deg, rgba(26,31,59,.98) 0%, rgba(11,14,30,.98) 100%) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 18px 34px rgba(0,0,0,.34) !important;
    color:#fff !important;
    font-size:12px !important;
    font-weight:800 !important;
    line-height:1 !important;
    transition:opacity .18s ease, transform .18s ease, visibility .18s ease !important;
    z-index:99999 !important;
}

.account-card .delivery-tooltip::after,
.account-card__recommended-icon .recommended-tooltip::after{
    content:"" !important;
    position:absolute !important;
    right:12px !important;
    top:100% !important;
    border-width:6px !important;
    border-style:solid !important;
    border-color:rgba(15,18,38,.98) transparent transparent transparent !important;
}

.account-card .delivery-type:hover .delivery-tooltip,
.account-card__recommended-icon:hover .recommended-tooltip{
    opacity:1 !important;
    visibility:visible !important;
    transform:translateY(0) !important;
}

/* Make sure LoL and Valorant cards do not clip top icon tooltips */
.ranked-accounts-page #accountsGrid,
.ranked-accounts-page #accountsGrid .account-card,
.ranked-accounts-page #accountsGrid .account-card > .cover-link,
.account-card .title{
    overflow:visible !important;
}

.account-card .image-box,
.account-card .excerpt,
.account-card .highlights{
    overflow:hidden;
}

.account-card .highlights{
    overflow-y:auto !important;
}


/* Final tooltip fix, no pseudo tooltip, same style as recommended */
.account-card .delivery-type[data-tooltip]::after{
    content:none !important;
    display:none !important;
}

.account-card .delivery-type,
.account-card .account-card__recommended-icon{
    overflow:visible !important;
}

.account-card .delivery-tooltip,
.account-card .account-card__recommended-icon .recommended-tooltip{
    position:absolute !important;
    right:0 !important;
    bottom:calc(100% + 10px) !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    transform:translateY(6px) !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
    white-space:nowrap !important;
    padding:8px 12px !important;
    border-radius:12px !important;
    background:linear-gradient(180deg, rgba(26,31,59,.98) 0%, rgba(11,14,30,.98) 100%) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 18px 34px rgba(0,0,0,.34) !important;
    color:#fff !important;
    font-size:12px !important;
    font-weight:800 !important;
    line-height:1 !important;
    transition:opacity .18s ease, transform .18s ease, visibility .18s ease !important;
    z-index:999999 !important;
}

.account-card .delivery-tooltip::after,
.account-card .account-card__recommended-icon .recommended-tooltip::after{
    content:"" !important;
    position:absolute !important;
    right:12px !important;
    top:100% !important;
    border-width:6px !important;
    border-style:solid !important;
    border-color:rgba(15,18,38,.98) transparent transparent transparent !important;
}

.account-card .delivery-type:hover .delivery-tooltip,
.account-card .account-card__recommended-icon:hover .recommended-tooltip{
    opacity:1 !important;
    visibility:visible !important;
    transform:translateY(0) !important;
}

.account-card .delivery-type:hover,
.account-card .account-card__recommended-icon:hover{
    z-index:999998 !important;
}

/* Same grid/card width everywhere */
.ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(auto-fill, minmax(430px, 1fr)) !important;
    gap:24px !important;
    align-items:stretch !important;
}

.ranked-accounts-page #accountsGrid .account-card{
    width:100% !important;
    max-width:none !important;
    min-height:590px !important;
}

@media (max-width:1100px){
    .ranked-accounts-page #accountsGrid.accounts-grid{
        grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)) !important;
    }
}

@media (max-width:640px){
    .ranked-accounts-page #accountsGrid.accounts-grid{
        grid-template-columns:1fr !important;
    }
}


/* Seller rank tooltip visible on dynamic cards too */
.account-card,
.account-card > .cover-link,
.account-card .seller-info,
.account-card .seller-info__left,
.account-card .seller-info__name,
.account-card .seller-rank-trigger{
    overflow:visible !important;
}

.account-card .seller-rank-trigger{
    position:relative !important;
    cursor:pointer !important;
    z-index:9999 !important;
}

.account-card .seller-rank-tooltip{
    position:absolute !important;
    left:0 !important;
    bottom:calc(100% + 10px) !important;
    display:inline-flex !important;
    align-items:center !important;
    gap:8px !important;
    padding:8px 12px !important;
    border-radius:12px !important;
    background:linear-gradient(180deg, rgba(26,31,59,.98) 0%, rgba(11,14,30,.98) 100%) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 18px 34px rgba(0,0,0,.34) !important;
    color:#fff !important;
    font-size:12px !important;
    font-weight:800 !important;
    line-height:1 !important;
    white-space:nowrap !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
    transform:translateY(6px) !important;
    transition:opacity .18s ease, transform .18s ease, visibility .18s ease !important;
    z-index:999999 !important;
}

.account-card .seller-rank-tooltip::after{
    content:"" !important;
    position:absolute !important;
    left:16px !important;
    top:100% !important;
    border-width:6px !important;
    border-style:solid !important;
    border-color:rgba(15,18,38,.98) transparent transparent transparent !important;
}

.account-card .seller-rank-trigger:hover .seller-rank-tooltip,
.account-card .seller-rank-trigger:focus-within .seller-rank-tooltip{
    opacity:1 !important;
    visibility:visible !important;
    transform:translateY(0) !important;
}


/* Final seller rank tooltip fix */
.account-card .seller-info{
    overflow:visible !important;
    position:relative !important;
    z-index:50 !important;
}

.account-card .seller-info__left,
.account-card .seller-info__name,
.account-card .seller-rank-trigger{
    overflow:visible !important;
    position:relative !important;
}

.account-card .seller-rank-trigger{
    z-index:99999 !important;
}

.account-card .seller-rank-tooltip{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px !important;
    position:absolute !important;
    left:0 !important;
    bottom:calc(100% + 10px) !important;
    min-width:max-content !important;
    width:max-content !important;
    max-width:none !important;
    padding:8px 12px !important;
    border-radius:12px !important;
    background:linear-gradient(180deg, rgba(26,31,59,.98) 0%, rgba(11,14,30,.98) 100%) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 18px 34px rgba(0,0,0,.34) !important;
    color:#fff !important;
    font-size:12px !important;
    font-weight:800 !important;
    line-height:1 !important;
    white-space:nowrap !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
    transform:translateY(6px) !important;
    transition:opacity .18s ease, transform .18s ease, visibility .18s ease !important;
    z-index:999999 !important;
}

.account-card .seller-rank-tooltip::after{
    content:"" !important;
    position:absolute !important;
    left:16px !important;
    top:100% !important;
    border-width:6px !important;
    border-style:solid !important;
    border-color:rgba(15,18,38,.98) transparent transparent transparent !important;
}

.account-card .seller-rank-trigger:hover .seller-rank-tooltip,
.account-card .seller-rank-trigger:focus-within .seller-rank-tooltip,
.account-card .seller-info__name:hover .seller-rank-tooltip{
    opacity:1 !important;
    visibility:visible !important;
    transform:translateY(0) !important;
}

.ranked-accounts-page #accountsGrid,
.ranked-accounts-page #accountsGrid .account-card{
    overflow:visible !important;
}


/* Seller rank tooltip hover fix for dynamic shop */
.account-card .seller-info,
.account-card .seller-info__left,
.account-card .seller-info__name,
.account-card .seller-rank-trigger{
    overflow:visible !important;
}

.account-card .seller-info__name{
    position:relative !important;
    z-index:99999 !important;
    cursor:pointer !important;
}

.account-card .seller-info__name .seller-rank-tooltip{
    position:absolute !important;
    left:0 !important;
    bottom:calc(100% + 12px) !important;
    display:inline-flex !important;
    align-items:center !important;
    gap:8px !important;
    min-width:max-content !important;
    width:max-content !important;
    max-width:none !important;
    padding:8px 12px !important;
    border-radius:12px !important;
    background:linear-gradient(180deg, rgba(26,31,59,.98) 0%, rgba(11,14,30,.98) 100%) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 18px 34px rgba(0,0,0,.34) !important;
    color:#fff !important;
    font-size:12px !important;
    font-weight:800 !important;
    line-height:1 !important;
    white-space:nowrap !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
    transform:translateY(6px) !important;
    transition:opacity .18s ease, transform .18s ease, visibility .18s ease !important;
    z-index:999999 !important;
}

.account-card .seller-info__name .seller-rank-tooltip::after{
    content:"" !important;
    position:absolute !important;
    left:16px !important;
    top:100% !important;
    border-width:6px !important;
    border-style:solid !important;
    border-color:rgba(15,18,38,.98) transparent transparent transparent !important;
}

.account-card .seller-info__name:hover .seller-rank-tooltip,
.account-card .seller-info__name:focus .seller-rank-tooltip,
.account-card .seller-rank-trigger:hover .seller-rank-tooltip{
    opacity:1 !important;
    visibility:visible !important;
    transform:translateY(0) !important;
}

.ranked-accounts-page .container,
.ranked-accounts-page #accountsGrid,
.ranked-accounts-page #accountsGrid .account-card{
    overflow:visible !important;
}

</style>





<?php foreach ($accounts as $account): ?>
    <?php
        // price is already converted to session currency (cents) by controller/ajax
        $price_cents = (int)($account['price'] ?? 0);
        $price_float = round($price_cents / 100, 2);
        $_gameRaw    = strtolower(trim((string)($account['game'] ?? 'lol')));
        // Short code for icon/rank lookups
        $_s2s = ['league-of-legends'=>'lol','leagu'=>'lol','valorant'=>'val','valor'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft','call-of-duty'=>'cod','callofduty'=>'cod'];
        $game        = $_s2s[$_gameRaw] ?? $_gameRaw;
        // Full slug for URL (e.g. 'apex-legends', 'call-of-duty')
        $_slugForUrl = ['lol'=>'league-of-legends','val'=>'valorant','tft'=>'teamfight-tactics','cod'=>'call-of-duty'][$game] ?? $_gameRaw;
        $cardGameIconUrl = function_exists('account_card_game_icon_url') ? account_card_game_icon_url($_slugForUrl, $game, $account) : '';

        // Universal rank fields (prefer new columns, fall back to legacy)
        $universalRank  = (int)($account['rank'] ?? $account['current_rank'] ?? 0);
        $rankScore      = $universalRank * 100000;

        $gameData = json_decode((string)($account['game_data'] ?? '{}'), true);
        if (!is_array($gameData)) {
            $gameData = [];
        }
        $isRobloxCard = in_array($_gameRaw, ['roblox', 'roblox-accounts', 'roblox-account'], true) || $game === 'roblox';
        $robloxExperienceMeta = $isRobloxCard && function_exists('account_card_roblox_experience_meta')
            ? account_card_roblox_experience_meta($account, $gameData)
            : ['name' => '', 'icon' => ''];

        $div = 0;
        $lp = 0;

        // LoL-spezifisch: division/lp für Sortierung & Anzeige aus game_data oder legacy-Spalten
        if ($game === 'lol') {
            $div = (int)($account['current_division'] ?? $gameData['division'] ?? 0);
            $lp  = (int)($account['current_lp']       ?? $gameData['lp']       ?? 0);
            $rankScore = ($universalRank * 100000) + ($div * 1000) + $lp;
        }

        // Use manual list count when available, fall back to count-only field
        $lolChampionsList  = array_filter(explode('|', (string)($account['champions'] ?? '')));
        $lolSkinsList      = array_filter(explode('|', (string)($account['skins'] ?? '')));
        $lolChampionsCount = count($lolChampionsList) > 0
            ? count($lolChampionsList)
            : (isset($account['champion_count']) && $account['champion_count'] !== null && $account['champion_count'] !== ''
               ? (int)$account['champion_count'] : 0);
        $lolSkinsCount = count($lolSkinsList) > 0
            ? count($lolSkinsList)
            : (isset($account['skin_count']) && $account['skin_count'] !== null && $account['skin_count'] !== ''
               ? (int)$account['skin_count'] : 0);
        $valAgents         = $gameData['agents'] ?? [];
        if (is_string($valAgents) && $valAgents !== '') {
            $valAgents = array_filter(array_map('trim', explode('|', $valAgents)));
        }
        if (!is_array($valAgents)) {
            $valAgents = [];
        }
        $valAgentsManualCount = count(array_filter($valAgents, static fn($v) => $v !== null && $v !== ''));
        $valAgentsCount       = $valAgentsManualCount > 0
            ? $valAgentsManualCount
            : (isset($account['val_agent_count']) && $account['val_agent_count'] !== null && $account['val_agent_count'] !== ''
               ? (int)$account['val_agent_count'] : 0);
        $valWeaponSkinsCount = (int)($gameData['val_weapon_skins'] ?? 0);
        $valPoints           = (int)($gameData['val_points'] ?? 0);
        $valRadianite        = (int)($gameData['val_radianite'] ?? 0);
        $valWinrate          = (int)($gameData['val_winrate'] ?? 0);
        $valRankedReady      = !empty($gameData['val_ranked_ready']);

        $codTitles = $gameData['cod_titles'] ?? [];
        if (is_string($codTitles) && $codTitles !== '') {
            $codTitles = array_filter(array_map('trim', explode('|', $codTitles)));
        }
        if (!is_array($codTitles)) {
            $codTitles = [];
        }
        $codPrimaryTitle = $gameData['main_title'] ?? ($codTitles[0] ?? 'Call of Duty');
        $codPlatform     = $gameData['platform'] ?? '';
        $codLevel        = (int)($gameData['level'] ?? $account['level'] ?? 0);
        $codPrestige     = (int)($gameData['prestige'] ?? 0);
        $codOperators    = (int)($gameData['operators'] ?? 0);
        $codWeapons      = (int)($gameData['weapons'] ?? 0);
        $codCamos        = (int)($gameData['camos'] ?? 0);
        $codPoints       = (int)($gameData['cod_points'] ?? 0);
        $codRankedReady  = !empty($gameData['ranked_ready']);

        $cardSchema = function_exists('util_get_game_account_schema') ? util_get_game_account_schema($game) : [];

        // Every non-LoL/Valorant game should use the generic/dynamic card layout.
        // Otherwise games like Fortnite/Apex fall back to the old rank layout and show
        // "Unranked" instead of the real listing title.
        $useDynamicCardSchema = !in_array($game, ['lol','val'], true);

        $cardTitleField = trim((string)($cardSchema['title_field'] ?? ''));
        $dynamicCardTitle = '';

        if ($useDynamicCardSchema && $cardTitleField !== '') {
            $_titleValue = null;
            if (array_key_exists($cardTitleField, $gameData)) {
                $_titleValue = $gameData[$cardTitleField];
            } elseif (array_key_exists($cardTitleField, $account)) {
                $_titleValue = $account[$cardTitleField];
            }
            if ($_titleValue !== null && $_titleValue !== '' && !(is_array($_titleValue) && empty($_titleValue))) {
                $dynamicCardTitle = function_exists('util_account_format_schema_value')
                    ? util_account_format_schema_value($_titleValue)
                    : (is_array($_titleValue) ? implode(', ', array_filter(array_map('strval', $_titleValue))) : (string)$_titleValue);
            }
        }

        // Safe fallback order for generic games: normal listing title first, not rank_label.
        // This keeps cards like "Fortnite — the best account ever" from becoming "Unranked".
        if (trim((string)$dynamicCardTitle) === '') {
            foreach ([$account['title'] ?? '', $gameData['main_title'] ?? '', $gameData['main_game'] ?? '', $account['rank_label'] ?? ''] as $_fallbackTitle) {
                if (is_array($_fallbackTitle)) {
                    $_fallbackTitle = implode(', ', array_filter(array_map('strval', $_fallbackTitle)));
                }
                $_fallbackTitle = trim((string)$_fallbackTitle);
                if ($_fallbackTitle !== '') {
                    $dynamicCardTitle = $_fallbackTitle;
                    break;
                }
            }
        }
        if (trim((string)$dynamicCardTitle) === '') {
            $dynamicCardTitle = 'Account';
        }

        // Clash of Clans: show the compact GameBoost style card title, e.g.
        // "Town Hall 18 · Level 264". If these values are missing, keep the normal
        // listing title, but cut it before noisy details like "| HEROS ...".
        if ($useDynamicCardSchema && in_array($_gameRaw, ['clash-of-clans', 'clashofclans', 'coc'], true)) {
            $_cocTownHall = $gameData['town_hall_level'] ?? $gameData['townHallLevel'] ?? $gameData['townhall_level']
                ?? $gameData['town_hall'] ?? $gameData['townhall'] ?? $gameData['th_level'] ?? $gameData['th']
                ?? $account['town_hall_level'] ?? $account['town_hall'] ?? null;
            $_cocLevel    = $gameData['xp_level'] ?? $gameData['level'] ?? $gameData['xpLevel'] ?? $gameData['account_level'] ?? $gameData['exp_level']
                ?? $account['xp_level'] ?? $account['level'] ?? null;

            $_cocParts = [];
            if ($_cocTownHall !== null && $_cocTownHall !== '' && !(is_array($_cocTownHall) && empty($_cocTownHall))) {
                $_cocParts[] = 'Town Hall ' . (is_array($_cocTownHall) ? implode(', ', array_filter(array_map('strval', $_cocTownHall))) : (string)$_cocTownHall);
            }
            if ($_cocLevel !== null && $_cocLevel !== '' && !(is_array($_cocLevel) && empty($_cocLevel))) {
                $_cocParts[] = 'Level ' . (is_array($_cocLevel) ? implode(', ', array_filter(array_map('strval', $_cocLevel))) : (string)$_cocLevel);
            }

            if (!empty($_cocParts)) {
                $dynamicCardTitle = implode(' · ', $_cocParts);
            } else {
                $_fallbackCompactTitle = trim((string)$dynamicCardTitle);
                $_fallbackCompactTitle = preg_split('/\s*[|\[\(]\s*/u', $_fallbackCompactTitle, 2)[0] ?? $_fallbackCompactTitle;
                $_fallbackCompactTitle = preg_replace('/\s+/', ' ', trim((string)$_fallbackCompactTitle));

                if (function_exists('mb_strlen') && mb_strlen($_fallbackCompactTitle, 'UTF-8') > 24) {
                    $_fallbackCompactTitle = trim((string)mb_substr($_fallbackCompactTitle, 0, 24, 'UTF-8'));
                } elseif (!function_exists('mb_strlen') && strlen($_fallbackCompactTitle) > 24) {
                    $_fallbackCompactTitle = trim(substr($_fallbackCompactTitle, 0, 24));
                }

                $dynamicCardTitle = $_fallbackCompactTitle !== '' ? $_fallbackCompactTitle : 'Clash of Clans Account';
            }
        }

        // Rocket League: show platform and rank only, e.g. "PC · Champion II".
        if ($useDynamicCardSchema && in_array($_gameRaw, ['rocket-league', 'rocketleague', 'rl'], true)) {
            $_rlFormat = static function ($value): string {
                if (is_array($value)) {
                    $value = implode(', ', array_filter(array_map('strval', $value)));
                }
                return trim((string)$value);
            };

            $_rlPlatform = '';
            foreach (['platform', 'platforms', 'account_platform'] as $_rlPlatformKey) {
                $_rlPlatform = $_rlFormat($gameData[$_rlPlatformKey] ?? $account[$_rlPlatformKey] ?? '');
                if ($_rlPlatform !== '') break;
            }

            $_rlRank = '';
            foreach (['rank_label', 'rank_name', 'rank', 'current_rank'] as $_rlRankKey) {
                $_rlRank = $_rlFormat($gameData[$_rlRankKey] ?? '');
                if ($_rlRank !== '') break;
            }
            if ($_rlRank === '' || is_numeric($_rlRank)) {
                $_rlRankLabel = $_rlFormat($account['rank_label'] ?? '');
                if ($_rlRankLabel === '' && function_exists('util_get_rank_label')) {
                    $_rlRankLabel = $_rlFormat(util_get_rank_label($game, $universalRank));
                }
                if ($_rlRankLabel !== '') $_rlRank = $_rlRankLabel;
            }

            $_rlParts = [];
            if ($_rlPlatform !== '') $_rlParts[] = $_rlPlatform;
            if ($_rlRank !== '' && !is_numeric($_rlRank)) $_rlParts[] = $_rlRank;

            if (!empty($_rlParts)) {
                $dynamicCardTitle = implode(' · ', $_rlParts);
            }
        }

        // ARC Raiders: the title field only yields the bare level number ("113").
        // Prefix it so the card header reads "Level 113" instead of just a number.
        if ($useDynamicCardSchema && in_array($_gameRaw, ['arc-raiders', 'arcraiders', 'arc'], true)) {
            $_arcTitle = trim((string)$dynamicCardTitle);
            if ($_arcTitle !== '' && is_numeric($_arcTitle)) {
                $dynamicCardTitle = 'Level ' . $_arcTitle;
            } elseif ($_arcTitle === '' || stripos($_arcTitle, 'level') === false) {
                $_arcLevel = $gameData['level'] ?? $gameData['account_level'] ?? $gameData['xp_level'] ?? $account['level'] ?? null;
                if (is_array($_arcLevel)) $_arcLevel = reset($_arcLevel);
                $_arcLevel = trim((string)$_arcLevel);
                if ($_arcLevel !== '' && is_numeric($_arcLevel)) {
                    $dynamicCardTitle = 'Level ' . (int)$_arcLevel;
                }
            }
        }

        // Fortnite: the title field only yields the platform ("PC"), which says very little.
        // Append the skin count so the card header reads e.g. "PlayStation · 224 Skins".
        if ($useDynamicCardSchema && in_array($_gameRaw, ['fortnite', 'fn'], true)) {
            $_fnSkins = null;
            // game_data stores the amount as "outfits_skins" (e.g. {"platform":"PC","outfits_skins":"176"}).
            foreach (['outfits_skins', 'outfit_skins', 'skins', 'skins_count', 'skin_count', 'total_skins'] as $_skinKey) {
                $_rawSkins = $gameData[$_skinKey] ?? $account[$_skinKey] ?? null;
                if ($_rawSkins === null || $_rawSkins === '' || (is_array($_rawSkins) && empty($_rawSkins))) continue;

                if (is_array($_rawSkins)) {
                    $_fnSkins = count(array_filter($_rawSkins, static fn($v) => $v !== null && $v !== ''));
                    break;
                }

                $_rawSkins = trim((string)$_rawSkins);
                if ($_rawSkins === '') continue;
                if (is_numeric($_rawSkins)) {
                    $_fnSkins = (int)$_rawSkins;
                    break;
                }
                if (strpos($_rawSkins, '|') !== false) {
                    $_fnSkins = count(array_filter(array_map('trim', explode('|', $_rawSkins))));
                    break;
                }
                if (strpos($_rawSkins, ',') !== false) {
                    $_fnSkins = count(array_filter(array_map('trim', explode(',', $_rawSkins))));
                    break;
                }
                // Free text we cannot count: keep looking at the next candidate key.
            }

            if (is_int($_fnSkins) && $_fnSkins > 0) {
                $_fnTitle = trim((string)$dynamicCardTitle);
                $_fnSkinsLabel = number_format($_fnSkins, 0, ',', '.') . ' Skins';
                // Do not duplicate if the title already mentions the skins.
                if ($_fnTitle !== '' && stripos($_fnTitle, 'skin') === false) {
                    $dynamicCardTitle = $_fnTitle . ' · ' . $_fnSkinsLabel;
                } elseif ($_fnTitle === '') {
                    $dynamicCardTitle = $_fnSkinsLabel;
                }
            }
        }

        // Pokémon: show team and Pokémon count as the card title instead of the listing title.
        // Example: "Instinct · 340 Pokémons".
        if ($useDynamicCardSchema && in_array($_gameRaw, ['pokemon-go', 'pokemongo', 'pokemon'], true)) {
            $_pokemonTeam = '';
            foreach (['team', 'pokemon_team', 'team_name'] as $_teamKey) {
                if (isset($gameData[$_teamKey]) && $gameData[$_teamKey] !== '') {
                    $_pokemonTeam = $gameData[$_teamKey];
                    break;
                }
                if (isset($account[$_teamKey]) && $account[$_teamKey] !== '') {
                    $_pokemonTeam = $account[$_teamKey];
                    break;
                }
            }
            if (is_array($_pokemonTeam)) {
                $_pokemonTeam = implode(', ', array_filter(array_map('strval', $_pokemonTeam)));
            }
            $_pokemonTeam = trim((string)$_pokemonTeam);

            $_pokemonCount = null;
            foreach (['pokemon_count', 'pokemons_count', 'pokemons', 'pokemon', 'pokemon_amount', 'number_of_pokemons'] as $_countKey) {
                $_rawCount = null;
                if (array_key_exists($_countKey, $gameData) && $gameData[$_countKey] !== '' && $gameData[$_countKey] !== null) {
                    $_rawCount = $gameData[$_countKey];
                } elseif (array_key_exists($_countKey, $account) && $account[$_countKey] !== '' && $account[$_countKey] !== null) {
                    $_rawCount = $account[$_countKey];
                }

                if ($_rawCount === null) {
                    continue;
                }

                if (is_array($_rawCount)) {
                    $_pokemonCount = count(array_filter($_rawCount, static fn($v) => $v !== null && $v !== ''));
                } else {
                    $_rawCountString = trim((string)$_rawCount);
                    if ($_rawCountString === '') {
                        continue;
                    }
                    if (is_numeric($_rawCountString)) {
                        $_pokemonCount = (int)$_rawCountString;
                    } elseif (strpos($_rawCountString, '|') !== false) {
                        $_pokemonCount = count(array_filter(array_map('trim', explode('|', $_rawCountString))));
                    } elseif (strpos($_rawCountString, ',') !== false) {
                        $_pokemonCount = count(array_filter(array_map('trim', explode(',', $_rawCountString))));
                    } else {
                        $_pokemonCount = $_rawCountString;
                    }
                }
                break;
            }

            $_pokemonTitleParts = [];
            if ($_pokemonTeam !== '') {
                $_pokemonTitleParts[] = $_pokemonTeam;
            }
            if ($_pokemonCount !== null && $_pokemonCount !== '' && $_pokemonCount !== 0) {
                $_pokemonTitleParts[] = is_int($_pokemonCount)
                    ? number_format($_pokemonCount, 0, ',', '.') . ' Pokémons'
                    : (string)$_pokemonCount;
            }

            if (!empty($_pokemonTitleParts)) {
                $dynamicCardTitle = implode(' · ', $_pokemonTitleParts);
            }
        }

        $dynamicHeaderFields = $useDynamicCardSchema && function_exists('util_account_schema_fields') ? util_account_schema_fields($game, 'show_on_card_header') : [];
        $dynamicBadgeFields  = $useDynamicCardSchema && function_exists('util_account_schema_fields') ? util_account_schema_fields($game, 'show_on_card') : [];

        // Fallback: if no schema/header field exists but the account has platform data,
        // still show platform icons for generic games.
        if ($useDynamicCardSchema && empty($dynamicHeaderFields) && !empty($gameData['platform'])) {
            $dynamicHeaderFields = [[
                'key' => 'platform',
                'label' => 'Platform',
                'type' => is_array($gameData['platform']) ? 'multiselect' : 'select',
                'icon_type' => 'platform',
                'show_on_card_header' => true,
            ]];
        }

        $images = json_decode($account['images'] ?? '[]', true);
        if (!is_array($images)) $images = [];
        $firstImage = !empty($images) ? $images[0] : '';
        $remainingCount = max(0, count($images) - 1);

        // ═══════════════════════════════════════════════════════════════════
        // SELLER SALES - UNIFIED SYSTEM
        // ═══════════════════════════════════════════════════════════════════
        // Prefer seller_total_sales from SQL query (see ajax.php)
        // Fallback to function call if not present
        // ═══════════════════════════════════════════════════════════════════
        
        $sellerName     = $account['seller_username'] ?? null;
        $sellerOnline   = !empty($account['seller_is_online']);
        $sellerIcon     = $account['seller_icon'] ?? null;
        $sellerSlug     = lb_seller_profile_slug_from_value($account['seller_slug'] ?? '', $sellerName ?? '');
        $sellerLink     = '/sellers/' . rawurlencode($sellerSlug);
        
        // Get total sales from query or fallback to function
        $sellerSold = lb_db_seller_total_sales(
            (int)($account['seller_id'] ?? 0),
            (int)($account['seller_total_sales'] ?? $account['total_sales'] ?? $account['total_sold'] ?? $account['seller_sold'] ?? 0)
        );
        
        $sellerRating   = $account['seller_rating'] ?? null;
        $sellerRank     = trim((string)($account['seller_rank'] ?? ''));
        $sellerRankIconStored = trim((string)($account['seller_rank_icon'] ?? ''));
        $sellerRecommended = $sellerSold >= 10;
        $sellerVerified = !empty($account['seller_is_active']);

        $sellerRankMeta = seller_card_rank_meta($sellerRank, $sellerRankIconStored);
        $sellerCheckColor = $sellerRankMeta['color'];
        $sellerRankTooltip = $sellerRankMeta['title'];
        $sellerRankIcon = $sellerRankMeta['class'];
    ?>

    <div class="account-card" data-price="<?= $price_float ?>" data-rank="<?= $rankScore ?>">
        <a href="/<?= htmlspecialchars($_slugForUrl) ?>/account/<?= $account['slug'] ?>" class="cover-link">
            <h3 class="title">
                <?php if ($isRobloxCard && (!empty($robloxExperienceMeta['name']) || !empty($robloxExperienceMeta['icon']))): ?>
                    <?php if (!empty($robloxExperienceMeta['icon'])): ?>
                        <?= account_card_game_icon_html($robloxExperienceMeta['icon']) ?>
                    <?php else: ?>
                        <?= account_card_game_icon_html($cardGameIconUrl) ?>
                    <?php endif; ?>
                    <span class="account-card-title-text"><?= htmlspecialchars(!empty($robloxExperienceMeta['name']) ? $robloxExperienceMeta['name'] : $dynamicCardTitle) ?></span>
                <?php elseif ($useDynamicCardSchema): ?>
                    <?php $_dynamicHeaderIconRendered = false; ?>
                    <?php foreach ($dynamicHeaderFields as $_hField): ?>
                        <?php
                            $_hVal = function_exists('util_account_schema_value') ? util_account_schema_value($account, $gameData, $_hField) : ($gameData[$_hField['key'] ?? ''] ?? '');
                            $_hIconType = $_hField['icon_type'] ?? '';
                        ?>
                        <?php if ($_hIconType === 'platform' && function_exists('util_account_platform_icons_html')): ?>
                            <?php $_hIconHtml = trim((string)util_account_platform_icons_html($_hVal, 'account-card-platform-icon')); ?>
                            <?php if ($_hIconHtml !== ''): ?>
                                <?= $_hIconHtml ?>
                                <?php $_dynamicHeaderIconRendered = true; ?>
                            <?php endif; ?>
                        <?php elseif ($_hIconType === 'rank' && !empty($_hVal)): ?>
                            <img src="<?= account_card_rank_img($game, (int)$_hVal) ?>" class="rank-icon">
                            <?php $_dynamicHeaderIconRendered = true; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$_dynamicHeaderIconRendered): ?>
                        <?php if ($isRobloxCard && !empty($robloxExperienceMeta['icon'])): ?>
                            <?= account_card_game_icon_html($robloxExperienceMeta['icon']) ?>
                        <?php else: ?>
                            <?= account_card_game_icon_html($cardGameIconUrl) ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <span class="account-card-title-text"><?= htmlspecialchars($isRobloxCard && !empty($robloxExperienceMeta['name']) ? $robloxExperienceMeta['name'] : $dynamicCardTitle) ?></span>
                <?php else: ?>
                    <img src="<?= account_card_rank_img($game, $universalRank) ?>" class="rank-icon">
                    <?php if ($game === 'lol'): ?>
                        <?= strtoupper($account['server'] ?? '') . ' - ' . lol_rank_display_text($account['current_rank'] ?? $universalRank, $div, $lp) ?>
                    <?php else: ?>
                        <?= strtoupper($account['server'] ?? '') . ' - ' . htmlspecialchars($account['rank_label'] ?? util_get_rank_label($game, $universalRank)) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </h3>

            <p class="excerpt">
                <?= implode(' ', array_slice(explode(' ', $account['title']), 0, 40)) ?>
            </p>

            <div class="image-box">
                <img src="<?= $firstImage ?: ASSET_URL . '/core/main/img/banners/account.jpg' ?>">

                <?php if ($remainingCount > 0): ?>
                    <span class="badge ">
                        <i class="fas fa-images"></i> +<?= $remainingCount ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="highlights">
                <?php if ($game === 'val'): ?>
                    <span class="badge bg-grey">
                        <i class="fas fa-user-ninja"></i>
                        <?= $valAgentsCount ?> Agents
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-gun"></i>
                        <?= $valWeaponSkinsCount ?> Skins
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-arrow-turn-up"></i> Level <?= (int)($account['level'] ?? 0) ?>
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-coins"></i> <?= $valPoints ?> VP
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-gem"></i> <?= $valRadianite ?> Rad
                    </span>
                    <?php if ($valWinrate > 0): ?>
                        <span class="badge bg-grey">
                            <i class="fas fa-chart-line"></i> <?= $valWinrate ?>% WR
                        </span>
                    <?php endif; ?>
                    <?php if ($valRankedReady): ?>
                        <span class="badge bg-grey">
                            <i class="fas fa-circle-check"></i> Ranked Ready
                        </span>
                    <?php endif; ?>
                <?php elseif ($useDynamicCardSchema): ?>
                    <?php foreach ($dynamicBadgeFields as $_bField): ?>
                        <?php
                            $_bVal = function_exists('util_account_schema_value') ? util_account_schema_value($account, $gameData, $_bField) : ($gameData[$_bField['key'] ?? ''] ?? null);
                            $_bType = $_bField['type'] ?? 'text';
                            if ($_bType === 'checkbox' && empty($_bVal)) continue;
                            if ($_bVal === null || $_bVal === '' || (is_array($_bVal) && empty($_bVal))) continue;
                            $_bIcon = trim((string)($_bField['icon'] ?? 'fas fa-circle'));
                            $_bSuffix = trim((string)($_bField['suffix'] ?? ''));
                            $_bPrefix = trim((string)($_bField['prefix'] ?? ''));
                            $_bBaseText = function_exists('util_account_format_schema_value') ? util_account_format_schema_value($_bVal) : (string)$_bVal;
                            $_bText = $_bType === 'checkbox' ? (string)($_bField['label'] ?? 'Yes') : trim(preg_replace('/\s+/', ' ', $_bBaseText));
                        ?>
                        <span class="badge bg-grey"><i class="<?= htmlspecialchars($_bIcon) ?>"></i>
                            <?php if ($_bPrefix !== '' && $_bType !== 'checkbox'): ?><?= htmlspecialchars($_bPrefix) ?>&nbsp;<?php endif; ?><?= htmlspecialchars($_bText) ?><?php if ($_bSuffix !== '' && $_bType !== 'checkbox'): ?>&nbsp;<?= htmlspecialchars($_bSuffix) ?><?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="badge bg-grey">
                        <i class="fas fa-helmet-battle"></i>
                        <?= $lolChampionsCount ?> Champions
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-masks-theater"></i>
                        <?= $lolSkinsCount ?> Skins
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-arrow-turn-up"></i> Level <?= (int)($account['level'] ?? 0) ?>
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-gem"></i> <?= (int)($account['blue_essence'] ?? 0) ?> BE
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-hand-back-fist"></i> <?= (int)($account['riot_points'] ?? 0) ?> RP
                    </span>
                <?php endif; ?>
            </div>

            <div class="totals">
                <span class="fw-bold price-eur">
                    <?= util_format_currency_display($_SESSION['currency']) . util_format_price_display($account['price']) ?>
                    <?= $_SESSION['currency'] ?>
                </span>
                <span class="btn primary"><?= t('Buy Now') ?> <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <?php if ($sellerName): ?>
        <div class="seller-info">
            <div class="seller-info__left">
                <?php
                    $defaultIcon = ASSET_URL . '/public/uploads/icons/default.png';
                    $iconSrc = !empty($sellerIcon) ? $sellerIcon : $defaultIcon;
                ?>
                <img src="<?= htmlspecialchars($iconSrc) ?>" alt="<?= htmlspecialchars($sellerName) ?>" class="seller-info__avatar">
                <a class="seller-info__name" href="<?= htmlspecialchars($sellerLink, ENT_QUOTES) ?>" data-seller-name="<?= htmlspecialchars($sellerName, ENT_QUOTES) ?>" data-seller-slug="<?= htmlspecialchars($sellerSlug, ENT_QUOTES) ?>">
                    <?php if ($sellerVerified): ?>
                        <span class="seller-rank-trigger">
                            <span class="seller-info__name-text"><?= htmlspecialchars($sellerName) ?></span>
                            <?php if ($sellerOnline): ?><span class="seller-online-dot" aria-label="Online" title="Online"></span><?php endif; ?>
                            <i class="<?= htmlspecialchars($sellerRankIcon) ?> seller-info__verified"
                               style="color: <?= htmlspecialchars($sellerCheckColor) ?>;"></i>
                            <span class="seller-rank-tooltip">
                                <i class="<?= htmlspecialchars($sellerRankIcon) ?> seller-rank-tooltip__icon"
                                   style="color: <?= htmlspecialchars($sellerCheckColor) ?>;"></i>
                                <?= htmlspecialchars($sellerRankTooltip) ?>
                            </span>
                        </span>
                    <?php else: ?>
                        <span class="seller-info__name-text"><?= htmlspecialchars($sellerName) ?></span>
                        <?php if ($sellerOnline): ?><span class="seller-online-dot" aria-label="Online" title="Online"></span><?php endif; ?>
                    <?php endif; ?>
                </a>
            </div>
            <div class="seller-info__right">
                <span class="seller-info__sold">
                    <?= number_format($sellerSold) ?> Sold
                </span>
                <?php if ($sellerRating !== null): ?>
                    <span class="seller-info__rating">
                        <i class="fas fa-thumbs-up"></i>
                        <?= t('Trusted') ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($sellerRecommended): ?>
            <i class="fas fa-star delivery-type account-card__recommended-icon">
                <span class="recommended-tooltip">Recommended Seller</span>
            </i>
        <?php endif; ?>

        <?php if (($account['delivery_type'] ?? '') === 'instant'): ?>
            <i class="fas fa-bolt delivery-type"><span class="delivery-tooltip">Instant Delivery</span></i>
        <?php else: ?>
            <i class="fas fa-truck delivery-type"><span class="delivery-tooltip">Manual Delivery</span></i>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<style id="lb-account-cards-redesign-2026">
body.ranked-accounts-page{
  --shop-bg:#04050f;
  --shop-surface:#0a0d1e;
  --shop-surface-2:#0d1124;
  --shop-line:rgba(255,255,255,.09);
  --shop-line-strong:rgba(124,159,255,.24);
  --shop-text:#f7f8ff;
  --shop-muted:rgba(230,234,255,.62);
  --shop-accent:#4f6ef7;
  --shop-accent-2:#7c9fff;
  --shop-success:#34d399;
}
body.ranked-accounts-page #accountsGrid.accounts-grid{
  display:grid!important;
  grid-template-columns:repeat(3,minmax(0,1fr))!important;
  gap:18px!important;
  align-items:stretch!important;
  overflow:visible!important;
}
body.ranked-accounts-page #accountsGrid .account-card{
  position:relative!important;
  width:100%!important;
  min-width:0!important;
  min-height:0!important;
  height:auto!important;
  padding:0!important;
  border:1px solid var(--shop-line)!important;
  border-radius:24px!important;
  background:
    radial-gradient(500px 220px at 12% 0%,rgba(79,110,247,.13),transparent 62%),
    linear-gradient(155deg,rgba(13,17,36,.98),rgba(7,9,22,.98))!important;
  box-shadow:0 18px 48px rgba(0,0,0,.32),inset 0 1px 0 rgba(255,255,255,.04)!important;
  overflow:visible!important;
  transition:transform .22s ease,border-color .22s ease,box-shadow .22s ease!important;
}
body.ranked-accounts-page #accountsGrid .account-card:hover{
  transform:translateY(-4px)!important;
  border-color:rgba(124,159,255,.32)!important;
  box-shadow:0 26px 65px rgba(0,0,0,.42),0 0 0 1px rgba(79,110,247,.06),inset 0 1px 0 rgba(255,255,255,.06)!important;
}
body.ranked-accounts-page #accountsGrid .account-card>.cover-link{
  display:flex!important;
  flex-direction:column!important;
  gap:0!important;
  min-height:0!important;
  padding:18px 18px 0!important;
  color:inherit!important;
  text-decoration:none!important;
  overflow:visible!important;
}
body.ranked-accounts-page #accountsGrid .account-card .title{
  display:flex!important;
  align-items:center!important;
  gap:11px!important;
  min-height:38px!important;
  height:auto!important;
  max-height:none!important;
  margin:0!important;
  padding:0 84px 0 0!important;
  color:var(--shop-text)!important;
  font-size:16px!important;
  font-weight:900!important;
  line-height:1.2!important;
  letter-spacing:-.015em!important;
  overflow:visible!important;
}
body.ranked-accounts-page #accountsGrid .account-card .title>img.rank-icon,
body.ranked-accounts-page #accountsGrid .account-card .title>.rank-icon,
body.ranked-accounts-page #accountsGrid .account-card .account-card-game-icon-fallback{
  width:38px!important;
  height:38px!important;
  min-width:38px!important;
  flex:0 0 38px!important;
  border-radius:12px!important;
  padding:5px!important;
  background:linear-gradient(145deg,rgba(255,255,255,.08),rgba(255,255,255,.025))!important;
  border:1px solid rgba(255,255,255,.09)!important;
  object-fit:contain!important;
}
body.ranked-accounts-page #accountsGrid .account-card .account-card-title-text{
  min-width:0!important;
  overflow:hidden!important;
  text-overflow:ellipsis!important;
  white-space:nowrap!important;
}
body.ranked-accounts-page #accountsGrid .account-card .excerpt{
  min-height:40px!important;
  height:auto!important;
  max-height:40px!important;
  margin:10px 0 14px!important;
  color:var(--shop-muted)!important;
  font-size:13px!important;
  font-weight:500!important;
  line-height:1.5!important;
  display:-webkit-box!important;
  -webkit-line-clamp:2!important;
  -webkit-box-orient:vertical!important;
  overflow:hidden!important;
}
body.ranked-accounts-page #accountsGrid .account-card .image-box{
  position:relative!important;
  width:100%!important;
  height:184px!important;
  min-height:184px!important;
  max-height:184px!important;
  margin:0!important;
  border-radius:18px!important;
  overflow:hidden!important;
  background:#070914!important;
  border:1px solid rgba(255,255,255,.07)!important;
}
body.ranked-accounts-page #accountsGrid .account-card .image-box:after{
  content:""!important;
  position:absolute!important;
  inset:0!important;
  display:block!important;
  background:linear-gradient(180deg,transparent 48%,rgba(4,5,15,.65) 100%)!important;
  pointer-events:none!important;
}
body.ranked-accounts-page #accountsGrid .account-card .image-box>img{
  width:100%!important;
  height:100%!important;
  border-radius:0!important;
  object-fit:cover!important;
  transition:transform .35s ease,filter .35s ease!important;
}
body.ranked-accounts-page #accountsGrid .account-card:hover .image-box>img{
  transform:scale(1.035)!important;
  filter:saturate(1.06) contrast(1.02)!important;
}
body.ranked-accounts-page #accountsGrid .account-card .image-box>.badge{
  position:absolute!important;
  right:10px!important;
  bottom:10px!important;
  z-index:2!important;
  padding:6px 9px!important;
  border-radius:10px!important;
  color:#fff!important;
  background:rgba(5,7,18,.78)!important;
  border:1px solid rgba(255,255,255,.14)!important;
  backdrop-filter:blur(10px)!important;
  font-size:11px!important;
}
body.ranked-accounts-page #accountsGrid .account-card .highlights{
  display:flex!important;
  flex-wrap:wrap!important;
  align-content:flex-start!important;
  gap:7px!important;
  width:100%!important;
  height:76px!important;
  min-height:76px!important;
  max-height:76px!important;
  margin:14px 0 0!important;
  padding:0 5px 0 0!important;
  overflow-y:auto!important;
  overflow-x:hidden!important;
}
body.ranked-accounts-page #accountsGrid .account-card .highlights .badge{
  display:inline-flex!important;
  align-items:center!important;
  gap:6px!important;
  min-height:30px!important;
  padding:6px 9px!important;
  border-radius:10px!important;
  background:rgba(255,255,255,.045)!important;
  border:1px solid rgba(255,255,255,.075)!important;
  color:rgba(239,242,255,.82)!important;
  font-size:11px!important;
  font-weight:750!important;
  line-height:1!important;
  white-space:nowrap!important;
}
body.ranked-accounts-page #accountsGrid .account-card .highlights .badge i{
  color:#8ea5ff!important;
  font-size:11px!important;
}
body.ranked-accounts-page #accountsGrid .account-card .totals{
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  gap:14px!important;
  width:100%!important;
  height:auto!important;
  min-height:70px!important;
  max-height:none!important;
  margin:14px 0 0!important;
  padding:14px 0 16px!important;
  border-top:1px solid rgba(255,255,255,.075)!important;
}
body.ranked-accounts-page #accountsGrid .account-card .totals .price-eur{
  color:#fff!important;
  font-size:22px!important;
  font-weight:950!important;
  letter-spacing:-.03em!important;
  white-space:nowrap!important;
}
body.ranked-accounts-page #accountsGrid .account-card .totals .btn.primary{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:8px!important;
  min-width:126px!important;
  height:42px!important;
  padding:0 16px!important;
  border:1px solid rgba(124,159,255,.36)!important;
  border-radius:13px!important;
  background:linear-gradient(135deg,#4f6ef7,#3b58e8)!important;
  color:#fff!important;
  box-shadow:0 10px 24px rgba(79,110,247,.23)!important;
  font-size:13px!important;
  font-weight:900!important;
  line-height:1!important;
  transition:transform .18s ease,filter .18s ease,box-shadow .18s ease!important;
}
body.ranked-accounts-page #accountsGrid .account-card:hover .totals .btn.primary{
  filter:brightness(1.08)!important;
  box-shadow:0 14px 30px rgba(79,110,247,.31)!important;
}
body.ranked-accounts-page #accountsGrid .account-card .totals .btn.primary i{
  margin:0!important;
  font-size:11px!important;
}
body.ranked-accounts-page #accountsGrid .account-card>.seller-info,
body.ranked-accounts-page #accountsGrid .account-card>.lb-seller-footer{
  display:flex!important;
  align-items:center!important;
  min-height:58px!important;
  height:auto!important;
  max-height:none!important;
  margin:0!important;
  padding:12px 18px!important;
  border-top:1px solid rgba(255,255,255,.075)!important;
  border-radius:0 0 24px 24px!important;
  background:rgba(255,255,255,.018)!important;
  overflow:visible!important;
}
body.ranked-accounts-page #accountsGrid .account-card .delivery-type{
  top:18px!important;
  right:18px!important;
  width:34px!important;
  height:34px!important;
  min-width:34px!important;
  min-height:34px!important;
  border:1px solid rgba(255,255,255,.1)!important;
  border-radius:11px!important;
  background:rgba(8,10,25,.88)!important;
  box-shadow:0 8px 20px rgba(0,0,0,.22)!important;
  backdrop-filter:blur(12px)!important;
}
body.ranked-accounts-page #accountsGrid .account-card .delivery-type.account-card__recommended-icon{
  right:58px!important;
}
@media(max-width:1180px){
  body.ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;}
}
@media(max-width:720px){
  body.ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:1fr!important;gap:14px!important;}
  body.ranked-accounts-page #accountsGrid .account-card{border-radius:20px!important;}
  body.ranked-accounts-page #accountsGrid .account-card>.cover-link{padding:15px 15px 0!important;}
  body.ranked-accounts-page #accountsGrid .account-card .title{padding-right:78px!important;font-size:15px!important;}
  body.ranked-accounts-page #accountsGrid .account-card .image-box{height:176px!important;min-height:176px!important;max-height:176px!important;}
  body.ranked-accounts-page #accountsGrid .account-card .highlights{height:auto!important;min-height:0!important;max-height:70px!important;}
  body.ranked-accounts-page #accountsGrid .account-card .totals .price-eur{font-size:20px!important;}
  body.ranked-accounts-page #accountsGrid .account-card>.seller-info,
  body.ranked-accounts-page #accountsGrid .account-card>.lb-seller-footer{padding:11px 15px!important;border-radius:0 0 20px 20px!important;}
}
</style>

<style id="lb-account-card-visual-correction-final">
/* Wider, calmer cards with fixed content zones and stronger readability. */
body.ranked-accounts-page #accountsGrid.accounts-grid{
  display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:18px!important;align-items:stretch!important;
}
body.ranked-accounts-page #accountsGrid .account-card{
  position:relative!important;z-index:1!important;min-width:0!important;height:100%!important;border-radius:18px!important;
  background:#0d1021!important;border:1px solid rgba(255,255,255,.075)!important;
  box-shadow:none!important;overflow:visible!important;transition:border-color .18s ease,transform .18s ease!important;
}
body.ranked-accounts-page #accountsGrid .account-card:hover{z-index:1000!important;border-color:rgba(124,146,255,.25)!important;transform:translateY(-2px)!important;}
body.ranked-accounts-page #accountsGrid .account-card>.cover-link{display:flex!important;flex-direction:column!important;height:100%!important;padding:16px 16px 0!important;}
body.ranked-accounts-page #accountsGrid .account-card .title{min-height:38px!important;padding-right:76px!important;font-size:15px!important;gap:10px!important;}
body.ranked-accounts-page #accountsGrid .account-card .title>img.rank-icon,
body.ranked-accounts-page #accountsGrid .account-card .title>.rank-icon,
body.ranked-accounts-page #accountsGrid .account-card .account-card-game-icon-fallback{width:36px!important;height:36px!important;min-width:36px!important;flex-basis:36px!important;border-radius:11px!important;}
body.ranked-accounts-page #accountsGrid .account-card .excerpt{min-height:36px!important;max-height:36px!important;margin:8px 0 12px!important;font-size:11.5px!important;line-height:1.55!important;color:#8f97b5!important;}
body.ranked-accounts-page #accountsGrid .account-card .image-box{height:190px!important;min-height:190px!important;max-height:190px!important;border-radius:14px!important;background:#070912!important;border-color:rgba(255,255,255,.07)!important;}
body.ranked-accounts-page #accountsGrid .account-card .highlights{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:6px!important;height:auto!important;min-height:68px!important;max-height:none!important;margin:12px 0 0!important;padding:0 0 18px!important;overflow:visible!important;}
body.ranked-accounts-page #accountsGrid .account-card .highlights .badge{min-width:0!important;min-height:30px!important;padding:6px 7px!important;border-radius:9px!important;justify-content:flex-start!important;background:#151827!important;border-color:rgba(255,255,255,.07)!important;color:#b8bfd8!important;font-size:10px!important;overflow:hidden!important;text-overflow:ellipsis!important;}
body.ranked-accounts-page #accountsGrid .account-card .totals{margin-top:auto!important;min-height:64px!important;padding:13px 0!important;border-top:1px solid rgba(255,255,255,.065)!important;}
body.ranked-accounts-page #accountsGrid .account-card .totals .price-eur{font-size:20px!important;}
body.ranked-accounts-page #accountsGrid .account-card .totals .btn.primary{min-width:112px!important;height:40px!important;border-radius:11px!important;background:#4f6ef7!important;border:0!important;box-shadow:none!important;font-size:12px!important;}
body.ranked-accounts-page #accountsGrid .account-card>.seller-info,
body.ranked-accounts-page #accountsGrid .account-card>.lb-seller-footer{min-height:54px!important;padding:10px 16px!important;border-radius:0 0 18px 18px!important;background:#101322!important;border-top:1px solid rgba(255,255,255,.065)!important;}
body.ranked-accounts-page #accountsGrid .account-card .delivery-type{top:16px!important;right:16px!important;width:32px!important;height:32px!important;min-width:32px!important;min-height:32px!important;border-radius:10px!important;background:#090b17!important;}
body.ranked-accounts-page #accountsGrid .account-card .delivery-type.account-card__recommended-icon{right:54px!important;}
body.ranked-accounts-page #accountsGrid .account-card .delivery-tooltip,
body.ranked-accounts-page #accountsGrid .account-card .recommended-tooltip{
  top:calc(100% + 9px)!important;
  bottom:auto!important;
  z-index:1001!important;
}
body.ranked-accounts-page #accountsGrid .account-card .delivery-tooltip::after,
body.ranked-accounts-page #accountsGrid .account-card .recommended-tooltip::after{
  top:auto!important;
  bottom:100%!important;
  border-color:transparent transparent rgba(26,31,59,.98) transparent!important;
}
@media(max-width:1080px){body.ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;}}
@media(max-width:680px){
 body.ranked-accounts-page #accountsGrid.accounts-grid{grid-template-columns:1fr!important;gap:12px!important;}
 body.ranked-accounts-page #accountsGrid .account-card .image-box{height:178px!important;min-height:178px!important;max-height:178px!important;}
 body.ranked-accounts-page #accountsGrid .account-card .highlights{grid-template-columns:repeat(3,minmax(0,1fr))!important;}
}
</style>




<style id="lb-account-card-readable-type-v7">
/* Font sizes compensate for html zoom:0.88 without enlarging the whole layout. */
@media (min-width:768px){
  html body.ranked-accounts-page #accountsGrid .account-card .title,
  html body.ranked-accounts-page #accountsGrid .account-card .account-card-title-text{
    font-size:17px!important;line-height:1.28!important;font-weight:900!important;
  }
  html body.ranked-accounts-page #accountsGrid .account-card .excerpt{
    font-size:13px!important;line-height:1.48!important;min-height:42px!important;max-height:42px!important;
  }
  html body.ranked-accounts-page #accountsGrid .account-card .highlights .badge{
    font-size:11.5px!important;line-height:1.15!important;min-height:32px!important;padding:7px 8px!important;
  }
  html body.ranked-accounts-page #accountsGrid .account-card .highlights .badge i{
    font-size:11.5px!important;
  }
  html body.ranked-accounts-page #accountsGrid .account-card .totals .price-eur{
    font-size:23px!important;line-height:1.05!important;font-weight:950!important;
  }
  html body.ranked-accounts-page #accountsGrid .account-card .totals .btn,
  html body.ranked-accounts-page #accountsGrid .account-card .totals .btn.primary{
    min-width:122px!important;height:43px!important;font-size:13.5px!important;font-weight:850!important;
  }
  html body.ranked-accounts-page #accountsGrid .account-card .delivery-type{
    font-size:15px!important;
  }
}
@media (max-width:767px){
  html body.ranked-accounts-page #accountsGrid .account-card .title,
  html body.ranked-accounts-page #accountsGrid .account-card .account-card-title-text{font-size:16px!important;}
  html body.ranked-accounts-page #accountsGrid .account-card .excerpt{font-size:12.5px!important;}
  html body.ranked-accounts-page #accountsGrid .account-card .highlights .badge{font-size:10.5px!important;}
  html body.ranked-accounts-page #accountsGrid .account-card .totals .price-eur{font-size:22px!important;}
}

</style>
