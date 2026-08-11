<?php
$versionData = file_get_contents("https://ddragon.leagueoflegends.com/api/versions.json");
$latestVersion = json_decode($versionData, true)[0];

$championListData = file_get_contents("https://ddragon.leagueoflegends.com/cdn/$latestVersion/data/en_US/champion.json");
$championList = json_decode($championListData, true)['data'];

$skins = [];

foreach ($championList as $championKey => $championInfo) {
    $champName = $championInfo['id'];
    $champDetailUrl = "https://ddragon.leagueoflegends.com/cdn/$latestVersion/data/en_US/champion/{$champName}.json";
    $champDetailJson = @file_get_contents($champDetailUrl);

    if ($champDetailJson === false) {
        continue;
    }

    $champDetail = json_decode($champDetailJson, true);
    foreach ($champDetail['data'][$champName]['skins'] as $skin) {
        if ($skin['name'] === "default") {
            continue;
        }

        $skinName = $skin['name'];

        $skins[] = [
            'value' => "{$champName}_{$skin['num']}",
            'label' => "{$skinName}"
        ];
    }
}

file_put_contents(__DIR__ . "/../../public/assets/lol_skins.json", json_encode($skins, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));