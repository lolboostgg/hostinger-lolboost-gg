<?php
/**
 * Complete delivered digital-good orders after 24 hours without buyer action.
 *
 * Recommended cron (every five minutes):
 * /usr/bin/php /home/u319049446/domains/lolboost.gg/public_html/app/tasks/digital_good_auto_complete.php
 */

date_default_timezone_set('Europe/Berlin');

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';
require_once $root . '/app/core/config.php';
require_once $root . '/app/core/functions.php';

try {
    $completed = dg_auto_complete_overdue_purchases(1000);
    echo 'Completed ' . $completed . ' digital-good order(s).' . PHP_EOL;
} catch (Throwable $e) {
    error_log('digital_good_auto_complete task failed: ' . $e->getMessage());
    echo 'Digital-good auto completion failed.' . PHP_EOL;
    exit(1);
}
