<?php

require '/home/u319049446/domains/lolboost.gg/public_html/vendor/autoload.php';

require '/home/u319049446/domains/lolboost.gg/public_html/app/core/config.php';
require '/home/u319049446/domains/lolboost.gg/public_html/app/core/functions.php';

$storagePath = SYS_PATH . '/public/uploads/private/exchange-rate/exchange_rate.json';
$ecbUrl = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

libxml_use_internal_errors(true);

$xml = simplexml_load_file($ecbUrl);
if ($xml === false) {
    $errors = libxml_get_errors();
    libxml_clear_errors();

    $msg = 'ECB XML could not be loaded.';
    if (!empty($errors)) {
        $msg .= ' First libxml error: ' . trim($errors[0]->message);
    }
    die($msg);
}

$nodes = $xml->xpath("//*[local-name()='Cube' and @currency='USD']");
$dateNodes = $xml->xpath("//*[local-name()='Cube' and @time]");

if (!$nodes || !isset($nodes[0])) {
    die('USD rate not found in ECB XML.');
}

$exchangeRate = (float)$nodes[0]['rate'];
$rateDate = null;

if ($dateNodes && isset($dateNodes[0])) {
    $rateDate = (string)$dateNodes[0]['time'];
}

if ($exchangeRate < 0.90 || $exchangeRate > 1.40) {
    die('ECB exchange rate outside allowed range: ' . $exchangeRate);
}

$payload = [
    date('Y-m-d H:i:s') => [
        'exchange_rate' => round($exchangeRate, 6),
        'date' => $rateDate ? ($rateDate . 'T00:00:00.000Z') : null,
        'source' => 'ECB'
    ]
];

file_put_contents(
    $storagePath,
    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo 'Saved EUR->USD rate from ECB: ' . round($exchangeRate, 6);
