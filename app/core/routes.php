<?php
/*
 * Merged _core/routes.php
 * Base: _core (29) test.zip, with live seller image API, live transactions/data endpoint and legacy live URL redirects restored.
 */

// Checkout routes also publish paid orders to the booster realtime room.
$__lbRoutesRealtimeHelper = dirname(__DIR__) . '/helpers/realtime.php';
if (file_exists($__lbRoutesRealtimeHelper)) {
    require_once $__lbRoutesRealtimeHelper;
}

// LoLBoost merge safety helpers for profile routes
if (!function_exists('lb_safe_array')) {
    function lb_safe_array($value): array {
        return is_array($value) ? $value : [];
    }
}


if (!function_exists('item_meta_display_text')) {
    // Only decodes — the result feeds $meta['title'], which master.php layout
    // htmlspecialchars()'s on output. Escaping here too would double-escape.
    function item_meta_display_text($value, string $default = ''): string
    {
        $raw = ($value === null || $value === '') ? $default : (string)$value;
        return html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}


// Dynamic item schemas, used by Admin Games, Seller Items, and game item shops.
if (!function_exists('lb_items_schema_table_ensure')) {
    function lb_items_schema_table_ensure(): void
    {
        global $db;
        if (empty($db)) return;
        try {
            $db->run("CREATE TABLE IF NOT EXISTS game_item_schemas (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                game_slug VARCHAR(100) NOT NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                schema_json JSON NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_game_item_schema (game_slug),
                KEY idx_enabled (enabled)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {}
    }
}

if (!function_exists('lb_items_default_delivery_field')) {
    function lb_items_default_delivery_field(): array
    {
        return [];
    }
}

if (!function_exists('lb_items_schema_with_system_defaults')) {
    function lb_items_schema_with_system_defaults(array $schema): array
    {
        // Waiting Time is no longer an admin JSON field. It is a fixed seller input with amount + unit.
        // Item Type, Server and every other item field stay fully controlled by the Admin Games builder.
        $schema['fields'] = isset($schema['fields']) && is_array($schema['fields']) ? array_values($schema['fields']) : [];
        return $schema;
    }
}

if (!function_exists('lb_items_default_schema')) {
    function lb_items_default_schema(string $gameSlug = ''): array
    {
        // Item Type, Server and other game data stay fully controlled by the Admin Games builder.
        // Waiting Time is handled separately as seller amount + unit.
        return lb_items_schema_with_system_defaults([
            'enabled' => true,
            'title_field' => 'title',
            'headline_icon_field' => 'waiting_time',
            'fields' => [],
        ]);
    }
}

if (!function_exists('lb_get_game_item_schema')) {
    function lb_get_game_item_schema(string $gameSlug): array
    {
        global $db;
        $gameSlug = strtolower(trim($gameSlug));
        $fallback = lb_items_default_schema($gameSlug);
        if ($gameSlug === '' || empty($db)) return $fallback;
        lb_items_schema_table_ensure();
        try {
            $rows = $db->run('SELECT schema_json FROM game_item_schemas WHERE game_slug = ? LIMIT 1', $gameSlug) ?: [];
            if (!empty($rows[0]['schema_json'])) {
                $decoded = json_decode((string)$rows[0]['schema_json'], true);
                if (is_array($decoded)) {
                    $decoded['fields'] = isset($decoded['fields']) && is_array($decoded['fields']) ? array_values($decoded['fields']) : [];
                    $decoded = array_merge($fallback, $decoded);
                    // Do not inject Item Type, Server or Waiting Time into the dynamic admin schema.
                    $decoded['fields'] = isset($decoded['fields']) && is_array($decoded['fields']) ? array_values($decoded['fields']) : [];
                    foreach ($decoded['fields'] as &$__lbItemField) {
                        if (isset($__lbItemField['key']) && $__lbItemField['key'] === 'item_type') $__lbItemField['key'] = 'type';
                        if (
                            in_array($gameSlug, ['league-of-legends', 'lol'], true)
                            && (($__lbItemField['key'] ?? '') === 'type')
                            && (($__lbItemField['type'] ?? '') === 'select')
                        ) {
                            $__lbItemField['options'] = isset($__lbItemField['options']) && is_array($__lbItemField['options'])
                                ? array_values($__lbItemField['options'])
                                : [];
                            $existingItemTypes = [];
                            foreach ($__lbItemField['options'] as $__lbItemOption) {
                                $optionValue = is_array($__lbItemOption)
                                    ? (string)($__lbItemOption['value'] ?? $__lbItemOption['label'] ?? '')
                                    : (string)$__lbItemOption;
                                $existingItemTypes[] = strtolower(trim($optionValue));
                            }
                            foreach ([
                                'icons' => 'Icons',
                                'wards' => 'Wards',
                            ] as $requiredValue => $requiredLabel) {
                                if (!in_array($requiredValue, $existingItemTypes, true)) {
                                    $__lbItemField['options'][] = ['value' => $requiredValue, 'label' => $requiredLabel];
                                }
                            }
                        }
                    }
                    unset($__lbItemField);
                    return lb_items_schema_with_system_defaults($decoded);
                }
            }
        } catch (Throwable $e) {}
        return $fallback;
    }
}

if (!function_exists('lb_get_items_page_config')) {
    function lb_get_items_page_config(string $gameSlug): array
    {
        global $db;
        $gameSlug = strtolower(trim($gameSlug));
        $game = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
        $gameName = $game['name'] ?? ucwords(str_replace('-', ' ', $gameSlug));
        $cfg = [
            'page_title' => $gameName . ' Items',
            'page_description' => 'Browse ' . $gameName . ' items, skins and digital goods on LoLBoost.',
            'filters' => ['type','server','price'],
            'show_type_cards' => true,
        ];
        if (!empty($db) && !empty($game['id'])) {
            try {
                $rows = $db->run('SELECT config FROM game_services WHERE game_id = ? AND service_type = ? LIMIT 1', (int)$game['id'], 'items') ?: [];
                if (!empty($rows[0]['config'])) {
                    $decoded = json_decode((string)$rows[0]['config'], true);
                    if (is_array($decoded)) $cfg = array_merge($cfg, $decoded);
                }
            } catch (Throwable $e) {}
        }
        return $cfg;
    }
}

if (!function_exists('lb_render_item_dynamic_fields')) {
    function lb_render_item_dynamic_fields(string $gameSlug, array $values = []): string
    {
        $schema = lb_get_game_item_schema($gameSlug);
        if (empty($schema['enabled']) || empty($schema['fields']) || !is_array($schema['fields'])) return '';
        $html = '<div class="js-item-dynamic-fields" data-game-fields="'.htmlspecialchars($gameSlug, ENT_QUOTES, 'UTF-8').'">';
        foreach ($schema['fields'] as $field) {
            $key = trim((string)($field['key'] ?? ''));
            if ($key === '' || in_array($key, ['title','description','price','stock','images'], true)) continue;
            $label = trim((string)($field['label'] ?? ucwords(str_replace('_', ' ', $key))));
            $type = trim((string)($field['type'] ?? 'text')) ?: 'text';
            $required = !empty($field['required']);
            $val = $values[$key] ?? '';
            $name = 'item_data[' . $key . ']';
            if ($key === 'item_type') $key = 'type';
            if (in_array($key, ['type','server'], true)) $name = $key;
            $req = $required ? ' required' : '';
            $html .= '<div class="col-md-6 lb-item-dyn-field"><label class="form-label">'.htmlspecialchars($label).($required ? ' <span class="oc-required">*</span>' : '').'</label>';
            if ($type === 'select') {
                $html .= '<select class="form-select" name="'.htmlspecialchars($name).'"'.$req.'><option value="">Choose '.htmlspecialchars($label).'</option>';
                $opts = isset($field['options']) && is_array($field['options']) ? $field['options'] : [];
                foreach ($opts as $opt) {
                    $ov = is_array($opt) ? (string)($opt['value'] ?? $opt['label'] ?? '') : (string)$opt;
                    $ol = is_array($opt) ? (string)($opt['label'] ?? $ov) : (string)$opt;
                    $selected = strtolower(trim((string)$val)) === strtolower(trim($ov)) ? ' selected' : '';
                    $html .= '<option value="'.htmlspecialchars($ov).'"'.$selected.'>'.htmlspecialchars($ol).'</option>';
                }
                $html .= '</select>';
            } elseif ($type === 'textarea') {
                $html .= '<textarea class="form-control" rows="3" name="'.htmlspecialchars($name).'"'.$req.'>'.htmlspecialchars((string)$val).'</textarea>';
            } else {
                $inputType = in_array($type, ['number','date','url','email'], true) ? $type : 'text';
                $html .= '<input type="'.$inputType.'" class="form-control" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars((string)$val).'"'.$req.'>';
            }
            $html .= '<div class="invalid-feedback">Please fill this field.</div></div>';
        }
        $html .= '</div>';
        return $html;
    }
}

use Buki\Router\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CoinbaseCommerce\Webhook;
use const Dom\NOT_FOUND_ERR;

/**
 * ==== Defining Routes ====
 **/

$router = new Router();


// Lightweight anonymous HTML cache for the landing page.
// This avoids re-rendering the huge landing view on every mobile visit.
if (!function_exists('lb_landing_cache_dir')) {
    function lb_landing_cache_dir(): string
    {
        $base = defined('SYS_PATH') ? rtrim((string)SYS_PATH, '/') : dirname(__DIR__);
        return $base . '/public/uploads/private/page-cache';
    }
}

if (!function_exists('lb_landing_cache_can_use')) {
    function lb_landing_cache_can_use(): bool
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return false;
        if (!empty($_GET)) return false;
        if (defined('CLIENT_ID') || defined('ADMIN_ID') || defined('BOOSTER_ID') || defined('SELLER_ID')) return false;
        foreach (['client_id','admin_id','booster_id','seller_id','user_id','uid'] as $sessionKey) {
            if (!empty($_SESSION[$sessionKey])) return false;
        }
        return true;
    }
}

if (!function_exists('lb_landing_cache_file')) {
    function lb_landing_cache_file(): string
    {
        $lang = defined('LANG') ? preg_replace('/[^a-z0-9_-]/i', '', (string)LANG) : 'default';
        $currency = $_SESSION['currency'] ?? 'EUR';
        $currency = preg_replace('/[^A-Z]/', '', strtoupper((string)$currency)) ?: 'EUR';
        $viewFile = defined('VIEW_PATH') ? rtrim((string)VIEW_PATH, '/') . '/website/landing.php' : dirname(__DIR__) . '/views/website/landing.php';
        $version = is_file($viewFile) ? (string)@filemtime($viewFile) : 'v2';
        $version = preg_replace('/[^0-9a-z_-]/i', '', $version) ?: 'v2';
        return lb_landing_cache_dir() . '/landing-' . $lang . '-' . $currency . '-' . $version . '.html';
    }
}

if (!function_exists('lb_landing_cache_serve')) {
    function lb_landing_cache_serve(int $ttlSeconds = 300): bool
    {
        if (!lb_landing_cache_can_use()) return false;
        $file = lb_landing_cache_file();
        if (is_file($file) && (time() - (int)@filemtime($file)) < $ttlSeconds) {
            header('X-LB-Page-Cache: HIT');
            header('Cache-Control: public, max-age=60, stale-while-revalidate=300');
            readfile($file);
            return true;
        }
        header('X-LB-Page-Cache: MISS');
        return false;
    }
}

if (!function_exists('lb_landing_cache_store')) {
    function lb_landing_cache_store(string $html): void
    {
        if (!lb_landing_cache_can_use() || $html === '') return;
        $dir = lb_landing_cache_dir();
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if (is_dir($dir) && is_writable($dir)) {
            @file_put_contents(lb_landing_cache_file(), $html, LOCK_EX);
        }
    }
}

// Anonymous microcache for public catalogue and hub pages. Query-string values
// are part of the key, while signed-in and user-specific requests are excluded.
if (!function_exists('lb_public_page_cache_file')) {
    function lb_public_page_cache_file(string $key): string
    {
        $lang = defined('LANG') ? preg_replace('/[^a-z0-9_-]/i', '', (string)LANG) : 'default';
        $currency = preg_replace('/[^A-Z]/', '', strtoupper((string)($_SESSION['currency'] ?? 'EUR'))) ?: 'EUR';
        $query = $_GET;
        unset($query['path'], $query['lang']);
        ksort($query);
        $version = (string)max((int)@filemtime(__FILE__), (int)@filemtime(__DIR__ . '/functions.php'));
        $hash = hash('sha256', $key . '|' . http_build_query($query) . '|' . $lang . '|' . $currency . '|' . $version);
        return lb_landing_cache_dir() . '/public-' . $hash . '.html';
    }
}

if (!function_exists('lb_public_page_cache_can_use')) {
    function lb_public_page_cache_can_use(): bool
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return false;
        if (defined('CLIENT_ID') || defined('ADMIN_ID') || defined('BOOSTER_ID') || defined('SELLER_ID')) return false;
        foreach (['client_id','admin_id','booster_id','seller_id','user_id','uid'] as $sessionKey) {
            if (!empty($_SESSION[$sessionKey])) return false;
        }
        foreach (array_keys($_GET) as $name) {
            if (in_array((string)$name, ['path','lang'], true)) continue;
            if (!in_array((string)$name, ['page','sort','game','type','server','region','platform','min_price','max_price'], true)) return false;
        }
        return true;
    }
}

if (!function_exists('lb_public_page_cache_serve')) {
    function lb_public_page_cache_serve(string $key, int $ttlSeconds = 120): bool
    {
        if (!lb_public_page_cache_can_use()) return false;
        $file = lb_public_page_cache_file($key);
        if (!is_file($file) || (time() - (int)@filemtime($file)) >= $ttlSeconds) return false;
        header('X-LB-Page-Cache: HIT');
        header('Cache-Control: public, max-age=30, stale-while-revalidate=120');
        readfile($file);
        return true;
    }
}

if (!function_exists('lb_public_page_cache_start')) {
    function lb_public_page_cache_start(): bool
    {
        if (!lb_public_page_cache_can_use()) return false;
        ob_start();
        return true;
    }
}

if (!function_exists('lb_public_page_cache_finish')) {
    function lb_public_page_cache_finish(string $key, bool $started): void
    {
        if (!$started) return;
        $html = (string)ob_get_clean();
        $dir = lb_landing_cache_dir();
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if ($html !== '' && is_dir($dir) && is_writable($dir)) {
            @file_put_contents(lb_public_page_cache_file($key), $html, LOCK_EX);
        }
        header('X-LB-Page-Cache: MISS');
        header('Cache-Control: public, max-age=30, stale-while-revalidate=120');
        echo $html;
    }
}

if (!function_exists('lb_articles_ensure_game_column')) {
    // Blog posts can be tagged with a game so /blog can be filtered and the game
    // service pages can list their own articles. The column is added lazily so no
    // migration step is required.
    function lb_articles_ensure_game_column(): void
    {
        global $db;
        if (empty($db)) return;
        static $done = false;
        if ($done) return;
        $done = true;
        try { $db->run("ALTER TABLE articles ADD COLUMN IF NOT EXISTS game_id INT UNSIGNED NULL DEFAULT NULL"); } catch (\Throwable $e) {}
    }
}

if (!function_exists('lb_article_game_options')) {
    function lb_article_game_options(): array
    {
        global $db;
        lb_articles_ensure_game_column();
        if (empty($db)) return [];
        try {
            return $db->run("SELECT id, name, slug, icon FROM games WHERE status = 1 ORDER BY sort_order ASC, name ASC") ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('lb_blog_render_list')) {
    // Blog list, optionally scoped to one game category. Used by /blog,
    // /blog/categories/<slug> and the legacy /blog?game=<slug> links.
    function lb_blog_render_list(string $gameSlug = ''): void
    {
        global $db;
        lb_articles_ensure_game_column();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $itemsPerPage = 12;

        $gameSlug = strtolower(trim($gameSlug));
        $game = ($gameSlug !== '' && function_exists('util_get_game_by_slug')) ? util_get_game_by_slug($gameSlug) : null;
        $gameId = (int)($game['id'] ?? 0);
        $where = $gameId > 0 ? ' WHERE a.game_id = ' . $gameId : '';

        // An unknown category slug must not silently render the full blog.
        if ($gameSlug !== '' && $gameId <= 0) { redirect_url('blog/categories'); return; }

        $total = 0;
        try {
            $row = $db->row("SELECT COUNT(*) AS cnt FROM articles a{$where}");
            $total = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {}

        $totalPages = max(1, (int)ceil($total / $itemsPerPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $itemsPerPage;

        $articles = [];
        try {
            $articles = $db->run(
                "SELECT a.*, g.name AS game_name, g.slug AS game_slug, g.icon AS game_icon
                 FROM articles a
                 LEFT JOIN games g ON g.id = a.game_id
                 {$where}
                 ORDER BY a.updated_at DESC
                 LIMIT {$itemsPerPage} OFFSET {$offset}"
            ) ?: [];
        } catch (\Throwable $e) {
            $articles = db_get_rows('articles', ['order' => 'updated_at,DESC', 'limit' => $itemsPerPage, 'offset' => $offset]) ?: [];
        }

        $category = $gameId > 0 ? [
            'id'    => $gameId,
            'name'  => (string)($game['name'] ?? ''),
            'slug'  => (string)($game['slug'] ?? $gameSlug),
            'icon'  => (string)($game['icon'] ?? ''),
            'count' => $total,
        ] : [];

        $meta = [
            'h1' => $category ? (($category['name'] ?: 'Game') . ' Articles') : 'Gaming Blog',
            'title' => $category
                ? (($category['name'] ?: 'Game') . ' Articles | LoLBoost Blog')
                : 'Blog | Gaming News & Guides | LoLBoost',
            'description' => $category
                ? t('All guides, news and tips we published for this game.')
                : t('Gaming guides, tips and news for League of Legends, Valorant, Fortnite and more.'),
            'keywords' => 'LoLBoost blog, gaming guides, LoL tips, Valorant guides',
            'canonical' => BASE_URL . ($category ? ('/blog/categories/' . rawurlencode((string)$category['slug'])) : '/blog'),
            'robots' => 'index, follow',
        ];

        view_file('website/pages/blog', [
            'articles' => $articles,
            'meta' => $meta,
            'pagination' => ['page' => $page, 'totalPages' => $totalPages, 'category' => $category['slug'] ?? ''],
            'category' => $category,
            'totalArticles' => $total,
        ]);
    }
}

if (!function_exists('lb_blog_render_article')) {
    function lb_blog_render_article(string $slug): void
    {
        global $db;
        lb_articles_ensure_game_column();

        $article = null;
        try {
            $article = $db->row(
                "SELECT a.*, g.name AS game_name, g.slug AS game_slug, g.icon AS game_icon
                 FROM articles a
                 LEFT JOIN games g ON g.id = a.game_id
                 WHERE a.slug = ?
                 LIMIT 1",
                $slug
            );
        } catch (\Throwable $e) {
            $article = db_get_row('articles', ['slug' => $slug], 1) ?: null;
        }

        if (empty($article)) { redirect_url('blog'); return; }

        $meta = [
            'title' => html_entity_decode((string)$article['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . ' | LoLBoost Blog',
            'description' => (string)($article['description'] ?? ''),
            'canonical' => BASE_URL . '/blog/' . rawurlencode((string)$article['slug']),
            'robots' => 'index, follow',
        ];

        view_file('website/pages/article', ['article' => $article, 'meta' => $meta]);
    }
}

if (!function_exists('lb_blog_render_categories')) {
    // Blog categories overview: one card per game that has at least one article,
    // plus a bucket for articles without a game.
    function lb_blog_render_categories(): void
    {
        global $db;
        lb_articles_ensure_game_column();

        $categories = [];
        $uncategorised = 0;
        try {
            $categories = $db->run(
                "SELECT g.id, g.name, g.slug, g.icon, COUNT(a.id) AS article_count
                 FROM games g
                 INNER JOIN articles a ON a.game_id = g.id
                 GROUP BY g.id, g.name, g.slug, g.icon
                 ORDER BY article_count DESC, g.name ASC"
            ) ?: [];
            $row = $db->row("SELECT COUNT(*) AS cnt FROM articles WHERE game_id IS NULL OR game_id = 0");
            $uncategorised = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            $categories = [];
        }

        $meta = [
            'h1' => 'Blog Categories',
            'title' => 'Blog Categories | Guides per Game | LoLBoost',
            'description' => t('Browse our gaming guides and news by game. Every category collects the articles written for that title.'),
            'keywords' => 'LoLBoost blog categories, gaming guides by game',
            'canonical' => BASE_URL . '/blog/categories',
            'robots' => 'index, follow',
        ];

        view_file('website/pages/blog-categories', [
            'meta' => $meta,
            'categories' => $categories,
            'uncategorised' => $uncategorised,
        ]);
    }
}

if (!function_exists('lb_is_seo_article_admin')) {
    function lb_is_seo_article_admin(): bool
    {
        return defined('ADMIN_DATA')
            && is_array(ADMIN_DATA)
            && strtolower(trim((string)(ADMIN_DATA['email'] ?? ''))) === 'primeseohub92@gmail.com';
    }
}
if (!function_exists('lb_redirect_game_alias')) {
    function lb_redirect_game_alias(string $targetBase, string $path = ''): void
    {
        $targetBase = trim($targetBase, '/');
        $path = trim($path, '/');
        $url = $targetBase . ($path !== '' ? '/' . $path : '');

        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }

        redirect_url($url);
    }
}

if (!function_exists('lb_register_game_alias_routes')) {
    function lb_register_game_alias_routes(Router $router, string $aliasBase, string $targetBase, array $staticPaths = [], array $dynamicPaths = []): void
    {
        $aliasBase = '/' . trim($aliasBase, '/');
        $targetBase = trim($targetBase, '/');

        $router->get($aliasBase, function () use ($targetBase) {
            lb_redirect_game_alias($targetBase);
        });

        foreach ($staticPaths as $path) {
            $path = trim((string) $path, '/');
            if ($path === '') {
                continue;
            }

            $router->get($aliasBase . '/' . $path, function () use ($targetBase, $path) {
                lb_redirect_game_alias($targetBase, $path);
            });
        }

        foreach ($dynamicPaths as $pattern => $targetPattern) {
            $pattern = trim((string) $pattern, '/');
            $targetPattern = trim((string) $targetPattern, '/');
            if ($pattern === '' || $targetPattern === '') {
                continue;
            }

            $router->get($aliasBase . '/' . $pattern, function ($value) use ($targetBase, $targetPattern) {
                $safeValue = rawurlencode((string) $value);
                $targetPath = str_replace([':slug', ':id'], $safeValue, $targetPattern);
                lb_redirect_game_alias($targetBase, $targetPath);
            });
        }
    }
}

$lolAliasPages = [
    'rank-boost',
    'win-boost',
    'placements-boost',
    'arena-boost',
    'normals-boost',
    'champion-mastery',
    'clash-boost',
    'level-boost',
    'matches-boost',
    'pro-games',
    'duo-pass',
    'ggirls',
    'coaching',
    'premium-accounts',
    'accounts',
    'items',
    'hire-pro-teammate',
];

$valorantAliasPages = [
    'rank-boost',
    'win-boost',
    'placements-boost',
    'unrated-matches',
    'coaching',
    'premium-accounts',
    'accounts',
];

$tftAliasPages = [
    'rank-boost',
    'win-boost',
    'placements-boost',
    'double-up-boost',
    'coaching',
];

// Backward-compat: /lol/* → /league-of-legends/*, /val/* → /valorant/*, /tft/* → /teamfight-tactics/*
lb_register_game_alias_routes($router, 'lol', 'league-of-legends', $lolAliasPages, [
    'account/:slug' => 'account/:slug',
    'item/:slug' => 'item/:slug',
    'selling_item/:id' => 'selling_item/:id',
]);
lb_register_game_alias_routes($router, 'val', 'valorant', $valorantAliasPages, [
    'account/:slug' => 'account/:slug',
]);
lb_register_game_alias_routes($router, 'tft', 'teamfight-tactics', $tftAliasPages);


// Legacy live short URLs, kept for compatibility and redirected to canonical multi-game URLs.
if (!function_exists('lb_register_legacy_root_boost_routes')) {
    function lb_register_legacy_root_boost_routes(Router $router): void
    {
        $routes = [
            '/rank-boost'        => 'league-of-legends/rank-boost',
            '/win-boost'         => 'league-of-legends/win-boost',
            '/placements-boost'  => 'league-of-legends/placements-boost',
            '/arena-boost'       => 'league-of-legends/arena-boost',
            '/normals-boost'     => 'league-of-legends/normals-boost',
            '/champion-mastery'  => 'league-of-legends/champion-mastery',
            '/clash-boost'       => 'league-of-legends/clash-boost',
            '/level-boost'       => 'league-of-legends/level-boost',
            '/matches-boost'     => 'league-of-legends/matches-boost',
            '/pro-games'         => 'league-of-legends/pro-games',
            '/duo-pass'          => 'league-of-legends/duo-pass',
            '/coaching'          => 'league-of-legends/coaching',
            '/top-ups'           => 'league-of-legends/top-ups',
            '/unrated-matches'   => 'valorant/unrated-matches',
            '/double-up-boost'   => 'teamfight-tactics/double-up-boost',
        ];
        foreach ($routes as $from => $to) {
            $router->get($from, function () use ($to) { lb_redirect_game_alias($to); });
        }
    }
}
lb_register_legacy_root_boost_routes($router);



// Ajax Operations (It only works with POST method)

$router->post('ajax', function () {
    // Keep AJAX available for restricted admin roles such as the SEO article admin.
    // Article create/update requests are handled and authorized inside App\Ajax.
    $ajax = new \App\Ajax();
    $ajax->run();
});

if (!empty($_GET['ref']) && function_exists('lb_referral_store_code')) {
    lb_referral_store_code((string) $_GET['ref']);
}


// Seller Partner API
$router->group('api/v1', function () {
    global $router;

    // Seller gallery images, restored from live core.
    $router->get('images', function () { seller_api_list_images(); });
    $router->post('images', function () { seller_api_upload_image(); });
    $router->delete('images/:id', function ($id) { seller_api_delete_image($id); });

    $router->get('accounts', function () { seller_api_list_accounts(); });
    $router->post('accounts', function () { seller_api_create_account(); });
    $router->post('accounts/bulk-remove', function () { seller_api_bulk_remove_accounts(); });
    $router->post('accounts/bulk_remove', function () { seller_api_bulk_remove_accounts(); });
    $router->get('accounts/:id', function ($id) { seller_api_get_account($id); });
    $router->patch('accounts/:id', function ($id) { seller_api_update_account($id); });
    $router->delete('accounts/:id', function ($id) { seller_api_delete_account($id); });

    // GameBoost style alias names
    $router->get('account-offers', function () { seller_api_list_accounts(); });
    $router->post('account-offers', function () { seller_api_create_account(); });
    $router->post('account-offers/bulk-remove', function () { seller_api_bulk_remove_accounts(); });
    $router->post('account-offers/bulk_remove', function () { seller_api_bulk_remove_accounts(); });
    $router->get('account-offers/:id', function ($id) { seller_api_get_account($id); });
    $router->patch('account-offers/:id', function ($id) { seller_api_update_account($id); });
    $router->delete('account-offers/:id', function ($id) { seller_api_delete_account($id); });

    // Seller Item API
    $router->get('items', function () { seller_api_list_items(); });
    $router->post('items', function () { seller_api_create_item(); });
    $router->post('items/bulk-remove', function () { seller_api_bulk_remove_items(); });
    $router->post('items/bulk_remove', function () { seller_api_bulk_remove_items(); });
    $router->post('items/bulk-update', function () { seller_api_bulk_update_items(); });
    $router->post('items/bulk_update', function () { seller_api_bulk_update_items(); });
    $router->get('items/:id', function ($id) { seller_api_get_item($id); });
    $router->patch('items/:id', function ($id) { seller_api_update_item($id); });
    $router->delete('items/:id', function ($id) { seller_api_delete_item($id); });

    // GameBoost style alias names
    $router->get('item-offers', function () { seller_api_list_items(); });
    $router->post('item-offers', function () { seller_api_create_item(); });
    $router->post('item-offers/bulk-remove', function () { seller_api_bulk_remove_items(); });
    $router->post('item-offers/bulk_remove', function () { seller_api_bulk_remove_items(); });
    $router->post('item-offers/bulk-update', function () { seller_api_bulk_update_items(); });
    $router->post('item-offers/bulk_update', function () { seller_api_bulk_update_items(); });
    $router->get('item-offers/:id', function ($id) { seller_api_get_item($id); });
    $router->patch('item-offers/:id', function ($id) { seller_api_update_item($id); });
    $router->delete('item-offers/:id', function ($id) { seller_api_delete_item($id); });

    // Seller Top-Ups API
    $router->get('top-ups', function () { seller_api_list_topups(); });
    $router->post('top-ups', function () { seller_api_create_topup(); });
    $router->post('top-ups/bulk-remove', function () { seller_api_bulk_remove_topups(); });
    $router->post('top-ups/bulk_remove', function () { seller_api_bulk_remove_topups(); });
    $router->post('top-ups/bulk-update', function () { seller_api_bulk_update_topups(); });
    $router->post('top-ups/bulk_update', function () { seller_api_bulk_update_topups(); });
    $router->get('top-ups/:id', function ($id) { seller_api_get_topup($id); });
    $router->patch('top-ups/:id', function ($id) { seller_api_update_topup($id); });
    $router->delete('top-ups/:id', function ($id) { seller_api_delete_topup($id); });

    // Alias: topups (no hyphen)
    $router->get('topups', function () { seller_api_list_topups(); });
    $router->post('topups', function () { seller_api_create_topup(); });
    $router->post('topups/bulk-remove', function () { seller_api_bulk_remove_topups(); });
    $router->post('topups/bulk-update', function () { seller_api_bulk_update_topups(); });
    $router->get('topups/:id', function ($id) { seller_api_get_topup($id); });
    $router->patch('topups/:id', function ($id) { seller_api_update_topup($id); });
    $router->delete('topups/:id', function ($id) { seller_api_delete_topup($id); });
});

/**
 * ==== Views ====
 **/

// Landing

$router->get('/', function () {
    global $db;

    if (lb_landing_cache_serve(300)) {
        return;
    }

    // print_r([
    //     'GET' => $_GET,
    //     'LANG' => defined('LANG') ? LANG : null,
    //     'REQUEST_PATH' => defined('REQUEST_PATH') ? REQUEST_PATH : null,
    //     'REQUEST_URI' => $_SERVER['REQUEST_URI'],
    // ]);
    // die;

    $meta = [
        'title' => 'LoLBoost | Buy & Sell Game Accounts, Items & Boosting',
        'description' => 'LoLBoost is the all-in-one marketplace for gamers. Buy and sell game accounts, items, skins, boosting and coaching. Fast, safe and professional.',
        'keywords' => 'LoLBoost, buy game accounts, sell game accounts, game boosting, game items, gaming marketplace',
        'canonical' => BASE_URL . '/',
        'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
    ];

    /* Landing performance: keep the first page request light.
       The current landing view is static and no longer uses the booster/live-order
       arrays during initial render. Heavy booster/live-order data remains available
       through existing AJAX endpoints when a component explicitly asks for it. */
    $boosters = [];
    $liveOrders = [];
    $landingLiveOrdersTotal = 0;

    if (isset($_GET['tk'])) {
        $recovery_id = esc($_GET['tk']);
        $client = db_get_row('clients', ['recovery_id' => $recovery_id], 1);
        if (!empty($client)) {
            $meta['reset_password'] = $recovery_id;
        }
    }

    if (lb_landing_cache_can_use()) {
        ob_start();
        view_file('website/landing', [
          'meta'         => $meta,
          'boosters'     => $boosters,
          'liveOrders'   => $liveOrders,
          'liveOrdersTotal' => $landingLiveOrdersTotal,
          'reset_password' => $meta['reset_password'] ?? null,
        ]);
        $html = (string)ob_get_clean();
        lb_landing_cache_store($html);
        header('Cache-Control: public, max-age=60, stale-while-revalidate=300');
        echo $html;
        return;
    }

    view_file('website/landing', [
      'meta'         => $meta,
      'boosters'     => $boosters,
      'liveOrders'   => $liveOrders,
      'liveOrdersTotal' => $landingLiveOrdersTotal,
      'reset_password' => $meta['reset_password'] ?? null,
    ]);
});


$router->get('/reviews', function () {
    $meta = [
        'title' => 'Reviews | LoLBoost',
        'description' => 'Read verified customer reviews and marketplace feedback for LoLBoost services.',
        'keywords' => 'LoLBoost reviews, gaming marketplace reviews, boosting reviews',
        'canonical' => BASE_URL . '/reviews',
        'robots' => 'index, follow',
    ];
    view_file('website/pages/reviews', ['meta' => $meta]);
});

$router->get('/world-cup-predictions', function () {
    $meta = [
        'title' => 'World Cup 26 Predictions | LoLBoost',
        'description' => 'Explore World Cup 2026 predictions, match insights and community picks.',
        'keywords' => 'World Cup 2026 predictions, football predictions, LoLBoost',
        'canonical' => BASE_URL . '/world-cup-predictions',
        'robots' => 'index, follow',
    ];
    view_file('website/pages/world-cup-predictions', ['meta' => $meta]);
});

$router->get('/contact', function () {
    $meta = [
        'h1' => 'Contact Us',
        'title' => 'Contact Us | 24/7 Support | LoLBoost',
        'description' => t('Reach out to us 24/7 for help with boosting, accounts, items and all other services. Fast, friendly and professional support.'),
        'keywords' => 'contact LoLBoost, support, lolboost.gg',
        'eyebrow' => 'Support Center',
        'badges' => [
            ['icon' => 'fa-headset', 'label' => '24/7 Support'],
            ['icon' => 'fa-bolt', 'label' => 'Fast Response'],
            ['icon' => 'fa-lock', 'label' => 'Confidential'],
        ],
    ];
    view_file('website/pages/contact', ['meta' => $meta]);
});


$router->get('/work-with-us', function () {
    $meta = [
        'h1' => 'Join Our Team',
        'title' => 'Work with us | LoLBoost',
        'description' => t('Work with LoLBoost as a booster, coach, seller or partner and earn money doing what you love.'),
        'keywords' => 'work with us LoLBoost, become a booster, become a seller, gaming jobs',
        'eyebrow' => 'Careers at LoLBoost',
        'badges' => [
            ['icon' => 'fa-gamepad', 'label' => 'Play & Earn'],
            ['icon' => 'fa-users', 'label' => 'Great Community'],
            ['icon' => 'fa-globe', 'label' => 'Remote Work'],
        ],
        'canonical' => BASE_URL . '/work-with-us',
        'robots' => 'index, follow',
    ];
    view_file('website/pages/jobs', ['meta' => $meta]);
});

$router->get('/jobs', function () {
    $meta = [
        'h1' => 'Join Our Team',
        'title' => 'Jobs | Work with LoLBoost | LoLBoost',
        'description' => t('Join our team at LoLBoost. Become a booster, coach, seller or GG Girl and earn money doing what you love.'),
        'keywords' => 'jobs LoLBoost, become a booster, become a seller, gaming jobs',
        'eyebrow' => 'Careers at LoLBoost',
        'badges' => [
            ['icon' => 'fa-gamepad', 'label' => 'Play & Earn'],
            ['icon' => 'fa-users', 'label' => 'Great Community'],
            ['icon' => 'fa-globe', 'label' => 'Remote Work'],
        ],
    ];
    view_file('website/pages/jobs', ['meta' => $meta]);
});

if (!function_exists('lb_public_booster_slug')) {
    function lb_public_booster_slug($value): string
    {
        $value = trim((string) $value);
        if ($value === '') return '';
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) $value = $converted;
        }
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim($value, '-');
    }
}

if (!function_exists('lb_public_booster_id_from_identifier')) {
    function lb_public_booster_id_from_identifier($identifier): int
    {
        global $db;
        $identifier = trim(rawurldecode((string) $identifier));
        if ($identifier === '') return 0;
        if (ctype_digit($identifier)) return (int) $identifier;
        if (preg_match('/^(\d+)(?:-|$)/', $identifier, $m)) return (int) $m[1];

        $wantedSlug = lb_public_booster_slug($identifier);
        if ($wantedSlug === '') return 0;

        $rows = $db->run("SELECT id, username FROM boosters WHERE is_banned = 0 AND show_profile = 1") ?: [];
        foreach ($rows as $row) {
            if (lb_public_booster_slug($row['username'] ?? '') === $wantedSlug) return (int) $row['id'];
        }
        return 0;
    }
}

if (!function_exists('lb_public_booster_show_profile')) {
    function lb_public_booster_show_profile($identifier): void
    {
        global $db;
        $rawIdentifier = trim(rawurldecode((string) $identifier));
        $id = lb_public_booster_id_from_identifier($rawIdentifier);
        if ($id <= 0) { redirect_url('boosters'); return; }
        if (!ctype_digit($rawIdentifier)) { redirect_url('boosters/' . $id); return; }

        $ordersPage = isset($_GET['opage']) ? max(1, (int) $_GET['opage']) : (isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1);
        $ordersPerPage = 10;
        $ordersOffset = ($ordersPage - 1) * $ordersPerPage;
        $ordersTotal = 0;
        try {
            $ordersRows = $db->run("SELECT COUNT(*) AS total FROM orders WHERE booster_id = ? AND status IN ('COMPLETED', 'IN_PROGRESS')", $id) ?: [];
            $ordersTotal = (int)($ordersRows[0]['total'] ?? 0);
        } catch (Throwable $e) {
            $ordersTotal = (int) db_get_row_count('orders', ['booster_id' => $id]);
        }
        $ordersTotalPages = max(1, (int)ceil($ordersTotal / max(1, $ordersPerPage)));
        $ordersPage = max(1, min($ordersTotalPages, $ordersPage));
        $ordersOffset = ($ordersPage - 1) * $ordersPerPage;

        $query = "SELECT boosters.*, boosters.id as booster_id, booster_ranks.name as rank_name, booster_profiles.*
        FROM boosters
        LEFT JOIN booster_profiles ON boosters.id = booster_profiles.booster_id
        LEFT JOIN booster_ranks ON boosters.rank_id = booster_ranks.id
        WHERE boosters.id = $id AND boosters.is_banned = 0 LIMIT 1";
        $booster = $db->row($query);
        if (!$booster) { redirect_url('boosters'); return; }

        $sql = "SELECT * FROM orders WHERE booster_id = $id AND status IN ('COMPLETED', 'IN_PROGRESS') ORDER BY created_at DESC LIMIT $ordersOffset, $ordersPerPage";
        $booster['orders'] = $db->run($sql) ?: [];

        $forms = db_get_rows('boost_forms', ['select' => 'id,type,name,icon,game']);
        $forms = array_column($forms, null, 'id');
        if (!empty($booster['orders'])) {
            foreach ($booster['orders'] as $key => $order) {
                $order_opts = db_get_row('order_options', ['order_id' => $order['id'], 'select' => 'server,hours,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo,current_rank'], 1) ?: [];
                $booster['orders'][$key] = array_merge($order_opts, $forms[$order['form_id']] ?? [], $order);
            }
        } else {
            $booster['orders'] = [];
        }

        $pagination = [
            'page' => $ordersPage,
            'totalPages' => $ordersTotalPages,
            'totalItems' => $ordersTotal,
            'itemsPerPage' => $ordersPerPage,
        ];

        $reviews_page = isset($_GET['rpage']) ? max(1, (int) $_GET['rpage']) : 1;
        $reviews_per_page = 6;
        $review_count = 0;
        try {
            $reviewRows = $db->run("SELECT COUNT(*) AS total FROM reviews WHERE booster_id = ? AND approved = 1", (int)$booster['booster_id']) ?: [];
            $review_count = (int)($reviewRows[0]['total'] ?? 0);
        } catch (Throwable $e) {
            $review_count = (int) db_get_row_count('reviews', ['booster_id' => $booster['booster_id'], 'approved' => 1]);
        }
        $reviews_total_pages = max(1, (int)ceil($review_count / max(1, $reviews_per_page)));
        $reviews_page = max(1, min($reviews_total_pages, $reviews_page));
        $reviews_offset = ($reviews_page - 1) * $reviews_per_page;

        $reviews = db_get_rows('reviews', ['booster_id' => $booster['booster_id'], 'approved' => 1, 'order' => 'created_at,DESC', 'limit' => $reviews_per_page, 'offset' => $reviews_offset]);
        if (!empty($reviews)) {
            foreach ($reviews as $key => $review) {
                $order = db_get_row('orders', ['id' => $review['order_id']]);
                if (empty($order)) { unset($reviews[$key]); continue; }
                $order_opts = db_get_row('order_options', ['order_id' => $order['id']]) ?: [];
                $order_acc = db_get_row('order_accounts', ['order_id' => $order['id']], 1) ?: [];
                $form = db_get_row('boost_forms', ['id' => $order['form_id']]) ?: [];
                $notes = db_get_rows('order_notes', ['order_id' => $order['id'], 'type' => 'client']) ?: [];
                $order = array_merge($form, $order_opts, $order_acc, $order, ['notes' => $notes]);
                $client = db_get_row('clients', ['id' => $order['client_id']], 1) ?: [];
                $order = array_merge($order, ['client' => $client['username'] ?? 'Guest']);
                $reviews[$key] = array_merge($review, ['order' => $order]);
            }
            $reviews = array_values($reviews);
        }

        $reviewPagination = [
            'page' => $reviews_page,
            'totalPages' => $reviews_total_pages,
            'totalItems' => $review_count,
            'itemsPerPage' => $reviews_per_page,
        ];
        $meta = ['h1' => 'Meet Our Boosters', 'title' => 'Our Boosters | Verified Pro Players | LoLBoost', 'description' => t('Our elite boosters are top-ranked, verified, and ready to help you climb faster. Safe, secure, and trusted by thousands of gamers worldwide.'), 'keywords' => 'LoLBoost boosters, verified boosters, pro players, game boosting', 'eyebrow' => 'Elite Booster Marketplace', 'badges' => [['icon' => 'fa-circle-check', 'label' => 'Verified Boosters'], ['icon' => 'fa-shield-halved', 'label' => 'Secure & Safe'], ['icon' => 'fa-globe', 'label' => 'All Servers']]];
        view_file('website/pages/boosters/view', ['booster' => $booster, 'meta' => $meta, 'pagination' => $pagination, 'reviews' => $reviews, 'reviewPagination' => $reviewPagination]);
    }
}

$router->get('/booster/:slug', function ($slug) {
    $id = lb_public_booster_id_from_identifier($slug);
    redirect_url($id > 0 ? 'boosters/' . $id : 'boosters');
});

$router->get('/boosters/:slug', function ($slug) {
    lb_public_booster_show_profile($slug);
});
$router->group('/boosters', function () {
    global $router;

    $router->get('/', function () {
        global $db;

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $itemsPerPage = 9;
        $offset = ($page - 1) * $itemsPerPage;
        $boosterCountRow = $db->row("
            SELECT COUNT(DISTINCT boosters.id) AS total
            FROM boosters
            INNER JOIN booster_profiles ON boosters.id = booster_profiles.booster_id
            WHERE boosters.is_banned = 0
              AND (boosters.is_egirl IS NULL OR boosters.is_egirl = 0)
              AND boosters.show_profile = 1
              AND booster_profiles.champions IS NOT NULL
              AND booster_profiles.roles IS NOT NULL
        ");
        $totalBoosters = (int)($boosterCountRow['total'] ?? 0);
        $totalPages = max(1, (int)ceil($totalBoosters / $itemsPerPage));

        $query = "
        SELECT 
            boosters.*, 
            booster_ranks.name as rank_name, 
            booster_profiles.*, 
            boosters.id as booster_id,
            COUNT(orders.id) as completed_orders,
            COALESCE(rev.review_count, 0) AS review_count
        FROM boosters
        INNER JOIN booster_profiles 
            ON boosters.id = booster_profiles.booster_id
        LEFT JOIN booster_ranks 
            ON boosters.rank_id = booster_ranks.id
        LEFT JOIN orders 
            ON boosters.id = orders.booster_id AND orders.status = 'COMPLETED'
        LEFT JOIN (
            SELECT booster_id, COUNT(*) AS review_count
            FROM reviews WHERE approved = 1
            GROUP BY booster_id
        ) rev ON rev.booster_id = boosters.id
        WHERE 
            boosters.is_banned = 0 
            AND (boosters.is_egirl IS NULL OR boosters.is_egirl = 0)
            AND booster_profiles.champions IS NOT NULL 
            AND booster_profiles.roles IS NOT NULL
            AND boosters.show_profile = 1
        GROUP BY boosters.id
        ORDER BY completed_orders DESC, boosters.id ASC
        LIMIT $itemsPerPage OFFSET $offset";

        $boosters = $db->run($query);

        // Build the public game filter from every visible booster, not only from
        // the first paginated result set. This keeps newly assigned games in the
        // dropdown without requiring another hard-coded entry in the view.
        $boosterGameRows = $db->run("
            SELECT DISTINCT boosters.games
            FROM boosters
            INNER JOIN booster_profiles ON boosters.id = booster_profiles.booster_id
            WHERE boosters.is_banned = 0
              AND (boosters.is_egirl IS NULL OR boosters.is_egirl = 0)
              AND boosters.show_profile = 1
              AND booster_profiles.champions IS NOT NULL
              AND booster_profiles.roles IS NOT NULL
              AND COALESCE(boosters.games, '') != ''
        ");
        $boosterGames = [];
        foreach ((array)$boosterGameRows as $boosterGameRow) {
            $rawGames = trim((string)($boosterGameRow['games'] ?? ''));
            $decodedGames = json_decode($rawGames, true);
            $gameSlugs = is_array($decodedGames)
                ? $decodedGames
                : preg_split('/[,|]+/', strtolower($rawGames), -1, PREG_SPLIT_NO_EMPTY);

            foreach ((array)$gameSlugs as $gameSlug) {
                $gameSlug = strtolower(trim((string)$gameSlug));
                if ($gameSlug === '' || isset($boosterGames[$gameSlug])) continue;

                $fullLabels = [
                    'lol' => 'League of Legends',
                    'lol_classic' => 'League of Legends Classic',
                    'val' => 'Valorant',
                    'tft' => 'Teamfight Tactics',
                ];
                $boosterGames[$gameSlug] = [
                    'slug'  => $gameSlug,
                    'label' => $fullLabels[$gameSlug]
                        ?? (function_exists('util_game_display_name') ? util_game_display_name($gameSlug) : ucwords(str_replace(['-', '_'], ' ', $gameSlug))),
                    'icon'  => function_exists('util_game_icon_url') ? util_game_icon_url($gameSlug) : '',
                ];
            }
        }
        uasort($boosterGames, static fn($a, $b) => strcasecmp($a['label'], $b['label']));

        $pagination = [
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalBoosters,
        ];

        $meta = [
            'h1' => 'Meet Our Boosters',
            'title' => 'Our Boosters | League of Legends | LoLBoost',
            'description' => t('Our elite boosters are top-ranked, verified, and ready to help you climb faster. Safe, secure, and trusted by thousands of gamers worldwide.'),
            'keywords' => 'LoLBoost boosters, verified boosters, pro players, game boosting',
            'eyebrow' => 'Elite Booster Marketplace',
            'badges' => [
                ['icon' => 'fa-circle-check', 'label' => 'Verified Boosters'],
                ['icon' => 'fa-shield-halved', 'label' => 'Secure & Safe'],
                ['icon' => 'fa-globe', 'label' => 'All Servers'],
            ],
        ];
        view_file('website/pages/boosters/list', [
            'boosters' => $boosters,
            'boosterGames' => array_values($boosterGames),
            'meta' => $meta,
            'pagination' => $pagination,
        ]);
    });

    $router->get('/:id', function ($id) {
        lb_public_booster_show_profile($id);
    });

});

$router->group('/sellers', function () {
    global $router;

    $router->get('/', function () {
        global $db;

        $sellerSalesSql = function_exists('get_seller_total_sales_subquery')
            ? get_seller_total_sales_subquery('s')
            : "0";

        $sellers = $db->run(
            "SELECT s.id, s.username, s.slug, s.icon, s.banner, s.banner_position, s.is_active, s.rank, s.discord,
                    {$sellerSalesSql} AS total_sold,
                    (SELECT COUNT(*) FROM selling_accounts WHERE seller_id = s.id AND sold = 0 AND COALESCE(active, 1) = 1) AS active_listings,
                    (SELECT ROUND(AVG(rating), 1) FROM seller_reviews WHERE seller_id = s.id AND approved = 1) AS avg_rating,
                    (SELECT COUNT(*) FROM seller_reviews WHERE seller_id = s.id AND approved = 1) AS review_count
             FROM sellers s
             WHERE s.is_active = 1 AND COALESCE(s.is_banned, 0) = 0
             ORDER BY total_sold DESC, s.username ASC"
        ) ?: [];

        $meta = [
            'title' => 'Our Sellers | Verified Marketplace Sellers | LoLBoost',
            'h1' => 'Our Sellers',
            'description' => 'Browse verified sellers on LoLBoost. Trusted accounts, items and boosting from top-rated sellers.',
            'keywords' => 'LoLBoost sellers, verified sellers, buy game accounts',
        ];

        view_file('website/pages/sellers/list', [
            'meta' => $meta,
            'sellers' => $sellers,
        ]);
    });

    $router->get('/:slug', function ($slug) {
        global $db, $is_client;

        // Decode URL-encoded characters (e.g. %20 -> space) and trim
        $slug = trim(rawurldecode((string)$slug));

        // Also grab full slug from REQUEST_URI in case router truncated at space
        $uri_path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (preg_match('#^(?:/[a-z]{2})?/sellers/(.+)$#i', $uri_path, $m)) {
            $slug = trim($m[1]);
        }

        if ($slug === '') {
            redirect_url('sellers');
            return;
        }

        $seller = null;

        // 1) Match by numeric ID
        if (ctype_digit($slug)) {
            $seller = $db->row(
                "SELECT id, username, slug, icon, banner, banner_position, is_active, rank, discord
                 FROM sellers
                 WHERE id = ? AND is_active = 1 AND COALESCE(is_banned, 0) = 0
                 LIMIT 1",
                (int)$slug
            );
        }

        // 2) Match by slug field (URL-safe, no spaces)
        if (!$seller) {
            $seller = $db->row(
                "SELECT id, username, slug, icon, banner, banner_position, is_active, rank, discord
                 FROM sellers
                 WHERE LOWER(TRIM(slug)) = LOWER(TRIM(?))
                   AND is_active = 1
                   AND COALESCE(is_banned, 0) = 0
                 LIMIT 1",
                $slug
            );
        }

        // 3) Fallback: match by username (for sellers without slug set)
        if (!$seller) {
            $seller = $db->row(
                "SELECT id, username, slug, icon, banner, banner_position, is_active, rank, discord
                 FROM sellers
                 WHERE LOWER(TRIM(username)) = LOWER(TRIM(?))
                   AND is_active = 1
                   AND COALESCE(is_banned, 0) = 0
                 LIMIT 1",
                $slug
            );
        }

        // 4) Fallback: generated slug from username (spaces -> hyphens), useful for old sellers without slug filled.
        if (!$seller) {
            $seller = $db->row(
                "SELECT id, username, slug, icon, banner, banner_position, is_active, rank, discord
                 FROM sellers
                 WHERE LOWER(TRIM(REPLACE(username, ' ', '-'))) = LOWER(TRIM(?))
                   AND is_active = 1
                   AND COALESCE(is_banned, 0) = 0
                 LIMIT 1",
                $slug
            );
        }

        if (!$seller) {
            redirect_url('sellers');
            return;
        }

        // Always use canonical seller slug in the browser URL.
        // This fixes links like /sellers/QsA%20Feint by redirecting them to /sellers/qsa-feint.
        $canonicalSellerSlug = trim((string)($seller['slug'] ?? ''));
        if ($canonicalSellerSlug === '' && function_exists('seller_profile_slug')) {
            $canonicalSellerSlug = seller_profile_slug($seller['username'] ?? '');
        }
        if ($canonicalSellerSlug !== '' && strtolower($slug) !== strtolower($canonicalSellerSlug)) {
            redirect_url('sellers/' . rawurlencode($canonicalSellerSlug));
            return;
        }

        $seller_id = (int)$seller['id'];

        $seller_is_online = false;
        try {
            $sellerPresenceTable = $db->cell("SHOW TABLES LIKE 'seller_session_logs'");
            if (!empty($sellerPresenceTable)) {
                $sellerOnlineRow = $db->row(
                    "SELECT 1 AS online
                     FROM seller_session_logs
                     WHERE seller_id = ?
                       AND created_at >= (NOW() - INTERVAL 5 MINUTE)
                     ORDER BY id DESC
                     LIMIT 1",
                    $seller_id
                );
                $seller_is_online = !empty($sellerOnlineRow);
            }
        } catch (\Throwable $e) {
            $seller_is_online = false;
        }
        $seller['is_online'] = $seller_is_online ? 1 : 0;

        $seller_total_sales = function_exists('get_seller_total_sales')
            ? get_seller_total_sales($seller_id)
            : 0;
        $total_sold = function_exists('get_seller_service_sales')
            ? get_seller_total_sales($seller_id)
            : (int)$db->single(
                "SELECT COUNT(*) FROM selling_accounts WHERE seller_id = ? AND sold = 1 AND client_id IS NOT NULL",
                $seller_id
            );
        $seller['seller_total_sales'] = $seller_total_sales;
        $seller['seller_account_sales'] = $seller_total_sales;

        $accounts = $db->run(
            "SELECT id, title, slug, server, game, current_rank, current_division, current_lp,
                    images, price, delivery_type, level, blue_essence, riot_points, champions, skins,
                    game_data, rank_label, rank
             FROM selling_accounts
             WHERE seller_id = ? AND sold = 0 AND COALESCE(active, 1) = 1
             ORDER BY created_at DESC",
            $seller_id
        ) ?: [];

        $items = $db->run(
            "SELECT si.id, si.title, si.slug, si.type, si.server, si.images, si.price,
                    si.stock, si.sold_count, si.game,
                    COALESCE(g.slug, si.game) AS game_slug,
                    g.name AS game_name,
                    g.icon AS game_icon
             FROM selling_items si
             LEFT JOIN games g ON g.id = si.game_id
             WHERE si.seller_id = ? AND COALESCE(si.active, 1) = 1
             ORDER BY si.id DESC",
            $seller_id
        ) ?: [];

        $topups = [];
        $digital_goods = [];
        try {
            if (function_exists('lb_topups_table_ensure')) lb_topups_table_ensure();
            $topups = $db->run(
                "SELECT st.id, st.offer_title AS title, st.game_slug, st.game_name, st.region,
                        st.platform, st.image, st.price, st.stock, st.sold_count
                 FROM selling_topups st
                 WHERE st.seller_id = ? AND COALESCE(st.active,1) = 1 AND COALESCE(st.stock,0) > 0
                 ORDER BY st.created_at DESC",
                $seller_id
            ) ?: [];
        } catch (\Throwable $e) { $topups = []; }
        try {
            $digital_goods = $db->run(
                "SELECT dg.id, dg.title, dg.slug, dg.brand, dg.brand_icon, dg.region, dg.images, dg.price,
                        dg.stock, dg.sold_count, dgc.name AS category_name
                 FROM digital_goods dg
                 LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id
                 WHERE dg.seller_id = ? AND COALESCE(dg.active,1) = 1 AND COALESCE(dg.stock,0) > 0
                 ORDER BY dg.created_at DESC",
                $seller_id
            ) ?: [];
        } catch (\Throwable $e) { $digital_goods = []; }

        $avg_row = $db->row(
            "SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS review_count
             FROM seller_reviews
             WHERE seller_id = ? AND approved = 1",
            $seller_id
        );
        $avg_rating = (float)($avg_row['avg_rating'] ?? 0);
        $review_count = (int)($avg_row['review_count'] ?? 0);

        $rating_dist_rows = $db->run(
            "SELECT rating, COUNT(*) AS cnt
             FROM seller_reviews
             WHERE seller_id = ? AND approved = 1
             GROUP BY rating",
            $seller_id
        ) ?: [];

        $rating_dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($rating_dist_rows as $row) {
            $r = (int)($row['rating'] ?? 0);
            if (isset($rating_dist[$r])) {
                $rating_dist[$r] = (int)($row['cnt'] ?? 0);
            }
        }

        $reviews_page = isset($_GET['rpage']) ? max(1, (int)$_GET['rpage']) : 1;
        $reviews_per_page = 10;
        $reviews_offset = ($reviews_page - 1) * $reviews_per_page;

        $all_reviews = $db->run(
            "SELECT sr.*, c.username AS client_username, c.icon AS client_icon
             FROM seller_reviews sr
             LEFT JOIN clients c ON c.id = sr.client_id
             WHERE sr.seller_id = ? AND sr.approved = 1
             ORDER BY sr.created_at DESC",
            $seller_id
        ) ?: [];

        // Sales without buyer feedback show up as 5-star "No Feedback left." cards
        // after 24h and count towards rating, review count and the rating breakdown —
        // the seller list card uses the exact same source, so the numbers stay in sync.
        $no_feedback_entries = function_exists('seller_no_feedback_entries')
            ? seller_no_feedback_entries($seller_id, 24)
            : [];
        if ($no_feedback_entries) {
            $auto_count = count($no_feedback_entries);
            $combined_count = $review_count + $auto_count;
            $avg_rating = $combined_count > 0
                ? round((($avg_rating * $review_count) + ($auto_count * 5)) / $combined_count, 1)
                : 0.0;
            $review_count = $combined_count;
            $rating_dist[5] += $auto_count;

            $all_reviews = array_merge($all_reviews, $no_feedback_entries);
            usort($all_reviews, static fn($a, $b) => strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? '')));
        }

        $reviews_total = count($all_reviews);
        $reviews_total_pages = max(1, (int)ceil($reviews_total / $reviews_per_page));
        $reviews = array_slice($all_reviews, $reviews_offset, $reviews_per_page);

        $can_review = false;
        $already_reviewed = false;
        $purchase_id = null;

        if (!empty($is_client) && defined('CLIENT_ID')) {
            $purchase = $db->row(
                "SELECT id
                 FROM selling_accounts
                 WHERE seller_id = ? AND client_id = ? AND sold = 1
                 ORDER BY id DESC
                 LIMIT 1",
                $seller_id,
                (int)CLIENT_ID
            );

            if ($purchase) {
                $purchase_id = (int)$purchase['id'];
                $can_review = true;

                $existing_review = $db->row(
                    "SELECT id
                     FROM seller_reviews
                     WHERE seller_id = ? AND client_id = ? AND purchase_id = ?
                     LIMIT 1",
                    $seller_id,
                    (int)CLIENT_ID,
                    $purchase_id
                );

                $already_reviewed = !empty($existing_review);
            }
        }

        $meta = [
            'title' => $seller['username'] . ' | Seller Profile | LoLBoost',
            'h1' => $seller['username'],
            'description' => 'View seller profile on LoLBoost. Check ratings, reviews and available listings.',
            'keywords' => 'seller profile, LoLBoost',
        ];

        view_file('website/pages/sellers/view', [
            'meta' => $meta,
            'seller' => $seller,
            'seller_is_online' => $seller_is_online,
            'total_sold' => $total_sold,
            'accounts' => $accounts,
            'items' => $items,
            'topups' => $topups,
            'digital_goods' => $digital_goods,
            'reviews' => $reviews,
            'avg_rating' => $avg_rating,
            'rating_dist' => $rating_dist,
            'review_count' => $review_count,
            'reviews_total' => $reviews_total,
            'reviews_per_page' => $reviews_per_page,
            'reviews_page' => $reviews_page,
            'reviews_total_pages' => $reviews_total_pages,
            'can_review' => $can_review,
            'already_reviewed' => $already_reviewed,
            'purchase_id' => $purchase_id,
        ]);
    });
});

$router->get('/loyalty', function () {

    $meta = [
        'h1' => 'Loyalty Program',
        'title' => 'Loyalty Program | Earn Rewards | LoLBoost',
        'description' => t('Join the LoLBoost Loyalty Program. Earn points with every purchase and redeem them for exclusive rewards and discounts.'),
        'keywords' => 'LoLBoost loyalty program, earn points, gaming rewards',
        'eyebrow' => 'Loyalty Program',
        'badges' => [
            ['icon' => 'fa-bolt', 'label' => 'Earn Points'],
            ['icon' => 'fa-trophy', 'label' => '7 Ranks'],
            ['icon' => 'fa-infinity', 'label' => 'Never Expires'],
        ],
    ];

    view_file('website/pages/loyalty', ['meta' => $meta]);
});

$router->get('/points-store', function () {
    global $db;

    $prizes = db_get_rows('prizes');

    $meta = [
        'h1' => 'Points Store',
        'title' => 'Points Store | Redeem Your Rewards | LoLBoost',
        'description' => t('Redeem your LoLBoost loyalty points for exclusive prizes and discounts.'),
        'keywords' => 'LoLBoost points store, redeem points, gaming rewards',
        'eyebrow' => 'Rewards Store',
        'badges' => [
            ['icon' => 'fa-coins', 'label' => 'Spend Points'],
            ['icon' => 'fa-gift', 'label' => 'Exclusive Prizes'],
            ['icon' => 'fa-bolt', 'label' => 'Instant Redeem'],
        ],
    ];

    view_file('website/pages/points-store', ['prizes' => $prizes, 'meta' => $meta]);
});

$router->group('/blog', function () {
    global $router;
    $router->get('/', function () {
        lb_blog_render_list((string)($_GET['game'] ?? ''));
    });

    // Category pages live under /blog/categories/<game-slug>. Both routes are
    // also reachable through /:slug below, because some router versions match the
    // single-segment slug pattern first.
    $router->get('/categories', function () { lb_blog_render_categories(); });
    $router->get('/categories/:slug', function ($slug) { lb_blog_render_list((string)$slug); });

    $router->get('/:slug', function ($slug) {
        if (strtolower(trim((string)$slug)) === 'categories') { lb_blog_render_categories(); return; }
        lb_blog_render_article((string)$slug);
    });
});

$router->group('/league-of-legends', function () {
    global $router;

        // $router->get('/smurf-accounts', function () {

    //     $data = db_get_rows('account_packages', ['status' => 1, 'icon' => 'be', 'order' => 'price,ASC']);

    //     // group by server
    //     $servers = [];
    //     foreach ($data as $row) {
    //         // get available accounts for this package
    //         $row['available'] = count(db_get_rows('accounts', ['package_id' => $row['id'], 'status' => 0, 'select' => 'id'], 1));
    //         $servers[$row['server']][] = $row;
    //     }

    //     $meta = [
    //         'title' => 'LoL Smurf Accounts Fast & Safe Elo Boost LoL Quality | LoL Boost',
    //         'h1' => 'LoL Smurf Accounts',
    //         'description' => 'Buy high-quality LoL smurf accounts.',
    //         'keywords' => 'Lol Smurf Account',
    //     ];
    //     view_file('main/accounts/lol', ['meta' => $meta, 'data' => $servers, 'faq' => 'smurf-accounts', 'active' => 'smurf-accounts']);
    // });
    $router->get('/premium-accounts', function () {

        if (lb_public_page_cache_serve('premium-accounts-league-of-legends', 90)) return;
        $__pageCache = lb_public_page_cache_start();

        // LoL Premium Accounts (Smurf Accounts) - filter by game_id = 1
        $data = db_get_rows('account_packages', ['status' => 1, 'game_id' => 1, 'order' => 'price,ASC']);

        // group by server
        $servers = [];
        foreach ($data as $row) {
            // get available accounts for this package
            $row['available'] = count(db_get_rows('accounts', ['package_id' => $row['id'], 'status' => 0, 'game_id' => 1, 'select' => 'id'], 1));
            $servers[$row['server']][] = $row;
        }

        $meta = [
            'title' => 'Buy Premium LoL Accounts | Ranked & Ready | LoLBoost',
            'h1' => 'League of Legends Smurf Accounts – Handleveled, Ranked & sofort spielbereit',
            'description' => t('Buy premium LoL smurf accounts that are handleveled and ready for Ranked. Choose from 20–30 champions, fast delivery, and a minimum 14-day warranty. Start playing instantly at the rank you want.'),
            'keywords' => 'LoL Smurf Accounts, Buy LoL Smurf Account, League of Legends Smurf Account, Handleveled LoL Account, Ranked Ready LoL Account, LoL Accounts for Sale',
            'canonical' => BASE_URL . '/league-of-legends/premium-accounts',
            'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        ];
        view_file('website/pages/accounts/lol', ['meta' => $meta, 'data' => $servers, 'faq' => 'premium-accounts', 'active' => 'premium-accounts']);
        lb_public_page_cache_finish('premium-accounts-league-of-legends', $__pageCache);
    });

    $router->get('/accounts', function () {
        global $db;

        if (lb_public_page_cache_serve('accounts-league-of-legends', 90)) return;
        $__pageCache = lb_public_page_cache_start();

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        // LoL account shop: 25 listings per page.
        $itemsPerPage = 25;

        $totalItems = (int)$db->single("SELECT COUNT(*) FROM selling_accounts WHERE sold = 0 AND active = 1 AND (game IN ('league-of-legends','lol','leagu') OR game IS NULL)");
        $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        // recommended default:
        // - reward sellers with the most completed sales
        // - still keep all listings visible across pagination
        // - distribute sellers so one seller does not dominate a page
        // page rule:
        //   top seller by sales can appear up to 3x per page
        //   second seller by sales can appear up to 2x per page
        //   everyone else gets one slot in the first pass
        //   any remaining slots are then backfilled by sales order
        // Seller-level stats are computed once per seller via GROUP BY derived
        // tables and joined in, instead of running 3 correlated subqueries for
        // every single account row (previously re-executed per row, so a
        // seller with 50 listings paid the cost 50 times over).
        $query = "SELECT
                    sa.id, sa.title, sa.slug, sa.server, sa.current_rank, sa.current_division,
                    sa.current_lp, sa.images, sa.description, sa.champions, sa.skins,
                    sa.level, sa.blue_essence, sa.riot_points, sa.price, sa.delivery_type,
                    sa.created_at,
                    sa.seller_id,
                    s.username  AS seller_username,
                    s.slug      AS seller_slug,
                    s.icon      AS seller_icon,
                    s.rank      AS seller_rank,
                    s.rank_icon AS seller_rank_icon,
                    s.is_active AS seller_is_active,
                    (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales,
                    (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_sold,
                    COALESCE(active_listings.listing_count, 0) AS seller_active_listings
                  FROM selling_accounts sa
                  LEFT JOIN sellers s ON s.id = sa.seller_id
                  LEFT JOIN (
                        SELECT seller_id, COUNT(*) AS sold_count
                        FROM selling_accounts
                        WHERE sold = 1 AND client_id IS NOT NULL
                        GROUP BY seller_id
                  ) acct_sales ON acct_sales.seller_id = s.id
                  LEFT JOIN (
                        SELECT seller_id, SUM(sold_count) AS sold_count
                        FROM selling_items
                        GROUP BY seller_id
                  ) item_sales ON item_sales.seller_id = s.id
                  LEFT JOIN (
                        SELECT COUNT(*) AS special_count
                        FROM accounts
                        WHERE admin_id = 51 AND status = 1 AND client_id IS NOT NULL
                  ) seller28 ON s.id = 28
                  LEFT JOIN (
                        SELECT seller_id, COUNT(*) AS listing_count
                        FROM selling_accounts
                        WHERE sold = 0 AND COALESCE(active, 1) = 1
                        GROUP BY seller_id
                  ) active_listings ON active_listings.seller_id = s.id
                  WHERE sa.sold = 0 AND sa.active = 1 AND (sa.game IN ('league-of-legends','lol','leagu') OR sa.game IS NULL)
                  ORDER BY
                    seller_total_sales DESC,
                    seller_active_listings ASC,
                    sa.created_at DESC,
                    sa.id DESC";

        $allRows = $db->run($query) ?: [];

        $sellerBuckets = [];
        $sellerOrder = [];
        foreach ($allRows as $row) {
            $sellerId = (int)($row['seller_id'] ?? 0);
            if (!isset($sellerBuckets[$sellerId])) {
                $sellerBuckets[$sellerId] = [];
                $sellerOrder[] = $sellerId;
            }
            $sellerBuckets[$sellerId][] = $row;
        }

        $topSeller1 = $sellerOrder[0] ?? null;
        $topSeller2 = $sellerOrder[1] ?? null;

        $takeFromSeller = function (&$buckets, $sid) {
            if ($sid === null) {
                return null;
            }
            if (!isset($buckets[$sid]) || empty($buckets[$sid])) {
                return null;
            }
            return array_shift($buckets[$sid]);
        };

        $takeFromOtherSellers = function (&$buckets, $sellerOrder, &$usedThisPage, $exclude = []) {
            foreach ($sellerOrder as $sid) {
                if (in_array($sid, $exclude, true)) {
                    continue;
                }
                if (!empty($usedThisPage[$sid])) {
                    continue;
                }
                if (!empty($buckets[$sid])) {
                    $usedThisPage[$sid] = 1;
                    return array_shift($buckets[$sid]);
                }
            }
            return null;
        };

        $takeFromAnySeller = function (&$buckets, $sellerOrder) {
            foreach ($sellerOrder as $sid) {
                if (!empty($buckets[$sid])) {
                    return array_shift($buckets[$sid]);
                }
            }
            return null;
        };

        $balanced = [];
        while (true) {
            $hasRemaining = false;
            foreach ($sellerOrder as $sid) {
                if (!empty($sellerBuckets[$sid])) {
                    $hasRemaining = true;
                    break;
                }
            }
            if (!$hasRemaining) {
                break;
            }

            $pageRows = [];
            $usedOtherThisPage = [];

            // Fixed per-page slot plan for recommended:
            // top seller 1 -> 3 guaranteed attempts
            // top seller 2 -> 2 guaranteed attempts
            // other sellers fill the remaining slots, but only one slot per non-top seller
            // in this first pass. Remaining slots are backfilled afterwards.
            $slotPlan = [
                ['type' => 'top1'],
                ['type' => 'top2'],
                ['type' => 'other'],
                ['type' => 'other'],
                ['type' => 'top1'],
                ['type' => 'other'],
                ['type' => 'other'],
                ['type' => 'top2'],
                ['type' => 'other'],
                ['type' => 'other'],
                ['type' => 'top1'],
                ['type' => 'other'],
            ];

            foreach ($slotPlan as $slot) {
                $picked = null;

                if ($slot['type'] === 'top1') {
                    $picked = $takeFromSeller($sellerBuckets, $topSeller1);
                } elseif ($slot['type'] === 'top2') {
                    $picked = $takeFromSeller($sellerBuckets, $topSeller2);
                } else {
                    $picked = $takeFromOtherSellers($sellerBuckets, $sellerOrder, $usedOtherThisPage, [$topSeller1, $topSeller2]);
                }

                if ($picked !== null) {
                    $pageRows[] = $picked;
                }

                if (count($pageRows) >= $itemsPerPage) {
                    break;
                }
            }

            while (count($pageRows) < $itemsPerPage) {
                $picked = $takeFromAnySeller($sellerBuckets, $sellerOrder);
                if ($picked === null) {
                    break;
                }
                $pageRows[] = $picked;
            }

            if (empty($pageRows)) {
                break;
            }

            $balanced = array_merge($balanced, $pageRows);
        }

        $offset = ($page - 1) * $itemsPerPage;
        $data = array_slice($balanced, $offset, $itemsPerPage);

        // Convert price to display currency (price stored as EUR cents)
        $session_currency = $_SESSION['currency'] ?? 'EUR';
        if ($session_currency === 'USD') {
            $rate = get_exchange_rate();
            foreach ($data as &$row) {
                $row['price'] = (int)round($row['price'] * $rate);
            }
            unset($row);
        }

        $pagination = [
            'page' => $page,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
            'totalItems' => $totalItems
        ];

        $meta = [
            'title' => 'Buy Ranked LoL Accounts | Verified Sellers | LoLBoost',
            'h1' => 'Ranked League of Legends Accounts',
            'description' => t('Buy Ranked LoL Accounts and start climbing instantly. Choose from a wide range of League of Legends ranked accounts, verified and ready for competitive play.'),
            'keywords' => 'buy ranked lol accounts, ranked lol accounts for sale, lol ranked accounts, lol accounts, lol smurf accounts',
            'canonical' => BASE_URL . '/league-of-legends/accounts',
            'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        ];

        $_lolAcctConfig = util_get_accounts_page_config('league-of-legends');
        view_file('website/pages/accounts/shop_lol', [
            'meta'       => $meta,
            'data'       => $data,
            'pagination' => $pagination,
            'game'       => 'lol',
            'gameConfig' => $_lolAcctConfig,
            'faq'        => 'accounts',
        ]);
        lb_public_page_cache_finish('accounts-league-of-legends', $__pageCache);
    });

    $router->get('/top-ups', function () {
        global $db;

        if (lb_public_page_cache_serve('topups-league-of-legends', 90)) return;
        $__pageCache = lb_public_page_cache_start();

        if (function_exists('lb_topups_table_ensure')) {
            lb_topups_table_ensure();
        }

        $gameSlug = 'league-of-legends';
        $game = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
        if (empty($game) && function_exists('util_get_game_by_slug')) {
            $game = util_get_game_by_slug('lol') ?: [];
        }
        $gameId = (int)($game['id'] ?? 0);
        $gameName = (string)($game['name'] ?? 'League of Legends');
        $cfg = function_exists('lb_get_topups_page_config') ? lb_get_topups_page_config($gameSlug) : [];
        $schema = function_exists('lb_get_game_topup_schema') ? lb_get_game_topup_schema($gameSlug) : [];

        $rows = [];
        try {
            $rows = $db->run("SELECT st.*, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
                 FROM selling_topups st
                 LEFT JOIN sellers s ON s.id = st.seller_id
                 LEFT JOIN (
                        SELECT seller_id, COUNT(*) AS sold_count
                        FROM selling_accounts
                        WHERE sold = 1 AND client_id IS NOT NULL
                        GROUP BY seller_id
                 ) acct_sales ON acct_sales.seller_id = s.id
                 LEFT JOIN (
                        SELECT seller_id, SUM(sold_count) AS sold_count
                        FROM selling_items
                        GROUP BY seller_id
                 ) item_sales ON item_sales.seller_id = s.id
                 LEFT JOIN (
                        SELECT seller_id, SUM(sold_count) AS sold_count
                        FROM selling_topups
                        GROUP BY seller_id
                 ) topup_sales ON topup_sales.seller_id = s.id
                 WHERE COALESCE(st.active,1) = 1
                   AND ((? > 0 AND st.game_id = ?) OR LOWER(TRIM(COALESCE(st.game_slug,''))) IN ('league-of-legends','lol','league'))
                 ORDER BY st.region ASC, st.offer_amount ASC, st.price ASC, st.waiting_time_minutes ASC", $gameId, $gameId) ?: [];
        } catch (Throwable $e) {
            $rows = [];
        }

        $topupRegions = function_exists('lb_topup_regions_for_game') ? lb_topup_regions_for_game($gameSlug) : [];
        $selectedTopupRegion = '';
        if (isset($_GET['region']) && trim((string)$_GET['region']) !== '') {
            $selectedTopupRegion = function_exists('lb_topup_normalize_region') ? lb_topup_normalize_region($_GET['region'], $gameSlug) : trim((string)$_GET['region']);
        }

        $serviceLabel = (string)($cfg['service_label'] ?? 'Riot Points');
        $meta = [
            'title' => (string)($cfg['page_title'] ?? ('Buy ' . $gameName . ' ' . $serviceLabel)) . ' | LoLBoost',
            'h1' => (string)($cfg['page_title'] ?? ('Buy ' . $gameName . ' ' . $serviceLabel)),
            'description' => (string)($cfg['page_description'] ?? ('Buy ' . $gameName . ' ' . $serviceLabel . ' on LoLBoost.')),
            'canonical' => BASE_URL . '/league-of-legends/top-ups',
            'robots' => 'index, follow',
        ];

        view_file('website/pages/topups/shop_dynamic', [
            'meta' => $meta,
            'game' => $gameSlug,
            'gameConfig' => $game,
            'topupsConfig' => $cfg,
            'topupSchema' => $schema,
            'topupRegions' => $topupRegions ?? [],
            'selectedTopupRegion' => $selectedTopupRegion ?? '',
            'topups' => $rows,
        ]);
        lb_public_page_cache_finish('topups-league-of-legends', $__pageCache);
    });




    $router->get('/items', function () {
        global $db;

        if (lb_public_page_cache_serve('items-league-of-legends', 90)) return;
        $__pageCache = lb_public_page_cache_start();

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $itemsPerPage = 20; // 5 rows × 4 cards

        $gameSlug = 'league-of-legends';
        $game = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
        if (empty($game) && function_exists('util_get_game_by_slug')) {
            $game = util_get_game_by_slug('lol') ?: [];
        }
        $gameId = (int)($game['id'] ?? 0);
        $gameName = (string)($game['name'] ?? 'League of Legends');

        $scopeSql = "((? > 0 AND si.game_id = ?) OR ((si.game_id IS NULL OR si.game_id = 0) AND LOWER(TRIM(COALESCE(si.game,''))) IN ('league-of-legends','lol','league')))";

        $totalRows = $db->run("SELECT COUNT(*) AS c FROM selling_items si WHERE si.active = 1 AND {$scopeSql}", $gameId, $gameId) ?: [];
        $totalItems = (int)($totalRows[0]['c'] ?? 0);
        $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $itemsPerPage;

        $items = $db->run(
            "SELECT si.*,
                    g.slug AS game_slug,
                    g.name AS game_name,
                    g.icon AS game_icon,
                    s.username AS seller_username,
                    s.slug AS seller_slug,
                    s.icon AS seller_icon,
                    s.rank AS seller_rank,
                    s.rank_icon AS seller_rank_icon,
                    s.is_active AS seller_is_active,
                    (
                        COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1 AND sa2.client_id IS NOT NULL), 0)
                        +
                        COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                        +
                        CASE
                            WHEN s.id = 28 THEN COALESCE((
                                SELECT COUNT(*)
                                FROM accounts a2
                                WHERE a2.admin_id = 51
                                  AND a2.status = 1
                                  AND a2.client_id IS NOT NULL
                            ), 0)
                            ELSE 0
                        END
                    ) AS seller_total_sales
             FROM selling_items si
             LEFT JOIN sellers s ON s.id = si.seller_id
             LEFT JOIN games g ON g.id = si.game_id
             WHERE si.active = 1 AND {$scopeSql}
             ORDER BY si.created_at DESC
             LIMIT {$itemsPerPage} OFFSET {$offset}",
             $gameId,
             $gameId
        ) ?: [];

        $pagination = [
            'page' => $page,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
            'totalItems' => $totalItems,
        ];

        $meta = [
            'title' => $gameName . ' Items | LoLBoost',
            'h1' => $gameName . ' Items',
            'description' => 'Browse ' . $gameName . ' items, skins and digital goods on LoLBoost.',
            'keywords' => 'league of legends items, lol skins, lol items, LoLBoost items',
            'canonical' => BASE_URL . '/league-of-legends/items',
            'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        ];

        view_file('website/pages/items/shop_dynamic', [
            'meta' => $meta,
            'game' => $gameSlug,
            'gameConfig' => $game,
            'itemsConfig' => function_exists('lb_get_items_page_config') ? lb_get_items_page_config($gameSlug) : [],
            'itemSchema' => function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($gameSlug) : [],
            'items' => $items,
            'pagination' => $pagination,
        ]);
        lb_public_page_cache_finish('items-league-of-legends', $__pageCache);
    });

    // Redirect legacy /selling_item/:id links to the correct profile page
    $router->get('/selling_item/:id', function ($id) {
        redirect_url('profile/item/' . (int)$id);
    });

    $router->get('/item/:slug', function ($slug) {
        global $db;

        $raw = trim(rawurldecode((string)$slug));

        // Recover the full slug from the real request URI, this avoids router
        // edge cases where pure text slugs are not passed reliably.
        $uri_path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
        if (preg_match('#^(?:/[a-z]{2})?/lol/item/(.+)$#i', $uri_path, $m)) {
            $raw = trim($m[1]);
        }

        if ($raw === '') {
            redirect_url('league-of-legends/items');
            return;
        }

        $__cacheKey = 'item-league-of-legends-' . preg_replace('/[^a-z0-9_-]/i', '', strtolower($raw));
        if (lb_public_page_cache_serve($__cacheKey, 90)) return;

        $item = null;

        // Allow numeric ID URLs for debugging / legacy links
        if (ctype_digit($raw)) {
            $item = $db->row(
                "SELECT si.*,
                        s.username AS seller_username,
                        s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon,
                        s.rank AS seller_rank,
                        s.rank_icon AS seller_rank_icon,
                        s.slug AS seller_slug,
                        s.is_active AS seller_is_active
                 FROM selling_items si
                 LEFT JOIN sellers s ON s.id = si.seller_id
                 WHERE si.id = ? AND si.active = 1
                 LIMIT 1",
                (int)$raw
            );
        }

        // Main lookup by slug, case and whitespace tolerant
        if (!$item) {
            $item = $db->row(
                "SELECT si.*,
                        s.username AS seller_username,
                        s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon,
                        s.rank AS seller_rank,
                        s.rank_icon AS seller_rank_icon,
                        s.slug AS seller_slug,
                        s.is_active AS seller_is_active
                 FROM selling_items si
                 LEFT JOIN sellers s ON s.id = si.seller_id
                 WHERE LOWER(TRIM(si.slug)) = LOWER(TRIM(?)) AND si.active = 1
                 LIMIT 1",
                $raw
            );
        }

        if (empty($item)) {
            redirect_url('league-of-legends/items');
            return;
        }

        // Legacy /league-of-legends/item/:slug must not show the wrong game nav.
        // If the item belongs to another game, send the user to /{game}/item/:slug.
        $_legacyItemGameSlug = strtolower(trim((string)($item['game'] ?? '')));
        if (!empty($item['game_id']) && function_exists('util_get_all_games')) {
            try {
                foreach ((array)util_get_all_games(true) as $_g) {
                    if ((int)($_g['id'] ?? 0) === (int)$item['game_id']) {
                        $_legacyItemGameSlug = strtolower(trim((string)($_g['slug'] ?? $_legacyItemGameSlug)));
                        break;
                    }
                }
            } catch (Throwable $e) {}
        }
        if ($_legacyItemGameSlug !== '' && !in_array($_legacyItemGameSlug, ['league-of-legends','lol'], true)) {
            redirect_url($_legacyItemGameSlug . '/item/' . rawurlencode($item['slug'] ?: $item['id']));
            return;
        }

        $__pageCache = lb_public_page_cache_start();

        $seller = null;
        $seller_items = [];

        if (!empty($item['seller_id'])) {
            $seller_id = (int)$item['seller_id'];

            $seller = $db->row(
                "SELECT id, username, icon, rank, rank_icon, slug, is_active, allow_chat_requests,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts WHERE seller_id = ? AND sold = 1 AND client_id IS NOT NULL), 0)
                            +
                            COALESCE((SELECT SUM(sold_count) FROM selling_items WHERE seller_id = ?), 0)
                            +
                            CASE
                                WHEN id = 28 THEN COALESCE((
                                    SELECT COUNT(*)
                                    FROM accounts a2
                                    WHERE a2.admin_id = 51
                                      AND a2.status = 1
                                      AND a2.client_id IS NOT NULL
                                ), 0)
                                ELSE 0
                            END
                        ) AS total_sold
                 FROM sellers
                 WHERE id = ?
                 LIMIT 1",
                $seller_id,
                $seller_id,
                $seller_id
            );

            $seller_items = $db->run(
                "SELECT id, title, slug, images, price, type, game, server, stock, requires_friendship_days
                 FROM selling_items
                 WHERE seller_id = ? AND active = 1 AND id != ?
                 ORDER BY created_at DESC
                 LIMIT 8",
                $seller_id,
                (int)$item['id']
            ) ?: [];
        }

        $meta = [
            'title' => item_meta_display_text($item['title'] ?? null, 'Item') . ' | LoLBoost',
            'h1' => 'Browse Gaming Items',
            'description' => !empty($item['description']) ? strip_tags((string)$item['description']) : 'Buy this gaming item on LoLBoost. Secure checkout and instant delivery.',
            'canonical' => BASE_URL . '/league-of-legends/item/' . rawurlencode($item['slug'] ?: $item['id']),
        ];

        view_file('website/pages/items/view', [
            'meta'                   => $meta,
            'item'                   => $item,
            'seller'                 => $seller,
            'seller_items'           => $seller_items,
            'sellerChatAllowedInline' => !empty($seller['allow_chat_requests'])
                                         || !array_key_exists('allow_chat_requests', (array)$seller),
        ]);
        lb_public_page_cache_finish($__cacheKey, $__pageCache);
    });
    $router->get('/account/:slug', function ($slug) {
        global $db;
        $slug = esc($slug);

        $account = db_get_row('selling_accounts', ['slug' => $slug], 1);

        if (!$account) {
            redirect_url('league-of-legends/accounts');
            return;
        }

        $_accountGame = strtolower(trim((string)($account['game'] ?? 'lol')));
        if ($_accountGame !== '' && $_accountGame !== 'lol' && $_accountGame !== 'league-of-legends') {
            redirect_url($_accountGame . '/account/' . rawurlencode($slug));
            return;
        }

        $__cacheKey = 'account-league-of-legends-' . preg_replace('/[^a-z0-9_-]/i', '', strtolower($slug));
        if (lb_public_page_cache_serve($__cacheKey, 60)) return;
        $__pageCache = lb_public_page_cache_start();

        $seller = null;
        $seller_accounts = [];
        if (!empty($account['seller_id'])) {
            $seller_id = (int)$account['seller_id'];
            $seller = $db->row(
                "SELECT s.id, s.username, s.slug, s.icon, s.rank, s.rank_icon,
                        s.fee_percent, s.seller_rank_id, s.is_active, s.allow_chat_requests,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS total_sold,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS total_sales,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
                 FROM sellers s
                 LEFT JOIN seller_stats ss ON ss.seller_id = s.id
                 WHERE s.id = ?
                 LIMIT 1",
                $seller_id
            );
            $seller_accounts = $db->run(
                "SELECT id, title, slug, server, game,
                        current_rank, current_division, current_lp,
                        rank, rank_label, game_data,
                        images, price, delivery_type, level, blue_essence, riot_points, champions, skins
                 FROM selling_accounts
                 WHERE seller_id = ? AND sold = 0 AND slug != ?
                   AND (game = 'lol' OR game = 'league-of-legends' OR game IS NULL OR game = '')
                 ORDER BY created_at DESC LIMIT 8",
                $seller_id, $slug
            );
        }

        $game = strtolower(trim((string)($account['game'] ?? 'league-of-legends')));
        if ($game === 'league-of-legends') {
            $rankLabel = util_get_lol_rank($account['current_rank'] ?? 0) . ' ' . util_format_lol_division($account['current_division'] ?? 0);
        } else {
            $rankLabel = $account['rank_label'] ?? util_get_rank_label($game, (int)($account['rank'] ?? 0));
        }
        $gameDisplayName = in_array($game, ['lol', 'league-of-legends'], true)
            ? 'League of Legends'
            : ucwords(str_replace('-', ' ', $game));

        $meta = [
            'title'       => 'Buy ' . $gameDisplayName . ' Account | LoLBoost',
            'h1'          => 'Buy ' . $gameDisplayName . ' Account',
            'description' => t('Buy this ' . $gameDisplayName . ' account: ' . $rankLabel . '.'),
            'keywords'    => 'buy ' . $game . ' accounts, ' . $game . ' accounts for sale',
            'image'       => 'three',
        ];

        view_file('website/pages/accounts/view_lol', [
            'meta'                   => $meta,
            'account'                => $account,
            'seller'                 => $seller,
            'seller_accounts'        => $seller_accounts,
            'sellerChatAllowedInline' => !empty($seller['allow_chat_requests'])
                                         || !array_key_exists('allow_chat_requests', (array)$seller),
        ]);
        lb_public_page_cache_finish($__cacheKey, $__pageCache);
    });


    // GGirls dedicated buy boostform, kept explicit because this form may be missing from the dynamic game form table on new.lolboost.gg.
    $router->get('/ggirls', function () {
        if (lb_public_page_cache_serve('ggirls-league-of-legends', 180)) return;
        $__pageCache = lb_public_page_cache_start();
        $data = db_load_boost_form(28);
        if (!$data) {
            $data = [
                'id' => 28,
                'uuid' => 'ggirls-lol',
                'type' => 'ggirls',
                'name' => 'Gamer Girl',
                'name_long' => 'Play with a Gamer Girl',
                'slug' => 'ggirls',
                'game' => 'lol',
                'json' => [],
                'description' => 'Play League of Legends together with a Gamer Girl.',
            ];
        }
        $data['type'] = 'ggirls';
        $data['slug'] = 'ggirls';
        $data['game'] = 'lol';
        $meta = [
            'title' => 'Play with a Gamer Girl | League of Legends | LoLBoost',
            'h1' => 'Play with a Gamer Girl',
            'description' => t('Play League of Legends together with a Gamer Girl. Choose mode, server, rank and amount, then buy instantly.'),
            'keywords' => 'lol ggirls, gamer girls, play with gamer girl, lolboost.gg',
            'image' => 'one',
            'canonical' => BASE_URL . '/league-of-legends/ggirls',
            'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        ];
        view_file('website/boost/lol', ['data' => $data, 'meta' => $meta, 'game' => 'lol']);
        lb_public_page_cache_finish('ggirls-league-of-legends', $__pageCache);
    });

    // ── Boost-form routes LAST — /:slug must not catch /accounts/items ────────
    util_register_game_boost_routes($router, 'league-of-legends', 'lol');
});


// League of Legends Classic, keep /lol-classic as the game services overview
// and register every active Classic boost form below it.
$router->group('/lol-classic', function () {
    global $router;
    util_register_game_boost_routes($router, 'lol-classic', 'lol-classic');
});

// Valorant Premium Accounts (Smurf Accounts)
// URL: /val/premium-accounts (alias: /valorant/premium-accounts)
$router->group('/valorant', function () {
    global $router;

    // ── Valorant Seller Accounts Shop ─────────────────────────
    $router->get('/accounts', function () {
        global $db;

        if (lb_public_page_cache_serve('accounts-valorant', 90)) return;
        $__pageCache = lb_public_page_cache_start();

        $page         = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $itemsPerPage = 25;
        $totalItems   = (int)$db->single("SELECT COUNT(*) FROM selling_accounts WHERE game IN ('valorant','val','valor') AND sold = 0 AND active = 1");
        $totalPages   = max(1, (int)ceil($totalItems / $itemsPerPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $itemsPerPage;

        $data = $db->run("
            SELECT sa.id, sa.title, sa.slug, sa.server, sa.game,
                   sa.rank, sa.rank_label, sa.game_data,
                   sa.images, sa.description, sa.level, sa.price, sa.delivery_type,
                   sa.created_at, sa.seller_id,
                   s.username  AS seller_username,
                   s.slug      AS seller_slug,
                   s.icon      AS seller_icon,
                   s.rank      AS seller_rank,
                   s.rank_icon AS seller_rank_icon,
                   s.is_active AS seller_is_active,
                   (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
            FROM selling_accounts sa
            LEFT JOIN sellers s ON s.id = sa.seller_id
            LEFT JOIN seller_stats ss ON ss.seller_id = s.id
            WHERE sa.game IN ('valorant','val','valor') AND sa.sold = 0 AND sa.active = 1
            ORDER BY seller_total_sales DESC, sa.created_at DESC
            LIMIT $itemsPerPage OFFSET $offset
        ") ?: [];

        $session_currency = $_SESSION['currency'] ?? 'EUR';
        if ($session_currency === 'USD') {
            $rate = get_exchange_rate();
            foreach ($data as &$row) { $row['price'] = (int)round($row['price'] * $rate); }
            unset($row);
        }

        $meta = [
            'title'       => 'Buy Valorant Accounts | Verified Sellers | LoLBoost',
            'h1'          => 'Valorant Accounts',
            'description' => 'Buy ranked Valorant accounts on LoLBoost. Instant delivery, verified sellers and secure transactions.',
            'canonical'   => BASE_URL . '/valorant/accounts',
            'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        ];

        $_valAcctConfig = util_get_accounts_page_config('valorant');
        view_file('website/pages/accounts/shop_val', [
            'meta'       => $meta,
            'data'       => $data,
            'pagination' => ['page' => $page, 'totalPages' => $totalPages, 'itemsPerPage' => $itemsPerPage, 'totalItems' => $totalItems],
            'game'       => 'val',
            'gameConfig' => $_valAcctConfig,
            'faq'        => 'accounts',
        ]);
        lb_public_page_cache_finish('accounts-valorant', $__pageCache);
    });

    // ── Valorant Account Detail ────────────────────────────────
    $router->get('/account/:slug', function ($slug) {
        global $db;
        $slug    = esc($slug);
        $account = $db->row("SELECT * FROM selling_accounts WHERE slug = ? AND LOWER(TRIM(game)) IN ('valorant','val','valor') LIMIT 1", $slug);
        if (!$account) { redirect_url('valorant/accounts'); return; }

        $__cacheKey = 'account-valorant-' . preg_replace('/[^a-z0-9_-]/i', '', strtolower($slug));
        if (lb_public_page_cache_serve($__cacheKey, 60)) return;
        $__pageCache = lb_public_page_cache_start();

        $seller          = null;
        $seller_accounts = [];
        if (!empty($account['seller_id'])) {
            $seller_id = (int)$account['seller_id'];
            $seller    = $db->row(
                "SELECT s.id, s.username, s.slug, s.icon, s.rank, s.rank_icon,
                        s.fee_percent, s.seller_rank_id, s.is_active, s.allow_chat_requests,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS total_sold,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS total_sales,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
                 FROM sellers s
                 LEFT JOIN seller_stats ss ON ss.seller_id = s.id
                 WHERE s.id = ?
                 LIMIT 1",
                $seller_id
            );
            $seller_accounts = $db->run(
                "SELECT id, title, slug, server, game, rank, rank_label, game_data,
                        images, price, delivery_type, level
                 FROM selling_accounts
                 WHERE seller_id = ? AND LOWER(TRIM(game)) IN ('valorant','val','valor') AND sold = 0 AND slug != ?
                 ORDER BY created_at DESC LIMIT 8",
                $seller_id, $slug
            );
        }

        $rankLabel = $account['rank_label'] ?? util_get_val_rank((int)($account['rank'] ?? 0));
        $meta = [
            'title'       => 'Buy Valorant Account – ' . $rankLabel . ' | LoLBoost',
            'h1'          => 'Buy Valorant Account',
            'description' => 'Buy this Valorant account on LoLBoost: ' . $rankLabel . '. Instant delivery, verified seller.',
            'keywords'    => 'buy valorant accounts, valorant accounts for sale',
            'image'       => 'three',
        ];

        view_file('website/pages/accounts/view_val', [
            'meta'                   => $meta,
            'account'                => $account,
            'seller'                 => $seller,
            'seller_accounts'        => $seller_accounts,
            'sellerChatAllowedInline' => !empty($seller['allow_chat_requests'])
                                         || !array_key_exists('allow_chat_requests', (array)$seller),
        ]);
        lb_public_page_cache_finish($__cacheKey, $__pageCache);
    });

    $router->get('/premium-accounts', function () {

        if (lb_public_page_cache_serve('premium-accounts-valorant', 90)) return;
        $__pageCache = lb_public_page_cache_start();

        $data = db_get_rows('account_packages', ['status' => 1, 'game_id' => 2, 'order' => 'price,ASC']);

        // group by server
        $servers = [];
        foreach ($data as $row) {
            // get available accounts for this package
            $row['available'] = count(db_get_rows('accounts', ['package_id' => $row['id'], 'status' => 0, 'game_id' => 2, 'select' => 'id'], 1));
            $servers[$row['server']][] = $row;
        }

        $meta = [
            'title' => 'Buy Premium Valorant Accounts | Ready to Play | LoLBoost',
            'h1' => 'Premium Valorant Accounts',
            'description' => t('Buy Valorant accounts ready to play. Instant delivery and secure checkout.'),
            'keywords' => 'valorant accounts, buy valorant smurf, valorant smurf account',
        ];

        // Dedicated Valorant template (ranks + icons)
        view_file('website/pages/accounts/val', ['meta' => $meta, 'data' => $servers, 'faq' => 'premium-accounts', 'active' => 'premium-accounts']);
        lb_public_page_cache_finish('premium-accounts-valorant', $__pageCache);
    });

    // ── Boost-form routes LAST — /:slug must not catch /accounts ─────────────
    // Valorant Top Ups Shop (service-gated, mirrors dynamic game routing)
    $_valTopupGame = function_exists('util_get_game_by_slug') ? util_get_game_by_slug('valorant') : null;
    if ($_valTopupGame && function_exists('util_game_has_service') && (
            util_game_has_service((int)$_valTopupGame['id'], 'topups')
            || util_game_has_service((int)$_valTopupGame['id'], 'top-ups')
            || util_game_has_service((int)$_valTopupGame['id'], 'currencies')
        )) {
        $router->get('/top-ups', function () {
            global $db;

            if (lb_public_page_cache_serve('topups-valorant', 90)) return;
            $__pageCache = lb_public_page_cache_start();

            $_dynSlug = 'valorant';
            if (function_exists('lb_topups_table_ensure')) lb_topups_table_ensure();
            $game = function_exists('util_get_game_by_slug') ? util_get_game_by_slug($_dynSlug) : [];
            $gameId = (int)($game['id'] ?? 0);
            $gameName = (string)($game['name'] ?? 'Valorant');
            $cfg = function_exists('lb_get_topups_page_config') ? lb_get_topups_page_config($_dynSlug) : [];
            $schema = function_exists('lb_get_game_topup_schema') ? lb_get_game_topup_schema($_dynSlug) : [];
            $rows = $db->run("SELECT st.*, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
                 FROM selling_topups st
                 LEFT JOIN sellers s ON s.id = st.seller_id
                 LEFT JOIN (
                        SELECT seller_id, COUNT(*) AS sold_count
                        FROM selling_accounts
                        WHERE sold = 1 AND client_id IS NOT NULL
                        GROUP BY seller_id
                 ) acct_sales ON acct_sales.seller_id = s.id
                 LEFT JOIN (
                        SELECT seller_id, SUM(sold_count) AS sold_count
                        FROM selling_items
                        GROUP BY seller_id
                 ) item_sales ON item_sales.seller_id = s.id
                 LEFT JOIN (
                        SELECT seller_id, SUM(sold_count) AS sold_count
                        FROM selling_topups
                        GROUP BY seller_id
                 ) topup_sales ON topup_sales.seller_id = s.id
                 WHERE st.active = 1 AND ((? > 0 AND st.game_id = ?) OR LOWER(TRIM(COALESCE(st.game_slug,''))) IN ('valorant','val','valor'))
                 ORDER BY st.region ASC, st.offer_amount ASC, st.price ASC, st.waiting_time_minutes ASC", $gameId, $gameId) ?: [];
            $topupRegions = function_exists('lb_topup_regions_for_game') ? lb_topup_regions_for_game($_dynSlug) : [];
            $selectedTopupRegion = '';
            if (isset($_GET['region']) && trim((string)$_GET['region']) !== '') {
                $selectedTopupRegion = function_exists('lb_topup_normalize_region') ? lb_topup_normalize_region($_GET['region'], $_dynSlug) : trim((string)$_GET['region']);
                $rows = array_values(array_filter((array)$rows, function ($row) use ($selectedTopupRegion, $_dynSlug) {
                    $rowRegion = function_exists('lb_topup_normalize_region') ? lb_topup_normalize_region($row['region'] ?? '', $_dynSlug) : trim((string)($row['region'] ?? ''));
                    return strtolower($rowRegion) === strtolower($selectedTopupRegion);
                }));
            }
            $serviceLabel = (string)($cfg['service_label'] ?? 'Valorant Points');
            $meta = [
                'title' => (string)($cfg['page_title'] ?? ($gameName . ' Top Ups')) . ' | LoLBoost',
                'h1' => (string)($cfg['page_title'] ?? ($gameName . ' Top Ups')),
                'description' => (string)($cfg['page_description'] ?? ('Buy ' . $gameName . ' top ups on LoLBoost.')),
                'canonical' => BASE_URL . '/valorant/top-ups',
                'robots' => 'index, follow',
            ];
            view_file('website/pages/topups/shop_dynamic', [
                'meta' => $meta,
                'game' => $_dynSlug,
                'gameConfig' => $game,
                'topupsConfig' => $cfg,
                'topupSchema' => $schema,
                'topupRegions' => $topupRegions ?? [],
                'selectedTopupRegion' => $selectedTopupRegion ?? '',
                'topups' => $rows,
            ]);
            lb_public_page_cache_finish('topups-valorant', $__pageCache);
        });
    }

    util_register_game_boost_routes($router, 'valorant', 'val');
});



$router->group('/teamfight-tactics', function () {
    global $router;

    // ── Boost-form routes (/, /rank-boost, /win-boost, …) — DB-driven ─────────
    util_register_game_boost_routes($router, 'teamfight-tactics', 'tft');

});




$is_client = defined('CLIENT_DATA') && !empty(CLIENT_DATA);

// =============================================
// SELLER ROLE CHECK
// =============================================
$is_seller   = defined('SELLER_DATA') && !empty(SELLER_DATA);
$seller_data = $is_seller ? SELLER_DATA : null;
if ($is_seller && $seller_data) {
    $seller_data['effective_fee'] = ($seller_data['fee_percent'] !== null && $seller_data['fee_percent'] !== '')
        ? (float)$seller_data['fee_percent']
        : 15.0;
}
// =============================================

$router->get('/checkout/:uuid:all', function ($uuid) {
    global $is_client, $db;
    $uuid = esc($uuid);
    $invoice = db_get_row('invoices', ['uuid' => $uuid]);
    if (!$invoice) {
        redirect_url('');
    }
    switch ($invoice['order_type']) {
        case 'egirl_session':
            $order = $db->row(
                "SELECT eo.*, b.username AS egirl_username, b.icon AS egirl_icon
                 FROM egirl_orders eo
                 LEFT JOIN boosters b ON b.id = eo.egirl_id
                 WHERE eo.id = ? LIMIT 1",
                $invoice['order_id']
            );

            if (!$order) {
                redirect_url('');
                return;
            }

            // Claim guest egirl checkout for the logged in client
            if ($is_client && (int)($invoice['client_id'] ?? 0) === 0 && (int)($order['client_id'] ?? 0) === 0) {
                db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
                db_update_row('egirl_orders', ['id' => $order['id']], ['client_id' => CLIENT_ID]);
                $invoice['client_id'] = CLIENT_ID;
                $order['client_id'] = CLIENT_ID;
            }

            if ((int)$order['client_id'] !== (int)($invoice['client_id'] ?? 0)) {
                redirect_url('profile/orders');
                return;
            }

            $data = [
                'order'   => $order,
                'invoice' => $invoice,
            ];
            break;

        case 'order':
            $order = db_get_row('orders', ['id' => $invoice['order_id']]);

            // --- Abandoned unpaid bonus (extra 10% off on total_price) ---
            $ab = $_GET['ab'] ?? null;
            if ($ab && $order && isset($order['status']) && $order['status'] === 'UNPAID') {
                // refresh invoice row
                $invoiceFresh = db_get_row('invoices', ['id' => $invoice['id']]);
                if (!$invoiceFresh) {
                    // fail safe
                } else {

                $tokenOk = !empty($invoiceFresh['abandoned_bonus_token']) && hash_equals((string)$invoiceFresh['abandoned_bonus_token'], (string)$ab);
                $notApplied = empty($invoiceFresh['abandoned_bonus_applied_at']);

                $notExpired = true;
                if (!empty($invoiceFresh['abandoned_discount_expires_at'])) {
                    $notExpired = (strtotime($invoiceFresh['abandoned_discount_expires_at']) >= time());
                }

                if ($tokenOk && $notApplied && $notExpired) {
                    $oldTotal = (int)$invoiceFresh['total_price'];
                    if ($oldTotal > 0) {
                        $bonus = (int) round($oldTotal * 0.05);
                        $newTotal = max(0, $oldTotal - $bonus);

                        db_update_row('invoices', ['id' => $invoice['id']], [
                            'total_price' => $newTotal,
                            'abandoned_bonus_amount' => $bonus,
                            'abandoned_bonus_applied_at' => date('Y-m-d H:i:s'),
                        ]);

                        // IMPORTANT: also update order price so admin/booster/client UI shows the reduced amount
                        db_update_row('orders', ['id' => (int)$invoice['order_id']], [
                            'price' => $newTotal,
                            'price_eur' => $newTotal,
                        ]);

                        // update in-memory invoice so checkout renders the reduced price
                        $invoice['total_price'] = $newTotal;
                    }
                }
                }
            }
            // --- end abandoned unpaid bonus ---

            $order_opts = db_get_row('order_options', ['order_id' => $order['id']]);
            $form = db_get_row('boost_forms', ['id' => $order['form_id']]);
            // Dynamic games (anything besides lol/val/tft) store rank names/icons/divisions
            // in the form's own pricing JSON — the checkout summary needs it to render ranks correctly.
            if (!empty($form['uuid'])) {
                $form['json'] = get_pricing_json($form['uuid']);
            }
            // NOTE: Booster lookup can be expensive because it uses LIKE '%<game>%'. Keep it lightweight.
// We limit results and only query when a game is present. For full search, load via AJAX/pagination.
$boosters = [];
if (!empty($form['game'])) {
    $boosters = db_get_rows('boosters', [
        'select' => 'id,username',
        'is_banned' => 0,
        'boost_requests' => 1,
        'games' => ['s' => $form['game']],
    ]);
}
            $ranked5s_boosters_count = max(1, min(4, (int)($order_opts['boosters'] ?? 1)));
            $data = array_merge($form, $order_opts, $order, [
                'boosters' => $boosters,
                'ranked_5s_boosters_count' => $ranked5s_boosters_count,
            ]);
            // if $is_client = true then update order and invoice set client_id = CLIENT_ID (only if needed to avoid unnecessary DB writes/locks)
if ($is_client) {
    if (empty($order['client_id']) || (int)$order['client_id'] !== (int)CLIENT_ID) {
        db_update_row('orders', ['id' => $order['id']], ['client_id' => CLIENT_ID]);
    }
    if (empty($invoice['client_id']) || (int)$invoice['client_id'] !== (int)CLIENT_ID) {
        db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
    }
}

            break;
        case 'account':
            // get package data
            $package = db_get_row('account_packages', ['id' => $invoice['order_id']]);
            $data = $package;
            // if $is_client = true then update invoice set client_id = CLIENT_ID
            if ($is_client) {
                db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
            }

            break;
        case 'tip':
            $tip = db_get_row('tips', ['id' => $invoice['order_id']]);
            $data = $tip;

            break;
        case 'invoice':
            $data = db_get_row('invoices', ['id' => $invoice['id']]);
            break;
        case 'lol_account':
            try {
                $data = $db->row(
                    "SELECT sa.*,
                            g.id AS checkout_game_id,
                            g.name AS checkout_game_name,
                            g.slug AS checkout_game_slug,
                            g.icon AS checkout_game_icon
                     FROM selling_accounts sa
                     LEFT JOIN games g ON g.id = sa.game_id
                     WHERE sa.id = ?
                     LIMIT 1",
                    (int)$invoice['order_id']
                );
            } catch (Throwable $e) {
                $data = db_get_row('selling_accounts', ['id' => $invoice['order_id']]);
            }

            if (!is_array($data)) {
                $data = [];
            }

            // Compatibility fallback for older accounts which only store a game slug.
            if (empty($data['checkout_game_name'])) {
                $storedGame = trim((string)($data['game'] ?? $data['game_slug'] ?? ''));
                if ($storedGame !== '' && function_exists('util_get_game_by_slug')) {
                    $gameRow = util_get_game_by_slug($storedGame);
                    if (is_array($gameRow)) {
                        $data['checkout_game_id'] = (int)($gameRow['id'] ?? 0);
                        $data['checkout_game_name'] = (string)($gameRow['name'] ?? '');
                        $data['checkout_game_slug'] = (string)($gameRow['slug'] ?? '');
                        $data['checkout_game_icon'] = (string)($gameRow['icon'] ?? '');
                    }
                }
            }

            if ($is_client) {
                db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
            }
            break;

        case 'selling_item':
            try {
                $data = $db->row(
                    "SELECT si.*, g.id AS checkout_game_id, g.name AS checkout_game_name, g.slug AS checkout_game_slug, g.icon AS checkout_game_icon
                     FROM selling_items si
                     LEFT JOIN games g ON g.id = si.game_id
                     WHERE si.id = ?
                     LIMIT 1",
                    (int)$invoice['order_id']
                );
                if ((!is_array($data) || empty($data['checkout_game_slug'])) && !empty($data['game'])) {
                    $g = function_exists('util_get_game_by_slug') ? util_get_game_by_slug((string)$data['game']) : null;
                    if (is_array($g)) {
                        $data['checkout_game_id'] = (int)($g['id'] ?? 0);
                        $data['checkout_game_name'] = (string)($g['name'] ?? '');
                        $data['checkout_game_slug'] = (string)($g['slug'] ?? '');
                        $data['checkout_game_icon'] = (string)($g['icon'] ?? '');
                    }
                }
            } catch (Throwable $e) {
                $data = db_get_row('selling_items', ['id' => $invoice['order_id']]);
            }
            if (!is_array($data)) $data = [];

            // Parse quantity from invoice description (e.g. 'Item Title x12')
            $_si_desc = (string)($invoice['description'] ?? '');
            $_si_qty  = 1;
            if (preg_match('/x(\d+)$/', $_si_desc, $_si_m)) {
                $_si_qty = (int)$_si_m[1];
            }
            $data['_qty'] = $_si_qty;

            // Claim guest checkout for the logged-in client
            if ($is_client && (int)($invoice['client_id'] ?? 0) === 0) {
                db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
                $invoice['client_id'] = CLIENT_ID;
            }
            break;

        case 'selling_topup':
            try {
                $data = $db->row(
                    "SELECT st.*, g.id AS checkout_game_id, g.name AS checkout_game_name, g.slug AS checkout_game_slug, g.icon AS checkout_game_icon
                     FROM selling_topups st
                     LEFT JOIN games g ON g.id = st.game_id
                     WHERE st.id = ?
                     LIMIT 1",
                    (int)$invoice['order_id']
                );
            } catch (Throwable $e) {
                $data = [];
            }
            if (!is_array($data)) $data = [];
            $_tu_desc = (string)($invoice['description'] ?? '');
            $_tu_qty = 1;
            if (preg_match('/x(\d+)$/', $_tu_desc, $_tu_m)) {
                $_tu_qty = (int)$_tu_m[1];
            }
            $data['_qty'] = $_tu_qty;
            $data['_checkout_fields'] = [];
            if (!empty($invoice['checkout_data'])) {
                $_tu_checkout = json_decode((string)$invoice['checkout_data'], true);
                if (is_array($_tu_checkout) && isset($_tu_checkout['fields']) && is_array($_tu_checkout['fields'])) {
                    $data['_checkout_fields'] = $_tu_checkout['fields'];
                }
            }
            if ($is_client && (int)($invoice['client_id'] ?? 0) === 0) {
                db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
                $invoice['client_id'] = CLIENT_ID;
            }
            break;

        case 'digital_good':
            $data = function_exists('dg_get_listing')
                ? dg_get_listing((int)$invoice['order_id'])
                : db_get_row('digital_goods', ['id' => $invoice['order_id']]);
            if (!is_array($data)) $data = [];
            if ($is_client && (int)($invoice['client_id'] ?? 0) === 0) {
                db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
                $invoice['client_id'] = CLIENT_ID;
            }
            break;

        case 'egirl_session':
            $egirl_order = $db->row(
                "SELECT eo.*, b.username AS egirl_username, b.icon AS egirl_icon
                 FROM egirl_orders eo
                 LEFT JOIN boosters b ON b.id = eo.egirl_id
                 WHERE eo.id = ? LIMIT 1",
                $invoice['order_id']
            );
            $data = $egirl_order ?: [];
            if ($is_client) {
                db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
            }
            break;
    }
    $meta = [
        'title' => 'Checkout | Secure & Fast | LoLBoost',
        'h1' => 'Order Checkout',
        'description' => 'LoLBoost â Fast, safe and professional. Buy and sell game accounts, items, boosting and coaching. Secure checkout and 24/7 support.',
        'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
    ];
    view_file('website/pages/checkout', ['invoice' => $invoice, 'data' => $data, 'meta' => $meta, 'uuid' => $uuid]);
});

// ----- ALT version of $router->get('/checkout/:uuid', ...) BEGIN -----
// $router->get('/checkout/:uuid', function ($uuid) {
//     global $is_client;
//     $uuid = esc($uuid);
//     $invoice = db_get_row('invoices', ['uuid' => $uuid]);
//     if (!$invoice) {
//         redirect_url('');
//     }
//     switch ($invoice['order_type']) {
//         case 'order':
//             $order = db_get_row('orders', ['id' => $invoice['order_id']]);
//             $order_opts = db_get_row('order_options', ['order_id' => $order['id']]);
//             $form = db_get_row('boost_forms', ['id' => $order['form_id']]);
//             $boosters = db_get_rows('boosters', ['select' => 'id,username', 'is_banned' => 0, 'boost_requests' => 1, 'games' => ['s' => $form['game']]]);
//             $data = array_merge($form, $order_opts, $order, ['boosters' => $boosters]);
//             // if $is_client = true then update order and invoice set client_id = CLIENT_ID
//             if ($is_client) {
//                 db_update_row('orders', ['id' => $order['id']], ['client_id' => CLIENT_ID]);
//                 db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
//             }
// 
//             break;
//         case 'account':
//             // get package data
//             $package = db_get_row('account_packages', ['id' => $invoice['order_id']]);
//             $data = $package;
//             // if $is_client = true then update invoice set client_id = CLIENT_ID
//             if ($is_client) {
//                 db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
//             }
// 
//             break;
//         case 'tip':
//             $tip = db_get_row('tips', ['id' => $invoice['order_id']]);
//             $data = $tip;
// 
//             break;
//         case 'invoice':
//             $data = db_get_row('invoices', ['id' => $invoice['id']]);
//             break;
//         case 'lol_account':
//             $data = db_get_row('selling_accounts', ['id' => $invoice['order_id']]);
// 
//             if ($is_client) {
//                 db_update_row('invoices', ['id' => $invoice['id']], ['client_id' => CLIENT_ID]);
//             }
//             break;
//     }
//     $meta = [
//         'title' => 'Checkout | Secure & Fast | LoLBoost',
//         'h1' => 'Order Checkout',
//         'description' => 'LoLBoost â Fast, safe and professional. Buy and sell game accounts, items, boosting and coaching. Secure checkout and 24/7 support.',
//         'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
//     ];
//     view_file('website/pages/checkout', ['invoice' => $invoice, 'data' => $data, 'meta' => $meta, 'uuid' => $uuid]);
// });
// ----- ALT version END -----


$router->get('/checkout/:uuid/process:all', function ($uuid) {
    global $db;
    $uuid = esc($uuid);
    $invoice = db_get_row('invoices', ['uuid' => $uuid]);
    $coins_order = !empty($_GET['coins_order']);

    if (!$invoice) {
        redirect_url('');
    }

    if ($coins_order) {
        if ($invoice['status'] == 'PAID') {
            if (($invoice['order_type'] ?? '') === 'digital_good') { lb_dg_mark_invoice_paid($invoice); }
            redirect_url('checkout/' . $uuid . '/complete');
        }

        $invoice_status = 'PAID';
    } else {
        // check if invoice total is 0 or if invoice is already paid
        if ($invoice['total_price'] <= 0 || $invoice['status'] == 'PAID') {
            if (($invoice['order_type'] ?? '') === 'digital_good') { lb_dg_mark_invoice_paid($invoice); }
            redirect_url('checkout/' . $uuid . '/complete');
        }

        // Stripe browser return is a fallback. The webhook remains the primary path.
        // Process the same Checkout Session through the shared idempotent function.
        if (($_GET['m'] ?? '') === 'stripe' && !empty($_GET['t'])) {
            try {
                lb_fulfill_stripe_checkout_session((string)$_GET['t']);
            } catch (\Throwable $e) {
                error_log('Stripe success URL fallback failed: ' . $e->getMessage());
            }

            $fresh_invoice = db_get_row('invoices', ['id' => $invoice['id']]);
            if (($fresh_invoice['status'] ?? '') === 'PAID') {
                redirect_url('checkout/' . $uuid . '/complete');
            }
        }

        // A succeeded transaction may already have been stored by the webhook.
        $transaction = db_get_row('transactions', ['invoice_id' => $invoice['id']]);
        if ($transaction && ($transaction['status'] ?? '') === 'succeeded') {
            redirect_url('checkout/' . $uuid);
        }

        if (isset($_GET['t']) && !empty($_GET['t'])) {
            $token = esc($_GET['t']);
        } else {
            redirect_url('checkout/' . $uuid);
        }

        if (isset($_GET['m']) && !empty($_GET['m'])) {
            $processor = esc($_GET['m']);
        } else {
            redirect_url('checkout/' . $uuid);
        }

        $transaction = get_tx_data($processor, $token);

        if ($transaction == false) {
            redirect_url('checkout/' . $uuid);
        }

        if ($transaction['amount'] < $invoice['total_price'] || $transaction['status'] == "failed") {
            redirect_url('checkout/' . $uuid);
        }

        $transaction['invoice_id'] = $invoice['id'];
        $transaction['client_id'] = $invoice['client_id'];
        $transaction['order_id'] = $invoice['order_id'];
        $transaction['order_type'] = $invoice['order_type'];

        $transaction_id = db_insert_row('transactions', $transaction);


        // check if transaction is not failed
        if ($transaction['status'] == "failed") {
            redirect_url('checkout/' . $uuid);
        }


        $invoice_status = tx_status_to_invoice($transaction['status']);
    }

    db_update_row('invoices', ['id' => $invoice['id']], ['status' => $invoice_status, 'paid_at' => date('Y-m-d h:i:s')]);

    // ── selling_item: create purchase record now that client_id is known ──
    if ($invoice['order_type'] === 'selling_item') {
        try {
            $db->run("ALTER TABLE selling_item_purchases ADD COLUMN IF NOT EXISTS game_id INT(11) NULL DEFAULT NULL");
            $db->run("ALTER TABLE selling_item_purchases ADD COLUMN IF NOT EXISTS game_slug VARCHAR(100) NULL DEFAULT NULL");
            $db->run("ALTER TABLE selling_item_purchases ADD COLUMN IF NOT EXISTS game_name VARCHAR(191) NULL DEFAULT NULL");
        } catch (Throwable $e) {}
        $si_client_id = (int)($invoice['client_id'] ?? 0);
        $si_item      = db_get_row('selling_items', ['id' => $invoice['order_id']], 1);
        $si_existing  = $si_client_id
            ? db_get_row('selling_item_purchases', ['invoice_id' => (int)$invoice['id']], 1)
            : null;

        if ($si_item && !$si_existing) {
            // Parse quantity from invoice description (e.g. "Item Title x12")
            $si_qty = 1;
            if (preg_match('/x(\d+)$/', (string)($invoice['description'] ?? ''), $si_m)) {
                $si_qty = max(1, (int)$si_m[1]);
            }
            $si_unit = (int)($si_item['price'] ?? 0);
            $si_game_id = (int)($si_item['game_id'] ?? 0);
            $si_game_slug = trim((string)($si_item['game'] ?? ''));
            $si_game_name = '';
            try {
                if ($si_game_id > 0) {
                    $si_g = $db->row("SELECT id, name, slug FROM games WHERE id = ? LIMIT 1", $si_game_id);
                    if (!empty($si_g)) {
                        $si_game_slug = trim((string)($si_g['slug'] ?? $si_game_slug));
                        $si_game_name = trim((string)($si_g['name'] ?? ''));
                    }
                } elseif ($si_game_slug !== '' && function_exists('util_get_game_by_slug')) {
                    $si_g = util_get_game_by_slug($si_game_slug);
                    if (is_array($si_g)) {
                        $si_game_id = (int)($si_g['id'] ?? 0);
                        $si_game_name = trim((string)($si_g['name'] ?? ''));
                    }
                }
            } catch (Throwable $e) {}
            if ($si_game_name === '') $si_game_name = $si_game_slug !== '' ? ucwords(str_replace('-', ' ', $si_game_slug)) : 'Game';

            db_add_row('selling_item_purchases', [
                'item_id'    => (int)$invoice['order_id'],
                'seller_id'  => (int)($si_item['seller_id'] ?? 0),
                'client_id'  => $si_client_id ?: null,
                'invoice_id' => (int)$invoice['id'],
                'game_id'    => $si_game_id ?: null,
                'game_slug'  => $si_game_slug ?: null,
                'game_name'  => $si_game_name ?: null,
                'price'      => $si_unit * $si_qty,
                'quantity'   => $si_qty,
                'unit_price' => $si_unit,
                'currency'   => $invoice['currency'] ?? 'EUR',
                'status'     => 'PAID',
                'paid_at'    => date('Y-m-d H:i:s'),
            ]);
            // Keep listing stock and seller sales in sync with the paid quantity.
            $db->run(
                "UPDATE selling_items
                 SET stock = GREATEST(0, COALESCE(stock, 0) - ?),
                     sold_count = COALESCE(sold_count, 0) + ?
                 WHERE id = ?",
                $si_qty,
                $si_qty,
                (int)$invoice['order_id']
            );
            if (!empty($si_item['seller_id']) && function_exists('sync_seller_stats')) sync_seller_stats((int)$si_item['seller_id']);
            // Notifications are handled by process_order() in functions.php
        }
    }
    // ── selling_topup: create purchase record and reserve stock after payment ──
    if (($invoice['order_type'] ?? '') === 'selling_topup' && $invoice_status === 'PAID') {
        try {
            if (function_exists('lb_topups_table_ensure')) lb_topups_table_ensure();
            if (function_exists('lb_topup_purchases_table_ensure')) lb_topup_purchases_table_ensure();
            $db->run("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS checkout_data LONGTEXT NULL");
        } catch (Throwable $e) {}

        $tu_client_id = (int)($invoice['client_id'] ?? 0);
        $tu_topup = $db->row("SELECT st.*, g.name AS db_game_name, g.slug AS db_game_slug FROM selling_topups st LEFT JOIN games g ON g.id = st.game_id WHERE st.id = ? LIMIT 1", (int)$invoice['order_id']);
        $tu_existing = $db->row("SELECT id FROM selling_topup_purchases WHERE invoice_id = ? LIMIT 1", (int)$invoice['id']);

        if ($tu_topup && !$tu_existing) {
            $tu_qty = 1;
            if (preg_match('/x(\d+)$/', (string)($invoice['description'] ?? ''), $tu_m)) {
                $tu_qty = max(1, (int)$tu_m[1]);
            }
            $tu_unit = (int)($tu_topup['price'] ?? 0);
            $tu_checkout_data = (string)($invoice['checkout_data'] ?? '');
            $db->run("INSERT INTO selling_topup_purchases (topup_id, seller_id, client_id, invoice_id, game_id, game_slug, game_name, offer_key, offer_title, offer_amount, offer_unit, region, platform, quantity, unit_price, price, currency, waiting_time_value, waiting_time_unit, waiting_time_minutes, checkout_data, status, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PAID', NOW())",
                (int)$tu_topup['id'],
                (int)$tu_topup['seller_id'],
                $tu_client_id ?: null,
                (int)$invoice['id'],
                !empty($tu_topup['game_id']) ? (int)$tu_topup['game_id'] : null,
                (string)($tu_topup['db_game_slug'] ?? $tu_topup['game_slug'] ?? ''),
                (string)($tu_topup['db_game_name'] ?? $tu_topup['game_name'] ?? 'Game'),
                (string)($tu_topup['offer_key'] ?? ''),
                (string)($tu_topup['offer_title'] ?? 'Top Up'),
                $tu_topup['offer_amount'] ?? null,
                (string)($tu_topup['offer_unit'] ?? ''),
                (string)($tu_topup['region'] ?? 'Global'),
                (string)($tu_topup['platform'] ?? ''),
                $tu_qty,
                $tu_unit,
                $tu_unit * $tu_qty,
                (string)($invoice['currency'] ?? 'EUR'),
                (int)($tu_topup['waiting_time_value'] ?? 0),
                (string)($tu_topup['waiting_time_unit'] ?? 'minutes'),
                (int)($tu_topup['waiting_time_minutes'] ?? 0),
                $tu_checkout_data
            );
            $tu_new_stock = max(0, (int)($tu_topup['stock'] ?? 0) - $tu_qty);
            $db->run("UPDATE selling_topups SET stock = ?, sold_count = COALESCE(sold_count,0) + ?, updated_at = NOW() WHERE id = ?", $tu_new_stock, $tu_qty, (int)$tu_topup['id']);
            if (!empty($tu_topup['seller_id']) && function_exists('sync_seller_stats')) sync_seller_stats((int)$tu_topup['seller_id']);
        }
    }

    // ── digital_good: create/update purchase record after payment ──
    // Same idea as LoL orders: paid invoice = paid order record.
    if (($invoice['order_type'] ?? '') === 'digital_good' && $invoice_status === 'PAID') {
        $invoice['status'] = $invoice_status;
        $invoice['paid_at'] = date('Y-m-d H:i:s');
        lb_dg_mark_invoice_paid($invoice);
    }
    // ────────────────────────────────────────────────────────────────────

    $notifications = process_order($invoice);

    if ($notifications == false) {
        redirect_url('checkout/' . $uuid);
    }

    foreach ($notifications as $notification) {
        db_add_row('notifications', $notification);
    }

    // Notify eligible boosters immediately on every paid boost order. Discord and
    // the browser toast are separate channels; without this realtime event, a
    // connected booster only saw LoL orders that happened to enter another path.
    if (($invoice['order_type'] ?? '') === 'order'
        && !empty($invoice['order_id'])
        && function_exists('lb_realtime_emit_new_order')) {
        lb_realtime_emit_new_order((int)$invoice['order_id']);
    }

    // Start Discord/email delivery as soon as the paid-order notifications exist.
    // Previously this happened only after the browser reached the complete page,
    // which could leave New Order webhooks queued until a later sender run.
    if (function_exists('trigger_notification_sender_async')) {
        trigger_notification_sender_async();
    }

    if ($invoice['order_type'] == 'addon') {
        redirect_url('order/' . $invoice['order_id']);
    }

    redirect_url('checkout/' . $uuid . '/complete');
});

$router->get('/checkout/:uuid:all/complete', function ($uuid) {
    global $db;
    $uuid = esc($uuid);
    $invoice = db_get_row('invoices', ['uuid' => $uuid]);

    if (!$invoice) {
        redirect_url('');
    }

    if ($invoice['status'] != 'PAID') {
        redirect_url('checkout/' . $uuid);
    }

    $meta = [
        'title' => 'Payment Completed | LoLBoost',
        'h1' => 'Payment Completed',
        'description' => 'LoLBoost â Fast, safe and professional. Buy and sell game accounts, items, boosting and coaching. Secure checkout and 24/7 support.',
        'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
    ];

    // Option A: render the page first (fast for the user), then run notifications after the response is sent.
    header('X-Complete-Option: A');
    // For lol_account: pass the account so the view can show a "View Account" button
    $lol_account_id = null;
    if ($invoice['order_type'] === 'lol_account' && !empty($invoice['order_id'])) {
        $lol_account_id = (int)$invoice['order_id'];
    }

    // For selling_item: pass the purchase id so the view can link to profile/item/:id
    $selling_item_purchase_id = null;
    if ($invoice['order_type'] === 'selling_item') {
        $si_p = $db->row("SELECT id FROM selling_item_purchases WHERE invoice_id = ? LIMIT 1", (int)$invoice['id']);
        if ($si_p) $selling_item_purchase_id = (int)$si_p['id'];
    }

    // For selling_topup: pass the purchase id so the thank you button opens the exact order.
    $selling_topup_purchase_id = null;
    if (($invoice['order_type'] ?? '') === 'selling_topup') {
        try {
            $tu_p = $db->row("SELECT id FROM selling_topup_purchases WHERE invoice_id = ? LIMIT 1", (int)$invoice['id']);
            if ($tu_p) $selling_topup_purchase_id = (int)$tu_p['id'];
        } catch (Throwable $e) { $selling_topup_purchase_id = null; }
    }

    // For digital_good: make sure paid invoice has a paid purchase, then pass purchase id.
    $digital_good_purchase_id = null;
    if (($invoice['order_type'] ?? '') === 'digital_good') {
        $digital_good_purchase_id = lb_dg_mark_invoice_paid($invoice);
        if (!$digital_good_purchase_id) {
            $dg_p = $db->row("SELECT id FROM digital_good_purchases WHERE invoice_id = ? LIMIT 1", (int)$invoice['id']);
            if ($dg_p) $digital_good_purchase_id = (int)$dg_p['id'];
        }
    }

    // Render view into a buffer so we can flush the response before doing slow background work
    ob_start();
    view_file('website/pages/checkout-complete', [
        'invoice' => $invoice,
        'meta' => $meta,
        'lol_account_id' => $lol_account_id,
        'selling_item_purchase_id' => $selling_item_purchase_id,
        'selling_topup_purchase_id' => $selling_topup_purchase_id,
        'digital_good_purchase_id' => $digital_good_purchase_id,
    ]);
    $html = ob_get_clean();
    echo $html;

    // Send response to the client immediately (works on PHP-FPM; Hostinger usually uses it)
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        @ob_flush();
        @flush();
    }

    // Tip-specific logic (kept here so it doesn't delay the complete page)
    if ($invoice['order_type'] == 'tip') {
        $tip = db_get_row('tips', ['id' => $invoice['order_id']]);
        $booster = db_get_row('boosters', ['id' => $tip['booster_id']]);

        // Discord DM logic currently commented out in your code – left unchanged.
    }
});



$router->get('/checkout/pending', function () {
    global $is_client;

    $meta = [
        'title' => 'Payment Processing | LoLBoost',
        'h1' => 'Payment Processing',
        'description' => 'LoLBoost â Fast, safe and professional. Buy and sell game accounts, items, boosting and coaching. Secure checkout and 24/7 support.',
        'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
    ];
    // print_r($invoice);
    view_file('website/pages/checkout-pending', ['meta' => $meta]);
});

$router->post('/checkout/callback/stripe', function () {
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    if ($payload === false || $payload === '' || $signature === '') {
        http_response_code(400);
        echo 'Missing Stripe payload or signature';
        return;
    }

    if (!defined('STRIPE_WEBHOOK_SECRET') || STRIPE_WEBHOOK_SECRET === '') {
        error_log('STRIPE_WEBHOOK_SECRET is not configured.');
        http_response_code(500);
        echo 'Webhook secret not configured';
        return;
    }

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            STRIPE_WEBHOOK_SECRET
        );

        if (in_array($event->type, [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
        ], true)) {
            $session = $event->data->object;

            // completed can also mean that a delayed payment is still unpaid.
            // In that case async_payment_succeeded will fulfil it later.
            if (($session->payment_status ?? '') === 'paid') {
                lb_fulfill_stripe_checkout_session($session);
            }
        }

        http_response_code(200);
        echo 'ok';
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        error_log('Stripe webhook signature failed: ' . $e->getMessage());
        http_response_code(400);
        echo 'Invalid signature';
    } catch (\UnexpectedValueException $e) {
        error_log('Stripe webhook payload failed: ' . $e->getMessage());
        http_response_code(400);
        echo 'Invalid payload';
    } catch (\Throwable $e) {
        // Return 500 so Stripe retries the event automatically.
        error_log('Stripe webhook processing failed: ' . $e->getMessage());
        http_response_code(500);
        echo 'Webhook processing failed';
    }
});


$router->post('/checkout/callback/coinbase', function () {
    $secret = "cba5d67a-fe7a-44d6-835a-77230df3032d";
    $signature = $_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'] ?? null;
    $payload = file_get_contents('php://input');

    // Debug logging
    file_put_contents('webhook_debug.log', "Payload:\n" . $payload . "\n\nHeaders:\n" . json_encode(getallheaders(), JSON_PRETTY_PRINT), FILE_APPEND);

    if (!$signature || !$payload) {
        http_response_code(400);
        exit('Missing signature or payload');
    }

    $expectedSig = hash_hmac('sha256', $payload, $secret);
    if (!hash_equals($expectedSig, $signature)) {
        http_response_code(400);
        exit('Invalid signature');
    }

    try {
        $response = json_decode($payload, true);
        $event = $response['event'] ?? null;

        if (!$event || $event['type'] !== 'charge:confirmed') {
            http_response_code(200); // Accept other events silently
            return;
        }

        $data = $event['data'];
        $payment = $data['payments'][0] ?? null;

        if (!$payment || $payment['status'] !== 'confirmed') {
            http_response_code(200);
            return;
        }

        $invoice_id = intval($data['metadata']['invoiceId']);
        $tx_token = $data['code'];
        $invoice_data = db_get_row('invoices', ['id' => $invoice_id]);


        if (!$invoice_data || !in_array($invoice_data['status'], ['UNPAID', 'PENDING'])) {
            http_response_code(200);
            return;
        }

        $mapped_status = strtolower(str_replace(
            ['refunded', 'failed', 'cancelled', 'pending', 'confirmed'],
            ['chargedback', 'failed', 'cancelled', 'pending', 'succeeded'],
            $payment['status']
        ));

        $tx_data = [
            'client_id' => $invoice_data['client_id'],
            'invoice_id' => $invoice_id,
            'order_id' => $invoice_data['order_id'],
            'order_type' => $invoice_data['order_type'],
            'processor' => 'coinbase',
            'amount' => $payment['value']['local']['amount'] * 100,
            'currency' => $payment['value']['local']['currency'],
            'token' => $tx_token,
            'payment_method' => $payment['network'],
            'created_at' => date('Y-m-d H:i:s'),
            'status' => $mapped_status,
        ];

        if ($tx_data['amount'] >= $invoice_data['total_price'] && $tx_data['status'] !== 'failed') {
            db_add_row('transactions', $tx_data);
            $invoice_status = tx_status_to_invoice($tx_data['status']);
            db_update_row('invoices', ['id' => $invoice_id], ['status' => $invoice_status, 'paid_at' => date('Y-m-d H:i:s')]);

            if ($notifications = process_order($invoice_data)) {
                foreach ($notifications as $note) {
                    db_add_row('notifications', $note);
                }
                if (function_exists('trigger_notification_sender_async')) {
                    trigger_notification_sender_async();
                }
            }
        }

        http_response_code(200);

    } catch (\Throwable $e) {
        file_put_contents('webhook_debug.log', "Exception:\n" . $e->getMessage(), FILE_APPEND);
        http_response_code(400);
        echo 'Webhook error: ' . $e->getMessage();
    }
});

// =========================
// Public Giveaway Page
// =========================
$router->get('/giveaway', function () {
    global $db;

    $gw = function_exists('giveaway_get_active') ? giveaway_get_active() : false;

    $leaderboard = [];
    $winners = [];

    if (!empty($gw)) {
        $gid = (int)$gw['id'];
        $leaderboard = $db->run(
            "SELECT gt.client_id, gt.tickets, c.username, c.icon
             FROM giveaway_tickets gt
             JOIN clients c ON c.id = gt.client_id
             WHERE gt.giveaway_id = {$gid} AND gt.tickets > 0
             ORDER BY gt.tickets DESC, gt.updated_at ASC
             LIMIT 100"
        ) ?: [];
    }

    // Winners of the last drawn giveaway (if any)
    $last_drawn_rows = $db->run("SELECT id, title, drawn_at FROM giveaways WHERE status='DRAWN' ORDER BY drawn_at DESC, id DESC LIMIT 1") ?: [];
    $last_drawn = (!empty($last_drawn_rows) && is_array($last_drawn_rows)) ? $last_drawn_rows[0] : null;

    if (!empty($last_drawn)) {
        $lid = (int)$last_drawn['id'];
        $winners = $db->run(
            "SELECT gw.rank, gw.client_id, gw.tickets_at_draw, c.username, c.icon
             FROM giveaway_winners gw
             JOIN clients c ON c.id = gw.client_id
             WHERE gw.giveaway_id = {$lid}
             ORDER BY gw.rank ASC"
        ) ?: [];
    }

    $meta = [
        'title' => 'Giveaway',
        'description' => 'Earn 1 ticket with every paid purchase. More tickets = higher chance to win.',
        'keywords' => 'giveaway, tickets, leaderboard',
    ];

    view_file('website/pages/giveaway', [
        'meta' => $meta,
        'giveaway' => $gw,
        'leaderboard' => $leaderboard,
        'winners' => $winners,
    ]);
});

$router->group('profile', function ($router) {
    $router->get('giveaway', function () {
            global $is_client, $db;
            if (!$is_client) {
                redirect_url('');
            }

            $gw = function_exists('giveaway_get_active') ? giveaway_get_active() : false;
            $my_tickets = 0;
            $leaderboard = [];

            if (!empty($gw)) {
                $gid = (int)$gw['id'];

                $row = db_get_row('giveaway_tickets', ['giveaway_id' => $gid, 'client_id' => CLIENT_ID]);
                $my_tickets = !empty($row) ? (int)$row['tickets'] : 0;

                // IMPORTANT: do NOT use $db->rows() or db_get_rows() operators like tickets[>]
                // because they generate invalid SQL in this project.
                $leaderboard = $db->run(
                    "SELECT gt.client_id, gt.tickets, c.username, c.icon
                     FROM giveaway_tickets gt
                     JOIN clients c ON c.id = gt.client_id
                     WHERE gt.giveaway_id = {$gid} AND gt.tickets > 0
                     ORDER BY gt.tickets DESC, gt.updated_at ASC
                     LIMIT 100"
                ) ?: [];
            }

            $meta = [
                'title' => 'Giveaway',
                'description' => 'View your giveaway tickets and the leaderboard.',
                'keywords' => 'giveaway, tickets, leaderboard',
            ];

            view_file('client/pages/giveaway', [
                'meta' => $meta,
                'giveaway' => $gw,
                'my_tickets' => $my_tickets,
                'leaderboard' => $leaderboard,
            ]);
        });

    $router->get('orders', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $orders = db_get_rows('orders', ['client_id' => CLIENT_ID], 1);
            $orders = is_array($orders) ? $orders : [];
            $__boosters_rows = db_get_rows('boosters', ['select' => 'id,username,icon']);
            $__boosters_rows = is_array($__boosters_rows) ? $__boosters_rows : [];
            $boosters = array_column(
                $__boosters_rows,
                null,
                'id'
            );
            // get order options and boost_form data and merge it with order data
            foreach ($orders as $k => $order) {
                $order_opts = db_get_row('order_options', ['order_id' => $order['id']]);
                // Customer snapshot: if exists, customer should see the snapshot values (original or last-synced)
                $snap = db_get_row('order_original_data', ['order_id' => $order['id']], 1);
                if (!empty($snap)) {
                    $snap_order = json_decode((!empty($snap['customer_orders_json']) ? $snap['customer_orders_json'] : ($snap['orders_json'] ?? '')), true) ?: [];
                    $snap_opts  = json_decode((!empty($snap['customer_options_json']) ? $snap['customer_options_json'] : ($snap['options_json'] ?? '')), true) ?: [];
                    if (isset($snap_order['form_id'])) { $order['form_id'] = $snap_order['form_id']; }
                    if (isset($snap_order['price'])) { $order['price'] = $snap_order['price']; }
                    if (isset($snap_order['price_eur'])) { $order['price_eur'] = $snap_order['price_eur']; }
                    if (is_array($order_opts)) { $order_opts = array_merge($order_opts, $snap_opts); }
                    else { $order_opts = $snap_opts; }
                }
                $form = db_get_row('boost_forms', ['id' => $order['form_id']]);
                $orders[$k] = array_merge(lb_safe_array($form), lb_safe_array($order_opts), lb_safe_array($order));
                if (isset($order['booster_id']) && isset($boosters[$order['booster_id']])) {
                    $orders[$k]['booster'] = $boosters[$order['booster_id']];
                }
            }
            $meta = [
                'title' => 'My Orders | LoLBoost',
                'h1' => 'Orders List',
                'description' => 'All the orders you have placed.',
                'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
            ];
            // ── Merge selling item purchases into normal client orders ─────────────
            global $db;
            $item_rows = $db->run(
                "SELECT
                    sip.id              AS item_order_id,
                    sip.client_id,
                    sip.status,
                    sip.price,
                    sip.unit_price,
                    sip.quantity,
                    sip.currency,
                    sip.created_at,
                    sip.invoice_id,
                    si.title            AS item_title,
                    si.type             AS item_type,
                    si.server           AS item_server,
                    si.images           AS item_images,
                    si.game_id          AS item_game_id,
                    si.game             AS item_game_slug,
                    si.item_data        AS item_data,
                    g.name              AS item_game_name,
                    g.slug              AS game_slug,
                    g.icon              AS game_icon,
                    s.username          AS seller_username,
                    s.icon              AS seller_icon,
                    inv.coins_used      AS coins_used
                 FROM selling_item_purchases sip
                 LEFT JOIN selling_items si ON si.id = sip.item_id
                 LEFT JOIN games g ON g.id = si.game_id
                 LEFT JOIN sellers s ON s.id = sip.seller_id
                 LEFT JOIN invoices inv ON inv.id = sip.invoice_id
                 WHERE sip.client_id = ?",
                (int) CLIENT_ID
            ) ?? [];

            foreach ($item_rows as &$it) {
                $it['is_item_order'] = true;
                $it['id'] = 'item_' . $it['item_order_id'];
                $it['game'] = $it['game_slug'] ?: ($it['item_game_slug'] ?: 'league-of-legends');
                $it['game_name'] = $it['item_game_name'] ?: ucwords(str_replace('-', ' ', (string)$it['game']));
                $it['title'] = $it['item_title'] ?: ('Digital Good Order #' . (int)$it['item_order_id']);
                $it['type'] = 'item';
                $it['name'] = 'Item';
                $it['price_eur'] = (int)($it['price'] ?? 0);
                $it['price'] = (int)($it['price'] ?? 0);
            }
            unset($it);

            $orders = array_merge($orders ?? [], $item_rows);

            // ── Merge purchased account orders into $orders ──────────────────
            // Important: keep this in sync with /profile/accounts.
            // Do not use a large joined SQL here, because older installs can miss columns
            // like sold_at, game_id or currency. /profile/accounts works because it reads
            // the raw account rows first and enriches them safely afterwards.
            try {
                $invoiceLookup = function (int $clientId, int $orderId, array $types) use ($db) {
                    if ($clientId <= 0 || $orderId <= 0 || empty($types)) {
                        return [];
                    }
                    $placeholders = implode(',', array_fill(0, count($types), '?'));
                    $params = array_merge([$clientId, $orderId], array_map('strtolower', $types));
                    try {
                        return $db->row(
                            "SELECT coins_used, currency, total_price
                             FROM invoices
                             WHERE client_id = ?
                               AND order_id = ?
                               AND LOWER(order_type) IN ($placeholders)
                             ORDER BY id DESC
                             LIMIT 1",
                            ...$params
                        ) ?: [];
                    } catch (Throwable $e) {
                        return [];
                    }
                };

                $marketplace_account_rows = db_get_rows('selling_accounts', ['client_id' => CLIENT_ID, 'sold' => 1], 1) ?: [];
                foreach ($marketplace_account_rows as &$acc) {
                    $accountId = (int)($acc['id'] ?? 0);
                    $invoice = $invoiceLookup((int)CLIENT_ID, $accountId, ['lol_account', 'selling_account']);

                    $seller = [];
                    if (!empty($acc['seller_id'])) {
                        $seller = db_get_row('sellers', ['id' => (int)$acc['seller_id'], 'select' => 'id,username,icon'], 1) ?: [];
                    }

                    $gameName = trim((string)($acc['game_name'] ?? ''));
                    if ($gameName === '') {
                        $gameRaw = trim((string)($acc['game'] ?? 'Account'));
                        $gameName = ucwords(str_replace(['-', '_'], ' ', $gameRaw ?: 'Account'));
                    }

                    $acc['is_marketplace_account_order'] = true;
                    $acc['account_order_id'] = $accountId;
                    $acc['selling_account_id'] = $accountId;
                    $acc['type'] = 'selling_account';
                    $acc['name'] = 'Selling Account';
                    $acc['status'] = 'PAID';
                    $acc['account_title'] = (string)($acc['title'] ?? ('Account #' . $accountId));
                    $acc['game_name'] = $gameName;
                    $acc['account_game_name'] = $gameName;
                    $acc['seller_username'] = $seller['username'] ?? null;
                    $acc['seller_icon'] = $seller['icon'] ?? null;
                    $acc['created_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['updated_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['currency'] = (string)($invoice['currency'] ?? $acc['currency'] ?? 'EUR');
                    $acc['coins_used'] = $invoice['coins_used'] ?? ($acc['coins_used'] ?? null);
                    $acc['price'] = (int)($acc['price'] ?? $invoice['total_price'] ?? 0);
                    $acc['price_eur'] = (int)($acc['price_eur'] ?? $acc['price'] ?? $invoice['total_price'] ?? 0);
                }
                unset($acc);

                $premium_account_rows = db_get_rows('accounts', ['client_id' => CLIENT_ID, 'status' => 1], 1) ?: [];
                foreach ($premium_account_rows as &$acc) {
                    $accountId = (int)($acc['id'] ?? 0);
                    $packageId = (int)($acc['package_id'] ?? 0);
                    $package = $packageId > 0 ? (db_get_row('account_packages', ['id' => $packageId], 1) ?: []) : [];
                    $invoice = $invoiceLookup((int)CLIENT_ID, $packageId, ['account', 'premium_account']);

                    $acc['is_premium_account_order'] = true;
                    $acc['premium_account_id'] = $accountId;
                    $acc['account_order_id'] = $accountId;
                    $acc['type'] = 'account';
                    $acc['name'] = 'Account';
                    $acc['status'] = 'PAID';
                    $acc['package_name'] = (string)($package['name'] ?? $acc['login'] ?? ('Account #' . $accountId));
                    $acc['title'] = $acc['package_name'];
                    $acc['package_server'] = (string)($package['server'] ?? $acc['server'] ?? '');
                    $acc['game_name'] = 'League of Legends';
                    $acc['created_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['updated_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['currency'] = (string)($invoice['currency'] ?? $acc['currency'] ?? 'EUR');
                    $acc['coins_used'] = $invoice['coins_used'] ?? ($acc['coins_used'] ?? null);
                    $acc['price'] = (int)($package['price'] ?? $acc['price'] ?? $invoice['total_price'] ?? 0);
                    $acc['price_eur'] = (int)($package['price'] ?? $acc['price_eur'] ?? $acc['price'] ?? $invoice['total_price'] ?? 0);
                }
                unset($acc);

                $orders = array_merge($orders ?? [], $marketplace_account_rows, $premium_account_rows);
            } catch (Throwable $e) {}
            // ── End account merge ───────────────────────────────────────────



            // ── Merge top_up purchases into $orders ─────────────────────────
            try {
                if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
                $topup_rows = $db->run(
                    "SELECT
                        p.id AS topup_order_id,
                        p.client_id,
                        p.seller_id,
                        p.status,
                        p.price,
                        p.currency,
                        p.created_at,
                        p.invoice_id,
                        p.game_name,
                        p.game_slug,
                        p.offer_title,
                        p.offer_amount,
                        p.offer_unit,
                        p.region,
                        p.platform,
                        p.quantity,
                        p.waiting_time_value,
                        p.waiting_time_unit,
                        p.waiting_time_minutes,
                        g.icon AS game_icon,
                        s.username AS seller_username,
                        s.icon AS seller_icon,
                        inv.coins_used AS coins_used
                     FROM selling_topup_purchases p
                     LEFT JOIN games g ON g.id = p.game_id
                     LEFT JOIN sellers s ON s.id = p.seller_id
                     LEFT JOIN invoices inv ON inv.id = p.invoice_id
                     WHERE p.client_id = ?",
                    (int)CLIENT_ID
                ) ?: [];
                foreach ($topup_rows as &$tu) {
                    $tu['is_topup_order'] = true;
                    $tu['id'] = 'topup_' . (int)$tu['topup_order_id'];
                    $tu['title'] = (string)($tu['offer_title'] ?? 'Top Up');
                    $tu['type'] = 'topup';
                    $tu['name'] = 'Top Up';
                    $tu['price_eur'] = (int)($tu['price'] ?? 0);
                }
                unset($tu);
                $orders = array_merge($orders ?? [], $topup_rows);
            } catch (Throwable $e) {}
            // ── End top_up merge ─────────────────────────────────────────────



// ── Merge egirl_orders into $orders ──────────────────────────────
            global $db;
            $egirl_rows = $db->run(
                "SELECT
                    eo.id              AS egirl_order_id,
                    eo.client_id,
                    eo.status,
                    eo.price,
                    eo.price_eur,
                    eo.currency,
                    eo.created_at,
                    eo.invoice_id,
                    eo.service_title,
                    eo.game,
                    b.username         AS egirl_username,
                    b.icon             AS egirl_icon,
                    es.includes_voice  AS includes_voice,
                    inv.coins_used     AS coins_used
                 FROM egirl_orders eo
                 LEFT JOIN boosters b         ON b.id  = eo.egirl_id
                 LEFT JOIN egirl_services es  ON es.id = eo.service_id
                 LEFT JOIN invoices inv       ON inv.id = eo.invoice_id
                 WHERE eo.client_id = ?",
                (int) CLIENT_ID
            ) ?? [];

            foreach ($egirl_rows as &$eg) {
                $eg['is_egirl'] = true;
                $eg['id']       = 'eg_' . $eg['egirl_order_id'];
            }
            unset($eg);

            $orders = array_merge($orders ?? [], $egirl_rows);

            // Re-sort newest first
            usort($orders, function ($a, $b) {
                return strtotime((string)($b['created_at'] ?? '2000-01-01'))
                     - strtotime((string)($a['created_at'] ?? '2000-01-01'));
            });
            // ── End egirl merge ───────────────────────────────────────────────

            view_file('client/pages/orders/list', ['orders' => $orders, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });


    $router->get('billing', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $payments = db_get_rows('transactions', ['client_id' => CLIENT_ID], 1);
            $meta = [
                'title' => 'Billing | LoLBoost',
                'h1' => 'Billing',
                'description' => 'All the transactions you made on the website.',
                'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
            ];
            view_file('client/pages/payments', ['payments' => $payments, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });

    $router->get('accounts', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $accounts = db_get_rows('accounts', ['client_id' => CLIENT_ID, 'status' => 1], 1);
            $lol_accounts = db_get_rows('selling_accounts', ['client_id' => CLIENT_ID, 'sold' => 1], 1);

            // get package data for each account
            foreach ($accounts as $k => $account) {
                $package = db_get_row('account_packages', ['id' => $account['package_id']]);
                $accounts[$k] = array_merge($package, $account);
            }
            $meta = [
                'title' => 'My Accounts | LoLBoost',
                'h1' => 'LoL Accounts',
                'description' => 'View and manage all your purchased game accounts on LoLBoost.',
                'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
            ];
            view_file('client/pages/accounts', ['accounts' => $accounts, 'lol_accounts' => $lol_accounts, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });

    $router->get('items', function () {
        global $is_client, $db;
        if (!$is_client) {
            redirect_url('');
            return;
        }

        $items = $db->run(
            "SELECT sip.*, si.title AS item_title, si.type, si.server, si.images,
                    si.price AS item_price, si.description AS item_description,
                    si.game_id AS item_game_id, si.game AS item_game_slug, si.item_data,
                    g.name AS item_game_name, g.slug AS game_slug, g.icon AS game_icon,
                    s.username AS seller_username
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN games g ON g.id = si.game_id
             LEFT JOIN sellers s ON s.id = sip.seller_id
             WHERE sip.client_id = ?
             ORDER BY sip.created_at DESC",
            (int) CLIENT_ID
        ) ?: [];

        $meta = [
            'title' => 'My Items | LoLBoost',
            'h1' => 'My Items',
            'description' => 'View and manage all your purchased game items on LoLBoost.',
            'keywords' => 'my items, purchased items, LoLBoost',
        ];

        view_file('client/pages/items/list', [
            'items' => $items,
            'meta' => $meta,
        ]);
    });

    $router->get('item/:id', function ($id) {
        global $is_client, $db;
        if (!$is_client) {
            redirect_url('');
            return;
        }

        $id = (int) $id;
        $purchase = $db->row(
            "SELECT sip.*, si.title AS item_title, si.type, si.server, si.images,
                    si.price AS item_price, si.description AS item_description,
                    si.requires_friendship_days, si.game_id AS item_game_id,
                    si.game AS item_game_slug, si.item_data,
                    g.name AS item_game_name, g.slug AS game_slug, g.icon AS game_icon,
                    sip.id AS purchase_id
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN games g ON g.id = si.game_id
             WHERE sip.id = ? AND sip.client_id = ?
             LIMIT 1",
            $id,
            (int) CLIENT_ID
        );

        if (empty($purchase)) {
            redirect_url('profile/items');
            return;
        }

        $seller = null;
        if (!empty($purchase['seller_id'])) {
            $seller = $db->row(
                "SELECT id, username, email, icon FROM sellers WHERE id = ? LIMIT 1",
                (int) $purchase['seller_id']
            );
        }

        $details = db_get_row('selling_item_purchase_details', ['purchase_id' => $id], 1) ?: [];
        $chat_messages = [];
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_item_purchase_' . $id) . '.json';
        if (is_file($chat_path)) {
            $raw = file_get_contents($chat_path);
            $chat_data = json_decode($raw, true);
            if (is_array($chat_data) && isset($chat_data['messages'])) {
                $chat_messages = array_values(array_filter($chat_data['messages'], fn($m) => empty($m['deleted'])));
            }
        }

        $remaining = !empty($purchase['friendship_ready_at']) ? (strtotime($purchase['friendship_ready_at']) - time()) : null;

        $can_review = false;
        $already_reviewed = false;
        if (!empty($purchase['seller_id'])) {
            $existing_rv = $db->row(
                "SELECT id FROM seller_reviews WHERE seller_id = ? AND client_id = ? LIMIT 1",
                (int)$purchase['seller_id'], (int)CLIENT_ID
            );
            $can_review       = true;
            $already_reviewed = !empty($existing_rv);
        }

        $meta = [
            'title' => item_meta_display_text($purchase['title'] ?? null, 'Digital Good Order #' . $id) . ' | LoLBoost',
            'h1' => 'Digital Good Order #' . $id,
            'description' => 'View your purchased item details and chat with the seller.',
        ];

        view_file('client/pages/items/view', [
            'purchase'         => $purchase,
            'seller'           => $seller,
            'details'          => $details,
            'chat_messages'    => $chat_messages,
            'remaining'        => $remaining,
            'meta'             => $meta,
            'can_review'       => $can_review,
            'already_reviewed' => $already_reviewed,
        ]);
    });



    $router->get('top-ups', function () {
        global $is_client, $db;
        if (!$is_client) { redirect_url(''); return; }
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        try {
            $topups = $db->run(
                "SELECT p.*, st.image, st.instructions,
                        g.name AS db_game_name, g.slug AS db_game_slug, g.icon AS game_icon,
                        s.username AS seller_username, s.icon AS seller_icon
                 FROM selling_topup_purchases p
                 LEFT JOIN selling_topups st ON st.id = p.topup_id
                 LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                 LEFT JOIN sellers s ON s.id = p.seller_id
                 WHERE p.client_id = ?
                 ORDER BY p.created_at DESC, p.id DESC",
                (int)CLIENT_ID
            ) ?: [];
        } catch (Throwable $e) { $topups = []; }
        $meta = ['title' => 'My Top Ups | LoLBoost', 'h1' => 'My Top Ups'];
        view_file('client/pages/topups/list', compact('meta', 'topups'));
    });

    $router->get('top-up/:id', function ($id) {
        global $is_client, $db;
        if (!$is_client) { redirect_url(''); return; }
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        $id = (int)$id;
        try {
            $purchase = $db->row(
                "SELECT p.*, st.image, st.instructions,
                        g.name AS db_game_name, g.slug AS db_game_slug, g.icon AS game_icon,
                        s.username AS seller_username, s.email AS seller_email, s.icon AS seller_icon
                 FROM selling_topup_purchases p
                 LEFT JOIN selling_topups st ON st.id = p.topup_id
                 LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                 LEFT JOIN sellers s ON s.id = p.seller_id
                 WHERE p.id = ? AND p.client_id = ?
                 LIMIT 1",
                $id, (int)CLIENT_ID
            );
        } catch (Throwable $e) { $purchase = null; }
        if (empty($purchase)) { redirect_url('profile/top-ups'); return; }
        $checkoutData = [];
        $raw = (string)($purchase['checkout_data'] ?? '');
        if ($raw !== '') { $decoded = json_decode($raw, true); if (is_array($decoded)) $checkoutData = $decoded; }
        $meta = ['title' => 'Top Up Order #' . $id . ' | LoLBoost', 'h1' => 'Top Up Order #' . $id];
        view_file('client/pages/topups/view', compact('meta', 'purchase', 'checkoutData'));
    });

    $router->get('settings', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $meta = [
                'title' => 'Account Settings | LoLBoost',
                'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
            ];
            view_file('client/pages/settings', ['meta' => $meta]);
        } else {
            redirect_url('');
        }
    });
    $router->get('overview', function () {
        global $is_client, $db;

        if ($is_client) {
            $client_id = CLIENT_ID;
            $client_data = $db->row('SELECT * FROM clients WHERE id = ?', $client_id);
            $client_data = is_array($client_data) ? $client_data : [];
            $orders = db_get_rows('orders', ['client_id' => $client_id, 'status' => ['n' => 'UNPAID'], 'order' => 'created_at,DESC']);
            $unpaid_orders = db_get_rows('orders', ['client_id' => $client_id, 'status' => 'UNPAID', 'order' => 'created_at,DESC']);
            $unpaid_orders = is_array($unpaid_orders) ? $unpaid_orders : [];
$total_spent = 0;
            $current_rank = null;
            $next_rank = null;
            $progress = 0;

            if (!empty($orders)) {
                foreach ($orders as $k => $order) {
                    $order_opts = db_get_row('order_options', ['order_id' => $order['id']]);
                    $form = db_get_row('boost_forms', ['id' => $order['form_id']]);
                    $orders[$k] = array_merge(lb_safe_array($form), lb_safe_array($order_opts), lb_safe_array($order));
                }

                $total_spent = array_sum(array_column($orders, 'price')) / 100;
            }


            if (!empty($unpaid_orders)) {
                foreach ($unpaid_orders as $k => $order) {
                    $order_opts = db_get_row('order_options', ['order_id' => $order['id']]);
                    $form = db_get_row('boost_forms', ['id' => $order['form_id']]);
                    $unpaid_orders[$k] = array_merge(lb_safe_array($form), lb_safe_array($order_opts), lb_safe_array($order));
                }
            }
// Top 15 Boosters with most completed orders (same query logic as website)
$top_boosters = [];
try {
    $query = "
        SELECT 
            boosters.*, 
            booster_ranks.name as rank_name, 
            booster_profiles.*, 
            boosters.id as booster_id,
            COUNT(orders.id) as completed_orders
        FROM boosters
        INNER JOIN booster_profiles 
            ON boosters.id = booster_profiles.booster_id
        LEFT JOIN booster_ranks 
            ON boosters.rank_id = booster_ranks.id
        LEFT JOIN orders 
            ON boosters.id = orders.booster_id AND orders.status = 'COMPLETED'
        WHERE 
            boosters.is_banned = 0 
            AND (boosters.is_egirl IS NULL OR boosters.is_egirl = 0)
            AND booster_profiles.champions IS NOT NULL 
            AND booster_profiles.roles IS NOT NULL
            AND boosters.show_profile = 1
        GROUP BY boosters.id
        ORDER BY completed_orders DESC, boosters.id ASC
        LIMIT 50";
    $top_boosters = $db->run($query);
} catch (Throwable $e) {
    $top_boosters = [];
}



            $loyalty_ranks = db_get_rows('loyalty_ranks', ['order' => 'target_amount,ASC']);
            $loyalty_ranks = is_array($loyalty_ranks) ? $loyalty_ranks : [];

            $current_rank = null;
            $next_rank = null;
            foreach ($loyalty_ranks as $rank) {
                if ($rank['id'] == ($client_data['loyalty_rank_id'] ?? null)) {
                    $current_rank = $rank;
                } elseif ($rank['target_amount'] > $total_spent && (!$next_rank || $rank['target_amount'] < $next_rank['target_amount'])) {
                    $next_rank = $rank;
                }
            }

            $progress = $next_rank ? min(100, ($total_spent / $next_rank['target_amount']) * 100) : 100;

            $meta = [
                'title' => 'My Overview | LoLBoost',
                'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
            ];
            view_file('client/pages/overview', [
                'meta' => $meta,
                'orders' => $orders,
                'unpaid_orders' => $unpaid_orders,
                'client_data' => $client_data,
                'total_spent' => $total_spent,
                'current_rank' => $current_rank,
                'next_rank' => $next_rank,
                'progress' => $progress,
                'top_boosters' => $top_boosters,
            ]);
        } else {
            redirect_url('');
        }
    });

    $router->get('chat', function () {
        global $is_client, $db;
        if (!$is_client) { redirect_url(''); return; }

        $client_id = (int) CLIENT_ID;
        $chat_dir  = SYS_PATH . '/public/uploads/private/chat';
        $conversations = [];

        $read_chat_summary = function (string $chat_path) use ($client_id) {
            $out = ['exists' => is_file($chat_path), 'count' => 0, 'client_match' => false, 'last_body' => '', 'last_message_at' => 0, 'unread_client' => 0, 'data' => []];
            if (!$out['exists']) { return $out; }
            $data = json_decode(@file_get_contents($chat_path) ?: '', true);
            if (!is_array($data)) { return $out; }
            $out['data'] = $data;
            if ((int)($data['client_id'] ?? 0) === $client_id) { $out['client_match'] = true; }
            $messages = isset($data['messages']) && is_array($data['messages']) ? array_values($data['messages']) : [];

            $msg_order = function (array $m, int $index): int {
                if (!empty($m['time']) && is_numeric($m['time'])) { return (int)$m['time']; }
                if (!empty($m['created_at'])) {
                    $ts = strtotime((string)$m['created_at']);
                    if ($ts !== false) { return (int)$ts; }
                }
                return $index;
            };
            $msg_sender = function (array $m): string {
                $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? '')));
                $type = strtolower(trim((string)($m['type'] ?? '')));
                if ($sender === '' && in_array($type, ['client', 'seller', 'system'], true)) { $sender = $type; }
                return $sender;
            };

            $last_client_value = 0;
            foreach ($messages as $idx => $m) {
                if (!is_array($m) || !empty($m['deleted'])) { continue; }
                $sender = $msg_sender($m);
                if ($sender === 'client') {
                    $last_client_value = max($last_client_value, $msg_order($m, $idx + 1));
                    if ((int)($m['sender_id'] ?? 0) === $client_id) { $out['client_match'] = true; }
                }
            }

            foreach ($messages as $idx => $m) {
                if (!is_array($m) || !empty($m['deleted'])) { continue; }
                $out['count']++;
                $sender = $msg_sender($m);
                $ts = $msg_order($m, $idx + 1);
                if ($ts >= (int)$out['last_message_at']) {
                    $body = trim(strip_tags((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? $m['raw'] ?? '')));
                    if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image' || preg_match('/<img\\s/i', (string)($m['content'] ?? ''))) { $body = $body !== '' ? '[Image] ' . $body : '[Image]'; }
                    $out['last_body'] = $body;
                    $out['last_message_at'] = $ts;
                }
                if ($sender === 'seller') {
                    if (array_key_exists('seen_by_client', $m)) {
                        $seen_by_client = (int)$m['seen_by_client'];
                    } elseif (array_key_exists('seen', $m)) {
                        $seen_by_client = (int)$m['seen'];
                    } elseif (array_key_exists('is_read', $m)) {
                        $seen_by_client = (int)$m['is_read'];
                    } else {
                        $seen_by_client = 1;
                    }
                    if ($seen_by_client === 1) { continue; }
                    $message_value = $msg_order($m, $idx + 1);
                    if ($last_client_value > 0 && $message_value <= $last_client_value) { continue; }
                    $out['unread_client']++;
                }
            }
            return $out;
        };

        if (is_dir($chat_dir)) {
            foreach (glob($chat_dir . '/selling_*.json') ?: [] as $chat_path) {
                $sum = $read_chat_summary($chat_path);
                if (!$sum['exists'] || $sum['count'] < 1 || !$sum['client_match']) { continue; }
                $data = $sum['data'];
                $ref_type = (string)($data['ref_type'] ?? '');
                $account_id = (int)($data['account_id'] ?? 0);
                $purchase_id = (int)($data['purchase_id'] ?? 0);
                if ($ref_type === '' && $purchase_id > 0) { $ref_type = 'item_purchase'; }
                if ($ref_type === '' && $account_id > 0) { $ref_type = 'account'; }

                if ($ref_type === 'item_purchase' && $purchase_id > 0) {
                    $purchase = $db->row("SELECT sip.id, sip.item_id, sip.seller_id, sip.client_id, si.title AS item_title, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon FROM selling_item_purchases sip LEFT JOIN selling_items si ON si.id = sip.item_id LEFT JOIN sellers s ON s.id = sip.seller_id WHERE sip.id = ? AND sip.client_id = ? LIMIT 1", $purchase_id, $client_id);
                    if (empty($purchase)) { continue; }
                    $conversations[] = ['id'=>'item-'.$purchase_id,'kind'=>'item','kind_label'=>'DIGITAL GOOD','request_status'=>'paid','ref_type'=>'item_purchase','ref_id'=>$purchase_id,'seller_id'=>(int)($purchase['seller_id'] ?? 0),'seller_username'=>trim((string)($purchase['seller_username'] ?? '')) ?: 'Seller','seller_icon'=>(string)($purchase['seller_icon'] ?? ''),'title'=>$purchase['item_title'] ?? ('Digital Good Order #'.$purchase_id),'last_body'=>$sum['last_body'],'last_message_at'=>$sum['last_message_at'],'unread_client'=>$sum['unread_client'],'source_url'=>BASE_URL.'/profile/item/'.$purchase_id];
                    continue;
                }

                // Digital Goods seller chat, same inbox handling as account seller chat.
                $dg_id = 0;
                if ($ref_type === 'digital_good') {
                    $dg_id = (int)($data['ref_id'] ?? $data['digital_good_id'] ?? 0);
                }
                if ($ref_type === 'digital_good' && $dg_id > 0) {
                    $dg = $db->row("SELECT dg.id, dg.title, dg.slug, dg.seller_id, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active FROM digital_goods dg LEFT JOIN sellers s ON s.id = dg.seller_id WHERE dg.id = ? LIMIT 1", $dg_id);
                    if (empty($dg)) { continue; }
                    $dg_slug = trim((string)($dg['slug'] ?? ''));
                    $dg_url = BASE_URL . '/digital-good/' . rawurlencode($dg_slug !== '' ? $dg_slug : (string)$dg_id);
                    $conversations[] = ['id'=>'digital-good-'.$dg_id,'kind'=>'digital_good','kind_label'=>'DIGITAL GOOD','request_status'=>'request','ref_type'=>'digital_good','ref_id'=>$dg_id,'seller_id'=>(int)($dg['seller_id'] ?? ($data['seller_id'] ?? 0)),'seller_username'=>trim((string)($dg['seller_username'] ?? '')) ?: 'Seller','seller_icon'=>(string)($dg['seller_icon'] ?? ''),'title'=>$dg['title'] ?? ('Digital Good #'.$dg_id),'last_body'=>$sum['last_body'],'last_message_at'=>$sum['last_message_at'],'unread_client'=>$sum['unread_client'],'source_url'=>$dg_url];
                    continue;
                }

                if ($account_id <= 0) { continue; }
                $account = $db->row("SELECT a.id, a.title, a.seller_id, a.sold, a.client_id, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon FROM selling_accounts a LEFT JOIN sellers s ON s.id = a.seller_id WHERE a.id = ? LIMIT 1", $account_id);
                if (empty($account)) { continue; }
                $is_paid_order = ((int)($account['sold'] ?? 0) === 1 && (int)($account['client_id'] ?? 0) === $client_id);
                $conversations[] = ['id'=>'account-'.$account_id,'kind'=>'account','kind_label'=>$is_paid_order ? 'ORDER' : 'ACCOUNT REQUEST','request_status'=>$is_paid_order ? 'paid' : 'request','ref_type'=>'account','ref_id'=>$account_id,'seller_id'=>(int)($account['seller_id'] ?? ($data['seller_id'] ?? 0)),'seller_username'=>trim((string)($account['seller_username'] ?? '')) ?: 'Seller','seller_icon'=>(string)($account['seller_icon'] ?? ''),'title'=>$account['title'] ?? ('Account #'.$account_id),'last_body'=>$sum['last_body'],'last_message_at'=>$sum['last_message_at'],'unread_client'=>$sum['unread_client'],'source_url'=>$is_paid_order ? (BASE_URL.'/account/'.$account_id) : (BASE_URL.'/league-of-legends/accounts/'.$account_id)];
            }
        }

        // Include normal LoL boosting order chats in the initial /profile/chat render.
        // Realtime AJAX already refreshes them, but the route must seed them on first load.
        try {
            $orders = $db->run(
                "SELECT o.id, o.form_id, o.status, o.created_at, o.paid_at, o.booster_id,
                        b.username AS booster_username, b.icon AS booster_icon,
                        bf.name AS form_name, bf.type AS form_type
                 FROM orders o
                 LEFT JOIN boosters b ON b.id = o.booster_id
                 LEFT JOIN boost_forms bf ON bf.id = o.form_id
                 WHERE o.client_id = ?
                 ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC
                 LIMIT 150",
                $client_id
            ) ?: [];

            $normalize_order_messages = function ($raw): array {
                if (!is_array($raw)) { return []; }
                if (isset($raw['messages']) && is_array($raw['messages'])) { return array_values($raw['messages']); }
                return array_values($raw);
            };

            foreach ($orders as $order) {
                if (!is_array($order)) { continue; }
                $order_id = (int)($order['id'] ?? 0);
                if ($order_id <= 0) { continue; }

                $chat_path = $chat_dir . '/' . sha1((string)$order_id) . '.json';
                $messages = [];
                $has_chat_file = is_file($chat_path);

                if ($has_chat_file) {
                    $raw_chat = json_decode(@file_get_contents($chat_path) ?: '', true);
                    $messages = $normalize_order_messages($raw_chat);
                } elseif (function_exists('chat_load_messages')) {
                    $raw_chat = chat_load_messages($order_id);
                    $messages = $normalize_order_messages($raw_chat);
                }

                if (!$has_chat_file && empty($messages) && (int)($order['booster_id'] ?? 0) <= 0) { continue; }

                $last_body = 'No booster message yet';
                $last_message_at = 0;
                $unread_client = 0;

                foreach ($messages as $idx => $m) {
                    if (!is_array($m) || !empty($m['deleted'])) { continue; }
                    $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? '')));
                    $type = strtolower(trim((string)($m['type'] ?? '')));
                    if ($sender === '' && in_array($type, ['client', 'booster', 'admin', 'system'], true)) { $sender = $type; }

                    if (!empty($m['time']) && is_numeric($m['time'])) {
                        $ts = (int)$m['time'];
                    } elseif (!empty($m['created_at']) && strtotime((string)$m['created_at']) !== false) {
                        $ts = (int)strtotime((string)$m['created_at']);
                    } elseif (!empty($m['timestamp']) && is_numeric($m['timestamp'])) {
                        $ts = (int)$m['timestamp'];
                    } else {
                        $ts = $idx + 1;
                    }

                    if ($ts >= $last_message_at) {
                        $body = trim(strip_tags((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? $m['raw'] ?? '')));
                        if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image') { $body = $body !== '' ? '[Image] ' . $body : '[Image]'; }
                        if ($body === '') { $body = (($m['message_type'] ?? '') === 'system' || ($m['type'] ?? '') === 'system') ? 'System message' : 'No message yet'; }
                        $last_body = $body;
                        $last_message_at = $ts;
                    }

                    if ($sender === 'booster') {
                        if (array_key_exists('seen_by_client', $m)) { $is_unread = ((int)$m['seen_by_client'] === 0); }
                        elseif (array_key_exists('is_read', $m)) { $is_unread = ((int)$m['is_read'] === 0); }
                        elseif (array_key_exists('seen', $m)) { $is_unread = ((int)$m['seen'] === 0); }
                        else { $is_unread = false; }
                        if ($is_unread) { $unread_client++; }
                    }
                }

                if ($last_message_at <= 0) {
                    $base_ts = strtotime((string)($order['paid_at'] ?? $order['created_at'] ?? ''));
                    $last_message_at = $base_ts !== false ? (int)$base_ts : 0;
                }

                $booster_name = trim((string)($order['booster_username'] ?? ''));
                if ($booster_name === '') { $booster_name = ((int)($order['booster_id'] ?? 0) > 0) ? 'Booster' : 'Booster not assigned'; }

                $title = trim((string)($order['form_name'] ?? $order['form_type'] ?? ''));
                if ($title === '') { $title = 'Boosting Order #' . $order_id; }

                $conversations[] = [
                    'id' => 'booster-order-' . $order_id,
                    'kind' => 'booster_order',
                    'kind_label' => 'BOOSTER CHAT',
                    'chat_type' => 'booster',
                    'seller_id' => 0,
                    'seller_username' => $booster_name,
                    'seller_icon' => (string)($order['booster_icon'] ?? ''),
                    'request_status' => 'paid',
                    'ref_type' => 'booster_order',
                    'ref_id' => $order_id,
                    'title' => $title,
                    'last_body' => $last_body,
                    'last_message_at' => $last_message_at,
                    'unread_client' => $unread_client,
                    'source_url' => BASE_URL . '/profile/orders/' . $order_id,
                    'source_label' => 'View Order',
                ];
            }
        } catch (Throwable $e) {}


        $random_booster_chat_summary = function(string $chat_file) use ($client_id, $read_chat_summary) {
            $base = basename($chat_file, '.json');
            if (strpos($base, 'selling_') === 0) return null;
            $data = json_decode(@file_get_contents($chat_file) ?: '', true);
            if (!is_array($data)) return null;
            $messages = (isset($data['messages']) && is_array($data['messages'])) ? array_values($data['messages']) : array_values($data);
            if (empty($messages)) return null;
            $client_username = defined('CLIENT_DATA') && is_array(CLIENT_DATA) ? strtolower(trim((string)(CLIENT_DATA['username'] ?? ''))) : '';
            $belongs = ((int)($data['client_id'] ?? $data['user_id'] ?? 0) === $client_id);
            $senderOf = function(array $m): string { $s = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? ''))); $t = strtolower(trim((string)($m['type'] ?? ''))); if ($s === '' && in_array($t, ['client','booster','seller','admin','system'], true)) $s = $t; return $s; };
            $timeOf = function(array $m, int $i): int { if (!empty($m['time']) && is_numeric($m['time'])) return (int)$m['time']; if (!empty($m['created_at'])) { $ts = strtotime((string)$m['created_at']); if ($ts !== false) return (int)$ts; } if (!empty($m['timestamp']) && is_numeric($m['timestamp'])) return (int)$m['timestamp']; return $i; };
            $bodyOf = function(array $m): string { $body = trim(strip_tags((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? $m['raw'] ?? ''))); if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image') return $body !== '' ? '[Image] ' . $body : '[Image]'; return $body !== '' ? $body : 'No message yet'; };
            foreach ($messages as $m) { if (!is_array($m) || !empty($m['deleted']) || $senderOf($m) !== 'client') continue; $mid = (int)($m['sender_id'] ?? $m['client_id'] ?? $m['user_id'] ?? 0); $mname = strtolower(trim((string)($m['sender_name'] ?? $m['username'] ?? $m['name'] ?? ''))); if ($mid === $client_id || ($client_username !== '' && $mname !== '' && $mname === $client_username)) { $belongs = true; break; } }
            if (!$belongs) return null;
            $last = null; $last_at = 0; $unread = 0;
            foreach ($messages as $i => $m) { if (!is_array($m) || !empty($m['deleted'])) continue; $sender=$senderOf($m); $t=$timeOf($m,$i+1); if ($t >= $last_at) { $last_at=$t; $last=$m; } if (in_array($sender, ['booster','admin','seller'], true)) { if (array_key_exists('seen_by_client',$m)) $is_unread=((int)$m['seen_by_client']===0); elseif (array_key_exists('is_read',$m)) $is_unread=((int)$m['is_read']===0); elseif (array_key_exists('seen',$m)) $is_unread=((int)$m['seen']===0); else $is_unread=false; if ($is_unread) $unread++; } }
            return ['chat_key'=>$base, 'order_id'=>(int)($data['order_id'] ?? $data['ref_id'] ?? $data['id'] ?? 0), 'last'=>$last, 'last_body'=>$last ? $bodyOf($last) : 'No booster message yet', 'last_at'=>$last_at, 'unread'=>$unread];
        };
        if (is_dir($chat_dir)) {
            $existing = [];
            foreach ($conversations as $c) { if (($c['chat_type'] ?? '') === 'booster' || ($c['ref_type'] ?? '') === 'booster_order') { $existing[(string)($c['ref_id'] ?? 0)] = true; if (!empty($c['chat_key'])) $existing[(string)$c['chat_key']] = true; } }
            foreach (glob($chat_dir . '/*.json') ?: [] as $chat_file) {
                $sum = $random_booster_chat_summary($chat_file);
                if (!$sum) continue;
                $order_id = (int)$sum['order_id']; $chat_key = (string)$sum['chat_key'];
                if (($order_id > 0 && isset($existing[(string)$order_id])) || isset($existing[$chat_key])) continue;
                $order = null; $booster_name = 'Booster Chat'; $booster_icon = ''; $title = 'Boosting Chat'; $source_url = '';
                if ($order_id > 0) { $order = $db->row("SELECT o.id, o.client_id, o.form_id, o.status, o.created_at, o.paid_at, o.booster_id, b.username AS booster_username, b.icon AS booster_icon, bf.name AS form_name, bf.type AS form_type FROM orders o LEFT JOIN boosters b ON b.id=o.booster_id LEFT JOIN boost_forms bf ON bf.id=o.form_id WHERE o.id=? AND o.client_id=? LIMIT 1", $order_id, $client_id); }
                if (!empty($order)) { $booster_name = trim((string)($order['booster_username'] ?? '')) ?: (((int)($order['booster_id'] ?? 0)>0) ? 'Booster' : 'Booster not assigned'); $booster_icon=(string)($order['booster_icon'] ?? ''); $title=trim((string)($order['form_name'] ?? $order['form_type'] ?? '')) ?: ('Boosting Order #' . $order_id); $source_url=BASE_URL . '/profile/orders/' . $order_id; }
                $conversations[] = ['id'=>'booster-raw-'.$chat_key,'kind'=>'booster_order','kind_label'=>'BOOSTER CHAT','chat_type'=>'booster','seller_id'=>0,'seller_username'=>$booster_name,'seller_icon'=>$booster_icon,'request_status'=>'paid','ref_type'=>'booster_order','ref_id'=>$order_id,'chat_key'=>$chat_key,'title'=>$title,'last_body'=>$sum['last_body'],'last_message_at'=>(int)$sum['last_at'],'unread_client'=>(int)$sum['unread'],'source_url'=>$source_url,'source_label'=>$order_id > 0 ? 'View Order' : 'Boosting Chat'];
            }
        }

        usort($conversations, function ($a, $b) { return ((int)($b['last_message_at'] ?? 0)) <=> ((int)($a['last_message_at'] ?? 0)); });
        $meta = ['title' => 'My Chats | LoLBoost'];
        view_file('client/pages/chat/inbox', compact('meta', 'conversations'));
    });

    $router->get('coins-history', function () {
        global $is_client;
        global $db;

        if ($is_client) {
            $query = 'SELECT id, type, reason, amount, created_at
                        FROM coins_history
                        WHERE client_id = ?
                        ORDER BY id DESC';

            $data = $db->run($query, CLIENT_ID);

            $meta = [
                'title' => 'Coins History | LoLBoost',
                'h1' => 'Coins History',
                'description' => 'View your loyalty coins history and transactions on LoLBoost.',
                'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
            ];

            view_file('client/pages/coins-history', ['data' => $data, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });
});
$router->get('order/:id', function ($order_id) {
    // check if is_client = true
    global $is_client;
    global $db;

    if ($is_client) {
        $order_id = intval($order_id);
        $order = db_get_row('orders', ['client_id' => CLIENT_ID, 'id' => $order_id], 1);
        if (empty($order)) {
            redirect_url('profile/orders');
        }

        // Live data (status, booster assignment, etc.) should stay live,
        // but the customer-facing "order details" should come from the snapshot if it exists.
        $order_opts = db_get_row('order_options', ['order_id' => $order['id']]) ?: [];
        $order_acc  = db_get_row('order_accounts', ['order_id' => $order['id']], 1);

        $display_order = $order;
        $display_opts  = $order_opts;
        $display_form_id = $order['form_id'];

        $snap = db_get_row('order_original_data', ['order_id' => $order['id']], 1);
        if (!empty($snap)) {
            $snap_order = json_decode((!empty($snap['customer_orders_json']) ? $snap['customer_orders_json'] : ($snap['orders_json'] ?? '')), true) ?: [];
            $snap_opts  = json_decode((!empty($snap['customer_options_json']) ? $snap['customer_options_json'] : ($snap['options_json'] ?? '')), true) ?: [];

            if (isset($snap_order['form_id'])) {
                $display_form_id = $snap_order['form_id'];
                $display_order['form_id'] = $snap_order['form_id'];
            }

            // Only override customer-facing fields (keep status/booster/etc. from live order)
            foreach (['price','price_eur','currency'] as $k) {
                if (array_key_exists($k, $snap_order)) {
                    $display_order[$k] = $snap_order[$k];
                }
            }

            // Snapshot options should win over current options
            if (is_array($snap_opts) && !empty($snap_opts)) {
                $display_opts = array_merge($display_opts, $snap_opts);
            }
        }

        $form  = db_get_row('boost_forms', ['id' => $display_form_id]);
        $notes = db_get_rows('order_notes', ['order_id' => $order['id'], 'type' => 'client']);

        $order = array_merge($form, $display_opts, (array) $order_acc, $display_order, ['notes' => $notes]);

        $invoice = db_get_row('invoices', ['order_id' => $order['id'], 'order_type' => 'order'], 1);
        $meta = [
            'title' => 'Order #' . $order['id'] . ' | LoLBoost',
            'h1' => 'Order #' . $order['id'],
            'description' => 'View and interract with your order | Chat with your booster | Tip your booster.',
            'keywords' => 'LoLBoost, game boosting, buy game accounts, gaming marketplace',
        ];

        $addons = get_addable_addons_for_order($order);
        $result = $db->run("
            SELECT COALESCE(SUM(total_price), 0) AS total
            FROM invoices
            WHERE order_type = 'addon'
            AND order_id = ?
            AND status = 'PAID'
        ", $order['id']);

        $total_addon_price = (float) $result[0]['total'];
        $review = db_get_row('reviews', ['order_id' => $order['id']]);
        $order['progress'] = lb_order_progress_ensure_start_rank(
            (int) $order['id'],
            db_get_row('order_progress', ['order_id' => $order['id']], 1) ?: []
        );

        view_file('client/pages/orders/view', [
            'data' => $order,
            'meta' => $meta,
            'invoice' => $invoice,
            'addons' => $addons,
            'total_addon_price' => $total_addon_price,
            'review' => $review
        ]);
    } else {
        redirect_url('');
    }
});
$router->get('account/:id', function ($account_id) {
    global $is_client;
    if (!$is_client) {
        redirect_url('');
    }

    $id = (int)$account_id;
    $account = db_get_row('selling_accounts', ['id' => $id, 'client_id' => CLIENT_ID, 'sold' => 1], 1);
    if (empty($account)) {
        redirect_url('profile/accounts');
    }

    $seller = null;
    if (!empty($account['seller_id'])) {
        $seller = db_get_row('sellers', ['id' => (int)$account['seller_id']], 1);
    }

    $can_review = false;
    $already_reviewed = false;
    if (!empty($account['seller_id'])) {
        global $db;
        $existing_rv = $db->row(
            "SELECT id FROM seller_reviews WHERE seller_id = ? AND client_id = ? LIMIT 1",
            (int)$account['seller_id'], (int)CLIENT_ID
        );
        $can_review       = true;
        $already_reviewed = !empty($existing_rv);
    }

    $meta = [
        'title'       => htmlspecialchars($account['title'] ?? ('Account #S' . $id)) . ' | LoLBoost',
        'h1'          => 'Account Details',
        'description' => 'View your purchased account details and chat with the seller on LoLBoost.',
    ];

    view_file('client/pages/orders/account_view', [
        'account'          => $account,
        'seller'           => $seller,
        'meta'             => $meta,
        'can_review'       => $can_review,
        'already_reviewed' => $already_reviewed,
    ]);
});
$router->get('logout', function () {
    logout_all_sessions();
    redirect_url('');
});

$is_admin = defined('ADMIN_DATA') && !empty(ADMIN_DATA);

$router->group('admin-area', function () {
    global $router;
    $router->get('auth/login', function () {
        global $is_admin;
        if (!$is_admin) {
            view_file('admin/auth/login');
        } else {
            redirect_url('admin-area/dashboard');
        }
    });
    $router->get('auth/logout', function () {
        logout_all_sessions();
        redirect_url('');
    });
    $router->get('/', function () {
        redirect_url('admin-area/dashboard');
    });
    $router->get('dashboard', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de'
            ) {
                redirect_url('admin-area/orders');
            }

            view_file('admin/pages/dashboard');
        } else {
            redirect_url('admin-area/auth/login');
        }
    });


if (!function_exists('lb_admin_upload_game_icon')) {
    function lb_admin_upload_game_icon(string $slug, ?string $currentIcon = null): ?string
    {
        if (empty($_FILES['game_icon']) || !is_array($_FILES['game_icon'])) {
            return $currentIcon;
        }

        $file = $_FILES['game_icon'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $currentIcon;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return $currentIcon;
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return $currentIcon;
        }

        $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
        if (!in_array($ext, $allowed, true)) {
            return $currentIcon;
        }

        $isSvg = ($ext === 'svg');
        if (!$isSvg && @getimagesize($file['tmp_name']) === false) {
            return $currentIcon;
        }
        if ($isSvg) {
            $svg = @file_get_contents($file['tmp_name']);
            if ($svg === false || stripos($svg, '<svg') === false || preg_match('/<script|on\w+\s*=/i', $svg)) {
                return $currentIcon;
            }
        }

        $safeSlug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower(trim($slug)));
        $safeSlug = trim($safeSlug, '-') ?: 'game-icon';
        $filename = $safeSlug . '-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.' . $ext;

        $uploadDir = (defined('ROOT_PATH') ? rtrim(ROOT_PATH, '/') : dirname(__DIR__)) . '/public/assets/website/images/icons';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            return $currentIcon;
        }

        $target = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return $currentIcon;
        }
        @chmod($target, 0644);

        $assetBase = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '/public/assets';
        return $assetBase . '/website/images/icons/' . $filename;
    }
}

    // ── Games & Services Manager ─────────────────────────────────────────────
    $router->get('games', function () {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        global $db;
        $games = util_get_all_games(false);
        foreach ($games as &$g) {
            $g['services'] = util_get_game_services((int)$g['id']);
            $cnt = $db->run('SELECT COUNT(*) AS c FROM boost_forms WHERE game_id = ?', (int)$g['id']);
            $g['boost_form_count'] = $cnt[0]['c'] ?? 0;
        }
        unset($g);
        view_file('admin/pages/games/index', [
            'meta'          => ['title' => 'Games Manager | Admin | LoLBoost'],
            'games'         => $games,
            'service_types' => ['boosting', 'accounts', 'items', 'topups', 'coaching', 'egirl'],
        ]);
    });
    $router->get('games/create', function () {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        view_file('admin/pages/games/create', [
            'meta'          => ['title' => 'Add Game | Admin | LoLBoost'],
            'service_types' => ['boosting', 'accounts', 'items', 'topups', 'coaching', 'egirl'],
        ]);
    });
    $router->post('games/create', function () {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $services = $_POST['services'] ?? [];
        $slug = $_POST['slug'] ?? '';
        $gameIcon = lb_admin_upload_game_icon($slug, $_POST['icon'] ?? null);
        $gameId = admin_create_game([
            'name'          => $_POST['name']          ?? '',
            'slug'          => $slug,
            'short_code'    => $_POST['short_code']    ?? '',
            'color_primary' => $_POST['color_primary'] ?? '#8b5cf6',
            'color_accent'  => $_POST['color_accent']  ?? '#a78bfa',
            'icon'          => $gameIcon,
            'banner'        => $_POST['banner']        ?? null,
        ], $services);
        if ($gameId) {
            redirect_url('admin-area/games/' . $gameId . '/edit?created=1');
        } else {
            redirect_url('admin-area/games/create?error=duplicate');
        }
    });
    $router->get('games/:id/edit', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { redirect_url('admin-area/games'); return; }
        $services       = util_get_game_services((int)$id);
        $activeServices = array_column($services, 'service_type');
        $boostForms     = $db->run('SELECT * FROM boost_forms WHERE game_id = ? ORDER BY id ASC', (int)$id) ?: [];
        $accountSchema = [];
        try {
            $db->run("CREATE TABLE IF NOT EXISTS game_account_schemas (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                game_slug VARCHAR(100) NOT NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                schema_json JSON NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_game_account_schema (game_slug),
                KEY idx_enabled (enabled)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $schemaRows = $db->run('SELECT schema_json FROM game_account_schemas WHERE game_slug = ? LIMIT 1', $game['slug']);
            if (!empty($schemaRows[0]['schema_json'])) {
                $decodedSchema = json_decode((string)$schemaRows[0]['schema_json'], true);
                if (is_array($decodedSchema)) $accountSchema = $decodedSchema;
            } elseif (function_exists('util_get_game_account_schema')) {
                $accountSchema = util_get_game_account_schema($game['slug']);
            }
        } catch (Throwable $e) {
            if (function_exists('util_get_game_account_schema')) $accountSchema = util_get_game_account_schema($game['slug']);
        }
        view_file('admin/pages/games/edit', [
            'meta'           => ['title' => 'Edit ' . ($game['name'] ?? '') . ' | Admin | LoLBoost'],
            'game'           => $game,
            'services'       => $services,
            'activeServices' => $activeServices,
            'boostForms'     => $boostForms,
            'accountSchema'  => $accountSchema,
            'itemSchema'     => function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($game['slug']) : [],
            'itemsConfig'    => function_exists('lb_get_items_page_config') ? lb_get_items_page_config($game['slug']) : [],
            'service_types'  => ['boosting', 'accounts', 'items', 'topups', 'coaching', 'egirl'],
        ]);
    });
    $router->post('games/:id/update', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { redirect_url('admin-area/games'); return; }
        $gameIcon = lb_admin_upload_game_icon($game['slug'] ?? '', $game['icon'] ?? null);
        $db->run(
            'UPDATE games SET name=?, short_code=?, icon=?, color_primary=?, color_accent=?, status=?, sort_order=?, updated_at=NOW() WHERE id=?',
            $_POST['name']          ?? '',
            $_POST['short_code']    ?? '',
            $gameIcon,
            $_POST['color_primary'] ?? '#8b5cf6',
            $_POST['color_accent']  ?? '#a78bfa',
            isset($_POST['status']) ? 1 : 0,
            (int)($_POST['sort_order'] ?? 0),
            (int)$id
        );
        $allTypes = ['boosting', 'accounts', 'items', 'topups', 'coaching', 'egirl'];
        $enabled  = $_POST['services'] ?? [];
        foreach ($allTypes as $type) {
            admin_toggle_game_service((int)$id, $type, in_array($type, $enabled, true));
        }
        redirect_url('admin-area/games/' . (int)$id . '/edit?saved=1');
    });
    $router->post('games/:id/toggle-service', function ($id) {
        global $is_admin;
        if (!$is_admin) { http_response_code(403); echo json_encode(['error' => 'forbidden']); return; }
        $type   = $_POST['service_type'] ?? '';
        $enable = ($_POST['action'] ?? '') === 'enable';
        $ok = admin_toggle_game_service((int)$id, $type, $enable);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
    });

    
    $router->get('games/:id/accounts-config', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { redirect_url('admin-area/games'); return; }
        $services       = util_get_game_services((int)$id);
        $activeServices = array_column($services, 'service_type');
        if (!in_array('accounts', $activeServices, true)) { redirect_url('admin-area/games/' . (int)$id . '/edit'); return; }
        $accountsConfig = util_get_accounts_page_config($game['slug']);
        $boostForms     = $db->run('SELECT * FROM boost_forms WHERE game_id = ? ORDER BY id ASC', (int)$id) ?: [];
        $accountSchema = [];
        try {
            $db->run("CREATE TABLE IF NOT EXISTS game_account_schemas (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                game_slug VARCHAR(100) NOT NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                schema_json JSON NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_game_account_schema (game_slug),
                KEY idx_enabled (enabled)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $schemaRows = $db->run('SELECT schema_json FROM game_account_schemas WHERE game_slug = ? LIMIT 1', $game['slug']);
            if (!empty($schemaRows[0]['schema_json'])) {
                $decodedSchema = json_decode((string)$schemaRows[0]['schema_json'], true);
                if (is_array($decodedSchema)) $accountSchema = $decodedSchema;
            } elseif (function_exists('util_get_game_account_schema')) {
                $accountSchema = util_get_game_account_schema($game['slug']);
            }
        } catch (Throwable $e) {
            if (function_exists('util_get_game_account_schema')) $accountSchema = util_get_game_account_schema($game['slug']);
        }
        view_file('admin/pages/games/edit', [
            'meta'           => ['title' => 'Edit ' . ($game['name'] ?? '') . ' | Admin | LoLBoost'],
            'game'           => $game,
            'services'       => $services,
            'activeServices' => $activeServices,
            'boostForms'     => $boostForms,
            'accountsConfig' => $accountsConfig,
            'accountSchema'  => $accountSchema,
            'itemSchema'     => function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($game['slug']) : [],
            'itemsConfig'    => function_exists('lb_get_items_page_config') ? lb_get_items_page_config($game['slug']) : [],
            'service_types'  => ['boosting', 'accounts', 'items', 'topups', 'coaching', 'egirl'],
            'showAccountsConfig' => true,
        ]);
    });

    $router->post('games/:id/accounts-config', function ($id) {
        global $is_admin, $db;
        header('Content-Type: application/json; charset=utf-8');
        if (!$is_admin) { http_response_code(403); echo json_encode(['success' => false, 'error' => 'forbidden']); return; }

        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { http_response_code(404); echo json_encode(['success' => false, 'error' => 'game_not_found']); return; }

        $schemaJson = trim((string)($_POST['schema_json'] ?? ''));
        $decodedSchema = json_decode($schemaJson, true);
        if (!is_array($decodedSchema)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'invalid_schema_json']);
            return;
        }

        $decodedSchema['enabled'] = !empty($_POST['schema_enabled']);
        $decodedSchema['title_field'] = (string)($decodedSchema['title_field'] ?? '');
        $decodedSchema['headline_icon_field'] = (string)($decodedSchema['headline_icon_field'] ?? '');
        $decodedSchema['fields'] = isset($decodedSchema['fields']) && is_array($decodedSchema['fields']) ? array_values($decodedSchema['fields']) : [];

        $filterKeys = [];
        foreach ($decodedSchema['fields'] as $field) {
            if (!empty($field['filterable']) && !empty($field['key'])) $filterKeys[] = (string)$field['key'];
        }
        if (!in_array('price', $filterKeys, true)) $filterKeys[] = 'price';

        $newConfig = [
            'page_title'       => trim((string)($_POST['page_title'] ?? '')),
            'page_description' => trim((string)($_POST['page_description'] ?? '')),
            'filters'          => array_values(array_unique($filterKeys)),
            'show_type_cards'  => isset($_POST['show_type_cards']) && $_POST['show_type_cards'] === '1',
        ];

        try {
            $existingConfig = function_exists('util_get_accounts_page_config') ? util_get_accounts_page_config($game['slug']) : [];
            if (!is_array($existingConfig)) $existingConfig = [];
            $merged = array_merge($existingConfig, $newConfig);

            $serviceRows = $db->run('SELECT id FROM game_services WHERE game_id = ? AND service_type = ? LIMIT 1', (int)$id, 'accounts') ?: [];
            if (!empty($serviceRows[0]['id'])) {
                $db->run(
                    'UPDATE game_services SET config = ?, status = 1 WHERE id = ?',
                    json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    (int)$serviceRows[0]['id']
                );
            } else {
                $db->run(
                    'INSERT INTO game_services (game_id, service_type, label, status, sort_order, config) VALUES (?, ?, ?, 1, 99, ?)',
                    (int)$id,
                    'accounts',
                    'Account Shop',
                    json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
            }

            $db->run("CREATE TABLE IF NOT EXISTS game_account_schemas (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                game_slug VARCHAR(100) NOT NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                schema_json JSON NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_game_account_schema (game_slug),
                KEY idx_enabled (enabled)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $schemaToStore = json_encode($decodedSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $enabled = !empty($decodedSchema['enabled']) ? 1 : 0;
            $schemaRows = $db->run('SELECT id FROM game_account_schemas WHERE game_slug = ? LIMIT 1', $game['slug']) ?: [];
            if (!empty($schemaRows[0]['id'])) {
                $db->run(
                    'UPDATE game_account_schemas SET enabled = ?, schema_json = ?, updated_at = NOW() WHERE id = ?',
                    $enabled,
                    $schemaToStore,
                    (int)$schemaRows[0]['id']
                );
            } else {
                $db->run(
                    'INSERT INTO game_account_schemas (game_slug, enabled, schema_json) VALUES (?, ?, ?)',
                    $game['slug'],
                    $enabled,
                    $schemaToStore
                );
            }

            echo json_encode(['success' => true, 'game_slug' => $game['slug']]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    });



    $router->post('games/:id/items-config', function ($id) {
        global $is_admin, $db;
        header('Content-Type: application/json; charset=utf-8');
        if (!$is_admin) { http_response_code(403); echo json_encode(['success' => false, 'error' => 'forbidden']); return; }

        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { http_response_code(404); echo json_encode(['success' => false, 'error' => 'game_not_found']); return; }

        $schemaJson = trim((string)($_POST['schema_json'] ?? ''));
        $decodedSchema = json_decode($schemaJson, true);
        if (!is_array($decodedSchema)) { http_response_code(422); echo json_encode(['success' => false, 'error' => 'invalid_schema_json']); return; }

        $decodedSchema['enabled'] = !empty($_POST['schema_enabled']);
        $decodedSchema['title_field'] = (string)($decodedSchema['title_field'] ?? 'title');
        $decodedSchema['headline_icon_field'] = (string)($decodedSchema['headline_icon_field'] ?? 'type');
        $decodedSchema['fields'] = isset($decodedSchema['fields']) && is_array($decodedSchema['fields']) ? array_values($decodedSchema['fields']) : [];

        $filterKeys = [];
        foreach ($decodedSchema['fields'] as $field) {
            if (!empty($field['filterable']) && !empty($field['key'])) $filterKeys[] = (string)$field['key'];
        }
        foreach (['type','server','price'] as $must) if (!in_array($must, $filterKeys, true)) $filterKeys[] = $must;

        $newConfig = [
            'page_title'       => trim((string)($_POST['page_title'] ?? '')),
            'page_description' => trim((string)($_POST['page_description'] ?? '')),
            'filters'          => array_values(array_unique($filterKeys)),
            'show_type_cards'  => isset($_POST['show_type_cards']) && $_POST['show_type_cards'] === '1',
        ];

        try {
            $existingConfig = function_exists('lb_get_items_page_config') ? lb_get_items_page_config($game['slug']) : [];
            if (!is_array($existingConfig)) $existingConfig = [];
            $merged = array_merge($existingConfig, $newConfig);

            $serviceRows = $db->run('SELECT id FROM game_services WHERE game_id = ? AND service_type = ? LIMIT 1', (int)$id, 'items') ?: [];
            if (!empty($serviceRows[0]['id'])) {
                $db->run('UPDATE game_services SET config = ?, status = 1 WHERE id = ?', json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), (int)$serviceRows[0]['id']);
            } else {
                $db->run('INSERT INTO game_services (game_id, service_type, label, status, sort_order, config) VALUES (?, ?, ?, 1, 99, ?)', (int)$id, 'items', 'Items', json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            lb_items_schema_table_ensure();
            $schemaToStore = json_encode($decodedSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $enabled = !empty($decodedSchema['enabled']) ? 1 : 0;
            $schemaRows = $db->run('SELECT id FROM game_item_schemas WHERE game_slug = ? LIMIT 1', $game['slug']) ?: [];
            if (!empty($schemaRows[0]['id'])) {
                $db->run('UPDATE game_item_schemas SET enabled = ?, schema_json = ?, updated_at = NOW() WHERE id = ?', $enabled, $schemaToStore, (int)$schemaRows[0]['id']);
            } else {
                $db->run('INSERT INTO game_item_schemas (game_slug, enabled, schema_json) VALUES (?, ?, ?)', $game['slug'], $enabled, $schemaToStore);
            }

            echo json_encode(['success' => true, 'game_slug' => $game['slug']]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    });


    $router->post('games/:id/topups-config', function ($id) {
        global $is_admin, $db;
        header('Content-Type: application/json; charset=utf-8');
        if (!$is_admin) { http_response_code(403); echo json_encode(['success' => false, 'error' => 'forbidden']); return; }

        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { http_response_code(404); echo json_encode(['success' => false, 'error' => 'game_not_found']); return; }

        $schemaJson = trim((string)($_POST['schema_json'] ?? ''));
        $decodedSchema = json_decode($schemaJson, true);
        if (!is_array($decodedSchema)) { http_response_code(422); echo json_encode(['success' => false, 'error' => 'invalid_schema_json']); return; }

        $decodedSchema['enabled'] = !empty($_POST['schema_enabled']);
        $decodedSchema['checkout_fields'] = isset($decodedSchema['checkout_fields']) && is_array($decodedSchema['checkout_fields']) ? array_values($decodedSchema['checkout_fields']) : [];
        $decodedSchema['seller_fields'] = isset($decodedSchema['seller_fields']) && is_array($decodedSchema['seller_fields']) ? array_values($decodedSchema['seller_fields']) : [];

        $serviceLabel = trim((string)($_POST['service_label'] ?? 'Top Up')) ?: 'Top Up';
        $newConfig = [
            'service_label' => $serviceLabel,
            'page_title' => trim((string)($_POST['page_title'] ?? '')),
            'page_description' => trim((string)($_POST['page_description'] ?? '')),
            'amount_label' => trim((string)($_POST['amount_label'] ?? 'Amount')) ?: 'Amount',
            'region_label' => trim((string)($_POST['region_label'] ?? 'Region')) ?: 'Region',
            'show_other_sellers' => isset($_POST['show_other_sellers']) && $_POST['show_other_sellers'] === '1',
        ];

        try {
            $serviceRows = $db->run("SELECT id FROM game_services WHERE game_id = ? AND service_type IN ('topups','top-ups','currencies') LIMIT 1", (int)$id) ?: [];
            $cfgJson = json_encode($newConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (!empty($serviceRows[0]['id'])) {
                $db->run('UPDATE game_services SET service_type = ?, label = ?, status = 1, config = ? WHERE id = ?', 'topups', $serviceLabel, $cfgJson, (int)$serviceRows[0]['id']);
            } else {
                $db->run('INSERT INTO game_services (game_id, service_type, label, status, sort_order, config) VALUES (?, ?, ?, 1, 45, ?)', (int)$id, 'topups', $serviceLabel, $cfgJson);
            }

            lb_topups_schema_table_ensure();
            $schemaToStore = json_encode($decodedSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $enabled = !empty($decodedSchema['enabled']) ? 1 : 0;
            $schemaRows = $db->run('SELECT id FROM game_topup_schemas WHERE game_slug = ? LIMIT 1', $game['slug']) ?: [];
            if (!empty($schemaRows[0]['id'])) {
                $db->run('UPDATE game_topup_schemas SET enabled = ?, schema_json = ?, updated_at = NOW() WHERE id = ?', $enabled, $schemaToStore, (int)$schemaRows[0]['id']);
            } else {
                $db->run('INSERT INTO game_topup_schemas (game_slug, enabled, schema_json) VALUES (?, ?, ?)', $game['slug'], $enabled, $schemaToStore);
            }

            echo json_encode(['success' => true, 'game_slug' => $game['slug']]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    });

    // ── Game Boost Forms: Create ─────────────────────────────────────────────
    $router->get('games/:id/boost-forms/create', function ($id) {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { redirect_url('admin-area/games'); return; }
        view_file('admin/pages/games/boost-form-edit', [
            'meta' => ['title' => 'Add Boost Form – ' . $game['name'] . ' | Admin | LoLBoost'],
            'game' => $game,
            'form' => null,
        ]);
    });

    $router->post('games/:id/boost-forms/create', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $game = db_get_row('games', ['id' => (int)$id]);
        if (!$game) { redirect_url('admin-area/games'); return; }

        $name     = trim((string)($_POST['name']     ?? ''));
        $nameLong = trim((string)($_POST['name_long'] ?? $name));
        $slug     = trim(strtolower(preg_replace('/[^a-z0-9-]+/', '-', (string)($_POST['slug'] ?? ''))));
        $type     = trim((string)($_POST['type']     ?? 'rank'));
        $desc     = trim((string)($_POST['description'] ?? ''));
        $status   = (int)($_POST['status'] ?? 1);

        if (!$name || !$slug) {
            redirect_url('admin-area/games/' . (int)$id . '/boost-forms/create?error=missing');
            return;
        }

        // Build minimal pricing JSON and save to file
        $uuid    = bin2hex(random_bytes(16));
        $uuid    = substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
        $jsonDir = SYS_PATH . '/public/uploads/private/boost-forms';
        if (!is_dir($jsonDir)) { mkdir($jsonDir, 0755, true); }

        $defaultJson = [
            'main'            => new stdClass(),
            'extra'           => ['is_priority' => 0.25, 'is_streaming' => 0.15, 'is_solo_only' => 0.20],
            'completion_time' => 24,
            'points_label'    => 'LP',
        ];
        $pricingJson = trim((string)($_POST['pricing_json'] ?? ''));
        $decodedJson = $pricingJson !== '' ? json_decode($pricingJson, true) : null;
        $jsonToSave  = is_array($decodedJson) ? $decodedJson : $defaultJson;
        file_put_contents(
            $jsonDir . '/' . $uuid . '.json',
            json_encode($jsonToSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $db->run(
            'INSERT INTO boost_forms (game, game_id, name, name_long, slug, type, description, banner, status, created_at, uuid)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)',
            $game['slug'], (int)$id, $name, $nameLong, $slug, $type, $desc, 'one', $status, $uuid
        );
        $newId = (int)$db->getPdo()->lastInsertId();
        redirect_url('admin-area/games/' . (int)$id . '/boost-form-edit?fid=' . $newId . '&created=1');
    });

    // ── Game Boost Forms: Edit / Save / Delete ───────────────────────────────
    // Flat routes - fid passed as GET/POST param to avoid dual-param router issues
    $router->get('games/:id/boost-form-edit', function ($id) {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $formId = (int)($_GET['fid'] ?? 0);
        $game = db_get_row('games', ['id' => (int)$id]);
        $form = $formId ? db_get_row('boost_forms', ['id' => $formId]) : null;
        if (!$game) { redirect_url('admin-area/games'); return; }
        if ($form) { $form['json'] = load_boost_form_json($form['uuid']); }
        view_file('admin/pages/games/boost-form-edit', [
            'meta' => ['title' => ($form ? 'Edit ' . $form['name'] : 'Add Boost Form') . ' | Admin | LoLBoost'],
            'game' => $game,
            'form' => $form,
        ]);
    });

    $router->post('games/:id/boost-form-save', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $formId = (int)($_POST['fid'] ?? 0);
        $game = db_get_row('games', ['id' => (int)$id]);
        $form = $formId ? db_get_row('boost_forms', ['id' => $formId]) : null;
        if (!$game) { redirect_url('admin-area/games'); return; }

        $name     = trim((string)($_POST['name']      ?? ($form['name'] ?? '')));
        $nameLong = trim((string)($_POST['name_long'] ?? $name));
        $slug     = trim(strtolower(preg_replace('/[^a-z0-9-]+/', '-', (string)($_POST['slug'] ?? ($form['slug'] ?? '')))));
        $type     = trim((string)($_POST['type']      ?? ($form['type'] ?? 'rank')));
        $desc     = trim((string)($_POST['description'] ?? ''));
        $status   = (int)($_POST['status'] ?? 1);

        if ($form) {
            $db->run(
                'UPDATE boost_forms SET name=?, name_long=?, slug=?, type=?, description=?, status=? WHERE id=?',
                $name, $nameLong, $slug, $type, $desc, $status, $formId
            );
        }

        $pricingJson = trim((string)($_POST['pricing_json'] ?? ''));
        if ($pricingJson && $form) {
            $decoded = json_decode($pricingJson, true);
            if (is_array($decoded)) {
                $jsonDir = SYS_PATH . '/public/uploads/private/boost-forms';
                if (!is_dir($jsonDir)) { mkdir($jsonDir, 0755, true); }
                file_put_contents($jsonDir . '/' . $form['uuid'] . '.json', json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        redirect_url('admin-area/games/' . (int)$id . '/boost-form-edit?fid=' . $formId . '&saved=1');
    });

    $router->post('games/:id/boost-form-delete', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { http_response_code(403); echo json_encode(['error' => 'forbidden']); return; }
        $formId = (int)($_POST['fid'] ?? 0);
        if ($formId) { $db->run('UPDATE boost_forms SET status=0 WHERE id=?', $formId); }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    });


    // Support Shift Planner
    $router->get('support-shifts', function () {
        global $is_admin;
        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }
        if (!function_exists('lb_support_shift_can_access') || !lb_support_shift_can_access()) {
            redirect_url('admin-area/orders');
        }
        $from = isset($_GET['from']) ? esc($_GET['from']) : date('Y-m-d');
        $to = isset($_GET['to']) ? esc($_GET['to']) : date('Y-m-d', strtotime('+6 days'));
        $data = function_exists('lb_support_shift_fetch_data') ? lb_support_shift_fetch_data($from, $to) : [];
        view_file('admin/pages/support-shifts', ['data' => $data, 'meta' => ['title' => 'Support Shifts']]);
    });

    $router->get('referrals', function () {
        global $is_admin;
        if ($is_admin) {
            view_file('admin/pages/referrals', [
                'meta' => [
                    'title' => 'Referral Settings | Admin | LoLBoost',
                ],
                'settings' => function_exists('lb_referral_get_settings') ? lb_referral_get_settings() : [],
            ]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('coming-soon-notifications', function () {
        global $is_admin, $db;
        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }

        $search = trim((string)($_GET['q'] ?? ''));
        $game = trim((string)($_GET['game'] ?? ''));
        $service = trim((string)($_GET['service'] ?? ''));
        $where = [];
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $where[] = '(csn.email LIKE ? OR csn.game_name LIKE ? OR csn.game_slug LIKE ? OR c.username LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        if ($game !== '') {
            $where[] = 'csn.game_slug = ?';
            $params[] = $game;
        }
        if ($service !== '') {
            $where[] = 'csn.service_type = ?';
            $params[] = $service;
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        try {
            try {
                $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS client_id INT UNSIGNED DEFAULT NULL AFTER email");
                $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS notified_at DATETIME DEFAULT NULL AFTER updated_at");
                $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS notification_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER notified_at");
                $db->run("ALTER TABLE coming_soon_notifications ADD COLUMN IF NOT EXISTS notification_error VARCHAR(255) DEFAULT NULL AFTER notification_attempts");
                $db->run("UPDATE coming_soon_notifications csn
                          INNER JOIN clients c ON LOWER(c.email) = LOWER(csn.email)
                          SET csn.client_id = c.id
                          WHERE csn.client_id IS NULL");
            } catch (\Throwable $e) {}

            $sql = "SELECT csn.id, csn.email, csn.client_id, csn.game_slug, csn.game_name,
                           csn.service_type, csn.created_at, csn.updated_at, csn.notified_at,
                           g.icon AS game_icon,
                           c.username AS client_username, c.icon AS client_icon
                    FROM coming_soon_notifications csn
                    LEFT JOIN games g ON g.slug = csn.game_slug
                    LEFT JOIN clients c ON c.id = csn.client_id
                    {$whereSql}
                    ORDER BY csn.created_at DESC, csn.id DESC
                    LIMIT 1000";
            $notifications = $params ? ($db->run($sql, ...$params) ?: []) : ($db->run($sql) ?: []);
            $games = $db->run("SELECT game_slug, MAX(game_name) AS game_name, COUNT(*) AS total
                               FROM coming_soon_notifications
                               GROUP BY game_slug ORDER BY game_name") ?: [];
            $services = $db->run("SELECT service_type, COUNT(*) AS total
                                  FROM coming_soon_notifications
                                  GROUP BY service_type ORDER BY service_type") ?: [];
            $statsRows = $db->run("SELECT COUNT(*) AS total,
                                          COUNT(DISTINCT LOWER(email)) AS unique_emails,
                                          COUNT(DISTINCT game_slug) AS games,
                                          SUM(created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS last_7_days
                                   FROM coming_soon_notifications") ?: [];
            $stats = $statsRows[0] ?? [];

            if (isset($_GET['export']) && $_GET['export'] === 'emails') {
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="coming-soon-emails-' . date('Y-m-d') . '.csv"');
                echo "\xEF\xBB\xBF";
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Email', 'Client', 'Game', 'Service', 'Registered', 'Notified']);
                foreach ($notifications as $row) {
                    fputcsv($output, [
                        (string)($row['email'] ?? ''),
                        (string)($row['client_username'] ?? ''),
                        (string)($row['game_name'] ?? $row['game_slug'] ?? ''),
                        (string)($row['service_type'] ?? ''),
                        (string)($row['created_at'] ?? ''),
                        (string)($row['notified_at'] ?? ''),
                    ]);
                }
                fclose($output);
                return;
            }
        } catch (\Throwable $e) {
            $notifications = [];
            $games = [];
            $services = [];
            $stats = [];
            $queryError = $e->getMessage();
        }

        view_file('admin/pages/coming-soon-notifications', compact(
            'notifications', 'games', 'services', 'stats', 'search', 'game', 'service', 'queryError'
        ));
    });

    // =========================
    // Giveaways
    // =========================
    $router->get('giveaways', function () {
        global $is_admin;
        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        // Use helper (compatible with current DB wrapper)
        $giveaways = db_get_rows('giveaways', [
            'order' => 'id DESC'
        ], true);

        view_file('admin/pages/giveaways/list', [
            'giveaways' => $giveaways ?: []
        ]);
    });

    $router->get('giveaways/edit', function () {
        global $is_admin, $db;
        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $giveaway = false;
        $prizes = [];
        $leaderboard = [];
        $winners = [];

        if ($id > 0) {
            $giveaway = db_get_row('giveaways', ['id' => $id]);

            // Prizes
            $prizes = $db->run("SELECT * FROM giveaway_prizes WHERE giveaway_id = {$id} ORDER BY position ASC") ?: [];

            // Leaderboard (top 200)
            $gid = (int)$id;
            $leaderboard = $db->run(
                "SELECT gt.client_id, gt.tickets, c.username, c.email, c.icon
                 FROM giveaway_tickets gt
                 JOIN clients c ON c.id = gt.client_id
                 WHERE gt.giveaway_id = {$gid} AND gt.tickets > 0
                 ORDER BY gt.tickets DESC, gt.updated_at ASC
                 LIMIT 200"
            ) ?: [];

            // Winners
            $winners = $db->run(
                "SELECT gw.rank, gw.client_id, gw.tickets_at_draw, c.username, c.email, c.icon
                 FROM giveaway_winners gw
                 JOIN clients c ON c.id = gw.client_id
                 WHERE gw.giveaway_id = {$gid}
                 ORDER BY gw.rank ASC"
            ) ?: [];
        }

        view_file('admin/pages/giveaways/edit', [
            'giveaway' => $giveaway,
            'prizes' => $prizes,
            'leaderboard' => $leaderboard,
            'winners' => $winners,
        ]);
    });

    // =========================
    // Booster Games
    // =========================
    $router->get('booster-games', function () {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }

        // Filters and paging run in SQL. Loading 5000 joined match rows, enriching every
        // one of them in the view and only then paginating client-side made this page
        // take many seconds; now only the 30 rows of the current page are fetched.
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(10, min(100, (int)($_GET['limit'] ?? 30)));
        $mode    = strtolower(trim((string)($_GET['mode'] ?? 'all')));
        if (!in_array($mode, ['solo', 'duo', 'all'], true)) $mode = 'all';
        $boosterFilter = (int)($_GET['booster'] ?? 0);
        $search  = trim((string)($_GET['search'] ?? ''));

        // Detect Duo: order has is_duo=1 on order_options OR a paid Duo/Play-With-Booster addon invoice
        $playWithBoosterSql = "(
            COALESCE(oo.is_duo, 0) = 1
            OR EXISTS (
                SELECT 1 FROM invoices ai
                WHERE ai.order_id = o.id
                  AND ai.order_type = 'addon'
                  AND ai.status = 'PAID'
                  AND (
                       LOWER(COALESCE(ai.note, '')) LIKE '%duo%'
                    OR LOWER(COALESCE(ai.note, '')) LIKE '%play with booster%'
                    OR LOWER(COALESCE(ai.note, '')) LIKE '%play_with_booster%'
                  )
            )
        )";

        $where = ['1'];
        $args  = [];

        // Soft-hidden matches were filtered in the view before, which meant a page could
        // render fewer than $perPage rows. Filter here so paging stays exact.
        try {
            $hiddenCol = $db->row("SHOW COLUMNS FROM order_matches LIKE 'is_hidden'");
            if (!empty($hiddenCol)) $where[] = "COALESCE(om.is_hidden, 0) = 0";
        } catch (\Throwable $e) {}

        if ($boosterFilter > 0) {
            $where[] = "COALESCE(om.booster_id, o.booster_id) = ?";
            $args[]  = $boosterFilter;
        }
        if ($mode === 'duo') {
            $where[] = $playWithBoosterSql;
        } elseif ($mode === 'solo') {
            $where[] = "NOT " . $playWithBoosterSql;
        }
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $where[] = "(b.username LIKE ? OR om.champion LIKE ? OR CAST(om.order_id AS CHAR) LIKE ? OR CAST(om.match_id AS CHAR) LIKE ?)";
            array_push($args, $like, $like, $like, $like);
        }
        $whereSql = implode(' AND ', $where);

        $fromSql = "FROM order_matches om
                    JOIN orders o ON o.id = om.order_id
                    LEFT JOIN order_options oo ON oo.order_id = o.id
                    LEFT JOIN boosters b ON b.id = COALESCE(om.booster_id, o.booster_id)
                    WHERE {$whereSql}";

        $totalRows = 0;
        try {
            $countRow = $db->row("SELECT COUNT(*) AS cnt {$fromSql}", ...$args);
            $totalRows = (int)($countRow['cnt'] ?? 0);
        } catch (\Throwable $e) {}

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $perPage;

        $rows = [];
        try {
            $rows = $db->run(
                "SELECT om.id,
                        om.order_id,
                        om.match_id,
                        om.champion,
                        om.position,
                        om.kills,
                        om.deaths,
                        om.assists,
                        om.won,
                        om.duration,
                        om.queue_id,
                        om.played_at,
                        COALESCE(om.is_remake, 0) AS is_remake,
                        COALESCE(om.booster_id, o.booster_id) AS booster_id,
                        b.username AS booster_username,
                        b.icon AS booster_icon,
                        CASE WHEN {$playWithBoosterSql} THEN 1 ELSE 0 END AS is_duo
                 {$fromSql}
                 ORDER BY om.played_at DESC, om.id DESC
                 LIMIT {$perPage} OFFSET {$offset}",
                ...$args
            ) ?: [];
        } catch (\Throwable $e) {
            $rows = [];
        }

        // Booster dropdown: distinct boosters that actually have matches. The old query
        // used a correlated EXISTS per order_matches row, which is why it was slow.
        $boosters = [];
        try {
            $boosters = $db->run(
                "SELECT b.id, b.username
                 FROM boosters b
                 WHERE EXISTS (SELECT 1 FROM order_matches om2 WHERE om2.booster_id = b.id)
                    OR EXISTS (
                        SELECT 1 FROM order_matches om3
                        JOIN orders o3 ON o3.id = om3.order_id
                        WHERE om3.booster_id IS NULL AND o3.booster_id = b.id
                    )
                 ORDER BY b.username ASC"
            ) ?: [];
        } catch (\Throwable $e) {
            $boosters = [];
        }

        view_file('admin/pages/booster-games', [
            'rows'       => $rows,
            'boosters'   => $boosters,
            'page'       => $page,
            'limit'      => $perPage,
            'totalRows'  => $totalRows,
            'totalPages' => $totalPages,
            'booster'    => $boosterFilter,
            'mode'       => $mode,
            'search'     => $search,
        ]);
    });

    // =========================
    // Booster Leaderboard
    // =========================
    $router->get('booster-leaderboard', function () {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }

        $month = trim((string)($_GET['month'] ?? date('Y-m')));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $start = $month . '-01 00:00:00';
        $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));

        $minimumGames = 10;
        $minimumTopWinrate = 50.0;

        $isDuoSql = "(COALESCE(oo.is_duo, 0) = 1)";

        $soloWinPointsSql = "CASE COALESCE(oo.start_tier, 0)
            WHEN 10 THEN 25
            WHEN 9 THEN 22
            WHEN 8 THEN 18
            WHEN 7 THEN 13
            WHEN 6 THEN 10
            WHEN 5 THEN 7
            WHEN 4 THEN 5
            WHEN 3 THEN 3
            WHEN 2 THEN 2
            WHEN 1 THEN 2
            ELSE 4
        END";

        $duoWinPointsSql = "CASE COALESCE(oo.start_tier, 0)
            WHEN 10 THEN 32
            WHEN 9 THEN 28
            WHEN 8 THEN 23
            WHEN 7 THEN 17
            WHEN 6 THEN 13
            WHEN 5 THEN 10
            WHEN 4 THEN 8
            WHEN 3 THEN 5
            WHEN 2 THEN 3
            WHEN 1 THEN 3
            ELSE 7
        END";

        $soloLossPointsSql = "CASE COALESCE(oo.start_tier, 0)
            WHEN 10 THEN 8
            WHEN 9 THEN 9
            WHEN 8 THEN 10
            WHEN 7 THEN 12
            WHEN 6 THEN 13
            WHEN 5 THEN 14
            WHEN 4 THEN 15
            WHEN 3 THEN 18
            WHEN 2 THEN 20
            WHEN 1 THEN 22
            ELSE 16
        END";

        $duoLossPointsSql = "CASE COALESCE(oo.start_tier, 0)
            WHEN 10 THEN 6
            WHEN 9 THEN 7
            WHEN 8 THEN 8
            WHEN 7 THEN 10
            WHEN 6 THEN 11
            WHEN 5 THEN 12
            WHEN 4 THEN 13
            WHEN 3 THEN 15
            WHEN 2 THEN 17
            WHEN 1 THEN 19
            ELSE 14
        END";

        $winPointsSql = "(CASE WHEN $isDuoSql THEN ($duoWinPointsSql) ELSE ($soloWinPointsSql) END)";
        $lossPointsSql = "(CASE WHEN $isDuoSql THEN ($duoLossPointsSql) ELSE ($soloLossPointsSql) END)";

        $rawRows = $db->run(
            "SELECT
                b.id AS booster_id,
                b.username,
                b.icon,
                b.rank_id,

                COUNT(*) AS games,
                SUM(CASE WHEN om.won = 1 THEN 1 ELSE 0 END) AS wins,
                SUM(CASE WHEN om.won = 0 THEN 1 ELSE 0 END) AS losses,

                SUM(CASE WHEN $isDuoSql THEN 0 ELSE 1 END) AS solo_games,
                SUM(CASE WHEN $isDuoSql THEN 1 ELSE 0 END) AS duo_games,
                SUM(CASE WHEN NOT $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS solo_wins,
                SUM(CASE WHEN $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS duo_wins,
                SUM(CASE WHEN NOT $isDuoSql AND om.won = 0 THEN 1 ELSE 0 END) AS solo_losses,
                SUM(CASE WHEN $isDuoSql AND om.won = 0 THEN 1 ELSE 0 END) AS duo_losses,

                SUM(CASE WHEN om.won = 1 THEN $winPointsSql ELSE 0 END) AS win_points,
                SUM(CASE WHEN om.won = 0 THEN $lossPointsSql ELSE 0 END) AS loss_penalty,

                SUM(CASE WHEN COALESCE(oo.start_tier, 0) >= 7 THEN 1 ELSE 0 END) AS diamond_plus_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) >= 8 THEN 1 ELSE 0 END) AS master_plus_games,

                SUM(CASE WHEN COALESCE(oo.start_tier, 0) >= 8 AND NOT $isDuoSql THEN 1 ELSE 0 END) AS master_plus_solo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) >= 8 AND NOT $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS master_plus_solo_wins,

                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 7 AND NOT $isDuoSql THEN 1 ELSE 0 END) AS diamond_solo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 7 AND NOT $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS diamond_solo_wins,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 7 AND $isDuoSql THEN 1 ELSE 0 END) AS diamond_duo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 7 AND $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS diamond_duo_wins,

                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 6 AND NOT $isDuoSql THEN 1 ELSE 0 END) AS emerald_solo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 6 AND NOT $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS emerald_solo_wins,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 6 AND $isDuoSql THEN 1 ELSE 0 END) AS emerald_duo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 6 AND $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS emerald_duo_wins,

                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 5 AND NOT $isDuoSql THEN 1 ELSE 0 END) AS platinum_solo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 5 AND NOT $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS platinum_solo_wins,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 5 AND $isDuoSql THEN 1 ELSE 0 END) AS platinum_duo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) = 5 AND $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS platinum_duo_wins,

                SUM(CASE WHEN COALESCE(oo.start_tier, 0) IN (0,4) AND NOT $isDuoSql THEN 1 ELSE 0 END) AS gold_unranked_solo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) IN (0,4) AND NOT $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS gold_unranked_solo_wins,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) IN (0,4) AND $isDuoSql THEN 1 ELSE 0 END) AS gold_unranked_duo_games,
                SUM(CASE WHEN COALESCE(oo.start_tier, 0) IN (0,4) AND $isDuoSql AND om.won = 1 THEN 1 ELSE 0 END) AS gold_unranked_duo_wins

             FROM order_matches om FORCE INDEX (idx_order_matches_leaderboard_fast)
             JOIN orders o
               ON o.id = om.order_id
             LEFT JOIN order_options oo
               ON oo.order_id = o.id
             JOIN boosters b
               ON b.id = COALESCE(om.booster_id, o.booster_id)
             WHERE om.played_at >= ?
               AND om.played_at < ?
               AND COALESCE(om.is_remake, 0) = 0
               AND COALESCE(b.is_banned, 0) = 0
               AND COALESCE(b.is_egirl, 0) = 0
               AND COALESCE(oo.is_undercover_winrate, 0) = 0
               AND COALESCE(oo.is_moderate_kda, 0) = 0
             GROUP BY b.id, b.username, b.icon, b.rank_id",
            $start,
            $end
        ) ?: [];

        $tierWinrateReward = function ($wins, $games, $minGames, array $rewards) {
            $games = (int)$games;
            $wins = (int)$wins;

            if ($games < $minGames || $games <= 0) {
                return 0;
            }

            $wr = ($wins / $games) * 100;

            foreach ($rewards as $threshold => $points) {
                if ($wr >= $threshold) {
                    return $points;
                }
            }

            return 0;
        };

        $rows = [];
        foreach ($rawRows as $row) {
            $games = (int)($row['games'] ?? 0);
            $wins = (int)($row['wins'] ?? 0);
            $losses = (int)($row['losses'] ?? 0);
            $winrate = $games > 0 ? round(($wins / $games) * 100, 2) : 0.0;

            $winPoints = (float)($row['win_points'] ?? 0);
            $lossPenalty = (float)($row['loss_penalty'] ?? 0);

            $winrateRewards = 0;
            $winrateRewards += $tierWinrateReward($row['master_plus_solo_wins'] ?? 0, $row['master_plus_solo_games'] ?? 0, 10, [95 => 100, 90 => 80, 85 => 60, 80 => 40, 75 => 20]);
            $winrateRewards += $tierWinrateReward($row['diamond_solo_wins'] ?? 0, $row['diamond_solo_games'] ?? 0, 10, [95 => 50, 90 => 40, 85 => 30, 80 => 20, 75 => 10]);
            $winrateRewards += $tierWinrateReward($row['diamond_duo_wins'] ?? 0, $row['diamond_duo_games'] ?? 0, 5, [95 => 100, 90 => 80, 85 => 60, 80 => 40, 75 => 20]);
            $winrateRewards += $tierWinrateReward($row['emerald_solo_wins'] ?? 0, $row['emerald_solo_games'] ?? 0, 20, [95 => 25, 90 => 20, 85 => 15, 80 => 10, 75 => 5]);
            $winrateRewards += $tierWinrateReward($row['emerald_duo_wins'] ?? 0, $row['emerald_duo_games'] ?? 0, 10, [95 => 50, 90 => 40, 85 => 30, 80 => 20, 75 => 10]);
            $winrateRewards += $tierWinrateReward($row['platinum_solo_wins'] ?? 0, $row['platinum_solo_games'] ?? 0, 20, [95 => 14, 90 => 12, 85 => 9, 80 => 7, 75 => 5]);
            $winrateRewards += $tierWinrateReward($row['platinum_duo_wins'] ?? 0, $row['platinum_duo_games'] ?? 0, 10, [95 => 35, 90 => 25, 85 => 20, 80 => 15, 75 => 7]);
            $winrateRewards += $tierWinrateReward($row['gold_unranked_solo_wins'] ?? 0, $row['gold_unranked_solo_games'] ?? 0, 40, [95 => 12, 90 => 10, 85 => 7, 80 => 5, 75 => 3]);
            $winrateRewards += $tierWinrateReward($row['gold_unranked_duo_wins'] ?? 0, $row['gold_unranked_duo_games'] ?? 0, 20, [95 => 24, 90 => 20, 85 => 14, 80 => 10, 75 => 6]);

            $activityBonus = 0;
            if ($games >= 150) {
                $activityBonus = 40;
            } elseif ($games >= 100) {
                $activityBonus = 25;
            } elseif ($games >= 50) {
                $activityBonus = 10;
            }

            $diamondPlusGames = (int)($row['diamond_plus_games'] ?? 0);
            $masterPlusGames = (int)($row['master_plus_games'] ?? 0);

            $highEloBonus = 0;
            if ($diamondPlusGames >= 40) {
                $highEloBonus += 50;
            } elseif ($diamondPlusGames >= 20) {
                $highEloBonus += 20;
            }

            if ($masterPlusGames >= 15) {
                $highEloBonus += 60;
            }

            $soloGames = (int)($row['solo_games'] ?? 0);
            $duoGames = (int)($row['duo_games'] ?? 0);
            $duoRatio = $games > 0 ? ($duoGames / $games) * 100 : 0;

            $duoRatioBonus = 0;
            if ($duoRatio >= 40) {
                $duoRatioBonus = 25;
            } elseif ($duoRatio >= 20) {
                $duoRatioBonus = 10;
            }

            $rawScore = $winPoints - $lossPenalty + $winrateRewards + $activityBonus + $highEloBonus + $duoRatioBonus;
            $score = max(0, $rawScore);

            if ($winrate < $minimumTopWinrate) {
                $score = 0;
            }

            if ($winrate >= 50 && $winrate < 60) {
                $score *= max(0, ($winrate - 50) / 10);
            }

            $qualified = $games >= $minimumGames && $winrate >= $minimumTopWinrate;

            $rows[] = [
                'booster_id' => (int)$row['booster_id'],
                'username' => (string)($row['username'] ?? 'Booster'),
                'icon' => (string)($row['icon'] ?? ''),
                'rank_id' => (int)($row['rank_id'] ?? 0),
                'games' => $games,
                'wins' => $wins,
                'losses' => $losses,
                'solo_games' => $soloGames,
                'duo_games' => $duoGames,
                'solo_wins' => (int)($row['solo_wins'] ?? 0),
                'solo_losses' => (int)($row['solo_losses'] ?? 0),
                'duo_wins' => (int)($row['duo_wins'] ?? 0),
                'duo_losses' => (int)($row['duo_losses'] ?? 0),
                'winrate' => $winrate,
                'win_points' => round($winPoints, 2),
                'loss_penalty' => round($lossPenalty, 2),
                'winrate_rewards' => round($winrateRewards, 2),
                'activity_bonus' => round($activityBonus, 2),
                'high_elo_bonus' => round($highEloBonus, 2),
                'duo_ratio_bonus' => round($duoRatioBonus, 2),
                'duo_ratio' => round($duoRatio, 2),
                'raw_score' => round($rawScore, 2),
                'score' => round($score, 2),
                'qualified' => $qualified,
            ];
        }

        usort($rows, function ($a, $b) {
            if ($a['qualified'] !== $b['qualified']) {
                return $a['qualified'] ? -1 : 1;
            }

            if ($a['score'] == $b['score']) {
                if ($a['winrate'] == $b['winrate']) {
                    return $b['games'] <=> $a['games'];
                }
                return $b['winrate'] <=> $a['winrate'];
            }

            return $b['score'] <=> $a['score'];
        });

        foreach ($rows as $i => &$row) {
            $row['position'] = $i + 1;
            $row['is_top_5'] = $row['qualified'] && $i < 5;
        }
        unset($row);

        $summary = [
            'boosters' => count($rows),
            'qualified' => count(array_filter($rows, fn($r) => !empty($r['qualified']))),
            'below_50' => count(array_filter($rows, fn($r) => (float)($r['winrate'] ?? 0) < 50)),
            'games' => array_sum(array_map(fn($r) => (int)($r['games'] ?? 0), $rows)),
            'top_booster' => $rows[0]['username'] ?? '-',
        ];

        view_file('admin/pages/booster-leaderboard', [
            'month' => $month,
            'rows' => $rows,
            'summary' => $summary,
        ]);
    });

    // =========================
    // Calculate Progress Payment
    // =========================
    $router->get('calculate-progress-payment', function () {
        global $is_admin;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        // optional: same owner restriction as other admin tools
        if (
            ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
            ADMIN_DATA['email'] !== 'justsromail@freenet.de'
        ) {
            redirect_url('admin-area/dashboard');
        }

        $game = $_GET['game'] ?? 'league-of-legends';
        $form_id = isset($_GET['form_id']) ? (int) $_GET['form_id'] : 0;

        $GLOBALS['cpp'] = [
            'game' => in_array($game, ['league-of-legends', 'valorant'], true) ? $game : 'league-of-legends',
            'form_id' => $form_id,
            'post' => [],
            'result' => null,
            'errors' => [],
        ];

        view_file('admin/pages/calculate-progress-payment');
    });

    $router->post('calculate-progress-payment', function () {
        global $is_admin;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        if (
            ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
            ADMIN_DATA['email'] !== 'justsromail@freenet.de'
        ) {
            redirect_url('admin-area/dashboard');
        }

        $errors = [];
        $result = null;

        $game = $_POST['game'] ?? 'league-of-legends';
        $game = in_array($game, ['league-of-legends', 'valorant'], true) ? $game : 'league-of-legends';

        $form_id = isset($_POST['form_id']) ? (int) $_POST['form_id'] : 0;
        if ($form_id <= 0) {
            $errors[] = 'Bitte eine Boost Form auswählen.';
        }

        $form = null;
        if (!$errors) {
            $form = db_get_row('boost_forms', ['id' => $form_id], true);
            if (empty($form) || empty($form['uuid'])) {
                $errors[] = 'Boost Form nicht gefunden (DB).';
            }
        }

        $pricing = null;
        if (!$errors) {
            $pricing = get_pricing_json($form['uuid']);
            if (empty($pricing) || !is_array($pricing)) {
                $errors[] = 'Pricing JSON nicht gefunden/ungültig.';
            }
        }

        if (!$errors) {
            $result = tool_calculate_progress_payment($pricing, $form, $_POST);
            if (empty($result['ok'])) {
                $errors[] = $result['error'] ?? 'Unknown calc error';
            }
        }

        $GLOBALS['cpp'] = [
            'game' => $game,
            'form_id' => $form_id,
            'post' => $_POST,
            'result' => $result,
            'errors' => $errors,
        ];

        view_file('admin/pages/calculate-progress-payment');
    });

    $router->get('manage-languages', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'duck_sauce@live.de'
            ) {
                redirect_url('admin-area/orders');
            }

            $langs = [];
            foreach (glob(dirname(__DIR__, 2) . '/public/assets/core/main/translations/*.json') as $file) {

                if (basename($file) === 'master.json') {
                    continue;
                }

                $lang = basename($file, '.json');
                $data = json_decode(file_get_contents($file), true);
                $langs[$lang] = $data['name'] ?? null;
            }

            view_file('admin/pages/langs/list', ['langs' => $langs]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });


    $router->get('sync-languages', function () {
        global $is_admin;
        if ($is_admin) {
            // Same access restriction as manage-languages
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'duck_sauce@live.de'
            ) {
                redirect_url('admin-area/orders');
            }

            $translationsDir = dirname(__DIR__, 2) . '/public/assets/core/main/translations';
            $masterPath = $translationsDir . '/master.json';

            if (!file_exists($masterPath)) {
                redirect_url('admin-area/manage-languages?sync=ok&updated=0');
            }

            $master = json_decode(file_get_contents($masterPath), true);
            if (!is_array($master)) {
                $master = [];
            }

            $updatedFiles = 0;

            foreach (glob($translationsDir . '/*.json') as $file) {
                if (basename($file) === 'master.json') {
                    continue;
                }

                $data = json_decode(@file_get_contents($file), true);
                if (!is_array($data)) {
                    $data = [];
                }

                $changed = false;
                foreach ($master as $key => $value) {
                    if (!array_key_exists($key, $data)) {
                        $data[$key] = (string) $key; // placeholder
                        $changed = true;
                    }
                }

                if ($changed) {
                    @file_put_contents(
                        $file,
                        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    );
                    $updatedFiles++;
                }
            }

            redirect_url('admin-area/manage-languages?sync=ok&updated=' . $updatedFiles);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    /**
     * Picks the correct range-key from an associative array like:
     *  ['0-20'=>..., '21-40'=>..., '41-60'=>..., '61-80'=>..., '81-100'=>..., '30+'=>...]
     */
    $router->get('manage-language/:slug', function ($slug) {
        global $is_admin;
        if ($is_admin) {
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'duck_sauce@live.de'
            ) {
                redirect_url('admin-area/orders');
            }

            $lang = esc($slug);
            $file = dirname(__DIR__, 2) . '/public/assets/core/main/translations/' . $lang . '.json';

            if (!file_exists($file)) {
                redirect_url('admin-area/manage-languages');
            }

            $data = json_decode(file_get_contents($file), true);

            if (!$data) {
                redirect_url('admin-area/manage-languages');
            }

            $masterFile = dirname(__DIR__, 2) . '/public/assets/core/main/translations/master.json';
            $masterData = is_file($masterFile) ? json_decode(file_get_contents($masterFile), true) : ['keys' => []];

            view_file('admin/pages/langs/view', ['data' => $data, 'lang' => $lang, 'masterKeys' => $masterData['keys'] ?? []]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('orders', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'ziad202175@yahoo.com' &&
                ADMIN_DATA['email'] !== 'hesham0elkomy@gmail.com' &&
                ADMIN_DATA['email'] !== 'samsayahix@gmail.com' &&
                ADMIN_DATA['email'] !== 'abdoazzam281@gmail.com' &&
                ADMIN_DATA['email'] !== 'mostafa.frag.thefox@gmail.com' &&
                ADMIN_DATA['email'] !== 'nototakuulol@gmail.com' &&
                ADMIN_DATA['email'] !== 'duck_sauce@live.de'
            ) {
                redirect_url('admin-area/selling-accounts');
            }

            // The orders table is rendered by SERVER-SIDE DataTables (see the view:
            // serverSide=true, ajax=/orders/data). The view does not read $data at all,
            // so the previous code here — loading the ENTIRE orders table + all
            // order_options + all boosters + ALL egirl_orders, merging in PHP and
            // usort()-ing on every single page load — was pure wasted work and the main
            // reason the page hung on "Processing..." before the table even started.
            // Just render the shell; the grid pulls its own paginated data from
            // /orders/data. (Kept an empty 'data' key so the view never sees it undefined.)
            view_file('admin/pages/orders/list', ['data' => []]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });



    // =========================
    // Banner Settings
    // =========================
    $router->get('banner-settings', function () {
        global $is_admin;
        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }
        view_file('admin/pages/banner-settings', [
            'meta' => ['title' => 'Banner Settings | Admin | LoLBoost.gg']
        ]);
    });


    // =========================
    // World Cup Predictions
    // =========================
    $router->get('world-cup-predictions', function () {
        global $is_admin;
        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }
        $meta = [
            'title' => 'World Cup Predictions | Admin',
            'h1'    => 'World Cup Predictions',
        ];
        view_file('admin/pages/world-cup-predictions', ['meta' => $meta]);
    });



    // =========================
    // Security Mode, Under Attack Toggle
    // =========================
    $router->get('security-mode', function () {
        global $is_admin;

        $allowedEmails = [
            'r.machmueller@gmx.de',
            'justsromail@freenet.de',
            'duck_sauce@live.de',
        ];
        $adminEmail = defined('ADMIN_DATA') && isset(ADMIN_DATA['email']) ? strtolower(trim((string) ADMIN_DATA['email'])) : '';

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }

        if (!in_array($adminEmail, $allowedEmails, true)) {
            redirect_url('admin-area/orders');
            return;
        }

        $root = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2)), '/');
        $flagFile = $root . '/security_under_attack.flag';

        view_file('admin/pages/security-mode', [
            'meta' => [
                'title' => 'Security Mode | Admin | LoLBoost.gg',
                'h1' => 'Security Mode',
                'description' => 'Turn the custom LoLBoost.gg browser verification page on or off.',
            ],
            'active' => is_file($flagFile),
            'updated' => isset($_GET['updated']) ? (string) $_GET['updated'] : '',
        ]);
    });

    $router->post('security-mode', function () {
        global $is_admin;

        $allowedEmails = [
            'r.machmueller@gmx.de',
            'justsromail@freenet.de',
            'duck_sauce@live.de',
        ];
        $adminEmail = defined('ADMIN_DATA') && isset(ADMIN_DATA['email']) ? strtolower(trim((string) ADMIN_DATA['email'])) : '';

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }

        if (!in_array($adminEmail, $allowedEmails, true)) {
            redirect_url('admin-area/orders');
            return;
        }

        if (function_exists('csrf_verify') && !csrf_verify($_POST['csrf_token'] ?? '')) {
            redirect_url('admin-area/security-mode?updated=csrf');
            return;
        }

        $root = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2)), '/');
        $flagFile = $root . '/security_under_attack.flag';
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'enable') {
            $content = "LoLBoost Security Mode active since " . date('Y-m-d H:i:s') . " by " . $adminEmail . PHP_EOL;
            @file_put_contents($flagFile, $content, LOCK_EX);
            redirect_url('admin-area/security-mode?updated=enabled');
            return;
        }

        if ($action === 'disable') {
            if (is_file($flagFile)) {
                @unlink($flagFile);
            }
            redirect_url('admin-area/security-mode?updated=disabled');
            return;
        }

        redirect_url('admin-area/security-mode');
    });

    // =========================
    // Security Log (IP Changes)
    // =========================
    $router->get('security-log', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $perPage      = 50;
        $page         = max(1, (int)($_GET['page'] ?? 1));
        $search       = trim((string)($_GET['booster'] ?? ''));
        $onlyFlags    = !empty($_GET['only_flagged']);
        $sourceFilter = trim((string)($_GET['source'] ?? 'history'));
        if (!in_array($sourceFilter, ['history', 'log', 'all'], true)) $sourceFilter = 'history';

        $whereH = '1=1';
        $whereL = '1=1';
        $pH = [];
        $pL = [];
        if ($search !== '') {
            $like = '%' . $search . '%';
            $whereH = 'b.username LIKE ?'; $pH[] = $like;
            $whereL = 'b.username LIKE ?'; $pL[] = $like;
        }

        if ($sourceFilter === 'history') {
            $unionSql = "
                SELECT h.booster_id, h.ip_address, h.created_at, h.device_info,
                       h.city, h.country, 'history' AS source,
                       b.username AS booster_username
                FROM booster_sessions_history h
                LEFT JOIN boosters b ON b.id = h.booster_id
                WHERE $whereH";
            $unionParams = $pH;
        } elseif ($sourceFilter === 'log') {
            $unionSql = "
                SELECT l.booster_id, l.ip_address, l.created_at, l.device_info,
                       l.city, l.country, 'log' AS source,
                       b.username AS booster_username
                FROM booster_session_logs l
                LEFT JOIN boosters b ON b.id = l.booster_id
                WHERE $whereL";
            $unionParams = $pL;
        } else {
            $unionSql = "
                SELECT h.booster_id, h.ip_address, h.created_at, h.device_info,
                       h.city, h.country, 'history' AS source,
                       b.username AS booster_username
                FROM booster_sessions_history h
                LEFT JOIN boosters b ON b.id = h.booster_id
                WHERE $whereH

                UNION ALL

                SELECT l.booster_id, l.ip_address, l.created_at, l.device_info,
                       l.city, l.country, 'log' AS source,
                       b.username AS booster_username
                FROM booster_session_logs l
                LEFT JOIN boosters b ON b.id = l.booster_id
                WHERE $whereL";
            $unionParams = array_merge($pH, $pL);
        }

        $flagWhere = $onlyFlags ? 'WHERE ip_changed = 1' : '';
        $withSql = "
            WITH combined AS (
                $unionSql
            ),
            ranked AS (
                SELECT *,
                    LAG(ip_address)  OVER (PARTITION BY booster_id ORDER BY created_at DESC) AS prev_ip,
                    LAG(created_at)   OVER (PARTITION BY booster_id ORDER BY created_at DESC) AS prev_created_at
                FROM combined
            ),
            flagged AS (
                SELECT *,
                    CASE
                        WHEN prev_ip IS NOT NULL
                         AND prev_ip <> ''
                         AND ip_address <> ''
                         AND ip_address <> prev_ip
                        THEN 1 ELSE 0
                    END AS ip_changed,
                    CASE
                        WHEN prev_created_at IS NOT NULL
                        THEN ABS(TIMESTAMPDIFF(SECOND, prev_created_at, created_at))
                        ELSE NULL
                    END AS time_diff
                FROM ranked
            )
        ";

        try {
            $countSql  = $withSql . " SELECT COUNT(*) FROM flagged $flagWhere";
            $totalRows = (int)($db->cell($countSql, ...$unionParams) ?? 0);
            $totalPages = max(1, (int)ceil($totalRows / $perPage));
            $page       = min($page, $totalPages);
            $offset     = ($page - 1) * $perPage;

            $flagCountSql = $withSql . " SELECT COUNT(*) FROM flagged WHERE ip_changed = 1";
            $flagCount    = (int)($db->cell($flagCountSql, ...$unionParams) ?? 0);

            $rowsSql = $withSql . "
                SELECT booster_id, booster_username, ip_address, prev_ip,
                       created_at, device_info, city, country, source,
                       ip_changed, time_diff
                FROM flagged
                $flagWhere
                ORDER BY booster_id ASC, created_at DESC
                LIMIT $perPage OFFSET $offset
            ";
            $rows = $db->run($rowsSql, ...$unionParams) ?: [];
        } catch (Throwable $e) {
            $totalRows = 0;
            $totalPages = 1;
            $flagCount = 0;
            $rows = [];
        }

        foreach ($rows as &$row) {
            $row['ip_changed'] = (bool)($row['ip_changed'] ?? false);
            $row['prev_ip']    = (string)($row['prev_ip'] ?? '');
            $row['time_diff']  = isset($row['time_diff']) ? (int)$row['time_diff'] : null;
        }
        unset($row);

        try {
            $boosterNames = $db->run("SELECT id, username FROM boosters WHERE is_egirl = 0 ORDER BY username ASC") ?: [];
        } catch (Throwable $e) {
            $boosterNames = [];
        }

        view_file('admin/pages/security-log', [
            'rows'         => $rows,
            'totalRows'    => $totalRows,
            'flagCount'    => $flagCount,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'perPage'      => $perPage,
            'search'       => $search,
            'onlyFlags'    => $onlyFlags,
            'sourceFilter' => $sourceFilter,
            'boosterNames' => $boosterNames,
            'meta'         => ['title' => 'Security Log | Admin | LoLBoost.gg', 'h1' => 'Security Log'],
        ]);
    });

    $router->get('order-accounts', function () {
        global $is_admin, $db;
        if ($is_admin) {
            $allowedEmails = [
                'r.machmueller@gmx.de',
                'justsromail@freenet.de',
                'duck_sauce@live.de'
            ];

            if (!in_array(strtolower(trim((string) ADMIN_DATA['email'])), $allowedEmails, true)) {
                redirect_url('admin-area/orders');
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = (int)($_GET['limit'] ?? 50);
            $limit = max(25, min(100, $limit));
            $offset = ($page - 1) * $limit;
            $search = trim((string)($_GET['search'] ?? ''));
            $sort = strtolower(trim((string)($_GET['sort'] ?? 'id')));
            $dir = strtolower(trim((string)($_GET['dir'] ?? 'desc')));
            $dir = $dir === 'asc' ? 'ASC' : 'DESC';
            $sortMap = [
                'id' => 'oa.id',
                'order' => 'oa.order_id',
                'client' => 'c.username',
                'ign' => 'oa.ign',
                'login' => 'oa.login',
                'password' => 'oa.password',
                'status' => 'o.status',
                'created' => 'o.created_at',
            ];
            if (!isset($sortMap[$sort])) $sort = 'id';
            $orderBy = $sortMap[$sort] . ' ' . $dir . ', oa.id DESC';

            $where = ['1=1'];
            $args = [];

            if ($search !== '') {
                $where[] = "(
                    CAST(oa.id AS CHAR) LIKE ?
                    OR CAST(oa.order_id AS CHAR) LIKE ?
                    OR COALESCE(oa.ign, '') LIKE ?
                    OR COALESCE(oa.login, '') LIKE ?
                    OR COALESCE(oa.password, '') LIKE ?
                    OR COALESCE(o.status, '') LIKE ?
                    OR CAST(COALESCE(o.client_id, 0) AS CHAR) LIKE ?
                    OR COALESCE(c.username, '') LIKE ?
                )";
                $like = '%' . $search . '%';
                array_push($args, $like, $like, $like, $like, $like, $like, $like, $like);
            }

            $whereSql = implode(' AND ', $where);

            $totalRows = (int)($db->cell("
                SELECT COUNT(*)
                FROM order_accounts oa
                LEFT JOIN orders o ON o.id = oa.order_id
                LEFT JOIN clients c ON c.id = o.client_id
                WHERE {$whereSql}
            ", ...$args) ?: 0);

            $totalPages = max(1, (int)ceil($totalRows / $limit));
            if ($page > $totalPages) {
                $page = $totalPages;
                $offset = ($page - 1) * $limit;
            }

            $data = $db->run("
                SELECT
                    oa.id, oa.order_id, oa.ign, oa.login, oa.password,
                    o.status, o.created_at, o.client_id,
                    c.username AS client_username
                FROM order_accounts oa
                LEFT JOIN orders o ON o.id = oa.order_id
                LEFT JOIN clients c ON c.id = o.client_id
                WHERE {$whereSql}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}
            ", ...$args) ?: [];

            view_file('admin/pages/orders/accounts', [
                'data' => $data,
                'page' => $page,
                'limit' => $limit,
                'search' => $search,
                'sort' => $sort,
                'dir' => strtolower($dir),
                'totalRows' => $totalRows,
                'totalPages' => $totalPages,
            ]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('order-screenshots', function () {
        global $is_admin, $db;
        if ($is_admin) {
            $allowedEmails = [
                'r.machmueller@gmx.de',
                'justsromail@freenet.de',
                'duck_sauce@live.de'
            ];

            if (!in_array(strtolower(trim((string) ADMIN_DATA['email'])), $allowedEmails, true)) {
                redirect_url('admin-area/orders');
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = (int)($_GET['limit'] ?? 50);
            $limit = max(25, min(100, $limit));
            $offset = ($page - 1) * $limit;
            $search = trim((string)($_GET['search'] ?? ''));
            $sort = strtolower(trim((string)($_GET['sort'] ?? 'id')));
            $dir = strtolower(trim((string)($_GET['dir'] ?? 'desc')));
            $dir = $dir === 'asc' ? 'ASC' : 'DESC';
            $sortMap = [
                'id' => 'os.id',
                'preview' => 'os.file_url',
                'order' => 'os.order_id',
                'booster' => 'b.username',
                'file' => 'os.file_url',
                'version' => 'os.file_version',
                'created' => 'os.created_at',
            ];
            if (!isset($sortMap[$sort])) $sort = 'id';
            $orderBy = $sortMap[$sort] . ' ' . $dir . ', os.id DESC';

            $where = ['1=1'];
            $args = [];

            if ($search !== '') {
                $where[] = "(
                    CAST(os.id AS CHAR) LIKE ?
                    OR CAST(os.order_id AS CHAR) LIKE ?
                    OR COALESCE(os.file_url, '') LIKE ?
                    OR COALESCE(os.file_version, '') LIKE ?
                    OR CAST(COALESCE(os.booster_id, 0) AS CHAR) LIKE ?
                    OR COALESCE(b.username, '') LIKE ?
                )";
                $like = '%' . $search . '%';
                array_push($args, $like, $like, $like, $like, $like, $like);
            }

            $whereSql = implode(' AND ', $where);

            $totalRows = (int)($db->cell("
                SELECT COUNT(*)
                FROM order_screenshots os
                LEFT JOIN boosters b ON b.id = os.booster_id
                WHERE {$whereSql}
            ", ...$args) ?: 0);

            $totalPages = max(1, (int)ceil($totalRows / $limit));
            if ($page > $totalPages) {
                $page = $totalPages;
                $offset = ($page - 1) * $limit;
            }

            $data = $db->run("
                SELECT
                    os.id, os.order_id, os.file_url, os.file_version, os.created_at, os.booster_id,
                    b.username AS booster_username, b.icon AS booster_icon
                FROM order_screenshots os
                LEFT JOIN boosters b ON b.id = os.booster_id
                WHERE {$whereSql}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}
            ", ...$args) ?: [];

            view_file('admin/pages/orders/screenshots', [
                'data' => $data,
                'page' => $page,
                'limit' => $limit,
                'search' => $search,
                'sort' => $sort,
                'dir' => strtolower($dir),
                'totalRows' => $totalRows,
                'totalPages' => $totalPages,
            ]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('orders/data', function () {

        global $is_admin;

        if (!$is_admin) {
            http_response_code(401);
            exit;
        }

        // ======================================================
        // DataTables params
        // ======================================================
        $dt = array_merge($_GET, $_POST);

        $draw = intval($dt['draw'] ?? 1);
        $start = intval($dt['start'] ?? 0);
        $length = intval($dt['length'] ?? 12);

        $search = trim($dt['search']['value'] ?? '');

        $orderDir = strtolower($dt['order'][0]['dir'] ?? 'desc') === 'asc'
            ? 'ASC'
            : 'DESC';

        // Status filter
        $statusLabel = trim($dt['columns'][3]['search']['value'] ?? '');

        // Waiting-for-approval filter (set by pills)
        $waitingForApproval = false;
        if (!empty($dt['waiting_for_approval']) && (string)$dt['waiting_for_approval'] === '1') {
            $waitingForApproval = true;
        }
        if (strcasecmp($statusLabel, 'Waiting for Approval') === 0) {
            $waitingForApproval = true;
        }

        // Game filter (sent by game pills as 'league-of-legends', 'valorant', 'tft')
        $gameFilter = strtolower(trim($dt['game_filter'] ?? ''));

        $statusMap = [
            'Processing' => 'PAID',
            'Unpaid' => 'UNPAID',
            'In Progress' => 'IN_PROGRESS',
            'Paused' => 'PAUSED',
            'Completed' => 'COMPLETED',
            'Refunded' => 'REFUNDED'
        ];

        $status = $statusMap[$statusLabel] ?? null;

        // Refunded should match both legacy REFUND and new REFUNDED values
        if (strcasecmp($statusLabel, 'Refunded') === 0) {
            $status = 'REFUNDED';
        }


        // ======================================================
        // WHERE clause
        // ======================================================
        $where = [];

        if ($waitingForApproval) {
            $where[] = "o.waiting_for_approval = 1 AND o.status NOT IN ('COMPLETED','REFUND','REFUNDED')";
        } elseif ($status) {
            if (strtoupper((string)$status) === 'REFUNDED') {
                // match both legacy and new refunded values
                $where[] = "o.status IN ('REFUND','REFUNDED')";
                $where[] = "(o.waiting_for_approval IS NULL OR o.waiting_for_approval = 0)";
            } else {
                $where[] = "o.status = '" . addslashes($status) . "'";
                // When filtering by a normal status, exclude approval-queue orders
                if (strtoupper((string)$status) !== 'COMPLETED') {
                    $where[] = "(o.waiting_for_approval IS NULL OR o.waiting_for_approval = 0)";
                }
            }
        }

        // Game filter — pills send normalized short slugs ('lol','val','tft','lol_classic','egirl',
        // or any dynamically added game's own slug); map to the actual boost_forms.game values.
        $onlyEgirl = ($gameFilter === 'egirl');
        if ($onlyEgirl) {
            $where[] = '1 = 0'; // normal orders excluded entirely when filtering for E-Girl only
        } elseif ($gameFilter !== '') {
            $gf = addslashes($gameFilter);
            if ($gameFilter === 'lol_classic') {
                $where[] = "(f.game = 'lol_classic' OR f.game = 'lol-classic' OR f.game LIKE '%classic%')";
            } elseif ($gameFilter === 'lol') {
                $where[] = "(f.game = 'lol' OR f.game = 'league-of-legends')";
            } elseif ($gameFilter === 'val') {
                $where[] = "(f.game = 'val' OR f.game = 'valorant')";
            } elseif ($gameFilter === 'tft') {
                $where[] = "(f.game = 'tft' OR f.game = 'teamfight-tactics')";
            } else {
                $where[] = "f.game = '{$gf}'";
            }
        }

        if ($search !== '') {
            $s = addslashes($search);
            $where[] = "(
            o.id LIKE '%{$s}%'
            OR b.username LIKE '%{$s}%'
            OR f.name LIKE '%{$s}%'
            OR f.type LIKE '%{$s}%'
        )";
        }

        $whereSql = $where
            ? 'WHERE ' . implode(' AND ', $where)
            : '';
        // ======================================================
        // Total / filtered counts (normal + egirl)
        // ======================================================
        $totalRow = db_run_query("
        SELECT COUNT(*) AS cnt
        FROM orders
    ");

        $recordsTotal = (int) $totalRow[0]['cnt'];

        $filteredRow = db_run_query("
        SELECT COUNT(*) AS cnt
        FROM orders o
        LEFT JOIN boosters b ON b.id = o.booster_id
        LEFT JOIN boost_forms f ON f.id = o.form_id
        {$whereSql}
    ");

        $recordsFiltered = (int) $filteredRow[0]['cnt'];

        $egirl_status_cond = '';
        $egirl_search_cond = '';
        $include_egirl = (($onlyEgirl || $gameFilter === '') && !$waitingForApproval);

        if ($include_egirl) {
            if ($status) {
                if (strtoupper((string)$status) === 'REFUNDED') {
                    $egirl_status_cond = "AND eo.status IN ('REFUND','REFUNDED')";
                } else {
                    $egirl_status_cond = "AND eo.status = '" . addslashes($status) . "'";
                }
            }
            if ($search !== '') {
                $s3 = addslashes($search);
                $egirl_search_cond = "AND (CAST(eo.id AS CHAR) LIKE '%{$s3}%' OR b2.username LIKE '%{$s3}%' OR eo.service_title LIKE '%{$s3}%')";
            }

            $egirl_total_cnt = (int)(db_run_query("SELECT COUNT(*) AS cnt FROM egirl_orders")[0]['cnt'] ?? 0);
            $egirl_filtered_cnt_row = db_run_query("
                SELECT COUNT(*) AS cnt
                FROM egirl_orders eo
                LEFT JOIN boosters b2 ON b2.id = eo.egirl_id
                WHERE 1=1 {$egirl_status_cond} {$egirl_search_cond}
            ") ?? [];
            $egirl_filtered_cnt = (int)($egirl_filtered_cnt_row[0]['cnt'] ?? 0);

            $recordsTotal += $egirl_total_cnt;
            $recordsFiltered += $egirl_filtered_cnt;
        }

        // ======================================================
        // Main data query (page ids first, then detail rows per type)
        // ======================================================
        if ($include_egirl) {
            $pageRows = db_run_query("
                SELECT src_type, row_id, created_at
                FROM (
                    SELECT 'normal' AS src_type, o.id AS row_id, o.created_at
                    FROM orders o
                    LEFT JOIN boosters b ON b.id = o.booster_id
                    LEFT JOIN boost_forms f ON f.id = o.form_id
                    {$whereSql}

                    UNION ALL

                    SELECT 'egirl' AS src_type, eo.id AS row_id, eo.created_at
                    FROM egirl_orders eo
                    LEFT JOIN boosters b2 ON b2.id = eo.egirl_id
                    WHERE 1=1 {$egirl_status_cond} {$egirl_search_cond}
                ) combined_rows
                ORDER BY created_at {$orderDir}
                LIMIT {$length} OFFSET {$start}
            ") ?? [];

            $normalIds = [];
            $egirlIds = [];
            foreach ($pageRows as $pageRow) {
                if (($pageRow['src_type'] ?? '') === 'egirl') {
                    $egirlIds[] = (int)$pageRow['row_id'];
                } else {
                    $normalIds[] = (int)$pageRow['row_id'];
                }
            }

            $normalMap = [];
            if (!empty($normalIds)) {
                $normalIdSql = implode(',', array_map('intval', array_unique($normalIds)));
                $normalRows = db_run_query("
                    SELECT
                        o.id,
                        o.form_id,
                        o.price,
                        o.currency,
                        o.status,
                        o.booster_id,
                        o.created_at,
                        o.booster_cut,
                        o.waiting_for_approval,

                        b.username,
                        b.icon AS booster_icon,

                        f.name,
                        f.type,
                        f.icon,
                        f.game,

                        oo.server,
                        oo.hours,
                        oo.boosters,
                        oo.start_tier,
                        oo.start_division,
                        oo.end_tier,
                        oo.end_division,
                        oo.start_lp,
                        oo.end_lp,
                        oo.matches,
                        oo.queue_type,
                        oo.is_duo,
                        oo.coach_type,
                        oo.roles,
                        oo.champions,
                        oo.agents,
                        oo.vpn_country,
                        oo.flash_position,
                        oo.is_priority,
                        oo.is_streaming,
                        oo.is_solo_only,
                        oo.is_bonus_win,
                        oo.is_hidden_duo,
                        oo.is_offline_mode,
                        oo.is_coaching
                    FROM orders o
                    LEFT JOIN boosters b ON b.id = o.booster_id
                    LEFT JOIN boost_forms f ON f.id = o.form_id
                    LEFT JOIN order_options oo ON oo.order_id = o.id
                    WHERE o.id IN ({$normalIdSql})
                ") ?? [];

                foreach ($normalRows as $normalRow) {
                    $normalRow['__row_kind'] = 'normal';
                    $normalMap[(int)$normalRow['id']] = $normalRow;
                }
            }

            $egirlMap = [];
            if (!empty($egirlIds)) {
                $egirlIdSql = implode(',', array_map('intval', array_unique($egirlIds)));
                $egirlRows = db_run_query("
                    SELECT
                        eo.id AS eg_id,
                        eo.service_title,
                        eo.price AS eg_price,
                        eo.currency AS eg_currency,
                        eo.status AS eg_status,
                        eo.created_at AS eg_created_at,
                        eo.egirl_id,
                        b2.username AS egirl_username,
                        b2.icon AS egirl_icon
                    FROM egirl_orders eo
                    LEFT JOIN boosters b2 ON b2.id = eo.egirl_id
                    WHERE eo.id IN ({$egirlIdSql})
                ") ?? [];

                foreach ($egirlRows as $egirlRow) {
                    $egirlRow['__row_kind'] = 'egirl';
                    $egirlMap[(int)$egirlRow['eg_id']] = $egirlRow;
                }
            }

            $rows = [];
            foreach ($pageRows as $pageRow) {
                $rowId = (int)($pageRow['row_id'] ?? 0);
                if (($pageRow['src_type'] ?? '') === 'egirl') {
                    if (isset($egirlMap[$rowId])) {
                        $rows[] = $egirlMap[$rowId];
                    }
                } else {
                    if (isset($normalMap[$rowId])) {
                        $rows[] = $normalMap[$rowId];
                    }
                }
            }
        } else {
            $rows = db_run_query("
            SELECT
                o.id,
                o.form_id,
                o.price,
                o.currency,
                o.status,
                o.booster_id,
                o.created_at,
                o.booster_cut,
                o.waiting_for_approval,

                b.username,
                b.icon AS booster_icon,

                f.name,
                f.type,
                f.icon,
                f.game,

                oo.server,
                oo.hours,
                oo.boosters,
                oo.start_tier,
                oo.start_division,
                oo.end_tier,
                oo.end_division,
                oo.start_lp,
                oo.end_lp,
                oo.matches,
                oo.queue_type,
                oo.is_duo,
                oo.coach_type,
                oo.roles,
                oo.champions,
                oo.agents,
                oo.vpn_country,
                oo.flash_position,
                oo.is_priority,
                oo.is_streaming,
                oo.is_solo_only,
                oo.is_bonus_win,
                oo.is_hidden_duo,
                oo.is_offline_mode,
                oo.is_coaching

            FROM orders o
            LEFT JOIN boosters b ON b.id = o.booster_id
            LEFT JOIN boost_forms f ON f.id = o.form_id
            LEFT JOIN order_options oo ON oo.order_id = o.id

            {$whereSql}

            ORDER BY o.created_at {$orderDir}
            LIMIT {$length} OFFSET {$start}
        ") ?? [];

            foreach ($rows as &$row) {
                $row['__row_kind'] = 'normal';
            }
            unset($row);
        }

        // ======================================================
        // Format output
        // ======================================================
        
        $data = [];
        $adminOrderTeamBoosters = [];
        $normalOrderIds = [];
        foreach ($rows as $teamRow) {
            if (($teamRow['__row_kind'] ?? 'normal') === 'normal' && in_array((int)($teamRow['form_id'] ?? 0), [4, 19, 29], true)) {
                $normalOrderIds[] = (int)($teamRow['id'] ?? 0);
            }
        }
        if (!empty($normalOrderIds)) {
            $teamOrderIdSql = implode(',', array_map('intval', array_unique(array_filter($normalOrderIds))));
            if ($teamOrderIdSql !== '') {
                try {
                    $teamRows = db_run_query("
                        SELECT ob.order_id, ob.booster_id, b.username, b.icon
                          FROM order_boosters ob
                          INNER JOIN boosters b ON b.id = ob.booster_id
                         WHERE ob.order_id IN ({$teamOrderIdSql})
                           AND ob.status = 'ACTIVE'
                         ORDER BY ob.order_id ASC, ob.slot_no ASC, ob.id ASC
                    ") ?: [];
                    foreach ($teamRows as $teamBooster) {
                        $adminOrderTeamBoosters[(int)$teamBooster['order_id']][] = $teamBooster;
                    }
                } catch (Throwable $e) {}
            }
        }

        foreach ($rows as $r) {
            if (($r['__row_kind'] ?? 'normal') === 'egirl') {
                $egId    = (int)$r['eg_id'];
                $egTitle = htmlspecialchars($r['service_title'] ?? 'E-Girl Session');

                $egirlIconHtml =
                    '<img src="' . ASSET_URL . '/website/images/gg-girl.svg" alt="" style="width:34px;height:34px;border-radius:.5rem;object-fit:cover;display:block;">';

                $titleHtmlEg =
                    '<div class="lb-titlewrap" data-game="egirl">'
                    . '<div class="d-flex align-items-center gap-2" style="min-width:0">'
                    . $egirlIconHtml
                    . '<div style="min-width:0"><div class="lb-title-name" style="display:flex;align-items:center;gap:5px;">'
                    . '<span style="font-size:.62rem;padding:1px 6px;border-radius:999px;background:rgba(168,85,247,.18);border:1px solid rgba(168,85,247,.28);color:#c084fc;font-weight:800;text-transform:uppercase;white-space:nowrap;flex-shrink:0">E-GIRL</span>'
                    . $egTitle . '</div>'
                    . '<div class="lb-title-sub text-muted" style="font-size:.78rem">E-Girl Booking</div></div>'
                    . '</div></div>';

                $orderIdHtmlEg =
                    '<div class="lb-oid-row">'
                    . '<a class="lb-oid-link" href="' . ADMN_URL . '/egirl/order/' . $egId . '">#eg' . $egId . '</a>'
                    . '<button type="button" class="lb-copy-btn" data-copy="#eg' . $egId . '" aria-label="Copy"><i class="fa-regular fa-copy"></i></button>'
                    . '</div>'
                    . '<div class="lb-orderid-sub"><span class="lb-pill lb-pill-opt"><i class="fa-solid fa-microphone"></i><span>Voice</span></span></div>';

                $boosterHtmlEg = !empty($r['egirl_id'])
                    ? '<a href="' . ADMN_URL . '/egirl/' . (int)$r['egirl_id'] . '" class="text-reset text-decoration-none d-inline-block">'
                        . util_format_user($r['egirl_username'] ?? '—', $r['egirl_icon'] ?? '')
                        . '</a>'
                    : '-';

                $statusHtmlEg  = strtoupper((string)($r['eg_status'] ?? 'UNPAID'));
                $priceHtmlEg   = util_format_currency_display($r['eg_currency'] ?? 'EUR')
                                 . util_format_price_display($r['eg_price'] ?? 0);
                $createdHtmlEg = util_format_date_display($r['eg_created_at']);
                $egStatusRaw = strtoupper((string)($r['eg_status'] ?? 'UNPAID'));
                $actionHtmlEg =
                    '<div class="d-inline-flex gap-2 justify-content-end">'
                    . '<a href="' . ADMN_URL . '/egirl/order/' . $egId . '" class="btn btn-white btn-sm">'
                    . '<i class="fa-duotone fa-eye me-1"></i> View</a>';
                if ($egStatusRaw === 'UNPAID') {
                    $actionHtmlEg .=
                        '<button type="button" class="btn btn-soft-danger btn-sm js-admin-delete-order" '
                        . 'data-order-id="eg_' . $egId . '" data-order-status="UNPAID" data-order-type="egirl">'
                        . '<i class="fa-duotone fa-trash-can me-1"></i> Delete</button>';
                }
                $actionHtmlEg .= '</div>';

                $data[] = [
                    $titleHtmlEg, $orderIdHtmlEg, $boosterHtmlEg,
                    $statusHtmlEg, $priceHtmlEg, $createdHtmlEg, $actionHtmlEg,
                ];
                continue;
            }


            $fid = (int)($r['form_id'] ?? 0);
            $isCoachingForm = in_array($fid, [15,16], true);

            // ------------------------------
            // Build pills (Title vs OrderID)
            // ------------------------------
            $chipsTitle = [];
            $chipsOrder = [];
            // Queue (SOLO/DUO) hidden in admin list
            // Helpers
            $boolOn = function($v) {
                return !empty($v) && (string)$v !== '0';
            };
            $addChip = function (&$arr, $html) {
                $arr[] = $html;
            };
            $svg = function($src) {
                return '<img src="' . $src . '" alt="option_icon" class="lb-svgico">';
            };

            // Extra options (always under Title if present)
            if ($boolOn($r['is_priority'] ?? null)) {
                $addChip($chipsTitle, '<span class="lb-pill lb-pill-opt">'.$svg('https://lolboost.gg/public/assets/website/images/boost-forms/priority.svg').'<span>Priority</span></span>');
            }
            if ($boolOn($r['is_bonus_win'] ?? null)) {
                $addChip($chipsTitle, '<span class="lb-pill lb-pill-opt">'.$svg('https://lolboost.gg/public/assets/website/images/boost-forms/bonus-win1.svg').'<span>Bonus Win</span></span>');
            }

            // Remaining options under Order ID
            if ($boolOn($r['is_streaming'] ?? null)) {
                $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt">'.$svg('https://lolboost.gg/public/assets/website/images/boost-forms/stream-games1.svg').'<span>Streaming</span></span>');
            }
            if ($boolOn($r['is_solo_only'] ?? null)) {
                $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt">'.$svg('https://lolboost.gg/public/assets/website/images/boost-forms/solo-queue1.svg').'<span>Solo Only</span></span>');
            }
            // Voice chat (coaching option)
            if ($boolOn($r['is_coaching'] ?? null) || !empty($r['coach_type'])) {
                $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt"><i class="fa-solid fa-microphone-lines"></i><span>Voice Chat</span></span>');
            }
            if ($boolOn($r['is_hidden_duo'] ?? null)) {
                $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt"><i class="fa-duotone fa-user-secret"></i><span>Hidden Duo</span></span>');
            }

            // Champions / Roles hover triggers (desktop hover, mobile shows inline list)
            $championsSelected = isset($r['champions']) && trim((string)$r['champions']) !== '' && strtolower(trim((string)$r['champions'])) !== 'null';
            $rolesSelected = isset($r['roles']) && trim((string)$r['roles']) !== '' && strtolower(trim((string)$r['roles'])) !== 'null';

            $championsHover = '';
            $rolesHover = '';
            $mobileExtras = '';

            if ($championsSelected) {
                $championsContent = util_format_champions($r['champions']);
                $championsHover = '<span class="lb-hoverwrap d-none d-md-inline-flex">
                    <span class="lb-pill lb-pill-opt"><i class="fa fa-helmet-battle"></i><span>Champions</span></span>
                    <span class="lb-hovercard">'.$championsContent.'</span>
                </span>';
                $mobileExtras .= '<div class="lb-mobile-extra d-md-none"><div class="lb-mobile-extra-label">Champions</div><div class="lb-mobile-extra-body">'.$championsContent.'</div></div>';
            }

            if ($rolesSelected) {
                $rolesContent = util_format_roles($r['roles']);
                $rolesHover = '<span class="lb-hoverwrap d-none d-md-inline-flex">
                    <span class="lb-pill lb-pill-opt"><i class="fa-solid fa-asterisk"></i><span>Roles</span></span>
                    <span class="lb-hovercard">'.$rolesContent.'</span>
                </span>';
                $mobileExtras .= '<div class="lb-mobile-extra d-md-none"><div class="lb-mobile-extra-label">Roles</div><div class="lb-mobile-extra-body">'.$rolesContent.'</div></div>';
            }

            // Title block (existing util) + our pills
            $titleHtml = util_format_boost_form($r);
            if (!empty($chipsTitle)) {
                $titleHtml .= '<div class="lb-meta-row">'.implode('', $chipsTitle).'</div>';
            }

            // Add game key for client-side badge overlay. lb_game_icon_attr also lets the
            // JS badge fall back to the game's own icon (from the admin Games area) for any
            // dynamically added game beyond the hardcoded lol/val/tft/classic set.
            $lb_game_raw = strtolower(trim((string)($r['game'] ?? '')));
            $lb_game_attr = htmlspecialchars($lb_game_raw, ENT_QUOTES, 'UTF-8');
            $lb_game_icon = function_exists('util_game_icon_url') ? util_game_icon_url($lb_game_raw) : '';
            $lb_game_icon_attr = htmlspecialchars($lb_game_icon, ENT_QUOTES, 'UTF-8');
            $titleHtml = '<div class="lb-titlewrap" data-game="' . $lb_game_attr . '" data-game-icon="' . $lb_game_icon_attr . '">' . $titleHtml . '</div>';


            // Order ID block: link + copy + pills + hover triggers
            $orderUrl = ADMN_URL . '/order/' . esc($r['id']);
            $copyVal = '#' . esc($r['id']);

            $orderIdHtml  = '<div class="lb-oid-row">';
            $orderIdHtml .=   '<a class="lb-oid-link" href="'.$orderUrl.'">#'.esc($r['id']).'</a>';
            $orderIdHtml .=   '<button type="button" class="lb-copy-btn" data-copy="'.$copyVal.'" aria-label="Copy Order ID"><i class="fa-regular fa-copy"></i></button>';
            $orderIdHtml .= '</div>';

            $sub = '';
            if (!empty($chipsOrder) || $championsHover || $rolesHover) {
                // limit chips to 3 (keep row compact), rest as +X
                $max = 3;
                $shown = array_slice($chipsOrder, 0, $max);
                $rest = max(0, count($chipsOrder) - count($shown));
                if ($rest > 0) {
                    $shown[] = '<span class="lb-pill lb-pill-more">+'.(int)$rest.'</span>';
                }
                $sub = '<div class="lb-orderid-sub">'.implode('', $shown).$championsHover.$rolesHover.'</div>';
            }
            $orderIdHtml .= $sub . $mobileExtras;

            // Booster
            $teamBoostersForOrder = $adminOrderTeamBoosters[(int)($r['id'] ?? 0)] ?? [];
            if (!empty($teamBoostersForOrder)) {
                $teamBoosterHtml = [];
                foreach ($teamBoostersForOrder as $teamBooster) {
                    $teamBoosterHtml[] =
                        '<a href="' . ADMN_URL . '/booster/' . (int)$teamBooster['booster_id'] . '" class="d-inline-block me-1 mb-1">'
                        . util_format_user($teamBooster['username'] ?? 'Booster', $teamBooster['icon'] ?? '')
                        . '</a>';
                }
                $boosterHtml = '<div class="d-flex flex-wrap align-items-center">' . implode('', $teamBoosterHtml) . '</div>';
            } elseif (!empty($r['booster_id'])) {
                $boosterHtml =
                    '<a href="' . ADMN_URL . '/booster/' . esc($r['booster_id']) . '">' .
                    util_format_user($r['username'], $r['booster_icon']) .
                    '</a>';
            } else {
                $boosterHtml = '-';
            }

            // Status (raw text; UI converts to badge)
            $statusHtml = (!empty($r['waiting_for_approval']) && (int)$r['waiting_for_approval'] === 1 && !in_array(strtoupper((string)$r['status']), ['COMPLETED','REFUND','REFUNDED'], true))
                ? 'WAITING FOR APPROVAL'
                : $r['status'];

            // Price
            $priceHtml =
                util_format_currency_display($r['currency']) .
                util_format_price_display($r['price']);

            // Booster cut (shown under price)
            $isTeamOrder = in_array((int)($r['form_id'] ?? 0), [4, 19, 29], true)
                && max(1, (int)($r['boosters'] ?? 1)) > 1;
            $cutPct = $isTeamOrder ? 50.0 : (isset($r['booster_cut']) ? (float)$r['booster_cut'] : 0);

            if ($isTeamOrder && is_numeric($r['price'])) {
                $perBoosterCut = (int)floor(
                    (((float)$r['price'] * 50) / 100)
                    / max(1, (int)($r['boosters'] ?? 1))
                );
                $priceHtml .= '<div class="small text-muted">('
                    . util_format_currency_display($r['currency'])
                    . util_format_price_display($perBoosterCut)
                    . ' / Booster)</div>';
            } elseif ($cutPct > 0 && is_numeric($r['price'])) {
                $cutAmount = (int) round(((float)$r['price']) * ($cutPct / 100));

                $priceHtml .= '<div class="small text-muted">('
                    . util_format_currency_display($r['currency'])
                    . util_format_price_display($cutAmount)
                    . ' × '
                    . rtrim(rtrim(number_format($cutPct, 2, '.', ''), '0'), '.')
                    . '%)</div>';
            }

            // Created
            $createdHtml = util_format_date_display($r['created_at']);

            // Action
            $actionHtml =
                '<div class="d-inline-flex gap-2 justify-content-end">'
                . '<a href="' . ADMN_URL . '/order/' . esc($r['id']) . '" class="btn btn-white btn-sm">'
                . '<i class="fa-duotone fa-eye me-1"></i> View'
                . '</a>';

            // Allow deleting directly from the list when UNPAID
            if (($r['status'] ?? null) === 'UNPAID') {
                $actionHtml .=
                    '<button type="button" class="btn btn-soft-danger btn-sm js-admin-delete-order" '
                    . 'data-order-id="' . esc($r['id']) . '" data-order-status="UNPAID">'
                    . '<i class="fa-duotone fa-trash-can me-1"></i> Delete'
                    . '</button>';
            }

            $actionHtml .= '</div>';

            $data[] = [
                $titleHtml,
                $orderIdHtml,
                $boosterHtml,
                $statusHtml,
                $priceHtml,
                $createdHtml,
                $actionHtml
            ];
        }

        // ======================================================
        // Response
        // ======================================================
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);

        exit;
    });

// ----- ALT version of $router->get('orders/data', ...) BEGIN -----
//     $router->get('orders/data', function () {
// 
//         global $is_admin;
// 
//         if (!$is_admin) {
//             http_response_code(401);
//             exit;
//         }
// 
//         // ======================================================
//         // DataTables params
//         // ======================================================
//         $dt = array_merge($_GET, $_POST);
// 
//         $draw = intval($dt['draw'] ?? 1);
//         $start = intval($dt['start'] ?? 0);
//         $length = intval($dt['length'] ?? 12);
// 
//         $search = trim($dt['search']['value'] ?? '');
// 
//         $orderDir = strtolower($dt['order'][0]['dir'] ?? 'desc') === 'asc'
//             ? 'ASC'
//             : 'DESC';
// 
//         // Status filter
//         $statusLabel = trim($dt['columns'][3]['search']['value'] ?? '');
// 
//         // Waiting-for-approval filter (set by pills)
//         $waitingForApproval = false;
//         if (!empty($dt['waiting_for_approval']) && (string) $dt['waiting_for_approval'] === '1') {
//             $waitingForApproval = true;
//         }
//         if (strcasecmp($statusLabel, 'Waiting for Approval') === 0) {
//             $waitingForApproval = true;
//         }
// 
//         $statusMap = [
//             'Processing' => 'PAID',
//             'Unpaid' => 'UNPAID',
//             'In Progress' => 'IN_PROGRESS',
//             'Paused' => 'PAUSED',
//             'Completed' => 'COMPLETED',
//             'Refunded' => 'REFUNDED'
//         ];
// 
//         $status = $statusMap[$statusLabel] ?? null;
// 
//         // Refunded should match both legacy REFUND and new REFUNDED values
//         if (strcasecmp($statusLabel, 'Refunded') === 0) {
//             $status = 'REFUNDED';
//         }
// 
// 
//         // ======================================================
//         // WHERE clause
//         // ======================================================
//         $where = [];
// 
//         if ($waitingForApproval) {
//             $where[] = "o.waiting_for_approval = 1 AND o.status NOT IN ('COMPLETED','REFUND','REFUNDED')";
//         } elseif ($status) {
//             if (strtoupper((string) $status) === 'REFUNDED') {
//                 // match both legacy and new refunded values
//                 $where[] = "o.status IN ('REFUND','REFUNDED')";
//                 $where[] = "(o.waiting_for_approval IS NULL OR o.waiting_for_approval = 0)";
//             } else {
//                 $where[] = "o.status = '" . addslashes($status) . "'";
//                 // When filtering by a normal status, exclude approval-queue orders
//                 if (strtoupper((string) $status) !== 'COMPLETED') {
//                     $where[] = "(o.waiting_for_approval IS NULL OR o.waiting_for_approval = 0)";
//                 }
//             }
//         }
// 
//         if ($search !== '') {
//             $s = addslashes($search);
//             $where[] = "(
//             o.id LIKE '%{$s}%'
//             OR b.username LIKE '%{$s}%'
//             OR f.name LIKE '%{$s}%'
//             OR f.type LIKE '%{$s}%'
//         )";
//         }
// 
//         $whereSql = $where
//             ? 'WHERE ' . implode(' AND ', $where)
//             : '';
// 
//         // ======================================================
//         // Total count
//         // ======================================================
//         $totalRow = db_run_query("
//         SELECT COUNT(*) AS cnt
//         FROM orders
//     ");
// 
//         $recordsTotal = (int) $totalRow[0]['cnt'];
// 
//         // ======================================================
//         // Filtered count
//         // ======================================================
//         $filteredRow = db_run_query("
//         SELECT COUNT(*) AS cnt
//         FROM orders o
//         LEFT JOIN boosters b ON b.id = o.booster_id
//         LEFT JOIN boost_forms f ON f.id = o.form_id
//         {$whereSql}
//     ");
// 
//         $recordsFiltered = (int) $filteredRow[0]['cnt'];
// 
//         // ======================================================
//         // Main data query
//         // ======================================================
//         $rows = db_run_query("
//         SELECT
//             o.id,
//             o.form_id,
//             o.price,
//             o.currency,
//             o.status,
//             o.booster_id,
//             o.created_at,
//             o.booster_cut,
//             o.waiting_for_approval,
// 
//             b.username,
//             b.icon AS booster_icon,
// 
//             f.name,
//             f.type,
//             f.icon,
//             f.game,
// 
//             oo.server,
//             oo.hours,
//             oo.start_tier,
//             oo.start_division,
//             oo.end_tier,
//             oo.end_division,
//             oo.start_lp,
//             oo.end_lp,
//             oo.matches,
//             oo.queue_type,
//             oo.is_duo,
//             oo.coach_type,
//             oo.roles,
//             oo.champions,
//             oo.agents,
//             oo.vpn_country,
//             oo.flash_position,
//             oo.is_priority,
//             oo.is_streaming,
//             oo.is_solo_only,
//             oo.is_bonus_win,
//             oo.is_hidden_duo,
//             oo.is_offline_mode,
//             oo.is_coaching
// 
//         FROM orders o
//         LEFT JOIN boosters b ON b.id = o.booster_id
//         LEFT JOIN boost_forms f ON f.id = o.form_id
//         LEFT JOIN order_options oo ON oo.order_id = o.id
// 
//         {$whereSql}
// 
//         ORDER BY o.created_at {$orderDir}
//         LIMIT {$length} OFFSET {$start}
//     ");
// 
//         // ======================================================
//         // Format output
//         // ======================================================
// 
//         $data = [];
// 
//         foreach ($rows as $r) {
// 
//             $fid = (int) ($r['form_id'] ?? 0);
//             $isCoachingForm = in_array($fid, [15, 16], true);
// 
//             // ------------------------------
//             // Build pills (Title vs OrderID)
//             // ------------------------------
//             $chipsTitle = [];
//             $chipsOrder = [];
// 
//             // SOLO / DUO (only if not coaching form)
//             if (!$isCoachingForm) {
//                 $isDuo = !empty($r['is_duo']) && (int) $r['is_duo'] === 1;
//                 $chipsTitle[] = $isDuo
//                     ? '<span class="lb-pill lb-pill-duo"><span class="lb-dot"></span> DUO</span>'
//                     : '<span class="lb-pill lb-pill-solo"><span class="lb-dot"></span> SOLO</span>';
//             }
// 
//             // Helpers
//             $boolOn = function ($v) {
//                 return !empty($v) && (string) $v !== '0';
//             };
//             $addChip = function (&$arr, $html) {
//                 $arr[] = $html;
//             };
//             $svg = function ($src) {
//                 return '<img src="' . $src . '" alt="option_icon" class="lb-svgico">';
//             };
// 
//             // Extra options (always under Title if present)
//             if ($boolOn($r['is_priority'] ?? null)) {
//                 $addChip($chipsTitle, '<span class="lb-pill lb-pill-opt">' . $svg('https://lolboost.gg/public/assets/website/images/boost-forms/priority.svg') . '<span>Priority</span></span>');
//             }
//             if ($boolOn($r['is_bonus_win'] ?? null)) {
//                 $addChip($chipsTitle, '<span class="lb-pill lb-pill-opt">' . $svg('https://lolboost.gg/public/assets/website/images/boost-forms/bonus-win1.svg') . '<span>Bonus Win</span></span>');
//             }
// 
//             // Remaining options under Order ID
//             if ($boolOn($r['is_streaming'] ?? null)) {
//                 $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt">' . $svg('https://lolboost.gg/public/assets/website/images/boost-forms/stream-games1.svg') . '<span>Streaming</span></span>');
//             }
//             if ($boolOn($r['is_solo_only'] ?? null)) {
//                 $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt">' . $svg('https://lolboost.gg/public/assets/website/images/boost-forms/solo-queue1.svg') . '<span>Solo Only</span></span>');
//             }
//             // Voice chat (coaching option)
//             if ($boolOn($r['is_coaching'] ?? null) || !empty($r['coach_type'])) {
//                 $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt"><i class="fa-solid fa-microphone-lines"></i><span>Voice Chat</span></span>');
//             }
//             if ($boolOn($r['is_hidden_duo'] ?? null)) {
//                 $addChip($chipsOrder, '<span class="lb-pill lb-pill-opt"><i class="fa-duotone fa-user-secret"></i><span>Hidden Duo</span></span>');
//             }
// 
//             // Champions / Roles hover triggers (desktop hover, mobile shows inline list)
//             $championsSelected = isset($r['champions']) && trim((string) $r['champions']) !== '' && strtolower(trim((string) $r['champions'])) !== 'null';
//             $rolesSelected = isset($r['roles']) && trim((string) $r['roles']) !== '' && strtolower(trim((string) $r['roles'])) !== 'null';
// 
//             $championsHover = '';
//             $rolesHover = '';
//             $mobileExtras = '';
// 
//             if ($championsSelected) {
//                 $championsContent = util_format_champions($r['champions']);
//                 $championsHover = '<span class="lb-hoverwrap d-none d-md-inline-flex">
//                     <span class="lb-pill lb-pill-opt"><i class="fa fa-helmet-battle"></i><span>Champions</span></span>
//                     <span class="lb-hovercard">' . $championsContent . '</span>
//                 </span>';
//                 $mobileExtras .= '<div class="lb-mobile-extra d-md-none"><div class="lb-mobile-extra-label">Champions</div><div class="lb-mobile-extra-body">' . $championsContent . '</div></div>';
//             }
// 
//             if ($rolesSelected) {
//                 $rolesContent = util_format_roles($r['roles']);
//                 $rolesHover = '<span class="lb-hoverwrap d-none d-md-inline-flex">
//                     <span class="lb-pill lb-pill-opt"><i class="fa-solid fa-asterisk"></i><span>Roles</span></span>
//                     <span class="lb-hovercard">' . $rolesContent . '</span>
//                 </span>';
//                 $mobileExtras .= '<div class="lb-mobile-extra d-md-none"><div class="lb-mobile-extra-label">Roles</div><div class="lb-mobile-extra-body">' . $rolesContent . '</div></div>';
//             }
// 
//             // Title block (existing util) + our pills
//             $titleHtml = util_format_boost_form($r);
//             if (!empty($chipsTitle)) {
//                 $titleHtml .= '<div class="lb-meta-row">' . implode('', $chipsTitle) . '</div>';
//             }
// 
//             // Order ID block: link + copy + pills + hover triggers
//             $orderUrl = ADMN_URL . '/order/' . esc($r['id']);
//             $copyVal = '#' . esc($r['id']);
// 
//             $orderIdHtml = '<div class="lb-oid-row">';
//             $orderIdHtml .= '<a class="lb-oid-link" href="' . $orderUrl . '">#' . esc($r['id']) . '</a>';
//             $orderIdHtml .= '<button type="button" class="lb-copy-btn" data-copy="' . $copyVal . '" aria-label="Copy Order ID"><i class="fa-regular fa-copy"></i></button>';
//             $orderIdHtml .= '</div>';
// 
//             $sub = '';
//             if (!empty($chipsOrder) || $championsHover || $rolesHover) {
//                 // limit chips to 3 (keep row compact), rest as +X
//                 $max = 3;
//                 $shown = array_slice($chipsOrder, 0, $max);
//                 $rest = max(0, count($chipsOrder) - count($shown));
//                 if ($rest > 0) {
//                     $shown[] = '<span class="lb-pill lb-pill-more">+' . (int) $rest . '</span>';
//                 }
//                 $sub = '<div class="lb-orderid-sub">' . implode('', $shown) . $championsHover . $rolesHover . '</div>';
//             }
//             $orderIdHtml .= $sub . $mobileExtras;
// 
//             // Booster
//             if (!empty($r['booster_id'])) {
//                 $boosterHtml =
//                     '<a href="' . ADMN_URL . '/booster/' . esc($r['booster_id']) . '">' .
//                     util_format_user($r['username'], $r['booster_icon']) .
//                     '</a>';
//             } else {
//                 $boosterHtml = '-';
//             }
// 
//             // Status (raw text; UI converts to badge)
//             $statusHtml = (!empty($r['waiting_for_approval']) && (int) $r['waiting_for_approval'] === 1 && !in_array(strtoupper((string) $r['status']), ['COMPLETED', 'REFUND', 'REFUNDED'], true))
//                 ? 'WAITING FOR APPROVAL'
//                 : $r['status'];
// 
//             // Price
//             $priceHtml =
//                 util_format_currency_display($r['currency']) .
//                 util_format_price_display($r['price']);
// 
//             // Booster cut (shown under price)
//             $cutPct = isset($r['booster_cut']) ? (float) $r['booster_cut'] : 0;
// 
//             if ($cutPct > 0 && is_numeric($r['price'])) {
//                 $cutAmount = (int) round(((float) $r['price']) * ($cutPct / 100));
// 
//                 $priceHtml .= '<div class="small text-muted">('
//                     . util_format_currency_display($r['currency'])
//                     . util_format_price_display($cutAmount)
//                     . ' × '
//                     . rtrim(rtrim(number_format($cutPct, 2, '.', ''), '0'), '.')
//                     . '%)</div>';
//             }
// 
//             // Created
//             $createdHtml = util_format_date_display($r['created_at']);
// 
//             // Action
//             $actionHtml =
//                 '<a href="' . ADMN_URL . '/order/' . esc($r['id']) . '" class="btn btn-white btn-sm">
//                 <i class="fa-duotone fa-eye me-1"></i> View
//              </a>';
// 
//             $data[] = [
//                 $titleHtml,
//                 $orderIdHtml,
//                 $boosterHtml,
//                 $statusHtml,
//                 $priceHtml,
//                 $createdHtml,
//                 $actionHtml
//             ];
//         }
// 
// 
//         // ======================================================
//         // Response
//         // ======================================================
//         echo json_encode([
//             'draw' => $draw,
//             'recordsTotal' => $recordsTotal,
//             'recordsFiltered' => $recordsFiltered,
//             'data' => $data
//         ]);
// 
//         exit;
//     });
// ----- ALT version END -----


    $router->get('order/:id', function ($id) {
        global $is_admin;
        global $db;
        if ($is_admin) {
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'ziad202175@yahoo.com' &&
                ADMIN_DATA['email'] !== 'abdoazzam281@gmail.com' &&
                ADMIN_DATA['email'] !== 'hesham0elkomy@gmail.com' &&
                ADMIN_DATA['email'] !== 'samsayahix@gmail.com' &&
                ADMIN_DATA['email'] !== 'mostafa.frag.thefox@gmail.com' &&
                ADMIN_DATA['email'] !== 'nototakuulol@gmail.com' &&
                ADMIN_DATA['email'] !== 'duck_sauce@live.de'
            ) {
                redirect_url('admin-area/selling-accounts');
            }

            $id = intval($id);

            // Admin should be able to open ANY order reliably.
            // NOTE: This route previously (incorrectly) contained booster-only access logic and could redirect to /booster-area.
            $order = db_get_row('orders', ['id' => $id], 1);
            if (empty($order)) {
                redirect_url('admin-area/orders');
            }

            // Repair legacy primary-booster pointers for existing team orders.
            // The active memberships remain authoritative, so all accepted
            // boosters stay visible and retain their access.
            if (in_array((int)($order['form_id'] ?? 0), [4, 19, 29], true)
                && function_exists('lb_multi_booster_sync_primary')) {
                lb_multi_booster_sync_primary($id);
                $order = db_get_row('orders', ['id' => $id], 1) ?: $order;
            }

            $form = db_get_row('boost_forms', ['id' => $order['form_id'], 'select' => 'id,type,name,icon,game'], 1);

            // Pull full option/account context (admin view needs everything)
            $order_opts = db_get_row('order_options', ['order_id' => $order['id']], 1) ?: [];
            $order_acc  = db_get_row('order_accounts', ['order_id' => $order['id']], 1) ?: [];
            $order_progress = db_get_row('order_progress', ['order_id' => $order['id']], 1) ?: [];
            $riot_tracking_warning = null;

            // On page refresh, auto-backfill missing Riot PUUID for tracking.
            // Guard order: Riot ID must exist first.
            $riotId = trim((string)($order_acc['ign'] ?? ''));
            $server = trim((string)($order_opts['server'] ?? ''));
            $progressPuuid = trim((string)($order_progress['puuid'] ?? ''));
            $needsPuuid = ($riotId !== '') && (empty($order_progress) || $progressPuuid === '');

            if ($needsPuuid) {
                try {
                    $resolvedPuuid = riot_get_puuid($riotId, $server !== '' ? $server : 'euw');

                    if (!empty($resolvedPuuid)) {
                        if (empty($order_progress)) {
                            db_add_row('order_progress', [
                                'order_id' => $order['id'],
                                'puuid' => $resolvedPuuid,
                            ]);
                        } else {
                            db_update_row('order_progress', ['order_id' => $order['id']], [
                                'puuid' => $resolvedPuuid,
                            ]);
                        }

                        $order_progress = db_get_row('order_progress', ['order_id' => $order['id']], 1) ?: $order_progress;
                    } else {
                        $riot_tracking_warning = 'Riot account data not found. Order tracking will not run for this order.';
                    }
                } catch (Throwable $e) {
                    $riot_tracking_warning = 'Riot tracking error: ' . $e->getMessage();
                }
            }

            $ss = db_get_row('order_screenshots', [
                'order_id' => $order['id'],
                'order' => 'created_at,DESC',
                'limit' => 1
            ], 1);
            if (!empty($ss) && isset($ss['id'])) {
                $ss['screenshot_id'] = $ss['id'];
                unset($ss['id']);
            }
            $order = array_merge((array)($form ?: []), (array)$order_opts, (array)$order_acc, (array)($ss ?: []), (array)$order);
            $order['progress'] = lb_order_progress_ensure_start_rank((int) $order['id'], $order_progress);
            $invoice = db_get_row('invoices', ['order_id' => $order['id'], 'order_type' => 'order'], 1);

            if (isset($order['client_id'])) {
                $client = db_get_row('clients', ['id' => $order['client_id'], 'select' => 'id,username,icon'], 1);
                $order['client'] = $client;
            }

            if (isset($order['booster_id'])) {
                $booster = db_get_row('boosters', ['id' => $order['booster_id'], 'select' => 'id,username,icon'], 1);
                $order['booster'] = $booster;
            }

            $addons = get_addable_addons_for_order($order);
            $result = $db->run("
                SELECT COALESCE(SUM(total_price), 0) AS total
                FROM invoices
                WHERE order_type = 'addon'
                AND order_id = ?
                AND status = 'PAID'
            ", $order['id']);

            $total_addon_price = (float) $result[0]['total'];
            $review = db_get_row('reviews', ['order_id' => $order['id']]);

                        // Snapshot data
            $original_order = null;      // immutable original snapshot
            $original_meta  = null;
            $customer_order = null;      // what the customer currently sees (can be synced to edited order)
            $customer_meta  = null;

            $snap = db_get_row('order_original_data', ['order_id' => $order['id']], 1);
            if (!empty($snap)) {
                // Original (immutable): saved on first edit
                $orig_order_arr = json_decode($snap['orders_json'] ?? '', true) ?: [];
                $orig_opts_arr  = json_decode($snap['options_json'] ?? '', true) ?: [];

                $orig_form_id = $orig_order_arr['form_id'] ?? $order['form_id'];
                $orig_form = db_get_row('boost_forms', ['id' => $orig_form_id, 'select' => 'id,type,name,icon,game'], 1);
                $original_order = array_merge($orig_form ?: [], $orig_opts_arr ?: [], $orig_order_arr ?: []);
                $original_meta  = $snap;

                // Customer view: defaults to original, can be overridden by customer_* columns
                $cust_order_arr = !empty($snap['customer_orders_json'])
                    ? (json_decode($snap['customer_orders_json'], true) ?: [])
                    : $orig_order_arr;

                $cust_opts_arr = !empty($snap['customer_options_json'])
                    ? (json_decode($snap['customer_options_json'], true) ?: [])
                    : $orig_opts_arr;

                $cust_form_id = $cust_order_arr['form_id'] ?? $orig_form_id;
                $cust_form = ($cust_form_id == $orig_form_id) ? $orig_form : db_get_row('boost_forms', ['id' => $cust_form_id, 'select' => 'id,type,name,icon,game'], 1);
                $customer_order = array_merge($cust_form ?: [], $cust_opts_arr ?: [], $cust_order_arr ?: []);
                $customer_meta  = $snap;
            }

            view_file('admin/pages/orders/view', [
                'data' => $order,
                'invoice' => $invoice,
                'addons' => $addons,
                'total_addon_price' => $total_addon_price,
                'review' => $review,
                'original_order' => $original_order,
                'original_meta' => $original_meta,
                'riot_tracking_warning' => $riot_tracking_warning,
            ]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    // Admin: delete an uploaded order screenshot (proof)
    $router->post('orders/screenshot/delete', function () {
        global $is_admin;
        global $db;

        if (!$is_admin) {
            http_response_code(401);
            exit;
        }

        $screenshotId = intval($_POST['screenshot_id'] ?? 0);
        if ($screenshotId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing screenshot_id']);
            exit;
        }

        $shot = db_get_row('order_screenshots', ['id' => $screenshotId], 1);
        if (empty($shot)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Screenshot not found']);
            exit;
        }

        $orderId = intval($shot['order_id'] ?? 0);

        // Delete DB record
        $db->run('DELETE FROM order_screenshots WHERE id = ?', $screenshotId);

        // If this screenshot was used to set waiting_for_approval, reset it
        if ($orderId > 0) {
            $db->run('UPDATE orders SET waiting_for_approval = 0 WHERE id = ?', $orderId);
        }

        // Optional: delete the physical file if it is stored locally in /public/uploads
        $fileUrl = (string)($shot['file_url'] ?? '');
        if ($fileUrl !== '' && strpos($fileUrl, '/public/uploads/') !== false) {
            $rel = explode('/public/uploads/', $fileUrl, 2)[1] ?? '';
            if ($rel !== '') {
                $abs = rtrim(SYS_PATH, '/') . '/public/uploads/' . $rel;
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }
        }

        echo json_encode(['success' => true]);
        exit;
    });

// ----- ALT version of $router->post('orders/screenshot/delete', ...) BEGIN -----
//     $router->post('orders/screenshot/delete', function () {
//         global $is_admin;
//         global $db;
// 
//         if (!$is_admin) {
//             http_response_code(401);
//             exit;
//         }
// 
//         $screenshotId = intval($_POST['screenshot_id'] ?? 0);
//         if ($screenshotId <= 0) {
//             http_response_code(400);
//             echo json_encode(['success' => false, 'message' => 'Missing screenshot_id']);
//             exit;
//         }
// 
//         $shot = db_get_row('order_screenshots', ['id' => $screenshotId], 1);
//         if (empty($shot)) {
//             http_response_code(404);
//             echo json_encode(['success' => false, 'message' => 'Screenshot not found']);
//             exit;
//         }
// 
//         $orderId = intval($shot['order_id'] ?? 0);
// 
//         // Delete DB record
//         $db->run('DELETE FROM order_screenshots WHERE id = ?', $screenshotId);
// 
//         // If this screenshot was used to set waiting_for_approval, reset it
//         if ($orderId > 0) {
//             $db->run('UPDATE orders SET waiting_for_approval = 0 WHERE id = ?', $orderId);
//         }
// 
//         // Optional: delete the physical file if it is stored locally in /public/uploads
//         $fileUrl = (string) ($shot['file_url'] ?? '');
//         if ($fileUrl !== '' && strpos($fileUrl, '/public/uploads/') !== false) {
//             $rel = explode('/public/uploads/', $fileUrl, 2)[1] ?? '';
//             if ($rel !== '') {
//                 $abs = rtrim(SYS_PATH, '/') . '/public/uploads/' . $rel;
//                 if (is_file($abs)) {
//                     @unlink($abs);
//                 }
//             }
//         }
// 
//         echo json_encode(['success' => true]);
//         exit;
//     });
// ----- ALT version END -----

    $router->get('boost/forms', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de'
            ) {
                redirect_url('admin-area/orders');
            }
            $data = db_get_rows('boost_forms');

            // echo '<pre>';
            // print_r($data);
            // die;

            view_file('admin/pages/orders/forms/list', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('discounts', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'samsayahix@gmail.com' &&
                ADMIN_DATA['email'] !== 'duck_sauce@live.de' 
            ) {
                redirect_url('admin-area/orders');
            }
            $data = db_get_rows('discounts', [], 1);
            view_file('admin/pages/discounts/list', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('drop-requests', function () {
        global $is_admin;

        if ($is_admin) {
            $data = db_get_rows('drop_requests', [], 1);
            view_file('admin/pages/drop-requests', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('reviews', function () {
        global $is_admin, $db;

        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        // Boosting, seller and GG-Girl reviews on one page. Filtering and paging happen
        // in SQL: rendering all ~1200 rows and hiding them client-side made the page load
        // slowly and visibly flash the full list before the Pending filter kicked in.
        $status = strtolower(trim((string)($_GET['status'] ?? 'pending')));
        if (!in_array($status, ['pending', 'approved', 'any'], true)) $status = 'pending';
        $type = strtolower(trim((string)($_GET['type'] ?? '')));
        if (!in_array($type, ['boost', 'seller', 'egirl'], true)) $type = '';
        $search  = trim((string)($_GET['q'] ?? ''));
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
        // Built per alias: for status "any" the condition must not be prefixed, otherwise
        // it becomes "WHERE r.1", which is a syntax error and silently returned no rows.
        $approvedWhere = static function (string $alias) use ($status): string {
            if ($status === 'any') return '1';
            return $alias . '.approved = ' . ($status === 'approved' ? '1' : '0');
        };

        $rvRun = function (string $sql, ...$args) use ($db): array {
            try { return $db->run($sql, ...$args) ?: []; } catch (\Throwable $e) { return []; }
        };
        $rvCell = function (string $sql, ...$args) use ($db): int {
            try { $row = $db->row($sql, ...$args); return (int)($row['cnt'] ?? 0); } catch (\Throwable $e) { return 0; }
        };

        // Avatars are stored either absolute or relative; no default here, an empty
        // string simply means "render no image".
        $rvAvatar = static function ($icon): string {
            $icon = trim((string)$icon);
            if ($icon === '') return '';
            if (preg_match('~^https?://~i', $icon)) return $icon;
            return rtrim(BASE_URL, '/') . '/' . ltrim($icon, '/');
        };

        // Header counters always describe the whole dataset, independent of the filters.
        $countAll = static function (string $where) use ($rvCell): int {
            return $rvCell("SELECT (
                    (SELECT COUNT(*) FROM reviews        WHERE {$where}) +
                    (SELECT COUNT(*) FROM seller_reviews WHERE {$where}) +
                    (SELECT COUNT(*) FROM egirl_reviews  WHERE {$where})
                ) AS cnt");
        };
        $stats = [
            'total'    => $countAll('1'),
            'approved' => $countAll('approved = 1'),
            'pending'  => $countAll('approved = 0'),
        ];

        // Per-source index rows (id + sort keys only) so paging can span all three tables.
        $index  = [];
        $counts = ['boost' => 0, 'seller' => 0, 'egirl' => 0];

        $sources = [
            'boost' => [
                'sql'   => "FROM reviews r LEFT JOIN boosters b ON b.id = r.booster_id
                            WHERE " . $approvedWhere('r') . ($search !== '' ? " AND (r.comments LIKE ? OR b.username LIKE ? OR CAST(r.order_id AS CHAR) LIKE ?)" : ''),
                'args'  => $search !== '' ? [$like, $like, $like] : [],
                'alias' => 'r',
            ],
            'seller' => [
                'sql'   => "FROM seller_reviews sr LEFT JOIN sellers s ON s.id = sr.seller_id
                            WHERE " . $approvedWhere('sr') . ($search !== '' ? " AND (sr.comment LIKE ? OR s.username LIKE ?)" : ''),
                'args'  => $search !== '' ? [$like, $like] : [],
                'alias' => 'sr',
            ],
            'egirl' => [
                'sql'   => "FROM egirl_reviews er LEFT JOIN boosters b ON b.id = er.egirl_id
                            WHERE " . $approvedWhere('er') . ($search !== '' ? " AND (er.comment LIKE ? OR b.username LIKE ?)" : ''),
                'args'  => $search !== '' ? [$like, $like] : [],
                'alias' => 'er',
            ],
        ];

        foreach ($sources as $key => $cfg) {
            $counts[$key] = $rvCell("SELECT COUNT(*) AS cnt {$cfg['sql']}", ...$cfg['args']);
            if ($type !== '' && $type !== $key) continue;
            foreach ($rvRun("SELECT {$cfg['alias']}.id, {$cfg['alias']}.created_at, {$cfg['alias']}.approved {$cfg['sql']}", ...$cfg['args']) as $row) {
                $index[] = [
                    'src'        => $key,
                    'id'         => (int)$row['id'],
                    'approved'   => (int)$row['approved'],
                    'created_ts' => strtotime((string)$row['created_at']) ?: 0,
                ];
            }
        }

        usort($index, static function ($a, $b) {
            if ($a['approved'] !== $b['approved']) return $a['approved'] <=> $b['approved'];
            return $b['created_ts'] <=> $a['created_ts'];
        });

        $totalRows  = count($index);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page       = min($page, $totalPages);
        $slice      = array_slice($index, ($page - 1) * $perPage, $perPage);

        $idsBySource = ['boost' => [], 'seller' => [], 'egirl' => []];
        foreach ($slice as $row) $idsBySource[$row['src']][] = $row['id'];

        $rowsByKey = [];

        if (!empty($idsBySource['boost'])) {
            $in = implode(',', array_map('intval', $idsBySource['boost']));
            foreach ($rvRun(
                "SELECT r.*, b.username AS partner_name, b.icon AS partner_icon
                 FROM reviews r LEFT JOIN boosters b ON b.id = r.booster_id
                 WHERE r.id IN ({$in})"
            ) as $r) {
                $id = (int)$r['id'];
                $rowsByKey['boost-' . $id] = [
                    'source'        => 'boost',
                    'source_label'  => 'Boosting',
                    'source_icon'   => 'fa-rocket-launch',
                    'id'            => $id,
                    'row_key'       => 'boost-' . $id,
                    'ref_label'     => '#' . (int)($r['order_id'] ?? 0),
                    'ref_url'       => ADMN_URL . '/order/' . (int)($r['order_id'] ?? 0),
                    'partner_name'  => trim((string)($r['partner_name'] ?? '')) ?: ('Booster #' . (int)($r['booster_id'] ?? 0)),
                    'partner_url'   => !empty($r['booster_id']) ? (ADMN_URL . '/booster/' . (int)$r['booster_id']) : '',
                    'partner_icon'  => $rvAvatar($r['partner_icon'] ?? ''),
                    'communication' => (int)($r['communication'] ?? 0),
                    'skill'         => (int)($r['skill'] ?? 0),
                    'speed'         => (int)($r['speed'] ?? 0),
                    'overall'       => (int)($r['overall'] ?? 0),
                    'highlights'    => (string)($r['highlights'] ?? ''),
                    'comments'      => (string)($r['comments'] ?? ''),
                    'created_at'    => (string)($r['created_at'] ?? ''),
                    'approved'      => (int)($r['approved'] ?? 0),
                    'act_approve'   => ['action' => 'admin_approve_review', 'id' => $id],
                    'act_hide'      => ['action' => 'admin_disapprove_review', 'id' => $id],
                    'act_delete'    => ['action' => 'admin_delete_review', 'id' => $id],
                ];
            }
        }

        if (!empty($idsBySource['seller'])) {
            $in = implode(',', array_map('intval', $idsBySource['seller']));
            foreach ($rvRun(
                "SELECT sr.*, s.username AS partner_name, s.icon AS partner_icon
                 FROM seller_reviews sr LEFT JOIN sellers s ON s.id = sr.seller_id
                 WHERE sr.id IN ({$in})"
            ) as $r) {
                $id = (int)$r['id'];
                $rowsByKey['seller-' . $id] = [
                    'source'        => 'seller',
                    'source_label'  => 'Seller',
                    'source_icon'   => 'fa-store',
                    'id'            => $id,
                    'row_key'       => 'seller-' . $id,
                    'ref_label'     => function_exists('lb_seller_review_service_label') ? lb_seller_review_service_label($r) : 'Purchase',
                    'ref_url'       => !empty($r['seller_id']) ? (ADMN_URL . '/seller/' . (int)$r['seller_id'] . '/reviews') : '',
                    'partner_name'  => trim((string)($r['partner_name'] ?? '')) ?: ('Seller #' . (int)($r['seller_id'] ?? 0)),
                    'partner_url'   => !empty($r['seller_id']) ? (ADMN_URL . '/seller/' . (int)$r['seller_id']) : '',
                    'partner_icon'  => $rvAvatar($r['partner_icon'] ?? ''),
                    'communication' => 0,
                    'skill'         => 0,
                    'speed'         => 0,
                    'overall'       => (int)($r['rating'] ?? 0),
                    'highlights'    => '',
                    'comments'      => (string)($r['comment'] ?? ''),
                    'created_at'    => (string)($r['created_at'] ?? ''),
                    'approved'      => (int)($r['approved'] ?? 0),
                    // One endpoint toggles both ways for seller reviews.
                    'act_approve'   => ['action' => 'admin_hide_seller_review', 'id' => $id],
                    'act_hide'      => ['action' => 'admin_hide_seller_review', 'id' => $id],
                    'act_delete'    => ['action' => 'admin_delete_seller_review', 'id' => $id],
                ];
            }
        }

        if (!empty($idsBySource['egirl'])) {
            $in = implode(',', array_map('intval', $idsBySource['egirl']));
            foreach ($rvRun(
                "SELECT er.*, b.username AS partner_name, b.icon AS partner_icon
                 FROM egirl_reviews er LEFT JOIN boosters b ON b.id = er.egirl_id
                 WHERE er.id IN ({$in})"
            ) as $r) {
                $id = (int)$r['id'];
                $rowsByKey['egirl-' . $id] = [
                    'source'        => 'egirl',
                    'source_label'  => 'GG-Girl',
                    'source_icon'   => 'fa-star-shooting',
                    'id'            => $id,
                    'row_key'       => 'egirl-' . $id,
                    'ref_label'     => '#' . (int)($r['egirl_order_id'] ?? 0),
                    'ref_url'       => ADMN_URL . '/egirl/order/' . (int)($r['egirl_order_id'] ?? 0),
                    'partner_name'  => trim((string)($r['partner_name'] ?? '')) ?: ('GG-Girl #' . (int)($r['egirl_id'] ?? 0)),
                    'partner_url'   => !empty($r['egirl_id']) ? (ADMN_URL . '/egirl/' . (int)$r['egirl_id']) : '',
                    'partner_icon'  => $rvAvatar($r['partner_icon'] ?? ''),
                    'communication' => 0,
                    'skill'         => 0,
                    'speed'         => 0,
                    'overall'       => (int)($r['rating'] ?? 0),
                    'highlights'    => '',
                    'comments'      => (string)($r['comment'] ?? ''),
                    'created_at'    => (string)($r['created_at'] ?? ''),
                    'approved'      => (int)($r['approved'] ?? 0),
                    'act_approve'   => ['action' => 'admin_egirl_review_update', 'review_id' => $id, 'approved' => 1],
                    'act_hide'      => ['action' => 'admin_egirl_review_update', 'review_id' => $id, 'approved' => 0],
                    'act_delete'    => ['action' => 'admin_egirl_review_update', 'review_id' => $id, 'approved' => -1],
                ];
            }
        }

        $data = [];
        foreach ($slice as $row) {
            $key = $row['src'] . '-' . $row['id'];
            if (isset($rowsByKey[$key])) $data[] = $rowsByKey[$key];
        }

        view_file('admin/pages/reviews', [
            'data'    => $data,
            'stats'   => $stats,
            'filters' => [
                'status'      => $status,
                'type'        => $type,
                'q'           => $search,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_rows'  => $totalRows,
                'total_pages' => $totalPages,
                'counts'      => $counts,
            ],
        ]);
    });
    $router->get('boost/form/:id/edit', function ($id) {
        global $is_admin;
        if ($is_admin) {
            $id = intval($id);
            $data = db_load_boost_form($id);

            // The legacy pricing editor only has hand-written Plates templates for
            // LoL, TFT and Valorant. All other games are managed by the generic game
            // form editor; trying to insert a non-existent template caused a fatal
            // LogicException for e.g. Marvel Rivals and LoL Classic.
            $gameSlug = strtolower(trim((string)($data['game'] ?? '')));
            $formType = strtolower(trim((string)($data['type'] ?? '')));
            $safeGame = preg_replace('/[^a-z0-9_-]/', '', $gameSlug);
            $safeType = preg_replace('/[^a-z0-9_-]/', '', $formType);
            $legacyTemplate = rtrim((string)SYS_PATH, '/\\')
                . '/public/views/admin/pages/orders/forms/price-tables/'
                . $safeGame . '/' . $safeType . '.php';

            if (!is_file($legacyTemplate)) {
                $gameId = (int)($data['game_id'] ?? 0);
                if ($gameId <= 0 && function_exists('util_get_game_by_slug')) {
                    $gameRow = util_get_game_by_slug($gameSlug);
                    $gameId = (int)($gameRow['id'] ?? 0);
                }
                if ($gameId > 0) {
                    redirect_url('admin-area/games/' . $gameId . '/boost-form-edit?fid=' . $id);
                    return;
                }
            }

            view_file('admin/pages/orders/forms/edit', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('booster/add', function () {
        global $is_admin;
        if ($is_admin) {
            view_file('admin/pages/boosters/add');
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('booster/applications', function () {
        global $is_admin;
        global $db;

        $query = "SELECT ba.*, b.username, b.created_at
                    FROM booster_personal_details ba
                    LEFT JOIN boosters b ON ba.booster_id = b.id
                    WHERE b.verified = 0
                    ORDER BY b.created_at DESC";

        $data = $db->run($query);

        if ($is_admin) {
            view_file('admin/pages/boosters/applications', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('boosters', function () {
        global $is_admin;
        if ($is_admin) {
            $data = db_get_rows('boosters', ['is_egirl' => 0], 1);
            $ranks = db_get_rows('booster_ranks');
            $ranks = array_column($ranks, null, 'id');
            foreach ($data as $key => $booster) {
                if (isset($ranks[$booster['rank_id']])) {
                    $data[$key] = array_merge($ranks[$booster['rank_id']], $booster);
                } else {
                    $data[$key] = $booster;
                }
            }
            view_file('admin/pages/boosters/list', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    // =========================
    // Unified Payout Requests (Admin)
    // =========================
    $router->get('payout-requests', function () {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }

        $booster_requests = $db->run(
            "SELECT r.*, b.username AS booster_username, b.id AS booster_id,
                    b.balance AS booster_current_balance,
                    b.insurance_required_amount AS booster_insurance_required_amount,
                    m.method AS method_type, m.details AS method_details, m.label AS method_label
             FROM booster_payout_requests r
             LEFT JOIN boosters b ON b.id = r.booster_id
             LEFT JOIN booster_payout_methods m ON m.id = r.payout_method_id
             ORDER BY r.id DESC"
        ) ?: [];

        $seller_requests = $db->run(
            "SELECT r.*, s.username AS seller_username, s.email AS seller_email
             FROM seller_payout_requests r
             LEFT JOIN sellers s ON s.id = r.seller_id
             ORDER BY r.id DESC"
        ) ?: [];

        $egirl_requests = $db->run(
            "SELECT ep.*, b.username AS egirl_username,
                    pm.method AS method_type,
                    pm.label AS method_label,
                    pm.details AS method_details
             FROM egirl_payout_requests ep
             LEFT JOIN boosters b ON b.id = ep.egirl_id
             LEFT JOIN booster_payout_methods pm
               ON pm.id = CAST(JSON_UNQUOTE(JSON_EXTRACT(ep.details, '$.payout_method_id')) AS UNSIGNED)
              AND pm.booster_id = ep.egirl_id
             ORDER BY ep.id DESC"
        ) ?: [];

        view_file('admin/pages/payout-requests', compact(
            'booster_requests', 'seller_requests', 'egirl_requests'
        ));
    });

    // =========================
    // Payout Requests (Admin)
    // =========================
    $router->get('booster/payout-requests', function () {
        global $is_admin, $db;
        if ($is_admin) {
            $data = $db->run("SELECT r.*, b.username AS booster_username, b.id AS booster_id, m.method AS method_type, m.details AS method_details, m.label AS method_label FROM booster_payout_requests r LEFT JOIN boosters b ON b.id=r.booster_id LEFT JOIN booster_payout_methods m ON m.id=r.payout_method_id ORDER BY r.id DESC");
            view_file('admin/pages/boosters/payout_requests', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    // =========================
    // Booster Payout Methods (Admin - Edit/Save)
    // =========================
    $router->post('booster/:id/payout-methods/save', function ($id) {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        $boosterId = intval($id);

        $method = strtolower(trim($_POST['method'] ?? ''));
        if (!in_array($method, ['bank', 'crypto'], true)) {
            http_response_code(400);
            echo 'Invalid method';
            exit;
        }

        $methodId = intval($_POST['method_id'] ?? 0);
        $makeDefault = (isset($_POST['make_default']) && $_POST['make_default'] === '1');

        // Keep JSON keys consistent across booster/admin views
        if ($method === 'crypto') {
            $details = [
                'coin' => trim($_POST['coin'] ?? ''),
                'network' => trim($_POST['network'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
            ];
            $label = 'Crypto';
        } else {
            $details = [
                'beneficiary' => trim($_POST['beneficiary'] ?? ''),
                'iban' => trim($_POST['iban'] ?? ''),
                'swift' => trim($_POST['swift'] ?? ''),
                'bank_name' => trim($_POST['bank_name'] ?? ''),
            ];
            $label = 'Bank Transfer';
        }

        $detailsJson = json_encode($details, JSON_UNESCAPED_UNICODE);

        // Update or insert
        if ($methodId > 0) {
            $existing = db_get_row('booster_payout_methods', ['id' => $methodId, 'booster_id' => $boosterId], 1);
            if (empty($existing)) {
                http_response_code(404);
                echo 'Payout method not found';
                exit;
            }

            db_update_row('booster_payout_methods', ['id' => $methodId], [
                'method' => $method,
                'label' => $label,
                'details' => $detailsJson,
            ]);

            $savedId = $methodId;
        } else {
            $savedId = db_add_row('booster_payout_methods', [
                'booster_id' => $boosterId,
                'method' => $method,
                'label' => $label,
                'details' => $detailsJson,
                'is_default' => 0,
            ]);

            if (is_array($savedId)) {
                $savedId = intval($savedId['id'] ?? 0);
            } else {
                $savedId = intval($savedId);
            }
        }

        // Set default if requested
        if ($makeDefault && $savedId > 0) {
            $db->run("UPDATE booster_payout_methods SET is_default=0 WHERE booster_id=?", $boosterId);
            $db->run("UPDATE booster_payout_methods SET is_default=1 WHERE id=? AND booster_id=?", $savedId, $boosterId);
        }

        header('Location: ' . ADMN_URL . '/booster/' . $boosterId . '/payout-methods?success=1&active=' . $method);
        exit;
    });

    $router->post('booster/:id/payout-methods/set-default', function ($id) {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        $boosterId = intval($id);
        $methodId = intval($_POST['method_id'] ?? 0);

        if ($methodId <= 0) {
            http_response_code(400);
            echo 'Invalid method_id';
            exit;
        }

        $db->run("UPDATE booster_payout_methods SET is_default=0 WHERE booster_id=?", $boosterId);
        $db->run("UPDATE booster_payout_methods SET is_default=1 WHERE id=? AND booster_id=?", $methodId, $boosterId);

        header('Location: ' . ADMN_URL . '/booster/' . $boosterId . '/payout-methods?success=1');
        exit;
    });

    $router->post('booster/:id/payout-methods/delete', function ($id) {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        $boosterId = intval($id);
        $methodId = intval($_POST['method_id'] ?? 0);

        if ($methodId <= 0) {
            http_response_code(400);
            echo 'Invalid method_id';
            exit;
        }

        $db->run("DELETE FROM booster_payout_methods WHERE id=? AND booster_id=?", $methodId, $boosterId);

        header('Location: ' . ADMN_URL . '/booster/' . $boosterId . '/payout-methods?success=1');
        exit;
    });



    
    // =========================
    // Booster Insurance (Admin - Edit/Save)
    // =========================
    $router->post('booster/:id/insurance/save', function ($id) {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
        }

        $boosterId = intval($id);

        // amounts are stored in cents (int). Inputs are in EUR (e.g. 25 or 25.50)
        $required_input = trim((string)($_POST['insurance_required'] ?? ''));
        $paid_input = trim((string)($_POST['insurance_paid'] ?? ''));

        // allow blank required => NULL (meaning "use default / policy")
        $required_cents = null;
        if ($required_input !== '') {
            $required_cents = (int) util_format_price_db($required_input);
            if ($required_cents < 0) $required_cents = 0;
        }

        $paid_cents = (int) util_format_price_db($paid_input !== '' ? $paid_input : 0);
        if ($paid_cents < 0) $paid_cents = 0;

        $current = $db->row("SELECT insurance_paid_amount, insurance_paid_at FROM boosters WHERE id=? LIMIT 1", $boosterId);
        $currentPaid = (int)($current['insurance_paid_amount'] ?? 0);
        $currentPaidAt = $current['insurance_paid_at'] ?? null;

        $update = [
            'insurance_paid_amount' => $paid_cents,
            'insurance_required_amount' => $required_cents,
        ];

        // Set paid_at if we just set a positive paid amount for the first time
        if ($paid_cents > 0 && empty($currentPaidAt)) {
            $update['insurance_paid_at'] = date('Y-m-d H:i:s');
        }

        db_update_row('boosters', ['id' => $boosterId], $update);

        header('Location: ' . ADMN_URL . '/booster/' . $boosterId . '/payout-methods?success=1');
        exit;
    });

// ----- ALT version of $router->post('booster/:id/insurance/save', ...) BEGIN -----
//     $router->post('booster/:id/insurance/save', function ($id) {
//         global $is_admin, $db;
// 
//         if (!$is_admin) {
//             redirect_url('admin-area/auth/login');
//         }
// 
//         $boosterId = intval($id);
// 
//         // amounts are stored in cents (int). Inputs are in EUR (e.g. 25 or 25.50)
//         $required_input = trim((string) ($_POST['insurance_required'] ?? ''));
//         $paid_input = trim((string) ($_POST['insurance_paid'] ?? ''));
// 
//         // allow blank required => NULL (meaning "use default / policy")
//         $required_cents = null;
//         if ($required_input !== '') {
//             $required_cents = (int) util_format_price_db($required_input);
//             if ($required_cents < 0)
//                 $required_cents = 0;
//         }
// 
//         $paid_cents = (int) util_format_price_db($paid_input !== '' ? $paid_input : 0);
//         if ($paid_cents < 0)
//             $paid_cents = 0;
// 
//         $current = $db->row("SELECT insurance_paid_amount, insurance_paid_at FROM boosters WHERE id=? LIMIT 1", $boosterId);
//         $currentPaid = (int) ($current['insurance_paid_amount'] ?? 0);
//         $currentPaidAt = $current['insurance_paid_at'] ?? null;
// 
//         $update = [
//             'insurance_paid_amount' => $paid_cents,
//             'insurance_required_amount' => $required_cents,
//         ];
// 
//         // Set paid_at if we just set a positive paid amount for the first time
//         if ($paid_cents > 0 && empty($currentPaidAt)) {
//             $update['insurance_paid_at'] = date('Y-m-d H:i:s');
//         }
// 
//         db_update_row('boosters', ['id' => $boosterId], $update);
// 
//         header('Location: ' . ADMN_URL . '/booster/' . $boosterId . '/payout-methods?success=1');
//         exit;
//     });
// ----- ALT version END -----


$router->get('clients', function () {
        global $is_admin;
        global $db;
        if ($is_admin) {
            $stats = [
                'total'     => (int)$db->single("SELECT COUNT(*) FROM clients"),
                'banned'    => (int)$db->single("SELECT COUNT(*) FROM clients WHERE is_banned = 1"),
                'new_month' => (int)$db->single("SELECT COUNT(*) FROM clients WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"),
                'total_coins'=> (float)$db->single("SELECT COALESCE(SUM(points),0) FROM clients"),
            ];
            $loyaltyRanks = $db->run("SELECT id, name FROM loyalty_ranks ORDER BY target_amount ASC") ?: [];
            view_file('admin/pages/clients/list', ['stats' => $stats, 'loyaltyRanks' => $loyaltyRanks]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    // Server-side data endpoint for Clients (DataTables)
    $router->get('clients/data', function () {
        global $is_admin, $db;
        if (!$is_admin) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $draw    = isset($_GET['draw'])   ? intval($_GET['draw'])   : 1;
        $start   = isset($_GET['start'])  ? intval($_GET['start'])  : 0;
        $length  = isset($_GET['length']) ? intval($_GET['length']) : 8;
        $search  = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
        $orderDir = (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
        $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
        $loyaltyRankFilter = isset($_GET['loyalty_rank']) ? trim($_GET['loyalty_rank']) : '';

        // Total count (unfiltered)
        $totalCount = (int)$db->single("SELECT COUNT(*) FROM clients");

        // Build WHERE — all values are either hardcoded ints or escaped strings, no placeholders
        $conditions = [];

        if ($statusFilter === 'banned') {
            $conditions[] = 'c.is_banned = 1';
        } elseif ($statusFilter === 'active') {
            $conditions[] = 'c.is_banned = 0';
        }

        if ($loyaltyRankFilter !== '' && ctype_digit($loyaltyRankFilter)) {
            $conditions[] = 'c.loyalty_rank_id = ' . intval($loyaltyRankFilter);
        }

        if ($search !== '') {
            if (ctype_digit($search)) {
                $conditions[] = 'c.id = ' . intval($search);
            } else {
                $escaped = esc($search);
                $like    = '%' . $escaped . '%';
                $conditions[] = "(c.username LIKE '" . $like . "' OR c.email LIKE '" . $like . "' OR c.discord LIKE '" . $like . "')";
            }
        }

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

        // Filtered count
        $filteredCount = (int)$db->single(
            "SELECT COUNT(*) FROM clients c LEFT JOIN loyalty_ranks lr ON c.loyalty_rank_id = lr.id" . $where
        );

        // Data
        $rowsRaw = $db->run(
            "SELECT c.*, lr.name AS loyalty_rank_name
             FROM clients c
             LEFT JOIN loyalty_ranks lr ON c.loyalty_rank_id = lr.id"
            . $where
            . " ORDER BY c.created_at " . $orderDir
            . " LIMIT " . intval($length) . " OFFSET " . intval($start)
        ) ?: [];

        $rankColors = [
            'bronze'     => 'background:rgba(180,110,60,.15);color:#c07840;border:1px solid rgba(180,110,60,.3)',
            'silver'     => 'background:rgba(150,170,190,.12);color:#96aabe;border:1px solid rgba(150,170,190,.28)',
            'gold'       => 'background:rgba(245,202,153,.14);color:#f5ca99;border:1px solid rgba(245,202,153,.3)',
            'platinum'   => 'background:rgba(9,165,190,.12);color:#09a5be;border:1px solid rgba(9,165,190,.28)',
            'diamond'    => 'background:rgba(85,170,255,.12);color:#55aaff;border:1px solid rgba(85,170,255,.28)',
            'challenger' => 'background:rgba(255,215,0,.13);color:#ffd700;border:1px solid rgba(255,215,0,.3)',
        ];

        $rows = [];
        foreach ($rowsRaw as $row) {
            $coins       = number_format((float)($row['points'] ?? 0), 2);
            $loyaltyName = htmlspecialchars($row['loyalty_rank_name'] ?? '—');
            $rankStyle   = $rankColors[strtolower($loyaltyName)] ?? 'background:rgba(109,116,123,.10);color:#6d747b;border:1px solid rgba(109,116,123,.2)';
            $rankBadge   = '<span style="display:inline-flex;align-items:center;padding:.22rem .6rem;border-radius:20px;font-size:.73rem;font-weight:600;white-space:nowrap;' . $rankStyle . '">' . $loyaltyName . '</span>';

            $userHtml = util_format_user($row['username'] ?? '', $row['icon'] ?? '');

            // Column order must match the <thead>: ID, Username, Email, Discord, Loyalty Rank, LB Coins, Joined At, Action.
            $rows[] = [
                '<a href="' . ADMN_URL . '/client/' . (int)$row['id'] . '">#' . (int)$row['id'] . '</a>',
                $userHtml,
                htmlspecialchars($row['email'] ?? '—'),
                htmlspecialchars($row['discord'] ?? '—'),
                $rankBadge,
                '<span class="d-block text-end">' . $coins . '</span>',
                '<span class="d-block text-end">' . util_format_date_display($row['created_at'] ?? '') . '</span>',
                '<span class="d-block text-end"><a href="' . ADMN_URL . '/client/' . (int)$row['id'] . '" class="btn btn-white btn-sm"><i class="fa-duotone fa-eye me-1 fs-6"></i> View</a></span>',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $rows,
        ]);
        exit;
    });
    $router->get('client/:id/:slug?', function ($id, $page = 'profile') {
        global $is_admin;
        if ($is_admin) {
            $id = intval($id);
            $data = db_get_row('clients', ['id' => $id], 1);
            $loyalty = db_get_row('loyalty_ranks', ['id' => $data['loyalty_rank_id']], 1);

            $data['loyalty'] = $loyalty;

            switch ($page) {
                case 'orders':
                    $data['orders'] = db_get_rows('orders', ['client_id' => $id], 1);
                    // array map order_options and boost_forms to orders
                    $forms = db_get_rows('boost_forms', ['select' => 'id,type,name,icon,game']);
                    $forms = array_column($forms, null, 'id');
                    $boosters = db_get_rows('boosters', ['select' => 'id,username,icon']);
                    $boosters = array_column($boosters, null, 'id');
                    foreach ($data['orders'] as $key => $order) {
                        $order_opts = db_get_row('order_options', ['order_id' => $order['id'], 'select' => 'server,hours,boosters,roles,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo'], 1);
                        $data['orders'][$key] = array_merge($order_opts, $forms[$order['form_id']], $order);
                        if (isset($order['booster_id'])) {
                            $data['orders'][$key]['booster'] = $boosters[$order['booster_id']];
                        }
                    }

                    // Everything the client ever bought, not just boosting: GG-Girl bookings,
                    // marketplace + package accounts, items, top ups and digital goods all get
                    // normalized into one list so the Orders tab is actually complete.
                    global $db;
                    $data['all_orders'] = [];
                    $pushOrder = function (array $row) use (&$data) { $data['all_orders'][] = $row; };

                    // Avatars are stored inconsistently: sometimes an absolute URL, sometimes a
                    // path relative to the site root, and often not at all. Normalize here so the
                    // view can just print the value.
                    $partnerAvatar = function ($icon, string $role = 'seller'): string {
                        $icon = trim((string)$icon);
                        if ($icon !== '') {
                            if (preg_match('~^https?://~i', $icon)) return $icon;
                            return rtrim(BASE_URL, '/') . '/' . ltrim($icon, '/');
                        }
                        $defaults = [
                            'booster' => ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png',
                            'egirl'   => ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png',
                            'seller'  => ICON_URL . '/default1.png',
                        ];
                        return $defaults[$role] ?? $defaults['seller'];
                    };

                    // Admins get no default: showing default1.png made an admin without an
                    // own icon look like a different admin.
                    $partnerAvatarRaw = function ($icon): string {
                        $icon = trim((string)$icon);
                        if ($icon === '') return '';
                        if (preg_match('~^https?://~i', $icon)) return $icon;
                        return rtrim(BASE_URL, '/') . '/' . ltrim($icon, '/');
                    };

                    foreach (($data['orders'] ?: []) as $o) {
                        $pushOrder([
                            'kind'       => 'Boosting',
                            'kind_icon'  => 'fa-rocket-launch',
                            'title'      => util_format_boost_overview($o['game'] ?? '', $o['type'] ?? '', $o),
                            'subtitle'   => trim((string)($o['name'] ?? '')),
                            'game'       => (string)($o['game'] ?? ''),
                            'ref'        => '#' . (int)($o['id'] ?? 0),
                            'url'        => ADMN_URL . '/order/' . (int)($o['id'] ?? 0),
                            'status'     => (string)($o['status'] ?? ''),
                            'partner'    => !empty($o['booster']) ? (string)$o['booster']['username'] : '',
                            'partner_icon' => !empty($o['booster']) ? $partnerAvatar($o['booster']['icon'] ?? '', 'booster') : '',
                            'partner_url'=> !empty($o['booster_id']) ? (ADMN_URL . '/booster/' . (int)$o['booster_id']) : '',
                            'price'      => (int)($o['price'] ?? 0),
                            'currency'   => (string)($o['currency'] ?? 'EUR'),
                            'created_at' => (string)($o['created_at'] ?? ''),
                        ]);
                    }

                    $safeRows = function (string $sql) use ($db, $id): array {
                        try { return $db->run($sql, $id) ?: []; } catch (\Throwable $e) { return []; }
                    };

                    foreach ($safeRows(
                        "SELECT eo.*, b.username AS egirl_username, b.icon AS egirl_icon
                         FROM egirl_orders eo
                         LEFT JOIN boosters b ON b.id = eo.egirl_id
                         WHERE eo.client_id = ?"
                    ) as $eo) {
                        $pushOrder([
                            'kind'       => 'GG-Girl Booking',
                            'kind_icon'  => 'fa-star-shooting',
                            'title'      => trim((string)($eo['service_title'] ?? '')) ?: 'GG-Girl Session',
                            'subtitle'   => trim((string)($eo['unit_value'] ?? '') . ' ' . (string)($eo['unit_type'] ?? '')),
                            'game'       => (string)($eo['game'] ?? ''),
                            'ref'        => '#' . (int)($eo['id'] ?? 0),
                            'url'        => ADMN_URL . '/egirl/order/' . (int)($eo['id'] ?? 0),
                            'status'     => (string)($eo['status'] ?? ''),
                            'partner'    => (string)($eo['egirl_username'] ?? ''),
                            'partner_icon' => $partnerAvatar($eo['egirl_icon'] ?? '', 'egirl'),
                            'partner_url'=> !empty($eo['egirl_id']) ? (ADMN_URL . '/egirl/' . (int)$eo['egirl_id']) : '',
                            'price'      => (int)($eo['price'] ?? 0),
                            'currency'   => (string)($eo['currency'] ?? 'EUR'),
                            'created_at' => (string)($eo['created_at'] ?? ''),
                        ]);
                    }

                    foreach ($safeRows(
                        "SELECT sa.*, s.username AS seller_username, s.icon AS seller_icon
                         FROM selling_accounts sa
                         LEFT JOIN sellers s ON s.id = sa.seller_id
                         WHERE sa.client_id = ? AND sa.sold = 1"
                    ) as $sa) {
                        $pushOrder([
                            'kind'       => 'Account',
                            'kind_icon'  => 'fa-user-shield',
                            'title'      => trim((string)($sa['title'] ?? '')) ?: 'Ranked Account',
                            'subtitle'   => strtoupper((string)($sa['server'] ?? '')),
                            'game'       => (string)($sa['game'] ?? ''),
                            'ref'        => '#' . (int)($sa['id'] ?? 0),
                            'url'        => ADMN_URL . '/selling-account/' . (int)($sa['id'] ?? 0),
                            'status'     => ((int)($sa['sold'] ?? 0) === 2) ? 'REFUNDED' : 'COMPLETED',
                            'partner'    => (string)($sa['seller_username'] ?? ''),
                            'partner_icon' => $partnerAvatar($sa['seller_icon'] ?? '', 'seller'),
                            'partner_url'=> !empty($sa['seller_id']) ? (ADMN_URL . '/seller/' . (int)$sa['seller_id']) : '',
                            'price'      => (int)($sa['price'] ?? 0),
                            'currency'   => 'EUR',
                            'created_at' => (string)($sa['sold_at'] ?? $sa['created_at'] ?? ''),
                        ]);
                    }

                    foreach ($safeRows(
                        "SELECT a.*, ap.name AS package_name, ap.price AS package_price, g.slug AS game_slug,
                                adm.username AS uploader_username, adm.icon AS uploader_icon
                         FROM accounts a
                         LEFT JOIN account_packages ap ON ap.id = a.package_id
                         LEFT JOIN games g ON g.id = ap.game_id
                         LEFT JOIN admins adm ON adm.id = a.admin_id
                         WHERE a.client_id = ? AND a.status = 1"
                    ) as $ac) {
                        // Premium accounts have no seller — the admin who uploaded them is
                        // the counterpart shown in the Partner column.
                        $uploaderId = (int)($ac['admin_id'] ?? 0);
                        $uploaderName = trim((string)($ac['uploader_username'] ?? ''));
                        if ($uploaderName === '' && $uploaderId > 0) $uploaderName = 'Admin #' . $uploaderId;

                        $pushOrder([
                            'kind'       => 'Premium Account',
                            'kind_icon'  => 'fa-crown',
                            'title'      => trim((string)($ac['package_name'] ?? '')) ?: ('Package #' . (int)($ac['package_id'] ?? 0)),
                            'subtitle'   => strtoupper((string)($ac['server'] ?? '')),
                            'game'       => (string)($ac['game_slug'] ?? ''),
                            'ref'        => '#' . (int)($ac['id'] ?? 0),
                            'url'        => ADMN_URL . '/account/' . (int)($ac['id'] ?? 0),
                            'status'     => 'COMPLETED',
                            'partner'    => $uploaderName,
                            'partner_icon' => $partnerAvatarRaw($ac['uploader_icon'] ?? ''),
                            'partner_url'=> '',
                            'price'      => (int)($ac['package_price'] ?? 0),
                            'currency'   => 'EUR',
                            'created_at' => (string)($ac['sold_at'] ?? $ac['created_at'] ?? ''),
                        ]);
                    }

                    foreach ($safeRows(
                        "SELECT sip.*, si.title AS item_title, si.game AS item_game, g.slug AS game_slug, s.username AS seller_username, s.icon AS seller_icon
                         FROM selling_item_purchases sip
                         LEFT JOIN selling_items si ON si.id = sip.item_id
                         LEFT JOIN games g ON g.id = si.game_id
                         LEFT JOIN sellers s ON s.id = sip.seller_id
                         WHERE sip.client_id = ?"
                    ) as $ip) {
                        $pushOrder([
                            'kind'       => 'Item',
                            'kind_icon'  => 'fa-gift',
                            'title'      => trim((string)($ip['item_title'] ?? '')) ?: 'Item',
                            'subtitle'   => 'x' . max(1, (int)($ip['quantity'] ?? 1)),
                            'game'       => (string)($ip['game_slug'] ?? $ip['item_game'] ?? ''),
                            'ref'        => '#' . (int)($ip['id'] ?? 0),
                            'url'        => ADMN_URL . '/item-order/' . (int)($ip['id'] ?? 0),
                            'status'     => (string)($ip['status'] ?? ''),
                            'partner'    => (string)($ip['seller_username'] ?? ''),
                            'partner_icon' => $partnerAvatar($ip['seller_icon'] ?? '', 'seller'),
                            'partner_url'=> !empty($ip['seller_id']) ? (ADMN_URL . '/seller/' . (int)$ip['seller_id']) : '',
                            'price'      => (int)($ip['price'] ?? 0),
                            'currency'   => (string)($ip['currency'] ?? 'EUR'),
                            'created_at' => (string)($ip['created_at'] ?? ''),
                        ]);
                    }

                    foreach ($safeRows(
                        "SELECT stp.*, s.username AS seller_username, s.icon AS seller_icon
                         FROM selling_topup_purchases stp
                         LEFT JOIN sellers s ON s.id = stp.seller_id
                         WHERE stp.client_id = ?"
                    ) as $tp) {
                        $pushOrder([
                            'kind'       => 'Top Up',
                            'kind_icon'  => 'fa-bolt',
                            'title'      => trim((string)($tp['offer_title'] ?? '')) ?: 'Top Up',
                            'subtitle'   => trim((string)($tp['game_name'] ?? '')),
                            'game'       => (string)($tp['game_slug'] ?? ''),
                            'ref'        => '#' . (int)($tp['id'] ?? 0),
                            'url'        => ADMN_URL . '/top-up-order/' . (int)($tp['id'] ?? 0),
                            'status'     => (string)($tp['status'] ?? ''),
                            'partner'    => (string)($tp['seller_username'] ?? ''),
                            'partner_icon' => $partnerAvatar($tp['seller_icon'] ?? '', 'seller'),
                            'partner_url'=> !empty($tp['seller_id']) ? (ADMN_URL . '/seller/' . (int)$tp['seller_id']) : '',
                            'price'      => (int)($tp['price'] ?? 0),
                            'currency'   => (string)($tp['currency'] ?? 'EUR'),
                            'created_at' => (string)($tp['created_at'] ?? ''),
                        ]);
                    }

                    foreach ($safeRows(
                        "SELECT dgp.*, dg.title AS good_title, s.username AS seller_username, s.icon AS seller_icon
                         FROM digital_good_purchases dgp
                         LEFT JOIN digital_goods dg ON dg.id = dgp.item_id
                         LEFT JOIN sellers s ON s.id = dgp.seller_id
                         WHERE dgp.client_id = ?"
                    ) as $dg) {
                        $pushOrder([
                            'kind'       => 'Digital Good',
                            'kind_icon'  => 'fa-floppy-disk',
                            'title'      => trim((string)($dg['good_title'] ?? '')) ?: 'Digital Good',
                            'subtitle'   => 'x' . max(1, (int)($dg['quantity'] ?? 1)),
                            'ref'        => '#' . (int)($dg['id'] ?? 0),
                            'url'        => ADMN_URL . '/digital-good-order/' . (int)($dg['id'] ?? 0),
                            'status'     => (string)($dg['status'] ?? ''),
                            'partner'    => (string)($dg['seller_username'] ?? ''),
                            'partner_icon' => $partnerAvatar($dg['seller_icon'] ?? '', 'seller'),
                            'partner_url'=> !empty($dg['seller_id']) ? (ADMN_URL . '/seller/' . (int)$dg['seller_id']) : '',
                            'price'      => (int)($dg['price'] ?? 0),
                            'currency'   => (string)($dg['currency'] ?? 'EUR'),
                            'created_at' => (string)($dg['created_at'] ?? ''),
                        ]);
                    }

                    usort($data['all_orders'], static function ($a, $b) {
                        return strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? ''));
                    });
                    break;
                case 'accounts':
                    global $db;

                    // Same shape as the Orders tab so both tables read identically:
                    // title + game icon, price, status, partner, type, date.
                    // No default avatar here on purpose: falling back to default1.png made an
                    // admin without an own icon look like a different admin. Empty means
                    // "render no image".
                    $accAvatar = function ($icon): string {
                        $icon = trim((string)$icon);
                        if ($icon === '') return '';
                        if (preg_match('~^https?://~i', $icon)) return $icon;
                        return rtrim(BASE_URL, '/') . '/' . ltrim($icon, '/');
                    };
                    $accRows = function (string $sql) use ($db, $id): array {
                        try { return $db->run($sql, $id) ?: []; } catch (\Throwable $e) { return []; }
                    };

                    $data['account_rows'] = [];

                    foreach ($accRows(
                        "SELECT a.*, ap.name AS package_name, ap.price AS package_price, g.slug AS game_slug,
                                adm.username AS uploader_username, adm.icon AS uploader_icon
                         FROM accounts a
                         LEFT JOIN account_packages ap ON ap.id = a.package_id
                         LEFT JOIN games g ON g.id = ap.game_id
                         LEFT JOIN admins adm ON adm.id = a.admin_id
                         WHERE a.client_id = ? AND a.status = 1"
                    ) as $ac) {
                        // Premium accounts have no seller — the admin who uploaded them is
                        // the counterpart, same role the seller plays for marketplace accounts.
                        $uploaderId = (int)($ac['admin_id'] ?? 0);
                        $uploaderName = trim((string)($ac['uploader_username'] ?? ''));
                        if ($uploaderName === '' && $uploaderId > 0) $uploaderName = 'Admin #' . $uploaderId;

                        $data['account_rows'][] = [
                            'kind'       => 'Premium',
                            'kind_icon'  => 'fa-crown',
                            'id'         => (int)($ac['id'] ?? 0),
                            'url'        => ADMN_URL . '/account/' . (int)($ac['id'] ?? 0),
                            'title'      => trim((string)($ac['package_name'] ?? '')) ?: ('Package #' . (int)($ac['package_id'] ?? 0)),
                            'subtitle'   => strtoupper((string)($ac['server'] ?? '')),
                            'game'       => (string)($ac['game_slug'] ?? ''),
                            'status'     => 'COMPLETED',
                            'partner'    => $uploaderName,
                            'partner_icon' => $accAvatar($ac['uploader_icon'] ?? ''),
                            'partner_url'  => '',
                            'price'      => (int)($ac['package_price'] ?? 0),
                            'created_at' => (string)($ac['sold_at'] ?? $ac['created_at'] ?? ''),
                            'details'    => trim((string)($ac['data'] ?? '')),
                        ];
                    }

                    foreach ($accRows(
                        "SELECT sa.*, s.username AS seller_username, s.icon AS seller_icon
                         FROM selling_accounts sa
                         LEFT JOIN sellers s ON s.id = sa.seller_id
                         WHERE sa.client_id = ? AND sa.sold = 1"
                    ) as $sa) {
                        $extra = [];
                        foreach ([
                            'email'                 => 'Email',
                            'email_password'        => 'Email Password',
                            'in_game_name'          => 'In-Game Name',
                            'delivery_instructions' => 'Delivery',
                        ] as $key => $label) {
                            $value = trim((string)($sa[$key] ?? ''));
                            if ($value !== '' && $value !== '-') $extra[] = $label . ': ' . $value;
                        }
                        if (isset($sa['2fa']) && (int)$sa['2fa'] === 1) $extra[] = '2FA: Yes';

                        $data['account_rows'][] = [
                            'kind'       => 'Marketplace',
                            'kind_icon'  => 'fa-store',
                            'id'         => (int)($sa['id'] ?? 0),
                            'url'        => ADMN_URL . '/selling-account/' . (int)($sa['id'] ?? 0),
                            'title'      => trim((string)($sa['title'] ?? '')) ?: 'Ranked Account',
                            'subtitle'   => strtoupper((string)($sa['server'] ?? '')),
                            'game'       => (string)($sa['game'] ?? ''),
                            'status'     => ((int)($sa['sold'] ?? 0) === 2) ? 'REFUNDED' : 'COMPLETED',
                            'partner'    => (string)($sa['seller_username'] ?? ''),
                            'partner_icon' => $accAvatar($sa['seller_icon'] ?? ''),
                            'partner_url'  => !empty($sa['seller_id']) ? (ADMN_URL . '/seller/' . (int)$sa['seller_id']) : '',
                            'price'      => (int)($sa['price'] ?? 0),
                            'created_at' => (string)($sa['sold_at'] ?? $sa['created_at'] ?? ''),
                            'details'    => implode(' · ', $extra),
                        ];
                    }

                    usort($data['account_rows'], static function ($a, $b) {
                        return strtotime((string)$a['created_at']) <=> strtotime((string)$b['created_at']);
                    });
                    $data['account_rows'] = array_reverse($data['account_rows']);
                    break;

                case 'payments':
                    $data['payments'] = db_get_rows('transactions', ['client_id' => $id], 1);
                    break;
                case 'coins-history':
                    $data['coins_history'] = db_get_rows('coins_history', ['client_id' => $id, 'order' => 'id DESC'], 1);
                    break;
                            case 'payout-methods':
                    // Booster payout methods (bank/crypto) saved by booster
                    $data['payout_methods'] = db_get_rows('booster_payout_methods', ['booster_id' => $id], 1);
                    // Sort: default first, newest first
                    if (is_array($data['payout_methods'])) {
                        usort($data['payout_methods'], function($a,$b){
                            $ad = (int)($a['is_default'] ?? 0);
                            $bd = (int)($b['is_default'] ?? 0);
                            if ($ad !== $bd) return $bd <=> $ad;
                            return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
                        });

// ----- ALT version of $router->get('client/:id/:slug?', ...) BEGIN -----
//     $router->get('client/:id/:slug?', function ($id, $page = 'profile') {
//         global $is_admin;
//         if ($is_admin) {
//             $id = intval($id);
//             $data = db_get_row('clients', ['id' => $id], 1);
//             $loyalty = db_get_row('loyalty_ranks', ['id' => $data['loyalty_rank_id']], 1);
// 
//             $data['loyalty'] = $loyalty;
// 
//             switch ($page) {
//                 case 'orders':
//                     $data['orders'] = db_get_rows('orders', ['client_id' => $id], 1);
//                     // array map order_options and boost_forms to orders
//                     $forms = db_get_rows('boost_forms', ['select' => 'id,type,name,icon,game']);
//                     $forms = array_column($forms, null, 'id');
//                     $boosters = db_get_rows('boosters', ['select' => 'id,username,icon']);
//                     $boosters = array_column($boosters, null, 'id');
//                     foreach ($data['orders'] as $key => $order) {
//                         $order_opts = db_get_row('order_options', ['order_id' => $order['id'], 'select' => 'server,hours,boosters,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo'], 1);
//                         $data['orders'][$key] = array_merge($order_opts, $forms[$order['form_id']], $order);
//                         if (isset($order['booster_id'])) {
//                             $data['orders'][$key]['booster'] = $boosters[$order['booster_id']];
//                         }
//                     }
//                     break;
//                 case 'accounts':
//                     $data['accounts'] = db_get_rows('accounts', ['client_id' => $id, 'status' => 1], 1);
//                     // Purchased marketplace accounts (selling_accounts): sold=1 and client_id is set to the buyer
//                     $data['lol_accounts'] = db_get_rows('selling_accounts', ['client_id' => $id, 'sold' => 1], 1);
//                     break;
// 
//                 case 'payments':
//                     $data['payments'] = db_get_rows('transactions', ['client_id' => $id], 1);
//                     break;
//                 case 'coins-history':
//                     $data['coins_history'] = db_get_rows('coins_history', ['client_id' => $id, 'order' => 'id DESC'], 1);
//                     break;
//                 case 'payout-methods':
//                     // Booster payout methods (bank/crypto) saved by booster
//                     $data['payout_methods'] = db_get_rows('booster_payout_methods', ['booster_id' => $id], 1);
//                     // Sort: default first, newest first
//                     if (is_array($data['payout_methods'])) {
//                         usort($data['payout_methods'], function ($a, $b) {
//                             $ad = (int) ($a['is_default'] ?? 0);
//                             $bd = (int) ($b['is_default'] ?? 0);
//                             if ($ad !== $bd)
//                                 return $bd <=> $ad;
//                             return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
//                         });
// ----- ALT version END -----

                    }
                    break;
            }
            view_file('admin/pages/clients/view', ['data' => $data, 'page' => $page]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('booster/:id/:slug?', function ($id, $page = 'profile') {
        global $is_admin, $db;
        if ($is_admin) {
            $id = intval($id);
            $data = db_get_row('boosters', ['id' => $id]);

            // E-Girls should be managed in the egirl admin area
            if (!empty($data['is_egirl'])) {
                redirect_url('admin-area/egirl/' . $id);
                return;
            }

            $ranks = db_get_rows('booster_ranks');
            $ranks = array_column($ranks, null, 'id');
            $data['games'] = explode('|', $data['games']);
            // Only merge rank if it exists (prevent crash for boosters without rank)
            if (!empty($data['rank_id']) && isset($ranks[$data['rank_id']])) {
                $data = array_merge($ranks[$data['rank_id']], $data);
            }
            switch ($page) {
                case 'profile':
                    $profile = db_get_row('booster_profiles', ['booster_id' => $id], 1);
                    if (empty($profile)) {
                        db_add_row('booster_profiles', ['booster_id' => $id]);
                        $profile = db_get_row('booster_profiles', ['booster_id' => $id], 1);
                    }
                    $limits = db_get_row('booster_limits', ['booster_id' => $id], 1);
                    if (empty($limits)) {
                        db_add_row('booster_limits', ['booster_id' => $id]);
                        $limits = db_get_row('booster_limits', ['booster_id' => $id], 1);
                    }
                    // db_get_row() returns false when no row exists; keep both as arrays
                    // so the array_merge() below cannot fail.
                    $profile = is_array($profile) ? $profile : [];
                    $limits = is_array($limits) ? $limits : [];
                    // Boosters store games as short codes after the migration ('lol', 'val',
                    // 'tft'), while older rows may still use full slugs. Accept both so the
                    // rank-limit fields are always populated (otherwise the tier select falls
                    // back to Iron and looks like it "resets").
                    if (in_array('league-of-legends', $data['games']) || in_array('lol', $data['games'])) {
                        $profile['lol_rank'] = explode('|', $profile['lol_rank'] ?? ' ');
                        $profile['features'] = explode('|', $profile['features'] ?? '||');
                        $limits['lol_rank_limit'] = explode('|', $limits['lol_rank_limit'] ?? '0|0');
                        $limits['lol_tier_limit'] = $limits['lol_rank_limit'][0] ?? 0;
                        $limits['lol_division_limit'] = $limits['lol_rank_limit'][1] ?? 0;
                        unset($limits['lol_rank_limit']);
                    }
                    if (in_array('valorant', $data['games']) || in_array('val', $data['games'])) {
                        $profile['val_rank'] = explode('|', $profile['val_rank'] ?? ' ');
                        $limits['val_rank_limit'] = explode('|', $limits['val_rank_limit'] ?? '0|0');
                        $limits['val_tier_limit'] = $limits['val_rank_limit'][0] ?? 0;
                        $limits['val_division_limit'] = $limits['val_rank_limit'][1] ?? 0;
                        unset($limits['val_rank_limit']);
                    }
                    if (in_array('teamfight-tactics', $data['games']) || in_array('tft', $data['games'])) {
                        $profile['tft_rank'] = explode('|', $profile['tft_rank'] ?? ' ');
                        $limits['tft_rank_limit'] = explode('|', $limits['tft_rank_limit'] ?? '0|0');
                        $limits['tft_tier_limit'] = $limits['tft_rank_limit'][0] ?? 0;
                        $limits['tft_division_limit'] = $limits['tft_rank_limit'][1] ?? 0;
                        unset($limits['tft_rank_limit']);
                    }
                    $data = array_merge($profile, $limits, $data);
                    break;
                case 'orders':
                    // Legacy single-booster orders use orders.booster_id. Team
                    // orders store every assigned member in order_boosters and
                    // keep only the first member in orders.booster_id.
                    $data['orders'] = $db->run(
                        "SELECT DISTINCT o.*
                           FROM orders o
                          WHERE o.booster_id = ?
                             OR EXISTS (
                                SELECT 1
                                  FROM order_boosters ob
                                 WHERE ob.order_id = o.id
                                   AND ob.booster_id = ?
                                   AND ob.status = 'ACTIVE'
                             )
                          ORDER BY o.created_at DESC, o.id DESC",
                        $id,
                        $id
                    ) ?: [];
                    // array map order_options and boost_forms to orders
                    $forms = db_get_rows('boost_forms', ['select' => 'id,type,name,icon,game']);
                    $forms = array_column($forms, null, 'id');
                    foreach ($data['orders'] as $key => $order) {
                        $order_opts = db_get_row('order_options', ['order_id' => $order['id'], 'select' => 'server,hours,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo'], 1) ?: [];
                        $data['orders'][$key] = array_merge($order_opts, $forms[$order['form_id']] ?? [], $order);
                    }
                    break;
                case 'payments':
                    $data['payments'] = db_get_rows('booster_payments', ['booster_id' => $id], 1);
                    break;
                case 'performance':
                    // No extra DB calls needed — performance tab loads via AJAX
                    break;
                            case 'payout-methods':
                    // Booster payout methods (bank/crypto) saved by booster
                    $data['payout_methods'] = db_get_rows('booster_payout_methods', ['booster_id' => $id], 1);
                    // Sort: default first, newest first
                    if (is_array($data['payout_methods'])) {
                        usort($data['payout_methods'], function($a,$b){
                            $ad = (int)($a['is_default'] ?? 0);
                            $bd = (int)($b['is_default'] ?? 0);
                            if ($ad !== $bd) return $bd <=> $ad;
                            return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
                        });

// ----- ALT version of $router->get('booster/:id/:slug?', ...) BEGIN -----
//     $router->get('booster/:id/:slug?', function ($id, $page = 'profile') {
//         global $is_admin;
//         if ($is_admin) {
//             $id = intval($id);
//             $data = db_get_row('boosters', ['id' => $id]);
//             $ranks = db_get_rows('booster_ranks');
//             $ranks = array_column($ranks, null, 'id');
//             $data['games'] = explode('|', $data['games']);
//             $data = array_merge($ranks[$data['rank_id']], $data);
//             switch ($page) {
//                 case 'profile':
//                     $profile = db_get_row('booster_profiles', ['booster_id' => $id], 1);
//                     if (empty($profile)) {
//                         db_add_row('booster_profiles', ['booster_id' => $id]);
//                         $profile = db_get_row('booster_profiles', ['booster_id' => $id], 1);
//                     }
//                     $limits = db_get_row('booster_limits', ['booster_id' => $id], 1);
//                     if (in_array('league-of-legends', $data['games'])) {
//                         $profile['lol_rank'] = explode('|', $profile['lol_rank'] ?? ' ');
//                         $profile['features'] = explode('|', $profile['features'] ?? '||');
//                         $limits['lol_rank_limit'] = explode('|', $limits['lol_rank_limit']);
//                         $limits['lol_tier_limit'] = $limits['lol_rank_limit'][0];
//                         $limits['lol_division_limit'] = $limits['lol_rank_limit'][1];
//                         unset($limits['lol_rank_limit']);
//                     }
//                     if (in_array('valorant', $data['games'])) {
//                         $profile['val_rank'] = explode('|', $profile['val_rank'] ?? ' ');
//                         $limits['val_rank_limit'] = explode('|', $limits['val_rank_limit']);
//                         $limits['val_tier_limit'] = $limits['val_rank_limit'][0];
//                         $limits['val_division_limit'] = $limits['val_rank_limit'][1];
//                         unset($limits['val_rank_limit']);
//                     }
//                     $data = array_merge($profile, $limits, $data);
//                     break;
//                 case 'orders':
//                     $data['orders'] = db_get_rows('orders', ['booster_id' => $id], 1);
//                     // array map order_options and boost_forms to orders
//                     $forms = db_get_rows('boost_forms', ['select' => 'id,type,name,icon,game']);
//                     $forms = array_column($forms, null, 'id');
//                     foreach ($data['orders'] as $key => $order) {
//                         $order_opts = db_get_row('order_options', ['order_id' => $order['id'], 'select' => 'server,hours,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo'], 1);
//                         $data['orders'][$key] = array_merge($order_opts, $forms[$order['form_id']], $order);
//                     }
//                     break;
//                 case 'payments':
//                     $data['payments'] = db_get_rows('booster_payments', ['booster_id' => $id], 1);
//                     break;
//                 case 'payout-methods':
//                     // Booster payout methods (bank/crypto) saved by booster
//                     $data['payout_methods'] = db_get_rows('booster_payout_methods', ['booster_id' => $id], 1);
//                     // Sort: default first, newest first
//                     if (is_array($data['payout_methods'])) {
//                         usort($data['payout_methods'], function ($a, $b) {
//                             $ad = (int) ($a['is_default'] ?? 0);
//                             $bd = (int) ($b['is_default'] ?? 0);
//                             if ($ad !== $bd)
//                                 return $bd <=> $ad;
//                             return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
//                         });
// ----- ALT version END -----

                    }
                    break;
            }

            $data['personal_details'] = db_get_row('booster_personal_details', ['booster_id' => $id], 1);


            // Provide payout methods for tabs (also used on payout-methods page)
            if (!isset($data['payout_methods'])) {
                $data['payout_methods'] = db_get_rows('booster_payout_methods', ['booster_id' => $id], 1);
                if (is_array($data['payout_methods'])) {
                    usort($data['payout_methods'], function($a,$b){
                        $ad = (int)($a['is_default'] ?? 0);
                        $bd = (int)($b['is_default'] ?? 0);
                        if ($ad !== $bd) return $bd <=> $ad;
                        return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
                    });
                }
            }
            
            // Load booster payout methods for ALL booster view tabs (profile, orders, etc.)
            $data['payout_methods'] = db_get_rows('booster_payout_methods', ['booster_id' => $id], 1);
            if (is_array($data['payout_methods'])) {
                usort($data['payout_methods'], function($a,$b){
                    $ad = (int)($a['is_default'] ?? 0);
                    $bd = (int)($b['is_default'] ?? 0);
                    if ($ad !== $bd) return $bd <=> $ad;
                    return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
                });
            }

view_file('admin/pages/boosters/view', ['data' => $data, 'page' => $page]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('booster/payments', function () {
        global $is_admin;
        if ($is_admin) {
            $data = db_get_rows('booster_payments', [], 1);
            $boosters = db_get_rows('boosters', ['select' => 'id,username']);
            $boosters = array_column($boosters, null, 'id');

            foreach ($data as $key => $payment) {
                $sender = db_get_row('admins', ['id' => $payment['sender_id'], 'select' => 'id,username,icon'], 1);

                if (isset($boosters[$payment['booster_id']])) {
                    $data[$key] = array_merge($boosters[$payment['booster_id']], $payment);
                } else {
                    $data[$key] = $payment;
                    $data[$key]['booster_info'] = null;
                }

                $data[$key]['sender_data'] = $sender;
            }

            view_file('admin/pages/boosters/payments', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

// ----- Merged from routes alt.php: missing route -----
    $router->get('booster/payments/data', function () {
        global $is_admin, $db;

        if (!$is_admin) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 8;
        $search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
        $orderColIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 6;
        $orderDir = isset($_GET['order'][0]['dir']) ? strtoupper($_GET['order'][0]['dir']) : 'DESC';
        $orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';
        $start = max(0, $start);
        $length = max(1, min(100, $length));

        $orderColumns = [
            0 => 'bp.id',
            1 => 'b.username',
            2 => "IFNULL(a.username, '')",
            3 => 'bp.type',
            4 => 'bp.note',
            5 => 'bp.amount',
            6 => 'bp.created_at',
        ];
        $orderBy = $orderColumns[$orderColIndex] ?? 'bp.created_at';

        $conditions = [];

        if ($search !== '') {
            $like = '%' . esc($search) . '%';
            $conditions[] = "(CAST(bp.id AS CHAR) LIKE '" . $like . "'"
                          . " OR b.username LIKE '" . $like . "'"
                          . " OR a.username LIKE '" . $like . "'"
                          . " OR bp.type LIKE '" . $like . "'"
                          . " OR bp.note LIKE '" . $like . "'"
                          . " OR CAST(bp.amount AS CHAR) LIKE '" . $like . "')";
        }

        $typeFilter = isset($_GET['type_filter']) ? trim($_GET['type_filter']) : '';
        if ($typeFilter !== '') {
            $conditions[] = "bp.type = '" . esc($typeFilter) . "'";
        }

        $senderFilter = isset($_GET['sender_filter']) ? trim($_GET['sender_filter']) : '';
        if ($senderFilter === 'system') {
            $conditions[] = "bp.sender_id IS NULL";
        } elseif ($senderFilter !== '') {
            $conditions[] = "a.username = '" . esc($senderFilter) . "'";
        }

        $where = !empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';

        $totalCount = db_get_row_count('booster_payments');
        $sqlCount = "SELECT COUNT(*) AS cnt FROM booster_payments bp LEFT JOIN boosters b ON b.id = bp.booster_id LEFT JOIN admins a ON a.id = bp.sender_id" . $where;
        $countRow = $db->run($sqlCount);
        $filteredCount = isset($countRow[0]['cnt']) ? (int) $countRow[0]['cnt'] : 0;

        $sqlData = "SELECT bp.*, b.username AS booster_username, a.username AS sender_username, a.icon AS sender_icon FROM booster_payments bp LEFT JOIN boosters b ON b.id = bp.booster_id LEFT JOIN admins a ON a.id = bp.sender_id" . $where . " ORDER BY " . $orderBy . " " . $orderDir . " LIMIT " . $length . " OFFSET " . $start;
        $rows = $db->run($sqlData) ?: [];

        // Resolve IDs in log action text to readable names without changing stored logs.
        // Examples: "booster #737" -> "booster Paradox (#737)".
        $adminLogNameCache = [
            'admin'   => [],
            'booster' => [],
            'client'  => [],
            'seller'  => [],
        ];
        $resolveAdminLogPersonName = function (string $type, int $id) use (&$adminLogNameCache, $db): string {
            if ($id <= 0) {
                return '#' . $id;
            }
            if (isset($adminLogNameCache[$type][$id])) {
                return $adminLogNameCache[$type][$id];
            }

            $table = null;
            if ($type === 'admin')   $table = 'admins';
            if ($type === 'booster') $table = 'boosters';
            if ($type === 'client')  $table = 'clients';
            if ($type === 'seller')  $table = 'sellers';

            if (!$table) {
                return '#' . $id;
            }

            try {
                $row = $db->row("SELECT username, email FROM {$table} WHERE id = ? LIMIT 1", $id);
            } catch (Throwable $e) {
                $row = [];
            }

            $name = trim((string)($row['username'] ?? ''));
            if ($name === '') {
                $name = trim((string)($row['email'] ?? ''));
            }

            $adminLogNameCache[$type][$id] = $name !== '' ? ($name . ' (#' . $id . ')') : ('#' . $id);
            return $adminLogNameCache[$type][$id];
        };

        $resolveAdminLogActionNames = function (string $action) use ($resolveAdminLogPersonName): string {
            // Important: "booster account #123" means a booster, not a shop account.
            // Therefore handle "<person> account #id" before the generic "account #id" linkifier.
            $action = preg_replace_callback('/\b(admin|booster|egirl|e-girl|gaminggirl|gg-girl|client|seller)\s+account\s+#?(\d+)\b/i', function ($m) use ($resolveAdminLogPersonName) {
                $labelType = $m[1];
                $type = strtolower($labelType);
                if (in_array($type, ['egirl', 'e-girl', 'gaminggirl', 'gg-girl'], true)) {
                    $type = 'booster';
                }
                return $labelType . ' account ' . $resolveAdminLogPersonName($type, (int)$m[2]);
            }, $action);

            $patterns = [
                'admin'   => '/\b(admin)\s+#(\d+)\b/i',
                'booster' => '/\b(booster|egirl|e-girl|gaminggirl|gg-girl)\s+#(\d+)\b/i',
                'client'  => '/\b(client)\s+#(\d+)\b/i',
                'seller'  => '/\b(seller)\s+#(\d+)\b/i',
            ];

            foreach ($patterns as $type => $regex) {
                $action = preg_replace_callback($regex, function ($m) use ($type, $resolveAdminLogPersonName) {
                    return $m[1] . ' ' . $resolveAdminLogPersonName($type, (int)$m[2]);
                }, $action);
            }

            // Also handle common variants like "seller_id 12" or "client_id: 12".
            $action = preg_replace_callback('/\b(admin|booster|client|seller)_id\s*[:=]?\s*(\d+)\b/i', function ($m) use ($resolveAdminLogPersonName) {
                $type = strtolower($m[1]);
                return $m[1] . '_id: ' . $resolveAdminLogPersonName($type, (int)$m[2]);
            }, $action);

            return $action;
        };

        $data = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id'] ?? 0);
            $boosterId = (int) ($r['booster_id'] ?? 0);
            $boosterUsername = esc($r['booster_username'] ?? '—');
            $boosterHtml = '<a href="' . ADMN_URL . '/booster/' . $boosterId . '">' . $boosterUsername . '</a>';

            $senderUsername = $r['sender_username'] ?? null;
            $senderIcon = $r['sender_icon'] ?? null;
            $senderHtml = empty($senderUsername) ? 'System' : util_format_user($senderUsername, $senderIcon);

            $typeHtml = util_format_default_type($r['type'] ?? '');
            $note = htmlspecialchars($r['note'] ?? '', ENT_QUOTES);

            $amount = isset($r['amount']) ? (int) $r['amount'] : 0;
            $amountClass = $amount < 0 ? 'text-danger' : 'text-success';
            $currency = esc($r['currency'] ?? '');
            $amountHtml = '<span class="' . $amountClass . '">' . util_format_price_display($amount) . ' ' . $currency . '</span>';

            $createdRaw = $r['created_at'] ?? '';
            $createdHtml = '<span data-order="' . htmlspecialchars($createdRaw, ENT_QUOTES) . '">' . util_format_date_display($createdRaw) . '</span>';

            $data[] = [
                '#' . $id,
                $boosterHtml,
                $senderHtml,
                $typeHtml,
                $note,
                $amountHtml,
                $createdHtml,
                $r['type'] ?? '',                  // index 7 – raw type (für Filter)
                $r['sender_username'] ?? 'system', // index 8 – raw sender (für Filter)
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => (int) $totalCount,
            'recordsFiltered' => (int) $filteredCount,
            'data' => $data,
        ]);
        exit;
    });

    $router->get('booster/payments/senders', function () {
        global $is_admin, $db;
        if (!$is_admin) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }
        $rows = $db->run(
            "SELECT DISTINCT a.username, a.icon
             FROM booster_payments bp
             LEFT JOIN admins a ON a.id = bp.sender_id
             WHERE bp.sender_id IS NOT NULL AND a.username IS NOT NULL
             ORDER BY a.username ASC"
        ) ?: [];
        $senders = [['username' => 'system', 'label' => 'System', 'icon' => null]];
        foreach ($rows as $r) {
            $senders[] = ['username' => $r['username'], 'label' => $r['username'], 'icon' => $r['icon'] ?? null];
        }
        header('Content-Type: application/json');
        echo json_encode($senders);
        exit;
    });


// ----- ALT version of $router->get('booster/payments', ...) BEGIN -----
//     $router->get('booster/payments', function () {
//         global $is_admin;
//         if ($is_admin) {
//             // Render the page. Data will be loaded via server-side AJAX endpoint.
//             view_file('admin/pages/boosters/payments', ['data' => []]);
//         } else {
//             redirect_url('admin-area/auth/login');
//         }
//     });
// ----- ALT version END -----

    $router->get('transactions', function () {
        global $is_admin;
        if ($is_admin) {
            // Render page only. Transaction rows are loaded through the server-side DataTables endpoint below.
            view_file('admin/pages/transactions', ['data' => []]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

$router->get('transactions/data', function () {
        global $is_admin, $db;

        if (!$is_admin) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 8;
        $search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
        $orderColIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 7;
        $orderDir = isset($_GET['order'][0]['dir']) ? strtoupper($_GET['order'][0]['dir']) : 'DESC';
        $orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';

        $start = max(0, $start);
        $length = max(1, min(100, $length));

        $orderColumns = [
            0 => 't.id',
            1 => 'c.username',
            2 => 't.processor',
            3 => 't.invoice_id',
            4 => 't.token',
            5 => 't.status',
            6 => 't.amount',
            7 => 't.created_at',
        ];
        $orderBy = $orderColumns[$orderColIndex] ?? 't.created_at';

        $conditions = [];
        if ($search !== '') {
            $like = '%' . esc($search) . '%';
            $conditions[] = "(CAST(t.id AS CHAR) LIKE '" . $like . "'"
                          . " OR c.username LIKE '" . $like . "'"
                          . " OR t.processor LIKE '" . $like . "'"
                          . " OR CAST(t.invoice_id AS CHAR) LIKE '" . $like . "'"
                          . " OR t.token LIKE '" . $like . "'"
                          . " OR t.status LIKE '" . $like . "'"
                          . " OR CAST(t.amount AS CHAR) LIKE '" . $like . "'"
                          . " OR t.currency LIKE '" . $like . "')";
        }

        $where = !empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';

        $totalCount = db_get_row_count('transactions');
        $sqlCount = "SELECT COUNT(*) AS cnt
                     FROM transactions t
                     LEFT JOIN clients c ON c.id = t.client_id" . $where;
        $countRow = $db->run($sqlCount);
        $filteredCount = isset($countRow[0]['cnt']) ? (int)$countRow[0]['cnt'] : 0;

        $sqlData = "SELECT t.*, c.username AS client_username
                    FROM transactions t
                    LEFT JOIN clients c ON c.id = t.client_id" . $where . "
                    ORDER BY " . $orderBy . " " . $orderDir . "
                    LIMIT " . $length . " OFFSET " . $start;
        $rows = $db->run($sqlData) ?: [];

        $data = [];
        foreach ($rows as $r) {
            $id = (int)($r['id'] ?? 0);
            $clientId = (int)($r['client_id'] ?? 0);
            $clientUsername = trim((string)($r['client_username'] ?? ''));
            $clientHtml = $clientUsername !== ''
                ? '<a href="' . ADMN_URL . '/client/' . $clientId . '">' . esc($clientUsername) . '</a>'
                : '—';

            $amount = isset($r['amount']) ? (int)$r['amount'] : 0;
            $currency = esc($r['currency'] ?? '');
            $amountHtml = util_format_price_display($amount) . ' ' . $currency;

            $createdRaw = $r['created_at'] ?? '';
            $createdHtml = '<span data-order="' . htmlspecialchars($createdRaw, ENT_QUOTES) . '">' . util_format_date_display($createdRaw) . '</span>';

            $data[] = [
                '#' . $id,
                $clientHtml,
                util_format_default_type($r['processor'] ?? ''),
                '#' . esc($r['invoice_id'] ?? ''),
                esc($r['token'] ?? ''),
                util_format_tx_status($r['status'] ?? ''),
                $amountHtml,
                $createdHtml,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => (int)$totalCount,
            'recordsFiltered' => (int)$filteredCount,
            'data' => $data,
        ]);
        exit;
    });
    $router->get('invoices', function () {
        global $is_admin;
        if ($is_admin) {
            $data = db_get_rows('invoices', ['order_type' => 'invoice'], 1);
            $clients = db_get_rows('clients', ['select' => 'id,username']);
            $clients = array_column($clients, null, 'id');
            foreach ($data as $key => $invoice) {
                $data[$key] = array_merge($clients[$invoice['client_id']], $invoice);
            }
            view_file('admin/pages/invoices', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('account-package/add', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'samsayahix@gmail.com' 
            ) {
                redirect_url('admin-area/orders');
            }
            view_file('admin/pages/packages/add');
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    $router->get('account-packages', function () {
    global $is_admin;
    if ($is_admin) {
        if (
            ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
            ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
            ADMIN_DATA['email'] !== 'duck_sauce@live.de' &&
            ADMIN_DATA['email'] !== 'mohamedzeyad474@gmail.com' &&
            ADMIN_DATA['email'] !== 'samsayahix@gmail.com' 

        ) {
            redirect_url('admin-area/orders');
        }

        // Filters (GET):
        //   status: all | enabled | disabled
        //   game:   all | lol | val
        //   server: depends on game
        // Default on first visit: enabled
        $status = $_GET['status'] ?? 'enabled';
        $game   = $_GET['game'] ?? 'all';
        $server = $_GET['server'] ?? 'all';

        $allowedStatus = ['all', 'enabled', 'disabled'];
        $allowedGame   = ['all', 'league-of-legends', 'valorant'];

        if (!in_array($status, $allowedStatus, true)) $status = 'all';
        if (!in_array($game, $allowedGame, true)) $game = 'all';

        // Allowed servers vary by game
        if ($game === 'valorant') {
            $allowedServer = ['all', 'eu', 'na'];
        } elseif ($game === 'league-of-legends') {
            $allowedServer = ['all', 'euw', 'eune', 'na'];
        } else {
            $allowedServer = ['all', 'euw', 'eune', 'eu', 'na'];
        }
        if (!in_array($server, $allowedServer, true)) $server = 'all';

        $data = db_get_rows('account_packages', [], 1);

        foreach ($data as $key => $row) {
            $data[$key]['available_count'] = db_get_row_count('accounts', [
                'package_id' => (int)$row['id'],
                'status' => 0
            ]);
        }

        // Apply filters in PHP (safe regardless of DB storage type)
        if ($status !== 'all' || $server !== 'all' || $game !== 'all') {
            $data = array_values(array_filter($data, function ($row) use ($status, $server, $game) {
                // game filter
                if ($game !== 'all') {
                    $gid = (int)($row['game_id'] ?? 1);
                    if ($game === 'league-of-legends' && $gid !== 1) return false;
                    if ($game === 'valorant' && $gid !== 2) return false;
                }

                // server filter
                if ($server !== 'all') {
                    $rowServer = strtolower((string)($row['server'] ?? ''));

                    // Backward-compat mapping: treat euw/eune as EU for Valorant
                    if ($game === 'valorant' || ((int)($row['game_id'] ?? 1) === 2)) {
                        if ($server === 'eu') {
                            if (!in_array($rowServer, ['eu', 'euw', 'eune'], true)) return false;
                        } else {
                            if ($rowServer !== $server) return false;
                        }
                    } else {
                        if ($rowServer !== $server) return false;
                    }
                }

                // status filter (supports 1/0, "enabled"/"disabled", "Enabled"/"Disabled")
                if ($status !== 'all') {
                    $raw = $row['status'] ?? null;
                    $norm = strtolower(trim((string)$raw));

                    $isEnabled =
                        $raw === 1 || $raw === '1' ||
                        $norm === '1' ||
                        $norm === 'enabled' ||
                        $norm === 'enable' ||
                        $norm === 'true';

                    if ($status === 'enabled' && !$isEnabled) return false;
                    if ($status === 'disabled' && $isEnabled) return false;
                }

                return true;
            }));
        }

        view_file('admin/pages/packages/list', ['data' => $data]);
    } else {
        redirect_url('admin-area/auth/login');
    }
});

// ----- ALT version of $router->get('account-packages', ...) BEGIN -----
//     $router->get('account-packages', function () {
//         global $is_admin;
//         if ($is_admin) {
//             if (
//                 ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
//                 ADMIN_DATA['email'] !== 'nimm2oder3@gmx.de' &&
//                 ADMIN_DATA['email'] !== 'duck_sauce@live.de' &&
//                 ADMIN_DATA['email'] !== 'mohamedzeyad474@gmail.com' &&
//                 ADMIN_DATA['email'] !== 'hbilalshah@gmail.com' &&
//                 ADMIN_DATA['email'] !== 'lovely@lolboost.gg'
//             ) {
//                 redirect_url('admin-area/orders');
//             }
//             $data = db_get_rows('account_packages', [], 1);
//             view_file('admin/pages/packages/list', ['data' => $data]);
//         } else {
//             redirect_url('admin-area/auth/login');
//         }
//     });
// ----- ALT version END -----

$router->get('account-package/:id', function ($id) {
        global $is_admin, $db;
        if ($is_admin) {
            $id = intval($id);
            $data = db_get_row('account_packages', ['id' => $id]);
            if (empty($data)) {
                redirect_url('admin-area/account-packages');
            }
            $data['features'] = explode('|', $data['features']);
            $data['price'] = util_format_price_input($data['price']);
            $data['accounts'] = $db->run("
                SELECT a.*, ad.username AS uploaded_by_admin, ad.email AS uploaded_by_admin_email
                FROM accounts a
                LEFT JOIN admins ad ON ad.id = a.admin_id
                WHERE a.package_id = ?
                ORDER BY a.id DESC
            ", $id);
            view_file('admin/pages/packages/view', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('items', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $data = $db->run(
            "SELECT si.*, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon
             FROM selling_items si
             LEFT JOIN sellers s ON s.id = si.seller_id
             ORDER BY si.created_at DESC"
        ) ?: [];

        view_file('admin/pages/items/list', ['data' => $data]);
    });

    $router->get('item/:id', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $id = (int)$id;
        $item = $db->row(
            "SELECT si.*,
                    s.username AS seller_username,
                    s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.email AS seller_email,
                    s.icon AS seller_icon,
                    s.fee_percent AS seller_fee_percent
             FROM selling_items si
             LEFT JOIN sellers s ON s.id = si.seller_id
             WHERE si.id = ?
             LIMIT 1",
            $id
        );
        if (empty($item)) { redirect_url('admin-area/items'); return; }

        $seller = !empty($item['seller_id'])
            ? [
                'id' => (int)$item['seller_id'],
                'username' => $item['seller_username'] ?? null,
                'email' => $item['seller_email'] ?? null,
                'icon' => $item['seller_icon'] ?? null,
                'fee_percent' => $item['seller_fee_percent'] ?? null,
            ]
            : null;

        view_file('admin/pages/items/view', compact('item', 'seller'));
    });

    $router->get('item-orders', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $orders = $db->run(
            "SELECT sip.*, si.title AS item_title, si.images AS item_images, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, c.username AS client_username, c.icon AS client_icon
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN sellers s ON s.id = sip.seller_id
             LEFT JOIN clients c ON c.id = sip.client_id
             ORDER BY sip.created_at DESC, sip.id DESC"
        ) ?: [];
        $listings = $db->run(
            "SELECT si.*, s.username AS seller_username, s.icon AS seller_icon
             FROM selling_items si
             LEFT JOIN sellers s ON s.id = si.seller_id
             ORDER BY si.created_at DESC"
        ) ?: [];
        $marketplaceType = 'items';
        $meta = ['title' => 'Item Orders | Admin Area', 'h1' => 'Item Orders'];
        view_file('admin/pages/marketplace/orders', compact('meta', 'orders', 'listings', 'marketplaceType'));
    });

    $router->get('item-order/:id', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $id = (int)$id;
        $purchase = $db->row(
            "SELECT sip.*, si.title AS item_title, si.images, si.description AS item_description,
                    s.username AS seller_username, s.email AS seller_email, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active,
                    c.username AS client_username, c.email AS client_email, c.icon AS client_icon
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN sellers s ON s.id = sip.seller_id
             LEFT JOIN clients c ON c.id = sip.client_id
             WHERE sip.id = ?
             LIMIT 1",
            $id
        );
        if (empty($purchase)) { redirect_url('admin-area/item-orders'); return; }

        $details = db_get_row('selling_item_purchase_details', ['purchase_id' => $id], 1) ?: [];
        $seller = ['id'=>(int)($purchase['seller_id'] ?? 0), 'username'=>$purchase['seller_username'] ?? 'Seller', 'email'=>$purchase['seller_email'] ?? '', 'icon'=>$purchase['seller_icon'] ?? ''];
        $buyer = ['id'=>(int)($purchase['client_id'] ?? 0), 'username'=>$purchase['client_username'] ?? 'Client', 'email'=>$purchase['client_email'] ?? '', 'icon'=>$purchase['client_icon'] ?? ''];
        $chat = [];
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_item_purchase_' . $id) . '.json';
        if (file_exists($chat_path)) {
            $raw = @file_get_contents($chat_path);
            if ($raw) {
                $decoded = @json_decode($raw, true);
                $chat = $decoded['messages'] ?? [];
            }
        }

        view_file('admin/pages/items/order_view', compact('purchase', 'details', 'seller', 'buyer', 'chat'));
    });

    // Backward-compatible admin URL used by the live website.
    $router->get('accounts', function () {
        global $is_admin;
        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }
        redirect_url('admin-area/selling-accounts');
    });

    $router->get('selling-accounts', function () {
        global $is_admin, $db;

        if (!$is_admin) {
            redirect_url('admin-area/auth/login');
            return;
        }

        // Ranked marketplace accounts, including seller and buyer names.
        $data = $db->run(
            // Older sales were stored without sold_at, so fall back to the paid
            // invoice date instead of rendering an empty \"Sold at\" cell.
            "SELECT sa.*,
                    COALESCE(sa.sold_at, inv.paid_at, inv.created_at) AS sold_at,
                    s.username AS seller_username,
                    c.username AS client_username
             FROM selling_accounts sa
             LEFT JOIN sellers s ON s.id = sa.seller_id
             LEFT JOIN clients c ON c.id = sa.client_id
             LEFT JOIN (
                 SELECT order_id, MAX(paid_at) AS paid_at, MAX(created_at) AS created_at
                 FROM invoices
                 WHERE order_type = 'lol_account' AND status = 'PAID'
                 GROUP BY order_id
             ) inv ON inv.order_id = sa.id
             ORDER BY sa.created_at DESC, sa.id DESC"
        ) ?: [];

        // Premium / smurf accounts from the legacy `accounts` table.
        // These rows are normalized by the shared account list view.
        $smurfRows = $db->run(
            "SELECT a.*,
                    ap.name AS package_name,
                    ap.price AS package_price,
                    ap.server AS package_server,
                    ap.game_id AS package_game_id,
                    c.username AS client_username,
                    ad.username AS uploaded_by_admin,
                    COALESCE(i.total_price, ap.price) AS sold_price,
                    i.currency AS sold_currency
             FROM accounts a
             LEFT JOIN account_packages ap ON ap.id = a.package_id
             LEFT JOIN clients c ON c.id = a.client_id
             LEFT JOIN admins ad ON ad.id = a.admin_id
             LEFT JOIN (
                 SELECT i1.*
                 FROM invoices i1
                 INNER JOIN (
                     SELECT client_id, order_id, MAX(id) AS max_id
                     FROM invoices
                     WHERE LOWER(order_type) = 'account'
                     GROUP BY client_id, order_id
                 ) latest ON latest.max_id = i1.id
             ) i ON i.client_id = a.client_id AND i.order_id = a.package_id
             ORDER BY COALESCE(a.sold_at, a.created_at) DESC, a.id DESC"
        ) ?: [];

        view_file('admin/pages/accounts/list', [
            'data' => $data,
            'smurfRows' => $smurfRows,
        ]);
    });

// ----- ALT version of $router->get('selling-accounts', ...) BEGIN -----
//     $router->get('selling-accounts', function () {
//         global $is_admin;
// 
//         if ($is_admin) {
//             $data = db_get_rows('selling_accounts', [], 1);
// 
//             view_file('admin/pages/accounts/list', ['data' => $data]);
//         } else {
//             redirect_url('admin-area/auth/login');
//         }
//     });
// ----- ALT version END -----


    $router->get('selling-account/:id', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $id = (int)$id;
        $account = db_get_row('selling_accounts', ['id' => $id]);
        if (empty($account)) { redirect_url('admin-area/selling-accounts'); return; }

        // Load seller info
        $seller = !empty($account['seller_id'])
            ? $db->row("SELECT id, username, email, icon, fee_percent FROM sellers WHERE id = ? LIMIT 1", (int)$account['seller_id'])
            : null;

        // Older marketplace sales did not always persist client_id on the account.
        // Recover the buyer from the latest paid invoice so sold accounts never show
        // "No buyer assigned" while the order list already knows the buyer.
        $buyerId = (int)($account['client_id'] ?? 0);
        if ($buyerId <= 0 && !empty($account['sold'])) {
            $invoiceBuyer = $db->row(
                "SELECT client_id FROM invoices
                 WHERE order_id = ? AND LOWER(order_type) = 'lol_account'
                   AND status = 'PAID' AND client_id IS NOT NULL AND client_id > 0
                 ORDER BY COALESCE(paid_at, created_at) DESC, id DESC LIMIT 1",
                $id
            );
            $buyerId = (int)($invoiceBuyer['client_id'] ?? 0);
            if ($buyerId > 0) $account['client_id'] = $buyerId;
        }
        $buyer = $buyerId > 0
            ? $db->row("SELECT id, username, email, icon FROM clients WHERE id = ? LIMIT 1", $buyerId)
            : null;

        // Load chat messages from JSON file
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_account_' . $id) . '.json';
        $chat = [];
        if (file_exists($chat_path)) {
            $raw = @file_get_contents($chat_path);
            if ($raw) {
                $decoded = @json_decode($raw, true);
                $chat = $decoded['messages'] ?? [];
            }
        }

        view_file('admin/pages/accounts/view', compact('account', 'seller', 'buyer', 'chat'));
    });

    // ======================================================
    // Selling Smurfs (Sold Only)
    // ======================================================
    $router->get('selling-smurfs', function () {
        global $is_admin, $db;

        if ($is_admin) {
            // Smurf accounts live in `accounts`.
            // In your DB: status = 1 means SOLD.
            // IMPORTANT: invoices.order_id refers to accounts.package_id (NOT accounts.id).
            // We show only sold accounts that have a buyer (accounts.client_id IS NOT NULL).

            $sql = "
                SELECT
                    a.id,
                    a.package_id,
                    a.login,
                    a.password,
                    a.data,
                    a.status,
                    a.created_at,
                    a.client_id,
                    COALESCE(a.sold_at, i.created_at, a.created_at) AS sold_at,

                    ap.name  AS package_name,
                    ap.price AS package_price,

                    c.username AS client_username,
                    COALESCE(i.total_price, ap.price) AS sold_price,
                    i.currency AS sold_currency

                FROM accounts a

                LEFT JOIN account_packages ap ON ap.id = a.package_id
                LEFT JOIN (
                    SELECT client_id, order_id, MAX(id) AS max_id
                    FROM invoices
                    WHERE LOWER(order_type) = 'account'
                      AND status = 'PAID'
                      AND client_id IS NOT NULL
                      AND client_id <> 0
                    GROUP BY client_id, order_id
                ) im ON im.client_id = a.client_id AND im.order_id = a.package_id
                LEFT JOIN invoices i ON i.id = im.max_id

                LEFT JOIN clients c ON c.id = a.client_id

                WHERE a.status = 1
                  AND a.client_id IS NOT NULL

                ORDER BY COALESCE(a.sold_at, i.created_at, a.created_at) DESC
                LIMIT 2000
            ";

            $data = $db->run($sql);

            view_file('admin/pages/accounts/selling_smurfs_sold', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    // Backwards-compat: old URL -> new URL
    $router->get('sold-accounts', function () {
        global $is_admin;
        if ($is_admin) {
            redirect_url('admin-area/selling-smurfs');
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    // =============================================
    // ADMIN: SELLER MANAGEMENT ROUTES
    // =============================================
    $router->get('seller-applications', function () {
        global $db, $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $migrations = [
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS fullname VARCHAR(191) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS dob VARCHAR(32) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS address TEXT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS country VARCHAR(100) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS id_front VARCHAR(512) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS id_back VARCHAR(512) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS selfie VARCHAR(512) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS onboarding_status VARCHAR(16) NOT NULL DEFAULT 'pending'",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS discord VARCHAR(191) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS application_note TEXT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS icon VARCHAR(512) NULL",
        ];
        foreach ($migrations as $sql) {
            try { $db->run($sql); } catch (\Throwable $e) {}
        }
        $pending = $db->run("SELECT * FROM sellers WHERE is_active = 0 AND (is_banned IS NULL OR is_banned = 0) AND onboarding_status = 'pending' ORDER BY id DESC") ?: [];
        $rejected = $db->run("SELECT * FROM sellers WHERE onboarding_status = 'rejected' ORDER BY id DESC LIMIT 50") ?: [];
        $meta = ['title' => 'Seller Applications - Admin Area | LoLBoost', 'h1' => 'Seller Applications', 'description' => 'Review seller onboarding applications.'];
        view_file('admin/pages/sellers/applications', ['meta' => $meta, 'pending' => $pending, 'rejected' => $rejected]);
    });

    $router->get('sellers', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        // Ensure extended columns exist
        try { $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
        try { $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS discord VARCHAR(191) NULL"); } catch (\Throwable $e) {}
        try { $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS application_note TEXT NULL"); } catch (\Throwable $e) {}
        try { $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS icon VARCHAR(512) NULL"); } catch (\Throwable $e) {}
        try { $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS allowed_games TEXT NULL COMMENT 'comma-separated game slugs; empty = all games allowed'"); } catch (\Throwable $e) {}
        try { $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS can_list_digital_goods TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
        try {
            $data = $db->run("SELECT * FROM sellers ORDER BY id DESC") ?: [];
        } catch (\Throwable $e) {
            $data = [];
        }
        $default_fee = 15.0;
        view_file('admin/pages/sellers/list', compact('data', 'default_fee'));
    });

    $router->get('seller/:id', function ($id) {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        redirect_url('admin-area/seller/' . (int)$id . '/profile');
    });

    $router->get('seller/:id/:slug?', function ($id, $page = 'profile') {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $id = (int)$id;
        $allowedPages = ['profile', 'accounts', 'items', 'topups', 'digital-goods', 'payouts', 'payments', 'payout-methods', 'reviews'];
        if (!in_array($page, $allowedPages, true)) {
            $page = 'profile';
        }

        try {
            $data = $db->row("SELECT * FROM sellers WHERE id = ? LIMIT 1", $id);
            if (empty($data)) { redirect_url('admin-area/sellers'); return; }

            $default_fee = 15.0;
            $accounts = $db->run("SELECT * FROM selling_accounts WHERE seller_id = ? ORDER BY created_at DESC", $id) ?: [];
            $payments = $db->run("SELECT * FROM seller_payments WHERE seller_id = ? ORDER BY id DESC", $id) ?: [];
            $payouts  = $db->run("SELECT * FROM seller_payout_requests WHERE seller_id = ? ORDER BY id DESC", $id) ?: [];
            try {
                $items = $db->run("SELECT si.*, g.name AS db_game_name, g.icon AS game_icon FROM selling_items si LEFT JOIN games g ON g.id = si.game_id WHERE si.seller_id = ? ORDER BY si.created_at DESC", $id) ?: [];
            } catch (\Throwable $e) { $items = []; }
            try {
                $topups = $db->run("SELECT st.*, g.name AS db_game_name, g.icon AS game_icon FROM selling_topups st LEFT JOIN games g ON g.id = st.game_id WHERE st.seller_id = ? ORDER BY st.created_at DESC", $id) ?: [];
            } catch (\Throwable $e) { $topups = []; }
            try {
                $digitalGoods = $db->run("SELECT dg.*, dgc.name AS category_name FROM digital_goods dg LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id WHERE dg.seller_id = ? ORDER BY dg.created_at DESC, dg.id DESC", $id) ?: [];
            } catch (\Throwable $e) { $digitalGoods = []; }
            $reviews  = $db->run(
                "SELECT sr.*, c.username AS client_username, c.icon AS client_icon
                 FROM seller_reviews sr
                 LEFT JOIN clients c ON c.id = sr.client_id
                 WHERE sr.seller_id = ?
                 ORDER BY sr.created_at DESC",
                $id
            ) ?: [];
        } catch (\Throwable $e) {
            redirect_url('admin-area/sellers'); return;
        }

        $viewMap = [
            'profile'        => 'admin/pages/sellers/profile',
            'accounts'       => 'admin/pages/sellers/accounts',
            'items'          => 'admin/pages/sellers/items',
            'topups'         => 'admin/pages/sellers/topups',
            'digital-goods'  => 'admin/pages/sellers/digital-goods',
            'payouts'        => 'admin/pages/sellers/payouts',
            'payments'       => 'admin/pages/sellers/payments',
            'payout-methods' => 'admin/pages/sellers/payout-methods',
            'reviews'        => 'admin/pages/sellers/reviews',
        ];

        view_file($viewMap[$page], compact('data', 'default_fee', 'accounts', 'items', 'topups', 'digitalGoods', 'payments', 'payouts', 'reviews', 'page'));
    });

    // Sync all unpaid admin #51 accounts to seller #28 (one-time + safe to re-run)
    $router->post('seller/28/sync-admin-payouts', function () {
        global $is_admin;
        if (!$is_admin) { echo json_encode(['ok' => false, 'message' => 'Unauthorized']); exit; }
        if (!function_exists('seller28_sync_admin_account_payouts')) {
            echo json_encode(['ok' => false, 'message' => 'Function not available']); exit;
        }
        $result = seller28_sync_admin_account_payouts();
        echo json_encode([
            'ok'        => true,
            'processed' => $result['processed'],
            'paid'      => $result['paid'],
            'skipped'   => $result['skipped'],
            'total_eur' => number_format($result['total_cents'] / 100, 2),
            'errors'    => $result['errors'],
        ]);
        exit;
    });



    $router->get('top-up-orders', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        try {
            $orders = $db->run(
                "SELECT p.*, st.image, st.instructions, st.offer_title AS listing_offer_title, st.offer_amount AS listing_offer_amount, st.offer_unit AS listing_offer_unit,
                        g.icon AS game_icon, g.name AS db_game_name, g.slug AS db_game_slug,
                        s.username AS seller_username, s.email AS seller_email, s.icon AS seller_icon,
                        c.username AS client_username, c.email AS client_email, c.icon AS client_icon, c.icon AS client_avatar
                 FROM selling_topup_purchases p
                 LEFT JOIN selling_topups st ON st.id = p.topup_id
                 LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                 LEFT JOIN sellers s ON s.id = p.seller_id
                 LEFT JOIN clients c ON c.id = p.client_id
                 ORDER BY COALESCE(p.paid_at, p.created_at) DESC, p.id DESC"
            ) ?: [];
            $listings = $db->run(
                "SELECT st.*, g.name AS db_game_name, g.icon AS game_icon, s.username AS seller_username, s.icon AS seller_icon
                 FROM selling_topups st
                 LEFT JOIN games g ON g.id = st.game_id
                 LEFT JOIN sellers s ON s.id = st.seller_id
                 ORDER BY st.created_at DESC, st.id DESC"
            ) ?: [];
        } catch (Throwable $e) { $orders = []; $listings = []; }
        $marketplaceType = 'topups';
        $meta = ['title' => 'Top Up Orders | Admin Area', 'h1' => 'Top Up Orders'];
        view_file('admin/pages/marketplace/orders', compact('meta', 'orders', 'listings', 'marketplaceType'));
    });

    $router->get('top-up-order/:id', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        $id = (int)$id;
        try {
            $purchase = $db->row(
                "SELECT p.*, st.image, st.instructions, st.offer_title AS listing_offer_title, st.offer_amount AS listing_offer_amount, st.offer_unit AS listing_offer_unit,
                        g.icon AS game_icon, g.name AS db_game_name, g.slug AS db_game_slug,
                        s.username AS seller_username, s.email AS seller_email, s.icon AS seller_icon,
                        c.username AS client_username, c.email AS client_email, c.icon AS client_icon, c.icon AS client_avatar
                 FROM selling_topup_purchases p
                 LEFT JOIN selling_topups st ON st.id = p.topup_id
                 LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                 LEFT JOIN sellers s ON s.id = p.seller_id
                 LEFT JOIN clients c ON c.id = p.client_id
                 WHERE p.id = ?
                 LIMIT 1",
                $id
            );
        } catch (Throwable $e) { $purchase = null; }
        if (empty($purchase)) { redirect_url('admin-area/top-up-orders'); return; }
        $checkoutData = [];
        $raw = (string)($purchase['checkout_data'] ?? '');
        if ($raw !== '') { $decoded = json_decode($raw, true); if (is_array($decoded)) $checkoutData = $decoded; }
        $chat = [];
        $chatPath = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_topup_purchase_' . $id) . '.json';
        if (is_file($chatPath)) {
            $chatData = json_decode((string)@file_get_contents($chatPath), true);
            $chat = is_array($chatData['messages'] ?? null) ? array_values(array_filter($chatData['messages'], static fn($m) => is_array($m) && empty($m['deleted']))) : [];
        }
        $meta = ['title' => 'Top Up Order #' . $id . ' | Admin Area', 'h1' => 'Top Up Order #' . $id];
        view_file('admin/pages/topups/order_view', compact('meta', 'purchase', 'checkoutData', 'chat'));
    });

    $router->get('seller-payout-requests', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $data = $db->run(
            "SELECT r.*,
                    s.username AS seller_username,
                    s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.email    AS seller_email,
                    s.icon     AS seller_icon,
                    s.rank     AS seller_rank
             FROM seller_payout_requests r
             LEFT JOIN sellers s ON s.id = r.seller_id
             ORDER BY (r.status = 'PENDING') DESC, r.id DESC"
        ) ?: [];
        view_file('admin/pages/sellers/seller_payout_requests', compact('data'));
    });

    // ── Seller payout-methods routes ──────────────────────────────────────
    $router->post('seller/:id/payout-methods/save', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $sellerId   = (int)$id;
        $method     = strtolower(trim($_POST['method'] ?? ''));
        $allowed    = ['bank_transfer', 'bank', 'crypto'];
        if (!in_array($method, $allowed, true)) { http_response_code(400); echo 'Invalid method'; exit; }

        $methodNorm = str_contains($method, 'crypto') ? 'crypto' : 'bank_transfer';
        $methodId   = (int)($_POST['method_id'] ?? 0);
        $makeDefault = (($_POST['make_default'] ?? '') === '1');

        if ($methodNorm === 'crypto') {
            $details = ['coin' => trim($_POST['coin'] ?? ''), 'network' => trim($_POST['network'] ?? ''), 'address' => trim($_POST['address'] ?? '')];
        } else {
            $details = ['beneficiary' => trim($_POST['beneficiary'] ?? ''), 'iban' => trim($_POST['iban'] ?? ''), 'swift' => trim($_POST['swift'] ?? ''), 'bank_name' => trim($_POST['bank_name'] ?? '')];
        }
        $detailsJson = json_encode($details, JSON_UNESCAPED_UNICODE);

        try {
            $db->run("CREATE TABLE IF NOT EXISTS seller_payout_methods (
                id INT AUTO_INCREMENT PRIMARY KEY, seller_id INT NOT NULL, method VARCHAR(32) NOT NULL,
                details TEXT NULL, is_default TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX(seller_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Throwable $e) {}

        if ($methodId > 0) {
            $existing = $db->row("SELECT id FROM seller_payout_methods WHERE id=? AND seller_id=? LIMIT 1", $methodId, $sellerId);
            if (empty($existing)) { http_response_code(404); echo 'Not found'; exit; }
            $db->run("UPDATE seller_payout_methods SET method=?, details=? WHERE id=? AND seller_id=?", $methodNorm, $detailsJson, $methodId, $sellerId);
            $savedId = $methodId;
        } else {
            $db->run("INSERT INTO seller_payout_methods (seller_id, method, details, is_default) VALUES (?,?,?,0)", $sellerId, $methodNorm, $detailsJson);
            $savedId = (int)$db->lastInsertId();
        }

        if ($makeDefault && $savedId > 0) {
            $db->run("UPDATE seller_payout_methods SET is_default=0 WHERE seller_id=?", $sellerId);
            $db->run("UPDATE seller_payout_methods SET is_default=1 WHERE id=? AND seller_id=?", $savedId, $sellerId);
        }

        header('Location: ' . ADMN_URL . '/seller/' . $sellerId . '/payout-methods?success=1&active=' . $methodNorm);
        exit;
    });

    $router->post('seller/:id/payout-methods/set-default', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $sellerId = (int)$id;
        $methodId = (int)($_POST['method_id'] ?? 0);
        if ($methodId <= 0) { http_response_code(400); echo 'Invalid'; exit; }
        $db->run("UPDATE seller_payout_methods SET is_default=0 WHERE seller_id=?", $sellerId);
        $db->run("UPDATE seller_payout_methods SET is_default=1 WHERE id=? AND seller_id=?", $methodId, $sellerId);
        header('Location: ' . ADMN_URL . '/seller/' . $sellerId . '/payout-methods?success=1');
        exit;
    });

    $router->post('seller/:id/payout-methods/delete', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $sellerId = (int)$id;
        $methodId = (int)($_POST['method_id'] ?? 0);
        if ($methodId <= 0) { http_response_code(400); echo 'Invalid'; exit; }
        $db->run("DELETE FROM seller_payout_methods WHERE id=? AND seller_id=?", $methodId, $sellerId);
        header('Location: ' . ADMN_URL . '/seller/' . $sellerId . '/payout-methods?success=1');
        exit;
    });
    // =============================================


    //single account route
    $router->get('account/:id', function ($id) {
        global $is_admin, $db;
        if ($is_admin) {
            $id = intval($id);
            $data = db_get_row('accounts', ['id' => $id]);
            if (empty($data)) {
                redirect_url('admin-area/account-packages');
                return;
            }
            $package = !empty($data['package_id'])
                ? db_get_row('account_packages', ['id' => (int)$data['package_id']], 1)
                : null;
            $client = !empty($data['client_id'])
                ? db_get_row('clients', ['id' => (int)$data['client_id']], 1)
                : null;
            $admin = defined('ADMIN_ID') && (int)ADMIN_ID > 0
                ? db_get_row('admins', ['id' => (int)ADMIN_ID], 1)
                : [];
            $chat_messages = [];
            $chatPath = SYS_PATH . '/public/uploads/private/chat/accounts_' . sha1('account_' . $id) . '.json';
            if (is_file($chatPath)) {
                $chatData = json_decode((string)@file_get_contents($chatPath), true);
                $chat_messages = is_array($chatData['messages'] ?? null) ? $chatData['messages'] : [];
            }
            view_file('admin/pages/packages/account', compact('data', 'package', 'client', 'admin', 'chat_messages'));
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    // articles
    $router->get('articles', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'samsayahix@gmail.com' 
         
            ) {
                redirect_url('admin-area/orders');
            }
            $data = db_get_rows('articles', [], 1);
            view_file('admin/pages/articles/list', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('article/add', function () {
        global $is_admin;
        if ($is_admin) {
            view_file('admin/pages/articles/add', ['games' => lb_article_game_options()]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('article/:id', function ($id) {
        global $is_admin;
        if ($is_admin) {
            $id = intval($id);
            $data = db_get_row('articles', ['id' => $id]);
            if (empty($data)) {
                redirect_url('admin-area/articles');
            }
            view_file('admin/pages/articles/edit', ['data' => $data, 'games' => lb_article_game_options()]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/loyalty', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' 
            ) {
                redirect_url('admin-area/orders');
            }
            $data = db_get_rows('loyalty_ranks', [], 1);

            if (!empty($data)) {
                foreach ($data as $key => $rank) {
                    $count = db_get_row_count('clients', ['loyalty_rank_id' => $rank['id']]);
                    $data[$key]['clients'] = $count > 0 ? $count : 0;
                }
            }

            view_file('admin/pages/loyalty/list', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/loyalty/add', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' 
            ) {
                redirect_url('admin-area/orders');
            }
            view_file('admin/pages/loyalty/add');
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/loyalty/:id', function ($id) {
        global $is_admin;
        if ($is_admin) {
            $id = intval($id);
            $data = db_get_row('loyalty_ranks', ['id' => $id]);

            if (empty($data)) {
                redirect_url('admin-area/loyalty');
            }

            view_file('admin/pages/loyalty/view', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });


    $router->get('/seller-ranks', function () {
        global $is_admin, $db;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de'
            ) {
                redirect_url('admin-area/orders');
            }

            $data = db_get_rows('seller_ranks', ['order' => 'min_sales,ASC'], 1);

            if (!empty($data)) {
                foreach ($data as $key => $rank) {
                    $count = db_get_row_count('sellers', ['seller_rank_id' => $rank['id']]);
                    $data[$key]['sellers'] = $count > 0 ? $count : 0;
                    $data[$key]['status_badge'] = (int) ($rank['status'] ?? 1) === 1 ? 'Active' : 'Inactive';
                }
            }

            view_file('admin/pages/seller-ranks/list', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/prizes', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de'
            ) {
                redirect_url('admin-area/orders');
            }
            $data = db_get_rows('prizes', [], 1);
            view_file('admin/pages/prizes/list', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/prizes/add', function () {
        global $is_admin;
        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' 
            ) {
                redirect_url('admin-area/orders');
            }
            view_file('admin/pages/prizes/add');
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/prizes/:id', function ($id) {
        global $is_admin;
        if ($is_admin) {
            $id = intval($id);
            $data = db_get_row('prizes', ['id' => $id]);

            if (empty($data)) {
                redirect_url('admin-area/prizes');
            }

            view_file('admin/pages/prizes/view', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/prizes/:id/delete', function ($id) {
        global $is_admin;
        if ($is_admin) {
            $id = intval($id);
            db_delete_rows('prizes', ['id' => $id]);
            redirect_url('admin-area/prizes');
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/prizes/redeemed', function () {
        global $is_admin;
        global $db;

        if ($is_admin) {
            if (
                !lb_is_seo_article_admin() &&
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de'
            ) {
                redirect_url('admin-area/orders');
            }
            $query = 'SELECT pr.*, 
                        c.username AS client_name, 
                        p.name AS prize_name 
                        FROM redeemed_prizes pr 
                        LEFT JOIN prizes p ON pr.prize_id = p.id 
                        LEFT JOIN clients c ON pr.client_id = c.id 
                        ORDER BY pr.created_at DESC';

            $data = $db->run($query);

            view_file('admin/pages/prizes/redeemed', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/coins-history', function () {
        global $is_admin;
        global $db;

        if ($is_admin) {
            $query = 'SELECT ch.*, c.username AS client_name
                        FROM coins_history ch
                        LEFT JOIN clients c ON ch.client_id = c.id
                        ORDER BY ch.created_at DESC';

            $data = $db->run($query);

            view_file('admin/pages/coins-history', ['data' => $data]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });

    $router->get('/admin-logs', function () {
        global $db;
        global $is_admin;
        if ($is_admin) {
            if (
                ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
                ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
                ADMIN_DATA['email'] !== 'samsayahix@gmail.com' &&
                ADMIN_DATA['email'] !== 'duck_sauce@live.de'
            ) {
                redirect_url('admin-area/orders');
            }

            // $query = 'SELECT al.*, a.username AS admin_name
            //             FROM admin_logs al
            //             LEFT JOIN admins a ON al.admin_id = a.id
            //             ORDER BY al.created_at DESC';

            // $data = $db->run($query);

            view_file('admin/pages/admin-logs', ['data' => []]);
        } else {
            redirect_url('admin-area/auth/login');
        }
    });
    // Server-side DataTables endpoint for Admin Logs
    $router->get('admin-logs/data', function () {
        global $is_admin, $db;

        if (!$is_admin) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        if (
            ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
            ADMIN_DATA['email'] !== 'justsromail@freenet.de' &&
            ADMIN_DATA['email'] !== 'samsayahix@gmail.com' &&
            ADMIN_DATA['email'] !== 'duck_sauce@live.de'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 8;
        $search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
        $orderColIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 4;
        $orderDir = isset($_GET['order'][0]['dir']) ? strtoupper($_GET['order'][0]['dir']) : 'DESC';
        $orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';
        $start = max(0, $start);
        $length = max(1, min(100, $length));

        $orderColumns = [
            0 => 'al.admin_id',
            1 => 'admin_name',
            2 => 'al.action',
            3 => 'al.id', // Changes column (not orderable, fallback)
            4 => 'al.created_at',
        ];
        $orderBy = $orderColumns[$orderColIndex] ?? 'al.created_at';

        $where = '';
        $conditions = [];

        if ($search !== '') {
            $like = '%' . esc($search) . '%';
            $conditions[] = "(CAST(al.admin_id AS CHAR) LIKE '" . $like . "' OR a.username LIKE '" . $like . "' OR al.action LIKE '" . $like . "')";
        }

        // Action-type filter from pill buttons (edit / delete / complete / create)
        $actionFilter = isset($_GET['action_filter']) ? trim($_GET['action_filter']) : '';
        $actionFilterMap = [
            'edit'     => "(al.action LIKE '%edit%' OR al.action LIKE '%updated%')",
            'delete'   => "(al.action LIKE '%delet%' OR al.action LIKE '%remov%')",
            'complete' => "(al.action LIKE '%complet%')",
            'create'   => "(al.action LIKE '%creat%' OR al.action LIKE '%add%')",
        ];
        if ($actionFilter !== '' && isset($actionFilterMap[$actionFilter])) {
            $conditions[] = $actionFilterMap[$actionFilter];
        }

        // Admin filter
        $adminFilter = isset($_GET['admin_filter']) ? (int)$_GET['admin_filter'] : 0;
        if ($adminFilter > 0) {
            $conditions[] = "al.admin_id = " . $adminFilter;
        }

        if (!empty($conditions)) {
            $where = ' WHERE ' . implode(' AND ', $conditions);
        }

        $totalCount = db_get_row_count('admin_logs');
        $sqlCount = "SELECT COUNT(*) AS cnt FROM admin_logs al LEFT JOIN admins a ON al.admin_id = a.id" . $where;
        $countRow = $db->run($sqlCount);
        $filteredCount = isset($countRow[0]['cnt']) ? (int) $countRow[0]['cnt'] : 0;

        $sqlData = "SELECT al.*, a.username AS admin_name, a.icon AS admin_icon,
                        (SELECT COUNT(*) FROM admin_log_changes c WHERE c.admin_log_id = al.id) AS changes_count
                     FROM admin_logs al
                     LEFT JOIN admins a ON al.admin_id = a.id" . $where . "
                     ORDER BY $orderBy $orderDir
                     LIMIT $length OFFSET $start";
        $rows = $db->run($sqlData) ?: [];

        // Resolve IDs in log action text to readable names without changing stored logs.
        // Examples: "booster #737" -> "booster Paradox (#737)".
        $adminLogNameCache = [
            'admin'   => [],
            'booster' => [],
            'client'  => [],
            'seller'  => [],
        ];
        $resolveAdminLogPersonName = function (string $type, int $id) use (&$adminLogNameCache, $db): string {
            if ($id <= 0) {
                return '#' . $id;
            }
            if (isset($adminLogNameCache[$type][$id])) {
                return $adminLogNameCache[$type][$id];
            }

            $table = null;
            if ($type === 'admin')   $table = 'admins';
            if ($type === 'booster') $table = 'boosters';
            if ($type === 'client')  $table = 'clients';
            if ($type === 'seller')  $table = 'sellers';

            if (!$table) {
                return '#' . $id;
            }

            try {
                $row = $db->row("SELECT username, email FROM {$table} WHERE id = ? LIMIT 1", $id);
            } catch (Throwable $e) {
                $row = [];
            }

            $name = trim((string)($row['username'] ?? ''));
            if ($name === '') {
                $name = trim((string)($row['email'] ?? ''));
            }

            $adminLogNameCache[$type][$id] = $name !== '' ? ($name . ' (#' . $id . ')') : ('#' . $id);
            return $adminLogNameCache[$type][$id];
        };

        $resolveAdminLogActionNames = function (string $action) use ($resolveAdminLogPersonName): string {
            // Important: "booster account #123" means a booster, not a shop account.
            // Therefore handle "<person> account #id" before the generic "account #id" linkifier.
            $action = preg_replace_callback('/\b(admin|booster|egirl|e-girl|gaminggirl|gg-girl|client|seller)\s+account\s+#?(\d+)\b/i', function ($m) use ($resolveAdminLogPersonName) {
                $labelType = $m[1];
                $type = strtolower($labelType);
                if (in_array($type, ['egirl', 'e-girl', 'gaminggirl', 'gg-girl'], true)) {
                    $type = 'booster';
                }
                return $labelType . ' account ' . $resolveAdminLogPersonName($type, (int)$m[2]);
            }, $action);

            $patterns = [
                'admin'   => '/\b(admin)\s+#(\d+)\b/i',
                'booster' => '/\b(booster|egirl|e-girl|gaminggirl|gg-girl)\s+#(\d+)\b/i',
                'client'  => '/\b(client)\s+#(\d+)\b/i',
                'seller'  => '/\b(seller)\s+#(\d+)\b/i',
            ];

            foreach ($patterns as $type => $regex) {
                $action = preg_replace_callback($regex, function ($m) use ($type, $resolveAdminLogPersonName) {
                    return $m[1] . ' ' . $resolveAdminLogPersonName($type, (int)$m[2]);
                }, $action);
            }

            // Also handle common variants like "seller_id 12" or "client_id: 12".
            $action = preg_replace_callback('/\b(admin|booster|client|seller)_id\s*[:=]?\s*(\d+)\b/i', function ($m) use ($resolveAdminLogPersonName) {
                $type = strtolower($m[1]);
                return $m[1] . '_id: ' . $resolveAdminLogPersonName($type, (int)$m[2]);
            }, $action);

            return $action;
        };

        $adminLogBuildLink = function (string $url, string $label): string {
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" class="text-primary fw-600" style="text-decoration:none;" title="Open" onclick="event.stopPropagation();">' . htmlspecialchars($label, ENT_QUOTES) . '</a>';
        };
        $adminLogPersonUrl = function (string $type, int $id): ?string {
            if ($id <= 0) return null;
            if ($type === 'booster') return ADMN_URL . '/booster/' . $id;
            if ($type === 'client')  return ADMN_URL . '/client/' . $id;
            if ($type === 'seller')  return ADMN_URL . '/seller/' . $id;
            return null; // No admin profile route exists here.
        };
        $adminLogEntityUrl = function (string $type, int $id): ?string {
            if ($id <= 0) return null;
            $type = strtolower(str_replace(['_', '-'], ' ', $type));
            $type = preg_replace('/\s+/', ' ', trim($type));
            if ($type === 'order')       return ADMN_URL . '/order/' . $id;
            if ($type === 'account')     return ADMN_URL . '/account/' . $id;
            if ($type === 'item order')  return ADMN_URL . '/item-order/' . $id;
            if ($type === 'egirl order') return ADMN_URL . '/egirl/order/' . $id;
            return null;
        };
        $resolveAdminLogActionHtml = function (string $action) use ($resolveAdminLogPersonName, $adminLogBuildLink, $adminLogPersonUrl, $adminLogEntityUrl): string {
            // Order matters: "booster account #123" must be treated as a booster.
            // Plain "account #123" remains a shop/account route.
            $regex = '/\b(?:(admin|booster|egirl|e-girl|gaminggirl|gg-girl|client|seller)\s+account\s+#?(\d+)|(admin|booster|egirl|e-girl|gaminggirl|gg-girl|client|seller)\s+#(\d+)|(admin|booster|client|seller)_id\s*[:=]?\s*(\d+)|(item[\s_-]?order|egirl[\s_-]?order|order|account)\s+#?(\d+))\b/i';
            $html = '';
            $offset = 0;

            if (!preg_match_all($regex, $action, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                return htmlspecialchars($action, ENT_QUOTES);
            }

            foreach ($matches as $m) {
                $matchText = $m[0][0];
                $pos = $m[0][1];
                $html .= htmlspecialchars(substr($action, $offset, $pos - $offset), ENT_QUOTES);

                if (!empty($m[1][0]) && !empty($m[2][0])) {
                    $labelType = $m[1][0];
                    $type = strtolower($labelType);
                    if (in_array($type, ['egirl', 'e-girl', 'gaminggirl', 'gg-girl'], true)) $type = 'booster';
                    $id = (int)$m[2][0];
                    $label = $labelType . ' account ' . $resolveAdminLogPersonName($type, $id);
                    $url = $adminLogPersonUrl($type, $id);
                    $html .= $url ? $adminLogBuildLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
                } elseif (!empty($m[3][0]) && !empty($m[4][0])) {
                    $labelType = $m[3][0];
                    $type = strtolower($labelType);
                    if (in_array($type, ['egirl', 'e-girl', 'gaminggirl', 'gg-girl'], true)) $type = 'booster';
                    $id = (int)$m[4][0];
                    $label = $labelType . ' ' . $resolveAdminLogPersonName($type, $id);
                    $url = $adminLogPersonUrl($type, $id);
                    $html .= $url ? $adminLogBuildLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
                } elseif (!empty($m[5][0]) && !empty($m[6][0])) {
                    $type = strtolower($m[5][0]);
                    $id = (int)$m[6][0];
                    $label = $m[5][0] . '_id: ' . $resolveAdminLogPersonName($type, $id);
                    $url = $adminLogPersonUrl($type, $id);
                    $html .= $url ? $adminLogBuildLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
                } elseif (!empty($m[7][0]) && !empty($m[8][0])) {
                    $entityType = $m[7][0];
                    $id = (int)$m[8][0];
                    $label = $entityType . ' #' . $id;
                    $url = $adminLogEntityUrl($entityType, $id);
                    $html .= $url ? $adminLogBuildLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
                } else {
                    $html .= htmlspecialchars($matchText, ENT_QUOTES);
                }

                $offset = $pos + strlen($matchText);
            }

            $html .= htmlspecialchars(substr($action, $offset), ENT_QUOTES);
            return $html;
        };

        $data = [];
        foreach ($rows as $r) {
            $logId        = (int) ($r['id'] ?? 0);
            $adminId      = (int) ($r['admin_id'] ?? 0);
            $adminName    = esc($r['admin_name'] ?? '');
            $adminIcon    = (string) ($r['admin_icon'] ?? '');
            $actionText   = $resolveAdminLogActionNames((string) ($r['action'] ?? ''));
            $actionHtml   = $resolveAdminLogActionHtml((string) ($r['action'] ?? ''));
            $createdAt    = $r['created_at'] ?? '';
            $createdHtml  = util_format_date_display($createdAt);
            $changesCount = (int) ($r['changes_count'] ?? 0);

            $adminIdHtml = '#' . $adminId;
            $changesBtn  = $changesCount > 0
                ? '<button type="button" class="btn btn-sm btn-soft-primary js-view-changes" data-log-id="' . $logId . '"><i class="fa-duotone fa-clock-rotate-left me-1"></i> View (' . $changesCount . ')</button>'
                : '<span class="text-muted">—</span>';

            $data[] = [
                'admin_id_html'   => $adminIdHtml,
                'admin_name_html' => $adminName,
                'admin_icon'      => $adminIcon,
                'log_action'      => $actionText,   // plain text for sorting/filtering/category detection
                'action_html'     => $actionHtml,   // safe HTML links for clickable orders/accounts/users
                'changes_html'    => $changesBtn,
                'created_at'      => $createdAt,
                'created_html'    => $createdHtml,
                'log_id'          => $logId,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => (int) $totalCount,
            'recordsFiltered' => (int) $filteredCount,
            'data' => $data,
        ]);
        exit;
    });

    // Endpoint to fetch changes for a specific admin log (JSON with HTML body)
    $router->get('admin-logs/:id/changes', function ($id) {
        global $is_admin, $db;

        if (!$is_admin) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        if (
            ADMIN_DATA['email'] !== 'r.machmueller@gmx.de' &&
            ADMIN_DATA['email'] !== 'samsayahix@gmail.com' &&
            ADMIN_DATA['email'] !== 'duck_sauce@live.de'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $logId = intval($id);
        $logRow = $db->row('SELECT al.*, a.username AS admin_name FROM admin_logs al LEFT JOIN admins a ON al.admin_id = a.id WHERE al.id = ? LIMIT 1', $logId);
        if (empty($logRow)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'not-found']);
            return;
        }

        $changes = $db->run('SELECT * FROM admin_log_changes WHERE admin_log_id = ? ORDER BY id ASC LIMIT 200', $logId) ?: [];

        // Formatting helpers (mirror of view logic, condensed)
        $tierMap = [0 => 'Unranked', 1 => 'Iron', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum', 6 => 'Emerald', 7 => 'Diamond', 8 => 'Master', 9 => 'Grandmaster', 10 => 'Challenger'];
        $divMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        $formMap = [];
        $personNameCache = [
            'admin'   => [],
            'booster' => [],
            'client'  => [],
            'seller'  => [],
        ];
        $resolvePersonName = function (string $type, int $id) use (&$personNameCache, $db): string {
            if ($id <= 0) return '#' . $id;
            if (isset($personNameCache[$type][$id])) return $personNameCache[$type][$id];

            $table = null;
            if ($type === 'admin')   $table = 'admins';
            if ($type === 'booster') $table = 'boosters';
            if ($type === 'client')  $table = 'clients';
            if ($type === 'seller')  $table = 'sellers';
            if (!$table) return '#' . $id;

            try {
                $row = $db->row("SELECT username, email FROM {$table} WHERE id = ? LIMIT 1", $id);
            } catch (Throwable $e) {
                $row = [];
            }

            $name = trim((string)($row['username'] ?? ''));
            if ($name === '') $name = trim((string)($row['email'] ?? ''));

            $personNameCache[$type][$id] = $name !== '' ? ($name . ' (#' . $id . ')') : ('#' . $id);
            return $personNameCache[$type][$id];
        };
        $resolveActionNames = function (string $action) use ($resolvePersonName): string {
            $patterns = [
                'admin'   => '/\b(admin)\s+#(\d+)\b/i',
                'booster' => '/\b(booster|egirl|e-girl|gaminggirl|gg-girl)\s+#(\d+)\b/i',
                'client'  => '/\b(client)\s+#(\d+)\b/i',
                'seller'  => '/\b(seller)\s+#(\d+)\b/i',
            ];
            foreach ($patterns as $type => $regex) {
                $action = preg_replace_callback($regex, function ($m) use ($type, $resolvePersonName) {
                    return $m[1] . ' ' . $resolvePersonName($type, (int)$m[2]);
                }, $action);
            }
            $action = preg_replace_callback('/\b(admin|booster|client|seller)_id\s*[:=]?\s*(\d+)\b/i', function ($m) use ($resolvePersonName) {
                $type = strtolower($m[1]);
                return $m[1] . '_id: ' . $resolvePersonName($type, (int)$m[2]);
            }, $action);
            return $action;
        };
        $buildLogLink = function (string $url, string $label): string {
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" class="text-primary fw-600" style="text-decoration:none;" title="Open">' . htmlspecialchars($label, ENT_QUOTES) . '</a>';
        };
        $personUrl = function (string $type, int $id): ?string {
            if ($id <= 0) return null;
            if ($type === 'booster') return ADMN_URL . '/booster/' . $id;
            if ($type === 'client')  return ADMN_URL . '/client/' . $id;
            if ($type === 'seller')  return ADMN_URL . '/seller/' . $id;
            return null;
        };
        $entityUrl = function (string $type, int $id): ?string {
            if ($id <= 0) return null;
            $type = strtolower(str_replace(['_', '-'], ' ', $type));
            $type = preg_replace('/\s+/', ' ', trim($type));
            if ($type === 'order')       return ADMN_URL . '/order/' . $id;
            if ($type === 'account')     return ADMN_URL . '/account/' . $id;
            if ($type === 'item order')  return ADMN_URL . '/item-order/' . $id;
            if ($type === 'egirl order') return ADMN_URL . '/egirl/order/' . $id;
            return null;
        };
        $resolveActionHtml = function (string $action) use ($resolvePersonName, $buildLogLink, $personUrl, $entityUrl): string {
            $regex = '/\b(?:(admin|booster|egirl|e-girl|gaminggirl|gg-girl|client|seller)\s+#(\d+)|(admin|booster|client|seller)_id\s*[:=]?\s*(\d+)|(item[\s_-]?order|egirl[\s_-]?order|order|account)\s+#?(\d+))\b/i';
            $html = '';
            $offset = 0;
            if (!preg_match_all($regex, $action, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) return htmlspecialchars($action, ENT_QUOTES);
            foreach ($matches as $m) {
                $matchText = $m[0][0];
                $pos = $m[0][1];
                $html .= htmlspecialchars(substr($action, $offset, $pos - $offset), ENT_QUOTES);
                if (!empty($m[1][0]) && !empty($m[2][0])) {
                    $labelType = $m[1][0];
                    $type = strtolower($labelType);
                    if (in_array($type, ['egirl', 'e-girl', 'gaminggirl', 'gg-girl'], true)) $type = 'booster';
                    $id = (int)$m[2][0];
                    $label = $labelType . ' ' . $resolvePersonName($type, $id);
                    $url = $personUrl($type, $id);
                    $html .= $url ? $buildLogLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
                } elseif (!empty($m[3][0]) && !empty($m[4][0])) {
                    $type = strtolower($m[3][0]);
                    $id = (int)$m[4][0];
                    $label = $m[3][0] . '_id: ' . $resolvePersonName($type, $id);
                    $url = $personUrl($type, $id);
                    $html .= $url ? $buildLogLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
                } elseif (!empty($m[5][0]) && !empty($m[6][0])) {
                    $entityType = $m[5][0];
                    $id = (int)$m[6][0];
                    $label = $entityType . ' #' . $id;
                    $url = $entityUrl($entityType, $id);
                    $html .= $url ? $buildLogLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
                } else {
                    $html .= htmlspecialchars($matchText, ENT_QUOTES);
                }
                $offset = $pos + strlen($matchText);
            }
            $html .= htmlspecialchars(substr($action, $offset), ENT_QUOTES);
            return $html;
        };
        $forms = db_get_rows('boost_forms', ['select' => 'id,name', 'order' => 'id,ASC', 'limit' => 500], true);
        if (!empty($forms)) {
            foreach ($forms as $f) {
                $fid = (int) ($f['id'] ?? 0);
                if ($fid > 0) {
                    $formMap[$fid] = (string) ($f['name'] ?? ('Form #' . $fid));
                }
            }
        }

        $fmtVal = function ($field, $value) use ($tierMap, $divMap, $formMap, $resolvePersonName, $buildLogLink, $personUrl, $entityUrl) {
            if ($value === null)
                return '<span class="text-muted">NULL</span>';
            if ((string) $value === '[REDACTED]')
                return '<span class="text-muted">[REDACTED]</span>';
            if ($field === 'form_id') {
                $fid = (int) $value;
                $name = $formMap[$fid] ?? null;
                return $name ? ('#' . $fid . ' ' . htmlspecialchars($name, ENT_QUOTES)) : '#' . $fid;
            }
            if (in_array($field, ['admin_id', 'booster_id', 'client_id', 'seller_id'], true)) {
                $type = substr($field, 0, -3);
                $label = $resolvePersonName($type, (int)$value);
                $url = $personUrl($type, (int)$value);
                return $url ? $buildLogLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
            }
            if (in_array($field, ['order_id', 'account_id', 'item_order_id', 'egirl_order_id'], true)) {
                $entityType = substr($field, 0, -3);
                $label = str_replace('_', ' ', $entityType) . ' #' . (int)$value;
                $url = $entityUrl($entityType, (int)$value);
                return $url ? $buildLogLink($url, $label) : htmlspecialchars($label, ENT_QUOTES);
            }
            if (strpos($field, 'is_') === 0) {
                $b = ((string) $value === '1' || $value === 1 || $value === true);
                return $b ? 'Yes' : 'No';
            }
            if (in_array($field, ['price', 'price_eur'], true)) {
                $eur = ((int) $value) / 100;
                return number_format($eur, 2, ',', '.') . '€';
            }
            if ($field === 'booster_cut') {
                return htmlspecialchars((string) $value, ENT_QUOTES) . '%';
            }
            if (in_array($field, ['start_tier', 'end_tier'], true)) {
                $k = (int) $value;
                return htmlspecialchars($tierMap[$k] ?? (string) $value, ENT_QUOTES);
            }
            if (in_array($field, ['start_division', 'end_division'], true)) {
                $k = (int) $value;
                return htmlspecialchars($divMap[$k] ?? (string) $value, ENT_QUOTES);
            }
            if (in_array($field, ['start_lp', 'end_lp', 'lp_gain'], true) && is_numeric($value)) {
                return htmlspecialchars((string) $value, ENT_QUOTES) . ' LP';
            }
            if ($field === 'start_rr' && is_numeric($value)) {
                return htmlspecialchars((string) $value, ENT_QUOTES) . ' RR';
            }
            if ($field === 'server') {
                return htmlspecialchars(strtoupper((string) $value), ENT_QUOTES);
            }
            if ($field === 'queue_type') {
                $map = ['soloq' => 'Ranked Solo/Duo', 'solo' => 'Ranked Solo/Duo', 'solo/duo' => 'Ranked Solo/Duo', 'solo_duo' => 'Ranked Solo/Duo', 'ranked_solo_duo' => 'Ranked Solo/Duo', 'duo' => 'Ranked Solo/Duo', 'flex' => 'Ranked Flex Queue', 'flexq' => 'Ranked Flex Queue', 'flex_queue' => 'Ranked Flex Queue', 'ranked_flex' => 'Ranked Flex Queue', 'normal' => 'Normal', 'aram' => 'ARAM'];
                $raw = strtolower(trim((string) $value));
                if (isset($map[$raw]))
                    return htmlspecialchars($map[$raw], ENT_QUOTES);
                $pretty = str_replace(['_', '-'], ' ', $raw);
                $pretty = preg_replace('/\s+/', ' ', $pretty);
                return htmlspecialchars(ucwords($pretty), ENT_QUOTES);
            }
            if (in_array($field, ['roles', 'champions', 'agents'], true)) {
                $s = str_replace(',', ', ', trim((string) $value));
                return htmlspecialchars($s, ENT_QUOTES);
            }
            $s = (string) $value;
            if (function_exists('mb_strlen') && mb_strlen($s) > 140) {
                $s = mb_substr($s, 0, 140) . '…';
            } elseif (strlen($s) > 140) {
                $s = substr($s, 0, 140) . '…';
            }return htmlspecialchars($s, ENT_QUOTES);
        };

        // Build HTML body
        $body = '';
        foreach ($changes as $e) {
            $entityType = (string) ($e['entity_type'] ?? '');
            $createdAt = (string) ($e['created_at'] ?? '');
            $diff = json_decode($e['changes'] ?? '[]', true);
            if (!is_array($diff))
                $diff = [];

            $body .= '<div class="d-flex align-items-center justify-content-between mb-2">'
                . '<span class="lb-chlog-badge">' . htmlspecialchars($entityType, ENT_QUOTES) . '</span>'
                . '<span class="text-muted small">' . htmlspecialchars($createdAt, ENT_QUOTES) . '</span>'
                . '</div>';

            if (empty($diff)) {
                $body .= '<div class="text-muted mb-3">No field diff available.</div><hr class="my-3">';
                continue;
            }

            foreach ($diff as $field => $pair) {
                $old = $pair['old'] ?? null;
                $nw = $pair['new'] ?? null;
                $body .= '<div class="lb-chlog-row mb-2">'
                    . '<div class="lb-chlog-field">' . htmlspecialchars((string) $field, ENT_QUOTES) . '</div>'
                    . '<div class="lb-chlog-val"><div class="text-muted small">old</div><div>' . $fmtVal((string) $field, $old) . '</div></div>'
                    . '<div class="lb-chlog-arrow">→</div>'
                    . '<div class="lb-chlog-val"><div class="text-muted small">new</div><div>' . $fmtVal((string) $field, $nw) . '</div></div>'
                    . '</div>';
            }

            $body .= '<hr class="my-3">';
        }

        header('Content-Type: application/json');
        echo json_encode([
            'log_id' => (int) $logRow['id'],
            'admin_id' => (int) $logRow['admin_id'],
            'admin_name' => (string) $logRow['admin_name'],
            'action' => $resolveActionHtml((string) $logRow['action']),
            'created_at' => (string) $logRow['created_at'],
            'body_html' => $body,
        ]);
        exit;
    });

    // =============================================
    // ADMIN E-GIRL ROUTES
    // =============================================

    $router->get('egirls', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $egirls = $db->run(
            "SELECT b.id, b.username, b.email, b.icon, b.is_banned, b.verified,
                    ep.games, ep.review_count, ep.review_avg, ep.total_sessions,
                    COALESCE(eb.balance, 0) AS balance
             FROM boosters b
             LEFT JOIN egirl_profiles ep ON ep.egirl_id = b.id
             LEFT JOIN egirl_balance eb ON eb.egirl_id = b.id
             WHERE b.is_egirl = 1
             ORDER BY COALESCE(eb.balance, 0) DESC, b.id DESC"
        );
        $meta = ['title' => 'E-Girls - Admin Area | LoLBoost', 'h1' => 'E-Girls'];
        view_file('admin/pages/egirls/list', compact('egirls', 'meta'));
    });

    $router->get('egirl/add', function () {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $meta = ['title' => 'Add E-Girl - Admin Area | LoLBoost', 'h1' => 'Add E-Girl'];
        view_file('admin/pages/egirls/add', compact('meta'));
    });

    $router->get('egirl/orders', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $orders = $db->run(
            "SELECT eo.*, c.username AS client_username, b.username AS egirl_username
             FROM egirl_orders eo
             LEFT JOIN clients c ON c.id = eo.client_id
             LEFT JOIN boosters b ON b.id = eo.egirl_id
             ORDER BY eo.created_at DESC LIMIT 200"
        );
        $meta = ['title' => 'E-Girl Bookings - Admin Area | LoLBoost', 'h1' => 'E-Girl Bookings'];
        view_file('admin/pages/egirls/orders', compact('orders', 'meta'));
    });


    $router->get('egirl/order/:id', function ($order_id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $order_id = (int)$order_id;
        $order = $db->row(
            "SELECT eo.*,
                    c.username AS client_username, c.icon AS client_icon, c.discord AS client_discord,
                    b.username AS egirl_username, b.icon AS egirl_icon,
                    es.title AS service_title, es.type AS service_type,
                    es.game, es.unit_value, es.unit_type, es.includes_voice
             FROM egirl_orders eo
             LEFT JOIN clients c ON c.id = eo.client_id
             LEFT JOIN boosters b ON b.id = eo.egirl_id
             LEFT JOIN egirl_services es ON es.id = eo.service_id
             WHERE eo.id = ? LIMIT 1",
            $order_id
        );
        if (!$order) { redirect_url('admin-area/egirl/orders'); return; }

        $chat_raw  = chat_load_messages('eg_' . $order_id);
        $chat_prep = chat_prepare_for_viewer($chat_raw, 'admin');
        $messages  = $chat_prep['messages'] ?? [];

        $meta = [
            'title'       => 'Booking #' . $order_id . ' | Admin Area | LoLBoost',
            'h1'          => 'Booking #' . $order_id,
            'description' => 'View E-Girl booking.',
        ];
        view_file('admin/pages/egirls/order-view', compact('meta', 'order', 'messages'));
    });

    $router->get('egirl/payout-requests', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $requests = $db->run(
            "SELECT ep.*, b.username AS egirl_username
             FROM egirl_payout_requests ep
             LEFT JOIN boosters b ON b.id = ep.egirl_id
             ORDER BY ep.id DESC"
        );
        $meta = ['title' => 'E-Girl Payouts - Admin Area | LoLBoost', 'h1' => 'E-Girl Payout Requests'];
        view_file('admin/pages/egirls/payout-requests', compact('requests', 'meta'));
    });

    $router->get('egirl/reviews', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $reviews = $db->run(
            "SELECT er.*, c.username AS client_username, b.username AS egirl_username
             FROM egirl_reviews er
             LEFT JOIN clients c ON c.id = er.client_id
             LEFT JOIN boosters b ON b.id = er.egirl_id
             ORDER BY er.approved ASC, er.created_at DESC"
        );
        $meta = ['title' => 'E-Girl Reviews - Admin Area | LoLBoost', 'h1' => 'E-Girl Reviews'];
        view_file('admin/pages/egirls/reviews', compact('reviews', 'meta'));
    });

    $router->get('egirl/applications', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $data = $db->run(
            "SELECT b.id AS booster_id, b.username, b.email, b.created_at,
                    pd.fullname, pd.country, pd.address
             FROM boosters b
             LEFT JOIN booster_personal_details pd ON pd.booster_id = b.id
             WHERE b.is_egirl = 1 AND b.verified = 0 AND (b.is_banned = 1 OR b.is_banned IS NULL)
             ORDER BY b.created_at DESC"
        );
        $meta = ['title' => 'eGirl Applications - Admin Area | LoLBoost', 'h1' => 'eGirl Applications', 'description' => 'View the eGirl Applications.'];
        view_file('admin/pages/egirls/applications', compact('data', 'meta'));
    });

    $router->get('egirl/:id/:slug?', function ($id, $page = 'overview') {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $id = (int)$id;

        // game_ranks holds the ranks of every game beyond lol/val/tft and is created lazily.
        if (function_exists('lb_egirl_profiles_ensure_game_ranks_column')) {
            lb_egirl_profiles_ensure_game_ranks_column();
        }

        $egirl = $db->row(
            "SELECT b.*,
                    b.id AS booster_id,
                    b.cover AS booster_cover,
                    ep.id AS egirl_profile_id,
                    ep.egirl_id AS profile_egirl_id,
                    ep.bio, ep.age, ep.languages, ep.country, ep.timezone, ep.games,
                    ep.lol_rank, ep.val_rank, ep.tft_rank, ep.game_ranks, ep.review_count, ep.review_avg,
                    ep.total_sessions, ep.is_online, ep.created_at AS egirl_profile_created_at,
                    ep.updated_at AS egirl_profile_updated_at,
                    COALESCE(eb.balance, 0) AS balance
             FROM boosters b
             LEFT JOIN egirl_profiles ep ON ep.egirl_id = b.id
             LEFT JOIN egirl_balance eb ON eb.egirl_id = b.id
             WHERE b.id = ? AND b.is_egirl = 1 LIMIT 1",
            $id
        );
        if (!$egirl) { redirect_url('admin-area/egirls'); return; }

        $baseUrl = ADMN_URL . '/egirl/' . $id;
        $meta    = ['h1' => $egirl['username']];

        // The tab badges must show the real totals on every tab. Deriving them from the
        // per-page result set made them jump around: Overview only loads 5 bookings, and
        // the Services tab loads none at all, so "Bookings" read 0 there and 1 on its own tab.
        $tabCounts = ['services' => 0, 'bookings' => 0, 'payments' => 0, 'reviews' => 0];
        $countTable = static function (string $sql) use ($db, $id): int {
            try {
                $row = $db->row($sql, $id);
                return (int)($row['cnt'] ?? 0);
            } catch (\Throwable $e) {
                return 0;
            }
        };
        $tabCounts['services'] = $countTable("SELECT COUNT(*) AS cnt FROM egirl_services WHERE egirl_id = ?");
        $tabCounts['bookings'] = $countTable("SELECT COUNT(*) AS cnt FROM egirl_orders WHERE egirl_id = ?");
        $tabCounts['payments'] = $countTable("SELECT COUNT(*) AS cnt FROM egirl_payments WHERE egirl_id = ?");
        $tabCounts['reviews']  = $countTable("SELECT COUNT(*) AS cnt FROM egirl_reviews WHERE egirl_id = ?");

        switch ($page) {
            case 'profile':
                $meta['title'] = $egirl['username'] . ' – Profile | Admin';
                view_file('admin/pages/egirls/view', compact('egirl', 'page', 'baseUrl', 'meta', 'tabCounts'));
                break;

            case 'services':
                $services = $db->run("SELECT * FROM egirl_services WHERE egirl_id = ? ORDER BY sort_order ASC", $id);
                $meta['title'] = $egirl['username'] . ' – Services | Admin';
                view_file('admin/pages/egirls/view', compact('egirl', 'page', 'baseUrl', 'services', 'meta', 'tabCounts'));
                break;

            case 'reviews':
                $reviews = $db->run(
                    "SELECT er.*, c.username AS client_username, c.icon AS client_icon
                     FROM egirl_reviews er
                     LEFT JOIN clients c ON c.id = er.client_id
                     WHERE er.egirl_id = ?
                     ORDER BY er.created_at DESC",
                    $id
                ) ?: [];
                $meta['title'] = $egirl['username'] . ' – Reviews | Admin';
                view_file('admin/pages/egirls/view', compact('egirl', 'page', 'baseUrl', 'reviews', 'meta', 'tabCounts'));
                break;

            case 'bookings':
                $orders = $db->run(
                    "SELECT eo.*, c.username AS client_username FROM egirl_orders eo
                     LEFT JOIN clients c ON c.id = eo.client_id
                     WHERE eo.egirl_id = ? ORDER BY eo.created_at DESC",
                    $id
                );
                $meta['title'] = $egirl['username'] . ' – Bookings | Admin';
                view_file('admin/pages/egirls/view', compact('egirl', 'page', 'baseUrl', 'orders', 'meta', 'tabCounts'));
                break;

            case 'payments':
                $payments = $db->run("SELECT * FROM egirl_payments WHERE egirl_id = ? ORDER BY id DESC", $id);
                $meta['title'] = $egirl['username'] . ' – Payments | Admin';
                view_file('admin/pages/egirls/view', compact('egirl', 'page', 'baseUrl', 'payments', 'meta', 'tabCounts'));
                break;

            case 'revenue':
                $meta['title'] = $egirl['username'] . ' – Revenue Share | Admin';
                view_file('admin/pages/egirls/view', compact('egirl', 'page', 'baseUrl', 'meta', 'tabCounts'));
                break;

            default: // overview
                $services = $db->run("SELECT * FROM egirl_services WHERE egirl_id = ? ORDER BY sort_order ASC", $id);
                $orders   = $db->run("SELECT eo.* FROM egirl_orders eo WHERE eo.egirl_id = ? ORDER BY eo.created_at DESC LIMIT 5", $id);
                $payments = $db->run("SELECT * FROM egirl_payments WHERE egirl_id = ? ORDER BY id DESC LIMIT 5", $id);
                $meta['title'] = $egirl['username'] . ' – Overview | Admin';
                $page = 'overview';
                view_file('admin/pages/egirls/view', compact('egirl', 'page', 'baseUrl', 'services', 'orders', 'payments', 'meta', 'tabCounts'));
                break;
        }
    });
    // =============================================
    // END ADMIN E-GIRL ROUTES
    // =============================================

    // =============================================
    // JOB APPLICATIONS (Unified: LoL/TFT/VAL Booster, GG-Girl, Seller)
    // =============================================
    $router->get('job-applications', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        // Tabelle existiert nach Migration (migration_job_applications.sql).
        // Fallback: leere Liste wenn Tabelle noch nicht angelegt wurde.
        $applications = [];
        try {
            $rows = $db->run("SELECT * FROM job_applications ORDER BY created_at DESC");
            $applications = is_array($rows) ? $rows : ($rows ? $rows->fetchAll() : []);
        } catch (\Throwable $e) {
            // Tabelle existiert noch nicht — bitte migration_job_applications.sql ausführen
        }

        $meta = [
            'title'       => 'Job Applications — Admin Area | LoLBoost',
            'h1'          => 'Job Applications',
            'description' => 'Review all incoming job applications.',
        ];
        view_file('admin/pages/applications/list', compact('meta', 'applications'));
    });
    // =============================================
    // END JOB APPLICATIONS
    // =============================================


    // =============================================
    // ONBOARDING APPLICATIONS (Unified: Booster, E-Girl, Seller)
    // =============================================
    $router->get('applications', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $sellerMigrations = [
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS is_banned TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS fullname VARCHAR(191) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS dob VARCHAR(32) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS address TEXT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS country VARCHAR(100) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS id_front VARCHAR(512) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS id_back VARCHAR(512) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS selfie VARCHAR(512) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS onboarding_status VARCHAR(16) NOT NULL DEFAULT 'pending'",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS discord VARCHAR(191) NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS application_note TEXT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS icon VARCHAR(512) NULL",
            // Written at onboarding submit from the one-time link's creator, but the column
            // is created lazily there — make sure it exists before we SELECT it below.
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS hired_by_admin_id INT UNSIGNED NULL DEFAULT NULL",
            "ALTER TABLE boosters ADD COLUMN IF NOT EXISTS hired_by_admin_id INT UNSIGNED NULL DEFAULT NULL",
        ];
        foreach ($sellerMigrations as $sql) {
            try { $db->run($sql); } catch (\Throwable $e) {}
        }

        $rows = [];

        try {
            $boosters = $db->run(
                "SELECT 'booster' AS type, b.id AS id, b.id AS user_id, b.username, b.email, b.created_at,
                        COALESCE(NULLIF(pd.fullname, ''), b.username) AS fullname,
                        pd.country, pd.address, b.discord_id AS discord,
                        'pending' AS status, '' AS id_front, '' AS id_back, '' AS selfie,
                        b.hired_by_admin_id, ha.username AS hired_by_username, ha.icon AS hired_by_icon
                 FROM booster_personal_details pd
                 INNER JOIN boosters b ON b.id = pd.booster_id
                 LEFT JOIN admins ha ON ha.id = b.hired_by_admin_id
                 WHERE b.is_egirl = 0
                   AND b.verified = 0
                   AND (COALESCE(pd.fullname, '') <> '' OR COALESCE(pd.country, '') <> '' OR COALESCE(pd.address, '') <> '')
                 ORDER BY b.created_at DESC"
            ) ?: [];
            $rows = array_merge($rows, is_array($boosters) ? $boosters : ($boosters ? $boosters->fetchAll() : []));
        } catch (\Throwable $e) {}

        try {
            $egirls = $db->run(
                "SELECT 'egirl' AS type, b.id AS id, b.id AS user_id, b.username, b.email, b.created_at,
                        COALESCE(NULLIF(pd.fullname, ''), b.username) AS fullname,
                        pd.country, pd.address, b.discord_id AS discord,
                        'pending' AS status, '' AS id_front, '' AS id_back, '' AS selfie,
                        b.hired_by_admin_id, ha.username AS hired_by_username, ha.icon AS hired_by_icon
                 FROM booster_personal_details pd
                 INNER JOIN boosters b ON b.id = pd.booster_id
                 LEFT JOIN admins ha ON ha.id = b.hired_by_admin_id
                 WHERE b.is_egirl = 1
                   AND b.verified = 0
                   AND (COALESCE(pd.fullname, '') <> '' OR COALESCE(pd.country, '') <> '' OR COALESCE(pd.address, '') <> '')
                 ORDER BY b.created_at DESC"
            ) ?: [];
            $rows = array_merge($rows, is_array($egirls) ? $egirls : ($egirls ? $egirls->fetchAll() : []));
        } catch (\Throwable $e) {}

        try {
            $sellers = $db->run(
                "SELECT 'seller' AS type, s.id, s.id AS user_id, s.username, s.email, s.created_at,
                        COALESCE(NULLIF(s.fullname, ''), s.username) AS fullname, s.country, s.address, s.discord,
                        CASE
                            WHEN LOWER(TRIM(COALESCE(s.onboarding_status, ''))) IN ('pending','completed','submitted','in_review') THEN 'pending'
                            ELSE LOWER(TRIM(COALESCE(s.onboarding_status, 'pending')))
                        END AS status,
                        s.id_front, s.id_back, s.selfie,
                        s.hired_by_admin_id, ha.username AS hired_by_username, ha.icon AS hired_by_icon
                 FROM sellers s
                 LEFT JOIN admins ha ON ha.id = s.hired_by_admin_id
                 WHERE (s.is_banned IS NULL OR s.is_banned = 0)
                   AND LOWER(TRIM(COALESCE(s.onboarding_status, 'pending'))) IN ('pending','completed','submitted','in_review')
                   AND (
                        COALESCE(s.fullname, '') <> ''
                        OR COALESCE(s.country, '') <> ''
                        OR COALESCE(s.address, '') <> ''
                        OR COALESCE(s.id_front, '') <> ''
                        OR COALESCE(s.id_back, '') <> ''
                        OR COALESCE(s.selfie, '') <> ''
                   )
                 ORDER BY s.id DESC"
            ) ?: [];
            $rows = array_merge($rows, is_array($sellers) ? $sellers : ($sellers ? $sellers->fetchAll() : []));
        } catch (\Throwable $e) {}

        usort($rows, function ($a, $b) {
            return strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? ''));
        });

        $meta = [
            'title' => 'Onboarding Applications — Admin Area | LoLBoost.gg',
        ];
        view_file('admin/pages/applications/onboarding', compact('meta', 'rows'));
    });
    // =============================================
    // END ONBOARDING APPLICATIONS
    // =============================================

    // ── Digital Goods (Admin) ─────────────────
    $router->get('digital-good-order/:id', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

        $id = (int)$id;
        $rows = $db->run(
            "SELECT dgp.*,
                    CASE WHEN dgp.status='UNPAID' AND inv.status='PAID' THEN 'PAID' ELSE dgp.status END AS status,
                    inv.status AS invoice_status, inv.currency,
                    dg.title AS item_title, dg.brand, dg.brand_icon, dg.images AS item_images,
                    dg.delivery_type, dg.delivery_instructions, dg.validity_days,
                    dgc.name AS category_name, dgc.icon AS category_icon,
                    s.username AS seller_username, s.email AS seller_email, s.icon AS seller_icon,
                    c.username AS client_username, c.email AS client_email, c.icon AS client_icon
             FROM digital_good_purchases dgp
             LEFT JOIN invoices inv ON inv.id = dgp.invoice_id
             LEFT JOIN digital_goods dg ON dg.id = COALESCE(NULLIF(dgp.item_id, 0), NULLIF(inv.order_id, 0))
             LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id
             LEFT JOIN sellers s ON s.id = dgp.seller_id
             LEFT JOIN clients c ON c.id = dgp.client_id
             WHERE dgp.id = ?
             LIMIT 1",
            $id
        ) ?: [];
        $purchase = $rows[0] ?? null;
        if (!$purchase) { redirect_url('admin-area/digital-good-orders'); return; }

        $chat = [];
        if (function_exists('dg_chat_path')) {
            $chatPath = dg_chat_path($id);
            if (is_file($chatPath)) {
                $chatData = json_decode((string)@file_get_contents($chatPath), true);
                $chat = is_array($chatData['messages'] ?? null) ? $chatData['messages'] : [];
                if (function_exists('dg_chat_normalize')) $chat = dg_chat_normalize($chat);
                $chat = array_values(array_filter($chat, static fn($message) => is_array($message) && empty($message['deleted'])));
            }
        }

        $images = json_decode((string)($purchase['item_images'] ?? '[]'), true);
        if (!is_array($images)) $images = [];
        $meta = ['title' => 'Digital Good Order #' . $id . ' | Admin Area'];
        view_file('admin/pages/digital-goods/order-view', compact('meta', 'purchase', 'chat', 'images'));
    });

    $router->get('digital-good-orders', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        try {
            $orders = $db->run(
                "SELECT dgp.*, dg.title AS item_title, dg.brand, dg.brand_icon, dg.images AS item_images,
                        s.username AS seller_username, s.icon AS seller_icon, c.username AS client_username, c.icon AS client_icon
                 FROM digital_good_purchases dgp
                 LEFT JOIN invoices inv ON inv.id = dgp.invoice_id
                 LEFT JOIN digital_goods dg ON dg.id = COALESCE(NULLIF(dgp.item_id, 0), NULLIF(inv.order_id, 0))
                 LEFT JOIN sellers s ON s.id = dgp.seller_id
                 LEFT JOIN clients c ON c.id = dgp.client_id
                 ORDER BY COALESCE(dgp.paid_at, dgp.created_at) DESC, dgp.id DESC"
            ) ?: [];
            $listings = $db->run(
                "SELECT dg.*, dgc.name AS category_name, s.username AS seller_username, s.icon AS seller_icon
                 FROM digital_goods dg
                 LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id
                 LEFT JOIN sellers s ON s.id = dg.seller_id
                 ORDER BY dg.created_at DESC, dg.id DESC"
            ) ?: [];
        } catch (Throwable $e) {
            $orders = [];
            $listings = [];
        }
        $marketplaceType = 'digital';
        $meta = ['title' => 'Digital Good Orders | Admin Area', 'h1' => 'Digital Good Orders'];
        view_file('admin/pages/marketplace/orders', compact('meta', 'orders', 'listings', 'marketplaceType'));
    });

    $router->get('digital-goods', function () {
        global $is_admin;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        redirect_url('admin-area/digital-goods/categories');
    });

    $router->get('digital-goods/categories', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $categories = $db->run("SELECT * FROM digital_good_categories ORDER BY sort_order ASC, name ASC") ?: [];
        foreach ($categories as &$cat) {
            $cat['listing_count'] = (int)($db->run("SELECT COUNT(*) AS c FROM digital_goods WHERE category_id=? AND active=1", (int)$cat['id'])[0]['c'] ?? 0);
        }
        unset($cat);
        $meta = ['title' => 'DG Categories | Admin'];
        view_file('admin/pages/digital-goods/categories', compact('meta', 'categories'));
    });

    $router->get('digital-goods/listings/:id/edit', function ($id) {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $id = (int)$id;
        $rows = $db->run("SELECT dg.*, dgc.slug AS category_slug FROM digital_goods dg LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id WHERE dg.id=? LIMIT 1", $id);
        $listing = $rows[0] ?? null;
        if (!$listing) { redirect_url('admin-area/digital-goods/listings'); return; }
        $categories = $db->run("SELECT id, name, slug FROM digital_good_categories ORDER BY name ASC") ?: [];
        $brands = function_exists('dg_get_brands') ? dg_get_brands(true) : [];
        $meta = ['title' => 'Edit DG Listing | Admin'];
        // Reuse the seller create/edit view — admin can edit any listing
        view_file('seller/pages/digital-goods/create', compact('meta', 'listing', 'categories', 'brands'));
    });

    $router->get('digital-goods/listings', function () {
        global $is_admin, $db;
        if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
        $status  = esc($_GET['status'] ?? '');
        $catId   = (int)($_GET['category_id'] ?? 0);
        $search  = esc($_GET['search'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 40;
        $offset  = ($page - 1) * $perPage;

        $where = "1=1"; $params = [];
        if ($status === 'active')   { $where .= " AND dg.active=1"; }
        if ($status === 'inactive') { $where .= " AND dg.active=0"; }
        if ($catId > 0)             { $where .= " AND dg.category_id=?"; $params[] = $catId; }
        if ($search !== '')         { $where .= " AND (dg.title LIKE ? OR dg.brand LIKE ?)"; $s = '%'.$search.'%'; $params[] = $s; $params[] = $s; }

        $total = (int)($db->run("SELECT COUNT(*) AS c FROM digital_goods dg WHERE {$where}", ...$params)[0]['c'] ?? 0);
        $params[] = $perPage; $params[] = $offset;
        $listings = $db->run(
            "SELECT dg.*, dgc.name AS category_name, dgc.slug AS category_slug, s.username AS seller_username
             FROM digital_goods dg
             LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id
             LEFT JOIN sellers s ON s.id=dg.seller_id
             WHERE {$where}
             ORDER BY dg.created_at DESC LIMIT ? OFFSET ?",
            ...$params
        ) ?: [];
        $categories = $db->run("SELECT id, name, slug FROM digital_good_categories ORDER BY name ASC") ?: [];
        $brands = function_exists('dg_get_brands') ? dg_get_brands(false) : [];
        $pagination = ['page'=>$page,'totalPages'=>max(1,(int)ceil($total/$perPage)),'totalItems'=>$total];
        $meta = ['title' => 'DG Listings | Admin'];
        view_file('admin/pages/digital-goods/listings', compact('meta', 'listings', 'categories', 'brands', 'pagination', 'status', 'catId', 'search', 'page', 'perPage'));
    });

});

$is_booster = defined('BOOSTER_DATA') && !empty(BOOSTER_DATA);
$is_egirl   = defined('IS_EGIRL') && IS_EGIRL;

$router->group('booster-area', function () {
    global $router;
    $router->get('auth/login', function () {
        global $is_booster;
        if (!$is_booster) {
            view_file('booster/auth/login');
        } else {
            redirect_url('booster-area/dashboard');
        }
    });

    $router->get('auth/logout', function () {
        logout_all_sessions();
        redirect_url('');
    });
    $router->get('/', function () {
        redirect_url('booster-area/dashboard');
    });
    $router->get('setup', function () {
        global $is_booster;
        if (!$is_booster) {
            redirect_url('booster-area/auth/login');
        }
        $status = booster_setup_status(BOOSTER_ID);
        view_file('booster/pages/setup', ['data' => $status]);
    });

    $router->get('dashboard', function () {
        global $is_booster, $is_egirl;

        // E-Girls get their own dashboard
        if ($is_egirl) {
            redirect_url('booster-area/egirl-dashboard');
            return;
        }

        // get orders where booster_id = BOOSTER_ID and status is in_progress, completed and all time count, justneed these three counts
        $orders_in_progress = db_get_row_count('orders', ['booster_id' => BOOSTER_ID, 'status' => 'in_progress']);
        $orders_completed = db_get_row_count('orders', ['booster_id' => BOOSTER_ID, 'status' => 'completed']);
        // Available orders should only include the games this booster can handle
    global $db;
    $booster_row = db_get_row('boosters', ['id' => BOOSTER_ID, 'select' => 'id,games'], 1);
    $booster_games = explode('|', $booster_row['games'] ?? (BOOSTER_DATA['games'] ?? ''));
    $allowed_games = ['league-of-legends', 'teamfight-tactics', 'valorant'];
    $booster_games = array_values(array_filter(array_intersect($booster_games, $allowed_games)));

    $orders_total = 0;
    if (!empty($booster_games)) {
        $in = implode(',', array_map(function ($g) {
            return db_format_val($g);
        }, $booster_games));

        // Count only unassigned PAID orders for the booster games (orders table doesn't store game directly)
        $q = "SELECT COUNT(*) 
              FROM orders o 
              JOIN boost_forms f ON o.form_id = f.id 
              WHERE o.booster_id IS NULL 
                AND (o.claimed_at IS NULL OR o.claimed_at = '0000-00-00 00:00:00')
                AND UPPER(o.status) = 'PAID'
                AND f.game IN ($in)";
        $orders_total = (int) ($db->single($q) ?? 0);
    }

        $data = [
            'orders_in_progress' => $orders_in_progress,
            'orders_completed' => $orders_completed,
            'orders_total' => $orders_total,
        ];

        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            view_file('booster/pages/dashboard', ['data' => $data]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });
    $router->get('payments', function () {
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            $data = db_get_rows('booster_payments', ['booster_id' => BOOSTER_ID], 1);
            foreach ($data as $key => $payment) {
                $sender = db_get_row('admins', ['id' => $payment['sender_id'], 'select' => 'id,username,icon'], 1);
                $data[$key]['sender'] = $sender;
            }
            view_file('booster/pages/payments', ['data' => $data]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });

    $router->get('/rules-and-fines', function () {
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            view_file('booster/pages/rules');
        } else {
            redirect_url('booster-area/auth/login');
        }
    });


    $router->get('leaderboard', function () {
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }
            view_file('booster/pages/leaderboard');
        } else {
            redirect_url('booster-area/auth/login');
        }
    });
    $router->get('performance', function () {
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }
            view_file('booster/pages/performance', ['booster_id' => BOOSTER_ID]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });
    $router->get('profile', function () {
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            $profile = db_get_row('booster_profiles', ['booster_id' => BOOSTER_ID], 1);
            $games = explode('|', BOOSTER_DATA['games']);
            if (empty($profile)) {
                db_add_row('booster_profiles', ['booster_id' => BOOSTER_ID]);
                $profile = db_get_row('booster_profiles', ['booster_id' => BOOSTER_ID], 1);
            }
            $limits = db_get_row('booster_limits', ['booster_id' => BOOSTER_ID], 1);
            if (empty($limits)) {
                db_add_row('booster_limits', ['booster_id' => BOOSTER_ID]);
                $limits = db_get_row('booster_limits', ['booster_id' => BOOSTER_ID], 1);
            }
            if (in_array('league-of-legends', $games) || in_array('lol', $games)) {
                $profile['lol_rank'] = explode('|', $profile['lol_rank'] ?? ' ');
                $limits['lol_rank_limit'] = explode('|', $limits['lol_rank_limit'] ?? '0|0');
                $limits['lol_tier_limit'] = $limits['lol_rank_limit'][0];
                $limits['lol_division_limit'] = $limits['lol_rank_limit'][1];
                unset($limits['lol_rank_limit']);
            }
            if (in_array('valorant', $games) || in_array('val', $games)) {
                $profile['val_rank'] = explode('|', $profile['val_rank'] ?? ' ');
                $limits['val_rank_limit'] = explode('|', $limits['val_rank_limit'] ?? '0|0');
                $limits['val_tier_limit'] = $limits['val_rank_limit'][0];
                $limits['val_division_limit'] = $limits['val_rank_limit'][1];
                unset($limits['val_rank_limit']);
            }
            if (in_array('teamfight-tactics', $games) || in_array('tft', $games)) {
                $profile['tft_rank'] = explode('|', $profile['tft_rank'] ?? ' ');
                $limits['tft_rank_limit'] = explode('|', $limits['tft_rank_limit'] ?? '0|0');
                $limits['tft_tier_limit'] = $limits['tft_rank_limit'][0];
                $limits['tft_division_limit'] = $limits['tft_rank_limit'][1];
                unset($limits['tft_rank_limit']);
            }

            $profile['features'] = explode('|', $profile['features'] ?? '||');
            $data = array_merge($profile, $limits);
            $data['games'] = $games;

            view_file('booster/pages/profile', ['data' => $data]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });
    $router->get('personal-details', function () {
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            $data = db_get_row('booster_personal_details', ['booster_id' => BOOSTER_ID], 1);
            $limits = db_get_row('booster_limits', ['booster_id' => BOOSTER_ID], 1);
            $games = explode('|', BOOSTER_DATA['games']);
            $profile = db_get_row('booster_profiles', ['booster_id' => BOOSTER_ID], 1);

            // db_get_row() returns false when a booster has no details/limits/profile row yet.
            // Normalize to arrays so the array_merge() below cannot fatal.
            $data = is_array($data) ? $data : [];
            $limits = is_array($limits) ? $limits : [];
            $profile = is_array($profile) ? $profile : [];

            if (in_array('league-of-legends', $games) || in_array('lol', $games)) {
                $profile['lol_rank'] = explode('|', $profile['lol_rank'] ?? ' ');
                $limits['lol_rank_limit'] = explode('|', $limits['lol_rank_limit'] ?? '0|0');
                $limits['lol_tier_limit'] = $limits['lol_rank_limit'][0] ?? 0;
                $limits['lol_division_limit'] = $limits['lol_rank_limit'][1] ?? 0;
                unset($limits['lol_rank_limit']);
            }
            if (in_array('valorant', $games) || in_array('val', $games)) {
                $profile['val_rank'] = explode('|', $profile['val_rank'] ?? ' ');
                $limits['val_rank_limit'] = explode('|', $limits['val_rank_limit'] ?? '0|0');
                $limits['val_tier_limit'] = $limits['val_rank_limit'][0] ?? 0;
                $limits['val_division_limit'] = $limits['val_rank_limit'][1] ?? 0;
                unset($limits['val_rank_limit']);
            }
            if (in_array('teamfight-tactics', $games) || in_array('tft', $games)) {
                $profile['tft_rank'] = explode('|', $profile['tft_rank'] ?? ' ');
                $limits['tft_rank_limit'] = explode('|', $limits['tft_rank_limit'] ?? '0|0');
                $limits['tft_tier_limit'] = $limits['tft_rank_limit'][0] ?? 0;
                $limits['tft_division_limit'] = $limits['tft_rank_limit'][1] ?? 0;
                unset($limits['tft_rank_limit']);
            }

            $data = array_merge($data, $limits, $profile);
            $data['games'] = $games;

            if (empty($data)) {
                db_add_row('booster_personal_details', ['booster_id' => BOOSTER_ID]);
                $data = db_get_row('booster_personal_details', ['booster_id' => BOOSTER_ID], 1);
            }

            view_file('booster/pages/personal-details', ['data' => $data]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });
    $router->get('orders', function () {
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            // 1. Load all regular orders for this booster.
            $data = db_get_rows('orders', ['select' => 'id,form_id,price,price_eur,currency,status,client_id,created_at,paid_at,booster_cut', 'booster_id' => BOOSTER_ID, 'status' => ['n' => 'UNPAID']], 1) ?: [];

            // Ranked 5s stores every participating booster in order_boosters. Only the first
            // booster may also exist in orders.booster_id, therefore merge active memberships.
            try {
                global $db;
                $r5AssignedRows = $db->run(
                    "SELECT o.id,o.form_id,o.price,o.price_eur,o.currency,o.status,o.client_id,o.created_at,o.paid_at,
                            CASE WHEN o.form_id IN (4, 19, 29) THEN 50 ELSE o.booster_cut END AS booster_cut,
                            ob.role AS ranked_5s_role,ob.slot_no AS ranked_5s_slot_no,
                            ob.claimed_at AS ranked_5s_claimed_at
                       FROM order_boosters ob
                       INNER JOIN orders o ON o.id = ob.order_id
                      WHERE ob.booster_id = ?
                        AND ob.status = 'ACTIVE'
                        AND o.form_id IN (4, 19, 29)
                        AND o.status <> 'UNPAID'",
                    (int)BOOSTER_ID
                ) ?: [];

                $knownOrderIds = [];
                foreach ($data as $existingOrder) {
                    $knownOrderIds[(int)($existingOrder['id'] ?? 0)] = true;
                }
                foreach ($r5AssignedRows as $assignedOrder) {
                    $assignedId = (int)($assignedOrder['id'] ?? 0);
                    if ($assignedId <= 0 || isset($knownOrderIds[$assignedId])) continue;
                    $data[] = $assignedOrder;
                    $knownOrderIds[$assignedId] = true;
                }
            } catch (Throwable $e) {}
            
            // 2. Load all forms at once
            $forms = db_get_rows('boost_forms', ['select' => 'id,type,name,icon,game']);
            $forms = array_column($forms, null, 'id');
            
            // 3. OPTIMIZED: Load all clients at once (batch loading instead of N+1 queries)
            $clientIds = array_unique(array_filter(array_column($data, 'client_id')));
            $clients = [];
            if (!empty($clientIds)) {
                $clientIdsList = implode(',', array_map('intval', $clientIds));
                global $db;
                $clientsData = $db->run("SELECT id, username, icon FROM clients WHERE id IN ({$clientIdsList})");
                $clients = array_column($clientsData, null, 'id');
            }
            
            // 4. OPTIMIZED: Load all order_options at once — includes all boolean flags for Options column
            $orderIds = array_column($data, 'id');
            $orderOptions = [];
            if (!empty($orderIds)) {
                $orderIdsList = implode(',', array_map('intval', $orderIds));
                global $db;
                $orderOptionsData = $db->run("
                    SELECT order_id, server, hours, boosters, start_tier, start_division,
                           end_tier, end_division, start_lp, end_lp, matches,
                           queue_type, flash_position, vpn_country, lp_gain,
                           is_duo, is_priority, is_streaming, is_solo_only,
                           is_bonus_win, is_offline_mode, is_coaching,
                           is_hidden_duo, is_undercover_winrate, is_moderate_kda,
                           agents, champions, roles
                    FROM order_options
                    WHERE order_id IN ({$orderIdsList})
                ");
                $orderOptions = array_column($orderOptionsData, null, 'order_id');
            }
            
            // 5. Merge data (no additional queries needed)
            foreach ($data as $key => $order) {
                $order_opts = $orderOptions[$order['id']] ?? [];
                $client = $clients[$order['client_id']] ?? ['id' => $order['client_id'], 'username' => 'Unknown', 'icon' => ''];
                $form   = $forms[$order['form_id']] ?? [];
                $data[$key] = array_merge($order_opts, $form, $order);
                if ((int)($data[$key]['form_id'] ?? 0) === 29) {
                    $data[$key]['booster_cut'] = 50;
                }
                $data[$key]['client_data'] = $client;
                // Expose game at top level so the list view can filter by it
                if (!isset($data[$key]['game']) && !empty($form['game'])) {
                    $data[$key]['game'] = $form['game'];
                }
            }

            view_file('booster/pages/orders/list', ['data' => $data]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });

    $router->get('orders-panel', function () {
        global $is_booster, $is_egirl;
        // E-Girls only handle GG-Girl bookings, not the normal boost order pool.
        // Without this guard they'd see regular orders (egirl accounts are created
        // with games='lol', which matches the LoL order feed below).
        if ($is_egirl) {
            redirect_url('booster-area/egirl-dashboard');
            return;
        }
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            $booster = db_get_row('boosters', ['id' => BOOSTER_ID], 1);
            $games = array_values(array_filter(array_map('trim', explode('|', (string)($booster['games'] ?? '')))));

            // LoL Classic uses the same booster pool and permissions as regular LoL.
            if (in_array('lol', $games, true)) {
                $games[] = 'lol_classic';
                $games[] = 'lol-classic';
            }

            // Some boosters have a legacy short code saved (e.g. "rl") from a previous version
            // of the Games select that incorrectly aliased these dynamically-added games.
            // boost_forms.game actually stores the full games.slug (e.g. "rocket-league"), so
            // add the full-slug equivalent for any legacy short code found in boosters.games.
            $legacyShortCodeToSlug = [
                'rl' => 'rocket-league',
                'apex' => 'apex-legends',
                'rivals' => 'marvel-rivals',
                'ow2' => 'overwatch-2',
                'wild-rift' => 'lol-wild-rift',
                // LoL Classic is not a separate qualification: every LoL booster may take
                // Classic orders and uses the same LoL rank/order limits.
                'lol' => 'lol_classic',
            ];
            foreach ($games as $g) {
                if (isset($legacyShortCodeToSlug[$g])) {
                    $games[] = $legacyShortCodeToSlug[$g];
                }
            }

            $games = array_values(array_unique($games));
            $data = [];

            if (!empty($games)) {
                $forms = db_get_rows('boost_forms', ['select' => 'id,type,name,icon,game']);
                $forms = array_column($forms, null, 'id');

                $paidOrders = db_get_rows('orders', [
                    'select' => 'id,form_id,price,currency,status,created_at,paid_at,booster_id,claimed_at,client_id',
                    'status' => 'PAID',
                    'booster_id' => null,
                    'claimed_at' => null,
                    'order' => 'created_at DESC',
                ], 1) ?: [];

                foreach ($paidOrders as $order) {
                    $form = $forms[$order['form_id']] ?? [];
                    if (empty($form) || !in_array($form['game'] ?? '', $games, true)) continue;
                    $order_opts = db_get_row('order_options', [
                        'order_id' => $order['id'],
                        'select' => 'server,hours,boosters,roles,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo,current_rank'
                    ], 1) ?: [];
                    $data[] = array_merge($order_opts, $form, $order);
                }

                // Ranked 5s stays visible until every requested booster slot is claimed.
                // Important: after the first booster joins, orders.booster_id is no longer NULL,
                // so this must be handled separately from regular solo orders.
                try {
                    global $db;
                    $r5Form = $forms[29] ?? [];
                    $clashForm = $forms[19] ?? [];
                    $r5GameAllowed = empty($r5Form['game']) || in_array((string)$r5Form['game'], $games, true);
                    $clashGameAllowed = empty($clashForm['game']) || in_array((string)$clashForm['game'], $games, true);

                    if ($r5GameAllowed || $clashGameAllowed) {
                        $r5rows = $db->run(
                            "SELECT o.id,o.form_id,o.price,o.currency,o.status,o.created_at,o.paid_at,o.booster_id,o.claimed_at,o.client_id
                               FROM orders o
                              WHERE o.form_id IN (4, 19, 29)
                                AND o.completed_at IS NULL
                                AND o.status IN ('PAID','PROCESSING','IN_PROGRESS')
                              ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC
                              LIMIT 150"
                        ) ?: [];

                        $seen = array_fill_keys(array_map(static fn($r) => (int)($r['id'] ?? 0), $data), true);
                        foreach ($r5rows as $order) {
                            $r5OrderId = (int)($order['id'] ?? 0);
                            if ($r5OrderId <= 0 || !empty($seen[$r5OrderId])) continue;

                            $form = $forms[$order['form_id']] ?? [];
                            if (!in_array((string)($form['game'] ?? ''), $games, true)) continue;
                            $order_opts = db_get_row('order_options', [
                                'order_id' => $r5OrderId,
                                'select' => 'server,hours,boosters,roles,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo,current_rank'
                            ], 1) ?: [];

                            $requiredBoosters = max(1, min(4, (int)($order_opts['boosters'] ?? 1)));
                            $claimedBoosterIds = [];
                            $boosterAlreadyJoined = false;

                            try {
                                $claimedRows = $db->run(
                                    "SELECT DISTINCT booster_id
                                       FROM order_boosters
                                      WHERE order_id = ?
                                        AND status = 'ACTIVE'
                                        AND booster_id IS NOT NULL
                                        AND booster_id > 0",
                                    $r5OrderId
                                ) ?: [];

                                foreach ($claimedRows as $claimedRow) {
                                    $claimedId = (int)($claimedRow['booster_id'] ?? 0);
                                    if ($claimedId <= 0) continue;
                                    $claimedBoosterIds[$claimedId] = true;
                                    if ($claimedId === (int)BOOSTER_ID) {
                                        $boosterAlreadyJoined = true;
                                    }
                                }
                            } catch (Throwable $e) {}

                            // Legacy fallback: the first/main booster can still be stored only on orders.booster_id.
                            $mainBoosterId = (int)($order['booster_id'] ?? 0);
                            if ($mainBoosterId > 0) {
                                $claimedBoosterIds[$mainBoosterId] = true;
                                if ($mainBoosterId === (int)BOOSTER_ID) {
                                    $boosterAlreadyJoined = true;
                                }
                            }

                            if ($boosterAlreadyJoined) continue;
                            if (count($claimedBoosterIds) >= $requiredBoosters) continue;

                            $data[] = array_merge($order_opts, $form, $order);
                        }
                    }
                } catch (Throwable $e) {}
            }

            view_file('booster/pages/orders/panel', ['data' => $data]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });
    $router->get('order/:id', function ($id) {
        global $db;
        global $is_booster;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            $id = intval($id);
            $orderSelect = 'id,form_id,price,price_eur,currency,status,created_at,booster_id,claimed_at,booster_cut,paid_at,client_id';
            $order = db_get_row('orders', ['id' => $id, 'select' => $orderSelect, 'booster_id' => BOOSTER_ID], 1);
            $ranked5sMembership = null;

            // Ranked 5s participants after the first booster live in order_boosters, not in
            // orders.booster_id. Grant access through their active membership and mark the
            // order as assigned to the current booster for the booster order view.
            if (empty($order)) {
                try {
                    $membershipRows = $db->run(
                        "SELECT id,order_id,booster_id,slot_no,role,cut_percent,status,claimed_at
                           FROM order_boosters
                          WHERE order_id = ?
                            AND booster_id = ?
                            AND status = 'ACTIVE'
                          LIMIT 1",
                        $id,
                        (int)BOOSTER_ID
                    ) ?: [];
                    $ranked5sMembership = $membershipRows[0] ?? null;
                } catch (Throwable $e) {
                    $ranked5sMembership = null;
                }

                if (!empty($ranked5sMembership)) {
                    $order = db_get_row('orders', ['id' => $id, 'select' => $orderSelect], 1);
                    if (!empty($order)) {
                        $order['original_booster_id'] = (int)($order['booster_id'] ?? 0);
                        $order['booster_id'] = (int)BOOSTER_ID;
                        $order['claimed_at'] = $ranked5sMembership['claimed_at'] ?? $order['claimed_at'];
                        $order['booster_cut'] = 50;
                        $order['ranked_5s_role'] = $ranked5sMembership['role'] ?? '';
                        $order['ranked_5s_slot_no'] = (int)($ranked5sMembership['slot_no'] ?? 0);
                        $order['is_ranked_5s_member'] = 1;

                        // A joined Ranked 5s booster must see the active order/chat view even
                        // while free team slots keep the public order available for others.
                        if (in_array((string)($order['status'] ?? ''), ['PAID', 'PROCESSING'], true)) {
                            $order['status'] = 'IN_PROGRESS';
                        }
                    }
                }
            }

            // Only genuinely unassigned paid orders may be opened as claimable orders.
            if (empty($order)) {
                $order = db_get_row('orders', [
                    'id' => $id,
                    'select' => $orderSelect,
                    'status' => 'PAID',
                    'booster_id' => null,
                ], 1);
            }
            if (empty($order)) {
                redirect_url('booster-area/orders');
            }
            $form = db_get_row('boost_forms', ['id' => $order['form_id'], 'select' => 'id,type,name,icon,game']);
            $order_opts = db_get_row('order_options', ['order_id' => $order['id'], 'select' => 'server,hours,boosters,start_tier,start_division,end_tier,end_division,start_lp,end_lp,matches,queue_type,is_duo,is_streaming,is_coaching,is_voice,is_hidden_duo'], 1);
            $order_acc = db_get_row('order_accounts', ['order_id' => $order['id']], 1);
            $ss = db_get_row('order_screenshots', [
                'order_id' => $order['id'],
                'order' => 'created_at,DESC',
                'limit' => 1
            ], 1);
            if (!empty($ss) && isset($ss['id'])) {
                $ss['screenshot_id'] = $ss['id'];
                unset($ss['id']);
            }
            // $notes = db_get_rows('order_notes', ['order_id' => $order['id'], 'type' => 'client']);

            // $note_query = raw query to get order_notes type both client and booster
            $note_query = "SELECT * FROM order_notes WHERE order_id = $id AND (type = 'client' OR type = 'booster')";

            $notes = $db->run($note_query);

            $order = array_merge($form, $order_opts, $order_acc, $ss, $order, ['notes' => $notes]);
            if ((int)($order['form_id'] ?? 0) === 29) {
                $order['booster_cut'] = 50;
            }
            $invoice = db_get_row('invoices', ['order_id' => $order['id'], 'order_type' => 'order'], 1);
            $client = db_get_row('clients', ['id' => $order['client_id'], 'select' => 'id,username,icon,email'], 1);

            $review = db_get_row('reviews', ['order_id' => $order['id']]);
            $order['progress'] = lb_order_progress_ensure_start_rank(
                (int) $order['id'],
                db_get_row('order_progress', ['order_id' => $order['id']], 1) ?: []
            );

            view_file('booster/pages/orders/view', ['data' => $order, 'invoice' => $invoice, 'client' => $client, 'review' => $review]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });
    // =========================
    // Payout (Booster)
    // =========================
    $router->get('payout', function () {
        global $is_booster, $db;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            $methods = $db->run("SELECT * FROM booster_payout_methods WHERE booster_id=? ORDER BY is_default DESC, id DESC", BOOSTER_ID);
            view_file('booster/pages/payout', ['methods' => $methods]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });

    $router->get('payout-requests', function () {
        global $is_booster, $db;
        if ($is_booster) {
            if (!booster_setup_is_complete(BOOSTER_ID)) {
                redirect_url('booster-area/setup');
            }

            $methods = $db->run("SELECT * FROM booster_payout_methods WHERE booster_id=? ORDER BY is_default DESC, id DESC", BOOSTER_ID);
            $requests = $db->run("SELECT r.*, m.method AS method_type, m.details AS method_details, m.label AS method_label FROM booster_payout_requests r LEFT JOIN booster_payout_methods m ON m.id=r.payout_method_id WHERE r.booster_id=? ORDER BY r.id DESC", BOOSTER_ID);
            view_file('booster/pages/payout-requests', ['methods' => $methods, 'requests' => $requests]);
        } else {
            redirect_url('booster-area/auth/login');
        }
    });

});

$router->get('jobs/apply', function () {
    $meta = [
        'title'       => 'Apply — Join Our Team | LoLBoost',
        'description' => 'Apply to become a booster, coach, seller or GG Girl at LoLBoost. Earn money doing what you love.',
    ];
    view_file('website/pages/apply', compact('meta'));
});

// Legacy redirects → neue unified Bewerbungsseite
$router->get('apply/lol', function () {
    header('location: /jobs/apply');
    exit;
});

$router->get('apply/val', function () {
    header('location: /jobs/apply');
    exit;
});

$router->get('apply/tft', function () {
    header('location: /jobs/apply');
    exit;
});

$router->get('auth/google', function () {
    $client = new Google_Client();

    $client->setClientId(GL_CLIENT_ID);
    $client->setClientSecret(GL_SECRET);
    $client->setRedirectUri(GL_REDIRECT_URL);
    $client->addScope("email");

    // add state to url set it to referer url
    // Direct hits on /auth/google have no referer; fall back to the site root.
    $client->setState($_SERVER['HTTP_REFERER'] ?? BASE_URL);

    $redirect_url = $client->createAuthUrl();

    header('location: ' . $redirect_url);
});

$router->get('auth/callback/google', function () {
    if (isset($_GET['code']) && !empty($_GET['code'])) {
        $login_error = false;
        $code = htmlspecialchars($_GET["code"]);
        $data = auth_google_login($code);
        if ($data == false) {
            $login_error = true;
        }
        if (isset($data) && !empty($data) && !$login_error) {
            #check if user exists social_oauth, oauth_uid, 
            $client_id = db_get_row('clients', ['email' => $data['email'], 'oauth_uid' => $data['oauth_uid'], 'oauth_provider' => $data['oauth_provider']]);
            if ($client_id == false) {
                #if false check if email already exists
                if (db_get_row('clients', ['email' => $data['email']]) == false) {
                    #if false create account flow
                    if (!isset($data['discord'])) {
                        $data['discord'] = null;
                    }
                    $client_id = db_add_row('clients', $data);
                } else {
                    #if true forward user to email used error
                    echo "Email is linked to another account";
                    exit();
                }
            }
            if ($client_id != false && !empty($client_id)) {
                if (is_array($client_id)) {
                    $client_id = $client_id['id'];
                }
                #create session flow
                $token = db_auth_session_start($client_id, 'clients');
                util_create_cookie('client_session_token', $token, 1);
                if (isset($_GET['state']) && !empty($_GET['state'])) {
                    $state = esc($_GET['state']);
                    header('Location: ' . $state);
                } else {
                    redirect_url('');
                }
            } else {
                echo "Client ID does not match social login provider.";
                exit();
            }
        } else {
            echo "Something went wrong try Logging in again.";
        }
    }
});


$router->get('auth/discord', function () {
    // client_id/redirect_uri MUST match the app whose secret auth_discord_load_token()
    // uses for the token exchange, otherwise Discord rejects the code.
    $redirect_url = 'https://discord.com/api/oauth2/authorize?client_id=' . urlencode(DS_CLIENT_ID) . '&response_type=code&scope=email%20identify&redirect_uri=' . urlencode(DS_REDIRECT_URL) . '&state=' . urlencode($_SERVER['HTTP_REFERER'] ?? BASE_URL);

    header('location: ' . $redirect_url);
});

$router->get('auth/discord/connect', function () {
    $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
    $booster_id = isset($_GET['booster_id']) ? intval($_GET['booster_id']) : null;
    $seller_id  = isset($_GET['seller_id'])  ? intval($_GET['seller_id'])  : null;

    if ($seller_id) {
        $state = "seller|$seller_id|$referer";
    } else {
        $state = "booster|$booster_id|$referer";
    }

    // Same app as the token exchange (see auth_discord_load_token()).
    $redirect_url = 'https://discord.com/api/oauth2/authorize?client_id=' . urlencode(DS_CLIENT_ID) . '&response_type=code&scope=email%20identify&redirect_uri=' . urlencode(DS_REDIRECT_URL) . '&state=' . urlencode($state);

    header('location: ' . $redirect_url);
});

$router->get('auth/callback/discord', function () {
    if (isset($_GET['code']) && !empty($_GET['code'])) {
        $login_error = false;
        $code = esc($_GET["code"]);
        $data = auth_discord_login($code);

        if ($data == false) {
            $login_error = true;
        }

        if (isset($data) && !empty($data) && !$login_error) {
            $state = isset($_GET['state']) ? esc($_GET['state']) : '';
            $state_parts = explode('|', $state);

            // Default fallback for safety
            $flow_type = $state_parts[0] ?? 'customer';
            $booster_id = $state_parts[1] ?? null;
            $redirect_to = $state_parts[2] ?? BASE_URL;

            if (count($state_parts) >= 3 && $flow_type === 'seller' && !empty($booster_id)) {
                // seller_id is stored in $booster_id slot (index 1)
                try {
                    global $db;
                    $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS discord VARCHAR(191) NULL, ADD COLUMN IF NOT EXISTS discord_id VARCHAR(64) NULL");
                } catch (\Throwable $e) {}
                try {
                    db_update_row('sellers', ['id' => (int)$booster_id], [
                        'discord'    => $data['discord'],
                        'discord_id' => $data['oauth_uid'],
                    ]);
                    header('Location: ' . $redirect_to);
                    exit();
                } catch (\Throwable $th) {
                    echo 'Error: ' . $th->getMessage();
                    exit();
                }
            } elseif (count($state_parts) >= 3 && $flow_type === 'booster' && !empty($booster_id)) {
                try {
                    db_update_row(
                        'boosters',
                        ['id' => $booster_id],
                        [
                            'discord' => $data['discord'],
                            'discord_id' => $data['oauth_uid'],
                        ]
                    );

                    header('Location: ' . $redirect_to);
                    exit();
                } catch (\Throwable $th) {
                    echo "Error: " . $th->getMessage();
                    exit();
                }
            } else {
                $client_id = db_get_row('clients', ['email' => $data['email'], 'oauth_uid' => $data['oauth_uid'], 'oauth_provider' => $data['oauth_provider']]);
                if ($client_id == false) {
                    if (db_get_row('clients', ['email' => $data['email']]) == false) {
                        if (!isset($data['discord'])) {
                            $data['discord'] = null;
                        }
                        $client_id = db_add_row('clients', $data);
                    } else {
                        echo "Email is linked to another account";
                        exit();
                    }
                }
                if ($client_id != false && !empty($client_id)) {
                    if (is_array($client_id)) {
                        $client_id = $client_id['id'];
                    }
                    $token = db_auth_session_start($client_id, 'clients');
                    util_create_cookie('client_session_token', $token, 1);
                    if (isset($_GET['state']) && !empty($_GET['state'])) {
                        $state = esc($_GET['state']);
                        header('Location: ' . $state);
                    } else {
                        redirect_url('');
                    }
                } else {
                    echo "Client ID does not match social login provider.";
                    exit();
                }
            }
        } else {
            echo "Something went wrong try Logging in again.";
        }
    }
});

$router->get('discord', function () {
    header('Location: https://discord.gg/GzzfyTffzN');
});

$router->get('instagram', function () {
    header('Location: https://www.instagram.com/lolboost.gg/');
});

$router->get('facebook', function () {
    header('Location: https://www.facebook.com/lolboost.gg');
});

$router->get('tiktok', function () {
    header('Location: https://tiktok.com/@lolboost.gg');
});

$router->get('review', function () {
    header('Location: https://www.trustpilot.com/evaluate/lolboost.gg');
});

$router->get('coaching', function () {
    header('Location: https://discord.gg/u2HD2TeVv4');
});

$router->get('streaming', function () {
    header('Location: https://discord.gg/u2HD2TeVv4');
});


$router->get('become-a-seller', function () {
    $meta = [
        'title'       => 'Become a Seller | Sell Accounts, Items & More | LoLBoost',
        'description' => 'Apply to sell game accounts, items and more on LoLBoost. Keep up to 99% of your earnings with instant payouts.',
        'canonical'   => BASE_URL . '/become-a-seller',
        'robots'      => 'index, follow',
    ];
    view_file('website/pages/become-seller', ['meta' => $meta]);
});

$router->get('become-a-gg-girl', function () {
    $meta = [
        'title'       => 'Become a GG Girl | Get Paid to Play | LoLBoost',
        'description' => 'Join our Gamer Girls team and get paid to play games with customers. Set your own schedule, flexible hours, fast payouts. Apply now.',
        'canonical'   => BASE_URL . '/become-a-gg-girl',
        'robots'      => 'index, follow',
    ];
    view_file('website/pages/become-a-gg-girl', ['meta' => $meta]);
});

$router->group('legal', function ($router) {
    $router->get('terms', function () {
        $meta = [
            'title' => 'Terms of Service | LoLBoost',
            'description' => t('Review the LoLBoost Terms of Service and understand your rights and responsibilities.'),
            'keywords' => 'Terms of Service',
        ];
        view_file('website/pages/legal/terms', ['meta' => $meta]);
    });

    $router->get('privacy', function () {
        $meta = [
            'title' => 'Privacy Policy | LoLBoost',
            'description' => t('Learn how LoLBoost handles and protects your personal data. Your privacy is our priority.'),
            'keywords' => 'Privacy Policy',
        ];
        view_file('website/pages/legal/privacy', ['meta' => $meta]);
    });

    $router->get('imprint', function () {
        $meta = [
            'title' => 'Imprint | Company Information | LoLBoost',
            'description' => t('Company information and legal details for LoLBoost â LB Gaming Services LTD.'),
            'keywords' => 'Imprint',
        ];
        view_file('website/pages/legal/imprint', ['meta' => $meta]);
    });
});

$router->get('become-a-booster', function () {
    $meta = [
        'title' => 'Become a Booster | LoLBoost',
        'description' => 'Join our team of professional boosters at LoLBoost. Apply now, boost players across all games and earn money doing what you love.',
        'keywords' => 'become a booster, LoLBoost, gaming jobs',
    ];

    view_file('main/pages/become-booster', ['meta' => $meta]);
});

$router->get('apply-for-booster', function () {
    $meta = [
        'title' => 'Apply for Booster Position | LoLBoost',
        'description' => 'Join our team of professional boosters at LoLBoost. Apply now, boost players across all games and earn money doing what you love.',
        'keywords' => 'apply for booster, LoLBoost',
    ];

    view_file('main/pages/application', ['meta' => $meta]);
});

$router->get('application/complete', function () {
    $meta = [
        'title' => 'Application Complete | LoLBoost',
        'description' => 'Thank you for applying at LoLBoost. We will review your application and get back to you soon.',
        'keywords' => 'application complete, LoLBoost',
    ];
    view_file('website/pages/application-complete', ['meta' => $meta]);
});

$router->get('onboarding', function () {
    global $db;

    $token = isset($_GET['t']) ? esc($_GET['t']) : null;
    $query = "SELECT * FROM one_time_links WHERE token = ? AND action = 'onboarding' AND used < 3 AND expires_at > NOW() LIMIT 1";
    $token = $db->run($query, $token);

    if (empty($token)) {
        $meta = [
            'title' => '404 - Page not found',
            'description' => '404 - Page not found',
            'keywords' => '404, Page not found',
        ];

        view_file('main/errors/404', ['meta' => $meta]);
        exit;
    }

    db_update_row('one_time_links', ['token' => $_GET['t']], ['used' => intval($token[0]['used']) + 1]);
    onboarding_remember_token($token[0]['token']);
    view_file('main/pages/onboarding', ['onboarding_token' => (string) $token[0]['token']]);
});

$router->get('egirl-onboarding', function () {
    global $db;

    $token = isset($_GET['t']) ? esc($_GET['t']) : null;
    $query = "SELECT * FROM one_time_links WHERE token = ? AND action = 'egirl_onboarding' AND used < 3 AND expires_at > NOW() LIMIT 1";
    $token = $db->run($query, $token);

    if (empty($token)) {
        $meta = [
            'title' => '404 - Page not found',
            'description' => '404 - Page not found',
            'keywords' => '404, Page not found',
        ];

        view_file('main/errors/404', ['meta' => $meta]);
        exit;
    }

    db_update_row('one_time_links', ['token' => $_GET['t']], ['used' => intval($token[0]['used']) + 1]);
    onboarding_remember_token($token[0]['token']);
    view_file('main/pages/egirl-onboarding', ['onboarding_token' => (string) $token[0]['token']]);
});

$router->get('seller-onboarding', function () {
    global $db;

    $token = isset($_GET['t']) ? esc($_GET['t']) : null;
    $query = "SELECT * FROM one_time_links WHERE token = ? AND action = 'seller_onboarding' AND used < 3 AND expires_at > NOW() LIMIT 1";
    $tokenRow = $db->run($query, $token);

    if (empty($tokenRow)) {
        $meta = ['title' => '404 - Page not found'];
        view_file('main/errors/404', ['meta' => $meta]);
        exit;
    }

    // Pass token to view so form can include it for one-time-use tracking
    $onboarding_token = (string) $tokenRow[0]['token'];
    onboarding_remember_token($onboarding_token);
    view_file('seller/pages/onboarding', ['onboarding_token' => $onboarding_token]);
});

$router->get('/custom-script', function () {
    $accounts = db_get_rows('selling_accounts');

    foreach ($accounts as $account) {
        $price = $account['price'];
        $newprice = rtrim($price, '0');
        $newprice = rtrim($newprice, '.');

        db_update_row('selling_accounts', ['id' => $account['id']], ['price' => $newprice]);
    }

    echo "Custom script executed successfully.";
});

$router->get('/clean-orders', function () {
    $unpaid_order = db_get_rows('orders', ['booster_id' => null]);

    foreach ($unpaid_order as $order) {
        db_delete_rows('orders', ['id' => $order['id']]);
        db_delete_rows('invoices', ['order_id' => $order['id']]);
        db_delete_rows('order_options', ['order_id' => $order['id']]);

    }

    echo "Cleaned up unpaid orders older than 48 hours.";
});

$router->notFound(function () {

    $meta = [
        'title' => '404 - Page not found',
        'description' => '404 - Page not found',
        'keywords' => '404, Page not found',
    ];
    view_file('main/errors/404', ['meta' => $meta]);
});

$router->error(function (Request $request, Response $response, Exception $exception) {
    // $response->setStatusCode(Response::HTTP_NOT_FOUND);
    // $response->setContent('Oops! Page not found!');
    return $exception;
});

// =============================================
// SELLER AREA ROUTES
// =============================================
$router->group('seller-area', function () {
    global $router, $is_seller, $seller_data, $db;

    // Keep authenticated sellers inside the one-time setup until every required
    // profile field is complete. The setup and logout routes must remain reachable.
    if ($is_seller && is_array($seller_data) && function_exists('lb_seller_setup_require_complete')) {
        $seller_request_path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $seller_setup_allowed_paths = [
            'seller-area/setup',
            'seller-area/auth/logout',
        ];
        if (strpos($seller_request_path, 'seller-area/') === 0
            && !in_array($seller_request_path, $seller_setup_allowed_paths, true)) {
            lb_seller_setup_require_complete($seller_data, $db);
        }
    }

    $router->get('setup', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller || !is_array($seller_data)) {
            redirect_url('seller-area/auth/login');
            return;
        }

        // Older seller rows may predate fields used by the setup checklist.
        $setup_columns = [
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS icon VARCHAR(512) NULL DEFAULT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS banner VARCHAR(512) NULL DEFAULT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS languages TEXT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS description TEXT NULL",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS allow_chat_requests TINYINT(1) NOT NULL DEFAULT 1",
            "ALTER TABLE sellers ADD COLUMN IF NOT EXISTS seller_setup_chat_ack TINYINT(1) NOT NULL DEFAULT 0",
        ];
        foreach ($setup_columns as $setup_sql) {
            try { $db->run($setup_sql); } catch (\Throwable $e) {}
        }

        $fresh_seller = db_get_row('sellers', ['id' => (int)$seller_data['id']], 1) ?: $seller_data;
        $setup = lb_seller_setup_status($fresh_seller, $db);
        if (!empty($setup['complete'])) {
            redirect_url('seller-area/dashboard');
            return;
        }

        $meta = ['title' => 'Seller Setup | LoLBoost.gg'];
        view_file('seller/pages/setup', compact('meta', 'setup'));
    });

    $router->get('auth/login', function () {
        global $is_seller;
        if (!$is_seller) {
            $meta = ['title' => 'Seller Login'];
            view_file('seller/auth/login', ['meta' => $meta]);
        } else {
            redirect_url('seller-area/dashboard');
        }
    });

    $router->get('auth/register', function () {
        global $is_seller;
        if ($is_seller) { redirect_url('seller-area/dashboard'); return; }
        $meta = ['title' => 'Become a Seller | LoLBoost'];
        view_file('seller/auth/register', ['meta' => $meta]);
    });

    $router->get('auth/logout', function () {
        // Only log out the current device/session. Do not remove other seller devices.
        if (!empty($_COOKIE['seller_session_token'])) {
            global $db;
            $db->run("DELETE FROM seller_sessions WHERE token = ?", esc($_COOKIE['seller_session_token']));
        }
        setcookie('seller_session_token', '', time() - 3600, '/');
        redirect_url('seller-area/auth/login');
    });

    $router->get('/', function () {
        redirect_url('seller-area/dashboard');
    });

    $router->get('api', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        if (function_exists('seller_api_ensure_tables')) { seller_api_ensure_tables(); }
        $seller_id = (int)($seller_data['id'] ?? 0);
        $api_keys = $db->run("SELECT id, name, key_prefix, last_used_at, is_active, created_at FROM seller_api_keys WHERE seller_id = ? ORDER BY id DESC", $seller_id) ?: [];
        $webhooks = $db->run("SELECT * FROM seller_webhooks WHERE seller_id = ? ORDER BY id DESC", $seller_id) ?: [];
        $webhook_logs = $db->run("SELECT * FROM seller_webhook_logs WHERE seller_id = ? ORDER BY id DESC LIMIT 20", $seller_id) ?: [];
        $api_logs = $db->run("SELECT * FROM seller_api_request_logs WHERE seller_id = ? ORDER BY id DESC LIMIT 30", $seller_id) ?: [];
        $api_stats = [
            'requests_24h' => (int)($db->cell("SELECT COUNT(*) FROM seller_api_request_logs WHERE seller_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)", $seller_id) ?? 0),
            'requests_7d'  => (int)($db->cell("SELECT COUNT(*) FROM seller_api_request_logs WHERE seller_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)", $seller_id) ?? 0),
            'errors_7d'    => (int)($db->cell("SELECT COUNT(*) FROM seller_api_request_logs WHERE seller_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status_code >= 400", $seller_id) ?? 0),
        ];
        $meta = ['title' => 'Partner API | LoLBoost.gg'];
        view_file('seller/pages/api', compact('meta', 'seller_data', 'api_keys', 'webhooks', 'webhook_logs', 'api_logs', 'api_stats'));
    });

    $router->get('dashboard', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        try {
            $seller_id = (int)$seller_data['id'];
            $total          = (int)$db->single("SELECT COUNT(*) FROM selling_accounts WHERE seller_id = $seller_id");
            // Unified sales: selling_accounts + selling_items + admin_id 51 bonus for seller #28
            $admin_bonus = ($seller_id === 28)
                ? (int)$db->single("SELECT COUNT(*) FROM accounts WHERE admin_id = 51 AND status = 1 AND client_id IS NOT NULL")
                : 0;
            $sold = (int)$db->single("
                SELECT
                    COALESCE((SELECT COUNT(*) FROM selling_accounts WHERE seller_id = $seller_id AND sold = 1 AND client_id IS NOT NULL), 0)
                    +
                    COALESCE((SELECT SUM(sold_count) FROM selling_items WHERE seller_id = $seller_id), 0)
            ") + $admin_bonus;
            $pending        = (int)$db->single("SELECT COUNT(*) FROM selling_accounts WHERE seller_id = $seller_id AND sold = 0 AND active = 1");
            $earnings_total = (int)$db->single("SELECT COALESCE(SUM(amount_cents),0) FROM seller_payments WHERE seller_id = $seller_id AND type IN ('sale_payout','admin_account_payout')");
            $recent_payments = $db->run("SELECT * FROM seller_payments WHERE seller_id = ? ORDER BY id DESC LIMIT 10", $seller_id) ?: [];

            // Rank is no longer auto-calculated from earnings here.
            // Seller rank should come from the shared seller-rank logic / valid sales logic.
        } catch (\Throwable $e) {
            $total = $sold = $pending = $earnings_total = 0;
            $recent_payments = [];
        }

        $meta = ['title' => 'Seller Dashboard | LoLBoost', 'h1' => 'Dashboard', 'description' => 'Overview of your listings, sales and earnings on LoLBoost.'];
        view_file('seller/pages/dashboard', compact('meta', 'seller_data', 'total', 'sold', 'pending', 'earnings_total', 'recent_payments'));
    });

    $router->get('analytics', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        $seller_id = (int)$seller_data['id'];

        // Date range: ?from=YYYY-MM-DD&to=YYYY-MM-DD, default last 14 days.
        $rangeTo   = !empty($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']) ? $_GET['to'] : date('Y-m-d');
        $rangeFrom = !empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime($rangeTo . ' -13 days'));
        if (strtotime($rangeFrom) > strtotime($rangeTo)) { $tmp = $rangeFrom; $rangeFrom = $rangeTo; $rangeTo = $tmp; }
        // Cap the window so the query stays cheap.
        if ((strtotime($rangeTo) - strtotime($rangeFrom)) / 86400 > 366) {
            $rangeFrom = date('Y-m-d', strtotime($rangeTo . ' -366 days'));
        }

        $analytics = ['orders_24h' => 0, 'profit_24h' => 0, 'listed_offers' => 0, 'profit_total' => 0, 'profit_prev' => 0, 'orders_total' => 0, 'orders_prev' => 0, 'days' => []];
        try {
            $analytics['orders_24h'] = (int)$db->single("
                SELECT COUNT(*) FROM seller_payments
                WHERE seller_id = $seller_id
                  AND type IN ('sale_payout','admin_account_payout','digital_good_payout','dg_sale_payout')
                  AND created_at >= (NOW() - INTERVAL 24 HOUR)
            ");
            $analytics['profit_24h'] = (int)$db->single("
                SELECT COALESCE(SUM(amount_cents),0) FROM seller_payments
                WHERE seller_id = $seller_id
                  AND type IN ('sale_payout','admin_account_payout','digital_good_payout','dg_sale_payout')
                  AND created_at >= (NOW() - INTERVAL 24 HOUR)
            ");
            $analytics['listed_offers'] = (int)$db->single("SELECT COUNT(*) FROM selling_accounts WHERE seller_id = $seller_id AND sold = 0 AND active = 1")
                + (int)$db->single("SELECT COUNT(*) FROM selling_items WHERE seller_id = $seller_id AND COALESCE(active,1) = 1")
                + (int)$db->single("SELECT COUNT(*) FROM selling_topups WHERE seller_id = $seller_id AND COALESCE(active,1) = 1");

            $rows = $db->run(
                "SELECT DATE(created_at) AS d,
                        COALESCE(SUM(amount_cents),0) AS revenue_cents,
                        SUM(CASE WHEN type IN ('sale_payout','admin_account_payout') THEN 1 ELSE 0 END) AS accounts_n,
                        SUM(CASE WHEN type IN ('digital_good_payout','dg_sale_payout') THEN 1 ELSE 0 END) AS dg_n
                 FROM seller_payments
                 WHERE seller_id = ?
                   AND type IN ('sale_payout','admin_account_payout','digital_good_payout','dg_sale_payout')
                   AND created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY d ASC",
                $seller_id, $rangeFrom, $rangeTo
            ) ?: [];
            $byDate = [];
            foreach ($rows as $r) {
                $byDate[(string)$r['d']] = [
                    'revenue'  => (int)($r['revenue_cents'] ?? 0),
                    'accounts' => (int)($r['accounts_n'] ?? 0),
                    'dg'       => (int)($r['dg_n'] ?? 0),
                ];
            }
            $cursor = strtotime($rangeFrom);
            $end    = strtotime($rangeTo);
            while ($cursor <= $end) {
                $d = date('Y-m-d', $cursor);
                $row = $byDate[$d] ?? ['revenue' => 0, 'accounts' => 0, 'dg' => 0];
                $analytics['days'][] = [
                    'date'     => $d,
                    'revenue'  => round($row['revenue'] / 100, 2),
                    'accounts' => $row['accounts'],
                    'dg'       => $row['dg'],
                    'orders'   => $row['accounts'] + $row['dg'],
                ];
                $analytics['profit_total'] += $row['revenue'];
                $analytics['orders_total'] += $row['accounts'] + $row['dg'];
                $cursor = strtotime('+1 day', $cursor);
            }
            $analytics['profit_total'] = round($analytics['profit_total'] / 100, 2);

            // Previous period of the same length, for the trend comparison.
            $spanDays = max(1, (int)round(($end - strtotime($rangeFrom)) / 86400) + 1);
            $prevTo   = date('Y-m-d', strtotime($rangeFrom . ' -1 day'));
            $prevFrom = date('Y-m-d', strtotime($prevTo . ' -' . ($spanDays - 1) . ' days'));
            $prevRow = $db->row(
                "SELECT COALESCE(SUM(amount_cents),0) AS revenue_cents, COUNT(*) AS orders_n
                 FROM seller_payments
                 WHERE seller_id = ?
                   AND type IN ('sale_payout','admin_account_payout','digital_good_payout','dg_sale_payout')
                   AND created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)",
                $seller_id, $prevFrom, $prevTo
            ) ?: [];
            $analytics['profit_prev'] = round((int)($prevRow['revenue_cents'] ?? 0) / 100, 2);
            $analytics['orders_prev'] = (int)($prevRow['orders_n'] ?? 0);
        } catch (\Throwable $e) {}

        $meta = ['title' => 'Analytics | Seller Dashboard | LoLBoost', 'h1' => 'Analytics', 'description' => 'Sales and revenue analytics for your seller account on LoLBoost.'];
        view_file('seller/pages/analytics', compact('meta', 'seller_data', 'analytics', 'rangeFrom', 'rangeTo'));
    });

    $router->get('chat', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        $seller_id = (int)$seller_data['id'];

        $read_chat_summary = function (string $chat_path, string $thread_key = '', string $thread_type = '', int $ref_id = 0) use ($seller_id): array {
            $summary = [
                'exists' => false,
                'count' => 0,
                'last_body' => '',
                'last_message_at' => 0,
                'unread_seller' => 0,
                'client_id' => 0,
                'client_username' => '',
                'client_icon' => '',
            ];
            $data = null;
            if ($thread_key !== '' && function_exists('lb_legacy_chat_open')) {
                try {
                    $opened = lb_legacy_chat_open($thread_key, $thread_type, $ref_id, ['seller_id' => $seller_id], $chat_path);
                    $data = ['messages' => array_map(static function (array $row): array {
                        return [
                            'sender' => $row['sender_type'] ?? 'system',
                            'sender_id' => $row['sender_id'] ?? 0,
                            'sender_name' => $row['sender_name'] ?? '',
                            'sender_icon' => $row['sender_icon'] ?? '',
                            'message' => $row['body'] ?? '',
                            'message_type' => $row['message_type'] ?? 'text',
                            'seen_by_seller' => $row['seen_by_seller'] ?? 0,
                            'deleted' => $row['deleted'] ?? 0,
                            'created_at' => $row['created_at'] ?? '',
                        ];
                    }, (array)($opened['messages'] ?? []))];
                } catch (\Throwable $e) { $data = null; }
            }
            if (!is_array($data)) {
                if (!is_file($chat_path)) { return $summary; }
                $raw = @file_get_contents($chat_path);
                $data = json_decode($raw ?: '', true);
            }
            if (!is_array($data) || empty($data['messages']) || !is_array($data['messages'])) { return $summary; }
            $summary['exists'] = true;
            foreach ($data['messages'] as $m) {
                if (!is_array($m) || !empty($m['deleted']) || (($m['type'] ?? '') === 'deleted')) { continue; }
                $sender = (string)($m['sender'] ?? $m['type'] ?? '');
                $seenBySeller = array_key_exists('seen_by_seller', $m) ? (int)$m['seen_by_seller'] : (int)($m['seen'] ?? 0);
                if ($sender === 'client' && $seenBySeller !== 1) { $summary['unread_seller']++; }
                if ($sender === 'client') {
                    $cid = (int)($m['sender_id'] ?? 0);
                    if ($cid > 0) { $summary['client_id'] = $cid; }
                    $cname = trim((string)($m['sender_name'] ?? ''));
                    if ($cname !== '') { $summary['client_username'] = $cname; }
                    $cicon = trim((string)($m['sender_icon'] ?? ''));
                    if ($cicon !== '') { $summary['client_icon'] = $cicon; }
                }
                $text = trim(strip_tags((string)($m['raw'] ?? $m['message'] ?? $m['content'] ?? '')));
                if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image') { $text = $text !== '' ? '[Image] ' . $text : '[Image]'; }
                $time = 0;
                if (!empty($m['time']) && is_numeric($m['time'])) { $time = (int)$m['time']; }
                elseif (!empty($m['created_at'])) { $time = (int)strtotime((string)$m['created_at']); }
                $summary['count']++;
                if ($time >= (int)$summary['last_message_at']) { $summary['last_message_at'] = $time; $summary['last_body'] = $text; }
            }
            return $summary;
        };

        $conversations = [];

        $accounts = $db->run(
            "SELECT a.id, a.title, a.client_id, a.sold, c.username AS client_username, c.icon AS client_icon
             FROM selling_accounts a
             LEFT JOIN clients c ON c.id = a.client_id
             WHERE a.seller_id = ?
             ORDER BY a.id DESC",
            $seller_id
        ) ?: [];
        foreach ($accounts as $account) {
            $account_id = (int)($account['id'] ?? 0);
            if ($account_id <= 0) { continue; }
            $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_account_' . $account_id) . '.json';
            $sum = $read_chat_summary($chat_path, 'seller:account:' . $account_id, 'account', $account_id);
            if (!$sum['exists'] && $sum['count'] < 1) { continue; }
            $client_id = (int)($account['client_id'] ?? 0);
            if ($client_id <= 0 && (int)($sum['client_id'] ?? 0) > 0) { $client_id = (int)$sum['client_id']; }
            $client_username = trim((string)($account['client_username'] ?? ''));
            $client_icon = trim((string)($account['client_icon'] ?? ''));
            if (($client_username === '' || $client_id !== (int)($account['client_id'] ?? 0)) && $client_id > 0) {
                $client_row = db_get_row('clients', ['id' => $client_id, 'select' => 'username, icon']);
                if (!empty($client_row['username'])) { $client_username = $client_row['username']; }
                if (!empty($client_row['icon'])) { $client_icon = $client_row['icon']; }
            }
            if ($client_username === '' && !empty($sum['client_username'])) { $client_username = $sum['client_username']; }
            if ($client_icon === '' && !empty($sum['client_icon'])) { $client_icon = $sum['client_icon']; }
            if ($client_username === '') { $client_username = $client_id > 0 ? ('Guest#' . $client_id) : 'Buyer'; }
            $is_paid_order = ((int)($account['sold'] ?? 0) === 1 && (int)($account['client_id'] ?? 0) > 0);
            $conversations[] = [
                'id' => 'account-' . $account_id,
                'kind' => 'account',
                'kind_label' => $is_paid_order ? 'Account' : 'Client Request',
                'request_status' => $is_paid_order ? 'paid' : 'request',
                'ref_id' => $account_id,
                'title' => $account['title'] ?? ('Account #' . $account_id),
                'client_id' => $client_id,
                'client_username' => $client_username,
                'client_icon' => $client_icon,
                'last_body' => $sum['last_body'],
                'last_message_at' => $sum['last_message_at'],
                'unread_seller' => $sum['unread_seller'],
                'url' => BASE_URL . '/seller-area/chat/account/' . $account_id,
                'source_url' => BASE_URL . '/seller-area/account/' . $account_id,
            ];
        }

        $items = $db->run(
            "SELECT sip.id, sip.item_id, sip.client_id, si.title AS item_title, c.username AS client_username, c.icon AS client_icon
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN clients c ON c.id = sip.client_id
             WHERE sip.seller_id = ? AND sip.client_id IS NOT NULL AND sip.client_id <> 0
             ORDER BY sip.id DESC",
            $seller_id
        ) ?: [];
        foreach ($items as $purchase) {
            $purchase_id = (int)($purchase['id'] ?? 0);
            if ($purchase_id <= 0) { continue; }
            $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_item_purchase_' . $purchase_id) . '.json';
            $sum = $read_chat_summary($chat_path, 'seller:item_purchase:' . $purchase_id, 'item_purchase', $purchase_id);
            if (!$sum['exists'] && $sum['count'] < 1) { continue; }
            $conversations[] = [
                'id' => 'item-' . $purchase_id,
                'kind' => 'item',
                'kind_label' => 'Item',
                'ref_id' => $purchase_id,
                'title' => $purchase['item_title'] ?? ('Item Order #' . $purchase_id),
                'client_id' => (int)($purchase['client_id'] ?? 0),
                'client_username' => $purchase['client_username'] ?? ('Guest#' . (int)($purchase['client_id'] ?? 0)),
                'client_icon' => $purchase['client_icon'] ?? '',
                'last_body' => $sum['last_body'],
                'last_message_at' => $sum['last_message_at'],
                'unread_seller' => $sum['unread_seller'],
                'url' => BASE_URL . '/seller-area/chat/item/' . $purchase_id,
                'source_url' => BASE_URL . '/seller-area/item-order/' . $purchase_id,
            ];
        }


        $topup_purchases = [];
        try {
            $topup_purchases = $db->run(
                "SELECT p.id, p.client_id, p.offer_title, p.game_name, p.created_at,
                        st.offer_title AS listing_offer_title,
                        g.name AS db_game_name,
                        c.username AS client_username, c.icon AS client_icon
                 FROM selling_topup_purchases p
                 LEFT JOIN selling_topups st ON st.id = p.topup_id
                 LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                 LEFT JOIN clients c ON c.id = p.client_id
                 WHERE p.seller_id = ? AND p.client_id IS NOT NULL AND p.client_id <> 0
                 ORDER BY p.id DESC",
                $seller_id
            ) ?: [];
        } catch (Throwable $e) { $topup_purchases = []; }
        foreach ($topup_purchases as $purchase) {
            $purchase_id = (int)($purchase['id'] ?? 0);
            if ($purchase_id <= 0) { continue; }
            $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_topup_purchase_' . $purchase_id) . '.json';
            $sum = $read_chat_summary($chat_path, 'seller:topup_purchase:' . $purchase_id, 'topup_purchase', $purchase_id);
            if (!$sum['exists'] && $sum['count'] < 1) { continue; }
            $title = trim((string)($purchase['offer_title'] ?? ''));
            if ($title === '') { $title = trim((string)($purchase['listing_offer_title'] ?? '')); }
            if ($title === '') { $title = 'Top Up Order #' . $purchase_id; }
            $game_name = trim((string)($purchase['game_name'] ?? ''));
            if ($game_name === '') { $game_name = trim((string)($purchase['db_game_name'] ?? '')); }
            if ($game_name !== '') { $title .= ' · ' . $game_name; }
            $conversations[] = [
                'id' => 'topup-' . $purchase_id,
                'kind' => 'topup',
                'kind_label' => 'Top Up',
                'request_status' => 'paid',
                'ref_type' => 'topup_purchase',
                'ref_id' => $purchase_id,
                'title' => $title,
                'client_id' => (int)($purchase['client_id'] ?? 0),
                'client_username' => $purchase['client_username'] ?? ('Guest#' . (int)($purchase['client_id'] ?? 0)),
                'client_icon' => $purchase['client_icon'] ?? '',
                'last_body' => $sum['last_body'],
                'last_message_at' => $sum['last_message_at'],
                'unread_seller' => $sum['unread_seller'],
                'url' => BASE_URL . '/seller-area/chat/topup/' . $purchase_id,
                'source_url' => BASE_URL . '/seller-area/top-up-order/' . $purchase_id,
            ];
        }

        // Digital Goods pre-purchase seller chats
        // Client side stores these chats in:
        // public/uploads/private/chat/selling_{sha1('digital_good_' . listing_id)}.json
        // The seller inbox did not load them before, so the client could see the DG chat
        // while the seller inbox showed 0 Digital Goods conversations.
        $digital_goods = $db->run(
            "SELECT dg.id, dg.title, dg.brand, dg.slug, dg.seller_id
             FROM digital_goods dg
             WHERE dg.seller_id = ?
             ORDER BY dg.id DESC",
            $seller_id
        ) ?: [];
        foreach ($digital_goods as $dg) {
            $dg_id = (int)($dg['id'] ?? 0);
            if ($dg_id <= 0) { continue; }
            $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('digital_good_' . $dg_id) . '.json';
            $sum = $read_chat_summary($chat_path, 'seller:digital_good:' . $dg_id, 'digital_good', $dg_id);
            if (!$sum['exists'] && $sum['count'] < 1) { continue; }

            $client_id = (int)($sum['client_id'] ?? 0);
            $client_username = trim((string)($sum['client_username'] ?? ''));
            $client_icon = trim((string)($sum['client_icon'] ?? ''));
            if ($client_id > 0 && ($client_username === '' || $client_icon === '')) {
                $client_row = db_get_row('clients', ['id' => $client_id, 'select' => 'username, icon']);
                if (!empty($client_row['username'])) { $client_username = $client_row['username']; }
                if (!empty($client_row['icon'])) { $client_icon = $client_row['icon']; }
            }
            if ($client_username === '') { $client_username = $client_id > 0 ? ('Guest#' . $client_id) : 'Buyer'; }

            $dg_title = trim((string)($dg['title'] ?? ''));
            if ($dg_title === '') { $dg_title = 'Digital Good #' . $dg_id; }

            $conversations[] = [
                'id' => 'digital-good-' . $dg_id,
                'kind' => 'digital_good',
                'kind_label' => 'DG',
                'request_status' => 'request',
                'ref_type' => 'digital_good',
                'ref_id' => $dg_id,
                'title' => $dg_title,
                'client_id' => $client_id,
                'client_username' => $client_username,
                'client_icon' => $client_icon,
                'last_body' => $sum['last_body'],
                'last_message_at' => $sum['last_message_at'],
                'unread_seller' => $sum['unread_seller'],
                'url' => BASE_URL . '/seller-area/chat/digital-good/' . $dg_id,
                'source_url' => BASE_URL . '/digital-goods/' . ($dg['slug'] ?: $dg_id),
            ];
        }


        $random_booster_chat_summary = function(string $chat_file) use ($client_id, $read_chat_summary) {
            $base = basename($chat_file, '.json');
            if (strpos($base, 'selling_') === 0) return null;
            $data = json_decode(@file_get_contents($chat_file) ?: '', true);
            if (!is_array($data)) return null;
            $messages = (isset($data['messages']) && is_array($data['messages'])) ? array_values($data['messages']) : array_values($data);
            if (empty($messages)) return null;
            $client_username = defined('CLIENT_DATA') && is_array(CLIENT_DATA) ? strtolower(trim((string)(CLIENT_DATA['username'] ?? ''))) : '';
            $belongs = ((int)($data['client_id'] ?? $data['user_id'] ?? 0) === $client_id);
            $senderOf = function(array $m): string { $s = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? ''))); $t = strtolower(trim((string)($m['type'] ?? ''))); if ($s === '' && in_array($t, ['client','booster','seller','admin','system'], true)) $s = $t; return $s; };
            $timeOf = function(array $m, int $i): int { if (!empty($m['time']) && is_numeric($m['time'])) return (int)$m['time']; if (!empty($m['created_at'])) { $ts = strtotime((string)$m['created_at']); if ($ts !== false) return (int)$ts; } if (!empty($m['timestamp']) && is_numeric($m['timestamp'])) return (int)$m['timestamp']; return $i; };
            $bodyOf = function(array $m): string { $body = trim(strip_tags((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? $m['raw'] ?? ''))); if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image') return $body !== '' ? '[Image] ' . $body : '[Image]'; return $body !== '' ? $body : 'No message yet'; };
            foreach ($messages as $m) { if (!is_array($m) || !empty($m['deleted']) || $senderOf($m) !== 'client') continue; $mid = (int)($m['sender_id'] ?? $m['client_id'] ?? $m['user_id'] ?? 0); $mname = strtolower(trim((string)($m['sender_name'] ?? $m['username'] ?? $m['name'] ?? ''))); if ($mid === $client_id || ($client_username !== '' && $mname !== '' && $mname === $client_username)) { $belongs = true; break; } }
            if (!$belongs) return null;
            $last = null; $last_at = 0; $unread = 0;
            foreach ($messages as $i => $m) { if (!is_array($m) || !empty($m['deleted'])) continue; $sender=$senderOf($m); $t=$timeOf($m,$i+1); if ($t >= $last_at) { $last_at=$t; $last=$m; } if (in_array($sender, ['booster','admin','seller'], true)) { if (array_key_exists('seen_by_client',$m)) $is_unread=((int)$m['seen_by_client']===0); elseif (array_key_exists('is_read',$m)) $is_unread=((int)$m['is_read']===0); elseif (array_key_exists('seen',$m)) $is_unread=((int)$m['seen']===0); else $is_unread=false; if ($is_unread) $unread++; } }
            return ['chat_key'=>$base, 'order_id'=>(int)($data['order_id'] ?? $data['ref_id'] ?? $data['id'] ?? 0), 'last'=>$last, 'last_body'=>$last ? $bodyOf($last) : 'No booster message yet', 'last_at'=>$last_at, 'unread'=>$unread];
        };
        if (is_dir($chat_dir)) {
            $existing = [];
            foreach ($conversations as $c) { if (($c['chat_type'] ?? '') === 'booster' || ($c['ref_type'] ?? '') === 'booster_order') { $existing[(string)($c['ref_id'] ?? 0)] = true; if (!empty($c['chat_key'])) $existing[(string)$c['chat_key']] = true; } }
            foreach (glob($chat_dir . '/*.json') ?: [] as $chat_file) {
                $sum = $random_booster_chat_summary($chat_file);
                if (!$sum) continue;
                $order_id = (int)$sum['order_id']; $chat_key = (string)$sum['chat_key'];
                if (($order_id > 0 && isset($existing[(string)$order_id])) || isset($existing[$chat_key])) continue;
                $order = null; $booster_name = 'Booster Chat'; $booster_icon = ''; $title = 'Boosting Chat'; $source_url = '';
                if ($order_id > 0) { $order = $db->row("SELECT o.id, o.client_id, o.form_id, o.status, o.created_at, o.paid_at, o.booster_id, b.username AS booster_username, b.icon AS booster_icon, bf.name AS form_name, bf.type AS form_type FROM orders o LEFT JOIN boosters b ON b.id=o.booster_id LEFT JOIN boost_forms bf ON bf.id=o.form_id WHERE o.id=? AND o.client_id=? LIMIT 1", $order_id, $client_id); }
                if (!empty($order)) { $booster_name = trim((string)($order['booster_username'] ?? '')) ?: (((int)($order['booster_id'] ?? 0)>0) ? 'Booster' : 'Booster not assigned'); $booster_icon=(string)($order['booster_icon'] ?? ''); $title=trim((string)($order['form_name'] ?? $order['form_type'] ?? '')) ?: ('Boosting Order #' . $order_id); $source_url=BASE_URL . '/profile/orders/' . $order_id; }
                $conversations[] = ['id'=>'booster-raw-'.$chat_key,'kind'=>'booster_order','kind_label'=>'BOOSTER CHAT','chat_type'=>'booster','seller_id'=>0,'seller_username'=>$booster_name,'seller_icon'=>$booster_icon,'request_status'=>'paid','ref_type'=>'booster_order','ref_id'=>$order_id,'chat_key'=>$chat_key,'title'=>$title,'last_body'=>$sum['last_body'],'last_message_at'=>(int)$sum['last_at'],'unread_client'=>(int)$sum['unread'],'source_url'=>$source_url,'source_label'=>$order_id > 0 ? 'View Order' : 'Boosting Chat'];
            }
        }

        usort($conversations, function ($a, $b) { return ((int)($b['last_message_at'] ?? 0)) <=> ((int)($a['last_message_at'] ?? 0)); });
        $meta = ['title' => 'Chat Inbox | LoLBoost', 'h1' => 'Chat Inbox'];
        view_file('seller/pages/chat/inbox', compact('meta', 'seller_data', 'conversations'));
    });

    $router->get('chat/account/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id = (int)$id;
        $account = $db->row(
            "SELECT a.*, c.username AS client_username, c.email AS client_email, c.icon AS client_icon, c.icon AS client_avatar
             FROM selling_accounts a
             LEFT JOIN clients c ON c.id = a.client_id
             WHERE a.id = ? AND a.seller_id = ? LIMIT 1",
            $id, (int)$seller_data['id']
        );
        if (empty($account)) { redirect_url('seller-area/chat'); return; }
        $client_id = (int)($account['client_id'] ?? 0);
        $client_username = trim((string)($account['client_username'] ?? ''));
        $client_email = trim((string)($account['client_email'] ?? ''));
        $client_icon = trim((string)($account['client_icon'] ?? ''));
        if ($client_id <= 0 || $client_username === '' || $client_icon === '') {
            $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_account_' . $id) . '.json';
            if (is_file($chat_path)) {
                $chat_data = json_decode(@file_get_contents($chat_path) ?: '', true);
                if (is_array($chat_data) && !empty($chat_data['messages']) && is_array($chat_data['messages'])) {
                    foreach (array_reverse($chat_data['messages']) as $m) {
                        if (!is_array($m) || !empty($m['deleted'])) { continue; }
                        $sender = (string)($m['sender'] ?? $m['type'] ?? '');
                        if ($sender !== 'client') { continue; }
                        if ($client_id <= 0) { $client_id = (int)($m['sender_id'] ?? 0); }
                        if ($client_username === '') { $client_username = trim((string)($m['sender_name'] ?? '')); }
                        if ($client_icon === '') { $client_icon = trim((string)($m['sender_icon'] ?? '')); }
                        break;
                    }
                }
            }
            if ($client_id > 0 && ($client_username === '' || $client_email === '' || $client_icon === '')) {
                $client_row = db_get_row('clients', ['id' => $client_id, 'select' => 'username, email, icon']);
                if (!empty($client_row['username'])) { $client_username = $client_row['username']; }
                if (!empty($client_row['email'])) { $client_email = $client_row['email']; }
                if (!empty($client_row['icon'])) { $client_icon = $client_row['icon']; }
            }
        }
        if ($client_username === '') { $client_username = $client_id > 0 ? ('Guest#' . $client_id) : 'Buyer'; }
        $client = ['id' => $client_id, 'username' => $client_username, 'email' => $client_email, 'icon' => $client_icon];
        $is_paid_order = ((int)($account['sold'] ?? 0) === 1 && (int)($account['client_id'] ?? 0) > 0);
        $conversation = ['kind' => 'account', 'kind_label' => $is_paid_order ? 'Account' : 'Client Request', 'request_status' => $is_paid_order ? 'paid' : 'request', 'ref_id' => $id, 'title' => $account['title'] ?? ('Account #' . $id), 'source_url' => BASE_URL . '/seller-area/account/' . $id, 'load_action' => 'seller_account_chat_load', 'send_action' => 'seller_account_chat_send', 'id_field' => 'account_id', 'id_value' => $id];
        $meta = ['title' => 'Chat with ' . ($client['username'] ?? 'Client') . ' | LoLBoost'];
        view_file('seller/pages/chat/view', compact('meta', 'seller_data', 'conversation', 'client'));
    });

    $router->get('chat/item/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id = (int)$id;
        $purchase = $db->row(
            "SELECT sip.*, si.title AS item_title, c.username AS client_username, c.email AS client_email, c.icon AS client_icon, c.icon AS client_avatar
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN clients c ON c.id = sip.client_id
             WHERE sip.id = ? AND sip.seller_id = ? LIMIT 1",
            $id, (int)$seller_data['id']
        );
        if (empty($purchase)) { redirect_url('seller-area/chat'); return; }
        $client = ['id' => (int)($purchase['client_id'] ?? 0), 'username' => $purchase['client_username'] ?? ('Guest#' . (int)($purchase['client_id'] ?? 0)), 'email' => $purchase['client_email'] ?? '', 'icon' => $purchase['client_icon'] ?? ''];
        $conversation = ['kind' => 'item', 'kind_label' => 'Item', 'ref_id' => $id, 'title' => $purchase['item_title'] ?? ('Item Order #' . $id), 'source_url' => BASE_URL . '/seller-area/item-order/' . $id, 'load_action' => 'item_chat_load', 'send_action' => 'seller_item_chat_send', 'id_field' => 'purchase_id', 'id_value' => $id];
        $meta = ['title' => 'Chat with ' . ($client['username'] ?? 'Client') . ' | LoLBoost'];
        view_file('seller/pages/chat/view', compact('meta', 'seller_data', 'conversation', 'client'));
    });


    $router->get('chat/topup/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id = (int)$id;
        $purchase = $db->row(
            "SELECT p.*, c.username AS client_username, c.email AS client_email, c.icon AS client_icon,
                    st.offer_title AS listing_offer_title, g.name AS db_game_name
             FROM selling_topup_purchases p
             LEFT JOIN selling_topups st ON st.id = p.topup_id
             LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
             LEFT JOIN clients c ON c.id = p.client_id
             WHERE p.id = ? AND p.seller_id = ? LIMIT 1",
            $id, (int)$seller_data['id']
        );
        if (empty($purchase)) { redirect_url('seller-area/chat'); return; }

        $title = trim((string)($purchase['offer_title'] ?? ''));
        if ($title === '') { $title = trim((string)($purchase['listing_offer_title'] ?? '')); }
        if ($title === '') { $title = 'Top Up Order #' . $id; }
        $game_name = trim((string)($purchase['game_name'] ?? ''));
        if ($game_name === '') { $game_name = trim((string)($purchase['db_game_name'] ?? '')); }
        if ($game_name !== '') { $title .= ' · ' . $game_name; }

        $client_id = (int)($purchase['client_id'] ?? 0);
        $client = [
            'id' => $client_id,
            'username' => trim((string)($purchase['client_username'] ?? '')) ?: ($client_id > 0 ? ('Guest#' . $client_id) : 'Buyer'),
            'email' => (string)($purchase['client_email'] ?? ''),
            'icon' => (string)($purchase['client_icon'] ?? ''),
        ];
        $conversation = [
            'kind' => 'topup',
            'kind_label' => 'Top Up',
            'ref_id' => $id,
            'title' => $title,
            'source_url' => BASE_URL . '/seller-area/top-up-order/' . $id,
            'load_action' => 'topup_chat_load',
            'send_action' => 'seller_topup_chat_send',
            'id_field' => 'purchase_id',
            'id_value' => $id,
        ];
        $meta = ['title' => 'Chat with ' . ($client['username'] ?? 'Client') . ' | LoLBoost'];
        view_file('seller/pages/chat/view', compact('meta', 'seller_data', 'conversation', 'client'));
    });

    $router->get('chat/digital-good/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id = (int)$id;
        $dg = $db->row(
            "SELECT * FROM digital_goods WHERE id = ? AND seller_id = ? LIMIT 1",
            $id, (int)$seller_data['id']
        );
        if (empty($dg)) { redirect_url('seller-area/chat'); return; }

        $client_id = 0;
        $client_username = '';
        $client_email = '';
        $client_icon = '';
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('digital_good_' . $id) . '.json';
        if (is_file($chat_path)) {
            $chat_data = json_decode(@file_get_contents($chat_path) ?: '', true);
            if (is_array($chat_data) && !empty($chat_data['messages']) && is_array($chat_data['messages'])) {
                foreach (array_reverse($chat_data['messages']) as $m) {
                    if (!is_array($m) || !empty($m['deleted'])) { continue; }
                    $sender = (string)($m['sender'] ?? $m['sender_type'] ?? $m['type'] ?? '');
                    if ($sender !== 'client') { continue; }
                    if ($client_id <= 0) { $client_id = (int)($m['sender_id'] ?? $m['client_id'] ?? 0); }
                    if ($client_username === '') { $client_username = trim((string)($m['sender_name'] ?? $m['username'] ?? '')); }
                    if ($client_icon === '') { $client_icon = trim((string)($m['sender_icon'] ?? '')); }
                    break;
                }
            }
        }
        if ($client_id > 0 && ($client_username === '' || $client_email === '' || $client_icon === '')) {
            $client_row = db_get_row('clients', ['id' => $client_id, 'select' => 'username, email, icon']);
            if (!empty($client_row['username'])) { $client_username = $client_row['username']; }
            if (!empty($client_row['email'])) { $client_email = $client_row['email']; }
            if (!empty($client_row['icon'])) { $client_icon = $client_row['icon']; }
        }
        if ($client_username === '') { $client_username = $client_id > 0 ? ('Guest#' . $client_id) : 'Buyer'; }

        $client = ['id' => $client_id, 'username' => $client_username, 'email' => $client_email, 'icon' => $client_icon];
        $conversation = [
            'kind' => 'digital_good',
            'kind_label' => 'DG',
            'request_status' => 'request',
            'ref_id' => $id,
            'ref_type' => 'digital_good',
            'title' => $dg['title'] ?? ('Digital Good #' . $id),
            'source_url' => BASE_URL . '/digital-goods/' . (($dg['slug'] ?? '') ?: $id),
            'load_action' => 'seller_direct_chat_load',
            'send_action' => 'seller_direct_chat_send',
            'id_field' => 'ref_id',
            'id_value' => $id,
            'extra_fields' => ['ref_type' => 'digital_good'],
        ];
        $meta = ['title' => 'Chat with ' . ($client['username'] ?? 'Client') . ' | LoLBoost'];
        view_file('seller/pages/chat/view', compact('meta', 'seller_data', 'conversation', 'client'));
    });

    $router->get('accounts', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        $accounts = $db->run(
            "SELECT a.*, c.username AS buyer_username, c.icon AS buyer_icon
              FROM selling_accounts a
              LEFT JOIN clients c ON c.id = a.client_id
              WHERE a.seller_id = ?
              ORDER BY a.created_at DESC",
            (int)$seller_data['id']
        ) ?: [];

        $meta = ['title' => 'My Accounts | LoLBoost'];
        view_file('seller/pages/accounts/list', compact('meta', 'seller_data', 'accounts'));
    });

    $router->get('import-accounts', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        if (function_exists('seller_api_ensure_tables')) { seller_api_ensure_tables(); }

        $seller_id = (int)($seller_data['id'] ?? 0);
        $selected_id = (int)($_GET['id'] ?? 0);
        $selected_batch = null;
        $import_rows = [];
        $import_batches = [];
        $gallery_lookup = [];

        try {
            $import_batches = $db->run("SELECT * FROM seller_account_import_batches WHERE seller_id = ? ORDER BY id DESC LIMIT 50", $seller_id) ?: [];
            if ($selected_id > 0) {
                $selected_batch = $db->row("SELECT * FROM seller_account_import_batches WHERE id = ? AND seller_id = ? LIMIT 1", $selected_id, $seller_id);
                if (!empty($selected_batch)) {
                    $import_rows = $db->run("SELECT * FROM seller_account_import_rows WHERE batch_id = ? AND seller_id = ? ORDER BY id ASC LIMIT 1500", $selected_id, $seller_id) ?: [];
                    $allImageIds = [];
                    foreach ($import_rows as $r) {
                        $rids = json_decode((string)($r['image_ids'] ?? '[]'), true);
                        if (is_array($rids)) { $allImageIds = array_merge($allImageIds, $rids); }
                    }
                    $allImageIds = array_values(array_unique(array_filter(array_map('intval', $allImageIds))));
                    if (!empty($allImageIds) && function_exists('seller_gallery_images_by_ids')) {
                        $galleryRows = seller_gallery_images_by_ids($seller_id, $allImageIds);
                        foreach ($galleryRows as $gr) { $gallery_lookup[(int)$gr['id']] = $gr; }
                    }
                }
            }
        } catch (\Throwable $e) {
            $import_batches = [];
            $selected_batch = null;
            $import_rows = [];
        }

        $listing_games = [];
        try {
            $listing_games = $db->run("SELECT id, name, slug, icon FROM games WHERE status = 1 ORDER BY sort_order ASC, name ASC") ?: [];
        } catch (\Throwable $e) { $listing_games = []; }

        $meta = ['title' => 'Import Accounts | Seller Area | LoLBoost.gg'];
        view_file('seller/pages/import-accounts', compact('meta', 'seller_data', 'import_batches', 'selected_batch', 'import_rows', 'gallery_lookup', 'listing_games'));
    });

    $sellerListingImportRoute = function (string $entity) use ($router) {
        $slug = $entity === 'topups' ? 'import-top-ups' : 'import-items';
        $router->get($slug, function () use ($entity) {
            global $is_seller, $seller_data, $db;
            if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
            $seller_id = (int)($seller_data['id'] ?? 0);
            if (method_exists('Ajax', 'seller_listing_import_ensure_tables')) { /* tables are also ensured by AJAX */ }
            try {
                $db->run("CREATE TABLE IF NOT EXISTS seller_listing_import_batches (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,seller_id INT UNSIGNED NOT NULL,entity_type VARCHAR(20) NOT NULL,game_slug VARCHAR(191) NULL,import_name VARCHAR(191) NULL,status VARCHAR(30) NOT NULL DEFAULT 'draft',total_rows INT UNSIGNED NOT NULL DEFAULT 0,created_count INT UNSIGNED NOT NULL DEFAULT 0,updated_count INT UNSIGNED NOT NULL DEFAULT 0,skipped_count INT UNSIGNED NOT NULL DEFAULT 0,failed_count INT UNSIGNED NOT NULL DEFAULT 0,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,KEY idx_slie_seller_entity (seller_id,entity_type)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $cols=$db->run("SHOW COLUMNS FROM seller_listing_import_batches LIKE 'game_slug'"); if(!$cols){$db->run("ALTER TABLE seller_listing_import_batches ADD COLUMN game_slug VARCHAR(191) NULL AFTER entity_type");}
                try { $db->run("UPDATE seller_listing_import_batches b SET b.game_slug=(SELECT JSON_UNQUOTE(JSON_EXTRACT(r.row_data,'$.game')) FROM seller_listing_import_rows r WHERE r.batch_id=b.id AND r.seller_id=b.seller_id AND JSON_UNQUOTE(JSON_EXTRACT(r.row_data,'$.game')) IS NOT NULL LIMIT 1) WHERE (b.game_slug IS NULL OR b.game_slug='')"); } catch (Throwable $e) {}
                $db->run("CREATE TABLE IF NOT EXISTS seller_listing_import_rows (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,batch_id INT UNSIGNED NOT NULL,seller_id INT UNSIGNED NOT NULL,entity_type VARCHAR(20) NOT NULL,external_id VARCHAR(191) NULL,row_data LONGTEXT NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'pending',message TEXT NULL,listing_id INT UNSIGNED NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,KEY idx_slir_batch (batch_id,seller_id),KEY idx_slir_external (seller_id,entity_type,external_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (Throwable $e) {}
            $selected_id = (int)($_GET['id'] ?? 0);
            $import_batches = $db->run("SELECT b.*,g.name AS resolved_game_name,g.slug AS resolved_game_slug,g.icon AS resolved_game_icon FROM seller_listing_import_batches b LEFT JOIN games g ON CONVERT(g.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(b.game_slug USING utf8mb4) COLLATE utf8mb4_unicode_ci WHERE b.seller_id=? AND b.entity_type=? ORDER BY b.id DESC LIMIT 50", $seller_id, $entity) ?: [];
            $selected_batch = $selected_id ? $db->row("SELECT b.*,g.name AS resolved_game_name,g.slug AS resolved_game_slug,g.icon AS resolved_game_icon FROM seller_listing_import_batches b LEFT JOIN games g ON CONVERT(g.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(b.game_slug USING utf8mb4) COLLATE utf8mb4_unicode_ci WHERE b.id=? AND b.seller_id=? AND b.entity_type=? LIMIT 1", $selected_id, $seller_id, $entity) : null;
            $import_rows = $selected_batch ? ($db->run("SELECT * FROM seller_listing_import_rows WHERE batch_id=? AND seller_id=? ORDER BY id ASC LIMIT 2000", $selected_id, $seller_id) ?: []) : [];
            $listing_games = [];
            try {
                $schemaTable = $entity === 'topups' ? 'game_topup_schemas' : 'game_item_schemas';
                $listing_games = $db->run("SELECT COALESCE(g.id,0) AS id,COALESCE(g.name,JSON_UNQUOTE(JSON_EXTRACT(s.schema_json,'$.game_name')),REPLACE(s.game_slug,'-',' ')) AS name,s.game_slug AS slug,COALESCE(g.icon,JSON_UNQUOTE(JSON_EXTRACT(s.schema_json,'$.icon_path'))) AS icon FROM {$schemaTable} s LEFT JOIN games g ON CONVERT(g.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(s.game_slug USING utf8mb4) COLLATE utf8mb4_unicode_ci WHERE s.enabled=1 AND (g.id IS NULL OR g.status=1) ORDER BY COALESCE(g.sort_order,999999) ASC,name ASC") ?: [];
                if (!$listing_games) $listing_games = $db->run("SELECT id,name,slug,icon FROM games WHERE status=1 ORDER BY sort_order ASC,name ASC") ?: [];
            } catch (Throwable $e) { $listing_games = []; }
            $meta = ['title' => 'Import ' . ($entity === 'topups' ? 'Top Ups' : 'Items') . ' | Seller Area | LoLBoost.gg'];
            view_file('seller/pages/import-listings', compact('meta','seller_data','entity','import_batches','selected_batch','import_rows','listing_games'));
        });
    };
    $sellerListingImportRoute('items');
    $sellerListingImportRoute('topups');

    $router->get('accounts/add', function () {
        global $is_seller, $seller_data;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $meta = ['title' => 'List Account | LoLBoost'];
        view_file('seller/pages/accounts/add', compact('meta', 'seller_data'));
    });

    $router->get('account-orders', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        $seller_id = (int)($seller_data['id'] ?? 0);
        $orders = $db->run(
            "SELECT a.*, c.username AS buyer_username, c.icon AS buyer_icon
             FROM selling_accounts a
             LEFT JOIN clients c ON c.id = a.client_id
             WHERE a.seller_id = ? AND a.sold = 1 AND a.client_id IS NOT NULL
             ORDER BY a.created_at DESC",
            $seller_id
        ) ?: [];

        $meta = ['title' => 'Account Orders | Seller Area | LoLBoost.gg'];
        view_file('seller/pages/accounts/orders', compact('meta', 'seller_data', 'orders'));
    });

    $router->get('account-order/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id = (int)$id;
        $account = $db->row(
            "SELECT * FROM selling_accounts WHERE id = ? AND seller_id = ? AND sold IN (1,2) LIMIT 1",
            $id, (int)$seller_data['id']
        );
        if (empty($account)) { redirect_url('seller-area/account-orders'); return; }

        $buyer = null;
        if (!empty($account['client_id'])) {
            $buyer = $db->row(
                "SELECT id, username, email FROM clients WHERE id = ? LIMIT 1",
                (int)$account['client_id']
            );
        }

        $chat_messages = [];
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_account_' . $id) . '.json';
        if (is_file($chat_path)) {
            $raw = file_get_contents($chat_path);
            $chat_data = json_decode($raw, true);
            if (is_array($chat_data) && isset($chat_data['messages'])) {
                $chat_messages = array_values(array_filter($chat_data['messages'], fn($m) => empty($m['deleted'])));
            }
        }

        $meta = ['title' => htmlspecialchars($account['title']) . ' | Account Order | LoLBoost'];
        view_file('seller/pages/accounts/view', compact('meta', 'seller_data', 'account', 'buyer', 'chat_messages'));
    });

    $router->get('account/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id      = (int)$id;
        $account = $db->row(
            "SELECT * FROM selling_accounts WHERE id = ? AND seller_id = ? LIMIT 1",
            $id, (int)$seller_data['id']
        );
        if (empty($account)) { redirect_url('seller-area/accounts'); return; }

        // Load buyer info if sold
        $buyer = null;
        if (!empty($account['client_id'])) {
            $buyer = $db->row(
                "SELECT id, username, email FROM clients WHERE id = ? LIMIT 1",
                (int)$account['client_id']
            );
        }

        // Load chat messages from JSON file
        $chat_messages = [];
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_account_' . $id) . '.json';
        if (is_file($chat_path)) {
            $raw = file_get_contents($chat_path);
            $chat_data = json_decode($raw, true);
            if (is_array($chat_data) && isset($chat_data['messages'])) {
                $chat_messages = array_values(array_filter($chat_data['messages'], fn($m) => empty($m['deleted'])));
            }
        }

        $meta = ['title' => htmlspecialchars($account['title']) . ' | LoLBoost'];
        view_file('seller/pages/accounts/view', compact('meta', 'seller_data', 'account', 'buyer', 'chat_messages'));
    });

    $router->get('payout', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        // Load saved payout methods (auto-create table on first use)
        try {
            $db->run("CREATE TABLE IF NOT EXISTS seller_payout_methods (
                id INT AUTO_INCREMENT PRIMARY KEY, seller_id INT NOT NULL, method VARCHAR(32) NOT NULL,
                details TEXT, is_default TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX(seller_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $methods = $db->run("SELECT * FROM seller_payout_methods WHERE seller_id=? ORDER BY is_default DESC, id DESC", (int)$seller_data['id']) ?: [];
        } catch (Throwable $e) { $methods = []; }

        $meta = ['title' => 'Payout Settings | LoLBoost'];
        view_file('seller/pages/payout', compact('meta', 'seller_data', 'methods'));
    });

    $router->get('payout-requests', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        try {
            $db->run("CREATE TABLE IF NOT EXISTS seller_payout_methods (
                id INT AUTO_INCREMENT PRIMARY KEY, seller_id INT NOT NULL, method VARCHAR(32) NOT NULL,
                details TEXT, is_default TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX(seller_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $methods = $db->run("SELECT * FROM seller_payout_methods WHERE seller_id=? ORDER BY is_default DESC, id DESC", (int)$seller_data['id']) ?: [];
        } catch (Throwable $e) { $methods = []; }

        try {
            $db->run("CREATE TABLE IF NOT EXISTS seller_payout_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                seller_id INT NOT NULL,
                amount_cents INT NOT NULL DEFAULT 0,
                method VARCHAR(32) NOT NULL DEFAULT 'bank_transfer',
                details TEXT NULL,
                status VARCHAR(16) NOT NULL DEFAULT 'PENDING',
                admin_note TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX(seller_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $requests = $db->run(
                "SELECT * FROM seller_payout_requests WHERE seller_id = ? ORDER BY id DESC",
                (int)$seller_data['id']
            ) ?: [];
        } catch (Throwable $e) { $requests = []; }

        $meta = ['title' => 'Payout Requests | LoLBoost'];
        view_file('seller/pages/payout-requests', compact('meta', 'seller_data', 'methods', 'requests'));
    });

    $router->get('settings', function () {
        redirect_url('seller-area/profile');
    });

    $router->get('profile', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        try { $db->run("ALTER TABLE sellers ADD COLUMN IF NOT EXISTS allow_chat_requests TINYINT(1) NOT NULL DEFAULT 1"); } catch (\Throwable $e) {}
        $meta = ['title' => 'My Profile | LoLBoost'];
        view_file('seller/pages/profile', compact('meta', 'seller_data'));
    });

    $router->get('referrals', function () {
        global $is_seller, $seller_data;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $meta = ['title' => 'Seller Referrals | LoLBoost'];
        view_file('seller/pages/referrals', compact('meta', 'seller_data'));
    });

    $router->get('personal-details', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }

        $seller_id = (int)$seller_data['id'];

        $requests = $db->run(
            "SELECT * FROM seller_payout_requests WHERE seller_id = ? ORDER BY id DESC",
            $seller_id
        ) ?: [];
        $payments = $db->run(
            "SELECT * FROM seller_payments WHERE seller_id = ? ORDER BY id DESC LIMIT 50",
            $seller_id
        ) ?: [];

        try {
            $db->run("CREATE TABLE IF NOT EXISTS seller_payout_methods (
                id INT AUTO_INCREMENT PRIMARY KEY, seller_id INT NOT NULL, method VARCHAR(32) NOT NULL,
                details TEXT, is_default TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX(seller_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $methods = $db->run(
                "SELECT * FROM seller_payout_methods WHERE seller_id=? ORDER BY is_default DESC, id DESC",
                $seller_id
            ) ?: [];
        } catch (\Throwable $e) { $methods = []; }

        $meta = ['title' => 'Personal Details | LoLBoost', 'h1' => 'Personal Details'];
        view_file('seller/pages/personal-details', compact('meta', 'seller_data', 'requests', 'payments', 'methods'));
    });

    // profile is an alias for settings — kept for backwards compat

    $router->get('payments', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $payments = $db->run(
            "SELECT * FROM seller_payments WHERE seller_id = ? ORDER BY id DESC",
            (int)$seller_data['id']
        ) ?: [];
        $meta = ['title' => 'Payments | LoLBoost'];
        view_file('seller/pages/payments', compact('meta', 'seller_data', 'payments'));
    });

    $router->get('rules', function () {
        global $is_seller, $seller_data;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $meta = ['title' => 'Seller Rules | LoLBoost'];
        view_file('seller/pages/rules', compact('meta', 'seller_data'));
    });

    $router->get('fines', function () {
        global $is_seller, $seller_data;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $meta = ['title' => 'Seller Fines | LoLBoost'];
        view_file('seller/pages/fines', compact('meta', 'seller_data'));
    });


    $router->get('top-ups', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        lb_topups_table_ensure();
        $topups = $db->run("SELECT st.*, g.icon AS game_icon, g.name AS db_game_name FROM selling_topups st LEFT JOIN games g ON g.id = st.game_id WHERE st.seller_id = ? ORDER BY st.created_at DESC", (int)$seller_data['id']) ?: [];
        $topupGames = [];
        try {
            $topupGames = $db->run("SELECT g.* FROM games g INNER JOIN game_services gs ON gs.game_id = g.id AND gs.service_type IN ('topups','top-ups','currencies') AND gs.status = 1 WHERE g.status = 1 ORDER BY g.sort_order ASC, g.name ASC") ?: [];
        } catch (Throwable $e) {
            try { $topupGames = $db->run("SELECT * FROM games WHERE status = 1 ORDER BY sort_order ASC, name ASC") ?: []; } catch (Throwable $e2) { $topupGames = []; }
        }
        $topupConfigs = [];
        $topupSchemas = [];
        foreach ($topupGames as $g) {
            $slug = (string)($g['slug'] ?? '');
            if ($slug !== '') {
                $topupConfigs[$slug] = lb_get_topups_page_config($slug);
                $topupSchemas[$slug] = lb_get_game_topup_schema($slug);
            }
        }
        $meta = ['title' => 'My Top Ups | LoLBoost'];
        view_file('seller/pages/topups/list', compact('meta', 'seller_data', 'topups', 'topupGames', 'topupConfigs', 'topupSchemas'));
    });

    $router->post('top-ups/create', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        lb_topups_table_ensure();
        $gameId = (int)($_POST['game_id'] ?? 0);
        $game = $gameId > 0 ? db_get_row('games', ['id' => $gameId]) : null;
        if (!$game) { redirect_url('seller-area/top-ups?error=game'); return; }
        $title = trim((string)($_POST['offer_title'] ?? ''));
        if ($title === '') { redirect_url('seller-area/top-ups?error=title'); return; }
        $amount = (float)str_replace(',', '.', (string)($_POST['offer_amount'] ?? 0));
        $unit = trim((string)($_POST['offer_unit'] ?? '')) ?: 'Points';
        $region = lb_topup_normalize_region($_POST['region'] ?? '', (string)($game['slug'] ?? ''));
        $platform = trim((string)($_POST['platform'] ?? ''));
        $priceRaw = str_replace(',', '.', (string)($_POST['price'] ?? '0'));
        $price = (int)round(((float)$priceRaw) * 100);
        $stock = max(0, (int)($_POST['stock'] ?? 999));
        $minQty = max(1, (int)($_POST['min_quantity'] ?? 1));
        $wVal = max(0, (int)($_POST['waiting_time_value'] ?? 10));
        $wUnit = strtolower(trim((string)($_POST['waiting_time_unit'] ?? 'minutes')));
        if (!in_array($wUnit, ['minutes','hours','days'], true)) $wUnit = 'minutes';
        $waitingMinutes = lb_topup_waiting_minutes($wVal, $wUnit);
        $offerKey = lb_topup_offer_key($title, $amount, $unit);
        $cfg = lb_get_topups_page_config((string)$game['slug']);
        $image = trim((string)($_POST['image'] ?? ''));
        if (isset($_FILES['topup_image']) && !empty($_FILES['topup_image']['tmp_name'])) {
            $uploadedTopupImage = false;
            if (function_exists('upload_image')) {
                $uploadedTopupImage = upload_image($_FILES['topup_image'], 'public/uploads/topups');
            } else {
                $uploadDir = SYS_PATH . '/public/uploads/topups';
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
                $ext = strtolower(pathinfo((string)($_FILES['topup_image']['name'] ?? ''), PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) { $ext = 'webp'; }
                $fileName = 'topup_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (@move_uploaded_file($_FILES['topup_image']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $uploadedTopupImage = '/public/uploads/topups/' . $fileName;
                }
            }
            if ($uploadedTopupImage !== false) { $image = $uploadedTopupImage; }
        }
        $instructions = trim((string)($_POST['instructions'] ?? ''));
        $active = (int)($_POST['active'] ?? 1) === 1 ? 1 : 0;
        $db->run("INSERT INTO selling_topups (seller_id, game_id, game_slug, game_name, service_label, offer_key, offer_title, offer_amount, offer_unit, region, platform, price, currency, stock, min_quantity, waiting_time_value, waiting_time_unit, waiting_time_minutes, instructions, image, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'EUR', ?, ?, ?, ?, ?, ?, ?, ?)",
            (int)$seller_data['id'], $gameId, (string)$game['slug'], (string)$game['name'], (string)($cfg['service_label'] ?? 'Top Up'), $offerKey, $title, $amount, $unit, $region, $platform, $price, $stock, $minQty, $wVal, $wUnit, $waitingMinutes, $instructions, $image, $active
        );
        redirect_url('seller-area/top-ups?created=1');
    });

    $router->post('top-ups/:id/update', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        lb_topups_table_ensure();
        $topupId = (int)$id;
        $existing = $db->row('SELECT id FROM selling_topups WHERE id = ? AND seller_id = ? LIMIT 1', $topupId, (int)$seller_data['id']);
        if (!$existing) { redirect_url('seller-area/top-ups?error=notfound'); return; }
        $gameId = (int)($_POST['game_id'] ?? 0);
        $game = $gameId > 0 ? db_get_row('games', ['id' => $gameId]) : null;
        if (!$game) { redirect_url('seller-area/top-ups?error=game'); return; }
        $title = trim((string)($_POST['offer_title'] ?? ''));
        if ($title === '') { redirect_url('seller-area/top-ups?error=title'); return; }
        $amount = (float)str_replace(',', '.', (string)($_POST['offer_amount'] ?? 0));
        $unit = trim((string)($_POST['offer_unit'] ?? '')) ?: 'Points';
        $region = lb_topup_normalize_region($_POST['region'] ?? '', (string)($game['slug'] ?? ''));
        $platform = trim((string)($_POST['platform'] ?? ''));
        $priceRaw = str_replace(',', '.', (string)($_POST['price'] ?? '0'));
        $price = max(0, (int)round(((float)$priceRaw) * 100));
        $stock = max(0, (int)($_POST['stock'] ?? 999));
        $minQty = max(1, (int)($_POST['min_quantity'] ?? 1));
        $wVal = max(0, (int)($_POST['waiting_time_value'] ?? 10));
        $wUnit = strtolower(trim((string)($_POST['waiting_time_unit'] ?? 'minutes')));
        if (!in_array($wUnit, ['minutes','hours','days'], true)) $wUnit = 'minutes';
        $waitingMinutes = lb_topup_waiting_minutes($wVal, $wUnit);
        $offerKey = lb_topup_offer_key($title, $amount, $unit);
        $cfg = lb_get_topups_page_config((string)$game['slug']);
        $image = trim((string)($_POST['image'] ?? ''));
        if (isset($_FILES['topup_image']) && !empty($_FILES['topup_image']['tmp_name'])) {
            $uploadedTopupImage = false;
            if (function_exists('upload_image')) {
                $uploadedTopupImage = upload_image($_FILES['topup_image'], 'public/uploads/topups');
            } else {
                $uploadDir = SYS_PATH . '/public/uploads/topups';
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
                $ext = strtolower(pathinfo((string)($_FILES['topup_image']['name'] ?? ''), PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) { $ext = 'webp'; }
                $fileName = 'topup_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (@move_uploaded_file($_FILES['topup_image']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $uploadedTopupImage = '/public/uploads/topups/' . $fileName;
                }
            }
            if ($uploadedTopupImage !== false) { $image = $uploadedTopupImage; }
        }
        $instructions = trim((string)($_POST['instructions'] ?? ''));
        $active = (int)($_POST['active'] ?? 1) === 1 ? 1 : 0;
        $db->run("UPDATE selling_topups SET game_id = ?, game_slug = ?, game_name = ?, service_label = ?, offer_key = ?, offer_title = ?, offer_amount = ?, offer_unit = ?, region = ?, platform = ?, price = ?, stock = ?, min_quantity = ?, waiting_time_value = ?, waiting_time_unit = ?, waiting_time_minutes = ?, instructions = ?, image = ?, active = ?, updated_at = NOW() WHERE id = ? AND seller_id = ?",
            $gameId, (string)$game['slug'], (string)$game['name'], (string)($cfg['service_label'] ?? 'Top Up'), $offerKey, $title, $amount, $unit, $region, $platform, $price, $stock, $minQty, $wVal, $wUnit, $waitingMinutes, $instructions, $image, $active, $topupId, (int)$seller_data['id']
        );
        redirect_url('seller-area/top-ups?updated=1');
    });

    $router->post('top-ups/:id/delete', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        lb_topups_table_ensure();
        $db->run('DELETE FROM selling_topups WHERE id = ? AND seller_id = ?', (int)$id, (int)$seller_data['id']);
        redirect_url('seller-area/top-ups?deleted=1');
    });



    $router->get('top-up-orders', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        try {
            $orders = $db->run(
                "SELECT p.*, st.image, st.instructions, st.offer_title AS listing_offer_title, st.offer_amount AS listing_offer_amount, st.offer_unit AS listing_offer_unit,
                        st.region AS listing_region, st.platform AS listing_platform, st.service_label AS listing_service_label,
                        st.stock AS listing_stock, st.sold_count AS listing_sold_count, st.active AS listing_active,
                        st.waiting_time_amount, st.waiting_time_unit, st.waiting_time_minutes,
                        g.icon AS game_icon, g.name AS db_game_name, g.slug AS db_game_slug,
                        c.username AS client_username, c.email AS client_email,
                        -- Guest checkouts leave clients.icon NULL. Hand the list a usable
                        -- avatar URL here so the Buyer column never falls back to the letter.
                        COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS client_icon,
                        COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS client_avatar,
                        COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS buyer_icon
                 FROM selling_topup_purchases p
                 LEFT JOIN selling_topups st ON st.id = p.topup_id
                 LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                 LEFT JOIN clients c ON c.id = p.client_id
                 WHERE p.seller_id = ?
                 ORDER BY p.created_at DESC, p.id DESC",
                (int)$seller_data['id']
            ) ?: [];
        } catch (Throwable $e) { $orders = []; }
        $meta = ['title' => 'Top Up Orders | LoLBoost'];
        view_file('seller/pages/topups/orders', compact('meta', 'seller_data', 'orders'));
    });

    $router->get('top-up-order/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        $id = (int)$id;
        try {
            $purchase = $db->row(
                "SELECT p.*, st.image, st.instructions, st.offer_title AS listing_offer_title, st.offer_amount AS listing_offer_amount, st.offer_unit AS listing_offer_unit,
                        g.icon AS game_icon, g.name AS db_game_name, g.slug AS db_game_slug,
                        c.username AS client_username, c.email AS client_email, c.icon AS client_icon, c.icon AS client_avatar
                 FROM selling_topup_purchases p
                 LEFT JOIN selling_topups st ON st.id = p.topup_id
                 LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                 LEFT JOIN clients c ON c.id = p.client_id
                 WHERE p.id = ? AND p.seller_id = ?
                 LIMIT 1",
                $id, (int)$seller_data['id']
            );
        } catch (Throwable $e) { $purchase = null; }
        if (empty($purchase)) { redirect_url('seller-area/top-up-orders'); return; }
        $checkoutData = [];
        $raw = (string)($purchase['checkout_data'] ?? '');
        if ($raw !== '') { $decoded = json_decode($raw, true); if (is_array($decoded)) $checkoutData = $decoded; }
        $meta = ['title' => 'Top Up Order #' . $id . ' | LoLBoost', 'h1' => 'Top Up Order #' . $id];
        view_file('seller/pages/topups/order_view', compact('meta', 'seller_data', 'purchase', 'checkoutData'));
    });

    $router->get('items', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $items = $db->run(
            "SELECT * FROM selling_items WHERE seller_id = ? ORDER BY created_at DESC",
            (int)$seller_data['id']
        ) ?: [];
        $itemGames = [];
        try {
            $itemGames = $db->run("SELECT g.* FROM games g INNER JOIN game_services gs ON gs.game_id = g.id AND gs.service_type = 'items' AND gs.status = 1 WHERE g.status = 1 ORDER BY g.sort_order ASC, g.name ASC") ?: [];
        } catch (Throwable $e) {
            try { $itemGames = $db->run("SELECT * FROM games WHERE status = 1 ORDER BY sort_order ASC, name ASC") ?: []; } catch (Throwable $e2) { $itemGames = []; }
        }
        $itemSchemas = [];
        foreach ($itemGames as $g) {
            $slug = (string)($g['slug'] ?? '');
            if ($slug !== '') $itemSchemas[$slug] = function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($slug) : [];
        }
        $meta = ['title' => 'My Items | LoLBoost'];
        view_file('seller/pages/items/list', compact('meta', 'seller_data', 'items', 'itemGames', 'itemSchemas'));
    });


    $router->get('items/add', function () {
        global $is_seller, $seller_data;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $meta = ['title' => 'List Item | LoLBoost'];
        view_file('seller/pages/items/add', compact('meta', 'seller_data'));
    });

    $router->get('item/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id = (int)$id;
        $item = $db->row("SELECT * FROM selling_items WHERE id = ? AND seller_id = ? LIMIT 1", $id, (int)$seller_data['id']);
        if (empty($item)) { redirect_url('seller-area/items'); return; }
        $itemGames = [];
        try { $itemGames = $db->run("SELECT g.* FROM games g INNER JOIN game_services gs ON gs.game_id = g.id AND gs.service_type = 'items' AND gs.status = 1 WHERE g.status = 1 ORDER BY g.sort_order ASC, g.name ASC") ?: []; } catch (Throwable $e) { $itemGames = []; }
        $itemSchemas = [];
        foreach ($itemGames as $g) { $slug = (string)($g['slug'] ?? ''); if ($slug !== '') $itemSchemas[$slug] = function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($slug) : []; }
        $meta = ['title' => item_meta_display_text($item['title'] ?? null, 'Item') . ' | LoLBoost', 'h1' => $item['title'] ?? 'Item'];
        view_file('seller/pages/items/view', compact('meta', 'seller_data', 'item', 'itemGames', 'itemSchemas'));
    });

    $router->get('item-orders', function () {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $orders = $db->run(
            "SELECT sip.*, si.title AS item_title, si.images, si.type, si.server,
                    si.game, si.game_id, si.item_data, si.stock AS listing_stock,
                    si.sold_count AS listing_sold_count, si.active AS listing_active,
                    si.requires_friendship_days, si.waiting_time_amount, si.waiting_time_unit,
                    g.icon AS game_icon, g.name AS game_name, g.slug AS game_slug,
                    c.username AS client_username, c.email AS client_email,
                    -- Guest checkouts leave clients.icon NULL. Hand the list a usable
                    -- avatar URL here so the Buyer column never falls back to the letter.
                    COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS client_icon,
                    COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS client_avatar,
                    COALESCE(NULLIF(TRIM(c.icon), ''), 'https://lolboost.gg/public/uploads/icons/default.png') AS buyer_icon
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN games g ON g.id = si.game_id
             LEFT JOIN clients c ON c.id = sip.client_id
             WHERE sip.seller_id = ?
             ORDER BY sip.created_at DESC, sip.id DESC",
            (int)$seller_data['id']
        ) ?: [];
        $meta = ['title' => 'Digital Goods | LoLBoost'];
        view_file('seller/pages/items/orders', compact('meta', 'seller_data', 'orders'));
    });

    $router->get('item-order/:id', function ($id) {
        global $is_seller, $seller_data, $db;
        if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
        $id = (int)$id;
        $purchase = $db->row(
            "SELECT sip.*, si.title AS item_title, si.images, si.type, si.server,
                    si.description AS item_description, si.requires_friendship_days
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             WHERE sip.id = ? AND sip.seller_id = ? LIMIT 1",
            $id, (int)$seller_data['id']
        );
        if (empty($purchase)) { redirect_url('seller-area/item-orders'); return; }
        $buyer = !empty($purchase['client_id']) ? db_get_row('clients', ['id' => (int)$purchase['client_id']], 1) : null;
        $details = db_get_row('selling_item_purchase_details', ['purchase_id' => $id], 1) ?: [];
        $chat_messages = [];
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_item_purchase_' . $id) . '.json';
        if (is_file($chat_path)) {
            $raw = file_get_contents($chat_path);
            $chat_data = json_decode($raw, true);
            if (is_array($chat_data) && isset($chat_data['messages'])) {
                $chat_messages = array_values(array_filter($chat_data['messages'], fn($m) => empty($m['deleted'])));
            }
        }
        $remaining = !empty($purchase['friendship_ready_at']) ? (strtotime($purchase['friendship_ready_at']) - time()) : null;
        $meta = ['title' => 'Digital Good Order #' . $id . ' | LoLBoost', 'h1' => 'Digital Good Order #' . $id];
        view_file('seller/pages/items/order_view', compact('meta', 'seller_data', 'purchase', 'buyer', 'details', 'chat_messages', 'remaining'));
    });
});
// =============================================
// E-GIRL PUBLIC ROUTES
// =============================================

$router->get('/egirls', function () {
    global $db;

    $page    = max(1, (int)($_GET['page']   ?? 1));
    $perPage = 12;
    $offset  = ($page - 1) * $perPage;
    $game    = esc($_GET['game']   ?? '');
    $lang    = esc($_GET['lang']   ?? '');
    $search  = esc($_GET['search'] ?? '');
    $voice   = esc($_GET['voice']  ?? ''); // '1' = voice only, '' = all

    $where  = "b.is_banned = 0 AND b.is_egirl = 1 AND b.show_profile = 1 AND EXISTS (SELECT 1 FROM egirl_services es WHERE es.egirl_id = b.id AND es.is_active = 1)";
    $params = [];

    if ($game !== '') {
        $where   .= " AND ep.games LIKE ?";
        $params[] = "%{$game}%";
    }
    if ($lang !== '') {
        $where   .= " AND ep.languages LIKE ?";
        $params[] = "%{$lang}%";
    }
    if ($search !== '') {
        $where   .= " AND (b.username LIKE ? OR ep.bio LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    if ($voice === '1') {
        $where .= " AND voice_chat = 1";
    }

    $total      = (int)$db->cell("SELECT COUNT(*) FROM boosters b LEFT JOIN egirl_profiles ep ON ep.egirl_id = b.id WHERE {$where}", ...$params);
    $totalPages = (int)ceil($total / $perPage);

    $egirls = $db->run(
        "SELECT b.id, b.username, b.icon,
                ep.bio, ep.languages, ep.games, ep.lol_rank, ep.val_rank, ep.tft_rank,
                ep.review_count, ep.review_avg, ep.total_sessions, ep.is_online,
                cover, voice_chat,
                (SELECT MIN(es.price_cents) FROM egirl_services es WHERE es.egirl_id = b.id AND es.is_active = 1) AS min_price
         FROM boosters b
         LEFT JOIN egirl_profiles ep ON ep.egirl_id = b.id
         WHERE {$where}
         ORDER BY ep.is_online DESC, ep.total_sessions DESC
         LIMIT {$perPage} OFFSET {$offset}",
        ...$params
    );

    $meta = [
        'title'       => 'E-Girls | Play with Female Gamers | LoLBoost',
        'description' => 'Book a gaming session with our E-Girls. Voice chat included. LoL, Valorant & TFT.',
        'h1'          => 'E-Girls',
    ];

    // Convert min_price to session currency for display on listing cards
    $displayCurrency = $_SESSION['currency'] ?? 'EUR';
    $displayRate     = ($displayCurrency === 'USD' && function_exists('get_exchange_rate')) ? (float)get_exchange_rate() : 1.0;

    // Cards show the same rating as the profile, so the 24h "No Feedback left."
    // placeholders have to be folded in here too.
    $noFeedback = function_exists('egirl_no_feedback_counts')
        ? egirl_no_feedback_counts(array_column($egirls ?: [], 'id'), 24)
        : [];

    foreach ($egirls as &$egirl) {
        $auto = (int)($noFeedback[(int)$egirl['id']] ?? 0);
        if ($auto > 0) {
            $stored   = (int)($egirl['review_count'] ?? 0);
            $avg      = (float)($egirl['review_avg'] ?? 0);
            $combined = $stored + $auto;
            $egirl['review_count'] = $combined;
            $egirl['review_avg']   = $combined > 0
                ? round((($avg * $stored) + ($auto * 5)) / $combined, 1)
                : 0.0;
        }

        $egirl['min_price_eur']     = (int)($egirl['min_price'] ?? 0);
        $egirl['min_price_display'] = $egirl['min_price_eur'] > 0
            ? (int)round($egirl['min_price_eur'] * $displayRate)
            : null;
        $egirl['display_currency']  = $displayCurrency;
    }
    unset($egirl);

    view_file('website/pages/egirls/list', compact('egirls', 'meta', 'page', 'totalPages', 'game', 'lang', 'search', 'displayCurrency', 'voice'));
});

$router->get('/egirls/:id', function ($id) {
    global $db;
    $id = (int)$id;

    $egirl = $db->row(
        "SELECT b.*, ep.*, b.id AS egirl_id, b.cover AS booster_cover
         FROM boosters b
         LEFT JOIN egirl_profiles ep ON ep.egirl_id = b.id
         WHERE b.id = ? AND b.is_egirl = 1 AND b.is_banned = 0 AND b.show_profile = 1
         LIMIT 1",
        $id
    );
    if (!$egirl) { redirect_url('egirls'); return; }

    $services     = $db->run("SELECT * FROM egirl_services WHERE egirl_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC", $id);
    $all_reviews = $db->run("SELECT er.*, c.username AS client_username, c.icon AS client_icon FROM egirl_reviews er LEFT JOIN clients c ON c.id = er.client_id WHERE er.egirl_id = ? AND er.approved = 1 ORDER BY er.created_at DESC", $id) ?: [];

    // Completed sessions without client feedback become 5-star "No Feedback left."
    // cards after 24h and count towards the rating, same as on seller profiles.
    $no_feedback_entries = function_exists('egirl_no_feedback_entries')
        ? egirl_no_feedback_entries($id, 24)
        : [];
    if ($no_feedback_entries) {
        $stored_count = (int)($egirl['review_count'] ?? 0);
        $stored_avg   = (float)($egirl['review_avg'] ?? 0);
        $auto_count   = count($no_feedback_entries);
        $combined     = $stored_count + $auto_count;

        $egirl['review_count'] = $combined;
        $egirl['review_avg']   = $combined > 0
            ? round((($stored_avg * $stored_count) + ($auto_count * 5)) / $combined, 1)
            : 0.0;

        $all_reviews = array_merge($all_reviews, $no_feedback_entries);
        usort($all_reviews, static fn($a, $b) => strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? '')));
    }

    // 10 per page; the tab keeps its own ?rpage= param so it does not clash with
    // other paginated widgets on the page.
    $reviews_per_page  = 10;
    $reviews_total     = count($all_reviews);
    $reviews_pages     = max(1, (int)ceil($reviews_total / $reviews_per_page));
    $reviews_page      = min($reviews_pages, max(1, (int)($_GET['rpage'] ?? 1)));
    $reviews           = array_slice($all_reviews, ($reviews_page - 1) * $reviews_per_page, $reviews_per_page);

    $availability = $db->run("SELECT * FROM egirl_availability WHERE egirl_id = ? ORDER BY day_of_week ASC", $id);

    $meta = [
        'title'       => $egirl['username'] . ' | E-Girl | LoLBoost',
        'description' => substr(strip_tags($egirl['bio'] ?? 'Book a gaming session.'), 0, 160),
    ];
    view_file('website/pages/egirls/view', compact('egirl', 'services', 'reviews', 'availability', 'meta', 'reviews_total', 'reviews_page', 'reviews_pages'));
});

// =============================================
// CLIENT EGIRL ORDER VIEW ROUTE
// =============================================

$router->get('egirl-order/:id', function ($order_id) {
    global $is_client, $db;
    if (!$is_client) { redirect_url(''); return; }
    $order_id = (int)$order_id;
    $order = $db->row(
        "SELECT eo.*,
                c.username AS client_username, c.icon AS client_icon, c.discord AS client_discord,
                b.username AS egirl_username, b.icon AS egirl_icon,
                es.title AS service_title, es.type AS service_type,
                es.game, es.unit_value, es.unit_type, es.includes_voice
         FROM egirl_orders eo
         LEFT JOIN clients c   ON c.id  = eo.client_id
         LEFT JOIN boosters b  ON b.id  = eo.egirl_id
         LEFT JOIN egirl_services es ON es.id = eo.service_id
         WHERE eo.id = ? AND eo.client_id = ? LIMIT 1",
        $order_id, (int) CLIENT_ID
    );
    if (!$order) { redirect_url('profile/orders'); return; }
    $review = $db->row(
        "SELECT id, rating, comment, approved, created_at
         FROM egirl_reviews
         WHERE egirl_order_id = ? AND client_id = ?
         LIMIT 1",
        $order_id, (int)CLIENT_ID
    );

    // Load chat messages from file-based chat system
    $chat_raw  = chat_load_messages('eg_' . $order_id);
    $chat_prep = chat_prepare_for_viewer($chat_raw, 'client');
    $messages  = $chat_prep['messages'] ?? [];

    $meta = [
        'title'       => 'Booking #' . $order_id . ' | LoLBoost',
        'h1'          => 'Booking #' . $order_id,
        'description' => 'View your E-Girl booking.',
    ];
    view_file('client/pages/orders/egirl_view', compact('meta', 'order', 'messages', 'review'));
});

// =============================================
// E-GIRL AREA ROUTES (booster-area extension)
// =============================================

$router->group('booster-area', function () {
    global $router, $is_egirl;

    // ── E-Girl Setup ──────────────────────────────────────────────────────────
    $router->get('egirl-setup', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        $status  = egirl_setup_status(BOOSTER_ID);
        $booster = $db->row("SELECT * FROM boosters WHERE id = ? LIMIT 1", BOOSTER_ID);
        $profile = $db->row("SELECT * FROM egirl_profiles WHERE egirl_id = ? LIMIT 1", BOOSTER_ID);
        view_file('booster/pages/egirl/setup', ['data' => $status, 'booster' => $booster, 'profile' => $profile]);
    });
    // ─────────────────────────────────────────────────────────────────────────

    $router->get('egirl-dashboard', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        if (!egirl_setup_is_complete(BOOSTER_ID)) { redirect_url('booster-area/egirl-setup'); return; }
        $id    = BOOSTER_ID;
        $stats = [
            'orders_active'    => (int)$db->cell("SELECT COUNT(*) FROM egirl_orders WHERE egirl_id = ? AND status = 'IN_PROGRESS'", $id),
            'orders_completed' => (int)$db->cell("SELECT COUNT(*) FROM egirl_orders WHERE egirl_id = ? AND status = 'COMPLETED'", $id),
            'orders_total'     => (int)$db->cell("SELECT COUNT(*) FROM egirl_orders WHERE egirl_id = ?", $id),
        ];
        $balance_cents = (int)($db->cell("SELECT balance FROM egirl_balance WHERE egirl_id = ?", $id) ?? 0);
        $recent_orders = $db->run("SELECT eo.*, c.username AS client_username, c.icon AS client_icon FROM egirl_orders eo LEFT JOIN clients c ON c.id = eo.client_id WHERE eo.egirl_id = ? AND eo.status <> 'UNPAID' ORDER BY eo.created_at DESC LIMIT 5", $id);
        $meta = ['title' => 'Dashboard | E-Girl Area | LoLBoost', 'h1' => 'Dashboard'];
        view_file('booster/pages/egirl/dashboard', compact('meta', 'stats', 'balance_cents', 'recent_orders'));
    });

    $router->get('egirl-orders', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        if (!egirl_setup_is_complete(BOOSTER_ID)) { redirect_url('booster-area/egirl-setup'); return; }
        $orders = $db->run("SELECT eo.*, c.username AS client_username, c.icon AS client_icon FROM egirl_orders eo LEFT JOIN clients c ON c.id = eo.client_id WHERE eo.egirl_id = ? ORDER BY eo.created_at DESC", BOOSTER_ID);
        $meta = ['title' => 'My Bookings | E-Girl Area | LoLBoost', 'h1' => 'My Bookings'];
        view_file('booster/pages/egirl/orders', compact('meta', 'orders'));
    });

    $router->get('egirl-panel', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        if (!egirl_setup_is_complete(BOOSTER_ID)) { redirect_url('booster-area/egirl-setup'); return; }
        $open_orders = $db->run(
            "SELECT eo.*, c.username AS client_username, c.icon AS client_icon,
                    es.title AS service_title, es.type AS service_type, es.game
             FROM egirl_orders eo
             LEFT JOIN clients c ON c.id = eo.client_id
             LEFT JOIN egirl_services es ON es.id = eo.service_id
             WHERE eo.egirl_id = ? AND eo.status = 'PAID'
             ORDER BY eo.created_at ASC",
            BOOSTER_ID
        );
        $meta = ['title' => 'Booking Panel | E-Girl Area | LoLBoost', 'h1' => 'Booking Panel'];
        view_file('booster/pages/egirl/panel', compact('meta', 'open_orders'));
    });

    $router->get('egirl-services', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        if (!egirl_setup_is_complete(BOOSTER_ID)) { redirect_url('booster-area/egirl-setup'); return; }
        $services = $db->run("SELECT * FROM egirl_services WHERE egirl_id = ? ORDER BY sort_order ASC, id ASC", BOOSTER_ID);
        // The service game picker must use this GG-Girl's complete profile
        // selection. Previously the view received no $profile at all, so it
        // could only fall back to games found on already saved services.
        $profile = $db->row("SELECT * FROM egirl_profiles WHERE egirl_id = ? LIMIT 1", BOOSTER_ID) ?: [];
        // Resolve individual cut rate; fall back to platform default of 60%
        $booster_row    = $db->row("SELECT egirl_cut_percent FROM boosters WHERE id = ? LIMIT 1", BOOSTER_ID);
        $egirl_cut_rate = ($booster_row && $booster_row['egirl_cut_percent'] !== null)
            ? (float)$booster_row['egirl_cut_percent'] / 100
            : 0.60;
        $meta = ['title' => 'My Services | E-Girl Area | LoLBoost', 'h1' => 'My Services'];
        view_file('booster/pages/egirl/services', compact('meta', 'services', 'profile', 'egirl_cut_rate'));
    });

    $router->get('egirl-profile', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        if (!egirl_setup_is_complete(BOOSTER_ID)) { redirect_url('booster-area/egirl-setup'); return; }
        $profile = $db->row("SELECT * FROM egirl_profiles WHERE egirl_id = ?", BOOSTER_ID);
        if (!$profile) {
            $db->run("INSERT IGNORE INTO egirl_profiles (egirl_id) VALUES (?)", BOOSTER_ID);
            $profile = $db->row("SELECT * FROM egirl_profiles WHERE egirl_id = ?", BOOSTER_ID);
        }
        $availability = $db->run("SELECT * FROM egirl_availability WHERE egirl_id = ? ORDER BY day_of_week ASC", BOOSTER_ID);
        $meta = ['title' => 'My Profile | E-Girl Area | LoLBoost', 'h1' => 'My Profile'];
        view_file('booster/pages/egirl/profile', compact('meta', 'profile', 'availability'));
    });

    $router->get('egirl-payments', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        if (!egirl_setup_is_complete(BOOSTER_ID)) { redirect_url('booster-area/egirl-setup'); return; }
        $payments      = $db->run("SELECT * FROM egirl_payments WHERE egirl_id = ? ORDER BY id DESC", BOOSTER_ID);
        $balance_cents = (int)($db->cell("SELECT balance FROM egirl_balance WHERE egirl_id = ?", BOOSTER_ID) ?? 0);
        $meta = ['title' => 'My Payments | E-Girl Area | LoLBoost', 'h1' => 'My Payments'];
        view_file('booster/pages/egirl/payments', compact('meta', 'payments', 'balance_cents'));
    });

    $router->get('egirl-payout', function () {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        if (!egirl_setup_is_complete(BOOSTER_ID)) { redirect_url('booster-area/egirl-setup'); return; }
        $requests      = $db->run("SELECT * FROM egirl_payout_requests WHERE egirl_id = ? ORDER BY id DESC", BOOSTER_ID);
        $balance_cents = (int)($db->cell("SELECT balance FROM egirl_balance WHERE egirl_id = ?", BOOSTER_ID) ?? 0);
        $meta = ['title' => 'Payout Requests | E-Girl Area | LoLBoost', 'h1' => 'Payout Requests'];
        view_file('booster/pages/egirl/payout', compact('meta', 'requests', 'balance_cents'));
    });

    $router->get('egirl-order/:id', function ($order_id) {
        global $is_egirl, $db;
        if (!$is_egirl) { redirect_url('booster-area/auth/login'); return; }
        $order_id = (int)$order_id;
        $order = $db->row(
            "SELECT eo.*, c.username AS client_username, c.icon AS client_icon, c.discord AS client_discord,
                    es.title AS service_title, es.type AS service_type, es.game, es.unit_value, es.unit_type
             FROM egirl_orders eo
             LEFT JOIN clients c ON c.id = eo.client_id
             LEFT JOIN egirl_services es ON es.id = eo.service_id
             WHERE eo.id = ? AND eo.egirl_id = ? LIMIT 1",
            $order_id, BOOSTER_ID
        );
        if (!$order) { redirect_url('booster-area/egirl-orders'); return; }
        // Load chat messages from file-based chat system
        $chat_raw  = chat_load_messages('eg_' . $order_id);
        $chat_prep = chat_prepare_for_viewer($chat_raw, 'booster');
        $messages  = $chat_prep['messages'] ?? [];
        $meta = ['title' => 'Booking #' . $order_id . ' | E-Girl Area | LoLBoost'];
        view_file('booster/pages/egirl/order-view', compact('meta', 'order', 'messages'));
    });
});

// =============================================

/**
 * ==== Running Routes ====-
 **/

// ── Dynamic routes for any game added via Admin Dashboard ────────────────────
$_knownGameSlugs = ['league-of-legends', 'valorant', 'teamfight-tactics'];

// ── Dynamic Top Ups helpers ─────────────────────────────────────────────────
if (!function_exists('lb_topups_schema_table_ensure')) {
    function lb_topups_schema_table_ensure(): void
    {
        global $db;
        if (empty($db)) return;
        try {
            $db->run("CREATE TABLE IF NOT EXISTS game_topup_schemas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                game_slug VARCHAR(191) NOT NULL UNIQUE,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                schema_json LONGTEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {}
    }
}

if (!function_exists('lb_topups_table_ensure')) {
    function lb_topups_table_ensure(): void
    {
        global $db;
        if (empty($db)) return;
        try {
            $db->run("CREATE TABLE IF NOT EXISTS selling_topups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                seller_id INT NOT NULL,
                game_id INT NULL,
                game_slug VARCHAR(191) NULL,
                game_name VARCHAR(191) NULL,
                service_label VARCHAR(191) NULL,
                offer_key VARCHAR(191) NULL,
                offer_title VARCHAR(255) NOT NULL,
                offer_amount DECIMAL(12,2) NULL,
                offer_unit VARCHAR(64) NULL,
                region VARCHAR(128) NULL,
                platform VARCHAR(128) NULL,
                price INT NOT NULL DEFAULT 0,
                currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
                stock INT NOT NULL DEFAULT 999,
                min_quantity INT NOT NULL DEFAULT 1,
                waiting_time_value INT NOT NULL DEFAULT 10,
                waiting_time_unit ENUM('minutes','hours','days') NOT NULL DEFAULT 'minutes',
                waiting_time_minutes INT NOT NULL DEFAULT 10,
                instructions TEXT NULL,
                image VARCHAR(500) NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                sold_count INT NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_game_offer (game_id, offer_key, region, active),
                INDEX idx_seller (seller_id),
                INDEX idx_waiting (waiting_time_minutes),
                INDEX idx_price (price)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {}
    }
}

if (!function_exists('lb_topup_purchases_table_ensure')) {
    function lb_topup_purchases_table_ensure(): void
    {
        global $db;
        if (empty($db)) return;
        try {
            $db->run("CREATE TABLE IF NOT EXISTS selling_topup_purchases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                topup_id INT NOT NULL,
                seller_id INT NOT NULL,
                client_id INT NULL,
                invoice_id INT NULL,
                game_id INT NULL,
                game_slug VARCHAR(191) NULL,
                game_name VARCHAR(191) NULL,
                offer_key VARCHAR(191) NULL,
                offer_title VARCHAR(255) NOT NULL,
                offer_amount DECIMAL(12,2) NULL,
                offer_unit VARCHAR(64) NULL,
                region VARCHAR(128) NULL,
                platform VARCHAR(128) NULL,
                quantity INT NOT NULL DEFAULT 1,
                unit_price INT NOT NULL DEFAULT 0,
                price INT NOT NULL DEFAULT 0,
                currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
                waiting_time_value INT NOT NULL DEFAULT 0,
                waiting_time_unit VARCHAR(16) NOT NULL DEFAULT 'minutes',
                waiting_time_minutes INT NOT NULL DEFAULT 0,
                checkout_data LONGTEXT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'PAID',
                paid_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_invoice (invoice_id),
                INDEX idx_seller (seller_id),
                INDEX idx_client (client_id),
                INDEX idx_game (game_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {}
    }
}

if (!function_exists('lb_topup_waiting_minutes')) {
    function lb_topup_waiting_minutes($value, $unit): int
    {
        $value = max(0, (int)$value);
        $unit = strtolower(trim((string)$unit));
        if ($unit === 'days') return $value * 1440;
        if ($unit === 'hours') return $value * 60;
        return $value;
    }
}

if (!function_exists('lb_topup_offer_key')) {
    function lb_topup_offer_key(string $title, $amount = null, string $unit = ''): string
    {
        $base = trim(strtolower($title . ' ' . (string)$amount . ' ' . $unit));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base);
        return trim((string)$base, '-') ?: 'topup-offer';
    }
}



if (!function_exists('lb_topup_regions_for_game')) {
    function lb_topup_regions_for_game(string $gameSlug): array
    {
        $slug = strtolower(trim($gameSlug));
        try {
            if (function_exists('lb_get_game_topup_schema')) {
                $schema = lb_get_game_topup_schema($gameSlug);
                if (!empty($schema['regions']) && is_array($schema['regions'])) {
                    $out = [];
                    foreach ($schema['regions'] as $regionRow) {
                        if (is_array($regionRow)) {
                            $value = trim((string)($regionRow['value'] ?? $regionRow['key'] ?? $regionRow['slug'] ?? $regionRow['label'] ?? ''));
                            $label = trim((string)($regionRow['label'] ?? $regionRow['name'] ?? $value));
                        } else {
                            $value = trim((string)$regionRow);
                            $label = $value;
                        }
                        if ($value !== '') $out[$value] = $label !== '' ? $label : $value;
                    }
                    if ($out) return $out;
                }
            }
        } catch (Throwable $e) {}
        if (in_array($slug, ['league-of-legends', 'lol', 'league'], true)) {
            return [
                'euw' => 'EU-West',
                'eune' => 'EU-Nordic & East',
                'na' => 'North America',
                'br' => 'Brazil',
                'lan' => 'Latin America North',
                'las' => 'Latin America South',
                'oce' => 'Oceania',
                'ru' => 'Russia',
                'tr' => 'Turkey',
                'jp' => 'Japan',
                'kr' => 'Korea',
                'pbe' => 'PBE',
                'me' => 'Middle East',
                'vn' => 'Vietnam',
                'ph' => 'Philippines',
                'sg' => 'Singapore',
                'th' => 'Thailand',
                'tw' => 'Taiwan',
            ];
        }
        return ['Global' => 'Global'];
    }
}

if (!function_exists('lb_topup_region_default')) {
    function lb_topup_region_default(string $gameSlug): string
    {
        $opts = lb_topup_regions_for_game($gameSlug);
        $keys = array_keys($opts);
        return (string)($keys[0] ?? 'Global');
    }
}

if (!function_exists('lb_topup_normalize_region')) {
    function lb_topup_normalize_region($region, string $gameSlug): string
    {
        $raw = trim((string)$region);
        $opts = lb_topup_regions_for_game($gameSlug);
        if ($raw === '') return lb_topup_region_default($gameSlug);
        $wanted = strtolower($raw);
        foreach ($opts as $key => $label) {
            if (strtolower((string)$key) === $wanted || strtolower((string)$label) === $wanted || strtoupper((string)$key) === strtoupper($raw)) {
                return (string)$key;
            }
        }
        return $raw !== '' ? $raw : lb_topup_region_default($gameSlug);
    }
}

if (!function_exists('lb_topup_region_label')) {
    function lb_topup_region_label($region, string $gameSlug): string
    {
        $raw = trim((string)$region);
        if ($raw === '') return 'Global';
        $opts = lb_topup_regions_for_game($gameSlug);
        $wanted = strtolower($raw);
        foreach ($opts as $key => $label) {
            if (strtolower((string)$key) === $wanted || strtolower((string)$label) === $wanted || strtoupper((string)$key) === strtoupper($raw)) return (string)$label;
        }
        return $raw;
    }
}

if (!function_exists('lb_get_topups_page_config')) {
    function lb_get_topups_page_config(string $gameSlug): array
    {
        global $db;
        $game = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
        $label = 'Top Up';
        try {
            if (!empty($game['id'])) {
                $row = $db->row("SELECT label, config FROM game_services WHERE game_id = ? AND service_type IN ('topups','top-ups','currencies') ORDER BY FIELD(service_type,'topups','top-ups','currencies') LIMIT 1", (int)$game['id']);
                if (!empty($row['label'])) $label = (string)$row['label'];
                $cfg = !empty($row['config']) ? json_decode((string)$row['config'], true) : [];
                if (is_array($cfg)) {
                    $cfg['service_label'] = $cfg['service_label'] ?? $label;
                    return $cfg;
                }
            }
        } catch (Throwable $e) {}
        return [
            'service_label' => $label,
            'page_title' => trim(($game['name'] ?? ucwords(str_replace('-', ' ', $gameSlug))) . ' ' . $label),
            'page_description' => 'Buy ' . ($game['name'] ?? 'game') . ' top ups securely on LoLBoost.',
            'amount_label' => 'Amount',
            'region_label' => 'Region',
            'show_other_sellers' => true,
        ];
    }
}

if (!function_exists('lb_get_game_topup_schema')) {
    function lb_get_game_topup_schema(string $gameSlug): array
    {
        global $db;
        lb_topups_schema_table_ensure();
        try {
            $row = $db->row('SELECT enabled, schema_json FROM game_topup_schemas WHERE game_slug = ? LIMIT 1', $gameSlug);
            if ($row && !empty($row['schema_json'])) {
                $schema = json_decode((string)$row['schema_json'], true);
                if (is_array($schema)) {
                    $schema['enabled'] = !empty($row['enabled']);
                    return $schema;
                }
            }
        } catch (Throwable $e) {}
        return [
            'enabled' => true,
            'checkout_fields' => [
                ['key' => 'account_id', 'label' => 'Account ID', 'type' => 'text', 'required' => true],
            ],
            'seller_fields' => [],
        ];
    }
}


foreach (util_get_all_games() as $_dynGame) {
    if (in_array($_dynGame['slug'], $_knownGameSlugs, true)) continue;
    $_dynSlug = $_dynGame['slug'];
    $router->group('/' . $_dynSlug, function () use ($_dynSlug) {
        global $router;
        util_register_game_boost_routes($router, $_dynSlug, 'generic');
        $_g = util_get_game_by_slug($_dynSlug);
        if ($_g && util_game_has_service((int)$_g['id'], 'accounts')) {
            $_acctFn = function () use ($_dynSlug) {
                global $db;
                $__cacheKey = 'accounts-dynamic-' . $_dynSlug;
                if (lb_public_page_cache_serve($__cacheKey, 90)) return;
                $__pageCache = lb_public_page_cache_start();
                $game    = util_get_game_by_slug($_dynSlug);
                $gameName = $game['name'] ?? $_dynSlug;

                // Load seller listings from selling_accounts
                $data = $db->run(
                    "SELECT sa.*, s.username AS seller_username, s.slug AS seller_slug,
                            s.icon AS seller_icon, s.rank AS seller_rank,
                            s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active,
                            (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
                     FROM selling_accounts sa
                     LEFT JOIN sellers s ON s.id = sa.seller_id
                     LEFT JOIN seller_stats ss ON ss.seller_id = s.id
                     WHERE sa.sold = 0 AND sa.active = 1
                       AND (sa.game = ? OR sa.game = ?)
                     ORDER BY sa.created_at DESC
                     LIMIT 100",
                    $_dynSlug, ($_dynSlug === 'league-of-legends' ? 'lol' : $_dynSlug)
                ) ?: [];

                $totalItems = count($data);
                $pagination = ['totalItems' => $totalItems, 'page' => 1, 'totalPages' => 1];

                $_dynCfg = util_get_accounts_page_config($_dynSlug);
                $meta = [
                    'title'       => 'Buy ' . $gameName . ' Accounts | LoLBoost',
                    'h1'          => $gameName . ' Accounts',
                    'description' => 'Buy ' . $gameName . ' accounts. Instant delivery, verified sellers.',
                    'canonical'   => BASE_URL . '/' . $_dynSlug . '/accounts',
                    'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
                ];
                view_file('website/pages/accounts/shop_dynamic', [
                    'meta'       => $meta,
                    'data'       => $data,
                    'pagination' => $pagination,
                    'game'       => $_dynSlug,
                    'gameConfig' => $_dynCfg,
                    'faq'        => 'accounts',
                ]);
                lb_public_page_cache_finish($__cacheKey, $__pageCache);
            };
            $router->get('/accounts', $_acctFn);

            // Account detail page
            $router->get('/account/:slug', function ($slug) use ($_dynSlug) {
                global $db;
                $slug    = esc($slug);
                $account = $db->row(
                    "SELECT sa.*, s.username AS seller_username, s.slug AS seller_slug,
                            s.icon AS seller_icon, s.rank AS seller_rank,
                            s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active,
                            s.allow_chat_requests AS allow_chat_requests
                     FROM selling_accounts sa
                     LEFT JOIN sellers s ON s.id = sa.seller_id
                     WHERE sa.slug = ? AND sa.game = ?
                     LIMIT 1",
                    $slug, $_dynSlug
                );
                if (!$account) { redirect_url($_dynSlug . '/accounts'); return; }

                $game     = util_get_game_by_slug($_dynSlug);
                $gameName = $game['name'] ?? ucwords(str_replace('-', ' ', $_dynSlug));
                $meta = [
                    'title'       => ($account['title'] ?? $gameName . ' Account') . ' | LoLBoost',
                    'description' => substr(strip_tags($account['description'] ?? ''), 0, 160),
                    'canonical'   => BASE_URL . '/' . $_dynSlug . '/account/' . $slug,
                    'robots'      => 'index, follow',
                ];
                $seller          = null;
                $seller_accounts = [];
                if (!empty($account['seller_id'])) {
                    $seller = db_get_row('sellers', ['id' => (int)$account['seller_id']]);
                    $seller_accounts = $db->run(
                        "SELECT * FROM selling_accounts WHERE seller_id = ? AND game = ? AND sold = 0 AND active = 1 AND id != ? ORDER BY created_at DESC LIMIT 8",
                        (int)$account['seller_id'], $_dynSlug, (int)$account['id']
                    ) ?: [];
                }
                view_file('website/pages/accounts/view_generic', [
                    'meta'                   => $meta,
                    'account'                => $account,
                    'seller'                 => $seller,
                    'seller_accounts'        => $seller_accounts,
                    'sellerChatAllowedInline'=> !empty($account['allow_chat_requests'])
                                               || !array_key_exists('allow_chat_requests', (array)$seller),
                ]);
            });
        }
if ($_g && function_exists('util_game_has_service') && (util_game_has_service((int)$_g['id'], 'topups') || util_game_has_service((int)$_g['id'], 'top-ups') || util_game_has_service((int)$_g['id'], 'currencies'))) {
    $router->get('/top-ups', function () use ($_dynSlug) {
        global $db;
        lb_topups_table_ensure();
        $game = function_exists('util_get_game_by_slug') ? util_get_game_by_slug($_dynSlug) : [];
        $gameId = (int)($game['id'] ?? 0);
        $gameName = (string)($game['name'] ?? ucwords(str_replace('-', ' ', $_dynSlug)));
        $cfg = lb_get_topups_page_config($_dynSlug);
        $schema = lb_get_game_topup_schema($_dynSlug);
        $rows = $db->run("SELECT st.*, s.username AS seller_username, s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active,
                    (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
             FROM selling_topups st
             LEFT JOIN sellers s ON s.id = st.seller_id
             WHERE st.active = 1 AND ((? > 0 AND st.game_id = ?) OR LOWER(TRIM(COALESCE(st.game_slug,''))) = LOWER(TRIM(?)))
             ORDER BY st.region ASC, st.offer_amount ASC, st.price ASC, st.waiting_time_minutes ASC", $gameId, $gameId, $_dynSlug) ?: [];
        $topupRegions = function_exists('lb_topup_regions_for_game') ? lb_topup_regions_for_game($_dynSlug) : [];
        $selectedTopupRegion = '';
        if (isset($_GET['region']) && trim((string)$_GET['region']) !== '') {
            $selectedTopupRegion = function_exists('lb_topup_normalize_region') ? lb_topup_normalize_region($_GET['region'], $_dynSlug) : trim((string)$_GET['region']);
            $rows = array_values(array_filter((array)$rows, function ($row) use ($selectedTopupRegion, $_dynSlug) {
                $rowRegion = function_exists('lb_topup_normalize_region') ? lb_topup_normalize_region($row['region'] ?? '', $_dynSlug) : trim((string)($row['region'] ?? ''));
                return strtolower($rowRegion) === strtolower($selectedTopupRegion);
            }));
        }
        $meta = [
            'title' => (string)($cfg['page_title'] ?? ($gameName . ' Top Ups')) . ' | LoLBoost',
            'h1' => (string)($cfg['page_title'] ?? ($gameName . ' Top Ups')),
            'description' => (string)($cfg['page_description'] ?? ('Buy ' . $gameName . ' top ups on LoLBoost.')),
            'canonical' => BASE_URL . '/' . $_dynSlug . '/top-ups',
            'robots' => 'index, follow',
        ];
        view_file('website/pages/topups/shop_dynamic', [
            'meta' => $meta,
            'game' => $_dynSlug,
            'gameConfig' => $game,
            'topupsConfig' => $cfg,
            'topupSchema' => $schema,
            'topupRegions' => $topupRegions ?? [],
            'selectedTopupRegion' => $selectedTopupRegion ?? '',
            'topups' => $rows,
        ]);
    });
}

if ($_g && function_exists('util_game_has_service') && util_game_has_service((int)$_g['id'], 'items')) {
    $router->get('/items', function () use ($_dynSlug) {
        global $db;

        $game = function_exists('util_get_game_by_slug') ? util_get_game_by_slug($_dynSlug) : [];
        $gameId = (int)($game['id'] ?? 0);
        $gameName = (string)($game['name'] ?? ucwords(str_replace('-', ' ', $_dynSlug)));

        $items = [];
        try {
            $items = $db->run(
                "SELECT si.*,
                        s.username AS seller_username,
                        s.slug AS seller_slug,
                        s.icon AS seller_icon,
                        s.rank AS seller_rank,
                        s.rank_icon AS seller_rank_icon,
                        s.is_active AS seller_is_active,
                        (
                            COALESCE((SELECT COUNT(*) FROM selling_accounts sa2 WHERE sa2.seller_id = s.id AND sa2.sold = 1), 0)
                            + COALESCE((SELECT SUM(si2.sold_count) FROM selling_items si2 WHERE si2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(st2.sold_count) FROM selling_topups st2 WHERE st2.seller_id = s.id), 0)
                            + COALESCE((SELECT SUM(dg2.sold_count) FROM digital_goods dg2 WHERE dg2.seller_id = s.id), 0)
                            + CASE
                                WHEN s.id = 28 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 51 AND a2.status = 1), 0)
                                WHEN s.id = 1 THEN COALESCE((SELECT COUNT(*) FROM accounts a2 WHERE a2.admin_id = 2 AND a2.status = 1), 0)
                                ELSE 0
                              END
                        ) AS seller_total_sales
                 FROM selling_items si
                 LEFT JOIN sellers s ON s.id = si.seller_id
                 WHERE ((? > 0 AND si.game_id = ?) OR LOWER(TRIM(COALESCE(si.game,''))) = LOWER(TRIM(?))) AND si.active = 1
                 ORDER BY si.created_at DESC",
                $gameId,
                $gameId,
                $_dynSlug
            ) ?: [];
        } catch (Throwable $e) {
            $items = $db->run(
                "SELECT si.*,
                        s.username AS seller_username,
                        s.slug AS seller_slug,
                        s.icon AS seller_icon,
                        s.rank AS seller_rank,
                        s.rank_icon AS seller_rank_icon,
                        s.is_active AS seller_is_active
                 FROM selling_items si
                 LEFT JOIN sellers s ON s.id = si.seller_id
                 WHERE si.game = ? AND si.active = 1
                 ORDER BY si.created_at DESC",
                $_dynSlug
            ) ?: [];
        }

        $pagination = [
            'page' => 1,
            'totalPages' => 1,
            'itemsPerPage' => max(1, count($items)),
            'totalItems' => count($items),
        ];

        $meta = [
            'title' => $gameName . ' Items | LoLBoost',
            'h1' => $gameName . ' Items',
            'description' => 'Browse ' . $gameName . ' items, skins and digital goods on LoLBoost.',
            'canonical' => BASE_URL . '/' . $_dynSlug . '/items',
            'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        ];

        view_file('website/pages/items/shop_dynamic', [
            'meta' => $meta,
            'game' => $_dynSlug,
            'gameConfig' => $game,
            'itemsConfig' => function_exists('lb_get_items_page_config') ? lb_get_items_page_config($_dynSlug) : [],
            'itemSchema' => function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($_dynSlug) : [],
            'items' => $items,
            'pagination' => $pagination,
        ]);
    });

    $router->get('/item/:slug', function ($slug) use ($_dynSlug) {
        global $db;

        $raw = trim(rawurldecode((string)$slug));
        if ($raw === '') {
            redirect_url($_dynSlug . '/items');
            return;
        }

        $game = function_exists('util_get_game_by_slug') ? util_get_game_by_slug($_dynSlug) : [];
        $gameId = (int)($game['id'] ?? 0);
        $gameName = (string)($game['name'] ?? ucwords(str_replace('-', ' ', $_dynSlug)));

        $item = null;

        try {
            if (ctype_digit($raw)) {
                $item = $db->row(
                    "SELECT si.*,
                            s.username AS seller_username,
                            s.slug AS seller_slug,
                            s.icon AS seller_icon,
                            s.rank AS seller_rank,
                            s.rank_icon AS seller_rank_icon,
                            s.is_active AS seller_is_active,
                            s.allow_chat_requests AS allow_chat_requests
                     FROM selling_items si
                     LEFT JOIN sellers s ON s.id = si.seller_id
                     WHERE si.id = ? AND ((? > 0 AND si.game_id = ?) OR LOWER(TRIM(COALESCE(si.game,''))) = LOWER(TRIM(?))) AND si.active = 1
                     LIMIT 1",
                    (int)$raw,
                    $gameId,
                    $gameId,
                    $_dynSlug
                );
            }

            if (!$item) {
                $item = $db->row(
                    "SELECT si.*,
                            s.username AS seller_username,
                            s.slug AS seller_slug,
                            s.icon AS seller_icon,
                            s.rank AS seller_rank,
                            s.rank_icon AS seller_rank_icon,
                            s.is_active AS seller_is_active,
                            s.allow_chat_requests AS allow_chat_requests
                     FROM selling_items si
                     LEFT JOIN sellers s ON s.id = si.seller_id
                     WHERE LOWER(TRIM(si.slug)) = LOWER(TRIM(?)) AND ((? > 0 AND si.game_id = ?) OR LOWER(TRIM(COALESCE(si.game,''))) = LOWER(TRIM(?))) AND si.active = 1
                     LIMIT 1",
                    $raw,
                    $gameId,
                    $gameId,
                    $_dynSlug
                );
            }
        } catch (Throwable $e) {
            if (ctype_digit($raw)) {
                $item = $db->row(
                    "SELECT si.*,
                            s.username AS seller_username,
                            s.slug AS seller_slug,
                            s.icon AS seller_icon,
                            s.rank AS seller_rank,
                            s.rank_icon AS seller_rank_icon,
                            s.is_active AS seller_is_active,
                            s.allow_chat_requests AS allow_chat_requests
                     FROM selling_items si
                     LEFT JOIN sellers s ON s.id = si.seller_id
                     WHERE si.id = ? AND si.game = ? AND si.active = 1
                     LIMIT 1",
                    (int)$raw,
                    $_dynSlug
                );
            }

            if (!$item) {
                $item = $db->row(
                    "SELECT si.*,
                            s.username AS seller_username,
                            s.slug AS seller_slug,
                            s.icon AS seller_icon,
                            s.rank AS seller_rank,
                            s.rank_icon AS seller_rank_icon,
                            s.is_active AS seller_is_active,
                            s.allow_chat_requests AS allow_chat_requests
                     FROM selling_items si
                     LEFT JOIN sellers s ON s.id = si.seller_id
                     WHERE LOWER(TRIM(si.slug)) = LOWER(TRIM(?)) AND si.game = ? AND si.active = 1
                     LIMIT 1",
                    $raw,
                    $_dynSlug
                );
            }
        }

        if (empty($item)) {
            redirect_url($_dynSlug . '/items');
            return;
        }

        $seller = null;
        $seller_items = [];

        if (!empty($item['seller_id'])) {
            $sellerId = (int)$item['seller_id'];
            $seller = $db->row(
                "SELECT id, username, icon, rank, rank_icon, slug, is_active, allow_chat_requests
                 FROM sellers
                 WHERE id = ?
                 LIMIT 1",
                $sellerId
            );

            try {
                $seller_items = $db->run(
                    "SELECT id, title, slug, images, price, type, game, game_id, server, stock, requires_friendship_days
                     FROM selling_items
                     WHERE seller_id = ? AND active = 1 AND id != ? AND ((? > 0 AND game_id = ?) OR LOWER(TRIM(COALESCE(game,''))) = LOWER(TRIM(?)))
                     ORDER BY created_at DESC
                     LIMIT 8",
                    $sellerId,
                    (int)$item['id'],
                    $gameId,
                    $gameId,
                    $_dynSlug
                ) ?: [];
            } catch (Throwable $e) {
                $seller_items = $db->run(
                    "SELECT id, title, slug, images, price, type, game, server, stock, requires_friendship_days
                     FROM selling_items
                     WHERE seller_id = ? AND active = 1 AND id != ? AND game = ?
                     ORDER BY created_at DESC
                     LIMIT 8",
                    $sellerId,
                    (int)$item['id'],
                    $_dynSlug
                ) ?: [];
            }
        }

        $meta = [
            'title' => item_meta_display_text($item['title'] ?? null, $gameName . ' Item') . ' | LoLBoost',
            'h1' => $gameName . ' Item',
            'description' => !empty($item['description']) ? strip_tags((string)$item['description']) : 'Buy this ' . $gameName . ' item on LoLBoost.',
            'canonical' => BASE_URL . '/' . $_dynSlug . '/item/' . rawurlencode($item['slug'] ?: $item['id']),
            'robots' => 'index, follow',
        ];

        view_file('website/pages/items/view_generic', [
            'meta' => $meta,
            'item' => $item,
            'seller' => $seller,
            'seller_items' => $seller_items,
            'game' => $_dynSlug,
            'gameConfig' => $game,
            'itemsConfig' => function_exists('lb_get_items_page_config') ? lb_get_items_page_config($_dynSlug) : [],
            'itemSchema' => function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($_dynSlug) : [],
            'sellerChatAllowedInline' => !empty($seller['allow_chat_requests']) || !array_key_exists('allow_chat_requests', (array)$seller),
        ]);
    });
}

    });
}


// ── Services hub offer counters ──────────────────────────────────────────────
if (!function_exists('lb_service_game_aliases')) {
    function lb_service_game_aliases(array $game): array
    {
        $slug = strtolower(trim((string)($game['slug'] ?? '')));
        $aliases = [$slug];

        $map = [
            'league-of-legends' => ['league-of-legends', 'lol', 'leagu', 'league'],
            'valorant' => ['valorant', 'val', 'valor'],
            'teamfight-tactics' => ['teamfight-tactics', 'tft', 'teamf'],
        ];

        if (isset($map[$slug])) {
            $aliases = array_merge($aliases, $map[$slug]);
        }

        return array_values(array_unique(array_filter($aliases, static fn($v) => $v !== '')));
    }
}

if (!function_exists('lb_sql_in_placeholders')) {
    function lb_sql_in_placeholders(array $values): string
    {
        return implode(',', array_fill(0, max(1, count($values)), '?'));
    }
}

if (!function_exists('lb_count_service_offers')) {
    function lb_count_service_offers(array $game, string $serviceType): int
    {
        global $db;
        if (empty($db)) return 0;

        $slug   = strtolower(trim((string)($game['slug'] ?? '')));
        $gameId = (int)($game['id'] ?? 0);
        $name   = strtolower(trim((string)($game['name'] ?? '')));

        // Build all possible values stored in the `game` text column
        $aliases = lb_service_game_aliases($game);
        if ($name !== '' && !in_array($name, $aliases, true)) {
            $aliases[] = $name;
        }

        // Request-scoped batch caches. The /services/* hub pages call this once per game per
        // service type (dozens of games -> dozens of COUNT queries each). Load every game's
        // count for a given service type in one or two grouped queries the first time this
        // runs for that type, then serve every subsequent game from memory.
        static $accountCounts = null;   // [lower(game) => count]
        static $accountLolExtra = null; // count of rows with NULL/empty game (legacy LoL rows)
        static $itemCountsByGameId = null;
        static $itemCountsByGameKey = null;
        static $topupCountsByGameId = null;
        static $topupCountsByGameKey = null;

        try {
            switch ($serviceType) {
                case 'boosting':
                case 'coaching':
                    $forms = function_exists('util_load_game_boost_forms') ? (util_load_game_boost_forms($slug) ?: []) : [];
                    if ($serviceType === 'coaching') {
                        $forms = array_values(array_filter($forms, static function ($f) {
                            return stripos((string)($f['slug'] ?? ''), 'coaching') !== false
                                || stripos((string)($f['name'] ?? ''), 'coaching') !== false
                                || stripos((string)($f['type'] ?? ''), 'coaching') !== false;
                        }));
                    }
                    return count($forms);

                case 'accounts':
                    if ($accountCounts === null) {
                        $accountCounts = [];
                        $rows = $db->run(
                            "SELECT LOWER(COALESCE(game,'')) AS game_key, COUNT(*) AS cnt
                             FROM selling_accounts
                             WHERE COALESCE(sold,0) = 0 AND COALESCE(active,1) = 1
                             GROUP BY LOWER(COALESCE(game,''))"
                        ) ?: [];
                        foreach ($rows as $row) {
                            $key = (string)($row['game_key'] ?? '');
                            $accountCounts[$key] = (int)($row['cnt'] ?? 0);
                        }
                        $accountLolExtra = $accountCounts[''] ?? 0;
                    }

                    $count = 0;
                    foreach ($aliases as $alias) {
                        $count += $accountCounts[$alias] ?? 0;
                    }
                    if ($slug === 'league-of-legends') {
                        $count += $accountLolExtra;
                    }
                    return $count;

                case 'items':
                    if ($itemCountsByGameId === null) {
                        $itemCountsByGameId = [];
                        $rows = $db->run(
                            "SELECT game_id, COUNT(*) AS cnt FROM selling_items
                             WHERE COALESCE(active,1) = 1 AND game_id IS NOT NULL AND game_id != 0
                             GROUP BY game_id"
                        ) ?: [];
                        foreach ($rows as $row) {
                            $itemCountsByGameId[(int)($row['game_id'] ?? 0)] = (int)($row['cnt'] ?? 0);
                        }

                        $itemCountsByGameKey = [];
                        $rows = $db->run(
                            "SELECT LOWER(COALESCE(game,'')) AS game_key, COUNT(*) AS cnt FROM selling_items
                             WHERE COALESCE(active,1) = 1
                             GROUP BY LOWER(COALESCE(game,''))"
                        ) ?: [];
                        foreach ($rows as $row) {
                            $itemCountsByGameKey[(string)($row['game_key'] ?? '')] = (int)($row['cnt'] ?? 0);
                        }
                    }

                    if ($gameId > 0 && !empty($itemCountsByGameId[$gameId])) {
                        return $itemCountsByGameId[$gameId];
                    }
                    $count = 0;
                    foreach ($aliases as $alias) {
                        $count += $itemCountsByGameKey[$alias] ?? 0;
                    }
                    return $count;

                case 'topups':
                case 'top-ups':
                case 'currencies':
                    if ($topupCountsByGameId === null) {
                        $topupCountsByGameId = [];
                        $rows = $db->run(
                            "SELECT game_id, COUNT(*) AS cnt FROM selling_topups
                             WHERE COALESCE(active,1) = 1 AND game_id IS NOT NULL AND game_id != 0
                             GROUP BY game_id"
                        ) ?: [];
                        foreach ($rows as $row) {
                            $topupCountsByGameId[(int)($row['game_id'] ?? 0)] = (int)($row['cnt'] ?? 0);
                        }

                        $topupCountsByGameKey = [];
                        $rows = $db->run(
                            "SELECT LOWER(COALESCE(game_slug,'')) AS game_key, COUNT(*) AS cnt FROM selling_topups
                             WHERE COALESCE(active,1) = 1
                             GROUP BY LOWER(COALESCE(game_slug,''))"
                        ) ?: [];
                        foreach ($rows as $row) {
                            $topupCountsByGameKey[(string)($row['game_key'] ?? '')] = (int)($row['cnt'] ?? 0);
                        }
                    }

                    if ($gameId > 0 && !empty($topupCountsByGameId[$gameId])) {
                        return $topupCountsByGameId[$gameId];
                    }
                    $count = 0;
                    foreach ($aliases as $alias) {
                        $count += $topupCountsByGameKey[$alias] ?? 0;
                    }
                    return $count;
            }
        } catch (Throwable $e) {
            return 0;
        }

        return 0;
    }
}

// ── /services/* hub pages ─────────────────────────────────────────────────────

$router->get('/services/boosting', function () {

    if (lb_public_page_cache_serve('services-boosting', 180)) return;
    $__pageCache = lb_public_page_cache_start();

    $allGames = util_get_all_games();
    $games = [];
    foreach ($allGames as $g) {
        if (!util_game_has_service((int)$g['id'], 'boosting')) continue;
        // Every game card in the Boosting Hub opens the rank-boost page.
        // Do not fall back to the neutral game overview (/<game>).
        $href = '/' . trim((string)$g['slug'], '/') . '/rank-boost/';
        $games[] = [
            'slug'   => $g['slug'],
            'name'   => $g['name'] ?? $g['slug'],
            'icon'   => $g['icon'] ?? null,
            'banner' => $g['banner'] ?? null,
            'href'   => $href,
            'is_new' => !empty($g['is_new']),
            'active_offers' => lb_count_service_offers($g, 'boosting'),
        ];
    }

    // LoL Classic has its own boost forms and public routes, but is intentionally
    // not dependent on a separate games-table row. Keep it visible in the
    // Boosting Hub and place it directly after League of Legends.
    $hasLolClassic = false;
    foreach ($games as $game) {
        if (in_array(strtolower((string)($game['slug'] ?? '')), ['lol-classic', 'lol_classic'], true)) {
            $hasLolClassic = true;
            break;
        }
    }
    if (!$hasLolClassic) {
        $classicGame = [
            'id' => 0,
            'slug' => 'lol-classic',
            'name' => 'LoL Classic',
        ];
        $classicCard = [
            'slug' => 'lol-classic',
            'name' => 'LoL Classic',
            'icon' => '/public/assets/website/images/icons/lol-classic.png',
            'banner' => '/public/assets/website/images/banner/lol-classic.webp',
            'href' => '/lol-classic/rank-boost/',
            'is_new' => true,
            'active_offers' => lb_count_service_offers($classicGame, 'boosting'),
        ];

        $insertAt = count($games);
        foreach ($games as $index => $game) {
            if (strtolower((string)($game['slug'] ?? '')) === 'league-of-legends') {
                $insertAt = $index + 1;
                break;
            }
        }
        array_splice($games, $insertAt, 0, [$classicCard]);
    }

    $meta = [
        'title'       => 'Game Boosting | Fast & Safe Rank Boost | LoLBoost',
        'description' => 'Boost your rank in League of Legends, Valorant, TFT and more. Fast, safe and professional boosting by verified top-ranked players.',
        'canonical'   => BASE_URL . '/services/boosting',
        'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'          => 'Boosting',
        'subtitle'    => 'Fast & safe rank boost across all games',
        'icon_class'  => 'fa-solid fa-rocket',
        'service_type'=> 'boosting',
    ];

    view_file('website/pages/services/hub', compact('meta', 'games'));
    lb_public_page_cache_finish('services-boosting', $__pageCache);
});

$router->get('/services/accounts', function () {

    if (lb_public_page_cache_serve('services-accounts', 120)) return;
    $__pageCache = lb_public_page_cache_start();

    $allGames = util_get_all_games();
    $games = [];
    foreach ($allGames as $g) {
        if (!util_game_has_service((int)$g['id'], 'accounts')) continue;
        $href = '/' . $g['slug'] . '/accounts';
        $games[] = [
            'slug'   => $g['slug'],
            'name'   => $g['name'] ?? $g['slug'],
            'icon'   => $g['icon'] ?? null,
            'banner' => $g['banner'] ?? null,
            'href'   => $href,
            'is_new' => !empty($g['is_new']),
            'active_offers' => lb_count_service_offers($g, 'accounts'),
        ];
    }

    $meta = [
        'title'       => 'Buy & Sell Game Accounts | Verified Sellers | LoLBoost',
        'description' => 'Buy and sell gaming accounts for all popular games. Verified sellers, instant delivery and secure transactions.',
        'canonical'   => BASE_URL . '/services/accounts',
        'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'          => 'Accounts',
        'subtitle'    => 'Buy & sell verified game accounts',
        'icon_class'  => 'fa-solid fa-box-archive',
        'service_type'=> 'accounts',
    ];

    view_file('website/pages/services/hub', compact('meta', 'games'));
    lb_public_page_cache_finish('services-accounts', $__pageCache);
});

$router->get('/services/items', function () {

    if (lb_public_page_cache_serve('services-items', 120)) return;
    $__pageCache = lb_public_page_cache_start();

    $allGames = util_get_all_games();
    $games = [];
    foreach ($allGames as $g) {
        if (!util_game_has_service((int)$g['id'], 'items')) continue;
        $href = '/' . $g['slug'] . '/items';
        $games[] = [
            'slug'   => $g['slug'],
            'name'   => $g['name'] ?? $g['slug'],
            'icon'   => $g['icon'] ?? null,
            'banner' => $g['banner'] ?? null,
            'href'   => $href,
            'is_new' => !empty($g['is_new']),
            'active_offers' => lb_count_service_offers($g, 'items'),
        ];
    }

    $meta = [
        'title'       => 'Buy Game Items & Skins | All Games | LoLBoost',
        'description' => 'Browse and buy in-game items, skins and cosmetics for all popular games. Safe checkout, instant delivery and trusted sellers.',
        'canonical'   => BASE_URL . '/services/items',
        'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'          => 'Items',
        'subtitle'    => 'Browse items & skins across all games',
        'icon_class'  => 'fa-solid fa-gem',
        'service_type'=> 'items',
    ];

    view_file('website/pages/services/hub', compact('meta', 'games'));
    lb_public_page_cache_finish('services-items', $__pageCache);
});

$router->get('/services/coaching', function () {

    if (lb_public_page_cache_serve('services-coaching', 180)) return;
    $__pageCache = lb_public_page_cache_start();

    // Slugs that always have coaching even if not in game_services DB table
    $coachingAlwaysOn = ['league-of-legends', 'valorant', 'teamfight-tactics'];

    $allGames = util_get_all_games();
    $seen = [];
    $games = [];
    foreach ($allGames as $g) {
        $slug = $g['slug'];
        $hasService = util_game_has_service((int)$g['id'], 'coaching')
                   || in_array($slug, $coachingAlwaysOn, true);
        if (!$hasService) continue;
        if (isset($seen[$slug])) continue;
        $seen[$slug] = true;

        $forms = util_load_game_boost_forms($slug);
        $coachForm = null;
        foreach ($forms as $f) {
            if (strpos($f['slug'] ?? '', 'coaching') !== false) { $coachForm = $f; break; }
        }
        $href = $coachForm ? ($coachForm['href'] ?? '/' . $slug . '/coaching') : '/' . $slug . '/coaching';
        $games[] = [
            'slug'   => $slug,
            'name'   => $g['name'] ?? $slug,
            'icon'   => $g['icon'] ?? null,
            'banner' => $g['banner'] ?? null,
            'href'   => $href,
            'is_new' => !empty($g['is_new']),
            'active_offers' => lb_count_service_offers($g, 'coaching'),
        ];
    }

    $meta = [
        'title'       => 'Game Coaching | 1-on-1 Sessions with Pro Players | LoLBoost',
        'description' => 'Level up your gameplay with personalised coaching from verified professional players. Available for LoL, Valorant, TFT and more.',
        'canonical'   => BASE_URL . '/services/coaching',
        'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'          => 'Coaching',
        'subtitle'    => '1-on-1 coaching from pro players',
        'icon_class'  => 'fa-solid fa-chalkboard-user',
        'service_type'=> 'coaching',
    ];

    view_file('website/pages/services/hub', compact('meta', 'games'));
    lb_public_page_cache_finish('services-coaching', $__pageCache);
});

$router->get('/services/currencies', function () {

    // No games with currency service yet — show empty hub
    $games = [];

    $meta = [
        'title'       => 'Buy Game Currencies | Coins, Gold & More | LoLBoost',
        'description' => 'Buy in-game currencies for all popular games. Riot Points, V-Bucks, Robux and more — safe, instant and at the best prices.',
        'canonical'   => BASE_URL . '/services/currencies',
        'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'          => 'Game currency',
        'subtitle'    => 'Top up your in-game balance instantly',
        'icon_class'  => 'fa-solid fa-coins',
        'service_type'=> 'currencies',
    ];

    view_file('website/pages/services/hub', compact('meta', 'games'));
});

$router->get('/services/top-ups', function () {

    // No games with top-up service yet — show empty hub
    $games = [];

    $meta = [
        'title'       => 'Game Top-ups | Instant In-Game Balance | LoLBoost',
        'description' => 'Instantly top up your in-game balance. V-Bucks, Riot Points, Robux and more — fast delivery, best prices, 100% safe.',
        'canonical'   => BASE_URL . '/services/top-ups',
        'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'          => 'Top-ups',
        'subtitle'    => 'Instantly top up your in-game balance',
        'icon_class'  => 'fa-solid fa-bolt',
        'service_type'=> 'top-ups',
    ];

    view_file('website/pages/services/hub', compact('meta', 'games'));
});

// ─────────────────────────────────────────────────────────────────────────────

// ══════════════════════════════════════════════════════════════════════════════
//  DIGITAL GOODS — Public Shop, Detail, Seller Dashboard, Client Orders
// ══════════════════════════════════════════════════════════════════════════════


if (!function_exists('lb_dg_paid_statuses')) {
    function lb_dg_paid_statuses(): array
    {
        return ['PAID', 'DELIVERED', 'COMPLETED'];
    }
}

if (!function_exists('lb_dg_extract_invoice_quantity')) {
    function lb_dg_extract_invoice_quantity(array $invoice, int $unitPriceCents = 0): int
    {
        $desc = (string)($invoice['description'] ?? '');
        if (preg_match('/x(\d+)\s*$/i', $desc, $m)) {
            return max(1, (int)$m[1]);
        }

        $total = (int)($invoice['price_eur'] ?? 0);
        if ($total <= 0) {
            $total = (int)($invoice['total_price'] ?? 0);
        }
        if ($unitPriceCents > 0 && $total > 0) {
            return max(1, (int)round($total / $unitPriceCents));
        }

        return 1;
    }
}

if (!function_exists('lb_dg_mark_invoice_paid')) {
    function lb_dg_mark_invoice_paid(array $invoice): int
    {
        global $db;

        if (($invoice['order_type'] ?? '') !== 'digital_good' || empty($invoice['id'])) {
            return 0;
        }

        $invoiceId = (int)$invoice['id'];
        $listingId = (int)($invoice['order_id'] ?? 0);
        $clientId = (int)($invoice['client_id'] ?? 0);
        $now = date('Y-m-d H:i:s');

        // Keep invoice itself consistent first.
        db_update_row('invoices', ['id' => $invoiceId], [
            'status' => 'PAID',
            'paid_at' => !empty($invoice['paid_at']) ? $invoice['paid_at'] : $now,
        ]);

        $listing = null;
        if ($listingId > 0) {
            $listing = function_exists('dg_get_listing')
                ? dg_get_listing($listingId)
                : db_get_row('digital_goods', ['id' => $listingId], 1);
        }
        if (!$listing || !is_array($listing)) {
            return 0;
        }

        $unit = (int)($listing['price'] ?? 0);
        $qty = lb_dg_extract_invoice_quantity($invoice, $unit);
        $total = $unit > 0 ? ($unit * $qty) : (int)($invoice['price_eur'] ?? $invoice['total_price'] ?? 0);
        $sellerId = (int)($listing['seller_id'] ?? 0);
        $currency = $invoice['currency'] ?? 'EUR';

        $existing = $db->row("SELECT * FROM digital_good_purchases WHERE invoice_id = ? LIMIT 1", $invoiceId);

        if ($existing && is_array($existing)) {
            $purchaseId = (int)$existing['id'];
            $wasUnpaid = strtoupper((string)($existing['status'] ?? 'UNPAID')) === 'UNPAID';

            db_update_row('digital_good_purchases', ['id' => $purchaseId], [
                'item_id' => $listingId,
                'seller_id' => $sellerId,
                'client_id' => $clientId ?: ($existing['client_id'] ?? null),
                'invoice_id' => $invoiceId,
                'quantity' => max(1, (int)($existing['quantity'] ?? $qty ?: 1)),
                'unit_price' => (int)($existing['unit_price'] ?? $unit ?: $unit),
                'price' => (int)($existing['price'] ?? $total ?: $total),
                'currency' => $currency,
                'status' => in_array(strtoupper((string)($existing['status'] ?? '')), ['DELIVERED', 'COMPLETED'], true)
                    ? strtoupper((string)$existing['status'])
                    : 'PAID',
                'paid_at' => !empty($existing['paid_at']) ? $existing['paid_at'] : (!empty($invoice['paid_at']) ? $invoice['paid_at'] : $now),
                'updated_at' => $now,
            ]);

            if ($wasUnpaid && function_exists('dg_chat_append')) {
                dg_chat_append($purchaseId, 'system', 0, 'System', '✅ Payment confirmed! The seller has been notified and will deliver shortly.');
            }

            return $purchaseId;
        }

        $purchaseId = (int)db_add_row('digital_good_purchases', [
            'item_id' => $listingId,
            'seller_id' => $sellerId,
            'client_id' => $clientId ?: null,
            'invoice_id' => $invoiceId,
            'quantity' => $qty,
            'unit_price' => $unit,
            'price' => $total,
            'currency' => $currency,
            'status' => 'PAID',
            'paid_at' => !empty($invoice['paid_at']) ? $invoice['paid_at'] : $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Only decrement stock on first purchase creation for this invoice.
        try {
            $db->run(
                "UPDATE digital_goods SET stock = GREATEST(0, COALESCE(stock, 0) - ?), sold_count = COALESCE(sold_count, 0) + ? WHERE id = ?",
                $qty,
                $qty,
                $listingId
            );
            if (!empty($listing['seller_id']) && function_exists('sync_seller_stats')) sync_seller_stats((int)$listing['seller_id']);
        } catch (Throwable $e) {}

        if ($purchaseId > 0 && function_exists('dg_chat_append')) {
            dg_chat_append($purchaseId, 'system', 0, 'System', '✅ Payment confirmed! The seller has been notified and will deliver shortly.');
        }

        return $purchaseId;
    }
}

if (!function_exists('lb_dg_sync_paid_purchases')) {
    function lb_dg_sync_paid_purchases(?int $clientId = null, ?int $sellerId = null): void
    {
        global $db;

        // First, fix existing purchases whose invoice is paid.
        $where = "i.order_type = 'digital_good' AND i.status = 'PAID'";
        $params = [];
        if ($clientId !== null) {
            $where .= " AND (dgp.client_id = ? OR i.client_id = ?)";
            $params[] = $clientId;
            $params[] = $clientId;
        }
        if ($sellerId !== null) {
            $where .= " AND dgp.seller_id = ?";
            $params[] = $sellerId;
        }

        try {
            $db->run(
                "UPDATE digital_good_purchases dgp
                 INNER JOIN invoices i ON i.id = dgp.invoice_id
                 SET dgp.status = CASE
                        WHEN dgp.status IN ('DELIVERED', 'COMPLETED') THEN dgp.status
                        ELSE 'PAID'
                     END,
                     dgp.paid_at = COALESCE(dgp.paid_at, i.paid_at, NOW()),
                     dgp.client_id = CASE
                        WHEN (dgp.client_id IS NULL OR dgp.client_id = 0) THEN i.client_id
                        ELSE dgp.client_id
                     END,
                     dgp.updated_at = NOW()
                 WHERE {$where}",
                ...$params
            );
        } catch (Throwable $e) {}

        // Then, create missing purchases for paid invoices. This covers old broken checkouts.
        $where2 = "i.order_type = 'digital_good' AND i.status = 'PAID'";
        $params2 = [];
        if ($clientId !== null) {
            $where2 .= " AND i.client_id = ?";
            $params2[] = $clientId;
        }
        if ($sellerId !== null) {
            $where2 .= " AND dg.seller_id = ?";
            $params2[] = $sellerId;
        }

        try {
            $rows = $db->run(
                "SELECT i.*
                 FROM invoices i
                 LEFT JOIN digital_good_purchases dgp ON dgp.invoice_id = i.id
                 LEFT JOIN digital_goods dg ON dg.id = i.order_id
                 WHERE {$where2} AND dgp.id IS NULL
                 ORDER BY i.id DESC
                 LIMIT 100",
                ...$params2
            ) ?: [];

            foreach ($rows as $invoice) {
                if (is_array($invoice)) {
                    lb_dg_mark_invoice_paid($invoice);
                }
            }
        } catch (Throwable $e) {}
    }
}


if (!function_exists('lb_dg_service_cards')) {
    function lb_dg_service_cards(): array
    {
        $categories = function_exists('dg_get_categories') ? (dg_get_categories() ?: []) : [];
        $games = [];

        foreach ($categories as $cat) {
            $categoryId = (int)($cat['id'] ?? 0);
            $offers = 0;
            if ($categoryId > 0 && function_exists('dg_count_listings')) {
                try {
                    $offers = (int)dg_count_listings($categoryId);
                } catch (Throwable $e) {
                    $offers = 0;
                }
            }

            $games[] = [
                'slug'        => (string)($cat['slug'] ?? ''),
                'name'        => (string)($cat['name'] ?? 'Digital Goods'),
                'icon'        => (string)($cat['icon'] ?? 'fa-solid fa-layer-group'),
                'banner'      => (string)($cat['banner'] ?? ''),
                'description' => (string)($cat['description'] ?? ''),
                'href'        => BASE_URL . '/digital-goods/' . rawurlencode((string)($cat['slug'] ?? '')),
                'is_new'      => false,
                'active_offers' => $offers,
                'subtitle'    => $offers > 0 ? ($offers . ' offers available') : 'Browse offers',
            ];
        }

        // Fallback: if categories are missing but offers exist, keep the hub useful.
        if (empty($games)) {
            try {
                global $db;
                if (!empty($db)) {
                    $rows = $db->run("SELECT DISTINCT COALESCE(dgc.id, 0) AS id, COALESCE(dgc.name, 'Digital Goods') AS name, COALESCE(dgc.slug, 'all') AS slug, COALESCE(dgc.icon, 'fa-solid fa-layer-group') AS icon FROM digital_goods dg LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id WHERE dg.active = 1 AND dg.stock > 0 ORDER BY name ASC") ?: [];
                    foreach ($rows as $row) {
                        $games[] = [
                            'slug' => (string)($row['slug'] ?? 'all'),
                            'name' => (string)($row['name'] ?? 'Digital Goods'),
                            'icon' => (string)($row['icon'] ?? 'fa-solid fa-layer-group'),
                            'banner' => '',
                            'description' => '',
                            'href' => BASE_URL . '/digital-goods/' . rawurlencode((string)($row['slug'] ?? 'all')),
                            'is_new' => false,
                            'subtitle' => 'Browse offers',
                        ];
                    }
                }
            } catch (Throwable $e) {}
        }

        return $games;
    }
}

// /services/digital-goods  (Digital-Goods-Kategorien im bestehenden Services-Hub)
$router->get('/services/digital-goods', function () {
    $games = lb_dg_service_cards();
    $meta = [
        'title'        => 'Digital Goods | Subscriptions & more | LoLBoost.gg',
        'description'  => 'Buy streaming subscriptions, software keys and more. Instant delivery, secure checkout.',
        'canonical'    => BASE_URL . '/services/digital-goods',
        'robots'       => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'           => 'Digital Goods',
        'subtitle'     => 'Subscriptions, streaming and more',
        'icon_class'   => 'fa-solid fa-layer-group',
        'service_type' => 'digital-goods',
    ];
    view_file('website/pages/services/hub', compact('meta', 'games'));
});

// /digital-goods  (gleiche Kategorien-Übersicht wie /services/digital-goods)
$router->get('/digital-goods', function () {
    $games = lb_dg_service_cards();
    $meta = [
        'title'        => 'Digital Goods | LoLBoost.gg',
        'description'  => 'Browse streaming subscriptions, software and more. Safe, fast delivery.',
        'canonical'    => BASE_URL . '/digital-goods',
        'robots'       => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'           => 'Digital Goods',
        'subtitle'     => 'Subscriptions, streaming and more',
        'icon_class'   => 'fa-solid fa-layer-group',
        'service_type' => 'digital-goods',
    ];
    view_file('website/pages/services/hub', compact('meta', 'games'));
});

// Digital Good detail pages use a singular, account-style URL now:
//   /digital-good/:slug
// This avoids route collisions with category shop URLs such as /digital-goods/software.
if (!function_exists('lb_dg_slugify_value')) {
    function lb_dg_slugify_value($value): string
    {
        $value = trim((string)$value);
        if ($value === '') return '';
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) $value = $converted;
        }
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim((string)$value, '-');
    }
}

if (!function_exists('lb_dg_public_slug')) {
    function lb_dg_public_slug(array $listing): string
    {
        $id = (int)($listing['id'] ?? 0);
        $slug = trim((string)($listing['slug'] ?? ''));
        if ($slug === '') {
            $slug = lb_dg_slugify_value($listing['title'] ?? '');
        }
        if ($slug === '' && $id > 0) {
            $slug = (string)$id;
        }
        if ($id > 0 && !preg_match('/-' . preg_quote((string)$id, '/') . '$/', $slug)) {
            $slug .= '-' . $id;
        }
        return $slug;
    }
}

if (!function_exists('lb_dg_load_listing_for_view')) {
    function lb_dg_load_listing_for_view($identifier): ?array
    {
        global $db;
        if (empty($db)) return null;

        $raw = trim(rawurldecode((string)$identifier));
        if ($raw === '') return null;

        $candidates = [];
        $addCandidate = static function ($value) use (&$candidates): void {
            if ($value === null || $value === '') return;
            $key = is_int($value) ? 'i:' . $value : 's:' . (string)$value;
            if (!isset($candidates[$key])) $candidates[$key] = $value;
        };

        // Same idea as selling_accounts: a single stable slug identifies the row.
        // For generated slugs like t-1, prefer the trailing numeric ID because it is unique.
        if (ctype_digit($raw)) {
            $addCandidate((int)$raw);
        }
        if (preg_match('/-(\d+)$/', $raw, $m)) {
            $addCandidate((int)$m[1]);
            $baseSlug = substr($raw, 0, -strlen($m[0]));
            $addCandidate($baseSlug);
        }
        $addCandidate($raw);

        foreach ($candidates as $candidate) {
            try {
                if (is_int($candidate) || (is_string($candidate) && ctype_digit($candidate))) {
                    $row = $db->row(
                        "SELECT dg.*,
                                dgc.slug AS category_slug,
                                dgc.name AS category_name,
                                dgc.description AS category_description,
                                s.username AS seller_username,
                                s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon,
                                s.slug AS seller_slug,
                                s.rank AS seller_rank,
                                s.rank_icon AS seller_rank_icon,
                                s.is_active AS seller_is_active,
                                COALESCE(AVG(dgr.rating), 0) AS avg_rating,
                                COUNT(dgr.id) AS review_count
                         FROM digital_goods dg
                         LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id
                         LEFT JOIN sellers s ON s.id = dg.seller_id
                         LEFT JOIN seller_reviews dgr ON dgr.digital_good_id = dg.id AND dgr.review_source = 'digital_good' AND dgr.approved = 1
                         WHERE dg.id = ? AND COALESCE(dg.active, 1) = 1
                         GROUP BY dg.id
                         LIMIT 1",
                        (int)$candidate
                    );
                } else {
                    $row = $db->row(
                        "SELECT dg.*,
                                dgc.slug AS category_slug,
                                dgc.name AS category_name,
                                dgc.description AS category_description,
                                s.username AS seller_username,
                                s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon,
                                s.slug AS seller_slug,
                                s.rank AS seller_rank,
                                s.rank_icon AS seller_rank_icon,
                                s.is_active AS seller_is_active,
                                COALESCE(AVG(dgr.rating), 0) AS avg_rating,
                                COUNT(dgr.id) AS review_count
                         FROM digital_goods dg
                         LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id
                         LEFT JOIN sellers s ON s.id = dg.seller_id
                         LEFT JOIN seller_reviews dgr ON dgr.digital_good_id = dg.id AND dgr.review_source = 'digital_good' AND dgr.approved = 1
                         WHERE LOWER(TRIM(dg.slug)) = LOWER(TRIM(?)) AND COALESCE(dg.active, 1) = 1
                         GROUP BY dg.id
                         LIMIT 1",
                        (string)$candidate
                    );
                }

                if (!empty($row) && is_array($row)) {
                    return $row;
                }
            } catch (Throwable $e) {
                // Try next fallback.
            }
        }

        // Final compatibility fallback: old rows can have tiny or duplicated slugs.
        // Try matching the generated title slug, then return the newest exact match.
        try {
            $wanted = lb_dg_slugify_value($raw);
            if (preg_match('/-(\d+)$/', $wanted, $m)) {
                $wanted = substr($wanted, 0, -strlen($m[0]));
            }
            if ($wanted !== '') {
                $rows = $db->run(
                    "SELECT dg.*,
                            dgc.slug AS category_slug,
                            dgc.name AS category_name,
                            dgc.description AS category_description,
                            s.username AS seller_username,
                            s.slug AS seller_slug, s.icon AS seller_icon, s.rank AS seller_rank, s.rank_icon AS seller_rank_icon, s.is_active AS seller_is_active, s.icon AS seller_icon,
                            s.slug AS seller_slug,
                            s.rank AS seller_rank,
                            s.rank_icon AS seller_rank_icon,
                            s.is_active AS seller_is_active,
                            COALESCE(AVG(dgr.rating), 0) AS avg_rating,
                            COUNT(dgr.id) AS review_count
                     FROM digital_goods dg
                     LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id
                     LEFT JOIN sellers s ON s.id = dg.seller_id
                     LEFT JOIN seller_reviews dgr ON dgr.digital_good_id = dg.id AND dgr.review_source = 'digital_good' AND dgr.approved = 1
                     WHERE COALESCE(dg.active, 1) = 1
                     GROUP BY dg.id
                     ORDER BY dg.id DESC
                     LIMIT 500"
                ) ?: [];
                foreach ($rows as $row) {
                    if (lb_dg_slugify_value($row['title'] ?? '') === $wanted) {
                        return $row;
                    }
                }
            }
        } catch (Throwable $e) {}

        return null;
    }
}

if (!function_exists('lb_dg_show_listing_view')) {
    function lb_dg_show_listing_view($identifier, bool $redirectToCanonical = false): void
    {
        global $db;

        $listing = lb_dg_load_listing_for_view($identifier);
        if (!$listing || !(int)($listing['active'] ?? 1)) {
            http_response_code(404);
            view_file('website/pages/404');
            return;
        }

        $category = null;
        if (function_exists('dg_get_category')) {
            $category = dg_get_category((int)($listing['category_id'] ?? 0));
        }
        if (!$category) {
            $category = [
                'id' => (int)($listing['category_id'] ?? 0),
                'name' => $listing['category_name'] ?? 'Digital Goods',
                'slug' => $listing['category_slug'] ?? 'digital-goods',
                'description' => $listing['category_description'] ?? '',
            ];
        }

        $publicSlug = lb_dg_public_slug($listing);
        $canonicalPath = 'digital-good/' . rawurlencode($publicSlug);
        if ($redirectToCanonical) {
            redirect_url($canonicalPath);
            return;
        }

        $images = json_decode((string)($listing['images'] ?? '[]'), true);
        if (!is_array($images)) $images = [];

        $reviews = [];
        try {
            $reviews = $db->run(
                "SELECT dgr.*, c.username AS client_username, c.icon AS client_icon
                 FROM seller_reviews dgr
                 LEFT JOIN clients c ON c.id = dgr.client_id
                 WHERE dgr.digital_good_id=? AND dgr.review_source='digital_good' AND dgr.approved=1
                 ORDER BY dgr.created_at DESC LIMIT 20",
                (int)$listing['id']
            ) ?: [];
        } catch (Throwable $e) {
            $reviews = [];
        }

        $sellerListings = [];
        if (function_exists('dg_get_listings')) {
            try {
                $sellerListings = dg_get_listings((int)$listing['category_id'], ['seller_id' => (int)$listing['seller_id']], 4) ?: [];
            } catch (Throwable $e) {
                $sellerListings = [];
            }
        }
        $sellerListings = array_values(array_filter($sellerListings, fn($l) => (int)($l['id'] ?? 0) !== (int)$listing['id']));

        // Real total sold across ALL seller products (accounts + items + digital goods)
        $sellerTotalSold = 0;
        try {
            $sellerId = (int)($listing['seller_id'] ?? 0);
            if ($sellerId > 0) {
                $row = $db->row(
                    "SELECT
                        COALESCE((SELECT COUNT(*) FROM selling_accounts WHERE seller_id = ? AND sold = 1 AND client_id IS NOT NULL), 0)
                        + COALESCE((SELECT SUM(sold_count) FROM selling_items WHERE seller_id = ?), 0)
                        + COALESCE((SELECT SUM(sold_count) FROM digital_goods WHERE seller_id = ?), 0)
                     AS cnt",
                    $sellerId, $sellerId, $sellerId
                );
                $sellerTotalSold = (int)($row['cnt'] ?? 0);
            }
        } catch (Throwable $e) {
            $sellerTotalSold = 0;
        }

        $meta = [
            'title'       => esc($listing['title']) . ' | ' . esc($category['name'] ?? 'Digital Goods') . ' | LoLBoost.gg',
            'description' => strip_tags(substr((string)($listing['description'] ?? ''), 0, 160)),
            'canonical'   => BASE_URL . '/' . $canonicalPath,
            'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
            'og_image'    => $images[0] ?? null,
        ];

        $dgCategoryList = [];
        try {
            $dgCategoryList = $db->run("SELECT id, slug, name, icon, banner, description FROM digital_good_categories WHERE active=1 ORDER BY sort_order ASC, name ASC") ?: [];
        } catch (Throwable $e) { $dgCategoryList = []; }

        view_file('website/pages/digital-goods/view', compact('meta', 'category', 'listing', 'images', 'reviews', 'sellerListings', 'sellerTotalSold', 'dgCategoryList'));
    }
}

// New canonical product URL, like /league-of-legends/account/:slug.
$router->get('/digital-good/:slug', function (string $slug) {
    lb_dg_show_listing_view($slug, false);
});

// Compatibility URL if old buttons still link to /digital-goods/view/:slug.
$router->get('/digital-goods/view/:slug', function (string $slug) {
    lb_dg_show_listing_view($slug, true);
});

// Compatibility with old category/product URLs. They redirect to the new stable URL.
$router->get('/digital-goods/:cat/:slug', function (string $cat, string $slug) {
    lb_dg_show_listing_view($slug, true);
});

// /digital-goods/:category  (Kategorie-Listing = Shop)
$router->get('/digital-goods/:slug', function (string $slug) {
    if ($slug === 'checkout') { http_response_code(405); exit; }

    $category = dg_get_category($slug);
    if (!$category) {
        http_response_code(404);
        view_file('website/pages/404');
        return;
    }

    global $db;
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 24;
    $brand   = esc($_GET['brand']  ?? '');
    $region  = esc($_GET['region'] ?? '');
    $search  = esc($_GET['search'] ?? '');
    $sort    = esc($_GET['sort']   ?? 'recommended');
    $priceMin = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? max(0, (int)$_GET['price_min']) : 0;
    $priceMax = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? max(0, (int)$_GET['price_max']) : 0;

    $filters = array_filter([
        'brand' => $brand,
        'region' => $region,
        'search' => $search,
        'sort' => $sort,
    ], static fn($v) => $v !== '' && $v !== null);

    // Keep compatibility with the current dg_get_listings() helper and then apply
    // optional price filtering locally, because older helper versions do not know
    // price_min/price_max yet.
    $rawLimit = ($priceMin > 0 || $priceMax > 0) ? 500 : $perPage;
    $rawOffset = ($priceMin > 0 || $priceMax > 0) ? 0 : (($page - 1) * $perPage);
    $listings = dg_get_listings((int)$category['id'], $filters, $rawLimit, $rawOffset);
    $total    = dg_count_listings((int)$category['id'], $filters);

    if ($priceMin > 0 || $priceMax > 0) {
        $minCents = $priceMin * 100;
        $maxCents = $priceMax > 0 ? $priceMax * 100 : PHP_INT_MAX;
        $listings = array_values(array_filter($listings, static function ($listing) use ($minCents, $maxCents) {
            $price = (int)($listing['price'] ?? 0);
            return $price >= $minCents && $price <= $maxCents;
        }));
        $total = count($listings);
        $listings = array_slice($listings, ($page - 1) * $perPage, $perPage);
    }

    $brands   = $db->run("SELECT DISTINCT brand FROM digital_goods WHERE category_id=? AND active=1 AND brand IS NOT NULL AND brand!='' ORDER BY brand ASC", (int)$category['id']) ?: [];
    $regions  = $db->run("SELECT DISTINCT region FROM digital_goods WHERE category_id=? AND active=1 AND region IS NOT NULL AND region!='' ORDER BY region ASC", (int)$category['id']) ?: [];
    $pagination = ['page' => $page, 'totalPages' => max(1, (int)ceil($total / $perPage)), 'totalItems' => $total];

    $meta = [
        'title'       => $category['name'] . ' | Digital Goods | LoLBoost.gg',
        'description' => $category['description'] ?? 'Buy ' . $category['name'] . ' instantly. Secure checkout, fast delivery.',
        'canonical'   => BASE_URL . '/digital-goods/' . $category['slug'],
        'robots'      => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'h1'          => $category['name'],
    ];

    view_file('website/pages/digital-goods/shop', compact('meta', 'category', 'listings', 'brands', 'regions', 'pagination', 'page', 'perPage', 'brand', 'region', 'search', 'sort', 'priceMin', 'priceMax'));
});

// Legacy URLs — the client digital-goods area now lives under /profile/digital-goods.
$router->get('/account/digital-goods', function () {
    redirect_url('profile/digital-goods');
});
$router->get('/account/digital-goods/:id', function (string $id) {
    redirect_url('profile/digital-goods/' . (int)$id);
});

// /profile/digital-goods  (Client: Orders list)
$router->get('/profile/digital-goods', function () {
    if (!defined('CLIENT_ID') || (int)CLIENT_ID <= 0) { redirect_url('login'); }
    if (function_exists('dg_auto_complete_overdue_purchases')) {
        dg_auto_complete_overdue_purchases(250);
    }
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $status  = esc($_GET['status'] ?? '');
    lb_dg_sync_paid_purchases((int)CLIENT_ID, null);
    $purchases = dg_get_client_purchases((int)CLIENT_ID, $status, $perPage, ($page - 1) * $perPage);

    // Attach unread flag per purchase
    $chatDir = (defined('SYS_PATH') ? SYS_PATH : '') . '/public/uploads/private/chat';
    foreach ($purchases as &$p) {
        $path = dg_chat_path((int)$p['id']);
        $p['has_unread'] = false;
        if (file_exists($path)) {
            $data = json_decode(@file_get_contents($path) ?: '', true);
            foreach (($data['messages'] ?? []) as $m) {
                if (!is_array($m) || !empty($m['deleted'])) continue;
                $sender = $m['sender'] ?? $m['type'] ?? '';
                if ($sender !== 'client' && (int)($m['seen_by_client'] ?? 0) === 0) {
                    $p['has_unread'] = true; break;
                }
            }
        }
    }
    unset($p);

    $meta = ['title' => 'My Digital Goods | LoLBoost.gg', 'robots' => 'noindex, nofollow'];
    view_file('client/pages/digital-goods/list', compact('meta', 'purchases', 'page', 'perPage', 'status'));
});

// /profile/digital-goods/:id  (Client: Order-Detail + Chat)
$router->get('/profile/digital-goods/:id', function (string $id) {
    if (!defined('CLIENT_ID') || (int)CLIENT_ID <= 0) { redirect_url(''); }
    if (function_exists('dg_auto_complete_overdue_purchases')) {
        dg_auto_complete_overdue_purchases(250);
    }
    lb_dg_sync_paid_purchases((int)CLIENT_ID, null);
    $purchase = dg_get_purchase((int)$id);
    if (!$purchase || (int)$purchase['client_id'] !== (int)CLIENT_ID) {
        http_response_code(404); view_file('website/pages/404'); return;
    }
    $images = json_decode((string)($purchase['item_images'] ?? '[]'), true);
    if (!is_array($images)) $images = [];

    $already_reviewed = false;
    $can_review = false;
    if (!empty($purchase['seller_id'])) {
        global $db;
        $existingReviewRows = $db->run(
            "SELECT id FROM seller_reviews
             WHERE review_source = 'digital_good' AND source_purchase_id = ? AND client_id = ?
             LIMIT 1",
            (int)$purchase['id'],
            (int)CLIENT_ID
        ) ?: [];
        $already_reviewed = !empty($existingReviewRows);
        $can_review = strtoupper((string)($purchase['status'] ?? '')) === 'COMPLETED' && !$already_reviewed;
    }

    $meta = ['title' => 'Digital Good Order #' . $id . ' | LoLBoost.gg', 'robots' => 'noindex, nofollow'];
    view_file('client/pages/digital-goods/view', compact('meta', 'purchase', 'images', 'can_review', 'already_reviewed'));
});

// ── Seller Digital Goods permission check ─────────────────────────────────
// Centralised guard: seller must be logged in AND have can_list_digital_goods = 1.
// Order-detail pages are accessible without the flag (seller may have old orders
// even if the admin later disables the permission).
function dg_seller_check(bool $requirePermission = true): void {
    if (!defined('SELLER_ID') || (int)SELLER_ID <= 0) {
        redirect_url('seller-area/auth/login');
    }
    if ($requirePermission && empty(SELLER_DATA['can_list_digital_goods'])) {
        redirect_url('seller-area/dashboard');
    }
}

// /seller-area/digital-goods  (Seller: Orders verwalten)
$router->get('/seller-area/digital-goods', function () {
    dg_seller_check();
    $status    = esc($_GET['status'] ?? '');
    $page      = max(1, (int)($_GET['page'] ?? 1));
    $perPage   = 30;
    lb_dg_sync_paid_purchases(null, (int)SELLER_ID);
    $purchases = dg_get_seller_purchases((int)SELLER_ID, $status, $perPage, ($page - 1) * $perPage);

    // Ensure seller order rows always contain the Digital Goods brand icon.
    // Some helper/query versions only returned purchase data, so the orders view
    // could only render the fallback tag/default icon. Enrich rows here from
    // digital_goods by item_id before passing them to the view.
    if (!empty($purchases) && is_array($purchases)) {
        global $db;
        $itemIds = [];
        $purchaseIds = [];
        foreach ($purchases as $purchaseRow) {
            $itemId = (int)($purchaseRow['item_id'] ?? $purchaseRow['digital_good_id'] ?? $purchaseRow['listing_id'] ?? $purchaseRow['order_id'] ?? 0);
            if ($itemId > 0) $itemIds[$itemId] = $itemId;
            $purchaseId = (int)($purchaseRow['id'] ?? $purchaseRow['purchase_id'] ?? 0);
            if ($purchaseId > 0) $purchaseIds[$purchaseId] = $purchaseId;
        }

        $goodsById = [];
        if (!empty($itemIds)) {
            $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
            $goodsRows = $db->run(
                "SELECT id, brand, brand_icon FROM digital_goods WHERE id IN ({$placeholders})",
                ...array_values($itemIds)
            ) ?: [];
            foreach ($goodsRows as $goodsRow) {
                $goodsById[(int)($goodsRow['id'] ?? 0)] = $goodsRow;
            }
        }

        // Purchase/invoice fallback: old rows may not have item_id in the selected row,
        // but invoice.order_id points to the digital_goods listing.
        $goodsByPurchaseId = [];
        if (!empty($purchaseIds)) {
            $placeholders = implode(',', array_fill(0, count($purchaseIds), '?'));
            $rows = $db->run(
                "SELECT dgp.id AS purchase_id, dg.id, dg.brand, dg.brand_icon
                 FROM digital_good_purchases dgp
                 LEFT JOIN invoices inv ON inv.id = dgp.invoice_id
                 LEFT JOIN digital_goods dg ON dg.id = COALESCE(NULLIF(dgp.item_id,0), NULLIF(inv.order_id,0))
                 WHERE dgp.id IN ({$placeholders})",
                ...array_values($purchaseIds)
            ) ?: [];
            foreach ($rows as $row) {
                $goodsByPurchaseId[(int)($row['purchase_id'] ?? 0)] = $row;
            }
        }

        foreach ($purchases as &$purchaseRow) {
            $itemId = (int)($purchaseRow['item_id'] ?? $purchaseRow['digital_good_id'] ?? $purchaseRow['listing_id'] ?? $purchaseRow['order_id'] ?? 0);
            $purchaseId = (int)($purchaseRow['id'] ?? $purchaseRow['purchase_id'] ?? 0);
            $goodsRow = ($itemId > 0 && isset($goodsById[$itemId])) ? $goodsById[$itemId] : ($goodsByPurchaseId[$purchaseId] ?? null);
            if (is_array($goodsRow)) {
                $icon = (string)($goodsRow['brand_icon'] ?? '');
                $brand = (string)($goodsRow['brand'] ?? '');

                if ($icon !== '') {
                    $purchaseRow['item_brand_icon'] = $icon;
                    $purchaseRow['listing_brand_icon'] = $icon;
                    $purchaseRow['brand_icon'] = $icon;
                }
                if (($purchaseRow['brand'] ?? '') === '' && $brand !== '') {
                    $purchaseRow['brand'] = $brand;
                }
            }
        }
        unset($purchaseRow);
    }

    $meta = ['title' => 'Digital Goods Orders | Seller | LoLBoost.gg', 'robots' => 'noindex, nofollow'];
    view_file('seller/pages/digital-goods/orders', compact('meta', 'purchases', 'status', 'page', 'perPage'));
});

// /seller-area/digital-goods/listings  (Seller: Listings verwalten)
$router->get('/seller-area/digital-goods/listings', function () {
    dg_seller_check();
    $listings   = dg_get_seller_listings((int)SELLER_ID);
    $categories = dg_get_categories();
    $brands = function_exists('dg_get_brands') ? dg_get_brands(true) : [];
    $meta = ['title' => 'My Digital Goods | Seller | LoLBoost.gg', 'robots' => 'noindex, nofollow'];
    view_file('seller/pages/digital-goods/listings', compact('meta', 'listings', 'categories', 'brands'));
});

// /seller-area/digital-goods/listings/create
$router->get('/seller-area/digital-goods/listings/create', function () {
    dg_seller_check();
    redirect_url('seller-area/digital-goods/listings?add=1');
});

// /seller-area/digital-goods/listings/:id/edit
$router->get('/seller-area/digital-goods/listings/:id/edit', function (string $id) {
    dg_seller_check();
    global $db;
    $rows = $db->run("SELECT dg.*, dgc.slug AS category_slug FROM digital_goods dg LEFT JOIN digital_good_categories dgc ON dgc.id=dg.category_id WHERE dg.id=? AND dg.seller_id=? LIMIT 1", (int)$id, (int)SELLER_ID);
    $listing = $rows[0] ?? null;
    if (!$listing) { http_response_code(404); view_file('website/pages/404'); return; }
    $categories = dg_get_categories();
    $brands = function_exists('dg_get_brands') ? dg_get_brands(true) : [];
    $meta = ['title' => 'Edit Listing | Seller | LoLBoost.gg', 'robots' => 'noindex, nofollow'];
    view_file('seller/pages/digital-goods/edit', compact('meta', 'listing', 'categories', 'brands'));
});

// /seller-area/digital-goods/:id  (Seller: Order-Detail + Chat)
// No permission check — seller may have orders even if permission was revoked later.
$router->get('/seller-area/digital-goods/:id', function (string $id) {
    dg_seller_check(false); // only login check, no permission check
    if (function_exists('dg_auto_complete_overdue_purchases')) {
        dg_auto_complete_overdue_purchases(250);
    }
    lb_dg_sync_paid_purchases(null, (int)SELLER_ID);
    $purchase = dg_get_purchase((int)$id);
    if (!$purchase || (int)$purchase['seller_id'] !== (int)SELLER_ID) {
        http_response_code(404); view_file('website/pages/404'); return;
    }
    $images = json_decode((string)($purchase['item_images'] ?? '[]'), true);
    if (!is_array($images)) $images = [];
    $meta = ['title' => 'Order #' . $id . ' | Seller | LoLBoost.gg', 'robots' => 'noindex, nofollow'];
    view_file('seller/pages/digital-goods/order-view', compact('meta', 'purchase', 'images'));
});

// ─────────────────────────────────────────────────────────────────────────────

/* ===== MERGED FROM LIVE lolboost.gg core/routes.php =====
   Missing public boost routes, GGirls/order compatibility routes, image/import/admin data endpoints. */


/* MERGED ROUTE: /premium-account/:id */
$router->get('/premium-account/:id', function ($id) {
    global $is_client, $db;
    if (!$is_client) {
        redirect_url('');
        return;
    }

    $id = (int)$id;

    // Account muss diesem Client gehören (status=1 = sold/assigned)
    $account = db_get_row('accounts', ['id' => $id, 'client_id' => (int)CLIENT_ID, 'status' => 1], 1);
    if (empty($account)) {
        redirect_url('profile/accounts');
        return;
    }

    // Package-Daten laden
    $package = null;
    if (!empty($account['package_id'])) {
        $package = db_get_row('account_packages', ['id' => (int)$account['package_id']], 1);
    }

    // Chat-Nachrichten laden
    $chat_messages = [];
    $chat_raw = [];
    $chat_path = SYS_PATH . '/public/uploads/private/chat/accounts_' . sha1('account_' . $id) . '.json';

    // Seed the welcome message here as well, so accounts bought before this existed
    // still get it. The helper skips it when the same text is already in the file.
    if (function_exists('lb_seed_chat_system_message')) {
        lb_seed_chat_system_message(
            $chat_path,
            'account_' . $id,
            'Thank you for your purchase at lolboost.gg! 🎉

Here you can chat with our support team. 😊
If you have any questions, our live chat is available anytime! 💬

📌 Please note: Your account details are shown on this page — our team will assist you here if anything is missing!'
        );
    }

    if (is_file($chat_path)) {
        $raw_content = @file_get_contents($chat_path) ?: '';
        $chat_raw = @json_decode($raw_content, true) ?: [];
        if (isset($chat_raw['messages']) && is_array($chat_raw['messages'])) {
            $chat_messages = array_values(array_filter($chat_raw['messages'], fn($m) => is_array($m) && empty($m['deleted'])));
        }
    }

    // Admin-Icons aus DB normalisieren
    $admin_rows_by_id   = [];
    $admin_rows_by_name = [];
    foreach ($chat_messages as $m) {
        if (!is_array($m)) continue;
        $sender = strtolower(trim((string)($m['sender'] ?? $m['sender_type'] ?? '')));
        if ($sender !== 'admin') continue;
        $aid = (int)($m['sender_id'] ?? 0);
        if ($aid > 0 && empty($admin_rows_by_id[$aid])) {
            $row = $db->row("SELECT id, username, icon FROM admins WHERE id = ? LIMIT 1", $aid);
            if (!empty($row)) {
                $admin_rows_by_id[$aid] = $row;
                $admin_rows_by_name[strtolower(trim((string)($row['username'] ?? '')))] = $row;
            }
        }
    }
    foreach ($chat_messages as &$m) {
        if (!is_array($m)) continue;
        $sender = strtolower(trim((string)($m['sender'] ?? $m['sender_type'] ?? '')));
        if ($sender === 'admin') {
            $aid     = (int)($m['sender_id'] ?? 0);
            $nameKey = strtolower(trim((string)($m['sender_name'] ?? '')));
            $row = ($aid > 0 && !empty($admin_rows_by_id[$aid])) ? $admin_rows_by_id[$aid] : ($admin_rows_by_name[$nameKey] ?? null);
            if (!empty($row)) {
                $m['sender_name'] = (string)($row['username'] ?? ($m['sender_name'] ?? 'Admin'));
                $m['sender_icon'] = trim((string)($row['icon'] ?? ''));
            } else {
                $m['sender_icon'] = '';
            }
        }
    }
    unset($m);

    // Ungelesene Admin-Nachrichten als gelesen markieren
    if (!empty($chat_raw) && is_array($chat_raw)) {
        $changed = false;
        foreach ($chat_raw['messages'] as &$m) {
            $sender = strtolower((string)($m['sender'] ?? $m['sender_type'] ?? ''));
            if ($sender === 'admin' && empty($m['seen_by_client'])) {
                $m['seen_by_client'] = 1;
                $changed = true;
            }
        }
        unset($m);
        if ($changed) {
            @file_put_contents($chat_path, json_encode($chat_raw, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    $meta = [
        'title'       => ($package['name'] ?? ('Account #' . $id)) . ' | LoLBoost.gg',
        'h1'          => 'Account Details',
        'description' => 'View your purchased account details and chat with support.',
    ];

    view_file('client/pages/orders/premium_account_view', [
        'account'       => $account,
        'package'       => $package,
        'chat_messages' => $chat_messages,
        'meta'          => $meta,
    ]);
});

$router->group('profile', function ($router) {
    $router->get('giveaway', function () {
            global $is_client, $db;
            if (!$is_client) {
                redirect_url('');
            }

            $gw = function_exists('giveaway_get_active') ? giveaway_get_active() : false;
            $my_tickets = 0;
            $leaderboard = [];

            if (!empty($gw)) {
                $gid = (int)$gw['id'];

                $row = db_get_row('giveaway_tickets', ['giveaway_id' => $gid, 'client_id' => CLIENT_ID]);
                $my_tickets = !empty($row) ? (int)$row['tickets'] : 0;

                // IMPORTANT: do NOT use $db->rows() or db_get_rows() operators like tickets[>]
                // because they generate invalid SQL in this project.
                $leaderboard = $db->run(
                    "SELECT gt.client_id, gt.tickets, c.username, c.icon
                     FROM giveaway_tickets gt
                     JOIN clients c ON c.id = gt.client_id
                     WHERE gt.giveaway_id = {$gid} AND gt.tickets > 0
                     ORDER BY gt.tickets DESC, gt.updated_at ASC
                     LIMIT 100"
                ) ?: [];
            }

            $meta = [
                'title' => 'Giveaway',
                'description' => 'View your giveaway tickets and the leaderboard.',
                'keywords' => 'giveaway, tickets, leaderboard',
            ];

            view_file('client/pages/giveaway', [
                'meta' => $meta,
                'giveaway' => $gw,
                'my_tickets' => $my_tickets,
                'leaderboard' => $leaderboard,
            ]);
        });

    $router->get('orders', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $orders = db_get_rows('orders', ['client_id' => CLIENT_ID], 1);
            $boosters = array_column(
                db_get_rows('boosters', ['select' => 'id,username,icon']),
                null,
                'id'
            );
            // get order options and boost_form data and merge it with order data
            foreach ($orders as $k => $order) {
                $order_opts = db_get_row('order_options', ['order_id' => $order['id']]) ?: [];
                // Customer snapshot: if exists, customer should see the snapshot values (original or last-synced)
                $snap = db_get_row('order_original_data', ['order_id' => $order['id']], 1);
                if (!empty($snap)) {
                    $snap_order = json_decode((!empty($snap['customer_orders_json']) ? $snap['customer_orders_json'] : ($snap['orders_json'] ?? '')), true) ?: [];
                    $snap_opts  = json_decode((!empty($snap['customer_options_json']) ? $snap['customer_options_json'] : ($snap['options_json'] ?? '')), true) ?: [];
                    if (isset($snap_order['form_id'])) { $order['form_id'] = $snap_order['form_id']; }
                    if (isset($snap_order['price'])) { $order['price'] = $snap_order['price']; }
                    if (isset($snap_order['price_eur'])) { $order['price_eur'] = $snap_order['price_eur']; }
                    if (is_array($order_opts)) { $order_opts = array_merge($order_opts, $snap_opts); }
                    else { $order_opts = $snap_opts; }
                }

                // Keep the customer-facing server from order_options/customer_options_json.
                // Without this, orders.server overwrites order_options.server in the final merge.
                if (isset($order_opts['server']) && trim((string)$order_opts['server']) !== '') {
                    $order['server'] = $order_opts['server'];
                }

                $invoice_for_order = db_get_row('invoices', ['order_id' => $order['id'], 'order_type' => 'order'], 1);
                if (empty($invoice_for_order)) {
                    $invoice_for_order = db_get_row('invoices', ['order_id' => $order['id']], 1);
                }
                if (!empty($invoice_for_order)) {
                    $order['coins_used'] = $invoice_for_order['coins_used'] ?? 0;
                    $order['invoice_currency'] = $invoice_for_order['currency'] ?? ($order['currency'] ?? 'EUR');
                }

                $form = db_get_row('boost_forms', ['id' => $order['form_id']]) ?: [];
                $orders[$k] = array_merge($form, $order_opts, $order);
                if (isset($order['booster_id']) && isset($boosters[$order['booster_id']])) {
                    $orders[$k]['booster'] = $boosters[$order['booster_id']];
                }
            }
            $meta = [
                'title' => 'Orders List | Fast & Safe | Elo Boosting | Quality Boosting - LoLBoost.gg',
                'h1' => 'Orders List',
                'description' => 'All the orders you have placed.',
                'keywords' => 'lol boost, lol boosting, lolboost.gg, lol boost gg, league of legends boost',
            ];
            // ── Merge selling item purchases into normal client orders ─────────────
            global $db;
            $item_rows = $db->run(
                "SELECT
                    sip.id              AS item_order_id,
                    sip.client_id,
                    sip.status,
                    sip.price,
                    sip.unit_price,
                    sip.quantity,
                    sip.currency,
                    sip.created_at,
                    sip.invoice_id,
                    si.title            AS item_title,
                    si.type             AS item_type,
                    si.server           AS item_server,
                    si.images           AS item_images,
                    si.game_id          AS item_game_id,
                    si.game             AS item_game_slug,
                    si.item_data        AS item_data,
                    g.name              AS item_game_name,
                    g.slug              AS game_slug,
                    g.icon              AS game_icon,
                    s.username          AS seller_username,
                    s.icon              AS seller_icon,
                    inv.coins_used      AS coins_used
                 FROM selling_item_purchases sip
                 LEFT JOIN selling_items si ON si.id = sip.item_id
                 LEFT JOIN games g ON g.id = si.game_id
                 LEFT JOIN sellers s ON s.id = sip.seller_id
                 LEFT JOIN invoices inv ON inv.id = sip.invoice_id
                 WHERE sip.client_id = ?",
                (int) CLIENT_ID
            ) ?? [];

            foreach ($item_rows as &$it) {
                $it['is_item_order'] = true;
                $it['id'] = 'item_' . $it['item_order_id'];
                $it['game'] = $it['game_slug'] ?: ($it['item_game_slug'] ?: 'league-of-legends');
                $it['game_name'] = $it['item_game_name'] ?: ucwords(str_replace('-', ' ', (string)$it['game']));
                $it['title'] = $it['item_title'] ?: ('Digital Good Order #' . (int)$it['item_order_id']);
                $it['type'] = 'item';
                $it['name'] = 'Item';
                $it['price_eur'] = (int)($it['price'] ?? 0);
                $it['price'] = (int)($it['price'] ?? 0);
            }
            unset($it);

            $orders = array_merge($orders ?? [], $item_rows);

            // ── Merge purchased account orders into $orders ──────────────────
            // Important: keep this in sync with /profile/accounts.
            // Do not use a large joined SQL here, because older installs can miss columns
            // like sold_at, game_id or currency. /profile/accounts works because it reads
            // the raw account rows first and enriches them safely afterwards.
            try {
                $invoiceLookup = function (int $clientId, int $orderId, array $types) use ($db) {
                    if ($clientId <= 0 || $orderId <= 0 || empty($types)) {
                        return [];
                    }
                    $placeholders = implode(',', array_fill(0, count($types), '?'));
                    $params = array_merge([$clientId, $orderId], array_map('strtolower', $types));
                    try {
                        return $db->row(
                            "SELECT coins_used, currency, total_price
                             FROM invoices
                             WHERE client_id = ?
                               AND order_id = ?
                               AND LOWER(order_type) IN ($placeholders)
                             ORDER BY id DESC
                             LIMIT 1",
                            ...$params
                        ) ?: [];
                    } catch (Throwable $e) {
                        return [];
                    }
                };

                $marketplace_account_rows = db_get_rows('selling_accounts', ['client_id' => CLIENT_ID, 'sold' => 1], 1) ?: [];
                foreach ($marketplace_account_rows as &$acc) {
                    $accountId = (int)($acc['id'] ?? 0);
                    $invoice = $invoiceLookup((int)CLIENT_ID, $accountId, ['lol_account', 'selling_account']);

                    $seller = [];
                    if (!empty($acc['seller_id'])) {
                        $seller = db_get_row('sellers', ['id' => (int)$acc['seller_id'], 'select' => 'id,username,icon'], 1) ?: [];
                    }

                    $gameName = trim((string)($acc['game_name'] ?? ''));
                    if ($gameName === '') {
                        $gameRaw = trim((string)($acc['game'] ?? 'Account'));
                        $gameName = ucwords(str_replace(['-', '_'], ' ', $gameRaw ?: 'Account'));
                    }

                    $acc['is_marketplace_account_order'] = true;
                    $acc['account_order_id'] = $accountId;
                    $acc['selling_account_id'] = $accountId;
                    $acc['type'] = 'selling_account';
                    $acc['name'] = 'Selling Account';
                    $acc['status'] = 'PAID';
                    $acc['account_title'] = (string)($acc['title'] ?? ('Account #' . $accountId));
                    $acc['game_name'] = $gameName;
                    $acc['account_game_name'] = $gameName;
                    $acc['seller_username'] = $seller['username'] ?? null;
                    $acc['seller_icon'] = $seller['icon'] ?? null;
                    $acc['created_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['updated_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['currency'] = (string)($invoice['currency'] ?? $acc['currency'] ?? 'EUR');
                    $acc['coins_used'] = $invoice['coins_used'] ?? ($acc['coins_used'] ?? null);
                    $acc['price'] = (int)($acc['price'] ?? $invoice['total_price'] ?? 0);
                    $acc['price_eur'] = (int)($acc['price_eur'] ?? $acc['price'] ?? $invoice['total_price'] ?? 0);
                }
                unset($acc);

                $premium_account_rows = db_get_rows('accounts', ['client_id' => CLIENT_ID, 'status' => 1], 1) ?: [];
                foreach ($premium_account_rows as &$acc) {
                    $accountId = (int)($acc['id'] ?? 0);
                    $packageId = (int)($acc['package_id'] ?? 0);
                    $package = $packageId > 0 ? (db_get_row('account_packages', ['id' => $packageId], 1) ?: []) : [];
                    $invoice = $invoiceLookup((int)CLIENT_ID, $packageId, ['account', 'premium_account']);

                    $acc['is_premium_account_order'] = true;
                    $acc['premium_account_id'] = $accountId;
                    $acc['account_order_id'] = $accountId;
                    $acc['type'] = 'account';
                    $acc['name'] = 'Account';
                    $acc['status'] = 'PAID';
                    $acc['package_name'] = (string)($package['name'] ?? $acc['login'] ?? ('Account #' . $accountId));
                    $acc['title'] = $acc['package_name'];
                    $acc['package_server'] = (string)($package['server'] ?? $acc['server'] ?? '');
                    $acc['game_name'] = 'League of Legends';
                    $acc['created_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['updated_at'] = (string)($acc['sold_at'] ?? $acc['created_at'] ?? '');
                    $acc['currency'] = (string)($invoice['currency'] ?? $acc['currency'] ?? 'EUR');
                    $acc['coins_used'] = $invoice['coins_used'] ?? ($acc['coins_used'] ?? null);
                    $acc['price'] = (int)($package['price'] ?? $acc['price'] ?? $invoice['total_price'] ?? 0);
                    $acc['price_eur'] = (int)($package['price'] ?? $acc['price_eur'] ?? $acc['price'] ?? $invoice['total_price'] ?? 0);
                }
                unset($acc);

                $orders = array_merge($orders ?? [], $marketplace_account_rows, $premium_account_rows);
            } catch (Throwable $e) {}
            // ── End account merge ───────────────────────────────────────────



            // ── Merge top_up purchases into $orders ─────────────────────────
            try {
                if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
                $topup_rows = $db->run(
                    "SELECT
                        p.id AS topup_order_id,
                        p.client_id,
                        p.seller_id,
                        p.status,
                        p.price,
                        p.currency,
                        p.created_at,
                        p.invoice_id,
                        p.game_name,
                        p.game_slug,
                        p.offer_title,
                        p.offer_amount,
                        p.offer_unit,
                        p.region,
                        p.platform,
                        p.quantity,
                        p.waiting_time_value,
                        p.waiting_time_unit,
                        p.waiting_time_minutes,
                        g.icon AS game_icon,
                        s.username AS seller_username,
                        s.icon AS seller_icon,
                        inv.coins_used AS coins_used
                     FROM selling_topup_purchases p
                     LEFT JOIN games g ON g.id = p.game_id
                     LEFT JOIN sellers s ON s.id = p.seller_id
                     LEFT JOIN invoices inv ON inv.id = p.invoice_id
                     WHERE p.client_id = ?",
                    (int)CLIENT_ID
                ) ?: [];
                foreach ($topup_rows as &$tu) {
                    $tu['is_topup_order'] = true;
                    $tu['id'] = 'topup_' . (int)$tu['topup_order_id'];
                    $tu['title'] = (string)($tu['offer_title'] ?? 'Top Up');
                    $tu['type'] = 'topup';
                    $tu['name'] = 'Top Up';
                    $tu['price_eur'] = (int)($tu['price'] ?? 0);
                }
                unset($tu);
                $orders = array_merge($orders ?? [], $topup_rows);
            } catch (Throwable $e) {}
            // ── End top_up merge ─────────────────────────────────────────────

            // ── Merge egirl_orders into $orders ──────────────────────────────
            global $db;
            $egirl_rows = $db->run(
                "SELECT
                    eo.id              AS egirl_order_id,
                    eo.client_id,
                    eo.status,
                    eo.price,
                    eo.price_eur,
                    eo.currency,
                    eo.created_at,
                    eo.invoice_id,
                    eo.service_title,
                    eo.game,
                    b.username         AS egirl_username,
                    b.icon             AS egirl_icon,
                    es.includes_voice  AS includes_voice,
                    inv.coins_used     AS coins_used
                 FROM egirl_orders eo
                 LEFT JOIN boosters b         ON b.id  = eo.egirl_id
                 LEFT JOIN egirl_services es  ON es.id = eo.service_id
                 LEFT JOIN invoices inv       ON inv.id = eo.invoice_id
                 WHERE eo.client_id = ?",
                (int) CLIENT_ID
            ) ?? [];

            foreach ($egirl_rows as &$eg) {
                $eg['is_egirl'] = true;
                $eg['id']       = 'eg_' . $eg['egirl_order_id'];
            }
            unset($eg);

            $orders = array_merge($orders ?? [], $egirl_rows);

            // Re-sort newest first
            usort($orders, function ($a, $b) {
                return strtotime((string)($b['created_at'] ?? '2000-01-01'))
                     - strtotime((string)($a['created_at'] ?? '2000-01-01'));
            });
            // ── End egirl merge ───────────────────────────────────────────────

            view_file('client/pages/orders/list', ['orders' => $orders, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });


    $router->get('billing', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $payments = db_get_rows('transactions', ['client_id' => CLIENT_ID], 1);
            $meta = [
                'title' => 'Billing | Fast & Safe | Elo Boosting | Quality Boosting - LoLBoost.gg',
                'h1' => 'Billing',
                'description' => 'All the transactions you made on the website.',
                'keywords' => 'lol boost, lol boosting, lolboost.gg, lol boost gg, league of legends boost',
            ];
            view_file('client/pages/payments', ['payments' => $payments, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });

    $router->get('accounts', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $accounts = db_get_rows('accounts', ['client_id' => CLIENT_ID, 'status' => 1], 1);
            $lol_accounts = db_get_rows('selling_accounts', ['client_id' => CLIENT_ID, 'sold' => 1], 1);

            // get package data for each account
            foreach ($accounts as $k => $account) {
                $package = db_get_row('account_packages', ['id' => $account['package_id']]);
                $accounts[$k] = array_merge($package, $account);
            }
            $meta = [
                'title' => 'LoL Accounts | Fast & Safe | Elo Boosting | Quality Boosting - LoLBoost.gg',
                'h1' => 'LoL Accounts',
                'description' => 'View the list of all your purchased accounts',
                'keywords' => 'lol boost, lol boosting, lolboost.gg, lol boost gg, league of legends boost',
            ];
            view_file('client/pages/accounts', ['accounts' => $accounts, 'lol_accounts' => $lol_accounts, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });

    $router->get('items', function () {
        global $is_client, $db;
        if (!$is_client) {
            redirect_url('');
            return;
        }

        $items = $db->run(
            "SELECT sip.*, si.title AS item_title, si.type, si.server, si.images,
                    si.price AS item_price, si.description AS item_description,
                    si.game_id AS item_game_id, si.game AS item_game_slug, si.item_data,
                    g.name AS item_game_name, g.slug AS game_slug, g.icon AS game_icon,
                    s.username AS seller_username
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN games g ON g.id = si.game_id
             LEFT JOIN sellers s ON s.id = sip.seller_id
             WHERE sip.client_id = ?
             ORDER BY sip.created_at DESC",
            (int) CLIENT_ID
        ) ?: [];

        $meta = [
            'title' => 'My Items | LoLBoost.gg',
            'h1' => 'My Items',
            'description' => 'View the list of all your purchased items',
            'keywords' => 'items, lolboost items, purchased items',
        ];

        view_file('client/pages/items/list', [
            'items' => $items,
            'meta' => $meta,
        ]);
    });

    $router->get('item/:id', function ($id) {
        global $is_client, $db;
        if (!$is_client) {
            redirect_url('');
            return;
        }

        $id = (int) $id;
        $purchase = $db->row(
            "SELECT sip.*, si.title AS item_title, si.type, si.server, si.images,
                    si.price AS item_price, si.description AS item_description,
                    si.requires_friendship_days, si.game_id AS item_game_id,
                    si.game AS item_game_slug, si.item_data,
                    g.name AS item_game_name, g.slug AS game_slug, g.icon AS game_icon,
                    sip.id AS purchase_id
             FROM selling_item_purchases sip
             LEFT JOIN selling_items si ON si.id = sip.item_id
             LEFT JOIN games g ON g.id = si.game_id
             WHERE sip.id = ? AND sip.client_id = ?
             LIMIT 1",
            $id,
            (int) CLIENT_ID
        );

        if (empty($purchase)) {
            redirect_url('profile/items');
            return;
        }

        $seller = null;
        if (!empty($purchase['seller_id'])) {
            $seller = $db->row(
                "SELECT id, username, email, icon FROM sellers WHERE id = ? LIMIT 1",
                (int) $purchase['seller_id']
            );
        }

        $details = db_get_row('selling_item_purchase_details', ['purchase_id' => $id], 1) ?: [];
        $chat_messages = [];
        $chat_path = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_item_purchase_' . $id) . '.json';
        if (is_file($chat_path)) {
            $raw = file_get_contents($chat_path);
            $chat_data = json_decode($raw, true);
            if (is_array($chat_data) && isset($chat_data['messages'])) {
                $chat_messages = array_values(array_filter($chat_data['messages'], fn($m) => empty($m['deleted'])));
            }
        }

        $remaining = !empty($purchase['friendship_ready_at']) ? (strtotime($purchase['friendship_ready_at']) - time()) : null;

        $can_review = false;
        $already_reviewed = false;
        if (!empty($purchase['seller_id'])) {
            $existing_rv = $db->row(
                "SELECT id FROM seller_reviews WHERE seller_id = ? AND client_id = ? LIMIT 1",
                (int)$purchase['seller_id'], (int)CLIENT_ID
            );
            $can_review       = true;
            $already_reviewed = !empty($existing_rv);
        }

        $meta = [
            'title' => item_meta_display_text($purchase['title'] ?? null, 'Digital Good Order #' . $id) . ' | LoLBoost.gg',
            'h1' => 'Digital Good Order #' . $id,
            'description' => 'View your purchased item details and chat with the seller.',
        ];

        view_file('client/pages/items/view', [
            'purchase'         => $purchase,
            'seller'           => $seller,
            'details'          => $details,
            'chat_messages'    => $chat_messages,
            'remaining'        => $remaining,
            'meta'             => $meta,
            'can_review'       => $can_review,
            'already_reviewed' => $already_reviewed,
        ]);
    });


    $router->get('premium-account/:id', function ($id) {
        redirect_url('premium-account/' . (int)$id);
    });

    $router->get('referrals', function () {
        global $is_client;
        if ($is_client) {
            if (function_exists('lb_referral_client_is_allowed') && !lb_referral_client_is_allowed((int)CLIENT_ID)) {
                redirect_url('profile/settings');
            }
            $meta = [
                'title' => 'Referrals | Client Area | LoLBoost.gg',
                'keywords' => 'lolboost referrals, invite friends, lb coins',
            ];
            view_file('client/pages/referrals', ['meta' => $meta]);
        } else {
            redirect_url('');
        }
    });

    $router->get('settings', function () {
        // check if is_client = true
        global $is_client;
        if ($is_client) {
            $meta = [
                'title' => 'Settings | Fast & Safe | Elo Boosting | Quality Boosting - LoLBoost.gg',
                'keywords' => 'lol boost, lol boosting, lolboost.gg, lol boost gg, league of legends boost',
            ];
            view_file('client/pages/settings', ['meta' => $meta]);
        } else {
            redirect_url('');
        }
    });
    $router->get('overview', function () {
        global $is_client, $db;

        if ($is_client) {
            $client_id = CLIENT_ID;
            $client_data = $db->row('SELECT * FROM clients WHERE id = ?', $client_id);
            $orders = db_get_rows('orders', ['client_id' => $client_id, 'status' => ['n' => 'UNPAID'], 'order' => 'created_at,DESC']);
            $unpaid_orders = db_get_rows('orders', ['client_id' => $client_id, 'status' => 'UNPAID', 'order' => 'created_at,DESC']);
$total_spent = 0;
            $current_rank = null;
            $next_rank = null;
            $progress = 0;

            if (!empty($orders)) {
                foreach ($orders as $k => $order) {
                    $order_opts = db_get_row('order_options', ['order_id' => $order['id']]) ?: [];
                    $form = db_get_row('boost_forms', ['id' => $order['form_id']]) ?: [];
                    $orders[$k] = array_merge($form, $order_opts, $order);
                }

                $total_spent = array_sum(array_column($orders, 'price')) / 100;
            }


            if (!empty($unpaid_orders)) {
                foreach ($unpaid_orders as $k => $order) {
                    $order_opts = db_get_row('order_options', ['order_id' => $order['id']]) ?: [];
                    $form = db_get_row('boost_forms', ['id' => $order['form_id']]) ?: [];
                    $unpaid_orders[$k] = array_merge($form, $order_opts, $order);
                }
            }
// Top 15 Boosters with most completed orders (same query logic as website)
$top_boosters = [];
try {
    $query = "
        SELECT 
            boosters.*, 
            booster_ranks.name as rank_name, 
            booster_profiles.*, 
            boosters.id as booster_id,
            COUNT(orders.id) as completed_orders
        FROM boosters
        INNER JOIN booster_profiles 
            ON boosters.id = booster_profiles.booster_id
        LEFT JOIN booster_ranks 
            ON boosters.rank_id = booster_ranks.id
        LEFT JOIN orders 
            ON boosters.id = orders.booster_id AND orders.status = 'COMPLETED'
        WHERE 
            boosters.is_banned = 0 
            AND (boosters.is_egirl IS NULL OR boosters.is_egirl = 0)
            AND booster_profiles.champions IS NOT NULL 
            AND booster_profiles.roles IS NOT NULL
            AND boosters.show_profile = 1
        GROUP BY boosters.id
        ORDER BY completed_orders DESC, boosters.id ASC
        LIMIT 50";
    $top_boosters = $db->run($query);
} catch (Throwable $e) {
    $top_boosters = [];
}



            $loyalty_ranks = db_get_rows('loyalty_ranks', ['order' => 'target_amount,ASC']);

            $current_rank = null;
            $next_rank = null;
            foreach ($loyalty_ranks as $rank) {
                if ($rank['id'] == $client_data['loyalty_rank_id']) {
                    $current_rank = $rank;
                } elseif ($rank['target_amount'] > $total_spent && (!$next_rank || $rank['target_amount'] < $next_rank['target_amount'])) {
                    $next_rank = $rank;
                }
            }

            $progress = $next_rank ? min(100, ($total_spent / $next_rank['target_amount']) * 100) : 100;

            $meta = [
                'title' => 'Overview | Fast & Safe | Elo Boosting | Quality Boosting - LoLBoost.gg',
                'keywords' => 'lol boost, lol boosting, lolboost.gg, lol boost gg, league of legends boost',
            ];
            view_file('client/pages/overview', [
                'meta' => $meta,
                'orders' => $orders,
                'unpaid_orders' => $unpaid_orders,
                'client_data' => $client_data,
                'total_spent' => $total_spent,
                'current_rank' => $current_rank,
                'next_rank' => $next_rank,
                'progress' => $progress,
                'top_boosters' => $top_boosters,
            ]);
        } else {
            redirect_url('');
        }
    });


    $router->get('chat', function () {
        global $is_client, $db;
        if (!$is_client) { redirect_url(''); return; }

        $client_id = (int) CLIENT_ID;
        $chat_dir  = SYS_PATH . '/public/uploads/private/chat';
        $conversations = [];

        $read_chat_summary = function (string $chat_path) use ($client_id) {
            $out = ['exists' => is_file($chat_path), 'count' => 0, 'client_match' => false, 'last_body' => '', 'last_message_at' => 0, 'unread_client' => 0, 'data' => []];
            if (!$out['exists']) { return $out; }
            $data = json_decode(@file_get_contents($chat_path) ?: '', true);
            if (!is_array($data)) { return $out; }
            $out['data'] = $data;
            if ((int)($data['client_id'] ?? 0) === $client_id) { $out['client_match'] = true; }
            $messages = isset($data['messages']) && is_array($data['messages']) ? array_values($data['messages']) : [];

            $msg_order = function (array $m, int $index): int {
                if (!empty($m['time']) && is_numeric($m['time'])) { return (int)$m['time']; }
                if (!empty($m['created_at'])) {
                    $ts = strtotime((string)$m['created_at']);
                    if ($ts !== false) { return (int)$ts; }
                }
                return $index;
            };
            $msg_sender = function (array $m): string {
                $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? '')));
                $type = strtolower(trim((string)($m['type'] ?? '')));
                if ($sender === '' && in_array($type, ['client', 'seller', 'system'], true)) { $sender = $type; }
                return $sender;
            };

            $last_client_value = 0;
            foreach ($messages as $idx => $m) {
                if (!is_array($m) || !empty($m['deleted'])) { continue; }
                $sender = $msg_sender($m);
                if ($sender === 'client') {
                    $last_client_value = max($last_client_value, $msg_order($m, $idx + 1));
                    if ((int)($m['sender_id'] ?? 0) === $client_id) { $out['client_match'] = true; }
                }
            }

            foreach ($messages as $idx => $m) {
                if (!is_array($m) || !empty($m['deleted'])) { continue; }
                $out['count']++;
                $sender = $msg_sender($m);
                $ts = $msg_order($m, $idx + 1);
                if ($ts >= (int)$out['last_message_at']) {
                    $body = trim(strip_tags((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? $m['raw'] ?? '')));
                    if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image' || preg_match('/<img\\s/i', (string)($m['content'] ?? ''))) { $body = $body !== '' ? '[Image] ' . $body : '[Image]'; }
                    $out['last_body'] = $body;
                    $out['last_message_at'] = $ts;
                }
                if ($sender === 'seller') {
                    if (array_key_exists('seen_by_client', $m)) {
                        $seen_by_client = (int)$m['seen_by_client'];
                    } elseif (array_key_exists('seen', $m)) {
                        $seen_by_client = (int)$m['seen'];
                    } elseif (array_key_exists('is_read', $m)) {
                        $seen_by_client = (int)$m['is_read'];
                    } else {
                        $seen_by_client = 1;
                    }
                    if ($seen_by_client === 1) { continue; }
                    $message_value = $msg_order($m, $idx + 1);
                    if ($last_client_value > 0 && $message_value <= $last_client_value) { continue; }
                    $out['unread_client']++;
                }
            }
            return $out;
        };

        if (is_dir($chat_dir)) {
            foreach (glob($chat_dir . '/selling_*.json') ?: [] as $chat_path) {
                $sum = $read_chat_summary($chat_path);
                if (!$sum['exists'] || $sum['count'] < 1 || !$sum['client_match']) { continue; }
                $data = $sum['data'];
                $ref_type = (string)($data['ref_type'] ?? '');
                $account_id = (int)($data['account_id'] ?? 0);
                $purchase_id = (int)($data['purchase_id'] ?? 0);
                if ($ref_type === '' && $purchase_id > 0) { $ref_type = 'item_purchase'; }
                if ($ref_type === '' && $account_id > 0) { $ref_type = 'account'; }

                if ($ref_type === 'item_purchase' && $purchase_id > 0) {
                    $purchase = $db->row("SELECT sip.id, sip.item_id, sip.seller_id, sip.client_id, si.title AS item_title, s.username AS seller_username, s.icon AS seller_icon FROM selling_item_purchases sip LEFT JOIN selling_items si ON si.id = sip.item_id LEFT JOIN sellers s ON s.id = sip.seller_id WHERE sip.id = ? AND sip.client_id = ? LIMIT 1", $purchase_id, $client_id);
                    if (empty($purchase)) { continue; }
                    $conversations[] = ['id'=>'item-'.$purchase_id,'kind'=>'item','kind_label'=>'DIGITAL GOOD','request_status'=>'paid','ref_type'=>'item_purchase','ref_id'=>$purchase_id,'seller_id'=>(int)($purchase['seller_id'] ?? 0),'seller_username'=>trim((string)($purchase['seller_username'] ?? '')) ?: 'Seller','seller_icon'=>(string)($purchase['seller_icon'] ?? ''),'title'=>$purchase['item_title'] ?? ('Digital Good Order #'.$purchase_id),'last_body'=>$sum['last_body'],'last_message_at'=>$sum['last_message_at'],'unread_client'=>$sum['unread_client'],'source_url'=>BASE_URL.'/profile/item/'.$purchase_id];
                    continue;
                }

                if ($account_id <= 0) { continue; }
                $account = $db->row("SELECT a.id, a.title, a.seller_id, a.sold, a.client_id, s.username AS seller_username, s.icon AS seller_icon FROM selling_accounts a LEFT JOIN sellers s ON s.id = a.seller_id WHERE a.id = ? LIMIT 1", $account_id);
                if (empty($account)) { continue; }
                $is_paid_order = ((int)($account['sold'] ?? 0) === 1 && (int)($account['client_id'] ?? 0) === $client_id);
                $conversations[] = ['id'=>'account-'.$account_id,'kind'=>'account','kind_label'=>$is_paid_order ? 'ORDER' : 'ACCOUNT REQUEST','request_status'=>$is_paid_order ? 'paid' : 'request','ref_type'=>'account','ref_id'=>$account_id,'seller_id'=>(int)($account['seller_id'] ?? ($data['seller_id'] ?? 0)),'seller_username'=>trim((string)($account['seller_username'] ?? '')) ?: 'Seller','seller_icon'=>(string)($account['seller_icon'] ?? ''),'title'=>$account['title'] ?? ('Account #'.$account_id),'last_body'=>$sum['last_body'],'last_message_at'=>$sum['last_message_at'],'unread_client'=>$sum['unread_client'],'source_url'=>$is_paid_order ? (BASE_URL.'/account/'.$account_id) : (BASE_URL.'/lol/accounts/'.$account_id)];
            }
        }

        // Include normal LoL boosting order chats in the initial /profile/chat render.
        // Realtime AJAX already refreshes them, but the route must seed them on first load.
        try {
            $orders = $db->run(
                "SELECT o.id, o.form_id, o.status, o.created_at, o.paid_at, o.booster_id,
                        b.username AS booster_username, b.icon AS booster_icon,
                        bf.name AS form_name, bf.type AS form_type
                 FROM orders o
                 LEFT JOIN boosters b ON b.id = o.booster_id
                 LEFT JOIN boost_forms bf ON bf.id = o.form_id
                 WHERE o.client_id = ?
                 ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC
                 LIMIT 150",
                $client_id
            ) ?: [];

            $normalize_order_messages = function ($raw): array {
                if (!is_array($raw)) { return []; }
                if (isset($raw['messages']) && is_array($raw['messages'])) { return array_values($raw['messages']); }
                return array_values($raw);
            };

            foreach ($orders as $order) {
                if (!is_array($order)) { continue; }
                $order_id = (int)($order['id'] ?? 0);
                if ($order_id <= 0) { continue; }

                $chat_path = $chat_dir . '/' . sha1((string)$order_id) . '.json';
                $messages = [];
                $has_chat_file = is_file($chat_path);

                if ($has_chat_file) {
                    $raw_chat = json_decode(@file_get_contents($chat_path) ?: '', true);
                    $messages = $normalize_order_messages($raw_chat);
                } elseif (function_exists('chat_load_messages')) {
                    $raw_chat = chat_load_messages($order_id);
                    $messages = $normalize_order_messages($raw_chat);
                }

                if (!$has_chat_file && empty($messages) && (int)($order['booster_id'] ?? 0) <= 0) { continue; }

                $last_body = 'No booster message yet';
                $last_message_at = 0;
                $unread_client = 0;

                foreach ($messages as $idx => $m) {
                    if (!is_array($m) || !empty($m['deleted'])) { continue; }
                    $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? '')));
                    $type = strtolower(trim((string)($m['type'] ?? '')));
                    if ($sender === '' && in_array($type, ['client', 'booster', 'admin', 'system'], true)) { $sender = $type; }

                    if (!empty($m['time']) && is_numeric($m['time'])) {
                        $ts = (int)$m['time'];
                    } elseif (!empty($m['created_at']) && strtotime((string)$m['created_at']) !== false) {
                        $ts = (int)strtotime((string)$m['created_at']);
                    } elseif (!empty($m['timestamp']) && is_numeric($m['timestamp'])) {
                        $ts = (int)$m['timestamp'];
                    } else {
                        $ts = $idx + 1;
                    }

                    if ($ts >= $last_message_at) {
                        $body = trim(strip_tags((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? $m['raw'] ?? '')));
                        if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image') { $body = $body !== '' ? '[Image] ' . $body : '[Image]'; }
                        if ($body === '') { $body = (($m['message_type'] ?? '') === 'system' || ($m['type'] ?? '') === 'system') ? 'System message' : 'No message yet'; }
                        $last_body = $body;
                        $last_message_at = $ts;
                    }

                    if ($sender === 'booster') {
                        if (array_key_exists('seen_by_client', $m)) { $is_unread = ((int)$m['seen_by_client'] === 0); }
                        elseif (array_key_exists('is_read', $m)) { $is_unread = ((int)$m['is_read'] === 0); }
                        elseif (array_key_exists('seen', $m)) { $is_unread = ((int)$m['seen'] === 0); }
                        else { $is_unread = false; }
                        if ($is_unread) { $unread_client++; }
                    }
                }

                if ($last_message_at <= 0) {
                    $base_ts = strtotime((string)($order['paid_at'] ?? $order['created_at'] ?? ''));
                    $last_message_at = $base_ts !== false ? (int)$base_ts : 0;
                }

                $booster_name = trim((string)($order['booster_username'] ?? ''));
                if ($booster_name === '') { $booster_name = ((int)($order['booster_id'] ?? 0) > 0) ? 'Booster' : 'Booster not assigned'; }

                $title = trim((string)($order['form_name'] ?? $order['form_type'] ?? ''));
                if ($title === '') { $title = 'Boosting Order #' . $order_id; }

                $conversations[] = [
                    'id' => 'booster-order-' . $order_id,
                    'kind' => 'booster_order',
                    'kind_label' => 'BOOSTER CHAT',
                    'chat_type' => 'booster',
                    'seller_id' => 0,
                    'seller_username' => $booster_name,
                    'seller_icon' => (string)($order['booster_icon'] ?? ''),
                    'request_status' => 'paid',
                    'ref_type' => 'booster_order',
                    'ref_id' => $order_id,
                    'title' => $title,
                    'last_body' => $last_body,
                    'last_message_at' => $last_message_at,
                    'unread_client' => $unread_client,
                    'source_url' => BASE_URL . '/profile/orders/' . $order_id,
                    'source_label' => 'View Order',
                ];
            }
        } catch (Throwable $e) {}


        $client_id = (int)(defined('CLIENT_ID') ? CLIENT_ID : (CLIENT_DATA['id'] ?? ($_SESSION['client_id'] ?? 0)));
        $chat_dir = SYS_PATH . '/public/uploads/private/chat';

        $random_booster_chat_summary = function(string $chat_file) use ($client_id, $read_chat_summary) {
            $base = basename($chat_file, '.json');
            if (strpos($base, 'selling_') === 0) return null;
            $data = json_decode(@file_get_contents($chat_file) ?: '', true);
            if (!is_array($data)) return null;
            $messages = (isset($data['messages']) && is_array($data['messages'])) ? array_values($data['messages']) : array_values($data);
            if (empty($messages)) return null;
            $client_username = defined('CLIENT_DATA') && is_array(CLIENT_DATA) ? strtolower(trim((string)(CLIENT_DATA['username'] ?? ''))) : '';
            $belongs = ((int)($data['client_id'] ?? $data['user_id'] ?? 0) === $client_id);
            $senderOf = function(array $m): string { $s = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? ''))); $t = strtolower(trim((string)($m['type'] ?? ''))); if ($s === '' && in_array($t, ['client','booster','seller','admin','system'], true)) $s = $t; return $s; };
            $timeOf = function(array $m, int $i): int { if (!empty($m['time']) && is_numeric($m['time'])) return (int)$m['time']; if (!empty($m['created_at'])) { $ts = strtotime((string)$m['created_at']); if ($ts !== false) return (int)$ts; } if (!empty($m['timestamp']) && is_numeric($m['timestamp'])) return (int)$m['timestamp']; return $i; };
            $bodyOf = function(array $m): string { $body = trim(strip_tags((string)($m['message'] ?? $m['body'] ?? $m['content'] ?? $m['raw'] ?? ''))); if (($m['message_type'] ?? '') === 'image' || ($m['type'] ?? '') === 'image') return $body !== '' ? '[Image] ' . $body : '[Image]'; return $body !== '' ? $body : 'No message yet'; };
            foreach ($messages as $m) { if (!is_array($m) || !empty($m['deleted']) || $senderOf($m) !== 'client') continue; $mid = (int)($m['sender_id'] ?? $m['client_id'] ?? $m['user_id'] ?? 0); $mname = strtolower(trim((string)($m['sender_name'] ?? $m['username'] ?? $m['name'] ?? ''))); if ($mid === $client_id || ($client_username !== '' && $mname !== '' && $mname === $client_username)) { $belongs = true; break; } }
            if (!$belongs) return null;
            $last = null; $last_at = 0; $unread = 0;
            foreach ($messages as $i => $m) { if (!is_array($m) || !empty($m['deleted'])) continue; $sender=$senderOf($m); $t=$timeOf($m,$i+1); if ($t >= $last_at) { $last_at=$t; $last=$m; } if (in_array($sender, ['booster','admin','seller'], true)) { if (array_key_exists('seen_by_client',$m)) $is_unread=((int)$m['seen_by_client']===0); elseif (array_key_exists('is_read',$m)) $is_unread=((int)$m['is_read']===0); elseif (array_key_exists('seen',$m)) $is_unread=((int)$m['seen']===0); else $is_unread=false; if ($is_unread) $unread++; } }
            return ['chat_key'=>$base, 'order_id'=>(int)($data['order_id'] ?? $data['ref_id'] ?? $data['id'] ?? 0), 'last'=>$last, 'last_body'=>$last ? $bodyOf($last) : 'No booster message yet', 'last_at'=>$last_at, 'unread'=>$unread];
        };
        if (is_dir($chat_dir)) {
            $existing = [];
            foreach ($conversations as $c) { if (($c['chat_type'] ?? '') === 'booster' || ($c['ref_type'] ?? '') === 'booster_order') { $existing[(string)($c['ref_id'] ?? 0)] = true; if (!empty($c['chat_key'])) $existing[(string)$c['chat_key']] = true; } }
            foreach (glob($chat_dir . '/*.json') ?: [] as $chat_file) {
                $sum = $random_booster_chat_summary($chat_file);
                if (!$sum) continue;
                $order_id = (int)$sum['order_id']; $chat_key = (string)$sum['chat_key'];
                if (($order_id > 0 && isset($existing[(string)$order_id])) || isset($existing[$chat_key])) continue;
                $order = null; $booster_name = 'Booster Chat'; $booster_icon = ''; $title = 'Boosting Chat'; $source_url = '';
                if ($order_id > 0) { $order = $db->row("SELECT o.id, o.client_id, o.form_id, o.status, o.created_at, o.paid_at, o.booster_id, b.username AS booster_username, b.icon AS booster_icon, bf.name AS form_name, bf.type AS form_type FROM orders o LEFT JOIN boosters b ON b.id=o.booster_id LEFT JOIN boost_forms bf ON bf.id=o.form_id WHERE o.id=? AND o.client_id=? LIMIT 1", $order_id, $client_id); }
                if (!empty($order)) { $booster_name = trim((string)($order['booster_username'] ?? '')) ?: (((int)($order['booster_id'] ?? 0)>0) ? 'Booster' : 'Booster not assigned'); $booster_icon=(string)($order['booster_icon'] ?? ''); $title=trim((string)($order['form_name'] ?? $order['form_type'] ?? '')) ?: ('Boosting Order #' . $order_id); $source_url=BASE_URL . '/profile/orders/' . $order_id; }
                $conversations[] = ['id'=>'booster-raw-'.$chat_key,'kind'=>'booster_order','kind_label'=>'BOOSTER CHAT','chat_type'=>'booster','seller_id'=>0,'seller_username'=>$booster_name,'seller_icon'=>$booster_icon,'request_status'=>'paid','ref_type'=>'booster_order','ref_id'=>$order_id,'chat_key'=>$chat_key,'title'=>$title,'last_body'=>$sum['last_body'],'last_message_at'=>(int)$sum['last_at'],'unread_client'=>(int)$sum['unread'],'source_url'=>$source_url,'source_label'=>$order_id > 0 ? 'View Order' : 'Boosting Chat'];
            }
        }

        usort($conversations, function ($a, $b) { return ((int)($b['last_message_at'] ?? 0)) <=> ((int)($a['last_message_at'] ?? 0)); });
        $meta = ['title' => 'My Chats | LoLBoost.gg'];
        view_file('client/pages/chat/inbox', compact('meta', 'conversations'));
    });

    $router->get('coins-history', function () {
        global $is_client;
        global $db;

        if ($is_client) {
            $query = 'SELECT id, type, reason, amount, created_at
                        FROM coins_history
                        WHERE client_id = ?
                        ORDER BY id DESC';

            $data = $db->run($query, CLIENT_ID);

            $meta = [
                'title' => 'Coins History | Fast & Safe | Elo Boosting | Quality Boosting - LoLBoost.gg',
                'h1' => 'Coins History',
                'description' => 'View your coins history and transactions on LoLBoost.gg.',
                'keywords' => 'lol boost, lol boosting, lolboost.gg, lol boost gg, league of legends boost',
            ];

            view_file('client/pages/coins-history', ['data' => $data, 'meta' => $meta]);
        } else {
            redirect_url('');
        }
    });
});



/* TOP UP ROUTE ALIASES, keep these outside groups so old and new links never 404 */
if (!function_exists('lb_topup_route_load_purchase')) {
    function lb_topup_route_load_purchase($id, $scope = 'client') {
        global $db, $seller_data;
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        $id = (int)$id;
        $where = 'p.id = ?';
        $args = [$id];
        if ($scope === 'client' && defined('CLIENT_ID')) { $where .= ' AND p.client_id = ?'; $args[] = (int)CLIENT_ID; }
        if ($scope === 'seller' && is_array($seller_data ?? null)) { $where .= ' AND p.seller_id = ?'; $args[] = (int)$seller_data['id']; }
        $sql = "SELECT p.*, st.image, st.instructions, st.offer_title AS listing_offer_title, st.offer_amount AS listing_offer_amount, st.offer_unit AS listing_offer_unit,
                       g.icon AS game_icon, g.name AS db_game_name, g.slug AS db_game_slug,
                       s.username AS seller_username, s.email AS seller_email, s.icon AS seller_icon, s.icon AS seller_avatar,
                       c.username AS client_username, c.email AS client_email, c.icon AS client_icon, c.icon AS client_avatar
                FROM selling_topup_purchases p
                LEFT JOIN selling_topups st ON st.id = p.topup_id
                LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
                LEFT JOIN sellers s ON s.id = p.seller_id
                LEFT JOIN clients c ON c.id = p.client_id
                WHERE {$where}
                LIMIT 1";
        try { return $db->row($sql, ...$args); } catch (Throwable $e) { return null; }
    }
    function lb_topup_route_checkout_data($purchase) {
        $checkoutData = [];
        $raw = (string)($purchase['checkout_data'] ?? '');
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) { $checkoutData = $decoded; }
        }
        if (isset($checkoutData['fields'])) {
            if (is_string($checkoutData['fields'])) {
                $fields = json_decode($checkoutData['fields'], true);
                if (is_array($fields)) { $checkoutData = $fields; }
            } elseif (is_array($checkoutData['fields'])) {
                $checkoutData = $checkoutData['fields'];
            }
        }
        return $checkoutData;
    }
}

$router->get('/profile/top-up/:id', function ($id) {
    global $is_client;
    if (!$is_client) { redirect_url(''); return; }
    $purchase = lb_topup_route_load_purchase($id, 'client');
    if (empty($purchase)) { redirect_url('profile/top-ups'); return; }
    $checkoutData = lb_topup_route_checkout_data($purchase);
    $meta = ['title' => 'Top Up Order #' . (int)$id . ' | LoLBoost', 'h1' => 'Top Up Order #' . (int)$id];
    view_file('client/pages/topups/view', compact('meta', 'purchase', 'checkoutData'));
});

$router->get('/seller-area/top-up/:id', function ($id) {
    redirect_url('seller-area/top-up-order/' . (int)$id);
});

$router->get('/seller-area/top-up-order/:id', function ($id) {
    global $is_seller, $seller_data;
    if (!$is_seller) { redirect_url('seller-area/auth/login'); return; }
    $purchase = lb_topup_route_load_purchase($id, 'seller');
    if (empty($purchase)) { redirect_url('seller-area/top-up-orders'); return; }
    $checkoutData = lb_topup_route_checkout_data($purchase);
    $meta = ['title' => 'Top Up Order #' . (int)$id . ' | LoLBoost', 'h1' => 'Top Up Order #' . (int)$id];
    view_file('seller/pages/topups/order_view', compact('meta', 'seller_data', 'purchase', 'checkoutData'));
});

$router->get('/admin-area/top-up-orders', function () {
    global $is_admin, $db;
    if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
    if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
    try {
        $orders = $db->run(
            "SELECT p.*, st.image, st.instructions, st.offer_title AS listing_offer_title, st.offer_amount AS listing_offer_amount, st.offer_unit AS listing_offer_unit,
                    g.icon AS game_icon, g.name AS db_game_name, g.slug AS db_game_slug,
                    s.username AS seller_username, s.email AS seller_email, s.icon AS seller_icon,
                    c.username AS client_username, c.email AS client_email, c.icon AS client_icon, c.icon AS client_avatar
             FROM selling_topup_purchases p
             LEFT JOIN selling_topups st ON st.id = p.topup_id
             LEFT JOIN games g ON g.id = COALESCE(p.game_id, st.game_id)
             LEFT JOIN sellers s ON s.id = p.seller_id
             LEFT JOIN clients c ON c.id = p.client_id
             ORDER BY COALESCE(p.paid_at, p.created_at) DESC, p.id DESC"
        ) ?: [];
        $listings = $db->run(
            "SELECT st.*, g.name AS db_game_name, g.icon AS game_icon, s.username AS seller_username, s.icon AS seller_icon
             FROM selling_topups st
             LEFT JOIN games g ON g.id = st.game_id
             LEFT JOIN sellers s ON s.id = st.seller_id
             ORDER BY st.created_at DESC, st.id DESC"
        ) ?: [];
    } catch (Throwable $e) { $orders = []; $listings = []; }
    $marketplaceType = 'topups';
    $meta = ['title' => 'Top Up Orders | Admin Area', 'h1' => 'Top Up Orders'];
    view_file('admin/pages/marketplace/orders', compact('meta', 'orders', 'listings', 'marketplaceType'));
});

$router->get('/admin-area/top-up-order/:id', function ($id) {
    global $is_admin;
    if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }
    $purchase = lb_topup_route_load_purchase($id, 'admin');
    if (empty($purchase)) { redirect_url('admin-area/top-up-orders'); return; }
    $checkoutData = lb_topup_route_checkout_data($purchase);
    $chat = [];
    $chatPath = SYS_PATH . '/public/uploads/private/chat/selling_' . sha1('selling_topup_purchase_' . (int)$id) . '.json';
    if (is_file($chatPath)) {
        $chatData = json_decode((string)@file_get_contents($chatPath), true);
        $chat = is_array($chatData['messages'] ?? null) ? array_values(array_filter($chatData['messages'], static fn($m) => is_array($m) && empty($m['deleted']))) : [];
    }
    $meta = ['title' => 'Top Up Order #' . (int)$id . ' | Admin Area', 'h1' => 'Top Up Order #' . (int)$id];
    view_file('admin/pages/topups/order_view', compact('meta', 'purchase', 'checkoutData', 'chat'));
});

/* MERGED ROUTE: /lol/hire-pro-teammate */
$router->get('/lol/hire-pro-teammate', function () {
    header('Location: https://lolboost.gg/lol/pro-games');
});

/* Load merged legacy routes before dispatching the router. */
/* Consolidated reward, account compatibility and LoL Classic routes. */
$router->get('/lootboxes', function () {
    global $db, $is_client;

    $loggedIn = !empty($is_client) && defined('CLIENT_ID') && (int)CLIENT_ID > 0;
    $clientId = $loggedIn ? (int)CLIENT_ID : 0;
    $client = $loggedIn ? (db_get_row('clients', ['id' => $clientId], 1) ?: []) : [];

    $boxes = [];
    try {
        $boxes = $db->run("SELECT * FROM reward_boxes WHERE status = 1 ORDER BY sort_order ASC, id ASC") ?: [];
    } catch (Throwable $e) {
        $boxes = [];
    }

    foreach ($boxes as &$box) {
        $boxId = (int)($box['id'] ?? 0);
        $box['items_count'] = 0;
        $box['next_available_at'] = null;
        $box['can_open'] = true;

        if ($boxId <= 0) continue;

        try {
            $countRows = $db->run("SELECT COUNT(*) AS total FROM reward_box_items WHERE box_id = ? AND status = 1", $boxId) ?: [];
            $box['items_count'] = (int)($countRows[0]['total'] ?? 0);
        } catch (Throwable $e) {}

        if ($loggedIn && (int)($box['is_daily'] ?? 0) === 1) {
            $cooldown = max(1, (int)($box['cooldown_hours'] ?? 24));
            try {
                $lastRows = $db->run(
                    "SELECT created_at FROM reward_openings WHERE client_id = ? AND box_id = ? ORDER BY id DESC LIMIT 1",
                    $clientId,
                    $boxId
                ) ?: [];
                $last = $lastRows[0]['created_at'] ?? null;
                if ($last && strtotime((string)$last) !== false) {
                    $next = strtotime((string)$last) + ($cooldown * 3600);
                    if ($next > time()) {
                        $box['can_open'] = false;
                        $box['next_available_at'] = date('Y-m-d H:i:s', $next);
                    }
                }
            } catch (Throwable $e) {}
        }
    }
    unset($box);

    $recent_wins = [];
    try {
        $recent_wins = $db->run(
            "SELECT
                ro.id,
                ro.created_at,
                ro.client_id,
                COALESCE(c.username, CONCAT('Guest#', ro.client_id)) AS username,
                COALESCE(c.icon, '') AS client_icon,
                rbi.name AS item_name,
                rbi.rarity,
                rbi.reward_type,
                rbi.reward_value,
                COALESCE(rbi.icon, '') AS item_icon,
                rb.name AS box_name
             FROM reward_openings ro
             LEFT JOIN clients c ON c.id = ro.client_id
             LEFT JOIN reward_box_items rbi ON rbi.id = ro.item_id
             LEFT JOIN reward_boxes rb ON rb.id = ro.box_id
             ORDER BY ro.id DESC
             LIMIT 24"
        ) ?: [];
    } catch (Throwable $e) {
        $recent_wins = [];
    }

    $my_rewards = [];
    if ($loggedIn) {
        try {
            $my_rewards = $db->run(
                "SELECT cr.*, rbi.name AS item_name, rbi.rarity
                 FROM client_rewards cr
                 LEFT JOIN reward_box_items rbi ON rbi.id = cr.item_id
                 WHERE cr.client_id = ?
                 ORDER BY cr.id DESC
                 LIMIT 8",
                $clientId
            ) ?: [];
        } catch (Throwable $e) {
            $my_rewards = [];
        }
    }

    $meta = [
        'h1' => 'Lootboxes & Reward Boxes',
        'title' => 'Lootboxes & Reward Boxes | Free Daily Gift | LoLBoost.gg',
        'description' => t('Open LoLBoost reward boxes and win Reward Points, discount coupons, wallet credit and order perks. Log in to claim a free Daily Gift every 24 hours.'),
        'keywords' => 'lolboost lootboxes, reward boxes, daily gift, gaming rewards, discount coupons, reward points',
        'eyebrow' => 'LoLBoost Rewards',
        'badges' => [
            ['icon' => 'fa-gift', 'label' => 'Free Daily Gift'],
            ['icon' => 'fa-box-open', 'label' => 'Instant Rewards'],
            ['icon' => 'fa-shield-check', 'label' => 'Saved to Account'],
        ],
    ];

    view_file('website/pages/lootboxes', [
        'meta' => $meta,
        'client' => $client,
        'boxes' => $boxes,
        'recent_wins' => $recent_wins,
        'my_rewards' => $my_rewards,
        'is_client' => $loggedIn,
    ]);
});



/* ─────────── Client: selling account order view ─────────── */
/* ==== ported from old routes.php line 3121 ==== */
$router->get('/profile/account/:id', function ($account_id) {
    lb_client_selling_account_order_view($account_id);
});

/* ==== ported from old routes.php line 3124 ==== */
$router->get('/profile/accounts/:id', function ($account_id) {
    lb_client_selling_account_order_view($account_id);
});



/* ─────────── LoL Classic public boost routes ─────────── */
if (!function_exists('lb_register_lol_classic_route')) {
    function lb_register_lol_classic_route($router, string $path, int $formId, string $title, string $h1, string $description): void
    {
        // $path is needed for the canonical URL below, so it has to be imported
        // into the closure too — without it PHP 8 emits "Undefined variable $path"
        // and the canonical ends up as the bare domain.
        $router->get($path, function () use ($path, $formId, $title, $h1, $description) {
            $data = db_load_boost_form($formId);
            if (empty($data)) {
                http_response_code(404);
                return;
            }

            $meta = [
                'title' => $title,
                'h1' => $h1,
                'description' => $data['description'] ?? t($description),
                'keywords' => 'lol classic boost, league of legends classic boosting, lolboost.gg',
                'image' => 'three',
                'canonical' => 'https://lolboost.gg' . $path,
                'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
            ];

            view_file('website/boost/lol-classic', ['data' => $data, 'meta' => $meta]);
        });
    }
}

lb_register_lol_classic_route($router, '/lol-classic/rank-boost', 30, 'LoL Classic Rank Boost | LoLBoost.gg', 'LoL Classic Rank Boost', 'Climb from your current LoL Classic rank to your desired rank with a verified booster.');
lb_register_lol_classic_route($router, '/lol-classic/win-boost', 31, 'LoL Classic Win Boost | LoLBoost.gg', 'LoL Classic Win Boost', 'Purchase a selected number of wins for your LoL Classic account.');
lb_register_lol_classic_route($router, '/lol-classic/placements-boost', 32, 'LoL Classic Placement Boost | LoLBoost.gg', 'LoL Classic Placement Boost', 'Complete your LoL Classic placement games with a professional booster.');
lb_register_lol_classic_route($router, '/lol-classic/coaching', 33, 'LoL Classic Coaching | LoLBoost.gg', 'LoL Classic Coaching', 'Improve at LoL Classic with personalized coaching from an experienced player.');
lb_register_lol_classic_route($router, '/lol-classic/level-boost', 34, 'LoL Classic Level Boost | LoLBoost.gg', 'LoL Classic Level Boost', 'Level your LoL Classic account safely and efficiently.');
lb_register_lol_classic_route($router, '/lol-classic/pro-games', 35, 'LoL Classic Pro Games | LoLBoost.gg', 'LoL Classic Pro Games', 'Play LoL Classic games together with a high elo booster.');
lb_register_lol_classic_route($router, '/lol-classic/duo-pass', 36, 'LoL Classic Duo Pass | LoLBoost.gg', 'LoL Classic Duo Pass', 'Book LoL Classic duo hours and play together with a verified booster.');

/* ─────────── LoL: Ranked 5s, boost form ID 29 ─────────── */
$router->get('/lol/ranked-5s', function () {
    $data = db_load_boost_form(29);
    $meta = [
        'title' => 'LoL Ranked 5s Boost | 5 Stack Ranked Boost | LoLBoost.gg',
        'h1' => 'LoL Ranked 5s Boosting',
        'description' => $data['description'] ?? t('Play Ranked 5s with professional boosters. Choose your current rank, games, server and how many boosters join your 5 stack.'),
        'keywords' => 'lol ranked 5s boost, league of legends 5 stack boost, lol flex boost, lolboost.gg',
        'image' => 'three',
        'canonical' => 'https://lolboost.gg/lol/ranked-5s',
        'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1'
    ];
    view_file('website/boost/lol', ['data' => $data, 'meta' => $meta]);
});

/* Keep the former merged URL working, but use /lol/ranked-5s as canonical route. */
$router->get('/league-of-legends/ranked-5s', function () {
    redirect_url('lol/ranked-5s');
});

/* ─────────── Client area: Rewards ─────────── */
$router->group('profile', function ($router) {
    /* ==== ported from old routes.php line 4192 ==== */
        $router->get('rewards', function () {
            global $is_client, $db;
            if (!$is_client) { redirect_url(''); }

            $client_id = (int) CLIENT_ID;
            $client = db_get_row('clients', ['id' => $client_id], 1) ?: [];
            $boxes = [];
            try {
                $boxes = lb_rewards_route_rows("SELECT * FROM reward_boxes WHERE status = 1 ORDER BY sort_order ASC, id ASC");
            } catch (Throwable $e) { $boxes = []; }

            foreach ($boxes as &$box) {
                $box_id = (int)($box['id'] ?? 0);
                $box['items_count'] = 0;
                $box['next_available_at'] = null;
                $box['can_open'] = true;
                if ($box_id > 0) {
                    try {
                        $box['items_count'] = (int)lb_rewards_route_single("SELECT COUNT(*) FROM reward_box_items WHERE box_id = ? AND status = 1", [$box_id]);
                    } catch (Throwable $e) {}
                    if ((int)($box['is_daily'] ?? 0) === 1) {
                        $cooldown = max(1, (int)($box['cooldown_hours'] ?? 24));
                        try {
                            $last = lb_rewards_route_single("SELECT created_at FROM reward_openings WHERE client_id = ? AND box_id = ? ORDER BY id DESC LIMIT 1", [$client_id, $box_id]);
                            if ($last && strtotime((string)$last) !== false) {
                                $next = strtotime((string)$last) + ($cooldown * 3600);
                                if ($next > time()) {
                                    $box['can_open'] = false;
                                    $box['next_available_at'] = date('Y-m-d H:i:s', $next);
                                }
                            }
                        } catch (Throwable $e) {}
                    }
                }
            }
            unset($box);

            $recent_wins = [];
            try {
                $recent_wins = $db->run(
                    "SELECT
                        ro.id,
                        ro.created_at,
                        ro.client_id,
                        COALESCE(c.username, CONCAT('Guest#', ro.client_id)) AS username,
                        COALESCE(c.icon, '') AS client_icon,
                        rbi.name AS item_name,
                        rbi.rarity,
                        rbi.reward_type,
                        rbi.reward_value,
                        COALESCE(rbi.icon, '') AS item_icon,
                        rb.name AS box_name
                     FROM reward_openings ro
                     LEFT JOIN clients c ON c.id = ro.client_id
                     LEFT JOIN reward_box_items rbi ON rbi.id = ro.item_id
                     LEFT JOIN reward_boxes rb ON rb.id = ro.box_id
                     ORDER BY ro.id DESC
                     LIMIT 24"
                ) ?: [];
            } catch (Throwable $e) { $recent_wins = []; }

            $my_rewards = [];
            try {
                $my_rewards = $db->run(
                    "SELECT cr.*, rbi.name AS item_name, rbi.rarity
                     FROM client_rewards cr
                     LEFT JOIN reward_box_items rbi ON rbi.id = cr.item_id
                     WHERE cr.client_id = ?
                     ORDER BY cr.id DESC
                     LIMIT 20",
                    [$client_id]
                ) ?: [];
            } catch (Throwable $e) { $my_rewards = []; }

            $meta = [
                'title' => 'LB Rewards | LoLBoost.gg',
                'h1' => 'LB Rewards',
                'description' => 'Open reward boxes with Reward Points and win bonus points, coupons, wallet credit and order perks.',
            ];

            view_file('client/pages/rewards/list', compact('meta', 'client', 'boxes', 'recent_wins', 'my_rewards'));
        });

    /* ==== ported from old routes.php line 4276 ==== */
        $router->get('rewards/wins', function () {
            global $is_client, $db;
            if (!$is_client) { redirect_url(''); }

            $client_id = (int) CLIENT_ID;
            $client = db_get_row('clients', ['id' => $client_id], 1) ?: [];
            $wins = [];

            try {
                $wins = lb_rewards_route_rows(
                    "SELECT
                        cr.id,
                        cr.opening_id,
                        cr.box_id,
                        cr.item_id,
                        cr.reward_type,
                        cr.reward_value,
                        cr.status,
                        cr.coupon_code,
                        cr.expires_at,
                        COALESCE(ro.created_at, cr.created_at) AS won_at,
                        ro.cost_coins,
                        rbi.name AS item_name,
                        rbi.rarity,
                        COALESCE(rbi.icon, '') AS item_icon,
                        rb.name AS box_name,
                        rb.slug AS box_slug
                     FROM client_rewards cr
                     LEFT JOIN reward_openings ro ON ro.id = cr.opening_id
                     LEFT JOIN reward_box_items rbi ON rbi.id = cr.item_id
                     LEFT JOIN reward_boxes rb ON rb.id = cr.box_id
                     WHERE cr.client_id = ?
                     ORDER BY COALESCE(ro.created_at, cr.created_at) DESC, cr.id DESC
                     LIMIT 300",
                    [$client_id]
                );
            } catch (Throwable $e) { $wins = []; }

            $meta = [
                'title' => 'My Wins | LB Rewards | LoLBoost.gg',
                'h1' => '',
                'description' => '',
            ];

            view_file('client/pages/rewards/wins', compact('meta', 'client', 'wins'));
        });

    /* ==== ported from old routes.php line 4323 ==== */
        $router->get('rewards/:slug', function ($slug) {
            global $is_client, $db;
            if (!$is_client) { redirect_url(''); }

            $slug = preg_replace('/[^a-z0-9\-]/i', '', (string)$slug);

            // Some router setups match /profile/rewards/wins as rewards/:slug.
            // Handle it here too so the My Wins page never falls through to the box lookup redirect.
            if (strtolower($slug) === 'wins') {
                $client_id = (int) CLIENT_ID;
                $client = db_get_row('clients', ['id' => $client_id], 1) ?: [];
                $wins = [];

                try {
                    $wins = lb_rewards_route_rows(
                        "SELECT
                            cr.id,
                            cr.opening_id,
                            cr.box_id,
                            cr.item_id,
                            cr.reward_type,
                            cr.reward_value,
                            cr.status,
                            cr.coupon_code,
                            cr.expires_at,
                            COALESCE(ro.created_at, cr.created_at) AS won_at,
                            ro.cost_coins,
                            rbi.name AS item_name,
                            rbi.rarity,
                            COALESCE(rbi.icon, '') AS item_icon,
                            rb.name AS box_name,
                            rb.slug AS box_slug
                         FROM client_rewards cr
                         LEFT JOIN reward_openings ro ON ro.id = cr.opening_id
                         LEFT JOIN reward_box_items rbi ON rbi.id = cr.item_id
                         LEFT JOIN reward_boxes rb ON rb.id = cr.box_id
                         WHERE cr.client_id = ?
                         ORDER BY COALESCE(ro.created_at, cr.created_at) DESC, cr.id DESC
                         LIMIT 300",
                        [$client_id]
                    );
                } catch (Throwable $e) { $wins = []; }

                $meta = [
                    'title' => 'My Wins | LB Rewards | LoLBoost.gg',
                    'h1' => '',
                    'description' => '',
                ];

                view_file('client/pages/rewards/wins', compact('meta', 'client', 'wins'));
                return;
            }
            $box = [];
            try { $box = lb_rewards_route_row("SELECT * FROM reward_boxes WHERE slug = ? AND status = 1 LIMIT 1", [$slug]); } catch (Throwable $e) { $box = []; }
            if (empty($box)) {
                $slugFallback = strtolower(str_replace('-', ' ', $slug));
                $box = lb_rewards_route_row("SELECT * FROM reward_boxes WHERE LOWER(name) = ? AND status = 1 LIMIT 1", [$slugFallback]);
            }
            if (empty($box)) { redirect_url('profile/rewards'); }

            $items = [];
            try { $items = lb_rewards_route_rows("SELECT * FROM reward_box_items WHERE box_id = ? AND status = 1 ORDER BY FIELD(rarity,'legendary','epic','rare','uncommon','common'), chance ASC, id ASC", [(int)$box['id']]); } catch (Throwable $e) { $items = []; }

            $client = db_get_row('clients', ['id' => (int)CLIENT_ID], 1) ?: [];
            $can_open = true;
            $next_available_at = null;
            if ((int)($box['is_daily'] ?? 0) === 1) {
                $cooldown = max(1, (int)($box['cooldown_hours'] ?? 24));
                try {
                    $last = lb_rewards_route_single("SELECT created_at FROM reward_openings WHERE client_id = ? AND box_id = ? ORDER BY id DESC LIMIT 1", [(int)CLIENT_ID, (int)$box['id']]);
                    if ($last && strtotime((string)$last) !== false) {
                        $next = strtotime((string)$last) + ($cooldown * 3600);
                        if ($next > time()) { $can_open = false; $next_available_at = date('Y-m-d H:i:s', $next); }
                    }
                } catch (Throwable $e) {}
            }

            $meta = [
                'title' => ($box['name'] ?? 'Reward Box') . ' | LB Rewards | LoLBoost.gg',
                'h1' => $box['name'] ?? 'Reward Box',
                'description' => 'Open this reward box and win Reward Points, coupons and order perks.',
            ];

            view_file('client/pages/rewards/view', compact('meta', 'client', 'box', 'items', 'can_open', 'next_available_at'));
        });


});

/* ─────────── Admin area: Lootboxes + Applications ─────────── */
$router->group('admin-area', function () {
    global $router, $is_admin;
    /* ==== ported from old routes.php line 9274 ==== */
        $router->get('/lootboxes', function () {
            global $is_admin, $db;

            if (!$is_admin) {
                redirect_url('admin-area/auth/login');
                return;
            }

            $data = [];
            try {
                $data = $db->run(
                    "SELECT
                        ro.id,
                        ro.client_id,
                        ro.box_id,
                        ro.item_id,
                        ro.cost_coins,
                        ro.created_at,
                        COALESCE(c.username, CONCAT('Guest#', ro.client_id)) AS client_name,
                        COALESCE(c.email, '') AS client_email,
                        COALESCE(c.icon, '') AS client_icon,
                        COALESCE(c.reward_points, 0) AS client_reward_points,
                        rb.name AS box_name,
                        rb.slug AS box_slug,
                        rbi.name AS item_name,
                        rbi.rarity,
                        rbi.reward_type,
                        rbi.reward_value,
                        COALESCE(rbi.icon, '') AS item_icon,
                        cr.status AS reward_status,
                        cr.coupon_code
                     FROM reward_openings ro
                     LEFT JOIN clients c ON c.id = ro.client_id
                     LEFT JOIN reward_boxes rb ON rb.id = ro.box_id
                     LEFT JOIN reward_box_items rbi ON rbi.id = ro.item_id
                     LEFT JOIN client_rewards cr ON cr.opening_id = ro.id
                     ORDER BY ro.id DESC
                     LIMIT 1000"
                ) ?: [];
            } catch (Throwable $e) {
                $data = [];
            }

            view_file('admin/pages/lootboxes/list', ['data' => $data]);
        });

});

$router->run();
