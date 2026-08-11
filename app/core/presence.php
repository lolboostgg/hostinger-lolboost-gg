<?php

/**
 * Booster availability / presence.
 *
 * The booster sets the status manually in /booster-area/ (Online / Away / Offline).
 * That flag is the truth. The heartbeat only refreshes `boosters.last_seen_at` so a
 * forgotten "Online" decays on its own after LB_BOOSTER_PRESENCE_GRACE_MINUTES.
 *
 * Everything lives in three columns on `boosters`, so an online lookup is a plain
 * indexed condition instead of a scan over booster_session_logs.
 */

if (!defined('LB_BOOSTER_STATUS_ONLINE'))  define('LB_BOOSTER_STATUS_ONLINE',  'online');
if (!defined('LB_BOOSTER_STATUS_AWAY'))    define('LB_BOOSTER_STATUS_AWAY',    'away');
if (!defined('LB_BOOSTER_STATUS_OFFLINE')) define('LB_BOOSTER_STATUS_OFFLINE', 'offline');

// How long a manual "Online" survives without a single heartbeat from the dashboard.
if (!defined('LB_BOOSTER_PRESENCE_GRACE_MINUTES')) define('LB_BOOSTER_PRESENCE_GRACE_MINUTES', 30);

// Server-side write throttle. The browser pings every 5 minutes, this is the floor
// that protects the DB when a booster has several tabs open.
if (!defined('LB_BOOSTER_PRESENCE_TOUCH_SECONDS')) define('LB_BOOSTER_PRESENCE_TOUCH_SECONDS', 240);

if (!function_exists('lb_booster_presence_statuses')) {
    function lb_booster_presence_statuses(): array
    {
        return [
            LB_BOOSTER_STATUS_ONLINE  => ['label' => 'Online',  'sub' => 'Visible as online, you get order requests', 'dot' => 'success'],
            LB_BOOSTER_STATUS_AWAY    => ['label' => 'Away',    'sub' => 'Shown as away, no new order requests',      'dot' => 'warning'],
            LB_BOOSTER_STATUS_OFFLINE => ['label' => 'Offline', 'sub' => 'Hidden from the online list',               'dot' => 'secondary'],
        ];
    }
}

if (!function_exists('lb_booster_presence_normalize_status')) {
    function lb_booster_presence_normalize_status($status): string
    {
        $status = strtolower(trim((string) $status));

        return array_key_exists($status, lb_booster_presence_statuses())
            ? $status
            : LB_BOOSTER_STATUS_OFFLINE;
    }
}

/**
 * Adds the presence columns once and remembers the result in a marker file, so the
 * check costs a file_exists() instead of a query on every request.
 *
 * Returns false when the columns are not available (e.g. no ALTER permission). All
 * callers then fall back to the old booster_session_logs lookup instead of breaking.
 */
if (!function_exists('lb_booster_presence_last_error')) {
    /**
     * Last DB error from the presence helpers, for diagnostics. Empty when all is well.
     */
    function lb_booster_presence_last_error(?string $set = null): string
    {
        static $error = '';
        if ($set !== null) {
            $error = $set;
        }

        return $error;
    }
}

if (!function_exists('lb_booster_presence_ready')) {
    function lb_booster_presence_ready(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        $marker = rtrim(sys_get_temp_dir(), "/\\") . '/lolboost_booster_presence_schema_v1';
        if (is_file($marker)) {
            return $ready = true;
        }

        global $db;
        if (!isset($db) || !is_object($db)) {
            lb_booster_presence_last_error('no db handle');
            return $ready = false;
        }

        $wanted = [
            'availability_status'     => "ALTER TABLE boosters ADD COLUMN availability_status VARCHAR(16) NOT NULL DEFAULT 'offline'",
            'availability_changed_at' => "ALTER TABLE boosters ADD COLUMN availability_changed_at DATETIME NULL",
            'last_seen_at'            => "ALTER TABLE boosters ADD COLUMN last_seen_at DATETIME NULL",
        ];

        // Read the existing columns once. information_schema returns a normal result set,
        // unlike SHOW COLUMNS, which some drivers hand back in a shape EasyDB chokes on.
        // If that lookup is not available, fall through and let the ALTERs decide.
        $existing = [];
        try {
            $rows = $db->run(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'boosters'"
            ) ?: [];
            foreach ($rows as $row) {
                $name = (string) ($row['COLUMN_NAME'] ?? $row['column_name'] ?? '');
                if ($name !== '') {
                    $existing[strtolower($name)] = true;
                }
            }
        } catch (\Throwable $e) {
            $existing = [];
        }

        foreach ($wanted as $column => $sql) {
            if (isset($existing[strtolower($column)])) {
                continue;
            }
            try {
                // "ADD COLUMN IF NOT EXISTS" is MariaDB-only, so the check above does the work.
                $db->run($sql);
            } catch (\Throwable $e) {
                // A duplicate-column error just means another request won the race.
                $message = $e->getMessage();
                if (stripos($message, 'duplicate column') === false && stripos($message, '1060') === false) {
                    lb_booster_presence_last_error('add column ' . $column . ': ' . $message);
                    return $ready = false;
                }
            }
        }

        // Prove the columns are really usable before we trust them everywhere.
        try {
            $db->run("SELECT availability_status, availability_changed_at, last_seen_at FROM boosters LIMIT 1");
        } catch (\Throwable $e) {
            lb_booster_presence_last_error('verify columns: ' . $e->getMessage());
            return $ready = false;
        }

        try {
            $db->run("ALTER TABLE boosters ADD INDEX idx_boosters_availability (availability_status, last_seen_at)");
        } catch (\Throwable $e) {
            // Index already there — not fatal.
        }

        @file_put_contents($marker, (string) time());

        return $ready = true;
    }
}

