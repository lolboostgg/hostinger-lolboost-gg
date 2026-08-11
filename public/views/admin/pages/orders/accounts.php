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
$baseUrl = ADMN_URL . '/order-accounts';
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

$statusPill = function ($raw) use ($h) {
    $value = trim(strip_tags((string)($raw ?? '')));
    $key = strtoupper(str_replace(['_', '-'], ' ', $value));
    $key = preg_replace('/\s+/', ' ', $key);
    $map = [
        'IN PROGRESS' => ['In Progress', 'status-inprogress'],
        'WAITING FOR APPROVAL' => ['Waiting for Approval', 'status-waitingapproval'],
        'AWAITING APPROVAL' => ['Waiting for Approval', 'status-waitingapproval'],
        'PENDING APPROVAL' => ['Waiting for Approval', 'status-waitingapproval'],
        'PROCESSING' => ['Processing', 'status-processing'],
        'PAID' => ['Processing', 'status-processing'],
        'UNPAID' => ['Unpaid', 'status-unpaid'],
        'REFUND' => ['Refunded', 'status-refund'],
        'REFUNDED' => ['Refunded', 'status-refund'],
        'PAUSED' => ['Paused', 'status-paused'],
        'COMPLETED' => ['Completed', 'status-completed'],
    ];
    $item = $map[$key] ?? [ucwords(strtolower($key ?: 'Unknown')), 'status-processing'];
    return '<span class="lb-status ' . $h($item[1]) . '"><span class="lb-status__dot" aria-hidden="true"></span><span>' . $h($item[0]) . '</span></span>';
};

