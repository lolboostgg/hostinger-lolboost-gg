<?php

// This file lives in app/tasks/, so config.php is one level up in app/core/.
// The previous path (__DIR__ . '/app/core/config.php') resolved to
// app/tasks/app/core/config.php — which does not exist, so the require fatal-ed
// before any DELETE ran and old rows were never cleaned up.
//
// config.php builds \ParagonIE\EasyDB\EasyDB, so Composer's autoloader must be
// loaded first (cron runs this standalone, without index.php doing it for us).
$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php'; // <root>/app/tasks -> <root>/vendor
if (is_file($autoload)) {
    require_once $autoload;
}
require_once dirname(__DIR__) . '/core/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $queries = [
        "DELETE FROM notifications WHERE created_at < NOW() - INTERVAL 30 DAY",
        "DELETE FROM booster_session_logs WHERE created_at < NOW() - INTERVAL 30 DAY",
        "DELETE FROM admin_session_logs WHERE created_at < NOW() - INTERVAL 30 DAY",
        "DELETE FROM client_session_logs WHERE created_at < NOW() - INTERVAL 30 DAY",
        "DELETE FROM seller_session_logs WHERE created_at < NOW() - INTERVAL 30 DAY",
    ];

    foreach ($queries as $sql) {
        $deleted = $pdo->exec($sql);
        echo $deleted . " rows deleted" . PHP_EOL;
    }

    echo "Cleanup completed." . PHP_EOL;

} catch (Throwable $e) {
    echo "Cleanup failed." . PHP_EOL;
    exit(1);
}