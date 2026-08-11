<?php
/**
 * View: website/pages/services/hub.php
 *
 * Generic services hub — /services/boosting | /services/accounts | /services/items
 *
 * Variables passed in:
 *   $meta  – array: title, description, canonical, robots, h1, subtitle, icon_class, service_type
 *   $games – array of [ slug, name, icon, banner, href, is_new ]
 */

$h1          = $meta['h1']          ?? 'Services';
$subtitle    = $meta['subtitle']    ?? '';
$iconClass   = $meta['icon_class']  ?? 'fa-solid fa-gamepad';
$serviceType = $meta['service_type'] ?? 'boosting';
$serviceTypeKey = strtolower(trim(str_replace('_', '-', (string)$serviceType)));
$isTopups = in_array($serviceTypeKey, ['topups', 'top-ups', 'topup', 'top-up', 'currencies', 'currency'], true);

// Dynamic labels — different for Digital Goods vs game-based services
$isDg         = ($serviceTypeKey === 'digital-goods');
$labelSearch  = $isDg ? t('Search Digital Goods...') : ($isTopups ? t('Search top-up games...') : t('Search games...'));
$labelAllGrid = $isDg ? t('All Digital Goods')        : t('All Games');
$labelCount   = $isDg ? t('categories')               : t('games');
$labelEmpty   = $isDg ? t('No categories found.')     : ($isTopups ? t('No top-up games found.') : t('No games found.'));

/*
 * Top-ups can arrive as service_type "top-ups" from /services/top-ups,
 * while older controller logic often checks only "topups" or "currencies".
 * This view now backfills the game list when the controller passes an empty
 * $games array, using the same service checks as the header/modal.
 */
if ($isTopups && empty($games) && function_exists('util_game_nav_config')) {
    $navGames = util_game_nav_config();
    $games = [];

    foreach ($navGames as $slug => $gameRow) {
        $gameRow = is_array($gameRow) ? $gameRow : [];
        $gameId = 0;

        if (function_exists('util_get_game_by_slug')) {
            $dbGame = util_get_game_by_slug((string)$slug);
            if (is_array($dbGame)) {
                $gameId = (int)($dbGame['id'] ?? 0);
                if (empty($gameRow['icon']) && !empty($dbGame['icon'])) $gameRow['icon'] = $dbGame['icon'];
                if (empty($gameRow['banner']) && !empty($dbGame['banner'])) $gameRow['banner'] = $dbGame['banner'];
                if (empty($gameRow['name']) && !empty($dbGame['name'])) $gameRow['name'] = $dbGame['name'];
            }
        }

        $enabled = false;
        if ($gameId > 0 && function_exists('util_game_has_service')) {
            $enabled = util_game_has_service($gameId, 'topups')
                || util_game_has_service($gameId, 'top-ups')
                || util_game_has_service($gameId, 'currencies')
                || util_game_has_service($gameId, 'currency');
        }

        if (!$enabled && !empty($gameRow['categories']) && is_array($gameRow['categories'])) {
            foreach (['topups', 'top-ups', 'currencies', 'currency'] as $catKey) {
                if (!empty($gameRow['categories'][$catKey])) { $enabled = true; break; }
            }
        }

        if (!$enabled) continue;

        $cfg = function_exists('lb_get_topups_page_config') ? (lb_get_topups_page_config((string)$slug) ?: []) : [];
        $activeOffers = 0;
        if (function_exists('lb_count_service_offers') && !empty($dbGame) && is_array($dbGame)) {
            $activeOffers = (int)lb_count_service_offers($dbGame, 'topups');
        } else {
            $activeOffers = (int)($gameRow['topup_count'] ?? $gameRow['currency_count'] ?? 0);
        }
        $games[] = [
            'slug' => (string)$slug,
            'name' => (string)($gameRow['name'] ?? ucwords(str_replace('-', ' ', (string)$slug))),
            'icon' => (string)($gameRow['icon'] ?? ''),
            'banner' => (string)($gameRow['banner'] ?? ''),
            'href' => '/' . rawurlencode((string)$slug) . '/top-ups',
            'is_new' => !empty($gameRow['is_new']),
            'active_offers' => $activeOffers,
            'service_label' => (string)($cfg['service_label'] ?? ($slug === 'league-of-legends' ? 'Riot Points' : 'Top-ups')),
        ];
    }
}

if ($isTopups && !empty($games)) {
    foreach ($games as &$__shTopupGame) {
        if (!is_array($__shTopupGame)) continue;
        $slug = (string)($__shTopupGame['slug'] ?? '');
        if ($slug !== '') $__shTopupGame['href'] = '/' . rawurlencode($slug) . '/top-ups';
        if (empty($__shTopupGame['service_label'])) {
            $cfg = function_exists('lb_get_topups_page_config') ? (lb_get_topups_page_config($slug) ?: []) : [];
            $__shTopupGame['service_label'] = (string)($cfg['service_label'] ?? ($slug === 'league-of-legends' ? 'Riot Points' : 'Top-ups'));
        }
    }
    unset($__shTopupGame);
}

// Keep all games with live offers in front of the Coming soon entries while
// preserving the configured order within both groups.
if (!$isDg && !empty($games)) {
    $__shLiveGames = [];
    $__shComingSoonGames = [];
    foreach ($games as $__shGame) {
        $offerCount = max(0, (int)($__shGame['active_offers'] ?? $__shGame['offer_count'] ?? 0));
        if ($offerCount > 0) {
            $__shLiveGames[] = $__shGame;
        } else {
            $__shComingSoonGames[] = $__shGame;
        }
    }
    $games = array_merge($__shLiveGames, $__shComingSoonGames);
    unset($__shLiveGames, $__shComingSoonGames, $__shGame);
}

// ── Resolve banner URLs the same way as the landing page ──────────────────────
$bannerDirRel = '/public/assets/website/images/banner/';
$bannerDirFs  = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . $bannerDirRel;
$bannerExts   = ['webp', 'jpg', 'png', 'jpeg'];
// Derive base URL from current request
$gmBase       = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

function sh_resolve_banner(string $slug, string $bannerDirFs, string $bannerDirRel, string $gmBase, array $exts): string {
    foreach ($exts as $ext) {
        $file = $slug . '.' . $ext;
        if ($bannerDirFs !== '' && @file_exists($bannerDirFs . $file)) {
            return $gmBase . $bannerDirRel . $file;
        }
    }
    return '';
}

// Auto-resolve icon by slug from filesystem (same as landing page)
$iconDirsRel = [
    '/public/assets/website/images/icons/games/',
    '/public/assets/website/images/icons/',
    '/public/assets/website/images/game-icons/',
    '/public/assets/website/images/games/icons/',
];
$iconExts = ['svg', 'webp', 'png', 'jpg', 'jpeg'];
$docroot  = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');

