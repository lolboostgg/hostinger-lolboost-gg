<?php


declare(strict_types=1);

$debug = !empty($_GET['debug']);

if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

header('Content-Type: application/json; charset=utf-8');

// Cheap per-browser throttle before loading app/session code.
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
$lbPresenceNow = time();
$lbPresenceKey = 'lb_admin_presence_ping_last_at';
$lbPresenceMinGap = 25;
if (isset($_SESSION[$lbPresenceKey]) && ($lbPresenceNow - (int)$_SESSION[$lbPresenceKey]) < $lbPresenceMinGap) {
    echo json_encode([
        'ok' => true,
        'skipped' => true,
        'client_throttled' => true,
        'min_gap_seconds' => $lbPresenceMinGap,
    ]);
    exit;
}
$_SESSION[$lbPresenceKey] = $lbPresenceNow;
session_write_close();

register_shutdown_function(function () use ($debug) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        if ($debug) {
            echo json_encode([
                'ok' => false,
                'error' => 'fatal_error',
                'type' => $err['type'],
                'message' => $err['message'],
                'file' => $err['file'],
                'line' => $err['line'],
            ]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'server_error']);
        }
    }
});

try {
    foreach ([__DIR__ . '/vendor/autoload.php', __DIR__ . '/../vendor/autoload.php'] as $p) {
        if (file_exists($p)) {
            require_once $p;
            break;
        }
    }

    require_once __DIR__ . '/app/core/config.php';
    // Do not load functions.php/session.php for a heartbeat. Keep it to one
    // lightweight token lookup plus the optional presence insert.

    global $db;

    $token = (string)($_COOKIE['admin_session_token'] ?? '');

    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'missing_token']);
        exit;
    }

    $adminId = $db->cell('SELECT admin_id FROM admin_sessions WHERE token = ? LIMIT 1', $token);
    if (empty($adminId)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'invalid_token']);
        exit;
    }

    $nowSql = date('Y-m-d H:i:s');
    $nowIso = date('c');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $payload = [
        'tag'   => 'admin_presence',
        'v'     => (string)($_POST['v'] ?? ''),
        'focus' => (string)($_POST['focus'] ?? ''),
        'path'  => (string)($_POST['path'] ?? ''),
        'ts'    => $nowIso,
        'ua'    => $ua,
    ];

    $throttleSeconds = 30;
    $keepPerAdmin = 200;

    $latestPresence = $db->row(
        "SELECT id, created_at
         FROM admin_session_logs
         WHERE admin_id = ?
           AND token = ?
           AND device_info LIKE '%\"tag\":\"admin_presence\"%'
         ORDER BY id DESC
         LIMIT 1",
        (int)$adminId,
        $token
    );

    $shouldInsert = true;
    if (!empty($latestPresence['created_at'])) {
        $lastTs = strtotime((string)$latestPresence['created_at']);
        if ($lastTs !== false && (time() - $lastTs) < $throttleSeconds) {
            $shouldInsert = false;
        }
    }

    if ($shouldInsert) {
        $db->insert('admin_session_logs', [
            'token'       => $token,
            'admin_id'    => (int)$adminId,
            'ip_address'  => $ip,
            'created_at'  => $nowSql,
            'device_info' => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);

        $idsToKeep = $db->run(
            "SELECT id
             FROM admin_session_logs
             WHERE admin_id = ?
               AND device_info LIKE '%\"tag\":\"admin_presence\"%'
             ORDER BY id DESC
             LIMIT {$keepPerAdmin}",
            (int)$adminId
        ) ?: [];

        if (!empty($idsToKeep)) {
            $keepIds = array_map(static function ($row) {
                return (int)($row['id'] ?? 0);
            }, $idsToKeep);
            $keepIds = array_values(array_filter($keepIds));

            if (!empty($keepIds)) {
                $keepList = implode(',', $keepIds);
                $db->run(
                    "DELETE FROM admin_session_logs
                     WHERE admin_id = ?
                       AND device_info LIKE '%\"tag\":\"admin_presence\"%'
                       AND id NOT IN ({$keepList})",
                    (int)$adminId
                );
            }
        }
    }

    echo json_encode([
        'ok' => true,
        'admin_id' => (int)$adminId,
        'throttled' => !$shouldInsert,
        'throttle_seconds' => $throttleSeconds,
        'keep_per_admin' => $keepPerAdmin,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    if ($debug) {
        echo json_encode([
            'ok' => false,
            'error' => 'server_error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'server_error']);
    }
}
