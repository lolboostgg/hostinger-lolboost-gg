<?php

/**
 * ============================================================
 * Chat-System: Datenbankbasierte Ersatzfunktionen
 * ============================================================
 * Drop-in Ersatz für die bisherigen JSON-basierten chat_*-Funktionen.
 * Gleiche Funktionsnamen, gleiche Rückgabestruktur – kein weiterer Code muss geändert werden.
 *
 * Voraussetzung: Tabelle chat_messages existiert (siehe chat_db_migration.sql)
 * ============================================================
 */


// ---------------------------------------------------------------------------
// chat_load_messages()
// Lädt alle Nachrichten einer Order – gibt dieselbe Struktur zurück wie vorher.
// ---------------------------------------------------------------------------
function chat_load_messages($order_id)
{
    global $db;

    $order_id = (string) $order_id;

    $rows = $db->run(
        "SELECT * FROM chat_messages
         WHERE order_id = ?
         ORDER BY time ASC, id ASC",
        $order_id
    );

    $messages = [];
    foreach ((array) $rows as $row) {
        $messages[] = _chat_row_to_array($row);
    }

    return [
        'order_id' => $order_id,
        'messages' => $messages,
    ];
}


// ---------------------------------------------------------------------------
// chat_prepare_for_viewer()
// Filtert gelöschte Nachrichten je nach Viewer-Typ – identisch zur alten Logik.
// ---------------------------------------------------------------------------
function chat_prepare_for_viewer(array $chat_data, string $viewer): array
{
    if (!isset($chat_data['messages']) || !is_array($chat_data['messages'])) {
        $chat_data['messages'] = [];
        return $chat_data;
    }

    $out = [];

    foreach ($chat_data['messages'] as $m) {
        if (!is_array($m)) continue;

        $content = trim((string)($m['content'] ?? ''));
        $raw     = trim((string)($m['raw'] ?? ''));

        if ($content === '' && $raw === '') continue;
        if (empty($m['time']) || !is_numeric($m['time'])) continue;

        // Sender-Name Fallback
        if (empty($m['sender_name'])) {
            $sender = $m['sender'] ?? '';
            $m['sender_name'] =
                ($sender === 'system') ? 'System' :
                (($sender === 'admin') ? 'Admin' :
                (($sender === 'booster') ? 'Booster' : 'Customer'));
        }

        $is_deleted = !empty($m['deleted']) || (($m['type'] ?? '') === 'deleted');

        // Client/Booster: gelöschte Nachrichten komplett ausblenden
        if ($is_deleted && $viewer !== 'admin') {
            continue;
        }

        // Admin: Platzhalter anzeigen
        if ($is_deleted && $viewer === 'admin') {
            $m['type']    = 'deleted';
            $m['content'] = 'Message deleted.';
        }

        $out[] = $m;
    }

    $chat_data['messages'] = array_values($out);
    return $chat_data;
}


// ---------------------------------------------------------------------------
// chat_insert_message()
// Fügt eine neue Nachricht in die DB ein – atomare INSERT-Operation.
// ---------------------------------------------------------------------------
function chat_insert_message($order_id, $data, $user, $user_type)
{
    global $db;

    $order_id    = (string) $order_id;
    $msg_content = esc($data['message'] ?? '');
    $msg_type    = (string)($data['message_type'] ?? 'text');

    // Validierung (identisch zu vorher)
    if ($msg_type === 'image') {
        if ($msg_content === '') {
            return ['error' => 'Image is empty.'];
        }
    } else {
        if ($msg_content === '') {
            return ['error' => 'Message is empty.'];
        }
    }

    $now         = time();
    $sender_id   = intval($user['id'] ?? 0);
    $sender_name = (string)($user['username'] ?? (($user_type === 'system') ? 'System' : 'Unknown'));
    $sender_icon = (string)($user['icon'] ?? '');
    $type_value  = ($msg_type === 'image') ? 'image' : 'text';

    // Inhalt rendern
    if ($msg_type === 'image') {
        $safe_url         = htmlspecialchars($msg_content, ENT_QUOTES, 'UTF-8');
        $rendered_content = '<a href="' . $safe_url . '" target="_blank" rel="noopener">'
            . '<img class="lb-chat-image" src="' . $safe_url . '" alt="Image">'
            . '</a>';
    } else {
        $rendered_content = make_links_clickable($msg_content);
    }

    $uuid = $now . '-' . bin2hex(random_bytes(4));

    try {
        $db->run(
            "INSERT INTO chat_messages
                (order_id, uuid, sender, sender_id, sender_name, sender_icon,
                 content, raw, type, edited, seen, seen_at, notify, deleted, time)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 0, 0, ?)",
            $order_id,
            $uuid,
            $user_type,
            $sender_id,
            $sender_name,
            $sender_icon,
            $rendered_content,
            $msg_content,
            $type_value,
            $now
        );
    } catch (\Exception $e) {
        return ['error' => 'DB error: ' . $e->getMessage()];
    }

    return ['success' => 'Sent.'];
}


// ---------------------------------------------------------------------------
// chat_update_notify()
// Setzt notify=1 für eine bestimmte Nachricht (per DB-ID oder Array-Index).
// ---------------------------------------------------------------------------
function chat_update_notify($order_id, $message_id)
{
    global $db;

    $order_id   = (string) $order_id;
    $message_id = (int) $message_id;

    // message_id war früher der Array-Index – wir ermitteln die echte DB-ID
    // über LIMIT/OFFSET (gleiche Reihenfolge wie chat_load_messages)
    $row = $db->row(
        "SELECT id, notify FROM chat_messages
         WHERE order_id = ?
         ORDER BY time ASC, id ASC
         LIMIT 1 OFFSET ?",
        $order_id,
        $message_id
    );

    if (!$row) {
        return ['success' => false];
    }

    if (empty($row['notify'])) {
        $db->run(
            "UPDATE chat_messages SET notify = 1 WHERE id = ?",
            $row['id']
        );
    }

    return ['success' => true];
}


