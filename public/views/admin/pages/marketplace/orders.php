<?= $this->layout('admin/layouts/main', ['meta' => $meta ?? ['title' => 'Marketplace Orders | Admin Area']]) ?>
<?php
$type = (string)($marketplaceType ?? 'items');
$cfg = [
    'items' => ['title'=>'Item Orders','icon'=>'fa-box-open','noun'=>'item','listing_url'=>ADMN_URL.'/item/','order_url'=>ADMN_URL.'/item-order/'],
    'topups' => ['title'=>'Top Up Orders','icon'=>'fa-coins','noun'=>'top up','listing_url'=>'','order_url'=>ADMN_URL.'/top-up-order/'],
    'digital' => ['title'=>'Digital Good Orders','icon'=>'fa-gem','noun'=>'digital good','listing_url'=>ADMN_URL.'/digital-goods/listings/','listing_suffix'=>'/edit','order_url'=>ADMN_URL.'/digital-good-order/'],
][$type] ?? ['title'=>'Marketplace Orders','icon'=>'fa-store','noun'=>'listing','listing_url'=>'','order_url'=>''];
$normalizeImage = static function ($path): string {
    $path = trim((string)($path ?? ''));
    if ($path === '') return '';
    if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
    $path = preg_replace('#^/public/assets#i', '', $path);
    $path = '/' . ltrim((string)$path, '/');
    return defined('ASSET_URL') ? rtrim(ASSET_URL, '/') . $path : $path;
};
$normalizeAvatar = static function ($path, string $name = '', string $side = 'seller'): string {
    $path = trim((string)($path ?? ''));
    $iconBase = defined('ICON_URL') ? rtrim((string)ICON_URL, '/') : 'https://lolboost.gg/public/uploads/icons';
    if ($path === '' && $side === 'seller' && strtolower(trim($name)) === 'lolboost.gg') {
        return $iconBase . '/03ce541a1f4bf8b06c924439ffcc8173.png';
    }
    if ($path === '') return '';
    if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
    if (str_contains($path, '/')) {
        return defined('BASE_URL') ? rtrim((string)BASE_URL, '/') . '/' . ltrim($path, '/') : '/' . ltrim($path, '/');
    }
    return $iconBase . '/' . ltrim($path, '/');
};

