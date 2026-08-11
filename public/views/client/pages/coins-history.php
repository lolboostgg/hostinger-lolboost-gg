<?= $this->layout('client/layouts/main', ['meta' => ['title' => 'Coins History — LoLBoost.gg', 'h1' => 'Coins History', 'description' => 'View your LB Coins transaction history.']]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$totalIn  = 0; $totalOut = 0;
foreach ($data as $r) {
    if (($r['type'] ?? '') === 'increment') $totalIn  += (float)($r['amount'] ?? 0);
    else                                    $totalOut += (float)($r['amount'] ?? 0);
}
$balance = $totalIn - $totalOut;
$total   = count($data);
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats ── */
.ch-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:16px;}
.ch-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.ch-stat:hover{transform:translateY(-2px);}
.ch-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.ch-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.ch-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
/* ── Hero ── */
.ch-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.ch-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(250,204,21,.2),rgba(250,204,21,.07));border:1px solid rgba(250,204,21,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#facc15;flex-shrink:0;}
.ch-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.ch-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Toolbar ── */
.ch-toolbar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.ch-pills{display:flex;gap:6px;flex-wrap:wrap;}
.ch-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:all .12s;user-select:none;}
.ch-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.ch-pill.active{background:rgba(250,204,21,.14);border-color:rgba(250,204,21,.4);color:#facc15;}
.ch-pill[data-filter="increment"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.35);color:#4ade80;}
.ch-pill[data-filter="decrement"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.35);color:#fb7185;}
.ch-search-wrap{position:relative;display:flex;align-items:center;}
.ch-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:7px 12px 7px 34px;outline:none;width:220px;transition:border-color .15s;}
.ch-search-wrap input:focus{border-color:rgba(250,204,21,.4);box-shadow:0 0 0 3px rgba(250,204,21,.08);}
.ch-search-wrap input::placeholder{color:rgba(255,255,255,.25);}
.ch-search-icon{position:absolute;left:10px;color:rgba(255,255,255,.3);font-size:.78rem;pointer-events:none;}
/* ── Table ── */
.ch-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.2);margin-bottom:14px;}
.ch-table{width:100%;border-collapse:collapse;}
.ch-table thead tr{border-bottom:1px solid rgba(255,255,255,.06);}
.ch-table thead th{padding:12px 16px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;background:rgba(0,0,0,.12);}
.ch-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.ch-table tbody tr:last-child{border-bottom:none;}
.ch-table tbody tr:hover{background:rgba(255,255,255,.025);}
.ch-table td{padding:13px 16px;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle;}
.ch-id{font-size:.75rem;font-weight:800;color:rgba(255,255,255,.25);}
.ch-reason{font-size:.85rem;color:rgba(255,255,255,.65);}
.ch-amount-in{display:inline-flex;align-items:center;gap:4px;font-weight:900;font-size:.9rem;color:#4ade80;}
.ch-amount-out{display:inline-flex;align-items:center;gap:4px;font-weight:900;font-size:.9rem;color:#fb7185;}
.ch-date{font-size:.8rem;color:rgba(255,255,255,.4);white-space:nowrap;}
/* ── Footer ── */
.ch-footer{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.ch-per-page{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.72)!important;padding:7px 10px!important;font-size:.78rem!important;font-weight:800;outline:none;}
.ch-per-page option{background:#25282a;color:#fff;}
.ch-page-btn{min-width:34px;height:34px;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s;font-family:inherit;}
.ch-page-btn:hover:not(:disabled){background:rgba(250,204,21,.13);border-color:rgba(250,204,21,.35);color:#facc15;transform:translateY(-1px);}
.ch-page-btn.active{background:rgba(250,204,21,.18);border-color:rgba(250,204,21,.45);color:#facc15;}
.ch-page-btn:disabled{opacity:.35;cursor:not-allowed;}
.ch-page-ellipsis{padding:0 5px;color:rgba(255,255,255,.28);font-weight:900;}
.ch-pagination{display:flex;align-items:center;gap:5px;flex-wrap:wrap;}
/* ── Empty ── */
.ch-empty{padding:48px 20px;text-align:center;}
.ch-empty i{font-size:2.2rem;margin-bottom:10px;display:block;color:rgba(255,255,255,.15);}
@media(max-width:768px){.ch-search-wrap input{width:160px;}.ch-table-wrap{overflow-x:auto;}.ch-table{min-width:500px;}}
</style>
<?= $this->end() ?>

<!-- Stats -->
<div class="ch-stats">
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.2);color:#facc15;"><i class="fa-duotone fa-coins"></i></div>
        <div><div class="ch-stat-label">Balance</div><div class="ch-stat-value" style="color:<?= $balance >= 0 ? '#facc15' : '#fb7185' ?>"><?= number_format($balance, 0) ?></div></div>
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
        <div class="ch-stat-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-duotone fa-receipt"></i></div>
        <div><div class="ch-stat-label">Transactions</div><div class="ch-stat-value"><?= $total ?></div></div>
    </div>
</div>

<!-- Hero -->
<div class="ch-hero">
    <div class="ch-hero-icon"><i class="fa-duotone fa-coins"></i></div>
    <div>
        <h2 class="ch-hero-title">LB Coins History</h2>
        <p class="ch-hero-sub"><?= $total ?> transaction<?= $total !== 1 ? 's' : '' ?> · current balance: <strong style="color:#facc15;"><?= number_format($balance, 0) ?> coins</strong></p>
    </div>
</div>

<!-- Toolbar -->
<div class="ch-toolbar">
    <div class="ch-pills" id="chTypeFilter">
        <span class="ch-pill active" data-filter="all"><i class="fa-duotone fa-layer-group" style="font-size:.75rem;"></i> All</span>
        <span class="ch-pill" data-filter="increment"><i class="fa-solid fa-circle-plus" style="font-size:.7rem;"></i> Earned</span>
        <span class="ch-pill" data-filter="decrement"><i class="fa-solid fa-circle-minus" style="font-size:.7rem;"></i> Spent</span>
    </div>
    <div class="ch-search-wrap">
        <i class="fa-solid fa-magnifying-glass ch-search-icon"></i>
        <input type="search" id="chSearch" placeholder="Search reason…">
    </div>
</div>

<!-- Table -->
<div class="ch-table-wrap">
    <table class="ch-table">
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Reason</th>
                <th class="text-center" style="width:110px;">Amount</th>
                <th class="text-end" style="width:160px;">Date</th>
            </tr>
        </thead>
        <tbody id="chTbody">
            <?php if (empty($data)): ?>
                <tr><td colspan="4">
                    <div class="ch-empty">
                        <i class="fa-duotone fa-coins"></i>
                        <div style="font-weight:800;font-size:.95rem;color:rgba(255,255,255,.5);margin-bottom:4px;">No transactions yet</div>
                        <div style="font-size:.8rem;color:rgba(255,255,255,.25);">Coins earned from orders will appear here.</div>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($data as $row):
                    $isIn = ($row['type'] ?? '') === 'increment';
                ?>
                <tr class="ch-row"
                    data-type="<?= $h($row['type'] ?? '') ?>"
                    data-search="<?= $h(strtolower($row['reason'] ?? '')) ?>"
                    data-date="<?= $h($row['created_at'] ?? '') ?>">
                    <td><span class="ch-id">#<?= (int)$row['id'] ?></span></td>
                    <td><span class="ch-reason"><?= $h($row['reason'] ?? '—') ?></span></td>
                    <td class="text-center">
                        <?php if ($isIn): ?>
                            <span class="ch-amount-in"><i class="fa-solid fa-plus" style="font-size:.6rem;"></i><?= $h($row['amount']) ?></span>
                        <?php else: ?>
                            <span class="ch-amount-out"><i class="fa-solid fa-minus" style="font-size:.6rem;"></i><?= $h($row['amount']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><span class="ch-date"><?= $h(util_format_date_display($row['created_at'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<div class="ch-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
        Showing <strong id="chVisibleCount">0</strong> of <strong><?= $total ?></strong>
        <span style="margin-left:8px;font-size:.75rem;color:rgba(255,255,255,.2);" id="chPageInfo"></span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <select id="chPerPage" class="ch-per-page">
            <option value="10">10 / page</option>
            <option value="25" selected>25 / page</option>
            <option value="50">50 / page</option>
        </select>
        <div class="ch-pagination" id="chPagination"></div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    var allRows    = Array.from(document.querySelectorAll('#chTbody .ch-row'));
    var activeType = 'all', searchQ = '', currentPage = 1, perPage = 25;

    // Sort newest first
    var tbody = document.getElementById('chTbody');
    allRows.sort(function(a,b){ return (b.getAttribute('data-date')||'').localeCompare(a.getAttribute('data-date')||''); });
    allRows.forEach(function(r){ tbody.appendChild(r); });

    function getFiltered(){
        return allRows.filter(function(r){
            var typeOk   = activeType === 'all' || r.getAttribute('data-type') === activeType;
            var searchOk = !searchQ   || (r.getAttribute('data-search')||'').includes(searchQ);
            return typeOk && searchOk;
        });
    }

    function render(){
        var filtered = getFiltered(), total = filtered.length;
        var pages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > pages) currentPage = pages;
        var start = (currentPage-1)*perPage, end = start+perPage;
        allRows.forEach(function(r){ r.style.display='none'; });
        filtered.slice(start,end).forEach(function(r){ r.style.display=''; });
        var shown = filtered.slice(start,end).length;
        var vc=document.getElementById('chVisibleCount'); if(vc) vc.textContent=shown;
        var pi=document.getElementById('chPageInfo'); if(pi) pi.textContent=pages>1?'Page '+currentPage+' of '+pages:'';
        buildPagination(pages);
    }

    function buildPagination(pages){
        var wrap=document.getElementById('chPagination'); if(!wrap) return;
        wrap.innerHTML=''; if(pages<=1) return;
        function btn(label,page,disabled,active){
            var b=document.createElement('button');
            b.className='ch-page-btn'+(active?' active':''); b.innerHTML=label; b.disabled=!!disabled;
            b.addEventListener('click',function(){ if(disabled||currentPage===page) return; currentPage=page; render(); });
            wrap.appendChild(b);
        }
        function ell(){ var s=document.createElement('span'); s.className='ch-page-ellipsis'; s.textContent='…'; wrap.appendChild(s); }
        btn('<i class="fa-solid fa-chevron-left"></i>',currentPage-1,currentPage===1,false);
        for(var i=1;i<=pages;i++){
            if(i===1||i===pages||(i>=currentPage-1&&i<=currentPage+1)) btn(i,i,false,i===currentPage);
            else if(i===currentPage-2||i===currentPage+2) ell();
        }
        btn('<i class="fa-solid fa-chevron-right"></i>',currentPage+1,currentPage===pages,false);
    }

    document.querySelectorAll('#chTypeFilter .ch-pill').forEach(function(pill){
        pill.addEventListener('click',function(){
            document.querySelectorAll('#chTypeFilter .ch-pill').forEach(function(p){ p.classList.remove('active'); });
            pill.classList.add('active'); activeType=pill.getAttribute('data-filter'); currentPage=1; render();
        });
    });
    var se=document.getElementById('chSearch');
    if(se) se.addEventListener('input',function(){ searchQ=(se.value||'').toLowerCase().trim(); currentPage=1; render(); });
    var pp=document.getElementById('chPerPage');
    if(pp) pp.addEventListener('change',function(){ perPage=parseInt(pp.value,10)||25; currentPage=1; render(); });

    render();
})();
</script>
<?= $this->end() ?>