function sh_resolve_icon(string $slug, string $docroot, array $dirs, array $exts, string $gmBase): string {
    foreach ($dirs as $dir) {
        foreach ($exts as $ext) {
            if ($docroot !== '' && @file_exists($docroot . $dir . $slug . '.' . $ext)) {
                return $gmBase . $dir . $slug . '.' . $ext;
            }
        }
    }
    return '';
}


if (!function_exists('sh_game_search_aliases')) {
    function sh_game_search_aliases(string $slug, string $name, string $serviceLabel = ''): string {
        $slugKey = strtolower(trim($slug));
        $nameKey = strtolower(trim($name));
        $aliases = [];

        $map = [
            'league-of-legends' => ['lol', 'league', 'league of legends', 'leagueoflegends', 'riot', 'riot games', 'summoners rift', 'smurf', 'smurfs', 'rp', 'riot points'],
            'lol-classic' => ['lol classic', 'league classic', 'league of legends classic', 'classic league', 'classic lol'],
            'valorant' => ['val', 'valo', 'valorant', 'riot', 'riot games', 'vp', 'valorant points', 'ranked account', 'smurf', 'smurfs'],
            'teamfight-tactics' => ['tft', 'teamfight', 'team fight tactics', 'teamfight tactics', 'lol tft'],
            'call-of-duty' => ['cod', 'call of duty', 'callofduty', 'black ops', 'warzone', 'mw', 'modern warfare'],
            'call-of-duty-mobile' => ['codm', 'cod mobile', 'call of duty mobile', 'callofdutymobile'],
            'counter-strike-2' => ['cs2', 'csgo', 'cs go', 'counter strike', 'counter-strike', 'counter strike 2'],
            'grand-theft-auto-v' => ['gta', 'gta 5', 'gta v', 'gtav', 'grand theft auto', 'grand theft auto 5'],
            'grand-theft-auto-vi' => ['gta6', 'gta 6', 'gta vi', 'gtavi', 'grand theft auto 6'],
            'rocket-league' => ['rl', 'rocketleague', 'rocket league'],
            'overwatch-2' => ['ow', 'ow2', 'overwatch', 'overwatch 2'],
            'apex-legends' => ['apex', 'apex legends'],
            'fortnite' => ['fn', 'fort', 'fortnite'],
            'marvel-rivals' => ['mr', 'marvel', 'marvel rivals', 'rivals'],
            'clash-of-clans-clans' => ['coc', 'clash of clans', 'clash clans', 'clans'],
            'clash-royale' => ['cr', 'clash royale'],
            'brawl-stars' => ['bs', 'brawlstars', 'brawl stars'],
            'genshin-impact' => ['gi', 'genshin', 'genshin impact'],
            'honkai-star-rail' => ['hsr', 'honkai', 'star rail', 'honkai star rail'],
            'pubg-mobile' => ['pubg', 'pubgm', 'pubg mobile'],
            'bgmi' => ['battlegrounds mobile india', 'battleground mobile india'],
            'mobile-legends' => ['ml', 'mlbb', 'mobile legends', 'mobile legends bang bang'],
            'pokemon-go' => ['pogo', 'pokemon', 'pokemon go'],
            'rainbow-six-siege' => ['r6', 'r6s', 'rainbow six', 'rainbow six siege'],
            'dead-by-daylight' => ['dbd', 'dead by daylight'],
            'roblox-rivals' => ['rivals roblox', 'roblox rivals'],
            'roblox' => ['rbx', 'robux', 'roblox'],
            'blood-strike' => ['bloodstrike', 'blood strike'],
            'bloxstrike' => ['blox strike', 'bloxstrike'],
            'delta-force' => ['df', 'delta force'],
            'free-fire' => ['ff', 'freefire', 'free fire'],
            'honor-of-kings' => ['hok', 'honor of kings'],
        ];

        if (isset($map[$slugKey])) {
            $aliases = array_merge($aliases, $map[$slugKey]);
        }

        $tokens = preg_split('/[^a-z0-9]+/i', $nameKey . ' ' . $slugKey . ' ' . strtolower($serviceLabel));
        $initials = '';
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token !== '' && !in_array($token, ['of', 'the', 'and', 'for'], true)) {
                $initials .= $token[0];
            }
        }
        if (strlen($initials) >= 2) $aliases[] = $initials;

        $aliases[] = str_replace(['-', ' '], '', $slugKey);
        $aliases[] = str_replace(['-', ' '], '', $nameKey);
        $aliases[] = str_replace(['-', ' '], ' ', $slugKey);
        $aliases[] = str_replace(['-', ' '], ' ', $nameKey);

        return implode(' ', array_unique(array_filter($aliases)));
    }
}

// Attach resolved banner to each game
foreach ($games as &$g) {
    // Discord follows the same Font Awesome card treatment as the other
    // Digital Goods categories. Do not auto-resolve discord.* as a banner or
    // attempt to render the database icon value as an image URL.
    if ($isDg && strtolower(trim((string)($g['slug'] ?? ''))) === 'discord') {
        $g['icon'] = 'fa-brands fa-discord';
        $g['banner'] = '';
        $g['banner_src'] = '';
        continue;
    }

    // Resolve icon
    if (empty($g['icon'])) {
        $g['icon'] = sh_resolve_icon($g['slug'], $docroot, $iconDirsRel, $iconExts, $gmBase);
    }

    // Use DB-supplied banner if it already has a path/URL
    $raw = trim((string)($g['banner'] ?? ''));
    if ($raw !== '' && (str_starts_with($raw, '/') || preg_match('~^https?://~i', $raw))) {
        $g['banner_src'] = $raw;
    } else {
        // Resolve by slug from file system (same logic as landing.php)
        $resolved = sh_resolve_banner($g['slug'], $bannerDirFs, $bannerDirRel, $gmBase, $bannerExts);
        $g['banner_src'] = $resolved;
    }
}
unset($g);
?>
<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'services-hub-page']) ?>

<?php $this->start('styles') ?>
<style>

