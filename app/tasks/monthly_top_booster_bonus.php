<?php
/**
 * Monthly Top Booster Bonus Task
 *
 * Ablage: public_html/app/tasks/monthly_top_booster_bonus.php
 * Cron Empfehlung: 0 0 1 * * /usr/bin/php /home/USER/domains/lolboost.gg/public_html/app/tasks/monthly_top_booster_bonus.php
 * Start: ab 01.07.2026 automatisch aktiv.
 * Beispiel am 01.07.2026: award_month = 2026-07, source_month = 2026-06.
 * Test: /app/tasks/monthly_top_booster_bonus.php?month=2026-07&force=1
 * Wichtig: award_month ist immer der Monat, in dem der Bonus aktiv ist.
 * source_month ist immer automatisch der Vormonat.
 */

declare(strict_types=1);

date_default_timezone_set('Europe/Berlin');

$taskStartedAt = date('Y-m-d H:i:s');

function lb_mtb_output(array $payload, int $statusCode = 200): void
{
    if (PHP_SAPI !== 'cli') {
        http_response_code($statusCode);
    }

    foreach ($payload as $key => $value) {
        if (is_array($value)) {
            echo $key . ': ' . json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } else {
            echo $key . ': ' . (string)$value . PHP_EOL;
        }
    }
}

function lb_mtb_is_valid_db($db): bool
{
    return is_object($db) && (
        method_exists($db, 'run') ||
        method_exists($db, 'query') ||
        method_exists($db, 'cell') ||
        method_exists($db, 'single')
    );
}

if (!class_exists('LbMonthlyTopBonusDbAdapter')) {
    class LbMonthlyTopBonusDbAdapter
    {
        private PDO $pdo;

        public function __construct(PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        public function run(string $sql, ...$params): array
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            if ($stmt->columnCount() <= 0) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        public function cell(string $sql, ...$params)
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        }

        public function single(string $sql, ...$params)
        {
            return $this->cell($sql, ...$params);
        }

        public function row(string $sql, ...$params)
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: false;
        }

        public function query(string $sql)
        {
            return $this->pdo->query($sql);
        }

        public function insert(string $table, array $data): void
        {
            $columns = array_keys($data);
            $quoted = array_map(static fn($c) => '`' . str_replace('`', '``', (string)$c) . '`', $columns);
            $placeholders = array_map(static fn($c) => ':' . $c, $columns);
            $sql = 'INSERT INTO `' . str_replace('`', '``', $table) . '` (' . implode(',', $quoted) . ') VALUES (' . implode(',', $placeholders) . ')';
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
        }

        public function update(string $table, array $data, array $where): void
        {
            $sets = [];
            foreach ($data as $key => $value) {
                $sets[] = '`' . str_replace('`', '``', (string)$key) . '` = :set_' . $key;
            }

            $clauses = [];
            foreach ($where as $key => $value) {
                $clauses[] = '`' . str_replace('`', '``', (string)$key) . '` = :where_' . $key;
            }

            $sql = 'UPDATE `' . str_replace('`', '``', $table) . '` SET ' . implode(', ', $sets) . ' WHERE ' . implode(' AND ', $clauses);
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':set_' . $key, $value);
            }
            foreach ($where as $key => $value) {
                $stmt->bindValue(':where_' . $key, $value);
            }
            $stmt->execute();
        }

        public function lastInsertId(): string
        {
            return $this->pdo->lastInsertId();
        }
    }
}

function lb_mtb_read_env_file(string $file): array
{
    if (!is_file($file) || !is_readable($file)) {
        return [];
    }

    $env = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '') {
            $env[$key] = $value;
        }
    }
    return $env;
}

