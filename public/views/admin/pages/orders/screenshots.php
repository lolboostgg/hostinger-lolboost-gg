<?php
$rows = $data ?? [];
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$currentPage = max(1, (int)($page ?? ($_GET['page'] ?? 1)));
$currentLimit = max(25, min(100, (int)($limit ?? ($_GET['limit'] ?? 50))));
$currentSearch = trim((string)($search ?? ($_GET['search'] ?? '')));
$currentSort = strtolower((string)($sort ?? ($_GET['sort'] ?? 'id')));
$currentDir = strtolower((string)($dir ?? ($_GET['dir'] ?? 'desc'))) === 'asc' ? 'asc' : 'desc';
$totalRows = (int)($totalRows ?? count($rows));
$totalPages = max(1, (int)($totalPages ?? 1));
$fromRow = $totalRows > 0 ? (($currentPage - 1) * $currentLimit) + 1 : 0;
$toRow = min($totalRows, $fromRow + count($rows) - 1);
$baseUrl = ADMN_URL . '/order-screenshots';
$buildUrl = function ($pageNumber) use ($baseUrl, $currentLimit, $currentSearch, $currentSort, $currentDir) {
    $query = ['page' => max(1, (int)$pageNumber), 'limit' => $currentLimit, 'sort' => $currentSort, 'dir' => $currentDir];
    if ($currentSearch !== '') $query['search'] = $currentSearch;
    return $baseUrl . '?' . http_build_query($query);
};
$sortUrl = function ($column) use ($baseUrl, $currentLimit, $currentSearch, $currentSort, $currentDir) {
    $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    $query = ['page' => 1, 'limit' => $currentLimit, 'sort' => $column, 'dir' => $nextDir];
    if ($currentSearch !== '') $query['search'] = $currentSearch;
    return $baseUrl . '?' . http_build_query($query);
};
$sortIcon = function ($column) use ($currentSort, $currentDir) {
    if ($currentSort !== $column) return '<i class="fa-solid fa-sort"></i>';
    return $currentDir === 'asc' ? '<i class="fa-solid fa-sort-up"></i>' : '<i class="fa-solid fa-sort-down"></i>';
};
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Order Screenshots - Admin Area | LoLBoost.gg', 'h1' => 'Order Screenshots', 'description' => 'View uploaded order screenshots.']]) ?>
<?= $this->start('styles') ?>
<style>
.os-hero,.os-toolbar,.os-footer,.os-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);box-shadow:0 2px 20px rgba(0,0,0,.2)}
.os-hero{border-radius:20px;padding:20px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px}.os-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(56,189,248,.22),rgba(56,189,248,.07));border:1px solid rgba(56,189,248,.25);display:flex;align-items:center;justify-content:center;color:#7dd3fc;font-size:1.1rem}.os-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0}.os-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0}.os-toolbar{border-radius:16px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:16px}.os-search{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.os-search-wrap{position:relative}.os-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:8px 12px 8px 34px;outline:none;width:310px}.os-search-wrap input:focus{border-color:rgba(56,189,248,.45);box-shadow:0 0 0 3px rgba(56,189,248,.1)}.os-search-wrap input::placeholder{color:rgba(255,255,255,.25)}.os-search-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.32);font-size:.78rem}.os-btn{height:36px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.72);font-size:.78rem;font-weight:900;padding:0 14px;display:inline-flex;align-items:center;gap:7px;text-decoration:none}.os-btn:hover{background:rgba(56,189,248,.13);border-color:rgba(56,189,248,.35);color:#7dd3fc}.os-btn-primary{background:rgba(56,189,248,.18);border-color:rgba(56,189,248,.45);color:#7dd3fc}.os-table-wrap{border-radius:20px;overflow:hidden;margin-bottom:14px}.os-table{width:100%;border-collapse:collapse}.os-table thead th{padding:12px 16px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;background:rgba(0,0,0,.12);white-space:nowrap}.os-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s}.os-table tbody tr:hover{background:rgba(255,255,255,.025)}.os-table td{padding:13px 16px;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle}.os-id{font-size:.75rem;font-weight:900;color:rgba(255,255,255,.32)}.os-link{font-weight:800;color:rgba(255,255,255,.84);text-decoration:none}.os-link:hover{color:#7dd3fc}.os-thumb{width:96px;height:60px;object-fit:cover;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);display:block}.os-file{max-width:440px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle;color:rgba(255,255,255,.58);text-decoration:none}.os-file:hover{color:#7dd3fc}.os-badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:999px;background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.2);color:#7dd3fc;font-size:.72rem;font-weight:900}.os-muted{color:rgba(255,255,255,.32)}.os-date{font-size:.8rem;color:rgba(255,255,255,.42);white-space:nowrap}.os-footer{border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}.os-per-page{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.72)!important;padding:7px 10px!important;font-size:.78rem!important;font-weight:800;outline:none}.os-per-page option{background:#25282a;color:#fff}.os-pagination{display:flex;align-items:center;gap:5px;flex-wrap:wrap}.os-page{min-width:34px;height:34px;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;text-decoration:none}.os-page:hover{background:rgba(56,189,248,.13);border-color:rgba(56,189,248,.35);color:#7dd3fc}.os-page.active{background:rgba(56,189,248,.18);border-color:rgba(56,189,248,.45);color:#7dd3fc}.os-page.disabled{opacity:.35;pointer-events:none}.os-empty{padding:48px 20px;text-align:center;color:rgba(255,255,255,.28)}@media(max-width:1200px){.os-table-wrap{overflow-x:auto}.os-table{min-width:920px}}