/* ═══════════════════════════════════════════════════════════════
   LOLBOOST SERVICES HUB
   Rebuilt to match the site's indigo/violet premium visual language
   (same palette + glow language as the shop hero headers and the
   coming-soon empty states). Structure, IDs and classes referenced
   by JS are all preserved 1:1 — this is a full visual rebuild, not
   a functional rewrite.
═══════════════════════════════════════════════════════════════ */
.services-hub-page{
    --gm-bg:#05050f;
    --gm-bg2:#090b16;
    --gm-panel:rgba(20,21,36,.72);
    --gm-panel2:rgba(12,13,24,.78);
    --gm-stroke:rgba(255,255,255,.09);
    --gm-stroke2:rgba(129,140,248,.30);
    --gm-text:#f5f6fb;
    --gm-muted:rgba(229,231,255,.60);
    --gm-muted2:rgba(229,231,255,.40);
    --gm-indigo:#6366f1;
    --gm-violet:#8b5cf6;
    --gm-lilac:#a78bfa;
    background:
      radial-gradient(1100px 620px at 8% -4%, rgba(99,102,241,.20), transparent 58%),
      radial-gradient(900px 560px at 92% 6%, rgba(139,92,246,.14), transparent 58%),
      linear-gradient(180deg,#050510 0%,#080a18 52%,#05050f 100%);
    color:var(--gm-text);
}
.services-hub-page main{position:relative;overflow:visible;background:transparent;}
.services-hub-page main::after{
    content:"";position:fixed;inset:auto -15% -35% -15%;height:520px;z-index:0;pointer-events:none;
    background:radial-gradient(closest-side at 50% 50%, rgba(99,102,241,.16), transparent 70%);
    filter:blur(18px);
}

/* ── Topbar ── */
.sh-topbar{
    padding-top:calc(var(--lb-content-top, 110px) + 44px);
    padding-bottom:52px;
    padding-left:max(8vw, 24px);
    padding-right:max(8vw, 24px);
    min-height:270px;
    background:
      radial-gradient(820px 440px at 22% 12%, rgba(99,102,241,.22), transparent 58%),
      radial-gradient(760px 420px at 84% 30%, rgba(139,92,246,.13), transparent 60%),
      linear-gradient(180deg, rgba(10,10,24,.9), rgba(7,7,17,.72));
    border-bottom:1px solid rgba(255,255,255,.07);
    position:relative;
    z-index:20;
    overflow:visible;
    isolation:isolate;
}
.sh-topbar::before{
    content:"";position:absolute;inset:0;z-index:0;pointer-events:none;
    background-image:
      linear-gradient(to right, rgba(255,255,255,.04) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(255,255,255,.032) 1px, transparent 1px);
    background-size:72px 72px;
    mask-image:radial-gradient(ellipse 72% 72% at 42% 20%, black, transparent 78%);
}
.sh-topbar::after{
    content:"";position:absolute;inset:0;z-index:0;pointer-events:none;
    background:
      radial-gradient(440px 230px at 26% 28%, rgba(129,140,248,.14), transparent 70%),
      linear-gradient(180deg, transparent 0%, rgba(0,0,0,.26) 100%);
}
.sh-topbar__inner{
    position:relative;z-index:1;
    width:min(1500px,100%);
    margin:0 auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:28px;
    flex-wrap:wrap;
}
.sh-topbar__brand{display:flex;align-items:center;gap:20px;min-width:0;}
.sh-topbar__icon{
    position:relative;
    width:68px;height:68px;border-radius:22px;
    background:linear-gradient(135deg, #6366f1, #8b5cf6);
    border:1px solid rgba(167,139,250,.5);
    display:inline-flex;align-items:center;justify-content:center;
    font-size:27px;color:#fff;flex-shrink:0;
    box-shadow:0 22px 55px rgba(99,102,241,.32), inset 0 1px 0 rgba(255,255,255,.22);
}
.sh-topbar__icon::before{
    content:"";position:absolute;inset:-9px;border-radius:28px;
    border:1px dashed rgba(167,139,250,.32);
    animation:sh-spin-slow 20s linear infinite;
}
@keyframes sh-spin-slow{to{transform:rotate(360deg);}}
.sh-topbar__eyebrow{
    display:inline-flex;align-items:center;gap:8px;
    margin-bottom:8px;
    font-size:11px;font-weight:900;letter-spacing:.16em;text-transform:uppercase;
    color:#a5b4fc;
}
.sh-topbar__eyebrow::before{
    content:"";width:7px;height:7px;border-radius:50%;
    background:linear-gradient(90deg,var(--gm-indigo),var(--gm-violet));
    box-shadow:0 0 0 6px rgba(99,102,241,.14);
}
.sh-topbar__h1{
    margin:0;
    font-size:clamp(34px,3.2vw,58px);
    line-height:1.02;
    letter-spacing:-.045em;
    font-weight:950;
    color:#fff;
    text-shadow:0 16px 50px rgba(0,0,0,.35);
}
.sh-topbar__sub{
    margin:8px 0 0;
    font-size:15px;
    line-height:1.55;
    color:var(--gm-muted);
    font-weight:700;
}

/* ── Search ── */
.sh-search{
    display:flex;align-items:center;gap:11px;
    width:min(360px,100%);
    height:52px;
    padding:0 20px;
    border-radius:999px;
    cursor:text;
    color:#fff;
    background:linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.035));
    border:1px solid rgba(255,255,255,.13);
    box-shadow:0 18px 55px rgba(0,0,0,.32), inset 0 1px 0 rgba(255,255,255,.08);
    transition:border-color .18s ease, box-shadow .18s ease, background .18s ease, transform .18s ease;
}
.sh-search:hover,
.sh-search:focus-within{
    transform:translateY(-1px);
    border-color:rgba(129,140,248,.5);
    background:linear-gradient(180deg, rgba(99,102,241,.14), rgba(255,255,255,.05));
    box-shadow:0 20px 60px rgba(0,0,0,.38), 0 0 0 4px rgba(99,102,241,.12);
}
.sh-search i{font-size:14px;color:#a5b4fc;flex-shrink:0;}
.sh-search input{
    flex:1;min-width:0;background:transparent;border:0;outline:0;
    color:#fff;font-size:14px;font-weight:800;
}
.sh-search input::placeholder{color:rgba(229,231,255,.42);}

.sh-search-wrap{
    position:relative;
    width:min(360px,100%);
    z-index:9999;
}
.sh-search-wrap .sh-search{
    width:100%;
}
.sh-dg-dropdown{
    position:absolute;
    top:calc(100% + 10px);
    left:0;
    right:0;
    z-index:10000;
    display:none;
    max-height:min(520px,calc(100vh - 180px));
    overflow-x:hidden;
    overflow-y:auto;
    overscroll-behavior:contain;
    border-radius:20px;
    background:linear-gradient(180deg, rgba(19,19,36,.98), rgba(8,8,17,.98));
    border:1px solid rgba(129,140,248,.42);
    box-shadow:0 24px 70px rgba(0,0,0,.58), 0 0 0 1px rgba(255,255,255,.06);
}
.sh-dg-dropdown.is-open{
    display:block;
}
.sh-dg-result{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 14px;
    text-decoration:none;
    color:#fff;
    border-bottom:1px solid rgba(255,255,255,.06);
    transition:background .16s ease;
}
.sh-dg-result:last-child{
    border-bottom:0;
}
.sh-dg-result:hover{
    background:rgba(99,102,241,.16);
}
.sh-dg-result__icon{
    width:38px;
    height:38px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    background:rgba(99,102,241,.16);
    border:1px solid rgba(255,255,255,.09);
    overflow:hidden;
    color:#c7b9ff;
}
.sh-dg-result__icon img{
    width:100%;
    height:100%;
    object-fit:contain;
    display:block;
    padding:6px;
}
.sh-dg-result__brand-initial{
    width:100%;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    font-weight:950;
    color:#e0d9ff;
    background:linear-gradient(180deg, rgba(99,102,241,.32), rgba(139,92,246,.14));
}
.sh-dg-result__body{
    min-width:0;
    flex:1;
}
.sh-dg-result__title{
    display:block;
    font-size:13px;
    font-weight:950;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.sh-dg-result__meta{
    display:block;
    margin-top:3px;
    font-size:11px;
    font-weight:800;
    color:rgba(229,231,255,.55);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.sh-dg-result__price{
    font-size:13px;
    font-weight:950;
    color:#fff;
    flex-shrink:0;
}
.sh-dg-empty{
    padding:14px;
    font-size:13px;
    font-weight:800;
    color:rgba(229,231,255,.6);
}
/* Heading shown above the focus suggestions ("Popular right now") */
.sh-dg-head{
    padding:10px 14px 8px;
    font-size:10.5px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:rgba(167,180,255,.72);
    border-bottom:1px solid rgba(255,255,255,.06);
    margin-bottom:4px;
}


/* ── Content ── */
.sh-content{
    position:relative;z-index:1;
    width:min(1500px,calc(100vw - 48px));
    margin:0 auto;
    padding:56px 0 110px;
}
.sh-sec{margin-bottom:64px;}
.sh-sec-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:28px;}
.sh-sec-head h2{font-size:clamp(24px,2.1vw,36px);font-weight:950;margin:0;letter-spacing:-.035em;color:#fff;}
.sh-count{
    display:inline-flex;align-items:center;justify-content:center;
    min-height:32px;padding:0 14px;border-radius:999px;
    font-size:12px;font-weight:900;color:rgba(229,231,255,.68);
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);
}

/* ── Card grid ── */
.sh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;}
.sh-grid--dg{grid-template-columns:repeat(4,minmax(0,1fr));}

/* ── Cards ── */
.sh-card{
    display:flex;flex-direction:column;text-decoration:none;color:#fff;
    border-radius:22px;overflow:hidden;position:relative;
    background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.022));
    border:1px solid rgba(255,255,255,.10);
    box-shadow:0 18px 60px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.05);
    transition:transform .20s cubic-bezier(.22,1,.36,1), box-shadow .20s ease, border-color .20s ease, filter .20s ease;
}
.sh-card::before{
    content:"";position:absolute;inset:-1px;z-index:1;pointer-events:none;opacity:0;
    background:
      radial-gradient(500px 180px at 18% 0%, rgba(99,102,241,.20), transparent 62%),
      radial-gradient(500px 180px at 88% 0%, rgba(139,92,246,.20), transparent 64%);
    transition:opacity .20s ease;
}
.sh-card:hover{
    transform:translateY(-5px);
    border-color:rgba(129,140,248,.42);
    box-shadow:0 28px 80px rgba(0,0,0,.42), 0 0 0 1px rgba(129,140,248,.14), 0 0 52px rgba(99,102,241,.16);
}
.sh-card:hover::before{opacity:1;}

