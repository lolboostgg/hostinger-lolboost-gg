<?php
global $db;

// ── Currency ────────────────────────────────────────────────────────────────
$_gs_currency = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$_gs_symbol   = function_exists('util_format_currency_display')
    ? util_format_currency_display($_gs_currency)
    : ($_gs_currency === 'USD' ? '$' : '€');


// ── Top-ups service detection ────────────────────────────────────────────────
if (!function_exists('gs_game_public_slug')) {
    function gs_game_public_slug(string $game): string {
        $game = strtolower(trim($game));
        $map = [
            'lol' => 'league-of-legends',
            'league' => 'league-of-legends',
            'league-of-legends' => 'league-of-legends',
            'lol-classic' => 'lol-classic',
            'lol_classic' => 'lol-classic',
            'val' => 'valorant',
            'valorant' => 'valorant',
            'tft' => 'teamfight-tactics',
            'teamfight-tactics' => 'teamfight-tactics',
        ];
        return $map[$game] ?? $game;
    }
}

if (!function_exists('gs_service_card_kind')) {
    function gs_service_card_kind(array $card): string {
        $label = strtolower(trim((string)($card['label'] ?? '')));
        $href  = strtolower(trim((string)($card['href'] ?? '')));

        if (strpos($href, '/top-ups') !== false || strpos($href, '/topups') !== false || strpos($label, 'riot point') !== false || strpos($label, 'top') !== false) {
            return 'topups';
        }
        if (strpos($href, '/items') !== false || strpos($label, 'item') !== false || strpos($label, 'gifting') !== false) {
            return 'items';
        }
        if (strpos($href, '/premium-accounts') !== false || strpos($label, 'premium') !== false) {
            return 'premium_accounts';
        }
        if (strpos($href, '/smurf') !== false || strpos($label, 'smurf') !== false) {
            return 'smurf_accounts';
        }
        if (strpos($href, '/accounts') !== false || strpos($label, 'ranked account') !== false || strpos($label, 'account') !== false) {
            return 'ranked_accounts';
        }
        return 'default';
    }
}

if (!function_exists('gs_service_card_label')) {
    function gs_service_card_label(array $card, string $gameSlug = ''): string {
        switch (gs_service_card_kind($card)) {
            case 'topups':
                return $gameSlug === 'league-of-legends' ? 'Riot Points' : 'Top-ups';
            case 'items':
                return 'Items';
            case 'premium_accounts':
                return 'Smurf Accounts';
            case 'smurf_accounts':
                return 'Smurf Accounts';
            case 'ranked_accounts':
                return 'Ranked Accounts';
            default:
                return (string)($card['label'] ?? 'Service');
        }
    }
}

if (!function_exists('gs_service_card_icon')) {
    function gs_service_card_icon(array $card): string {
        switch (gs_service_card_kind($card)) {
            case 'topups':
                return 'fa-solid fa-coins';
            case 'items':
                return 'fa-solid fa-gem';
            case 'smurf_accounts':
            case 'premium_accounts':
                return 'fa-solid fa-user-ninja';
            case 'ranked_accounts':
                return 'fa-solid fa-helmet-battle';
            default:
                return (string)($card['fa_icon'] ?? 'fa-solid fa-box');
        }
    }
}

/**
 * Accent colour (as "R,G,B") per service type so each tile looks distinct.
 * Used via CSS custom property --gs-accent on the service tiles.
 */
if (!function_exists('gs_service_card_accent')) {
    function gs_service_card_accent(array $card): string {
        switch (gs_service_card_kind($card)) {
            case 'topups':            // Riot Points / Top-ups → orange
                return '245,158,11';
            case 'items':             // Items → red
                return '239,68,68';
            case 'ranked_accounts':   // Accounts → blue
            case 'premium_accounts':
            case 'smurf_accounts':
                return '56,150,255';
            default:                  // Fallback → purple (brand)
                return '124,107,255';
        }
    }
}

$gsGameSlug = gs_game_public_slug((string)($game ?? ''));
$gsIsLolClassic = in_array($gsGameSlug, ['lol-classic'], true)
    || in_array(strtolower((string)($game ?? '')), ['lol_classic', 'lol-classic', 'league-of-legends-classic'], true);

// LoL Classic is a boosting-only game hub. Its boost forms are rendered below
// in the same "Boosting services" section as the regular LoL page.
if ($gsIsLolClassic) {
    $cards = [];
}
$gsTopupsEnabled = false;
$gsGameId = 0;
if ($gsGameSlug !== '') {
    $gsGameRow = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gsGameSlug) ?: []) : [];
    if (!$gsGameRow && $gsGameSlug !== (string)($game ?? '') && function_exists('util_get_game_by_slug')) {
        $gsGameRow = util_get_game_by_slug((string)$game) ?: [];
    }
    $gsGameId = (int)($gsGameRow['id'] ?? 0);
    if ($gsGameId > 0 && function_exists('util_game_has_service')) {
        $gsTopupsEnabled = util_game_has_service($gsGameId, 'topups')
            || util_game_has_service($gsGameId, 'top-ups')
            || util_game_has_service($gsGameId, 'currencies');
    }
}
$gsTopupsHref = '/' . ($gsGameSlug !== '' ? $gsGameSlug : (string)$game) . '/top-ups';
$gsTopupsLabel = ($gsGameSlug === 'league-of-legends') ? 'Riot Points' : 'Top-ups';
$gsTopupsDescription = ($gsGameSlug === 'league-of-legends')
    ? 'Buy Riot Points with secure delivery'
    : 'Instantly top up your in-game balance';


// ── Game-specific FAQ from database ─────────────────────────────────────────
$gsFaq = [
    'eyebrow' => 'FAQ',
    'title' => 'Frequently Asked Questions',
    'lead' => 'Quick answers about ordering, delivery and support on the marketplace.',
    'aside_title' => 'Need help choosing?',
    'aside_text' => 'Tell us what you want to buy or improve and we will point you to the right service.',
    'aside_button' => 'Chat with support',
    'items' => [
        ['question' => 'How does ordering work?', 'answer' => 'Choose the service or listing you want, complete the checkout and follow the instructions in your order dashboard. You can track the order, message support and receive updates from one place.'],
        ['question' => 'Are sellers and boosters verified?', 'answer' => 'Boosters and marketplace sellers are reviewed before they can offer services. Listings are checked for quality and customers can see seller information, sales and trust badges before buying.'],
        ['question' => 'How fast will I receive my order?', 'answer' => 'Delivery depends on the selected service. Digital goods and many marketplace listings are usually delivered quickly, while boosting and coaching depend on your selected options and schedule.'],
        ['question' => 'Can I contact support after buying?', 'answer' => 'Yes. Support is available before and after the order. Use the order chat or live support if you need help with delivery, login details, scheduling or any questions about your purchase.'],
        ['question' => 'What happens if something is wrong with a listing?', 'answer' => 'Contact support through your order dashboard. We will review the case, contact the seller if needed and help resolve delivery issues as quickly as possible.'],
    ],
];

if ($gsGameId > 0) {
    try {
        $faqSettingsRows = $db->run(
            'SELECT eyebrow, title, lead, aside_title, aside_text, aside_button FROM game_faq_settings WHERE game_id = ? AND enabled = 1 LIMIT 1',
            $gsGameId
        ) ?: [];
        if (!empty($faqSettingsRows[0])) {
            foreach (['eyebrow','title','lead','aside_title','aside_text','aside_button'] as $faqKey) {
                if (trim((string)($faqSettingsRows[0][$faqKey] ?? '')) !== '') {
                    $gsFaq[$faqKey] = (string)$faqSettingsRows[0][$faqKey];
                }
            }
        }

        $faqItemRows = $db->run(
            'SELECT question, answer FROM game_faq_items WHERE game_id = ? AND enabled = 1 ORDER BY sort_order ASC, id ASC',
            $gsGameId
        ) ?: [];
        if (!empty($faqItemRows)) {
            $gsFaq['items'] = array_values(array_filter(array_map(static function (array $row): array {
                return [
                    'question' => trim((string)($row['question'] ?? '')),
                    'answer' => trim((string)($row['answer'] ?? '')),
                ];
            }, $faqItemRows), static function (array $row): bool {
                return $row['question'] !== '' && $row['answer'] !== '';
            }));
        }
    } catch (Throwable $e) {
        // The migration may not be installed yet. Keep the built-in fallback FAQ.
    }
}

// ── Premium Accounts only exist for LoL and Valorant ────────────────────────
// Other games keep Ranked Accounts, Items and Top-ups, but the old Premium
// Accounts entry is hidden from the game services overview.
$gsPremiumAccountGames = ['league-of-legends', 'valorant', 'lol', 'val'];
if (!in_array($gsGameSlug, $gsPremiumAccountGames, true) && !empty($cards) && is_array($cards)) {
    $cards = array_values(array_filter($cards, static function ($card): bool {
        $label = strtolower(trim((string)($card['label'] ?? '')));
        $href  = strtolower(trim((string)($card['href'] ?? '')));

        return ! (
            strpos($label, 'premium account') !== false
            || strpos($href, '/premium-accounts') !== false
            || strpos($href, '/premium-account') !== false
        );
    }));
}

if ($gsTopupsEnabled) {
    $gsHasTopupsCard = false;
    if (!empty($cards) && is_array($cards)) {
        foreach ($cards as $_gsCard) {
            $_href = (string)($_gsCard['href'] ?? '');
            $_label = strtolower((string)($_gsCard['label'] ?? ''));
            if (strpos($_href, '/top-ups') !== false || strpos($_label, 'top') !== false || strpos($_label, 'riot points') !== false) {
                $gsHasTopupsCard = true;
                break;
            }
        }
    } else {
        $cards = [];
    }
    if (!$gsHasTopupsCard) {
        $cards[] = [
            'href' => $gsTopupsHref,
            'icon' => '',
            'label' => $gsTopupsLabel,
            'description' => $gsTopupsDescription,
            'fa_icon' => 'fa-solid fa-coins',
            'is_topups' => true,
        ];
    }
}

// ── Seller-balanced distribution (same logic as shop pages) ─────────────────
function gs_balance_by_seller(array $rows): array {
    $buckets = []; $order = [];
    foreach ($rows as $row) {
        $sid = (int)($row['seller_id'] ?? 0);
        if (!isset($buckets[$sid])) { $buckets[$sid] = []; $order[] = $sid; }
        $buckets[$sid][] = $row;
    }
    $out = [];
    while (true) {
        $any = false;
        foreach ($order as $sid) { if (!empty($buckets[$sid])) { $any = true; break; } }
        if (!$any) break;
        $top = $order[0] ?? null;
        if ($top !== null && !empty($buckets[$top])) { $out[] = array_shift($buckets[$top]); }
        foreach ($order as $sid) {
            if ($sid === $top) continue;
            if (!empty($buckets[$sid])) { $out[] = array_shift($buckets[$sid]); }
        }
    }
    return $out;
}

$sellerSql = "s.username AS seller_username, s.icon AS seller_icon,
                  s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active,
                  (COALESCE(sas.account_sales, 0)
                   + COALESCE(sis.item_sales, 0)
                   + COALESCE(sts.topup_sales, 0)
                   + COALESCE(sdgs.digital_good_sales, 0)
                   + COALESCE(ss.admin_alias_sales, 0)) AS seller_total_sales";
$sellerSalesJoins = "LEFT JOIN seller_stats ss ON ss.seller_id = s.id
         LEFT JOIN (
             SELECT seller_id, COUNT(*) AS account_sales
             FROM selling_accounts
             WHERE sold = 1
             GROUP BY seller_id
         ) sas ON sas.seller_id = s.id
         LEFT JOIN (
             SELECT seller_id, COALESCE(SUM(sold_count), 0) AS item_sales
             FROM selling_items
             GROUP BY seller_id
         ) sis ON sis.seller_id = s.id
         LEFT JOIN (
             SELECT seller_id, COALESCE(SUM(sold_count), 0) AS topup_sales
             FROM selling_topups
             GROUP BY seller_id
         ) sts ON sts.seller_id = s.id
         LEFT JOIN (
             SELECT seller_id, COALESCE(SUM(sold_count), 0) AS digital_good_sales
             FROM digital_goods
             GROUP BY seller_id
         ) sdgs ON sdgs.seller_id = s.id";

// Every game stores its listings under a slightly different `game` value
// (short code, public slug, or a legacy alias), so collect them all.
$gsGameAliases = [strtolower(trim((string)$game)), strtolower(trim((string)$gsGameSlug))];
$gsGameAliasMap = [
    'lol' => ['lol', 'league-of-legends', 'league', 'leagu'],
    'val' => ['val', 'valorant', 'valor'],
    'tft' => ['tft', 'teamfight-tactics', 'teamf'],
    'cod' => ['cod', 'call-of-duty', 'callofduty'],
];
foreach ($gsGameAliasMap as $_aliasKey => $_aliasList) {
    if (in_array($_aliasKey, $gsGameAliases, true) || array_intersect($gsGameAliases, $_aliasList)) {
        $gsGameAliases = array_merge($gsGameAliases, $_aliasList);
    }
}
$gsGameAliases = array_values(array_unique(array_filter($gsGameAliases, static fn($v) => $v !== '')));
$gsGameAliasSql = implode(',', array_map(static fn($v) => "'" . esc($v) . "'", $gsGameAliases));
$gsIsLolHub = (bool) array_intersect($gsGameAliases, ['lol', 'league-of-legends']);

// ── Accounts ─────────────────────────────────────────────────────────────────
$featuredAccounts = [];
if (!empty($gsGameAliasSql)) {
    // Legacy LoL listings can have an empty/NULL game column.
    $gw = "LOWER(TRIM(COALESCE(sa.game,''))) IN ({$gsGameAliasSql})";
    if ($gsIsLolHub) $gw = "(sa.game IS NULL OR TRIM(sa.game) = '' OR {$gw})";

    $rows = $db->run(
        "SELECT sa.id, sa.title, sa.slug, sa.server, sa.current_rank, sa.current_division,
                sa.current_lp, sa.images, sa.description, sa.champions, sa.skins,
                sa.level, sa.blue_essence, sa.riot_points, sa.price, sa.delivery_type,
                sa.seller_id, sa.game, sa.game_data, sa.rank, sa.rank_label, {$sellerSql}
         FROM selling_accounts sa
         LEFT JOIN sellers s ON s.id = sa.seller_id
         {$sellerSalesJoins}
         WHERE {$gw} AND sa.sold = 0 AND sa.active = 1
         ORDER BY seller_total_sales DESC, sa.id DESC
         LIMIT 18"
    ) ?: [];
    $featuredAccounts = array_slice(gs_balance_by_seller($rows), 0, 6);
}

// ── Items ─────────────────────────────────────────────────────────────────────
$featuredItems = [];
if (!empty($gsGameAliasSql)) {
    $itemsScopeSql = "((? > 0 AND si.game_id = ?) OR ((si.game_id IS NULL OR si.game_id = 0) AND LOWER(TRIM(COALESCE(si.game,''))) IN ({$gsGameAliasSql})))";
    $rows = $db->run(
        "SELECT si.*, {$sellerSql}
         FROM selling_items si
         LEFT JOIN sellers s ON s.id = si.seller_id
         {$sellerSalesJoins}
         WHERE si.active = 1 AND {$itemsScopeSql}
         ORDER BY seller_total_sales DESC, si.id DESC
         LIMIT 18",
        $gsGameId, $gsGameId
    ) ?: [];
    $featuredItems = array_slice(gs_balance_by_seller($rows), 0, 6);
}

// ── Blog articles for this game ───────────────────────────────────────────────
// Tagged via articles.game_id in the admin article editor. The column is created
// lazily there, so guard the query for installs that have not written one yet.
$gsArticles = [];
if ($gsGameId > 0) {
    try {
        $gsArticles = $db->run(
            "SELECT id, title, slug, excerpt, description, image_url, updated_at
             FROM articles
             WHERE game_id = ?
             ORDER BY updated_at DESC
             LIMIT 3",
            $gsGameId
        ) ?: [];
    } catch (\Throwable $e) {
        $gsArticles = [];
    }
}

$gameNames = ['lol' => 'League of Legends', 'lol_classic' => 'LoL Classic', 'lol-classic' => 'LoL Classic', 'val' => 'Valorant', 'tft' => 'Teamfight Tactics'];
$gameName  = $gameNames[$game] ?? '';
if ($gameName === '') {
    // Dynamically added games: use the real name from the games table.
    $gameName = is_array($gsGameRow ?? null) ? trim((string)($gsGameRow['name'] ?? '')) : '';
}
if ($gameName === '') {
    $gameName = ucwords(str_replace('-', ' ', $gsGameSlug !== '' ? $gsGameSlug : (string)$game));
}

// ── Featured boosters, exact data source used by /boosters/ ─────────────────
$gsBoosterGames = ['lol', 'val', 'tft', 'lol_classic', 'lol-classic'];
$gsFeaturedBoosters = [];
$gsCurrentBoosterGame = strtolower(trim((string)$game));
$gsBoosterProfileGame = in_array($gsCurrentBoosterGame, ['lol_classic', 'lol-classic'], true) ? 'lol' : $gsCurrentBoosterGame;

