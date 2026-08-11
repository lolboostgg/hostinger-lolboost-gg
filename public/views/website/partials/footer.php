<?php
/* ── FOOTER GAMES: same dynamic source as header modal ── */
$_footerBase = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$_footerGames = [];

/* Primary source: header modal config */
if (function_exists('util_game_nav_config')) {
    $_cfgGames = util_game_nav_config();
    if (is_iterable($_cfgGames)) {
        foreach ($_cfgGames as $_key => $_row) {
            if (!is_array($_row) && !is_object($_row)) continue;
            $_status = is_array($_row) ? ($_row['status'] ?? $_row['is_active'] ?? $_row['active'] ?? 1) : ($_row->status ?? $_row->is_active ?? $_row->active ?? 1);
            if ((string)$_status !== '' && (string)$_status !== '1' && strtolower((string)$_status) !== 'active' && strtolower((string)$_status) !== 'true') continue;

            $_name = trim((string)(is_array($_row) ? ($_row['name'] ?? $_row['label'] ?? $_row['title'] ?? '') : ($_row->name ?? $_row->label ?? $_row->title ?? '')));
            $_slug = trim((string)(is_array($_row) ? ($_row['slug'] ?? $_key ?? '') : ($_row->slug ?? $_key ?? '')));
            $_icon = trim((string)(is_array($_row) ? ($_row['icon'] ?? $_row['icon_url'] ?? $_row['image'] ?? '') : ($_row->icon ?? $_row->icon_url ?? $_row->image ?? '')));
            $_sort = (int)(is_array($_row) ? ($_row['sort'] ?? $_row['sort_order'] ?? $_row['order'] ?? 999) : ($_row->sort ?? $_row->sort_order ?? $_row->order ?? 999));
            $_cats = is_array($_row) ? ($_row['categories'] ?? []) : ($_row->categories ?? []);
            if ($_slug === '' || $_name === '') continue;

            $_footerGames[$_slug] = [
                'slug' => $_slug,
                'name' => $_name,
                'sort' => $_sort,
                'iconRaw' => $_icon,
                'categories' => is_array($_cats) ? $_cats : [],
            ];
        }
    }
}

/* Fallbacks from landing/controller variables */
if (empty($_footerGames) && !empty($gmAvailableGames) && is_array($gmAvailableGames)) {
    $_footerGames = $gmAvailableGames;
}
if (empty($_footerGames)) {
    foreach (['games','allGames','dbGames','availableGames','gameList'] as $_sn) {
        if (isset(${$_sn}) && is_iterable(${$_sn})) {
            foreach (${$_sn} as $_row) {
                $_status = is_array($_row) ? ($_row['status'] ?? $_row['is_active'] ?? 1) : ($_row->status ?? $_row->is_active ?? 1);
                if ((string)$_status !== '' && (string)$_status !== '1' && strtolower((string)$_status) !== 'active') continue;
                $_name    = trim((string)(is_array($_row) ? ($_row['name'] ?? $_row['label'] ?? $_row['title'] ?? '') : ($_row->name ?? $_row->label ?? $_row->title ?? '')));
                $_slug    = trim((string)(is_array($_row) ? ($_row['slug'] ?? $_row['game_slug'] ?? '') : ($_row->slug ?? $_row->game_slug ?? '')));
                $_iconRaw = trim((string)(is_array($_row) ? ($_row['icon'] ?? $_row['icon_url'] ?? $_row['image'] ?? '') : ($_row->icon ?? $_row->icon_url ?? $_row->image ?? '')));
                if ($_slug === '' || $_name === '') continue;
                $_sort = (int)(is_array($_row) ? ($_row['sort'] ?? $_row['sort_order'] ?? $_row['order'] ?? 999) : ($_row->sort ?? $_row->sort_order ?? $_row->order ?? 999));
                $_footerGames[$_slug] = ['slug'=>$_slug,'name'=>$_name,'sort'=>$_sort,'iconRaw'=>$_iconRaw,'categories'=>[]];
            }
            if (!empty($_footerGames)) break;
        }
    }
}
if (empty($_footerGames)) {
    $_footerGames = [
        'league-of-legends' => ['slug'=>'league-of-legends','name'=>'League of Legends','sort'=>1,'iconRaw'=>'','categories'=>[]],
        'valorant'          => ['slug'=>'valorant','name'=>'Valorant','sort'=>2,'iconRaw'=>'','categories'=>[]],
        'teamfight-tactics' => ['slug'=>'teamfight-tactics','name'=>'Teamfight Tactics','sort'=>3,'iconRaw'=>'','categories'=>[]],
    ];
}


