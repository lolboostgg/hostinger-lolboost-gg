<?php
global $db;
$displayCurrency = $_SESSION['currency'] ?? 'EUR';
$displayRate = ($displayCurrency === 'USD' && function_exists('get_exchange_rate')) ? (float)get_exchange_rate() : 1.0;
$egirls = [];
try {
    $egirls = $db->run(
        // ep.* first so the profile row also carries game_ranks (ranks of the newer
        // boosting games); the explicit b.* columns after it keep winning on collisions.
        "SELECT ep.*, b.id, b.username, b.icon, b.voice_chat,
                ep.review_count, ep.review_avg, ep.is_online,
                ep.languages, ep.games, ep.lol_rank, ep.val_rank, ep.tft_rank, ep.timezone,
                (SELECT MIN(es.price_cents) FROM egirl_services es WHERE es.egirl_id = b.id AND es.is_active = 1) AS min_price
         FROM boosters b
         LEFT JOIN egirl_profiles ep ON ep.egirl_id = b.id
         WHERE b.is_banned = 0 AND b.is_egirl = 1 AND b.show_profile = 1
         ORDER BY ep.is_online DESC, ep.total_sessions DESC, b.username ASC
         LIMIT 24"
    );
} catch (Throwable $e) {
    $egirls = [];
}
$queueTypes = [
    'normal_draft' => ['title' => 'Normal Draft Game', 'desc' => 'Queue Normal Draft games together.', 'price' => 500, 'unit' => 'Games'],
    'swiftplay'    => ['title' => 'Swift Play', 'desc' => 'Fast Swift Play matches by her side.', 'price' => 400, 'unit' => 'Games'],
    'aram'         => ['title' => 'ARAM', 'desc' => 'Fun ARAM games with a GGirl.', 'price' => 400, 'unit' => 'Games'],
    'solo_queue'   => ['title' => 'Solo Queue', 'desc' => 'Duo your Ranked Solo/Duo queue.', 'price' => 600, 'unit' => 'Games'],
    'chatting'     => ['title' => '30 Minutes of Chatting', 'desc' => '30 minutes of voice chat and company.', 'price' => 600, 'unit' => 'Sessions'],
    'watching'     => ['title' => 'Watching You Play', 'desc' => 'She watches and hypes while you play.', 'price' => 500, 'unit' => 'Sessions'],
];
function ggl_money_from_cents($cents, $rate = 1.0, $currency = 'EUR') {
    $value = ((int)$cents / 100) * (float)$rate;
    return ($currency === 'USD' ? '$' : '€') . number_format($value, 2, '.', '');
}
function ggl_asset_path($path, $fallback) {
    $path = trim((string)$path);
    if ($path === '') return $fallback;
    if (preg_match('~^https?://~i', $path)) return $path;
    if (strpos($path, 'uploads/') === 0) return ASSET_URL . '/' . ltrim($path, '/');
    return ASSET_URL . '/uploads/' . ltrim($path, '/');
}
function ggl_unit_suffix($unit) {
    $unit = strtolower(trim((string)$unit));
    return rtrim($unit, 's'); // "Games" -> "game", "Sessions" -> "session"
}
$modeIcons = [
    'normal_draft' => 'fa-solid fa-gamepad',
    'swiftplay'    => 'fa-solid fa-bolt',
    'aram'         => 'fa-solid fa-dice-d20',
    'solo_queue'   => 'fa-solid fa-trophy',
    'chatting'     => 'fa-solid fa-comments',
    'watching'     => 'fa-solid fa-eye',
];

/* ── Servers (not price-related, stays hardcoded) ── */
$ggServers = [
    'euw'  => ['label' => 'EU-West',           'icon' => 'fa-solid fa-earth-europe'],
    'eune' => ['label' => 'EU-Nordic & East',  'icon' => 'fa-solid fa-earth-europe'],
    'na'   => ['label' => 'North America',     'icon' => 'fa-solid fa-earth-americas'],
    'tr'   => ['label' => 'Turkey',            'icon' => 'fa-solid fa-earth-europe'],
    'me'   => ['label' => 'Middle East',       'icon' => 'fa-solid fa-earth-asia'],
];

/* ── Rank fallback (used only if the JSON file is missing/invalid) ── */
$ggRanks = [
    'unranked' => ['label' => 'Unranked', 'tier' => 0, 'modifier' => 0],
    'iron'     => ['label' => 'Iron',     'tier' => 1, 'modifier' => 0],
    'bronze'   => ['label' => 'Bronze',   'tier' => 2, 'modifier' => 0],
    'silver'   => ['label' => 'Silver',   'tier' => 3, 'modifier' => 0],
    'gold'     => ['label' => 'Gold',     'tier' => 4, 'modifier' => 0],
    'platinum' => ['label' => 'Platinum', 'tier' => 5, 'modifier' => 0],
    'emerald'  => ['label' => 'Emerald',  'tier' => 6, 'modifier' => 0],
    'diamond'  => ['label' => 'Diamond',  'tier' => 7, 'modifier' => 0],
    'master'   => ['label' => 'Master',   'tier' => 8, 'modifier' => 0],
];

/* ════════════════════════════════════════════════════════════════
   Price config — loaded from ggirls-pricing.json (same folder as
   this file). Lets you change mode prices and per-rank surcharges
   without touching any code. If the file is missing or malformed,
   the hardcoded $queueTypes / $ggRanks above are used instead, so
   the page never breaks.

   Expected JSON shape:
   {
     "modes": {
       "normal_draft": { "title": "Normal Draft Game", "desc": "...", "price_cents": 500, "unit": "Games", "icon": "fa-solid fa-gamepad" },
       ...
     },
     "ranks": {
       "unranked": { "label": "Unranked", "tier": 0, "price_modifier_cents": 0 },
       "diamond":  { "label": "Diamond",  "tier": 7, "price_modifier_cents": 150 },
       ...
     }
   }

   "price_modifier_cents" is added ON TOP of the mode's per-unit
   price for whichever rank is selected (e.g. higher ranks can cost
   more per game). Set it to 0 for ranks that shouldn't change price.
   ════════════════════════════════════════════════════════════════ */
$ggPricingFileCandidates = [
    SYS_PATH . '/public/uploads/private/boost-forms/ggirls-lol.json',
    __DIR__ . '/ggirls-pricing.json',
];
$ggPricingFile = null;
foreach ($ggPricingFileCandidates as $candidate) {
    if (is_file($candidate) && is_readable($candidate)) {
        $ggPricingFile = $candidate;
        break;
    }
}
if ($ggPricingFile !== null) {
    $ggPricingRaw = @file_get_contents($ggPricingFile);
    $ggPricingDecoded = $ggPricingRaw !== false ? json_decode($ggPricingRaw, true) : null;
    if (is_array($ggPricingDecoded)) {
        if (!empty($ggPricingDecoded['modes']) && is_array($ggPricingDecoded['modes'])) {
            $jsonQueueTypes = [];
            $jsonModeIcons = [];
            foreach ($ggPricingDecoded['modes'] as $mkey => $m) {
                if (!is_array($m)) continue;
                $jsonQueueTypes[$mkey] = [
                    'title' => (string)($m['title'] ?? $mkey),
                    'desc'  => (string)($m['desc'] ?? ''),
                    'price' => (int)($m['price_cents'] ?? 0),
                    'unit'  => (string)($m['unit'] ?? 'Games'),
                ];
                $jsonModeIcons[$mkey] = (string)($m['icon'] ?? 'fa-solid fa-gamepad');
            }
            if (!empty($jsonQueueTypes)) {
                $queueTypes = $jsonQueueTypes;
                $modeIcons = $jsonModeIcons;
            }
        }
        if (!empty($ggPricingDecoded['ranks']) && is_array($ggPricingDecoded['ranks'])) {
            $jsonRanks = [];
            foreach ($ggPricingDecoded['ranks'] as $rkey => $r) {
                if (!is_array($r)) continue;
                $jsonRanks[$rkey] = [
                    'label'    => (string)($r['label'] ?? $rkey),
                    'tier'     => (int)($r['tier'] ?? 0),
                    'modifier' => (int)($r['price_modifier_cents'] ?? 0),
                ];
            }
            if (!empty($jsonRanks)) {
                $ggRanks = $jsonRanks;
            }
        }
    }
}

