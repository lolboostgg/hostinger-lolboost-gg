<?php
/* ── Admin: Digital Goods Listings — /admin-area/digital-goods/listings ── */
$listings    = is_array($listings    ?? null) ? $listings    : [];
$categories  = is_array($categories  ?? null) ? $categories  : [];
$pagination  = is_array($pagination  ?? null) ? $pagination  : [];
$filterStatus= $status      ?? '';
$filterCat   = (int)($catId ?? 0);
$filterSearch= $search      ?? '';
$page        = (int)($page  ?? 1);
$totalPages  = (int)($pagination['totalPages'] ?? 1);
$totalItems  = (int)($pagination['totalItems'] ?? count($listings));
$h = fn($v) => htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8');

$dgCategoryIconMap = [
    'streaming'             => 'fa-solid fa-play',
    'streaming & music'     => 'fa-solid fa-play',
    'software'              => 'fa-solid fa-microchip',
    'software & tools'      => 'fa-solid fa-microchip',
    'subscriptions'         => 'fa-solid fa-repeat',
    'ingame-currency'       => 'fa-solid fa-coins',
    'ingame currency'       => 'fa-solid fa-coins',
    'social-dating'         => 'fa-solid fa-heart',
    'social dating'         => 'fa-solid fa-heart',
    'gaming-subscriptions'  => 'fa-solid fa-gamepad',
    'gaming subscriptions'  => 'fa-solid fa-gamepad',
    'ai-productivity'       => 'fa-solid fa-robot',
    'ai productivity'       => 'fa-solid fa-robot',
    'discord'               => 'fa-brands fa-discord',
];
$dgCategoryIcon = static function (array $cat) use ($dgCategoryIconMap): string {
    $icon = trim((string)($cat['icon'] ?? ''));
    if ($icon !== '') return $icon;

    $slugKey = strtolower(trim((string)($cat['slug'] ?? '')));
    if ($slugKey !== '' && isset($dgCategoryIconMap[$slugKey])) return $dgCategoryIconMap[$slugKey];

    $nameKey = strtolower(trim((string)($cat['name'] ?? '')));
    if ($nameKey !== '' && isset($dgCategoryIconMap[$nameKey])) return $dgCategoryIconMap[$nameKey];

    return 'fa-solid fa-tag';
};
?>
<?= $this->layout('admin/layouts/main', ['meta' => $meta ?? ['title' => 'DG Listings | Admin']]) ?>

