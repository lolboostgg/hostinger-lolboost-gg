<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Redeem Requests — Admin', 'h1' => 'Redeem Requests', 'description' => 'View all prize redeem requests.']]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$total = count($data);

// Count unique clients and prizes
$uniqueClients = count(array_unique(array_column($data, 'client_id')));
$uniquePrizes  = count(array_unique(array_column($data, 'prize_id')));
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats ── */
.rd-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;}
.rd-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.rd-stat:hover{transform:translateY(-2px);}
.rd-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.rd-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.rd-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(74,222,128,.18),rgba(74,222,128,.06));border:1px solid rgba(74,222,128,.22);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#4ade80;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.al-search-wrap{position:relative;display:flex;align-items:center;}
.al-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:7px 12px 7px 34px;outline:none;width:240px;transition:border-color .15s;}
.al-search-wrap input:focus{border-color:rgba(74,222,128,.4);box-shadow:0 0 0 3px rgba(74,222,128,.08);}
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
/* ── Cells ── */
.rd-id{font-size:.75rem;font-weight:800;color:rgba(255,255,255,.25);}
.rd-client a{font-weight:700;color:rgba(255,255,255,.82);text-decoration:none;transition:color .12s;}
.rd-client a:hover{color:#4ade80;}
.rd-prize a{font-size:.85rem;color:rgba(255,255,255,.6);text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:color .12s;}
.rd-prize a:hover{color:#fbbf24;}
.rd-prize-icon{width:28px;height:28px;border-radius:7px;object-fit:cover;border:1px solid rgba(255,255,255,.08);}
.rd-prize-placeholder{width:28px;height:28px;border-radius:7px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);display:inline-flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:.65rem;}
.rd-badge{display:inline-flex;align-items:center;gap:4px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80;padding:3px 10px;border-radius:99px;font-size:.72rem;font-weight:800;}
.rd-date{font-size:.8rem;color:rgba(255,255,255,.4);white-space:nowrap;}
/* ── Footer ── */
.al-footer{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.ja-per-page{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.72)!important;padding:7px 10px!important;font-size:.78rem!important;font-weight:800;outline:none;}
.ja-per-page option{background:#25282a;color:#fff;}
.ja-page-btn{min-width:34px;height:34px;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s;font-family:inherit;}
.ja-page-btn:hover:not(:disabled){background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.3);color:#4ade80;transform:translateY(-1px);}
.ja-page-btn.active{background:rgba(74,222,128,.15);border-color:rgba(74,222,128,.38);color:#4ade80;}
.ja-page-btn:disabled{opacity:.35;cursor:not-allowed;}
.ja-page-ellipsis{padding:0 5px;color:rgba(255,255,255,.28);font-weight:900;}
.ja-pagination{display:flex;align-items:center;gap:5px;flex-wrap:wrap;}
/* ── Empty ── */
.al-empty{padding:48px 20px;text-align:center;color:rgba(255,255,255,.25);}
.al-empty i{font-size:2.2rem;margin-bottom:10px;display:block;opacity:.35;}
@media only screen and (max-width:1200px){.al-table-wrap{overflow-x:auto;}.al-table{min-width:600px;}}
</style>
<?= $this->end() ?>

<!-- Stats -->
<div class="rd-stats">
    <div class="rd-stat">
        <div class="rd-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-ticket"></i></div>
        <div><div class="rd-stat-label">Total Redeems</div><div class="rd-stat-value"><?= $total ?></div></div>
    </div>
    <div class="rd-stat">
        <div class="rd-stat-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-duotone fa-users"></i></div>
        <div><div class="rd-stat-label">Unique Clients</div><div class="rd-stat-value"><?= $uniqueClients ?></div></div>
    </div>
    <div class="rd-stat">
        <div class="rd-stat-icon" style="background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.2);color:#fbbf24;"><i class="fa-duotone fa-gift"></i></div>
        <div><div class="rd-stat-label">Unique Prizes</div><div class="rd-stat-value"><?= $uniquePrizes ?></div></div>
    </div>
</div>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-icon"><i class="fa-duotone fa-ticket"></i></div>
    <div>
        <h2 class="al-hero-title">Redeem Requests</h2>
        <p class="al-hero-sub"><?= $total ?> request<?= $total !== 1 ? 's' : '' ?> total</p>
    </div>
</div>

<!-- Toolbar -->
<div class="al-toolbar-card">
    <div style="font-size:.82rem;color:rgba(255,255,255,.35);font-weight:700;">
        <span id="rdVisibleCount"><?= $total ?></span> requests
    </div>
    <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="search" id="rdSearch" placeholder="Search client, prize…">
    </div>
</div>

<!-- Table -->
<div class="al-table-wrap">
    <table class="al-table">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>Client</th>
                <th>Prize</th>
                <th class="text-center" style="width:120px;">Status</th>
                <th class="text-end" style="width:180px;">Redeemed At</th>
            </tr>
        </thead>
        <tbody id="rdTbody">
            <?php if (empty($data)): ?>
                <tr><td colspan="5">
                    <div class="al-empty">
                        <i class="fa-duotone fa-ticket"></i>
                        <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);">No redeem requests yet</div>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($data as $index => $row): ?>
                <tr class="rd-row"
                    data-date="<?= $h($row['created_at'] ?? '') ?>"
                    data-search="<?= $h(strtolower(($row['client_name'] ?? '') . ' ' . ($row['prize_name'] ?? ''))) ?>">
                    <td><span class="rd-id">#<?= $index + 1 ?></span></td>
                    <td class="rd-client">
                        <a href="<?= ADMN_URL ?>/client/<?= (int)$row['client_id'] ?>">
                            <?= $h($row['client_name'] ?? '—') ?>
                        </a>
                    </td>
                    <td class="rd-prize">
                        <a href="<?= ADMN_URL ?>/prizes/<?= (int)$row['prize_id'] ?>">
                            <?php if (!empty($row['prize_image'])): ?>
                                <img src="<?= $h($row['prize_image']) ?>" class="rd-prize-icon" alt="">
                            <?php else: ?>
                                <span class="rd-prize-placeholder"><i class="fa-duotone fa-gift"></i></span>
                            <?php endif; ?>
                            <?= $h($row['prize_name'] ?? '—') ?>
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="rd-badge"><i class="fa-solid fa-circle-check" style="font-size:.55rem;"></i> Redeemed</span>
                    </td>
                    <td class="text-end">
                        <span class="rd-date"><?= $h(date('d M Y, H:i', strtotime($row['created_at']))) ?></span>
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
        Showing <strong id="rdFooterCount"><?= $total ?></strong> of <strong><?= $total ?></strong> requests
        <span style="margin-left:10px;font-size:.75rem;color:rgba(255,255,255,.2);" id="rdPageInfo"></span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <select id="rdPerPage" class="ja-per-page">
            <option value="10">10 / page</option>
            <option value="25" selected>25 / page</option>
            <option value="50">50 / page</option>
            <option value="100">100 / page</option>
        </select>
        <div class="ja-pagination" id="rdPagination"></div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    var allRows    = Array.from(document.querySelectorAll('#rdTbody .rd-row'));
    var searchQ    = '';
    var currentPage= 1;
    var perPage    = 25;

    // Sort newest first
    var tbody = document.getElementById('rdTbody');
    allRows.sort(function(a, b) {
        return (b.getAttribute('data-date') || '').localeCompare(a.getAttribute('data-date') || '');
    });
    allRows.forEach(function(r) { tbody.appendChild(r); });

    function getFiltered() {
        return allRows.filter(function(r) {
            return !searchQ || (r.getAttribute('data-search') || '').includes(searchQ);
        });
    }

    function render() {
        var filtered = getFiltered();
        var total    = filtered.length;
        var pages    = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > pages) currentPage = pages;
        var start = (currentPage - 1) * perPage;
        var end   = start + perPage;

        allRows.forEach(function(r) { r.style.display = 'none'; });
        filtered.slice(start, end).forEach(function(r) { r.style.display = ''; });

        var shown = filtered.slice(start, end).length;
        var vc = document.getElementById('rdVisibleCount');
        if (vc) vc.textContent = total;
        var fc = document.getElementById('rdFooterCount');
        if (fc) fc.textContent = shown;
        var pi = document.getElementById('rdPageInfo');
        if (pi) pi.textContent = pages > 1 ? 'Page ' + currentPage + ' of ' + pages : '';

        buildPagination(pages);
    }

    function buildPagination(pages) {
        var wrap = document.getElementById('rdPagination');
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
                currentPage = page; render();
            });
            wrap.appendChild(b);
        }
        function ellipsis() {
            var s = document.createElement('span');
            s.className = 'ja-page-ellipsis'; s.textContent = '…';
            wrap.appendChild(s);
        }

        btn('<i class="fa-solid fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false);
        for (var i = 1; i <= pages; i++) {
            if (i === 1 || i === pages || (i >= currentPage - 1 && i <= currentPage + 1)) btn(i, i, false, i === currentPage);
            else if (i === currentPage - 2 || i === currentPage + 2) ellipsis();
        }
        btn('<i class="fa-solid fa-chevron-right"></i>', currentPage + 1, currentPage === pages, false);
    }

    var searchEl = document.getElementById('rdSearch');
    if (searchEl) searchEl.addEventListener('input', function() {
        searchQ = (searchEl.value || '').toLowerCase().trim();
        currentPage = 1; render();
    });

    var perPageEl = document.getElementById('rdPerPage');
    if (perPageEl) perPageEl.addEventListener('change', function() {
        perPage = parseInt(perPageEl.value, 10) || 25;
        currentPage = 1; render();
    });

    render();
})();
</script>
<?= $this->end() ?>
