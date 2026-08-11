<?php

if (!function_exists('seller_api_json')) {
    function seller_api_json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('seller_api_read_json')) {
    function seller_api_read_json(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('seller_api_bearer_token')) {
    function seller_api_bearer_token(): string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if ($header === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            return trim($m[1]);
        }
        return '';
    }
}

if (!function_exists('seller_api_ensure_tables')) {
    function seller_api_ensure_tables(): void
    {
        global $db;
        $db->run("CREATE TABLE IF NOT EXISTS seller_api_keys (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT UNSIGNED NOT NULL,
            name VARCHAR(100) NULL,
            api_key_hash CHAR(64) NOT NULL,
            key_prefix VARCHAR(30) NOT NULL,
            permissions TEXT NULL,
            last_used_at DATETIME NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_seller_api_key_hash (api_key_hash),
            KEY idx_seller_api_keys_seller (seller_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->run("CREATE TABLE IF NOT EXISTS seller_webhooks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT UNSIGNED NOT NULL,
            url VARCHAR(500) NOT NULL,
            secret VARCHAR(255) NOT NULL,
            events TEXT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_seller_webhooks_seller (seller_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->run("CREATE TABLE IF NOT EXISTS seller_webhook_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT UNSIGNED NOT NULL,
            webhook_id INT UNSIGNED NOT NULL,
            account_id INT UNSIGNED NULL,
            event VARCHAR(100) NOT NULL,
            payload LONGTEXT NOT NULL,
            response_code INT NULL,
            response_body TEXT NULL,
            success TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_seller_webhook_logs_seller (seller_id),
            KEY idx_seller_webhook_logs_account (account_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->run("CREATE TABLE IF NOT EXISTS seller_api_request_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT UNSIGNED NOT NULL,
            api_key_id INT UNSIGNED NULL,
            method VARCHAR(12) NOT NULL,
            endpoint VARCHAR(255) NOT NULL,
            account_id INT UNSIGNED NULL,
            external_id VARCHAR(191) NULL,
            status_code INT NOT NULL,
            request_body LONGTEXT NULL,
            response_body LONGTEXT NULL,
            ip_address VARCHAR(64) NULL,
            user_agent VARCHAR(500) NULL,
            duration_ms INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_api_logs_seller_created (seller_id, created_at),
            KEY idx_api_logs_account (account_id),
            KEY idx_api_logs_external (external_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        try { $db->run("ALTER TABLE selling_accounts ADD COLUMN IF NOT EXISTS external_id VARCHAR(191) NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_accounts ADD COLUMN IF NOT EXISTS api_source TINYINT(1) NOT NULL DEFAULT 0"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_accounts ADD COLUMN IF NOT EXISTS api_created_at DATETIME NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_accounts ADD COLUMN IF NOT EXISTS api_updated_at DATETIME NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_accounts ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE seller_api_request_logs ADD COLUMN IF NOT EXISTS entity_type VARCHAR(30) NULL AFTER endpoint"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE seller_api_request_logs ADD COLUMN IF NOT EXISTS item_id INT UNSIGNED NULL AFTER account_id"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE seller_webhook_logs ADD COLUMN IF NOT EXISTS entity_type VARCHAR(30) NULL AFTER webhook_id"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE seller_webhook_logs ADD COLUMN IF NOT EXISTS item_id INT UNSIGNED NULL AFTER account_id"); } catch (Throwable $e) {}

        try { $db->run("ALTER TABLE selling_items ADD COLUMN IF NOT EXISTS external_id VARCHAR(191) NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_items ADD COLUMN IF NOT EXISTS api_source TINYINT(1) NOT NULL DEFAULT 0"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_items ADD COLUMN IF NOT EXISTS api_created_at DATETIME NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_items ADD COLUMN IF NOT EXISTS api_updated_at DATETIME NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_items ADD UNIQUE KEY uq_seller_item_external_id (seller_id, external_id)"); } catch (Throwable $e) {}

        // Top-Ups API columns
        try { $db->run("ALTER TABLE selling_topups ADD COLUMN IF NOT EXISTS external_id VARCHAR(191) NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_topups ADD COLUMN IF NOT EXISTS api_source TINYINT(1) NOT NULL DEFAULT 0"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_topups ADD COLUMN IF NOT EXISTS api_created_at DATETIME NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_topups ADD COLUMN IF NOT EXISTS api_updated_at DATETIME NULL"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE selling_topups ADD UNIQUE KEY uq_seller_topup_external_id (seller_id, external_id)"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE seller_webhook_logs ADD COLUMN IF NOT EXISTS topup_id INT UNSIGNED NULL AFTER item_id"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE seller_api_request_logs ADD COLUMN IF NOT EXISTS topup_id INT UNSIGNED NULL AFTER item_id"); } catch (Throwable $e) {}
    }
}

if (!function_exists('seller_api_auth')) {
    function seller_api_auth(): array
    {
        global $db;
        seller_api_ensure_tables();

        $token = seller_api_bearer_token();
        if ($token === '') {
            seller_api_json(['message' => 'Missing bearer token.'], 401);
        }

        $hash = hash('sha256', $token);
        $row = $db->row(
            "SELECT k.*, s.username, s.email, s.is_active AS seller_is_active, s.is_banned
               FROM seller_api_keys k
               INNER JOIN sellers s ON s.id = k.seller_id
              WHERE k.api_key_hash = ? AND k.is_active = 1
              LIMIT 1",
            $hash
        );

        if (empty($row) || (int)($row['is_banned'] ?? 0) === 1 || (int)($row['seller_is_active'] ?? 0) !== 1) {
            seller_api_json(['message' => 'Invalid or inactive API key.'], 401);
        }

        $db->run("UPDATE seller_api_keys SET last_used_at = NOW() WHERE id = ?", (int)$row['id']);
        return $row;
    }
}

if (!function_exists('seller_api_decode_json_field')) {
    function seller_api_decode_json_field($value)
    {
        if ($value === null || $value === '') { return null; }
        $decoded = json_decode((string)$value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}

if (!function_exists('seller_api_status_from_account')) {
    function seller_api_status_from_account(array $account): string
    {
        if ((int)($account['sold'] ?? 0) === 1) { return 'sold'; }
        return ((int)($account['active'] ?? 1) === 1) ? 'listed' : 'archived';
    }
}

if (!function_exists('seller_api_public_account')) {
    function seller_api_public_account(array $account): array
    {
        return [
            'id' => (int)($account['id'] ?? 0),
            'external_id' => $account['external_id'] ?? null,
            'game' => [
                'slug' => $account['game'] ?? null,
                'name' => strtoupper((string)($account['game'] ?? '')),
            ],
            'title' => $account['title'] ?? null,
            'slug' => $account['slug'] ?? null,
            'description' => $account['description'] ?? null,
            'parameters' => seller_api_decode_json_field($account['game_data'] ?? null),
            'status' => seller_api_status_from_account($account),
            'delivery_time' => [
                'duration' => 1,
                'unit' => 'hours',
                'format' => '1 hr',
                'format_long' => '1 hour',
                'seconds' => 3600,
            ],
            'is_manual_delivery' => (($account['delivery_type'] ?? 'manual') === 'manual'),
            'credentials' => [
                'login' => $account['login'] ?? null,
                'password' => $account['password'] ?? null,
                'email_login' => $account['email'] ?? null,
                'email_password' => $account['email_password'] ?? null,
            ],
            'delivery_instructions' => $account['delivery_instructions'] ?? null,
            'price' => isset($account['price']) ? (string)$account['price'] : null,
            'server' => $account['server'] ?? null,
            'level' => isset($account['level']) ? (int)$account['level'] : null,
            'blue_essence' => isset($account['blue_essence']) ? (int)$account['blue_essence'] : null,
            'riot_points' => isset($account['riot_points']) ? (int)$account['riot_points'] : null,
            'champion_count' => isset($account['champion_count']) ? (int)$account['champion_count'] : null,
            'skin_count' => isset($account['skin_count']) ? (int)$account['skin_count'] : null,
            'image_urls' => seller_api_decode_json_field($account['images'] ?? null) ?: [],
            'created_at' => !empty($account['created_at']) ? strtotime((string)$account['created_at']) : null,
            'updated_at' => !empty($account['updated_at']) ? strtotime((string)$account['updated_at']) : null,
            'listed_at' => ((int)($account['active'] ?? 0) === 1 && !empty($account['created_at'])) ? strtotime((string)$account['created_at']) : null,
            'sold_at' => $account['sold_at'] ?? null,
        ];
    }
}

if (!function_exists('seller_api_log_request')) {
    function seller_api_log_request(array $auth, string $method, string $endpoint, int $statusCode, $requestBody = null, $responseBody = null, ?int $accountId = null, ?string $externalId = null, ?float $startedAt = null, string $entityType = 'account', ?int $itemId = null): void
    {
        global $db;
        try {
            $duration = $startedAt ? (int)round((microtime(true) - $startedAt) * 1000) : null;
            db_add_row('seller_api_request_logs', [
                'seller_id' => (int)$auth['seller_id'],
                'api_key_id' => (int)($auth['id'] ?? 0),
                'method' => strtoupper($method),
                'endpoint' => $endpoint,
                'entity_type' => $entityType,
                'account_id' => $accountId,
                'item_id' => $itemId,
                'external_id' => $externalId,
                'status_code' => $statusCode,
                'request_body' => $requestBody === null ? null : substr(json_encode($requestBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 0, 5000),
                'response_body' => $responseBody === null ? null : substr(json_encode($responseBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 0, 5000),
                'ip_address' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
                'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
                'duration_ms' => $duration,
            ]);
        } catch (Throwable $e) {}
    }
}

if (!function_exists('seller_api_finish')) {
    function seller_api_finish(array $auth, string $method, string $endpoint, array $data, int $status = 200, $requestBody = null, ?int $accountId = null, ?string $externalId = null, ?float $startedAt = null, string $entityType = 'account', ?int $itemId = null): void
    {
        seller_api_log_request($auth, $method, $endpoint, $status, $requestBody, $data, $accountId, $externalId, $startedAt, $entityType, $itemId);
        seller_api_json($data, $status);
    }
}

if (!function_exists('seller_api_find_account')) {
    function seller_api_find_account($idOrExternalId, int $sellerId): ?array
    {
        global $db;
        if (is_numeric($idOrExternalId)) {
            $row = $db->row("SELECT * FROM selling_accounts WHERE id = ? AND seller_id = ? LIMIT 1", (int)$idOrExternalId, $sellerId);
        } else {
            $row = $db->row("SELECT * FROM selling_accounts WHERE external_id = ? AND seller_id = ? LIMIT 1", (string)$idOrExternalId, $sellerId);
        }
        return !empty($row) ? $row : null;
    }
}

if (!function_exists('seller_api_create_account')) {
    function seller_api_create_account(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $data = seller_api_read_json();
        $external_id = trim((string)($data['external_id'] ?? ''));
        $title = trim((string)($data['title'] ?? ''));
        // Accept "40.99" as well as 4099 (cents), same as the JSON importer.
        $price = seller_api_account_price_cents($data['price'] ?? 0);
        $endpoint = '/api/v1/accounts';

        if ($external_id === '' || $title === '' || $price <= 0) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'external_id, title and price are required.'], 422, $data, null, $external_id, $startedAt);
        }

        $allowedStatuses = ['draft', 'pending', 'listed', 'archived'];
        $status = in_array((string)($data['status'] ?? 'listed'), $allowedStatuses, true) ? (string)($data['status'] ?? 'listed') : 'listed';
        $active = $status === 'listed' ? 1 : 0;

        // Gallery images: image_ids reference /api/v1/images uploads and are resolved to URLs.
        $imageIds = seller_api_extract_image_ids($data);
        $galleryUrls = [];
        if (!empty($imageIds)) {
            $galleryUrls = seller_gallery_urls_from_ids($seller_id, $imageIds);
            if (count($galleryUrls) !== count($imageIds)) {
                seller_api_finish($auth, 'POST', $endpoint, ['message' => 'One or more image_ids do not exist for this seller.'], 422, $data, null, $external_id, $startedAt);
            }
        }
        if (empty($galleryUrls) && !empty($data['image_urls']) && is_array($data['image_urls'])) {
            $galleryUrls = array_values($data['image_urls']);
        }
        if (empty($galleryUrls) && !empty($data['images']) && is_array($data['images'])) {
            // Only plain URL strings, ids were already handled above.
            $galleryUrls = array_values(array_filter($data['images'], static fn($v) => is_string($v) && $v !== ''));
        }

        // Ranks, LP, level, BE/RP, champions... same mapping the JSON importer uses.
        $mapped = seller_api_account_mapped_fields($data);

        $insert = [
            'seller_id' => $seller_id,
            'external_id' => $external_id,
            'api_source' => 1,
            'api_created_at' => date('Y-m-d H:i:s'),
            'api_updated_at' => date('Y-m-d H:i:s'),
            'title' => $title,
            'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title . '-' . substr(sha1($external_id . microtime(true)), 0, 6)), '-')),
            'price' => $price,
            'game' => $data['game'] ?? 'lol',
            'server' => $data['server'] ?? null,
            'description' => $data['description'] ?? null,
            'images' => !empty($galleryUrls) ? json_encode($galleryUrls, JSON_UNESCAPED_SLASHES) : null,
            'gallery_image_ids' => !empty($imageIds) ? json_encode(array_values($imageIds)) : null,
            'delivery_type' => !empty($data['is_manual_delivery']) ? 'manual' : ($data['delivery_type'] ?? 'instant'),
            'login' => $data['credentials']['login'] ?? ($data['login'] ?? null),
            'password' => $data['credentials']['password'] ?? ($data['password'] ?? null),
            'email' => $data['credentials']['email_login'] ?? ($data['email'] ?? null),
            'email_password' => $data['credentials']['email_password'] ?? ($data['email_password'] ?? null),
            'delivery_instructions' => $data['delivery_instructions'] ?? null,
            'level' => $data['level'] ?? null,
            'blue_essence' => $data['blue_essence'] ?? null,
            'riot_points' => $data['riot_points'] ?? null,
            'champion_count' => $data['champion_count'] ?? null,
            'skin_count' => $data['skin_count'] ?? null,
            'active' => $active,
            'sold' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Mapped rank/LP/stat columns win over the raw payload defaults above.
        $insert = array_merge($insert, $mapped);

        try {
            $id = db_add_row('selling_accounts', $insert);
        } catch (Throwable $e) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Could not create account. external_id may already exist.'], 409, $data, null, $external_id, $startedAt);
        }

        $account = $db->row("SELECT * FROM selling_accounts WHERE id = ? AND seller_id = ? LIMIT 1", (int)$id, $seller_id);
        if (!empty($account)) {
            seller_api_dispatch_webhook('account.created', $account);
            if ((int)($account['active'] ?? 0) === 1) { seller_api_dispatch_webhook('account.listed', $account); }
        }
        seller_api_finish($auth, 'POST', $endpoint, ['data' => seller_api_public_account($account)], 201, $data, (int)$id, $external_id, $startedAt);
    }
}

if (!function_exists('seller_api_list_accounts')) {
    function seller_api_list_accounts(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $filter = $_GET['filter'] ?? [];
        if (!is_array($filter)) { $filter = []; }
        foreach (['search','id','external_id','title','game','game_id','status'] as $flat) {
            if (isset($_GET[$flat]) && !isset($filter[$flat])) { $filter[$flat] = $_GET[$flat]; }
        }

        $where = ['seller_id = ?'];
        $args = [$seller_id];
        if (!empty($filter['search'])) {
            $search = '%' . (string)$filter['search'] . '%';
            $where[] = '(title LIKE ? OR description LIKE ? OR external_id LIKE ? OR CAST(id AS CHAR) = ?)';
            array_push($args, $search, $search, $search, (string)$filter['search']);
        }
        foreach (['id','external_id','title','game','game_id'] as $field) {
            if (!empty($filter[$field])) {
                $column = $field === 'game_id' ? 'game' : $field;
                $values = array_values(array_filter(array_map('trim', explode(',', (string)$filter[$field])), 'strlen'));
                if (!empty($values)) {
                    $where[] = $column . ' IN (' . implode(',', array_fill(0, count($values), '?')) . ')';
                    foreach ($values as $v) { $args[] = $v; }
                }
            }
        }
        if (!empty($filter['status'])) {
            $statuses = array_values(array_filter(array_map('trim', explode(',', (string)$filter['status'])), 'strlen'));
            $parts = [];
            foreach ($statuses as $s) {
                if ($s === 'listed') { $parts[] = '(sold = 0 AND active = 1)'; }
                if (in_array($s, ['draft','pending','archived','unlisted'], true)) { $parts[] = '(sold = 0 AND active = 0)'; }
                if ($s === 'sold') { $parts[] = '(sold = 1)'; }
            }
            if (!empty($parts)) { $where[] = '(' . implode(' OR ', $parts) . ')'; }
        }

        $sortMap = ['id' => 'id', 'price' => 'price', 'created_at' => 'created_at', 'updated_at' => 'updated_at', 'listed_at' => 'created_at'];
        $sort = (string)($_GET['sort'] ?? '-updated_at');
        $dir = str_starts_with($sort, '-') ? 'DESC' : 'ASC';
        $sortKey = ltrim($sort, '-');
        $orderColumn = $sortMap[$sortKey] ?? 'id';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 15)));
        $offset = ($page - 1) * $perPage;
        $whereSql = implode(' AND ', $where);

        $total = (int)($db->cell("SELECT COUNT(*) FROM selling_accounts WHERE {$whereSql}", ...$args) ?? 0);
        $rows = $db->run("SELECT * FROM selling_accounts WHERE {$whereSql} ORDER BY {$orderColumn} {$dir}, id DESC LIMIT {$perPage} OFFSET {$offset}", ...$args) ?: [];
        $lastPage = max(1, (int)ceil($total / $perPage));
        $response = [
            'data' => array_map('seller_api_public_account', $rows),
            'meta' => [
                'current_page' => $page,
                'from' => $total ? $offset + 1 : null,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'to' => $total ? min($offset + $perPage, $total) : null,
                'total' => $total,
            ],
            'links' => [
                'first' => '?page=1',
                'last' => '?page=' . $lastPage,
                'prev' => $page > 1 ? '?page=' . ($page - 1) : null,
                'next' => $page < $lastPage ? '?page=' . ($page + 1) : null,
            ],
        ];
        seller_api_finish($auth, 'GET', '/api/v1/accounts', $response, 200, $_GET, null, null, $startedAt);
    }
}

if (!function_exists('seller_api_get_account')) {
    function seller_api_get_account($id): void
    {
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $row = seller_api_find_account($id, (int)$auth['seller_id']);
        if (empty($row)) { seller_api_finish($auth, 'GET', '/api/v1/accounts/' . $id, ['message' => 'Account not found.'], 404, null, null, is_numeric($id) ? null : (string)$id, $startedAt); }
        seller_api_finish($auth, 'GET', '/api/v1/accounts/' . $id, ['data' => seller_api_public_account($row)], 200, null, (int)$row['id'], $row['external_id'] ?? null, $startedAt);
    }
}

if (!function_exists('seller_api_update_account')) {
    function seller_api_update_account($id): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $row = seller_api_find_account($id, $seller_id);
        $data = seller_api_read_json();
        if (empty($row)) { seller_api_finish($auth, 'PATCH', '/api/v1/accounts/' . $id, ['message' => 'Account not found.'], 404, $data, null, is_numeric($id) ? null : (string)$id, $startedAt); }
        if ((int)($row['sold'] ?? 0) === 1) { seller_api_finish($auth, 'PATCH', '/api/v1/accounts/' . $id, ['message' => 'Sold accounts cannot be changed.'], 409, $data, (int)$row['id'], $row['external_id'] ?? null, $startedAt); }

        $allowed = ['title','price','game','server','description','login','password','email','email_password','delivery_instructions','level','blue_essence','riot_points','champion_count','skin_count'];
        $update = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) { $update[$field] = $data[$field]; }
        }
        if (array_key_exists('status', $data)) {
            $update['active'] = ((string)$data['status'] === 'listed') ? 1 : 0;
        }
        if (array_key_exists('price', $data)) { $update['price'] = seller_api_account_price_cents($data['price']); }

        // Rank/LP/stat mapping, only when the payload actually touches one of those fields.
        $rankKeys = ['parameters','game_data','current_rank','rank','solo_rank','current_division','division','current_lp','lp',
                     'flex_rank','flex_division','flex_lp','previous_rank','prev_rank','previous_division','prev_division','previous_lp','prev_lp',
                     'rank_label','level','blue_essence','riot_points','winrate_percent','winrate','champions','skins','roles',
                     'champion_count','skin_count','in_game_name','ign','summoner_name'];
        $touchesRank = (bool)array_intersect($rankKeys, array_keys($data));
        if ($touchesRank) {
            $mapData = $data;
            if (!isset($mapData['game'])) { $mapData['game'] = $row['game'] ?? 'lol'; }
            if (!isset($mapData['parameters']) && !isset($mapData['game_data'])) {
                $existing = seller_api_decode_json_field($row['game_data'] ?? null);
                if (is_array($existing)) { $mapData['parameters'] = $existing; }
            }
            $update = array_merge($update, seller_api_account_mapped_fields($mapData));
        }

        // Gallery images (image_ids) or plain image_urls.
        $imageIds = seller_api_extract_image_ids($data);
        if (!empty($imageIds)) {
            $galleryUrls = seller_gallery_urls_from_ids($seller_id, $imageIds);
            if (count($galleryUrls) !== count($imageIds)) {
                seller_api_finish($auth, 'PATCH', '/api/v1/accounts/' . $id, ['message' => 'One or more image_ids do not exist for this seller.'], 422, $data, (int)$row['id'], $row['external_id'] ?? null, $startedAt);
            }
            $update['images'] = json_encode($galleryUrls, JSON_UNESCAPED_SLASHES);
            $update['gallery_image_ids'] = json_encode(array_values($imageIds));
        } elseif (array_key_exists('image_urls', $data)) {
            $update['images'] = json_encode($data['image_urls'], JSON_UNESCAPED_SLASHES);
        }
        if (isset($data['credentials']) && is_array($data['credentials'])) {
            if (array_key_exists('login', $data['credentials'])) { $update['login'] = $data['credentials']['login']; }
            if (array_key_exists('password', $data['credentials'])) { $update['password'] = $data['credentials']['password']; }
            if (array_key_exists('email_login', $data['credentials'])) { $update['email'] = $data['credentials']['email_login']; }
            if (array_key_exists('email_password', $data['credentials'])) { $update['email_password'] = $data['credentials']['email_password']; }
        }
        $update['api_updated_at'] = date('Y-m-d H:i:s');
        if (empty($update)) { seller_api_finish($auth, 'PATCH', '/api/v1/accounts/' . $id, ['message' => 'No valid fields sent.'], 422, $data, (int)$row['id'], $row['external_id'] ?? null, $startedAt); }
        db_update_row('selling_accounts', ['id' => (int)$row['id'], 'seller_id' => $seller_id], $update);
        $updated = $db->row("SELECT * FROM selling_accounts WHERE id = ? AND seller_id = ? LIMIT 1", (int)$row['id'], $seller_id);
        seller_api_finish($auth, 'PATCH', '/api/v1/accounts/' . $id, ['data' => seller_api_public_account($updated)], 200, $data, (int)$row['id'], $updated['external_id'] ?? null, $startedAt);
    }
}

if (!function_exists('seller_api_delete_account')) {
    function seller_api_delete_account($id): void
    {
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $row = seller_api_find_account($id, $seller_id);
        if (empty($row)) { seller_api_finish($auth, 'DELETE', '/api/v1/accounts/' . $id, ['message' => 'Account not found.'], 404, null, null, is_numeric($id) ? null : (string)$id, $startedAt); }
        if ((int)($row['sold'] ?? 0) === 1) { seller_api_finish($auth, 'DELETE', '/api/v1/accounts/' . $id, ['message' => 'Sold accounts cannot be deleted.'], 409, null, (int)$row['id'], $row['external_id'] ?? null, $startedAt); }
        db_update_row('selling_accounts', ['id' => (int)$row['id'], 'seller_id' => $seller_id], ['active' => 0, 'api_updated_at' => date('Y-m-d H:i:s')]);
        $updated = seller_api_find_account($row['id'], $seller_id);
        if (!empty($updated)) { seller_api_dispatch_webhook('account.archived', $updated, ['archived_reason' => 'api_delete']); }
        seller_api_finish($auth, 'DELETE', '/api/v1/accounts/' . $id, ['message' => 'Account archived.', 'data' => seller_api_public_account($updated ?: $row)], 200, null, (int)$row['id'], $row['external_id'] ?? null, $startedAt);
    }
}


if (!function_exists('seller_api_bulk_remove_accounts')) {
    function seller_api_bulk_remove_accounts(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $data = seller_api_read_json();
        $endpoint = '/api/v1/accounts/bulk-remove';

        $externalIds = $data['external_ids'] ?? $data['external_id'] ?? [];
        $lolboostIds = $data['lolboost_account_ids'] ?? $data['ids'] ?? [];
        if (is_string($externalIds)) { $externalIds = array_filter(array_map('trim', explode(',', $externalIds)), 'strlen'); }
        if (is_string($lolboostIds)) { $lolboostIds = array_filter(array_map('trim', explode(',', $lolboostIds)), 'strlen'); }
        if (!is_array($externalIds)) { $externalIds = []; }
        if (!is_array($lolboostIds)) { $lolboostIds = []; }

        $externalIds = array_values(array_unique(array_filter(array_map('strval', $externalIds), 'strlen')));
        $lolboostIds = array_values(array_unique(array_filter(array_map('intval', $lolboostIds), fn($v) => $v > 0)));

        if (empty($externalIds) && empty($lolboostIds)) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'external_ids or lolboost_account_ids are required.'], 422, $data, null, null, $startedAt);
        }
        if (count($externalIds) + count($lolboostIds) > 250) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Maximum 250 accounts per bulk remove request.'], 422, $data, null, null, $startedAt);
        }

        $whereParts = [];
        $args = [$seller_id];
        if (!empty($externalIds)) {
            $whereParts[] = 'external_id IN (' . implode(',', array_fill(0, count($externalIds), '?')) . ')';
            foreach ($externalIds as $v) { $args[] = $v; }
        }
        if (!empty($lolboostIds)) {
            $whereParts[] = 'id IN (' . implode(',', array_fill(0, count($lolboostIds), '?')) . ')';
            foreach ($lolboostIds as $v) { $args[] = $v; }
        }
        $whereSql = 'seller_id = ? AND (' . implode(' OR ', $whereParts) . ')';
        $rows = $db->run("SELECT * FROM selling_accounts WHERE {$whereSql}", ...$args) ?: [];

        $archived = [];
        $alreadySold = [];
        $notFoundExternalIds = $externalIds;
        $notFoundLolboostIds = $lolboostIds;

        foreach ($rows as $row) {
            $rid = (int)($row['id'] ?? 0);
            $rexternal = (string)($row['external_id'] ?? '');
            $notFoundExternalIds = array_values(array_diff($notFoundExternalIds, [$rexternal]));
            $notFoundLolboostIds = array_values(array_diff($notFoundLolboostIds, [$rid]));

            if ((int)($row['sold'] ?? 0) === 1) {
                $alreadySold[] = seller_api_public_account($row);
                continue;
            }

            db_update_row('selling_accounts', ['id' => $rid, 'seller_id' => $seller_id], ['active' => 0, 'api_updated_at' => date('Y-m-d H:i:s')]);
            $updated = seller_api_find_account($rid, $seller_id) ?: $row;
            $archived[] = seller_api_public_account($updated);
            seller_api_dispatch_webhook('account.archived', $updated, ['archived_reason' => 'api_bulk_remove']);
        }

        $response = [
            'message' => 'Bulk remove completed.',
            'data' => [
                'archived_count' => count($archived),
                'already_sold_count' => count($alreadySold),
                'not_found_count' => count($notFoundExternalIds) + count($notFoundLolboostIds),
                'archived' => $archived,
                'already_sold' => $alreadySold,
                'not_found' => [
                    'external_ids' => array_values($notFoundExternalIds),
                    'lolboost_account_ids' => array_values($notFoundLolboostIds),
                ],
            ],
        ];
        $first = $archived[0] ?? null;
        seller_api_finish($auth, 'POST', $endpoint, $response, 200, $data, $first['id'] ?? null, $first['external_id'] ?? null, $startedAt);
    }
}


if (!function_exists('seller_api_status_from_item')) {
    function seller_api_status_from_item(array $item): string
    {
        return ((int)($item['active'] ?? 1) === 1) ? 'listed' : 'archived';
    }
}

if (!function_exists('seller_api_public_item')) {
    function seller_api_public_item(array $item): array
    {
        return [
            'id' => (int)($item['id'] ?? 0),
            'external_id' => $item['external_id'] ?? null,
            'game' => $item['game'] ?? null,
            'type' => $item['type'] ?? null,
            'title' => $item['title'] ?? null,
            'slug' => $item['slug'] ?? null,
            'description' => $item['description'] ?? null,
            'delivery_instructions' => $item['delivery_instructions'] ?? null,
            'image_urls' => seller_api_decode_json_field($item['images'] ?? null) ?: [],
            'price_type' => $item['price_type'] ?? 'fixed',
            'price' => isset($item['price']) ? (string)$item['price'] : null,
            'currency' => $item['currency'] ?? 'EUR',
            'server' => $item['server'] ?? null,
            'delivery_type' => $item['delivery_type'] ?? 'manual',
            'requires_friendship_days' => isset($item['requires_friendship_days']) ? (int)$item['requires_friendship_days'] : null,
            'stock' => isset($item['stock']) ? (int)$item['stock'] : null,
            'min_purchase_qty' => isset($item['min_purchase_qty']) ? (int)$item['min_purchase_qty'] : null,
            'max_purchase_qty' => isset($item['max_purchase_qty']) ? (int)$item['max_purchase_qty'] : null,
            'sold_count' => isset($item['sold_count']) ? (int)$item['sold_count'] : 0,
            'status' => seller_api_status_from_item($item),
            'created_at' => !empty($item['created_at']) ? strtotime((string)$item['created_at']) : null,
            'updated_at' => !empty($item['updated_at']) ? strtotime((string)$item['updated_at']) : null,
            'archived_at' => ((int)($item['active'] ?? 1) === 0) ? ($item['api_updated_at'] ?? $item['updated_at'] ?? null) : null,
        ];
    }
}

if (!function_exists('seller_api_find_item')) {
    function seller_api_find_item($idOrExternalId, int $sellerId): ?array
    {
        global $db;
        if (is_numeric($idOrExternalId)) {
            $row = $db->row("SELECT * FROM selling_items WHERE id = ? AND seller_id = ? LIMIT 1", (int)$idOrExternalId, $sellerId);
        } else {
            $row = $db->row("SELECT * FROM selling_items WHERE external_id = ? AND seller_id = ? LIMIT 1", (string)$idOrExternalId, $sellerId);
        }
        return !empty($row) ? $row : null;
    }
}

if (!function_exists('seller_api_create_item')) {
    function seller_api_create_item(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $data = seller_api_read_json();
        $endpoint = '/api/v1/items';
        $external_id = trim((string)($data['external_id'] ?? ''));
        $title = trim((string)($data['title'] ?? ''));
        $price = (int)($data['price'] ?? 0);

        if ($external_id === '' || $title === '' || $price <= 0) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'external_id, title and price are required.'], 422, $data, null, $external_id, $startedAt, 'item');
        }

        $status = in_array((string)($data['status'] ?? 'listed'), ['draft','pending','listed','archived'], true) ? (string)$data['status'] : 'listed';
        $insert = [
            'seller_id' => $seller_id,
            'external_id' => $external_id,
            'api_source' => 1,
            'api_created_at' => date('Y-m-d H:i:s'),
            'api_updated_at' => date('Y-m-d H:i:s'),
            'game' => $data['game'] ?? 'league-of-legends',
            'type' => $data['type'] ?? 'gifting',
            'title' => $title,
            'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title . '-' . substr(sha1($external_id . microtime(true)), 0, 6)), '-')),
            'description' => $data['description'] ?? null,
            'delivery_instructions' => $data['delivery_instructions'] ?? null,
            'images' => !empty($data['image_urls']) ? json_encode($data['image_urls'], JSON_UNESCAPED_SLASHES) : (!empty($data['images']) ? json_encode($data['images'], JSON_UNESCAPED_SLASHES) : null),
            'price_type' => $data['price_type'] ?? 'fixed',
            'price' => $price,
            'currency' => $data['currency'] ?? 'EUR',
            'server' => $data['server'] ?? 'EUW',
            'delivery_type' => 'manual',
            'requires_friendship_days' => (int)($data['requires_friendship_days'] ?? 7),
            'stock' => (int)($data['stock'] ?? 1),
            'min_purchase_qty' => (int)($data['min_purchase_qty'] ?? 1),
            'max_purchase_qty' => isset($data['max_purchase_qty']) ? (int)$data['max_purchase_qty'] : null,
            'active' => $status === 'listed' ? 1 : 0,
            'sold_count' => 0,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $id = db_add_row('selling_items', $insert);
        } catch (Throwable $e) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Could not create item. external_id may already exist.'], 409, $data, null, $external_id, $startedAt, 'item');
        }

        $item = $db->row("SELECT * FROM selling_items WHERE id = ? AND seller_id = ? LIMIT 1", (int)$id, $seller_id);
        if (!empty($item)) {
            seller_api_dispatch_item_webhook('item.created', $item);
            if ((int)($item['active'] ?? 0) === 1) { seller_api_dispatch_item_webhook('item.listed', $item); }
        }
        seller_api_finish($auth, 'POST', $endpoint, ['data' => seller_api_public_item($item)], 201, $data, null, $external_id, $startedAt, 'item', (int)$id);
    }
}