function ggl_rank_icon_html($tier) {
    if (function_exists('util_rank_img')) {
        try {
            $src = util_rank_img('lol', 'mini', (int)$tier);
            if (!empty($src)) {
                return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="">';
            }
        } catch (Throwable $e) { /* fall through to icon */ }
    }
    return '<i class="fa-solid fa-ranking-star"></i>';
}
?>
<input type="hidden" name="selected_egirl_id" id="selected_egirl_id" value="">
<input type="hidden" name="ggirl_assignment" id="ggirl_assignment" value="any_available">
<input type="hidden" name="ggirl_queue_type" id="ggirl_queue_type" value="normal_draft">
<input type="hidden" name="ggirl_price_cents" id="ggirl_price_cents" value="500">
<input type="hidden" name="ggirl_rank" id="ggirl_rank" value="unranked">

<div class="boost ggirl-boost-form" id="ggirl_lol_form_box" data-currency="<?= htmlspecialchars($displayCurrency, ENT_QUOTES, 'UTF-8') ?>" data-rate="<?= htmlspecialchars((string)$displayRate, ENT_QUOTES, 'UTF-8') ?>">
    <div class="ggl-hero">
        <span class="ggl-hero-icon"><img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" alt="GGirls"></span>
        <span>
            <h3><?= t('Choose Your Game Mode') ?></h3>
            <p><?= t('Select your game mode, server, rank and amount. Prices update instantly.') ?></p>
        </span>
    </div>

    <h4 class="ggl-title"><?= t('Available Modes') ?></h4>

    <div class="ggl-modes" id="ggl_modes">
        <?php foreach ($queueTypes as $key => $mode):
            $unitSuffix = ggl_unit_suffix($mode['unit']);
        ?>
            <label class="ggl-mode <?= $key === 'normal_draft' ? 'active' : '' ?>"
                data-price-cents="<?= (int)$mode['price'] ?>"
                data-title="<?= htmlspecialchars($mode['title'], ENT_QUOTES, 'UTF-8') ?>"
                data-icon="<?= htmlspecialchars($modeIcons[$key] ?? 'fa-solid fa-gamepad', ENT_QUOTES, 'UTF-8') ?>"
                data-unit="<?= htmlspecialchars($mode['unit'], ENT_QUOTES, 'UTF-8') ?>"
                data-unit-suffix="<?= htmlspecialchars($unitSuffix, ENT_QUOTES, 'UTF-8') ?>">
                <input type="radio" name="ggirl_mode_radio" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $key === 'normal_draft' ? 'checked' : '' ?>>

                <span class="ggl-mode-row">
                    <span class="ggl-mode-radio" aria-hidden="true"></span>
                    <span class="ggl-mode-icon"><i class="<?= htmlspecialchars($modeIcons[$key] ?? 'fa-solid fa-gamepad', ENT_QUOTES, 'UTF-8') ?>"></i></span>
                    <span class="ggl-mode-copy">
                        <strong><?= t($mode['title']) ?></strong>
                        <small><?= t($mode['desc']) ?></small>
                    </span>
                    <span class="ggl-price">
                        <strong class="ggl-price-amount"><?= ggl_money_from_cents((int)$mode['price'], $displayRate, $displayCurrency) ?></strong>
                        <small class="ggl-price-suffix">/<?= htmlspecialchars($unitSuffix, ENT_QUOTES, 'UTF-8') ?></small>
                    </span>
                </span>

                <?php if ($key === 'normal_draft'): ?>
                <div class="ggl-inline-config" id="ggl_inline_config">
                    <div class="ggl-inline-fields">
                        <div class="ggl-inline-field">
                            <h6><?= t('Server') ?></h6>
                            <div class="ggl-drop" id="ggl_server_drop">
                                <button type="button" class="ggl-drop-trigger" id="ggl_server_trigger">
                                    <span class="ggl-drop-icon"><i class="fa-solid fa-earth-europe"></i></span>
                                    <span class="ggl-drop-text"><?= t('EU-West') ?></span>
                                    <i class="fa-solid fa-chevron-down ggl-drop-arrow"></i>
                                </button>
                                <div class="ggl-drop-list" id="ggl_server_list">
                                    <?php $firstSrv = true; foreach ($ggServers as $skey => $srv): ?>
                                        <div class="ggl-drop-opt<?= $firstSrv ? ' active' : '' ?>" data-value="<?= htmlspecialchars($skey, ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="<?= htmlspecialchars($srv['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                            <span><?= t($srv['label']) ?></span>
                                        </div>
                                    <?php $firstSrv = false; endforeach; ?>
                                </div>
                                <select name="server" id="ggl_server" data-no-search="true" style="display:none;">
                                    <?php $firstSrv = true; foreach ($ggServers as $skey => $srv): ?>
                                        <option value="<?= htmlspecialchars($skey, ENT_QUOTES, 'UTF-8') ?>" <?= $firstSrv ? 'selected' : '' ?>><?= t($srv['label']) ?></option>
                                    <?php $firstSrv = false; endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ggl-inline-field">
                            <h6><?= t('Your Rank') ?></h6>
                            <div class="ggl-drop" id="ggl_rank_drop">
                                <button type="button" class="ggl-drop-trigger" id="ggl_rank_trigger">
                                    <span class="ggl-drop-icon"><?= ggl_rank_icon_html(0) ?></span>
                                    <span class="ggl-drop-text"><?= t('Unranked') ?></span>
                                    <i class="fa-solid fa-chevron-down ggl-drop-arrow"></i>
                                </button>
                                <div class="ggl-drop-list" id="ggl_rank_list">
                                    <?php $firstRank = true; foreach ($ggRanks as $rkey => $rank):
                                        $modifier = (int)($rank['modifier'] ?? 0);
                                    ?>
                                        <div class="ggl-drop-opt<?= $firstRank ? ' active' : '' ?>" data-value="<?= htmlspecialchars($rkey, ENT_QUOTES, 'UTF-8') ?>" data-modifier-cents="<?= $modifier ?>">
                                            <?= ggl_rank_icon_html($rank['tier']) ?>
                                            <span><?= t($rank['label']) ?></span>
                                            <?php if ($modifier !== 0): ?>
                                                <small class="ggl-drop-opt-modifier"><?= $modifier > 0 ? '+' : '' ?><?= ggl_money_from_cents($modifier, $displayRate, $displayCurrency) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php $firstRank = false; endforeach; ?>
                                </div>
                                <select name="ggirl_rank_select" id="ggirl_rank_select" data-no-search="true" style="display:none;">
                                    <?php $firstRank = true; foreach ($ggRanks as $rkey => $rank): ?>
                                        <option value="<?= htmlspecialchars($rkey, ENT_QUOTES, 'UTF-8') ?>" <?= $firstRank ? 'selected' : '' ?>><?= t($rank['label']) ?></option>
                                    <?php $firstRank = false; endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ggl-inline-field ggl-inline-games">
                            <h6 id="ggl_amount_label"><?= t('Games') ?></h6>
                            <div class="ggl-stepper ggl-stepper-pill">
                                <button type="button" class="ggl-step" data-step="-1" aria-label="Decrease">−</button>
                                <span class="ggl-stepper-center"><strong id="ggl_amount_display">1</strong><small id="ggl_amount_unit">games</small></span>
                                <input type="hidden" name="ggirl_amount" id="ggirl_amount" value="1">
                                <button type="button" class="ggl-step" data-step="1" aria-label="Increase">+</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="ggl-girls-source" id="ggl_girls_source" style="display:none;">

    <div class="ggl-girls">
        <?php if (!empty($egirls)): ?>
            <?php foreach ($egirls as $idx => $egirl):
                $avatar = ggl_asset_path($egirl['icon'] ?? '', ASSET_URL . '/website/images/gg-girl.svg');
                $rating = (float)($egirl['review_avg'] ?? 0);
                $gamesRaw = trim((string)($egirl['games'] ?? ''));
                $gameList = array_values(array_filter(array_map('trim', explode('|', $gamesRaw))));
                $gameLabels = ['lol' => 'League of Legends', 'val' => 'Valorant', 'tft' => 'Teamfight Tactics'];
                $gameIconMap = ['lol' => 'league-of-legends', 'val' => 'valorant', 'tft' => 'teamfight-tactics'];
                // Every boosting game a girl can pick in her dashboard, so newly added
                // games get the right label/icon here too.
                $gglGameOpts = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
                foreach ($gglGameOpts as $gglKey => $gglOpt) {
                    if (!isset($gameLabels[$gglKey])) $gameLabels[$gglKey] = $gglOpt['label'];
                    if (!isset($gameIconMap[$gglKey])) $gameIconMap[$gglKey] = $gglOpt['slug'];
                }
                $mainGame = $gameList[0] ?? 'lol';
                $mainGameLabel = $gameLabels[$mainGame] ?? strtoupper($mainGame);
                $rankByGame = function_exists('lb_egirl_game_ranks') ? lb_egirl_game_ranks($egirl) : [];
                $mainRank = trim((string)($rankByGame[$mainGame] ?? $egirl['lol_rank'] ?? ''));
                if ($mainRank === '') $mainRank = 'Unranked';
                $langs = array_values(array_filter(array_map('trim', explode('|', (string)($egirl['languages'] ?? '')))));
                $langNames = ['en'=>'English','de'=>'German','fr'=>'French','es'=>'Spanish','tr'=>'Turkish','pt'=>'Portuguese','it'=>'Italian','pl'=>'Polish','ru'=>'Russian','nl'=>'Dutch','sv'=>'Swedish','da'=>'Danish','no'=>'Norwegian','fi'=>'Finnish','cs'=>'Czech','ro'=>'Romanian','hu'=>'Hungarian','uk'=>'Ukrainian','ar'=>'Arabic','zh'=>'Chinese','ja'=>'Japanese','ko'=>'Korean'];
                $timezone = trim((string)($egirl['timezone'] ?? ''));
                if ($timezone === '') $timezone = 'Europe/Berlin';
            ?>
                <label class="ggl-girl"
                       data-name="<?= htmlspecialchars($egirl['username'], ENT_QUOTES, 'UTF-8') ?>"
                       data-avatar="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>"
                       data-game="<?= htmlspecialchars($mainGameLabel, ENT_QUOTES, 'UTF-8') ?>"
                       data-rank="<?= htmlspecialchars($mainRank, ENT_QUOTES, 'UTF-8') ?>"
                       data-timezone="<?= htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8') ?>"
                       data-languages="<?= htmlspecialchars(implode(', ', array_map(function($lc) use ($langNames) { return $langNames[$lc] ?? strtoupper($lc); }, $langs)), ENT_QUOTES, 'UTF-8') ?>"
                       data-voice="<?= !empty($egirl['voice_chat']) ? '1' : '0' ?>">
                    <input type="radio" name="egirl_choice" value="<?= (int)$egirl['id'] ?>">
                    <span class="ggl-avatar"><img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($egirl['username'], ENT_QUOTES, 'UTF-8') ?>"></span>
                    <span class="ggl-girl-copy">
                        <span class="ggl-girl-head">
                            <strong><?= htmlspecialchars($egirl['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= !empty($egirl['is_online']) ? t('Online now') : t('Available') ?><?= $rating > 0 ? ' · ' . number_format($rating, 1) . '★' : '' ?></small>
                        </span>
                        <span class="ggl-girl-tags">
                            <?php foreach (array_slice($langs, 0, 3) as $lc): ?>
                                <span><?= htmlspecialchars(strtoupper($lc), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                            <span><?= htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                        <span class="ggl-girl-info">
                            <span><i class="fa-solid fa-globe"></i><b class="ggl-js-server">EUW</b></span>
                            <span>
                                <?php if (isset($gameIconMap[$mainGame])): ?><img src="<?= ASSET_URL ?>/website/images/icons/<?= $gameIconMap[$mainGame] ?>.png" alt=""><?php else: ?><i class="fa-solid fa-gamepad"></i><?php endif; ?>
                                <?= htmlspecialchars($mainGameLabel, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span><i class="fa-solid fa-trophy"></i><?= htmlspecialchars($mainRank, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (!empty($egirl['voice_chat'])): ?><span><i class="fa-solid fa-microphone"></i><?= t('Voice') ?></span><?php endif; ?>
                        </span>
                    </span>
                </label>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ggl-empty"><?= t('No GGirls are available right now.') ?></div>
        <?php endif; ?>
    </div>

    </div>
</div>

<style>
/* ════════════════════════════════════════════════════════════════
   GGirls boost form — pink/purple theme (matches the GGirl profile
   page colors: #a855f7 → #ec4899 / #f472b6 on dark purple backgrounds)

   NOTE on sizing: the site renders at 0.88 browser zoom, so every
   font/icon size below is intentionally larger than it looks in a
   plain 100%-zoom preview — that's by design, to compensate.
   ════════════════════════════════════════════════════════════════ */

/* Lock background scrolling while any "select your GGirl"-style
   overlay is open — see the JS block at the bottom for how this
   class gets toggled. Works no matter which template renders the
   overlay markup. */
html.ggl-noscroll, body.ggl-noscroll { overflow: hidden !important; height: 100% !important; }

.ggirl-boost-form{
    --ggl-pink:#ec4899;
    --ggl-pink-soft:#f472b6;
    --ggl-purple:#a855f7;
    --ggl-purple-soft:#c084fc;
    --ggl-bg-1:#150726;
    --ggl-bg-2:#1f0a3a;
    --ggl-line:rgba(168,85,247,.20);

    width:100%;height:100%;min-height:0;display:flex;flex-direction:column;
    color:#fff;
    background:linear-gradient(165deg,var(--ggl-bg-1) 0%,var(--ggl-bg-2) 100%);
    border:.07vw solid var(--ggl-line);
    border-radius:1.5vw;
    padding:1.8vw;
    box-shadow:0 .6vw 2.6vw rgba(168,85,247,.10), inset 0 0 0 .052vw rgba(255,255,255,.02);
}

/* ── Hero ── */
.ggl-hero{display:flex;align-items:center;gap:1vw;margin:0 0 1.4vw;padding:0 0 1.2vw;border-bottom:.06vw solid rgba(255,255,255,.07);}
.ggl-hero-icon{width:3.1vw;height:3.1vw;flex-shrink:0;border-radius:.75vw;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(168,85,247,.35),rgba(236,72,153,.22));border:.05vw solid rgba(168,85,247,.4);box-shadow:0 0 1.1vw rgba(168,85,247,.25);}
.ggl-hero-icon img{width:1.85vw;height:1.85vw;object-fit:contain;}
.ggl-hero h3{margin:0 0 .3vw;font-size:1.85vw;line-height:1.15;font-weight:900;color:#fff;}
.ggl-hero p{margin:0;max-width:none;font-size:.92vw;line-height:1.4;font-weight:600;color:rgba(255,255,255,.5);}
.ggl-title{display:none;}

/* ── Mode list — header row always visible, fields expand inline
   below the header row inside whichever card is active ── */
.ggl-modes{display:flex;flex-direction:column;gap:.7vw;flex:0 0 auto;margin-bottom:.9vw;}
.ggl-mode{
    position:relative;width:100%;display:flex;flex-direction:column;
    border-radius:1vw;padding:1.15vw 1.3vw;
    background:rgba(255,255,255,.03);border:.06vw solid rgba(255,255,255,.07);
    cursor:pointer;transition:border-color .18s ease,background .18s ease,box-shadow .18s ease;
}
.ggl-mode:hover{border-color:rgba(236,72,153,.4);background:rgba(168,85,247,.06);}
.ggl-mode.active{
    border-color:var(--ggl-purple);
    background:linear-gradient(135deg,rgba(168,85,247,.16),rgba(236,72,153,.08));
    box-shadow:0 0 0 .07vw rgba(168,85,247,.25),0 .5vw 1.5vw rgba(168,85,247,.12);
}
.ggl-mode input{position:absolute;opacity:0;pointer-events:none;}

.ggl-mode-row{display:flex;align-items:center;gap:1vw;width:100%;}

.ggl-mode-radio{
    width:1.4vw;height:1.4vw;flex:0 0 1.4vw;border-radius:50%;position:relative;
    border:.13vw solid rgba(255,255,255,.22);transition:border-color .18s ease;
}
.ggl-mode-radio::after{
    content:'';position:absolute;inset:.24vw;border-radius:50%;
    background:linear-gradient(135deg,var(--ggl-purple),var(--ggl-pink));
    transform:scale(0);transition:transform .18s ease;
}
.ggl-mode.active .ggl-mode-radio{border-color:var(--ggl-purple);}
.ggl-mode.active .ggl-mode-radio::after{transform:scale(1);}

.ggl-mode-icon{
    width:3.1vw;height:3.1vw;flex:0 0 3.1vw;border-radius:.8vw;
    display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.05);border:.05vw solid rgba(255,255,255,.08);
    transition:background .18s ease,border-color .18s ease;
}
.ggl-mode.active .ggl-mode-icon{background:rgba(168,85,247,.22);border-color:rgba(168,85,247,.4);}
.ggl-mode-icon i{font-size:1.5vw;color:var(--ggl-pink-soft);}

.ggl-mode-copy{display:flex;flex-direction:column;min-width:0;flex:1 1 auto;}
.ggl-mode-copy strong{display:block;font-size:1.45vw;font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ggl-mode-copy small{display:block;margin-top:.25vw;font-size:1.05vw;font-weight:600;line-height:1.3;color:rgba(255,255,255,.5);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

.ggl-price{
    margin-left:auto;flex:0 0 auto;text-align:right;
    display:flex;flex-direction:column;align-items:flex-end;line-height:1.15;
}
.ggl-price-amount{font-size:1.5vw;font-weight:900;color:var(--ggl-pink-soft);white-space:nowrap;}
.ggl-price-suffix{margin-top:.16vw;font-size:1vw;font-weight:700;color:rgba(255,255,255,.45);white-space:nowrap;}
.ggl-mode.active .ggl-price-suffix{color:var(--ggl-purple-soft);}



/* Mobile width guard: mode cards must stay inside the form column. */
@media (max-width: 767px){
    .ggirl-boost-form{padding:18px!important;max-width:100%!important;overflow:visible!important;}
    .ggl-modes,.ggl-mode{width:100%!important;max-width:100%!important;min-width:0!important;box-sizing:border-box!important;overflow:hidden!important;}
    .ggl-mode{padding:13px 14px!important;border-radius:14px!important;}
    .ggl-mode-row{width:100%!important;min-width:0!important;gap:10px!important;}
    .ggl-mode-radio{width:18px!important;height:18px!important;flex-basis:18px!important;}
    .ggl-mode-radio::after{inset:4px!important;}
    .ggl-mode-icon{width:40px!important;height:40px!important;flex-basis:40px!important;border-radius:11px!important;}
    .ggl-mode-icon i{font-size:16px!important;}
    .ggl-mode-copy{min-width:0!important;flex:1 1 0!important;}
    .ggl-mode-copy strong{font-size:14px!important;}
    .ggl-mode-copy small{font-size:11px!important;}
    .ggl-price{max-width:64px!important;min-width:0!important;flex:0 1 64px!important;}
    .ggl-price-amount{font-size:14px!important;}
    .ggl-price-suffix{font-size:10px!important;}
    .ggl-inline-config,.ggl-inline-fields,.ggl-inline-field,.ggl-drop,.ggl-drop-trigger,.ggl-stepper{max-width:100%!important;min-width:0!important;box-sizing:border-box!important;}
}

/* ── Inline session settings — appear inside the active card only ── */
.ggl-mode:not(.active) .ggl-inline-config{display:none!important;}
.ggl-inline-config{
    width:100%;margin-top:1vw;padding-top:1vw;
    border-top:.05vw solid rgba(168,85,247,.18);
    overflow:visible;
}
.ggl-inline-fields{display:grid;grid-template-columns:1fr 1fr 1.05fr;gap:.85vw;}
.ggl-inline-field h6{margin:0 0 .5vw;font-size:.85vw;font-weight:900;letter-spacing:.06em;text-transform:uppercase;color:var(--ggl-purple-soft);}
.ggl-inline-games{display:flex;flex-direction:column;align-items:stretch;}

/* ── Custom dropdowns ("ggl-drop") for Server & Rank ── */
.ggl-drop{position:relative;user-select:none;}
.ggl-drop-trigger{
    width:100%;display:flex;align-items:center;gap:.6vw;
    background:#150a26;border:.06vw solid rgba(168,85,247,.3);border-radius:.85vw;
    padding:.75vw .9vw;cursor:pointer;
    transition:border-color .18s ease,background .18s ease,box-shadow .18s ease;
}
.ggl-drop-trigger:hover{border-color:rgba(236,72,153,.45);}
.ggl-drop.open .ggl-drop-trigger{
    border-color:var(--ggl-pink);
    background:#1c0d33;
    box-shadow:0 0 0 .08vw rgba(168,85,247,.18);
}
.ggl-drop-icon{
    flex:0 0 auto;width:2.1vw;height:2.1vw;display:flex;align-items:center;justify-content:center;
    font-size:1.1vw;color:var(--ggl-pink-soft);border-radius:.55vw;
    background:#0c0518;border:.05vw solid rgba(168,85,247,.3);
}
.ggl-drop-icon img{width:1.3vw;height:1.3vw;object-fit:contain;}
.ggl-drop-text{flex:1;min-width:0;font-size:1.2vw;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ggl-drop-arrow{font-size:.86vw;color:rgba(255,255,255,.45);flex-shrink:0;transition:transform .2s ease,color .2s ease;}
.ggl-drop.open .ggl-drop-arrow{transform:rotate(180deg);color:var(--ggl-pink-soft);}
.ggl-drop-list{
    display:none;position:absolute;top:calc(100% + .4vw);left:0;right:0;z-index:60;
    background:#170a2a;
    border:.06vw solid rgba(168,85,247,.35);border-radius:.85vw;
    max-height:14vw;overflow-y:auto;
    box-shadow:0 .8vw 2.4vw rgba(0,0,0,.5),0 0 0 .052vw rgba(168,85,247,.1);
}
.ggl-drop.open .ggl-drop-list{display:block;}
.ggl-drop-opt{
    display:flex;align-items:center;gap:.65vw;padding:.8vw .9vw;
    font-size:1.1vw;font-weight:700;color:rgba(255,255,255,.7);cursor:pointer;
    border-bottom:.05vw solid rgba(168,85,247,.1);
    transition:background .15s ease,color .15s ease;
}
.ggl-drop-opt:last-child{border-bottom:none;}
.ggl-drop-opt i,.ggl-drop-opt img{width:1.3vw;flex-shrink:0;font-size:1.15vw;color:var(--ggl-purple-soft);}
.ggl-drop-opt img{height:1.3vw;object-fit:contain;}
.ggl-drop-opt:hover{background:rgba(168,85,247,.12);color:#fff;}
.ggl-drop-opt.active{background:rgba(236,72,153,.12);color:#fff;}
.ggl-drop-opt.active i,.ggl-drop-opt.active img{color:var(--ggl-pink-soft);}
.ggl-drop-opt-modifier{margin-left:auto;flex-shrink:0;font-size:.92vw;font-weight:900;color:var(--ggl-pink-soft);}
.ggl-drop-list::-webkit-scrollbar{width:.3vw;}
.ggl-drop-list::-webkit-scrollbar-track{background:rgba(168,85,247,.05);}
.ggl-drop-list::-webkit-scrollbar-thumb{background:rgba(168,85,247,.4);border-radius:999px;}

/* ── Games stepper ── */
.ggl-stepper-pill{
    height:3.6vw;display:flex;align-items:center;justify-content:center;gap:.85vw;
    border-radius:.85vw;
    background:#150a26;border:.06vw solid rgba(168,85,247,.3);
}
.ggl-stepper-pill input{display:none;}
.ggl-stepper-center{min-width:3.9vw;display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1;text-align:center;}
.ggl-stepper-center strong{font-size:1.9vw;font-weight:900;color:#fff;line-height:1;}
.ggl-stepper-center small{margin-top:.25vw;font-size:.88vw;font-weight:700;color:rgba(255,255,255,.5);text-transform:lowercase;}
.ggl-step{
    width:2.5vw;height:2.5vw;border-radius:50%;
    border:none;background:linear-gradient(135deg,var(--ggl-purple),var(--ggl-pink));
    color:#fff;font-size:1.45vw;font-weight:900;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 .2vw .6vw rgba(168,85,247,.25);
    transition:opacity .15s ease,box-shadow .15s ease,transform .15s ease;
}
.ggl-step:hover{opacity:.9;box-shadow:0 .35vw 1.1vw rgba(236,72,153,.42);transform:translateY(-1px);}

/* ── Empty state ── */
.ggl-empty{grid-column:1 / -1;padding:1vw;border-radius:.9vw;background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);font-weight:700;text-align:center;font-size:.95vw;}

/* ── Hidden data source (girls). Stays hidden — selection happens
   through the "select your GGirl" overlay rendered elsewhere — but
   restyled in case its markup gets reused/cloned for that overlay. ── */
.ggl-girls-source{display:none!important;}
.ggl-girls{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8vw;}
.ggl-girl{
    position:relative;min-height:5.8vw;border-radius:.9vw;
    border:.06vw solid rgba(168,85,247,.16);background:rgba(255,255,255,.03);
    display:flex;align-items:flex-start;gap:.8vw;padding:.85vw;cursor:pointer;
    transition:border-color .18s ease,background .18s ease,box-shadow .18s ease;
}
.ggl-girl:hover{border-color:rgba(236,72,153,.45);background:rgba(168,85,247,.07);}
.ggl-girl.active{
    border-color:var(--ggl-purple);
    background:linear-gradient(135deg,rgba(168,85,247,.18),rgba(236,72,153,.1));
    box-shadow:0 0 0 .052vw rgba(168,85,247,.3);
}
.ggl-avatar{width:3.05vw;height:3.05vw;flex:0 0 3.05vw;border-radius:50%;overflow:hidden;background:rgba(255,255,255,.05);}
.ggl-avatar img{width:100%;height:100%;object-fit:cover;}
.ggl-girl-copy{display:flex;flex-direction:column;min-width:0;gap:.42vw;flex:1;}
.ggl-girl-head{display:flex;flex-direction:column;min-width:0;}
.ggl-girl-copy strong{font-size:1vw;font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ggl-girl-copy small{margin-top:.18vw;font-size:.78vw;font-weight:600;color:rgba(255,255,255,.45);}
.ggl-girl-tags{display:flex;flex-wrap:wrap;gap:.28vw;}
.ggl-girl-tags span{display:inline-flex;align-items:center;padding:.16vw .42vw;border-radius:999px;background:rgba(255,255,255,.055);border:.052vw solid rgba(255,255,255,.08);font-size:.64vw;font-weight:850;color:rgba(255,255,255,.62);}
.ggl-girl-info{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.34vw .45vw;}
.ggl-girl-info span{display:flex;align-items:center;gap:.28vw;min-width:0;font-size:.72vw;font-weight:760;color:rgba(255,255,255,.72);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ggl-girl-info i{color:var(--ggl-purple-soft);font-size:.72vw;flex:0 0 auto;}
.ggl-girl-info img{width:.85vw;height:.85vw;object-fit:contain;flex:0 0 auto;}

/* ════════════════════════════════════════════════════════════════
   Page layout — two-column grid (left card / right order summary)
   ════════════════════════════════════════════════════════════════ */
.boost-form.ggirls-page>.form-content{display:grid!important;grid-template-columns:minmax(0,1.42fr) minmax(360px,.98fr)!important;gap:1.2vw!important;align-items:start!important;}
.boost-form.ggirls-page>.form-content>.left,
.boost-form.ggirls-page>.form-content>.right{height:auto!important;align-self:start!important;}
.boost-form.ggirls-page .summary-wrapper,
.boost-form.ggirls-page .order-summary{height:auto!important;min-height:0!important;}
.boost-form.ggirls-page #discount-box,
.boost-form.ggirls-page #discount-input,
.boost-form.ggirls-page .discount-box,
.boost-form.ggirls-page .discount-input{display:none!important;}

/* Safety net against horizontal overflow on narrow screens, no
   matter what causes it (the right column has a 360px floor, so
   between roughly 800–1300px wide it can squeeze the left column or
   overflow before the stacked layout below kicks in). */
.ggirl-boost-form,
.ggirl-boost-form *{box-sizing:border-box;}
.ggirl-boost-form{max-width:100%;overflow:visible!important;}
.ggl-mode,.ggl-inline-config,.ggl-inline-fields,.ggl-inline-field,.ggl-drop{overflow:visible!important;}
.ggl-drop-list{z-index:99999!important;}

/* Collapse to a single column well before that squeeze zone — and
   do it via :has() too (not just the .ggirls-page class that's only
   added by JS) so it applies immediately on first paint, with no
   dependency on script timing. */
@media(max-width:1300px){
    .boost-form.ggirls-page>.form-content,
    .form-content:has(#ggirl_lol_form_box){display:block!important}
    .boost-form.ggirls-page>.form-content>.left,
    .boost-form.ggirls-page>.form-content>.right,
    .form-content:has(#ggirl_lol_form_box)>.left,
    .form-content:has(#ggirl_lol_form_box)>.right{width:100%!important;max-width:100%!important;}
}

/* ════════════════════════════════════════════════════════════════
   Best-effort pink skin for the "select your GGirl" overlay.
   NOTE: this overlay's HTML is rendered by another template that
   wasn't part of this file — these rules target it by the class
   names ggirls.php already relies on (.ggl-summary-*).
   ════════════════════════════════════════════════════════════════ */
.ggl-summary-overlay{
    align-items:center!important;justify-content:center!important;padding:24px!important;
    z-index:2147483647!important;
    background:rgba(8,2,18,.78)!important;backdrop-filter:blur(8px)!important;
}
.ggl-summary-panel{
    margin-top:0!important;
    max-width:min(920px,calc(100vw - 32px))!important;
    background:linear-gradient(165deg,#150726 0%,#1f0a3a 100%)!important;
    border:1px solid rgba(168,85,247,.3)!important;
    border-radius:18px!important;
    box-shadow:0 24px 70px rgba(0,0,0,.55),0 0 0 1px rgba(236,72,153,.06)!important;
}
.ggl-summary-girl{border-color:rgba(168,85,247,.18)!important;background:rgba(255,255,255,.03)!important;}
.ggl-summary-girl:hover{border-color:rgba(236,72,153,.4)!important;background:rgba(168,85,247,.07)!important;}
.ggl-summary-girl.active{
    border-color:#a855f7!important;
    background:linear-gradient(135deg,rgba(168,85,247,.18),rgba(236,72,153,.1))!important;
    box-shadow:0 0 0 1px rgba(168,85,247,.3)!important;
}

.ggl-summary-overlay .ggl-girls{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;}
.ggl-summary-overlay .ggl-girl,.ggl-summary-overlay .ggl-summary-girl{min-height:88px!important;padding:12px!important;align-items:flex-start!important;}
.ggl-summary-overlay .ggl-avatar{width:46px!important;height:46px!important;flex-basis:46px!important;}
.ggl-summary-overlay .ggl-girl-copy{gap:6px!important;}
.ggl-summary-overlay .ggl-girl-copy strong{font-size:14px!important;}
.ggl-summary-overlay .ggl-girl-copy small{font-size:11px!important;}
.ggl-summary-overlay .ggl-girl-tags span{font-size:10px!important;padding:3px 7px!important;}
.ggl-summary-overlay .ggl-girl-info{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:5px 8px!important;}
.ggl-summary-overlay .ggl-girl-info span{font-size:11px!important;}
.ggl-summary-overlay .ggl-girl-info i{font-size:11px!important;}
.ggl-summary-overlay .ggl-girl-info img{width:13px!important;height:13px!important;}
@media(max-width:640px){.ggl-summary-overlay .ggl-girls{grid-template-columns:1fr!important;}.ggl-summary-panel{max-width:calc(100vw - 18px)!important;}}

@media(max-width:991px){
    .boost-form.ggirls-page>.form-content{display:block!important}
    .ggirl-boost-form{border-radius:22px!important;padding:18px!important}
    .ggl-hero{margin-bottom:16px!important;padding-bottom:14px!important}
    .ggl-hero-icon{width:48px!important;height:48px!important;border-radius:12px!important}
    .ggl-hero-icon img{width:30px!important;height:30px!important}
    .ggl-hero h3{font-size:23px!important}
    .ggl-hero p{font-size:13px!important}
    .ggl-mode{border-radius:14px!important;padding:14px!important}
    .ggl-mode-radio{width:19px!important;height:19px!important;flex-basis:19px!important}
    .ggl-mode-radio::after{inset:3px!important}
    .ggl-mode-icon{width:42px!important;height:42px!important;flex-basis:42px!important;border-radius:10px!important}
    .ggl-mode-icon i{font-size:18px!important}
    .ggl-mode-copy strong{font-size:15px!important}
    .ggl-mode-copy small{font-size:12px!important}
    .ggl-price-amount{font-size:16px!important}
    .ggl-price-suffix{font-size:11px!important}
    .ggl-inline-fields{grid-template-columns:1fr!important;gap:12px!important}
    .ggl-inline-field h6{font-size:11.5px!important}
    .ggl-drop-trigger{padding:13px 14px!important;border-radius:10px!important}
    .ggl-drop-text{font-size:15px!important}
    .ggl-drop-icon{width:30px!important;height:30px!important;font-size:14px!important;border-radius:9px!important}
    .ggl-drop-list{max-height:230px!important;border-radius:12px!important}
    .ggl-drop-opt{padding:13px 14px!important;font-size:14px!important}
    .ggl-drop-opt-modifier{font-size:12px!important}
    .ggl-stepper-pill{height:54px!important;border-radius:12px!important}
    .ggl-step{width:38px!important;height:38px!important;font-size:19px!important}
    .ggl-stepper-center strong{font-size:23px!important}
    .ggl-stepper-center small{font-size:11.5px!important}
    .ggl-girls{grid-template-columns:1fr!important}
}
</style>

<script>
(function(){
    var form = document.getElementById('lol_boost_form');
    var box = document.getElementById('ggirl_lol_form_box');
    if(!form || !box) return;
    form.classList.add('ggirls-page');
    var rate = parseFloat(box.getAttribute('data-rate') || '1') || 1;
    var currency = box.getAttribute('data-currency') || 'EUR';
    var queueInput = document.getElementById('ggirl_queue_type');
    var priceInput = document.getElementById('ggirl_price_cents');
    var egirlInput = document.getElementById('selected_egirl_id');
    var assignmentInput = document.getElementById('ggirl_assignment');
    var amountInput = document.getElementById('ggirl_amount');
    var amountDisplay = document.getElementById('ggl_amount_display');
    var amountUnit = document.getElementById('ggl_amount_unit');
    var amountLabel = document.getElementById('ggl_amount_label');
    var rankSelect = document.getElementById('ggirl_rank_select');
    var rankInput = document.getElementById('ggirl_rank');
    var inlineConfig = document.getElementById('ggl_inline_config');

    /* The Server/Rank/Games block is a single DOM node that gets
       moved into whichever mode card is currently active — this is
       what makes the fields "appear inside the clicked card". */
    function placeInlineConfig(){
        var active = selectedMode();
        if(active && inlineConfig && inlineConfig.parentNode !== active){ active.appendChild(inlineConfig); }
    }

    /* money() always respects the page's active currency/rate (set
       server-side from the session currency) — every price shown,
       whether rendered on load or updated live, goes through this
       same helper, so EUR/USD stay correct everywhere. */
    function money(cents){ var value = (parseInt(cents || 0,10) / 100) * rate; return (currency === 'USD' ? '$' : '€') + value.toFixed(2); }
    function selectedMode(){ return box.querySelector('.ggl-mode.active') || box.querySelector('.ggl-mode'); }
    function selectedGirl(){
        /* selected_egirl_id is the source of truth. Empty means Any Available,
           even if another script/browser state accidentally leaves a source card active. */
        if(!egirlInput || String(egirlInput.value || '').trim() === '') return null;
        var modalActive = document.querySelector('.ggl-summary-girl.active');
        if(modalActive && !modalActive.classList.contains('ggl-summary-any')) return modalActive;
        var sourceActive = box.querySelector('.ggl-girl.active');
        return sourceActive || null;
    }
    function rankModifierCents(){
        var opt = document.querySelector('#ggl_rank_drop .ggl-drop-opt.active');
        return opt ? (parseInt(opt.getAttribute('data-modifier-cents') || '0', 10) || 0) : 0;
    }
    function current(){
        var mode = selectedMode();
        var baseCents = mode ? parseInt(mode.getAttribute('data-price-cents') || '0',10) : 0;
        var modifierCents = rankModifierCents();
        var cents = Math.max(0, baseCents + modifierCents);
        var amount = Math.max(1, Math.min(20, parseInt(amountInput && amountInput.value ? amountInput.value : '1',10) || 1));
        return {mode:mode,baseCents:baseCents,modifierCents:modifierCents,cents:cents,amount:amount,total:cents * amount};
    }
    function setText(id, text){ var el = document.getElementById(id); if(el) el.textContent = text; }

    /* Keep every mode row's price tag in sync: the active row shows
       the live total (unit price × games/sessions), every other row
       shows its plain per-unit price. Both include the selected
       rank's price surcharge (if any), since rank is one global
       choice that applies no matter which mode ends up selected. */
    function updateModePrices(c){
        box.querySelectorAll('.ggl-mode').forEach(function(m){
            var cents = Math.max(0, parseInt(m.getAttribute('data-price-cents') || '0', 10) + c.modifierCents);
            var suffix = m.getAttribute('data-unit-suffix') || 'game';
            var amountEl = m.querySelector('.ggl-price-amount');
            var suffixEl = m.querySelector('.ggl-price-suffix');
            if(!amountEl || !suffixEl) return;
            if(m.classList.contains('active')){
                amountEl.textContent = money(cents * c.amount);
                suffixEl.textContent = '× ' + c.amount + ' ' + suffix + (c.amount > 1 ? 's' : '');
            } else {
                amountEl.textContent = money(cents);
                suffixEl.textContent = '/' + suffix;
            }
        });
    }

    function updateSummary(){
        var c = current();
        var girl = selectedGirl();
        var modeTitle = c.mode ? (c.mode.getAttribute('data-title') || '') : '';
        var unit = c.mode ? (c.mode.getAttribute('data-unit') || 'Games') : 'Games';
        var girlName = girl ? (girl.getAttribute('data-name') || '') : 'Any Available';
        var avatar = girl ? (girl.getAttribute('data-avatar') || '') : '';
        var rankText = rankSelect && rankSelect.options[rankSelect.selectedIndex] ? rankSelect.options[rankSelect.selectedIndex].text : 'Unranked';
        var serverTextEl = document.querySelector('#ggl_server_trigger .ggl-drop-text');
        var serverText = serverTextEl ? serverTextEl.textContent.trim() : 'EUW';
        document.querySelectorAll('.ggl-js-server').forEach(function(el){ el.textContent = serverText; });
        if(rankInput && rankSelect) rankInput.value = rankSelect.value;
        if(priceInput) priceInput.value = String(c.cents);
        if(amountLabel) amountLabel.textContent = unit;
        if(amountDisplay) amountDisplay.textContent = String(c.amount);
        if(amountUnit) amountUnit.textContent = (unit || 'Games').toLowerCase();

        updateModePrices(c);

        setText('total-price', money(c.total));
        setText('sticky-total-price', money(c.total));
        setText('new-price', money(c.total));
        setText('saved-price', money(0));
        setText('completion-time', c.amount + ' ' + unit);
        setText('ggl-summary-title', modeTitle);
        setText('ggl-summary-sub', rankText + ' · ' + c.amount + ' ' + unit);
        setText('ggl-sticky-summary-title', modeTitle);
        setText('ggl-sticky-summary-sub', rankText + ' · ' + c.amount + ' ' + unit);
        var modeIcon = document.getElementById('ggl-summary-mode-icon');
        if(modeIcon && c.mode){ modeIcon.className = c.mode.getAttribute('data-icon') || 'fa-solid fa-gamepad'; }
        var stickyModeIcon = document.getElementById('ggl-sticky-summary-mode-icon');
        if(stickyModeIcon && c.mode){ stickyModeIcon.className = c.mode.getAttribute('data-icon') || 'fa-solid fa-gamepad'; }
        var img = document.getElementById('ggl-summary-avatar');
        if(img && avatar) img.src = avatar;
        var old = document.getElementById('old-price'); if(old){ old.textContent = ''; old.style.display = 'none'; }
        var stickyOld = document.getElementById('sticky-old-price'); if(stickyOld) stickyOld.style.display = 'none';
        var cashback = document.getElementById('cashback_amount');
        var cashbackBox = document.querySelector('.cashback_info');
        if(cashback && cashbackBox){
            var percent = parseFloat(cashbackBox.getAttribute('data-cashback-percent') || '2') || 2;
            cashback.textContent = money(Math.round(c.total * percent / 100));
        }
        window.dispatchEvent(new CustomEvent('ggirls:update', {detail:{totalPrice:c.total, mode:modeTitle, girl:girlName, rank:rankText, amount:c.amount, unit:unit}}));
    }
    box.querySelectorAll('.ggl-mode').forEach(function(mode){
        mode.addEventListener('click', function(){
            box.querySelectorAll('.ggl-mode').forEach(function(m){m.classList.remove('active');});
            mode.classList.add('active');
            var radio = mode.querySelector('input[type="radio"]');
            if(radio){ radio.checked = true; if(queueInput) queueInput.value = radio.value; }
            placeInlineConfig();
            updateSummary();
        });
    });
    box.querySelectorAll('.ggl-girl').forEach(function(girl){
        girl.addEventListener('click', function(){
            box.querySelectorAll('.ggl-girl').forEach(function(g){g.classList.remove('active');});
            girl.classList.add('active');
            var radio = girl.querySelector('input[type="radio"]');
            if(radio){ radio.checked = true; if(egirlInput) egirlInput.value = radio.value; }
            if(assignmentInput) assignmentInput.value = 'selected';
            updateSummary();
        });
    });
    if(amountInput) amountInput.addEventListener('input', updateSummary);
    window.addEventListener('ggirls-summary-select', updateSummary);
    document.addEventListener('DOMContentLoaded', function(){ updateSummary(); setTimeout(updateSummary, 50); setTimeout(updateSummary, 250); setTimeout(updateSummary, 700); });
    if(rankSelect) rankSelect.addEventListener('change', updateSummary);
    box.querySelectorAll('.ggl-step').forEach(function(btn){
        btn.addEventListener('click', function(ev){
            ev.preventDefault(); ev.stopPropagation();
            var step = parseInt(btn.getAttribute('data-step') || '0', 10) || 0;
            var val = Math.max(1, Math.min(20, (parseInt(amountInput.value || '1', 10) || 1) + step));
            amountInput.value = String(val);
            amountInput.dispatchEvent(new Event('input', {bubbles:true}));
            updateSummary();
        });
    });
    if(inlineConfig){ inlineConfig.addEventListener('click', function(ev){ ev.stopPropagation(); }); }

    /* ── Custom dropdowns (server / rank) — keeps the underlying
       hidden <select> in sync so all existing logic above keeps working ── */
    function ggInitDrop(id){
        var drop = document.getElementById(id);
        if(!drop) return;
        var trigger = drop.querySelector('.ggl-drop-trigger');
        var list = drop.querySelector('.ggl-drop-list');
        var select = drop.querySelector('select');
        var textEl = drop.querySelector('.ggl-drop-text');
        var iconEl = drop.querySelector('.ggl-drop-trigger .ggl-drop-icon');
        if(!trigger || !list) return;
        function closeAll(){ document.querySelectorAll('.ggl-drop.open').forEach(function(d){ d.classList.remove('open'); }); }
        trigger.addEventListener('click', function(ev){
            ev.preventDefault(); ev.stopPropagation();
            var wasOpen = drop.classList.contains('open');
            closeAll();
            if(!wasOpen) drop.classList.add('open');
        });
        list.addEventListener('click', function(ev){ ev.stopPropagation(); });
        list.querySelectorAll('.ggl-drop-opt').forEach(function(opt){
            opt.addEventListener('click', function(){
                list.querySelectorAll('.ggl-drop-opt').forEach(function(o){ o.classList.remove('active'); });
                opt.classList.add('active');
                if(textEl){ var span = opt.querySelector('span'); textEl.textContent = span ? span.textContent : opt.textContent.trim(); }
                if(iconEl){ var src = opt.querySelector('i, img'); iconEl.innerHTML = src ? src.outerHTML : ''; }
                if(select){
                    select.value = opt.getAttribute('data-value') || '';
                    select.dispatchEvent(new Event('change', {bubbles:true}));
                }
                drop.classList.remove('open');
            });
        });
        document.addEventListener('click', closeAll);
        document.addEventListener('keydown', function(ev){ if(ev.key === 'Escape') closeAll(); });
    }
    ggInitDrop('ggl_server_drop');
    ggInitDrop('ggl_rank_drop');

    /* Default selection is "Any Available" (random) — handled by the
       order-summary picker. We intentionally do NOT auto-select the
       first source girl here anymore. */
    placeInlineConfig();
    updateSummary();

    /* Make the GGirls checkout own the Buy Now click before the global lol.js handler can block it. */
    ['start_boost', 'sticky_start_boost'].forEach(function(btnId){
        var buyBtn = document.getElementById(btnId);
        if(!buyBtn) return;
        buyBtn.removeAttribute('disabled');
        buyBtn.addEventListener('click', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            if(form.requestSubmit){
                form.requestSubmit();
            }else{
                form.dispatchEvent(new Event('submit', {bubbles:true, cancelable:true}));
            }
        }, true);
    });

    form.addEventListener('submit', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        /* An empty selected_egirl_id is valid — it means "Any Available". */
        if(egirlInput && String(egirlInput.value || '').trim() === '' && assignmentInput){ assignmentInput.value = 'any_available'; }
        var btn = document.getElementById('start_boost') || form.querySelector('[type="submit"]');
        if(btn){ btn.disabled = true; btn.dataset.oldText = btn.innerHTML; btn.innerHTML = 'Loading...'; }
        var fd = new FormData(form);
        fd.set('action','egirl_book_lol_boost');
        fd.set('selected_egirl_id', egirlInput ? String(egirlInput.value || '').trim() : '');
        fd.set('ggirl_assignment', (egirlInput && String(egirlInput.value || '').trim() !== '') ? 'selected' : 'any_available');
        fetch(form.getAttribute('action'), {method:'POST', body:fd, credentials:'same-origin'})
            .then(function(r){return r.text();})
            .then(function(t){var res; try{res=JSON.parse(t);}catch(e){res={};} if(res.redirectUrl){window.location.href=res.redirectUrl;return;} alert((res.sendToast&&res.sendToast.message)?res.sendToast.message:'Could not create booking.');})
            .catch(function(){ alert('Could not create booking.'); })
            .finally(function(){ if(btn){ btn.disabled=false; btn.innerHTML=btn.dataset.oldText || 'Buy Now'; }});
        return false;
    }, true);
})();

/* ── Background scroll-lock for the "select your GGirl" overlay ──
   File-agnostic on purpose: instead of hooking a specific open/close
   function we just watch whether that element is actually visible
   and (un)lock html/body scrolling accordingly. */
(function(){
    var LOCK_CLASS = 'ggl-noscroll';
    function isVisible(el){
        if(!el) return false;
        var cs = window.getComputedStyle(el);
        if(cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity || '1') === 0) return false;
        var r = el.getBoundingClientRect();
        return r.width > 0 && r.height > 0;
    }
    function sync(){
        var overlay = document.querySelector('.ggl-summary-overlay');
        var open = isVisible(overlay);
        document.documentElement.classList.toggle(LOCK_CLASS, open);
        document.body.classList.toggle(LOCK_CLASS, open);
    }
    var scheduled = false;
    function scheduleSync(){
        if(scheduled) return;
        scheduled = true;
        requestAnimationFrame(function(){ scheduled = false; sync(); });
    }
    var mo = new MutationObserver(scheduleSync);
    mo.observe(document.documentElement, {childList:true, subtree:true, attributes:true, attributeFilter:['class','style']});
    document.addEventListener('click', scheduleSync, true);
    window.addEventListener('resize', scheduleSync);
    setInterval(sync, 500);
    document.addEventListener('DOMContentLoaded', sync);
    sync();
})();
</script>
