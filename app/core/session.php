<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('db_get_row')) {
    $lb_functions_file = __DIR__ . '/functions.php';
    if (is_file($lb_functions_file)) {
        require_once $lb_functions_file;
    }
}

// Returns null when the lookup itself failed (DB hiccup/timeout), as opposed to
// false/[] which means the query ran fine and simply found no matching row.
// Callers must not treat null as "no session" — that would log a user out of a
// transient error instead of a real "your session doesn't exist" case, which is
// exactly what caused spurious 403s when admins had multiple tabs polling at once.
function lb_session_db_get_row($table, $params = [], $return_array = false) {
    if (function_exists('db_get_row')) {
        try {
            return db_get_row($table, $params, $return_array);
        } catch (\Throwable $e) {
            error_log('[session] DB lookup failed for ' . $table . ': ' . $e->getMessage());
            return null;
        }
    }

    return $return_array ? [] : false;
}

$user_id = false;
$user_type = false;
$user_data = false;
$token = [
    'global' => 'global-l9',
    'admin' => false,
    'booster' => false,
    'client' => false,
    'seller' => false,
];

function lb_clear_cookie($name) {
    setcookie($name, '', time() - 3600, '/');
}

function lb_define_once($name, $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

function lb_session_token_value($value) {
    if (function_exists('esc')) {
        return esc($value);
    }
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

# ADMIN CHECK
if (!empty($_COOKIE['admin_session_token'])) {
    $token['admin'] = lb_session_token_value($_COOKIE['admin_session_token']);
    $admin_id_row = lb_session_db_get_row('admin_sessions', ['select' => 'admin_id', 'token' => $token['admin']]);

    if ($admin_id_row === null) {
        // DB lookup failed transiently — treat as logged-out for THIS request only,
        // but keep the cookie so the next request can succeed instead of forcing a real logout.
        lb_define_once('ADMIN_ID', false);
        lb_define_once('ADMIN_DATA', false);
    } elseif (is_array($admin_id_row) && !empty($admin_id_row['admin_id'])) {
        $admin_id = $admin_id_row['admin_id'];
        $admin_data = lb_session_db_get_row('admins', ['id' => $admin_id]);

        if ($admin_data === null) {
            lb_define_once('ADMIN_ID', false);
            lb_define_once('ADMIN_DATA', false);
        } elseif (is_array($admin_data) && !empty($admin_data)) {
            $admin_role_data = lb_session_db_get_row('admin_roles', ['id' => $admin_data['role_id'] ?? null]);

            $admin_data['role_name'] = is_array($admin_role_data) ? ($admin_role_data['name'] ?? null) : null;
            $admin_data['permissions'] = is_array($admin_role_data) ? ($admin_role_data['permissions'] ?? null) : null;

            lb_define_once('ADMIN_ID', $admin_id);
            lb_define_once('ADMIN_DATA', $admin_data);
        } else {
            lb_clear_cookie('admin_session_token');
            lb_define_once('ADMIN_ID', false);
            lb_define_once('ADMIN_DATA', false);
        }
    } else {
        lb_clear_cookie('admin_session_token');
        lb_define_once('ADMIN_ID', false);
        lb_define_once('ADMIN_DATA', false);
    }
} else {
    lb_define_once('ADMIN_ID', false);
    lb_define_once('ADMIN_DATA', false);
}

# BOOSTER CHECK
if (!empty($_COOKIE['booster_session_token'])) {
    $token['booster'] = lb_session_token_value($_COOKIE['booster_session_token']);
    $booster_id_row = lb_session_db_get_row('booster_sessions', ['select' => 'booster_id', 'token' => $token['booster']]);

    if ($booster_id_row === null) {
        lb_define_once('BOOSTER_ID', false);
        lb_define_once('BOOSTER_DATA', false);
    } elseif (is_array($booster_id_row) && !empty($booster_id_row['booster_id'])) {
        $booster_id = $booster_id_row['booster_id'];
        $booster_data = lb_session_db_get_row('boosters', ['id' => $booster_id]);

        if ($booster_data === null) {
            lb_define_once('BOOSTER_ID', false);
            lb_define_once('BOOSTER_DATA', false);
        } elseif (is_array($booster_data) && !empty($booster_data) && empty($booster_data['is_banned'])) {
            $rank_name = null;
            if (!empty($booster_data['rank_id'])) {
                $rank_data = lb_session_db_get_row('booster_ranks', ['id' => $booster_data['rank_id']]);
                $rank_name = is_array($rank_data) ? ($rank_data['name'] ?? null) : null;
            }
            $booster_data['rank_name'] = $rank_name;

            lb_define_once('BOOSTER_ID', $booster_id);
            lb_define_once('BOOSTER_DATA', $booster_data);

            // "Letzte Aktivitaet zaehlt": jeder authentifizierte Booster-Request
            // (Seitenaufruf ODER AJAX-Poll) frischt last_seen_at auf, nicht nur der
            // Dashboard-Heartbeat. presence.php ist via init.php bereits geladen; der
            // UPDATE ist server-seitig auf LB_BOOSTER_PRESENCE_TOUCH_SECONDS gedrosselt,
            // also ein No-Op innerhalb des Fensters. Beruehrt NIE availability_status,
            // ein Booster auf Away/Offline bleibt also unsichtbar.
            if (function_exists('lb_booster_presence_touch')) {
                lb_booster_presence_touch((int) $booster_id);
            }
        } else {
            lb_clear_cookie('booster_session_token');
            lb_define_once('BOOSTER_ID', false);
            lb_define_once('BOOSTER_DATA', false);
        }
    } else {
        lb_clear_cookie('booster_session_token');
        lb_define_once('BOOSTER_ID', false);
        lb_define_once('BOOSTER_DATA', false);
    }
} else {
    lb_define_once('BOOSTER_ID', false);
    lb_define_once('BOOSTER_DATA', false);
}

# CLIENT CHECK
if (!empty($_COOKIE['client_session_token'])) {
    $token['client'] = lb_session_token_value($_COOKIE['client_session_token']);
    $client_id_row = lb_session_db_get_row('client_sessions', ['select' => 'client_id', 'token' => $token['client']]);

    if ($client_id_row === null) {
        lb_define_once('CLIENT_ID', false);
        lb_define_once('CLIENT_DATA', false);
    } elseif (is_array($client_id_row) && !empty($client_id_row['client_id'])) {
        $client_id = $client_id_row['client_id'];
        $client_data = lb_session_db_get_row('clients', ['id' => $client_id]);

        if ($client_data === null) {
            lb_define_once('CLIENT_ID', false);
            lb_define_once('CLIENT_DATA', false);
        } elseif (is_array($client_data) && !empty($client_data) && empty($client_data['is_banned'])) {
            lb_define_once('CLIENT_ID', $client_id);
            lb_define_once('CLIENT_DATA', $client_data);
        } else {
            lb_clear_cookie('client_session_token');
            lb_define_once('CLIENT_ID', false);
            lb_define_once('CLIENT_DATA', false);
        }
    } else {
        lb_clear_cookie('client_session_token');
        lb_define_once('CLIENT_ID', false);
        lb_define_once('CLIENT_DATA', false);
    }
} else {
    lb_define_once('CLIENT_ID', false);
    lb_define_once('CLIENT_DATA', false);
}

# SELLER CHECK
if (!empty($_COOKIE['seller_session_token'])) {
    $token['seller'] = lb_session_token_value($_COOKIE['seller_session_token']);
    $seller_id_row = lb_session_db_get_row('seller_sessions', ['select' => 'seller_id', 'token' => $token['seller']]);

    if ($seller_id_row === null) {
        lb_define_once('SELLER_ID', false);
        lb_define_once('SELLER_DATA', false);
    } elseif (is_array($seller_id_row) && !empty($seller_id_row['seller_id'])) {
        $seller_id_val = $seller_id_row['seller_id'];
        $seller_data_val = lb_session_db_get_row('sellers', ['id' => $seller_id_val]);

        if ($seller_data_val === null) {
            lb_define_once('SELLER_ID', false);
            lb_define_once('SELLER_DATA', false);
        } elseif (is_array($seller_data_val) && !empty($seller_data_val) && empty($seller_data_val['is_banned'])) {
            lb_define_once('SELLER_ID', $seller_id_val);
            lb_define_once('SELLER_DATA', $seller_data_val);
        } else {
            lb_clear_cookie('seller_session_token');
            lb_define_once('SELLER_ID', false);
            lb_define_once('SELLER_DATA', false);
        }
    } else {
        lb_clear_cookie('seller_session_token');
        lb_define_once('SELLER_ID', false);
        lb_define_once('SELLER_DATA', false);
    }
} else {
    lb_define_once('SELLER_ID', false);
    lb_define_once('SELLER_DATA', false);
}

// E-GIRL SESSION CHECK
// E-Girls nutzen die boosters Tabelle mit is_egirl=1
// Sie loggen sich über booster_session_token ein
// is_egirl wird aus BOOSTER_DATA gelesen (bereits oben gesetzt)
lb_define_once('IS_EGIRL', (bool)(defined('BOOSTER_DATA') && is_array(BOOSTER_DATA) && !empty(BOOSTER_DATA['is_egirl'])));

if (!isset($_SESSION['currency']) || empty($_SESSION['currency'])) {
    $_SESSION['currency'] = 'EUR';
}

$session_token = $token;
lb_define_once('SESSION_TOKEN', $token);