if (!function_exists('seller_api_list_items')) {
    function seller_api_list_items(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $filter = $_GET['filter'] ?? [];
        if (!is_array($filter)) { $filter = []; }
        foreach (['search','id','external_id','title','game','type','server','status'] as $flat) {
            if (isset($_GET[$flat]) && !isset($filter[$flat])) { $filter[$flat] = $_GET[$flat]; }
        }
        $where = ['seller_id = ?'];
        $args = [$seller_id];
        if (!empty($filter['search'])) {
            $search = '%' . (string)$filter['search'] . '%';
            $where[] = '(title LIKE ? OR description LIKE ? OR external_id LIKE ? OR CAST(id AS CHAR) = ?)';
            array_push($args, $search, $search, $search, (string)$filter['search']);
        }
        foreach (['id','external_id','title','game','type','server'] as $field) {
            if (!empty($filter[$field])) {
                $values = array_values(array_filter(array_map('trim', explode(',', (string)$filter[$field])), 'strlen'));
                if (!empty($values)) {
                    $where[] = $field . ' IN (' . implode(',', array_fill(0, count($values), '?')) . ')';
                    foreach ($values as $v) { $args[] = $v; }
                }
            }
        }
        if (!empty($filter['status'])) {
            $statuses = array_values(array_filter(array_map('trim', explode(',', (string)$filter['status'])), 'strlen'));
            $parts = [];
            foreach ($statuses as $s) {
                if ($s === 'listed') { $parts[] = '(active = 1)'; }
                if (in_array($s, ['draft','pending','archived','unlisted'], true)) { $parts[] = '(active = 0)'; }
            }
            if (!empty($parts)) { $where[] = '(' . implode(' OR ', $parts) . ')'; }
        }
        $sortMap = ['id'=>'id','price'=>'price','stock'=>'stock','sold_count'=>'sold_count','created_at'=>'created_at','updated_at'=>'updated_at'];
        $sort = (string)($_GET['sort'] ?? '-updated_at');
        $dir = str_starts_with($sort, '-') ? 'DESC' : 'ASC';
        $orderColumn = $sortMap[ltrim($sort, '-')] ?? 'id';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 15)));
        $offset = ($page - 1) * $perPage;
        $whereSql = implode(' AND ', $where);
        $total = (int)($db->cell("SELECT COUNT(*) FROM selling_items WHERE {$whereSql}", ...$args) ?? 0);
        $rows = $db->run("SELECT * FROM selling_items WHERE {$whereSql} ORDER BY {$orderColumn} {$dir}, id DESC LIMIT {$perPage} OFFSET {$offset}", ...$args) ?: [];
        $lastPage = max(1, (int)ceil($total / $perPage));
        $response = [
            'data' => array_map('seller_api_public_item', $rows),
            'meta' => ['current_page'=>$page,'from'=>$total ? $offset + 1 : null,'last_page'=>$lastPage,'per_page'=>$perPage,'to'=>$total ? min($offset + $perPage, $total) : null,'total'=>$total],
            'links' => ['first'=>'?page=1','last'=>'?page='.$lastPage,'prev'=>$page > 1 ? '?page='.($page - 1) : null,'next'=>$page < $lastPage ? '?page='.($page + 1) : null],
        ];
        seller_api_finish($auth, 'GET', '/api/v1/items', $response, 200, $_GET, null, null, $startedAt, 'item');
    }
}

