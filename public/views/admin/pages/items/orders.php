<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Item Orders | Admin Area']]) ?>
<?php
// Route passes $data, normalize to $orders
$orders = $orders ?? $data ?? [];
$orders = is_array($orders) ? $orders : [];

function aio_status_label($row) {
    $raw = strtolower(trim((string)($row['status'] ?? 'pending')));
    if (in_array($raw, ['completed','delivered','success','fulfilled'], true)) return 'Delivered';
    if (in_array($raw, ['cancelled','canceled','failed','refunded'], true)) return 'Cancelled';
    return 'Pending';
}
function aio_status_class($status) {
    if ($status === 'Delivered') return 'tb-badge--ok';
    if ($status === 'Cancelled') return 'tb-badge--bad';
    return 'tb-badge--wait';
}
?>

<?php echo $this->start('styles'); ?>
<style>
.tb-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:14px}
.tb-hero-left{display:flex;align-items:center;gap:14px}.tb-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.05rem;flex-shrink:0}.tb-hero-title{font-size:1.1rem;font-weight:950;color:#fff;margin:0}.tb-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0}
.tb-toolbar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.tb-pills{display:flex;gap:6px;flex-wrap:wrap}.tb-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:999px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6)}.tb-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd}.tb-pill[data-status="Delivered"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80}.tb-pill[data-status="Cancelled"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.30);color:#fb7185}
.tb-search-wrap{position:relative}.tb-search-wrap input{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px}.tb-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem}
.tb-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;background:#25282a}.tb-table{width:100%;border-collapse:collapse}.tb-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06)}.tb-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap}.tb-table thead th.sortable{cursor:pointer}.tb-table thead th.sortable:hover{color:rgba(255,255,255,.7)}.tb-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;cursor:pointer}.tb-table tbody tr:last-child{border-bottom:none}.tb-table tbody tr:hover{background:rgba(109,92,255,.08)}.tb-table tbody td{padding:13px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8)}
.tb-item{display:flex;align-items:center;gap:11px}.tb-item-img{width:40px;height:40px;border-radius:10px;object-fit:cover;background:rgba(255,255,255,.04);flex-shrink:0}.tb-item-name{font-size:.88rem;font-weight:800;color:#fff;line-height:1.2}.tb-item-sub{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:1px}.tb-id{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25)}.tb-money{font-weight:800;color:#fff}.tb-date{font-size:.78rem;color:rgba(255,255,255,.38)}.tb-badge{display:inline-flex;align-items:center;gap:.3rem;padding:4px 10px;border-radius:999px;font-size:.71rem;font-weight:800;white-space:nowrap}.tb-badge--ok{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80}.tb-badge--bad{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185}.tb-badge--wait{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15}
.tb-user-link{color:#c4b5fd;text-decoration:none;font-weight:700}.tb-user-link:hover{color:#fff;text-decoration:underline}
.tb-view{display:inline-flex;align-items:center;gap:.35rem;padding:7px 14px;border-radius:9px;font-size:.79rem;font-weight:800;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.28);color:#c4b5fd;text-decoration:none}.tb-view:hover{background:rgba(109,92,255,.28);border-color:rgba(109,92,255,.55);color:#fff}
.tb-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:14px 0 0}.tb-page-btn{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.tb-page-btn.active{background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#fff}.tb-page-btn:disabled{opacity:.35;cursor:not-allowed}
.tb-empty{text-align:center;padding:64px 24px;color:rgba(255,255,255,.35)}.tb-empty i{font-size:3rem;display:block;margin-bottom:12px;opacity:.3}
.tb-sort-icon{margin-left:4px;opacity:.35;font-size:.6rem}.th-sort-asc .tb-sort-icon,.th-sort-desc .tb-sort-icon{opacity:1;color:#c4b5fd}
@media(max-width:991px){.tb-table-scroll{overflow:auto}.tb-table{min-width:1100px}.tb-search-wrap input{width:100%}}
</style>
<?php echo $this->end(); ?>

<div>
  <div class="tb-hero">
    <div class="tb-hero-left">
      <div class="tb-hero-icon"><i class="fa-solid fa-box-open"></i></div>
      <div>
        <h1 class="tb-hero-title">Item Orders</h1>
        <div class="tb-hero-sub"><?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?> total</div>
      </div>
    </div>
  </div>

  <div class="tb-toolbar">
    <div class="tb-pills">
      <button type="button" class="tb-pill active" data-status="all">All</button>
      <button type="button" class="tb-pill" data-status="Pending">Pending</button>
      <button type="button" class="tb-pill" data-status="Delivered">Delivered</button>
      <button type="button" class="tb-pill" data-status="Cancelled">Cancelled</button>
    </div>
    <div class="tb-search-wrap">
      <i class="fa-solid fa-magnifying-glass tb-search-icon"></i>
      <input type="text" id="tbSearch" placeholder="Search orders…">
    </div>
  </div>

  <div class="tb-table-wrap">
    <div class="tb-table-scroll">
      <table class="tb-table">
        <thead>
          <tr>
            <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort tb-sort-icon"></i></th>
            <th>Item</th>
            <th>Seller</th>
            <th>Client</th>
            <th class="sortable" data-col="price">Price <i class="fa-solid fa-sort tb-sort-icon"></i></th>
            <th>Status</th>
            <th class="sortable" data-col="date">Created <i class="fa-solid fa-sort tb-sort-icon"></i></th>
            <th></th>
          </tr>
        </thead>
        <tbody id="tbTbody">
          <?php if (!empty($orders)): foreach ($orders as $row):
            $orderId    = (int)($row['id'] ?? 0);
            $status     = aio_status_label($row);
            $statusClass= aio_status_class($status);
            $price      = round((float)($row['price'] ?? 0) / 100, 2);
            $title      = (string)($row['item_title'] ?? 'Item');
            $server     = (string)($row['server'] ?? '');
            $type       = (string)($row['item_type'] ?? $row['type'] ?? 'Item');
            $sellerUsername = (string)($row['seller_username'] ?? 'Seller');
            $sellerId   = (int)($row['seller_id'] ?? 0);
            $clientUsername = (string)($row['client_username'] ?? 'Client');
            $clientId   = (int)($row['client_id'] ?? 0);
            $images     = $row['item_images'] ?? $row['images'] ?? [];
            if (is_string($images)) { $tmp = json_decode($images, true); $images = is_array($tmp) ? $tmp : []; }
            $cover      = !empty($images[0]) ? (string)$images[0] : (defined('ASSET_URL') ? ASSET_URL . '/public/uploads/icons/default2.png' : '');
            $createdTs  = !empty($row['created_at']) ? strtotime($row['created_at']) : 0;
            $createdFmt = $createdTs ? date('d.m.Y H:i', $createdTs) : '—';
          ?>
          <tr class="tb-row"
              data-status="<?= htmlspecialchars($status) ?>"
              data-search="<?= htmlspecialchars(strtolower($title . ' ' . $sellerUsername . ' ' . $clientUsername . ' ' . $server . ' ' . $type)) ?>"
              data-id="<?= $orderId ?>"
              data-price="<?= $price ?>"
              data-date="<?= $createdTs ?>"
              onclick="window.location='<?= ADMN_URL ?>/item-order/<?= $orderId ?>'">
            <td><span class="tb-id">#<?= $orderId ?></span></td>
            <td>
              <div class="tb-item">
                <img class="tb-item-img" src="<?= htmlspecialchars($cover) ?>" alt="">
                <div>
                  <div class="tb-item-name"><?= htmlspecialchars($title) ?></div>
                  <div class="tb-item-sub"><?= htmlspecialchars($server ?: ucwords(str_replace(['-','_'], ' ', $type))) ?></div>
                </div>
              </div>
            </td>
            <td onclick="event.stopPropagation()">
              <?php if ($sellerId): ?>
                <a class="tb-user-link" href="<?= BASE_URL ?>/admin-area/seller/<?= $sellerId ?>"><?= htmlspecialchars($sellerUsername) ?></a>
              <?php else: ?><?= htmlspecialchars($sellerUsername) ?><?php endif; ?>
            </td>
            <td onclick="event.stopPropagation()">
              <?php if ($clientId): ?>
                <a class="tb-user-link" href="<?= BASE_URL ?>/admin-area/client/<?= $clientId ?>"><?= htmlspecialchars($clientUsername) ?></a>
              <?php else: ?><?= htmlspecialchars($clientUsername) ?><?php endif; ?>
            </td>
            <td><span class="tb-money">€<?= number_format($price, 2) ?></span></td>
            <td><span class="tb-badge <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span></td>
            <td><span class="tb-date"><?= $createdFmt ?></span></td>
            <td class="text-end" onclick="event.stopPropagation()">
              <a class="tb-view" href="<?= ADMN_URL ?>/item-order/<?= $orderId ?>"><i class="fa-solid fa-eye"></i> View</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8"><div class="tb-empty"><i class="fa-duotone fa-box-open"></i><div>No item orders found.</div></div></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tb-footer">
    <div style="font-size:.8rem;color:rgba(255,255,255,.45)">Showing <span id="tbShowing">0</span> of <span id="tbTotal"><?= count($orders) ?></span> orders</div>
    <div id="tbPagination" style="display:flex;gap:6px;align-items:center"></div>
  </div>
</div>

<?php echo $this->start('scripts'); ?>
<script>
(function(){
  var PER_PAGE = 25, filter = 'all', search = '', page = 1, sortCol = 'id', sortDir = 'desc';
  var tbody = document.getElementById('tbTbody');
  var allRows = tbody ? Array.from(tbody.querySelectorAll('.tb-row')) : [];
  var showEl = document.getElementById('tbShowing'), totEl = document.getElementById('tbTotal'), pageEl = document.getElementById('tbPagination'), srchEl = document.getElementById('tbSearch');
  var pills = document.querySelectorAll('.tb-pill'), ths = document.querySelectorAll('.tb-table thead th.sortable');

  function getSorted(arr){ return arr.slice().sort(function(a,b){ var av=a.dataset[sortCol]||'',bv=b.dataset[sortCol]||''; var an=parseFloat(av),bn=parseFloat(bv); var cmp=(isNaN(an)||isNaN(bn))?String(av).localeCompare(String(bv)):an-bn; return sortDir==='asc'?cmp:-cmp; }); }
  function getFiltered(){ return allRows.filter(function(row){ var okStatus=filter==='all'||row.dataset.status===filter; var okSearch=!search||(row.dataset.search||'').indexOf(search)!==-1; return okStatus&&okSearch; }); }
  function render(){
    var filtered=getSorted(getFiltered()), total=filtered.length, pages=Math.max(1,Math.ceil(total/PER_PAGE));
    if(page>pages) page=pages;
    var start=(page-1)*PER_PAGE, end=start+PER_PAGE;
    allRows.forEach(function(row){ row.style.display='none'; });
    filtered.slice(start,end).forEach(function(row){ tbody.appendChild(row); row.style.display=''; });
    if(showEl) showEl.textContent=total>0?(start+1)+'–'+Math.min(end,total):'0';
    if(totEl) totEl.textContent=total;
    ths.forEach(function(th){ th.classList.remove('th-sort-asc','th-sort-desc'); if(th.dataset.col===sortCol) th.classList.add('th-sort-'+sortDir); });
    if(pageEl){
      pageEl.innerHTML='';
      if(pages>1){
        function btn(label,p,disabled,active){ var b=document.createElement('button'); b.className='tb-page-btn'+(active?' active':''); b.innerHTML=label; b.disabled=!!disabled; if(!disabled) b.addEventListener('click',function(){ page=p; render(); }); return b; }
        pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>',page-1,page===1,false));
        for(var i=1;i<=pages;i++){
          if(pages>7&&i>2&&i<pages-1&&Math.abs(i-page)>1){ if(i===3||i===pages-2){ var d=document.createElement('span'); d.style.cssText='color:rgba(255,255,255,.3);padding:0 4px;line-height:32px'; d.textContent='…'; pageEl.appendChild(d); } continue; }
          pageEl.appendChild(btn(i,i,false,i===page));
        }
        pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>',page+1,page===pages,false));
      }
    }
  }
  pills.forEach(function(pill){ pill.addEventListener('click',function(){ pills.forEach(function(x){x.classList.remove('active');}); pill.classList.add('active'); filter=pill.dataset.status; page=1; render(); }); });
  if(srchEl) srchEl.addEventListener('input',function(){ search=srchEl.value.trim().toLowerCase(); page=1; render(); });
  ths.forEach(function(th){ th.addEventListener('click',function(){ var col=th.dataset.col; if(sortCol===col) sortDir=sortDir==='asc'?'desc':'asc'; else{sortCol=col;sortDir='desc';} page=1; render(); }); });
  render();
})();
</script>
<?php echo $this->end(); ?>
