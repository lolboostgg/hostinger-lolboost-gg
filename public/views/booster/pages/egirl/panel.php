<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>

<style>
.eg-panel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}
.eg-booking-card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 100%;
    background: var(--bs-card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-card-border-radius, 1rem);
    box-shadow: var(--bs-card-box-shadow, none);
    overflow: hidden;
    transition: transform .12s ease, border-color .12s ease, box-shadow .12s ease;
}
.eg-booking-card:hover {
    transform: translateY(-2px);
    border-color: rgba(255,255,255,.14);
    box-shadow: 0 12px 30px rgba(0,0,0,.18);
}
.eg-booking-card-body { padding: 1rem 1rem .875rem; flex: 1; }
.eg-booking-card-footer {
    padding: .875rem 1rem;
    border-top: 1px solid var(--bs-border-color);
    background: rgba(255,255,255,.02);
    display: flex;
    gap: .625rem;
}
.eg-booking-client { display:flex; align-items:center; gap:.75rem; margin-bottom:.9rem; }
.eg-booking-avatar {
    width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
    display:flex; align-items:center; justify-content:center;
    font-size:.9rem; font-weight:800; color:#fff;
    background: rgba(255,255,255,.06);
    border: 1px solid var(--bs-border-color);
    overflow:hidden;
}
.eg-booking-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.eg-booking-client-name { font-weight: 700; font-size: .95rem; color: #fff; line-height:1.25; }
.eg-booking-client-time { font-size: .72rem; color: rgba(255,255,255,.6); }
.eg-booking-service { font-size: 1rem; font-weight: 700; color:#fff; line-height:1.35; margin-bottom:.45rem; }
.eg-booking-meta-label { font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:rgba(255,255,255,.58); margin-bottom:.15rem; }
.eg-game-badge {
    display:inline-flex; align-items:center; gap:.3rem; margin-bottom:.65rem;
    padding:.22rem .6rem; border-radius:999px; font-size:.72rem; font-weight:700;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--bs-border-color);
    color: rgba(255,255,255,.82);
}
.eg-game-badge img {
    width: 16px;
    height: 16px;
    object-fit: contain;
    border-radius: 50%;
}
.eg-details-box {
    background: rgba(255,255,255,.03);
    border: 1px solid var(--bs-border-color);
    border-radius: .75rem;
    padding: .7rem .8rem;
    margin-bottom: .75rem;
}
.eg-detail-row {
    display:grid;
    grid-template-columns: 86px 1fr;
    gap:.6rem;
    padding:.25rem 0;
    font-size:.8rem;
    line-height:1.35;
}
.eg-detail-row + .eg-detail-row { border-top:1px solid rgba(255,255,255,.045); }
.eg-detail-label { color:rgba(255,255,255,.55); font-weight:800; text-transform:uppercase; letter-spacing:.04em; font-size:.68rem; }
.eg-detail-value { color:rgba(255,255,255,.88); font-weight:650; word-break:break-word; }
.eg-notes-box {
    background: rgba(255,255,255,.03);
    border: 1px solid var(--bs-border-color);
    border-radius: .75rem;
    padding: .6rem .75rem;
    font-size: .78rem;
    color: rgba(255,255,255,.68);
    line-height: 1.5;
    margin-bottom: .75rem;
}
.eg-notes-box i { color: rgba(255,255,255,.72); margin-right:.3rem; }
.eg-booking-price { font-size: 1.45rem; font-weight: 800; color: var(--bs-success); line-height:1.15; }
.eg-feature-chips { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.65rem; }
.eg-chip {
    display:inline-flex; align-items:center; gap:.3rem;
    padding:.22rem .6rem; border-radius:999px; font-size:.72rem; font-weight:700;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--bs-border-color);
    color: rgba(255,255,255,.78);
}
.eg-btn-accept {
    flex:1; padding:.68rem 0; border-radius:.75rem; font-weight:700; font-size:.88rem;
    cursor:pointer; border:1px solid transparent; transition:opacity .15s ease, transform .1s ease;
    background: var(--bs-primary); color:#fff; display:flex; align-items:center; justify-content:center; gap:.4rem;
}
.eg-btn-accept:hover { opacity:.92; }
.eg-btn-accept:disabled { opacity:.5; cursor:not-allowed; }
.eg-panel-empty {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:4rem 2rem; text-align:center; color:rgba(255,255,255,.6);
}
.eg-panel-empty-icon {
    width:72px; height:72px; border-radius:1rem; background: rgba(255,255,255,.03);
    border:1px solid var(--bs-border-color); display:flex; align-items:center; justify-content:center;
    font-size:1.8rem; color: rgba(255,255,255,.4); margin-bottom:1rem;
}
.eg-panel-strip {
    display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;
    padding:.85rem 1.25rem; border-top:1px solid var(--bs-border-color); border-bottom:1px solid var(--bs-border-color);
    background: rgba(255,255,255,.02);
}
.eg-panel-strip-item { display:flex; align-items:center; gap:.5rem; }
.eg-panel-strip-num { font-size:1.05rem; font-weight:800; color:#fff; }
.eg-panel-strip-lbl { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,.6); }
.eg-panel-strip-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
</style>

