<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/functions.php';
require __DIR__ . '/../core/view.php';

try {
    global $db;

    $query = "UPDATE boosters SET drop_tokens = 2";
    $db->run($query);
} catch (Exception $e) {
}