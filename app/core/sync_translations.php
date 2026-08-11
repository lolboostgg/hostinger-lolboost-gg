<?php
// One-time sync helper: ensure every <lang>.json has all keys from master.json.
// Run: php sync_translations.php

$translationsDir = __DIR__ . '/../../public/assets/core/main/translations';
$masterFile = $translationsDir . '/master.json';

$raw = is_file($masterFile) ? json_decode(file_get_contents($masterFile), true) : null;
$keys = (is_array($raw) && isset($raw['keys']) && is_array($raw['keys'])) ? $raw['keys'] : [];

if (empty($keys)) {
    fwrite(STDERR, "No keys found in master.json\n");
    exit(1);
}

$langFiles = glob($translationsDir . '/*.json') ?: [];
foreach ($langFiles as $filePath) {
    $base = pathinfo($filePath, PATHINFO_FILENAME);
    if ($base === 'master') continue;

    $json = json_decode(file_get_contents($filePath), true);
    if (!is_array($json)) $json = [];
    if (!isset($json['translations']) || !is_array($json['translations'])) {
        $json['translations'] = [];
    }

    $changed = false;
    foreach ($keys as $k) {
        if (!array_key_exists($k, $json['translations'])) {
            $json['translations'][$k] = $k; // placeholder
            $changed = true;
        }
    }

    if ($changed) {
        file_put_contents(
            $filePath,
            json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
        echo "Updated: {$base}.json\n";
    } else {
        echo "OK: {$base}.json\n";
    }
}

echo "Done.\n";