<?php
$open_orders = $open_orders ?? [];
$detectAdminRole = static function (): bool {
    $candidates = [];
    foreach ([
        $GLOBALS['BOOSTER_DATA']['role'] ?? null,
        $GLOBALS['BOOSTER_DATA']['type'] ?? null,
        $GLOBALS['user']['role'] ?? null,
        $GLOBALS['account']['role'] ?? null,
        $GLOBALS['meta']['role'] ?? null,
        $_SESSION['role'] ?? null,
        $_SESSION['user_role'] ?? null,
    ] as $candidate) {
        if ($candidate !== null && $candidate !== '') {
            $candidates[] = strtolower((string)$candidate);
        }
    }
    foreach ($candidates as $candidate) {
        if (in_array($candidate, ['admin', 'administrator', 'superadmin', 'super_admin'], true)) {
            return true;
        }
    }
    return false;
};
$isAdminViewer = $detectAdminRole();

if (!$isAdminViewer) {
    $open_orders = array_values(array_filter($open_orders, static function(array $order): bool {
        return strtoupper((string)($order['status'] ?? '')) !== 'UNPAID';
    }));
}

$resolveEgirlEarningCents = static function(array $order): int {
    // Always calculate earnings in EUR cents (payout currency)
    $priceEurCents = (int)($order['price_eur'] ?? 0);
    if ($priceEurCents <= 0) {
        $priceEurCents = (int)($order['price_cents'] ?? $order['price'] ?? 0);
        if (strtoupper((string)($order['currency'] ?? 'EUR')) === 'USD') {
            $rate = (float)(function_exists('get_exchange_rate') ? get_exchange_rate() : 0);
            if ($rate > 0) $priceEurCents = (int)round($priceEurCents / $rate);
        }
    }
    $cutPct = $order['egirl_cut'] ?? null;
    $cutPct = ($cutPct === null || $cutPct === '') ? 60.0 : (float)$cutPct;
    return (int) round($priceEurCents * max(0, $cutPct) / 100);
};
$formatMoney = static function(int $amountCents, string $currency = 'EUR'): string {
    $currency = strtoupper($currency ?: 'EUR');
    $symbol = function_exists('util_currency_symbol') ? util_currency_symbol($currency) : ($currency === 'USD' ? '$' : '€');
    $amount = function_exists('util_format_price_display') ? util_format_price_display($amountCents) : number_format($amountCents / 100, 2);
    return $symbol . $amount;
};
$decodeEgirlAssignment = static function(array $order): array {
    $data = [];
    if (!empty($order['assignment'])) {
        if (is_array($order['assignment'])) {
            $data = $order['assignment'];
        } elseif (is_string($order['assignment'])) {
            $decoded = json_decode($order['assignment'], true);
            if (is_array($decoded)) $data = $decoded;
        }
    }
    if (empty($data) && !empty($order['client_notes']) && is_string($order['client_notes'])) {
        if (preg_match('/DATA:\s*(\{.*\})/s', $order['client_notes'], $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) $data = $decoded;
        }
    }
    return $data;
};
$cleanClientNotes = static function(array $order): string {
    $notes = trim((string)($order['notes'] ?? $order['client_note'] ?? ''));
    if ($notes !== '') return $notes;
    $raw = trim((string)($order['client_notes'] ?? ''));
    if ($raw === '') return '';
    $raw = preg_replace('/\s*DATA:\s*\{.*$/s', '', $raw);
    $raw = preg_replace('/^LoL GGirl Booking\s*/i', '', (string)$raw);
    $raw = preg_replace('/Mode:.*$/s', '', (string)$raw);
    return trim((string)$raw);
};
$pickOrderValue = static function(array $order, array $data, array $keys, string $fallback = ''): string {
    foreach ($keys as $key) {
        if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') return (string)$data[$key];
        if (array_key_exists($key, $order) && $order[$key] !== null && $order[$key] !== '') return (string)$order[$key];
    }
    return $fallback;
};

