<?php echo $this->layout('seller/layouts/main', ['meta' => ['title' => 'My Top Ups | LoLBoost.gg']]); ?>

<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
$effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$topups = is_array($topups ?? null) ? $topups : [];
$topupGames = is_array($topupGames ?? null) ? $topupGames : [];
$topupConfigs = is_array($topupConfigs ?? null) ? $topupConfigs : [];
$topupSchemas = is_array($topupSchemas ?? null) ? $topupSchemas : [];

$sellerTopupRegionOptions = [];
$sellerTopupOfferPresets = [];
foreach ($topupSchemas as $schemaSlug => $schemaRow) {
    if (!is_array($schemaRow)) continue;
    $slugKey = strtolower(trim((string)$schemaSlug));
    $regions = [];
    foreach (($schemaRow['regions'] ?? []) as $regionRow) {
        if (is_array($regionRow)) {
            $value = trim((string)($regionRow['value'] ?? $regionRow['key'] ?? $regionRow['slug'] ?? $regionRow['label'] ?? ''));
            $label = trim((string)($regionRow['label'] ?? $regionRow['name'] ?? $value));
        } else {
            $value = trim((string)$regionRow);
            $label = $value;
        }
        if ($value !== '') $regions[$value] = $label !== '' ? $label : $value;
    }
    if ($regions) $sellerTopupRegionOptions[$slugKey] = $regions;
    $presets = [];
    foreach (($schemaRow['offer_presets'] ?? $schemaRow['offers'] ?? []) as $presetRow) {
        if (!is_array($presetRow)) continue;
        $title = trim((string)($presetRow['title'] ?? $presetRow['offer_title'] ?? ''));
        if ($title === '') continue;
        $presets[] = [
            'key' => (string)($presetRow['offer_key'] ?? $presetRow['external_id'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'))),
            'title' => $title,
            'amount' => (string)($presetRow['amount'] ?? $presetRow['offer_amount'] ?? ''),
            'unit' => (string)($presetRow['unit'] ?? $presetRow['offer_unit'] ?? ''),
            'price' => isset($presetRow['price']) ? number_format(((int)$presetRow['price']) / 100, 2, '.', '') : '',
            'price_cents' => (int)($presetRow['price'] ?? 0),
            'image' => (string)($presetRow['image'] ?? $presetRow['local_image_path'] ?? $presetRow['image_url'] ?? ''),
            'popular' => (int)($presetRow['popular'] ?? 0),
        ];
    }
    if ($presets) $sellerTopupOfferPresets[$slugKey] = $presets;
}

if (!function_exists('seller_topup_money')) {
    
function lb_topup_amount_clean($value): string
{
    $raw = trim((string)$value);
    if ($raw === '') return '';
    if (is_numeric($raw)) {
        $num = (float)$raw;
        return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
    }
    return $raw;
}
function seller_topup_money($cents): string
    {
        return '€' . number_format(((int)$cents) / 100, 2, '.', ',');
    }
}

if (!function_exists('seller_topup_wait')) {
    function seller_topup_wait(array $row): string
    {
        $value = (int)($row['waiting_time_value'] ?? 0);
        $unit = strtolower((string)($row['waiting_time_unit'] ?? 'minutes'));
        if (!in_array($unit, ['minutes', 'hours', 'days'], true)) {
            $unit = 'minutes';
        }
        $label = $value === 1 ? rtrim($unit, 's') : $unit;
        return $value . ' ' . $label;
    }
}

if (!function_exists('seller_topup_game_name')) {
    function seller_topup_game_name(array $row): string
    {
        $name = trim((string)($row['game_name'] ?? ''));
        if ($name !== '') return $name;
        $name = trim((string)($row['db_game_name'] ?? ''));
        if ($name !== '') return $name;
        return trim((string)($row['game_slug'] ?? 'Game'));
    }
}



if (!function_exists('seller_topup_region_options')) {
    function seller_topup_region_options(string $gameSlug): array
    {
        global $sellerTopupRegionOptions;
        $slug = strtolower(trim($gameSlug));
        if (!empty($sellerTopupRegionOptions[$slug]) && is_array($sellerTopupRegionOptions[$slug])) {
            return $sellerTopupRegionOptions[$slug];
        }
        if (in_array($slug, ['league-of-legends', 'lol', 'league'], true)) {
            return [
                'euw' => 'EU-West',
                'eune' => 'EU-Nordic & East',
                'na' => 'North America',
                'br' => 'Brazil',
                'lan' => 'Latin America North',
                'las' => 'Latin America South',
                'oce' => 'Oceania',
                'ru' => 'Russia',
                'tr' => 'Turkey',
                'jp' => 'Japan',
                'kr' => 'Korea',
                'pbe' => 'PBE',
                'me' => 'Middle East',
                'vn' => 'Vietnam',
                'ph' => 'Philippines',
                'sg' => 'Singapore',
                'th' => 'Thailand',
                'tw' => 'Taiwan',
            ];
        }
        return ['Global' => 'Global'];
    }
}

if (!function_exists('seller_topup_region_label')) {
    function seller_topup_region_label($region, string $gameSlug = ''): string
    {
        $raw = trim((string)$region);
        if ($raw === '') return 'Global';
        $opts = seller_topup_region_options($gameSlug);
        $key = strtolower($raw);
        if (isset($opts[$key])) return $opts[$key];
        foreach ($opts as $v => $label) {
            if (strtolower((string)$label) === strtolower($raw) || strtoupper((string)$v) === strtoupper($raw)) return $label;
        }
        return $raw;
    }
}

$topupLolRpPresets = [13500,10875,6500,5295,4500,3625,2800,2105,1380,1005,574,460];
$topupLolRpPresetRows = [];
foreach ($topupLolRpPresets as $rp) {
    $topupLolRpPresetRows[] = [
        'amount' => (string)$rp,
        'unit' => 'RP',
        'title' => $rp . ' Riot Points',
        'image' => '/public/assets/website/images/league-of-legends/riot-points/rp' . $rp . '.webp',
    ];
}

$topupsJson = [];
foreach ($topups as $t) {
    $topupsJson[(int)$t['id']] = [
        'id' => (int)$t['id'],
        'game_id' => (int)($t['game_id'] ?? 0),
        'game_slug' => (string)($t['game_slug'] ?? ''),
        'offer_title' => (string)($t['offer_title'] ?? ''),
        'offer_amount' => (string)($t['offer_amount'] ?? ''),
        'offer_unit' => (string)($t['offer_unit'] ?? ''),
        'region' => (string)($t['region'] ?? 'Global'),
        'platform' => (string)($t['platform'] ?? ''),
        'price' => number_format(((int)($t['price'] ?? 0)) / 100, 2, '.', ''),
        'stock' => (int)($t['stock'] ?? 0),
        'min_quantity' => (int)($t['min_quantity'] ?? 1),
        'waiting_time_value' => (int)($t['waiting_time_value'] ?? 0),
        'waiting_time_unit' => (string)($t['waiting_time_unit'] ?? 'minutes'),
        'image' => (string)($t['image'] ?? ''),
        'instructions' => (string)($t['instructions'] ?? ''),
        'active' => (int)($t['active'] ?? 1),
    ];
}

$topupGameRegions = [];
foreach ($topupGames as $g) {
    $gid = (int)($g['id'] ?? 0);
    if ($gid <= 0) continue;
    $opts = seller_topup_region_options((string)($g['slug'] ?? ''));
    $topupGameRegions[$gid] = [];
    foreach ($opts as $value => $label) {
        $topupGameRegions[$gid][] = ['value' => (string)$value, 'label' => (string)$label];
    }
}

$topupGamePresets = [];
foreach ($topupGames as $g) {
    $gid = (int)($g['id'] ?? 0);
    $slug = strtolower(trim((string)($g['slug'] ?? '')));
    if ($gid <= 0 || $slug === '') continue;
    $topupGamePresets[$gid] = $sellerTopupOfferPresets[$slug] ?? [];
}
?>

<?php $this->start('styles') ?>
<style>
.topup-page{display:flex;flex-direction:column;gap:18px;}
.topup-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:20px 22px;border:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(124,92,255,.10),rgba(255,255,255,.025));border-radius:18px;box-shadow:0 18px 44px rgba(0,0,0,.20);}
.topup-head__meta{display:flex;align-items:center;gap:14px;min-width:0;}
.topup-head__icon{width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:rgba(124,92,255,.18);border:1px solid rgba(124,92,255,.25);color:#b8a7ff;font-size:1.15rem;}
.topup-head h1{margin:0;font-size:1.28rem;font-weight:950;color:#fff;}
.topup-muted{color:rgba(255,255,255,.52);font-size:.82rem;}
.topup-btn-primary{border:0;border-radius:12px;background:linear-gradient(135deg,#6d5cff,#b05cff);color:#fff;font-weight:900;padding:12px 18px;box-shadow:0 12px 26px rgba(109,92,255,.20);}
.topup-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025);border-radius:16px;}
.topup-tabs{display:flex;gap:8px;flex-wrap:wrap;}
.topup-tab{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:999px;color:rgba(255,255,255,.62);font-weight:850;font-size:.8rem;padding:8px 14px;}
.topup-tab.active{border-color:rgba(124,92,255,.55);background:rgba(124,92,255,.14);color:#cabdff;}
.topup-search{position:relative;width:min(280px,100%);}
.topup-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.38);font-size:.85rem;}
.topup-search input{width:100%;height:38px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.16);border-radius:12px;color:#fff;padding:0 13px 0 38px;outline:none;}
.topup-panel{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025);border-radius:18px;overflow:hidden;}
.topup-table{width:100%;border-collapse:collapse;}
.topup-table th{padding:14px 14px;background:rgba(255,255,255,.035);color:rgba(255,255,255,.43);font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;font-weight:900;white-space:nowrap;}
.topup-table td{padding:14px;border-top:1px solid rgba(255,255,255,.06);vertical-align:middle;color:rgba(255,255,255,.82);}
.topup-table tr:hover td{background:rgba(255,255,255,.018);}
.topup-id{font-weight:900;color:rgba(255,255,255,.35);}
.topup-game{display:flex;align-items:center;gap:10px;min-width:160px;}
.topup-game img,.topup-game__fallback{width:38px;height:38px;border-radius:12px;object-fit:cover;border:1px solid rgba(255,255,255,.08);background:rgba(124,92,255,.12);display:flex;align-items:center;justify-content:center;color:#b8a7ff;}
.topup-offer{display:flex;align-items:center;gap:10px;min-width:210px;}
.topup-offer img{width:44px;height:44px;border-radius:12px;object-fit:cover;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.18);}
.topup-offer strong{display:block;color:#fff;font-weight:950;line-height:1.25;}
.topup-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.22);color:#d8d0ff;font-weight:850;font-size:.75rem;padding:6px 9px;white-space:nowrap;}
.topup-price strong{color:#fff;font-size:1rem;}
.topup-earnings{color:#42e685;font-weight:950;}
.topup-status{display:inline-flex;border-radius:999px;padding:6px 10px;font-size:.72rem;font-weight:950;border:1px solid rgba(34,197,94,.30);background:rgba(34,197,94,.12);color:#4ade80;}
.topup-status.off{border-color:rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:rgba(255,255,255,.48);}
.topup-actions{display:flex;justify-content:flex-end;gap:8px;}
.topup-action{width:34px;height:34px;border-radius:10px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.05);color:#fff;display:inline-flex;align-items:center;justify-content:center;}
.topup-action.danger{color:#ff7979;background:rgba(255,80,80,.08);border-color:rgba(255,80,80,.16);}
.topup-empty{padding:56px 18px;text-align:center;color:rgba(255,255,255,.48);}.topup-img-preview{margin-top:10px;width:112px;height:72px;border-radius:14px;background-size:cover;background-position:center;border:1px solid rgba(255,255,255,.10);background-color:rgba(0,0,0,.18);}
.topup-canvas.custom-offcanvas{width:50vw!important;display:flex!important;flex-direction:column!important;height:100%!important;}
.topup-canvas .offcanvas-header{flex-shrink:0;padding:18px 22px;border-bottom:1px solid var(--bs-card-border-color);}
.topup-canvas .offcanvas-title{font-weight:950;color:#fff;}
.topup-earnings-bar{flex-shrink:0;padding:10px 22px;background:rgba(109,92,255,.08);border-bottom:1px solid rgba(109,92,255,.18);display:flex;align-items:center;gap:8px;font-size:.83rem;color:rgba(255,255,255,.70);}
.topup-steps{flex-shrink:0;display:flex;align-items:center;padding:14px 22px;border-bottom:1px solid var(--bs-card-border-color);gap:0;}
.topup-step{display:flex;align-items:center;gap:8px;flex:1;min-width:0;}
.topup-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:900;flex-shrink:0;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.5);}
.topup-step.active .topup-step-num{background:linear-gradient(135deg,#6d5cff,#b05cff);border-color:transparent;color:#fff;}
.topup-step-label{font-size:.8rem;font-weight:800;color:rgba(255,255,255,.42);white-space:nowrap;}
.topup-step.active .topup-step-label{color:#c4b5fd;font-weight:950;}
.topup-step-line{flex:1;height:1px;background:rgba(255,255,255,.08);margin:0 8px;}
.topup-canvas .offcanvas-body{flex:1!important;overflow:hidden!important;display:flex!important;flex-direction:column!important;padding:0!important;}
.topup-form{flex:1;display:flex;flex-direction:column;overflow:hidden;min-height:0;}
.topup-form-scroll{flex:1;overflow:auto;padding:22px;}
.topup-section{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025);border-radius:16px;margin-bottom:16px;overflow:hidden;}
.topup-section-title{display:flex;align-items:center;gap:9px;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.06);font-weight:950;color:#fff;}
.topup-section-body{padding:16px;}
.topup-game-picker{border:1px solid rgba(255,255,255,.08);border-radius:16px;background:rgba(255,255,255,.025);overflow:hidden;}
.topup-game-picker__top{display:flex;align-items:center;gap:12px;padding:12px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025);}
.topup-game-search{position:relative;flex:1;min-width:0;}
.topup-game-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.34);font-size:.82rem;}
.topup-game-search input{width:100%;height:40px;border-radius:12px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.16);color:#fff;padding:0 14px 0 38px;outline:none;}
.topup-game-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding:12px;max-height:320px;overflow:auto;}
.topup-game-card{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:14px;padding:12px;display:flex;align-items:center;gap:11px;color:#fff;text-align:left;transition:.18s ease;width:100%;}
.topup-game-card:hover{border-color:rgba(124,92,255,.35);background:rgba(124,92,255,.08);}
.topup-game-card.active{border-color:rgba(124,92,255,.70);background:rgba(124,92,255,.16);box-shadow:inset 0 0 0 1px rgba(124,92,255,.18);}
.topup-game-card img,.topup-game-card__icon{width:38px;height:38px;border-radius:11px;object-fit:cover;background:rgba(0,0,0,.18);display:flex;align-items:center;justify-content:center;color:#b8a7ff;}
.topup-game-card strong{display:block;font-size:.9rem;line-height:1.15;}
.topup-game-card small{color:rgba(255,255,255,.45);font-weight:750;}
.topup-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.topup-field label{display:flex;align-items:center;gap:4px;margin-bottom:7px;font-size:.78rem;font-weight:850;color:rgba(255,255,255,.62);}
.topup-field input,.topup-field select,.topup-field textarea{width:100%;border-radius:12px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.18);color:#fff;padding:12px 13px;outline:none;}
.topup-field textarea{min-height:96px;resize:vertical;}
.topup-field input:focus,.topup-field select:focus,.topup-field textarea:focus{border-color:rgba(124,92,255,.55);box-shadow:0 0 0 3px rgba(124,92,255,.12);}
.topup-inline{display:grid;grid-template-columns:1fr 150px;gap:10px;}
.topup-help{margin-top:6px;font-size:.74rem;color:rgba(255,255,255,.42);}
.topup-footer{flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 22px;border-top:1px solid var(--bs-card-border-color);background:var(--bs-offcanvas-bg,#1e2028);}
.topup-save{border:0;border-radius:12px;background:linear-gradient(135deg,#6d5cff,#b05cff);color:#fff;font-weight:950;padding:12px 20px;}

.topup-field select{appearance:none;-webkit-appearance:none;background:#171a21;color:#fff;color-scheme:dark;}
.topup-field select option{background:#171a21;color:#fff;}
.topup-section[data-step-panel]{display:none;}
.topup-section[data-step-panel].active{display:block;}
.topup-step.done .topup-step-num{background:rgba(34,197,94,.18);border-color:rgba(34,197,94,.42);color:#5cf08a;font-size:0;}
.topup-step.done .topup-step-num::before{content:'✓';font-size:.78rem;}
.topup-select-ui{position:relative;}
.topup-select-button{width:100%;height:46px;border:1px solid rgba(124,92,255,.42);background:#171a21;border-radius:12px;color:#fff;padding:0 42px 0 13px;display:flex;align-items:center;justify-content:space-between;font-weight:850;text-align:left;position:relative;}
.topup-select-button i{color:rgba(255,255,255,.66);position:absolute;right:14px;top:50%;transform:translateY(-50%);pointer-events:none;}
.topup-select-menu{position:fixed;z-index:99999;border:1px solid rgba(124,92,255,.42);background:#11141c;border-radius:14px;box-shadow:0 28px 70px rgba(0,0,0,.62);padding:8px;display:none;max-height:min(430px,calc(100vh - 120px));overflow:hidden;}
.topup-select-ui.open .topup-select-menu{display:block;}
.topup-select-search{position:sticky;top:0;background:#11141c;padding:0 0 8px;z-index:2;}
.topup-select-search input{width:100%;height:40px;border-radius:11px;border:1px solid rgba(255,255,255,.10);background:#181b24;color:#fff;padding:0 12px 0 36px;outline:none;}
.topup-select-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.42);font-size:.82rem;}
.topup-select-list{max-height:330px;overflow:auto;padding-right:2px;}
.topup-select-option{width:100%;border:0;background:transparent;color:rgba(255,255,255,.78);padding:10px 12px;border-radius:10px;text-align:left;font-weight:800;display:flex;align-items:center;justify-content:space-between;}
.topup-select-option i{opacity:0;color:#7c5cff;}
.topup-select-option:hover,.topup-select-option.active{background:rgba(124,92,255,.18);color:#fff;}
.topup-select-option.active i{opacity:1;}
.topup-dropzone{border:1px dashed rgba(255,255,255,.18);background:rgba(255,255,255,.025);border-radius:16px;min-height:152px;display:flex;align-items:center;justify-content:center;text-align:center;padding:20px;cursor:pointer;transition:.18s ease;}
.topup-dropzone:hover,.topup-dropzone.dragover{border-color:rgba(124,92,255,.58);background:rgba(124,92,255,.08);}
.topup-dropzone i{font-size:1.5rem;color:#7c5cff;margin-bottom:8px;}
.topup-dropzone strong{display:block;color:#fff;font-weight:950;margin-bottom:4px;}
.topup-dropzone span{color:rgba(255,255,255,.52);font-size:.8rem;}
.topup-img-preview{margin-top:12px;width:160px;height:96px;border-radius:14px;background-size:cover;background-position:center;border:1px solid rgba(124,92,255,.35);background-color:rgba(0,0,0,.18);}
.topup-footer-actions{display:flex;align-items:center;gap:10px;}
.topup-save.secondary{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);box-shadow:none;}


.topup-rp-presets{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;}
.topup-rp-preset{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);color:#fff;border-radius:14px;padding:10px 12px;display:flex;align-items:center;gap:10px;text-align:left;font-weight:950;transition:.16s ease;min-height:60px;}
.topup-rp-preset img{width:42px;height:36px;object-fit:contain;filter:drop-shadow(0 7px 12px rgba(255,195,50,.15));}
.topup-rp-preset:hover{border-color:rgba(124,92,255,.45);background:rgba(124,92,255,.09);}
.topup-rp-preset.active{border-color:rgba(47,125,255,.82);background:linear-gradient(135deg,rgba(47,125,255,.24),rgba(124,92,255,.14));box-shadow:inset 0 0 0 1px rgba(47,125,255,.25);}
.topup-auto-image-note{display:flex;align-items:center;gap:9px;margin-top:8px;color:rgba(255,255,255,.54);font-size:.78rem;font-weight:750;}
.topup-auto-image-note i{color:#7c5cff;}
@media(max-width:920px){.topup-rp-presets{grid-template-columns:repeat(2,minmax(0,1fr));}}

@media(max-width:1200px){.topup-canvas.custom-offcanvas{width:72vw!important}.topup-table-wrap{overflow:auto}.topup-table{min-width:1040px}}
@media(max-width:768px){.topup-head,.topup-toolbar{align-items:stretch;flex-direction:column}.topup-search{width:100%}.topup-canvas.custom-offcanvas{width:100vw!important}.topup-game-list,.topup-grid{grid-template-columns:1fr}.topup-step-label{display:none}}

/* League of Legends RP preset picker */
.topup-rp-presets{grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}
.topup-rp-preset{min-height:76px;background:#111722;border-color:rgba(255,255,255,.09);padding:12px 14px;border-radius:16px;}
.topup-rp-preset img{width:56px;height:46px;object-fit:contain;filter:drop-shadow(0 10px 16px rgba(255,192,44,.18));}
.topup-rp-preset span{display:flex;flex-direction:column;gap:2px;line-height:1.1;}
.topup-rp-preset span small{color:rgba(255,255,255,.45);font-weight:800;font-size:.72rem;}
.topup-rp-preset.active{background:linear-gradient(135deg,rgba(47,125,255,.26),rgba(124,92,255,.16));border-color:#2f7dff;}
.topup-form.is-lol .topup-manual-rp-field input{background:rgba(0,0,0,.12);color:rgba(255,255,255,.58);pointer-events:none;}
.topup-form.is-lol .topup-manual-rp-field label:after{content:'auto';margin-left:6px;padding:2px 6px;border-radius:999px;background:rgba(47,125,255,.14);color:#8fb6ff;font-size:.66rem;font-weight:950;text-transform:uppercase;}
@media(max-width:920px){.topup-rp-presets{grid-template-columns:repeat(2,minmax(0,1fr));}}


/* Top Up offcanvas color refresh */
.topup-canvas.custom-offcanvas{
  background:linear-gradient(180deg,#171923 0%,#1b1f27 48%,#151821 100%)!important;
  color:#fff!important;
  border-left:1px solid rgba(124,92,255,.22)!important;
  box-shadow:-34px 0 90px rgba(0,0,0,.52)!important;
}
.topup-canvas .offcanvas-header{
  background:linear-gradient(135deg,rgba(124,92,255,.16),rgba(47,125,255,.06))!important;
  border-bottom:1px solid rgba(124,92,255,.22)!important;
}
.topup-canvas .btn-close{
  filter:invert(1) grayscale(1);
  opacity:.65;
}
.topup-canvas .btn-close:hover{opacity:1;}
.topup-earnings-bar{
  background:linear-gradient(90deg,rgba(14,165,233,.12),rgba(124,92,255,.13),rgba(20,184,166,.08))!important;
  border-bottom:1px solid rgba(124,92,255,.24)!important;
  color:rgba(255,255,255,.78)!important;
}
.topup-earnings-bar i{color:#21e7c4!important;filter:drop-shadow(0 0 10px rgba(33,231,196,.24));}
.topup-steps{
  background:#191d25!important;
  border-bottom:1px solid rgba(255,255,255,.075)!important;
}
.topup-step-line{background:linear-gradient(90deg,rgba(124,92,255,.30),rgba(255,255,255,.08))!important;}
.topup-step-num{background:#242934!important;border-color:rgba(255,255,255,.12)!important;color:rgba(255,255,255,.56)!important;}
.topup-step.active .topup-step-num{background:linear-gradient(135deg,#3577ff,#8b5cf6)!important;box-shadow:0 9px 22px rgba(53,119,255,.24)!important;color:#fff!important;}
.topup-step.done .topup-step-num{background:rgba(34,197,94,.16)!important;border-color:rgba(34,197,94,.42)!important;color:#5cf08a!important;}
.topup-step-label{color:rgba(255,255,255,.42)!important;}
.topup-step.active .topup-step-label{color:#9fbfff!important;}
.topup-form-scroll{background:radial-gradient(circle at 18% 0%,rgba(47,125,255,.09),transparent 34%),radial-gradient(circle at 95% 18%,rgba(124,92,255,.08),transparent 32%),#1b1f26!important;}
.topup-section{
  background:linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.018))!important;
  border:1px solid rgba(124,92,255,.16)!important;
  box-shadow:0 18px 42px rgba(0,0,0,.20)!important;
}
.topup-section-title{
  background:#20252d!important;
  border-bottom:1px solid rgba(124,92,255,.16)!important;
}
.topup-section-title i{color:#5b8cff!important;background:rgba(47,125,255,.12);width:20px;height:20px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;}
.topup-field label{color:#b6c1d5!important;}
.topup-field input,.topup-field select,.topup-field textarea{
  background:#151922!important;
  border-color:rgba(148,163,184,.14)!important;
  color:#fff!important;
}
.topup-field input::placeholder,.topup-field textarea::placeholder{color:rgba(203,213,225,.42)!important;}
.topup-field input:focus,.topup-field select:focus,.topup-field textarea:focus{
  border-color:#3b82f6!important;
  box-shadow:0 0 0 3px rgba(59,130,246,.16)!important;
}
.topup-help{color:rgba(203,213,225,.55)!important;}
.topup-footer{
  background:#171b23!important;
  border-top:1px solid rgba(124,92,255,.20)!important;
}
.topup-save{background:linear-gradient(135deg,#367cff,#8b5cf6)!important;box-shadow:0 14px 30px rgba(54,124,255,.20)!important;}
.topup-save.secondary{background:#20252d!important;border-color:rgba(255,255,255,.12)!important;color:#fff!important;box-shadow:none!important;}
.topup-save.secondary:hover{border-color:rgba(59,130,246,.42)!important;background:#242b36!important;}
.topup-select-button{
  background:linear-gradient(180deg,#111827,#151a24)!important;
  border-color:rgba(59,130,246,.42)!important;
  box-shadow:inset 0 0 0 1px rgba(124,92,255,.08)!important;
}
.topup-select-button:hover{border-color:rgba(96,165,250,.74)!important;}
.topup-select-button i{color:#93c5fd!important;}
.topup-select-menu{
  background:#0f141d!important;
  border-color:rgba(59,130,246,.42)!important;
  box-shadow:0 28px 80px rgba(0,0,0,.70),0 0 0 1px rgba(124,92,255,.08)!important;
}
.topup-select-search{background:#0f141d!important;}
.topup-select-search input{
  background:#171d28!important;
  border-color:rgba(148,163,184,.16)!important;
  color:#fff!important;
}
.topup-select-search input:focus{border-color:#3b82f6!important;box-shadow:0 0 0 3px rgba(59,130,246,.14)!important;}
.topup-select-search i{color:#60a5fa!important;}
.topup-select-list::-webkit-scrollbar,.topup-form-scroll::-webkit-scrollbar{width:8px;}
.topup-select-list::-webkit-scrollbar-track,.topup-form-scroll::-webkit-scrollbar-track{background:#111722;border-radius:999px;}
.topup-select-list::-webkit-scrollbar-thumb,.topup-form-scroll::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#3577ff,#8b5cf6);border-radius:999px;}
.topup-select-option{
  color:rgba(226,232,240,.82)!important;
  border:1px solid transparent!important;
}
.topup-select-option:hover{
  background:rgba(59,130,246,.14)!important;
  border-color:rgba(59,130,246,.22)!important;
  color:#fff!important;
}
.topup-select-option.active{
  background:linear-gradient(135deg,rgba(54,124,255,.24),rgba(139,92,246,.18))!important;
  border-color:rgba(96,165,250,.42)!important;
  color:#fff!important;
}
.topup-select-option i{color:#60a5fa!important;}
.topup-rp-preset{
  background:linear-gradient(180deg,#111827,#0f1622)!important;
  border-color:rgba(148,163,184,.13)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.03)!important;
}
.topup-rp-preset:hover{
  border-color:rgba(59,130,246,.50)!important;
  background:linear-gradient(180deg,#142033,#101827)!important;
  transform:translateY(-1px);
}
.topup-rp-preset.active{
  background:linear-gradient(135deg,rgba(54,124,255,.26),rgba(139,92,246,.20))!important;
  border-color:#3b82f6!important;
  box-shadow:0 12px 28px rgba(54,124,255,.16),inset 0 0 0 1px rgba(147,197,253,.16)!important;
}
.topup-rp-preset span small{color:#93a4bd!important;}
.topup-dropzone{
  background:#121923!important;
  border-color:rgba(96,165,250,.24)!important;
}
.topup-dropzone:hover,.topup-dropzone.dragover{
  background:rgba(59,130,246,.10)!important;
  border-color:rgba(96,165,250,.70)!important;
}
.topup-dropzone i{color:#60a5fa!important;}



/* Top Up list, aligned with My Items list until the dashboard rebuild */
.topup-page{display:flex;flex-direction:column;gap:0;}
.topup-head{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.topup-head__meta{display:flex;align-items:center;gap:14px;}
.topup-head__icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.topup-head h1{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.topup-muted{font-size:.8rem;color:rgba(255,255,255,.40);}
.topup-btn-primary{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;border-radius:13px;padding:.6rem 1.4rem;font-weight:900;font-size:.9rem;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;text-decoration:none;box-shadow:none;}
.topup-btn-primary:hover{opacity:.88;transform:translateY(-1px);color:#fff;}
.topup-toolbar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.topup-tabs{display:flex;gap:6px;flex-wrap:wrap;}
.topup-tab{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:999px;color:rgba(255,255,255,.55);font-weight:850;font-size:.8rem;padding:7px 13px;line-height:1;}
.topup-tab:hover{background:rgba(255,255,255,.06);color:rgba(255,255,255,.82);}
.topup-tab.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.topup-search{position:relative;min-width:260px;}
.topup-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:.86rem;color:rgba(255,255,255,.32);}
.topup-search input{width:100%;height:38px;border-radius:11px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.14);color:rgba(255,255,255,.86);padding:0 14px 0 37px;font-size:.86rem;outline:none;}
.topup-search input:focus{border-color:rgba(109,92,255,.45);box-shadow:0 0 0 3px rgba(109,92,255,.11);}
.topup-panel.topup-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);position:relative;}
.topup-table{width:100%;border-collapse:collapse;border-radius:20px;overflow:hidden;display:table;}
.topup-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.topup-table th{padding:11px 16px;background:transparent;color:rgba(255,255,255,.35);font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;user-select:none;}
.topup-table td{padding:13px 16px;border-top:1px solid rgba(255,255,255,.04);vertical-align:middle;color:rgba(255,255,255,.78);font-size:.86rem;}
.topup-table tr:hover td{background:rgba(255,255,255,.025);}
.topup-id{font-weight:900;color:rgba(255,255,255,.35);}
.topup-game{display:flex;align-items:center;gap:10px;min-width:170px;}
.topup-game img,.topup-game__fallback{width:36px;height:36px;border-radius:10px;object-fit:cover;border:1px solid rgba(109,92,255,.25);background:rgba(109,92,255,.18);display:flex;align-items:center;justify-content:center;color:#c4b5fd;}
.topup-game strong,.topup-offer strong{display:block;color:rgba(255,255,255,.92);font-weight:950;line-height:1.25;}
.topup-offer{display:flex;align-items:center;gap:10px;min-width:210px;}
.topup-offer img{width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.18);}
.topup-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;background:rgba(109,92,255,.16);border:1px solid rgba(109,92,255,.32);color:#d8d0ff;font-weight:850;font-size:.74rem;padding:5px 9px;white-space:nowrap;}
.topup-price strong{color:#fff;font-size:.92rem;font-weight:950;}
.topup-earnings{color:#42e685;font-weight:950;}
.topup-status{display:inline-flex;align-items:center;border-radius:999px;padding:6px 10px;font-size:.72rem;font-weight:950;border:1px solid rgba(34,197,94,.30);background:rgba(34,197,94,.12);color:#4ade80;}
.topup-status.off{border-color:rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:rgba(255,255,255,.48);}
.topup-actions{display:flex;justify-content:flex-end;gap:8px;}
.topup-action{width:32px;height:32px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.78);display:inline-flex;align-items:center;justify-content:center;transition:background .12s,color .12s,border-color .12s;}
.topup-action:hover{background:rgba(255,255,255,.09);color:#fff;}
.topup-action.danger{color:#fb7185;background:rgba(251,113,133,.10);border-color:rgba(251,113,133,.18);}
.topup-action.danger:hover{background:rgba(251,113,133,.16);border-color:rgba(251,113,133,.30);}
.topup-empty{padding:64px 24px;text-align:center;color:rgba(255,255,255,.35);}
@media (max-width: 900px){.topup-toolbar{align-items:stretch}.topup-search{min-width:100%;}.topup-panel.topup-table-wrap{overflow-x:auto}.topup-table{min-width:980px}.topup-head{padding:18px}.topup-btn-primary{width:100%;justify-content:center;}}



/* Unified Top Up seller colors, keep temporary dashboard look close to Items */
.topup-canvas.custom-offcanvas{
  background:#202324!important;
  color:#fff!important;
  border-left:1px solid rgba(255,255,255,.08)!important;
  box-shadow:-36px 0 90px rgba(0,0,0,.52)!important;
}
.topup-canvas .offcanvas-header{
  background:#202324!important;
  border-bottom:1px solid rgba(255,255,255,.07)!important;
}
.topup-earnings-bar{
  background:#2b2c3c!important;
  border-bottom:1px solid rgba(255,255,255,.08)!important;
  color:rgba(255,255,255,.76)!important;
}
.topup-earnings-bar i{color:#22d3b6!important;}
.topup-steps{
  background:#202324!important;
  border-bottom:1px solid rgba(255,255,255,.07)!important;
}
.topup-step-line{background:rgba(255,255,255,.10)!important;}
.topup-step-num{
  background:#303436!important;
  border-color:rgba(255,255,255,.12)!important;
  color:rgba(255,255,255,.55)!important;
}
.topup-step.active .topup-step-num{
  background:linear-gradient(135deg,#6d5cff,#9b5cff)!important;
  color:#fff!important;
  border-color:transparent!important;
}
.topup-step.done .topup-step-num{
  background:rgba(34,197,94,.18)!important;
  border-color:rgba(34,197,94,.40)!important;
  color:#58e58a!important;
}
.topup-step-label{color:rgba(255,255,255,.42)!important;}
.topup-step.active .topup-step-label{color:#d7ccff!important;}
.topup-form-scroll{
  background:#202324!important;
}
.topup-section{
  background:#26292a!important;
  border:1px solid rgba(255,255,255,.08)!important;
  box-shadow:none!important;
}
.topup-section-title{
  background:#26292a!important;
  border-bottom:1px solid rgba(255,255,255,.07)!important;
  color:#fff!important;
}
.topup-section-title i{
  color:#6d5cff!important;
  background:rgba(109,92,255,.16)!important;
}
.topup-game-picker{
  background:#2a2d2e!important;
  border-color:rgba(255,255,255,.08)!important;
}
.topup-game-picker__top{
  background:#26292a!important;
  border-bottom-color:rgba(255,255,255,.07)!important;
}
.topup-game-search input,
.topup-field input,
.topup-field select,
.topup-field textarea{
  background:#1d2021!important;
  border-color:rgba(255,255,255,.10)!important;
  color:#fff!important;
}
.topup-game-search input:focus,
.topup-field input:focus,
.topup-field select:focus,
.topup-field textarea:focus{
  border-color:rgba(109,92,255,.58)!important;
  box-shadow:0 0 0 3px rgba(109,92,255,.13)!important;
}
.topup-game-card{
  background:#303334!important;
  border-color:rgba(255,255,255,.09)!important;
}
.topup-game-card:hover{
  background:#343739!important;
  border-color:rgba(109,92,255,.35)!important;
}
.topup-game-card.active{
  background:rgba(109,92,255,.18)!important;
  border-color:rgba(109,92,255,.65)!important;
  box-shadow:none!important;
}
.topup-select-button{
  background:#1d2021!important;
  border-color:rgba(109,92,255,.50)!important;
  box-shadow:none!important;
}
.topup-select-button:hover{border-color:rgba(143,122,255,.72)!important;}
.topup-select-button i{color:#c8bdff!important;}
.topup-select-menu{
  background:#1e2123!important;
  border-color:rgba(109,92,255,.45)!important;
  box-shadow:0 28px 70px rgba(0,0,0,.65)!important;
}
.topup-select-search{background:#1e2123!important;}
.topup-select-search input{
  background:#17191b!important;
  border-color:rgba(255,255,255,.11)!important;
}
.topup-select-search i{color:rgba(255,255,255,.44)!important;}
.topup-select-option{color:rgba(255,255,255,.76)!important;}
.topup-select-option:hover,
.topup-select-option.active{
  background:rgba(109,92,255,.16)!important;
  border-color:rgba(109,92,255,.24)!important;
  color:#fff!important;
}
.topup-select-option i{color:#a78bfa!important;}
.topup-select-list::-webkit-scrollbar,
.topup-form-scroll::-webkit-scrollbar{width:8px;}
.topup-select-list::-webkit-scrollbar-track,
.topup-form-scroll::-webkit-scrollbar-track{background:#1a1d1e!important;border-radius:999px;}
.topup-select-list::-webkit-scrollbar-thumb,
.topup-form-scroll::-webkit-scrollbar-thumb{background:#4a4d51!important;border-radius:999px;}
.topup-select-list::-webkit-scrollbar-thumb:hover,
.topup-form-scroll::-webkit-scrollbar-thumb:hover{background:#6d5cff!important;}
.topup-rp-preset{
  background:#151b25!important;
  border-color:rgba(255,255,255,.09)!important;
  box-shadow:none!important;
}
.topup-rp-preset:hover{
  background:#1d2430!important;
  border-color:rgba(109,92,255,.45)!important;
  transform:translateY(-1px);
}
.topup-rp-preset.active{
  background:linear-gradient(135deg,rgba(109,92,255,.24),rgba(109,92,255,.12))!important;
  border-color:rgba(109,92,255,.75)!important;
  box-shadow:none!important;
}
.topup-footer{
  background:#202324!important;
  border-top:1px solid rgba(255,255,255,.07)!important;
}
.topup-save{
  background:linear-gradient(135deg,#6d5cff,#9b5cff)!important;
  box-shadow:none!important;
}
.topup-save.secondary{
  background:#2b2e30!important;
  border-color:rgba(255,255,255,.11)!important;
}
.topup-dropzone{
  background:#1d2021!important;
  border-color:rgba(255,255,255,.16)!important;
}
.topup-dropzone:hover,.topup-dropzone.dragover{
  background:rgba(109,92,255,.08)!important;
  border-color:rgba(109,92,255,.52)!important;
}
.topup-dropzone i{color:#a78bfa!important;}

</style>
<?php $this->stop() ?>

<div class="topup-page">
    <div class="topup-head">
        <div class="topup-head__meta">
            <div class="topup-head__icon"><i class="fa-solid fa-coins"></i></div>
            <div>
                <h1>My Top Ups</h1>
                <div class="topup-muted"><?= count($topups) ?> offers total</div>
            </div>
        </div>
        <button class="topup-btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#topupCanvas" onclick="openTopupCreate()">
            <i class="fa-solid fa-plus me-1"></i> Add Top Up
        </button>
    </div>

    <div class="topup-toolbar">
        <div class="topup-tabs" id="topupTabs">
            <button class="topup-tab active" type="button" data-filter="all">All</button>
            <button class="topup-tab" type="button" data-filter="active">Active</button>
            <button class="topup-tab" type="button" data-filter="inactive">Unlisted</button>
            <button class="topup-tab" type="button" data-filter="sold">Sold</button>
        </div>
        <div class="topup-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="topupSearch" placeholder="Search top ups..." autocomplete="off">
        </div>
    </div>

    <div class="topup-panel topup-table-wrap">
        <table class="topup-table" id="topupTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Game</th>
                    <th>Offer</th>
                    <th>Region</th>
                    <th>Price</th>
                    <th>Earnings</th>
                    <th>Waiting</th>
                    <th>Stock</th>
                    <th>Sold</th>
                    <th>Status</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topups as $t):
                    $rowText = strtolower(trim(seller_topup_game_name($t) . ' ' . ($t['offer_title'] ?? '') . ' ' . ($t['offer_amount'] ?? '') . ' ' . ($t['offer_unit'] ?? '') . ' ' . ($t['region'] ?? '') . ' ' . ($t['platform'] ?? '')));
                    $active = (int)($t['active'] ?? 1) === 1;
                    $sold = (int)($t['sold_count'] ?? 0);
                    $earnings = (int)round((int)($t['price'] ?? 0) * max(0, 100 - (float)$effective_fee) / 100);
                ?>
                    <tr data-search="<?= htmlspecialchars($rowText) ?>" data-status="<?= $active ? 'active' : 'inactive' ?>" data-sold="<?= $sold > 0 ? 'sold' : 'none' ?>">
                        <td><span class="topup-id">#<?= (int)$t['id'] ?></span></td>
                        <td>
                            <div class="topup-game">
                                <?php if (!empty($t['game_icon'])): ?>
                                    <img src="<?= htmlspecialchars((string)$t['game_icon']) ?>" alt="">
                                <?php else: ?>
                                    <span class="topup-game__fallback"><i class="fa-solid fa-gamepad"></i></span>
                                <?php endif; ?>
                                <div>
                                    <strong><?= htmlspecialchars(seller_topup_game_name($t)) ?></strong>
                                    <div class="topup-muted"><?= htmlspecialchars((string)($t['service_label'] ?? 'Top Up')) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="topup-offer">
                                <?php if (!empty($t['image'])): ?><img src="<?= htmlspecialchars((string)$t['image']) ?>" alt=""><?php endif; ?>
                                <div>
                                    <strong><?= htmlspecialchars((string)($t['offer_title'] ?? 'Top Up')) ?></strong>
                                    <div class="topup-muted"><?= htmlspecialchars(lb_topup_amount_clean($t['offer_amount'] ?? '')) ?> <?= htmlspecialchars((string)($t['offer_unit'] ?? '')) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="topup-pill"><i class="fa-solid fa-globe"></i><?= htmlspecialchars(seller_topup_region_label($t['region'] ?? 'Global', (string)($t['game_slug'] ?? ''))) ?></span></td>
                        <td class="topup-price"><strong><?= seller_topup_money($t['price'] ?? 0) ?></strong></td>
                        <td><span class="topup-earnings"><?= seller_topup_money($earnings) ?></span></td>
                        <td><span class="topup-pill"><i class="fa-regular fa-clock"></i><?= htmlspecialchars(seller_topup_wait($t)) ?></span></td>
                        <td><?= (int)($t['stock'] ?? 0) ?></td>
                        <td><?= $sold ?></td>
                        <td><span class="topup-status <?= $active ? '' : 'off' ?>"><?= $active ? 'Active' : 'Unlisted' ?></span></td>
                        <td>
                            <div class="topup-actions">
                                <button class="topup-action" type="button" title="Edit" data-bs-toggle="offcanvas" data-bs-target="#topupCanvas" onclick="openTopupEdit(<?= (int)$t['id'] ?>)"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/seller-area/top-ups/<?= (int)$t['id'] ?>/delete" onsubmit="return confirm('Delete this top up?')">
                                    <button class="topup-action danger" type="submit" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$topups): ?>
                    <tr class="topup-empty-row"><td colspan="11"><div class="topup-empty"><i class="fa-solid fa-coins mb-2"></i><br>No top ups listed yet.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="offcanvas offcanvas-end topup-canvas custom-offcanvas" tabindex="-1" id="topupCanvas" aria-labelledby="topupCanvasTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="topupCanvasTitle">Add Top Up</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="topup-earnings-bar">
        <i class="fa-solid fa-sack-dollar text-success"></i>
        <span>Estimated seller payout after <strong class="text-primary"><?= htmlspecialchars((string)$effective_fee) ?>%</strong> fee, <strong class="text-success" id="topupPayoutPreview">€0.00</strong> per unit</span>
    </div>
    <div class="topup-steps">
        <div class="topup-step active" data-step="1"><span class="topup-step-num">1</span><span class="topup-step-label">Game</span></div>
        <div class="topup-step-line"></div>
        <div class="topup-step" data-step="2"><span class="topup-step-num">2</span><span class="topup-step-label">Offer Info</span></div>
        <div class="topup-step-line"></div>
        <div class="topup-step" data-step="3"><span class="topup-step-num">3</span><span class="topup-step-label">Price & Stock</span></div>
    </div>
    <div class="offcanvas-body">
        <form class="topup-form" method="post" enctype="multipart/form-data" action="/seller-area/top-ups/create" id="topupForm">
            <input type="hidden" name="game_id" id="topupGameId" value="<?= htmlspecialchars((string)($topupGames[0]['id'] ?? '')) ?>">
            <div class="topup-form-scroll">
                <div class="topup-section active" data-step-panel="1">
                    <div class="topup-section-title"><i class="fa-solid fa-gamepad text-primary"></i> Choose Game</div>
                    <div class="topup-section-body">
                        <div class="topup-game-picker">
                            <div class="topup-game-picker__top">
                                <div class="topup-game-search">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="search" id="topupGameSearch" placeholder="Search games..." autocomplete="off">
                                </div>
                            </div>
                            <div class="topup-game-list" id="topupGameList">
                                <?php foreach ($topupGames as $i => $g):
                                    $slug = (string)($g['slug'] ?? '');
                                    $cfg = $topupConfigs[$slug] ?? [];
                                    $label = (string)($cfg['service_label'] ?? 'Top Up');
                                ?>
                                    <button type="button" class="topup-game-card <?= $i === 0 ? 'active' : '' ?>" data-id="<?= (int)$g['id'] ?>" data-slug="<?= htmlspecialchars((string)$slug) ?>" data-name="<?= htmlspecialchars(strtolower((string)$g['name'] . ' ' . $label)) ?>">
                                        <?php if (!empty($g['icon'])): ?><img src="<?= htmlspecialchars((string)$g['icon']) ?>" alt=""><?php else: ?><span class="topup-game-card__icon"><i class="fa-solid fa-gamepad"></i></span><?php endif; ?>
                                        <span><strong><?= htmlspecialchars((string)$g['name']) ?></strong><small><?= htmlspecialchars($label) ?></small></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="topup-section" data-step-panel="2">
                    <div class="topup-section-title"><i class="fa-solid fa-circle-info text-primary"></i> Offer Info</div>
                    <div class="topup-section-body">
                        <div class="topup-grid">
                            <div class="topup-field topup-rp-field" id="topupRpPresetField" style="grid-column:1/-1;display:none"><label>Offer Preset <span class="text-danger">*</span></label><div class="topup-rp-presets" id="topupRpPresets"></div><div class="topup-help">Choose one of the imported offers. Title, amount, price and icon are filled automatically.</div></div>
                            <div class="topup-field topup-manual-rp-field"><label>Offer Title <span class="text-danger">*</span></label><input name="offer_title" id="topupOfferTitle" required placeholder="2800 RP" readonly></div>
                            <div class="topup-field"><label>Region / Server <span class="text-danger">*</span></label><input type="hidden" name="region" id="topupRegion" required><div class="topup-select-ui" id="topupRegionUi"><button class="topup-select-button" type="button" id="topupRegionButton"><span id="topupRegionLabel">Select region</span><i class="fa-solid fa-chevron-down"></i></button><div class="topup-select-menu" id="topupRegionMenu"><div class="topup-select-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="topupRegionSearch" placeholder="Search region..." autocomplete="off"></div><div class="topup-select-list" id="topupRegionList"></div></div></div><div class="topup-help">For League of Legends this controls which server customers can select and which offers they see.</div></div>
                            <div class="topup-field topup-manual-rp-field"><label>Amount <span class="text-danger">*</span></label><input name="offer_amount" id="topupAmount" type="number" step="0.01" required placeholder="2800" readonly></div>
                            <div class="topup-field topup-manual-rp-field"><label>Unit <span class="text-danger">*</span></label><input name="offer_unit" id="topupUnit" required placeholder="RP, Gems, Diamonds, Coins" readonly></div>
                            <div class="topup-field"><label>Platform</label><input name="platform" id="topupPlatform" placeholder="PC, Mobile, PlayStation"></div>
                            <input type="hidden" name="image" id="topupImage"><input type="file" name="topup_image" id="topupImageFile" accept="image/*" hidden>
                        </div>
                    </div>
                </div>

                <div class="topup-section" data-step-panel="3">
                    <div class="topup-section-title"><i class="fa-solid fa-box text-primary"></i> Price, Stock & Delivery</div>
                    <div class="topup-section-body">
                        <div class="topup-grid">
                            <div class="topup-field"><label>Price EUR <span class="text-danger">*</span></label><input name="price" id="topupPrice" required type="number" step="0.01" placeholder="6.42"></div>
                            <div class="topup-field"><label>Stock</label><input name="stock" id="topupStock" type="number" value="999"></div>
                            <div class="topup-field"><label>Minimum Quantity</label><input name="min_quantity" id="topupMinQuantity" type="number" value="1"></div>
                            <div class="topup-field"><label>Waiting Time</label><div class="topup-inline"><input name="waiting_time_value" id="topupWaitingValue" type="number" min="0" value="10"><select name="waiting_time_unit" id="topupWaitingUnit"><option value="minutes">Minutes</option><option value="hours">Hours</option><option value="days">Days</option></select></div><div class="topup-help">Customers see this in the offer card and checkout.</div></div>
                            <div class="topup-field"><label>Status</label><select name="active" id="topupActive"><option value="1">Active</option><option value="0">Unlisted</option></select></div>
                            <div class="topup-field" style="grid-column:1/-1"><label>Seller Instructions</label><textarea name="instructions" id="topupInstructions" rows="4" placeholder="Please provide User ID and Server..."></textarea></div>
                            <div class="topup-field" style="grid-column:1/-1"><label>Image Gallery</label><div class="topup-dropzone" id="topupDropzone"><div><i class="fa-solid fa-image"></i><strong>Custom Image (optional)</strong><span>Imported icons are assigned automatically, or upload your own image</span></div></div><div id="topupImagePreview" class="topup-img-preview" style="display:none"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="topup-footer">
                <button class="topup-save secondary" type="button" id="topupPrevBtn">Previous</button>
                <div class="topup-footer-actions">
                    <div class="topup-muted" id="topupModeHelp">Create a new top up offer for the selected game.</div>
                    <button class="topup-save" type="button" id="topupNextBtn">Next</button>
                    <button class="topup-save" type="submit" id="topupSubmitBtn" style="display:none"><i class="fa-solid fa-floppy-disk me-1"></i> Save Top Up</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $this->start('scripts') ?>
<script>
const topupRows = <?= json_encode($topupsJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const topupLolRpPresets = <?= json_encode($topupLolRpPresetRows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const topupGameRegions = <?= json_encode($topupGameRegions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const topupGamePresets = <?= json_encode($topupGamePresets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const topupFee = <?= json_encode((float)$effective_fee) ?>;

function topupMoney(value){
    const n = Number(value || 0);
    return '€' + n.toFixed(2);
}
function updateTopupPayout(){
    const price = Number(document.getElementById('topupPrice')?.value || 0);
    const payout = price * Math.max(0, 100 - Number(topupFee || 0)) / 100;
    const target = document.getElementById('topupPayoutPreview');
    if (target) target.textContent = topupMoney(payout);
}

function getTopupSelectedGameCard(){
    return document.querySelector('.topup-game-card.active') || document.querySelector('.topup-game-card');
}
function isTopupLolGame(){
    const card = getTopupSelectedGameCard();
    const slug = String((card && card.dataset.slug) || '').toLowerCase();
    const name = String((card && card.dataset.name) || '').toLowerCase();
    return ['league-of-legends','lol','league'].includes(slug) || slug.indexOf('league-of-legends') !== -1 || name.indexOf('league of legends') !== -1;
}
function setTopupAutoImage(image){
    const hidden = document.getElementById('topupImage');
    const pv = document.getElementById('topupImagePreview');
    if (hidden) hidden.value = image || '';
    if (pv) {
        if (image) { pv.style.display='block'; pv.style.backgroundImage='url(' + image + ')'; }
        else { pv.style.display='none'; pv.style.backgroundImage='none'; }
    }
}
function getSelectedTopupGameId(){
    const card = getTopupSelectedGameCard();
    return String((card && card.dataset.id) || document.getElementById('topupGameId')?.value || '');
}
function getSelectedTopupPresets(){
    return topupGamePresets[String(getSelectedTopupGameId())] || [];
}
function applyTopupRpPreset(preset){
    if (!preset) return;
    document.getElementById('topupOfferTitle').value = preset.title || '';
    document.getElementById('topupAmount').value = preset.amount || '';
    document.getElementById('topupUnit').value = preset.unit || 'Points';
    if (preset.price) document.getElementById('topupPrice').value = preset.price;
    setTopupAutoImage(preset.image || '');
    document.querySelectorAll('.topup-rp-preset').forEach(btn => btn.classList.toggle('active', String(btn.dataset.key) === String(preset.key || preset.title)));
    updateTopupPayout();
}
function renderTopupRpPresets(){
    const wrap = document.getElementById('topupRpPresets');
    const field = document.getElementById('topupRpPresetField');
    const form = document.getElementById('topupForm');
    if (!wrap || !field) return;
    const presets = getSelectedTopupPresets();
    const show = presets.length > 0;
    if (form) form.classList.toggle('is-lol', show);
    ['topupOfferTitle','topupAmount','topupUnit'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = show;
    });
    field.style.display = show ? '' : 'none';
    wrap.innerHTML = '';
    if (!show) return;
    const currentTitle = String(document.getElementById('topupOfferTitle')?.value || '').trim().toLowerCase();
    const currentAmount = String(document.getElementById('topupAmount')?.value || '').replace(/[^0-9.]/g, '');
    let matchedPreset = null;
    presets.forEach(preset => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'topup-rp-preset';
        btn.dataset.key = preset.key || preset.title || '';
        const title = preset.title || 'Top Up';
        const image = preset.image || '';
        const meta = [preset.amount && preset.unit ? (preset.amount + ' ' + preset.unit) : '', preset.price ? ('€' + preset.price) : '', preset.popular ? 'Popular' : ''].filter(Boolean).join(' · ');
        btn.innerHTML = (image ? '<img src="' + image + '" alt="">' : '<span class="topup-game-card__icon"><i class="fa-solid fa-coins"></i></span>') + '<span>' + title + '<small>' + meta + '</small></span>';
        btn.addEventListener('click', () => applyTopupRpPreset(preset));
        wrap.appendChild(btn);
        const titleMatches = currentTitle && String(title).toLowerCase() === currentTitle;
        const amountMatches = currentAmount && String(preset.amount || '').replace(/[^0-9.]/g, '') === currentAmount;
        if (!matchedPreset && (titleMatches || amountMatches)) matchedPreset = preset;
    });
    if (matchedPreset) {
        document.querySelectorAll('.topup-rp-preset').forEach(btn => btn.classList.toggle('active', String(btn.dataset.key) === String(matchedPreset.key || matchedPreset.title)));
        if (!document.getElementById('topupImage')?.value && matchedPreset.image) setTopupAutoImage(matchedPreset.image);
    } else if (!currentTitle && presets[0]) {
        applyTopupRpPreset(presets[0]);
    }
}
function setTopupRegionValue(value, label){
    const input = document.getElementById('topupRegion');
    const labelEl = document.getElementById('topupRegionLabel');
    if (input) input.value = value || '';
    if (labelEl) labelEl.textContent = label || value || 'Select region';
    document.querySelectorAll('#topupRegionMenu .topup-select-option').forEach(btn => btn.classList.toggle('active', String(btn.dataset.value).toLowerCase() === String(value || '').toLowerCase()));
}
function refreshTopupRegionOptions(gameId, selectedRegion){
    const menu = document.getElementById('topupRegionMenu');
    const list = document.getElementById('topupRegionList') || menu;
    if (!menu || !list) return;
    const options = topupGameRegions[String(gameId)] || [{value:'Global', label:'Global'}];
    const current = selectedRegion || document.getElementById('topupRegion')?.value || (options[0] ? options[0].value : 'Global');
    list.innerHTML = '';
    const search = document.getElementById('topupRegionSearch');
    if (search) search.value = '';
    options.forEach(opt => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'topup-select-option';
        btn.dataset.value = opt.value;
        btn.dataset.label = opt.label;
        btn.innerHTML = '<span>' + opt.label + '</span><i class="fa-solid fa-check"></i>';
        btn.addEventListener('click', () => { setTopupRegionValue(opt.value, opt.label); closeTopupRegionMenu(); });
        list.appendChild(btn);
    });
    const wanted = String(current || '').toLowerCase();
    const match = options.find(opt => String(opt.value).toLowerCase() === wanted || String(opt.label).toLowerCase() === wanted) || options[0] || {value:'Global', label:'Global'};
    setTopupRegionValue(match.value, match.label);
}
function setTopupGame(id, selectedRegion){
    document.querySelectorAll('.topup-game-card').forEach(card => card.classList.toggle('active', String(card.dataset.id) === String(id)));
    const input = document.getElementById('topupGameId');
    if (input) input.value = id || '';
    refreshTopupRegionOptions(id, selectedRegion || '');
    renderTopupRpPresets();
}
function clearTopupForm(){
    const form = document.getElementById('topupForm');
    if (!form) return;
    form.reset();
    form.action = '/seller-area/top-ups/create';
    const firstGame = document.querySelector('.topup-game-card');
    setTopupGame(firstGame ? firstGame.dataset.id : '');
    renderTopupRpPresets();
    document.getElementById('topupWaitingValue').value = 10;
    document.getElementById('topupWaitingUnit').value = 'minutes';
    document.getElementById('topupStock').value = 999;
    document.getElementById('topupMinQuantity').value = 1;
    document.getElementById('topupActive').value = 1;
    updateTopupPayout();
}
function openTopupCreate(){
    clearTopupForm();
    document.getElementById('topupCanvasTitle').textContent = 'Add Top Up';
    document.getElementById('topupModeHelp').textContent = 'Create a new top up offer for the selected game.';
    setTopupStep(1);
}
function openTopupEdit(id){
    clearTopupForm();
    const data = topupRows[id];
    if (!data) return;
    document.getElementById('topupCanvasTitle').textContent = 'Edit Top Up';
    document.getElementById('topupModeHelp').textContent = 'Update this top up offer.';
    setTopupStep(1);
    document.getElementById('topupForm').action = '/seller-area/top-ups/' + id + '/update';
    setTopupGame(data.game_id, data.region || '');
    document.getElementById('topupOfferTitle').value = data.offer_title || '';
    refreshTopupRegionOptions(data.game_id, data.region || '');
    document.getElementById('topupAmount').value = data.offer_amount || '';
    document.getElementById('topupUnit').value = data.offer_unit || '';
    renderTopupRpPresets();
    document.getElementById('topupPlatform').value = data.platform || '';
    document.getElementById('topupImage').value = data.image || ''; const pv=document.getElementById('topupImagePreview'); if(pv){ if(data.image){pv.style.display='block';pv.style.backgroundImage='url('+data.image+')'}else{pv.style.display='none';pv.style.backgroundImage='none'} }
    document.getElementById('topupPrice').value = data.price || '';
    document.getElementById('topupStock').value = data.stock || 0;
    document.getElementById('topupMinQuantity').value = data.min_quantity || 1;
    document.getElementById('topupWaitingValue').value = data.waiting_time_value || 0;
    document.getElementById('topupWaitingUnit').value = data.waiting_time_unit || 'minutes';
    document.getElementById('topupInstructions').value = data.instructions || '';
    document.getElementById('topupActive').value = String(data.active ?? 1);
    updateTopupPayout();
}

document.querySelectorAll('.topup-game-card').forEach(card => {
    card.addEventListener('click', () => setTopupGame(card.dataset.id));
});

function previewTopupImage(file){
    const pv = document.getElementById('topupImagePreview');
    if (!file || !pv) return;
    const url = URL.createObjectURL(file);
    pv.style.display = 'block';
    pv.style.backgroundImage = 'url(' + url + ')';
}
document.getElementById('topupImageFile')?.addEventListener('change', function(){ previewTopupImage(this.files && this.files[0]); });
const topupDropzone = document.getElementById('topupDropzone');
const topupFileInput = document.getElementById('topupImageFile');
if (topupDropzone && topupFileInput) {
    topupDropzone.addEventListener('click', () => topupFileInput.click());
    topupDropzone.addEventListener('dragover', e => { e.preventDefault(); topupDropzone.classList.add('dragover'); });
    topupDropzone.addEventListener('dragleave', () => topupDropzone.classList.remove('dragover'));
    topupDropzone.addEventListener('drop', e => {
        e.preventDefault();
        topupDropzone.classList.remove('dragover');
        const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        topupFileInput.files = dt.files;
        previewTopupImage(file);
    });
    document.addEventListener('paste', e => {
        const items = e.clipboardData && e.clipboardData.items;
        if (!items) return;
        for (const item of items) {
            if (item.type && item.type.indexOf('image/') === 0) {
                const file = item.getAsFile();
                const dt = new DataTransfer();
                dt.items.add(file);
                topupFileInput.files = dt.files;
                previewTopupImage(file);
                break;
            }
        }
    });
}


function positionTopupRegionMenu(){
    const ui = document.getElementById('topupRegionUi');
    const btn = document.getElementById('topupRegionButton');
    const menu = document.getElementById('topupRegionMenu');
    if (!ui || !btn || !menu || !ui.classList.contains('open')) return;
    const r = btn.getBoundingClientRect();
    const gap = 7;
    const maxBelow = window.innerHeight - r.bottom - 20;
    const maxAbove = r.top - 20;
    const openUp = maxBelow < 260 && maxAbove > maxBelow;
    menu.style.left = r.left + 'px';
    menu.style.width = r.width + 'px';
    menu.style.top = openUp ? 'auto' : (r.bottom + gap) + 'px';
    menu.style.bottom = openUp ? (window.innerHeight - r.top + gap) + 'px' : 'auto';
    menu.style.maxHeight = Math.max(220, Math.min(430, (openUp ? maxAbove : maxBelow) - gap)) + 'px';
}
function openTopupRegionMenu(){
    const ui = document.getElementById('topupRegionUi');
    if (!ui) return;
    ui.classList.add('open');
    positionTopupRegionMenu();
    const search = document.getElementById('topupRegionSearch');
    if (search) { setTimeout(() => search.focus(), 20); }
}
function closeTopupRegionMenu(){
    const ui = document.getElementById('topupRegionUi');
    if (ui) ui.classList.remove('open');
}
function filterTopupRegionOptions(){
    const q = (document.getElementById('topupRegionSearch')?.value || '').toLowerCase().trim();
    document.querySelectorAll('#topupRegionList .topup-select-option').forEach(btn => {
        const hay = ((btn.dataset.label || '') + ' ' + (btn.dataset.value || '')).toLowerCase();
        btn.style.display = !q || hay.includes(q) ? '' : 'none';
    });
}
window.addEventListener('resize', positionTopupRegionMenu);
window.addEventListener('scroll', positionTopupRegionMenu, true);
document.getElementById('topupRegionSearch')?.addEventListener('input', filterTopupRegionOptions);

document.getElementById('topupRegionButton')?.addEventListener('click', function(){
    const ui=document.getElementById('topupRegionUi'); if(ui && ui.classList.contains('open')){ closeTopupRegionMenu(); } else { openTopupRegionMenu(); }
});
document.addEventListener('click', function(e){
    const ui = document.getElementById('topupRegionUi');
    if (ui && !ui.contains(e.target)) closeTopupRegionMenu();
});

let topupCurrentStep = 1;
function setTopupStep(step){
    topupCurrentStep = Math.max(1, Math.min(3, Number(step || 1)));
    document.querySelectorAll('[data-step-panel]').forEach(panel => panel.classList.toggle('active', Number(panel.dataset.stepPanel) === topupCurrentStep));
    document.querySelectorAll('.topup-step[data-step]').forEach(stepEl => {
        const n = Number(stepEl.dataset.step);
        stepEl.classList.toggle('active', n === topupCurrentStep);
        stepEl.classList.toggle('done', n < topupCurrentStep);
    });
    const prev = document.getElementById('topupPrevBtn');
    const next = document.getElementById('topupNextBtn');
    const submit = document.getElementById('topupSubmitBtn');
    if (prev) prev.style.visibility = topupCurrentStep === 1 ? 'hidden' : 'visible';
    if (next) next.style.display = topupCurrentStep === 3 ? 'none' : '';
    if (submit) submit.style.display = topupCurrentStep === 3 ? '' : 'none';
}
document.getElementById('topupPrevBtn')?.addEventListener('click', () => setTopupStep(topupCurrentStep - 1));

function validateTopupStep(step){
    const panel = document.querySelector('[data-step-panel="' + step + '"]');
    if (!panel) return true;
    const fields = Array.from(panel.querySelectorAll('input,select,textarea')).filter(el => el.offsetParent !== null || el.type === 'hidden');
    for (const el of fields) {
        if (el.required && !String(el.value || '').trim()) {
            if (el.id === 'topupRegion') openTopupRegionMenu();
            else if (typeof el.reportValidity === 'function') el.reportValidity();
            return false;
        }
    }
    return true;
}
document.getElementById('topupNextBtn')?.addEventListener('click', () => { if (validateTopupStep(topupCurrentStep)) setTopupStep(topupCurrentStep + 1); });

document.getElementById('topupGameSearch')?.addEventListener('input', function(){
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.topup-game-card').forEach(card => {
        card.style.display = !q || (card.dataset.name || '').includes(q) ? '' : 'none';
    });
});

document.getElementById('topupPrice')?.addEventListener('input', updateTopupPayout);
document.getElementById('topupAmount')?.addEventListener('input', function(){ if(!isTopupLolGame()) return; const current=String(this.value||'').replace(/[^0-9]/g,''); const match=topupLolRpPresets.find(p=>String(p.amount)===current); if(match){ applyTopupRpPreset(match); } });

function filterTopupTable(){
    const q = (document.getElementById('topupSearch')?.value || '').toLowerCase().trim();
    const activeTab = document.querySelector('.topup-tab.active')?.dataset.filter || 'all';
    let visible = 0;
    document.querySelectorAll('#topupTable tbody tr[data-search]').forEach(row => {
        const matchesSearch = !q || (row.dataset.search || '').includes(q);
        const matchesTab = activeTab === 'all' || row.dataset.status === activeTab || row.dataset.sold === activeTab;
        const show = matchesSearch && matchesTab;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const empty = document.querySelector('.topup-empty-row');
    if (empty) empty.style.display = visible === 0 ? '' : 'none';
}

document.getElementById('topupForm')?.addEventListener('submit', function(e){
    if (!document.getElementById('topupRegion')?.value) {
        const first = document.querySelector('#topupRegionList .topup-select-option');
        if (first) setTopupRegionValue(first.dataset.value, first.dataset.label);
    }
});
document.getElementById('topupSearch')?.addEventListener('input', filterTopupTable);
document.querySelectorAll('.topup-tab').forEach(btn => btn.addEventListener('click', function(){
    document.querySelectorAll('.topup-tab').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    filterTopupTable();
}));
setTopupStep(1);
refreshTopupRegionOptions(document.getElementById('topupGameId')?.value || '');
renderTopupRpPresets();

// Initial UI state for RP presets and regions
setTimeout(function(){
    const firstGame = document.querySelector('.topup-game-card.active') || document.querySelector('.topup-game-card');
    if (firstGame) setTopupGame(firstGame.dataset.id);
    renderTopupRpPresets();
}, 0);
</script>
<?php $this->stop() ?>
