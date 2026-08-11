<?php
/**
 * Sitemap generator.
 *
 * The old sitemap.xml was hand-maintained and listed 31 URLs — only LoL and
 * Valorant. Every game added since (TFT, CS2, Fortnite, Apex, Rocket League,
 * Overwatch 2, Marvel Rivals, Wild Rift, LoL Classic) was missing entirely, and
 * so was every blog post written after the file was last touched.
 *
 * This builds the file from the database instead, so it can never drift again.
 * It deliberately writes a static file rather than serving /sitemap.xml through
 * PHP: the webserver can then deliver it with zero PHP/DB cost, which matters
 * because crawler traffic is exactly what drives the CPU spikes.
 *
 * Run from cron, e.g. daily:
 *   php /home/USER/public_html/app/tasks/sitemap_generator.php
 *
 * Or over HTTP, using the exact value of SITEMAP_SECRET below as ?key=
 *   https://lolboost.gg/app/tasks/sitemap_generator.php?key=lb_sitemap_9fK2mQ7xR4
 */

set_time_limit(300);
date_default_timezone_set('Europe/Berlin');

// Change this if it ever leaks. It only guards regeneration, no data is exposed.
const SITEMAP_SECRET = 'lb_sitemap_9fK2mQ7xR4';

$sm_is_cli = (PHP_SAPI === 'cli');

// app/tasks/ is web-reachable (that is how notification_sender.php gets fired),
// so an unguarded script here could be hammered by anyone.
if (!$sm_is_cli) {
    if (!hash_equals(SITEMAP_SECRET, (string)($_GET['key'] ?? ''))) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Forbidden\n";
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
}

/** Writes a line to the browser or terminal and always to the PHP error log. */
function sm_out(string $line, bool $isError = false): void
{
    echo $line;
    if ($isError) {
        error_log('[sitemap_generator] ' . trim($line));
    }
}

$_sm_root = dirname(dirname(__DIR__));
require $_sm_root . '/vendor/autoload.php';
require $_sm_root . '/app/core/config.php';
require $_sm_root . '/app/core/functions.php';

$base = rtrim((string)BASE_URL, '/');
$urls = [];
$seen = [];

/**
 * @param string $path      Path beginning with "/".
 * @param string $priority  0.0 - 1.0
 * @param string $changefreq daily|weekly|monthly|yearly
 */
$add = function (string $path, string $priority = '0.5', string $changefreq = 'weekly', ?string $lastmod = null)
       use (&$urls, &$seen, $base): void {
    $path = '/' . ltrim(trim($path), '/');
    if ($path === '/') $path = '';

    // A URL listed twice tells crawlers nothing and only inflates the file.
    $key = strtolower($path);
    if (isset($seen[$key])) return;
    $seen[$key] = true;

    // lastmod is deliberately omitted unless we know a real modification date.
    // Stamping today's date on every run would tell Google the whole site changed
    // daily; it detects that and then ignores lastmod everywhere, including the
    // articles where the date is genuine.
    $urls[] = [
        'loc' => $base . $path,
        'lastmod' => $lastmod,
        'changefreq' => $changefreq,
        'priority' => $priority,
    ];
};

// ── Static pages ─────────────────────────────────────────────────────────────
$add('/', '1.0', 'daily');
$add('/services/boosting', '0.8', 'weekly');
$add('/services/accounts', '0.8', 'weekly');
$add('/services/items', '0.8', 'weekly');
$add('/services/coaching', '0.8', 'weekly');
$add('/services/currencies', '0.8', 'weekly');
$add('/services/top-ups', '0.8', 'weekly');
$add('/services/digital-goods', '0.8', 'weekly');
$add('/boosters', '0.7', 'daily');
$add('/reviews', '0.7', 'daily');
$add('/digital-goods', '0.7', 'daily');
$add('/ggirls', '0.6', 'weekly');
$add('/blog', '0.6', 'daily');
$add('/blog/categories', '0.4', 'weekly');
$add('/jobs', '0.5', 'monthly');
$add('/work-with-us', '0.5', 'monthly');
$add('/contact', '0.4', 'monthly');
$add('/legal/terms', '0.2', 'yearly');
$add('/legal/privacy', '0.2', 'yearly');
$add('/legal/imprint', '0.2', 'yearly');