.sh-card__img-wrap{
    position:relative;
    aspect-ratio:3/4;
    overflow:hidden;
    background:
      radial-gradient(360px 180px at 50% 18%, rgba(99,102,241,.26), transparent 62%),
      linear-gradient(180deg, rgba(139,92,246,.08), rgba(99,102,241,.10) 45%, rgba(5,5,15,.92));
}
.sh-grid--dg .sh-card__img-wrap,
.sh-grid--dg .sh-card__img-wrap--placeholder,
.sh-grid--dg .sh-card__fa-wrap{
    aspect-ratio:16/7;
    min-height:150px;
}
.sh-card__img{
    width:100%;height:100%;object-fit:cover;display:block;
    transition:transform .32s ease, filter .32s ease;
}
.sh-card:hover .sh-card__img{transform:scale(1.055);filter:saturate(1.06) contrast(1.02);}
.sh-card__img-wrap::after{
    content:"";position:absolute;left:0;right:0;bottom:0;height:62%;z-index:1;pointer-events:none;
    background:linear-gradient(to top, rgba(5,5,15,.94) 0%, rgba(5,5,15,.55) 48%, transparent 100%);
}
.sh-card__img-wrap--placeholder{
    display:flex;align-items:center;justify-content:center;
    font-size:36px;color:rgba(199,185,255,.5);
}
.sh-card__icon-fallback{
    width:56px;height:56px;object-fit:contain;opacity:.68;
    filter:drop-shadow(0 0 22px rgba(129,140,248,.35));
}
.sh-card__fa-wrap{
    width:100%;display:flex;align-items:center;justify-content:center;
    background:
      radial-gradient(320px 160px at 50% 16%, rgba(139,92,246,.22), transparent 62%),
      linear-gradient(180deg, rgba(99,102,241,.24), rgba(8,7,18,.94));
}
.sh-card__fa-wrap i{
    font-size:clamp(38px,3.8vw,58px);
    color:#a78bfa;
    filter:drop-shadow(0 0 28px rgba(129,140,248,.5));
    transition:transform .24s ease, filter .24s ease;
}
.sh-card:hover .sh-card__fa-wrap i{transform:scale(1.08) rotate(-3deg);filter:drop-shadow(0 0 42px rgba(139,92,246,.68));}