?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Order Accounts - Admin Area | LoLBoost.gg', 'h1' => 'Order Accounts', 'description' => 'View order account login details.']]) ?>
<?= $this->start('styles') ?>
<style>
.oa-hero,.oa-toolbar,.oa-footer,.oa-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);box-shadow:0 2px 20px rgba(0,0,0,.2)}
.oa-hero{border-radius:20px;padding:20px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px}.oa-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.22),rgba(109,92,255,.07));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.1rem}.oa-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0}.oa-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0}.oa-toolbar{border-radius:16px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:16px}.oa-search{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.oa-search-wrap{position:relative}.oa-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:8px 12px 8px 34px;outline:none;width:280px}.oa-search-wrap input:focus{border-color:rgba(109,92,255,.45);box-shadow:0 0 0 3px rgba(109,92,255,.1)}.oa-search-wrap input::placeholder{color:rgba(255,255,255,.25)}.oa-search-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.32);font-size:.78rem}.oa-btn{height:36px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.72);font-size:.78rem;font-weight:900;padding:0 14px;display:inline-flex;align-items:center;gap:7px;text-decoration:none}.oa-btn:hover{background:rgba(109,92,255,.14);border-color:rgba(109,92,255,.36);color:#c4b5fd}.oa-btn-primary{background:rgba(109,92,255,.2);border-color:rgba(109,92,255,.45);color:#c4b5fd}.oa-table-wrap{border-radius:20px;overflow:hidden;margin-bottom:14px}.oa-table{width:100%;border-collapse:collapse}.oa-table thead th{padding:12px 16px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;background:rgba(0,0,0,.12);white-space:nowrap}.oa-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s}.oa-table tbody tr:hover{background:rgba(255,255,255,.025)}.oa-table td{padding:13px 16px;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle}.oa-id{font-size:.75rem;font-weight:900;color:rgba(255,255,255,.32)}.oa-link{font-weight:800;color:rgba(255,255,255,.84);text-decoration:none}.oa-link:hover{color:#c4b5fd}.oa-copy{display:inline-flex;align-items:center;gap:8px;max-width:260px}.oa-copy code{font-size:.82rem;color:#ff3fb4;background:rgba(255,63,180,.08);border:1px solid rgba(255,63,180,.16);border-radius:8px;padding:4px 8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px}.oa-copy button{width:30px;height:30px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7)}.oa-muted{color:rgba(255,255,255,.32)}.oa-date{font-size:.8rem;color:rgba(255,255,255,.42);white-space:nowrap}.oa-footer{border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}.oa-per-page{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.72)!important;padding:7px 10px!important;font-size:.78rem!important;font-weight:800;outline:none}.oa-per-page option{background:#25282a;color:#fff}.oa-pagination{display:flex;align-items:center;gap:5px;flex-wrap:wrap}.oa-page{min-width:34px;height:34px;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;text-decoration:none}.oa-page:hover{background:rgba(109,92,255,.13);border-color:rgba(109,92,255,.35);color:#c4b5fd}.oa-page.active{background:rgba(109,92,255,.2);border-color:rgba(109,92,255,.45);color:#c4b5fd}.oa-page.disabled{opacity:.35;pointer-events:none}.oa-empty{padding:48px 20px;text-align:center;color:rgba(255,255,255,.28)}@media(max-width:1200px){.oa-table-wrap{overflow-x:auto}.oa-table{min-width:980px}}

.oa-sort{color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:6px}.oa-sort:hover{color:rgb(109,92,255)}.oa-sort i{font-size:.66rem;opacity:.55}
.lb-status{display:inline-flex;align-items:center;gap:.45rem;padding:.34rem .70rem;border-radius:999px;font-weight:950;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:rgba(255,255,255,.85);white-space:nowrap}.lb-status__dot{width:7px;height:7px;border-radius:50%;background:currentColor;opacity:.95;flex:0 0 auto}.lb-status.status-inprogress{color:#4ea1ff;border-color:rgba(78,161,255,.25);background:rgba(78,161,255,.12)}.lb-status.status-completed{color:#1fe6c6;border-color:rgba(31,230,198,.22);background:rgba(31,230,198,.10)}.lb-status.status-paused{color:#ffc44d;border-color:rgba(255,196,77,.22);background:rgba(255,196,77,.10)}.lb-status.status-unpaid{color:#ff6b6b;border-color:rgba(255,107,107,.20);background:rgba(255,107,107,.10)}.lb-status.status-paid,.lb-status.status-processing{color:#b18cff;border-color:rgba(177,140,255,.22);background:rgba(177,140,255,.10)}.lb-status.status-refund{color:#ff8a4c;border-color:rgba(255,138,76,.22);background:rgba(255,138,76,.10)}.lb-status.status-waitingapproval{color:#a78bfa;border-color:rgba(167,139,250,.24);background:rgba(167,139,250,.10)}
</style>
<?= $this->end() ?>
<div class="oa-hero"><div class="oa-hero-icon"><i class="fa-duotone fa-key"></i></div><div><h2 class="oa-hero-title">Order Accounts</h2><p class="oa-hero-sub"><?= $currentSearch !== '' ? 'Search results' : 'Newest account credentials' ?>, showing <?= $h($fromRow) ?> to <?= $h($toRow) ?> of <?= $h($totalRows) ?></p></div></div>
<div class="oa-toolbar"><div style="font-size:.82rem;color:rgba(255,255,255,.42);"><strong style="color:rgba(255,255,255,.82);"><?= $h($totalRows) ?></strong> matching entries<?= $currentSearch !== '' ? ' for “' . $h($currentSearch) . '”' : '' ?></div><form class="oa-search" method="get" action="<?= $h($baseUrl) ?>"><input type="hidden" name="limit" value="<?= $h($currentLimit) ?>"><div class="oa-search-wrap"><i class="fa-solid fa-magnifying-glass oa-search-icon"></i><input class="js-auto-search" name="search" type="search" placeholder="Search ID, order, client, IGN…" value="<?= $h($currentSearch) ?>" autocomplete="off" data-base-url="<?= $h($baseUrl) ?>" data-limit="<?= $h($currentLimit) ?>" data-sort="<?= $h($currentSort) ?>" data-dir="<?= $h($currentDir) ?>"></div><?php if ($currentSearch !== ''): ?><a class="oa-btn" href="<?= $h($baseUrl) ?>?limit=<?= $h($currentLimit) ?>">Reset</a><?php endif; ?></form></div>
<div class="oa-table-wrap"><table class="oa-table"><thead><tr><th><a class="oa-sort" href="<?= $h($sortUrl('id')) ?>">ID <?= $sortIcon('id') ?></a></th><th><a class="oa-sort" href="<?= $h($sortUrl('order')) ?>">Order <?= $sortIcon('order') ?></a></th><th><a class="oa-sort" href="<?= $h($sortUrl('client')) ?>">Client <?= $sortIcon('client') ?></a></th><th><a class="oa-sort" href="<?= $h($sortUrl('ign')) ?>">IGN <?= $sortIcon('ign') ?></a></th><th><a class="oa-sort" href="<?= $h($sortUrl('login')) ?>">Login <?= $sortIcon('login') ?></a></th><th><a class="oa-sort" href="<?= $h($sortUrl('password')) ?>">Password <?= $sortIcon('password') ?></a></th><th><a class="oa-sort" href="<?= $h($sortUrl('status')) ?>">Status <?= $sortIcon('status') ?></a></th><th class="text-end"><a class="oa-sort" href="<?= $h($sortUrl('created')) ?>">Created <?= $sortIcon('created') ?></a></th></tr></thead><tbody><?php if (empty($rows)): ?><tr><td colspan="8"><div class="oa-empty"><i class="fa-duotone fa-key" style="font-size:2rem;display:block;margin-bottom:10px;"></i>No account entries found</div></td></tr><?php endif; ?><?php foreach ($rows as $row): ?><tr><td><span class="oa-id">#<?= $h($row['id'] ?? '') ?></span></td><td><a class="oa-link" href="<?= ADMN_URL ?>/order/<?= $h($row['order_id'] ?? '') ?>">#<?= $h($row['order_id'] ?? '') ?></a></td><td><?= !empty($row['client_id']) ? '<a class="oa-link" href="' . ADMN_URL . '/client/' . $h($row['client_id']) . '">' . $h($row['client_username'] ?? ('Client #' . $row['client_id'])) . '</a>' : '<span class="oa-muted">empty</span>' ?></td><td><?= $h($row['ign'] ?? '') ?: '<span class="oa-muted">empty</span>' ?></td><td><?php if (!empty($row['login'])): ?><span class="oa-copy"><code><?= $h($row['login']) ?></code><button type="button" class="js-copy" data-copy="<?= $h($row['login']) ?>"><i class="fa-regular fa-copy"></i></button></span><?php else: ?><span class="oa-muted">empty</span><?php endif; ?></td><td><?php if (!empty($row['password'])): ?><span class="oa-copy"><code><?= $h($row['password']) ?></code><button type="button" class="js-copy" data-copy="<?= $h($row['password']) ?>"><i class="fa-regular fa-copy"></i></button></span><?php else: ?><span class="oa-muted">empty</span><?php endif; ?></td><td><?= !empty($row['status']) ? $statusPill($row['status']) : '<span class="oa-muted">empty</span>' ?></td><td class="text-end"><span class="oa-date"><?= !empty($row['created_at']) ? $h(util_format_date_display($row['created_at'])) : 'empty' ?></span></td></tr><?php endforeach; ?></tbody></table></div>
<div class="oa-footer"><form method="get" action="<?= $h($baseUrl) ?>" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><?php if ($currentSearch !== ''): ?><input type="hidden" name="search" value="<?= $h($currentSearch) ?>"><?php endif; ?><input type="hidden" name="page" value="1"><input type="hidden" name="sort" value="<?= $h($currentSort) ?>"><input type="hidden" name="dir" value="<?= $h($currentDir) ?>"><span style="font-size:.82rem;color:rgba(255,255,255,.42);">Rows per page</span><select name="limit" class="oa-per-page" onchange="this.form.submit()"><?php foreach ([25,50,100] as $limitOption): ?><option value="<?= $h($limitOption) ?>" <?= $currentLimit === $limitOption ? 'selected' : '' ?>><?= $h($limitOption) ?> / page</option><?php endforeach; ?></select></form><div class="oa-pagination"><a class="oa-page <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="<?= $h($buildUrl($currentPage - 1)) ?>"><i class="fa-solid fa-chevron-left"></i></a><?php $startPage=max(1,$currentPage-2);$endPage=min($totalPages,$currentPage+2);if($startPage>1){echo '<a class="oa-page" href="'.$h($buildUrl(1)).'">1</a>';if($startPage>2)echo '<span class="oa-page disabled">…</span>';}for($i=$startPage;$i<=$endPage;$i++){echo '<a class="oa-page '.($i===$currentPage?'active':'').'" href="'.$h($buildUrl($i)).'">'.$h($i).'</a>';}if($endPage<$totalPages){if($endPage<$totalPages-1)echo '<span class="oa-page disabled">…</span>';echo '<a class="oa-page" href="'.$h($buildUrl($totalPages)).'">'.$h($totalPages).'</a>';} ?><a class="oa-page <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" href="<?= $h($buildUrl($currentPage + 1)) ?>"><i class="fa-solid fa-chevron-right"></i></a></div></div>
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
  document.addEventListener('click',function(e){
    const btn=e.target.closest('.js-copy');
    if(!btn)return;
    navigator.clipboard.writeText(btn.getAttribute('data-copy')||'');
    btn.innerHTML='<i class="fa-regular fa-check"></i>';
    setTimeout(()=>btn.innerHTML='<i class="fa-regular fa-copy"></i>',900);
  });
})();
</script>
<?= $this->end() ?>
