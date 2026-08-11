<?php
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
$icon = trim((string)($data['icon'] ?? ''));
$renderIcon = function() use ($icon, $h, $svgBaseUrl): string {
    if ($icon !== '' && str_ends_with(strtolower($icon), '.svg')) {
        return '<img src="' . $h($svgBaseUrl . '/' . basename($icon)) . '" alt="">';
    }
    if ($icon !== '') return '<i class="fa-duotone ' . $h($icon) . '"></i>';
    return '<i class="fa-duotone fa-wand-magic-sparkles"></i>';
};
$countLeafValues = function($value) use (&$countLeafValues): int {
    if (!is_array($value)) return is_numeric($value) ? 1 : 0;
    $count = 0;
    foreach ($value as $item) $count += $countLeafValues($item);
    return $count;
};
$pricingInputs = $countLeafValues($data['json'] ?? []);
$sectionCount = is_array($data['json'] ?? null) ? count($data['json']) : 0;
$game = strtolower((string)($data['game'] ?? ''));
$gameIconUrl = function_exists('util_game_icon_url') ? util_game_icon_url($game) : '';
$renderGameIcon = static function() use ($h, $gameIconUrl, $game): string {
    if ($gameIconUrl !== '') return '<img class="fp-game-img" src="' . $h($gameIconUrl) . '" alt="' . $h(function_exists('util_game_display_name') ? util_game_display_name($game) : $game) . '">';
    return '<i class="fa-solid fa-gamepad"></i>';
};
$safeGame = preg_replace('/[^a-z0-9_-]/', '', $game);
$safeType = preg_replace('/[^a-z0-9_-]/', '', strtolower((string)($data['type'] ?? '')));
$priceTemplate = 'admin/pages/orders/forms/price-tables/' . $safeGame . '/' . $safeType;
$priceTemplateFile = rtrim((string)SYS_PATH, '/\\') . '/public/views/' . $priceTemplate . '.php';
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Edit ' . $data['name'] . ' Form Pricing - Admin Area | LoLBoost.gg', 'h1' => 'Edit ' . $data['name'] . ' Form Pricing', 'description' => 'Edit the boost form pricing.']]) ?>
<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/hs-table-sticky-header/src/hs.table-sticky-header.css">
<style>
:root{--unranked:#6b6963;--iron:#51484A;--bronze:#8C513A;--silver:#80989D;--gold:#CD8837;--platinum:#4E9996;--emerald:#4bc374;--diamond:#576BCE;--master:#9D48E0;--grandmaster:#CD4545;--challenger:#3FBFDD;--unranked-dark:#2b2a28;--iron-dark:#201d1e;--bronze-dark:#382017;--silver-dark:#323e40;--gold-dark:#533615;--platinum-dark:#1f3d3c;--diamond-dark:#1a255b;--master-dark:#401165;--grandmaster-dark:#561717;--challenger-dark:#115160;--ascendant:#3f9367;--immortal:#a52b48;--radiant:#d4af37;}
.border-iron{border-left:4px solid var(--iron)!important;border-bottom:transparent!important}.border-bronze{border-left:4px solid var(--bronze)!important;border-bottom:transparent!important}.border-silver{border-left:4px solid var(--silver)!important;border-bottom:transparent!important}.border-gold{border-left:4px solid var(--gold)!important;border-bottom:transparent!important}.border-platinum{border-left:4px solid var(--platinum)!important;border-bottom:transparent!important}.border-emerald{border-left:4px solid var(--emerald)!important;border-bottom:transparent!important}.border-diamond{border-left:4px solid var(--diamond)!important;border-bottom:transparent!important}.border-master{border-left:4px solid var(--master)!important;border-bottom:transparent!important}.border-grandmaster{border-left:4px solid var(--grandmaster)!important;border-bottom:transparent!important}.border-challenger{border-left:4px solid var(--challenger)!important;border-bottom:transparent!important}.border-immortal{border-left:4px solid var(--immortal)!important;border-bottom:transparent!important}.border-ascendant{border-left:4px solid var(--ascendant)!important;border-bottom:transparent!important}.border-radiant{border-left:4px solid var(--radiant)!important;border-bottom:transparent!important}.border-unranked{border-left:4px solid var(--unranked)!important;border-bottom:transparent!important}
.fp-edit{--fp-bg:#222527;--fp-panel:#1b1d1f;--fp-surface:#262a2d;--fp-surface-2:#2b3033;--fp-border:rgba(255,255,255,.075);--fp-muted:rgba(255,255,255,.46);--fp-purple:#7c5cff;--fp-cyan:#57c7ff;--fp-green:#26d39a;}
.fp-edit .card{background:var(--fp-bg)!important;border:1px solid var(--fp-border)!important;border-radius:22px!important;box-shadow:none!important;overflow:hidden;}
.fp-edit .card::before{display:none!important;}
.fp-edit .card-header{border-bottom:1px solid rgba(255,255,255,.06)!important;background:rgba(255,255,255,.025)!important;padding:15px 18px!important;}
.fp-edit .card-header-title{color:#fff!important;font-weight:950!important;font-size:.98rem!important;display:flex;align-items:center;gap:.5rem;}
.fp-edit .card-body{padding:18px!important;}
.fp-hero{position:relative;overflow:hidden;border:1px solid var(--fp-border);border-radius:24px;background:linear-gradient(135deg,rgba(124,92,255,.15),rgba(49,60,66,.45) 52%,rgba(38,42,45,.98)),var(--fp-bg);padding:24px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;}
.fp-hero-left{display:flex;align-items:center;gap:15px;min-width:0;}
.fp-hero-icon{width:58px;height:58px;border-radius:18px;background:linear-gradient(135deg,rgba(124,92,255,.22),rgba(255,255,255,.045));border:1px solid rgba(124,92,255,.28);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.35rem;box-shadow:0 16px 38px rgba(0,0,0,.18);overflow:hidden;}
.fp-hero-icon img{width:70%;height:70%;object-fit:contain;display:block;}
.fp-title{margin:0;color:#fff;font-size:1.34rem;font-weight:950;letter-spacing:-.035em;line-height:1.08;}
.fp-sub{margin-top:5px;color:var(--fp-muted);font-size:.84rem;font-weight:650;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.fp-chip{display:inline-flex;align-items:center;gap:8px;padding:5px 10px;border-radius:999px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.7);font-size:.72rem;font-weight:900;line-height:1;}
.fp-chip--game{background:rgba(56,189,248,.1);border-color:rgba(56,189,248,.23);color:#7dd3fc;text-transform:uppercase;}
.fp-game-img{width:20px;height:20px;object-fit:contain;display:block;flex-shrink:0;border-radius:6px;}
.fp-meta-game{display:flex;align-items:center;gap:8px;}
.fp-hero-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.fp-btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border:1px solid rgba(255,255,255,.11);background:rgba(255,255,255,.055);color:rgba(255,255,255,.74);text-decoration:none;border-radius:12px;padding:9px 14px;font-size:.82rem;font-weight:900;cursor:pointer;transition:background .15s,border-color .15s,color .15s,transform .12s;}
.fp-btn:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.18);color:#fff;transform:translateY(-1px);}
.fp-btn-primary{background:linear-gradient(135deg,#8b5cf6,#c026d3);border-color:rgba(192,132,252,.42);color:#fff;}
.fp-layout{display:grid;grid-template-columns:minmax(280px,380px) minmax(0,1fr);gap:16px;align-items:start;}
.fp-side{position:sticky;top:82px;}
.fp-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;}
.fp-meta{border:1px solid rgba(255,255,255,.08);border-radius:15px;background:rgba(255,255,255,.035);padding:12px;}
.fp-meta-label{font-size:.68rem;color:rgba(255,255,255,.36);font-weight:950;text-transform:uppercase;letter-spacing:.08em;}
.fp-meta-value{font-size:1.05rem;color:#fff;font-weight:950;margin-top:2px;}
.fp-edit .form-label{font-size:.72rem;font-weight:950;color:rgba(255,255,255,.48);text-transform:uppercase;letter-spacing:.075em;margin-bottom:7px;}
.fp-edit .form-control{background:rgba(255,255,255,.055)!important;border:1px solid rgba(255,255,255,.1)!important;border-radius:12px!important;color:#fff!important;font-weight:700;box-shadow:none!important;}
.fp-edit .form-control:focus{border-color:rgba(139,92,246,.52)!important;box-shadow:0 0 0 3px rgba(139,92,246,.12)!important;}
.fp-edit .form-control::placeholder{color:rgba(255,255,255,.25)!important;}
.fp-price-tools{border:1px solid var(--fp-border);border-radius:18px;background:var(--fp-bg);padding:13px;margin-bottom:16px;display:flex;align-items:center;gap:12px;justify-content:space-between;flex-wrap:wrap;}
.fp-search{position:relative;flex:1;min-width:min(360px,100%);}
.fp-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.36);font-size:.86rem;}
.fp-search input{width:100%;height:42px;border-radius:13px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.055);color:#fff;outline:none;padding:0 13px 0 39px;font-size:.88rem;font-weight:700;}
.fp-search input:focus{border-color:rgba(139,92,246,.52);box-shadow:0 0 0 3px rgba(139,92,246,.12);}
.fp-price-tools-note{color:rgba(255,255,255,.38);font-size:.78rem;font-weight:800;}
.fp-edit .table-responsive{border-radius:16px;border:1px solid rgba(255,255,255,.065);background:var(--fp-panel);overflow:auto;}
.fp-edit table.table{margin:0!important;color:rgba(255,255,255,.78)!important;}
.fp-edit table.table thead tr{background:rgba(255,255,255,.035)!important;border-bottom:1px solid rgba(255,255,255,.07)!important;}
.fp-edit table.table thead th{padding:12px 14px!important;color:rgba(255,255,255,.38)!important;font-size:.68rem!important;font-weight:950!important;letter-spacing:.08em!important;text-transform:uppercase!important;border:0!important;}
.fp-edit table.table tbody tr{border-bottom:1px solid rgba(255,255,255,.045)!important;transition:background .12s;}
.fp-edit table.table tbody tr:last-child{border-bottom:0!important;}
.fp-edit table.table tbody tr:hover{background:rgba(139,92,246,.07)!important;}
.fp-edit table.table tbody td{padding:10px 14px!important;border:0!important;color:rgba(255,255,255,.76)!important;font-weight:750;}
.fp-edit table.table img{border-radius:7px;}
.fp-edit table.table input.form-control{width:116px!important;height:38px!important;text-align:right!important;border-radius:10px!important;padding:6px 10px!important;font-weight:900!important;background:rgba(255,255,255,.07)!important;}
.fp-edit .text-muted{color:rgba(255,255,255,.4)!important;}
.fp-empty-prices{display:none;text-align:center;color:rgba(255,255,255,.42);padding:30px;border:1px dashed rgba(255,255,255,.12);border-radius:16px;background:rgba(255,255,255,.025);margin-bottom:16px;}
.fp-empty-prices.is-visible{display:block;}
#footer_popup .card{border-radius:18px!important;background:rgba(20,22,30,.96)!important;border:1px solid rgba(139,92,246,.26)!important;box-shadow:0 20px 70px rgba(0,0,0,.42),0 0 0 1px rgba(255,255,255,.035) inset!important;}
#footer_popup .btn-primary{background:linear-gradient(135deg,#8b5cf6,#c026d3)!important;border:0!important;border-radius:12px!important;font-weight:950!important;}
#footer_popup .btn-ghost-light{border-radius:12px!important;font-weight:900!important;}

/* Uniform pricing table surface */
.fp-edit .card:has(table),
.fp-edit .table-responsive,
.fp-edit .js-sticky-header,
.fp-edit table.table,
.fp-edit table.table thead,
.fp-edit table.table tbody,
.fp-edit table.table tr,
.fp-edit table.table th,
.fp-edit table.table td{background:#1e2028!important;background-color:#1e2028!important;}
.fp-edit .table-responsive{box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;}
.fp-edit table.table thead tr,
.fp-edit table.table thead th{background:#2a2d30!important;background-color:#2a2d30!important;}
.fp-edit table.table tbody tr:nth-child(even),
.fp-edit table.table tbody tr:nth-child(odd){background:#1e2028!important;background-color:#1e2028!important;}
.fp-edit table.table tbody tr:hover,
.fp-edit table.table tbody tr:hover td{background:#242636!important;background-color:#242636!important;}
.fp-edit table.table td.table-active,
.fp-edit table.table th.table-active,
.fp-edit table.table .table-active,
.fp-edit table.table .bg-soft-dark,
.fp-edit table.table .bg-light,
.fp-edit table.table .bg-white{background:#1e2028!important;background-color:#1e2028!important;}
.fp-edit table.table td:first-child{background:#1e2028!important;background-color:#1e2028!important;}
.fp-edit table.table input.form-control{background:#2b2e36!important;border-color:rgba(255,255,255,.12)!important;}


/* Dashboard color pass + cleaner pricing tables */
.fp-edit .card{background:var(--fp-bg)!important;border-color:rgba(255,255,255,.075)!important;}
.fp-edit .card-header{background:var(--fp-bg)!important;border-bottom-color:rgba(255,255,255,.065)!important;}
.fp-edit .card-body{background:var(--fp-bg)!important;}
.fp-price-tools,.fp-meta{background:var(--fp-bg)!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.025);}
.fp-chip--game,.fp-meta-game{display:inline-flex;align-items:center;gap:8px;white-space:nowrap;}
.fp-chip--game{background:rgba(255,255,255,.045);border-color:rgba(255,255,255,.09);color:rgba(255,255,255,.84);}
.fp-game-img{width:20px;height:20px;margin:0;border-radius:6px;}
.fp-edit .sticky-header-cloned-wrapper{display:none!important;}
.fp-edit .sticky-header-original-th-inner-wrapper{display:contents!important;background:transparent!important;}
.fp-edit .table-responsive{border-radius:16px;border:1px solid rgba(255,255,255,.075)!important;background:var(--fp-bg)!important;overflow:auto;}
.fp-edit .card:has(table),
.fp-edit .card:has(table) .card-body,
.fp-edit .js-sticky-header,
.fp-edit table.table,
.fp-edit table.table thead,
.fp-edit table.table tbody,
.fp-edit table.table tr,
.fp-edit table.table th,
.fp-edit table.table td{background:var(--fp-bg)!important;background-color:var(--fp-bg)!important;}
.fp-edit table.table thead tr,
.fp-edit table.table thead th{background:var(--fp-surface-2)!important;background-color:var(--fp-surface-2)!important;}
.fp-edit table.table thead th:first-child{border-top-left-radius:10px!important;}
.fp-edit table.table thead th:last-child{border-top-right-radius:10px!important;}
.fp-edit table.table tbody tr:nth-child(even),
.fp-edit table.table tbody tr:nth-child(odd),
.fp-edit table.table tbody td{background:var(--fp-bg)!important;background-color:var(--fp-bg)!important;}
.fp-edit table.table tbody tr:hover,
.fp-edit table.table tbody tr:hover td{background:#282c2f!important;background-color:#282c2f!important;}
.fp-edit table.table td.table-active,
.fp-edit table.table th.table-active,
.fp-edit table.table .table-active,
.fp-edit table.table .bg-soft-dark,
.fp-edit table.table .bg-light,
.fp-edit table.table .bg-white{background:var(--fp-bg)!important;background-color:var(--fp-bg)!important;}
.fp-edit table.table td:first-child{background:var(--fp-bg)!important;background-color:var(--fp-bg)!important;}
.fp-edit table.table input.form-control{background:#2d3135!important;border-color:rgba(255,255,255,.13)!important;color:#fff!important;}
.fp-edit table.table input.form-control:focus{background:#31363b!important;border-color:rgba(124,92,255,.48)!important;box-shadow:0 0 0 3px rgba(124,92,255,.12)!important;}
.fp-edit table.table tbody td:first-child{display:flex;align-items:center;gap:8px;}
.fp-edit table.table tbody td:first-child img{width:25px!important;height:25px!important;object-fit:contain;margin:0!important;padding:0!important;flex-shrink:0;}

@media(max-width:1199px){.fp-layout{grid-template-columns:1fr}.fp-side{position:static}.fp-meta-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:767px){.fp-meta-grid{grid-template-columns:1fr}.fp-hero{padding:18px}.fp-title{font-size:1.13rem}.fp-price-tools{align-items:stretch}.fp-search{min-width:100%;}}
</style>
<?= $this->end() ?>

<div class="fp-edit">
<form class="form ajax-form" action="<?= AJAX_URL ?>">
    <input type="text" name="action" value="update_boost_form" hidden>
    <input type="text" name="uuid" value="<?= $h($data['uuid'] ?? '') ?>" hidden>
    <input type="text" name="slug" value="<?= $h($data['slug'] ?? '') ?>" hidden>
    <input type="text" name="id" value="<?= (int)($data['id'] ?? 0) ?>" hidden>

    <div class="fp-hero">
        <div class="fp-hero-left">
            <div class="fp-hero-icon"><?= $renderIcon() ?></div>
            <div>
                <h1 class="fp-title">Edit <?= $h($data['name'] ?? 'Boost Form') ?> Pricing</h1>
                <div class="fp-sub">
                    <span class="fp-chip fp-chip--game"><?= $renderGameIcon() ?><span><?= $h(strtoupper($game ?: 'game')) ?></span></span>
                    <span class="fp-chip">#<?= $h($data['slug'] ?? '') ?></span>
                    <span class="fp-chip"><?= $h($data['type'] ?? 'pricing') ?></span>
                </div>
            </div>
        </div>
        <div class="fp-hero-actions">
            <a href="<?= ADMN_URL ?>/boost/forms" class="fp-btn"><i class="fa-duotone fa-arrow-left"></i> All forms</a>
            <button type="submit" class="fp-btn fp-btn-primary"><i class="fa-duotone fa-floppy-disk"></i> Save changes</button>
        </div>
    </div>

    <div class="fp-layout">
        <div class="fp-side">
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-header-title"><i class="fa-duotone fa-circle-info"></i> Form Info</h5></div>
                <div class="card-body">
                    <div class="fp-meta-grid">
                        <div class="fp-meta"><div class="fp-meta-label">Sections</div><div class="fp-meta-value"><?= (int)$sectionCount ?></div></div>
                        <div class="fp-meta"><div class="fp-meta-label">Inputs</div><div class="fp-meta-value"><?= (int)$pricingInputs ?></div></div>
                        <div class="fp-meta"><div class="fp-meta-label">Game</div><div class="fp-meta-value fp-meta-game"><?= $renderGameIcon() ?><span><?= $h(strtoupper($game ?: '—')) ?></span></div></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="name">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= $h($data['name'] ?? '') ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="name_long">Name Long</label>
                        <input type="text" name="name_long" class="form-control" value="<?= $h($data['name_long'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label" for="description">Description</label>
                        <textarea name="description" class="form-control" placeholder="Description" rows="4"><?= $h($data['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="fp-price-tools">
                <div class="fp-search"><i class="fa-duotone fa-search"></i><input type="search" id="fpPriceSearch" placeholder="Search pricing rows, ranks or options..."></div>
                <div class="fp-price-tools-note"><span id="fpPriceVisible">0</span> visible rows</div>
            </div>
            <div class="fp-empty-prices" id="fpPriceEmpty"><i class="fa-duotone fa-magnifying-glass"></i><br>No pricing rows match your search.</div>
            <?php if (is_file($priceTemplateFile)): ?>
                <?= $this->insert($priceTemplate, ['data' => $data['json']]) ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:38px!important;">
                        <i class="fa-duotone fa-triangle-exclamation" style="font-size:2rem;color:#facc15;"></i>
                        <h3 style="margin:14px 0 6px;color:#fff;">Pricing editor not assigned</h3>
                        <p class="text-muted">This form belongs to the generic game editor. Open it from the Games area or return to the forms overview.</p>
                        <a href="<?= ADMN_URL ?>/boost/forms" class="fp-btn fp-btn-primary"><i class="fa-duotone fa-arrow-left"></i> All forms</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="footer_popup" class="position-fixed start-50 bottom-0 translate-middle-x w-100 zi-99 mb-3 d-none" style="max-width: 44rem;">
        <div class="card card-sm mx-2">
            <div class="card-body">
                <div class="row justify-content-center justify-content-sm-between align-items-center g-3">
                    <div class="col"><span class="fw-500 text-white"><i class="fa-duotone fa-triangle-exclamation me-2" style="color:#facc15;"></i>You have unsaved pricing changes.</span></div>
                    <div class="col-auto"><div class="d-flex gap-3"><button type="button" id="reset_form" class="btn btn-ghost-light">Reset</button><button type="submit" id="submit_form" class="btn btn-primary"><span class="indicator-label">Save Changes</span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span><span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span></button></div></div>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-table-sticky-header/dist/hs-table-sticky-header.min.js"></script>
<script>
$(document).ready(function () {
    $(function () { $('form').find(':input').each(function (i, elem) { var input = $(elem); input.data('initialState', input.val()); }); });
    function restore() { $('form').find(':input').each(function (i, elem) { var input = $(elem); input.val(input.data('initialState')); }); }
    $('#reset_form').on('click', function () { footer_pop_toggle('hide'); restore(); });
    $('.ajax-form input, .ajax-form textarea').on('keyup change', function () { if (footer_pop == false) { footer_pop_toggle('show'); } });
    $.each($('.js-sticky-header'), function () { new HSTableStickyHeader(this, { offsetTop: '72px' }).init(); });

    function refreshPriceSearch(){
        var q = String($('#fpPriceSearch').val() || '').toLowerCase().trim();
        var visible = 0;
        $('.fp-edit table tbody tr').each(function(){
            var text = String($(this).text() || '').toLowerCase();
            var show = !q || text.indexOf(q) !== -1;
            $(this).toggle(show);
            if (show) visible++;
        });
        $('#fpPriceVisible').text(visible);
        $('#fpPriceEmpty').toggleClass('is-visible', visible === 0);
        $('.fp-edit .card').each(function(){
            var hasRows = $(this).find('tbody tr:visible').length > 0 || $(this).find('tbody tr').length === 0;
            if ($(this).closest('#footer_popup').length) return;
            if ($(this).find('table').length) $(this).toggle(hasRows || !q);
        });
    }
    $('#fpPriceSearch').on('input', refreshPriceSearch);
    refreshPriceSearch();
});
</script>
<?= $this->end() ?>