.os-sort{color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:6px}.os-sort:hover{color:rgb(56,189,248)}.os-sort i{font-size:.66rem;opacity:.55}
.lb-status{display:inline-flex;align-items:center;gap:.45rem;padding:.34rem .70rem;border-radius:999px;font-weight:950;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:rgba(255,255,255,.85);white-space:nowrap}.lb-status__dot{width:7px;height:7px;border-radius:50%;background:currentColor;opacity:.95;flex:0 0 auto}.lb-status.status-inprogress{color:#4ea1ff;border-color:rgba(78,161,255,.25);background:rgba(78,161,255,.12)}.lb-status.status-completed{color:#1fe6c6;border-color:rgba(31,230,198,.22);background:rgba(31,230,198,.10)}.lb-status.status-paused{color:#ffc44d;border-color:rgba(255,196,77,.22);background:rgba(255,196,77,.10)}.lb-status.status-unpaid{color:#ff6b6b;border-color:rgba(255,107,107,.20);background:rgba(255,107,107,.10)}.lb-status.status-paid,.lb-status.status-processing{color:#b18cff;border-color:rgba(177,140,255,.22);background:rgba(177,140,255,.10)}.lb-status.status-refund{color:#ff8a4c;border-color:rgba(255,138,76,.22);background:rgba(255,138,76,.10)}.lb-status.status-waitingapproval{color:#a78bfa;border-color:rgba(167,139,250,.24);background:rgba(167,139,250,.10)}
</style>
<?= $this->end() ?>
<div class="os-hero"><div class="os-hero-icon"><i class="fa-duotone fa-images"></i></div><div><h2 class="os-hero-title">Order Screenshots</h2><p class="os-hero-sub"><?= $currentSearch !== '' ? 'Search results' : 'Newest uploaded screenshots' ?>, showing <?= $h($fromRow) ?> to <?= $h($toRow) ?> of <?= $h($totalRows) ?></p></div></div>
<div class="os-toolbar"><div style="font-size:.82rem;color:rgba(255,255,255,.42);"><strong style="color:rgba(255,255,255,.82);"><?= $h($totalRows) ?></strong> matching screenshots<?= $currentSearch !== '' ? ' for “' . $h($currentSearch) . '”' : '' ?></div><form class="os-search" method="get" action="<?= $h($baseUrl) ?>"><input type="hidden" name="limit" value="<?= $h($currentLimit) ?>"><div class="os-search-wrap"><i class="fa-solid fa-magnifying-glass os-search-icon"></i><input class="js-auto-search" name="search" type="search" placeholder="Search ID, order, booster or file…" value="<?= $h($currentSearch) ?>" autocomplete="off" data-base-url="<?= $h($baseUrl) ?>" data-limit="<?= $h($currentLimit) ?>" data-sort="<?= $h($currentSort) ?>" data-dir="<?= $h($currentDir) ?>"></div><?php if ($currentSearch !== ''): ?><a class="os-btn" href="<?= $h($baseUrl) ?>?limit=<?= $h($currentLimit) ?>">Reset</a><?php endif; ?></form></div>
<div class="os-table-wrap"><table class="os-table"><thead><tr><th><a class="os-sort" href="<?= $h($sortUrl('id')) ?>">ID <?= $sortIcon('id') ?></a></th><th><a class="os-sort" href="<?= $h($sortUrl('preview')) ?>">Preview <?= $sortIcon('preview') ?></a></th><th><a class="os-sort" href="<?= $h($sortUrl('order')) ?>">Order <?= $sortIcon('order') ?></a></th><th><a class="os-sort" href="<?= $h($sortUrl('booster')) ?>">Booster <?= $sortIcon('booster') ?></a></th><th><a class="os-sort" href="<?= $h($sortUrl('file')) ?>">File <?= $sortIcon('file') ?></a></th><th><a class="os-sort" href="<?= $h($sortUrl('version')) ?>">Version <?= $sortIcon('version') ?></a></th><th class="text-end"><a class="os-sort" href="<?= $h($sortUrl('created')) ?>">Created <?= $sortIcon('created') ?></a></th></tr></thead><tbody><?php if (empty($rows)): ?><tr><td colspan="7"><div class="os-empty"><i class="fa-duotone fa-images" style="font-size:2rem;display:block;margin-bottom:10px;"></i>No screenshots found</div></td></tr><?php endif; ?><?php foreach ($rows as $row): ?><?php $url = trim((string)($row['file_url'] ?? '')); ?><tr><td><span class="os-id">#<?= $h($row['id'] ?? '') ?></span></td><td><?php if ($url !== ''): ?><a href="<?= $h($url) ?>" target="_blank" rel="noopener"><img class="os-thumb" loading="lazy" src="<?= $h($url) ?>" alt="Screenshot #<?= $h($row['id'] ?? '') ?>"></a><?php else: ?><span class="os-muted">No file</span><?php endif; ?></td><td><a class="os-link" href="<?= ADMN_URL ?>/order/<?= $h($row['order_id'] ?? '') ?>">#<?= $h($row['order_id'] ?? '') ?></a></td><td><?= !empty($row['booster_id']) ? '<a class="os-link" href="' . ADMN_URL . '/booster/' . $h($row['booster_id']) . '">' . $h($row['booster_username'] ?? ('Booster #' . $row['booster_id'])) . '</a>' : '<span class="os-muted">empty</span>' ?></td><td><?php if ($url !== ''): ?><a class="os-file" href="<?= $h($url) ?>" target="_blank" rel="noopener"><?= $h($url) ?></a><?php else: ?><span class="os-muted">empty</span><?php endif; ?></td><td><span class="os-badge"><?= $h($row['file_version'] ?? 'default') ?></span></td><td class="text-end"><span class="os-date"><?= !empty($row['created_at']) ? $h(util_format_date_display($row['created_at'])) : 'empty' ?></span></td></tr><?php endforeach; ?></tbody></table></div>
<div class="os-footer"><form method="get" action="<?= $h($baseUrl) ?>" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><?php if ($currentSearch !== ''): ?><input type="hidden" name="search" value="<?= $h($currentSearch) ?>"><?php endif; ?><input type="hidden" name="page" value="1"><input type="hidden" name="sort" value="<?= $h($currentSort) ?>"><input type="hidden" name="dir" value="<?= $h($currentDir) ?>"><span style="font-size:.82rem;color:rgba(255,255,255,.42);">Rows per page</span><select name="limit" class="os-per-page" onchange="this.form.submit()"><?php foreach ([25,50,100] as $limitOption): ?><option value="<?= $h($limitOption) ?>" <?= $currentLimit === $limitOption ? 'selected' : '' ?>><?= $h($limitOption) ?> / page</option><?php endforeach; ?></select></form><div class="os-pagination"><a class="os-page <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="<?= $h($buildUrl($currentPage - 1)) ?>"><i class="fa-solid fa-chevron-left"></i></a><?php $startPage=max(1,$currentPage-2);$endPage=min($totalPages,$currentPage+2);if($startPage>1){echo '<a class="os-page" href="'.$h($buildUrl(1)).'">1</a>';if($startPage>2)echo '<span class="os-page disabled">…</span>';}for($i=$startPage;$i<=$endPage;$i++){echo '<a class="os-page '.($i===$currentPage?'active':'').'" href="'.$h($buildUrl($i)).'">'.$h($i).'</a>';}if($endPage<$totalPages){if($endPage<$totalPages-1)echo '<span class="os-page disabled">…</span>';echo '<a class="os-page" href="'.$h($buildUrl($totalPages)).'">'.$h($totalPages).'</a>';} ?><a class="os-page <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" href="<?= $h($buildUrl($currentPage + 1)) ?>"><i class="fa-solid fa-chevron-right"></i></a></div></div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var timer = null;
  function runSearch(input){
    var baseUrl = input.getAttribute('data-base-url') || window.location.pathname;
    var limit = input.getAttribute('data-limit') || '50';
    var query = (input.value || '').trim();
    var url = new URL(baseUrl, window.location.origin);
    url.searchParams.set('limit', limit);
    var sort = input.getAttribute('data-sort') || '';
    var dir = input.getAttribute('data-dir') || '';
    if (sort) url.searchParams.set('sort', sort);
    if (dir) url.searchParams.set('dir', dir);
    if (query !== '') url.searchParams.set('search', query);
    window.location.href = url.toString();
  }
  document.addEventListener('input', function(e){
    var input = e.target.closest('.js-auto-search');
    if (!input) return;
    clearTimeout(timer);
    timer = setTimeout(function(){ runSearch(input); }, 450);
  });
  document.addEventListener('keydown', function(e){
    var input = e.target.closest('.js-auto-search');
    if (!input || e.key !== 'Enter') return;
    e.preventDefault();
    clearTimeout(timer);
    runSearch(input);
  });
})();
</script>
<?= $this->end() ?>