/* Add Top Ups to footer games using the same dynamic topup source as the header */
if (!function_exists('lb_footer_topups_enabled_for_game')) {
    function lb_footer_topups_enabled_for_game(string $gameSlug): bool {
        $gameSlug = trim($gameSlug);
        if ($gameSlug === '') return false;

        $gameRow = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
        $gameId = (int)($gameRow['id'] ?? 0);

        if ($gameId > 0 && function_exists('util_game_has_service')) {
            return util_game_has_service($gameId, 'topups')
                || util_game_has_service($gameId, 'top-ups')
                || util_game_has_service($gameId, 'top_ups')
                || util_game_has_service($gameId, 'currencies')
                || util_game_has_service($gameId, 'currency');
        }

        if (function_exists('lb_get_topups_page_config')) {
            $cfg = lb_get_topups_page_config($gameSlug) ?: [];
            if (!empty($cfg)) return true;
        }

        return in_array($gameSlug, [
            'league-of-legends',
            'valorant',
            'call-of-duty',
            'fortnite',
            'marvel-rivals',
            'genshin-impact',
            'clash-of-clans',
            'clash-royale',
            'brawl-stars',
            'pokemon',
            'pokemon-go',
            'mobile-legends',
            'roblox',
            'roblox-rivals',
        ], true);
    }
}

$_footerTopupLabelByGame = [
    'league-of-legends' => 'Riot Points',
    'valorant' => 'Valorant Points',
    'call-of-duty' => 'COD Points',
    'fortnite' => 'V-Bucks',
    'roblox' => 'Robux',
    'roblox-rivals' => 'Robux',
    'brawl-stars' => 'Gems',
    'clash-of-clans' => 'Gems',
    'clash-royale' => 'Gems',
    'pokemon' => 'Coins',
    'pokemon-go' => 'Coins',
    'genshin-impact' => 'Coins',
    'mobile-legends' => 'Diamonds',
    'marvel-rivals' => 'Top Up',
];

foreach ($_footerGames as $_footerGameSlug => &$_footerGameRow) {
    $_footerGameSlug = (string)($_footerGameRow['slug'] ?? $_footerGameSlug);
    if ($_footerGameSlug === '' || !lb_footer_topups_enabled_for_game($_footerGameSlug)) continue;

    $_footerTopupCfg = function_exists('lb_get_topups_page_config') ? (lb_get_topups_page_config($_footerGameSlug) ?: []) : [];
    $_footerTopupLabel = (string)($_footerTopupCfg['service_label'] ?? ($_footerTopupLabelByGame[$_footerGameSlug] ?? 'Top Up'));

    if (!isset($_footerGameRow['categories']) || !is_array($_footerGameRow['categories'])) {
        $_footerGameRow['categories'] = [];
    }

    foreach (['topups', 'top-ups', 'top_ups', 'currencies', 'currency'] as $_footerTopupAlias) {
        if (isset($_footerGameRow['categories'][$_footerTopupAlias])) {
            unset($_footerGameRow['categories'][$_footerTopupAlias]);
        }
    }

    $_footerGameRow['categories']['topups'] = [
        'label' => $_footerTopupLabel,
        'href' => '/' . trim($_footerGameSlug, '/') . '/top-ups',
    ];
}
unset($_footerGameRow);