$formatEgirlOrderTitle = static function(string $server, string $mode, string $amountText): string {
    $server = strtoupper(trim($server));
    $mode = trim(preg_replace('/\s+/', ' ', $mode));
    $mode = preg_replace('/\s+Game$/i', '', $mode);
    $amountText = trim(preg_replace('/\s+/', ' ', $amountText));
    $amountText = preg_replace_callback('/\b(game|games)\b/i', static function($m) { return ucfirst(strtolower($m[1])); }, $amountText);
    $parts = array_values(array_filter([$server !== '' ? $server : null, $mode !== '' ? $mode : null, $amountText !== '' ? $amountText : null]));
    return implode(' - ', $parts);
};

$egirlGameMeta = static function($game): array {
    $key = strtolower(trim((string)$game));
    if (!in_array($key, ['lol', 'val', 'tft'], true)) $key = 'lol';
    $icons = [
        'lol' => ['league-of-legends.png', 'League of Legends'],
        'val' => ['valorant.png', 'Valorant'],
        'tft' => ['teamfight-tactics.png', 'Teamfight Tactics'],
    ];
    return ['key' => $key, 'icon' => $icons[$key][0], 'label' => $icons[$key][1]];
};

$earningBuckets = [];
foreach ($open_orders as $openOrder) {
    $bucketCurrency = strtoupper((string)($openOrder['currency'] ?? 'EUR'));
    if (!isset($earningBuckets[$bucketCurrency])) {
        $earningBuckets[$bucketCurrency] = 0;
    }
    $earningBuckets[$bucketCurrency] += $resolveEgirlEarningCents($openOrder);
}
?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h4 class="card-header-title mb-0">
            <i class="fa-duotone fa-rocket-launch me-2" style="color:var(--bs-primary)"></i>Booking Panel
        </h4>
        <?php if (!empty($open_orders)): ?>
        <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .75rem;border-radius:50rem;font-size:.78rem;font-weight:700;background:rgba(236,72,153,.12);border:1px solid rgba(236,72,153,.28);color:#f472b6;">
            <span style="width:7px;height:7px;border-radius:50%;background:#f472b6;display:inline-block"></span>
            <?= count($open_orders) ?> new <?= count($open_orders) === 1 ? 'booking' : 'bookings' ?> waiting
        </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($open_orders)): ?>
    <!-- Stats strip -->
    <div class="eg-panel-strip">
        <div class="eg-panel-strip-item">
            <span class="eg-panel-strip-dot" style="background:var(--bs-primary)"></span>
            <span class="eg-panel-strip-num"><?= count($open_orders) ?></span>
            <span class="eg-panel-strip-lbl">Pending</span>
        </div>
        <div class="eg-panel-strip-item">
            <span class="eg-panel-strip-dot" style="background:#22c55e"></span>
            <span class="eg-panel-strip-num">
                <?php $bucketTexts = []; foreach ($earningBuckets as $bucketCurrency => $bucketAmount) { $bucketTexts[] = $formatMoney((int)$bucketAmount, $bucketCurrency); } echo htmlspecialchars(implode(' / ', $bucketTexts)); ?>
            </span>
            <span class="eg-panel-strip-lbl">Potential Earnings</span>
        </div>
    </div>

    <!-- Cards grid -->
    <div class="card-body">
        <div class="eg-panel-grid" id="egPanelGrid">
            <?php foreach ($open_orders as $o):
                $clientInitial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $o['client_username'] ?? 'C') ?: 'C', 0, 1));
                $orderCurrency = strtoupper((string)($o['currency'] ?? 'EUR'));
                $priceCents    = (int)($o['price'] ?? $o['price_cents'] ?? 0);
                $earningCents  = $resolveEgirlEarningCents($o);
                $hasVoice      = !empty($o['has_voice_chat']) || !empty($o['voice_chat']);
                $hasCam        = !empty($o['has_webcam']) || !empty($o['webcam']);
                $created       = !empty($o['created_at']) ? date('M j, Y · H:i', strtotime($o['created_at'])) : '—';
                $assignment    = $decodeEgirlAssignment($o);
                $modeTitle     = $pickOrderValue($o, $assignment, ['mode_title', 'mode', 'service_title'], 'Normal Draft Game');
                $serverText    = strtoupper($pickOrderValue($o, $assignment, ['server'], '—'));
                $rankText      = $pickOrderValue($o, $assignment, ['rank_label', 'rank'], '—');
                $amountValue   = (int)$pickOrderValue($o, $assignment, ['amount'], '1');
                $amountText    = $amountValue . ' ' . ($amountValue === 1 ? 'game' : 'games');
                $panelNotes    = $cleanClientNotes($o);
                $orderTitle    = $formatEgirlOrderTitle($serverText, $modeTitle, $amountText);
    $gameMeta = $egirlGameMeta($o['game'] ?? 'lol');
            ?>
            <div class="eg-booking-card" id="panel-card-<?= $o['id'] ?>">
                <div class="eg-booking-card-body">
                    <!-- Client -->
                    <div class="eg-booking-client">
                        <div class="eg-booking-avatar">
                            <?php if (!empty($o['client_icon'])): ?>
                                <img src="<?= htmlspecialchars($o['client_icon']) ?>" alt="">
                            <?php else: ?>
                                <?= $clientInitial ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="eg-booking-client-name">
                                <?= htmlspecialchars($o['client_username'] ?? '—') ?>
                            </div>
                            <div class="eg-booking-client-time">
                                <i class="fa-solid fa-clock" style="font-size:.65rem;margin-right:.25rem"></i><?= $created ?>
                            </div>
                        </div>
                    </div>

                    <!-- Service -->
                    <div class="eg-booking-service">
                        <?= htmlspecialchars($orderTitle) ?>
                    </div>

                    <!-- Game -->
                    <?php if (!empty($o['game'])): ?>
                    <span class="eg-game-badge">
                        <img src="<?= ASSET_URL ?>/website/images/icons/<?= htmlspecialchars($gameMeta['icon'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($gameMeta['label'], ENT_QUOTES) ?>">
                        <?= htmlspecialchars($gameMeta['label'], ENT_QUOTES) ?>
                    </span>
                    <?php endif; ?>

                    <!-- Booking details -->
                    <div class="eg-details-box">
                        <div class="eg-detail-row">
                            <div class="eg-detail-label">Server</div>
                            <div class="eg-detail-value"><?= htmlspecialchars($serverText) ?></div>
                        </div>
                        <div class="eg-detail-row">
                            <div class="eg-detail-label">Rank</div>
                            <div class="eg-detail-value"><?= htmlspecialchars($rankText) ?></div>
                        </div>
                        <div class="eg-detail-row">
                            <div class="eg-detail-label">Amount</div>
                            <div class="eg-detail-value"><?= htmlspecialchars($amountText) ?></div>
                        </div>
                    </div>

                    <?php if ($panelNotes !== ''): ?>
                    <div class="eg-notes-box">
                        <i class="fa-solid fa-comment-lines"></i><?= htmlspecialchars($panelNotes) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Earning + features -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div style="font-size:.68rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--eg-muted);margin-bottom:.18rem">Earning</div>
                            <div class="eg-booking-price"><?= htmlspecialchars($formatMoney($earningCents, 'EUR')) ?></div>
                        </div>
                        <div class="eg-feature-chips">
                            <?php if ($hasVoice): ?>
                            <span class="eg-chip"><i class="fa-solid fa-microphone" style="font-size:.65rem"></i>Voice</span>
                            <?php endif; ?>
                            <?php if ($hasCam): ?>
                            <span class="eg-chip"><i class="fa-solid fa-video" style="font-size:.65rem"></i>Cam</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Card footer actions -->
                <div class="eg-booking-card-footer">
                    <button class="eg-btn-accept js-accept-booking" data-id="<?= $o['id'] ?>">
                        <i class="fa-duotone fa-circle-check"></i> Accept Booking
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php else: ?>
    <!-- Empty state -->
    <div class="eg-panel-empty">
        <div class="eg-panel-empty-icon">
            <i class="fa-duotone fa-rocket-launch"></i>
        </div>
        <h5 style="color:var(--eg-text);margin-bottom:.5rem">No new bookings</h5>
        <p style="max-width:360px;margin:0">
            When clients pay for your services, their bookings will show up here for you to accept.
        </p>
    </div>
    <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    const AJAX = '<?= AJAX_URL ?>';

    document.querySelectorAll('.js-accept-booking').forEach(function(btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Accepting…';

            $.post(AJAX, { action: 'egirl_accept_order', order_id: id }, function (res) {
                if (!toast_from_response(res, 'Booking accepted.')) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-duotone fa-circle-check"></i> Accept Booking';
                    return;
                }
                // Let the toast show before jumping into the order.
                setTimeout(function () { window.location.href = '<?= BSTR_URL ?>/egirl-order/' + id; }, 700);
            }).fail(function () {
                toast_request_failed('Could not accept the booking.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-duotone fa-circle-check"></i> Accept Booking';
            });
        });
    });
})();
</script>
<?= $this->end() ?>
