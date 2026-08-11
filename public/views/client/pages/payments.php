<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$total = count($payments);

// Tally totals per currency
$totalPaid = 0;
$completed = array_filter($payments, fn($p) => ($p['status'] ?? '') === 'completed');
foreach ($completed as $p) { $totalPaid += (float)($p['amount'] ?? 0); }
$pending   = array_filter($payments, fn($p) => ($p['status'] ?? '') === 'pending');
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats ── */
.py-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:16px;}
.py-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.py-stat:hover{transform:translateY(-2px);}
.py-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.py-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.py-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
/* ── Hero ── */
.py-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.py-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(74,222,128,.2),rgba(74,222,128,.07));border:1px solid rgba(74,222,128,.22);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#4ade80;flex-shrink:0;}
.py-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.py-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Toolbar ── */
.py-toolbar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.py-pills{display:flex;gap:6px;flex-wrap:wrap;}
.py-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:all .12s;user-select:none;}
.py-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.py-pill.active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.35);color:#4ade80;}
.py-pill[data-filter="pending"].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.32);color:#facc15;}
.py-pill[data-filter="failed"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.32);color:#fb7185;}
.py-search-wrap{position:relative;display:flex;align-items:center;}
.py-search-wrap input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.82rem;padding:7px 12px 7px 34px;outline:none;width:220px;transition:border-color .15s;}
.py-search-wrap input:focus{border-color:rgba(74,222,128,.4);box-shadow:0 0 0 3px rgba(74,222,128,.08);}
.py-search-wrap input::placeholder{color:rgba(255,255,255,.25);}
.py-search-icon{position:absolute;left:10px;color:rgba(255,255,255,.3);font-size:.78rem;pointer-events:none;}
/* ── Table ── */
.py-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.2);margin-bottom:14px;}
.py-table{width:100%;border-collapse:collapse;}
.py-table thead tr{border-bottom:1px solid rgba(255,255,255,.06);}
.py-table thead th{padding:12px 16px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;background:rgba(0,0,0,.12);}
.py-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.py-table tbody tr:last-child{border-bottom:none;}
.py-table tbody tr:hover{background:rgba(255,255,255,.025);}
.py-table td{padding:13px 16px;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle;}
/* ── Cells ── */
.py-invoice{font-size:.78rem;font-weight:800;color:rgba(255,255,255,.28);}
.py-processor{display:inline-flex;align-items:center;gap:5px;font-size:.8rem;font-weight:700;color:rgba(255,255,255,.6);}
.py-order a{font-size:.82rem;font-weight:700;color:rgba(255,255,255,.5);text-decoration:none;transition:color .12s;}
.py-order a:hover{color:#4ade80;}
.py-amount{font-weight:900;color:rgba(255,255,255,.88);}
.py-date{font-size:.8rem;color:rgba(255,255,255,.4);white-space:nowrap;}
/* Status badges */
.py-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:.72rem;font-weight:800;}
.py-badge--completed{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.25);color:#4ade80;}
.py-badge--pending{background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.25);color:#facc15;}
.py-badge--failed{background:rgba(251,113,133,.1);border:1px solid rgba(251,113,133,.25);color:#fb7185;}
.py-badge--refunded{background:rgba(109,92,255,.1);border:1px solid rgba(109,92,255,.25);color:#c4b5fd;}
.py-badge--default{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.5);}
/* ── Footer ── */
.py-footer{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.py-per-page{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.72)!important;padding:7px 10px!important;font-size:.78rem!important;font-weight:800;outline:none;}
.py-per-page option{background:#25282a;color:#fff;}
.py-page-btn{min-width:34px;height:34px;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s;font-family:inherit;}
.py-page-btn:hover:not(:disabled){background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.3);color:#4ade80;transform:translateY(-1px);}
.py-page-btn.active{background:rgba(74,222,128,.16);border-color:rgba(74,222,128,.38);color:#4ade80;}
.py-page-btn:disabled{opacity:.35;cursor:not-allowed;}
.py-page-ellipsis{padding:0 5px;color:rgba(255,255,255,.28);font-weight:900;}
.py-pagination{display:flex;align-items:center;gap:5px;flex-wrap:wrap;}
/* ── Empty ── */
.py-empty{padding:48px 20px;text-align:center;}
.py-empty i{font-size:2.2rem;margin-bottom:10px;display:block;color:rgba(255,255,255,.15);}
@media(max-width:768px){.py-search-wrap input{width:160px;}.py-table-wrap{overflow-x:auto;}.py-table{min-width:650px;}}
</style>
<?= $this->end() ?>

<!-- Stats -->
<div class="py-stats">
    <div class="py-stat">
        <div class="py-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-credit-card"></i></div>
        <div><div class="py-stat-label">Total Payments</div><div class="py-stat-value"><?= $total ?></div></div>
    </div>
    <div class="py-stat">
        <div class="py-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-circle-check"></i></div>
        <div><div class="py-stat-label">Completed</div><div class="py-stat-value"><?= count($completed) ?></div></div>
    </div>
    <div class="py-stat">
        <div class="py-stat-icon" style="background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.2);color:#facc15;"><i class="fa-duotone fa-clock"></i></div>
        <div><div class="py-stat-label">Pending</div><div class="py-stat-value"><?= count($pending) ?></div></div>
    </div>
    <div class="py-stat">
        <div class="py-stat-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-duotone fa-euro-sign"></i></div>
        <div><div class="py-stat-label">Total Paid</div><div class="py-stat-value">€<?= number_format($totalPaid, 2) ?></div></div>
    </div>
</div>

<!-- Hero -->
<div class="py-hero">
    <div class="py-hero-icon"><i class="fa-duotone fa-credit-card"></i></div>
    <div>
        <h2 class="py-hero-title">Payments</h2>
        <p class="py-hero-sub"><?= $total ?> payment<?= $total !== 1 ? 's' : '' ?> · <?= count($completed) ?> completed</p>
    </div>
</div>

<!-- Toolbar -->
<div class="py-toolbar">
    <div class="py-pills" id="pyStatusFilter">
        <span class="py-pill active" data-filter="all"><i class="fa-duotone fa-layer-group" style="font-size:.75rem;"></i> All</span>
        <span class="py-pill" data-filter="completed"><i class="fa-solid fa-circle-check" style="font-size:.65rem;"></i> Completed</span>
        <span class="py-pill" data-filter="pending"><i class="fa-duotone fa-clock" style="font-size:.65rem;"></i> Pending</span>
        <span class="py-pill" data-filter="failed"><i class="fa-solid fa-xmark" style="font-size:.7rem;"></i> Failed</span>
    </div>
    <div class="py-search-wrap">
        <i class="fa-solid fa-magnifying-glass py-search-icon"></i>
        <input type="search" id="pySearch" placeholder="Search invoice, order…">
    </div>
</div>

<!-- Table -->
<div class="py-table-wrap">
    <table class="py-table">
        <thead>
            <tr>
                <th style="width:120px;">Invoice</th>
                <th style="width:130px;">Processor</th>
                <th style="width:100px;">Order</th>
                <th style="width:120px;">Status</th>
                <th class="text-end" style="width:110px;">Amount</th>
                <th class="text-end" style="width:160px;">Date</th>
            </tr>
        </thead>
        <tbody id="pyTbody">
            <?php if (empty($payments)): ?>
                <tr><td colspan="6">
                    <div class="py-empty">
                        <i class="fa-duotone fa-credit-card"></i>
                        <div style="font-weight:800;font-size:.95rem;color:rgba(255,255,255,.5);margin-bottom:4px;">No payments yet</div>
                        <div style="font-size:.8rem;color:rgba(255,255,255,.25);">Your payment history will appear here.</div>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($payments as $payment):
                    $status = strtolower($payment['status'] ?? 'default');
                    $badgeClass = match($status) {
                        'completed' => 'py-badge--completed',
                        'pending'   => 'py-badge--pending',
                        'failed'    => 'py-badge--failed',
                        'refunded'  => 'py-badge--refunded',
                        default     => 'py-badge--default',
                    };
                    $badgeIcon = match($status) {
                        'completed' => '<i class="fa-solid fa-circle-check" style="font-size:.55rem;"></i>',
                        'pending'   => '<i class="fa-duotone fa-clock" style="font-size:.55rem;"></i>',
                        'failed'    => '<i class="fa-solid fa-xmark" style="font-size:.6rem;"></i>',
                        'refunded'  => '<i class="fa-duotone fa-rotate-left" style="font-size:.55rem;"></i>',
                        default     => '',
                    };
                ?>
                <tr class="py-row"
                    data-status="<?= $h($status) ?>"
                    data-date="<?= $h($payment['created_at'] ?? '') ?>"
                    data-search="<?= $h(strtolower(($payment['invoice_id'] ?? '') . ' ' . ($payment['order_id'] ?? '') . ' ' . ($payment['processor'] ?? ''))) ?>">
                    <td><span class="py-invoice">#<?= $h($payment['invoice_id']) ?></span></td>
                    <td><span class="py-processor"><i class="fa-duotone fa-building-columns" style="font-size:.75rem;"></i><?= $h(util_format_default_type($payment['processor'])) ?></span></td>
                    <td class="py-order"><a href="#">#<?= $h($payment['order_id']) ?></a></td>
                    <td><span class="py-badge <?= $badgeClass ?>"><?= $badgeIcon ?><?= ucfirst($status) ?></span></td>
                    <td class="text-end"><span class="py-amount"><?= $h(util_format_currency_display($payment['currency']) . util_format_price_display($payment['amount'])) ?></span></td>
                    <td class="text-end"><span class="py-date"><?= $h(util_format_date_display($payment['created_at'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<div class="py-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
        Showing <strong id="pyVisibleCount">0</strong> of <strong><?= $total ?></strong>
        <span style="margin-left:8px;font-size:.75rem;color:rgba(255,255,255,.2);" id="pyPageInfo"></span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <select id="pyPerPage" class="py-per-page">
            <option value="10">10 / page</option>
            <option value="25" selected>25 / page</option>
            <option value="50">50 / page</option>
        </select>
        <div class="py-pagination" id="pyPagination"></div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    var allRows = Array.from(document.querySelectorAll('#pyTbody .py-row'));
    var activeStatus = 'all', searchQ = '', currentPage = 1, perPage = 25;

    // Sort newest first
    var tbody = document.getElementById('pyTbody');
    allRows.sort(function(a,b){ return (b.getAttribute('data-date')||'').localeCompare(a.getAttribute('data-date')||''); });
    allRows.forEach(function(r){ tbody.appendChild(r); });

    function getFiltered(){
        return allRows.filter(function(r){
            var statusOk = activeStatus === 'all' || r.getAttribute('data-status') === activeStatus;
            var searchOk = !searchQ || (r.getAttribute('data-search')||'').includes(searchQ);
            return statusOk && searchOk;
        });
    }

    function render(){
        var filtered = getFiltered(), total = filtered.length;
        var pages = Math.max(1, Math.ceil(total/perPage));
        if (currentPage > pages) currentPage = pages;
        var start=(currentPage-1)*perPage, end=start+perPage;
        allRows.forEach(function(r){ r.style.display='none'; });
        filtered.slice(start,end).forEach(function(r){ r.style.display=''; });
        var shown=filtered.slice(start,end).length;
        var vc=document.getElementById('pyVisibleCount'); if(vc) vc.textContent=shown;
        var pi=document.getElementById('pyPageInfo'); if(pi) pi.textContent=pages>1?'Page '+currentPage+' of '+pages:'';
        buildPagination(pages);
    }

    function buildPagination(pages){
        var wrap=document.getElementById('pyPagination'); if(!wrap) return;
        wrap.innerHTML=''; if(pages<=1) return;
        function btn(label,page,disabled,active){
            var b=document.createElement('button');
            b.className='py-page-btn'+(active?' active':''); b.innerHTML=label; b.disabled=!!disabled;
            b.addEventListener('click',function(){ if(disabled||currentPage===page) return; currentPage=page; render(); });
            wrap.appendChild(b);
        }
        function ell(){ var s=document.createElement('span'); s.className='py-page-ellipsis'; s.textContent='…'; wrap.appendChild(s); }
        btn('<i class="fa-solid fa-chevron-left"></i>',currentPage-1,currentPage===1,false);
        for(var i=1;i<=pages;i++){
            if(i===1||i===pages||(i>=currentPage-1&&i<=currentPage+1)) btn(i,i,false,i===currentPage);
            else if(i===currentPage-2||i===currentPage+2) ell();
        }
        btn('<i class="fa-solid fa-chevron-right"></i>',currentPage+1,currentPage===pages,false);
    }

    document.querySelectorAll('#pyStatusFilter .py-pill').forEach(function(pill){
        pill.addEventListener('click',function(){
            document.querySelectorAll('#pyStatusFilter .py-pill').forEach(function(p){ p.classList.remove('active'); });
            pill.classList.add('active'); activeStatus=pill.getAttribute('data-filter'); currentPage=1; render();
        });
    });
    var se=document.getElementById('pySearch');
    if(se) se.addEventListener('input',function(){ searchQ=(se.value||'').toLowerCase().trim(); currentPage=1; render(); });
    var pp=document.getElementById('pyPerPage');
    if(pp) pp.addEventListener('change',function(){ perPage=parseInt(pp.value,10)||25; currentPage=1; render(); });

    render();
})();
</script>
<?= $this->end() ?>
