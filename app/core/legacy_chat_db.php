<?php

/** Database storage and idempotent archive import for former JSON chats. */

function lb_legacy_chat_ensure_schema(): void
{
    global $db;
    static $done = false;
    if ($done) return;
    $done = true;
    $db->run("CREATE TABLE IF NOT EXISTS legacy_chat_threads (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        chat_key VARCHAR(191) NOT NULL,
        chat_type VARCHAR(64) NOT NULL,
        ref_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        seller_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        client_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        metadata LONGTEXT NULL,
        imported_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL,
        UNIQUE KEY uq_legacy_chat_key (chat_key),
        KEY idx_legacy_chat_ref (chat_type, ref_id),
        KEY idx_legacy_chat_seller (seller_id, updated_at),
        KEY idx_legacy_chat_client (client_id, updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->run("CREATE TABLE IF NOT EXISTS legacy_chat_messages (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        thread_id BIGINT UNSIGNED NOT NULL,
        legacy_key CHAR(64) NULL,
        sender_type VARCHAR(32) NOT NULL,
        sender_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        sender_name VARCHAR(191) NULL,
        sender_icon VARCHAR(512) NULL,
        body LONGTEXT NULL,
        rendered_body LONGTEXT NULL,
        message_type VARCHAR(32) NOT NULL DEFAULT 'text',
        seen_by_client TINYINT(1) NOT NULL DEFAULT 0,
        seen_by_seller TINYINT(1) NOT NULL DEFAULT 0,
        seen_by_admin TINYINT(1) NOT NULL DEFAULT 0,
        notify TINYINT(1) NOT NULL DEFAULT 0,
        deleted TINYINT(1) NOT NULL DEFAULT 0,
        edited TINYINT(1) NOT NULL DEFAULT 0,
        read_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_legacy_message (thread_id, legacy_key),
        KEY idx_legacy_messages_thread (thread_id, id),
        KEY idx_legacy_messages_unread (thread_id, sender_type, deleted)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function lb_legacy_chat_thread(string $key, string $type, int $refId, array $meta = []): array
{
    global $db;
    lb_legacy_chat_ensure_schema();
    $row = $db->row("SELECT * FROM legacy_chat_threads WHERE chat_key=? LIMIT 1", $key);
    if (!$row) {
        $db->run("INSERT INTO legacy_chat_threads (chat_key,chat_type,ref_id,seller_id,client_id,metadata,updated_at) VALUES (?,?,?,?,?,?,NOW())",
            $key, $type, $refId, (int)($meta['seller_id'] ?? 0), (int)($meta['client_id'] ?? 0), json_encode($meta, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        $row = $db->row("SELECT * FROM legacy_chat_threads WHERE chat_key=? LIMIT 1", $key);
    } else {
        $db->run("UPDATE legacy_chat_threads SET seller_id=GREATEST(seller_id,?),client_id=GREATEST(client_id,?),metadata=?,updated_at=COALESCE(updated_at,NOW()) WHERE id=?",
            (int)($meta['seller_id'] ?? 0), (int)($meta['client_id'] ?? 0), json_encode($meta, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), (int)$row['id']);
    }
    return $row ?: [];
}

function lb_legacy_chat_import_json(array $thread, string $path): void
{
    global $db;
    // Keep the archive import idempotent, but do not skip the file forever after its first
    // import. Some legacy send handlers can still append during the rollout; INSERT IGNORE
    // makes re-scanning safe and ensures those messages reach the database too.
    if (!$thread || !is_file($path)) return;
    $data = json_decode((string)@file_get_contents($path), true);
    $messages = is_array($data['messages'] ?? null) ? $data['messages'] : [];
    foreach (array_values($messages) as $index => $m) {
        if (!is_array($m)) continue;
        $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['type'] ?? 'system')));
        if (!in_array($sender, ['client','seller','admin','booster','system'], true)) $sender = 'system';
        $body = (string)($m['message'] ?? $m['body'] ?? $m['raw'] ?? $m['content'] ?? '');
        $created = (string)($m['created_at'] ?? '');
        if ($created === '' && !empty($m['time'])) {
            $timestamp = (int)$m['time'];
            if ($timestamp > 20000000000) $timestamp = (int)floor($timestamp / 1000);
            $created = gmdate('Y-m-d H:i:s', $timestamp);
        } elseif ($created !== '') {
            // JSON chat timestamps are generated in Europe/Berlin, while the
            // database server stores CURRENT_TIMESTAMP in UTC. Normalize before
            // inserting so five-minute comparisons cannot be two hours late.
            try {
                $createdDate = new \DateTime($created, new \DateTimeZone('Europe/Berlin'));
                $createdDate->setTimezone(new \DateTimeZone('UTC'));
                $created = $createdDate->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $created = '';
            }
        }
        if ($created === '' || strtotime($created . ' UTC') === false) $created = gmdate('Y-m-d H:i:s');
        $legacyKey = hash('sha256', $path.'|'.$index.'|'.json_encode($m, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        $db->run("INSERT IGNORE INTO legacy_chat_messages
            (thread_id,legacy_key,sender_type,sender_id,sender_name,sender_icon,body,rendered_body,message_type,seen_by_client,seen_by_seller,seen_by_admin,notify,deleted,edited,read_at,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            (int)$thread['id'], $legacyKey, $sender, (int)($m['sender_id'] ?? 0), (string)($m['sender_name'] ?? ''), (string)($m['sender_icon'] ?? ''),
            $body, (string)($m['content'] ?? $body), (string)($m['message_type'] ?? (($m['type'] ?? '') === 'image' ? 'image' : 'text')),
            (int)($m['seen_by_client'] ?? ($sender === 'client' ? 1 : ($m['seen'] ?? 0))),
            (int)($m['seen_by_seller'] ?? ($sender === 'seller' ? 1 : ($m['seen'] ?? 0))),
            (int)($m['seen_by_admin'] ?? ($sender === 'admin' ? 1 : 0)), (int)($m['notify'] ?? 0), (int)($m['deleted'] ?? 0), (int)($m['edited'] ?? 0),
            !empty($m['read_at']) ? date('Y-m-d H:i:s', strtotime((string)$m['read_at'])) : null, $created);
    }
    $db->run("UPDATE legacy_chat_threads SET imported_at=NOW(),updated_at=COALESCE((SELECT MAX(created_at) FROM legacy_chat_messages WHERE thread_id=?),NOW()) WHERE id=?", (int)$thread['id'], (int)$thread['id']);
}

function lb_legacy_chat_open(string $key, string $type, int $refId, array $meta = [], ?string $archivePath = null): array
{
    $thread = lb_legacy_chat_thread($key, $type, $refId, $meta);
    if ($archivePath) lb_legacy_chat_import_json($thread, $archivePath);
    global $db;
    return ['thread' => $db->row("SELECT * FROM legacy_chat_threads WHERE id=?", (int)$thread['id']),
        'messages' => $db->run("SELECT * FROM legacy_chat_messages WHERE thread_id=? ORDER BY created_at,id", (int)$thread['id']) ?: []];
}

/**
 * Mirrors a seller's "mark as read" action from the flat JSON chat files into
 * legacy_chat_messages. Once a thread has been imported (imported_at set),
 * util_chat_json_unread_count() reads seen_by_seller from this table instead of
 * the JSON file, so without this sync the seller unread badge never reflects
 * reads performed via seller_account_chat_load/item_chat_load/topup_chat_load.
 */
function lb_legacy_chat_sync_seller_seen(string $refType, int $refId): void
{
    global $db;
    if (!isset($db) || !is_object($db) || $refId <= 0) return;
    try {
        $key = 'seller:' . $refType . ':' . $refId;
        $thread = $db->row("SELECT id FROM legacy_chat_threads WHERE chat_key=? AND imported_at IS NOT NULL LIMIT 1", $key);
        if (!empty($thread['id'])) {
            $db->run("UPDATE legacy_chat_messages SET seen_by_seller=1, read_at=COALESCE(read_at, NOW()) WHERE thread_id=? AND sender_type='client' AND seen_by_seller=0", (int)$thread['id']);
        }
    } catch (\Throwable $e) {}
}

function lb_legacy_chat_add(int $threadId, string $sender, int $senderId, string $name, string $body, string $type = 'text', array $extra = []): int
{
    global $db;
    $db->run("INSERT INTO legacy_chat_messages (thread_id,sender_type,sender_id,sender_name,sender_icon,body,rendered_body,message_type,seen_by_client,seen_by_seller,seen_by_admin,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())",
        $threadId, $sender, $senderId, $name, (string)($extra['sender_icon'] ?? ''), $body, (string)($extra['rendered_body'] ?? $body), $type,
        $sender === 'client' ? 1 : 0, $sender === 'seller' ? 1 : 0, $sender === 'admin' ? 1 : 0);
    $id = (int)$db->lastInsertId();
    $db->run("UPDATE legacy_chat_threads SET updated_at=NOW() WHERE id=?", $threadId);
    return $id;
}