$rows = [];
foreach ((array)($listings ?? []) as $row) {
    $id = (int)($row['id'] ?? 0);
    $title = (string)($row['title'] ?? $row['offer_title'] ?? ucfirst($cfg['noun']));
    $subtitle = (string)($row['category_name'] ?? $row['game_name'] ?? $row['db_game_name'] ?? $row['brand'] ?? $row['region'] ?? '');
    $image = (string)($row['image'] ?? $row['brand_icon'] ?? '');
    if ($image === '' && !empty($row['images'])) {
        $images = is_array($row['images']) ? $row['images'] : json_decode((string)$row['images'], true);
        $image = is_array($images) && !empty($images[0]) ? (string)$images[0] : '';
    }
    $image = $normalizeImage($image);
    $active = (int)($row['active'] ?? 1) === 1;
    $url = $cfg['listing_url'] !== '' ? $cfg['listing_url'].$id.($cfg['listing_suffix'] ?? '') : '';
    $rows[] = [
        'id'=>$id,'kind'=>'Listing','title'=>$title,'subtitle'=>$subtitle,'image'=>$image,
        'seller'=>(string)($row['seller_username'] ?? 'Seller'),'seller_id'=>(int)($row['seller_id'] ?? 0),'seller_icon'=>$normalizeAvatar($row['seller_icon'] ?? '', (string)($row['seller_username'] ?? ''), 'seller'),
        'buyer'=>'—','buyer_id'=>0,'buyer_icon'=>'',
        'price'=>(int)($row['price'] ?? 0),'status'=>$active ? 'Listed' : 'Unlisted',
        'raw_status'=>$active ? 'LISTED' : 'UNLISTED','sold'=>(int)($row['sold_count'] ?? 0),
        'created'=>(string)($row['created_at'] ?? ''),'sold_at'=>'','url'=>$url,
        'listing_kind'=>$type === 'digital' ? 'digital_good' : ($type === 'topups' ? 'topup' : 'item'),
    ];
}
foreach ((array)($orders ?? []) as $row) {
    $id = (int)($row['id'] ?? 0);
    $rawStatus = strtoupper(trim((string)($row['status'] ?? '')));
    $statusMap = [
        'UNPAID'=>'Unpaid', 'PENDING'=>'Unpaid', 'PAID'=>'Processing',
        'PROCESSING'=>'Processing', 'DELIVERED'=>'Delivered',
        'COMPLETED'=>'Completed', 'CANCELLED'=>'Cancelled',
        'CANCELED'=>'Cancelled', 'REFUNDED'=>'Refunded', 'SOLD'=>'Sold',
        'WAITING_FRIENDSHIP'=>'Waiting Friendship', 'FRIENDSHIP_PENDING'=>'Waiting Friendship',
    ];
    $status = $statusMap[$rawStatus] ?? ($rawStatus !== '' ? ucwords(strtolower(str_replace('_', ' ', $rawStatus))) : 'Unpaid');
    $title = (string)($row['item_title'] ?? $row['offer_title'] ?? $row['listing_offer_title'] ?? ucfirst($cfg['noun']).' Order');
    $subtitle = (string)($row['brand'] ?? $row['game_name'] ?? $row['db_game_name'] ?? $row['region'] ?? '');
    $image = (string)($row['image'] ?? $row['brand_icon'] ?? '');
    if ($image === '' && !empty($row['item_images'])) {
        $images = is_array($row['item_images']) ? $row['item_images'] : json_decode((string)$row['item_images'], true);
        $image = is_array($images) && !empty($images[0]) ? (string)$images[0] : '';
    }
    $image = $normalizeImage($image);
    $rows[] = [
        'id'=>$id,'kind'=>'Order','title'=>$title,'subtitle'=>$subtitle,'image'=>$image,
        'seller'=>(string)($row['seller_username'] ?? 'Seller'),'seller_id'=>(int)($row['seller_id'] ?? 0),'seller_icon'=>$normalizeAvatar($row['seller_icon'] ?? '', (string)($row['seller_username'] ?? ''), 'seller'),
        'buyer'=>(string)($row['client_username'] ?? $row['client_email'] ?? 'Client'),'buyer_id'=>(int)($row['client_id'] ?? 0),'buyer_icon'=>$normalizeAvatar($row['client_icon'] ?? '', (string)($row['client_username'] ?? ''), 'client'),
        'price'=>(int)($row['price'] ?? 0),'status'=>$status,'raw_status'=>$rawStatus,
        'sold'=>(int)($row['quantity'] ?? 1),'created'=>(string)($row['created_at'] ?? ''),
        'sold_at'=>(string)($row['sold_at'] ?? $row['paid_at'] ?? $row['created_at'] ?? ''),
        'url'=>$cfg['order_url'] !== '' ? $cfg['order_url'].$id : '',
        'delete_kind'=>$type === 'digital' ? 'digital_good' : ($type === 'topups' ? 'topup' : 'item'),
    ];
}
?>

