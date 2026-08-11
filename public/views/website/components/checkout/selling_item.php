<?php
// ── Helpers (same mapping as item view / shop) ─────────────────────────────

if (!function_exists('_sico_game_meta')) {
    function _sico_game_meta(array $data): array {
        $slug = strtolower(trim((string)($data['checkout_game_slug'] ?? $data['game'] ?? '')));
        $name = trim((string)($data['checkout_game_name'] ?? ''));
        $icon = trim((string)($data['checkout_game_icon'] ?? ''));
        if (($slug === '' || $name === '' || $icon === '') && !empty($data['game_id']) && function_exists('util_get_all_games')) {
            try {
                foreach ((array)util_get_all_games(true) as $g) {
                    if ((int)($g['id'] ?? 0) !== (int)$data['game_id']) continue;
                    if ($slug === '') $slug = strtolower(trim((string)($g['slug'] ?? '')));
                    if ($name === '') $name = trim((string)($g['name'] ?? ''));
                    if ($icon === '') $icon = trim((string)($g['icon'] ?? ''));
                    break;
                }
            } catch (Throwable $e) {}
        }
        if ($name === '' && $slug !== '') $name = ucwords(str_replace('-', ' ', $slug));
        if ($name === '') $name = 'Game';
        if ($icon === '' && $slug !== '') $icon = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $slug . '.png';
        return ['slug' => $slug, 'name' => $name, 'icon' => $icon];
    }
}

if (!function_exists('_sico_type_label')) {    function _sico_type_label(string $t): string {
        $m = [
            'skins'=>'Skins','skin'=>'Skins','chests-keys'=>'Chests & Keys',
            'chest-key'=>'Chests & Keys','chest'=>'Chests & Keys','orbs'=>'Orbs',
            'orb'=>'Orbs','capsules'=>'Capsules','capsule'=>'Capsules',
            'event-pass'=>'Event Pass','pass'=>'Event Pass','bundles'=>'Bundles',
            'bundle'=>'Bundles','tft-item'=>'TFT Item','tft'=>'TFT Item',
            'mystery-gift'=>'Mystery Gift','gifting'=>'Mystery Gift',
        ];
        $k = strtolower(trim($t));
        return $m[$k] ?? ucwords(str_replace(['-','_'],' ',$t));
    }
    function _sico_type_key(string $t): string {
        $label = strtolower(_sico_type_label($t));
        return trim(preg_replace('/[^a-z0-9]+/', '-', $label), '-');
    }
    function _sico_type_img(string $t): ?string {
        // Same stems as items_shop_type_img() in item view
        $stems = [
            'skins'        => 'skins-item',
            'chests-keys'  => 'chest-item',
            'orbs'         => 'orbs-item',
            'capsules'     => 'capsules-item',
            'event-pass'   => 'event-pass-item',
            'bundles'      => 'bundle-item',
            'tft-item'     => 'tft-item',
            'mystery-gift' => null,
        ];
        $key = _sico_type_key($t);
        if (!array_key_exists($key, $stems) || $stems[$key] === null) return null;
        return rtrim(ASSET_URL, '/') . '/website/images/items/' . $stems[$key] . '.webp';
    }
    function _sico_type_fa(string $t): string {
        $m = [
            'skins'=>'fa-shirt','chests-keys'=>'fa-key','orbs'=>'fa-circle-nodes',
            'capsules'=>'fa-capsules','event-pass'=>'fa-ticket','bundles'=>'fa-gift',
            'tft-item'=>'fa-chess-board','mystery-gift'=>'fa-sparkles',
        ];
        return 'fa-solid ' . ($m[_sico_type_key($t)] ?? 'fa-tag');
    }
    function _sico_server_flag(string $s): string {
        $m = [
            'EUW'=>'🇪🇺','EUNE'=>'🇪🇺','NA'=>'🇺🇸','TR'=>'🇹🇷','RU'=>'🇷🇺',
            'BR'=>'🇧🇷','LAN'=>'🌎','LAS'=>'🌎','OCE'=>'🇦🇺','JP'=>'🇯🇵',
            'KR'=>'🇰🇷','ME'=>'🌍','SG'=>'🇸🇬','TW'=>'🇹🇼','VN'=>'🇻🇳',
            'PH'=>'🇵🇭','TH'=>'🇹🇭','ID'=>'🇮🇩',
        ];
        return $m[strtoupper(trim($s))] ?? '🌐';
    }
}

