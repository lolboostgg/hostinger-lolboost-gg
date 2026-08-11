<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('You Have Been Poked') ?>

<?= $p("Hi {$data['username']},") ?>

<?php
$sentBy = strtolower((string)($data['sent_by'] ?? 'user'));
$senderName = (string)($data['sender_name'] ?? 'Someone');
$refType = (string)($data['ref_type'] ?? 'order');
$subjectTitle = trim((string)($data['title'] ?? ''));
$actionUrl = trim((string)($data['action_url'] ?? ''));
$orderId = (string)($data['order_id'] ?? '');

if ($actionUrl === '') {
    if ($sentBy === 'client') {
        $actionUrl = BASE_URL . '/booster-area/order/' . $orderId;
    } elseif ($sentBy === 'seller') {
        $actionUrl = BASE_URL . '/order/' . $orderId;
    } else {
        $actionUrl = BASE_URL . '/order/' . $orderId;
    }
}

$senderLabelMap = [
    'admin' => 'LoLBoost.gg Support',
    'support' => 'LoLBoost.gg Support',
    'egirl' => 'your GG-Girl',
    'e-girl' => 'your GG-Girl',
    'booster' => 'your booster',
    'seller' => 'your seller',
    'client' => 'your client',
];
$senderLabel = $senderLabelMap[$sentBy] ?? ('your ' . $sentBy);
$displaySender = in_array($sentBy, ['admin', 'support'], true)
    ? $senderLabel
    : trim($senderLabel . ($senderName !== '' ? ' ' . $senderName : ''));
?>

<?= $p("{$displaySender} has poked you regarding your {$refType} on lolboost.gg" . ($subjectTitle !== '' ? " ({$subjectTitle})" : '') . ".") ?>

<?= $p("This usually means they have a question, need your input, or are waiting for you to take action based on the chat conversation.") ?>

<?= $p("To check the chat, click the button below:") ?>

<?= $btn($actionUrl, 'Open Chat') ?>

<?= $p("If you've already replied, feel free to ignore this message.") ?>

<?= $hr() ?>

<?= $p('If you have any questions or need assistance, our team is here to help.') ?>

<?= $p('Best regards,') ?>

<?= $p('The LBGG Team') ?>
