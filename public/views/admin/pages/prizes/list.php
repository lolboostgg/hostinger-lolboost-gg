<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Prizes — Admin', 'h1' => 'Prizes', 'description' => 'Manage prizes in the LB Coins Store.']]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$shorten = static function ($value, int $limit = 72): string {
    $value = trim((string)($value ?? ''));
    $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    if ($length <= $limit) return $value;
    $cut = function_exists('mb_substr') ? mb_substr($value, 0, $limit - 1, 'UTF-8') : substr($value, 0, $limit - 1);
    return rtrim($cut) . '…';
};
$totalPoints = !empty($data) ? array_sum(array_column($data, 'points')) : 0;
$minPoints   = !empty($data) ? min(array_column($data, 'points')) : 0;
$maxPoints   = !empty($data) ? max(array_column($data, 'points')) : 0;
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats ── */
.pr-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;}
.pr-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.pr-stat:hover{transform:translateY(-2px);}
.pr-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.pr-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.pr-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(251,191,36,.2),rgba(251,191,36,.07));border:1px solid rgba(251,191,36,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fbbf24;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Add button ── */
.pr-add-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:rgba(251,191,36,.14);border:1px solid rgba(251,191,36,.35);color:#fbbf24;font-size:.82rem;font-weight:800;text-decoration:none;transition:all .13s;}
.pr-add-btn:hover{background:rgba(251,191,36,.24);color:#fbbf24;transform:translateY(-1px);}
/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.al-search-wrap{position:relative;display:flex;align-items:center;}
.al-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:7px 12px 7px 34px;outline:none;width:230px;transition:border-color .15s;}
.al-search-wrap input:focus{border-color:rgba(251,191,36,.4);box-shadow:0 0 0 3px rgba(251,191,36,.08);}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25);}
.al-search-icon{position:absolute;left:10px;color:rgba(255,255,255,.3);font-size:.78rem;pointer-events:none;}
/* ── Table ── */
.al-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.2);margin-bottom:14px;}
.al-table{width:100%;border-collapse:collapse;table-layout:fixed;}
.al-table thead tr{border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:12px 16px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;background:rgba(0,0,0,.12);}
.al-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.al-table tbody tr:last-child{border-bottom:none;}
.al-table tbody tr:hover{background:rgba(255,255,255,.025);}
.al-table td{padding:13px 16px;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle;}
/* ── Prize image ── */
.pr-img{width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);}
.pr-img-placeholder{width:44px;height:44px;border-radius:10px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.04);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:.9rem;flex-shrink:0;}
/* ── Cells ── */
.pr-name{display:block;font-weight:850;color:rgba(255,255,255,.9);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.pr-desc{display:block;width:100%;font-size:.78rem;line-height:1.4;color:rgba(255,255,255,.43);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.pr-points{display:inline-flex;align-items:center;gap:4px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);color:#fbbf24;padding:3px 10px;border-radius:99px;font-size:.78rem;font-weight:800;}
.pr-id{font-size:.75rem;font-weight:800;color:rgba(255,255,255,.25);}
/* ── Action buttons ── */
.pr-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:9px;font-size:.78rem;font-weight:800;cursor:pointer;border:none;text-decoration:none;transition:all .12s;}
.pr-btn--edit{background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.3);color:#c4b5fd;}
.pr-btn--edit:hover{background:rgba(109,92,255,.24);color:#c4b5fd;transform:translateY(-1px);}
.pr-btn--delete{background:rgba(251,113,133,.1);border:1px solid rgba(251,113,133,.25);color:#fb7185;}
.pr-btn--delete:hover{background:rgba(251,113,133,.2);color:#fb7185;transform:translateY(-1px);}
.pr-actions{display:flex;align-items:center;justify-content:flex-end;gap:6px;white-space:nowrap;}
/* ── Footer ── */
.al-footer{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
/* ── Empty ── */
.al-empty{padding:48px 20px;text-align:center;color:rgba(255,255,255,.25);}
.al-empty i{font-size:2.2rem;margin-bottom:10px;display:block;opacity:.35;}
@media only screen and (max-width:1100px){
  .al-table thead th,.al-table td{padding-left:10px;padding-right:10px}
  .pr-btn{padding:6px 9px}
}
@media only screen and (max-width:760px){
  .al-search-wrap,.al-search-wrap input{width:100%}
  .al-table-wrap{padding:10px;background:transparent;border:0;box-shadow:none;overflow:visible}
  .al-table,.al-table tbody{display:block}
  .al-table thead{display:none}
  .al-table tbody{display:grid;gap:10px}
  .al-table tbody .pr-row{display:grid;grid-template-columns:48px minmax(0,1fr) auto;grid-template-areas:"image name points" "desc desc desc" "actions actions actions";gap:8px 10px;padding:12px;border:1px solid rgba(255,255,255,.08);border-radius:15px;background:#25282a}
  .al-table tbody .pr-row td{padding:0;border:0;min-width:0}
  .al-table tbody .pr-row td:nth-child(1){display:none}
  .al-table tbody .pr-row td:nth-child(2){grid-area:image}
  .al-table tbody .pr-row td:nth-child(3){grid-area:name;align-self:center}
  .al-table tbody .pr-row td:nth-child(4){grid-area:desc}
  .al-table tbody .pr-row td:nth-child(5){grid-area:points;align-self:center}
  .al-table tbody .pr-row td:nth-child(6){grid-area:actions}
  .pr-actions{justify-content:stretch;padding-top:8px;border-top:1px solid rgba(255,255,255,.06)}
  .pr-actions .pr-btn{flex:1;justify-content:center;padding:8px 10px}
}
</style>
<?= $this->end() ?>

<!-- Stats -->
<div class="pr-stats">
    <div class="pr-stat">
        <div class="pr-stat-icon" style="background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.2);color:#fbbf24;"><i class="fa-duotone fa-gift"></i></div>
        <div><div class="pr-stat-label">Total Prizes</div><div class="pr-stat-value"><?= count($data) ?></div></div>
    </div>
    <div class="pr-stat">
        <div class="pr-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-coins"></i></div>
        <div><div class="pr-stat-label">Cheapest</div><div class="pr-stat-value"><?= $minPoints ?> <span style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.35);">coins</span></div></div>
    </div>
    <div class="pr-stat">
        <div class="pr-stat-icon" style="background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.2);color:#fb7185;"><i class="fa-duotone fa-trophy"></i></div>
        <div><div class="pr-stat-label">Most Expensive</div><div class="pr-stat-value"><?= $maxPoints ?> <span style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.35);">coins</span></div></div>
    </div>
    <div class="pr-stat">
        <div class="pr-stat-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-duotone fa-sigma"></i></div>
        <div><div class="pr-stat-label">Total Coins</div><div class="pr-stat-value"><?= number_format($totalPoints) ?></div></div>
    </div>
</div>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-left">
        <div class="al-hero-icon"><i class="fa-duotone fa-gift"></i></div>
        <div>
            <h2 class="al-hero-title">Prizes</h2>
            <p class="al-hero-sub"><?= count($data) ?> prize<?= count($data) !== 1 ? 's' : '' ?> in the LB Coins Store</p>
        </div>
    </div>
    <a href="<?= ADMN_URL ?>/prizes/add" class="pr-add-btn">
        <i class="fa-solid fa-plus"></i> Add Prize
    </a>
</div>

<!-- Toolbar -->
<div class="al-toolbar-card">
    <div style="font-size:.82rem;color:rgba(255,255,255,.35);font-weight:700;">
        <span id="prVisibleCount"><?= count($data) ?></span> prizes
    </div>
    <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="search" id="prSearch" placeholder="Search prizes…">
    </div>
</div>

<!-- Table -->
<div class="al-table-wrap">
    <table class="al-table">
        <colgroup>
            <col style="width:52px;">
            <col style="width:64px;">
            <col style="width:22%;">
            <col>
            <col style="width:112px;">
            <col style="width:172px;">
        </colgroup>
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th style="width:56px;"></th>
                <th>Name</th>
                <th>Description</th>
                <th class="text-center" style="width:110px;">Points</th>
                <th class="text-end" style="width:160px;">Actions</th>
            </tr>
        </thead>
        <tbody id="prTbody">
            <?php if (empty($data)): ?>
                <tr><td colspan="6">
                    <div class="al-empty">
                        <i class="fa-duotone fa-gift"></i>
                        <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:8px;">No prizes yet</div>
                        <a href="<?= ADMN_URL ?>/prizes/add" class="pr-add-btn" style="margin:0 auto;">
                            <i class="fa-solid fa-plus"></i> Add first prize
                        </a>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($data as $index => $row): ?>
                <tr class="pr-row" data-search="<?= $h(strtolower(($row['name'] ?? '') . ' ' . ($row['description'] ?? ''))) ?>">
                    <td><span class="pr-id">#<?= $index + 1 ?></span></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= $h($row['image']) ?>" class="pr-img" alt="<?= $h($row['name']) ?>">
                        <?php else: ?>
                            <div class="pr-img-placeholder"><i class="fa-duotone fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="pr-name"><?= $h($row['name']) ?></span></td>
                    <td><span class="pr-desc" title="<?= $h($row['description']) ?>"><?= $h($shorten($row['description'] ?? '')) ?></span></td>
                    <td class="text-center">
                        <span class="pr-points"><i class="fa-duotone fa-coins" style="font-size:.65rem;"></i> <?= (int)$row['points'] ?></span>
                    </td>
                    <td class="text-end">
                        <div class="pr-actions">
                            <a href="<?= ADMN_URL ?>/prizes/<?= (int)$row['id'] ?>" class="pr-btn pr-btn--edit">
                                <i class="fa-duotone fa-pencil"></i> Edit
                            </a>
                            <a href="<?= ADMN_URL ?>/prizes/<?= (int)$row['id'] ?>/delete" class="pr-btn pr-btn--delete">
                                <i class="fa-duotone fa-trash"></i> Delete
                            </a>
                        </div>
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
        Showing <strong id="prFooterCount"><?= count($data) ?></strong> of <?= count($data) ?> prizes
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function() {
    var searchInput  = document.getElementById('prSearch');
    var rows         = document.querySelectorAll('#prTbody .pr-row');
    var visibleCount = document.getElementById('prVisibleCount');
    var footerCount  = document.getElementById('prFooterCount');

    function applySearch() {
        var q = (searchInput.value || '').toLowerCase().trim();
        var shown = 0;
        rows.forEach(function(row) {
            var match = !q || (row.getAttribute('data-search') || '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        if (visibleCount) visibleCount.textContent = shown;
        if (footerCount)  footerCount.textContent  = shown;
    }

    if (searchInput) searchInput.addEventListener('input', applySearch);
})();
</script>
<?= $this->end() ?>
