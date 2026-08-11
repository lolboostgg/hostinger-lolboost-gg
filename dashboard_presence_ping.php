<?php
/**
 * Dashboard Presence Ping (Booster / Client / Seller)
 *
 * Called by booster/client/seller dashboard JS as POST /dashboard_presence_ping.php.
 * Writes a lightweight heartbeat into the matching *_session_logs table so other
 * pages can show an accurate Online / Last seen badge for that user.
 *
 * NOTE: admins use a separate /admin_presence_ping.php endpoint, not this file.
 *
 * Deployment note: this file may be served either directly from the web root
 * (public_html/dashboard_presence_ping.php) or from app/tasks/. Both bootstrap
 * locations are tried below so it keeps working either way.
 */

declare(strict_types=1);

$debug = !empty($_GET['debug']);

if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

header('Content-Type: application/json; charset=utf-8');

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

// Find app/core regardless of whether this file lives at the web root or in app/tasks/.
$coreDir = null;
foreach ([__DIR__ . '/app/core', __DIR__ . '/../core', __DIR__ . '/core'] as $candidate) {
    if (file_exists($candidate . '/config.php')) {
        $coreDir = $candidate;
        break;
    }
}

if ($coreDir === null) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'bootstrap_not_found']);
    exit;
}

require_once $coreDir . '/config.php';
require_once $coreDir . '/session.php';
require_once $coreDir . '/functions.php';
// Presence helpers (lb_booster_presence_touch) live here. functions.php does NOT
// pull them in; app/init.php does, but this endpoint bootstraps directly without
// init.php — so load it explicitly or the booster online status can't be refreshed.
if (!function_exists('lb_booster_presence_touch') && file_exists($coreDir . '/presence.php')) {
    require_once $coreDir . '/presence.php';
}

try {
    global $db;

    // Each role has its own cookie. Try them in this order; whichever is present
    // tells us the real role (the actual identity is always re-verified against
    // the token below, $_POST['user_type'] is only used as a hint, never trusted).
    $cookieByType = [
        'client'  => 'client_session_token',
        'seller'  => 'seller_session_token',
        'booster' => 'booster_session_token',
    ];

    $token = '';
    foreach ($cookieByType as $type => $cookieName) {
        if (!empty($_COOKIE[$cookieName])) {
            $token = $_COOKIE[$cookieName];
            break;
        }
    }

    // Fallbacks (in case a different cookie name is used somewhere)
    if (!$token) $token = $_COOKIE['session_token'] ?? '';
    if (!$token) $token = $_COOKIE['token'] ?? '';

    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'missing_token']);
        exit;
    }

    $userType = auth_session_check_user_type($token);
    if (!in_array($userType, ['booster', 'client', 'seller'], true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'unsupported_user_type', 'user_type' => $userType]);
        exit;
    }

    $userId = auth_session_check_token($token, $userType);
    if (empty($userId)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'invalid_token']);
        exit;
    }

    // Only count as "active" if tab is visible (sent by JS snippet)
    $payload = [
        'tag'   => 'dashboard_presence',
        'v'     => ($_POST['v'] ?? ''),
        'focus' => ($_POST['focus'] ?? ''),
        'ts'    => date('c'),
        'ua'    => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];

    $row = [
        'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
        'created_at'  => date('Y-m-d H:i:s'),
        'device_info' => json_encode($payload, JSON_UNESCAPED_SLASHES)
    ];

    switch ($userType) {
        case 'booster':
            // Keeps the original column shape booster code already relies on.
            $row['token'] = $token;
            $row['booster_id'] = (int)$userId;
            db_add_row('booster_session_logs', $row);
            // Refresh boosters.last_seen_at so the "Online" status (which requires
            // availability_status='online' AND a fresh last_seen_at) stays alive
            // while the dashboard is open. Without this the heartbeat only wrote
            // session logs and the booster decayed to offline after the grace window.
            if (function_exists('lb_booster_presence_touch')) {
                lb_booster_presence_touch((int)$userId);
            }
            break;
        case 'client':
            $row['client_id'] = (int)$userId;
            db_add_row('client_session_logs', $row);
            break;
        case 'seller':
            $row['seller_id'] = (int)$userId;
            db_add_row('seller_session_logs', $row);
            break;
    }

    echo json_encode(['ok' => true, 'user_type' => $userType, 'user_id' => (int)$userId]);
} catch (Throwable $e) {
    http_response_code(500);

    // The exception was only ever visible with ?debug=1, which is useless for a
    // failure that happens sporadically under load — by the time you can retry
    // by hand it works again. Always record it so the real cause is captured
    // the moment it occurs.
    error_log('[presence_ping] ' . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine());

    // Optional debug: /dashboard_presence_ping.php?debug=1
    if ($debug) {
        echo json_encode([
            'ok' => false,
            'error' => 'server_error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'server_error']);
}