function lb_mtb_try_create_db_adapter(array $roots): bool
{
    global $db;

    if (lb_mtb_is_valid_db($db ?? null)) {
        return true;
    }

    $env = [];
    foreach ($roots as $root) {
        foreach ([$root . '/.env', $root . '/app/.env', dirname($root) . '/.env'] as $file) {
            $env = array_merge($env, lb_mtb_read_env_file($file));
        }
    }

    $host = $env['DB_HOST'] ?? $env['DATABASE_HOST'] ?? (defined('DB_HOST') ? DB_HOST : null) ?? (defined('DATABASE_HOST') ? DATABASE_HOST : null);
    $name = $env['DB_NAME'] ?? $env['DB_DATABASE'] ?? $env['DATABASE_NAME'] ?? (defined('DB_NAME') ? DB_NAME : null) ?? (defined('DB_DATABASE') ? DB_DATABASE : null);
    $user = $env['DB_USER'] ?? $env['DB_USERNAME'] ?? $env['DATABASE_USER'] ?? (defined('DB_USER') ? DB_USER : null) ?? (defined('DB_USERNAME') ? DB_USERNAME : null);
    $pass = $env['DB_PASS'] ?? $env['DB_PASSWORD'] ?? $env['DATABASE_PASSWORD'] ?? (defined('DB_PASS') ? DB_PASS : null) ?? (defined('DB_PASSWORD') ? DB_PASSWORD : null) ?? '';
    $port = $env['DB_PORT'] ?? (defined('DB_PORT') ? DB_PORT : 3306);

    if (!$host || !$name || !$user) {
        return false;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4',
            (string)$user,
            (string)$pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        $db = new LbMonthlyTopBonusDbAdapter($pdo);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function lb_mtb_bootstrap(): array
{
    global $db;

    $taskDir = __DIR__;
    $roots = array_values(array_unique(array_filter([
        dirname($taskDir),
        dirname($taskDir, 2),
        dirname($taskDir, 3),
        $_SERVER['DOCUMENT_ROOT'] ?? null,
        defined('SYS_PATH') ? SYS_PATH : null,
        defined('ROOT_DIR') ? ROOT_DIR : null,
    ], static fn($path) => is_string($path) && $path !== '')));

    foreach ($roots as $root) {
        $root = rtrim($root, '/\\');

        $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] ?? $root;
        $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/app/tasks/monthly_top_booster_bonus.php';
        $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'lolboost.gg';
        $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'lolboost.gg';
        $_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'on';

        $candidates = [
            $root . '/app/init.php',
            $root . '/app/bootstrap.php',
            $root . '/bootstrap.php',
            $root . '/config.php',
            $root . '/app/config.php',
            $root . '/config/app.php',
            $root . '/app/config/app.php',
            $root . '/core/init.php',
            $root . '/core/main/init.php',
            $root . '/index.php',
        ];

        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }

            try {
                ob_start();
                require_once $file;
                ob_end_clean();
            } catch (Throwable $e) {
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                continue;
            }

            if (lb_mtb_is_valid_db($db ?? null) && function_exists('lb_monthly_bonus_refresh_awards')) {
                return ['ok' => true, 'roots' => $roots, 'bootstrap' => $file];
            }
        }
    }

    lb_mtb_try_create_db_adapter($roots);

    foreach ($roots as $root) {
        $root = rtrim($root, '/\\');
        foreach ([
            $root . '/core/main/functions.php',
            $root . '/app/core/main/functions.php',
            $root . '/app/functions.php',
            $root . '/functions.php',
        ] as $file) {
            if (!is_file($file)) {
                continue;
            }

            try {
                ob_start();
                require_once $file;
                ob_end_clean();
            } catch (Throwable $e) {
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                continue;
            }

            if (lb_mtb_is_valid_db($db ?? null) && function_exists('lb_monthly_bonus_refresh_awards')) {
                return ['ok' => true, 'roots' => $roots, 'bootstrap' => $file];
            }
        }
    }

    return [
        'ok' => lb_mtb_is_valid_db($db ?? null) && function_exists('lb_monthly_bonus_refresh_awards'),
        'roots' => $roots,
        'bootstrap' => null,
    ];
}


$bootstrap = lb_mtb_bootstrap();

global $db;
if (!lb_mtb_is_valid_db($db ?? null)) {
    lb_mtb_output([
        'ok' => 'false',
        'task' => 'monthly_top_booster_bonus',
        'message' => 'Bootstrap failed. DB connection missing.',
        'started_at' => $taskStartedAt,
        'finished_at' => date('Y-m-d H:i:s'),
    ], 500);
    exit(1);
}