<?= $this->start('styles') ?>
<style>
/* ── table page base ── */
.al-page .card{background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important;}
.al-page .card::before{display:none!important;}
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:var(--bs-card-bg,#141720);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(139,60,247,.25),rgba(192,38,211,.15));border:1px solid rgba(139,60,247,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
.al-toolbar{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:var(--bs-card-bg,#141720);padding:12px 16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;}
.al-search-wrap{position:relative;}
.al-search-wrap input{background:rgba(255,255,255,.05)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;outline:none;}
.al-search-wrap input:focus{border-color:rgba(139,60,247,.45)!important;}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25)!important;}
.al-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none;}
.al-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);text-decoration:none;}
.al-pill:hover,.al-pill.active{background:rgba(139,60,247,.18);border-color:rgba(139,60,247,.45);color:#c4b5fd;}
.al-pill[data-status="active"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.3);color:#4ade80;}
.al-pill[data-status="inactive"].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.3);color:#facc15;}
.al-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;background:var(--bs-card-bg,#141720);}
.al-table{width:100%;border-collapse:collapse;}
.al-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;}
.al-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.al-table tbody tr:last-child{border-bottom:none;}
.al-table tbody tr:hover{background:rgba(139,60,247,.06);}
.al-table tbody td{padding:12px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8);}
.al-listing-wrap{display:flex;align-items:center;gap:10px;}
.al-listing-img{width:44px;height:44px;border-radius:10px;object-fit:cover;background:rgba(255,255,255,.04);flex-shrink:0;}
.al-listing-name{font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2;}
.al-listing-sub{font-size:.73rem;color:rgba(255,255,255,.38);}
.al-toggle{appearance:none;width:36px;height:20px;border-radius:99px;background:rgba(255,255,255,.12);cursor:pointer;position:relative;border:none;transition:background .2s;flex-shrink:0;}
.al-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:rgba(255,255,255,.9);transition:transform .2s;}
.al-toggle:checked{background:linear-gradient(135deg,#8b3cf7,#c026d3);}
.al-toggle:checked::after{transform:translateX(16px);}
.al-btn{display:inline-flex;align-items:center;gap:.3rem;padding:6px 12px;border-radius:9px;font-size:.77rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);text-decoration:none;}
.al-btn:hover{background:rgba(255,255,255,.10);color:rgba(255,255,255,.9);}
.al-btn--view{background:rgba(139,60,247,.12);border-color:rgba(139,60,247,.25);color:#c084fc;}
.al-btn--view:hover{background:rgba(139,60,247,.22);color:rgba(255,255,255,.9);}
.al-btn--edit{background:rgba(56,189,248,.10);border-color:rgba(56,189,248,.22);color:#7dd3fc;}
.al-btn--edit:hover{background:rgba(56,189,248,.20);color:rgba(255,255,255,.9);}
.al-btn--duplicate{background:rgba(34,211,238,.10);border-color:rgba(34,211,238,.22);color:#67e8f9;}
.al-btn--duplicate:hover{background:rgba(34,211,238,.20);color:rgba(255,255,255,.9);}
.al-btn--delete{background:rgba(251,113,133,.09);border-color:rgba(251,113,133,.22);color:#fb7185;}
.al-btn--delete:hover{background:rgba(251,113,133,.18);}
.al-pg{display:flex;align-items:center;gap:6px;justify-content:center;padding:16px;}
.al-pg-btn{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;text-decoration:none;}
.al-pg-btn:hover:not(.al-pg-disabled){background:rgba(255,255,255,.09);}
.al-pg-btn.al-pg-active{background:rgba(139,60,247,.25);border-color:rgba(139,60,247,.45);color:#c4b5fd;}
.al-pg-btn.al-pg-disabled{opacity:.35;cursor:not-allowed;}
/* Force dark dropdown on all selects */
.al-page select, .dgl-edit-canvas select { color-scheme: dark; background:#1a1c24 !important; color:rgba(255,255,255,.85) !important; }
.al-page select option, .dgl-edit-canvas select option { background:#1a1c24 !important; color:rgba(255,255,255,.9) !important; }

/* ── Offcanvas edit panel ── */
.dgl-edit-canvas{width:min(980px,96vw)!important;display:flex!important;flex-direction:column!important;height:100%!important;}

.dgl-edit-canvas .offcanvas-body{flex:1!important;overflow:hidden!important;display:flex!important;flex-direction:column!important;padding:0!important;}
.dgl-edit-canvas form{height:100%;display:flex;flex-direction:column;overflow:hidden;min-height:0;}
.dgl-oc-scroll{flex:1;overflow-y:auto;padding:18px 22px;min-height:0;}
.dgl-oc-footer{flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 22px;border-top:1px solid rgba(255,255,255,.07);background:var(--bs-offcanvas-bg,#1e2028);}
.dgl-oc-footer-left{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
@media(max-width:991px){.dgl-edit-canvas{width:100vw!important;}.dgf-row,.dgf-row-3{grid-template-columns:1fr;}}
.dgf-section-head{font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.3);font-weight:900;margin:18px 0 10px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.06);}
.dgf-label{font-size:.78rem;font-weight:700;color:rgba(255,255,255,.5);margin-bottom:4px;display:block;}
.dgf-input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.11);border-radius:10px;color:rgba(255,255,255,.9);padding:9px 13px;font-size:.88rem;outline:none;transition:border-color .15s;}
.dgf-input:focus{border-color:rgba(139,60,247,.5);box-shadow:0 0 0 3px rgba(139,60,247,.12);}
.dgf-input::placeholder{color:rgba(255,255,255,.2);}
select.dgf-input{color-scheme:dark;} select.dgf-input option{background:#1a1c24;color:rgba(255,255,255,.9);}
textarea.dgf-input{resize:vertical;}
.dgf-custom-select{position:relative;user-select:none;}
.dgf-custom-select-trigger{display:flex;align-items:center;gap:10px;width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.11);border-radius:10px;color:rgba(255,255,255,.9);padding:9px 13px;font-size:.88rem;cursor:pointer;transition:border-color .15s,box-shadow .15s;}
.dgf-custom-select-trigger:hover,.dgf-custom-select.open .dgf-custom-select-trigger{border-color:rgba(139,60,247,.5);box-shadow:0 0 0 3px rgba(139,60,247,.12);}
.dgf-custom-select-icon{width:22px;height:22px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:rgba(255,255,255,.55);font-size:.85rem;}
.dgf-custom-select-text{flex:1;color:rgba(255,255,255,.55);}
.dgf-custom-select-text.selected{color:rgba(255,255,255,.9);}
.dgf-custom-select-arrow{color:rgba(255,255,255,.35);font-size:.7rem;margin-left:auto;transition:transform .2s;}
.dgf-custom-select.open .dgf-custom-select-arrow{transform:rotate(180deg);}
.dgf-custom-select-dropdown{display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:9999;background:#1e2028;border:1px solid rgba(139,60,247,.35);border-radius:12px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.5);}
.dgf-custom-select.open .dgf-custom-select-dropdown{display:block;}
.dgf-custom-select-option{display:flex;align-items:center;gap:10px;padding:10px 13px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.05);transition:background .1s;font-size:.88rem;color:rgba(255,255,255,.9);font-weight:700;}
.dgf-custom-select-option:last-child{border-bottom:none;}
.dgf-custom-select-option:hover{background:rgba(139,60,247,.18);}
.dgf-custom-select-option.active{background:rgba(139,60,247,.25);color:#c084fc;}
.dgf-brand-wrap{position:relative;}
.dgf-brand-line{display:flex;align-items:center;gap:10px;}
.dgf-brand-icon-box{width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.dgf-brand-icon-box img{width:100%;height:100%;object-fit:contain;display:none;border-radius:9px;}
.dgf-brand-suggestions{display:none;position:absolute;top:100%;left:0;right:0;z-index:9999;background:#1e2028;border:1px solid rgba(139,60,247,.35);border-radius:12px;margin-top:4px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.5);max-height:420px;}
.dgf-dropzone{border:1.5px dashed rgba(255,255,255,.16);background:rgba(255,255,255,.035);border-radius:14px;padding:18px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s;}
.dgf-dropzone:hover,.dgf-dropzone.is-dragover{border-color:rgba(139,60,247,.65);background:rgba(139,60,247,.09);}
.dgf-dropzone i{font-size:1.55rem;color:#a78bfa;margin-bottom:8px;display:block;}
.dgf-dropzone-title{font-size:.9rem;font-weight:900;color:rgba(255,255,255,.9);}
.dgf-dropzone-sub{font-size:.76rem;color:rgba(255,255,255,.38);margin-top:3px;}
.dgf-preview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(84px,1fr));gap:10px;margin-top:12px;}
.dgf-preview{position:relative;border-radius:12px;overflow:hidden;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);aspect-ratio:1/1;}
.dgf-preview img{width:100%;height:100%;object-fit:cover;display:block;}
.dgf-preview button{position:absolute;top:5px;right:5px;width:24px;height:24px;border-radius:999px;border:none;background:rgba(0,0,0,.65);color:rgba(255,255,255,.9);font-size:.7rem;display:flex;align-items:center;justify-content:center;}
.dgf-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.dgf-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}
.dgf-help{font-size:.72rem;color:rgba(255,255,255,.28);margin-top:3px;}
/* ── Validity Picker ── */
.dgf-validity-picker{margin-bottom:4px;}
.dgf-vp-pills{display:flex;flex-wrap:wrap;gap:6px;}
.dgf-vp-pill{display:inline-flex;align-items:center;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(255,255,255,.55);transition:background .15s,border-color .15s,color .15s;}
.dgf-vp-pill:hover{background:rgba(139,60,247,.15);border-color:rgba(139,60,247,.4);color:#c084fc;}
.dgf-vp-pill.active{background:rgba(139,60,247,.25);border-color:rgba(139,60,247,.6);color:rgba(255,255,255,.9);}
.dgf-vp-pill--custom{border-style:solid;color:rgba(255,255,255,.45);}
.dgf-vp-custom-wrap{display:flex;align-items:center;gap:8px;margin-top:8px;padding:7px 12px;border-radius:9px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);}
.dgf-vp-custom-input{flex:1;background:transparent;border:0;outline:0;color:rgba(255,255,255,.85);font-size:.84rem;font-weight:600;}
.dgf-vp-custom-input::placeholder{color:rgba(255,255,255,.22);font-weight:500;}
.dgf-vp-custom-hint{font-size:.74rem;font-weight:700;color:#a78bfa;white-space:nowrap;}
.dgf-save-btn{display:inline-flex;align-items:center;gap:.45rem;padding:9px 22px;border-radius:12px;background:linear-gradient(135deg,#8b3cf7,#c026d3);border:none;color:rgba(255,255,255,.9);font-weight:900;font-size:.88rem;cursor:pointer;transition:opacity .15s;}
.dgf-save-btn:hover{opacity:.88;}
.dgf-save-btn:disabled{opacity:.5;cursor:not-allowed;}

/* ── Listing thumbnail in offcanvas header ── */
.dgl-oc-head{display:flex;align-items:center;gap:12px;padding:14px 22px;border-bottom:1px solid rgba(255,255,255,.07);}
.dgl-oc-thumb{width:48px;height:48px;border-radius:12px;object-fit:cover;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);flex-shrink:0;}
.dgl-oc-info{}
.dgl-oc-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;margin:0;}
.dgl-oc-sub{font-size:.76rem;color:rgba(255,255,255,.4);margin-top:2px;}
</style>
<?= $this->stop() ?>

<div class="al-page">

  <!-- Hero -->
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-solid fa-box-open"></i></div>
      <div>
        <h1 class="al-hero-title">Digital Goods — Listings</h1>
        <div class="al-hero-sub"><?= $totalItems ?> total listings across all sellers</div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/admin-area/digital-goods/categories" class="al-btn">
      <i class="fa-solid fa-tags"></i> Manage Categories
    </a>
  </div>

  <!-- Toolbar -->
  <div class="al-toolbar">
    <form method="GET" style="display:contents;">
      <input type="hidden" name="category_id" value="<?= $filterCat ?>">
      <input type="hidden" name="status"       value="<?= $h($filterStatus) ?>">
      <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="text" name="search" placeholder="Search title or brand..." value="<?= $h($filterSearch) ?>" onchange="this.form.submit()">
      </div>
    </form>
    <a class="al-pill <?= $filterStatus===''?'active':'' ?>" href="?category_id=<?= $filterCat ?>&search=<?= urlencode($filterSearch) ?>">All</a>
    <a class="al-pill <?= $filterStatus==='active'?'active':'' ?>" data-status="active" href="?status=active&category_id=<?= $filterCat ?>&search=<?= urlencode($filterSearch) ?>">Active</a>
    <a class="al-pill <?= $filterStatus==='inactive'?'active':'' ?>" data-status="inactive" href="?status=inactive&category_id=<?= $filterCat ?>&search=<?= urlencode($filterSearch) ?>">Inactive</a>
    <select style="background:#1a1c24;border:1px solid rgba(255,255,255,.09);border-radius:10px;color:rgba(255,255,255,.8);padding:6px 12px;font-size:.84rem;outline:none;color-scheme:dark;"
            onchange="location.href='?category_id='+this.value+'&status=<?= urlencode($filterStatus) ?>&search=<?= urlencode($filterSearch) ?>'">
      <option value="0" <?= $filterCat===0?'selected':'' ?> style="background:#1a1c24;color:#fff;">All Categories</option>
      <?php foreach ($categories as $cat): ?>
      <option value="<?= (int)$cat['id'] ?>" <?= $filterCat===(int)$cat['id']?'selected':'' ?> style="background:#1a1c24;color:#fff;"><?= $h($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <div style="margin-left:auto;font-size:.82rem;color:rgba(255,255,255,.35);"><?= $totalItems ?> results</div>
  </div>

  <!-- Table -->
  <div class="al-table-wrap">
    <?php if (empty($listings)): ?>
    <div style="text-align:center;padding:56px 24px;color:rgba(255,255,255,.35);">
      <i class="fa-solid fa-inbox" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:12px;"></i>
      No listings found.
    </div>
    <?php else: ?>
    <table class="al-table">
      <thead>
        <tr>
          <th>#</th><th>Product</th><th>Category</th><th>Seller</th><th>Price</th><th>Stock</th><th>Sold</th><th>Active</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($listings as $l):
          $brandIconPath = !empty($l['brand_icon']) ? (string)$l['brand_icon'] : '';
          $brandIconPath = preg_replace('#^https?://[^/]+#', '', $brandIconPath);
          $brandIconPath = preg_replace('#^/public/assets#', '', $brandIconPath);
          $price    = '€'.number_format((int)$l['price']/100, 2);
          $priceNum = number_format((int)$l['price']/100, 2, '.', '');
        ?>
        <tr id="dgl-row-<?= (int)$l['id'] ?>">
          <td style="font-size:.72rem;color:rgba(255,255,255,.25);font-weight:800;">#<?= (int)$l['id'] ?></td>
          <td>
            <div class="al-listing-wrap">
              <div class="al-listing-img" style="display:flex;align-items:center;justify-content:center;<?= $brandIconPath ? '' : 'background:rgba(99,102,241,.12);' ?>">
                <?php if ($brandIconPath !== ''): ?>
                  <img src="<?= $h(ASSET_URL . $brandIconPath) ?>" alt="" style="width:100%;height:100%;object-fit:contain;border-radius:10px;padding:4px;">
                <?php else: ?>
                  <i class="fa-solid fa-box" style="color:#a78bfa;font-size:1rem;"></i>
                <?php endif; ?>
              </div>
              <div>
                <div class="al-listing-name"><?= $h($l['title']??'') ?></div>
                <div class="al-listing-sub"><?= $h($l['brand']??'') ?><?= $l['region'] ? ' · '.$h($l['region']) : '' ?></div>
              </div>
            </div>
          </td>
          <td style="font-size:.82rem;color:rgba(255,255,255,.55);"><?= $h($l['category_name']??'—') ?></td>
          <td>
            <a href="<?= BASE_URL ?>/admin-area/seller/<?= (int)$l['seller_id'] ?>" style="color:#c084fc;font-weight:700;font-size:.82rem;text-decoration:none;" target="_blank"><?= $h($l['seller_username']??'—') ?></a>
          </td>
          <td style="font-weight:800;color:#fff;"><?= $price ?></td>
          <td><?= (int)$l['stock'] ?></td>
          <td style="color:rgba(255,255,255,.5);"><?= (int)$l['sold_count'] ?></td>
          <td>
            <input type="checkbox" class="al-toggle dgl-toggle" <?= (int)$l['active']?'checked':'' ?> data-id="<?= (int)$l['id'] ?>">
          </td>
          <td>
            <div style="display:flex;gap:6px;align-items:center;">
              <a href="<?= BASE_URL ?>/digital-good/<?= $h($l['slug']??'') ?>" target="_blank" class="al-btn al-btn--view" title="View on site">
                <i class="fa-solid fa-eye"></i>
              </a>
              <button type="button" class="al-btn al-btn--edit dgl-edit-btn" title="Edit"
                data-id="<?= (int)$l['id'] ?>"
                data-title="<?= $h($l['title']??'') ?>"
                data-slug="<?= $h($l['slug'] ?? '') ?>"
                data-brand="<?= $h($l['brand']??'') ?>"
                data-description="<?= $h($l['description']??'') ?>"
                data-price="<?= $priceNum ?>"
                data-stock="<?= (int)$l['stock'] ?>"
                data-region="<?= $h($l['region']??'') ?>"
                data-validity="<?= (int)($l['validity_days']??0) ?>"
                data-category="<?= (int)$l['category_id'] ?>"
                data-active="<?= (int)$l['active'] ?>"
                data-delivery="<?= $h($l['delivery_type']??'manual') ?>"
                data-delivery-instructions="<?= $h($l['delivery_instructions']??'') ?>"
                data-min-qty="<?= (int)($l['min_purchase_qty']??1) ?>"
                data-max-qty="<?= (int)($l['max_purchase_qty']??0) ?>"
                data-brand-icon="<?= $h($brandIconPath) ?>"
                data-seller="<?= $h($l['seller_username']??'') ?>"
              >
                <i class="fa-solid fa-pen"></i> Edit
              </button>
              <button type="button" class="al-btn al-btn--duplicate dgl-duplicate-btn" title="Duplicate"
                data-id="<?= (int)$l['id'] ?>"
                data-name="<?= $h($l['title']??'') ?>">
                <i class="fa-solid fa-copy"></i>
              </button>
              <button type="button" class="al-btn al-btn--delete dgl-delete-btn" title="Delete"
                data-id="<?= (int)$l['id'] ?>"
                data-name="<?= $h($l['title']??'') ?>">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="al-pg">
      <?php if ($page > 1): ?>
      <a class="al-pg-btn" href="?page=<?= $page-1 ?>&status=<?= urlencode($filterStatus) ?>&category_id=<?= $filterCat ?>&search=<?= urlencode($filterSearch) ?>"><i class="fa-solid fa-chevron-left"></i></a>
      <?php else: ?><span class="al-pg-btn al-pg-disabled"><i class="fa-solid fa-chevron-left"></i></span><?php endif; ?>
      <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
      <a class="al-pg-btn <?= $p===$page?'al-pg-active':'' ?>" href="?page=<?= $p ?>&status=<?= urlencode($filterStatus) ?>&category_id=<?= $filterCat ?>&search=<?= urlencode($filterSearch) ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
      <a class="al-pg-btn" href="?page=<?= $page+1 ?>&status=<?= urlencode($filterStatus) ?>&category_id=<?= $filterCat ?>&search=<?= urlencode($filterSearch) ?>"><i class="fa-solid fa-chevron-right"></i></a>
      <?php else: ?><span class="al-pg-btn al-pg-disabled"><i class="fa-solid fa-chevron-right"></i></span><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>

</div>

<!-- ══ Offcanvas: Edit Listing ══ -->
<div class="offcanvas offcanvas-end dgl-edit-canvas" tabindex="-1" id="dglEditCanvas">
  <div class="dgl-oc-head">
    <div id="dglOcThumb" class="dgl-oc-thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#a78bfa;"></div>
    <div class="dgl-oc-info">
      <div class="dgl-oc-title" id="dglOcTitle">Edit Listing</div>
      <div class="dgl-oc-sub" id="dglOcSub"></div>
    </div>
    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <form id="dglEditForm" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="action" value="admin_dg_update_listing">
      <input type="hidden" name="id"     id="dglEditId" value="">

      <div class="dgl-oc-scroll">
      <!-- Basic -->
      <div class="dgf-section-head">Basic Information</div>
      <div class="mb-3">
        <label class="dgf-label">Title</label>
        <input type="text" name="title" id="dglEditTitle" class="dgf-input" required placeholder="Product title">
      </div>
      <div class="mb-3">
        <label class="dgf-label">Slug</label>
        <input type="text" name="slug" id="dglEditSlug" class="dgf-input" placeholder="youtube-premium-12-months">
        <div class="dgf-help">Auto-generated from the title. You can customize it. Duplicates become -2, -3, etc.</div>
      </div>
      <div class="dgf-row mb-3">
        <div>
          <label class="dgf-label">Brand</label>
          <div class="dgf-brand-wrap">
            <div class="dgf-brand-line">
              <div class="dgf-brand-icon-box">
                <i class="fa-solid fa-tag" style="color:rgba(255,255,255,.25);font-size:.9rem;" id="dglEditBrandIconPlaceholder"></i>
                <img id="dglEditBrandIconImg" src="" alt="">
              </div>
              <input type="text" name="brand" id="dglEditBrand" class="dgf-input" placeholder="Spotify, Netflix…" autocomplete="off" style="flex:1;">
            </div>
            <div id="dglEditBrandSuggestions" class="dgf-brand-suggestions"></div>
          </div>
          <input type="hidden" name="brand_icon" id="dglEditBrandIconField" value="">
        </div>
        <div>
          <label class="dgf-label">Category</label>
          <input type="hidden" name="category_id" id="dglEditCategory" required>
          <div class="dgf-custom-select" id="dglEditCategorySelect">
            <div class="dgf-custom-select-trigger">
              <span class="dgf-custom-select-icon"><i class="fa-solid fa-layer-group"></i></span>
              <span class="dgf-custom-select-text">Select category</span>
              <i class="fa-solid fa-chevron-down dgf-custom-select-arrow"></i>
            </div>
            <div class="dgf-custom-select-dropdown">
              <?php foreach ($categories as $cat): ?>
              <div class="dgf-custom-select-option" data-value="<?= (int)$cat['id'] ?>" data-name="<?= $h($cat['name'] ?? '') ?>" data-icon="<?= $h($dgCategoryIcon($cat)) ?>">
                <span class="dgf-custom-select-icon"><i class="<?= $h($dgCategoryIcon($cat)) ?>"></i></span>
                <span><?= $h($cat['name'] ?? '') ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label class="dgf-label">Description</label>
        <textarea name="description" id="dglEditDesc" class="dgf-input" rows="4" placeholder="Product description…"></textarea>
      </div>

      <!-- Pricing & Stock -->
      <div class="dgf-section-head">Pricing & Stock</div>
      <div class="dgf-row-3 mb-3">
        <div>
          <label class="dgf-label">Price (€)</label>
          <input type="number" name="price_display" id="dglEditPrice" class="dgf-input" step="0.01" min="0.01" required placeholder="9.99">
        </div>
        <div>
          <label class="dgf-label">Stock</label>
          <input type="number" name="stock" id="dglEditStock" class="dgf-input" min="0" placeholder="50">
        </div>
        <div>
          <label class="dgf-label">Validity</label>
          <div class="dgf-validity-picker" id="dglEditValidityPicker">
            <div class="dgf-vp-pills">
              <button type="button" class="dgf-vp-pill" data-days="7">7 days</button>
              <button type="button" class="dgf-vp-pill" data-days="14">14 days</button>
              <button type="button" class="dgf-vp-pill" data-days="30">1 month</button>
              <button type="button" class="dgf-vp-pill" data-days="90">3 months</button>
              <button type="button" class="dgf-vp-pill" data-days="180">6 months</button>
              <button type="button" class="dgf-vp-pill" data-days="365">12 months</button>
              <button type="button" class="dgf-vp-pill" data-days="0">Lifetime</button>
              <button type="button" class="dgf-vp-pill dgf-vp-pill--custom" data-days="custom">Custom</button>
            </div>
            <div class="dgf-vp-custom-wrap" style="display:none;">
              <input type="text" class="dgf-vp-custom-input" placeholder="e.g. 12 hours, 3 days, 2 weeks…">
              <span class="dgf-vp-custom-hint"></span>
            </div>
          </div>
          <input type="hidden" name="validity_days" id="dglEditValidity" value="0">
          <div class="dgf-help">0 = lifetime</div>
        </div>
      </div>
      <div class="dgf-row mb-3">
        <div>
          <label class="dgf-label">Min qty</label>
          <input type="number" name="min_purchase_qty" id="dglEditMinQty" class="dgf-input" min="1" step="1" value="1">
        </div>
        <div>
          <label class="dgf-label">Max qty</label>
          <input type="number" name="max_purchase_qty" id="dglEditMaxQty" class="dgf-input" min="1" step="1" placeholder="—">
        </div>
      </div>

      <!-- Region & Delivery -->
      <div class="dgf-section-head">Region & Delivery</div>
      <div class="dgf-row mb-3">
        <div>
          <label class="dgf-label">Region</label>
          <input type="text" name="region" id="dglEditRegion" class="dgf-input" placeholder="EU, DE, Global…">
        </div>
        <div>
          <label class="dgf-label">Delivery Type</label>
          <select name="delivery_type" id="dglEditDelivery" class="dgf-input">
            <option value="manual">Manual</option>
            <option value="auto">Auto</option>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label class="dgf-label">Delivery instructions</label>
        <textarea name="delivery_instructions" id="dglEditDeliveryInstructions" class="dgf-input" rows="4" placeholder="Private delivery instructions or redemption details for the buyer."></textarea>
      </div>

      <!-- Status -->
      <div class="dgf-section-head">Visibility</div>
      <div class="mb-4" style="display:flex;align-items:center;gap:10px;">
        <input type="checkbox" name="active" id="dglEditActive" class="al-toggle" value="1">
        <label for="dglEditActive" style="font-size:.88rem;font-weight:700;color:rgba(255,255,255,.7);cursor:pointer;">Listing active</label>
      </div>

      </div>

      <div class="dgl-oc-footer">
        <button type="button" class="al-btn" data-bs-dismiss="offcanvas"><i class="fa-solid fa-xmark"></i> Cancel</button>
        <div class="dgl-oc-footer-left">
          <span id="dglEditMsg" style="font-size:.82rem;"></span>
          <button type="submit" class="dgf-save-btn" id="dglEditSaveBtn">
            <i class="fa-solid fa-save"></i> Save Changes
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    var AJAX_URL = (typeof ajax_url !== 'undefined') ? ajax_url : '<?= addslashes(AJAX_URL) ?>';
    var canvas   = document.getElementById('dglEditCanvas');
    var bsCanvas = new bootstrap.Offcanvas(canvas);
    var form     = document.getElementById('dglEditForm');
    var msgEl    = document.getElementById('dglEditMsg');
    var saveBtn  = document.getElementById('dglEditSaveBtn');
    var DG_ASSET_URL = (typeof asset_url !== 'undefined') ? asset_url : '<?= addslashes(ASSET_URL) ?>';

    function parseAjaxResponse(raw) {
        if (raw && typeof raw === 'object') return raw;
        raw = String(raw || '').trim();
        if (!raw) return null;
        try { return JSON.parse(raw); } catch (e) {}
        var first = raw.indexOf('{'), last = raw.lastIndexOf('}');
        if (first >= 0 && last > first) {
            try { return JSON.parse(raw.slice(first, last + 1)); } catch (e) {}
        }
        return null;
    }

    function toast(type, title, message) {
        if (typeof create_toast === 'function') create_toast(type || 'primary', title || '', message || '');
        else if (message) alert(message);
    }

    function dglSlugify(value) {
        return String(value || '')
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '') || 'digital-good';
    }

    function dglBindSlugAuto() {
        var titleEl = document.getElementById('dglEditTitle');
        var slugEl = document.getElementById('dglEditSlug');
        if (!titleEl || !slugEl) return function(){};
        var touched = false;
        slugEl.addEventListener('input', function() {
            touched = true;
            this.value = dglSlugify(this.value);
        });
        titleEl.addEventListener('input', function() {
            if (!touched || !slugEl.value.trim()) slugEl.value = dglSlugify(this.value);
        });
        return function(resetTouched) { touched = !!resetTouched; };
    }
    var dglEditSlugTouched = dglBindSlugAuto();


    var dgBrandIcons = {
        'youtube'             : '/website/images/digital-goods/youtube.png',
        'spotify'             : '/website/images/digital-goods/spotify.jpg',
        'discord nitro'       : '/website/images/digital-goods/discord-nitro.png',
        'chatgpt'             : '/website/images/digital-goods/chat-gpt.png',
        'xbox game pass'      : '/website/images/digital-goods/xbox-gamepass.jpg',
        'hytale'              : '/website/images/digital-goods/hytale.webp',
        'adguard premium'     : '/website/images/digital-goods/adguard-premium.webp',
        'voicemod pro'        : '/website/images/digital-goods/voicemod-pro.webp',
        'perplexity'          : '/website/images/digital-goods/perplexity.webp',
        'deezer'              : '/website/images/digital-goods/deezer.webp',
        'fortnite vbucks'     : '/website/images/digital-goods/fortnite-vbucks.webp',
        'grok'                : '/website/images/digital-goods/grok.webp',
        'warframe'            : '/website/images/digital-goods/warframe.webp',
        'rocket league'       : '/website/images/digital-goods/rocket-league.webp',
        'linkedin'            : '/website/images/digital-goods/linkedin.webp',
        'runescape'           : '/website/images/digital-goods/runescape-fantasy.webp',
        'evernote'            : '/website/images/digital-goods/evernote.webp',
        'canva'               : '/website/images/digital-goods/canva.webp',
        'photoroom'           : '/website/images/digital-goods/photoroom.webp',
        'grammarly'           : '/website/images/digital-goods/grammarly.webp',
        'f1 tv'               : '/website/images/digital-goods/f1-tv.webp',
        'steam'               : '/website/images/digital-goods/steam.webp',
        'snapchat'            : '/website/images/digital-goods/snapchat.webp',
        'hbo'                 : '/website/images/digital-goods/hbo.webp',
        'bumble'              : '/website/images/digital-goods/bumble.webp',
        'disney plus'         : '/website/images/digital-goods/disney.webp',
        'capcut'              : '/website/images/digital-goods/capcut.webp',
        'duolingo'            : '/website/images/digital-goods/duolingo.webp',
        'nba league pass'     : '/website/images/digital-goods/nba-pass.webp',
        'reddit'              : '/website/images/digital-goods/reddit.webp',
        'medal tv'            : '/website/images/digital-goods/medaltv.webp',
        'turbo vpn'           : '/website/images/digital-goods/turbo-vpn.webp',
        'prime video'         : '/website/images/digital-goods/prime-video.webp',
        'twitch'              : '/website/images/digital-goods/twitch.webp',
        'adobe creative cloud': '/website/images/digital-goods/adobe-creative-cloud.webp',
        'badoo'               : '/website/images/digital-goods/badoo.webp',
        'claude'              : '/website/images/digital-goods/claude.webp',
        'epic games'          : '/website/images/digital-goods/epic-games.webp',
        'crunchyroll'         : '/website/images/digital-goods/crunchyroll.webp',
        'tinder'              : '/website/images/digital-goods/tinder.webp',
        'ps plus'             : '/website/images/digital-goods/ps-plus.webp',
        'gemini'              : '/website/images/digital-goods/gemini.webp',
        'cod points'          : '/website/images/digital-goods/cod-points.webp',
        'discord'             : '/website/images/digital-goods/discord-nitro.png',
        'chat gpt'            : '/website/images/digital-goods/chat-gpt.png',
        'openai'              : '/website/images/digital-goods/chat-gpt.png',
        'xbox'                : '/website/images/digital-goods/xbox-gamepass.jpg',
        'xbox gamepass'       : '/website/images/digital-goods/xbox-gamepass.jpg',
        'game pass'           : '/website/images/digital-goods/xbox-gamepass.jpg',
        'adguard'             : '/website/images/digital-goods/adguard-premium.webp',
        'voice mod'           : '/website/images/digital-goods/voicemod-pro.webp',
        'voicemod'            : '/website/images/digital-goods/voicemod-pro.webp',
        'fortnite'            : '/website/images/digital-goods/fortnite-vbucks.webp',
        'vbucks'              : '/website/images/digital-goods/fortnite-vbucks.webp',
        'v-bucks'             : '/website/images/digital-goods/fortnite-vbucks.webp',
        'rocketleague'        : '/website/images/digital-goods/rocket-league.webp',
        'runescape fantasy'   : '/website/images/digital-goods/runescape-fantasy.webp',
        'photo room'          : '/website/images/digital-goods/photoroom.webp',
        'formula 1'           : '/website/images/digital-goods/f1-tv.webp',
        'f1tv'                : '/website/images/digital-goods/f1-tv.webp',
        'disney'              : '/website/images/digital-goods/disney.webp',
        'disney+'             : '/website/images/digital-goods/disney.webp',
        'nba pass'            : '/website/images/digital-goods/nba-pass.webp',
        'league pass'         : '/website/images/digital-goods/nba-pass.webp',
        'medaltv'             : '/website/images/digital-goods/medaltv.webp',
        'prime'               : '/website/images/digital-goods/prime-video.webp',
        'amazon prime'        : '/website/images/digital-goods/prime-video.webp',
        'adobe'               : '/website/images/digital-goods/adobe-creative-cloud.webp',
        'creative cloud'      : '/website/images/digital-goods/adobe-creative-cloud.webp',
        'playstation plus'    : '/website/images/digital-goods/ps-plus.webp',
        'playstation'         : '/website/images/digital-goods/ps-plus.webp',
        'cod'                 : '/website/images/digital-goods/cod-points.webp',
        'call of duty'        : '/website/images/digital-goods/cod-points.webp'
};
    var dgBrandLabels = {
        'youtube'             : 'YouTube',
        'spotify'             : 'Spotify',
        'discord nitro'       : 'Discord Nitro',
        'chatgpt'             : 'ChatGPT',
        'xbox game pass'      : 'Xbox Game Pass',
        'hytale'              : 'Hytale',
        'adguard premium'     : 'AdGuard Premium',
        'voicemod pro'        : 'VoiceMod Pro',
        'perplexity'          : 'Perplexity',
        'deezer'              : 'Deezer',
        'fortnite vbucks'     : 'Fortnite V-Bucks',
        'grok'                : 'Grok',
        'warframe'            : 'Warframe',
        'rocket league'       : 'Rocket League',
        'linkedin'            : 'LinkedIn',
        'runescape'           : 'RuneScape',
        'evernote'            : 'Evernote',
        'canva'               : 'Canva',
        'photoroom'           : 'PhotoRoom',
        'grammarly'           : 'Grammarly',
        'f1 tv'               : 'F1 TV',
        'steam'               : 'Steam',
        'snapchat'            : 'Snapchat',
        'hbo'                 : 'HBO',
        'bumble'              : 'Bumble',
        'disney plus'         : 'Disney+',
        'capcut'              : 'CapCut',
        'duolingo'            : 'Duolingo',
        'nba league pass'     : 'NBA League Pass',
        'reddit'              : 'Reddit',
        'medal tv'            : 'MedalTV',
        'turbo vpn'           : 'Turbo VPN',
        'prime video'         : 'Prime Video',
        'twitch'              : 'Twitch',
        'adobe creative cloud': 'Adobe Creative Cloud',
        'badoo'               : 'Badoo',
        'claude'              : 'Claude',
        'epic games'          : 'Epic Games',
        'crunchyroll'         : 'Crunchyroll',
        'tinder'              : 'Tinder',
        'ps plus'             : 'PS Plus',
        'gemini'              : 'Gemini',
        'cod points'          : 'COD Points',
        'discord'             : 'Discord Nitro',
        'chat gpt'            : 'ChatGPT',
        'openai'              : 'ChatGPT / OpenAI',
        'xbox'                : 'Xbox Game Pass',
        'xbox gamepass'       : 'Xbox Game Pass',
        'game pass'           : 'Xbox Game Pass',
        'adguard'             : 'AdGuard Premium',
        'voice mod'           : 'VoiceMod Pro',
        'voicemod'            : 'VoiceMod Pro',
        'fortnite'            : 'Fortnite V-Bucks',
        'vbucks'              : 'Fortnite V-Bucks',
        'v-bucks'             : 'Fortnite V-Bucks',
        'rocketleague'        : 'Rocket League',
        'runescape fantasy'   : 'RuneScape',
        'photo room'          : 'PhotoRoom',
        'formula 1'           : 'F1 TV',
        'f1tv'                : 'F1 TV',
        'disney'              : 'Disney+',
        'disney+'             : 'Disney+',
        'nba pass'            : 'NBA League Pass',
        'league pass'         : 'NBA League Pass',
        'medaltv'             : 'MedalTV',
        'prime'               : 'Prime Video',
        'amazon prime'        : 'Prime Video',
        'adobe'               : 'Adobe Creative Cloud',
        'creative cloud'      : 'Adobe Creative Cloud',
        'playstation plus'    : 'PS Plus',
        'playstation'         : 'PS Plus',
        'cod'                 : 'COD Points',
        'call of duty'        : 'COD Points'
};
    var dgBrandDisplayKeys = ["youtube",  "spotify",  "discord nitro",  "chatgpt",  "xbox game pass",  "hytale",  "adguard premium",  "voicemod pro",  "perplexity",  "deezer",  "fortnite vbucks",  "grok",  "warframe",  "rocket league",  "linkedin",  "runescape",  "evernote",  "canva",  "photoroom",  "grammarly",  "f1 tv",  "steam",  "snapchat",  "hbo",  "bumble",  "disney plus",  "capcut",  "duolingo",  "nba league pass",  "reddit",  "medal tv",  "turbo vpn",  "prime video",  "twitch",  "adobe creative cloud",  "badoo",  "claude",  "epic games",  "crunchyroll",  "tinder",  "ps plus",  "gemini",  "cod points"];

    function setBrandIconByPath(path) {
        var imgEl = document.getElementById('dglEditBrandIconImg');
        var placeholderEl = document.getElementById('dglEditBrandIconPlaceholder');
        var hiddenEl = document.getElementById('dglEditBrandIconField');
        path = String(path || '');
        if (imgEl && placeholderEl) {
            if (path) {
                imgEl.src = DG_ASSET_URL + path;
                imgEl.style.display = 'block';
                placeholderEl.style.display = 'none';
                if (hiddenEl) hiddenEl.value = path;
            } else {
                imgEl.src = '';
                imgEl.style.display = 'none';
                placeholderEl.style.display = 'block';
                if (hiddenEl) hiddenEl.value = '';
            }
        }
    }

    function setBrandIconByValue(val) {
        var key = String(val || '').trim().toLowerCase();
        setBrandIconByPath(dgBrandIcons[key] || '');
    }

    function buildBrandSuggestions(query) {
        var q = String(query || '').toLowerCase().trim();
        if (!q) return dgBrandDisplayKeys.slice();
        return dgBrandDisplayKeys.filter(function (key) {
            var label = (dgBrandLabels[key] || key).toLowerCase();
            return key.indexOf(q) !== -1 || label.indexOf(q) !== -1;
        });
    }

    function renderBrandSuggestions(items) {
        var suggestionsEl = document.getElementById('dglEditBrandSuggestions');
        var inputEl = document.getElementById('dglEditBrand');
        if (!suggestionsEl || !inputEl) return;
        suggestionsEl.innerHTML = '';
        if (!items || !items.length) { suggestionsEl.style.display = 'none'; return; }

        var searchWrap = document.createElement('div');
        searchWrap.style.cssText = 'position:sticky;top:0;z-index:2;padding:10px;background:#1e2028;border-bottom:1px solid rgba(255,255,255,.07);';
        var searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.placeholder = 'Search Brand...';
        searchInput.autocomplete = 'off';
        searchInput.style.cssText = 'width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:8px 12px;font-size:.86rem;outline:none;';
        searchWrap.appendChild(searchInput);

        var listWrap = document.createElement('div');
        listWrap.style.cssText = 'max-height:340px;overflow-y:auto;overscroll-behavior:contain;scrollbar-width:thin;';

        function drawList(listItems) {
            listWrap.innerHTML = '';
            if (!listItems.length) {
                var empty = document.createElement('div');
                empty.style.cssText = 'padding:12px 13px;color:rgba(255,255,255,.45);font-size:.86rem;';
                empty.textContent = 'Keine Brands gefunden';
                listWrap.appendChild(empty);
                return;
            }
            listItems.forEach(function (key) {
                var icon = dgBrandIcons[key];
                var label = dgBrandLabels[key] || key;
                var item = document.createElement('div');
                item.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 13px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.05);transition:background .1s;';
                item.innerHTML = '<img src="' + DG_ASSET_URL + icon + '" style="width:28px;height:28px;object-fit:contain;border-radius:6px;flex-shrink:0;" alt="">' +
                                 '<span style="font-size:.88rem;font-weight:800;color:#fff;">' + label + '</span>';
                item.addEventListener('mouseenter', function () { this.style.background = 'rgba(139,60,247,.18)'; });
                item.addEventListener('mouseleave', function () { this.style.background = ''; });
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    inputEl.value = label;
                    setBrandIconByValue(key);
                    suggestionsEl.style.display = 'none';
                });
                listWrap.appendChild(item);
            });
        }

        function filterItems(q) {
            q = String(q || '').toLowerCase().trim();
            if (!q) return items.slice();
            return items.filter(function (key) {
                var label = (dgBrandLabels[key] || key).toLowerCase();
                return key.indexOf(q) !== -1 || label.indexOf(q) !== -1;
            });
        }

        searchInput.addEventListener('input', function () { drawList(filterItems(this.value)); });
        searchInput.addEventListener('keydown', function (e) { if (e.key === 'Escape') suggestionsEl.style.display = 'none'; });

        suggestionsEl.appendChild(searchWrap);
        suggestionsEl.appendChild(listWrap);
        drawList(items);
        suggestionsEl.style.display = 'block';
    }

    function initBrandPicker() {
        var inputEl = document.getElementById('dglEditBrand');
        var suggestionsEl = document.getElementById('dglEditBrandSuggestions');
        if (!inputEl || !suggestionsEl) return;
        inputEl.addEventListener('input', function () {
            setBrandIconByValue(this.value);
            renderBrandSuggestions(buildBrandSuggestions(this.value));
        });
        inputEl.addEventListener('focus', function () {
            renderBrandSuggestions(buildBrandSuggestions(this.value));
        });
        inputEl.addEventListener('blur', function () {
            setTimeout(function () {
                if (!suggestionsEl.contains(document.activeElement)) suggestionsEl.style.display = 'none';
            }, 180);
        });
    }

    function customSelectSetValue(value) {
        var wrap = document.getElementById('dglEditCategorySelect');
        if (!wrap) return;
        var textEl = wrap.querySelector('.dgf-custom-select-text');
        var iconEl = wrap.querySelector('.dgf-custom-select-trigger .dgf-custom-select-icon');
        var hiddenEl = document.getElementById('dglEditCategory');
        var opts = wrap.querySelectorAll('.dgf-custom-select-option');
        opts.forEach(function (opt) { opt.classList.remove('active'); });
        if (!value) {
            if (textEl) { textEl.textContent = 'Select category'; textEl.classList.remove('selected'); }
            if (iconEl) iconEl.innerHTML = '<i class="fa-solid fa-layer-group"></i>';
            if (hiddenEl) hiddenEl.value = '';
            return;
        }
        opts.forEach(function (opt) {
            if (opt.dataset.value == value) {
                opt.classList.add('active');
                if (textEl) { textEl.textContent = opt.dataset.name; textEl.classList.add('selected'); }
                if (iconEl) iconEl.innerHTML = '<i class="' + (opt.dataset.icon || 'fa-solid fa-tag') + '"></i>';
                if (hiddenEl) hiddenEl.value = value;
            }
        });
    }

    function initCustomSelect() {
        var wrap = document.getElementById('dglEditCategorySelect');
        if (!wrap) return;
        var trigger = wrap.querySelector('.dgf-custom-select-trigger');
        var opts = wrap.querySelectorAll('.dgf-custom-select-option');
        if (trigger) trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            wrap.classList.toggle('open');
        });
        opts.forEach(function (opt) {
            opt.addEventListener('click', function () {
                customSelectSetValue(this.dataset.value);
                wrap.classList.remove('open');
            });
        });
        document.addEventListener('click', function () { wrap.classList.remove('open'); });
    }

    /* ── Validity Picker ── */
    function parseValidityInput(str) {
        str = String(str || '').trim().toLowerCase();
        if (!str) return null;
        var m;
        m = str.match(/^(\d+(?:\.\d+)?)\s*(hour|hours|hr|hrs|h)$/);
        if (m) return parseFloat(m[1]) / 24;
        m = str.match(/^(\d+(?:\.\d+)?)\s*(day|days|d)$/);
        if (m) return parseFloat(m[1]);
        m = str.match(/^(\d+(?:\.\d+)?)\s*(week|weeks|w)$/);
        if (m) return parseFloat(m[1]) * 7;
        m = str.match(/^(\d+(?:\.\d+)?)\s*(month|months|mo)$/);
        if (m) return Math.round(parseFloat(m[1]) * 30);
        m = str.match(/^(\d+(?:\.\d+)?)$/);
        if (m) return parseFloat(m[1]);
        return null;
    }
    function formatValidityPreview(days) {
        if (days === null) return '—';
        if (days <= 0) return 'Lifetime';
        if (days < 1) { var h = Math.round(days * 24); return h + (h===1?' hour':' hours'); }
        if (days < 7)  return days + (days===1?' day':' days');
        if (days < 30) { var w = Math.round(days/7); return w + (w===1?' week':' weeks'); }
        var mo = Math.round(days/30); return mo + (mo===1?' month':' months');
    }
    function initAdminValidityPicker() {
        var picker = document.getElementById('dglEditValidityPicker');
        var hidden = document.getElementById('dglEditValidity');
        if (!picker || !hidden) return;
        var customWrap  = picker.querySelector('.dgf-vp-custom-wrap');
        var customInput = picker.querySelector('.dgf-vp-custom-input');
        var customHint  = picker.querySelector('.dgf-vp-custom-hint');

        function setVal(days) {
            var stored = parseInt(days || 0, 10);
            hidden.value = stored;
            picker.querySelectorAll('.dgf-vp-pill').forEach(function(p) {
                var pd = p.dataset.days;
                p.classList.toggle('active', pd !== 'custom' && parseInt(pd, 10) === stored);
            });
            if (customWrap) customWrap.style.display = 'none';
        }

        picker.querySelectorAll('.dgf-vp-pill').forEach(function(pill) {
            pill.addEventListener('click', function() {
                var d = this.dataset.days;
                picker.querySelectorAll('.dgf-vp-pill').forEach(function(p){ p.classList.remove('active'); });
                this.classList.add('active');
                if (d === 'custom') {
                    if (customWrap) { customWrap.style.display = 'flex'; }
                    if (customInput) customInput.focus();
                } else {
                    if (customWrap) customWrap.style.display = 'none';
                    hidden.value = d;
                }
            });
        });

        if (customInput) {
            customInput.addEventListener('input', function() {
                var days = parseValidityInput(this.value);
                if (days !== null) {
                    var stored = days > 0 ? Math.max(1, Math.round(days)) : 0;
                    hidden.value = stored;
                    if (customHint) customHint.textContent = '→ ' + (stored === 0 ? 'Lifetime' : formatValidityPreview(stored) + ' (' + stored + 'd)');
                } else {
                    if (customHint) customHint.textContent = this.value.trim() ? '? invalid' : '';
                }
            });
        }

        setVal(0);
        return function(days) {
            var initDays = parseInt(days || 0, 10);
            var presetMatch = [0,7,14,30,90,180,365].indexOf(initDays) !== -1;
            if (presetMatch) {
                setVal(initDays);
            } else {
                picker.querySelectorAll('.dgf-vp-pill').forEach(function(p){ p.classList.remove('active'); });
                var customPill = picker.querySelector('.dgf-vp-pill--custom');
                if (customPill) customPill.classList.add('active');
                if (customWrap) customWrap.style.display = 'flex';
                if (customInput) customInput.value = initDays + ' days';
                if (customHint)  customHint.textContent = '→ ' + initDays + ' days';
                hidden.value = initDays;
            }
        };
    }
    var setAdminValidity = initAdminValidityPicker();

    initBrandPicker();
    initCustomSelect();

    /* ── Toggle active ── */
    document.querySelectorAll('.dgl-toggle').forEach(function (tog) {
        tog.addEventListener('change', function () {
            fetch(AJAX_URL, {
                method: 'POST',
                body: new URLSearchParams({ action: 'admin_dg_toggle_listing', id: this.dataset.id, active: this.checked ? 1 : 0 })
            }).catch(function () {});
        });
    });

    /* ── Duplicate ── */
    document.querySelectorAll('.dgl-duplicate-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.id;
            var name = this.dataset.name || 'this listing';
            if (!confirm('Duplicate listing "' + name + '"? The copy will be inactive. You can edit or activate it manually.')) return;
            this.disabled = true;
            fetch(AJAX_URL, {
                method: 'POST',
                body: new URLSearchParams({ action: 'admin_dg_duplicate_listing', id: id })
            })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (r && r.success) {
                    toast('success', 'Duplicated', (r.sendToast && r.sendToast.message) || 'Listing duplicated.');
                    window.location.href = r.redirectUrl || '<?= BASE_URL ?>/admin-area/digital-goods/listings';
                } else {
                    btn.disabled = false;
                    toast('danger', 'Error', (r && (r.message || (r.sendToast && r.sendToast.message))) || 'Could not duplicate listing.');
                }
            })
            .catch(function () { btn.disabled = false; toast('danger', 'Error', 'Could not reach the server.'); });
        });
    });

    /* ── Delete ── */
    document.querySelectorAll('.dgl-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Delete "' + this.dataset.name + '"? This cannot be undone.')) return;
            var id = this.dataset.id;
            fetch(AJAX_URL, {
                method: 'POST',
                body: new URLSearchParams({ action: 'admin_dg_delete_listing', id: id })
            })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (r && r.success) {
                    var row = document.getElementById('dgl-row-' + id);
                    if (row) row.remove();
                } else if (r && r.sendToast) {
                    alert(r.sendToast.message);
                }
            });
        });
    });

    /* ── Open Edit Offcanvas ── */
    document.querySelectorAll('.dgl-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = this.dataset;

            // Header
            document.getElementById('dglOcTitle').textContent = d.title || 'Edit Listing';
            document.getElementById('dglOcSub').textContent   = d.seller ? 'Seller: ' + d.seller : '';

            // Reset form state
            if (form) form.classList.remove('was-validated');

            // Fields
            document.getElementById('dglEditId').value        = d.id        || '';
            document.getElementById('dglEditTitle').value     = d.title     || '';
            document.getElementById('dglEditSlug').value      = d.slug      || dglSlugify(d.title || '');
            if (typeof dglEditSlugTouched === 'function') dglEditSlugTouched(false);
            document.getElementById('dglEditBrand').value     = d.brand     || '';
            document.getElementById('dglEditDesc').value      = d.description || '';
            document.getElementById('dglEditDeliveryInstructions').value = d.deliveryInstructions || '';
            document.getElementById('dglEditPrice').value     = d.price     || '';
            document.getElementById('dglEditStock').value     = d.stock     || '0';
            document.getElementById('dglEditMinQty').value    = d.minQty    || '1';
            document.getElementById('dglEditMaxQty').value    = parseInt(d.maxQty || '0', 10) > 0 ? d.maxQty : '';
            document.getElementById('dglEditRegion').value    = d.region    || '';
            document.getElementById('dglEditValidity').value  = (d.validity && d.validity !== '0') ? d.validity : '';
            if (typeof setAdminValidity === 'function') setAdminValidity(parseInt(d.validity || '0', 10));
            document.getElementById('dglEditActive').checked  = d.active    === '1';

            // Category custom select
            customSelectSetValue(d.category || '');

            // Delivery
            var delSel = document.getElementById('dglEditDelivery');
            delSel.value = d.delivery || 'manual';

            if (d.brandIcon) {
                setBrandIconByPath(d.brandIcon);
            } else {
                setBrandIconByValue(d.brand || '');
            }

            msgEl.textContent = '';
            bsCanvas.show();
        });
    });

    var dglParams = new URLSearchParams(window.location.search);
    var dglEditParam = dglParams.get('edit_dg') || dglParams.get('edit');
    if (dglEditParam) {
        setTimeout(function () {
            var btn = document.querySelector('.dgl-edit-btn[data-id="' + CSS.escape(dglEditParam) + '"]');
            if (btn) btn.click();
        }, 120);
    }

    /* ── Submit ── */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            toast('danger', 'Missing information', 'Please check the required fields.');
            return;
        }
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        msgEl.textContent = '';

        var fd = new FormData(form);
        if (!document.getElementById('dglEditActive').checked) fd.set('active', '0');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', AJAX_URL, true);
        xhr.onload = function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Changes';
            try {
                var r = parseAjaxResponse(xhr.responseText);
                if (r && r.success) {
                    msgEl.textContent = 'Saved!';
                    msgEl.style.color = '#4ade80';

                    // Update the table row live
                    var id  = document.getElementById('dglEditId').value;
                    var row = document.getElementById('dgl-row-' + id);
                    if (row) {
                        var nameEl = row.querySelector('.al-listing-name');
                        var subEl  = row.querySelector('.al-listing-sub');
                        if (nameEl) nameEl.textContent = document.getElementById('dglEditTitle').value;
                        if (subEl)  subEl.textContent  = (document.getElementById('dglEditBrand').value || '') + (document.getElementById('dglEditRegion').value ? ' · ' + document.getElementById('dglEditRegion').value : '');
                        // Update price cell
                        var cells = row.querySelectorAll('td');
                        if (cells[4]) cells[4].textContent = '€' + parseFloat(document.getElementById('dglEditPrice').value).toFixed(2);
                        if (cells[5]) cells[5].textContent = document.getElementById('dglEditStock').value;
                        // Sync toggle
                        var tog = row.querySelector('.dgl-toggle');
                        if (tog) tog.checked = document.getElementById('dglEditActive').checked;

                        // Update data-* attrs so re-opening edit shows fresh values
                        row.querySelector('.dgl-edit-btn').dataset.title = document.getElementById('dglEditTitle').value;
                        row.querySelector('.dgl-edit-btn').dataset.slug = document.getElementById('dglEditSlug').value;
                        row.querySelector('.dgl-edit-btn').dataset.brand = document.getElementById('dglEditBrand').value;
                        row.querySelector('.dgl-edit-btn').dataset.region = document.getElementById('dglEditRegion').value;
                        row.querySelector('.dgl-edit-btn').dataset.description = document.getElementById('dglEditDesc').value;
                        row.querySelector('.dgl-edit-btn').dataset.deliveryInstructions = document.getElementById('dglEditDeliveryInstructions').value;
                        row.querySelector('.dgl-edit-btn').dataset.price = document.getElementById('dglEditPrice').value;
                        row.querySelector('.dgl-edit-btn').dataset.stock = document.getElementById('dglEditStock').value;
                        row.querySelector('.dgl-edit-btn').dataset.minQty = document.getElementById('dglEditMinQty').value;
                        row.querySelector('.dgl-edit-btn').dataset.maxQty = document.getElementById('dglEditMaxQty').value || '0';
                        row.querySelector('.dgl-edit-btn').dataset.validity = document.getElementById('dglEditValidity').value || '0';
                        row.querySelector('.dgl-edit-btn').dataset.category = document.getElementById('dglEditCategory').value;
                        row.querySelector('.dgl-edit-btn').dataset.delivery = document.getElementById('dglEditDelivery').value;
                        row.querySelector('.dgl-edit-btn').dataset.active = document.getElementById('dglEditActive').checked ? '1' : '0';
                        row.querySelector('.dgl-edit-btn').dataset.brandIcon = document.getElementById('dglEditBrandIconField').value || '';
                    }

                    setTimeout(function () { bsCanvas.hide(); }, 800);
                } else {
                    var msg = (r && r.sendToast && r.sendToast.message) ? r.sendToast.message : 'Error saving.';
                    msgEl.textContent = msg;
                    msgEl.style.color = '#fb7185';
                }
            } catch (err) {
                msgEl.textContent = 'Server error.';
                msgEl.style.color = '#fb7185';
                console.error(xhr.responseText);
            }
        };
        xhr.onerror = function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Changes';
            msgEl.textContent = 'Network error.';
        };
        xhr.send(fd);
    });
})();
</script>
<?= $this->stop() ?>
