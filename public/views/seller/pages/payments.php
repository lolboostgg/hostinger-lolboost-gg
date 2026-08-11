<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<?php
$seller_id = (int)($seller_data['id'] ?? 0);
$payments  = $payments ?? [];

$totalIn  = 0;
$totalOut = 0;
foreach ($payments as $p) {
    $a = (int)($p['amount_cents'] ?? 0);
    if ($a > 0) $totalIn  += $a;
    else        $totalOut += abs($a);
}
?>

<style>
.pm-page .card { background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important; }
.pm-page .card::before { display:none!important; }

.pm-summary-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px; }
@media(max-width:576px){ .pm-summary-grid { grid-template-columns:1fr 1fr; } }
.pm-summary-tile { border:1px solid var(--bs-card-border-color);border-radius:14px;padding:14px 16px;background:rgba(255,255,255,.025); }
.pm-summary-lbl  { font-size:.7rem;font-weight:800;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.09em;margin-bottom:4px; }
.pm-summary-val  { font-size:1.2rem;font-weight:950;color:rgba(255,255,255,.92); }

.pm-pills { display:flex;flex-wrap:wrap;gap:6px; }
.pm-pill { display:inline-flex;align-items:center;gap:.35rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none; }
.pm-pill:hover { background:rgba(255,255,255,.08);color:rgba(255,255,255,.85); }
.pm-pill.active { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.pm-pill[data-filter="sale_payout"].active       { background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80; }
.pm-pill[data-filter="payout_withdrawal"].active { background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.30);color:#fb7185; }
.pm-pill[data-filter="manual_adjustment"].active { background:rgba(96,165,250,.12);border-color:rgba(96,165,250,.30);color:#60a5fa; }

.pm-table { width:100%;border-collapse:collapse; }
.pm-table thead th { font-size:.7rem;font-weight:800;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.09em;padding:10px 16px;border-bottom:1px solid var(--bs-card-border-color);white-space:nowrap; }
.pm-table tbody tr { border-bottom:1px solid var(--bs-card-border-color);transition:background .1s; }
.pm-table tbody tr:last-child { border-bottom:0; }
.pm-table tbody tr:hover { background:rgba(255,255,255,.02); }
.pm-table tbody td { padding:13px 16px;font-size:.87rem;color:rgba(255,255,255,.82);vertical-align:middle; }

.pm-type-badge { display:inline-flex;align-items:center;gap:.3rem;padding:3px 10px;border-radius:99px;font-size:.72rem;font-weight:800; }
.pm-type-sale       { background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);color:#4ade80; }
.pm-type-withdrawal { background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.25);color:#fb7185; }
.pm-type-manual     { background:rgba(96,165,250,.12);border:1px solid rgba(96,165,250,.25);color:#60a5fa; }
.pm-type-default    { background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.6); }

.pm-amount-pos { color:#4ade80;font-weight:900; }
.pm-amount-neg { color:#fb7185;font-weight:900; }

.pm-search-wrap { position:relative; }
.pm-search-wrap input { background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s; }
.pm-search-wrap input:focus { border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important; }
.pm-search-wrap input::placeholder { color:rgba(255,255,255,.25)!important; }
.pm-search-icon { position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none; }

.pm-empty { text-align:center;padding:48px 24px; }
.pm-empty-icon { font-size:2.5rem;color:rgba(255,255,255,.15);margin-bottom:12px; }
.pm-empty-text { color:rgba(255,255,255,.4);font-size:.88rem; }

.pm-page-btn { width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s; }
.pm-page-btn:hover:not(:disabled) { background:rgba(255,255,255,.09); }
.pm-page-btn.pm-active { background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.pm-page-btn:disabled { opacity:.35;cursor:not-allowed; }
</style>

<div class="pm-page">

  <!-- Summary -->
  <div class="pm-summary-grid">
    <div class="pm-summary-tile">
      <div class="pm-summary-lbl"><i class="fa-solid fa-arrow-down me-1"></i>Total Earned</div>
      <div class="pm-summary-val" style="color:#4ade80;">+<?= number_format($totalIn / 100, 2) ?> €</div>
    </div>
    <div class="pm-summary-tile">
      <div class="pm-summary-lbl"><i class="fa-solid fa-arrow-up me-1"></i>Total Withdrawn</div>
      <div class="pm-summary-val" style="color:#fb7185;">-<?= number_format($totalOut / 100, 2) ?> €</div>
    </div>
    <div class="pm-summary-tile">
      <div class="pm-summary-lbl"><i class="fa-solid fa-list me-1"></i>Transactions</div>
      <div class="pm-summary-val"><?= count($payments) ?></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header" style="border-bottom:1px solid var(--bs-card-border-color);padding:16px 20px;">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-duotone fa-money-bill-transfer" style="color:#9f8cff;font-size:1rem;"></i>
          <span style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.92);">Payments History</span>
        </div>
        <div class="pm-search-wrap">
          <i class="fa-solid fa-magnifying-glass pm-search-icon"></i>
          <input type="search" id="pmSearch" placeholder="Search payments…">
        </div>
      </div>
      <div class="pm-pills mt-3">
        <span class="pm-pill active" data-filter="all">All</span>
        <span class="pm-pill" data-filter="sale_payout"><i class="fa-solid fa-tag"></i> Account Sale</span>
        <span class="pm-pill" data-filter="payout_withdrawal"><i class="fa-solid fa-arrow-up-from-bracket"></i> Payout Withdrawal</span>
        <span class="pm-pill" data-filter="manual_adjustment"><i class="fa-solid fa-sliders"></i> Manual Adjustment</span>
      </div>
    </div>

    <div class="table-responsive">
      <table class="pm-table">
        <thead>
          <tr>
            <th style="width:60px;">ID</th>
            <th>Type</th>
            <th>Note</th>
            <th class="text-end">Amount</th>
            <th class="text-end">Balance After</th>
            <th class="text-end">Date</th>
          </tr>
        </thead>
        <tbody id="pmTbody">
          <?php if (empty($payments)): ?>
            <tr class="pm-static-empty"><td colspan="6">
              <div class="pm-empty"><div class="pm-empty-icon"><i class="fa-duotone fa-inbox"></i></div><div class="pm-empty-text">No payments yet.</div></div>
            </td></tr>
          <?php else: foreach ($payments as $row):
            $amt      = (int)($row['amount_cents'] ?? 0);
            $balAfter = (int)($row['balance_after'] ?? 0);
            $isPos    = $amt >= 0;
            $typeRaw  = (string)($row['type'] ?? '');
            $typeLabel = match($typeRaw) {
              'sale_payout'       => 'Account Sale',
              'payout_withdrawal' => 'Payout Withdrawal',
              'manual_adjustment' => 'Manual Adjustment',
              'manual_credit'     => 'Manual Credit',
              'manual_debit'      => 'Manual Debit',
              default             => htmlspecialchars(ucwords(str_replace('_', ' ', $typeRaw ?: '—'))),
            };
            $typeCls = match($typeRaw) {
              'sale_payout'                         => 'pm-type-sale',
              'payout_withdrawal'                   => 'pm-type-withdrawal',
              'manual_adjustment','manual_credit','manual_debit' => 'pm-type-manual',
              default                               => 'pm-type-default',
            };
            $createdAt  = $row['created_at'] ?? '';
            $createdFmt = $createdAt ? date('d.m.Y H:i', strtotime($createdAt)) : '—';
          ?>
          <tr data-type="<?= htmlspecialchars($typeRaw) ?>"
              data-search="<?= htmlspecialchars(strtolower($typeLabel . ' ' . ($row['note'] ?? '') . ' ' . $createdFmt)) ?>">
            <td style="color:rgba(255,255,255,.4);font-size:.8rem;">#<?= (int)$row['id'] ?></td>
            <td><span class="pm-type-badge <?= $typeCls ?>"><?= $typeLabel ?></span></td>
            <td style="color:rgba(255,255,255,.55);font-size:.83rem;max-width:300px;white-space:normal;"><?= htmlspecialchars($row['note'] ?? '—') ?></td>
            <td class="text-end <?= $isPos ? 'pm-amount-pos' : 'pm-amount-neg' ?>">
              <?= ($isPos ? '+' : '−') . number_format(abs($amt) / 100, 2) ?> €
            </td>
            <td class="text-end" style="color:rgba(255,255,255,.45);font-size:.83rem;"><?= number_format($balAfter / 100, 2) ?> €</td>
            <td class="text-end" style="color:rgba(255,255,255,.45);font-size:.83rem;white-space:nowrap;"><?= $createdFmt ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card-footer" style="border-top:1px solid var(--bs-card-border-color);padding:14px 20px;">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div style="font-size:.82rem;color:rgba(255,255,255,.45);">
          Showing <span id="pmShowing">—</span> of <span id="pmTotal">—</span>
        </div>
        <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;" id="pmPagination"></div>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var PER_PAGE = 8;
  var filter   = 'all';
  var search   = '';
  var page     = 1;

  var tbody   = document.getElementById('pmTbody');
  var allRows = Array.from(tbody.querySelectorAll('tr[data-type]'));
  var showEl  = document.getElementById('pmShowing');
  var totEl   = document.getElementById('pmTotal');
  var pageEl  = document.getElementById('pmPagination');
  var srchEl  = document.getElementById('pmSearch');
  var pills   = document.querySelectorAll('.pm-pill');

  function getFiltered(){
    return allRows.filter(function(r){
      var okType   = filter === 'all' || r.dataset.type === filter;
      var okSearch = !search  || (r.dataset.search||'').indexOf(search) !== -1;
      return okType && okSearch;
    });
  }

  function render(){
    var filtered = getFiltered();
    var total    = filtered.length;
    var pages    = Math.max(1, Math.ceil(total / PER_PAGE));
    if(page > pages) page = pages;

    var start = (page - 1) * PER_PAGE;
    var end   = start + PER_PAGE;

    allRows.forEach(function(r){ r.style.display = 'none'; });
    filtered.slice(start, end).forEach(function(r){ r.style.display = ''; });

    // Empty state
    var old = tbody.querySelector('.pm-js-empty');
    if(old) old.remove();
    if(total === 0){
      var tr = document.createElement('tr');
      tr.className = 'pm-js-empty';
      tr.innerHTML = '<td colspan="6"><div class="pm-empty"><div class="pm-empty-icon"><i class="fa-duotone fa-inbox"></i></div><div class="pm-empty-text">No payments match your filter.</div></div></td>';
      tbody.appendChild(tr);
    }

    var shown = Math.min(end, total) - start;
    showEl.textContent = total > 0 ? (start+1) + '–' + Math.min(end,total) : '0';
    totEl.textContent  = total;

    // Pagination
    pageEl.innerHTML = '';
    if(pages <= 1) return;

    function btn(label, p, disabled, active){
      var b = document.createElement('button');
      b.className = 'pm-page-btn' + (active ? ' pm-active' : '');
      b.innerHTML = label;
      b.disabled  = !!disabled;
      if(!disabled) b.addEventListener('click', function(){ page = p; render(); });
      return b;
    }

    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>', page-1, page===1, false));
    for(var i=1;i<=pages;i++){
      if(pages>7 && i>2 && i<pages-1 && Math.abs(i-page)>1){
        if(i===3||i===pages-2){
          var d=document.createElement('span');
          d.style.cssText='color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;';
          d.textContent='…';
          pageEl.appendChild(d);
        }
        continue;
      }
      pageEl.appendChild(btn(i, i, false, i===page));
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>', page+1, page===pages, false));
  }

  pills.forEach(function(p){
    p.addEventListener('click', function(){
      pills.forEach(function(x){ x.classList.remove('active'); });
      p.classList.add('active');
      filter = p.dataset.filter;
      page   = 1;
      render();
    });
  });

  if(srchEl) srchEl.addEventListener('input', function(){
    search = srchEl.value.trim().toLowerCase();
    page   = 1;
    render();
  });

  render();
})();
</script>
<?= $this->end() ?>