/* Trending footer order, keeps the first two visible rows focused on the games users click most. */
$_footerTrendingOrder = [
    'fortnite',
    'valorant',
    'steal-a-brainrot',
    'grow-a-garden-2',
    'league-of-legends',
    'grand-theft-auto-v',
    'call-of-duty',
    'clash-of-clans',
    'brawl-stars',
    'roblox',
    'raid-shadow-legends',
    'pubg-mobile',
    'mobile-legends',
    'clash-royale',
    'wow-classic-era',
    'rainbow-six-siege',
    'pokemon-go',
    'pokemon',
    'rocket-league',
    'plants-vs-brainrots',
    'apex-legends',
    'grow-a-garden',
    'free-fire',
    'genshin-impact',
    'marvel-rivals',
    'overwatch-2',
    'teamfight-tactics',
    'arc-raiders',
    'grand-theft-auto-vi',
    'call-of-duty-mobile',
];
$_footerTrendingRank = array_flip($_footerTrendingOrder);
uasort($_footerGames, function($a, $b) use ($_footerTrendingRank) {
    $aSlug = (string)($a['slug'] ?? '');
    $bSlug = (string)($b['slug'] ?? '');
    $aRank = $_footerTrendingRank[$aSlug] ?? 9999;
    $bRank = $_footerTrendingRank[$bSlug] ?? 9999;
    if ($aRank !== $bRank) return $aRank <=> $bRank;
    return (($a['sort'] ?? 999) <=> ($b['sort'] ?? 999)) ?: strcasecmp($a['name'] ?? '', $b['name'] ?? '');
});

/* Resolve icon URL */
$_footerDocroot  = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
$_footerIconDirs = ['/public/assets/website/images/icons/games/','/public/assets/website/images/icons/','/public/assets/website/images/game-icons/','/public/assets/website/images/games/icons/'];
$_footerIconExts = ['svg','webp','png','jpg','jpeg'];
$_footerResolveIcon = function(string $slug, string $raw) use ($_footerBase, $_footerDocroot, $_footerIconDirs, $_footerIconExts): string {
    $raw = trim($raw);
    if ($raw !== '') {
        if (preg_match('~^https?://~i', $raw) || strpos($raw, '/') === 0) return $raw;
    }
    foreach ($_footerIconDirs as $d) {
        foreach ($_footerIconExts as $e) {
            $f = $_footerDocroot.$d.$slug.'.'.$e;
            if ($_footerDocroot !== '' && @file_exists($f)) return $_footerBase.$d.$slug.'.'.$e;
        }
    }
    return '';
};

/* Service fallback if a game has no header categories */
$_footerServicesByGame = [
    'league-of-legends' => ['boosting','win-boost','accounts','items','topups','coaching'],
    'valorant' => ['boosting','accounts','items','topups','coaching'],
    'teamfight-tactics' => ['boosting','accounts','coaching'],
    'call-of-duty' => ['boosting','accounts','items','topups'],
    'apex-legends' => ['accounts'],
    'arc-raiders' => ['accounts'],
    'fortnite' => ['accounts','items','topups'],
    'marvel-rivals' => ['accounts','topups'],
    'rocket-league' => ['accounts'],
    'overwatch-2' => ['accounts','boosting'],
    'genshin-impact' => ['accounts','items','topups'],
];
$_footerSvcDef = [
    'boosting'  => ['label' => t('Boosting'),      'path' => '/rank-boost/'],
    'win-boost' => ['label' => t('Win Boost'),     'path' => '/win-boost/'],
    'accounts'  => ['label' => t('Accounts'),      'path' => '/accounts/'],
    'items'     => ['label' => t('Items & Skins'), 'path' => '/items/'],
    'topups'    => ['label' => t('Top Up'),        'path' => '/top-ups/'],
    'coaching'  => ['label' => t('Coaching'),      'path' => '/coaching/'],
];

