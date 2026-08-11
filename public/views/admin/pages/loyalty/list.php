<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Loyalty Ranks — Admin', 'h1' => 'Loyalty Ranks', 'description' => 'Manage loyalty ranks and cashback tiers.']]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$totalClients = array_sum(array_column($data, 'clients'));

// Map rank names (lowercase) → icon slug
$rankIconMap = [
    'silver'      => 'silver',
    'gold'        => 'gold',
    'platinum'    => 'platinum',
    'diamond'     => 'diamond',
    'master'      => 'master',
    'grandmaster' => 'grandmaster',
    'challenger'  => 'challenger',
];

function loyaltyRankIcon(string $name, string $assetUrl): string {
    $rankIconMap = ['silver'=>'silver','gold'=>'gold','platinum'=>'platinum','diamond'=>'diamond','master'=>'master','grandmaster'=>'grandmaster','challenger'=>'challenger'];
    $slug = $rankIconMap[strtolower(trim($name))] ?? null;
    if ($slug) {
        $src = htmlspecialchars($assetUrl . '/core/main/img/loyalty/' . $slug . '_icon.svg', ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        return '<img src="' . $src . '" alt="' . $alt . '">';
    }
    return '<i class="fa-duotone fa-crown" style="font-size:.85rem;"></i>';
}
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats ── */
.lr-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;}
.lr-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.lr-stat:hover{transform:translateY(-2px);}
.lr-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.lr-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.lr-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.al-search-wrap{position:relative;display:flex;align-items:center;}
.al-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:7px 12px 7px 34px;outline:none;width:220px;transition:border-color .15s;}
.al-search-wrap input:focus{border-color:rgba(109,92,255,.45);box-shadow:0 0 0 3px rgba(109,92,255,.12);}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25);}
.al-search-icon{position:absolute;left:10px;color:rgba(255,255,255,.3);font-size:.78rem;pointer-events:none;}
/* ── Table ── */
.al-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.2);margin-bottom:14px;}
.al-table{width:100%;border-collapse:collapse;}
.al-table thead tr{border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:12px 16px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;background:rgba(0,0,0,.12);}
.al-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.al-table tbody tr:last-child{border-bottom:none;}
.al-table tbody tr:hover{background:rgba(255,255,255,.025);}
.al-table td{padding:13px 16px;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle;}
/* ── Rank badge ── */
.lr-rank-badge{display:inline-flex;align-items:center;gap:8px;padding:5px 14px 5px 6px;border-radius:99px;font-size:.82rem;font-weight:800;}
.lr-rank-badge img{width:32px;height:32px;object-fit:contain;flex-shrink:0;filter:drop-shadow(0 1px 4px rgba(0,0,0,.5));}
/* ── Amount / cashback ── */
.lr-amount{font-weight:800;color:rgba(255,255,255,.88);}
.lr-cashback{display:inline-flex;align-items:center;gap:4px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.22);color:#4ade80;padding:3px 10px;border-radius:99px;font-size:.78rem;font-weight:800;}
.lr-clients{font-weight:700;color:rgba(255,255,255,.55);}
/* ── Action buttons ── */
.lr-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 13px;border-radius:9px;font-size:.78rem;font-weight:800;cursor:pointer;border:none;text-decoration:none;transition:all .12s;}
.lr-btn--edit{background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.3);color:#c4b5fd;}
.lr-btn--edit:hover{background:rgba(109,92,255,.24);color:#c4b5fd;transform:translateY(-1px);}
/* ── Footer ── */
.al-footer{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
/* ── Empty state ── */
.al-empty{padding:48px 20px;text-align:center;color:rgba(255,255,255,.25);}
.al-empty i{font-size:2.2rem;margin-bottom:10px;display:block;opacity:.35;}
/* ── Add button ── */
.lr-add-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:rgba(109,92,255,.18);border:1px solid rgba(109,92,255,.35);color:#c4b5fd;font-size:.82rem;font-weight:800;text-decoration:none;transition:all .13s;}
.lr-add-btn:hover{background:rgba(109,92,255,.28);color:#c4b5fd;transform:translateY(-1px);}
/* ── Row colors per tier — matching real LoL rank colors ── */
.lr-tier-1{background:rgba(160,160,175,.1);border:1px solid rgba(160,160,175,.3);color:#b8b8c8;}   /* Silver  — grau     */
.lr-tier-2{background:rgba(242,148,33,.1);border:1px solid rgba(242,148,33,.35);color:#f29421;}    /* Gold    — orange   */
.lr-tier-3{background:rgba(16,170,203,.1);border:1px solid rgba(16,170,203,.35);color:#10aacb;}    /* Platinum— cyan     */
.lr-tier-4{background:rgba(89,92,242,.12);border:1px solid rgba(89,92,242,.35);color:#8b8ef5;}     /* Diamond — blau     */
.lr-tier-5{background:rgba(156,58,231,.12);border:1px solid rgba(156,58,231,.35);color:#b36be8;}   /* Master  — lila     */
.lr-tier-6{background:rgba(237,28,28,.12);border:1px solid rgba(237,28,28,.35);color:#f05555;}     /* Grandmaster— rot  */
.lr-tier-7{background:rgba(242,204,109,.12);border:1px solid rgba(242,204,109,.35);color:#f2cc6d;} /* Challenger— gold  */
.lr-tier-default{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.65);}
</style>
<?= $this->end() ?>

<!-- Stats -->
<div class="lr-stats">
    <div class="lr-stat">
        <div class="lr-stat-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-duotone fa-layer-group"></i></div>
        <div><div class="lr-stat-label">Total Ranks</div><div class="lr-stat-value"><?= count($data) ?></div></div>
    </div>
    <div class="lr-stat">
        <div class="lr-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-users"></i></div>
        <div><div class="lr-stat-label">Total Clients</div><div class="lr-stat-value"><?= $totalClients ?></div></div>
    </div>
    <div class="lr-stat">
        <div class="lr-stat-icon" style="background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.2);color:#facc15;"><i class="fa-duotone fa-coins"></i></div>
        <div><div class="lr-stat-label">Highest Cashback</div><div class="lr-stat-value"><?= !empty($data) ? max(array_column($data, 'cashback')) : 0 ?>%</div></div>
    </div>
    <div class="lr-stat">
        <div class="lr-stat-icon" style="background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.2);color:#fb7185;"><i class="fa-duotone fa-euro-sign"></i></div>
        <div><div class="lr-stat-label">Top Threshold</div><div class="lr-stat-value">€<?= !empty($data) ? number_format(max(array_column($data, 'target_amount'))) : 0 ?></div></div>
    </div>
</div>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-left">
        <div class="al-hero-icon"><i class="fa-duotone fa-crown"></i></div>
        <div>
            <h2 class="al-hero-title">Loyalty Ranks</h2>
            <p class="al-hero-sub"><?= count($data) ?> tier<?= count($data) !== 1 ? 's' : '' ?> · <?= $totalClients ?> total clients</p>
        </div>
    </div>
    <a href="<?= ADMN_URL ?>/loyalty/add" class="lr-add-btn">
        <i class="fa-solid fa-plus"></i> Add Rank
    </a>
</div>

<!-- Toolbar -->
<div class="al-toolbar-card">
    <div style="font-size:.82rem;color:rgba(255,255,255,.35);font-weight:700;">
        <span id="lrVisibleCount"><?= count($data) ?></span> ranks
    </div>
    <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="search" id="lrSearch" placeholder="Search ranks…">
    </div>
</div>

<!-- Table -->
<div class="al-table-wrap">
    <table class="al-table" id="lrTable">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>Name</th>
                <th class="text-center">Target Amount</th>
                <th class="text-center">Cashback</th>
                <th class="text-center">Clients</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody id="lrTbody">
            <?php if (empty($data)): ?>
                <tr><td colspan="6">
                    <div class="al-empty">
                        <i class="fa-duotone fa-crown"></i>
                        <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No loyalty ranks yet</div>
                        <a href="<?= ADMN_URL ?>/loyalty/add" class="lr-add-btn" style="margin:0 auto;">
                            <i class="fa-solid fa-plus"></i> Add first rank
                        </a>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php
                $tierClasses = ['lr-tier-1','lr-tier-2','lr-tier-3','lr-tier-4','lr-tier-5','lr-tier-6','lr-tier-7'];
                foreach ($data as $index => $row):
                    $tierClass = $tierClasses[$index] ?? 'lr-tier-default';
                ?>
                <tr class="lr-row" data-search="<?= $h(strtolower($row['name'])) ?>">
                    <td><span style="font-size:.78rem;font-weight:800;color:rgba(255,255,255,.3);">#<?= $index + 1 ?></span></td>
                    <td>
                        <span class="lr-rank-badge <?= $tierClass ?>">
                            <?= loyaltyRankIcon($row['name'], ASSET_URL) ?>
                            <?= $h($row['name']) ?>
                        </span>
                    </td>
                    <td class="text-center"><span class="lr-amount">€ <?= number_format((float)$row['target_amount'], 0, '.', ',') ?></span></td>
                    <td class="text-center"><span class="lr-cashback"><i class="fa-solid fa-percent" style="font-size:.6rem;"></i> <?= $h($row['cashback']) ?>%</span></td>
                    <td class="text-center"><span class="lr-clients"><?= (int)$row['clients'] ?></span></td>
                    <td class="text-end">
                        <a href="<?= ADMN_URL ?>/loyalty/<?= (int)$row['id'] ?>" class="lr-btn lr-btn--edit">
                            <i class="fa-duotone fa-pencil"></i> Edit
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<div class="al-footer">
    <div style="font-size:.8rem;color:rgba(255,255,255,.35);">
        Showing <strong id="lrFooterCount"><?= count($data) ?></strong> of <?= count($data) ?> ranks
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function() {
    var searchInput = document.getElementById('lrSearch');
    var rows = document.querySelectorAll('#lrTbody .lr-row');
    var visibleCount = document.getElementById('lrVisibleCount');
    var footerCount = document.getElementById('lrFooterCount');

    function applySearch() {
        var q = (searchInput.value || '').toLowerCase().trim();
        var shown = 0;
        rows.forEach(function(row) {
            var match = !q || (row.getAttribute('data-search') || '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        if (visibleCount) visibleCount.textContent = shown;
        if (footerCount) footerCount.textContent = shown;
    }

    if (searchInput) searchInput.addEventListener('input', applySearch);
})();
</script>
<?= $this->end() ?>