<?= $this->start('styles') ?>
<style>
.mpo{color:#fff}.mpo-hero{min-height:84px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border:1px solid rgba(255,255,255,.08);border-radius:20px;background:#25282a;box-shadow:0 2px 16px rgba(0,0,0,.18)}.mpo-hero-left{display:flex;align-items:center;gap:14px}.mpo-icon{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;background:linear-gradient(145deg,rgba(109,92,255,.28),rgba(139,92,246,.16));border:1px solid rgba(139,124,255,.3);color:#c4b5fd;font-size:19px}.mpo h1{margin:0;font-size:18px;font-weight:900;letter-spacing:-.01em}.mpo-sub{margin-top:3px;color:rgba(255,255,255,.42);font-size:12px}.mpo-toolbar{min-height:58px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border:1px solid rgba(255,255,255,.08);border-radius:16px;background:#25282a;box-shadow:0 2px 14px rgba(0,0,0,.14)}.mpo-pills{display:flex;gap:7px;flex-wrap:wrap}.mpo-pill{height:31px;padding:0 13px;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.045);color:rgba(255,255,255,.62);font-size:11px;font-weight:850;cursor:pointer}.mpo-pill:hover{color:#fff;border-color:rgba(139,124,255,.35)}.mpo-pill.active{background:rgba(109,92,255,.2);border-color:rgba(139,124,255,.48);color:#cfc8ff}.mpo-pill[data-status="Sold"].active{background:rgba(244,63,94,.13);border-color:rgba(251,113,133,.35);color:#fb7185}.mpo-search{position:relative}.mpo-search i{position:absolute;left:12px;top:11px;color:rgba(255,255,255,.3);font-size:12px}.mpo-search input{width:220px;height:36px;padding:0 12px 0 35px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.035);color:#fff;outline:0;font-size:12px}.mpo-table-wrap{overflow:auto;border:1px solid rgba(255,255,255,.075);border-radius:16px;background:#25282a;box-shadow:0 2px 16px rgba(0,0,0,.15)}.mpo-table{width:100%;min-width:1080px;border-collapse:collapse}.mpo-table th{height:42px;padding:0 14px;text-align:left;color:rgba(255,255,255,.38);font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.055em;border-bottom:1px solid rgba(255,255,255,.075);background:rgba(255,255,255,.015)}.mpo-table td{height:66px;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.05);font-size:12px;color:rgba(255,255,255,.7)}.mpo-row{cursor:pointer}.mpo-row:hover{background:rgba(255,255,255,.025)}.mpo-product{display:flex;align-items:center;gap:11px;min-width:270px}.mpo-product-img,.mpo-product-fallback{width:36px;height:36px;border-radius:10px;object-fit:cover;background:rgba(109,92,255,.13);border:1px solid rgba(255,255,255,.09);flex:0 0 36px}.mpo-product-fallback{display:grid;place-items:center;color:#a99cff}.mpo-title{font-size:13px;font-weight:900;color:#fff;line-height:1.25}.mpo-meta{max-width:250px;margin-top:3px;color:rgba(255,255,255,.36);font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.mpo-status{display:inline-flex;align-items:center;height:28px;padding:0 10px;border-radius:999px;font-size:10px;font-weight:900}.mpo-status--active,.mpo-status--paid,.mpo-status--completed{color:#4ade80;background:rgba(34,197,94,.1);border:1px solid rgba(74,222,128,.22)}.mpo-status--unlisted,.mpo-status--unpaid{color:#b5bac7;background:rgba(148,163,184,.09);border:1px solid rgba(148,163,184,.15)}.mpo-status--sold,.mpo-status--cancelled{color:#fb7185;background:rgba(244,63,94,.11);border:1px solid rgba(251,113,133,.3)}.mpo-status--refunded,.mpo-status--processing{color:#fbbf24;background:rgba(245,158,11,.1);border:1px solid rgba(251,191,36,.22)}.mpo-status--delivered{color:#60a5fa;background:rgba(59,130,246,.1);border:1px solid rgba(96,165,250,.22)}.mpo-raw{display:block;margin-top:3px;color:rgba(255,255,255,.25);font-size:8px;text-align:center}.mpo-money{font-weight:900;color:#fff!important}.mpo-seller{color:#b9a6ff!important;font-weight:850}.mpo-buyer{color:#36e986!important;font-weight:850}.mpo-view{height:34px;padding:0 14px;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;gap:6px;color:#fff;text-decoration:none;font-size:11px;font-weight:900;background:linear-gradient(135deg,#8b5cf6,#3b82f6);box-shadow:0 7px 18px rgba(99,102,241,.2)}.mpo-footer{display:flex;justify-content:space-between;align-items:center;padding-top:14px;color:rgba(255,255,255,.38);font-size:11px}.mpo-pages{display:flex;gap:5px}.mpo-page{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7)}.mpo-page.active{background:rgba(109,92,255,.25);border-color:rgba(139,124,255,.45);color:#fff}.mpo-empty{text-align:center!important;padding:54px!important;color:rgba(255,255,255,.35)}
.mpo-status{gap:6px;text-transform:uppercase;letter-spacing:.05em}.mpo-status:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}.mpo-status--active{color:#00c9a7;background:rgba(0,201,167,.12);border:1px solid rgba(0,201,167,.28)}.mpo-status--unpaid{color:#ff6b6b;background:rgba(255,107,107,.10);border:1px solid rgba(255,107,107,.20)}.mpo-status--paid,.mpo-status--processing{color:#b18cff;background:rgba(177,140,255,.10);border:1px solid rgba(177,140,255,.22)}.mpo-status--completed{color:#1fe6c6;background:rgba(31,230,198,.10);border:1px solid rgba(31,230,198,.22)}.mpo-status--delivered{color:#4ea1ff;background:rgba(78,161,255,.12);border:1px solid rgba(78,161,255,.25)}.mpo-status--refunded{color:#ff8a4c;background:rgba(255,138,76,.10);border:1px solid rgba(255,138,76,.22)}.mpo-status--sold,.mpo-status--cancelled{color:#fb7185;background:rgba(244,63,94,.11);border:1px solid rgba(251,113,133,.3)}.mpo-status--unlisted{color:#ffc44d;background:rgba(255,196,77,.10);border:1px solid rgba(255,196,77,.22)}.mpo-status-delete{margin-left:2px;padding:0;border:0;background:transparent;color:inherit;cursor:pointer;opacity:.65}.mpo-status-delete:hover{opacity:1}
.mpo-status--listed{color:#00c9a7;background:rgba(0,201,167,.12);border:1px solid rgba(0,201,167,.28)}
.mpo-person{display:inline-flex;align-items:center;gap:7px;color:inherit;text-decoration:none}.mpo-person:hover{color:#fff}.mpo-person img,.mpo-person-fallback{width:24px;height:24px;border-radius:7px;object-fit:cover;display:inline-grid;place-items:center;background:#191b1d;border:1px solid rgba(255,255,255,.09);font-size:10px}.mpo-unlist{height:34px;padding:0 14px;border-radius:11px;border:1px solid rgba(245,158,11,.3);display:inline-flex;align-items:center;justify-content:center;gap:6px;color:#fbbf24;font-size:11px;font-weight:900;background:rgba(245,158,11,.1);cursor:pointer}.mpo-unlist:hover{background:rgba(245,158,11,.17)}
.mpo-list{height:34px;padding:0 14px;border-radius:11px;border:1px solid rgba(34,197,94,.3);display:inline-flex;align-items:center;justify-content:center;gap:6px;color:#4ade80;font-size:11px;font-weight:900;background:rgba(34,197,94,.1);cursor:pointer}.mpo-list:hover{background:rgba(34,197,94,.17)}.mpo-status--waiting-friendship{color:#fbbf24;background:rgba(245,158,11,.1);border:1px solid rgba(251,191,36,.24)}
.mpo-filter-stack{display:flex;align-items:center;gap:14px;flex-wrap:wrap}.mpo-filter-group{display:flex;align-items:center;gap:7px}.mpo-filter-label{color:rgba(255,255,255,.35);font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}
</style>
<?= $this->end() ?>

<div class="mpo">
  <div class="mpo-hero"><div class="mpo-hero-left"><div class="mpo-icon"><i class="fa-duotone <?= $cfg['icon'] ?>"></i></div><div><h1><?= htmlspecialchars($cfg['title']) ?></h1><div class="mpo-sub"><?= count($rows) ?> listings and orders loaded</div></div></div></div>
  <div class="mpo-toolbar">
    <div class="mpo-filter-stack">
      <div class="mpo-filter-group"><span class="mpo-filter-label">Type</span><div class="mpo-pills" id="mpoTypeFilters">
        <?php foreach (['All','Listing','Order'] as $label): ?><button class="mpo-pill<?= $label==='All'?' active':'' ?>" data-type="<?= $label ?>"><?= $label ?></button><?php endforeach; ?>
      </div></div>
      <div class="mpo-filter-group"><span class="mpo-filter-label">Status</span><div class="mpo-pills" id="mpoStatusFilters"></div></div>
    </div>
    <div class="mpo-search"><i class="fa-solid fa-magnifying-glass"></i><input id="mpoSearch" type="search" placeholder="Search <?= htmlspecialchars(strtolower($cfg['title'])) ?>…"></div>
  </div>
  <div class="mpo-table-wrap"><table class="mpo-table"><thead><tr><th>ID</th><th><?= ucfirst($cfg['noun']) ?></th><th>Type</th><th>Seller</th><th>Buyer</th><th>Price</th><th>Sales / Qty</th><th>Status</th><th>Created</th><th>Sold At</th><th></th></tr></thead><tbody id="mpoBody"></tbody></table></div>
  <div class="mpo-footer"><span>Showing <b id="mpoShowing">0</b> of <b id="mpoTotal">0</b></span><div class="mpo-pages" id="mpoPages"></div></div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var rows=<?= json_encode($rows, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>, typeFilter='All', statusFilter='All', query='', page=1, perPage=25;
  var body=document.getElementById('mpoBody'), pages=document.getElementById('mpoPages'), showing=document.getElementById('mpoShowing'), total=document.getElementById('mpoTotal');
  var statusBox=document.getElementById('mpoStatusFilters'), listingStatuses=['All','Listed','Unlisted'], orderStatuses=['All','Unpaid','Processing','Delivered','Completed','Cancelled','Refunded'];
  function esc(v){var d=document.createElement('div');d.textContent=String(v==null?'':v);return d.innerHTML}
  function money(v){return '€'+(Number(v||0)/100).toFixed(2)}
  function dateOnly(v){if(!v)return '—';var m=String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);return m?m[3]+'-'+m[2]+'-'+m[1].slice(-2):'—'}
  function person(r,side){var name=r[side],id=r[side+'_id'],icon=r[side+'_icon'],href=id?(side==='seller'?'<?= ADMN_URL ?>/seller/'+id+'/profile':'<?= ADMN_URL ?>/client/'+id):'';if(!id&&name==='—')return '—';var avatar=icon?'<img src="'+esc(icon)+'" alt="">':'<span class="mpo-person-fallback"><i class="fa-solid '+(side==='seller'?'fa-store':'fa-user')+'"></i></span>';var html=avatar+'<span>'+esc(name)+'</span>';return href?'<a class="mpo-person" href="'+href+'">'+html+'</a>':'<span class="mpo-person">'+html+'</span>'}
  function rebuildStatuses(){var labels=typeFilter==='Listing'?listingStatuses:(typeFilter==='Order'?orderStatuses:['All']);statusFilter='All';statusBox.innerHTML=labels.map(function(label){return '<button class="mpo-pill'+(label==='All'?' active':'')+'" data-status="'+label+'">'+label+'</button>'}).join('');statusBox.querySelectorAll('.mpo-pill').forEach(function(btn){btn.onclick=function(){statusBox.querySelectorAll('.mpo-pill').forEach(function(x){x.classList.remove('active')});btn.classList.add('active');statusFilter=btn.dataset.status;page=1;render()}})}
  function render(){
    var filtered=rows.filter(function(r){var typeOk=typeFilter==='All'||r.kind===typeFilter,statusOk=statusFilter==='All'||r.status===statusFilter;var hay=(r.id+' '+r.title+' '+r.subtitle+' '+r.seller+' '+r.buyer+' '+r.raw_status).toLowerCase();return typeOk&&statusOk&&(!query||hay.indexOf(query)!==-1)}).sort(function(a,b){return String(b.sold_at||'').localeCompare(String(a.sold_at||''))||b.id-a.id});
    var max=Math.max(1,Math.ceil(filtered.length/perPage));if(page>max)page=max;var start=(page-1)*perPage,slice=filtered.slice(start,start+perPage);
    body.innerHTML=slice.length?slice.map(function(r){var img=r.image?'<img class="mpo-product-img" src="'+esc(r.image)+'" alt="">':'<span class="mpo-product-fallback"><i class="fa-solid <?= $cfg['icon'] ?>"></i></span>';var action=r.kind==='Order'&&r.url?'<a class="mpo-view" href="'+esc(r.url)+'"><i class="fa-solid fa-eye"></i> View</a>':(r.kind==='Listing'?(r.raw_status==='LISTED'?'<button class="mpo-unlist js-mpo-listing-toggle" data-active="0" data-id="'+r.id+'" data-kind="'+esc(r.listing_kind)+'"><i class="fa-solid fa-eye-slash"></i> Unlist</button>':'<button class="mpo-list js-mpo-listing-toggle" data-active="1" data-id="'+r.id+'" data-kind="'+esc(r.listing_kind)+'"><i class="fa-solid fa-eye"></i> List</button>'):'—');var del=r.kind==='Order'&&r.raw_status==='UNPAID'?'<button type="button" class="mpo-status-delete js-mpo-delete" data-id="'+r.id+'" data-kind="'+esc(r.delete_kind)+'" title="Delete unpaid order"><i class="fa-duotone fa-trash-can"></i></button>':'';var statusClass=String(r.status).toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');return '<tr class="mpo-row"'+(r.kind==='Order'&&r.url?' data-url="'+esc(r.url)+'"':'')+'><td>#'+r.id+'</td><td><div class="mpo-product">'+img+'<div><div class="mpo-title">'+esc(r.title)+'</div><div class="mpo-meta">'+esc(r.subtitle)+'</div></div></div></td><td>'+esc(r.kind)+'</td><td class="mpo-seller">'+person(r,'seller')+'</td><td class="mpo-buyer">'+person(r,'buyer')+'</td><td class="mpo-money">'+money(r.price)+'</td><td>'+r.sold+'</td><td><span class="mpo-status mpo-status--'+statusClass+'">'+esc(r.status)+del+'</span></td><td>'+dateOnly(r.created)+'</td><td>'+dateOnly(r.sold_at)+'</td><td>'+action+'</td></tr>'}).join(''):'<tr><td colspan="11" class="mpo-empty">No entries found.</td></tr>';
    body.querySelectorAll('tr[data-url]').forEach(function(tr){tr.onclick=function(e){if(!e.target.closest('a,button'))location.href=tr.dataset.url}});
    body.querySelectorAll('.js-mpo-delete').forEach(function(btn){btn.onclick=function(e){e.preventDefault();e.stopPropagation();if(!confirm('Delete unpaid order #'+btn.dataset.id+'?\\n\\nThis cannot be undone.'))return;btn.disabled=true;var fd=new FormData;fd.set('action','admin_delete_unpaid_marketplace_order');fd.set('kind',btn.dataset.kind);fd.set('id',btn.dataset.id);fetch('<?= AJAX_URL ?>',{method:'POST',body:fd,credentials:'same-origin'}).then(function(res){return res.json()}).then(function(d){if(d&&d.success){rows=rows.filter(function(r){return !(String(r.id)===String(btn.dataset.id)&&r.kind==='Order')});render();if(d.sendToast&&typeof create_toast==='function')create_toast(d.sendToast.type||'success',d.sendToast.title||'Deleted',d.sendToast.message||'Order deleted.');return}var msg=d&&d.sendToast?d.sendToast.message:'Order could not be deleted.';if(typeof create_toast==='function')create_toast('danger','Error',msg);else alert(msg)}).catch(function(){alert('Order could not be deleted.')}).finally(function(){btn.disabled=false})}});
    body.querySelectorAll('.js-mpo-listing-toggle').forEach(function(btn){btn.onclick=function(e){e.preventDefault();e.stopPropagation();var active=btn.dataset.active==='1';if(!confirm((active?'List':'Unlist')+' this listing?'))return;btn.disabled=true;var fd=new FormData;fd.set('action','admin_unlist_marketplace_listing');fd.set('kind',btn.dataset.kind);fd.set('id',btn.dataset.id);fd.set('active',active?'1':'0');fetch('<?= AJAX_URL ?>',{method:'POST',body:fd,credentials:'same-origin'}).then(function(res){return res.json()}).then(function(d){if(!d||!d.success)throw new Error(d&&d.sendToast?d.sendToast.message:'Could not update listing.');var row=rows.find(function(r){return r.kind==='Listing'&&String(r.id)===String(btn.dataset.id)});if(row){row.status=active?'Listed':'Unlisted';row.raw_status=active?'LISTED':'UNLISTED'}render();if(d.sendToast&&typeof create_toast==='function')create_toast(d.sendToast.type||'success',d.sendToast.title||(active?'Listed':'Unlisted'),d.sendToast.message||'Listing updated.');}).catch(function(err){if(typeof create_toast==='function')create_toast('danger','Error',err.message);else alert(err.message)}).finally(function(){btn.disabled=false})}});
    showing.textContent=filtered.length?start+1+'–'+Math.min(start+perPage,filtered.length):'0';total.textContent=filtered.length;pages.innerHTML='';
    for(var i=1;i<=max;i++){if(max>9&&Math.abs(i-page)>2&&i!==1&&i!==max)continue;var b=document.createElement('button');b.className='mpo-page'+(i===page?' active':'');b.textContent=i;(function(p){b.onclick=function(){page=p;render()}})(i);pages.appendChild(b)}
  }
  document.querySelectorAll('#mpoTypeFilters .mpo-pill').forEach(function(btn){btn.onclick=function(){document.querySelectorAll('#mpoTypeFilters .mpo-pill').forEach(function(x){x.classList.remove('active')});btn.classList.add('active');typeFilter=btn.dataset.type;page=1;rebuildStatuses();render()}});
  document.getElementById('mpoSearch').oninput=function(){query=this.value.trim().toLowerCase();page=1;render()};rebuildStatuses();render();
})();
</script>
<?= $this->end() ?>