if (!function_exists('seller_api_get_item')) {
    function seller_api_get_item($id): void
    {
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $row = seller_api_find_item($id, (int)$auth['seller_id']);
        if (empty($row)) { seller_api_finish($auth, 'GET', '/api/v1/items/' . $id, ['message' => 'Item not found.'], 404, null, null, is_numeric($id) ? null : (string)$id, $startedAt, 'item'); }
        seller_api_finish($auth, 'GET', '/api/v1/items/' . $id, ['data' => seller_api_public_item($row)], 200, null, null, $row['external_id'] ?? null, $startedAt, 'item', (int)$row['id']);
    }
}

if (!function_exists('seller_api_update_item')) {
    function seller_api_update_item($id): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $row = seller_api_find_item($id, $seller_id);
        $data = seller_api_read_json();
        if (empty($row)) { seller_api_finish($auth, 'PATCH', '/api/v1/items/' . $id, ['message' => 'Item not found.'], 404, $data, null, is_numeric($id) ? null : (string)$id, $startedAt, 'item'); }
        $allowed = ['game','type','title','description','delivery_instructions','price_type','price','currency','server','requires_friendship_days','stock','min_purchase_qty','max_purchase_qty','sort_order'];
        $update = [];
        foreach ($allowed as $field) { if (array_key_exists($field, $data)) { $update[$field] = $data[$field]; } }
        if (array_key_exists('status', $data)) { $update['active'] = ((string)$data['status'] === 'listed') ? 1 : 0; }
        if (array_key_exists('image_urls', $data)) { $update['images'] = json_encode($data['image_urls'], JSON_UNESCAPED_SLASHES); }
        if (array_key_exists('images', $data)) { $update['images'] = json_encode($data['images'], JSON_UNESCAPED_SLASHES); }
        $update['api_updated_at'] = date('Y-m-d H:i:s');
        if (empty($update)) { seller_api_finish($auth, 'PATCH', '/api/v1/items/' . $id, ['message' => 'No valid fields sent.'], 422, $data, null, $row['external_id'] ?? null, $startedAt, 'item', (int)$row['id']); }
        db_update_row('selling_items', ['id' => (int)$row['id'], 'seller_id' => $seller_id], $update);
        $updated = $db->row("SELECT * FROM selling_items WHERE id = ? AND seller_id = ? LIMIT 1", (int)$row['id'], $seller_id);
        seller_api_dispatch_item_webhook('item.updated', $updated);
        seller_api_finish($auth, 'PATCH', '/api/v1/items/' . $id, ['data' => seller_api_public_item($updated)], 200, $data, null, $updated['external_id'] ?? null, $startedAt, 'item', (int)$updated['id']);
    }
}