// ---------------------------------------------------------------------------
// chat_update_seen()
// Markiert alle Nachrichten bis message_id als gelesen (für den Viewer).
// ---------------------------------------------------------------------------
function chat_update_seen($order_id, $viewer_type, $viewer_id, $message_id)
{
    global $db;

    $order_id    = (string) $order_id;
    $viewer_type = (string) $viewer_type;
    $viewer_id   = (int) $viewer_id;
    $message_id  = (int) $message_id;
    $now         = time();

    // Alle Nachrichten bis zum Index ermitteln (nach gleichem Sort)
    $rows = $db->run(
        "SELECT id, sender FROM chat_messages
         WHERE order_id = ?
         ORDER BY time ASC, id ASC
         LIMIT ?",
        $order_id,
        $message_id + 1  // +1 weil 0-basierter Index
    );

    if (empty($rows)) {
        return ['success' => false];
    }

    $ids_to_mark = [];
    foreach ($rows as $row) {
        $sender = (string)($row['sender'] ?? '');
        if ($sender === '' || $sender === 'system') continue;
        if ($sender === $viewer_type) continue;  // nur fremde Nachrichten markieren
        $ids_to_mark[] = (int) $row['id'];
    }

    if (!empty($ids_to_mark)) {
        $placeholders = implode(',', array_fill(0, count($ids_to_mark), '?'));
        $db->run(
            "UPDATE chat_messages
             SET seen = 1, seen_at = ?, seen_by = ?, seen_by_id = ?
             WHERE id IN ($placeholders) AND seen = 0",
            array_merge([$now, $viewer_type, $viewer_id], $ids_to_mark)
        );
    }

    return ['success' => true];
}


// ---------------------------------------------------------------------------
// admin_delete_chat_message()  – Hilfsfunktion für den AJAX-Handler
// Löscht eine Nachricht (Soft-Delete, setzt deleted=1)
// ---------------------------------------------------------------------------
function chat_admin_delete_message($order_id, $message_id, $admin_name = 'Admin')
{
    global $db;

    $order_id   = (string) $order_id;
    $message_id = (int) $message_id;

    // Via Offset die DB-ID ermitteln
    $row = $db->row(
        "SELECT id FROM chat_messages
         WHERE order_id = ?
         ORDER BY time ASC, id ASC
         LIMIT 1 OFFSET ?",
        $order_id,
        $message_id
    );

    if (!$row) {
        return ['success' => false, 'error' => 'Message not found.'];
    }

    $db->run(
        "UPDATE chat_messages
         SET deleted = 1, deleted_by = ?, type = 'deleted', content = 'Message deleted.', raw = ''
         WHERE id = ?",
        $admin_name,
        $row['id']
    );

    return ['success' => true];
}


// ---------------------------------------------------------------------------
// chat_edit_message()  – Hilfsfunktion für den AJAX-Handler
// Bearbeitet eine bestehende Nachricht
// ---------------------------------------------------------------------------
function chat_edit_message($order_id, $message_id, $new_content, $editor_type = 'admin')
{
    global $db;

    $order_id    = (string) $order_id;
    $message_id  = (int) $message_id;
    $new_content = esc($new_content);

    if ($new_content === '') {
        return ['error' => 'Message cannot be empty.'];
    }

    $row = $db->row(
        "SELECT id, type FROM chat_messages
         WHERE order_id = ?
         ORDER BY time ASC, id ASC
         LIMIT 1 OFFSET ?",
        $order_id,
        $message_id
    );

    if (!$row) {
        return ['error' => 'Message not found.'];
    }

    if (($row['type'] ?? '') === 'image') {
        return ['error' => 'Cannot edit image messages.'];
    }

    $rendered = make_links_clickable($new_content);

    $db->run(
        "UPDATE chat_messages
         SET content = ?, raw = ?, edited = 1
         WHERE id = ?",
        $rendered,
        $new_content,
        $row['id']
    );

    return ['success' => true, 'content' => $rendered];
}


// ---------------------------------------------------------------------------
// Interner Helper: DB-Zeile → Array im alten Format
// ---------------------------------------------------------------------------
function _chat_row_to_array(array $row): array
{
    return [
        'sender'      => (string)($row['sender'] ?? ''),
        'sender_id'   => (int)($row['sender_id'] ?? 0),
        'sender_name' => (string)($row['sender_name'] ?? ''),
        'sender_icon' => (string)($row['sender_icon'] ?? ''),
        'content'     => (string)($row['content'] ?? ''),
        'raw'         => (string)($row['raw'] ?? ''),
        'edited'      => (int)($row['edited'] ?? 0),
        'type'        => (string)($row['type'] ?? 'text'),
        'seen'        => (int)($row['seen'] ?? 0),
        'seen_at'     => (int)($row['seen_at'] ?? 0),
        'seen_by'     => (string)($row['seen_by'] ?? ''),
        'seen_by_id'  => (int)($row['seen_by_id'] ?? 0),
        'notify'      => (int)($row['notify'] ?? 0),
        'deleted'     => (int)($row['deleted'] ?? 0),
        'time'        => (int)($row['time'] ?? 0),
        'uuid'        => (string)($row['uuid'] ?? ''),
    ];
}
