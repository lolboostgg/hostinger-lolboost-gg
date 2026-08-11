<?php
/**
 * Shared marketplace-style listing table for the seller admin tabs.
 *
 * Expects (defined before the include):
 *   $lstCfg  array  id, title, icon, searchPlaceholder, emptyText,
 *                   filterLabel (Game/Category, '' hides it), statuses (label list),
 *                   columns (subset of: game, region, stock, sold)
 *   $lstRows array  rows built by the calling page (see keys used below)
 */
$lstCfg = is_array($lstCfg ?? null) ? $lstCfg : [];
$lstRows = is_array($lstRows ?? null) ? array_values($lstRows) : [];
$lstId = preg_replace('/[^a-z0-9_]/', '', strtolower((string)($lstCfg['id'] ?? 'listings')));
$lstColumns = (array)($lstCfg['columns'] ?? ['game', 'stock', 'sold']);
$lstStatuses = (array)($lstCfg['statuses'] ?? ['All', 'Listed', 'Unlisted']);
$lstFilterLabel = (string)($lstCfg['filterLabel'] ?? 'Game');
$lstFilters = [];
foreach ($lstRows as $row) {
    $key = (string)($row['filter_key'] ?? '');
    if ($key === '' || isset($lstFilters[$key])) continue;
    $lstFilters[$key] = ['key' => $key, 'label' => (string)($row['filter_label'] ?? $key), 'icon' => (string)($row['filter_icon'] ?? '')];
}
uasort($lstFilters, fn($a, $b) => strcasecmp($a['label'], $b['label']));
?>
<style>
.slt{color:#fff}
.slt-toolbar{min-height:58px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border:1px solid rgba(255,255,255,.08);border-radius:16px;background:#25282a;box-shadow:0 2px 14px rgba(0,0,0,.14)}
.slt-filter-stack{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.slt-filter-group{display:flex;align-items:center;gap:7px}
.slt-filter-label{color:rgba(255,255,255,.35);font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}
.slt-pills{display:flex;gap:7px;flex-wrap:wrap}
.slt-pill{height:31px;padding:0 13px;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.045);color:rgba(255,255,255,.62);font-size:11px;font-weight:850;cursor:pointer;display:inline-flex;align-items:center;gap:7px}
.slt-pill:hover{color:#fff;border-color:rgba(139,124,255,.35)}
.slt-pill.active{background:rgba(109,92,255,.2);border-color:rgba(139,124,255,.48);color:#cfc8ff}
.slt-pill img{width:16px;height:16px;border-radius:5px;object-fit:cover}
.slt-search{position:relative}
.slt-search i{position:absolute;left:12px;top:11px;color:rgba(255,255,255,.3);font-size:12px}
.slt-search input{width:220px;height:36px;padding:0 12px 0 35px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.035);color:#fff;outline:0;font-size:12px}
.slt-table-wrap{overflow:auto;border:1px solid rgba(255,255,255,.075);border-radius:16px;background:#25282a;box-shadow:0 2px 16px rgba(0,0,0,.15)}
.slt-table{width:100%;min-width:900px;border-collapse:collapse}
.slt-table th{height:42px;padding:0 14px;text-align:left;color:rgba(255,255,255,.38);font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.055em;border-bottom:1px solid rgba(255,255,255,.075);background:rgba(255,255,255,.015);white-space:nowrap}
.slt-table td{height:66px;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.05);font-size:12px;color:rgba(255,255,255,.7)}
.slt-table th.slt-right,.slt-table td.slt-right{text-align:right}
.slt-row:hover{background:rgba(255,255,255,.025)}
.slt-row[data-url]{cursor:pointer}
.slt-product{display:flex;align-items:center;gap:11px;min-width:250px}
.slt-product-img,.slt-product-fallback{width:36px;height:36px;border-radius:10px;object-fit:cover;background:rgba(109,92,255,.13);border:1px solid rgba(255,255,255,.09);flex:0 0 36px}
.slt-product-fallback{display:grid;place-items:center;color:#a99cff}
.slt-title{font-size:13px;font-weight:900;color:#fff;line-height:1.25}
.slt-meta{max-width:250px;margin-top:3px;color:rgba(255,255,255,.36);font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.slt-tag{display:inline-flex;align-items:center;gap:8px;white-space:nowrap}
.slt-tag img{width:22px;height:22px;border-radius:6px;object-fit:cover;flex:0 0 22px}
.slt-tag-fallback{width:22px;height:22px;border-radius:6px;background:rgba(255,255,255,.06);display:inline-grid;place-items:center;flex:0 0 22px;color:rgba(255,255,255,.4);font-size:10px}
.slt-money{font-weight:900;color:#fff!important}
.slt-status{display:inline-flex;align-items:center;gap:6px;height:28px;padding:0 10px;border-radius:999px;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
.slt-status:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
.slt-status--listed{color:#00c9a7;background:rgba(0,201,167,.12);border:1px solid rgba(0,201,167,.28)}
.slt-status--unlisted{color:#ffc44d;background:rgba(255,196,77,.10);border:1px solid rgba(255,196,77,.22)}
.slt-status--sold{color:#b18cff;background:rgba(177,140,255,.10);border:1px solid rgba(177,140,255,.22)}
.slt-status--refunded{color:#ff8a4c;background:rgba(255,138,76,.10);border:1px solid rgba(255,138,76,.22)}
.slt-btn{height:34px;padding:0 14px;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:11px;font-weight:900;text-decoration:none;cursor:pointer;border:1px solid transparent}
.slt-btn[disabled]{opacity:.55;cursor:default}
.slt-btn--unlist{border-color:rgba(245,158,11,.3);color:#fbbf24;background:rgba(245,158,11,.1)}
.slt-btn--unlist:hover{background:rgba(245,158,11,.17)}
.slt-btn--list{border-color:rgba(34,197,94,.3);color:#4ade80;background:rgba(34,197,94,.1)}
.slt-btn--list:hover{background:rgba(34,197,94,.17)}
.slt-btn--view{color:#fff;background:linear-gradient(135deg,#8b5cf6,#3b82f6);box-shadow:0 7px 18px rgba(99,102,241,.2)}
.slt-footer{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding-top:14px;color:rgba(255,255,255,.38);font-size:11px}
.slt-perpage{height:30px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:11px;padding:0 6px}
.slt-pages{display:flex;gap:5px;flex-wrap:wrap}
.slt-page{min-width:32px;height:32px;padding:0 8px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:11px;font-weight:800;cursor:pointer}
.slt-page:hover{color:#fff}
.slt-page.active{background:rgba(109,92,255,.25);border-color:rgba(139,124,255,.45);color:#fff}
.slt-page[disabled]{opacity:.35;cursor:default}
.slt-empty{text-align:center!important;padding:54px!important;color:rgba(255,255,255,.35)}
</style>

<div class="slt" id="slt_<?= $lstId ?>">
  <div class="slt-toolbar">
    <div class="slt-filter-stack">
      <?php if ($lstFilterLabel !== '' && $lstFilters): ?>
      <div class="slt-filter-group">
        <span class="slt-filter-label"><?= htmlspecialchars($lstFilterLabel, ENT_QUOTES) ?></span>
        <div class="slt-pills" data-role="filter">
          <button type="button" class="slt-pill active" data-filter=""><i class="fa-duotone fa-layer-group"></i>All</button>
          <?php foreach ($lstFilters as $f): ?>
          <button type="button" class="slt-pill" data-filter="<?= htmlspecialchars($f['key'], ENT_QUOTES) ?>">
            <?php if ($f['icon'] !== '' && ($lstCfg['filterIcons'] ?? true)): ?><img src="<?= htmlspecialchars($f['icon'], ENT_QUOTES) ?>" alt=""><?php endif; ?>
            <?= htmlspecialchars($f['label'], ENT_QUOTES) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="slt-filter-group">
        <span class="slt-filter-label">Status</span>
        <div class="slt-pills" data-role="status">
          <?php foreach ($lstStatuses as $status): ?>
          <button type="button" class="slt-pill<?= $status === 'All' ? ' active' : '' ?>" data-status="<?= htmlspecialchars($status, ENT_QUOTES) ?>"><?= htmlspecialchars($status, ENT_QUOTES) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="slt-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" data-role="search" placeholder="<?= htmlspecialchars((string)($lstCfg['searchPlaceholder'] ?? 'Search…'), ENT_QUOTES) ?>"></div>
  </div>

  <div class="slt-table-wrap">
    <table class="slt-table">
      <thead><tr>
        <th>#</th>
        <th><?= htmlspecialchars((string)($lstCfg['title'] ?? 'Listing'), ENT_QUOTES) ?></th>
        <?php if (in_array('game', $lstColumns, true)): ?><th><?= htmlspecialchars($lstFilterLabel ?: 'Game', ENT_QUOTES) ?></th><?php endif; ?>
        <?php if (in_array('region', $lstColumns, true)): ?><th>Region</th><?php endif; ?>
        <th class="slt-right">Price</th>
        <?php if (in_array('stock', $lstColumns, true)): ?><th>Stock</th><?php endif; ?>
        <?php if (in_array('sold', $lstColumns, true)): ?><th>Sold</th><?php endif; ?>
        <th>Status</th>
        <th>Listed</th>
        <th></th>
      </tr></thead>
      <tbody data-role="body"></tbody>
    </table>
  </div>

  <div class="slt-footer">
    <span>Showing <b data-role="showing">0</b> of <b data-role="total">0</b> · <select class="slt-perpage" data-role="perpage"><option>10</option><option selected>25</option><option>50</option><option>100</option></select> per page</span>
    <div class="slt-pages" data-role="pages"></div>
  </div>
</div>

<script>
(function(){
  var root=document.getElementById('slt_<?= $lstId ?>');
  if(!root)return;
  var rows=<?= json_encode($lstRows, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  var cols=<?= json_encode(array_values($lstColumns)) ?>;
  var fallbackIcon=<?= json_encode((string)($lstCfg['icon'] ?? 'fa-box-open')) ?>;
  var emptyText=<?= json_encode((string)($lstCfg['emptyText'] ?? 'No entries found.')) ?>;
  var ajaxUrl=<?= json_encode(AJAX_URL) ?>;
  var body=root.querySelector('[data-role="body"]'), pages=root.querySelector('[data-role="pages"]');
  var showing=root.querySelector('[data-role="showing"]'), total=root.querySelector('[data-role="total"]');
  var filter='', status='All', query='', page=1, perPage=25;
  var colCount=3+cols.length+3;
  function has(c){return cols.indexOf(c)!==-1}
  function esc(v){var d=document.createElement('div');d.textContent=String(v==null?'':v);return d.innerHTML}
  function money(v){return '€'+(Number(v||0)/100).toFixed(2)}
  function dateOnly(v){if(!v)return '—';var m=String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);return m?m[3]+'.'+m[2]+'.'+m[1]:'—'}
  var tagIcon=<?= json_encode((string)($lstCfg['tagIcon'] ?? 'fa-gamepad')) ?>;
  function tag(label,icon){if(!label)return '—';var img=icon?'<img src="'+esc(icon)+'" alt="">':'<span class="slt-tag-fallback"><i class="fa-duotone '+tagIcon+'"></i></span>';return '<span class="slt-tag">'+img+esc(label)+'</span>'}
  function action(r){
    if(r.toggle){var listed=r.status==='listed';
      return '<button type="button" class="slt-btn '+(listed?'slt-btn--unlist':'slt-btn--list')+' js-slt-toggle" data-id="'+esc(r.id)+'"><i class="fa-solid '+(listed?'fa-eye-slash':'fa-eye')+'"></i>'+(listed?'Unlist':'List')+'</button>';}
    if(r.url)return '<a class="slt-btn slt-btn--view" href="'+esc(r.url)+'"><i class="fa-solid fa-eye"></i>View</a>';
    return '—';
  }
  function render(){
    var filtered=rows.filter(function(r){
      var fOk=!filter||String(r.filter_key||'')===filter;
      var sOk=status==='All'||String(r.status||'').toLowerCase()===status.toLowerCase();
      var hay=(r.id+' '+(r.title||'')+' '+(r.subtitle||'')+' '+(r.filter_label||'')+' '+(r.region||'')+' '+(r.status||'')).toLowerCase();
      return fOk&&sOk&&(!query||hay.indexOf(query)!==-1);
    });
    var max=Math.max(1,Math.ceil(filtered.length/perPage));
    if(page>max)page=max;
    var start=(page-1)*perPage, slice=filtered.slice(start,start+perPage);
    body.innerHTML=slice.length?slice.map(function(r){
      var img=r.image?'<img class="slt-product-img" src="'+esc(r.image)+'" alt="">':'<span class="slt-product-fallback"><i class="fa-duotone '+fallbackIcon+'"></i></span>';
      var html='<tr class="slt-row" data-id="'+esc(r.id)+'"'+(r.url?' data-url="'+esc(r.url)+'"':'')+'>';
      html+='<td>#'+esc(r.id)+'</td>';
      html+='<td><div class="slt-product">'+img+'<div><div class="slt-title">'+esc(r.title)+'</div><div class="slt-meta">'+esc(r.subtitle||'')+'</div></div></div></td>';
      if(has('game'))html+='<td>'+tag(r.filter_label,r.filter_icon)+'</td>';
      if(has('region'))html+='<td class="text-uppercase">'+esc(r.region||'—')+'</td>';
      html+='<td class="slt-right slt-money">'+money(r.price)+'</td>';
      if(has('stock'))html+='<td>'+esc(r.stock==null?'—':r.stock)+'</td>';
      if(has('sold'))html+='<td>'+esc(r.sold==null?'—':r.sold)+'</td>';
      html+='<td><span class="slt-status slt-status--'+esc(r.status)+'">'+esc(r.status_label||r.status)+'</span></td>';
      html+='<td>'+dateOnly(r.created)+'</td>';
      html+='<td>'+action(r)+'</td></tr>';
      return html;
    }).join(''):'<tr><td colspan="'+colCount+'" class="slt-empty">'+esc(emptyText)+'</td></tr>';

    body.querySelectorAll('tr[data-url]').forEach(function(tr){tr.onclick=function(e){if(!e.target.closest('a,button'))location.href=tr.dataset.url}});
    body.querySelectorAll('.js-slt-toggle').forEach(function(btn){btn.onclick=function(e){
      e.preventDefault();e.stopPropagation();
      var row=rows.find(function(r){return String(r.id)===String(btn.dataset.id)});
      if(!row||!row.toggle)return;
      var makeActive=row.status!=='listed';
      btn.disabled=true;
      var fd=new FormData();
      Object.keys(row.toggle).forEach(function(k){fd.set(k,row.toggle[k])});
      if(Object.prototype.hasOwnProperty.call(row.toggle,'active'))fd.set('active',makeActive?'1':'0');
      fetch(ajaxUrl,{method:'POST',body:fd,credentials:'same-origin'})
        .then(function(res){return res.json()})
        .then(function(d){
          var ok=d&&(d.success===true||(d.sendToast&&d.sendToast.type==='success'));
          if(!ok)throw new Error(d&&d.sendToast?d.sendToast.message:'Could not update listing.');
          row.status=makeActive?'listed':'unlisted';row.status_label=makeActive?'Listed':'Unlisted';
          render();
          if(typeof create_toast==='function')create_toast((d.sendToast&&d.sendToast.type)||'success',(d.sendToast&&d.sendToast.title)||(makeActive?'Listed':'Unlisted'),(d.sendToast&&d.sendToast.message)||'Listing updated.');
        })
        .catch(function(err){if(typeof create_toast==='function')create_toast('danger','Error',err.message);else alert(err.message)})
        .finally(function(){btn.disabled=false});
    }});

    showing.textContent=filtered.length?(start+1)+'–'+Math.min(start+perPage,filtered.length):'0';
    total.textContent=filtered.length;
    pages.innerHTML='';
    function pageBtn(label,target,opts){
      var b=document.createElement('button');b.className='slt-page'+(opts&&opts.active?' active':'');b.innerHTML=label;
      if(opts&&opts.disabled)b.disabled=true;else b.onclick=function(){page=target;render()};
      pages.appendChild(b);
    }
    if(max>1){
      pageBtn('<i class="fa-solid fa-chevron-left"></i>',page-1,{disabled:page===1});
      for(var i=1;i<=max;i++){
        if(max>9&&Math.abs(i-page)>2&&i!==1&&i!==max)continue;
        pageBtn(String(i),i,{active:i===page});
      }
      pageBtn('<i class="fa-solid fa-chevron-right"></i>',page+1,{disabled:page===max});
    }
  }
  root.querySelectorAll('[data-role="filter"] .slt-pill').forEach(function(btn){btn.onclick=function(){
    root.querySelectorAll('[data-role="filter"] .slt-pill').forEach(function(x){x.classList.remove('active')});
    btn.classList.add('active');filter=btn.dataset.filter;page=1;render();
  }});
  root.querySelectorAll('[data-role="status"] .slt-pill').forEach(function(btn){btn.onclick=function(){
    root.querySelectorAll('[data-role="status"] .slt-pill').forEach(function(x){x.classList.remove('active')});
    btn.classList.add('active');status=btn.dataset.status;page=1;render();
  }});
  root.querySelector('[data-role="search"]').oninput=function(){query=this.value.trim().toLowerCase();page=1;render()};
  root.querySelector('[data-role="perpage"]').onchange=function(){perPage=parseInt(this.value,10)||25;page=1;render()};
  render();
})();
</script>
