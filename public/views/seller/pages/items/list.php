<?php echo $this->layout('seller/layouts/main', ['meta' => ['title' => 'My Items | LoLBoost.gg']]); ?>

<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
$effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$items = is_array($items ?? null) ? $items : [];

$itemGames = is_array($itemGames ?? null) ? $itemGames : [];
$itemSchemas = is_array($itemSchemas ?? null) ? $itemSchemas : [];
if (empty($itemGames) && function_exists('util_get_game_by_slug')) {
    $g = util_get_game_by_slug('league-of-legends');
    if (is_array($g)) $itemGames[] = $g;
}
if (empty($itemGames)) {
    $itemGames[] = ['id'=>0, 'slug'=>'league-of-legends', 'name'=>'League of Legends'];
}
if (!function_exists('seller_item_decode_item_data')) {
    function seller_item_decode_item_data($item): array {
        $data = json_decode((string)($item['item_data'] ?? '{}'), true);
        if (!is_array($data)) $data = [];
        foreach (['game','type','server'] as $k) if (!empty($item[$k]) && empty($data[$k])) $data[$k] = $item[$k];
        if (empty($data['delivery_time'])) {
            $days = (int)($item['requires_friendship_days'] ?? 1);
            $data['delivery_time'] = $days > 1 ? 'more_than_24_hours' : 'within_6_24_hours';
        }
        return $data;
    }
}


if (!function_exists('seller_item_waiting_time_meta')) {
    function seller_item_waiting_time_meta($item): array {
        $data = seller_item_decode_item_data($item);
        $amount = isset($item['waiting_time_amount']) ? (int)$item['waiting_time_amount'] : (int)($data['waiting_time_amount'] ?? 0);
        $unit = strtolower(trim((string)($item['waiting_time_unit'] ?? ($data['waiting_time_unit'] ?? ''))));
        if (!in_array($unit, ['minutes','hours','days'], true)) $unit = 'hours';
        if ($amount <= 0 && $unit !== 'minutes') {
            $days = (int)($item['requires_friendship_days'] ?? 0);
            if ($days >= 7) { $amount = 7; $unit = 'days'; }
            elseif ($days > 1) { $amount = $days; $unit = 'days'; }
            else { $amount = 24; $unit = 'hours'; }
        }
        $minutes = $unit === 'days' ? $amount * 1440 : ($unit === 'hours' ? $amount * 60 : $amount);
        $labelUnit = $amount === 1 ? rtrim($unit, 's') : $unit;
        return ['amount'=>$amount, 'unit'=>$unit, 'minutes'=>$minutes, 'label'=>$amount . ' ' . $labelUnit];
    }
}

if (!function_exists('seller_item_game_meta')) {
    function seller_item_game_meta(array $item, array $itemGames): array {
        static $cache = [];
        $gameData = seller_item_decode_item_data($item);
        $raw = strtolower(trim((string)($item['game'] ?? ($gameData['game'] ?? ''))));
        $gameId = (int)($item['game_id'] ?? ($gameData['game_id'] ?? 0));
        $slugToShort = ['league-of-legends'=>'lol','valorant'=>'val','teamfight-tactics'=>'tft'];
        $shortToSlug = ['lol'=>'league-of-legends','val'=>'valorant','tft'=>'teamfight-tactics'];
        $norm = $shortToSlug[$raw] ?? $raw;
        $games = $itemGames;
        if (empty($games) && function_exists('util_get_all_games')) {
            try { $games = util_get_all_games(true); } catch (Throwable $e) { $games = []; }
        }
        foreach ($games as $g) {
            $gSlug = strtolower(trim((string)($g['slug'] ?? '')));
            $gShort = $slugToShort[$gSlug] ?? $gSlug;
            $gId = (int)($g['id'] ?? 0);
            if (($gameId > 0 && $gId === $gameId) || ($norm !== '' && ($gSlug === $norm || $gShort === $raw || $gSlug === $raw))) {
                $icon = (string)($g['icon'] ?? '');
                if ($icon === '') $icon = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $gSlug . '.png';
                return [
                    'id' => $gId,
                    'slug' => $gSlug !== '' ? $gSlug : $norm,
                    'short' => $gShort,
                    'name' => (string)($g['name'] ?? ucwords(str_replace('-', ' ', $gSlug !== '' ? $gSlug : $norm))),
                    'icon' => $icon,
                ];
            }
        }
        if ($norm === '') $norm = 'league-of-legends';
        $icon = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $norm . '.png';
        return ['id'=>$gameId, 'slug'=>$norm, 'short'=>$slugToShort[$norm] ?? $norm, 'name'=>ucwords(str_replace('-', ' ', $norm)), 'icon'=>$icon];
    }
}