if (!function_exists('seller_api_delete_item')) {
    function seller_api_delete_item($id): void
    {
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $row = seller_api_find_item($id, $seller_id);
        if (empty($row)) { seller_api_finish($auth, 'DELETE', '/api/v1/items/' . $id, ['message' => 'Item not found.'], 404, null, null, is_numeric($id) ? null : (string)$id, $startedAt, 'item'); }
        db_update_row('selling_items', ['id' => (int)$row['id'], 'seller_id' => $seller_id], ['active' => 0, 'api_updated_at' => date('Y-m-d H:i:s')]);
        $updated = seller_api_find_item($row['id'], $seller_id);
        if (!empty($updated)) { seller_api_dispatch_item_webhook('item.archived', $updated, ['archived_reason' => 'api_delete']); }
        seller_api_finish($auth, 'DELETE', '/api/v1/items/' . $id, ['message' => 'Item archived.', 'data' => seller_api_public_item($updated ?: $row)], 200, null, null, $row['external_id'] ?? null, $startedAt, 'item', (int)$row['id']);
    }
}

if (!function_exists('seller_api_bulk_remove_items')) {
    function seller_api_bulk_remove_items(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $data = seller_api_read_json();
        $endpoint = '/api/v1/items/bulk-remove';
        $externalIds = $data['external_ids'] ?? $data['external_id'] ?? [];
        $lolboostIds = $data['lolboost_item_ids'] ?? $data['ids'] ?? [];
        if (is_string($externalIds)) { $externalIds = array_filter(array_map('trim', explode(',', $externalIds)), 'strlen'); }
        if (is_string($lolboostIds)) { $lolboostIds = array_filter(array_map('trim', explode(',', $lolboostIds)), 'strlen'); }
        if (!is_array($externalIds)) { $externalIds = []; }
        if (!is_array($lolboostIds)) { $lolboostIds = []; }
        $externalIds = array_values(array_unique(array_filter(array_map('strval', $externalIds), 'strlen')));
        $lolboostIds = array_values(array_unique(array_filter(array_map('intval', $lolboostIds), fn($v) => $v > 0)));
        if (empty($externalIds) && empty($lolboostIds)) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'external_ids or lolboost_item_ids are required.'], 422, $data, null, null, $startedAt, 'item');
        }
        if (count($externalIds) + count($lolboostIds) > 250) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Maximum 250 items per bulk remove request.'], 422, $data, null, null, $startedAt, 'item');
        }
        $whereParts = [];
        $args = [$seller_id];
        if (!empty($externalIds)) { $whereParts[] = 'external_id IN (' . implode(',', array_fill(0, count($externalIds), '?')) . ')'; foreach ($externalIds as $v) { $args[] = $v; } }
        if (!empty($lolboostIds)) { $whereParts[] = 'id IN (' . implode(',', array_fill(0, count($lolboostIds), '?')) . ')'; foreach ($lolboostIds as $v) { $args[] = $v; } }
        $whereSql = 'seller_id = ? AND (' . implode(' OR ', $whereParts) . ')';
        $rows = $db->run("SELECT * FROM selling_items WHERE {$whereSql}", ...$args) ?: [];
        $archived = [];
        $notFoundExternalIds = $externalIds;
        $notFoundLolboostIds = $lolboostIds;
        foreach ($rows as $row) {
            $rid = (int)($row['id'] ?? 0);
            $rexternal = (string)($row['external_id'] ?? '');
            $notFoundExternalIds = array_values(array_diff($notFoundExternalIds, [$rexternal]));
            $notFoundLolboostIds = array_values(array_diff($notFoundLolboostIds, [$rid]));
            db_update_row('selling_items', ['id' => $rid, 'seller_id' => $seller_id], ['active' => 0, 'api_updated_at' => date('Y-m-d H:i:s')]);
            $updated = seller_api_find_item($rid, $seller_id) ?: $row;
            $archived[] = seller_api_public_item($updated);
            seller_api_dispatch_item_webhook('item.archived', $updated, ['archived_reason' => 'api_bulk_remove']);
        }
        $response = ['message'=>'Bulk remove completed.','data'=>['archived_count'=>count($archived),'not_found_count'=>count($notFoundExternalIds)+count($notFoundLolboostIds),'archived'=>$archived,'not_found'=>['external_ids'=>array_values($notFoundExternalIds),'lolboost_item_ids'=>array_values($notFoundLolboostIds)]]];
        $first = $archived[0] ?? null;
        seller_api_finish($auth, 'POST', $endpoint, $response, 200, $data, null, $first['external_id'] ?? null, $startedAt, 'item', $first['id'] ?? null);
    }
}

if (!function_exists('seller_api_bulk_update_items')) {
    function seller_api_bulk_update_items(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $data = seller_api_read_json();
        $endpoint = '/api/v1/items/bulk-update';
        $items = $data['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'items array is required.'], 422, $data, null, null, $startedAt, 'item');
        }
        if (count($items) > 100) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Maximum 100 items per bulk update request.'], 422, $data, null, null, $startedAt, 'item');
        }
        $updatedItems = [];
        $notFound = [];
        foreach ($items as $payload) {
            if (!is_array($payload)) { continue; }
            $id = $payload['external_id'] ?? $payload['lolboost_item_id'] ?? $payload['id'] ?? null;
            if ($id === null || $id === '') { continue; }
            $row = seller_api_find_item($id, $seller_id);
            if (empty($row)) { $notFound[] = (string)$id; continue; }
            $update = [];
            foreach (['price','stock','status','title','description','server','type','sort_order'] as $field) {
                if (array_key_exists($field, $payload)) {
                    if ($field === 'status') { $update['active'] = ((string)$payload[$field] === 'listed') ? 1 : 0; }
                    else { $update[$field] = $payload[$field]; }
                }
            }
            if (empty($update)) { continue; }
            $update['api_updated_at'] = date('Y-m-d H:i:s');
            db_update_row('selling_items', ['id'=>(int)$row['id'], 'seller_id'=>$seller_id], $update);
            $updated = seller_api_find_item($row['id'], $seller_id) ?: $row;
            $updatedItems[] = seller_api_public_item($updated);
            seller_api_dispatch_item_webhook('item.updated', $updated);
        }
        $response = ['message'=>'Bulk update completed.','data'=>['updated_count'=>count($updatedItems),'not_found_count'=>count($notFound),'updated'=>$updatedItems,'not_found'=>$notFound]];
        $first = $updatedItems[0] ?? null;
        seller_api_finish($auth, 'POST', $endpoint, $response, 200, $data, null, $first['external_id'] ?? null, $startedAt, 'item', $first['id'] ?? null);
    }
}

