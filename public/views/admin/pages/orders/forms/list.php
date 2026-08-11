<?php
$forms = is_array($data ?? null) ? $data : [];
$h = fn($v) => esc($v);

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        $haystack = (string)$haystack;
        $needle   = (string)$needle;
        if ($needle === '') return true;
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

$svgBaseUrl = defined('ASSET_URL')
    ? (ASSET_URL . '/website/images/boost-forms/boost-type-icons')
    : '/public/assets/website/images/boost-forms/boost-type-icons';

$gameLabels = [];
$totalActive = 0;
$totalInactive = 0;
foreach ($forms as $row) {
    $game = strtolower(trim((string)($row['game'] ?? 'unknown')));
    $gameLabels[$game] = function_exists('util_game_display_name')
        ? util_game_display_name($game)
        : strtoupper($game);
    $status = strtolower(trim((string)($row['status'] ?? '')));
    if (in_array($status, ['active', '1', 'enabled', 'published'], true) || (int)($row['status'] ?? 0) === 1) $totalActive++;
    else $totalInactive++;
}
asort($gameLabels, SORT_NATURAL | SORT_FLAG_CASE);
usort($forms, static function(array $a, array $b): int {
    $gameCompare = strnatcasecmp(
        function_exists('util_game_display_name') ? util_game_display_name((string)($a['game'] ?? '')) : (string)($a['game'] ?? ''),
        function_exists('util_game_display_name') ? util_game_display_name((string)($b['game'] ?? '')) : (string)($b['game'] ?? '')
    );
    return $gameCompare !== 0 ? $gameCompare : strnatcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
});

$renderFormIcon = static function(array $row) use ($h, $svgBaseUrl): string {
    $icon = trim((string)($row['icon'] ?? ''));
    if ($icon !== '' && str_ends_with(strtolower($icon), '.svg')) {
        return '<img class="fp-form-icon-img" src="' . $h($svgBaseUrl . '/' . basename($icon)) . '" alt="">';
    }
    if ($icon !== '') {
        return '<i class="fa-duotone ' . $h($icon) . '"></i>';
    }
    return '<i class="fa-duotone fa-wand-magic-sparkles"></i>';
};

$renderGameIcon = static function(string $game) use ($h): string {
    $url = function_exists('util_game_icon_url') ? util_game_icon_url($game) : '';
    if ($url !== '') {
        return '<img class="fp-game-img" src="' . $h($url) . '" alt="' . $h(function_exists('util_game_display_name') ? util_game_display_name($game) : $game) . '">';
    }
    return '<i class="fa-solid fa-gamepad"></i>';
};
$gameName = static function(string $game): string {
    return function_exists('util_game_display_name') ? util_game_display_name($game) : strtoupper($game);
};
$editUrl = static function(array $row): string {
    $game = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim((string)($row['game'] ?? ''))));
    $type = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim((string)($row['type'] ?? ''))));
    $legacyTemplate = rtrim((string)SYS_PATH, '/\\')
        . '/public/views/admin/pages/orders/forms/price-tables/' . $game . '/' . $type . '.php';
    $gameId = (int)($row['game_id'] ?? 0);
    if (!is_file($legacyTemplate) && $gameId > 0) {
        return ADMN_URL . '/games/' . $gameId . '/boost-form-edit?fid=' . (int)$row['id'];
    }
    return ADMN_URL . '/boost/form/' . (int)$row['id'] . '/edit';
};
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Boost Forms Pricing - Admin Area | LoLBoost.gg', 'h1' => 'Boost Forms Pricing', 'description' => 'Manage and edit the boost forms pricing.']]) ?>

