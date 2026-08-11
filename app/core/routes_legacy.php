<?php
/**
 * Merged legacy routes from lolboost.gg
 * Appended intentionally at the end of routes.php — the codebase already
 * re-opens groups this way (see the second 'profile' group), so we do not
 * have to touch the 14k-line routes.php itself.
 */
global $router;

/* ─────────── Public: Lootboxes ─────────── */
/* ==== ported from old routes.php line 827 ==== */
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
        $router->get($path, function () use ($formId, $title, $h1, $description) {
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

    /* ==== ported from old routes.php line 10129 ==== */
        $router->get('applications', function () {
            global $is_admin, $db;
            if (!$is_admin) { redirect_url('admin-area/auth/login'); return; }

            $sellerMigrations = [
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
            foreach ($sellerMigrations as $sql) {
                try { $db->run($sql); } catch (\Throwable $e) {}
            }

            $rows = [];

            // Only users who already completed the onboarding details/documents should appear here.
            // This is not the public job application list.
            try {
                $boosters = $db->run(
                    "SELECT 'booster' AS type, b.id AS id, b.id AS user_id, b.username, b.email, b.created_at,
                            COALESCE(NULLIF(pd.fullname, ''), b.username) AS fullname,
                            pd.country, pd.address, b.discord_id AS discord,
                            'pending' AS status, '' AS id_front, '' AS id_back, '' AS selfie
                     FROM booster_personal_details pd
                     INNER JOIN boosters b ON b.id = pd.booster_id
                     WHERE b.is_egirl = 0
                       AND b.verified = 0
                       AND (b.is_banned = 0 OR b.is_banned IS NULL)
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
                            'pending' AS status, '' AS id_front, '' AS id_back, '' AS selfie
                     FROM booster_personal_details pd
                     INNER JOIN boosters b ON b.id = pd.booster_id
                     WHERE b.is_egirl = 1
                       AND b.verified = 0
                       AND (b.is_banned = 0 OR b.is_banned IS NULL)
                       AND (COALESCE(pd.fullname, '') <> '' OR COALESCE(pd.country, '') <> '' OR COALESCE(pd.address, '') <> '')
                     ORDER BY b.created_at DESC"
                ) ?: [];
                $rows = array_merge($rows, is_array($egirls) ? $egirls : ($egirls ? $egirls->fetchAll() : []));
            } catch (\Throwable $e) {}

            try {
                $sellers = $db->run(
                    "SELECT 'seller' AS type, id, id AS user_id, username, email, created_at,
                            COALESCE(NULLIF(fullname, ''), username) AS fullname, country, address, discord,
                            COALESCE(onboarding_status, 'pending') AS status, id_front, id_back, selfie
                     FROM sellers
                     WHERE (is_banned IS NULL OR is_banned = 0)
                       AND (is_active = 0 OR COALESCE(onboarding_status, 'pending') IN ('pending','rejected'))
                       AND COALESCE(onboarding_status, 'pending') IN ('pending','rejected')
                       AND COALESCE(id_front, '') <> ''
                       AND COALESCE(id_back, '') <> ''
                       AND COALESCE(selfie, '') <> ''
                     ORDER BY id DESC"
                ) ?: [];
                $rows = array_merge($rows, is_array($sellers) ? $sellers : ($sellers ? $sellers->fetchAll() : []));
            } catch (\Throwable $e) {}

            usort($rows, function ($a, $b) {
                return strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? ''));
            });

            $meta = [
                'title'       => 'Onboarding Applications — Admin Area | LoLBoost.gg',
            ];
            view_file('admin/pages/applications/onboarding', compact('meta', 'rows'));
        });


});