if (!function_exists('seller_api_dispatch_item_webhook')) {
    function seller_api_dispatch_item_webhook(string $event, array $item, array $extra = []): void
    {
        global $db;
        seller_api_ensure_tables();
        $seller_id = (int)($item['seller_id'] ?? 0);
        if ($seller_id <= 0) { return; }
        $hooks = $db->run("SELECT * FROM seller_webhooks WHERE seller_id = ? AND is_active = 1", $seller_id) ?: [];
        if (empty($hooks)) { return; }
        $payload = array_merge([
            'event' => $event,
            'item' => seller_api_public_item($item),
            'external_id' => $item['external_id'] ?? null,
            'lolboost_item_id' => (int)($item['id'] ?? 0),
            'quantity' => isset($extra['quantity']) ? (int)$extra['quantity'] : null,
            'sold_at' => ($event === 'item.sold') ? ($extra['sold_at'] ?? date('Y-m-d H:i:s')) : null,
            'created_at' => $item['created_at'] ?? null,
            'updated_at' => $item['updated_at'] ?? $item['api_updated_at'] ?? null,
            'archived_at' => ($event === 'item.archived') ? date('Y-m-d H:i:s') : null,
        ], $extra);
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        foreach ($hooks as $hook) {
            $events = array_filter(array_map('trim', explode(',', (string)$hook['events'])));
            if (!in_array($event, $events, true) && !in_array('*', $events, true)) { continue; }
            $signature = hash_hmac('sha256', $body, (string)$hook['secret']);
            $code = 0; $response = ''; $success = 0;
            try {
                $ch = curl_init((string)$hook['url']);
                curl_setopt_array($ch, [CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Lolboost-Event: '.$event,'X-Lolboost-Signature: sha256='.$signature],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>8]);
                $response = (string)curl_exec($ch);
                $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $success = ($code >= 200 && $code < 300) ? 1 : 0;
            } catch (Throwable $e) { $response = $e->getMessage(); }
            db_add_row('seller_webhook_logs', ['seller_id'=>$seller_id,'webhook_id'=>(int)$hook['id'],'entity_type'=>'item','account_id'=>null,'item_id'=>(int)($item['id'] ?? 0),'event'=>$event,'payload'=>$body,'response_code'=>$code,'response_body'=>substr($response,0,2000),'success'=>$success]);
        }
    }
}

if (!function_exists('seller_api_dispatch_webhook')) {
    function seller_api_dispatch_webhook(string $event, array $account, array $extra = []): void
    {
        global $db;
        seller_api_ensure_tables();
        $seller_id = (int)($account['seller_id'] ?? 0);
        if ($seller_id <= 0) { return; }

        $hooks = $db->run("SELECT * FROM seller_webhooks WHERE seller_id = ? AND is_active = 1", $seller_id) ?: [];
        if (empty($hooks)) { return; }

        $payload = array_merge([
            'event' => $event,
            'account' => seller_api_public_account($account),
            'external_id' => $account['external_id'] ?? null,
            'lolboost_account_id' => (int)($account['id'] ?? 0),
            'sold_at' => ($event === 'account.sold') ? ($account['sold_at'] ?? date('Y-m-d H:i:s')) : ($account['sold_at'] ?? null),
            'created_at' => $account['created_at'] ?? null,
            'updated_at' => $account['updated_at'] ?? $account['api_updated_at'] ?? null,
            'archived_at' => ($event === 'account.archived') ? date('Y-m-d H:i:s') : null,
        ], $extra);
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        foreach ($hooks as $hook) {
            $events = array_filter(array_map('trim', explode(',', (string)$hook['events'])));
            if (!in_array($event, $events, true) && !in_array('*', $events, true)) { continue; }
            $signature = hash_hmac('sha256', $body, (string)$hook['secret']);
            $code = 0;
            $response = '';
            $success = 0;
            try {
                $ch = curl_init((string)$hook['url']);
                curl_setopt_array($ch, [
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'X-Lolboost-Event: ' . $event,
                        'X-Lolboost-Signature: sha256=' . $signature,
                    ],
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_TIMEOUT => 8,
                ]);
                $response = (string)curl_exec($ch);
                $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $success = ($code >= 200 && $code < 300) ? 1 : 0;
            } catch (Throwable $e) {
                $response = $e->getMessage();
            }
            db_add_row('seller_webhook_logs', [
                'seller_id' => $seller_id,
                'webhook_id' => (int)$hook['id'],
                'entity_type' => 'account',
                'account_id' => (int)($account['id'] ?? 0),
                'item_id' => null,
                'event' => $event,
                'payload' => $body,
                'response_code' => $code,
                'response_body' => substr($response, 0, 2000),
                'success' => $success,
            ]);
        }
    }
}


/* Restored from live: seller gallery and account mapping helpers. */
    function seller_api_account_division_id($division): ?int
    {
        if ($division === null || $division === '') { return null; }
        $key = strtoupper(trim((string)$division));
        $map = ['IV' => 1, 'III' => 2, 'II' => 3, 'I' => 4, '4' => 1, '3' => 2, '2' => 3, '1' => 4];
        return $map[$key] ?? null;
    }

    function seller_api_account_int_or_null($value): ?int
    {
        if ($value === null || $value === '') { return null; }
        return (int)$value;
    }

    function seller_api_account_mapped_fields(array $data): array
    {
        $parameters = [];
        if (!empty($data['parameters']) && is_array($data['parameters'])) { $parameters = $data['parameters']; }
        if (!empty($data['game_data']) && is_array($data['game_data'])) { $parameters = array_merge($parameters, $data['game_data']); }

        $rankValue = seller_api_account_pick($data, $parameters, ['current_rank', 'rank', 'solo_rank']);
        [$parsedRankId, $parsedDivisionId] = seller_api_account_split_rank_division($rankValue);
        $rankId = $parsedRankId;
        $divisionId = seller_api_account_division_id(seller_api_account_pick($data, $parameters, ['current_division', 'division', 'solo_division']));
        if ($divisionId === null) { $divisionId = $parsedDivisionId; }
        $rankLabel = seller_api_account_pick($data, $parameters, ['rank_label'], null);
        if (!$rankLabel && $rankValue !== null) {
            $rankLabel = trim((string)$rankValue . ($divisionId !== null && !preg_match('/\b(IV|III|II|I|4|3|2|1)\b/i', (string)$rankValue) ? ' ' . (string)seller_api_account_pick($data, $parameters, ['division'], '') : ''));
        }

        if ($rankId !== null) { $parameters['rank_id'] = $rankId; }
        if ($divisionId !== null) {
            $parameters['division_id'] = $divisionId;
            $parameters['division'] = $divisionId;
        }
        if ($rankLabel !== null) { $parameters['rank_label'] = $rankLabel; }

        $flexRankValue = seller_api_account_pick($data, $parameters, ['flex_rank']);
        [$flexRankId, $parsedFlexDivisionId] = seller_api_account_split_rank_division($flexRankValue);
        $flexDivisionId = seller_api_account_division_id(seller_api_account_pick($data, $parameters, ['flex_division']));
        if ($flexDivisionId === null) { $flexDivisionId = $parsedFlexDivisionId; }
        $previousRankValue = seller_api_account_pick($data, $parameters, ['previous_rank', 'prev_rank']);
        [$previousRankId, $parsedPreviousDivisionId] = seller_api_account_split_rank_division($previousRankValue);
        $previousDivisionId = seller_api_account_division_id(seller_api_account_pick($data, $parameters, ['previous_division', 'prev_division']));
        if ($previousDivisionId === null) { $previousDivisionId = $parsedPreviousDivisionId; }
        if ($flexRankId !== null) { $parameters['flex_rank'] = $flexRankId; }
        if ($flexDivisionId !== null) { $parameters['flex_division'] = $flexDivisionId; }
        if ($previousRankId !== null) { $parameters['previous_rank'] = $previousRankId; $parameters['prev_rank'] = $previousRankId; }
        if ($previousDivisionId !== null) { $parameters['previous_division'] = $previousDivisionId; $parameters['prev_division'] = $previousDivisionId; }

        $currentLp = seller_api_account_int_or_null(seller_api_account_pick($data, $parameters, ['current_lp', 'lp']));
        $flexLp = seller_api_account_int_or_null(seller_api_account_pick($data, $parameters, ['flex_lp']));
        $previousLp = seller_api_account_int_or_null(seller_api_account_pick($data, $parameters, ['previous_lp', 'prev_lp']));

        $gameKey = strtolower((string)($data['game'] ?? 'lol'));
        $normalizeLolRankParts = static function (?int $rank, ?int &$division, ?int &$lp): void {
            if ($rank === null) { return; }
            if ($rank >= 8) {
                $division = null;
            } else {
                $lp = null;
                if ($rank <= 0) { $division = null; }
            }
        };

        if (in_array($gameKey, ['lol', 'tft'], true)) {
            $normalizeLolRankParts($rankId, $divisionId, $currentLp);
            $normalizeLolRankParts($flexRankId, $flexDivisionId, $flexLp);
            $normalizeLolRankParts($previousRankId, $previousDivisionId, $previousLp);

            unset($parameters['lp'], $parameters['flex_lp'], $parameters['previous_lp'], $parameters['prev_lp']);

            if ($rankId !== null && ($rankId >= 8 || $rankId <= 0)) {
                unset($parameters['division_id'], $parameters['division']);
            } elseif ($divisionId !== null) {
                $parameters['division_id'] = $divisionId;
                $parameters['division'] = $divisionId;
            }

            if ($flexRankId !== null && ($flexRankId >= 8 || $flexRankId <= 0)) {
                unset($parameters['flex_division']);
            } elseif ($flexDivisionId !== null) {
                $parameters['flex_division'] = $flexDivisionId;
            }

            if ($previousRankId !== null && ($previousRankId >= 8 || $previousRankId <= 0)) {
                unset($parameters['previous_division'], $parameters['prev_division']);
            } elseif ($previousDivisionId !== null) {
                $parameters['previous_division'] = $previousDivisionId;
                $parameters['prev_division'] = $previousDivisionId;
            }

            if ($currentLp !== null) { $parameters['lp'] = $currentLp; }
            if ($flexLp !== null) { $parameters['flex_lp'] = $flexLp; }
            if ($previousLp !== null) { $parameters['previous_lp'] = $previousLp; }
        }

        $fields = [
            'game_data' => !empty($parameters) ? json_encode($parameters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'rank' => $rankId,
            'rank_label' => $rankLabel,
            'current_rank' => $rankId,
            'current_division' => $divisionId,
            'current_lp' => $currentLp,
            'flex_rank' => $flexRankId,
            'flex_division' => $flexDivisionId,
            'flex_lp' => $flexLp,
            'previous_rank' => $previousRankId,
            'previous_division' => $previousDivisionId,
            'previous_lp' => $previousLp,
            'level' => seller_api_account_pick($data, $parameters, ['level']),
            'blue_essence' => seller_api_account_pick($data, $parameters, ['blue_essence', 'blueEssence', 'be']),
            'riot_points' => seller_api_account_pick($data, $parameters, ['riot_points', 'riotPoints', 'rp']),
            'winrate_percent' => seller_api_account_pick($data, $parameters, ['winrate_percent', 'winrate', 'win_rate']),
            'champion_count' => seller_api_account_pick($data, $parameters, ['champion_count', 'champions_count']),
            'skin_count' => seller_api_account_pick($data, $parameters, ['skin_count', 'skins_count']),
            'champions' => is_array(seller_api_account_pick($data, $parameters, ['champions'])) ? implode('|', seller_api_account_pick($data, $parameters, ['champions'])) : seller_api_account_pick($data, $parameters, ['champions']),
            'skins' => is_array(seller_api_account_pick($data, $parameters, ['skins'])) ? implode('|', seller_api_account_pick($data, $parameters, ['skins'])) : seller_api_account_pick($data, $parameters, ['skins']),
            'roles' => is_array(seller_api_account_pick($data, $parameters, ['roles'])) ? implode('|', seller_api_account_pick($data, $parameters, ['roles'])) : seller_api_account_pick($data, $parameters, ['roles']),
            'in_game_name' => seller_api_account_pick($data, $parameters, ['in_game_name', 'ign', 'summoner_name']),
        ];

        return array_filter($fields, static function ($value): bool { return $value !== null; });
    }

    function seller_api_account_pick(array $data, array $parameters, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== '') { return $data[$key]; }
        }
        foreach ($keys as $key) {
            if (array_key_exists($key, $parameters) && $parameters[$key] !== '') { return $parameters[$key]; }
        }
        return $default;
    }

    function seller_api_account_price_cents($price): int
    {
        if (is_string($price) && strpos($price, '.') !== false) { return (int)round(((float)$price) * 100); }
        if (is_float($price)) { return (int)round($price * 100); }
        return (int)$price;
    }

    function seller_api_account_rank_id($rank): ?int
    {
        if ($rank === null || $rank === '') { return null; }
        if (is_numeric($rank)) { return (int)$rank; }
        $value = strtolower(trim((string)$rank));
        $value = preg_replace('/\s+/', ' ', str_replace(['-', '_'], ' ', $value));
        $map = [
            'unranked' => 0,
            'iron' => 1,
            'bronze' => 2,
            'silver' => 3,
            'gold' => 4,
            'platinum' => 5,
            'plat' => 5,
            'emerald' => 6,
            'diamond' => 7,
            'master' => 8,
            'grandmaster' => 9,
            'challenger' => 10,
        ];
        $firstToken = strtok($value, ' ');
        if ($firstToken !== false && isset($map[$firstToken])) { return $map[$firstToken]; }
        $compact = str_replace(' ', '', $value);
        foreach ($map as $name => $id) {
            if ($compact === $name || str_starts_with($compact, $name)) { return $id; }
        }
        return null;
    }

    function seller_api_account_split_rank_division($value): array
    {
        if ($value === null || is_array($value)) { return [null, null]; }
        $text = trim((string)$value);
        if ($text === '') { return [null, null]; }
        $rankId = seller_api_account_rank_id($text);
        $division = null;
        if (preg_match('/\b(IV|III|II|I|4|3|2|1)\b/i', $text, $m)) {
            $division = seller_api_account_division_id($m[1]);
        } elseif (preg_match('/(?:iron|bronze|silver|gold|platinum|plat|emerald|diamond)\s*(iv|iii|ii|i|4|3|2|1)$/i', str_replace(['-', '_'], ' ', $text), $m)) {
            $division = seller_api_account_division_id($m[1]);
        }
        return [$rankId, $division];
    }

    function seller_api_delete_image($id): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $sellerId = (int)$auth['seller_id'];
        $row = $db->row("SELECT * FROM seller_gallery_images WHERE id = ? AND seller_id = ? LIMIT 1", (int)$id, $sellerId);
        if (empty($row)) { seller_api_finish($auth, 'DELETE', '/api/v1/images/' . $id, ['message' => 'Image not found.'], 404, null, null, null, $startedAt, 'image'); }
        if (!empty($row['storage_path']) && is_file((string)$row['storage_path'])) { @unlink((string)$row['storage_path']); }
        $db->run("DELETE FROM seller_gallery_images WHERE id = ? AND seller_id = ?", (int)$id, $sellerId);
        seller_api_finish($auth, 'DELETE', '/api/v1/images/' . $id, ['success' => true, 'deleted_id' => (int)$id], 200, null, null, null, $startedAt, 'image');
    }

    function seller_api_extract_image_ids(array $data): array
    {
        $ids = [];
        $appendId = static function ($value) use (&$ids): void {
            if (is_array($value)) {
                if (isset($value['id'])) { $ids[] = $value['id']; return; }
                if (isset($value['image_id'])) { $ids[] = $value['image_id']; return; }
                foreach ($value as $nested) {
                    if (is_array($nested) && (isset($nested['id']) || isset($nested['image_id']))) {
                        $ids[] = $nested['id'] ?? $nested['image_id'];
                    } elseif (!is_array($nested)) {
                        $ids[] = $nested;
                    }
                }
                return;
            }
            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $decodedValue) { $ids[] = is_array($decodedValue) ? ($decodedValue['id'] ?? ($decodedValue['image_id'] ?? 0)) : $decodedValue; }
                    return;
                }
                foreach (preg_split('/\s*,\s*/', $value) ?: [] as $part) { $ids[] = $part; }
                return;
            }
            $ids[] = $value;
        };
        foreach (['image_id', 'image_ids', 'gallery_image_id', 'gallery_image_ids', 'gallery_images', 'images'] as $key) {
            if (!array_key_exists($key, $data)) { continue; }
            $appendId($data[$key]);
        }
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn($v) => $v > 0)));
    }

    function seller_api_list_images(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $sellerId = (int)$auth['seller_id'];
        $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 30)));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $rows = $db->run("SELECT * FROM seller_gallery_images WHERE seller_id = ? ORDER BY id DESC LIMIT $perPage OFFSET $offset", $sellerId) ?: [];
        $total = (int)($db->cell("SELECT COUNT(*) FROM seller_gallery_images WHERE seller_id = ?", $sellerId) ?? 0);
        seller_api_finish($auth, 'GET', '/api/v1/images', ['data'=>array_map('seller_api_public_gallery_image', $rows),'meta'=>['current_page'=>$page,'per_page'=>$perPage,'total'=>$total,'last_page'=>(int)ceil(max(1, $total) / $perPage)]], 200, null, null, null, $startedAt, 'image');
    }

    function seller_api_public_gallery_image(array $row): array
    {
        return ['id'=>(int)($row['id'] ?? 0),'image_id'=>(int)($row['id'] ?? 0),'url'=>$row['url'] ?? null,'filename'=>$row['filename'] ?? null,'original_name'=>$row['original_name'] ?? null,'mime_type'=>$row['mime_type'] ?? null,'size_bytes'=>isset($row['size_bytes']) ? (int)$row['size_bytes'] : null,'created_at'=>!empty($row['created_at']) ? strtotime((string)$row['created_at']) : null];
    }

    function seller_api_upload_image(): void
    {
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $sellerId = (int)$auth['seller_id'];
        $endpoint = '/api/v1/images';
        try {
            if (!empty($_FILES['file']) && is_array($_FILES['file'])) {
                $image = seller_gallery_upload_file($_FILES['file'], $sellerId);
                seller_api_finish($auth, 'POST', $endpoint, ['data' => $image], 201, ['multipart' => true], null, null, $startedAt, 'image');
            }
            if (!empty($_FILES['image']) && is_array($_FILES['image'])) {
                $image = seller_gallery_upload_file($_FILES['image'], $sellerId);
                seller_api_finish($auth, 'POST', $endpoint, ['data' => $image], 201, ['multipart' => true], null, null, $startedAt, 'image');
            }
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Upload field must be named file or image.'], 422, ['multipart' => true], null, null, $startedAt, 'image');
        } catch (Throwable $e) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => $e->getMessage()], 422, ['multipart' => true], null, null, $startedAt, 'image');
        }
    }

    function seller_gallery_images_by_ids(int $sellerId, array $ids): array
    {
        global $db;
        seller_api_ensure_tables();
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
        if (empty($ids)) { return []; }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $db->run("SELECT id, url, original_name, size_bytes FROM seller_gallery_images WHERE seller_id = ? AND id IN ($placeholders) ORDER BY FIELD(id, $placeholders)", ...array_merge([$sellerId], $ids, $ids)) ?: [];
        return is_array($rows) ? $rows : [];
    }

    function seller_gallery_public_url(string $filename): string
    {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
        if ($base === '') { $base = rtrim(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''), '/'); }
        return $base . '/public/assets/seller-gallery/' . rawurlencode($filename);
    }

    function seller_gallery_upload_dir(): string
    {
        $candidates = [];
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/') . '/public/assets/seller-gallery';
        }
        $candidates[] = dirname(__DIR__) . '/public/assets/seller-gallery';
        $candidates[] = dirname(__DIR__, 2) . '/public/assets/seller-gallery';
        foreach ($candidates as $dir) {
            if (is_dir($dir) || @mkdir($dir, 0775, true)) { return $dir; }
        }
        return $candidates[0];
    }

    function seller_gallery_upload_file(array $file, int $sellerId): array
    {
        global $db;
        seller_api_ensure_tables();
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { throw new RuntimeException('Image upload failed.'); }
        if ((int)($file['size'] ?? 0) <= 0 || (int)$file['size'] > 8 * 1024 * 1024) { throw new RuntimeException('Image must be smaller than 8 MB.'); }
        $tmp = (string)($file['tmp_name'] ?? '');
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? (string)finfo_file($finfo, $tmp) : '';
            if ($finfo) { finfo_close($finfo); }
        }
        if ($mime === '') { $mime = (string)($file['type'] ?? ''); }
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
        if (!isset($allowed[$mime])) { throw new RuntimeException('Only JPG, PNG, WEBP and GIF images are allowed.'); }
        $dir = seller_gallery_upload_dir();
        $filename = 'seller_' . $sellerId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $target = rtrim($dir, '/') . '/' . $filename;
        if (!move_uploaded_file($tmp, $target)) {
            if (!@rename($tmp, $target)) { throw new RuntimeException('Could not save image.'); }
        }
        @chmod($target, 0644);
        $url = seller_gallery_public_url($filename);
        $id = db_add_row('seller_gallery_images', [
            'seller_id' => $sellerId,
            'filename' => $filename,
            'original_name' => substr((string)($file['name'] ?? ''), 0, 255),
            'mime_type' => $mime,
            'size_bytes' => (int)($file['size'] ?? 0),
            'url' => $url,
            'storage_path' => $target,
        ]);
        return ['id'=>(int)$id,'image_id'=>(int)$id,'url'=>$url,'filename'=>$filename,'original_name'=>(string)($file['name'] ?? ''),'mime_type'=>$mime,'size_bytes'=>(int)($file['size'] ?? 0)];
    }

    function seller_gallery_urls_from_ids(int $sellerId, array $ids): array
    {
        $rows = seller_gallery_images_by_ids($sellerId, $ids);
        return array_values(array_filter(array_map(fn($r) => $r['url'] ?? null, $rows)));
    }

