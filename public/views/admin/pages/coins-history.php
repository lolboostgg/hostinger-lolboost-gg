<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Coins History — Admin', 'h1' => 'Coins History', 'description' => 'View all LB Coins transactions.']]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$data = is_array($data ?? null) ? $data : [];
$stats = is_array($stats ?? null) ? $stats : [];
$total = (int)($stats['total_transactions'] ?? count($data));
$totalIn = (float)($stats['total_in'] ?? 0);
$totalOut = (float)($stats['total_out'] ?? 0);
if (empty($stats)) {
    foreach ($data as $r) {
        if (($r['type'] ?? '') === 'increment') $totalIn += (float)($r['amount'] ?? 0);
        else $totalOut += (float)($r['amount'] ?? 0);
    }
}
$loadedLimit = (int)($loaded_limit ?? count($data));
$loadedCount = count($data);
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats ── */
.ch-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;}
.ch-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.ch-stat:hover{transform:translateY(-2px);}
.ch-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.ch-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.ch-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(250,204,21,.2),rgba(250,204,21,.07));border:1px solid rgba(250,204,21,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#facc15;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.al-search-wrap{position:relative;display:flex;align-items:center;}
.al-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:7px 12px 7px 34px;outline:none;width:240px;transition:border-color .15s;}
.al-search-wrap input:focus{border-color:rgba(250,204,21,.4);box-shadow:0 0 0 3px rgba(250,204,21,.08);}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25);}
.al-search-icon{position:absolute;left:10px;color:rgba(255,255,255,.3);font-size:.78rem;pointer-events:none;}
/* ── Pills ── */
.al-pills{display:flex;gap:6px;flex-wrap:wrap;}
.al-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:all .12s;user-select:none;}
.al-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.al-pill.active{background:rgba(250,204,21,.14);border-color:rgba(250,204,21,.4);color:#facc15;}
.al-pill[data-filter="increment"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.35);color:#4ade80;}
.al-pill[data-filter="decrement"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.35);color:#fb7185;}
/* ── Table ── */
.al-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.2);margin-bottom:14px;}
.al-table{width:100%;border-collapse:collapse;}
.al-table thead tr{border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:12px 16px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;background:rgba(0,0,0,.12);}
.al-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.al-table tbody tr:last-child{border-bottom:none;}
.al-table tbody tr:hover{background:rgba(255,255,255,.025);}
.al-table td{padding:13px 16px;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle;}
/* ── Cells ── */
.ch-id{font-size:.75rem;font-weight:800;color:rgba(255,255,255,.28);}
.ch-client a{font-weight:700;color:rgba(255,255,255,.82);text-decoration:none;transition:color .12s;}
.ch-client a:hover{color:#facc15;}
.ch-reason{font-size:.82rem;color:rgba(255,255,255,.5);max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ch-amount-in{display:inline-flex;align-items:center;gap:4px;font-weight:900;font-size:.9rem;color:#4ade80;}
.ch-amount-out{display:inline-flex;align-items:center;gap:4px;font-weight:900;font-size:.9rem;color:#fb7185;}
.ch-date{font-size:.8rem;color:rgba(255,255,255,.4);white-space:nowrap;}
/* ── Footer ── */
.al-footer{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.ja-per-page{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.72)!important;padding:7px 10px!important;font-size:.78rem!important;font-weight:800;outline:none;}
.ja-per-page option{background:#25282a;color:#fff;}
.ja-page-btn{min-width:34px;height:34px;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s;font-family:inherit;}
.ja-page-btn:hover:not(:disabled){background:rgba(250,204,21,.13);border-color:rgba(250,204,21,.35);color:#facc15;transform:translateY(-1px);}
.ja-page-btn.active{background:rgba(250,204,21,.18);border-color:rgba(250,204,21,.45);color:#facc15;}
.ja-page-btn:disabled{opacity:.35;cursor:not-allowed;}
.ja-page-ellipsis{padding:0 5px;color:rgba(255,255,255,.28);font-weight:900;}
.ja-pagination{display:flex;align-items:center;gap:5px;flex-wrap:wrap;}
/* ── Empty ── */
.al-empty{padding:48px 20px;text-align:center;color:rgba(255,255,255,.25);}
.al-empty i{font-size:2.2rem;margin-bottom:10px;display:block;opacity:.35;}
/* ── Row highlight ── */
.ch-row-updating{animation:ch-flash .4s ease;}
@keyframes ch-flash{0%{background:rgba(250,204,21,.08)}100%{background:transparent}}
@media only screen and (max-width:1200px){.al-table-wrap{overflow-x:auto;}.al-table{min-width:750px;}}
</style>
<?= $this->end() ?>

<!-- Stats -->
<div class="ch-stats">
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.2);color:#facc15;"><i class="fa-duotone fa-coins"></i></div>
        <div><div class="ch-stat-label">Total Transactions</div><div class="ch-stat-value"><?= number_format($total) ?></div></div>
    </div>
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-circle-plus"></i></div>
        <div><div class="ch-stat-label">Total Earned</div><div class="ch-stat-value">+<?= number_format($totalIn, 0) ?></div></div>
    </div>
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.2);color:#fb7185;"><i class="fa-duotone fa-circle-minus"></i></div>
        <div><div class="ch-stat-label">Total Spent</div><div class="ch-stat-value">-<?= number_format($totalOut, 0) ?></div></div>
    </div>
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-duotone fa-scale-balanced"></i></div>
        <div><div class="ch-stat-label">Net Balance</div><div class="ch-stat-value" style="color:<?= ($totalIn - $totalOut) >= 0 ? '#4ade80' : '#fb7185' ?>"><?= ($totalIn - $totalOut) >= 0 ? '+' : '' ?><?= number_format($totalIn - $totalOut, 0) ?></div></div>
    </div>
</div>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-icon"><i class="fa-duotone fa-coins"></i></div>
    <div>
        <h2 class="al-hero-title">Coins History</h2>
        <p class="al-hero-sub"><?= number_format($total) ?> transaction<?= $total !== 1 ? 's' : '' ?> total, showing latest <?= number_format($loadedCount) ?></p>
    </div>
</div>

<!-- Toolbar -->
<div class="al-toolbar-card">
    <div class="al-pills" id="chTypeFilter">
        <span class="al-pill active" data-filter="all"><i class="fa-duotone fa-layer-group" style="font-size:.75rem;"></i> All</span>
        <span class="al-pill" data-filter="increment"><i class="fa-solid fa-circle-plus" style="font-size:.7rem;"></i> Earned</span>
        <span class="al-pill" data-filter="decrement"><i class="fa-solid fa-circle-minus" style="font-size:.7rem;"></i> Spent</span>
    </div>
    <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="search" id="chSearch" placeholder="Search client, reason…">
    </div>
</div>

<!-- Table -->
<div class="al-table-wrap">
    <table class="al-table">
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Client</th>
                <th>Reason</th>
                <th class="text-center" style="width:100px;">Amount</th>
                <th class="text-end" style="width:160px;">Created At</th>
            </tr>
        </thead>
        <tbody id="chTbody">
            <?php if (empty($data)): ?>
                <tr><td colspan="5">
                    <div class="al-empty">
                        <i class="fa-duotone fa-coins"></i>
                        <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);">No transactions yet</div>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($data as $row):
                    $isIn = ($row['type'] ?? '') === 'increment';
                ?>
                <tr class="ch-row"
                    data-type="<?= $h($row['type'] ?? '') ?>"
                    data-search="<?= $h(strtolower(($row['client_name'] ?? '') . ' ' . ($row['reason'] ?? ''))) ?>"
                    data-date="<?= $h($row['created_at'] ?? '') ?>">
                    <td><span class="ch-id">#<?= (int)$row['id'] ?></span></td>
                    <td class="ch-client">
                        <a href="<?= ADMN_URL ?>/client/<?= (int)$row['client_id'] ?>">
                            <?= $h($row['client_name'] ?? '—') ?>
                        </a>
                    </td>
                    <td><span class="ch-reason" title="<?= $h($row['reason'] ?? '') ?>"><?= $h($row['reason'] ?? '—') ?></span></td>
                    <td class="text-center">
                        <?php if ($isIn): ?>
                            <span class="ch-amount-in"><i class="fa-solid fa-plus" style="font-size:.6rem;"></i><?= $h($row['amount']) ?></span>
                        <?php else: ?>
                            <span class="ch-amount-out"><i class="fa-solid fa-minus" style="font-size:.6rem;"></i><?= $h($row['amount']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <span class="ch-date"><?= $h(util_format_date_display($row['created_at'])) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<div class="al-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
        Showing <strong id="chVisibleCount">0</strong> of <strong><?= number_format($loadedCount) ?></strong> loaded entries
        <span style="margin-left:10px;font-size:.75rem;color:rgba(255,255,255,.2);" id="chPageInfo"></span>
        <?php if ($total > $loadedCount): ?>
            <span style="margin-left:10px;font-size:.75rem;color:rgba(255,255,255,.28);">Latest <?= number_format($loadedCount) ?> of <?= number_format($total) ?></span>
        <?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <select id="chPerPage" class="ja-per-page">
            <option value="10">10 / page</option>
            <option value="25" selected>25 / page</option>
            <option value="50">50 / page</option>
            <option value="100">100 / page</option>
        </select>
        <div class="ja-pagination" id="chPagination"></div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    var allRows    = Array.from(document.querySelectorAll('#chTbody .ch-row'));
    var activeType = 'all';
    var searchQ    = '';
    var currentPage= 1;
    var perPage    = 25;

    // Sort by date desc (newest first)
    var tbody = document.getElementById('chTbody');
    allRows.sort(function(a, b) {
        return (b.getAttribute('data-date') || '').localeCompare(a.getAttribute('data-date') || '');
    });
    allRows.forEach(function(r) { tbody.appendChild(r); });

    function getFiltered() {
        return allRows.filter(function(r) {
            var typeOk   = activeType === 'all' || r.getAttribute('data-type') === activeType;
            var searchOk = !searchQ   || (r.getAttribute('data-search') || '').includes(searchQ);
            return typeOk && searchOk;
        });
    }

    function render() {
        var filtered = getFiltered();
        var total    = filtered.length;
        var pages    = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > pages) currentPage = pages;
        var start    = (currentPage - 1) * perPage;
        var end      = start + perPage;

        allRows.forEach(function(r) { r.style.display = 'none'; });
        filtered.slice(start, end).forEach(function(r) { r.style.display = ''; });

        var vc = document.getElementById('chVisibleCount');
        if (vc) vc.textContent = Math.min(end, total) - start > 0 ? Math.min(end, total) - start : 0;
        var pi = document.getElementById('chPageInfo');
        if (pi) pi.textContent = pages > 1 ? 'Page ' + currentPage + ' of ' + pages : '';

        buildPagination(pages);
    }

    function buildPagination(pages) {
        var wrap = document.getElementById('chPagination');
        if (!wrap) return;
        wrap.innerHTML = '';
        if (pages <= 1) return;

        function btn(label, page, disabled, active) {
            var b = document.createElement('button');
            b.className = 'ja-page-btn' + (active ? ' active' : '');
            b.innerHTML = label;
            b.disabled  = !!disabled;
            b.addEventListener('click', function() {
                if (disabled || currentPage === page) return;
                currentPage = page;
                render();
            });
            wrap.appendChild(b);
        }
        function ellipsis() {
            var s = document.createElement('span');
            s.className   = 'ja-page-ellipsis';
            s.textContent = '…';
            wrap.appendChild(s);
        }

        btn('<i class="fa-solid fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false);
        for (var i = 1; i <= pages; i++) {
            if (i === 1 || i === pages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                btn(i, i, false, i === currentPage);
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                ellipsis();
            }
        }
        btn('<i class="fa-solid fa-chevron-right"></i>', currentPage + 1, currentPage === pages, false);
    }

    // Type filter pills
    document.querySelectorAll('#chTypeFilter .al-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            document.querySelectorAll('#chTypeFilter .al-pill').forEach(function(p) { p.classList.remove('active'); });
            pill.classList.add('active');
            activeType  = pill.getAttribute('data-filter');
            currentPage = 1;
            render();
        });
    });

    // Search
    var searchEl = document.getElementById('chSearch');
    if (searchEl) searchEl.addEventListener('input', function() {
        searchQ     = (searchEl.value || '').toLowerCase().trim();
        currentPage = 1;
        render();
    });

    // Per page
    var perPageEl = document.getElementById('chPerPage');
    if (perPageEl) perPageEl.addEventListener('change', function() {
        perPage     = parseInt(perPageEl.value, 10) || 25;
        currentPage = 1;
        render();
    });

    render();
})();
</script>
<?= $this->end() ?>