$_footerCols = 7;
$_footerInitial = $_footerCols * 2;
$_footerGameList = array_values($_footerGames);
$_footerTotal = count($_footerGameList);
$_footerHasMore = $_footerTotal > $_footerInitial;
?>
<footer>
    <!-- ── SERVICE PILLS ── -->
    <div class="footer-services">
        <a href="/services/boosting" class="footer-service-pill">
            <i class="fa-solid fa-rocket" aria-hidden="true"></i>
            <span><?= t('Boosting') ?></span>
        </a>
        <a href="/services/accounts" class="footer-service-pill">
            <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
            <span><?= t('Accounts') ?></span>
        </a>
        <a href="/digital-goods/ingame-currency" class="footer-service-pill">
            <i class="fa-solid fa-coins" aria-hidden="true"></i>
            <span><?= t('Currencies') ?></span>
        </a>
        <a href="/digital-goods" class="footer-service-pill">
            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
            <span><?= t('Top-ups') ?></span>
        </a>
        <a href="/services/items" class="footer-service-pill">
            <i class="fa-solid fa-gem" aria-hidden="true"></i>
            <span><?= t('Items') ?></span>
        </a>
        <a href="/services/coaching" class="footer-service-pill">
            <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
            <span><?= t('Coaching') ?></span>
        </a>
        <a href="/digital-goods" class="footer-service-pill">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
            <span><?= t('Digital Goods') ?></span>
        </a>
    </div>

    <div class="footer-main">
        <div class="footer-brand">
            <div class="footer-logo-row" onclick="window.location.href='<?= BASE_URL ?>'">
                <img src="<?= ASSET_URL ?>/website/images/logo-footer.webp" alt="LOLBOOST.GG" class="logo-footer">
            </div>
            <p class="footer-tagline"><?= t('Fast. Safe. Professional.') ?></p>
            <div class="footer-social">
                <a href="/discord"   aria-label="Discord"><i class="fab fa-discord"></i></a>
                <a href="/instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="/facebook"  aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="/tiktok"    aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>

        <div class="footer-nav">
            <div class="footer-col">
                <div class="footer-col-title"><?= t('Services') ?></div>
                <ul>
                    <li><a href="/services/boosting"><?= t('Boosting') ?></a></li>
                    <li><a href="/services/accounts"><?= t('Accounts') ?></a></li>
                    <li><a href="/services/items"><?= t('Items') ?></a></li>
                    <li><a href="/services/coaching"><?= t('Coaching') ?></a></li>
                    <li><a href="/digital-goods/ingame-currency"><?= t('Currencies') ?></a></li>
                    <li><a href="/digital-goods"><?= t('Top-ups') ?></a></li>
                    <li><a href="/digital-goods"><?= t('Digital Goods') ?></a></li>
                </ul>
            </div>
            <div class="footer-col">
                <div class="footer-col-title"><?= t('Earn & Join') ?></div>
                <ul>
                    <li><a href="/become-a-seller"><?= t('Become a Seller') ?></a></li>
                    <li><a href="/work-with-us"><?= t('Become a Booster') ?></a></li>
                    <li><a href="/work-with-us"><?= t('Become a GG Girl') ?></a></li>
                    <li><a href="/egirls"><?= t('Gamer Girls') ?></a></li>
                </ul>
            </div>
            <div class="footer-col">
                <div class="footer-col-title"><?= t('Company') ?></div>
                <ul>
                    <li><a href="/contact"><?= t('Contact 24/7') ?></a></li>
                    <li><a href="/blog"><?= t('Blog') ?></a></li>
                    <li><a href="/loyalty"><?= t('Loyalty') ?></a></li>
                    <li><a href="/boosters"><?= t('Our Boosters') ?></a></li>
                </ul>
            </div>
            <div class="footer-col">
                <div class="footer-col-title"><?= t('Legal') ?></div>
                <ul>
                    <li><a href="/legal/imprint"><?= t('Imprint') ?></a></li>
                    <li><a href="/legal/terms"><?= t('Terms of Service') ?></a></li>
                    <li><a href="/legal/privacy"><?= t('Privacy Policy') ?></a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ── GAMES SECTION ── -->
    <?php if (!empty($_footerGameList)): ?>
    <div class="footer-games-section">
        <div class="footer-games-grid" id="footerGamesGrid">
            <?php foreach ($_footerGameList as $_i => $_g):
                $_slug     = $_g['slug'];
                $_name     = $_g['name'];
                $_gameBase = $_footerBase . '/' . $_slug;
                $_iconSrc  = $_footerResolveIcon($_slug, $_g['iconRaw'] ?? '');
                $_cats     = is_array($_g['categories'] ?? null) ? $_g['categories'] : [];
                $_svcKeys  = !empty($_cats) ? array_keys($_cats) : ($_footerServicesByGame[$_slug] ?? ['accounts']);
                $_hidden   = ($_i >= $_footerInitial) ? ' footer-game-hidden' : '';
            ?>
            <div class="footer-game-col<?= $_hidden ?>" data-footer-game>
                <a href="<?= htmlspecialchars($_gameBase . '/') ?>" class="footer-game-title">
                    <?php if ($_iconSrc): ?>
                        <img src="<?= htmlspecialchars($_iconSrc) ?>" alt="<?= htmlspecialchars($_name) ?>" class="footer-game-icon" loading="lazy">
                    <?php else: ?>
                        <span class="footer-game-icon-fallback"><?= htmlspecialchars(mb_strtoupper(mb_substr($_name, 0, 1))) ?></span>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($_name) ?></span>
                </a>
                <ul>
                    <?php foreach ($_svcKeys as $_key):
                        $_cat = $_cats[$_key] ?? [];
                        $_label = $_cat['label'] ?? ($_footerSvcDef[$_key]['label'] ?? ucfirst(str_replace('-', ' ', (string)$_key)));
                        $_href = $_cat['href'] ?? ($_gameBase . ($_footerSvcDef[$_key]['path'] ?? ('/' . $_key . '/')));
                        $_seoLabel = $_name . ' ' . $_label;
                    ?>
                    <li><a href="<?= htmlspecialchars($_href) ?>"><?= htmlspecialchars($_seoLabel) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($_footerHasMore): ?>
        <div class="footer-games-showmore">
            <button type="button" class="footer-showmore-btn" id="footerShowMoreBtn" aria-expanded="false">
                <span class="footer-showmore-label"><?= t('Show All Games') ?></span>
                <i class="fa-solid fa-chevron-down footer-showmore-icon"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="footer-bottom">
        <div class="footer-bottom-left">
            <p class="footer-copy">&copy; <?= date('Y') ?> LB Gaming Services LTD. All rights reserved.</p>
            <p class="footer-riot"><?= t("LB Gaming Services LTD isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends &copy; Riot Games, Inc.") ?></p>
        </div>
    </div>