/**
 * ============================================================
 *  Seller Top-Ups API  (mirrors the Items API)
 *  Table: selling_topups
 * ============================================================
 */

if (!function_exists('seller_api_status_from_topup')) {
    function seller_api_status_from_topup(array $t): string
    {
        return ((int)($t['active'] ?? 1) === 1) ? 'listed' : 'archived';
    }
}

if (!function_exists('seller_api_topup_waiting_minutes')) {
    function seller_api_topup_waiting_minutes(int $value, string $unit): int
    {
        $unit = strtolower(trim($unit));
        $factor = $unit === 'hours' ? 60 : ($unit === 'days' ? 1440 : 1);
        return max(0, $value * $factor);
    }
}

if (!function_exists('seller_api_public_topup')) {
    function seller_api_public_topup(array $t): array
    {
        return [
            'id' => (int)($t['id'] ?? 0),
            'external_id' => $t['external_id'] ?? null,
            'game' => $t['game_slug'] ?? null,
            'game_name' => $t['game_name'] ?? null,
            'service_label' => $t['service_label'] ?? null,
            'offer_key' => $t['offer_key'] ?? null,
            'title' => $t['offer_title'] ?? null,
            'amount' => isset($t['offer_amount']) ? (float)$t['offer_amount'] : null,
            'unit' => $t['offer_unit'] ?? null,
            'region' => $t['region'] ?? null,
            'platform' => $t['platform'] ?? null,
            'price' => isset($t['price']) ? (string)$t['price'] : null,
            'currency' => $t['currency'] ?? 'EUR',
            'stock' => isset($t['stock']) ? (int)$t['stock'] : null,
            'min_quantity' => isset($t['min_quantity']) ? (int)$t['min_quantity'] : null,
            'waiting_time_value' => isset($t['waiting_time_value']) ? (int)$t['waiting_time_value'] : null,
            'waiting_time_unit' => $t['waiting_time_unit'] ?? 'minutes',
            'waiting_time_minutes' => isset($t['waiting_time_minutes']) ? (int)$t['waiting_time_minutes'] : null,
            'instructions' => $t['instructions'] ?? null,
            'image_url' => $t['image'] ?? null,
            'sold_count' => isset($t['sold_count']) ? (int)$t['sold_count'] : 0,
            'status' => seller_api_status_from_topup($t),
            'created_at' => !empty($t['created_at']) ? strtotime((string)$t['created_at']) : null,
            'updated_at' => !empty($t['updated_at']) ? strtotime((string)$t['updated_at']) : null,
            'archived_at' => ((int)($t['active'] ?? 1) === 0) ? ($t['api_updated_at'] ?? $t['updated_at'] ?? null) : null,
        ];
    }
}

