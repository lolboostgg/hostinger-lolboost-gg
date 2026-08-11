<?php
/**
 * Birthday discount cron task with debug output.
 * Cron: 0 9 * * * php domains/lolboost.gg/public_html/app/tasks/birthday_discount_sender.php
 */

date_default_timezone_set('Europe/Berlin');
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=UTF-8');
}

$taskStartedAt = microtime(true);
$_ns_root = dirname(dirname(dirname(__FILE__))); // /public_html

require $_ns_root . '/vendor/autoload.php';
require $_ns_root . '/app/core/config.php';
require $_ns_root . '/app/core/functions.php';

if (!function_exists('cron_send_birthday_discount_emails')) {
    echo "Birthday discount cron failed: cron_send_birthday_discount_emails() not found.\n";
    exit(1);
}

try {
    $result = cron_send_birthday_discount_emails(500);
    $seconds = round(microtime(true) - $taskStartedAt, 3);

    echo "Birthday discount cron completed.\n";
    echo "Matched: " . (int)($result['matched'] ?? 0) . "\n";
    echo "Processed: " . (int)($result['processed'] ?? 0) . "\n";
    echo "Runtime: {$seconds}s\n";

    if (!empty($result['created'])) {
        echo "Created:\n";
        foreach ($result['created'] as $row) {
            echo "- client_id={$row['client_id']} discount_id={$row['discount_id']} notification_id={$row['notification_id']} code={$row['code']} expires_at={$row['expires_at']}\n";
        }
    }

    if (!empty($result['errors'])) {
        echo "Errors:\n";
        foreach ($result['errors'] as $error) {
            echo "- {$error}\n";
        }
    }

    if (!empty($result['debug'])) {
        echo "Debug:\n";
        foreach ($result['debug'] as $line) {
            echo "- {$line}\n";
        }
    }

    if ((int)($result['processed'] ?? 0) === 0) {
        echo "No notification was created. Use the Debug/Errors lines above to see why.\n";
    }

    exit(0);
} catch (Throwable $e) {
    echo "Birthday discount cron failed: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