if (in_array($gsCurrentBoosterGame, $gsBoosterGames, true)) {
    try {
        $gsBoosterRows = $db->run("
            SELECT
                boosters.*,
                booster_ranks.name AS rank_name,
                booster_profiles.*,
                boosters.id AS booster_id,
                COUNT(orders.id) AS completed_orders,
                COALESCE(rev.review_count, 0) AS review_count
            FROM boosters
            INNER JOIN booster_profiles ON boosters.id = booster_profiles.booster_id
            LEFT JOIN booster_ranks ON boosters.rank_id = booster_ranks.id
            LEFT JOIN orders ON boosters.id = orders.booster_id AND orders.status = 'COMPLETED'
            LEFT JOIN (
                SELECT booster_id, COUNT(*) AS review_count
                FROM reviews
                WHERE approved = 1
                GROUP BY booster_id
            ) rev ON rev.booster_id = boosters.id
            WHERE boosters.is_banned = 0
              AND (boosters.is_egirl IS NULL OR boosters.is_egirl = 0)
              AND booster_profiles.champions IS NOT NULL
              AND booster_profiles.roles IS NOT NULL
              AND boosters.show_profile = 1
            GROUP BY boosters.id
            ORDER BY completed_orders DESC, boosters.id ASC
            LIMIT 60
        ") ?: [];

        $gsMatchingBoosters = [];
        foreach ($gsBoosterRows as $gsBoosterRow) {
            $gsGames = array_values(array_filter(array_map('trim', explode('|', strtolower((string)($gsBoosterRow['games'] ?? 'lol'))))));
            if (empty($gsGames)) $gsGames = ['lol'];
            if (in_array($gsBoosterProfileGame, $gsGames, true)) $gsMatchingBoosters[] = $gsBoosterRow;
        }

        $gsFeaturedBoosters = array_slice($gsMatchingBoosters, 0, 12);
    } catch (Throwable $e) {
        $gsFeaturedBoosters = [];
    }
}
$faqPaths  = ['lol' => 'website/components/faqs/forms/rank',
               'lol_classic' => 'website/components/faqs/forms/lol-classic/rank',
               'lol-classic' => 'website/components/faqs/forms/lol-classic/rank',
               'val' => 'website/components/faqs/forms/val/rank',
               'tft' => 'website/components/faqs/forms/tft/rank'];
$faqPath   = $faqPaths[$game] ?? null;
$bgBase    = rtrim(ASSET_URL, '/') . '/images/boost-forms/';
$boostBgs  = ['rank-boost'=>'header-1.webp','win-boost'=>'header-2.webp',
               'placements-boost'=>'header-3.webp','placement-boost'=>'header-3.webp',
               'arena-boost'=>'header-1.webp','coaching'=>'header-2.webp',
               'normal-matches'=>'header-3.webp','champion-mastery'=>'header-1.webp',
               'clash-boost'=>'header-2.webp','level-boost'=>'header-3.webp',
               'pro-games'=>'header-1.webp','duo-pass'=>'header-2.webp',
               'double-up'=>'header-3.webp','rank'=>'header-1.webp',
               'win'=>'header-2.webp','placement'=>'header-3.webp'];
?>
<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'game-hub']) ?>