if (!function_exists('seller_api_find_topup')) {
    function seller_api_find_topup($idOrExternalId, int $sellerId): ?array
    {
        global $db;
        if (is_numeric($idOrExternalId)) {
            $row = $db->row("SELECT * FROM selling_topups WHERE id = ? AND seller_id = ? LIMIT 1", (int)$idOrExternalId, $sellerId);
        } else {
            $row = $db->row("SELECT * FROM selling_topups WHERE external_id = ? AND seller_id = ? LIMIT 1", (string)$idOrExternalId, $sellerId);
        }
        return !empty($row) ? $row : null;
    }
}

if (!function_exists('seller_api_create_topup')) {
    function seller_api_create_topup(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        seller_api_ensure_tables();
        $data = seller_api_read_json();
        $endpoint = '/api/v1/top-ups';

        $external_id = trim((string)($data['external_id'] ?? ''));
        $title = trim((string)($data['title'] ?? ($data['offer_title'] ?? '')));
        $price = (int)($data['price'] ?? 0);
        if ($external_id === '' || $title === '' || $price <= 0) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'external_id, title and price are required.'], 422, $data, null, $external_id, $startedAt, 'topup');
        }

        // Resolve game (any game slug allowed)
        $gameSlug = strtolower(trim((string)($data['game'] ?? 'league-of-legends')));
        if ($gameSlug === '') { $gameSlug = 'league-of-legends'; }
        $gameRow = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
        $gameId = (int)($gameRow['id'] ?? 0);
        $gameName = (string)($data['game_name'] ?? ($gameRow['name'] ?? ucwords(str_replace('-', ' ', $gameSlug))));

        $unit = strtolower(trim((string)($data['waiting_time_unit'] ?? 'minutes')));
        if (!in_array($unit, ['minutes','hours','days'], true)) { $unit = 'minutes'; }
        $waitValue = (int)($data['waiting_time_value'] ?? 10);
        $waitMinutes = seller_api_topup_waiting_minutes($waitValue, $unit);

        $status = in_array((string)($data['status'] ?? 'listed'), ['draft','pending','listed','archived'], true) ? (string)$data['status'] : 'listed';

        $insert = [
            'seller_id' => $seller_id,
            'external_id' => $external_id,
            'api_source' => 1,
            'api_created_at' => date('Y-m-d H:i:s'),
            'api_updated_at' => date('Y-m-d H:i:s'),
            'game_id' => $gameId ?: null,
            'game_slug' => $gameSlug,
            'game_name' => $gameName,
            'service_label' => $data['service_label'] ?? null,
            'offer_key' => $data['offer_key'] ?? null,
            'offer_title' => $title,
            'offer_amount' => isset($data['amount']) ? (float)$data['amount'] : (isset($data['offer_amount']) ? (float)$data['offer_amount'] : null),
            'offer_unit' => $data['unit'] ?? ($data['offer_unit'] ?? null),
            'region' => $data['region'] ?? null,
            'platform' => $data['platform'] ?? null,
            'price' => $price,
            'currency' => $data['currency'] ?? 'EUR',
            'stock' => (int)($data['stock'] ?? 999),
            'min_quantity' => (int)($data['min_quantity'] ?? 1),
            'waiting_time_value' => $waitValue,
            'waiting_time_unit' => $unit,
            'waiting_time_minutes' => $waitMinutes,
            'instructions' => $data['instructions'] ?? null,
            'image' => $data['image_url'] ?? ($data['image'] ?? null),
            'active' => $status === 'listed' ? 1 : 0,
            'sold_count' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $id = db_add_row('selling_topups', $insert);
        } catch (Throwable $e) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Could not create top-up. external_id may already exist.'], 409, $data, null, $external_id, $startedAt, 'topup');
        }

        $topup = $db->row("SELECT * FROM selling_topups WHERE id = ? AND seller_id = ? LIMIT 1", (int)$id, $seller_id);
        if (!empty($topup)) {
            seller_api_dispatch_topup_webhook('topup.created', $topup);
            if ((int)($topup['active'] ?? 0) === 1) { seller_api_dispatch_topup_webhook('topup.listed', $topup); }
        }
        seller_api_finish($auth, 'POST', $endpoint, ['data' => seller_api_public_topup($topup)], 201, $data, null, $external_id, $startedAt, 'topup');
    }
}

if (!function_exists('seller_api_list_topups')) {
    function seller_api_list_topups(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        if (function_exists('lb_topups_table_ensure')) { lb_topups_table_ensure(); }
        $filter = $_GET['filter'] ?? [];
        if (!is_array($filter)) { $filter = []; }
        foreach (['search','id','external_id','title','game','region','platform','status'] as $flat) {
            if (isset($_GET[$flat]) && !isset($filter[$flat])) { $filter[$flat] = $_GET[$flat]; }
        }
        $where = ['seller_id = ?'];
        $args = [$seller_id];
        if (!empty($filter['search'])) {
            $search = '%' . (string)$filter['search'] . '%';
            $where[] = '(offer_title LIKE ? OR instructions LIKE ? OR external_id LIKE ? OR CAST(id AS CHAR) = ?)';
            array_push($args, $search, $search, $search, (string)$filter['search']);
        }
        $colMap = ['id'=>'id','external_id'=>'external_id','title'=>'offer_title','game'=>'game_slug','region'=>'region','platform'=>'platform'];
        foreach ($colMap as $field => $col) {
            if (!empty($filter[$field])) {
                $values = array_values(array_filter(array_map('trim', explode(',', (string)$filter[$field])), 'strlen'));
                if (!empty($values)) {
                    $where[] = $col . ' IN (' . implode(',', array_fill(0, count($values), '?')) . ')';
                    foreach ($values as $v) { $args[] = $v; }
                }
            }
        }
        if (!empty($filter['status'])) {
            $statuses = array_values(array_filter(array_map('trim', explode(',', (string)$filter['status'])), 'strlen'));
            $parts = [];
            foreach ($statuses as $s) {
                if ($s === 'listed') { $parts[] = '(active = 1)'; }
                if (in_array($s, ['draft','pending','archived','unlisted'], true)) { $parts[] = '(active = 0)'; }
            }
            if (!empty($parts)) { $where[] = '(' . implode(' OR ', $parts) . ')'; }
        }
        $sortMap = ['id'=>'id','price'=>'price','stock'=>'stock','sold_count'=>'sold_count','created_at'=>'created_at','updated_at'=>'updated_at'];
        $sort = (string)($_GET['sort'] ?? '-updated_at');
        $dir = str_starts_with($sort, '-') ? 'DESC' : 'ASC';
        $orderColumn = $sortMap[ltrim($sort, '-')] ?? 'id';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 15)));
        $offset = ($page - 1) * $perPage;
        $whereSql = implode(' AND ', $where);
        $total = (int)($db->cell("SELECT COUNT(*) FROM selling_topups WHERE {$whereSql}", ...$args) ?? 0);
        $rows = $db->run("SELECT * FROM selling_topups WHERE {$whereSql} ORDER BY {$orderColumn} {$dir}, id DESC LIMIT {$perPage} OFFSET {$offset}", ...$args) ?: [];
        $lastPage = max(1, (int)ceil($total / $perPage));
        $response = [
            'data' => array_map('seller_api_public_topup', $rows),
            'meta' => ['current_page'=>$page,'from'=>$total ? $offset + 1 : null,'last_page'=>$lastPage,'per_page'=>$perPage,'to'=>$total ? min($offset + $perPage, $total) : null,'total'=>$total],
            'links' => ['first'=>'?page=1','last'=>'?page='.$lastPage,'prev'=>$page > 1 ? '?page='.($page - 1) : null,'next'=>$page < $lastPage ? '?page='.($page + 1) : null],
        ];
        seller_api_finish($auth, 'GET', '/api/v1/top-ups', $response, 200, $_GET, null, null, $startedAt, 'topup');
    }
}

if (!function_exists('seller_api_get_topup')) {
    function seller_api_get_topup($id): void
    {
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $row = seller_api_find_topup($id, (int)$auth['seller_id']);
        if (empty($row)) { seller_api_finish($auth, 'GET', '/api/v1/top-ups/' . $id, ['message' => 'Top-up not found.'], 404, null, null, is_numeric($id) ? null : (string)$id, $startedAt, 'topup'); }
        seller_api_finish($auth, 'GET', '/api/v1/top-ups/' . $id, ['data' => seller_api_public_topup($row)], 200, null, null, $row['external_id'] ?? null, $startedAt, 'topup');
    }
}