// ── Per game: landing page, every boost form, and shop sections with stock ───
//
// util_game_has_service() only says a service is switched on, and it is switched
// on for every one of the ~275 rows in the games table. That is why an earlier
// version emitted a landing page for all of them. A shop section is only worth
// listing when it actually has something to sell, so count real listings first.
$listingCounts = ['accounts' => [], 'items' => [], 'topups' => []];

/** Normalises the different slug spellings used across the listing tables. */
$canonGame = static function ($value): string {
    $v = strtolower(trim((string)$value));
    $aliases = [
        'lol' => 'league-of-legends',
        'league' => 'league-of-legends',
        'val' => 'valorant',
        'tft' => 'teamfight-tactics',
    ];
    return $aliases[$v] ?? $v;
};

try {
    global $db;

    foreach ($db->run(
        "SELECT LOWER(TRIM(COALESCE(game,''))) AS g, COUNT(*) AS c
           FROM selling_accounts
          WHERE sold = 0 AND COALESCE(active, 1) = 1
          GROUP BY g"
    ) ?: [] as $row) {
        $key = $canonGame($row['g'] ?? '');
        if ($key !== '') $listingCounts['accounts'][$key] = (int)($row['c'] ?? 0);
    }

    foreach ($db->run(
        "SELECT COALESCE(g.slug, LOWER(TRIM(COALESCE(si.game,'')))) AS g, COUNT(*) AS c
           FROM selling_items si
           LEFT JOIN games g ON g.id = si.game_id
          WHERE COALESCE(si.active, 1) = 1
          GROUP BY g"
    ) ?: [] as $row) {
        $key = $canonGame($row['g'] ?? '');
        if ($key !== '') $listingCounts['items'][$key] = (int)($row['c'] ?? 0);
    }

    foreach ($db->run(
        "SELECT COALESCE(g.slug, LOWER(TRIM(COALESCE(st.game_slug,'')))) AS g, COUNT(*) AS c
           FROM selling_topups st
           LEFT JOIN games g ON g.id = st.game_id
          WHERE COALESCE(st.active, 1) = 1
          GROUP BY g"
    ) ?: [] as $row) {
        $key = $canonGame($row['g'] ?? '');
        if ($key !== '') $listingCounts['topups'][$key] = (int)($row['c'] ?? 0);
    }
} catch (\Throwable $e) {
    sm_out("Could not count listings: " . $e->getMessage() . "\n", true);
}

$games = function_exists('util_get_all_games') ? (util_get_all_games() ?: []) : [];
$gameCount = 0;
$formCount = 0;

foreach ($games as $game) {
    $slug = strtolower(trim((string)($game['slug'] ?? '')));
    if ($slug === '') continue;
    if (isset($game['status']) && (int)$game['status'] !== 1) continue;

    $forms = function_exists('util_load_game_boost_forms') ? (util_load_game_boost_forms($slug) ?: []) : [];
    $formSlugs = [];
    foreach ($forms as $form) {
        $formSlug = trim((string)($form['slug'] ?? ''));
        if ($formSlug !== '') $formSlugs[] = $formSlug;
    }

    // A shop section needs the service enabled AND at least one live listing.
    // Without stock the page renders empty, so listing it would send crawlers to
    // a thin page and waste crawl budget that the real pages need.
    $gameId = (int)($game['id'] ?? 0);
    $countKey = $canonGame($slug);
    $svc = static function (int $id, string $type): bool {
        return $id > 0 && function_exists('util_game_has_service')
            && util_game_has_service($id, $type);
    };

    $hasAccounts = $svc($gameId, 'accounts')
        && ($listingCounts['accounts'][$countKey] ?? 0) > 0;
    $hasItems = $svc($gameId, 'items')
        && ($listingCounts['items'][$countKey] ?? 0) > 0;
    $hasTopups = ($svc($gameId, 'topups') || $svc($gameId, 'top-ups') || $svc($gameId, 'currencies'))
        && ($listingCounts['topups'][$countKey] ?? 0) > 0;

    // Nothing to boost and nothing in stock means the landing page is empty too.
    if (empty($formSlugs) && !$hasAccounts && !$hasItems && !$hasTopups) {
        continue;
    }

    $gameCount++;
    $add('/' . $slug, '0.9', 'weekly');

    // Boost forms are the actual money pages, so they rank highest after home.
    foreach ($formSlugs as $formSlug) {
        $add('/' . $slug . '/' . $formSlug, '0.9', 'weekly');
        $formCount++;
    }

    if ($hasAccounts) $add('/' . $slug . '/accounts', '0.8', 'daily');
    if ($hasItems)    $add('/' . $slug . '/items', '0.8', 'daily');
    if ($hasTopups)   $add('/' . $slug . '/top-ups', '0.8', 'daily');
}

