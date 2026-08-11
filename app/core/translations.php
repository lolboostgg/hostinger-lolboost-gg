<?php

$langDir = dirname(__DIR__, 2) . '/public/assets/core/main/translations';
$allowedLangs = array_map(
    fn($f) => pathinfo($f, PATHINFO_FILENAME),
    glob($langDir . '/*.json')
);

// master.json is a key registry and must never be selectable as a language
$allowedLangs = array_values(array_filter($allowedLangs, fn($l) => $l !== 'master'));

$currentLang = 'en';
if (isset($_GET['lang'])) {
    $langParam = $_GET['lang'];

    if ($langParam === 'en' || in_array($langParam, $allowedLangs, true)) {
        $currentLang = $langParam;
        $_SESSION['lang'] = $currentLang;
    }
} elseif (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], $allowedLangs, true)) {
    $currentLang = $_SESSION['lang'];
}

if (!defined('LANG')) {
    define('LANG', $currentLang);
}

$path = isset($_GET['path']) ? trim($_GET['path'], '/') : '';

if (!defined('REQUEST_PATH')) {
    define('REQUEST_PATH', $path);
}

$params = $_GET;
unset($params['lang'], $params['path']);
$query = http_build_query($params);

$cleanPath = REQUEST_PATH === '' ? '/' : '/' . REQUEST_PATH;
$_SERVER['REQUEST_URI'] = $cleanPath . ($query ? '?' . $query : '');
$_SERVER['QUERY_STRING'] = $query;