if (!function_exists('seller_api_update_topup')) {
    function seller_api_update_topup($id): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $row = seller_api_find_topup($id, $seller_id);
        $data = seller_api_read_json();
        if (empty($row)) { seller_api_finish($auth, 'PATCH', '/api/v1/top-ups/' . $id, ['message' => 'Top-up not found.'], 404, $data, null, is_numeric($id) ? null : (string)$id, $startedAt, 'topup'); }

        $update = [];
        // Direct column fields
        $direct = ['service_label','offer_key','region','platform','price','currency','stock','min_quantity','instructions'];
        foreach ($direct as $field) { if (array_key_exists($field, $data)) { $update[$field] = $data[$field]; } }
        // Aliased fields
        if (array_key_exists('title', $data))  { $update['offer_title']  = $data['title']; }
        if (array_key_exists('offer_title', $data)) { $update['offer_title'] = $data['offer_title']; }
        if (array_key_exists('amount', $data)) { $update['offer_amount'] = (float)$data['amount']; }
        if (array_key_exists('offer_amount', $data)) { $update['offer_amount'] = (float)$data['offer_amount']; }
        if (array_key_exists('unit', $data))   { $update['offer_unit']   = $data['unit']; }
        if (array_key_exists('offer_unit', $data)) { $update['offer_unit'] = $data['offer_unit']; }
        if (array_key_exists('image_url', $data)) { $update['image'] = $data['image_url']; }
        if (array_key_exists('image', $data))     { $update['image'] = $data['image']; }
        if (array_key_exists('game', $data)) {
            $gs = strtolower(trim((string)$data['game']));
            if ($gs !== '') {
                $gr = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gs) ?: []) : [];
                $update['game_slug'] = $gs;
                $update['game_id'] = (int)($gr['id'] ?? 0) ?: null;
                $update['game_name'] = (string)($data['game_name'] ?? ($gr['name'] ?? ucwords(str_replace('-', ' ', $gs))));
            }
        }
        if (array_key_exists('waiting_time_value', $data) || array_key_exists('waiting_time_unit', $data)) {
            $unit = strtolower(trim((string)($data['waiting_time_unit'] ?? ($row['waiting_time_unit'] ?? 'minutes'))));
            if (!in_array($unit, ['minutes','hours','days'], true)) { $unit = 'minutes'; }
            $val = (int)($data['waiting_time_value'] ?? ($row['waiting_time_value'] ?? 0));
            $update['waiting_time_unit'] = $unit;
            $update['waiting_time_value'] = $val;
            $update['waiting_time_minutes'] = seller_api_topup_waiting_minutes($val, $unit);
        }
        if (array_key_exists('status', $data)) { $update['active'] = ((string)$data['status'] === 'listed') ? 1 : 0; }

        $update['api_updated_at'] = date('Y-m-d H:i:s');
        if (count($update) <= 1) { seller_api_finish($auth, 'PATCH', '/api/v1/top-ups/' . $id, ['message' => 'No valid fields sent.'], 422, $data, null, $row['external_id'] ?? null, $startedAt, 'topup'); }
        db_update_row('selling_topups', ['id' => (int)$row['id'], 'seller_id' => $seller_id], $update);
        $updated = $db->row("SELECT * FROM selling_topups WHERE id = ? AND seller_id = ? LIMIT 1", (int)$row['id'], $seller_id);
        seller_api_dispatch_topup_webhook('topup.updated', $updated);
        seller_api_finish($auth, 'PATCH', '/api/v1/top-ups/' . $id, ['data' => seller_api_public_topup($updated)], 200, $data, null, $updated['external_id'] ?? null, $startedAt, 'topup');
    }
}

if (!function_exists('seller_api_delete_topup')) {
    function seller_api_delete_topup($id): void
    {
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $row = seller_api_find_topup($id, $seller_id);
        if (empty($row)) { seller_api_finish($auth, 'DELETE', '/api/v1/top-ups/' . $id, ['message' => 'Top-up not found.'], 404, null, null, is_numeric($id) ? null : (string)$id, $startedAt, 'topup'); }
        db_update_row('selling_topups', ['id' => (int)$row['id'], 'seller_id' => $seller_id], ['active' => 0, 'api_updated_at' => date('Y-m-d H:i:s')]);
        $updated = seller_api_find_topup($row['id'], $seller_id);
        if (!empty($updated)) { seller_api_dispatch_topup_webhook('topup.archived', $updated, ['archived_reason' => 'api_delete']); }
        seller_api_finish($auth, 'DELETE', '/api/v1/top-ups/' . $id, ['message' => 'Top-up archived.', 'data' => seller_api_public_topup($updated ?: $row)], 200, null, null, $row['external_id'] ?? null, $startedAt, 'topup');
    }
}

if (!function_exists('seller_api_bulk_remove_topups')) {
    function seller_api_bulk_remove_topups(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $data = seller_api_read_json();
        $endpoint = '/api/v1/top-ups/bulk-remove';
        $externalIds = $data['external_ids'] ?? $data['external_id'] ?? [];
        $lolboostIds = $data['lolboost_topup_ids'] ?? $data['ids'] ?? [];
        if (is_string($externalIds)) { $externalIds = array_filter(array_map('trim', explode(',', $externalIds)), 'strlen'); }
        if (is_string($lolboostIds)) { $lolboostIds = array_filter(array_map('trim', explode(',', $lolboostIds)), 'strlen'); }
        if (!is_array($externalIds)) { $externalIds = []; }
        if (!is_array($lolboostIds)) { $lolboostIds = []; }
        $externalIds = array_values(array_unique(array_filter(array_map('strval', $externalIds), 'strlen')));
        $lolboostIds = array_values(array_unique(array_filter(array_map('intval', $lolboostIds), fn($v) => $v > 0)));
        if (empty($externalIds) && empty($lolboostIds)) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'external_ids or lolboost_topup_ids are required.'], 422, $data, null, null, $startedAt, 'topup');
        }
        if (count($externalIds) + count($lolboostIds) > 250) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Maximum 250 top-ups per bulk remove request.'], 422, $data, null, null, $startedAt, 'topup');
        }
        $whereParts = [];
        $args = [$seller_id];
        if (!empty($externalIds)) { $whereParts[] = 'external_id IN (' . implode(',', array_fill(0, count($externalIds), '?')) . ')'; foreach ($externalIds as $v) { $args[] = $v; } }
        if (!empty($lolboostIds)) { $whereParts[] = 'id IN (' . implode(',', array_fill(0, count($lolboostIds), '?')) . ')'; foreach ($lolboostIds as $v) { $args[] = $v; } }
        $whereSql = 'seller_id = ? AND (' . implode(' OR ', $whereParts) . ')';
        $rows = $db->run("SELECT * FROM selling_topups WHERE {$whereSql}", ...$args) ?: [];
        $archived = [];
        $notFoundExternalIds = $externalIds;
        $notFoundLolboostIds = $lolboostIds;
        foreach ($rows as $row) {
            $rid = (int)($row['id'] ?? 0);
            $rexternal = (string)($row['external_id'] ?? '');
            $notFoundExternalIds = array_values(array_diff($notFoundExternalIds, [$rexternal]));
            $notFoundLolboostIds = array_values(array_diff($notFoundLolboostIds, [$rid]));
            db_update_row('selling_topups', ['id' => $rid, 'seller_id' => $seller_id], ['active' => 0, 'api_updated_at' => date('Y-m-d H:i:s')]);
            $updated = seller_api_find_topup($rid, $seller_id) ?: $row;
            $archived[] = seller_api_public_topup($updated);
            seller_api_dispatch_topup_webhook('topup.archived', $updated, ['archived_reason' => 'api_bulk_remove']);
        }
        $response = ['message'=>'Bulk remove completed.','data'=>['archived_count'=>count($archived),'not_found_count'=>count($notFoundExternalIds)+count($notFoundLolboostIds),'archived'=>$archived,'not_found'=>['external_ids'=>array_values($notFoundExternalIds),'lolboost_topup_ids'=>array_values($notFoundLolboostIds)]]];
        seller_api_finish($auth, 'POST', $endpoint, $response, 200, $data, null, null, $startedAt, 'topup');
    }
}

if (!function_exists('seller_api_bulk_update_topups')) {
    function seller_api_bulk_update_topups(): void
    {
        global $db;
        $startedAt = microtime(true);
        $auth = seller_api_auth();
        $seller_id = (int)$auth['seller_id'];
        $data = seller_api_read_json();
        $endpoint = '/api/v1/top-ups/bulk-update';
        $topups = $data['topups'] ?? ($data['top_ups'] ?? []);
        if (!is_array($topups) || empty($topups)) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'topups array is required.'], 422, $data, null, null, $startedAt, 'topup');
        }
        if (count($topups) > 100) {
            seller_api_finish($auth, 'POST', $endpoint, ['message' => 'Maximum 100 top-ups per bulk update request.'], 422, $data, null, null, $startedAt, 'topup');
        }
        $updatedTopups = [];
        $notFound = [];
        foreach ($topups as $payload) {
            if (!is_array($payload)) { continue; }
            $tid = $payload['external_id'] ?? $payload['lolboost_topup_id'] ?? $payload['id'] ?? null;
            if ($tid === null || $tid === '') { continue; }
            $row = seller_api_find_topup($tid, $seller_id);
            if (empty($row)) { $notFound[] = (string)$tid; continue; }
            $update = [];
            foreach (['price','stock','region','platform','service_label','instructions'] as $field) {
                if (array_key_exists($field, $payload)) { $update[$field] = $payload[$field]; }
            }
            if (array_key_exists('title', $payload)) { $update['offer_title'] = $payload['title']; }
            if (array_key_exists('amount', $payload)) { $update['offer_amount'] = (float)$payload['amount']; }
            if (array_key_exists('status', $payload)) { $update['active'] = ((string)$payload['status'] === 'listed') ? 1 : 0; }
            if (empty($update)) { continue; }
            $update['api_updated_at'] = date('Y-m-d H:i:s');
            db_update_row('selling_topups', ['id'=>(int)$row['id'], 'seller_id'=>$seller_id], $update);
            $updated = seller_api_find_topup($row['id'], $seller_id) ?: $row;
            $updatedTopups[] = seller_api_public_topup($updated);
            seller_api_dispatch_topup_webhook('topup.updated', $updated);
        }
        $response = ['message'=>'Bulk update completed.','data'=>['updated_count'=>count($updatedTopups),'not_found_count'=>count($notFound),'updated'=>$updatedTopups,'not_found'=>$notFound]];
        seller_api_finish($auth, 'POST', $endpoint, $response, 200, $data, null, null, $startedAt, 'topup');
    }
}

if (!function_exists('seller_api_dispatch_topup_webhook')) {
    function seller_api_dispatch_topup_webhook(string $event, array $topup, array $extra = []): void
    {
        global $db;
        seller_api_ensure_tables();
        $seller_id = (int)($topup['seller_id'] ?? 0);
        if ($seller_id <= 0) { return; }
        $hooks = $db->run("SELECT * FROM seller_webhooks WHERE seller_id = ? AND is_active = 1", $seller_id) ?: [];
        if (empty($hooks)) { return; }
        $payload = array_merge([
            'event' => $event,
            'topup' => seller_api_public_topup($topup),
            'external_id' => $topup['external_id'] ?? null,
            'lolboost_topup_id' => (int)($topup['id'] ?? 0),
            'quantity' => isset($extra['quantity']) ? (int)$extra['quantity'] : null,
            'sold_at' => ($event === 'topup.sold') ? ($extra['sold_at'] ?? date('Y-m-d H:i:s')) : null,
            'created_at' => $topup['created_at'] ?? null,
            'updated_at' => $topup['updated_at'] ?? $topup['api_updated_at'] ?? null,
            'archived_at' => ($event === 'topup.archived') ? date('Y-m-d H:i:s') : null,
        ], $extra);
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        foreach ($hooks as $hook) {
            $events = array_filter(array_map('trim', explode(',', (string)$hook['events'])));
            if (!in_array($event, $events, true) && !in_array('*', $events, true)) { continue; }
            $signature = hash_hmac('sha256', $body, (string)$hook['secret']);
            $code = 0; $response = ''; $success = 0;
            try {
                $ch = curl_init((string)$hook['url']);
                curl_setopt_array($ch, [CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Lolboost-Event: '.$event,'X-Lolboost-Signature: sha256='.$signature],CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>8]);
                $response = (string)curl_exec($ch);
                $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $success = ($code >= 200 && $code < 300) ? 1 : 0;
            } catch (Throwable $e) { $response = $e->getMessage(); }
            db_add_row('seller_webhook_logs', ['seller_id'=>$seller_id,'webhook_id'=>(int)$hook['id'],'entity_type'=>'topup','account_id'=>null,'item_id'=>null,'topup_id'=>(int)($topup['id'] ?? 0),'event'=>$event,'payload'=>$body,'response_code'=>$code,'response_body'=>substr($response,0,2000),'success'=>$success]);
        }
    }
}