if (!function_exists('seller_item_safe_json')) {
    function seller_item_safe_json($value) {
        return htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('seller_item_type_label')) {
    function seller_item_type_label($type) {
        $map = [
            'skins' => 'Skins',
            'skin' => 'Skins',
            'chests-keys' => 'Chests & Keys',
            'chest-key' => 'Chests & Keys',
            'orbs' => 'Orbs',
            'orb' => 'Orbs',
            'capsules' => 'Capsules',
            'capsule' => 'Capsules',
            'event-pass' => 'Event Pass',
            'pass' => 'Event Pass',
            'bundles' => 'Bundles',
            'bundle' => 'Bundles',
            'tft-item' => 'TFT Item',
            'tft' => 'TFT Item',
        ];
        return $map[$type] ?? ucwords(str_replace(['_', '-'], ' ', (string)$type));
    }
}


if (!function_exists('seller_item_schema_has_field')) {
    function seller_item_schema_has_field(array $schema, string $key): bool {
        if (empty($schema['fields']) || !is_array($schema['fields'])) return false;
        foreach ($schema['fields'] as $field) {
            if (trim((string)($field['key'] ?? '')) === $key) return true;
        }
        return false;
    }
}

if (!function_exists('seller_item_game_data_summary')) {
    function seller_item_game_data_summary(array $item, array $gameMeta, array $itemSchemas): string {
        $slug = strtolower(trim((string)($gameMeta['slug'] ?? '')));
        $schema = $itemSchemas[$slug] ?? [];
        $data = seller_item_decode_item_data($item);
        $parts = [];
        $skip = ['title'=>true,'description'=>true,'price'=>true,'stock'=>true,'images'=>true,'game'=>true,'game_id'=>true,'waiting_time_amount'=>true,'waiting_time_unit'=>true,'waiting_time_minutes'=>true,'delivery_time'=>true];

        if (!empty($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as $field) {
                $key = trim((string)($field['key'] ?? ''));
                if ($key === '' || isset($skip[$key])) continue;
                $label = trim((string)($field['label'] ?? ucwords(str_replace('_', ' ', $key))));
                $value = trim((string)($data[$key] ?? ($item[$key] ?? '')));
                if ($value === '') continue;
                if ($key === 'item_type') $key = 'type';
                if ($key === 'type') $value = seller_item_type_label($value);
                if ($key === 'server') $value = function_exists('util_format_server_code') ? util_format_server_code($value) : strtoupper($value);
                $parts[] = $label . ': ' . $value;
            }
        } elseif (in_array($slug, ['league-of-legends','lol','league'], true)) {
            $rawType = trim((string)($data['type'] ?? $data['item_type'] ?? ($item['type'] ?? '')));
            $rawServer = trim((string)($data['server'] ?? ($item['server'] ?? '')));
            if ($rawType !== '') $parts[] = 'Type: ' . seller_item_type_label($rawType);
            if ($rawServer !== '') $parts[] = 'Server: ' . (function_exists('util_format_server_code') ? util_format_server_code($rawServer) : strtoupper($rawServer));
        }

        $waiting = seller_item_waiting_time_meta($item);
        if (!empty($waiting['label'])) $parts[] = 'Waiting: ' . $waiting['label'];
        return !empty($parts) ? implode(' · ', array_slice($parts, 0, 4)) : '—';
    }
}
?>

<?php echo $this->start('styles'); ?>
<style>
.al-page .card { background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important; }
.al-page .card::before { display:none!important; }
.al-add-btn { display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;border-radius:13px;padding:.6rem 1.4rem;font-weight:900;font-size:.9rem;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;text-decoration:none; }
.al-add-btn:hover { opacity:.88;transform:translateY(-1px);color:#fff; }
.al-pills { display:flex;gap:6px;flex-wrap:wrap; }
.al-type-pills { display:flex;gap:6px;flex-wrap:wrap; }
.al-type-divider { width:1px;height:22px;background:rgba(255,255,255,.08);margin:0 2px; }
.al-pill[data-type].active { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-chk {
    appearance: none;
    -webkit-appearance: none;
    width: 17px;
    height: 17px;
    border-radius: 5px;
    border: 1.5px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.06);
    cursor: pointer;
    flex-shrink: 0;
    position: relative;
    transition: background .12s, border-color .12s;
    display: inline-block;
    vertical-align: middle;
}
.al-chk:hover { border-color: rgba(109,92,255,.6); background: rgba(109,92,255,.12); }
.al-chk:checked { background: #6d5cff; border-color: #6d5cff; }
.al-chk:checked::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 1.5px;
    width: 5px;
    height: 9px;
    border: 2px solid #fff;
    border-top: 0;
    border-left: 0;
    transform: rotate(45deg);
}
.al-chk:indeterminate { background: rgba(109,92,255,.4); border-color: rgba(109,92,255,.7); }
.al-chk:indeterminate::after {
    content: '';
    position: absolute;
    left: 3px;
    top: 6.5px;
    width: 9px;
    height: 2px;
    background: #fff;
    border-radius: 1px;
}
.al-chk:disabled { opacity: .3; cursor: not-allowed; }
.al-search-wrap { position:relative; }
.al-search-wrap input { background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s; }
.al-search-wrap input:focus { border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important; }
.al-search-wrap input::placeholder { color:rgba(255,255,255,.25)!important; }
.al-search-icon { position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none; }
.al-pill { display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none; }
.al-pill:hover { background:rgba(255,255,255,.08);color:rgba(255,255,255,.85); }
.al-pill.active { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-pill[data-status="Active"].active { background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80; }
.al-pill[data-status="Unlisted"].active { background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.35);color:#facc15; }
.al-pill[data-status="Sold"].active { background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.30);color:#fb7185; }
.al-table-wrap { border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:visible;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);position:relative; }
.al-table { width:100%; border-collapse:collapse; border-radius:20px; overflow:hidden; display:table; }
.al-table thead tr { background:rgba(255,255,255,.03); border-bottom:1px solid rgba(255,255,255,.06); }
.al-table thead th { padding:11px 16px; font-size:.68rem; font-weight:900; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; user-select:none; }
.al-table thead th.sortable { cursor:pointer; }
.al-table thead th.sortable:hover { color:rgba(255,255,255,.7); }
.al-table thead th .sort-icon { margin-left:4px; opacity:.35; font-size:.6rem; }
.al-table thead th.sort-asc .sort-icon, .al-table thead th.sort-desc .sort-icon { opacity:1;color:#c4b5fd; }
.al-table tbody .al-row { border-bottom:1px solid rgba(255,255,255,.04); transition:background .12s; }
.al-table tbody .al-row:last-child { border-bottom:none; }
.al-table tbody .al-row:hover { background:rgba(109,92,255,.08); }
.al-table tbody td { padding:13px 16px; vertical-align:middle; font-size:.85rem; color:rgba(255,255,255,.8); }
.al-col-id { font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);font-variant-numeric:tabular-nums; }
.al-listing-wrap { display:flex;align-items:center;gap:11px; }
.al-listing-media { position:relative;width:42px;height:42px;flex:0 0 42px; }
.al-listing-img { width:42px;height:42px;border-radius:10px;object-fit:cover;background:rgba(255,255,255,.04);display:block; }
.al-game-corner-icon { position:absolute;right:-4px;bottom:-4px;width:17px;height:17px;border-radius:5px;object-fit:contain;background:#24282b;border:1px solid rgba(255,255,255,.14);padding:1px;box-shadow:0 2px 7px rgba(0,0,0,.4); }
.al-game-cell { display:flex;align-items:center;gap:8px;min-width:0; }
.al-data-summary{display:block;max-width:260px;color:rgba(255,255,255,.72);font-size:.78rem;line-height:1.35;}
.al-game-cell img { width:20px;height:20px;object-fit:contain;border-radius:5px;flex:0 0 20px; }
.al-game-cell span { font-size:.8rem;font-weight:800;color:rgba(255,255,255,.78);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px; }
.al-listing-name { font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2; }
.al-listing-sub { font-size:.74rem;color:rgba(255,255,255,.38);margin-top:1px; }
.al-col-price { font-size:.9rem;font-weight:800;color:rgba(255,255,255,.9);font-variant-numeric:tabular-nums; }
.al-col-earnings { font-size:.85rem;font-weight:700;color:#4ade80;font-variant-numeric:tabular-nums; }
.al-badge { display:inline-flex;align-items:center;gap:.3rem; padding:4px 10px;border-radius:99px; font-size:.71rem;font-weight:800;white-space:nowrap; }
.al-badge--active { background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80; }
.al-badge--unlisted { background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15; }
.al-badge--sold { background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185; }
.al-actions-wrap { position:relative;display:inline-block; }
.al-actions-btn { width:32px;height:32px;border-radius:9px; border:1px solid rgba(255,255,255,.09); background:rgba(255,255,255,.04); color:rgba(255,255,255,.5); font-size:.8rem;cursor:pointer; display:inline-flex;align-items:center;justify-content:center; transition:background .12s,color .12s; }
.al-actions-btn:hover { background:rgba(255,255,255,.09);color:rgba(255,255,255,.9); }
.al-actions-btn.is-open { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.4);color:#c4b5fd; }
.al-actions-menu { display:none;position:fixed;min-width:190px;z-index:9999; background:#2a2d35;border:1px solid rgba(255,255,255,.1); border-radius:13px;padding:5px; box-shadow:0 8px 32px rgba(0,0,0,.6);animation:alMenuIn .12s ease; }
.al-actions-menu.is-open { display:block; }
@keyframes alMenuIn { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:none} }
.al-action-item { display:flex;align-items:center;gap:9px; width:100%;padding:8px 11px;border-radius:8px; font-size:.8rem;font-weight:700; color:rgba(255,255,255,.72); background:none;border:none;cursor:pointer; text-decoration:none;text-align:left;transition:background .1s,color .1s; }
.al-action-item:hover { background:rgba(255,255,255,.06);color:#fff; }
.al-action-item i { width:14px;text-align:center;color:rgba(255,255,255,.3);font-size:.78rem;flex-shrink:0; }
.al-action-item:hover i { color:rgba(255,255,255,.6); }
.al-action-danger { color:#fb7185 !important; }
.al-action-danger:hover { background:rgba(251,113,133,.08) !important; }
.al-action-danger i { color:#fb7185 !important; }
.al-action-divider { height:1px;background:rgba(255,255,255,.06);margin:4px 0; }
.al-hero { border-radius:20px; border:1px solid rgba(255,255,255,.07); background:#25282a; padding:20px 24px; display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px; margin-bottom:14px; box-shadow:0 2px 20px rgba(0,0,0,.22); }
.al-hero-left { display:flex;align-items:center;gap:14px; }
.al-hero-icon { width:44px;height:44px;border-radius:13px; background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15)); border:1px solid rgba(109,92,255,.25); display:flex;align-items:center;justify-content:center; font-size:1.1rem;color:#c4b5fd;flex-shrink:0; }
.al-hero-title { font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0; }
.al-hero-sub { font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0; }
.al-toolbar-card { border-radius:16px; border:1px solid rgba(255,255,255,.07); background:#25282a; padding:12px 16px; display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px; margin-bottom:16px; box-shadow:0 2px 16px rgba(0,0,0,.18); }
.al-empty { text-align:center;padding:64px 24px;color:rgba(255,255,255,.35); }
.al-pg-btn { width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s; }
.al-pg-btn:hover:not(:disabled) { background:rgba(255,255,255,.09); }
.al-pg-btn.al-pg-active { background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-pg-btn:disabled { opacity:.35;cursor:not-allowed; }

.item-canvas.custom-offcanvas{ width:50vw !important; display:flex !important; flex-direction:column !important; height:100% !important; }
.item-canvas .offcanvas-header{flex-shrink:0;padding:18px 22px;border-bottom:1px solid var(--bs-card-border-color);}
.item-canvas .oc-earnings-bar{ flex-shrink:0; padding:10px 22px; background:rgba(109,92,255,.08); border-bottom:1px solid rgba(109,92,255,.18); display:flex; align-items:center; gap:8px; font-size:.83rem; color:rgba(255,255,255,.7); }
.item-canvas .oc-steps{ flex-shrink:0; display:flex; align-items:center; padding:14px 22px; border-bottom:1px solid var(--bs-card-border-color); gap:0; }
.item-canvas .oc-step{ display:flex; align-items:center; gap:8px; flex:1; }
.item-canvas .oc-step-num{ width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:900; background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12); color:rgba(255,255,255,.5); }
.item-canvas .oc-step.active .oc-step-num{ background:linear-gradient(135deg,#6d5cff,#b05cff); border-color:transparent; color:#fff; }
.item-canvas .oc-step.done .oc-step-num{ background:rgba(74,222,128,.15); border-color:rgba(74,222,128,.3); color:#4ade80; }
.item-canvas .oc-step-label{ font-size:.8rem; font-weight:700; color:rgba(255,255,255,.4); }
.item-canvas .oc-step.active .oc-step-label{color:#c4b5fd;font-weight:900;}
.item-canvas .oc-step.done .oc-step-label{color:rgba(255,255,255,.6);}
.item-canvas .oc-step-line{ flex:1; height:1px; background:rgba(255,255,255,.08); margin:0 8px; }
.item-canvas .oc-step-line.done{background:rgba(74,222,128,.3);}
.item-canvas .offcanvas-body{ flex:1 !important; overflow:hidden !important; display:flex !important; flex-direction:column !important; padding:0 !important; }
.item-canvas .offcanvas-body > form{ flex:1; display:flex; flex-direction:column; overflow:hidden; min-height:0; }
.item-canvas .oc-scroll{ flex:1; overflow-y:auto; padding:18px 22px; }
.item-canvas .oc-footer{ flex-shrink:0; display:flex; align-items:center; justify-content:space-between; padding:12px 22px; border-top:1px solid var(--bs-card-border-color); background:var(--bs-offcanvas-bg, #1e2028); }
.item-canvas .oc-btn-next{ display:inline-flex; align-items:center; gap:.45rem; background:linear-gradient(135deg,#6d5cff,#b05cff); border:none; border-radius:11px; padding:8px 20px; font-size:.87rem; font-weight:900; color:#fff; cursor:pointer; }
.item-canvas .oc-btn-prev{ display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.10); border-radius:11px; padding:8px 16px; font-size:.87rem; font-weight:700; color:rgba(255,255,255,.65); cursor:pointer; }
.item-canvas .oc-section-label{ display:flex; align-items:center; gap:6px; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.09em; color:rgba(255,255,255,.3); margin:14px 0 8px; padding-bottom:6px; border-bottom:1px solid rgba(255,255,255,.06); }
.item-canvas .oc-required{color:#f87171;font-size:.75rem;vertical-align:super;}
.item-canvas .account-upload-box{ border:2px dashed rgba(255,255,255,.12); border-radius:12px; background:rgba(255,255,255,.02); cursor:pointer; }
.item-canvas .account-upload-box.dragover{ border-color:#6366f1; background:rgba(99,102,241,.08); }
.item-canvas .gallery-preview-tile{ position:relative; overflow:hidden; border-radius:.5rem; background:rgba(255,255,255,.02); cursor:grab; }
.item-canvas .gallery-preview-tile img{ width:100%!important; height:150px!important; object-fit:cover; display:block; }
.item-canvas .gallery-preview-tile.is-main{ outline:2px solid rgba(99,102,241,.9); outline-offset:2px; }
.item-canvas .gallery-preview-badge{ position:absolute; top:.5rem; left:.5rem; padding:.25rem .5rem; border-radius:999px; background:rgba(99,102,241,.95); color:#fff; font-size:.75rem; font-weight:600; z-index:2; }
.item-canvas .gallery-preview-hint{ position:absolute; bottom:.5rem; left:.5rem; right:.5rem; padding:.25rem .5rem; border-radius:.5rem; background:rgba(0,0,0,.35); color:rgba(255,255,255,.9); font-size:.75rem; z-index:2; }
.item-canvas .gallery-preview-overlay{ position:absolute; inset:0; background-color:rgba(220,53,69,.30); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity .25s ease; }
.item-canvas .gallery-preview-tile:hover .gallery-preview-overlay{ opacity:1; }
.item-canvas .gallery-preview-remove{ border:0; background:rgba(220,53,69,.95); color:#fff; width:44px; height:44px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; }
@media only screen and (max-width:1200px){ .al-table-wrap{overflow-x:auto}.al-table{min-width:1040px} }

.item-canvas .is-invalid,
.item-canvas .form-control.is-invalid,
.item-canvas .form-select.is-invalid{
  border-color:#dc3545 !important;
  box-shadow:0 0 0 0.2rem rgba(220,53,69,.12) !important;
}
.item-canvas .invalid-feedback{
  display:none;
  color:#ff8b95;
  font-size:.75rem;
  margin-top:6px;
}
.item-canvas .is-invalid + .invalid-feedback,
.item-canvas .form-control.is-invalid ~ .invalid-feedback,
.item-canvas .form-select.is-invalid ~ .invalid-feedback{
  display:block;
}

.js-item-dynamic-fields{display:contents}.lb-item-dyn-field{margin-bottom:12px}
.item-canvas .oc-game-picker{border:1px solid rgba(255,255,255,.08);border-radius:16px;background:rgba(255,255,255,.025);overflow:hidden}.item-canvas .oc-game-picker__top{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025)}.item-canvas .oc-game-search{position:relative;flex:1;min-width:0}.item-canvas .oc-game-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.34);font-size:.82rem;pointer-events:none}.item-canvas .oc-game-search input{width:100%;height:40px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.16);color:#fff;padding:0 14px 0 38px;font-size:.86rem;outline:none}.item-canvas .oc-game-search input:focus{border-color:rgba(109,92,255,.55);box-shadow:0 0 0 3px rgba(109,92,255,.12)}.item-canvas .oc-game-selected-chip{display:inline-flex;align-items:center;gap:8px;max-width:260px;padding:8px 12px;border-radius:999px;border:1px solid rgba(109,92,255,.35);background:rgba(109,92,255,.14);color:#fff;font-size:.8rem;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.item-canvas .oc-game-selected-chip img{width:18px;height:18px;object-fit:contain;flex:0 0 18px}.item-canvas .oc-game-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;max-height:430px;overflow-y:auto;padding:12px}.item-canvas .oc-game-card{display:flex;align-items:center;gap:10px;min-height:52px;padding:9px 11px;border-radius:13px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.035);cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s,transform .12s;width:100%;text-align:left}.item-canvas .oc-game-card:hover{border-color:rgba(109,92,255,.42);background:rgba(109,92,255,.08);transform:translateY(-1px)}.item-canvas .oc-game-card.selected{border-color:#6d5cff;background:rgba(109,92,255,.16);box-shadow:0 0 0 2px rgba(109,92,255,.13)}.item-canvas .oc-game-card__icon{width:32px;height:32px;display:flex;align-items:center;justify-content:center;flex:0 0 32px}.item-canvas .oc-game-card__icon img{width:30px;height:30px;object-fit:contain;display:block;filter:drop-shadow(0 4px 10px rgba(0,0,0,.22))}.item-canvas .oc-game-card__name{font-size:.86rem;font-weight:900;color:#fff;line-height:1.15;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.item-canvas .oc-game-card__sub{font-size:.68rem;color:rgba(255,255,255,.36);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.item-canvas .oc-game-card.is-hidden{display:none!important}.item-canvas .oc-game-empty{display:none;padding:28px 14px;text-align:center;color:rgba(255,255,255,.36);font-size:.86rem}
@media only screen and (max-width:576px){ .item-canvas.custom-offcanvas{width:100vw!important}.item-canvas .oc-game-picker__top{flex-direction:column;align-items:stretch}.item-canvas .oc-game-selected-chip{max-width:100%;justify-content:center}.item-canvas .oc-game-list{grid-template-columns:1fr;max-height:360px} }
</style>
<?php echo $this->end(); ?>

<div class="al-page">
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-duotone fa-gift"></i></div>
      <div>
        <h2 class="al-hero-title">My Items</h2>
        <p class="al-hero-sub"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?> total</p>
      </div>
    </div>
    <button type="button" class="al-add-btn" data-bs-toggle="offcanvas" data-bs-target="#addItemCanvas"><i class="fa-solid fa-plus"></i> Add Item</button>
  </div>

  <div class="al-toolbar-card">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1;">
      <div class="al-pills" id="alStatusFilters">
        <span class="al-pill active" data-status="all">All</span>
        <span class="al-pill" data-status="Active">Active</span>
        <span class="al-pill" data-status="Unlisted">Unlisted</span>
        <span class="al-pill" data-status="Sold">Sold</span>
      </div>

      <?php
      $typeFilterMap = [];
      foreach ($items as $typeFilterItem) {
          $typeKeyRaw = (string)($typeFilterItem['type'] ?? '');
          if ($typeKeyRaw === '') continue;
          $typeLabel = seller_item_type_label($typeKeyRaw);
          $typeKey = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $typeLabel), '-'));
          if ($typeKey === '') continue;
          $typeFilterMap[$typeKey] = $typeLabel;
      }
      asort($typeFilterMap);
      ?>
      <?php if (!empty($typeFilterMap)): ?>
      <div class="al-type-divider"></div>
      <div class="al-type-pills" id="alTypeFilters">
        <?php foreach ($typeFilterMap as $typeKey => $typeLabel): ?>
          <span class="al-pill" data-type="<?= htmlspecialchars($typeKey) ?>"><?= htmlspecialchars($typeLabel) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <button type="button" id="alBulkDeleteBtn" style="display:none;align-items:center;gap:.4rem;padding:6px 14px;border-radius:10px;background:rgba(251,113,133,.14);border:1px solid rgba(251,113,133,.28);color:#fb7185;font-size:.8rem;font-weight:800;cursor:pointer;transition:background .12s;">
        <i class="fa-duotone fa-trash"></i>
        Delete selected (<span id="alBulkCount">0</span>)
      </button>
    </div>
    <div class="al-search-wrap">
      <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
      <input type="search" id="alSearch" placeholder="Search items…">
    </div>
  </div>

  <div class="al-table-wrap" id="alTableWrap">
    <table class="al-table" id="alGrid">
      <thead>
        <tr>
          <th style="width:36px;padding:10px 8px;">
            <input type="checkbox" id="alChkAll" class="al-chk" aria-label="Select all">
          </th>
          <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Item</th>
          <th>Game</th>
          <th>Game Data</th>
          <th class="sortable" data-col="price">Price <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="earnings">Earnings <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="stock">Stock <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="sold">Sold <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Status</th>
          <th class="sortable" data-col="date">Created <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody id="alTbody">
        <?php if (!empty($items)): foreach ($items as $it):
          $images = json_decode((string)($it['images'] ?? '[]'), true); if (!is_array($images)) $images = [];
          $cover = $images[0] ?? (ASSET_URL . '/public/uploads/icons/default2.png');
          $priceRaw = ((int)($it['price'] ?? 0)) / 100;
          $earningsRaw = $priceRaw * (1 - ($effective_fee / 100));
          $soldCount = (int)($it['sold_count'] ?? 0);
          $activeState = (int)($it['active'] ?? 1) === 1;
          $status = $soldCount > 0 ? 'Sold' : ($activeState ? 'Active' : 'Unlisted');
          $createdAtRaw = $it['created_at'] ?? '';
          $createdAtTs = $createdAtRaw ? strtotime((string)$createdAtRaw) : false;
          if ($createdAtTs) {
            $diffSec = time() - $createdAtTs;
            $diffDays = (int)floor($diffSec / 86400);
            if ($diffDays < 1) $createdAtFmt = 'today';
            elseif ($diffDays === 1) $createdAtFmt = '1 day ago';
            elseif ($diffDays < 7) $createdAtFmt = $diffDays . ' days ago';
            elseif ($diffDays < 14) $createdAtFmt = '1 week ago';
            elseif ($diffDays < 30) $createdAtFmt = (int)floor($diffDays / 7) . ' weeks ago';
            elseif ($diffDays < 60) $createdAtFmt = '1 month ago';
            else $createdAtFmt = (int)floor($diffDays / 30) . ' months ago';
          } else {
            $createdAtFmt = (string)$createdAtRaw;
          }
          $canDelete = $soldCount === 0;
          $itemGameMeta = seller_item_game_meta($it, $itemGames);
          $itemGameName = (string)($itemGameMeta['name'] ?? 'Game');
          $itemGameIcon = (string)($itemGameMeta['icon'] ?? '');
          $itemGameKey = (string)($itemGameMeta['short'] ?? ($itemGameMeta['slug'] ?? ''));
          $gameDataSummary = seller_item_game_data_summary((array)$it, (array)$itemGameMeta, $itemSchemas);
        ?>
        <tr class="al-row"
            data-status="<?= htmlspecialchars($status) ?>"
            data-type="<?= htmlspecialchars(strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', seller_item_type_label($it['type'] ?? '')), '-'))) ?>"
            data-search="<?= htmlspecialchars(strtolower(($it['title'] ?? '') . ' ' . ($it['type'] ?? '') . ' ' . seller_item_type_label($it['type'] ?? '') . ' ' . ($it['server'] ?? '') . ' ' . $itemGameName . ' ' . $itemGameKey . ' ' . $gameDataSummary)) ?>"
            data-id="<?= (int)$it['id'] ?>"
            data-game="<?= htmlspecialchars($itemGameKey) ?>"
            data-price="<?= htmlspecialchars((string)$priceRaw) ?>"
            data-earnings="<?= htmlspecialchars((string)$earningsRaw) ?>"
            data-stock="<?= (int)($it['stock'] ?? 1) ?>"
            data-sold="<?= $soldCount ?>"
            data-date="<?= $createdAtTs ? $createdAtTs : 0 ?>">
          <td style="padding:10px 8px;vertical-align:middle;">
            <input type="checkbox" class="al-row-chk al-chk" value="<?= (int)$it['id'] ?>" <?= $canDelete ? '' : 'disabled title="Sold items cannot be deleted"' ?>>
          </td>
          <td><span class="al-col-id">#<?= (int)$it['id'] ?></span></td>
          <td><div class="al-listing-wrap"><div class="al-listing-media"><img class="al-listing-img" src="<?= htmlspecialchars($cover) ?>" alt=""><?php if ($itemGameIcon !== ''): ?><img class="al-game-corner-icon" src="<?= htmlspecialchars($itemGameIcon) ?>" alt="<?= htmlspecialchars($itemGameName) ?>"><?php endif; ?></div><div><div class="al-listing-name"><?= htmlspecialchars($it['title'] ?? 'Untitled Item') ?></div><div class="al-listing-sub"><?php $waitSub = seller_item_waiting_time_meta($it)['label']; echo htmlspecialchars($itemGameName . ' · Waiting ' . $waitSub); ?></div></div></div></td>
          <td><div class="al-game-cell"><?php if ($itemGameIcon !== ''): ?><img src="<?= htmlspecialchars($itemGameIcon) ?>" alt=""><?php endif; ?><span><?= htmlspecialchars($itemGameName) ?></span></div></td>
          <td><span class="al-data-summary"><?= htmlspecialchars($gameDataSummary) ?></span></td>
          <td><span class="al-col-price">€<?= number_format($priceRaw, 2) ?></span></td>
          <td><span class="al-col-earnings">€<?= number_format($earningsRaw, 2) ?></span></td>
          <td><?= (int)($it['stock'] ?? 1) ?></td>
          <td><?= $soldCount ?></td>
          <td><span class="al-badge <?= $status === 'Sold' ? 'al-badge--sold' : ($status === 'Active' ? 'al-badge--active' : 'al-badge--unlisted') ?>"><?= $status ?></span></td>
          <td><span class="al-col-date"><?= htmlspecialchars($createdAtFmt) ?></span></td>
          <td class="text-end"><div class="al-actions-wrap"><button type="button" class="al-actions-btn" onclick="event.stopPropagation();alToggleMenu(this)" title="Actions"><i class="fa-solid fa-ellipsis"></i></button><div class="al-actions-menu"><button type="button" class="al-action-item js-edit-item" data-bs-toggle="offcanvas" data-bs-target="#editItemCanvas<?= (int)$it['id'] ?>"><i class="fa-solid fa-pen"></i> Edit Item</button><?php if ($status !== 'Sold'): ?><button type="button" class="al-action-item js-toggle-item" data-id="<?= (int)$it['id'] ?>" data-active="<?= $activeState ? '1' : '0' ?>"><?= $activeState ? '<i class="fa-solid fa-eye-slash"></i> Unlist Item' : '<i class="fa-solid fa-eye"></i> Relist Item' ?></button><?php endif; ?><?php if ($canDelete): ?><div class="al-action-divider"></div><button type="button" class="al-action-item al-action-danger js-delete-item" data-id="<?= (int)$it['id'] ?>"><i class="fa-solid fa-trash"></i> Delete Item</button><?php endif; ?></div></div></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="12"><div class="al-empty">No items yet</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0;">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">Showing <span id="alShowing">—</span> of <span id="alTotal">—</span></div>
    <div style="display:flex;gap:5px;flex-wrap:wrap;" id="alPagination"></div>
  </div>
</div>

<?php
$canvasItems = array_merge([null], $items);
foreach ($canvasItems as $canvasItem):
$isEdit = !empty($canvasItem['id']);
$title = $canvasItem['title'] ?? '';
$description = $canvasItem['description'] ?? '';
$type = trim((string)($canvasItem['type'] ?? ''));
$server = trim((string)($canvasItem['server'] ?? ''));
$itemData = seller_item_decode_item_data($canvasItem);
$currentGame = strtolower(trim((string)($canvasItem['game'] ?? ($itemData['game'] ?? ($itemGames[0]['slug'] ?? 'league-of-legends')))));
if ($currentGame === '') $currentGame = 'league-of-legends';
if (!empty($itemData['type'])) $type = (string)$itemData['type'];
if ($type === '' && !empty($itemData['item_type'])) $type = (string)$itemData['item_type'];
if (!empty($itemData['server'])) $server = (string)$itemData['server'];
$priceCents = (int)($canvasItem['price'] ?? 0);
$priceEur = $isEdit ? number_format($priceCents / 100, 2, '.', '') : '';
$stock = (int)($canvasItem['stock'] ?? 999);
$minQty = (int)($canvasItem['min_purchase_qty'] ?? 1);
$maxQty = isset($canvasItem['max_purchase_qty']) && $canvasItem['max_purchase_qty'] !== null ? (int)$canvasItem['max_purchase_qty'] : '';
$waitingMeta = seller_item_waiting_time_meta($canvasItem);
$waitingAmount = (int)$waitingMeta['amount'];
$waitingUnit = (string)$waitingMeta['unit'];
$active = (int)($canvasItem['active'] ?? 1);
$itemImages = [];
try { $itemImages = json_decode($canvasItem['images'] ?? '[]', true) ?: []; } catch (Throwable $e) { $itemImages = []; }
$canvasId = $isEdit ? 'editItemCanvas' . (int)$canvasItem['id'] : 'addItemCanvas';
$fileInputId = $isEdit ? 'galleryUploadItem' . (int)$canvasItem['id'] : 'galleryUploadItemNew';
$dropzoneId = $isEdit ? 'galleryDropzoneItem' . (int)$canvasItem['id'] : 'galleryDropzoneItemNew';
$previewId = $isEdit ? 'previewGalleryItem' . (int)$canvasItem['id'] : 'previewGalleryItemNew';
$orderInputId = $isEdit ? 'imagesOrderInputItem' . (int)$canvasItem['id'] : 'imagesOrderInputItemNew';
$existingInputId = $isEdit ? 'existingImagesJsonItem' . (int)$canvasItem['id'] : 'existingImagesJsonItemNew';
$priceInputId = $isEdit ? 'itemPriceInput' . (int)$canvasItem['id'] : 'itemPriceInputNew';
$earningsId = $isEdit ? 'itemEarningsPreview' . (int)$canvasItem['id'] : 'itemEarningsPreviewNew';
$formId = $isEdit ? 'itemForm' . (int)$canvasItem['id'] : 'itemFormNew';
?>
<div class="offcanvas offcanvas-end custom-offcanvas item-canvas" tabindex="-1" id="<?= $canvasId ?>" data-bs-backdrop="static" data-bs-keyboard="true">
  <div class="offcanvas-header"><div><h5 class="offcanvas-title mb-0"><?= $isEdit ? 'Edit Item' : 'Add Item' ?></h5></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
  <div class="oc-earnings-bar">Estimated seller payout after <strong style="color:#c4b5fd;margin:0 3px;"><?= number_format($effective_fee, 2) ?>%</strong> fee, <strong class="text-success">€<span id="<?= $earningsId ?>"><?= number_format(((float)$priceEur) * (1 - $effective_fee / 100), 2) ?></span></strong> per unit</div>
  <div class="oc-steps">
    <div class="oc-step active" id="<?= $canvasId ?>Step1"><div class="oc-step-num">1</div><div class="oc-step-label">Game</div></div>
    <div class="oc-step-line" id="<?= $canvasId ?>Line1"></div>
    <div class="oc-step" id="<?= $canvasId ?>Step2"><div class="oc-step-num">2</div><div class="oc-step-label">Listing Info</div></div>
    <div class="oc-step-line" id="<?= $canvasId ?>Line2"></div>
    <div class="oc-step" id="<?= $canvasId ?>Step3"><div class="oc-step-num">3</div><div class="oc-step-label">Stock & Delivery</div></div>
    <div class="oc-step-line" id="<?= $canvasId ?>Line3"></div>
    <div class="oc-step" id="<?= $canvasId ?>Step4"><div class="oc-step-num">4</div><div class="oc-step-label">Images</div></div>
  </div>
  <div class="offcanvas-body">
    <form action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" class="ajax-form" id="<?= $formId ?>" novalidate>
      <input type="hidden" name="action" value="<?= $isEdit ? 'seller_update_item' : 'seller_create_item' ?>">
      <input type="hidden" name="active" value="1">
      <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$canvasItem['id'] ?>"><?php endif; ?>
      <input type="hidden" name="images_order" id="<?= $orderInputId ?>" value='<?= seller_item_safe_json($itemImages) ?>'>
      <input type="hidden" name="existing_images_json" id="<?= $existingInputId ?>" value='<?= seller_item_safe_json($itemImages) ?>'>
      <div class="oc-scroll">
        <div class="js-item-step active" data-step="1">
          <div class="oc-section-label"><i class="fa-solid fa-gamepad"></i> Select Game</div>
          <input type="hidden" class="js-item-game-select" name="game" value="<?= htmlspecialchars($currentGame) ?>" required>
          <div class="oc-game-picker mb-3">
            <div class="oc-game-picker__top">
              <div class="oc-game-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" class="js-item-game-search" placeholder="Search game..." autocomplete="off">
              </div>
              <div class="oc-game-selected-chip js-item-game-chip"><span>Choose game</span></div>
            </div>
            <div class="oc-game-list">
              <?php foreach ($itemGames as $g):
                $gSlug = strtolower(trim((string)($g['slug'] ?? '')));
                if ($gSlug === '') continue;
                $gName = (string)($g['name'] ?? ucwords(str_replace('-', ' ', $gSlug)));
                $gIcon = (string)($g['icon'] ?? '');
                if ($gIcon === '') $gIcon = rtrim((string)(defined('ASSET_URL') ? ASSET_URL : '/assets'), '/') . '/website/images/icons/' . $gSlug . '.png';
                $isSelectedGame = $currentGame === $gSlug;
                $searchText = strtolower($gName . ' ' . $gSlug);
              ?>
                <label class="oc-game-card js-item-game-card <?= $isSelectedGame ? 'selected' : '' ?>" data-game="<?= htmlspecialchars($gSlug) ?>" data-name="<?= htmlspecialchars($gName) ?>" data-icon="<?= htmlspecialchars($gIcon) ?>" data-search="<?= htmlspecialchars($searchText) ?>">
                  <input type="radio" name="_item_game_ui_<?= htmlspecialchars($canvasId) ?>" value="<?= htmlspecialchars($gSlug) ?>" <?= $isSelectedGame ? 'checked' : '' ?> style="display:none">
                  <div class="oc-game-card__icon"><img src="<?= htmlspecialchars($gIcon) ?>" alt="<?= htmlspecialchars($gName) ?>"></div>
                  <div style="min-width:0;flex:1;">
                    <div class="oc-game-card__name"><?= htmlspecialchars($gName) ?></div>
                    <div class="oc-game-card__sub">Items, Top Ups and game data</div>
                  </div>
                </label>
              <?php endforeach; ?>
            </div>
            <div class="oc-game-empty js-item-game-empty">No games found.</div>
          </div>
        </div>
        <div class="js-item-step" data-step="2" style="display:none;">
          <div class="oc-section-label">Basic Info</div>
          <div class="row g-2 mb-3">
            <div class="col-12"><label class="form-label">Item Title <span class="oc-required">*</span></label><input type="text" class="form-control" name="title" value="<?= htmlspecialchars($title) ?>" required><div class="invalid-feedback">Please enter an item title.</div></div>
            <?php foreach ($itemGames as $g):
              $gSlug = strtolower(trim((string)($g['slug'] ?? '')));
              if ($gSlug === '') continue;
              $vals = $currentGame === $gSlug ? $itemData : [];
              if ($currentGame === $gSlug && $type !== '' && !array_key_exists('type', $vals)) $vals['type'] = $type;
              if ($currentGame === $gSlug && $type !== '' && !array_key_exists('item_type', $vals)) $vals['item_type'] = $type;
              if ($currentGame === $gSlug && $server !== '' && !array_key_exists('server', $vals)) $vals['server'] = $server;
              $_schemaHtml = function_exists('lb_render_item_dynamic_fields') ? lb_render_item_dynamic_fields($gSlug, $vals) : '';
              if (trim($_schemaHtml) === '') continue;
            ?>
              <div class="col-12 js-item-game-fields" data-game="<?= htmlspecialchars($gSlug) ?>" style="<?= $currentGame === $gSlug ? '' : 'display:none' ?>">
                <div class="row g-3">
                  <?= $_schemaHtml ?>
                </div>
              </div>
            <?php endforeach; ?>
            <div class="col-12"><label class="form-label">Description <span class="oc-required">*</span></label><textarea class="form-control" rows="5" name="description" required><?= htmlspecialchars($description) ?></textarea><div class="invalid-feedback">Please enter a description.</div></div>
          </div>
        </div>
        <div class="js-item-step" data-step="3" style="display:none;">
          <div class="oc-section-label">Pricing & Stock</div>
          <div class="row g-2 mb-3">
            <div class="col-md-6"><label class="form-label">Price per unit <span class="oc-required">*</span></label><div class="input-group"><span class="input-group-text">€</span><input type="text" class="form-control" id="<?= $priceInputId ?>" name="price" value="<?= htmlspecialchars($priceEur) ?>" required><span class="input-group-text">EUR</span></div><div class="invalid-feedback">Please enter a valid price.</div></div>
            <div class="col-md-6"><label class="form-label">Stock <span class="oc-required">*</span></label><input type="number" class="form-control" name="stock" min="1" step="1" value="<?= $stock ?>" required><div class="invalid-feedback">Please enter stock.</div></div>
            <div class="col-md-6"><label class="form-label">Minimum quantity per order <span class="oc-required">*</span></label><input type="number" class="form-control" name="min_purchase_qty" min="1" step="1" value="<?= $minQty ?>" required><div class="invalid-feedback">Please enter a minimum quantity.</div></div>
            <div class="col-md-6"><label class="form-label">Waiting time <span class="oc-required">*</span></label><div class="input-group"><input type="number" class="form-control" name="waiting_time_amount" min="0" max="365" step="1" value="<?= max(0, (int)$waitingAmount) ?>" required><select class="form-select" name="waiting_time_unit" required><option value="minutes" <?= $waitingUnit === 'minutes' ? 'selected' : '' ?>>Minutes</option><option value="hours" <?= $waitingUnit === 'hours' ? 'selected' : '' ?>>Hours</option><option value="days" <?= $waitingUnit === 'days' ? 'selected' : '' ?>>Days</option></select></div><div class="form-text text-muted" style="font-size:.75rem;">Example: 0–60 minutes, 1–24 hours, or 7 days for LoL gifts.</div><div class="invalid-feedback">Please enter the waiting time.</div></div>
          </div>
        </div>
        <div class="js-item-step" data-step="4" style="display:none;">
          <div class="oc-section-label">Delivery Instructions</div>
          <div class="row g-2 mb-3">
            <div class="col-12">
              <label class="form-label">Delivery instructions for buyer email</label>
              <textarea class="form-control" rows="4" name="delivery_instructions" placeholder="Example: Add this account, wait 7 days, then send me your summoner name. The buyer will receive these instructions by email after purchase."><?= htmlspecialchars((string)($canvasItem['delivery_instructions'] ?? '')) ?></textarea>
            </div>
          </div>

          <div class="oc-section-label">Image Gallery</div>
          <div id="<?= $dropzoneId ?>" class="account-upload-box text-center p-3">
            <div class="mb-2"><i class="fa-duotone fa-images fa-xl text-primary"></i></div>
            <h6 class="mb-1" style="font-size:.88rem;font-weight:800;">Upload Item Images</h6>
            <p class="text-muted small mb-2" style="font-size:.78rem;">Click, drag & drop, or paste with <strong>Ctrl+V</strong></p>
            <button type="button" class="btn btn-primary btn-sm" id="<?= $fileInputId ?>Btn">Select Images</button>
            <input class="form-control d-none" name="images[]" type="file" id="<?= $fileInputId ?>" multiple accept="image/*" <?= $isEdit ? '' : 'required' ?>>
          </div>
          <div id="<?= $previewId ?>" class="row mt-3 g-2"></div>
        </div>
      </div>
      <div class="oc-footer">
        <button type="button" class="oc-btn-prev js-item-prev" style="display:none;">Previous</button>
        <div class="ms-auto d-flex gap-2"><button type="button" class="oc-btn-next js-item-next">Next</button><button type="submit" class="oc-btn-next js-item-submit" style="display:none;"><?= $isEdit ? 'Save Item' : 'Create Item' ?></button></div>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php echo $this->start('scripts'); ?>
<script>
document.addEventListener('click', function(e) {
  if (!e.target.closest('.al-actions-wrap')) {
    document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
      m.classList.remove('is-open');
      if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open');
    });
  }
});
window.alToggleMenu = function(btn) {
  var menu = btn.nextElementSibling;
  var isOpen = menu.classList.contains('is-open');
  document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open'); });
  if (!isOpen) {
    var rect = btn.getBoundingClientRect();
    menu.style.top  = (rect.bottom + 6) + 'px';
    menu.style.left = Math.max(8, rect.right - 190) + 'px';
    menu.classList.add('is-open');
    btn.classList.add('is-open');
  }
};
window.addEventListener('scroll', function() {
  document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
    var btn = m.previousElementSibling;
    var rect = btn.getBoundingClientRect();
    m.style.top  = (rect.bottom + 6) + 'px';
    m.style.left = Math.max(8, rect.right - 190) + 'px';
  });
}, true);
function alCloseMenus() {
  document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
    m.classList.remove('is-open');
    if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open');
  });
}
window.alToggleItem = function(btn, e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  alCloseMenus();
  var id = btn.getAttribute('data-id');
  var isActive = btn.getAttribute('data-active') === '1';
  var action = isActive ? 'Unlist' : 'Relist';
  if (!confirm(action + ' this item?')) return false;
  btn.disabled = true;
  $.post('<?= AJAX_URL ?>', { action: 'seller_toggle_item_active', id: id, active: isActive ? 0 : 1 }, function(resp) {
    var d = resp;
    try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(err) {}
    if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
    if (d && d.refreshPage) window.location.reload();
    else btn.disabled = false;
  }).fail(function(){
    if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not update the item.');
    btn.disabled = false;
  });
  return false;
};
window.alDeleteItem = function(btn, e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  alCloseMenus();
  if (!confirm('Delete this item? This cannot be undone.')) return false;
  btn.disabled = true;
  $.post('<?= AJAX_URL ?>', { action: 'seller_delete_item', id: btn.getAttribute('data-id') }, function(resp) {
    var d = resp;
    try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(err) {}
    if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
    if (d && d.refreshPage) window.location.reload();
    else btn.disabled = false;
  }).fail(function(){
    if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete the item.');
    btn.disabled = false;
  });
  return false;
};
document.addEventListener('click', function(e) {
  var btn;
  btn = e.target.closest('.js-toggle-item');
  if (btn) return alToggleItem(btn, e);
  btn = e.target.closest('.js-delete-item');
  if (btn) return alDeleteItem(btn, e);
});
(function () {
  var selected = new Set();
  var $bulkBtn = $('#alBulkDeleteBtn');
  var $bulkCnt = $('#alBulkCount');
  var $chkAll = $('#alChkAll');
  function updateUI() {
    var n = selected.size;
    $bulkCnt.text(n);
    if (n > 0) { $bulkBtn.css('display','inline-flex'); } else { $bulkBtn.hide(); }
    var $rows = $('.al-row-chk:not(:disabled)');
    var total = $rows.length;
    var checked = $rows.filter(function(){ return selected.has(String(this.value)); }).length;
    if (total === 0 || checked === 0) $chkAll.prop('checked', false).prop('indeterminate', false);
    else if (checked === total) $chkAll.prop('checked', true).prop('indeterminate', false);
    else $chkAll.prop('checked', false).prop('indeterminate', true);
  }
  $(document).on('change', '.al-row-chk', function(e) {
    e.stopPropagation();
    var id = String(this.value);
    if (this.checked) selected.add(id);
    else selected.delete(id);
    updateUI();
  });
  $chkAll.on('change', function() {
    var shouldCheck = this.checked;
    $('.al-row-chk:not(:disabled)').each(function() {
      var id = String(this.value);
      var $row = $(this).closest('tr.al-row');
      if ($row.is(':visible')) {
        this.checked = shouldCheck;
        if (shouldCheck) selected.add(id);
        else selected.delete(id);
      }
    });
    updateUI();
  });
  $bulkBtn.on('click', function() {
    if (!selected.size) return;
    var ids = Array.from(selected).map(function(v){ return parseInt(v, 10); }).filter(function(n){ return isFinite(n); });
    if (!ids.length) return;
    if (!confirm('Delete ' + ids.length + ' selected item(s)? This cannot be undone.')) return;
    $bulkBtn.prop('disabled', true);
    $.ajax({
      type: 'post',
      url: '<?= AJAX_URL ?>',
      // No traditional serialization: ids=1&ids=2 collapses to one value in PHP.
      data: { action: 'seller_bulk_delete_items', ids: ids },
      dataType: 'text',
      success: function(response) {
        var d = response;
        try { if (typeof response === 'string') d = JSON.parse(response); } catch(err) {}
        if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
        selected.clear();
        if (d && d.refreshPage) window.location.reload();
        else { $bulkBtn.prop('disabled', false); updateUI(); }
      },
      error: function() {
        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete items.');
        $bulkBtn.prop('disabled', false);
      }
    });
  });
  window.alItemsBulkSelectionSync = function() {
    $('.al-row-chk').each(function() {
      this.checked = !this.disabled && selected.has(String(this.value));
    });
    updateUI();
  };
  updateUI();
})();
(function(){
        var PER_PAGE  = 20;
        var filter    = 'all';
        var typeFilter = '';
        var search    = '';
        var page      = 1;
        var sortCol   = 'id';
        var sortDir   = 'desc';

        var tbody     = document.getElementById('alTbody');
        var allRows   = tbody ? Array.from(tbody.querySelectorAll('.al-row')) : [];
        var showEl    = document.getElementById('alShowing');
        var totEl     = document.getElementById('alTotal');
        var pageEl    = document.getElementById('alPagination');
        var srchEl    = document.getElementById('alSearch');
        var pills     = document.querySelectorAll('#alStatusFilters .al-pill');
        var typePills = document.querySelectorAll('#alTypeFilters .al-pill');
        var ths       = document.querySelectorAll('.al-table thead th.sortable');

        function getSorted(arr){
            return arr.slice().sort(function(a,b){
                var av=a.dataset[sortCol]||'', bv=b.dataset[sortCol]||'';
                var an=parseFloat(av), bn=parseFloat(bv);
                var cmp = isNaN(an)||isNaN(bn) ? String(av).localeCompare(String(bv), undefined, {numeric:true, sensitivity:'base'}) : an-bn;
                return sortDir==='asc' ? cmp : -cmp;
            });
        }

        function getFiltered(){
            return allRows.filter(function(c){
                var okStatus = filter === 'all' || c.dataset.status === filter;
                var okType   = !typeFilter || (c.dataset.type || '') === typeFilter;
                var okSearch = !search || (c.dataset.search||'').indexOf(search) !== -1;
                return okStatus && okType && okSearch;
            });
        }

        function render(){
            var filtered = getSorted(getFiltered());
            var total    = filtered.length;
            var pages    = Math.max(1, Math.ceil(total / PER_PAGE));
            if(page > pages) page = pages;
            var start = (page-1)*PER_PAGE, end = start+PER_PAGE;

            allRows.forEach(function(c){ c.style.display='none'; });

            var visible = filtered.slice(start,end);
            visible.forEach(function(c){ tbody.appendChild(c); c.style.display=''; });

            if(showEl) showEl.textContent = total>0 ? (start+1)+'–'+Math.min(end,total) : '0';
            if(totEl)  totEl.textContent  = total;

            ths.forEach(function(th){
                th.classList.remove('sort-asc','sort-desc');
                if(th.dataset.col===sortCol) th.classList.add('sort-'+sortDir);
            });

            if(typeof window.alItemsBulkSelectionSync === 'function') window.alItemsBulkSelectionSync();

            if(!pageEl) return;
            pageEl.innerHTML='';
            if(pages<=1) return;

            function btn(label,p,disabled,active){
                var b=document.createElement('button');
                b.className='al-pg-btn'+(active?' al-pg-active':'');
                b.innerHTML=label;
                b.disabled=!!disabled;
                if(!disabled) b.addEventListener('click',function(){page=p;render();});
                return b;
            }

            pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>',page-1,page===1,false));
            for(var i=1;i<=pages;i++){
                if(pages>7&&i>2&&i<pages-1&&Math.abs(i-page)>1){
                    if(i===3||i===pages-2){
                        var d=document.createElement('span');
                        d.style.cssText='color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;';
                        d.textContent='…';
                        pageEl.appendChild(d);
                    }
                    continue;
                }
                pageEl.appendChild(btn(i,i,false,i===page));
            }
            pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>',page+1,page===pages,false));
        }

        pills.forEach(function(p){
            p.addEventListener('click',function(){
                pills.forEach(function(x){x.classList.remove('active');});
                p.classList.add('active');
                filter = p.dataset.status || 'all';
                page = 1;
                render();
            });
        });

        typePills.forEach(function(p){
            p.addEventListener('click',function(){
                var isActive = p.classList.contains('active');
                typePills.forEach(function(x){x.classList.remove('active');});
                if(isActive){
                    typeFilter = '';
                } else {
                    p.classList.add('active');
                    typeFilter = p.dataset.type || '';
                }
                page = 1;
                render();
            });
        });

        if(srchEl) srchEl.addEventListener('input',function(){
            search = srchEl.value.trim().toLowerCase();
            page = 1;
            render();
        });

        ths.forEach(function(th){
            th.addEventListener('click',function(){
                var col=th.dataset.col;
                if(sortCol===col) sortDir = sortDir==='asc'?'desc':'asc';
                else {
                    sortCol=col;
                    sortDir = (col === 'price' || col === 'earnings') ? 'desc' : 'desc';
                }
                page=1;
                render();
            });
        });

        render();
    })();

document.querySelectorAll('.item-canvas').forEach(function(canvas){
  if (canvas.dataset.itemCanvasInit === '1') return;
  canvas.dataset.itemCanvasInit = '1';
  var form = canvas.querySelector('form');
  var priceInput = form.querySelector('input[name="price"]');
  var earningsEl = canvas.querySelector('[id^="itemEarningsPreview"]');
  var steps = Array.from(canvas.querySelectorAll('.js-item-step'));
  var prevBtn = canvas.querySelector('.js-item-prev');
  var nextBtn = canvas.querySelector('.js-item-next');
  var submitBtn = canvas.querySelector('.js-item-submit');
  var scrollArea = canvas.querySelector('.oc-scroll');
  var currentStep = 1;
  function updateEarnings(){ if (!priceInput || !earningsEl) return; var raw = String(priceInput.value || '0').replace(',', '.'); var value = parseFloat(raw); var safe = isNaN(value) ? 0 : value; earningsEl.textContent = (safe * (1 - <?= json_encode($effective_fee) ?> / 100)).toFixed(2); }
  if (priceInput) { priceInput.addEventListener('input', updateEarnings); updateEarnings(); }
  var waitingAmountInput = form.querySelector('input[name="waiting_time_amount"]');
  var waitingUnitSelect = form.querySelector('select[name="waiting_time_unit"]');
  function syncWaitingTimeLimits(){
    if (!waitingAmountInput || !waitingUnitSelect) return;
    if (waitingUnitSelect.value === 'minutes') {
      waitingAmountInput.min = '0';
      waitingAmountInput.max = '60';
      if (parseInt(waitingAmountInput.value || '0', 10) > 60) waitingAmountInput.value = '60';
    } else if (waitingUnitSelect.value === 'hours') {
      waitingAmountInput.min = '1';
      waitingAmountInput.max = '24';
      if (parseInt(waitingAmountInput.value || '0', 10) < 1) waitingAmountInput.value = '1';
      if (parseInt(waitingAmountInput.value || '0', 10) > 24) waitingAmountInput.value = '24';
    } else {
      waitingAmountInput.min = '1';
      waitingAmountInput.max = '365';
      if (parseInt(waitingAmountInput.value || '0', 10) < 1) waitingAmountInput.value = '1';
    }
  }
  if (waitingUnitSelect) waitingUnitSelect.addEventListener('change', syncWaitingTimeLimits);
  syncWaitingTimeLimits();
  function markStepState(){ for (var i = 1; i <= 4; i++) { var stepEl = canvas.querySelector('#' + canvas.id + 'Step' + i); if (!stepEl) continue; stepEl.classList.remove('active', 'done'); var num = stepEl.querySelector('.oc-step-num'); if (i < currentStep) { stepEl.classList.add('done'); if (num) num.innerHTML = '<i class="fa-solid fa-check" style="font-size:.7rem;"></i>'; } else if (i === currentStep) { stepEl.classList.add('active'); if (num) num.textContent = i; } else { if (num) num.textContent = i; } } var line1 = canvas.querySelector('#' + canvas.id + 'Line1'); var line2 = canvas.querySelector('#' + canvas.id + 'Line2'); var line3 = canvas.querySelector('#' + canvas.id + 'Line3'); if (line1) line1.classList.toggle('done', currentStep > 1); if (line2) line2.classList.toggle('done', currentStep > 2); if (line3) line3.classList.toggle('done', currentStep > 3); }
  function showStep(n){ currentStep = n; steps.forEach(function(step){ step.style.display = parseInt(step.dataset.step, 10) === n ? '' : 'none'; }); if (prevBtn) prevBtn.style.display = n > 1 ? '' : 'none'; if (nextBtn) nextBtn.style.display = n < 4 ? '' : 'none'; if (submitBtn) submitBtn.style.display = n === 4 ? '' : 'none'; markStepState(); if (scrollArea) scrollArea.scrollTop = 0; }
  function validateStep(stepNumber){
    var step = steps.find(function(s){ return parseInt(s.dataset.step, 10) === stepNumber; });
    if (!step) return true;
    var fields = Array.from(step.querySelectorAll('input, select, textarea')).filter(function(el){ return el.type !== 'hidden' && !el.disabled && el.required; });

    fields.forEach(function(el){
      el.classList.remove('is-invalid');
      if (el.name === 'title' || el.name === 'description') {
        if (!String(el.value || '').trim()) el.value = String(el.value || '').trim();
      }
      if (el.name === 'price') {
        var raw = String(el.value || '').replace(',', '.').trim();
        var num = parseFloat(raw);
        if (!raw || isNaN(num) || num <= 0) {
          el.setCustomValidity('Please enter a valid price.');
        } else {
          el.setCustomValidity('');
        }
      } else if (el.name === 'stock' || el.name === 'min_purchase_qty' || el.name === 'waiting_time_amount') {
        var val = String(el.value || '').trim();
        if (!val) {
          el.setCustomValidity('This field is required.');
        } else {
          el.setCustomValidity('');
        }
      } else {
        el.setCustomValidity(el.value ? '' : 'This field is required.');
      }
    });

    for (var i = 0; i < fields.length; i++) {
      var el = fields[i];
      if (!el.checkValidity()) {
        el.classList.add('is-invalid');
        if (typeof el.reportValidity === 'function') el.reportValidity();
        try { el.focus({preventScroll:false}); } catch(e) { el.focus(); }
        return false;
      }
    }
    return true;
  }
  if (nextBtn) nextBtn.addEventListener('click', function(){ if (validateStep(currentStep)) showStep(Math.min(4, currentStep + 1)); });
  if (prevBtn) prevBtn.addEventListener('click', function(){ showStep(Math.max(1, currentStep - 1)); });
  form.querySelectorAll('input, select, textarea').forEach(function(el){
    el.addEventListener('input', function(){ el.classList.remove('is-invalid'); el.setCustomValidity(''); });
    el.addEventListener('change', function(){ el.classList.remove('is-invalid'); el.setCustomValidity(''); });
  });
  var fileInput = form.querySelector('input[type="file"]');
  var fileBtn = canvas.querySelector('[id$="Btn"]');
  var dropzone = canvas.querySelector('.account-upload-box');
  var preview = canvas.querySelector('[id^="previewGalleryItem"]');
  var orderInput = form.querySelector('input[name="images_order"]');
  var existingInput = form.querySelector('input[name="existing_images_json"]');
  var galleryItems = [];
  var dragFromIndex = null;
  var tempSeq = 0;
  function parseExistingImages(raw){ try { var parsed = JSON.parse(raw || '[]'); return Array.isArray(parsed) ? parsed : []; } catch(e){ return []; } }
  function fileKey(f){ return f.name + '__' + f.size + '__' + f.lastModified; }
  function escHtml(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
  function syncInputAndRender(){
    if (!preview) return;
    var dt = new DataTransfer();
    galleryItems.filter(function(x){ return x.type === 'new' && x.file; }).forEach(function(x){ dt.items.add(x.file); });
    if (fileInput) fileInput.files = dt.files;
    if (orderInput) orderInput.value = JSON.stringify(galleryItems.map(function(x){ return x.type === 'existing' ? x.url : x.tempId; }));
    if (existingInput) existingInput.value = JSON.stringify(galleryItems.filter(function(x){ return x.type === 'existing'; }).map(function(x){ return x.url; }));
    preview.innerHTML = '';
    galleryItems.forEach(function(item, i){
      var src = item.type === 'existing' ? item.url : URL.createObjectURL(item.file);
      var col = document.createElement('div');
      col.className = 'col-6 col-md-3';
      col.innerHTML = '<div class="gallery-preview-tile ' + (i === 0 ? 'is-main' : '') + '" draggable="true" data-index="' + i + '">' +
        '<div class="gallery-preview-badge" style="left:auto;right:.5rem;background:rgba(0,0,0,.45)">#' + (i+1) + '</div>' +
        (i === 0 ? '<div class="gallery-preview-badge">MAIN</div>' : '') +
        '<img src="' + escHtml(src) + '" alt="Preview">' +
        '<div class="gallery-preview-hint">Drag to reorder</div>' +
        '<div class="gallery-preview-overlay"><button type="button" class="gallery-preview-remove" data-remove-index="' + i + '">×</button></div>' +
      '</div>';
      preview.appendChild(col);
    });
  }
  function addFiles(files){
    var incoming = Array.from(files || []).filter(function(f){ return f && f.type && f.type.indexOf('image/') === 0; });
    var existing = new Set(galleryItems.filter(function(x){ return x.type === 'new'; }).map(function(x){ return fileKey(x.file); }));
    incoming.forEach(function(f){ var k = fileKey(f); if (!existing.has(k)) { galleryItems.push({ type:'new', file:f, tempId:'__new__' + (tempSeq++) }); existing.add(k); } });
    syncInputAndRender();
  }
  if (fileInput) fileInput.addEventListener('change', function(){ addFiles(fileInput.files); });
  if (fileBtn) fileBtn.addEventListener('click', function(e){ e.preventDefault(); if (fileInput) fileInput.click(); });
  if (dropzone) {
    dropzone.addEventListener('click', function(e){ if (e.target.closest('button')) return; if (fileInput) fileInput.click(); });
    ['dragenter','dragover'].forEach(function(evt){ dropzone.addEventListener(evt, function(e){ e.preventDefault(); dropzone.classList.add('dragover'); }); });
    ['dragleave','drop'].forEach(function(evt){ dropzone.addEventListener(evt, function(e){ e.preventDefault(); dropzone.classList.remove('dragover'); }); });
    dropzone.addEventListener('drop', function(e){ addFiles((e.dataTransfer || {}).files || []); });
  }
  if (preview) {
    preview.addEventListener('click', function(e){ var btn = e.target.closest('.gallery-preview-remove'); if (!btn) return; var idx = parseInt(btn.getAttribute('data-remove-index'), 10); if (!isNaN(idx)) { galleryItems.splice(idx, 1); syncInputAndRender(); } });
    preview.addEventListener('dragstart', function(e){ var tile = e.target.closest('.gallery-preview-tile'); if (!tile) return; dragFromIndex = parseInt(tile.getAttribute('data-index'), 10); });
    preview.addEventListener('dragover', function(e){ if (dragFromIndex !== null) e.preventDefault(); });
    preview.addEventListener('drop', function(e){ if (dragFromIndex === null) return; e.preventDefault(); var tile = e.target.closest('.gallery-preview-tile'); if (!tile) return; var to = parseInt(tile.getAttribute('data-index'), 10); if (!isNaN(to) && to !== dragFromIndex) { var moved = galleryItems.splice(dragFromIndex, 1)[0]; galleryItems.splice(to, 0, moved); syncInputAndRender(); } dragFromIndex = null; });
    preview.addEventListener('dragend', function(){ dragFromIndex = null; });
  }
  canvas.addEventListener('paste', function(e){ var items = (e.clipboardData || {}).items || []; var files = []; for (var i = 0; i < items.length; i++) { if (items[i].kind === 'file') { var blob = items[i].getAsFile(); if (blob && blob.type && blob.type.indexOf('image/') === 0) files.push(blob); } } if (files.length) { e.preventDefault(); addFiles(files); } });
  galleryItems = parseExistingImages(existingInput ? existingInput.value : '').map(function(url){ return { type:'existing', url:url }; });
  syncInputAndRender();
  showStep(1);
});

(function(){
  document.querySelectorAll('.item-canvas').forEach(function(canvas){
    var gameInput = canvas.querySelector('.js-item-game-select');
    if (!gameInput) return;
    var cards = Array.from(canvas.querySelectorAll('.js-item-game-card'));
    var search = canvas.querySelector('.js-item-game-search');
    var empty = canvas.querySelector('.js-item-game-empty');
    var chip = canvas.querySelector('.js-item-game-chip');

    function setGame(game){
      game = String(game || '').toLowerCase();
      if (!game && cards[0]) game = String(cards[0].dataset.game || '').toLowerCase();
      gameInput.value = game;
      cards.forEach(function(card){
        var active = String(card.dataset.game || '').toLowerCase() === game;
        card.classList.toggle('selected', active);
        var radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = active;
      });
      syncGameFields();
      updateGameChip();
      try { gameInput.dispatchEvent(new Event('change', {bubbles:true})); } catch(e) {}
    }

    function updateGameChip(){
      if (!chip) return;
      var selected = cards.find(function(card){ return String(card.dataset.game || '').toLowerCase() === String(gameInput.value || '').toLowerCase(); });
      if (!selected) { chip.innerHTML = '<span>Choose game</span>'; return; }
      var name = selected.dataset.name || selected.dataset.game || 'Game';
      var icon = selected.dataset.icon || '';
      chip.innerHTML = (icon ? '<img src="' + icon.replace(/"/g, '&quot;') + '" alt="">' : '') + '<span>' + name.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
    }

    function syncGameFields(){
      var game = String(gameInput.value || '').toLowerCase();
      canvas.querySelectorAll('.js-item-game-fields').forEach(function(group){
        var active = String(group.dataset.game || '').toLowerCase() === game;
        group.style.display = active ? '' : 'none';
        group.querySelectorAll('input,select,textarea').forEach(function(el){ el.disabled = !active; });
      });
    }

    function filterGames(){
      var q = search ? String(search.value || '').trim().toLowerCase() : '';
      var visible = 0;
      cards.forEach(function(card){
        var ok = !q || String(card.dataset.search || '').indexOf(q) !== -1;
        card.classList.toggle('is-hidden', !ok);
        if (ok) visible++;
      });
      if (empty) empty.style.display = visible ? 'none' : 'block';
    }

    cards.forEach(function(card){
      card.addEventListener('click', function(e){
        e.preventDefault();
        setGame(card.dataset.game || '');
      });
    });
    if (search) search.addEventListener('input', filterGames);
    canvas.addEventListener('shown.bs.offcanvas', function(){ setGame(gameInput.value); filterGames(); });
    setGame(gameInput.value);
    filterGames();
  });
})();

</script>
<?php echo $this->end(); ?>