<?= $this->start('styles') ?>
<style>
.fp-page{--fp-bg:#222527;--fp-panel:#1b1d1f;--fp-surface:#262a2d;--fp-surface-2:#2b3033;--fp-border:rgba(255,255,255,.075);--fp-muted:rgba(255,255,255,.46);--fp-soft:rgba(255,255,255,.055);--fp-purple:#7c5cff;--fp-cyan:#57c7ff;--fp-green:#26d39a;--fp-yellow:#f4c430;}
.fp-page .card{background:var(--fp-bg)!important;border:1px solid var(--fp-border)!important;border-radius:22px!important;box-shadow:none!important;}
.fp-page .card::before{display:none!important;}
.fp-hero{position:relative;overflow:hidden;border:1px solid var(--fp-border);border-radius:24px;background:linear-gradient(135deg,rgba(124,92,255,.15),rgba(49,60,66,.45) 52%,rgba(38,42,45,.98)),var(--fp-bg);padding:24px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;}
.fp-hero-left{display:flex;align-items:center;gap:15px;min-width:0;}
.fp-hero-icon{width:54px;height:54px;border-radius:17px;background:linear-gradient(135deg,rgba(124,92,255,.22),rgba(255,255,255,.045));border:1px solid rgba(124,92,255,.28);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.35rem;box-shadow:0 16px 38px rgba(0,0,0,.18);}
.fp-title{margin:0;color:#fff;font-size:1.38rem;font-weight:950;letter-spacing:-.035em;line-height:1.08;}
.fp-sub{margin-top:5px;color:var(--fp-muted);font-size:.86rem;font-weight:650;}
.fp-hero-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.fp-btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border:1px solid rgba(255,255,255,.11);background:rgba(255,255,255,.055);color:rgba(255,255,255,.74);text-decoration:none;border-radius:12px;padding:9px 14px;font-size:.82rem;font-weight:900;cursor:pointer;transition:background .15s,border-color .15s,color .15s,transform .12s;}
.fp-btn:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.18);color:#fff;transform:translateY(-1px);}
.fp-btn-primary{background:linear-gradient(135deg,#8b5cf6,#c026d3);border-color:rgba(192,132,252,.42);color:#fff;}
.fp-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px;}
.fp-stat{border:1px solid var(--fp-border);border-radius:18px;background:var(--fp-bg);padding:15px;display:flex;align-items:center;gap:12px;min-width:0;}
.fp-stat-icon{width:40px;height:40px;border-radius:13px;background:rgba(139,92,246,.14);border:1px solid rgba(139,92,246,.24);display:flex;align-items:center;justify-content:center;color:#c4b5fd;flex-shrink:0;}
.fp-game-stack{gap:0;overflow:hidden}.fp-game-stack .fp-game-img{width:20px;height:20px;margin-left:-5px;border-radius:6px}.fp-game-stack .fp-game-img:first-child{margin-left:0}
.fp-stat-label{font-size:.72rem;color:rgba(255,255,255,.38);font-weight:900;text-transform:uppercase;letter-spacing:.07em;}
.fp-stat-value{font-size:1.12rem;color:#fff;font-weight:950;line-height:1.15;margin-top:2px;}
.fp-toolbar{border:1px solid var(--fp-border);border-radius:18px;background:var(--fp-bg);padding:13px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;}
.fp-search{position:relative;min-width:min(360px,100%);flex:1;}
.fp-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.36);font-size:.86rem;}
.fp-search input{width:100%;height:42px;border-radius:13px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.055);color:#fff;outline:none;padding:0 13px 0 39px;font-size:.88rem;font-weight:700;}
.fp-search input:focus{border-color:rgba(139,92,246,.52);box-shadow:0 0 0 3px rgba(139,92,246,.12);}
.fp-search input::placeholder{color:rgba(255,255,255,.28);}
.fp-filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.fp-filter{border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.045);color:rgba(255,255,255,.72);border-radius:999px;padding:7px 13px;font-size:.77rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:8px;line-height:1;}
.fp-filter:hover,.fp-filter.active{background:rgba(139,92,246,.18);border-color:rgba(139,92,246,.42);color:#ddd6fe;}
.fp-panel{border:1px solid var(--fp-border);border-radius:22px;background:var(--fp-bg);overflow:hidden;}
.fp-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025);}
.fp-panel-title{font-size:.98rem;font-weight:950;color:#fff;display:flex;align-items:center;gap:.55rem;}
.fp-table-wrap{overflow-x:auto;}
.fp-table{width:100%;border-collapse:collapse;min-width:980px;}
.fp-table thead tr{background:rgba(255,255,255,.018);border-bottom:1px solid rgba(255,255,255,.06);}
.fp-table thead th{padding:12px 16px;font-size:.67rem;font-weight:950;color:rgba(255,255,255,.34);text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;}
.fp-table tbody tr{border-bottom:1px solid rgba(255,255,255,.045);transition:background .12s;}
.fp-table tbody tr:last-child{border-bottom:none;}
.fp-table tbody tr:hover{background:rgba(139,92,246,.07);}
.fp-table tbody td{padding:13px 16px;vertical-align:middle;color:rgba(255,255,255,.76);font-size:.86rem;}
.fp-form-cell{display:flex;align-items:center;gap:12px;min-width:280px;text-decoration:none;color:inherit;}
.fp-form-icon{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,rgba(139,92,246,.18),rgba(56,189,248,.08));border:1px solid rgba(139,92,246,.22);display:flex;align-items:center;justify-content:center;overflow:hidden;color:#c4b5fd;flex-shrink:0;font-size:1.1rem;}
.fp-form-icon-img{width:68%;height:68%;object-fit:contain;display:block;}
.fp-form-name{color:#fff;font-weight:950;line-height:1.15;}
.fp-form-slug{margin-top:3px;color:rgba(255,255,255,.35);font-size:.74rem;font-weight:750;}
.fp-type{display:inline-flex;align-items:center;padding:5px 9px;border-radius:9px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.075);color:rgba(255,255,255,.62);font-size:.73rem;font-weight:850;text-transform:capitalize;}
.fp-game-pill{display:inline-flex;align-items:center;gap:8px;padding:6px 11px;border-radius:999px;background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.22);color:#7dd3fc;font-size:.76rem;font-weight:900;text-transform:uppercase;line-height:1;}
.fp-game-img{width:20px;height:20px;object-fit:contain;display:block;flex-shrink:0;border-radius:6px;}
.fp-filter .fp-game-img{width:22px;height:22px;margin-left:-3px;}
.fp-empty{display:none;text-align:center;padding:46px 20px;color:rgba(255,255,255,.42);}
.fp-empty.is-visible{display:block;}
.fp-empty i{font-size:2.5rem;opacity:.28;display:block;margin-bottom:12px;}

/* Dashboard-matched game display */
.fp-stat--games{justify-content:space-between;align-items:center;gap:14px;}
.fp-game-list{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;min-width:0;}
.fp-game-mini{display:inline-flex;align-items:center;gap:7px;min-height:32px;padding:6px 9px;border-radius:999px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.78);font-size:.72rem;font-weight:950;line-height:1;}
.fp-game-mini .fp-game-img{width:20px;height:20px;margin:0;border-radius:6px;}
.fp-filter{min-height:40px;padding:7px 14px;background:rgba(255,255,255,.045);border-color:rgba(255,255,255,.10);}
.fp-filter .fp-game-img{width:20px;height:20px;margin:0;border-radius:6px;}
.fp-filter.active{background:rgba(124,92,255,.18);border-color:rgba(124,92,255,.45);color:#fff;}
.fp-game-pill{background:rgba(255,255,255,.045);border-color:rgba(255,255,255,.09);color:rgba(255,255,255,.82);}
.fp-game-pill .fp-game-img{width:20px;height:20px;margin:0;}
.fp-stat{box-shadow:inset 0 1px 0 rgba(255,255,255,.025);}
.fp-panel,.fp-toolbar,.fp-stat{background:var(--fp-bg)!important;}

@media(max-width:991px){.fp-stats{grid-template-columns:repeat(2,minmax(0,1fr));}.fp-toolbar{align-items:stretch;}.fp-search{min-width:100%;}.fp-filters{width:100%;}.fp-filter{flex:1;}}
@media(max-width:575px){.fp-stats{grid-template-columns:1fr;}.fp-hero{padding:18px}.fp-title{font-size:1.15rem;}}
</style>
<?= $this->end() ?>

<div class="fp-page">
    <div class="fp-hero">
        <div class="fp-hero-left">
            <div class="fp-hero-icon"><i class="fa-duotone fa-sliders"></i></div>
            <div>
                <h1 class="fp-title">Boost Forms Pricing</h1>
                <div class="fp-sub">Manage form prices, modifiers and active boost products in one clean overview.</div>
            </div>
        </div>
        <div class="fp-hero-actions">
            <button type="button" class="fp-btn" id="fpResetFilters"><i class="fa-duotone fa-rotate-left"></i> Reset filters</button>
        </div>
    </div>

    <div class="fp-stats">
        <div class="fp-stat"><div class="fp-stat-icon"><i class="fa-duotone fa-layer-group"></i></div><div><div class="fp-stat-label">Total Forms</div><div class="fp-stat-value"><?= count($forms) ?></div></div></div>
        <div class="fp-stat"><div class="fp-stat-icon" style="color:#4ade80;background:rgba(74,222,128,.1);border-color:rgba(74,222,128,.22);"><i class="fa-duotone fa-circle-check"></i></div><div><div class="fp-stat-label">Active</div><div class="fp-stat-value"><?= (int)$totalActive ?></div></div></div>
        <div class="fp-stat"><div class="fp-stat-icon" style="color:#facc15;background:rgba(250,204,21,.1);border-color:rgba(250,204,21,.22);"><i class="fa-duotone fa-circle-pause"></i></div><div><div class="fp-stat-label">Inactive</div><div class="fp-stat-value"><?= (int)$totalInactive ?></div></div></div>
        <div class="fp-stat fp-stat--games"><div><div class="fp-stat-label">Games</div><div class="fp-stat-value"><?= count($gameLabels) ?></div></div><div class="fp-game-list"><?php foreach ($gameLabels as $g => $label): ?><span class="fp-game-mini"><?= $renderGameIcon($g) ?><span><?= $h($label) ?></span></span><?php endforeach; ?></div></div>
    </div>

    <div class="fp-toolbar">
        <div class="fp-search"><i class="fa-duotone fa-search"></i><input id="fpSearch" type="search" placeholder="Search forms, games or slugs..."></div>
        <div class="fp-filters" id="fpFilters">
            <button type="button" class="fp-filter active" data-game="all">All</button>
            <?php foreach ($gameLabels as $game => $label): ?>
                <button type="button" class="fp-filter" data-game="<?= $h(strtolower($game)) ?>"><?= $renderGameIcon($game) ?><span><?= $h($label) ?></span></button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="fp-panel">
        <div class="fp-panel-head">
            <div class="fp-panel-title"><i class="fa-duotone fa-list-check"></i> Pricing Forms</div>
            <div style="color:rgba(255,255,255,.38);font-size:.82rem;font-weight:800;"><span id="fpVisibleCount"><?= count($forms) ?></span> visible</div>
        </div>
        <?php if (empty($forms)): ?>
            <div class="fp-empty is-visible"><i class="fa-duotone fa-folder-open"></i><strong>No forms found</strong><br>No pricing forms are available yet.</div>
        <?php else: ?>
        <div class="fp-table-wrap">
            <table class="fp-table" id="fpFormsTable">
                <thead><tr><th>Name</th><th>Game</th><th>Type</th><th>Created</th><th>Status</th><th style="text-align:right;">Action</th></tr></thead>
                <tbody>
                    <?php foreach ($forms as $row):
                        $game = strtolower(trim((string)($row['game'] ?? '')));
                        $searchBlob = strtolower(trim(($row['name'] ?? '') . ' ' . ($row['name_long'] ?? '') . ' ' . ($row['slug'] ?? '') . ' ' . ($row['game'] ?? '') . ' ' . ($row['type'] ?? '')));
                    ?>
                    <tr data-search="<?= $h($searchBlob) ?>" data-game="<?= $h($game) ?>">
                        <td>
                            <a class="fp-form-cell" href="<?= $h($editUrl($row)) ?>">
                                <span class="fp-form-icon"><?= $renderFormIcon($row) ?></span>
                                <span>
                                    <span class="fp-form-name"><?= $h(util_format_game_short($row['game']) . ' ' . $row['name']) ?></span>
                                    <span class="fp-form-slug">#<?= $h($row['slug'] ?? '') ?></span>
                                </span>
                            </a>
                        </td>
                        <td><span class="fp-game-pill"><?= $renderGameIcon($game) ?><span><?= $h($gameName($game)) ?></span></span></td>
                        <td><span class="fp-type"><?= $h(str_replace(['-', '_'], ' ', (string)($row['type'] ?? '—'))) ?></span></td>
                        <td><?= $h(util_format_date_display($row['created_at'])) ?></td>
                        <td><?= util_format_smurf_pkg_status($row['status']) ?></td>
                        <td style="text-align:right;"><a href="<?= $h($editUrl($row)) ?>" class="fp-btn fp-btn-primary"><i class="fa-duotone fa-pen-to-square"></i> Edit pricing</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="fp-empty" id="fpNoResults"><i class="fa-duotone fa-magnifying-glass"></i><strong>No matching forms</strong><br>Try another search or filter.</div>
        <?php endif; ?>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var search = document.getElementById('fpSearch');
  var filters = document.querySelectorAll('.fp-filter');
  var countEl = document.getElementById('fpVisibleCount');
  var emptyEl = document.getElementById('fpNoResults');
  var activeGame = 'all';
  function applyFilters(){
    var q = String(search && search.value || '').toLowerCase().trim();
    var visible = 0;
    document.querySelectorAll('#fpFormsTable tbody tr').forEach(function(row){
      var gameOk = activeGame === 'all' || row.dataset.game === activeGame;
      var searchOk = !q || String(row.dataset.search || '').indexOf(q) !== -1;
      var show = gameOk && searchOk;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (countEl) countEl.textContent = visible;
    if (emptyEl) emptyEl.classList.toggle('is-visible', visible === 0);
  }
  if (search) search.addEventListener('input', applyFilters);
  filters.forEach(function(btn){ btn.addEventListener('click', function(){ filters.forEach(function(b){b.classList.remove('active');}); btn.classList.add('active'); activeGame = btn.dataset.game || 'all'; applyFilters(); }); });
  var reset = document.getElementById('fpResetFilters');
  if (reset) reset.addEventListener('click', function(){ if(search) search.value=''; activeGame='all'; filters.forEach(function(b){ b.classList.toggle('active', b.dataset.game === 'all'); }); applyFilters(); });
})();
</script>
<?= $this->end() ?>