</footer>

<style>
footer {
    border-top: 1px solid rgba(96,165,250,.12);
    background:
        radial-gradient(780px 360px at 14% 0%, rgba(37,99,235,.22), transparent 66%),
        radial-gradient(680px 340px at 86% 16%, rgba(14,165,233,.13), transparent 68%),
        linear-gradient(180deg, #070b1d 0%, #050817 46%, #030511 100%) !important;
    background-color:#050817 !important;
}

footer .footer-main {
    display: flex;
    gap: 6vw;
    padding: 3.5vw 7.8125vw;
    align-items: flex-start;
}
footer .footer-brand { flex-shrink: 0; width: 19vw; min-width: 250px; }
footer .footer-logo-row {
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    gap: 0;
    cursor: pointer;
    margin: 0 0 18px 0;
    flex-wrap: nowrap;
    white-space: nowrap;
    line-height: 1;
}
footer .logo-footer {
    width: clamp(210px, 13.5vw, 285px) !important;
    height: auto !important;
    max-width: none !important;
    max-height: none !important;
    object-fit: contain;
    display: block;
    flex: 0 0 auto;
    margin: 0 !important;
}
footer .footer-brand-name { display: none !important; }
footer .footer-tagline {
    font-size: 1vw !important; color: rgba(255,255,255,.3) !important;
    width: auto !important; margin-bottom: 1.2vw; line-height: 1.5 !important;
}
footer .footer-social { display: flex; gap: 0.7vw; }
footer .footer-social a {
    width: 2.4vw; height: 2.4vw; border-radius: 50%;
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.09);
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.5); font-size: 0.9375vw; text-decoration: none;
    transition: background .15s, color .15s, border-color .15s;
}
footer .footer-social a:hover { background: rgba(59,130,246,.2); border-color: rgba(59,130,246,.35); color: #fff; }

footer .footer-nav { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2vw; flex: 1; }
footer .footer-col-title {
    font-size: 1vw; font-weight: 800; color: rgba(255,255,255,.85);
    text-transform: uppercase; letter-spacing: .08em; margin-bottom: 1vw;
}
footer .footer-col ul { padding: 0; margin: 0; list-style: none; }
footer .footer-col ul li { margin-bottom: 0.3vw; }
footer .footer-col ul li a {
    font-size: 0.9375vw; color: rgba(255,255,255,.38);
    text-decoration: none; transition: color .12s; line-height: 1.9;
}
footer .footer-col ul li a:hover { color: rgba(255,255,255,.85); }

footer .footer-services {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 22px 7.8125vw 26px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
footer .footer-service-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 11px 24px;
    border-radius: 999px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    text-decoration: none;
    color: rgba(255,255,255,.55);
    font-size: 14px;
    font-weight: 700;
    letter-spacing: .02em;
    transition: background .18s ease, border-color .18s ease, color .18s ease;
    white-space: nowrap;
}
footer .footer-service-pill:hover {
    background: rgba(59,130,246,.14);
    border-color: rgba(59,130,246,.35);
    color: rgba(255,255,255,.9);
}
footer .footer-service-pill i {
    font-size: 15px;
    opacity: .75;
    transition: opacity .18s ease;
}
footer .footer-service-pill:hover i { opacity: 1; }

/* ── GAMES SECTION ── */
footer .footer-games-section {
    padding: 36px 7.8125vw 40px;
    border-top: 1px solid rgba(255,255,255,.06);
}
footer .footer-games-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    column-gap: 22px;
    row-gap: 0;
    align-items: start;
}
footer .footer-game-hidden { display: none !important; }
footer .footer-games-grid:not(.is-expanded) > .footer-game-col:nth-child(n+15) { display: none !important; }
footer .footer-game-col { display: flex; flex-direction: column; min-width: 0; padding-bottom: 34px; }
footer .footer-game-title {
    display: flex; align-items: center; gap: 9px;
    text-decoration: none; margin-bottom: 12px; min-width: 0;
}
footer .footer-game-icon {
    width: 26px; height: 26px; flex: 0 0 26px;
    border-radius: 7px; object-fit: contain;
}
footer .footer-game-icon-fallback {
    width: 26px; height: 26px; flex: 0 0 26px; border-radius: 7px;
    background: linear-gradient(135deg, rgba(59,130,246,.5), rgba(14,165,233,.3));
    border: 1px solid rgba(255,255,255,.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 900; color: rgba(255,255,255,.8);
}
footer .footer-game-title span {
    font-size: clamp(13px, 0.78vw, 15px); font-weight: 800; color: rgba(255,255,255,.75);
    text-transform: uppercase; letter-spacing: .055em; line-height: 1.3;
    transition: color .12s; white-space: normal; word-break: break-word;
}
footer .footer-game-title:hover span { color: rgba(255,255,255,.98); }
footer .footer-game-col ul { padding: 0; margin: 0; list-style: none; }
footer .footer-game-col ul li { margin-bottom: 0; }
footer .footer-game-col ul li a {
    font-size: clamp(12px, 0.72vw, 14px); color: rgba(255,255,255,.32);
    text-decoration: none; transition: color .12s; line-height: 1.95; display: block;
}
footer .footer-game-col ul li a:hover { color: rgba(255,255,255,.75); }

footer .footer-games-showmore { display: flex; justify-content: center; margin-top: 28px; }
footer .footer-showmore-btn {
    display: inline-flex; align-items: center; gap: 9px;
    padding: 11px 28px; border-radius: 999px;
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
    color: rgba(255,255,255,.55); font-size: 13px; font-weight: 700; cursor: pointer;
    transition: background .18s, border-color .18s, color .18s; letter-spacing: .03em;
}
footer .footer-showmore-btn:hover { background: rgba(59,130,246,.12); border-color: rgba(59,130,246,.28); color: rgba(255,255,255,.88); }
footer .footer-showmore-icon { font-size: 11px; transition: transform .25s ease; }
footer .footer-showmore-btn[aria-expanded="true"] .footer-showmore-icon { transform: rotate(180deg); }

/* ── BOTTOM ── */
footer .footer-bottom { padding: 1.2vw 7.8125vw; border-top: 1px solid rgba(255,255,255,.06); }
footer .footer-bottom-left { display: flex; flex-direction: column; gap: 0.5vw; }
footer .footer-copy { font-size: 0.875vw !important; color: rgba(255,255,255,.35) !important; width: auto !important; line-height: 1.4 !important; }
footer .footer-riot { font-size: 0.75vw !important; color: rgba(255,255,255,.2) !important; width: auto !important; max-width: 70vw; line-height: 1.6 !important; }

/* hide old footer elements */
footer .content, footer .left, footer .right,
footer .footer-bottom .social-icons,
footer p:not(.footer-tagline):not(.footer-copy):not(.footer-riot) { display: none !important; }


footer .footer-games-section,
footer .footer-services,
footer .footer-bottom {
    background: transparent !important;
}
footer .footer-service-pill,
footer .footer-showmore-btn {
    background: rgba(15, 23, 42, .58);
    border-color: rgba(96,165,250,.14);
}
footer .footer-game-title:hover .footer-game-icon,
footer .footer-game-title:hover .footer-game-icon-fallback {
    box-shadow: 0 0 18px rgba(59,130,246,.28);
}
@media (max-width: 767px) {
    footer {
        background:
            radial-gradient(420px 300px at 24% 0%, rgba(37,99,235,.26), transparent 70%),
            linear-gradient(180deg, #080d22 0%, #050817 58%, #030511 100%) !important;
    }
}

/* ── TABLET ── */
@media (max-width: 1200px) and (min-width: 768px) {
    footer .footer-games-grid { grid-template-columns: repeat(4, 1fr); gap: 28px 20px; }
    footer .footer-games-grid:not(.is-expanded) > .footer-game-col:nth-child(n+9) { display: none !important; }
    footer .footer-game-title span { font-size: 1vw; }
    footer .footer-game-col ul li a { font-size: 0.9vw; }
    footer .footer-service-pill { font-size: 13px; padding: 10px 20px; }
}

/* ── MOBILE ── */
@media (max-width: 767px) {
    footer .footer-main { flex-direction: column; padding: 8vw 6vw; gap: 8vw; }
    footer .footer-brand { width: 100%; min-width: 0; }
    footer .footer-logo-row { gap: 0; margin-bottom: 4vw; }
    footer .logo-footer { width: min(62vw, 280px) !important; height: auto !important; max-width: none !important; max-height: none !important; margin: 0 !important; }
    footer .footer-brand-name { display: none !important; }
    footer .footer-tagline { font-size: 4vw !important; margin-bottom: 5vw; }
    footer .footer-social { gap: 3vw; }
    footer .footer-social a { width: 12vw; height: 12vw; font-size: 5vw; }
    footer .footer-nav { grid-template-columns: repeat(2, 1fr); gap: 6vw 4vw; width: 100%; }
    footer .footer-col-title { font-size: 4.2vw; margin-bottom: 3.5vw; }
    footer .footer-col ul li a { font-size: 3.8vw; line-height: 2.2; }
    footer .footer-col ul li { margin-bottom: 1vw; }

    footer .footer-services {
        flex-wrap: nowrap; justify-content: flex-start;
        overflow-x: auto; scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        padding: 14px 6vw 16px;
        scrollbar-width: none; -ms-overflow-style: none; gap: 8px;
    }
    footer .footer-services::-webkit-scrollbar { display: none; }
    footer .footer-service-pill {
        scroll-snap-align: start; flex-shrink: 0;
        font-size: 12px; padding: 9px 16px; gap: 8px;
    }

    footer .footer-games-section { padding: 24px 6vw 28px; }
    footer .footer-games-grid { grid-template-columns: repeat(2, 1fr); gap: 5vw 4vw; }
    footer .footer-games-grid:not(.is-expanded) > .footer-game-col:nth-child(n+5) { display: none !important; }
    footer .footer-game-title span { font-size: 3.6vw; }
    footer .footer-game-col ul li a { font-size: 3.2vw; }
    footer .footer-game-icon, footer .footer-game-icon-fallback { width: 22px; height: 22px; flex: 0 0 22px; }

    footer .footer-bottom { padding: 5vw 6vw; }
    footer .footer-copy { font-size: 3vw !important; }
    footer .footer-riot { font-size: 2.5vw !important; max-width: 100%; }
}
</style>

<style>
/* LOLBOOST blue footer safety layer */
footer {
    background:
        radial-gradient(780px 360px at 14% 0%, rgba(37,99,235,.22), transparent 66%),
        radial-gradient(680px 340px at 86% 16%, rgba(14,165,233,.13), transparent 68%),
        linear-gradient(180deg, #070b1d 0%, #050817 46%, #030511 100%) !important;
    background-color:#050817 !important;
}
footer .footer-logo-row .logo-footer {
    object-fit: contain;
}
footer .footer-brand-name {
    display: none !important;
}

/* Footer logo refinement */
footer .footer-logo-row {
    margin-bottom: 18px !important;
}
footer .footer-logo-row .logo-footer {
    width: clamp(210px, 13.5vw, 285px) !important;
    height: auto !important;
    max-width: none !important;
    max-height: none !important;
    margin: 0 !important;
    object-fit: contain !important;
}
footer .footer-brand-name { display: none !important; }
@media (max-width: 767px) {
    footer .footer-logo-row .logo-footer { width: min(62vw, 280px) !important; }
}

@media (max-width: 767px) {
    footer { margin-bottom: 0; }
}
</style>

<script>
(function(){
    var btn = document.getElementById('footerShowMoreBtn');
    var grid = document.getElementById('footerGamesGrid');
    if (!btn || !grid) return;

    function getCols(){
        var cols = window.getComputedStyle(grid).gridTemplateColumns.split(' ').filter(Boolean).length;
        return cols > 0 ? cols : 7;
    }

    function collapseGames(){
        var limit = getCols() * 2;
        grid.classList.remove('is-expanded');
        document.querySelectorAll('[data-footer-game]').forEach(function(el, i){
            el.classList.toggle('footer-game-hidden', i >= limit);
        });
        btn.setAttribute('aria-expanded', 'false');
        btn.querySelector('.footer-showmore-label').textContent = '<?= addslashes(t('Show All Games')) ?>';
    }

    function expandGames(){
        grid.classList.add('is-expanded');
        document.querySelectorAll('[data-footer-game]').forEach(function(el){
            el.classList.remove('footer-game-hidden');
        });
        btn.setAttribute('aria-expanded', 'true');
        btn.querySelector('.footer-showmore-label').textContent = '<?= addslashes(t('Show Less')) ?>';
    }

    collapseGames();

    btn.addEventListener('click', function(){
        if (btn.getAttribute('aria-expanded') === 'true') {
            collapseGames();
        } else {
            expandGames();
        }
    });

    window.addEventListener('resize', function(){
        if (btn.getAttribute('aria-expanded') !== 'true') collapseGames();
    }, { passive: true });
})();
</script>

<style>
/* FINAL PATCH: solid non transparent footer background */
footer,
footer .footer-services,
footer .footer-main,
footer .footer-games-section,
footer .footer-bottom {
    background: #050817 !important;
    background-image: none !important;
    background-color: #050817 !important;
}
footer {
    border-top: 1px solid rgba(96,165,250,.16) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.035) !important;
}
footer .footer-services,
footer .footer-games-section,
footer .footer-bottom {
    border-color: rgba(96,165,250,.10) !important;
}
@media (max-width: 767px) {
    footer,
    footer .footer-services,
    footer .footer-main,
    footer .footer-games-section,
    footer .footer-bottom {
        background: #050817 !important;
        background-image: none !important;
        background-color: #050817 !important;
    }
}
</style>
