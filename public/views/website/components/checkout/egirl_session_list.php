<?php
$order   = $data['order'] ?? [];
$invoice = $data['invoice'] ?? [];
$type_labels = [
    'hourly'     => 'Hourly',
    'per_game'   => 'Per Game',
    'rank_boost' => 'Rank Boost',
    'coaching'   => 'Coaching',
    'just_chat'  => 'Just Chat',
    'custom'     => 'Custom',
];
$bookingDetails = [];
$freeNotes = [];
foreach (preg_split('/\R+/', trim((string)($order['client_notes'] ?? ''))) as $line) {
    $line = trim($line);
    if ($line === '') continue;
    if (preg_match('/^(Mode|Server|Rank|Region)\s*:\s*(.+)$/i', $line, $match)) {
        $bookingDetails[strtolower($match[1])] = trim($match[2]);
    } else {
        $freeNotes[] = $line;
    }
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Session with</span>
    <span class="fw-500 text-primary"><?= htmlspecialchars($order['egirl_username'] ?? 'E-Girl') ?></span>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Service</span>
    <span class="fw-500 text-primary"><?= htmlspecialchars($order['service_title'] ?? 'Session Booking') ?></span>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Type</span>
    <span class="fw-500 text-primary"><?= htmlspecialchars($type_labels[$order['service_type'] ?? ''] ?? ($order['service_type'] ?? 'Session')) ?></span>
</div>
<?php if (!empty($order['game'])): ?>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Game</span>
    <span class="fw-500 text-primary"><?= strtoupper(htmlspecialchars($order['game'])) ?></span>
</div>
<?php endif; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Duration</span>
    <span class="fw-500 text-primary"><?= (int)($order['unit_value'] ?? 1) . ' ' . htmlspecialchars($order['unit_type'] ?? 'hours') ?></span>
</div>
<?php if (!empty($bookingDetails['server']) || !empty($bookingDetails['region'])): ?>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Server</span>
    <span class="fw-500 text-primary"><?= htmlspecialchars($bookingDetails['server'] ?? $bookingDetails['region']) ?></span>
</div>
<?php endif; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Voice Chat</span>
    <span class="fw-500 text-primary">Included</span>
</div>
<?php if (!empty($freeNotes)): ?>
<div>
    <div class="fw-600 d-block mb-2">
        Notes for the session
        <span class="fw-500 fs-xs text-secondary-dark ms-2">(saved with this booking)</span>
    </div>
    <div class="rounded-3 p-3" style="border:1px solid rgba(255,255,255,.08);background:rgba(6,6,18,.45)">
        <?php foreach ($freeNotes as $noteLine):
            $noteParts = array_map('trim', explode(':', $noteLine, 2));
        ?>
            <div class="d-flex justify-content-between gap-3 py-1">
                <?php if (count($noteParts) === 2): ?>
                    <span class="text-secondary"><?= htmlspecialchars($noteParts[0]) ?></span>
                    <span class="fw-500 text-end"><?= htmlspecialchars($noteParts[1]) ?></span>
                <?php else: ?>
                    <span style="white-space:pre-wrap;overflow-wrap:anywhere"><?= htmlspecialchars($noteLine) ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