/**
 * SQL fragment that evaluates to 1 when the booster counts as online.
 * Usable in SELECT lists and WHERE clauses.
 */
if (!function_exists('lb_booster_online_sql')) {
    function lb_booster_online_sql(string $alias = 'boosters'): string
    {
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', $alias) ?: 'boosters';

        $grace = (int) LB_BOOSTER_PRESENCE_GRACE_MINUTES;

        if (!lb_booster_presence_ready()) {
            // Legacy fallback so nothing goes dark if the migration could not run.
            // Same window as above — the dashboard only pings every 5 minutes now.
            return "(EXISTS (
                        SELECT 1 FROM booster_session_logs
                        WHERE booster_id = {$alias}.id
                          AND created_at >= (NOW() - INTERVAL {$grace} MINUTE)
                          AND device_info LIKE '%dashboard_presence%'
                    ))";
        }

        return "({$alias}.availability_status = 'online'
                 AND {$alias}.last_seen_at IS NOT NULL
                 AND {$alias}.last_seen_at >= (NOW() - INTERVAL {$grace} MINUTE))";
    }
}

/**
 * IDs of every booster currently online, as [id => true]. Cached per request.
 */
if (!function_exists('lb_booster_online_map')) {
    function lb_booster_online_map(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $map = [];

        global $db;
        if (!isset($db) || !is_object($db)) {
            return $map;
        }

        try {
            $rows = $db->run("SELECT id FROM boosters WHERE " . lb_booster_online_sql('boosters')) ?: [];
            foreach ($rows as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id > 0) {
                    $map[$id] = true;
                }
            }
        } catch (\Throwable $e) {
            $map = [];
        }

        return $map;
    }
}

if (!function_exists('lb_booster_is_online')) {
    function lb_booster_is_online($boosterId): bool
    {
        $boosterId = (int) $boosterId;
        if ($boosterId <= 0) {
            return false;
        }

        return isset(lb_booster_online_map()[$boosterId]);
    }
}

/**
 * Raw presence of one booster: the chosen status plus whether it is still fresh.
 */
if (!function_exists('lb_booster_presence_state')) {
    function lb_booster_presence_state($boosterId): array
    {
        $boosterId = (int) $boosterId;
        $state = [
            'status'       => LB_BOOSTER_STATUS_OFFLINE,
            'is_online'    => false,
            'last_seen_at' => null,
            'stale'        => false,
        ];

        if ($boosterId <= 0 || !lb_booster_presence_ready()) {
            return $state;
        }

        global $db;

        try {
            $row = $db->row(
                "SELECT availability_status, last_seen_at FROM boosters WHERE id = ? LIMIT 1",
                $boosterId
            );
        } catch (\Throwable $e) {
            return $state;
        }

        if (empty($row)) {
            return $state;
        }

        $state['status'] = lb_booster_presence_normalize_status($row['availability_status'] ?? '');
        $state['last_seen_at'] = $row['last_seen_at'] ?? null;

        $lastSeen = !empty($state['last_seen_at']) ? strtotime((string) $state['last_seen_at']) : false;
        $fresh = $lastSeen !== false && (time() - $lastSeen) <= (LB_BOOSTER_PRESENCE_GRACE_MINUTES * 60);

        $state['is_online'] = ($state['status'] === LB_BOOSTER_STATUS_ONLINE && $fresh);
        $state['stale'] = ($state['status'] === LB_BOOSTER_STATUS_ONLINE && !$fresh);

        return $state;
    }
}

/**
 * Heartbeat write. Throttled server-side, so extra tabs cost nothing.
 * Never touches the chosen status — only proves the dashboard is still open.
 */
if (!function_exists('lb_booster_presence_touch')) {
    function lb_booster_presence_touch($boosterId): bool
    {
        $boosterId = (int) $boosterId;
        if ($boosterId <= 0 || !lb_booster_presence_ready()) {
            return false;
        }

        global $db;
        $throttle = (int) LB_BOOSTER_PRESENCE_TOUCH_SECONDS;

        try {
            // Single indexed UPDATE, and the WHERE keeps it a no-op inside the throttle window.
            $db->run(
                "UPDATE boosters
                    SET last_seen_at = NOW()
                  WHERE id = ?
                    AND (last_seen_at IS NULL OR last_seen_at < (NOW() - INTERVAL {$throttle} SECOND))",
                $boosterId
            );
        } catch (\Throwable $e) {
            lb_booster_presence_last_error('touch: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}

/**
 * Manual status change from the booster dashboard.
 */
if (!function_exists('lb_booster_set_status')) {
    function lb_booster_set_status($boosterId, $status): bool
    {
        $boosterId = (int) $boosterId;
        if ($boosterId <= 0 || !lb_booster_presence_ready()) {
            return false;
        }

        $status = lb_booster_presence_normalize_status($status);

        global $db;

        try {
            lb_booster_presence_last_error('');
            // last_seen_at is refreshed here too: switching to Online must count as
            // "dashboard is open right now", otherwise the grace window starts stale.
            $db->run(
                "UPDATE boosters
                    SET availability_status = ?,
                        availability_changed_at = NOW(),
                        last_seen_at = NOW()
                  WHERE id = ?",
                $status,
                $boosterId
            );
        } catch (\Throwable $e) {
            lb_booster_presence_last_error('set status: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