$_si_title    = htmlspecialchars($data['title'] ?? 'Item');
$_si_game     = _sico_game_meta(is_array($data ?? null) ? $data : []);
$_si_game_name = $_si_game['name'];
$_si_game_icon = $_si_game['icon'];
$_si_game_slug = $_si_game['slug'];
$_si_item_data = json_decode((string)($data['item_data'] ?? '{}'), true);
if (!is_array($_si_item_data)) $_si_item_data = [];
foreach (['type','server'] as $_k) if (!empty($data[$_k]) && empty($_si_item_data[$_k])) $_si_item_data[$_k] = $data[$_k];
if (!function_exists('_sico_waiting_time_label')) {
    function _sico_waiting_time_label(array $item, array $data = []): string {
        $amount = isset($item['waiting_time_amount']) ? (int)$item['waiting_time_amount'] : (int)($data['waiting_time_amount'] ?? 0);
        $unit = strtolower(trim((string)($item['waiting_time_unit'] ?? ($data['waiting_time_unit'] ?? ''))));
        if (!in_array($unit, ['minutes','hours','days'], true)) $unit = 'hours';
        if ($amount <= 0 && $unit !== 'minutes') {
            $days = (int)($item['requires_friendship_days'] ?? 0);
            if ($days >= 7) { $amount = 7; $unit = 'days'; }
            elseif ($days > 1) { $amount = $days; $unit = 'days'; }
            else { $amount = 24; $unit = 'hours'; }
        }
        $labelUnit = $amount === 1 ? rtrim($unit, 's') : $unit;
        return $amount . ' ' . $labelUnit;
    }
}

$_si_schema = function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($_si_game_slug) : [];
$_si_schema_fields = (!empty($_si_schema['fields']) && is_array($_si_schema['fields'])) ? array_values($_si_schema['fields']) : [];
$_si_schema_map = [];
foreach ($_si_schema_fields as $_field) {
    $_key = trim((string)($_field['key'] ?? ''));
    if ($_key !== '') $_si_schema_map[$_key] = $_field;
}
$_si_type     = (string)($_si_item_data['type'] ?? '');
$_si_server_raw = trim((string)($_si_item_data['server'] ?? ''));
$_si_server   = function_exists('util_format_server_code') ? util_format_server_code($_si_server_raw) : strtoupper($_si_server_raw);
$_si_days     = (int)($data['requires_friendship_days'] ?? 0);
$_si_qty      = max(1, (int)($data['quantity'] ?? $data['_qty'] ?? 1));
$_si_show_type = $_si_type !== '' && isset($_si_schema_map['type']);
$_si_show_server = $_si_server !== '' && isset($_si_schema_map['server']);
$_si_type_lbl = $_si_show_type ? _sico_type_label($_si_type) : '';
$_si_type_fa  = $_si_show_type ? _sico_type_fa($_si_type) : 'fa-solid fa-gamepad';
$_si_flag     = _sico_server_flag($_si_server);

// Type image only when the admin schema has an Item Type field.
$_si_type_img = $_si_show_type ? _sico_type_img($_si_type) : null;
?>

<style>
.si-summary { display: flex; flex-direction: column; gap: 0; }
.si-title-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 0 0 14px; border-bottom: 1px solid rgba(255,255,255,.07);
    margin-bottom: 14px;
}
.si-type-icon {
    width: 64px; height: 64px; border-radius: 10px; flex-shrink: 0;
    background: rgba(99,102,241,.18); border: 1px solid rgba(99,102,241,.35);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #a5b4fc; overflow: hidden;
}
.si-type-icon img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.si-title-text { flex: 1; min-width: 0; }
.si-title-text strong {
    display: block; font-size: 13px; font-weight: 700; color: #fff;
    line-height: 1.4; word-break: break-word;
}
.si-title-text small {
    font-size: 11px; color: rgba(255,255,255,.45); font-weight: 500;
}
.si-meta-value i { color: #818cf8; font-size: 12px; }
</style>

<div class="si-summary">

    <!-- Title + type image (e.g. bundle-item.webp) -->
    <div class="si-title-row">
        <div class="si-type-icon">
            <?php if ($_si_type_img): ?>
                <img src="<?= htmlspecialchars($_si_type_img) ?>" alt="<?= htmlspecialchars($_si_type_lbl) ?>">
            <?php elseif ($_si_game_icon): ?>
                <img src="<?= htmlspecialchars($_si_game_icon) ?>" alt="<?= htmlspecialchars($_si_game_name) ?>">
            <?php else: ?>
                <i class="fa-solid fa-gamepad"></i>
            <?php endif; ?>
        </div>
        <div class="si-title-text">
            <strong><?= $_si_title ?></strong>
            <small><?= htmlspecialchars($_si_game_name) ?><?= $_si_show_type ? ' · ' . htmlspecialchars($_si_type_lbl) : '' ?></small>
        </div>
    </div>

    <!-- Perks -->
    <div class="checkout-benefit-pills">
        <div class="checkout-benefit-pill"><i class="fa-solid fa-shield-halved"></i><span><?= t('Secure checkout & instant confirmation') ?></span></div>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-gift"></i><span><?= t('Item delivered via safe in-game gifting') ?></span></div>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-headset"></i><span><?= t('Full support & warranty on every order') ?></span></div>
    </div>

</div>