// Hand-built LoL landing pages that are not boost_forms rows. Only URLs that
// answer 200 belong here — /lol/hire-pro-teammate and /lol/premium-accounts
// redirect, and a sitemap should never list a redirect.
$add('/lol/ranked-5s', '0.8', 'weekly');
$add('/lol/smurf-accounts', '0.8', 'daily');

// ── Blog ─────────────────────────────────────────────────────────────────────
$articleCount = 0;
try {
    $articles = db_get_rows('articles', ['order' => 'updated_at,DESC'], 1) ?: [];
    foreach ($articles as $article) {
        $articleSlug = trim((string)($article['slug'] ?? ''));
        if ($articleSlug === '') continue;
        $lastmod = null;
        foreach (['updated_at', 'created_at'] as $dateField) {
            if (!empty($article[$dateField])) {
                $ts = strtotime((string)$article[$dateField]);
                if ($ts !== false) { $lastmod = date('Y-m-d', $ts); break; }
            }
        }
        $add('/blog/' . $articleSlug, '0.5', 'monthly', $lastmod);
        $articleCount++;
    }
} catch (\Throwable $e) {
    sm_out("Could not load articles: " . $e->getMessage() . "\n", true);
}

// Blog categories are games that actually have at least one article — the same
// query the /blog/categories page uses. Listing a game with no posts would only
// send crawlers to an empty page.
$categoryCount = 0;
try {
    global $db;
    $categories = $db->run(
        "SELECT g.slug
           FROM games g
           INNER JOIN articles a ON a.game_id = g.id
          GROUP BY g.slug
          ORDER BY g.name ASC"
    ) ?: [];
    foreach ($categories as $category) {
        $categorySlug = trim((string)($category['slug'] ?? ''));
        if ($categorySlug === '') continue;
        $add('/blog/categories/' . $categorySlug, '0.4', 'weekly');
        $categoryCount++;
    }
} catch (\Throwable $e) {
    sm_out("Could not load blog categories: " . $e->getMessage() . "\n", true);
}

// ── Public booster profiles ──────────────────────────────────────────────────
$boosterCount = 0;
try {
    global $db;
    $boosters = $db->run(
        "SELECT id FROM boosters
          WHERE is_banned = 0
            AND show_profile = 1
            AND COALESCE(is_egirl, 0) = 0
          ORDER BY id ASC"
    ) ?: [];
    foreach ($boosters as $booster) {
        $add('/boosters/' . (int)$booster['id'], '0.4', 'weekly');
        $boosterCount++;
    }
} catch (\Throwable $e) {
    sm_out("Could not load boosters: " . $e->getMessage() . "\n", true);
}

// ── Write the file ───────────────────────────────────────────────────────────
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    $xml .= "  <url>\n";
    $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    if (!empty($url['lastmod'])) {
        $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
    }
    $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
    $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= '</urlset>' . "\n";

$target = $_sm_root . '/sitemap.xml';

// Write to a temp file and rename, so a crawler can never read a half-written
// sitemap while this is running.
$tmp = $target . '.tmp';
if (@file_put_contents($tmp, $xml, LOCK_EX) === false || !@rename($tmp, $target)) {
    @unlink($tmp);
    sm_out("FAILED to write {$target}\n", true);
    exit(1);
}

$summary = sprintf(
    "sitemap.xml written: %d URLs (%d of %d games kept, %d boost forms, %d articles, %d categories, %d boosters)\n"
    . "skipped %d games with no boost form and no live listings\n",
    count($urls), $gameCount, count($games), $formCount, $articleCount, $categoryCount, $boosterCount,
    max(0, count($games) - $gameCount)
);
sm_out($summary);