function lb_mtb_ensure_award_table(): void
{
    global $db;
    $db->run("CREATE TABLE IF NOT EXISTS booster_monthly_bonus_awards (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        award_month CHAR(7) NOT NULL,
        source_month CHAR(7) NOT NULL,
        booster_id INT UNSIGNED NOT NULL,
        position TINYINT UNSIGNED NOT NULL,
        bonus_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        title VARCHAR(64) NOT NULL DEFAULT 'Top Booster',
        active_from DATETIME NOT NULL,
        active_until DATETIME NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_award_month_booster (award_month, booster_id),
        KEY idx_active_booster (booster_id, active_from, active_until),
        KEY idx_award_month_position (award_month, position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

function lb_mtb_calculate_top3_from_matches(string $sourceMonth): array
{
    global $db;

    $start = $sourceMonth . '-01 00:00:00';
    $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));
    $minimumGames = 10;
    $minimumTopWinrate = 50.0;

    $isDuoSql = "(COALESCE(oo.is_duo, 0) = 1)";

    $soloWinPointsSql = "CASE COALESCE(oo.start_tier, 0)
        WHEN 10 THEN 25 WHEN 9 THEN 22 WHEN 8 THEN 18 WHEN 7 THEN 13 WHEN 6 THEN 10
        WHEN 5 THEN 7 WHEN 4 THEN 5 WHEN 3 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 2 ELSE 4 END";

    $duoWinPointsSql = "CASE COALESCE(oo.start_tier, 0)
        WHEN 10 THEN 32 WHEN 9 THEN 28 WHEN 8 THEN 23 WHEN 7 THEN 17 WHEN 6 THEN 13
        WHEN 5 THEN 10 WHEN 4 THEN 8 WHEN 3 THEN 5 WHEN 2 THEN 3 WHEN 1 THEN 3 ELSE 7 END";

    $soloLossPointsSql = "CASE COALESCE(oo.start_tier, 0)
        WHEN 10 THEN 8 WHEN 9 THEN 9 WHEN 8 THEN 10 WHEN 7 THEN 12 WHEN 6 THEN 13
        WHEN 5 THEN 14 WHEN 4 THEN 15 WHEN 3 THEN 18 WHEN 2 THEN 20 WHEN 1 THEN 22 ELSE 16 END";

    $duoLossPointsSql = "CASE COALESCE(oo.start_tier, 0)
        WHEN 10 THEN 6 WHEN 9 THEN 7 WHEN 8 THEN 8 WHEN 7 THEN 10 WHEN 6 THEN 11
        WHEN 5 THEN 12 WHEN 4 THEN 13 WHEN 3 THEN 15 WHEN 2 THEN 17 WHEN 1 THEN 19 ELSE 14 END";

    $winPointsSql = "(CASE WHEN $isDuoSql THEN ($duoWinPointsSql) ELSE ($soloWinPointsSql) END)";
    $lossPointsSql = "(CASE WHEN $isDuoSql THEN ($duoLossPointsSql) ELSE ($soloLossPointsSql) END)";

    $rows = $db->run(
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

            SUM(CASE WHEN COALESCE(oo.start_tier, 0) >= 8 THEN 1 ELSE 0 END) AS master_plus_games,
            SUM(CASE WHEN COALESCE(oo.start_tier, 0) >= 7 THEN 1 ELSE 0 END) AS diamond_plus_games,

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
         JOIN orders o ON o.id = om.order_id
         LEFT JOIN order_options oo ON oo.order_id = o.id
         JOIN boosters b ON b.id = COALESCE(om.booster_id, o.booster_id)
         WHERE om.played_at >= ?
           AND om.played_at < ?
           AND COALESCE(om.is_hidden, 0) = 0
           AND COALESCE(om.is_remake, 0) = 0
           AND COALESCE(b.is_banned, 0) = 0
           AND COALESCE(b.is_egirl, 0) = 0
           AND COALESCE(oo.is_undercover_winrate, 0) = 0
           AND COALESCE(o.form_id, 0) NOT IN (15, 16, 25)
           AND COALESCE(om.queue_id, 0) <> 400
           AND COALESCE(oo.is_moderate_kda, 0) = 0
         GROUP BY b.id, b.username, b.icon, b.rank_id",
        $start,
        $end
    ) ?: [];

    $tierWinrateReward = function ($wins, $games, int $minGames, array $rewards): int {
        $games = (int)$games;
        $wins = (int)$wins;
        if ($games < $minGames || $games <= 0) return 0;
        $wr = ($wins / $games) * 100;
        foreach ($rewards as $threshold => $points) {
            if ($wr >= $threshold) return (int)$points;
        }
        return 0;
    };

    $leaderboard = [];
    foreach ($rows as $row) {
        $games = (int)($row['games'] ?? 0);
        $wins = (int)($row['wins'] ?? 0);
        $winrate = $games > 0 ? round(($wins / $games) * 100, 2) : 0.0;
        $duoGames = (int)($row['duo_games'] ?? 0);
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

        $activityBonus = $games >= 150 ? 40 : ($games >= 100 ? 25 : ($games >= 50 ? 10 : 0));
        $diamondPlusGames = (int)($row['diamond_plus_games'] ?? 0);
        $masterPlusGames = (int)($row['master_plus_games'] ?? 0);
        $highEloBonus = 0;
        if ($diamondPlusGames >= 40) $highEloBonus += 50;
        elseif ($diamondPlusGames >= 20) $highEloBonus += 20;
        if ($masterPlusGames >= 15) $highEloBonus += 60;

        $duoRatio = $games > 0 ? ($duoGames / $games) * 100 : 0;
        $duoRatioBonus = $duoRatio >= 40 ? 25 : ($duoRatio >= 20 ? 10 : 0);

        $rawScore = $winPoints - $lossPenalty + $winrateRewards + $activityBonus + $highEloBonus + $duoRatioBonus;
        $score = max(0, $rawScore);
        if ($winrate < $minimumTopWinrate) $score = 0;
        if ($winrate >= 50 && $winrate < 60) $score *= max(0, ($winrate - 50) / 10);

        $qualified = $games >= $minimumGames && $winrate >= $minimumTopWinrate;
        $leaderboard[] = [
            'booster_id' => (int)($row['booster_id'] ?? 0),
            'username' => (string)($row['username'] ?? 'Booster'),
            'games' => $games,
            'winrate' => $winrate,
            'score' => round($score, 2),
            'qualified' => $qualified,
        ];
    }

    usort($leaderboard, function ($a, $b) {
        if ($a['qualified'] !== $b['qualified']) return $a['qualified'] ? -1 : 1;
        if ($a['score'] == $b['score']) {
            if ($a['winrate'] == $b['winrate']) return $b['games'] <=> $a['games'];
            return $b['winrate'] <=> $a['winrate'];
        }
        return $b['score'] <=> $a['score'];
    });

    $out = [];
    foreach ($leaderboard as $row) {
        if (count($out) >= 3) break;
        if (empty($row['qualified']) || (int)$row['booster_id'] <= 0) continue;
        $position = count($out) + 1;
        $out[] = [
            'booster_id' => (int)$row['booster_id'],
            'position' => $position,
            'bonus_percent' => 5.00,
            'title' => 'Top Booster #' . $position,
            'score' => (float)$row['score'],
            'winrate' => (float)$row['winrate'],
            'games' => (int)$row['games'],
            'username' => (string)$row['username'],
        ];
    }

    return $out;
}

function lb_mtb_refresh_awards_previous_month(string $awardMonth, bool $force = false): array
{
    global $db;
    lb_mtb_ensure_award_table();

    $awardStart = $awardMonth . '-01 00:00:00';
    $awardEnd = date('Y-m-d H:i:s', strtotime($awardStart . ' +1 month'));
    $sourceMonth = date('Y-m', strtotime($awardStart . ' -1 month'));

    $existing = (int)($db->cell('SELECT COUNT(*) FROM booster_monthly_bonus_awards WHERE award_month = ?', $awardMonth) ?: 0);
    if ($existing > 0 && !$force) {
        return $db->run('SELECT * FROM booster_monthly_bonus_awards WHERE award_month = ? ORDER BY position ASC', $awardMonth) ?: [];
    }

    if ($force && $existing > 0) {
        $db->run('DELETE FROM booster_monthly_bonus_awards WHERE award_month = ?', $awardMonth);
    }

    $topRows = lb_mtb_calculate_top3_from_matches($sourceMonth);

    foreach ($topRows as $row) {
        $db->run(
            'INSERT INTO booster_monthly_bonus_awards
                (award_month, source_month, booster_id, position, bonus_percent, title, active_from, active_until)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            $awardMonth,
            $sourceMonth,
            (int)$row['booster_id'],
            (int)$row['position'],
            number_format((float)$row['bonus_percent'], 2, '.', ''),
            (string)$row['title'],
            $awardStart,
            $awardEnd
        );
    }

    return $db->run('SELECT * FROM booster_monthly_bonus_awards WHERE award_month = ? ORDER BY position ASC', $awardMonth) ?: [];
}

$argvMonth = PHP_SAPI === 'cli' ? ($argv[1] ?? null) : ($_GET['month'] ?? null);
$force = PHP_SAPI === 'cli' ? in_array('--force', $argv ?? [], true) : isset($_GET['force']);
$startFromMonth = '2026-07';

if (is_string($argvMonth) && preg_match('/^\d{4}-\d{2}$/', $argvMonth)) {
    $awardMonth = $argvMonth;
} else {
    $awardMonth = date('Y-m');
}

$sourceMonthForOutput = date('Y-m', strtotime($awardMonth . '-01 -1 month'));

if ($awardMonth < $startFromMonth && !$force) {
    lb_mtb_output([
        'ok' => 'true',
        'task' => 'monthly_top_booster_bonus',
        'status' => 'skipped',
        'message' => 'Task starts from 2026-07. No awards created before this month.',
        'award_month' => $awardMonth,
        'source_month' => $sourceMonthForOutput,
        'started_at' => $taskStartedAt,
        'finished_at' => date('Y-m-d H:i:s'),
    ]);
    exit(0);
}

try {
    $awards = lb_mtb_refresh_awards_previous_month($awardMonth, $force);

    $cleanAwards = [];
    foreach (($awards ?: []) as $award) {
        $cleanAwards[] = [
            'position' => (int)($award['position'] ?? 0),
            'booster_id' => (int)($award['booster_id'] ?? 0),
            'bonus_percent' => (float)($award['bonus_percent'] ?? 0),
            'title' => (string)($award['title'] ?? ''),
            'award_month' => (string)($award['award_month'] ?? $awardMonth),
            'source_month' => (string)($award['source_month'] ?? $sourceMonthForOutput),
            'active_from' => (string)($award['active_from'] ?? ''),
            'active_until' => (string)($award['active_until'] ?? ''),
        ];
    }

    lb_mtb_output([
        'ok' => 'true',
        'task' => 'monthly_top_booster_bonus',
        'award_month' => $awardMonth,
        'source_month' => $sourceMonthForOutput,
        'awards_count' => count($cleanAwards),
        'awards' => $cleanAwards,
        'force' => $force ? 1 : 0,
        'bootstrap' => (string)($bootstrap['bootstrap'] ?? ''),
        'started_at' => $taskStartedAt,
        'finished_at' => date('Y-m-d H:i:s'),
    ]);
    exit(0);
} catch (Throwable $e) {
    lb_mtb_output([
        'ok' => 'false',
        'task' => 'monthly_top_booster_bonus',
        'message' => $e->getMessage(),
        'award_month' => $awardMonth,
        'source_month' => $sourceMonthForOutput,
        'bootstrap' => (string)($bootstrap['bootstrap'] ?? ''),
        'started_at' => $taskStartedAt,
        'finished_at' => date('Y-m-d H:i:s'),
    ], 500);
    exit(1);
}