<?php $this->start('styles') ?>
<style>
/* ═══════════════════════════════════════════════
   GAME SERVICES, DYNAMIC LANDING REDESIGN
═══════════════════════════════════════════════ */
.gs{width:min(1500px,calc(100vw - 28px));margin:0 auto;padding:34px 0 80px;color:#fff}
body.game-hub{background:#0e0c1c}

.gs-hero{position:relative;margin-top:var(--gs-hero-nav-gap,0px)!important;overflow:hidden;color:#fff}
.gs-hero__inner{width:min(1500px,calc(100vw - 28px));margin:0 auto;min-height:330px;padding:48px 0 38px;display:grid;grid-template-columns:minmax(0,1fr) 430px;gap:42px;align-items:center;position:relative}
.gs-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(760px 300px at 23% 25%,rgba(96,104,255,.22),transparent 65%),radial-gradient(700px 330px at 80% 20%,rgba(0,194,255,.11),transparent 68%),linear-gradient(180deg,rgba(255,255,255,.025),transparent 72%);pointer-events:none}
.gs-hero::after{content:'';position:absolute;left:0;right:0;bottom:0;height:1px;background:linear-gradient(90deg,transparent,rgba(124,107,255,.28),transparent)}
.gs-hero__main{position:relative;z-index:2;display:grid;grid-template-columns:82px minmax(0,1fr);gap:22px;align-items:start}
.gs-hero__icon{width:82px;height:82px;border-radius:25px;background:linear-gradient(180deg,rgba(255,255,255,.10),rgba(255,255,255,.035));border:1px solid rgba(255,255,255,.13);display:flex;align-items:center;justify-content:center;box-shadow:0 24px 70px rgba(0,0,0,.34),0 0 0 8px rgba(124,107,255,.045);overflow:hidden}
.gs-hero__icon img{width:52px;height:52px;border-radius:15px;object-fit:cover;display:block}.gs-hero__icon i{font-size:34px;color:#8b82ff}
.gs-hero__kicker{display:inline-flex;align-items:center;gap:8px;margin:0 0 12px;font-size:12px;font-weight:950;letter-spacing:.14em;text-transform:uppercase;color:#8b9bff}.gs-hero__kicker:before{content:'';width:7px;height:7px;border-radius:99px;background:#6d75ff;box-shadow:0 0 18px rgba(109,117,255,.75)}
.gs-hero__title{margin:0;font-size:46px;line-height:1.02;font-weight:1000;letter-spacing:-.055em;color:#fff;text-shadow:0 18px 48px rgba(0,0,0,.35)}
.gs-hero__desc{margin:14px 0 0;max-width:780px;font-size:16px;line-height:1.65;color:rgba(225,231,255,.66);font-weight:650}
.gs-hero__actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}.gs-hero__pill{height:46px;border-radius:999px;display:inline-flex;align-items:center;gap:9px;padding:0 18px;text-decoration:none;color:#fff;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.10);font-size:15px;font-weight:850;transition:.18s}.gs-hero__pill:hover{transform:translateY(-2px);background:rgba(109,117,255,.14);border-color:rgba(129,140,248,.38)}.gs-hero__pill i{color:#aeb7ff;font-size:15px}.gs-hero__pill--primary{background:linear-gradient(90deg,#5b62f6,#6d75ff);border-color:rgba(157,166,255,.40);box-shadow:0 16px 34px rgba(91,98,246,.24)}.gs-hero__pill--primary i{color:#fff}
.gs-hero__showcase{position:relative;z-index:2;min-height:245px;display:flex;align-items:center;justify-content:center;overflow:visible}.gs-hero__showcase:before{content:'';position:absolute;width:330px;height:330px;border-radius:999px;background:radial-gradient(circle,rgba(109,117,255,.18),transparent 66%);filter:blur(3px);opacity:.95}.gs-hero__showcase:after{content:'';position:absolute;width:238px;height:238px;border-radius:999px;border:1px solid rgba(129,140,248,.20);box-shadow:inset 0 0 55px rgba(109,117,255,.075),0 0 70px rgba(0,194,255,.055)}.gs-hero__orbit{position:relative;width:330px;height:230px}.gs-hero__orbit-core{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:116px;height:116px;border-radius:34px;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(255,255,255,.085),rgba(255,255,255,.025));border:1px solid rgba(255,255,255,.12);box-shadow:0 22px 70px rgba(0,0,0,.35),0 0 0 12px rgba(109,117,255,.035)}.gs-hero__orbit-core i{font-size:42px;color:#fff}.gs-hero__orbit-core span{position:absolute;inset:-8px;border-radius:40px;border:1px solid rgba(129,140,248,.16)}.gs-hero__proof{position:absolute;display:inline-flex;align-items:center;gap:9px;height:42px;padding:0 14px;border-radius:999px;background:rgba(15,17,31,.74);border:1px solid rgba(255,255,255,.10);box-shadow:0 14px 36px rgba(0,0,0,.24);backdrop-filter:blur(12px);color:#fff;font-size:12px;font-weight:950;white-space:nowrap}.gs-hero__proof i{color:#aeb7ff;font-size:13px}.gs-hero__proof--one{left:0;top:26px}.gs-hero__proof--two{right:0;top:86px}.gs-hero__proof--three{left:34px;bottom:24px}.gs-hero__proof small{font-size:10px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.42)}

.gs-sec{margin-bottom:58px}
.gs-acc,.gs-items,.gs-proof,.gs-faq{content-visibility:auto;contain-intrinsic-size:1px 620px}.gs-sec-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:22px}.gs-sec-head h2{font-size:28px;font-weight:1000;margin:0;letter-spacing:-.035em}.gs-sec-lead{margin:7px 0 0;color:rgba(255,255,255,.48);font-size:14px;font-weight:650}.gs-sec-head__r{display:flex;align-items:center;gap:10px}.gs-showall{font-size:13px;font-weight:850;color:rgba(255,255,255,.7);text-decoration:none;padding:9px 16px;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);transition:.15s}.gs-showall:hover{color:#fff;border-color:rgba(129,140,248,.45);background:rgba(99,102,241,.14)}.gs-arr{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#fff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:.15s;padding:0;font-size:13px}.gs-arr:hover{background:rgba(99,102,241,.22);border-color:rgba(129,140,248,.4)}.gs-sw{position:relative}.gs-sl{display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none;padding-bottom:4px;padding-top:10px}.gs-sl::-webkit-scrollbar{display:none}
/* ═══════════════════════════════════════════════════════════
   BOOSTER CARD — redesigned to match egirl card layout
   ═══════════════════════════════════════════════════════════ */

.gs-boosters .cover-link {
    display:block; text-decoration:none; color:inherit;
    min-width:0; max-width:100%; width:100%;
}
.gs-boosters .cover-link:hover { text-decoration:none; color:inherit; }

.booster-card {
    display:flex; flex-direction:column;
    background:#050713;
    border:1px solid rgba(99,102,241,.18);
    border-radius:20px; overflow:visible;
    position:relative; height:100%;
    min-width:0; max-width:100%;
    transition:transform .28s cubic-bezier(.22,1,.36,1), border-color .25s, box-shadow .25s;
}
.gs-boosters .cover-link:hover .booster-card {
    transform:translateY(-7px);
    border-color:rgba(99,102,241,.5);
    box-shadow:0 22px 52px rgba(99,102,241,.2), 0 0 0 1px rgba(124,58,237,.12);
}

/* Cover */
.booster-card .cover {
    position:relative; width:100%; height:160px; flex-shrink:0;
    border-radius:20px 20px 0 0; overflow:hidden;
    background-size:cover; background-position:center top; background-color:#1a0530;
    transition:background-position .45s ease;
}
.gs-boosters .cover-link:hover .booster-card .cover { background-position:center 20%; }
.booster-card .cover::after {
    content:''; position:absolute; inset:0; z-index:1; pointer-events:none;
    background:linear-gradient(to bottom, rgba(15,11,30,0) 15%, rgba(15,11,30,.55) 100%);
}

/* Status pill — top left of cover (like egirls) */
.booster-card .cover-status {
    position:absolute; top:12px; left:12px; z-index:5;
    display:inline-flex; align-items:center; gap:7px;
    padding:6px 15px; border-radius:999px;
    font-size:14px; font-weight:700; letter-spacing:.03em;
    backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
    white-space:nowrap;
}
.booster-card .cover-status.online  { background:rgba(34,197,94,.15); border:1px solid rgba(34,197,94,.35); color:#4ade80; }
.booster-card .cover-status.offline { background:rgba(0,0,0,.50); border:1px solid rgba(255,255,255,.1); color:rgba(255,255,255,.4); }
.booster-card .cover-status .sdot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.booster-card .cover-status.online  .sdot { background:#22c55e; box-shadow:0 0 0 2px rgba(34,197,94,.25); animation:bcPulse 2s ease-in-out infinite; }
.booster-card .cover-status.offline .sdot { background:rgba(255,255,255,.25); }

/* Games icons + Rank — top right of cover, stacked column: games on top, rank below */
.booster-card .cover-games {
    position:absolute; top:12px; right:12px; z-index:5;
    display:flex; flex-direction:column; align-items:flex-end; gap:6px;
}
/* Rank box */
.booster-card .rank-box {
    width:50px; height:50px; border-radius:12px;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
    display:flex; align-items:center; justify-content:center;
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    transition:border-color .15s;
}
.gs-boosters .cover-link:hover .booster-card .rank-box { border-color:rgba(99,102,241,.5); }
.booster-card .rank-box .rank_icon { width:38px; height:38px; object-fit:contain; display:block; }

/* Game icon pills */
.booster-card .cover-game-icons { display:flex; gap:5px; }
.booster-card .cover-game-icon {
    width:34px; height:34px; border-radius:9px;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
    display:inline-flex; align-items:center; justify-content:center;
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    overflow:hidden; transition:border-color .15s;
}
.gs-boosters .cover-link:hover .booster-card .cover-game-icon { border-color:rgba(99,102,241,.4); }
.booster-card .cover-game-icon img { width:22px; height:22px; object-fit:contain; display:block; }

/* Tooltips — shown/hidden via JS, not CSS :hover */
.booster-card .champs-tooltip,
.booster-card .langs-tooltip {
    display:none;
    position:absolute;
    bottom:calc(100% + 10px);
    left:50%; transform:translateX(-50%);
    background:linear-gradient(160deg, #1a0f35 0%, #0f0b22 100%);
    border:1px solid rgba(99,102,241,.4);
    border-radius:14px;
    padding:12px 14px;
    z-index:200;
    min-width:180px;
    box-shadow:0 16px 48px rgba(0,0,0,.8), 0 0 0 1px rgba(99,102,241,.08);
    white-space:nowrap;
    pointer-events:none;
    /* Arrow pointing down */
}
.booster-card .champs-tooltip::after,
.booster-card .langs-tooltip::after {
    content:'';
    position:absolute;
    bottom:-6px; left:50%; transform:translateX(-50%);
    width:10px; height:10px;
    background:#1a0f35;
    border-right:1px solid rgba(99,102,241,.4);
    border-bottom:1px solid rgba(99,102,241,.4);
    transform:translateX(-50%) rotate(45deg);
}

/* Avatar */
.booster-card .avatar {
    position:absolute; top:116px; left:18px; z-index:10;
    width:88px; height:88px; border-radius:50%;
    border:4px solid #0f0b1e; overflow:visible; background:#2a1040;
    box-shadow:0 6px 20px rgba(0,0,0,.65), 0 0 0 1px rgba(99,102,241,.3);
    transition:box-shadow .25s;
}
.gs-boosters .cover-link:hover .booster-card .avatar { box-shadow:0 8px 28px rgba(0,0,0,.65), 0 0 0 2px rgba(99,102,241,.55); }
.booster-card .avatar img { width:100%; height:100%; object-fit:cover; object-position:center top; display:block; border-radius:50%; }

/* Avatar online dot */
.booster-card .booster-online-dot {
    position:absolute; bottom:4px; right:4px;
    width:16px; height:16px; border-radius:50%;
    border:3px solid #0f0b1e; z-index:11;
}
.booster-card .booster-online-dot.online  { background:#22c55e; }
.booster-card .booster-online-dot.offline { background:#4b5563; }

/* Details body */
.booster-card .details {
    padding:54px 20px 20px;
    display:flex; flex-direction:column; gap:10px; flex:1;
    border-radius:0 0 20px 20px; overflow:visible;
}

/* Top — name + status */
.booster-card .details .top { display:flex; flex-direction:column; gap:6px; }

.booster-card h5 {
    margin:0; display:flex; align-items:center; gap:8px; flex-wrap:wrap; min-width:0;
    font-size:22px; font-weight:800; color:#e0e7ff; letter-spacing:-.3px; line-height:1.2;
}
.booster-card .name-text { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0; }
.booster-card .verify-icon { color:#818cf8; font-size:18px; filter:drop-shadow(0 0 5px rgba(99,102,241,.5)); flex-shrink:0; }

/* Rating badge */
.booster-card .rating-badge {
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(0,0,0,.4); border:1px solid rgba(255,255,255,.08);
    border-radius:10px; padding:4px 11px;
    font-size:15px; font-weight:700; color:#e0e7ff; flex-shrink:0;
}
.booster-card .rating-badge img { width:15px; height:15px; flex-shrink:0; }
.booster-card .review-count { color:rgba(255,255,255,.4); font-weight:500; }

/* Online badge */
.booster-card h6 { margin:0; }
.booster-card .booster-online-badge {
    display:inline-flex; align-items:center; gap:7px;
    padding:5px 14px; border-radius:999px;
    font-size:14px; font-weight:700; align-self:flex-start;
}
.booster-card .booster-online-badge.online  { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#4ade80; }
.booster-card .booster-online-badge.offline { background:rgba(0,0,0,.35); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.35); }
.booster-card .booster-online-badge .dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.booster-card .booster-online-badge.online  .dot { background:#22c55e; box-shadow:0 0 0 2px rgba(34,197,94,.25); animation:bcPulse 2s ease-in-out infinite; }
.booster-card .booster-online-badge.offline .dot { background:rgba(255,255,255,.25); }
@keyframes bcPulse {
    0%,100% { box-shadow:0 0 0 2px rgba(34,197,94,.25); }
    50%      { box-shadow:0 0 0 5px rgba(34,197,94,.06); }
}

/* Mid — role icons */
.booster-card .mid { display:flex; align-items:center; flex-wrap:wrap; gap:6px; }
.booster-card .role-icon {
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:9px;
    background:rgba(10,10,24,.45); border:1px solid rgba(255,255,255,.13);
    box-shadow:0 2px 8px rgba(0,0,0,.45); overflow:hidden;
}
.booster-card .role-icon img { width:22px; height:22px; object-fit:contain; display:block; }

/* Bottom — champions + languages */
.booster-card .bottom {
    display:flex; align-items:center; justify-content:space-between;
    gap:10px; flex-wrap:wrap; margin-top:auto;
    padding-top:12px; border-top:1px solid rgba(255,255,255,.06);
}

/* Champions */
.booster-card .champions { display:flex; align-items:center; flex-wrap:wrap; gap:5px; }
.booster-card .champion-icon {
    width:34px; height:34px; border-radius:9px;
    background:rgba(10,10,24,.45); border:1px solid rgba(255,255,255,.13);
    box-shadow:0 2px 8px rgba(0,0,0,.45); padding:3px; object-fit:contain;
}
.booster-card .more-champions-icon {
    position:relative; display:inline-flex; align-items:center;
    background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3);
    border-radius:8px; padding:4px 10px; font-size:13px; font-weight:700;
    color:rgba(129,140,248,.9); cursor:default;
    transition:background .15s, border-color .15s;
}
.booster-card .more-champions-icon:hover {
    background:rgba(99,102,241,.25); border-color:rgba(99,102,241,.5);
}

/* Languages */
.booster-card .languages { display:flex; align-items:center; flex-wrap:wrap; gap:5px; }
.booster-card .lang-icon {
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:9px;
    background:rgba(10,10,24,.45); border:1px solid rgba(255,255,255,.13);
    box-shadow:0 2px 8px rgba(0,0,0,.45); overflow:hidden;
}
.booster-card .lang-icon img { width:24px; height:17px; object-fit:cover; display:block; border-radius:3px; }
.booster-card .more-lang-icon {
    position:relative; display:inline-flex; align-items:center;
    background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3);
    border-radius:8px; padding:4px 10px; font-size:13px; font-weight:700;
    color:rgba(129,140,248,.9); cursor:default;
    transition:background .15s, border-color .15s;
}
.booster-card .more-lang-icon:hover {
    background:rgba(99,102,241,.25); border-color:rgba(99,102,241,.5);
}

/* ── GLOBAL FLOATING TOOLTIP ── */
#bc-global-tooltip {
    display:none;
    position:fixed;
    z-index:99999;
    pointer-events:none;
    background:linear-gradient(160deg,#1c1040 0%,#120828 100%);
    border:1px solid rgba(99,102,241,.5);
    border-radius:14px;
    padding:12px 14px;
    min-width:260px;
    max-width:320px;
    box-shadow:0 20px 60px rgba(0,0,0,.85), 0 0 0 1px rgba(99,102,241,.1);
    transform:translateY(-8px);
    transition:opacity .15s ease, transform .15s ease;
    opacity:0;
}
#bc-global-tooltip.is-visible {
    display:block;
    opacity:1;
    transform:translateY(0);
}
/* Arrow */
#bc-global-tooltip::after {
    content:'';
    position:absolute;
    bottom:-7px; left:50%; transform:translateX(-50%) rotate(45deg);
    width:12px; height:12px;
    background:#1c1040;
    border-right:1px solid rgba(99,102,241,.5);
    border-bottom:1px solid rgba(99,102,241,.5);
}
#bc-global-tooltip .bc-tt-title {
    display:block; font-size:10px; font-weight:800;
    text-transform:uppercase; letter-spacing:.12em;
    color:rgba(129,140,248,.55); margin-bottom:10px;
    padding-bottom:8px; border-bottom:1px solid rgba(99,102,241,.12);
}
#bc-global-tooltip .bc-tt-list {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:5px;
}
#bc-global-tooltip .bc-tt-item {
    display:flex; align-items:center; gap:10px;
    font-size:13px; font-weight:600; color:rgba(220,220,255,.9);
    padding:4px 5px; border-radius:8px;
}
#bc-global-tooltip .bc-tt-item img {
    width:28px; height:28px; border-radius:8px;
    object-fit:contain; background:rgba(0,0,0,.35);
    padding:2px; border:1px solid rgba(255,255,255,.08);
}
#bc-global-tooltip .bc-tt-item span { line-height:1; }

.gs-boosters .no-boosters { grid-column:1/-1; text-align:center; padding:60px 20px; color:rgba(255,255,255,.35); font-size:17px; }

/* ── Val rank badge in mid section ── */
.booster-card .bc-val-rank-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:8px;
    font-size:12px; font-weight:800; letter-spacing:.05em; text-transform:uppercase;
    white-space:nowrap;
}
.booster-card .bc-val-rank-badge img {
    width:20px; height:20px; object-fit:contain; flex-shrink:0;
}
/* Tier colors */
.booster-card .bc-val-rank-badge.tier-0                        { background:rgba(150,150,150,.15); border:1px solid rgba(150,150,150,.3); color:rgba(200,200,200,.7); }
.booster-card .bc-val-rank-badge.tier-1                        { background:rgba(109,99,94,.2);   border:1px solid rgba(109,99,94,.4);   color:#a0938c; }
.booster-card .bc-val-rank-badge.tier-2                        { background:rgba(205,127,50,.15); border:1px solid rgba(205,127,50,.35); color:#cd7f32; }
.booster-card .bc-val-rank-badge.tier-3                        { background:rgba(192,192,192,.15);border:1px solid rgba(192,192,192,.35);color:#c0c0c0; }
.booster-card .bc-val-rank-badge.tier-4                        { background:rgba(255,215,0,.12);  border:1px solid rgba(255,215,0,.3);   color:#ffd700; }
.booster-card .bc-val-rank-badge.tier-5                        { background:rgba(0,200,150,.12);  border:1px solid rgba(0,200,150,.3);   color:#00c896; }
.booster-card .bc-val-rank-badge.tier-6                        { background:rgba(100,150,255,.12);border:1px solid rgba(100,150,255,.3); color:#6496ff; }
.booster-card .bc-val-rank-badge.tier-7                        { background:rgba(180,100,255,.12);border:1px solid rgba(180,100,255,.3); color:#b464ff; }
.booster-card .bc-val-rank-badge.tier-8                        { background:rgba(255,80,80,.12);  border:1px solid rgba(255,80,80,.3);   color:#ff5050; }
.booster-card .bc-val-rank-badge.tier-9                        { background:rgba(255,180,0,.15);  border:1px solid rgba(255,180,0,.4);   color:#ffb400; }
.booster-card .bc-val-rank-badge.tier-tft                      { background:rgba(32,191,191,.12); border:1px solid rgba(32,191,191,.3);  color:#20bfbf; }

/* Val rank text in rank-box */
.booster-card .rank-box .rank-text-badge {
    font-size:9px; font-weight:900; text-transform:uppercase;
    letter-spacing:.05em; text-align:center; line-height:1.2;
    padding:2px 4px; border-radius:5px; word-break:break-word;
}
.booster-card .rank-box .rank-text-badge.val { color:#ff4655; }

/* Game tags (fallback) */
.booster-card .bc-game-tag {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:8px; font-size:12px; font-weight:700;
    letter-spacing:.04em; text-transform:uppercase;
}
.booster-card .bc-game-tag img { width:16px; height:16px; object-fit:contain; }
.booster-card .bc-game-tag.val { background:rgba(255,70,85,.12); border:1px solid rgba(255,70,85,.3); color:#ff4655; }
.booster-card .bc-game-tag.tft { background:rgba(32,191,191,.12); border:1px solid rgba(32,191,191,.3); color:#20bfbf; }
.gs-boosters{content-visibility:auto;contain-intrinsic-size:1px 620px}
.gs-booster-slider{position:relative;overflow:hidden}
.gs-booster-grid{display:flex;gap:18px;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;padding:10px 2px 24px}
.gs-booster-grid::-webkit-scrollbar{display:none}
.gs-booster-grid>.cover-link{flex:0 0 410px;min-width:410px;scroll-snap-align:start}
.gs-booster-controls{display:flex;align-items:center;gap:8px}
@media(max-width:760px){.gs-booster-grid{gap:12px}.gs-booster-grid>.cover-link{flex-basis:calc(100vw - 44px);min-width:calc(100vw - 44px)}.gs-booster-controls .gs-arr{display:none}}



/* Choose path, dynamic service lanes */
.gs-paths{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(310px,1fr));
  gap:14px;
  border:0;
  border-radius:0;
  overflow:visible;
  background:transparent;
  box-shadow:none;
}
.gs-path{
  position:relative;
  min-height:104px;
  padding:17px 18px;
  text-decoration:none;
  color:#fff;
  background:
    radial-gradient(220px 120px at 92% 0%,rgba(124,107,255,.16),transparent 68%),
    linear-gradient(135deg,rgba(255,255,255,.055),rgba(255,255,255,.024));
  border:1px solid rgba(255,255,255,.09);
  border-radius:22px;
  overflow:hidden;
  transition:.18s ease;
  display:grid;
  grid-template-columns:52px minmax(0,1fr) auto;
  gap:15px;
  align-items:center;
}
.gs-path:hover{
  transform:translateY(-2px);
  border-color:rgba(129,140,248,.34);
  background:
    radial-gradient(260px 140px at 92% 0%,rgba(124,107,255,.27),transparent 68%),
    linear-gradient(135deg,rgba(109,117,255,.105),rgba(255,255,255,.035));
  box-shadow:0 18px 46px rgba(0,0,0,.22);
}
.gs-path:before{
  content:attr(data-index);
  position:absolute;
  right:16px;
  bottom:-8px;
  font-size:64px;
  line-height:1;
  font-weight:1000;
  letter-spacing:-.08em;
  color:rgba(255,255,255,.025);
  pointer-events:none;
}
.gs-path__top{display:contents}
.gs-path__ico{
  width:52px;
  height:52px;
  border-radius:17px;
  background:rgba(109,117,255,.14);
  border:1px solid rgba(129,140,248,.25);
  display:flex;
  align-items:center;
  justify-content:center;
  flex-shrink:0;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.06);
}
.gs-path__ico img{width:30px;height:30px;object-fit:contain}.gs-path__ico i{font-size:21px;color:#fff}
.gs-path__body{min-width:0;position:relative;z-index:2}
.gs-path__name{margin:0;font-size:17px;font-weight:1000;letter-spacing:-.025em;line-height:1.15}
.gs-path__desc{margin-top:6px;color:rgba(255,255,255,.50);font-size:12.5px;line-height:1.38;font-weight:650;max-width:440px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.gs-path__action{
  position:relative;
  z-index:2;
  height:38px;
  padding:0 13px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  gap:8px;
  color:rgba(255,255,255,.72);
  background:rgba(255,255,255,.055);
  border:1px solid rgba(255,255,255,.08);
  font-size:12px;
  font-weight:900;
  white-space:nowrap;
  transition:.18s ease;
}
.gs-path:hover .gs-path__action{color:#fff;background:rgba(109,117,255,.18);border-color:rgba(129,140,248,.33)}
.gs-path__arrow{font-size:11px;transition:.18s ease}.gs-path:hover .gs-path__arrow{transform:translateX(2px)}

/* ── Service buttons — icon + label only ────────────────────────────────── */
.gs-tiles{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
  gap:16px;
}
.gs-tile{
  --gs-accent:124,107,255;
  position:relative;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:16px;
  min-height:158px;
  padding:26px 18px;
  text-decoration:none;
  text-align:center;
  color:#fff;
  border-radius:24px;
  border:1px solid rgba(var(--gs-accent),.24);
  background:
    radial-gradient(300px 180px at 50% -25%,rgba(var(--gs-accent),.24),transparent 70%),
    linear-gradient(160deg,rgba(var(--gs-accent),.10),rgba(255,255,255,.02) 60%,rgba(255,255,255,.012));
  box-shadow:0 18px 44px rgba(0,0,0,.26),inset 0 1px 0 rgba(255,255,255,.05);
  overflow:hidden;
  transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;
}
.gs-tile::before{
  content:'';position:absolute;left:0;right:0;top:0;height:3px;
  background:linear-gradient(90deg,rgba(var(--gs-accent),.90),rgba(var(--gs-accent),.25));
}
.gs-tile:hover{
  transform:translateY(-4px);
  border-color:rgba(var(--gs-accent),.60);
  box-shadow:0 26px 60px rgba(0,0,0,.40),0 0 0 1px rgba(var(--gs-accent),.30),inset 0 1px 0 rgba(255,255,255,.07);
}
.gs-tile__ico{
  width:74px;height:74px;border-radius:22px;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(160deg,rgba(var(--gs-accent),.34),rgba(var(--gs-accent),.13));
  border:1px solid rgba(var(--gs-accent),.42);
  box-shadow:0 12px 28px rgba(var(--gs-accent),.24),inset 0 1px 0 rgba(255,255,255,.14);
  transition:transform .2s ease;
}
.gs-tile:hover .gs-tile__ico{transform:scale(1.06)}
.gs-tile__ico i{font-size:32px;color:#fff;filter:drop-shadow(0 3px 10px rgba(var(--gs-accent),.7))}
.gs-tile__ico img{width:38px;height:38px;object-fit:contain}
.gs-tile__name{margin:0;font-size:17px;font-weight:900;letter-spacing:.01em;line-height:1.2;color:#fff}
@media(max-width:760px){
  .gs-tiles{grid-template-columns:repeat(auto-fit,minmax(104px,1fr));gap:10px}
  .gs-tile{min-height:122px;padding:18px 10px;border-radius:18px;gap:11px}
  .gs-tile__ico{width:56px;height:56px;border-radius:17px}
  .gs-tile__ico i{font-size:24px}
  .gs-tile__ico img{width:30px;height:30px}
  .gs-tile__name{font-size:13px}
}

/* Boosting, slider services */
.gs-boost{
  scroll-snap-align:start;
  flex:0 0 218px;
  min-width:218px;
  min-height:236px;
  border-radius:24px;
  text-decoration:none;
  color:#fff;
  background:
    radial-gradient(260px 140px at 50% 0%,rgba(124,107,255,.20),transparent 72%),
    linear-gradient(180deg,rgba(255,255,255,.058),rgba(255,255,255,.026));
  border:1px solid rgba(255,255,255,.09);
  display:flex;
  flex-direction:column;
  padding:22px 18px 17px;
  position:relative;
  overflow:hidden;
  transition:.18s ease;
}
.gs-boost:hover{transform:translateY(-3px);border-color:rgba(129,140,248,.42);box-shadow:0 20px 52px rgba(0,0,0,.28)}
.gs-boost:after{content:'';position:absolute;right:-54px;bottom:-64px;width:150px;height:150px;border-radius:999px;background:radial-gradient(circle,rgba(0,194,255,.11),transparent 70%);opacity:.75;pointer-events:none}
.gs-boost__ico{width:62px;height:62px;border-radius:20px;background:rgba(109,117,255,.16);border:1px solid rgba(129,140,248,.24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.06)}
.gs-boost__ico img{width:34px;height:34px;object-fit:contain;filter:brightness(0) invert(1)}.gs-boost__ico i{font-size:25px;color:#fff}
.gs-boost__name{margin-top:18px;font-size:16px;font-weight:1000;line-height:1.16;letter-spacing:-.025em;position:relative;z-index:2}
.gs-boost__desc{margin-top:8px;font-size:12.5px;color:rgba(255,255,255,.50);line-height:1.42;font-weight:650;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;position:relative;z-index:2}
.gs-boost__foot{margin-top:auto;padding-top:18px;display:flex;align-items:center;justify-content:space-between;gap:10px;position:relative;z-index:2}
.gs-boost__cta{font-size:12px;font-weight:950;color:#aeb7ff;display:inline-flex;align-items:center;gap:7px}.gs-boost:hover .gs-boost__cta{color:#fff}.gs-boost__cta i{font-size:11px;transition:.18s}.gs-boost:hover .gs-boost__cta i{transform:translateX(2px)}
.gs-bdg{font-size:9px;font-weight:950;letter-spacing:.08em;text-transform:uppercase;padding:3px 8px;border-radius:99px;line-height:1.5}.gs-bdg--hot{background:rgba(239,68,68,.16);color:#fca5a5;border:1px solid rgba(239,68,68,.28)}.gs-bdg--new{background:rgba(34,197,94,.13);color:#86efac;border:1px solid rgba(34,197,94,.22)}

@media(max-width:1180px){.gs-hero__inner{grid-template-columns:1fr}.gs-hero__showcase{display:none}}
@media(max-width:760px){.gs{width:calc(100vw - 20px);padding:20px 0 80px}.gs-hero{overflow:visible}.gs-hero__inner{width:calc(100vw - 20px);min-height:0;padding:18px 0 26px;gap:18px}.gs-hero__main{grid-template-columns:48px minmax(0,1fr);gap:12px}.gs-hero__icon{width:48px;height:48px;border-radius:15px}.gs-hero__icon img{width:30px;height:30px;border-radius:9px}.gs-hero__title{font-size:28px}.gs-hero__desc{font-size:13px;line-height:1.48}.gs-hero__actions{margin-top:16px}.gs-hero__pill{height:40px;font-size:12px;padding:0 13px}.gs-hero__showcase{display:none}.gs-sec{margin-bottom:42px}.gs-sec-head{align-items:flex-start}.gs-sec-head h2{font-size:23px}.gs-paths{grid-template-columns:1fr}.gs-path{grid-template-columns:46px minmax(0,1fr);padding:15px}.gs-path__ico{width:46px;height:46px}.gs-path__action{grid-column:2;margin-top:4px;width:max-content}.gs-boost{flex:0 0 180px;min-width:180px;min-height:220px}.gs-boost__ico{width:54px;height:54px;border-radius:17px}.gs-boost__ico img{width:30px;height:30px}.gs-acc .account-card{flex:0 0 270px;min-width:270px}.gs-items .item-shop-card{flex:0 0 250px;min-width:250px}}

/* ════════════════════════════════════════
   ACCOUNT CARDS (override scoped CSS)
════════════════════════════════════════ */
.gs-acc .gs-sl{align-items:stretch}
.gs-acc .account-card{
  scroll-snap-align:start;flex:0 0 320px;min-width:320px;max-width:320px;
  border-radius:18px;border:1px solid rgba(99,102,241,.22);overflow:visible;
  background:linear-gradient(180deg, rgba(14,17,33,.98) 0%, rgba(10,12,24,.99) 100%);
  padding:18px;position:relative;display:flex;flex-direction:column;
  transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease;
}
.gs-acc .account-card:hover{
  transform:translateY(-3px);
  border-color:rgba(99,102,241,.55);
  box-shadow:0 0 0 1px rgba(99,102,241,.45),0 18px 52px rgba(0,0,0,.32);
}
.gs-acc .cover-link{text-decoration:none;color:inherit;display:flex;flex-direction:column;flex:1}
.gs-acc .account-card .title{font-size:16px;font-weight:700;display:flex;align-items:center;gap:8px;margin-bottom:12px}
.gs-acc .account-card .title img{width:auto;height:30px}
.gs-acc .account-card .excerpt{font-size:14px;color:rgba(255,255,255,.5);line-height:1.45;margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.gs-acc .account-card .image-box{position:relative;margin:0 0 12px}
.gs-acc .account-card .image-box img{width:100%;max-height:170px;object-fit:cover;border-radius:12px;display:block}
.gs-acc .account-card .image-box .badge{position:absolute;right:8px;bottom:8px;border-radius:7px;padding:4px 8px;display:flex;align-items:center;gap:4px;font-size:12px;background:rgba(0,0,0,.65);color:#fff}
.gs-acc .account-card .highlights{gap:6px;display:flex;flex-wrap:wrap;margin-bottom:12px}
.gs-acc .account-card .highlights .badge{font-size:13px;background:rgba(99,102,241,.22);color:#c7d2fe;display:inline-flex;align-items:center;border-radius:7px;padding:4px 9px;gap:4px}
.gs-acc .account-card .totals{display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:8px}
.gs-acc .account-card .totals .price-eur{font-size:24px;font-weight:900;color:#fff}
.gs-acc .account-card .totals .btn{padding:10px 17px;font-size:14px;font-weight:700;background:linear-gradient(90deg,#5b62f6,#6d75ff);border-radius:11px;color:#fff;text-decoration:none;white-space:nowrap;border:none}
.gs-acc .account-card .delivery-type{position:absolute;top:14px;right:14px;font-size:15px;color:rgba(255,255,255,.45)}
.gs-acc .account-card .account-card__recommended-icon{position:absolute;top:14px;right:36px;font-size:15px;color:#ffd54a}
/* seller footer, full width like marketplace cards */
.gs-acc .seller-info{margin:14px -18px -18px!important;padding:12px 14px!important;border-top:1px solid rgba(255,255,255,.08)!important;display:flex;align-items:center;justify-content:space-between;gap:10px;min-height:62px;background:linear-gradient(180deg,rgba(12,15,27,.72),rgba(8,10,18,.94))!important;border-radius:0 0 18px 18px!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.035)}
.gs-acc .seller-info__left{display:flex;align-items:center;gap:10px;min-width:0;flex:1}
.gs-acc .seller-info__avatar{width:36px;height:36px;min-width:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(150,109,255,.32);box-shadow:0 0 0 3px rgba(122,92,255,.08)}
.gs-acc .seller-info__name{min-width:0;color:#fff;text-decoration:none}.gs-acc .seller-info__name-text{font-size:14px;font-weight:900;text-transform:uppercase;letter-spacing:-.01em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:inline-block;max-width:150px}
.gs-acc .seller-info__right{display:flex;align-items:center;gap:7px;flex-shrink:0}.gs-acc .seller-info__sold{display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:850;color:rgba(255,255,255,.78);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.065);border-radius:9px;padding:5px 10px;white-space:nowrap}

/* ════════════════════════════════════════
   ITEM CARDS (full override)
════════════════════════════════════════ */
.gs-items .gs-sl{align-items:stretch}
.gs-items .item-shop-card{
  scroll-snap-align:start;flex:0 0 300px;min-width:300px;max-width:300px;
  border-radius:18px;border:1px solid rgba(99,102,241,.22);overflow:hidden;
  background: linear-gradient(180deg, rgba(14,17,33,.98) 0%, rgba(10,12,24,.99) 100%);
  display:flex;flex-direction:column;
  transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease;
}
.gs-items .item-shop-card:hover{transform:translateY(-3px);border-color:rgba(99,102,241,.55);box-shadow:0 0 0 1px rgba(99,102,241,.45),0 18px 52px rgba(0,0,0,.32)}
.gs-items .item-shop-card__img{height:170px;overflow:hidden;background:#0c1020;display:flex;align-items:center;justify-content:center;position:relative;flex-shrink:0}
.gs-items .item-shop-card__img img{width:100%;height:100%;object-fit:contain;display:block;transition:transform .3s}
.gs-items .item-shop-card:hover .item-shop-card__img img{transform:scale(1.05)}
.gs-items .item-shop-card__body{padding:14px 16px;display:flex;flex-direction:column;flex:1;gap:0}
.gs-items .item-shop-card__title{color:#fff;font-size:15px;font-weight:800;line-height:1.35;margin-bottom:8px;text-decoration:none;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.gs-items .item-shop-card__title:hover{color:#c7d2fe}
.gs-items .item-shop-card__desc{display:none}
.gs-items .item-shop-badges{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:10px}
.gs-items .item-shop-badge{display:inline-flex;align-items:center;gap:4px;font-size:13px;background:rgba(255,255,255,.07);border-radius:7px;padding:3px 9px;color:rgba(255,255,255,.6)}
.gs-items .item-shop-badge img.item-type-img{width:14px;height:14px;object-fit:contain}
.gs-items .item-shop-bottom{margin-top:auto;display:flex;align-items:center;justify-content:space-between;padding-top:10px;gap:8px}
.gs-items .item-shop-price{font-size:20px;font-weight:900;color:#fff}
.gs-items .item-shop-price small{font-size:13px;font-weight:400;color:rgba(255,255,255,.4)}
.gs-items .item-shop-btn{padding:10px 17px;font-size:14px;font-weight:700;background:linear-gradient(90deg,#5b62f6,#6d75ff);border-radius:11px;color:#fff;text-decoration:none;white-space:nowrap;border:none;transition:transform .15s ease,box-shadow .15s ease,filter .15s ease}
.gs-items .item-shop-btn:hover{background:linear-gradient(90deg,#5b62f6,#6d75ff);color:#fff;transform:translateY(-1px);box-shadow:0 10px 22px rgba(93,100,255,.28)}

/* Compact layout (non-LoL item cards): airy right-side thumbnail, full title */
.gs-items .item-shop-card--compact .item-shop-card__top{display:flex;align-items:flex-start;gap:16px}
.gs-items .item-shop-card--compact .item-shop-card__info{flex:1;min-width:0;display:flex;flex-direction:column;gap:12px}
.gs-items .item-shop-card--compact .item-shop-card__img{width:104px;height:104px;flex:0 0 104px;overflow:hidden;background:#0c1020;border-radius:16px;display:flex;align-items:center;justify-content:center;position:relative}
.gs-items .item-shop-card--compact .item-shop-card__img img{width:100%;height:100%;object-fit:contain;display:block;transition:transform .3s}
.gs-items .item-shop-card--compact:hover .item-shop-card__img img{transform:scale(1.05)}
.gs-items .item-shop-card--compact .item-shop-card__body{padding:16px 18px;gap:14px}
.gs-items .item-shop-card--compact .item-shop-card__title{margin-bottom:0;display:block;-webkit-line-clamp:unset;-webkit-box-orient:unset;overflow:visible;white-space:normal}
.gs-items .item-shop-card--compact .item-shop-badges{gap:8px;margin-bottom:0}
.gs-items .item-shop-card--compact .item-shop-badge{padding:6px 11px}
.gs-items .item-shop-card--compact .item-shop-bottom{padding-top:14px}
/* seller footer, full width like marketplace cards */
.gs-items .item-shop-seller{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:12px 14px!important;margin:14px -16px -16px!important;
  min-height:62px;background:linear-gradient(180deg,rgba(12,15,27,.72),rgba(8,10,18,.94))!important;
  border-top:1px solid rgba(255,255,255,.08)!important;border-radius:0 0 18px 18px!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.035);
}
/* left: avatar + name + rank icon */
.gs-items .item-shop-seller__left{
  display:flex;align-items:center;gap:10px;min-width:0;flex:1;text-decoration:none;color:inherit;
}
.gs-items .item-shop-seller__left img{
  width:36px;height:36px;min-width:36px;border-radius:50%;object-fit:cover;flex-shrink:0;
  border:2px solid rgba(150,109,255,.32);box-shadow:0 0 0 3px rgba(122,92,255,.08);
}
.gs-items .item-shop-seller__namewrap{
  min-width:0;display:flex;align-items:center;gap:7px;
}
.gs-items .item-shop-seller__name{
  font-size:14px;font-weight:900;letter-spacing:-.01em;text-transform:uppercase;
  color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  text-shadow:0 2px 10px rgba(0,0,0,.25);
}
.gs-items .item-shop-seller__rank{
  font-size:15px;flex-shrink:0;
  filter:drop-shadow(0 0 8px currentColor);
}
/* right: sold badge — same as seller-info__sold */
.gs-items .item-shop-seller__stats{
  display:inline-flex;align-items:center;gap:5px;
  font-size:13px;font-weight:850;color:rgba(255,255,255,.78);
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.065);
  border-radius:9px;padding:5px 10px;flex-shrink:0;white-space:nowrap;
}

/* RESPONSIVE */
@media(max-width:900px){
  .gs{width:calc(100vw - 20px);padding:20px 0 80px}
  .gs-boost{flex:0 0 170px;min-width:170px}
  .gs-boost__ico{width:60px;height:60px}
  .gs-boost__ico img{width:32px;height:32px}
  .gs-boost__ico i{font-size:24px}
}
@media(max-width:600px){
  .gs-acc .account-card{flex:0 0 270px;min-width:270px}
  .gs-items .item-shop-card{flex:0 0 250px;min-width:250px}
}



/* ════════════════════════════════════════
   MOBILE RESPONSIVE POLISH — featured sliders
════════════════════════════════════════ */
@media (max-width: 760px){
  body.game-hub{overflow-x:hidden;}
  .gs{width:100%;max-width:100vw;padding:18px 12px 78px;box-sizing:border-box;overflow:hidden;}
  .gs-sec{width:100%;max-width:100%;overflow:hidden;margin-bottom:38px;}
  .gs-sec-head{
    display:grid;
    grid-template-columns:minmax(0,1fr) auto;
    align-items:start;
    gap:12px;
    margin-bottom:16px;
  }
  .gs-sec-head > div:first-child{min-width:0;}
  .gs-sec-head h2{
    font-size:24px!important;
    line-height:1.12!important;
    max-width:none;
    word-break:normal;
    overflow-wrap:normal;
  }
  .gs-sec-lead{
    max-width:none;
    margin-top:8px;
    font-size:13px;
    line-height:1.45;
  }
  .gs-sec-head__r{
    align-self:start;
    display:flex;
    align-items:center;
    gap:8px;
    flex-shrink:0;
  }
  .gs-showall{
    width:54px;
    height:54px;
    padding:0;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    line-height:1.08;
    font-size:12px;
    border-radius:999px;
  }
  .gs-arr{width:42px;height:42px;min-width:42px;}

  /* Mobile users swipe sliders, so arrows are hidden to keep the header clean. */
  #gs-arr-b .gs-arr,
  #gs-arrbtn-a,
  #gs-arrbtn-i{display:none!important;}
  .gs-sw{width:100%;max-width:100%;overflow:visible;}
  .gs-sl{
    width:100%;
    max-width:100%;
    gap:12px;
    padding-top:8px;
    padding-bottom:10px;
    overflow-x:auto;
    overflow-y:visible;
    scroll-snap-type:x mandatory;
    scroll-padding-left:0;
  }
  .gs-sl::after{content:'';display:block;flex:0 0 1px;}

  .gs-acc .account-card,
  .gs-items .item-shop-card{
    flex:0 0 calc(100vw - 44px)!important;
    min-width:calc(100vw - 44px)!important;
    max-width:calc(100vw - 44px)!important;
    box-sizing:border-box;
    scroll-snap-align:start;
  }

  .gs-acc .account-card{
    padding:14px!important;
    border-radius:18px!important;
    overflow:hidden!important;
  }
  .gs-acc .account-card .title{
    font-size:14px!important;
    line-height:1.22!important;
    margin-bottom:9px!important;
  }
  .gs-acc .account-card .excerpt{
    font-size:12.5px!important;
    line-height:1.4!important;
    -webkit-line-clamp:2!important;
    margin-bottom:10px!important;
  }
  .gs-acc .account-card .image-box{margin-left:-14px!important;margin-right:-14px!important;margin-bottom:10px!important;}
  .gs-acc .account-card .image-box img{border-radius:0!important;max-height:150px!important;min-height:130px!important;object-fit:cover!important;}
  .gs-acc .account-card .highlights{gap:5px!important;margin-bottom:9px!important;max-height:58px;overflow:hidden;}
  .gs-acc .account-card .highlights .badge{font-size:11.5px!important;padding:4px 8px!important;}
  .gs-acc .account-card .totals{gap:10px!important;align-items:center!important;}
  .gs-acc .account-card .totals .price-eur{font-size:20px!important;white-space:nowrap;}
  .gs-acc .account-card .totals .btn{padding:9px 13px!important;font-size:12px!important;min-width:auto!important;}
  .gs-acc .seller-info{
    margin:12px -14px -14px!important;
    padding:10px 12px!important;
    min-height:58px!important;
    border-radius:0 0 18px 18px!important;
  }
  .gs-acc .seller-info__avatar{width:32px!important;height:32px!important;min-width:32px!important;}
  .gs-acc .seller-info__name-text{font-size:12px!important;max-width:120px!important;}
  .gs-acc .seller-info__sold{font-size:11.5px!important;padding:4px 8px!important;}

  .gs-items .item-shop-card{
    border-radius:18px!important;
    overflow:hidden!important;
  }
  .gs-items .item-shop-card__img{height:168px!important;}
  .gs-items .item-shop-card__body{padding:13px 14px 0!important;}
  .gs-items .item-shop-card__title{
    font-size:14px!important;
    line-height:1.28!important;
    min-height:36px;
    margin-bottom:8px!important;
  }
  .gs-items .item-shop-badges{gap:5px!important;max-height:56px;overflow:hidden;margin-bottom:8px!important;}
  .gs-items .item-shop-badge{font-size:11.5px!important;padding:3px 8px!important;}
  .gs-items .item-shop-bottom{padding-top:9px!important;gap:8px!important;}
  .gs-items .item-shop-price{font-size:20px!important;white-space:nowrap;}
  .gs-items .item-shop-price small{font-size:11px!important;}
  .gs-items .item-shop-btn{padding:9px 13px!important;font-size:12px!important;min-width:auto!important;}
  .gs-items .item-shop-card--compact .item-shop-card__top{gap:12px!important;}
  .gs-items .item-shop-card--compact .item-shop-card__img{width:84px!important;height:84px!important;flex:0 0 84px!important;}
  .gs-items .item-shop-card--compact .item-shop-card__body{padding:13px 14px!important;}
  .gs-items .item-shop-card--compact .item-shop-card__title{margin-bottom:0!important;-webkit-line-clamp:unset!important;overflow:visible!important;}
  .gs-items .item-shop-card--compact .item-shop-badges{max-height:none!important;margin-bottom:0!important;}
  .gs-items .item-shop-seller{
    margin:12px -14px 0!important;
    padding:10px 12px!important;
    min-height:58px!important;
    border-radius:0 0 18px 18px!important;
  }
  .gs-items .item-shop-seller__left img{width:32px!important;height:32px!important;min-width:32px!important;}
  .gs-items .item-shop-seller__name{font-size:12px!important;max-width:120px;}
  .gs-items .item-shop-seller__stats{font-size:11.5px!important;padding:4px 8px!important;}
}

@media (max-width: 420px){
  .gs{padding-left:10px;padding-right:10px;}
  .gs-sec-head{grid-template-columns:minmax(0,1fr) auto;gap:10px;}
  .gs-sec-head h2{font-size:23px!important;max-width:none;}
  .gs-sec-lead{max-width:none;font-size:12.5px;}
  .gs-showall{width:50px;height:50px;font-size:11.5px;}
  .gs-arr{width:38px;height:38px;min-width:38px;}

  #gs-arr-b .gs-arr,#gs-arrbtn-a,#gs-arrbtn-i{display:none!important;}
  .gs-sec-head__r{gap:6px;}
  .gs-acc .account-card,
  .gs-items .item-shop-card{
    flex-basis:calc(100vw - 32px)!important;
    min-width:calc(100vw - 32px)!important;
    max-width:calc(100vw - 32px)!important;
  }
}



/* ════════════════════════════════════════
   MOBILE SECTION HEADERS — cleaner featured layout
════════════════════════════════════════ */
@media (max-width: 760px){
  .gs-acc .gs-sec-head,
  .gs-items .gs-sec-head{
    display:flex!important;
    flex-direction:column!important;
    align-items:flex-start!important;
    gap:10px!important;
    margin-bottom:14px!important;
  }

  .gs-acc .gs-sec-head > div:first-child,
  .gs-items .gs-sec-head > div:first-child{
    width:100%!important;
    min-width:0!important;
  }

  .gs-acc .gs-sec-head h2,
  .gs-items .gs-sec-head h2{
    max-width:none!important;
    width:100%!important;
    font-size:24px!important;
    line-height:1.12!important;
    letter-spacing:-.04em!important;
  }

  .gs-acc .gs-sec-lead,
  .gs-items .gs-sec-lead{
    max-width:none!important;
    width:100%!important;
    margin-top:7px!important;
    font-size:13px!important;
    line-height:1.42!important;
  }

  .gs-acc .gs-sec-head__r,
  .gs-items .gs-sec-head__r{
    width:100%!important;
    display:flex!important;
    justify-content:flex-start!important;
    align-items:center!important;
    gap:0!important;
    margin-top:0!important;
  }

  .gs-acc .gs-showall,
  .gs-items .gs-showall{
    width:auto!important;
    height:34px!important;
    min-width:0!important;
    padding:0 13px!important;
    border-radius:999px!important;
    font-size:12px!important;
    line-height:1!important;
    gap:6px!important;
    background:rgba(99,102,241,.12)!important;
    border-color:rgba(129,140,248,.22)!important;
    color:rgba(255,255,255,.82)!important;
  }

  .gs-acc .gs-showall::after,
  .gs-items .gs-showall::after{
    content:'›';
    font-size:16px;
    line-height:1;
    margin-left:2px;
    transform:translateY(-1px);
  }
}

@media (max-width: 420px){
  .gs-acc .gs-sec-head h2,
  .gs-items .gs-sec-head h2{
    max-width:none!important;
    font-size:23px!important;
  }

  .gs-acc .gs-sec-lead,
  .gs-items .gs-sec-lead{
    max-width:none!important;
    font-size:12.5px!important;
  }
}


/* ════════════════════════════════════════
   INLINE TESTIMONIALS + FAQ, game services native
════════════════════════════════════════ */
.gs-proof{position:relative;overflow:hidden;padding:8px 0 2px}
.gs-proof__head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:22px;align-items:end;margin-bottom:22px}
.gs-proof__eyebrow,.gs-faq__eyebrow{display:inline-flex;align-items:center;gap:10px;margin:0 0 9px;color:#8b9bff;font-size:12px;font-weight:950;letter-spacing:.14em;text-transform:uppercase}
.gs-proof__eyebrow:before,.gs-faq__eyebrow:before{content:'';width:24px;height:2px;border-radius:99px;background:linear-gradient(90deg,#6d75ff,rgba(109,117,255,.18))}
.gs-proof__head h2,.gs-faq__head h2{margin:0;font-size:30px;line-height:1.08;font-weight:1000;letter-spacing:-.04em}
.gs-proof__lead,.gs-faq__lead{margin:9px 0 0;color:rgba(255,255,255,.50);font-size:14px;line-height:1.55;font-weight:650;max-width:680px}
.gs-proof__rating{display:inline-flex;align-items:center;gap:10px;min-height:42px;padding:0 14px;border-radius:999px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.10);color:#fff;white-space:nowrap}
.gs-proof__rating strong{font-size:12px;font-weight:950}.gs-proof__stars{display:inline-flex;gap:3px;color:#fbbf24;font-size:12px}.gs-proof__rating span:last-child{font-size:12px;color:rgba(255,255,255,.52);font-weight:800}
.gs-proof__marquee{position:relative;width:100%;overflow:hidden;padding:4px 0}.gs-proof__row{display:flex;gap:14px;width:max-content;animation:gsProofMove var(--gs-proof-speed,46s) linear infinite}.gs-proof__row + .gs-proof__row{margin-top:14px;animation-direction:reverse;--gs-proof-speed:52s}.gs-proof:hover .gs-proof__row{animation-play-state:paused}
@keyframes gsProofMove{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.gs-review{flex:0 0 340px;min-height:164px;border-radius:22px;padding:18px 19px;color:#fff;background:radial-gradient(240px 120px at 90% 0%,rgba(109,117,255,.10),transparent 70%),rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.085);box-shadow:inset 0 1px 0 rgba(255,255,255,.035)}
.gs-review__top{display:flex;align-items:center;gap:11px;margin-bottom:11px}.gs-review__avatar{position:relative;overflow:hidden;width:38px;height:38px;border-radius:14px;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:1000;box-shadow:0 12px 28px rgba(91,98,246,.20);flex:0 0 auto}.gs-review__avatar img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.gs-review__name{font-size:13px;font-weight:950;line-height:1.15}.gs-review__type{font-size:11px;color:rgba(255,255,255,.46);font-weight:800;margin-top:2px}.gs-review__stars{display:flex;gap:3px;color:#fbbf24;font-size:11px;margin:0 0 10px}.gs-review__text{margin:0;color:rgba(255,255,255,.70);font-size:13px;line-height:1.58;font-weight:650}.gs-review__tag{display:inline-flex;align-items:center;width:max-content;max-width:100%;margin-top:14px;padding:5px 10px;border-radius:999px;color:rgba(255,255,255,.70);background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.075);font-size:11px;font-weight:900}
.gs-faq{position:relative;padding-top:2px}
.gs-faq__grid{display:block}
.gs-faq__top{display:flex;align-items:flex-end;justify-content:space-between;gap:22px;margin-bottom:22px}
.gs-faq__head{min-width:0;margin:0}
.gs-faq__panel{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;align-items:start}
.gs-faq__item{position:relative;border:1px solid rgba(255,255,255,.085);border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.022));overflow:hidden;box-shadow:inset 0 1px 0 rgba(255,255,255,.035);transition:border-color .18s ease,background .18s ease,box-shadow .18s ease}
.gs-faq__item:hover{border-color:rgba(129,140,248,.24);background:linear-gradient(180deg,rgba(255,255,255,.060),rgba(255,255,255,.026))}
.gs-faq__item[open]{border-color:rgba(129,140,248,.34);background:radial-gradient(420px 140px at 15% 0%,rgba(109,117,255,.13),transparent 62%),linear-gradient(180deg,rgba(255,255,255,.064),rgba(255,255,255,.026));box-shadow:0 16px 46px rgba(0,0,0,.20),inset 0 1px 0 rgba(255,255,255,.055)}
.gs-faq__item summary{list-style:none;min-height:68px;padding:0 18px;display:flex;align-items:center;justify-content:space-between;gap:18px;cursor:pointer;color:rgba(255,255,255,.86);font-size:17px;line-height:1.28;font-weight:950;letter-spacing:-.025em}
.gs-faq__item summary::-webkit-details-marker{display:none}
.gs-faq__item summary i{width:32px;height:32px;min-width:32px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:#9aa5ff;background:rgba(109,117,255,.10);border:1px solid rgba(129,140,248,.16);font-size:12px;transition:.18s ease;flex:0 0 auto}
.gs-faq__item[open] summary{color:#fff}
.gs-faq__item[open] summary i{transform:rotate(180deg);background:rgba(109,117,255,.18);border-color:rgba(129,140,248,.28)}
.gs-faq__body{color:rgba(255,255,255,.62);font-size:13.5px;line-height:1.68;font-weight:650;padding:0 18px 18px;max-width:760px}
.gs-faq__aside{display:flex;align-items:center;gap:14px;min-width:340px;border-radius:999px;padding:10px 12px 10px 16px;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.085);box-shadow:inset 0 1px 0 rgba(255,255,255,.035)}
.gs-faq__aside-title{font-size:13px;font-weight:1000;letter-spacing:-.02em;white-space:nowrap}
.gs-faq__aside-text{display:none}
.gs-faq__aside-link{height:38px;padding:0 14px;margin-left:auto;width:max-content;display:inline-flex;align-items:center;gap:8px;border-radius:999px;text-decoration:none;color:#fff;background:rgba(109,117,255,.16);border:1px solid rgba(129,140,248,.28);font-size:12px;font-weight:950;white-space:nowrap}
@media(max-width:900px){.gs-proof__head{grid-template-columns:1fr;align-items:start}.gs-proof__rating{width:max-content}.gs-faq__top{display:block}.gs-faq__aside{margin-top:16px;min-width:0;width:max-content;max-width:100%}.gs-faq__panel{grid-template-columns:1fr}}
@media(max-width:760px){.gs-proof__head h2,.gs-faq__head h2{font-size:25px}.gs-proof__lead,.gs-faq__lead{font-size:13px}.gs-review{flex-basis:286px;min-height:160px;padding:16px}.gs-proof__row{gap:12px}.gs-faq__panel{gap:10px}.gs-faq__item{border-radius:17px}.gs-faq__item summary{font-size:15px;min-height:60px;padding:0 14px}.gs-faq__item summary i{width:30px;height:30px;min-width:30px}.gs-faq__body{font-size:13px;padding:0 14px 16px}.gs-proof__rating{gap:7px;padding:0 11px;max-width:100%;white-space:normal}.gs-faq__aside{width:100%;box-sizing:border-box;border-radius:18px;align-items:flex-start;flex-direction:column;padding:15px}.gs-faq__aside-link{margin-left:0}}
@media(prefers-reduced-motion:reduce){.gs-proof__row{animation:none}}



/* ════════════════════════════════════════
   FAQ, clean full width accordion, no card grid
════════════════════════════════════════ */
.gs-faq{
  padding-top:10px!important;
  margin-bottom:76px!important;
}
.gs-faq__grid{
  width:100%!important;
  max-width:1220px!important;
  margin:0 auto!important;
}
.gs-faq__top{
  display:grid!important;
  grid-template-columns:minmax(0,1fr) auto!important;
  align-items:end!important;
  gap:24px!important;
  margin-bottom:24px!important;
}
.gs-faq__head{
  max-width:720px!important;
}
.gs-faq__eyebrow{
  margin-bottom:10px!important;
}
.gs-faq__head h2{
  font-size:36px!important;
  line-height:1.06!important;
}
.gs-faq__lead{
  max-width:700px!important;
  color:rgba(255,255,255,.52)!important;
  font-size:16px!important;
  line-height:1.6!important;
}
.gs-faq__aside{
  min-width:0!important;
  width:auto!important;
  display:inline-flex!important;
  align-items:center!important;
  gap:12px!important;
  padding:0!important;
  border:0!important;
  background:transparent!important;
  box-shadow:none!important;
  border-radius:0!important;
  justify-self:end!important;
}
.gs-faq__aside-title{
  display:inline-flex!important;
  align-items:center!important;
  gap:8px!important;
  color:rgba(255,255,255,.62)!important;
  font-size:13.5px!important;
  font-weight:900!important;
  letter-spacing:0!important;
  white-space:nowrap!important;
}
.gs-faq__aside-title:before{
  content:'?';
  width:22px;
  height:22px;
  display:inline-grid;
  place-items:center;
  border-radius:999px;
  background:rgba(109,117,255,.12);
  border:1px solid rgba(129,140,248,.20);
  color:#aeb7ff;
  font-size:12px;
  font-weight:1000;
}
.gs-faq__aside-text{
  display:none!important;
}
.gs-faq__aside-link{
  height:42px!important;
  padding:0 17px!important;
  font-size:13.5px!important;
  margin-left:0!important;
  border-radius:999px!important;
  background:rgba(109,117,255,.12)!important;
  border:1px solid rgba(129,140,248,.26)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05)!important;
  color:rgba(255,255,255,.88)!important;
  transition:.18s ease!important;
}
.gs-faq__aside-link:hover{
  background:rgba(109,117,255,.20)!important;
  border-color:rgba(129,140,248,.42)!important;
  color:#fff!important;
  transform:translateY(-1px);
}
.gs-faq__panel{
  display:block!important;
  width:100%!important;
  max-width:100%!important;
  border-top:1px solid rgba(255,255,255,.075)!important;
}
.gs-faq__item{
  border:0!important;
  border-radius:0!important;
  background:transparent!important;
  box-shadow:none!important;
  border-bottom:1px solid rgba(255,255,255,.075)!important;
  overflow:hidden!important;
}
.gs-faq__item:hover{
  background:rgba(255,255,255,.018)!important;
  border-color:rgba(255,255,255,.09)!important;
}
.gs-faq__item[open]{
  background:linear-gradient(90deg,rgba(109,117,255,.075),transparent 54%)!important;
  border-color:rgba(129,140,248,.18)!important;
  box-shadow:none!important;
}
.gs-faq__item summary{
  min-height:76px!important;
  padding:0 4px!important;
  display:grid!important;
  grid-template-columns:minmax(0,1fr) 38px!important;
  align-items:center!important;
  gap:18px!important;
  color:rgba(255,255,255,.74)!important;
  font-size:21px!important;
  line-height:1.25!important;
  font-weight:900!important;
  letter-spacing:-.035em!important;
}
.gs-faq__item summary i{
  justify-self:end!important;
  width:34px!important;
  height:34px!important;
  min-width:34px!important;
  border-radius:999px!important;
  background:transparent!important;
  border:0!important;
  color:#9aa5ff!important;
  font-size:12px!important;
}
.gs-faq__item[open] summary{
  color:#fff!important;
}
.gs-faq__item[open] summary i{
  background:rgba(109,117,255,.12)!important;
  border:1px solid rgba(129,140,248,.20)!important;
}
.gs-faq__body{
  max-width:980px!important;
  padding:0 58px 26px 4px!important;
  color:rgba(255,255,255,.62)!important;
  font-size:16px!important;
  line-height:1.72!important;
  font-weight:650!important;
}
@media(max-width:900px){
  .gs-faq__top{
    grid-template-columns:1fr!important;
    align-items:start!important;
    gap:14px!important;
  }
  .gs-faq__aside{
    justify-self:start!important;
  }
}
@media(max-width:760px){
  .gs-faq{margin-bottom:52px!important;}
  .gs-faq__top{margin-bottom:16px!important;}
  .gs-faq__head h2{font-size:28px!important;}
  .gs-faq__lead{font-size:14px!important;line-height:1.55!important;}
  .gs-faq__aside{
    width:100%!important;
    justify-content:space-between!important;
    gap:10px!important;
  }
  .gs-faq__aside-title{font-size:12.5px!important;}
  .gs-faq__aside-link{height:38px!important;font-size:12.5px!important;padding:0 13px!important;}
  .gs-faq__item summary{
    min-height:64px!important;
    font-size:18px!important;
    padding:0!important;
    grid-template-columns:minmax(0,1fr) 30px!important;
  }
  .gs-faq__item summary i{
    width:28px!important;
    height:28px!important;
    min-width:28px!important;
  }
  .gs-faq__body{
    padding:0 36px 20px 0!important;
    font-size:14px!important;
    line-height:1.68!important;
  }
}


/* ════════════════════════════════════════
   MOBILE HERO SERVICE PILLS, swipe row
════════════════════════════════════════ */
@media (max-width: 760px){
  .gs-hero__actions{
    display:flex!important;
    flex-wrap:nowrap!important;
    gap:8px!important;
    margin-top:16px!important;
    max-width:calc(100vw - 72px)!important;
    overflow-x:auto!important;
    overflow-y:hidden!important;
    -webkit-overflow-scrolling:touch!important;
    scrollbar-width:none!important;
    scroll-snap-type:x proximity!important;
    padding:0 2px 8px 0!important;
    touch-action:pan-x!important;
  }
  .gs-hero__actions::-webkit-scrollbar{display:none!important;}
  .gs-hero__pill{
    flex:0 0 auto!important;
    height:38px!important;
    min-width:max-content!important;
    padding:0 13px!important;
    border-radius:999px!important;
    font-size:12px!important;
    white-space:nowrap!important;
    scroll-snap-align:start!important;
  }
  .gs-hero__pill i{
    font-size:12px!important;
    width:14px!important;
    text-align:center!important;
  }
  .gs-hero__pill--primary{
    padding:0 15px!important;
    box-shadow:0 12px 26px rgba(91,98,246,.20)!important;
  }

  /* FAQ mobile: remove the extra help choosing CTA, the floating chat button is enough on phone. */
  .gs-faq__aside{
    display:none!important;
  }
  .gs-faq__top{
    gap:0!important;
  }
}

@media (max-width: 420px){
  .gs-hero__actions{
    max-width:calc(100vw - 68px)!important;
    gap:7px!important;
  }
  .gs-hero__pill{
    height:37px!important;
    padding:0 12px!important;
    font-size:11.5px!important;
  }
  .gs-hero__pill--primary{
    padding:0 14px!important;
  }
}


/* ════════════════════════════════════════
   MOBILE FAQ, keep clean list style, add breathing room
════════════════════════════════════════ */
@media (max-width: 760px){
  .gs-faq__panel{
    border-top:1px solid rgba(255,255,255,.075)!important;
  }
  .gs-faq__item summary{
    min-height:66px!important;
    padding:0 18px!important;
    grid-template-columns:minmax(0,1fr) 30px!important;
    font-size:18px!important;
    line-height:1.28!important;
  }
  .gs-faq__item summary i{
    width:28px!important;
    height:28px!important;
    min-width:28px!important;
  }
  .gs-faq__body{
    padding:0 54px 22px 18px!important;
    font-size:13px!important;
    line-height:1.68!important;
  }
}
@media (max-width: 420px){
  .gs-faq__item summary{
    padding:0 16px!important;
  }
  .gs-faq__body{
    padding:0 46px 22px 16px!important;
  }
}


/* Final conversion focused service hub layer */
body.game-hub{background:radial-gradient(900px 520px at 50% 0%,rgba(91,98,246,.10),transparent 70%),#0b0a17}
.gs{padding-top:0;overflow:visible}
.gs-hero__inner{min-height:300px;padding-top:42px;padding-bottom:46px}
.gs-hero__title{max-width:860px}
.gs-hero__desc{max-width:700px}
.gs-hero__pill--primary{min-width:132px;justify-content:center}
.gs-sec{margin-bottom:64px}
.gs-sec-head{margin-bottom:18px}
.gs-sec-head h2{font-size:30px}

.gs-paths{grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.gs-path{min-height:118px;padding:20px 20px 19px;border-radius:22px;background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018));box-shadow:inset 0 1px 0 rgba(255,255,255,.025),0 18px 46px rgba(0,0,0,.12)}
.gs-path__action{height:36px;padding:0 12px;background:rgba(255,255,255,.035);border-color:rgba(255,255,255,.08)}
.gs-path__desc{font-size:12px}
.gs-path:nth-child(3n+1){background:radial-gradient(320px 160px at 92% 0%,rgba(124,107,255,.20),transparent 70%),linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018));}
.gs-path:nth-child(3n+2){background:radial-gradient(320px 160px at 92% 0%,rgba(0,194,255,.14),transparent 70%),linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018));}
.gs-path:nth-child(3n+3){background:radial-gradient(320px 160px at 92% 0%,rgba(34,197,94,.12),transparent 70%),linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018));}
.gs-path:nth-child(4n+1) .gs-path__ico{background:rgba(109,117,255,.17);border-color:rgba(129,140,248,.26)}
.gs-path:nth-child(4n+2) .gs-path__ico{background:rgba(0,194,255,.12);border-color:rgba(56,189,248,.24)}
.gs-path:nth-child(4n+3) .gs-path__ico{background:rgba(245,158,11,.12);border-color:rgba(251,191,36,.24)}
.gs-path:nth-child(4n+4) .gs-path__ico{background:rgba(34,197,94,.11);border-color:rgba(74,222,128,.22)}
.gs-path:first-child{transform:translateY(-2px);box-shadow:0 24px 58px rgba(46,42,120,.16),inset 0 1px 0 rgba(255,255,255,.04)}

.gs-sl{gap:14px;padding-top:14px;padding-bottom:14px}
.gs-boost{flex-basis:230px;min-width:230px;min-height:252px;padding:20px;border-radius:22px}
.gs-boost:nth-child(4n+1){background:radial-gradient(260px 140px at 50% 0%,rgba(124,107,255,.24),transparent 72%),linear-gradient(180deg,rgba(255,255,255,.060),rgba(255,255,255,.028));}
.gs-boost:nth-child(4n+2){background:radial-gradient(260px 140px at 50% 0%,rgba(0,194,255,.18),transparent 72%),linear-gradient(180deg,rgba(255,255,255,.058),rgba(255,255,255,.026));}
.gs-boost:nth-child(4n+3){background:radial-gradient(260px 140px at 50% 0%,rgba(245,158,11,.14),transparent 72%),linear-gradient(180deg,rgba(255,255,255,.058),rgba(255,255,255,.026));}
.gs-boost:nth-child(4n+4){background:radial-gradient(260px 140px at 50% 0%,rgba(34,197,94,.14),transparent 72%),linear-gradient(180deg,rgba(255,255,255,.058),rgba(255,255,255,.026));}
.gs-boost:nth-child(3n+2){transform:translateY(10px)}
.gs-boost:nth-child(3n+3){transform:translateY(4px)}
.gs-boost:hover{transform:translateY(-3px)!important}
.gs-boost--featured{flex-basis:290px;min-width:290px;border-color:rgba(129,140,248,.42);background:radial-gradient(270px 150px at 50% 0%,rgba(124,107,255,.29),transparent 72%),linear-gradient(180deg,rgba(109,117,255,.10),rgba(255,255,255,.027));box-shadow:0 18px 48px rgba(46,42,120,.18)}
.gs-boost__popular{position:absolute;top:12px;right:12px;display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;background:rgba(245,158,11,.13);border:1px solid rgba(245,158,11,.23);color:#fcd34d;font-size:9px;font-weight:950;text-transform:uppercase;letter-spacing:.06em}
.gs-boost__ico{width:58px;height:58px;border-radius:17px}
.gs-boost__name{font-size:17px}
.gs-boost__cta{padding:7px 10px;border-radius:10px;background:rgba(109,117,255,.11);border:1px solid rgba(129,140,248,.16)}
.gs-boost:hover .gs-boost__cta{background:rgba(109,117,255,.22)}

.gs-proof{position:relative;left:50%;width:100vw;max-width:none;transform:translateX(-50%);padding:10px max(16px,calc((100vw - min(1500px,calc(100vw - 28px)))/2)) 6px;overflow:hidden}
.gs-proof:before{content:'';position:absolute;inset:0;background:radial-gradient(720px 220px at 50% 0%,rgba(91,98,246,.08),transparent 68%);pointer-events:none}
.gs-proof__head,.gs-proof__marquee{position:relative;z-index:2}
.gs-proof__head{width:min(1500px,calc(100vw - 32px));margin:0 auto 22px}
.gs-proof__marquee{width:100%;overflow:hidden;padding:6px 0}
.gs-proof__row{display:flex;gap:16px;width:max-content;animation:gsProofMove var(--gs-proof-speed,46s) linear infinite}
.gs-review{flex:0 0 340px;min-height:170px;border-radius:24px;padding:18px 19px;color:#fff;background:radial-gradient(240px 120px at 90% 0%,rgba(109,117,255,.10),transparent 70%),rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.085);box-shadow:inset 0 1px 0 rgba(255,255,255,.035),0 16px 34px rgba(0,0,0,.16)}
.gs-review:nth-child(5n+1){flex-basis:300px;background:radial-gradient(220px 120px at 90% 0%,rgba(109,117,255,.12),transparent 70%),rgba(255,255,255,.035)}
.gs-review:nth-child(5n+2){flex-basis:360px;background:radial-gradient(240px 120px at 90% 0%,rgba(0,194,255,.10),transparent 70%),rgba(255,255,255,.035)}
.gs-review:nth-child(5n+3){flex-basis:330px;background:radial-gradient(240px 120px at 90% 0%,rgba(245,158,11,.10),transparent 70%),rgba(255,255,255,.035)}
.gs-review:nth-child(5n+4){flex-basis:390px;background:radial-gradient(240px 120px at 90% 0%,rgba(34,197,94,.09),transparent 70%),rgba(255,255,255,.035)}
.gs-review:nth-child(5n+5){flex-basis:350px;background:radial-gradient(240px 120px at 90% 0%,rgba(236,72,153,.08),transparent 70%),rgba(255,255,255,.035)}
.gs-review:nth-child(even){transform:translateY(8px)}
.gs-review__avatar{border-radius:16px}
.gs-review__tag{background:rgba(255,255,255,.07)}

@media(max-width:1100px){.gs-paths{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){
  .gs-hero__inner{padding-bottom:38px}
  .gs-paths{grid-template-columns:1fr;gap:10px}
  .gs-path{min-height:100px;border-radius:18px;padding:17px}
  .gs-boost{flex:0 0 80vw;min-width:80vw;min-height:232px;transform:none!important}
  .gs-boost--featured{flex:0 0 84vw;min-width:84vw}
  .gs-sec{margin-bottom:46px}
  .gs-proof{padding-left:10px;padding-right:10px}
  .gs-proof__head{width:100%;padding:0 2px;box-sizing:border-box}
  .gs-proof__row{gap:12px}
  .gs-review,.gs-review:nth-child(n){flex-basis:78vw;min-height:164px;transform:none}
}
@media(max-width:420px){
  .gs-proof{padding-left:8px;padding-right:8px}
  .gs-review,.gs-review:nth-child(n){flex-basis:84vw}
}


/* ════════════════════════════════════════
   FINAL POLISH, FULL BLEED REVIEWS + UNIFIED BADGES
════════════════════════════════════════ */
@media (min-width: 761px){
  /* Keep the section title aligned with the page, while the moving reviews
     run from one viewport edge to the other. */
  .gs-proof{
    left:50%!important;
    width:100vw!important;
    max-width:none!important;
    transform:translateX(-50%)!important;
    padding:10px 0 8px!important;
    overflow:hidden!important;
  }
  .gs-proof__head{
    width:min(1500px,calc(100vw - 28px))!important;
    margin:0 auto 22px!important;
    padding:0!important;
  }
  .gs-proof__marquee{
    width:100vw!important;
    max-width:none!important;
    margin:0!important;
    padding:8px 0 14px!important;
    overflow:hidden!important;
    -webkit-mask-image:linear-gradient(90deg,transparent 0,#000 3.5vw,#000 96.5vw,transparent 100%);
    mask-image:linear-gradient(90deg,transparent 0,#000 3.5vw,#000 96.5vw,transparent 100%);
  }
  .gs-proof__row{
    gap:16px!important;
    padding:0!important;
    will-change:transform;
  }
  .gs-proof__row:first-child{--gs-proof-speed:42s;}
  .gs-proof__row + .gs-proof__row{--gs-proof-speed:48s;}
}

/* Account and item metadata badges, exactly the same size and appearance. */
.gs-acc .account-card .highlights{
  display:flex!important;
  flex-wrap:wrap!important;
  gap:5px!important;
}
.gs-items .item-shop-badges{
  display:grid!important;
  grid-template-columns:repeat(2,minmax(0,1fr))!important;
  gap:5px!important;
}
.gs-acc .account-card .highlights .badge,
.gs-items .item-shop-badge{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:5px!important;
  min-height:24px!important;
  padding:4px 8px!important;
  margin:0!important;
  background:#0B0A17!important;
  border:1px solid rgba(99,112,230,.28)!important;
  color:#dbe1ff!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.035),0 4px 12px rgba(2,4,18,.28)!important;
  border-radius:7px!important;
  font-size:11.5px!important;
  line-height:1!important;
  font-weight:750!important;
  white-space:nowrap!important;
}
.gs-items .item-shop-badge{
  min-width:0!important;
  justify-content:flex-start!important;
  overflow:hidden!important;
  text-overflow:ellipsis!important;
}
.gs-acc .account-card .highlights .badge i,
.gs-items .item-shop-badge i{
  width:12px!important;
  min-width:12px!important;
  text-align:center!important;
  font-size:11px!important;
  color:#aeb9ff!important;
}
.gs-items .item-shop-badge img.item-type-img{
  width:12px!important;
  height:12px!important;
  min-width:12px!important;
  object-fit:contain!important;
  filter:saturate(.9) brightness(1.12);
}

/* Sold badges in both seller footers are identical as well. */
.gs-acc .seller-info__sold,
.gs-items .item-shop-seller__stats{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:5px!important;
  min-height:28px!important;
  padding:5px 10px!important;
  background:#0B0A17!important;
  border:1px solid rgba(255,255,255,.065)!important;
  border-radius:9px!important;
  color:rgba(255,255,255,.78)!important;
  font-size:12px!important;
  line-height:1!important;
  font-weight:850!important;
  white-space:nowrap!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.025)!important;
}
.gs-acc .seller-info__sold i,
.gs-items .item-shop-seller__stats i{
  font-size:11px!important;
  color:rgba(255,255,255,.62)!important;
}

/* Typography compensation for the site wide 0.88 desktop zoom */
@media (min-width: 761px){
  .gs-hero__kicker{font-size:14px!important;}
  .gs-hero__title{font-size:clamp(38px,4vw,58px)!important;}
  .gs-hero__desc{font-size:16px!important;line-height:1.6!important;}
  .gs-sec-head h2{font-size:27px!important;}
  .gs-sec-lead{font-size:15px!important;}
  .gs-path__title,.gs-boost__title{font-size:17px!important;}
  .gs-path__desc,.gs-boost__desc{font-size:14px!important;}
}

/* ═══════════════════════════════════════════════════════
   GAME-SERVICES — full visual refresh (v2)
   Re-skins hero / category tiles / boosting cards / reviews
   marquee / FAQ to the sitewide flat dark-panel system used on
   the boosters/sellers/egirls profile views. Booster/account/item
   card components further up are left untouched — already fit.
═══════════════════════════════════════════════════════ */
html{ overflow-x:hidden !important; }
body.game-hub{
  overflow-x:hidden !important;
  background:
    radial-gradient(1100px 600px at 18% -8%, rgba(79,110,247,.12), transparent 62%),
    radial-gradient(900px 520px at 86% 4%, rgba(99,102,241,.09), transparent 65%),
    #070815 !important;
}

/* ── Hero ─────────────────────────────────────────────── */
.gs-hero{ overflow:hidden !important; background:transparent !important; }
.gs-hero::before{
  content:'' !important; position:absolute !important; inset:0 !important;
  background:
    radial-gradient(820px 380px at 18% 10%, rgba(79,110,247,.16), transparent 62%),
    radial-gradient(680px 340px at 86% 0%, rgba(99,102,241,.10), transparent 66%) !important;
  pointer-events:none !important;
}
.gs-hero::after{ background:linear-gradient(90deg,transparent,rgba(129,140,248,.22),transparent) !important; }
.gs-hero__inner{ min-height:0 !important; padding:52px 0 46px !important; grid-template-columns:minmax(0,1fr) !important; gap:36px !important; }
.gs-hero__icon{
  width:76px !important; height:76px !important; border-radius:20px !important;
  background:#0d1021 !important; border:1px solid rgba(255,255,255,.08) !important;
  box-shadow:0 20px 48px rgba(0,0,0,.35) !important;
}
.gs-hero__icon img{ width:46px !important; height:46px !important; border-radius:12px !important; }
.gs-hero__icon i{ font-size:30px !important; color:#8ea5ff !important; }
.gs-hero__kicker{ color:#8ea5ff !important; }
.gs-hero__kicker:before{ background:#5b57ff !important; box-shadow:0 0 14px rgba(91,87,255,.7) !important; }
.gs-hero__title{ font-size:clamp(32px,3.4vw,50px) !important; font-weight:900 !important; letter-spacing:-.03em !important; text-shadow:none !important; }
.gs-hero__desc{ color:rgba(255,255,255,.6) !important; font-weight:500 !important; max-width:700px !important; }
.gs-hero__pill{ background:rgba(255,255,255,.045) !important; border:1px solid rgba(255,255,255,.09) !important; font-weight:800 !important; }
.gs-hero__pill:hover{ background:rgba(99,102,241,.16) !important; border-color:rgba(129,140,248,.4) !important; }
.gs-hero__pill i{ color:#a5b4fc !important; }
.gs-hero__pill--primary{
  background:linear-gradient(135deg,#7c83ff,#5b57ff 55%,#4f46e5) !important;
  border-color:transparent !important; box-shadow:0 14px 34px rgba(91,87,255,.38) !important;
}
.gs-hero__pill--primary:hover{ box-shadow:0 16px 40px rgba(91,87,255,.5) !important; }

/* ── Section headers + slider chrome (shared by every gs-sec) ── */
.gs-sec-head h2{ font-weight:900 !important; letter-spacing:-.03em !important; color:#fff !important; }
.gs-sec-lead{ color:rgba(255,255,255,.5) !important; font-weight:500 !important; }
.gs-showall{ border:1px solid rgba(255,255,255,.09) !important; background:rgba(255,255,255,.03) !important; color:rgba(255,255,255,.75) !important; }
.gs-showall:hover{ color:#fff !important; border-color:rgba(129,140,248,.4) !important; background:rgba(99,102,241,.14) !important; }
.gs-arr{ background:rgba(255,255,255,.05) !important; border:1px solid rgba(255,255,255,.09) !important; }
.gs-arr:hover{ background:rgba(99,102,241,.2) !important; border-color:rgba(129,140,248,.4) !important; }

/* ── Category tiles ("Shop by category") ── */
.gs-tile{ background:#0d1021 !important; border:1px solid rgba(255,255,255,.07) !important; box-shadow:none !important; }
.gs-tile::before{ background:radial-gradient(220px 140px at 50% -20%, rgba(var(--gs-accent),.18), transparent 70%) !important; }
.gs-tile:hover{ border-color:rgba(var(--gs-accent),.5) !important; box-shadow:0 20px 46px rgba(0,0,0,.32) !important; }
.gs-tile__ico{ background:rgba(var(--gs-accent),.16) !important; border:1px solid rgba(var(--gs-accent),.32) !important; box-shadow:none !important; }
.gs-tile__ico i{ filter:none !important; }
.gs-tile__name{ font-weight:800 !important; }

/* ── Boosting service cards ── */
.gs-boost{ background:#0d1021 !important; border:1px solid rgba(255,255,255,.07) !important; }
.gs-boost:after{ display:none !important; }
.gs-boost:hover{ border-color:rgba(129,140,248,.42) !important; box-shadow:0 20px 50px rgba(0,0,0,.32) !important; }
.gs-boost--featured{
  border-color:rgba(129,140,248,.42) !important;
  background:linear-gradient(180deg,rgba(99,102,241,.09),#0d1021 62%) !important;
  box-shadow:0 18px 46px rgba(46,42,120,.22) !important;
}
.gs-boost__ico{ background:rgba(99,102,241,.15) !important; border:1px solid rgba(129,140,248,.26) !important; box-shadow:none !important; }
.gs-boost__cta{ color:#a5b4fc !important; }
.gs-boost:hover .gs-boost__cta{ color:#fff !important; }

/* ── Reviews marquee ── */
.gs-proof:before{ background:radial-gradient(720px 220px at 50% 0%, rgba(91,98,246,.09), transparent 68%) !important; }
.gs-proof__eyebrow{ color:#8ea5ff !important; }
.gs-proof__eyebrow:before{ background:#5b57ff !important; }
.gs-proof__head h2{ font-weight:900 !important; letter-spacing:-.03em !important; }
.gs-proof__lead{ color:rgba(255,255,255,.5) !important; }
.gs-proof__rating{ background:#0d1021 !important; border:1px solid rgba(255,255,255,.08) !important; }
.gs-review{ background:#0d1021 !important; border:1px solid rgba(255,255,255,.07) !important; box-shadow:none !important; }
.gs-review__avatar{ border-radius:12px !important; box-shadow:none !important; }
.gs-review__tag{ background:rgba(255,255,255,.04) !important; border:1px solid rgba(255,255,255,.07) !important; }

/* ── FAQ ── */
.gs-faq__eyebrow{ color:#8ea5ff !important; }
.gs-faq__eyebrow:before{ background:#5b57ff !important; }
.gs-faq__head h2{ font-weight:900 !important; letter-spacing:-.03em !important; }
.gs-faq__lead{ color:rgba(255,255,255,.52) !important; }
.gs-faq__aside-title:before{ background:rgba(99,102,241,.14) !important; border:1px solid rgba(129,140,248,.24) !important; color:#a5b4fc !important; }
.gs-faq__aside-link{ background:rgba(99,102,241,.14) !important; border:1px solid rgba(129,140,248,.28) !important; color:rgba(255,255,255,.88) !important; }
.gs-faq__aside-link:hover{ background:rgba(99,102,241,.22) !important; border-color:rgba(129,140,248,.45) !important; color:#fff !important; }
.gs-faq__panel{ border-top:1px solid rgba(255,255,255,.07) !important; }
.gs-faq__item{ border-bottom:1px solid rgba(255,255,255,.07) !important; background:transparent !important; }
.gs-faq__item:hover{ background:rgba(255,255,255,.02) !important; }
.gs-faq__item[open]{ background:linear-gradient(90deg,rgba(99,102,241,.08),transparent 55%) !important; }
.gs-faq__item summary{ color:rgba(255,255,255,.78) !important; font-weight:800 !important; }
.gs-faq__item[open] summary{ color:#fff !important; }
.gs-faq__item summary i{ color:#a5b4fc !important; }
.gs-faq__item[open] summary i{ background:rgba(99,102,241,.14) !important; border:1px solid rgba(129,140,248,.24) !important; }
.gs-faq__body{ color:rgba(255,255,255,.6) !important; }

@media(max-width:760px){
  .gs-hero__icon{ width:48px !important; height:48px !important; border-radius:15px !important; }
  .gs-hero__icon img{ width:30px !important; height:30px !important; border-radius:9px !important; }
  .gs-hero__title{ font-size:28px !important; }
  .gs-hero__desc{ font-size:13.5px !important; line-height:1.5 !important; }
}

/* Mobile: content-visibility:auto (used above on .gs-acc/.gs-items/.gs-proof/.gs-faq/
   .gs-boosters) intentionally skips rendering those sections until they're near the
   viewport. On slower mobile devices that "just in time" render can lag behind a fast
   scroll, leaving a blank placeholder briefly visible — same issue fixed on the landing
   page. Disabling it on mobile trades a slightly heavier initial render for no blank
   sections while scrolling; desktop keeps the optimization untouched. */
@media(max-width:820px){
  body.game-hub .gs-acc,
  body.game-hub .gs-items,
  body.game-hub .gs-proof,
  body.game-hub .gs-faq,
  body.game-hub .gs-boosters{
    content-visibility:visible !important;
    contain:none !important;
    contain-intrinsic-size:auto !important;
  }
}

/* ═══════════════════════════════════════════════════════
   CATEGORY TILES — compact horizontal rows
   The old auto-fit grid stretched each tile to fill the row, so a
   game with 3 categories got three huge boxes with a tiny centered
   icon in the middle. Fixed-width horizontal tiles read the same
   whether a game has 3 or 6 services.
═══════════════════════════════════════════════════════ */
/* The category section is the first block under the hero and needs room. */
body.game-hub #gs-categories{padding-top:26px !important;}

body.game-hub .gs-tiles{
  display:flex !important;
  flex-wrap:wrap !important;
  gap:14px !important;
  justify-content:flex-start !important;
}
/* Same panel language as the boosting cards below: #0d1021, 22px radius,
   58px icon tile — just laid out horizontally so a game with 3 categories
   does not get three oversized empty boxes. */
body.game-hub .gs-tile{
  flex:1 1 240px !important;
  max-width:320px !important;
  display:grid !important;
  grid-template-columns:58px minmax(0,1fr) 16px !important;
  align-items:center !important;
  gap:16px !important;
  min-height:0 !important;
  padding:18px 20px !important;
  text-align:left !important;
  border-radius:22px !important;
  background:#0d1021 !important;
  border:1px solid rgba(255,255,255,.07) !important;
  box-shadow:none !important;
  transition:transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease !important;
}
/* The old top accent bar becomes a left edge glow that only shows on hover. */
body.game-hub .gs-tile::before{
  content:'' !important;
  position:absolute !important;
  left:0 !important; top:0 !important; bottom:0 !important;
  right:auto !important;
  width:3px !important;
  height:auto !important;
  border-radius:22px 0 0 22px !important;
  background:rgba(var(--gs-accent),.85) !important;
  opacity:0 !important;
  transition:opacity .18s ease !important;
}
body.game-hub .gs-tile:hover{
  transform:translateY(-3px) !important;
  border-color:rgba(var(--gs-accent),.45) !important;
  background:linear-gradient(100deg,rgba(var(--gs-accent),.10),#0d1021 62%) !important;
  box-shadow:0 20px 46px rgba(0,0,0,.32) !important;
}
body.game-hub .gs-tile:hover::before{opacity:1 !important;}
body.game-hub .gs-tile__ico{
  width:58px !important;
  height:58px !important;
  border-radius:17px !important;
  background:rgba(var(--gs-accent),.15) !important;
  border:1px solid rgba(var(--gs-accent),.30) !important;
  box-shadow:none !important;
}
body.game-hub .gs-tile:hover .gs-tile__ico{transform:none !important;}
body.game-hub .gs-tile__ico i{font-size:23px !important;}
body.game-hub .gs-tile__name{
  font-size:17px !important;
  font-weight:900 !important;
  line-height:1.18 !important;
  letter-spacing:-.025em !important;
  /* Wrap instead of truncating — "Ranked Accounts" must stay readable. */
  white-space:normal !important;
  overflow:visible !important;
  text-overflow:clip !important;
}
body.game-hub .gs-tile__go{
  font-size:12px !important;
  color:rgba(255,255,255,.26) !important;
  transition:color .18s ease, transform .18s ease !important;
}
body.game-hub .gs-tile:hover .gs-tile__go{
  color:rgba(var(--gs-accent),1) !important;
  transform:translateX(3px) !important;
}
@media(max-width:760px){
  body.game-hub #gs-categories{padding-top:14px !important;}
  body.game-hub .gs-tiles{gap:10px !important;}
  body.game-hub .gs-tile{
    flex:1 1 100% !important;
    max-width:none !important;
    grid-template-columns:48px minmax(0,1fr) 14px !important;
    gap:14px !important;
    padding:14px 16px !important;
    border-radius:18px !important;
  }
  body.game-hub .gs-tile::before{border-radius:18px 0 0 18px !important;}
  body.game-hub .gs-tile__ico{width:48px !important;height:48px !important;border-radius:14px !important;}
  body.game-hub .gs-tile__ico i{font-size:20px !important;}
  body.game-hub .gs-tile__name{font-size:15px !important;}
}

/* ═══════════════════════════════════════════════════════
   FEATURED ACCOUNT / ITEM CARDS — match the shop pages
   The account-card component's own redesign CSS is scoped to
   body.ranked-accounts-page, so nothing of it reaches this page and
   the .gs-acc/.gs-items blocks further up invented their own look
   (2-column badge grid, different badge colours, smaller type).
   These rules mirror the shop values 1:1; only the slider width is
   still owned by this page.
═══════════════════════════════════════════════════════ */
body.game-hub .gs-acc .account-card{
  border-radius:18px !important;
  border:1px solid rgba(255,255,255,.075) !important;
  background:#0d1021 !important;
  padding:0 !important;
  box-shadow:none !important;
}
body.game-hub .gs-acc .account-card:hover{
  transform:translateY(-2px) !important;
  border-color:rgba(124,146,255,.25) !important;
  box-shadow:none !important;
}
body.game-hub .gs-acc .account-card > .cover-link{
  padding:16px 16px 0 !important;
  gap:0 !important;
}
body.game-hub .gs-acc .account-card .title{
  display:flex !important;
  align-items:center !important;
  gap:10px !important;
  min-height:38px !important;
  margin:0 !important;
  padding-right:76px !important;
  font-size:17px !important;
  font-weight:900 !important;
  line-height:1.28 !important;
  letter-spacing:-.015em !important;
  color:#fff !important;
}
body.game-hub .gs-acc .account-card .title > img.rank-icon,
body.game-hub .gs-acc .account-card .title > .rank-icon,
body.game-hub .gs-acc .account-card .account-card-game-icon-fallback{
  width:36px !important;
  height:36px !important;
  min-width:36px !important;
  flex:0 0 36px !important;
  padding:5px !important;
  border-radius:11px !important;
  background:linear-gradient(145deg,rgba(255,255,255,.08),rgba(255,255,255,.025)) !important;
  border:1px solid rgba(255,255,255,.09) !important;
  object-fit:contain !important;
}
body.game-hub .gs-acc .account-card .excerpt{
  margin:8px 0 12px !important;
  min-height:36px !important;
  max-height:36px !important;
  font-size:13px !important;
  line-height:1.48 !important;
  color:#8f97b5 !important;
  display:-webkit-box !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
  overflow:hidden !important;
}
body.game-hub .gs-acc .account-card .image-box{
  height:190px !important;
  min-height:190px !important;
  max-height:190px !important;
  margin:0 !important;
  border-radius:14px !important;
  background:#070912 !important;
  border:1px solid rgba(255,255,255,.07) !important;
  overflow:hidden !important;
}
body.game-hub .gs-acc .account-card .image-box img{
  width:100% !important;
  height:100% !important;
  max-height:none !important;
  border-radius:0 !important;
  object-fit:cover !important;
}
/* Badges: same chip style as the shop, but the slider cards are only 320px
   wide. A fixed 3-column grid gives each chip ~95px, which cut labels like
   "227 Outfits/Skins" down to "227 Outfits/Sk…". Wrapping flex chips size
   themselves to their text and simply break into the next row. */
body.game-hub .gs-acc .account-card .highlights{
  display:flex !important;
  flex-wrap:wrap !important;
  align-content:flex-start !important;
  gap:6px !important;
  margin:12px 0 0 !important;
  padding:0 0 18px !important;
  height:auto !important;
  min-height:68px !important;
  max-height:none !important;
  overflow:visible !important;
}
body.game-hub .gs-acc .account-card .highlights .badge{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:flex-start !important;
  gap:6px !important;
  flex:0 1 auto !important;
  max-width:100% !important;
  min-width:0 !important;
  min-height:30px !important;
  padding:6px 9px !important;
  border-radius:9px !important;
  background:#151827 !important;
  border:1px solid rgba(255,255,255,.07) !important;
  color:#b8bfd8 !important;
  box-shadow:none !important;
  font-size:11.5px !important;
  font-weight:750 !important;
  line-height:1.15 !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  white-space:nowrap !important;
}
body.game-hub .gs-acc .account-card .highlights .badge i{
  flex:0 0 auto !important;
}
body.game-hub .gs-acc .account-card .highlights .badge i{
  width:auto !important;
  min-width:0 !important;
  font-size:11.5px !important;
  color:#8ea5ff !important;
}
body.game-hub .gs-acc .account-card .totals{
  margin-top:auto !important;
  min-height:64px !important;
  padding:13px 0 !important;
  border-top:1px solid rgba(255,255,255,.065) !important;
}
body.game-hub .gs-acc .account-card .totals .price-eur{
  font-size:23px !important;
  font-weight:950 !important;
  letter-spacing:-.03em !important;
}
body.game-hub .gs-acc .account-card .totals .btn,
body.game-hub .gs-acc .account-card .totals .btn.primary{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  min-width:122px !important;
  height:43px !important;
  padding:0 16px !important;
  border:0 !important;
  border-radius:11px !important;
  background:#4f6ef7 !important;
  box-shadow:none !important;
  font-size:13.5px !important;
  font-weight:850 !important;
}
body.game-hub .gs-acc .account-card > .seller-info,
body.game-hub .gs-acc .account-card > .lb-seller-footer{
  margin:0 !important;
  min-height:54px !important;
  padding:10px 16px !important;
  border-top:1px solid rgba(255,255,255,.065) !important;
  border-radius:0 0 18px 18px !important;
  background:#101322 !important;
  box-shadow:none !important;
}

/* Item cards ship their own styling with the component — only undo the
   badge grid and recolouring this page forced on top of it. */
body.game-hub .gs-items .item-shop-card{
  border-radius:18px !important;
  border:1px solid rgba(255,255,255,.075) !important;
  background:#0d1021 !important;
}
body.game-hub .gs-items .item-shop-card:hover{
  transform:translateY(-2px) !important;
  border-color:rgba(124,146,255,.25) !important;
  box-shadow:none !important;
}
body.game-hub .gs-items .item-shop-badges{
  display:flex !important;
  flex-wrap:wrap !important;
  gap:8px !important;
}
body.game-hub .gs-items .item-shop-badge{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:flex-start !important;
  gap:6px !important;
  min-height:30px !important;
  padding:6px 11px !important;
  border-radius:9px !important;
  background:rgba(255,255,255,.055) !important;
  border:1px solid rgba(255,255,255,.08) !important;
  color:rgba(255,255,255,.72) !important;
  box-shadow:none !important;
  font-size:12px !important;
  font-weight:700 !important;
  line-height:1 !important;
}
body.game-hub .gs-items .item-shop-badge i{
  width:auto !important;
  min-width:0 !important;
  font-size:11.5px !important;
  color:#8ea5ff !important;
}
body.game-hub .gs-items .item-shop-badge img.item-type-img{
  width:14px !important;
  height:14px !important;
  min-width:14px !important;
  filter:none !important;
}

@media(max-width:760px){
  body.game-hub .gs-acc .account-card{padding:0 !important;}
  body.game-hub .gs-acc .account-card > .cover-link{padding:15px 15px 0 !important;}
  body.game-hub .gs-acc .account-card .title{font-size:15px !important;padding-right:70px !important;}
  body.game-hub .gs-acc .account-card .image-box{
    height:178px !important;min-height:178px !important;max-height:178px !important;
    margin:0 !important;border-radius:14px !important;
  }
  body.game-hub .gs-acc .account-card .image-box img{border-radius:0 !important;}
  body.game-hub .gs-acc .account-card .highlights .badge{font-size:10.5px !important;}
  body.game-hub .gs-acc .account-card > .seller-info{margin:0 !important;padding:10px 15px !important;}
}

/* FAQ rows sat flush against the panel edge — give question and answer
   the same inner breathing room the rest of the page has. */
body.game-hub .gs-faq__item summary{padding:0 22px !important;}
body.game-hub .gs-faq__body{padding:0 64px 26px 22px !important;}
@media(max-width:760px){
  body.game-hub .gs-faq__item summary{padding:0 18px !important;}
  body.game-hub .gs-faq__body{padding:0 54px 22px 18px !important;}
}


/* ── Blog articles for this game ── */
.gs-blog-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;align-items:stretch;}
.gs-blog-card{display:flex;min-width:0;flex-direction:column;background:#090d1d;border:1px solid rgba(255,255,255,.09);border-radius:20px;overflow:hidden;text-decoration:none;color:inherit;box-shadow:0 16px 42px rgba(0,0,0,.24);transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease;}
.gs-blog-card:hover{transform:translateY(-4px);border-color:rgba(124,159,255,.32);box-shadow:0 24px 58px rgba(0,0,0,.34);}
.gs-blog-img{display:block;aspect-ratio:16/9;overflow:hidden;background:#070914;border-bottom:1px solid rgba(255,255,255,.07);}
.gs-blog-img img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .35s ease;}
.gs-blog-card:hover .gs-blog-img img{transform:scale(1.035);}
.gs-blog-body{display:flex;flex:1;flex-direction:column;gap:10px;padding:16px 18px 18px;}
.gs-blog-badge{align-self:flex-start;display:inline-flex;align-items:center;min-height:28px;padding:0 11px;border-radius:10px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);color:rgba(219,226,255,.72);font-size:10.5px;font-weight:850;text-transform:uppercase;letter-spacing:.035em;}
.gs-blog-title{font-weight:850;font-size:1rem;line-height:1.35;color:#fff;letter-spacing:-.015em;}
.gs-blog-ex{font-size:.84rem;line-height:1.55;color:rgba(255,255,255,.54);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.gs-blog-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:auto;padding-top:14px;border-top:1px solid rgba(255,255,255,.075);}
.gs-blog-date,.gs-blog-read{font-size:.76rem;font-weight:750;display:inline-flex;align-items:center;gap:6px;}
.gs-blog-date{color:rgba(255,255,255,.4);}
.gs-blog-read{color:#fff;}
.gs-blog-actions{display:flex;justify-content:center;margin-top:24px;}
.gs-blog-actions .gs-showall{min-width:120px;text-align:center;}
.gs-blog--legacy{display:none;}
@media(max-width:900px){.gs-blog-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:600px){.gs-blog-grid{grid-template-columns:1fr;}.gs-blog-card{border-radius:18px;}}
</style>
<?php $this->end('styles') ?>

<?php
$csGsGameIcon = '';
$gsIconSlug = $gsGameSlug !== '' ? $gsGameSlug : (string)$game;
if (function_exists('util_game_icon_url')) {
    $csGsGameIcon = (string)util_game_icon_url($gsIconSlug);
}
if ($csGsGameIcon === '' && function_exists('util_get_game_by_slug')) {
    $csGsGameIcon = (string)(util_get_game_by_slug($gsIconSlug)['icon'] ?? '');
}
$gsFirstBoostHref = '';
foreach ((array)$boostForms as $gsBoostForm) {
    if (($gsBoostForm['slug'] ?? '') === 'rank-boost') {
        $gsFirstBoostHref = (string)($gsBoostForm['href'] ?? '');
        break;
    }
}
if ($gsFirstBoostHref === '' && !empty($boostForms[0]['href'])) {
    $gsFirstBoostHref = (string)$boostForms[0]['href'];
}

$gsFirstCategoryHref = $gsFirstBoostHref !== ''
    ? $gsFirstBoostHref
    : (!empty($cards[0]['href']) ? (string)$cards[0]['href'] : '#');
?>
<section class="gs-hero">
  <div class="gs-hero__inner">
    <div class="gs-hero__main">
      <div class="gs-hero__icon">
        <?php if ($csGsGameIcon): ?><img src="<?= htmlspecialchars($csGsGameIcon) ?>" alt="<?= htmlspecialchars($meta['h1'] ?? '', ENT_QUOTES) ?>" loading="eager" fetchpriority="high" decoding="async"><?php else: ?><i class="fa-solid fa-gamepad"></i><?php endif; ?>
      </div>
      <div>
        <div class="gs-hero__kicker"><?= t('Game Overview') ?></div>
        <h1 class="gs-hero__title"><?= htmlspecialchars($meta['h1'] ?? '', ENT_QUOTES) ?></h1>
        <?php if (!empty($meta['description'])): ?><p class="gs-hero__desc"><?= htmlspecialchars($meta['description'], ENT_QUOTES) ?></p><?php endif; ?>
        <div class="gs-hero__actions">
          <?php
          // Primary CTA jumps to the service buttons below; falls back to a real
          // category/boost page when there are no cards to scroll to.
          $gsHeroPrimaryHref = !empty($cards) ? '#gs-categories' : $gsFirstCategoryHref;
          ?>
          <?php if ($gsHeroPrimaryHref !== '#'): ?>
            <a class="gs-hero__pill gs-hero__pill--primary" href="<?= htmlspecialchars($gsHeroPrimaryHref, ENT_QUOTES) ?>"><i class="fa-solid fa-bolt"></i><?= t('Start now') ?></a>
          <?php endif; ?>
          <?php if (in_array(strtolower((string)$game), $gsBoosterGames, true)): ?>
            <a class="gs-hero__pill" href="/boosters/"><i class="fa-solid fa-users"></i><?= t('Our Boosters') ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<div class="gs">

  <?php if (!empty($cards)): ?>
  <section class="gs-sec" id="gs-categories" style="scroll-margin-top:90px">
    <div class="gs-sec-head">
      <div>
        <h2><?= t('Shop by category') ?></h2>
        <p class="gs-sec-lead"><?= t('Pick what you need and go directly to the matching offers.') ?></p>
      </div>
    </div>
    <div class="gs-tiles">
      <?php foreach ($cards as $idx => $card): ?>
        <a class="gs-tile"
           style="--gs-accent:<?= htmlspecialchars(gs_service_card_accent($card), ENT_QUOTES) ?>"
           href="<?= htmlspecialchars($card['href'], ENT_QUOTES) ?>">
          <span class="gs-tile__ico">
            <i class="<?= htmlspecialchars(gs_service_card_icon($card), ENT_QUOTES) ?>"></i>
          </span>
          <div class="gs-tile__name"><?= htmlspecialchars(gs_service_card_label($card, $gsGameSlug), ENT_QUOTES) ?></div>
          <i class="fa-solid fa-arrow-right gs-tile__go" aria-hidden="true"></i>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($boostForms)): ?>
  <section class="gs-sec" id="gs-boosting">
    <div class="gs-sec-head">
      <div>
        <h2><?= t('Choose your boosting service') ?></h2>
        <p class="gs-sec-lead"><?= t('Select the service that matches your goal. Your options and price are shown on the next page.') ?></p>
      </div>
      <div class="gs-sec-head__r" id="gs-arr-b" style="display:none">
        <button class="gs-arr" onclick="gsSlide('b',-1)" aria-label="Previous services"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="gs-arr" onclick="gsSlide('b',1)" aria-label="Next services"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>
    <div class="gs-sw"><div class="gs-sl" id="gs-b">
      <?php foreach ($boostForms as $gsBoostIndex => $form):
        $badge = !empty($form['is_hot']) ? 'hot' : (!empty($form['is_new']) ? 'new' : '');
        // PHP 8.1 emits a deprecation notice for htmlspecialchars(null). With
        // display_errors on, that notice is printed inside the href attribute
        // and breaks the whole card, so cast every value before escaping it.
        $gsBoostHref = trim((string)($form['href'] ?? ''));
        $gsBoostName = trim((string)($form['name'] ?? ''));
        $gsBoostDesc = trim((string)($form['description'] ?? ''));
        if ($gsBoostHref === '') continue;
      ?>
        <a class="gs-boost<?= $gsBoostIndex === 0 ? ' gs-boost--featured' : '' ?>" href="<?= htmlspecialchars($gsBoostHref, ENT_QUOTES) ?>">
          <?php if ($gsBoostIndex === 0): ?><span class="gs-boost__popular"><i class="fa-solid fa-fire"></i><?= t('Most popular') ?></span><?php endif; ?>
          <div class="gs-boost__ico">
            <?php if (!empty($form['icon_url'])): ?>
              <img src="<?= htmlspecialchars((string)$form['icon_url'], ENT_QUOTES) ?>" alt="" loading="lazy" decoding="async">
            <?php else: ?>
              <i class="fa-solid <?= htmlspecialchars((string)($form['icon_class'] ?? 'fa-bolt'), ENT_QUOTES) ?>"></i>
            <?php endif; ?>
          </div>
          <div class="gs-boost__name"><?= htmlspecialchars($gsBoostName, ENT_QUOTES) ?></div>
          <?php if ($gsBoostDesc !== ''): ?>
            <div class="gs-boost__desc"><?= htmlspecialchars(mb_strimwidth($gsBoostDesc, 0, 92, '…'), ENT_QUOTES) ?></div>
          <?php endif; ?>
          <div class="gs-boost__foot">
            <span class="gs-boost__cta"><?= t('View service') ?> <i class="fa-solid fa-arrow-right"></i></span>
            <?php if ($badge): ?><span class="gs-bdg gs-bdg--<?= $badge ?>"><?= strtoupper($badge) ?></span><?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div></div>
  </section>
  <?php endif; ?>

  <?php if (in_array(strtolower((string)$game), $gsBoosterGames, true)): ?>
  <section class="gs-sec gs-boosters" aria-label="<?= t('Our Boosters') ?>">
    <div class="gs-sec-head">
      <div>
        <h2><?= t('Our Boosters') ?></h2>
        <p class="gs-sec-lead"><?= t('Meet verified players for this game, using live data from our booster directory.') ?></p>
      </div>
      <div class="gs-sec-head__r">
        <?php if (!empty($gsFeaturedBoosters)): ?>
          <div class="gs-booster-controls">
            <button class="gs-arr" type="button" data-gs-booster-prev aria-label="Previous boosters"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="gs-arr" type="button" data-gs-booster-next aria-label="Next boosters"><i class="fa-solid fa-chevron-right"></i></button>
          </div>
        <?php endif; ?>
        <a class="gs-showall" href="/boosters/"><?= t('View all boosters') ?></a>
      </div>
    </div>

    <?php if (!empty($gsFeaturedBoosters)): ?>
      <div class="gs-booster-slider">
        <div class="gs-booster-grid" id="gs-booster-slider">
          <?= $this->insert('website/components/boosters/booster-cards', [
              'boosters' => $gsFeaturedBoosters,
              'selected_game' => $gsBoosterProfileGame,
          ]) ?>
        </div>
      </div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($featuredAccounts)): ?>
  <section class="gs-sec gs-acc">
    <div class="gs-sec-head">
      <div>
        <h2><?= t('Featured') ?> <?= htmlspecialchars($gameName) ?> <?= t('Accounts') ?></h2>
        <p class="gs-sec-lead"><?= t('A quick look at trusted listings from the marketplace.') ?></p>
      </div>
      <div class="gs-sec-head__r" id="gs-arr-a">
        <a class="gs-showall" href="/<?= htmlspecialchars($gsGameSlug !== '' ? $gsGameSlug : (string)$game) ?>/accounts"><?= t('Show All') ?></a>
        <span id="gs-arrbtn-a" style="display:none;align-items:center;gap:10px">
          <button class="gs-arr" onclick="gsSlide('a',-1)"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="gs-arr" onclick="gsSlide('a',1)"><i class="fa-solid fa-chevron-right"></i></button>
        </span>
      </div>
    </div>
    <div class="gs-sw"><div class="gs-sl" id="gs-a">
      <?php
        $gsAccountsHtml = $this->insert('website/components/accounts/account-cards', [
            'accounts' => $featuredAccounts,
            'game' => $game,
        ]);
        echo preg_replace('/<img(?![^>]*\bloading=)/i', '<img loading="lazy" decoding="async" fetchpriority="low"', $gsAccountsHtml);
      ?>
    </div></div>
  </section>
  <?php endif; ?>

  <?php if (!empty($featuredItems)): ?>
  <section class="gs-sec gs-items">
    <div class="gs-sec-head">
      <div>
        <h2><?= t('Featured') ?> <?= htmlspecialchars($gameName) ?> <?= t('Items') ?></h2>
        <p class="gs-sec-lead"><?= t('Popular item listings from verified sellers.') ?></p>
      </div>
      <div class="gs-sec-head__r" id="gs-arr-i">
        <a class="gs-showall" href="/<?= htmlspecialchars($gsGameSlug !== '' ? $gsGameSlug : (string)$game) ?>/items"><?= t('Show All') ?></a>
        <span id="gs-arrbtn-i" style="display:none;align-items:center;gap:10px">
          <button class="gs-arr" onclick="gsSlide('i',-1)"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="gs-arr" onclick="gsSlide('i',1)"><i class="fa-solid fa-chevron-right"></i></button>
        </span>
      </div>
    </div>
    <div class="gs-sw"><div class="gs-sl" id="gs-i">
      <?php
        $gsItemsHtml = $this->insert('website/components/items/item-cards', [
            'items' => $featuredItems,
            'currentGameSlug' => $gsGameSlug !== '' ? $gsGameSlug : (string)$game,
        ]);
        echo preg_replace('/<img(?![^>]*\bloading=)/i', '<img loading="lazy" decoding="async" fetchpriority="low"', $gsItemsHtml);
      ?>
    </div></div>
  </section>
  <?php endif; ?>

  <?php if (!empty($gsArticles)): ?>
  <section class="gs-sec gs-blog gs-blog--legacy">
    <div class="gs-sec-head">
      <div>
        <h2><?= htmlspecialchars($gameName) ?> <?= t('Guides & News') ?></h2>
        <p class="gs-sec-lead"><?= t('Latest articles and guides for this game.') ?></p>
      </div>
      <div class="gs-sec-head__r">
        <a class="gs-showall" href="<?= BASE_URL ?>/blog/categories/<?= rawurlencode($gsGameSlug !== '' ? $gsGameSlug : (string)$game) ?>"><?= t('Show All') ?></a>
      </div>
    </div>
    <div class="gs-blog-grid">
      <?php foreach ($gsArticles as $gsArticle):
        $gsArticleExcerpt = trim(strip_tags((string)($gsArticle['excerpt'] ?? $gsArticle['description'] ?? '')));
        if (function_exists('mb_strimwidth')) $gsArticleExcerpt = mb_strimwidth($gsArticleExcerpt, 0, 140, '…', 'UTF-8');
      ?>
        <a class="gs-blog-card" href="<?= BASE_URL ?>/blog/<?= esc($gsArticle['slug']) ?>">
          <?php if (!empty($gsArticle['image_url'])): ?>
            <span class="gs-blog-img"><img src="<?= htmlspecialchars((string)$gsArticle['image_url'], ENT_QUOTES) ?>" alt="<?= esc($gsArticle['title']) ?>" loading="lazy" decoding="async"></span>
          <?php endif; ?>
          <span class="gs-blog-body">
            <span class="gs-blog-title"><?= esc($gsArticle['title']) ?></span>
            <?php if ($gsArticleExcerpt !== ''): ?><span class="gs-blog-ex"><?= esc($gsArticleExcerpt) ?></span><?php endif; ?>
            <span class="gs-blog-date"><i class="fa-solid fa-calendar"></i> <?= util_format_date_display($gsArticle['updated_at'] ?? '') ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="gs-sec gs-proof" aria-label="<?= t('Customer reviews') ?>">
    <div class="gs-proof__head">
      <div>
        <div class="gs-proof__eyebrow"><?= t('Reviews') ?></div>
        <h2><?= t('Community Trust') ?></h2>
        <p class="gs-proof__lead"><?= t('Real feedback from customers who ordered boosting, accounts, top-ups and marketplace items.') ?></p>
      </div>
      <div class="gs-proof__rating" aria-label="<?= t('Rated Excellent') ?>">
        <strong><?= t('Rated Excellent') ?></strong>
        <span class="gs-proof__stars" aria-hidden="true">
          <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
        </span>
        <span><?= t('1000+ customer ratings') ?></span>
      </div>
    </div>

    <div class="gs-proof__marquee">
      <?php
        $gsReviewsA = [
          ['S', 'Sophie K.', 'LoL Boosting', 'Climbed from Gold to Diamond in two weeks. The booster was professional, fast and very communicative.', 'League of Legends'],
          ['J', 'Jake R.', 'Fortnite Skins', 'Got rare skins at a great price. Account transfer was smooth and quick.', 'Fortnite'],
          ['M', 'Maria T.', 'V-Bucks Top-up', 'Instant delivery, no issues. Will buy again without hesitation.', 'Fortnite'],
          ['L', 'Lukas B.', 'CS2 Account', 'Prime account with a lot of hours. Exactly what I needed for competitive play.', 'CS2'],
          ['A', 'Alex M.', 'Valorant Account', 'Super fast delivery and the account was exactly as described. Highly recommend.', 'Valorant'],
        ];
        $gsReviewsB = [
          ['N', 'Noah W.', 'Apex Account', 'Great selection of accounts. Found one with all the heirlooms I wanted.', 'Apex Legends'],
          ['C', 'Clara Z.', 'Gift Card', 'PSN code worked instantly. Super smooth experience from start to finish.', 'PSN'],
          ['P', 'Paula N.', 'Coaching Session', 'The coach improved my positioning and game sense drastically. Worth every cent.', 'Coaching'],
          ['T', 'Tom C.', 'TFT Boosting', 'Hit Challenger on TFT faster than I expected. Booster explained their strategy too.', 'TFT'],
          ['E', 'Eva H.', 'Riot Points', 'RP showed up in seconds. Cheapest and fastest place I’ve found.', 'Valorant'],
        ];
        foreach ([$gsReviewsA, $gsReviewsB] as $gsReviewRow):
          $gsLoopReviews = array_merge($gsReviewRow, $gsReviewRow);
      ?>
        <div class="gs-proof__row">
          <?php foreach ($gsLoopReviews as $review): ?>
            <article class="gs-review">
              <div class="gs-review__top">
                <span class="gs-review__avatar"><img src="/public/assets/website/images/reviews/default.webp" alt="" loading="lazy"></span>
                <div>
                  <div class="gs-review__name"><?= htmlspecialchars($review[1], ENT_QUOTES) ?></div>
                  <div class="gs-review__type"><?= htmlspecialchars($review[2], ENT_QUOTES) ?></div>
                </div>
              </div>
              <div class="gs-review__stars" aria-label="5 stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
              <p class="gs-review__text"><?= t($review[3]) ?></p>
              <span class="gs-review__tag"><?= htmlspecialchars($review[4], ENT_QUOTES) ?></span>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="gs-sec gs-faq" aria-label="<?= htmlspecialchars($gsFaq['title'], ENT_QUOTES, 'UTF-8') ?>">
    <div class="gs-faq__grid">
      <div class="gs-faq__top">
        <div class="gs-faq__head">
          <div class="gs-faq__eyebrow"><?= htmlspecialchars($gsFaq['eyebrow'], ENT_QUOTES, 'UTF-8') ?></div>
          <h2><?= htmlspecialchars($gsFaq['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="gs-faq__lead"><?= htmlspecialchars($gsFaq['lead'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <aside class="gs-faq__aside">
          <div class="gs-faq__aside-title"><?= htmlspecialchars($gsFaq['aside_title'], ENT_QUOTES, 'UTF-8') ?></div>
          <p class="gs-faq__aside-text"><?= htmlspecialchars($gsFaq['aside_text'], ENT_QUOTES, 'UTF-8') ?></p>
          <a class="gs-faq__aside-link" href="#" role="button" data-tawk-open="1">
            <i class="fa-solid fa-headset"></i><?= htmlspecialchars($gsFaq['aside_button'], ENT_QUOTES, 'UTF-8') ?>
          </a>
        </aside>
      </div>

      <div class="gs-faq__panel">
        <?php foreach ($gsFaq['items'] as $faqIndex => $faqItem): ?>
          <details class="gs-faq__item"<?= $faqIndex === 0 ? ' open' : '' ?>>
            <summary>
              <?= htmlspecialchars((string)$faqItem['question'], ENT_QUOTES, 'UTF-8') ?>
              <i class="fa-solid fa-chevron-down"></i>
            </summary>
            <div class="gs-faq__body">
              <?= nl2br(htmlspecialchars((string)$faqItem['answer'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($gsArticles)): ?>
  <section class="gs-sec gs-blog">
    <div class="gs-sec-head">
      <div>
        <h2><?= htmlspecialchars($gameName) ?> <?= t('Guides & News') ?></h2>
        <p class="gs-sec-lead"><?= t('Latest articles and guides for this game.') ?></p>
      </div>
    </div>
    <div class="gs-blog-grid">
      <?php foreach ($gsArticles as $gsArticle):
        $gsArticleExcerpt = trim(strip_tags((string)($gsArticle['excerpt'] ?? $gsArticle['description'] ?? '')));
        if (function_exists('mb_strimwidth')) $gsArticleExcerpt = mb_strimwidth($gsArticleExcerpt, 0, 140, '…', 'UTF-8');
      ?>
        <a class="gs-blog-card" href="<?= BASE_URL ?>/blog/<?= esc($gsArticle['slug']) ?>">
          <?php if (!empty($gsArticle['image_url'])): ?>
            <span class="gs-blog-img"><img src="<?= htmlspecialchars((string)$gsArticle['image_url'], ENT_QUOTES) ?>" alt="<?= esc($gsArticle['title']) ?>" loading="lazy" decoding="async"></span>
          <?php endif; ?>
          <span class="gs-blog-body">
            <span class="gs-blog-badge"><?= t('Article') ?></span>
            <span class="gs-blog-title"><?= esc($gsArticle['title']) ?></span>
            <?php if ($gsArticleExcerpt !== ''): ?><span class="gs-blog-ex"><?= esc($gsArticleExcerpt) ?></span><?php endif; ?>
            <span class="gs-blog-footer">
              <span class="gs-blog-date"><i class="fa-solid fa-calendar"></i> <?= util_format_date_display($gsArticle['updated_at'] ?? '') ?></span>
              <span class="gs-blog-read"><?= t('Read') ?> <i class="fa-solid fa-arrow-right"></i></span>
            </span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="gs-blog-actions">
      <a class="gs-showall" href="<?= BASE_URL ?>/blog/categories/<?= rawurlencode($gsGameSlug !== '' ? $gsGameSlug : (string)$game) ?>"><?= t('View all') ?></a>
    </div>
  </section>
  <?php endif; ?>

</div>

<?php $this->start('scripts') ?>
<script>
var _gsl = {b:'gs-b', a:'gs-a', i:'gs-i'};

function gsSlide(k, d) {
  var el = document.getElementById(_gsl[k]);
  if (!el) return;
  var card = el.firstElementChild;
  el.scrollBy({ left: d * ((card ? card.offsetWidth : 300) + 16) * 2, behavior: 'smooth' });
}

function gsCheckArrows() {
  // Boost arrows
  var slB = document.getElementById('gs-b');
  var arrB = document.getElementById('gs-arr-b');
  if (slB && arrB) {
    arrB.style.display = slB.scrollWidth > slB.clientWidth + 10 ? 'flex' : 'none';
  }
  // Account arrows
  var slA = document.getElementById('gs-a');
  var btnA = document.getElementById('gs-arrbtn-a');
  if (slA && btnA) {
    btnA.style.display = slA.scrollWidth > slA.clientWidth + 10 ? 'inline-flex' : 'none';
  }
  // Item arrows
  var slI = document.getElementById('gs-i');
  var btnI = document.getElementById('gs-arrbtn-i');
  if (slI && btnI) {
    btnI.style.display = slI.scrollWidth > slI.clientWidth + 10 ? 'inline-flex' : 'none';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  gsCheckArrows();
  window.addEventListener('resize', gsCheckArrows);
});

// "Chat with support" in the FAQ uses the shared [data-tawk-open] handler
// from header.php.
</script>
<?php $this->end('scripts') ?>

<style id="lb-shop-hero-subnav-seat-final">
body.game-hub .gs-hero{margin-top:var(--gs-hero-nav-gap,0px)!important}
@media(max-width:1024px){
  body.game-hub .gs-hero{margin-top:0!important}
}
</style>
<script id="lb-shop-hero-subnav-seat-js">
(function(){
  function seatGameServicesHero(){
    var hero = document.querySelector('main > .gs-hero, .page-zoom > main > .gs-hero, .gs-hero');
    var subnav = document.querySelector('.lb-game-subnav');
    if(!hero || !subnav) return;

    document.documentElement.style.setProperty('--gs-hero-nav-gap','0px');
    // The desktop subnav is display:none on mobile. Measuring it there returns
    // a bottom edge of 0 and creates a negative hero margin that cancels the
    // dynamic mobile header/gamebar offset.
    if(window.innerWidth <= 1024) return;

    requestAnimationFrame(function(){
      var gap = Math.round(hero.getBoundingClientRect().top - subnav.getBoundingClientRect().bottom);
      document.documentElement.style.setProperty('--gs-hero-nav-gap', gap > 1 ? (-gap) + 'px' : '0px');
    });
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', seatGameServicesHero);
  } else {
    seatGameServicesHero();
  }
  window.addEventListener('load', seatGameServicesHero);
  window.addEventListener('resize', seatGameServicesHero);
  setTimeout(seatGameServicesHero, 250);
})();
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  var slider = document.getElementById('gs-booster-slider');
  if (!slider) return;
  var prev = document.querySelector('[data-gs-booster-prev]');
  var next = document.querySelector('[data-gs-booster-next]');
  function amount() {
    var card = slider.querySelector('.gs-booster-card');
    return card ? card.getBoundingClientRect().width + 16 : 316;
  }
  if (prev) prev.addEventListener('click', function () { slider.scrollBy({left: -amount(), behavior: 'smooth'}); });
  if (next) next.addEventListener('click', function () { slider.scrollBy({left: amount(), behavior: 'smooth'}); });
});
</script>
