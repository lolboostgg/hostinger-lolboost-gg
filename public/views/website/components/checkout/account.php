<?php
$esc = static function ($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$invoiceDescription = trim((string)($data['_invoice_description'] ?? ($invoice['description'] ?? '')));
$accountTitle = trim((string)($data['title'] ?? $data['name'] ?? $data['account_title'] ?? ''));

if ($accountTitle === '' && $invoiceDescription !== '') {
    $accountTitle = preg_replace('/^[A-Z0-9_-]+\s*-\s*/i', '', $invoiceDescription);
    $accountTitle = preg_replace('/\s+(?:game\s+)?account$/i', '', (string)$accountTitle);
    $accountTitle = trim((string)$accountTitle);
}
if ($accountTitle === '') {
    $accountTitle = 'Game Account';
}

$gameSlug = strtolower(trim((string)($data['checkout_game_slug'] ?? $data['game_slug'] ?? $data['game'] ?? '')));
$gameName = trim((string)($data['checkout_game_name'] ?? $data['game_name'] ?? $data['game_title'] ?? ''));
$gameIcon = trim((string)($data['checkout_game_icon'] ?? $data['game_icon'] ?? ''));

$knownGames = [
    'lol' => 'League of Legends',
    'league-of-legends' => 'League of Legends',
    'valorant' => 'Valorant',
    'teamfight-tactics' => 'Teamfight Tactics',
    'tft' => 'Teamfight Tactics',
    'marvel-rivals' => 'Marvel Rivals',
    'mobile-legends' => 'Mobile Legends',
    'rocket-league' => 'Rocket League',
    'overwatch-2' => 'Overwatch 2',
    'fortnite' => 'Fortnite',
    'call-of-duty' => 'Call of Duty',
    'call-of-duty-account' => 'Call of Duty',
    'cod' => 'Call of Duty',
    'warzone' => 'Call of Duty',
];

if ($gameName === '' && isset($knownGames[$gameSlug])) {
    $gameName = $knownGames[$gameSlug];
}
if ($gameName === '' && stripos($invoiceDescription, 'LoL account') !== false) {
    $gameName = 'League of Legends';
    $gameSlug = 'league-of-legends';
}
if ($gameName === '' && preg_match('/call\s*of\s*duty|\bcod\b|warzone/i', $invoiceDescription . ' ' . $accountTitle)) {
    $gameName = 'Call of Duty';
    $gameSlug = 'call-of-duty';
}
if ($gameName === '') {
    $gameName = 'Game Account';
}

if ($gameIcon !== '' && !preg_match('~^(?:https?:)?//|^data:|^/~i', $gameIcon)) {
    $gameIcon = rtrim((string)ASSET_URL, '/') . '/' . ltrim($gameIcon, '/');
}

$server = strtoupper(trim((string)($data['server'] ?? '')));
$level = (int)($data['level'] ?? 0);
$rankedReady = array_key_exists('ranked_ready', $data) ? (int)$data['ranked_ready'] === 1 : null;
$rankValue = $data['current_rank'] ?? $data['rank'] ?? $data['lol_rank'] ?? null;
$rankIcon = '';
$rankName = '';

if ($rankValue !== null && $rankValue !== '' && function_exists('util_rank_img')) {
    $rankIcon = (string)util_rank_img('lol', 'mini', (int)$rankValue);
}
if ($rankValue !== null && $rankValue !== '' && function_exists('util_format_rank_advanced')) {
    $rankName = trim((string)util_format_rank_advanced((int)$rankValue, (int)($data['division'] ?? $data['current_division'] ?? 0), 'lol'));
}

$featuresRaw = $data['features'] ?? '';
$features = is_array($featuresRaw) ? $featuresRaw : preg_split('/[|\r\n]+/', (string)$featuresRaw);
$features = array_values(array_filter(array_map('trim', (array)$features), static fn($value) => $value !== ''));

?>
<style>
.account-checkout-summary{display:flex;flex-direction:column}
.account-checkout-product{display:flex;align-items:center;gap:15px;padding:5px 0 20px;margin-bottom:15px;border-bottom:1px solid rgba(255,255,255,.09)}
.account-checkout-icon{width:58px;height:58px;flex:0 0 58px;padding:9px;border-radius:16px;object-fit:contain;background:linear-gradient(145deg,rgba(99,102,241,.2),rgba(139,92,246,.12));border:1px solid rgba(129,140,248,.3)}
.account-checkout-icon-fallback{display:flex;align-items:center;justify-content:center;font-size:25px}
.account-checkout-eyebrow{margin-bottom:7px;color:#9b8cff;font-size:11px;font-weight:900;line-height:1;text-transform:uppercase;letter-spacing:.09em}
.account-checkout-title{color:#fff;font-size:19px;font-weight:900;line-height:1.25;overflow-wrap:anywhere}
.account-checkout-game{display:flex;align-items:center;gap:7px;margin-top:6px;color:rgba(255,255,255,.62);font-size:13px;font-weight:750}
.account-checkout-game i{color:#8176ff}
@media(max-width:600px){.account-checkout-product{gap:11px}.account-checkout-icon{width:49px;height:49px;flex-basis:49px;border-radius:14px}.account-checkout-title{font-size:16px}}
</style>

<div class="account-checkout-summary">
    <div class="account-checkout-product">
        <?php if ($rankIcon !== ''): ?>
            <img class="account-checkout-icon" src="<?= $esc($rankIcon) ?>" alt="<?= $esc($rankName) ?>">
        <?php elseif ($gameIcon !== ''): ?>
            <img class="account-checkout-icon" src="<?= $esc($gameIcon) ?>" alt="<?= $esc($gameName) ?>">
        <?php else: ?>
            <div class="account-checkout-icon account-checkout-icon-fallback">🎮</div>
        <?php endif; ?>

        <div>
            <div class="account-checkout-eyebrow"><?= t('Selected Account') ?></div>
            <div class="account-checkout-title"><?= $esc($accountTitle) ?></div>
            <div class="account-checkout-game"><i class="fa-solid fa-gamepad"></i><span><?= $esc($gameName) ?></span></div>
        </div>
    </div>

    <?php if (!empty($features)): ?>
        <div class="checkout-benefit-pills">
            <?php foreach ($features as $feature): ?>
                <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span><?= $esc($feature) ?></span></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