.sh-offer-badge{
    position:absolute;right:12px;top:12px;z-index:3;
    display:inline-flex;align-items:center;gap:6px;
    min-height:30px;padding:0 11px;border-radius:999px;
    font-size:11px;font-weight:950;color:#fff;
    background:linear-gradient(135deg, rgba(99,102,241,.92), rgba(139,92,246,.82));
    border:1px solid rgba(255,255,255,.20);
    box-shadow:0 14px 34px rgba(0,0,0,.34), 0 0 28px rgba(99,102,241,.26);
    backdrop-filter:blur(10px);
}
.sh-offer-badge i{font-size:10px;color:#fff;}
.sh-offer-badge--soon{background:linear-gradient(135deg,rgba(251,191,36,.92),rgba(249,115,22,.86));box-shadow:0 14px 34px rgba(0,0,0,.34),0 0 28px rgba(251,191,36,.22);}
.sh-card--coming-soon{border-color:rgba(251,191,36,.20);}
.sh-card--coming-soon .sh-card__img{filter:saturate(.75) brightness(.74);}
.sh-card--coming-soon .sh-card__img-wrap::before{content:"";position:absolute;inset:0;z-index:2;background:linear-gradient(180deg,rgba(8,7,18,.08),rgba(8,7,18,.34));pointer-events:none;}
.sh-card__offers-inline--soon{color:#fde68a;background:rgba(251,191,36,.12);border-color:rgba(251,191,36,.24);}
.sh-card__offers-inline{
    flex-shrink:0;
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 8px;border-radius:999px;
    font-size:10px;font-weight:950;color:#c7b9ff;
    background:rgba(99,102,241,.12);border:1px solid rgba(129,140,248,.24);
}

/* Footer row */
.sh-card__foot{
    position:relative;z-index:2;
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    padding:10px 12px;
    min-height:46px;
    background:rgba(6,6,16,.76);
    border-top:1px solid rgba(255,255,255,.06);
}
.sh-card__name{
    font-size:13px;font-weight:950;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    flex:1;min-width:0;letter-spacing:-.015em;color:#fff;
    text-shadow:0 10px 28px rgba(0,0,0,.45);
}
.sh-card__game-icon{
    width:24px;height:24px;border-radius:8px;object-fit:contain;flex-shrink:0;
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
}
.sh-card__dg-icon{
    width:28px;height:28px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
    background:linear-gradient(180deg, rgba(99,102,241,.26), rgba(139,92,246,.14));
    border:1px solid rgba(129,140,248,.36);
}
.sh-card__dg-icon i{font-size:13px;color:#c7b9ff;}
.sh-card__arrow{
    width:28px;height:28px;border-radius:10px;flex-shrink:0;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:11px;color:#e0d9ff;
    background:rgba(99,102,241,.20);border:1px solid rgba(129,140,248,.35);
    transition:transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.sh-card:hover .sh-card__arrow{
    transform:translateX(2px);
    background:linear-gradient(135deg, rgba(99,102,241,.75), rgba(139,92,246,.6));
    border-color:rgba(255,255,255,.20);
    box-shadow:0 0 22px rgba(129,140,248,.3);
}
.sh-card:hover .sh-card__game-icon{background:rgba(255,255,255,.08);}

/* NEW badge */
.sh-badge-new{
    position:absolute;top:10px;left:10px;z-index:3;
    display:inline-flex;align-items:center;gap:5px;
    padding:5px 10px;border-radius:999px;
    font-size:10px;font-weight:950;letter-spacing:.08em;text-transform:uppercase;
    color:#fff;background:linear-gradient(90deg,#6366f1,#8b5cf6,#a78bfa);
    box-shadow:0 12px 28px rgba(99,102,241,.28);
    pointer-events:none;
}

/* Empty / no-results */
.sh-empty,.sh-no-results{
    color:var(--gm-muted);font-size:15px;padding:42px 0;text-align:center;
}
.sh-card[data-hidden="1"]{display:none;}

/* Coming soon empty state */
.sh-coming-soon{
  display:flex;align-items:center;gap:20px;max-width:720px;
  padding:26px;border-radius:24px;
  background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.024));
  border:1px solid rgba(255,255,255,.10);
  box-shadow:0 20px 70px rgba(0,0,0,.28);
}
.sh-cs-icon,.sh-cs-chat__icon{
  display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
  background:rgba(99,102,241,.16);border:1px solid rgba(129,140,248,.28);color:#c7b9ff;
}
.sh-cs-icon{width:58px;height:58px;border-radius:18px;font-size:22px;}
.sh-cs-body{display:flex;flex-direction:column;gap:4px;flex:1;}
.sh-cs-title{font-size:18px;font-weight:950;color:#fff;letter-spacing:-.02em;}
.sh-cs-sub{font-size:14px;color:var(--gm-muted);font-weight:600;line-height:1.5;}
.sh-cs-chat{
  display:inline-flex;align-items:center;gap:12px;flex-shrink:0;text-decoration:none;color:#fff;
  padding:12px 18px;border-radius:16px;
  background:rgba(99,102,241,.14);border:1px solid rgba(129,140,248,.28);
  transition:transform .18s ease, background .18s ease, border-color .18s ease;
}
.sh-cs-chat:hover{transform:translateY(-2px);background:rgba(99,102,241,.24);border-color:rgba(129,140,248,.48);}
.sh-cs-chat__icon{width:36px;height:36px;border-radius:12px;font-size:16px;}
.sh-cs-chat__text{display:flex;flex-direction:column;gap:1px;}
.sh-cs-chat__text span:first-child{font-size:11px;color:rgba(255,255,255,.55);font-weight:800;text-transform:uppercase;letter-spacing:.08em;}
.sh-cs-chat__text span:last-child{font-size:14px;font-weight:950;color:#fff;}

/* Responsive */
@media (max-width:1100px){
    .sh-grid--dg{grid-template-columns:repeat(3,minmax(0,1fr));}
}
@media (max-width:900px){
    .sh-topbar{padding-left:16px;padding-right:16px;min-height:auto;padding-bottom:34px;}
    .sh-topbar__inner{flex-direction:column;align-items:flex-start;gap:20px;}
    .sh-search{width:100%;min-width:0;}
    .sh-search-wrap{width:100%;}
    .sh-content{width:calc(100vw - 28px);padding:34px 0 90px;}
    .sh-grid{grid-template-columns:repeat(2,1fr);gap:16px;}
    .sh-grid--dg{grid-template-columns:repeat(2,1fr);}
    .sh-grid--dg .sh-card__img-wrap,
    .sh-grid--dg .sh-card__img-wrap--placeholder,
    .sh-grid--dg .sh-card__fa-wrap{min-height:128px;}
    .sh-coming-soon{flex-direction:column;align-items:flex-start;}
}
@media (max-width:520px){
    .sh-topbar__brand{align-items:flex-start;}
    .sh-topbar__icon{width:54px;height:54px;border-radius:18px;font-size:22px;}
    .sh-grid{grid-template-columns:1fr;}
    .sh-grid--dg{grid-template-columns:1fr;}
    .sh-sec-head{align-items:flex-start;flex-direction:column;}
}



/* ═══════════════════════════════════════════════════════
   Mobile game hub, desktop-like mini cards, 3 per row
═══════════════════════════════════════════════════════ */
@media (max-width:520px){
    .sh-content{
        width:calc(100vw - 28px)!important;
        padding:32px 0 86px!important;
    }
    .sh-sec-head{
        flex-direction:row!important;
        align-items:center!important;
        margin-bottom:18px!important;
    }
    .sh-count{
        min-height:28px!important;
        padding:0 10px!important;
        font-size:11px!important;
    }

    .sh-grid:not(.sh-grid--dg){
        grid-template-columns:repeat(3,minmax(0,1fr))!important;
        gap:14px 10px!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card{
        border-radius:14px!important;
        overflow:hidden!important;
        background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.024))!important;
        border:1px solid rgba(255,255,255,.10)!important;
        box-shadow:0 12px 32px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.045)!important;
        min-width:0!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card::before{
        display:block!important;
        opacity:.65!important;
        background:radial-gradient(170px 90px at 45% 0%,rgba(99,102,241,.16),transparent 68%)!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card:hover{
        transform:none!important;
        box-shadow:0 12px 32px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.045)!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card__img-wrap{
        aspect-ratio:3 / 4!important;
        border-radius:0!important;
        overflow:hidden!important;
        border:0!important;
        background:rgba(255,255,255,.04)!important;
        box-shadow:none!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card__img-wrap::after{
        display:block!important;
        height:54%!important;
        background:linear-gradient(to top,rgba(5,5,15,.88) 0%,rgba(5,5,15,.42) 50%,transparent 100%)!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card__img{
        width:100%!important;
        height:100%!important;
        object-fit:cover!important;
        object-position:center!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-offer-badge{
        display:inline-flex!important;
        right:6px!important;
        top:6px!important;
        min-height:21px!important;
        max-width:calc(100% - 12px)!important;
        padding:0 7px!important;
        gap:4px!important;
        font-size:8px!important;
        line-height:1!important;
        white-space:nowrap!important;
        overflow:hidden!important;
        text-overflow:ellipsis!important;
        border-radius:999px!important;
        box-shadow:0 8px 18px rgba(0,0,0,.30),0 0 18px rgba(99,102,241,.18)!important;
    }
    .sh-grid:not(.sh-grid--dg) .sh-offer-badge i{font-size:8px!important;}

    .sh-grid:not(.sh-grid--dg) .sh-card__foot{
        min-height:39px!important;
        padding:7px 7px!important;
        border-top:1px solid rgba(255,255,255,.07)!important;
        background:rgba(6,6,16,.82)!important;
        display:grid!important;
        grid-template-columns:18px minmax(0,1fr) auto!important;
        gap:5px!important;
        align-items:center!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card__game-icon{
        display:block!important;
        width:18px!important;
        height:18px!important;
        border-radius:6px!important;
        background:rgba(255,255,255,.06)!important;
        border:1px solid rgba(255,255,255,.08)!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card__name{
        display:block!important;
        min-width:0!important;
        width:100%!important;
        font-size:10px!important;
        line-height:1.12!important;
        font-weight:950!important;
        color:#fff!important;
        text-shadow:0 8px 20px rgba(0,0,0,.45)!important;
        white-space:nowrap!important;
        overflow:hidden!important;
        text-overflow:ellipsis!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-card__offers-inline{
        display:inline-flex!important;
        min-width:0!important;
        max-width:38px!important;
        height:22px!important;
        padding:0 6px!important;
        gap:3px!important;
        font-size:9px!important;
        line-height:1!important;
        overflow:hidden!important;
        white-space:nowrap!important;
    }
    .sh-grid:not(.sh-grid--dg) .sh-card__offers-inline i{font-size:8px!important;}

    .sh-grid:not(.sh-grid--dg) .sh-card__arrow{
        display:none!important;
    }

    .sh-grid:not(.sh-grid--dg) .sh-badge-new{
        top:6px!important;
        left:6px!important;
        padding:4px 7px!important;
        font-size:8px!important;
    }
}

@media (max-width:380px){
    .sh-grid:not(.sh-grid--dg){
        gap:12px 8px!important;
    }
    .sh-grid:not(.sh-grid--dg) .sh-card{
        border-radius:13px!important;
    }
    .sh-grid:not(.sh-grid--dg) .sh-card__foot{
        min-height:36px!important;
        padding:6px!important;
        grid-template-columns:17px minmax(0,1fr)!important;
    }
    .sh-grid:not(.sh-grid--dg) .sh-card__offers-inline{
        display:none!important;
    }
    .sh-grid:not(.sh-grid--dg) .sh-card__game-icon{
        width:17px!important;
        height:17px!important;
    }
    .sh-grid:not(.sh-grid--dg) .sh-card__name{
        font-size:9.5px!important;
    }
}

/* Flat shop-style hub header */
.services-hub-page{
    --gm-bg:#030817;
    --gm-bg2:#030817;
    --gm-panel:#090d1d;
    --gm-panel2:#090d1d;
    --gm-indigo:#4f6ef7;
    --gm-violet:#637cff;
    --gm-lilac:#8ea5ff;
    background:#030817;
}
.services-hub-page main::after{display:none;}
.sh-topbar{
    background:#030817;
    border-bottom:1px solid rgba(255,255,255,.07);
}
.sh-topbar::before,
.sh-topbar::after{display:none;}
.sh-topbar__icon{
    width:62px;
    height:62px;
    border-radius:18px;
    background:#10162d;
    border:1px solid rgba(124,159,255,.24);
    color:#7187ff;
    box-shadow:none;
}
.sh-topbar__icon::before{display:none;}
.sh-topbar__eyebrow{
    color:#8ea5ff;
    letter-spacing:.13em;
}
.sh-topbar__eyebrow::before{
    width:6px;
    height:6px;
    background:#637cff;
    box-shadow:none;
}
.sh-topbar__h1{
    font-size:clamp(32px,3vw,50px);
    letter-spacing:-.035em;
    font-weight:900;
    text-shadow:none;
}
.sh-topbar__sub{
    color:rgba(196,205,239,.68);
    font-weight:600;
}
.sh-search{
    height:50px;
    border-radius:16px;
    background:#0b1022;
    border:1px solid rgba(255,255,255,.11);
    box-shadow:none;
}
.sh-search:hover,
.sh-search:focus-within{
    transform:none;
    background:#0d1328;
    border-color:rgba(124,159,255,.32);
    box-shadow:0 0 0 3px rgba(79,110,247,.08);
}
.sh-search i{color:#8ea5ff;}
.sh-dg-dropdown{
    background:#090d1d;
    border-color:rgba(124,159,255,.24);
    box-shadow:0 22px 58px rgba(0,0,0,.48);
}
</style>
<?php $this->end('styles') ?>

<!-- ── Topbar (below navbar via --lb-content-top) ── -->
<div class="sh-topbar">
    <div class="sh-topbar__inner">
        <div class="sh-topbar__brand">
            <span class="sh-topbar__icon">
                <i class="<?= htmlspecialchars($iconClass, ENT_QUOTES) ?>"></i>
            </span>
            <div>
                <h1 class="sh-topbar__h1"><?= htmlspecialchars($h1, ENT_QUOTES) ?></h1>
                <p class="sh-topbar__sub"><?= htmlspecialchars($subtitle, ENT_QUOTES) ?></p>
            </div>
        </div>
        <div class="sh-search-wrap">
            <label class="sh-search" aria-label="<?= htmlspecialchars($labelSearch, ENT_QUOTES) ?>">
                <i class="fas fa-search"></i>
                <input
                    type="search"
                    id="shSearchInput"
                    placeholder="<?= htmlspecialchars($labelSearch, ENT_QUOTES) ?>"
                    autocomplete="off"
                >
            </label>

            <?php if ($isDg): ?>
            <div class="sh-dg-dropdown" id="shDgDropdown"></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── All Games grid ── -->
<div class="sh-content">
    <div class="sh-sec">
        <div class="sh-sec-head">
            <h2><?= $labelAllGrid ?></h2>
            <?php if (!empty($games)): ?>
            <span class="sh-count"><?= count($games) ?> <?= $labelCount ?></span>
            <?php endif; ?>
        </div>

        <?php if (empty($games)): ?>
        <div class="sh-coming-soon">
          <div class="sh-cs-icon"><i class="fas fa-clock"></i></div>
          <div class="sh-cs-body">
            <span class="sh-cs-title"><?= t('Coming soon') ?></span>
            <span class="sh-cs-sub"><?= t('No games available for this service yet. More games are on the way!') ?></span>
          </div>
          <a href="#" class="sh-cs-chat" data-tawk-open="1" onclick="if(window.Tawk_API&&window.Tawk_API.maximize)window.Tawk_API.maximize();return false;">
            <span class="sh-cs-chat__icon"><i class="fas fa-comments"></i></span>
            <span class="sh-cs-chat__text">
              <span><?= t('Questions?') ?></span>
              <span><?= t('Live Chat') ?></span>
            </span>
          </a>
        </div>
        <?php else: ?>
        <div class="sh-grid<?= $isDg ? ' sh-grid--dg' : '' ?>" id="shGrid">
            <?php foreach ($games as $game):
                $bannerSrc = $game['banner_src'] ?? '';
                $iconSrc   = $game['icon']       ?? '';
                $faIcon    = $game['fa_icon']    ?? ''; // FontAwesome class string for DG cats
                $name      = htmlspecialchars($game['name'], ENT_QUOTES);
                $href      = htmlspecialchars($game['href'], ENT_QUOTES);
                $isNew     = !empty($game['is_new']);
                $offerCount = max(0, (int)($game['active_offers'] ?? $game['offer_count'] ?? 0));
                $isComingSoon = (!$isDg && $offerCount <= 0);
                $offerLabel = $offerCount === 1 ? t('offer') : t('offers');
                $serviceLabel = trim((string)($game['service_label'] ?? ($isTopups ? t('Top-ups') : '')));
                $searchMeta = strtolower(trim(($game['name'] ?? '') . ' ' . ($game['slug'] ?? '') . ' ' . $serviceLabel . ' ' . sh_game_search_aliases((string)($game['slug'] ?? ''), (string)($game['name'] ?? ''), $serviceLabel) . ' boosting boost accounts account smurf smurfs ranked premium items item top-ups topups currencies currency riot points rp'));
                $hasImg    = $bannerSrc !== '';
                // For DG: icon field IS the FA class (e.g. "fa-solid fa-play")
                $isFaIcon  = $isDg && !$hasImg && preg_match('/^fa[-\s]/', $iconSrc);
            ?>
            <a
                href="<?= $href ?>"
                class="sh-card<?= $isFaIcon ? ' sh-card--fa' : '' ?><?= $isComingSoon ? ' sh-card--coming-soon' : '' ?>"
                data-game-name="<?= htmlspecialchars($searchMeta, ENT_QUOTES) ?>"
            >
                <?php if ($isNew): ?>
                <span class="sh-badge-new"><i class="fas fa-sparkles"></i> <?= t('New') ?></span>
                <?php endif; ?>
                <?php if ($isComingSoon): ?>
                <span class="sh-offer-badge sh-offer-badge--soon"><i class="fas fa-clock"></i> <?= t('Coming soon') ?></span>
                <?php elseif ($isTopups): ?>
                <span class="sh-offer-badge"><i class="fas fa-bolt"></i> <?= htmlspecialchars($serviceLabel !== '' ? $serviceLabel : t('Top-ups'), ENT_QUOTES) ?></span>
                <?php else: ?>
                <span class="sh-offer-badge"><i class="fas fa-tags"></i> <?= number_format($offerCount, 0, '.', ',') ?> <?= $offerLabel ?></span>
                <?php endif; ?>

                <div class="sh-card__img-wrap<?= ($hasImg || $isFaIcon) ? '' : ' sh-card__img-wrap--placeholder' ?>">
                    <?php if ($hasImg): ?>
                    <img
                        src="<?= htmlspecialchars($bannerSrc, ENT_QUOTES) ?>"
                        alt="<?= $name ?>"
                        class="sh-card__img"
                        loading="lazy"
                        decoding="async"
                    >
                    <?php elseif ($isFaIcon): ?>
                    <div class="sh-card__fa-wrap">
                        <i class="<?= htmlspecialchars($iconSrc, ENT_QUOTES) ?>"></i>
                    </div>
                    <?php elseif ($iconSrc): ?>
                    <img
                        src="<?= htmlspecialchars($iconSrc, ENT_QUOTES) ?>"
                        alt="<?= $name ?>"
                        class="sh-card__icon-fallback"
                        loading="lazy"
                    >
                    <?php else: ?>
                    <i class="fas fa-layer-group"></i>
                    <?php endif; ?>
                </div>

                <div class="sh-card__foot">
                    <?php if ($isFaIcon): ?>
                    <span class="sh-card__dg-icon"><i class="<?= htmlspecialchars($iconSrc, ENT_QUOTES) ?>"></i></span>
                    <?php elseif ($iconSrc && !$isFaIcon): ?>
                    <img src="<?= htmlspecialchars($iconSrc, ENT_QUOTES) ?>" alt="" class="sh-card__game-icon" loading="lazy">
                    <?php endif; ?>
                    <span class="sh-card__name"><?= $name ?></span>
                    <?php if ($isComingSoon): ?>
                    <span class="sh-card__offers-inline sh-card__offers-inline--soon"><i class="fas fa-clock"></i> <?= t('Soon') ?></span>
                    <?php elseif ($isTopups): ?>
                    <span class="sh-card__offers-inline"><i class="fas fa-bolt"></i> <?= htmlspecialchars($serviceLabel !== '' ? $serviceLabel : t('Top-ups'), ENT_QUOTES) ?></span>
                    <?php else: ?>
                    <span class="sh-card__offers-inline"><i class="fas fa-tags"></i> <?= number_format($offerCount, 0, '.', ',') ?></span>
                    <?php endif; ?>
                    <span class="sh-card__arrow"><i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <p class="sh-no-results" id="shNoResults" style="display:none"><?= $labelEmpty ?></p>
        <?php endif; ?>
    </div>
</div>

<?php $this->start('scripts') ?>
<script>
(function () {
    var input = document.getElementById('shSearchInput');
    var grid  = document.getElementById('shGrid');
    var noRes = document.getElementById('shNoResults');
    var dropdown = document.getElementById('shDgDropdown');
    var isDg = <?= $isDg ? 'true' : 'false' ?>;
    var timer = null;
    var lastQuery = '';

    if (!input || !grid) return;

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (m) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[m];
        });
    }

    function brandInitial(item) {
        var source = (item && (item.brand || item.title || item.category)) ? String(item.brand || item.title || item.category) : '•';
        return source.trim().charAt(0).toUpperCase() || '•';
    }

    window.shNextDgBrandIcon = function (img) {
        var candidates = [];
        try {
            candidates = JSON.parse(img.getAttribute('data-candidates') || '[]');
        } catch (e) {
            candidates = [];
        }

        var nextIndex = parseInt(img.getAttribute('data-icon-index') || '0', 10) + 1;
        if (candidates[nextIndex]) {
            img.setAttribute('data-icon-index', String(nextIndex));
            img.src = candidates[nextIndex];
            return;
        }

        var initial = img.getAttribute('data-initial') || '•';
        if (img.parentNode) {
            img.parentNode.innerHTML = '<span class="sh-dg-result__brand-initial">' + escapeHtml(initial) + '</span>';
        }
    };

    function renderBrandIcon(item) {
        var candidates = Array.isArray(item.icon_candidates) ? item.icon_candidates.slice() : [];
        if (item.icon && candidates.indexOf(item.icon) === -1) {
            candidates.unshift(item.icon);
        }

        var initial = item.brand_initial || brandInitial(item);
        if (!candidates.length) {
            return '<span class="sh-dg-result__brand-initial">' + escapeHtml(initial) + '</span>';
        }

        return '<img src="' + escapeHtml(candidates[0]) + '" alt="" data-icon-index="0" data-initial="' + escapeHtml(initial) + '" data-candidates="' + escapeHtml(JSON.stringify(candidates)) + '" onerror="window.shNextDgBrandIcon(this)">';
    }

    function closeDropdown() {
        if (!dropdown) return;
        dropdown.classList.remove('is-open');
        dropdown.innerHTML = '';
    }

    function renderDropdown(items, q, isSuggestion) {
        if (!dropdown) return;

        if (!items || !items.length) {
            dropdown.innerHTML = '<div class="sh-dg-empty">'
                + (isSuggestion ? '<?= t('No listings available right now.') ?>' : '<?= t('No listings found.') ?>')
                + '</div>';
            dropdown.classList.add('is-open');
            return;
        }

        // Focus with an empty field shows the best sellers instead of nothing.
        var head = isSuggestion
            ? '<div class="sh-dg-head"><?= t('Popular right now') ?></div>'
            : '';

        dropdown.innerHTML = head + items.map(function (item) {
            var icon = renderBrandIcon(item);

            var meta = [item.brand, item.category, item.stock ? (item.stock + ' in stock') : '']
                .filter(Boolean)
                .join(' • ');

            return ''
                + '<a class="sh-dg-result" href="' + escapeHtml(item.url) + '">'
                + '  <span class="sh-dg-result__icon">' + icon + '</span>'
                + '  <span class="sh-dg-result__body">'
                + '    <span class="sh-dg-result__title">' + escapeHtml(item.title) + '</span>'
                + '    <span class="sh-dg-result__meta">' + escapeHtml(meta) + '</span>'
                + '  </span>'
                + '  <span class="sh-dg-result__price">€' + escapeHtml(item.price) + '</span>'
                + '</a>';
        }).join('');

        dropdown.classList.add('is-open');
    }

    function fetchListings(q) {
        if (!isDg || !dropdown) return;

        // q === '' is valid: the backend then returns the popular listings.
        if (q.length && q.length < 2) {
            closeDropdown();
            return;
        }

        lastQuery = q;

        var form = new FormData();
        form.append('action', 'dg_search_listings');
        form.append('q', q);

        fetch('<?= BASE_URL ?>/ajax', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (q !== lastQuery) return;
            var items = (data && data.items) ? data.items : [];
            var isSuggestion = !!(data && data.suggestions);
            if (isSuggestion) suggestionCache = items;
            renderDropdown(items, q, isSuggestion);
        })
        .catch(function () {
            closeDropdown();
        });
    }

    input.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var qCompact = q.replace(/[^a-z0-9]/g, '');
        var qWords = q.replace(/[-_]+/g, ' ');
        var queries = [q, qCompact, qWords].filter(Boolean);

        if (isDg) {
            clearTimeout(timer);
            timer = setTimeout(function () {
                fetchListings(q);
            }, 180);
            return;
        }

        var cards = grid.querySelectorAll('.sh-card');
        var vis = 0;

        cards.forEach(function (c) {
            var haystack = c.getAttribute('data-game-name') || '';
            var match = !q || queries.some(function (term) { return term && haystack.indexOf(term) !== -1; });
            c.setAttribute('data-hidden', match ? '0' : '1');
            if (match) vis++;
        });

        if (noRes) noRes.style.display = (q && vis === 0) ? '' : 'none';
    });

    // Clicking / focusing the empty field already shows offers.
    var suggestionCache = null;

    function showSuggestions() {
        if (!isDg || !dropdown) return;
        if (input.value.trim().length >= 2) return;

        if (suggestionCache) {
            lastQuery = '';
            renderDropdown(suggestionCache, '', true);
            return;
        }
        fetchListings('');
    }

    input.addEventListener('focus', showSuggestions);
    input.addEventListener('click', showSuggestions);

    document.addEventListener('click', function (e) {
        if (!dropdown) return;
        if (!e.target.closest('.sh-search-wrap')) {
            closeDropdown();
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDropdown();
    });
})();
</script>
<?php $this->end('scripts') ?>
