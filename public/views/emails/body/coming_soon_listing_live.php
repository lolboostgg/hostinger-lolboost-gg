<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?php
$gameName = htmlspecialchars((string)($data['game_name'] ?? 'Your requested game'), ENT_QUOTES, 'UTF-8');
$serviceName = htmlspecialchars((string)($data['service_name'] ?? 'Listings'), ENT_QUOTES, 'UTF-8');
$listingUrl = htmlspecialchars((string)($data['listing_url'] ?? BASE_URL), ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars((string)($data['username'] ?? 'there'), ENT_QUOTES, 'UTF-8');
?>

<?= $title($gameName . ' is now available!') ?>

<?= $p('Hi ' . $username . ',') ?>

<?= $p('Good news: <strong>' . $serviceName . '</strong> listings for <strong>' . $gameName . '</strong> are now live on LoLBoost.gg.') ?>

<?= $btn($listingUrl, 'View Listings') ?>

<?= $hr() ?>

<?= $p('You received this email because you asked us to notify you when this game and service became available.') ?>
